<?php
include("../library/library.php");
include("admin/adminUsuario_class.php");
/*	verificación a nivel de usuario */
verificaUsuario(0);

$conn = new db();
$conn->open();

$fusername=getParam("user_name");

$usuario=new clsUsers_SQLlista();
$usuario->whereUserLogin($fusername);
$usuario->setDatos();
        
// Inicio Para Ajax
require_once("../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("actualizar");

function actualizar($formdata){    
        global $conn;
	$objResponse = new xajaxResponse();
        $_senha = md5($formdata['sr_senha']);
        $_senhanueva= md5($formdata['sr_senhanueva']);
        $_senharepite=md5($formdata['sr_senharepite']);
        
        $sSql="SELECT admin.func_cambiacontrasena(".getSession("sis_userid").",'$_senha','$_senhanueva','$_senharepite')";

        $conn->execute($sSql);
        $error=$conn->error();
        if($error){
            $objResponse->addAlert($error);
        }else{
            $otable = new AddTableForm();
            $otable->setLabelWidth("20%");
            $otable->setDataWidth("80%");
            $otable->addField("","<font size=3 color=red>ACTUALIZACION EXITOSA</font>");	
            $otable->addLine();
            $contenido_respuesta=$otable->writeHTML();		
            $objResponse->addAssign("divContinuar",'innerHTML', $contenido_respuesta);
        }
        return $objResponse;
}
$xajax->processRequests();
?>
<html>
<head>
<title>Actualizar Contraseña</title>
<meta http-equiv="content-type" content="text/html; charset=es-utf-8" />
<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
<script language="JavaScript" src="../library/js/focus.js"></script>
<script language="JavaScript" src="../library/js/libjsgen.js"></script>
<script type="text/javascript" src="../library/jquery/jquery-1.11.3.min.js"></script>
<link rel="stylesheet" type="text/css" href="../library/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="../library/bootstrap/css/bootstrap-theme.min.css">
<script src="../library/bootstrap/js/bootstrap.min.js"></script>        
<script language="JavaScript">

	function inicializa() {
            document.frm.sr_senha.focus();
	}
        
	function mivalidacion(frm) {  
            return true			
	}
        
        
</script>

    <?php 
        $xajax->printJavascript(PATH_INC.'ajax/'); 
        verif_framework();
    ?>        
            
        
</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("Modificar Contrase&ntilde;a","");

?>
<br>
<?php
/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$otable = new AddTableForm();
$otable->setLabelWidth("20%");
$otable->setDataWidth("80%");
$otable->addField("Usuario:",getSession("sis_username")." [".trim(getSession("sis_username_antiguo"))."]");
$otable->addField("Contrase&ntilde;a actual:",passwordField("Contrase\u00f1a actual","sr_senha","",20,50));
$otable->addField("Nueva Contrase&ntilde;a:",passwordField("Nueva Contraseña","sr_senhanueva","",20,50));
$otable->addField("Repita su nueva Contrase&ntilde;a:",passwordField("Repita su nueva Contraseña","sr_senharepite","",20,50));


/*
	botones,
	configure conforme sus necesidades
*/
$button = new Button;
$button->setDiv(false);
$button->addItem(" Continuar ","javascript:if(ObligaCampos(frm)){xajax_actualizar(xajax.getFormValues('frm'))}","content",0,0,'btn btn-default btn-sm','button');

$otable->addField("",$button->writeHTML());

$form->addHtml("<tr><td colspan=2><div id='divContinuar'>\n");
$form->addHtml($otable->writeHTML());
$form->addHtml("</div></td></tr>\n");

//echo "<div id='divContinuar'>\n"; //guarda campos ocultos con datos de inicio
echo $form->writeHTML();
//echo "</div>\n";	
?>
</body>
</html>
<?php
/* cierro la conexion a la BD */
$conn->close();