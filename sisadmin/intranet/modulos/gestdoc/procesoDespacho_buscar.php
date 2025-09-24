<?php
/* Modelo de página que apresenta um formulario con criterios de busqueda */
include($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("procesoDespacho_class.php");
include("registroDespacho_class.php");
include("../catalogos/catalogosDependencias_class.php");
include("../catalogos/catalogosArchivadores_class.php");
include("../admin/adminUsuario_class.php");
include("../catalogos/catalogosTipoExpediente_class.php");
include("registroDespacho_edicionAdjuntosClass.php");
include("registroDespachoEnvios_class.php");
include("despachoRequisitos_class.php");
include("../admin/valoresSemaforo_class.php"); 
include("gruposDerivaciones_class.php");

class carDeriva_class {
    function __construct(){
       $this->cuenta=0;
       $this->carrito=array();
       $this->id=array();
       //$this->eliminados=array();
    }

    function Add($contenido){
        $hallado=array_search($contenido['id'], $this->id);
        if(!strlen($hallado)) {
            $this->carrito[]=$contenido;
            $this->id[]=$contenido['id'];
            $this->cuenta++;
            return(1);
        }else{
             return(0);
        }
    }

    function Mod($id,$contenido){
        $this->carrito[$id]=$contenido;
    }

    //elimina un producto del carrito. recibe el id del registro a eliminar
    function Del($id){
        /* Agrego al array de eliminados el id del registro a eliminar.  El array eliminados se usa al editar el registro  */
	if($this->carrito[$id]['tx_hijo_id'])
            $this->eliminados[]=$this->carrito[$id]['tx_hijo_id'];

        unset($this->id[$id]);
	unset($this->carrito[$id]);	/* Elimino el registro del array */
	$this->id--;
        $this->cuenta--;
    }

    function getConteo(){
        return ($this->cuenta);
    }

    function getArray(){
        return ($this->carrito);
    }

    function getArrayEliminados(){
        return ($this->eliminados);
    }
}

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$busEmpty = 1; //getParam("busEmpty"); // para definir si se muestran los datos en el primer ingreso
$depe_id=getParam("nbusc_depe_id");
$user_id=getParam("nbusc_user_id");
$semaforo=getParam("semaforo");

$bd_depe_id=getSession("sis_depeid");
$bd_user_id=getSession("sis_userid");

$param= new manUrlv1();
$param->removePar('clear');

$myClass = new despachoProceso(0,NAME_EXPEDIENTE." en Proceso");

if (!isset($_SESSION["ocarrito_deriva"])){
    $_SESSION["ocarrito_deriva"] = new carDeriva_class();
}

if ($clear==1 || $clear==2) {
	setSession("cadSearch","");
        $depe_id=getSession("sis_depeid");
        
        
        if (!getSession("SET_TODOS_USUARIOS"))
        //if(!$user_id)
        {
            $user_id=getSession("sis_userid");
        }
}

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "despachoProceso","buscar"),"");
$xajax->registerExternalFunction(array("verDetalle", "despacho","verDetalle"),"");
$xajax->registerExternalFunction(array("clearDiv", "despacho","clearDiv"),"");
$xajax->registerFunction("imprimir");
$xajax->registerFunction("getUsuarios");
$xajax->registerFunction("addCarrito");
$xajax->registerFunction("elimCarrito");
$xajax->registerFunction("verCarrito");
$xajax->registerFunction("limpiarCarrito");
$xajax->registerFunction("getDerivar");
$xajax->registerFunction("guardar_derivaciones");
$xajax->registerFunction("elimDerivar");
$xajax->registerFunction("getAdjuntar");
$xajax->registerFunction("guardar_adjuntar");
$xajax->registerFunction("autorizar");
$xajax->registerFunction("deshace_autorizacion");
$xajax->registerFunction("extraeAdjuntados");
$xajax->registerFunction("getArchivar");
$xajax->registerFunction("guardar_archivar");
$xajax->registerFunction("getArchivadores");
$xajax->registerFunction("nvoArchivador");
$xajax->registerFunction("addArchivador");
$xajax->registerFunction("getValidarRequisitos");
$xajax->registerFunction("guardar_validarRequisitos");
$xajax->registerFunction("getComentario");
$xajax->registerFunction("guardar_requisitoTexto");
$xajax->registerFunction("guardar_validarRequisitosTodos");
$xajax->registerFunction("responder");
$xajax->registerFunction("upload");
$xajax->registerFunction("listaHistorialUploads");
$xajax->registerFunction("eliminarFile");
$xajax->registerFunction("enviar_email");
$xajax->registerFunction("listaHistorialEnvios");
$xajax->registerFunction("seguir");

function imprimir($form){

    $objResponse = new xajaxResponse();
    
    
    $array=$form['sel'];
    if(!is_array($array)){
        $objResponse->addAlert('No existen datos para procesar, probablemente no ha seleccionado registro...!');
    }else{

        $regSeleccionado=explode("_",$array[0]);
        $objResponse->addScript("AbreVentana('rptHT.php?id=".$regSeleccionado[0]."')");
    }
    return $objResponse;

}

function getUsuarios($op,$depe_id,$user_id,$arrayParam){

    $objResponse = new xajaxResponse();
        
    //$objResponse->addAlert($tipoDespacho);
    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");
    $usuarios=new clsUsersDatosLaborales_SQLlista();
    $usuarios->whereDepeID($depe_id);
    $usuarios->whereActivo();
    $sqlUsuarios=$usuarios->getSQL_cbox();
    $oForm->addField("Usuario: ",listboxField("Usuario",$sqlUsuarios,"nbusc_user_id",$user_id,"-- Todos los Usuarios --","onChange=\"xajax_buscar(1,xajax.getFormValues('frm'),'$arrayParam',1,'DivResultado')\""));

    $contenido_respuesta=$oForm->writeHTML();
    $objResponse->addAssign('divUsuarios','innerHTML', $contenido_respuesta);

    if($op==1){
        return $objResponse;
    }
    else
        return $contenido_respuesta	;
}

