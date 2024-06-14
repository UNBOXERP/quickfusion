<?php
require_once(__DIR__ . '/../inc/vendor/autoload.php');
require_once(__DIR__ . '/actions.php');

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Item;
use QuickBooksOnline\API\Facades\Account;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Payment;
use QuickBooksOnline\API\Facades\PaymentMethod;
use QuickBooksOnline\API\Facades\Vendor;
use QuickBooksOnline\API\Facades\Purchase;
use QuickBooksOnline\API\Facades\Bill;
use QuickBooksOnline\API\Facades\BillPayment;
use QuickBooksOnline\API\Facades\Deposit;
use QuickBooksOnline\API\Facades\Estimate;
use QuickBooksOnline\API\Facades\JournalEntry;
use QuickBooksOnline\API\Data\IPPCreditCardPaymentTxn;
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Facades\PurchaseOrder;
use QuickBooksOnline\API\Data\IPPPurchase;
use Dolibarr\QuickBooks\Actions;


class Syncqbooks
{

	public $dataService;

	public function __construct()
	{
		global $conf;

		$this->dataService = $this->configureDataService($conf);

	}

	private function configureDataService($conf)
	{
		return DataService::Configure([
			'auth_mode' => 'oauth2',
			'ClientID' => $conf->global->QUICKBOOKS_CLIENTID,
			'ClientSecret' => $conf->global->QUICKBOOKS_CLIENTSECRET,
			'RedirectURI' => $conf->global->QUICKBOOKS_OAUTHREDIRECT,
			'scope' => $conf->global->QUICKBOOKS_OAUTHSCOPE,
			'baseUrl' => "development"
		]);
	}

	public function updateFacturasQBooks()
	{
		$facturas = $this->getFacturasDolibarr();
		foreach ($facturas as $factura) {
			$factura->update();
		}
	}

	//get facturas
	public function getFacturasQBooks($idmax)
	{
		$facturas = array();
		$facturas = $this->getFacturasQbooksNoDolibarr($idmax);
		$facturas = $this->getFacturasSistema();
		return $facturas;
	}

	//get facturas qbooks
	public function getFacturasDolibarr()
	{
		global $conf, $db;
		$facturas = array();
		require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
		$facturasdoli = new Facture($db);
//		$facturasdoli->get
		//Obtener las facturas de dolibarr donde extrafields quickbooks_id sea null
		$sql = "Select rowid as id from " . MAIN_DB_PREFIX . "facture Where entity = '$conf->entity' and ref_client like 'QBOOKS%' and ref_client not like 'QBOOKS%-%'";
		return $facturas;
	}

	//update facturas qbooks
	public function updateFacturasDolibarr()
	{
		$facturas = array();
		$facturas = $this->getFacturasQbooks();
		foreach ($facturas as $factura) {
			$factura->update();
		}
	}

	private function getFacturasQbooksNoDolibarr($id)
	{
		global $conf, $db;


		$dataService = DataService::Configure(array(
			'auth_mode' => 'oauth2',
			'ClientID' => $conf->global->QUICKBOOKS_CLIENTID,
			'ClientSecret' => $conf->global->QUICKBOOKS_CLIENTSECRET,
			'RedirectURI' => $conf->global->QUICKBOOKS_CLIENTSECRET,
			'scope' => $conf->global->QUICKBOOKS_OAUTHSCOPE,
			'baseUrl' => "development"
		));

		/*
		 * Retrieve the accessToken value from session variable
		 */
		$accessToken = $_SESSION['sessionAccessToken'];
		if (!$accessToken) {

		}

		/*
		 * Update the OAuth2Token of the dataService object
		 */
		$dataService->updateOAuth2Token($accessToken);
		$i = 1;
		while (1) {

//        $result = $dataService->FindById("CreditCardPaymentTxn", "159");
//        $result = $dataService->Query("select * from Invoice where id = '148'");
			$result = $dataService->Query("select * from Invoice where id > '$id'");

			$error = $dataService->getLastError();
			if ($error) {
				echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
				echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
				echo "The Response message is: " . $error->getResponseBody() . "\n";
			} else {
				var_dump($result);
			}
			exit;
		}
	}

	public function CreaFacturasQbooks($facturas)
	{
        $invoicesLenght = count($facturas);
        if ($invoicesLenght > 1) {
            Actions::syncBatchInvoices($facturas);
        } else if ($invoicesLenght == 1) {
            Actions::syncSingleInvoice($facturas[0]);
        } else {
            setEventMessages("Debe seleccionar al menos una factura para sincronizar", [422], 'errors');
        }
	}

