<?php
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/modulos/catalogos/catalogosTipoExpediente_class.php");
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/modulos/catalogos/catalogosDependencias_class.php");
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/modulos/admin/adminUsuario_class.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', 1800);

error_reporting(E_ALL);
//include("../recaptcha/keys.php");

//if( $_POST['token'] ){
//    
//    
//
//
//$url = "https://www.google.com/recaptcha/api/siteverify";
//$data = [
//        'secret' => SECRET_KEY,
//        'response' => $_POST['token'],
//        // 'remoteip' => $_SERVER['REMOTE_ADDR']
//];
//
//$options = array(
//    'http' => array(
//      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
//      'method'  => 'POST',
//      'content' => http_build_query($data)
//    )
//  );
//
//$context  = stream_context_create($options);
//$response = json_decode(file_get_contents($url, false, $context), true);
//
//if($response['success'] == true) {

$conn = new db();
$conn->open();

if(isset($_POST['id'])){
    $depe_id=intval($_POST['id']);
}else{
    $depe_id=0;
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
                                     ORDER BY depe_id
                                     LIMIT 1
                                     ");
if($hora_activo==1){
        
        
        $data = $_POST;
        
        //SI ES PARA SUBSANAR OBSERVACIONES
        if(isset($data['pass'])){
            $pass=$data['pass'];
            $array = explode("_",$pass);
            $id_rand = base64_decode($array[0]);
            $desp_id = base64_decode($array[1]);
            include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/modulos/gestdoc/registroDespacho_class.php");
                
            $despacho=new despacho_SQLlista();
            $despacho->whereID($desp_id);
            $despacho->whereIDRand($id_rand);
            $despacho->setDatos();
                
            if($despacho->existeDatos()){
                $ocumento_codigo_seguridad = $data['documento_codigo_seguridad'];
                
                if($despacho->field('desp_notificacion_codigo_seguridad')==$ocumento_codigo_seguridad){
                    $persona_tipodocumento = $despacho->field('desp_tipo_persona');
                    $persona_numerodocumento = $despacho->field('desp_codigo');
                    $persona_nombre = $despacho->field('desp_firma');
                    $persona_telefono = $despacho->field('desp_telefono');
                    $persona_email = $data['persona_email'];

                    $documento_tipodocumento = $despacho->field('tiex_id');
                    $documento_numerodocumento = $despacho->field('desp_numero');
                    $documento_siglas = $despacho->field('desp_siglas');
                    $documento_numerofolios = $despacho->field('desp_folios');        
                    $documento_tipo_tramite = $despacho->field('proc_id');        
                    $documento_asunto = $despacho->field('desp_asunto');
                    $desp_url_mas_files = $despacho->field('desp_url_mas_files');
                    $desp_id_origen = $despacho->field('desp_id');
                    $desp_expediente = $despacho->field('desp_expediente');
                    $ok=1;
                }else{
                    $respuesta = false;
                    $mensaje = "Lo sentimos, Código de Seguidad No es correcto." ;
                    $ok=0;
                }
            }else{
                $respuesta = false;
                $mensaje = "Lo sentimos, no se encontro Documento de Noticación." ;
                $ok=0;
            }
        }else{
            $validacion = new miValidacionString();
            $id = $data['id'];//parametro que se recibe Del index
            //$tipo_documento = $data['tipo_documento'];
            $persona_tipodocumento = $data['persona_tipodocumento'];
            $persona_numerodocumento = $data['persona_numerodocumento'];
            $persona_nombre = $validacion->replace_nameFile($data['persona_nombre']);
            $persona_telefono = $validacion->replace_invalid_caracters($data['persona_telefono']);
            $persona_email = $data['persona_email'];

            $documento_tipodocumento = $data['documento_tipodocumento'];
            
            $documento_annodocumento = intval($data['documento_annodocumento']);
            $documento_annodocumento = $documento_annodocumento>0?$documento_annodocumento:0;
            
            $documento_numerodocumento = intval($data['documento_numerodocumento']);
            $documento_numerodocumento = $documento_numerodocumento>0?$documento_numerodocumento:0;
            
            $documento_siglas = $validacion->replace_invalid_caracters($data['documento_siglas']);
            
            $documento_numerofolios = intval($data['documento_numerofolios']);
            $documento_numerofolios = $documento_numerofolios>0?$documento_numerofolios:0;
            
            $documento_tipo_tramite = $data['documento_tipo_tramite'];        
            $documento_asunto = $validacion->replace_nameFile($data['documento_asunto']);
            $desp_url_mas_files = 0;
            $desp_id_origen=0;
            $ok=1;
        }
        
        if($ok==1){
            if( $documento_tipo_tramite > 0 ){


                $setTable='gestdoc.despachos';
                $setKey='desp_id';
                $typeKey='Number';

                $sql = new UpdateSQL();
                $sql->setTable($setTable);
                $sql->setKey($setKey,"",$typeKey);
                $sql->setAction("INSERT"); /* Operación */
                $sql->addField('desp_secuencia_automatica',0, "Number"); //no GENERA SECUENCIA LA BD           
                $sql->addField('desp_procesador',2, "Number"); //DESDE MESA DE PARTES VIRTUAL         
                $sql->addField('tabl_tipodespacho',142, "Number"); //EXTERNO
                $sql->addField('desp_fecha','NOW()', "String");

                if($desp_id_origen>0){//SI ES CORRECCION
                    $sql->addField('desp_expediente',$desp_expediente, "Number");
                    $sql->addField('desp_id_origen',$desp_id_origen, "Number");
                }
                
                /*DEPENDENCIA*/
                $dependencia_mpvirtual=new dependencia_SQLlista();
                $dependencia_mpvirtual->whereMPVirtual();
                $dependencia_mpvirtual->setDatos();                    
                if( $dependencia_mpvirtual->existeDatos() ){

                    $depe_id_mpvirtual = $dependencia_mpvirtual->field('depe_id');
    //                $desp_siglas_mpvirtual = $dependencia_mpvirtual->field('depe_siglasdoc');

                    $sql->addField('depe_id',$depe_id_mpvirtual, "Number");

                    /*TIPO DE EXPEDIENTE*/
    //                $tiex_mpvirtual = new clsTipExp_SQLlista();
    //                $tiex_mpvirtual->whereMPVirtual();
    //                $tiex_mpvirtual->setDatos(); 
    //                if( $tiex_mpvirtual->existeDatos() ){
    //                    $tiex_id_mpvirtual = $tiex_mpvirtual->field('tiex_id');

                        $sql->addField('tiex_id',$documento_tipodocumento, "Number");      
                        $sql->addField('desp_numero',$documento_numerodocumento, "String");
                        $sql->addField('desp_anno',$documento_annodocumento, "Number");
                        
                        $sql->addField('desp_siglas',$documento_siglas, "String");            
                        $sql->addField('desp_asunto',  strtoupper($documento_asunto), "String");            
                        $sql->addField('desp_folios',$documento_numerofolios, "Number");


                        $sql->addField('proc_id',$documento_tipo_tramite, "Number");                        
                        $sql->addField("desp_actualfecha", "NOW()", "String");
                        //$sql->addField("desp_actualusua", getSession("sis_userid"), "String");  

                        $sql->addField('desp_tipo_persona',$persona_tipodocumento, "Number");
                        $sql->addField('desp_codigo',$persona_numerodocumento, "String");

                        if($persona_nombre){

                            if($persona_tipodocumento==1){//PERSONA NATURAL
                                $sql->addField('desp_firma',strtoupper($persona_nombre), "String");
                            }else{
                                $sql->addField('desp_entidad_origen',strtoupper($persona_nombre), "String");
                            }

                            $sql->addField('desp_telefono',$persona_telefono, "String");
                            $sql->addField('desp_email',$persona_email, "String");


                            $desp_para_depe_id=getDbValue("SELECT depe_id_destinatario FROM gestdoc.procedimiento WHERE proc_id=$documento_tipo_tramite");
                            if($desp_para_depe_id){
                                $sql->addField('desp_para_depe_id',$desp_para_depe_id, "String");

                                $usuario_mpvirtual = new clsUsers_SQLlista();
                                $usuario_mpvirtual->whereMPVirtual();
                                $usuario_mpvirtual->setDatos(); 
                                if( $usuario_mpvirtual->existeDatos() ){                                        
                                    $usua_id_mpvirtual = $usuario_mpvirtual->field('usua_id');

                                    $sql->addField('usua_id',$usua_id_mpvirtual, "Number");

                                    if($desp_url_mas_files){
                                        $sql->addField('desp_url_mas_files', $desp_url_mas_files, "String");
                                    }

                                    $sql=$sql->getSQL()." RETURNING desp_id::text||'_'||TO_CHAR(desp_fregistro,'DD/MM/YYYY HH:MI:SS AM')";

                                    $target_dir = "../../docs/gestdoc/";
                                    //$target_dir=$_SERVER['DOCUMENT_ROOT'] ."/docs/gestdoc/";

                                    $nvo_file = 'mpv_' .basename($_FILES["documento_archivo"]["name"]);
                                    $target_file = $target_dir . $nvo_file;
                                    $uploadOk = 1;
                                    $fileType = strtolower(pathinfo($_FILES["documento_archivo"]["name"],PATHINFO_EXTENSION));

                                    if( $_FILES["documento_archivo2"]["name"] ){
                                        $nvo_file2 = 'mpv_' .basename($_FILES["documento_archivo2"]["name"]);
                                        $target_file2 = $target_dir . $nvo_file2;
                                        $fileType2 = strtolower(pathinfo($_FILES["documento_archivo2"]["name"],PATHINFO_EXTENSION));
                                        $existe_archivo2=1;
                                    }else{
                                        $existe_archivo2=0;
                                    }
                                    
                                    // Allow certain file formats
                                    $extensiones_aceptadas="pdf,xls,xlsx,doc,docx,ppt,pptx,zip";
                                    if(!inlist($fileType,$extensiones_aceptadas)) {
                                      $respuesta = false;
                                      $mensaje = "Lo sentimos, en el Archivo de Solicitud solo aceptamos ".strtoupper($extensiones_aceptadas);
                                      $uploadOk = 0;
                                    }

                                    if($existe_archivo2==1 && !inlist($fileType2,$extensiones_aceptadas)) {
                                      $respuesta = false;
                                      $mensaje = "Lo sentimos, en el archivo de Requisitos y/o Anexos solo aceptamos ".strtoupper($extensiones_aceptadas);;
                                      $uploadOk = 0;
                                    }
                                    
                                    if(file_exists($target_file)) {
                                        chmod($target_file,0755); //Change the file permissions if allowed
                                        unlink($target_file); //remove the file
                                    }
                                    
                                    // Check if file already exists
                                    if (file_exists($target_file)) {
                                      $respuesta = false;
                                      $mensaje = "Lo sentimos, su Archivo PDF ya fue Subido, reintente cambiando de archivo.";
                                      $uploadOk = 0;
                                    }

                                    if ($existe_archivo2==1 && file_exists($target_file2)) {
                                      $respuesta = false;
                                      $mensaje = "Lo sentimos, su Archivo de Requisitos y/o Anexos ya fue Subido, reintente cambiando de archivo.";
                                      $uploadOk = 0;
                                    }
                                    
                                    // Check file size
                                    if ($_FILES["documento_archivo"]["size"] > 31457280) { //1024*1024*10 10MB  
                                      $respuesta = false;
                                      $mensaje = "Lo sentimos, su Archivo PDF es Demasiado Largo (".$_FILES["documento_archivo"]["size"].")" ;
                                      $uploadOk = 0;
                                    }
                                    if ($existe_archivo2==1 && $_FILES["documento_archivo2"]["size"] > 31457280) { //10MB  
                                      $respuesta = false;
                                      $mensaje = "Lo sentimos, Archivo de Requisitos y/o Anexos es Demasiado Largo (".$_FILES["documento_archivo2"]["size"].")" ;
                                      $uploadOk = 0;
                                    }
                                    //valida si el documento ya fue registrado		
                                    $siglas = $validacion->replace_invalid_caracters($documento_siglas);
                                    $asunto=strtoupper($validacion->replace_invalid_caracters($documento_asunto));
                                    $anno=date('Y');
                                    $existe_documento = getDbValue("SELECT desp_id FROM gestdoc.despachos WHERE desp_numero=$documento_numerodocumento and desp_siglas='$siglas' and tiex_id=$documento_tipodocumento and date_part('year'::text, desp_fecha)=$anno");
                                    if($existe_documento!=''){
                                        $respuesta = false;
                                        $mensaje = "Lo sentimos, su Documento ya fue Registrado, reintente cambiando de número.";
                                        $uploadOk = 0;
                                    }
                                    // Check if $uploadOk is set to 0 by an error
                                    if ($uploadOk == 1) {
                                        if (move_uploaded_file($_FILES["documento_archivo"]["tmp_name"], $target_file)){
                                            
                                            if($existe_archivo2==1){
                                                move_uploaded_file($_FILES["documento_archivo2"]["tmp_name"], $target_file2);
                                            }
                                            //71323194                                        
                                            //EJECUTA EL SQL
//                                                echo $sql;
//                                                exit(0);

                                            $return=$conn->execute($sql); 
                                            $error=$conn->error();

                                            if(!$error){
                                                $expediente=explode("_",$return);
                                                $desp_id=$expediente[0];
                                                $dia=$expediente[1];
                                                
                                                //$periodo = date('Y');
                                                $periodo = $documento_annodocumento;

                                                include("makeDirectory.php");
                                                $nvoPath_file= $target_dir.SIS_EMPRESA_RUC."/$periodo/".$desp_id."/".$nvo_file;
                                                if(rename($target_file, $nvoPath_file)){
                                                        $setTable='gestdoc.despachos_adjuntados'; //nombre de la tabla
                                                        $setKey='dead_id'; //campo clave
                                                        $typeKey="Number"; //tipo  de dato del campo clave

                                                        $sql = new UpdateSQL();
                                                        $sql->setTable($setTable);
                                                        $sql->setKey($setKey,"",$typeKey);
                                                        $sql->setAction("INSERT"); /* Operación */
                                                        $sql->addField('desp_id',$desp_id, "Number");
                                                        $sql->addField('dead_descripcion',$nvo_file, "String");
                                                        $sql->addField('area_adjunto',$nvo_file, "String");                    
                                                        //IDENTIFICA SI ARCHIVO ZIP
                                                        if(strpos(strtoupper($fileType),'ZIP')>0){
                                                            $sql->addField('dead_zip',1, "Number");      
                                                        }
                                                        $sql->addField('usua_id',$usua_id_mpvirtual, "Number");

                                                        $sql=$sql->getSQL();
                                                        $return=$conn->execute($sql); 
                                                        $error=$conn->error();
                                                        if(!$error){
                                                            
                                                            if($desp_id_origen>0){//SI ES CORRECCION
                                                                //PASA LOS ARCHIVOS DEL DOCUMENTO ORIGEN
                                                                include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/modulos/gestdoc/registroDespacho_edicionAdjuntosClass.php");
                                                                $adjuntados=new despachoAdjuntados_SQLlista();
                                                                $adjuntados->wherePadreID("$desp_id_origen");
                                                                $sql = $adjuntados->getSQL();

                                                                $rsFiles = new query($conn, $sql);    
                                                                while ($rsFiles->getrow()) {
                                                                    $periodo_origen=$rsFiles->field("desp_anno");
                                                                    $file=$rsFiles->field("area_adjunto");

                                                                    $path_file_origen  = $target_dir.SIS_EMPRESA_RUC."/$periodo_origen/$desp_id_origen/$file";
                                                                    $nvo_file = str_replace('mpv_','mpv_'.$desp_id_origen.'_',$file);
                                                                    $path_file_destino = $target_dir.SIS_EMPRESA_RUC."/$periodo/".$desp_id."/".$nvo_file;
                                                                    
                                                                    if(strpos(strtoupper($file),'.PDF')>0 && strpos($file, 'mpv_')!==false){
                                                                        if(copy($path_file_origen, $path_file_destino)){
                                                                            
                                                                            $setTable='gestdoc.despachos_adjuntados'; //nombre de la tabla
                                                                            $setKey='dead_id'; //campo clave
                                                                            $typeKey="Number"; //tipo  de dato del campo clave

                                                                            $sql = new UpdateSQL();
                                                                            $sql->setTable($setTable);
                                                                            $sql->setKey($setKey,"",$typeKey);
                                                                            $sql->setAction("INSERT"); /* Operación */
                                                                            $sql->addField('desp_id',$desp_id, "Number");
                                                                            $sql->addField('dead_descripcion',$nvo_file, "String");
                                                                            $sql->addField('area_adjunto',$nvo_file, "String");                    
                                                                            $sql->addField('usua_id',$usua_id_mpvirtual, "Number");

                                                                            $sql=$sql->getSQL();
                                                                            $return=$conn->execute($sql); 
                                                                            $error=$conn->error();
                                                                            
                                                                        }
                                                                    }
                                                                }
                                                                //FIN PASA LOS ARCHIVOS DEL DOCUMENTO ORIGEN
                                                            }
                                                            
                                                            if($existe_archivo2==1){
                                                                $nvoPath_file2= $target_dir.SIS_EMPRESA_RUC."/$periodo/".$desp_id."/".$nvo_file2;
                                                                if(rename($target_file2, $nvoPath_file2)){

                                                                    $setTable='gestdoc.despachos_adjuntados'; //nombre de la tabla
                                                                    $setKey='dead_id'; //campo clave
                                                                    $typeKey="Number"; //tipo  de dato del campo clave

                                                                    $sql = new UpdateSQL();
                                                                    $sql->setTable($setTable);
                                                                    $sql->setKey($setKey,"",$typeKey);
                                                                    $sql->setAction("INSERT"); /* Operación */
                                                                    $sql->addField('desp_id',$desp_id, "Number");
                                                                    $sql->addField('dead_descripcion',$nvo_file2, "String");
                                                                    $sql->addField('area_adjunto',$nvo_file2, "String");                    
                                                                    //IDENTIFICA SI ARCHIVO ZIP
                                                                    if(strpos(strtoupper($fileType2),'ZIP')>0){
                                                                        $sql->addField('dead_zip',1, "Number");      
                                                                    }
                                                                    $sql->addField('usua_id',$usua_id_mpvirtual, "Number");

                                                                    $sql=$sql->getSQL();
                                                                    $return=$conn->execute($sql); 
                                                                    $error=$conn->error();
                                                                    if(!$error){
                                                                    }
                                                                }
                                                            }
                                                            
                                                            $respuesta = true;
                                                            
                                                            $depe_id=$depe_id>0?$depe_id:2;
                                                            $dependencia=new dependencia_SQLlista();
                                                            $dependencia->whereID($depe_id);
                                                            $dependencia->setDatos();
                                                            
                                                            $mensaje=$dependencia->field("depe_mpv_mensaje_registro");
                                                            
                                                            if($mensaje==""){
                                                                $mensaje = "Pronto le informaremos sobre la recepción de su documento.";                                                            
                                                            }
                                                            $mensaje = str_replace("{expe_id}","<b><font size=4px>$desp_id</font></b>",$mensaje);
                                                            $mensaje = str_replace("{fecha_hora}","$dia",$mensaje);
                                                            $mensaje = str_replace("\n","<br>",$mensaje);

                                                            //$mensaje = "Se ha generado su N&uacute;mero de Trámite : <b><font size=4px>$desp_id</font></b> el día $dia <br>Pronto le informaremos el estado de recepción.";


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
    
                                                            $subject_flujo=utf8_decode("NOTIFICACION DE REGISTRO: N° de Trámite $desp_id");
    
                                                            $email=$persona_email;
                                                            $persona_nombre;
                                                            if($email){
                                                                $mail = new Zend_Mail();
    
                                                                $mail->setBodyHtml(utf8_decode($persona_nombre." </b>		
                                                                                                <br>Tu solicitud se ha registrado de manera Exitosa! en nuestra Mesa de Partes Virtual 
                                                                                                <br>$mensaje
                                                                                                <br><br>
                                                                                                <b>Enviado Desde:</b> Sistema de Gesti&oacute;n Administrativa-".SIS_EMPRESA.
                                                                                                 "<br><b>IMPORTANTE:</b> NO responda a este Mensaje"))
    
                                                                    ->setFrom($email_from,'SISADMIN '.SIS_EMPRESA_SIGLAS)
                                                                    ->setSubject($subject_flujo)
                                                                    ->addTo($email, 'Solicitante');
                                                                

                                                                    $depe_logo=$dependencia->field('file_logo');
                                                                    $depe_logo_path='../docs/catalogos/'.SIS_EMPRESA_RUC.'/'.$depe_id.'/'.$depe_logo;

                                                                    if(file_exists($depe_logo_path) && $depe_logo){    
                                                                        $content = file_get_contents($nameFileFullPath); // e.g. ("attachment/abc.pdf")
                                                                        $attachment = new Zend_Mime_Part($content);
                                                                        $attachment->type = 'application/pdf';
                                                                        $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                                                                        $attachment->encoding = Zend_Mime::ENCODING_BASE64;
                                                                        $attachment->filename = $name_file; // name of file
                                                                        $mail->addAttachment($depe_logo_path);
                                                                    }

                                                                
    
    
                                                                try {
                                                                    $ahora=date('d/m/Y h:i:s');                                    
                                                                    $mail->send();
                                                                } catch (Exception $e) {
                                                                        //$mensaje = 'Error al enviar el Correo...Por favor, Comunicarse con el Area de Soporte Informático  <br>
                                                                        //                    Su mensaje de error es: <br>
                                                                        //   
                                                                        //                                     '.$e->getMessage();
                                                                        //$respuesta = true;    
                                                                        $mensaje .= "<BR>";
                                                                        $mensaje .=  $e->getMessage();
                                                                        //alert($mensaje);
                                                                }
                                                                //alert($mensaje);
                                                                //echo $mensaje;
                                                            }
                                                            //FIN DE ENVIO DE CORREO

                                                        }else{
                                                            $respuesta = false;
                                                            $error=str_replace("\n","\\n",$error); // Para controlar los retornos de carro que devuelve el postgres
                                                            $error=str_replace("\"","\'",$error); // Para controlar los retornos de carro que devuelve el postgres
                                                            $mensaje = "Lo Sentimos, su Archivo no puede ser Adjuntado.".$error;
                                                        }
                                                    }else{
                                                        $respuesta = false;
                                                        $mensaje = "Lo Sentimos, su Archivo no puede ser ubicado ";
                                                        if( file_exists($target_file) ){
                                                            unlink($target_file);
                                                        }
                                                    }                                            
                                            }else{
                                                $respuesta = false;
                                                $error=str_replace("\n","\\n",$error); // Para controlar los retornos de carro que devuelve el postgres
                                                $error=str_replace("\"","\'",$error); // Para controlar los retornos de carro que devuelve el postgres
                                                $mensaje = "Lo Sentimos, No se pudo grabar su solicitud: $error";
                                                if( file_exists($target_file) ){
                                                    unlink($target_file);
                                                }
                                            }
                                        } else {
                                          $respuesta = false;
                                          $error=str_replace("\n","\\n",$error); // Para controlar los retornos de carro que devuelve el postgres
                                          $error=str_replace("\"","\'",$error); // Para controlar los retornos de carro que devuelve el postgres                                      
                                          $mensaje = "Lo Sentimos, su Archivo no puede ser Recibido. ".$error;
                                        }
                                    }

                                }else{
                                    $respuesta = false;
                                    $mensaje = "Proceso Cancelado, No se halló Usuario de Mesa de Partes Virtual";
                                }                                    
                            }else{
                                $respuesta = false;
                                $mensaje = "Proceso Cancelado, No se halló Dependencia Destinantario ";                            
                            }                        

                        }else{
                            $respuesta = false;
                            $mensaje = "Proceso Cancelado, No Registró Nombre o Razon Social ";                            
                        }                                                        
    //                }else{
    //                    $respuesta = false;
    //                    $mensaje = "Proceso Cancelado, No se halló Tipo de Documento para Mesa de Partes Virtual";
    //                }
                }else{
                        $respuesta = false;
                        $mensaje = "Proceso Cancelado, No se halló Dependencia para Mesa de Partes Virtual";
                    }   

    //          $respuesta = true;
    //          $mensaje =  $tipo_documento."<BR>".  //CAMBIAR MENSAJE
    //                      $persona_tipodocumento."<BR>".
    //                      $persona_numerodocumento."<BR>".
    //                      $persona_nombre."<BR>".
    //                      $persona_telefono."<BR>".
    //                      $persona_email."<BR>".
    //                      $documento_asunto."<BR>".
    //                      $documento_archivo;


            }else{
                $respuesta = false;
                $mensaje = "Lo Sentimos, NO Selecciono Procedimiento.";
            }        
        }
//    }else{
//        $respuesta = false;
//        $mensaje = "Lo Sentimos, su Solicitud No pudo Ser Procesada.";
//    }
//}else{
//        $respuesta = false;
//        $mensaje = "Lo Sentimos, No se hallaron credenciales de segridad para Continuar.";   
//}
        
}else{
    $respuesta = false;
    $mensaje=getDbValue("SELECT depe_mpv_mensaje_externo
                                FROM catalogos.dependencia
                                WHERE depe_id=CASE WHEN $depe_id>0 THEN $depe_id ELSE 2 END
                                ORDER BY depe_id
                                LIMIT 1
                                ");
                                        
    $mensaje=str_replace(array("\r\n", "\n", "\r"),'<br>',$mensaje);
}                

$conn->close();

            
            
setSession('id',$id);                    
setSession('respuesta',$respuesta);
setSession('mensaje', $mensaje);
//echo     getSession('respuesta')."<br>".getSession('mensaje');
$page="../mensaje.php";
if($id!=""){
    $page.="?id=$id";
}

header("Location: $page");
exit;