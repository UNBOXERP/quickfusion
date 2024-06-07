<?php

/*
 * Copyright (C) 2017-2019-2020 ProgSI (contact@progsi.ma)
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

/**
 * \file kanview/class/abstract_my_table.class.php
 * \ingroup kanview
 * \brief This file is a CRUD class file (Create/Read/Update/Delete)
 * Put some comments here
 */
// Protection (if external user for example)
if (!($conf->kanview->enabled)) {
	accessforbidden('', 0, 0);
	exit();
}

require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';

// $build = str_replace('.', '', KANVIEW_VERSION);

/**
 * Class ReqKbMainProjets
 *
 * Put some comments here
 */
class ReqKbMainProjets extends CommonObject {

	/**
	 *
	 * @var string module auquel appartient cet objet, ne doit pas être modifié
	 */
	public $modulepart = 'kanview';

	/**
	 * nom du champ id (ex.: 'rowid')
	 */
	public $idfield = 'rowid';

	/**
	 * nom du champ Ref (ex.
	 * : 'ref', 'code')
	 */
	public $reffield = 'ref';

	/**
	 * nbre total des enregistrements
	 *
	 */
	public $nbtotalofrecords = 0; // voir fetchAll()

	/**/
	public $container = 'kanview';

	/**
	 *
	 * @var ReqKbMainProjetsLine[] Lines
	 */
	public $lines = array();
	// public $prop1;

	public $rowid;
	public $id;
	public $fk_soc;
	public $datec;
	public $tms;
	public $dateo;
	public $datee;
	public $ref;
	public $entity;
	public $title;
	public $description;
	public $fk_user_creat;
	public $public;
	public $opp_percent;
	public $date_close;
	public $fk_user_close;
	public $note_private;
	public $note_public;
	public $opp_amount;
	public $budget_amount;
	public $model_pdf;
	public $import_key;
	public $fk_statut;
	public $fk_opp_status;
	public $opp_status_code;
	public $opp_status_label;
	public $position;
	public $opp_status_percent;
	public $lead_status_active;
	public $societe_nom;
	public $societe_name_alias;
	public $societe_logo;
	public $contactsIdsWithSource;

	// sql where params declarations


	/**
	 */
	// -------------------------------------------- __construct()

	/**
	 * Constructor
	 *
	 * @param DoliDb $db
	 *        	Database handler
	 */
	public function __construct(DoliDB $db) {
		global $langs;
		$this->db = $db;

		// désactivation temporaire du mode ONLY_FULL_GROUP_BY (qui interdit l'utilisation du GROUP BY sans tous les champs de la requête) 
		$sqlTmp1 = "SET SESSION sql_mode=(SELECT REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY,',''));";
		$sqlTmp2 = "SET SESSION sql_mode=(SELECT REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY,',''));";
		$db->query($sqlTmp1);
		$db->query($sqlTmp2);
		return 1;
	}

	// --------------------------------------------------- init()

	/**
	 * Initialise object with example values
	 *
	 * @return void
	 */
	public function init() {
		// $this->prop1 = '';

		$this->rowid								 = '';
		$this->id										 = '';
		$this->fk_soc								 = '';
		$this->datec								 = '';
		$this->tms									 = '';
		$this->dateo								 = '';
		$this->datee								 = '';
		$this->ref									 = '';
		$this->entity								 = '';
		$this->title								 = '';
		$this->description					 = '';
		$this->fk_user_creat				 = '';
		$this->public								 = '';
		$this->opp_percent					 = '';
		$this->date_close						 = '';
		$this->fk_user_close				 = '';
		$this->note_private					 = '';
		$this->note_public					 = '';
		$this->opp_amount						 = '';
		$this->budget_amount				 = '';
		$this->model_pdf						 = '';
		$this->import_key						 = '';
		$this->fk_statut						 = '';
		$this->fk_opp_status				 = '';
		$this->opp_status_code			 = '';
		$this->opp_status_label			 = '';
		$this->position							 = '';
		$this->opp_status_percent		 = '';
		$this->lead_status_active		 = '';
		$this->societe_nom					 = '';
		$this->societe_name_alias		 = '';
		$this->societe_logo					 = '';
		$this->contactsIdsWithSource = '';
	}

