<?php
require_once("../../library/library.php");
require_once("getAcumDespacho_class.php");


$depeid=getSession("sis_depeid");
$userid=getSession("sis_userid");
$pers_id=getSession("sis_persid");

if(isset($userid) && isset($pers_id)){
    $conn = new db();
    $conn->open();
    $despUsers=new getAcumDespachosUsuario();
    $despUsers->whereUserID($userid);
                
    $sql = $despUsers->getSQL();

    $rs = new query($conn, $sql);
    $rs->getrow() ;
    $usua_acum_despachos_porrecibir=$rs->field('usua_acum_despachos_porrecibir') ;
    $usua_acum_despachos_enproceso=$rs->field('usua_acum_despachos_enproceso') ;

    $despDepen=new getAcumDespachosDependencia();
    $despDepen->whereVarios($pers_id);
                
    $sql = $despDepen->getSQL();
    
    $rs = new query($conn, $sql);
    $rs->getrow() ;
    $depe_acum_despachos_porrecibir=$rs->field('depe_acum_despachos_porrecibir') ;
    $depe_acum_despachos_enproceso=$rs->field('depe_acum_despachos_enproceso') ;

    //$link=addLink(NAME_EXPEDIENTE."s por Recibir: ".$usua_acum_despachos_porrecibir.'/'.$depe_acum_despachos_porrecibir,"javascript:abreRecibir()","Click aqu&iacute; para Recibir ".NAME_EXPEDIENTE,"");
    $link =addLink("(U/D) ","javascript:setDespachosAcumulados()","","header").
    addLink(substr(NAME_EXPEDIENTE,0,1).' en Proceso: '.$usua_acum_despachos_enproceso.'/'.$depe_acum_despachos_enproceso,"javascript:abreEnProceso()","Click aqu&iacute; para Ver Documentos en Proceso ".NAME_EXPEDIENTE,"").
    addLink(" x Recib: ".$usua_acum_despachos_porrecibir.'/'.$depe_acum_despachos_porrecibir,"javascript:abreRecibir()","Click aqu&iacute; para Recibir ".NAME_EXPEDIENTE,"");
    //$link =NAME_EXPEDIENTE." Por Recibir: ".$usua_acum_despachos_porrecibir.'/'.$depe_acum_despachos_porrecibir;
    $conn->close();

    echo $link ;
}else{
    echo "";
}