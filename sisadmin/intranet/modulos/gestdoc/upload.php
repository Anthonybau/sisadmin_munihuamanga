<?php
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
$usua_id=getSession('sis_userid');
        
if($usua_id){
    
        $conn = new db();
        $conn->open();

        $setTable='despachos_adjuntados'; //nombre de la tabla
        $setKey='dead_id'; //campo clave
        $id=$_POST['desp_id'];
        $dede_id=$_POST['dede_id'];        
        $descripcion=$_POST['detalle'];

        $typeKey="Number"; //tipo  de dato del campo clave
        
        $sql="SELECT a.desp_anno AS periodo,
                     b.tiex_adjuntos_para_firma
                 FROM gestdoc.despachos a
                 LEFT JOIN catalogos.tipo_expediente b ON a.tiex_id=b.tiex_id
                 WHERE a.desp_id=$id
                ";
        $rsVerifica = new query($conn, $sql);
        $rsVerifica->getrow();
        $periodo = $rsVerifica->field('periodo');
        $tiex_adjuntos_para_firma = $rsVerifica->field('tiex_adjuntos_para_firma');
    
        include('./makeDirectory.php');
        
        //echo count($_FILES["file0"]["name"]);exit;
        //$errors[]=count($_FILES["file0"]["name"]);

        if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_FILES["fileToUpload"]["type"][0])){

            $max_filesize=ini_get('upload_max_filesize');
            $max_filesize= intval(str_replace("M","",$max_filesize));
            
            for ($i = 0; $i < count($_FILES["fileToUpload"]['name']); $i++) {
                $exactName = basename($_FILES["fileToUpload"]['name'][$i]);
                $fileTmp = $_FILES["fileToUpload"]['tmp_name'][$i];
                $fileSize = $_FILES["fileToUpload"]['size'][$i]; /*the size in bytes)*/
                $error = $_FILES["fileToUpload"]['error'][$i];
                $type = $_FILES["fileToUpload"]['type'][$i];
                
                $exactName=str_replace("α", "", $exactName);
                $miValidacion=new miValidacionString();
                $exactName=$miValidacion->replace_nameFile($exactName);  
                
                $nvo_file="df_".$id."_".$exactName;
                $target_file= PUBLICUPLOAD."gestdoc/".SIS_EMPRESA_RUC."/$periodo/".$id."/".$nvo_file ;
                
                if (file_exists($target_file)) {
                    $jsondata["success"] = false;
                    $jsondata["mensaje"] = "Lo sentimos, su Archivo ya fue Subido, reintente cambiando de archivo.";
                    break;
                }elseif (($fileSize/(1024*1024)) > $max_filesize) { //de bytes a megas
                    $jsondata["success"] = false;
                    $jsondata["mensaje"] = "Lo sentimos, el archivo $exactName NO fue subido, es muy extenso ".($fileSize/(1024*1024))."M";
                    break;
                } elseif (move_uploaded_file($fileTmp, $target_file)) {
                    
                    $sql = new UpdateSQL();
                    $sql->setTable($setTable);
                    $sql->setKey($setKey,"",$typeKey);
                    $sql->setAction("INSERT"); /* Operación */

                    $sql->addField('desp_id',$id, "Number");
                    $descripcion=$descripcion?strtoupper($descripcion):$exactName;
                    $sql->addField('dead_descripcion',$descripcion, "String");
                    $sql->addField('area_adjunto',$nvo_file, "String");
                    
                    if($dede_id){
                        $sql->addField('dede_id',$dede_id, "Number");
                    }
                    
                    if($tiex_adjuntos_para_firma==1){
                        $sql->addField('dead_signer',1, "Number");    
                    }
                    
                    //IDENTIFICA SI ARCHIVO ZIP
                    if(strpos(strtoupper($type),'ZIP')>0){
                        $sql->addField('dead_zip',1, "Number");                            
                    }
                    
                    
                    $sql->addField('usua_id',$usua_id, "Number");
                    $descripcion='';
                    $sql=$sql->getSQL();
                    $return=$conn->execute($sql); 
                    $error=$conn->error();
                    if($error){
                        $jsondata["success"] = false;
                        $jsondata["mensaje"] = $error;
                        break;
                     }else{
                        $jsondata["success"] = true;
                        $jsondata["mensaje"] = 'Archivo Subido Correctamente';
                     }
         
                     
                }    
                else{
                    $jsondata["success"] = false;
                    $jsondata["mensaje"] = "Lo sentimos, el archivo $exactName NO fue subido";                        
                    break;
                }                
            }

        }
        
        $conn->close();

}else{
    $jsondata["success"] = false;
    $jsondata["mensaje"] = "Lo sentimos, No se hallo sesion de usuario iniciado";
}


header('Content-Type: application/json');
echo json_encode($jsondata, JSON_FORCE_OBJECT);