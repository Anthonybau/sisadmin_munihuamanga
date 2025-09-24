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
        global $conn,$usuario;
	$objResponse = new xajaxResponse();
        $usua_login= getParam("user_name");
        $_senhanueva= md5($formdata['sr_senhanueva']);
        $_senharepite=md5($formdata['sr_senharepite']);
        
        if($usuario->field('usua_activo')==2){ /*creado recien desde escalafon*/
            $fecha_nacimiento=dtos($formdata['Dr_fechanacimiento']);
        }else{//completa la fecha de namiento de manera automatica
            $fecha_nacimiento=dtos($usuario->field('pers_nacefecha'));
        }

        $email=trim($formdata['cx_pers_email']);
        $fecha_nacimiento='01/01/2000';
        $sSql="SELECT admin.func_cambiacontrasena_login('$usua_login','$_senhanueva','$_senharepite','$fecha_nacimiento')";

        $conn->execute($sSql);
        $error=$conn->error();
        if($error){
            $objResponse->addAlert($error);
        }else{
            $ok=1;
            if($email){
                //ACTUALIZA EL CORREO
                $sSql="UPDATE personal.persona 
                          SET pers_email='$email'
                          WHERE pers_id=".$usuario->field('pers_id');
                $conn->execute($sSql);
                $error=$conn->error();
                if($error){
                    $ok=0;
                    $objResponse->addAlert($error);
                }
            }

            if($ok==1){
                $otable = new AddTableForm();
                $otable->setLabelWidth("20%");
                $otable->setDataWidth("80%");

                $otable->addHidden('Sr_username',$usua_login);
                $otable->addHidden('sx_senha',$formdata['sr_senhanueva']);

                $otable->addField("","<font size=3 color=red>ACTUALIZACION EXITOSA</font>");	
                $otable->addLine();
                $button = new Button;
                $button->setDiv(false);
                $button->addItem(" Continuar ","javascript:continuar()","content",0,0,'btn btn-default btn-sm','button');
                $otable->addField("",$button->writeHTML());

                $contenido_respuesta=$otable->writeHTML();		
                $objResponse->addAssign("divContinuar",'innerHTML', $contenido_respuesta);
            }
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
            document.frm.sr_senhanueva.focus();
	}
        
	function mivalidacion(frm) {  
            return true			
	}
        
	function continuar() {
            //alert(sURL);
            parent.content.document.frm.target = "content";
            parent.content.document.frm.action = 'login.php';
            parent.content.document.frm.submit();
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
$otable->addField("Usuario:",$fusername." [".$usuario->field('empleado')."]");
$otable->addField("Nueva Contrase&ntilde;a:",passwordField("Nueva Contraseña","sr_senhanueva","",20,50));
$otable->addField("Repita su nueva Contrase&ntilde;a:",passwordField("Repita su nueva Contraseña","sr_senharepite","",20,50));

//if($usuario->field('usua_activo')==2){ /*creado recien desde escalafon*/
//    $otable->addField("Fecha de Nacimiento:",dateField2("Fecha de Nacimiento","Dr_fechanacimiento","",""));
//}

if($usuario->field('pers_email')){
    $otable->addField("Email: ",$usuario->field('pers_email'));   
}else{
    $otable->addField("Email: ",textField("Email","cx_pers_email",$bd_pers_email,55,50));
}
//$form->addField("Fecha de Nacimiento: ", $calendar->make_input_field('Fecha de Nacimiento', array(), array('name' => 'Dr_fechanacimiento', 'value' => '')));
/*
	botones,
	configure conforme sus necesidades
*/
$button = new Button;
$button->setDiv(false);
$button->addItem(" Continuar ","javascript:if(ObligaCampos(frm)){xajax_actualizar(xajax.getFormValues('frm'))}","content",0,0,'btn btn-default btn-sm','button');
$button->addItem(" Salir ","javascript:top.close()","content",0,0,'btn btn-default btn-sm','button');
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