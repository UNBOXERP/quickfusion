<?php
/* Copyright (C) 2019 Admin <marcello.gribaudo@gmail.com>
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
 * \file    agendarecurevents/admin/setup.php
 * \ingroup agendarecurevents
 * \brief   Agendarecurevents setup page.
 */

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include substr($tmp, 0, ($i+1))."/main.inc.php";
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include dirname(substr($tmp, 0, ($i+1)))."/main.inc.php";
// Try main.inc.php using relative path
if (! $res && file_exists("../../main.inc.php")) $res=@include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=@include "../../../main.inc.php";
if (! $res) die("Include of main fails");



require_once DOL_DOCUMENT_ROOT.'/core/lib/agenda.lib.php';
require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
dol_include_once('/agendarecurevents/lib/agendarecurevents.lib.php');


// Translations
$langs->loadLangs(array("admin", "main", "agendarecurevents@agendarecurevents"));

$action=GETPOST('action','alpha');
$cancel=GETPOST('cancel','alpha');
$confirm = GETPOST('confirm', 'alpha');


// Security check
$socid = GETPOST('socid','int');
$id = GETPOST('id','int');
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'agenda', $id, 'actioncomm&societe', 'myactions|allactions', 'fk_soc', 'id');
if ($user->societe_id && $socid) $result = restrictedArea($user,'societe',$socid);

// Get form parameters 
$repeat = GETPOST('repeat','alpha');
$frequency = GETPOST('frequency','alpha');
if (!$frequency)
    $frequency = $langs->trans('Weekly');
$each = GETPOST('each','int');
$weeksDays = GETPOST('Weeksdays','array');
if (!$each)
    $each = 1;
$times = GETPOST('times', 'int');
$dateuntil=dol_mktime('00','00','00',GETPOST("untilmonth",'int'), GETPOST("untilday",'int'), GETPOST("untilyear",'int'));


$object = new ActionComm($db);
$form = new Form($db);


/*
 * Actions
 */
// Action clone object
if ($action == 'confirm_clone' && $confirm == 'yes') {
    
    $error = 0;
    $backtopage = DOL_URL_ROOT.'/comm/action/card.php?id='.$id;

    $result1=$object->fetch($id);
    if ($result1 <= 0) 	{
        $langs->load("errors");
        print $langs->trans("ErrorRecordNotFound");

        llxFooter();
        exit;
    }
    
    $datenow=strtotime(date("Y-m-d",  $object->datep));
        
    $mesgs = array();
    if ($repeat == 'times') {
        // Clone event n times
        if (!$times)
            setEventMessage($langs->trans('TimesNotValid'), 'errors');
        else {
            for ($i=1; $i<=$times; $i++) {
                $datenow = getNextDate($frequency, $datenow, $each);
                if ($datenow > 0) {
                    $dayOftheweek = date('D', $datenow);
                    if ($frequency == $langs->trans('Daily') && in_array($dayOftheweek, $weeksDays))
                       $i--;
                    else {
                        if (cloneEvent($object, $datenow) > 0)
                            $mesgs[]=$langs->trans('NewEventCreatedAt').' '. dol_print_date($datenow,'day');
                    }
                } else {
                    $error = $datenow;
                    break;
                }
            }
        }
    } else {
        // Clone event till a date
        if (!$dateuntil || $dateuntil < $datenow)
            setEventMessage($langs->trans('DateNotValid'), 'errors');
        else {
            while ($datenow <= $dateuntil) {
                
                $datenow = getNextDate($frequency, $datenow, $each);
                if ($datenow > 0) {
                    $dayOftheweek = date('D', $datenow);
                    if ($datenow <= $dateuntil && !($frequency == $langs->trans('Daily') && in_array($dayOftheweek, $weeksDays))) {
                        if (cloneEvent($object, $datenow) > 0)
                            $mesgs[]=$langs->trans('NewEventCreatedAt').' '. dol_print_date($datenow,'day');
                    }
                } else {
                    $error = $datenow;
                    break;
                }
            }
        }
        
    }
    
    // At the end, show messages
    if (!$error)
        setEventMessage($mesgs, 'mesgs');
    else {
        if ($error == -1)
            $mesgs[] = $langs->trans('NoDateTypeFound');
        else
            $mesgs[] = $langs->trans('ErrorInEventGeneration');
        setEventMessage($mesgs, 'errors');
    }
    header("Location: ".$backtopage);
    exit;
        
    
}


