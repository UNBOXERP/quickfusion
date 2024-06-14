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
 * \file    quickbooks/class/actions_quickbooks.class.php
 * \ingroup quickbooks
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

/**
 * Class ActionsQuickbooks
 */
class ActionsQuickbooks
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
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {        // do something only for the context 'somecontext1' or 'somecontext2'
			// Do what you want here...
			// You can for example call global vars like $fieldstosearchall to overwrite them, or update database depending on $action and $_POST values.
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
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {        // do something only for the context 'somecontext1' or 'somecontext2'
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
		$disabled = 1;

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {        // do something only for the context 'somecontext1' or 'somecontext2'
			$this->resprints = '<option value="0"' . ($disabled ? ' disabled="disabled"' : '') . '>' . $langs->trans("QuickbooksMassAction") . '</option>';
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
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {        // do something only for the context 'somecontext1' or 'somecontext2'
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

		$langs->load("quickbooks@quickbooks");

		$this->results = array();

		$head = array();
		$h = 0;

		if ($parameters['tabfamily'] == 'quickbooks') {
			$head[$h][0] = dol_buildpath('/module/index.php', 1);
			$head[$h][1] = $langs->trans("Home");
			$head[$h][2] = 'home';
			$h++;

			$this->results['title'] = $langs->trans("Quickbooks");
			$this->results['picto'] = 'quickbooks@quickbooks';
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
			if ($user->rights->quickbooks->myobject->read) {
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
	 * @param array $parameters Array of parameters
	 * @param CommonObject $object The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param string $action 'add', 'update', 'view'
	 * @param Hookmanager $hookmanager hookmanager
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
			$langs->load('quickbooks@quickbooks');
			// utilisé si on veut ajouter des onglets.
			$counter = count($parameters['head']);
			$element = $parameters['object']->element;
			$id = $parameters['object']->id;
			// verifier le type d'onglet comme member_stats où ça ne doit pas apparaitre
			// if (in_array($element, ['societe', 'member', 'contrat', 'fichinter', 'project', 'propal', 'commande', 'facture', 'order_supplier', 'invoice_supplier'])) {
			if (in_array($element, ['context1', 'context2'])) {
				$datacount = 0;

				$parameters['head'][$counter][0] = dol_buildpath('/quickbooks/quickbooks_tab.php', 1) . '?id=' . $id . '&amp;module=' . $element;
				$parameters['head'][$counter][1] = $langs->trans('QuickbooksTab');
				if ($datacount > 0) {
					$parameters['head'][$counter][1] .= '<span class="badge marginleftonlyshort">' . $datacount . '</span>';
				}
				$parameters['head'][$counter][2] = 'quickbooksemails';
				$counter++;
			}
			if ($counter > 0 && (int)DOL_VERSION < 14) {
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

	public function printCommonFooter($parameters, &$object, &$action, $hookmanager)
	{
		global $db, $langs, $conf, $user;
		require_once DOL_DOCUMENT_ROOT . '/custom/quickbooks/class/sync.php';
		$sync = new Syncqbooks();
		$sync->GetAuthURl();

		$optioncss = GETPOST('optioncss', 'alpha');
		$is_list = strpos($parameters['context'], 'list') !== false && $optioncss != 'print';

		if ($is_list) {
			//crear un array de valores segun $parameters['context'] para poder usar el valor en el switch tanto de el nombre como del mensaje a mostrar pasandole a syncronizeToQuickbooks($mensaje)
			$contexto = array(
				'invoicelist' => array('name' => 'Invoice', 'message' => 'Sync to Quickbooks','error'=>'No invoices selected','pregunta'=>'Do you want to syncronize  invoices to Quickbooks?'),
                'thirdpartylist' => array('name' => 'Thirdparty', 'message' => 'Sync thirdparty','error'=>'No thirdparty selected','pregunta'=>'Do you want to syncronize  thirdparty to Quickbooks?'),
                'productservicelist' => array('name' => 'Product', 'message' => 'Sync  products to quickbooks?','error'=>'No products selected','pregunta'=>'Do you want to syncronize  products to quickbooks?'));

			if (strpos($parameters['context'], 'thirdpartylist') !== false || strpos($parameters['context'], 'invoicelist') !== false || strpos($parameters['context'], 'productservicelist') !== false) {
				//create button syncronize to quickbooks
				//$synctitle = $langs->trans("Syncronize to Quickbooks");
				//$synctitle segun el contexto corresponda con strpos($parameters['context'], 'invoicelist') o strpos($parameters['context'], 'productservicelist')
				$mensaje = "";
				if(strpos($parameters['context'], 'invoicelist') !== false) {
					$mensaje = $contexto['invoicelist']['message'];
					$pregunta = $contexto['invoicelist']['pregunta'];
				} elseif(strpos($parameters['context'], 'productservicelist') !== false) {
					$mensaje = $contexto['productservicelist']['message'];
					$pregunta = $contexto['productservicelist']['pregunta'];
				} elseif(strpos($parameters['context'], 'thirdpartylist') !== false) {
					$mensaje = $contexto['thirdpartylist']['message'];
					$pregunta = $contexto['thirdpartylist']['pregunta'];
				}

				$error = "";
				if(strpos($parameters['context'], 'invoicelist') !== false) {
					$error = $contexto['invoicelist']['error'];
					$pregunta= $contexto['invoicelist']['pregunta'];
				} elseif(strpos($parameters['context'], 'productservicelist') !== false) {
					$error = $contexto['productservicelist']['error'];
					$pregunta=$contexto['productservicelist']['pregunta'];
				} elseif(strpos($parameters['context'], 'thirdpartylist') !== false) {
					$error = $contexto['thirdpartylist']['error'];
					$pregunta=$contexto['thirdpartylist']['pregunta'];
				}
				$synctitle=$langs->trans($mensaje);

				$test=0;
				$boton = '<button class="butAction" type="button" id="button_sync" onclick="syncronizeToQuickbooks()">' . $synctitle . '</button>';
				?>
				<script type="text/javascript" language="javascript">

					$(document).ready(function () {
						var $form = $('div.fiche form').first();
						var urlqb = '<?php echo $_SESSION['authUrl']; ?>';
						<?php if (strpos($parameters['context'], 'projecttasklist') !== false) { ?>
						$('#id-right > form#searchFormList div.titre').first().append('<?php echo $boton; ?>');
						<?php } else { ?>
						$('div.fiche div.titre').first().append('<?php echo $boton; ?>'); // Il peut y avoir plusieurs titre dans la page
						<?php } ?>

					});


					function syncronizeToQuickbooks() {
						<?php   //verificar la version de dolibarr es mayor que la 15
									if (version_compare(DOL_VERSION, '15.0.0', '>='))
									{
									}
						 ?>
						var $form = $('form[name=searchFormList]');
						if ($form.length==0){
							$form = $('form[name=formulaire]');
						}
                        <?php
                        if($GLOBALS["object"]->element == 'societe'){
                          ?>
                            $form = $('form[id=searchFormList]');

                        <?php
                        } ?>

						//find all checked checkboxes checkforselect and get their values
						var $checked = $form.find('input.checkforselect:checked');

						var $ids = $checked.map(function () {
							return $(this).attr('value');
						}).get();
						//comprobar si hay ids seleccionados
						if ($ids.length == 0) {
							$.jnotify('<?php echo $langs->trans($error) ?> ', 'error', true);
							return false;
						}
					<?php
						if($GLOBALS["object"]->element == 'facture'){
						?>	var url = "<?php echo dol_buildpath('/quickbooks/script/syncronize.php?action=syncronize&token='.newToken(), 1) ?>";
						<?php
						}elseif($GLOBALS["object"]->element == 'product'){
						?>
							var url = "<?php echo dol_buildpath('/quickbooks/script/syncronize.php?action=syncronizeproduct&token='.newToken(), 1) ?>";

						<?php
						}elseif($GLOBALS["object"]->element == 'societe'){
						?>
							var url = "<?php echo dol_buildpath('/quickbooks/script/syncronize.php?action=syncronizesociete&token='.newToken(), 1) ?>";

						<?php
						}
						$test=0;

						?>


						//ver si aqui podemos checar el element
						function syncquickbook() {
							$('<div title="Syncronize to Quickbooks"><p><?php echo $pregunta;  ?></p></div>').dialog({
								modal: true,
								buttons: {
									"Continue": function () {
										$(this).dialog("close");
                                        showLoadingSpinner();
										$.ajax({
											url: url,
											type: 'POST',
											data: {ids: $ids},
											dataType: 'json',
											success: function (data) {
												if (data.error) {
													alert(data.error);
												} else {
													alert(data.message);
												}
                                              window.location.reload();
											},
											error: function (e) {
                                              window.location.reload();
											}
										});
									},
									"Cancel": function () {
										$(this).dialog("close");
									}
								}
							});
						}

						var OAuthCode = function (url) {

							this.loginPopup = function (parameter) {
								this.loginPopupUri(parameter);
							}

							this.loginPopupUri = function (parameter) {

								// Launch Popup
								var parameters = "location=1,width=800,height=650";
								parameters += ",left=" + (screen.width - 800) / 2 + ",top=" + (screen.height - 650) / 2;
								//open a window invisible

								var win = window.open(url, 'connectPopup', parameters);
								var pollOAuth = window.setInterval(function () {
									try {

										if (win.document.URL.indexOf("code") != -1) {
											window.clearInterval(pollOAuth);
											win.close();
											syncquickbook();
										}
									} catch (e) {
										console.log(e)
									}
								}, 100);
							}
						}
						var urlqb = '<?php echo $_SESSION['authUrl']; ?>';
						var oauth = new OAuthCode(urlqb);
						oauth.loginPopup();
						//open a jquery ui modal dialog
					}

                    function showLoadingSpinner() {
                      var loadingSpinner = document.createElement('div');
                      loadingSpinner.setAttribute('id', 'loadingSpinner');
                      loadingSpinner.style.display = 'block';
                      loadingSpinner.style.position = 'fixed';
                      loadingSpinner.style.zIndex = '99';
                      loadingSpinner.style.height = '100%';
                      loadingSpinner.style.width = '100%';
                      loadingSpinner.style.background = 'rgba(255,255,255,0.8)';
                      loadingSpinner.style.top = '0';
                      loadingSpinner.style.left = '0';

                      var spinnerText = document.createElement('div');
                      spinnerText.style.position = 'absolute';
                      spinnerText.style.top = '50%';
                      spinnerText.style.left = '50%';
                      spinnerText.style.transform = 'translate(-50%, -50%)';
                      spinnerText.innerHTML = '<p>Loading...</p>';

                      loadingSpinner.appendChild(spinnerText);
                      document.body.appendChild(loadingSpinner);
                    }

				</script>
				<?php

			}


		}

		return 0;
	}
}
