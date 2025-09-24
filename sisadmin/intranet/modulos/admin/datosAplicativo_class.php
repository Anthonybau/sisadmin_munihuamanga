<?php
require_once('../../library/clases/entidad.php');

class datosAplicativo extends entidad
{
	function __construct($id='',$title=''){
            $this->setTable='admin.aplicativo'; //nombre de la tabla
            $this->setKey='apli_id'; //campo clave
            $this->valueKey=getParam("f_id"); //valor del campo clave
            $this->typeKey="Integer"; //tipo  de dato del campo clave
            $this->id=$id;
            $this->title=$title;

            /* Destinos luego de actulizar, agregar o eliminar un registro */
            $this->destinoUpdate='datosAplicativo_edicion.php?clear=1';

	}
    
	function getSql(){
		$sql = "SELECT * FROM
				admin.aplicativo
				WHERE apli_id=$this->id";

		return($sql);
	}

	function addField(&$sql){
            
            if ($_POST["hx_apli_gestdoc"]){
                $sql->addField("apli_gestdoc", 1, "Number");
            }else{
                $sql->addField("apli_gestdoc", 0, "Number");
            }
            
            if ($_POST["hx_apli_gestleg"]){
                $sql->addField("apli_gestleg", 1, "Number");
            }else{
                $sql->addField("apli_gestleg", 0, "Number");
            }
            
            if ($_POST["hx_apli_sislogal"]){
                $sql->addField("apli_sislogal", 1, "Number");
            }else{
                $sql->addField("apli_sislogal", 0, "Number");
            }
            
            if ($_POST["hx_apli_siaf"]){
                $sql->addField("apli_siaf", 1, "Number");
            }else{
                $sql->addField("apli_siaf", 0, "Number");
            }
            
            if ($_POST["hx_apli_control_igv"]){
                $sql->addField("apli_control_igv", 1, "Number");
            }else{
                $sql->addField("apli_control_igv", 0, "Number");
            }
            
            if ($_POST["hx_apli_gestcne"]){
                $sql->addField("apli_gestcne", 1, "Number");
            }else{
                $sql->addField("apli_gestcne", 0, "Number");
            }

            if ($_POST["hx_apli_efact"]){
                $sql->addField("apli_efact", 1, "Number");
            }else{
                $sql->addField("apli_efact", 0, "Number");
            }

            if ($_POST["hx_apli_siscore"]){
                $sql->addField("apli_siscore", 1, "Number");
            }else{
                $sql->addField("apli_siscore", 0, "Number");
            }
            
//            if ($_POST["hx_apli_siscore_despliega_conceptos"]){
//                $sql->addField("apli_siscore_despliega_conceptos", 1, "Number");
//            }else{
//                $sql->addField("apli_siscore_despliega_conceptos", 0, "Number");
//            }
            
//            if ($_POST["hx_apli_siscore_elige_conceptos_min"]){
//                $sql->addField("apli_siscore_elige_conceptos_min", 1, "Number");
//            }else{
//                $sql->addField("apli_siscore_elige_conceptos_min", 0, "Number");
//            }
            
            if ($_POST["hx_apli_laboratorio"]){
                $sql->addField("apli_laboratorio", 1, "Number");
            }else{
                $sql->addField("apli_laboratorio", 0, "Number");
            }

//            if ($_POST["hx_apli_pedidos"]){
//                $sql->addField("apli_pedidos", 1, "Number");
//            }else{
//                $sql->addField("apli_pedidos", 0, "Number");
//            }
            

            if ($_POST["hx_apli_imprime_credenciales_laboratorio"]){
                $sql->addField("apli_imprime_credenciales_laboratorio", 1, "Number");
            }else{
                $sql->addField("apli_imprime_credenciales_laboratorio", 0, "Number");
            }
            
            $sql->addField("apli_actualfecha", "now()", "String");
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
        $dml=new datosAplicativo();

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