	public function CreaThirdparty($customers): void
    {
        if (!count($customers)) {
            setEventMessages("Debe seleccionar al menos un cliente para sincronizar", [422], 'errors');
        }

        Actions::syncCustomers($customers);
    }

	public function tokenrefresh()
	{
		$OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
		$authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();

		$_SESSION['authUrl'] = $authUrl;
		if (isset($_SESSION['sessionAccessToken'])) {

			$accessToken = $_SESSION['sessionAccessToken'];
			$accessTokenJson = array('token_type' => 'bearer',
				'access_token' => $accessToken->getAccessToken(),
				'refresh_token' => $accessToken->getRefreshToken(),
				'x_refresh_token_expires_in' => $accessToken->getRefreshTokenExpiresAt(),
				'expires_in' => $accessToken->getAccessTokenExpiresAt()
			);
			$this->dataService->updateOAuth2Token($accessToken);
			$oauthLoginHelper = $this->dataService->getOAuth2LoginHelper();
			$CompanyInfo = $this->dataService->getCompanyInfo();
		}
	}
		private	function CreaTokenNuevo()
		{

			session_start();

			$dataService = DataService::Configure(array(
				'auth_mode' => 'oauth2',
				'ClientID' => $config['client_id'],
				'ClientSecret' => $config['client_secret'],
				'RedirectURI' => $config['oauth_redirect_uri'],
				'scope' => $config['oauth_scope'],
				'baseUrl' => "development"
			));

			$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
			$authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();


// Store the url in PHP Session Object;
			$_SESSION['authUrl'] = $authUrl;

//set the access token using the auth object
			if (isset($_SESSION['sessionAccessToken'])) {

				$accessToken = $_SESSION['sessionAccessToken'];
				$accessTokenJson = array('token_type' => 'bearer',
					'access_token' => $accessToken->getAccessToken(),
					'refresh_token' => $accessToken->getRefreshToken(),
					'x_refresh_token_expires_in' => $accessToken->getRefreshTokenExpiresAt(),
					'expires_in' => $accessToken->getAccessTokenExpiresAt()
				);
				$dataService->updateOAuth2Token($accessToken);
				$oauthLoginHelper = $dataService->getOAuth2LoginHelper();
				$CompanyInfo = $dataService->getCompanyInfo();
			}

		}

		public function GetAuthURl()
		{
			$OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
			$authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
			$_SESSION['authUrl'] = $authUrl;
		}
		public function GetCustomer($factura){


			global $conf, $db;
			$lineas = array();
			$lineasarray = array();
//		$this->tokenrefresh();
			$dataService = DataService::Configure(array(
				'auth_mode' => 'oauth2',
				'ClientID' => $conf->global->QUICKBOOKS_CLIENTID,
				'ClientSecret' => $conf->global->QUICKBOOKS_CLIENTSECRET,
				'RedirectURI' => $conf->global->QUICKBOOKS_OAUTHREDIRECT,
				'scope' => $conf->global->QUICKBOOKS_OAUTHSCOPE,
				'baseUrl' => "development"
			));

			/*
			 * Retrieve the accessToken value from session variable
			 */
			$accessToken = $_SESSION['sessionAccessToken'];

			/*
			 * Update the OAuth2Token of the dataService object
			 */
			$dataService->updateOAuth2Token($accessToken);
			;
			//solo comentario  aqui factura es societe!!!  para tomarlo en cuenta
			$idQb=$factura->array_options["options_quickbooks_id"];
			//traer el customer


			$result = $dataService->Query("select * from Customer where id = '$idQb'");

			return $result;

		}

