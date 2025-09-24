<?php
include("../../library/library.php");
include("CabeceraDocumento_class.php"); 
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

$myClass = new CabeceraDocumento($id,"Edici&oacute;n de Encabezado de Documento");

if (strlen($id)>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
		$bd_cado_id=$myClass->field("cado_id");
		$bd_cado_nombre=$myClass->field("cado_nombre");	
                $bd_cado_contenido=$myClass->field("cado_contenido");
		$bd_usua_id= $myClass->field("usua_id");                
                $bd_cado_estado=$myClass->field('cado_estado');
                $bd_cado_incluir_oficina_origen=$myClass->field('cado_incluir_oficina_origen');
                $bd_cado_incluir_nombre_anno=$myClass->field('cado_incluir_nombre_anno');
                $username = $myClass->field('username');
                $usernameactual = $myClass->field('usernameactual');
                $bd_cado_fregistro=$myClass->field('cado_fregistro');
                $bd_cado_fregistroactual=$myClass->field('cado_actualfecha');
        }

}else{ // Si es nuevo
}

?>
<html>
<head>
	<title><?php echo $myClass->getTitle() ?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">

	<script language="JavaScript" src="../../library/js/focus.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>
        

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
                        parent.content.document.frm.Sr_cado_nombre.focus();
                }
                
                
                </script>
        <?php
	verif_framework();
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
$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$id); // clave primaria

if (strlen($id)>0) { // edición
    $form->addField("C&oacute;digo: ",$id);
}
$form->addField("Nombre: ",textField("Nombre","Sr_cado_nombre",$bd_cado_nombre,80,80).
                     help("Utilice las siguientes Etiquetas:","{fecha} Fecha del Documento.<br>{numero} N&uacute;mero de Documento y Siglas.<br>{destinatario} Nombre(s), Apellido(s) y Cargo(s) del Destinatario(s).<br>{asunto} Asunto del Documento.<br> {referencia} Referencia del Documento, UTILICE: <b>REFERENCIA :</b> o <b>Referencia :</b> o <b>REFERENCIA:</b> o <b>Referencia:</b><br>{firmante} Nombres, Apellidos, Cargo y oficina del/los Firmante(s)<br>{procedencia} Dependencia de donde se Origina el documento.<br>{numero_tramite} N&uacute;mero de Tr&sscute;mite Documentario.<br>{fecha_grabacion} Fecha de Grabaci&oacute;n del registro.<br>{hora_grabacion} Hora de Grabaci&oacute;n del registro.<br>{tipo_persona} Tipo de Persona de documento externo.<br>{firma_doc_externo} Nombre de Persona que firma el documento externo.<br>{entidad} Entidad de documento externo.<br>{tipo_solicitante} Tipo de Solicitante de documento externo.<br>{codigo_largo} Tipo y n&uacute;mero de documento de identidad.<br>{direccion} Direcci&oacute;n de documento externo.<br>{distrito} Distrito de la direcci&oacute;n de documento externo.<br>{provincia} Provincia de la direcci&oacute;n de documento externo.<br>{departamento} Departamento de la direcci&oacute;n de documento externo.",2));

$form->addField("Incluir Nombre la Oficina Origen: ",checkboxField("Incluir Nombre la Oficina Origen","hx_cado_incluir_oficina_origen",1,$bd_cado_incluir_oficina_origen==1));
$form->addField("Incluir Nombre del A&ntilde;o: ",checkboxField("Incluit nombre del año","hx_cado_incluir_nombre_anno",1,$bd_cado_incluir_nombre_anno==1));

if (strlen($id)>0) { // edición
    $form->addField("Activo: ",checkboxField("Activo","hx_cado_estado",1,$bd_cado_estado==1));
    $form->addField("Creado por: ",$username.'/'.$bd_cado_fregistro.' / '." Actualizado por: ".$usernameactual.'/'.$bd_cado_fregistroactual);
}else{
    $form->addHidden("hx_cado_estado",1); // clave primaria
}

$form->addHtml("<tr><td colspan=2>");
$form->addHtml("<textarea name=\"K__cado_contenido\" id=\"K__cado_contenido\" rows=\"10\" cols=\"80\">
                $bd_cado_contenido
                </textarea>");
$form->addHtml("</td></tr>\n");

echo $form->writeHTML();
?>
    
    
    
</body>
  
    <script>

        CKEDITOR.replace( 'K__cado_contenido', {
                            filebrowserBrowseUrl: '../../library/ckfinder/ckfinder.html',
                            filebrowserUploadUrl: '../../library/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files'
        });
                    
    </script>

</html>

<?php
/*
	cierro la conexion a la BD
*/
$conn->close();