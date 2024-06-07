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
require_once DOL_DOCUMENT_ROOT . '/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
if (! empty($conf->categorie->enabled)) require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
dol_include_once('/discounts/class/discounts.class.php');

global $langs,$conf, $user, $db;

$langs->load("companies");
$langs->load("bills");
if (! empty($conf->categorie->enabled)) $langs->load("categories");
$langs->load("discounts@discounts");

// Security check
$prodid = GETPOST('prodid','int');
$ref = GETPOST('ref','alpha');
$action = GETPOST('action','alpha');
$cancel = GETPOST('cancel','alpha');
$type= GETPOST('type','int');
$desc=GETPOST('desc','alpha');
$dto =GETPOST('dtotoaply');
$category=GETPOST("category","int");
$customer=GETPOST("customer","int");
$facture=GETPOST("facture","int");
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

$object = new Product($db);

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
		if ((empty($category) || $category < 0) && (empty($facture) || $facture < 0) && $customer > 0) {
			$category = $customer;
			$type_target = Discounts::SOURCE_THIRD;
		}
        elseif ((empty($category) || $category < 0) && (empty($customer) || $customer < 0) && $facture > 0) {
			$category = $facture;
            $type_target = Discounts::SOURCE_INVOICE;
        }
		elseif ($category > 0) {
			$type_target = Discounts::SOURCE_CATEGORY;
		}
        else {
            $category = 0;
            $type_target = 0;
        }
		if ($discount->Check_ifExists($discount::SOURCE_PRODUCT,$prodid, $type,$dto,$category,$type_target) == true) {
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
			$discount->type_source=$discount::SOURCE_PRODUCT;
			$discount->fk_source=$prodid;
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
		if ((empty($category) || $category < 0) && (empty($facture) || $facture < 0) && $customer > 0) {
			$category = $customer;
			$type_target = Discounts::SOURCE_THIRD;
		}
		elseif ((empty($category) || $category < 0) && (empty($customer) || $customer < 0) && $facture > 0) {
			$category = $facture;
			$type_target = Discounts::SOURCE_INVOICE;
		}
        else if ($category > 0) {
            $type_target = Discounts::SOURCE_CATEGORY;
        }
        else {
            $category = 0;
            $type_target = 0;
        }
        if($type == $discount::DTO_COMM) {
			if ($discount->Check_ifExists($discount::SOURCE_PRODUCT,$prodid, $type,$dto,$category, $type_target,true,$dtoid) == true) {
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
			$discount->type_source=$discount::SOURCE_PRODUCT;
			$discount->fk_source=$prodid;
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
		if ($discount->fk_source == $prodid && $discount->type_source == Discounts::SOURCE_PRODUCT) {
            if ($discount->type_target > 0) {
                if ($discount->type_target == Discounts::SOURCE_CATEGORY) {
                    $category = $discount->fk_target;
                } elseif ($discount->type_target == Discounts::SOURCE_THIRD) {
                    $customer = $discount->fk_target;
                } else {
                	$facture = $discount->fk_target;
				}
            }
        }
        else {
            if ($discount->type_source == Discounts::SOURCE_CATEGORY) {
                $category = $discount->fk_source;
            } elseif ($discount->type_target == Discounts::SOURCE_THIRD) {
				$customer = $discount->fk_target;
			} else {
				$facture = $discount->fk_target;
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

/*
 * Product discount card
 */
if ($prodid || $ref) {
    $form = new Form($db);

    $result = $object->fetch($prodid, $ref);
    $prodid = $object->id;

    llxHeader("", "", $langs->trans("CardProduct" . $object->type));

    $formconfirm = '';
    // Confirmation to delete line
    if ($action === 'delete_discount') {
        $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?dtoid=' . GETPOST("dtoid",
                'int') . '&prodid=' . GETPOST("prodid", 'int'), $langs->trans('DeleteDiscountLine'),
            $langs->trans('ConfirmDeleteDiscountLine'), 'confirm_deleteline', '', 0, 1);
    }
    print $formconfirm;

    $head = product_prepare_head($object);

    $titre = $langs->trans("CardProduct" . $object->type);
    $picto = ($object->type == 1 ? 'service' : 'product');
    dol_fiche_head($head, 'discounts', $titre, 0, $picto);

    print '<table class="border" width="100%">';

    // Ref
    print '<tr>';
    print '<td width="15%">' . $langs->trans("Ref") . '</td><td colspan="2">';
    print $form->showrefnav($object, 'ref', '', 1, 'ref');
    print '</td>';
    print '</tr>';

    // Label
    print '<tr><td>' . $langs->trans("Label") . '</td><td>' . $object->libelle . '</td>';

    $isphoto = $object->is_photo_available($conf->product->multidir_output [$object->entity]);

    $nblignes = 5;
    if ($isphoto) {
        // Photo
        print '<td valign="middle" align="center" width="30%" rowspan="' . $nblignes . '">';
        print $object->show_photos($conf->product->multidir_output [$object->entity], 1, 1, 0, 0, 0, 80);
        print '</td>';
    }

    print '</tr>';

    // MultiPrix
    if (!empty($conf->global->PRODUIT_MULTIPRICES)) {
        if (!empty($socid)) {
            $soc = new Societe($db);
            $soc->id = $socid;
            $soc->fetch($socid);

            print '<tr><td>' . $langs->trans("SellingPrice") . '</td>';

            if ($object->multiprices_base_type ["$soc->price_level"] === 'TTC') {
                print '<td>' . price($object->multiprices_ttc ["$soc->price_level"]);
            } else {
                print '<td>' . price($object->multiprices ["$soc->price_level"]);
            }

            if ($object->multiprices_base_type ["$soc->price_level"]) {
                print ' ' . $langs->trans($object->multiprices_base_type ["$soc->price_level"]);
            } else {
                print ' ' . $langs->trans($object->price_base_type);
            }
            print '</td></tr>';

            // Prix mini
            print '<tr><td>' . $langs->trans("MinPrice") . '</td><td>';
            if ($object->multiprices_base_type ["$soc->price_level"] === 'TTC') {
                print price($object->multiprices_min_ttc ["$soc->price_level"]) . ' ' . $langs->trans($object->multiprices_base_type ["$soc->price_level"]);
            } else {
                print price($object->multiprices_min ["$soc->price_level"]) . ' ' . $langs->trans($object->multiprices_base_type ["$soc->price_level"]);
            }
            print '</td></tr>';

            // TVA
            print '<tr><td>' . $langs->trans("VATRate") . '</td><td>' . vatrate($object->multiprices_tva_tx ["$soc->price_level"],
                    true) . '</td></tr>';
        } else {
            for ($i = 1; $i <= $conf->global->PRODUIT_MULTIPRICES_LIMIT; $i++) {
                // TVA
                if ($i == 1)            // We show only price for level 1
                {
                    print '<tr><td>' . $langs->trans("VATRate") . '</td><td>' . vatrate($object->multiprices_tva_tx [1],
                            true) . '</td></tr>';
                }

                print '<tr>';

                // Label of price
                print '<td>' . $langs->trans("SellingPrice") . ' ' . $i;
                $keyforlabel = 'PRODUIT_MULTIPRICES_LABEL' . $i;
                if (!empty($conf->global->$keyforlabel)) {
                    print ' - ' . $langs->trans($conf->global->$keyforlabel);
                }
                print '</td>';

                if ($object->multiprices_base_type ["$i"] === 'TTC') {
                    print '<td>' . price($object->multiprices_ttc ["$i"]);
                } else {
                    print '<td>' . price($object->multiprices ["$i"]);
                }

                if ($object->multiprices_base_type ["$i"]) {
                    print ' ' . $langs->trans($object->multiprices_base_type ["$i"]);
                } else {
                    print ' ' . $langs->trans($object->price_base_type);
                }
                print '</td></tr>';

                // Prix mini
                print '<tr><td>' . $langs->trans("MinPrice") . ' ' . $i . '</td><td>';
                if (empty($object->multiprices_base_type["$i"])) {
                    $object->multiprices_base_type["$i"] = "HT";
                }
                if ($object->multiprices_base_type["$i"] === 'TTC') {
                    print price($object->multiprices_min_ttc["$i"]) . ' ' . $langs->trans($object->multiprices_base_type["$i"]);
                } else {
                    print price($object->multiprices_min["$i"]) . ' ' . $langs->trans($object->multiprices_base_type["$i"]);
                }
                print '</td></tr>';

                // Price by quantity
                if ($conf->global->PRODUIT_CUSTOMER_PRICES_BY_QTY) {
                    print '<tr><td>' . $langs->trans("PriceByQuantity") . ' ' . $i;
                    print '</td><td>';

                    if ($object->prices_by_qty [$i] == 1) {
                        print '<table width="50%" class="noborder">';

                        print '<tr class="liste_titre">';
                        print '<td>' . $langs->trans("PriceByQuantityRange") . ' ' . $i . '</td>';
                        print '<td align="right">' . $langs->trans("HT") . '</td>';
                        print '<td align="right">' . $langs->trans("UnitPrice") . '</td>';
                        print '<td align="right">' . $langs->trans("Discount") . '</td>';
                        print '<td>&nbsp;</td>';
                        print '</tr>';
                        foreach ($object->prices_by_qty_list [$i] as $ii => $prices) {
                            if ($action === 'edit_price_by_qty' && $rowid == $prices ['rowid'] && ($user->rights->produit->creer || $user->rights->service->creer)) {
                                print '<form action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="POST">';
                                print '<input type="hidden" name="action" value="update_price_by_qty">';
                                print '<input type="hidden" name="priceid" value="' . $object->prices_by_qty_id [$i] . '">';
                                print '<input type="hidden" value="' . $prices ['rowid'] . '" name="rowid">';
                                print '<tr class="' . ($ii % 2 == 0 ? 'pair' : 'impair') . '">';
                                print '<td><input size="5" type="text" value="' . $prices ['quantity'] . '" name="quantity"></td>';
                                print '<td align="right" colspan="2"><input size="10" type="text" value="' . $prices ['price'] . '" name="price">&nbsp;' . $object->price_base_type . '</td>';
                                // print '<td align="right">&nbsp;</td>';
                                print '<td align="right"><input size="5" type="text" value="' . $prices ['remise_percent'] . '" name="remise_percent">&nbsp;%</td>';
                                print '<td align="center"><input type="submit" value="' . $langs->trans("Modify") . '" class="button"></td>';
                                print '</tr>';
                                print '</form>';
                            } else {
                                print '<tr class="' . ($ii % 2 == 0 ? 'pair' : 'impair') . '">';
                                print '<td>' . $prices ['quantity'] . '</td>';
                                print '<td align="right">' . price($prices ['price']) . '</td>';
                                print '<td align="right">' . price($prices ['unitprice']) . '</td>';
                                print '<td align="right">' . price($prices ['remise_percent']) . ' %</td>';
                                print '<td align="center">';
                                if (($user->rights->produit->creer || $user->rights->service->creer)) {
                                    print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=edit_price_by_qty&amp;rowid=' . $prices ["rowid"] . '">';
                                    print img_edit() . '</a>';
                                    print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=delete_price_by_qty&amp;rowid=' . $prices ["rowid"] . '">';
                                    print img_delete() . '</a>';
                                } else {
                                    print '&nbsp;';
                                }
                                print '</td>';
                                print '</tr>';
                            }
                        }


                        print '</table>';
                    } else {
                        print $langs->trans("No");
                        print '&nbsp;<a href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=activate_price_by_qty&level=' . $i . '">(' . $langs->trans("Activate") . ')</a>';
                    }
                    print '</td></tr>';
                }
            }
        }
    } else {
        // TVA
        print '<tr><td>' . $langs->trans("VATRate") . '</td><td>' . vatrate($object->tva_tx . ($object->tva_npr ? '*' : ''),
                true) . '</td></tr>';

        // Price
        print '<tr><td>' . $langs->trans("SellingPrice") . '</td><td>';
        if ($object->price_base_type === 'TTC') {
            print price($object->price_ttc) . ' ' . $langs->trans($object->price_base_type);
        } else {
            print price($object->price) . ' ' . $langs->trans($object->price_base_type);
        }
        print '</td></tr>';

        // Price minimum
        print '<tr><td>' . $langs->trans("MinPrice") . '</td><td>';
        if ($object->price_base_type === 'TTC') {
            print price($object->price_min_ttc) . ' ' . $langs->trans($object->price_base_type);
        } else {
            print price($object->price_min) . ' ' . $langs->trans($object->price_base_type);
        }
        print '</td></tr>';

        // Price by quantity
        if ($conf->global->PRODUIT_CUSTOMER_PRICES_BY_QTY) {
            print '<tr><td>' . $langs->trans("PriceByQuantity");
            if ($object->prices_by_qty [0] == 0) {
                print '&nbsp;<a href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=activate_price_by_qty&level=1">' . $langs->trans("Activate");
            }
            print '</td><td>';

            if ($object->prices_by_qty [0] == 1) {
                print '<table width="50%" class="noborder">';
                print '<tr class="liste_titre">';
                print '<td>' . $langs->trans("PriceByQuantityRange") . '</td>';
                print '<td align="right">' . $langs->trans("HT") . '</td>';
                print '<td align="right">' . $langs->trans("UnitPrice") . '</td>';
                print '<td align="right">' . $langs->trans("Discount") . '</td>';
                print '<td>&nbsp;</td>';
                print '</tr>';
                foreach ($object->prices_by_qty_list [0] as $ii => $prices) {
                    if ($action === 'edit_price_by_qty' && $rowid == $prices ['rowid'] && ($user->rights->produit->creer || $user->rights->service->creer)) {
                        print '<form action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="POST">';
                        print '<input type="hidden" name="action" value="update_price_by_qty">';
                        print '<input type="hidden" name="priceid" value="' . $object->prices_by_qty_id [0] . '">';
                        print '<input type="hidden" value="' . $prices ['rowid'] . '" name="rowid">';
                        print '<tr class="' . ($ii % 2 == 0 ? 'pair' : 'impair') . '">';
                        print '<td><input size="5" type="text" value="' . $prices ['quantity'] . '" name="quantity"></td>';
                        print '<td align="right" colspan="2"><input size="10" type="text" value="' . $prices ['price'] . '" name="price">&nbsp;' . $object->price_base_type . '</td>';
                        // print '<td align="right">&nbsp;</td>';
                        print '<td align="right"><input size="5" type="text" value="' . $prices ['remise_percent'] . '" name="remise_percent">&nbsp;%</td>';
                        print '<td align="center"><input type="submit" value="' . $langs->trans("Modify") . '" class="button"></td>';
                        print '</tr>';
                        print '</form>';
                    } else {
                        print '<tr class="' . ($ii % 2 == 0 ? 'pair' : 'impair') . '">';
                        print '<td>' . $prices ['quantity'] . '</td>';
                        print '<td align="right">' . price($prices ['price']) . '</td>';
                        print '<td align="right">' . price($prices ['unitprice']) . '</td>';
                        print '<td align="right">' . price($prices ['remise_percent']) . ' %</td>';
                        print '<td align="center">';
                        if (($user->rights->produit->creer || $user->rights->service->creer)) {
                            print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=edit_price_by_qty&amp;rowid=' . $prices ["rowid"] . '">';
                            print img_edit() . '</a>';
                            print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=delete_price_by_qty&amp;rowid=' . $prices ["rowid"] . '">';
                            print img_delete() . '</a>';
                        } else {
                            print '&nbsp;';
                        }
                        print '</td>';
                        print '</tr>';
                    }
                }


                print '</table>';
            } else {
                print $langs->trans("No");
            }
            print '</td></tr>';
        }
    }

    // Status (to sell)
    print '<tr><td>' . $langs->trans("Status") . ' (' . $langs->trans("Sell") . ')</td><td>';
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
				$(".customer").hide();
				$(".category").hide();
				$(".qtybuy").hide();
				$(".qtypay").hide();
			}
			if($("#type").val()==4){
				$(".dtotoaply").hide();
				$(".category").show();
				$(".customer").show();
				$(".qtybuy").show();
				$(".qtypay").show()
		    }
			if($("#type").val()==5 || $("#type").val()==1){
				$(".dtotoaply").show();
				$(".category").show();
				$(".customer").show();
				$(".qtybuy").hide();
				$(".qtypay").hide()
		    }
			
		$("#type").change(function() {
			if($("#type").val()==-1){
				$(".dtotoaply").hide();
				$(".customer").hide();
				$(".category").hide();
				$(".qtybuy").hide();
				$(".qtypay").hide();
			}
			if($("#type").val()==4){
				$(".dtotoaply").hide();
				$(".category").show();
				$(".customer").show();
				$(".qtybuy").show();
				$(".qtypay").show();
		    }
			if($("#type").val()==5 || $("#type").val()==1){
				$(".dtotoaply").show();
				$(".category").show();
				$(".customer").show();
				$(".qtybuy").hide();
				$(".qtypay").hide();
		    }
		});
		
		$("#customer").change(function(){
				$(".category").hide();
		});
		
		$("#category").change(function(){
				$(".customer").hide();
		});
			 
		});';
        print '</script>' . "\n";

        print '<form enctype="multipart/form-data" action="' . $_SERVER["PHP_SELF"] . '?prodid=' . $prodid . '"method="post" name="formaction">';
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

        //Customer
        if ($action == 'create' || ($action == 'update' && empty($category))) {
            print '<tr class="customer"><td>' . $langs->trans("Customer") . '</td><td>';
            print $form->select_company($customer, 'customer', 'client > 0', 1);
            print '</td></tr>';
        }

        //Category
        if (!empty($conf->categorie->enabled)) {
            if ($action == 'create' || ($action == 'update' && empty($customer))) {
                print '<tr id="category" class="category"><td>' . $langs->trans("CustomersCategoryShort") . '</td><td>';
                print $form->select_all_categories(Categorie::TYPE_CUSTOMER, $category, "category");
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
            print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?prodid=' . $prodid . '&amp;action=create">' . $langs->trans("NewDiscount") . '</a>';
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

    $result = $discount->fetch_all(Discounts::SOURCE_PRODUCT, $prodid);
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
            if ($result[$i]['fk_source'] == $prodid && $result[$i]['type_source'] == Discounts::SOURCE_PRODUCT) {
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
                        } else {
							if ($result[$i]["type_target"] == Discounts::SOURCE_INVOICE) {
								$cat = new Facture($db);
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
                    } else {
						if ($result[$i]["type_source"] == Discounts::SOURCE_INVOICE) {
							$cat = new Facture($db);
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
                print '<a href="' . $_SERVER["PHP_SELF"] . '?action=edit_discount&amp;dtoid=' . $result[$i]['id'] . '&amp;prodid=' . $prodid . '&type=' . $result[$i]['type_dto'] . '">';
                print img_edit();
                print '</a>';
                print ' <a href="' . $_SERVER["PHP_SELF"] . '?action=delete_discount&amp;dtoid=' . $result[$i]['id'] . '&amp;prodid=' . $prodid . '">';
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
