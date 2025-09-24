<?php

include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
include("PersonaContrato_class.php");
include("../admin/datosEmpresa_class.php"); 

require_once('../../library/tcpdf/tcpdf.php');

class MYPDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        $image_file = '../../img/header_'.strtolower(SIS_EMPRESA_SIGLAS).'.jpg';
        if(file_exists($image_file)){
            $this->Image($image_file, 10, 5, 175, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }else{
            $this->Ln(20);
        }            
//        // Set font
//        $this->SetFont('helvetica', 'B', 20);
//        // Title
//        $this->Cell(0, 15, '<< TCPDF Example 003 >>', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {

        $image_file = "../../img/footer_".strtolower(SIS_EMPRESA_SIGLAS).".jpg";
        if(file_exists($image_file)){
            $this->Image($image_file, 10, 275, 185, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }else{
            $this->Ln(20);
        }    
        $this->SetY(-10);
        $this->SetFont('helvetica', 'N', 6);
        // Page number
        $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
    


}


$conn = new db();
$conn->open();

$id = getParam("id");

/* obtengo los datos del documento */
$persona_contrato= new clsPersonaContrato_SQLlista();
$persona_contrato->whereID($id);

$sql = $persona_contrato->getSQL();

/* creo el recordset */
$rsDoc = new query($conn, $sql);

if ($rsDoc->numrows() == 0) {
    alert("No existen registros para procesar...!");
}
$rsDoc->getrow();



/* Imprimo Documento */
//define ('K_PATH_IMAGES', '../../img/');
// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);


// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT+5, PDF_MARGIN_TOP+5, PDF_MARGIN_RIGHT);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// add a page
$pdf->AddPage();

// set font
$pdf->SetFont('helvetica', '', 10);

// create some HTML content
$empresa = new datosEmpresa(1,'');
$empresa->setDatos();

$contenido=$rsDoc->field('peco_contenido');

//$contenido=str_replace('{numero}',$rsDoc->field('numero_contrato_corto'),$contenido);
//$contenido=str_replace('{empleado}',$rsDoc->field('empleado'),$contenido);
//$contenido=str_replace('{dni_empleado}',$rsDoc->field('dni'),$contenido);
//$contenido=str_replace('{direccion_empleado}', $rsDoc->field('pers_direccion'),$contenido);
//$contenido=str_replace('{cargo}', $rsDoc->field('cargo_clasificado'),$contenido);
//$contenido=str_replace('{funciones}', str_replace(chr(13),'<br>',$rsDoc->field('peco_funciones')),$contenido);
//
//$contenido=str_replace('{titular}', $empresa->field('empr_titular'),$contenido);
//$contenido=str_replace('{dni_titular}', $empresa->field('empr_titular_dni'),$contenido);
//$contenido=str_replace('{direccion_empresa}', $rsDoc->field('empr_direccion'),$contenido);
//
//$contenido=str_replace('{periodo}', dtos($rsDoc->field('peco_finicio')).' AL '.dtos($rsDoc->field('peco_ftermino')),$contenido);
//$contenido=str_replace('{anno}', $rsDoc->field('peco_periodo'),$contenido);
//$contenido=str_replace('{remuneracion}', $rsDoc->field('peco_monto'),$contenido);
//
//$fecha=explode("-",$rsDoc->field('peco_fcontrato'));
//$contenido=str_replace('{fecha}', $fecha[2].' días del mes de '.  strtolower(list_mes($fecha[1])).' del '.$fecha[0],$contenido);


//echo $contenido;

$html = $contenido;

// output the HTML content
$pdf->writeHTML($html, true, 0, true, true);


// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//$nameFile = "../../../../D/$id.pdf";
$nameFile=$_SERVER['DOCUMENT_ROOT'] .'/docs/reportes/rpt'.rand(1000,1000000).'.pdf';                            
//Close and output PDF document

$pdf->Output($nameFile, 'FI');  /* genera y veo el archivo en el navegador */


/* Fin: Imprimo Documento */

$conn->close();