<?php
require_once(__DIR__ . '/../inc/vendor/autoload.php');

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
		global $conf;

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
		global $conf, $db;
		$lineas = array();
		$lineasarray = array();
//		$this->tokenrefresh();
		$dataService = $this->configureDataService($conf);

		/*
		 * Retrieve the accessToken value from session variable
		 */
		$accessToken = $_SESSION['sessionAccessToken'];

		/*
		 * Update the OAuth2Token of the dataService object
		 */
		$dataService->updateOAuth2Token($accessToken);
	;
		foreach ($facturas as $factura) {
			$clientes=$this->GetCustomer($factura->thirdparty);
			if ($clientes[0]->Id == null || $clientes[0]->Id == "") {
				$cliente = $this->CreaClienteQbooks($factura->thirdparty);
			} else {
				$cliente = $clientes;
			}
			foreach ($factura->lines as $line) {
                $productos=$this->GetProduct($line->fk_product);
				if ($productos[0]->Id == null || $productos[0]->Id == "") {
					$producto = $this->CreaProductoQbooks($line->fk_product);
				} else {
					$producto = $productos;
				}
				$ClienteId=(string)$cliente[0]->Id;
				$ClienteName=$cliente[0]->DisplayName;
				$lineasarray[] = array(
					"Description" => $line->desc,
					"Amount" => $line->total_ht,
					"LineNum" => $line->rang,
					"DetailType" => "SalesItemLineDetail",
					"SalesItemLineDetail" => array(
						"ItemRef" => array(
							"value" => $line->fk_product,
							"name" => $line->label
						),
						"TaxCodeRef" => array(
							"value" => "NON"
						),
						"Qty" => $line->qty,
						"UnitPrice" => $line->subprice,
					)
				);
			}
				$facturaarray = array(
					"DocNumber" => $factura->ref,
					"DueDate" => $db->idate($factura->date_lim_reglement),
					"Balance" => $factura->total_ttc - $factura->paiement,
					"Line" =>
						$lineasarray
				,
					"CustomerRef" => array(
						"value" => "$ClienteId",
						"name" => "$ClienteName"
					)
				);

				$theResourceObj = Invoice::create($facturaarray);
				$resultingObj = $dataService->Add($theResourceObj);
				$error = $this->dataService->getLastError();
			}

			if ($error) {
				echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
				echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
				echo "The Response message is: " . $error->getResponseBody() . "\n";
			} else {
				echo "Created Id={$resultingObj->Id}. Reconstructed response body:\n\n";
				$xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
				echo $xmlBody . "\n";
			}
			$test="['Invoice' => [ 'TxnDate' => '2014-09-19',
    'domain' => 'QBO',
    'PrintStatus' => 'NeedToPrint',
    'SalesTermRef' => [
      'value' => '3',
    ],
    'TotalAmt' => 362.07,
    'Line' => [
      0 => [
        'Description' => 'Rock Fountain',
        'DetailType' => 'SalesItemLineDetail',
        'SalesItemLineDetail' => [
          'TaxCodeRef' => [
            'value' => 'TAX',
          ],
          'Qty' => 1,
          'UnitPrice' => 275,
          'ItemRef' => [
            'name' => 'Rock Fountain',
            'value' => '5',
          ],
        ],
        'LineNum' => 1,
        'Amount' => 275.0,
        'Id' => '1',
      ],
      1 => [
        'Description' => 'Fountain Pump',
        'DetailType' => 'SalesItemLineDetail',
        'SalesItemLineDetail' => [
          'TaxCodeRef' => [
            'value' => 'TAX',
          ],
          'Qty' => 1,
          'UnitPrice' => 12.75,
          'ItemRef' => [
            'name' => 'Pump',
            'value' => '11',
          ],
        ],
        'LineNum' => 2,
        'Amount' => 12.75,
        'Id' => '2',
      ],
      2 => [
        'Description' => 'Concrete for fountain installation',
        'DetailType' => 'SalesItemLineDetail',
        'SalesItemLineDetail' => [
          'TaxCodeRef' => [
            'value' => 'TAX',
          ],
          'Qty' => 5,
          'UnitPrice' => 9.5,
          'ItemRef' => [
            'name' => 'Concrete',
            'value' => '3',
          ],
        ],
        'LineNum' => 3,
        'Amount' => 47.5,
        'Id' => '3',
      ],
      3 => [
        'DetailType' => 'SubTotalLineDetail',
        'Amount' => 335.25,
        'SubTotalLineDetail' => [
        ],
      ],
    ],
    'DueDate' => '2014-10-19',
    'ApplyTaxAfterDiscount' => false,
    'DocNumber' => '1037',
    'sparse' => false,
    'CustomerMemo' => [
      'value' => 'Thank you for your business and have a great day!',
    ],
    'Deposit' => 0,
    'Balance' => 362.07,
    'CustomerRef' => [
      'name' => 'Sonnenschein Family Store',
      'value' => '24',
    ],
    'TxnTaxDetail' => [
      'TxnTaxCodeRef' => [
        'value' => '2',
      ],
      'TotalTax' => 26.82,
      'TaxLine' => [
        0 => [
          'DetailType' => 'TaxLineDetail',
          'Amount' => 26.82,
          'TaxLineDetail' => [
            'NetAmountTaxable' => 335.25,
            'TaxPercent' => 8,
            'TaxRateRef' => [
              'value' => '3',
            ],
            'PercentBased' => true,
          ],
        ],
      ],
    ],
    'SyncToken' => '0',
    'LinkedTxn' => [
      0 => [
        'TxnId' => '100',
        'TxnType' => 'Estimate',
      ],
    ],
    'BillEmail' => [
      'Address' => 'Familiystore@intuit.com',
    ],
    'ShipAddr' => [
      'City' => 'Middlefield',
      'Line1' => '5647 Cypress Hill Ave.',
      'PostalCode' => '94303',
      'Lat' => '37.4238562',
      'Long' => '-122.1141681',
      'CountrySubDivisionCode' => 'CA',
      'Id' => '25',
    ],
    'EmailStatus' => 'NotSet',
    'BillAddr' => [
      'Line4' => 'Middlefield, CA  94303',
      'Line3' => '5647 Cypress Hill Ave.',
      'Line2' => 'Sonnenschein Family Store',
      'Line1' => 'Russ Sonnenschein',
      'Long' => '-122.1141681',
      'Lat' => '37.4238562',
      'Id' => '95',
    ],
    'MetaData' => [
      'CreateTime' => '2014-09-19T13:16:17-07:00',
      'LastUpdatedTime' => '2014-09-19T13:16:17-07:00',
    ],
    'CustomField' => [
      0 => [
        'DefinitionId' => '1',
        'StringValue' => '102',
        'Type' => 'StringType',
        'Name' => 'Crew #',
      ],
    ],
    'Id' => '130',
  ],
  'time' => '2015-07-24T10:48:27.082-07:00',
]";

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
			$result = $dataService->Query("select * from Customer where id = '$factura->id'");

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
			"GivenName"=>  "$thirdparty->name",
			"MiddleName"=>  "",
			"FamilyName"=>  "",
			"Suffix"=>  "",
			"FullyQualifiedName"=>  "$thirdparty->name",
			"CompanyName"=>  "$thirdparty->name_alias",
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
		$result = $dataService->Query("select * from Item where Id = '$fk_product'");

		return $result;
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
		$Item = Item::create([
			"Name" => "1111Office Sudfsfasdfpplies",
			"Description" => "This is the sales description.",
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
			"InvStartDate"=> $dateTime
		]);
		$resultingObj = $dataService->Add($Item);
		$error = $dataService->getLastError();
		if ($error) {
			echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
			echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
			echo "The Response message is: " . $error->getResponseBody() . "\n";
		}
		else {
			echo "Created Id={$resultingObj->Id}. Reconstructed response body:\n\n";
			$xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
			echo $xmlBody . "\n";
		}


	}


}
