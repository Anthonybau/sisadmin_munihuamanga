<?php
include("../../library/library.php");
include("./sistemas_class.php");

/* tratamiento de campos */
$depe_id = $_GET['depe_id'];
if($depe_id>0){   
    try {
        $arHost= explode(":",DB_HOST);
        $db = new PDO("pgsql:host=$arHost[0];port=$arHost[1];dbname=".DB_DATABASE, DB_USER, DB_PASSWORD);      
        
        $sistemas = new clsSistemas_SQLlista();
        $sistemas->whereNOTAdmin();
        $sistemas->whereNOTMiPerfil();
        $sistemas->whereConTutorial();
        $sistemas->whereActivo();

        $data = $db->query($sistemas->getSQL_cbox())->fetchAll( PDO::FETCH_ASSOC);
        echo json_encode($data);
        $db = null;

    } catch (PDOException $e) {
        print "¡Error!: " . $e->getMessage() . "<br/>";
        die();
    }
}