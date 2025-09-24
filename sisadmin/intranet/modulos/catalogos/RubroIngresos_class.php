<?php
require_once("../../library/clases/entidad.php");


class clsRubroIngresos extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='fuente_ingreso'; //nombre de la tabla
		$this->setKey='fuin_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

		/* Destinos luego de actulizar o agregar un registro */
		$this->destinoUpdate = "RubroIngresos_buscar.php";
		$this->destinoInsert = "RubroIngresos_buscar.php";
		$this->destinoDelete = "RubroIngresos_buscar.php";
                $this->pagEdicion ="RubroIngresos_edicion.php";
                $this->pagBuscar="RubroIngresos_buscar.php";
	}

	function addField(&$sql){
	}

	function getSql(){
		$sql=new clsRubroIngresos_SQLlista();
		$sql->whereID($this->id);
		return($sql->getSQL());
	}

	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	
}

class clsRubroIngresos_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.*,
					x.usua_login as username
				FROM siscopp.fuente_ingreso a
				LEFT JOIN admin.usuario x ON a.usua_id=x.usua_id
				";
	
	}
	
	function whereID($id){
		$this->addWhere("a.fuin_id=$id");
	}

	function whereDescrip($descrip){
		if($descrip) $this->addWhere("a.fuin_descripcion ILIKE '%$descrip%'");
	}

	
	function orderUno(){
		$this->addOrder("a.fuin_id DESC");
	}


	function getSQL_cbox(){
		$sql="SELECT fuin_id,fuin_id::text||' '||fuin_descripcion
				FROM (".$this->getSQL().") AS a ORDER BY 1 ";
		return $sql;
	}
	
}


if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
            include("../../library/library.php");
            /*	verificaci�n a nivel de usuario */
            verificaUsuario(1);
            verif_framework();

            $param= new manUrlv1();
            $param->removePar('control');
            $param->removePar('relacionamento_id');
            $param->removePar('pg'); /* Remuevo el par�metro */

            /* Recibo la p�gina actual de la lista y lo agrego como par�metro a ser enviado por la URL.  Este dato viene por POST */
            $pg = getParam("pg");
            $param->addParComplete('pg',$pg); /* Agrego el par�metro */

            //	conexión a la BD
            $conn = new db();
            $conn->open();

            $dml=new clsRubroIngresos();

            switch($control){

                    case 1: // Guardar
                            $dml->guardar();
                            break;
                    case 2: // Eliminar
                            $dml->eliminar();
                            break;
            }
            //	cierra la conexi�n con la BD
            $conn->close();
    }
}