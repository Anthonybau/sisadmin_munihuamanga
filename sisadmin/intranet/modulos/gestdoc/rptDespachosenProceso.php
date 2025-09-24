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

		$this->CampoGrupo1='depe_iddestino'; // Voy a tener el Grupo 1 agrupado por el campo repe_descripcion
		$this->CampoGrupo2='usua_idrecibe'; // Voy a tener el Grupo 1 agrupado por el campo repe_descripcion

		
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
		$this->Cell(15,$this->lineHeight+1,NAME_EXPEDIENTE_UPPER,1,0,'C',1);
                $this->Cell(24,$this->lineHeight+1,'RECIBIDO',1,0,'C',1);
		$this->Cell(40,$this->lineHeight+1,'DOCUMENTO',1,0,'C',1);
		$this->Cell(15,$this->lineHeight+1,'FECHA',1,0,'C',1);
		$this->Cell(10,$this->lineHeight+1,'FOL',1,0,'C',1);
		$this->Cell(30,$this->lineHeight+1,'PROCEDENCIA',1,0,'C',1);
		$this->Cell(50,$this->lineHeight+1,'FIRMA EL DOCUMENTO',1,0,'C',1);
                $this->Cell(40,$this->lineHeight+1,'CARGO',1,0,'C',1);
                $this->Cell(60,$this->lineHeight+1,'ASUNTO',1,0,'C',1);
	}

	function SeteoCampos(){

		/* Defino los campos que voy a usar en el cuerpo del reporte */
		// Campos que van en en detalle, deben empezar su nombre con 'C' o 'N'
		$this->addField('C1', 	99999,    0,	15);
                $this->addField('C2', 	99999,    0,	24);
		$this->addField('C3', 	99999,    0,	40);
		$this->addField('C4', 	99999,    0,	15);
		$this->addField('C5', 	99999,    0,	10);
		$this->addField('C6',   99999,	  0,	30);
                $this->addField('C7',   99999,	  0,	50);
                $this->addField('C8',   99999,	  0,	40);
                $this->addField('C9',   99999,	  0,	60);
                
                $this->addField('HG1',  0,	0,	125);
		$this->addField('HG2',  0,	0,	40);
		$this->addField('HG3',  40,	0,	20);

	}

	function TituloGrupo1(){
		global $rs;
		$this->beginBlock();
		$this->printField("DEPENDENCIA (UBICACION) : ".$rs->field("depe_nombre_destino").' / '.$rs->field("depe_superior_nombre_destino"), 'HG1','bold','T','L');
	}

	function TituloGrupo2(){
		global $rs;
                $usuario_nombre_completo=GetDbValue("SELECT pers_nombres||' '||pers_apelLpaterno||' '||pers_apeLlmaterno
                                                     FROM persona WHERE pers_id IN 
                                                     (SELECT pers_id FROM persona_datos_laborales WHERE pdla_id IN
                                                     (SELECT pdla_id FROM usuario WHERE usua_id=".$rs->field('usua_idrecibe')."))");
		$this->beginBlock();
		$this->printField('        USUARIO: '.$rs->field("usuario_recibe")." [".$usuario_nombre_completo."]", 'HG1','bold','B','L');
	}


	function Detalle(){
		global $rs;
		$this->printField($rs->field("id_padre"),  'C1','items','','L');
                $this->printField(date("d/m/Y i:m:s",strtotime($rs->field('dede_fecharecibe'))),  'C2','items','','L');
		$this->printField($rs->field("tiex_abreviado").' '.$rs->field("num_documento"),  'C3','items','','L');
                $this->printField(dtos($rs->field("desp_fecha")),  'C4','items','','L');
                $this->printField($rs->field("desp_folios"),  'C5','items','','C');
                $this->printField($rs->field("depe_nombrecorto_origen"),  'C6','items','','L');
                $this->printField(utf8_decode($rs->field("desp_firma")),  'C7','items','','L');
                $this->printField(utf8_decode($rs->field("desp_cargo")),  'C8','items','','L');
                $this->printField(utf8_decode($rs->field("desp_asunto")),  'C9','items','','L',true);
	}

	function PieGrupo2(){
		$this->Line($this->blockPosX, $this->blockPosY+$this->lasth,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+$this->lasth); // Imprimo Línea al final de cada grupo
		$this->beginBlock();
		$this->printField('        Total Registros en Usuario:', 'HG2','bold',0,'L');
		$this->printField(number_format($this->functions['CONT_GRUPO2']['C1'], 0, '.', ','), 'HG3','bold',0,'R');
	}

	function PieGrupo1(){
		$this->Line($this->blockPosX, $this->blockPosY+$this->lasth,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+$this->lasth); // Imprimo Línea al final de cada grupo
		$this->beginBlock();
		$this->printField('Total Registros en Dependencia:', 'HG2','bold',0,'L');
		$this->printField(number_format($this->functions['CONT_GRUPO1']['C1'], 0, '.', ','), 'HG3','bold',0,'R');
	}

	function Summary(){
		/* Summary del Reporte*/
		$this->beginBlock();	
		$this->Line($this->blockPosX, $this->blockPosY,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY);
		$this->printField("TOTAL GENERAL DE REGISTROS:", 'HG2','bold',0,'L');
		$this->printField(number_format($this->functions['CONT_TOTAL']['C1'], 0, '.', ','), 'HG3','bold',0,'R');
		$this->Line($this->blockPosX, $this->blockPosY+4,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+4);
		$this->Line($this->blockPosX, $this->blockPosY+4+1,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+4+1);		

	}

}

/*	recibo los parámetros */
$_titulo        = getParam("_titulo"); // Título del reporte
$nbusc_depe_id  = getParam("nbusc_depe_id");
$nbusc_user_id  = getParam("nbusc_user_id");
$nbusc_tiex_id  = getParam("nbusc_tiex_id");
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
$sql=new despachoDerivacion_SQLlista();
$sql->whereEnProceso();
$sql->whereNOAdjuntados();

if($nbusc_depe_id)
    $sql->whereDepeDestino($nbusc_depe_id);

if($nbusc_user_id)
    $sql->whereUsuaRecibe($nbusc_user_id);

if($nbusc_tiex_id)
    $sql->whereTExpediente($nbusc_tiex_id);

//if($nbusc_estado)
//    $sql->whereEstado($nbusc_estado);

if($nrbusc_fdesde)
    $sql->whereFechaRecibeDesde($nrbusc_fdesde);

if($nrbusc_fhasta)
    $sql->whereFechaRecibeHasta($nrbusc_fhasta);

$sql->orderDos2();
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

$Subtitle.="INGRESADOS A 'EN PROCESO' DESDE: ".dtos($nrbusc_fdesde)." HASTA: ".dtos($nrbusc_fhasta);
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
