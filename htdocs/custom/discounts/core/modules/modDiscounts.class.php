<?php
/* Copyright (C) 2014 Juanjo Menent <jmenent@2byte.es>
 * Copyright (C) 2015 Ferran Marcet <fmarcet@2byte.es>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\defgroup	contactdefault	Discounts module
 * 	\brief		Discounts module descriptor.
 * 	\file		core/modules/modDiscounts.class.php
 * 	\ingroup	contactdefault
 * 	\brief		Description and activation file for module Discounts
 */
include_once DOL_DOCUMENT_ROOT . "/core/modules/DolibarrModules.class.php";

/**
 * Description and activation class for module Discounts
 */
class modDiscounts extends DolibarrModules
{

    /**
     * 	Constructor. Define names, constants, directories, boxes, permissions
     *
     * 	@param	DoliDB		$db	Database handler
     */
    public function __construct($db)
    {
        global $langs, $conf;

        $this->db = $db;

        // Id for module (must be unique).
        // Use a free id here
        // (See in Home -> System information -> Dolibarr for list of used modules id).
        $this->numero =400027;
        // Key text used to identify module (for permissions, menus, etc...)
        $this->rights_class = 'discounts';

        // Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
        // It is used to group modules in module setup page
        $this->family = "crm";
        // Module label (no space allowed)
        // used if translation string 'ModuleXXXName' not found
        // (where XXX is value of numeric property 'numero' of module)
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        // Module description
        // used if translation string 'ModuleXXXDesc' not found
        // (where XXX is value of numeric property 'numero' of module)
        $this->description = "Description of module Discounts";
        // Possible values for version are: 'development', 'experimental' or version
        $this->version = '14.0.0';
        // Key used in llx_const table to save module status enabled/disabled
        // (where MYMODULE is value of property name of module in uppercase)
        $this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);
        // Where to store the module in setup page
        // (0=common,1=interface,2=others,3=very specific)
        $this->special = 2;
        // Name of image file used for this module.
        // If file is in theme/yourtheme/img directory under name object_pictovalue.png
        // use this->picto='pictovalue'
        // If file is in module/img directory under name object_pictovalue.png
        // use this->picto='pictovalue@module'
        $this->picto = 'discounts@discounts'; // mypicto@contactdefault

        $this->editor_name = "<b>2byte.es</b>";
        $this->editor_web = "www.2byte.es";
        // Defined all module parts (triggers, login, substitutions, menus, css, etc...)
        // for default path (eg: /contactdefault/core/xxxxx) (0=disable, 1=enable)
        // for specific path of parts (eg: /contactdefault/core/modules/barcode)
        // for specific css file (eg: /contactdefault/css/contactdefault.css.php)
        $this->module_parts = array(
            // Set this to 1 if module has its own trigger directory
            'triggers' => 1,
            // Set this to 1 if module has its own login method directory
            //'login' => 0,
            // Set this to 1 if module has its own substitution function file
            //'substitutions' => 0,
            // Set this to 1 if module has its own menus handler directory
            //'menus' => 0,
            // Set this to 1 if module has its own barcode directory
            //'barcode' => 0,
            // Set this to 1 if module has its own models directory
            'models' => 1,
            // Set this to relative path of css if module has its own css file
            'css' => array('/discounts/css/promo.css'),
            // Set here all hooks context managed by module
            //'hooks' => array('hookcontext1','hookcontext2')
            // Set here all workflow context managed by module
            //'workflow' => array('order' => array('WORKFLOW_ORDER_AUTOCREATE_INVOICE'))
        );

        // Data directories to create when module is enabled.
        // Example: this->dirs = array("/contactdefault/temp");
        $this->dirs = array();

        // Config pages. Put here list of php pages
        // stored into contactdefault/admin directory, used to setup module.
        $this->config_page_url = array("discounts.php@discounts");

        // Dependencies
        // List of modules id that must be enabled if this module is enabled
        $this->depends = array();
        // List of modules id to disable if this one is disabled
        $this->requiredby = array();
        // Minimum version of PHP required by module
        $this->phpmin = array(5, 6);
        // Minimum version of Dolibarr required by module
        $this->need_dolibarr_version = array(7, 0);
        $this->langfiles = array("discounts@discounts"); // langfiles@contactdefault
        // Constants
        $this->const = array();

