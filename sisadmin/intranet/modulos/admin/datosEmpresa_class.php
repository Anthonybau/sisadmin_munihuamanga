<?php
require_once('../../library/clases/entidad.php');

class datosEmpresa extends entidad
{
	function __construct($id='',$title=''){
            $this->setTable='admin.empresa'; //nombre de la tabla
            $this->setKey='empr_id'; //campo clave
            $this->valueKey=getParam("f_id"); //valor del campo clave
            $this->typeKey="Integer"; //tipo  de dato del campo clave
            $this->id=$id;
            $this->title=$title;

            /* Destinos luego de actulizar, agregar o eliminar un registro */
            $this->destinoUpdate='datosEmpresa_edicion.php?clear=1';

	}
    
	function getSql(){
		$sql = "SELECT * FROM
				admin.empresa
				WHERE empr_id=$this->id";

		return($sql);
	}

	function addField(&$sql){
            $sql->addField("empr_actualfecha", "now()", "String");
	}
        
        function guardar(){
            global $conn,$param;
            /*recibo los parametro de la URL*/

            $destinoUpdate=$this->destinoUpdate.$param->buildPars(true);	

            $sql = new UpdateSQL();

            $sql->setTable($this->setTable);
            $sql->setKey($this->setKey, $this->valueKey, $this->typeKey);

            include("../guardar_tipoDato.php");
            $sql->setAction("UPDATE");
            $this->addField($sql);

                    /* Ejecuto el SQL */
            $sqlCommand=$sql->getSQL();
            //echo $sqlCommand;
            //exit(0);
            $padre_id=$conn->execute("$sqlCommand  RETURNING $this->setKey");

            $error=$conn->error();
            if($error){
                alert($error);				
            }else {
                // muestra mensaje noticia del la base de datos, pero no detiene la ejecucion						
                $notice=$conn->notice();
                if($notice){
                    alert($notice,0);
                }
            }
            $destinoUpdate.="&id=$padre_id";
            $destino=$destinoUpdate;

            redirect($destino,"content");								
        }


	function getNameFile()
	{
            return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	
}

$control=base64_decode($_GET['control']);
if($control){
	include("../../library/library.php");
	/*	verificacion a nivel de usuario */
	verificaUsuario(1);
	verif_framework();
	
	$param= new manUrlv1();	
	$param->removePar('control');
	
	//	conexion a la BD 
	$conn = new db();
	$conn->open();
        $dml=new datosEmpresa();

        switch($control){
            case 1: // Guardar
                $password1=getParam("__password");
                $password2=date('Ymd');
                if($password1!=$password2){
                    alert("Contraseña de Grabación Incorrecta!",0);    
                }else{
                    $dml->guardar();
                }
                break;
                
        }
	//	cierra la conexi�n con la BD
	$conn->close();
}
