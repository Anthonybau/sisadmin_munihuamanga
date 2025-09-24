<?php
include($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");
include($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/PDFMerger/setasign/fpdi/src/autoload.php");
include($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/PDFMerger/setasign/fpdf/fpdf.php");
require_once('registroDespacho_class.php');

use setasign\Fpdi\Fpdi;
// or for usage with TCPDF:
// use setasign\Fpdi\Tcpdf\Fpdi;

// or for usage with tFPDF:
// use setasign\Fpdi\Tfpdf\Fpdi;


$conn = new db();
$conn->open();

$id = getParam("id");
$defi_id = getParam("id_firma");


$xFirmar=NEW despachoFirmas_SQLlista();
$xFirmar->whereID($defi_id);
$sql=$xFirmar->getSQL();
$rsxFirmar = new query($conn, $sql);
$rsxFirmar->getrow();
 
if ($rsxFirmar->numrows() == 0) {
    $mensaje="No existen registros para procesar...!";

    $jsondata["success"] = false;
    $jsondata["mensaje"] = $mensaje;
}else{

    $posicion=$rsxFirmar->field('defi_posicion');
    $firma_tipo=$rsxFirmar->field('defi_tipo'); //1->FIRMA, 2->VISTO DE JEFE, 3->VISTO DE EMPLEADO
    $empleado=$rsxFirmar->field('empleado');
    $job=$rsxFirmar->field('cargo');
    $dni=$rsxFirmar->field('pers_dni');    
    $fecha_hora=$rsxFirmar->field('fecha_hora');

    if($firma_tipo==2){
        $format="VB electrónica de:";
        $razon="Apruebo el documento";
        $pos_x=-10;
        $pos_y=40+(12*($posicion-1))+iif($posicion,'>',1,(($posicion-1)*2),0);
    }else{        
        $format="Firmado electrónicamente por:";
        $razon="Soy el autor del documento";
        $pos_x=150;
        $pos_y=7+(12*($posicion-1))+iif($posicion,'>',1,(($posicion-1)*2),0);

    }
    $height=4;
        
    $sql = "SELECT  a.tabl_tipodespacho,
                    a.desp_anno AS periodo,
                    a.desp_id_rand
            FROM gestdoc.despachos a
                 WHERE a.desp_id = $id ";

    /* creo el recordset */
    $rsDoc = new query($conn, $sql);
    $rsDoc->getrow();

    $tabl_tipodespacho=$rsDoc->field('tabl_tipodespacho');
    $id_rand=$rsDoc->field('desp_id_rand');
    $periodo = $rsDoc->field('periodo');

    $nameFile=$_SERVER['DOCUMENT_ROOT'] ."/docs/gestdoc/".SIS_EMPRESA_RUC."/$periodo/$id/df_".$id_rand.".pdf";


    $pdf = new Fpdi();

    // set the source file
    $pageCount=$pdf->setSourceFile($nameFile);

    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {

        // import page 1
        $tplId = $pdf->importPage($pageNo);
        // use the imported page and place it at point 10,10 with a width of 100 mm

        // add a page
        $pdf->AddPage();
        $pdf->useTemplate($tplId);

        if ($pageNo == 1){
            // Set font
            $pdf->SetFont('Arial','',6);
            // Move to 8 cm to the right
            $pdf->SetY($pos_y);
            
            $pdf->Cell($pos_x);
            $pdf->Cell(40,2,utf8_decode($format),'TLR',1,'L');
            $pdf->Cell($pos_x);
            $pdf->Cell(40,2,utf8_decode(substr($empleado,0,30)),'LR',1,'L');
            $pdf->Cell($pos_x);
            $pdf->Cell(40,2,iif(trim(substr($empleado,30,18)),'!=','',trim(substr($empleado,30,18)).'-','').$dni,'LR',1,'L');
            $pdf->Cell($pos_x);
            $pdf->Cell(40,2,$job,'LR',1,'L');
            $pdf->Cell($pos_x);
            $pdf->Cell(40,2,'Motivo:'.$razon,'LR',1,'L');            
            $pdf->Cell($pos_x);
            $pdf->Cell(40,2,'Fecha:'.dtos($fecha_hora,"-"),'BLR',1,'L');
        }
        
        
        $jsondata["success"] = true;
        $jsondata["mensaje"] = "";


    }

    $pdf->Output($nameFile,"F"); 
        
    
}        


$conn->close();


header('Content-Type: application/json');
echo json_encode($jsondata, JSON_FORCE_OBJECT);
