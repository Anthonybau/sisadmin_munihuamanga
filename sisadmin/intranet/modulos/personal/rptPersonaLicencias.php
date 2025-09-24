<?
/* Consideraciones
	1.- Asegurarse que el ancho total de la cabecera sea igual al ancho total de todos los campos detalle
	2.- Si cambio el tama?o de papel debo controlar el ?rea de impresi?n con 
		$this->setMaxWidth(210); 
		$this->setMaxHeight(270);
*/

/*  Cargo librerias necesarias */
include("../../library/library.php"); /* Librer?as Generales y de conexi?n del sistema */ 
include("../../library/genpdf/include/genreporte.php"); /* Librer?as para generar el reporte en PDF */ 
include("PersonaLicencias_class.php");

class Reporte extends GenReporte
{
	function SeteoPdf(){
		/* Nombre del archivo a generar */
		$this->NameFile='../../../../docs/reportes/rpt'.rand(1000,1000000).'.pdf';
		
		/* Agrego las fuentes que voy a usar en el reporte */
		$this->addFont('bold', 'Arial', 'B', 8);
		$this->addFont('items', 'Arial', '', 8);
		
		/* Seteo o configuro los campos que voy a usar en el reporte*/
	 	$this->SeteoCampos();		
                $this->CampoGrupo1='pers_id';
                
		/* Establezco mi �rea de impresi�n */
		/* Para A4 */ 
		$this->setMaxWidth(297); // Por lo que ancho de A4 son 21cm=210mm
		$this->setMaxHeight(180);  // Por lo que alto de A4 son 29.70=297mm .    (La diferencia entre la altura real del papel y la altura de mi ?rea de impresi?n, debe ser m?nimo 30. Por ejm. 297-265=32)
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
		$this->SetX($this->blockPosX);
                $this->Cell(35,$this->lineHeight,'TIPO DE','TRL',0,'C',1);
		$this->Cell(22,$this->lineHeight,'REMUNERADO','TRL',0,'C',1);
		$this->Cell(20,$this->lineHeight,'FECHA','TRL',0,'C',1);		
                $this->Cell(55,$this->lineHeight,'DOCUMENTO','TRL',0,'C',1);		
                $this->Cell(40,$this->lineHeight,'PERIODO','1',0,'C',1);
                $this->Cell(18,$this->lineHeight,'TOTAL','1',0,'C',1);
                $this->Cell(18,$this->lineHeight,'SUBVENC','TRL',0,'C',1);		                
                $this->Cell(55,$this->lineHeight,'EMISOR','TLR',1,'C',1);
                
                
                $this->Cell(35,$this->lineHeight,'LICENCIA','BRL',0,'C',1);
                $this->Cell(22,$this->lineHeight,'','BRL',0,'C',1);
                $this->Cell(20,$this->lineHeight,'DOC.','BRL',0,'C',1);		
                $this->Cell(55,$this->lineHeight,'','BRL',0,'C',1);
                $this->Cell(20,$this->lineHeight,'DESDE','1',0,'C',1);
                $this->Cell(20,$this->lineHeight,'HASTA','1',0,'C',1);                
                $this->Cell(18,$this->lineHeight,'EN DIAS','BLR',0,'C',1);
                $this->Cell(18,$this->lineHeight,'EN DIAS','BLR',0,'C',1);
                $this->Cell(55,$this->lineHeight,'','BRL',0,'C',1);
	}

	function SeteoCampos(){
		/* Defino los campos que voy a usar en el cuerpo del reporte */
		// Campos que van en en detalle, deben empezar su nombre con 'C'
		$this->addField('C1',  99999,	0,  35);
                $this->addField('C2',  99999,	0,  22);
                $this->addField('C3',  99999,	0,  20);
                $this->addField('C4',  99999,	0,  55);
                $this->addField('C5',  99999,	0,  20);
                $this->addField('C6',  99999,	0,  20);
                $this->addField('C7',  99999,	0,  18);
                $this->addField('C8',  99999,	0,  18);
                $this->addField('C9',  99999,	0,  55);
                
		// Campos que se imprimir�n como Head de grupos, deben empezar su nombre con 'HG'
		   // Para este caso puedo usar tambien la funci�n 	$pdf->beginBlock("T�tulo del Grupo");	
		$this->addField('HG1',    0,	0,	120);
                $this->addField('S1',   0,	0,  10);

	}
	
