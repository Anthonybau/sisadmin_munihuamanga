<?php
require_once("../../library/library.php");
require_once("getAcumDespacho_class.php");

$userid=getSession("sis_userid");
$pers_id=getSession("sis_persid");

if(isset($userid) && isset($pers_id)){
    $conn = new db();
    $conn->open();

    $despDepen=new getAcumDespachosxFirmarUsuario();
    $despDepen->whereUserID($userid);
    $sql = $despDepen->getSQL();
    
    $rs = new query($conn, $sql);
    $rs->getrow() ;
    $xFirmar=$rs->field('usua_acum_pend_firmar') ;
    $conn->close();
    if($xFirmar>0){
        //$link="<span id='notification_xFirmar'>$xFirmar</span>";
        $jsondata["success"] = true;
        $jsondata["acum_xfirmar"] = $xFirmar;
    }else{ 
        $jsondata["success"] = true;
        $jsondata["acum_xfirmar"] = 0;
    }
}else{
    $jsondata["success"] = false;
    $jsondata["acum_xfirmar"] = 0;
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata, JSON_FORCE_OBJECT);