<?php
/* Copyright (C) 2001-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005      Brice Davoleau       <brice.davoleau@gmail.com>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2006-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2007      Patrick Raguin  		<patrick.raguin@gmail.com>
 * Copyright (C) 2014-2015 Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2015-2018 Ferran Marcet 		<fmarcet@2byte.es>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

$res=@include("../../main.inc.php");					// For root directory
if (! $res) $res=@include("../../../main.inc.php");		// For "custom" directory
require_once DOL_DOCUMENT_ROOT . '/core/lib/invoice.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/invoice2.lib.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
dol_include_once('/discounts/class/discounts.class.php');

global $langs,$conf, $user, $db;

$langs->load("companies");
$langs->load("bills");
$langs->load("discounts@discounts");

// Security check
$id = GETPOST('id','int');
$ref = GETPOST('ref','alpha');
$action = GETPOST('action','alpha');
$cancel = GETPOST('cancel','alpha');
$type= GETPOST('type','int');
$desc=GETPOST('desc','alpha');
$dto =GETPOST('dtotoaply');
$category=GETPOST("category","int");
$customer=GETPOST("customer","int");
$product=GETPOST("product","int");
$qtybuy = GETPOST("qtybuy","int");
$qtypay = GETPOST("qtypay","int");
$priority = GETPOST("priority","int");
$payment_cond= GETPOST('payment_cond');
$date_start=dol_mktime(0,0,0,$_POST['date_startmonth'],$_POST['date_startday'],$_POST['date_startyear']);
$date_end=dol_mktime(0,0,0,$_POST['date_endmonth'],$_POST['date_endday'],$_POST['date_endyear']);

$confirm = GETPOST("confirm",'alpha');

if ($user->socid) $socid=$user->socid;
$result = restrictedArea($user, 'societe', $socid, '&societe');

$discount = new Discounts($db);
$error = 0;

$object = new Facture($db);
$result = $object->fetch($id, $ref);

/*
 *	Actions
 */
if ($action==='adddiscount' && $user->rights->discounts->create )
{
	if (! empty($_POST["cancel"]))
	{
		$action = '';

	}
	else
	{
		//$desc=GETPOST('desc','alpha');
		//$dto =GETPOST('dtotoaply');

		if(($dto<0 || $dto>100 || empty($dto)) && $type>0 && $type!=$discount::DTO_BUYXPAYY)
		{
			setEventMessage($langs->trans('ErrDiscountValue'), 'errors');
			$action='create';
			$error++;
		}

		if(!$desc)
		{
			setEventMessage($langs->trans('ErrDescription'), 'errors');
			$action='create';
			$error++;
		}

		if ($type==-1) //commercial
		{
			setEventMessage($langs->trans('ErrType'), 'errors');
			$action='create';
			$error++;
		}

		if($type==$discount::DTO_BUYXPAYY) //BuyXPayY
		{
			if ($qtybuy < 1 || $qtypay < 1)
			{
				setEventMessage($langs->trans('ErrQty'), 'errors');
				$action='create';
				$error++;
			}
		}

		// Check if type already exists
		if ((empty($category) || $category < 0) && (empty($product) || $product < 0) && $customer > 0) {
			$category = $customer;
			$type_target = Discounts::SOURCE_THIRD;
		}
		else if ($product > 0 && (empty($category) || $category < 0) && (empty($customer) || $customer < 0)) {
			$category = $product;
			$type_target = Discounts::SOURCE_PRODUCT;
		}
		else if ($category > 0) {
			$type_target = Discounts::SOURCE_CATEGORY;
		}
		else {
			$category = 0;
			$type_target = 0;
		}
		if ($discount->Check_ifExists($discount::SOURCE_INVOICE,$id, $type,$dto,$category,$type_target) == true) {
			if($type == $discount::DTO_COMM) {
				setEventMessage($langs->trans('ErrDiscountCommercialAlreadyExists'), 'errors');
			}
			else {
				setEventMessage($langs->trans('ErrDiscountAlreadyExists',$discount->getLabelTypeDto($type)), 'errors');
			}

			$action='create';
			$error++;
		}

		if(!$error)
		{
			$discount->desc=$desc;
			$discount->type_dto=$type;
			$discount->dto_rate=$dto;
			$discount->date_start=$date_start;
			$discount->date_end=$date_end;
			$discount->payment_cond=$payment_cond;
			$discount->type_source=$discount::SOURCE_INVOICE;
			$discount->fk_source=$id;
			$discount->fk_target=$category;
			$discount->type_target=$type_target;
			$discount->qtybuy=$qtybuy;
			$discount->qtypay=$qtypay;
			$discount->priority=$priority;
			$res=$discount->create($user);

			if($res<1)
			{
				setEventMessage($discount->error, 'errors');
				$action='create';
			}
			else
			{
				$action='';
			}
		}
	}
}


