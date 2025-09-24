<?php
/* Consideraciones
	1.- Asegurarse que el ancho total de la cabecera sea igual al ancho total de todos los campos detalle
	2.- Si cambio el tama�o de papel debo controlar el �rea de impresi�n con 
		$this->setMaxWidth(210); 
		$this->setMaxHeight(270);

*/

/*  Cargo librerias necesarias */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
include("../../library/genpdf/include/genreporte.php"); /* Librer�as para generar el reporte en PDF */ 

class Reporte extends GenReporte
{
	function SeteoPdf(){
                global $cuenta_tipos_precios;
		$this->NameFile='../../../../docs/reportes/rpt'.rand(1000,1000000).'.pdf';            
		/* Agrego las fuentes que voy a usar en el reporte */
		$this->addFont('bold', 'Arial', 'B', 9); // Esta fuente la uso para los t�tulos de los grupos
		$this->addFont('items', 'Arial', '', 9); // Esta fuente la uso para los t�tulos de los grupos
		/* Seteo o configuro los campos que voy a usar en el reporte*/
		$this->SeteoCampos();

		/* Agrego los grupos que voy a tener */
                $this->CampoGrupo1='depe_id';
                //$this->CampoGrupo2='tabl_tipoprecio';
                $this->CampoGrupo2='segr_id';
                $this->CampoGrupo3='sesg_id';
                $this->Grupo1NewPage=1;
                        


		$this->nlnCabecera=2; // Para no dejar l�neas en blanco despu�s de imprimir el Head para pasar a la cabecera		
		$this->lineHeight=4; // Altura de cada celda


		
		/* Establezco mi �rea de impresi�n */
		/* Para A4 */ 
                if($cuenta_tipos_precios>5){
                    $this->setMaxWidth(297); // Por lo que ancho de A4 son 21cm=210mm
                    $this->setMaxHeight(180);  // Por lo que alto de A4 son 29.70=297mm .    (La diferencia entre la altura real del papel y la altura de mi �rea de impresi�n, debe ser m�nimo 30. Por ejm. 297-265=32)
                }else{
                    $this->setMaxWidth(210); // Por lo que ancho de A4 son 21cm=210mm
                    $this->setMaxHeight(260);  // Por lo que alto de A4 son 29.70=297mm .    (La diferencia entre la altura real del papel y la altura de mi �rea de impresi�n, debe ser m�nimo 30. Por ejm. 297-265=32)
                }

		// Establezco mi m�rgen izquierdo para que el cuerpo del reporte apareza centrado
		$this->SetLeftMargin((($this->maxWidth-$this->WidthTotalCampos)/2));

		// Modo de visualizaci�n. (real equivale a  100%)
		$this->SetDisplayMode('real');

		// Creo la primera p�gina
		$this->Open(); 
		$this->AddPage();

	}

	function Cabecera(){
                global $rsPrecios;
		// Aqu� imprimo los campos como t�tulos para el cuerpo del discurso
		$this->SetX($this->blockPosX);
		$this->Cell(12,$this->lineHeight,utf8_decode('CODIGO'),1,0,'C',1);
                $this->Cell(100,$this->lineHeight,utf8_decode('DESCRIPCION'),1,0,'C',1);
                $this->Cell(10,$this->lineHeight,'U.MED',1,0,'C',1);
                $this->Cell(10,$this->lineHeight,'T.IGV',1,0,'C',1);
		//$this->Cell(10,$this->lineHeight,'TIPO',1,0,'C',1);
                
//                $this->Cell((20),$this->lineHeight,'PRECIO ',1,0,'C',1);
                
                $rsPrecios->skiprow(0);
                while ($rsPrecios->getrow()) {
                    $this->Cell(16,$this->lineHeight,'P.'. substr($rsPrecios->field('tabl_descripcion'),0,7),1,0,'C',1);
                }
                
                if(inlist(SIS_EMPRESA_TIPO,'2,3')){//PUBLICA
                    $this->Cell(25,$this->lineHeight,'CLASIFICADOR',1,0,'C',1);		
                }
	}

