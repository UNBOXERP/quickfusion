<?php
/* Copyright (C) 2012-2015 Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2014-2017 Ferran Marcet        <fmarcet@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

function discountsadmin_prepare_head()
{
	global $langs, $conf, $user;
	$langs->load("discounts@discounts");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath('/discounts/admin/discounts.php',1);
	$head[$h][1] = $langs->trans("DiscountsSetup");
	$head[$h][2] = 'configuration';
	$h++;

	return $head;
}

function calcul_discount($object, $socid,$action,$type_doc){
	global $db, $user, $conf, $mysoc;

	$objDto=new Discounts($db);
	$fk = 0;

	if($object->element=='facturedet'){
		$fk = $object->fk_facture;
	}
	elseif($object->element=='propaldet'){
		$fk = $object->fk_propal;
	}
	elseif($object->element=='commandedet'){
		$fk = $object->fk_commande;
	}

	$result=$objDto->fetch_all_calcul($conf->global->DIS_APPLY,$socid,$object->fk_product,$fk);

	$dis_third = new Discount_third($db);
	$dis_third->fetch('',$socid);

	$i=0;
	$priority = 0;
	if (is_array($result))
	{
		$num = count($result);
		$precio_unit = $object->subprice;
		$remise = $object->remise_percent;
		//$tasa_cambio = $object->multicurrency_subprice/$object->subprice;
		$desc_of_applied_dtos[]="";

		// 2nd Unit Percent Discount
		if(in_array('5', array_column($result, 'type_dto')) && $object->qty > 1) {
			$dto_rate = 0;
			for($n=0; $n<$num;$n++){
				if ($result[$n]['type_dto'] == 5){
					$dto_rate += $result[$n]['dto_rate']/100;
					$desc_of_applied_dtos[$n] = $result[$n]['desc'];
				}
			}
			$en_promo = (int)($object->qty / 2);
			//$precio_unit = ($object->subprice * ($dto_rate) * $en_promo + ($object->qty - $en_promo) * $object->subprice) / $object->qty;
			$discount['2unitdiscount'] = $object->subprice * ($dto_rate) * $en_promo;
		}

		while ($i < $num) {
			// X x Y Promo
			if ($result[$i]['type_dto'] == 4 && $object->qty >= $result[$i]['qtybuy']) {
				$en_promo = (int)($object->qty / $result[$i]['qtybuy']);
				//$precio_unit = ((/*$result[$i]['qtybuy'] -*/
				//		$result[$i]['qtypay']) * $en_promo + ($object->qty % $result[$i]['qtybuy'])) * $object->subprice / $object->qty;
				$desc_of_applied_dtos[$i] = $result[$i]['desc'];

				//$discount['xypromo'] = $object->subprice * $en_promo;
				$discount['xypromo'] = ($object->subprice * ($result[$i]['qtybuy']-$result[$i]['qtypay']) * ($en_promo));
			}
			// Comercial Percent Discount
			else {
				if ($result[$i]['type_dto'] == 1 && $object->qty > 0) {
					if ($dis_third->show_dis == $dis_third::REMISE || (empty($dis_third->show_dis) && $conf->global->DISCOUNT_SHOW == $dis_third::REMISE)) {
						if(empty($_POST['origin']) && dol_substr($action,-6,6) == 'INSERT') {
							$object->remise_percent += $result[$i]['dto_rate'];
							$desc_of_applied_dtos[$i] = $result[$i]['desc'];
						}
					} else {
						//$precio_unit = $precio_unit - (($precio_unit * $result[$i]['dto_rate']) / 100);
						$discount['comercial'] = ($object->subprice * $object->qty) * ($result[$i]['dto_rate'] / 100) ;
						$desc_of_applied_dtos[$i] = $result[$i]['desc'];
					}
				}
				else{
					if($result[$i]['type_dto'] == 5 && $object->qty > 1){
						if ($dis_third->show_dis == $dis_third::REMISE || (empty($dis_third->show_dis) && $conf->global->DISCOUNT_SHOW == $dis_third::REMISE)) {
							if(empty($_POST['origin']) && dol_substr($action,-6,6) == 'UPDATE') {
								$object->remise_percent += $result[$i]['dto_rate'];
								$desc_of_applied_dtos[$i] = $result[$i]['desc'];
							}
						}
					}
				}
			}

			if ($conf->global->DIS_APPLY == 3 && ($precio_unit != $object->subprice || $object->remise_percent != $remise) && $priority == 0) {
				$priority = $result[$i]['priority'];
			}
			$i++;
			if ($conf->global->DIS_APPLY == 3 && ($precio_unit != $object->subprice || $object->remise_percent != $remise) && $priority != $result[$i]['priority']) {
				break;
			}

		}

		$precio_unit = (($object->subprice * $object->qty) - $discount['2unitdiscount'] - $discount['xypromo'] - $discount['comercial'])/$object->qty;

		if($precio_unit != $object->subprice || $object->remise_percent != $remise){
			global $langs;

			if ($conf->global->DIS_APPLY != 3) {
				for ($g = 0; $g < count($result); $g++) {
					if ($object->libelle == null) {
						$product = new Product($db);
						$product->fetch($object->fk_product);
						$object->libelle = $product->label;
					}

					if ($result[$g]['type_dto'] == "4" && $object->qty >= $result[$g]['qtybuy']) {
						$object->libelle .= '&nbsp;&nbsp;<b>- ' . $langs->trans('Discount') . ' ' . $result[$g]['desc'];
						//$object->libelle .= ' (' . $langs->trans('type_dto4') . ')</b>';
						$object->libelle .= ' (' . $result[$g]['qtybuy'] . ' x ' . $result[$g]['qtypay'] . ')</b>';
					} elseif ($result[$g]['type_dto'] == "5" && $object->qty > 1) {
						$object->libelle .= '&nbsp;&nbsp;<b>- ' . $langs->trans('Discount') . ' ' . $result[$g]['desc'];
						$object->libelle .= ' (' . $langs->trans('type_dto5') . ')</b>';
					} elseif ($result[$g]['type_dto'] == "1") {
						$object->libelle .= '&nbsp;&nbsp;<b>- ' . $langs->trans('Discount') . ' ' . $result[$g]['desc'];
						$object->libelle .= ' (' . $langs->trans('type_dto1') . ')</b>';
					}
				}
			}
			$object->label = $object->libelle;

			$dis_doc = new Discounts_doc($db);
			$dis_doc->type_doc = $type_doc;
			$dis_doc->fk_doc = $object->rowid;
			$exist = $dis_doc->fetch($type_doc, $object->rowid);
			$dis_doc->ori_subprice = $object->subprice;
			$dis_doc->ori_totalht = $object->total_ht;
			$dis_doc->descr = "";
			foreach ($desc_of_applied_dtos as $value){
				$dis_doc->descr .= "<dd><var>".$value."</var></dd>";
			}
			$dis_doc->descr .= "</dl>";

			if($exist > 0){
				$dis_doc->update($user);
			}
			else{
				$dis_doc->create($user);
			}

			// Local Taxes para solventar bug en Dolibarr
			if ($action == 'LINEORDER_INSERT') {
				require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
				$thirdparty = new Societe($db);
				$thirdparty->fetch($socid);
				$object->localtax1_tx = get_localtax($object->tva_tx, 1, $thirdparty, $mysoc);
				$object->localtax2_tx = get_localtax($object->tva_tx, 2, $thirdparty, $mysoc);
			}

			$localtaxes_type=array($object->localtax1_type,$object->localtax1_tx,$object->localtax2_type,$object->localtax2_tx);
			$result = calcul_price_total($object->qty, $precio_unit, $object->remise_percent, $object->tva_tx, $object->localtax1_tx, $object->localtax2_tx, 0, 'HT', 0, $object->product_type, '', $localtaxes_type);

			$object->total_ht = $result[0];
			$object->total_tva = $result[1];
			$object->total_localtax1 = $result[9];
			$object->total_localtax2 = $result[10];
			$object->total_ttc = $result[2];
			$object->subprice = $result[3];

			/*$object->multicurrency_total_ht  = $result[16];
			$object->multicurrency_total_tva = $result[17];
			$object->multicurrency_total_ttc = $result[18];
			$object->pu_ht_devise = $result[19];*/

			if (version_compare(DOL_VERSION, "5.0") >= 0 ){
				if ($action === 'LINEPROPAL_INSERT' || $action === 'LINEPROPAL_UPDATE') {
					$res = $object->update(1);
				} else {
					$res = $object->update($user,1);
				}
			}
			else {
				if ($action === 'LINEBILL_INSERT' || $action === 'LINEBILL_UPDATE') {
					$res = $object->update($user, 1);
				} else {
					$res = $object->update(1);
				}
			}
			return $res;
		}
		return 0;
	}
	return 0;
}