        function TituloGrupo1(){
		global $rs,$cont_empleado;
		$this->beginBlock(); 		
                $this->printField($rs->field("empleado")." / ".$rs->field("dni"), 'HG1','bold',0,'L');
                $cont_empleado++;
	}
		
	function Detalle(){
		global $rs;	
                $this->printField($rs->field("tipo_licencia"), 'C1','items',0,'L');		
                $this->printField($rs->field("remunerado"), 'C2','items',0,'L');		
		$this->printField(dtos($rs->field("peli_fecha")), 'C3','items',0,'C');		
                $this->printField($rs->field("peli_documento"), 'C4','items',0,'L');		
                $this->printField(dtos($rs->field("peli_desde")), 'C5','items',0,'C');		
                $this->printField(dtos($rs->field("peli_hasta")), 'C6','items',0,'C');		
                $this->printField($rs->field("peli_dias"), 'C7','items',0,'C');
                $this->printField($rs->field("peli_subvencion"), 'C8','items',0,'C');                
                $this->printField($rs->field("peli_emisor"), 'C9','items',0,'L');
	}

        function PieGrupo1(){
		/* */
                $this->Line($this->blockPosX, $this->blockPosY+$this->lasth,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+$this->lasth); // Imprimo L?nea al final de cada grupo		
		$this->beginBlock();
		$this->printField('TOTAL DE REGISTROS', 'HG1','','','C');
		$this->printField($this->functions[CONT_GRUPO1][C1], 'S1','',0,'R');
        }
        
	function Summary(){
                global $cont_empleado;
		$this->Line($this->blockPosX, $this->blockPosY+$this->lasth,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+$this->lasth); // Imprimo L?nea al final de cada grupo		
		$this->beginBlock();					
		$this->printField('TOTAL TRABAJADORES', 'HG1','bold',0,'C');		
		$this->printField($cont_empleado, 'S1','bold',0,'R');
	}
}

/*	recibo los parametros */
	
	$_titulo = "REGISTRO DE LICENCIAS LABORALES" ; // T?tulo del reporte
	
        $id_relacion = getParam("id_relacion"); 
        $fechIni = getParam("Dr_fechadesde"); 
        $fechFin = getParam("Dr_fechahasta"); 
        $tabl_tipo_licencia=getParam("tx_tabl_tipo_licencia"); 
        $tipo_remunerado=getParam("xr_tipo_remunerado"); 
        $filtro=getParam("sx_filtro"); 
        $cont_empleado=0;
        
/*	establecer conexion con la BD */
	$conn = new db();
	$conn->open();
	
	$sql=new clsPersonaLicencias_SQLlista();
        
        if($id_relacion) {
            $sql->wherePadreID($id_relacion);
        }
        if($fechIni && $fechFin){
            $sql->wherePeriodo($fechIni,$fechFin);
            $Subtitle="DESDE: ".$fechIni.'  '."HASTA:".$fechFin;
        }
        
        if($tabl_tipo_licencia){
            $sql->whereTipoLicencia($tabl_tipo_licencia);
        }
        
        if(inlist($tipo_remunerado,'1,2')){
            $sql->whereTipoRemuneado($tipo_remunerado);
        }
        
        if($filtro){
            $sql->whereDescrip($filtro);
        }
                
        $sql->orderDos();
        $sql=$sql->getSQL();
        //echo $sql;

	/* creo el recordset */
	$rs = new query($conn, "SET CLIENT_ENCODING=LATIN1;".
                               $sql);
	
	if ($rs->numrows()==0){
            alert("No existen datos con los parametros seleccionados");
	}

/* Creo el objeto PDF a partir del REPORTE */
	$pdf = new Reporte('L'); // Por defecto crea en hoja A4
/* Define el t�tulo y subt�tulo que tendr� el reporte  */ 
	$pdf->setTitle($_titulo);
        if($Subtitle){$pdf->setSubTitle($Subtitle);}
	/* Genero el Pdf */
	$pdf->GeneraPdf();
/* Cierrro la conexi�n */
	$conn->close();
/* Visualizo el pdf generado*/ 
	$pdf->VerPdf();