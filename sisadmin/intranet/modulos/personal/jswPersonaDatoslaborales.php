<?php
include("../../library/library.php");
include("../personal/personalDatosLaborales_class.php");

/* tratamiento de campos */
$validacion = new miValidacionString();
$search = $validacion->replace_invalid_caracters(trim($_GET['q']));
$depe_id = $_GET['depe_id'];

if($search!=''){   
    try {
        $arHost= explode(":",DB_HOST);
        $db = new PDO("pgsql:host=$arHost[0];port=$arHost[1];dbname=".DB_DATABASE, DB_USER, DB_PASSWORD);      
        
        $personaDatosLaborales_buscar=new personaDatosLaborales_buscar($depe_id);
        $personaDatosLaborales_buscar->whereBuscar($search);
        $personaDatosLaborales_buscar->orderUno();
        $sql=$personaDatosLaborales_buscar->getSQL();

        $data = $db->query($sql)->fetchAll( PDO::FETCH_ASSOC);
        echo json_encode($data);
        $db = null;

    } catch (PDOException $e) {
        print "¡Error!: " . $e->getMessage() . "<br/>";
        die();
    }
}