	// ------------------------------------------------------- fetchOne()

	/**
	 * Load first object in memory from the database
	 *
	 * @return int <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchOne($_ORDERBY = '', $_isNewOrderBy = true, $_WHERE = '', $_isNewWhere = true, $_HAVING = '', $_isNewHaving = true) {
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = $this->getCodeSQL($_ORDERBY, $_isNewOrderBy, $_WHERE, $_isNewWhere, $_HAVING, $_isNewHaving);

		// var_dump($sql);

		$resql = $this->db->query($sql);
		if ($resql) {
			$numrows = $this->db->num_rows($resql);
			if ($numrows) {
				$obj = $this->db->fetch_object($resql);

				// $this->id = $obj->rowid;
				$this->copyObject($obj, $this);
			}

			$this->db->free($resql);

			if ($numrows) {
				return 1;
			}
			else {
				return 0;
			}
		}
		else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return - 1;
		}
	}

	// ------------------------------------------------------- fetchOneByField()

	/**
	 * Load first object in memory from the database by field
	 *
	 * @return int <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchOneByField($fieldName, $fieldValue) {
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql					 = $this->getCodeSQL($ORDERBY			 = '', $isNewOrderBy	 = true, $WHERE				 = $fieldName . " = '" . $fieldValue . "' ", $isNewWhere		 = true, $HAVING				 = '', $isNewHaving	 = true);

		// var_dump($sql);

		$resql = $this->db->query($sql);
		if ($resql) {
			$numrows = $this->db->num_rows($resql);
			if ($numrows) {
				$obj = $this->db->fetch_object($resql);

				// $this->id = $obj->rowid;
				$this->copyObject($obj, $this);
			}

			$this->db->free($resql);

			if ($numrows) {
				return 1;
			}
			else {
				return 0;
			}
		}
		else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return - 1;
		}
	}

	// ------------------------------------------------------- fetchById()

	/**
	 * Load first object in memory from the database by Id
	 *
	 * @return int <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchById($rowid) {
		dol_syslog(__METHOD__, LOG_DEBUG);

		$idField = 'rowid';

		if (empty($idField))
			$idField = 'rowid';

		$sql					 = $this->getCodeSQL($ORDERBY			 = '', $isNewOrderBy	 = true, $WHERE				 = $idField . ' = ' . intval($rowid), $isNewWhere		 = true, $HAVING				 = '', $isNewHaving	 = true);

		// var_dump($sql);

		$resql = $this->db->query($sql);
		if ($resql) {
			$numrows = $this->db->num_rows($resql);
			if ($numrows) {
				$obj = $this->db->fetch_object($resql);

				// $this->id = $obj->rowid;
				$this->copyObject($obj, $this);
			}

			$this->db->free($resql);

			if ($numrows) {
				return 1;
			}
			else {
				return 0;
			}
		}
		else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return - 1;
		}
	}

	// ------------------------------------------------------- fetchByRef()

	/**
	 * Load first object in memory from the database by Ref
	 *
	 * @return int <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchByRef($ref) {
		dol_syslog(__METHOD__, LOG_DEBUG);

		$refField = 'ref';

		if (empty($refField))
			$refField = 'ref';

		$sql					 = $this->getCodeSQL($ORDERBY			 = '', $isNewOrderBy	 = true, $WHERE				 = $refField . " = '" . $ref . "' ", $isNewWhere		 = true, $HAVING				 = '', $isNewHaving	 = true);

		// var_dump($sql);

		$resql = $this->db->query($sql);
		if ($resql) {
			$numrows = $this->db->num_rows($resql);
			if ($numrows) {
				$obj = $this->db->fetch_object($resql);

				// $this->id = $obj->rowid;
				$this->copyObject($obj, $this);
			}

			$this->db->free($resql);

			if ($numrows) {
				return 1;
			}
			else {
				return 0;
			}
		}
		else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return - 1;
		}
	}

	// ---------------------------------------------- fetchAll()

	/**
	 * Load object in memory from the database
	 *
	 * @param int $LIMIT
	 *        	Clause LIMIT
	 * @param int $OFFSET
	 *        	Clause OFFSET
	 * @param string $ORDERBY
	 *        	Clause ORDER BY (sans le "ORDER BY", et même syntax que SQL)
	 *        	Ce paramètre, s'il est non vide, est prioritaire sur la clause ORDER BY initialement fourni par la requete de la classe
	 * @return int <0 if KO, number of records if OK
	 */
	public function fetchAll($LIMIT = 0, $OFFSET = 0, $ORDERBY = '', $isNewOrderBy = true, $WHERE = '', $isNewWhere = true, $HAVING = '', $isNewHaving = true) {
		dol_syslog(__METHOD__, LOG_DEBUG);

		global $conf;

		$sql = $this->getCodeSQL($ORDERBY, $isNewOrderBy, $WHERE, $isNewWhere, $HAVING, $isNewHaving);

		// nbre total des enregistrements (avant d'appliquer limit/offset)
		if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
			$result									 = $this->db->query($sql);
			if ($result)
				$this->nbtotalofrecords	 = $this->db->num_rows($result);
		}