	function SeteoCampos(){
                global $rsPrecios;
                
		//grupos		
		$this->addField('HG1',   0,	0,	197);				

		/* Defino los campos que voy a usar en el cuerpo del reporte */
		// Campos que van en en detalle, deben empezar su nombre con 'C' o 'N'
		$this->addField('C1', 	99999,    0,	12);
                $this->addField('C2', 	99999,    0,	100);
		$this->addField('C3', 	99999,    0,	10);
                $this->addField('C4', 	99999,    0,	10);
                //$this->addField('C4', 	99999,    0,	10);
                
                $rsPrecios->skiprow(0);
                while ($rsPrecios->getrow()) {
                    $this->addField('C'.$rsPrecios->field('tabl_id'), 	99999,    0,	16);
                }
                
                
                if(inlist(SIS_EMPRESA_TIPO,'2,3')){//PUBLICA
                    $this->addField('C6', 	99999,    0,	25);
                }
		

		$this->addField('HG2',   0,	0,	25);
		$this->addField('HG3',  25, 0,	8);		

	}

	function Detalle(){
		global $rs,$rsPrecios,$ult_codigo,$ult_grupo;

		if($ult_codigo!=$rs->field("serv_codigo") || $ult_grupo==1){
                    /* Imprimo campos */		
                    if($rs->field("serv_codigo_aux")){//SI TIENE CODIGO INTERNO
                        $this->printField($rs->field("serv_codigo_aux"),'C1','',0,'C');		                    
                    }else{    
                        $this->printField($rs->field("serv_codigo"),'C1','',0,'C');		
                    }

                    $this->printField(utf8_decode(utf8_decode($rs->field("serv_descripcion"))),  'C2','',0,'L',true);
                    $this->printField(utf8_decode(substr($rs->field("serv_umedida"),0,3)),  'C3','',0,'C');                    
                    $this->printField(substr($rs->field("tipo_igv"),0,3),  'C4','',0,'C');                    
                    $ult_codigo=$rs->field("serv_codigo");
                    $ult_grupo==0;
                }
                
                
                $rsPrecios->skiprow(0);
                while ($rsPrecios->getrow()) {
                    $campo="precio_". $rsPrecios->field('tabl_id');
                    $this->printField(' '.$rs->field("$campo"),  'C'.$rsPrecios->field('tabl_id'),'',0,'R');                
                }
                
                if(inlist(SIS_EMPRESA_TIPO,'2,3')){//PUBLICA
                    $this->printField(' '.$rs->field("clas_id"),  'C6','',0,'C');
                }
	}

	function TituloGrupo1(){
	/*  Imprimo los campos como t�tulo o Head del Grupo 1
		Aqu� puedo usar tambien $this->beginBlock(); para pasar a otra fila o l�nea 
	*/
		global $rs;
		$this->beginBlock(); 
		$this->printField($rs->field("ruc").' '.utf8_decode($rs->field("razon_social")), 'HG1','bold',0,'L');
	}

//        function TituloGrupo2(){
//		global $rs;
//		$this->beginBlock(); 
//		$this->printField('TIPO DE PRECIO: '.$rs->field("tipo_precio"), 'HG1','bold',0,'L');
//	}
        
	function TituloGrupo2(){
	/*  Imprimo los campos como t�tulo o Head del Grupo 1
		Aqu� puedo usar tambien $this->beginBlock(); para pasar a otra fila o l�nea 
	*/
		global $rs,$ult_grupo;
		$this->beginBlock(); 
		$this->printField('  '.utf8_decode($rs->field("grupo")), 'HG1','bold',0,'L');
                $ult_grupo=1;

	}

	function TituloGrupo3(){
	/*  Imprimo los campos como t�tulo o Head del Grupo 1
		Aqu� puedo usar tambien $this->beginBlock(); para pasar a otra fila o l�nea 
	*/
		global $rs;
		$this->beginBlock(); 
		$this->printField('      '.utf8_decode($rs->field("sgrupo")), 'HG1','bold',0,'L');
	}

	function PieGrupo1(){
		parent::PieGrupo1();	// Llamo a la funci�n padre.
		$this->beginBlock();		
		$this->printField("TOTAL Items:", 'HG2','items',0,'L');				
		$this->printField($this->functions['CONT_GRUPO1']['C1'], 'HG3','items',0,'R');	
                
		$this->Line($this->blockPosX, $this->blockPosY,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY);
                $this->beginBlock();
	}
        
//        function PieGrupo2(){
//		parent::PieGrupo1();	// Llamo a la funci�n padre.
//		$this->beginBlock();		
//		$this->printField("Sub total Items:", 'HG2','items',0,'L');				
//		$this->printField($this->functions['CONT_GRUPO2']['C1'], 'HG3','items',0,'R');	
//                
//		$this->Line($this->blockPosX, $this->blockPosY,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY);
//                $this->beginBlock();
//	}
        
