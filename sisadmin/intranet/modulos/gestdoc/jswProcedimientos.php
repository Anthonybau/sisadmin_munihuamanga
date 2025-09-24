<?php
include("../../library/library.php");
include("./catalogosProcedimientos_class.php");

/* tratamiento de campos */
$depe_id = $_GET['depe_id'];
if($depe_id>0){   
    try {
        $arHost= explode(":",DB_HOST);
        $db = new PDO("pgsql:host=$arHost[0];port=$arHost[1];dbname=".DB_DATABASE, DB_USER, DB_PASSWORD);      
        
        $procedimiento = new procedimiento_SQLlista();
        $procedimiento->whereDepeID($depe_id);
        $procedimiento->whereEstado(1);
        $procedimiento->whereModoVirtual();
        $procedimiento->whereDestinatario();
        //$procedimiento->whereTipoDespacho(142); //EXTERNOS

        $data = $db->query( ' SELECT a.proc_id AS id,
                                     a.proc_nombre AS descripcion
				FROM ('.$procedimiento->getSQL().') AS a 
                                WHERE proc_id!=9999    
                                UNION ALL  
                                SELECT  proc_id AS id,
                                        proc_nombre AS descripcion
                                    FROM gestdoc.procedimiento
                                    WHERE proc_id=9999')->fetchAll( PDO::FETCH_ASSOC);
        echo json_encode($data);
        $db = null;

    } catch (PDOException $e) {
        print "¡Error!: " . $e->getMessage() . "<br/>";
        die();
    }
}