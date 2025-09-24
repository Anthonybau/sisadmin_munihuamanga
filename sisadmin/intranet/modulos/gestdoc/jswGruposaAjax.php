<?php
include("../../library/library.php");
include("gruposDerivaciones_class.php");

/* tratamiento de campos */
$validacion = new miValidacionString();
$search = $validacion->replace_invalid_caracters(trim($_GET['q']));

$id = intval($_GET['id']);

if($search!='' || $id>0){   
    try {
        $arHost= explode(":",DB_HOST);
        $db = new PDO("pgsql:host=$arHost[0];port=$arHost[1];dbname=".DB_DATABASE, DB_USER, DB_PASSWORD);      
        
        $sqlGrupos=new gruposBuscar_SQLlista();
        $sqlGrupos->whereActivo();
        if($id){
            $sqlGrupos->whereID(($id));
        }else{
            $sqlGrupos->whereBuscar($search);
        }
        $sqlGrupos->orderUno();
        $data = $db->query($sqlGrupos->getSQL())->fetchAll( PDO::FETCH_ASSOC);
        echo json_encode($data);
        $db = null;

    } catch (PDOException $e) {
        print "¡Error!: " . $e->getMessage() . "<br/>";
        die();
    }
}