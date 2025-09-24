<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/selectSQL.php");

class despachoAdjuntados extends entidad {
	
	function __construct($id='',$title=''){
		$this->setTable='despachos_adjuntados'; //nombre de la tabla
		$this->setKey='dead_id'; //campo clave
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

class despachoAdjuntados_SQLlista extends selectSQL {

	function __construct(){
		$this->sql= "SELECT a.desp_id,
                                    a.dead_id,
                                    a.area_adjunto,
                                    a.dead_descripcion,
                                    a.dead_signer,
                                    a.usua_id,
                                    b.desp_anno AS periodo,
                                    b.desp_file_firmado,
                                    b.desp_ocultar_editor,
                                    b.desp_anno,
                                    x.usua_login
                             FROM gestdoc.despachos_adjuntados a
                             LEFT JOIN  gestdoc.despachos b ON  a.desp_id=b.desp_id
                             LEFT JOIN admin.usuario x ON a.usua_id = x.usua_id
                            ";
	}

        function whereID($dead_id){
		$this->addWhere("a.dead_id=$dead_id");
	}
        
        function whereIDVarios($dead_id_varios){
		$this->addWhere("a.dead_id IN ($dead_id_varios)");
	}
        
	function wherePadreID($padre_id){
		$this->addWhere("a.desp_id=$padre_id");	
	}

        function whereDedeID($dede_id){ //despacho_deriacion
		$this->addWhere("a.dede_id=$dede_id");	
	}
        
        function whereFirmar(){
		$this->addWhere("a.dead_signer=1");	
	}
         
        function whereUsuaID($usua_id){
		$this->addWhere("a.usua_id=$usua_id");	
	}
        
        function whereZIP(){
		$this->addWhere("a.dead_zip=1");	
	}
        
        function whereNOZIP(){
		$this->addWhere("a.dead_zip=0");	
	}
        
        function orderUno(){
		$this->addOrder("a.desp_id,
                                 a.dead_signer,
                                 a.dead_id DESC");
	}        
}
