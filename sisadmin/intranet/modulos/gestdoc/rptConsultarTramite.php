<?php
/* Consideraciones
	1.- Asegurarse que el ancho total de la cabecera sea igual al ancho total de todos los campos detalle
	2.- Si cambio el tama?o de papel debo controlar el ?rea de impresi?n con 
		$this->setMaxWidth(210); 
		$this->setMaxHeight(270);

*/

/*  Cargo librerias necesarias */
require_once "../../library/library.php"; /* Librerias Generales y de conexion del sistema */ 
require_once "../../library/genpdf/include/genreporte.php"; /* Librerias para generar el reporte en PDF */ 
require_once "$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/modulos/gestdoc/registroDespacho_class.php";


class Reporte extends GenReporte
{
        var $subTitle2;
	function SeteoPdf(){
                $this->NameFile='../../../../docs/reportes/rpt'.rand(1000,1000000).'.pdf';
                
		/* Agrego las fuentes que voy a usar en el reporte */
		$this->addFont('bold', 'Arial', 'B', 8); // Esta fuente la uso para los t?tulos de los grupos
                $this->addFont('detalles', 'Arial', '', 7); // Esta fuente la uso para los t?tulos de los grupos                
		$this->addFont('textocabecera', 'Arial', '', 8); // Esta fuente la uso para los t?tulos de los grupos

		/* Seteo o configuro los campos que voy a usar en el reporte*/
		$this->SeteoCampos();

		/* Agrego los grupos que voy a tener */
		$this->CampoGrupo1='desp_id'; // Voy a tener el Grupo 1 agrupado por el campo grupo_nivel		
                $this->Grupo1NewPage=1;
                
		$this->lineHeight=4; // Altura de cada celda
                
		/* Establezco mi ?rea de impresi?n */
		/* Para A4 */ 
		$this->setMaxWidth(210); // Por lo que ancho de A4 son 21cm=210mm
		$this->setMaxHeight(297);  // Por lo que alto de A4 son 29.70=297mm .    (La diferencia entre la altura real del papel y la altura de mi área de impresión, debe ser mínimo 30. Por ejm. 297-265=32)
								   // Uso solo 265 porque considero mi area de impresi?n solo para el cuerpo del reporte,  Sin considerar el Head y el footer
		// Modo de visualizaci?n. (real equivale a  100%)
		$this->SetDisplayMode('real');

		// Creo la primera p?gina
		$this->Open(); 
		$this->AddPage();
	}
             
        
	function SeteoCampos(){
		/* Defino los campos que voy a usar en el cuerpo del reporte */
		// Campos que van en en detalle, deben empezar su nombre con 'C'
		$this->addField('C1',  99999,	0,  6);
		$this->addField('C2',  99999,	0,  16);		
		$this->addField('C3',  99999,	0,  30);
		$this->addField('C4',  99999,	0,  30);		
		$this->addField('C5',  99999,	0,  16);
		$this->addField('C6',  99999,	0,  30);
		$this->addField('C7',  99999,	0,  30);
		$this->addField('C8',  99999,	0,  20); 
                $this->addField('C9',  99999,	0,  20); 
                
                
		// Campos que se imprimir?n como Head de grupos, deben empezar su nombre con 'HG'
		   // Para este caso puedo usar tambien la funci?n 	$pdf->beginBlock("T?tulo del Grupo");	
		$this->addField('HG1',   0,	0,  190);
	}

	function title(){
	}