	public function CreaClienteQbooks($thirdparty)
	{
		global $conf, $db;
		$dataService = DataService::Configure(array(
			'auth_mode' => 'oauth2',
			'ClientID' => $conf->global->QUICKBOOKS_CLIENTID,
			'ClientSecret' => $conf->global->QUICKBOOKS_CLIENTSECRET,
			'RedirectURI' => $conf->global->QUICKBOOKS_OAUTHREDIRECT,
			'scope' => $conf->global->QUICKBOOKS_OAUTHSCOPE,
			'baseUrl' => "development"
		));

		/*
		 * Retrieve the accessToken value from session variable
		 */
		$accessToken = $_SESSION['sessionAccessToken'];

		/*
		 * Update the OAuth2Token of the dataService object
		 */
		$dataService->updateOAuth2Token($accessToken);
		//verifiaccion de datos

		if(!isset($thirdparty->note_public)) $thirdparty->note_public="";
		if(!isset($thirdparty->email)) $thirdparty->email="";
		if(!isset($thirdparty->phone)) $thirdparty->phone="";
		if(!isset($thirdparty->name_alias) || $thirdparty->name_alias =="") $thirdparty->name_alias=$thirdparty->name;
		$customerObj = Customer::create([
			"BillAddr" => [
				"Line1"=>  "$thirdparty->address",
				"City"=>  "$thirdparty->state",
				"Country"=>  "$thirdparty->country",
				"CountrySubDivisionCode"=>  "$thirdparty->state_code",
				"PostalCode"=>  "$thirdparty->zip"
			],
			"Notes" =>  "$thirdparty->note_public",
			"Title"=>  "",
			"GivenName"=>  "",
			"MiddleName"=>  "",
			"FamilyName"=>  "", //lastname
			"Suffix"=>  "",
			"FullyQualifiedName"=>  "$thirdparty->name",
			"CompanyName"=>  "$thirdparty->name",
			"DisplayName"=>  "$thirdparty->name",
			"PrimaryPhone"=>  [
				"FreeFormNumber"=>  "$thirdparty->phone"
			],
			"PrimaryEmailAddr"=>  [
				"Address" => "$thirdparty->email"
			]
		]);

		$resultingCustomerObj = $dataService->Add($customerObj);
		return $resultingCustomerObj;

	}


	public function UpdateExtrafieldsQuickbooks( $fk_object, $value, $element)
	{

		global $db;

		$sql = " SELECT * FROM " . MAIN_DB_PREFIX . " ".$element."_extrafields WHERE fk_object = " . $fk_object. " ";
		$ExisteEXtraiflds = $db->query($sql);
		if (isset($ExisteEXtraiflds)) {
			$ExisteEXtraifldscount = $ExisteEXtraiflds->num_rows;
		} else {
			$ExisteEXtraifldscount = 0;
		}

		if ($ExisteEXtraifldscount == 0) {
			$sql = 'INSERT INTO ".MAIN_DB_PREFIX." ".$element."_extrafields (fk_object, quickbooks_id) VALUES (' . $fk_object . ', ' . $value. '  );  ';
			$Actualizar = $db->query($sql);
		} else {

			$sql = "  update " . MAIN_DB_PREFIX . " ".$element."_extrafields set quickbooks_id= " . $value . " where fk_object =" . $fk_object . " ";
			$Actualizar = $db->query($sql);

		}


		$test = 0;
	}
	public function GetProduct($fk_product)
	{
		global $conf, $db;
		$dataService = DataService::Configure(array(
			'auth_mode' => 'oauth2',
			'ClientID' => $conf->global->QUICKBOOKS_CLIENTID,
			'ClientSecret' => $conf->global->QUICKBOOKS_CLIENTSECRET,
			'RedirectURI' => $conf->global->QUICKBOOKS_OAUTHREDIRECT,
			'scope' => $conf->global->QUICKBOOKS_OAUTHSCOPE,
			'baseUrl' => "development"
		));

		/*
		 * Retrieve the accessToken value from session variable
		 */
		$accessToken = $_SESSION['sessionAccessToken'];

		/*
		 * Update the OAuth2Token of the dataService object
		 */
		$dataService->updateOAuth2Token($accessToken);
		$result = $dataService->Query("select * from Item where Id = '".$fk_product."'");
		//$result1 = $dataService->Query("select * from Item where Id = '$fk_product'");

		return $result;
	}



	public function GetInvoice($InvoiceQB)
	{
		global $conf, $db;
		$dataService = DataService::Configure(array(
			'auth_mode' => 'oauth2',
			'ClientID' => $conf->global->QUICKBOOKS_CLIENTID,
			'ClientSecret' => $conf->global->QUICKBOOKS_CLIENTSECRET,
			'RedirectURI' => $conf->global->QUICKBOOKS_OAUTHREDIRECT,
			'scope' => $conf->global->QUICKBOOKS_OAUTHSCOPE,
			'baseUrl' => "development"
		));

		/*
		 * Retrieve the accessToken value from session variable
		 */
		$accessToken = $_SESSION['sessionAccessToken'];

		/*
		 * Update the OAuth2Token of the dataService object
		 */
		$dataService->updateOAuth2Token($accessToken);
		$result = $dataService->Query("select * from Invoice where id = '".$InvoiceQB."'");
		//$result1 = $dataService->Query("select * from Item where Id = '$fk_product'");

		return $result;
	}


