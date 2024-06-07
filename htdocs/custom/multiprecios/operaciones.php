<?php
require '../../main.inc.php';


$idproducto = $_POST["id"];
$idorden = $_POST["orden"];


function getorder($id){
	global $db;

	$cat_prod = "select  *";
	$cat_prod .= " from " . MAIN_DB_PREFIX . "commande as c ";
	$cat_prod .= " left join " . MAIN_DB_PREFIX . "commande_extrafields as cf on c.rowid = cf.fk_object ";
	$cat_prod .= " where c.rowid =  ".$id." ";
	$cat_prod .= " ORDER BY  cf.rowid  DESC ";

	$total_cat = $db->query($cat_prod);

	if ($total_cat) {
		$num = $db->num_rows($total_cat);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($total_cat);
			$ordenescompra[] = $obj;
			$i++;
		}
	}

	return $ordenescompra[0];
}
function getprice($PROD, $nivel){
	global $db;


	$cat_prod .= " select * from ".MAIN_DB_PREFIX."product_price lpp ";
	$cat_prod .= " where fk_product  = ".$PROD."  and price_level  = ".$nivel." ";
	$cat_prod .= " order by date_price  DESC ";


	$total_cat = $db->query($cat_prod);

	if ($total_cat) {
		$num = $db->num_rows($total_cat);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($total_cat);
			$ordenescompra[] = $obj;
			$i++;
		}
	}

	return $ordenescompra[0];
}


$orden= getorder($idorden);

if($orden->tipoorden=='A') $TIPO=1;
if($orden->tipoorden=='B') $TIPO=2;
if($orden->tipoorden=='C') $TIPO=3;
if($orden->tipoorden=='D') $TIPO=4;
if($orden->tipoorden=='E') $TIPO=5;
$preciossss= getprice($idproducto, $TIPO);
//$valorPrecio=  price2num($precios->subprice);
$valorPrecio= null;
$Precio1=  price2num($preciossss->price);

$identificador=0;


$result  = array("valor" => $Precio1,

);
echo json_encode($result);
