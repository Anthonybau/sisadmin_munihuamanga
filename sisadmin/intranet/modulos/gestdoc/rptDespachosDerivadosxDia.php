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

		$this->CampoGrupo1='id_origen'; // Voy a tener el Grupo 1 agrupado por el campo repe_descripcion
		$this->CampoGrupo2='id_destino'; // Voy a tener el Grupo 1 agrupado por el campo repe_descripcion

		
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
                $this->Cell(24,$this->lineHeight+1,'DERIVACION',1,0,'C',1);
		$this->Cell(40,$this->lineHeight+1,'DOCUMENTO',1,0,'C',1);
		$this->Cell(15,$this->lineHeight+1,'FECHA',1,0,'C',1);
		$this->Cell(10,$this->lineHeight+1,'FOL',1,0,'C',1);
                $this->Cell(35,$this->lineHeight+1,'RECIBIDO',1,0,'C',1);
		$this->Cell(50,$this->lineHeight+1,'FIRMA EL DOCUMENTO',1,0,'C',1);
                $this->Cell(85,$this->lineHeight+1,'ASUNTO',1,0,'C',1);
	}

	function SeteoCampos(){

		/* Defino los campos que voy a usar en el cuerpo del reporte */
		// Campos que van en en detalle, deben empezar su nombre con 'C' o 'N'
		$this->addField('C1', 	99999,    0,	15);
                $this->addField('C2', 	99999,    0,	24);
		$this->addField('C3', 	99999,    0,	40);
		$this->addField('C4', 	99999,    0,	15);
		$this->addField('C5', 	99999,    0,	10);
                $this->addField('C7',  99999,	  0,	35);
                $this->addField('C8',   99999,	  0,	50);
                $this->addField('C9',   99999,	  0,	85);
                
                $this->addField('HG1',  0,	0,	125);
		$this->addField('HG2',  0,	0,	40);
		$this->addField('HG3',  40,	0,	20);

	}

	function TituloGrupo1(){
		global $rs;
		$this->beginBlock();                
		$this->printField("POOCEDENCIA: ".$rs->field("depe_nombre_origen").' / '.$rs->field("depe_superior_nombre_origen").' / '.$rs->field("usuario_origen"), 'HG1','bold','T','L');
	}

	function TituloGrupo2(){
		global $rs;
		$this->beginBlock();
		$this->printField("DESTINATARIO: ".$rs->field("depe_nombre_destino").' / '.$rs->field("depe_superior_nombre_destino").iif($rs->field("usuario_destino"),"<>","",' / '.$rs->field("usuario_destino"),""), 'HG1','bold','T','L');
	}


	function Detalle(){
		global $rs;
                
                $this->printField($rs->field("id_padre"),  'C1','items','','L');
                $this->printField(date("d/m/Y i:m:s",strtotime($rs->field('desp_fregistro'))),  'C2','items','','L');
                $this->printField($rs->field("tiex_abreviado").' '.$rs->field("num_documento"),  'C3','items','','L');
                $this->printField(dtos($rs->field("desp_fecha")),  'C4','items','','L');
                $this->printField($rs->field("desp_folios"),  'C5','items','','C');

                if($rs->field('dede_fecharecibe')){
                    $this->printField(date("d/m/Y i:m:s",strtotime($rs->field('dede_fecharecibe'))).' '.$rs->field('login_recibe'),  'C7','items','','L');
                }

                $this->printField(utf8_decode($rs->field("desp_firma")),  'C8','items','','L');
                $this->printField(utf8_decode($rs->field("desp_asunto")),  'C9','items','','L');
	}

	function PieGrupo2(){
		$this->Line($this->blockPosX, $this->blockPosY+$this->lasth,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+$this->lasth); // Imprimo Línea al final de cada grupo
		$this->beginBlock();
		$this->printField('Total Destinatario:', 'HG2','bold',0,'L');
		$this->printField(number_format($this->functions['CONT_GRUPO2']['C1'], 0, '.', ','), 'HG3','bold',0,'R');
	}

	function PieGrupo1(){
		$this->Line($this->blockPosX, $this->blockPosY+$this->lasth,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+$this->lasth); // Imprimo Línea al final de cada grupo
		$this->beginBlock();
		$this->printField('Total Procedencia:', 'HG2','bold',0,'L');
		$this->printField(number_format($this->functions['CONT_GRUPO1']['C1'], 0, '.', ','), 'HG3','bold',0,'R');
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
$nbusc_tipo_despacho  = getParam("nbusc_tipo_despacho");
$nbusc_depe_id  = getParam("nbusc_depe_id");
$nbusc_user_id  = getParam("nbusc_user_id");

$nrbusc_fecha  = getParam("nrbusc_fecha");

$hora_desde  = getParam("Sx_hora_desde");
$hora_hasta  = getParam("Sx_hora_hasta");


$tipo_filtro   = getParam("tipo_filtro");

if(getSession("sis_userid")>1 && getSession("sis_level")!=3 && !$nbusc_depe_id){
    alert('Seleccione Dependencia...');
}

/*	establecer conexión con la BD */
$conn = new db();
$conn->open();
$ult_desp_id='';
//ojo esta funcion se encuntra en 'registroDespacho_class.php'
$sql=new despachoDerivacion_SQLlista(1);

if($nbusc_depe_id){
    $sql->whereDepeOrigen($nbusc_depe_id);
}

if($nbusc_user_id){
    $sql->wherePadreUsuaID($nbusc_user_id);
}

$sql->whereFechaRegistro($nrbusc_fecha);

if($hora_desde){
    $sql->whereHoraDesde($hora_desde);
}

if($hora_hasta){
    $sql->whereHoraHasta($hora_hasta);
}

if($tipo_filtro==2){//RECIBIDOS
    $sql->whereRecibidos();
}elseif($tipo_filtro==3){//PENDIENTES DE RECEPCION
    $sql->wherePendienteRecibido();
}

$sql->orderSiete();
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
if($hora_desde){
    $Subtitle.="HORA DESDE: ".$hora_desde;    
}
if($hora_hasta){
    $Subtitle.="HORA HASTA: ".$hora_hasta;    
}
$fecha=explode("-",$nrbusc_fecha);

$pdf->setTitle($_titulo." EL ".$fecha[2]." DE ". list_mes($fecha[1])." DEL ".$fecha[0]);
$pdf->setSubTitle($Subtitle);

$tipoDespacho=getDBValue("SELECT tabl_descripcion FROM tabla WHERE tabl_id=$nbusc_tipo_despacho");

$rs->getrow();
$rs->skiprow(0);



/* Genero el Pdf */
$pdf->GeneraPdf();

/* Cierrro la conexión */
$conn->close();
/* Visualizo el pdf generado */ 
$pdf->VerPdf();
/* para eliminar la animación WAIT */