function addCarrito($deriva,$proveido)
{
    $objResponse = new xajaxResponse();
    
    $usua_idcrea=getSession("sis_userid");

    $nvoArrayDeriva=$deriva;
    //$nvoArrayConCopia=$concopia;
    //if(!$nvoArrayDeriva[0] && !$nvoArrayConCopia[0]){
    
    if(count($nvoArrayDeriva)==0){
        $objResponse->addAlert('Proceso cancelado, Ingrese dependencia a Derivar ');
        $objResponse->addScript("document.frmDeriva.Sx_dependencia.focus()");
	return $objResponse;
    }

    if(!$proveido){
        $objResponse->addAlert('Proceso cancelado, Ingrese Proveido de Atención');
        $objResponse->addScript("document.frmDeriva.Sx_desp_proveido.focus()");
	return $objResponse;
    }

    $data= new manUrlv1();
    $data->removeAllPar(0);
    //derivación a dependencias
    if(count($nvoArrayDeriva)>0){
        //busco GRUPOS
        foreach ($deriva as $i => $value) {
             $pos = strpos($value, "@" );
             if ($pos !== false) {
                 $grupo_id=explode('@',$value);
                 if(is_array($grupo_id)){
                    $sqlGrupos=new clsGruposDerivaciones_SQLlista();
                    $sqlGrupos->whereID($grupo_id[0]);
                    $sqlGrupos->setDatos();                    
                    if($sqlGrupos->field('grde_grupo')){
                       $arElementosGrupo=explode(",",$sqlGrupos->field('grde_grupo'));
                        for($x=0;$x<count($arElementosGrupo);$x++){
                            $nvoArrayDeriva[]=$arElementosGrupo[$x];
                        }
                    }
                 }
             }
        }
        
        foreach ($nvoArrayDeriva as $i => $value) {
            $pos = strpos($value,"@");
            if ($pos === false && $value!='') {
                $arrayDepeUser=explode('_',$value);
                $idDeriva=$arrayDepeUser[0];
                $depe_id=$arrayDepeUser[0];//dependencia
                
//                $elemento=$depe_id;
//                if($arrayDepeUser[1]>0){
//                    $elemento.="_".$arrayDepeUser[1];//usuario
//                }                
//                $elemento.=" ";
                $elemento=getDbValue("SELECT depe_nombre FROM catalogos.dependencia WHERE depe_id=$depe_id");
                    
                //$elemento=$value;
                $usua_id='';
                if($arrayDepeUser[1]>0){
                    $usua_id=$arrayDepeUser[1];//usuario                    
                    $elemento.= ' ['.getDbValue("SELECT xx.usua_login||'-'||xxxx.pers_apellpaterno||' '||SUBSTRING(xxxx.pers_nombres,1,CASE WHEN POSITION(' ' IN xxxx.pers_nombres)>0 THEN POSITION(' ' IN xxxx.pers_nombres) ELSE 100 END)  AS usuario
                                            FROM admin.usuario xx
                                            LEFT JOIN personal.persona_datos_laborales xxx on  xx.pdla_id=xxx.pdla_id
                                            LEFT JOIN personal.persona xxxx on  xxx.pers_id=xxxx.pers_id                                
                                            WHERE xx.usua_id=$usua_id").']';                    
                    $idDeriva.='_'.$usua_id;
                }

               $data->addParComplete('id', $idDeriva);
               $data->addParComplete('depe_id', $depe_id);
               $data->addParComplete('usua_id', $usua_id);
               $data->addParComplete('elemento',$elemento);
               $data->addParComplete('proveido',$proveido);
               $data->addParComplete('cc','');
               $data->addParComplete('usua_idcrea',$usua_idcrea);
               $data->addParComplete('dede_estado',2);

               $_SESSION["ocarrito_deriva"]->Add($data->getUrl());
            }
        }
    }
    $data= new manUrlv1();
    $data->removeAllPar(0);
    
    $objResponse->addScript("$('#Dependencia_Destino').val(null).trigger('change');");
    $objResponse->addScript("document.frmDeriva.Sx_desp_proveido.value=''");
    $objResponse->addScript("xajax_verCarrito()");
    return $objResponse;
}


function verCarrito()
{
	$objResponse = new xajaxResponse();

        if($_SESSION["ocarrito_deriva"]->getConteo()==0){
            $objResponse->clear('divDerivacion','innerHTML');
            return $objResponse;
        }

	$otable = new  Table("","100%",5);
	$otable->setColumnTD("ColumnBlueTD") ;
	$otable->setColumnFont("ColumnWholeFont") ;
	$otable->setFormTotalTD("FormTotalBlueTD");
	$otable->setAlternateBackTD("AlternateBackBlueTD");

	$otable->addBreak("<div align='center' style='color:#000000'><b>:: DERIVACIONES REALIZADAS ::</b></div>");
	$otable->addColumnHeader("Eli"); // Título, Ordenar?, ancho, alineación
	$otable->addColumnHeader("Dependencia/Usuario Destino",false,"45%", "L"); // Título, Ordenar?, ancho, alineación
	$otable->addColumnHeader("Proveido",false,"45%", "L"); // Título, Ordenar?, ancho, alineación
	$otable->addColumnHeader("Dist",false,"5%", "L"); // Título, Ordenar?, ancho, alineación
        $otable->addColumnHeader("Creado Por",false,"5%", "L"); // Título, Ordenar?, ancho, alineación
	$otable->addRow(); // adiciona la linea (TR)

	$array=$_SESSION["ocarrito_deriva"]->getArray();
	foreach($array as $arrItem) {
		$items=key($array); /* Para guardar el key del array padre */
                $usua_idcrea=$arrItem['usua_idcrea'];
                $dede_estado=$arrItem['dede_estado'];

                //si el registro no esta procesado y el usuario que esta editando es el que lo ha creado
                if($dede_estado==2 && $usua_idcrea==getSession("sis_userid"))
                    $otable->addData("<a class=\"link\" href=\"#\" onClick=\"javascript:if(confirm('Eliminar este registro?')) {xajax_elimCarrito($items)}\"><img src=\"../../img/delete.gif\" border=0 align=absmiddle hspace=1 alt=\"Eliminar\"></a>");
                else{
                    $otable->addData("&nbsp;");
                }

		$otable->addData($arrItem['elemento']);
		$otable->addData($arrItem['proveido']);
		$otable->addData($arrItem['cc']);

                $usua_idCrea=$arrItem['usua_idcrea'];
                $nameUsuaCrea=getDbValue("SELECT usua_login FROM usuario WHERE usua_id=$usua_idCrea");
                $otable->addData($nameUsuaCrea);
                //$otable->addData($items);

		$otable->addRow();
		next($array); /* voy al siguiente registro del array padre */
	}

        $contenido_respuesta.=$otable->writeHTML();
	$contenido_respuesta.="<div class='BordeatablaBlue' style='width:50%;float:left' align='left'>&nbsp;</div>";
	$contenido_respuesta.="<div class='BordeatablaBlue' style='width:50%;float:right' align='right'>Total Items: ".$_SESSION["ocarrito_deriva"]->getConteo()."</div>";


	$objResponse->addAssign('divDerivacion','innerHTML', $contenido_respuesta);
	return $objResponse;
}

//funcion que elimina un item al carrito
function elimCarrito($id)
{
    $objResponse = new xajaxResponse();
    $_SESSION["ocarrito_deriva"]->Del($id);
    return(verCarrito());
}


function limpiarCarrito()
{
    $objResponse = new xajaxResponse();

    $array=$_SESSION["ocarrito_deriva"]->getArray();
    foreach($array as $arrItem) {
        $id=key($array);
        $_SESSION["ocarrito_deriva"]->Del($id);
        next($array); /* voy al siguiente registro del array padre */
    }
    return $objResponse;
}


function getDerivar($ar_registros_seleccionados,$nameDiv){
    
    $objResponse = new xajaxResponse();        
    
    if(is_array($ar_registros_seleccionados)){
        $regSeleccionados=implode(",",$ar_registros_seleccionados);
    }else{
        $regSeleccionados=$ar_registros_seleccionados;
    }

    $button = new Button;
    //$button->addItem("Limpiar Datos","javascript:ocultarObj('Limpiar Datos',3);if(confirm('Seguro de Limpiar datos?')){limpiarDatos()}","content",2,"","link");
    $button->addItem("Derivar","javascript:ocultarObj('Derivar',3);xajax_guardar_derivaciones(xajax.getFormValues('frmDeriva'),document.frm.nbusc_depe_id.value)","content",2);
    $contenido_respuesta=$button->writeHTML();

    /* Formulario */
    $form = new Form("frmDeriva", "", "POST", "controle", "100%",false);
    $form->setLabelWidth("20%");
    $form->setDataWidth("80%");    
    
    //$form->addHidden("reg_seleccionados","$regSeleccionados");
    $form->addField("Reg.Seleccionado(s): ",textField("Reg.Seleccionado","reg_seleccionados","$regSeleccionados",80,150,"readonly")); 
    $form->addBreak("DATOS DE LA DERIVACION", true);

    $sql = array(1 => "Destino: Dependencia",
                 2 => "Destino: Empleados",
                 3 => "Destino: Grupo");
    
    $form->addField(listboxField("Tipo_destino", $sql, "Sr_tipo_destino",1).':',listboxField("Dependencia_Destino","","Sx_dependencia","","seleccione Destino","","","class=\"my_select_box2\" multiple "));
    $form->addField("Proveido de Atenci&oacute;n: ",textField("Proveido","Sx_desp_proveido","",80,150));
    
    /* botones */
    $button = new Button;
    $button->addItem("Agregar","javascript:xajax_addCarrito($('#Dependencia_Destino').val(),document.frmDeriva.Sx_desp_proveido.value);","content",2,"",'botao','button');
    $button->align('L');    
    $form->addField("",$button->writeHTML());

    $form->addHtml("<tr><td colspan=2><div id='divDerivacion' >\n"); //pide datos de afectacion presupuestal
    $form->addHtml("</div></td></tr>\n");

    $contenido_respuesta.=$form->writeHTML();
    
    $objResponse->addAssign($nameDiv,'innerHTML', $contenido_respuesta);
    
    $objResponse->addScript("mySelect();
                                
                            $('#myModalOpc').modal('show');
                            ");
    return $objResponse;    
}


function guardar_derivaciones($formdata,$depe_idorigen)
{
    global $conn;
    $objResponse = new xajaxResponse();
    $objResponse->setCharEncoding('utf-8');

    if($formdata['reg_seleccionados']==''){
        $objResponse->addAlert('Proceso cancelado, No existen registros seleccionados para procesar... ');
        return $objResponse;
    }

    if($_SESSION["ocarrito_deriva"]->getConteo()==0){
        $objResponse->addAlert('Proceso cancelado, Sin registros para procesar... ');
        return $objResponse;
    }
    
    /*genero un array con los registros seleccionados*/
    $regSeleccionados=$formdata['reg_seleccionados'];
    $nvoArraySeleccionados=explode(",",$regSeleccionados);

    // objeto para instanciar la clase sql
    $setTable='despachos_derivaciones';
    $setKey='dede_id';
    $typeKey='Number';

    /********* INICIO PROCESO DE GRABACION EN EL HIJO *********/
    $array=$_SESSION["ocarrito_deriva"]->getArray();
    foreach($array as $arrItem){

        $depe_iddestino=$arrItem['depe_id'];
        $usua_iddestino=$arrItem['usua_id'];
        $dede_concopia=$arrItem['cc']=='Cc'?1:0;
        $dede_proveido=$arrItem['proveido'];

        //$depe_idorigen=getSession("sis_depeid");
        $usua_idorigen=getSession("sis_userid");
        $usua_idcrea=getSession("sis_userid"); /* Id del usuario que graba el registro */
	

        for ($i = 0; $i < count($nvoArraySeleccionados); $i++) {
            $sql = new UpdateSQL();

            $sql->setTable($setTable);
            $sql->setKey($setKey,0,$typeKey);
            $sql->setAction("INSERT"); /* Operación */

            /* Campos */
            //obtengo el ID del padre y el Id del Hijo, esto se construye en buscar de procesoDespachoClass
            $arrayPadreHijo=explode('_',$nvoArraySeleccionados[$i]);
            $desp_id=$arrayPadreHijo[0];
            $dede_idrelacionado=$arrayPadreHijo[1];
            //$objResponse->addAlert($arrayPadreHijo[1]);
            
            $sql->addField('desp_id',$desp_id, "Number");
            $sql->addField('dede_idrelacionado', $dede_idrelacionado, "Number");

            $sql->addField('depe_idorigen', $depe_idorigen, "Number");
            $sql->addField('usua_idorigen', $usua_idorigen, "Number");

            $sql->addField('depe_iddestino', $depe_iddestino, "Number");
            $sql->addField('usua_iddestino', $usua_iddestino, "Number");
            $sql->addField('dede_concopia', $dede_concopia, "Number");
            $sql->addField('dede_proveido', $dede_proveido, "String");
            $sql->addField('dede_donde_se_creo', 1, "Number");
            $sql->addField('usua_idcrea', $usua_idcrea, "Number");


            $sql=$sql->getSQL();

            $sql= strtoupper($sql);
            

            $conn->execute($sql);
            $error=$conn->error();
            if($error){
                $objResponse->addAlert($error);
                return $objResponse;
            }else{
                if($formdata['hx_mantener_copia']==1){//si solicita mantener copia
                        $sql="INSERT INTO gestdoc.despachos_derivaciones 
                                        (desp_id,
                                                dede_idrelacionado,
                                                depe_idorigen,
                                                usua_idorigen,
                                                depe_iddestino,
                                                dede_proveido,
                                                dede_donde_se_creo,
                                                usua_idcrea,
                                                dede_estado,
                                                usua_idrecibe,
                                                dede_fecharecibe)
                                    (SELECT desp_id,
                                                dede_idrelacionado,
                                                depe_idorigen,
                                                usua_idorigen,
                                                depe_iddestino,
                                                dede_proveido,
                                                dede_donde_se_creo,
                                                usua_idcrea,
                                                dede_estado,
                                                usua_idrecibe,
                                                dede_fecharecibe
                                            FROM gestdoc.despachos_derivaciones 
                                            WHERE dede_id=$dede_idrelacionado)";
                                
                        $conn->execute($sql);
                        $error=$conn->error();
                        if($error){
                            $objResponse->addAlert($error);
                            return $objResponse;
                        }

                }
            }
            
       }
    }
    /********* FIN PROCESO DE GRABACION EN EL HIJO *********/
    $objResponse->addScript("limpiarDatos()");

    $Sbusc_cadena=$formdata['Sbusc_cadena'];
    $paramFunction= new manUrlv1();
    $paramFunction->removeAllPar();
    $paramFunction->addParComplete('colSearch','');
    $paramFunction->addParComplete('colOrden','1');
    $paramFunction->addParComplete('busEmpty',1);
    
    if($Sbusc_cadena){
        $paramFunction->addParComplete('Sbusc_cadena',$Sbusc_cadena);
    }
    
    $objResponse->addScript("xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')");
    $objResponse->addScript("$('#myModalOpc').modal('hide')");
    return $objResponse;
}


function elimDerivar($formdata)
{
    global $conn;
    $objResponse = new xajaxResponse();
    $objResponse->setCharEncoding('utf-8');
    
    $arLista_elimina=$formdata['sel'];

    if(!is_array($arLista_elimina)){
        $objResponse->addAlert('Proceso cancelado, No existen registros seleccionados para procesar... ');
        return $objResponse;
    }
    
    $lista_elimina='';
    //echo $arLista_elimina[0];
    for ($i = 0; $i < count($arLista_elimina); $i++) {
            $arrayPadreHijo=explode('_',$arLista_elimina[$i]);
            $lista_elimina.=iif($lista_elimina,'==','','',',').$arrayPadreHijo[0];
    }

    //$objResponse->addAlert($lista_elimina);

    $sql ="DELETE FROM despachos_derivaciones 
                        WHERE desp_id IN ($lista_elimina) 
                            AND usua_idcrea=".getSession("sis_userid"). " 
                            AND dede_estado=2 AND dede_donde_se_creo=1 " ;
    
    $conn->execute($sql);
    $error=$conn->error();
    if($error){
        $objResponse->addAlert($error);
        return $objResponse;
    }

    $Sbusc_cadena=$formdata['Sbusc_cadena'];
    $paramFunction= new manUrlv1();
    $paramFunction->removeAllPar();
    $paramFunction->addParComplete('colSearch','');
    $paramFunction->addParComplete('colOrden','1');
    $paramFunction->addParComplete('busEmpty',1);
    
    if($Sbusc_cadena)
        $paramFunction->addParComplete('Sbusc_cadena',$Sbusc_cadena);
    
    $objResponse->addScript("xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')");
    return $objResponse;
}

function getArchivar($ar_registros_seleccionados,$motivo,$nameDiv){
    global $bd_user_id;
    
    $objResponse = new xajaxResponse();        
    
    
    if(is_array($ar_registros_seleccionados)){
        $regSeleccionados=implode(",",$ar_registros_seleccionados);
        $registro=explode("_",$ar_registros_seleccionados[0]);
        $id_uno=$registro[1];
    }else{
        $regSeleccionados=$ar_registros_seleccionados;
        $registro=explode("_",$regSeleccionados);
        $id_uno=$registro[1];
    }
    $bd_depe_id=getDBValue("SELECT depe_iddestino FROM gestdoc.despachos_derivaciones WHERE dede_id=$id_uno");
            
    
    $button = new Button;
    $button->addItem("Archivar","javascript:ocultarObj('Archivar',3);xajax_guardar_archivar(xajax.getFormValues('frmArchivar'))","content",2);
    $contenido_respuesta=$button->writeHTML();
    

    /* Formulario */
    $form = new Form("frmArchivar", "", "POST", "controle", "100%",false);
    $form->setLabelWidth("20%");
    $form->setDataWidth("80%");
    //$form->addHidden("reg_seleccionados","$regSeleccionados");
    $form->addField("Reg.Seleccionado(s): ",textField("Reg.Seleccionado","reg_seleccionados","$regSeleccionados",80,150,"readonly")); 
    $form->addBreak("ARCHIVAR EN:", true);

    $form->addHtml("<tr><td colspan=2><div id='divArchivador' >\n"); //pide datos de afectacion presupuestal

    $form->addHtml(getArchivadores(2,$bd_depe_id,$bd_user_id,''));
    $form->addHtml("</div></td></tr>\n");

    $form->addField("Motivo: ",textField("Motivo","Sx_motivo","$motivo",80,150));
    
    $contenido_respuesta.=$form->writeHTML();
    
    $objResponse->addAssign($nameDiv,'innerHTML', $contenido_respuesta);
    
    $objResponse->addScript("$('.my_select_box').select2({
                                placeholder: 'Seleccione un elemento de la lista',
                                width: '98%',
                                allowClear: true
                             });
                                
                             $('#myModalOpc').modal('show');
                             ");
    return $objResponse;    
}



function getArchivadores($op,$depe_id,$user_id,$nvoValor){

    $objResponse = new xajaxResponse();
    //$objResponse->addAlert($tipoDespacho);
    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");

    $link="<a class=\"linkBlack\" href=\"#\" onClick=\"javascript:xajax_nvoArchivador('$depe_id','$user_id')\" title=\"Click para Agregar nuevo Archivador\">Agregar nuevo Archivador</a>";
    $oForm->addField("",$link);
        
    $archivador=new archivador_SQLlista();
    $archivador->whereMisArchivadores($depe_id,$user_id);
    $archivador->whereDisponible();
    $sqlArchivador=$archivador->getSQL_cbox();
    $oForm->addField("Archivador: ",listboxField("Archivador",$sqlArchivador,"nx_archivador","$nvoValor","-- Seleccione Archivador --"));        

    $contenido_respuesta=$oForm->writeHTML();
    $objResponse->addAssign('divArchivador','innerHTML', $contenido_respuesta);

    if($op==1){
        return $objResponse;
    }
    else
        return $contenido_respuesta	;
}

function nvoArchivador($depe_id,$user_id){
    $objResponse = new xajaxResponse();

    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");

    $button = new Button;
    $button->setDiv("false");
    $button->align("L");
    $button->addItem(" Agregar ","javascript:if(document.frmArchivar.Sr_arch_descripcion.value!=''){xajax_addArchivador('$depe_id','$user_id',document.frmArchivar.Sr_arch_descripcion.value)}","content",2);
    $button->addItem(" Cancelar ","javascript:xajax_getArchivadores(1,'$depe_id','$user_id','')","content");

    $oForm->addField("Archivador: ",textField("Archivador","Sr_arch_descripcion","",80,80));
    $oForm->addField("",$button->writeHTML());

    $contenido_respuesta=$oForm->writeHTML();
    $objResponse->addAssign("divArchivador",'innerHTML', $contenido_respuesta);
    $objResponse->addScript("document.frmArchivar.Sr_arch_descripcion.focus()");
    return $objResponse;
}

function addArchivador($depe_id,$user_id,$descripcion,$oldVal='')
{
    global $conn;

    $objResponse = new xajaxResponse(); 
    
    $usua_id = getSession("sis_userid");
    $anno = date('Y');
    $sSql="INSERT INTO catalogos.archivador (  arch_anno,
                                               depe_id,        
                                               arch_descripcion,
                                               arch_actualfecha,
                                               usua_id,
                                               arch_actualusua,
                                               arch_tabltipoarchivador                                                       
                                               )
                                            VALUES ( $anno,
                                                     $depe_id,'".
                                                     strtoupper($descripcion)."',
                                                     NOW(),
                                                    '$usua_id',
                                                    '$usua_id',
                                                    200    
                                                    )
                                             RETURNING arch_id";

    
    // Ejecuto el string
    $arch_id=$conn->execute($sSql);
    $error=$conn->error();		
    if($error){ $objResponse->Alert($error); return $objResponse;}
    else{
        $nvoVal=$oldVal!=''?$oldVal.','.$arch_id:$arch_id;
        $objResponse->addScript("xajax_getArchivadores(1,'$depe_id','$user_id','$nvoVal')");
    }     
    return $objResponse;
}