	function TituloGrupo1(){
		global $rs;

                $this->blockPosX = $this->GetX();
                
		//$this->SetFontHeadFooter(); // Seteo el font para head y footer
                $this->SetFont('Arial', '', 6);
		//Colors of frame, background and text
		$this->SetFillColor(230,230,230);
		$this->SetTextColor(0,0,0);
	
		// Ancho de la l�nea borde de cualquier celda
		$this->SetLineWidth(0.3);

		if($this->font_defs['header'][0] == "") {
			$this->_setFontDefs();
		}
		$font_type = $this->font_defs['header'][0];
		$font_weight = $this->font_defs['header'][1];
		$font_size = $this->font_defs['header'][2]+2;
	
		$extra_width = 20;
		
		$w = $this->GetStringWidth($this->title)+ $extra_width;

		//$this->SetFont($font_type, $font_weight, $font_size-3);
		if(($this->GetStringWidth($this->subTitle)+ $extra_width) > $w)
			$w = $this->GetStringWidth($this->subTitle)+ $extra_width;

		//Title
		if($w>$this->maxWidth){
			$w=$this->maxWidth;
                }
                
		$this->SetX(($this->maxWidth-$w)/2);
		$this->SetFont($font_type, $font_weight, $font_size-2);
		$this->Cell($w,$this->lineHeight,$this->title,0,1,'C');

		if($this->subTitle){
			// Subt?tulo	
			$this->SetX(($this->maxWidth-$w)/2);
			$this->SetFont($font_type, $font_weight, $font_size-3);;
			$this->Cell($w,$this->lineHeight-1,$this->subTitle,0,0,'C');
                }        
                    
                
                $this->SetX(10);
                $this->Ln(8);

		// Save the Y offset.  This is where the first block following the header will appear.
		$this->maxYoff = $this->GetY();
                
		//$this->SetX(15);                                
		$this->beginBlock(); 		                
                $this->SetFont('Arial', '', 8);
                $this->Cell(24,$this->lineHeight,'FECHA','LT',0,'l',0);
                $this->Cell(2,$this->lineHeight,':','T',0,'l',0);
		$this->SetFont('Arial', 'B', 8);
                $this->Cell(172,$this->lineHeight,$rs->field("desp_fecha"),'TR',0,'L',0);
                $this->beginBlock(); 		
                $this->SetFont('Arial', '', 8);
                $this->Cell(24,$this->lineHeight,'DOCUMENTO:','L',0,'l',0);
                $this->Cell(2,$this->lineHeight,':','',0,'l',0);
		$this->SetFont('Arial', 'B', 8);
                $this->Cell(172,$this->lineHeight,$rs->field("num_documento"),'R',0,'L',0);
                $this->beginBlock();
                $this->SetFont('Arial', '', 8);
                $this->Cell(24,$this->lineHeight,'ASUNTO:','L',0,'l',0);
                $this->Cell(2,$this->lineHeight,':','',0,'l',0);
		$this->SetFont('Arial', 'B', 8);
                $this->Cell(172,$this->lineHeight,$rs->field("desp_asunto"),'R',0,'L',0);
                $this->beginBlock();
                $this->SetFont('Arial', '', 8);
                $this->Cell(24,$this->lineHeight,'FIRMA:','L',0,'l',0);
                $this->Cell(2,$this->lineHeight,':','',0,'l',0);
		$this->SetFont('Arial', 'B', 8);
                $this->Cell(172,$this->lineHeight,$rs->field("desp_firma"),'R',0,'L',0);
                $this->beginBlock();
                $this->SetFont('Arial', '', 8);
                $this->Cell(24,$this->lineHeight,'EMAIL:','L',0,'l',0);
                $this->Cell(2,$this->lineHeight,':','',0,'l',0);
		$this->SetFont('Arial', 'B', 8);
                $this->Cell(172,$this->lineHeight,$rs->field("desp_email"),'R',0,'L',0);
                $this->beginBlock();
                
                $this->_resetFontDef();                
                $this->SetX(10);
                $this->Cell(6,$this->lineHeight,'Ord','TLR',0,'C',1);
                $this->Cell(76,$this->lineHeight+1,utf8_decode('PROCEDENCIA'),1,0,'C',1);
		$this->Cell(76,$this->lineHeight+1,utf8_decode('DESTINO'),1,0,'C',1);
		$this->Cell(20,$this->lineHeight,'PROVEIDO',1,0,'C',1);
		$this->Cell(20,$this->lineHeight,'ESTADO',1,1,'C',1);

                $this->Cell(6,$this->lineHeight,'','BLR',0,'C',1);
		$this->Cell(16,$this->lineHeight,'F.Registro',1,0,'C',1);
		$this->Cell(30,$this->lineHeight,'Dependencia',1,0,'C',1);		
		$this->Cell(30,$this->lineHeight,'Responsable',1,0,'C',1);
		$this->Cell(16,$this->lineHeight,'F.Recibe',1,0,'C',1);
		$this->Cell(30,$this->lineHeight,'Dependencia',1,0,'C',1);
		$this->Cell(30,$this->lineHeight,'Responsable',1,0,'C',1);
                $this->Cell(20,$this->lineHeight,'',1,0,'C',1);
                $this->Cell(20,$this->lineHeight,'',1,0,'C',1);
                
                $pos_y = $this->GetY();
                $this->nyIniciaDetalle = $this->GetY(); // Guardo la posicion "Y" donde empiezo a imprimir el detalle
                $this->maxYoff = $pos_y;
                $this->SetFont('Arial', 'B', 6);
	}

