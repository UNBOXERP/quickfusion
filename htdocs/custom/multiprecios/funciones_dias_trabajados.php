<?php



class workeddays {
	public const EXPLIMPIARIDFISCAL = [ '/[^0-9 -]/', '/[ -]+/', '/^-|-$/' ];

	/*public static $diasFeriados1 = [
		'01-01', //Año nuevo
		'02-06', //CUMPLE ALAN
		'04-19', //Declaración de la Independencia,
		'05-01', //Día del trabajador
		'06-24', //Batalla de Carabobo
		'07-05', //Día de la independencia
		'07-24', //Natalicio de Simón Bolívar
		'10-12', //Día de la Resistencia Indígena
		'12-24', //Víspera de Navidad
		'12-25', //Navidad
		'12-31', //Fiesta de Fin de Año
	];*/

	function  getHollidays(){

		global $db;
		$sql = " select rowid as id, ";
		$sql .= " label as label, ";
		$sql .= " holliday as day ";
		$sql .= " FROM ".MAIN_DB_PREFIX."automatic_hollidays ";
		$sql .= " WHERE status = 1 ";


		$result = $db->query($sql);

		if ($result) {
			$num = $db->num_rows($result);
			$i = 0;
			while ($i < $num) {
				$obj = $db->fetch_object($result);
				//$nowarray = dol_getdate($obj->day, true);
				$nowarray = date("m-d", strtotime($obj->day));

				//print_r($obj->day);




				//$template[] = $date;
				$template[] =$nowarray;
				$i++;
			}
		}
		return $template;

	}//REGRESA pos de template





	/**
	 * [formatearFecha Formatea la fecha Y-m-d]
	 * @param  [string] $fecha [description]
	 * @return [string]        [description]
	 */
	public static function formatearFecha($fecha) {

	if ( strpos($fecha, '/') ) {

		$fechaArray = explode('/', $fecha);

		$dia  = $fechaArray[0];
		$mes  = $fechaArray[1];
		$year = $fechaArray[2];

		$fecha = $year . '-' . $mes . '-' . $dia;

	}

	return $fecha;

}

	/**
	 * [diferenciaEntreFechas Obtiene la diferencia entre 2 fechas]
	 * @param  [string] $fechaInicial    [description]
	 * @param  [string] $fechaFinal      [description]
	 * @param  string $formatoDiferencia [ % = Literal %; %
	 *                                   	 Y = Años, numérico, al menos 2 dígitos empezando con 0; 01, 03
	 *                                   	 y = Años, numérico; 1, 3
	 *                                   	 M = Meses, numérico, al menos 2 dígitos empezando con 0; 01, 03, 12
	 *                                   	 m = Meses, numérico; 1, 3, 12
	 *                                   	 D = Días, numérico, al menos 2 dígitos empezando con 0; 01, 03, 31
	 *                                   	 d = Días, numérico; 1, 3, 31
	 *                                   	 a = Número total de días como resultado de una operación con DateTime::diff(), o de lo contrario (unknown); 4, 18, 8123
	 *                                   	 H = Horas, numérico, al menos 2 dígitos empezando con 0; 01, 03, 23
	 *                                   	 h = Horas, numérico; 1, 3, 23
	 *                                   	 I = Minutos, numérico, al menos 2 dígitos empezando con 0; 01, 03, 59
	 *                                   	 i = Minutos, numérico; 1, 3, 59
	 *                                   	 S = Segundos, numérico, al menos 2 dígitos empezando con 0; 01, 03, 57
	 *                                   	 s = Segundos, numérico; 1, 3, 57
	 *                                   	 R = Signo "-" cuando es negativo, "+" cuando es positivo; -, +
	 *                                   	 r = Signo "-" cuando es negativo, vacío cuando es positivo; - ]
	 * @return [string]                  [description]
	 */
	public static function diferenciaEntreFechas($fechaInicial, $fechaFinal, $formatoDiferencia = 'a') {

		$fechaInicial = new \DateTime( self::formatearFecha($fechaInicial) );
		$fechaFinal   = new \DateTime( self::formatearFecha($fechaFinal) );

		//Diferencia de fechas y formato
		return $fechaInicial->diff($fechaFinal)->format("%$formatoDiferencia%");

	}

	/**
	 * [contarDiasNoHabiles Cuenta los días NO hábiles entre 2 fechas]
	 * @param  [string] $fechaInicial [description]
	 * @param  [string] $fechaFinal   [description]
	 * @return [array]  $arrayDias    [Número de días No hábiles (Sábados y Domingos)]
	 */
	public static function contarDiasNoHabiles($fechaInicial, $fechaFinal/*, $fechaHoy*/) {

	//Contadores de días
	$feriados = 0;
	$sabados  = 0;
	$domingos = 0;

	$msg          = '';
	$diasFeriados = [];
	$feriados=getHolidays($fechaInicial, $fechaFinal);
	if(is_null($feriados))
		{
			$feriados=0;
		};
	$fechaInicial = new \DateTime( self::formatearFecha($fechaInicial) );
	$fechaFinal   = new \DateTime( self::formatearFecha($fechaFinal) );


	while ( $fechaInicial <= $fechaFinal ) {

		( $fechaInicial->format('l') === 'Saturday' ) ? $sabados++  : $sabados  += 0;
		( $fechaInicial->format('l') === 'Sunday' )   ? $domingos++ : $domingos += 0;

		//Si está en el array y es sábado o domingo (Se restan esos días)
		//if ( in_array($fechaInicial->format('m-d'), self::$diasFeriados ) ) {


		//if ( in_array($fechaInicial->format('m-d'), self::$diasFeriados ) ) {


		/*if ( in_array($fechaInicial->format('m-d'), self::getHollidays() ) ) {

			$feriados++;

			if ( $fechaInicial->format('l') === 'Saturday' ) {
				$sabados--;
				$msg .= '* El sábado ' . $fechaInicial->format('Y-m-d') . ' fue feriado ';
			}

			if ( $fechaInicial->format('l') === 'Sunday' ) {
				$domingos--;
				$msg .= '* El domingo ' . $fechaInicial->format('Y-m-d') . ' fue feriado ';
			}

			array_push($diasFeriados, $fechaInicial->format('Y-m-d'));

		}*/

		$fechaInicial->modify('+1 days'); //Incremento de la fecha inicial
		//alan cambie fecha inicial alan

	}


	return $arrayDias = ['feriados'     => (int)$feriados,

		//'diferencia dias'      => diferenciaEntreFechas($fechaInicial, $fechaFinal, $formatoDiferencia = 'a'),
		'sabados'      => (int)$sabados,
		'domingos'     => (int)$domingos,
		'totalDias'    => (int)($feriados + $sabados + $domingos),

		'diasFeriados' => [$diasFeriados][0],
		'msg'          => $msg];

}

}


function getHolidays($dateInitial, $dateEnd){

	global $db;
	$sql='SELECT  SUM(datediff(date_fin, date_debut)+1) holidays
	FROM llx_holiday
	WHERE date_debut BETWEEN "'.$dateInitial.'" AND "'.$dateEnd.'"';
	$resql = $db->query($sql);

	$holiD=[];
	if ($resql) {
		if ($db->num_rows($resql)) {
			$obj = $db->fetch_object($resql);
			$holiD=$obj;

		}

	}
	return $holiD->holidays;
}
