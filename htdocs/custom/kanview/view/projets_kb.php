<?php

/* Copyright (C) 2018-2021   ProgSI  (contact@progsi.ma)
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
 * \file
 * \ingroup agenda
 * \brief Home page of kanban events
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

// Protection
if (!hasPermissionForKanbanView('projets')) {
	accessforbidden();
	exit();
}

require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
include_once(DOL_DOCUMENT_ROOT . '/core/class/hookmanager.class.php');
$hookmanager = new HookManager($db);
$hookmanager->initHooks(array('kanview'));

$build = str_replace('.', '', KANVIEW_VERSION);

// ------------------------------------------- Params

$action	 = GETPOST('action', 'alpha');
if (empty($action))
	$action	 = 'show';

// paramètres filtres additionnels
//off//
$search_rowid							 = GETPOST('search_rowid', 'int');
// proprités Date ou DateTime, filtre par Day/Mois/Année
$search_dateo_day					 = GETPOST('search_dateo_day', 'int');
$search_dateo_month				 = GETPOST('search_dateo_month', 'int');
$search_dateo_year				 = GETPOST('search_dateo_year', 'int');
// proprités Date ou DateTime, filtre par Day/Mois/Année
$search_datee_day					 = GETPOST('search_datee_day', 'int');
$search_datee_month				 = GETPOST('search_datee_month', 'int');
$search_datee_year				 = GETPOST('search_datee_year', 'int');
$search_ref								 = GETPOST('search_ref', 'alpha');
$search_title							 = GETPOST('search_title', 'alpha');
$search_description				 = GETPOST('search_description', 'alpha');
$search_fk_user_creat			 = GETPOST('search_fk_user_creat', 'int');
$search_public						 = GETPOST('search_public', 'int');
$search_opp_percent				 = GETPOST('search_opp_percent', 'alpha');
// proprités Date ou DateTime, filtre par Day/Mois/Année
$search_date_close_day		 = GETPOST('search_date_close_day', 'int');
$search_date_close_month	 = GETPOST('search_date_close_month', 'int');
$search_date_close_year		 = GETPOST('search_date_close_year', 'int');
$search_fk_user_close			 = GETPOST('search_fk_user_close', 'int');
$search_opp_amount				 = GETPOST('search_opp_amount', 'alpha');
$search_budget_amount			 = GETPOST('search_budget_amount', 'alpha');
$search_fk_statut					 = GETPOST('search_fk_statut', 'int');
$search_fk_opp_status			 = GETPOST('search_fk_opp_status', 'int');
$search_opp_status_code		 = GETPOST('search_opp_status_code', 'alpha');
$search_opp_status_label	 = GETPOST('search_opp_status_label', 'alpha');
$search_position					 = GETPOST('search_position', 'alpha');
$search_opp_status_percent = GETPOST('search_opp_status_percent', 'alpha');
$search_lead_status_active = GETPOST('search_lead_status_active', 'alpha');
$search_societe_nom				 = GETPOST('search_societe_nom', 'alpha');
$search_id								 = GETPOST('search_id', 'alpha');
$search_fk_soc						 = GETPOST('search_fk_soc', 'int');
$search_category					 = GETPOST('search_category', 'int');

// proprités Date ou DateTime, filtre par Day/Mois/Année
// datec - date début
$search_dd_datec_day	 = str_pad(GETPOST('search_dd_datec_day', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_dd_datec_month = str_pad(GETPOST('search_dd_datec_month', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_dd_datec_year	 = str_pad(GETPOST('search_dd_datec_year', 'alpha'), 4, '0', STR_PAD_LEFT);
$search_dd_datec_hour	 = str_pad(GETPOST('search_dd_datec_hour', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_dd_datec_min	 = str_pad(GETPOST('search_dd_datec_min', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_dd_datec_sec	 = str_pad(GETPOST('search_dd_datec_sec', 'alpha'), 2, '0', STR_PAD_LEFT);

// 1er affichage, par défaut : la Date début est le nbre de mois du paramètre global KANVIEW_FILTER_DEFAULT_DATE_START
$moisFlottants = (!empty($conf->global->KANVIEW_FILTER_DEFAULT_DATE_START) ? $conf->global->KANVIEW_FILTER_DEFAULT_DATE_START : 6);
if ((empty($search_dd_datec_year) || $search_dd_datec_year == '0000') && (empty($search_dd_datec_month) || $search_dd_datec_month == '00') && (empty($search_dd_datec_day) || $search_dd_datec_day == '00')) {
	$ddTmp								 = $db->idate(dol_time_plus_duree(dol_now('tzserver') + (60 * 60 * 24), -($moisFlottants), 'm')); // format timstamp puis format : yyyymmddhhiiss
	$search_dd_datec_year	 = substr($ddTmp, 0, 4);
	$search_dd_datec_month = substr($ddTmp, 4, 2);
	$search_dd_datec_day	 = substr($ddTmp, 6, 2);

	$search_dd_datec			 = dol_stringtotime($search_dd_datec_year . $search_dd_datec_month . $search_dd_datec_day . $search_dd_datec_hour . $search_dd_datec_min . $search_dd_datec_sec, 0);
	$search_dd_datec_mysql = dol_print_date($search_dd_datec, '%Y-%m-%d %H:%M:%S', 'tzserver'); // format mysql pour le WHERE
}
else {
	$search_dd_datec			 = dol_stringtotime($search_dd_datec_year . $search_dd_datec_month . $search_dd_datec_day . $search_dd_datec_hour . $search_dd_datec_min . $search_dd_datec_sec, 0);
	$search_dd_datec_mysql = dol_print_date($search_dd_datec, '%Y-%m-%d %H:%M:%S', 'tzserver'); // format mysql pour le WHERE
}
// datec - date fin
$search_df_datec_day	 = str_pad(GETPOST('search_df_datec_day', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_df_datec_month = str_pad(GETPOST('search_df_datec_month', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_df_datec_year	 = str_pad(GETPOST('search_df_datec_year', 'alpha'), 4, '0', STR_PAD_LEFT);
$search_df_datec_hour	 = str_pad(GETPOST('search_df_datec_hour', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_df_datec_min	 = str_pad(GETPOST('search_df_datec_min', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_df_datec_sec	 = str_pad(GETPOST('search_df_datec_sec', 'alpha'), 2, '0', STR_PAD_LEFT);
// si l'heure n'est pas fourni, on la règle de façon à ce que la journée de "date fin" soit incluse
if (empty($search_df_datec_hour) || $search_df_datec_hour == '00')
	$search_df_datec_hour	 = '23';
if (empty($search_df_datec_min) || $search_df_datec_min == '00')
	$search_df_datec_min	 = '59';
if (empty($search_df_datec_sec) || $search_df_datec_sec == '00')
	$search_df_datec_sec	 = '59';
$search_df_datec			 = dol_stringtotime($search_df_datec_year . $search_df_datec_month . $search_df_datec_day . $search_df_datec_hour . $search_df_datec_min . $search_df_datec_sec, 0);
$search_df_datec_mysql = dol_print_date($search_df_datec, '%Y-%m-%d %H:%M:%S', 'tzserver'); // format mysql pour le WHERE
// 1er affichage, par défaut : la Date fin est le mois flottant suivant
if ((empty($search_df_datec_year) || $search_df_datec_year == '0000') && (empty($search_df_datec_month) || $search_df_datec_month == '00') && (empty($search_df_datec_day) || $search_df_datec_day == '00')) {
	$search_df_datec_year	 = date('Y');
	$search_df_datec_month = date('m');
	$search_df_datec_day	 = date('d');
	// $next_month				 = dol_get_next_month($search_df_datec_month, $search_df_datec_year);
	$search_df_datec_month = str_pad($search_df_datec_month, 2, '0', STR_PAD_LEFT);
	$search_df_datec_year	 = str_pad($search_df_datec_year, 4, '0', STR_PAD_LEFT);

	// $tmp				 = dol_get_prev_day(intval($search_df_datec_day), intval($next_month['month']), intval($next_month['year']));
	// $search_df_datec_day = str_pad($tmp['day'], 2, '0', STR_PAD_LEFT);
// si l'heure n'est pas fourni, on la règle de façon à ce que la journée de "date fin" soit incluse
	if (empty($search_df_datec_hour) || $search_df_datec_hour == '00')
		$search_df_datec_hour	 = '23';
	if (empty($search_df_datec_min) || $search_df_datec_min == '00')
		$search_df_datec_min	 = '59';
	if (empty($search_df_datec_sec) || $search_df_datec_sec == '00')
		$search_df_datec_sec	 = '59';
	$search_df_datec			 = dol_stringtotime($search_df_datec_year . $search_df_datec_month . $search_df_datec_day . $search_df_datec_hour . $search_df_datec_min . $search_df_datec_sec, 0);
	$search_df_datec_mysql = dol_print_date($search_df_datec, '%Y-%m-%d %H:%M:%S', 'tzserver'); // format mysql pour le WHERE
}

// proprités Date ou DateTime, filtre par Day/Mois/Année
$search_tms_day				 = GETPOST('search_tms_day', 'int');
$search_tms_month			 = GETPOST('search_tms_month', 'int');
$search_tms_year			 = GETPOST('search_tms_year', 'int');
$search_entity				 = GETPOST('search_entity', 'int');
$search_fk_user_modif	 = GETPOST('search_fk_user_modif', 'int');
$search_note_private	 = GETPOST('search_note_private', 'alpha');
$search_contact				 = GETPOST('search_contact', 'alpha');

$search_note_public	 = GETPOST('search_note_public', 'alpha');
$search_model_pdf		 = GETPOST('search_model_pdf', 'alpha');
$search_import_key	 = GETPOST('search_import_key', 'alpha');

// --- current view
$default_current_view	 = '';
$current_view					 = GETPOST('current_view', 'alpha'); // TODO : ajouter mécanisme pour modfier le hidden input current_view lorsque l'utilisateur sélectionne une vue dans le Kanban
if (empty($current_view) || !in_array($current_view, array('day', 'week', 'workweek', 'month', 'agenda')))
	$current_view					 = $default_current_view;

// read only ?
$read_only = false;

// paramètres additionnels
// __GETPOST_ADDITIONNELS__

$langs->load("kanview@kanview");
$langs->load('projects');
$langs->load("other");

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array(
		'my_prefix_kanban'));

// ------------ récupération des données (ce code doit rester avant les actions car utilisé par printPDF())
// si l'heure n'est pas fourni, on la règle de façon à ce que la journée de "date fin" soit incluse
if (empty($df_hour) || $df_hour == '00')
	$df_hour			 = '23';
if (empty($df_min) || $df_min == '00')
	$df_min				 = '59';
if (empty($df_sec) || $df_sec == '00')
	$df_sec				 = '59';
$date_fin_str	 = $df_year . $df_month . $df_day . $df_hour . $df_min . $df_sec;

// sortfield, sortorder, page, limit et offset
$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');
$page			 = GETPOST("page", "int");
if ($page == - 1) {
	$page = 0;
}
$limit	 = GETPOST('limit') ? GETPOST('limit', 'int') : $conf->liste_limit;
$offset	 = (int) $limit * (int) $page;
$offset	 = ($offset > 0 ? $offset - 1 : 0);

// effacement de la recherche si demandée
// doit rester avant le calcul des WHERE/HAVING
if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter.x") || GETPOST("button_removefilter")) { // All test are required to be compatible with all browsers
	// ------------------------------------------------------
	// $search_prop1 = '';
	$search_rowid = '';

	$search_dateo_day		 = '';
	$search_dateo_month	 = '';
	$search_dateo_year	 = '';

	$search_datee_day					 = '';
	$search_datee_month				 = '';
	$search_datee_year				 = '';
	$search_ref								 = '';
	$search_title							 = '';
	$search_description				 = '';
	$search_fk_user_creat			 = '';
	$search_public						 = '';
	$search_opp_percent				 = '';
	$search_date_close_day		 = '';
	$search_date_close_month	 = '';
	$search_date_close_year		 = '';
	$search_fk_user_close			 = '';
	$search_opp_amount				 = '';
	$search_budget_amount			 = '';
	$search_fk_statut					 = '';
	$search_fk_opp_status			 = '';
	$search_opp_status_code		 = '';
	$search_opp_status_label	 = '';
	$search_position					 = '';
	$search_opp_status_percent = '';
	$search_lead_status_active = '';
	$search_societe_nom				 = '';
	$search_id								 = '';

	// fonctionnalité expérimentale
	if ($conf->kanviewplus->enabled) {
		$search_fk_soc = '';
	}

	//
	$search_category = '';

	// datec - date début
	$search_dd_datec_day	 = '';
	$search_dd_datec_month = '';
	$search_dd_datec_year	 = '';
	$search_dd_datec_hour	 = '';
	$search_dd_datec_min	 = '';
	$search_dd_datec_sec	 = '';
	$search_dd_datec			 = '';
	$search_dd_datec_mysql = '';
// datec - date fin
	$search_df_datec_day	 = '';
	$search_df_datec_month = '';
	$search_df_datec_year	 = '';
	$search_df_datec_hour	 = '';
	$search_df_datec_min	 = '';
	$search_df_datec_sec	 = '';
	$search_df_datec			 = '';
	$search_df_datec_mysql = '';

	$search_tms_day				 = '';
	$search_tms_month			 = '';
	$search_tms_year			 = '';
	$search_entity				 = '';
	$search_fk_user_modif	 = '';
	$search_note_private	 = '';
	$search_note_public		 = '';
	$search_contact				 = '';

	$search_model_pdf	 = '';
	$search_import_key = '';

	// -----------------------------------------------------

	$search_array_options = array();
}

// garder ces lignes avant Actions Part1
include_once dol_buildpath('/kanview/class/req_kb_main_projets.class.php');
$ReqObject								 = new ReqKbMainProjets($db);
// liste des id des projets uatorisés pour l'utilisateur en cours
// getProjectsAuthorizedForUser($user, $mode = 0, $list = 0, $socid = 0);
$projectsAuthorizedArray	 = array_keys($ReqObject->getProjectsAuthorizedForUser($user, 0, 0, 0)); // on ne veut que les id des projets
$projectsAuthorizedString	 = implode(',', $projectsAuthorizedArray);
if (empty($projectsAuthorizedString)) {
	$projectsAuthorizedString = "0";
}
///
//
// ***************************************************************************************************************
//
//                                                 Actions Part 1 - Avant collecte de données
//
// ***************************************************************************************************************
// ---------------------------------------- action après Drag&Drop d'une tuile ==> mise à jour du "Status" de l'objet
// cette action doit rester avant collecte de données parce qu'elle peut les modifier en amont
if ($action == 'cardDrop') {

	require_once DOL_DOCUMENT_ROOT . '/projet/class/project.class.php';
	$response		 = array();
	$object			 = new Project($db);
	$id					 = GETPOST('id', 'int');
	$newStatusID = GETPOST('newStatusID', 'int');
	$err				 = 0;

	$response['token'] = $_SESSION['newtoken'];

	if ($id > 0) {
		// si l'utilisateur n'a pas le droit de modifier le projet
		if (empty($user->rights->projet->creer)
				|| (!empty($user->rights->projet->creer) && empty($user->rights->projet->all->creer) && !in_array($id, $projectsAuthorizedArray))) {
			$response['status']	 = 'KO';
			$response['message'] = $langs->trans("UpdateNotAllowed_NotEnoughRights");
		}
		else {
			$ret = $object->fetch($id);
			if ($ret > 0) {
				//
				$SQL1	 = "SELECT active, percent FROM " . MAIN_DB_PREFIX . "c_lead_status WHERE rowid = " . $newStatusID;
				$res1	 = $db->query($SQL1);
				if ($res1) {
					// --- il est interdit d'avoir comme destination un état désactivé
					$obj = $db->fetch_object($res1);
					if ($obj && $obj->active == 0) {
						$response['status']	 = 'KO';
						$response['message'] = $langs->trans("ActionNotAllowed_ProjectDestStatusDisabled");
					}
					else {
						$object->opp_status = $newStatusID;
						if ($object->fk_statut != 2) { // on ne met pas à jour un projet clos
							//
							// faut-t-il mettre à jour le pourcentage de probabilité également ?
							if (!empty($conf->global->KANVIEW_PROJETS_SYCHRONIZE_OPP_PERCENT_WITH_STATUS)) {
								$object->opp_percent = $obj->percent;
							}

							// fonctionnalité expérimentale
							// faut-il mettre à jour la date fin également ?
							if ($conf->kanviewplus->enabled) {
								if (strpos($conf->global->KANVIEWPLUS_PROJECT_STATUSES_FORCE_DATEEND_TODAY . ',', $newStatusID . ',') !== false) {
									$object->date_end		 = dol_now();
									// statut date fin (pour gestion des couleurs de fond)
									$object->dateeStatus = 'exceeded'; // on considère que la date d'aujourd'hui est une date dépassée
								}
							}

							$res = $object->update($user);
							if ($res > 0) {
								$response['status']													 = 'OK';
								$response['message']												 = $langs->trans("RecordUpdatedSuccessfully");
								$response['data']['project']['opp_percent']	 = $object->opp_percent;
								$response['data']['project']['rowid']				 = $object->id;
								$response['data']['project']['ref']					 = $object->ref;
								$response['data']['project']['datee']				 = dol_print_date($object->date_end, 'day', 'tzuser');
								$response['data']['project']['dateeStatus']	 = (!empty($object->dateeStatus) ? $object->dateeStatus : '');
							}
							else {
								$response['status']	 = 'KO';
								$response['message'] = setEventMessages($object->error, null, 'errors');
							}
						}
						else {
							$response['status']	 = 'KO';
							$response['message'] = $langs->trans("UpdateNotAllowed_ProjectClosed");
						}
					}
				}
				else {
					dol_syslog($db->lasterror, LOG_DEBUG);
					$response['status']	 = 'KO';
					$response['message'] = $db->lasterror;
				}
			}
			elseif ($ret == 0) {
				dol_syslog('RecordNotFound : project : ' . $id, LOG_DEBUG);
				$response['status']	 = 'KO';
				$response['message'] = $langs->trans("RecordNotFound");
				// setEventMessages($langs->trans("RecordNotFound"), null, 'errors');
				// $err++;
			}
			elseif ($ret < 0) {
				dol_syslog($object->error, LOG_DEBUG);
				$response['status']	 = 'KO';
				$response['message'] = $object->error;
			}
		}
	}
	else {
		$response['status']	 = 'KO';
		$response['message'] = $langs->trans('IncorrectParameter');
	}

	// l'envoi des données se fait plus loin après collecte de données
	//	exit(json_encode($response));
}

// **************************************************************************************************************
//
//                                        Kanban - Collecte de données
//
// ***************************************************************************************************************
//
// --------------------------- Requête principale (doit rester avant les actions)
//
// ----------- WHERE, HAVING et ORDER BY

$WHERE	 = " 1 = 1 ";
$HAVING	 = " 1 = 1 ";

if ($conf->multicompany->enabled) {
	if (compareVersions(DOL_VERSION, '6.0.0') == -1)
		$WHERE .= " AND t.entity IN (" . getEntity('project', 1) . ")";
	else
		$WHERE .= " AND t.entity IN (" . getEntity('project') . ")";
}

// si l'utilisateur est restreint aux projets publiques et ceux dont il est contact
if (empty($user->rights->projet->all->lire)) {
	$WHERE .= " AND (t.rowid IN (" . $projectsAuthorizedString . ") ) ";
}

// --- filtre période
// $WHERE .= " AND __FIELD_START_TIME__ >= '" . $date_debut_str . "' ";
// $WHERE .= " AND __FIELD_END_TIME__ <= '" . $date_fin_str . "' ";
/// ---

if ($search_rowid != '')
	$WHERE .= natural_search("t.rowid", $search_rowid);

if ($search_dateo_month > 0) {
	if ($search_dateo_year > 0 && empty($search_dateo_day))
		$WHERE .= " AND t.dateo BETWEEN '" . $db->idate(dol_get_first_day($search_dateo_year, $search_dateo_month, false)) . "' AND '" . $db->idate(dol_get_last_day($search_dateo_year, $search_dateo_month, false)) . "'";
	else if ($search_dateo_year > 0 && !empty($search_dateo_day))
		$WHERE .= " AND t.dateo BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $search_dateo_month, $search_dateo_day, $search_dateo_year)) . "' AND '" . $db->idate(dol_mktime(23, 59, 59, $search_dateo_month, $search_dateo_day, $search_dateo_year)) . "'";
	else
		$WHERE .= " AND date_format(t.dateo, '%m') = '" . $search_dateo_month . "'";
}
else if ($search_dateo_year > 0) {
	$WHERE .= " AND t.dateo BETWEEN '" . $db->idate(dol_get_first_day($search_dateo_year, 1, false)) . "' AND '" . $db->idate(dol_get_last_day($search_dateo_year, 12, false)) . "'";
}

if ($search_datee_month > 0) {
	if ($search_datee_year > 0 && empty($search_datee_day))
		$WHERE .= " AND t.datee BETWEEN '" . $db->idate(dol_get_first_day($search_datee_year, $search_datee_month, false)) . "' AND '" . $db->idate(dol_get_last_day($search_datee_year, $search_datee_month, false)) . "'";
	else if ($search_datee_year > 0 && !empty($search_datee_day))
		$WHERE .= " AND t.datee BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $search_datee_month, $search_datee_day, $search_datee_year)) . "' AND '" . $db->idate(dol_mktime(23, 59, 59, $search_datee_month, $search_datee_day, $search_datee_year)) . "'";
	else
		$WHERE .= " AND date_format(t.datee, '%m') = '" . $search_datee_month . "'";
}
else if ($search_datee_year > 0) {
	$WHERE .= " AND t.datee BETWEEN '" . $db->idate(dol_get_first_day($search_datee_year, 1, false)) . "' AND '" . $db->idate(dol_get_last_day($search_datee_year, 12, false)) . "'";
}
if ($search_ref != '')
	$WHERE .= natural_search("t.ref", $search_ref);
if ($search_title != '')
	$WHERE .= natural_search("t.title", $search_title);
if ($search_description != '')
	$WHERE .= natural_search("t.description", $search_description);
if ($search_fk_user_creat != '')
	$WHERE .= natural_search("t.fk_user_creat", $search_fk_user_creat, 1);
if ($search_public != '')
	$WHERE .= natural_search("t.public", $search_public, 1);
if ($search_contact != '')
	$WHERE .= " AND CONCAT(',', contactsIdsWithSource, ',') LIKE '%" . $db->escape(',' . $search_contact . ',') . "%'";
if ($search_opp_percent != '')
	$WHERE .= natural_search("t.opp_percent", $search_opp_percent, 1);
if ($search_date_close_month > 0) {
	if ($search_date_close_year > 0 && empty($search_date_close_day))
		$WHERE .= " AND t.date_close BETWEEN '" . $db->idate(dol_get_first_day($search_date_close_year, $search_date_close_month, false)) . "' AND '" . $db->idate(dol_get_last_day($search_date_close_year, $search_date_close_month, false)) . "'";
	else if ($search_date_close_year > 0 && !empty($search_date_close_day))
		$WHERE .= " AND t.date_close BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $search_date_close_month, $search_date_close_day, $search_date_close_year)) . "' AND '" . $db->idate(dol_mktime(23, 59, 59, $search_date_close_month, $search_date_close_day, $search_date_close_year)) . "'";
	else
		$WHERE .= " AND date_format(t.date_close, '%m') = '" . $search_date_close_month . "'";
}
else if ($search_date_close_year > 0) {
	$WHERE .= " AND t.date_close BETWEEN '" . $db->idate(dol_get_first_day($search_date_close_year, 1, false)) . "' AND '" . $db->idate(dol_get_last_day($search_date_close_year, 12, false)) . "'";
}
if ($search_fk_user_close != '')
	$WHERE .= natural_search("t.fk_user_close", $search_fk_user_close, 1);
if ($search_opp_amount != '')
	$WHERE .= natural_search("t.opp_amount", $search_opp_amount, 1);
if ($search_budget_amount != '')
	$WHERE .= natural_search("t.budget_amount", $search_budget_amount, 1);

if ($search_fk_statut != '') {
	$WHERE .= natural_search("t.fk_statut", $search_fk_statut, 1);
}
elseif (!isset($_GET['search_fk_statut'])) { // c'est le 1er affichage
	if ($conf->global->KANVIEW_PROJETS_OPENED_PROJECTS_BY_DEFAULT == 1) {
		$search_fk_statut	 = 1; // par défaut, on affiche les projets ouverts
		$WHERE						 .= natural_search("t.fk_statut", $search_fk_statut, 1);
	}
}

if ($search_fk_opp_status != '')
	$WHERE .= natural_search("t.fk_opp_status", $search_fk_opp_status, 1);

if ($search_opp_status_code != '')
	$WHERE .= natural_search("" . MAIN_DB_PREFIX . "c_lead_status.code", $search_opp_status_code);

if ($search_opp_status_label != '')
	$WHERE .= natural_search("" . MAIN_DB_PREFIX . "c_lead_status.label", $search_opp_status_label);

if ($search_position != '')
	$WHERE .= natural_search("" . MAIN_DB_PREFIX . "c_lead_status.position", $search_position, 1);
if ($search_opp_status_percent != '')
	$WHERE .= natural_search("" . MAIN_DB_PREFIX . "c_lead_status.percent", $search_opp_status_percent, 1);
if ($search_lead_status_active != '')
	$WHERE .= natural_search("" . MAIN_DB_PREFIX . "c_lead_status.active", $search_lead_status_active, 1);
if ($search_societe_nom != '')
	$WHERE .= natural_search("" . MAIN_DB_PREFIX . "societe.nom", $search_societe_nom);
if ($search_id != '')
	$WHERE .= natural_search("t.rowid", $search_id, 1);
if ($search_fk_soc != '')
	$WHERE .= natural_search("t.fk_soc", $search_fk_soc, 1);

if ($search_dd_datec_mysql != '' && $search_df_datec_mysql != '') {
	// si date début et date fin sont dans le mauvais ordre, on les inverse
	if ($search_dd_datec_mysql > $search_df_datec_mysql) {
		$tmp									 = $search_dd_datec_mysql;
		$search_dd_datec_mysql = $search_df_datec_mysql;
		$search_df_datec_mysql = $tmp;
	}

	$WHERE .= " AND (t.datec BETWEEN '" . $search_dd_datec_mysql . "' AND '" . $search_df_datec_mysql . "')";
}

if ($search_tms_month > 0) {
	if ($search_tms_year > 0 && empty($search_tms_day))
		$WHERE .= " AND t.tms BETWEEN '" . $db->idate(dol_get_first_day($search_tms_year, $search_tms_month, false)) . "' AND '" . $db->idate(dol_get_last_day($search_tms_year, $search_tms_month, false)) . "'";
	else if ($search_tms_year > 0 && !empty($search_tms_day))
		$WHERE .= " AND t.tms BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $search_tms_month, $search_tms_day, $search_tms_year)) . "' AND '" . $db->idate(dol_mktime(23, 59, 59, $search_tms_month, $search_tms_day, $search_tms_year)) . "'";
	else
		$WHERE .= " AND date_format(t.tms, '%m') = '" . $search_tms_month . "'";
}
else if ($search_tms_year > 0) {
	$WHERE .= " AND t.tms BETWEEN '" . $db->idate(dol_get_first_day($search_tms_year, 1, false)) . "' AND '" . $db->idate(dol_get_last_day($search_tms_year, 12, false)) . "'";
}
if ($search_entity != '')
	$WHERE .= natural_search("t.entity", $search_entity, 1);
if ($search_fk_user_modif != '')
	$WHERE .= natural_search("t.fk_user_modif", $search_fk_user_modif, 1);
if ($search_note_private != '')
	$WHERE .= natural_search("t.note_private", $search_note_private);
if ($search_note_public != '')
	$WHERE .= natural_search("t.note_public", $search_note_public);
if ($search_model_pdf != '')
	$WHERE .= natural_search("t.model_pdf", $search_model_pdf);
if ($search_import_key != '')
	$WHERE .= natural_search("t.import_key", $search_import_key);

if ($search_category != '') {
	$HAVING .= " AND CONCAT(categories, ',') LIKE '%" . $search_category . ",%'";
}

$ORDERBY	 = '';
if (empty($sortorder))
	$sortorder = 'ASC';
if (empty($sortfield))
	$sortfield = '1'; //
if ((!empty($sortfield)) && (!empty($sortorder))) {
	$ORDERBY = $sortfield . ' ' . $sortorder;
}

if ($WHERE == ' 1 = 1 ')
	$WHERE	 = '';
if ($HAVING == ' 1 = 1 ')
	$HAVING	 = '';

// ----- exécution de la requete principale (doit rester avant les actions)

$dataArray				 = array(); // array of events
// les "isNew" sont à "false" parce qu'on veut garder les paramétrage de la requete d'origine
$num							 = $ReqObject->fetchAll($limit, $offset, $ORDERBY, $isNewOrderBy			 = false, $WHERE, $isNewWhere				 = false, $HAVING, $isNewHaving			 = false);
$nbtotalofrecords	 = $ReqObject->nbtotalofrecords;

//
// --------------------------- Requête fournissant les titres des colonnes
//
$titlesValues					 = "";
$columnsArray					 = array();
$columnsIDsArray			 = array(); // tableau associatid : 'titre' => 'son id', ça nous permet de retrouver (côté js) les ids des Statuts en fonction de leur code
$columnsCountArray		 = array(); // tableau associatif : 'titre' => 'nbre d'éléments' dans la colonne (incrémenté dans la boucle de parcours des données principales)
$columnsOppAmountTotal = array(); // tableau associatif : 'HEADER_CODE' => "total opp amount"
// requete pour sélectionner les états des projets comme titre des colonnes
// ici, on sélectionne même les états désactivés pour pouvoir affciher les projets qui ont été mis dans cet état avant sa désactivation
// mais on ne pourra pas faire un Drop sur une colonne d'un état désactivé
$titlesSQL						 = "SELECT
					t.rowid AS rowid,
					t.rowid AS id,
					t.code AS opp_status_code,
					t.label AS opp_status_label,
					t.position AS position,
					t.percent AS opp_status_percent,
					t.active AS active
					FROM
					" . MAIN_DB_PREFIX . "c_lead_status as t
					ORDER BY
					t.position
";

$titlesField	 = 'opp_status_code';
$titlesIdField = 'rowid';
$res1					 = $db->query($titlesSQL);
if ($res1) {
	$i	 = 0;
	while ($obj = $db->fetch_object($res1)) {
		if (!$obj->active && !empty($conf->global->KANVIEW_PROJECTS_NOT_SHOW_INACTIVE))
			continue;
		$headerText = $langs->transnoentities($obj->{$titlesField});
		// si pas de traduction pour ce code, on affiche le libellé si existant
		// (ce cas est possible si l'utilisateur a ajouté de nouveaux statuts personnalisés dans llx_c_lead_status par exemple)
		if ($headerText === $obj->{$titlesField} && !empty($obj->opp_status_label)) {
			$headerText = $obj->opp_status_label;
		}
		$columnsArray[$i]['headerText']				 = $headerText;
		$columnsArray[$i]['key']							 = $obj->{$titlesField};
		$columnsArray[$i]['active']						 = $obj->active;
		$columnsArray[$i]['width']						 = 150;	// min width en pixel
		$columnsIDsArray[$obj->{$titlesField}] = $obj->{$titlesIdField};

		$columnsCountArray[$obj->{$titlesField}]		 = 0; // sera incrémenté dans la boucle de parcours des données principales ci-dessous
		$columnsOppAmountTotal[$obj->{$titlesField}] = 0; // total des montants des opportinutés de la colonne, sera renseigné dans la boucle de parcours des données principales ci-dessous
		//
		// traitements additionnels
		// on interdit le Drop sur une colonne dont l'état est désactivé
		if ($columnsArray[$i]['active'] == 0) {
			$columnsArray[$i]['allowDrop'] = false;
		}

		$i++;
	}

	// ajout du statut inconnu, utile quand aucun statut n'est fourni
	if (empty($conf->global->KANVIEW_PROJECTS_NOT_SHOW_UNKNOWN_STATUS)) {
		$columnsArray[$i]['headerText']						 = $langs->transnoentities("PROJECT_STATUS_UNKNOWN");
		$columnsArray[$i]['key']									 = "PROJECT_STATUS_UNKNOWN";
		$columnsArray[$i]['active']								 = 1;
		$columnsIDsArray["PROJECT_STATUS_UNKNOWN"] = 0;

		$columnsCountArray["PROJECT_STATUS_UNKNOWN"]		 = 0; // sera incrémenté dans la boucle de parcours des données principales ci-dessous
		$columnsOppAmountTotal["PROJECT_STATUS_UNKNOWN"] = 0; // sera renseigné dans la boucle de parcours des données principales ci-dessous
		//
		// on interdit le Drop sur une colonne dont l'état est inconnu
		$columnsArray[$i]['allowDrop']									 = false;
	}
}
else {
	dol_syslog($db->lasterror(), LOG_ERR);
	setEventMessages($db->lasterror(), null, 'errors');
}
/// ---
//
// --------------------------- données principales
// 
$projectsIDsCommaSeparated = '';

if (!empty($conf->global->KANVIEW_SHOW_PICTO))
	$fieldImageUrl = 'societe_logo';
else
	$fieldImageUrl = 'unknown';

if ($num >= 0) {
	$i = 0;

	// parcours des résultas
	while ($i < $num) {

		$obj = $ReqObject->lines[$i];

		// $dataArray[$i]['nom_field'] = $obj->nom_field;
		if (!$conf->kanviewplus->enabled) {
			$dataArray[$i]['priority'] = - $obj->datec; // date création timestamp inversé pour le trie descendant des cartes du kanban, voir fields.priority
		}
		else {
			// fonctionnalité expérimentale
			$dataArray[$i]['priority'] = $obj->datee; // date fin timestamp pour le trie ascendant des cartes du kanban, voir fields.priority
		}

		// collecte des ids des projets (utilisé plus loin lors de la collecte des contacts des projets)
//		if (!empty($projectsIDsCommaSeparated))
//			$projectsIDsCommaSeparated	 .= ',';
//		$projectsIDsCommaSeparated	 .= $obj->rowid;

		$dataArray[$i]['rowid']							 = $obj->rowid;
		$dataArray[$i]['dateo']							 = $obj->dateo;
		$dataArray[$i]['datee']							 = $obj->datee;
		$dataArray[$i]['ref']								 = $obj->ref;
		$dataArray[$i]['title']							 = $obj->title;
		$dataArray[$i]['description']				 = $obj->description;
		$dataArray[$i]['fk_user_creat']			 = $obj->fk_user_creat;
		$dataArray[$i]['public']						 = $obj->public;
		$dataArray[$i]['opp_percent']				 = $obj->opp_percent;
		$dataArray[$i]['date_close']				 = $obj->date_close;
		$dataArray[$i]['fk_user_close']			 = $obj->fk_user_close;
		$dataArray[$i]['opp_amount']				 = $obj->opp_amount;
		$dataArray[$i]['budget_amount']			 = price($obj->budget_amount, 0, '', 0, 0, 2, 'auto');
		$dataArray[$i]['fk_statut']					 = $obj->fk_statut;
		$dataArray[$i]['fk_opp_status']			 = $obj->fk_opp_status;
		$dataArray[$i]['opp_status_code']		 = ((!empty($obj->opp_status_code)) ? $obj->opp_status_code : "PROJECT_STATUS_UNKNOWN"); // keyField
		$dataArray[$i]['opp_status_label']	 = ((!empty($obj->opp_status_label)) ? $obj->opp_status_label : $langs->transnoentities("PROJECT_STATUS_UNKNOWN"));
		$dataArray[$i]['position']					 = $obj->position;
		$dataArray[$i]['opp_status_percent'] = $obj->opp_status_percent;
		$dataArray[$i]['lead_status_active'] = $obj->lead_status_active;
		$dataArray[$i]['fk_soc']						 = $obj->fk_soc;
		$dataArray[$i]['societe_logo']			 = $obj->societe_logo;
		$dataArray[$i]['societe_nom']				 = $obj->societe_nom;

		$columnsCountArray[$dataArray[$i]['opp_status_code']]			 += 1; // on incrémente le nbre d'éléments dans la colonne
		$columnsOppAmountTotal[$dataArray[$i]['opp_status_code']]	 += $obj->opp_amount; // on additionne le montant de l'opportunité de la même colonne

		$statut = '';
		switch ($obj->fk_statut) {
			case 0:
				$statut	 = $langs->trans('DRAFT');
				break;
			case 1:
				$statut	 = $langs->trans('OPENED');
				break;
			case 2:
				$statut	 = $langs->trans('CLOSED');
				break;
		}

		// la rubrique image a un traitement supplémentaire pour générer l'url complète de l'image
		if ((!empty($fieldImageUrl)) && !empty($obj->{$fieldImageUrl})) {
			$dataArray[$i][$fieldImageUrl] = DOL_URL_ROOT . '/viewimage.php?modulepart=societe&file=' . $obj->fk_soc . '/logos/' . urlencode($obj->{$fieldImageUrl});
		}
		else {
			// $dataArray[$i][$fieldImageUrl] = DOL_URL_ROOT . '/viewimage.php?modulepart=societe&file=';
		}

		// traitements additionnels si nécessaire
		$dataArray[$i]['dateo']			 = dol_print_date($dataArray[$i]['dateo'], 'day', 'tzuser');
		$dataArray[$i]['datee']			 = dol_print_date($dataArray[$i]['datee'], 'day', 'tzuser');
		$dataArray[$i]['opp_amount'] = str_replace(',', '.', price($dataArray[$i]['opp_amount'], 0, '', 0, 0, 2, 'auto'));

		// gestion tooltip
		$prefix														 = '<div id="projet-' . $obj->rowid . '">'; // encapsulation du contenu dans un div pour permettre l'affichage du tooltip
		$suffix														 = '</div>';
		$dataArray[$i]['tooltip_content']	 = '<table><tbody>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainProjets_Fieldref') . '</b></td><td>: <span class="tooltip-ref-' . $obj->rowid . '">' . $obj->ref . '</span></td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainProjets_Fieldtitle') . '</b></td><td>: ' . $obj->title . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainProjets_Fieldsociete_nom') . '</b></td><td>: ' . $obj->societe_nom . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainProjets_Fieldpublic') . '</b></td><td>: ' . ($obj->public == 0 ? $langs->trans('PROJECT_VISIBILITY_PRIVATE') : $langs->trans('PROJECT_VISIBILITY_PUBLIC')) . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainProjets_Fieldopp_percent') . '</b></td><td>: <span id="' . $obj->rowid . '-opp_percent">' . (!empty($obj->opp_percent) ? $obj->opp_percent . '</span>%' : '') . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainProjets_Fieldopp_amount') . '</b></td><td>: ' . price($obj->opp_amount, 0, '', 0, 0, 2, 'auto') . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainProjets_Fieldbudget_amount') . '</b></td><td>: ' . price($obj->budget_amount, 0, '', 0, 0, 2, 'auto') . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainProjets_Fielddateo') . '</b></td><td>: ' . dol_print_date($obj->dateo, 'day', 'tzuser') . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainProjets_Fielddatee') . '</b></td><td>: <span id="' . $obj->rowid . '-datee">' . dol_print_date($obj->datee, 'day', 'tzuser') . '</span></td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainProjets_Fieldfk_statut') . '</b></td><td>: ' . $statut . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '</tbody></table>';

		// contenu
		$dataArray[$i]['label']	 = $obj->title; // on garde une trace du libellé "brute"
		$dataArray[$i]['title']	 = '<a class="object-link" href="' . DOL_URL_ROOT . '/projet/card.php?id=' . $obj->rowid . '" target="_blank">' . $obj->ref . '</a>' . $prefix . $obj->title . $suffix;

		// fonctionnalité expérimentale
		if ($conf->kanviewplus->enabled) {
			// title
			$thirdpartyCard = 'card.php';
			if ($compareVersionTo600 === -1) { // < 6.0.0
				$thirdpartyCard = 'soc.php';
			}
			$dataArray[$i]['title']	 = $prefix;
			$dataArray[$i]['title']	 .= '<a class="object-link" href="' . DOL_URL_ROOT . '/societe/' . $thirdpartyCard . '?id=' . $obj->fk_soc . '" target="_blank" style="font-size: 0.9em;"><b>' . $dataArray[$i]['societe_nom'] . '</b></a>';
			$dataArray[$i]['title']	 .= '<br>';
			$dataArray[$i]['title']	 .= '<a class="object-link" href="' . DOL_URL_ROOT . '/projet/card.php?id=' . $obj->rowid . '" target="_blank" style="font-size: 0.9em;"><i>' . $dataArray[$i]['label'] . '</i></a>';
			$dataArray[$i]['title']	 .= '<br>';
			$dataArray[$i]['title']	 .= $dataArray[$i]['opp_amount'];
			$dataArray[$i]['title']	 .= '<br>';
			$dataArray[$i]['title']	 .= '<i><span id="' . $obj->rowid . '-datee">' . $dataArray[$i]['datee'] . '</span></i>';
			$dataArray[$i]['title']	 .= $suffix;

			// background-color en fonction de datee
			$now = dol_now();
			if (empty($obj->datee)) {
				$dataArray[$i]['dateeStatus'] = 'notProvided';
			}
			elseif ($now >= $obj->datee) {
				$dataArray[$i]['dateeStatus'] = 'exceeded';
			}
			else {
				$dataArray[$i]['dateeStatus'] = 'notReached';
			}
		}

		$i++; // prochaine ligne de données
	}

	unset($ReqObject);
}
else {
	$error++;
	setEventMessages(join('-', $ReqObject->errors), null, 'errors');
	dol_print_error($db);
}

// formatage des totaux des montants opportunités pour chaque colonne
// garder cette boucle avant la celle qui la suit
foreach ($columnsOppAmountTotal as $key => $value) {
	$valueFormated							 = str_replace(',', '.', price($value, 0, '', 0, 0, 2, 'auto'));
	$columnsOppAmountTotal[$key] = $valueFormated;
}

// nbre d'éléments dans une colonnes. on utilise :
// soit la propriété "enableTotalCount: true" du kanban
// soit on ajoute le nbre d'éléments de la colonne au titre de celle-ci
// en fonction de la constante cachée KANVIEW_ENABLE_NATIVE_TOTAL_COUNT
$kanbanHeaderCounts			 = array(); // si action ajax, ce tableau permet la mise à jour du nbre des taches de chaque colonne
$enableNativeTotalCount	 = false;
if (!empty($conf->global->KANVIEW_ENABLE_NATIVE_TOTAL_COUNT))
	$enableNativeTotalCount	 = true;
if (!$enableNativeTotalCount) {
	$countColumns = count($columnsCountArray);
	for ($i = 0; $i < $countColumns; $i++) {
		foreach ($columnsCountArray as $key => $value) {
			if ($columnsArray[$i]['key'] === $key) {
				$columnsArray[$i]['headerText']	 .= ' <span id="' . $key . '" class="badge">' . $value . '</span>';
				// (cette fonctionnalité est en cours de développement)
				// if ($conf->kanviewplus->enabled && $user->rights->kanviewplus->canuse) {
				$columnsArray[$i]['headerText']	 .= '<br><b><div id="total_opp_amount-' . $key . '" class="total_opp_amount">' . $columnsOppAmountTotal[$key] . '</div></b>';
				// }

				$kanbanHeaderCounts[$key] = $value; // si action ajax, ce tableau permet la mise à jour du nbre des taches de chaque colonne
				break;
			}
		}
	}
}

//
// ---------------------------- collecte des contacts des taches (doit rester après les données principales)
//
$WHERE											 = '';
include_once dol_buildpath('/kanview/class/req_kb_internal_project_contacts.class.php');
$ReqProjectsInternalContacts = new ReqKbInternalProjectContacts($db);
$ORDERBY										 = 'type_contact_code DESC, firstname, lastname';
// $WHERE										 = 't.element_id IN (' . $projectsIDsCommaSeparated . ') '; // on ne prend que les contacts des tâches collectées ci-dessus
$nbProjectsInternalContacts	 = $ReqProjectsInternalContacts->fetchAll(0, 0, $ORDERBY, true, $WHERE, false);
// $nbtotalofrecords_tic		 = $ReqProjectsInternalContacts->nbtotalofrecords;

include_once dol_buildpath('/kanview/class/req_kb_external_project_contacts.class.php');
$ReqProjectsExternalContacts = new ReqKbExternalProjectContacts($db);
$ORDERBY										 = 'type_contact_code DESC, firstname, lastname';
// $WHERE										 = 't.element_id IN (' . $projectsIDsCommaSeparated . ') '; // on ne prend que les contacts des tâches collectées ci-dessus
$nbProjectsExternalContacts	 = $ReqProjectsExternalContacts->fetchAll(0, 0, $ORDERBY, true, $WHERE, false);
// $nbtotalofrecords_tec		 = $ReqProjectsExternalContacts->nbtotalofrecords;
//
// on réorganise ces contacts de façon à avoir un tableau associatif de format :
// $projectsResources['project_rowid'] = array(les ressources du projet)
// utilisé pour afficher les contacts du projet dans sa fiche Kanban  (TODO : à implémenter)
$projectsResources					 = array();
$resourcesDistinct					 = array(); // utilisé pour remplir la combo du filtre contact
$resourcesDistinctIdsTmp		 = array();
foreach ($ReqProjectsInternalContacts->lines as $resource) {
	$resource->projectResourceId					 = $resource->fk_socpeople;
	$resource->projectResourceIdWithSource = $resource->fk_socpeople . '-I'; // composition de l'ID de façon à avoir un id unique même en faisant l'UNION des contacts internes (llx_user) et externes (llx_socpeople) (ne pas modifier ce format car utilisé dans JS)
	$resource->projectResourceName				 = trim($resource->firstname . ' ' . $resource->lastname) . ' (I)';
	$resource->projectResourceType				 = $resource->type_contact_code;
	$resource->projectResourceSource			 = $resource->type_contact_source;
	$resource->projectResourceRowid				 = $resource->rowid;
	$resource->projectResourcePhoto				 = $resource->photo;

	$projectsResources[$resource->element_id][] = $resource;

	if (!in_array($resource->projectResourceIdWithSource, $resourcesDistinctIdsTmp)) {
		$resourcesDistinctIdsTmp[] = $resource->projectResourceIdWithSource;
		$resourcesDistinct[]			 = array(
				'id'	 => $resource->projectResourceIdWithSource,
				'name' => $resource->projectResourceName
		);
	}
}
foreach ($ReqProjectsExternalContacts->lines as $resource) {
	$resource->projectResourceId					 = $resource->fk_socpeople;
	$resource->projectResourceIdWithSource = $resource->fk_socpeople . '-E'; // composition de l'ID de façon à avoir un id unique même en faisant l'UNION des contacts internes (llx_user) et externes (llx_socpeople) ((ne pas modifier ce format car utilisé dans JS))
	$resource->projectResourceName				 = trim($resource->firstname . ' ' . $resource->lastname) . ' (E)';
	$resource->projectResourceType				 = $resource->type_contact_code;
	$resource->projectResourceSource			 = $resource->type_contact_source;
	$resource->projectResourceRowid				 = $resource->rowid;
	$resource->projectResourcePhoto				 = $resource->photo;

	$projectsResources[$resource->element_id][] = $resource;

	if (!in_array($resource->projectResourceIdWithSource, $resourcesDistinctIdsTmp)) {
		$resourcesDistinctIdsTmp[] = $resource->projectResourceIdWithSource;
		$resourcesDistinct[]			 = array(
				'id'	 => $resource->projectResourceIdWithSource,
				'name' => $resource->projectResourceName
		);
	}
}

// Complete $dataArray with data coming from external module
$parameters	 = array();
$object			 = null;
$reshook		 = $hookmanager->executeHooks('getKanbanData', $parameters, $object, $action);
if (!empty($hookmanager->resArray['dataarray']))
	$dataArray	 = array_merge($dataArray, $hookmanager->resArray['dataarray']);

// on trie le tableau des événements
usort($dataArray, 'natural_sort');

// paramètres de l'url
$params = '';

if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"])
	$params	 .= '&contextpage=' . $contextpage;
if ($limit > 0 && $limit != $conf->liste_limit)
	$params	 .= '&limit=' . $limit;
if ($search_all != '')
	$params	 = "&amp;sall=" . urlencode($search_all);
if ($sall != '')
	$params	 .= "&amp;sall=" . urlencode($sall);

if ($search_rowid != '')
	$params	 .= '&amp;search_rowid=' . urlencode($search_rowid);
if ($search_dateo != '')
	$params	 .= '&amp;search_dateo=' . urlencode($search_dateo);
if ($search_datee != '')
	$params	 .= '&amp;search_datee=' . urlencode($search_datee);
if ($search_ref != '')
	$params	 .= '&amp;search_ref=' . urlencode($search_ref);
if ($search_title != '')
	$params	 .= '&amp;search_title=' . urlencode($search_title);
if ($search_description != '')
	$params	 .= '&amp;search_description=' . urlencode($search_description);
if ($search_fk_user_creat != '')
	$params	 .= '&amp;search_fk_user_creat=' . urlencode($search_fk_user_creat);
if ($search_public != '')
	$params	 .= '&amp;search_public=' . urlencode($search_public);
if ($search_opp_percent != '')
	$params	 .= '&amp;search_opp_percent=' . urlencode($search_opp_percent);
if ($search_date_close != '')
	$params	 .= '&amp;search_date_close=' . urlencode($search_date_close);
if ($search_fk_user_close != '')
	$params	 .= '&amp;search_fk_user_close=' . urlencode($search_fk_user_close);
if ($search_opp_amount != '')
	$params	 .= '&amp;search_opp_amount=' . urlencode($search_opp_amount);
if ($search_budget_amount != '')
	$params	 .= '&amp;search_budget_amount=' . urlencode($search_budget_amount);
if ($search_fk_statut != '')
	$params	 .= '&amp;search_fk_statut=' . urlencode($search_fk_statut);
if ($search_fk_opp_status != '')
	$params	 .= '&amp;search_fk_opp_status=' . urlencode($search_fk_opp_status);
if ($search_opp_status_code != '')
	$params	 .= '&amp;search_opp_status_code=' . urlencode($search_opp_status_code);
if ($search_opp_status_label != '')
	$params	 .= '&amp;search_opp_status_label=' . urlencode($search_opp_status_label);
if ($search_position != '')
	$params	 .= '&amp;search_position=' . urlencode($search_position);
if ($search_opp_status_percent != '')
	$params	 .= '&amp;search_opp_status_percent=' . urlencode($search_opp_status_percent);
if ($search_lead_status_active != '')
	$params	 .= '&amp;search_lead_status_active=' . urlencode($search_lead_status_active);
if ($search_societe_nom != '')
	$params	 .= '&amp;search_societe_nom=' . urlencode($search_societe_nom);
if ($search_id != '')
	$params	 .= '&amp;search_id=' . urlencode($search_id);
if ($search_fk_soc != '')
	$params	 .= '&amp;search_fk_soc=' . urlencode($search_fk_soc);
$params	 .= '&amp;search_category=' . urlencode($search_category);

// -- date création période
// - début
if ($search_dd_datec_year != '')
	$params	 .= '&amp;search_dd_datec_year=' . urlencode($search_dd_datec_year);
if ($search_dd_datec_month != '')
	$params	 .= '&amp;search_dd_datec_month=' . urlencode($search_dd_datec_month);
if ($search_dd_datec_day != '')
	$params	 .= '&amp;search_dd_datec_day=' . urlencode($search_dd_datec_day);
// - fin
if ($search_df_datec_year != '')
	$params	 .= '&amp;search_df_datec_year=' . urlencode($search_df_datec_year);
if ($search_df_datec_month != '')
	$params	 .= '&amp;search_df_datec_month=' . urlencode($search_df_datec_month);
if ($search_df_datec_day != '')
	$params	 .= '&amp;search_df_datec_day=' . urlencode($search_df_datec_day);

if ($search_datec != '')
	$params	 .= '&amp;search_datec=' . urlencode($search_datec);
if ($search_tms != '')
	$params	 .= '&amp;search_tms=' . urlencode($search_tms);
if ($search_entity != '')
	$params	 .= '&amp;search_entity=' . urlencode($search_entity);
if ($search_fk_user_modif != '')
	$params	 .= '&amp;search_fk_user_modif=' . urlencode($search_fk_user_modif);
if ($search_note_private != '')
	$params	 .= '&amp;search_note_private=' . urlencode($search_note_private);
if ($search_note_public != '')
	$params	 .= '&amp;search_note_public=' . urlencode($search_note_public);
if ($search_contact != '')
	$params	 .= '&amp;search_contact=' . urlencode($search_contact);

if ($search_model_pdf != '')
	$params	 .= '&amp;search_model_pdf=' . urlencode($search_model_pdf);
if ($search_import_key != '')
	$params	 .= '&amp;search_import_key=' . urlencode($search_import_key);

/*
  limit=25
  &token=a6ba28aea6ec2788e4bc67c7167e80cd
  &current_view=
  &search_dd_datec_=01%2F10%2F2016
  &search_dd_datec_day=01
  &search_dd_datec_month=10
  &search_dd_datec_year=2016
  &search_df_datec_=22%2F04%2F2019
  &search_df_datec_day=22
  &search_df_datec_month=04
  &search_df_datec_year=2019
  &search_title=
  &search_fk_statut=
  &search_fk_soc=
  &refresh=Rafraichir
 */