function guardar_archivar($formdata)
{
    global $conn;
    $objResponse = new xajaxResponse();
    $arch_id=$formdata['nx_archivador'];
    $dede_motivoarchiva=$formdata['Sx_motivo'];

    if($formdata['reg_seleccionados']==''){
        $objResponse->addAlert('Proceso cancelado, No existen registros seleccionados para procesar... ');
        return $objResponse;
    }

    if($arch_id==0){
        $objResponse->addAlert('Proceso cancelado, Seleccione archivador... ');
        $objResponse->addScript("document.frmArchivar.nx_archivador.focus()");
        return $objResponse;
    }

    if($dede_motivoarchiva==''){
        $objResponse->addAlert('Proceso cancelado, Ingrese Motivo... ');
        $objResponse->addScript("document.frmArchivar.Sx_motivo.focus()");
        return $objResponse;
    }

    /*genero un array con los registros seleccionados*/
    $regSeleccionados=$formdata['reg_seleccionados'];
    $nvoArraySeleccionados=explode(",",$regSeleccionados);
    $reg_seleccionados='';
    for ($i = 0; $i < count($nvoArraySeleccionados); $i++) {
            /* Campos */
            //obtengo el ID del padre y el Id del Hijo, esto se construye en buscar de procesoDespachoClass
            $arrayPadreHijo=explode('_',$nvoArraySeleccionados[$i]);
            //$reg_seleccionados.=iif($reg_seleccionados,'===','','',',').$arrayPadreHijo[1];

            /* Campos */
            $sql ="UPDATE despachos_derivaciones SET dede_estado=6,
                    usua_idarchiva=".getSession("sis_userid").",dede_fechaarchiva='".date('d/m/Y').' '.date('H:i:s')."',
                    arch_id=$arch_id,
                    dede_motivoarchiva='".addslashes($dede_motivoarchiva)."'
                    WHERE dede_acum_derivaciones=0 
                          AND dede_id=".$arrayPadreHijo[1]." 
                          AND dede_estado IN (3,7) 
                    RETURNING dede_id"; 
                    //dede_estado=3 ->quiere decir que esta aun en proceso.
            $sql= strtoupper($sql);
            //$objResponse->addAlert($sql);

            $dede_id=$conn->execute($sql);
            $error=$conn->error();
            if($error){
                $objResponse->addAlert($error);
            }elseif(!$dede_id){
                $objResponse->addAlert("Imposible Archivar Registro ".$nvoArraySeleccionados[$i].", probablemente contiene derivaciones o el registro ya no pertenece a Usted!");
            }

    }



    /********* FIN PROCESO DE GRABACION EN EL HIJO *********/
    $objResponse->addScript("limpiarDatosArchiva()");


    $paramFunction= new manUrlv1();
    $paramFunction->removeAllPar();
    $paramFunction->addParComplete('colSearch','');
    $paramFunction->addParComplete('colOrden','1');
    $paramFunction->addParComplete('busEmpty',1);

    $objResponse->addScript("xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')");
    $objResponse->addScript("$('#myModalOpc').modal('hide')");
    return $objResponse;
}

    

    
function getAdjuntar($ar_registros_seleccionados,$nameDiv){
    global $bd_usua_id;
    $objResponse = new xajaxResponse();

    if(!is_array($ar_registros_seleccionados)){
        $objResponse->addAlert("Sin registros seleccionados para proceder...");
    }elseif(count($array)==1){
            $objResponse->addAlert("Para adjuntar seleccione por lo menos dos(2) registros...");
        }else{
            for ($i = 0; $i < count($ar_registros_seleccionados); $i++) {
                $arrayPadreHijo=explode('_',$ar_registros_seleccionados[$i]);    
                $regSeleccionadosId.=$arrayPadreHijo[1].',';
            }        
            $regSeleccionadosId=trim($regSeleccionadosId,',');

            $form = new Form("frmAdjuntar", "", "POST", "controle", "100%",false);
            $form->setLabelWidth("20%");
            $form->setDataWidth("80%");
            $form->addBreak("ADJUNTAR EN:", true);
            

            $adjuntar=new despachoProceso_SQLlista();
            $adjuntar->whereIDVarios($regSeleccionadosId);
            $sqlAdjuntar=$adjuntar->getSQL_resumen2($regSeleccionadosId);
            $form->addField("Registros Seleccionados: ",listboxField("Adjuntar Registros Seleccionados en",$sqlAdjuntar,"nx_adjuntar","","-- Seleccione Registro --"));
            /* botones */
            $button = new Button;
            $list_registros_seleccionados=implode(',',$ar_registros_seleccionados);
            $button->addItem("Adjuntar","javascript:if(document.frmAdjuntar.nx_adjuntar.value==''){alert('Seleccione un registro')}else{ if(confirm('Seguro de adjuntarlos en este registro?')) {xajax_guardar_adjuntar(document.frmAdjuntar.nx_adjuntar.value,'$list_registros_seleccionados')}}","content",2,$bd_usua_id,'botao','button');
            $button->align('L');
            $form->addField("",$button->writeHTML());
            $contenido_respuesta=$form->writeHTML();
            
            $objResponse->addAssign($nameDiv,'innerHTML', $contenido_respuesta);

            $objResponse->addScript("$('#myModalOpc').modal('show');");            
            
        }
        
    return $objResponse;    
}

