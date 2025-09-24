<?php
require_once("../../library/clases/entidad.php");

class Especialidades extends entidad {

    function __construct($id='',$title='') {
        $this->setTable='personal.especialidades'; //nombre de la tabla
        $this->setKey='espe_id'; //campo clave
        $this->valueKey=getParam("f_id"); //valor del campo clave
        $this->typeKey="Integer"; //tipo de dato del campo clave
        $this->id=$id;
        $this->title=$title;

		/* Destinos luego de actulizar, agregar o eliminar un registro */
        $this->destinoInsert=$this->getNamePage('buscar');
        $this->destinoUpdate=$this->getNamePage('buscar');
        $this->destinoDelete=$this->getNamePage('buscar');

		/* Datos que se retorna cuando la clase es cargada en una AvanzLookup */
        $this->return_val = '$last_id'; /* Valor que se devuelve */
        $this->return_txt = strtoupper(getParam('Sr_espe_descripcion')); /* Texo que se devuelve */

        $this->arrayNameVar[0]='nomeCampoForm';
        $this->arrayNameVar[1]='busEmpty';
        $this->arrayNameVar[2]='cadena';
        $this->arrayNameVar[3]='pg';
        $this->arrayNameVar[4]='colSearch';
        $this->arrayNameVar[5]='numForm';
    }

        function getSql(){
		$sql=new Especialidades_SQLlista();
		$sql->whereID($this->id);
		return($sql->getSQL());
	}

        function addField(&$sql){
		$sql->addField("espe_actualfecha", 'NOW()', "String");
		$sql->addField("espe_actualusua", getSession("sis_userid"), "String");
	}
    

