<?php

namespace Dolibarr\QuickBooks;

use Product;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Item;
use Societe;
use Throwable;

class Actions
{
    public static function syncCustomers($customers): void
    {
        global $db;
        require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
        foreach ($customers as $id) {
            $customer = new Societe($db);
            $customer->fetch($id);
            Actions::GetCustomer($customer);
        }
        setEventMessages("Sincronizados correctamente", [], 'mesgs');
    }

    public static function syncSingleInvoice($invoice): void
    {
        global $db;

        $dataService = Actions::dataService();

        if (Actions::invoiceCanBeSync($invoice) && !Actions::hasFreeTextProducts($invoice)) {

            Actions::validateInvoiceReferenceMatch($invoice);

            $QBCustomer = Actions::GetCustomer($invoice->thirdparty);

            $invoiceArray = Actions::processDolibarrInvoceLines($invoice, $QBCustomer);

            $theResourceObj = \QuickBooksOnline\API\Facades\Invoice::create($invoiceArray);

            $resultingObj = $dataService->Add($theResourceObj);

            $error = $dataService->getLastError();

            if ($resultingObj->Id > 0) {

                $sqlBuscarCLienteEXtra = " SELECT * FROM " . MAIN_DB_PREFIX . "facture_extrafields where fk_object = " . $invoice->id;
                $resultUpdate = $db->query($sqlBuscarCLienteEXtra);

                $EXisteEXtrafield = $db->fetch_object($resultUpdate);

                $query = " insert into " . MAIN_DB_PREFIX . "facture_extrafields (fk_object, quickbooks_id ) values (" . $invoice->id . "," . $resultingObj->Id . ");";

                if ($EXisteEXtrafield) {
                    $query = " UPDATE  " . MAIN_DB_PREFIX . "facture_extrafields  set quickbooks_id = " . $resultingObj->Id . " where fk_object =  " . $invoice->id;
                }

                $resultUpdate = $db->query($query);
                $db->commit();
                $db->begin();

                if ($resultUpdate) {
                    setEventMessage(" Invoice $invoice->ref created in Quickbooks correctly ", 'mesgs');
                } else {
                    setEventMessage(" Invoice $invoice->ref not created in Quickbooks correctly ", 'errors');

                }
            } else {
                setEventMessage(" Invoice not created in Quickbooks correctly ", 'errors');
            }

            if ($error) {
                setEventMessages("The Status code is: ", [$error->getHttpStatusCode()], 'errors');
                setEventMessages("The Helper message is: ", [$error->getOAuthHelperError()], 'errors');
                setEventMessages("The Response message is: ", [$error->getResponseBody()], 'errors');
                echo '<script>window.location.reload(true);</script>';
            } else {
                //echo "Created Id={$resultingObj->Id}. Reconstructed response body:\n\n";
                $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
                setEventMessages("Created Id={$resultingObj->Id}. Reconstructed response body:\n\n", [$xmlBody], 'mesgs');
                Actions::UpdateExtrafieldsQuickbooks($invoice->id, $xmlBody, 'societe');
            }
        }
    }

    public static function syncBatchInvoices($invoices): void
    {
        foreach ($invoices as $invoice) {
            Actions::syncSingleInvoice($invoice);
        }
    }