if ($action==='updatediscount' && $user->rights->discounts->create )
{
	if (! empty($_POST["cancel"]))
	{
		$action = '';

	}
	else
	{
		$dtoid = GETPOST('dtoid');

		if(($dto<0 || $dto>100 || empty($dto)) && $type>0 && $type!=$discount::DTO_BUYXPAYY)
		{
			setEventMessage($langs->trans('ErrDiscountValue'), 'errors');
			$action='update';
			$error++;
		}

		if(!$desc)
		{
			setEventMessage($langs->trans('ErrDescription'), 'errors');
			$action='update';
			$error++;
		}

		if ($type==-1)
		{
			setEventMessage($langs->trans('ErrType'), 'errors');
			$action='update';
			$error++;
		}

		if($type==$discount::DTO_BUYXPAYY)
		{
			if ($qtybuy < 1 || $qtypay < 1)
			{
				setEventMessage($langs->trans('ErrQty'), 'errors');
				$action='update';
				$error++;
			}
		}

		// Check if type already exists
		if ((empty($category) || $category < 0) && (empty($product) || $product < 0) && $customer > 0) {
			$category = $customer;
			$type_target = Discounts::SOURCE_THIRD;
		}
		elseif ($product > 0 && (empty($category) || $category < 0) && (empty($customer) || $customer < 0)) {
			$category = $product;
			$type_target = Discounts::SOURCE_PRODUCT;
		}
		elseif ($category > 0) {
			$type_target = Discounts::SOURCE_CATEGORY;
		}
		else {
			$category = 0;
			$type_target = 0;
		}
		if($type == $discount::DTO_COMM) {
			if ($discount->Check_ifExists($discount::SOURCE_INVOICE,$id, $type,$dto,$category, $type_target,true,$dtoid) == true) {
				setEventMessage($langs->trans('ErrDiscountCommercialAlreadyExists'), 'errors');
				$action = 'update';
				$error++;
			}
		}

		if(!$error)
		{
			$discount->id=$dtoid;
			$discount->desc=$desc;
			$discount->type_dto=$type;
			$discount->dto_rate=$dto;
			$discount->date_start=$date_start;
			$discount->date_end=$date_end;
			$discount->payment_cond=$payment_cond;
			$discount->type_source=$discount::SOURCE_INVOICE;
			$discount->fk_source=$id;
			$discount->fk_target=$category;
			$discount->type_target=$type_target;
			$discount->qtybuy=$qtybuy;
			$discount->qtypay=$qtypay;
			$discount->priority=$priority;
			$res=$discount->update($user);

			if($res<1)
			{
				setEventMessage($discount->error, 'errors');
				$action='update';
			}
			else
			{
				$action='';
			}
		}
	}
}



if( $action==='confirm_deleteline' && $confirm === 'yes' && $user->rights->discounts->create )
{
	$dtoid=GETPOST('dtoid');
	$res=$discount->fetch($dtoid);
	if($res==1)
	{
		$res=$discount->delete($user);
		if( $res<1 ) {
			setEventMessage($discount->error, 'errors');
		}
		else {
			setEventMessage($langs->trans("DiscountDeleted"));
		}
		$action='';
	}
}

if ($action==='edit_discount' && $user->rights->discounts->create )
{
	$dtoid=GETPOST('dtoid');
	$res=$discount->fetch($dtoid);
	if($res)
	{
		$type= $discount->type_dto;
		$desc=$discount->desc;
		$dto = $discount->dto_rate ;
		$payment_cond= $discount->payment_cond;
		$date_start=$discount->date_start;
		$date_end = $discount->date_end;
		if ($discount->fk_source == $id && $discount->type_source == Discounts::SOURCE_INVOICE) {
			if ($discount->type_target > 0) {
				if ($discount->type_target == Discounts::SOURCE_CATEGORY) {
					$category = $discount->fk_target;
				} elseif ($discount->type_target == Discounts::SOURCE_THIRD) {
					$customer = $discount->fk_target;
				} else{
					$product = $discount->fk_target;
				}
			}
		}
		else {
			if ($discount->type_source == Discounts::SOURCE_CATEGORY) {
				$category = $discount->fk_source;
			} elseif ($discount->type_target == Discounts::SOURCE_THIRD) {
				$customer = $discount->fk_source;
			} else{
				$product = $discount->fk_source;
			}
		}
		$qtybuy = $discount->qtybuy;
		$qtypay = $discount->qtypay;
		$priority = $discount->priority;
		$action="update";

	}
}

