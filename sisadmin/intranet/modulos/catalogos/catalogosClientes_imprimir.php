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
include("catalogosCliente_class.php"); 

class Reporte extends GenReporte
{
	function SeteoPdf(){
                global $tipo_formato;
		$this->NameFile='../../../../docs/reportes/rpt'.rand(1000,1000000).'.pdf';            
		/* Agrego las fuentes que voy a usar en el reporte */
		$this->addFont('bold', 'Arial', 'B', 9); // Esta fuente la uso para los t�tulos de los grupos
		$this->addFont('items', 'Arial', '', 9); // Esta fuente la uso para los t�tulos de los grupos
		/* Seteo o configuro los campos que voy a usar en el reporte*/
		$this->SeteoCampos();

		/* Agrego los grupos que voy a tener */
                if($tipo_formato==1){//agrupado por UBIGEO
                    $this->CampoGrupo1='tabl_ubigeo';
                }elseif($tipo_formato==2){//agrupado por UBIGEO-2
                    $this->CampoGrupo1='ubig_id';
                }else{
                    $this->CampoGrupo1='clie_id';
                }        


		$this->nlnCabecera=2; // Para no dejar l�neas en blanco despu�s de imprimir el Head para pasar a la cabecera		
		$this->lineHeight=4; // Altura de cada celda


		
		/* Establezco mi �rea de impresi�n */
		/* Para A4 */ 
                $this->setMaxWidth(297); // Por lo que ancho de A4 son 21cm=210mm
                $this->setMaxHeight(180);  // Por lo que alto de A4 son 29.70=297mm .    (La diferencia entre la altura real del papel y la altura de mi �rea de impresi�n, debe ser m�nimo 30. Por ejm. 297-265=32)

		// Establezco mi m�rgen izquierdo para que el cuerpo del reporte apareza centrado
		$this->SetLeftMargin((($this->maxWidth-$this->WidthTotalCampos)/2));

		// Modo de visualizaci�n. (real equivale a  100%)
		$this->SetDisplayMode('real');

		// Creo la primera p�gina
		$this->Open(); 
		$this->AddPage();

	}

	function Cabecera(){
		// Aqu� imprimo los campos como titulos para el cuerpo del discurso
		$this->SetX($this->blockPosX);
		$this->Cell(20,$this->lineHeight,'CODIGO',1,0,'C',1);
                $this->Cell(100,$this->lineHeight,'NOMBRES/APELLIDOS/RAZON SOCIAL',1,0,'C',1);
                $this->Cell(90,$this->lineHeight,'DIRECCION',1,0,'C',1);
                $this->Cell(20,$this->lineHeight,'TELEFONO',1,0,'C',1);
                
                if(SIS_EMPRESA_TIPO!=4){//EMPRESA TIPO ALMACENES
                    $this->Cell(30,$this->lineHeight,'TIPO NEGOCIO',1,0,'C',1);
                }
	}

	function SeteoCampos(){
                
		//grupos		

		/* Defino los campos que voy a usar en el cuerpo del reporte */
		// Campos que van en en detalle, deben empezar su nombre con 'C' o 'N'
		$this->addField('C1', 	99999,    0,	20);
                $this->addField('C2', 	99999,    0,	100);
		$this->addField('C3', 	99999,    0,	90);
                $this->addField('C4', 	99999,    0,	20);
                if(SIS_EMPRESA_TIPO!=4){//EMPRESA TIPO ALMACENES
                    $this->addField('C5', 	99999,    0,	30);
                }
                $this->addField('HG1',   0,	0,	100);
        

	}