	function Detalle(){
		global $rs,$i;
		
        	/* Imprimo campos */		
                $this->printField($i, "C1",'detalles','0','C',true);
                
                if($rs->field('dede_fregistro')){
                    $this->printField(date("d/m/Y H:i:s",strtotime($rs->field('dede_fregistro'))), "C2",'detalles','0','C',true);
                }else{
                    $this->printField("", "C2",'detalles','0','C');
                }
                
                $this->printField($rs->field("depe_nombrecorto_origen"), "C3",'detalles','0','C',true);
                
                $this->printField($rs->field('usuario_origen'), "C4",'detalles','0','C',true);
                    
                
                if($rs->field('dede_fecharecibe')){
                    $this->printField(date("d/m/Y H:i:s",strtotime($rs->field('dede_fecharecibe'))), "C5",'detalles','0','C',true);
                }else{
                    $this->printField("", "C5",'detalles','0','C');
                }
                
                $this->printField($rs->field('depe_nombrecorto_destino'), "C6",'detalles','0','C',true);
                if($rs->field('usuario_recibe')){
                    $this->printField($rs->field('usuario_recibe'), "C7",'detalles','0','C',true);
                }
                else{
                    $this->printField($rs->field('usuario_destino'), "C7",'detalles','0','C',true);
                }
                $this->printField($rs->field('dede_proveido'), "C8",'detalles','0','C',true);
                
                if($rs->field('dede_estado')==3){//recibido
                    $adjuntadoID=$rs->field('desp_adjuntadoid');
                    if($adjuntadoID){
                        $this->printField('ADJUNTADO AL REG:'.$adjuntadoID, "C9",'detalles','0','C',true);
                    }
                    else{
                        $this->printField($rs->field('estado'), "C9",'detalles','0','C',true);
                    }

                }
                elseif($rs->field('dede_estado')==4){//derivado
                    $this->printField($rs->field('estado'), "C9",'detalles','0','C',true);

                }
                elseif($rs->field('dede_estado')==6){//Archivado
                    $this->printField($rs->field('estado'), "C9",'detalles','0','C',true);
                    $this->beginBlock();
                    $this->printField($rs->field('estado').utf8_decode(' EL DIA ').date("d/m/Y-H:i:s",strtotime($rs->field('dede_fechaarchiva'))).' '.
                                    'MOTIVO: '.$rs->field('dede_motivoarchiva'), "HG1",'bold','0','C',true);

                }
                elseif($rs->field('dede_estado')==7){//Activado
                    $this->printField($rs->field('estado'), "C9",'detalles','0','C',true);
                    $this->beginBlock();
                    $this->printField($rs->field('estado').utf8_decode(' EL DIA ').date("d/m/Y-H:i:s",strtotime($rs->field('dede_fechaactiva'))).' '.
                                        'MOTIVO: '.$rs->field('dede_motivoactiva'), "C9",'bold','0','C',true);

                }
                else{
                    $this->printField($rs->field('estado'), "C9",'detalles','0','C',true);
                }
                $this->beginBlock();	
                $this->Line($this->blockPosX, $this->blockPosY,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY);
                $i++;
	}
	
        
        function VerPdf()
	{
		/* Muestro el PDF final */
		$this->Output($this->NameFile);

                header("Content-type: application/pdf");
                header("Content-Disposition: inline; filename=".$this->NameFile);
                readfile($this->NameFile);

	}

        
}

if(isset($_POST['id_despacho']) || isset($_GET['id_despacho'])){

    /* establecer conexi?n con la BD */
    $conn = new db();
    $conn->open();


    $id_despacho = getParam("id_despacho");


    $_titulo = "CONSULTA DE TRAMITE DE DOCUMENTO : $id_despacho"; 
    $constultaTime="Consulta Realizada el ".date('d/m/Y')." a las ".date('H:i:s');
    $i=1;

    $sql=new despachoDerivacion_SQLlista(2);
    $sql->wherePadreID($id_despacho);
    //$sql->whereTDespacho(142);
    $sql->orderUno();
    $sql=$sql->getSQL();
    //echo $sql;
    //exit(0);
    /*	creo el recordset */
    $rs = new query($conn, "SET CLIENT_ENCODING=LATIN1;$sql");

    if ($rs->numrows()==0){
        alert("No existen datos para procesar");
    }


    /* Creo el objeto PDF a partir del REPORTE */
    $pdf = new Reporte(); 
    $pdf->setTitle($_titulo);
    $pdf->setSubTitle($constultaTime);

    /* Genero el Pdf */
    $pdf->GeneraPdf();


    
    /* Visualizo el pdf generado*/ 
    $pdf->VerPdf();

    $conn->close();

    
}
