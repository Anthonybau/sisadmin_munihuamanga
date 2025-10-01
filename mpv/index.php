<?php
    include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
    //include("recaptcha/keys.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
        
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
	<title>Mesa de Partes Virtual</title>

	<!-- Global stylesheets -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
	<link href="assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
	<link href="assets/css/icons/fontawesome/styles.min.css" rel="stylesheet" type="text/css">
	<link href="assets/css/bootstrap.css" rel="stylesheet" type="text/css">
	<link href="assets/css/core.css" rel="stylesheet" type="text/css">
	<link href="assets/css/components.css" rel="stylesheet" type="text/css">
	<link href="assets/css/colors.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="assets/css/modal-custom.css">
    <!-- <link rel="stylesheet" href="assets/css/layout/page-footer.css"> -->
	<!-- /global stylesheets -->

	<!-- Core JS files -->
	<script type="text/javascript" src="assets/js/plugins/loaders/pace.min.js"></script>
	<script type="text/javascript" src="assets/js/core/libraries/jquery.min.js"></script>
	<script type="text/javascript" src="assets/js/core/libraries/bootstrap.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/loaders/blockui.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/ui/nicescroll.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/ui/drilldown.js"></script>
	<!-- /core JS files -->

	<!-- Theme JS files -->
	<script type="text/javascript" src="assets/js/plugins/forms/selects/select2.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/forms/styling/uniform.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/forms/inputs/touchspin.min.js"></script>
        <script type="text/javascript" src="js/documentoelectronico_interaccion.js?i=<?php echo rand(); ?>"></script>	
	<!-- /theme JS files -->
        <!--script src='https://www.google.com/recaptcha/api.js?render=<?php echo SITE_KEY; ?>'></script-->
</head>