    private static function processDolibarrInvoceLines($invoice, $customer): array
    {
        global $db;

        $lineasarray = [];

        foreach ($invoice->lines as $line) {

            $productoDOLIBARR = Actions::GetProductDOLIBARR($line->fk_product);

            $producto = Actions::GetProduct($productoDOLIBARR->array_options["options_quickbooks_id"]);

            if (is_null($producto)) {

                $producto = Actions::CreaProductoQbooks($line->fk_product);

                $sqlBuscarCLienteEXtra = " SELECT * FROM " . MAIN_DB_PREFIX . "product_extrafields where fk_object = " . $line->fk_product;

                $resultUpdate = $db->query($sqlBuscarCLienteEXtra);

                $EXisteEXtrafield = $db->fetch_object($resultUpdate);

                $query = " insert into " . MAIN_DB_PREFIX . "product_extrafields (fk_object, quickbooks_id ) values (" . $line->fk_product . "," . $producto->Id . ");";

                if ($EXisteEXtrafield) {
                    $query = " UPDATE  " . MAIN_DB_PREFIX . "product_extrafields  set quickbooks_id = " . $producto . " where fk_object =  " . $line->fk_product;
                }

                $db->query($query);
                $db->commit();
                $db->begin();
            }

            $lineasarray[] = [
                "Description" => $line->desc,
                "Amount" => $line->total_ht,
                "LineNum" => $line->rang,
                "DetailType" => "SalesItemLineDetail",
                "SalesItemLineDetail" => [
                    "ItemRef" => [
                        "value" => $producto->Id,
                        "name" => $producto->Name,
                    ],
                    "TaxCodeRef" => [
                        "value" => "NON",
                    ],
                    "Qty" => $line->qty,
                    # TODO: Revisar si esta es la manera correcta de registrar un descuento aplicado
                    "UnitPrice" => $line->total_ht / $line->qty,
                ],
            ];
        }

        return [
            "DocNumber" => $invoice->ref,
            "DueDate" => $db->idate($invoice->date_lim_reglement),
            "Balance" => $invoice->total_ttc - $invoice->paiement,
            "Line" => $lineasarray,
            "CustomerRef" => [
                "value" => $customer->Id,
                "name" => $customer->GivenName,
            ],
        ];
    }

    public static function dataService(): DataService
    {
        global $conf;

        $configData = [
            'auth_mode' => 'oauth2',
            'ClientID' => $conf->global->QUICKBOOKS_CLIENTID,
            'ClientSecret' => $conf->global->QUICKBOOKS_CLIENTSECRET,
            'RedirectURI' => $conf->global->QUICKBOOKS_OAUTHREDIRECT,
            'scope' => $conf->global->QUICKBOOKS_OAUTHSCOPE,
            'baseUrl' => "development"
        ];

        $dataService = DataService::Configure($configData);

        $accessToken = $_SESSION['sessionAccessToken'];

        $dataService->updateOAuth2Token($accessToken);

        return $dataService;
    }

    /**
     * Validate if the invoice to be Synchronized has products created
     * using the Free Text Type option
     *
     * @param $invoice
     * @return bool
     */
    private static function hasFreeTextProducts($invoice): bool
    {
        foreach ($invoice->lines as $line) {
            if (!$line->fk_product) {
                setEventMessages("🛑 Invoice Ref. $invoice->ref not synchronized", ["&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The invoice has free text products, replace it wit products registered and try again"], 'errors');
                return true;
            }
        }
        return false;
    }

    private static function invoiceCanBeSync($invoice): bool
    {
        $invoiceQB = Actions::GetInvoice($invoice);

        if(!is_null($invoiceQB->DocNumber) && $invoiceQB->DocNumber == $invoice->ref) {
            setEventMessages("⚠ Invoice $invoice->ref previously synchronized", [], 'warnings');
            return false;
        }

        return true;
    }

    private static function GetInvoice($invoice): ?object
    {
        try {
            $dataService = Actions::dataService();

            $invoiceQBId = $invoice->array_options["options_quickbooks_id"];

            return $dataService->Query("select * from Invoice where id = '" . $invoiceQBId . "'")[0];
        } catch (Throwable $e) {
            Actions::InfoLog('Error: ' . __METHOD__, $e->getMessage());
            return null;
        }
    }

    /**
     * Verify that the references between Dolibarr and Quickbooks invoices does match
     *
     * @param $invoice
     * @return void
     */
    private static function validateInvoiceReferenceMatch($invoice)
    {
        try {
            $dataService = Actions::dataService();

            $invoiceQBId = $invoice->array_options["options_quickbooks_id"] ?? null;

            $result = $dataService->Query("select * from Invoice where id = '" . $invoiceQBId . "'");

            if (isset($result[0]->DocNumber) && $result[0]->DocNumber != $invoice->ref) {
                setEventMessages("🛑 Invoice Ref. $invoice->ref doesn't match", ["&nbsp;&nbsp;&nbsp;The Dolibarr Invoice Ref doesn't match with the Quickbooks Invoice Ref"], 'errors');
            }
        } catch (\Throwable $e) {
            Actions::InfoLog('Error: '.__METHOD__, $e->getMessage());
        }
    }

