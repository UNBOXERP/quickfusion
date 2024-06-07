<?php
/* Copyright (C) 2014 Juanjo Menent <menent@2byte.es>
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

dol_include_once('/discounts/class/discounts.class.php');

/**
 * Trigger class
 */
class InterfaceDiscounttrigger
{

    private $db;

    /**
     * Constructor
     *
     * 	@param		DoliDB		$db		Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;

        $this->name = preg_replace('/^Interface/i', '', get_class($this));
        $this->family = "demo";
        $this->description = "Triggers of this module are empty functions."
            . "They have no effect."
            . "They are provided for tutorial purpose only.";
        // 'development', 'experimental', 'dolibarr' or version
        $this->version = 'development';
        $this->picto = 'contactdefault@contactdefault';
    }

    /**
     * Trigger name
     *
     * 	@return		string	Name of trigger file
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Trigger description
     *
     * 	@return		string	Description of trigger file
     */
    public function getDesc()
    {
        return $this->description;
    }

    /**
     * Trigger version
     *
     * 	@return		string	Version of trigger file
     */
    public function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version === 'development') {
            return $langs->trans("Development");
        } elseif ($this->version === 'experimental')

                return $langs->trans("Experimental");
        elseif ($this->version === 'dolibarr') return DOL_VERSION;
        elseif ($this->version) return $this->version;
        else {
            return $langs->trans("Unknown");
        }
    }

    /**
     * Function called when a Dolibarrr business event is done.
     * All functions "run_trigger" are triggered if file
     * is inside directory core/triggers
     *
     * 	@param		string		$action		Event action code
     * 	@param		Object		$object		Object
     * 	@param		User		$user		Object user
     * 	@param		Translate	$langs		Object langs
     * 	@param		conf		$conf		Object conf
     * 	@return		int						<0 if KO, 0 if no triggered ran, >0 if OK
     */
    public function run_trigger($action, $object, $user, $langs, $conf)
    {
    	$hola = 0;
    	if ($action === 'LINEBILL_INSERT' || $action === 'LINEORDER_INSERT' || $action === 'LINEPROPAL_INSERT')
		{
			require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
			dol_include_once('/discounts/lib/discounts.lib.php');
			dol_include_once('/discounts/class/discounts.class.php');
			dol_include_once('/discounts/class/discount_doc.class.php');

			if(GETPOST("action")!== "saveTicket")
			{
				if(empty($_POST['origin']) && empty($object->origin))
				{
					if($action === "LINEPROPAL_INSERT"){
						$propal = new Propal($this->db);
						$propal->fetch($object->fk_propal);
						$type_doc = 1;
						$socid = $propal->socid;
						if ($conf->global->DISCOUNT_NO_GROUP_LINES == 0){
							$res = search_duplicates($propal, $object, $type_doc);
							if($res > 0){
								$object->delete($user);
								return $res;
							}
						}
					}
					if($action === "LINEORDER_INSERT"){
						$order = new Commande($this->db);
						$order->fetch($object->fk_commande);
						$type_doc = 2;
						$socid = $order->socid;
						if ($conf->global->DISCOUNT_NO_GROUP_LINES == 0) {
							$res = search_duplicates($order, $object, $type_doc);
							if($res > 0){
								$object->delete();
								return $res;
							}
						}
					}
					if($action === "LINEBILL_INSERT"){
						$bill = new Facture($this->db);
						$bill->fetch($object->fk_facture);
						$type_doc = 3;
						$socid = $bill->socid;
						if ($conf->global->DISCOUNT_NO_GROUP_LINES == 0) {
							$res = search_duplicates($bill, $object, $type_doc);
							if($res > 0){
								$object->delete();
								return $res;
							}
						}
					}

					$res = calcul_discount($object,$socid,$action, $type_doc);
					return $res;
				}
				else{//Si tiene origen
					$hola = 0;
					if($_POST['origin'] === "fichinter" || $object->origin === "fichinter"){
						$objori = new Propal($this->db);
						$objori->fetch($_POST['originid']);
						$type_doc = 1;
					}
					else if($_POST['origin'] === "propal" || $object->origin === "propal"){
						$objori = new Propal($this->db);
                        $objori->fetch($_POST['originid']);
						$type_doc = 1;
					}
					else if($_POST['origin'] === "commande" || $object->origin === "commande"){
						$objori = new Commande($this->db);
                        $objori->fetch($_POST['originid']);
						$type_doc = 2;
					}
                    else if($_POST['origin'] === "shipping" || $object->origin === "shipping"){
                        $send = new Expedition($this->db);
                        $send->fetch($_POST['originid']);
                        $objori = new Commande($this->db);
                        $objori->fetch($send->origin_id);
                        $type_doc = 2;

                        foreach ($send->lines as $line){
                            if($line->id==$object->origin_id){
                                $object->origin_id = $line->origin_line_id;
                            }
                        }
                    }
                    else if($_POST['origin'] === "contrat" || $object->origin === "contrat"){
                        $send = new Contrat($this->db);
                        $send->fetch($_POST['originid']);
                        $send->fetchObjectLinked();
                        foreach ($send->linkedObjectsIds as $key => $value){
                            foreach ($value as $val) {
                                if ($key!='facture') {
                                    if ($key == 'propal') {
                                        $objori = new Propal($this->db);
                                        $objori->fetch($val);
                                        $type_doc = 1;
                                    } elseif ($key == 'commande' && $val!=$object->fk_commande) {
                                        $objori = new Commande($this->db);
                                        $objori->fetch($val);
                                        $type_doc = 2;
                                    }
                                }
                            }
                        }

                        if($object->element=='facturedet') {
                            $facture = new Facture($this->db);
                            $facture->fetch($object->fk_facture);
                        }
                        elseif($object->element=='commandedet'){
                            $facture = new Commande($this->db);
                            $facture->fetch($object->fk_commande);
                        }
                        $num = (count($facture->lines)-1);
                        $object->origin_id = $objori->lines[$num]->id;
                    }
					else{
						$objori = new Facture($this->db);
                        $objori->fetch($_POST['originid']);
						$type_doc = 3;
					}
                    if (version_compare(DOL_VERSION, 3.9) >= 0) {
                        $sql = "SELECT dis.ori_subprice, dis.ori_totalht, dis.descr FROM " . MAIN_DB_PREFIX . $objori->table_element_line . " AS pd ";
                        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "discount_doc AS dis ON dis.fk_doc = pd.rowid";
                        $sql .= " WHERE dis.type_doc = " . $type_doc . " AND pd.fk_product = " . $object->fk_product . " AND pd.rowid =" . $object->origin_id . " AND pd.qty = " . $object->qty;
                    }
                    else {
                        $sql = "SELECT dis.ori_subprice, dis.ori_totalht, dis.descr FROM " . MAIN_DB_PREFIX . $objori->table_element . " AS p LEFT JOIN " . MAIN_DB_PREFIX . $objori->table_element_line . " AS pd ON pd." . $objori->fk_element . " = p.rowid";
                        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "discount_doc AS dis ON dis.fk_doc = pd.rowid";
                        $sql .= " WHERE dis.type_doc = " . $type_doc . " AND pd.fk_product = " . $object->fk_product . " AND p.rowid =" . $_POST['originid'] . " AND pd.qty = " . $object->qty;
                    }

					$resql = $this->db->query($sql);
					if ($this->db->num_rows($resql)){
						$obj = $this->db->fetch_object($resql);
						$dis_doc = new Discounts_doc($this->db);
						$dis_doc->type_doc = $action === "LINEPROPAL_INSERT"?1:($action === "LINEORDER_INSERT"?2:3);
						$dis_doc->fk_doc = $object->rowid;
						$dis_doc->ori_subprice = $obj->ori_subprice;
						$dis_doc->ori_totalht = $obj->ori_totalht;
						$dis_doc->descr = $obj->descr;
						$res = $dis_doc->create($user);
						return $res;
					}
					else{
						if($action === "LINEPROPAL_INSERT"){
							$propal = new Propal($this->db);
							$propal->fetch($object->fk_propal);
							$type_doc = 1;
							$socid = $propal->socid;
						}
						else if($action === "LINEORDER_INSERT"){
							$order = new Commande($this->db);
							$order->fetch($object->fk_commande);
							$type_doc = 2;
							$socid = $order->socid;
						}
						if($action === "LINEBILL_INSERT"){
							$bill = new Facture($this->db);
							$bill->fetch($object->fk_facture);
							$type_doc = 3;
							$socid = $bill->socid;
						}

						$res = calcul_discount($object, $socid, $action,$type_doc);
						return $res;

					}
				}
				return 0;
			}
			return 0;
		}

    	if ($action === 'LINEBILL_UPDATE' || $action === 'LINEORDER_UPDATE' || $action === 'LINEPROPAL_UPDATE')
    	{
    		require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
    		dol_include_once('/discounts/class/discounts.class.php');
    		dol_include_once('/discounts/class/discount_doc.class.php');
    		dol_include_once('/discounts/lib/discounts.lib.php');


    		if(!empty($object->fk_product)) {
				$object->fk_product = empty($object->fk_product) ? $object->oldline->fk_product : $object->fk_product;
			}
			else{
				$object->fk_product = 0;
			}
    			if($action === "LINEPROPAL_UPDATE"){
    				$object->fk_propal = empty($object->fk_propal)?$object->oldline->fk_propal:$object->fk_propal;
    				$propal = new Propal($this->db);
    				$propal->fetch($object->fk_propal);
    				$type_doc = 1;
    				$socid = $propal->socid;
    			}
    			if($action === "LINEORDER_UPDATE"){
    				$object->fk_commande = empty($object->fk_commande)?$object->oldline->fk_commande:$object->fk_commande;
    				$order = new Commande($this->db);
    				$order->fetch($object->fk_commande);
    				$type_doc = 2;
    				$socid = $order->socid;
    			}
    			if($action === "LINEBILL_UPDATE"){
    				$object->fk_facture = empty($object->fk_facture)?$object->oldline->fk_facture:$object->fk_facture;
    				$bill = new Facture($this->db);
    				$bill->fetch($object->fk_facture);
    				$type_doc = 3;
    				$socid = $bill->socid;
    			}

    			$dis_doc = new Discounts_doc($this->db);
    			$res = $dis_doc->fetch($type_doc, $object->rowid);
    			if($res > 0 && $object->subprice != $dis_doc->ori_subprice)
    				$object->subprice = $dis_doc->ori_subprice;

    			$res = calcul_discount($object, $socid, $action,$type_doc);
    			return $res;

   				return 0;

    		return 0;
   		}
    	if ($action === 'LINEBILL_DELETE' || $action === 'LINEORDER_DELETE' || $action === 'LINEPROPAL_DELETE')
   		{
   			require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
   			dol_include_once('/discounts/class/discount_doc.class.php');

   			if(!empty($object->fk_product))
   			{
   				if($action === "LINEPROPAL_DELETE"){
   					$propal = new Propal($this->db);
   					$propal->fetch($object->fk_propal);
   					$type_doc = 1;
   				}
   				if($action === "LINEORDER_DELETE"){
   					$order = new Commande($this->db);
   					$order->fetch($object->fk_commande);
   					$type_doc = 2;
   				}
   				if($action === "LINEBILL_DELETE"){
   					$bill = new Facture($this->db);
   					$bill->fetch($object->fk_facture);
   					$type_doc = 3;
   				}

   				$dis_doc = new Discounts_doc($this->db);
   				$res = $dis_doc->delete($type_doc, $object->rowid);

   				return $res;
   			}
   			return 0;
   		}
   		if ($action === 'ORDER_DELETE' || $action === 'PROPAL_DELETE' || $action === 'BILL_DELETE'){
   			require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
   			dol_include_once('/discounts/class/discount_doc.class.php');

   			$dis_doc = new Discounts_doc($this->db);
   			$type_doc = $action === "PROPAL_DELETE"?1:($action === "ORDER_DELETE"?2:3);
   			foreach($object->lines as $line){
   				$res = $dis_doc->delete($type_doc, $line->rowid);
   			}

   			if($action=='BILL_DELETE'){
   				$sql = 'DELETE FROM ' . MAIN_DB_PREFIX . 'discount WHERE type_source = ' . Discounts::SOURCE_INVOICE . ' AND fk_source = ' . $object->id;
				if ( !$this->db->query($sql) )
				{
					$this->errors[]=$this->db->lasterror();
					$this->db->rollback();
					return -1;
				}
			}
   			return $res;
   		}
   		return 0;
    }
}
