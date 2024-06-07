<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       hmv/hmvindex.php
 *	\ingroup    hmv
 *	\brief      Home page of hmv top menu
 */

header ("Expires: Fri, 14 Mar 1980 20:53:00 GMT"); //la pagina expira en fecha pasada
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); //ultima actualizacion ahora cuando la cargamos
header ("Cache-Control: no-cache, must-revalidate"); //no guardar en CACHE
header ("Pragma: no-cache"); //PARANOIA, NO GUARDAR EN CACHE


// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res && file_exists("../../../../main.inc.php")) $res = @include "../../../../main.inc.php";

global $user, $db, $langs, $conf;


//require_once DOL_DOCUMENT_ROOT . '/custom/jsreports/core/operations/operations.php';
//require_once DOL_DOCUMENT_ROOT.'/custom/scrum/class/functions.php';

// $idSociete 	= GETPOST('societe', 'aZ09');
// $date 		= GETPOST('date', 'aZ09');


// if($idSociete = null || $idSociete='')
// {
//     $fecha='2022-12-01';
// }else{
//     $fecha=$date;
// }
//$url='https://master.unboxcrm.com';
//$dolikey='Ac3zgN8j9ATRAaCB3h3j82QzYr9mz1Y0';
// $url=$conf->global->UrlMaster;
// $dolikey=$conf->global->DoliKeyMaster;
// $email=$conf->global->EmailSASS;
// $destino=$conf->global->DestinoSASS;
// $CabeceraEmailSASS1=$conf->global->CabeceraEmailSASS1;
// $CabeceraEmailSASS2=$conf->global->CabeceraEmailSASS2;

$sender_email=$conf->global->EMAILCOMMANDE_sender_email;
$destination_email=$conf->global->EMAILCOMMANDE_destination_email;
$subject=$conf->global->EMAILCOMMANDE_subject;
$body=$conf->global->EMAILCOMMANDE_body;
// $sender_email=$conf->global->EMAILCOMMANDE_sender_email;





// $num=0;
// $llamada= "/api/index.php/contracts?sortfield=t.rowid&sortorder=ASC&sqlfilters=datec%3E'".$fecha."%2000%3A00%3A00'";
// $registros= GetCurl($url, $dolikey, $llamada);
// $num=count($registros);
// if($registros >0){
// $DATOS= " <br><br><br><h1> ".$CabeceraEmailSASS1."</h1> <p>". $CabeceraEmailSASS2. "</p><br><br>";

//     $tabla= CreateTable($registros, $url, $dolikey);
//     $PARAENVIAR= $DATOS." ".$tabla;
$sender_email=$conf->global->EMAILCOMMANDE_sender_email;
$destination_email=$conf->global->EMAILCOMMANDE_destination_email;
$subject=$conf->global->EMAILCOMMANDE_subject;
$body=$conf->global->EMAILCOMMANDE_body;



//$sender_email=$email='mstoluca@gmail.com';
//$destination_email=$sender_email='ame@unboxerp.com';
    $nombre = 'Alan Montoya';
    $cabeceras = "MIME-Version: 1.0 \r\n";
    $cabeceras .= "Content-type: text/html; charset=utf-8 \r\n";
    $cabeceras.= "From: $nombre <$sender_email>\r\n";
    //$destino="mstoluca@gmail.com";
//$subject=$asunto="REPORTE SASS";
    $fecha=date("d/m/y");
    $hora=date("H:i:s");





require_once (DOL_DOCUMENT_ROOT."/core/class/CMailFile.class.php");

$mail = new CMailFile(
        $subject,
        $destination_email,
        $sender_email,
        $body,
        array(),
        array(),
        array(),
        '',
        '',
        0,
        1,
	'',
	'',
	'',
	'',
	'emailing',
	''
   );

    $mail->errors_to = $conf->global->MAIN_MAIL_ERRORS_TO;

    // Send or not email
    $result=$mail->sendfile();
    if (! $result)
    {
        print "Error sending email ".$mail->error."\n";
        dol_syslog("Error sending email ".$mail->error."\n");
    }else{
		setEventMessages('EMAIL ENVIADO', '', 'mesgs');
	}

	header("Location: ".DOL_URL_ROOT.'/custom/emailcommande/admin/setup.php');



    // if(mail("$destination_email","$subject","$PARAENVIAR","$cabeceras"))
    // if(
    //     // sendMails($to['email'], $subject, $secondBody);mail("$destination_email","$subject","$body","$cabeceras"))
    //     sendMails($destination_email, $subject, $body))
    // {
    //     echo "Datos enviados correctamente";

    // }else{
    //     echo "no se enviaron los Datos";

    // }



// }


