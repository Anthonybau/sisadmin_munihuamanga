<?php
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

/* Seteo para utilizar librerias de Zend */
set_include_path(get_include_path().
            PATH_SEPARATOR.$_SERVER['DOCUMENT_ROOT']."/library");

require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true);
$loader->suppressNotFoundWarnings(false);

// Para Ajax
require_once("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("solicitar");
$xajax->setCharEncoding("utf-8");

function solicitar($usuario,$NameDiv)
{
    global $conn;
    
    $objResponse = new xajaxResponse();
    
    /* Obtengo el Id del usuario */
    $IdUsuario = getDbValue("SELECT usua_id 
		                    FROM admin.usuario  
		                    WHERE upper(usua_login) = upper('$usuario')");

    
    if (!$IdUsuario) { /* NO existe el usuario */
        $objResponse->addScript("$(\".botao\").show();"); /* Muestro los botones */
        $objResponse->addAlert('NO existe el Usuario.  Por favor corrija');
        return $objResponse;
    }		                    
    

    /* Obtengo el email de la persona */
    $emailUsuario = getDbValue("SELECT c.pers_email 
		                    FROM admin.usuario a 
		 			  LEFT JOIN personal.persona_datos_laborales b on  a.pdla_id=b.pdla_id
                                          LEFT JOIN personal.persona c on  b.pers_id=c.pers_id
		                    WHERE upper(a.usua_login) = upper('$usuario')");

    if (!$emailUsuario or strlen($emailUsuario) == 0) { /* Si no tiene asignado ninguna persona */
        $objResponse->addScript("$(\".botao\").show();"); /* Muestro los botones */
        $objResponse->addAlert('Su usuario no tiene email asignado.');
        return $objResponse;
    } else {

        /* Guardo c�digo de seguridad */
//        $cs = mt_rand(1000, 9000);        
//        $sSql="UPDATE admin.usuario
//                     SET usua_codigoseguridad = $cs  
//        		WHERE usua_id = $IdUsuario";

        $nvaClave = mt_rand(1000, 9000);
        $sSql="UPDATE admin.usuario 
                SET usua_password = md5('$nvaClave'),
                    usua_activo=3,  /*obligado a cambiar contrasena*/
                    usua_fultimo_cambio=NOW()
    		WHERE usua_id = $IdUsuario";
        $conn->execute($sSql);

        /* Envio mail */
        $email_gmail=trim(SIS_EMAIL_GMAIL);
        $pass_email_gmail=trim(SIS_PASS_EMAIL_GMAIL);
        $email_servidor=trim(SIS_EMAIL_SERVIDOR);
        $email_from=trim(SIS_EFACT_EMAIL_FROM);

        $posGmail = stripos($email_gmail, 'gmail');    

        if($posGmail === false) { /* Si no se está usando el Gmail */

            $config = array('auth' => 'login',
                    'username' => $email_gmail,
                    'password' => $pass_email_gmail,'ssl' => 'tls','port' => 587);
            $mailTransport = new Zend_Mail_Transport_Smtp($email_servidor,$config);
        } else {

            $config = array('auth' => 'login',
                    'username' => $email_gmail,
                    // in case of Gmail username also acts as mandatory value of FROM header
                    'password' => $pass_email_gmail,'ssl' => 'tls','port' => 587);
            $mailTransport = new Zend_Mail_Transport_Smtp('smtp.gmail.com',$config);
        }


        Zend_Mail::setDefaultTransport($mailTransport);
                    
        
        $mail = new Zend_Mail();
        $IdUsuario = base64_encode($IdUsuario);
        $cs = base64_encode($cs);
        

        //$url_sistema = PATH_PORT . "intranet/modulos/admin/olvidecontrasenaGeneraClave.php?pass=$IdUsuario&cs=$cs";
        $empresa = SIS_EMPRESA;
        $mail->setBodyHtml("<b>SISADMIN:</b> Sistema Integrado Administrativo <BR><BR>
        					<b>Su nueva contrase&ntilde;a es:</b><BR><BR>
							<b><h3> $nvaClave </h3></b>
							<BR><BR>
							<b>NOTA.</b><BR>
							Si usted ha recibido este correo sin haberlo solicitado.  Tenga cuidado!.  Esto indica que alguien est&aacute; solicitando 
							obtener la contrase&ntilde;a del usuario que le pertenece.  Le recomendamos cambiar su contrase&ntilde;a como precauci&oacute;n 
							y eliminar este correo.<BR><BR>
							<b>IMPORTANTE</b><BR>
							Por favor NO RESPONDA este correo, Ha sido generado de manera autom&aacute;tica; su respuesta no ser&aacute; recibida<BR><BR><BR><BR>
							Atte.<BR>
							Soporte SISADMIN<BR>
							$empresa<BR>		
							")
            ->setFrom($email_from,'WEB-ADMIN ')
            ->addTo("$emailUsuario", 'Usuario del SISADMIN')
            ->setSubject(utf8_decode('SISADMIN: Instrucciones para restablecer su contraseña'));
        
        try {
            $mail->send();
            $mensaje = "Se le ha enviado a su email: <b>$emailUsuario</b>, las instrucciones para restablecer su contrase&ntilde;a.
        				Por favor, revise su correo y siga las instrucciones que se detallan en su contenido.";		    
            
        } catch (Exception $e) {
            $mensaje = 'Error al enviar el Correo...<br>
            			Su mensaje de error es: <br>
            			'.$e->getMessage();
        	
            $objResponse->addScript("$(\".botao\").show();"); /* Muestro los botones */            
        }         
    }    

    $contenido_respuesta = $mensaje;

    $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);

    // Se devuelve el objeto, que este dara todas las instruccione JS para realizar la tarea
    return $objResponse;
}


$xajax->processRequests();
// fin para Ajax
?>
<html>
<head>
	<title>Olvido de Contraseña</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="<?php echo PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>                
        <?php $xajax->printJavascript(PATH_INC.'ajax/');?>	
	<script language='JavaScript'>

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
		document.frm.Sr_usua_login.focus();
	}

		function muestra_cargando(){
		      xajax.$('MensajeCarga').style.display='block';
		   }

		   function oculta_cargando(){
		      xajax.$('MensajeCarga').style.display='none';
		   }
		   
		   xajax.loadingFunction = muestra_cargando;
		   xajax.doneLoadingFunction = oculta_cargando;
		   		
	</script>

    <!-- Este es la impresion de las rutinas JS que necesita Xajax para funcionar -->
    <?php 
    verif_framework(); 
    ?>	

</head>
<body class="contentBODY" onload="inicializa()" >
<?php
pageTitle("Olvid&oacute; su Contrase&ntilde;a?","");

/*	botones */
$button = new Button;
$button->setDiv(false);
$button->addItem(" Restablecer mi Contrase&ntilde;a ","if(document.frm.Sr_usua_login.value!=''){javascript:$('.botao').hide();xajax_solicitar(document.frm.Sr_usua_login.value,'divOlvido')}else{document.frm.Sr_usua_login.focus()}","content",0,0,'btn btn-default btn-sm','button');

/* Formulario */
$form = new Form("frm", "", "POST", "content", "100%",true);
$form->setLabelWidth("40%");
$form->setDataWidth("60%");

// si es edicion el usuario no es editable
$form->addField("INGRESE SU USUARIO: ",textField("Usuario","Sr_usua_login",'',20,20));
$form->addField("",$button->writeHTML());
echo $form->writeHTML();

?>
<div id='divOlvido'>
</div>
<div id="MensajeCarga" style="display: none;">
Enviando Email!.... Por favor espere
</div> 
</body>

</html>
<?php
$conn->close(); 