<?php
/*contents.css, para controlar el alto del parrafo
  p { margin: 0; }
  https://ckeditor.com/docs/ckeditor4/latest/examples/enterkey.html
  */

/* formulario de ingreso y modificacion */
include($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");

/* Cargo mi clase Base */
include("registroDespacho_class.php");
include("registroDespacho_edicionAdjuntosClass.php");
include("../catalogos/catalogosDependencias_class.php");
include("../catalogos/catalogosTabla_class.php");
include("../catalogos/catalogosPriorideadAtencion_class.php");
include("../catalogos/catalogosTipoExpediente_class.php");
include("../personal/personalDatosLaborales_class.php");
include("../admin/adminUsuario_class.php");
include("getAcumDespacho_class.php");
include("PlantillaDespacho_class.php");
include("gruposDerivaciones_class.php");
include("firmar_class.php");
include("catalogosProcedimientos_class.php");
include("../catalogos/catalogosProveedor_class.php"); 
include("registroDespachoEnvios_class.php");
include("registroDespachoColaborativos_class.php");
include("procesoDespacho_class.php");
include("../catalogos/catalogosUbigeo_class.php"); 

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

/* establecer conexiÃ³n con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id=getParam("id"); // captura la variable que viene del objeto lista
$clear=getParam("clear");
$array=getParam("sel");
$op=getParam("op");
$regSeleccionadosExp='';
$regSeleccionadosExpLink='';
$depe_id_origen=0;
if(is_array($array)) {
    for ($i = 0; $i < count($array); $i++) {
            $arrayPadreHijo=explode('_',$array[$i]);    
            $regSeleccionadosExp.=$arrayPadreHijo[0].',';
            
            $regSeleccionadosExpLink.= $regSeleccionadosExpLink!=''?',':'';
            $regSeleccionadosExpLink.=addLink($arrayPadreHijo[0],"javascript:lectorPDF('/sisadmin/gestdoc/controllers/procesar_data.php?nr_numTramite=$arrayPadreHijo[0]&vista=NoConsulta','Seguimiento')","Click aqu&iacute; para Seguimiento de registro","","h5");
                    
            $regSeleccionadosId.=$arrayPadreHijo[1].',';
            $referencia=explode('.',$arrayPadreHijo[0]);    
            $bd_desp_expediente=$bd_desp_expediente?$bd_desp_expediente:$referencia[0];
    }        
    $regSeleccionadosExp=trim($regSeleccionadosExp,',');
    $regSeleccionadosId=trim($regSeleccionadosId,',');
    $bd_tabl_tipodespacho=140; //DOCUMENTO INSTITUCIONAL
    $depe_id_origen = getDbValue("SELECT depe_iddestino
                                     FROM gestdoc.despachos_derivaciones 
                                     WHERE dede_id=".$arrayPadreHijo[1]);
}

$myClass = new despacho($id,NAME_EXPEDIENTE.' de Documentos');


if (!isset($_SESSION["ocarrito"])){
    $_SESSION["ocarrito"] = new carDeriva_class();
    
    /*variable que cuenta los registos archivados del despacho*/
    $cuenta_archivados=0;
    $cuenta_derivaciones=0;
    $cuenta_recibidos=0;
    $cuenta_firmantes=0;
    $cuenta_firmados=0;

    if (strlen($id)) { // ediciÃ³n
        $myClass->setDatos();
            if($myClass->existeDatos()){
                    $bd_id_despacho= $myClass->field('id');
                    $bd_depe_id = $myClass->field('depe_id');
                    $bd_prat_descripcion = $myClass->field('prat_descripcion');
                    $bd_tabl_modorecepcion = $myClass->field('tabl_modorecepcion');
                    $bd_modo_recepcion= $myClass->field('modo_recepcion');
                    $bd_tabl_tipodespacho = $myClass->field('tabl_tipodespacho');
                    $bd_tipo_despacho = $myClass->field('tipo_despacho');
                    $tipo_despacho = $myClass->field("tipo_despacho");
                    $num_documento = $myClass->field("num_documento");
                    $bd_desp_fecha = $myClass->field('desp_fecha');
                    $bd_plde_id= $myClass->field('plde_id');
                    $bd_tiex_id = $myClass->field('tiex_id');
                    $bd_tiex_exigir_marcar_documento_final = $myClass->field('tiex_exigir_marcar_documento_final');
                    
                    $tiex_descripcion = $myClass->field('tiex_descripcion');
                    $bd_desp_numero = $myClass->field('desp_numero');
                    $bd_desp_anno = $myClass->field('desp_anno');
                    $depe_nombre = $myClass->field('depe_nombre');
                    $bd_prov_id= $myClass->field('prov_id');
                    $bd_desp_descripaux= $myClass->field('desp_descripaux');
                    $bd_pdla_id = $myClass->field('pdla_firma');
                    $bd_desp_firma = $myClass->field('desp_firma');
                    $bd_desp_especbreve = $myClass->field('desp_especbreve');
                    $bd_desp_cargo = $myClass->field('desp_cargo');                   
                    $bd_desp_telefono = $myClass->field('desp_telefono');
                    $bd_desp_email = $myClass->field('desp_email');
                    
                    $bd_desp_codigo= $myClass->field('desp_codigo');
                    $bd_desp_entidad_origen = $myClass->field('desp_entidad_origen');
                    $bd_desp_direccion = $myClass->field('desp_direccion');
 
                    $bd_desp_siglas = $myClass->field('desp_siglas');
                    $bd_desp_procesador = $myClass->field('desp_procesador');
                    $bd_desp_para_depe_id = $myClass->field('desp_para_depe_id');
                    $bd_desp_para_pdla_id = $myClass->field('desp_para_pdla_id');
                    $bd_desp_para_grupo = $myClass->field('desp_para_grupo');
                            
                    $bd_desp_para_destino = $myClass->field('desp_para_destino');
                    $bd_desp_para_cargo = $myClass->field('desp_para_cargo');
                    $bd_desp_para_dependencia = $myClass->field('desp_para_dependencia');
                    $bd_desp_vistos = $myClass->field('desp_vistos');
                    $bd_desp_firmas_jefes = $myClass->field('desp_firmas_jefes');
                    
                    $bd_desp_vistos_empleados = $myClass->field('desp_vistos_empleados');
                    $bd_desp_firmas_externos = $myClass->field('desp_firmas_externos');
                    
                    $bd_desp_asunto = $myClass->field('desp_asunto');
                    $bd_desp_referencia= $myClass->field('desp_referencia');
                    $bd_desp_folios = $myClass->field('desp_folios');
                    $bd_desp_proyectadopor = $myClass->field('desp_proyectadopor');
                    //$bd_desp_trelacionado= $myClass->field('desp_trelacionado');
                    $bd_desp_expediente= $myClass->field('desp_expediente');
                    $bd_desp_notas= $myClass->field('desp_notas');
                    $bd_prat_id = $myClass->field('prat_id');
                    $bd_desp_exp_legal=$myClass->field('desp_exp_legal');
                    $bd_desp_demandante=$myClass->field('desp_demandante');
                    $bd_desp_demandado=$myClass->field('desp_demandado');
                    $bd_desp_resolucion=$myClass->field('desp_resolucion');
                    $bd_exle_id=$myClass->field('exle_id');
                    $bd_desp_contenido=$myClass->field('desp_contenido');
                    $bd_desp_proyectado=$myClass->field('desp_proyectado');
                    $bd_depe_id_proyectado=$myClass->field('depe_id_proyectado');
                    $bd_poyectado_por=$myClass->field('poyectado_por');
                    
                    $bd_desp_vb=$myClass->field('desp_vb');
                    $bd_desp_exterior=$myClass->field('desp_exterior');
                    $bd_desp_estado=$myClass->field('desp_estado');
                    $bd_proc_id=$myClass->field('proc_id');
                            
                    $regSeleccionadosExp=$myClass->field('desp_adjuntados_exp');
                    $regSeleccionadosId=$myClass->field('desp_adjuntados_id');
                    $bd_usua_id	= $myClass->field('usua_id');
                    $bd_usua_idfirma = $myClass->field('usua_idfirma');
                    $username = $myClass->field("usuario_crea");
                    $usernameactual = $myClass->field("usuario_modifica");
                    $bd_desp_actualfecha = $myClass->field("desp_actualfecha");
                    $cuenta_firmantes = $myClass->field("desp_cont_firmas");
                    $cuenta_firmados = $myClass->field("desp_cont_firmados");
                    $cuenta_recibidos = $myClass->field("desp_acum_recibidos");
                    
                    $bd_plde_titulo = $myClass->field("plde_titulo");
                    $bd_tipo_persona = $myClass->field("tipo_persona");
                    $bd_tabl_tipopersona = $myClass->field("tabl_tipopersona");
                    
                    $bd_ubigeo = $myClass->field("ubigeo");
                    $ubig_id = $myClass->field("ubig_id");
                            
                    if($cuenta_recibidos>0){
                        $habilita_edicion=0;
                    }else{
                        $habilita_edicion=1;
                    }
                        
                    $desp_file_firmado=$myClass->field("desp_file_firmado");
                    $desp_ocultar_editor=$myClass->field("desp_ocultar_editor");

                    /*recorre las derivaciones*/
                    $derivacion= new despachoDerivacion_SQLlista();
                    $derivacion->wherePadreID($id);
                    //$derivacion->whereUsuaIDCrea($bd_usua_id);
                    $derivacion->orderUno();
                    $sql=$derivacion->getSQL();
                    $rs = new query($conn, $sql);

                    /*recorre las derivaciones*/
                    while ($rs->getrow()) {

                            if($rs->field('usua_iddestino')){
                                $idDeriva=$rs->field('depe_iddestino').'_'.$rs->field('usua_iddestino');
                                $elemento=$rs->field("depe_iddestino").' '.$rs->field("depe_nombre_destino")." [".$rs->field("usuario_destino")."]";
                            }else{
                                $idDeriva=$rs->field('depe_iddestino');
                                $elemento=$rs->field('depe_iddestino').' '.$rs->field("depe_nombre_destino");
                            }

                            $arrayEdit['tx_hijo_id']=$rs->field('dede_id');
                            $arrayEdit['id']=$idDeriva;
                            $arrayEdit['depe_id']=$rs->field('depe_iddestino');
                            $arrayEdit['usua_id']=$rs->field('usua_iddestino');
                            $arrayEdit['elemento']=$elemento;
                            $arrayEdit['proveido']=$rs->field('dede_proveido');
                            $arrayEdit['cc']=$cc;
                            $arrayEdit['usua_idcrea']=$rs->field('usua_idcrea');
                            $arrayEdit['dede_estado']=$rs->field('dede_estado');

                            $_SESSION["ocarrito"]->Add($arrayEdit);

                            if($rs->field('dede_estado')==6){ //archivado
                                $cuenta_archivados++;
                            }
                            
                            $cuenta_derivaciones++;
                            
                            if($rs->field('usua_idrecibe')){ //recibidos
                                //$cuenta_recibidos++;
                            }
                    }
                    /*fin recorre las derivaciones*/
            }
    }
}

if (!$id){
    //si la dependencoa viene del boton reponder
    if($depe_id_origen>0){
        $bd_depe_id=$depe_id_origen;
    }else{
        $bd_depe_id=getSession("sis_depeid");        
    }
    $bd_desp_fecha=date('Y-m-d');
    $bd_tabl_modorecepcion=145; //DIRECTA
    //$bd_tiex_id=19;//OFICIO INFORME x default    
    $bd_desp_asunto='';
    $bd_desp_folios='';
    $bd_desp_proyectadopor='';
    $bd_desp_trelacionado='';
    $bd_prat_id=0;
    $desp_ocultar_editor=0;
    $habilita_edicion=1;    
    $bd_tabl_tipopersona=1;
    
    if(!isset($bd_tabl_tipodespacho)){
        $bd_tabl_tipodespacho=getSession("SET_TIPO_DESPACHO");
    }
    
    $bd_desp_estado=1;//documento abietor
    //OBTIENE EL DOCUMENTO MAS UTILIZADO
    $bd_tiex_id=getDbValue("SELECT tiex_id FROM gestdoc.despachos
                                        WHERE depe_id=$bd_depe_id
                                               AND tabl_tipodespacho=$bd_tabl_tipodespacho
                                        GROUP BY tiex_id 
                                        ORDER BY count(tiex_id) DESC
                                        LIMIT 1");    
    $bd_tiex_id=$bd_tiex_id?$bd_tiex_id:19;
}

/* verificaciÃ³n del nÃ­vel de usuario */
verificaUsuario(1);


// Inicio Para Ajax
require_once("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("beforeFirma", "Firmar","beforeFirma"),"");
$xajax->registerExternalFunction(array("buscarProveedor", "proveedor","buscarProveedor"),"");
$xajax->registerExternalFunction(array("buscarUsuario", "clsUsers","buscarUsuario"),"");
$xajax->registerExternalFunction(array("buscarEnProceso", "despachoProceso","buscar"),"");
$xajax->setCharEncoding('utf-8');
$xajax->registerFunction("addProveedor");
$xajax->registerFunction("eligeProveedor");
$xajax->registerFunction("getInicia");
$xajax->registerFunction("getDependencia");
$xajax->registerFunction("setContenido");
$xajax->registerFunction("getDatosFirma");
$xajax->registerFunction("actFirmaCargo");
$xajax->registerFunction("addCarrito");
$xajax->registerFunction("elimCarrito");
$xajax->registerFunction("modiCarrito");
$xajax->registerFunction("verCarrito");
$xajax->registerFunction("btnManten");
$xajax->registerFunction("getVistos");
$xajax->registerFunction("getPara");
$xajax->registerFunction("todasDependencias");
$xajax->registerFunction("getSecuencia");
$xajax->registerFunction("getFecha");
$xajax->registerFunction("getChecks");
$xajax->registerFunction("getRequisitos");
$xajax->registerFunction("guardar");
$xajax->registerFunction("guardarDerivaciones");
//$xajax->registerFunction("getEntidad");
$xajax->registerFunction("upload_eFirma");
$xajax->registerFunction("getDocJudicial");
$xajax->registerFunction("reload");
$xajax->registerFunction("reload2");
$xajax->registerFunction("setFirma");
$xajax->registerFunction("deshacerMarcarFinal");
$xajax->registerFunction("closeModal");
$xajax->registerFunction("getEditor");
$xajax->registerFunction("muestraNuevo");
$xajax->registerFunction("getFiles");
$xajax->registerFunction("eliminaFile");
$xajax->registerFunction("updownFile");
$xajax->registerFunction("enviar_email");
$xajax->registerFunction("listaHistorialEnvios");
$xajax->registerFunction("colaborativo");
$xajax->registerFunction("listaColaborativos");
$xajax->registerFunction("ejigeUsuario");
$xajax->registerFunction("actualizaPermiso");
$xajax->registerFunction("elijeExpediente");
$xajax->registerFunction("getTipoPersona");
function reload($id,$op)
{
    $objResponse = new xajaxResponse();
    //DENTRO DEL REPORTE ACTUALIZA EL CAMPO desp_file_firmado
    //$objResponse->addScript("generar_pdf('rptDocumento.php?id=$id&tipo_vista=F&marcar_final=1')");        
    
    unset($_SESSION["ocarrito"]);
    $destino="registroDespacho_edicionConFirma.php?id=$id&op=$op";
    $objResponse->addRedirect($destino);           
    return $objResponse;
}

function reload2($id,$op)
{
    $objResponse = new xajaxResponse();
    //DENTRO DEL REPORTE ACTUALIZA EL CAMPO desp_file_firmado
    //$objResponse->addScript("generar_pdf('rptDocumento.php?id=$id&tipo_vista=F&marcar_final=1')");        
    
    unset($_SESSION["ocarrito"]);
    $objResponse->addScript("enviarEmail('$id')");
    $destino="registroDespacho_edicionConFirma.php?id=$id&op=$op";
    $objResponse->addRedirect($destino);           
    return $objResponse;
}


function deshacerMarcarFinal($id)
{
    global $conn;
    $objResponse = new xajaxResponse();
    
    $sSql="UPDATE gestdoc.despachos 
                SET desp_file_firmado=NULL,
                    desp_file_firmado_fregistro=NULL,
                    desp_estado=1 /*ABIERTO*/
                    WHERE desp_id=$id;";

    // Ejecuto el string
    $conn->execute($sSql);
    $error=$conn->error();

    if($error){ 
            $objResponse->addAlert($error);
    }else{
        unset($_SESSION["ocarrito"]);
        $destino="registroDespacho_edicionConFirma.php?id=$id";
        $objResponse->addRedirect($destino);           
    }
    return $objResponse;
}



function closeModal()
{
    global $conn;
    //ELIMINA ARCHIVOS ZIP GENERADOS
    $objResponse = new xajaxResponse();
    
    $signerDelete=new signer_SQLlista();
    $signerDelete->whereUsuaID(getSession("sis_userid"));
    $signerDelete->whereHoy();    
    $sqlSignerDelete=$signerDelete->getSQL();
    $rsSignerDelete = new query($conn, $sqlSignerDelete);
    while ($rsSignerDelete->getrow()){
        $file=$_SERVER[DOCUMENT_ROOT]."/firmar/df_".$rsSignerDelete->field('sign_id').iif(MOTOR_FIRMA,'==',2,'.7z','.zip');
        if(file_exists($file)){
            unlink($file);
        }
    }
    
    $signer=new signer();    
    $signer->desbloquear();
    unset($_SESSION["ocarrito"]);
    $objResponse->addScript("parent.content.location.reload()");    
    return $objResponse;
}

function elijeExpediente($id,$desp_expediente,$accion){
	$objResponse = new xajaxResponse();

        $arrayPadreHijo=explode('_',$id);    
        $regSeleccionadosExp=$arrayPadreHijo[0];
        $regSeleccionadosId=$arrayPadreHijo[1];
            
        $objResponse->addClear("divBuscarExpediente",'innerHTML');
        $objResponse->addScript("document.frm.nx_desp_expediente.value=$desp_expediente");
        $objResponse->addScript("document.frm.hx_asjuntar.value=1");
        $objResponse->addScript("document.frm.hx_relacionado_exp.value=$regSeleccionadosExp");
        $objResponse->addScript("document.frm.hx_relacionado_id.value=$regSeleccionadosId");
    
	return $objResponse;
}

function addProveedor($codigo,$formdata)
{
    global $conn;
    $objResponse = new xajaxResponse();
    
    $raz_social=strtoupper(substr($formdata['razon_social'],0,120));
 
    $apellidos=strtoupper(substr($formdata['paterno'].' '.$formdata['materno'],0,35));
    $nombres=strtoupper(substr($formdata['nombre'],0,35));
     
    $direccion=strtoupper(substr($formdata['direccion'],0,100));
    
    $usua_id=getSession("sis_userid");
    
    /* Sql a ejecutar */
    if(strlen(trim($codigo))==11){
        $sqlCommand ="SELECT catalogos.func_add_provedor_pj(  '$codigo',
                                                    '$raz_social',
                                                    '$direccion',
                                                    0::INTEGER,
                                                    $usua_id::INTEGER
                                                  ) ";
    }else{
        $sqlCommand ="SELECT catalogos.func_add_provedor_pn(  '$codigo',
                                                   '$apellidos',
                                                   '$nombres',
                                                   '$direccion',
                                                    0::INTEGER,
                                                    $usua_id::INTEGER,
                                                    NULL::INTEGER
                                                  ) ";
        $raz_social=$apellidos.' '.$nombres;
    }
    //ECHO $sqlCommand;
    $prov_id=$conn->execute($sqlCommand);
    $error=$conn->error();		
    if($error){ 
        $objResponse->addAlert($error);
    }
    else{
        $objResponse->addScript("xajax_eligeProveedor($prov_id,'$raz_social','$codigo',1)");
    }
    return $objResponse;
}

function eligeProveedor($prov_id,$prov_razsocial,$prov_codigo,$accion){
	$objResponse = new xajaxResponse();

        $proveedor=new proveedor_SQLlista();
        $proveedor->whereID($prov_id);
        $proveedor->setDatos();

        $prov_direccion=$proveedor->field('prov_direccion');
        $prov_cargo=$proveedor->field('prov_cargo');
        $prov_telefono=$proveedor->field('prov_telefono');
        $prov_email=$proveedor->field('prov_email');
        $ubig_id=$proveedor->field('ubig_id');
 
        $objResponse->addClear("divResultado",'innerHTML');
        $objResponse->addScript("document.frm.tx_prov_id.value=$prov_id");
        $objResponse->addScript("document.frm.Sx_proveedor.value='$prov_codigo'");
        $objResponse->addScript("document.frm._DummySx_proveedor.value='$prov_razsocial'");        
        $objResponse->addScript("document.frm.Sr_desp_firma.value='$prov_razsocial'");
        
        $objResponse->addScript("document.frm.Sx_desp_direccion.value='$prov_direccion'");
        $objResponse->addScript("$('#UBIGEO').val('$ubig_id').trigger('change')");
        $objResponse->addScript("document.frm.Sx_desp_cargo.value='$prov_cargo'");
        $objResponse->addScript("document.frm.Sx_desp_telefono.value='$prov_telefono'");
        $objResponse->addScript("document.frm.cx_desp_email.value='$prov_email'");

        
        return $objResponse;
}

