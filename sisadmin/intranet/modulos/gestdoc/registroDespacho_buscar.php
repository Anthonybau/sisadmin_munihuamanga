<?php
/* Modelo de página que apresenta um formulario con criterios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("registroDespacho_class.php");
include("../catalogos/catalogosDependencias_class.php");
include("../admin/adminUsuario_class.php");
include("../catalogos/catalogosTipoExpediente_class.php");
include("registroDespacho_edicionAdjuntosClass.php");
include("registroDespachoEnvios_class.php");

/*elimino la variable de session */
if (isset($_SESSION["ocarrito"])) unset($_SESSION["ocarrito"]);

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$busEmpty = 1; //getParam("busEmpty"); // para definir si se muestran los datos en el primer ingreso
$depe_id=getParam("nbusc_depe_id");
$user_id=getParam("nbusc_user_id");

$param= new manUrlv1();
$param->removePar('clear');

$myClass = new despacho(0,NAME_EXPEDIENTE." Registrados");


if ($clear==1) {
	setSession("cadSearch","");
        $depe_id=getSession("sis_depeid");
        $user_id=getSession("sis_userid");
}

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "despacho","buscar"),"");
$xajax->registerExternalFunction(array("verDetalle", "despacho","verDetalle"),"");
$xajax->registerExternalFunction(array("clearDiv", "despacho","clearDiv"),"");
$xajax->registerFunction("getUsuarios");
$xajax->registerFunction("imprimir");
$xajax->registerFunction("getClonar");
$xajax->registerFunction("getSecuencia");
$xajax->registerFunction("clonar");
$xajax->registerFunction("enviar_email");
$xajax->registerFunction("listaHistorialEnvios");
$xajax->registerFunction("seguir");

function getUsuarios($op,$depe_id,$user_id){

    global $conn;
    
        $objResponse = new xajaxResponse();
        //$objResponse->addAlert($tipoDespacho);
        $oForm = new AddTableForm();
        $oForm->setLabelWidth("20%");
        $oForm->setDataWidth("80%");

        $es_jefe=new dependenciaJefe_SQLlista();
        $es_jefe->whereID($depe_id);
        $pdla_id=getDbValue("SELECT pdla_id FROM personal.persona_datos_laborales WHERE depe_id=$depe_id AND pers_id IN (SELECT pers_id FROM  personal.persona_datos_laborales WHERE pdla_id IN (SELECT pdla_id FROM admin.usuario WHERE usua_id=".getSession("sis_userid").")) ");        
        $es_jefe->wherePdlaID($pdla_id);
        $es_jefe->setDatos();
                
        
        if(($depe_id>0 && $es_jefe->existeDatos()) || getSession("sis_userid")==1){
            $usuarios=new clsUsersDatosLaborales_SQLlista();
            $usuarios->whereDepeID($depe_id);
            $usuarios->whereActivo();
            
            if (getSession("sis_userid")==1){
                
                $sqlUsuarios.= " SELECT a.usua_id,a.usua_login||' '||empleado
				 FROM (".$usuarios->getSQL().") AS a
                                 UNION ALL 
                                 SELECT usua_id, 'ADMIN ADMIN' 
                                    FROM admin.usuario 
                                    WHERE usua_id=1 
                                    AND usua_id NOT IN (SELECT x.usua_id FROM (".$usuarios->getSQL().") AS x) 
                                 ORDER BY 1";
            } else {
                $sqlUsuarios=$usuarios->getSQL_cbox();
            }
            
        }elseif($user_id>1){
            
            $usuarios=new clsUsers_SQLlista();
            $usuarios->whereID($user_id);            
            $sqlUsuarios=$usuarios->getSQL_cbox2();
            
        }

//       $usuarios->whereDepeID($depe_id);
        $oForm->addField(" Usuario: ",listboxField("Usuario",$sqlUsuarios,"nbusc_user_id",$user_id,"--Seleccione Usuarui--","","","class=\"my_select_box\"")); 


        $contenido_respuesta=$oForm->writeHTML();
	$objResponse->addAssign('divUsuarios','innerHTML', $contenido_respuesta);

        if($op==1){
            $objResponse->addScript("$('.my_select_box').select2({
                                        placeholder: 'Seleccione un elemento de la lista',
                                        width:'90%',
                                        allowClear: true
                                    })");
            
            return $objResponse;
        }
	else
            return $contenido_respuesta	;
}