function guardar_adjuntar($id,$list_registros_seleccionados)
{
    global $conn;
    $objResponse = new xajaxResponse();
    
    $ar_registros_seleccionados=explode(",",$list_registros_seleccionados);
    for ($i = 0; $i < count($ar_registros_seleccionados); $i++) {
        $arrayPadreHijo=explode('_',$ar_registros_seleccionados[$i]);    
        if($arrayPadreHijo[0]!=$id){
            $regSeleccionadosExp.=$arrayPadreHijo[0].',';
            $regSeleccionadosId.=$arrayPadreHijo[1].',';
        }
    }
    
    $regSeleccionadosExp=trim($regSeleccionadosExp,',');
    $regSeleccionadosId=trim($regSeleccionadosId,',');
    
    $ok=1; 
    //BUSCO DERIVACIONES EN TOOS LOS REGISTROS SELECCIONADOS
    $arSeleccionadosId=explode(',',$regSeleccionadosId);
    for ($x = 0; $x < count($arSeleccionadosId); $x++) {
        $pos = strpos($arSeleccionadosId[$x], ".");
        if ($pos === false) {            
        }else{
            $objResponse->addAlert("Proceso Cancelado, imposible adjuntar registro $arSeleccionadosId[$x], en estado 'Registrado' ");
            $ok=0; 
            break;
        }
    }
    
    if($ok){
        
        $sql= "UPDATE gestdoc.despachos 
                    SET desp_adjuntados_exp=COALESCE(desp_adjuntados_exp||',','')||'$regSeleccionadosExp',
                        desp_adjuntados_id=COALESCE(desp_adjuntados_id||',','')||'$regSeleccionadosId',
                        desp_adjuntados=1
                    WHERE desp_id=$id;

               UPDATE gestdoc.despachos_derivaciones
                    SET desp_adjuntadoid=$id 
                    WHERE desp_id!=$id
                        AND dede_id IN ($regSeleccionadosId) " ;
        
        $conn->execute($sql);
        $error=$conn->error();
        if($error){
            $objResponse->addAlert($error);
            return $objResponse;
        }    
        
    }



    $paramFunction= new manUrlv1();
    $paramFunction->removeAllPar();
    $paramFunction->addParComplete('colSearch','');
    $paramFunction->addParComplete('colOrden','1');
    $paramFunction->addParComplete('busEmpty',1);

    $objResponse->addScript("xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')");
    $objResponse->addScript("$('#myModalOpc').modal('hide')");
    return $objResponse;
}

function extraeAdjuntados($form)
{
    global $conn;
    $objResponse = new xajaxResponse();

    $array=$form['sel'];
    if(!is_array($array)){
        $objResponse->addAlert("Sin registros seleccionados para proceder...");
        return $objResponse;
    }else{
        //RECORRO LOS REGISTRO SELECCIONADOS
        for ($i = 0; $i < count($array); $i++) {
                $arSeleccionado=explode('_',$array[$i]);    
                $desp_idSeleccionado=$arSeleccionado[0];
                //OBTENGO LOS REGISTROS ADJUNTADOS DEL PRIMER REGISTRO SELECCIONADO
                
                $regSeleccionadosExp=new despacho_SQLlista();
                $regSeleccionadosExp->whereID(trim($desp_idSeleccionado));
                $rsSeleccionado = new query($conn, $regSeleccionadosExp->getSQL());
                
                $error=$conn->error();
                if($error){
                    $objResponse->addAlert($error);
                    return $objResponse;
                }else{
                            
                    $rsSeleccionado->getrow();
                    $desp_adjuntados_exp=$rsSeleccionado->field('desp_adjuntados_exp');
                    $desp_adjuntados_id=$rsSeleccionado->field('desp_adjuntados_id');
                    $arAdjuntadosExp=explode(',',$desp_adjuntados_exp);    
                    $arAdjuntadosId=explode(',',$desp_adjuntados_id);    
                    $setTable='gestdoc.despachos_derivaciones';
                    $setKey='dede_id';
                    $typeKey='Number';
                    $depe_iddestino=$form['nbusc_depe_id']; //getSession("sis_depeid");
                    $usua_iddestino=getSession("sis_userid");
                    $usua_idcrea=getSession("sis_userid"); /* Id del usuario que graba el registro */
                    $hh_recibe=date('d/m/Y').' '.date('H:i:s');
                    //RECORRO LOS REGISTROS ADJUNTADOS
                    for ($x = 0; $x < count($arAdjuntadosExp); $x++) {
                        $desp_id=$arAdjuntadosExp[$x];
                        $dede_idrelacionado=$arAdjuntadosId[$x];
                                                
 
                        //primero regreso los registros adjuntados de la dependencia donde se encuentra
                        $sql="UPDATE gestdoc.despachos_derivaciones
                                SET desp_adjuntadoid=NULL 
                                WHERE desp_id=$desp_id
                                    AND dede_id=$dede_idrelacionado 
                                    AND depe_iddestino=$depe_iddestino  
                                RETURNING desp_id";
                        
                        $return=$conn->execute($sql);
                        //$objResponse->addAlert($error);
                        if(!$return){
                                $sql = new UpdateSQL();
                                $sql->setTable($setTable);
                                $sql->setKey($setKey,0,$typeKey);
                                $sql->setAction("INSERT"); /* Operación */

                                //$objResponse->addAlert($arrayPadreHijo[1]);
                                $sqlRelacionado=new despachoProceso_SQLlista();
                                $sqlRelacionado->whereIDUno($dede_idrelacionado);
                                $rsRelacionado = new query($conn, $sqlRelacionado->getSQL());
                                $rsRelacionado->getrow();
                                $depe_idorigen=$rsRelacionado->field("depe_idorigen");
                                $usua_idorigen=$rsRelacionado->field("usua_idorigen");
                                $dede_proveido=$rsRelacionado->field("dede_proveido");

                                /*DATOS DE LA DERIVACION*/
                                $sql->addField('desp_id',$desp_id, "Number");
                                $sql->addField('dede_idrelacionado', $dede_idrelacionado, "Number");
                                $sql->addField('depe_idorigen', $depe_idorigen, "Number");
                                $sql->addField('usua_idorigen', $usua_idorigen, "Number");

                                $sql->addField('depe_iddestino', $depe_iddestino, "Number");
                                //$sql->addField('usua_iddestino', $usua_iddestino, "Number");
                                $sql->addField('dede_proveido', $dede_proveido, "String");
                                $sql->addField('dede_donde_se_creo', 1, "Number");
                                $sql->addField('usua_idcrea', $usua_idcrea, "Number");

                                /*DATOS DE LA RECEPCION*/                
                                $sql->addField('dede_estado', 3, "Number");
                                $sql->addField('usua_idrecibe', $usua_idcrea, "Number");
                                //echo "$hh_recibe";
                                $sql->addField('dede_fecharecibe', $hh_recibe, "String");

                                $sql=$sql->getSQL();
                                $sql= strtoupper($sql);

                                $conn->execute($sql);
                                $error=$conn->error();
                                if($error){
                                //    $objResponse->addAlert($error);
                                //    return $objResponse;
                                }
                        }
                    }
                    
                    $sql= "UPDATE gestdoc.despachos 
                                SET desp_adjuntados_exp=NULL,
                                    desp_adjuntados_id=NULL,
                                    desp_adjuntados=0 
                                WHERE desp_id=$desp_idSeleccionado";

                    $conn->execute($sql);
                    $error=$conn->error();
                    if($error){
                        $objResponse->addAlert($error);
                        return $objResponse;
                    }                   
                }
            }      
        
        
        //if($desp_adjuntados_exp){

        //}
        
        $paramFunction= new manUrlv1();
        $paramFunction->removeAllPar();
        $paramFunction->addParComplete('colSearch','');
        $paramFunction->addParComplete('colOrden','1');
        $paramFunction->addParComplete('busEmpty',1);

        $objResponse->addScript("xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')");
    }
    return $objResponse;
}