<body class="navbar-bottom">
	
	<style type="text/css" media="screen">
                .stepwizard-step p {
                    margin-top: 10px;
                }
                .stepwizard-row {
                    display: table-row;
                }
                .stepwizard {
                    display: table;
                    width: 100%;
                    position: relative;
                }
                .stepwizard-step button[disabled] {
                    opacity: 1 !important;
                    filter: alpha(opacity=100) !important;
                }
                .stepwizard-row:before {
                    top: 14px;
                    bottom: 0;
                    position: absolute;
                    content: " ";
                    width: 100%;
                    height: 1px;
                    background-color: #ccc;
                    z-order: 0;
                }
                .stepwizard-step {
                    display: table-cell;
                    text-align: center;
                    position: relative;
                }
                .btn-circle {
                    width: 30px;
                    height: 30px;
                    text-align: center;
                    padding: 4px 0;
                    font-size: 12px;
                    line-height: 1.428571429;
                    border-radius: 15px;
                    border: 3px solid #229688;
                } 
                
                .btn-success, .btn-success:hover, .btn-success:active, .btn-success:visited {
                    background-color: #005B89 !important;
                }
                
                hr.new5 {
                    border: 5px solid #455a64;
                    border-radius: 5px;
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
                    margin-top: -8px;
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
                    margin-top: -8px;
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

        
        <header>
            <?php 
            $conn = new db();
            $conn->open();
            include("layout/page-header.php"); 
            ?>
        </header>            
            
            <?php
            
            if(isset($_GET['id'])){
                $id=$_GET['id'];
                $depe_id=base64_decode($id);
                $depe_id=intval($depe_id);
            }else{
                $depe_id=2; //SEDE por default
            }            
            
            $hora_activo=getDbValue("SELECT CASE WHEN 
                                                    CASE WHEN date_part('dow',current_date) BETWEEN 1 AND 5  /*LUNES*/ THEN  to_char(current_timestamp, 'HH24:MI:SS')::TIME BETWEEN depe_lunes_viernes_desde AND depe_lunes_viernes_hasta
                                                         WHEN date_part('dow',current_date)=6 THEN to_char(current_timestamp, 'HH24:MI:SS')::TIME BETWEEN depe_sabado_desde AND depe_sabado_hasta 				
                                                         WHEN date_part('dow',current_date)=0 /*DOMINGO*/ THEN to_char(current_timestamp, 'HH24:MI:SS')::TIME BETWEEN depe_domingo_desde AND depe_domingo_hasta 
                                                    END=TRUE THEN 1
                                            ELSE 0
                                     END
                                     FROM catalogos.dependencia
                                     WHERE depe_id=CASE WHEN $depe_id>0 THEN $depe_id ELSE 2 END
                                     LIMIT 1
                                     ");

            if(isset($_GET['pass'])){
                $pass=$_GET['pass'];
                $array = explode("_",$pass);
                $id_rand = base64_decode($array[0]);
                $desp_id = base64_decode($array[1]);
                
                include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/modulos/gestdoc/registroDespacho_class.php");
                
                $despacho=new despacho_SQLlista();
                $despacho->whereID($desp_id);
                $despacho->whereIDRand($id_rand);
                $despacho->notificacionEstado(1);//SI HA SIDO NOTIFICADO PAA CORRECCIONES
                $despacho->setDatos();
                
                if($despacho->existeDatos()){
                    $notificacion_estado=$despacho->field('desp_notificacion_estado');
                    $notiicacion_fecha_activo=$despacho->field('notificacion_activo');   
                    $procedimiento=$despacho->field('procedimiento');
                    $firma=$despacho->field('desp_firma');
                    $codigo=$despacho->field('desp_codigo');
                    $email=$despacho->field('desp_email');
                }else{
                    $notificacion_estado=2;//YA SE ATENDIO LA NOTIFICACION
                }
            }else{
                $notificacion_estado=0;//NO ES NOTIFICACION
            }
                        
            $sql="SELECT depe_mpv_mensaje_externo,
                         depe_video_mpv
                         FROM catalogos.dependencia
                         WHERE depe_id=CASE WHEN $depe_id>0 THEN $depe_id ELSE 2 END
                         ORDER BY depe_id
                         LIMIT 1
                    ";
            $rs = new query($conn, $sql);
            $rs->getrow();
            $bd_mensaje_mpv=$rs->field("depe_mpv_mensaje_externo");
            $bd_mensaje_mpv=str_replace(array("\r\n", "\n", "\r"),'<br>',$bd_mensaje_mpv);
            
            $bd_video_mpv=$rs->field("depe_video_mpv");
            $bd_anno=date('Y');
            
            ?>
	<!-- Page container -->
	<main class="page-container content-wrap">

		<!-- Page content -->
		<div class="page-content">

			<!-- Main content -->
			<div class="content-wrapper">

				<!-- content -->
				<div class="row">
					
                                   <?php 
                                   if($hora_activo==1){
                                   ?>    
                                    <div class="stepwizard col-md-12">
                                        <div class="stepwizard-row setup-panel">
                                          <div class="stepwizard-step">
                                            <a href="#step-1" type="button" class="btn btn-dark btn-circle">1</a>
                                            <p>Documento</p>
                                          </div>
                                          <div class="stepwizard-step">
                                            <a href="#step-2" type="button" class="btn btn-default btn-circle" disabled="disabled">2</a>
                                            <p>Identificación y Confirmación</p>
                                          </div>
                                        </div>
                                    </div>
                                    
					<!-- Documento Electrónico -->
                                        <form name="frm_mpartes_virtual" enctype='multipart/form-data' id="frm_mpartes_virtual" action="controllers/procesar_data.php" method="post" accept-charset="utf-8">
                                            
					<div class="row">
			                <div class="col-md-12" style="margin-bottom: 15px;">
			                    <div class="panel" style="max-width: 1100px; margin: 0 auto;">
			                        <div class="panel-body" id="cuerpo_comprobante">
                                                    
                                                    <?php
                                                        if($notificacion_estado==1 && $notiicacion_fecha_activo==1){//NOTIFFICADO PARA CORRECCION Y A TIEMPO
                                                     ?>
                                                            <legend class="text-bold">DOCUMENTO: </legend>
                                                            <div class="form-group col-md-12">
                                                            <label class="control-label"> Procedmiento: </label>
                                                                <?php
                                                                    echo "<p> ".$procedimiento . "</p>";
                                                                ?>                                                                            
                                                            </div>
                                                        
                                                            <div class="col-md-12">
                                                              <!--h3> Documento</h3-->
                                                                    <fieldset class="content-group">                                                    
                                                                        <div class="form-group col-md-12">
                                                                            <?php $max_filesize=ini_get('upload_max_filesize');?>
                                                                            <label class="control-label">Archivo: <b>(Máx <?php echo $max_filesize ?>)</b><span class="text-danger">*</span></label>
                                                                            <input type="file" title="Agregue Aqui Su Archivo" name="documento_archivo" id="documento_archivo" onchange="validaextension(this,'ZIP,PDF')" placeholder="Suba Aquí su Archivo" class="form-control documento_archivo" required="required">
                                                                            <p class="text-black text-justify"><span class="text-danger"><b>El Documento PDF debe contener las correcciones a las observaciones notificadas.</b></span></p>
                                                                        </div>
                                                                    </fieldset>
                                                            </div>

                                                            <legend class="text-bold">IDENTIFICACION: </legend>
                                                            <div class="form-group col-md-12">
                                                            <label class="control-label"> Persona / DNI/RUC: </label>
                                                                <?php
                                                                    echo "<p> ".$firma .' / '.$codigo. "</p>";
                                                                ?>                                                                            
                                                            </div>

                                                            <div class="form-group col-md-12">
                                                                  <label class="control-label"> Correo: <span class="text-danger">*</span></label>
                                                                  <input type="email" title="Ingresa Correo" name="persona_email" id="persona_email" value="<?php echo $email?>" placeholder="Correo Aquí" class="form-control persona_correo" required="required">
                                                            </div>
                                                            <legend class="text-bold">CONFIRMACION: </legend>
                                                            
                                                            <div class="form-group col-md-12">
                                                                <label class="control-label"> Código de Seguridad (ubiquelo en el correo de Notificación) : <span class="text-danger">*</span></label>
                                                                <input type="text" title="Ingresa el Código de Seguridad" name="documento_codigo_seguridad" id="documento_codigo_seguridad" placeholder="Escribe aquí el Código de Seguridad" class="form-control documento_codigo_seguridad"  required="required">
                                                            </div>
                                                            
                                                            
                                                            <div class="col-md-12 text-center" style="padding-bottom: 25px;">
                                                                <button id="btn_guardar_doc_electronico" type="submit" onClick="if(document.getElementById('documento_archivo').value != '' && document.getElementById('documento_codigo_seguridad').value != '') {$('#btn_guardar_doc_electronico').hide()}"   class="btn bg-indigo btn-labeled btn-xs legitRipple" ><b><i class="glyphicon glyphicon-send"></i></b> Enviar Correcciones </button>
                                                                <input type="hidden" id="token" name="pass" value="<?php echo $pass?>">
                                                            </div>
                                                                        
                                                     <?php
                                                        }elseif($notificacion_estado==0){//NO NOTIFICADO
                                                     ?>
                                                    
                                                    <div class="row setup-content" id="step-1">

                                                            <div class="col-md-12">
                                                              <!--h3> Documento</h3-->
                                                                    <fieldset class="content-group">
                                                                        
                                                                        <legend class="text-bold">DOCUMENTO: </legend>
                                                                                <?php if($depe_id==0){ ?>
                                                                        
                                                                                    <div class="form-group col-md-12">
                                                                                        <label class="control-label"> Seleccione Dependencia: <span class="text-danger">*</span></label>

                                                                                        <select title="Selecciona Dependencia" data-placeholder="Dependencia" class="select dependencia" name="tipo_dependencia" id="tipo_dependencia"  required="required">
                                                                                            <option value='0'>Pulse aqui para abrir lista </option>
                                                                                        </select>
                                                                                    </div>                                                                        
                                                                                
                                                                                <?php } else { 
                                                                                    echo "<input type='hidden' id='tipo_dependencia' name='tipo_dependencia' value=$depe_id>";
                                                                                }                                                                                
                                                                                ?>
                                                                                <input type="hidden" id="token" name="id" value="<?php echo $id?>">
                                                                            <?php 
                                                                            if(SIN_PROCEDIMIENTOS==1){
                                                                                echo "<input type='hidden' id='documento_tipo_tramite' name='documento_tipo_tramite' value=9999>";
                                                                            }else{
                                                                                ?>
                                                                                <div class="form-group col-md-12">
                                                                                        <label class="control-label"> Seleccione Procedimiento: <span class="text-danger">*</span></label>
                                                                                        <ul class="list-group" >
                                                                                            <li class="list-group-item">
                                                                                                &nbsp;&nbsp;Sin Procedimiento
                                                                                                <div class="TriSea-technologies-Switch pull-left">
                                                                                                    <input id="TriSeaDefault" name="TriSea1" type="checkbox" onChange="javascript:if(this.checked){$('#documento_tipo_tramite').val(9999).trigger('change')}else{$('#documento_tipo_tramite').val(0).trigger('change');}"/>
                                                                                                    <label for="TriSeaDefault" class="label-default"></label>
                                                                                                </div>
                                                                                            </li>
                                                                                        </ul>
                                                                                     

                                                                                    <select title="Selecciona Tipo de Procedimiento" data-placeholder="Tipo de Procedimiento" class="select documento_tipo_tramite" name="documento_tipo_tramite" id="documento_tipo_tramite"  required="required">
                                                                                        <option value='0'>Pulse aqui para abrir lista</option>
                                                                                    </select>

                                                                                    <div id=requisitos></div>
                                                                                </div>
                                                                                <?php
                                                                                }
                                                                                ?>
                                                                        
                                                                        <div class="form-group col-md-3">
                                                                                    <label> Tipo de Documento: <span class="text-danger">*</span></label>
                                                                                    <select title="Selecciona el Tipo de Documento" data-placeholder="Tipo de Documento" class="select documento_tipodocumento" name="documento_tipodocumento" id="documento_tipodocumento" required="required">
                                                                                    </select>
                                                                        </div>

                                                                        <div class="form-group col-md-2">								
                                                                                    <label> <span id="titulo_numerodocumentox">N° de Documento</span>: <span class="text-danger"></span></label>
                                                                                    <input type="number" title="Número de Documento" name="documento_numerodocumento" id="documento_numerodocumento" placeholder="Número Aquí!" class="form-control documento_numerodocumento" required="required">
                                                                        </div>

                                                                        <div class="form-group col-md-2">								
                                                                                    <label> <span id="titulo_annodocumento">Año</span>: <span class="text-danger"></span></label>
                                                                                    <input type="number" title="Año" name="documento_annodocumento" id="documento_annodocumento" placeholder="Año Aquí!" class="form-control documento_annodocumento" value=<?php echo $bd_anno ?> required="required">
                                                                        </div>
                                                                        <div class="form-group col-md-3">
                                                                                    <label> Siglas del Doc/Iniciales Pers: <span class="text-danger"></span></label>
                                                                                    <input type="text" title="Siglas del Documento" name="documento_siglas" id="documento_siglas" placeholder="Siglas del Documento Aquí!" class="form-control documento_siglas">

                                                                        </div>                                                                         
                                                                        <div class="form-group col-md-2">								
                                                                                    <label> <span id="titulo_numerofolios">N° de Folios</span>: <span class="text-danger">*</span></label>
                                                                                    <input type="number" title="Número de Folios" name="documento_numerofolios" id="documento_numerofolios" placeholder="Núm.de Folios Aquí!" class="form-control documento_numerofolios" required>
                                                                        </div>
                                                                        
                                                                        <div class="form-group col-md-12">
                                                                            <label class="control-label"> Asunto: <span class="text-danger">*</span></label>
                                                                            <input type="text" title="Ingresa el Asunto del Documento" name="documento_asunto" id="documento_asunto" placeholder="Escribe aquí el Asunto del Documento" class="form-control documento_asunto"  required="required">
                                                                        </div>
                                                                        
                                                                        <div class="form-group col-md-12">
                                                                            <?php 
                                                                            $extensiones_aceptadas="PDF,XLS,XLSX,DOCX,DOC,PPT,PPTX,ZIP";
                                                                            $max_filesize=ini_get('upload_max_filesize');
                                                                            ?>
                                                                            <label class="control-label">Archivo(s) en formato <?php echo $extensiones_aceptadas?><span class="text-danger"><br><b> (Máx <?php echo $max_filesize ?>)</b></span></label><br>
                                                                            <label class="control-label">Solicitud: <span class="text-danger">*</span></label>
                                                                            <input type="file" title="Agregue Aqui Su Archivo" name="documento_archivo" id="documento_archivo" onchange="validaextension(this,'<?php echo $extensiones_aceptadas?>')" placeholder="Suba Aquí su Solicitud" class="form-control documento_archivo" required="required">
                                                                            <p class="text-black text-justify"><b>El Archivo debe contener el escaneado del Documento Original Firmado por la Persona que hace la Solicitud.</b></p>
                                                                        </div>
                                                                        <div class="form-group col-md-12">
                                                                            <label class="control-label"> Anexos:<span class="text-danger"></span></label>
                                                                            <input type="file" title="Agregue Aqui Sus Requisitos y/o Anexos" name="documento_archivo2" id="documento_archivo2" onchange="validaextension(this,'<?php echo $extensiones_aceptadas?>')" placeholder="Suba Aquí sus Requisitos y/o Anexos" class="form-control documento_archivo2" >
                                                                            <p class="text-black text-justify"><b>Adjunte sus Requisitos y/o Anexos.</b></p>
                                                                        </div>                                                                        
<!--                                                                        <div class="form-group col-md-12">
                                                                            <label class="control-label"> Enlace(URL) para más Archivos: <span class="text-danger"></span></label>
                                                                            <input type="text" title="Ingresa un enlace con más Archivos" name="mas_files" id="mas_files" placeholder="Escribe aquí un enlace con más archivos" class="form-control mas_files" >
                                                                        </div>-->


                                                                    </fieldset>
                                                              <button class="btn btn-success nextBtn btn-sm pull-right" type="button" >Siguiente</button>

                                                          </div>
                                                        </div>
                                                    
                                                    
                                                        <div class="row setup-content" id="step-2">

                                                            <div class="col-md-12">
                                                              <!--h3> Step 2</h3-->
                                                               <fieldset class="content-group">

                                                                    <legend class="text-bold">IDENTIFICACION: </legend>
                                                                    <div class="col-md-3">
                                                                        <div class="radio">
                                                                        <label>
                                                                            <input type="radio" value="01" name="tipo_documento" class="control-success" checked="checked">
                                                                            Persona Natural
                                                                        </label>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-3">
                                                                        <div class="radio">
                                                                            <label>
                                                                            <input type="radio" value="02" name="tipo_documento" class="control-success">
                                                                            Persona Juridica
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="radio">
                                                                            <label>
                                                                            <input type="radio" value="03" name="tipo_documento" class="control-success">
                                                                            Carnet de Extranjería
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </fieldset>

                                                                <fieldset class="content-group">
                                                                    <legend class="text-bold">Persona: </legend>

                                                                    <div class="form-group col-md-3">
                                                                            <div class="form-group has-feedback has-feedback-left">
                                                                                <label class="control-label"> Tipo de Documento: <span class="text-danger">*</span></label>
                                                                                <select title="Selecciona el Tipo de Documento" data-placeholder="Tipo de Documento" class="select persona_tipodocumento" name="persona_tipodocumento" id="persona_tipodocumento" required="required">
                                                                                    <option value='1'>DNI</option>
                                                                                </select>
                                                                            </div>                        
                                                                    </div>

                                                                    <div class="form-group col-md-4">
                                                                            <button type="button" style="float: right; margin-top: 27px;" class="btn bg-indigo btn-icon legitRipple search_document"><i class="icon-search4" id="icon_search_document"></i><i class="icon-spinner10 spinner position-left" style="display: none;" id="icon_searching_document"></i></button>
                                                                            <div style="overflow: hidden; padding-right: .5em;">
                                                                                <label class="control-label"> <span id="titulo_numerodocumento" >N° de DNI</span>: <span class="text-danger">*</span></label>
                                                                                <input type="text" title="Número de Documento" name="persona_numerodocumento" id="persona_numerodocumento" placeholder="Número de Documento Aquí!" class="form-control persona_numerodocumento" required="required">
                                                                            </div>​
                                                                    </div>

                                                                    <div class="form-group col-md-5">
                                                                        <label class="control-label"> <span id="titulo_nombresolicitante">Nombres y Apellidos</span>: <span class="text-danger">*</span></label>
                                                                        <input type="text" title="Ingresa la Razón Social o Nombre" name="persona_nombre" id="persona_nombre" placeholder="Nombres y Apellidos o Razón Social Aquí" class="form-control persona_nombre"  required="required" readonly="true">
                                                                    </div>                                                        
                                                                </fieldset> 

                                                                <fieldset class="content-group">
                                                                    <legend class="text-bold">Contacto: </legend>
                                                                    <div class="form-group col-md-6">
                                                                        <label class="control-label"> Teléfono: <span class="text-danger">*</span></label>
                                                                        <input type="tel" title="Ingresa Teléfono" name="persona_telefono" id="persona_telefono" placeholder="Teléfono Aquí"  class="form-control persona_telefono" required="required">
                                                                    </div>                                                        

                                                                    <div class="form-group col-md-6">
                                                                        <label class="control-label"> Correo: <span class="text-danger">*</span></label>
                                                                        <input type="email" title="Ingresa Correo" name="persona_email" id="persona_email" placeholder="Correo Aquí" class="form-control persona_correo" required="required">
                                                                    </div>

                                                                </fieldset>

                                                                <fieldset class="content-group">
                                                                        <legend class="text-bold">CONFIRMACION: </legend>
                                                                        
                                                                        <div class="form-group col-md-12">
                                                                            <a href="terminos/Terminos y Condiciones MPV_<?=strtolower(SIS_EMPRESA_SIGLAS)?>.pdf" download="Terminos y Condiciones MPV">
                                                                                <i class="glyphicon glyphicon-file position-center"></i>Términos y condiciones, descarguelo en formato PDF
                                                                            </a><br>
                                                                            <input type="checkbox" id="acepto_terminos_condiciones" name="acepto_terminos_condiciones" value="1" required="required">
                                                                             <label for="confirmacion"> Acepto los términos y condiciones</label>
                                                                        </div>
                                                                        
                                                               </fieldset>
                                                              
                                                                <div class="col-md-12 text-center" style="padding-bottom: 25px;">
                                                                    <button id="btn_guardar_doc_electronico" type="submit" onClick="if(document.getElementById('acepto_terminos_condiciones').checked == true) {$('#btn_guardar_doc_electronico').hide()}"   class="btn bg-indigo btn-labeled btn-xs legitRipple" ><b><i class="glyphicon glyphicon-send"></i></b> Enviar Solicitud </button>
                                                                    <input type="hidden" id="token" name="token">
                                                                </div>


                                                          </div>
                                                        </div>
                                                    									                                                              
                                                    
                                                    <?php
                                                        }elseif($notificacion_estado==2){//NOTIICACION YA ATENDIDO
                                                            $mensaje="<br>Usted ya remitió sus correcciones!";                                                       
                                                            echo "<div class='row'>
                                                                    <div class='col-md-12' align=center>
                                                                        <h4> ".$mensaje."</h> 
                                                                    </div>
                                                                 </div>                       
                                                                 <br>
                                                                 <hr class='new5'>
                                                                 ";
                                                        }elseif($notificacion_estado==1 && $notiicacion_fecha_activo==0){//NOTIFFICADO PARA CORRECCION Y UEA DE PLAZO
                                                            $mensaje="Plazo vencido para remitir correcciones!<br><br>Usted puede optar por ingresar una nueva solicitud.";                                                       
                                                            echo "<div class='row'>
                                                                    <div class='col-md-12' align=center>
                                                                        <h4> ".$mensaje."</h> 
                                                                    </div>
                                                                 </div>                       
                                                                 <br>
                                                                 <hr class='new5'>
                                                                 ";
                                                        }
                                                     ?>
                                                    
			                        </div>
			                    </div>
			                </div>
			            </div>
                                    </form>
					<!-- /Documento Electrónico -->
                                   <?php 
                                   }else{
                                       //SI el horario esta cerrado
                                        echo "<div class='row'>
                                                <div class='col-md-12' align=center>
                                                    <h4> ".$bd_mensaje_mpv."</h> 
                                                </div>
                                             </div>                       
                                             <br>
                                             <hr class='new5'>
                                             ";

                                        
                                   }
                                   
                                   $conn->close();
                                   ?>    
				</div>
				<!-- /content -->

			</div>
			<!-- /main content -->

		</div>
		<!-- /page content -->

	</main>
	<!-- /page container -->

        <!-- Modal -->
        <div class="modal fade" id="myModal" role="dialog">
            <div class="modal-dialog modal-lg modal-moderno"> 
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                           <i class="fa fa-info-circle"></i> AVISO
                        </h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Aquí va el contenido dinámico o estático del modal -->
                        <p>Este es un mensaje importante para el usuario.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>


<!-- Footer -->
<?php include("layout/page-Footer.php"); ?>

<?php
    if($hora_activo==1){
        $dialog=new Dialog("myModalConfirm","error");
        $dialog->setModal("modal-ms");//largo
        echo $dialog->writeHTML();
    }
?>

</body>
</html>
<?php 
    if($hora_activo==1){
?>    
<script type="text/javascript">

//        grecaptcha.ready(function() {
//            grecaptcha.execute('<?php echo SITE_KEY; ?>', {action: 'homepage'}).then(function(token) {
//               // console.log(token);
//               document.getElementById("token").value = token;
//            });
//        });
        
        $(document).ready(function () {
        
            /* Para que se abra el emergente al entrar a la página */
            var dataURL = 'emergente.php';
            var xhr = new XMLHttpRequest();
            xhr.open('HEAD', dataURL, false);
            xhr.send();

            if (xhr.status != "404") {
                $('.modal-body').load(dataURL, {mpv_mensaje:"<?php echo $bd_mensaje_mpv ?>", mpv_video:"<?php echo $bd_video_mpv ?>"}, function(){
                    $('#myModal').modal({show:true});
                });
            }
        
            var navListItems = $('div.setup-panel div a'),
                  allWells = $('.setup-content'),
                  allNextBtn = $('.nextBtn');

            allWells.hide();

            navListItems.click(function (e) {
                e.preventDefault();
                var $target = $($(this).attr('href')),
                        $item = $(this);

                if (!$item.hasClass('disabled')) {
                    navListItems.removeClass('btn-dark').addClass('btn-default');
                    $item.addClass('btn-default');
                    allWells.hide();
                    $target.show();
                    $target.find('input:eq(0)').focus();
                }
           });

            allNextBtn.click(function(){
                var curStep = $(this).closest(".setup-content"),
                    curStepBtn = curStep.attr("id"),
                    nextStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
                    curInputs = curStep.find("input[type='text'],input[type='file'],input[type='tel'],input[type='email']"),
                    isValid = true;

                $(".form-group").removeClass("has-error");
                for(var i=0; i<curInputs.length; i++){
                    if (!curInputs[i].validity.valid){
                        isValid = false;
                        $(curInputs[i]).closest(".form-group").addClass("has-error");
                    }
                }
                
                /*VALIDA DEPENDENCIA*/
                if( $("#tipo_dependencia").val()==0 ){
                    $("#tipo_dependencia").removeClass("has-error");
                    $("#tipo_dependencia").closest(".form-group").addClass("has-error");
                    isValid = false;
                }
                
                //VALIDO EL SELECT, QUE HAYA UN ELEMENTO SELECCIONADO
                if( $("#documento_tipo_tramite").val()==0 ){
                    $("#documento_tipo_tramite").removeClass("has-error");
                    $("#documento_tipo_tramite").closest(".form-group").addClass("has-error");
                    isValid = false;
                }
                
                //VALIDA QUE EL NOMBRE DEL SOLICITANTE NO ESTE VACIO
                if ( $("#persona_numerodocumento").val() != '' &&  $("#persona_nombre").val() == '' ){
                    $("#titulo_nombresolicitante").removeClass("has-error");
                    $("#titulo_nombresolicitante").closest(".form-group").addClass("has-error");
                    isValid = false;
                }
                 
                //VALIDO QUE EL NUMERO DE DOCUMENTO SEA ENTERO
                if ( $("#documento_numerodocumento").val() != parseInt($("#documento_numerodocumento").val(), 10) ) {
                    $("#titulo_numerodocumento").removeClass("has-error");
                    $("#titulo_numerodocumento").closest(".form-group").addClass("has-error");
                    isValid = false;
                }
                
                //VALIDO QUE EL NUMERO DE FOLIOS SEA ENTERO
                if ( $("#documento_numerofolios").val() != parseInt($("#documento_numerofolios").val(), 10) ) {
                    $("#titulo_numerofolios").removeClass("has-error");
                    $("#titulo_numerofolios").closest(".form-group").addClass("has-error");
                    isValid = false;
                }
                
                if (isValid)
                    nextStepWizard.removeAttr('disabled').trigger('click');
            });

            $('div.setup-panel div a.btn-dark').trigger('click');
            
              /* Para que funcione el link si deseo volver a abrir el emergente */  
            $('.openPopup').on('click',function(){
                var dataURL = $(this).attr('data-href');
                $('.modal-body').load(dataURL,function(){
                    $('#myModal').modal({show:true});
                });
            }); 

        });

        function validaextension(idObj,cExtenciones){
                cNameFile = idObj.value	
                nDesde = cNameFile.length - 3
                ext = cNameFile.substr(nDesde, 3)
                ext = ext.toUpperCase()
                nPosiExt = cExtenciones.indexOf(ext);
                if(nPosiExt<0){	
                        idObj.value = ''		
                        alert('Tipo de Archivo no aceptado.  Corrija')
                }
        }
        
</script>
<?php 
    }
?>    