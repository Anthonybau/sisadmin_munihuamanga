<?php

require_once('../../library/clases/selectSQL.php');

class clsDespachosColaborativos_SQLlista extends selectSQL {

	function __construct(){
		$this->sql= "SELECT a.*,
                                    CASE WHEN a.deco_permiso=1 THEN 'AUTORIZADO' ELSE 'DENEGADO' END AS permiso,
                                    c.pers_apellpaterno||' '||c.pers_apellmaterno||' '||c.pers_nombres AS empleado,
                                    c.pers_dni AS dni,
                                    x.usua_login
				FROM gestdoc.despachos_colaborativos a 
                                LEFT JOIN personal.persona_datos_laborales b ON a.pdla_id=b.pdla_id
                                LEFT JOIN personal.persona c ON b.pers_id=c.pers_id
				LEFT JOIN admin.usuario x         ON a.usua_id=x.usua_id 					
                            ";

					
	}

	function whereID($id){
		$this->addWhere("a.deco_id=$id");	
	}

	function wherePadreID($padre_id){
		$this->addWhere("a.desp_id=$padre_id");	
	}
        
	function orderUno(){
		$this->addOrder("a.deco_id DESC");
	}

        function orderDos(){
		$this->addOrder("c.pers_apellpaterno,
                                 c.pers_apellmaterno,
                                 c.pers_nombres");
	}
}

class permiteEdicionColaborativo extends selectSQL {
    	function __construct($desp_id,$pers_id){
            $this->sql="SELECT deco_permiso AS permiso
                        FROM gestdoc.despachos_colaborativos a
                        LEFT JOIN gestdoc.despachos b ON a.desp_id=b.desp_id
                        WHERE a.deco_permiso=1 /*HABILITADO PARA EDICION*/
                        AND a.desp_id=$desp_id 
                        AND b.desp_estado=1 /*abierto*/
                        AND a.pdla_id IN (SELECT a.pdla_id
                                            FROM personal.persona_datos_laborales a
                                            WHERE a.pers_id=$pers_id
                                            )
                        ";            
            $this->setDatos();
            return($this->field('permiso'));
        }    
        
}