function autorizar($formdata)
{
    global $conn;
    $objResponse = new xajaxResponse();
    $objResponse->setCharEncoding('utf-8');
    
    $arLista_autoriza=$formdata['sel'];
    if(!is_array($arLista_autoriza)){
        $objResponse->addAlert('Proceso cancelado, No existen registros seleccionados para procesar... ');
        return $objResponse;
    }
    
    $ok=1;
    for ($i = 0; $i < count($arLista_autoriza); $i++) {
        $arrayPadreHijo=explode('_',$arLista_autoriza[$i]);
        $tipo=getDbValue("SELECT b.tiex_abreviado FROM gestdoc.despachos a 
                                    LEFT JOIN catalogos.tipo_expediente b ON a.tiex_id=b.tiex_id 
                                    WHERE a.desp_id=".$arrayPadreHijo[0]);
        
        if(!inlist(substr($tipo,0,2),'SB,SS,PR')){
            $ok=0;        
            break;
        }
    }
    if($ok==0){
        $objResponse->addAlert('Proceso cancelado, NO es un Documento para Autorizar... ');
        return $objResponse;
    }
        
    for ($i = 0; $i < count($arLista_autoriza); $i++) {
            $arrayPadreHijo=explode('_',$arLista_autoriza[$i]);
            
            $tipo=getDbValue("SELECT b.tiex_abreviado FROM gestdoc.despachos a 
                                    LEFT JOIN catalogos.tipo_expediente b ON a.tiex_id=b.tiex_id 
                                    WHERE a.desp_id=".$arrayPadreHijo[0]);            
            
            if(inlist(substr($tipo,0,2),'SB,SS')){
                $sql ="UPDATE solicitudes.pedidos_bbss  
                        SET pedi_estado2=2,
                            pedi_usua_idaprueba2=".getSession("sis_userid").",
                            pedi_fregistroaprueba2='".date('d/m/Y').' '.date('H:i:s')."', 
                            dede_id=".$arrayPadreHijo[1]."    
                        WHERE pedi_estado2=1
                            AND desp_id=".$arrayPadreHijo[0]." 
                            AND pedi_acum_atendido=0 ";        
            }else{
                $sql ="/*SE ACTUALIZA EL ESTADO DEL CUADRO DE NECESIDADES*/
                        UPDATE gestcne.cnecesidades_dependencia 
                        SET cnde_estado2=2,
                            cnde_usua_idaprueba2=".getSession("sis_userid").",
                            cnde_fregistroaprueba2='".date('d/m/Y').' '.date('H:i:s')."', 
                            dede_id=".$arrayPadreHijo[1]."    
                        WHERE cnde_estado2=1
                            AND desp_id=".$arrayPadreHijo[0]." 
                            AND cnde_acum_atendido=0;
                            
                      /*SE ARCHIVA EL DOCUMENTO DE TRAMITE */      
                      UPDATE gestdoc.despachos_derivaciones SET dede_estado=6,
                                  usua_idarchiva=".getSession("sis_userid").",
                                  dede_fechaarchiva=NOW(),
                                  arch_id=NULL,
                          dede_motivoarchiva='REGISTRO CONSOLIDADO EN CUADRO DE NECESIDADES '||(SELECT cnde_anno FROM gestcne.cnecesidades_dependencia WHERE desp_id=".$arrayPadreHijo[0].")::TEXT
                          WHERE dede_acum_derivaciones=0 
                                AND dede_id=(SELECT dede_id_ult 
                                                  FROM gestdoc.despachos
                                                  WHERE desp_id=".$arrayPadreHijo[0].")
                                AND dede_estado IN (3,7); ";        
            }
             $conn->execute($sql);
             $error=$conn->error();
    }
    
    if($error){
        $objResponse->addAlert($error);
        return $objResponse;
    }

    $Sbusc_cadena=$formdata['Sbusc_cadena'];
    $paramFunction= new manUrlv1();
    $paramFunction->removeAllPar();
    $paramFunction->addParComplete('colSearch','');
    $paramFunction->addParComplete('colOrden','1');
    $paramFunction->addParComplete('busEmpty',1);
    
    if($Sbusc_cadena)
        $paramFunction->addParComplete('Sbusc_cadena',$Sbusc_cadena);
    
    $objResponse->addScript("xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')");
    return $objResponse;
}

function deshace_autorizacion($formdata)
{
    global $conn;
    $objResponse = new xajaxResponse();
    $objResponse->setCharEncoding('utf-8');
    
    $arLista_autoriza=$formdata['sel'];
    if(!is_array($arLista_autoriza)){
        $objResponse->addAlert('Proceso cancelado, No existen registros seleccionados para procesar... ');
        return $objResponse;
    }
    
    
    for ($i = 0; $i < count($arLista_autoriza); $i++) {
        $arrayPadreHijo=explode('_',$arLista_autoriza[$i]);
            $sql ="UPDATE solicitudes.pedidos_bbss  
                        SET pedi_estado2=1,
                            pedi_usua_idaprueba2=NULL,
                            pedi_fregistroaprueba2=NULL, 
                            dede_id=NULL 
                        WHERE pedi_estado2=2
                            AND desp_id=".$arrayPadreHijo[0]." 
                            AND pedi_usua_idaprueba2=".getSession("sis_userid")."
                            AND pedi_acum_atendido=0 ";
            //$objResponse->addAlert($sql);
             $conn->execute($sql);
             $error=$conn->error();
    }
    
    if($error){
        $objResponse->addAlert($error);
        return $objResponse;
    }

    $Sbusc_cadena=$formdata['Sbusc_cadena'];
    $paramFunction= new manUrlv1();
    $paramFunction->removeAllPar();
    $paramFunction->addParComplete('colSearch','');
    $paramFunction->addParComplete('colOrden','1');
    $paramFunction->addParComplete('busEmpty',1);
    
    if($Sbusc_cadena)
        $paramFunction->addParComplete('Sbusc_cadena',$Sbusc_cadena);
    
    $objResponse->addScript("xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')");
    return $objResponse;
}

function responder($registro_seleccionado)
{
    global $conn;
    $objResponse = new xajaxResponse();
    
    $ar_registro_seleccionado=explode('_',$registro_seleccionado);
            
    if(!is_array($ar_registro_seleccionado)){
        $objResponse->addAlert('Sin registros seleccionados paa pocesar...!');
    }else{
        $despacho=new despacho_SQLlista();
        $despacho->whereID($ar_registro_seleccionado[0]);
        $despacho->setDatos();
        $objResponse->addScript("responder_ir(".SIS_GESTDOC_TIPO.")");
            
    }
        
    return $objResponse;
}

function upload($desp_id, $dede_id, $NameDiv)
{
    $objResponse = new xajaxResponse();

    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");

    $oForm->addField("Detalles: ", textField("Detalles","Sr_descripcion","",60,120));
    $oForm->addField("Archivo:",fileField2("Archivo","exap_adjunto" ,"",60,"onchange=validaextension(this,'PDF,ZIP,DOC,XLS,DOCX,XLSX,PPT,PPTX')","uno"));
            
    $button = new Button;
    //$button->addItem(" Cerrar ","","",0,0,"","button-modal");
    $button->addItem(" Agregar Archivo ","javascript:if(document.frm.Sr_descripcion.value==''){
                                                       alert('Campo Descripcion es obligatorio');
                                                       document.frm.Sr_descripcion.focus();
                                                       return false;
                                                   }else{
                                                        if(document.frm.exap_adjunto.value==''){
                                                            alert('Campo Archivo es obligatorio');
                                                            document.frm.exap_adjunto.focus();
                                                            return false;
                                                        }else{
                                                            $('#id_subir').hide();
                                                            uploadFile('$desp_id','$dede_id', document.frm.Sr_descripcion.value);
                                                            return false;
                                                        }
                                                    }","content",0,0,"","button","","id_subir");
    
    $oForm->addHtml("<tr><td colspan=2><div class='modal-footer'>\n"); //pide datos de afectacion presupuestal
    $oForm->addHtml($button->writeHTML());
    $oForm->addHtml("</div></td></tr>\n");
    
    $oForm->addHtml("<tr><td colspan=2>\n"); 
    $oForm->addHtml("<span id='file-chk-error'></span><div class='progress'><div id='file-progress-bar' class='progress-bar'></div>");
    $oForm->addHtml("</td></tr>\n");
    
    $contenido_respuesta=$oForm->writeHTML();
    $contenido_respuesta.="<div id=\"historial-uploads\">";
    $contenido_respuesta.=listaHistorialUploads(2,$desp_id, $dede_id);
    $contenido_respuesta.="</div>";
    
    $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);
    return $objResponse;
}

function listaHistorialUploads($op,$desp_id, $dede_id){
    global $conn;	
    
    $objResponse = new xajaxResponse();
            
    $usua_id=getSession("sis_userid");
    
    $listUploads=new despachoAdjuntados_SQLlista();
    $listUploads->whereDedeID($dede_id);
    $listUploads->whereUsuaID($usua_id);    
    $listUploads->orderUno();
    $sql=$listUploads->getSQL();
    //echo $sql;
    $rs = new query($conn, $sql);

    /* inicializo tabla */
    $table = new Table("Archivos Agregados","100%",4); // Titulo, Largura, Quantidade de colunas

    /* construccion de cabezera de tabla */
    $table->addColumnHeader("Descripci&oacute;n",false,"30%","c");
    $table->addColumnHeader("Archivo",false,"50%","c");
    $table->addColumnHeader("Usuario",false,"15%","c");
    $table->addColumnHeader("",false,"5%","c");
    $table->addRow();
    
    while ($rs->getrow()) {
            /* adiciona columnas */
            $dead_id = $rs->field("dead_id");
            $periodo = $rs->field("periodo");
            $id = $rs->field("desp_id");
            $enlace= PUBLICUPLOAD."gestdoc/".SIS_EMPRESA_RUC."/$periodo/".$id."/".$rs->field("area_adjunto");

            if(strpos(strtoupper($enlace),'.PDF')>0){
                $link=addLink($rs->field("dead_descripcion"),"javascript:verFile('$enlace')","Click aqu&iacute; para Ver Documento","controle");
            }else{
                $link=addLink($rs->field("dead_descripcion"),"$enlace","Click aqu&iacute; para Descargar Archivo","controle");
            }

            $table->addData($link);
            $table->addData($rs->field("area_adjunto"));                
            $table->addData($rs->field("usua_login"));            
            $table->addData("<a class=\"link\" href=\"#\" onClick=\"javascript:if(confirm('Eliminar este registro?')) {xajax_eliminarFile('$dead_id', '$desp_id', '$dede_id')}\"><img src=\"../../img/delete.gif\" border=0 align=absmiddle hspace=1 alt=\"Eliminar\"></a>");
            $table->addRow(); // adiciona linea
            
    }
    $contenido_respuesta=$table->writeHTML();
  

    // Se devuelve el objeto, que este dara todas las instruccione JS para realizar la tarea
    if($op==1){
        $objResponse->addAssign('historial-uploads','innerHTML', $contenido_respuesta);
        return $objResponse;
    }else{
        return $contenido_respuesta;
    }	    
}

function eliminarFile($dead_id,$desp_id,$dede_id)
{
    global $conn;	
        
    $objResponse = new xajaxResponse();
    
    $despacho=new despachoAdjuntados_SQLlista();
    $despacho->whereID($dead_id);
    $despacho->setDatos();
    $periodo=$despacho->field('periodo');
    $name_file=$despacho->field('area_adjunto');
    $usua_id=getSession("sis_userid");
    
    $enlace=PUBLICUPLOAD."gestdoc/$periodo/".$desp_id."/".$name_file;

    $sql="DELETE FROM gestdoc.despachos_adjuntados
            WHERE dead_id=$dead_id 
                    AND usua_id=$usua_id ";

    $conn->execute($sql);
    $error=$conn->error();
    if($error){
       $objResponse->addAlert($error);
       return $objResponse;
    }else{
         if(file_exists($enlace)){
            unlink($enlace);
         }            
        $objResponse->addScript("xajax_listaHistorialUploads(1, '$desp_id', '$dede_id');");        
                    
    }                
    return $objResponse;
}


function getValidarRequisitos($op,$registro_seleccionado,$nameDiv){
    
    global $conn;
    $objResponse = new xajaxResponse();        

    $arrayPadreHijo=explode('_',$registro_seleccionado);
    $desp_id=$arrayPadreHijo[0];    

    /* Formulario */
    $form = new Form("frmDeriva", "", "POST", "controle", "100%",false);
    $form->setLabelWidth("20%");
    $form->setDataWidth("80%");    
    
    
    $despacho=new despachoProceso_SQLlista();
    $despacho->whereID("$desp_id");
    $despacho->whereProceso();
    $despacho->whereUsuaRecibeID(getSession("sis_userid"));
    $despacho->setDatos();

    if($despacho->existeDatos()){//si el documento esta en proceso y lo tiene el usuario actual
        $periodo = $despacho->field('desp_anno');
        $procedimiento = $despacho->field('procedimiento');
        $dede_id = $despacho->field('dede_id');
        
        $notificacion_estado = $despacho->field('desp_notificacion_estado');
        $notificacion_estado = $notificacion_estado>0?$notificacion_estado:0;
        $readonly=$notificacion_estado>0?'disabled':'';                
        
        $form->addHtml("<tr><td class='FormHeaderTD' colspan=2><font class='FormHeaderFONT'>$procedimiento<font></td></tr>"); //pide datos de afectacion presupuestal
        $procedimientoRequisitos=new despachoRequisitos_SQLlista();
        $procedimientoRequisitos->wherePadreID("$desp_id");
        $procedimientoRequisitos->orderUno();
        $sql=$procedimientoRequisitos->getSQL();
        //$form->addHtml($sql);
        $rs = new query($conn, $sql);
        if($rs->numrows()>0){//SI EXISTEN REQUISITOS
            //CARGO LOS ARCHIVOS ADJUNTOS
            $sql=new despachoAdjuntados_SQLlista();
            $sql->wherePadreID("$desp_id");
            $sql = $sql->getSQL();

            $rsFiles = new query($conn, $sql);
            if($rsFiles->numrows()>0){
                $tableAdjuntos = new Table("","100%",3); // Título, Largura, Quantidade de colunas
                while ($rsFiles->getrow()) {
                        $file=$rsFiles->field("area_adjunto");
                        $descripcion=$rsFiles->field("dead_descripcion");
                        $enlace=PUBLICUPLOAD."gestdoc/".SIS_EMPRESA_RUC."/$periodo/$desp_id/$file";

                        if(strpos(strtoupper($file),'.PDF')>0){
                            $link=addLink($file,"javascript:verFile('$enlace')","Click aqu&iacute; para Ver Documento","controle");
                        }else{
                            $link=addLink($file,"$enlace","Click aqu&iacute; para Descargar Archivo","controle");
                        }

                        $tableAdjuntos->addData($link);
                        if($file!=$descripcion){
                            $tableAdjuntos->addData($descripcion);
                        }else{
                            $tableAdjuntos->addData("");
                        }
                        $tableAdjuntos->addRow();                
                    }
                $form->addHtml("<tr><td colspan=2>".$tableAdjuntos->writeHTML()."</td></tr>");
            }
                                 
            $form->addHtml("<tr><td class='FormHeaderTD' colspan=2><font class='FormHeaderFONT'>REQUISITOS ".iif($notificacion_estado,'==',0,'','VALIDADOS')."<font></td></tr>"); //pide datos de afectacion presupuestal
            
            if($notificacion_estado==0){//SI AUN NO SE ENVIA CORREO
                $button = new Button;
                $button->addItem("MARCAR REQUISITOS CONFORMES","javascript:if(confirm('Seguro de validar Requisitos Conformes?')){xajax_guardar_validarRequisitosTodos('$desp_id')}","content",2);
                $form->addHtml("<tr><td colspan=2>".$button->writeHTML()."</td></tr>");
            }
            
            $form->addHtml("<tr><td colspan=2>"); //pide datos de afectacion presupuestal
            $form->addHtml("<ul class='list-group' >");
            $ndiv=0;
            while ($rs->getrow()) {
                $id=$rs->field('dere_id');

                $checked=$rs->field('dere_valida')==1?'checked':'';
                $html="<li class='list-group-item'>
                                        &nbsp;&nbsp;".$rs->field('dere_descripcion')."
                                        <div class='TriSea-technologies-Switch pull-right'>
                                            <input id='TriSeaDefault_$id' name='$id' type='checkbox' $checked onChange=\"javascript:xajax_guardar_validarRequisitos('$id','$ndiv',this.checked,'$notificacion_estado')\" $readonly>
                                            <label for='TriSeaDefault_$id' class='label-default' ></label>
                                        </div>
                                        <div id=\"addDatos_$ndiv\">";
                                if($rs->field('dere_valida')==0){//SI NO CUMPLE
                                    $html.=getComentario(2,$id,$ndiv,$notificacion_estado);
                                }
                                         
                $html.="                 </div>
                        </li>                    
                        ";
                
                $html.="</li>";
                
                $form->addHtml($html);
                $ndiv++;
            }
            $form->addHtml("</ul>");
            $form->addHtml("</td></tr>\n");                                  
            

            $button = new Button;
            $button->addItem("NOTIFICAR OBSERVACIONES","javascript:beforeEnviaEmail(3,'$desp_id','')","content",2);
            $form->addHtml("<tr><td colspan=2>".$button->writeHTML()."</td></tr>");            

            if($notificacion_estado==1){//SI SE ENVIO CORREO
                $button = new Button;
                $registro_seleccionadox=$desp_id.'_'.$dede_id;
                $button->addItem("ARCHIVAR ","javascript:archivar('$registro_seleccionadox','SE NOTIFICO AL CORREO ELECTRONICO LAS OBSERVACIONES HALLADAS EN LA SOLICITUD')","content",2);
                $form->addHtml("<tr><td colspan=2>".$button->writeHTML()."</td></tr>");
            }
        }
                
    }else{
        $objResponse->addAlert("No se encontró registro en proceso...");   
    }
        

    $contenido_respuesta=$form->writeHTML();
    
    $objResponse->addScript("$('#title-myModalOpc').html('<b>VALIDAR REQUISITOS DE: $desp_id</b>')");
    $objResponse->addAssign($nameDiv,'innerHTML', $contenido_respuesta);
    
    if($op==1){
        $objResponse->addScript("$('#myModalOpc').modal('show');");
    }
    return $objResponse;    
}

function getComentario($op,$id,$div,$notificacion_estado)
{
    global $conn;
    $objResponse = new xajaxResponse();    
    
    $readonly=$notificacion_estado>0?'readonly':'';
    $procedimientoRequisitos=new despachoRequisitos_SQLlista();
    $procedimientoRequisitos->whereID($id);
    $procedimientoRequisitos->setDatos();
    $dere_obsevacion=$procedimientoRequisitos->field('dere_observacion');
    $contenido_respuesta="<input type='text' name='obs_$div' id='obs_$div' value=\"$dere_obsevacion\" size='90%' onChange=javascript:xajax_guardar_requisitoTexto(this.value,'$id') $readonly> </input>";
    
    if($op==1){
        $objResponse->addAssign('addDatos_'.$div,'innerHTML', $contenido_respuesta);
        return $objResponse;
    }else{
        return $contenido_respuesta;
    }
    
}

function guardar_validarRequisitos($id,$ndiv,$checked,$notificacion_estado)
{
    global $conn;
    $objResponse = new xajaxResponse();
    
    $validar=$checked==='true'?1:0;
    $usua_id=getSession("sis_userid");
    
    $sql= "UPDATE gestdoc.despachos_requisitos 
                SET dere_valida=$validar,
                    dere_fregistro_valida=NOW(),
                    dere_observacion=CASE WHEN $validar=0 AND COALESCE(dere_observacion,'')='' THEN 'NO CUMPLE' ELSE dere_observacion END,
                    dere_usua_id_valida=$usua_id
                WHERE dere_id=$id;";
            
    $conn->execute($sql);
    $error=$conn->error();
    if($error){
        $objResponse->addAlert($error);
        return $objResponse;
    }    
    
    $objResponse->addScript("$('#addDatos_$ndiv').empty()");
    if($validar==0){
        $objResponse->addScript("xajax_getComentario(1,$id,$ndiv,$notificacion_estado)");
    }
    return $objResponse;
}

function guardar_requisitoTexto($texto,$id)
{
    global $conn;
    $objResponse = new xajaxResponse();

    $id= intval($id);
    $miValidacion=new miValidacionString();
    
    $texto = strtoupper($miValidacion->replace_invalid_caracters($texto));
    $texto = $texto==''?'NO CUMPLE':$texto;
    
    $usua_id=getSession("sis_userid");
    
    $sql= "UPDATE gestdoc.despachos_requisitos 
                SET dere_observacion=$$$texto$$
                WHERE dere_id=$id
                    AND dere_usua_id_valida=$usua_id
               ";

    $conn->execute($sql);
    $error=$conn->error();
    if($error){
        $objResponse->addAlert($error);
        return $objResponse;
    }    
    return $objResponse;
}

function guardar_validarRequisitosTodos($desp_id)
{
    global $conn;
    $objResponse = new xajaxResponse();

    $usua_id=getSession("sis_userid");
    
    $sql= "UPDATE gestdoc.despachos_requisitos 
                SET dere_valida=1,
                    dere_fregistro_valida=NOW(),
                    dere_usua_id_valida=$usua_id
                WHERE desp_id=$desp_id 
                    AND  dere_valida=0;";
            

    $conn->execute($sql);
    $error=$conn->error();
    if($error){
        $objResponse->addAlert($error);
        return $objResponse;
    }    
    $objResponse->addScript("$('#msg-myModalOpc').empty();xajax_getValidarRequisitos(2,'$desp_id','msg-myModalOpc')");
    return $objResponse;
}
$xajax->processRequests();
// fin para Ajax
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="javascript" src="<?php echo PATH_INC?>js/checkall.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>
        
        <link rel="stylesheet" href="<?php echo PATH_INC?>thickbox/thickbox.css" type="text/css" media="screen" />
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>           
        
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>
        
        <link rel="stylesheet" href="<?php echo PATH_INC?>textboxlist/test.css" type="text/css" media="screen" title="Test Stylesheet" charset="utf-8" />
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>
        
        <style>
                #lectorPDF{
                  width: 95% !important;
                }
                
                .TriSea-technologies-Switch > input[type="checkbox"] {
                    display: none;   
                }

                .TriSea-technologies-Switch > label {
                    cursor: pointer;
                    height: 0px;
                    position: relative; 
                    width: 40px;  
                }

                .TriSea-technologies-Switch > label::before {
                    background: rgb(0, 0, 0);
                    box-shadow: inset 0px 0px 10px rgba(0, 0, 0, 0.5);
                    border-radius: 8px;
                    content: '';
                    height: 16px;
                    margin-top: 0px;
                    position:absolute;
                    opacity: 0.3;
                    transition: all 0.4s ease-in-out;
                    width: 40px;
                }
                .TriSea-technologies-Switch > label::after {
                    background: rgb(255, 255, 255);
                    border-radius: 16px;
                    box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.3);
                    content: '';
                    height: 24px;
                    left: -4px;
                    margin-top: 0px;
                    position: absolute;
                    top: -4px;
                    transition: all 0.3s ease-in-out;
                    width: 24px;
                }
                .TriSea-technologies-Switch > input[type="checkbox"]:checked + label::before {
                    background: inherit;
                    opacity: 0.5;
                }
                .TriSea-technologies-Switch > input[type="checkbox"]:checked + label::after {
                    background: inherit;
                    left: 20px;
                }               
                
	</style>
            
        
	<script language='JavaScript'>

                function limpiarDatos(){
                    xajax_limpiarCarrito();
                    xajax_verCarrito();
                    //document.frmDeriva.Sx_desp_proveido.value='';
                }

                function limpiarDatosArchiva(){
                    document.frmArchivar.nx_archivador.value='';
                    document.frmArchivar.Sx_motivo.value='';
                }


                function AbreVentana(sURL){
                    var w=720, h=650;
                    venrepo=window.open(sURL,'rptSalida', "status=yes,resizable=yes,toolbar=no,scrollbars=yes,top=50,left=150,width=" + w + ",height=" + h, 1 );
                    venrepo.focus();
                }

                function abreConsulta(id) {
                    AbreVentana('../../../portal/gestdoc/consultarTramiteProceso.php?nr_numTramite=' + id+'&vista=NoConsulta');
                }

		function inicializa() {
                    document.frm.Sbusc_cadena.focus();
		}

		function excluir() {
                    if (confirm('Seguro de eliminar Derivaciones de registros seleccionados?')) {
                        parent.content.document.frm.target = "controle";
                        parent.content.document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(2)."&".$param->buildPars(false)?>";
                        parent.content.document.frm.submit();
                    }
                }

                
                function beforeResponder(){
                    regSel=$("#en_proceso input[type=checkbox]").is(":checked");
                    if(regSel){
                        var checked = $("input[name='sel[]']:checked").val();
                        xajax_responder(checked);
                    } else {
                        alert('Seleccione un registro');
                    }
                }
                
                function responderSinFirma() {
                    regSel=$("#en_proceso input[type=checkbox]").is(":checked");
                    if(regSel){
                        parent.content.document.frm.target = "content";                    
                        parent.content.document.frm.action = "registroDespacho_edicion.php?clear=2";
                        parent.content.document.frm.submit();
                    } else {
                        alert('Seleccione un registro');
                    }
                }
                
                function responderConFirma() {
                    regSel=$("#en_proceso input[type=checkbox]").is(":checked");
                    if(regSel){
                        parent.content.document.frm.target = "content";                    
                        parent.content.document.frm.action = "registroDespacho_edicionConFirma.php?clear=2";
                        parent.content.document.frm.submit();
                    } else {
                        alert('Seleccione un registro');
                    }
                }

                function beforeDerivar() {
                    regSel=$("#en_proceso input[type=checkbox]").is(":checked");
                    if(regSel){
                        var checked = []                    
                        $("input[name='sel[]']:checked").each(function ()
                        {
                            checked.push($(this).val());
                        });
                        derivar(checked);
                    } else {
                        alert('Seleccione un registro');
                    }
                }
                
                function derivar(checked) {
                        $("#title-myModalOpc").addClass("glyphicon glyphicon-triangle-right");
                        $("#title-myModalOpc").html("<b>DERIVAR</b>");
                        limpiarDatos();
                        xajax_getDerivar(checked,'msg-myModalOpc');

                }


                function beforeArchivar() {
                    regSel=$("#en_proceso input[type=checkbox]").is(":checked");
                    if(regSel){
                        var checked = []                    
                        $("input[name='sel[]']:checked").each(function ()
                        {
                            checked.push($(this).val());
                        });
                        archivar(checked,'');
                    } else {
                        alert('Seleccione un registro');
                    }
                }

                function archivar(checked,motivo) {
                    $("#title-myModalOpc").addClass("glyphicon glyphicon-folder-close");
                    $("#title-myModalOpc").html("<b>ARCHIVAR</b>");
                    xajax_getArchivar(checked,motivo,'msg-myModalOpc');
                }

                function beforeAdjuntar() {
                    regSel=$("#en_proceso input[type=checkbox]").is(":checked");
                    if(regSel){
                        var checked = []                    
                        $("input[name='sel[]']:checked").each(function ()
                        {
                            checked.push($(this).val());
                        });

                        if( checked.length < 2 ){
                            alert('Seleccione por lo menos dos (2) registros');
                        }else{
                            $("#title-myModalOpc").addClass("glyphicon glyphicon-random");
                            $("#title-myModalOpc").html("&nbsp;<b>ADJUNTAR</b>");
                            xajax_getAdjuntar(checked,'msg-myModalOpc');
                        }
                    } else {
                        alert('seleccione por lo menos dos (2) registros');
                    }
                }
                
                                
                function validarRequisitos(reg_seleccionado) {
                        $("#title-myModalOpc").addClass("glyphicon glyphicon-check");
                        $("#title-myModalOpc").html("<b>VALIDAR REQUISITOS</b>");
                        xajax_getValidarRequisitos(1,reg_seleccionado,'msg-myModalOpc');

                }
                
                function imprimirPedido(id) {
                    file = "../solicitudes/rptSolicitud_BS.php?sel="+id;
                    parent.content.document.frm.target = "controle";
                    parent.content.document.frm.action = file;
                    parent.content.document.frm.submit();
                }

                function beforeUpload(id, dede_id) {
                    $( "#title-myModalScreen" ).addClass( "glyphicon glyphicon-upload" );
                    $( "#title-myModalScreen" ).text( " Upload");
                    xajax_upload(id, dede_id, 'msg-myModalScreen');
                    $('#myModalScreen').modal('show');
                }
        
                function uploadFile(desp_id, dede_id, detalle){ 
                    var inputFile = document.getElementById("exap_adjunto");
                    $("#file-chk-error").html('<center><p><small class="text-success"><b>Espere, procesando...</b></small></p></center>');
                    var data = new FormData();

                    [].forEach.call(inputFile.files, function (file) {
                        data.append('fileToUpload[]', file);
                    });

                    data.append('desp_id', desp_id);
                    data.append('dede_id', dede_id);
                    data.append('detalle', detalle);
                    
                    
                    $.ajax({
                        xhr: function() {
                                    var xhr = new window.XMLHttpRequest();         
                                    xhr.upload.addEventListener("progress", function(element) {
                                        if (element.lengthComputable) {
                                            var percentComplete = ((element.loaded / element.total) * 100);
                                            $("#file-progress-bar").width(percentComplete + '%');
                                            $("#file-progress-bar").html(percentComplete+'%');
                                        }
                                    }, false);
                                    return xhr;
                        },                                                
                        url: "upload.php",        // Url to which the request is send
                        type: "POST",             // Type of request to be send, called as method
                        data: data, 			  // Data sent to server, a set of key/value pairs (i.e. form fields and values)
                        contentType: false,       // The content type used when sending data to the server.
                        cache: false,             // To unable request pages to be cached
                        processData:false,        // To send DOMDocument or non processed data file it is set to false
                        dataType:'json',
                        beforeSend: function(){
                                $("#file-progress-bar").width('0%');
                        },
                        success: function(data)   // A function to be called if request succeeds
                        {
                           if(data.success==true){
                                $('#file-chk-error').html( '<center><p><small class="text-success">'+ data.mensaje +'</small></p></center>' );
                                xajax_listaHistorialUploads(1,desp_id, dede_id);
                                $('#id_subir').show();
                           }else{
                                $('#file-chk-error').html( '<center><p><small class="text-danger">'+ data.mensaje +'</small></p></center>' );
                                $('#id_subir').show();
                            }    
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                          console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                          $('#id_subir').show();
                        }
                    });
                }

                function beforeEnviaEmail(op,desp_id,desp_expediente) {
                    $("#title-myModalImp").addClass("glyphicon glyphicon-envelope");
                    $("#title-myModalImp").html("");
                    xajax_enviar_email(op,desp_id,desp_expediente,'msg-myModalImp');
                    $('#myModalImp').modal('show');
                }
                
                
                function enviarEmailProveedor(op,id,email,mensaje){  
                    $("#email-chk-error").html('<center><p><small class="text-success"><b>Espere, procesando...</b></small></p></center>');
                    var data = new FormData();
                    data.append('op', op);
                    data.append('id', id);
                    data.append('email', email);
                    data.append('mensaje', mensaje);
                    
                    var checked = []                    
                    $("input[name='sel_adjunto[]']:checked").each(function ()
                    {
                        checked.push(parseInt($(this).val()));
                    });

                    data.append('adjunto_principal', checked[0]);
                    data.append('adjuntos', checked);
                    
                    $.ajax({
                        xhr: function() {
                            var xhr = new window.XMLHttpRequest();         
                            xhr.upload.addEventListener("progress", function(element) {
                                if (element.lengthComputable) {
                                    var percentComplete = ((element.loaded / element.total) * 100);
                                    $("#email-progress-bar").width(percentComplete + '%');
                                    $("#email-progress-bar").html(percentComplete+'%');
                                }
                            }, false);
                            return xhr;
                        },                
                        url: "enviarEmailProveedores.php",        // Url to which the request is send
                        type: "POST",             // Type of request to be send, called as method
                        data: data, 			  // Data sent to server, a set of key/value pairs (i.e. form fields and values)
                        contentType: false,       // The content type used when sending data to the server.
                        cache: false,             // To unable request pages to be cached
                        processData:false,        // To send DOMDocument or non processed data file it is set to false
                        dataType:'json',
                        beforeSend: function(){
                            $("#email-progress-bar").width('0%');
                        },                
                        success: function(data)   // A function to be called if request succeeds
                        {   
                           if(data.success==true){
                               xajax_listaHistorialEnvios(1,id);
                                   if(op==3){//PREPARA MENSAJE, VALIDACION DE REQUISTOS
                                        $('#msg-myModalOpc').empty();
                                        xajax_getValidarRequisitos(2,id,'msg-myModalOpc');    
                                   }

                               $('#email-chk-error').html( '<center><p><small class="text-success">'+ data.mensaje +'</small></p></center>' );
                           }else{
                                $('#email-chk-error').html( '<center><p><small class="text-danger">'+ data.mensaje +'</small></p></center>' );
                                $('#btn_envia_email').show();
                           }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                          console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                          $('#btn_envia_email').show();
                        }
                    });
                }
        
                function imprimir(id) {
                    AbreVentana('../gestdoc/rptDocumento.php?id=' + id);
                }
                
                function verFile(file) {
                    AbreVentana(file);
                }
	</script>

    <?php 
    $xajax->printJavascript(PATH_INC.'ajax/');
    verif_framework(); 
    ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("B&uacute;squedas de ".$myClass->getTitle());