function getInicia($op,$tipoDespacho,$id,$readonly){
        global $bd_tiex_id,$bd_prov_id,$bd_desp_descripaux,$bd_desp_firma,$bd_desp_especbreve,
               $bd_desp_cargo,$bd_desp_codigo,$bd_desp_entidad_origen,$bd_depe_id,$bd_desp_proyectado,
               $bd_desp_vb,$bd_desp_exterior,$bd_plde_id,$bd_desp_vistos,$bd_desp_firmas_jefes,$bd_desp_vistos_empleados,
               $bd_desp_firmas_externos,$bd_desp_estado,$bd_proc_id,$bd_desp_telefono,$bd_desp_email,$bd_pdla_id,$bd_desp_folios,
               $bd_desp_direccion,$cuenta_recibidos,$habilita_edicion,$desp_ocultar_editor,$bd_tabl_tipopersona,
               $bd_tipo_persona,$bd_ubigeo,$ubig_id,$bd_plde_titulo;
        
        $objResponse = new xajaxResponse();
        //$objResponse->addAlert($tipoDespacho);
        $oForm = new AddTableForm();
        $oForm->setLabelWidth("20%");
        $oForm->setDataWidth("80%");

        switch ($tipoDespacho){
                case 140://institucional
                    $bd_desp_proyectado=$bd_desp_proyectado?$bd_desp_proyectado:'true';
                    $bd_desp_vb=$bd_desp_vb?$bd_desp_vb:'false';
                    $bd_desp_exterior=$bd_desp_exterior?$bd_desp_exterior:1; 

                    if($bd_desp_estado==1){//abierto                    
                        $modo_para=new clsTabla_SQLlista();
                        $modo_para->whereTipo('MODO_PARA');
                        $sqlmodo_para=$modo_para->getSQL_cboxCodigo();

                        $oForm->addField("",checkboxField("Mas_Firmas","hx_visto",1,$bd_desp_vb==1,"onClick=\"xajax_getVistos(1,this.checked,document.frm.tr_tiex_id.value,0,'$bd_desp_vistos','$bd_desp_firmas_jefes','$bd_desp_vistos_empleados','$bd_desp_firmas_externos','')\" ".iif($readonly,'==','readonly','disabled',''))." <B>M&aacute;s Firmas </B> ".
                                                    "&nbsp;&nbsp;&nbsp;<font class='LabelFONT'>Destino:</font> ".listboxField("Destino",$sqlmodo_para,"hx_solicita",$bd_desp_exterior,"","onChange=\"xajax_getPara(1,this.value,document.frm.nr_depe_id.value,document.frm.___tabl_tipodespacho.value,'',document.frm.xxx_plantilla_destinatario.value)\" ".iif($readonly,'==','readonly','disabled','')));
                    }
                    
                    $oForm->addHtml("<tr><td colspan=2><div id='divDatosDependencia'>\n"); //pide datos de afectacion presupuestal
                    $oForm->addHtml(getDependencia(2,$id,$bd_depe_id,$bd_desp_proyectado));
                    $oForm->addHtml("</div></td></tr>\n");
                    
                    $oForm->addHidden("___tabl_tipodespacho",$tipoDespacho); // clave primaria
                    $oForm->addHtml("<tr><td colspan=2><div id='divDatosFirma'>\n"); //pide datos de afectacion presupuestal
                    $oForm->addHtml(getDatosFirma(2,$bd_plde_id,$tipoDespacho,$bd_depe_id,0,$bd_tiex_id,$readonly,1));
                    $oForm->addHtml("</div></td></tr>\n");
                    
                    $oForm->addHtml("<tr><td colspan=2><div id='divVistos'>\n"); //pide datos de afectacion presupuestal
                    
                    $oForm->addHtml(getVistos(2,$bd_desp_vb,$bd_tiex_id,$bd_desp_proyectado,$bd_desp_vistos,$bd_desp_firmas_jefes,$bd_desp_vistos_empleados,$bd_desp_firmas_externos,$readonly));
                    $oForm->addHtml("</div></td></tr>\n");                    
                    $oForm->addHidden("___proyectado",$bd_desp_proyectado); 
                    $oForm->addHidden("___vb",$bd_desp_vb); 
                    $oForm->addHidden("___exterior",$bd_desp_exterior); 
                    break;

                case 141://personal
                    $bd_desp_proyectado=$bd_desp_proyectado?$bd_desp_proyectado:'false';                    
                    $bd_desp_vb=$bd_desp_vb?$bd_desp_vb:'false';
                    
                    if($bd_desp_estado==1){//abierto                    
                        $modo_para=new clsTabla_SQLlista();
                        $modo_para->whereTipo('MODO_PARA');
                        $modo_para->whereID(137); //PARA UNA DEPENDENCIA
                        $sqlmodo_para=$modo_para->getSQL_cboxCodigo();

                        $oForm->addField("",checkboxField("Mas_Firmas","hx_visto",1,$bd_desp_vb==1,"onClick=\"xajax_getVistos(1,this.checked,document.frm.tr_tiex_id.value,0,'$bd_desp_vistos','$bd_desp_firmas_jefes','$bd_desp_vistos_empleados','$bd_desp_firmas_externos','')\" ".iif($readonly,'==','readonly','disabled',''))." <B>M&aacute;s Firmas </B> ".
                                                    "&nbsp;&nbsp;&nbsp;<font class='LabelFONT'>Destino:</font> ".listboxField("Destino",$sqlmodo_para,"hx_solicita",$bd_desp_exterior,"","onChange=\"xajax_getPara(1,this.value,document.frm.nr_depe_id.value,document.frm.___tabl_tipodespacho.value,'',document.frm.xxx_plantilla_destinatario.value)\" ".iif($readonly,'==','readonly','disabled','')));
                    }
                    
                   
                    
//                    if($bd_plde_id){
//                        $plantilla=new clsPlantillaDespacho_SQLlista();        
//                        $plantilla->whereID($bd_plde_id);
//                        $plantilla->setDatos();
//                        $oForm->addField("Plantilla: ",$plantilla->field('id').' '.$plantilla->field('plde_titulo'));
//                    }elseif($bd_desp_estado==1){//abierto 
//                        $plantilla=new clsPlantillaDespacho_SQLlista();
//                        $plantilla->whereDepeID($bd_depe_id);
//                        $plantilla->whereTipodespacho($tipoDespacho);
//                        //$plantilla->whereDdestino();
//                        $plantilla->whereActivo();
//                        $sqlTipoPlantilla=$plantilla->getSQL_cbox();
//                        $oForm->addField("Plantilla: ",listboxField("Plantilla",$sqlTipoPlantilla,"tx_plde_id",$bd_plde_id,"-- Seleccione Plantilla--","onChange=\"xajax_setContenido(this.value,'$readonly')\"","","class=\"my_select_box\"" ));
//                    }
                    
                    $dependencia=new dependencia_SQLlista();
                    $dependencia->whereID($bd_depe_id);
                    $dependencia->setDatos();
                    
                    if(!$id){
                        $bd_pdla_id=getSession("sis_pdlaid");
                        $empleado=new clsDatosLaborales_SQLlista();
                        $empleado->whereID($bd_pdla_id);
                        $empleado->setDatos();
                        $bd_desp_firma=$empleado->field('empleado');
                        $bd_desp_cargo=$empleado->field('pdla_cargofuncional_ext');
                        $bd_desp_especbreve=$empleado->field('pdla_especbreve');
                    }
                    
                    $oForm->addHtml("<tr><td colspan=2><div id='divDatosDependencia'>\n"); //pide datos de afectacion presupuestal
                    $oForm->addHtml(getDependencia(2,$id,$bd_depe_id,''));
                    $oForm->addHtml("</div></td></tr>\n");
                    
                    $oForm->addHidden("___tabl_tipodespacho",$tipoDespacho); // clave primaria
                    $oForm->addHtml("<tr><td colspan=2><div id='divDatosFirma'>\n"); //pide datos de afectacion presupuestal
                    $oForm->addHtml(getDatosFirma(2,$bd_plde_id,$tipoDespacho,$bd_depe_id,0,$bd_tiex_id,$readonly,1));
                    $oForm->addHtml("</div></td></tr>\n");
                    
                    //$oForm->addField("Procedencia: ",$dependencia->field("depe_nombre"));                    
                    //$oForm->addField("Firma/Cargo: ",$bd_desp_especbreve.' '.$bd_desp_firma.'/'.$bd_desp_cargo);
                    
                    //$oForm->addField("Cargo: ",$cargo);
                    if($readonly=='' && getSession("sis_pdlaid")!=$dependencia->field('pdla_id')){
                        $oForm->addField("","<div id='divMensajejefeInmediato'><font color=\"red\">IMPORTANTE: ESTE DOCUMENTO SER&Aacute; VISADO POR EL JEFE INMEDIATO</font></div>");
                    }
                    $oForm->addHtml("<tr><td colspan=2><div id='divVistos'>\n"); //pide datos de afectacion presupuestal
                    $oForm->addHtml(getVistos(2,$bd_desp_vb,$bd_tiex_id,$bd_desp_proyectado,$bd_desp_vistos,$bd_desp_firmas_jefes,$bd_desp_vistos_empleados,$bd_desp_firmas_externos,$readonly));                    
                    $oForm->addHtml("</div></td></tr>\n");                    
                    

                    $oForm->addHidden("Sr_desp_firma",$bd_desp_firma,"Firma");
                    $oForm->addHidden("Sx_desp_cargo",$bd_desp_cargo,"Cargo");
                    $oForm->addHidden("Sx_desp_especbreve",$bd_desp_especbreve,"Grado/Especialidad");
                    $oForm->addHidden("nr_pdla_firma",$bd_pdla_id,"Id Firmante");
                    $oForm->addHidden("___proyectado",0); 
                    $oForm->addHidden("___vb",$bd_desp_vb); 
                    $oForm->addHidden("___exterior",$bd_desp_exterior); 
                    //$objResponse->addClear('divDatosFirma','innerHTML');
                    break;

                case 142://otras entidades
                    if($bd_desp_estado==1 && $desp_ocultar_editor==0){//abierto                    
                        $modo_para=new clsTabla_SQLlista();
                        $modo_para->whereTipo('MODO_PARA');
                        $sqlmodo_para=$modo_para->getSQL_cboxCodigo();
                        $oForm->addField("",checkboxField("M&aacute;s Firmas: ","hx_visto",1,$bd_desp_vb==1,"onClick=\"xajax_getVistos(1,this.checked,document.frm.tr_tiex_id.value,0,'$bd_desp_vistos','$bd_desp_firmas_jefes','$bd_desp_vistos_empleados','$bd_desp_firmas_externos','')\" ".iif($readonly,'==','readonly','disabled',''))." <B>M&aacute;s Firmas</B> ");
                    }                    
                    
                    if($bd_proc_id){
                        $procedimiento = new procedimiento_SQLlista();
                        $procedimiento->whereID($bd_proc_id);
                        $procedimiento->setDatos();
                        $oForm->addField("Procedimiento: ",$procedimiento->field('proc_nombre'));
                    }else{                        
                        $procedimiento = new procedimiento_SQLlista();
                        $procedimiento->whereDepeID(getSession("sis_depe_superior"));
                        $procedimiento->whereEstado(1);
                        //$procedimiento->whereTipoDespacho($tipoDespacho);
                        $sqlProcedimiento = $procedimiento->getSQL_cbox();
                        $oForm->addField("Procedimiento: <font color=red>*</font>",listboxField("Procedimiento",$sqlProcedimiento,"tr_proc_id",$bd_proc_id,"-- Seleccione Procedimiento--","onChange=\"xajax_getRequisitos(this.value,'$readonly')\"","","class=\"my_select_box\"" ));
                    }
                    if(($cuenta_recibidos>0 && $habilita_edicion==0) || $readonly=='readonly')
                    {   
                        $oForm->addField("Plantilla: ",$bd_plde_titulo);
                        $oForm->addField("Tipo de Persona: ",$bd_tipo_persona);
                        $oForm->addField("DNI/RUC/OT: ",$bd_desp_codigo);
                        $oForm->addField("Entidad de Procedencia: ",$bd_desp_entidad_origen);
                        $oForm->addField("Diercci&oacute;nn: ",$bd_desp_direccion);
                        $oForm->addField("UBIGEO: ",$bd_ubigeo);
                        $oForm->addField("Firma: ",$bd_desp_firma);
                        $oForm->addField("Cargo: ",$bd_desp_cargo);
                        $oForm->addField("Tel&eacute;fono: ",$bd_desp_telefono);
                        $oForm->addField("Email: ",$bd_desp_email);
                        $oForm->addField("N&uacute;mero de Folios: ",$bd_desp_folios);
                    }
                    else{
                        $oForm->addHtml("<tr><td colspan=2><div id='divDatosFirma'>\n"); //pide datos de afectacion presupuestal
                        $oForm->addHtml(getDatosFirma(2,$bd_plde_id,$tipoDespacho,$bd_depe_id,0,$bd_tiex_id,$readonly,1));
                        $oForm->addHtml("</div></td></tr>\n");

                        $oForm->addHtml("<tr><td colspan=2><div id='divVistos'>\n"); //pide datos de afectacion presupuestal
                        $oForm->addHtml("</div></td></tr>\n");                    
                                        
//                        $oForm->addField("RUC/DNI: ",numField("codigo_ruc_dni","nx_desp_codigo",$bd_desp_codigo,11,11)."&nbsp;<input type=\"button\" onClick=\"xajax_getEntidad(document.frm.nx_desp_codigo.value,document.frm.Sx_desp_entidad_origen.value,document.frm.Sr_desp_firma.value,document.frm.Sx_desp_cargo.value)\" value=\"Buscar\">");
//                        $oForm->addHtml("<tr><td colspan=2><div id='divBuscarDNI'></div></td></tr>\n");
                        //$oForm->addField("Descripci&oacute;n Auxiliar: ",textField("Auxiliar","Sx_desp_descripaux",$bd_desp_descripaux,80,80));
                        $tipo_persona=new clsTabla_SQLlista();
                        $tipo_persona->whereTipo('TIPO_PERSONA');
                        $sqlTipo_persona=$tipo_persona->getSQL_cboxCodigo();
                        $oForm->addField("Tipo de Persona:",listboxField("Tipo de Persona",$sqlTipo_persona,"tx_tabl_tipopersona",$bd_tabl_tipopersona,"-- Seleccione Tipo --","onChange=\"xajax_getTipoPersona(1,this.value,'$bd_desp_codigo')\"","","class=\"my_select_box\"" ));
                        
                        $oForm->addHtml("<tr><td colspan=2><div id='divTipoPersona'>\n"); //pide datos de afectacion presupuestal
                        $oForm->addHtml(getTipoPersona(2,$bd_tabl_tipopersona,$bd_desp_codigo));
                        $oForm->addHtml("</div></td></tr>\n");
                        
                        $nvoProveedor="<a class=\"link\" href=\"javascript:nuevaEntidad()\" title=\"Ingresar Nueva Entidad\"><b>Nueva Entidad<b></a>";
                        $btnBuscar="<input type=\"button\" onClick=\"xajax_buscarProveedor(1,document.frm._DummySx_proveedor.value,'','2,3,4','divResultado',1);document.getElementById('divResultado').innerHTML = 'Espere, Buscando...'\" value=\"Buscar\">";

                        $oForm->addField("Entidad de Procedencia: <font color=red>*</font>",textField("Entidad de Procedencia","_DummySx_proveedor",$bd_desp_entidad_origen,60,80,$READONLY)."&nbsp;$btnBuscar&nbsp;$nvoProveedor");
                        $oForm->addHidden("__Change_Sx_proveedor",'');//SOLO PARA CUMPLIR CON LA NECESIDAD DE LA PAGINA DE NUEVO PROVEEDOR
                        $oForm->addHidden("tx_prov_id","$bd_prov_id",'ID Entidad de Procedencia'); //OJO si cambia a tx_prov_id cambiar tambien en update de dependencias_externas_class.php
                        $oForm->addHtml("<tr><td colspan=2><div id='divResultado'>\n");
                        $oForm->addHtml("</div></td></tr>\n");

                        
//                        $oForm->addField("Entidad de Procedencia: ",textField("Entidad_Origen","Sx_desp_entidad_origen",$bd_desp_entidad_origen,80,120));
                        $oForm->addField("Direcci&oacute;n: ",textField("Direccion","Sx_desp_direccion",$bd_desp_direccion,80,120));

                        $ubigeo=new ubigeo_SQLlista();
                        $sqlUbigeo=$ubigeo->getSQL_cbox();
                        $oForm->addField("UBIGEO: ",listboxField("UBIGEO",$sqlUbigeo, "sx_ubig_id",$ubig_id,"-- Seleccione UBIGEO --","", "","class=\"my_select_box\""));
                        
                        
                        $oForm->addField("Firma: <font color=red>*</font>",textField("Firma","Sr_desp_firma",$bd_desp_firma,80,80));
                        $oForm->addField("Cargo: ",textField("Cargo","Sx_desp_cargo",$bd_desp_cargo,80,80));

                        $oForm->addField("Tel&eacute;fono: ",textField("Tel&eacute;fono","Sx_desp_telefono",$bd_desp_telefono,12,12));
                        $oForm->addField("Email: ",textField("Email","cx_desp_email",$bd_desp_email,55,50));
                        $oForm->addField("N&uacute;mero de Folios: <font color=red>*</font>",numField("N&uacute;mero de Folios","nr_desp_folios",$bd_desp_folios,6,6,0));
                    }
                    
                    
                    $oForm->addHidden("___tabl_tipodespacho",$tipoDespacho); // clave primaria
                    $oForm->addHidden("nr_depe_id",$bd_depe_id,"Procedencia");
                    $oForm->addHidden("___proyectado",0); 
                    $oForm->addHidden("___vb",0); 
                    $oForm->addHidden("___exterior",1); 
                    
                    break;

                default:
                    $objResponse->addAlert('Proceso cancelado, Seleccione opciÃ³n');
                    $oForm->addHidden("___tabl_tipodespacho",NULL); // clave primaria
                    if($op==1){
                        return $objResponse;
                    }else{
			$oForm->addBreak("!NO SE ENCONTRARON DATOS...!!");
                    }
                    break;
           }
        $contenido_respuesta=$oForm->writeHTML();

        $objResponse->addAssign('divDatosIniciales','innerHTML', $contenido_respuesta);
        $objResponse->addScript("tb_init('a.thickbox')");

        if($op==1){
            $objResponse->script("xajax_getSecuencia(1,document.frm.tr_tiex_id.value,document.frm.Dr_desp_fecha.value,document.frm.___tabl_tipodespacho.value,document.frm.nr_depe_id.value,document.frm.___proyectado.value,'$readonly',1)");
            $objResponse->script("xajax_getFecha(1,document.frm.Dr_desp_fecha.value,document.frm.tr_tiex_id.value,document.frm.___tabl_tipodespacho.value,'$readonly','divFechaDoc')");
            $objResponse->script("xajax_getEditor(1,document.frm.tr_tiex_id.value,CKEDITOR.instances['K__desp_contenido'].getData(),'$tipoDespacho','$readonly','divEditor')");            
            
            if($tipoDespacho==142){//DOCUMENTO EXTERNO
                $objResponse->script("xajax_getPara(1,1,document.frm.nr_depe_id.value,document.frm.___tabl_tipodespacho.value,'',document.frm.xxx_plantilla_destinatario.value)");
            }else{
                $objResponse->script("xajax_getPara(1,document.frm.hx_solicita.value,document.frm.nr_depe_id.value,document.frm.___tabl_tipodespacho.value,'',document.frm.xxx_plantilla_destinatario.value)");
            }
                
            $objResponse->script("$('.my_select_box').select2({
                                    placeholder: 'Seleccione un elemento de la lista',
                                    allowClear: true,
                                    width: '90%',
                                });
                                mySelect();
                                ");            
            return $objResponse;
        }
	else{
            return $contenido_respuesta;
        };
}


function getTipoPersona($op,$valor,$codigo){
    
    $objResponse = new xajaxResponse();

    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");
    
    if ( $valor==1 ){//PERSONA NATURAL
        $label="DNI";
        $length=8;
    }elseif ( $valor==2 ){//PERSONA JURIDICA
        $label="RUC";
        $length=11;
    }elseif ( $valor==3 ){//CARNET DE EXTRANJERIA
        $label="CE";
        $length=9;
    }
    
    $btnBuscarCodigo="<input type=\"button\" onClick=\"javascript:xajax_buscarProveedor(3,document.frm.Sx_proveedor.value,'prov_codigo','2,3,4','divResultado',1);document.getElementById('divResultado').innerHTML = 'Espere, Buscando...'\" value=\"Buscar\">";
    $oForm->addField("$label: ",numField("$label","Sx_proveedor","$codigo",12,$length,0,false,"")."&nbsp;$btnBuscarCodigo&nbsp");    

    $contenido_respuesta=$oForm->writeHTML();    
    if($op==1){
        
        $objResponse->addAssign('divTipoPersona','innerHTML', $contenido_respuesta);
        
        return $objResponse;
    }
    else{
        return $contenido_respuesta;
    }
}


