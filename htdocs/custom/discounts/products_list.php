<?php
/* Copyright (C) 2001-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005      Brice Davoleau       <brice.davoleau@gmail.com>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2006-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2007      Patrick Raguin  		<patrick.raguin@gmail.com>
 * Copyright (C) 2014      Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2015-2017 Ferran Marcet 		<fmarcet@2byte.es>
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
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
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

$confirm = GETPOST("confirm",'alpha');

$sref=GETPOST("sref");
$snom=GETPOST("snom");
$search_categ = GETPOST("search_categ",'int');

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
$cat_static = new Categorie($db);

if( $action==='confirm_deleteline' && $confirm === 'yes' && $user->rights->discounts->create )
{
    $dtoid = GETPOST('dtoid');
    $res = $discount->fetch($dtoid);
    if( $res == 1 )
    {
        $res = $discount->delete($user);
        if( $res<1 ) {
            setEventMessages('',$discount->errors, 'errors');
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

if (isset($_POST["button_removefilter_x"]))
{
    $sref="";
    $snom="";
    $search_categ=0;
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
$sql.= ", p.ref";
$sql.= ", p.rowid as prodid";
$sql.= ", 1 AS product";
$sql.= " FROM ".MAIN_DB_PREFIX."discount AS d";
$sql.= " , ".MAIN_DB_PREFIX."product AS p";

$sql.= " WHERE ((d.type_source = ".Discounts::SOURCE_PRODUCT." AND p.rowid=d.fk_source) OR (p.rowid=d.fk_target AND d.type_target = ".Discounts::SOURCE_PRODUCT."))";
$sql.= " AND d.entity = ".$conf->entity;

if ($sref)     $sql.= " AND p.ref LIKE '%".$sref."%'";

$sql.= " UNION SELECT";
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
$sql.= ", p.label AS ref";
$sql.= ", p.rowid AS prodid";
$sql.= ", 0 AS product";
$sql.= " FROM ".MAIN_DB_PREFIX."discount AS d,";
$sql.= " ".MAIN_DB_PREFIX."categorie AS p INNER JOIN ".MAIN_DB_PREFIX."categorie_product AS cs ON cs.fk_categorie = p.rowid";
$sql.= " WHERE ((d.type_source = ".Discounts::SOURCE_CATEGORY." AND p.rowid = d.fk_source) OR (p.rowid = d.fk_target AND d.type_target = ".Discounts::SOURCE_CATEGORY."))";
$sql.= " AND d.entity = ".$conf->entity;

if ($sref)     $sql.= " AND p.label LIKE '%".$sref."%'";

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

if ($conf->global->DIS_APPLY == 3) {
    print '<div class="right">';
    print '<input type="submit" class="button" name="save" value="' . $langs->trans("Save") . '">';
    print '</div>';
}

print '<table class="notopnoleftnoright" width="100%">';

// Filter on categories
$moreforfilter='';

if ($moreforfilter)
{
    print '<tr class="liste_titre">';
    print '<td class="liste_titre" colspan="9">';
    print $moreforfilter;
    print '</td></tr>';
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
print '</td>';

print '</tr>';

print '<tr class="liste_titre">';
print '<td class="liste_titre" align="left">';
print '<input class="flat" type="text" name="sref" size="8" value="'.$sref.'">';
print '</td>';
print '<td class="liste_titre" colspan="6">';
print '&nbsp;';
print '</td>';

if ($conf->global->DIS_APPLY == 3) {
    print '<td class="liste_titre">';
    print '&nbsp;';
    print '</td>';
}

print '<td class="liste_titre" align="left"></td><td class="liste_titre" align="right"></td>';
print '<td class="liste_titre" align="right">';
print '<input type="image" class="liste_titre" name="button_search" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
print '<input type="image" class="liste_titre" name="button_removefilter" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/searchclear.png" value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
print '</td>';
print '</tr>';

$var=true;
$i=0;

while ($i < min($num,$limit)) {
    $objp = $db->fetch_object($resql);

    $var = !$var;

    print '<tr ' . $bc[$var] . '>';
    //Ref

    if ($objp->product == 1) {
        $product_static->fetch($objp->prodid);

        print '<td nowrap="nowrap">';
        print $product_static->getNomUrl(1, '', 24);
        print '</td>';
    } else {
        $cat_static->id = $objp->prodid;
        $cat_static->label = $objp->ref;
        $cat_static->type = Categorie::TYPE_PRODUCT;
        $cat_static->fetch($cat_static->id,'');
        //$cat_static->color = 'fff';
        print '<td>';
		print '<span class="noborderoncategories" '.($cat_static->color?' style="background: #'.$cat_static->color.';"':' style="background: #aaa"').'>';
        print $cat_static->getNomUrl(1);
        print '</span></td>';
    }
    $modo = 'inverso';
    if ($objp->prodid == $objp->fk_source)
        $modo = 'normal';

    print '<td  nowrap="nowrap">' . $objp->description . '</td>';

    print '<td  nowrap="nowrap">' . $discount->getLabelTypeDto($objp->type_dto) . '</td>';

    //Rate
    if ($objp->type_dto == 4) {
        print '<td  nowrap="nowrap">' . $objp->qtybuy . 'x' . $objp->qtypay . '</td>';
    } else {
        print '<td  nowrap="nowrap">' . price2num($objp->dto_rate) . ' %</td>';
    }

    //Category
    if ($modo == 'normal') {
        if ($objp->fk_target > 0) {
            if ($objp->type_target == Discounts::SOURCE_CATEGORY) {
                $cat = new Categorie($db);
                $cat->fetch($objp->fk_target);
				print '<td  nowrap="nowrap"><span class="noborderoncategories" '.($cat->color?' style="background: #'.$cat->color.';"':' style="background: #aaa"').'>';
				print $cat->getNomUrl(1);
				print '</span></td>';
            } else {
                $cat = new Societe($db);
                $cat->fetch($objp->fk_target);
				print '<td  nowrap="nowrap">' . $cat->getNomUrl(1) . '</td>';
            }
        } else {
            print '<td nowrap="nowrap"></td>';
        }
    }
    else {
        if ($objp->fk_source > 0) {
            if ($objp->type_source == Discounts::SOURCE_CATEGORY) {
                $cat = new Categorie($db);
                $cat->fetch($objp->fk_source);
				print '<td  nowrap="nowrap"><span class="noborderoncategories" '.($cat->color?' style="background: #'.$cat->color.';"':' style="background: #aaa"').'>';
				print $cat->getNomUrl(1);
				print '</span></td>';
            } else {
                $cat = new Societe($db);
                $cat->fetch($objp->fk_source);
				print '<td  nowrap="nowrap">' . $cat->getNomUrl(1) . '</td>';
            }
        } else {
            print '<td nowrap="nowrap"></td>';
        }
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

print '</form>';

//dol_htmloutput_mesg($mesg);
llxFooter();

$db->close();
