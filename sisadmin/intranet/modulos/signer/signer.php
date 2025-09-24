<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");


class signer extends entidad {

	function __construct(){
		$this->setTable='signer.signer'; //nombre de la tabla
		$this->setKey='sign_id'; //campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
	}

    
        function insert($formdata){
		global $conn;
                
		$sign_dni=$formdata['sign_dni'];
                $sign_posicion=$formdata['sign_posicion'];
                $sign_tipo_razon=$formdata['sign_tipo_razon'];
                $sign_job=$formdata['sign_job'];
                $sign_path_extract=$formdata['sign_path_extract'];
                
		// objeto para instanciar la clase sql
		$sql = new UpdateSQL();				
		$sql->setTable($this->setTable);
		$sql->setKey($this->setKey, '', $this->typeKey);
	
                $sql->setAction("INSERT");
                
                $sql->addField('sign_dni',$sign_dni, "String");
                $sql->addField('sign_posicion',$sign_posicion, "Number");
                $sql->addField('sign_tipo_razon',$sign_tipo_razon, "Number");
                $sql->addField('sign_job',$sign_job, "String");
                $sql->addField('sign_path_extract',$sign_path_extract, "String");
                $sql->addField('usua_id', getSession("sis_userid"), "Number");

		/* Aqu� puedo agregar otros campos a la sentencia SQL */
		$this->addField($sql);

		/* Ejecuto el SQL */
                $sqlCommand=$sql->getSQL();
                //echo $sql->getSQL();
		$signer_id=$conn->execute("$sqlCommand  RETURNING $this->setKey");
                               
		$error=$conn->error();
		if($error){ 
                    $return['success']=0;
                    $return['mensaje']=$error;
		}else{
                    $return['success']=$signer_id;
                    $return['mensaje']='Exito';
                }
                return($return);
	}        

	function insertItem($formdataItem){
		global $conn;
                
                $sign_id=$formdataItem['sign_id'];
                $sifi_file_origen=$formdataItem['sifi_file_origen']; 
                $sifi_name_file=$formdataItem['sifi_name_file']; 
                $sifi_path_destino=$formdataItem['sifi_path_destino']; 
                $sifi_file_zip=$formdataItem['sifi_file_zip']; 
                $sifi_indice_zip=$formdataItem['sifi_indice_zip']; 
                
		// objeto para instanciar la clase sql
		$sql = new UpdateSQL();				
		$sql->setTable('signer.signer_files');
		$sql->setKey('sifi_id', '', "Number");
                $sql->setAction("INSERT");
                
                $sql->addField('sign_id',$sign_id, "Number");
                $sql->addField('sifi_file_origen',$sifi_file_origen, "String");
                $sql->addField('sifi_path_destino',$sifi_path_destino, "String");
                $sql->addField('sifi_name_file',$sifi_name_file, "String");
                
                if($sifi_file_zip){
                    $sql->addField('sifi_file_zip',$sifi_file_zip, "String");
                    $sql->addField('sifi_indice_zip',$sifi_indice_zip, "Number");
                }
                
                $sql->addField('usua_id', getSession("sis_userid"), "Number");
                
		/* Aqu� puedo agregar otros campos a la sentencia SQL */
		$this->addField($sql);

		/* Ejecuto el SQL */
                $sqlCommand=$sql->getSQL();
                //echo $sql->getSQL();
		$signer_item_id=$conn->execute("$sqlCommand  RETURNING $this->setKey");
                               
		$error=$conn->error();
		if($error){ 
                    $return['success']=0;
                    $return['mensaje']=$error;
		}else{
                    $return['success']=$signer_item_id;
                    $return['mensaje']='Exito';
                }
                return($return);
	}
                 
        function desbloquear(){
		global $conn;
		
		/* Ejecuto el SQL */
                $sqlCommand="SELECT func_desbloquear FROM signer.func_desbloquear(".getSession("sis_userid")."::INTEGER);";
		$desbloqueado=$conn->execute("$sqlCommand");                               
                return($desbloqueado);                
	}        
                
}

class signer_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.*
                		FROM signer.signer a
				";	
	}
	
	function whereID($id){
		$this->addWhere("a.sign_id=$id");
	}
 
	function whereUsuaID($usua_id){
		$this->addWhere("a.usua_id=$usua_id");
	}
        function whereHoy(){
		$this->addWhere("a.sign_fregistro::DATE=NOW()::DATE");
	}
	function orderUno(){
            $this->addOrder("a.sign_id DESC");
	}
}

class signerFiles_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.*
                		FROM signer.signer_files a
                		LEFT JOIN signer.signer b ON a.sign_id=b.sign_id
				";	
	}

	function whereID($id){
		$this->addWhere("a.sifi_id=$id");
	}
        
	function wherePadreID($sign_id){
		$this->addWhere("a.sign_id=$sign_id");
	}
        
	function orderUno(){
            $this->addOrder("a.sifi_id DESC");
	}
}

class signerEmails_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.*
                		FROM signer.signer_emails a
				";	
	}

	function whereID($id){
		$this->addWhere("a.siem_id=$id");
	}
        
	function wherePadreID($sign_id){
		$this->addWhere("a.sign_id=$sign_id");
	}
        
	function orderUno(){
            $this->addOrder("a.siem_id DESC");
	}
}