function getDatosFirma($op,$bd_plde_id,$tipoDespacho,$depe_id,$proyecta,$bd_tiex_id,$readonly,$ejecutar_getPara){
    global $conn,$id,$bd_desp_estado,$bd_desp_firma,$bd_desp_especbreve,$bd_desp_cargo,
            $bd_pdla_id,$bd_depe_id_proyectado,$bd_poyectado_por,$desp_ocultar_editor;
    
    $objResponse = new xajaxResponse();
    //$objResponse->addAlert($tipoDespacho);
    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");
    
    //SE COLOCA ACA PORQUE LAS PLANTILLA SE PUEDEN FILTRAR POR UNA DEPENDENCIA
    if($bd_plde_id && $readonly=='readonly'){
        $plantilla=new clsPlantillaDespacho_SQLlista();        
        $plantilla->whereID($bd_plde_id);
        $plantilla->setDatos();
        $oForm->addField("Plantilla: ",$plantilla->field('id').' '.$plantilla->field('plde_titulo'));
    }elseif($bd_desp_estado==1 && $desp_ocultar_editor==0){//abierto  
        $plantilla=new clsPlantillaDespacho_SQLlista();
        if($depe_id==1){
            $depe_superior = 2;
        }else{
            $depe_superior = getDbValue("SELECT depe_id FROM catalogos.func_treeunidsuperior2($depe_id)");        
        }
        $plantilla->whereDepeID2($depe_superior);
        $plantilla->whereSubDependencia2($depe_id);
        $plantilla->whereTipodespacho($tipoDespacho);
        //$plantilla->whereDdestino();
        $plantilla->whereActivo();
        $sqlTipoPlantilla=$plantilla->getSQL_cbox();
        $oForm->addField("Plantilla: ",listboxField("Plantilla",$sqlTipoPlantilla,"tx_plde_id",$bd_plde_id,"-- Seleccione Plantilla--","onChange=\"xajax_setContenido(this.value,'$readonly')\"","","class=\"my_select_box\"" ));
    }
    
    if (!$id){
        if($tipoDespacho==140){//INSTITUCIONAL
            $dependencia=new dependencia_SQLlista();
            $dependencia->whereID($depe_id);
            $dependencia->setDatos();
            $bd_pdla_id=$dependencia->field("pdla_id");

            $jefe=new clsDatosLaborales_SQLlista();
            $jefe->whereID($bd_pdla_id);
            $jefe->setDatos();
            $bd_desp_firma=$jefe->field('empleado');
            $bd_desp_cargo=$jefe->field('pdla_cargofuncional_ext');
            $bd_desp_especbreve=$jefe->field('pdla_especbreve');        
        }elseif($tipoDespacho==141){//PERSONAL
            
            $persona=new clsDatosLaborales_SQLlista();
            $persona->PersID(getSession("sis_persid"));
            //$persona->whereDepeID($depe_id);
            $persona->whereOrigen(0);//ficha origen
            $persona->setDatos();
            $bd_pdla_id=$persona->field('pdla_id');
            $bd_desp_firma=$persona->field('empleado');
            $bd_desp_cargo=$persona->field('pdla_cargofuncional_ext');
            $bd_desp_especbreve=$persona->field('pdla_especbreve');        
        }
    }
    
    if(inlist($tipoDespacho,'140,141')){ //INSTITUCIONAL/PERSONAL
        $oForm->addField("Firma/Cargo: ",$bd_desp_especbreve.' '.$bd_desp_firma.'/'.$bd_desp_cargo);    
        //$oForm->addField("Cargo: ",$cargo);
        $oForm->addHidden("Sr_desp_firma",$bd_desp_firma,"Firma");
        $oForm->addHidden("Sx_desp_cargo",$bd_desp_cargo,"Cargo");
        $oForm->addHidden("Sx_desp_especbreve",$bd_desp_especbreve,"Grado/Especialidad");    
        $oForm->addHidden("nr_pdla_firma",$bd_pdla_id,"Id Firmante");
    }
    if($bd_depe_id_proyectado && $readonly=='readonly'){
        $oForm->addField("Proyectado Por: ",$bd_poyectado_por);
        $oForm->addHidden("nx_depe_id_proyectado",$bd_depe_id_proyectado);
    }else{       
        $dependencia_proyecta=new dependencia_SQLlista();
        $dependencia_proyecta->whereVarios(getSession("sis_persid"));
        $sqlDependencia=$dependencia_proyecta->getSQL(); //OBTENGO TODAS LAS OFICINAS DONDE ESTA EL USUARIO ACTUAL
        $dependencia_proyecta->whereID($depe_id);
        $dependencia_proyecta->setDatos();

        if($dependencia_proyecta->existeDatos()==0){

            $rsDependencia = new query($conn, $sqlDependencia);
            while ($rsDependencia->getrow()){
                $ar_proyectado[$rsDependencia->field('depe_id')]=getSession('sis_username').'/'.$rsDependencia->field('depe_nombrecorto');
                if(!$bd_depe_id_proyectado){
                    $bd_depe_id_proyectado=$rsDependencia->field('depe_id');
                }           
            }

            $oForm->addField("Proyectado Por: ",listboxField("Proyectado",$ar_proyectado,"nx_depe_id_proyectado",$bd_depe_id_proyectado,"","","","class=\"my_select_box\"")); 
        }else{
            $oForm->addHidden("nx_depe_id_proyectado",'');
        }
    }
    
    $contenido_respuesta=$oForm->writeHTML();

    if($op==1){
        //SI NO HAY documento
        //if(!$bd_tiex_id){
            //OBTIENE EL DOCUMENTO MAS UTILIZADO
            $tiex_id=getDbValue("SELECT tiex_id FROM gestdoc.despachos
                                        WHERE depe_id=$depe_id
                                               AND tabl_tipodespacho=$tipoDespacho
                                        GROUP BY tiex_id 
                                        ORDER BY count(tiex_id) DESC
                                        LIMIT 1");
            $tiex_id=$tiex_id?$bd_tiex_id:$bd_tiex_id;
            $objResponse->script("document.frm.tr_tiex_id.value=$tiex_id");
            
        //}
        
        $objResponse->addAssign('divDatosFirma','innerHTML', $contenido_respuesta);
        $objResponse->script("xajax_getSecuencia(1,document.frm.tr_tiex_id.value,document.frm.Dr_desp_fecha.value,'$tipoDespacho','$depe_id',0,'$readonly',$ejecutar_getPara)");
        $objResponse->script("$('.my_select_box').select2({
                                    placeholder: 'Seleccione un elemento de la lista',
                                    allowClear: true,
                                    width: '90%',
                                });");
        return $objResponse;
    }
    else
        return $contenido_respuesta;
}


function setContenido($plde_id,$readonly){
    global $conn,$bd_depe_id;
    
    $objResponse = new xajaxResponse();    
    //$objResponse->setCharEncoding('iso-8859-1');
    $plantilla=new clsPlantillaDespacho_SQLlista();
    $plantilla->whereID($plde_id);
    $plantilla->setDatos();
    $tiex_id=$plantilla->field('tiex_id');
    $contenido=$plantilla->field('plde_contenido');

//    $plde_procedencia=$plantilla->field('plde_procedencia');
    $plde_destinatario=$plantilla->field('plde_destinatario');
    $plde_mas_vistos=$plantilla->field('plde_mas_vistos');
    $plde_mas_firmas=$plantilla->field('plde_mas_firmas');
    
    //PASO PROCEDENCIA, SIEMPRE QIE EXISTA JEFE
//    $sqlDependencia=new dependenciaJefe_SQLlista();
//    $sqlDependencia->whereID($plde_procedencia);
//    $sqlDependencia->setDatos();
//    if($sqlDependencia->existeDatos()){
//       $objResponse->script("$('#Procedencia').val('$plde_procedencia').trigger('chosen:updated')");
//       $objResponse->script("xajax_getDatosFirma(1,'$plde_id',document.frm.___tabl_tipodespacho.value,'$plde_procedencia',document.frm.___proyectado.value,document.frm.tr_tiex_id.value,'$readonly',0)");                     
//    }
    
    //PASO DESTINATARIO, SIEMPRE QIE EXISTA JEFE    
    $desp_para_depe_id='';
    $sqlDependencia=new dependenciaJefe_SQLlista();
    $sqlDependencia->whereIDVarios($plde_destinatario);
    $sqlDependencia->orderUno();            
    $sql=$sqlDependencia->getSQL();
    $rs = new query($conn, $sql);
    while ($rs->getrow()) {
        $desp_para_depe_id.=$desp_para_depe_id!=''?",":$desp_para_depe_id;
        $desp_para_depe_id.=$rs->field('depe_id');
    }
    if($desp_para_depe_id){
        $objResponse->script("$('#plantilla_destinatario').val('$desp_para_depe_id')");
        //$objResponse->script("$('#Destinatario').val([$desp_para_depe_id]).trigger('change')");
    }

    //PASO MAS VISTOS
    $desp_vistos='';
    $sqlDependencia=new dependenciaJefe_SQLlista();
    $sqlDependencia->whereIDVarios($plde_mas_vistos);
    $sqlDependencia->orderUno();            
    $sql=$sqlDependencia->getSQL();
    $rs = new query($conn, $sql);
    while ($rs->getrow()) {
        $desp_vistos.=$desp_vistos!=''?',':$desp_vistos;
        $desp_vistos.=$rs->field('depe_id');
    }

    
    //PASO MAS FIRMAS
    $desp_firmas='';
    $sqlDependencia=new dependenciaJefe_SQLlista();
    $sqlDependencia->whereIDVarios($plde_mas_firmas);
    $sqlDependencia->orderUno();            
    $sql=$sqlDependencia->getSQL();
    $rs = new query($conn, $sql);
    while ($rs->getrow()) {
        $desp_firmas.=$desp_firmas!=''?',':$desp_firmas;
        $desp_firmas.=$rs->field('depe_id');
    }
    

    if($desp_vistos || $desp_firmas){
        $objResponse->script("$('#Mas_Firmas').prop('checked', true)");
        $objResponse->script("xajax_getVistos(1,1,'$tiex_id',1,'$desp_vistos','$desp_firmas','','','$readonly')");        
        
    }
    
    //PASO ASUNTO
    $asunto=$plantilla->field('plde_asunto');
        

    $objResponse->addAssign('divContenido','innerHTML', $contenido);
    $objResponse->script("setFCContenido()");        
    
    if($asunto){
        $objResponse->script("$('#Asunto').val('$asunto')");
    }
    
    if($tiex_id){
       $objResponse->script("$('#Tipo_de_Documento').val('$tiex_id').trigger('change');");
       $objResponse->script("xajax_getSecuencia(1,'$tiex_id',document.frm.Dr_desp_fecha.value,document.frm.___tabl_tipodespacho.value,document.frm.nr_depe_id.value,document.frm.___proyectado.value,'$readonly',2)");
    }

    
    return $objResponse;
}


function getChecks($op,$id,$tiex_id,$bd_depe_id){
    global $bd_desp_proyectado,$bd_desp_vb,$bd_desp_exterior;
    
    $objResponse = new xajaxResponse();

    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");
    $texp=new clsTipExp_SQLlista();
    $texp->whereID($tiex_id);
    $texp->setDatos();
    
    $readonly=$id?"disabled='disabled'":'';
    if($texp->field('tiex_editor')==1){
    }else{
        $objResponse->script("document.frm.___proyectado.value=0");
        $objResponse->script("document.frm.___vb.value=0");
        $objResponse->script("document.frm.___exterior.value=0");
    }
    $contenido_respuesta=$oForm->writeHTML();    
    if($op==1){
        $objResponse->addAssign('divProyectar','innerHTML', $contenido_respuesta);        
        return $objResponse;
    }
    else
        return $contenido_respuesta;
}

function todasDependencias($checked){
    $objResponse = new xajaxResponse();

    if($checked=='true' || $checked==1){
        $sqlDependencia=new dependenciaJefe_SQLlista();
        $sql=$sqlDependencia->getSQL_lista();
        $lista=getDbValue($sql);
        $objResponse->script("$('#Para').val([$lista]).trigger('change');");
    }else{
        $objResponse->script("$('#Para').val([]).trigger('change');");
    }
    return $objResponse;
}

function getPara($op,$exterior,$depe_id,$bd_tabl_tipodespacho,$readonly,$plantilla_destinatario){
    global $bd_desp_para_depe_id,
            $bd_desp_para_destino,
            $bd_desp_para_cargo,
            $bd_desp_para_dependencia,
            $bd_desp_para_pdla_id,
            $bd_plde_id,$bd_desp_para_grupo ;
    
    $objResponse = new xajaxResponse();

    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");
    
    if($op==1 && $plantilla_destinatario!=""){
        $bd_desp_para_depe_id=$plantilla_destinatario;
    }
    if($exterior==1) {//DOCUMENTO PARA UN JEFE    
        if($bd_desp_para_depe_id!=''){
            $sqlDependencia=new dependenciaJefe_SQLlista();
            $sqlDependencia->whereIDVarios($bd_desp_para_depe_id);
            //$sqlDependencia->whereHabilitado();
            $sqlDependencia=$sqlDependencia->getSQL_cbox();
        }else{
            $sqlDependencia="";
        }

//        if($bd_desp_para_depe_id){
//            $bd_desp_para_depe_idx=explode(",",$bd_desp_para_depe_id);
//        }else{
//            $bd_desp_para_depe_idx="";
//        }
        
        $oForm->addField("<B>DESTINATARIO: </B><font color=red>*</font>",listboxField("Destinatario",$sqlDependencia,"sr_desp_para_depe_id[]","","seleccione Destinatario","","","class=\"my_select_box2\" multiple  ".iif($readonly,'==','readonly','disabled','')));        
    }elseif($exterior==2) {//DOCUMENTO PARA UN TRABAJADOR
        if($bd_desp_para_pdla_id){
            $sqlEmpleado=new clsDatosLaborales_SQLlista();
            $sqlEmpleado->whereIDVarios($bd_desp_para_pdla_id);
            //$sqlEmpleado->whereActivo();        
            //$sqlEmpleado->whereNoJefe();
            $sqlEmpleado=$sqlEmpleado->getSQL_cbox();
        }else{
            $sqlEmpleado="";
        }
        
//        if($bd_desp_para_pdla_id){
//            $bd_desp_para_pdla_idx=explode(",",$bd_desp_para_pdla_id);
//        }else{
//            $bd_desp_para_pdla_idx="";
//        }
        $oForm->addField("<B>DESTINATARIO: </B><font color=red>*</font>",listboxField("destinatarioEmpleados",$sqlEmpleado,"sr_desp_para_pdla_id[]","","seleccione Destinatario","","","class=\"my_select_box2\" multiple  ".iif($readonly,'==','readonly','disabled','')));        
    }elseif($exterior==3){//DOCUMENTOS PARA VARIOS Y EL EXTERIOR

        if(!$exterior && inlist($bd_tabl_tipodespacho,'141,142')){//DESDE MESA DE PARTES O EXTERIOR        
            $sqlDependencia=new dependenciaJefe_SQLlista();
            $sqlDependencia->orderUno();
            $sqlDependencia->whereHabilitado();
            $sqlDependencia=$sqlDependencia->getSQL_cbox();
            
//            if($bd_desp_para_depe_id){
//                $bd_desp_para_depe_idx=explode(",",$bd_desp_para_depe_id);
//            }else{
//                $bd_desp_para_depe_idx="";
//            }
            
            $oForm->addField("<B>DESTINATARIO: </B><font color=red>*</font>",listboxField("Destinatario",$sqlDependencia,"sr_desp_para_depe_id[]","","seleccione Destinatario","","","class=\"my_select_box\" multiple style=\"width:99%;\" ".iif($readonly,'==','readonly','disabled','')));        
            $objResponse->script("document.frm.___exterior.value=''");
        }else{
            $oForm->addField("<B>DESTINATARIO: </B><font color=red>*</font>",textAreaField("Destinatario","Er_desp_para_destino","$bd_desp_para_destino",1,80,3000,$readonly,0,"normal")); 
        }
//        $oForm->addField("Cargo: ",textAreaField("Cargo","Ex_desp_para_cargo","$bd_desp_para_cargo",1,80,3000,$readonly,0,"normal")); 
//        $oForm->addField("Entidad: ",textAreaField("Entidad","Ex_desp_para_dependencia","$bd_desp_para_dependencia",1,80,3000,$readonly,0,"normal")); 
        
    }else{//DESTINATARIO GRUPO
        $grupos=new clsGruposDerivaciones_SQLlista();
        $grupos->whereActivo();
        $grupos->orderUno();
        $sqlGrupos=$grupos->getSQL_cbox();   
        $oForm->addField("<B>DESTINATARIO: </B><font color=red>*</font>",listboxField("destinatarioGruoo",$sqlGrupos,"sr_desp_para_grupo",$bd_desp_para_grupo,"-- Seleccione Grupo --","","","class=\"my_select_box\"  ".iif($readonly,'==','readonly','disabled','')));        
    }
    
    $contenido_respuesta=$oForm->writeHTML();    
    if($op==1){
        
        $objResponse->script("document.frm.___exterior.value=$exterior");
        
        $objResponse->addAssign('divPara','innerHTML', $contenido_respuesta);
        
        $objResponse->script("$('.my_select_box').select2({
                                    placeholder: 'Seleccione un elemento de la lista',
                                    allowClear: true,
                                    width: '90%',
                                });
                               mySelect(); 
                              $('.normal').autosize({append:''});");
        
        if($bd_desp_para_depe_id){
            $objResponse->script("$('#Destinatario').val([$bd_desp_para_depe_id]).trigger('change')");
        }
        return $objResponse;
    }
    else
        return $contenido_respuesta;
}

function getVistos($op,$reqVB,$tiex_id,$proyecta,$bd_desp_vistos,$bd_desp_firmas_jefes,$bd_desp_vistos_empleados,$bd_desp_firmas_externos,$readonly){
    $objResponse = new xajaxResponse();

    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");
    
    if($reqVB=='true' || $reqVB==1) {//REQUIERE VB
        if($bd_desp_vistos){
            $Dependencia=new dependenciaJefe_SQLlista();
            $Dependencia->whereIDVarios($bd_desp_vistos);            
            $sqlDependencia=$Dependencia->getSQL_cbox4();
        }else{
            $sqlDependencia="";
        }
                
        $oForm->addField("+Vistos de Jefes: ",listboxField("mas_vistos_jefes",$sqlDependencia,"sx_desp_vistos[]","","seleccione firmante para VB","","","class=\"my_select_box2\" multiple  ".iif($readonly,'==','readonly','disabled','')));
        
        if($bd_desp_firmas_jefes){
            $Dependencia=new dependenciaJefe_SQLlista();
            $Dependencia->whereIDVarios($bd_desp_firmas_jefes);
            $sqlDependencia=$Dependencia->getSQL_cbox4();
        }else{
            $sqlDependencia="";
        }
        
//        if($bd_desp_firmas_jefes){
//            $bd_desp_firmas_jefesx=explode(",",$bd_desp_firmas_jefes);
//        }else{
//            $bd_desp_firmas_jefesx="";
//        }
                
        $oForm->addField("+Firmas de Jefes: ",listboxField("mas_firmas_jefes",$sqlDependencia,"sx_desp_firmas_jefes[]","","seleccione firmante","","","class=\"my_select_box2\" multiple  ".iif($readonly,'==','readonly','disabled','')));
        
        
        //VERIFICO SI EL DOCUMENTO PERMITE MAS FIRMAS DE EMPLEADOS
        $td=new clsTipExp_SQLlista();
        $td->whereID($tiex_id);
        $td->setDatos();                            
        $tiex_habilitar_mas_firmas_empleado=$td->field('tiex_habilitar_mas_firmas_empleado');
        $tiex_habilitar_mas_firmas_externo=$td->field('tiex_habilitar_mas_firmas_externo');
        
        if($tiex_habilitar_mas_firmas_empleado==1){
            
            if($bd_desp_vistos_empleados){
                $sqlEmpleado=new clsDatosLaborales_SQLlista();
                $sqlEmpleado->whereIDVarios($bd_desp_vistos_empleados);
                //$sqlEmpleado->whereNoJefe();
                $sqlEmpleado=$sqlEmpleado->getSQL_cbox();
            }else{
                $sqlEmpleado="";
            }
//            if($bd_desp_vistos_empleados){
//                $bd_desp_vistos_empleadosx=explode(",",$bd_desp_vistos_empleados);
//            }else{
//                $bd_desp_vistos_empleadosx="";
//            }
            $oForm->addField("+Firmas de Empleados: ",listboxField("mas_firmas_empleados",$sqlEmpleado,"sx_desp_vistos_empleados[]","","seleccione mas Firmantes","","","class=\"my_select_box2\" multiple ".iif($readonly,'==','readonly','disabled','')));        
        }
        
        if($tiex_habilitar_mas_firmas_externo==1){
            
            if($bd_desp_firmas_externos){
                $sqlExternos=new clsDatosLaborales_SQLlista();
                $sqlExternos->whereIDVarios($bd_desp_firmas_externos);
                $sqlExternos=$sqlExternos->getSQL_cbox();
            }else{
                $sqlExternos="";
            }

            $oForm->addField("+Firmas de Externos: ",listboxField("mas_firmas_externos",$sqlExternos,"sx_desp_firmas_externos[]","","seleccione Firmantes Externos","","","class=\"my_select_box2\" multiple ".iif($readonly,'==','readonly','disabled','')));        
        }        
        
    }
    $contenido_respuesta=$oForm->writeHTML();    
    
    if($op==1){

        if($reqVB=='true' || $reqVB==1){
            $objResponse->script('document.frm.___vb.value=1');
        }else{
            $objResponse->script('document.frm.___vb.value=0');
        }
        
        $objResponse->addAssign('divVistos','innerHTML', $contenido_respuesta);        
        
        $objResponse->script("$('.my_select_box').select2({
                                    placeholder: 'Seleccione un elemento de la lista',
                                    allowClear: true,
                                    width: '90%',
                                });
                                mySelect();
                                ");     
        
        if($bd_desp_vistos){
            $objResponse->script("$('#mas_vistos_jefes').val([$bd_desp_vistos]).trigger('change')");
        }
        
        if($bd_desp_firmas_jefes){
            $objResponse->script("$('#mas_firmas_jefes').val([$bd_desp_firmas_jefes]).trigger('change')");
        }
        
        
       return $objResponse;
    }
    else
        return $contenido_respuesta;
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
        $objResponse->addScript("document.frm.Sx_dependencia.focus()");
	return $objResponse;
    }

    if(!$proveido){
        $objResponse->addAlert('Proceso cancelado, Ingrese Proveido de AtenciÃ³n');
        $objResponse->addScript("document.frm.Sx_desp_proveido.focus()");
	return $objResponse;
    }

    $data= new manUrlv1();
    $data->removeAllPar(0);
    //derivaciÃ³n a dependencias
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
                $elemento=getDbValue("SELECT depe_id::TEXT||' '||depe_nombre FROM catalogos.dependencia WHERE depe_id=$depe_id");
                    
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

               $_SESSION["ocarrito"]->Add($data->getUrl());
            }
        }
    }
    $data= new manUrlv1();
    $data->removeAllPar(0);
    
    $objResponse->addScript("$('#Dependencia_Destino').val(null).trigger('change');");

    $objResponse->addScript("document.frm.Sx_desp_proveido.value=''");
    $objResponse->addScript("xajax_verCarrito()");
    return $objResponse;
}

