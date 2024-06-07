<!--Este es el archivo que proceso la información que contiene el archivo CSV-->
<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       hmv/hmvindex.php
 *	\ingroup    hmv
 *	\brief      Home page of hmv top menu
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
//$tmp = empty($SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(FILE_); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
//while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
//if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
//if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res && file_exists("../../../../main.inc.php")) $res = @include "../../../../main.inc.php";
require_once DOL_DOCUMENT_ROOT.'/core/modules/commande/modules_commande.php';
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';

global $user, $db, $langs, $conf;
$id = GETPOST('id', 'alpha');

function getall($table, $filter= '1=1'  ) {

	global $db, $dolibarr_main_url_root;
	//$A=getProductNull($object);



	$sql  = " SELECT * FROM ".MAIN_DB_PREFIX."".$table;
	if($filter != '1=1' ) $sql .= " where ".$filter;

	$resqle = $db->query($sql);

	if ($resqle) {
		$num = $db->num_rows($resqle);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($resqle);
			$template[] = $obj;
			$i++;
		}
	}


	return $template;

}

//include('conexion.php');

if($_FILES["archivo"]["size"]>1000000){
    echo "Solo se permiten archivos menores de 1MB";
}else{
    // sacamos todas las propiedades del archivo
    $nombre_archivo = $_FILES['archivo']['name'];
    $tipo_archivo= $_FILES['archivo']['type'];
    $tamano_archivo = $_FILES["archivo"]['size'];
    $direccion_temporal = $_FILES['archivo']['tmp_name'];
    // movemos el archivo a la capeta de nuestro servidor
    move_uploaded_file($_FILES['archivo']['tmp_name'],"".$_FILES['archivo']['name']);
}






$gestor = fopen($nombre_archivo,"r");
$fila = 0;
$count = 0;
if ($gestor!== FALSE) {
    while (($data = fgetcsv($gestor,1000,"\n")) !== FALSE) {

		if($count == 1 ) {
        $num = count($data);
        //echo "<p> $num de campos en la línea $fila: <br /></p>\n";

        //datos completos
		$lineas = explode(',', $data[0]);

 		$campo1 = $lineas[0] ;
 		$campo2 = $lineas[1] ;
		$fila++;

		//print "campo1". $campo1 ."campo2". $campo2 ."campo3" . $campo3." <br>";

		//$sql2  =" INSERT INTO "  . MAIN_DB_PREFIX . "commandedet  (fk_commande, fk_product, qty) VALUES  ('". $campo1 ."' , '" . $campo2 ."', '".$campo3."' )";

		//$resql2 = $db->query($sql2);

		require_once DOL_DOCUMENT_ROOT.'/core/modules/commande/modules_commande.php';
		require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
		require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
		require_once DOL_DOCUMENT_ROOT.'/core/lib/order.lib.php';

	//$result = $object->updateline(GETPOST('lineid', 'int'), $description, $pu_ht, price2num(GETPOST('qty'), 'MS'), $remise_percent, $vat_rate, $localtax1_rate, $localtax2_rate, 'HT', $info_bits, $date_start, $date_end, $type, GETPOST('fk_parent_line'), 0, $fournprice, $buyingprice, $label, $special_code, $array_options, GETPOST('units'), $pu_ht_devise);
		$object = new Commande($db);
		$extrafields = new ExtraFields($db);

		$extrafields->fetch_name_optionals_label($object->table_element);
		$proid = $campo1;
		$ret = $object->fetch($id);

		$product = getall('product', "ref = '". $campo1."'") [0];


		//$result = $object->updateline(1, $proc->label, $proc->price, price2num($campo3, 'MS'),0, 0, null, null, 'HT', null, null, null, 0, 1, null, null, null, null, null, null, null, null);
		//if($result){ print 'guardado';}
		//else {print 'no guardado'; }


			/*$orderline = new OrderLine($db);
			//$orderline->ref_ext = (string)$line->id;
			$orderline->price = $line->price;
			$orderline->subprice = $line->base_price;
			$orderline->tva_tx = $line->total_tax;*/

			// $orderline->total_ht = $line->base_total;
			// $orderline->fk_product = $prodcreado->id;


			/*$productDolId=productDolId($line->product_id);
			$productDolId=$productDolId->rowid;
			if(!$prodcreado->fk_object) {
				$orderline->fk_product = $productDolId;
			} else {
				$orderline->fk_product = $productDolId;
			}*/
		/*$orderline->fk_product = $line->rowid;
			//$orderline->fk_product = $prodcreado->fk_object;
			$orderline->product_label = $line->name;
			$orderline->qty = $line->quantity;
			// $orderline->total_ht = $line->total_ex_tax;
			// $orderline->total_ttc = $line->total_inc_tax;
			// $orderline->total_tva = $line->total_tax;
			// $orderline->remise_percent = $line->total_tax;
			$object->lines[] = $orderline;*/
		    //$result = $orderline->insert();

		$result = $object->addline(0, $product->cost_price, $campo2, 0, 0, 0, $product->rowid, 0, 0, 0, $product->price_base_type, 0, 0, 0, 0, - 1, 0, 0, 0, $product->price, $product->label, 0, $product->fk_unit, '', 0, 0);

		//$value="add";
		//$object->update($object->id, $user);
		$db->commit();
		$db->begin();

	//	print '<pre>';
		//print_r ($proc);
     //   echo '<script type="text/javascript">alert("El Archivo fue procesado y los datos Registrados");</script>';
       // echo '<script type="text/javascript">window.location="importar.php";</script>';
    }
		$count = 1;
	}
    fclose($gestor);
}
echo '<script type="text/javascript">window.location="../../commande/card.php?id=' . $id . '";</script>';
?>
