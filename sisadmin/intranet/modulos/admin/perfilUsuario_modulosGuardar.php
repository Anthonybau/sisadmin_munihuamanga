<?
/*
	Modelo de transaccion para processar v�rios registros
*/
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
/* Cargo mi clase Base */
include("perfilUsuario_class.php");

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
$usua_id=getSession("sis_userid");

$lista_origen = "'".implode("','",getParam("f_origem"))."'";

/*
	proceso de grabaci�n,
*/


$sql = "DELETE FROM perfilu_modulo WHERE sist_id in ($lista_origen) and perf_id=$id";

$rs = new query($conn, $sql);
	
if (is_array($lista)) {
	//$lista = explode(",",$lista);
	for ($i=0; $i<sizeof($lista); $i++) {
			$sql="insert into perfilu_modulo (perf_id,sist_id,usua_id) values($id,'$lista[$i]',$usua_id)";
			$rs = new query($conn, $sql);	
	}
}

redirect("perfilUsuario_permisos.php?relacionamento_id=$id ","content");
/*
	cierra la conexi�n a la base de dados
*/
$conn->close();
?>