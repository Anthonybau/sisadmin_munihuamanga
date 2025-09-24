<?php

/*  Cargo librerias necesarias */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
include("registroDespacho_class.php");
include("registroDespacho_edicionAdjuntosClass.php");
include("rptConsultarTramite.php");

/*recibo los parametros */
$depe_id=getParam("tr_depe_id");
$nbusc_depe_id  = getParam("nr_busc_depe_id");
$nbusc_depe_id=$nbusc_depe_id?$nbusc_depe_id:0;
$nbusc_user_id  = getParam("nbusc_user_id");
$nbusc_user_id=$nbusc_user_id?$nbusc_user_id:0;
$nrbusc_fdesde   = getParam("nr_busc_fdesde");
$nrbusc_fhasta   = getParam("nr_busc_fhasta");


if(!$nbusc_depe_id){
    alert('Proceso Cancelado, Seleccione Dependencia...');
}

/*	establecer conexion con la BD */
$conn = new db();
$conn->open();

$sql="      /*DOCUMENTOS EN PROCESO*/
            SELECT DISTINCT 
                   1 AS tipo,  
                   'en_proceso' AS carpeta,
                   b.desp_id,
                   c.tiex_abreviado,
                   b.desp_fecha,
                   b.desp_numero,
                   b.desp_siglas,
                   LPAD(COALESCE(b.desp_numero,'0')::TEXT,6,'0')||'-'||b.desp_anno||COALESCE('-'||b.desp_siglas,'') AS num_documento,
                   b.desp_anno,
                   b.desp_id_rand
            FROM gestdoc.despachos_derivaciones a
                 LEFT JOIN gestdoc.despachos b ON a.desp_id = b.desp_id
                 LEFT JOIN catalogos.tipo_expediente c ON b.tiex_id = c.tiex_id
            WHERE (a.dede_estado = 3 OR a.dede_estado = 7) 
                  AND a.desp_adjuntadoid IS NULL 
                  AND CASE WHEN $nbusc_depe_id>0 THEN a.depe_iddestino=$nbusc_depe_id ELSE TRUE END
                  AND CASE WHEN $nbusc_user_id>0 THEN a.usua_idrecibe=$nbusc_user_id ELSE TRUE END
                  AND a.dede_fecharecibe::DATE BETWEEN  '$nrbusc_fdesde'  AND '$nrbusc_fhasta'                  
            UNION ALL       
              /*DOCUMENTOS RECIBIDOS*/
              SELECT  DISTINCT 
                   2 AS tipo,
                   'recibidos' AS carpeta,
                   b.desp_id,
                   c.tiex_abreviado,
                   b.desp_fecha,
                   b.desp_numero,
                   b.desp_siglas,
                   LPAD(COALESCE(b.desp_numero,'0')::TEXT,6,'0')||'-'||b.desp_anno||COALESCE('-'||b.desp_siglas,'') AS num_documento,
                   b.desp_anno,
                   b.desp_id_rand
            FROM gestdoc.despachos_derivaciones a
               LEFT JOIN gestdoc.despachos b ON a.desp_id = b.desp_id
               LEFT JOIN catalogos.tipo_expediente c ON b.tiex_id = c.tiex_id
            WHERE       CASE WHEN $nbusc_depe_id>0 THEN a.depe_iddestino=$nbusc_depe_id ELSE TRUE END
                  AND   CASE WHEN $nbusc_user_id>0 THEN a.usua_idrecibe=$nbusc_user_id ELSE TRUE END
                  AND a.dede_fecharecibe::DATE BETWEEN  '$nrbusc_fdesde'  AND '$nrbusc_fhasta'
            UNION ALL      
            /*DOCUMENTOS ARCHIVADOS*/
            SELECT DISTINCT 
                   3 AS tipo,
                   'archivados' AS carpeta,
                   b.desp_id,
                   c.tiex_abreviado,
                   b.desp_fecha,
                   b.desp_numero,
                   b.desp_siglas,
                   LPAD(COALESCE(b.desp_numero,'0')::TEXT,6,'0')||'-'||b.desp_anno||COALESCE('-'||b.desp_siglas,'') AS num_documento,
                   b.desp_anno,
                   b.desp_id_rand
            FROM gestdoc.despachos_derivaciones a
                 LEFT JOIN gestdoc.despachos b ON a.desp_id = b.desp_id
                 LEFT JOIN catalogos.tipo_expediente c ON b.tiex_id = c.tiex_id
            WHERE a.dede_estado = 6 
                  AND CASE WHEN $nbusc_depe_id>0 THEN a.depe_iddestino=$nbusc_depe_id ELSE TRUE END
                  AND CASE WHEN $nbusc_user_id>0 THEN a.usua_idrecibe=$nbusc_user_id ELSE TRUE END 
                  AND a.dede_fechaarchiva::DATE BETWEEN  '$nrbusc_fdesde'  AND '$nrbusc_fhasta'    
          UNION ALL
          /*DOCUMENTOS REGISTRADOS*/
            SELECT 4 AS tipo,  
                   'registrados' AS carpeta,
                   a.desp_id,
                   c.tiex_abreviado,
                   a.desp_fecha,
                   a.desp_numero,
                   a.desp_siglas,
                   LPAD(COALESCE(a.desp_numero,'0')::TEXT,6,'0')||'-'||a.desp_anno||COALESCE('-'||a.desp_siglas,'') AS num_documento,
                   a.desp_anno,
                   a.desp_id_rand
            FROM gestdoc.despachos a
                LEFT JOIN catalogos.tipo_expediente c ON a.tiex_id = c.tiex_id
            WHERE     CASE WHEN $nbusc_depe_id>0 THEN a.depe_id=$nbusc_depe_id ELSE TRUE END
                  AND CASE WHEN $nbusc_user_id>0 THEN a.usua_id=$nbusc_user_id ELSE TRUE END          
                  AND a.desp_fregistro::DATE BETWEEN  '$nrbusc_fdesde'  AND '$nrbusc_fhasta'
      ORDER BY 3,
               4,
               5,
               6
               ";