	function PieGrupo2(){
		parent::PieGrupo1();	// Llamo a la funci�n padre.
		$this->beginBlock();		
		$this->printField("Sub total Items:", 'HG2','items',0,'L');				
		$this->printField($this->functions['CONT_GRUPO2']['C1'], 'HG3','items',0,'R');	
                
		$this->Line($this->blockPosX, $this->blockPosY,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY);
                $this->beginBlock();
	}

	function PieGrupo3(){
		parent::PieGrupo1();	// Llamo a la funci�n padre.
		$this->beginBlock();		
		$this->printField("Sub total Items:", 'HG2','items',0,'L');				
		$this->printField($this->functions['CONT_GRUPO3']['C1'], 'HG3','items',0,'R');	
		$this->Line($this->blockPosX, $this->blockPosY,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY);
                $this->beginBlock();

	}
        
//	function Summary(){
//		/* Summary del Reporte*/
//		$this->beginBlock();	
//		$this->Line($this->blockPosX, $this->blockPosY,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY);
//		$this->lineHeight+=2 ;				
//		$this->printField("Total Items:", 'HG2','items',0,'L');				
//		$this->printField($this->functions['CONT_TOTAL']['C1'], 'HG3','items',0,'R');	
//		$this->Line($this->blockPosX, $this->blockPosY+6,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+6);
//                $this->Line($this->blockPosX, $this->blockPosY+6+1,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+6+1);
//	}


}

/*	recibo los par�metros */
if (SIS_EMPRESA_TIPO==4){ //empresa del tipo Almacenes
    $_titulo = 'LISTA DE BIENES/PRODUCTOS';
}else{
    $_titulo = 'LISTA DE BIENES/SERVICIOS Y PRECIOS'; // T�tulo del reporte
}

$destino = getParam("destino"); 
$depe_id = getParam("nbusc_depe_id");
$grupo   = getParam("nBusc_grupo_id");
$sgrupo  = getParam("nBusc_sgrupo_id");
$cadena  = getParam("Sbusc_cadena");
$tipo = getParam("tipo");
$ocultar_precios=getParam("hx_ocultar_precios");
$ocultar_precios=$ocultar_precios?$ocultar_precios:0;

$anulados=0;
$ult_codigo='';
$ult_grupo='';
/*	establecer conexi�n con la BD */
$conn = new db();
$conn->open();

if($ocultar_precios==0){
    $slqPrecios="SELECT tabl_id,
                        tabl_descripcion 
                 FROM catalogos.tabla 
                 WHERE tabl_tipo='TIPO_PRECIO'";
        
}else{
    $slqPrecios="";
}

$rsPrecios = new query($conn, "$slqPrecios;");

$cuenta_tipos_precios=$rsPrecios->numrows();

$sql = "SELECT grupo,
               sgrupo,
               serv_codigo,
               serv_descripcion,
               laboratorio,
               serv_umedida, ";

$rsPrecios->skiprow(0);
while ($rsPrecios->getrow()) {
    $sql .= "   MAX(preciox_".$rsPrecios->field('tabl_id').") AS precio_".$rsPrecios->field('tabl_id'). ",";
}

