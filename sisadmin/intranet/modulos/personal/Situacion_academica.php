<?php
require_once("../../library/clases/entidad.php");

class SituacionAcademica_SQLlista extends selectSQL {

    function __construct() {
        $this->sql="SELECT a.* 
                            FROM personal.situacion_academica a 
                        ";
    }
    function whereID($id) {
        $this->addWhere("a.siac_id=$id");
    }

    function whereDescrip($descrip) {
        if($descrip) $this->addWhere("(a.siac_descripcion ILIKE '%$descrip%')");
    }

    function orderUno() {
        $this->addOrder("a.siac_id");
    }
    
    function getSQL_cbox(){
		$sql="SELECT    a.siac_id,
                                a.siac_descripcion
                            FROM (".$this->getSQL().") AS a ";
		return $sql;
    }  
}
