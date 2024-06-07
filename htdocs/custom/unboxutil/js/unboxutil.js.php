<?php
/* Copyright (C) 2022 SuperAdmin
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Library javascript to enable Browser notifications
 */

if (!defined('NOREQUIREUSER')) define('NOREQUIREUSER', '1');
if (!defined('NOREQUIREDB')) define('NOREQUIREDB', '1');
if (!defined('NOREQUIRESOC')) define('NOREQUIRESOC', '1');
if (!defined('NOREQUIRETRAN')) define('NOREQUIRETRAN', '1');
if (!defined('NOCSRFCHECK')) define('NOCSRFCHECK', 1);
if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', 1);
if (!defined('NOLOGIN')) define('NOLOGIN', 1);
if (!defined('NOREQUIREMENU')) define('NOREQUIREMENU', 1);
if (!defined('NOREQUIREHTML')) define('NOREQUIREHTML', 1);
if (!defined('NOREQUIREAJAX')) define('NOREQUIREAJAX', '1');


/**
 * \file    unboxutil/js/unboxutil.js.php
 * \ingroup unboxutil
 * \brief   JavaScript file for module Unboxutil.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/../main.inc.php")) $res = @include substr($tmp, 0, ($i + 1)) . "/../main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

// Define js type
header('Content-Type: application/javascript');
// Important: Following code is to cache this file to avoid page request by browser at each Dolibarr page access.
// You can use CTRL+F5 to refresh your browser cache.
if (empty($dolibarr_nocache)) header('Cache-Control: max-age=3600, public, must-revalidate');
else header('Cache-Control: no-cache');

global $conf;


?>

/* Javascript library of module Unboxutil */