function calcul_discount_pos($product,$precio_unit=0){
	global $conf, $db;

	// Sacar precio original directamente del producto para no duplicar el descuento de 2promo
	if ($conf->global->PRODUIT_MULTIPRICES) {
		$sql = "SELECT price_level";
		$sql .= " FROM " . MAIN_DB_PREFIX . "societe";
		$sql .= " WHERE rowid = " . $product['socid'];
		$res = $db->query($sql);
		if ($res) {
			$obj = $db->fetch_object($res);
			if ($obj->price_level == null) {
				$pricelevel = 1;
			} else {
				$pricelevel = $obj->price_level;
			}
		}
	} else {
		$pricelevel = 1;
	}

	$objProduct = new Product($db);
	$objProduct->fetch($product['idProduct']);

	if (!empty($objProduct->multiprices[$pricelevel]) && $objProduct->multiprices[$pricelevel] > 0) {
		$precio_unit = $objProduct->multiprices_ttc[$pricelevel];

	} else {
		$precio_unit = $objProduct->price_ttc;
	}

	$objDto=new Discounts($db);
	$result=$objDto->fetch_all_calcul($conf->global->DIS_APPLY,$product['socid'],$product['idProduct']);
	/*if($conf->global->DIS_APPLY == 0){
		$result=$objDto->fetch_all(3,$product["socid"],$product["idProduct"]);
	}
	else if($conf->global->DIS_APPLY == 1){
		$result=$objDto->fetch_all(1,$product["socid"]);
	}
	else {
		$result=$objDto->fetch_all(2,$product["id"]);
	}*/

	$i=0;
	if (is_array($result))
	{
		$num = count($result);
		while ($i < $num)
		{
			$nb = 1;
			if($result[$i]['fk_target'] > 0 && $result[$i]['type_target']==3 && $result[$i]['type_source']!=3){
				$cat = new Categorie($db);
				$cat->fetch($result[$i]['fk_target']);
				if($result[$i]['type_source']== 1)
					$nb = $cat->containsObject("product", $product["idProduct"]);
				else
					$nb = $cat->containsObject("customer", $product["socid"]);
			}
			if($nb > 0){
				if($result[$i]['type_dto'] == 5 && $product["cant"] > 1){
					$en_promo = (int)($product["cant"]/2);
					$precio_unit = ($precio_unit*(1-$result[$i]['dto_rate']/100)*$en_promo + ($product["cant"] - $en_promo)*$precio_unit)/$product["cant"];
				}
				else if($result[$i]['type_dto'] == 4 && $product["cant"] >= $result[$i]['qtybuy']){
					$en_promo = (int)($product["cant"] / $result[$i]['qtybuy']);
					//$precio_unit = (($result[$i]['qtybuy'] - $result[$i]['qtypay'])*$en_promo + ($product["cant"] % $result[$i]['qtybuy'])) * $precio_unit/$product["cant"];
					$precio_unit = ((/*$result[$i]['qtybuy'] -*/ $result[$i]['qtypay'])*$en_promo + ($product["cant"] % $result[$i]['qtybuy'])) * $precio_unit/$product["cant"];
				}
				else if ( $result[$i]['type_dto'] == 1 && (int)($product["cant"]) > 0){
					$precio_unit = $precio_unit -(($precio_unit*$result[$i]['dto_rate'])/100);
				}
			}
			$i++;
		}
	}
	return $precio_unit == $product["orig_price"]?0:$precio_unit;
}

