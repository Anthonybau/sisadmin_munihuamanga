<?php
include("../../library/library.php");
include("./catalogosProcedimientos_class.php");

/* tratamiento de campos */
$proc_id = $_GET['proc_id'];
if($proc_id>0){   
    try {
        $arHost= explode(":",DB_HOST);
        $db = new PDO("pgsql:host=$arHost[0];port=$arHost[1];dbname=".DB_DATABASE, DB_USER, DB_PASSWORD);      
        
        $procedimiento = new procedimientoRequisitos_SQLlista();
        $procedimiento->wherePadreID($proc_id);
        $procedimiento->orderDos();
        $data = $db->query("SELECT COALESCE(' '||text_concat(x.prre_orden::TEXT||'.- '||TRIM(x.prre_descripcion)||CHR(13)),'') AS requisitos FROM (".$procedimiento->getSQL().") AS x ")->fetchAll( PDO::FETCH_ASSOC);
        echo json_encode($data);
        $db = null;

    } catch (PDOException $e) {
        print "¡Error!: " . $e->getMessage() . "<br/>";
        die();
    }
}