/*
 *	View
 */

$form = new Form($db);

// Protection if external user
if ($user->socid > 0)
{
	accessforbidden();
}

if ($id || $ref) {
	$form = new Form($db);

	$result = $object->fetch($id, $ref);
	$id = $object->id;

	llxHeader("", "", $langs->trans("InvoiceCustomer"));

	$formconfirm = '';
	// Confirmation to delete line
	if ($action === 'delete_discount') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?dtoid=' . GETPOST("dtoid",
				'int') . '&id=' . GETPOST("id", 'int'), $langs->trans('DeleteDiscountLine'),
			$langs->trans('ConfirmDeleteDiscountLine'), 'confirm_deleteline', '', 0, 1);
	}
	print $formconfirm;

	$head = facture_prepare_head($object);

	$titre = $langs->trans("InvoiceCustomer");
	dol_fiche_head($head, 'discounts', $titre, 0, 'bill');

	print '<table class="border" width="100%">';

	// Ref
	print '<tr>';
	print '<td width="15%">' . $langs->trans("Ref") . '</td><td colspan="2">';
	print $form->showrefnav($object, 'ref', '', 1, 'ref');
	print '</td>';
	print '</tr>';

	// Third
	$soci = new Societe($db);
	$soci->fetch($object->socid, '');
	print '<tr><td>' . $langs->trans("ThirdParty") . '</td><td>' . $soci->getNomUrl(1) . '</td>';

	// TVA
	print '<tr><td>' . $langs->trans("VAT") . '</td><td>' . price($object->total_tva) . '</td></tr>';

	// Price
	print '<tr><td>' . $langs->trans("TotalHT") . '</td><td>' . price($object->total_ht) . '</td></tr>';

	// Price total
	print '<tr><td>' . $langs->trans("AmountTTC") . '</td><td>' . price($object->total_ttc) . '</td></tr>';

	// Status
	print '<tr><td>' . $langs->trans("Status") . '</td><td>';
	print $object->getLibStatut(2, 0);
	print '</td></tr>';

	print "</table>\n";

	print "</div>\n";


	/*
	 * Barre d'action
	 */

	print '<div align="right">';

	if ($action === 'create' || $action === 'update') {
		if ($action === 'create') {
			print load_fiche_titre($langs->trans("NewDiscount"), '', '');
		} else {
			print load_fiche_titre($langs->trans("UpdateDiscount"), '', '');
		}

		print "\n" . '<script type="text/javascript">';
		print '$(document).ready(function () {
		
			if($("#type").val()==-1){
				$(".dtotoaply").hide();
				$(".product").hide();
				$(".category").hide();
				$(".qtybuy").hide();
				$(".qtypay").hide();
			}
			if($("#type").val()==4){
				$(".dtotoaply").hide();
				$(".category").show();
				$(".product").show();
				$(".qtybuy").show();
				$(".qtypay").show()
		    }
			if($("#type").val()==5 || $("#type").val()==1){
				$(".dtotoaply").show();
				$(".category").show();
				$(".product").show();
				$(".qtybuy").hide();
				$(".qtypay").hide()
		    }
			
		$("#type").change(function() {
			if($("#type").val()==-1){
				$(".dtotoaply").hide();
				$(".product").hide();
				$(".category").hide();
				$(".qtybuy").hide();
				$(".qtypay").hide();
			}
			if($("#type").val()==4){
				$(".dtotoaply").hide();
				$(".category").show();
				$(".product").show();
				$(".qtybuy").show();
				$(".qtypay").show();
		    }
			if($("#type").val()==5 || $("#type").val()==1){
				$(".dtotoaply").show();
				$(".category").show();
				$(".product").show();
				$(".qtybuy").hide();
				$(".qtypay").hide();
		    }
		});
		
		$("#product").change(function(){
				$(".category").hide();
		});
		
		$("#category").change(function(){
				$(".product").hide();
		});
			 
		});';
		print '</script>' . "\n";

		print '<form enctype="multipart/form-data" action="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '"method="post" name="formaction">';
		print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';

		if ($action === 'create') {
			print '<input type="hidden" name="action" value="adddiscount">';
		} else {
			print '<input type="hidden" name="action" value="updatediscount">';
			print '<input type="hidden" name="dtoid" value="' . $dtoid . '">';
		}
		print '<table class="border" width="100%">';

		// Discount
		print '<tr><td class="fieldrequired">' . $langs->trans("Description") . '</td><td>';
		print '<input name="desc" size="35" value="' . $desc . '">';
		print '</td></tr>';

		// Type
		print '<tr><td class="fieldrequired">' . $langs->trans('DiscountType') . '</td>';
		print '<td>';
		print $discount->select_type($type, 'type', 1);
		print '</td>';
		print '</tr>';

		//Discout to apply
		print '<tr class="dtotoaply"><td class="fieldrequired">' . $langs->trans("dtocom") . '</td><td>';
		print '<input class="flat" name="dtotoaply" size="5" maxlength="2" value="' . price2num($dto) . '"> %';
		print '</td></tr>';

		//Buy X Pay Y
		print '<tr class="qtybuy"><td class="fieldrequired">' . $langs->trans("Buy") . '</td><td>';
		print '<input class="flat" name="qtybuy" size="5" maxlength="2" value="' . price2num($qtybuy) . '"> ' . $langs->trans("Units");
		print '</td></tr>';

		//Buy X Pay Y
		print '<tr class="qtypay"><td class="fieldrequired">' . $langs->trans("Pay") . '</td><td>';
		print '<input class="flat" name="qtypay" size="5" maxlength="2" value="' . price2num($qtypay) . '"> ' . $langs->trans("Units");
		print '</td></tr>';

		//Product
		if ($action == 'create' || ($action == 'update' && empty($category))) {
			print '<tr class="product"><td>' . $langs->trans("Product") . '</td><td>';
			$form->select_produits($product, 'product');
			print '</td></tr>';
		}

		//Category
		if (!empty($conf->categorie->enabled)) {
			if ($action == 'create' || ($action == 'update' && empty($product))) {
				print '<tr id="category" class="category"><td>' . $langs->trans("ProductsCategoryShort") . '</td><td>';
				print $form->select_all_categories(0, $category, "category");
				print '</td></tr>';
			}
		}

		if ($conf->global->DIS_APPLY == 3) {
			//Priority
			print '<tr class="priority"><td class="fieldrequired">' . $langs->trans("Priority") . '</td><td>';
			print '<input class="flat" name="priority" size="5" maxlength="2" value="' . price2num($priority) . '"> ';
			print '</td></tr>';
		}

		//Date start
		print '<tr class="priority"><td>' . $langs->trans("DateStart") . '</td><td>';
		$form->select_date($date_start, 'date_start', '', '', 1, "editdatestart");
		print '</td></tr>';

		//Date end
		print '<tr class="priority"><td>' . $langs->trans("DateEnd") . '</td><td>';
		$form->select_date($date_end, 'date_end', '', '', 1, "editdateend");
		print '</td></tr>';

		print '</table>';
		print '<span class="center"><br><input type="submit" class="button" value="' . $langs->trans("Save") . '">&nbsp;';
		print '<input type="submit" class="button" name="cancel" value="' . $langs->trans("Cancel") . '"></span>';

		print '<br></form>';
	} else {
		if (!empty($user->rights->discounts->create)) {
			print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '&amp;action=create">' . $langs->trans("NewDiscount") . '</a>';
		}
	}

	print '</div>';

	print '</br>';

	print '<table class="notopnoleftnoright" width="100%">';
	print '<tr class="liste_titre">';

	print '<td width="30%">' . $langs->trans("Description") . '</td>';
	print '<td>' . $langs->trans("DiscountType") . '</td>';
	print '<td>' . $langs->trans("dtocom") . '</td>';
	print '<td>' . $langs->trans("AffectedTo") . '</td>';
	print '<td>' . $langs->trans("User") . '</td>';
	if ($conf->global->DIS_APPLY == 3) {
		print '<td>' . $langs->trans("Priority") . '</td>';
	}
	print '<td>' . $langs->trans("DateCreation") . '</td>';
	print '<td>' . $langs->trans("DateStart") . '</td>';
	print '<td>' . $langs->trans("DateEnd") . '</td>';
	print '<td></td>';

	print '</tr>';

	$result = $discount->fetch_all(Discounts::SOURCE_INVOICE, $id);
	$i = 0;
	if (is_array($result)) {
		$num = count($result);
		while ($i < $num) {
			$var = !$var;

			print '<tr ' . $bc[$var] . '>';
			//Ref
			print '<td  nowrap="nowrap">' . $result[$i]['desc'] . '</td>';

			print '<td  nowrap="nowrap">' . $discount->getLabelTypeDto($result[$i]['type_dto']) . '</td>';

			//Rate
			if ($result[$i]["type_dto"] == 4) {
				print '<td  nowrap="nowrap">' . $result[$i]['qtybuy'] . 'x' . $result[$i]['qtypay'] . '</td>';
			} else {
				print '<td  nowrap="nowrap">' . price2num($result[$i]['dto_rate']) . ' %</td>';
			}

			//Category
			if ($result[$i]['fk_source'] == $id && $result[$i]['type_source'] == Discounts::SOURCE_INVOICE) {
				if ($result[$i]["fk_target"] > 0) {
					if ($result[$i]["type_target"] == Discounts::SOURCE_CATEGORY) {
						$cat = new Categorie($db);
						$cat->fetch($result[$i]["fk_target"]);
						print '<td  nowrap="nowrap"><span class="noborderoncategories" '.($cat->color?' style="background: #'.$cat->color.';"':' style="background: #aaa"').'>';
						print $cat->getNomUrl(1);
						print '</span></td>';
					} else {
						if ($result[$i]["type_target"] == Discounts::SOURCE_THIRD) {
							$cat = new Societe($db);
							$cat->fetch($result[$i]["fk_target"]);
							print '<td  nowrap="nowrap">' . $cat->getNomUrl(1) . '</td>';
						}
						else{
							if ($result[$i]["type_target"] == Discounts::SOURCE_PRODUCT) {
								$cat = new Product($db);
								$cat->fetch($result[$i]["fk_target"]);
								print '<td  nowrap="nowrap">' . $cat->getNomUrl(1) . '</td>';
							}
						}
					}
				} else {
					print '<td nowrap="nowrap"></td>';
				}
			} else {
				if ($result[$i]["type_source"] == Discounts::SOURCE_CATEGORY) {
					$cat = new Categorie($db);
					$cat->fetch($result[$i]["fk_source"]);
					print '<td  nowrap="nowrap"><span class="noborderoncategories" '.($cat->color?' style="background: #'.$cat->color.';"':' style="background: #aaa"').'>';
					print $cat->getNomUrl(1);
					print '</span></td>';
				} else {
					if ($result[$i]["type_source"] == Discounts::SOURCE_THIRD) {
						$cat = new Societe($db);
						$cat->fetch($result[$i]["fk_source"]);
						print '<td  nowrap="nowrap">' . $cat->getNomUrl(1) . '</td>';
					}
					else{
						if ($result[$i]["type_source"] == Discounts::SOURCE_PRODUCT) {
							$cat = new Product($db);
							$cat->fetch($result[$i]["fk_source"]);
							print '<td  nowrap="nowrap">' . $cat->getNomUrl(1) . '</td>';
						}
					}
				}
			}

			print '<td>';
			$userstatic = new User($db);
			$userstatic->fetch($result[$i]['fk_user_author']);
			print $userstatic->getNomUrl(1);
			print '</td>';

			if ($conf->global->DIS_APPLY == 3) {
				//Priority
				print '<td nowrap="nowrap">' . $result[$i]["priority"] . "</td>\n";
			}
			//DateCreation
			print '<td nowrap="nowrap">' . dol_print_date($db->jdate($result[$i]["datec"]), "dayhour") . "</td>\n";
			print '<td nowrap="nowrap">' . dol_print_date($db->jdate($result[$i]["date_start"]), "day") . "</td>\n";
			print '<td nowrap="nowrap">' . dol_print_date($db->jdate($result[$i]["date_end"]), "day") . "</td>\n";

			print '<td align="right">';
			if ($user->rights->discounts->create) {
				print '<a href="' . $_SERVER["PHP_SELF"] . '?action=edit_discount&amp;dtoid=' . $result[$i]['id'] . '&amp;id=' . $id . '&type=' . $result[$i]['type_dto'] . '">';
				print img_edit();
				print '</a>';
				print ' <a href="' . $_SERVER["PHP_SELF"] . '?action=delete_discount&amp;dtoid=' . $result[$i]['id'] . '&amp;id=' . $id . '">';
				print img_delete();
				print '</a>';
			}
			print '</td>';

			print '</tr>';

			$i++;

		}
	}
	print "</table>";
}

llxFooter();

$db->close();