/*
 * View
 */

$form=new Form($db);

llxHeader('',$langs->trans("Agenda"),$help_url);


if (! empty($conf->use_javascript_ajax)) {
    print "\n".'<script type="text/javascript">';
    print 'function transalteTimeUnit(value) {
             switch(value) {
               case "'.$langs->trans('Daily').'":
                   return "'.$langs->trans('Day').'";
                   break;
               case "'.$langs->trans('Weekly').'":
                   return "'.$langs->trans('Week').'";
                   break;
               case "'.$langs->trans('Monthly').'":
                   return "'.$langs->trans('Month').'";
                   break;
               case "'.$langs->trans('Yearly').'":
                   return "'.$langs->trans('Year').'";
                   break;
             }
           }';
    print   '$(document).ready(function () {
                // Hide/Show days of the week on "frequency" radio change
                $("input[type=radio][name=frequency]").change(function() {
                    if (this.value == "'.$langs->trans('Daily').'")
                        $("td:nth-child(2)").show();
                    else
                        $("td:nth-child(2)").hide();
                    text = transalteTimeUnit(this.value);
                    $("#timeunit").text(text); 
                });
                
                // Clear non usefull filed on "repeat" radio change
                $("input[type=radio][name=repeat]").change(function() {
                    if (this.value == "times") {
                        $("#until").val("");
                        //$("#until").prop("disabled", true);
                        //$("#times").prop("disabled", false);
                        //$("#untilButtonNow").hide();
                    } else {
                        $("#times").val("");
                        //$("#until").prop("disabled", false);
                        //$("#times").prop("disabled", true);
                        //$("#untilButtonNow").show();
                    }
                });
                
                // Assign the preceeding frequncy setting
                //var $frequency = $("input:radio[name=\'frequency\']");
                //$frequency.filter("[value='.$frequency.']").prop("checked", true);
                $("input:radio[name=\'frequency\'][value=\''.$frequency.'\']").prop("checked", true);                
                if ("'.$frequency.'" == "'.$langs->trans('Daily').'")    
                    $("td:nth-child(2)").show();
                else
                    $("td:nth-child(2)").hide();
                    
                // Assign the preceeding repeat setting
                //var $repeat = $("input:radio[name=repeat]");
                //$repeat.filter("[value='.$repeat.']").prop("checked", true);
                $("input:radio[name=\'repeat\'][value=\''.$repeat.'\']").prop("checked", true);
            })';
    print '</script>'."\n";
}

