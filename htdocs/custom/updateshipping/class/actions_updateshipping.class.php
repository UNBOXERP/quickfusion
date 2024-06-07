<?php
/* Copyright (C) 2023 SuperAdmin
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
 * \file    updateshipping/class/actions_updateshipping.class.php
 * \ingroup updateshipping
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/commande/modules_commande.php';
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
/**

/**
 * Class ActionsUpdateshipping
 */
class ActionsUpdateshipping
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
		global $db, $conf, $langs, $user;
		$TContext = explode(':', $parameters['context']);
		if (in_array('ordercard', $TContext))
		{
			$action=GETPOST('action', 'alpha');
			$attribute=GETPOST('attribute', 'alpha');

			
			if($action== "update_extras" &&  $attribute=="custy"){
				global $db;
				require_once DOL_DOCUMENT_ROOT.'/custom/multiprecios/opera1.php';
				//Aqui va 3 weeks $object->availability

				$NuevaFecha=GETPOST('options_custy', 'int');
				$idshippingtype=GETPOST('id','int');
				$fechaShippingType = getall("commande_extrafields", 'fk_object = '.$idshippingtype )[0];
				$dolFechaCommande = $object->date_commande;
				$fechaCommande = getFecha($dolFechaCommande)[2];
				$fechaCommandeLivraison = getFecha($object->date_livraison)[2];
				$fechaCommandeDelivery = getFecha($object->date_livraison)[2];
				$test =0;
				require_once DOL_DOCUMENT_ROOT . '/custom/multiprecios/funciones_dias_trabajados.php';

				// Actualizar el campo date_livraison en la tabla commande
				try {
					// Conectar a la base de datos utilizando PDO
					$pdo = new PDO('mysql:host=localhost;dbname=unboxerp_fusionbrands', 'unboxerp_dbAdmin', '6JwRyl4vWQ_J');
					$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

					$sqlD = "UPDATE " . MAIN_DB_PREFIX . "commande SET date_livraison = :fechaLivraison WHERE rowid = :idCommande";
				
					if($NuevaFecha==1){
						$weeks=2;
						$fechaCommande = date("Y-m-d", strtotime($fechaCommande . " + " . (int)$weeks . " weeks"));
						$weeksUpdate = "2W";
						$object->cond_reglement = $weeksUpdate;

					}elseif ($NuevaFecha==2){
						$weeks=2;$dias=1;
						$fechaCommande = date("Y-m-d", strtotime($fechaCommande . " + " . (int)$weeks . " weeks"));
						$weeksUpdate = "2W";
					}elseif ($NuevaFecha==3){
						$weeks=3;
						$fechaCommande = date("Y-m-d", strtotime($fechaCommande . " + " . (int)$weeks . " weeks"));
						$weeksUpdate = "3 weeks";

					}elseif ($NuevaFecha==4){
						$weeks=3;
						$fechaCommande = date("Y-m-d", strtotime($fechaCommande . " + " . (int)$weeks . " weeks"));
						$weeksUpdate = "3 weeks";

					}

					$stmt = $pdo->prepare($sqlD);
					
					$test=0;
					$stmt->bindParam(':fechaLivraison',  $fechaCommande, PDO::PARAM_STR);
					$stmt->bindParam(':idCommande', $fechaShippingType->fk_object, PDO::PARAM_INT);
					
					$stmt->execute();
					$test=0;
					setEventMessages('Shipped updated '. $object->ref,null, 'mesgs');
				} catch (PDOException $e) {
					setEventMessages('Shipped was not able to be updated '. $object->ref,null, 'errors');
				}

				if($NuevaFecha==1){
					$semandas=1;
				}elseif($NuevaFecha==2){
					$semandas=2;
				}elseif($NuevaFecha==3){
					$semandas=3;
				}elseif($NuevaFecha==4){
					$semandas=4;
				}
				global $db;
				$sqlOption = "UPDATE " . MAIN_DB_PREFIX . "commande SET fk_availability = ".$semandas." WHERE rowid =".$idshippingtype;
				$result= $db->query($sqlOption);

				$sql = "UPDATE " . MAIN_DB_PREFIX . "commande_extrafields  SET custy = ".$NuevaFecha." WHERE fk_object =".$idshippingtype;
				$result1= $db->query($sql);

				
				$ttQty = "SELECT SUM(lf.qty) as suma_cantidad
				FROM llxas_commandedet lc
				INNER JOIN llxas_facturedet lf ON lc.fk_product = lf.fk_product
				INNER JOIN llxas_commande lc2 ON lc.fk_commande = lc2.rowid
				WHERE lc2.rowid = " . $idshippingtype . "";
	
				// Realiza la consulta SQL para obtener la suma de cantidades
					$result2 = $db->query($ttQty);
	
					if ($result2) {
						$row = $db->fetch_object($result2);
						$suma_cantidad = $row->suma_cantidad;
	
						// Ahora, actualiza la tabla "commande" con el valor obtenido
						$sqlTotalQty = "UPDATE " . MAIN_DB_PREFIX . "commande SET fk_shipping_method = " . $suma_cantidad . " WHERE rowid =".$idshippingtype;
						$result3 = $db->query($sqlTotalQty);
					}


				$test=0;
				$db->commit();
				$db->begin();
				$catname = GETPOST('catname', 'alpha');
				$nosearch = GETPOST('nosearch', 'int');
				$paramname = 'id';
				$moreparam = ($nosearch ? '&nosearch=1' : '');
				header('Location: '.$_SERVER["PHP_SELF"].'?'.($paramname ? $paramname : 'id').'='.(is_object($object) ? $object->id : '').$moreparam);
				exit();

				}
			}




