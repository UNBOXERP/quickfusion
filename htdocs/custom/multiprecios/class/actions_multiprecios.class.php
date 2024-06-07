<?php
/* Copyright (C) 2023 Alan Montoya UBE <info@bsrsupply.us>
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
require_once DOL_DOCUMENT_ROOT.'/core/modules/commande/modules_commande.php';
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
/**
 * \file    multiprecios/class/actions_multiprecios.class.php
 * \ingroup multiprecios
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

/**
 * Class ActionsMultiprecios
 */
class ActionsMultiprecios
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
		//$langs->load("multiprecio@multiprecio");
		$TContext = explode(':', $parameters['context']);



		if (in_array('ordercard', $TContext))
		{
			$action=GETPOST('action', 'alpha');
			$attribute=GETPOST('attribute', 'alpha');

			$test=0;
			$mult1=$conf->global->multiprecio_ORDEN1;
			$mult2=$conf->global->multiprecio_ORDEN2;
			$mult3=$conf->global->multiprecio_ORDEN3;
			$mult4=$conf->global->multiprecio_ORDEN4;
			$mult5=$conf->global->multiprecio_ORDEN5;
			$mult6=$conf->global->multiprecio_ORDEN6;
			$mult7=$conf->global->multiprecio_ORDEN7;
			$orden=$object->array_options["options_tipoorden"];
			$idproducto=GETPOST('idprod', 'alpha');
			if($idproducto){
				if($mult1 ==$orden )$priceLevel=1;
				if($mult2 ==$orden )$priceLevel=2;
				if($mult3 ==$orden )$priceLevel=3;
				if($mult4 ==$orden )$priceLevel=4;
				if($mult5 ==$orden )$priceLevel=5;
				if($mult6 ==$orden )$priceLevel=6;
				if($mult7 ==$orden )$priceLevel=7;
				$sql1=" select * from ".MAIN_DB_PREFIX."product_price where fk_product= ".$idproducto." and price_level=".$priceLevel." order by date_price desc ";
				$result1=$db->query($sql1);
				$precios= $db->fetch_object($result1);
				$_POST["price_ht"]=$precios->price;
				$test=0;
			}
			require_once DOL_DOCUMENT_ROOT.'/custom/multiprecios/opera1.php';

			if($object->statut != 0 &&  $action== "update_extras" &&  $attribute=="tipoorden"){
				setEventMessages('To be able to modify Price levels, please Modify the order to Draft status', null, 'errors');
			}elseif( $action== "update_extras" &&  $attribute=="tipoorden"){
				require_once DOL_DOCUMENT_ROOT.'/custom/multiprecios/opera1.php';
				$NuevoPrecio=GETPOST('options_tipoorden', 'int');
				$idcommande=GETPOST('id','int');
				$lineas = getall("commandedet", 'fk_commande = '.$idcommande ); //lineas de la comande   commandedet
				$numLineas = count($lineas);

				//product_price lpp  where fk_product = 408 and price_level =4
				if($numLineas >0){
					for($za= 0; $za<$numLineas;$za++){
						$linea = $lineas[$za];
						$test = 0;
						$producto = getall('product','rowid = '.$linea->fk_product)[0];
						$productPrice = getall("product_price",'fk_product ='.$linea->fk_product.' AND price_level = '.$NuevoPrecio)[0];
						$test=0;
						$precioAsignar = $productPrice->price;
						//$result = $object->updateline($linea->rowid, $linea->description, $precioAsignar, $linea->qty, $linea->remise_percent, $linea->total_tva, $linea->total_localtax1, $linea->total_localtax2, 'HT', $linea->info_bits, $linea->date_start, $linea->date_end, $linea->product_type, $linea->fk_parent_line, 0, '','',  $linea->label, $linea->special_code, $linea->fk_unit, '');
						$result = $object->updateline($linea->rowid, $linea->description, $precioAsignar, $linea->qty, $linea->remise_percent,$productPrice->tva_tx,'','','','HT');
						if($result > 0){
							setEventMessages('Price Product updated '. $producto->ref,null, 'mesgs');
						}else{
							setEventMessages('Price product was not able to be updated '. $producto->ref,null, 'errors');
						}
						$test=0;
						$db->commit();
						$db->begin();

					}
				}

				//$precioproductos =getall-> producto  ->sellingprice;

				/*foreach
				cartd de card de sales order

				obtines id

				traer required commande
				$lieactual_0


				selling_!   =45
				selling_2   =567

				line->subprice  //liea actualziar
				$result = $object->updateline($line->id, $line->desc, $line->subprice, $line->qty, $line->remise_percent, $vat_rate, $localtax1_rate, $localtax2_rate, 'HT', $line->info_bits, $line->date_start, $line->date_end, $line->product_type, $line->fk_parent_line, 0, $line->fk_fournprice, $line->pa_ht, $line->label, $line->special_code, $line->array_options, $line->fk_unit, $line->multicurrency_subprice);

				*
				 *
				 */
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
			$this->resprints = '<option value="0"'.($disabled ? ' disabled="disabled"' : '').'>'.$langs->trans("MultipreciosMassAction").'</option>';
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

		$langs->load("multiprecios@multiprecios");

		$this->results = array();

		$head = array();
		$h = 0;

		if ($parameters['tabfamily'] == 'multiprecios') {
			$head[$h][0] = dol_buildpath('/module/index.php', 1);
			$head[$h][1] = $langs->trans("Home");
			$head[$h][2] = 'home';
			$h++;

			$this->results['title'] = $langs->trans("Multiprecios");
			$this->results['picto'] = 'multiprecios@multiprecios';
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
			if ($user->rights->multiprecios->myobject->read) {
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
			$langs->load('multiprecios@multiprecios');
			// utilisé si on veut ajouter des onglets.
			$counter = count($parameters['head']);
			$element = $parameters['object']->element;
			$id = $parameters['object']->id;
			// verifier le type d'onglet comme member_stats où ça ne doit pas apparaitre
			// if (in_array($element, ['societe', 'member', 'contrat', 'fichinter', 'project', 'propal', 'commande', 'facture', 'order_supplier', 'invoice_supplier'])) {
			if (in_array($element, ['context1', 'context2'])) {
				$datacount = 0;

				$parameters['head'][$counter][0] = dol_buildpath('/multiprecios/multiprecios_tab.php', 1) . '?id=' . $id . '&amp;module='.$element;
				$parameters['head'][$counter][1] = $langs->trans('MultipreciosTab');
				if ($datacount > 0) {
					$parameters['head'][$counter][1] .= '<span class="badge marginleftonlyshort">' . $datacount . '</span>';
				}
				$parameters['head'][$counter][2] = 'multipreciosemails';
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
