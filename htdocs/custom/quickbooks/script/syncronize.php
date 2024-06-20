<?php

/* Copyright (C) 2017-2019 Regis Houssin  <regis.houssin@inodbox.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */
// Load Dolibarr environment

require_once(__DIR__ . '/../inc/vendor/autoload.php');

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Data\IPPCreditCardPaymentTxn;

use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Purchase;
use QuickBooksOnline\API\Facades\PurchaseOrder;
use QuickBooksOnline\API\Data\IPPPurchase;


class TablasListados
{


	public function mostrarbanking()
	{
		require_once DOL_DOCUMENT_ROOT . '/custom/reconciliation/class/leger.php';

		global $db, $conf;
		$sync = new Legger($db);
		$datos = new stdClass();

		$datos = $sync->outstanding();
		if (count((array)$datos) == 0) {
			echo '{"data": []}';
			return;
		}

		$datosJson = '{
		  "data": [';
		$i = 0;
		foreach ($datos as $item) {

			$datosJson .= '[
			      "' . ($i + 1) . '",
			      "' . $item->datec . '",
			      "' . $item->num_chq . '",
			      "' . $item->label . '",
			      "' . $item->amount . '"
			    ],';
			$i += 1;

		}


		$datosJson = substr($datosJson, 0, -1);

		$datosJson .= ']

		 }';

		echo $datosJson;

	}

	public function mostrarledger()
	{
		require_once DOL_DOCUMENT_ROOT . '/custom/reconciliation/class/leger.php';

		global $db, $conf;
		$sync = new Legger($db);
		$datos = new stdClass();
		$fechaini = $_REQUEST["startdate"];
		$fechafin = $_REQUEST["enddate"];
		$datos = $sync->ledger($fechaini, $fechafin);
		if (count((array)$datos) == 0) {
			echo '{"data": []}';
			return;
		}

		$datosJson = '{
		  "data": [';
		$i = 0;
		foreach ($datos as $item) {

			$datosJson .= '[
			      "' . ($i + 1) . '",
			      "' . $item->piece_num . '",
			      "' . $item->journal_label . '",
			      "' . $item->numero_compte . '",
			      "' . $item->label_operation . '",
			      "' . $item->debit . '"
			    ],';
			$i += 1;

		}


		$datosJson = substr($datosJson, 0, -1);

		$datosJson .= ']

		 }';

		echo $datosJson;

	}

	public function mostrardeposit()
	{
		require_once DOL_DOCUMENT_ROOT . '/custom/reconciliation/class/leger.php';

		global $db, $conf;
		$sync = new Legger($db);
		$datos = new stdClass();

		$datos = $sync->deposit();
		if (count((array)$datos) == 0) {
			echo '{"data": []}';
			return;
		}

		$datosJson = '{
		  "data": [';
		$i = 0;
		foreach ($datos as $item) {

			$datosJson .= '[
			      "' . ($i + 1) . '",
			      "' . $item->datec . '",
			      "' . $item->num_chq . '",
			      "' . $item->label . '",
			      "' . $item->amount . '"
			    ],';
			$i += 1;

		}


		$datosJson = substr($datosJson, 0, -1);

		$datosJson .= ']

		 }';

		echo $datosJson;

	}

}

/**
 * Define all constants needed for ajax request
 */
if (!defined('NOTOKENRENEWAL')) {
	define('NOTOKENRENEWAL', 1);
} // Disables token renewal
if (!defined('NOREQUIREMENU')) {
	define('NOREQUIREMENU', '1');
}
if (!defined('NOREQUIREHTML')) {
	define('NOREQUIREHTML', '1');
}
if (!defined('NOREQUIREAJAX')) {
	define('NOREQUIREAJAX', '1');
}
if (!defined('NOREQUIRESOC')) {
	define('NOREQUIRESOC', '1');
}
if (!defined('NOCSRFCHECK')) {
	define('NOCSRFCHECK', '1');
}
if (empty($_GET ['keysearch']) && !defined('NOREQUIREHTML')) {
	define('NOREQUIREHTML', '1');
}

$res = 0;
require_once '../../../main.inc.php';

