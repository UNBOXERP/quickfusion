<?php

/*
 * Copyright (C) 2018-2021 ProgSI (contact@progsi.ma)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
// 
// -- mode dév
// -- ATTENTION : - le répertoire source doit autoriser la lecture/ecriture pour l'utilisateur www-data (ou other)
//                  car rsync transfert ces permission au répertoire dest, ce qui est nécessaire pour pouvoir modifier cette dest
//                - un trailing "/" est necessaire dans la source
//                - le répertoire dest doit exister
// synchronisation du module KanView local avec celui en cours de dév
//if (strpos($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 'localhost/progsi/dolibarrs/dolibarr_140n_tests/htdocs/custom/kanview/') !== false) {
//	// kanview dev vers kanview test
//	$source1 = "/home/hamid/f/www/dolibarrs/dolibarr_500_KanView/htdocs/custom/kanview/";
//	$dest1	 = "/home/hamid/f/www/dolibarrs/dolibarr_140n_tests/htdocs/custom/kanview";
//	$res1		 = exec("rsync -avr --exclude '.git' " . $source1 . " " . $dest1, $output, $retVar);
//	// var_dump($res1);	
//}
// 
/**
 * \file config
 * \ingroup config
 * \brief config
 */

$res	 = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"]))
	$res	 = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp	 = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2	 = realpath(__FILE__);
$i		 = strlen($tmp) - 1;
$j		 = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php"))
	$res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php"))
	$res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php"))
	$res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php"))
	$res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php"))
	$res = @include "../../../main.inc.php";
if (!$res)
	die("Include of main fails");

include_once dol_buildpath('/kanview/init.inc.php');

// Protection (if external user for example)
if (!($conf->kanview->enabled && $user->admin)) {
	accessforbidden();
	exit();
}

require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

$build = str_replace('.', '', KANVIEW_VERSION);

$langs->load("kanview@kanview");
$langs->load("admin");
$langs->load('other');

// locale et rtl pour syncfusion controls
$rtl		 = "false";
$locale	 = str_replace('_', '-', $langs->defaultlang);
if (strpos($locale, 'ar-') !== false)
	$rtl		 = "true";
///

$nomenu			 = GETPOST('nomenu', 'int');
$action			 = GETPOST('action', 'alpha'); // possible actions : 'setprop1' | 'updateoptions'
$value			 = GETPOST('value', 'alpha');
$module_nom	 = 'kanview'; // utilisé par les actions des modèles pdf et la table " . MAIN_DB_PREFIX . "document_model

$hasNumberingGenerator = false;
$hasDocGenerator			 = false;

if (!empty($nomenu)) {
	$conf->dol_hide_topmenu	 = 1;
	$conf->dol_hide_leftmenu = 1;
}

/* * ************************************************************************************************
 *
 * ------------------------------------------ Actions
 *
 * ************************************************************************************************ */

// création
// ------------------------- action to update