function getClonar($registro_seleccionado,$nameDiv){
    global $bd_usua_id;
    $objResponse = new xajaxResponse();

    if(!$registro_seleccionado){
        $objResponse->addAlert("Sin registros seleccionados para proceder...");
    }else{
            //$form = new Form("frmClonar", "", "POST", "controle", "100%",false);
            $form = new AddTableForm();
            $form->setLabelWidth("20%");
            $form->setDataWidth("80%");
            
            $despacho=new despacho_SQLlista();
            $despacho->whereID($registro_seleccionado);
            $despacho->setDatos();
            $bd_tiex_id=$despacho->field('tiex_id');
            
            $form->addField("ORIGEN:",$registro_seleccionado);
                    
            $texp=new clsTipExp_SQLlista();
            $texp->whereNOAbreviado("'SB','SS','SSM'");
            $texp->orderUno();
            $sqltipo=$texp->getSQL_cbox2();
            $form->addField("Nuevo Tipo de Documento:",listboxField("Nuevo Tipo de Documento",$sqltipo,"tr_tiex_id",$bd_tiex_id,"-- Seleccione Tipo de Documento --","onChange=\"xajax_getSecuencia(this.value);\"","","class=\"my_select_box\"" ));
            //$form->addField(checkboxField("mover_adjuntados","hx_mover_adjuntados",1,0),"Mover Expedientes Adjuntados");
            $form->addField(checkboxField("mover_archivos","hx_mover_archivos",1,0),"Mover Archivos y Anexos ");
            
            /* botones */
            $button = new Button;
            //$button->addItem(" PROCEDER ","javascript:if(document.frmAdjuntar.nx_adjuntar.value==''){alert('Seleccione un registro')}else{ if(confirm('Seguro de adjuntarlos en este registro?')) {xajax_guardar_adjuntar(document.frmAdjuntar.nx_adjuntar.value,'$list_registros_seleccionados')}}","content",2,$bd_usua_id,'botao','button');
            
            $button->addItem(" PROCEDER ","$('#btn-proeder').hide();xajax_clonar('$registro_seleccionado',document.frm.tr_tiex_id.value,document.frm.mover_archivos.checked)","content","","","","button","","btn-proeder");
            $button->align('L');
            $form->addField("",$button->writeHTML());
            $contenido_respuesta=$form->writeHTML();
            
            $objResponse->addAssign($nameDiv,'innerHTML', $contenido_respuesta);
            $objResponse->addScript("$('#myModalOpc').modal('show');");
            $objResponse->addScript("$('.my_select_box').select2({
                                        placeholder: 'Seleccione un elemento de la lista',
                                        width:'90%',
                                        allowClear: true
                                    })");
        }
        
    return $objResponse;    
}

function getSecuencia($tipoExpediente){

        $objResponse = new xajaxResponse();

        if($tipoExpediente>0){
            $objResponse->addScript("$('#mover_adjuntados').prop('checked', 1)");
            $objResponse->addScript("$('#mover_archivos').prop('checked', 1)");
        }
        
        return $objResponse;
}


