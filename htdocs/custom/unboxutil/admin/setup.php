<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2022 Alan Montoya UBE <info@bsrsupply.us>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    maringscustom/admin/setup.php
 * \ingroup maringscustom
 * \brief   Maringscustom setup page.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/unboxutil.lib.php';
//require_once "../class/myclass.class.php";
global $conf;
// Translations
$langs->loadLangs(array("admin", "unboxutil@unboxutil"));

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('unboxutilsetup', 'globalsetup'));

// Access control
if (!$user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');
$modulepart = GETPOST('modulepart', 'aZ09');    // Used by actions_setmoduleoptions.inc.php

$value = GETPOST('value', 'alpha');
$label = GETPOST('label', 'alpha');
$scandir = GETPOST('scan_dir', 'alpha');
$type = 'myobject';

$arrayofparameters = array(
	'MARINGSCUSTOM_MYPARAM1' => array('type' => 'string', 'css' => 'minwidth500', 'enabled' => 1),
	'MARINGSCUSTOM_MYPARAM2' => array('type' => 'textarea', 'enabled' => 1),
	//'MARINGSCUSTOM_MYPARAM3'=>array('type'=>'category:'.Categorie::TYPE_CUSTOMER, 'enabled'=>1),
	//'MARINGSCUSTOM_MYPARAM4'=>array('type'=>'emailtemplate:thirdparty', 'enabled'=>1),
	//'MARINGSCUSTOM_MYPARAM5'=>array('type'=>'yesno', 'enabled'=>1),
	//'MARINGSCUSTOM_MYPARAM5'=>array('type'=>'thirdparty_type', 'enabled'=>1),
	//'MARINGSCUSTOM_MYPARAM6'=>array('type'=>'securekey', 'enabled'=>1),
	//'MARINGSCUSTOM_MYPARAM7'=>array('type'=>'product', 'enabled'=>1),
);

$error = 0;
$setupnotempty = 0;

// Set this to 1 to use the factory to manage constants. Warning, the generated module will be compatible with version v15+ only
$useFormSetup = 0;
// Convert arrayofparameter into a formSetup object


/*
 * Actions
 */



/*
 * View
 */

$form = new Form($db);

$help_url = '';
$page_name = "UnboxutilSetup";

llxHeader('', $langs->trans($page_name), $help_url);

// Subheader
$linkback = '<a href="' . ($backtopage ? $backtopage : DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">' . $langs->trans("BackToModuleList") . '</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = unboxutilAdminPrepareHead();
print dol_get_fiche_head($head, 'settings', $langs->trans($page_name), -1, "maringscustom@maringscustom");

// Setup page goes here
echo '<span class="opacitymedium">' . $langs->trans("UnoxutilcustomSetupPage") . '</span><br><br>';

print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="update">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td class="titlefield">' . $langs->trans("Parameter") . '</td><td>' . $langs->trans("Value") . '</td></tr>';
print '<tr class="oddeven">';
print '<td>';
print $form->textwithpicto( $langs->trans("UBUTIL_NOCUSTOMPRODUCT"), $langs->trans("UBUTIL_NOCUSTOMPRODUCT"));
print '</td>';
print '<td class="titlefield">';
if ($conf->use_javascript_ajax) {
	print ajax_constantonoff('UBUTIL_NOCUSTOMPRODUCT');
} else {
	$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
	print $form->selectarray("UBUTIL_NOCUSTOMPRODUCT", $arrval, $conf->global->UBUTIL_NOCUSTOMPRODUCT);
}
print "</td>\n";
print "</tr>\n";

print '<tr class="odd">';
print '<td>';
print $form->textwithpicto( $langs->trans("UBUTIL_FACTUREPAIDLIST"), $langs->trans("UBUTIL_FACTUREPAIDLIST"));
print '</td>';
print '<td class="titlefield">';
if ($conf->use_javascript_ajax) {
	print ajax_constantonoff('UBUTIL_FACTUREPAIDLIST');
} else {
	$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
	print $form->selectarray("UBUTIL_FACTUREPAIDLIST", $arrval, $conf->global->UBUTIL_FACTUREPAIDLIST);
}
print "</td>\n";
print "</tr>\n";

print '<tr class="oddeven">';
print '<td class="titlefield">';
print $form->textwithpicto( $langs->trans("UBUTIL_CORRECTSTOCK"), $langs->trans("UBUTIL_CORRECTSTOCK"));
print '</td>';
print '<td class="titlefield">';
if ($conf->use_javascript_ajax) {
	print ajax_constantonoff('UBUTIL_CORRECTSTOCK');

} else {
	$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
	print $form->selectarray("UBUTIL_CORRECTSTOCK", $arrval, $conf->global->UBUTIL_CORRECTSTOCK);
}
print "</td>\n";
print "</tr>\n";

print '</table>';

/*	print '<br><div class="center">';
	print '<input class="button button-save" type="submit" value="' . $langs->trans("Save") . '">';
	print '</div>';*/

print '</form>';


print '<br>';




// Page end
print dol_get_fiche_end();

llxFooter();
$db->close();
