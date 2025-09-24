<?php
require_once('../../library/clases/entidad.php');

class clsPerfil extends entidad {
	
	function __construct($id='',$title=''){
		$this->setTable='perfilu'; //nombre de la tabla
		$this->setKey='perf_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

		/* Puede usarse tambi�n de manera directa as�*/
//		$this->pagEdicion = "catalogosDependenciaAdquiriente_edicion.php";
//		$this->pagBuscar  = "catalogosDependenciaAdquiriente_buscar.php";		
		
		$this->pagEdicion=$this->getNamePage('edicion');
		
		$this->pagBuscar=$this->getNamePage('buscar');
		
		/* Destinos luego de actulizar, agregar o eliminar un registro */
		$this->destinoUpdate = $this->pagBuscar;
		$this->destinoInsert = $this->pagEdicion;
		$this->destinoDelete = $this->pagBuscar;

		$this->arrayNameVar[0]='nomeCampoForm';
		$this->arrayNameVar[1]='busEmpty';
		$this->arrayNameVar[2]='cadena';
		$this->arrayNameVar[3]='pg';
		$this->arrayNameVar[4]='colSearch';
		$this->arrayNameVar[5]='numForm';

	}

	function getSql(){
		$sql=new clsPerfil_SQLlista();
		$sql->whereID($this->id);
		return($sql->getSQL());
	}
	

	function buscar($op,$formData,$arrayParam,$pg,$Nameobj='')
	{
		global $conn,$param,$nomeCampoForm;
		$objResponse = new xajaxResponse();
		
		$arrayParam=decodeArray($arrayParam);

		$paramFunction= new manUrlv1();
		$paramFunction->setUrlExternal($arrayParam);

		if($op==1 && !is_array($formData)) $formData=decodeArray($formData);
		
		$nbusc_depe_id=$formData['nbusc_depe_id'];
		$nbusc_origen=$formData['nbusc_origen'];
		
		$cadena=is_array($formData)?trim($formData['Sbusc_cadena']):$formData;
		
		$busEmpty=$paramFunction->getValuePar($paramFunction->getValuePar(1));
		$colSearch=$paramFunction->getValuePar($paramFunction->getValuePar(4));
		$numForm=$paramFunction->getValuePar($paramFunction->getValuePar(5));

				
		$pageEdit=$paramFunction->getValuePar('pageEdit');
		
		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	
			/* Creo my objeto Table */
			$otable = new TableSimple("","100%",8,'tLista'); // T�tulo, ancho, Cantidad de columas,id de la tabla			

			/* Guardo la p�gina actual en un campo oculto.  Para que al eliminar regrese a la p�gina donde estaba */
			$otable->addHtml("<input type='hidden' name='".$paramFunction->getValuePar(3)."' value='$pg'>\n");			

			$sql=new clsPerfil_SQLlista();
			
			//se analiza la columna de busqueda
			switch($colSearch){
				case 'numero': // si se recibe el campo id
					$sql->whereID($cadena);
					break;
	
				default:// si se no se recibe ningun campo de busqueda
                                        if(ctype_digit($cadena)) //si la cadena recibida son todos digitos
						$sql->whereID($cadena);
					else
						$sql->whereDescrip($cadena);
					break;
                                        
				}
			$sql->orderUno();
			$sql=$sql->getSQL();
			
			
			//$objResponse->addAlert($sql);
			//echo $sql;
									
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) {
				$param->replaceParValue($paramFunction->getValuePar(2),$cadena); /* Agrego el par�metro */			
			}
	
			$rs = new query($conn, strtoupper($sql));			

			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,'".encodeArray($formData)."','".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,'".encodeArray($formData)."','".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
					$otable->addColumnHeader(""); // Coluna com checkbox
					/* Agrego las cabeceras de mi tabla */
					$otable->addColumnHeader("C&oacute;d","5%", "L"); // T�tulo, Ordenar?, ancho, alineaci�n
					$otable->addColumnHeader("Descripci&oacute;n","95%","L");
					$otable->addRowHead();
					 					
					while ($rs->getrow()) {
						$id = $rs->field("perf_id"); // captura la clave primaria del recordsource
						
						/* agrego pg como par�metro a ser enviado por la URL */
						$param->replaceParValue($paramFunction->getValuePar(3),$pg); /* Agrego el par�metro */
							
						$otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" >");

						$param->addParComplete("nbusc_depe_id",$nbusc_depe_id);
												
						$link=$pageEdit."?id=$id&".$param->buildPars(false);
						$otable->addData(addLink(str_pad($id,5,'0',STR_PAD_LEFT),$link,"Click aqu&iacute; para consultar o editar este registro"));
						
						$otable->addData($rs->field("perf_descripcion"));
						
						$otable->addRow();
					}
					
				$contenido_respuesta=$button->writeHTML();
				$contenido_respuesta.=$otable->writeHTML();
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";
				//$contenido_respuesta=$sql;

			} else {
				$otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!","100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
				$otable->addRowHead(); 	
				$otable->addRow();	
				$contenido_respuesta=$otable->writeHTML();
			}
		}
	else{
		$contenido_respuesta="";
	}

	
	//$contenido_respuesta=$sql;	
		//se analiza el tipo de funcionamiento
		if($op==1){//si es llamado para su funcionamiento en ajax con retornoa a un div
			$objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
			$objResponse->addscript("activaSorter()"); // Para activar el orden en la tabla 
			$objResponse->addscript("func_jquerytablas()"); // Para activar las funciones de css de la tabla
			return $objResponse;
			}
		else
			if($op==3){//si es llamado para su funcionamiento en ajax, desde un una busqueda avanzada, con retorno a un objeto
				if($Nameobj){
					$objResponse->addScript($Nameobj .' = "'.$campoTexto_de_Retorno.'";');
					return $objResponse;
					}
				else
					return $campoTexto_de_Retorno;
				}
			else//si es llamado como una simple funciona de PHP
				return $contenido_respuesta	;
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

class clsPerfil_SQLlista extends selectSQL {
	function __construct(){
		$this->sql = "SELECT a.*
		 			  FROM perfilu a 
					";
	}

	function whereID($id){
		$this->addWhere("a.perf_id=$id");	
	}

	function whereDescrip($descrip){
		if($descrip) $this->addWhere("(a.perf_id::TEXT ILIKE '%$descrip%' or a.perf_descripcion ILIKE '%$descrip%')");
	}

	function orderUno(){
		$this->addOrder("a.perf_id DESC");		
	}

}



////////

/* Llamando a la subclase */
if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
            require_once($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");

            /*	verificaci�n a nivel de usuario */
            verificaUsuario(1);
            verif_framework();

            $param= new manUrlv1();
            $param->removePar('control');

            //	conexi�n a la BD
            $conn = new db();
            $conn->open();

            $dml=new clsPerfil();
            /* Recibo la p�gina actual de la lista y lo agrego como par�metro a ser enviado por la URL.  Este dato viene por POST */
            $pg = getParam($dml->getArrayNameVarID(3));
            $param->replaceParValue($dml->getArrayNameVarID(3),$pg); /* Agrego el par�metro */

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