// ***************************************************************************************************************
//
//                                           Actions part 2 - Après collecte de données
//
// ***************************************************************************************************************
//
// suite de l'action if ($action == 'cardDragStop')
if ($action == 'cardDrop') {
	if (is_array($response) && $response['status'] == 'OK') {
		$response['data']['kanbanHeaderCounts']		 = $kanbanHeaderCounts;
		$response['data']['columnsOppAmountTotal'] = $columnsOppAmountTotal;
	}
	exit(json_encode($response));
}

//
// **************************************************************************************************************
//
//                                      VIEW - Envoi du header et Filter
//
// ***************************************************************************************************************

$LIB_URL_RELATIVE = str_replace(DOL_URL_ROOT, '', dol_buildpath('/kanview/lib', 1));

$arrayofcss		 = array();
$arrayofcss[]	 = $LIB_URL_RELATIVE . '/sf/Content/ejthemes/default-theme/ej.web.all.min.css';
$arrayofcss[]	 = $LIB_URL_RELATIVE . '/sf/Content/ejthemes/responsive-css/ej.responsive.css';
$arrayofcss[]	 = str_replace(DOL_URL_ROOT, '', dol_buildpath('/kanview', 1)) . '/css/kanview.css?b=' . $build;
// $arrayofcss[]	 = dol_buildpath('/kanview/css/', 1) . str_replace('.php', '.css', basename($_SERVER['SCRIPT_NAME'])) . '?b=' . $build;

