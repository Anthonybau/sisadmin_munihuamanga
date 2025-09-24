<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class clsClasificador extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='clasificador'; //nombre de la tabla
		$this->setKey='clas_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="String"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

		/* Destinos luego de actulizar o agregar un registro */
		$this->destinoUpdate = "clasificador_buscar.php";
		$this->destinoInsert = "clasificador_buscar.php";
		$this->destinoDelete = "clasificador_buscar.php";		
	}

	function getSql(){
		$sql=new clsClasificador_SQLlista();
		$sql->whereID($this->id);
		return($sql->getSQL());
	}
	
	function jsDevolver($nomeCampoForm){
			
			if($nomeCampoForm)
			
			//PARA EJECUTAR UNA FUNCION DEPENDNIENTE DEL VALOR ELEGIDO EN LA VENTANA DE BUSQUEDA
			//SE REQUIERE  DE self.parent.NOMBRE_DE_FUNCION_JAVASCRIPT(paramenteres) ejem:self.parent.xajax_cargaGrupo(valor,1)
			return ("function update(valor,descricao, numForm) {
					parent.parent.content.document.forms[numForm]._Dummy$nomeCampoForm.value = descricao;
					parent.parent.content.document.forms[numForm].$nomeCampoForm.value = valor;
					parent.parent.content.document.forms[numForm].__Change_$nomeCampoForm.value = 1;
					self.parent.tb_remove();
					}");
	}
	
	function buscar($op,$formData,$arrayParam,$pg,$Nameobj='')
	{
		global $conn,$param;
		$objResponse = new xajaxResponse();
		
		$arrayParam=decodeArray($arrayParam);

		$paramFunction= new manUrlv1();
		$paramFunction->setUrlExternal($arrayParam);

		$cadena=is_array($formData)?trim(strtoupper($formData['Sbusc_cadena'])):$formData;
		if(!$cadena && $op==2) $cadena=getSession("cadSearch");
		
		$colSearch=$paramFunction->getValuePar('colSearch');
		$colOrden=$paramFunction->getValuePar('colOrden');
		$busEmpty=$paramFunction->getValuePar('busEmpty');
						
		if(strlen($cadena)>0  or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	
			$otable = new TableSimple("","100%",6,'tLista'); // T�tulo, ancho, Cantidad de columas,id de la tabla						
			/* Guardo la p�gina actual en un campo oculto.  Para que al eliminar regrese a la p�gina donde estaba */
			$otable->addHtml("<input type='hidden' name='pg' value='$pg'>\n");

			$sql=new clsClasificador_SQLlista();
						
			//se analiza la columna de busqueda
			switch($colSearch){
				case 'codigo': // si se recibe el campo id
					$sql->whereID($cadena);
					break;
	
				default:// si se no se recibe ningun campo de busqueda
					if(ctype_digit($cadena)) //si la cadena recibida son todos digitos
						$sql->whereCodigo($cadena);
					else
						$sql->whereDescrip($cadena);
					break;
				}
			$sql->orderUno();
			$sql=$sql->getSQL();

			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) {setSession("cadSearch",$cadena);}			
	
			$rs = new query($conn, $sql, $pg, 20);

			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {

					if (!$nomeCampoForm) /* Si no estoy en una b�squeda avanzada (AvanzLookup) */
						$otable->addColumnHeader("<input type=\"checkbox\" id=\"checkall\" >"); // Coluna com checkbox


					$otable->addColumnHeader("C&oacute;d","10%", "L"); // T�tulo, Ordenar?, ancho, alineaci�n
					$otable->addColumnHeader("Descripci&oacute;n","90%", "L"); // T�tulo, Ordenar?, ancho, alineaci�n						
					$otable->addRowHead(); 		
					while ($rs->getrow()) {
						$id = $rs->field("clas_id"); // captura la clave primaria del recordsource
						$campoTexto_de_Retorno = especialChar($rs->field("clas_descripcion"));

						if ($nomeCampoForm){ /* si la llamada es desde la busqueda avanzada (AvanzLookup) */
							$otable->addData(addLink($id,"javascript:update('$id','$campoTexto_de_Retorno','$numForm')","Click aqu&iacute; para seleccionar el registro"));
						}elseif($op!=3){  /* Si estoy en la p�gina normal */ 
							/* agrego pg como par�metro a ser enviado por la URL */
							$param->removePar('pg'); /* Remuevo el par�metro */
							$param->addParComplete('pg',$pg); /* Agrego el par�metro */
							
							$otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" >");
							$otable->addData(addLink($id,"clasificador_edicion.php?id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));							
						}
						
						$otable->addData($campoTexto_de_Retorno);						
//						$otable->addData($rs->field("clase"));
						$otable->addRow();
					}
				$contenido_respuesta=$button->writeHTML();
				$contenido_respuesta.=$otable->writeHTML();
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

			} else {
				$otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!","100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
				$otable->addRowHead(); 	
				$otable->addRow();	
				$contenido_respuesta=$otable->writeHTML();
			}
		}
	else
		$contenido_respuesta="";
	
		//se analiza el tipo de funcionamiento
		if($op==1){//si es llamado para su funcionamiento en ajax con retornoa a un div
			$objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
			$objResponse->addscript("activaSorter()"); // Para activar el orden en la tabla 
			$objResponse->addscript("func_jquerytablas()"); // Para activar las funciones de css de la tabla
			return $objResponse;
			}
		else
			return $contenido_respuesta	;
	}

	function guardar(){
		global $conn,$param;
		$nomeCampoForm=getParam("nomeCampoForm");

		$destinoUpdate=$this->destinoUpdate.$param->buildPars(true);	
		$pg=is_array($this->arrayNameVar)?$this->arrayNameVar[3]:'pg';
		$param->removePar($pg); /* Remuevo el par�metro p�gina */
		$destinoInsert=$this->destinoInsert.$param->buildPars(true);
		
		// objeto para instanciar la clase sql
		$sql = new UpdateSQL();
				
		$sql->setTable($this->setTable);
		$sql->setKey($this->setKey, $this->valueKey, $this->typeKey);
	
		include("../guardar_tipoDato.php");
	
		if ($this->valueKey) { // modificaci�n
			$sql->setAction("UPDATE");
		}else{
			$sql->setAction("INSERT");
			$sql->addField('usua_id', getSession("sis_userid"), "Number");
		}
		
		/* Aqu� puedo agregar otros campos a la sentencia SQL */
		$this->addField($sql);

		/* Ejecuto el SQL */
		$conn->execute($sql->getSQL());
                //echo $sql->getSQL();
		$error=$conn->error();
		if($error){ 
			alert($error);	/* Muestro el error y detengo la ejecuci�n */
		}else{
			/*  muestra mensaje noticia del la base de datos, pero no detiene la ejecucion	*/
			$notice=$conn->notice();
			if($notice) 
				alert($notice,0);				
		}
		
		/* */
		if ($this->valueKey) {// modificaci�n
			if($this->is_thinckbox){ // Si es un Script javascript el que quiero que se ejecute luego de insertar un registro
				echo "<script language=\"javascript\">
						self.parent.tb_remove();
						self.parent.document.location='$destinoUpdate'
					</script>"; /* Al recargar la p�g. se cierra el Thickbox que he utilizado para editar o modificar */
			}
			$last_id=$this->valueKey; 
			if(strpos($destinoInsert, "?")>0)
				$destinoUpdate.="&id=$last_id";
			else
				$destinoUpdate.="?id=$last_id";

			redirect($destinoUpdate,"content");			
							
		}else{ /* Inserci�n */
			if($nomeCampoForm){//si se llama desde una ventana emergente (avanzlookup) para seleccionar un valor
				/* Datos que se retornan desde un (avanzlookup) */
				$return_val=$this->return_val; /* Valor que se devuelve */
				$return_txt=$this->return_txt; /* Texo que se devuelve */
	
				/* Comandos Javascript */		
				echo "<script language=\"javascript\">
						parent.parent.content.document.forms[0]._Dummy$nomeCampoForm.value = '$return_txt';
						parent.parent.content.document.forms[0].$nomeCampoForm.value = '$return_val';
						parent.parent.content.document.forms[0].__Change_$nomeCampoForm.value = 1;
						self.parent.tb_remove();
					</script>";
			}else{ /* Si se llama desde una p�gina normal */
				if($this->is_thinckbox){ // Si es un Script javascript el que quiero que se ejecute luego de insertar un registro
					echo "<script language=\"javascript\">
							self.parent.tb_remove();
							self.parent.document.location='$destinoInsert'
						</script>"; /* Al recargar la p�g. se cierra el Thickbox que he utilizado para editar o modificar */
					}
				/*a�ado el id del registro ingresado*/
				$last_id=$conn->lastid($this->setTable. '_' . $this->setKey . "_seq"); /* Obtengo el Id del registro ingreado.  Ojo esto solo funciona si el campo clave es un serial (est� basado en una secuencia) */								
				if(strpos($destinoInsert, "?")>0)
					$destinoInsert.="&id=$last_id&clear=1";  
				else
					$destinoInsert.="?id=$last_id&clear=1";
			
				/* Envio el "id" para cuando regreso a la misma p�gina de edici�n y el 
				"clear" para cuando regreso a la lista y deseo que se vea el �ltimo registro ingresado, 
				con el clear se limpia la variable "cadSearch" o "cadSearchhijo" */
				//echo $destinoInsert;	
				redirect($destinoInsert,"content");							
			}
		}
	}
	
	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	
}

class clsClasificador_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT DISTINCT ON (a.clas_id) a.*,
					  e.usua_login as username					  
					FROM clasificador a
					LEFT JOIN usuario e ON a.usua_id=e.usua_id";
	}

	function whereID($id){
		$this->addWhere("a.clas_id='$id'");	
	}

	function whereCodigo($codigo){
		$this->addWhere("SUBSTR(a.clas_id,1,LENGTH('$codigo'))='$codigo'");	
	}

	function whereDescrip($descrip){
		if($descrip) $this->addWhere("a.clas_id LIKE '%$descrip%' OR a.clas_descripcion ILIKE '%$descrip%'");
	}

	function whereTipo($tipo){
		$this->addWhere("SUBSTR(a.clas_id,1,1)='$tipo'");	
	}
	
        function whereEspecifica(){
		$this->addWhere("a.clas_especifica=1");
	}
        
        function whereGenerica(){
		$this->addWhere("LENGTH(a.clas_id)=3");
	}
        
	function orderUno(){
		$this->addOrder("a.clas_id");		
	}

}

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
	
	//	conexi�n a la BD 
	$conn = new db();
	$conn->open();

	$dml=new clsClasificador();

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
?>