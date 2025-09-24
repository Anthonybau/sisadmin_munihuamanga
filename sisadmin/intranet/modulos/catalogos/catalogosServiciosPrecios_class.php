<?php
require_once('../../library/clases/entidad.php');


class serviciosPrecios_SQLlista extends selectSQL {
	function __construct(){
		$this->sql="SELECT a.*,
                                   b.serv_descripcion||
                                                CASE WHEN COALESCE(xx.tabl_descripcion,'') = 'NINGUNO' THEN '' ELSE  COALESCE(' '||xx.tabl_descripcion,'') END|| 
                                                CASE WHEN COALESCE(a.sepr_umedida,'') = ''             THEN '' ELSE  '-'||a.sepr_umedida END
                                                AS serv_descripcion,                                   
                                   b.serv_equi_unidades,
                                   c.segr_descripcion as grupo,
                                   e.sesg_descripcion as sgrupo
                            FROM catalogos.servicio_precios a 
                            LEFT JOIN catalogos.servicio b          ON a.serv_codigo=b.serv_codigo 
                            LEFT JOIN catalogos.servicio_grupo c    ON b.segr_id=c.segr_id				
			    LEFT JOIN catalogos.servicio_sgrupo e   ON b.sesg_id=e.sesg_id								
                            LEFT JOIN catalogos.tabla xx            ON b.tabl_farmacia_laboratorio=xx.tabl_id
                            ";
	}

        
	function whereID($id){
		$this->addWhere(sprintf("a.sepr_id='%s'",$id));	
	}

	function whereCodServicio($serv_codigo){
		$this->addWhere(sprintf("a.serv_codigo='%s'",$serv_codigo));	
	}

        
        function whereTipoPrecio($tipo_precio){
		$this->addWhere(sprintf("a.tabl_tipoprecio='%s'",$tipo_precio));	
	}

        function whereDepeID($depe_id){
		$this->addWhere(sprintf("a.depe_id='%s'",$depe_id));
	}
        
        function whereOrigen($origen){
		$this->addWhere("a.sepr_origen=$origen");
	}
        
	function orderUno(){
		$this->addOrder("a.sepr_id");	
	}
        
}