global $db;

header("HTTP/1.1 200 OK");

$action = GETPOST('action', 'alpha');
$ledger = GETPOST('ledger');
$description = GETPOST('description');
$date = GETPOST('date');
$id = GETPOST('id');
$fecha = strtotime($date);
$fecha = $db->idate($fecha);

global $langs, $conf, $db;

/**
 * @return void
 * @throws \QuickBooksOnline\API\Exception\SdkException
 */
function InicializaToken()
{
	if (!isset($_SESSION['sessionAccessToken'])) {
		session_start();
		require_once DOL_DOCUMENT_ROOT . '/custom/quickbooks/class/sync.php';
		$sync = new Syncqbooks();
		$sync->GetAuthURl();
		if (isset($_SESSION['sessionAccessToken'])) {

			$accessToken = $_SESSION['sessionAccessToken'];
			$accessTokenJson = array('token_type' => 'bearer',
				'access_token' => $accessToken->getAccessToken(),
				'refresh_token' => $accessToken->getRefreshToken(),
				'x_refresh_token_expires_in' => $accessToken->getRefreshTokenExpiresAt(),
				'expires_in' => $accessToken->getAccessTokenExpiresAt()
			);
			$sync->dataService->updateOAuth2Token($accessToken);
			$oauthLoginHelper = $sync->dataService->getOAuth2LoginHelper();
			$CompanyInfo = $sync->dataService->getCompanyInfo();
		}
		$OAuth2LoginHelper = $sync->dataService->getOAuth2LoginHelper();
		$authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
		$_SESSION['authUrl'] = $authUrl;
		return $sync;

	}
}