function search_duplicates($object, $newline, $type_doc){
	require_once DOL_DOCUMENT_ROOT.'/core/lib/price.lib.php';

	global $db,$user;

	foreach($object->lines as $line){
		if($newline->fk_product == $line->fk_product && $line->rowid != $newline->rowid && $newline->fk_product > 0){
			$line->qty += $newline->qty;

			$dis_doc = new Discounts_doc($db);
			$res = $dis_doc->fetch($type_doc, $line->rowid);
			$res>0?$line->subprice = $dis_doc->ori_subprice:"";

			if($type_doc == 3) $line->fk_facture = $object->id;

			$localtaxes_type=array($object->localtax1_type,$object->localtax1_tx,$object->localtax2_type,$object->localtax2_tx);
			$result = calcul_price_total($line->qty, $line->subprice, $line->remise_percent, $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, 0, 'HT', 0, $line->product_type,'', $localtaxes_type);

			$line->total_ht = $result[0];
			$line->total_tva = $result[1];
			$line->total_localtax1 = $result[9];
			$line->total_localtax2 = $result[10];
			$line->total_ttc = $result[2];
			$line->subprice = $result[3];

			if($object->element=='propal'){
				$line->update(0, $user);
			}
			else {
				$line->update($user);
			}

			return 1;
		}

	}
	return 0;
}