//echo $sql;

/*	creo el recordset */
$rsArchDig = new query($conn, "$sql");
if ($rsArchDig->numrows()==0){
    alert("No existen registros para procesar...");
}

$directorioDestino="../../../../docs/";
$fileZIP="archd_". strtolower(getSession('sis_username')).'_'.iif($nbusc_depe_id,'>',0,$nbusc_depe_id,0).'_'. str_replace("-", "", $nrbusc_fdesde).'_'. str_replace("-", "", $nrbusc_fhasta).".zip";
$filePathZIP = $directorioDestino.$fileZIP;
$zip = new ZipArchive;
if ($zip->open($filePathZIP, ZIPARCHIVE::CREATE|ZipArchive::OVERWRITE) === true) {
    
    while ($rsArchDig->getrow()) {
        $id=$rsArchDig->field('desp_id');
        $tipo=$rsArchDig->field('tipo');
        $carpeta=$rsArchDig->field('carpeta');
        $periodo=$rsArchDig->field('desp_anno');
        $id_rand=$rsArchDig->field('desp_id_rand');
        $tipo=$rsArchDig->field('tiex_abreviado');
        $num_documento=str_replace("/","-",$rsArchDig->field('num_documento'));

        $directorioOrigen="../../../../docs/gestdoc/".SIS_EMPRESA_RUC."/$periodo/".$id."/";
        $file = "df_".$id_rand.".pdf";
        $filePath = $directorioOrigen.$file;

        $fileNvo = $tipo.'_'.$num_documento." (".$id.").pdf";
        
        $directorioDestino=$periodo."/".$carpeta."/";
        $filePathDestino=$directorioDestino.$fileNvo;

        if(file_exists($filePath) && $id_rand){

            $zip->addFile($filePath, $filePathDestino);
            
        }
        
        $sql=new despachoAdjuntados_SQLlista();
        $sql->wherePadreID($id);
        $sql = $sql->getSQL();

        $rsFiles = new query($conn, $sql);
        if($rsFiles->numrows()>0){

            while ($rsFiles->getrow()) {
                    $file=$rsFiles->field("area_adjunto");                                        
                    $filePath=PUBLICUPLOAD."gestdoc/".SIS_EMPRESA_RUC."/$periodo/$id/$file";

                    $fileNvo = $tipo.'_'.$num_documento." (".$id.")_anex_".$file;
                    $fileNvo = str_replace('df_'.$id.'_', "", $fileNvo);

                    $filePathDestino=$directorioDestino.$fileNvo;
                    
                    if(file_exists($filePath) && $file){
                        $zip->addFile($filePath, $filePathDestino);
                    }
            }
        }        
        
        $rand=rand(1000,1000000);
        $filePath=PUBLICUPLOAD.'/reportes/rpt'.$rand.'.pdf';
        
        
        $_titulo = "CONSULTA DE TRAMITE DE DOCUMENTO : $id"; 
        $constultaTime="Consulta Realizada el ".date('d/m/Y')." a las ".date('H:i:s');
        $i=1;

        $sql=new despachoDerivacion_SQLlista(2);
        $sql->wherePadreID($id);
        //$sql->whereTDespacho(142);
        $sql->orderUno();
        $sql=$sql->getSQL();
        //echo $sql;
        //exit(0);
        /*	creo el recordset */
        $rs = new query($conn, "SET CLIENT_ENCODING=LATIN1;$sql");

        if($rs->numrows()>0){


            /* Creo el objeto PDF a partir del REPORTE */
            $pdf = new Reporte(); 
            $pdf->setTitle($_titulo);
            $pdf->setSubTitle($constultaTime);

            /* Genero el Pdf */
            $pdf->GeneraPdf();        
            $pdf->Output($filePath,"F");

            $fileNvo = $tipo.'_'.$num_documento." (".$id.")_seg.pdf";
            $filePathDestino=$directorioDestino.$fileNvo;
            
            if(file_exists($filePath)){
                $zip->addFile($filePath, $filePathDestino);  
            }
        }
        
    }
    
             
    $zip->close();
    
} else {
   alert("No Se pudó crear archivo Digital...");    
}

$conn->close();

echo "<script>location.href='descargarArchivoDigitalDescarga.php?zip_file=".$filePathZIP."&zip_name=".$fileZIP."';</script>";

//header('Location: descargarArchivoDigitalDescarga.php?zip_file='.$filePathZIP.'&zip_name='.$fileZIP);
exit();