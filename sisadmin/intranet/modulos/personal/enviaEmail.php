<?php
    //ENVIO DE CORREO
    $persona=new clsPersona_SQLlista();
    $persona->whereID($pers_id);
    $persona->setDatos();
    if ($persona->existeDatos() && $persona->field('pers_envia_email')==0){

        $posDomain = stripos($_SERVER['SERVER_NAME'], 'mytienda.page');    

        if($posDomain === false) { 
            set_include_path(get_include_path().
                    PATH_SEPARATOR.$_SERVER['DOCUMENT_ROOT']."/library");


        }else{

            defined('APPLICATION_PATH')
                    || define('APPLICATION_PATH', '/home/lguevara/zfappMytienda'); 

            set_include_path(implode(PATH_SEPARATOR, array(
                    realpath(APPLICATION_PATH . '/library'),
                    get_include_path(),
            )));
        }

        require_once 'Zend/Loader/Autoloader.php';
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->setFallbackAutoloader(true);
        $loader->suppressNotFoundWarnings(false);

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

            
        $email=$persona->field('pers_email');
        $dni=trim($persona->field('pers_dni'));
        $empleado=trim($persona->field('pers_apellpaterno')).' '.trim($persona->field('pers_apellmaterno')).' '.trim($persona->field('pers_nombres'));

        $subject=utf8_decode("ALTA EXITOSA DE REGISTRO DE PERSONAL! DNI $dni");

        if($email){
            $mail = new Zend_Mail();

            $mail->setBodyHtml(utf8_decode($empleado)." </b>		
                            <br>Usted ha sido dado de Alta en nuestro Sistema Inform&aacute;tico
                            <br>Esta disponible su acceso con el siguiente usuario y contrase&ntilde;a: $dni
                            <br><b>Puede acceder "."<a href=\"".PATH_PORT."intranet/modulos/index.php\" target=\"_blank\">Ingresando Aqu&iacute;</a>"."</b>
                            <br><br>
                            <b>Enviado Desde:</b> Sistema de Informaci&oacute;n-".SIS_EMPRESA.
                             "<br><b>IMPORTANTE:</b> NO responda a este Mensaje")


                ->setFrom($email_from,'SISADMIN '.SIS_EMPRESA_SIGLAS)
                ->setSubject($subject)
                ->addTo($email, 'Empleado');


            try {
                
                $ahora=date('d/m/Y h:i:s');                                    
                $mail->send();
                
                $conn->execute("  UPDATE personal.persona 
                                                    SET pers_envia_email=1,
                                                        pers_envia_email_fregistro=NOW()
                                                    WHERE pers_id=$pers_id ");
                $error=$conn->error();
                if($error){ 
                    alert('3'. $error);
                }
                
            } catch (Exception $e) {
                    //$mensaje = 'Error al enviar el Correo...Por favor, Comunicarse con el Area de Soporte Informático  <br>
                    //                    Su mensaje de error es: <br>
                    //   
                    //                                     '.$e->getMessage();
                    $respuesta = false;    
                    $mensaje =  $e->getMessage();
                    //alert($mensaje);
            }
            //alert($mensaje);
            //echo $mensaje;
        }
    }
     //FIN DE ENVIO DE CORREO                            