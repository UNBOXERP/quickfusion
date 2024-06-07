<?php
// Load Dolibarr environment
//require '../../../main.inc.php';
//require_once DOL_DOCUMENT_ROOT . '/custom/quickbooks/class/sync.php';

$rawPost = file_get_contents('php://input');
//Convertir el JSON en un array de PHP
$datos = json_decode($rawPost);
switch ($datos->eventNotifications[0]->dataChangeEvent->entities[0]->name){
	case "Item":
		   switch ($datos->eventNotifications[0]->dataChangeEvent->entities[0]->operation){
			case "Create":
			   $id = $datos->eventNotifications[0]->dataChangeEvent->entities[0]->data->id;
			   $syncBook = new Syncqbooks();
			   $productos=$syncBook->GetProduct($id);
			   $syncBook->CreateProductDolibarr($productos[0]);
		   }

			break;
	case "Payment":



}

//crear un txt con la fecha actual
$fecha = date("d-m-Y");
$nombre_archivo = "log".$fecha.".txt";
$mensaje = "Este mensaje es para comprobar que se crea el archivo";
if(file_exists($nombre_archivo))
{
	$mensaje = "El Archivo $nombre_archivo se ha modificado";
}
else
{
	$mensaje = "El Archivo $nombre_archivo se ha creado";
}
$archivo = fopen($nombre_archivo, "a");
fwrite($archivo, $mensaje . "\n");
fclose($archivo);
