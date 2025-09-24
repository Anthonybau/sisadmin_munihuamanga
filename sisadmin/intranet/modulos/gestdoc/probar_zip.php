<?php
$sign_id='1';
$id_rand='943';
define(SIS_EMPRESA_RUC,'20178199251');
$periodo='2021';
$desp_id='216.001';
        
$directorioOrigen="$_SERVER[DOCUMENT_ROOT]/docs/gestdoc/".SIS_EMPRESA_RUC."/$periodo/".$desp_id."/";
$directorioDestino="../../../../firmar/";
$fileZIP = "df_".$sign_id.".zip";
$filePathZIP = $directorioDestino.$fileZIP;
//GENERO EL EMPAQUETADO
$zip = new ZipArchive();
$zip_open=$zip->open($filePathZIP, ZIPARCHIVE::CREATE|ZipArchive::OVERWRITE);
if ($zip_open === true) {
 //AGREGO EL DOCUMENTO PADRE
 echo "Creado!<br>";
 $file = "df_".$id_rand.".pdf";
 $filePath = $directorioOrigen.$file;
 echo $filePath;
 $zip->addFile($filePath, $file);
 
}else{  
    echo "El archivo No se creo!!";
}                               

$zip->close(); 

$salida = shell_exec('cd ../../../../firmar/; ls -lart');
echo "<pre>$salida</pre>";


//shell_exec('cd ../../../../firmar/; ./arepack_to7z.sh '.$fileZIP.'; ');