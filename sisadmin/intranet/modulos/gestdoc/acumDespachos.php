<?
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/modulos/gestdoc/setAcumDespacho_class.php");

$conn = new db();
$conn->open();

$depeid=getSession("sis_depeid");
$userid=getSession("sis_userid");
$pers_id=getSession("sis_persid");
if(isset($userid) && isset($pers_id)){

		/* Sql a ejecutar */
                $setNotiEnProcesoUsuario=new setNotiDespachosEnProcesoUsuario($pers_id);
                $setNotiEnProcesoUsuario->whereUserID($userid);
                //$setNotiZero->NOAdmin();
                
                $setNotiEnProcesoDependencia=new setNotiDespachosEnProcesoDependencia();
                $setNotiEnProcesoDependencia->whereVarios($pers_id);

                $setNotiDespachosPorRecibirUsuario=new setNotiDespachosPorRecibirUsuario($pers_id);
                $setNotiDespachosPorRecibirUsuario->whereUserID($userid);
                
                $setNotiDespachosPorRecibirDependencia=new setNotiDespachosPorRecibirDependencia();
                $setNotiDespachosPorRecibirDependencia->whereVarios($pers_id);
                
                $sqlCommand =$setNotiEnProcesoUsuario->getSQL().";";
                $sqlCommand .=$setNotiEnProcesoDependencia->getSQL().";";
                $sqlCommand .=$setNotiDespachosPorRecibirUsuario->getSQL().";";
                $sqlCommand .=$setNotiDespachosPorRecibirDependencia->getSQL().";";

                /* Ejecuto la sentencia */
                //echo $sqlCommand;
                
		$padre_id=$conn->execute($sqlCommand);
                echo 'Notificador Actualizado...!!';
		$error=$conn->error();
		//if($error) alert($error);
                //else alert('Proceso Terminado!');

}
$conn->close();
