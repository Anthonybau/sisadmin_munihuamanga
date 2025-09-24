<?php
include("../../library/library.php");
include("../personal/personalDatosLaborales_class.php");

/* tratamiento de campos */
$validacion = new miValidacionString();
$search = $validacion->replace_invalid_caracters(trim($_GET['q']));

if($search!=''){   
    try {
        $arHost= explode(":",DB_HOST);
        $db = new PDO("pgsql:host=$arHost[0];port=$arHost[1];dbname=".DB_DATABASE, DB_USER, DB_PASSWORD);      
        
        $personasExternas_buscar=new personasExternas_buscar();
        $personasExternas_buscar->whereBuscar($search);
        $personasExternas_buscar->orderUno();
        $sql=$personasExternas_buscar->getSQL();

        $data = $db->query($sql)->fetchAll( PDO::FETCH_ASSOC);
        echo json_encode($data);
        $db = null;

    } catch (PDOException $e) {
        print "¡Error!: " . $e->getMessage() . "<br/>";
        die();
    }
}