if (window.location.href.indexOf("projet/element.php") > -1) {
	var idproject = window.location.href.split('id=')[1];
	$(document).ready(function () {
		$("#id-right > div > div:nth-child(20)").css({'overflow': 'scroll', 'max-height': '600px'});
		//Set id Attr to #id-right > div > div:nth-child(20) > table
		var tablaordenada = $('#id-right > div > div:nth-child(20) > table').attr('id', 'tablaordenesordered');

		$('#tablaordenesordered tr').attr('data-order', function (index, value) {
			//if (index > 0) {
			//penultimate tablaordenesordered data-order parameter value
			var penultimo = $('#tablaordenesordered tr:nth-last-child(2)').attr('data-order');

			try {
				if (index == 0) return parseInt(index);

				if (index == $('#tablaordenesordered tr').length - 1) return parseInt(penultimo + 1);

				var id = $(this).find('a').attr('href').split('elementselect=')[1];
			} catch (e) {
				return parseInt(index);
			}
			//  }
			return parseInt(id);
		});

		var $tablaorden = $("#tablaordenesordered").sortable({
			//items not first tr and not last tr
			items: "tr:gt(0):not(:last)",
			//items: 'tr:not(:first)',
			scroll: true,
			axis: "y",
			update: function (event, ui) {
				//loop througth each data-order and set the value to array
				var orden = [];
				$('#tablaordenesordered tr').each(function (index, value) {
					orden.push($(this).attr('data-order'));
				});

				var tablaordenada = JSON.stringify(orden);
				//Send tablaordenada to server
				$.ajax({
					type: "POST",
					url: "<?php echo DOL_URL_ROOT . "/custom/unboxutil/ajax/ajax.php"; ?>",
					data: {
						action: "setorden",
						tablaordenada: tablaordenada,
						idproject: idproject
					},
					success: function (response) {
						$.jnotify('Order Succesfully Updated', 'success');

						$('#tablaordenesordered tr:not(:last)').each(function (index, value) {
							if (index > 0) {
								$(this).find('td:first').text(index);
							}
						});

					}
				});
			}
		});

		//Get tablaordenada from server
		$.ajax({
			type: "POST",
			url: "<?php echo DOL_URL_ROOT . "/custom/unboxutil/ajax/ajax.php"; ?>",
			data: {
				action: "getorden",
				idproject: idproject
			},
			success: function (response) {
				if (response) {
					var received = JSON.parse(response);
					var data = received;
					var contador = countInObject(data[0]);
					//loop througth each data-order and re order the table with the new order from server and keep values
					var table_tr = [];
					$('#tablaordenesordered tr').each(function (index, value) {
						if (contador == 0) {
							table_tr[parseInt(index)] = jQuery(this).clone(true, true);
						} else {
							table_tr[parseInt(jQuery(this).attr("data-order"))] = jQuery(this).clone(true, true);
						}

						$(this).detach();
					});

					jQuery("#tablaordenesordered").append(table_tr[0]);


					if (contador == 0) {
						//jQuery("#tablaordenesordered").append(tr);
						var tr = table_tr[1][0];
						jQuery("#tablaordenesordered").append(tr);
						var tr1 = table_tr[2][0];
						jQuery("#tablaordenesordered").append(tr1);
						//jQuery("#tablaordenesordered").append(table_tr[table_tr.length - 1]);
						$("#tablaordenesordered > tbody > tr.liste_titre").prepend("<td><span class='badge'>Ord</span></td>");
						$("#tablaordenesordered > tbody > tr:not(:first)").each(function (index, value) {

							$(this).prepend("<td>" + (index + 1) + "</td>");
						});

					} else {
						for (var i = 0; i < contador; i++) {
							var tr = table_tr[data[0][i]['fk_object']];
							//var tr = table_tr[i];
							jQuery("#tablaordenesordered").append(tr);
						}
						jQuery("#tablaordenesordered").append(table_tr[table_tr.length - 1]);
						$("#tablaordenesordered > tbody > tr.liste_titre").prepend("<td><span class='badge'>Ord</span></td>");
						$("#tablaordenesordered > tbody > tr:not(:first)").each(function (index, value) {

							$(this).prepend("<td>" + (index + 1) + "</td>");
						});

					}
					$("<table id='tabletotal' class=\"noborder centpercent ui-sortable\"><tr>" + $("#tablaordenesordered > tbody > tr:last").html() + "</tr></table>").prependTo("#id-right > div > div:nth-child(20)");
					$("#tablaordenesordered > tbody > tr:last").hide();
					$("#tabletotal td:first").text('');
				}
			}
		});
		//

		$("#tablaordenesordered > tbody > tr:first").css({
			"top": "0",
			"position": "sticky",
		});

		function countInObject(obj) {
			var count = 0;
			// iterate over properties, increment if a non-prototype property
			for (var key in obj) if (obj.hasOwnProperty(key)) count++;
			return count;
		}

		var btn = '<a href="#" class="cbtnfix"><span class="valignmiddle text-plus-circle hideonsmartphone">Sort Order</span><span class="fa fa-check fa-2x valignmiddle paddingleft"></span></a>';
		$("#id-right > div > table:nth-child(19) > tbody > tr > td.nobordernopadding.titre_right.wordbreakimp.right.valignmiddle > div").prepend(btn);
		//class .cbtnfix on click ajax
		$(".cbtnfix").click(function () {
			//send data to the server ajax.php
			$.ajax({
				type: "POST",
				url: "<?php echo DOL_URL_ROOT . "/custom/unboxutil/ajax/ajax.php"; ?>",
				data: {
					action: "fixprojects"
				},
				success: function (data) {
					$.jnotify("Successfully fixed", "success");
					location.reload();
				},
				error: function (data) {
					$.jnotify("Error Ocurred", "error");
				}
			});


		});


	});
}


if (window.location.href.indexOf("fourn/commande/card.php") > -1) {
	$(document).ready(function () {
		$("#variantr").val($("#options_variantreason").val());
		$("#options_variantreason").change(function () {
			var val = $(this).val();
			$("#variantr").val(val);
		});


	});


}

if (window.location.href.indexOf("compta/facture/list.php") > -1) {
	$(document).ready(function () {
		$("#idpago").val($("#selectidlistpaid").val());
		$("#selectidlistpaid").change(function (e) {
			$("#idpago").val($("#selectidlistpaid").val());
		});


	});

}

