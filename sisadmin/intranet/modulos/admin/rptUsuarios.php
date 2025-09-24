<?php
/* Consideraciones
	1.- Asegurarse que el ancho total de la cabecera sea igual al ancho total de todos los campos detalle
	2.- Si cambio el tama?o de papel debo controlar el ?rea de impresi?n con 
		$this->setMaxWidth(210); 
		$this->setMaxHeight(270);

*/

/*  Cargo librerias necesarias */
include("../../library/library.php"); /* Librer�as Generales y de conexi�n del sistema */ 
define('FPDF_FONTPATH','../../library/genpdf/include/font/');
include("../../library/genpdf/include/genreporte.php"); /* Librer�as para generar el reporte en PDF */ 
include("adminUsuario_class.php");

class Reporte extends GenReporte
{
// 	var $nDias;   // Ejemplo de una variable de grupo
        var $subTitle2;
        
	function SeteoPdf(){
                $this->NameFile='../../../../docs/reportes/rpt'.rand(1000,1000000).'.pdf';                            
            
		/* Agrego las fuentes que voy a usar en el reporte */
		$this->addFont('bold', 'Arial', 'B', 7); // Esta fuente la uso para los t?tulos de los grupos
                $this->addFont('detalles', 'Arial', '', 7);
                $this->addFont('categoria', 'Arial', 'B', 9);
                $this->addFont('textocabecera', 'Arial', 'B', 7); // Esta fuente la uso para los t?tulos de los grupos
                
		/* Seteo o configuro los campos que voy a usar en el reporte*/
		$this->SeteoCampos();

		$this->CampoGrupo1='depe_superior_id'; // Voy a tener el Grupo 1 agrupado por el campo grupo_nivel                
		$this->PosYIniciaTitulo=10; // Posici?n de Y en que debe empezar a imprimir el t?tulo
		$this->lineHeight=4; // Altura de cada celda

				
		/* Establezco mi ?rea de impresi?n */
		/* Para A4 */ 
		$this->setMaxWidth(297); // Por lo que ancho de A4 son 21cm=210mm
		$this->setMaxHeight(180);  // Por lo que alto de A4 son 29.70=297mm .    (La diferencia entre la altura real del papel y la altura de mi ?rea de impresi?n, debe ser m?nimo 30. Por ejm. 297-265=32)
								   // Uso solo 265 porque considero mi area de impresi?n solo para el cuerpo del reporte,  Sin considerar el Head y el footer

		// Establezco mi m?rgen izquierdo para que el cuerpo del reporte apareza centrado
		$this->SetLeftMargin((($this->maxWidth-$this->WidthTotalCampos)/2));

		// Modo de visualizaci?n. (real equivale a  100%)
		$this->SetDisplayMode('real');

		// Creo la primera p?gina
		$this->Open(); 
		$this->AddPage();
	}
	function Cabecera(){
		// Aquí imprimo los campos como títulos para el cuerpo del discurso
		$this->SetX($this->blockPosX);
                $this->Cell(8,$this->lineHeight+1,'Ord',1,0,'C',1);
		$this->Cell(15,$this->lineHeight+1,'Login',1,0,'C',1);
		$this->Cell(70,$this->lineHeight+1,'Empleado',1,0,'C',1);
		$this->Cell(60,$this->lineHeight+1,'Dependencoa',1,0,'C',1);		
                $this->Cell(60,$this->lineHeight+1,'Cargo Funcional',1,0,'C',1);		
                $this->Cell(30,$this->lineHeight+1,'Estado',1,0,'C',1);
                
	}

	function SeteoCampos(){
		/* Defino los campos que voy a usar en el cuerpo del reporte */
		// Campos que van en en detalle, deben empezar su nombre con 'C'
                $this->addField('C1',  99999,	0,  8);            
		$this->addField('C2',  99999,	0,  15);
		$this->addField('C3',  99999,	0,  70);
		$this->addField('C4',  99999,	0,  60);
                $this->addField('C5',  99999,	0,  60);
                $this->addField('C6',  99999,	0,  30);

		$this->addField('HG1',  0,	0,	140);
		
		$this->addField('S1',   0,	0,	40);
		$this->addField('S2',  40,	0,	10);
		
	}

	function TituloGrupo1(){
            global $rs,$j;
            $this->beginBlock(); 		
            $this->printField("DEPENDENCIA SUPERIOR: ".$rs->field("superior"), 'HG1','categoria',0,'L');
	}
        

	function Detalle(){
		global $rs,$j;
		/* Imprimo los campos */
                $this->printField($j, 'C1','detalles',0,'C');                
                $this->printField($rs->field("usua_login"), 'C2','',0,'L');
		$this->printField($rs->field("empleado"), 'C3','',0,'L');
                $this->printField($rs->field("dependencia"), 'C4','',0,'L');
                
                $this->printField($rs->field("cargo_funcional"), 'C5','',0,'L');
                $this->printField($rs->field("estado"), 'C6','',0,'L');
                $j=$j+1;	
	}

        
	function PieGrupo1(){
		/* Summary del Reporte*/
		$this->Line($this->blockPosX, $this->blockPosY+$this->lasth,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+$this->lasth); // Imprimo Línea al final de cada grupo		
		$this->beginBlock();	
		$this->printField('Total Trabajadores:  '.number_format($this->functions[CONT_GRUPO1][C1],0,'.',','), 'HG1','bold',0,'L');				
		$this->printField($this->nTotal.'         ', 'S2','bold',0,'R');
	}

}

/*	recibo los parámetros */
$_titulo = getParam("_titulo"); 
$sub_dependencia  = getParam("tr_sub_dependencia"); 

/*	establecer conexión con la BD */
$conn = new db();
$conn->open();
$j=1;



/* expresion SQL que define el reporte */
$personal=new clsUsers_SQLlista();
if($sub_dependencia){
    $personal->whereDepeID($sub_dependencia);
}

$sql="SELECT a.usua_login,
             a.empleado,
             a.depe_nombre AS dependencia,
             a.depe_superior_id,
             a.depe_superior_nombre AS superior,
             a.pdla_cargofuncional AS cargo_funcional,
             a.estado_activo AS estado
      FROM (".$personal->getSQL().") AS a 
            ORDER BY depe_superior_id,
                     empleado,
                     pers_dni";


//echo $sql; 
/*	creo el recordset */
$rs = new query($conn, "SET CLIENT_ENCODING=LATIN1;$sql");

if ($rs->numrows()==0){
	alert("No existen datos con los parámetros seleccionados");
}

/* Para cuando se elige el Destino "Hoja de Calculo" */
if(getParam("destino")==2){
    include("../../library/exportaraHojaCalculo.php");
}
/* Creo el objeto PDF a partir del REPORTE */
$pdf = new Reporte('L'); // Por defecto crea en hoja A4


/* Define el título y subtítulo que tendrá el reporte  */ 
$pdf->setTitle($_titulo);
/* Genero el Pdf */
$pdf->GeneraPdf();

/* Cierrro la conexión */
$conn->close();
/* Visualizo el pdf generado*/ 
$pdf->VerPdf();