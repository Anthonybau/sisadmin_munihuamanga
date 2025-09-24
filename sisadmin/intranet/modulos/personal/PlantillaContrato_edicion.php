<?php
include("../../library/library.php");
include("PlantillaContrato_class.php"); 
include("../catalogos/catalogosTabla_class.php");
/*
	verifica nivel de usuario
*/
verificaUsuario(1);

/*
	establecer conexion con la BD
*/
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

/*
	tratamiento de campos
*/
$id = getParam("id"); // captura la variable que viene del objeto lista
$depe_id=getParam("nbusc_depe_id");

$myClass = new clsPlantillaContrato($id,"Edici&oacute;n de Plantilla");

if (strlen($id)>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
		$bd_plco_id = $myClass->field("plco_id");
		$bd_plco_titulo = $myClass->field("plco_titulo");		
                $bd_tabl_idsitlaboral= $myClass->field("tabl_idsitlaboral");
                $bd_tabl_tipdoc= $myClass->field("tabl_tipdoc");

                $bd_plco_contenido = $myClass->field("plco_contenido");
		$bd_usua_id= $myClass->field("usua_id");
                
                $username1 = $myClass->field('username1');
                $username2 = $myClass->field('username2');
                $bd_plco_fregistro1=$myClass->field('plco_fregistro');
                $bd_plco_fregistro2=$myClass->field('plco_actualfecha');

        }

}else{ // Si es nuevo

}

/* Archivos para calendario */
require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);

// Inicio Para Ajax
require_once("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->setCharEncoding('utf-8');

$xajax->processRequests();
?>
<html>
<head>
	<title><?php echo $myClass->getTitle() ?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT ?>">

	<script language="JavaScript" src="../../library/js/focus.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>
        
        
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>   
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                
        <script src="../../library/ckeditor/ckeditor.js"></script>
        <script>CKEDITOR.dtd.$removeEmpty['span'] = false;</script>
	<script language='JavaScript'>
            
                function salvar(idObj) {
                    if (ObligaCampos(frm)){
                            ocultarObj(idObj,10)
                            document.frm.target = "controle";
                            document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
                            document.frm.submit();

                    }
                }
                /*
                        se invoca desde la funcion obligacampos (libjsgen.js)
                        en esta funci�n se puede personalizar la validaci�n del formulario
                        y se ejecuta al momento de gurdar los datos
                */
                function mivalidacion(frm) {
                        return true
                }

                /*
                        funci�n que define el foco inicial en el formulario
                */
                function inicializa() {
                        parent.content.document.frm.sr_plco_titulo.focus();
                }
                
                
                </script>
        <?php
            $xajax->printJavascript(PATH_INC.'ajax/');
            verif_framework();
            $calendar->load_files();
	?>


</head>

<body class="contentBODY" onload="inicializa()">

 <?php
 
//$nameSLab=getDbValue("select tabl_descripcion from tabla where tabl_tipo=9 and tabl_codigoauxiliar=$sitLab");
pageTitle($myClass->getTitle());

/* botones*/
$button = new Button;
$button->addItem("Guardar","javascript:salvar('Guardar')","content",2);
$button->addItem("Regresar a Lista de Registros",$myClass->getPageBuscar().'?clear=1&'.$param->buildPars(false),"content");
echo $button->writeHTML();


$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setUpload(true);
$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$id); // clave primaria

$form->addField("T&iacute;tulo: ",textField("T&iacute;tulo","sr_plco_titulo",$bd_plco_titulo,100,100));

$tabla=new clsTabla_SQLlista();
$tabla->whereTipo('CONDICION_LABORAL');
$tabla->orderUno();
$sqlSituLabo=$tabla->getSQL_cbox();

$tabla=new clsTabla_SQLlista();
$tabla->whereTipo('TIPO_DOCUMENTO_CONTRATO');
$tabla->orderUno();
$sqlTipDoc=$tabla->getSQL_cbox();
$form->addField("Condici&oacute;n Laboral: ",listboxField("Condici&oacute;n laboral",$sqlSituLabo, "tr_tabl_idsitlaboral",$bd_tabl_idsitlaboral,"-- Seleccione Condici&oacute;n Laboral --","").
                    "&nbsp;&nbsp;<b>Tipo Documento: </b>".listboxField("Tipo Documento",$sqlTipDoc, "tr_tabl_tipdoc",$bd_tabl_tipdoc,"-- Seleccione Tipo Documento --","").
                     help("Utilice las siguientes Etiquetas:","{numero} N&uacute;mero de Contrato y Siglas<br>{titular} Nombres y Apellidos del Titular de la Entidad<br>{dni_titular} DNI del titular de la Entidad<br>{cargo_titular} Cargo del titular de la Entidad<br>{resolucion_titular} Resoluci&oacute;n del titular de la Entidad<br>{direccion_empresa} Direcci&oacute;n Institucional<br>{empleado} Nombres y apellidos y del trabajador <br>{dni_empleado} DNI del trabajador<br>{direccion_empleado} Direcci&oacute;n del Trabajador<br>{cargo_empleado} Cargo del Trabajador<br>{dependencia} Dependencia Laboral<br>{funciones} Funciones del Trabajador<br>{num_concurso} N&uacute;mero de Concurso<br>{fecha_desde} Fecha Inicial de Contrato<br>{fecha_hasta} Fecha Final de Contrato<br>{lugar} lugar de trabajo<br>{anno} Año del Contrato<br>{remuneracion} Monto de la Remuneraci&oacute;n<br>{fecha} Fecha de Firma del Contrato<br>{numero_origen} N&uacute;mero de Contrato Origen (P/Adenda)<br>{fecha_origen} Fecha de Contrato Origen (P/Adenda)<br>{fecha_desde_origen} Fecha Inicial de Contrato Origen (P/Adenda)<br>{fecha_hasta_origen} Fecha Final de Contrato Origen (P/Adenda)<br>",2));
//solo si es edicion se agrega los datos de auditoria
if($id) {
    $form->addBreak("<b>Control</b>");
    $form->addField("Creado por: ",$username1.'/'.substr($bd_plco_fregistro1,0,19).
                    "&nbsp;-&nbsp;<b>Actualizado por: </b>".$username2.'/'.substr($bd_plco_fregistro2,0,19));
    
    $form->addBreak("<b>Indicaci&oacute;n : las tablas no deben exceder en 530 pixels de ancho</b>");    
}

$form->addHtml("<tr><td colspan=2>");
$form->addHtml("<textarea name=\"K__plco_contenido\" id=\"K__plco_contenido\" rows=\"10\" cols=\"80\">
                $bd_plco_contenido
                </textarea>");
$form->addHtml("</td></tr>\n");


echo $form->writeHTML();
?>
</body>
    <script>
                // Replace the <textarea id="editor1"> with a CKEditor
                // instance, using default configuration.
                CKEDITOR.replace( 'K__plco_contenido', {
                            filebrowserBrowseUrl: '../../library/ckfinder/ckfinder.html',
                            filebrowserUploadUrl: '../../library/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files'
                    } );                    
    </script>    
    
</html>

<?php
/*
	cierro la conexion a la BD
*/
$conn->close();