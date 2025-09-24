<?
/*
	Modelo de transaccion para processar v�rios registros
*/
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/*
	verificaci�n del nivel de usuario
*/
verificaUsuario(1);

/*
	conexi�n a la base de datos
*/
$conn = new db();
$conn->open();

/*
	captura y prepara la lista de registros
*/ 
$lista = getParam("f_destino");
$id = getParam("f_id");
$SistId=getSession("sist_id");
$usua_idcrea=getSession("sis_userid");

$lista_origen = "'".implode("','",getParam("f_origem"))."'";

/*
	proceso de grabaci�n,
*/


$sql = "delete from usuario_modulo where sist_id in ($lista_origen) and usua_id=$id";

$rs = new query($conn, $sql);	
if (is_array($lista)) {
	//$lista = explode(",",$lista);
	for ($i=0; $i<sizeof($lista); $i++) {
			$sql="insert into usuario_modulo (usua_id,sist_id,usua_idcrea) values($id,'$lista[$i]',$usua_idcrea)";
			$rs = new query($conn, $sql);	
	}
}

redirect("adminUsuario_permisos.php?id=$id ","content");
/*
	cierra la conexi�n a la base de dados
*/
$conn->close();
?>