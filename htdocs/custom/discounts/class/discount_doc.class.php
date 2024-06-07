<?php
/* Copyright (C) 2014-2017  Ferran Marcet	<fmarcet@2byte.es>
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

require_once DOL_DOCUMENT_ROOT .'/core/class/commonobject.class.php';


/**
 *	Classe des gestion des fiches interventions
 */
class Discounts_doc extends CommonObject
{
	public $element='discounts';
	public $table_element='discount_doc';
	public $fk_element='fk_dto';

	public $id;

    public $type_doc; // 1=Propal, 2=Commande, 3=Invoice
    public $fk_doc;
    public $descr;
    public $dto_rate;
    public $dto_amount;
    public $ori_subprice;
    public $ori_totalht;

	/**
	 *	Constructor
	 *
	 *  @param	DoliDB	$db		Database handler
 	 */
	public function __construct($db)
	{
		$this->db = $db;
	}


	/**
	 *	Create into data base
	 *
	 *  @param		User	$user 		Objet user that make creation
     *	@param		int		$notrigger	Disable all triggers
	 *	@return		int		<0 if KO, >0 if OK
	 */
	public function create($user, $notrigger=0)
	{
		global $conf;

        $error = 0;

		dol_syslog(get_class($this)."::create descr=".$this->descr);

		// Check parameters
		if (! is_numeric($this->dto_rate)) $this->dto_rate = 0;
		if (! is_numeric($this->dto_amount)) $this->dto_amount = 0;

		$now=dol_now();

		$this->db->begin();
		
		$sql = "INSERT INTO ".MAIN_DB_PREFIX.$this->table_element." (";
		$sql.= "entity";
		$sql.= ", type_doc";
		$sql.= ", fk_doc";
		$sql.= ", descr";
		$sql.= ", dto_rate";
  		$sql.= ", dto_amount";
  		$sql.= ", ori_subprice";
  		$sql.= ", ori_totalht";
  		$sql.= ") ";
		$sql.= " VALUES (";
		$sql.= $conf->entity;
		$sql.= ", ".$this->type_doc;
		$sql.= ", ".$this->fk_doc;
		$sql.= ", ".($this->descr?"'".$this->db->escape($this->descr)."'":"null");
		$sql.= ", ".$this->dto_rate;
		$sql.= ", ".$this->dto_amount;
		$sql.= ", ".$this->ori_subprice;
		$sql.= ", ".$this->ori_totalht;
		$sql.= ")";

		dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
		$result=$this->db->query($sql);
		if ($result)
		{
			$this->id=$this->db->last_insert_id($this->table_element);
			if (! $error)
			{
				$this->db->commit();
				return $this->id;
			}
			else
			{
				$this->db->rollback();
				$this->errors[]=implode(',',$this->errors);
				dol_syslog(get_class($this)."::create ".$this->errors,LOG_ERR);
				return -1;
			}
		}
		else
		{
			$this->errors[]=$this->db->error();
			dol_syslog(get_class($this)."::create ".$this->errors, LOG_ERR);
			$this->db->rollback();
			return -1;
		}

	}

