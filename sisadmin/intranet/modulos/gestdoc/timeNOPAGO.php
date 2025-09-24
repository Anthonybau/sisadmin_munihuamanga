<?php
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

$cadena1=date("Y-m-d H:i:s");
$cadena2=SIS_FCIERRE_NOPAGO;
//echo $cadena1.' '.$cadena2;

$horaInicio = new DateTime($cadena1);
$horaTermino = new DateTime($cadena2);

if($cadena2>$cadena1){
    $interval = $horaInicio->diff($horaTermino);
    $resp['mensaje'] = "ESTA PLATAFORMA SE CERRARA EN ".$interval->format('%a DIA(S) %H HORAS %i MINUTOS %s SEGUNDOS').", POR FALTA DE PAGO";    
    
}else{
    $resp['mensaje'] = "PLATAFORMA CERRADA POR FALTA DE PAGO";    
}


$resp['respuesta'] = "ok";

echo json_encode($resp);