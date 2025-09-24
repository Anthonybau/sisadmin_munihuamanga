<?php
require_once('../../library/clases/entidad.php');
require_once('../../library/clases/selectSQL.php');

class serviciosImagenes extends entidad {
	
	function __construct($id='',$title=''){
		$this->setTable='servicio_imagenes'; //nombre de la tabla
		$this->setKey='seim_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	
		$this->pagEdicion=$this->getNamePage('edicion');
		
		/* Ancho y Alto de Thickbox */
		$this->is_thinckbox=true;		
		$this->winWidth=650;  	/* Ancho de la ventana Thickbox cuando se cargue la claes en un Avanzlookup */
		$this->winHeight=400;  	/* Alto de la ventana Thickbox cuando se cargue la claes en un Avanzlookup */
		
		/* Destinos luego de actulizar, agregar o eliminar un registro */
		$this->destinoInsert=$this->getNamePage('lista');
		$this->destinoUpdate=$this->getNamePage('lista');
		$this->destinoDelete=$this->getNamePage('lista');

		$this->arrayNameVar[0]='nomeCampoForm';
		$this->arrayNameVar[1]='busEmpty';
		$this->arrayNameVar[2]='cadena2';
		$this->arrayNameVar[3]='pg2';
		$this->arrayNameVar[4]='colSearch';
		$this->arrayNameVar[5]='numForm';
	}


	/* Nombre del archivo php de la clase */
	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}

	function getNamePage($accion)
	{
		return(str_replace('class',$accion,$this->getNameFile()));
	}	
		
	
} /* Fin de la clase */

class serviciosImagenes_SQLlista extends selectSQL {

	function __construct(){
		$this->sql= "SELECT a.*,
                                    x.usua_login
				FROM catalogos.servicio_imagenes a 
				LEFT JOIN usuario x ON a.usua_id=x.usua_id 					
                            ";

					
	}

	function wherePadreID($padre_id){
		$this->addWhere("a.serv_codigo=$padre_id");	
	}

        function orderUno(){
		$this->addOrder("a.seim_id DESC");
	}        
}