function getDependencia($op,$id,$bd_depe_id,$proyecta){
    global $bd_desp_estado;
    $objResponse = new xajaxResponse();

    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");
    
    $readonly=$id?'disabled':'';

    if($proyecta=='true' || $proyecta==1) {//DOCUMENTO PROYECTADO
        
        //$bd_depe_id=$id?$bd_depe_id:'';
        if($readonly=='disabled'){
            $dependencia=new dependencia_SQLlista();
            $dependencia->whereId($bd_depe_id);    
            $dependencia->setDatos();
            
            if($bd_desp_estado==1){//abierto
                $link="<a class=\"link\" href=\"#\" onClick=\"javascript:getConfirm('Seguro de Actualizar Firma/Cargo?',function(result) {xajax_actFirmaCargo($id)})\">Actualizar Firma/Cargo</a>";
            }else{
                $link='';
            }
            $oForm->addField("PROCEDENCIA: ",$dependencia->field('depe_nombre')."&nbsp;".$link);
            $oForm->addHidden("nr_depe_id",$bd_depe_id);             
        }else{   
            $dependencia=new dependenciaJefe_SQLlista();
            $dependencia->whereID(getSession("sis_depeid"));
            $dependencia->whereHabilitado();
            $sqlDependencia=$dependencia->getSQL_cbox();
            
            $oForm->addField("PROCEDENCIA: <font color=red>*</font>",listboxField("Procedencia",$sqlDependencia,"nr_depe_id","$bd_depe_id","-- Seleccione Procedencia --","onChange=\"xajax_getDatosFirma(1,document.frm.tx_plde_id.value,document.frm.___tabl_tipodespacho.value,this.value,document.frm.___proyectado.value,document.frm.tr_tiex_id.value,'$readonly',1)\" $readonly","","class=\"my_select_box2\""));
        }
    }else{//DOCUMENTO PERSONAL
        if($id){
            $dependencia=new dependencia_SQLlista();
            $dependencia->whereId($bd_depe_id);    
            $dependencia->setDatos();
            $oForm->addField("PROCEDENCIA: ",$dependencia->field('depe_nombre'));
            $oForm->addHidden("nr_depe_id",$bd_depe_id); 
        }else{
            
            $dependencia=new dependencia_SQLlista();
            $dependencia->whereID(getSession("sis_depeid"));
            $dependencia->whereHabilitado();                        
            $sqlDependencia=$dependencia->getSQL_cbox();

            $oForm->addField("PROCEDENCIA: <font color=red>*</font>",listboxField("Procedencia",$sqlDependencia,"nr_depe_id",$bd_depe_id,"-- Seleccione Procedencia --","onChange=\"xajax_getDatosFirma(1,document.frm.tx_plde_id.value,document.frm.___tabl_tipodespacho.value,this.value,'',document.frm.tr_tiex_id.value,'$readonly',1);xajax_getSecuencia(1,document.frm.tr_tiex_id.value,document.frm.Dr_desp_fecha.value,document.frm.___tabl_tipodespacho.value,this.value,document.frm.___proyectado.value,'$readonly',1)\" ","","class=\"my_select_box2\""));        
        }
    }
    
    $contenido_respuesta=$oForm->writeHTML();    

    if($op==1){
        if($proyecta=='true' || $proyecta==1){//DOCUMENTO PROYECTADO
            $objResponse->addAssign('divNumeroDoc','innerHTML', '');
            $objResponse->script('document.frm.___proyectado.value=1');
        }else{
            $objResponse->script('document.frm.___proyectado.value=0');
            $objResponse->script("xajax_getSecuencia(1,document.frm.tr_tiex_id.value,document.frm.Dr_desp_fecha.value,document.frm.___tabl_tipodespacho.value,document.frm.nr_depe_id.value,0,'$readonly',1)");
        }

        $objResponse->addAssign('divDatosDependencia','innerHTML', $contenido_respuesta);

        $objResponse->script("$('.my_select_box').select2({
                                    placeholder: 'Seleccione un elemento de la lista',
                                    allowClear: true,
                                    width: '90%',
                                });

                                ");  
        
        return $objResponse;
    }
    else{
        return $contenido_respuesta;
    }
}


function actFirmaCargo($id)
{
    global $conn;
    $objResponse = new xajaxResponse();
    
    $despacho=new despacho_SQLlista();
    $despacho->whereID($id);
    $despacho->setDatos();
    
    if($despacho->field('desp_estado')==1){ //ABIERTO
        
            $dependencia=new dependenciaJefe_SQLlista();
            $dependencia->whereID($despacho->field('depe_id'));
            $dependencia->setDatos();
            
            if($dependencia->field('pdla_id')>0){

                    $sSql="UPDATE gestdoc.despachos 
                                    SET pdla_firma=".$dependencia->field('pdla_id').",
                                        desp_firma='".$dependencia->field('jefe')."',
                                        desp_especbreve='".$dependencia->field('pdla_especbreve')."',
                                        desp_cargo='".$dependencia->field('cargo')."'
                                 WHERE desp_id=$id
                                     AND desp_estado=1;
                                     
                            UPDATE gestdoc.despachos_firmas         
                                SET pdla_id=".$dependencia->field('pdla_id')." 
                                WHERE desp_id=$id
                                    AND defi_tipo=1; /*FIRMA DEL PRINCIPAL*/
                            ";
                    // Ejecuto el string
                    $conn->execute($sSql);
                    $error=$conn->error();
                    if($error){
                        $objResponse->addAlert($error);
                    }
            }else{
                $objResponse->addAlert('No se Hallo Jefe/Responsable en Dependencia..');
            }                    
    }else{
        $objResponse->addAlert('Estado del Registro No Habilitado..');
    }
    unset($_SESSION["ocarrito"]);
//    $destino="registroDespacho_edicionConFirma.php?id=$id";
    $objResponse->addScript("parent.content.location.reload()");
    return $objResponse;
}

function verCarrito($op=1)
{
	$objResponse = new xajaxResponse();

	$otable = new  Table("","100%",6);
	$otable->setColumnTD("ColumnBlueTD") ;
	$otable->setColumnFont("ColumnWholeFont") ;
	$otable->setFormTotalTD("FormTotalBlueTD");
	$otable->setAlternateBackTD("AlternateBackBlueTD");

	$otable->addBreak("<div align='center' style='color:#000000'><b>:: DERIVACIONES REALIZADAS ::</b></div>");
	$otable->addColumnHeader("El"); // TÃ­tulo, Ordenar?, ancho, alineaciÃ³n
	$otable->addColumnHeader("Ed"); // TÃ­tulo, Ordenar?, ancho, alineaciÃ³n        
	$otable->addColumnHeader("Dependencia/Usuario Destino",false,"50%", "L"); // TÃ­tulo, Ordenar?, ancho, alineaciÃ³n
	$otable->addColumnHeader("Proveido",false,"45%", "L"); // TÃ­tulo, Ordenar?, ancho, alineaciÃ³n
        $otable->addColumnHeader("Creado Por",false,"5%", "L"); // TÃ­tulo, Ordenar?, ancho, alineaciÃ³n
	$otable->addRow(); // adiciona la linea (TR)

	$array=$_SESSION["ocarrito"]->getArray();
	foreach($array as $arrItem) {
		$items=key($array); /* Para guardar el key del array padre */
                $depe_iddestino=$arrItem['depe_id'];
                $usua_iddestino=$arrItem['usua_id'];
                $dato=$depe_iddestino;
                
                if($usua_iddestino){
                    $dato=$dato.'_'.$usua_iddestino;
                }
                $usua_idcrea=$arrItem['usua_idcrea'];
                $dede_estado=$arrItem['dede_estado'];
                
                //si el registro esta derivado (no recibido) y el usuario que esta editando es el que lo ha creado
                if($dede_estado==2 && $usua_idcrea==getSession("sis_userid")){
                    $otable->addData("<a class=\"link\" href=\"#\" onClick=\"javascript:if(confirm('Eliminar este registro?')) {xajax_elimCarrito($items)}\"><img src=\"../../img/delete.gif\" border=0 align=absmiddle hspace=1 alt=\"Eliminar\"></a>");
                    $otable->addData("&nbsp;");
                    
//                    if($usua_iddestino){
//                        $otable->addData("<a class=\"link\" href=\"#\" onClick=\"javascript:$('#Dependencia_Destino').val(['$dato']).trigger('change');document.frm.Sx_desp_proveido.value='".$arrItem['proveido']."';xajax_btnManten(1,2,$items,'divBtnManten')\"><img src=\"../../img/editar.gif\" border=0 align=absmiddle hspace=1 alt=\"Editar\"></a>");                        
//                    }  else {
//                        $otable->addData("<a class=\"link\" href=\"#\" onClick=\"javascript:$('#Dependencia_Destino').val([$dato]).trigger('change');document.frm.Sx_desp_proveido.value='".$arrItem['proveido']."';xajax_btnManten(1,2,$items,'divBtnManten')\"><img src=\"../../img/editar.gif\" border=0 align=absmiddle hspace=1 alt=\"Editar\"></a>");                        
//                    }

                }                
                else{
                    $otable->addData("&nbsp;");
                    $otable->addData("&nbsp;");
                }


		$otable->addData($arrItem['elemento']);
		$otable->addData($arrItem['proveido']);

                $usua_idCrea=$arrItem['usua_idcrea'];
                $nameUsuaCrea=getDbValue("SELECT usua_login FROM usuario WHERE usua_id=$usua_idCrea");

                if($arrItem['tx_hijo_id']){
                    $otable->addData($nameUsuaCrea,"L","","",$arrItem['tx_hijo_id']);
                }
                else{
                    $otable->addData($nameUsuaCrea);
                }
                //$otable->addData($items);

		$otable->addRow();
		next($array); /* voy al siguiente registro del array padre */
	}

	$contenido_respuesta.=$otable->writeHTML();
	$contenido_respuesta.="<div class='BordeatablaBlue' style='width:50%;float:left' align='left'><a name=\"ancDatos0\">&nbsp;</a></div>";
	$contenido_respuesta.="<div class='BordeatablaBlue' style='width:50%;float:right' align='right'>Total Items: ".$_SESSION["ocarrito"]->getConteo()."</div>";

        if ($op==1){
            $objResponse->addScript("document.location='#ancDatos0'");
            $objResponse->addAssign('divDerivacion','innerHTML', $contenido_respuesta);
            return $objResponse;
        }else{
            return $contenido_respuesta;
        }
}

//funcion que elimina un item al carrito
function elimCarrito($id)
{
    $objResponse = new xajaxResponse();
    $_SESSION["ocarrito"]->Del($id);
    //alert($_SESSION["ocarrito"]->getConteo());
    return(verCarrito());
}

function modiCarrito($id,$deriva,$proveido)
{
    $objResponse = new xajaxResponse();
    $_SESSION["ocarrito"]->Del($id);
    return (addCarrito($deriva,$proveido));
}


function btnManten($op,$insert,$id,$div){
    $objResponse = new xajaxResponse();

    $otable = new AddTableForm();
    $otable->setLabelWidth("20%");
    $otable->setDataWidth("80%");
    $otable->setLabelTD("LabelOrangeTD");
    
    /* botones */
    $button = new Button;
    //javascript:tlist2.add('HOLA');
    if ($insert==1)
        $button->addItem(" Agregar ","xajax_addCarrito($('#Dependencia_Destino').val(),document.frm.Sx_desp_proveido.value);ocultarObj(' Agregar ',3);","content",2,'','botao','button');
    else
        $button->addItem(" Actualizar ","xajax_modiCarrito('$id',$('#Dependencia_Destino').val(),document.frm.Sx_desp_proveido.value);xajax_btnManten(1,1,0,'$div');","content",2,'','botao','button');

    $button->align('L');
    $otable->addField("<font color=red>*</font>",$button->writeHTML());
    $contenido_respuesta=$otable->writeHTML();

    if ($op==1){
        $objResponse->addAssign($div,'innerHTML', $contenido_respuesta);
        return $objResponse;
    }else{
        return $contenido_respuesta;
    }

}

function getSecuencia($op,$tipoExpediente,$fechaDoc,$tipoDespacho,$depe_id,$proyectado,$readonly,$ejecutar_getPara){
        global $conn,$id,$bd_desp_numero,$bd_desp_siglas,$num_documento,$tiex_descripcion,$bd_desp_estado,$habilita_edicion;

        $objResponse = new xajaxResponse();
        //$objResponse->addAlert($tipoExpediente);
        //si no se ha seleccionado tipo de expediente
        if(!$tipoExpediente) {
            if($op==1){
                return $objResponse;
            }else{
                return;
            }
        }

        $oForm = new AddTableForm();
        $oForm->setLabelWidth("20%");
        $oForm->setDataWidth("80%");
        $annoExplode=explode("-",$fechaDoc);
        $anno=$annoExplode[0];
        
        $td_secuencia='';

        switch ($tipoDespacho){
                case 140://institucional
                    $td=new clsTipExp_SQLlista();
                    $td->whereID($tipoExpediente);
                    $td->setDatos();                            
                    $td_secuencia=$td->field('tiex_secuencia');
                    //SI ES SECUENCIA AUTOMATICA
                    if($td_secuencia){
                        if(!$id){
                            $siglas=new dependencia_SQLlista();
                            $siglas->whereID($depe_id);
                            $siglas->setDatos();

                            //si el expediente es tipo resolucion
                            if($td->field('tiex_tiporesolucion')==1){
                                $bd_desp_siglas=$siglas->field('depe_siglasresolucion');
                            }else{
                                $bd_desp_siglas=$siglas->field('depe_siglasdoc');
                            }


                            $secuencia="gestdoc.corr_exp_".$td_secuencia.'_'.$anno.'_'.$depe_id.'_'.$tipoExpediente;

                            $bd_desp_numero=$conn->currval($secuencia);

                            if($bd_desp_numero==0){ /* Si la secuencia no estÃ¡ creada */
                                $bd_desp_numero=1; /* Asigno el nÃºmero 1 */
                            }
                            $bd_desp_numero=str_pad($bd_desp_numero,6,'0',STR_PAD_LEFT);

                            $num_documento="$bd_desp_numero-$anno-$bd_desp_siglas";

                        }

                        if($bd_desp_numero==1 && !$id){//SI ES UNO
                            $oForm->addField("N&uacute;mero-A&ntilde;o-Siglas: <font color=red>*</font>",numField("N&uacute;mero","nx_desp_numero",$bd_desp_numero,6,6,0)
                                    ."<font size=\"-1\">-$anno-$bd_desp_siglas</font>"
                                    );
                        }else{
                            $oForm->addField("N&uacute;mero-A&ntilde;o-Siglas: <font color=red>*</font>","<font size=\"-1\">$num_documento</font>");                        
                            $oForm->addHidden('nx_desp_numero',$bd_desp_numero,"N&uacute;mero");                            
                        }
                        
                        $oForm->addHidden('nx_anno_nume_doc',$anno);
                        $oForm->addHidden('Sx_desp_siglas',$bd_desp_siglas,"Siglas");
                        $oForm->addHidden('___td_secuencia',$td_secuencia);

                    }else{//SI ES SECUENCIA MANUAL
                            if($bd_desp_estado==2){
                                $oForm->addField("N&uacute;mero-A&ntilde;o: ","<font size=\"-1\">$bd_desp_numero-$anno</font>");
                            }else{    
                                $oForm->addField("N&uacute;mero-A&ntilde;o: ",textField("Numero","Sr_desp_numero",$bd_desp_numero,30,50)
                                        ."<font size=\"-1\">-$anno</font>"
                                        );
                                $oForm->addHidden('nx_anno_nume_doc',$anno);//guardo el numero para q funcione en las actualizacione
                            }
                    }

                    break;

                case 141://personal
                    if(!$id){
                        $siglas=new dependencia_SQLlista();
                        $siglas->whereID($depe_id);
                        $siglas->setDatos();
                        $bd_desp_siglas=$siglas->field('depe_siglasdoc');

                        $siglas=new clsUsers_SQLlista();
                        $siglas->whereID(getSession("sis_userid"));
                        $siglas->setDatos();
                        $siglasPers=$siglas->field('usua_iniciales');

                        $td=new clsTipExp_SQLlista();
                        $td->whereID($tipoExpediente);
                        $td->setDatos();
                        $td_secuencia=$td->field('tiex_secuencia');

                        $secuencia="gestdoc.corr_exp_".$td_secuencia.'_'.$anno.'_'.$tipoExpediente.'_'.getSession("sis_userid");

                        //$objResponse->addAlert($secuencia);

                        $bd_desp_numero=$conn->currval($secuencia);

                        if($bd_desp_numero==0){ /* Si la secuencia no estÃ¡ creada */
                            $bd_desp_numero=1; /* Asigno el nÃºmero 1 */
                        }
                        $bd_desp_numero=str_pad($bd_desp_numero,6,'0',STR_PAD_LEFT);
                        $bd_desp_siglas=$bd_desp_siglas.'-'.$siglasPers;
                        $num_documento="$bd_desp_numero-$anno-$bd_desp_siglas";
                    }
                    
                    if($bd_desp_numero==1 && !$id){//SI ES UNO
                            $oForm->addField("N&uacute;mero-A&ntilde;o-Siglas: ",numField("N&uacute;mero","nx_desp_numero",$bd_desp_numero,6,6,0)
                                    ."<font size=\"-1\">-$anno-$bd_desp_siglas</font>"
                                    );
                    }else{            
                        $oForm->addField("N&uacute;mero-A&ntilde;o-Siglas: ","<font size=\"-1\">$num_documento</font>");
                        $oForm->addHidden('nx_desp_numero',$bd_desp_numero,"N&uacute;mero");
                    }
                    $oForm->addHidden('nx_anno_nume_doc',$anno);
                    $oForm->addHidden('Sx_desp_siglas',$bd_desp_siglas,"Siglas");
                    $oForm->addHidden('___td_secuencia',$td_secuencia);                    

                    if($tipoExpediente==14){//SOLICITUD SIMPLE
                        $objResponse->addScript("$('#divMensajejefeInmediato').hide()");
                        
                    }else{
                        $objResponse->addScript("$('#divMensajejefeInmediato').show()");
                    }
                    
                    break;

                case 142://otras entidades
                    if( ($id && $habilita_edicion==0) || $readonly=='readonly' ) {
                        $oForm->addField("N&uacute;mero-A&ntilde;o: ","<font size=\"-1\">$bd_desp_numero-$anno</font>");
                    }else{   
                        $oForm->addField("N&uacute;mero-A&ntilde;o-Siglas: ",numField("N&uacute;mero","nx_desp_numero",$bd_desp_numero,6,6,0)
                                ."<font size=\"-1\">-$anno-</font>"
                                .textField("Siglas","Sx_desp_siglas",$bd_desp_siglas,30,30)
                                );
                        $oForm->addHidden('nx_anno_nume_doc',$anno);//guardo el numero para q funcione en las actualizaciones
                        $oForm->addHidden('___td_secuencia','');
                    }
                    break;

                case 999://proyectado
                    break;
                
                default:
                    if($op==1){
                        $objResponse->addAlert('Proceso cancelado, Seleccione opciÃ³n'.$tipoDespacho);
                        
                        return $objResponse;
                    }else{
			$oForm->addBreak("!NO SE ENCONTRARON DATOS...!!");
                    }

                    break;
           }



        $contenido_respuesta=$oForm->writeHTML();

        if($op==1){
            $objResponse->addAssign('divNumeroDoc','innerHTML', $contenido_respuesta);
            
            //if(inlist($tipoDespacho,'140,141')){ //PERSONAL
                if($ejecutar_getPara==1){
                    $objResponse->script("xajax_getPara(1,document.frm.hx_solicita.value,document.frm.nr_depe_id.value,document.frm.___tabl_tipodespacho.value,'',document.frm.xxx_plantilla_destinatario.value)");
                }
            //}
            $objResponse->script("xajax_getEditor(1,'$tipoExpediente',CKEDITOR.instances['K__desp_contenido'].getData(),'$tipoDespacho','$readonly','divEditor')");            

            return $objResponse;
        }else{
            return $contenido_respuesta;
        }
}

//function getEntidad($codigo,$entidad_origen,$firma,$cargo)
//{
//    global $conn;
//    $objResponse = new xajaxResponse();
//    $sql="SELECT desp_entidad_origen,desp_firma,desp_cargo
//                FROM despachos
//                WHERE desp_codigo='$codigo'
//                ORDER BY desp_id DESC
//                LIMIT 1 ";
//
//     $rs = new query($conn, $sql);
//     if($rs->numrows()>0){
//            //$objResponse->addAlert($sql);
//            $rs->getrow();
//            $desp_entidad_origen=$rs->field('desp_entidad_origen');
//            $desp_firma=$rs->field('desp_firma');
//            $desp_cargo=$rs->field('desp_cargo');
//
//            $objResponse->addScript("document.frm.Sx_desp_entidad_origen.value='".$desp_entidad_origen."'");
//            $objResponse->addScript("document.frm.Sr_desp_firma.value='".$desp_firma."'");
//            $objResponse->addScript("document.frm.Sx_desp_cargo.value='".$desp_cargo."'");
//     }else{
//            $objResponse->addScript("consultar_RUC_DNI('$codigo')");
//     }
//
//    //$objResponse->addAlert($codigo);
//
//    return $objResponse;
//}

function getDocJudicial($op,$tipoExpediente,$cuenta_recibidos,$divName){
        global $conn,$bd_desp_exp_legal,$bd_desp_demandante,$bd_desp_demandado,
               $habilita_edicion,$bd_desp_resolucion,$bd_tabl_tipodespacho,$bd_exle_id;

        $objResponse = new xajaxResponse();
        //$objResponse->addAlert($tipoExpediente);

        //si no se ha seleccionado tipo de expediente
        if(!$tipoExpediente) {
            if($op==1){
                return $objResponse;
            }else{
                return;
            }
        }

        $oForm = new AddTableForm();
        $oForm->setLabelWidth("20%");
        $oForm->setDataWidth("80%");

        $td=new clsTipExp_SQLlista();
        $td->whereID($tipoExpediente);
        $td->setDatos();
        if($td->field('tiex_tipojudicial')==1){
            $oForm->addBreak("<b>Datos Judiciales</b>");    
//            $sqlLegal="SELECT a.exle_id,
//                        LPAD(a.exle_numero::TEXT,6,'0')||'-'||LPAD(exle_anno::TEXT,4,'0')||COALESCE('-'||exle_siglas,'') AS exle_expediente 
//                        FROM gestleg.expediente_legal a
//                    ORDER BY exle_anno DESC,exle_numero ";
//            $oForm->addField("Expediente Legal:",listboxField("Expediente Legal",$sqlLegal,"tx_exle_id",$bd_exle_id,"-- Seleccione Expediente --"));
            
            if($cuenta_recibidos>0 && $habilita_edicion==0){
                $oForm->addField("N&ordm; Exp.Legal: ",$bd_desp_exp_legal);
                $oForm->addField("Demandante: ",$bd_desp_demandante);
                $oForm->addField("Demandado: ",$bd_desp_demandado);
                $oForm->addField("Resoluci&oacute;n ",$bd_desp_resolucion);
            }else{
                $oForm->addField("N&ordm; Exp.Legal: ",textField("No Exp.Legal","Sx_desp_exp_legal",$bd_desp_exp_legal,50,50));
                $oForm->addField("Demandante: ",textField("Demandante","Sx_desp_demandante",$bd_desp_demandante,80,80));
                $oForm->addField("Demandado: ",textField("Demandado","Sx_desp_demandado",$bd_desp_demandado,80,80));
                $oForm->addField("Resoluci&oacute;n ",textField("ResoluciÃ³n","Sx_desp_resolucion",$bd_desp_resolucion,80,80));
            }
        }

        $contenido_respuesta=$oForm->writeHTML();
	$objResponse->addAssign($divName,'innerHTML', $contenido_respuesta);

        if($op==1)
            return $objResponse;
	else
            return $contenido_respuesta;
}


