<?php
$control=base64_decode($_GET['control']);
if($control){
	include("../../library/library.php");
	/*	verificaci�n a nivel de usuario */
	verificaUsuario(1);
	verif_framework();
	
	$param= new manUrlv1();	
	$param->removePar('control');
	
	//	conexi�n a la BD 
	$conn = new db();
	$conn->open();

	switch($control){
		case 1: // Guardar
			guardar();   
			break;
	}
	//	cierra la conexi�n con la BD
	$conn->close();
}

function guardar(){
	global $conn,$param;
	/*recibo los parametro de la URL*/

	$destinoUpdate = "../admin/setearPeriodo_edicion.php".$param->buildPars(true);
	$destinoInsert = "../admin/setearPeriodo_edicion.php".$param->buildPars(true);

	include("guardar_tipoDato.php");
	
        
        if($param->getValuePar('clear')==1){
            
            $anno=getParam("f_id");
            $anno_nombre=getParam("Sx_periodo_nombre");
            $user_id=getSession("sis_userid");
            
            $sql ="UPDATE periodo SET peri_set=NULL;";
            $sql.="UPDATE periodo SET peri_set='*',
                                      peri_anno_nombre='$anno_nombre',
                                      usua_id=$user_id";
            $sql.=" WHERE peri_anno=$anno";   
            
        }elseif($param->getValuePar('clear')==2){//periodo de inventario
            
            
            $sql ="UPDATE periodo SET peri_set_inventario=NULL;";
            $sql.="UPDATE periodo SET peri_set_inventario='*',
                                      usua_id=".getSession("sis_userid");
            $sql.=" WHERE peri_anno=".getParam("f_id");   
            
        }elseif($param->getValuePar('clear')==3){//periodo de cuadro de necesidades
            $sql ="UPDATE periodo SET peri_set_cdronec=NULL;";
            $sql.="UPDATE periodo SET peri_set_cdronec='*',
                                      usua_id=".getSession("sis_userid");
            $sql.=" WHERE peri_anno=".getParam("f_id");            
        }

	
	$conn->execute($sql);
	$error=$conn->error();
	if($error){
		alert($error);				
        }else {
		// muestra mensaje noticia del la base de datos, pero no detiene la ejecucion						
		$notice=$conn->notice();
		if($notice) 
			alert($notice,0);				
	}

	
	if ($valueKey) {// modificacion
		$destino=$destinoUpdate;
		$last_id=$valueKey;
	}
	else{
		$destino=$destinoInsert;	
		$last_id=$conn->lastid();						
	}

	/*a�ado el id del registro ingresado*/
	if(strpos($destino, "?")>0){
		$destino.="&id=$last_id";
        }else{
		$destino.="?id=$last_id";
        }	
	redirect($destino,"content");							
	
}



class setPeriodo
{
	private $existe;
	private $title;
	private $field;
	
	function clsTipDoc($id='',$title=''){
		$this->id=$id;
		$this->title=$title;	
	}
		
	function getSql(){
		$sql = "SELECT * FROM
					periodo
				WHERE peri_anno=$this->id";

		return($sql);
	}

	function setDatos(){
		global $conn;						
		
		$sql = $this->getSql();
		$rs = new query($conn, $sql);

		if ($this->field=$rs->getrow()){
			$this->existe=1;
		}
		else $this->existe=0;
	}

	function field($nameField){
		return $this->field["$nameField"];
	}

	function existeDatos(){
		return($this->existe);
	}


	function getTitle(){
		return $this->title;
	}
	
	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	
}
