<?php
/* Copyright (C) 2005       Matthieu Valleton	<mv@seeschloss.org>
 * Copyright (C) 2006-2015  Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2007       Patrick Raguin		<patrick.raguin@gmail.com>
 * Copyright (C) 2005-2012  Regis Houssin		<regis.houssin@capnetworks.com>
 * Copyright (C) 2015       Raphaël Doursenaud  <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2017-2018  Ferran Marcet 		<fmarcet@2byte.es>
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

/**
 *       \file       htdocs/categories/viewcat.php
 *       \ingroup    category
 *       \brief      Page to show a category card
 */

$res=@include("../../main.inc.php");					// For root directory
if (! $res) $res=@include("../../../main.inc.php");		// For "custom" directory
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/categories.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
dol_include_once('/discounts/class/discounts.class.php');

global $langs,$conf, $user, $db;

$langs->load("categories");

// Security check
$id = GETPOST('id','int');
$ref = GETPOST('ref','alpha');
$action = GETPOST('action','alpha');
$cancel = GETPOST('cancel','alpha');
$type= GETPOST('type','int');
$type1= GETPOST('type1','alpha');
$type_dto= GETPOST('type_dto','int');
$desc=GETPOST('desc','alpha');
$dto =GETPOST('dtotoaply');
$category=GETPOST("category","int");
$individual=GETPOST("individual","int");
$qtybuy = GETPOST("qtybuy","int");
$qtypay = GETPOST("qtypay","int");
$priority = GETPOST("priority","int");
$date_start=dol_mktime(0,0,0,$_POST['date_startmonth'],$_POST['date_startday'],$_POST['date_startyear']);
$date_end=dol_mktime(0,0,0,$_POST['date_endmonth'],$_POST['date_endday'],$_POST['date_endyear']);

$confirm = GETPOST("confirm",'alpha');

if ($id == "")
{
	dol_print_error('','Missing parameter id');
	exit();
}

// Security check
$result = restrictedArea($user, 'categorie', $id, '&category');

$discount = new Discounts($db);

$object = new Categorie($db);
$result=$object->fetch($id);
$object->fetch_optionals($id,$extralabels);
if ($result <= 0)
{
	dol_print_error($db,$object->error);
	exit;
}

$type=$object->type;

$extrafields = new ExtraFields($db);
$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);

// Initialize technical object to manage hooks. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('categorycard'));

/*
 *	Actions
 */

