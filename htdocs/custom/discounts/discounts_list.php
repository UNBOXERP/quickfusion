<?php
/* Copyright (C) 2001-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005      Brice Davoleau       <brice.davoleau@gmail.com>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2006-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2007      Patrick Raguin  		<patrick.raguin@gmail.com>
 * Copyright (C) 2014      Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2015 		Ferran Marcet 		<fmarcet@2byte.es>
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

$res=@include("../main.inc.php");					// For root directory
if (! $res) $res=@include("../../main.inc.php");	// For "custom" directory
require_once DOL_DOCUMENT_ROOT . '/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
dol_include_once('/discounts/class/discounts.class.php');

global $langs, $user, $db, $conf;

$langs->load("companies");
$langs->load("bills");
$langs->load("discounts@discounts");

// Security check
$ref = GETPOST('ref','alpha');
$action = GETPOST('action','alpha');
$cancel = GETPOST('cancel','alpha');
$type= GETPOST('type','int');
$desc=GETPOST('desc','alpha');
$dto =GETPOST('dtotoaply');
$category=GETPOST("category","int");
$qtybuy = GETPOST("qtybuy","int");
$qtypay = GETPOST("qtypay","int");
$date_start=dol_mktime(0,0,0,$_POST['date_startmonth'],$_POST['date_startday'],$_POST['date_startyear']);
$date_end=dol_mktime(0,0,0,$_POST['date_endmonth'],$_POST['date_endday'],$_POST['date_endyear']);
$discountCheck = GETPOST('discountCheck');

$confirm = GETPOST("confirm",'alpha');

$sref=GETPOST("sref");

$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page",'int');
if ($page == -1) { $page = 0; }
$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortfield) $sortfield="priority";
if (! $sortorder) $sortorder="ASC";

$limit = $conf->liste_limit;

if ($user->socid) $socid=$user->socid;
$result = restrictedArea($user, 'societe', $socid, '&societe');

$discount = new Discounts($db);

$product_static = new Product($db);
$societe_static = new Societe($db);
$invoice_static = new Facture($db);
$cat_static = new Categorie($db);

if( $action==='confirm_deleteline' && $confirm === 'yes' && $user->rights->discounts->create )
{
    $dtoid = GETPOST('dtoid');
    $res = $discount->fetch($dtoid);
    if( $res == 1 )
    {
        $res = $discount->delete($user);
        if( $res<1 ) {
            setEventMessage($discount->error, 'errors');
        }
        else {
            setEventMessage($langs->trans("DiscountDeleted"));
        }

        $action='';
    }
}
if ($action == 'list'){
    $priority = GETPOST('priority');

    $db->begin();

    if (!empty($priority)) {
        foreach ($priority as $key => $value) {

            $sql = "UPDATE " . MAIN_DB_PREFIX . "discount SET priority= " . $value . " WHERE rowid = " . $key;
            $res = $db->query($sql);
            if ($res <= 0) {
                $error++;
            }
        }
    }
    if ($error == 0) {
        $db->commit();
        setEventMessage($langs->trans("CorrectlyUpdated"));
    } else {
        $db->rollback();
        setEventMessage($object->error, $object->errors, 'errors');
    }

    $action = '';
}

if($discountCheck){

    for($i=0; $i<count($discountCheck); $i++) {
		$sql = 'UPDATE ' . MAIN_DB_PREFIX . 'discount SET active = 1 WHERE rowid = ' . $discountCheck[$i];
		$res = $db->query($sql);
		if ($res) {
			$db->commit();
		} else {
			$db->rollback();
			setEventMessage('Error');
			return -1;
		}
	}

	$sql1 = 'SELECT rowid FROM ' . MAIN_DB_PREFIX . 'discount';
	$resql1 = $db->query($sql1);

	if ($resql1) {
		$num1 = $db->num_rows($resql1);
		$h=0;

		while ($h < $num1) {
			$objp1 = $db->fetch_object($resql1);
			if(!in_array($objp1->rowid,$discountCheck)){
				$sql2 = 'UPDATE ' . MAIN_DB_PREFIX . 'discount SET active = 0 WHERE rowid = ' . $objp1->rowid;
				$res2 = $db->query($sql2);
				if ($res2) {
					$db->commit();
				} else {
					$db->rollback();
					setEventMessage('Error');
					return -1;
				}
			}
			$h++;
		}
	}
}
elseif(GETPOST('button')){

	$sql = 'UPDATE ' . MAIN_DB_PREFIX . 'discount SET active = 0';
	$res = $db->query($sql);
	if ($res) {
		$db->commit();
	} else {
		$db->rollback();
		setEventMessage('Error');
		return -1;
	}
}

if (isset($_POST["button_removefilter_x"]))
{
    $sref="";
}

/*
 *	View
 */