if ($action == 'updateoptions') {

//
// ------------- update of KANVIEW_HOME_PAGE
//
	if (GETPOST('submit_KANVIEW_HOME_PAGE')) {
		$newvalue	 = GETPOST('KANVIEW_HOME_PAGE');
		$res			 = dolibarr_set_const($db, "KANVIEW_HOME_PAGE", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_SHOW_PICTO
//
	if (GETPOST('submit_KANVIEW_SHOW_PICTO')) {
		$newvalue	 = GETPOST('KANVIEW_SHOW_PICTO');
		$res			 = dolibarr_set_const($db, "KANVIEW_SHOW_PICTO", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}

	//
// ------------- update of KANVIEW_FILTER_DEFAULT_DATE_START
//
	if (GETPOST('submit_KANVIEW_FILTER_DEFAULT_DATE_START')) {
		$newvalue	 = GETPOST('KANVIEW_FILTER_DEFAULT_DATE_START');
		$res			 = dolibarr_set_const($db, "KANVIEW_FILTER_DEFAULT_DATE_START", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}

	//
	// ------------- update of KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT
	//
	if (GETPOST('submit_KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT')) {
		$newvalue	 = GETPOST('KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT');
		$res			 = dolibarr_set_const($db, "KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT", $newvalue, 'yesno', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


	//
	// ------------- update of KANVIEW_PROJETS_SYCHRONIZE_OPP_PERCENT_WITH_STATUS
	//
	if (GETPOST('submit_KANVIEW_PROJETS_SYCHRONIZE_OPP_PERCENT_WITH_STATUS')) {
		$newvalue	 = GETPOST('KANVIEW_PROJETS_SYCHRONIZE_OPP_PERCENT_WITH_STATUS');
		$res			 = dolibarr_set_const($db, "KANVIEW_PROJETS_SYCHRONIZE_OPP_PERCENT_WITH_STATUS", $newvalue, 'yesno', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_PROJETS_TAG
//
	if (GETPOST('submit_KANVIEW_PROJETS_TAG')) {
		$newvalue	 = GETPOST('KANVIEW_PROJETS_TAG');
		$res			 = dolibarr_set_const($db, "KANVIEW_PROJETS_TAG", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_PROJETS_DRAFT_COLOR
//
	if (GETPOST('submit_KANVIEW_PROJETS_DRAFT_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_PROJETS_DRAFT_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_PROJETS_DRAFT_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_PROJETS_OPEN_COLOR
//
	if (GETPOST('submit_KANVIEW_PROJETS_OPEN_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_PROJETS_OPEN_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_PROJETS_OPEN_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_PROJETS_CLOSED_COLOR
//
	if (GETPOST('submit_KANVIEW_PROJETS_CLOSED_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_PROJETS_CLOSED_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_PROJETS_CLOSED_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_TASKS_TAG
//
	if (GETPOST('submit_KANVIEW_TASKS_TAG')) {
		$newvalue	 = GETPOST('KANVIEW_TASKS_TAG');
		$res			 = dolibarr_set_const($db, "KANVIEW_TASKS_TAG", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_TASKS_OK_COLOR
//
	if (GETPOST('submit_KANVIEW_TASKS_OK_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_TASKS_OK_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_TASKS_OK_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_TASKS_LATE1_COLOR
//
	if (GETPOST('submit_KANVIEW_TASKS_LATE1_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_TASKS_LATE1_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_TASKS_LATE1_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_TASKS_LATE2_COLOR
//
	if (GETPOST('submit_KANVIEW_TASKS_LATE2_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_TASKS_LATE2_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_TASKS_LATE2_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_PROPALS_TAG
//
	if (GETPOST('submit_KANVIEW_PROPALS_TAG')) {
		$newvalue	 = GETPOST('KANVIEW_PROPALS_TAG');
		$res			 = dolibarr_set_const($db, "KANVIEW_PROPALS_TAG", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_PROPALS_LATE1_COLOR
//
	if (GETPOST('submit_KANVIEW_PROPALS_LATE1_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_PROPALS_LATE1_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_PROPALS_LATE1_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_PROPALS_LATE2_COLOR
//
	if (GETPOST('submit_KANVIEW_PROPALS_LATE2_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_PROPALS_LATE2_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_PROPALS_LATE2_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_PROPALS_LATE3_COLOR
//
	if (GETPOST('submit_KANVIEW_PROPALS_LATE3_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_PROPALS_LATE3_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_PROPALS_LATE3_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_PROPALS_LATE4_COLOR
//
	if (GETPOST('submit_KANVIEW_PROPALS_LATE4_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_PROPALS_LATE4_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_PROPALS_LATE4_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_INVOICES_TAG
//
	if (GETPOST('submit_KANVIEW_INVOICES_TAG')) {
		$newvalue	 = GETPOST('KANVIEW_INVOICES_TAG');
		$res			 = dolibarr_set_const($db, "KANVIEW_INVOICES_TAG", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_INVOICES_LATE1_COLOR
//
	if (GETPOST('submit_KANVIEW_INVOICES_LATE1_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_INVOICES_LATE1_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_INVOICES_LATE1_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_INVOICES_LATE2_COLOR
//
	if (GETPOST('submit_KANVIEW_INVOICES_LATE2_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_INVOICES_LATE2_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_INVOICES_LATE2_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_INVOICES_LATE3_COLOR
//
	if (GETPOST('submit_KANVIEW_INVOICES_LATE3_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_INVOICES_LATE3_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_INVOICES_LATE3_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_ORDERS_TAG
//
	if (GETPOST('submit_KANVIEW_ORDERS_TAG')) {
		$newvalue	 = GETPOST('KANVIEW_ORDERS_TAG');
		$res			 = dolibarr_set_const($db, "KANVIEW_ORDERS_TAG", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_ORDERS_LATE1_COLOR
//
	if (GETPOST('submit_KANVIEW_ORDERS_LATE1_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_ORDERS_LATE1_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_ORDERS_LATE1_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_ORDERS_LATE2_COLOR
//
	if (GETPOST('submit_KANVIEW_ORDERS_LATE2_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_ORDERS_LATE2_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_ORDERS_LATE2_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_ORDERS_LATE3_COLOR
//
	if (GETPOST('submit_KANVIEW_ORDERS_LATE3_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_ORDERS_LATE3_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_ORDERS_LATE3_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_PROSPECTS_ADD_PROSPECTSCLIENTS
//
	if (GETPOST('submit_KANVIEW_PROSPECTS_ADD_PROSPECTSCLIENTS')) {
		$newvalue	 = GETPOST('KANVIEW_PROSPECTS_ADD_PROSPECTSCLIENTS');
		$res			 = dolibarr_set_const($db, "KANVIEW_PROSPECTS_ADD_PROSPECTSCLIENTS", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}

//
// ------------- update of KANVIEW_PROSPECTS_TAG
//
	if (GETPOST('submit_KANVIEW_PROSPECTS_TAG')) {
		$newvalue	 = GETPOST('KANVIEW_PROSPECTS_TAG');
		$res			 = dolibarr_set_const($db, "KANVIEW_PROSPECTS_TAG", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_PROSPECTS_PL_HIGH_COLOR
//
	if (GETPOST('submit_KANVIEW_PROSPECTS_PL_HIGH_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_PROSPECTS_PL_HIGH_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_PROSPECTS_PL_HIGH_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_PROSPECTS_PL_LOW_COLOR
//
	if (GETPOST('submit_KANVIEW_PROSPECTS_PL_LOW_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_PROSPECTS_PL_LOW_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_PROSPECTS_PL_LOW_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_PROSPECTS_PL_MEDIUM_COLOR
//
	if (GETPOST('submit_KANVIEW_PROSPECTS_PL_MEDIUM_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_PROSPECTS_PL_MEDIUM_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_PROSPECTS_PL_MEDIUM_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_PROSPECTS_PL_NONE_COLOR
//
	if (GETPOST('submit_KANVIEW_PROSPECTS_PL_NONE_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_PROSPECTS_PL_NONE_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_PROSPECTS_PL_NONE_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_INVOICES_SUPPLIERS_TAG
//
	if (GETPOST('submit_KANVIEW_INVOICES_SUPPLIERS_TAG')) {
		$newvalue	 = GETPOST('KANVIEW_INVOICES_SUPPLIERS_TAG');
		$res			 = dolibarr_set_const($db, "KANVIEW_INVOICES_SUPPLIERS_TAG", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR
//
	if (GETPOST('submit_KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR
//
	if (GETPOST('submit_KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}


//
// ------------- update of KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR
//
	if (GETPOST('submit_KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR')) {
		$newvalue	 = GETPOST('KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR');
		$res			 = dolibarr_set_const($db, "KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
		if (!$res > 0)
			$error++;
		if (!$error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		}
		else {
			setEventMessages($langs->trans("Error"), null, 'errors');
		}
	}
}


// ----------------------- action to Define constants for submodules that contains parameters (forms with param1, param2, ... and value1, value2, ...)
elseif ($action == 'setModuleOptions') {
	$post_size = count($_POST);

	$db->begin();

	for ($i = 0; $i < $post_size; $i ++) {
		if (array_key_exists('param' . $i, $_POST)) {
			$param = GETPOST("param" . $i, 'alpha');
			$value = GETPOST("value" . $i, 'alpha');
			if ($param)
				$res	 = dolibarr_set_const($db, $param, $value, 'chaine', 0, '', $conf->entity);
			if (!$res > 0)
				$error ++;
		}
	}
	if (!$error) {
		$db->commit();
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	}
	else {
		$db->rollback();
		setEventMessages($langs->trans("Error"), null, 'errors');
	}
}

$container = 'kanview';

/* * *************************************************************************************************
 *
 * ---------------------------------------- View
 *
 * ************************************************************************************************* */

/*
 * $var = "some text";
 * $text = <<<EOT
 * Place your text between the EOT. It's
 * the delimiter that ends the text
 * of your multiline string.
 * $var
 * EOT;
 */

clearstatcache();

$dirmodels = array_merge(array(
		'/'), (array) $conf->modules_parts['models']);
$form			 = new Form($db);

//
// ------------------------------------ CSS & JS ---------------------------------------------
//
$LIB_URL_RELATIVE = str_replace(DOL_URL_ROOT, '', dol_buildpath('/kanview/lib', 1));

// ---- css
$arrayofcss		 = array();
$arrayofcss[]	 = $LIB_URL_RELATIVE . '/sf/Content/ejthemes/default-theme/ej.web.all.min.css';
//$arrayofcss[]	 = $LIB_URL_RELATIVE . '/sf/css/ejthemes/responsive-css/ej.responsive.css';
$arrayofcss[]	 = str_replace(DOL_URL_ROOT, '', dol_buildpath('/kanview', 1)) . '/css/kanview.css';
// $arrayofcss[]	 = dol_buildpath('/kanview/css/', 1) . str_replace('.php', '.css', basename($_SERVER['SCRIPT_NAME']));
// ---- js
$jsEnabled		 = true;
if (!empty($conf->use_javascript_ajax)) {
	$arrayofjs	 = array();
// $arrayofjs[] = $LIB_URL_RELATIVE . '/sf/js/jquery-3.1.1.min.js';
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/jsrender.min.js';
// ----- sf common
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/common/ej.core.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/common/ej.data.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/common/ej.draggable.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/common/ej.globalize.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/common/ej.scroller.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/common/ej.touch.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/common/ej.unobtrusive.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/common/ej.webform.min.js?b=' . $build;
// ----- sf others

	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.button.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.menu.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.slider.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.splitbutton.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.colorpicker.min.js?b=' . $build;
// ----- sf traductions (garder les après common et others)
	if (in_array($lang->defaultlang, array('fr_FR', 'en_US'))) {
		$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/i18n/ej.culture.' . str_replace('_', '-', $langs->defaultlang) . '.min.js?b=' . $build;
		$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/l10n/ej.localetexts.' . str_replace('_', '-', $langs->defaultlang) . '.min.js?b=' . $build;
	}
	else {
		$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/i18n/ej.culture.fr-FR.min.js?b=' . $build;
		$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/l10n/ej.localetexts.fr-FR.min.js?b=' . $build;
	}
/// ----------
}
else {
	$jsEnabled = false;
}
/// --------------------------------------- end css & js --------------------------------------------

llxHeader('', $langs->trans("Kanview_SetupPage"), '', '', 0, 0, $arrayofjs, $arrayofcss, '');

// llxHeader('', $langs->trans("Kanview_SetupPage"), '', '', 0, 0, '', array(str_replace(DOL_URL_ROOT, '', dol_buildpath('/kanview/css/', 1) . str_replace('.php', '.css', basename($_SERVER['SCRIPT_NAME']))));

$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
echo load_fiche_titre($langs->trans("Kanview_SetupPage"), $linkback, 'title_setup');

$head	 = kanview_admin_prepare_head();
// $titre = $langs->trans("libelleSingulierCode");
$picto = 'kanview@kanview'; // icone du module,

dol_fiche_head($head, 'setup', $langs->trans("Module125032Name"), 0, $picto);

//
// -------------------------------------------------  view options principales
//
//
// ----------- group Kanview_ConstGroupMain
//
print load_fiche_titre($langs->trans("Kanview_ConstGroupMain"), '', '');
$form	 = new Form($db);
$var	 = true;
echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
echo '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
echo '<input type="hidden" name="action" value="updateoptions">';

echo '<table class="noborder" width="100%">';
// ligne des titre de la table
echo '<tr class="liste_titre">';
echo "<td>" . $langs->trans("Parameters") . "</td>\n";
echo '<td align="right" width="60">' . $langs->trans("Value") . '</td>' . "\n";
echo '<td width="80">&nbsp;</td></tr>' . "\n";


//
// --- row KANVIEW_HOME_PAGE
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_HOME_PAGE') . ' </td>';
echo '<td width="20%" align="right">';
$ajax_combobox = false;
$values				 = 'PROJETS,TASKS,PROPALS,ORDERS,INVOICES,PROSPECTS,INVOICES_SUPPLIERS';
$keys					 = 'projets,tasks,propals,orders,invoices,prospects,invoices_suppliers';
$valuesArray	 = explode(',', $values);
$keysArray		 = explode(',', $keys);
$count				 = count($valuesArray);
if (count($keysArray) != $count)
	$keysArray		 = array();
if ($count > 0) {
	echo '<select id="KANVIEW_HOME_PAGE" class="flat" name="KANVIEW_HOME_PAGE" title="' . $langs->trans('KANVIEW_HOME_PAGE_DESC') . '">';
	echo ''; // fournie par le générateur
	for ($i = 0; $i < $count; $i++) {
		if ((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_HOME_PAGE) || (!isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_HOME_PAGE)) {
			$optionSelected = 'selected';
		}
		else {
			$optionSelected = '';
		}
		echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . (!empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) : '') . '</option>';
	}
	echo '</select>';
}
else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
	echo '<input id="KANVIEW_HOME_PAGE" class="flat __ADDITIONAL_CLASSES__" name="KANVIEW_HOME_PAGE" title="' . $langs->trans('KANVIEW_HOME_PAGE_DESC') . '" value="' . (!empty($conf->global->KANVIEW_HOME_PAGE) ? $langs->trans($conf->global->KANVIEW_HOME_PAGE) : '') . '">';
}
if ($ajax_combobox) {
	include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
	echo ajax_combobox('KANVIEW_HOME_PAGE');
}
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_HOME_PAGE" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_SHOW_PICTO
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_SHOW_PICTO') . ' </td>';
echo '<td width="20%" align="right">';
$ajax_combobox = false;
$values				 = 'OUI,NON';
$keys					 = '1,0';
$valuesArray	 = explode(',', $values);
$keysArray		 = explode(',', $keys);
$count				 = count($valuesArray);
if (count($keysArray) != $count)
	$keysArray		 = array();
if ($count > 0) {
	echo '<select id="KANVIEW_SHOW_PICTO" class="flat" name="KANVIEW_SHOW_PICTO" title="' . $langs->trans('KANVIEW_SHOW_PICTO_DESC') . '">';
	echo ''; // fournie par le générateur
	for ($i = 0; $i < $count; $i++) {
		if ((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_SHOW_PICTO) || (!isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_SHOW_PICTO)) {
			$optionSelected = 'selected';
		}
		else {
			$optionSelected = '';
		}
		echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . (!empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) : '') . '</option>';
	}
	echo '</select>';
}
else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
	echo '<input id="KANVIEW_SHOW_PICTO" class="flat " name="KANVIEW_SHOW_PICTO" title="' . $langs->trans('KANVIEW_SHOW_PICTO_DESC') . '" value="' . (!empty($conf->global->KANVIEW_SHOW_PICTO) ? $langs->trans($conf->global->KANVIEW_SHOW_PICTO) : '') . '">';
}
if ($ajax_combobox) {
	include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
	echo ajax_combobox('KANVIEW_SHOW_PICTO');
}
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_SHOW_PICTO" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';


//
// --- row KANVIEW_FILTER_DEFAULT_DATE_START
//
$var				 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_FILTER_DEFAULT_DATE_START') . ' </td>';
echo '<td width="20%" align="right">';
$valuesArray = array(1, 2, 3, 6, 12, 24, 36, 48, 60);
$keysArray	 = array(1, 2, 3, 6, 12, 24, 36, 48, 60);
$count			 = count($valuesArray);
if (count($keysArray) != $count)
	$keysArray	 = array();
if ($count > 0) {
	echo '<select id="KANVIEW_FILTER_DEFAULT_DATE_START" class="flat" name="KANVIEW_FILTER_DEFAULT_DATE_START" title="' . $langs->trans('KANVIEW_FILTER_DEFAULT_DATE_START_DESC') . '">';
	echo ''; // fournie par le générateur
	for ($i = 0; $i < $count; $i++) {
		if ((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_FILTER_DEFAULT_DATE_START) || (!isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_FILTER_DEFAULT_DATE_START)) {
			$optionSelected = 'selected';
		}
		else {
			$optionSelected = '';
		}
		echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . (!empty($valuesArray[$i]) ? $valuesArray[$i] : '') . '</option>';
	}
	echo '</select>';
}
else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
	echo '<input id="KANVIEW_FILTER_DEFAULT_DATE_START" class="flat " name="KANVIEW_FILTER_DEFAULT_DATE_START" title="' . $langs->trans('KANVIEW_FILTER_DEFAULT_DATE_START_DESC') . '" value="' . (!empty($conf->global->KANVIEW_FILTER_DEFAULT_DATE_START) ? $langs->trans($conf->global->KANVIEW_FILTER_DEFAULT_DATE_START) : '') . '">';
}
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_FILTER_DEFAULT_DATE_START" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';


print '</table>';
print '</form>';

print '<br><br>';

//
// ----------- group Kanview_ConstGroupProjets
//
print load_fiche_titre($langs->trans("Kanview_ConstGroupProjets"), '', '');
$form	 = new Form($db);
$var	 = true;
echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
echo '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
echo '<input type="hidden" name="action" value="updateoptions">';

echo '<table class="noborder" width="100%">';
// ligne des titre de la table
echo '<tr class="liste_titre">';
echo "<td>" . $langs->trans("Parameters") . "</td>\n";
echo '<td align="right" width="60">' . $langs->trans("Value") . '</td>' . "\n";
echo '<td width="80">&nbsp;</td></tr>' . "\n";


//
// --- row KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT') . ' </td>';
echo '<td width="20%" align="right">';
// ----------- EDIT - KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT
$ajax_combobox = false;
$values				 = 'NON,OUI';
$keys					 = '0,1';
$valuesArray	 = explode(',', $values);
$keysArray		 = explode(',', $keys);
$count				 = count($valuesArray);
if (count($keysArray) != $count)
	$keysArray		 = array();
if ($count > 0) {
	echo '<select id="KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT" class="flat" name="KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT" title="' . $langs->trans('KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT') . '">';
	echo ''; // fournie par le générateur
	for ($i = 0; $i < $count; $i++) {
		if ((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT) || (!isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT)) {
			$optionSelected = 'selected';
		}
		else {
			$optionSelected = '';
		}
		echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . (!empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) : '') . '</option>';
	}
	echo '</select>';
}
else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
	echo '<input id="KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT" class="flat" name="KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT" title="' . $langs->trans('KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT') . '" value="' . (!empty($conf->global->KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT) ? $langs->trans($conf->global->KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT) : '') . '">';
}
if ($ajax_combobox) {
	include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
	echo ajax_combobox('KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT');
}
/// ---
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';


//
// --- row KANVIEW_PROJETS_SYCHRONIZE_OPP_PERCENT_WITH_STATUS
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROJETS_SYCHRONIZE_OPP_PERCENT_WITH_STATUS') . ' </td>';
echo '<td width="20%" align="right">';
$ajax_combobox = false;
$values				 = 'NON,OUI';
$keys					 = '0,1';
$valuesArray	 = explode(',', $values);
$keysArray		 = explode(',', $keys);
$count				 = count($valuesArray);
if (count($keysArray) != $count)
	$keysArray		 = array();
if ($count > 0) {
	echo '<select id="KANVIEW_PROJETS_SYCHRONIZE_OPP_PERCENT_WITH_STATUS" class="flat" name="KANVIEW_PROJETS_SYCHRONIZE_OPP_PERCENT_WITH_STATUS" title="' . $langs->trans('KANVIEW_PROJETS_SYCHRONIZE_OPP_PERCENT_WITH_STATUS') . '">';
	echo ''; // fournie par le générateur
	for ($i = 0; $i < $count; $i++) {
		if ((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_PROJETS_SYCHRONIZE_OPP_PERCENT_WITH_STATUS) || (!isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_PROJETS_SYCHRONIZE_OPP_PERCENT_WITH_STATUS)) {
			$optionSelected = 'selected';
		}
		else {
			$optionSelected = '';
		}
		echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . (!empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) : '') . '</option>';
	}
	echo '</select>';
}
else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
	echo '<input id="KANVIEW_PROJETS_SYCHRONIZE_OPP_PERCENT_WITH_STATUS" class="flat" name="KANVIEW_PROJETS_SYCHRONIZE_OPP_PERCENT_WITH_STATUS" title="' . $langs->trans('KANVIEW_PROJETS_SYCHRONIZE_OPP_PERCENT_WITH_STATUS') . '" value="' . (!empty($conf->global->KANVIEW_PROJETS_SYCHRONIZE_OPP_PERCENT_WITH_STATUS) ? $langs->trans($conf->global->KANVIEW_PROJETS_SYCHRONIZE_OPP_PERCENT_WITH_STATUS) : '') . '">';
}
if ($ajax_combobox) {
	include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
	echo ajax_combobox('KANVIEW_PROJETS_SYCHRONIZE_OPP_PERCENT_WITH_STATUS');
}
/// ---
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROJETS_SYCHRONIZE_OPP_PERCENT_WITH_STATUS" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_PROJETS_TAG
//
$var = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROJETS_TAG') . ' </td>';
echo '<td width="20%" align="right">';

// ----------- EDIT - KANVIEW_PROJETS_TAG
$ajax_combobox = false;
$values				 = 'OPP_PERCENT,OPP_AMOUNT,DATEO,DATEE';
$keys					 = 'opp_percent,opp_amount,dateo,datee';
$valuesArray	 = explode(',', $values);
$keysArray		 = explode(',', $keys);
$count				 = count($valuesArray);
if (count($keysArray) != $count)
	$keysArray		 = array();
if ($count > 0) {
	echo '<select id="KANVIEW_PROJETS_TAG" class="flat" name="KANVIEW_PROJETS_TAG" title="' . $langs->trans('KANVIEW_PROJETS_TAG_DESC') . '">';
	echo ''; // fournie par le générateur
	for ($i = 0; $i < $count; $i++) {
		if ((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_PROJETS_TAG) || (!isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_PROJETS_TAG)) {
			$optionSelected = 'selected';
		}
		else {
			$optionSelected = '';
		}
		echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . (!empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) : '') . '</option>';
	}
	echo '</select>';
}
else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
	echo '<input id="KANVIEW_PROJETS_TAG" class="flat __ADDITIONAL_CLASSES__" name="KANVIEW_PROJETS_TAG" title="' . $langs->trans('KANVIEW_PROJETS_TAG_DESC') . '" value="' . (!empty($conf->global->KANVIEW_PROJETS_TAG) ? $langs->trans($conf->global->KANVIEW_PROJETS_TAG) : '') . '">';
}
if ($ajax_combobox) {
	include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
	echo ajax_combobox('KANVIEW_PROJETS_TAG');
}
/// ---


echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROJETS_TAG" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';




//
// --- row KANVIEW_PROJETS_DRAFT_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROJETS_DRAFT_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#46c6f4';
$value				 = ((!empty($conf->global->KANVIEW_PROJETS_DRAFT_COLOR)) ? $conf->global->KANVIEW_PROJETS_DRAFT_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROJETS_DRAFT_COLOR" class="flat" type="text" name="KANVIEW_PROJETS_DRAFT_COLOR" title="' . $langs->trans('KANVIEW_PROJETS_DRAFT_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROJETS_DRAFT_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROJETS_DRAFT_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';


//
// --- row KANVIEW_PROJETS_OPEN_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROJETS_OPEN_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#73bf44';
$value				 = ((!empty($conf->global->KANVIEW_PROJETS_OPEN_COLOR)) ? $conf->global->KANVIEW_PROJETS_OPEN_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROJETS_OPEN_COLOR" class="flat" type="text" name="KANVIEW_PROJETS_OPEN_COLOR" title="' . $langs->trans('KANVIEW_PROJETS_OPEN_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROJETS_OPEN_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROJETS_OPEN_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_PROJETS_CLOSED_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROJETS_CLOSED_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#ff0000';
$value				 = ((!empty($conf->global->KANVIEW_PROJETS_CLOSED_COLOR)) ? $conf->global->KANVIEW_PROJETS_CLOSED_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROJETS_CLOSED_COLOR" class="flat" type="text" name="KANVIEW_PROJETS_CLOSED_COLOR" title="' . $langs->trans('KANVIEW_PROJETS_CLOSED_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROJETS_CLOSED_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROJETS_CLOSED_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

print '</table>';
print '</form>';

print '<br><br>';

//
// ----------- group Kanview_ConstGroupTasks
//
print load_fiche_titre($langs->trans("Kanview_ConstGroupTasks"), '', '');
$form	 = new Form($db);
$var	 = true;
echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
echo '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
echo '<input type="hidden" name="action" value="updateoptions">';

echo '<table class="noborder" width="100%">';
// ligne des titre de la table
echo '<tr class="liste_titre">';
echo "<td>" . $langs->trans("Parameters") . "</td>\n";
echo '<td align="right" width="60">' . $langs->trans("Value") . '</td>' . "\n";
echo '<td width="80">&nbsp;</td></tr>' . "\n";

//
// --- row KANVIEW_TASKS_TAG
//
$var = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_TASKS_TAG') . ' </td>';
echo '<td width="20%" align="right">';

// ----------- EDIT - KANVIEW_TASKS_TAG
$ajax_combobox = false;
$values				 = 'TASK_PROJECT,TASK_PERIOD,TASK_PLANNED_WORKLOAD,TOTAL_TASK_DURATION,TASK_PROGRESSION';
$keys					 = 'projet_title,task_period,planned_workload,total_task_duration,progress';
$valuesArray	 = explode(',', $values);
$keysArray		 = explode(',', $keys);
$count				 = count($valuesArray);
if (count($keysArray) != $count)
	$keysArray		 = array();
if ($count > 0) {
	echo '<select id="KANVIEW_TASKS_TAG" class="flat" name="KANVIEW_TASKS_TAG" title="' . $langs->trans('KANVIEW_TASKS_TAG_DESC') . '">';
	echo ''; // fournie par le générateur
	for ($i = 0; $i < $count; $i++) {
		if ((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_TASKS_TAG) || (!isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_TASKS_TAG)) {
			$optionSelected = 'selected';
		}
		else {
			$optionSelected = '';
		}
		echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . (!empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) : '') . '</option>';
	}
	echo '</select>';
}
else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
	echo '<input id="KANVIEW_TASKS_TAG" class="flat __ADDITIONAL_CLASSES__" name="KANVIEW_TASKS_TAG" title="' . $langs->trans('KANVIEW_TASKS_TAG_DESC') . '" value="' . (!empty($conf->global->KANVIEW_TASKS_TAG) ? $langs->trans($conf->global->KANVIEW_TASKS_TAG) : '') . '">';
}
if ($ajax_combobox) {
	include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
	echo ajax_combobox('KANVIEW_TASKS_TAG');
}
/// ---

echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_TASKS_TAG" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_TASKS_OK_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_TASKS_OK_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#73bf44';
$value				 = ((!empty($conf->global->KANVIEW_TASKS_OK_COLOR)) ? $conf->global->KANVIEW_TASKS_OK_COLOR : $defaultvalue);
echo '<input id="KANVIEW_TASKS_OK_COLOR" class="flat" type="text" name="KANVIEW_TASKS_OK_COLOR" title="' . $langs->trans('KANVIEW_TASKS_OK_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_TASKS_OK_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_TASKS_OK_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_TASKS_LATE1_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_TASKS_LATE1_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#f7991d';
$value				 = ((!empty($conf->global->KANVIEW_TASKS_LATE1_COLOR)) ? $conf->global->KANVIEW_TASKS_LATE1_COLOR : $defaultvalue);
echo '<input id="KANVIEW_TASKS_LATE1_COLOR" class="flat" type="text" name="KANVIEW_TASKS_LATE1_COLOR" title="' . $langs->trans('KANVIEW_TASKS_LATE1_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_TASKS_LATE1_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_TASKS_LATE1_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_TASKS_LATE2_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_TASKS_LATE2_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#ff0000';
$value				 = ((!empty($conf->global->KANVIEW_TASKS_LATE2_COLOR)) ? $conf->global->KANVIEW_TASKS_LATE2_COLOR : $defaultvalue);
echo '<input id="KANVIEW_TASKS_LATE2_COLOR" class="flat" type="text" name="KANVIEW_TASKS_LATE2_COLOR" title="' . $langs->trans('KANVIEW_TASKS_LATE2_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_TASKS_LATE2_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_TASKS_LATE2_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

print '</table>';
print '</form>';

print '<br><br>';

//
// ----------- group Kanview_ConstGroupPropals
//
print load_fiche_titre($langs->trans("Kanview_ConstGroupPropals"), '', '');
$form	 = new Form($db);
$var	 = true;
echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
echo '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
echo '<input type="hidden" name="action" value="updateoptions">';

echo '<table class="noborder" width="100%">';
// ligne des titre de la table
echo '<tr class="liste_titre">';
echo "<td>" . $langs->trans("Parameters") . "</td>\n";
echo '<td align="right" width="60">' . $langs->trans("Value") . '</td>' . "\n";
echo '<td width="80">&nbsp;</td></tr>' . "\n";

//
// --- row KANVIEW_PROPALS_TAG
//
$var = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROPALS_TAG') . ' </td>';
echo '<td width="20%" align="right">';

// ----------- EDIT - KANVIEW_PROPALS_TAG
$ajax_combobox = false;
$values				 = 'DATEP,FIN_VALIDITE,DATE_LIVRAISON,TOTAL_HT';
$keys					 = 'datep,fin_validite,date_livraison,total_ht';
$valuesArray	 = explode(',', $values);
$keysArray		 = explode(',', $keys);
$count				 = count($valuesArray);
if (count($keysArray) != $count)
	$keysArray		 = array();
if ($count > 0) {
	echo '<select id="KANVIEW_PROPALS_TAG" class="flat" name="KANVIEW_PROPALS_TAG" title="' . $langs->trans('KANVIEW_PROPALS_TAG_DESC') . '">';
	echo ''; // fournie par le générateur
	for ($i = 0; $i < $count; $i++) {
		if ((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_PROPALS_TAG) || (!isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_PROPALS_TAG)) {
			$optionSelected = 'selected';
		}
		else {
			$optionSelected = '';
		}
		echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . (!empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) : '') . '</option>';
	}
	echo '</select>';
}
else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
	echo '<input id="KANVIEW_PROPALS_TAG" class="flat __ADDITIONAL_CLASSES__" name="KANVIEW_PROPALS_TAG" title="' . $langs->trans('KANVIEW_PROPALS_TAG_DESC') . '" value="' . (!empty($conf->global->KANVIEW_PROPALS_TAG) ? $langs->trans($conf->global->KANVIEW_PROPALS_TAG) : '') . '">';
}
if ($ajax_combobox) {
	include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
	echo ajax_combobox('KANVIEW_PROPALS_TAG');
}
/// ---

echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROPALS_TAG" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_PROPALS_LATE1_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROPALS_LATE1_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#46c6f4';
$value				 = ((!empty($conf->global->KANVIEW_PROPALS_LATE1_COLOR)) ? $conf->global->KANVIEW_PROPALS_LATE1_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROPALS_LATE1_COLOR" class="flat" type="text" name="KANVIEW_PROPALS_LATE1_COLOR" title="' . $langs->trans('KANVIEW_PROPALS_LATE1_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROPALS_LATE1_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROPALS_LATE1_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_PROPALS_LATE2_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROPALS_LATE2_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#f7991d';
$value				 = ((!empty($conf->global->KANVIEW_PROPALS_LATE2_COLOR)) ? $conf->global->KANVIEW_PROPALS_LATE2_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROPALS_LATE2_COLOR" class="flat" type="text" name="KANVIEW_PROPALS_LATE2_COLOR" title="' . $langs->trans('KANVIEW_PROPALS_LATE2_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROPALS_LATE2_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROPALS_LATE2_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_PROPALS_LATE3_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROPALS_LATE3_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#b76c99';
$value				 = ((!empty($conf->global->KANVIEW_PROPALS_LATE3_COLOR)) ? $conf->global->KANVIEW_PROPALS_LATE3_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROPALS_LATE3_COLOR" class="flat" type="text" name="KANVIEW_PROPALS_LATE3_COLOR" title="' . $langs->trans('KANVIEW_PROPALS_LATE3_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROPALS_LATE3_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROPALS_LATE3_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_PROPALS_LATE4_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROPALS_LATE4_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#ff0000';
$value				 = ((!empty($conf->global->KANVIEW_PROPALS_LATE4_COLOR)) ? $conf->global->KANVIEW_PROPALS_LATE4_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROPALS_LATE4_COLOR" class="flat" type="text" name="KANVIEW_PROPALS_LATE4_COLOR" title="' . $langs->trans('KANVIEW_PROPALS_LATE4_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROPALS_LATE4_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROPALS_LATE4_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

print '</table>';
print '</form>';

print '<br><br>';

//
// ----------- group Kanview_ConstGroupInvoices
//
print load_fiche_titre($langs->trans("Kanview_ConstGroupInvoices"), '', '');
$form	 = new Form($db);
$var	 = true;
echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
echo '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
echo '<input type="hidden" name="action" value="updateoptions">';

echo '<table class="noborder" width="100%">';
// ligne des titre de la table
echo '<tr class="liste_titre">';
echo "<td>" . $langs->trans("Parameters") . "</td>\n";
echo '<td align="right" width="60">' . $langs->trans("Value") . '</td>' . "\n";
echo '<td width="80">&nbsp;</td></tr>' . "\n";

//
// --- row KANVIEW_INVOICES_TAG
//
$var = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_INVOICES_TAG') . ' </td>';
echo '<td width="20%" align="right">';

// ----------- EDIT - KANVIEW_INVOICES_TAG
$ajax_combobox = false;
$values				 = 'DATEF,DATE_LIM_REGLEMENT,TOTAL_TTC_TOTAL_PAYE,TOTAL_RESTANT';
$keys					 = 'datef,date_lim_reglement,total_ttc_total_paye,total_restant';
$valuesArray	 = explode(',', $values);
$keysArray		 = explode(',', $keys);
$count				 = count($valuesArray);
if (count($keysArray) != $count)
	$keysArray		 = array();
if ($count > 0) {
	echo '<select id="KANVIEW_INVOICES_TAG" class="flat" name="KANVIEW_INVOICES_TAG" title="' . $langs->trans('KANVIEW_INVOICES_TAG_DESC') . '">';
	echo ''; // fournie par le générateur
	for ($i = 0; $i < $count; $i++) {
		if ((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_INVOICES_TAG) || (!isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_INVOICES_TAG)) {
			$optionSelected = 'selected';
		}
		else {
			$optionSelected = '';
		}
		echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . (!empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) : '') . '</option>';
	}
	echo '</select>';
}
else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
	echo '<input id="KANVIEW_INVOICES_TAG" class="flat __ADDITIONAL_CLASSES__" name="KANVIEW_INVOICES_TAG" title="' . $langs->trans('KANVIEW_INVOICES_TAG_DESC') . '" value="' . (!empty($conf->global->KANVIEW_INVOICES_TAG) ? $langs->trans($conf->global->KANVIEW_INVOICES_TAG) : '') . '">';
}
if ($ajax_combobox) {
	include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
	echo ajax_combobox('KANVIEW_INVOICES_TAG');
}
/// ---

echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_INVOICES_TAG" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_INVOICES_LATE1_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_INVOICES_LATE1_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#b76caa';
$value				 = ((!empty($conf->global->KANVIEW_INVOICES_LATE1_COLOR)) ? $conf->global->KANVIEW_INVOICES_LATE1_COLOR : $defaultvalue);
echo '<input id="KANVIEW_INVOICES_LATE1_COLOR" class="flat" type="text" name="KANVIEW_INVOICES_LATE1_COLOR" title="' . $langs->trans('KANVIEW_INVOICES_LATE1_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_INVOICES_LATE1_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_INVOICES_LATE1_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_INVOICES_LATE2_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_INVOICES_LATE2_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#f7991d';
$value				 = ((!empty($conf->global->KANVIEW_INVOICES_LATE2_COLOR)) ? $conf->global->KANVIEW_INVOICES_LATE2_COLOR : $defaultvalue);
echo '<input id="KANVIEW_INVOICES_LATE2_COLOR" class="flat" type="text" name="KANVIEW_INVOICES_LATE2_COLOR" title="' . $langs->trans('KANVIEW_INVOICES_LATE2_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_INVOICES_LATE2_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_INVOICES_LATE2_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_INVOICES_LATE3_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_INVOICES_LATE3_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#ff0000';
$value				 = ((!empty($conf->global->KANVIEW_INVOICES_LATE3_COLOR)) ? $conf->global->KANVIEW_INVOICES_LATE3_COLOR : $defaultvalue);
echo '<input id="KANVIEW_INVOICES_LATE3_COLOR" class="flat" type="text" name="KANVIEW_INVOICES_LATE3_COLOR" title="' . $langs->trans('KANVIEW_INVOICES_LATE3_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_INVOICES_LATE3_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_INVOICES_LATE3_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

print '</table>';
print '</form>';

print '<br><br>';

//
// ----------- group Kanview_ConstGroupOrders
//
print load_fiche_titre($langs->trans("Kanview_ConstGroupOrders"), '', '');
$form	 = new Form($db);
$var	 = true;
echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
echo '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
echo '<input type="hidden" name="action" value="updateoptions">';

echo '<table class="noborder" width="100%">';
// ligne des titre de la table
echo '<tr class="liste_titre">';
echo "<td>" . $langs->trans("Parameters") . "</td>\n";
echo '<td align="right" width="60">' . $langs->trans("Value") . '</td>' . "\n";
echo '<td width="80">&nbsp;</td></tr>' . "\n";

//
// --- row KANVIEW_ORDERS_TAG
//
$var = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_ORDERS_TAG') . ' </td>';
echo '<td width="20%" align="right">';

// ----------- EDIT - KANVIEW_ORDERS_TAG
$ajax_combobox = false;
$values				 = 'TOTAL_HT,DATE_COMANDE,DATE_LIVRAISON,TOTAL_HT_DATE_COMMANDE,TOTAL_HT_DATE_LIVRAISON';
$keys					 = 'total_ht,date_commande,date_livraison,total_ht_date_commande,total_ht_date_livraison';
$valuesArray	 = explode(',', $values);
$keysArray		 = explode(',', $keys);
$count				 = count($valuesArray);
if (count($keysArray) != $count)
	$keysArray		 = array();
if ($count > 0) {
	echo '<select id="KANVIEW_ORDERS_TAG" class="flat" name="KANVIEW_ORDERS_TAG" title="' . $langs->trans('KANVIEW_ORDERS_TAG_DESC') . '">';
	echo ''; // fournie par le générateur
	for ($i = 0; $i < $count; $i++) {
		if ((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_ORDERS_TAG) || (!isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_ORDERS_TAG)) {
			$optionSelected = 'selected';
		}
		else {
			$optionSelected = '';
		}
		echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . (!empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) : '') . '</option>';
	}
	echo '</select>';
}
else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
	echo '<input id="KANVIEW_ORDERS_TAG" class="flat __ADDITIONAL_CLASSES__" name="KANVIEW_ORDERS_TAG" title="' . $langs->trans('KANVIEW_ORDERS_TAG_DESC') . '" value="' . (!empty($conf->global->KANVIEW_ORDERS_TAG) ? $langs->trans($conf->global->KANVIEW_ORDERS_TAG) : '') . '">';
}
if ($ajax_combobox) {
	include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
	echo ajax_combobox('KANVIEW_ORDERS_TAG');
}
/// ---

echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_ORDERS_TAG" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_ORDERS_LATE1_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_ORDERS_LATE1_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#b76caa';
$value				 = ((!empty($conf->global->KANVIEW_ORDERS_LATE1_COLOR)) ? $conf->global->KANVIEW_ORDERS_LATE1_COLOR : $defaultvalue);
echo '<input id="KANVIEW_ORDERS_LATE1_COLOR" class="flat" type="text" name="KANVIEW_ORDERS_LATE1_COLOR" title="' . $langs->trans('KANVIEW_ORDERS_LATE1_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_ORDERS_LATE1_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_ORDERS_LATE1_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_ORDERS_LATE2_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_ORDERS_LATE2_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#f7991d';
$value				 = ((!empty($conf->global->KANVIEW_ORDERS_LATE2_COLOR)) ? $conf->global->KANVIEW_ORDERS_LATE2_COLOR : $defaultvalue);
echo '<input id="KANVIEW_ORDERS_LATE2_COLOR" class="flat" type="text" name="KANVIEW_ORDERS_LATE2_COLOR" title="' . $langs->trans('KANVIEW_ORDERS_LATE2_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_ORDERS_LATE2_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_ORDERS_LATE2_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_ORDERS_LATE3_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_ORDERS_LATE3_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#ff0000';
$value				 = ((!empty($conf->global->KANVIEW_ORDERS_LATE3_COLOR)) ? $conf->global->KANVIEW_ORDERS_LATE3_COLOR : $defaultvalue);
echo '<input id="KANVIEW_ORDERS_LATE3_COLOR" class="flat" type="text" name="KANVIEW_ORDERS_LATE3_COLOR" title="' . $langs->trans('KANVIEW_ORDERS_LATE3_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_ORDERS_LATE3_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_ORDERS_LATE3_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

print '</table>';
print '</form>';

print '<br><br>';

//
// ----------- group Kanview_ConstGroupProspects
//
print load_fiche_titre($langs->trans("Kanview_ConstGroupProspects"), '', '');
$form	 = new Form($db);
$var	 = true;
echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
echo '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
echo '<input type="hidden" name="action" value="updateoptions">';

echo '<table class="noborder" width="100%">';
// ligne des titre de la table
echo '<tr class="liste_titre">';
echo "<td>" . $langs->trans("Parameters") . "</td>\n";
echo '<td align="right" width="60">' . $langs->trans("Value") . '</td>' . "\n";
echo '<td width="80">&nbsp;</td></tr>' . "\n";


//
// --- row KANVIEW_PROSPECTS_ADD_PROSPECTSCLIENTS
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROSPECTS_ADD_PROSPECTSCLIENTS') . ' </td>';
echo '<td width="20%" align="right">';
$ajax_combobox = false;
$values				 = 'NON,OUI';
$keys					 = '0,1';
$valuesArray	 = explode(',', $values);
$keysArray		 = explode(',', $keys);
$count				 = count($valuesArray);
if (count($keysArray) != $count)
	$keysArray		 = array();
if ($count > 0) {
	echo '<select id="KANVIEW_PROSPECTS_ADD_PROSPECTSCLIENTS" class="flat" name="KANVIEW_PROSPECTS_ADD_PROSPECTSCLIENTS" title="' . $langs->trans('KANVIEW_PROSPECTS_ADD_PROSPECTSCLIENTS') . '">';
	echo ''; // fournie par le générateur
	for ($i = 0; $i < $count; $i++) {
		if ((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_PROSPECTS_ADD_PROSPECTSCLIENTS) || (!isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_PROSPECTS_ADD_PROSPECTSCLIENTS)) {
			$optionSelected = 'selected';
		}
		else {
			$optionSelected = '';
		}
		echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . (!empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) : '') . '</option>';
	}
	echo '</select>';
}
else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
	echo '<input id="KANVIEW_PROSPECTS_ADD_PROSPECTSCLIENTS" class="flat " name="KANVIEW_PROSPECTS_ADD_PROSPECTSCLIENTS" title="' . $langs->trans('KANVIEW_PROSPECTS_ADD_PROSPECTSCLIENTS') . '" value="' . (!empty($conf->global->KANVIEW_PROSPECTS_ADD_PROSPECTSCLIENTS) ? $langs->trans($conf->global->KANVIEW_PROSPECTS_ADD_PROSPECTSCLIENTS) : '') . '">';
}
if ($ajax_combobox) {
	include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
	echo ajax_combobox('KANVIEW_PROSPECTS_ADD_PROSPECTSCLIENTS');
}
/// ---

echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROSPECTS_ADD_PROSPECTSCLIENTS" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';


//
// --- row KANVIEW_PROSPECTS_TAG
//
$var = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROSPECTS_TAG') . ' </td>';
echo '<td width="20%" align="right">';

// ----------- EDIT - KANVIEW_PROSPECTS_TAG
$ajax_combobox = false;
$values				 = 'COUNTRY_TOWN,EMAIL,PHONE,TYPENT_LIBELLE,EFFECTIF_LIBELLE,PROSPECTLEVEL_LABEL';
$keys					 = 'country_town,email,phone,typent_libelle,effectif_libelle,prospectlevel_label';
$valuesArray	 = explode(',', $values);
$keysArray		 = explode(',', $keys);
$count				 = count($valuesArray);
if (count($keysArray) != $count)
	$keysArray		 = array();
if ($count > 0) {
	echo '<select id="KANVIEW_PROSPECTS_TAG" class="flat" name="KANVIEW_PROSPECTS_TAG" title="' . $langs->trans('KANVIEW_PROSPECTS_TAG_DESC') . '">';
	echo ''; // fournie par le générateur
	for ($i = 0; $i < $count; $i++) {
		if ((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_PROSPECTS_TAG) || (!isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_PROSPECTS_TAG)) {
			$optionSelected = 'selected';
		}
		else {
			$optionSelected = '';
		}
		echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . (!empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) : '') . '</option>';
	}
	echo '</select>';
}
else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
	echo '<input id="KANVIEW_PROSPECTS_TAG" class="flat __ADDITIONAL_CLASSES__" name="KANVIEW_PROSPECTS_TAG" title="' . $langs->trans('KANVIEW_PROSPECTS_TAG_DESC') . '" value="' . (!empty($conf->global->KANVIEW_PROSPECTS_TAG) ? $langs->trans($conf->global->KANVIEW_PROSPECTS_TAG) : '') . '">';
}
if ($ajax_combobox) {
	include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
	echo ajax_combobox('KANVIEW_PROSPECTS_TAG');
}
/// ---

echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROSPECTS_TAG" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_PROSPECTS_PL_HIGH_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROSPECTS_PL_HIGH_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#73bf44';
$value				 = ((!empty($conf->global->KANVIEW_PROSPECTS_PL_HIGH_COLOR)) ? $conf->global->KANVIEW_PROSPECTS_PL_HIGH_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROSPECTS_PL_HIGH_COLOR" class="flat" type="text" name="KANVIEW_PROSPECTS_PL_HIGH_COLOR" title="' . $langs->trans('KANVIEW_PROSPECTS_PL_HIGH_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROSPECTS_PL_HIGH_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROSPECTS_PL_HIGH_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_PROSPECTS_PL_LOW_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROSPECTS_PL_LOW_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#b76caa';
$value				 = ((!empty($conf->global->KANVIEW_PROSPECTS_PL_LOW_COLOR)) ? $conf->global->KANVIEW_PROSPECTS_PL_LOW_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROSPECTS_PL_LOW_COLOR" class="flat" type="text" name="KANVIEW_PROSPECTS_PL_LOW_COLOR" title="' . $langs->trans('KANVIEW_PROSPECTS_PL_LOW_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROSPECTS_PL_LOW_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROSPECTS_PL_LOW_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_PROSPECTS_PL_MEDIUM_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROSPECTS_PL_MEDIUM_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#f7991d';
$value				 = ((!empty($conf->global->KANVIEW_PROSPECTS_PL_MEDIUM_COLOR)) ? $conf->global->KANVIEW_PROSPECTS_PL_MEDIUM_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROSPECTS_PL_MEDIUM_COLOR" class="flat" type="text" name="KANVIEW_PROSPECTS_PL_MEDIUM_COLOR" title="' . $langs->trans('KANVIEW_PROSPECTS_PL_MEDIUM_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROSPECTS_PL_MEDIUM_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROSPECTS_PL_MEDIUM_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_PROSPECTS_PL_NONE_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROSPECTS_PL_NONE_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#ff0000';
$value				 = ((!empty($conf->global->KANVIEW_PROSPECTS_PL_NONE_COLOR)) ? $conf->global->KANVIEW_PROSPECTS_PL_NONE_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROSPECTS_PL_NONE_COLOR" class="flat" type="text" name="KANVIEW_PROSPECTS_PL_NONE_COLOR" title="' . $langs->trans('KANVIEW_PROSPECTS_PL_NONE_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROSPECTS_PL_NONE_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROSPECTS_PL_NONE_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

print '</table>';
print '</form>';

print '<br><br>';

//
// ----------- group Kanview_ConstGroupInvoicesSuppliers
//
print load_fiche_titre($langs->trans("Kanview_ConstGroupInvoicesSuppliers"), '', '');
$form	 = new Form($db);
$var	 = true;
echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
echo '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
echo '<input type="hidden" name="action" value="updateoptions">';

echo '<table class="noborder" width="100%">';
// ligne des titre de la table
echo '<tr class="liste_titre">';
echo "<td>" . $langs->trans("Parameters") . "</td>\n";
echo '<td align="right" width="60">' . $langs->trans("Value") . '</td>' . "\n";
echo '<td width="80">&nbsp;</td></tr>' . "\n";

//
// --- row KANVIEW_INVOICES_SUPPLIERS_TAG
//
$var = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_TAG') . ' </td>';
echo '<td width="20%" align="right">';

// ----------- EDIT - KANVIEW_INVOICES_SUPPLIERS_TAG
$ajax_combobox = false;
$values				 = 'DATEF,DATE_LIM_REGLEMENT,TOTAL_TTC_TOTAL_PAYE,TOTAL_RESTANT';
$keys					 = 'datef,date_lim_reglement,total_ttc_total_paye,total_restant';
$valuesArray	 = explode(',', $values);
$keysArray		 = explode(',', $keys);
$count				 = count($valuesArray);
if (count($keysArray) != $count)
	$keysArray		 = array();
if ($count > 0) {
	echo '<select id="KANVIEW_INVOICES_SUPPLIERS_TAG" class="flat" name="KANVIEW_INVOICES_SUPPLIERS_TAG" title="' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_TAG_DESC') . '">';
	echo ''; // fournie par le générateur
	for ($i = 0; $i < $count; $i++) {
		if ((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_INVOICES_SUPPLIERS_TAG) || (!isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_INVOICES_SUPPLIERS_TAG)) {
			$optionSelected = 'selected';
		}
		else {
			$optionSelected = '';
		}
		echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . (!empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) : '') . '</option>';
	}
	echo '</select>';
}
else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
	echo '<input id="KANVIEW_INVOICES_SUPPLIERS_TAG" class="flat __ADDITIONAL_CLASSES__" name="KANVIEW_INVOICES_SUPPLIERS_TAG" title="' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_TAG_DESC') . '" value="' . (!empty($conf->global->KANVIEW_INVOICES_SUPPLIERS_TAG) ? $langs->trans($conf->global->KANVIEW_INVOICES_SUPPLIERS_TAG) : '') . '">';
}
if ($ajax_combobox) {
	include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
	echo ajax_combobox('KANVIEW_INVOICES_SUPPLIERS_TAG');
}
/// ---

echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_INVOICES_SUPPLIERS_TAG" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#b76caa';
$value				 = ((!empty($conf->global->KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR)) ? $conf->global->KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR : $defaultvalue);
echo '<input id="KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR" class="flat" type="text" name="KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR" title="' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#f7991d';
$value				 = ((!empty($conf->global->KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR)) ? $conf->global->KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR : $defaultvalue);
echo '<input id="KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR" class="flat" type="text" name="KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR" title="' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

//
// --- row KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR
//
$var					 = !$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue	 = '#ff0000';
$value				 = ((!empty($conf->global->KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR)) ? $conf->global->KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR : $defaultvalue);
echo '<input id="KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR" class="flat" type="text" name="KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR" title="' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR_DESC') . '" size="3" maxlength="3" ' .
 'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR" value="' . $langs->trans("Modify") . '">';
echo '</td>';
echo '</tr>';

print '</table>';
print '</form>';

dol_fiche_end();

// ----------------------------------- javascripts spécifiques à cette page
// quelques variables javascripts fournis par php
echo '<script type="text/javascript">
 		var dateSeparator = "' . trim(substr($langs->trans('FormatDateShort'), 2, 1)) . '";
 		var KANVIEW_URL_ROOT = "' . trim(dol_buildpath('/kanview', 1)) . '";
 		var locale = "' . trim($langs->defaultlang) . '";
		var UpdateNotAllowed_ProjectClosed = "' . trim($langs->transnoentities('UpdateNotAllowed_ProjectClosed')) . '";
		var token = "' . trim($_SESSION['newtoken']) . '";
 	</script>';

// includes de fichiers javascripts
echo '<script src="' . dol_buildpath('/kanview/js/kanview.js', 1) . '?b=' . $build . '"></script>';
//echo '<script src="' . dol_buildpath('/kanview/js/', 1) . str_replace('.php', '.js', basename($_SERVER['SCRIPT_NAME'])) . '?b=' . $build . '"></script>';

llxFooter();
$db->close();

// --------------------------------------------- Functions -------------------------------------------

/**
 * Prepare array with list of tabs for page Admin/Config
 *
 * @return array Array of tabs to show
 */
function kanview_admin_prepare_head() {
	global $langs, $conf, $user;

	$langs->load("kanview@kanview");

	$h		 = 0;
	$head	 = array();

	// onglet principal page config
	$head[$h][0] = dol_buildpath('/kanview/admin/kanview_config.php', 1);
	$head[$h][1] = $langs->trans("Setup");
	$head[$h][2] = 'setup';
	$h ++;


	// onglet pour extrafields
	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array('entity:+tabname:Title:@kanview:/kanview/mypage.php?id=__ID__'); to add new tab
	// $this->tabs = array('entity:-tabname); to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'my_table_admin');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'my_table_admin', 'remove');

	return $head;
}

/**
 * Return a path to have a directory according to object without final '/'.
 * (hamid-210118-fonction ajoutée pour gérer les fichiers des modules perso)
 *
 * @param Object $object
 *        	Object
 * @param Object $idORref
 *        	'id' ou 'ref', si 'id' le nom du sous repertoire est l'id de l'objet sinon c'est la ref de l'objet
 * @param string $additional_subdirs
 *        	sous-repertoire à ajouter à cet objet pour stocker/retrouver le fichier en cours de traitement, doit être sans '/' ni au début ni à la fin (ex. 'album/famille')
 * @return string Dir to use ending. Example '' or '1/' or '1/2/'
 */
function get_exdir2($object, $idORref, $additional_subdirs = '') {
	global $conf;

	$path = '';

	if ((!empty($object->idfield)) && !empty($object->reffield)) {
		if ($idORref == 'id') // 'id' prioritaire
			$path	 = ($object->{$object->idfield} ? $object->{$object->idfield} : $object->{$object->reffield});
		else // 'ref' prioritaire
			$path	 = $object->{$object->reffield} ? $object->{$object->reffield} : $object->{$object->idfield};
	}

	if (isset($additional_subdirs) && $additional_subdirs != '') {
		$path	 = (!empty($path) ? $path	 .= '/' : '');
		$path	 .= trim($additional_subdirs, '/');
	}

	return $path;
}