$error=0;
if ($action==='adddiscount' && $user->rights->discounts->create )
{
    if (! empty($_POST["cancel"]))
    {
        $action = '';

    }
    else
    {
        if(($dto<0 || $dto>100 || empty($dto)) && $type_dto>0 && $type_dto!=4)
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

        if ($type_dto==-1)
        {
            setEventMessage($langs->trans('ErrType'), 'errors');
            $action='create';
            $error++;
        }

        if($type_dto== $discount::DTO_BUYXPAYY) //BuyXPayY
        {
            if ($qtybuy < 1 || $qtypay < 1)
            {
                setEventMessage($langs->trans('ErrQty'), 'errors');
                $action='create';
                $error++;
            }
        }

        // Check if type already exists
        if ((empty($category) || $category < 0) && $individual > 0) {
			$category = $individual;
			$type_target = ($type == 0 ? Discounts::SOURCE_THIRD : Discounts::SOURCE_PRODUCT);
		}
        else if ($category > 0) {
            $type_target = Discounts::SOURCE_CATEGORY;
        }
        else {
            $category = 0;
            $type_target = 0;
        }
        if ($discount->Check_ifExists($discount::SOURCE_CATEGORY,$id, $type_dto,$dto,$category,$type_target) == true) {
            if($type_dto == $discount::DTO_COMM) {
                setEventMessage($langs->trans('ErrDiscountCommercialAlreadyExists'), 'errors');
            }
            else {
                setEventMessage($langs->trans('ErrDiscountAlreadyExists',$discount->getLabelTypeDto($type_dto)), 'errors');
            }

            $action='create';
            $error++;
        }

        if(!$error)
        {
            $discount->desc=$desc;
            $discount->type_dto=$type_dto;
            $discount->dto_rate=$dto;
            $discount->date_start=$date_start;
            $discount->date_end=$date_end;
            $discount->payment_cond=$payment_cond;
            $discount->type_source=$discount::SOURCE_CATEGORY;
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

        if(($dto<0 || $dto>100 || empty($dto)) && $type_dto>0 && $type_dto!=$discount::DTO_BUYXPAYY)
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

        if ($type_dto==-1)
        {
            setEventMessage($langs->trans('ErrType'), 'errors');
            $action='create';
            $error++;
        }

        if($type_dto==$discount::DTO_BUYXPAYY)
        {
            if ($qtybuy < 1 || $qtypay < 1)
            {
                setEventMessage($langs->trans('ErrQty'), 'errors');
                $action='update';
                $error++;
            }
        }



		if ((empty($category) || $category < 0) && $individual > 0) {
			$category = $individual;
			$type_target = ($type == 0 ? Discounts::SOURCE_THIRD : Discounts::SOURCE_PRODUCT);
		}
        else if ($category > 0) {
            $type_target = Discounts::SOURCE_CATEGORY;
        }
        else {
            $category = 0;
            $type_target = 0;
        }
        if($type_dto == $discount::DTO_COMM) {
            // Check if type already exists
            if ($discount->Check_ifExists($discount::SOURCE_CATEGORY,$id, $type_dto,$dto,$category, $type_target, true,$dtoid) == true) {
                setEventMessage($langs->trans('ErrDiscountCommercialAlreadyExists'), 'errors');
                $action = 'update';
                $error++;
            }
        }

        if(!$error)
        {
            $discount->id=GETPOST('dtoid');
            $discount->desc=$desc;
            $discount->type_dto=$type_dto;
            $discount->dto_rate=$dto;
            $discount->date_start=$date_start;
            $discount->date_end=$date_end;
            $discount->payment_cond=$payment_cond;
            $discount->type_source=$discount::SOURCE_CATEGORY;
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
                $action='create';
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

if ($action==='edit_discount' && $user->rights->discounts->create ) {
    $dtoid = GETPOST('dtoid');
    $res = $discount->fetch($dtoid);
    if ($res) {
        $type_dto = $discount->type_dto;
        $desc = $discount->desc;
        $dto = $discount->dto_rate;
        $payment_cond = $discount->payment_cond;
        $date_start = $discount->date_start;
        $date_end = $discount->date_end;
        if ($discount->fk_source == $id && $discount->type_source == Discounts::SOURCE_CATEGORY) {
            if ($discount->type_target > 0) {
                if ($discount->type_target == Discounts::SOURCE_CATEGORY) {
                    $category = $discount->fk_target;
                } else {
                    $individual = $discount->fk_target;
                }
            }
        } else {
            if ($discount->type_source == Discounts::SOURCE_CATEGORY) {
                $category = $discount->fk_source;
            } else {
                $customer = $discount->fk_source;
            }
        }
        $qtybuy = $discount->qtybuy;
        $qtypay = $discount->qtypay;
        $priority = $discount->priority;
        $action = "update";

    }
}


/*
 * View
 */

$form = new Form($db);
$formother = new FormOther($db);

$helpurl='';
llxHeader("",$langs->trans("Categories"),$helpurl);
$formconfirm = '';
// Confirmation to delete line
if ($action === 'delete_discount') {
    $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?dtoid=' . GETPOST("dtoid", 'int') . '&id=' . GETPOST("id", 'int') . '&type1=' . $type1, $langs->trans('DeleteDiscountLine'), $langs->trans('ConfirmDeleteDiscountLine'), 'confirm_deleteline', '', 0, 1);
}
print $formconfirm;

if ($type == Categorie::TYPE_PRODUCT)       $title=$langs->trans("ProductsCategoryShort");
elseif ($type == Categorie::TYPE_CUSTOMER)  $title=$langs->trans("CustomersCategoryShort");
else                                        $title=$langs->trans("Category");

$head = categories_prepare_head($object,$type1);

dol_fiche_head($head, 'discounts', $title, 0, 'category');

$linkback = '<a href="'.DOL_URL_ROOT.'/categories/index.php?leftmenu=cat&type='.$type.'">'.$langs->trans("BackToList").'</a>';

$object->ref = $object->label;
$morehtmlref='<br><div class="refidno"><a href="'.DOL_URL_ROOT.'/categories/index.php?leftmenu=cat&type='.$type.'">'.$langs->trans("Root").'</a> >> ';
$ways = $object->print_all_ways(" &gt;&gt; ", '', 1);
foreach ($ways as $way)
{
    $morehtmlref.=$way."<br>\n";
}
$morehtmlref.='</div>';

dol_banner_tab($object, 'ref', $linkback, ($user->societe_id?0:1), 'ref', 'ref', $morehtmlref, '', 0, '', '', 1);

/*
 * Confirmation suppression
 */

print '<br>';

print '<div class="underbanner clearboth"></div>';
print '<table width="100%" class="border">';

// Description
print '<tr><td class="titlefield notopnoleft">';
print $langs->trans("Description").'</td><td>';
print dol_htmlentitiesbr($object->description);
print '</td></tr>';

// Color
print '<tr><td class="notopnoleft">';
print $langs->trans("Color").'</td><td>';
print $formother->showColor($object->color);
print '</td></tr>';

$reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
if (empty($reshook) && ! empty($extrafields->attribute_label))
{
	print $object->showOptionals($extrafields);
}

print '</table>';

dol_fiche_end();

/*
 * Boutons actions
 */

print '<div align="right">';

if($action === 'create' || $action ==='update')
{
    if($action === 'create'){
        print load_fiche_titre($langs->trans("NewDiscount"),'','');
    }else {
        print load_fiche_titre($langs->trans("UpdateDiscount"),'','');
    }

    print "\n".'<script type="text/javascript">';
    print '$(document).ready(function () {
		
		if($("#type_dto").val()==-1){
				$(".dtotoaply").hide();
				$(".individual").hide();
				$(".category").hide();
				$(".qtybuy").hide();
				$(".qtypay").hide();
			}
			if($("#type_dto").val()==4){
				$(".dtotoaply").hide();
				$(".category").show();
				$(".individual").show();
				$(".qtybuy").show();
				$(".qtypay").show()
		    }
			if($("#type_dto").val()==5 || $("#type_dto").val()==1){
				$(".dtotoaply").show();
				$(".category").show();
				$(".individual").show();
				$(".qtybuy").hide();
				$(".qtypay").hide()
		    }
			
		$("#type_dto").change(function() {
			if($("#type_dto").val()==-1){
				$(".dtotoaply").hide();
				$(".individual").hide();
				$(".category").hide();
				$(".qtybuy").hide();
				$(".qtypay").hide();
			}
			if($("#type_dto").val()==4){
				$(".dtotoaply").hide();
				$(".category").show();
				$(".individual").show();
				$(".qtybuy").show();
				$(".qtypay").show();
		    }
			if($("#type_dto").val()==5 || $("#type_dto").val()==1){
				$(".dtotoaply").show();
				$(".category").show();
				$(".individual").show();
				$(".qtybuy").hide();
				$(".qtypay").hide();
		    }
		});
		
		$("#individual").change(function(){
				$(".category").hide();
		});
		
		$("#category").change(function(){
				$(".individual").hide();
		});
			 
		});';
    print '</script>'."\n";

    print '<form enctype="multipart/form-data" action="'.$_SERVER["PHP_SELF"].'?id='.$id.'"method="post" name="formaction">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="type1" value="'.$type1.'">';

    if($action==='create')
    {
        print '<input type="hidden" name="action" value="adddiscount">';
    }
    else
    {
        print '<input type="hidden" name="action" value="updatediscount">';
        print '<input type="hidden" name="dtoid" value="'.$dtoid.'">';
    }
    print '<table class="border" width="100%">';

    // Discount
    print '<tr><td class="fieldrequired">'.$langs->trans("Description").'</td><td>';
    print '<input name="desc" size="35" value="'.$desc.'">';
    print '</td></tr>';

    // Type
    print '<tr><td class="fieldrequired">'.$langs->trans('DiscountType').'</td>';
    print '<td>';
    print $discount->select_type($type_dto,'type_dto',1);
    print '</td>';
    print '</tr>';

    //Discout to apply
    print '<tr class="dtotoaply"><td class="fieldrequired">'.$langs->trans("dtocom").'</td><td>';
    print '<input class="flat" name="dtotoaply" size="5" maxlength="2" value="'.price2num($dto).'"> %';
    print '</td></tr>';

    //Buy X Pay Y
    print '<tr class="qtybuy"><td class="fieldrequired">'.$langs->trans("Buy").'</td><td>';
    print '<input class="flat" name="qtybuy" size="5" maxlength="2" value="'.price2num($qtybuy).'"> '.$langs->trans("Units");
    print '</td></tr>';

    //Buy X Pay Y
    print '<tr class="qtypay"><td class="fieldrequired">'.$langs->trans("Pay").'</td><td>';
    print '<input class="flat" name="qtypay" size="5" maxlength="2" value="'.price2num($qtypay).'"> '.$langs->trans("Units");
    print '</td></tr>';

    //Product
    if ($action == 'create' || ($action=='update' && empty($category))) {
        if ($object->type == 0) {
            print '<tr class="individual"><td>' . $langs->trans("Customer") . '</td><td>';
            print $form->select_company($individual, 'individual', 'client > 0', 1);
            print '</td></tr>';
        }
        else {
            print '<tr class="individual"><td>' . $langs->trans("Product") . '</td><td>';
            $form->select_produits($individual, 'individual');
            print '</td></tr>';
        }
    }

    //Category
    if (! empty($conf->categorie->enabled)) {
        if ($object->type == 0) {
            if ($action == 'create' || ($action=='update' && empty($individual))) {
                print '<tr id="category" class="category"><td>' . $langs->trans("CustomersCategoryShort") . '</td><td>';
                print $form->select_all_categories(Categorie::TYPE_CUSTOMER, $category, "category");
                print '</td></tr>';
            }
        }
        else{
            print '<tr id="category" class="category"><td>' . $langs->trans("ProductsCategoryShort") . '</td><td>';
            print $form->select_all_categories(Categorie::TYPE_PRODUCT, $category, "category");
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

    print '<span class="center"><br><input type="submit" class="button" value="'.$langs->trans("Save").'">&nbsp;';
    print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'"></span>';

    print '<br></form>';

}
else
{
    if (! empty($user->rights->discounts->create))
    {
        print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$id.'&amp;action=create&amp;type1='.$type1.'">'.$langs->trans("NewDiscount").'</a>';
    }
}

print '</div>';

print '</br>';

print '<table class="notopnoleftnoright" width="100%">';
print '<tr class="liste_titre">';

print '<td width="30%">'.$langs->trans("Description").'</td>';
print '<td>'.$langs->trans("DiscountType").'</td>';
print '<td>'.$langs->trans("dtocom").'</td>';
print '<td>'.$langs->trans("AffectedTo").'</td>';
print '<td>'.$langs->trans("User").'</td>';
if ($conf->global->DIS_APPLY == 3) {
    print '<td>' . $langs->trans("Priority") . '</td>';
}
print '<td>'.$langs->trans("DateCreation").'</td>';
print '<td>' . $langs->trans("DateStart") . '</td>';
print '<td>' . $langs->trans("DateEnd") . '</td>';
print '<td></td>';

print '</tr>';

$result = $discount->fetch_all(3,$id);
$i=0;
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
        if ($result[$i]['fk_source'] == $id && $result[$i]['type_source'] == Discounts::SOURCE_CATEGORY) {
            if ($result[$i]["fk_target"] > 0) {
                if ($result[$i]["type_target"] == Discounts::SOURCE_CATEGORY) {
                    $cat = new Categorie($db);
                    $cat->fetch($result[$i]["fk_target"]);
                    //$cat->color = 'fff';
					print '<td><span class="noborderoncategories" '.($cat->color?' style="background: #'.$cat->color.';"':' style="background: #aaa"').'>';
					print $cat->getNomUrl(1);
					print '</span></td>';

                } else {
                    if ($result[$i]["type_target"] == Discounts::SOURCE_PRODUCT) {
                        $cat = new Product($db);
                        $cat->fetch($result[$i]["fk_target"]);
						print '<td  nowrap="nowrap" >' . $cat->getNomUrl(1) . '</td>';
                    } else {
                        if ($result[$i]["type_target"] == Discounts::SOURCE_THIRD) {
                            $cat = new Societe($db);
                            $cat->fetch($result[$i]["fk_target"]);
							print '<td  nowrap="nowrap" >' . $cat->getNomUrl(1) . '</td>';
                        } else {
							if ($result[$i]["type_target"] == Discounts::SOURCE_INVOICE) {
								$cat = new Facture($db);
								$cat->fetch($result[$i]["fk_target"]);
								print '<td  nowrap="nowrap" >' . $cat->getNomUrl(1) . '</td>';
							}
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
				//$cat->color = 'fff';
				print '<td><span class="noborderoncategories" '.($cat->color?' style="background: #'.$cat->color.';"':' style="background: #aaa"').'>';
				print $cat->getNomUrl(1);
				print '</span></td>';

            } else {
                if ($result[$i]["type_source"] == Discounts::SOURCE_PRODUCT) {
                    $cat = new Product($db);
                    $cat->fetch($result[$i]["fk_source"]);
					print '<td  nowrap="nowrap" >' . $cat->getNomUrl(1) . '</td>';
                } else {
                    if ($result[$i]["type_source"] == Discounts::SOURCE_THIRD) {
                        $cat = new Societe($db);
                        $cat->fetch($result[$i]["fk_source"]);
						print '<td  nowrap="nowrap" >' . $cat->getNomUrl(1) . '</td>';
                    } else {
						if ($result[$i]["type_source"] == Discounts::SOURCE_INVOICE) {
							$cat = new Facture($db);
							$cat->fetch($result[$i]["fk_source"]);
							print '<td  nowrap="nowrap" >' . $cat->getNomUrl(1) . '</td>';
						}
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
            print '<a href="' . $_SERVER["PHP_SELF"] . '?action=edit_discount&amp;dtoid=' . $result[$i]['id'] . '&amp;id=' . $id . '&type_dto=' . $result[$i]['type_dto'] . '&amp;type1='.$type1.'">';
            print img_edit();
            print '</a>';
            print ' <a href="' . $_SERVER["PHP_SELF"] . '?action=delete_discount&amp;dtoid=' . $result[$i]['id'] . '&amp;id=' . $id . '&amp;type1='.$type1.'">';
            print img_delete();
            print '</a>';
        }
        print '</td>';

        print '</tr>';

        $i++;

    }
}

llxFooter();

$db->close();
