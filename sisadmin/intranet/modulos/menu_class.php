<?php
require_once("../../intranet/library/clases/entidad.php");

class modulos_SQLlista extends selectSQL {
        var $usua_id;
	function __construct(){

		$this->sql = "SELECT  DISTINCT a.sist_id,
                                              a.sist_descripcion,
                                              a.sist_breve,
                                              a.sist_activo,
                                              sist_image_on,
                                              sist_image_off
                                 FROM admin.sistema a
                                 LEFT JOIN admin.perfilu_modulo b ON a.sist_id = b.sist_id
                                 LEFT JOIN admin.usuario_perfil c ON b.perf_id = c.perf_id
			";
	}

	function whereUser($usersId){
                $this->usua_id=$usersId;
		if($usersId==1){}
		else{
                    $this->addWhere("c.usua_id=".$usersId);
		}
	}

	function whereActivo(){
		$this->addWhere("a.sist_activo=1");
	}

        function whereNOTModulo($sist_id){
		$this->addWhere("a.sist_id NOT IN ($sist_id)");	
	}
        
	function getSQL_cboxModulo(){
		$sql="SELECT a.sist_id AS id,SUBSTR(a.sist_descripcion,1,30) AS descrip
				FROM (".$this->getSQL().") a 
				";
		return $sql;
	}
	
	function orderUno(){
		$this->addOrder("1");		
	}


}


class opcionModulo_SQLlista extends selectSQL {
        var $usua_id;
        var $sist_id;
	function __construct(){

		$this->sql = "SELECT DISTINCT b.simo_id,
                                              a.smop_id,
                                              a.smop_activo,
                                              b.simo_descripcion as modulo,
                                              a.smop_descripcion,
                                              b.simo_page,
                                              a.smop_page,
                                              c.sist_activo
                              FROM  admin.sistema_modulo_opciones a
                                    LEFT JOIN admin.sistema_modulo	  b ON a.simo_id=b.simo_id
                                    LEFT JOIN admin.sistema 		  c ON b.sist_id=c.sist_id    
                                    LEFT JOIN admin.perfilu_modulo_menu d ON d.smop_id=a.smop_id 
                                    LEFT JOIN admin.perfilu_modulo 	  e ON e.pemo_id=d.pemo_id
                                    LEFT JOIN admin.usuario_perfil 	  f ON e.perf_id=f.perf_id
        ";
	}

	function whereUser($usersId,$SistId){

                $this->usua_id=$usersId;
                $this->sist_id=$SistId;

		if($usersId==1){
                    $this->addWhere("(c.sist_id='$SistId' AND a.smop_acceso!=9 AND a.smop_activo=1)");
		}
		elseif($usersId>1){
                    $this->addWhere("(c.sist_id='$SistId' AND  f.usua_id=$usersId AND a.smop_acceso!=9 AND a.smop_activo=1) OR (c.sist_id='$SistId' AND a.smop_acceso=1 AND a.smop_activo=1)");
		}
		else{
                    $this->addWhere("c.sist_id='$SistId' AND a.smop_acceso=1 AND a.smop_activo=1");
		}
	}

	function wherePerfilUsuario($usersId){
                $this->addWhere("f.usua_id=$usersId");
	}

	function wherePageOpcion($pageOpcion){
                $this->addWhere("a.smop_page ILIKE '%$pageOpcion%'");
	}
        
        function whereActivo(){
		$this->addWhere("c.sist_activo=1");			
	}
	
        
        function whereNOTAcceso($sistAcceso){
		$this->addWhere("a.smop_acceso NOT IN ($sistAcceso)");	
	}
        
	function orderUno(){
		$this->addOrder("1,2");		
	}

}

class opcionMenuFiltro_SQLlista extends selectSQL {
	function __construct(){

		$this->sql = "SELECT DISTINCT b.simo_id,
                                              a.smop_id,
                                              b.simo_descripcion as modulo,
                                              a.smop_descripcion,
                                              b.simo_page,
                                              a.smop_page,
                                              c.sist_activo
                              FROM  admin.sistema_modulo_opciones a
                                    LEFT JOIN admin.sistema_modulo	  b ON a.simo_id=b.simo_id
                                    LEFT JOIN admin.sistema 		  c ON b.sist_id=c.sist_id
        ";
	}

	function whereActivo(){
		$this->addWhere("c.sist_activo=1");
	}

	function whereModulo($sist_id){
		$this->addWhere("c.sist_id='$sist_id'");
	}

	function whereFiltroLink($filtro1='',$filtro2=''){
		$this->addWhere("(a.smop_page ILIKE '%$filtro1%' OR a.smop_page ILIKE '%$filtro2%')");
	}

        function getSQLbox(){
            return("SELECT smop_id,smop_descripcion FROM (".$this->getSQL().") AS a ORDER BY 1");
        }

        function orderUno(){
		$this->addOrder("1,2");
	}

}