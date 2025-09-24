<?php
include("../../library/library.php");
include("./sistemas_class.php");

/* tratamiento de campos */
$validate=new miValidacionString();
$sist_id = $validate->replace_invalid_caracters($_GET['sist_id']);
$cadena = $validate->replace_invalid_caracters($_GET['cadena']);
$video_id = $validate->replace_invalid_caracters($_GET['video_id']);

if( $sist_id!='' || $cadena!='' || $video_id!='' ){   
    try {
        $arHost= explode(":",DB_HOST);
        $db = new PDO("pgsql:host=$arHost[0];port=$arHost[1];dbname=".DB_DATABASE, DB_USER, DB_PASSWORD);      
        
        $tutoriales = new clsTutoriales_SQLlista();
        if($video_id){
            $tutoriales->whereVideoID($video_id);
        }else{
            if($cadena){
                $tutoriales->whereDescripcion($cadena);
            }else{
                $tutoriales->wherePadreID($sist_id);
            }
        }
        $tutoriales->orderUno();

        $data = $db->query($tutoriales->getSQL())->fetchAll( PDO::FETCH_ASSOC);
        echo json_encode($data);
        $db = null;

    } catch (PDOException $e) {
        print "¡Error!: " . $e->getMessage() . "<br/>";
        die();
    }
}