$htmlother=new FormOther($db);

$title=$langs->trans('Module400027Name');


$sql = "SELECT";
$sql.= " d.rowid";
$sql.= ", d.type_dto";
$sql.= ", d.type_source";
$sql.= ", d.description";
$sql.= ", d.dto_rate";
$sql.= ", d.fk_source";
$sql.= ", d.payment_cond";
$sql.= ", d.datec";
$sql.= ", d.date_start";
$sql.= ", d.date_end";
$sql.= ", d.fk_user_author";
$sql.= ", d.fk_target";
$sql.= ", d.type_target";
$sql.= ", d.qtybuy";
$sql.= ", d.qtypay";
$sql.= ", d.payment_cond";
$sql.= ", d.priority";
$sql.= ", d.active";
$sql.= " FROM ".MAIN_DB_PREFIX."discount AS d";
$sql.= " WHERE d.entity = ".$conf->entity;

$sql.= $db->order($sortfield,$sortorder);
$sql.= $db->plimit($limit + 1, $offset);

dol_syslog("sql=".$sql);
$resql = $db->query($sql);

if ($resql) {
    $num = $db->num_rows($resql);
}

llxHeader('',$title,'','');

$form = new Form($db);

// Protection if external user
if ($user->socid > 0)
{
    accessforbidden();
}

$formconfirm ='';
// Confirmation to delete line
if ($action === 'delete_discount')
{
    $formconfirm=$form->formconfirm($_SERVER["PHP_SELF"].'?dtoid=' . GETPOST("dtoid",'int'), $langs->trans('DeleteDiscountLine'), $langs->trans('ConfirmDeleteDiscountLine'), 'confirm_deleteline', '', 0, 1);
}
print $formconfirm;


$param="&amp;sref=".$sref;
$param.=($sref?"&amp;sref=".sref:"");

print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder,'',$num);

print '<form action="' . $_SERVER["PHP_SELF"] .' " method="post" name="formulaire">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
print '<input type="hidden" name="type" value="'.$type.'">';

print '</br>';

//if ($conf->global->DIS_APPLY == 3) {
    print '<div class="right">';
    print '<input type="submit" class="button" name="save" value="' . $langs->trans("Save") . '">';
    print '</div>';
//}

print '<table class="notopnoleftnoright" width="100%">';

// Filter on categories
$moreforfilter='';

if ($moreforfilter)
{
    print '<tr class="liste_titre">';
    print '<td class="liste_titre" colspan="9">';
    print $moreforfilter;
    print '</td><td></td></tr>';
}
print '<tr class="liste_titre">';

print_liste_field_titre($langs->trans("Ref"), $_SERVER["PHP_SELF"], "ref",$param,"","",$sortfield,$sortorder);
print_liste_field_titre($langs->trans("Description"), $_SERVER["PHP_SELF"], "description",$param,"","",$sortfield,$sortorder);
print_liste_field_titre($langs->trans("DiscountType"), $_SERVER["PHP_SELF"], "type_dto",$param,"","",$sortfield,$sortorder);
print '<td class="liste_titre">'.$langs->trans("dtocom").'</td>';
print_liste_field_titre($langs->trans("AffectedTo"), $_SERVER["PHP_SELF"], "",$param,"","",$sortfield,$sortorder);
print_liste_field_titre($langs->trans("User"), $_SERVER["PHP_SELF"], "fk_user_author",$param,"","",$sortfield,$sortorder);
if ($conf->global->DIS_APPLY == 3) {
    print_liste_field_titre($langs->trans("Priority"), $_SERVER["PHP_SELF"], "priority", $param, "", "", $sortfield,
        $sortorder);
}
print_liste_field_titre($langs->trans("DateCreation"), $_SERVER["PHP_SELF"], "datec",$param,"","",$sortfield,$sortorder);
print_liste_field_titre($langs->trans("DateStart"), $_SERVER["PHP_SELF"], "date_start",$param,"","",$sortfield,$sortorder);
print_liste_field_titre($langs->trans("DateEnd"), $_SERVER["PHP_SELF"], "date_end",$param,"","",$sortfield,$sortorder);
print '<td class="liste_titre">';
print '&nbsp;';
print '</td><td class="liste_titre"></td>';

print '</tr>';

print '<tr class="liste_titre">';
print '<td class="liste_titre" align="left">';
print '&nbsp;';
print '</td>';
print '<td class="liste_titre" colspan="6">';
print '&nbsp;';
print '</td>';

if ($conf->global->DIS_APPLY == 3) {
    print '<td class="liste_titre">';
    print '&nbsp;';
    print '</td>';
}

print '<td class="liste_titre" align="left"></td><td class="liste_titre" align="right">';
print '<td class="liste_titre" align="left"></td><td class="liste_titre" align="right">';
print '<input type="image" class="liste_titre" name="button_search" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
print '<input type="image" class="liste_titre" name="button_removefilter" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/searchclear.png" value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
print '</td>';
print '</tr>';