    private static function GetCustomer($customerDLB)
    {
        global $db;

        $dataService = Actions::dataService();

        $idQB = $customerDLB->array_options["options_quickbooks_id"] ?? null;

        $result = $dataService->Query("select * from Customer where id = '$idQB'");

        $customerQB = $result[0] ?? null;

        if (!isset($customerQB->Id)) {

            $customerQB = Actions::CreaClienteQbooks($customerDLB);

            if (!isset($customerQB->Id)) {
                setEventMessages("Customer not created successfully  ", ["Possible duplicate name."], 'errors');
            } else if (isset($customerQB->Id) && $customerQB->Id > 0) {

                $searchCustomerDLBExtraInfo = " SELECT * FROM " . MAIN_DB_PREFIX . "societe_extrafields where fk_object = " . $customerDLB->id;

                $customerDLBExtraInfo = $db->query($searchCustomerDLBExtraInfo);

                $existsCustomerDLBExtraInfo = $db->fetch_object($customerDLBExtraInfo);

                $query = " insert into " . MAIN_DB_PREFIX . "societe_extrafields (fk_object, quickbooks_id ) values (" . $customerDLB->id . "," . $customerQB->Id . ");";

                if ($existsCustomerDLBExtraInfo) {
                    $query = " UPDATE  " . MAIN_DB_PREFIX . "societe_extrafields  set quickbooks_id = " . $customerQB->Id . " where fk_object =  " . $customerDLB->id;
                }

                $db->query($query);
                $db->commit();
                $db->begin();
            }
        }

        return $customerQB;
    }

    private static function CreaClienteQbooks($thirdparty)
    {
        try {
            $dataService = Actions::dataService();

            $name = $thirdparty->name;
            $thirdparty->note_public = $dolibarCustomer->note_public ?? "";
            $thirdparty->email = $dolibarCustomer->email ?? "";
            $thirdparty->phone = $dolibarCustomer->phone ?? "";

            $query = "SELECT * FROM Customer WHERE  FullyQualifiedName = '" . $name . "' AND  DisplayName = '" . $name . "'";

            $result = $dataService->Query($query);

            if (!empty($result)) {
                return $result[0];
            }

            $customerData = [
                "BillAddr" => [
                    "Line1" => "$thirdparty->address",
                    "City" => "$thirdparty->state",
                    "Country" => "$thirdparty->country",
                    "CountrySubDivisionCode" => "$thirdparty->state_code",
                    "PostalCode" => "$thirdparty->zip"
                ],
                "Notes" => "$thirdparty->note_public",
                "Title" => "",
                "GivenName" => "",
                "MiddleName" => "",
                "FamilyName" => "", //lastname
                "Suffix" => "",
                "FullyQualifiedName" => $name,
                "CompanyName" => $name,
                "DisplayName" => $name,
                "PrimaryPhone" => [
                    "FreeFormNumber" => "$thirdparty->phone"
                ],
                "PrimaryEmailAddr" => [
                    "Address" => "$thirdparty->email"
                ]
            ];

            $customerObj = Customer::create($customerData);

            return $dataService->Add($customerObj);
        } catch (\Throwable $e) {
            Actions::InfoLog("Error: " . __METHOD__, $e->getMessage());
            return null;
        }
    }

    private static function GetProductDOLIBARR($fk_product)
    {
        global $db;
        require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
        $objectP = new \Product($db);
        $extrafields = new \ExtraFields($db);

        $extrafields->fetch_name_optionals_label($objectP->table_element);

        if ($fk_product > 0)
            $result = $objectP->fetch($fk_product);

        return $objectP;
    }

    private static function GetProduct($fk_product)
    {
        $dataService = Actions::dataService();

        try {
            return $dataService->Query("select * from Item where Id = '" . $fk_product . "'")[0];
        } catch (Throwable $e) {
            Actions::InfoLog("Error: " . __METHOD__, $e->getMessage());
            return null;
        }
    }

