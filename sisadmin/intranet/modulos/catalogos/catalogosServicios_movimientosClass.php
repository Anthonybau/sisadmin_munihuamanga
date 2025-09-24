<?php
require_once('../../library/clases/entidad.php');
require_once('../../library/clases/selectSQL.php');

class serviciosMovimientos extends entidad {
	
	function __construct($id='',$title=''){
		$this->setTable='servicio_historico'; //nombre de la tabla
		$this->setKey='sehi_id'; //campo clave
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

	function buscar($op,$id)
	{
		global $conn,$param,$nomeCampoForm;
		$objResponse = new xajaxResponse();
		
		/* Variables auxiliares */
		$relacionamento_id=$id;

	
			/* Creo my objeto Table */
			$otable = new TableSimple(iif($cadena,'!=','','RESULTADO DE: '.$cadena,''),"100%",6,'tLista'); // T�tulo, ancho, Cantidad de columas,id de la tabla			

			
			$sql=new serviciosMovimientos_SQLlista();
			$sql->wherePadreID($relacionamento_id);
			$sql->orderUno();
			$sql=$sql->getSQL();
                        //echo $relacionamento_id;

			$rs = new query($conn, strtoupper($sql));

			
			if ($rs->numrows()>0) {
					/* Agrego las cabeceras de mi tabla */
					$otable->addColumnHeader("Fecha","25%", "C"); // T�tulo, ancho, alineaci�n
					$otable->addColumnHeader("Precio Anterior","30%", "C"); // T�tulo, ancho, 
                                        $otable->addColumnHeader("Nuevo Precio","30%", "C"); // T�tulo, ancho, 
                                        $otable->addColumnHeader("Usuario","15%", "C"); // T�tulo, ancho, alineaci�n
					$otable->addRowHead(); 					

					while ($rs->getrow()) {
						$id = $rs->field("sehi_id"); // captura la clave primaria del recordsource

                                                $otable->addData(_dttos($rs->field("sehi_fregistro")));
						$otable->addData($rs->field("serv_precio_old"),"R");
                                                $otable->addData($rs->field("serv_precio_new"),"R");
                                                $otable->addData($rs->field("usua_login"),'L');
						$otable->addRow();
					}

				$contenido_respuesta.=$otable->writeHTML();
                                $contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

			} else {
				$otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!","100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
				$otable->addRowHead(); 	
				$otable->addRow();	
				$contenido_respuesta=$otable->writeHTML();
			}
                        
                   return($contenido_respuesta);
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

class serviciosMovimientos_SQLlista extends selectSQL {

	function __construct(){
		$this->sql= "SELECT a.*,
                                    x.usua_login
				FROM catalogos.servicio_historico a 
				LEFT JOIN usuario x ON a.usua_id=x.usua_id 					
                            ";

					
	}

	function wherePadreID($padre_id){
		$this->addWhere("a.serv_codigo=$padre_id");	
	}

        function orderUno(){
		$this->addOrder("a.sehi_id DESC");
	}        
}




?>