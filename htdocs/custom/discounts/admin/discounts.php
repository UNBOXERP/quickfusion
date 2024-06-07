<?php
/* Copyright (C) 2012      		Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2013-2017      Ferran Marcet        <fmarcet@2byte.es>
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

/**
 *	\file       htdocs/labelprint/admin/labelprint.php
 *	\ingroup    products
 *	\brief      labels module setup page
 */

$res = @include("../../main.inc.php");                    // For root directory
if (!$res) $res = @include("../../../main.inc.php");        // For "custom" directory

require_once(DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php");
dol_include_once("/discounts/lib/discounts.lib.php");

global $langs, $user, $db, $conf;

$langs->load("admin");
$langs->load("discounts@discounts");

$action = GETPOST('action', 'alpha');
$value = GETPOST('value', 'int');

if (!$user->admin) accessforbidden();

//preferencias ofertas: ambas, productos, clientes

/*
 * Actions
 */
if (GETPOST("save")) {
    $db->begin();

    $res = 0;

	$res+=dolibarr_set_const($db,'DIS_APPLY',trim(GETPOST("disApply")),'chaine',0,'',$conf->entity);
    $res+=dolibarr_set_const($db,'DISCOUNT_SHOW',trim(GETPOST("showDiscount")),'chaine',0,'',$conf->entity);
	$res+=dolibarr_set_const($db,'DISCOUNT_NO_GROUP_LINES',trim(GETPOST("groupLines")),'chaine',0,'',$conf->entity);

	if ($res >= 1)
	{
		$db->commit();
		setEventMessage($langs->trans("SetupSaved"));
	}
	else
	{
		$db->rollback();
		setEventMessage($langs->trans("Error"),"errors");
		header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
}

/*
 * 	View
 */

clearstatcache();

// read const
$form=new Form($db);

//$helpurl='EN:Module_Labels|FR:Module_Labels_FR|ES:M&oacute;dulo_Labels';
llxHeader('',$langs->trans("DiscountsSetup"),$helpurl);

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($langs->trans("DiscountsSetup"),$linkback,'title_setup');


$head = discountsadmin_prepare_head();

dol_fiche_head($head, 'configuration', $langs->trans("Discounts"), 0, 'barcode');

dol_htmloutput_events();

//Show in
print '<form name="catalogconfig" action="' . $_SERVER["PHP_SELF"] . '" method="post">';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';

/*
 *  General Optiones
*/

$html = new Form($db);
print load_fiche_titre($langs->trans("ShowOptions"));
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Parameter") . '</td>';
print '<td align="center" width="60">' . $langs->trans("Value") . '</td>';
print "</tr>\n";

$array[0] = $langs->trans("All");
//$array[1] = $langs->trans("Customers");
//$array[2] = $langs->trans("Products");
$array[3] = $langs->trans("ByPriority");

// Show Rules to apply
print '<tr class="oddeven">';
print "<td>" . $langs->trans("RulesToApply") . "</td>";
print '<td>';
print $html->selectarray("disApply", $array, $conf->global->DIS_APPLY);
print '</td>';
print "</tr>";

$array2[1] = $langs->trans("TypeShowDiscount1");
$array2[2] = $langs->trans("TypeShowDiscount2");

print '<tr class="oddeven">';
print "<td>".$langs->trans("ShowDiscount")."</td>";
print '<td>';
print $html->selectarray("showDiscount", $array2, $conf->global->DISCOUNT_SHOW);
print '</td>';
print "</tr>";

$array3[0] = $langs->trans("OptionGroupLines");
$array3[1] = $langs->trans("OptionNoGroupLines");

print '<tr class="oddeven">';
print "<td>".$langs->trans("GroupLinesSameProduct")."</td>";
print '<td>';
print $html->selectarray("groupLines", $array3, $conf->global->DISCOUNT_NO_GROUP_LINES);
print '</td>';
print "</tr>";

print '</table>';

print '<br><div style="text-align: center">';
print '<input type="submit" name="save" class="button" value="' . $langs->trans("Save") . '">';
print "</div>";
print "</form>\n";


dol_fiche_end();

$db->close();

llxFooter();
