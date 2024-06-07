<?php
/* Copyright (C) 2023 TX2 unboxcrm <urc@unboxerp.com>
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
 * \file    emailcommande/class/actions_emailcommande.class.php
 * \ingroup emailcommande
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

/**
 * Class ActionsEmailcommande
 */
class ActionsEmailcommande
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var array Errors
	 */
	public $errors = array();


	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;


	/**
	 * Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}


	/**
	 * Execute action
	 *
	 * @param	array			$parameters		Array of parameters
	 * @param	CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	string			$action      	'add', 'update', 'view'
	 * @return	int         					<0 if KO,
	 *                           				=0 if OK but we want to process standard actions too,
	 *                            				>0 if OK and we want to replace standard actions.
	 */
	public function getNomUrl($parameters, &$object, &$action)
	{
		global $db, $langs, $conf, $user;
		$this->resprints = '';
		return 0;
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		global $conf, $user, $langs;
		$TContext = explode(':', $parameters['context']);
		$nowarray = dol_getdate(dol_now(), true);
		$day = $nowarray['mday'];
		$month = $nowarray['mon'];
		$year = $nowarray['year'];
		$hours = $nowarray['hours'];
		$minutes = $nowarray['minutes'];
		$seconds = $nowarray['seconds'];
		$action = GETPOST('action', 'aZ09');
		$id = GETPOST('id', 'aZ09');
		$date_day  = $year.'-'.$month.'-'.$day. ' '.$hours.':'.$minutes.':'.$seconds.'' ;

		if (in_array('ordercard', $TContext)) {
			if($action== 'enviaremailcustom')
			{

				function getall($table, $filter= '1=1'  ) {

					global $db, $dolibarr_main_url_root;
					//$A=getProductNull($object);


					$sql  = " SELECT * FROM ".MAIN_DB_PREFIX."".$table;
					if($filter != '1=1' ) $sql .= " where ".$filter;

					$resqle = $db->query($sql);

					if ($resqle) {
						$num = $db->num_rows($resqle);
						$i = 0;
						while ($i < $num) {
							$obj = $db->fetch_object($resqle);
							$template[] = $obj;
							$i++;
						}
					}


					return $template;

				}
				function getFecha($date)
				{
					// floatval(str_replace(",", "", $dataCommandeFor[$datacomm]));
					$nowarray = dol_getdate($date, true);
					$day = $nowarray['mday'];
					$month = $nowarray['mon'];
					$year = $nowarray['year'];
					$hours = $nowarray['hours'];
					$minutes = $nowarray['minutes'];
					$seconds = $nowarray['seconds'];
					$date_day1 = $day . '-' . $month . '-' . $year;
					$date_day = $year . '' . $month . '' . $day;
					$date_ref = $year . '' . $month;
					$date_insert = $year . '-' . $month . '-' . $day;
					$date_insert1 =  $month . '/' . $day. '/'.$year ;
					$dates = array($date_day, $date_ref, $date_insert,$date_day1,$date_insert1);

					return $dates;


				}
				$sender_email=$conf->global->EMAILCOMMANDE_sender_email;
				$destination_email=$conf->global->EMAILCOMMANDE_destination_email;
				$subject=$conf->global->EMAILCOMMANDE_subject;
				$body=$conf->global->EMAILCOMMANDE_body;



//$sender_email=$email='mstoluca@gmail.com';
//$destination_email=$sender_email='ame@unboxerp.com';
				//$nombre = 'Alan Montoya';
				$cabeceras = "MIME-Version: 1.0 \r\n";
				$cabeceras .= "Content-type: text/html; charset=utf-8 \r\n";
				//$cabeceras.= "From: $nombre <$sender_email>\r\n";
				//$destino="mstoluca@gmail.com";
//$subject=$asunto="REPORTE SASS";
				$fecha=date("d/m/y");
				$hora=date("H:i:s");
				require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
				global $db;
				$object = new Commande($db);
				$extrafields = new ExtraFields($db);
				//$extrafields->fetch_name_optionals_label($object->table_element);
				 $commande=$object->fetch($id);

				//$commande=getall('commande', 'rowid='.$id)[0];
				$ref=$object->ref;
				$societe=getall('societe', 'rowid='.$object->socid)[0];
				$fechacommande=getFecha($object->date_commande)[4];
				$fechalivraison=getFecha($object->date_livraison)[4];
				$bodymodificado=str_replace("__REF__", $ref, $body);
				$subjectmodificado=str_replace("__REF__", $ref, $subject);
				//__THIRDPARTY_NAME__
				$bodymodificado=str_replace("__THIRDPARTY_NAME__", $societe->nom, $bodymodificado);
				//__REF_CLIENT__
				$bodymodificado=str_replace("__REF_CLIENT__", $object->ref_client, $bodymodificado);
				//__DATE_COMMANDE__
				$bodymodificado=str_replace("__DATE_COMMANDE__", $fechacommande, $bodymodificado);
				//__DATE_LIVRAISON__
				$bodymodificado=str_replace("__DATE_LIVRAISON__", $fechalivraison, $bodymodificado);
				$societe=getall('societe', 'rowid='.$object->socid)[0];

				$test=0;

				//__THIRDPARTY_NAME__
				$subjectmodificado=str_replace("__THIRDPARTY_NAME__", $societe->nom, $subjectmodificado);
				//__REF_CLIENT__
				$subjectmodificado=str_replace("__REF_CLIENT__", $object->ref_client, $subjectmodificado);
				//__DATE_COMMANDE__
				$subjectmodificado=str_replace("__DATE_COMMANDE__", $fechacommande, $subjectmodificado);
				//__DATE_LIVRAISON__
				$subjectmodificado=str_replace("__DATE_LIVRAISON__", $fechalivraison, $subjectmodificado);

				require_once (DOL_DOCUMENT_ROOT."/core/class/CMailFile.class.php");

				$mail = new CMailFile(
					$subjectmodificado,
					$destination_email,
					$sender_email,
					$bodymodificado,
					array(),
					array(),
					array(),
					'',
					'',
					0,
					1,
					'',
					'',
					'',
					'',
					'emailing',
					''
				);

				$mail->errors_to = $conf->global->MAIN_MAIL_ERRORS_TO;

				// Send or not email
				$result=$mail->sendfile();
				if (! $result)
				{
					print "Error sending email ".$mail->error."\n";
					dol_syslog("Error sending email ".$mail->error."\n");
					setEventMessages($langs->trans('NOTIFY_EMAIL').' '.$mail->error, null, 'errors');
				}else{
					$MENSAJE='SUBJECT:'.$subjectmodificado.' -Recipient: '.$destination_email.' -SENDER:'.$sender_email.' -MESSAGE: '. $bodymodificado;
					setEventMessages('SEND EMAIL '.$sender_email.' >> '.$destination_email, '',  'mesgs');

					require_once DOL_DOCUMENT_ROOT.'/comm/action/class/cactioncomm.class.php';
					require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncommreminder.class.php';
					require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
					$now = dol_now();
					// Insert record of emails sent


					$actioncomm = new ActionComm($this->db);

					$actioncomm->type_code = 'AC_OTH_AUTO'; // Type of event ('AC_OTH', 'AC_OTH_AUTO', 'AC_XXX'...)
					$actioncomm->code = 'AC_EMAIL';
					$actioncomm->label = $langs->trans('NOTIFY_EMAIL');
					$actioncomm->note_private =$MENSAJE ;
					$actioncomm->fk_project = 0;
					$actioncomm->datep = $now;
					$actioncomm->datef = $now;
					$actioncomm->percentage = -1; // Not applicable
					$actioncomm->socid = $object->socid;
					$actioncomm->contact_id = 0;
					$actioncomm->authorid = $user->id; // User saving action
					$actioncomm->userownerid = $user->id; // Owner of action
					// Fields when action is en email (content should be added into note)
					$actioncomm->email_msgid = $mail->msgid;
					$actioncomm->email_from = $sender_email;
					$actioncomm->email_sender = '';
					$actioncomm->email_to = $destination_email;
					$actioncomm->email_tocc = '';
					$actioncomm->email_tobcc = '';
					$actioncomm->email_subject = $subjectmodificado;
					$actioncomm->errors_to = '';

					$actioncomm->fk_element = $id;
					$actioncomm->elementtype = $object->table_element;

					$actioncomm->extraparams = '';

					$actioncomm->create($user);

				//	setEventMessages($langs->trans('NOTIFY_EMAIL').' '.$MENSAJE,  '',  'mesgs');

				}

				header('Location: '.$_SERVER["PHP_SELF"].'?'.($paramname ? $paramname : 'id').'='.(is_object($object) ? $object->id : '').$moreparam);
				exit;
			}

		}
	}


	/**
	 * Overloading the doMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{
			foreach ($parameters['toselect'] as $objectid)
			{
				// Do action on each object id
			}
		}

		if (!$error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the addMoreMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter
		$disabled = 1;

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{
			$this->resprints = '<option value="0"'.($disabled ? ' disabled="disabled"' : '').'>'.$langs->trans("EmailcommandeMassAction").'</option>';
		}

		if (!$error) {
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}public function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;
		$TContext = explode(':', $parameters['context']);
		$nowarray = dol_getdate(dol_now(), true);
		$day = $nowarray['mday'];
		$month = $nowarray['mon'];
		$year = $nowarray['year'];
		$hours = $nowarray['hours'];
		$minutes = $nowarray['minutes'];
		$seconds = $nowarray['seconds'];
		$date_day  = $year.'-'.$month.'-'.$day. ' '.$hours.':'.$minutes.':'.$seconds.'' ;
		if (in_array('ordercard', $TContext)) {
			print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=enviaremailcustom">' . $langs->trans('notifycationmailsend') . '</a>';
		}
	}



	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$object		   	Object output on PDF
	 * @param   string	$action     	'add', 'update', 'view'
	 * @return  int 		        	<0 if KO,
	 *                          		=0 if OK but we want to process standard actions too,
	 *  	                            >0 if OK and we want to replace standard actions.
	 */
	public function beforePDFCreation($parameters, &$object, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0; $deltemp = array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{
		}

		return $ret;
	}

	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$pdfhandler     PDF builder handler
	 * @param   string	$action         'add', 'update', 'view'
	 * @return  int 		            <0 if KO,
	 *                                  =0 if OK but we want to process standard actions too,
	 *                                  >0 if OK and we want to replace standard actions.
	 */
	public function afterPDFCreation($parameters, &$pdfhandler, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0; $deltemp = array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {
			// do something only for the context 'somecontext1' or 'somecontext2'
		}

		return $ret;
	}



	/**
	 * Overloading the loadDataForCustomReports function : returns data to complete the customreport tool
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function loadDataForCustomReports($parameters, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$langs->load("emailcommande@emailcommande");

		$this->results = array();

		$head = array();
		$h = 0;

		if ($parameters['tabfamily'] == 'emailcommande') {
			$head[$h][0] = dol_buildpath('/module/index.php', 1);
			$head[$h][1] = $langs->trans("Home");
			$head[$h][2] = 'home';
			$h++;

			$this->results['title'] = $langs->trans("Emailcommande");
			$this->results['picto'] = 'emailcommande@emailcommande';
		}

		$head[$h][0] = 'customreports.php?objecttype='.$parameters['objecttype'].(empty($parameters['tabfamily']) ? '' : '&tabfamily='.$parameters['tabfamily']);
		$head[$h][1] = $langs->trans("CustomReports");
		$head[$h][2] = 'customreports';

		$this->results['head'] = $head;

		return 1;
	}



	/**
	 * Overloading the restrictedArea function : check permission on an object
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int 		      			  	<0 if KO,
	 *                          				=0 if OK but we want to process standard actions too,
	 *  	                            		>0 if OK and we want to replace standard actions.
	 */
	public function restrictedArea($parameters, &$action, $hookmanager)
	{
		global $user;

		if ($parameters['features'] == 'myobject') {
			if ($user->rights->emailcommande->myobject->read) {
				$this->results['result'] = 1;
				return 1;
			} else {
				$this->results['result'] = 0;
				return 1;
			}
		}

		return 0;
	}

	/* Add here any other hooked methods... */
}
