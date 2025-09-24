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

		$this->CampoGrupo1='depe_id'; // Voy a tener el Grupo 1 agrupado por el campo repe_descripcion

		
		/* Establezco mi área de impresión */
		/* Para A4 */ 
		$this->setMaxWidth(210); // Por lo que ancho de A4 son 21cm=210mm
		$this->setMaxHeight(265);  // Por lo que alto de A4 son 29.70=297mm .    (La diferencia entre la altura real del papel y la altura de mi �rea de impresi�n, debe ser m�nimo 30. Por ejm. 297-265=32)
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
		$this->SetX($this->blockPosX);

		$this->Cell(80,$this->lineHeight+1,'DEPENDENCIA/EMPLEADO',1,0,'C',1);
                $this->Cell(14,$this->lineHeight+1,'DNI',1,0,'C',1);
                
		$this->Cell(16,$this->lineHeight+1,'REG.INTER',1,0,'C',1);                
		$this->Cell(16,$this->lineHeight+1,'REG.EXTER',1,0,'C',1);                
		$this->Cell(16,$this->lineHeight+1,'DERIVADOS',1,0,'C',1);                
		$this->Cell(16,$this->lineHeight+1,'POR RECIB',1,0,'C',1);
		$this->Cell(16,$this->lineHeight+1,'EN PROCES',1,0,'C',1);
                $this->Cell(16,$this->lineHeight+1,'ARCHIVAD',1,0,'C',1);                
	}

	function SeteoCampos(){

		/* Defino los campos que voy a usar en el cuerpo del reporte */
		// Campos que van en en detalle, deben empezar su nombre con 'C' o 'N'
                $this->addField('C0', 	99999,    0,	5);
		$this->addField('C1', 	99999,    0,	75);
                $this->addField('C2', 	99999,    0,	14);
                $this->addField('N1', 	99999,    0,	16);
		$this->addField('N2', 	99999,    0,	16);                
                $this->addField('N3', 	99999,    0,	16);
		$this->addField('N4', 	99999,    0,	16);
		$this->addField('N5', 	99999,    0,	16);
                $this->addField('N6', 	99999,    0,	16);
                
                $this->addField('HG1',  0,	0,	110);
		$this->addField('X1',  94,	0,	16);
		$this->addField('X2',  110,	0,	16);
                $this->addField('X3',  126,	0,	16);
                $this->addField('X4',  142,	0,	16);
                $this->addField('X5',  158,	0,	16);
                $this->addField('X6',  174,	0,	16);

	}

	function TituloGrupo1(){
		global $rs;
		$this->beginBlock();
		$this->printField("DEPENDENCIA: ".$rs->field("dependencia").' / '.$rs->field("depe_superior_nombre"), 'HG1','bold','','L');
	}



	function Detalle(){
		global $rs;

                $this->printField($rs->field("persona"),  'C1','items','','L');
                $this->printField($rs->field("dni"),  'C2','items','','L');
                $this->printField($rs->field("registrados_internos"),  'N1','items','','C');
                $this->printField($rs->field("registrados_externos"),  'N2','items','','C');
                $this->printField($rs->field("derivados"),   'N3','items','','C');
                $this->printField($rs->field("por_recibir"), 'N4','items','','C');
                $this->printField($rs->field("en_proceso"),  'N5','items','','C');
                $this->printField($rs->field("archivados"),  'N6','items','','C');
	}


	function PieGrupo1(){
		$this->Line($this->blockPosX, $this->blockPosY+$this->lasth,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+$this->lasth); // Imprimo Línea al final de cada grupo
		$this->beginBlock();
		$this->printField('Total Registros en Dependencia:', 'HG1','bold',0,'C');
		$this->printField(number_format($this->functions['SUMA_GRUPO1']['N1'], 0, '.', ','), 'X1','bold',0,'C');
                $this->printField(number_format($this->functions['SUMA_GRUPO1']['N2'], 0, '.', ','), 'X2','bold',0,'C');
                $this->printField(number_format($this->functions['SUMA_GRUPO1']['N3'], 0, '.', ','), 'X3','bold',0,'C');
                $this->printField(number_format($this->functions['SUMA_GRUPO1']['N4'], 0, '.', ','), 'X4','bold',0,'C');
                $this->printField(number_format($this->functions['SUMA_GRUPO1']['N5'], 0, '.', ','), 'X5','bold',0,'C');
                $this->printField(number_format($this->functions['SUMA_GRUPO1']['N6'], 0, '.', ','), 'X6','bold',0,'C');                
	}

	function Summary(){
		/* Summary del Reporte*/
		$this->beginBlock();	
		$this->Line($this->blockPosX, $this->blockPosY,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY);
		$this->printField("TOTAL GENERAL DE REGISTROS:", 'HG1','bold',0,'C');
		$this->printField(number_format($this->functions['SUMA_TOTAL']['N1'], 0, '.', ','), 'X1','bold',0,'C');
                $this->printField(number_format($this->functions['SUMA_TOTAL']['N2'], 0, '.', ','), 'X2','bold',0,'C');
                $this->printField(number_format($this->functions['SUMA_TOTAL']['N3'], 0, '.', ','), 'X3','bold',0,'C');
                $this->printField(number_format($this->functions['SUMA_TOTAL']['N4'], 0, '.', ','), 'X4','bold',0,'C');
                $this->printField(number_format($this->functions['SUMA_TOTAL']['N5'], 0, '.', ','), 'X5','bold',0,'C');
                $this->printField(number_format($this->functions['SUMA_TOTAL']['N6'], 0, '.', ','), 'X6','bold',0,'C');
		$this->Line($this->blockPosX, $this->blockPosY+4,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+4);
		$this->Line($this->blockPosX, $this->blockPosY+4+1,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+4+1);		

	}

}