/* botones */
$button = new Button;

if($clear==1){
    $button->addItem("Eliminar Mis Derivaciones","javascript:if(confirm('Seguro de eliminar Derivaciones de registros seleccionados?')) {xajax_elimDerivar(xajax.getFormValues('frm'))}","content",2);
    $button->addItem("Extraer Adjuntados","javascript:if(confirm('Seguro de Abrir Adjuntados?')) {xajax_extraeAdjuntados(xajax.getFormValues('frm'))}","content",2);
}
//$button->addItem("Derivar","javascript:derivar()","content",2);


if($clear==1){
    if(inlist(SIS_GESTDOC_TIPO,'1,3')){/*SIN FIRMA/MIXTO*/
        $button->addItem("Responder","javascript:responderSinFirma()","content",2);
    }
    if(inlist(SIS_GESTDOC_TIPO,'2,3')){/*CON FIRMA/MIXTO*/
        $button->addItem("Responder (con Firma)","javascript:responderConFirma()","content",2);        
    }
    
    $button->addItem("Adjuntar","javascript:beforeAdjuntar()","content",2);
    
}

    $button->addItem("Derivar","javascript:beforeDerivar()","content",2);
    $button->addItem("Archivar","javascript:beforeArchivar()","content",2);
    
    $button->addItem("Imprimir HT","javascript:xajax_imprimir(xajax.getFormValues('frm'))","content",2);    