$arrayofjs	 = array();
// $arrayofjs[] = $LIB_URL_RELATIVE . '/sf/js/jquery-3.1.1.min.js';
$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/jsrender.min.js';

// ----------------------------------------- sf ---------------------------------------------------
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
$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.checkbox.min.js?b=' . $build;
$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.datepicker.min.js?b=' . $build;
$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.datetimepicker.min.js?b=' . $build;
$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.dialog.min.js?b=' . $build;
$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.dropdownlist.min.js?b=' . $build;
$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.editor.min.js?b=' . $build;
$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.kanban.min.js?b=' . $build;
$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.menu.min.js?b=' . $build;
$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.rte.min.js?b=' . $build;
$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.toolbar.min.js?b=' . $build;
$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.waitingpopup.min.js?b=' . $build;
$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.listbox.min.js?b=' . $build;
$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.tooltip.min.js?b=' . $build;

// ----- sf traductions (garder les après common et others)
if (in_array($langs->defaultlang, array('fr_FR', 'en_US'))) {
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/i18n/ej.culture.' . str_replace('_', '-', $langs->defaultlang) . '.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/l10n/ej.localetexts.' . str_replace('_', '-', $langs->defaultlang) . '.min.js?b=' . $build;
}
else {
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/i18n/ej.culture.fr-FR.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/l10n/ej.localetexts.fr-FR.min.js?b=' . $build;
}
/// ----------