/*	recibo los parámetros */
$_titulo = getParam("_titulo"); // Título del reporte
$destino = getParam("destino"); 

$anno=getParam("nbusc_periodo");
$anno=$anno?$anno:0;

$depe_id=getParam("tr_depe_id");
$nbusc_depe_id  = getParam("nbusc_depe_id");
$nbusc_depe_id=$nbusc_depe_id?$nbusc_depe_id:0;
$nbusc_user_id  = getParam("nbusc_user_id");
$nbusc_user_id=$nbusc_user_id?$nbusc_user_id:0;

if(getSession("sis_userid")>1 && getSession("sis_level")!=3 && !$nbusc_depe_id){
    alert('Seleccione Dependencia...');
}

/*	establecer conexión con la BD */
$conn = new db();
$conn->open();

$sql="SELECT SUM(x.registrados_internos) AS registrados_internos,
             SUM(x.registrados_externos) AS registrados_externos,
             SUM(x.derivados) AS derivados,
             SUM(x.por_recibir) AS por_recibir,
             SUM(x.en_proceso) AS en_proceso,             
             SUM(x.archivados) AS archivados,
             x.dependencia,
             x.depe_superior_nombre,
             x.dni,
             x.persona,
             x.depe_id
      FROM (      
            /*DOCUMENTOS EN PROCESO*/
            SELECT 1::NUMERIC AS en_proceso,
                   0::NUMERIC AS derivados,
                   0::NUMERIC AS por_recibir,
                   0::NUMERIC AS archivados,
                   0::NUMERIC AS registrados_internos,
                   0::NUMERIC AS registrados_externos,                   
                   a.depe_iddestino AS  depe_id,
                   a.usua_idrecibe AS  usua_id,
                   d.depe_nombre AS  dependencia,
                   (SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(a.depe_iddestino)) AS depe_superior_nombre,
                   ggg.pers_dni AS dni,
                   TRIM(COALESCE(ggg.pers_apellpaterno,'')) || ' ' || TRIM(COALESCE(ggg.pers_apellmaterno,'')) || ' ' || TRIM(COALESCE(ggg.pers_nombres,'')) AS persona
            FROM gestdoc.despachos_derivaciones a
                 LEFT JOIN gestdoc.despachos b ON a.desp_id = b.desp_id
                 LEFT JOIN catalogos.dependencia d ON a.depe_iddestino = d.depe_id
                 LEFT JOIN admin.usuario g ON a.usua_idrecibe = g.usua_id
                 LEFT JOIN personal.persona_datos_laborales gg on g.pdla_id = gg.pdla_id
                 LEFT JOIN personal.persona ggg on gg.pers_id = ggg.pers_id
            WHERE (a.dede_estado = 3 OR a.dede_estado = 7) 
                  AND a.desp_adjuntadoid IS NULL 
                  AND CASE WHEN $nbusc_depe_id>0 THEN a.depe_iddestino=$nbusc_depe_id ELSE TRUE END
                  AND CASE WHEN $nbusc_user_id>0 THEN a.usua_idrecibe=$nbusc_user_id ELSE TRUE END
                  AND b.desp_anno=$anno    
            UNION ALL       
              /*DOCUMENTOS DERIVADOS*/
              SELECT  0::NUMERIC as en_proceso,
                  1::NUMERIC as derivados,
                  0::NUMERIC AS por_recibir,
                  0::NUMERIC AS archivados,
                  0::NUMERIC AS registrados_internos,
                  0::NUMERIC AS registrados_externos,
                 a.depe_idorigen AS depe_id,
                 a.usua_idorigen AS usua_id,
                 c.depe_nombre as dependencia,
                 (SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(a.depe_idorigen)) AS depe_superior_nombre,
                 eee.pers_dni,
                 CASE WHEN e.usua_mesa_partes_virtual = 1 THEN b.desp_codigo || ' ' ||b.desp_firma
                      ELSE TRIM(COALESCE(eee.pers_apellpaterno,'')) || ' ' || TRIM(COALESCE(eee.pers_apellmaterno,'')) || ' ' || TRIM(COALESCE(eee.pers_nombres,'')) 
                 END AS persona                     
            FROM gestdoc.despachos_derivaciones a
               LEFT JOIN gestdoc.despachos b ON a.desp_id = b.desp_id
               LEFT JOIN catalogos.dependencia c ON a.depe_idorigen = c.depe_id
               LEFT JOIN admin.usuario e ON a.usua_idorigen = e.usua_id
               LEFT JOIN personal.persona_datos_laborales ee on e.pdla_id = ee.pdla_id
               LEFT JOIN personal.persona eee on ee.pers_id = eee.pers_id    
            WHERE       CASE WHEN $nbusc_depe_id>0 THEN a.depe_idorigen=$nbusc_depe_id ELSE TRUE END
                  AND   CASE WHEN $nbusc_user_id>0 THEN a.usua_idorigen=$nbusc_user_id ELSE TRUE END
                  AND b.desp_anno=$anno    
            UNION ALL      
            /*DOCUMENTOS POR RECIBIR*/      
               SELECT 0::NUMERIC as en_proceso,
                  0::NUMERIC as derivados,
                  1::NUMERIC AS por_recibir,
                  0::NUMERIC AS archivados,
                  0::NUMERIC AS registrados_internos,
                  0::NUMERIC AS registrados_externos,                  
                  a.depe_iddestino AS depe_id,
                  a.usua_iddestino AS usua_id,
                  d.depe_nombre as dependencia,
                  (SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(a.depe_iddestino)) AS depe_superior_nombre,
                  fff.pers_dni AS dni,
                  TRIM(COALESCE(fff.pers_apellpaterno,'')) || ' ' || TRIM(COALESCE(fff.pers_apellmaterno,'')) || ' ' || TRIM(COALESCE(fff.pers_nombres,'')) AS persona
          FROM gestdoc.despachos_derivaciones a
               LEFT JOIN gestdoc.despachos b ON a.desp_id = b.desp_id
               LEFT JOIN catalogos.dependencia d ON a.depe_iddestino = d.depe_id
               LEFT JOIN personal.persona_datos_laborales dd ON d.pdla_id=dd.pdla_id 
               LEFT JOIN admin.usuario f ON a.usua_iddestino = f.usua_id
               LEFT JOIN personal.persona_datos_laborales ff on f.pdla_id = ff.pdla_id
               LEFT JOIN personal.persona fff on COALESCE(ff.pers_id,dd.pers_id /*jefe*/) = fff.pers_id
          WHERE a.usua_idrecibe IS NULL                          
                AND CASE WHEN $nbusc_depe_id>0 THEN a.depe_iddestino=$nbusc_depe_id ELSE TRUE END
                AND CASE WHEN $nbusc_user_id>0 THEN a.usua_iddestino=$nbusc_user_id ELSE TRUE END
                AND b.desp_anno=$anno
          UNION ALL
          /*DOCUMENTOS ARCHIVADOS*/
            SELECT 0::NUMERIC AS en_proceso,
                   0::NUMERIC AS derivados,
                   0::NUMERIC AS por_recibir,
                   1::NUMERIC AS archivados,
                   0::NUMERIC AS registrados_internos,
                   0::NUMERIC AS registrados_externos,
                   a.depe_iddestino AS  depe_id,
                   a.usua_idrecibe AS  usua_id,
                   d.depe_nombre AS  dependencia,
                   (SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(a.depe_iddestino)) AS depe_superior_nombre,
                   ggg.pers_dni AS dni,
                   TRIM(COALESCE(ggg.pers_apellpaterno,'')) || ' ' || TRIM(COALESCE(ggg.pers_apellmaterno,'')) || ' ' || TRIM(COALESCE(ggg.pers_nombres,'')) AS persona
            FROM gestdoc.despachos_derivaciones a
                 LEFT JOIN gestdoc.despachos b ON a.desp_id = b.desp_id
                 LEFT JOIN catalogos.dependencia d ON a.depe_iddestino = d.depe_id
                 LEFT JOIN admin.usuario g ON a.usua_idrecibe = g.usua_id
                 LEFT JOIN personal.persona_datos_laborales gg on g.pdla_id = gg.pdla_id
                 LEFT JOIN personal.persona ggg on gg.pers_id = ggg.pers_id
            WHERE (a.dede_estado = 6) 
                  AND CASE WHEN $nbusc_depe_id>0 THEN a.depe_iddestino=$nbusc_depe_id ELSE TRUE END
                  AND CASE WHEN $nbusc_user_id>0 THEN a.usua_idrecibe=$nbusc_user_id ELSE TRUE END                                
                  AND b.desp_anno=$anno                      
          UNION ALL
          /*DOCUMENTOS REGISTRADOS*/
            SELECT 0::NUMERIC AS en_proceso,
                   0::NUMERIC AS derivados,
                   0::NUMERIC AS por_recibir,
                   0::NUMERIC AS archivados,
                   CASE WHEN tabl_tipodespacho!= 142 THEN 1 ELSE 0 END AS registrados_internos,
                   CASE WHEN tabl_tipodespacho = 142 THEN 1 ELSE 0 END AS registrados_externos,
                   a.depe_id AS  depe_id,
                   a.usua_id AS  usua_id,
                   d.depe_nombre AS  dependencia,
                   (SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(a.depe_id)) AS depe_superior_nombre,
                   ggg.pers_dni AS dni,
                   TRIM(COALESCE(ggg.pers_apellpaterno,'')) || ' ' || TRIM(COALESCE(ggg.pers_apellmaterno,'')) || ' ' || TRIM(COALESCE(ggg.pers_nombres,'')) AS persona
            FROM gestdoc.despachos a
                 LEFT JOIN catalogos.dependencia d ON a.depe_id = d.depe_id
                 LEFT JOIN admin.usuario g ON a.usua_id = g.usua_id
                 LEFT JOIN personal.persona_datos_laborales gg on g.pdla_id = gg.pdla_id
                 LEFT JOIN personal.persona ggg on gg.pers_id = ggg.pers_id
            WHERE     CASE WHEN $nbusc_depe_id>0 THEN a.depe_id=$nbusc_depe_id ELSE TRUE END
                  AND CASE WHEN $nbusc_user_id>0 THEN a.usua_id=$nbusc_user_id ELSE TRUE END          
                  AND a.desp_anno=$anno

                  ) AS x                      
      GROUP BY x.depe_id,	
                       x.dependencia,
                       x.depe_superior_nombre,
               x.dni,
               x.persona
      ORDER BY x.depe_id,
               x.persona ";

//echo $sql;

/*	creo el recordset */
$rs = new query($conn, "SET CLIENT_ENCODING=LATIN1; $sql");

if ($rs->numrows()==0){
    alert("No existen registros para procesar...");
}

if($destino==2){
    include("../../library/exportaraHojaCalculo.php"); 
}

/* Creo el objeto PDF a partir del REPORTE */
$pdf = new Reporte(); // Por defecto crea en hoja A4

/* Define el titulo y subtitulo que tendrá el reporte  */

$pdf->setTitle($_titulo.' AL '.date('d/m/Y')." - PERIODO $anno");


/* Genero el Pdf */
$pdf->GeneraPdf();

/* Cierrro la conexión */
$conn->close();
/* Visualizo el pdf generado */ 
$pdf->VerPdf();
/* para eliminar la animación WAIT */