    function buscar($op,$formData,$arrayParam,$pg=1,$Nameobj='') {
        global $conn,$param,$nomeCampoForm;
        $objResponse = new xajaxResponse();

        $arrayParam=decodeArray($arrayParam);

        $paramFunction= new manUrlv1();
        $paramFunction->setUrlExternal($arrayParam);

        if($op==1 && !is_array($formData)) $formData=decodeArray($formData);

        $cadena=is_array($formData)?trim($formData['Sbusc_cadena']):$formData;

        $busEmpty=$paramFunction->getValuePar($paramFunction->getValuePar(1));
        $colSearch=$paramFunction->getValuePar($paramFunction->getValuePar(4));
        $numForm=$paramFunction->getValuePar($paramFunction->getValuePar(5));

        if(strlen($cadena)>0 or $busEmpty==1) { //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias

            /* Consulta sql a mostrar */
            $sql=new Especialidades_SQLlista();

            //se analiza la columna de b�squeda
            switch($colSearch) {
                case 'codigo': // si se recibe el campo id
                    $sql->whereID($cadena);
                    break;

                default:// si se no se recibe ningun campo de busqueda
                    //if(ctype_digit($cadena)) //si la cadena recibida son todos digitos
                        //$sql->whereID($cadena);
                    //else
                        $sql->whereDescrip($cadena);

                    break;
            }

            $sql->orderUno();
            $sql=$sql->getSQL();

			/* Creo my objeto Table */
            $otable = new TableSimple(iif($cadena,'!=','','RESULTADO DE: '.$cadena,''),"100%",6,'tLista'); // T�tulo, ancho, Cantidad de columas,id de la tabla

			/* Guardo la p�gina actual en un campo oculto.  Para que al eliminar regrese a la p�gina donde estaba */
            $otable->addHtml("<input type='hidden' name='".$paramFunction->getValuePar(3)."' value='$pg'>\n");
            
            //guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
            if ($op==1 && !$nomeCampoForm) {
                $param->replaceParValue($paramFunction->getValuePar(2),$cadena); /* reemplazo el par�metro */
            }
            //setSession("cadSearch",$cadena);

            $rs = new query($conn, strtoupper($sql),$pg,80);

            $button = new Button;
            $pg_ant = $pg-1;
            $pg_prox = $pg+1;
            if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,'".encodeArray($formData)."','".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
            if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,'".encodeArray($formData)."','".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");

            if ($rs->numrows()>0) {
                if (!$nomeCampoForm) /* Si no estoy en una b�squeda avanzada (AvanzLookup) */
                    $otable->addColumnHeader("<input type=\"checkbox\" id=\"checkall\" >"); // Coluna com checkbox

					/* Agrego las cabeceras de mi tabla */
                $otable->addColumnHeader("Nombre","100%", "L"); // T�tulo, ancho, alineaci�n

                $otable->addRowHead();

                while ($rs->getrow()) {
                    $id = $rs->field("espe_id"); // captura la clave primaria del recordsource
                    $campoTexto_de_Retorno = especialChar($rs->field("espe_descripcion"));

                    if ($nomeCampoForm) { /* si la llamada es desde la busqueda avanzada (AvanzLookup) */
                        $otable->addData(addLink($rs->field("espe_descripcion"),"javascript:update('$id','$campoTexto_de_Retorno',$numForm)","Click aqu&iacute; para seleccionar el registro"));
                    }elseif($op!=3) {  /* Si estoy en la p�gina normal */
							/* agrego pg como par�metro a ser enviado por la URL */
                        $param->replaceParValue($paramFunction->getValuePar(3),$pg); /* Agrego el par�metro */

                        $otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" >");
                        $otable->addData(addLink($campoTexto_de_Retorno,"Especialidades_edicion.php?id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
                    }

                    $otable->addRow();
                }
                $contenido_respuesta=$button->writeHTML();
                $contenido_respuesta.=$otable->writeHTML();
                $contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()." </div>";
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
        if($op==1) {//si es llamado para su funcionamiento en ajax con retornoa a un div
            $objResponse->addAssign($Nameobj,'innerHTML', utf8_encode($contenido_respuesta));
            $objResponse->addscript("activaSorter()"); // Para activar el orden en la tabla
            $objResponse->addscript("func_jquerytablas()"); // Para activar las funciones de css de la tabla
            return $objResponse;
        }
        else
            if($op==3) {//si es llamado para su funcionamiento en ajax, desde un una busqueda avanzada, con retorno a un objeto
                if($Nameobj) {
                    $objResponse->addScript($Nameobj .' = "'.utf8_encode($campoTexto_de_Retorno).'";');
                    return $objResponse;
                }
                else
                    return $campoTexto_de_Retorno;
            }
            else//si es llamado como una simple funciona de PHP
                return $contenido_respuesta	;
    }


    function buscarEspecialidad($op,$cadena,$colSearch,$colOrden=1,$Nameobj,$accion=1)
    {
            global $conn;

            $objResponse = new xajaxResponse();
            //$objResponse->setCharEncoding('iso-8859-1');	

            $cadena=trim($cadena);

            if($cadena){

                    $sql=new Especialidades_SQLlista();

                    //se analiza la columna de busqueda
                    switch($colSearch){
                            case 'prov_id': // si se recibe el campo id
                                    $sql->whereID($cadena);								
                                    break;

                            default:// si se no se recibe ningun campo de busqueda
                                    if(ctype_digit($cadena)) //si la cadena recibida son todos digitos
                                            $sql->whereCodigo($cadena);
                                    else
                                            if(strlen($cadena)<3){
                                                    $objResponse->addAssign($Nameobj,'innerHTML', '');
                                                    $objResponse->addAlert('se espera al menos 3 caracteres de busqueda...');
                                                    return $objResponse;
                                            }
                                            else{
                                                    $sql->whereDescrip($cadena);
                                            }
                                    break;
                    }

                    $sql->orderUno();
                    $sql=$sql->getSQL();
    //  echo $sql;
    //	  $objResponse->addAlert($sql);

                            $otable = new  Table("","100%",9);
                            $btnFocus="";
                            $rs = new query($conn, strtoupper($sql));
                            if ($rs->numrows()>0) {
                                            $link=addLink("Cerrar","javascript:xajax_clearDiv('$Nameobj')");        
                                            $otable->addColumnHeader("$link",false,"1%","C"); 
                                            $otable->addColumnHeader("Especialidad","98%", "L"); 
                                            $otable->addColumnHeader("",false,"1%","C"); 

                                            $otable->addRow(); // adiciona la linea (TR)
                                            while ($rs->getrow()) {
                                                    $id = $rs->field("espe_id");// captura la clave primaria del recordsource
                                                    $campoTexto_de_Retorno = especialChar($rs->field("espe_descripcion"));

                                                    $button = new Button;
                                                    $button->setDiv(FALSE);
                                                    $button->setStyle("");
                                                    $button->addItem("Aceptar","javascript:xajax_eligeEspecialidad($id,'$campoTexto_de_Retorno','$accion')","content",2,0,"botonAgg","button","","btn_$id");
                                                    $otable->addData($button->writeHTML());	

                                                    $otable->addData($rs->field("espe_descripcion"));
                                                    if($accion==1){
                                                        $otable->addData("<a class=\"link\" href=\"#\" onClick=\"javascript:modiEspecialidad($id)\"><img src=\"../../img/editar.gif\" border=0 align=absmiddle hspace=1 alt=\"Editar\"></a>");
                                                    }
                                                    else {
                                                           $otable->addData("&nbsp;");
                                                    }
                                                    $otable->addRow();
                                                    $btnFocus=$btnFocus?$btnFocus:"btn_$id";
                                            }

                                    $contenido_respuesta=$otable->writeHTML();
                                    $contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

                            } else {
                                    $otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
                                    $otable->addRow();
                                    $contenido_respuesta=$otable->writeHTML();
                            }
                    }
            else
                    $contenido_respuesta="";

        $objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
        $objResponse->addScript("document.frm.$btnFocus.focus()");

        return $objResponse;

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
            //echo $sql->getSQL();
            $padre_id=$conn->execute("$sqlCommand  RETURNING $this->setKey");

            $error=$conn->error();
            if($error){ 
                     if(stristr($error,"duplicate key value")){
                         $x=substr($error,strpos($error,"constraint")+11,(strpos($error,"DETAIL")-strpos($error,"constraint"))-12);
                         $error="Valor Duplicado:".$x;
                     }

                    alert(substr($error,0,300));	/* Muestro el error y detengo la ejecuci�n */
            }else{
                    /*  muestra mensaje noticia del la base de datos, pero no detiene la ejecucion	*/
                    $notice=$conn->notice();
                    if($notice){ 
                        //alert($notice,0);
                    }
            }
            /* */
            if($nomeCampoForm){//si se llama desde una ventana emergente (avanzlookup) para seleccionar un valor
                    /* Datos que se retornan desde un (avanzlookup) */
                    $return_val=$this->return_val; /* Valor que se devuelve */
                    $return_txt=$this->return_txt; /* Texo que se devuelve */

                    /* Comandos Javascript */		
                    echo "<script language=\"javascript\">
                                    parent.opener.parent.content.document.forms[0]._Dummy$nomeCampoForm.value = '$return_txt';
                                    parent.opener.parent.content.document.forms[0].$nomeCampoForm.value = $padre_id;
                                    parent.opener.parent.content.document.getElementById('divResultado3').innerHTML = '';
                                    parent.parent.close();
                            </script>";
            }else{ /* Si se llama desde una p�gina normal */
            
                    if ($this->valueKey) {// modificación
                            $last_id=$this->valueKey; 
                            if(strpos($destinoInsert, "?")>0)
                                    $destinoUpdate.="&id=$last_id";
                            else
                                    $destinoUpdate.="?id=$last_id";

                            redirect($destinoUpdate,"content");			

                    }else{ /* Inserci�n */
                            /*a�ado el id del registro ingresado*/
                            $last_id=$padre_id; /* Obtengo el Id del registro ingreado.  Ojo esto solo funciona si el campo clave es un serial (est� basado en una secuencia) */								
                            if(strpos($destinoInsert, "?")>0){
                                    $destinoInsert.="&id=$last_id&clear=1";  
                            }else{
                                    $destinoInsert.="?id=$last_id&clear=1";
                            }
                            /* Envio el "id" para cuando regreso a la misma p�gina de edici�n y el 
                            "clear" para cuando regreso a la lista y deseo que se vea el �ltimo registro ingresado, 
                            con el clear se limpia la variable "cadSearch" o "cadSearchhijo" */
                            //echo $destinoInsert;	
                            redirect($destinoInsert,"content");							
                     }
            }
    }

    
    function getNameFile() {
        return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1);
    }

    function getNamePage($accion) {
        return(str_replace('class',$accion,$this->getNameFile()));
    }

} /* Fin de la clase */

class Especialidades_SQLlista extends selectSQL {

    function __construct() {
        $this->sql="SELECT a.*,
                           c.usua_login AS username,
                           d.usua_login AS usernameactual
                            FROM personal.especialidades a 
                            LEFT JOIN usuario c ON a.usua_id=c.usua_id
                            LEFT JOIN usuario d ON a.espe_actualusua=d.usua_id                                                       
                        ";
    }
    function whereID($id) {
        $this->addWhere("a.espe_id=$id");
    }

    function whereDescrip($descrip) {
        if($descrip) $this->addWhere("(a.espe_descripcion ILIKE '%$descrip%')");
    }

    function orderUno() {
        $this->addOrder("a.espe_id DESC");
    }
    
    function getSQL_cbox(){
		$sql="SELECT    a.espe_id,
                                a.espe_descripcion
                            FROM (".$this->getSQL().") AS a ";
		return $sql;
    }  
}

/* Llamando a la subclase */
$control=base64_decode($_GET['control']);
if($control) {
    require_once($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");

	/*	verificaci�n a nivel de usuario */
    verificaUsuario(1);
    verif_framework();

    $param= new manUrlv1();
    $param->removePar('control');

    //	conexi�n a la BD
    $conn = new db();
    $conn->open();

    $dml=new Especialidades();
    $param->removePar($dml->getArrayNameVarID(3)); /* Remuevo el par�metro */

	/* Recibo la p�gina actual de la lista y lo agrego como par�metro a ser enviado por la URL.  Este dato viene por POST */
    $pg = getParam($dml->getArrayNameVarID(3));
    $param->addParComplete($dml->getArrayNameVarID(3),$pg); /* Agrego el par�metro */


    switch($control) {
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