$help_url = ''; // EN:Module_Kanban_En|FR:Module_Kanban|AR:M&oacute;dulo_Kanban';
llxHeader('', $langs->trans("Kanview_KB_Projets"), $help_url, '', 0, 0, $arrayofjs, $arrayofcss, '');

$form = new Form($db);

//$head = kanview_kanban_prepare_head($params);
// le selecteur du nbre d'éléments par page généré par print_barre_liste() doit se trouver ds le <form>
// cette ligne doit donc rester avant l'appel à print_barre_liste()
print '<form id="listactionsfilter" name="listactionsfilter" class="listactionsfilter" action="' . $_SERVER["PHP_SELF"] . '" method="get">';

// dol_fiche_head($head, $tabactive, $langs->trans('Kanview_KB_Projets'), 0, 'action');
/// ------------------------------------------- fin zone du filtre
//
// __KANBAN_BEFORE_TITLE__
//
// titre du Kanban
$title = $langs->trans('Kanview_KB_Projets');
print_barre_liste($title, intval($page), $_SERVER["PHP_SELF"], $params, $sortfield, $sortorder, '', intval($num) + 1, intval($nbtotalofrecords), 'title_project', 0, '', '', intval($limit));

// __KANBAN_AFTER_TITLE__
//
// ------------------------------------------- zone Filter
//