echo $button->writeHTML();

if(getSession("SET_AUTORIZA_SOLICITUD")==1){
    $button = new Button;
    $button->addItem("Autorizar Solicitud o Programaci&oacute;n de ByS","javascript:if(confirm('Seguro de Autorizar registros seleccionados?')) {xajax_autorizar(xajax.getFormValues('frm'))}","content",2);    
    $button->addItem("Deshacer Autorizaci&oacute;n","javascript:if(confirm('Seguro de Deshacer Autorizaci\u00f3n?')) {xajax_deshace_autorizacion(xajax.getFormValues('frm'))}","content",2);        
    echo $button->writeHTML();
}
        

?>
<div align="center">
<!-- Lista -->
<form name="frm" id="frm" method="post">    
<?php
/* formulario de pesquisa */
$form = new AddTableForm();
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("rodou","s");

//array de parametros que se ingresaran a la funcion de busqueda de ajax
$paramFunction= new manUrlv1();
$paramFunction->removeAllPar();
$paramFunction->addParComplete('colSearch','');
$paramFunction->addParComplete('colOrden','1');
$paramFunction->addParComplete('busEmpty',$busEmpty);
$paramFunction->addParComplete('nbusc_depe_id',$depe_id);
$paramFunction->addParComplete('nbusc_user_id',$user_id);
$paramFunction->addParComplete('hx_incluir_registrados',0);
$paramFunction->addParComplete('nbusc_indicador',$semaforo);

