<?php
include("../../library/library.php");
include("registroDespacho_class.php");

try {
    $arHost= explode(":",DB_HOST);
    $db = new PDO("pgsql:host=$arHost[0];port=$arHost[1];dbname=".DB_DATABASE, DB_USER, DB_PASSWORD);      
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    /*OBTENGO DATOS DEL DATATABLE*/
    //https://makitweb.com/datatables-ajax-pagination-with-search-and-sort-php/
    
    
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page    
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    //$searchValue = mysqli_real_escape_string($con,$_POST['search']['value']); // Search value
    
    /*OBTENGO DATA*/
    $validacion = new miValidacionString();
    $nbusc_tiex_id = intval($_POST['formdata']['documento_tipodocumento']);

    $dbusc_fdesde  = $_POST['formdata']['dbusc_fdesde'];
    $dbusc_fhasta  = $_POST['formdata']['dbusc_fhasta'];
    
    $nbusc_dni_ruc = $validacion->replace_invalid_caracters($_POST['formdata']['nbusc_dni_ruc']);
    $nbusc_numero  = intval($_POST['formdata']['nbusc_numero']);    
    $cadena        = $validacion->replace_invalid_caracters($_POST['formdata']['Sbusc_cadena']);
    //$nbusc_dni_ruc='16708816';

    if(strlen($cadena)>=3 || $nbusc_tiex_id>0 || (validateDate($dbusc_fdesde,'Y-m-d') || validateDate($dbusc_fhasta,'Y-m-d')) || $nbusc_dni_ruc>0 || $nbusc_numero>0){ 
        
                /*obtengo el total de registros*/
                $sql=new despachoBuscarAjax_SQLlista();
                $sql->whereTipoDespacho(142);//OTRAS ENTIDADES
                
                if($nbusc_tiex_id){
                    $sql->whereTiExpID($nbusc_tiex_id);
                }
                    
                if($dbusc_fdesde){
                    $sql->whereFechaDesde($dbusc_fdesde);
                }
                
                if($dbusc_fhasta){
                    $sql->whereFechaHasta($dbusc_fhasta);
                }
                
                if($nbusc_dni_ruc){
                    $sql->whereCodigo($nbusc_dni_ruc);
                }                
                
                if($nbusc_numero){
                    $sql->whereNumero($nbusc_numero);
                }
                
                if($cadena){
                    $sql->whereDescripVarios($cadena);
                }

                $statement = $db->prepare("SELECT COUNT(*) AS allcount FROM (".$sql->getSQL().") AS a");               
                $statement->execute();
                $total_registros=$statement->fetchColumn();
                if($total_registros>0){
                    /*obtengo registros de la pagina a mostrar*/    
                    $sql->addLimit($row,$rowperpage);
                    $sql->orderUno();                

                    $statement = $db->prepare($sql->getSQL());               
                    $statement->execute();
                    
                    //$total_filtrado=$statement->rowCount();                
                    
                    //OJO SE COLOCA ESTE VALOR PORQUE NO HAY BUSQUEDAS
                    //https://datatables.net/forums/discussion/25985/resolved-datatables-server-side-problem-to-pagination-number-of-page-not-correct
                    $total_filtrado=$total_registros;

                    //VER ESTE EJEMPLO PARA APLICAR BUSQUEDAS https://makitweb.com/datatables-ajax-pagination-with-search-and-sort-php/
                    
                    if($total_filtrado>0){
                        $jsondata["success"] = true;
                        $jsondata["draw"]=intval($draw);
                        $jsondata["recordsTotal"]=$total_registros;
                        $jsondata["recordsFiltered"]=$total_filtrado; //OJO recordsFiltered, no es la longitud de la página
                        $jsondata['data'] = $statement->fetchAll( PDO::FETCH_ASSOC);
                    }else{
                        $jsondata["success"] = false;
                        $jsondata['mensaje'] = "Lo sentimos, NO se hallaron resultados.";        
                    }
                    
                }else{
                    $jsondata["success"] = false;
                    $jsondata['mensaje'] = "Lo sentimos, NO se hallaron resultados.";        
                }                
                            
    }else{
        $jsondata["success"] = false;
        $jsondata["mensaje"] = "Lo sentimos, Los datos recibidos NO son validos.";
    }
    
    
    
} catch (PDOException $e) {
    $jsondata["success"] = false;
    $jsondata["mensaje"] = $e->getMessage();
}    


if( $jsondata["success"] == false){
    $jsondata["draw"]=1;
    $jsondata["recordsTotal"]=0;
    $jsondata["recordsFiltered"]=0;    
}

header('Content-Type: application/json');
echo json_encode($jsondata);