print '<input id="input_token" type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="current_view" value="' . $current_view . '">';

print '<div class="fichecenter">';

// pour afficher la légende, on encapsule le filtre et la légende dans une table avec un seul <tr>
// et on affiche le filtre dans le 1er <td> et la légende dans le 2e <td>
// (ceci pour grand ecran uniquement, pour les autres on affcihe pas la légende)
// voir plus loin pour le fieldset légende
if (empty($conf->browser->phone)) {
	print '<table style="width: 100%; height: 100%; margin-bottom: 15px; padding-bottom: 0px;">';
	print '<tr style="width: 100%; height: 100%;">';
	print '<td style="width: 80%">';
}
/// ---
// fieldset filtre - formulaire et table contenant le filtre pour le Kanban
print '<fieldset class="filters-fields" style="width: 99%; height: 100%; padding-right: 1px;">';
print '<legend><span class="e-icon e-filter" style="text-align: left;"></span></legend>';

if (!empty($conf->browser->phone))
	print '<div class="fichehalfleft">';
else
	print '<table class="nobordernopadding" width="100%"><tr style="width: 100%; height: 100%;"><td class="borderright">';

print '<table class="nobordernopadding" width="100%">';

//
// ------------- filtre dateo - période
//
$value = empty($search_dd_datec) ? - 1 : $search_dd_datec;
echo '<tr id="tr-periode">';
echo '<td class="td-card-label">' . $langs->trans("ReqKbMainProjets_Fielddatec") . '</td>';
echo '<td>' . $langs->trans("Du") . '</td>';
echo '<td class="td-card-data">';
$form->select_date($value, 'search_dd_datec_', '', '', '', "dd", 1, 1); // datepicker
$value = empty($search_df_datec) ? - 1 : $search_df_datec;
echo '&nbsp;&nbsp;&nbsp;&nbsp;<span>' . $langs->trans("Au") . '</span>&nbsp;';
$form->select_date($value, 'search_df_datec_', '', '', '', "df", 1, 1); // datepicker
echo '</td>';
echo '</tr>';