function getFecha($op,$bd_desp_fecha,$tiex_id,$bd_tabl_tipodespacho,$readonly,$divName){

        $objResponse = new xajaxResponse();

        $oForm = new AddTableForm();
        $oForm->setLabelWidth("20%");
        $oForm->setDataWidth("80%");

        $td=new clsTipExp_SQLlista();
        $td->whereID($tiex_id);
        $td->setDatos();
        $td_secuencia=$td->field('tiex_secuencia');

        if(strtoupper($readonly)=='READONLY' || ($td_secuencia && !inlist($bd_tabl_tipodespacho,'142'))){
            $oForm->addField("Fecha de Documento: <font color=red>*</font>",dtos($bd_desp_fecha));
            $oForm->addHidden("Dr_desp_fecha",$bd_desp_fecha); // Prioridad de Atencion NORMAL
        }else{    
            $oForm->addField("Fecha de Documento: <font color=red>*</font>",dateField2("Fecha de Documento","Dr_desp_fecha","$bd_desp_fecha","onChange=xajax_getSecuencia(1,document.frm.tr_tiex_id.value,this.value,document.frm.___tabl_tipodespacho.value,document.frm.nr_depe_id.value,document.frm.___proyectado.value,'$readonly',1)"));
        }

        $contenido_respuesta=$oForm->writeHTML();

        if($op==1){
            $objResponse->addAssign($divName,'innerHTML', $contenido_respuesta);            
            return $objResponse;
        }else{
            return $contenido_respuesta;
        }
}


function getRequisitos($proc_id,$readonly){
    global $conn;
    
    $objResponse = new xajaxResponse();    
    
    $procedimiento = new procedimiento_SQLlista();
    $procedimiento->whereID($proc_id);
    $procedimiento->setDatos();
    
    $contenido="";
    $objResponse->addAssign('divContenido','innerHTML', $contenido);
    
    return $objResponse;
}

function guardar($formdata,$regSeleccionadosExp,$regSeleccionadosId,$desp_contenido)
{
	global $conn,$param;

	$objResponse = new xajaxResponse();
	$objResponse->setCharEncoding('utf-8');
                
	$usua_id=getSession("sis_userid"); /* Id del usuario que graba el registro */
        //$depe_id=getSession("sis_depeid");

        //asigno la variable de ediciÃ³n
        $edita=$formdata['f_id'];
        $padre_id=$edita; //variable creada solo para el update;
        
        if($edita){
            $dspacho=new despacho_SQLlista();
            $dspacho->whereID($edita);
            $dspacho->setDatos();
            $ok=1;
            if($dspacho->field('desp_estado')==2){
                $objResponse->addAlert('No es posible guardar los datos porque el registro ha sido Cerrado, este Documento sera recargado...');
                $ok=0;                    
            }elseif($dspacho->field('desp_cont_firmas')>0 && $dspacho->field('desp_cont_firmados')>0){
                $objResponse->addAlert('No es posible guardar los datos porque el registro ha sido firmado, este Documento sera recargado...');
                $ok=0;    
            }
            if($ok==0){
                unset($_SESSION["ocarrito"]);
                $destino="registroDespacho_edicionConFirma.php?id=$padre_id";
                $objResponse->addRedirect($destino);
                return $objResponse;
            }
        }
	/* Recibo campos */
        $depe_id=$formdata['nr_depe_id'];
        
	$tabl_tipodespacho=$formdata['___tabl_tipodespacho'];		/*campo id de la tabla en caso de modificacion*/
        $desp_fecha=$formdata['Dr_desp_fecha'];
        $plde_id=$formdata['tx_plde_id'];
        $tiex_id=$formdata['tr_tiex_id'];
        $desp_asunto=$formdata['Er_desp_asunto'];
        $desp_referencia=$formdata['Ex_desp_referencia'];
        $desp_firma=$formdata['Sr_desp_firma'];
        $desp_cargo=$formdata['Sx_desp_cargo'];
        $desp_especbreve=$formdata['Sx_desp_especbreve'];
        $tabl_modorecepcion=$formdata['tr_tabl_modorecepcion'];
        $desp_folios=$formdata['nr_desp_folios'];
        $desp_proyectadopor=$formdata['Sx_desp_proyectadopor'];
        //$desp_trelacionado=$formdata['nx_desp_trelacionado'];
        $desp_expediente=$formdata['nx_desp_expediente'];
        $desp_notas=$formdata['Sx_desp_notas'];

        $bd_exle_id=$formdata['tx_exle_id'];
        $desp_exp_legal=$formdata['Sx_desp_exp_legal'];
        $desp_demandante=$formdata['Sx_desp_demandante'];
        $desp_demandado=$formdata['Sx_desp_demandado'];
        $desp_resolucion=$formdata['Sx_desp_resolucion'];
        $si_adjuntar=$formdata['hx_asjuntar'];
        $name_file = $formdata['exap_adjunto'];
        
        $desp_proyectado=$formdata['___proyectado'];
        
        $desp_vb=$formdata['___vb'];
        $desp_exterior=$formdata['___exterior'];
        $nr_pdla_firma=$formdata['nr_pdla_firma'];
        
        $desp_para_depe_id=implode(',',$formdata['sr_desp_para_depe_id']);
        $desp_para_pdla_id=implode(',',$formdata['sr_desp_para_pdla_id']);
        $bd_desp_para_grupo=$formdata['sr_desp_para_grupo'];

        $desp_vistos=implode(',',$formdata['sx_desp_vistos']);
        if($desp_vb==1 && $desp_vistos){
            $desp_vistos=implode(",", $formdata['sx_desp_vistos']);
        }

        $desp_firmas_jefes=implode(',',$formdata['sx_desp_firmas_jefes']);
        if($desp_vb==1 && $desp_firmas_jefes){
            $desp_firmas_jefes=implode(",", $formdata['sx_desp_firmas_jefes']);
        }
        
        $desp_vistos_empleados=implode(',',$formdata['sx_desp_vistos_empleados']);
        if($desp_vb==1 && $desp_vistos_empleados){
            $desp_vistos_empleados=implode(',',$formdata['sx_desp_vistos_empleados']);
        }
        
        $desp_firmas_externos=implode(',',$formdata['sx_desp_firmas_externos']);
        if($desp_vb==1 && $desp_firmas_externos){
            $desp_firmas_externos=implode(',',$formdata['sx_desp_firmas_externos']);
        }
        
        $prat_id=$formdata['tx_prat_id'];
        $prov_id=$formdata['tx_prov_id'];
        $desp_entidad_origen=$formdata['_DummySx_proveedor'];
        
        $regRelacionado_exp=$formdata['hx_relacionado_exp'];
        $regRelacionado_id=$formdata['hx_relacionado_id'];
        //$prov_id=$prov_id?$prov_id:null;

        $annoExplode=explode("-",$desp_fecha);
        $anno=$annoExplode[0];
        $td_secuencia=$formdata['___td_secuencia'];
        
        if($td_secuencia){ //SECUENCIA AUTOMATICA
            $desp_secuencia_automatica=1; 
        }else{
            $desp_secuencia_automatica=0;
        }
        
        $desp_numero=$formdata['nx_desp_numero'];
        if(!$desp_numero){
            $desp_numero=$formdata['Sr_desp_numero'];
        }

        $depe_id_proyectado=$formdata['nx_depe_id_proyectado'];
        
        $desp_ocultar_editor=$formdata['xxx_ocultar_editor'];

        $tabl_tipopersona=$formdata['tx_tabl_tipopersona'];
        $ubig_id=$formdata['sx_ubig_id'];
        
        switch($tabl_tipodespacho){
            case 140://institucional
                if($edita) break;

//                $secuencia="gestdoc.corr_exp_".$td_secuencia.'_'.$anno.'_'.$depe_id.'_'.$tiex_id;
//                $numDocum=$conn->currval($secuencia);
//                if($numDocum==0){ /* Si la secuencia no estÃ¡ creada */
//                    $conn->nextid($secuencia); /* Creo la secuencia */
//                    $numDocum=1; /* Asigno el nÃºmero 1 */
//                }
//                $desp_numero=$numDocum;

                $desp_anno=$anno;
                $desp_siglas=$formdata['Sx_desp_siglas'];
                break;

            case 141://personal
                if($edita) break;

//                $secuencia="gestdoc.corr_exp_".$td_secuencia.'_'.$anno.'_'.$tiex_id.'_'.$usua_id;
//
//                $numDocum=$conn->currval($secuencia);
//                if($numDocum==0){ /* Si la secuencia no estÃ¡ creada */
//                    $conn->nextid($secuencia); /* Creo la secuencia */
//                    $numDocum=1; /* Asigno el nÃºmero 1 */
//                }
//                $desp_numero=$numDocum;
                
                $desp_anno=$anno;
                $desp_siglas=$formdata['Sx_desp_siglas'];
                break;

            case 142://otras entidades
                //$desp_descripaux=$formdata['Sx_desp_descripaux'];
//                if(!$prov_id){
//                    $objResponse->addAlert('Debe seleccionar Entidad de Procedencia, pulse en el boton buscar en campo RUC/DNI');
//                    $objResponse->addScript('document.frm.Sx_proveedor.focus()');
//                    return $objResponse;                    
//                }
                if(!$desp_entidad_origen){
                    $objResponse->addAlert('Debe registrar Entidad de Procedencia...');
                    $objResponse->addScript('document.frm._DummySx_proveedor.focus()');
                    return $objResponse;                    
                }
                
                $proc_id=$formdata['tr_proc_id'];                
                $desp_codigo=$formdata['Sx_proveedor'];
                $desp_direccion=$formdata['Sx_desp_direccion'];
                $desp_anno=$formdata['nx_anno_nume_doc'];
                $desp_siglas=$formdata['Sx_desp_siglas'];
                
                $desp_telefono=$formdata['Sx_desp_telefono'];
                $desp_email=$formdata['cx_desp_email'];
                break;
        }

	/********* INICIO PROCESO DE GRABACION EN EL PADRE *********/
	$conn->begin();

	// objeto para instanciar la clase sql
	$setTable='despachos';
	$setKey='desp_id';
	$typeKey='Number';

	$sql = new UpdateSQL();

	$sql->setTable($setTable);
	$sql->setKey($setKey,$padre_id,$typeKey);
        if(!$edita){
            $sql->setAction("INSERT"); /* OperaciÃ³n */
            $sql->addField('depe_id',$depe_id, "Number");
            $sql->addField('usua_id',$usua_id, "Number");
            $sql->addField('tiex_id',$tiex_id, "Number");
            $sql->addField('plde_id',$plde_id, "Number");
            $sql->addField('desp_secuencia_automatica',$desp_secuencia_automatica, "Number");

            //if( $desp_secuencia_automatica==0 ){//SECUENCIA MANUAL
                $sql->addField('desp_numero',$desp_numero, "String");
            //}
 
            //$sql->addField('desp_anno',$desp_anno, "Number");
            $sql->addField('desp_siglas',$desp_siglas, "String");
            $sql->addField('desp_procesador',1, "Number");//DESDE NUEVO CON IMA
            $sql->addField('desp_proyectado',$desp_proyectado, "Number");
            
            if( $desp_proyectado==1 ){
                $depe_idproyecta=getSession("sis_depeid");
                $sql->addField('depe_idproyecta',$depe_idproyecta, "Number");            
            }
            
            if( $proc_id ){
                $sql->addField('proc_id',$proc_id, "Number");            
            }
            
            $sql->addField("desp_actualfecha", "NOW()", "String");
            $sql->addField("desp_actualusua", getSession("sis_userid"), "String");           
        }else{ //SI EDITA
            $sql->setAction("UPDATE"); /* OperaciÃ³n */
            $sql->addField("desp_actualfecha", "NOW()", "String");
            $sql->addField("desp_actualusua", getSession("sis_userid"), "String");

            if($desp_secuencia_automatica==0){//SECUENCIA MANUAL
                $sql->addField('tiex_id',$tiex_id, "Number");
                $sql->addField('desp_numero',$desp_numero, "String");
                //$sql->addField('desp_anno',$desp_anno, "Number");
                $sql->addField('desp_siglas',$desp_siglas, "String");
            }
        }
        
        $sql->addField('desp_vb',$desp_vb, "Number");
        $sql->addField('desp_exterior',$desp_exterior, "Number");            
        
        $sql->addField('tabl_tipodespacho',$tabl_tipodespacho, "Number");
        $sql->addField('desp_fecha',$desp_fecha, "String");

        $sql->addField('desp_para_depe_id',$desp_para_depe_id, "String");
        $sql->addField('desp_para_pdla_id',$desp_para_pdla_id, "String");
        $sql->addField('desp_para_grupo',$bd_desp_para_grupo, "Number");
        
        
        if($desp_vistos){
            $sql->addField('desp_vistos',$desp_vistos, "String");
        }else{
            $sql->addField('desp_vistos','', "String");
        }


        if($desp_firmas_jefes){
            $sql->addField('desp_firmas_jefes',$desp_firmas_jefes, "String");
        }else{
            $sql->addField('desp_firmas_jefes','', "String");
        }
        
        if($desp_vistos_empleados){
            $sql->addField('desp_vistos_empleados',$desp_vistos_empleados, "String");            
        }else{
            $sql->addField('desp_vistos_empleados','', "String");
        }
        
        if($desp_firmas_externos){
            $sql->addField('desp_firmas_externos',$desp_firmas_externos, "String");            
        }else{
            $sql->addField('desp_firmas_externos','', "String");
        }
        
        $sql->addField('depe_id_proyectado',$depe_id_proyectado, "Number");
        
        if($desp_exterior==1){//SI ES PARA UN JEFE
//            $dependenciaJefe=new dependenciaJefe_SQLlista();
//            $dependenciaJefe->whereID($desp_para_depe_id);
//            $dependenciaJefe->setDatos();
//                   
//            $desp_para_destino=$dependenciaJefe->field('especialidad').' '.$dependenciaJefe->field('jefe');
//            $desp_para_cargo=$dependenciaJefe->field('cargo');
//            $desp_para_dependencia=$dependenciaJefe->field('depe_nombre');
        }elseif($desp_exterior==2){//SI ES PARA UN TRABAJADOR
//            $dependenciaEmpleado=new clsDatosLaborales_SQLlista();
//            $dependenciaEmpleado->whereID($desp_para_pdla_id);
//            $dependenciaEmpleado->setDatos();
//                   
//            $desp_para_destino=$dependenciaEmpleado->field('especialidad').' '.$dependenciaEmpleado->field('empleado');
//            $desp_para_cargo=$dependenciaEmpleado->field('pdla_cargofuncional_ext
//            ');
//            $desp_para_dependencia=$dependenciaEmpleado->field('depe_nombre');
        
        }else{
            $desp_para_destino=$formdata['Er_desp_para_destino'];
            $desp_para_cargo=$formdata['Ex_desp_para_cargo'];
            $desp_para_dependencia=$formdata['Ex_desp_para_dependencia'];
        }
        $sql->addField('desp_para_destino',  strtoupper($desp_para_destino), "String");    
        $sql->addField('desp_para_cargo',  strtoupper($desp_para_cargo), "String");    
        $sql->addField('desp_para_dependencia',  strtoupper($desp_para_dependencia), "String");    
        
        $sql->addField('pdla_firma',$nr_pdla_firma, "Number");                
        $sql->addField('desp_asunto',  strtoupper($desp_asunto), "String");
        $sql->addField('desp_referencia',  strtoupper($desp_referencia), "String");
        $sql->addField('desp_firma',$desp_firma, "String");
        $sql->addField('desp_cargo',$desp_cargo, "String");        
        $sql->addField('desp_especbreve',$desp_especbreve, "String");
        $sql->addField('tabl_modorecepcion',$tabl_modorecepcion, "Number");
        $sql->addField('desp_folios',$desp_folios, "Number");
        
        $sql->addField('desp_proyectadopor',$desp_proyectadopor, "String");
        //$sql->addField('desp_trelacionado',$desp_trelacionado, "Number");
        $sql->addField('desp_expediente',$desp_expediente, "Number");
        $sql->addField('desp_notas',$desp_notas, "String");
        $sql->addField('desp_contenido',$desp_contenido, "String");
        
        if($si_adjuntar==1 && $regSeleccionadosExp){
            $sql->addField('desp_adjuntados_exp',$regSeleccionadosExp, "String"); 
            $sql->addField('desp_adjuntados_id' ,$regSeleccionadosId, "String"); 
            $sql->addField('desp_adjuntados', 1 , "Number");
        }else{
            if($si_adjuntar==1 && $regRelacionado_id){//SI SE HIZO BUSQUEDA
                $sql->addField('desp_adjuntados_exp',$regRelacionado_exp, "String"); 
                $sql->addField('desp_adjuntados_id' ,$regRelacionado_id, "String"); 
                $sql->addField('desp_adjuntados', 1 , "Number");
                $regSeleccionadosId=$regRelacionado_id;
            }else{
                $sql->addField('desp_adjuntados_exp',null, "String"); 
                $sql->addField('desp_adjuntados_id' ,null, "String"); 
                $sql->addField('desp_adjuntados', 0 , "Number");
            }
        }
        
        $sql->addField('exle_id',$bd_exle_id, "Number");
        
        $sql->addField('desp_exp_legal',$desp_exp_legal, "String");
        $sql->addField('desp_demandante',strtoupper($desp_demandante), "String");
        $sql->addField('desp_demandado',strtoupper($desp_demandado), "String");
        $sql->addField('desp_resolucion',strtoupper($desp_resolucion), "String");
        
        $sql->addField('prat_id',$prat_id, "Number");

        //$sql->addField('prov_id',$prov_id, "Number");
        //$sql->addField('desp_descripaux',$desp_descripaux,"String");

        if($tabl_tipodespacho==142){ //OTRAS ENTIDADES            
            $sql->addField('prov_id',$prov_id, "Number");
            $sql->addField('tabl_tipopersona',$tabl_tipopersona, "Number");
             
            $sql->addField('desp_codigo',$desp_codigo, "String");
            $sql->addField('desp_entidad_origen',strtoupper($desp_entidad_origen), "String");
            $sql->addField('desp_direccion',strtoupper($desp_direccion), "String");
            $sql->addField('ubig_id',$ubig_id, "String");
            $sql->addField('desp_telefono',$desp_telefono, "String");
            $sql->addField('desp_email',$desp_email, "String");            
        }

	$sql=$sql->getSQL()." RETURNING desp_id::text||'_'||TO_CHAR(desp_fregistro,'DD/MM/YYYY HH:MI:SS AM')";

	//$sql= $sql;
	$return=$conn->execute($sql); //obtengo el id del registro generado
        $nvoReturn=explode('_',$return);

        $padre_id=$nvoReturn[0];
        $ff_hh=$nvoReturn[1];

        //$objResponse->addAlert($padre_id);

	$error=$conn->error();
	if($error){
            $conn-> rollback();
            $objResponse->addAlert($error);
	 }
	/********* FIN PROCESO DE GRABACION EN EL PADRE *********/
	else{

	}

	if($error){
                $conn-> rollback();
                $objResponse->addAlert($error);            
		return $objResponse;
	}else{
            if($regSeleccionadosId){
                
                if($si_adjuntar==1){
                    $sql="UPDATE despachos_derivaciones
                            SET desp_adjuntadoid=$padre_id
                            WHERE desp_id!=$padre_id 
                                AND dede_id IN ($regSeleccionadosId) ";
                }else{
                    $sql="UPDATE despachos_derivaciones
                                SET desp_adjuntadoid=NULL
                                WHERE desp_adjuntadoid=$padre_id; ";
                }
                //alert($sql);
                $conn->execute($sql);
                $error=$conn->error();
                if($error){
                    $conn-> rollback();
                    $objResponse->addAlert($error);
                }
            }            
            $conn->commit(); /* termino transacciÃ³n */
                        
            if( !$edita ){ $op=1; } else { $op=0; }

            if($tabl_tipodespacho==142){//OTRAS ENTIDADES
                if($name_file){
                    $objResponse->addScript("xajax_guardarDerivaciones('$padre_id','$op',xajax.getFormValues('frm'),'$regSeleccionadosId',0)");
                    $objResponse->addScript("upload('$padre_id','$op')");
                }else{
                    $objResponse->addScript("xajax_guardarDerivaciones('$padre_id','$op',xajax.getFormValues('frm'),'$regSeleccionadosId',1)");                    
                }
            }else{
                unset($_SESSION["ocarrito"]);
                //si adjunto archivo                
                
                
                if($name_file){                    
                    $objResponse->addScript("upload('$padre_id','$op')");
                }else{                
                    $destino="registroDespacho_edicionConFirma.php?id=$padre_id&op=$op";
                    $objResponse->addRedirect($destino);
                }
            }
	}
        //$objResponse->addAlert('Nuevo Despacho:'.str_pad($desp_numero,8,'0',STR_PAD_LEFT));

	return $objResponse;
}

