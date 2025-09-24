<?php
include("../../library/library.php");


try {
    $arHost= explode(":",DB_HOST);
    $db = new PDO("pgsql:host=$arHost[0];port=$arHost[1];dbname=".DB_DATABASE, DB_USER, DB_PASSWORD);      
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $id=$_POST['id'];

    $statement = $db->prepare("SELECT   a.desp_id,
                                        a.desp_anno,
                                        a.desp_file_firmado
                                       FROM gestdoc.despachos a
                                       WHERE a.desp_id = ? ");
                                                            
    $statement->execute(array($id));
    $fila=$statement->fetch(PDO::FETCH_ASSOC);
                        

        if(is_array($fila)){
                
                
                $periodo=$fila['desp_anno'];
                $name_file=$fila['desp_file_firmado'];
                
                $file = PUBLICUPLOAD."gestdoc/".SIS_EMPRESA_RUC."/$periodo/$id/$name_file";
                                
                if(file_exists($file)){//CERRADO
                    $rand=rand(1000,1000000000);
                    $nuevo_file=PUBLICUPLOAD."/reportes/rpt$rand.pdf";
                    $copy = copy($file, $nuevo_file);
                    
                    if ($copy) {
                                                
                        try {
                            $data = array(  'desp_id'              => $id, 
                                            'usua_id'              => getSession('sis_userid')
                                        );

                            $PDO_insert=new PDO_insert('gestdoc.despachos_descargas',$data);
                            $db->prepare($PDO_insert->getSQL())->execute($PDO_insert->values());

                            $jsondata["success"] = true;
                            $jsondata["file"] = $nuevo_file;
                            $jsondata["mensaje"] = "Proceso éxitoso.";


                        } catch (PDOException $e) {
                            $jsondata["success"] = false;
                            $jsondata["mensaje"] = $e->getMessage();

                        }                        
                                                                                                
                        
                    }else{
                        $jsondata["success"] = false;
                        $jsondata["mensaje"] = "Lo sentimos, el archivo NO se copio.";                                        
                    }
                    
                }else{
                    $jsondata["success"] = false;
                    $jsondata["mensaje"] = 'Lo sentimos, NO se halló Documento';
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