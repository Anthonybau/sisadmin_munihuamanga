<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class clsEmpresaRUC_SQLlista extends selectSQL {
	function __construct(){
		$this->sql = "SELECT a.*
		 			  FROM admin.empresa_ruc AS a
				";
	}

	function whereID($id){
		$this->addWhere("a.emru_id=$id");	
	}

	function whereDepeID($depe_id){
		$this->addWhere("a.depe_id=$depe_id");	
	}
        
	function whereRUC($ruc){
		$this->addWhere("a.emru_ruc='$ruc'");	
	}
        
        function whereToken(){
		$this->addWhere("emru_api_sunat_id IS NOT NULL 
                                    AND emru_api_sunat_clave IS NOT NULL ");	
	}        
        
	function orderUno(){
		$this->addOrder("a.emru_id");		
	}

        function getSQL_cbox(){
		$sql="SELECT a.emru_id,a.emru_ruc||' '||a.emru_razon_social||COALESCE('-'||a.emru_establecimiento,'')
				FROM (".$this->getSQL().") AS a
                      ORDER BY 1";
		return $sql;
	}
}

