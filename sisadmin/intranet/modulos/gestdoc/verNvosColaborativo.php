<?php
require_once("../../library/library.php");
require_once("colaborativoDespacho_class.php");

$userid=getSession("sis_userid");
$pers_id=getSession("sis_persid");

if(isset($userid) && isset($pers_id)){
    $conn = new db();
    $conn->open();

    $colaborativo=new colaborativo_SQLlista();
    $colaborativo->whereAbierto();
    $colaborativo->wherePdlaID($pers_id);
    $sql = $colaborativo->getSQL();
    
    $rs = new query($conn, $sql);
    $colaborativos=$rs->numrows();
    $conn->close();
    if( $colaborativos>0 ){
        $jsondata["success"] = true;
        $jsondata["acum_colaborativo"] = $colaborativos;
    }else{ 
        $jsondata["success"] = true;
        $jsondata["acum_colaborativo"] = 0;
    }
}else{
    $jsondata["success"] = false;
    $jsondata["acum_colaborativo"] = 0;
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata, JSON_FORCE_OBJECT);