	function Detalle(){
		global $rs;

                $this->printField($rs->field("codigo"),  'C1','','B','L');
                $this->printField(utf8_decode($rs->field("razon_social")),  'C2','','B','L');
                $this->printField(utf8_decode($rs->field("direccion")),  'C3','','B','L');                
                $this->printField($rs->field("telefono"),  'C4','','B','L');
                $this->printField($rs->field("tipo_negocio"),  'C5','','B','L');

	}

        
        function TituloGrupo1(){
		global $rs,$tipo_formato;
                if($tipo_formato==1){//AGRUPADO POR UBIGEO
                    $this->beginBlock();
                    $this->printField('UBIGEO: '.utf8_decode($rs->field("ubigeo")), 'HG1','bold',0,'L');                    
                }elseif($tipo_formato==2){//AGRUPADO POR UBIGEO-2 
                    $this->beginBlock();
                    $this->printField('UBIGEO: '.utf8_decode($rs->field("departamento").'-'.$rs->field("provincia").'-'.$rs->field("distrito")), 'HG1','bold',0,'L');
                }
  	}
        
	function Summary(){
		/* Summary del Reporte*/
		$this->beginBlock();	
		$this->Line($this->blockPosX, $this->blockPosY,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY);
		$this->lineHeight+=2 ;				
		$this->printField("Total Items:", 'C2','items',0,'L');				
		$this->printField($this->functions['CONT_TOTAL']['C1'], 'C1','items',0,'R');	
		$this->Line($this->blockPosX, $this->blockPosY+6,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+6);

	}


}


$tipo_formato = getParam("nbusc_formato");

/*	recibo los parametros */
$_titulo      = 'LISTADO DE CLIENTES'; // Titulo del reporte


$busc_ubigeo_pedido = getParam("nx_busc_ubigeo_pedido");
$busc_ubig_id = getParam("sx_busc_ubig_id");
$busc_tipo_negocio = getParam("nx_busc_tipo_negocio");
$grupo_id = getParam("nBusc_grupo_id");

$cadena    = getParam("Sbusc_cadena");
$op        = getParam("op");

$Subtitle="";

/*	establecer conexi�n con la BD */
$conn = new db();
$conn->open();