//
// ------------- filtre title
//
echo '<tr id="tr-search_title" class="tr-external-filter">';
echo '<td class="td-card-label">' . $langs->trans('ReqKbMainProjets_Fieldtitle') . '</td>';
echo '<td>' . '</td>';
echo '<td id="title_td_filtre" class="liste_filtre" align="" valign="">';
echo '<div class="div-external-filter">';

echo '<input  id="title_input_filtre" align="" type="text" class="flat" name="search_title" ' .
 'value="' . $search_title . '" size="10">';
echo '</div>';
echo '</td>';
echo '</tr>';

//
// ------------- filtre fk_statut
//
echo '<tr id="tr-search_fk_statut" class="tr-external-filter">';
echo '<td class="td-card-label">' . $langs->trans('ReqKbMainProjets_Fieldfk_statut') . '</td>';
echo '<td>' . '</td>';
echo '<td id="fk_statut_td_filtre" class="liste_filtre td-card-data" align="" valign="">';
echo '<div class="div-external-filter">';

$values					 = 'DRAFT,OPENED,CLOSED';
$keys						 = '0,1,2';
$valuesArray		 = explode(',', $values);
$keysArray			 = explode(',', $keys);
$count					 = count($valuesArray);
$defaultValue		 = '';
$blanckOption		 = 1;
$optionSelected	 = '';
if ($count > 0) {
	for ($i = 0; $i < $count; $i++) {
		if ((isset($keysArray[$i]) && $keysArray[$i] == $search_fk_statut) || (!isset($keysArray[$i]) && $valuesArray[$i] == $search_fk_statut)) {
			$optionSelected = (!isset($keysArray[$i]) ? $valuesArray[$i] : $keysArray[$i]);
			break;
		}
	}
	echo '<select  id="fk_statut_input_filtre" class="flat" name="search_fk_statut" title="">';
	if ($optionSelected == '' && !$blanckOption) {
		$optionSelected = $defaultValue;
	}
	if ($blanckOption) {
		echo '<option value="" ' . ($search_fk_statut == '' ? 'selected' : '') . '></option>';
	}
	for ($i = 0; $i < $count; $i++) {
		if ($optionSelected == (!isset($keysArray[$i]) ? $valuesArray[$i] : $keysArray[$i]))
			$selected	 = 'selected';
		else
			$selected	 = '';
		echo '<option value="' . (!isset($keysArray[$i]) ? $valuesArray[$i] : $keysArray[$i]) . '" ' . $selected . '>' . (!empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) : '') . '</option>';
	}
	echo '</select>';
}
else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
	echo '<input id="fk_statut" class="flat" name="fk_statut" title="" value="' . ( isset($object->fk_statut) ? $object->fk_statut : '') . '">';
}
echo '</div>';
echo '</td>';
echo '</tr>';
/// ---
//
// ------------- filtre fk_soc
//
echo '<tr id="tr-search_fk_soc" class="tr-external-filter"><td class="td-card-label">' . $langs->trans('ReqKbMainTasks_Fieldfk_soc') . '</td>';
echo '<td>' . '' . '</td>';
echo '<td id="fk_soc_td_filtre" class="liste_filtre" align="" valign="">';
echo '<div class="div-external-filter">';

