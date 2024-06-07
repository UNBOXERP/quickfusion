<?php
/* Copyright (C) 2014-2018  Ferran Marcet	<fmarcet@2byte.es>
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
class Discounts extends CommonObject
{
    public $element = 'discounts';
    public $table_element = 'discount';
    public $fk_element = 'fk_dto';

    public $id;

    public $type_dto; // 1=Comm, 2=Finan, 3=Time
    public $type_source; //1=Third, 2=Product, 3=Category
    public $desc;
    public $dto_rate;
    public $qtybuy;
    public $qtypay;
    public $fk_source;
    public $date_start;
    public $date_end;
    public $payment_cond;
    public $fk_target;
    public $type_target;
    public $priority;

    const DTO_COMM = 1;
    const DTO_BUYXPAYY = 4;
    const DTO_UNIT2DTO = 5;

	const SOURCE_INVOICE=4;
	const SOURCE_CATEGORY=3;
	const SOURCE_PRODUCT=2;
	const SOURCE_THIRD=1;

    /**
     *    Constructor
     *
     * @param    DoliDB $db Database handler
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
		global $conf, $user, $langs;

        dol_syslog(get_class($this) . "::create desc=" . $this->desc);

        // Check parameters
        if (!is_numeric($this->dto_rate)) $this->dto_rate = 0;

        if ($this->dto_rate <= 0 && $this->type_dto != self::DTO_BUYXPAYY) {
            $this->errors[] = 'ErrorBadParameter';
            dol_syslog(get_class($this) . "::create " . $this->errors, LOG_ERR);
            return -1;
        }

        $now = dol_now();

        $this->db->begin();

		$sql = "INSERT INTO ".MAIN_DB_PREFIX.$this->table_element." (";
		$sql.= "entity";
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
		$sql.= ", fk_target";
		$sql.= ", type_target";
		$sql.= ", qtybuy";
		$sql.= ", qtypay";
        $sql.= ", priority";
  		$sql.= ") ";
		$sql.= " VALUES (";
		$sql.= $conf->entity;
		$sql.= ", ".$this->type_dto;
		$sql.= ", ".$this->type_source;
		$sql.= ", ".($this->desc?"'".$this->db->escape($this->desc)."'":"null");
		$sql.= ", ".$this->dto_rate;
		$sql.= ", ".$this->fk_source;
		$sql.= ", ".($this->payment_cond?"'".$this->payment_cod."'":"null");
		$sql.= ", '".$this->db->idate($now)."'";
        if(empty($this->date_start)){
            $sql.= ", null";
        } else {
            $sql.= ", '".$this->db->idate($this->date_start)."'";
        }

        if(empty($this->date_end)){
            $sql.= ", null";
        } else {
            $sql.= ", '".$this->db->idate($this->date_end)."'";
        }

		$sql.= ", '".$user->id."'";
		$sql.= ", ".($this->fk_target?"'".$this->fk_target."'":"0");
        $sql.= ", ".($this->type_target?"'".$this->type_target."'":"0");
		$sql.= ", ".($this->qtybuy?"'".$this->qtybuy."'":"0");
		$sql.= ", ".($this->qtypay?"'".$this->qtypay."'":"0");
        $sql.= ", ".($this->priority?"'".$this->priority."'":"99");
		$sql.= ")";

		dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
		$result=$this->db->query($sql);
		if ($result)
		{
			$this->id=$this->db->last_insert_id($this->table_element);

			$this->db->commit();
			return $this->id;

		}
		else
		{
			$this->error=$this->db->error();
			dol_syslog(get_class($this)."::create ".$this->error, LOG_ERR);
			$this->db->rollback();
			return -1;
		}

	}

    /**
     *
     * @param User $user
     * @return number
     */
    public function update($user)
    {

		global $conf, $user, $langs;

		dol_syslog(get_class($this)."::create desc=".$this->desc);

		// Check parameters
		if (! is_numeric($this->dto_rate)) $this->dto_rate = 0;

		if ($this->dto_rate <= 0 && $this->type_dto!=self::DTO_BUYXPAYY)
		{
			$this->error='ErrorBadParameter';
			dol_syslog(get_class($this)."::create ".$this->error,LOG_ERR);
			return -1;
		}

		$now=dol_now();

		$this->db->begin();
		
		$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
		
		$sql.= " SET ";
		
		$sql.= " type_dto='".$this->type_dto."'";
		$sql.= ", type_source='".$this->type_source."'";
		$sql.= ", description='".$this->desc."'";
		$sql.= ", dto_rate='".$this->dto_rate."'";
  		$sql.= ", fk_source='".$this->fk_source."'";
  		$sql.= ", payment_cond=".($this->payment_cond?"'".$this->payment_cod."'":"null");
  		$sql.= ", fk_target=".($this->fk_target?"'".$this->fk_target."'":"null");
        $sql.= ", type_target=".($this->type_target?"'".$this->type_target."'":"null");
  		$sql.= ", qtybuy=".($this->qtybuy?"'".$this->qtybuy."'":"null");
  		$sql.= ", qtypay=".($this->qtypay?"'".$this->qtypay."'":"null");
		if(empty($this->date_start)){
			$sql.= ", date_start= null";
		} else {
			$sql.= ", date_start='".$this->db->idate($this->date_start)."'";
		}

		if(empty($this->date_end)){
			$sql.= ", date_end=null";
		} else {
			$sql.= ", date_end='".$this->db->idate($this->date_end)."'";
		}
		//$sql.= ", date_start='".$this->db->idate($this->date_start)."'";
		//$sql.= ", date_end='".$this->db->idate($this->date_end)."'";
        $sql.= ", priority=".($this->priority?"'".$this->priority."'":"99");
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
			$this->error=$this->db->error();
			dol_syslog(get_class($this)."::update ".$this->error, LOG_ERR);
			$this->db->rollback();
			return -1;
		}
		
	}
	
	/**
	 *	Fetch a discount
	 *
	 *	@param		int		$rowid		Id of discount
	 *	@return		int					<0 if KO, >0 if OK
	 */
	public function fetch($rowid)
	{
		$sql = "SELECT rowid, type_dto,type_source,description,dto_rate,fk_source, date_start,date_end, payment_cond, fk_target, type_target, qtybuy, qtypay,priority";
		$sql.= " FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql.= " WHERE rowid=".$rowid;
		
		dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			if ($this->db->num_rows($resql))
			{
				$obj = $this->db->fetch_object($resql);

				$this->id           = $obj->rowid;
				$this->type_dto		= $obj->type_dto;
				$this->type_source	= $obj->type_source;
				$this->desc			= $obj->description;
				$this->dto_rate		= $obj->dto_rate;
				$this->fk_source	= $obj->fk_source;
				$this->date_start	= $this->db->jdate($obj->date_start);
				$this->date_end		= $this->db->jdate($obj->date_end);
				$this->payment_cond = $obj->payment_cond;
				$this->fk_target	= $obj->fk_target;
				$this->type_target  = $obj->type_target;
				$this->qtybuy		= $obj->qtybuy;
				$this->qtypay		= $obj->qtypay;
				$this->priority     = $obj->priority;
				
				return 1;
			}
		}
		else
		{
			$this->error=$this->db->error();
			dol_syslog(get_class($this)."::fetch ".$this->error,LOG_ERR);
			return -1;
		}
	}
	
	/**
	 * Fetch all discounts from a type and source
	 * 
	 * @param 	int	 	$type		Type of source (1=Third, 2=Product, 3=Category, -1=All)
	 * @param 	int 	$fk_source	Source ID; in case type=-1 Customer
	 * @param	int		$fk_product only in case type=3
	 * 
	 * @return 	array|int	list of discounts, -1 if ko
	 */
	public function fetch_all($type=1,$fk_source, $fk_product=0)
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
		$sql.= ", fk_target";
		$sql.= ", type_target";
		$sql.= ", qtybuy";
		$sql.= ", qtypay";
		$sql.= ", payment_cond";
        $sql.= ", priority";
		$sql.= " FROM ".MAIN_DB_PREFIX.$this->table_element;
		//if($type > 0){
			$sql.= " WHERE (fk_source = ".$fk_source;
			$sql.= " AND type_source = ".$type.")";
			$sql.= " OR (fk_target = ".$fk_source;
			$sql.= " AND type_target = ".$type.")";
			$sql.= " AND entity = ".$conf->entity;
			$sql.= " ORDER BY description DESC";
		//}
		/*else {
            $sql .= " WHERE (fk_source = " . $fk_source . " AND type_source = " . Discounts::SOURCE_THIRD . ")";
            $sql .= " OR (fk_target = " . $fk_source . " AND type_target = " . Discounts::SOURCE_THIRD . ")";
            $sql .= " OR (fk_source = " . $fk_product . " AND type_source = " . Discounts::SOURCE_PRODUCT . ")";
            $sql .= " OR (fk_target = " . $fk_product . " AND type_target = " . Discounts::SOURCE_PRODUCT . ")";

            $category_static = new Categorie($this->db);
            $list_third = implode(',', $category_static->containing($fk_source, 'customer', 'id'));
            $list_prod = implode(',', $category_static->containing($fk_product, 'product', 'id'));

            $sql .= " OR (fk_source IN (" . $list_third . ") AND type_source = " . Discounts::SOURCE_CATEGORY . ")";
            $sql .= " OR (fk_target IN (" . $list_third . ") AND type_source = " . Discounts::SOURCE_CATEGORY . ")";
            $sql .= " OR (fk_source IN (" . $list_prod . ") AND type_source = " . Discounts::SOURCE_CATEGORY . ")";
            $sql .= " OR (fk_target IN (" . $list_prod . ") AND type_source = " . Discounts::SOURCE_CATEGORY . ")";

            $sql .= " AND entity = " . $conf->entity;
            $sql .= " ORDER BY priority ASC";
        }*/

		dol_syslog(get_class($this)."::fetchall sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$j=0;
			$num = $this->db->num_rows($resql);
            $result = null;
			while ($j<$num)
			{
				$obj = $this->db->fetch_object($resql);
				if ($obj)
				{
					$result[$j]['id'] 	= $obj->rowid;
					$result[$j]['type_dto']  = $obj->type_dto;
					$result[$j]['type_source'] = $obj->type_source;
                    $result[$j]['fk_source'] = $obj->fk_source;
					$result[$j]['desc'] = $obj->description;
					$result[$j]['dto_rate'] = $obj->dto_rate;
					$result[$j]['date_start'] = $obj->date_start;
					$result[$j]['date_end'] = $obj->date_end;
					$result[$j]['datec'] = $obj->datec;
					$result[$j]['fk_user_author'] = $obj->fk_user_author;
					$result[$j]['fk_target'] = $obj->fk_target;
					$result[$j]['type_target'] = $obj->type_target;
					$result[$j]['qtybuy'] = $obj->qtybuy;
					$result[$j]['qtypay'] = $obj->qtypay;
					$result[$j]['payment_cond'] = $obj->payment_cond;
                    $result[$j]['priority'] = $obj->priority;

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
	}


    /**
     * Fetch all discounts from a type and source
     *
     * @param 	int	 	$type		Type (0=All, 1=Third, 2=Product, 3=Priority)
     * @param 	int 	$fk_soc	customer ID; in case type=0 Customer
     * @param	int		$fk_product only in case type=0 and 3
     *
     * @return 	array|int	list of discounts, -1 if ko
     */
    public function fetch_all_calcul($type=0,$fk_soc=0, $fk_product=0, $id=0)
    {
        global $conf;

        dol_include_once('/categories/class/categorie.class.php');

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
        $sql.= ", fk_target";
        $sql.= ", type_target";
        $sql.= ", qtybuy";
        $sql.= ", qtypay";
        $sql.= ", payment_cond";
        $sql.= ", priority";
		$sql.= ", active";
        $sql.= " FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " WHERE ((date_start <= '" . $this->db->idate(dol_now()) . "' AND date_end >= '" . $this->db->idate(dol_now()) . "') OR (date_start IS NULL AND date_end IS NULL)";
		$sql .= " OR (date_start <= '" . $this->db->idate(dol_now()) . "' AND date_end IS NULL) OR (date_start IS NULL AND date_end >= '" . $this->db->idate(dol_now()) . "')) AND";
        if ($type == 3){//By priority
            $sql .= " ((fk_source = " . $fk_soc . " AND type_source = " . Discounts::SOURCE_THIRD . ")";
            $sql .= " OR (fk_target = " . $fk_soc . " AND type_target = " . Discounts::SOURCE_THIRD . ")";
            $sql .= " OR (fk_source = " . $fk_product . " AND type_source = " . Discounts::SOURCE_PRODUCT . ")";
            $sql .= " OR (fk_target = " . $fk_product . " AND type_target = " . Discounts::SOURCE_PRODUCT . ")";
			$sql .= " OR (fk_source = " . $id . " AND type_source = " . Discounts::SOURCE_INVOICE . ")";
			$sql .= " OR (fk_target = " . $id . " AND type_target = " . Discounts::SOURCE_INVOICE . ")";

            $category_static = new Categorie($this->db);
            $list_third = implode(',', $category_static->containing($fk_soc, 'customer', 'id'));
            $list_prod = implode(',', $category_static->containing($fk_product, 'product', 'id'));

            if (!empty($list_third)) {
                $sql .= " OR (fk_source IN (" . $list_third . ") AND type_source = " . Discounts::SOURCE_CATEGORY . ")";
                $sql .= " OR (fk_target IN (" . $list_third . ") AND type_target = " . Discounts::SOURCE_CATEGORY . ")";
            }
            if (!empty($list_prod)) {
                $sql .= " OR (fk_source IN (" . $list_prod . ") AND type_source = " . Discounts::SOURCE_CATEGORY . ")";
                $sql .= " OR (fk_target IN (" . $list_prod . ") AND type_target = " . Discounts::SOURCE_CATEGORY . ")";
            }
            $sql .= ") AND entity = " . $conf->entity;
            $sql .= " ORDER BY priority ASC";
        }
        else {//All
            $sql .= " ((fk_source = " . $fk_soc . " AND type_source = " . Discounts::SOURCE_THIRD . ")";
            $sql .= " OR (fk_target = " . $fk_soc . " AND type_target = " . Discounts::SOURCE_THIRD . ")";
            $sql .= " OR (fk_source = " . $fk_product . " AND type_source = " . Discounts::SOURCE_PRODUCT . ")";
            $sql .= " OR (fk_target = " . $fk_product . " AND type_target = " . Discounts::SOURCE_PRODUCT . ")";
			$sql .= " OR (fk_source = " . $id . " AND type_source = " . Discounts::SOURCE_INVOICE . ")";
			$sql .= " OR (fk_target = " . $id . " AND type_target = " . Discounts::SOURCE_INVOICE . ")";

            $category_static = new Categorie($this->db);
            $list_third = implode(',', $category_static->containing($fk_soc, 'customer', 'id'));
            $list_prod = implode(',', $category_static->containing($fk_product, 'product', 'id'));

            if (!empty($list_third)) {
                $sql .= " OR (fk_source IN (" . $list_third . ") AND type_source = " . Discounts::SOURCE_CATEGORY . ")";
                $sql .= " OR (fk_target IN (" . $list_third . ") AND type_target = " . Discounts::SOURCE_CATEGORY . ")";
            }
            if (!empty($list_prod)) {
                $sql .= " OR (fk_source IN (" . $list_prod . ") AND type_source = " . Discounts::SOURCE_CATEGORY . ")";
                $sql .= " OR (fk_target IN (" . $list_prod . ") AND type_target = " . Discounts::SOURCE_CATEGORY . ")";
            }
            $sql .= ") AND entity = " . $conf->entity;
            $sql .= " ORDER BY description ASC";
        }

        dol_syslog(get_class($this)."::fetchall sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $j=0;
            $num = $this->db->num_rows($resql);
            $result = null;
            while ($obj = $this->db->fetch_object($resql))
            {
                if ($obj->active==1)
                {
                    //si fuente es tercero y destino producto
                    if ($obj->type_source == Discounts::SOURCE_THIRD && $obj->type_target == Discounts::SOURCE_PRODUCT){
                        if ($obj->fk_source != $fk_soc || $obj->fk_target != $fk_product){
                            continue;
                        }
                    }

                    //si fuente es product y destino tercero
                    if ($obj->type_source == Discounts::SOURCE_PRODUCT && $obj->type_target == Discounts::SOURCE_THIRD){
                        if ($obj->fk_source != $fk_product || $obj->fk_target != $fk_soc){
                            continue;
                        }
                    }

					if ($obj->type_source == Discounts::SOURCE_INVOICE && $obj->type_target == null){
						if ($obj->fk_source != $id){
							continue;
						}
					}

                    //si fuente es tercero y destino categoría, mira si el producto está en la categoría destino
                    if ($obj->type_source == Discounts::SOURCE_THIRD && $obj->type_target == Discounts::SOURCE_CATEGORY){
                        if ($obj->fk_source != $fk_soc){
                            continue;
                        }
                        $cat = new Categorie($this->db);
                        $cat->fetch($obj->fk_target);
                        $nb = $cat->containsObject("product", $fk_product);
                        if (! $nb > 0){
                            continue;
                        }
                    }
                    //si fuente es producto y destino categoría, mira si el tercero está en la categoría destino
                    else if ($obj->type_source == Discounts::SOURCE_PRODUCT && $obj->type_target == Discounts::SOURCE_CATEGORY){
                        if ($obj->fk_source != $fk_product){
                            continue;
                        }
                        $cat = new Categorie($this->db);
                        $cat->fetch($obj->fk_target);
                        $nb = $cat->containsObject("customer", $fk_soc);
                        if (! $nb > 0){
                            continue;
                        }
                    }
                    //si fuente es categoría y destino tercero, mira si el producto está en la categoría fuente
                    else if ($obj->type_source == Discounts::SOURCE_CATEGORY && $obj->type_target == Discounts::SOURCE_THIRD){
                        if ($obj->fk_target != $fk_soc){
                            continue;
                        }
                        $cat = new Categorie($this->db);
                        $cat->fetch($obj->fk_source);
                        $nb = $cat->containsObject("product", $fk_product);
                        if (! $nb > 0){
                            continue;
                        }
                    }
                    //si fuente es categoría y destino product, mira si el tercero está en la categoría fuente
                    else if ($obj->type_source == Discounts::SOURCE_CATEGORY && $obj->type_target == Discounts::SOURCE_PRODUCT){
                        if ($obj->fk_target != $fk_product){
                            continue;
                        }
                        $cat = new Categorie($this->db);
                        $cat->fetch($obj->fk_source);
                        $nb = $cat->containsObject("customer", $fk_soc);
                        if (! $nb > 0){
                            continue;
                        }
                    }
                    //si fuente es categoría y destino categoría, mira si los respectivos están en sus categorías
                    else if ($obj->type_source == Discounts::SOURCE_CATEGORY && $obj->type_target == Discounts::SOURCE_CATEGORY) {
                        $cat = new Categorie($this->db);
                        $cat->fetch($obj->fk_source);
                        if ($cat->type == 0) {
                            $nb = $cat->containsObject("product", $fk_product);
                            if (!$nb > 0) {
                                continue;
                            }
                            $cat->fetch($obj->fk_target);
                            $nb = $cat->containsObject("customer", $fk_soc);
                            if (!$nb > 0) {
                                continue;
                            }
                        }
                        else if($cat->type == 2){
                            $nb = $cat->containsObject("customer", $fk_soc);
                            if (!$nb > 0) {
                                continue;
                            }
                            $cat->fetch($obj->fk_target);
                            $nb = $cat->containsObject("product", $fk_product);
                            if (!$nb > 0) {
                                continue;
                            }
                        }
                    }

                    $result[$j]['id'] 	= $obj->rowid;
                    $result[$j]['type_dto']  = $obj->type_dto;
                    $result[$j]['type_source'] = $obj->type_source;
                    $result[$j]['fk_source'] = $obj->fk_source;
                    $result[$j]['desc'] = $obj->description;
                    $result[$j]['dto_rate'] = $obj->dto_rate;
                    $result[$j]['date_start'] = $obj->date_start;
                    $result[$j]['date_end'] = $obj->date_end;
                    $result[$j]['datec'] = $obj->datec;
                    $result[$j]['fk_user_author'] = $obj->fk_user_author;
                    $result[$j]['fk_target'] = $obj->fk_target;
                    $result[$j]['type_target'] = $obj->type_target;
                    $result[$j]['qtybuy'] = $obj->qtybuy;
                    $result[$j]['qtypay'] = $obj->qtypay;
                    $result[$j]['payment_cond'] = $obj->payment_cond;
                    $result[$j]['priority'] = $obj->priority;

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
    }

	
	/**
	 *	Delete
	 *
	 *	@param      User	$user			Object user who delete
	 *	@param		int		$notrigger		Disable trigger
	 *	@return		int						<0 if KO, >0 if OK
	 */
	public function delete($user, $notrigger=0)
	{
		$this->db->begin();

		$sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql.= " WHERE rowid = ".$this->id;

		dol_syslog(get_class($this)."::delete sql=".$sql);
		
		if ( $this->db->query($sql) )
		{
			$this->db->commit();
			return 1;
			
		}
		else
		{
			$this->error=$this->db->lasterror();
			$this->db->rollback();
			return -1;
		}
	}
	
 	public function select_type($selected='',$htmlname='type',$showempty=0)
    {
        global $langs;

        $out='';
        if($selected=='') $selected=-1;
		$out.= '<select class="flat" id="'.$htmlname.'" name="'.$htmlname.'">';
		if ($showempty)
		{
			$out.=  '<option value="-1"';
			if ($selected == -1) $out.=  ' selected="selected"';
			$out.=  '>&nbsp;</option>';
		}

		$out.=  '<option value="'.self::DTO_COMM.'"';
		if (self::DTO_COMM == $selected) $out.=  ' selected="selected"';
		$out.=  '>'.$langs->trans("Commercial");

		/*
		$out.=  '<option value="2"';
		if (2 == $selected) $out.=  ' selected="selected"';
		$out.=  '>'.$langs->trans("Financial");
		
		$out.=  '<option value="3"';
		if (3 == $selected) $out.=  ' selected="selected"';
		$out.=  '>'.$langs->trans("Time");*/
		
		$out.=  '<option value="'.self::DTO_BUYXPAYY.'"';
		if (self::DTO_BUYXPAYY == $selected) $out.=  ' selected="selected"';
		$out.=  '>'.$this->getLabelTypeDto(self::DTO_BUYXPAYY);
		
		$out.=  '<option value="'.self::DTO_UNIT2DTO.'"';
		if (self::DTO_UNIT2DTO == $selected) $out.=  ' selected="selected"';
		$out.=  '>'.$this->getLabelTypeDto(self::DTO_UNIT2DTO);

		$out.=  '</select>';
            //if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
        return $out;
    }
    
    public function getLabelTypeDto($type)
    {
        global $langs;

        return $langs->trans('type_dto'.$type);
    }

	public function Check_ifExists($type_source, $fk_source, $type_dto = self::DTO_COMM, $dto = 0,$fk_target=0,$type_target=0,$modeupdate=false, $iddto=0)
	{
		global $conf;

		$sql = "SELECT";
		if ($type_dto == self::DTO_COMM) {
			$sql .= " sum(dto_rate) as dtototal";
		} else {
			$sql .= " rowid";
		}
		$sql .= " FROM " . MAIN_DB_PREFIX . $this->table_element;
		$sql .= " WHERE type_dto = " . $type_dto;
		$sql .= " AND type_source = " . $type_source;
		$sql .= " AND fk_source = " . $fk_source;
		if ($fk_target>0) {
            $sql .= " AND fk_target = " . $fk_target;
            $sql .= " AND type_target =" . $type_target;
        }
		if($modeupdate==true) {
			$sql .= " AND rowid != ".$iddto;
		}
		$sql .= " AND entity = " . $conf->entity;

		dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);

		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				if ($type_dto == self::DTO_COMM) {
					$obj = $this->db->fetch_object($resql);
					if ($obj) {
						$dtototal = $obj->dtototal + $dto;
						if ($dtototal > 100) {
							return true;
						} else {
							return false;
						}
					} else {
						return false;
					}
				}
				else {
					return true;
				}
			} else {
				return false;
			}
		}
		else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(__METHOD__ . " " . $this->error, LOG_ERR);
			return -1;
		}
	}
}


class Discount_third extends CommonObject
{
    public $element='discount_thirdparty';
    public $table_element='discount_thirdparty';
    public $fk_element='fk_dto';

    public $id;

    public $entity;
    public $fk_soc;
    public $show_dis;

    const BASE = 1;
    const REMISE = 2;

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
        global $conf, $user, $langs;

        // Check parameters
        if (! is_numeric($this->show_dis)) $this->show_dis = 0;

        $this->db->begin();

        $sql = "INSERT INTO ".MAIN_DB_PREFIX.$this->table_element." (";
        $sql.= "entity";
        $sql.= ", fk_soc";
        $sql.= ", show_dis";
        $sql.= ") ";
        $sql.= " VALUES (";
        $sql.= $conf->entity;
        $sql.= ", ".$this->fk_soc;
        $sql.= ", ".$this->show_dis;
        $sql.= ")";

        dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $result=$this->db->query($sql);
        if ($result)
        {
            $this->id=$this->db->last_insert_id($this->table_element);

            $this->db->commit();
            return $this->id;

        }
        else
        {
            $this->error=$this->db->error();
            dol_syslog(get_class($this)."::create ".$this->error, LOG_ERR);
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

        global $conf, $user, $langs;

        // Check parameters
        if (! is_numeric($this->show_dis)) $this->show_dis = 0;

        $this->db->begin();

        $sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;

        $sql.= " SET ";

        $sql.= " show_dis='".$this->show_dis."'";
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
            $this->error=$this->db->error();
            dol_syslog(get_class($this)."::update ".$this->error, LOG_ERR);
            $this->db->rollback();
            return -1;
        }

    }

    /**
     *	Fetch a discount
     *
     *	@param		int		$rowid		Id of discount
     *	@return		int					<0 if KO, >0 if OK
     */
    public function fetch($rowid='',$fk_soc='')
    {
        global $conf;

        $sql = "SELECT rowid, entity,fk_soc,show_dis";
        $sql.= " FROM ".MAIN_DB_PREFIX.$this->table_element;
        if ($rowid > 0) {
            $sql .= " WHERE rowid=" . $rowid;
        }
        else if ($fk_soc > 0){
            $sql .= " WHERE fk_soc = " . $fk_soc;
        }
        $sql .= " AND entity = " . $conf->entity;

        dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id           = $obj->rowid;
                $this->entity		= $obj->entity;
                $this->fk_soc   	= $obj->fk_soc;
                $this->show_dis		= $obj->show_dis;

                return 1;
            }
            else{
                $this->show_dis = 0;
            }
        }
        else
        {
            $this->error=$this->db->error();
            dol_syslog(get_class($this)."::fetch ".$this->error,LOG_ERR);
            return -1;
        }
    }

    /**
     *	Delete
     *
     *	@param      User	$user			Object user who delete
     *	@param		int		$notrigger		Disable trigger
     *	@return		int						<0 if KO, >0 if OK
     */
    public function delete($user, $notrigger=0)
    {
        $this->db->begin();

        $sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->table_element;
        $sql.= " WHERE rowid = ".$this->id;

        dol_syslog(get_class($this)."::delete sql=".$sql);

        if ( $this->db->query($sql) )
        {
            $this->db->commit();
            return 1;

        }
        else
        {
            $this->error=$this->db->lasterror();
            $this->db->rollback();
            return -1;
        }
    }

}
