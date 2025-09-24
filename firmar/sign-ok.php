<?php
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
//$_FILE["attach"]=Informaci�n del documento firmado.
//$_POST["documentID"]=Identificador que hemos marcado en index.php.
 
if (!empty($_FILES["attach"])) { 
    
    $myFile = $_FILES["attach"];
 
    if ($myFile["error"] !== UPLOAD_ERR_OK) {
        echo "<p>Ha ocurrido un error en la subida del Archivo.</p>";
        exit;
    }
    //en el nombre del archivo solo deja letras y numeros 
    $name = preg_replace("/[^A-Z0-9._-]/i", "_", $myFile["name"]);
 
    $explode_documentID=explode("-",$_POST["documentID"]);
    
    $documentID=$explode_documentID[0];    
    $sign_id=$explode_documentID[1];
    
            
    $parts = pathinfo($name); //devuelve un array con información del archivo
    $name=$parts["filename"]."-df_".$sign_id.".".$parts["extension"];
    /*
            Aqu� se puede indicar donde guardar el documento firmado. 
            Tal y como est� configurado se guardar� en la misma carpeta donde estan los PHP.
    */
    $file_firmado = $name;
    $success = move_uploaded_file($myFile["tmp_name"], $file_firmado);
    if (!$success) {
        echo "<p>No se puede guardar el archivo.</p>";
        exit;
    }else{
        /*BUSCO SI EXISTE ARCHIVO DE ERROR*/
        $error=0;
        if( file_exists($file_firmado) ){
            $zip = new ZipArchive();
            if ($zip->open($file_firmado) === TRUE) {
                for ( $i = 0; $i < $zip->numFiles; $i++ ) {
                    $filenameSigner = $zip->getNameIndex($i);
                    $pos = strpos($filenameSigner, '-signaturefailed.pdf');
                    if( $pos !== false ){
                        $error=1;
                        exit;
                    }                    
                }   
            }

        }
        
        if($error==0){
            //ini_set ('display_errors', true);
            //INICIA CONTROL DE FIRMADO       
            require_once("../sisadmin/intranet/modulos/signer/signer.php");

            $conn = new db();
            $conn->open();

            $signer=new signer_SQLlista();
            $signer->whereID($sign_id);
            $signer->setDatos();
            
            $function_execute=$signer->field('sign_function');
            $path_extract=$signer->field('sign_path_extract');;
            
            $zip = new ZipArchive();
            if ($zip->open($file_firmado) === TRUE) {
                $zip->extractTo($path_extract);
                
                $signerFiles=new signerFiles_SQLlista();
                $signerFiles->wherePadreID($sign_id);
                $sql=$signerFiles->getSQL();                
                $rs = new query($conn, $sql);
                while ($rs->getrow()) {
                    
                    $filezip=$rs->field('sifi_file_zip');
                    if($filezip){ //SI EXISTE ARCHIVO ZIP
                       $fileZip=$rs->field('sifi_path_destino').$filezip;
                       if (file_exists($fileZip)){                           
                            $zipAdjunto = new ZipArchive();
                            if ($zipAdjunto->open($fileZip) === TRUE) {
                                $namePDF=$path_extract.$rs->field('sifi_name_file');
                                $zipAdjunto->deleteName($rs->field('sifi_name_file'));
                                $zipAdjunto->addFile($namePDF, $rs->field('sifi_name_file'));
                                $zipAdjunto->close();
                                unlink($namePDF);
                            }
                       }
                    } else {
                        $namePDF_origen=$path_extract.$rs->field('sifi_name_file');
                        $namePDF_destino=$rs->field('sifi_path_destino').$rs->field('sifi_name_file');
                        copy($namePDF_origen, $namePDF_destino);
                        /*N BORRA LOS ARCHIVOS EXTRAIDOS QUE CORRESPONDEN AL LUGAR DE EXTRACCION*/
                        if($rs->field('sifi_path_destino')!=$path_extract){
                            unlink($namePDF_origen);
                        }
                        
                    }
                }
                
                $zip->close();
            }
            
            
            $conn->execute("SELECT $function_execute");
            //Desbloqueo el registro creado en SIGNER
            $conn->execute("SELECT func_desbloquear_uno FROM signer.func_desbloquear_uno($sign_id::INTEGER)");                    

            unlink(__DIR__."/df_".$sign_id.".zip");
            unlink(__DIR__."/".$file_firmado);

            //ENVIA CORREOS
            $signerEmails=new signerEmails_SQLlista();
            $signerEmails->wherePadreID($sign_id);
            $signerEmails->orderUno();
            $sql=$signerEmails->getSQL();                
            $rsEmails = new query($conn, $sql);
            if($rsEmails->numrows()>0){//si hay registros para eviar correo
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
                
                while ($rsEmails->getrow()) {
                    $siem_id=$rsEmails->field('siem_id');
                    $subject=$rsEmails->field('siem_subject');
                    $body=$rsEmails->field('siem_body');
                    $email=$rsEmails->field('siem_email');
                    $persona=$rsEmails->field('siem_persona');
                    
                    if( $email ){
                        
                        $signerFiles=new signerFiles_SQLlista();
                        $signerFiles->wherePadreID($sign_id);
                        $signerFiles->orderUno();
                        $sql=$signerFiles->getSQL();
                        $rsSignerFiles = new query($conn, $sql);
                        
                        if($rsSignerFiles->numrows()>0){//si hay archivos para eviar
                            $mail = new Zend_Mail();

                            $mail->setBodyHtml(utf8_decode($persona)."		
                                                            <br>$body!
                                                            <br><br>
                                                            <b>Enviado Desde:</b> Sistema de Informaci&oacute;n-".SIS_EMPRESA.
                                                             "<br><b>IMPORTANTE:</b> NO responda a este Mensaje")

                                ->setFrom($email_from,'SISADMIN '.SIS_EMPRESA_SIGLAS)
                                ->setSubject(utf8_decode($subject))
                                ->addTo($email, 'Destinatario');
                            
                                /*AGREGO LOGO*/
//                                $name_file="logo_". strtolower(SIS_EMPRESA_SIGLAS).".png";
//                                $depe_logo_path="$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/img/". $name_file;
//
//                                if(file_exists($depe_logo_path) && $name_file){    
//                                    $content = file_get_contents(null); // e.g. ("attachment/abc.pdf")
//                                    $attachment = new Zend_Mime_Part($depe_logo_path);
//                                    $attachment->type = 'application/png';
//                                    $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
//                                    $attachment->encoding = Zend_Mime::ENCODING_BASE64;
//                                    $attachment->filename = $name_file; // name of file
//                                    $mail->addAttachment($attachment);
//                                }
                                /*FIN AGREGO LOGO*/
                                
                            //agrego archivos al envo
                            while ($rsSignerFiles->getrow()) {

                                    $nameFileFullPath=$rsSignerFiles->field('sifi_file_origen');
                                    $name_file=$rsSignerFiles->field('sifi_name_file');
                                        if(file_exists($nameFileFullPath) && strpos(strtoupper($nameFileFullPath),'.PDF')>0){
                                            $content = file_get_contents($nameFileFullPath); // e.g. ("attachment/abc.pdf")
                                            $attachment = new Zend_Mime_Part($content);
                                            $attachment->type = 'application/pdf';                                            
                                            $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                                            $attachment->encoding = Zend_Mime::ENCODING_BASE64;
                                            $attachment->filename = $name_file; // name of file
                                            $mail->addAttachment($attachment);
                                        }                                    
                            }

                                try {
                                    $ahora=date('d/m/Y h:i:s');                                    
                                    $mail->send();
                                    $jsondata["success"] = True;
                                    $jsondata["mensaje"] = "Envio de Correo Exitoso";

                                } catch (Exception $e) {
                                        //$mensaje = 'Error al enviar el Correo...Por favor, Comunicarse con el Area de Soporte Inform�tico  <br>
                                        //                    Su mensaje de error es: <br>
                                        //                    '.$e->getMessage();
                                        $mensaje =  $e->getMessage();

                                        //alert($mensaje);
                                        $jsondata["success"] = false;
                                        $jsondata["mensaje"] = "Lo sentimos, No se pudo enviar Correo: ".$mensaje;                                    
                                }
                                //alert($mensaje);
                                //echo $mensaje;
                        }//fin si hay archivo para envias
                    }//fin hay el dato correo

                }//fin si hay registros en la tabla de correos
                                        
            }
           
            $conn->close();
            //FIN CONTROL DE FIRMADO       
        }else{
            echo "<p>El proceso de firma ha sido cancelado o ha ocurrido un error al firmar el documento</p>";
            exit;            
        }        
        
    }
    
    header("Location: sign-end-ok.php?file=".$name); /* Redireci�n del navegador */
    exit();
} else {
    header("Location: sign-end-error.php"); /* Redireci�n del navegador */
    exit();
}