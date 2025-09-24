<?php
require_once("../../library/clases/entidad.php");

class clsPersonaRegimenLaboral extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='personal.persona_regimen_laboral'; //nombre de la tabla
		$this->setKey='perl_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;

		/* Destinos luego de actulizar, agregar o eliminar un registro */
		$this->destinoInsert='PersonaRegimenLaboral_lista.php';
		$this->destinoUpdate='PersonaRegimenLaboral_buscar.php';
		$this->destinoDelete='PersonaRegimenLaboral_lista.php';

		$this->arrayNameVar[0]='nomeCampoForm';
		$this->arrayNameVar[1]='busEmpty';
		$this->arrayNameVar[2]='cadena';
		$this->arrayNameVar[3]='pg';
		$this->arrayNameVar[4]='colSearch';
		$this->arrayNameVar[5]='numForm';

	}

        function getSql(){
		$sql=new clsPersonaRegimenLaboral_SQLlista();
		$sql->whereID($this->id);
		return($sql->getSQL());
	}

        function addField(&$sql){
		$sql->addField("perl_actualfecha", 'NOW()', "String");
		$sql->addField("perl_actualusua", getSession("sis_userid"), "String");
	}
        
        function buscar($op,$formData,$arrayParam,$pg,$Nameobj='') {
                global $conn,$param,$nomeCampoForm,$id_relacion;
                $objResponse = new xajaxResponse();

                $arrayParam=decodeArray($arrayParam);

                $paramFunction= new manUrlv1();
                $paramFunction->setUrlExternal($arrayParam);

                if($op==1 && !is_array($formData)) $formData=decodeArray($formData);

                $cadena=is_array($formData)?trim($formData['Sbusc_cadena']):$formData;

                $busEmpty=$paramFunction->getValuePar('busEmpty');
                $colSearch=$paramFunction->getValuePar($paramFunction->getValuePar(4));
                $numForm=$paramFunction->getValuePar($paramFunction->getValuePar(5));

                $clear=$paramFunction->getValuePar('clear');
                $pageEdit=$paramFunction->getValuePar('pageEdit');


                if($busEmpty==1) { //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias

                    $sql=new clsPersonaRegimenLaboral_SQLlista();
                    $sql->wherePadreID($id_relacion);

                    //se analiza la columna de busqueda
                    switch($colSearch) {
                        case 'numero': // si se recibe el campo id
                            break;

                        default:// si se no se recibe ningun campo de busqueda
                            if($cadena)
                                if(ctype_digit($cadena)){ //si la cadena recibida son todos digitos
                                    $sql->whereID($cadena);
                                }else{
                                    $sql->whereDescrip($cadena);
                                }


                            break;
                    }
                    $sql->orderUno();
                    $sql=$sql->getSQL();
                    //echo $sql;
                    //guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
                    if ($op==1 && !$nomeCampoForm) {
                        $param->replaceParValue($paramFunction->getValuePar(2),$cadena); /* Agrego el par�metro */
                    }

                    $rs = new query($conn, $sql);

                    $otable = new TableSimple("","100%",16,'tLista'); // T�tulo, ancho, Cantidad de columas,id de la tabla
                                /* Guardo la pagina actual en un campo oculto.  Para que al eliminar regrese a la p�gina donde estaba */
                    $otable->addHtml("<input type='hidden' name='".$paramFunction->getValuePar(3)."' value='$pg'>\n");

                    $button = new Button;
                    $pg_ant = $pg-1;
                    $pg_prox = $pg+1;
                    if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,'".encodeArray($formData)."','".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
                    if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,'".encodeArray($formData)."','".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");

                    if ($rs->numrows()>0) {
                        /* Agrego las cabeceras de mi tabla */
                        $otable->addColumnHeader("<input type=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">"); // Coluna com checkbox
                        $otable->addColumnHeader("C&oacute;d","2%", "C"); 
                        $otable->addColumnHeader("Fecha","10%", "C"); 
                        $otable->addColumnHeader("R&eacute;gimen Laboral","28%", "C"); 
                        $otable->addColumnHeader("Condici&oacute;n","20%", "C"); 
                        $otable->addColumnHeader("Documento","40%", "C");
                        $otable->addRowHead();

                        while ($rs->getrow()) {
                            $id = $rs->field("perl_id"); // captura la clave primaria del recordsource

                            $otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" >");

                            $otable->addData(addLink($rs->field("perlid"),PATH_INC."auxiliar.php?pag=../../modulos/personal/PersonaRegimenLaboral_edicion.php?id=$id,id_relacion=$id_relacion","Click aqu&iacute; para consultar o editar este registro","content","ls-modal"));	
                            $otable->addData(dtos($rs->field("perl_fecha")));	
                            $otable->addData($rs->field("rela_descripcion"));
                            $otable->addData($rs->field("sit_laboral"));
                            $otable->addData($rs->field("perl_documento"));
                            $otable->addRow(); // adiciona linea

                        }

                        $contenido_respuesta=$button->writeHTML();
                        $contenido_respuesta.=$otable->writeHTML();
                        $contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
                        $contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

                    } else {
                        $otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!","100%", "C"); 
                        $otable->addRowHead();
                        $otable->addRow();
                        $contenido_respuesta=$otable->writeHTML();
                    }
                }
                else
                    $contenido_respuesta="";

                //se analiza el tipo de funcionamiento
                if($op==1) {//si es llamado para su funcionamiento en ajax con retornoa a un div
                    $objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
                    $objResponse->addscript("activaSorter()"); // Para activar el orden en la tabla
                    $objResponse->addscript("func_jquerytablas()"); // Para activar las funciones de css de la tabla
                    $objResponse->addscript("$(document).ready(function() {
                                                $('.ls-modal').on('click', function(e){
                                                    e.preventDefault();
                                                    $('#myModal').modal('show').find('.modal-body').load($(this).attr('href'));
                                                }); 
                                            });");                    
                    return $objResponse;
                }
                else
                    if($op==3) {//si es llamado para su funcionamiento en ajax, desde un una busqueda avanzada, con retorno a un objeto
                        if($Nameobj) {
                            $objResponse->addScript($Nameobj .' = "'.$campoTexto_de_Retorno.'";');
                            return $objResponse;
                        }
                        else
                            return $campoTexto_de_Retorno;
                    }
                    else//si es llamado como una simple funciona de PHP
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
	
		if ($this->valueKey) { // modificación
			$sql->setAction("UPDATE");
                        $sql_type=2;
		}else{
			$sql->setAction("INSERT");
                        $sql_type=1;
			$sql->addField('usua_id', getSession("sis_userid"), "Number");
		}


		/* Aqu� puedo agregar otros campos a la sentencia SQL */
		$this->addField($sql);

		/* Ejecuto el SQL */
                $sqlCommand=$sql->getSQL();
		$padre_id=$conn->execute("$sqlCommand  RETURNING $this->setKey");
                
		$error=$conn->error();
		if($error){ 
                         if(stristr($error,"duplicate key value")){
                             $x=substr($error,strpos($error,"constraint")+11,(strpos($error,"DETAIL")-strpos($error,"constraint"))-12);
                             $error="Valor Duplicado:".$x;
                         }
                             
			alert($error);	/* Muestro el error y detengo la ejecuci�n */
		}else{
                    /*  muestra mensaje noticia del la base de datos, pero no detiene la ejecucion	*/
                    $notice=$conn->notice();
                    if($notice) 
                    	alert($notice,0);
                }
		
		/* */
		if ($this->valueKey) {// modificación
			$last_id=$this->valueKey; 
			if(strpos($destinoInsert, "?")>0)
				$destinoUpdate.="&id=$last_id";
			else
				$destinoUpdate.="?id=$last_id";

		}else{ /* Inserci�n */
			$last_id=$conn->lastid($this->setTable. '_' . $this->setKey . "_seq"); /* Obtengo el Id del registro ingreado.  Ojo esto solo funciona si el campo clave es un serial (est� basado en una secuencia) */								
			if(strpos($destinoInsert, "?")>0)
				$destinoInsert.="&id=$last_id&clear=1";  
			else
				$destinoInsert.="?id=$last_id&clear=1";
		}

                echo "<"."script".">\n";
		echo "parent.parent.content.cerrar();\n";
                echo "parent.parent.content.location.reload();\n";
		echo "</"."script".">\n";

	}
        
	function eliminar(){
		global $conn,$param;

		$destinoDelete=$this->destinoDelete."?clear=1&".$param->buildPars(false);		
		
		/* captura y prepara la lista de registros a ser eliminados */ 
		$arLista_elimina = getParam("sel");
		if (is_array($arLista_elimina)) {
		 $lista_elimina = implode(",",$arLista_elimina);
		}
		if(!$lista_elimina) return;

		if(strtolower($this->typeKey)=='string'){
			/* debido a que el campo clave es char */
			$lista_elimina=str_replace(",","','",$lista_elimina);
		}

		/* Sql a ejecutar */
		$sqlCommand ="DELETE FROM $this->setTable WHERE $this->setKey";
		$sqlCommand.=" IN (".iif(strtolower($this->typeKey),"==","string","'","").$lista_elimina.iif(strtolower($this->typeKey),"==","string","'","").") " ;
		$sqlCommand.=" AND usua_id=".getSession("sis_userid");
                $sqlCommand.=" RETURNING $this->setKey ";

		/* Ejecuto la sentencia */
                //alert($sqlCommand);
		$padre_id=$conn->execute($sqlCommand);
		$error=$conn->error();		
		if($error) alert($error);
		else{
                    redirect($destinoDelete,"content");		
		}
	}
        

	/* Nombre del archivo php de la clase */
	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1);
	}
           
} /* Fin de la clase */


