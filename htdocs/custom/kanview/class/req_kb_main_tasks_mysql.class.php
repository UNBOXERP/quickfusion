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
 * Class ReqKbMainTasksMysql
 *
 * Put some comments here
 */
class ReqKbMainTasksMysql extends CommonObject {

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
	 * @var ReqKbMainTasksMysqlLine[] Lines
	 */
	public $lines = array();
	// public $prop1;

	public $rowid;
	public $id;
	public $ref;
	public $entity;
	public $fk_projet;
	public $fk_task_parent;
	public $datec;
	public $dateo;
	public $datee;
	public $task_period;
	public $datev;
	public $label;
	public $description;
	public $duration_effective;
	public $planned_workload;
	public $progress;
	public $priority;
	public $fk_statut;
	public $note_private;
	public $note_public;
	public $rang;
	public $progress_level;
	public $projet_ref;
	public $projet_title;
	public $fk_soc;
	public $contactsIdsWithSource;
	public $total_task_duration;

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
		$this->ref									 = '';
		$this->entity								 = '';
		$this->fk_projet						 = '';
		$this->fk_task_parent				 = '';
		$this->datec								 = '';
		$this->dateo								 = '';
		$this->datee								 = '';
		$this->task_period					 = '';
		$this->datev								 = '';
		$this->label								 = '';
		$this->description					 = '';
		$this->duration_effective		 = '';
		$this->planned_workload			 = '';
		$this->progress							 = '';
		$this->priority							 = '';
		$this->fk_statut						 = '';
		$this->note_private					 = '';
		$this->note_public					 = '';
		$this->rang									 = '';
		$this->progress_level				 = '';
		$this->projet_ref						 = '';
		$this->projet_title					 = '';
		$this->fk_soc								 = '';
		$this->contactsIdsWithSource = '';
		$this->total_task_duration	 = '';
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
				$line = new ReqKbMainTasksMysqlLine();

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
		$sql2											 .= " WHERE ctc.element = '" . 'project' . "'";
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
		$objDest->ref										 = $objSource->ref;
		$objDest->entity								 = $objSource->entity;
		$objDest->fk_projet							 = $objSource->fk_projet;
		$objDest->fk_task_parent				 = $objSource->fk_task_parent;
		$objDest->datec									 = $this->db->jdate($objSource->datec);
		$objDest->dateo									 = $this->db->jdate($objSource->dateo);
		$objDest->datee									 = $this->db->jdate($objSource->datee);
		$objDest->task_period						 = $this->db->jdate($objSource->task_period);
		$objDest->datev									 = $this->db->jdate($objSource->datev);
		$objDest->label									 = $objSource->label;
		$objDest->description						 = $objSource->description;
		$objDest->duration_effective		 = $objSource->duration_effective;
		$objDest->planned_workload			 = $objSource->planned_workload;
		$objDest->progress							 = $objSource->progress;
		$objDest->priority							 = $objSource->priority;
		$objDest->fk_statut							 = $objSource->fk_statut;
		$objDest->note_private					 = $objSource->note_private;
		$objDest->note_public						 = $objSource->note_public;
		$objDest->rang									 = $objSource->rang;
		$objDest->progress_level				 = $objSource->progress_level;
		$objDest->projet_ref						 = $objSource->projet_ref;
		$objDest->projet_title					 = $objSource->projet_title;
		$objDest->fk_soc								 = $objSource->fk_soc;
		$objDest->contactsIdsWithSource	 = $objSource->contactsIdsWithSource;
		$objDest->total_task_duration		 = $objSource->total_task_duration;
		// $objDest->datec = $this->db->jdate($objSource->datec);
		// $objDest->tms = $this->db->jdate($objSource->tms);
	}

	// ------------------------------------------------ getCodeSQL()

	/**
	 * renvoie la clause FROM sans le FROM
	 */
	public function getCodeSQL($_ORDERBY = '', $_isNewOrderBy = true, $_WHERE = '', $_isNewWhere = true, $_HAVING = '', $_isNewHaving = true) {
		$sql = '';

		$sql = "SELECT ";

		$sql .= " " . "DISTINCT  t.rowid AS rowid,";
		$sql .= " " . "t.rowid AS id,";
		$sql .= " " . "t.ref AS ref,";
		$sql .= " " . "t.entity AS entity,";
		$sql .= " " . "t.fk_projet AS fk_projet,";
		$sql .= " " . "t.fk_task_parent AS fk_task_parent,";
		$sql .= " " . "t.datec AS datec,";
		$sql .= " " . "t.dateo AS dateo,";
		$sql .= " " . "t.datee AS datee,";
		$sql .= " " . "concat(t.dateo, '-', t.datee) AS task_period,";
		$sql .= " " . "t.datev AS datev,";
		$sql .= " " . "t.label AS label,";
		$sql .= " " . "t.description AS description,";
		$sql .= " " . "t.duration_effective AS duration_effective,";
		$sql .= " " . "t.planned_workload AS planned_workload,";
		$sql .= " " . "t.progress AS progress,";
		$sql .= " " . "t.priority AS priority,";
		$sql .= " " . "t.fk_statut AS fk_statut,";
		$sql .= " " . "t.note_private AS note_private,";
		$sql .= " " . "t.note_public AS note_public,";
		$sql .= " " . "t.rang AS rang,";
		$sql .= " " . "(CASE   WHEN (t.progress <= 0 OR t.progress IS NULL)  THEN 'TASK_NOT_STARTED'   WHEN t.progress < 30 THEN 'TASK_LEVEL_1'   WHEN t.progress < 60 THEN 'TASK_LEVEL_2'   WHEN t.progress < 90 THEN 'TASK_LEVEL_3'   WHEN t.progress < 100 THEN 'TASK_LEVEL_4'   WHEN t.progress >= 100 THEN 'TASK_DONE'  END) AS progress_level,";
		$sql .= " " . "" . MAIN_DB_PREFIX . "projet.ref AS projet_ref,";
		$sql .= " " . "" . MAIN_DB_PREFIX . "projet.title AS projet_title,";
		$sql .= " " . "" . MAIN_DB_PREFIX . "projet.fk_soc AS fk_soc,";
		$sql .= " " . "contacts.contactsIdsWithSource AS contactsIdsWithSource,";
		$sql .= " " . "SUM(" . MAIN_DB_PREFIX . "projet_task_time.task_duration) AS total_task_duration";

		$sql .= " FROM ";
		$sql .= "" . MAIN_DB_PREFIX . "projet_task as t    "
				. " JOIN " . MAIN_DB_PREFIX . "projet on t.fk_projet = " . MAIN_DB_PREFIX . "projet.rowid  "
				. " LEFT JOIN " . MAIN_DB_PREFIX . "projet_task_time on t.rowid = " . MAIN_DB_PREFIX . "projet_task_time.fk_task  "
				. " LEFT JOIN (SELECT " . MAIN_DB_PREFIX . "element_contact.element_id, GROUP_CONCAT(concat(" . MAIN_DB_PREFIX . "element_contact.fk_socpeople, '-', upper(substring(" . MAIN_DB_PREFIX . "c_type_contact.source, 1, 1))), ',') as contactsIdsWithSource "
				. "							FROM  " . MAIN_DB_PREFIX . "element_contact "
				. "							LEFT JOIN " . MAIN_DB_PREFIX . "c_type_contact ON " . MAIN_DB_PREFIX . "element_contact.fk_c_type_contact = " . MAIN_DB_PREFIX . "c_type_contact.rowid    "
				. "							WHERE " . MAIN_DB_PREFIX . "c_type_contact.element = 'project_task'  "
				. "							GROUP BY " . MAIN_DB_PREFIX . "element_contact.element_id) AS contacts "
				. "				ON t.rowid = contacts.element_id";

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
		$GROUPBY = "t.rowid,  t.ref,  t.entity,  t.fk_projet,  t.fk_task_parent,  t.datec,   t.dateo,  t.datee,  task_period,  t.datev,  t.label,  t.description,  t.duration_effective,  t.planned_workload,  t.progress,  t.priority,  t.fk_statut,  t.note_private,  t.note_public,  t.rang,  progress_level,  projet_ref,   projet_title,   " . MAIN_DB_PREFIX . "projet.fk_soc,  contactsIdsWithSource";
		$GROUPBY = trim($GROUPBY);
		if ($GROUPBY !== "")
			$sql		 .= " GROUP BY " . $GROUPBY;

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
		$ORDERBY = "t.datec DESC, t.fk_projet DESC, t.rang";
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
		$fields_array	 = array('rowid', 'id', 'ref', 'entity', 'fk_projet', 'fk_task_parent', 'datec', 'dateo', 'datee', 'task_period', 'datev', 'label', 'description', 'duration_effective', 'planned_workload', 'progress', 'priority', 'fk_statut', 'note_private', 'note_public', 'rang', 'progress_level', 'projet_ref', 'projet_title', 'fk_soc', 'contactsIdsWithSource', 'total_task_duration',);

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
		$fields_array	 = array('rowid', 'id', 'ref', 'entity', 'fk_projet', 'fk_task_parent', 'datec', 'dateo', 'datee', 'task_period', 'datev', 'label', 'description', 'duration_effective', 'planned_workload', 'progress', 'priority', 'fk_statut', 'note_private', 'note_public', 'rang', 'progress_level', 'projet_ref', 'projet_title', 'fk_soc', 'contactsIdsWithSource', 'total_task_duration',);

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
 * Class ReqKbMainTasksMysqlLine
 */
class ReqKbMainTasksMysqlLine {

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
	public $ref;
	public $entity;
	public $fk_projet;
	public $fk_task_parent;
	public $datec;
	public $dateo;
	public $datee;
	public $task_period;
	public $datev;
	public $label;
	public $description;
	public $duration_effective;
	public $planned_workload;
	public $progress;
	public $priority;
	public $fk_statut;
	public $note_private;
	public $note_public;
	public $rang;
	public $progress_level;
	public $projet_ref;
	public $projet_title;
	public $fk_soc;
	public $contactsIdsWithSource;
	public $total_task_duration;

}

// *******************************************************************************************************************
//                                                  FIN
// *******************************************************************************************************************






















