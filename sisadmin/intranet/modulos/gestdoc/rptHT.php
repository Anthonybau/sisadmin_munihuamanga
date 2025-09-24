<?php
/* Consideraciones
	1.- Asegurarse que el ancho total de la cabecera sea igual al ancho total de todos los campos detalle
	2.- Si cambio el tama�o de papel debo controlar el �rea de impresi�n con 
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
		global $longitudHoja;
		/* Nombre del archivo a generar */
		$this->NameFile='../../../../docs/reportes/rpt'.rand(1000,1000000).'.pdf';
		
		/* Agrego las fuentes que voy a usar en el reporte */
		$this->addFont('bold', 'Arial', '', 8); // Esta fuente la uso para los títulos de los grupos
		$this->addFont('header', 'Arial', 'B', 12); // Esta fuente la uso para los títulos de los grupos
		$this->addFont('number', 'Arial', '', 12); // Esta fuente la uso para los títulos de los grupos		
		$this->addFont('hecho', 'Arial', '', 6); // Esta fuente la uso para los títulos de los grupos
		$this->addFont('detalle', 'Arial', '', 8); // Esta fuente la uso para los títulos de los grupos
		$this->nlnCabecera=0; //numero de lineas que dejara despues de imprimir el sub titulo
		
		/* Seteo o configuro los campos que voy a usar en el reporte*/
		$this->SeteoCampos();

		/* Establezco mi área de impresión */
		/* Para A4 */ 
		$this->setMaxWidth(105); // Por lo que ancho de A4 son 21cm=210mm
		$this->setMaxHeight($longitudHoja);  // Por lo que alto de A4 son 29.70=297mm .    (La diferencia entre la altura real del papel y la altura de mi área de impresión, debe ser mínimo 30. Por ejm. 297-265=32)
								   // Uso solo 265 porque considero mi area de impresión solo para el cuerpo del reporte,  Sin considerar el Head y el footer

		// Establezco mi márgen izquierdo para que el cuerpo del reporte apareza centrado
		$this->SetLeftMargin((($this->maxWidth-$this->WidthTotalCampos)/2));

		// Modo de visualización. (real equivale a  100%)
		$this->SetDisplayMode('real');

		// Creo la primera página
		$this->Open(); 
		$this->AddPage();
	}

	function Header()
	{
                global $rs;
                
		$this->blockPosX = $this->GetX();
                $this->SetXY(5,9);
                $image = "../../img/logo_".strtolower(SIS_EMPRESA_SIGLAS).".jpg";
                if(file_exists($image)){
                    $this->Image($image, 70,$this->GetY(), 28 , 20,'JPG', '');
                    $this->Ln($this->nlnCabecera);	
                    $this->PosYIniciaTitulo=27;
               }
                
		$this->SetFontHeadFooter(); // Seteo el font para head y footer

                $this->SetFillColor(230,230,230);
                $this->SetTextColor(0,0,0);
                
                $this->SetXY(5,3);
                

                $this->SetXY(5,5);
                $this->SetFont('Arial', '', 9);
                $this->Cell(100,4,utf8_decode(SIS_EMPRESA),0,0,'C');

                $this->SetXY(5,18);
                $this->SetFont('Arial', 'B', 8);
		$this->Cell(28,4,utf8_decode('N° DE EXPEDIENTE: '),0,0,'L');
                $this->Cell(20,4,$rs->field('desp_expediente'),0,1,'L');
                
                // Imprimo el t�tulo
		$this->title(); 
                $this->Ln(2);

//		$this->SetFont('Arial', 'B', 8);
		$this->Cabecera(); // Imprimo la cabecera (los t�tulos de los campos)

		$this->nyIniciaDetalle = $this->GetY()+$this->lasth; // Guardo la posici�n "Y" donde empiezo a imprimir el detalle
		
		// Save the Y offset.  This is where the first block following the header will appear.
		$this->maxYoff = $this->GetY();
		$this->_resetFontDef();
	}

	function Footer()
	{
	}

	function Cabecera(){
		global $rs;
		
		// Aquí imprimo los campos como títulos para el cuerpo del discurso
		$this->SetX($this->blockPosX);
                $this->SetFont('Arial', 'B', 9);
//		$this->Cell(28,4,utf8_decode('N° DE EXPEDIENTE: '),0,0,'L');
//                $this->SetFont('Arial', '', 8);
//                $this->Cell(20,4,$rsPadre->field('desp_expediente'),0,1,'L');
//                $this->SetFont('Arial', 'B', 8);
//                $this->Cell(14,4," FECHA: ",0,0,'L');
//                $this->SetFont('Arial', '', 8);
//                $this->Cell(18,4,dtos($rsPadre->field('desp_fecha')),0,1,'L');
//                $this->SetFont('Arial', 'B', 8);
//                $this->Cell(14,4," FOLIOS: ",0,0,'L');
//                $this->SetFont('Arial', '', 8);
//                $this->Cell(20,4,$rsPadre->field('desp_folios'),0,1,'L');
//                $this->SetFont('Arial', 'B', 8);
//		$this->Cell(20,4,'REMITENTE: ',0,0,'L');
//                $this->SetFont('Arial', '', 8);
//                $this->Cell(105,4,$rsPadre->field("desp_firma"),0,1,'L'); 
//                $this->SetFont('Arial', 'B', 8);
//		$this->Cell(20,4,'DOCUMENTO: ',0,0,'L'); 
//                $this->SetFont('Arial', '', 8);
//                $this->Cell(20,4,$rsPadre->field("tiex_abreviado").' '.$rsPadre->field("num_documento"),0,1,'L'); 
//                $this->SetFont('Arial', 'B', 8);
//		$this->Cell(16,4,'ASUNTO:',0,0,'L');
//                $this->SetFont('Arial', '', 8);
//                $this->Cell(105,4,$rsPadre->field("desp_asunto"),0,1,'L');
                
                //$this->Cell(100,$this->lineHeight+1,'DEL REMITENTE','TLR',1,'C',1);
                
                //$this->Cell(18,$this->lineHeight+1,'De',1,0,'C',1);
		$this->Cell(42,$this->lineHeight+1,'Pase A',1,0,'C',1);
		$this->Cell(22,$this->lineHeight+1,'Fecha',1,0,'C',1);		                
		$this->Cell(12,$this->lineHeight+1,'Folios',1,0,'C',1);
		$this->Cell(23,$this->lineHeight+1,'Firma',1,0,'C',1);
                $this->lineHeight=$this->lineHeight+3;
	}
	

	function SeteoCampos(){
		/* Defino los campos que voy a usar en el cuerpo del reporte */
		// Campos que van en en detalle, deben empezar su nombre con 'C': con esta letra calcula el ancho de los datos
		//			 NOMBRE/POS_COL/POS_FIL/ANCHO
		$this->addField('C1',  99999,  0,	42);
                $this->addField('C2',  99999,  0,	22);
		$this->addField('C3',  99999,  0,	12);
                $this->addField('C4',  99999,  0,	23);


	}

	function Detalle(){
		global $rs;
		/* Imprimo campos */		
		//$this->printField($rs->field("depe_nombrecorto_origen"), 'C1','detalle',1,'L');
		$this->printField($rs->field("depe_nombrecorto_destino"), 'C1','detalle',1,'L');
                $this->printField(substr(dtos($rs->field("dede_fregistro")),0,19),'C2','hecho',1,'C');		                
		$this->printField($rs->field("desp_folios"),'C3','detalle',1,'C');		
		//$this->printField($rs->field("dede_proveido"), 'C4','detalle',1,'L',true);
		$this->printField("", 'C4','detalle',1,'R');
	}
        
	function Summary(){

		$this->SetX(10);
                $this->beginBlock(); 		
                $this->_resetFontDef();
                $this->SetLineWidth(0.1);
                for($x=0;$x<10;$x++){
                    $this->printField('', 'C1','','1','C');
                    $this->printField('', 'C2','','1','C');
                    $this->printField('', 'C3','','1','C');
                    $this->printField('', 'C4','','1','C');
                    $this->beginBlock();
                }
                
		// Imprimo la fecha
		//$this->Ln(1);
		$this->SetX(($this->maxWidth-50));
                $this->SetFont('Arial', '', 6);
		$this->Cell(25,3,utf8_decode('Fecha - Hora impresiòn:'),0,0,'R');
		$this->Cell(25,3,date("d/m/Y") . ' - ' . date("H:i:s"),0,1,'L');		
	}		
	
}

