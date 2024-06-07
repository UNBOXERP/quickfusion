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
 * \file    agendarecurevents/lib/agendarecurevents.lib.php
 * \ingroup agendarecurevents
 * \brief   Library files with common functions for Agendarecurevents
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function agendarecureventsAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("agendarecurevents@agendarecurevents");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/agendarecurevents/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;
	$head[$h][0] = dol_buildpath("/agendarecurevents/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@agendarecurevents:/agendarecurevents/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@agendarecurevents:/agendarecurevents/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'agendarecurevents');

	return $head;
}

/**
 * Return the next date incremented by the selected period
 *
 * @param  int          $increment      Type of date increment
 * @param  date         $date           Date to increment
 * @param  int          $each           End of the period
 * @return date                         New date
 */
function getNextDate($increment, $date, $each) {
    global $langs;
    
    switch ($increment) {
        case $langs->trans('Daily'):
            $date = strtotime('+'.$each.' day', $date);
            break;
        case $langs->trans('Weekly'):
            $date = strtotime('+'.(7*$each).' day', $date);
            break;
        case $langs->trans('Monthly'):
            $date = strtotime('+'.$each.' month', $date);
            break;
        case $langs->trans('Yearly'):
            $date = strtotime('+'.$each.' year', $date);
            break;
        default: 
            return -1;
            break;
    }
    
    return $date;
}

/**
 * Clone an event adding x days at the staring and ending date
 *
 * @param  object       $object         Event object
 * @param  date         $newdate        Date where to plave the nwe event
 * @return int                          > 0 if Ok, < 0 if error
 */
function cloneEvent($object, $newdate) {
    
    global $user;

    $days = abs(round(($newdate - strtotime(date("Y-m-d",  $object->datep))) / (60 * 60 * 24)));
    $object->datep = strtotime('+'.$days.' day', $object->datep);
    $object->datef = strtotime('+'.$days.' day', $object->datef);
    $result = $object->createFromClone($user, $object->fk_user_author, $object->fk_soc);
    if ($result <= 0) {
        setEventMessages($object->error, $object->errors, 'errors');
        $action = '';
    }
    return $result;
    
}