<?
/* Consideraciones
	1.- Asegurarse que el ancho total de la cabecera sea igual al ancho total de todos los campos detalle
	2.- Si cambio el tama�o de papel debo controlar el �rea de impresi�n con 
		$this->setMaxWidth(210); 
		$this->setMaxHeight(270);

*/

/*  Cargo librerias necesarias */
include("../../library/library.php"); /* Librer?as Generales y de conexi?n del sistema */ 
include("../../library/genpdf/include/genreporte.php"); /* Librer�as para generar el reporte en PDF */ 
include("catalogosDependencias_class.php");

class Reporte extends GenReporte
{

	function SeteoPdf(){
		/* Nombre del archivo a generar */
		$this->NameFile='../../../../docs/reportes/rpt'.rand(1000,1000000).'.pdf';
		
		/* Agrego las fuentes que voy a usar en el reporte */
		$this->addFont('bold', 'Arial', 'B', 8); // Esta fuente la uso para los t�tulos de los grupos

		/* Seteo o configuro los campos que voy a usar en el reporte*/
	 	$this->SeteoCampos();

		/* Agrego los grupos que voy a tener */

		/* Establezco mi �rea de impresi�n */
		/* Para A4 */ 
		$this->setMaxWidth(210); // Por lo que ancho de A4 son 21cm=210mm
		$this->setMaxHeight(265);  // Por lo que alto de A4 son 29.70=297mm .    (La diferencia entre la altura real del papel y la altura de mi �rea de impresi�n, debe ser m�nimo 30. Por ejm. 297-265=32)
								   // Uso solo 265 porque considero mi area de impresi�n solo para el cuerpo del reporte,  Sin considerar el Head y el footer

		// Establezco mi m�rgen izquierdo para que el cuerpo del reporte apareza centrado
		$this->SetLeftMargin((($this->maxWidth-$this->WidthTotalCampos)/2));

		// Modo de visualizaci�n. (real equivale a  100%)
		$this->SetDisplayMode('real');

		// Creo la primera p�gina
		$this->Open(); 
		$this->AddPage();

	}
	
	function Cabecera(){
		// Aqu� imprimo los campos como t�tulos para el cuerpo del reporte
		$this->SetX($this->blockPosX);
		$this->Cell(10,$this->lineHeight+1,utf8_decode('Código'),1,0,'C',1);
		$this->Cell(140,$this->lineHeight+1,utf8_decode('Descripción'),1,0,'C',1);
                $this->Cell(15,$this->lineHeight+1,utf8_decode('Cta.Patrón'),1,0,'C',1);
		//$this->Cell(30,$this->lineHeight+1,'Equivalente',1,0,'C',1);
	}

	function SeteoCampos(){
		/* Defino los campos que voy a usar en el cuerpo del reporte */
		// Campos que van en en detalle, deben empezar su nombre con 'C'
		$this->addField('C1',  99999,	0,  10);
		$this->addField('C2',  99999,	0,  140);
                $this->addField('C3',  99999,	0,  15);
		//$this->addField('C3',  99999,	0,  30);
		
	}

	function Detalle(){
		global $rs;		
		
		/* Imprimo los campos */
		$this->printField($rs->field("depe_id"), 'C1','','','C');
		$this->printField($rs->field("depe_nombre"), 'C2','','','L');
                $this->printField($rs->field("depe_cuenta_patron"), 'C3','','','C');
		//$this->printField($rs->field("equivalente"), 'C3','','','L');
	}

}

/*	recibo los par�metros */
$_titulo = "RELACION DE DEPENDENCIAS" ; // T�tulo del reporte

/*	establecer conexi�n con la BD */
$conn = new db();
$conn->open();

$sql = new dependencia_SQLlista();
$sql->whereNotCero();
$sql->orderUno();
$sql = $sql->getSQL();

/* creo el recordset */
$rs = new query($conn, $sql);

if ($rs->numrows()==0){
	alert("No existen datos con los par�metros seleccionados");
}

/* Creo el objeto PDF a partir del REPORTE */
$pdf = new Reporte(); // Por defecto crea en hoja A4

/* Define el t�tulo y subt�tulo que tendr� el reporte  */ 
$pdf->setTitle($_titulo);
// $Subtitle=getDbValue("select depe_nombre from dependencia where depe_id=$nbusc_depe_id");
// $pdf->setSubTitle($Subtitle);

/* Genero el Pdf */
$pdf->GeneraPdf();

/* Cierrro la conexi�n */
$conn->close();
/* Visualizo el pdf generado*/ 
$pdf->VerPdf();
/* para eliminar la animaci�n WAIT */