        // Array to add new pages in new tabs
        // Example:
        $this->tabs = array(
            //	// To add a new tab identified by code tabname1
            //	'objecttype:+tabname1:Title1:langfile@contactdefault:$user->rights->contactdefault->read:/contactdefault/mynewtab1.php?id=__ID__',
            //	// To add another new tab identified by code tabname2
            //	'objecttype:+tabname2:Title2:langfile@contactdefault:$user->rights->othermodule->read:/contactdefault/mynewtab2.php?id=__ID__',
            //	// To remove an existing tab identified by code tabname
            //	'objecttype:-tabname'
            'thirdparty:+discounts:Discounts:discounts@discounts:$user->rights->discounts->read:/discounts/tabs/thirds.php?socid=__ID__',
        	'product:+discounts:Discounts:discounts@discounts:$user->rights->discounts->read:/discounts/tabs/products.php?prodid=__ID__',
			'invoice:+discounts:Discounts:discounts@discounts:$user->rights->discounts->read:/discounts/tabs/invoices.php?id=__ID__',
            'categories_product:+discounts:Discounts:discounts@discounts:$user->rights->discounts->read:/discounts/tabs/category.php?id=__ID__&type1=product',
            'categories_customer:+discounts:Discounts:discounts@discounts:$user->rights->discounts->read:/discounts/tabs/category.php?id=__ID__&type1=customer'
        );

        // Dictionnaries
        $this->dictionnaries = array();

        $this->boxes = array(); // Boxes list

        // Permissions
        $this->rights = array(); // Permission array used by this module
        $r=0;
        $this->rights[$r][0] = 4000271; 				// Permission id (must not be already used)
		$this->rights[$r][1] = 'read';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$r++;

        $this->rights[$r][0] = 4000272; 	// Permission id (must not be already used)
		$this->rights[$r][1] = 'create';	// Permission label
		$this->rights[$r][3] = 1; 			// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'create';	// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$r++;

        $this->menus = array(); // List of menus to add
        $r=0;
        //Menu left into products
        $this->menu[$r]=array('fk_menu'=>'fk_mainmenu=products',
            'type'=>'left',
            'titre'=>'Module400027Name',
            'mainmenu'=>'products',
            'leftmenu'=>'1',
            'url'=>'/discounts/products_list.php',
            'langs'=>'discounts@discounts',
            'position'=>100,
            'enabled'=>'$conf->discounts->enabled',
            'perms'=>'1',
            'target'=>'',
            'user'=>0);