/*	establecer conexi�n con la BD */
$conn = new db();
$conn->open();

/*	recibo los parametros */
$id = getParam("id");

/*obtengo los datos del registro padre*/
//$sql=new despacho_SQLlista();
//$sql->whereID($id);
//$sql->orderUno();
//$sql=$sql->getSQL();
//$rsPadre = new query($conn, "$sql");
//$rsPadre->getrow();

$sql=new despachoDerivacion_SQLlista();
$sql->wherePadreID($id);
$sql->orderUno();
$sql=$sql->getSQL();
/*	creo el recordset */
$rs = new query($conn, "$sql");

if ($rs->numrows()==0){
    alert("No existen datos para procesar");
}

$rs->getrow();
$rs->skiprow(0);

$longitudHoja = 148;  /* Para 9 Item */
$numeroItems = $rs->numrows();
 
if($numeroItems>9){
    $longitudHoja = $numeroItems * 20;  /* Para 1 Item */        
}
 
 
/* Creo el objeto PDF a partir del REPORTE */
$pdf = new Reporte('P','mm',array(105,$longitudHoja)); // A5 HORIZONTAL4
$pdf->setTitle("HOJA DE TRAMITE");

/* Genero el Pdf */
$pdf->GeneraPdf();
$pdf->VerPdf();

/* Cierrro la conexi�n */
$conn->close();