<?php
include($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");
define('K_PATH_IMAGES', $_SERVER['DOCUMENT_ROOT']);
require_once('../../library/tcpdf/tcpdf.php');
include('../../library/phpqrcode/qrlib.php');

class MYPDF extends TCPDF {

    //Page header
    public function Header() {

        global $oficina_origen, $oficina_origen_depende, $depe_superior_nombre, $peri_anno_nombre,$plde_formato,
               $plde_imagen_fondo, $plde_orientacion,$margin_top,$margin_right,$margin_left,$width,$alto_header,
               $header_img,$pone_oficina_origen,$pone_nombre_anno;
        
        if(!$plde_formato || inlist($plde_formato,'1,3')){//FORMATO DOCUMENTO
        
            if($header_img!=''){
                
                $image_file = K_PATH_IMAGES.$header_img;
                
                if (file_exists($image_file)){
                    $this->Image($image_file, $margin_left, $margin_top, $width-($margin_left+$margin_right), $alto_header,  'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                    $this->Ln(intval($alto_header));
                }
                
                // Subtitle 
                if($pone_oficina_origen==1){
                    $this->SetFont('helvetica', 'B', 8);
                    $this->Cell(0, 0, $oficina_origen, '', 1, 'C');
                }
                
                if($pone_nombre_anno==1){
                    $this->SetFont('helvetica', '', 10);
                    $this->Cell(0, 0, $peri_anno_nombre, '', false, 'C');                
                }
            }else{
                // Logo
                $image_file = K_PATH_IMAGES.'/sisadmin/intranet/img/'.'logo_'.strtolower(SIS_EMPRESA_SIGLAS).'.jpg';
                
                if (file_exists($image_file)){            
                    $this->Image($image_file, $margin_left, $margin_top, 8,  "", 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                    $this->Ln(1);
                }

                // Set font
                $this->SetFont('helvetica', 'B', 10);
                // Title
                $this->Cell(0, 0, SIS_EMPRESA, 0, false, 'C');
                
                // Subtitle 
                if($pone_oficina_origen==1){
                    $this->SetFont('helvetica', 'B', 8);
                    $this->Ln(5);
                    $this->Cell(0, 0, $oficina_origen, 'B', 1, 'C');
                }
                
                if($pone_nombre_anno==1){
                    $this->SetFont('helvetica', '', 10);
                    $this->Cell(0, 0, $peri_anno_nombre, '', false, 'C');
                }
            }
        }else{//FORMATO DISEÑO
            // get the current page break margin
            $bMargin = $this->getBreakMargin();
            // get current auto-page-break mode
            $auto_page_break = $this->AutoPageBreak;
            // disable auto-page-break
            $this->SetAutoPageBreak(false, 0);
            // set bacground image
            $img_file = K_PATH_IMAGES.$plde_imagen_fondo;
            
           if (file_exists($img_file)) {
                if($plde_orientacion==1){//VERTICAL
                    $this->Image($img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);   
                }else{//HORIZONTAL
                    $this->Image($img_file, 0, 0, 297, 210, '', '', '', false, 300, '', false, false, 0);   
                }
           }
            // restore auto-page-break status
            $this->SetAutoPageBreak($auto_page_break, $bMargin);
            // set the starting point for the page content
            $this->setPageMark();               
        }        
        
    }    
    
    
    
    // Page footer
    	public function Footer() {
            global $footer_img,$margin_bottom,$margin_left,$width,$margin_right,$width,$alto_footer,
                   $id,$id_rand,$marcar_final;
        
            if($footer_img!=''){
                $this->SetY(intval($margin_bottom+$alto_footer)*-1);
                $image_file = K_PATH_IMAGES.$footer_img;
                
                if (file_exists($image_file)){            
                    $this->Image($image_file, $margin_left, $this->GetY(), $width-($margin_left+$margin_right), $alto_footer,  'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                }
                $this->Ln(intval($alto_footer)-5);
                // Set font
                $this->SetFont('helvetica', 'I', 9);
                // Page number
                $this->Cell(0, 0, $this->getAliasRightShift().'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), '', 0, 'C');
            }else{            
		$cur_y = $this->y;
		$this->SetTextColorArray($this->footer_text_color);
		
                //set style for cell border
		$line_width = (0.85 / $this->k);
		$this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->footer_line_color));

		$w_page = isset($this->l['w_page']) ? $this->l['w_page'].' ' : '';
		$pagenumtxt = $w_page.$this->getAliasNumPage().'/'.$this->getAliasNbPages();
		$this->SetY($cur_y-8);
                $this->SetFont('helvetica', 'I', 9);
//                $this->SetX($this->original_lMargin);
//                $this->Cell(0, 0, $this->getAliasRightShift().'Página '.$pagenumtxt, 'T', 0, 'R');
                
                /*BUSCO SI EXISTE UN FIRMANTE SIN CERTIFICADO*/
//                $sin_certificado = getDBValue(" SELECT COUNT(a.desp_id) AS sin_certificado
//                                                FROM gestdoc.despachos_firmas a 
//                                                LEFT JOIN catalogos.dependencia b ON a.depe_id = b.depe_id
//                                                LEFT JOIN personal.persona_datos_laborales c ON c.pdla_id = a.pdla_id
//                                                LEFT JOIN personal.persona d ON d.pers_id = c.pers_id 
//                                                LEFT JOIN (SELECT DISTINCT a.usua_set_certificado,
//                                                                  b.pers_id
//                                                             FROM admin.usuario a 
//                                                             LEFT JOIN personal.persona_datos_laborales b on  a.pdla_id=b.pdla_id)  u ON c.pers_id = u.pers_id 
//                                                WHERE a.desp_id = $id 
//                                                AND a.defi_tipo IN (1,4) /*OJO, SOLO FIRMAS (PRINCIPAL Y OTROS JEFES)*/
//                                                AND u.usua_set_certificado=0 /*SIN CERTIFICADO*/");
//                $sin_certificado=$sin_certificado?$sin_certificado:0;
                            
                                        
                    if($marcar_final==1){
                            $this->SetY($cur_y-8);
                            $id_validacion=$id.'.'.$id_rand;

                            $nameFile_QR="../../../../docs/reportes/qr$id.png";
                            $url=PATH_PORT."valida/gestdoc/index.php";
                            $QR="$url?id_validacion=$id_validacion";
                            
                            $this->SetFont('helvetica', 'I', 8);
                            $this->SetFillColor(255, 255, 255);                            
                            $this->SetTextColor(0,0,0);
                            
                            $this->MultiCell(156, 0,"Esta es una copia autentica imprimible de un documento electrónico archivado en ".SIS_EMPRESA." aplicando ".
                                                   "lo dispuesto por el Art. 25 de D.S. 070-2013-PCM, la Tercera Disposición Complementaria Final del ".
                                                   "D.S. 026-2016-PCM y el Art. 36  del D.S. 029-2021-PCM. Su  autenticidad e integridad  pueden  ser contrastadas ".
                                                   "a  través  de  la  siguiente  dirección  web  Url: $url ".
                                                   "e ingresando el siguiente Código de Verificación Digital: $id_validacion                            ", 0,'J');
                            
//                            $this->Cell(0, 0,"Su autenticidad e integridad pueden ser contrastada a través de la siguiente dirección web: ", '', 1, 'L');
//                            $this->Cell(0, 0,"$url       ", '', 1, 'L');
//                            $this->Cell(0, 0,"ingresando el siguiente Código de Validación Digital-CVD: ".$id_validacion, '', 1, 'L');
                            
                            $this->SetY($cur_y-9);
                            QRcode::png($QR, $nameFile_QR, QR_ECLEVEL_Q,2,3); //nombre de archivo, (Error Correction Level): nivel Q, 2 cm, 3 en total, dejando 1 cm Zona de silencio mínimo (Quiet Zone) 
                            //$this->Cell(0, 0, 'Código de Validación Digital-CVD: '.$id_validacion, '', 1, 'C');
                            $this->Image("$nameFile_QR", $this->GetX()+$width-52, $this->GetY(), 20, 20, '', '', '', false, 20);
                    }
                    
                $this->SetFont('helvetica', 'I', 9);    
                $this->SetY($cur_y+6);                            
                $this->Cell(0, 0, 'Página '.$pagenumtxt, '', 0, 'R');
            }
	}

}


$conn = new db();
$conn->open();

$id = getParam("id");
$tipo_vista=getParam("tipo_vista");
$tipo_vista=$tipo_vista?$tipo_vista:'FI';

$marcar_final=getParam("marcar_final");
$marcar_final=$marcar_final?$marcar_final:0;

$op = getParam("op");         //->VISTA PREVIA DESDE PLANTILLA

if($op==2){//->VISTA PREVIA DESDE PLANTILLA
    $name_file="";
    $periodo = getDbValue("SELECT EXTRACT(YEAR FROM NOW()) AS periodo");
    $ocultar_editor==0;
}else{ //DESDE DESPACHO
    $sql="SELECT desp_id,
                 desp_ocultar_editor,
                 desp_anno AS periodo,
                 desp_file_firmado
            FROM gestdoc.despachos
         WHERE desp_id=$id
        ";
    
    $rsVerifica = new query($conn, $sql);
    $rsVerifica->getrow();
    $id=$rsVerifica->field('desp_id');    
    $name_file=$rsVerifica->field('desp_file_firmado');    
    $periodo = $rsVerifica->field('periodo');
    $ocultar_editor = $rsVerifica->field('desp_ocultar_editor');
}    

if($ocultar_editor==0){//SI SE HA ELABORADO DOCUMENTO

    include('./makeDirectory.php');

    if($name_file==''){
        if($op==2){//->VISTA PREVIA DESDE PLANTILLA
            $sql = "SELECT   EXTRACT(YEAR FROM NOW()) AS periodo,
                             TO_CHAR(NOW(),'YYYY-MM-DD') AS desp_fecha,
                             '00000' AS desp_id_rand,
                             a.plde_contenido AS desp_contenido,
                             '** VISTA PREVIA **' AS desp_asunto,                         
                            'VISTA PREVIA 0000' AS documento,
                            EXTRACT(YEAR FROM NOW()) AS num_documento,
                            '** VISTA PREVIA **'  AS desp_para_destino,
                            '** VISTA PREVIA **'  AS desp_para_cargo,
                            '** VISTA PREVIA **'  AS desp_para_dependencia,
                             a.plde_formato,
                             a.plde_orientacion,
                             a.plde_imagen_fondo,
                             0 AS pone_cabecera,
                             1 AS pone_oficina_origen,
                             1 AS pone_nombre_anno,
                             COALESCE(c.tapa_ancho,210) AS width,
                             COALESCE(c.tapa_alto,297) AS height,
                             COALESCE(c.tapa_top,21) AS margin_top,
                             COALESCE(c.tapa_left,15) AS margin_left,
                             COALESCE(c.tapa_right,15) AS margin_right,
                             COALESCE(c.tapa_botom,25) AS margin_bottom,
                             COALESCE(c.tapa_header,5) AS alto_header,
                             COALESCE(c.tapa_footer,15) AS alto_footer,
                             COALESCE(c.tapa_header_img,'') AS header_img,
                             COALESCE(c.tapa_footer_img,'') AS footer_img
                    FROM gestdoc.plantilla_despacho a
                         LEFT JOIN catalogos.tipo_expediente b ON a.tiex_id = b.tiex_id
                         LEFT JOIN gestdoc.tamano_pagina c ON COALESCE(b.tapa_id,1) = c.tapa_id
                         WHERE a.plde_id = $id
            ";
            $ok=1;
            
        }else{ //DESDE DESPACHO

            /*SI PIDE MARCAR COMO FINAL*/
            /*OJO esto debe IR AQUI para que aplique un UPDATE y actualice los destinatarios*/
            if($marcar_final==1)
            {
                /*actualiza el nombre del arhivo y se cierra el documento*/
                $sSql="UPDATE gestdoc.despachos
                                SET desp_file_firmado='df_'||desp_id_rand::TEXT||'.pdf',
                                    desp_file_firmado_fregistro=NOW(),
                                    desp_estado=2 /*CERRADO*/
                         WHERE desp_id=$id;
                        ";

                // Ejecuto el string
                $conn->execute($sSql);
                $error=$conn->error();

                if($error){ 
                    $ok=0;
                    $jsondata["success"] = false;
                    $jsondata["mensaje"] = $error;
                }else{
                    $ok=1;
                    $jsondata["success"] = true;
                    $jsondata["mensaje"] = "";
                }
            }else{
                $ok=1;
            }
                        
            /* obtengo los datos del documento */
            $sql = "SELECT  a.tabl_tipodespacho,
                            a.desp_fecha,
                            a.desp_para_destino,
                            a.desp_para_cargo,
                            a.desp_para_dependencia,
                            a.desp_asunto,
                            a.desp_cabecera,
                            a.desp_pie,
                            a.desp_contenido,
                            a.desp_cont_firmas,
                            a.desp_cont_firmados,
                            a.desp_especbreve,
                            a.desp_firma,
                            a.desp_cargo,
                            a.depe_id,
                            a.tiex_id,
                            a.desp_referencia,
                            a.desp_formato AS plde_formato,
                            a.desp_orientacion AS plde_orientacion,
                            a.desp_imagen_fondo AS plde_imagen_fondo,
                            a.depe_id_proyectado,
                            EXTRACT(YEAR FROM desp_fecha) AS periodo,
                            a.desp_anno AS periodo,
                            LPAD(a.desp_numero::TEXT,6,'0')||'-'||a.desp_anno||'-'||COALESCE(a.desp_siglas,'') AS num_documento,
                            a.desp_id::TEXT as id,
                            a.desp_trelacionado::TEXT as desp_trelacionado,
                            a.desp_id_rand,
                            b.tiex_abreviado,
                            b.tiex_descripcion AS documento,
                            CASE WHEN COALESCE(b.cado_id,0)>0 THEN 1 
                                 ELSE 0
                            END AS pone_cabecera,
                            CASE WHEN COALESCE(b.pido_id,0)>0 THEN 1 
                                 ELSE 0
                            END AS pone_pie,
                            COALESCE(a.desp_incluir_oficina_origen,0) AS pone_oficina_origen,
                            COALESCE(a.desp_incluir_nombre_anno,0) AS pone_nombre_anno,
                            COALESCE(c.tapa_ancho,210) AS width,
                            COALESCE(c.tapa_alto,297) AS height,
                            COALESCE(c.tapa_top,21) AS margin_top,
                            COALESCE(c.tapa_left,15) AS margin_left,
                            COALESCE(c.tapa_right,15) AS margin_right,
                            COALESCE(c.tapa_botom,25) AS margin_bottom,
                            COALESCE(c.tapa_header,5) AS alto_header,
                            COALESCE(c.tapa_footer,15) AS alto_footer,
                            COALESCE('/docs/catalogos/".SIS_EMPRESA_RUC."/'||a.depe_id::TEXT||'/'||cc.pcpi_cabecera,'/docs/gestdoc/margenes/".SIS_EMPRESA_RUC."/'||c.tapa_header_img) AS header_img,
                            COALESCE('/docs/catalogos/".SIS_EMPRESA_RUC."/'||a.depe_id::TEXT||'/'||cc.pcpi_pie,'/docs/gestdoc/margenes/".SIS_EMPRESA_RUC."/'||c.tapa_footer_img) AS footer_img,
                            e.prat_descripcion,
                            f.depe_nombre AS oficina_origen,
                            fff.depe_nombre AS oficina_origen_depende,
                            (SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(a.depe_id)) AS depe_superior_nombre,
                            x.usua_iniciales||'/'||ff.depe_nombrecorto AS poyectado_por
                    FROM gestdoc.despachos a
                         LEFT JOIN catalogos.tipo_expediente b ON a.tiex_id = b.tiex_id
                         LEFT JOIN gestdoc.tamano_pagina c ON COALESCE(b.tapa_id,1) = c.tapa_id
                         LEFT JOIN gestdoc.personalizar_cabecera_pie cc ON c.tapa_id=cc.tapa_id AND a.depe_id=cc.depe_id AND pcpi_estado=1
                         LEFT JOIN gestdoc.prioridad_atencion e ON a.prat_id = e.prat_id
                         LEFT JOIN catalogos.dependencia f ON a.depe_id = f.depe_id
                         LEFT JOIN catalogos.dependencia ff ON a.depe_id_proyectado=ff.depe_id                                
                         LEFT JOIN catalogos.dependencia fff ON f.depe_depeid = fff.depe_id
			 LEFT JOIN admin.usuario x ON a.usua_id=x.usua_id                         
                         WHERE a.desp_id = $id
            ";
        }
        
            if($ok==1){
        
                /* creo el recordset */
                $rsDoc = new query($conn, $sql);
                $error=$conn->error();

                if ($rsDoc->numrows() == 0) {
                    $mensaje="No existen registros para procesar...!";
                    if($marcar_final==1){
                        $jsondata["success"] = false;
                        $jsondata["mensaje"] = $error;
                    }else{
                        alert($error); 
                    }
                }
                $rsDoc->getrow();

                /* Variables para cabecera */ 
                $tabl_tipodespacho=$rsDoc->field('tabl_tipodespacho');
                $oficina_origen = $rsDoc->field('oficina_origen');
                $oficina_origen_depende = $rsDoc->field('oficina_origen_depende');
                $depe_superior_nombre = $rsDoc->field('depe_superior_nombre');

                $plde_formato=$rsDoc->field('plde_formato');
                $plde_orientacion=$rsDoc->field('plde_orientacion');
                $plde_imagen_fondo=$rsDoc->field('plde_imagen_fondo');
                $id_rand=$rsDoc->field('desp_id_rand');
                $pone_cabecera=$rsDoc->field('pone_cabecera');
                $pone_pie=$rsDoc->field('pone_pie');
                $pone_oficina_origen=$rsDoc->field('pone_oficina_origen');
                $pone_nombre_anno=$rsDoc->field('pone_nombre_anno');

                $width=$rsDoc->field('width');
                $height=$rsDoc->field('height');
                $margin_top=$rsDoc->field('margin_top');
                $margin_left=$rsDoc->field('margin_left');
                $margin_right=$rsDoc->field('margin_right');
                $margin_bottom=$rsDoc->field('margin_bottom');

                $alto_header=$rsDoc->field('alto_header');            
                $alto_footer=$rsDoc->field('alto_footer');


                $header_img=$rsDoc->field('header_img');
                $footer_img=$rsDoc->field('footer_img');

                $periodo = $rsDoc->field('periodo');

                $nameFile=$_SERVER['DOCUMENT_ROOT'] ."/docs/gestdoc/".SIS_EMPRESA_RUC."/$periodo/$id/df_".$id_rand.".pdf";

                //Close and output PDF document

                

                $sqlPeriodo = " SELECT *
                        FROM periodo
                            WHERE peri_anno = $periodo
                            ";

                /* creo el recordset */
                $rsPeriodo = new query($conn, $sqlPeriodo);

                $rsPeriodo->getrow();

                $peri_anno_nombre = $rsPeriodo->field('peri_anno_nombre');

                /* Imprimo Documento */

                // create new PDF document
                $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array($width,$height), true, 'UTF-8', false);

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                //set margins
                $pdf->SetMargins($margin_left, $margin_top+$alto_header+8, $margin_right);
                $pdf->SetHeaderMargin($alto_header);
                $pdf->SetFooterMargin($alto_footer);

                if($plde_orientacion==2){ //HORIZONTAL
                    $pdf->setPageOrientation('L');        
                }
                if($plde_formato==2){ //formato tipo diseño
                    $pdf->setPrintFooter(false);
                }

                //set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, $margin_bottom);

                //set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // add a page
                $pdf->AddPage();

                if(!$plde_formato || inlist($plde_formato,'1,2,3')){ //1->DOCUMENTO ADMINISTRATIVO, 3->DOCUMENTO DE GESTION
                        
                        if ( $pone_cabecera==1 ){//si tiene cabecera predefinida
                            
                            $firmantes=getDBValue("SELECT COALESCE(RTRIM(text_concat(x.funcionario||'<BR>'),'<BR>'),'')
                                                    FROM (SELECT 
                                                           COALESCE(d.pers_nombres || ' ' || d.pers_apellpaterno || ' ' || d.pers_apellmaterno||'<BR>','')||
                                                           TRIM(COALESCE(c.pdla_cargofuncional,'-- SIN CARGO --'))||CASE WHEN c.pdla_encargado=1 THEN '(E)' ELSE '' END||'<BR>'||
                                                           COALESCE(b.depe_nombre||'<BR>','')       
                                                           AS funcionario
                                                    FROM gestdoc.despachos_firmas a 
                                                    LEFT JOIN catalogos.dependencia b ON a.depe_id = b.depe_id
                                                    LEFT JOIN personal.persona_datos_laborales c ON c.pdla_id = a.pdla_id
                                                    LEFT JOIN personal.persona d ON d.pers_id = c.pers_id 
                                                    WHERE a.desp_id = $id
                                                    AND a.defi_tipo IN (1,4) /*OJO, SOLO FIRMAS (PRINCIPAL Y OTROS JEFES)*/
                                                    ORDER BY a.defi_tipo,
                                                             a.defi_id) AS x ");
                            
                            $html_cabecera = str_replace("{firmante}",$firmantes,$rsDoc->field('desp_cabecera'));
                            
                            
                        }else{
                                $html_cabecera = "";
                                
                                // set font
                                $pdf->SetFont('helvetica', '', 10);

                                $fechaDocumento = dtos($rsDoc->field('desp_fecha'));

                                $fechaDoc = ucwords(strtolower(SIS_CIUDAD)) . ", " . strtolower(diaSemana($fechaDocumento));

                                $pdf->Cell(0, 0, $fechaDoc , 0, 0, 'L');

                                $pdf->Ln();
                                $pdf->Ln();

                                $pdf->SetFont('helvetica', 'B', 10);

                                $documento = $rsDoc->field('documento') . ' ' . $rsDoc->field('num_documento');

                                if(inlist($plde_formato,'3')){ //3->DOCUMENTO DE GESTION
                                    $pdf->Cell(0, 0, "$documento [$id]", 0, 1, 'C');
                                }else{
                                    $pdf->Cell(0, 0, "$documento [$id]", 0, 1, 'L');
                                }

                                $pdf->Ln();
                                if(!$plde_formato || inlist($plde_formato,'1') //1->DOCUMENTO ADMINISTRATIVO
                                        && !$rsDoc->field('desp_para_destino')
                                        ){ 
                                    $sqlDestinatarios = " 
                                            SELECT a.dede_cargo,
                                                   d.pers_nombres || ' ' || d.pers_apellpaterno || ' ' || d.pers_apellmaterno AS destinatario,
                                                   b.depe_nombre,
                                                   (SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(a.depe_id)) AS depe_superior_nombre,
                                                   a.dede_especbreve 
                                            FROM gestdoc.despachos_destinatarios a 
                                            LEFT JOIN catalogos.dependencia b ON a.depe_id = b.depe_id
                                            LEFT JOIN personal.persona_datos_laborales c ON c.pdla_id = a.pdla_id
                                            LEFT JOIN personal.persona d ON d.pers_id = c.pers_id 
                                            WHERE a.desp_id = $id 
                                        ";

                                        /* creo el recordset */
                                        $rsDestinatarios = new query($conn, $sqlDestinatarios);

                                        if ($rsDestinatarios->numrows() > 0) {
                                            while ($rsDestinatarios->getrow()){
                                                $pdf->Cell(0, 0, $rsDestinatarios->field('destinatario'), '', 1, 'L');

                                                if($rsDestinatarios->field('dede_cargo')){
                                                    $pdf->Cell(0, 0, $rsDestinatarios->field('dede_cargo'), '', 1, 'L');
                                                }
                                                if($rsDestinatarios->field('depe_nombre').'-'.$rsDestinatarios->field('depe_superior_nombre')){
                                                    $pdf->Cell(0, 0, $rsDestinatarios->field('depe_nombre'), '', 1, 'L');
                                                }
                                                $pdf->Ln();
                                            }
                                        }

                                }elseif(!$plde_formato || inlist($plde_formato,'1') 
                                        && $rsDoc->field('desp_para_destino')
                                        ){ 

                                        $pdf->Write(0, $rsDoc->field('desp_para_destino'), '', 0, 'L', true, 0, false, false, 0);

                                        if($rsDoc->field('desp_para_cargo')){
                                            $pdf->Write(0, $rsDoc->field('desp_para_cargo'), '', 0, 'L', true, 0, false, false, 0);
                                        }
                                        if($rsDoc->field('desp_para_dependencia')){
                                            $pdf->Write(0, $rsDoc->field('desp_para_dependencia'), '', 0, 'L', true, 0, false, false, 0);
                                        }
                                        $pdf->Ln();                
                                }


                                if(!$plde_formato || inlist($plde_formato,'1')){ //1->DOCUMENTO ADMINISTRATIVO
                                    $pdf->Cell(27, 0, 'ASUNTO         :', 0, 0, 'L');
                                    // get current vertical position
                                    $x = $pdf->getX();
                                    //$pdf->writeHTMLCell(0, '', $x, '', $rsDoc->field('desp_asunto'), 0, 0, false, true, 'J', true);
                                    $pdf->MultiCell(0, '', $rsDoc->field('desp_asunto'), 0, iif(strlen(trim($rsDoc->field('desp_asunto'))),">","50",'J',"L"), false, 1, $x, '', true, 0, false, true, 0, 'T', false);
                                    $pdf->Ln(5);        
                                }

                                if($rsDoc->field('desp_referencia') && inlist($plde_formato,'1')){ //1->DOCUMENTO ADMINISTRATIVO
                                    $pdf->Cell(27, 0, 'REFERENCIA:', 0, 0, 'L');
                                    /* Utilizo MultiCell para poder indicarle que el texto envíado no es html y me reconozca los saltos de línea cuando se colocan varios documentos como referencia */
                                    $pdf->MultiCell(0, '', $rsDoc->field('desp_referencia'), 0, 'L', false, 1, $x, '', true, 0, false, true, 0, 'T', false);
                                    $pdf->Ln();
                                }

                        }
                        
                        
                        if ( $pone_pie==1 ){//si tiene pie predefinida   
                            $html_pie = $rsDoc->field('desp_pie');
                        }else{
                            $html_pie = "";
                        }
                        // set core font
                        $pdf->SetFont('helvetica', '', 10);

                        // create some HTML content
                        $html_contenido = $rsDoc->field('desp_contenido');
                        //para que imprima imagenes en base64 (copiados y pegados de internet)
                        $html_contenido = str_replace("data:image/jpeg;base64,","@",$html_contenido);
                        
                        $html = $html_cabecera;
                        $html.= $html_contenido;
                        $html.= $html_pie;
                        // output the HTML content
                        $pdf->writeHTML($html, true, 0, true, true);
                        
                        /* Imprimo la firma */
                        $pdf->Ln(10);


                        $contadorFirmas = $rsDoc->field('desp_cont_firmas');
                        $contadorFirmados = $rsDoc->field('desp_cont_firmados');

                        if ($marcar_final==1 && inlist($plde_formato,'1,3')){ //1->DOCUMENTO ADMINISTRATIVO
                             /* Obtengo los funcionarios que han dado el visto bueno */
                            $sqlFirmas = " SELECT d.pers_nombres || ' ' || d.pers_apellpaterno || ' ' || d.pers_apellmaterno AS funcionario,
                                                    a.defi_especbreve,
                                                    b.depe_nombre AS dependencia,
                                                    TRIM(COALESCE(c.pdla_cargofuncional,'-- SIN CARGO --'))||CASE WHEN c.pdla_encargado=1 THEN '(E)' ELSE '' END AS cargo,
                                                    COALESCE(u.usua_set_certificado,0) AS  set_certificado
                                             FROM gestdoc.despachos_firmas a 
                                             LEFT JOIN catalogos.dependencia b ON a.depe_id = b.depe_id
                                             LEFT JOIN personal.persona_datos_laborales c ON c.pdla_id = a.pdla_id
                                             LEFT JOIN personal.persona d ON d.pers_id = c.pers_id 
                                             LEFT JOIN (SELECT DISTINCT a.usua_set_certificado,
                                                               b.pers_id
                                                          FROM admin.usuario a 
                                                          LEFT JOIN personal.persona_datos_laborales b on  a.pdla_id=b.pdla_id)  u ON c.pers_id = u.pers_id 
                                             WHERE a.desp_id = $id 
                                             AND a.defi_tipo IN (1,4) /*OJO, SOLO FIRMAS (PRINCIPAL Y OTROS JEFES)*/
                                             ORDER BY a.defi_tipo,
                                                      a.defi_id
                                         ";
                            $rsFirmas = new query($conn, $sqlFirmas);
                            while ($rsFirmas->getrow()){
                                
                                if ($tabl_tipodespacho==141 && SIS_FIRMA_PERSONAL_ELECTRONICA==1){//DOCUMENTO PERSONAL
                                    $pdf->Cell(0, 0, 'Firmado Electrónicamente por:', '', 1, 'C');
                                    
                                }elseif( $rsFirmas->field('set_certificado') ==1 ){
                                    $pdf->Cell(0, 0, 'Firmado Digitalmente por:', '', 1, 'C');
                                }
                                
                                $pdf->Cell(0, 0, $rsFirmas->field('defi_especbreve') . ' ' . $rsFirmas->field('funcionario'), '', 1, 'C');
                                $pdf->Cell(0, 0, $rsFirmas->field('cargo'), '', 1, 'C');
                                $pdf->Cell(0, 0, $rsFirmas->field('dependencia'), '', 1, 'C');
                                $pdf->Cell(0, 0, '', '', 1, 'C');
                                
                            }


                            /* Obtengo los funcionarios que han dado el visto bueno */
                            $sqlVistos = " 
                                SELECT d.pers_nombres || ' ' || d.pers_apellpaterno || ' ' || d.pers_apellmaterno||' ('||COALESCE(c.pdla_cargofuncional,'--SIN CARGO--')||'-'||b.depe_nombrecorto||') ' AS funcionario_dio_visto,
                                       a.defi_especbreve,
                                       b.depe_nombrecorto
                                FROM gestdoc.despachos_firmas a 
                                LEFT JOIN catalogos.dependencia b ON a.depe_id = b.depe_id
                                LEFT JOIN personal.persona_datos_laborales c ON c.pdla_id = a.pdla_id
                                LEFT JOIN personal.persona d ON d.pers_id = c.pers_id 
                                WHERE desp_id = $id 
                                AND defi_tipo IN (2,3) /*OJO, SOLO FIRMAS SECUNDARIAS*/
                                ORDER BY defi_tipo,defi_id
                            ";

                            
                            if($rsDoc->field('depe_id_proyectado')){
                                $pdf->SetFont('helvetica', '', 7);
                                $pdf->Cell(0, 0, 'Pp.'.$rsDoc->field('poyectado_por'), '', 1, 'L');
                                $pdf->SetFont('helvetica', '', 10);
                            }
                            
                            /* creo el recordset */
                            $rsVistos = new query($conn, $sqlVistos);
                            if ($rsVistos->numrows() > 0) {
                                $pdf->SetFont('helvetica', '', 8);
                                $pdf->Cell(0, 0, 'VoBo de:', '', 1, 'L');        

                                while ($rsVistos->getrow()){

                                    $pdf->Cell(0, 0, ' - ' . $rsVistos->field('defi_especbreve') . ' ' . $rsVistos->field('funcionario_dio_visto'), '', 1, 'L');

                                }
                                $pdf->Ln(3);
                                $pdf->SetFont('helvetica', '', 10);
                            }

//                            $id_validacion=$id.'.'.$id_rand;
//
//                            $nameFile_QR="../../../../docs/reportes/qr$id.png";
//                            $url=PATH_PORT."valida/gestdoc/index.php";
//                            $QR="$url?id_validacion=$id_validacion";
//
//                            $pdf->Cell(0, 0,"Su autenticidad e integridad pueden ser contrastada a través de la siguiente dirección web: ", '', 1, 'C');
//                            $pdf->Cell(0, 0,"$url", '', 1, 'C');
//
//                            QRcode::png($QR, $nameFile_QR, QR_ECLEVEL_Q,2,3); //nombre de archivo, (Error Correction Level): nivel Q, 2 cm, 3 en total, dejando 1 cm Zona de silencio mínimo (Quiet Zone) 
//                            $pdf->Cell(0, 0, 'Código de Validación Digital-CVD: '.$id_validacion, '', 1, 'C');
//                            $pdf->Image("$nameFile_QR", $pdf->GetX()+75, $pdf->GetY(), 25, 25, '', '', '', false, 20);

                        }

                }else{
                        $html = $rsDoc->field('desp_contenido');
                        $pdf->writeHTML($html, true, 0, true, true);
                }    

                // reset pointer to the last page
                $pdf->lastPage();

                // ---------------------------------------------------------


                //Close and output PDF document

                 $pdf->Output($nameFile, $tipo_vista);  /* genera y veo el archivo en el navegador */

            }
    }
    else
    {
        $nameFile = $_SERVER['DOCUMENT_ROOT']."/docs/gestdoc/".SIS_EMPRESA_RUC."/$periodo/$id/$name_file";
        header("Content-type: application/pdf");
        header("Content-Disposition: inline; filename=$name_file");
        readfile($nameFile);

    }
}else{  //SI SE UN DOCUMENTO EXTERNO

        /*SI PIDE MARCAR COMO FINAL*/
        if($marcar_final==1){
            /*actualiza el nombre del arhivo y se cierra el documento*/
            $sSql="UPDATE gestdoc.despachos
                            SET desp_file_firmado_fregistro=NOW(),
                                desp_estado=2 /*CERRADO*/
                     WHERE desp_id=$id;
                    ";

            // Ejecuto el string
            $conn->execute($sSql);
            $error=$conn->error();

            if($error){ 
                //alert($error);
                $jsondata["success"] = false;
                $jsondata["mensaje"] = $error;
            }else{
                $jsondata["success"] = true;
                $jsondata["mensaje"] = "";
            }        
        }
    
}
/* Fin: Imprimo Documento */

$conn->close();

if($marcar_final==1){
    header('Content-Type: application/json');
    echo json_encode($jsondata, JSON_FORCE_OBJECT);
}                