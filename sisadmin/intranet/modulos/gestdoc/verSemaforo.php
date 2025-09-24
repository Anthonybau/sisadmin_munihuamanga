<?php
require_once("../../library/library.php");
require_once("procesoDespacho_class.php");

$userid=getSession("sis_userid");
$pers_id=getSession("sis_persid");

if(isset($userid) && isset($pers_id)){
    $conn = new db();
    $conn->open();

    $semaforo=new despachoProceso_SQLlista();
    $semaforo->whereNOAdjuntados();
    $semaforo->whereProceso();
    $semaforo->whereUsuaRecibeID($userid);
    $sql = "SELECT SUM(a.semaforo1) AS semaforo1,
                   valor_semaforo1, 
                   SUM(a.semaforo2) AS semaforo2,
                   valor_semaforo2,
                   SUM(a.semaforo3) AS semaforo3                   
            FROM (".$semaforo->getSQL().") AS a
            GROUP BY valor_semaforo1,
                     valor_semaforo2
            ";

    $rs = new query($conn, $sql);
    $hay_registros=$rs->numrows();
    if( $hay_registros>0 ){
        $rs->getrow();
        $jsondata["success"] = true;
        $jsondata["acum_semaforo1"] = $rs->field("semaforo1");
        $jsondata["acum_semaforo2"] = $rs->field("semaforo2");
        $jsondata["acum_semaforo3"] = $rs->field("semaforo3");
        $jsondata["valor_semaforo1"] = $rs->field("valor_semaforo1");
        $jsondata["valor_semaforo2"] = $rs->field("valor_semaforo2");
    }else{ 
        $jsondata["success"] = true;
        $jsondata["acum_semaforo1"] = 0;
        $jsondata["acum_semaforo2"] = 0;
        $jsondata["acum_semaforo3"] = 0;
    }
    $conn->close();

}else{
    $jsondata["success"] = false;
    $jsondata["acum_semaforo1"] = 0;
    $jsondata["acum_semaforo2"] = 0;
    $jsondata["acum_semaforo3"] = 0;
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata, JSON_FORCE_OBJECT);