switch ($action) {
	case 'syncronize':
		require_once DOL_DOCUMENT_ROOT . '/custom/quickbooks/class/sync.php';
		$sync = new Syncqbooks();
		$facturas = array();
		require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
		foreach ($_POST['ids'] as $id) {
			$facture = new Facture($db);
			$facture->fetch($id);
			$facture->fetch_thirdparty();
			$facturas[] = $facture;
		}
		if(1==1){
			if (!empty($facturas)) $sync->CreaFacturasQbooks($facturas);

		}
	break;
    case 'syncronizesociete':
        require_once DOL_DOCUMENT_ROOT . '/custom/quickbooks/class/sync.php';
        $sync = new Syncqbooks();
        $customers = $_POST['ids'];
        if (!empty($customers)) $sync->CreaThirdparty($customers);
    break;

	case 'syncronizepurchase':
		require_once DOL_DOCUMENT_ROOT . '/custom/quickbooks/class/sync.php';
		$sync = new Syncqbooks();
		$facturas = array();
		require_once DOL_DOCUMENT_ROOT . '/compta/fourn/class/fournisseur.facture.class.php';
		foreach ($_POST['ids'] as $id) {
			$facture = new FactureFournisseur($db);
			$facture->fetch($id);
			$facture->fetch_thirdparty();

			$facturas[] = $facture;
		}
		if (!empty($facturas)) $sync->CreaFacturasQbooks($facturas);
		break;
	case 'syncronizepurchaseorder':
		require_once DOL_DOCUMENT_ROOT . '/custom/quickbooks/class/sync.php';
		$sync = new Syncqbooks();
		$facturas = array();
		require_once DOL_DOCUMENT_ROOT . '/compta/fourn/class/fournisseur.commande.class.php';
		foreach ($_POST['ids'] as $id) {
			$facture = new CommandeFournisseur($db);
			$facture->fetch($id);
			$facture->fetch_thirdparty();
			$facturas[] = $facture;
		}
		if (!empty($facturas)) $sync->CreaFacturasQbooks($facturas);
		break;

	case 'syncronizeorder':
		require_once DOL_DOCUMENT_ROOT . '/custom/quickbooks/class/sync.php';
		$sync = new Syncqbooks();
		$facturas = array();
		require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
		foreach ($_POST['ids'] as $id) {
			$facture = new Commande($db);
			$facture->fetch($id);
			$facture->fetch_thirdparty();
			$facturas[] = $facture;
		}
		if (!empty($facturas)) $sync->CreaFacturasQbooks($facturas);
		break;
	case 'syncronizeinvoice':
		require_once DOL_DOCUMENT_ROOT . '/custom/quickbooks/class/sync.php';
		$sync = new Syncqbooks();
		$facturas = array();
		require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
		foreach ($_POST['ids'] as $id) {
			$facture = new Facture($db);
			$facture->fetch($id);
			$facture->fetch_thirdparty();
			$facturas[] = $facture;
		}
		if (!empty($facturas)) $sync->CreaFacturasQbooks($facturas);
		break;
	case 'syncronizecreditnote':
		require_once DOL_DOCUMENT_ROOT . '/custom/quickbooks/class/sync.php';
		$sync = new Syncqbooks();
		$facturas = array();
		require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
		foreach ($_POST['ids'] as $id) {
			$facture = new Facture($db);
			$facture->fetch($id);
			$facture->fetch_thirdparty();
			$facturas[] = $facture;
		}
		if (!empty($facturas)) $sync->CreaFacturasQbooks($facturas);
		break;
	case 'syncronizepayment':
		require_once DOL_DOCUMENT_ROOT . '/custom/quickbooks/class/sync.php';
		$sync = new Syncqbooks();
		$facturas = array();
		require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
		foreach ($_POST['ids'] as $id) {
			$facture = new Facture($db);
			$facture->fetch($id);
			$facture->fetch_thirdparty();
			$facturas[] = $facture;
		}
		if (!empty($facturas)) $sync->CreaFacturasQbooks($facturas);
		break;
	case 'syncronizeproduct':
		require_once DOL_DOCUMENT_ROOT . '/custom/quickbooks/class/sync.php';

		//aquihay q meter array de productos
		$ids = GETPOST('ids');
		if($ids){
			$numProd=count($ids);
			for($a=0;$a<$numProd;$a++){
					$idActual=$ids[$a];
				$sync = new Syncqbooks();
				//$sync->UpdateProducts(); //razmi
				$resultProduct=$sync->CreaProductoQbooks($idActual); //alan
			}
		}
		//refrescar pantalla
		$test=0;
		//echo '<meta http-equiv=Refresh content="0;url=card.php?id='. $object->id. '" ';
		//echo '<meta http-equiv=Refresh content="0;url=list.php?leftmenu=product&type=0';
		//$sync = new Syncqbooks();
		//$sync->UpdateProducts(); //razmi

		//$sync->CreaProductoQbooks(); //alan
		$resultado = json_encode(array('result' => 'ok'));

		// Envía la respuesta JSON
				header('Content-Type: application/json');
				echo $resultado;
  ?>
		<script>
			// Redirecciona utilizando JavaScript después de enviar la respuesta JSON
			window.location.href = 'list.php?leftmenu=product&type=0';

		</script>


<?php
		exit();
	$test=0;
		break;
	case 'syncronizecustomer':
		require_once DOL_DOCUMENT_ROOT . '/custom/quickbooks/class/sync.php';
		$sync = new Syncqbooks();
		$sync->UpdateCustomers();
		break;
	case 'syncronizevendor':
		require_once DOL_DOCUMENT_ROOT . '/custom/quickbooks/class/sync.php';
		$sync = new Syncqbooks();
		$sync->UpdateVendors();
		break;
	case 'syncronizebanking':
		require_once DOL_DOCUMENT_ROOT . '/custom/quickbooks/class/sync.php';
		$sync = new Syncqbooks();
		$sync->UpdateBanking();
		break;
		case 'syncronizeoneproduct':
		require_once DOL_DOCUMENT_ROOT . '/custom/quickbooks/class/sync.php';
		$sync = new Syncqbooks();
		$sync->UpdateOneProduct($id);
		break;


	case 'listadotabladeposit':
		$activarProductos = new TablasListados();
		$activarProductos->mostrardeposit();
		break;
	case 'listadotablaledger':
		$activarProductos = new TablasListados();
		$activarProductos->mostrarledger();
		break;


}
// Envía la respuesta JSON
//$resultado = json_encode(array('result' => 'ok'));