function addDispatchLinetoConsume(index, type, mode) {
	mode = mode || 'qtymissing'

	console.log("fourn/js/lib_dispatch.js.php Split line type=" + type + " index=" + index + " mode=" + mode);
	var nbrTrs = $("tr[name^='" + type + index + "']").length; 				// position of line for batch
	var $row = $("tr[name='" + type + '_' + index + "_1']").clone(true); 				// clone last batch line to jQuery object
	var qtyOrdered = parseFloat($("#qty_" + index).val()); 	// Qty ordered is same for all rows
	var qty = parseFloat($("#qty-" + index + "-" + nbrTrs).val());
	var qtyDispatched;

	if (mode === 'lessone') {
		qtyDispatched = parseFloat($("#qty_dispatched_" + index).val()) + 1;
	} else {
		qtyDispatched = parseFloat($("#qty_dispatched_" + index).val()) + qty;
		console.log(qty);
		// If user did not reduced the qty to dispatch on old line, we keep only 1 on old line and the rest on new line
		if (qtyDispatched == qtyOrdered && qtyDispatched > 1) {
			qtyDispatched = parseFloat($("#qty_dispatched_" + index).val()) + 1;
			mode = 'lessone';
		}
	}
	console.log("qtyDispatched=" + qtyDispatched + " qtyOrdered=" + qtyOrdered);

	if (qtyOrdered <= 1) {
		window.alert("Quantity can't be split");
	}
	if (qtyDispatched < qtyOrdered) {
		//replace tr suffix nbr
		var re1 = new RegExp('_' + index + '_1', 'g');
		var re2 = new RegExp('-' + index + '-1', 'g');
		$row.html($row.html().replace(re1, '_' + index + '_' + (nbrTrs + 1)));
		$row.html($row.html().replace(re2, '-' + index + '-' + (nbrTrs + 1)));
		//create new select2 to avoid duplicate id of cloned one
		$row.find("select[name='" + 'idwarehousetoproduce-' + index + '-' + (nbrTrs + 1) + "']").select2();
		// TODO find solution to copy selected option to new select
		// TODO find solution to keep new tr's after page refresh
		//clear value
		$row.find("input[name^='qtytoproduce']").val('');
		//change name of new row
		$row.attr('name', type + '_' + index + '_' + (nbrTrs + 1));
		//insert new row before last row
		$("tr[name^='" + type + "_" + index + "_" + nbrTrs + "']:last").after($row);

		//remove cloned select2 with duplicate id.
		$("#s2id_entrepot_" + nbrTrs + '_' + index).detach();			// old way to find duplicated select2 component
		$(".csswarehouse_" + index + "_" + (nbrTrs + 1) + ":first-child").parent("span.selection").parent(".select2").detach();

		/*  Suffix of lines are:  index _ trs.length */
		$("#qtytoproduce-" + index + "-" + (nbrTrs + 1)).focus();
		if ($("#qtytoproduce-" + index + "-" + (nbrTrs)).val() == 0) {
			$("#qtytoproduce-" + index + "-" + (nbrTrs)).val(1);
		}
		var totalonallines = 0;
		for (let i = 1; i <= nbrTrs; i++) {
			console.log(i + " = " + parseFloat($("#qtytoproduce-" + index + "-" + i).val()));
			totalonallines = totalonallines + parseFloat($("#qtytoproduce-" + index + "-" + i).val());
		}
		console.log("totalonallines=" + totalonallines);
		if (totalonallines == qtyOrdered && qtyOrdered > 1) {
			var prevouslineqty = $("#qtytoproduce-" + index + "-" + nbrTrs).val();
			$("#qtytoproduce-" + index + "-" + (nbrTrs)).val(1);
			$("#qtytoproduce-" + index + "-" + (nbrTrs + 1)).val(prevouslineqty - 1);
		}
		$("#qty_dispatched_" + index).val(qtyDispatched);

		//hide all buttons then show only the last one
		$("tr[name^='" + type + "_'][name$='_" + index + "'] .splitbutton").hide();
		$("tr[name^='" + type + "_'][name$='_" + index + "']:last .splitbutton").show();

		if (mode === 'lessone') {
			qty = 1; // keep 1 in old line
			$("#qty_" + (nbrTrs - 1) + "_" + index).val(qty);
		}
		// Store arbitrary data for dispatch qty input field change event
		$("#qtytoproduce-" + index + (nbrTrs)).data('qty', qty);
		$("#qtytoproduce-" + index + (nbrTrs)).data('type', type);
		$("#qtytoproduce-" + index + (nbrTrs)).data('index', index);
	}
}



