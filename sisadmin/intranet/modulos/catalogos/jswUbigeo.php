<?php
include("../../library/library.php");
include("./catalogosUbigeo_class.php");

/* tratamiento de campos */
$validacion = new miValidacionString();
$ubig_id = $validacion->replace_invalid_caracters(trim($_GET['ubig_id']));

if($ubig_id!=''){   
    try {
        $arHost= explode(":",DB_HOST);
        $db = new PDO("pgsql:host=$arHost[0];port=$arHost[1];dbname=".DB_DATABASE, DB_USER, DB_PASSWORD);      
        
        $ubigeo=new ubigeo_SQLlista();
        $ubigeo->whereUbigID($ubig_id);
        $ubigeo->orderUno();
        $sql=$ubigeo->getSQL_cbox();

        $data = $db->query($sql)->fetchAll( PDO::FETCH_ASSOC);
        echo json_encode($data);
        $db = null;

    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br/>";
        die();
    }
}