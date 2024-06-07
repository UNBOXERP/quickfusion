<?php
/* Copyright (C) 2022 SuperAdmin <testing@unboxcrm.com>
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
 * \file    unboxutil/class/actions_unboxutil.class.php
 * \ingroup unboxutil
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

/**
 * Class ActionsUnboxutil
 */
class ActionsUnboxutil
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
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}


	/**
	 * Execute action
	 *
	 * @param array $parameters Array of parameters
	 * @param CommonObject $object The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param string $action 'add', 'update', 'view'
	 * @return    int                            <0 if KO,
	 *                                        =0 if OK but we want to process standard actions too,
	 *                                            >0 if OK and we want to replace standard actions.
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
	 * @param array $parameters Hook metadatas (context, etc...)
	 * @param CommonObject $object The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param string $action Current action (if set). Generally create or edit or null
	 * @param HookManager $hookmanager Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs, $form, $db;


		$error = 0; // Error counter
		$form = new Form($db);
		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if ($action == 'confirm_clone') {
			$socid = $object->socid;
//			$object->array_options["options_variantreason"]=
			if (in_array($parameters['currentcontext'], array('ordersuppliercard')))        // do something only for the context 'somecontext1' or 'somecontext2'
			{
				if (1 == 0 && !GETPOST('clone_content') && !GETPOST('clone_receivers')) {
					setEventMessages($langs->trans("NoCloneOptionsSpecified"), null, 'errors');
				} else {
					if ($object->id > 0) {
						$orig = clone $object;
						$object->array_options['options_variantreason'] = $_GET["variantr"];

						$ref_supplier = $object->ref_supplier;
						$result = $object->createFromClone($user, $socid);
						if ($result > 0) {
							$object->ref_supplier = $ref_supplier;
							$object->array_options['options_variantreason'] = $_GET["variantr"];
							//dolibarr sql
							$sql = "UPDATE " . MAIN_DB_PREFIX . "commande_fournisseur SET ref_supplier = '" . $ref_supplier . "' WHERE rowid = '" . $object->id . "'";
							$db->query($sql);
							$db->commit();

							$object->updateExtraField('variantreason');
							header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $result);
							exit;
						} else {
							setEventMessages($object->error, $object->errors, 'errors');
							$object = $orig;
							$action = '';
						}
					}
				}

			}

		}

		if (!$error) {

			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	public function formobjectoptions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs, $form;
		$this->resprints = '';
		return 0;


	}

	public function formconfirm($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $db, $langs, $form;


		if ($_GET["action"] == 'clone' && $parameters["currentcontext"] == 'ordersuppliercard') {

			require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
			$extrafields = new ExtraFields($db);
			$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);
			$object->fetch_optionals($object->id, $extralabels);
			//$resu= $object->showOptionals($extrafields,'edit');
			$extrareasons = $extrafields->showInputField('variantreason', $object->array_options['options_variantreason'], '', '', '', '', $object->id);
			$formquestion = array(
				array('type' => 'other', 'name' => 'variantreason', 'label' => $langs->trans("Variant Expense Reason"), 'value' => $extrareasons),
				array('type' => 'hidden', 'name' => 'variantr', 'value' => GETPOST('variantreason')),
			);
			// Paiement incomplet. On demande si motif = escompte ou autre
			$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id . '&variantreason' . $object->array_options['options_variantreason'], $langs->trans('ToClone'), $langs->trans('ConfirmCloneOrder', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);

			$this->resprints = $formconfirm;
			return 1;
		}
	}


	/**
	 * Overloading the doMassActions function : replacing the parent's function with the one below
	 *
	 * @param array $parameters Hook metadatas (context, etc...)
	 * @param CommonObject $object The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param string $action Current action (if set). Generally create or edit or null
	 * @param HookManager $hookmanager Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs, $db;

		$error = 0; // Error counter
		$form = new Form($db);
		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('invoicelist', 'somecontext2')))        // do something only for the context 'somecontext1' or 'somecontext2'
		{
			//if (GETPOST('massaction') == 'confirm_listpaid') {
			if ($action == 'confirm_listpaid') {
				if ($conf->global->UBUTIL_FACTUREPAIDLIST) {

					include_once DOL_DOCUMENT_ROOT . '/custom/unboxutil/class/factura.class.php';
					$factura = new factura($db);
					$idpago = GETPOST('idpago');
					$listado = explode(",", GETPOST("listadofacturas"));
					foreach ($listado as $objectid) {
						if (is_numeric($objectid)) {
							$factura->fetch($objectid);
							if ($factura->paye == 0) {
								//TODO Razmi pasar el valor de paiement
								$factura->CreatePayment($factura, $idpago);
							} else {
								setEventMessage('The Invoice ' . $factura->ref . ' is Already Payed ', 'errors');
							}
						}

					}
				}
			}

		}

		if (!$error) {
//			$this->results = array('myreturn' => 999);
//			$this->resprints = 'A text to show';
			return 1; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	public function doPreMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $db, $conf;
		include_once DOL_DOCUMENT_ROOT . '/custom/unboxutil/class/formu.class.php';
		$langs->load("unboxutil@unboxutil");
		if ($conf->global->UBUTIL_FACTUREPAIDLIST) {

			$form = new formu($db);
			$tipospago = $form->select_types_paiements('', 'idlistpaid', '', 0, 0, 1, 0, 1, 'minwidth200imp');
			$formquestion = array(
				array('type' => 'hidden', 'name' => 'listadofacturas', 'value' => implode(",", $parameters["toselect"])),
				array('type' => 'hidden', 'name' => 'idpago', 'value' => GETPOST('idlistpaid')),
				array('type' => 'other', 'name' => 'idlistpaid', 'label' => $langs->trans("PaymentMode"), 'value' => $tipospago)
			);
			$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"], $langs->trans('Confirm'), $langs->trans('ConfirmPaidFactures'), 'confirm_listpaid', $formquestion, '', 1, 250, 400, 0);
			if (GETPOST('massaction') == 'listpaid') {
				$this->resprints = $formconfirm;
			}
		}
	}


	/**
	 * Overloading the addMoreMassActions function : replacing the parent's function with the one below
	 *
	 * @param array $parameters Hook metadatas (context, etc...)
	 * @param CommonObject $object The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param string $action Current action (if set). Generally create or edit or null
	 * @param HookManager $hookmanager Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter
		$disabled = 0;
		if ($conf->global->UBUTIL_FACTUREPAIDLIST) {

			/* print_r($parameters); print_r($object); echo "action: " . $action; */
			if (in_array($parameters['currentcontext'], array('invoicelist', 'somecontext2')))        // do something only for the context 'somecontext1' or 'somecontext2'
			{
				$this->resprints .= '<option value="listpaid"' . ($disabled ? ' disabled="disabled"' : '') . '>' . $langs->trans("Change to Paid") . '</option>';
			}
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
	 * @param array $parameters Array of parameters
	 * @param Object $object Object output on PDF
	 * @param string $action 'add', 'update', 'view'
	 * @return  int                    <0 if KO,
	 *                                =0 if OK but we want to process standard actions too,
	 *                                >0 if OK and we want to replace standard actions.
	 */
	public function beforePDFCreation($parameters, &$object, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0;
		$deltemp = array();
		dol_syslog(get_class($this) . '::executeHooks action=' . $action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2')))        // do something only for the context 'somecontext1' or 'somecontext2'
		{
		}

		return $ret;
	}

	/**
	 * Execute action
	 *
	 * @param array $parameters Array of parameters
	 * @param Object $pdfhandler PDF builder handler
	 * @param string $action 'add', 'update', 'view'
	 * @return  int                    <0 if KO,
	 *                                  =0 if OK but we want to process standard actions too,
	 *                                  >0 if OK and we want to replace standard actions.
	 */
	public function afterPDFCreation($parameters, &$pdfhandler, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0;
		$deltemp = array();
		dol_syslog(get_class($this) . '::executeHooks action=' . $action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {
			// do something only for the context 'somecontext1' or 'somecontext2'
		}

		return $ret;
	}


	/**
	 * Overloading the loadDataForCustomReports function : returns data to complete the customreport tool
	 *
	 * @param array $parameters Hook metadatas (context, etc...)
	 * @param string $action Current action (if set). Generally create or edit or null
	 * @param HookManager $hookmanager Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function loadDataForCustomReports($parameters, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$langs->load("unboxutil@unboxutil");

		$this->results = array();

		$head = array();
		$h = 0;

		if ($parameters['tabfamily'] == 'unboxutil') {
			$head[$h][0] = dol_buildpath('/module/index.php', 1);
			$head[$h][1] = $langs->trans("Home");
			$head[$h][2] = 'home';
			$h++;

			$this->results['title'] = $langs->trans("Unboxutil");
			$this->results['picto'] = 'unboxutil@unboxutil';
		}

		$head[$h][0] = 'customreports.php?objecttype=' . $parameters['objecttype'] . (empty($parameters['tabfamily']) ? '' : '&tabfamily=' . $parameters['tabfamily']);
		$head[$h][1] = $langs->trans("CustomReports");
		$head[$h][2] = 'customreports';

		$this->results['head'] = $head;

		return 1;
	}


	/**
	 * Overloading the restrictedArea function : check permission on an object
	 *
	 * @param array $parameters Hook metadatas (context, etc...)
	 * @param string $action Current action (if set). Generally create or edit or null
	 * @param HookManager $hookmanager Hook manager propagated to allow calling another hook
	 * @return  int                            <0 if KO,
	 *                                        =0 if OK but we want to process standard actions too,
	 *                                        >0 if OK and we want to replace standard actions.
	 */
	public function restrictedArea($parameters, &$action, $hookmanager)
	{
		global $user;

		if ($parameters['features'] == 'myobject') {
			if ($user->rights->unboxutil->myobject->read) {
				$this->results['result'] = 1;
				return 1;
			} else {
				$this->results['result'] = 0;
				return 1;
			}
		}

		return 0;
	}

	function printCommonFooter($parameters, &$object, &$action, $hookmanager)
	{
		global $conf;
		if ($conf->global->UBUTIL_NOCUSTOMPRODUCT) {
			echo '<script>$(document).ready(function() {
	                   $(".prod_entry_mode_free").hide();
	                   $("#dp_desc").hide();
                   });</script>';
			return 0;
		}
	}
	/* Add here any other hooked methods... */

}
