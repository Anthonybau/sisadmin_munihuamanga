<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class getAcumDespachosUsuario extends selectSQL {
        function __construct(){
		$this->sql="SELECT  a.usua_acum_despachos_porrecibir,
                                    a.usua_acum_despachos_enproceso 
                        FROM admin.usuario a";
	}

	function whereUserID($user_id){
		$this->addWhere("a.usua_id=$user_id");
	}
}

class getAcumDespachosDependencia extends selectSQL {
        function __construct(){
		$this->sql="SELECT  SUM(a.depe_acum_despachos_porrecibir) AS depe_acum_despachos_porrecibir,
                                    SUM(a.depe_acum_despachos_enproceso) AS depe_acum_despachos_enproceso,
                                    SUM(a.depe_max_x_recibir) AS depe_max_x_recibir,
                                    SUM(a.depe_max_dias_x_recibir) AS depe_max_dias_x_recibir,
                                    SUM(a.depe_max_doc_proceso) AS depe_max_doc_proceso,
                                    SUM(a.depe_max_dias_doc_proceso) AS depe_max_dias_doc_proceso
                                FROM catalogos.dependencia a ";
	}

	function whereDepeID($depe_id){
		$this->addWhere("a.depe_id=$depe_id");
	}
        
	function whereVarios($pers_id){
		$this->addWhere("a.depe_id IN (SELECT DISTINCT a.depe_id
		 			  FROM personal.persona_datos_laborales a
		 			  LEFT JOIN catalogos.dependencia b on  a.depe_id=b.depe_id
                                          WHERE a.pdla_estado=1 AND a.pers_id=$pers_id)");
	}
        
        function whereVarios2($pers_id){
		$this->addWhere("a.pdla_id IN (SELECT (a.pdla_id
		 			  FROM catalogos.dependencia a
                                          LEFT JOIN personal.persona_datos_laborales b ON a.pdla_id=b.pdla_id
                                          LEFT JOIN personal.persona c ON b.pers_id=c.pers_id
                                          WHERE a.depe_habiltado=1 
                                                AND c.pers_id=$pers_id)");
	}
}

class getAcumDespachosMaxDiasProceso extends selectSQL {
        function __construct($depe_id,$dias){
		$this->sql="SELECT  COUNT(a.desp_id) AS acum_max_dias_proceso
                                FROM gestdoc.despachos_derivaciones a 
                                WHERE (a.dede_estado=3 OR a.dede_estado=7)
                                    AND a.depe_iddestino=$depe_id
                                    AND (NOW()::date - a.dede_fecharecibe::date)>=$dias
                                ";
	}
}

class getAcumDespachosMaxDiasPorRecibir extends selectSQL {
        function __construct($depe_id,$dias){
		$this->sql="SELECT  COUNT(a.desp_id) AS acum_max_dias_por_recibir
                                FROM gestdoc.despachos_derivaciones a 
                                WHERE (a.dede_estado=2)
                                    AND a.depe_iddestino=$depe_id
                                    AND (NOW()::date - a.dede_fregistro::date)>=$dias
                                ";
	}
}

class getAcumDespachosxFirmarUsuario extends selectSQL {
        function __construct(){
		$this->sql="SELECT  a.usua_acum_pend_firmar
                                FROM admin.usuario a";
	}

	function whereUserID($user_id){
		$this->addWhere("a.usua_id=$user_id");
	}
}