	public function GetProductDOLIBARR($fk_product)
	{
		global $conf, $db;
		require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
		$objectP = new Product($db);
		$extrafields = new ExtraFields($db);

// fetch optionals attributes and labels
	$extrafields->fetch_name_optionals_label($objectP->table_element);

	if ($fk_product > 0)
		$result = $objectP->fetch($fk_product );

		return $objectP;
	}

	//------------------DOLIBARR------------------//
	public function CreateProductDolibarr($productoqb){
		global $conf, $db;
		$ref = $productoqb->Id;
		$label = $productoqb->Name;
		$description = $productoqb->Description;
		$price = $productoqb->UnitPrice;
		$price_base_type = $productoqb->UnitPrice;
		$price_ttc = $productoqb->UnitPrice;

	}

	/**
	 * Creates a product in QuickBooks.
	 *
	 * This function creates a product in QuickBooks using the QuickBooks API.
	 * It sets up the necessary DataService configuration, retrieves the access token,
	 * and creates a new Item with the provided product details.
	 *
	 * @param int $fk_product The ID of the product to be created in QuickBooks.
	 */
	public function CreaProductoQbooks($fk_product)
	{
		global $conf, $db;
		//obtener producto de dolibarr
		require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

		$producto = new Product($db);
		$producto->fetch($fk_product);
		$dataService = $this->configureDataService($conf);

		/*
		 * Retrieve the accessToken value from session variable
		 */
		$accessToken = $_SESSION['sessionAccessToken'];

		/*
		 * Update the OAuth2Token of the dataService object
		 */
		$dataService->updateOAuth2Token($accessToken);
		if($producto->description==""){
			$descrip=$producto->label;
		}else{
			$descrip=$producto->description;
		}

		$Item = Item::create([
			"Name" => $producto->ref,
			"Description" => $descrip,
			"Active" => true,
			"FullyQualifiedName" => "Office Supplies",
			"Taxable" => true,
			"UnitPrice" => 25,
			"Type" => "Inventory",
			"IncomeAccountRef"=> [
				"value"=> 79,
				"name" => "Landscaping Services:Job Materials:Fountains and Garden Lighting"
			],
			"PurchaseDesc"=> "This is the purchasing description.",
			"PurchaseCost"=> 35,
			"ExpenseAccountRef"=> [
				"value"=> 80,
				"name"=> "Cost of Goods Sold"
			],
			"AssetAccountRef"=> [
				"value"=> 81,
				"name"=> "Inventory Asset"
			],
			"TrackQtyOnHand" => true,
			"QtyOnHand"=> 100,
			//"InvStartDate"=> $dateTime
			"InvStartDate"=> "2023-02-15"
		]);
		$resultingObj = $dataService->Add($Item);
		$error = $dataService->getLastError();
		if ($error) {
			//echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
			//echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
			//echo "The Response message is: " . $error->getResponseBody() . "\n";
			setEventMessages("The Status code is: ", $error->getOAuthHelperError() . "\n", 'errors');
			setEventMessages("The Helper message is: ",  $error->getOAuthHelperError() ,'errors');

			setEventMessages("The Response message is: ", $error->getResponseBody() . "\n", 'errors');
			//echo '<meta http-equiv=Refresh content="0;url=list.php?leftmenu=product&type=0';

		}
		else {
			//echo "Created Id={$resultingObj->Id}. Reconstructed response body:\n\n";

			$xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
			//echo $xmlBody . "\n";
			setEventMessages("Created Id=".$resultingObj->Id, "Reconstructed response body:\n\n".$xmlBody, 'warnings');
			//aqui va lo de  update extrafields
			$sqlBuscarCLienteEXtra= " SELECT * FROM ".MAIN_DB_PREFIX."product_extrafields where fk_object = ".$fk_product;
			$resultUpdate = $db->query($sqlBuscarCLienteEXtra);
			$EXisteEXtrafield=$db->fetch_object($resultUpdate);

				if($EXisteEXtrafield){
					$sqlUpdate=" UPDATE  ".MAIN_DB_PREFIX."product_extrafields  set quickbooks_id = ".$resultingObj->Id." where fk_object =  ".$fk_product;
					$resultUpdate = $db->query($sqlUpdate);
					$db->commit();
					$db->begin();
				}else{
					$InsertExtrafield=" insert into ".MAIN_DB_PREFIX."product_extrafields (fk_object, quickbooks_id ) values (".$fk_product.",".$resultingObj->Id.");";
					$resultUpdate = $db->query($InsertExtrafield);
					$db->commit();
					$db->begin();

				}



		}

		return $resultingObj->Id;
	}


}
