<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class despachoRequisitos_SQLlista extends selectSQL {
	function __construct(){
            $this->sql="SELECT  a.dere_id,
                                a.desp_id,
                                a.dere_orden,
                                a.dere_descripcion,
                                a.dere_valida,
                                a.dere_fregistro,
                                a.usua_id,
                                a.dere_fregistro_valida,
                                a.dere_usua_id_valida,
                                a,dere_observacion,
                                x.usua_login as username
                            FROM gestdoc.despachos_requisitos a
                            LEFT JOIN admin.usuario x ON a.usua_id=x.usua_id
                        ";
	}

	function whereID($id){
            $this->addWhere("a.dere_id=$id");
	}

	function wherePadreID($desp_id){
            $this->addWhere("a.desp_id=$desp_id");
	}
         
        function whereNoCumple(){
            $this->addWhere("a.dere_valida=0");
	}
        
        function orderUno(){
            $this->addOrder("a.desp_id,a.dere_orden");
	}
        
}
