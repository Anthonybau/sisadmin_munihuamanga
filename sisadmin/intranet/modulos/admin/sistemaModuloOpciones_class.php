<?php
require_once($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/clases/entidad.php");


class sistemaModulo extends selectSQL {
	function __construct(){
		$this->sql = "SELECT DISTINCT a.simo_id as tree_id,
                                              '.|<b>'||a.simo_descripcion||'</b>' AS tree_text
                                FROM admin.sistema_modulo a
                                LEFT join admin.sistema_modulo_opciones b on a.simo_id = b.simo_id
                                LEFT join admin.sistema c on c.sist_id = a.sist_id
                                LEFT JOIN admin.perfilu_modulo_menu d ON d.smop_id=b.smop_id 
                                LEFT JOIN admin.perfilu_modulo e ON e.pemo_id=d.pemo_id
                                LEFT JOIN admin.usuario_perfil f ON e.perf_id=f.perf_id ";
	}

	function whereSistID($SistId){
		$this->addWhere("c.sist_id='$SistId'");	
	}
        
        function whereUserID($usersId){
		$this->addWhere("f.usua_id=$usersId");	
	}
         
        function whereAcceso($sistAcceso){
		$this->addWhere("b.smop_acceso=$sistAcceso");	
	}
        
	function orderUno(){
		$this->addOrder("a.simo_id");		
	}

}


class sistemaModuloOpciones extends selectSQL {
	function __construct($op=1){
            if($op==1){
		$this->sql = "SELECT DISTINCT a.smop_id as tree_id,
                                             '..|'||a.smop_descripcion||'|'||COALESCE(a.smop_page,'')||'|||content' AS tree_text
                                    FROM admin.sistema_modulo_opciones a
                                    LEFT JOIN admin.sistema_modulo b on a.simo_id = b.simo_id
                                    LEFT JOIN admin.sistema c on c.sist_id = b.sist_id
                                    LEFT JOIN admin.perfilu_modulo_menu d ON d.smop_id=a.smop_id 
                                    LEFT JOIN admin.perfilu_modulo e ON e.pemo_id=d.pemo_id
                                    LEFT JOIN admin.usuario_perfil f ON e.perf_id=f.perf_id ";
            }else{
                $this->sql = "SELECT DISTINCT a.smop_id,
                                              a.smop_page,
                                              a.smop_descripcion AS smop_descripcion 
                                    FROM admin.sistema_modulo_opciones a
                                    LEFT JOIN admin.sistema_modulo b on a.simo_id = b.simo_id
                                    LEFT JOIN admin.sistema c on c.sist_id = b.sist_id
                                    LEFT JOIN admin.perfilu_modulo_menu d ON d.smop_id=a.smop_id 
                                    LEFT JOIN admin.perfilu_modulo e ON e.pemo_id=d.pemo_id
                                    LEFT JOIN admin.usuario_perfil f ON e.perf_id=f.perf_id ";
            }
	}
        
        function whereID($smop_id){
		$this->addWhere("a.smop_id='$smop_id'");	
	}
        
	function whereSistID($SistId){
		$this->addWhere("b.sist_id='$SistId'");	
	}
        
        function whereUserID($usersId){
		$this->addWhere("f.usua_id=$usersId");	
	}
        
        function whereUserID2($usersId){
		$this->addWhere("(f.usua_id=$usersId OR a.smop_id='010110ESCALAFON')");	
	}
        
        function whereAcceso($sistAcceso){
		$this->addWhere("a.smop_acceso IN ($sistAcceso)");	
	}
        
	function orderUno(){
		$this->addOrder("a.smop_id");		
	}

}