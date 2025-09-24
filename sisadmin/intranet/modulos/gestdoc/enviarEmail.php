<?php
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
$usua_id=getSession('sis_userid');
include("./registroDespacho_class.php");
include("registroDespacho_edicionAdjuntosClass.php");

if($usua_id){
    
    $id = getParam("id");
    $flujo= getParam("flujo");
    $conn = new db();
    $conn->open();

            //ENVIO DE CORREO
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

            $despacho=new despacho_SQLlista();
            $despacho->whereID($id);
            $despacho->setDatos();
            $name_file=$despacho->field('desp_file_firmado');    
            $periodo = $despacho->field('desp_anno');
            $num_documento=$despacho->field("tiex_abreviado").' '.$despacho->field("desp_numero") ."-". $despacho->field("desp_anno") ."-". $despacho->field("desp_siglas");
            
            
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

            if($flujo==1){
                $subject="DOCUMENTO LISTO PARA SU FIRMA DIGITAL! N° de Trámite $id ";
            }else{
                $subject="ENVIO...";
            }

            $firmas=new despachoFirmas_SQLlista();
            $firmas->wherePadreID($id);
            $firmas->orderUno();
            $sql=$firmas->getSQL();
            $rsFirmar = new query($conn, $sql);
            while ($rsFirmar->getrow()) {
                $email=$rsFirmar->field('pers_email');
                $empleado=$rsFirmar->field('empleado');
                if($email){
                    $mail = new Zend_Mail();

                    $mail->setBodyHtml(utf8_decode($empleado)." </b>		
                                                    <br>Usted tiene el archivo adjunto listo para su firma digital
                                                    <br><b>"."<a href=\"".PATH_PORT."intranet/modulos/index.php\" target=\"_blank\">Ingrese Aqu&iacute;</a>". " para proceder</b>
                                                    <br><br>
                                                    <b>Enviado Desde:</b> Sistema de Informaci&oacute;n-".SIS_EMPRESA.
                                                     "<br><b>IMPORTANTE:</b> NO responda a este Mensaje")

                        ->setFrom($email_from,'SISADMIN '.SIS_EMPRESA_SIGLAS)
                        ->setSubject(utf8_decode($subject))
                        ->addTo($email, 'Empleado');
                        $nameFileFullPath = PUBLICUPLOAD."gestdoc/".SIS_EMPRESA_RUC."/$periodo/$id/$name_file";

                        if(file_exists($nameFileFullPath)){
                            $content = file_get_contents($nameFileFullPath); // e.g. ("attachment/abc.pdf")
                            $attachment = new Zend_Mime_Part($content);
                            $attachment->type = 'application/pdf';
                            $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                            $attachment->encoding = Zend_Mime::ENCODING_BASE64;
                            $attachment->filename = $name_file; // name of file
                            $mail->addAttachment($attachment);
                        }
                        
                        $despachoAdjuntados=new despachoAdjuntados_SQLlista();
                        $despachoAdjuntados->wherePadreID($id);
                        $despachoAdjuntados->whereFirmar(); /*PARA FIRMA*/
                        $despachoAdjuntados->orderUno();
                        $rsDespachoAdjuntados = new query($conn, $despachoAdjuntados->getSQL());
                        while ($rsDespachoAdjuntados->getrow()) {
                            $name_file=$rsDespachoAdjuntados->field("area_adjunto");
                            $nameFileFullPath = PUBLICUPLOAD."gestdoc/".SIS_EMPRESA_RUC."/$periodo/".$id."/".$name_file;
                            if(file_exists($nameFileFullPath) && strpos(strtoupper($name_file),'.PDF')>0){
                                $content = file_get_contents($nameFileFullPath); // e.g. ("attachment/abc.pdf")
                                $attachment2 = new Zend_Mime_Part($content);
                                $attachment2->type = 'application/pdf';
                                $attachment2->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                                $attachment2->encoding = Zend_Mime::ENCODING_BASE64;
                                $attachment2->filename = $name_file; // name of file
                                $mail->addAttachment($attachment2);
                                unset($attachment2);
                            }
                        }
                        
                    try {
                        $ahora=date('d/m/Y h:i:s');                                    
                        $mail->send();
                        $jsondata["success"] = True;
                        $jsondata["mensaje"] = "Envio de Correo Exitoso";

                    } catch (Exception $e) {
                            //$mensaje = 'Error al enviar el Correo...Por favor, Comunicarse con el Area de Soporte Informático  <br>
                            //                    Su mensaje de error es: <br>
                            //                    '.$e->getMessage();
                            $mensaje =  $e->getMessage();

                            //alert($mensaje);
                            $jsondata["success"] = false;
                            $jsondata["mensaje"] = "Lo sentimos, No se pudo enviar Correo: ".$mensaje;                                    
                    }
                    //alert($mensaje);
                    //echo $mensaje;
                }else{
                    $jsondata["success"] = false;
                    $jsondata["mensaje"] = "No se halló correo en firmante $empleado para envio de notificaciones ";
                }
            }                                    
                        
                    
    $conn->close();
}else{
    $jsondata["success"] = false;
    $jsondata["mensaje"] = "Lo sentimos, No se pudo enviar Correo";
}

header('Content-Type: application/json');
echo json_encode($jsondata, JSON_FORCE_OBJECT);