        $r++;
        $this->menu[$r]=array('fk_menu'=>'fk_mainmenu=companies',
            'type'=>'left',
            'titre'=>'Module400027Name',
            'mainmenu'=>'companies',
            'leftmenu'=>'1',
            'url'=>'/discounts/thirds_list.php',
            'langs'=>'discounts@discounts',
            'position'=>100,
            'enabled'=>'$conf->discounts->enabled',
            'perms'=>'1',
            'target'=>'',
            'user'=>0);
        $r++;
        $this->menu[$r]=array('fk_menu'=>'fk_mainmenu=commercial',
            'type'=>'left',
            'titre'=>'Module400027Name',
            'mainmenu'=>'commercial',
            'leftmenu'=>'1',
            'url'=>'/discounts/discounts_list.php',
            'langs'=>'discounts@discounts',
            'position'=>100,
            'enabled'=>'$conf->discounts->enabled',
            'perms'=>'1',
            'target'=>'',
            'user'=>0);
        $r++;
		$this->menu[$r]=array('fk_menu'=>'fk_mainmenu=billing',
			'type'=>'left',
			'titre'=>'Module400027Name',
			'mainmenu'=>'billing',
			'leftmenu'=>'1',
			'url'=>'/discounts/invoices_list.php',
			'langs'=>'discounts@discounts',
			'position'=>100,
			'enabled'=>'$conf->discounts->enabled',
			'perms'=>'1',
			'target'=>'',
			'user'=>0);
		$r++;

    }

    /**
     * Function called when module is enabled.
     * The init function add constants, boxes, permissions and menus
     * (defined in constructor) into Dolibarr database.
     * It also creates data directories
     *
     * 	@param		string	$options	Options when enabling module ('', 'noboxes')
     * 	@return		int					1 if OK, 0 if KO
     */
    public function init($options = '')
    {
        $sql = array();

        $result = $this->db->query("SHOW COLUMNS FROM ".MAIN_DB_PREFIX."discounts LIKE 'fk_category'");
        $exists = ($this->db->num_rows($result)?TRUE:FALSE);

        $result = $this->loadTables();

        if($exists) {
            $s = "UPDATE ".MAIN_DB_PREFIX."discounts SET type_target=3 WHERE fk_target > 0";
            $this->db->query($s);
        }

		if(version_compare(DOL_VERSION, 10.0) == 0){
			$replaced = DOL_DOCUMENT_ROOT ."/core/modules/facture/doc/pdf_crabe.modules.php";
			$origin = dol_buildpath('/discounts/core_10/pdf_crabe.modules.php');

			if (dol_copy($origin, $replaced) == -1) {

				$msg = 'dol_copy failed Permission denied to overwrite target file';
				setEventMessages($msg, null, 'warnings');
				return false;

			} elseif (dol_copy($origin, $replaced) == -2) {

				$msg = 'dol_copy failed Permission denied to write into target directory';
				setEventMessages($msg, null, 'warnings');
				return false;

			} elseif (dol_copy($origin, $replaced) == -3) {

				$msg = 'dol_copy failed to copy';
				setEventMessages($msg, null, 'warnings');
				return false;

			}

			$replaced1 = DOL_DOCUMENT_ROOT ."/core/modules/propale/doc/pdf_azur.modules.php";
			$origin1 = dol_buildpath('/discounts/core_10/pdf_azur.modules.php');

			if (dol_copy($origin1, $replaced1) == -1) {

				$msg = 'dol_copy failed Permission denied to overwrite target file';
				setEventMessages($msg, null, 'warnings');
				return false;

			} elseif (dol_copy($origin1, $replaced1) == -2) {

				$msg = 'dol_copy failed Permission denied to write into target directory';
				setEventMessages($msg, null, 'warnings');
				return false;

			} elseif (dol_copy($origin1, $replaced1) == -3) {

				$msg = 'dol_copy failed to copy';
				setEventMessages($msg, null, 'warnings');
				return false;

			}

			$replaced2 = DOL_DOCUMENT_ROOT ."/core/modules/commande/doc/pdf_einstein.modules.php";
			$origin2 = dol_buildpath('/discounts/core_10/pdf_einstein.modules.php');

			if (dol_copy($origin2, $replaced2) == -1) {

				$msg = 'dol_copy failed Permission denied to overwrite target file';
				setEventMessages($msg, null, 'warnings');
				return false;

			} elseif (dol_copy($origin2, $replaced2) == -2) {

				$msg = 'dol_copy failed Permission denied to write into target directory';
				setEventMessages($msg, null, 'warnings');
				return false;

			} elseif (dol_copy($origin2, $replaced2) == -3) {

				$msg = 'dol_copy failed to copy';
				setEventMessages($msg, null, 'warnings');
				return false;

			}
		}

        return $this->_init($sql, $options);
    }

    /**
     * Function called when module is disabled.
     * Remove from database constants, boxes and permissions from Dolibarr database.
     * Data directories are not deleted
     *
     * 	@param		string	$options	Options when enabling module ('', 'noboxes')
     * 	@return		int					1 if OK, 0 if KO
     */
    public function remove($options = '')
    {
        $sql = array();

        return $this->_remove($sql, $options);
    }

    /**
     * Create tables, keys and data required by module
     * Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
     * and create data commands must be stored in directory /contactdefault/sql/
     * This function is called by this->init
     *
     * 	@return		int		<=0 if KO, >0 if OK
     */
    private function loadTables()
    {
        return $this->_load_tables('/discounts/sql/');
    }
}
