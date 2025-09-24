<?php
/* Consideraciones
	1.- Asegurarse que el ancho total de la cabecera sea igual al ancho total de todos los campos detalle
	2.- Si cambio el tamaño de papel debo controlar el �rea de impresi�n con
		$this->setMaxWidth(210); 
		$this->setMaxHeight(270);

*/

/*  Cargo librerias necesarias */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
define('FPDF_FONTPATH','../../library/genpdf/include/font/');
include("../../library/genpdf/include/genreporte.php"); /* Librer�as para generar el reporte en PDF */
include("registroDespacho_class.php");

class Reporte extends GenReporte
{
	function SeteoPdf(){
                /* Nombre del archivo a generar */
		$this->NameFile='../../../../docs/reportes/rpt'.rand(1000,1000000).'.pdf';

		/* Agrego las fuentes que voy a usar en el reporte */
		$this->addFont('footer', 'Arial', 'I', 9); // Esta fuente la uso para los t�tulos de los grupos				
		$this->addFont('header', 'Arial', 'B', 13); // Esta fuente la uso para los t�tulos de los grupos		
		$this->addFont('bold', 'Arial', 'B', 7); // Esta fuente la uso para los t�tulos de los grupos
		$this->addFont('items', 'Arial', '', 7); // Esta fuente la uso para los t�tulos de los grupos

		/* Seteo o configuro los campos que voy a usar en el reporte*/
		$this->SeteoCampos();

		$this->nlnCabecera=2; // Para no dejar l�neas en blanco despu�s de imprimir el Head para pasar a la cabecera

		/* Establezco mi área de impresión */
		/* Para A4 */ 
		$this->setMaxWidth(297); // Por lo que ancho de A4 son 21cm=210mm
		$this->setMaxHeight(180);  // Por lo que alto de A4 son 29.70=297mm .    (La diferencia entre la altura real del papel y la altura de mi �rea de impresi�n, debe ser m�nimo 30. Por ejm. 297-265=32)
								   // Uso solo 265 porque considero mi area de impresi�n solo para el cuerpo del reporte,  Sin considerar el Head y el footer

		// Establezco mi margen izquierdo para que el cuerpo del reporte apareza centrado
		$this->SetLeftMargin((($this->maxWidth-$this->WidthTotalCampos)/2));

		// Modo de visualización. (real equivale a  100%)
		$this->SetDisplayMode('real');

		// Creo la primera página
		$this->Open(); 
		$this->AddPage();

	}

	function Cabecera(){
		$this->Cell(15,$this->lineHeight+1,'FECHA',1,0,'C',1);
                $this->Cell(15,$this->lineHeight+1,NAME_EXPEDIENTE_UPPER,1,0,'C',1);
		$this->Cell(25,$this->lineHeight+1,'EXP.LEGAL',1,0,'C',1);
		$this->Cell(50,$this->lineHeight+1,'DEMANDANTE',1,0,'C',1);
                $this->Cell(50,$this->lineHeight+1,'DEMANDADO',1,0,'C',1);
                $this->Cell(60,$this->lineHeight+1,'MATERIA',1,0,'C',1);
		$this->Cell(10,$this->lineHeight+1,'FOL',1,0,'C',1);
                $this->Cell(30,$this->lineHeight+1,'No RESOLUCION',1,0,'C',1);
		$this->Cell(25,$this->lineHeight+1,'RECIBIDO POR',1,0,'C',1);

	}

	function SeteoCampos(){

		/* Defino los campos que voy a usar en el cuerpo del reporte */
		// Campos que van en en detalle, deben empezar su nombre con 'C' o 'N'
		$this->addField('C1', 	99999,    0,	15);
                $this->addField('C2', 	99999,    0,	15);
		$this->addField('C3',   99999,	  0,	25);
                
                $this->addField('C4', 	99999,    0,	50);
		$this->addField('C5', 	99999,    0,	50);
		$this->addField('C6', 	99999,    0,	60);

                $this->addField('C7',   99999,	  0,	10);
                $this->addField('C8',   99999,	  0,	30);
                $this->addField('C9',   99999,	  0,	25);
                
                $this->addField('HG1',  0,	0,	125);
		$this->addField('HG2',  0,	0,	40);
		$this->addField('HG3',  40,	0,	20);
	}


