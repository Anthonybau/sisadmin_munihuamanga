<?php
/* Consideraciones
	1.- Asegurarse que el ancho total de la cabecera sea igual al ancho total de todos los campos detalle
	2.- Si cambio el tamaño de papel debo controlar el �rea de impresi�n con
		$this->setMaxWidth(210); 
		$this->setMaxHeight(270);

*/

/*  Cargo librerias necesarias */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
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
		$this->addFont('top', 'Arial', '', 10); // Esta fuente la uso para los t�tulos de los grupos

		/* Seteo o configuro los campos que voy a usar en el reporte*/
		$this->SeteoCampos();

		$this->nlnCabecera=2; // Para no dejar l�neas en blanco despu�s de imprimir el Head para pasar a la cabecera
                $this->CampoGrupo1='dias_en_proceso';
		$this->CampoGrupo2='depe_iddestino'; // Voy a tener el Grupo 1 agrupado por el campo repe_descripcion
		$this->CampoGrupo3='usua_idrecibe'; // Voy a tener el Grupo 1 agrupado por el campo repe_descripcion
                $this->Grupo1NewPage=1;
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
                $this->Cell(15,$this->lineHeight+1,'RECIBIDO',1,0,'C',1);
		$this->Cell(40,$this->lineHeight+1,'DOCUMENTO',1,0,'C',1);
		$this->Cell(15,$this->lineHeight+1,'FECHA',1,0,'C',1);
		$this->Cell(10,$this->lineHeight+1,'FOL',1,0,'C',1);
		$this->Cell(30,$this->lineHeight+1,'DEPENDEN.PROCEDE',1,0,'C',1);
		$this->Cell(50,$this->lineHeight+1,'FIRMA',1,0,'C',1);
                $this->Cell(100,$this->lineHeight+1,'ASUNTO',1,0,'C',1);
	}

	function SeteoCampos(){

		/* Defino los campos que voy a usar en el cuerpo del reporte */
		// Campos que van en en detalle, deben empezar su nombre con 'C' o 'N'
		$this->addField('C1', 	99999,    0,	15);
                $this->addField('C2', 	99999,    0,	15);
		$this->addField('C3', 	99999,    0,	40);
		$this->addField('C4', 	99999,    0,	15);
		$this->addField('C5', 	99999,    0,	10);
		$this->addField('C6',   99999,	  0,	30);
                $this->addField('C7',   99999,	  0,	50);
                $this->addField('C8',   99999,	  0,	100);
                
                $this->addField('HG1',  0,	0,	125);
		$this->addField('HG2',  0,	0,	40);
		$this->addField('HG3',  40,	0,	20);

	}

	function TituloGrupo1(){
		global $rs;
		$this->beginBlock();
		$this->printField($rs->field("dias_en_proceso"). ' dias' , 'HG1','top','T','L');
	}
        
	function TituloGrupo2(){
		global $rs;
		$this->beginBlock();
		$this->printField("DEPENDENCIA (UBICACION) : ".$rs->field("depe_nombre_destino").' / '.$rs->field("depe_superior_nombre_destino"), 'HG1','bold','T','L');
	}

	function TituloGrupo3(){
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
                $this->printField(date("d/m/Y",strtotime($rs->field('dede_fecharecibe'))),  'C2','items','','L');
		$this->printField($rs->field("tiex_abreviado").' '.$rs->field("num_documento"),  'C3','items','','L');
                $this->printField(dtos($rs->field("desp_fecha")),  'C4','items','','L');
                $this->printField($rs->field("desp_folios"),  'C5','items','','C');
                $this->printField($rs->field("depe_nombrecorto_origen"),  'C6','items','','L');
                $this->printField($rs->field("desp_firma"),  'C7','items','','L');
                //$this->printField($rs->field("desp_cargo"),  'C8','items','','L');
                $this->printField($rs->field("desp_asunto"),  'C8','items','','L',false);
	}

	function PieGrupo3(){
		$this->Line($this->blockPosX, $this->blockPosY+$this->lasth,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+$this->lasth); // Imprimo Línea al final de cada grupo
		$this->beginBlock();
		$this->printField('        Total Registros en Usuario:', 'HG2','bold',0,'L');
		$this->printField(number_format($this->functions['CONT_GRUPO3']['C1'], 0, '.', ','), 'HG3','bold',0,'R');
	}

	function PieGrupo2(){
		$this->Line($this->blockPosX, $this->blockPosY+$this->lasth,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+$this->lasth); // Imprimo Línea al final de cada grupo
		$this->beginBlock();
		$this->printField('Total Registros en Dependencia:', 'HG2','bold',0,'L');
		$this->printField(number_format($this->functions['CONT_GRUPO2']['C1'], 0, '.', ','), 'HG3','bold',0,'R');
	}

	function PieGrupo1(){
		$this->Line($this->blockPosX, $this->blockPosY+$this->lasth,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+$this->lasth); // Imprimo Línea al final de cada grupo
		$this->beginBlock();
		$this->printField('Total Registros en Top:', 'HG2','bold',0,'L');
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
$depe_id = getParam("tr_depe_id");
$nbusc_depe_id  = getParam("nbusc_depe_id");
$nrbusc_fdesde  = getParam("nrbusc_fdesde");
$nrbusc_fhasta  = getParam("nrbusc_fhasta");
$mayor_dias = getParam("Sx_mayor_dias");

if(getSession("sis_userid")>1 && getSession("sis_level")!=3 && !$nbusc_depe_id){
    alert('Seleccione Dependencia...');
}

/*	establecer conexión con la BD */
$conn = new db();
$conn->open();

//ojo esta funcion se encuntra en 'registroDespacho_class.php'
$sql=new despachoDerivacion_SQLlista();
$sql->whereEnProceso();

if($nbusc_depe_id){
    $sql->whereDepeDestino($nbusc_depe_id);
}
//if($nrbusc_fdesde){
    $sql->whereFechaRecibeDesde($nrbusc_fdesde);
//}
//if($nrbusc_fhasta){
    $sql->whereFechaRecibeHasta($nrbusc_fhasta);
//}
$sql->whereMayDiasenProceso($mayor_dias);

$sql->orderCinco();
$sql=$sql->getSQL();
//echo $sql;

/*	creo el recordset */
$rs = new query($conn, "SET CLIENT_ENCODING=LATIN1;$sql");

if ($rs->numrows()==0){
    alert("No existen registros para procesar...");
}


/* Creo el objeto PDF a partir del REPORTE */
$pdf = new Reporte('L'); // Por defecto crea en hoja A4

/* Define el titulo y subtitulo que tendrá el reporte  */

$Subtitle.="INGRESADOS A 'EN PROCESO' DESDE: ".$nrbusc_fdesde." HASTA: ".$nrbusc_fhasta;
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