function guardarDerivaciones($id,$op,$formdata,$regSeleccionadosId,$reload){
    global $conn;
    $objResponse = new xajaxResponse();
    $objResponse->setCharEncoding('utf-8');
    $padre_id=$id;  
    $si_adjuntar=$formdata['hx_asjuntar'];

    /* Recibo campos */
    $depe_id=$formdata['nr_depe_id'];

        
    $conn->begin();
    /********* INICIO PROCESO DE GRABACION EN EL HIJO *********/
    $nvasDerivaciones='';
    $array=$_SESSION["ocarrito"]->getArray();
    foreach($array as $arrItem){
            //$objResponse->addAlert($arrItem['tx_hijo_id']);
            if($arrItem['tx_hijo_id']) continue; /* Si es EdiciÃ³n, regresa y no ejecuta el update  */

            $depe_iddestino=$arrItem['depe_id'];
            $usua_iddestino=$arrItem['usua_id'];
            $dede_proveido=$arrItem['proveido'];

            $depe_idorigen=$depe_id; //getSession("sis_depeid");
            $usua_idorigen=getSession("sis_userid");
            $usua_idcrea=getSession("sis_userid"); /* Id del usuario que graba el registro */

            // objeto para instanciar la clase sql
            $setTable='despachos_derivaciones';
            $setKey='dede_id';
            $typeKey='Number';
            $sql = new UpdateSQL();
            $sql->setTable($setTable);
            $sql->setKey($setKey,$hijo_id,$typeKey);
            $sql->setAction("INSERT"); /* OperaciÃ³n */

            /* Campos */
            $sql->addField('depe_idorigen', $depe_idorigen, "Number");
            $sql->addField('usua_idorigen', $usua_idorigen, "Number");

            $sql->addField('desp_id', $padre_id, "Number");

            $sql->addField('depe_iddestino', $depe_iddestino, "Number");
            $sql->addField('usua_iddestino', $usua_iddestino, "Number");
            $sql->addField('dede_proveido', $dede_proveido, "String");

            $sql->addField('usua_idcrea', $usua_idcrea, "Number");

            $sql=$sql->getSQL()." RETURNING dede_id::text ";
            $return=$conn->execute($sql); //obtengo el id del registro generado

            $error=$conn->error();
            if($error){
                    $conn-> rollback();
                    $objResponse->addAlert($error);
                    break;
             }
    }
    /********* FIN PROCESO DE GRABACION EN EL HIJO *********/
    if(!$error){
            /********* FIN ACTUALIZA LOS NUEVOS REGISTROS DE DERIVACIONES GENERADOS *********/

                    /*-------- INICIO PROCESO DE ELIMINACION EN EL HIJO -------*/
                    $arLista_elimina=$_SESSION["ocarrito"]->getArrayEliminados();
                    if (is_array($arLista_elimina)) { /* Si existe array de registros eliminados */
                             $lista_elimina = implode(",",$arLista_elimina);

                            if(strtolower($typeKey)=='string'){
                                    /* debido a que el campo clave es char */
                                $lista_elimina=ereg_replace(",","','",$lista_elimina);
                            }

                            $setTable='despachos_derivaciones';
                            $setKey='dede_id';
                            $typeKey='Number';

                            /* Sql a ejecutar */
                            $sql="DELETE FROM $setTable ";
                            $sql.=" WHERE $setKey ";
                            $sql.="     IN (".iif(strtolower($typeKey),"==","string","'","").$lista_elimina.iif(strtolower($typeKey),"==","string","'","").") ";
                            $sql.="     AND usua_idcrea=".getSession("sis_userid");

                            //$objResponse->addAlert($sql);
                            $conn->execute($sql);
                            $error=$conn->error();
                            if($error){
                                     $conn-> rollback();
                                     $objResponse->addAlert($error);
                             }
                    }
                    /*----------- FIN PROCESO DE ELIMINACION EN EL HIJO -----------*/
    }
    $conn->commit();
    
    if($reload==1){
        unset($_SESSION["ocarrito"]);
        $destino="registroDespacho_edicionConFirma.php?id=$padre_id&op=$op";
        //$destino='registroDespacho_buscar.php?clear=1&busEmpty=1';
        $objResponse->addRedirect($destino);        
    }
    return $objResponse;
}
                

function upload_eFirma($id,$defi_id,$NameDiv)
{
    $objResponse = new xajaxResponse();

    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");

    $oForm->addField("Archivo: ",fileField2("Archivo","exap_adjunto" ,"",60,"onchange=validaextension(this,'PDF,ZIP,DOC,XLS,DOCX,XLSX,PPT,PPTX')","uno"));
            
    $button = new Button;
    //$button->addItem(" Cerrar ","","",0,0,"","button-modal");
    $button->addItem(" Subir e-Firma ","javascript:if(document.frm.exap_adjunto.value==''){alert('Campo Archivo es obligatorio');}else{ocultarObj('id_subir',10);afterUpload_eFirma('$id','$defi_id');}","content",0,0,"","btn-bootstrap","","id_subir");
            
    $contenido_respuesta=$oForm->writeHTML();
    $contenido_respuesta.="<div class=\"modal-footer\">";
    $contenido_respuesta.=$button->writeHTML();
    $contenido_respuesta.="</div>";
    $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);
    $objResponse->addScript("$('#password').focus();");
    return $objResponse;
}

function getEditor($op,$tiex_id,$bd_desp_contenido,$bd_tabl_tipodespacho,$readonly,$NameDiv){
    global $id;
    $objResponse = new xajaxResponse();

    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");
    
    $td=new clsTipExp_SQLlista();
    $td->whereID($tiex_id);
    $td->setDatos();
    $tiex_ocultar_editor=$td->field('tiex_ocultar_editor');
    $tiex_ocultar_editor=$tiex_ocultar_editor?$tiex_ocultar_editor:0;
    
    if($op==1){        
        //$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);                
        $objResponse->script("xajax_getFecha(1,document.frm.Dr_desp_fecha.value,$tiex_id,$bd_tabl_tipodespacho,'$readonly','divFechaDoc')");        
        $objResponse->addScript("$('.my_select_box').select2({
                                    placeholder: 'Seleccione un elemento de la lista',
                                    allowClear: true,
                                    width: '90%',
                                });");
        

        $objResponse->addScript("$('#xxx_ocultar_editor').val($tiex_ocultar_editor)");
        return $objResponse;
    }
    else
        return $contenido_respuesta;
}

function getFiles($op,$id,$bd_desp_estado,$NameDiv){
    global $conn,$cuenta_firmados,$cuenta_recibidos;
    $objResponse = new xajaxResponse();    
    
    $sql=new despachoAdjuntados_SQLlista();
    $sql->wherePadreID($id);
    $sql->orderUno();
    $sql = $sql->getSQL();
    $rs = new query($conn, $sql);
    
    if($rs->numrows()>0 || $bd_desp_estado==1){ //SI EXISTEN ARCHIVOS oesta en edicion
            $table = new Table("","80%",4); // TÃ­tulo, Largura, Quantidade de colunas
            $table->setTableAlign("C");
            $table->addColumnHeader("",false,"1"); 
            $table->addColumnHeader("CONTROL DE ARCHIVOS ADJUNTOS",false,"98%", "C"); 
            $table->addColumnHeader("",false,"1"); 
            $table->addColumnHeader("",false,"1%");
            $table->addRow();            
            
//            $rs->getrow();            
//            if($rs->field("desp_ocultar_editor")==0 && $rs->field("desp_file_firmado")!=''){
//                $enlace=PUBLICUPLOAD."gestdoc/".$rs->field("desp_anno")."/$id/".$rs->field("desp_file_firmado");                
//                $table->addData("&nbsp;");
//                $table->addData(addLink('DOCUMENTO ORIGINAL ELABORADO',"javascript:verPDF('$enlace','Lector de PDF')","Click aqu&iacute; para Ver Documento","controle"));
//                $table->addData(addLink($rs->field("desp_file_firmado"),"javascript:verPDF('$enlace','Lector de PDF')","Click aqu&iacute; para Ver Documento","controle"));
//                $table->addData($rs->field("usua_login"));                
//                $table->addRow('ATENDIDO');                
//            }
//            $rs->skiprow(0);
            
            $cont_files=0;
            $titulo_pendientes=0;
            $titulo_no_firmados=0;
            while ($rs->getrow()) {
                if($rs->field("area_adjunto")){
                    $bd_dead_id = $rs->field("dead_id");
                    $periodo = $rs->field("periodo");
                    $name_file=$rs->field("area_adjunto");
                    $usua_id=$rs->field("usua_id");
                    
                    if($cuenta_firmados>0){//FIRMADOS
                        
                    }elseif($titulo_pendientes==0 && $rs->field("dead_signer")==1){//PASAN PARA FIRMA
                        $table->addBreak("ADJUNTADO(S) PARA FIRMA","C");
                        $titulo_pendientes=1;
                    }elseif($titulo_no_firmados==0 && $rs->field("dead_signer")==2){//NO FIRMADOS
                        if($titulo_pendientes==0){//PASAN PARA FIRMA
                            $table->addBreak("ADJUNTADO(S) PARA FIRMA","C");
                            $titulo_pendientes=1;
                        }
                            $table->addData("&nbsp;");
                            $table->addTotal("$cont_files Archivos","C");                        
                            $table->addTotal("&nbsp;");
                            $table->addTotal("&nbsp;");                        
                            $table->addTotal("&nbsp;");
                            $table->addRow();
                        
                        $table->addBreak("OTROS DISPONIBLES","C");
                        $cont_files=0;       
                        $titulo_no_firmados=1;
                    }

                    $icon_files=getIconFile($name_file);                    
                    $enlace=PUBLICUPLOAD."gestdoc/".SIS_EMPRESA_RUC."/$periodo/".$id."/".$name_file;
                    
                    if(strpos(strtoupper($enlace),'.PDF')>0){
                        $link=addLink("<img src=\"../../img/$icon_files\" border=0 align=absmiddle hspace=1 alt=\"Archivo\">".$name_file,"javascript:lectorPDF('$enlace','Lector de PDF')","Click aqu&iacute; para Ver Documento","controle");
                    }else{
                        $link=addLink("<img src=\"../../img/$icon_files\" border=0 align=absmiddle hspace=1 alt=\"Archivo\">".$name_file,"$enlace","Click aqu&iacute; para Descargar Archivo","controle");
                    }
                    
                    //SI ESTA FIRMADO
                    if($cuenta_firmados>0 && $rs->field("dead_signer")==1){
                        $table->addData("<img src=\"../../img/ico_signer_check.png\" border=0 align=absmiddle hspace=1 alt=\"Archivo Firmado\">");
                    }else{
                        $table->addData("&nbsp;");
                    }
                    
                    $table->addData($link);

                    if($usua_id==getSession("sis_userid") && $bd_desp_estado==1 && $cuenta_firmados==0 && $cuenta_recibidos==0){
                        $table->addData("<a class=\"link\" href=\"#\" onClick=\" javascript:getConfirm('Seguro de eliminar esta Archivo?',function(result) {xajax_eliminaFile('$bd_dead_id','$id','$bd_desp_estado')}) \"><img src=\"../../img/delete.gif\" border=0 align=absmiddle hspace=1 alt=\"Eliminar\"></a>");                
                    }else{
                        $table->addData("&nbsp;");
                    }

                    if($usua_id==getSession("sis_userid") && $bd_desp_estado==1){
                        if($rs->field("dead_signer")==1){//PARA FIRMA
                            $boton="<button type=\"button\" onClick=\"javascript:xajax_updownFile('$bd_dead_id','$id',$bd_desp_estado)\" class=\"btn btn-default btn-xs\">
                                <span class=\"glyphicon glyphicon glyphicon-arrow-down\" aria-hidden=\"true\"></span>
                              </button>";

                        }else{
                            $boton="<button type=\"button\" onClick=\"javascript:xajax_updownFile('$bd_dead_id','$id',$bd_desp_estado)\" class=\"btn btn-default btn-xs\">
                                <span class=\"glyphicon glyphicon glyphicon-arrow-up\" aria-hidden=\"true\"></span>
                              </button>";                        
                        }

                        $table->addData($boton);
                    }else{
                        $table->addData("&nbsp;");
                    }
                                
                    $cont_files=$cont_files+1;
                    $table->addRow();

                }
            }
        $table->addData("&nbsp;");
        $table->addTotal("$cont_files Archivos","C");    
        $table->addTotal("&nbsp;");
        $table->addTotal("&nbsp;");    
        $table->addRow();
        $contenido_respuesta=$table->writeHTML();    
    
    }
    if($op==1){        
        $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);
        return $objResponse;
    }
    else{
        return $contenido_respuesta;
    }
}

function eliminaFile($dead_id,$id,$bd_desp_estado)
{
    global $conn;	
        
    $objResponse = new xajaxResponse();
    
    $despacho=new despachoAdjuntados_SQLlista();
    $despacho->whereID($dead_id);
    $despacho->setDatos();
    $periodo=$despacho->field('periodo');
    $name_file=$despacho->field('area_adjunto');
    $usua_id=getSession("sis_userid");
    
    $enlace=PUBLICUPLOAD."gestdoc/$periodo/".$id."/".$name_file;

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
        $objResponse->addScript("xajax_getFiles(1,$id,$bd_desp_estado,'divFiles')");        
                    
    }                
    return $objResponse;
}

function updownFile($dead_id,$id,$bd_desp_estado)
{
    global $conn;	
        
    $objResponse = new xajaxResponse();
    
    $usua_id=getSession("sis_userid");
    
    $sql="UPDATE gestdoc.despachos_adjuntados
            SET dead_signer=CASE WHEN dead_signer=1 THEN 2 ELSE 1 END
            WHERE dead_id=$dead_id 
                    AND usua_id=$usua_id ";

    $conn->execute($sql);
    $error=$conn->error();
    if($error){
       $objResponse->addAlert($error);
       return $objResponse;
    }else{
        $objResponse->addScript("xajax_getFiles(1,$id,$bd_desp_estado,'divFiles')");        
                    
    }                
    return $objResponse;
}



function colaborativo($desp_id,$desp_expediente,$NameDiv)
{
    global $conn ;
    $objResponse = new xajaxResponse();
    
    $otable = new AddTableForm();
    $otable->addBreak("<b>Colaboradores para ".NAME_EXPEDIENTE ." $desp_id</b>");
    
    $otable->addField("Agregar Colaborador: ",textField("Colaborador","Sbusc_cadena","",40,60)."&nbsp;<input type=\"button\" onClick=\"xajax_buscarUsuario(1,document.frm.Sbusc_cadena.value,'divResultadoColaborativo');document.getElementById('divResultadoColaborativo').innerHTML = 'Espere, Buscando...'\" value=\"Buscar\">");
    $otable->addHtml("<tr><td colspan=2><div id='divResultadoColaborativo'>\n");
    $otable->addHtml("</div></td></tr>\n");
                
    $contenido_respuesta=$otable->writeHTML();
    
    $contenido_respuesta.="<div id=\"lista-colaborativos\">";
    $contenido_respuesta.=listaColaborativos(2,$desp_id);
    $contenido_respuesta.="</div>";
      
    $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);
    return $objResponse;
}

function listaColaborativos($op,$id){
    global $conn;	
    
    $objResponse = new xajaxResponse();
        
    /*obtengo datos familiares*/
    $listEnvios=new clsDespachosColaborativos_SQLlista();
    $listEnvios->wherePadreID($id);
    $listEnvios->orderUno();
    $sql=$listEnvios->getSQL();
    //echo $sql;
    $rs = new query($conn, $sql);

    /* inicializo tabla */
    $table = new Table("Permisos de Edici&oacute;n de Documento","100%",4); // Titulo, Largura, Quantidade de colunas

    /* construccion de cabezera de tabla */
    $table->addColumnHeader("Colaborador",false,"60%","c");
    $table->addColumnHeader("Permiso",false,"10%","c");
    $table->addColumnHeader("Registrado por",false,"30%","c");
    $table->addRow();
    
    while ($rs->getrow()) {
            /* adiciona columnas */
            $table->addData($rs->field("empleado").'/'.$rs->field("dni"));	
            
            if($rs->field("usua_id")==getSession("sis_userid")){
                $lista_tipo=array('1'=>'AUTORIZADO','0'=>'DENEGADO');
                $table->addData(listboxField("Permiso",$lista_tipo, "tr_permiso",$rs->field("deco_permiso"),"","onChange=\"xajax_actualizaPermiso('".$rs->field("deco_id")."',this.value)\""));
            }else{
                $table->addData($rs->field("permiso"));
            }
            
            $table->addData($rs->field("usua_login")."/". substr($rs->field("deco_fregistro"),0,19));
            $table->addRow(); // adiciona linea
            
    }
    $table->addBreak("Total Colaboradores: ".$rs->numrows(),true,"left");
    $contenido_respuesta=$table->writeHTML();
  

    // Se devuelve el objeto, que este dara todas las instruccione JS para realizar la tarea
    if($op==1){
        $objResponse->addAssign('lista-colaborativos','innerHTML', $contenido_respuesta);
        $objResponse->addScript("$('#btn_agregar_colaborador').show()");
        return $objResponse;
    }else{
        return $contenido_respuesta	;
    }	    
}

function ejigeUsuario($usua_id,$descrip=''){
    global $id,$conn;
    $objResponse = new xajaxResponse();
        
    
    $usuario=new clsUsers_SQLlista();
    $usuario->whereID($usua_id);
    $usuario->setDatos();
    $pdla_id=$usuario->field('pdla_id');
    
    $sql="INSERT INTO gestdoc.despachos_colaborativos
                  (desp_id,
                   pdla_id,
                   usua_id)
           VALUES ($id,
                   $pdla_id,
                   ".getSession("sis_userid")."
                   )
        ";

    $conn->execute($sql);
    $error=$conn->error();
    if($error){
       $objResponse->addAlert($error);
       return $objResponse;
    }else{
        $objResponse->addClear("divResultadoColaborativo",'innerHTML');
        $objResponse->addScript("xajax_listaColaborativos(1,'$id')");                            
    }                    
    return $objResponse;
}

function actualizaPermiso($deco_id,$permiso){
    global $id,$conn;
    $objResponse = new xajaxResponse();
        
    
    $sql="UPDATE gestdoc.despachos_colaborativos
            SET deco_permiso=$permiso
            WHERE deco_id=$deco_id
             AND usua_id=".getSession("sis_userid");
    

    $conn->execute($sql);
    $error=$conn->error();
    if($error){
       $objResponse->addAlert($error);
       return $objResponse;
    }else{
        $objResponse->addScript("xajax_listaColaborativos(1,'$id')");                            
    }                    
    return $objResponse;
}
$xajax->processRequests();
// fin para Ajax

?>
<html>
<head>
    <title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">

    <script language="JavaScript" src="../../library/js/focus.js"></script>
    <script language="JavaScript" src="../../library/js/libjsgen.js"></script>
    <script language="JavaScript" src="../../library/js/textcounter.js"></script>
    <script language="JavaScript" src="../../library/js/janela.js"></script>
    
    <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>            
    <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
    <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>

    
    <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
    <script src="../../library/bootstrap/js/bootstrap.min.js"></script>
    

    <script type="text/javascript" src="../../library/jquery-autosize/jquery.autosize.js"></script>

    <script src="../../library/ckeditor/ckeditor.js"></script>
    <script>CKEDITOR.dtd.$removeEmpty['span'] = false;</script>
    
    <style>
        #lectorPDF{
          width: 95% !important;
        }
    </style>
  
    <script language='JavaScript'>

	function mivalidacion(){
            var sError="Mensajes del sistema: "+"\n\n";
            var nErrTot=0;
            if (nErrTot>0){
                alert(sError)
                eval(foco)
                return false
            }else
                return true
	}
        
	/*
		funciÃ³n que define el foco inicial en el formulario
	*/
	function inicializa() {
            document.frm.Dr_desp_fecha.focus();
	}
        
        function generar_pdf(page) {
            parent.content.document.frm.target = "controle";
            parent.content.document.frm.action = page;
            parent.content.document.frm.submit();
        }
        
        
        function beforeUpload_eFirma(id, defi_id) {
            $( "#title-myModalScreen" ).addClass( "glyphicon glyphicon-upload" );
            $( "#title-myModalScreen" ).text( " Upload");
            xajax_upload_eFirma(id, defi_id,'msg-myModalScreen');
            $('#myModalScreen').modal('show');
            $('#password').focus();
        } 

        