	function Detalle(){
		global $rs;
                $this->printField(dtos($rs->field("desp_fecha")),  'C1','items','','L');
		$this->printField($rs->field("id_padre"),  'C2','items','','L');
		$this->printField($rs->field("desp_exp_legal"),  'C3','items','','L');
                $this->printField(utf8_decode($rs->field("desp_demandante")),  'C4','items','','L');
                $this->printField(utf8_decode($rs->field("desp_demandado")),  'C5','items','','L');
                $this->printField(utf8_decode($rs->field("desp_asunto")),  'C6','items','','L');
                $this->printField($rs->field("desp_folios"),  'C7','items','','C');
                $this->printField(utf8_decode($rs->field("desp_resolucion")),  'C8','items','','L');
                $this->printField($rs->field("usuario_genera"),  'C9','items','','C');
                
                
	}


	function Summary(){
		/* Summary del Reporte*/
		$this->beginBlock();	
		$this->Line($this->blockPosX, $this->blockPosY,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY);
		$this->printField("TOTAL DE REGISTROS:", 'HG2','bold',0,'L');
		$this->printField(number_format($this->functions['CONT_TOTAL']['C1'], 0, '.', ','), 'HG3','bold',0,'R');
		$this->Line($this->blockPosX, $this->blockPosY+4,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+4);
		$this->Line($this->blockPosX, $this->blockPosY+4+1,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+4+1);		

	}

}

/*	recibo los parámetros */
$_titulo        = getParam("_titulo"); // Título del reporte
$nbusc_depe_id  = getParam("nbusc_depe_id");
$nbusc_user_id  = getParam("nbusc_user_id");

//$nbusc_estado   = getParam("nbusc_estado");
$nrbusc_fdesde  = getParam("nrbusc_fdesde");
$nrbusc_fhasta  = getParam("nrbusc_fhasta");

if(getSession("sis_userid")>1 && getSession("sis_level")!=3 && !$nbusc_depe_id){
    alert('Seleccione Dependencia...');
}

/*	establecer conexión con la BD */
$conn = new db();
$conn->open();

//ojo esta funcion se encuntra en 'registroDespacho_class.php'
$sql=new despachoDerivacion_SQLlista(2);
$sql->whereRegistrados();
$sql->whereDocLegal();

if($nbusc_depe_id)
    $sql->whereDepePadre($nbusc_depe_id);

if($nbusc_user_id)
    $sql->wherePadreUsuaID($nbusc_user_id);

//if($nbusc_estado)
//    $sql->whereEstado($nbusc_estado);

if($nrbusc_fdesde)
    $sql->whereFechaRegistroDesde($nrbusc_fdesde);

if($nrbusc_fhasta)
    $sql->whereFechaRegistroHasta($nrbusc_fhasta);

$sql->orderTres();
$sql=$sql->getSQL();
//echo $sql;

/*	creo el recordset */
$rs = new query($conn, "$sql");

if ($rs->numrows()==0){
	alert("No existen registros para procesar...");
}


/* Creo el objeto PDF a partir del REPORTE */
$pdf = new Reporte('L'); // Por defecto crea en hoja A4

/* Define el titulo y subtitulo que tendrá el reporte  */

$Subtitle.="DESDE: ".dtos($nrbusc_fdesde)." HASTA: ".dtos($nrbusc_fhasta);
$pdf->setTitle($_titulo);
$pdf->setSubTitle($Subtitle);

$rs->getrow();
$rs->skiprow(0);



/* Genero el Pdf */
$pdf->GeneraPdf();

/* Cierrro la conexión */
$conn->close();
/* Visualizo el pdf generado */ 
$pdf->VerPdf();
/* para eliminar la animación WAIT */