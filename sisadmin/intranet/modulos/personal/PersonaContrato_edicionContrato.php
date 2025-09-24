<?php
include("../../library/library.php");
include("Persona_class.php");
include("PersonaContrato_class.php"); 

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
$id = getParam("id"); 
$id_relacion=getParam("id_relacion"); 

$myClass = new clsPersonaContrato($id,"Edici&oacute;n de Documento");
$myClass->setDatos();
if($myClass->existeDatos()){
    $bd_plco_id = $myClass->field("plco_id");
    $bd_numero_contrato = $myClass->field("numero_contrato");
    $bd_peco_contenido = $myClass->field("peco_contenido");
    $bd_usua_id= $myClass->field("usua_id");

    $bd_username= $myClass->field("username");
    $bd_peco_fregistro= $myClass->field("peco_fregistro"); 

    $bd_usernameactual= $myClass->field(" usernameactual");
    $bd_peco_actualfecha= $myClass->field("peco_actualfecha");
}


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
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">

	<script language="JavaScript" src="../../library/js/focus.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>
        
        <script src="../../library/ckeditor/ckeditor.js"></script>
        <script src="../../library/ckeditor/config.js"></script>
        <script>CKEDITOR.dtd.$removeEmpty['span'] = false;</script>
	<script language='JavaScript'>
            
                function salvar(idObj) {
                    if (ObligaCampos(frm)){
                            ocultarObj(idObj,10)
                            document.frm.target = "controle";
                            document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(4)."&".$param->buildPars(false)?>";
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

                function AbreVentana(sURL){
                    var w=720, h=650;
                    venrepo=window.open(sURL,'rptSalida', "status=yes,resizable=yes,toolbar=no,scrollbars=yes,top=50,left=150,width=" + w + ",height=" + h, 1 );
                    venrepo.focus();
                }


                function imprimir(id) {
                    AbreVentana('rptContrato.php?id=' + id);
                }        

                </script>
    <?php
        $xajax->printJavascript(PATH_INC.'ajax/');
	verif_framework();
	?>


</head>

<body class="contentBODY">

 <?php
 
//$nameSLab=getDbValue("select tabl_descripcion from tabla where tabl_tipo=9 and tabl_codigoauxiliar=$sitLab");
pageTitle($myClass->getTitle());

/* botones*/
$button = new Button;
$button->addItem("Guardar","javascript:salvar('Guardar')","content",2);
$button->addItem("Imprimir","javascript:imprimir('$id')","content");
$button->addItem("Regresar a Lista de Contratos",'PersonaContrato_lista.php?clear=1&'.$param->buildPars(false),"content");
echo $button->writeHTML();


$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setUpload(true);
$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$id); // clave primaria

$myPersona = new clsPersona($id_relacion);
$myPersona->setDatos();

$form->addField("Persona: ",$myPersona->field("empleado").' / '.$myPersona->field("pers_dni").' / '.$myPersona->field("sit_laboral_larga"));
$form->addField("Documento: ",$bd_numero_contrato);


$form->addBreak("<b>Control</b>");
$form->addField("Creado por: ",$bd_username.'/'.substr($bd_peco_fregistro,0,19));

if($bd_usernameactual){
    $form->addField("Actualizado por: ",$bd_usernameactual.'/'.substr($bd_peco_actualfecha,0,19));
}

//$form->addBreak("<b>Indicaci&oacute;n : las tablas no deben exceder en 530 pixels de ancho</b>");    

$form->addHtml("<tr><td colspan=2>");
$form->addHtml("<textarea name=\"K__peco_contenido\" id=\"K__plco_contenido\" rows=\"10\" cols=\"80\">
                $bd_peco_contenido
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