//
//		$error = 0; // Error counter
//
//		/* print_r($parameters); print_r($object); echo "action: " . $action; */
//		if (in_array($parameters['currentcontext'], array('ordercard', 'globalcard')) && GETPOST("action")=='confirm_validate')
//		{
//			$object->total_ttc  +=$object->array_options['options_shpping'] ;
//			$object->array_options['options_updatedshiping']=1 ;
//			$object->update($user);
//			// do something only for the context 'somecontext1' or 'somecontext2'
//			// Do what you want here...
//			// You can for example call global vars like $fieldstosearchall to overwrite them, or update database depending on $action and $_POST values.
//		}
//		if (in_array($parameters['currentcontext'], array('ordercard', 'globalcard')) && $action=='update_extras')
//		{
//			if ($object->array_options['options_updatedshiping'] !=1) {
//				if ( $object->status==1){
//					$object->total_ttc  +=$object->array_options['options_shpping'] ;
//					$object->array_options['options_updatedshiping']=1 ;
////					$object->update($user);
//				}
//			}
//			else
//			{
//				setEventMessage('Shipping is already updated, You must set to draft to Update Total TTC', 'errors');
//				return -1;
//			}
//
//		}
//		if (in_array($parameters['currentcontext'], array('ordercard', 'globalcard')) && $action=='confirm_modif')
//		{
//			if ($object->array_options['options_updatedshiping'] ==1) {
//				$object->total_ttc  -=$object->array_options['options_shpping'] ;
//				$object->array_options['options_updatedshiping']=0 ;
////				$sql="UPDATE ".MAIN_DB_PREFIX."commande SET total_ttc = ".$object->total_ttc.",multicurrency_total_ttc=".$object->total_ttc." WHERE rowid = ".$object->id;
////				$resql=$db->query($sql);
//                $db->commit();
//				$object->update($user);
//			}
//
//		}
//
//		//facturas
//		if (in_array($parameters['currentcontext'], array('invoicecard', 'globalcard')) && GETPOST("action")=='add'){
//			if(GETPOST('origin')=='commande' && GETPOST('originid')>0){
//				$commande = new Commande($db);
//				$commande->fetch(GETPOST('originid'));
//				$object->array_options['options_shpping'] = $commande->array_options['options_shpping'];
//				$object->total_ttc = $commande->total_ttc;
//				$db->commit();
//				$object->update($user);
//			}
//		}
//		if (in_array($parameters['currentcontext'], array('invoicecard', 'globalcard')) && GETPOST("action")=='confirm_valid'){
//			$object->total_ttc  +=$object->array_options['options_shpping'] ;
//			$object->multicurrency_total_ttc  +=$object->array_options['options_shpping'] ;
//			$object->array_options['options_updatedshiping']=1 ;
//			$db->commit();
//			$object->update($user);
//
//
//		}
//		if (in_array($parameters['currentcontext'], array('invoicecard', 'globalcard')) && $action=='confirm_modif')
//		{
//			if ($object->array_options['options_updatedshiping'] ==1) {
//				$object->total_ttc  -=$object->array_options['options_shpping'] ;
//				$object->array_options['options_updatedshiping']=0 ;
//				$db->commit();
//				$object->update($user);
//			}
//
//		}
//		if (in_array($parameters['currentcontext'], array('invoicecard', 'globalcard')) && $action=='update_extras')
//		{
//			if ($object->array_options['options_updatedshiping'] !=1) {
//				if ( $object->status==1){
//					$object->total_ttc  +=$object->array_options['options_shpping'] ;
//					$object->array_options['options_updatedshiping']=1 ;
//					$db->commit();
//					$object->update($user);
//
//				}
//			}
//			else
//			{
//				setEventMessage('Shipping is already updated, You must set to draft to Update Total TTC', 'errors');
//				return -1;
//			}
//
//		}
//
//
//		if (!$error) {
//			$this->results = array('myreturn' => 999);
//			$this->resprints = 'A text to show';
//			return 0; // or return 1 to replace standard code
//		} else {
//			$this->errors[] = 'Error message';
//			return -1;
//		}
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
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
			foreach ($parameters['toselect'] as $objectid) {
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
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
			$this->resprints = '<option value="0"'.($disabled ? ' disabled="disabled"' : '').'>'.$langs->trans("UpdateshippingMassAction").'</option>';
		}

		if (!$error) {
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
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
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
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

		$langs->load("updateshipping@updateshipping");

		$this->results = array();

		$head = array();
		$h = 0;

		if ($parameters['tabfamily'] == 'updateshipping') {
			$head[$h][0] = dol_buildpath('/module/index.php', 1);
			$head[$h][1] = $langs->trans("Home");
			$head[$h][2] = 'home';
			$h++;

			$this->results['title'] = $langs->trans("Updateshipping");
			$this->results['picto'] = 'updateshipping@updateshipping';
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
			if ($user->rights->updateshipping->myobject->read) {
				$this->results['result'] = 1;
				return 1;
			} else {
				$this->results['result'] = 0;
				return 1;
			}
		}

		return 0;
	}

	/**
	 * Execute action completeTabsHead
	 *
	 * @param   array           $parameters     Array of parameters
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         'add', 'update', 'view'
	 * @param   Hookmanager     $hookmanager    hookmanager
	 * @return  int                             <0 if KO,
	 *                                          =0 if OK but we want to process standard actions too,
	 *                                          >0 if OK and we want to replace standard actions.
	 */
	public function completeTabsHead(&$parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $conf, $user;

		if (!isset($parameters['object']->element)) {
			return 0;
		}
		if ($parameters['mode'] == 'remove') {
			// utilisé si on veut faire disparaitre des onglets.
			return 0;
		} elseif ($parameters['mode'] == 'add') {
			$langs->load('updateshipping@updateshipping');
			// utilisé si on veut ajouter des onglets.
			$counter = count($parameters['head']);
			$element = $parameters['object']->element;
			$id = $parameters['object']->id;
			// verifier le type d'onglet comme member_stats où ça ne doit pas apparaitre
			// if (in_array($element, ['societe', 'member', 'contrat', 'fichinter', 'project', 'propal', 'commande', 'facture', 'order_supplier', 'invoice_supplier'])) {
			if (in_array($element, ['context1', 'context2'])) {
				$datacount = 0;

				$parameters['head'][$counter][0] = dol_buildpath('/updateshipping/updateshipping_tab.php', 1) . '?id=' . $id . '&amp;module='.$element;
				$parameters['head'][$counter][1] = $langs->trans('UpdateshippingTab');
				if ($datacount > 0) {
					$parameters['head'][$counter][1] .= '<span class="badge marginleftonlyshort">' . $datacount . '</span>';
				}
				$parameters['head'][$counter][2] = 'updateshippingemails';
				$counter++;
			}
			if ($counter > 0 && (int) DOL_VERSION < 14) {
				$this->results = $parameters['head'];
				// return 1 to replace standard code
				return 1;
			} else {
				// en V14 et + $parameters['head'] est modifiable par référence
				return 0;
			}
		}
	}

	/* Add here any other hooked methods... */
}
