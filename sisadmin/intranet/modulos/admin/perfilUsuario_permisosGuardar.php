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
$usua_id=getSession("sis_userid");
$pemo_id=getParam("_pemo_id");

/*
	proceso de grabaci�n,
*/

//$sql = "delete from usuario_menu where smop_id in (select a.smop_id "
//	  ."from usuario_menu a "
//	  ."left join sistema_modulo_opciones b on a.smop_id=b.smop_id "
//	  ."left join sistema_modulo          c on b.simo_id=c.simo_id "
//	  ."left join sistema                 d on d.sist_id=c.sist_id "
//	  ."where d.sist_id='".$SistId."') and usua_id=$id";

$lista_orig=getParam("f_origem");
if (is_array($lista_orig)) {	
	$lista_origen = "'".implode("','",$lista_orig)."'";
 	$sql = "DELETE FROM perfilu_modulo_menu WHERE pemo_id=$pemo_id AND smop_id in ($lista_origen) ";
	$rs = new query($conn, $sql);
}


	
if (is_array($lista)) {
	//$lista = explode(",",$lista);
	for ($i=0; $i<sizeof($lista); $i++) {
			$sql="INSERT INTO perfilu_modulo_menu (pemo_id,smop_id,usua_id) values($pemo_id,'$lista[$i]',$usua_id)";
			$rs = new query($conn, $sql);	
	}
}

	
redirect("perfilUsuario_permisos.php?relacionamento_id=$id","content");
/*
	cierra la conexi�n a la base de dados
*/
$conn->close();
?>