<?php
include("../../library/library.php");
include("../catalogos/catalogosTipoExpediente_class.php");

/* tratamiento de campos */
$depe_id = $_GET['depe_id'];
if($depe_id>0){   
    try {
        $arHost= explode(":",DB_HOST);
        $db = new PDO("pgsql:host=$arHost[0];port=$arHost[1];dbname=".DB_DATABASE, DB_USER, DB_PASSWORD);      
        
        $tipoExpediente = new clsTipExp_SQLlista();
        $tipoExpediente->whereMPVirtual();

        $data = $db->query($tipoExpediente->getSQL_cbox2())->fetchAll( PDO::FETCH_ASSOC);
        echo json_encode($data);
        $db = null;

    } catch (PDOException $e) {
        print "¡Error!: " . $e->getMessage() . "<br/>";
        die();
    }
}