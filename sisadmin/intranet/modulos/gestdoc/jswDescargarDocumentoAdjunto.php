<?php
include("../../library/library.php");


try {
    $arHost= explode(":",DB_HOST);
    $db = new PDO("pgsql:host=$arHost[0];port=$arHost[1];dbname=".DB_DATABASE, DB_USER, DB_PASSWORD);      
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $dead_id=$_POST['id'];

    $statement = $db->prepare("SELECT   a.desp_id,
                                        a.area_adjunto,
                                        b.desp_anno
                                       FROM gestdoc.despachos_adjuntados a
                                       LEFT JOIN  gestdoc.despachos b ON  a.desp_id=b.desp_id
                                       WHERE a.dead_id = ? ");
                                                            
    $statement->execute(array($dead_id));
    $fila=$statement->fetch(PDO::FETCH_ASSOC);
                        

        if(is_array($fila)){
                
                $id=$fila['desp_id'];
                $periodo=$fila['desp_anno'];
                $name_file=$fila['area_adjunto'];
                
                $file = PUBLICUPLOAD."gestdoc/".SIS_EMPRESA_RUC."/$periodo/$id/$name_file";
                                
                if(file_exists($file)){//CERRADO

                    $nuevo_file=PUBLICUPLOAD."/reportes/$name_file";
                    $copy = copy($file, $nuevo_file);
                    
                    if ($copy) {
                                                
                            $jsondata["success"] = true;
                            $jsondata["file"] = $nuevo_file;
                            $jsondata["mensaje"] = "Proceso éxitoso.";
                                                                      
                    }else{
                        $jsondata["success"] = false;
                        $jsondata["mensaje"] = "Lo sentimos, el archivo $name_file NO se copio.";                                        
                    }
                    
                }else{
                    $jsondata["success"] = false;
                    $jsondata["mensaje"] = 'Lo sentimos, NO se halló archivo Adjunto';
                }
               


                            
    }else{
        $jsondata["success"] = false;
        $jsondata["mensaje"] = "Lo sentimos, NO se hallaron registros.";
    }
    
    

} catch (PDOException $e) {
    $jsondata["success"] = false;
    $jsondata["mensaje"] = $e->getMessage();
}    



header('Content-Type: application/json');
echo json_encode($jsondata, JSON_FORCE_OBJECT);