    private static function CreaProductoQbooks($fk_product)
    {
        global $db;

        require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

        $producto = new Product($db);

        $producto->fetch($fk_product);

        $dataService = Actions::dataService();

        if ($producto->description == "") {
            $descrip = $producto->label;
        } else {
            $descrip = $producto->description;
        }

        $Item = Item::create([
            "Name" => $producto->ref,
            "Description" => $descrip,
            "Active" => true,
            "FullyQualifiedName" => "Office Supplies",
            "Taxable" => true,
            "UnitPrice" => 25,
            "Type" => "Inventory",
            "IncomeAccountRef" => [
                "value" => 79,
                "name" => "Landscaping Services:Job Materials:Fountains and Garden Lighting"
            ],
            "PurchaseDesc" => "This is the purchasing description.",
            "PurchaseCost" => 35,
            "ExpenseAccountRef" => [
                "value" => 80,
                "name" => "Cost of Goods Sold"
            ],
            "AssetAccountRef" => [
                "value" => 81,
                "name" => "Inventory Asset"
            ],
            "TrackQtyOnHand" => true,
            "QtyOnHand" => 100,
            //"InvStartDate"=> $dateTime
            "InvStartDate" => "2023-02-15"
        ]);
        $resultingObj = $dataService->Add($Item);
        $error = $dataService->getLastError();
        if ($error) {
            //echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
            //echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
            //echo "The Response message is: " . $error->getResponseBody() . "\n";
            setEventMessages("The Status code is: ", [$error->getOAuthHelperError() . "\n"], 'errors');
            setEventMessages("The Helper message is: ", [$error->getOAuthHelperError()], 'errors');

            setEventMessages("The Response message is: ", [$error->getResponseBody() . "\n"], 'errors');
            //echo '<meta http-equiv=Refresh content="0;url=list.php?leftmenu=product&type=0';

        } else {
            //echo "Created Id={$resultingObj->Id}. Reconstructed response body:\n\n";

            $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
            //echo $xmlBody . "\n";
            setEventMessages("Created Id=" . $resultingObj->Id, ["Reconstructed response body:\n\n" . $xmlBody], 'warnings');
            //aqui va lo de  update extrafields
            $sqlBuscarCLienteEXtra = " SELECT * FROM " . MAIN_DB_PREFIX . "product_extrafields where fk_object = " . $fk_product;
            $resultUpdate = $db->query($sqlBuscarCLienteEXtra);
            $EXisteEXtrafield = $db->fetch_object($resultUpdate);

            if ($EXisteEXtrafield) {
                $sqlUpdate = " UPDATE  " . MAIN_DB_PREFIX . "product_extrafields  set quickbooks_id = " . $resultingObj->Id . " where fk_object =  " . $fk_product;
                $resultUpdate = $db->query($sqlUpdate);
                $db->commit();
                $db->begin();
            } else {
                $InsertExtrafield = " insert into " . MAIN_DB_PREFIX . "product_extrafields (fk_object, quickbooks_id ) values (" . $fk_product . "," . $resultingObj->Id . ");";
                $resultUpdate = $db->query($InsertExtrafield);
                $db->commit();
                $db->begin();

            }


        }

        return $resultingObj;
    }

    public static function InfoLog(string $message, $data = ''): void
    {
        error_log("$message " . print_r($data, true) . "\n", 3, 'debug.log');
    }

    private static function UpdateExtrafieldsQuickbooks($fk_object, $value, $element)
    {
        global $db;

        $sql = " SELECT * FROM " . MAIN_DB_PREFIX . " " . $element . "_extrafields WHERE fk_object = " . $fk_object . " ";
        $ExisteEXtraiflds = $db->query($sql);
        if (isset($ExisteEXtraiflds)) {
            $ExisteEXtraifldscount = $ExisteEXtraiflds->num_rows;
        } else {
            $ExisteEXtraifldscount = 0;
        }

        if ($ExisteEXtraifldscount == 0) {
            $sql = 'INSERT INTO ".MAIN_DB_PREFIX." ".$element."_extrafields (fk_object, quickbooks_id) VALUES (' . $fk_object . ', ' . $value . '  );  ';
            $db->query($sql);
        } else {
            $sql = "  update " . MAIN_DB_PREFIX . " " . $element . "_extrafields set quickbooks_id= " . $value . " where fk_object =" . $fk_object . " ";
            $db->query($sql);
        }
    }
}