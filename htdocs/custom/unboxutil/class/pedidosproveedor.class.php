<?php
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.commande.class.php';
class pedidosproveedor extends CommandeFournisseur
{
 public function Get_PositionsFromProject($project)
 {
     $sql = "SELECT  rowid FROM " . MAIN_DB_PREFIX . "commande_fournisseur where fk_projet = " . $project;
     $result = $this->db->query($sql);
     if ($result) {
         $num = $this->db->num_rows($result);
         $i = 0;
         $commandes=array();
         while ($i < $num) {
             $obj = $this->db->fetch_object($result);
             $commandes[] = $obj->rowid;
             $i++;
         }
     }
     $orden=array();
     if (!empty($commandes)) {
         $orden[]=  $this->get_positions($commandes);
        /* foreach ($commandes as $commande) {
             $orden[]= $this->get_positioncommande($commande);
         }*/
     }
     return $orden;
 }
 private function get_positioncommande($commande)
    {
        $sql = "SELECT fk_object,poposition FROM " . MAIN_DB_PREFIX . "commande_fournisseur_extrafields where fk_object = " . $commande . " order by poposition";
        $result = $this->db->query($sql);
        $position=array();
        if ($result) {
            $num = $this->db->num_rows($result);
            $i = 0;
            while ($i < $num) {
                $obj = $this->db->fetch_object($result);
                $position[] = array("fk_object"=>$obj->fk_object,"poposition"=>$obj->poposition);
                return $position;
                $i++;
            }
        }
    }
    private function get_positions($commande)
    {
        $sql = "SELECT fk_object,poposition FROM " . MAIN_DB_PREFIX . "commande_fournisseur_extrafields where fk_object in ( " . implode(",",$commande). ") order by poposition";
        $result = $this->db->query($sql);
        $position=array();
        if ($result) {
            $num = $this->db->num_rows($result);
            $i = 0;
            while ($i < $num) {
                $obj = $this->db->fetch_object($result);
                $position[] = array("fk_object"=>$obj->fk_object,"poposition"=>$obj->poposition);
                $i++;
            }
        }
        return $position;
    }
    public function get_fkcommande($commande)
    {
        $sql = "SELECT  rowid FROM " . MAIN_DB_PREFIX . "commande_fournisseur where fk_projet = " . $commande;
//        $sql = "SELECT fk_object FROM " . MAIN_DB_PREFIX . "commande_fournisseur_extrafields where fk_object = " . $commande . " order by fk_object";
        $result = $this->db->query($sql);
        if ($result) {
            $num = $this->db->num_rows($result);
            $i = 0;
            while ($i < $num) {
                $obj = $this->db->fetch_object($result);
                $fkcommande[] = (int)$obj->rowid;
                $i++;
            }

            $orden=array();
            if (!empty($fkcommande)) {
                foreach ($fkcommande as $commande) {
                    $orden[]= $this->get_extrafield_fk($commande);
                }
            }
            return $orden;

        }
    }
    public function get_extrafield_fk($commande){
        $sql = "SELECT fk_object FROM " . MAIN_DB_PREFIX . "commande_fournisseur_extrafields where fk_object = " . $commande . " order by fk_object";
        $result = $this->db->query($sql);
        if ($result) {
            $num = $this->db->num_rows($result);
            $i = 0;
            while ($i < $num) {
                $obj = $this->db->fetch_object($result);
                $extras[] = $obj->fk_object;
                $i++;
            }
        }
           return $extras;
    }
}