//        function firmarEjecutar(id, defi_id)
//        {            
//            xajax_preparar_firma(id);
//            $('#myIframe').attr('src','<?php echo PATH_FIRMA ?>firmar/index.php?id='+id+'&defi_id='+defi_id);
//            $( "#title-myModalFirma" ).addClass( "glyphicon glyphicon-pencil" );
//            $( "#title-myModalFirma" ).text( "Firmar Documento");
//            $('#myModalFirma').modal('show');
//        }

        function afterUpload_eFirma(desp_id, defi_id){  
            var inputFile = document.getElementById("exap_adjunto");

            var data = new FormData();

            [].forEach.call(inputFile.files, function (file) {
                data.append('fileToUpload', file);
            });
            
            data.append('desp_id', desp_id);
            data.append('defi_id', defi_id);
            
                jQuery.ajax({
                    url: "upload_eFirma.php",        // Url to which the request is send
                    type: "POST",             // Type of request to be send, called as method
                    data: data, 			  // Data sent to server, a set of key/value pairs (i.e. form fields and values)
                    contentType: false,       // The content type used when sending data to the server.
                    cache: false,             // To unable request pages to be cached
                    processData:false,        // To send DOMDocument or non processed data file it is set to false
                    success: function(data)   // A function to be called if request succeeds
                    {
                        var data = JSON.parse(data);
                        $('#myModalScreen').modal('hide');
                        $('#msg-myModalFirma').text( data.message );
                        $('#myModalFirma').modal('show');

                    }
                });
        }    
        
        function upload(desp_id, op){ 
            var inputFile = document.getElementById("exap_adjunto");
            jQuery("#chk-error").html('<center><p><small class="text-success"><b>Espere, procesando...</b></small></p></center>');
            var data = new FormData();

            [].forEach.call(inputFile.files, function (file) {
                data.append('fileToUpload[]', file);
            });

            data.append('desp_id', desp_id);

                jQuery.ajax({
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
                            xajax_reload2(desp_id,op);
                       }else{
                            $('#msg-myModalFirma').text( data.mensaje );
                            $('#myModalFirma').modal('show');
                        }    
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                      console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                      jQuery('#Guardar').show();
                    }
                });
        }


        function setMarcarFinal(id){  
            jQuery("#chk-error").html('<center><p><small class="text-success"><b>Espere, procesando...</b></small></p></center>');
            var data = new FormData();
            data.append('id', id);
            data.append('tipo_vista', 'F');
            data.append('marcar_final',1)

            jQuery.ajax({
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
                url: "rptDocumento.php",        // Url to which the request is send
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
                       xajax_reload(id,0);
                       //enviarEmail(id,1); //1->DOCUMENTO PARA FIRMA
                   }else{
                       $('#msg-myModalFirma').text( data.mensaje );
                       $('#myModalFirma').modal('show');
                   }    
                },
                error: function (xhr, ajaxOptions, thrownError) {
                  console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                  jQuery('#Guardar').show();
                }
            });
        }
        
        
         function enviarEmail(id,flujo){  
            jQuery("#chk-error").html('<center><p><small class="text-success"><b>Espere, procesando...</b></small></p></center>');
            var data = new FormData();
            data.append('flujo', flujo);
            data.append('id', id);

            jQuery.ajax({
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
                url: "enviarEmail.php",        // Url to which the request is send
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
                       xajax_reload(id,0);
                   }else{
                        $('#msg-myModalFirma').text( data.mensaje );
                        $('#myModalFirma').modal('show');
                   }    
                },
                error: function (xhr, ajaxOptions, thrownError) {
                  console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                  jQuery('#Guardar').show();
                }
            });
        }
        
        
        function enviarEmailProveedor(op,id,email,mensaje){  
            jQuery("#email-chk-error").html('<center><p><small class="text-success"><b>Espere, procesando...</b></small></p></center>');
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
            jQuery.ajax({
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
                       $('#email-chk-error').html( '<center><p><small class="text-success">'+ data.mensaje +'</small></p></center>' );
                   }else{
                        $('#email-chk-error').html( '<center><p><small class="text-danger">'+ data.mensaje +'</small></p></center>' );
                        jQuery('#btn_envia_email').show();
                   }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                  console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                  jQuery('#btn_envia_email').show();
                }
            });
        }
        
        
        function setFirmaElectronica(id, id_firma){  
            jQuery("#chk-error").html('<center><p><small class="text-success"><b>Espere, procesando...</b></small></p></center>');
            var data = new FormData();
            data.append('id', id);
            data.append('id_firma',id_firma)

            jQuery.ajax({
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
                url: "ponerFirmaElectronica.php",        // Url to which the request is send
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
                       xajax_setFirma(id_firma);
                   }else{
                       $('#msg-myModalFirma').text( data.mensaje );
                       $('#myModalFirma').modal('show');
                   }    
                },
                error: function (xhr, ajaxOptions, thrownError) {
                  console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                  jQuery('#Guardar').show();
                }
            });
        }
        
        function setFCContenido(){          
            if( CKEDITOR.instances['K__desp_contenido'].getData()!='' ){
                getConfirm('Seguro de Reemplazar el Contenido?',function(result) {
                    CKEDITOR.instances['K__desp_contenido'].setData($("#divContenido").html());
                    }
                )               
            }else{
                CKEDITOR.instances['K__desp_contenido'].setData($("#divContenido").html());
            }        
        }

        function consultar_RUC(codigo, div){            
                jQuery.ajax({
                url : '<?php echo SIS_URL_SUNAT_RENIEC ?>',
                method :  'POST',
                dataType : "json",
                data: {'codigo' : codigo }
                        }).then(function(data){
                            if(data.success == true) {
                                xajax_addProveedor(codigo,data.result);
                            }else{
                                document.getElementById(div).innerHTML = '<font color=red>'+data.message+'</font>'
                            }
                        }, function(reason){
                            alert(reason.responseText);
                            //console.log(reason);
                        });
        }        

        function nuevaEntidad(){
            abreJanelaAuxiliar('../modulos/catalogos/catalogosDependenciaExterna_edicion.php?clear=2,nomeCampoForm=Sx_proveedor,fieldExtra=tx_prov_id,nbusc_cadena='+document.frm._DummySx_proveedor.value,820,600)
        }

        function beforeEnviaEmail(desp_id,desp_expediente) {
            $("#title-myModalImp").addClass("glyphicon glyphicon-envelope");
            $("#title-myModalImp").html("");
            xajax_enviar_email(1,desp_id,desp_expediente,'msg-myModalImp');
            $('#myModalImp').modal('show');
        }         

        function enviaEmail(id,clie_id,xml) {
            parent.content.document.frm.target = "controle";
            parent.content.document.frm.action = 'rptGuiaRemisionRemitente.php?id='+id+'&op=3'+'&email='+document.frm.cx_clie_email.value+'&clie_id='+clie_id+'&xml='+xml;
            parent.content.document.frm.submit();
        }
        
        function beforeColaborativo(desp_id,desp_expediente) {
            $("#title-myModalImp").addClass("");
            $("#title-myModalImp").html("EDICION COLABORATIVA");
            xajax_colaborativo(desp_id,desp_expediente,'msg-myModalImp');
            $('#myModalImp').modal('show');
        }         
        
        function verFile(file) {
            AbreVentana(file);
        }
	</script>
        
        <style>
            .select2-rendered__match {
                text-decoration : underline;
                text-decoration-color:  blue;
             }
        </style>        
        
	<?php
        verif_framework();
        $xajax->printJavascript(PATH_INC.'ajax/');
        ?>


</head>

<body class="contentBODY" onLoad="inicializa()">
<div class="col-md-12" id="respuesta_proceso"></div>
<?php
if(!strlen($id))
    pageTitle("Nuevo ".$myClass->getTitle());
else
    pageTitle("Edici&oacute;n de ".$myClass->getTitle());

/* Control de fichas */
//SI HAN FIRMADO TODOS
if(($cuenta_firmantes>0 && $cuenta_firmantes==$cuenta_firmados)
    && ($cuenta_recibidos==0 || $cuenta_recibidos<$cuenta_derivaciones)){

    $abas = new Abas();
    $abas->addItem(" EDICION DE DOCUMENTO ",true);
    if($id){//abierto){
            $abas->addItem(" ARCHIVOS ADJUNTADOS ",false,"registroDespacho_edicionAdjuntos.php?relacionamento_id=$id&clear=2");    
    }

    echo $abas->writeHTML();
    
}  


//APLICA FILTROS
//controla las estisticas
$EstadDespDepen=new getAcumDespachosDependencia();
$EstadDespDepen->whereDepeID($bd_depe_id);
$EstadDespDepen->setDatos();
$depe_acum_despachos_porrecibir=$EstadDespDepen->field("depe_acum_despachos_porrecibir");
$depe_max_x_recibir=$EstadDespDepen->field("depe_max_x_recibir");

$depe_acum_despachos_enproceso=$EstadDespDepen->field("depe_acum_despachos_enproceso");
$depe_max_doc_proceso=$EstadDespDepen->field("depe_max_doc_proceso");
$depe_max_dias_doc_proceso=$EstadDespDepen->field("depe_max_dias_doc_proceso");

$depe_max_dias_x_recibir=$EstadDespDepen->field("depe_max_dias_x_recibir");

if($depe_acum_despachos_porrecibir>$depe_max_x_recibir){
    $titulo="IMPOSIBLE CONTINUAR...!!!";
    $msj=getSession("sis_depename"). " tiene $depe_acum_despachos_porrecibir Documento por Recibir <br>Supera el Maximo Permitido ($depe_max_x_recibir)";
    $destino="mensajeArgumento.php?titulo=$titulo&msj=$msj";
    redirect($destino);
}

if($depe_acum_despachos_enproceso>$depe_max_doc_proceso){
    $titulo="IMPOSIBLE CONTINUAR...!!!";
    $msj=getSession("sis_depename"). " tiene $depe_acum_despachos_enproceso Documento en Proceso <br>Supera el Maximo Permitido ($depe_max_doc_proceso)";
    $destino="mensajeArgumento.php?titulo=$titulo&msj=$msj";
    redirect($destino);
}

//APLICA EL FILTRO SOLO SI EL VALOR HA SIDO CAMBIADO
if($depe_max_dias_doc_proceso<999999){
    $EstadMaxDiasenProceso=new getAcumDespachosMaxDiasProceso($bd_depe_id,$depe_max_dias_doc_proceso);
    $EstadMaxDiasenProceso->setDatos();
    $acum_max_dias_proceso=$EstadMaxDiasenProceso->field("acum_max_dias_proceso");
    if($acum_max_dias_proceso>0){
        $titulo="IMPOSIBLE CONTINUAR...!!!";
        $msj=getSession("sis_depename"). " tiene $acum_max_dias_proceso Documento(s) en Proceso de Muchos dias<br>Supera el Maximo de dias Permitido ($depe_max_dias_doc_proceso)";
        $destino="mensajeArgumento.php?titulo=$titulo&msj=$msj";
        redirect($destino);    
    }
}

//APLICA EL FILTRO SOLO SI EL VALOR HA SIDO CAMBIADO
if($depe_max_dias_x_recibir<999999){
	
    $EstadMaxDias_x_recibir=new getAcumDespachosMaxDiasPorRecibir($bd_depe_id,$depe_max_dias_x_recibir);
    $EstadMaxDias_x_recibir->setDatos();
    $acum_max_dias_x_recibir=$EstadMaxDias_x_recibir->field("acum_max_dias_por_recibir");
    if($acum_max_dias_x_recibir>0){
        $titulo="IMPOSIBLE CONTINUAR...!!!";
        $msj=getSession("sis_depename"). " tiene $acum_max_dias_x_recibir Documento(s) por Recibir de Muchos dias<br>Supera el Maximo de dias Permitido ($depe_max_dias_x_recibir)";
        $destino="mensajeArgumento.php?titulo=$titulo&msj=$msj";
        redirect($destino);    
    }
}
//FIN DE FILTROS


//SI SE HA RECIBIDO O SI HAY FIRMANTES 
if($bd_desp_estado==2 || $cuenta_recibidos>0 || $cuenta_firmados>0 || strlen($desp_file_firmado)>0){
    $readonly='readonly';
}else{
    /*si es el responsable o el firmante principal*/
    if($id){
        $permite_editar=new permiteEdicionColaborativo($id,getSession("sis_persid"));
        $permite_editar->setDatos();
        $permite_editar=$permite_editar->field('permiso');
        $permite_editar=$permite_editar?$permite_editar:0;        
    }else{
        $permite_editar=0;
    }
    if(!$id || getSession("sis_userid")==$bd_usua_id || $permite_editar==1){
        $readonly='';
    }else{
        if($id){
            


        }else{
            $readonly='readonly';
        }
    }
}

//SI HAN FIRMADO TODOS
//if((($cuenta_firmantes>0 && $cuenta_firmantes==$cuenta_firmados) || inlist($bd_tabl_tipodespacho,'142'))
//    && ($cuenta_archivados!=$cuenta_derivaciones || $cuenta_derivaciones==0)){
if((($cuenta_firmantes>0 && $cuenta_firmantes==$cuenta_firmados) || inlist($bd_tabl_tipodespacho,'142')) /*142->otras entidades*/
    && ($cuenta_recibidos==0 || $cuenta_recibidos<$cuenta_derivaciones)){
    $permite_derivaciones=1;
}else{
    $permite_derivaciones=0;
}  

/* botones */
$button = new Button;