$sql .=  "      MAX(serv_preciocosto) AS precio_compra,
                ruc,
                razon_social,
                serv_estado,
                serv_codigo_aux,
                serv_equi_unidades,
                tipo_igv,
                clas_id,
                depe_id,
                segr_id,
                sesg_id
        FROM    
            (SELECT 
                    b.segr_descripcion as grupo, 
                    e.sesg_descripcion as sgrupo,
                    LPAD(a.serv_codigo::TEXT,5,'0') AS serv_codigo,
                    a.serv_descripcion,
                    gg.tabl_descripcion AS laboratorio,
                    a.serv_umedida, ";

    $rsPrecios->skiprow(0);
    while ($rsPrecios->getrow()) {
        $sql .= "    
                    CASE WHEN aa.tabl_tipoprecio=".$rsPrecios->field('tabl_id')." THEN aa.sepr_precio ELSE 0 END AS preciox_".$rsPrecios->field('tabl_id').",
            ";    
    }

    $sql .= "   a.serv_preciocosto,
                ww.emru_ruc AS ruc,
                w.depe_nombre AS razon_social,
                a.serv_estado,            
                a.serv_codigo_aux,
                a.clas_id,
                aa.depe_id,
                g.tabl_descripcion AS tipo_igv,
                aa.tabl_tipoprecio,
                a.serv_equi_unidades,
                a.segr_id,
                a.sesg_id
            FROM catalogos.servicio_precios aa  
            LEFT JOIN catalogos.servicio a  ON aa.serv_codigo=a.serv_codigo
            LEFT JOIN catalogos.servicio_grupo b on a.segr_id=b.segr_id				
            LEFT JOIN catalogos.servicio_sgrupo e on a.sesg_id=e.sesg_id
            LEFT JOIN catalogos.clasificador d on a.clas_id=d.clas_id	
            LEFT JOIN catalogos.tabla g ON a.tabl_tipo_igv=g.tabl_codigo and g.tabl_tipo='TIPO_IGV'
            LEFT JOIN gestmed.examen f on a.exam_id=f.exam_id
            LEFT JOIN catalogos.dependencia w ON aa.depe_id=w.depe_id 
            LEFT JOIN admin.empresa_ruc ww ON w.emru_id=ww.emru_id 
            LEFT JOIN catalogos.tabla x ON aa.tabl_tipoprecio=x.tabl_id
            LEFT JOIN catalogos.tabla gg ON a.tabl_farmacia_laboratorio=gg.tabl_id
            WHERE a.serv_estado ";

            if($depe_id>0){
                $sql .= "AND aa.depe_id=$depe_id ";    
            }else{
                $sql .= "AND aa.depe_id IN (SELECT depe_id
                                                FROM catalogos.func_treedependencia2(".getSession("sis_depe_superior").")
                                                WHERE depe_id>0) ";
            }
            
            if($grupo)  $sql .= "AND a.segr_id=$grupo ";
            if($sgrupo) $sql .= "AND a.sesg_id=$sgrupo ";		
            if($cadena) $sql .= "AND (LPAD(a.serv_codigo::TEXT,4,'0')  ILIKE '%$cadena%' OR a.serv_descripcion ILIKE '%$cadena%') ";

//            if(getSession('SET_EMRU_EMISOR')){
//                $sql .= "AND a.emru_id=".getSession('SET_EMRU_EMISOR')." ";
//            }
            
        $sql .= ") AS a
            GROUP BY a.grupo,
                 a.sgrupo,
                 a.serv_codigo,
                 a.serv_descripcion,
                 a.laboratorio,
                 a.serv_umedida,
                 a.ruc,
                 a.razon_social,
                 a.serv_estado,
                 a.serv_codigo_aux,
                 a.tipo_igv,
                 a.clas_id,
                 a.depe_id,
                 a.serv_equi_unidades,
                 a.segr_id,
                 a.sesg_id     
        ORDER BY a.depe_id,
                 a.segr_id,
                 a.sesg_id,
                 a.serv_descripcion                       
";
        
	
//echo $sql;
$rs = new query($conn, $sql);

if ($rs->numrows()==0){
    alert("No existen datos con los Parametros seleccionados");
}

if($destino==2){
    $name_file='cat_prod_serv_'.rand(1000,1000000).'.xls';
    include("../../library/exportaraHojaCalculo.php"); 
}

/* Creo el objeto PDF a partir del REPORTE */
if($cuenta_tipos_precios>5){
    $pdf = new Reporte('L'); // Por defecto crea en hoja A4    
}else{
    $pdf = new Reporte(); // Por defecto crea en hoja A4
}
/* Define el t�tulo y subt�tulo que tendr� el reporte  */ 
$pdf->setTitle($_titulo);
$Subtitle="";

$pdf->setSubTitle($Subtitle);

/* Genero el Pdf */
$pdf->GeneraPdf();

/* Cierrro la conexi�n */
$conn->close();
/* Visualizo el pdf generado */ 
$pdf->VerPdf();
/* para eliminar la animaci�n WAIT */
//wait('');