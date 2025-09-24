<?php
//SIRVE PARA COLOCAR LOS ACHIVOS ADJUNTOS EN SU RESPECTIVA CARPETA
include("../../library/library.php");
include("./registroDespacho_edicionAdjuntosClass.php"); 

/* tratamiento de campos */
$depe_id = $_GET['depe_id'];
if($depe_id>0){   
    try {
        $arHost= explode(":",DB_HOST);
        $db = new PDO("pgsql:host=$arHost[0];port=$arHost[1];dbname=".DB_DATABASE, DB_USER, DB_PASSWORD);      
        
        $target_dir=PUBLICUPLOAD."gestdoc/";        
        $adjuntados=new despachoAdjuntados_SQLlista();
        $adjuntados->orderUno();
        $data = $db->query($adjuntados->getSQL());

        while ($row = $data->fetch()) {
            $periodo = $row['periodo'];
            $id = $row['desp_id'];
            $file=$row['area_adjunto'];
            $target_file = $target_dir . $file;
            $nvoPath_file= $target_dir.SIS_EMPRESA_RUC."/$periodo/".$id."/".$file ;
            if( file_exists( $target_file ) ){
                include('./makeDirectory.php');
                rename($target_file, $nvoPath_file);
                echo "moviendo $target_file -> $nvoPath_file"."\n";
            }
        }
        $db = null;

    } catch (PDOException $e) {
        print "¡Error!: " . $e->getMessage() . "<br/>";
        die();
    }
}