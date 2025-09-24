<?php
require_once('../../library/clases/entidad.php');
require_once('../../library/clases/selectSQL.php');

class clsDatosLaborales_movimientos extends entidad {
	
	function __construct($id='',$title=''){
		$this->setTable='persona_datos_laborales_auditoria'; //nombre de la tabla
		$this->setKey='pdla_serial'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="String"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	
		$this->pagEdicion=$this->getNamePage('Edicion');		

		/*Ancho y Alto de Thickbox */
		$this->is_thinckbox=true;
		$this->winWidth=900;  	/* Ancho de la ventana Thickbox cuando se cargue la claes en un Avanzlookup */
		$this->winHeight=300;  	/* Alto de la ventana Thickbox cuando se cargue la claes en un Avanzlookup */	
		
		/* Destinos luego de actulizar, agregar o eliminar un registro */
		$this->destinoInsert=$this->getNamePage('Lista1n');
		$this->destinoUpdate=$this->getNamePage('Lista1n');
		$this->destinoDelete=$this->getNamePage('Lista1n');

		$this->arrayNameVar[0]='nomeCampoForm';
		$this->arrayNameVar[1]='busEmpty';
		$this->arrayNameVar[2]='cadena2';
		$this->arrayNameVar[3]='pg2';
		$this->arrayNameVar[4]='colSearch';
		$this->arrayNameVar[5]='numForm';
	}

        function getSql(){
		$sql=new clsDatosLaborales_movimientos_SQLlista();
		$sql->wherePadreID($this->id);
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
		
		$cadena=is_array($formData)?trim($formData['Sbusc_cadena']):$formData;
		
		$busEmpty=$paramFunction->getValuePar($paramFunction->getValuePar(1));
		$relacionamento_id=$paramFunction->getValuePar('relacionamento_id');
		$colSearch=$paramFunction->getValuePar($paramFunction->getValuePar(4));
		$numForm=$paramFunction->getValuePar($paramFunction->getValuePar(5));
		
		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	
			/* Creo my objeto Table */
			$otable = new TableSimple(iif($cadena,'!=','','RESULTADO DE: '.$cadena,''),"100%",6,'tLista'); // T�tulo, ancho, Cantidad de columas,id de la tabla			

			/* Guardo la página actual en un campo oculto.  Para que al eliminar regrese a la p�gina donde estaba */
			$otable->addHtml("<input type='hidden' name='".$paramFunction->getValuePar(3)."' value='$pg'>\n");			
			
			$sql=new clsDatosLaborales_movimientos_SQLlista();
			$sql->wherePadreID($relacionamento_id);
			$sql->orderUno();
			
			//echo $sql->getSQL();
			//$objResponse->addAlert($sql->getSQL());
						
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) {
				$param->replaceParValue($paramFunction->getValuePar(2),$cadena); /* Agrego el par�metro */			
			}
			

			$rs = new query($conn, strtoupper($sql->getSQL()),$pg,40);

			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,'".encodeArray($formData)."','".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,'".encodeArray($formData)."','".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
					/* Agrego las cabeceras de mi tabla */
					$otable->addColumnHeader("Sit.Laboral","10%", "L"); // Título, ancho, alineación
                                        $otable->addColumnHeader("Dependencia","20%", "L");
                                        $otable->addColumnHeader("Cargo","15%", "L");
					$otable->addColumnHeader("Fecha.Doc","5%", "L"); // Título, ancho, alineación
                                        $otable->addColumnHeader("Documento","20%", "L");
                                        $otable->addColumnHeader("F.Registro","15%", "L"); // Título, ancho, alineación
                                        $otable->addColumnHeader("Usuario","10%", "L"); // Título, ancho, alineación
					$otable->addRowHead();

					$otable->addRow(); // adiciona la linea (TR)
					while ($rs->getrow()) {
						$id = $rs->field("pdla_id"); // captura la clave primaria del recordsource
						$campoTexto_de_Retorno = especialChar($rs->field("empleado"));
                                                $otable->addData($rs->field("sit_laboral"));
                                                $otable->addData($rs->field("depe_nombrecorto"));
						$otable->addData($rs->field("pdla_cargofuncional"));
                                                $otable->addData($rs->field("pdla_fecharesolingreso"));
						$otable->addData($rs->field("pdla_resolingresoestado"));
                                                $otable->addData($rs->field("pdla_fecharegistro"));
                                                $otable->addData($rs->field("username"));
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
		return(str_replace('Class',$accion,$this->getNameFile()));
	}	
	
	
} /* Fin de la clase */

class clsDatosLaborales_movimientos_SQLlista extends selectSQL {

	function __construct(){
		$this->sql = "SELECT a.*,
                                   b.pers_dni,
                                   b.pers_apellpaterno||' '||b.pers_apellmaterno||' '||b.pers_nombres AS empleado,
                                   COALESCE(c.tabl_descripaux,c.tabl_descripcion) AS sit_laboral,
                                   e.tabl_descripcion AS cargo_estructural,
                                   d.depe_nombre,d.depe_nombrecorto,
                                   x.usua_login as username,
                                   y.usua_login as usernameactual
			FROM persona_datos_laborales_auditoria a
                        LEFT JOIN persona b ON a.pers_id=b.pers_id
                        LEFT JOIN tabla   c ON a.tabl_idsitlaboral=c.tabl_codigo AND c.tabl_tipo='9'
                        LEFT JOIN dependencia   d ON a.depe_id=d.depe_id
                        LEFT JOIN tabla   e ON a.tabl_cargoestructural=e.tabl_id
                        LEFT JOIN usuario x ON a.usua_id=x.usua_id
                        LEFT JOIN usuario y ON a.pdla_actualusua=y.usua_id
	";
	}

	function wherePadreID($id){
		$this->addWhere("a.pdla_id=$id");
	}


	function orderUno(){
		$this->addOrder("a.pdla_serial DESC");
	}

}

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

            $dml=new clsDatosLaborales_movimientos();
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
?>