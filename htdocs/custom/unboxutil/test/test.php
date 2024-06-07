<?php
// Load Dolibarr environment
include '../../../main.inc.php';

require_once(DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php');
require_once(DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php');
require_once(DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php');
require_once(DOL_DOCUMENT_ROOT . '/compta/paiement/class/paiement.class.php');
require_once(DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php');
require_once(DOL_DOCUMENT_ROOT . '/core/lib/functionsnumtoword.lib.php');
require_once(DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php');
require_once DOL_DOCUMENT_ROOT . '/custom/unboxutil/class/factura.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/discountrules/class/discountSearch.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/discountrules/class/discountSearch.class.php';
global $db;
//Para Pagos bancos
/*$pago=new factura($db);
$pago->fetch(30);

//$pago->CreatePayment($pago,6);
//currencys to words for banks
global $langs,$conf;
$currency = $conf->currency;
$translateinletter = strtoupper(dol_convertToWord(24.5, $langs, $currency));
print $translateinletter;*/

//para probar descuentos

$descuento=new DiscountRule($db);
//$descuentosearch=new DiscountSearch($db);
////$discountSearchResult = $discountSearch->search($line->qty, $line->fk_product, $object->socid, $object->fk_project);
//$descuentos = $descuentosearch->search(4000, 0, 0, 0);
//$resultadodescuento=$descuento->fetchByCrit(4000,0,0,0,0,0,0,0,0);
$factura=new Facture($db);
$factura->fetch(22);
$resultadodescuento=$descuento->fetchByOrder($factura);
foreach ($resultadodescuento as $desc) {

}




