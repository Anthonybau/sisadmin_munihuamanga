<?php
require_once('../../library/clases/entidad.php');
require_once('../../modulos/signer/signer.php');

class Firmar extends selectSQL {

	function __construct(){
            
        }
        

        function beforeFirma($id,$defi_id)
        {
            global $conn;
            
            $objResponse = new xajaxResponse();
            
            //$xFirmar=NEW despachoFirmas_SQLlista();
            //$xFirmar->whereID($defi_id);
            //$xFirmar->setDatos();

            $i=1;
            $ok=1;


            /*Verifico si Esta Pendiente de Firma*/                        
            $xFirmar=NEW despachoFirmas_SQLlista();
            
            if(is_array($id)){//PROCESA VARIOS
                $id_varios=implode(",",$id);
                $xFirmar->wherePadreIDVarios($id_varios);
                $xFirmar->wherePersID(getSession("sis_persid"));//persona logueda
            }else{//PROCESA UNO
                $xFirmar->whereID($defi_id);
            }
            
            $xFirmar->orderDos();
            $sql=$xFirmar->getSQL();
            $rsxFirmar = new query($conn, $sql);
            
            if($rsxFirmar->numrows()>0){
                while ($rsxFirmar->getrow()) {//INICIO WHILE TODOS
                    if($i==1){//Si es el primer registro

                        $firma_posiscion=$rsxFirmar->field('defi_posicion');
                        $firma_tipo=$rsxFirmar->field('defi_tipo'); //1->FIRMA, 2->VISTO DE JEFE, 3->VISTO DE EMPLEADO
                        $job=$rsxFirmar->field('cargo');

                        $dni=$rsxFirmar->field('pers_dni');
                    }

                    $desp_id=$rsxFirmar->field('desp_id');
                    $ar_defi_id[]=$rsxFirmar->field('defi_id');
                    $firma_estado=$rsxFirmar->field('defi_estado');                

                    if( $firma_estado==0 ) //NO FIRMADO
                    {
                        //todos los registros deben ir en la misma posicion                    
                        if($firma_posiscion==$rsxFirmar->field('defi_posicion') && $firma_tipo==$rsxFirmar->field('defi_tipo') && $job==$rsxFirmar->field('cargo')){

                            $sql="SELECT a.desp_anno,
                                         a.desp_id_rand,
                                         signer.func_estado_bloqueo (sign_fregistro, sign_bloqueo) AS estado_bloqueo
                                         FROM gestdoc.despachos a
                                         LEFT JOIN signer.signer b ON a.sign_id=b.sign_id
                                         WHERE a.desp_id=$desp_id ";

                            $rsDoc = new query($conn, $sql);
                            $rsDoc->getrow();
                            $periodo=$rsDoc->field('desp_anno');
                            $id_rand=$rsDoc->field('desp_id_rand');
                            $estado_bloqueo=$rsDoc->field('estado_bloqueo');
                            
                            if($estado_bloqueo==0){//SI EL DOCUMENTO ESTA DESBLOQUEADO

                                /*OJO RUTA ABSOLUTA*/
                                $directorioOrigen="$_SERVER[DOCUMENT_ROOT]/docs/gestdoc/".SIS_EMPRESA_RUC."/$periodo/".$desp_id."/";
                                $directorioDestino="../../../../firmar/";
                                
                                /*OJO RUTA ABSOLUTA*/
                                $path_extract="$_SERVER[DOCUMENT_ROOT]/docs/gestdoc/".SIS_EMPRESA_RUC."/$periodo/".$desp_id."/";

                                if($i==1){//Si es el primer registro
                                    /*agrego archivo a firmar*/
                                    $formdata['sign_dni']=$dni;
                                    $formdata['sign_posicion']=$firma_posiscion;
                                    $formdata['sign_tipo_razon']=$firma_tipo;
                                    $formdata['sign_job']=$job;

                                    $formdata['sign_path_extract']=$path_extract;

                                    $signer=new signer();
                                    $arInsert=$signer->insert($formdata); //INSERTA Y BLOQUE EL REGISTRO

                                    $sign_id=$arInsert['success'];
                                    $mensaje_insert=$arInsert['mensaje'];

                                    $fileZIP = "df_".$sign_id.".zip";
                                    $filePathZIP = $directorioDestino.$fileZIP;
                                    //GENERO EL EMPAQUETADO
                                    $zip = new ZipArchive();
                                    $zip_open=$zip->open($filePathZIP, ZIPARCHIVE::CREATE|ZipArchive::OVERWRITE);

                                }


                                if( $sign_id>0 ){//INSERCION CON EXITO

                                    //ACTUALIZO EL ID DEL SIGNER EN LA TABLA DESPACHOS
                                    $estado_update=getDbValue("UPDATE gestdoc.despachos 
                                                                    SET sign_id=$sign_id 
                                                                 WHERE desp_id=$desp_id RETURNING desp_id");

                                    
                                    if( $estado_update>0 ) //ACTUALIZACION CON EXITO
                                    {


                                            if ($zip_open === true) {

                                                //AGREGO EL DOCUMENTO PADRE
                                                $file = "df_".$id_rand.".pdf";
                                                $filePath = $directorioOrigen.$file;

                                                if (file_exists($filePath)) 
                                                {
                                                  $zip->addFile($filePath, $file);

                                                  $formdataItem['sign_id']=$sign_id;
                                                  $formdataItem['sifi_file_origen']=$filePath;
                                                  $formdataItem['sifi_path_destino']=$path_extract;
                                                  $formdataItem['sifi_name_file']=$file;
                                                  $formdataItem['sifi_file_zip']='';
                                                  $arInsertItem=$signer->insertItem($formdataItem); //INSERTA Y BLOQUE EL EGISTRO
                                                  $signItem_id=$arInsertItem['success'];

                                                  if( $signItem_id>0 ){
                                                  }else{
                                                    $ok=0;
                                                    $mensaje="Proceso cancelado, Error al Insertar Item en archivo Signer Detalle (Documento $desp_id)";
                                                    $zip->close();  
                                                    break;
                                                  }

                                                }

                                                //AGREGO TODOS LOS PDF ADJUNTADO PARA FIRMAS
                                                $despachoAdjuntados=NEW despachoAdjuntados_SQLlista();
                                                $despachoAdjuntados->wherePadreID($desp_id);
                                                $despachoAdjuntados->whereFirmar();
                                                $despachoAdjuntados->whereNOZIP();
                                                $sql=$despachoAdjuntados->getSQL();
                                                $rs = new query($conn, $sql);

                                                while ($rs->getrow()) {
                                                    $file = $rs->field('area_adjunto');
                                                    $filePathAdjunto = $directorioOrigen.$file;
                                                    if(strpos(strtoupper($file),'.PDF')>0){ //si existe PDF
                                                        if (file_exists($filePathAdjunto)) 
                                                        {
                                                            $zip->addFile($filePathAdjunto, $file);
                                                            $formdataItem['sign_id']=$sign_id;
                                                            $formdataItem['sifi_file_origen']=$filePathAdjunto;
                                                            $formdataItem['sifi_path_destino']=$path_extract;
                                                            $formdataItem['sifi_name_file']=$file;
                                                            $formdataItem['sifi_file_zip']='';
                                                            $arInsertItem=$signer->insertItem($formdataItem); //INSERTA Y BLOQUE EL EGISTRO
                                                            $signItem_id=$arInsertItem['success'];
                                                            if( $signItem_id>0 ){
                                                            }else{
                                                              $ok=0;
                                                              $mensaje="Proceso cancelado, Error al Insertar Item PDF Adjunto en archivo signer (Documento $desp_id)";
                                                              $zip->close(); 
                                                              break;
                                                            }
                                                        }
                                                    }
                                                }
                                                //FIN AGREGO TODOS LOS PDF ADJUNTADO PARA FIRMAS

                                                if($ok==1){
                                                    //BUSCO SI EXISTE ARCHIVO ZIP ADJUNTADO
                                                    $despachoAdjuntados=NEW despachoAdjuntados_SQLlista();
                                                    $despachoAdjuntados->wherePadreID($desp_id);
                                                    $despachoAdjuntados->whereFirmar(); //archivos paaa firma
                                                    $despachoAdjuntados->whereZIP();//busco el zip (soloacepta un zip)
                                                    $sql=$despachoAdjuntados->getSQL();
                                                    $rs = new query($conn, $sql);

                                                    while ($rs->getrow()) {
                                                        $ar_dead_id[]=$rs->field('dead_id');
                                                        $file = $rs->field('area_adjunto');
                                                        $filePathAdjunto = $directorioOrigen.$file;
                                                        if (file_exists($filePathAdjunto)) 
                                                        {

                                                            //renombro caracteres raros
                                                            $zipAdjunto = new ZipArchive();
                                                            if ($zipAdjunto->open($filePathAdjunto) === true){
                                                                for($i = 0; $i < $zipAdjunto->numFiles; $i++) {
                                                                        $filename = $zipAdjunto->getNameIndex($i,ZipArchive::FL_UNCHANGED);
                                                                        if(strpos(strtoupper($filename),'.PDF')>0){ //si existe PDF
                                                                                $filenameNvo=str_replace("α", "", $filename);
                                                                                $miValidacion=new miValidacionString();
                                                                                $filenameNvo=$miValidacion->replace_nameFile($filenameNvo);  
                                                                                $zipAdjunto->renameName($filename,$filenameNvo);
                                                                        }
                                                                }
                                                            }                                            
                                                            $zipAdjunto->close();

                                                            if ($zipAdjunto->open($filePathAdjunto) === true){
                                                                 //EXTRAIGO EL ARCHIVO ADJUNTO
                                                                $zipAdjunto->extractTo($directorioOrigen);
                                                                //PASA LOS ZIP
                                                                for($i = 0; $i < $zipAdjunto->numFiles; $i++) {
                                                                        $filename = $zipAdjunto->getNameIndex($i);

                                                                        $filePDFAdjunto=$directorioOrigen.$filename;

                                                                         if (file_exists($filePDFAdjunto)){
                                                                            //$objResponse->addAlert($directorioOrigen.' + '.$filePathAdjunto.' + '.$filePDFAdjunto);
                                                                             
                                                                            if(strpos(strtoupper($filename),'.PDF')>0){ //si existe PDF
                                                                                //AÑADO EL ARCHIVO PARA FIRMA
                                                                                $zip->addFile($filePDFAdjunto, $filename);
                                                                                $formdataItem['sign_id']=$sign_id;
                                                                                $formdataItem['sifi_file_origen']=$filePDFAdjunto;
                                                                                $formdataItem['sifi_path_destino']=$path_extract;
                                                                                $formdataItem['sifi_name_file']=$filename;
                                                                                $formdataItem['sifi_file_zip']=$file;
                                                                                $formdataItem['sifi_indice_zip']=$i;
                                                                                $arInsertItem=$signer->insertItem($formdataItem); //INSERTA Y BLOQUE EL EGISTRO
                                                                                $signItem_id=$arInsertItem['success'];
                                                                                if( $signItem_id>0 ){
                                                                                }else{
                                                                                  $ok=0;                                                                                    
                                                                                  $mensaje="Error al Insertar Item ZIP Adjunto en archivo signer (Documento $desp_id)";
                                                                                  $zip->close();  
                                                                                  break;
                                                                                } 
                                                                            }
                                                                         }
                                                                }

                                                                $zipAdjunto->close();

                                                               }                                               
                                                            }
                                                        }
                                                }else{
                                                    break;
                                                }


                                            }


                                    }
                                    else
                                    {
                                        $zip->close();
                                        $ok=0; 
                                        $mensaje="Proceso cancelado, Error en la Actualización del Documento $desp_id ";
                                        break;
                                    }                       
                                }
                                else
                                {   
                                    $zip->close();
                                    $ok=0;
                                    $mensaje_insert=str_replace("\n","\\n",$mensaje_insert); // Para controlar los retornos de carro que devuelve el postgres
                                    $mensaje_insert=str_replace("\"","\'",$mensaje_insert); 
                                    $mensaje="Proceso cancelado, Error al Insertar registro en SIGNER (Documento $desp_id) ".$mensaje_insert;
                                    break;
                                }




                            }else{
                                $ok=0; 
                                $mensaje="Proceso cancelado, Documento $desp_id esta siendo utilizado por otro Firmante, reintente en un momento...";
                                break;
                            }

                        }//fin si es la misma posiscion
                        else
                        {
                            $ok=0; 
                            $mensaje="Proceso cancelado, Posisción de Firma o Tipo de Firma o Cargo del Firmante en Documento $desp_id es Diferente a los Otros!";
                            break;
                        }                    
                    }//Fin si estado es no firmado
                    else
                    {
                        $ok=0; 
                        $mensaje="Proceso cancelado, Documento $desp_id ya firmado!";
                        break;
                    }

                    $i++;

                }//FIN WHILE TODOS
            }else{
                $ok=0; 
                $mensaje="No se encontraron registros para procesar...".$id_varios;
            }
            
            //si todo esta OK
            if($ok==1){
                $zip->close();
                
                //si existe array de archivos adjuntados
                if(isset($ar_dead_id)){
                    $dead_id_varios=implode("",$ar_dead_id);
                    //ELIMINO LOS ZIP descomprimidos
                    $despachoAdjuntados=NEW despachoAdjuntados_SQLlista();
                    $despachoAdjuntados->whereIDVarios($dead_id_varios);
                    $despachoAdjuntados->whereFirmar(); //archivos paaa firma
                    $despachoAdjuntados->whereZIP();//busco el zip (soloacepta un zip)
                    $sql=$despachoAdjuntados->getSQL();
                    $rs = new query($conn, $sql);
                    while ($rs->getrow()) {
                            $file = $rs->field('area_adjunto');
                            $filePathAdjunto = $directorioOrigen.$file;
                            if (file_exists($filePathAdjunto)) 
                            {
                                $zipAdjunto = new ZipArchive();
                                if ($zipAdjunto->open($filePathAdjunto) === true){

                                    for($i = 0; $i < $zipAdjunto->numFiles; $i++) {
                                            $filename = $zipAdjunto->getNameIndex($i);
                                            $filePDFAdjunto=$directorioOrigen.$filename;

                                             if (file_exists($filePDFAdjunto)){
                                                 unlink($filePDFAdjunto);
                                             }
                                    }

                                    $zipAdjunto->close();

                                   }                                               
                                }
                    }//fin el while                   
                    //FIN ELIMINO LOS COMPRIMIDOS

                }//FIN isset
                
                //AGREGO FUNCION A EJECUTAR DESPUES DE LA FIRMA
                $defi_id_todos=implode(",",$ar_defi_id);
                $sql="UPDATE signer.signer SET sign_function=$$". "gestdoc.func_set_firmar('$defi_id_todos')". "$$ WHERE sign_id=$sign_id ";
                $conn->execute($sql);
                $error=$conn->error();		
		if($error) {
                    $error=str_replace("\n","\\n",$error); // Para controlar los retornos de carro que devuelve el postgres
                    $error=str_replace("\"","\'",$error); // Para controlar los retornos de carro que devuelve el postgres
                    $objResponse->addScript("$('#msg-myModalFirma').text( \"$error\");
                                                                    $('#myModalFirma').modal('show');                                                                                            
                                                                   ");                                    
                }else{

                    if(MOTOR_FIRMA=='2'){//REFIRMA RENIEC
                        if (isOsWin()){//windows
                            //instalar 7z1900-x64.exe hallado en la carpeta invoker-pcx/instalador 
                            //o descargar desde https://7zip-es.updatestar.com/
                            $salida = shell_exec('cd ..\..\..\..\firmar && arepack_to7z.bat '.$fileZIP.' && del '.$fileZIP);                                        
                        }else{
                            //yum install -y -q atool  ->Instalar en CENTOS 7 arepack
                            //$salida = shell_exec('cd ../../../../firmar/; arepack -e -F 7z '.$fileZIP.'; rm -f '.$fileZIP);
                            
                            //Consulatar ne el drive Resolver el problema de atools en Centos8
                            $salida = shell_exec('cd ../../../../firmar/; sh arepack_to7z.sh '.$fileZIP.'; rm -f '.$fileZIP);
                        }
                    }

                    if(MOTOR_FIRMA=='2'){//REFIRMA RENIEC
                        $objResponse->addScript("$('#myIframe').attr('src', '". PATH_FIRMA ."invoker-pcx/index.php?sign_id=$sign_id')
                                                                 $('#title-myModalFirma').addClass( 'glyphicon glyphicon-pencil' );
                                                                 $('#title-myModalFirma').text( 'Firma de Documento');
                                                                 $('#myModalFirma').modal('show');
                                                                    ");
                    }else{
                        $objResponse->addScript("$('#myIframe').attr('src', '". PATH_FIRMA ."firmar/index.php?sign_id=$sign_id')
                                                                 $('#title-myModalFirma').addClass( 'glyphicon glyphicon-pencil' );
                                                                 $('#title-myModalFirma').text( 'Firma de Documento');
                                                                 $('#myModalFirma').modal('show');
                                                                    ");
                    }
                }
                
            }else{
                $objResponse->addScript("$('#msg-myModalFirma').text( \"$mensaje\");
                                                                    $('#myModalFirma').modal('show');                                                                                            
                                                                   ");
            }  
            return $objResponse;
        }                                        
        
}


function setFirma($id)
{
    global $conn;
    $objResponse = new xajaxResponse();
    
    $sSql="SELECT gestdoc.func_set_firmar('$id');";

    // Ejecuto el string
    $conn->execute($sSql);
    $error=$conn->error();

    if($error){ 
            $objResponse->addAlert($error);
    }else{
        unset($_SESSION["ocarrito"]);
        $objResponse->addScript("parent.content.location.reload()");    
    }
    return $objResponse;
}