		if (!empty($LIMIT)) {
			$sql .= ' ' . $this->db->plimit($LIMIT, $OFFSET);
		}

		$this->lines = array();

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);

			while ($obj = $this->db->fetch_object($resql)) {
				$line = new ReqKbMainProjetsLine();

				// $line->id = $obj->rowid;
				$this->copyObject($obj, $line);

				$this->lines[] = $line;
			}

			$this->db->free($resql);

			return $num;
		}
		else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return - 1;
		}
	}

	// -------------------------------------------------------------------------------------------
	//
	//	                                    getProjectsAuthorizedForUser()
	//
	//

	/**
	 * Return array of projects a user has permission on, is affected to, or all projects
	 *
	 * @param 	User	$user			User object
	 * @param 	int		$mode			0=All project I have permission on (assigned to me and public), 1=Projects assigned to me only, 2=Will return list of all projects with no test on contacts
	 * @param 	int		$list			0=Return array,1=Return string list
	 * @param	int		$socid			0=No filter on third party, id of third party
	 * @return 	array or string			Array of projects id, or string with projects id separated with ","
	 */
	function getProjectsAuthorizedForUser($user, $mode = 0, $list = 0, $socid = 0) {
		$projects	 = array();
		$temp			 = array();

		$sql = "SELECT " . (($mode == 0 || $mode == 1) ? "DISTINCT " : "") . "p.rowid, p.ref";
		$sql .= " FROM " . MAIN_DB_PREFIX . "projet as p";
		if ($mode == 0 || $mode == 1) {
			$sql .= ", " . MAIN_DB_PREFIX . "element_contact as ec";
		}
		$sql .= " WHERE 1 = 1 ";
		// $sql .= " WHERE p.entity IN (" . getEntity('project', 1) . ")";
		// Internal users must see project he is contact to even if project linked to a third party he can't see.
		//if ($socid || ! $user->rights->societe->client->voir)	$sql.= " AND (p.fk_soc IS NULL OR p.fk_soc = 0 OR p.fk_soc = ".$socid.")";
		if ($socid > 0)
			$sql .= " AND (p.fk_soc IS NULL OR p.fk_soc = 0 OR p.fk_soc = " . $socid . ")";

		// Get id of types of contacts for projects (This list never contains a lot of elements)
		$listofprojectcontacttype	 = array();
		$sql2											 = "SELECT ctc.rowid, ctc.code FROM " . MAIN_DB_PREFIX . "c_type_contact as ctc";
		$sql2											 .= " WHERE ctc.element = '" . 'project' . "'"; // $sql2.= " WHERE ctc.element = '" . $this->db->escape($this->element) . "'";
		$sql2											 .= " AND ctc.source = 'internal'";
		$resql										 = $this->db->query($sql2);
		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
				$listofprojectcontacttype[$obj->rowid] = $obj->code;
			}
		}
		else
			dol_print_error($this->db);
		if (count($listofprojectcontacttype) == 0)
			$listofprojectcontacttype[0] = '0'; // To avoid syntax error if not found

		if ($mode == 0) {
			$sql .= " AND ec.element_id = p.rowid";
			$sql .= " AND ( p.public = 1";
			$sql .= " OR ( ec.fk_c_type_contact IN (" . join(',', array_keys($listofprojectcontacttype)) . ")";
			$sql .= " AND ec.fk_socpeople = " . $user->id . ")";
			$sql .= " )";
		}
		if ($mode == 1) {
			$sql .= " AND ec.element_id = p.rowid";
			$sql .= " AND (";
			$sql .= "  ( ec.fk_c_type_contact IN (" . join(',', array_keys($listofprojectcontacttype)) . ")";
			$sql .= " AND ec.fk_socpeople = " . $user->id . ")";
			$sql .= " )";
		}
		if ($mode == 2) {
			// No filter. Use this if user has permission to see all project
		}
		//print $sql;

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i	 = 0;
			while ($i < $num) {
				$row							 = $this->db->fetch_row($resql);
				$projects[$row[0]] = $row[1];
				$temp[]						 = $row[0];
				$i++;
			}

			$this->db->free($resql);

			if ($list) {
				if (empty($temp))
					return '0';
				$result = implode(',', $temp);
				return $result;
			}
		}
		else {
			dol_print_error($this->db);
		}

		return $projects;
	}

	// -------------------------------------------------- copyObject()

	/**
	 * copie un objet du même type que celui en cours vers un autre objet du meme type sauf l'id
	 *
	 * @param $objSource objet
	 *        	du même type à copier
	 */
	public function copyObject($objSource, $objDest) {

		// $objDest->prop1 = $objSource->prop1;

		$objDest->rowid									 = $objSource->rowid;
		$objDest->id										 = $objSource->id;
		$objDest->fk_soc								 = $objSource->fk_soc;
		$objDest->datec									 = $this->db->jdate($objSource->datec);
		$objDest->tms										 = $this->db->jdate($objSource->tms);
		$objDest->dateo									 = $this->db->jdate($objSource->dateo);
		$objDest->datee									 = $this->db->jdate($objSource->datee);
		$objDest->ref										 = $objSource->ref;
		$objDest->entity								 = $objSource->entity;
		$objDest->title									 = $objSource->title;
		$objDest->description						 = $objSource->description;
		$objDest->fk_user_creat					 = $objSource->fk_user_creat;
		$objDest->public								 = $objSource->public;
		$objDest->opp_percent						 = $objSource->opp_percent;
		$objDest->date_close						 = $this->db->jdate($objSource->date_close);
		$objDest->fk_user_close					 = $objSource->fk_user_close;
		$objDest->note_private					 = $objSource->note_private;
		$objDest->note_public						 = $objSource->note_public;
		$objDest->opp_amount						 = $objSource->opp_amount;
		$objDest->budget_amount					 = $objSource->budget_amount;
		$objDest->model_pdf							 = $objSource->model_pdf;
		$objDest->import_key						 = $objSource->import_key;
		$objDest->fk_statut							 = $objSource->fk_statut;
		$objDest->fk_opp_status					 = $objSource->fk_opp_status;
		$objDest->opp_status_code				 = $objSource->opp_status_code;
		$objDest->opp_status_label			 = $objSource->opp_status_label;
		$objDest->position							 = $objSource->position;
		$objDest->opp_status_percent		 = $objSource->opp_status_percent;
		$objDest->lead_status_active		 = $objSource->lead_status_active;
		$objDest->societe_nom						 = $objSource->societe_nom;
		$objDest->societe_name_alias		 = $objSource->societe_name_alias;
		$objDest->societe_logo					 = $objSource->societe_logo;
		// $objDest->datec = $this->db->jdate($objSource->datec);
		// $objDest->tms = $this->db->jdate($objSource->tms);
		$objDest->contactsIdsWithSource	 = $objSource->contactsIdsWithSource;
	}

	// ------------------------------------------------ getCodeSQL()

	/**
	 * renvoie la clause FROM sans le FROM
	 */
	public function getCodeSQL($_ORDERBY = '', $_isNewOrderBy = true, $_WHERE = '', $_isNewWhere = true, $_HAVING = '', $_isNewHaving = true) {
		global $conf;

		$sql = '';

		$sql = "SELECT ";

		$sql .= " " . "t.rowid AS rowid,";
		$sql .= " " . "t.rowid AS id,";
		$sql .= " " . "t.fk_soc AS fk_soc,";
		$sql .= " " . "t.datec AS datec,";
		$sql .= " " . "t.tms AS tms,";
		$sql .= " " . "t.dateo AS dateo,";
		$sql .= " " . "t.datee AS datee,";
		$sql .= " " . "t.ref AS ref,";
		$sql .= " " . "t.entity AS entity,";
		$sql .= " " . "t.title AS title,";
		$sql .= " " . "t.description AS description,";
		$sql .= " " . "t.fk_user_creat AS fk_user_creat,";
		$sql .= " " . "t.public AS public,";
		$sql .= " " . "t.opp_percent AS opp_percent,";
		$sql .= " " . "t.date_close AS date_close,";
		$sql .= " " . "t.fk_user_close AS fk_user_close,";
		$sql .= " " . "t.note_private AS note_private,";
		$sql .= " " . "t.note_public AS note_public,";
		$sql .= " " . "t.opp_amount AS opp_amount,";
		$sql .= " " . "t.budget_amount AS budget_amount,";
		$sql .= " " . "t.model_pdf AS model_pdf,";
		$sql .= " " . "t.import_key AS import_key,";
		$sql .= " " . "t.fk_statut AS fk_statut,";
		$sql .= " " . "t.fk_opp_status AS fk_opp_status,";
		$sql .= " " . "" . MAIN_DB_PREFIX . "c_lead_status.code AS opp_status_code,";
		$sql .= " " . "" . MAIN_DB_PREFIX . "c_lead_status.label AS opp_status_label,";
		$sql .= " " . "" . MAIN_DB_PREFIX . "c_lead_status.position AS position,";
		$sql .= " " . "" . MAIN_DB_PREFIX . "c_lead_status.percent AS opp_status_percent,";
		$sql .= " " . "" . MAIN_DB_PREFIX . "c_lead_status.active AS lead_status_active,";
		$sql .= " " . "" . MAIN_DB_PREFIX . "societe.nom AS societe_nom,";
		$sql .= " " . "" . MAIN_DB_PREFIX . "societe.name_alias AS societe_name_alias,";
		$sql .= " " . "" . MAIN_DB_PREFIX . "societe.logo AS societe_logo, ";
		$sql .= " " . "contacts.contactsIdsWithSource AS contactsIdsWithSource,";
		$sql .= " GROUP_CONCAT(" . MAIN_DB_PREFIX . "categorie_project.fk_categorie) AS categories ";

		$sql .= " FROM ";
		$sql .= "" . MAIN_DB_PREFIX . "projet as t  "
				. " left join " . MAIN_DB_PREFIX . "c_lead_status on t.fk_opp_status = " . MAIN_DB_PREFIX . "c_lead_status.rowid  "
				. " left join " . MAIN_DB_PREFIX . "societe on t.fk_soc = " . MAIN_DB_PREFIX . "societe.rowid ";
		$sql .= " LEFT JOIN (SELECT " . MAIN_DB_PREFIX . "element_contact.element_id, GROUP_CONCAT(concat(" . MAIN_DB_PREFIX . "element_contact.fk_socpeople, '-', UPPER(substring(" . MAIN_DB_PREFIX . "c_type_contact.source, 1, 1))), ',') AS contactsIdsWithSource "
				. "							FROM  " . MAIN_DB_PREFIX . "element_contact "
				. "							LEFT JOIN " . MAIN_DB_PREFIX . "c_type_contact ON " . MAIN_DB_PREFIX . "element_contact.fk_c_type_contact = " . MAIN_DB_PREFIX . "c_type_contact.rowid    "
				. "							WHERE " . MAIN_DB_PREFIX . "c_type_contact.element = 'project'  "
				. "							GROUP BY " . MAIN_DB_PREFIX . "element_contact.element_id) AS contacts "
				. "				ON t.rowid = contacts.element_id";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "categorie_project ON t.rowid = " . MAIN_DB_PREFIX . "categorie_project.fk_project ";

		// --------- WHERE
		$WHERE = "";
		$WHERE = trim($WHERE);
		if (!empty($_WHERE)) {
			if ($_isNewWhere)
				$sql .= " WHERE " . $_WHERE; // on remplace le where actuel
			else {
				if ($WHERE !== "") {
					$WHERE = $this->setWhereParams($WHERE);
					$sql	 .= " WHERE " . $WHERE . " AND (" . $_WHERE . ") "; // on ajoute le nouveau where
				}
				else {
					$sql .= " WHERE " . $_WHERE;
				}
			}
		}
		elseif ($WHERE !== "") {
			$WHERE = $this->setWhereParams($WHERE);
			$sql	 .= " WHERE " . $WHERE;
		}

		// ----------- GROUP BY
		$sql .= " GROUP BY t.rowid ";

		// ----------- HAVING
		$HAVING	 = "";
		$HAVING	 = trim($HAVING);
		if (!empty($_HAVING)) {
			if ($_isNewHaving)
				$sql .= " HAVING " . $_HAVING; // on remplace le having actuel
			else {
				if ($HAVING !== "") {
					$HAVING	 = $this->setHavingParams($HAVING);
					$sql		 .= " HAVING " . $HAVING . " AND (" . $_HAVING . ") "; // on ajoute le nouveau having
				}
				else {
					$sql .= " HAVING " . $_HAVING;
				}
			}
		}
		elseif ($HAVING !== "") {
			$HAVING	 = $this->setHavingParams($HAVING);
			$sql		 .= " HAVING " . $HAVING;
		}

		// ----------- ORDER BY
		$ORDERBY = "t.datec DESC, t.fk_opp_status";
		$ORDERBY = trim($ORDERBY);
		if (!empty($_ORDERBY)) {
			if ($_isNewOrderBy)
				$sql .= " ORDER BY " . $_ORDERBY; // on remplace l'ancien $ORDERBY par le nouveau $_ORDERBY
			else
			if ($ORDERBY !== "")
				$sql .= " ORDER BY " . $ORDERBY . ", " . $_ORDERBY; // // on ajoute le nouveau $_ORDERBY à l'ancien $ORDERBY
			else
				$sql .= " ORDER BY " . $_ORDERBY;
		}
		elseif ($ORDERBY !== "") {
			$sql .= " ORDER BY " . $ORDERBY;
		}

		return $sql;
	}

	// ---------------------------------------------- setWhereParams()

	/**
	 */
	private function setWhereParams($sqlWhereClause) {
		$where = $sqlWhereClause; // la variable $where est utilisée dans le code du Generator, NE PAS MODIFIER



		return $where;
	}

	// ---------------------------------------------- setHavingParams()

	/**
	 */
	private function setHavingParams($sqlHavingClause) {
		$having = $sqlHavingClause; // la variable $having est utilisée dans le code Generator, NE PAS MODIFIER



		return $having;
	}

	// ---------------------------------------------- initAsSpecimen()

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen() {
		$this->id		 = 0;
		$this->rowid = 0;

		// $this->prop1 = '';
		// __INIT_AS_SPECIMEN__
	}

	// -------------------------------------------------- generateDocument()

	/**
	 * Create a document onto disk accordign to template module.
	 *
	 * @param string $modele
	 *        	Force le mnodele a utiliser ('' to not force)
	 * @param Translate $outputlangs
	 *        	objet lang a utiliser pour traduction
	 * @param int $hidedetails
	 *        	Hide details of lines
	 * @param int $hidedesc
	 *        	Hide description
	 * @param int $hideref
	 *        	Hide ref
	 * @return int 0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0) {
		global $conf, $langs;

		$langs->load("kanview@kanview");

		// Positionne le modele sur le nom du modele a utiliser
		if (!dol_strlen($modele)) {
			if (!empty($conf->global->KANVIEW_ADDON_PDF)) {
				$modele = $conf->global->KANVIEW_ADDON_PDF;
			}
			else {
				$modele = 'generic';
			}
		}

		$modelpath = "core/modules/kanview/doc/";

		return $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref);
	}

	//
	// ------------------------------------- toArray()
	//
	// renvoie l'objet au format Array
	public function toArray() {
		$object_array	 = array();
		$fields_array	 = array('rowid', 'id', 'fk_soc', 'datec', 'tms', 'dateo', 'datee', 'ref', 'entity', 'title', 'description', 'fk_user_creat', 'public', 'opp_percent', 'date_close', 'fk_user_close', 'note_private', 'note_public', 'opp_amount', 'budget_amount', 'model_pdf', 'import_key', 'fk_statut', 'fk_opp_status', 'opp_status_code', 'opp_status_label', 'position', 'opp_status_percent', 'societe_nom', 'societe_name_alias', 'societe_logo',);

		$count = count($fields_array);

		for ($i = 0; $i < $count; $i++) {
			if (property_exists($this, $fields_array[$i])) {
				$object_array[$fields_array[$i]] = $this->{$fields_array[$i]};
			}
		}

		return $object_array;
	}

	//
	// ------------------------------------- toLinesArray()
	//
	// renvoie les lignes de l'objet au format Array
	public function toLinesArray() {
		$lines_array	 = array();
		$fields_array	 = array('rowid', 'id', 'fk_soc', 'datec', 'tms', 'dateo', 'datee', 'ref', 'entity', 'title', 'description', 'fk_user_creat', 'public', 'opp_percent', 'date_close', 'fk_user_close', 'note_private', 'note_public', 'opp_amount', 'budget_amount', 'model_pdf', 'import_key', 'fk_statut', 'fk_opp_status', 'opp_status_code', 'opp_status_label', 'position', 'opp_status_percent', 'societe_nom', 'societe_name_alias', 'societe_logo',);

		$count = count($fields_array);

		$countlines = count($this->lines);
		for ($j = 0; $j < $countlines; $j++) {
			for ($i = 0; $i < $count; $i++) {
				if (property_exists($this->lines[$j], $fields_array[$i])) {
					$lines_array[$j][$fields_array[$i]] = $this->lines->{$fields_array[$i]};
				}
			}
		}

		return $lines_array;
	}

}

/**
 * Class ReqKbMainProjetsLine
 */
class ReqKbMainProjetsLine {

	/**
	 *
	 * @var string module auquel appartient cet objet, ne doit pas être modifié
	 */
	public $modulepart = 'kanview';

	/**
	 * nom du champ id (ex.: 'rowid')
	 */
	public $idfield = 'rowid';

	/**
	 * nom du champ Ref (ex.
	 * : 'ref', 'code')
	 */
	public $reffield = 'ref';
	// public $prop1;

	public $rowid;
	public $id;
	public $fk_soc;
	public $datec;
	public $tms;
	public $dateo;
	public $datee;
	public $ref;
	public $entity;
	public $title;
	public $description;
	public $fk_user_creat;
	public $public;
	public $opp_percent;
	public $date_close;
	public $fk_user_close;
	public $note_private;
	public $note_public;
	public $opp_amount;
	public $budget_amount;
	public $model_pdf;
	public $import_key;
	public $fk_statut;
	public $fk_opp_status;
	public $opp_status_code;
	public $opp_status_label;
	public $position;
	public $opp_status_percent;
	public $lead_status_active;
	public $societe_nom;
	public $societe_name_alias;
	public $societe_logo;

}

// *******************************************************************************************************************
//                                                  FIN
// *******************************************************************************************************************






