// View or edit
if ($id > 0) {
    
    $object->fetch($id);
    $head=actions_prepare_head($object);

    $now=dol_now();
    // Confirmation  before cloning
    if ($action == 'clone') {
        
        // All parameters goes in $params
        $params = '';
        foreach ($_POST as $key => $value) {
            if ($key != 'token' && $key != 'action' && $key != 'id' && $key != 'backtopage') {
                if (is_array($value))
                    $params .= '&'.http_build_query(array($key => $value));
                else
                    $params .= '&'.$key.'='.$value;
            }
        }
        print $form->formconfirm("recurevents.php?id=".$id.$params,$langs->trans("CloneAction"),$langs->trans("ConfirmCloneEvent"),"confirm_clone",'','',1);
    }


    print '<form name="formaction" action="'.$_SERVER['PHP_SELF'].'" method="POST">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="action" value="clone">';
    print '<input type="hidden" name="id" value="'.$id.'">';
    if (empty($conf->global->AGENDA_USE_EVENT_TYPE)) print '<input type="hidden" name="actioncode" value="'.$object->type_code.'">';

    dol_fiche_head($head, 'recureven', $langs->trans("Action"),0,'action');
    $linkback =img_picto($langs->trans("BackToList"),'object_list','class="hideonsmartphone pictoactionview"');
    $linkback.= '<a href="'.DOL_URL_ROOT.'/comm/action/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';
    
    // Set timeunit label
    switch ($frequency) {
        case $langs->trans('Daily'):
            $label = $langs->trans('Day');
            break;
        case $langs->trans('Weekly'):
            $label = $langs->trans('Week');
            break;
        case $langs->trans('Monthly'):
            $label = $langs->trans('Month');
            break;
        case $langs->trans('Yearly'):
            $label = $langs->trans('Year');
            break;
    }

    // Link to other agenda views
    $out='';
    $out.='</li>';
    $out.='<li class="noborder litext">'.img_picto($langs->trans("ViewCal"),'object_calendar','class="hideonsmartphone pictoactionview"');
    $out.='<a href="'.DOL_URL_ROOT.'/comm/action/index.php?action=show_month&year='.dol_print_date($object->datep,'%Y').'&month='.dol_print_date($object->datep,'%m').'&day='.dol_print_date($object->datep,'%d').'">'.$langs->trans("ViewCal").'</a>';
    $out.='</li>';
    $out.='<li class="noborder litext">'.img_picto($langs->trans("ViewWeek"),'object_calendarweek','class="hideonsmartphone pictoactionview"');
    $out.='<a href="'.DOL_URL_ROOT.'/comm/action/index.php?action=show_week&year='.dol_print_date($object->datep,'%Y').'&month='.dol_print_date($object->datep,'%m').'&day='.dol_print_date($object->datep,'%d').'">'.$langs->trans("ViewWeek").'</a>';
    $out.='</li>';
    $out.='<li class="noborder litext">'.img_picto($langs->trans("ViewDay"),'object_calendarday','class="hideonsmartphone pictoactionview"');
    $out.='<a href="'.DOL_URL_ROOT.'/comm/action/index.php?action=show_day&year='.dol_print_date($object->datep,'%Y').'&month='.dol_print_date($object->datep,'%m').'&day='.dol_print_date($object->datep,'%d').'">'.$langs->trans("ViewDay").'</a>';
    $out.='</li>';
    $out.='<li class="noborder litext">'.img_picto($langs->trans("ViewPerUser"),'object_calendarperuser','class="hideonsmartphone pictoactionview"');
    $out.='<a href="'.DOL_URL_ROOT.'/comm/action/peruser.php?action=show_peruser&year='.dol_print_date($object->datep,'%Y').'&month='.dol_print_date($object->datep,'%m').'&day='.dol_print_date($object->datep,'%d').'">'.$langs->trans("ViewPerUser").'</a>';
    $linkback.=$out;

    $morehtmlref='<div class="refidno">';
    $morehtmlref.='</div>';

    dol_banner_tab($object, 'id', $linkback, ($user->societe_id?0:1), 'id', 'ref', $morehtmlref);

    print '<div class="fichecenter">';
    print '<div class="underbanner clearboth" style="margin-bottom: 25px;"></div>';
    
    print '<table class="border" >';

    // Frequency box
    print '<tr valign="top"=><td style="padding:0 15px 0 15px;">';
    print $langs->trans('Frequency').'<br>';
    print '<div style="border:1px solid black;padding: 3px;margin-top:5px;display: inline-block;">';
    print '<table style="border-collapse: collapse;">';
    print '<tr><td><input type="radio" id="frequency" name="frequency" value="'.$langs->trans('Daily').'">&nbsp'.$langs->trans('Daily').'</td></tr>';
    print '<tr><td><input type="radio" id="frequency" name="frequency" value="'.$langs->trans('Weekly').'">&nbsp'.$langs->trans('Weekly').'</td></tr>';
    print '<tr><td><input type="radio" id="frequency" name="frequency" value="'.$langs->trans('Monthly').'">&nbsp'.$langs->trans('Monthly').'</td></tr>';
    print '<tr><td><input type="radio" id="frequency" name="frequency" value="'.$langs->trans('Yearly').'">&nbsp'.$langs->trans('Yearly').'</td></tr>';
    print '</div>';
    print '</table>';
    
    // Excluded days box
    print '<td style="padding:0 15px 0 15px;">';
    print $langs->trans('ExcludedDays').'<br>';
    print '<div id="excludedays" style="border:1px solid black;padding: 3px;margin-top:5px;display: inline-block;">';
    print '<table style="border-collapse: collapse;">';
    print '<tr><td><input type="checkbox" name="Weeksdays[]" value="Sun"'.(in_array('Sun', $weeksDays) ? ' checked' : '').'>&nbsp'.$langs->trans('Sunday').'</td></tr>';
    print '<tr><td><input type="checkbox" name="Weeksdays[]" value="Mon"'.(in_array('Mon', $weeksDays) ? ' checked' : '').'>&nbsp'.$langs->trans('Monday').'</td></tr>';
    print '<tr><td><input type="checkbox" name="Weeksdays[]" value="Tue"'.(in_array('Tue', $weeksDays) ? ' checked' : '').'>&nbsp'.$langs->trans('Tuesday').'</td></tr>';
    print '<tr><td><input type="checkbox" name="Weeksdays[]" value="Wed"'.(in_array('Wed', $weeksDays) ? ' checked' : '').'>&nbsp'.$langs->trans('Wednesday').'</td></tr>';
    print '<tr><td><input type="checkbox" name="Weeksdays[]" value="Thu"'.(in_array('Thu', $weeksDays) ? ' checked' : '').'>&nbsp'.$langs->trans('Thursday').'</td></tr>';
    print '<tr><td><input type="checkbox" name="Weeksdays[]" value="Fri"'.(in_array('Fri', $weeksDays) ? ' checked' : '').'>&nbsp'.$langs->trans('Friday').'</td></tr>';
    print '<tr><td><input type="checkbox" name="Weeksdays[]" value="Sat"'.(in_array('Sat', $weeksDays) ? ' checked' : '').'>&nbsp'.$langs->trans('Saturday').'</td></tr>';
    print '</div>';
    print '</table>';
    print '</td>';

    // Repeat event box
    print '<td>';
    print $langs->trans('ReperatEvent').'<br>';
    print '<div style="border:1px solid black;padding: 3px;margin-top:5px;display: inline-block;">';
    print '<table style="border-collapse: collapse;">';
    print '<tr><td>'.$langs->trans('Each').'&nbsp&nbsp<input type="text" id="each" name="each" size="3" value="'.$each.'">&nbsp<label id="timeunit">'.$label.'</label></td></tr>';
    print '<tr><td><input type="radio" id="repeat" name="repeat" value="times" checked="checked">&nbsp'.$langs->trans('For').'<input type="text" id="times" name="times" size="3" value="'.$times.'">&nbsp'.$langs->trans('Times').'</td></tr>';
    print '<tr><td><input type="radio" id="repeat" name="repeat" value="until">&nbsp'.$langs->trans('UntilDate').'&nbsp&nbsp'.$form->select_date($dateuntil,'until','','','','add',1,1,1);'</td></tr>';
    print '</div>';
    print '</table>';
    print '</td></tr>';

    print '</table>';
    print '</div>';


    dol_fiche_end();

    print '<div class="center">';
    print '<input type="submit" class="button" name="edit" value="'.$langs->trans("Clone").'">';
    print '</div>';

    print '</form>';


}

// End of page
llxFooter();
$db->close();