$var=true;
$i=0;

while ($i < min($num,$limit))
{
    $objp = $db->fetch_object($resql);

    $var = !$var;

    print '<tr ' . $bc[$var] . '>';
    //Ref

    if($objp->type_source == Discounts::SOURCE_THIRD) {
        $societe_static->fetch($objp->fk_source);

        print '<td nowrap="nowrap">';
        print $societe_static->getNomUrl(1);
        print '</td>';
    }
    elseif($objp->type_source == Discounts::SOURCE_PRODUCT) {
        $product_static->fetch($objp->fk_source);

        print '<td nowrap="nowrap">';
        print $product_static->getNomUrl(1);
        print '</td>';
    }
    elseif($objp->type_source == Discounts::SOURCE_INVOICE) {
        $invoice_static->fetch($objp->fk_source);

        print '<td nowrap="nowrap">';
        print $invoice_static->getNomUrl(1);
        print '</td>';
    }
    else{
        $cat_static->fetch($objp->fk_source);
        //$cat_static->color = 'fff';
        print '<td>';
		print '<span class="noborderoncategories" '.($cat_static->color?' style="background: #'.$cat_static->color.';"':' style="background: #aaa"').'>';
        print $cat_static->getNomUrl(1);
        print '</span></td>';
    }

    print '<td  nowrap="nowrap">' . $objp->description . '</td>';

    print '<td  nowrap="nowrap">' . $discount->getLabelTypeDto($objp->type_dto) . '</td>';

    //Rate
    if ($objp->type_dto == 4) {
        print '<td  nowrap="nowrap">' . $objp->qtybuy . 'x' . $objp->qtypay . '</td>';
    } else {
        print '<td  nowrap="nowrap">' . price2num($objp->dto_rate) . ' %</td>';
    }

    if($objp->type_target == Discounts::SOURCE_THIRD) {
        $societe_static->fetch($objp->fk_target);

        print '<td nowrap="nowrap">';
        print $societe_static->getNomUrl(1);
        print '</td>';
    }
    else if($objp->type_target == Discounts::SOURCE_PRODUCT) {
        $product_static->fetch($objp->fk_target);

        print '<td nowrap="nowrap">';
        print $product_static->getNomUrl(1);
        print '</td>';
    }
    else if($objp->type_target == Discounts::SOURCE_CATEGORY) {
        $cat_static->fetch($objp->fk_target);
        //$cat_static->color = 'fff';
        print '<td>';
		print '<span class="noborderoncategories" '.($cat_static->color?' style="background: #'.$cat_static->color.';"':' style="background: #aaa"').'>';
        print $cat_static->getNomUrl(1);
        print '</span></td>';
    }
    else {
        print '<td>&nbsp;</td>';
    }

    print '<td>';
    $userstatic = new User($db);
    $userstatic->fetch($objp->fk_user_author);
    print $userstatic->getNomUrl(1);
    print '</td>';

    if ($conf->global->DIS_APPLY == 3) {
        //Priority
        print '<td><input class="flat" name="priority[' . $objp->rowid . ']" size="5" maxlength="2" value="' . price2num($objp->priority) . '"></td> ';
    }

    //DateCreation
    print '<td nowrap="nowrap">' . dol_print_date($db->jdate($objp->datec), "dayhour") . "</td>\n";
	//DateStart
	print '<td nowrap="nowrap">' . dol_print_date($db->jdate($objp->date_start), "day") . "</td>\n";
	//DateEnd
	print '<td nowrap="nowrap">' . dol_print_date($db->jdate($objp->date_end), "day") . "</td>\n";

	print '<td align="right">';
	if ($user->rights->discounts->create) {
	    if($objp->active==1){
			print '<input type="checkbox" name="discountCheck[]" value="'.$objp->rowid.'" checked>';
        }
        else{
			print '<input type="checkbox" name="discountCheck[]" value="'.$objp->rowid.'">';
        }
	}
	print '</td>';

    print '<td align="right">';
    //print '<a href="' . $_SERVER["PHP_SELF"] . '?action=edit_discount&amp;dtoid=' . $objp->id . '&amp;prodid=' . $prodid . '&type=' . $objp->type_dto . '">';
    //print img_edit();
    //print '</a>';
    if ($user->rights->discounts->create) {
        print ' <a href="' . $_SERVER["PHP_SELF"] . '?action=delete_discount&amp;dtoid=' . $objp->rowid . '">';
        print img_delete();
        print '</a>';
    }
    print '</td>';

    print '</tr>';

    $i++;

}
print '</table></form>';

//dol_htmloutput_mesg($mesg);
llxFooter();

$db->close();
?>
