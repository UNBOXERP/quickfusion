<?php

/* Copyright (C) 2017-2019 Regis Houssin  <regis.houssin@inodbox.com>
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

class TablaOrdenada
{
// constructor
    function __construct($db)
    {
        $this->db = $db;
    }

    public function getTablaOrdenada()
    {
        //get orden field from mysql table query
        /*$sql = "SELECT `order` FROM ".MAIN_DB_PREFIX."commande_fournisseurs_project_order ";
        $res = $this->db->query($sql);
        if (!$res) {
            $this->errors[] = 'Error ' . $this->db->lasterror();
            dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
        }
        $orden = array();
        while ($obj = $this->db->fetch_object($res)) {
            $orden[] = $obj->order;
        }*/

        //TODO Razmi en lugar de extraer de project_order extraer del extrafields position
        $idproject = GETPOST('idproject');
        require_once DOL_DOCUMENT_ROOT . '/custom/unboxutil/class/pedidosproveedor.class.php';
        $miproject = new pedidosproveedor($this->db);
        $orden = $miproject->Get_PositionsFromProject($idproject);

        //return $orden json encoded
        echo json_encode($orden);
        //return $orden;

    }

    public function setTablaOrdenada($datos)
    {
        $datosformatted = substr($datos, 1, strlen($datos));
        $datosformatted = substr($datosformatted, 0, -1);
        $arreglodatos = array();
        $arreglodatos = explode(",", $datosformatted);
        $i = 0;
        $commande = array();
        $idproject = GETPOST('idproject');
        require_once DOL_DOCUMENT_ROOT . '/custom/unboxutil/class/pedidosproveedor.class.php';
        $micommande = new pedidosproveedor($this->db);
        $commande[] = $micommande->get_fkcommande($idproject);
        foreach ($arreglodatos as $arreglodato) {
            if ((int)$arreglodato > 0) {
                $arr=($commande[0][$arreglodato-1][0])??-1;
                $sql = "UPDATE " . MAIN_DB_PREFIX . "commande_fournisseur_extrafields SET poposition=" . $i . " WHERE fk_object=" . $arreglodato ;
                $res = $this->db->query($sql);
                if (!$res) {
                    $this->errors[] = 'Error ' . $this->db->lasterror();
                    dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
                }
            }
            $i++;
        }
        //$sql = "UPDATE " . MAIN_DB_PREFIX . "commande_fournisseurs_project_order SET  `order` = '" . $datos . "' where id=1";

        return $res;
    }

    public function getPaymentType($selecteds)
    {   //get payment type from mysql table query
        $sql = "SELECT `id`,`code`,`libelle`,`payment_type` FROM " . MAIN_DB_PREFIX . "c_paiement Where active=1";
        $res = $this->db->query($sql);
        if (!$res) {
            $this->errors[] = 'Error ' . $this->db->lasterror();
            dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
        }
        $payment_type = array();
        while ($obj = $this->db->fetch_object($res)) {
            $payment_type[] = $obj;
        }
        //return $payment_type json encoded
        echo json_encode($payment_type);
        //return $payment_type;

    }

    public function fixProjects()
    {
        //execute sql db query
        $sql = "UPDATE ".MAIN_DB_PREFIX."commande_fournisseur_extrafields lcfe SET lcfe.poposition=  (SELECT CAST(pc.label as SIGNED INTEGER) from ".MAIN_DB_PREFIX."pcat_pcat pc WHERE lcfe.qir_qirdata=pc.rowid  ) ";
        $res = $this->db->query($sql);
        echo json_encode('true');
    }

}

/**
 * Define all constants needed for ajax request
 */
if (!defined('NOTOKENRENEWAL')) {
    define('NOTOKENRENEWAL', 1);
} // Disables token renewal
if (!defined('NOREQUIREMENU')) {
    define('NOREQUIREMENU', '1');
}
if (!defined('NOREQUIREHTML')) {
    define('NOREQUIREHTML', '1');
}
if (!defined('NOREQUIREAJAX')) {
    define('NOREQUIREAJAX', '1');
}
if (!defined('NOREQUIRESOC')) {
    define('NOREQUIRESOC', '1');
}
if (!defined('NOCSRFCHECK')) {
    define('NOCSRFCHECK', '1');
}
if (empty($_GET ['keysearch']) && !defined('NOREQUIREHTML')) {
    define('NOREQUIREHTML', '1');
}

$res = 0;
if (!$res && file_exists("../../main.inc.php"))
    $res = @require("../../main.inc.php");        // For root directory
if (!$res && file_exists("../../../main.inc.php"))
    $res = @require("../../../main.inc.php");    // For "custom" directory

global $db;

header("HTTP/1.1 200 OK");

$accion = GETPOST('action');
$datos = GETPOST('tablaordenada');
$selecteds = array();
$selecteds = GETPOST('selecteds');

global $langs, $conf, $db;

switch ($accion) {
    case 'getorden':
        $activarProductos = new TablaOrdenada($db);
        $activarProductos->getTablaOrdenada();
        break;
    case 'setorden':
        $updateproducts = new TablaOrdenada($db);
        $updateproducts->setTablaOrdenada($datos);
        break;
    case 'getpaymenttype':
        $activarProductos = new TablaOrdenada($db);
        $activarProductos->getPaymentType($selecteds);
        break;
    case 'fixprojects':
        $fixprojects = new TablaOrdenada($db);
        $fixprojects->fixProjects();
        break;
}