/*si todos los derivados estan archivados entonces el registro no debe modicarse*/
//if(($cuenta_archivados!=$cuenta_derivaciones) or $cuenta_derivaciones==0 or !$id){
if($cuenta_archivados==0){    

    if($readonly=='readonly'){
//        if($habilita_edicion==1 && inlist($bd_tabl_tipodespacho,'142')){//DOCUMENTO EXTERNO
//            $button->addItem(" Guardar ","javascript:if(ObligaCampos(frm)){
//                                $('#Guardar').hide()
//                                xajax_guardar(xajax.getFormValues('frm'),'$regSeleccionadosExp','$regSeleccionadosId','','');
//                            }",'content',2,getSession("sis_userid"),"","btn-bootstrap");
//            
//        }elseif($permite_derivaciones==1){//permite derivaciones
        if($permite_derivaciones==1){//permite derivaciones    
            $button->addItem(" Guardar Derivaciones ","javascript:$('#btn_guardar_derivacion').hide();
                                                                xajax_guardarDerivaciones('$id',2,xajax.getFormValues('frm'),'$regSeleccionadosId',1)",'content',2,getSession("sis_userid"),"","btn-bootstrap","","btn_guardar_derivacion");
        }
    }else{
        $button->addItem(" Guardar ","javascript:if(ObligaCampos(frm)){
                                $('#Guardar').hide()
                                if(document.frm.xxx_ocultar_editor.value==1){ 
                                    xajax_guardar(xajax.getFormValues('frm'),'$regSeleccionadosExp','$regSeleccionadosId','','');
                                }else{
                                    xajax_guardar(xajax.getFormValues('frm'),'$regSeleccionadosExp','$regSeleccionadosId',CKEDITOR.instances['K__desp_contenido'].getData());
                                }
                            }",'content',2,getSession("sis_userid"),"","btn-bootstrap");
        
    }
    
//    if($id && $readonly==''){
    
//        $button->addItem(" Subir eFirma ","javascript:beforeUpload_eFirma('$id')","content",0,0,"","btn-bootstrap");
//    }
    
    if($id && (inlist($bd_tabl_tipodespacho,'140,141') || $bd_tiex_exigir_marcar_documento_final==1) ){
            if($bd_desp_estado==1){ //ABIERTO

                $permite_editar=new permiteEdicionColaborativo($id,getSession("sis_persid"));
                $permite_editar->setDatos();
                $permite_editar=$permite_editar->field('permiso');
                $permite_editar=$permite_editar?$permite_editar:0;

                if($bd_usua_id==getSession("sis_userid") || $permite_editar==1){
                    $blocksLi.= "<li><a href=\"javascript:setMarcarFinal('$id')\">Marcar como Documento Final</a></li>";
                }
                //if($bd_usua_id==getSession("sis_userid")){
                    $blocksLi.= "<li role=\"separator\" class=\"divider\"></li>";
                    $blocksLi.="<li><a href=\"#\" onClick=\"javascript:beforeColaborativo('$id','$bd_desp_expediente')\" target=\"controle\">Edici&oacute;n Colaborativa</a></li>";
                //}
            }else{ 
                    $blocksLi='';
                    $ultTipo='';
                    $firmas=new despachoFirmas_SQLlista();
                    $firmas->wherePadreID($id);
                    $firmas->orderUno();
                    $sql=$firmas->getSQL();
                    $rs = new query($conn, $sql);
                    //echo $sql;
                    $hay_firmados=0;
                    while ($rs->getrow()) {
                        $idFirma=$rs->field('defi_id');
                        $defi_tipo=$rs->field('defi_tipo');
                        if($ultTipo!='' && $ultTipo!=$rs->field('defi_tipo')){
                            $blocksLi.= "<li role=\"separator\" class=\"divider\"></li>";
                            $ultTipo=$rs->field('defi_tipo');
                        }
                        if($rs->field('defi_estado')==1){ //si ya esta firmado³
                            $blocksLi.= "<li><span class=\"glyphicon glyphicon glyphicon-ok\" aria-hidden=\"true\"></span>&nbsp;".ucfirst(strtolower($rs->field('pers_apellpaterno'))).' '.ucfirst(strtolower($rs->field('pers_apellmaterno'))).' '.ucfirst(strtolower($rs->field('nombres'))).' ('.ucfirst(strtolower($rs->field('cargo'))).'-'.ucfirst(strtolower($rs->field('dependencia')))."</li>";   
                            /*SI NO MARCADO REHACER DOCUMENTO*/
                            if($rs->field('defi_autoriza_rehacer')==0){
                                $hay_firmados=1;
                            }
                        }else{ //si aun no firma
                            //SI ES EL MISMO USUARIO QUE CREA EL DOCUMENTO, EL QUE FIRMA
//                            $users=new clsUsers_SQLlista();
//                            $users->whereID(getSession("sis_userid"));
//                            $users->setDatos();
                            if($rs->field('pers_id')==getSession("sis_persid")){
                                /*si es documento personal*/
                                if ($bd_tabl_tipodespacho==141 && SIS_FIRMA_PERSONAL_ELECTRONICA==1){
                                    if(inlist($defi_tipo,'2')){//VB
                                        $tipo_firma="VB ELECTRÓNICO";
                                    }else{
                                        $tipo_firma="FIMA ELECTRÓNICA";
                                    }
                                    $blocksLi.= "<li><a href=\"#\" onClick=\"javascript:getConfirm('Seguro de Poner Firma Electrónica?',function(result) {setFirmaElectronica('$id','$idFirma')})\" >".ucfirst(strtolower($rs->field('defi_especbreve'))).' '.ucfirst(strtolower($rs->field('pers_apellpaterno'))).' '.ucfirst(strtolower($rs->field('pers_apellmaterno'))).' '.ucfirst(strtolower($rs->field('nombres'))).'('.ucfirst(strtolower($rs->field('cargo'))).")"." ** $tipo_firma **"."</a></li>";   
                                }else{
                                    if(inlist($defi_tipo,'2')){//VB
                                        $tipo_firma="VB";
                                    }else{
                                        $tipo_firma="";
                                    }

                                    if(getSession("SET_CERTIFICADO")==1){//SI FIRMA CON CERTIFICADO
                                        $blocksLi.= "<li><a href=\"#\" onClick=\"javascript:xajax_beforeFirma( '$id','$idFirma')\">".ucfirst(strtolower($rs->field('defi_especbreve'))).' '.ucfirst(strtolower($rs->field('pers_apellpaterno'))).' '.ucfirst(strtolower($rs->field('pers_apellmaterno'))).' '.ucfirst(strtolower($rs->field('nombres'))).' ('.ucfirst(strtolower($rs->field('cargo'))).'-'.ucfirst(strtolower($rs->field('dependencia'))).") $tipo_firma</a></li>";   
                                    }else{
                                        $blocksLi.= "<li><a href=\"#\" onClick=\"javascript:getConfirm('Seguro de Proceder?',function(result) {xajax_setFirma('$idFirma')})\" >".ucfirst(strtolower($rs->field('defi_especbreve'))).' '.ucfirst(strtolower($rs->field('pers_apellpaterno'))).' '.ucfirst(strtolower($rs->field('pers_apellmaterno'))).' '.ucfirst(strtolower($rs->field('nombres'))).'- ** CONTINUAR SIN CERTIFICADO DIGITAL **</a></li>';   
                                        //$blocksLi.= "<li><a href=\"#\" onClick=\"javascript:xajax_beforeFirma( '$id','$idFirma')\">".ucfirst(strtolower($rs->field('defi_especbreve'))).' '.ucfirst(strtolower($rs->field('pers_apellpaterno'))).' '.ucfirst(strtolower($rs->field('pers_apellmaterno'))).' '.ucfirst(strtolower($rs->field('nombres'))).'- ** SIN CERTIFICADO DIGITAL **</a></li>';   
                                    }    
                                }
                                //$blocksLi.= "<li><a href=\"#\" onClick=\"javascript:beforeUpload_eFirma( '$id','$idFirma')\"><b>(SUBIR DESDE REFIRMA)</b> ".ucfirst(strtolower($rs->field('defi_especbreve'))).' '.ucfirst(strtolower($rs->field('pers_apellpaterno'))).' '.ucfirst(strtolower($rs->field('pers_apellmaterno'))).' '.ucfirst(strtolower($rs->field('nombres'))).' ('.ucfirst(strtolower($rs->field('cargo'))).'-'.ucfirst(strtolower($rs->field('dependencia'))).")</a></li>";                                   
                            }else{                                
                                $blocksLi.= "<li><span class=\"glyphicon glyphicon-ban-circle\" aria-hidden=\"true\"></span>&nbsp;".ucfirst(strtolower($rs->field('pers_apellpaterno'))).' '.ucfirst(strtolower($rs->field('pers_apellmaterno'))).' '.ucfirst(strtolower($rs->field('nombres'))).' ('.ucfirst(strtolower($rs->field('cargo'))).'-'.ucfirst(strtolower($rs->field('dependencia'))).")  </li>";
                            }
                        }
                        
                        if($ultTipo==''){
                            $ultTipo=$rs->field('defi_tipo');
                        }
                    }

                 if($bd_desp_estado==2 && $hay_firmados==0 && (inlist($bd_tabl_tipodespacho,'140,141') || $bd_tiex_exigir_marcar_documento_final==1) && $bd_usua_id==getSession("sis_userid")){ //140->INSTITUCIONAL
                    $blocksLi.= "<li role=\"separator\" class=\"divider\"></li>";
                    $blocksLi.= "<li><a href=\"javascript:xajax_deshacerMarcarFinal('$id')\">Deshacer Documento Final</a></li>";
                 }
                 
                 if($bd_desp_estado==2){
                     $blocksLi.="<li><a href=\"#\" onClick=\"javascript:beforeEnviaEmail('$id','$bd_desp_expediente')\" target=\"controle\">Enviar Correo Electr&oacute;nico</a></li>";
                 }
                    
            }
     
            
            $button->addHtml("<button class=\"btn btn-default dropdown-toggle btn-sm\" type=\"button\" id=\"dropdownMenu1\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"true\">
                                <span class=\"glyphicon glyphicon glyphicon-pencil\" aria-hidden=\"true\"></span>&nbsp;Firmar
                                <span class=\"caret\"></span>
                              </button>
                              <ul class=\"dropdown-menu dropdown-menu-right\" aria-labelledby=\"dropdownMenu1\">
                                $blocksLi
                              </ul>");
    }
    
}

    if($id && (inlist($bd_tabl_tipodespacho,'140,141') || $bd_tiex_exigir_marcar_documento_final==1) && $desp_ocultar_editor==0){
        $button->addItem(" Ver Documento ","javascript:lectorPDF('rptDocumento.php?id=$id','Lector de PDF')","content",0,0,"","btn-bootstrap");
//        $button->addHtml("<button class=\"btn btn-default\" data-toggle=\"modal\" data-target=\"#elIDdelModal\">
//                            <i class=\"glyphicon glyphicon glyphicon-search\"></i> Ver Documento
//                        </button>");
    }
    
    $button->addItem(" Regresar ","javascript:if(confirm('Seguro de Regresar?')){
                                        document.location='registroDespacho_buscar.php?clear=1'}","content",0,0,"","btn-bootstrap");


    echo $button->writeHTML();
    

?>
<div align="center">
<!-- Lista -->
<form name="frm" id="frm" method="post">
<?php
$form = new AddTableForm();
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("f_id",$id); // clave primaria
$form->addHidden("xxx_ocultar_editor",$desp_ocultar_editor,'xxx_ocultar_editor'); // clave primaria
if($bd_desp_estado==2){//cerrado    
    $form->addHidden("___getFiles",1); // clave primaria    
}else{
    $form->addHidden("___getFiles",0); // clave primaria
}
$form->addHidden("xxx_focus_select","",'focus_select');
$form->addHidden("xxx_plantilla_destinatario","",'plantilla_destinatario');
//
//if($cuenta_recibidos>0){
//    $form->addField("Modo de Recepci&oacute;n/Envio:",$bd_modo_recepcion);
//}
//else{
//    $modo_recep=new clsTabla_SQLlista();
//    $modo_recep->whereTipo('MODO_RECEPCION');
//    $sqlmodo_recepcion=$modo_recep->getSQL_cbox();
//    $form->addField("Modo de Recepci&oacute;n/Envio:",listboxField("Modo de Recepci&oacute;n/Envio",$sqlmodo_recepcion,"tr_tabl_modorecepcion",$bd_tabl_modorecepcion));
//}

if (strlen($id)>0) { // edicion
        if($bd_desp_estado==1){//ABIERTO
            $ico_candado="<img src=\"../../img/look_o.png\" border=0 align=absmiddle hspace=1 alt=\"Abierto\">";
        }else{
            $ico_candado="<img src=\"../../img/look_c.png\" border=0 align=absmiddle hspace=1 alt=\"Cerrado\">";
        }
    $form->addField(NAME_EXPEDIENTE.": ",addLink($ico_candado.$id,"javascript:lectorPDF('/sisadmin/gestdoc/controllers/procesar_data.php?nr_numTramite=$id&vista=NoConsulta','Seguimiento')","Click aqu&iacute; para Seguimiento de registro","","h4").iif($id,">",0,"&nbsp;&nbsp;<b><font color=green>Ult.Grabaci&oacute;n: ".  $usernameactual.' '.substr(stod($bd_desp_actualfecha),0,19)."</font></b>",""));
}


//$periodoAtencion=new clsPrioriAtencion_SQLlista();
//$periodoAtencion->orderUno();
//$sql_periodoAtencion=$periodoAtencion->getSQL_cbox();
//$form->addField("Prioridad de Atenci&oacute;n:",listboxField("Prioridad de Atenci&oacute;n",$sql_periodoAtencion,"tx_prat_id",$bd_prat_id,'',iif($readonly,'==','readonly','disabled','')).iif($id,">",0,"&nbsp;&nbsp;<b><font color=green>Ultima Grabaci&oacute;n:".  substr(stod($bd_desp_actualfecha),0,19)."</font></b>",""));
$form->addHidden("tx_prat_id",1); // Prioridad de Atencion NORMAL

if (strlen($id)>0) { // edicion
    $form->addField("Tipo: ",$tipo_despacho);
}
else{
    $desp_tipo=new clsTabla_SQLlista();
    $desp_tipo->whereTipo('TIPO_DESPACHO');
    $desp_tipo->whereActivo();
    //$desp_tipo->whereNoID(141); //PERSONAL
    $desp_tipo->orderUno();
    $rs = new query($conn, $desp_tipo->getSQL());

    $lista_nivel = array();
    //$bd_tabl_tipodespacho=0;
    while ($rs->getrow()) {
        //$bd_tabl_tipodespacho=$bd_tabl_tipodespacho?$bd_tabl_tipodespacho:$rs->field("tabl_id");
        $lista_nivel[].=$rs->field("tabl_id").",".$rs->field("tabl_descripcion");
    }

    $form->addField("Tipo de ".NAME_EXPEDIENTE.": <font color=red>*</font>",radioField("Tipo_de_tramite",$lista_nivel, "xxtipo_despacho",$bd_tabl_tipodespacho,"onChange=\"xajax_getInicia(1,this.value,0,'')\"","H")); 
}

if($bd_desp_estado==1 && $habilita_edicion==1){
    if($id)
    {
        $cuenta_files=getDbValue("SELECT COUNT(*) AS cuenta_files FROM gestdoc.despachos_adjuntados WHERE desp_id=$id");
        $cuenta_files=$cuenta_files?$cuenta_files:0;
    }
    
    $max_filesize=ini_get('upload_max_filesize');
    $form->addBreak("<b>ADJUNTAR ARCHIVO: <font color=red>(Tama&ntilde;o M&aacute;ximo $max_filesize)</font></b>");
    $form->addHidden("postPath",'exp_legales/');
    $form->addField("Archivo: ",fileField2("Archivo","exap_adjunto" ,"",60,"onchange=validaextension(this,'ZIP,RAR,GIF,JPG,PNG,DOC,DOCX,XLS,XLSX,PPT,PPTX,ODT,ODS,ODP,PDF')").            
                    iif($id,'>',0,addLink("<b>VER ARCHIVOS ($cuenta_files)</b>","javascript:if(document.frm.___getFiles.value==0){ $( '#divFiles' ).show( 'slow' );xajax_getFiles(1,$id,'$bd_desp_estado','divFiles');document.frm.___getFiles.value=1;}else{document.frm.___getFiles.value=0;$( '#divFiles' ).hide( 'fast' );}","Click aqu&iacute; para Ver Documento","controle"),''));            

    $form->addHtml("<tr><td colspan=2><span id='chk-error'></span></td></tr>");
    $form->addHtml("<tr><td colspan=2><div class='progress'><div id='file-progress-bar' class='progress-bar'></div></td></tr>");    
    
}

$form->addHtml("<tr><td colspan=2><div id='divFiles'>\n"); //pide datos de afectacion presupuestal
if($bd_desp_estado==2){//cerrado    
    $form->addHtml(getFiles(2,$id,$bd_desp_estado,'divFiles'));
}    
$form->addHtml("</div></td></tr>\n");    
$form->addBreak("<b>DOCUMENTO:</b>");
$form->addHtml("<tr><td colspan=2><div id='divDatosIniciales'>\n"); //pide datos de afectacion presupuestal
$form->addHtml(getInicia(2,$bd_tabl_tipodespacho,$id,$readonly));
$form->addHtml("</div></td></tr>\n");

if (strlen($id)>0 && ($habilita_edicion==0 || $readonly=='readonly')) { 
    $form->addField("Tipo de Documento: ",$tiex_descripcion);
    $form->addHidden("tr_tiex_id",$bd_tiex_id);
}else{
    $texp=new clsTipExp_SQLlista();
    $texp->whereHabilitado();
    $texp->orderUno();
    $sqltipo=$texp->getSQL_cbox2();
    $form->addField("Tipo de Documento: <font color=red>*</font>",listboxField("Tipo_de_Documento",$sqltipo,"tr_tiex_id",$bd_tiex_id,"-- Seleccione Tipo de Documento --","onChange=\"xajax_getSecuencia(1,this.value,document.frm.Dr_desp_fecha.value,document.frm.___tabl_tipodespacho.value,document.frm.nr_depe_id.value,document.frm.___proyectado.value,'$readonly',1);xajax_getVistos(1,document.frm.hx_visto.checked,this.value,0,'$bd_desp_vistos','$bd_desp_firmas_jefes','$bd_desp_vistos_empleados','$bd_desp_firmas_externos','')\""
                                                                                                            ,"","class=\"my_select_box\""));        
}
        
$form->addHtml("<tr><td colspan=2><div id='divFechaDoc' >\n"); //muestra numero de documento
$form->addHtml(getFecha(2,$bd_desp_fecha,$bd_tiex_id,$bd_tabl_tipodespacho,$readonly,'divFechaDoc'));
$form->addHtml("</div></td></tr>\n");

$form->addHtml("<tr><td colspan=2><div id='divNumeroDoc' >\n"); //muestra numero de documento
$form->addHtml(getSecuencia(2,$bd_tiex_id,$bd_desp_fecha,$bd_tabl_tipodespacho,$bd_depe_id,0,$readonly,1));
$form->addHtml("</div></td></tr>\n");

if($regSeleccionadosExp){
    $regSeleccionadosExpLink=$regSeleccionadosExpLink?$regSeleccionadosExpLink:$regSeleccionadosExp;
    $form->addField("<font color=red>Registros Adjuntados:</font>",checkboxField("Adjuntar a este documento","hx_asjuntar",1,1)."&nbsp;<font color=red><b>".$regSeleccionadosExpLink."</b></font>");
}else{
    $form->addHidden("hx_asjuntar",0); 
    $form->addHidden("hx_relacionado_exp",''); 
    $form->addHidden("hx_relacionado_id",''); 
}
//echo "xx".$bd_desp_exterior;
$form->addHtml("<tr><td colspan=2><div id='divPara' >\n"); //muestra numero de documento
$form->addHtml(getPara(2,"$bd_desp_exterior",getSession("sis_depeid"),$bd_tabl_tipodespacho,$readonly,""));
$form->addHtml("</div></td></tr>\n");

if($readonly=='readonly'){
        $form->addField("Asunto: ",$bd_desp_asunto);
}else{
    $form->addField("Asunto: <font color=red>*</font>",textAreaField("Asunto","Er_desp_asunto",$bd_desp_asunto,2,80,5000,$readonly,0,"normal"));
}
if($readonly=='readonly'){
    if($bd_desp_referencia){
        $form->addField("Referencia: ",$bd_desp_referencia);
    }
}else{
    $form->addField("Referencia: ",textAreaField("Referencia","Ex_desp_referencia","$bd_desp_referencia",1,80,5000,$readonly,0,"normal")); 
}

if (strlen($id)>0){
    if($bd_desp_expediente){
        $form->addField("<font colore=Red>Expediente: </font>","<b>$bd_desp_expediente</b>");
    }
    $form->addHidden("nx_desp_expediente",$bd_desp_expediente);
}
else{
    $form->addField(iif($bd_desp_expediente,'!=','','<font color=Red>','')."Expediente: ".iif($bd_desp_expediente,'!=','','</font>',''),numField("Expediente","nx_desp_expediente",$bd_desp_expediente,8,8,0,false));
    
    $btnBuscar="<input type=\"button\" onClick=\"xajax_buscarEnProceso(5,document.frm.Sbusc_expediente.value,document.frm.nr_depe_id.value,'1','divBuscarExpediente');document.getElementById('divBuscarExpediente').innerHTML = 'Espere, Buscando...'\" value=\"Buscar\">";
    $form->addField("Buscar Exp/Asunto: ",textField("Cadena de B&uacute;squeda","Sbusc_expediente","",60,60)."&nbsp;$btnBuscar");    
    $form->addHtml("<tr><td colspan=2><div id='divBuscarExpediente'>\n");
    $form->addHtml("</div></td></tr>\n");
}

$form->addHtml("<tr><td colspan=2><div id='divDatosJudiciales' >\n"); //pide datos judiciales
$form->addHtml(getDocJudicial(2,$bd_tiex_id,$cuenta_recibidos,'divDatosJudiciales'));
$form->addHtml("</div></td></tr>\n");

if($bd_desp_estado==2 || ($cuenta_recibidos>0 && $habilita_edicion==0)){
    if($bd_desp_notas){
        $form->addField("Observaciones: ",$bd_desp_notas);        
    }
}
else{
    $form->addField("Observaciones: ",textField("Observaciones","Sx_desp_notas",$bd_desp_notas,80,250));
}

//
//OJO, EL EDITOR DENE SER PERMANENTE
if($desp_ocultar_editor==0 && $bd_desp_estado==1 && $readonly==''){ //&& inlist($bd_tabl_tipodespacho,'140,141')
    $form->addHtml("<tr><td colspan=2>");
    $form->addHtml("<textarea name=\"K__desp_contenido\" id=\"K__desp_contenido\" rows=\"10\" cols=\"80\">
                    $bd_desp_contenido
                    </textarea>");
    $form->addHtml("</td></tr>\n");
}

    //SI SE HA FIRMADO TODO
    if($id && (($bd_desp_estado==2 && $cuenta_firmantes>0 && $cuenta_firmantes==$cuenta_firmados)
                || $bd_tabl_tipodespacho==142) /*DOCUMENTO EXTERNO*/ && ($cuenta_recibidos==0 || $cuenta_recibidos<$cuenta_derivaciones)){//OTRAS ENTIDADES
        
        $form->addHtml("<tr><td colspan=2>&nbsp;</td></tr>"); 
        $form->addBreak("DERIVACIONES", true);

//        $sqlDependencia=new dependenciaJefe_SQLlista();
//        $sqlDependencia->orderUno();
//        $sqlDependencia->whereHabilitado();
//        $sqlDependencia=$sqlDependencia->getSQL_cbox3(getSession("sis_depeid"));

        $sql = array(1 => "Destino: Dependencia",
                 2 => "Destino: Empleados",
                 3 => "Destino: Grupo");
    
        $form->addField(listboxField("Tipo_destino", $sql, "Sr_tipo_destino",1).': <font color=red>*</font>',listboxField("Dependencia_Destino","","Sx_dependencia","","seleccione Destino","","","class=\"my_select_box2\" multiple "));
        
        $form->addField("Proveido de Atenci&oacute;n: <font color=red>*</font>",textField("Proveido","Sx_desp_proveido",'',100,150));

        $form->addHtml("<tr><td colspan=2><div id='divBtnManten' >\n"); //pide datos judiciales
        $form->addHtml(btnManten(2,1,'','divBtnManten'));
        $form->addHtml("</div></td></tr>\n");

        $form->addHtml("<tr><td colspan=2>&nbsp;</td></tr>\n"); //pide datos de afectacion presupuestal

        $form->addHtml("<tr><td colspan=2><div id='divDerivacion'>\n"); //pide datos de afectacion presupuestal
        if(strlen($id)){
            $form->addHtml(verCarrito(2));
        }
        $form->addHtml("</div></td></tr>\n");
 } 

$form->addHtml("<tr><td colspan=2><div id='divEditor' >\n"); //muestra numero de documento
$form->addHtml(getEditor(2,"$bd_tiex_id","$bd_desp_contenido","$bd_tabl_tipodespacho","$readonly",'divEditor'));
$form->addHtml("</div></td></tr>\n");
$form->addHtml("<tr><td colspan=2><div id='divContenido' style=\"display:none\"></div></td></tr>\n");


$dialog=new Dialog("myModalScreen","screen");
$dialog->setModal("modal-ms");//largo
$form->addHtml($dialog->writeHTML());

$dialogFirma=new Dialog("myModalFirma","screen");
if(MOTOR_FIRMA=='2'){//REFIRMA
    $dialogFirma->setModal("modal-lg");//largo
    $dialogFirma->setCloseModal();
    $dialogFirma->addObjets("<iframe id=\"myIframe\" scrolling=\"no\" frameborder=\"0\" marginwidth=\"0\" marginheight=\"0\" width=\"100%\" height=\"40%\" allowfullscreen></iframe>");       
}else{
    $dialogFirma->setModal("modal-sm");//largo
    $dialogFirma->setCloseModal();
    $dialogFirma->addObjets("<iframe id=\"myIframe\" scrolling=\"no\" frameborder=\"0\" marginwidth=\"0\" marginheight=\"0\" width=\"100%\" height=\"20%\" allowfullscreen></iframe>");    
}
$form->addHtml($dialogFirma->writeHTML());

$dialogAviso=new Dialog("myModalAviso","warning");
$dialogAviso->setModal("modal-sm");//mediano
$form->addHtml($dialogAviso->writeHTML());        

$dialog=new Dialog("myModalConfirm","confirm");
$dialog->setModal("modal-sm");//mediano
$form->addHtml($dialog->writeHTML());        

$lectorPDF=new lectorPDF();
$form->addHtml($lectorPDF->writeHTML());

if(strlen($id)) {
//solo si es edicion se agrega los datos de auditoria
    $form->addBreak("<b>Control</b>");
    $form->addField("Creado por: ",$username);
    //$form->addField("Actualizado por: ",$usernameactual);
}
        
echo $form->writeHTML();

if(($cuenta_archivados!=$cuenta_derivaciones) or $cuenta_derivaciones==0 or !$id){
    //echo $button->writeHTML();
}else{
    echo "<br>";
}
    echo "<br>";
    echo "<br>";
    
?>

<div id="myModalImp" class="modal fade">
        <div class="modal-dialog modal-mg">
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

    <style>
    .pdfobject-container { height: 30rem; border: 1rem solid rgba(0,0,0,.1); }
    </style>

    <script>

        $('.my_select_box').select2({
            placeholder: 'Seleccione un elemento de la lista',
            allowClear: true,
            width: '90%',
        });
            
        mySelect();
        
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
                    return 'Searching...';
                  }
                }
              });

        }

            
        function getURL() {
            if($("#focus_select").val()=='Procedencia'){
                if($("#frm input[id='Tipo_de_tramite']:checked").val()==140){//INSTITUCIONAL
                    return '../catalogos/jswDependenciasJefeAjax.php';                    
                }else if($("#frm input[id='Tipo_de_tramite']:checked").val()==141){//PERSONAL
                    return '../catalogos/jswDependenciasPersonaAjax.php';                    
                }    
            }
            else if($("#focus_select").val()=='Destinatario' || $("#focus_select").val()=='mas_firmas_jefes' || $("#focus_select").val()=='mas_vistos_jefes'){
                return '../catalogos/jswDependenciasJefeTodosAjax.php';                                    
            }else if($("#focus_select").val()=='destinatarioEmpleados' || $("#focus_select").val()=='mas_firmas_empleados'){
                    return '../catalogos/jswDependenciasEmpleados2Ajax.php';
                }else if ($("#focus_select").val()=='mas_firmas_externos'){
                        return '../personal/jswPersonasExternas.php';
                    }else if($("#focus_select").val()=='Dependencia_Destino'){
                        if($("#Tipo_destino").val()==1){ //dependencias
                            return '../catalogos/jswDependenciasAjax.php';
                        }else if($("#Tipo_destino").val()==2){ //empleado){
                            return '../catalogos/jswDependenciasEmpleadosAjax.php';
                        }else{
                            return '../gestdoc/jswGruposaAjax.php';
                        }
                    }
                    

//              if($("#tipo_ajax").val()==1){ //dependencias
//                return '../catalogos/jswDependenciasJefeAjax.php';
//              }else if($("#Tipo_destino").val()==2){ //dependencias){
//                return '../catalogos/jswDependenciasEmpleadosAjax.php';
//              }else{
//                  return '../gestdoc/jswGruposaAjax.php';
//              }
        }
          
          
            window.jQuery(document).on('select2:open', e => {
              const id = e.target.id;
              $("#focus_select").val(id);             
              //const target = document.querySelector(`[aria-controls=select2-${id}-results]`);
              //target.focus();
            });
            
        <?php 

        if($bd_desp_vistos){
            echo "$('#mas_vistos_jefes').val([$bd_desp_vistos]).trigger('change');";
        }
        
        if($bd_desp_firmas_jefes){
            echo "$('#mas_firmas_jefes').val([$bd_desp_firmas_jefes]).trigger('change');";
        }
        
        if($bd_desp_vistos_empleados){
            echo "$('#mas_firmas_empleados').val([$bd_desp_vistos_empleados]).trigger('change');";
        }

        if($bd_desp_firmas_externos){
            echo "$('#mas_firmas_externos').val([$bd_desp_firmas_externos]).trigger('change');";
        }
        
        if($bd_desp_para_depe_id){       
            echo "$('#Destinatario').val([$bd_desp_para_depe_id]).trigger('change');";
        }

        if($bd_desp_para_pdla_id){
            echo "$('#destinatarioEmpleados').val([$bd_desp_para_pdla_id]).trigger('change');";
        }        
        
        if($desp_ocultar_editor==0 && $bd_desp_estado==1){//cerrado
        ?>
                    // Replace the <textarea id="editor1"> with a CKEditor
                    // instance, using default configuration.
                    CKEDITOR.replace( 'K__desp_contenido', {
                                        filebrowserBrowseUrl: '../../library/ckfinder/ckfinder.html',
                                        filebrowserUploadUrl: '../../library/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files'
                                    });
        <?php        
        }
        if($op==1){ ?>
            $("#respuesta_proceso").html('<div class="alert alert-success alert-styled-left alert-arrow-left alert-bordered text-center">\
                                                <button type="button" class="close" data-dismiss="alert"><span>X</span><span class="sr-only">Close</span></button>\
                                                <h4><b>NUMERO DE <?php echo NAME_EXPEDIENTE_UPPER.' '.$id ?></b></h4>\
                                            </div>');
        <?php                                    
        }
        
        
        if($bd_tabl_tipodespacho==141 && $bd_tiex_id==14){//SOLICITUD SIMPLE
            echo "$('#divMensajejefeInmediato').hide()";
        }   
        
        ?>
        
        $('.normal').autosize({append:''});
        
        
        
//        $(document).ready(function(){
//
//            $('a').on('mousedown', stopNavigate);
//
//            $('a').on('mouseleave', function () {
//                   $(window).on('beforeunload', function(){
//                          return 'Are you sure you want to leave?';
//                   });
//            });
//        });
//
//        function stopNavigate(){    
//            $(window).off('beforeunload');
//        }
        
    </script>
    

</body>
   
</html>

<?php
/* cierro la conexiÃ³n a la BD */
$conn->close();
unset($_SESSION["ocarrito"]);