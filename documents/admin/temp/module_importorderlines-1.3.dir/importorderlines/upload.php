<?php
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2016 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2013      Florian Henry		<florian.henry@open-concept.pro>
 * Copyright (C) 2017      Ferran Marcet       	 <fmarcet@2byte.es>
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
 *  \file       htdocs/commande/note.php
 *  \ingroup    commande
 *  \brief      Fiche de notes sur une commande
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/order.lib.php';
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
if (!empty($conf->projet->enabled)) {
	require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
}

// Load translation files required by the page
$langs->loadLangs(array('companies', 'bills', 'orders'));

$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$socid = GETPOST('socid', 'int');
$action = GETPOST('action', 'aZ09');

// Security check
$socid = 0;
if ($user->socid) $socid = $user->socid;
$result = restrictedArea($user, 'commande', $id, '');


$object = new Commande($db);
if (!$object->fetch($id, $ref) > 0)
{
	dol_print_error($db);
	exit;
}

$sql = " select CONCAT(u.firstname,' ',u.lastname) , MAX(c.date_imported) , c.file_name , c.total_registered, c.not_registered from " . MAIN_DB_PREFIX . "importorderline_hist AS c ";
$sql .= " LEFT JOIN " .MAIN_DB_PREFIX."user AS u ON u.rowid = c.user_import ";
$sql .= " WHERE c.rowid = (select max(c2.rowid) FROM " .MAIN_DB_PREFIX."importorderline_hist AS c2 where c.file_name = c2.file_name) AND  c.fk_commande = " . $id . " GROUP BY c.file_name";
$resql = $db->query($sql);
$i = 0;
$num = $db->num_rows($resql);

$m=0;
if($resql){
	foreach($resql as $values)
	{
		foreach($values as $value4)
		{
			$ordi[$m][].=	$value4;
		}
		$m=$m+1;
	}
}



$permissionnote = $user->rights->commande->creer; // Used by the include of actions_setnotes.inc.php


/*
 * Actions
 */

include DOL_DOCUMENT_ROOT.'/core/actions_setnotes.inc.php'; // Must be include, not includ_once


/*
 * View
 */

llxHeader('', $langs->trans('Order'), 'EN:Customers_Orders|FR:Commandes_Clients|ES:Pedidos de clientes');

$form = new Form($db);

if ($id > 0 || !empty($ref))
{
	$object->fetch_thirdparty();

	$head = commande_prepare_head($object);

	print dol_get_fiche_head($head, 'importorderlines', $langs->trans("CustomerOrder"), -1, 'order');

	// Order card

	$linkback = '<a href="'.DOL_URL_ROOT.'/commande/list.php?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';


	$morehtmlref = '<div class="refidno">';
	// Ref customer
	$morehtmlref .= $form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', 0, 1);
	$morehtmlref .= $form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', null, null, '', 1);
	// Thirdparty
	$morehtmlref .= '<br>'.$langs->trans('ThirdParty').' : '.$object->thirdparty->getNomUrl(1);
	// Project
	if (!empty($conf->projet->enabled))
	{
		$langs->load("projects");
		$morehtmlref .= '<br>'.$langs->trans('Project').' ';
		if ($user->rights->commande->creer)
		{
			if ($action != 'classify') {
				//$morehtmlref.='<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?action=classify&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> : ';
				$morehtmlref .= ' : ';
			}
			if ($action == 'classify') {
				//$morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
				$morehtmlref .= '<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
				$morehtmlref .= '<input type="hidden" name="action" value="classin">';
				$morehtmlref .= '<input type="hidden" name="token" value="'.newToken().'">';
				$morehtmlref .= $formproject->select_projects($object->socid, $object->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
				$morehtmlref .= '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
				$morehtmlref .= '</form>';
			} else {
				$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'].'?id='.$object->id, $object->socid, $object->fk_project, 'none', 0, 0, 0, 1);
			}
		} else {
			if (!empty($object->fk_project)) {
				$proj = new Project($db);
				$proj->fetch($object->fk_project);
				$morehtmlref .= '<a href="'.DOL_URL_ROOT.'/projet/card.php?id='.$object->fk_project.'" title="'.$langs->trans('ShowProject').'">';
				$morehtmlref .= $proj->ref;
				$morehtmlref .= '</a>';
			} else {
				$morehtmlref .= '';
			}
		}
	}
	$morehtmlref .= '</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="underbanner clearboth"></div>';


	$cssclass = "titlefield";





?>
	<div class="container">
<form method="post" action="import.php" enctype="multipart/form-data">
    <table align="center" >
        <tr>
            <td colspan="2">
               Import Order:
            </td>
            <td>
                <input type="file" accept=".csv" name="archivo" size="10"/>
                <input type="hidden" value="cargar" name="action"/>
				<input type="hidden" value="<?php echo $id; ?>" name="id"/>
            </td>
        </tr>
        <tr>
			<style>
				input[type=button], input[type=submit], input[type=reset] {
					background-color: #8827E9;
					border: none;
					color: white;
					padding: 10px 25px;
					text-decoration: none;
					margin: 4px 2px;
					cursor: pointer;
				}
			</style>
            <td align="center" colspan="5">
                <input type="submit" value="UPLOAD"/>
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td align="right" colspan="11">
                <br><a href="../../commande/card.php?id=<?php echo $id;?>">Back to Order</a></br>
            </td>
        </tr>
    </table>
	<br>
	<br>
	<br>
	<tr>
		<!--<td align="left">
			<br><a>File Imported: <strong> <?php print  $fil; ?> </strong> </a></br>
			<br><a>Imported Date: <strong><?php print  $dat; ?></strong></a></br>
			<br><a>User Action: <strong><?php print  $usr; ?></strong></a></br>
		</td> -->
	</tr>

</form>
</div>


	<table class="data-table">
		<tr class="data-heading">
			<style>
				table,
				th,
				td {
					padding: 10px;
					/*border: 1px solid black;*/
					border-collapse: collapse;
				}
			</style>
			<td>User</td>  <td>Date Imported</td>  <td>Imported File</td> <td>Total Uploaded</td> <td>Total Not Uploaded</td>
		</tr>

		<tr>
			<?php for ($i=0; $i < $num; $i++)
			{ ?>
		<tr>

			<td><?php print  $ordi[$i][0] ; ?></td>

			<td><?php print  $ordi[$i][1] ; ?></td>
			<td><a href="<?php print $ordi[$i][2]  ; ?>" title="Link title" target="_blank"><?php print  $ordi[$i][2] ; ?></a></td>
			<td><?php print  $ordi[$i][3] ; ?></td>
			<td><?php print  $ordi[$i][4] ; ?></td>
			<?php } ?>
		</tr>
		</tr>
	</table>

<?php
	print '</div>';

	print dol_get_fiche_end();
}


// End of page
llxFooter();
$db->close();