$SQL						 = "SELECT
	t.rowid AS rowid,
	t.nom AS societe_nom,
	t.name_alias AS name_alias,
	t.ref_int AS ref_int
FROM
	" . MAIN_DB_PREFIX . "societe AS t
WHERE
	t.status = 1
ORDER BY
	t.nom
";
$sql_fk_soc			 = $db->query($SQL);
$options				 = array();
$optionSelected	 = '';
$defaultValue		 = '';
$blanckOption		 = 1;
if ($sql_fk_soc) {
	while ($obj_fk_soc = $db->fetch_object($sql_fk_soc)) {
		if ($obj_fk_soc->rowid == $search_fk_soc) {
			$optionSelected = $obj_fk_soc->rowid;
		}
		$options[$obj_fk_soc->rowid] = $obj_fk_soc->societe_nom;
	}
	$db->free($sql_fk_soc);
}
else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . $db->lasterror(), LOG_ERR);
}
echo '<select  id="fk_soc_input_filtre" class="flat" name="search_fk_soc" title="">';
if (empty($optionSelected) && !$blanckOption) {
	$optionSelected = $defaultValue;
}
if ($blanckOption) {
	echo '<option value="" ' . (empty($search_fk_soc) ? 'selected' : '') . '></option>';
	echo '<option value="" ' . (empty($search_fk_soc) ? 'selected' : '') . '></option>'; // pour une raison obscure, en mode ajax, il faut 2 options vides pour afficher une option vide
}
foreach ($options as $key => $value) {
	echo '<option value="' . $key . '" ' . ($key == $optionSelected ? 'selected' : '') . '>' . $value . '</option>';
}
echo '</select>';
echo '</div>';
echo '</td>';
echo '</tr>';
echo ajax_combobox('fk_soc_input_filtre');
/// -----
//
// ------------- filtre category
//
echo '<tr id="tr-search_category" class="tr-external-filter"><td class="td-card-label">' . ('Tag/Catégorie') . '</td>';
echo '<td>' . '' . '</td>';
echo '<td id="category_td_filtre" class="liste_filtre" align="" valign="">';
echo '<div class="div-external-filter">';

// t.type = 6 c'est le type projet pour les catégories (voir categorie.class.php)
$SQL						 = "SELECT
	t.rowid AS rowid,
	t.label AS category_ref,
	t.description AS category_description,
	t.type AS category_type
FROM
	" . MAIN_DB_PREFIX . "categorie AS t
WHERE
	t.type = 6
ORDER BY
	t.label
";
$sql_category		 = $db->query($SQL);
$options				 = array();
$optionSelected	 = '';
$defaultValue		 = '';
$blanckOption		 = 1;
if ($sql_category) {
	while ($obj_category = $db->fetch_object($sql_category)) {
		if ($obj_category->rowid == $search_category) {
			$optionSelected = $obj_category->rowid;
		}
		$options[$obj_category->rowid] = $obj_category->category_ref;
	}
	$db->free($sql_category);
}
else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . $db->lasterror(), LOG_ERR);
}
echo '<select  id="category_input_filtre" class="flat" name="search_category" title="">';
if (empty($optionSelected) && !$blanckOption) {
	$optionSelected = $defaultValue;
}
if ($blanckOption) {
	echo '<option value="" ' . (empty($search_category) ? 'selected' : '') . '></option>';
}
foreach ($options as $key => $value) {
	echo '<option value="' . $key . '" ' . ($key == $optionSelected ? 'selected' : '') . '>' . $value . '</option>';
}
echo '</select>';
echo '</div>';
echo '</td>';
echo '</tr>';
/// ---
// 
//
// ------------- filtre -- contact
//
echo '<tr id="tr-search_contact" class="tr-external-filter"><td class="td-card-label">' . $langs->trans('ReqKbMainTasks_FieldContact') . '</td>';
echo '<td>' . '' . '</td>';
echo '<td id="contact_td_filtre" class="liste_filtre" align="" valign="">';
echo '<div class="div-external-filter">';
echo '<select  id="contact_input_filtre" class="flat" name="search_contact" title="">';
echo '<option value="" ' . (empty($search_contact) ? 'selected' : '') . '></option>';
$count = count($resourcesDistinct);
for ($i = 0; $i < $count; $i++) {
	echo '<option value="' . $resourcesDistinct[$i]['id'] . '" ' . ($resourcesDistinct[$i]['id'] == $search_contact ? 'selected' : '') . '>' . $resourcesDistinct[$i]['name'] . '</option>';
}
echo '</select>';
echo '</div>';
echo '</td>';
echo '</tr>';
/// -----

