<?php
/* Copyright (C) 2016-2022	Charlene Benke	<charlene@patas-monkey.com>
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
 * 	\file	   htdocs/extraprice/core/triggers/interface_10_modextraprice_price.class.php
 * 	\ingroup	extraprice
 * 	\brief	   détermination d'un prix de vente en fonction des extrafields saisies
 */
class InterfacePrice
{
	/**
	 *   \brief	  Constructeur.
	 *   \param	  DB	  Handler d'acces base
	 */
	function __construct($db)
	{
		$this->db = $db ;
		
		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = "interfaceprix";
		$this->description = "Triggers pour modifier le prix de vente en fonction de valeurs saisie.";
		$this->version = '3.9+1.3.0';						// 'experimental' or 'dolibarr' or version
	}
	/**
	 *   \brief	  Renvoi nom du lot de triggers
	 *   \return	 string	  Nom du lot de triggers
	 */
	function getName()
	{
		return $this->name;
	}
	/**
	 *   \brief	  Renvoi descriptif du lot de triggers
	 *   \return	 string	  Descriptif du lot de triggers
	 */
	function getDesc()
	{
		return $this->description;
	}
	/**
	 *   \brief	  Renvoi version du lot de triggers
	 *   \return	 string	  Version du lot de triggers
	 */
	function getVersion()
	{
		global $langs;
		$langs->load("admin");

		if ($this->version == 'experimental') return $langs->trans("Experimental");
		elseif ($this->version == 'dolibarr') return DOL_VERSION;
		elseif ($this->version) return $this->version;
		else return $langs->trans("Unknown");
	}
	/**
	 *	  \brief	  Fonction appelee lors du declenchement d'un evenement Dolibarr.
	 *				  D'autres fonctions run_trigger peuvent etre presentes dans includes/triggers
	 *	  \param	  action	  Code de l'evenement
	 *	  \param	  object	  Objet concerne
	 *	  \param	  user		Objet user
	 *	  \param	  lang		Objet lang
	 *	  \param	  conf		Objet conf
	 *	  \return	 int		 <0 si ko, 0 si aucune action faite, >0 si ok
	 */
	function run_trigger($action, $object, $user, $langs, $conf)
	{
		dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

		if ($action == 'LINEBILL_INSERT' || $action == 'LINEBILL_UPDATE' || $action == 'LINEBILL_MODIFY') {
			$objecttrigger=new Facture($this->db);
			// en insert on travail sur la ligne en cours, en update sur la ligne ancienne
			if ($action == 'LINEBILL_INSERT')
				$objectline=$object;
			else {
				$objectline=$object->oldline;
			}
			// pas de old line sur les factures... pour le moment
			$objecttrigger->fetch($objectline->fk_facture);
		} elseif ($action == 'LINEORDER_INSERT' || $action == 'LINEORDER_UPDATE' || $action == 'LINEORDER_MODIFY') {
			$objecttrigger=new Commande($this->db);
			if ($action == 'LINEORDER_INSERT')
				$objectline=$object;
			else
				$objectline=$object->oldline;
			$objecttrigger->fetch($objectline->fk_commande);
		} elseif ($action == 'LINEPROPAL_INSERT' || $action == 'LINEPROPAL_UPDATE' || $action == 'LINEPROPAL_MODIFY') {
			$objecttrigger=new Propal($this->db);
			if ($action == 'LINEPROPAL_INSERT')
				$objectline=$object;
			else
				$objectline=$object->oldline;
			$objecttrigger->fetch($objectline->fk_propal);
		} elseif ($action == 'LINEBILL_SUPPLIER_UPDATE' || $action == 'LINEBILL_SUPPLIER_MODIFY') {
			if ($conf->global->EXTRAPRICE_PRODUCT_TVAEXPORT > 0) {
                $arrayProductTvaExport = explode(",", $conf->global->EXTRAPRICE_PRODUCT_TVAEXPORT);
				// on recherche si il y a une ligne de produit correspondant
				if (in_array($object->fk_product, $arrayProductTvaExport)) {
					$lineupdate = new SupplierInvoiceLine($this->db);
					$lineupdate->fetch($object->id);
					$lineupdate->pu_ht		= 0;
					$lineupdate->pu_ttc		= $object->pu_ttc;
					$lineupdate->qty		= $object->qty;
					$lineupdate->taux_tva	= 0;
					$lineupdate->total_ht	= 0;
					$lineupdate->total_tva	= ($object->pu_ttc * $object->qty);
					$lineupdate->total_ttc	= $lineupdate->total_tva;

					$lineupdate->multicurrency_subprice		= $object->multicurrency_subprice;
					$lineupdate->multicurrency_total_ht		= 0;
					$lineupdate->multicurrency_total_tva	= $object->multicurrency_total_ttc;
					$lineupdate->multicurrency_total_ttc	= $object->multicurrency_total_ttc;

					$lineupdate->update(1);
				}
			}
            // il n'y a pas plus de mise à jour à réaliser
            return 0;
		} else
		    return 0; // pour les autres éléments, on ne fait rien

		// Si on est pas sortie avant on commence à traiter extraprice
		include_once (DOL_DOCUMENT_ROOT.'/core/lib/price.lib.php');
		dol_include_once('/extraprice/class/extraprice.class.php');

		// on récupère le nouveau prix 
		$extrapricestatic=new Extraprice($this->db);
		$autreprix=$extrapricestatic->PriceWithExtrafields($objecttrigger, $objectline);

		// si le prix a été changé
		if (! $autreprix == false) {
			$pu =price2num($autreprix);
			$price_base_type='HT';

			$remise_percent=$objectline->remise;

			// détermination du prix 
			$tabprice = calcul_price_total(
							$objectline->qty, $pu, $remise_percent, $objectline->tva_tx,
							$objectline->localtax1_tx, $objectline->localtax2_tx, 0,
							$price_base_type, $objectline->info_bits, $objectline->product_type
			);

			$total_ht  = $tabprice[0];
			$total_tva = $tabprice[1];
			$total_ttc = $tabprice[2];
			$total_localtax1=$tabprice[9];
			$total_localtax2=$tabprice[10];
			$pu_ht  = $tabprice[3];
			$pu_tva = $tabprice[4];
			$pu_ttc = $tabprice[5];

			$remise = 0;
			$price = 0;
			if ($remise_percent > 0) {
				$remise = round(($pu * $remise_percent / 100),2);
				$price = ($pu - $remise);
			}

			$price	= price2num($price);
			// pour les facture si c'est un avoir on inverse le signe
			$objType = 1;
			if($objecttrigger->element =="facture" && $objecttrigger->type == 2)
				$objType = -1;

			$object->tva_tx				= $objectline->tva_tx;
			$object->remise_percent		= $remise_percent;
			$object->subprice			= $objType*abs($pu);

			$object->total_ht			= $objType*abs($total_ht);
			$object->total_tva			= $objType*abs($total_tva);
			$object->total_localtax1	= $objType*abs($total_localtax1);
			$object->total_localtax2	= $objType*abs($total_localtax2);
			$object->total_ttc			= $objType*abs($total_ttc);

			// on met à jour mais on n'execute pas le trigger (sinon on boucle en MAJ)
			$result=$object->update($user, 1);
			if ($result > 0)
				$objecttrigger->update_price();
		}
		return 0;
	}
}
?>