class clsPersonaRegimenLaboral_SQLlista extends selectSQL {
	function __construct(){
		$this->sql = "SELECT  a.*,
                                      LPAD(a.perl_id::TEXT,5,'0') AS perlid,  
                                      b.rela_descripcion,
                                      c.tabl_descripaux AS sit_laboral,
                                      i.pers_apellpaterno||' '||i.pers_apellmaterno||' '||i.pers_nombres AS empleado,
                                      i.pers_dni AS dni,                                      
                                      x.usua_login AS username,
                                      y.usua_login AS usernameactual
			FROM personal.persona_regimen_laboral   a 
                        LEFT JOIN catalogos.regimen_laboral     b ON a.rela_id = b.rela_id  
                        LEFT JOIN catalogos.tabla               c ON a.tabl_idsitlaboral=c.tabl_id
                        LEFT JOIN personal.persona              i ON a.pers_id=i.pers_id
                        LEFT JOIN admin.usuario                 x ON a.usua_id=x.usua_id
                        LEFT JOIN admin.usuario                 y ON a.perl_actualusua=y.usua_id
                        
                        ";
	}

	function wherePadreID($padre_id){
		$this->addWhere("a.pers_id=$padre_id");
	}

	function whereID($id){
		$this->addWhere("a.perl_id=$id");
	}
        
	function whereDescrip($descrip){
		$this->addWhere("a.perl_documento ILIKE '%$descrip%'");
	}

	function orderUno(){
		$this->addOrder("a.perl_id DESC");
	}

}

////////

/* Llamando a la subclase */
if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
            require_once($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");

            /*	verificación a nivel de usuario */
            verificaUsuario(1);
            verif_framework();

            $param= new manUrlv1();
            $param->removePar('control');

            //	conexión a la BD
            $conn = new db();
            $conn->open();

            $dml=new clsPersonaRegimenLaboral();
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
            //	cierra la conexión con la BD
            $conn->close();
    }
}