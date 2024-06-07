<?php
function getall($table, $filter= '1=1'  ) {

	global $db, $dolibarr_main_url_root;
	//$A=getProductNull($object);



	$sql  = " SELECT * FROM ".MAIN_DB_PREFIX."".$table;
	if($filter != '1=1' ) $sql .= " where ".$filter;

	$resqle = $db->query($sql);

	if ($resqle) {
		$num = $db->num_rows($resqle);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($resqle);
			$template[] = $obj;
			$i++;
		}
	}


	return $template;

}

function getFecha($date)
{
	// floatval(str_replace(",", "", $dataCommandeFor[$datacomm]));
	$nowarray = dol_getdate($date, true);
	$day = $nowarray['mday'];
	$month = $nowarray['mon'];
	$year = $nowarray['year'];
	$hours = $nowarray['hours'];
	$minutes = $nowarray['minutes'];
	$seconds = $nowarray['seconds'];
	$date_day = $day . '-' . $month . '-' . $year;
	$date_day = $year . '' . $month . '' . $day;
	$date_ref = $year . '' . $month;
	$date_insert = $year . '-' . $month . '-' . $day;
	$dates = array($date_day, $date_ref, $date_insert);

	return $dates;


}