//if($tipo_formato==1){//agrupado por UBIGEO
//    $sql = "SELECT a.tabl_ubigeo, 
//               COALESCE(a.clie_dni,a.clie_codigo) AS codigo,
//               a.clie_razsocial AS razon_social,
//               a.clie_direccion AS direccion,
//               a.clie_telefono AS telefono,
//               ubigeo.tabl_descripcion AS ubigeo
//            FROM catalogos.cliente a
//           LEFT JOIN catalogos.tabla                           ubigeo ON a.tabl_ubigeo=ubigeo.tabl_id 
//            WHERE a.clie_id IN (SELECT DISTINCT clie_id 
//                                FROM siscore.recaudaciones
//                                WHERE reca_estado!=9
//                                AND reca_modingreso!=20
//                                )
//        ORDER BY a.tabl_ubigeo,
//                 a.clie_razsocial
//    ";    
//}elseif($tipo_formato==2){//agrupado por UBIGEO-2
//    $sql = "SELECT a.ubig_id, 
//               COALESCE(a.clie_dni,a.clie_codigo) AS codigo,
//               a.clie_razsocial AS razon_social,
//               a.clie_direccion AS direccion,
//               a.clie_telefono AS telefono,
//               split_part(ubigeo.ubig_descripcion::TEXT,'-', 1) AS departamento,
//               split_part(ubigeo.ubig_descripcion::TEXT,'-', 2) AS provincia,
//               split_part(ubigeo.ubig_descripcion::TEXT,'-', 3) AS distrito
//            FROM catalogos.cliente a
//            LEFT JOIN catalogos.ubigeo   ubigeo ON a.ubig_id=ubigeo.ubig_id
//            WHERE a.clie_id IN (SELECT DISTINCT clie_id 
//                                FROM siscore.recaudaciones
//                                WHERE reca_estado!=9
//                                AND reca_modingreso!=20
//                                )
//        ORDER BY a.ubig_id,
//                 a.clie_razsocial
//    ";    
//}else{
//
//    if($op==1){//impresion desde la busquedas
        
        $clientes=new cliente_SQLlista();

        if ($busc_ubigeo_pedido>0){
            $clientes->whereTablUbigeo($busc_ubigeo_pedido);

        }
        if ($busc_ubig_id>0){
            $clientes->whereUbigeoID($busc_ubig_id);
        }

        if ($busc_tipo_negocio>0){
            $clientes->whereTipoNegocio($busc_tipo_negocio);

        }
        
        if ($grupo_id>0){
            $Subtitle="GRUPO : ";
            $Subtitle.=getDbValue("SELECT segr_descripcion FROM catalogos.servicio_grupo WHERE segr_id=$grupo_id");
            $clientes->whereGrupoId($grupo_id);
        }

        //se analiza la columna de b�squeda
        switch($colSearch){
                case 'clie_id': // si se recibe el campo id
                        $clientes->whereID($cadena);								
                        break;

                case 'codigo': // si se recibe el campo id
                        $clientes->whereCodigo($cadena);
                        break;

                default:// si se no se recibe ningun campo de busqueda
                        if(ctype_digit($cadena)){ //si la cadena recibida son todos digitos
                            $clientes->whereCodigo($cadena);
                        }else{
                            $clientes->whereDescrip($cadena);
                        }
                        
                        break;
        }

        
        
        if (SIS_TIPO_UBIGEO_CLIENTE==1){
            $tipo_formato=1;
            $sql="SELECT a.codigo,
                               a.clie_razsocial AS razon_social,
                               a.clie_direccion AS direccion,
                               a.clie_telefono AS telefono,
                               a.tipo_negocio,
                               a.tabl_ubigeo,
                               a.ubigeo
                           FROM (".$clientes->getSQL().") AS a 
                          ORDER BY a.tabl_ubigeo,
                                a.clie_razsocial DESC ";      
            
        }elseif (SIS_TIPO_UBIGEO_CLIENTE==2){
            $tipo_formato=2;            
            $sql="SELECT a.codigo,
                               a.clie_razsocial AS razon_social,
                               a.clie_direccion AS direccion,
                               a.clie_telefono AS telefono,
                               a.tipo_negocio,
                               a.ubig_id,
                               a.departamento,
                               a.provincia,
                               a.distrito
                           FROM (".$clientes->getSQL().") AS a 
                          ORDER BY a.ubig_id,
                                a.clie_razsocial DESC "; 
            
        }else{
            $sql="SELECT a.codigo,
                                           a.clie_razsocial AS razon_social,
                                           a.clie_direccion AS direccion,
                                           a.clie_telefono AS telefono,
                                           a.tipo_negocio
                                       FROM (".$clientes->getSQL().") AS a 
                                      ORDER BY a.clie_razsocial DESC ";              
        }
        
           
        
        
//    }else{
//        $sql = "SELECT COALESCE(a.clie_dni,a.clie_codigo) AS codigo,
//                   a.clie_razsocial AS razon_social,
//                   a.clie_direccion AS direccion,
//                   a.clie_telefono AS telefono
//                FROM catalogos.cliente a
//                WHERE a.clie_id IN (SELECT DISTINCT clie_id 
//                                    FROM siscore.recaudaciones
//                                    WHERE reca_estado!=9
//                                    AND reca_modingreso!=20
//                                    )
//            ORDER BY a.clie_razsocial
//        ";
//    }
    
//}        
	
//echo $sql;
$rs = new query($conn, "$sql");

if ($rs->numrows()==0){
    alert("No existen datos con los Parametros seleccionados");
}

if(getParam("destino")==2){
    $name_file='cat_clientes_'.rand(1000,1000000).'.xls';
    include("../../library/exportaraHojaCalculo.php"); 
}

$pdf = new Reporte('L'); // Por defecto crea en hoja A4    

/* Define el t�tulo y subt�tulo que tendr� el reporte  */ 
$pdf->setTitle($_titulo);
$pdf->setSubTitle($Subtitle);

/* Genero el Pdf */
$pdf->GeneraPdf();

/* Cierrro la conexi�n */
$conn->close();
/* Visualizo el pdf generado */ 
$pdf->VerPdf();
/* para eliminar la animaci�n WAIT */
//wait('');