	/**
	 * 
	 * @param unknown_type $user
	 * @return number
	 */
	public function update($user)
	{

		dol_syslog(get_class($this)."::create descr=".$this->descr);

		// Check parameters
		/*if (! is_numeric($this->dto_rate)) $this->dto_rate = 0;

		if ($this->dto_rate <= 0 && $this->type_dto!=4)
		{
			$this->error='ErrorBadParameter';
			dol_syslog(get_class($this)."::create ".$this->error,LOG_ERR);
			return -1;
		}*/


		$this->db->begin();
		
		$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
		
		$sql.= " SET ";
		
		$sql.= " type_doc='".$this->type_doc."'";
		$sql.= ", fk_doc='".$this->fk_doc."'";
		$sql.= ", descr='".$this->descr."'";
		$sql.= ", dto_rate='".$this->dto_rate."'";
  		$sql.= ", dto_amount='".$this->dto_amount."'";
  		$sql.= ", ori_subprice=".$this->ori_subprice;
  		$sql.= ", ori_totalht=".$this->ori_totalht;
  		$sql.= " WHERE rowid = " . $this->id;
		
		dol_syslog(get_class($this)."update sql=".$sql);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$this->db->commit();
			return 1;
		}
		else
		{
			$this->errors[]=$this->db->error();
			dol_syslog(get_class($this)."::update ".$this->errors, LOG_ERR);
			$this->db->rollback();
			return -1;
		}
		
	}
	
	/**
	 *	Fetch a discount
	 *
	 *	@param		int		$rowid		Id of discount
	 *	@return		int					<0 if KO, >0 if OK, =0 if no result
	 */
	public function fetch($type_doc, $fk_doc)
	{
		$sql = "SELECT rowid, type_doc,fk_doc,descr,dto_rate,dto_amount, ori_subprice, ori_totalht";    
		$sql.= " FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql.= " WHERE type_doc = ".$type_doc." AND fk_doc = ".$fk_doc;
		
		dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			if ($this->db->num_rows($resql))
			{
				$obj = $this->db->fetch_object($resql);

				$this->id           = $obj->rowid;
				$this->type_doc		= $obj->type_doc;
				$this->fk_doc		= $obj->fk_doc;
				$this->descr		= $obj->descr;
				$this->dto_rate		= $obj->dto_rate;
				$this->dto_amount	= $obj->dto_amount;
				$this->ori_subprice	= $obj->ori_subprice;
				$this->ori_totalht	= $obj->ori_totalht;
								
				return 1;
			}
			else 
				return 0;
		}
		else
		{
			$this->errors[]=$this->db->error();
			dol_syslog(get_class($this)."::fetch ".$this->errors,LOG_ERR);
			return -1;
		}
	}
	
	
	
	/**
	 * Fetch all discounts from a type and source
	 * 
	 * @param 	int	 	$type		Type of source (1=Third, 2=Product)
	 * @param 	int 	$fk_source	Source ID
	 * 
	 * @return 	array	list of discounts, -1 if ko
	 */
	/*function fetch_all($type=1,$fk_source)
	{
		global $conf;
		
		$sql = "SELECT";
		$sql.= " rowid";
		$sql.= ", type_dto";
		$sql.= ", type_source";
		$sql.= ", description";
		$sql.= ", dto_rate";
  		$sql.= ", fk_source";
  		$sql.= ", payment_cond";
		$sql.= ", datec";     
		$sql.= ", date_start";
		$sql.= ", date_end";
		$sql.= ", fk_user_author";
		$sql.= ", fk_category";
		$sql.= ", qtybuy";
		$sql.= ", qtypay";
		$sql.= ", payment_cond";
		$sql.= " FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql.= " WHERE fk_source = ".$fk_source;
		$sql.= " AND type_source = ".$type;
		$sql.= " AND entity = ".$conf->entity;
		$sql.= " ORDER BY description DESC";
		
		dol_syslog(get_class($this)."::fetchall sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$j=0;
			$num = $this->db->num_rows($resql);
			while ($j<$num)
			{
				$obj = $this->db->fetch_object($resql);
				if ($obj)
				{
					$result[$j]['id'] 	= $obj->rowid;
					$result[$j]['type_dto']  = $obj->type_dto;
					$result[$j]['type_source'] = $obj->type_source;
					$result[$j]['desc'] = $obj->description;
					$result[$j]['dto_rate'] = $obj->dto_rate;
					$result[$j]['date_start'] = $obj->date_start;
					$result[$j]['date_end'] = $obj->date_end;
					$result[$j]['datec'] = $obj->datec;
					$result[$j]['fk_user_author'] = $obj->fk_user_author;
					$result[$j]['fk_category'] = $obj->fk_category;
					$result[$j]['qtybuy'] = $obj->qtybuy;
					$result[$j]['qtypay'] = $obj->qtypay;
					$result[$j]['payment_cond'] = $obj->payment_cond;
					
					$j++;
				}
			}

			$this->db->free($resql);

			return $result;
		}
		else
		{
			$this->error="Error ".$this->db->lasterror();
			dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
			return -1;
		}
	}*/

	
	/**
	 *	Delete
	 *
	 *	@param      User	$user			Object user who delete
	 *	@param		int		$notrigger		Disable trigger
	 *	@return		int						<0 if KO, >0 if OK
	 */
	public function delete($type_doc, $fk_doc)
	{
		global $conf,$langs;

		$error=0;

		$this->db->begin();

		$sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql.= " WHERE type_doc = ".$type_doc." AND fk_doc = ".$fk_doc;

		dol_syslog(get_class($this)."::delete sql=".$sql);
		
		if ( $this->db->query($sql) )
		{
			$this->db->commit();
			return 1;
			
		}
		else
		{
			$this->errors[]=$this->db->lasterror();
			$this->db->rollback();
			return -1;
		}
	}
	
 	/*function select_type($selected='',$htmlname='type',$showempty=0)
    {
        global $db,$langs,$user,$conf;

        $out='';
        if($selected=='') $selected=-1;
		$out.= '<select class="flat" id="'.$htmlname.'" name="'.$htmlname.'">';
		if ($showempty)
		{
			$out.=  '<option value="-1"';
			if ($selected == -1) $out.=  ' selected="selected"';
			$out.=  '>&nbsp;</option>';
		}

		/*$out.=  '<option value="1"';
		if (1 == $selected) $out.=  ' selected="selected"';
		$out.=  '>'.$langs->trans("Commercial");

		$out.=  '<option value="2"';
		if (2 == $selected) $out.=  ' selected="selected"';
		$out.=  '>'.$langs->trans("Financial");
		
		$out.=  '<option value="3"';
		if (3 == $selected) $out.=  ' selected="selected"';
		$out.=  '>'.$langs->trans("Time");
		
		$out.=  '<option value="4"';
		if (4 == $selected) $out.=  ' selected="selected"';
		$out.=  '>'.$langs->trans("BuyXPayY");
		
		$out.=  '<option value="5"';
		if (5 == $selected) $out.=  ' selected="selected"';
		$out.=  '>'.$langs->trans("SecondUnit");

		$out.=  '</select>';
            //if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
        return $out;
    }*/
    
    /*function getLabelTypeDto($type)
    {
        global $langs;

        $label=$langs->trans('type_dto'.$type);
        return $label;
    }*/
}