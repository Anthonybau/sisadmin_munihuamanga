<?php
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
$usua_id=getSession('sis_userid');
        
if($usua_id){
    
        $conn = new db();
        $conn->open();


        $id=$_POST['desp_id'];
        $defi_id=$_POST['defi_id'];
//        $setTable='despachos_adjuntados'; //nombre de la tabla
//        $setKey='dead_id'; //campo clave
//        $typeKey="Number"; //tipo  de dato del campo clave
        
        //echo count($_FILES["file0"]["name"]);exit;
        //$errors[]=count($_FILES["file0"]["name"]);

        if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_FILES["fileToUpload"]["type"])){
            
                $exactName = basename($_FILES["fileToUpload"]['name']);
                $fileTmp = $_FILES["fileToUpload"]['tmp_name'];
                $fileSize = $_FILES["fileToUpload"]['size'];
                $error = $_FILES["fileToUpload"]['error'];
                $type = $_FILES["fileToUpload"]['type'];

                require_once("registroDespacho_class.php");
                
                $despacho=new despacho_SQLlista();
                $despacho->whereID($id);
                $despacho->setDatos(); 
                $periodo=$despacho->field('desp_anno');
                
                include('./makeDirectory.php');
                
                $name_file="df_".$id."_".$exactName;
                $nameFileFullPath = PUBLICUPLOAD."gestdoc/".SIS_EMPRESA_RUC."/$periodo/$id/$name_file";
                
//                if (file_exists( $nameFileFullPath )) {
//                    $messages[] = [
//                                    'status'  => 'error',
//                                    'code'    => 'doubleFile',
//                                    'message' => "Lo sentimos, su archivo $exactName YA existe en este u otro Expediente",
//                                    ];
//            
//                }else{
                    if (move_uploaded_file($fileTmp, $nameFileFullPath)) {
                    
//                            $sql = new UpdateSQL();
//                            $sql->setTable($setTable);
//                            $sql->setKey($setKey,"",$typeKey);
//                            $sql->setAction("INSERT"); /* Operación */
//
//
//                            $sql->addField('desp_id',$desp_id, "Number");
//                            $sql->addField('dead_descripcion','ADJUNTADO AL EXPEDIENTE (e-Firma)', "String");
//                            $sql->addField('area_adjunto',$nvo_file, "String");
//                            $sql->addField('dead_firmar',1, "Number");
//                            $sql->addField('usua_id',$usua_id, "Number");
//                            $sql=$sql->getSQL();
                        
                            $sql="UPDATE gestdoc.despachos
                                    SET desp_file_firmado='$name_file',
                                        desp_file_firmado_upload_fregistro=NOW(),
                                        desp_file_firmado_upload=1
                                    WHERE desp_id=$id;                                        
                                        
                                    SELECT gestdoc.func_firmar(2,'$defi_id','');                                        
                                    ";
                            $return=$conn->execute($sql); 
                            $error=$conn->error();
                            
                            if($error){
                                     $messages[] = [
                                            'status'  => 'error',
                                            'code'    => 'saveRecord',
                                            'message' => $error,
                                        ];
                             }else{
                                 $messages[] = [
                                            'status'  => 'ok',
                                            'code'    => 'saveRecord',
                                            'message' => "El archivo $exactName fue subido Correctamente ",
                                        ];
                             }
                                     
                        }    
                        else{
                            $messages[] = [
                                            'status'  => 'error',
                                            'code'    => 'somethingwrong',
                                            'message' => "Lo sentimos, el archivo $exactName NO fue subido ",
                                        ];
                        }                
//                }
                
        }else{
                    $messages[] = [
                                    'status'  => 'error',
                                    'code'    => 'somethingwrong',
                                    'message' => "Lo sentimos, el archivo NO fue subido ",
                                ];
            
        }

        $conn->close();
            
}


if (isset($messages)){
    foreach ($messages as $message){
            echo json_encode($message);
    }
}