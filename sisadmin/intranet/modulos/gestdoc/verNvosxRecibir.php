<?php
require_once("../../library/library.php");
require_once("getAcumDespacho_class.php");


$depeid=getSession("sis_depeid");
$userid=getSession("sis_userid");
$pers_id=getSession("sis_persid");

if(isset($userid) && isset($pers_id)){
    $conn = new db();
    $conn->open();

    $despDepen=new getAcumDespachosDependencia();
    $despDepen->whereVarios($pers_id);
    $sql = $despDepen->getSQL();
    
    $rs = new query($conn, $sql);
    $rs->getrow() ;
    $nuevos=$rs->field('depe_acum_despachos_porrecibir') ;

    $conn->close();
    if($nuevos>0){
        //$link="<span id='notification_count'>$nuevos</span>";
        $jsondata["success"] = true;
        $jsondata["acum_xrecibir"] = $nuevos;
        //$jsondata["acum_x_recibir"] = $depe_acum_despachos_porrecibir;
    }else{ 
        $jsondata["success"] = true;
        $jsondata["acum_xrecibir"] = 0;
        //$jsondata["acum_x_recibir"] = 0;
    }
}else{
    $jsondata["success"] = false;
    $jsondata["acum_xrecibir"] = 0;
    //$jsondata["acum_x_recibir"] = 0;
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata, JSON_FORCE_OBJECT);