/* Instancio la Dependencia */
$dependencia=new dependencia_SQLlista();
if(getSession("sis_userid")>1){
    $dependencia->whereVarios(getSession("sis_persid"));
    //$dependencia->whereID($depe_id);
    $todos="";
}else{
    $todos="--Seleccione Dependencia--";
}

$sqlDependencia=$dependencia->getSQL_cbox();
//FIN OBTENGO
$form->addField("Dependencia: ",listboxField("Dependencia.",$sqlDependencia,"nbusc_depe_id",$depe_id,"$todos","onChange=\"xajax_getUsuarios(1,this.value,document.frm.nbusc_user_id.value,'".encodeArray($paramFunction->getUrl())."','".encodeArray($paramFunction->getUrl())."');xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\"","","class=\"my_select_box\"")); 

$form->addHtml("<tr><td colspan=2><div id='divUsuarios' >\n"); //pide datos de afectacion presupuestal
$form->addHtml(getUsuarios(2,$depe_id,"$user_id",encodeArray($paramFunction->getUrl())));
$form->addHtml("</div></td></tr>\n");

$datosAplicativo=new datosAplicativo_SQLlista();
$datosAplicativo->whereID(1);
$datosAplicativo->setDatos();

if($datosAplicativo->field('apli_max_semaforo1')>0 && $datosAplicativo->field('apli_max_semaforo2')>0){
    $sql = array(1 => "EN PROCESO POR AL MENOS ".$datosAplicativo->field('apli_max_semaforo1')." DIAS ",
                 2 => "EN PROCESO POR MAS DE ".$datosAplicativo->field('apli_max_semaforo1')." Y HASTA ".$datosAplicativo->field('apli_max_semaforo2')." DIAS ",
                 3 => "EN PROCESO POR MAS DE ".$datosAplicativo->field('apli_max_semaforo2')." DIAS ");
    $form->addField("Indicador: ", listboxField("Indicador", $sql, "nbusc_indicador","$semaforo","-- Todos --"));
}

$texp=new clsTipExp_SQLlista();
$texp->orderUno();
$sqltipo=$texp->getSQL_cbox();
$form->addField("Tipo de Documento:",listboxField("Tipo de Documento",$sqltipo,"nbusc_tiex","","-- Todos --","","","class=\"my_select_box\"")); 

$form->addField(checkboxField("Incluir Registrados'","hx_incluir_registrados",1,0),"Incluir Registrados");
$ayer=sumaFechas($hoy,-1);
//$link=addLink("Ver Mis Ultimos Registrados","javascript:xajax_buscar(3,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')");

$form->addField("Exp/N&uacute;m.".NAME_EXPEDIENTE."/Asunto: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">".
                                        "&nbsp;&nbsp;$link");

$form->addHtml("<tr><td colspan=2>");
$form->addHtml("<table width=\"100%\"><tr valign=\"top\">");
$form->addHtml("<td><div id='DivResultado'>\n");
$form->addHtml($myClass->buscar(2,'',encodeArray($paramFunction->getUrl()),1,'DivResultado'));
$form->addHtml("</div></td>");
$form->addHtml("<td><div id='DivDetalles'>");
$form->addHtml("</div></td>");
$form->addHtml("</tr></table>\n");
$form->addHtml("</td></tr>");
$dialog=new Dialog("myModalScreen","screen");
$dialog->setModal("modal-ms");//largo

$lectorPDF=new lectorPDF();
$form->addHtml($lectorPDF->writeHTML());

$form->addHtml($dialog->writeHTML());

echo $form->writeHTML();
?>

<div id="myModalImp" class="modal fade" >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><span id="title-myModalImp" class="" aria-hidden="true">&nbsp;</span></h4>                    
                    </div>
                    <div id="msg-myModalImp" class="modal-body">
                        <p>Loading...</p>
                    </div>

            </div>
        </div>    
</div>

</form>
</div>     


<div id="myModalOpc" class="modal fade" >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><span id="title-myModalOpc" class="" aria-hidden="true">&nbsp;</span></h4>                    
                    </div>
                    <div id="msg-myModalOpc" class="modal-body">
                        <p>Loading...</p>
                    </div>

            </div>
        </div>    
</div>    


</body>
        <script>
            $(".my_select_box").select2({
                placeholder: "Seleccione un elemento de la lista",
                allowClear: true
            });        
    
            function mySelect(){

                var query = {};
                var $element = $('.my_select_box2');

                function markMatch (text, term) {
                  // Find where the match is
                  var match = text.toUpperCase().indexOf(term.toUpperCase());

                  var $result = $('<span></span>');

                  // If there is no match, move on
                  if (match < 0) {
                    return $result.text(text);
                  }

                  // Put in whatever text is before the match
                  $result.text(text.substring(0, match));

                  // Mark the match
                  var $match = $('<span class="select2-rendered__match"></span>');
                  $match.text(text.substring(match, match + term.length));

                  // Append the matching text
                  $result.append($match);

                  // Put in whatever is after the match
                  $result.append(text.substring(match + term.length));

                  return $result;
                }

                $element.select2({
                    placeholder: "Seleccione un elemento de la lista",
                    allowClear: true,
                    width: '90%',
                    ajax: {     
                              url: function () {
                                  return getURL();
                              },
                              dataType: 'json',
                              delay: 250,
                                data: function (params) {
                                      return {
                                        q: params.term // search term
                                      };
                                    },                      
                              processResults: function (data) {
                                return {
                                  results: data
                                };
                              },
                              cache: true
                           },      
                    templateResult: function (item) {
                      // No need to template the searching text
                      if (item.loading) {
                        return item.text;
                      }

                      var term = query.term || '';
                      var $result = markMatch(item.text, term);

                      return $result;
                    },
                    language: {
                      searching: function (params) {
                        // Intercept the query as it is happening
                        query = params;

                        // Change this to be appropriate for your application
                        return 'Searching…';
                      }
                    }
                  });

            }

            
            function getURL() {
                    if($("#Tipo_destino").val()==1){ //dependencias
                        return '../catalogos/jswDependenciasAjax.php';
                    }else if($("#Tipo_destino").val()==2){ //dependencias){
                        return '../catalogos/jswDependenciasEmpleadosAjax.php';
                    }else{
                        return '../gestdoc/jswGruposaAjax.php';
                    }
                }
    
            $(document).on({
                'show.bs.modal': function () {
                    var zIndex = 1040 + (10 * $('.modal:visible').length);
                    $(this).css('z-index', zIndex);
                    setTimeout(function() {
                        $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
                    }, 0);
                },
                'hidden.bs.modal': function() {
                    if ($('.modal:visible').length > 0) {
                        // restore the modal-open class to the body element, so that scrolling works
                        // properly after de-stacking a modal.
                        setTimeout(function() {
                            $(document.body).addClass('modal-open');
                        }, 0);
                    }
                }
            }, '.modal');
                
        </script>
</html>
<?php
unset($_SESSION["ocarrito_deriva"]);
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();