print '</table>';

if (!empty($conf->browser->phone))
	print '</div>';
else
	print '</td>';

if (!empty($conf->browser->phone))
	print '<div class="fichehalfright">';
else
	print '<td align="center" valign="middle" class="nowrap">';

// ---- bouton refresh
print '<table><tr><td align="center">';
print '<div class="formleftzone">';
print '<input type="submit" class="button" style="min-width:120px" name="refresh" value="' . $langs->trans("Refresh") . '">';
print '</div>';
print '</td></tr>';
print '</table>';

if (!empty($conf->browser->phone))
	print '</div>';
else
	print '</td></tr></table>';

print '</fieldset>'; /// .filters-fields
//
// ------ fieldset legend
// la légende n'est affichée que pour grand écran
if (empty($conf->browser->phone)) {
	print '</td>';
	print '<td style="width: 80%; ">'; // garder les 80% sinon défaut d'affichage
	print '<fieldset class="filters-fields" style="height: 100%; padding-left: 5px;">';
	print '<legend><span class="" style="text-align: left;">' . $langs->trans('LEGEND') . '</span></legend>';
	print '<table>';
	// -- legend 1
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . $conf->global->KANVIEW_PROJETS_DRAFT_COLOR . ' transparent;">'
// le découpage suivant n'a pas marché sur FF
//			. 'border-color-top: transparent; '
//			. 'border-color-right: transparent; '
//			. 'border-color-bottom: #007bff; '
//			. 'border-color-left: transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_PROJETS_DRAFT_COLOR') . '</td>';
	print '</tr>';
	// -- legend 2
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . $conf->global->KANVIEW_PROJETS_OPEN_COLOR . ' transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_PROJETS_OPEN_COLOR') . '</td>';
	print '</tr>';
	// -- legend 3
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . $conf->global->KANVIEW_PROJETS_CLOSED_COLOR . ' transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_PROJETS_CLOSED_COLOR') . '</td>';
	print '</tr>';
	// -- legend 4
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . '#179BD7' . ' transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_STATUS_UNKNOWN_COLOR') . '</td>';
	print '</tr>';
	// -- legend 5 (TAG)
	print '<tr>';
	print '<td>';
	print '<div class="legend-name">TAG</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans(strtoupper($conf->global->KANVIEW_PROJETS_TAG)) . '</td>';
	print '</tr>';

	print '</table>';
	print '</fieldset>';
	print '</td>';
	print '</tr>';
	print '</table>';
}
/// --- fin légend

print '</div>'; // Close fichecenter
print '<div style="clear:both"></div>';

print '</form>';
//
// -- si pas de données on le dit --
/* // pas la peine, kanban s'en charge
  if ($num === 0) {
  echo '<div id="AucunElementTrouve">';
  echo '<p>' . $langs->trans('AucunElementTrouve') . '</p>';
  echo '</div>';
  }
 */

// **************************************************************************************************************
//
//                                           VIEW - Kanban Output
//
// ***************************************************************************************************************

$columns		 = "[]";
$kanbanData	 = "[]";
$columnIDs	 = "[]";

if (count($columnsArray) > 0) {

	// --- titres des colunnes dans un tableau d'objets json
	$count	 = count($columnsArray);
	$columns = "[";
	for ($i = 0; $i < $count; $i++) {
		$columns .= json_encode($columnsArray[$i]) . ",";
	}
	$columns .= "]";

	// --- données principales du kanban dans un tableau d'objets json
	$count			 = count($dataArray);
	$kanbanData	 = "[";
	for ($i = 0; $i < $count; $i++) {
		$kanbanData .= json_encode($dataArray[$i]) . ",";
	}
	$kanbanData .= "]";

	$columnIDs = json_encode($columnsIDsArray);

	// $now = dol_print_date(dol_now('tzuser'), $format = '%Y-%m-%d %H:%M:%S', $tzoutput = 'tzuser', $outputlangs = '', $encodetooutput = false);
}

// __KANBAN_AFTER_KANBAN__
//
///
//
// --------------------------------------- END Output

dol_fiche_end(); // fermeture du cadre
// ----------------------------------- javascripts spécifiques à cette page
// quelques variables javascripts fournis par php
echo '<script type="text/javascript">
 	var dateSeparator							= "' . trim(substr($langs->trans('FormatDateShort'), 2, 1)) . '";
	var DOL_URL_ROOT							= "' . trim(DOL_URL_ROOT) . '";
	var DOL_VERSION							= "' . trim(DOL_VERSION) . '";
 	var KANVIEW_URL_ROOT					= "' . trim(dol_buildpath('/kanview', 1)) . '";
	var fieldImageUrl							= "' . trim($fieldImageUrl) . '";

	var locale									= "' . trim($langs->defaultlang) . '";
	var sfLocale								= "' . trim(str_replace('_', '-', $langs->defaultlang)) . '";

	var UpdateNotAllowed_ProjectClosed							= "' . trim($langs->transnoentities('UpdateNotAllowed_ProjectClosed')) . '";
	var ActionNotAllowed_ProjectDestStatusDisabled	= "' . trim($langs->transnoentities('ActionNotAllowed_ProjectDestStatusDisabled')) . '";
	var ActionNotAllowed														= "' . trim($langs->transnoentities('ActionNotAllowed')) . '";
	var msgPrintKanbanView				= "' . trim($langs->transnoentities('msgPrintKanbanView')) . '";
	var ActionNotAllowed_ProjectDestStatusUnknown		= "' . trim($langs->transnoentities('ActionNotAllowed_ProjectDestStatusUnknown')) . '";

	var enableNativeTotalCount		= ' . trim(empty($enableNativeTotalCount) ? 'false' : 'true') . ';
	var tooltipsActive						= false;		// mémorise le fait que les tooltps sont activés ou non

	var columns							= ' . trim($columns) . ';
	var columnIDs						= ' . trim($columnIDs) . ';
	var kanbanData					= ' . trim($kanbanData) . ';
	var projets_tag					= "' . trim($conf->global->KANVIEW_PROJETS_TAG) . '";

	var confKanviewplusEnabled = ' . intVal($conf->kanviewplus->enabled) . ';
	var confGlobalKANVIEWPLUS_PROJECT_STATUSES_TRIGGERING_EMAIL = "' . trim($conf->global->KANVIEWPLUS_PROJECT_STATUSES_TRIGGERING_EMAIL) . '";
	var confGlobalKANVIEWPLUS_NO_DATEEND_COLOR = "' . trim($conf->global->KANVIEWPLUS_NO_DATEEND_COLOR) . '";
	var confGlobalKANVIEWPLUS_DATEEND_EXCEEDED_COLOR = "' . trim($conf->global->KANVIEWPLUS_DATEEND_EXCEEDED_COLOR) . '";
	var confGlobalKANVIEWPLUS_DATEEND_NOT_REACHED_COLOR = "' . trim($conf->global->KANVIEWPLUS_DATEEND_NOT_REACHED_COLOR) . '";

	var colorMapping =  {
				"' . trim($conf->global->KANVIEW_PROJETS_DRAFT_COLOR) . '": "0",
				"' . trim($conf->global->KANVIEW_PROJETS_OPEN_COLOR) . '": "1",
				"' . trim($conf->global->KANVIEW_PROJETS_CLOSED_COLOR) . '": "2",
			};

	var token = "' . trim($_SESSION['newtoken']) . '";

 	</script>';

// inclusion des fichiers js
echo '<script src="' . dol_buildpath('/kanview/js/kanview.js', 1) . '?b=' . $build . '"></script>';
echo '<script src="' . dol_buildpath('/kanview/js/', 1) . str_replace('.php', '.js', basename($_SERVER['SCRIPT_NAME'])) . '?b=' . $build . '"></script>';
$parameters	 = array();
$reshook		 = $hookmanager->executeHooks('projets_kb_addJS', $parameters, $dataArray); //, $object, $action); // Note that $action and $object may have been modified by hook
//if (empty($reshook))
//{
//   ... // standard code that can be disabled/replaced by hook if return code > 0.
//}

llxFooter();

$db->close();

// -------------------------------------------------- Functions ----------------------------------------
//
// -------------------------------- displayField (pour la vue liste)
// test si on doit afficher le champ ou non
function displayField($fieldName) {
	global $arrayfields, $secondary;
	if (((!empty($arrayfields[$fieldName]['checked'])) && empty($secondary)) || ((!empty($arrayfields[$fieldName]['checked'])) && (!empty($secondary) && empty($arrayfields[$fieldName]['hideifsecondary']))))
		return true;
	else
		return false;
}

// ---------------------------- preapre_head
function kanview_kanban_prepare_head($params) {
	global $langs, $conf, $user;
	global $action;

	$h		 = 0;
	$head	 = array();

	// kanban par ressources
	// __KANBAN_HEAD__

	$object = new stdClass();

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array('entity:+tabname:Title:@kanview:/kanview/mypage.php?id=__ID__');   to add new tab
	// $this->tabs = array('entity:-tabname);   												to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'kanview_projets_kanban');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'kanview_projets_kanban', 'remove');

	return $head;
}

//
// ------------------------------------- natural_sort()
//
function natural_sort($a, $b) {
	global $sortfield, $sortorder;
	$sortorder = strtoupper($sortorder);
	if (empty($sortfield) || $sortfield == '1')
		$sortfield = 'id';
	if (empty($sortorder) || $sortorder == 'ASC')
		return strnatcasecmp($a[$sortfield], $b[$sortfield]);
	else
		return - strnatcasecmp($a[$sortfield], $b[$sortfield]);
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

// --------------------------------

