<?php
require_once("../../library/clases/entidad.php");


class CabeceraDocumento extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='gestdoc.cabecera_documento'; //nombre de la tabla
		$this->setKey='cado_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

                $this->pagEdicion=$this->getNamePage('edicion');
                $this->pagBuscar=$this->getNamePage('buscar');

                        /* Destinos luego de actulizar, agregar o eliminar un registro */
                $this->destinoUpdate = 'CabeceraDocumento_edicion.php';
                $this->destinoInsert = 'CabeceraDocumento_edicion.php';
                $this->destinoDelete = $this->pagBuscar;

                $this->arrayNameVar[0]='nomeCampoForm';
                $this->arrayNameVar[1]='busEmpty';
                $this->arrayNameVar[2]='cadena';
                $this->arrayNameVar[3]='pg';
                $this->arrayNameVar[4]='colSearch';
                $this->arrayNameVar[5]='numForm';
	}


	function getSql(){
		$sql=new CabeceraDocumento_SQLlista();
		$sql->whereID($this->id);
		return($sql->getSQL());
	}

	function addField(&$sql){
		$sql->addField("cado_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
		$sql->addField("cado_actualusua", getSession("sis_userid"), "String");
                
                if ($_POST["hx_cado_estado"]){
                    $sql->addField("cado_estado", 1, "Number");
                }else{
                    $sql->addField("cado_estado", 0, "Number");
                }
                
                if ($_POST["hx_cado_incluir_nombre_anno"]){
                    $sql->addField("cado_incluir_nombre_anno", 1, "Number");
                }else{
                    $sql->addField("cado_incluir_nombre_anno", 0, "Number");
                }
                
                if ($_POST["hx_cado_incluir_oficina_origen"]){
                    $sql->addField("cado_incluir_oficina_origen", 1, "Number");
                }else{
                    $sql->addField("cado_incluir_oficina_origen", 0, "Number");
                }
     
	}
        
    function buscar($op,$formData,$arrayParam,$pg,$Nameobj='') {
        global $conn,$param,$nomeCampoForm;
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
        $TipDocum=$paramFunction->getValuePar('TipDocum');
        $pageEdit=$paramFunction->getValuePar('pageEdit');

        $depe_id=$formData['nbusc_depe_id'];
        
        if($busEmpty==1) { //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias

            $sql=new CabeceraDocumento_SQLlista();

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

            $rs = new query($conn, $sql,$pg,80);

            $otable = new TableSimple("","100%",8,'tLista'); // T�tulo, ancho, Cantidad de columas,id de la tabla
			/* Guardo la pagina actual en un campo oculto.  Para que al eliminar regrese a la p�gina donde estaba */
            $otable->addHtml("<input type='hidden' name='".$paramFunction->getValuePar(3)."' value='$pg'>\n");

            $button = new Button;
            $pg_ant = $pg-1;
            $pg_prox = $pg+1;
            if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,'".encodeArray($formData)."','".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
            if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,'".encodeArray($formData)."','".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");

            if ($rs->numrows()>0) {
		/* Agrego las cabeceras de mi tabla */
                $otable->addColumnHeader("<input type=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">","1%"); // Coluna com checkbox
                $otable->addColumnHeader("ID","1%", "C"); 
                $otable->addColumnHeader("Nombre","94%", "C"); 
                $otable->addColumnHeader("Usuario","5%", "C");
                $otable->addRowHead();

                while ($rs->getrow()) {
                    $id = $rs->field("cado_id"); // captura la clave primaria del recordsource
                    $usua_id=$rs->field("usua_id");

                    $param->replaceParValue($paramFunction->getValuePar(3),$pg); /* Agrego el parametro */

                    $otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" >");

                    //if($usua_id==getSession("sis_userid")){ /* Si AUN NO SE RECIBE y ES EL USUARIO*/
                        $otable->addData(addLink($rs->field("id"),"CabeceraDocumento_edicion.php?id=$id&clear=1&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"),"C");
                    //}else{
                    //    $otable->addData($numero,"C");
                    //}
                    $otable->addData($rs->field("cado_nombre"));
                    $otable->addData($rs->field("username"),"C");

                    if($rs->field("cado_estado")==9){
                        $otable->addRow('ANULADO');
                    }else{
                        $otable->addRow();
                    }
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

	/* Nombre del archivo php de la clase */
    function getNameFile() {
        return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1);
    }

    function getNamePage($accion) {
        return(str_replace('class',$accion,$this->getNameFile()));
    }
    
    
    function guardar(){
            global $conn,$param;
            $destinoUpdate=$this->destinoUpdate.$param->buildPars(true);	
            $pg=is_array($this->arrayNameVar)?$this->arrayNameVar[3]:'pg';
            $param->removePar($pg); /* Remuevo el parametro pagina */
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


            /* Aqui puedo agregar otros campos a la sentencia SQL */
            $this->addField($sql);

            /* Ejecuto el SQL */
            $sqlCommand=$sql->getSQL();
            
            $padre_id=$conn->execute("$sqlCommand  RETURNING $this->setKey");
            $error=$conn->error();
            if($error){ 
                alert($error);	/* Muestro el error y detengo la ejecuci�n */
            }else{
                    /*  muestra mensaje noticia del la base de datos, pero no detiene la ejecucion	*/
                    //$notice=$conn->notice();
                    //if($notice) 
                    //    alert($notice,0); //OJO SI SE ACTIVA MUESTRA EL WARNING AL INSERTAR IMAGENES EN LOS TEMAS
            }

            /* */
            if ($this->valueKey) {// modificación
                    $last_id=$this->valueKey; 
                    if(strpos($destinoInsert, "?")>0)
                        $destinoUpdate.="&id=$last_id";
                    else
                        $destinoUpdate.="?id=$last_id";
                    //echo $destinoUpdate;
                    redirect($destinoUpdate,"content");			

            }else{ /* Insercion */
                    if(strpos($destinoInsert, "?")>0)
                            $destinoInsert.="&id=$padre_id&clear=1";  
                    else
                            $destinoInsert.="?id=$padre_id&clear=1";

                    redirect($destinoInsert,"content");							
            }
    }


	function activar(){
		global $conn,$param;
		
		/* captura y prepara la lista de registros a ser eliminados */ 
		$arLista_anular = getParam("sel");
		if (is_array($arLista_anular)) {
		 $lista_anular = implode(",",$arLista_anular);
		}
		
		if(strtolower($this->typeKey)=='string'){
			/* debido a que el campo clave es char */
			$lista_anular=str_replace(",","','",$lista_anular); 
		}

		/* Sql a ejecutar */
		$sqlCommand="UPDATE $this->setTable SET cado_estado=CASE WHEN cado_estado=1 THEN 9 ELSE 1 END ";
		$sqlCommand.=" WHERE $this->setKey ";
		$sqlCommand.=" IN ($lista_anular) ";
		//$sqlCommand.=" AND usua_id=".getSession("sis_userid");
		$sqlCommand.=" RETURNING $this->setKey ";
                            
		/* Ejecuto la sentencia */
		$padre_id=$conn->execute($sqlCommand);
		$error=$conn->error();		
		if($error){
                    alert($error);
                }
		else{
                    redirect($this->destinoDelete.$param->buildPars(true),"content");		
		}
	}
    
}

class CabeceraDocumento_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.*,
                                    LPAD(a.cado_id::TEXT,4,'0') AS id,
                                    CASE WHEN a.cado_estado=1 THEN 'ACTIVO'
                                         WHEN a.cado_estado=9 THEN 'ANULADO'
                                    END AS estado,
                                    x.usua_login as username,
                                    y.usua_login as usernameactual
				FROM gestdoc.cabecera_documento a
				LEFT JOIN admin.usuario x ON a.usua_id=x.usua_id
                                LEFT JOIN admin.usuario y ON  a.cado_actualusua=y.usua_id
				";
	
	}
	
	function whereID($id){
		$this->addWhere("a.cado_id=$id");
	}
       
        
	function whereDescrip($contenido){
		$this->addWhere("(a.cado_titulo ILIKE '%$contenido%')");
	}

        function whereActivo(){
		$this->addWhere("a.cado_estado=1");
	}

	function orderUno(){
            $this->addOrder("a.cado_id DESC");
	}
        
	function getSQL_cbox(){
		$sql="SELECT    a.cado_id,
                                a.id||' '||a.cado_nombre
				FROM (".$this->getSQL().") AS a 
                                ORDER BY 1 DESC";
		return $sql;
	}
        

}


if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
            require_once("../../library/library.php");
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

            $dml=new CabeceraDocumento();

            switch($control){

                    case 1: // Guardar
                            $dml->guardar();
                            break;
                        
                    case 2: // Eliminar
                            $dml->eliminar();
                            break;
                        
                    case 3: // Activar/Descativar
                            $dml->activar();
                            break;                        
                        
            }
            //	cierra la conexi�n con la BD
            $conn->close();
    }
}