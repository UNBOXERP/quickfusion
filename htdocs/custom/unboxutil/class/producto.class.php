<?php

require_once(DOL_DOCUMENT_ROOT . '/product/class/product.class.php');

class producto extends Product
{
    public function fetch_optional($optionsfilter = "")
    {
        // phpcs:enable
        global $conf, $extrafields;
        $sql = "SELECT rowid,fk_object ";
        $sql .= " FROM " . MAIN_DB_PREFIX . "product_extrafields";
        $sql .= " WHERE " . $optionsfilter . " limit 1";

        $resql = $this->db->query($sql);
        if ($resql) {
            $numrows = $this->db->num_rows($resql);
            if ($numrows) {
                $tab = $this->db->fetch_array($resql);

            }

            $this->db->free($resql);

            if ($numrows) return $tab["fk_object"];
            else return -1;
        } else {
            dol_print_error($this->db);
            return -1;
        }

        return 0;
    }

    public function getstock_bywarehouse($idwarehouse, $idproducto)
    {
        $sql = "SELECT e.rowid, e.ref, e.lieu, e.fk_parent, e.statut, ps.reel, ps.rowid as product_stock_id,";
        $sql .= "p.pmp FROM llx_entrepot as e, llx_product_stock as ps LEFT JOIN llx_product as p ON p.rowid = ps.fk_product";
        $sql .= " WHERE ps.reel != 0 AND ps.fk_entrepot = e.rowid AND e.entity";
        $sql .= " IN (" . getEntity('product') . ") AND ps.fk_product = " . $idproducto . " AND e.rowid=".$idwarehouse." ORDER BY e.ref limit 1";
        $result = $this->db->query($sql);
        if ($result) {
            $num = $this->db->num_rows($result);
            $i = 0;
            while ($i < $num) {
                $obj = $this->db->fetch_object($result);
                return $obj;
            }
        }

    }
    public function setCategoriefromName($categorieName,$idproduct){
        global $db;
        $sql="Select rowid from ".MAIN_DB_PREFIX."categorie where label='".$categorieName."' limit 1";
        $result = $db->query($sql);
        if ($result) {
            $num = $db->num_rows($result);
            $i = 0;
            while ($i < $num) {
                $obj = $db->fetch_object($result);
                $id = $obj->rowid;
                $i++;
            }
        }
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."categorie_product WHERE fk_categorie = ".$id." AND fk_product = ".$idproduct.";
        INSERT INTO ".MAIN_DB_PREFIX."categorie_product (fk_categorie, fk_product) VALUES (".$id.", ".$idproduct.")" ;
        $result = $db->query($sql);

    }
    public function correct_stock($user, $id_entrepot, $nbpiece, $movement, $label = '', $price = 0, $inventorycode = '', $origin_element = '', $origin_id = null, $disablestockchangeforsubproduct = 0,$isfather=false,$batch='')
    {
        // phpcs:enable
        if ($id_entrepot) {

            include_once DOL_DOCUMENT_ROOT.'/product/stock/class/mouvementstock.class.php';

            $op[0] = "+".trim($nbpiece);
            $op[1] = "-".trim($nbpiece);

            $movementstock = new MouvementStock($this->db);
            $movementstock->setOrigin($origin_element, $origin_id); // Set ->origin and ->origin->id
            //$result = $this->_create($user, $this->id, $id_entrepot, $op[$movement], $movement, $price, $label, $inventorycode, '', '', '', '', false, 0, $disablestockchangeforsubproduct,$isfather);
//            $result = $movementstock->_create($user, $this->id, $id_entrepot, $op[$movement], $movement, $price, $label, $inventorycode, '', '', '', '', false, 0, $disablestockchangeforsubproduct,$isfather);
            $result = $movementstock->_create($user, $this->id, $id_entrepot, $op[$movement], $movement, $price, $label, $inventorycode, '', '', '', $batch, false, 0, $disablestockchangeforsubproduct,$isfather);

        }
    }
    public function _create($user, $fk_product, $entrepot_id, $qty, $type, $price = 0, $label = '', $inventorycode = '', $datem = '', $eatby = '', $sellby = '', $batch = '', $skip_batch = false, $id_product_batch = 0, $disablestockchangeforsubproduct = 0,$isfather=false)
    {
        // phpcs:disable
        global $conf, $langs;

        require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
        require_once DOL_DOCUMENT_ROOT.'/product/stock/class/productlot.class.php';

        $error = 0;
        dol_syslog(get_class($this)."::_create start userid=$user->id, fk_product=$fk_product, warehouse_id=$entrepot_id, qty=$qty, type=$type, price=$price, label=$label, inventorycode=$inventorycode, datem=".$datem.", eatby=".$eatby.", sellby=".$sellby.", batch=".$batch.", skip_batch=".$skip_batch);

        // Clean parameters
        $price = price2num($price, 'MU'); // Clean value for the casse we receive a float zero value, to have it a real zero value.
        if (empty($price)) $price = 0;
        $now = (!empty($datem) ? $datem : dol_now());

        // Check parameters
        if (empty($fk_product)) return 0;

        if (is_numeric($eatby) && $eatby < 0) {
            dol_syslog(get_class($this)."::_create start ErrorBadValueForParameterEatBy eatby = ".$eatby);
            $this->errors[] = 'ErrorBadValueForParameterEatBy';
            return -1;
        }
        if (is_numeric($sellby) && $sellby < 0) {
            dol_syslog(get_class($this)."::_create start ErrorBadValueForParameterSellBy sellby = ".$sellby);
            $this->errors[] = 'ErrorBadValueForParameterSellBy';
            return -1;
        }

        // Set properties of movement
        $this->product_id = $fk_product;
        $this->entrepot_id = $entrepot_id; // deprecated
        $this->warehouse_id = $entrepot_id;
        $this->qty = $qty;
        $this->type = $type;
        $this->price = price2num($price);
        $this->label = $label;
        $this->inventorycode = $inventorycode;
        $this->datem = $now;
        $this->batch = $batch;

        $mvid = 0;

        $product = new Product($this->db);

        $result = $product->fetch($fk_product);
        if ($result < 0) {
            $this->error = $product->error;
            $this->errors = $product->errors;
            dol_print_error('', "Failed to fetch product");
            return -1;
        }
        if ($product->id <= 0) {	// Can happen if database is corrupted
            return 0;
        }

        $this->db->begin();

        $product->load_stock('novirtual');

        // Test if product require batch data. If yes, and there is not, we throw an error.
        if (!empty($conf->productbatch->enabled) && $product->hasbatch() && !$skip_batch)
        {
            if (empty($product->status_batch) || $product->status_batch=="0")
            {
                if(!$isfather){
                    $langs->load("errors");
                    $this->errors[] = $langs->transnoentitiesnoconv("ErrorTryToMakeMoveOnProductRequiringBatchData", $product->ref);
                    dol_syslog("Try to make a movement of a product with status_batch on without any batch data");

                    $this->db->rollback();
                    return -2;
                }
            }

            // Check table llx_product_lot from batchnumber for same product
            // If found and eatby/sellby defined into table and provided and differs, return error
            // If found and eatby/sellby defined into table and not provided, we take value from table
            // If found and eatby/sellby not defined into table and provided, we update table
            // If found and eatby/sellby not defined into table and not provided, we do nothing
            // If not found, we add record
            $sql = "SELECT pb.rowid, pb.batch, pb.eatby, pb.sellby FROM ".MAIN_DB_PREFIX."product_lot as pb";
            $sql .= " WHERE pb.fk_product = ".$fk_product." AND pb.batch = '".$this->db->escape($batch)."'";
            dol_syslog(get_class($this)."::_create scan serial for this product to check if eatby and sellby match", LOG_DEBUG);
            $resql = $this->db->query($sql);
            if ($resql)
            {
                $num = $this->db->num_rows($resql);
                $i = 0;
                if ($num > 0)
                {
                    while ($i < $num)
                    {
                        $obj = $this->db->fetch_object($resql);
                        if ($obj->eatby)
                        {
                            if ($eatby)
                            {
                                $tmparray = dol_getdate($eatby, true);
                                $eatbywithouthour = dol_mktime(0, 0, 0, $tmparray['mon'], $tmparray['mday'], $tmparray['year']);
                                if ($this->db->jdate($obj->eatby) != $eatby && $this->db->jdate($obj->eatby) != $eatbywithouthour)    // We test date without hours and with hours for backward compatibility
                                {
                                    // If found and eatby/sellby defined into table and provided and differs, return error
                                    $langs->load("stocks");
                                    $this->errors[] = $langs->transnoentitiesnoconv("ThisSerialAlreadyExistWithDifferentDate", $batch, dol_print_date($this->db->jdate($obj->eatby), 'dayhour'), dol_print_date($eatbywithouthour, 'dayhour'));
                                    dol_syslog("ThisSerialAlreadyExistWithDifferentDate batch=".$batch.", eatby found into product_lot = ".$obj->eatby." = ".dol_print_date($this->db->jdate($obj->eatby), 'dayhourrfc')." so eatbywithouthour = ".$eatbywithouthour." = ".dol_print_date($eatbywithouthour)." - eatby provided = ".$eatby." = ".dol_print_date($eatby, 'dayhourrfc'), LOG_ERR);
                                    $this->db->rollback();
                                    return -3;
                                }
                            } else {
                                $eatby = $obj->eatby; // If found and eatby/sellby defined into table and not provided, we take value from table
                            }
                        } else {
                            if ($eatby) // If found and eatby/sellby not defined into table and provided, we update table
                            {
                                $productlot = new Productlot($this->db);
                                $result = $productlot->fetch($obj->rowid);
                                $productlot->eatby = $eatby;
                                $result = $productlot->update($user);
                                if ($result <= 0)
                                {
                                    $this->error = $productlot->error;
                                    $this->errors = $productlot->errors;
                                    $this->db->rollback();
                                    return -5;
                                }
                            }
                        }
                        if ($obj->sellby)
                        {
                            if ($sellby)
                            {
                                $tmparray = dol_getdate($sellby, true);
                                $sellbywithouthour = dol_mktime(0, 0, 0, $tmparray['mon'], $tmparray['mday'], $tmparray['year']);
                                if ($this->db->jdate($obj->sellby) != $sellby && $this->db->jdate($obj->sellby) != $sellbywithouthour)    // We test date without hours and with hours for backward compatibility
                                {
                                    // If found and eatby/sellby defined into table and provided and differs, return error
                                    $this->errors[] = $langs->transnoentitiesnoconv("ThisSerialAlreadyExistWithDifferentDate", $batch, dol_print_date($this->db->jdate($obj->sellby)), dol_print_date($sellby));
                                    dol_syslog($langs->transnoentities("ThisSerialAlreadyExistWithDifferentDate", $batch, dol_print_date($this->db->jdate($obj->sellby)), dol_print_date($sellby)), LOG_ERR);
                                    $this->db->rollback();
                                    return -3;
                                }
                            } else {
                                $sellby = $obj->sellby; // If found and eatby/sellby defined into table and not provided, we take value from table
                            }
                        }
                        else
                        {
                            if ($sellby) // If found and eatby/sellby not defined into table and provided, we update table
                            {
                                $productlot = new Productlot($this->db);
                                $result = $productlot->fetch($obj->rowid);
                                $productlot->sellby = $sellby;
                                $result = $productlot->update($user);
                                if ($result <= 0)
                                {
                                    $this->error = $productlot->error;
                                    $this->errors = $productlot->errors;
                                    $this->db->rollback();
                                    return -5;
                                }
                            }
                        }

                        $i++;
                    }
                }
                else   // If not found, we add record
                {
                    $productlot = new Productlot($this->db);
                    $productlot->entity = $conf->entity;
                    $productlot->fk_product = $fk_product;
                    $productlot->batch = $batch;
                    // If we are here = first time we manage this batch, so we used dates provided by users to create lot
                    $productlot->eatby = $eatby;
                    $productlot->sellby = $sellby;
                    $result = $productlot->create($user);
                    if ($result <= 0)
                    {
                        $this->error = $productlot->error;
                        $this->errors = $productlot->errors;
                        $this->db->rollback();
                        return -4;
                    }
                }
            }
            else
            {
                dol_print_error($this->db);
                $this->db->rollback();
                return -1;
            }
        }

        // Define if we must make the stock change (If product type is a service or if stock is used also for services)
        $movestock = 0;
        if ($product->type != Product::TYPE_SERVICE || !empty($conf->global->STOCK_SUPPORTS_SERVICES)) $movestock = 1;

        // Check if stock is enough when qty is < 0
        // Note that qty should be > 0 with type 0 or 3, < 0 with type 1 or 2.
        if ($movestock && $qty < 0 && empty($conf->global->STOCK_ALLOW_NEGATIVE_TRANSFER))
        {
            if (!empty($conf->productbatch->enabled) && $product->hasbatch() && !$skip_batch)
            {
                $foundforbatch = 0;
                $qtyisnotenough = 0;
                foreach ($product->stock_warehouse[$entrepot_id]->detail_batch as $batchcursor => $prodbatch)
                {
                    if ((string) $batch != (string) $batchcursor) {		// Lot '59' must be different than lot '59c'
                        continue;
                    }
                    $foundforbatch = 1;
                    if ($prodbatch->qty < abs($qty)) $qtyisnotenough = $prodbatch->qty;
                    break;
                }
                if (!$foundforbatch || $qtyisnotenough)
                {
                    $langs->load("stocks");
                    include_once DOL_DOCUMENT_ROOT.'/product/stock/class/entrepot.class.php';
                    $tmpwarehouse = new Entrepot($this->db);
                    $tmpwarehouse->fetch($entrepot_id);

                    $this->error = $langs->trans('qtyToTranferLotIsNotEnough', $product->ref, $batch, $qtyisnotenough, $tmpwarehouse->ref);
                    $this->errors[] = $langs->trans('qtyToTranferLotIsNotEnough', $product->ref, $batch, $qtyisnotenough, $tmpwarehouse->ref);
                    $this->db->rollback();
                    return -8;
                }
            }
            else
            {
                if (empty($product->stock_warehouse[$entrepot_id]->real) || $product->stock_warehouse[$entrepot_id]->real < abs($qty))
                {
                    $langs->load("stocks");
                    $this->error = $langs->trans('qtyToTranferIsNotEnough').' : '.$product->ref;
                    $this->errors[] = $langs->trans('qtyToTranferIsNotEnough').' : '.$product->ref;
                    $this->db->rollback();
                    return -8;
                }
            }
        }

        if ($movestock && $entrepot_id > 0)	// Change stock for current product, change for subproduct is done after
        {
            // Set $origintype, fk_origin, fk_project
            $fk_project = 0;
            if (!empty($this->origin)) {			// This is set by caller for tracking reason
                $origintype = empty($this->origin->origin_type) ? $this->origin->element : $this->origin->origin_type;
                $fk_origin = $this->origin->id;
                if ($origintype == 'project') {
                    $fk_project = $fk_origin;
                } else {
                    $res = $this->origin->fetch($fk_origin);
                    if ($res > 0)
                    {
                        if (!empty($this->origin->fk_project))
                        {
                            $fk_project = $this->origin->fk_project;
                        }
                    }
                }
            } else {
                $origintype = '';
                $fk_origin = 0;
                $fk_project = 0;
            }

            $sql = "INSERT INTO ".MAIN_DB_PREFIX."stock_mouvement(";
            $sql .= " datem, fk_product, batch, eatby, sellby,";
            $sql .= " fk_entrepot, value, type_mouvement, fk_user_author, label, inventorycode, price, fk_origin, origintype, fk_projet";
            $sql .= ")";
            $sql .= " VALUES ('".$this->db->idate($now)."', ".$this->product_id.", ";
            $sql .= " ".($batch ? "'".$this->db->escape($batch)."'" : "null").", ";
            $sql .= " ".($eatby ? "'".$this->db->idate($eatby)."'" : "null").", ";
            $sql .= " ".($sellby ? "'".$this->db->idate($sellby)."'" : "null").", ";
            $sql .= " ".$this->entrepot_id.", ".$this->qty.", ".((int) $this->type).",";
            $sql .= " ".$user->id.",";
            $sql .= " '".$this->db->escape($label)."',";
            $sql .= " ".($inventorycode ? "'".$this->db->escape($inventorycode)."'" : "null").",";
            $sql .= " ".price2num($price).",";
            $sql .= " ".$fk_origin.",";
            $sql .= " '".$this->db->escape($origintype)."',";
            $sql .= " ".$fk_project;
            $sql .= ")";

            dol_syslog(get_class($this)."::_create insert record into stock_mouvement", LOG_DEBUG);
            $resql = $this->db->query($sql);

            if ($resql)
            {
                $mvid = $this->db->last_insert_id(MAIN_DB_PREFIX."stock_mouvement");
                $this->id = $mvid;
            }
            else
            {
                $this->error = $this->db->lasterror();
                $this->errors[] = $this->error;
                $error = -1;
            }

            // Define current values for qty and pmp
            $oldqty = $product->stock_reel;
            $oldpmp = $product->pmp;
            $oldqtywarehouse = 0;

            // Test if there is already a record for couple (warehouse / product), so later we will make an update or create.
            $alreadyarecord = 0;
            if (!$error)
            {
                $sql = "SELECT rowid, reel FROM ".MAIN_DB_PREFIX."product_stock";
                $sql .= " WHERE fk_entrepot = ".$entrepot_id." AND fk_product = ".$fk_product; // This is a unique key

                dol_syslog(get_class($this)."::_create check if a record already exists in product_stock", LOG_DEBUG);
                $resql = $this->db->query($sql);
                if ($resql)
                {
                    $obj = $this->db->fetch_object($resql);
                    if ($obj)
                    {
                        $alreadyarecord = 1;
                        $oldqtywarehouse = $obj->reel;
                        $fk_product_stock = $obj->rowid;
                    }
                    $this->db->free($resql);
                } else {
                    $this->errors[] = $this->db->lasterror();
                    $error = -2;
                }
            }

            // Calculate new AWP (PMP)
            $newpmp = 0;
            if (!$error)
            {
                if ($type == 0 || $type == 3)
                {
                    // After a stock increase
                    // Note: PMP is calculated on stock input only (type of movement = 0 or 3). If type == 0 or 3, qty should be > 0.
                    // Note: Price should always be >0 or 0. PMP should be always >0 (calculated on input)
                    if ($price > 0 || (!empty($conf->global->STOCK_UPDATE_AWP_EVEN_WHEN_ENTRY_PRICE_IS_NULL) && $price == 0)) {
                        $oldqtytouse = ($oldqty >= 0 ? $oldqty : 0);
                        // We make a test on oldpmp>0 to avoid to use normal rule on old data with no pmp field defined
                        if ($oldpmp > 0) {
                            $newpmp = price2num((($oldqtytouse * $oldpmp) + ($qty * $price)) / ($oldqtytouse + $qty), 'MU');
                        } else {
                            $newpmp = $price; // For this product, PMP was not yet set. We set it to input price.
                        }
                        //print "oldqtytouse=".$oldqtytouse." oldpmp=".$oldpmp." oldqtywarehousetouse=".$oldqtywarehousetouse." ";
                        //print "qty=".$qty." newpmp=".$newpmp;
                        //exit;
                    } else {
                        $newpmp = $oldpmp;
                    }
                } elseif ($type == 1 || $type == 2) {
                    // After a stock decrease, we don't change value of the AWP/PMP of a product.
                    $newpmp = $oldpmp;
                } else {
                    // Type of movement unknown
                    $newpmp = $oldpmp;
                }
            }
            // Update stock quantity
            if (!$error)
            {
                if ($alreadyarecord > 0)
                {
                    $sql = "UPDATE ".MAIN_DB_PREFIX."product_stock SET reel = reel + ".$qty;
                    $sql .= " WHERE fk_entrepot = ".$entrepot_id." AND fk_product = ".$fk_product;
                } else {
                    $sql = "INSERT INTO ".MAIN_DB_PREFIX."product_stock";
                    $sql .= " (reel, fk_entrepot, fk_product) VALUES ";
                    $sql .= " (".$qty.", ".$entrepot_id.", ".$fk_product.")";
                }

                dol_syslog(get_class($this)."::_create update stock value", LOG_DEBUG);
                $resql = $this->db->query($sql);
                if (!$resql)
                {
                    $this->errors[] = $this->db->lasterror();
                    $error = -3;
                }
                elseif (empty($fk_product_stock))
                {
                    $fk_product_stock = $this->db->last_insert_id(MAIN_DB_PREFIX."product_stock");
                }
            }

            // Update detail stock for batch product
            if (!$error && !empty($conf->productbatch->enabled) && $product->hasbatch() && !$skip_batch)
            {
                if ($id_product_batch > 0)
                {
                    $result = $this->createBatch($id_product_batch, $qty);
                } else {
                    $param_batch = array('fk_product_stock' =>$fk_product_stock, 'batchnumber'=>$batch);
                    $result = $this->createBatch($param_batch, $qty);
                }
                if ($result < 0) $error++;
            }

            // Update PMP and denormalized value of stock qty at product level
            if (!$error)
            {
                $newpmp = price2num($newpmp, 'MU');

                // $sql = "UPDATE ".MAIN_DB_PREFIX."product SET pmp = ".$newpmp.", stock = ".$this->db->ifsql("stock IS NULL", 0, "stock") . " + ".$qty;
                // $sql.= " WHERE rowid = ".$fk_product;
                // Update pmp + denormalized fields because we change content of produt_stock. Warning: Do not use "SET p.stock", does not works with pgsql
                $sql = "UPDATE ".MAIN_DB_PREFIX."product as p SET pmp = ".$newpmp.",";
                $sql .= " stock=(SELECT SUM(ps.reel) FROM ".MAIN_DB_PREFIX."product_stock as ps WHERE ps.fk_product = p.rowid)";
                $sql .= " WHERE rowid = ".$fk_product;

                dol_syslog(get_class($this)."::_create update AWP", LOG_DEBUG);
                $resql = $this->db->query($sql);
                if (!$resql)
                {
                    $this->errors[] = $this->db->lasterror();
                    $error = -4;
                }
            }

            // If stock is now 0, we can remove entry into llx_product_stock, but only if there is no child lines into llx_product_batch (detail of batch, because we can imagine
            // having a lot1/qty=X and lot2/qty=-X, so 0 but we must not loose repartition of different lot.
            $sql = "DELETE FROM ".MAIN_DB_PREFIX."product_stock WHERE reel = 0 AND rowid NOT IN (SELECT fk_product_stock FROM ".MAIN_DB_PREFIX."product_batch as pb)";
            $resql = $this->db->query($sql);
            // We do not test error, it can fails if there is child in batch details
        }

        // Add movement for sub products (recursive call)
        if (!$error && !empty($conf->global->PRODUIT_SOUSPRODUITS) && empty($conf->global->INDEPENDANT_SUBPRODUCT_STOCK) && empty($disablestockchangeforsubproduct))
        {
            if (!$isfather){
                $error = $this->_createSubProduct($user, $fk_product, $entrepot_id, $qty, $type, 0, $label, $inventorycode); // we use 0 as price, because AWP must not change for subproduct
            }
        }

        if ($movestock && !$error)
        {
            // Call trigger
            $this->db->commit();
            $result = $this->call_trigger('STOCK_MOVEMENT', $user);
            if ($result < 0) $error++;
            // End call triggers
        }

        if (!$error)
        {
            $this->db->commit();
            return $mvid;
        }
        else
        {
            $this->db->rollback();
            dol_syslog(get_class($this)."::_create error code=".$error, LOG_ERR);
            return -6;
        }
    }


}