function clonar($id_ant,$tiex_id,$mover_archivos)
{
    global $conn;
    $objResponse = new xajaxResponse();
    
    $despacho=new despacho_SQLlista();
    $despacho->whereID("$id_ant");
    $despacho->setDatos();
    $tabl_tipodespacho=$despacho->field('tabl_tipodespacho');
    $depe_id=$despacho->field('depe_id');
    $desp_numero=$despacho->field('desp_numero');
    
    $desp_adjuntados_id=$despacho->field('desp_adjuntados_id');
    $desp_adjuntados_exp=$despacho->field('desp_adjuntados_exp');
    

    $hoy=date('d/m/Y');
    $periodo=date('Y');
    $usua_id=getSession("sis_userid");
                
        switch($tabl_tipodespacho){
            case 140://institucional
                $tipo_expediente_secuencia=getDbValue("SELECT tiex_secuencia FROM catalogos.tipo_expediente WHERE tiex_id=$tiex_id");
                
                $secuencia="gestdoc.corr_exp_".$tipo_expediente_secuencia."_".$periodo."_".$depe_id."_".$tiex_id;

                $numDocum=$conn->currval($secuencia);
                if($numDocum==0){ /* Si la secuencia no está creada */
                    $conn->nextid($secuencia); /* Creo la secuencia */
                    $numDocum=1; /* Asigno el número 1 */
                }
                $desp_numero=$numDocum;
            break;

            case 141://personal
                $tipo_expediente_secuencia=getDbValue("SELECT tiex_secuencia FROM catalogos.tipo_expediente WHERE tiex_id=$tiex_id");
                
                $secuencia="gestdoc.corr_exp_".$tipo_expediente_secuencia."_".$periodo."_".$tiex_id."_".$usua_id;

                $numDocum=$conn->currval($secuencia);
                if($numDocum==0){ /* Si la secuencia no está creada */
                    $conn->nextid($secuencia); /* Creo la secuencia */
                    $numDocum=1; /* Asigno el número 1 */
                }
                $desp_numero=$numDocum;

                break;

            case 142://otras entidades
                $secuencia='';
                $desp_numero=$desp_numero+1;
                break;
        }
                
    
        
    $sql = "INSERT INTO gestdoc.despachos 
                                   (desp_secuencia_automatica,
                                    proc_id,
                                    depe_id, 
                                    usua_id,
                                    tiex_id, 
                                    plde_id,
                                    desp_numero, 
                                    desp_siglas, 
                                    tabl_tipodespacho, 
                                    desp_fecha, 
                                    desp_asunto, 
                                    desp_firma, 
                                    desp_cargo, 
                                    tabl_modorecepcion, 
                                    desp_folios, 
                                    desp_proyectadopor, 
                                    /*desp_expediente,*/ 
                                    desp_notas, 
                                    desp_exp_legal, 
                                    desp_demandante, 
                                    desp_demandado, 
                                    desp_resolucion, 
                                    prat_id,
                                    desp_modingreso,
                                    exle_id,
                                    desp_para,
                                    desp_ccopia,
                                    desp_contenido,
                                    desp_procesador,
                                    pdla_firma,
                                    desp_proyectado,
                                    desp_vb,
                                    desp_exterior,
                                    desp_vistos,
                                    depe_idproyecta,
                                    desp_especbreve,
                                    desp_referencia,
                                    desp_para_destino,
                                    desp_para_cargo,
                                    desp_para_dependencia,
                                    desp_para_depe_id,
                                    desp_set_derivados,
                                    desp_para_pdla_id ";
    
//                        if($mover_adjuntados=='true' ||  $mover_adjuntados==1){
//                            $sql .= "   ,
//                                        desp_adjuntados,
//                                        desp_adjuntados_id,
//                                        desp_adjuntados_exp ";
//                        }    
                        
                            $sql .=  "                            ) 
                                SELECT  desp_secuencia_automatica,
                                        proc_id,
                                        depe_id,
                                        $usua_id,
                                        $tiex_id,
                                        plde_id,
                                        $desp_numero, 
                                        desp_siglas, 
                                        tabl_tipodespacho, 
                                        '$hoy'::DATE, 
                                        desp_asunto, 
                                        desp_firma, 
                                        desp_cargo, 
                                        tabl_modorecepcion, 
                                        desp_folios, 
                                        desp_proyectadopor,
                                        /*CASE WHEN desp_expediente_control IS NOT NULL THEN NULL 
                                             ELSE desp_expediente 
                                        END,*/ 
                                        desp_notas, 
                                        desp_exp_legal, 
                                        desp_demandante, 
                                        desp_demandado, 
                                        desp_resolucion, 
                                        prat_id,
                                        desp_modingreso,
                                        exle_id,
                                        desp_para,
                                        desp_ccopia,
                                        desp_contenido,
                                        desp_procesador,
                                        pdla_firma,
                                        desp_proyectado,
                                        desp_vb,
                                        desp_exterior,
                                        desp_vistos,
                                        depe_idproyecta,
                                        desp_especbreve,
                                        desp_referencia,
                                        desp_para_destino,
                                        desp_para_cargo,
                                        desp_para_dependencia,
                                        desp_para_depe_id,
                                        0,
                                        desp_para_pdla_id ";
                                        
//                            if($mover_adjuntados=='true' ||  $mover_adjuntados==1){
//                                $sql .= "   ,
//                                            desp_adjuntados,
//                                            desp_adjuntados_id,
//                                            desp_adjuntados_exp ";
//                            }
                            
    $sql .= "                   FROM gestdoc.despachos
                                 WHERE desp_id='$id_ant'
                                 RETURNING desp_id ";
    
    $padre_id=$conn->execute($sql);
    $error=$conn->error();
    if($error){
        $conn-> rollback();
        $objResponse->addAlert($error);
        return $objResponse;
    }else{
        if($padre_id){
            
            if($secuencia){
                $conn->setval($secuencia,intval($desp_numero)+1);
            }
            
//            $sql="INSERT INTO gestdoc.despachos_derivaciones ( depe_idorigen, 
//                                                               usua_idorigen, 
//                                                               desp_id, 
//                                                               depe_iddestino, 
//                                                               usua_iddestino, 
//                                                               dede_concopia, 
//                                                               dede_proveido, 
//                                                               usua_idcrea ";
//            
//            if($mover_adjuntados=='true' ||  $mover_adjuntados==1){
//                $sql.=" ,desp_adjuntadoid ";
//            }
//            
//            $sql.="                                                   ) 
//                                                             SELECT depe_idorigen, 
//                                                               usua_idorigen, 
//                                                               $padre_id, 
//                                                               depe_iddestino, 
//                                                               usua_iddestino, 
//                                                               dede_concopia, 
//                                                               dede_proveido, 
//                                                               $usua_id ";
//
//            if($mover_adjuntados=='true' ||  $mover_adjuntados==1){
//                $sql.=" ,desp_adjuntadoid ";
//            }
//            
//            $sql.="            
//                                        FROM gestdoc.despachos_derivaciones
//                                            WHERE desp_id='$id_ant';";

            
//            if($mover_adjuntados=='true' ||  $mover_adjuntados==1){
//                $sql.=" UPDATE gestdoc.despachos  
//                            SET desp_adjuntados_exp=null,
//                                desp_adjuntados_id=null,
//                                desp_adjuntados=0
//                         WHERE desp_id='$id_ant';
//                         
//                        UPDATE despachos_derivaciones
//                                SET desp_adjuntadoid=NULL
//                                WHERE desp_adjuntadoid='$id_ant';
//                        ";
//                
//                if($desp_adjuntados_id){
//                    $sql.="    UPDATE despachos_derivaciones
//                                    SET desp_adjuntadoid=$padre_id
//                                    WHERE desp_id!=$padre_id 
//                                        AND dede_id IN ($desp_adjuntados_id);                                                            
//                                ";
//                }
//            }
            
//            $conn->execute($sql);
//            $error=$conn->error();
//            if($error){
//                $objResponse->addAlert($error);
//                return $objResponse;
//            }else{
                //SI SOLICITA COPIAR ARCHIVOS

                if($mover_archivos=='true' ||  $mover_archivos==1){

                        //CARGO LOS ARCHIVOS ADJUNTOS
                        $sql=new despachoAdjuntados_SQLlista();
                        $sql->wherePadreID("$id_ant");
                        $sql = $sql->getSQL();

                        $rsFiles = new query($conn, $sql);
                        if($rsFiles->numrows()>0){
                            $id=$padre_id;
                            include('./makeDirectory.php');    
                            
                            while ($rsFiles->getrow()) {
                                $file=$rsFiles->field("area_adjunto");
                                $periodo=$rsFiles->field('desp_anno');
                                $path_file_origen=PUBLICUPLOAD."gestdoc/".SIS_EMPRESA_RUC."/$periodo/$id_ant/$file";

                                $nvo_file="df_".$padre_id."_".$file;
                                $path_file_destino=PUBLICUPLOAD."gestdoc/".SIS_EMPRESA_RUC."/$periodo/$id/$nvo_file";

                                if(copy($path_file_origen, $path_file_destino)){

                                        $sql = new UpdateSQL();
                                        $sql->setTable("gestdoc.despachos_adjuntados");
                                        $sql->setKey("dead_id","","Number");
                                        $sql->setAction("INSERT"); /* Operación */                                

                                        $sql->addField('desp_id',$padre_id, "Number");
                                        $sql->addField('dead_descripcion',$rsFiles->field('dead_descripcion'), "String");
                                        $sql->addField('area_adjunto',$nvo_file, "String");
                                        $sql->addField('dede_id',$rsFiles->field('dede_id'), "Number");
                                        $sql->addField('dead_signer',$rsFiles->field('dead_signer'), "Number");
                                        $sql->addField('dead_zip',$rsFiles->field('dead_zip'), "Number");
                                        $sql->addField('usua_id',$usua_id, "Number");                    
                                        $sql=$sql->getSQL();
                                        $return=$conn->execute($sql); 
                                        $error=$conn->error();
                                        if($error){
                                            $objResponse->addAlert($error);
                                            return $objResponse;
                                        }
                                }
                            }
//                        }                    
                }
            }
        }
    }

    $objResponse->addScript("parent.content.location.reload()");
    return $objResponse;
}

function imprimir($form){

    $objResponse = new xajaxResponse();
    $regSeleccionado='';

    $array=$form['sel'];
    if(!is_array($array)){
        $objResponse->addAlert('No existen datos para procesar, probablemente no ha seleccionado registro...!');
    }else{

        $objResponse->addScript("AbreVentana('rptHT.php?id=".$array[0]."')");
    }
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
	<script language="javascript" src="../../library/js/checkall.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>

        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>
        
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>
        
        
        <style>
            #lectorPDF{
              width: 95% !important;
            }
        </style>

        
	<script language="JavaScript">

        function inicializa() {
                document.frm.Sbusc_cadena.focus();
        }

        function AbreVentana(sURL){
                var w=720, h=600;
                venrepo=window.open(sURL,'rptSalida', "status=yes,resizable=yes,toolbar=no,scrollbars=yes,top=50,left=150,width=" + w + ",height=" + h, 1 );
                venrepo.focus();
        }

        function excluir() {
            if (confirm('Eliminar registros seleccionados?')) {
                    parent.content.document.frm.target = "controle";
                    parent.content.document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(2)."&".$param->buildPars(false)?>";
                    parent.content.document.frm.submit();
             }
        }

        function beforeClonar(){
            regSel=$("#registados input[type=checkbox]").is(":checked");
            if(regSel){
                var checked = $("input[name='sel[]']:checked").val();
                jClonar(checked);
            } else {
                alert('Seleccione un registro');
            }
        }
        
        function jClonar(checked){
                $("#title-myModalOpc").addClass("glyphicon glyphicon-duplicate");
                $("#title-myModalOpc").html("&nbsp;<b>CLONAR</b>");
                xajax_getClonar(checked,'msg-myModalOpc');
        }
                
//        function clonar() {
//            if (confirm('Seguro de clonar registro seleccionado?')) {
//                    parent.content.document.frm.target = "controle";
//                    parent.content.document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(3)."&".$param->buildPars(false)?>";
//                    parent.content.document.frm.submit();
//             }
//        }
        
        
        function beforeEnviaEmail(desp_id,desp_expediente) {
            $("#title-myModalImp").addClass("glyphicon glyphicon-envelope");
            $("#title-myModalImp").html("");
            xajax_enviar_email(1,desp_id,desp_expediente,'msg-myModalImp');
            $('#myModalImp').modal('show');
        }         


        function abreConsulta(id) {
            AbreVentana('../../../portal/gestdoc/consultarTramiteProceso.php?nr_numTramite=' + id+'&vista=NoConsulta');
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

/* botones  */
$button = new Button;
$button->addItem("Imprimir Hoja de Tr&aacute;mite","javascript:xajax_imprimir(xajax.getFormValues('frm'))","content",2);
$button->addItem(" Clonar ","javascript:beforeClonar()","content");
/*
$button->addItem(" Nuevo ","catalogosArchivadores_edicion.php".$param->buildPars(true),"content");
$button->addItem("Eliminar","javascript:excluir()","content",2);
 */
echo $button->writeHTML();


?>
<div align="center">
<!-- Lista -->
<form name="frm" id="frm" method="post">    
<?php

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
$paramFunction->addParComplete('nbusc_depe_id',0);
$paramFunction->addParComplete('nbusc_user_id',$user_id);


/* Instancio la Dependencia */
$dependencia=new dependencia_SQLlista();
if(getSession("sis_userid")>1){
    $dependencia->whereVarios(getSession("sis_persid"));
    //$dependencia->whereID($depe_id);
    $todos="--Seleccione Dependencia--";
}else{
    $todos="--Seleccione Dependencia--";
}

$sqlDependencia=$dependencia->getSQL_cbox();
//FIN OBTENGO
$form->addField("Dependencia: ",listboxField("Dependencia.",$sqlDependencia,"nbusc_depe_id","","$todos","onChange=\"xajax_getUsuarios(1,this.value,document.frm.nbusc_user_id.value,'".encodeArray($paramFunction->getUrl())."','".encodeArray($paramFunction->getUrl())."');xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\"","","class=\"my_select_box\"")); 

$form->addHtml("<tr><td colspan=2><div id='divUsuarios' >\n"); //pide datos de afectacion presupuestal
$form->addHtml(getUsuarios(2,$depe_id,"$user_id"));
$form->addHtml("</div></td></tr>\n");

$texp=new clsTipExp_SQLlista();
$texp->orderUno();
$sqltipo=$texp->getSQL_cbox();
$form->addField("Tipo de Documento:",listboxField("Tipo de Documento",$sqltipo,"nbusc_tiex","","-- Todos --","","","class=\"my_select_box\"")); 

$form->addField("Exp/N&uacute;m.".NAME_EXPEDIENTE."/Asunto: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">");

$form->addHtml("<tr><td colspan=2>");
$form->addHtml("<table width=\"100%\"><tr valign=\"top\">");
$form->addHtml("<td><div id='DivResultado'>\n");
$form->addHtml($myClass->buscar(2,'',encodeArray($paramFunction->getUrl()),1,'DivResultado'));
$form->addHtml("</div></td>");
$form->addHtml("<td><div id='DivDetalles'>");
$form->addHtml("</div></td>");
$form->addHtml("</tr></table>\n");
$form->addHtml("</td></tr>");


$lectorPDF=new lectorPDF();
$form->addHtml($lectorPDF->writeHTML());

echo  $form->writeHTML();
?>
    
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
    

<div id="myModalImp" class="modal fade">
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
    
</body>
<script>
        $(".my_select_box").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true,
            width:'90%',
        });        
</script>
</html>
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();