<?php
//--------------------------------------------------------------------
// PHP GenReporte Class
//--------------------------------------------------------------------
require('genpdf.inc');

class GenReporte extends PDF
{
	var $NameFile = "";	// Nombre del archivo PDF que se generar�.  Este nombre puede incluir la ruta del archivo.  Ejm.  "../../docs/reporte.pdf"
        var $tmpFiles = array();
	var $WidthTotalCampos = 0;	// Aqu� se guardar� el ancho total de los campos acumulados del cuerpo del reporte
	var $nyIniciaDetalle=0;	 // Para guardar la posici�n "Y" donde se inicia la impresi�n del detalle del reporte
	
	var $HeadLeft = "";	// Aqu� se guardar� lo que deseamos que se imprima en la parte izquierda de la cabecera
	var $footLeft = "";	// Aqu� se guardar� lo que deseamos que se imprima en la parte izquierda del pie de p�gina
	var $footRight = ""; // Aqu� se guardar� lo que deseamos que se imprima en la parte derecha del pie de p�gina	
	var $nCuentaRegistro; // Contador general de registros de todo el reporte

	var $CampoGrupo1; // Campo que determina el Grupo1.  
	var $DatoGrupo1; // Variable donde se guardar� el Dato del campo del Grupo 1 
	var $nCtaRegistroGrupo1; // Contador de registros del grupo 1.  
	var $Grupo1NewPage; // Variable que determina si cada grupo se imprime en una nueva p�gina.  Para activarlo asignar el valor 1  	

	var $CampoGrupo2; // Campo que determina el Grupo2.  
	var $DatoGrupo2; // Variable donde se guardar� el Dato del campo del Grupo 2 
	var $nCtaRegistroGrupo2; // Contador de registros del grupo 2.  
	var $Grupo2NewPage; // Variable que determina si cada grupo se imprime en una nueva p�gina.  Para activarlo asignar el valor 1  	

	var $CampoGrupo3; // Campo que determina el Grupo3.  
	var $DatoGrupo3; // Variable donde se guardar� el Dato del campo del Grupo 3 
	var $nCtaRegistroGrupo3; // Contador de registros del grupo 3.  
	var $Grupo3NewPage; // Variable que determina si cada grupo se imprime en una nueva p�gina.  Para activarlo asignar el valor 1  	

	var $CampoGrupo4; // Campo que determina el Grupo4.  
	var $DatoGrupo4; // Variable donde se guardar� el Dato del campo del Grupo 4 
	var $nCtaRegistroGrupo4; // Contador de registros del grupo 4.  

	var $CampoGrupo5; // Campo que determina el Grupo5.  
	var $DatoGrupo5; // Variable donde se guardar� el Dato del campo del Grupo 5 
	var $nCtaRegistroGrupo5; // Contador de registros del grupo 5.  

	var $nlnCabecera=3; //numero de lineas en blanco q imprimir� despues de escribir el sub titulo
	var $PosYIniciaTitulo=15; // Posici�n de Y para que inicie la impresi�n del t�tulo 
	
	var $funcion=array(''); //inicializa el array de operaciones arimeticas
	
	function __construct($orientation='P',$unit='mm',$format='A4')
	{
		parent::__construct($orientation,$unit,$format);	// Llamo a la funci�n constructora de la clase Padre.
		$this->_FuncJavascript(); // Cargo en memoria las funciones javascript que voy a necesitar.
		
		// Configuro datos de Cabecera y pie de p�gina
		$this->footLeft=SIS_PIELEFT_REPORTE;	
		$this->HeadLeft=SIS_EMPRESA;
		$this->footRight=SIS_VERSION;	
	}
	
	function addField($name, $xoff, $yoff, $width)
	{
		$xoff=($xoff==99999)?$this->WidthTotalCampos:$xoff;
		parent::addField($name, $xoff, $yoff, $width);
		if(substr($name,0,1)=='C' || substr($name,0,1)=='N'){ // Solo considero WidthTotalCampos a los campos que van en el Detalle (empiezan con C)
			$this->WidthTotalCampos=$this->WidthTotalCampos + $width;

			//inicia el array de calculo
			$this->functions['CONT_GRUPO1'][$name]=0;	
			$this->functions['CONT_GRUPO2'][$name]=0;				
			$this->functions['CONT_GRUPO3'][$name]=0;				
			$this->functions['CONT_GRUPO4'][$name]=0;				
			$this->functions['CONT_GRUPO5'][$name]=0;
			$this->functions['CONT_TOTAL'][$name]=0;	
			
			if(substr($name,0,1)=='N'){
				$this->functions['SUMA_GRUPO1'][$name]=0;	
				$this->functions['SUMA_GRUPO2'][$name]=0;	
				$this->functions['SUMA_GRUPO3'][$name]=0;					
				$this->functions['SUMA_GRUPO4'][$name]=0;
				$this->functions['SUMA_GRUPO5'][$name]=0;
				$this->functions['SUMA_TOTAL'][$name]=0;	
			}
						
		}

	}

        function Header()
	{
		$this->blockPosX = $this->GetX();

		$this->SetFontHeadFooter(); // Seteo el font para head y footer
		// Imprimo la parte izquierda de la cabecera
		$this->SetXY(10,5);
                $image = "../../img/logo_".strtolower(SIS_EMPRESA_SIGLAS).".jpg";
                if(file($image)){
                    $with_img=13;
                    $this->Image($image, 10,2, 15, $with_img,'JPG', '');
                    $WidthHead=(($this->maxWidth-20-$with_img)/2); 
                    $this->SetXY(12+$with_img,5);
                }else{
                    $WidthHead=($this->maxWidth-20)/2; // Determino el ancho que deben tener las l�neas que van en el Head y en el footer, el 20 es porque dejo un espacio de 10 a cada lado de la l�nea                    
                }
                                
		$this->Cell($WidthHead,3,$this->HeadLeft,'B',0,'L');
		// Imprimo el n�mero de p�gina de cuantas p�ginas tenga el reporte
		$this->AliasNbPages(); // Para poder obtener el n�mero de p�ginas
		$this->Cell($WidthHead,3,'Pagina '.$this->PageNo().' de {nb}','B',1,'R');

                
		// Imprimo la fecha
		$this->SetFont('Arial', '', 7);
		$this->Ln(1);	
		$this->SetX(($this->maxWidth-45));
		$this->Cell(20,3,'Usuario:',0,0,'R');
		$this->Cell(15,3,$_SESSION["sis_username"],0,1,'L');		
                
		$this->SetX(($this->maxWidth-45));
		$this->Cell(20,3,'Fecha:',0,0,'R');
		$this->Cell(15,3,date("d/m/Y"),0,1,'L');		

		// Imprimo la hora
		$this->SetX(($this->maxWidth-45));
		$this->Cell(20,3,'Hora:',0,0,'R');
		$this->Cell(15,3,date("H:i:s"),0,1,'L');		

		// Ancho de la l�nea borde de cualquier celda
		$this->SetLineWidth(0.3);
		//Colors of frame, background and text
		$this->SetFillColor(230,230,230);
		$this->SetTextColor(0,0,0);

		$this->title(); // Imprimo el t�tulo

		$this->SetFont('Arial', 'B', 7);
		$this->Cabecera(); // Imprimo la cabecera (los t�tulos de los campos)

		$this->nyIniciaDetalle = $this->GetY()+$this->lasth; // Guardo la posici�n "Y" donde empiezo a imprimir el detalle
		
		// Save the Y offset.  This is where the first block following the header will appear.
		$this->maxYoff = $this->GetY();
		$this->_resetFontDef();
	}
                
	function Headerx()
	{
		$this->blockPosX = $this->GetX();

		$this->SetFontHeadFooter(); // Seteo el font para head y footer

		// Imprimo la parte izquierda de la cabecera
		$this->SetXY(10,5);
		$WidthHead=($this->maxWidth-20)/2; // Determino el ancho que deben tener las l�neas que van en el Head y en el footer, el 20 es porque dejo un espacio de 10 a cada lado de la l�nea
		$this->Cell($WidthHead,3,$this->HeadLeft,'B',0,'L');
		// Imprimo el n�mero de p�gina de cuantas p�ginas tenga el reporte
		$this->AliasNbPages(); // Para poder obtener el n�mero de p�ginas
		$this->Cell($WidthHead,3,'Pagina '.$this->PageNo().' de {nb}','B',1,'R');

		// Imprimo la fecha
		$this->SetFont('Arial', '', 7);
		$this->Ln(1);	
		$this->SetX(($this->maxWidth-45));
		$this->Cell(20,3,'Usuario:',0,0,'R');
		$this->Cell(15,3,$_SESSION["sis_username"],0,1,'L');		
                
		$this->SetX(($this->maxWidth-45));
		$this->Cell(20,3,'Fecha:',0,0,'R');
		$this->Cell(15,3,date("d/m/Y"),0,1,'L');		

		// Imprimo la hora
		$this->SetX(($this->maxWidth-45));
		$this->Cell(20,3,'Hora:',0,0,'R');
		$this->Cell(15,3,date("H:i:s"),0,1,'L');		

		// Ancho de la l�nea borde de cualquier celda
		$this->SetLineWidth(0.3);
		//Colors of frame, background and text
		$this->SetFillColor(230,230,230);
		$this->SetTextColor(0,0,0);

		$this->title(); // Imprimo el t�tulo

		$this->SetFont('Arial', 'B', 7);
		$this->Cabecera(); // Imprimo la cabecera (los t�tulos de los campos)

		$this->nyIniciaDetalle = $this->GetY()+$this->lasth; // Guardo la posici�n "Y" donde empiezo a imprimir el detalle
		
		// Save the Y offset.  This is where the first block following the header will appear.
		$this->maxYoff = $this->GetY();
		$this->_resetFontDef();
	}

	function title(){

		if($this->font_defs['header'][0] == "") {
			$this->_setFontDefs();
		}
		$font_type = $this->font_defs['header'][0];
		$font_weight = $this->font_defs['header'][1];
		$font_size = $this->font_defs['header'][2];
	
		$extra_width = 20;

		//Calculate width of title and position
		$this->SetFont($font_type, $font_weight, $font_size);
		$w = $this->GetStringWidth($this->title)+ $extra_width;

		$this->SetFont($font_type, $font_weight, $font_size-3);
		if(($this->GetStringWidth($this->subTitle)+ $extra_width) > $w)
			$w = $this->GetStringWidth($this->subTitle)+ $extra_width;

		//Title
		if($w>$this->maxWidth)		
			$w=$this->maxWidth;
		
		$this->SetY($this->PosYIniciaTitulo);
		$this->SetX(($this->maxWidth-$w)/2);
		$this->SetFont($font_type, $font_weight, $font_size-2);
		$this->Cell($w,$this->lineHeight,$this->title,0,1,'C');

		if($this->subTitle){
			// Subt�tulo	
			$this->SetX(($this->maxWidth-$w)/2);
			$this->SetFont($font_type, $font_weight, $font_size-4);;
			$this->Cell($w,$this->lineHeight-2,$this->subTitle,0,1,'C');
		}			

		$this->Ln($this->nlnCabecera);	
	}

	function Footer()
	{
		//Position at 1.5 cm from bottom
//		$this->SetY(-15);
//		$this->SetX($this->blockPosX);
//		$this->SetXY(10,-15);
		$this->SetXY(10,-5);
		$this->SetFontHeadFooter();

		$WidthHead=($this->maxWidth-20)/2; // Determino el ancho que deben tener las l�neas que van en el Head y en el footer

		$this->Cell($WidthHead,4,$this->footLeft,'T',0,'L');
		$this->Cell($WidthHead,4,$this->footRight,'T',1,'R');		
		
		$this->_resetFontDef();
	}

	function SetFontHeadFooter()
	{
		if($this->font_defs['footer'][0] == "") {
			$this->_setFontDefs();
		}
		$font_type = $this->font_defs['footer'][0];
		$font_weight = $this->font_defs['footer'][1];
		$font_size = $this->font_defs['footer'][2];
		
		$this->SetFont($font_type, $font_weight, $font_size);
		$this->SetTextColor(128);
	}

	function _setFontDefs()
	{
		if($this->font_defs['default'][0] == "")
			$this->font_defs['default'] = array('Arial', '', 8);
			
		if($this->font_defs['header'][0] == "")
			$this->font_defs['header'] = array('Arial', 'B', 10);
			
		if($this->font_defs['footer'][0] == "")
			$this->font_defs['footer'] = array('Arial', 'I', 7);
	}

	function _FuncJavascript()
	{
		?>
		<script language="JavaScript">
		//	funcion para abrir la ventana popup para mostrar el reporte	
		function AbreVentana(sURL) {
			var w=800, h=600;
			venrepo=window.open(sURL,'rptSalida', "status=yes,resizable=yes,toolbar=no,scrollbars=yes,top=0,left=0,width=" + w + ",height=" + h, 1 );
			venrepo.focus();
		}
		</script><?php
	}
	
	function Cabecera(){
	// Aqu� se imprimir� todos los campos que van como cabecera, esto se configura en cada reporte
	}

	function printField($valor, $field_name="", $font_name="", $border="", $align="", $flotante=false, $fill=0, $link='')
	{
		/*
 		$valor       --> valor a ser mostrado
		$field_name --> Nombre del campo donde se mostrar� $valor
		$font_name  --> Fuente con la que se mostrar� $valor 
		$border     --> Borde del campo, usar los mismos valores de $pdf->cell()
		$align      --> Alineaci�n del texto en el campo, usar los mismos valores de $pdf->cell()
		*/

		// Set offsets and width based on first field entry, or
		// given field entry.
		if($field_name == "") {
			$field_xoff = $this->field_defs[0][0];
			$field_yoff = $this->field_defs[0][1];
			$field_width = $this->field_defs[0][2];
		} else {
			$field_xoff = $this->field_defs[$field_name][0];
			$field_yoff = $this->field_defs[$field_name][1];
			$field_width = $this->field_defs[$field_name][2];
		}
		
		// Set font information based on first font entry, or given
		// font entry.
		$this->_useFontDef($font_name);

		// Set the field position.			
		$this->SetXY($this->blockPosX + $field_xoff, $this->blockPosY + $field_yoff);

		// Shorten the field however much it needs
		if($flotante){
			$outText = $valor;
			$twidth = $this->GetStringWidth($outText);			
			if($twidth>$field_width){
				$this->MultiCell($field_width, $this->lineHeight, $outText, $border, $align, $fill);
				$this->SetY($this->GetY()-$this->lineHeight); /* Regreso una fila, para que no se quede una fila en blanco al terminar de imprimir la celda, ya que el m�todo Multicell deja esta fila en blanco */
			}else
				$this->Cell($field_width, $this->lineHeight, $outText, $border, 0, $align, $fill, $link);
		}else{
			$outText = $this->_cutField($valor, $field_width);
			$this->Cell($field_width, $this->lineHeight, $outText, $border, 0, $align, $fill, $link);
		}

		//realiza operaciones de c�lculo
		if(substr($field_name,0,1)=='C' || substr($field_name,0,1)=='N'){ // Solo considero WidthTotalCampos a los campos que van en el Detalle (empiezan con C)
			$this->functions['CONT_GRUPO1'][$field_name]++;	
			$this->functions['CONT_GRUPO2'][$field_name]++;
			$this->functions['CONT_GRUPO3'][$field_name]++;
			$this->functions['CONT_GRUPO4'][$field_name]++;			
			$this->functions['CONT_GRUPO5'][$field_name]++;			
			$this->functions['CONT_TOTAL'][$field_name]++;	
			
			if(substr($field_name,0,1)=='N'){
				$this->functions['SUMA_GRUPO1'][$field_name]+=str_replace(',','',$valor);
				$this->functions['SUMA_GRUPO2'][$field_name]+=str_replace(',','',$valor);
				$this->functions['SUMA_GRUPO3'][$field_name]+=str_replace(',','',$valor);
				$this->functions['SUMA_GRUPO4'][$field_name]+=str_replace(',','',$valor);				
				$this->functions['SUMA_GRUPO5'][$field_name]+=str_replace(',','',$valor);								
				$this->functions['SUMA_TOTAL'][$field_name]+=str_replace(',','',$valor);	
			}
						
		}

		// Make sure to save the maximum y offset for this page.  This tells us 
		// how long the block is.  We use this to determine where to start the
		// next block.
		$t_yoff = $this->GetY();
		if($this->maxYoff < $t_yoff)
			$this->maxYoff = $t_yoff;
			
	}

        function getNameFile(){
            return ($this->NameFile);
        }
        
	function VerPdf($abreVentana=true)
	{
            if(getSession("sis_username")){
		/* Genero nombre aleatorio */
		if(!$this->NameFile) /* Si no existe nombre del archivo a generar */
			$this->NameFile='../../docs/reportes/rpt'.rand(1000,1000000).'.pdf';

		/* Muestro el PDF final */
		//$pdf->Output(); // Esto abre directamente el archivo generado pero tiene problemas a veces con el EXPLORER, eso s�, funciona bien en Firefox
		$this->Output($this->NameFile);
		//header("Location: usuario.pdf");
                if($abreVentana){
                    AbreVentana($this->NameFile,'');
                }
            }else {
                alert("Usted no esta autorizado...");
            }                  
	}
	
        
        
        function EnviarComprobantePdf($op,$id,$clie_id,$email,$file_xml,$numero)
	{
		global $conn;
                //$this->Output($this->NameFile);
                
		if($op==1){
                    $this->Output($this->NameFile,'FI');
                    //AbreVentana($this->NameFile,'');
                }elseif(inlist($op,'2,3')){
                    $this->Output($this->NameFile);
                    $content1 = file_get_contents($this->NameFile); // e.g. ("attachment/abc.pdf")
                    $attachment1 = new Zend_Mime_Part($content1);
                    $attachment1->type = 'application/pdf';
                    $attachment1->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                    $attachment1->encoding = Zend_Mime::ENCODING_BASE64;
                    $attachment1->filename = "$numero.pdf"; // name of file
                    
                    $content2 = file_get_contents($file_xml); // e.g. ("attachment/abc.pdf")
                    $attachment2 = new Zend_Mime_Part($content2);
                    $attachment2->type = 'text/plain';
                    
                    $attachment2->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                    $attachment2->encoding = Zend_Mime::ENCODING_BASE64;
                    $attachment2->filename = "$numero.xml"; // name of file
                    
                    $file_cdr=str_replace(SIS_EMPRESA_RUC.'-','R-'.SIS_EMPRESA_RUC.'-',$file_xml);
                    
                    if( !file_exists($file_cdr) ){
                        $file_cdr=str_replace('XML','xml',$file_cdr);
                    }
                    
                    if(file_exists($file_cdr)){
                        $content3 = file_get_contents($file_cdr); // e.g. ("attachment/abc.pdf")
                        $attachment3 = new Zend_Mime_Part($content3);
                        $attachment3->type = 'text/plain';

                        $attachment3->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                        $attachment3->encoding = Zend_Mime::ENCODING_BASE64;
                        $attachment3->filename = "R-$numero.xml"; // name of file
                    }
                    
                                         
                    $email_gmail=trim(SIS_EMAIL_GMAIL);
                    $pass_email_gmail=trim(SIS_PASS_EMAIL_GMAIL);
                    $email_servidor=trim(SIS_EMAIL_SERVIDOR);
                    $email_from=trim(SIS_EFACT_EMAIL_FROM);
                                                
                    $posGmail = stripos($email_gmail, 'gmail');    
                    
                    if($posGmail === false) { /* Si no se está usando el Gmail */

                        $config = array('auth' => 'login',
                                'username' => $email_gmail,
                                'password' => $pass_email_gmail,'ssl' => 'tls','port' => 587);
                        $mailTransport = new Zend_Mail_Transport_Smtp($email_servidor,$config);
                    } else {

                        $config = array('auth' => 'login',
                                'username' => $email_gmail,
                                // in case of Gmail username also acts as mandatory value of FROM header
                                'password' => $pass_email_gmail,'ssl' => 'tls','port' => 587);
                        $mailTransport = new Zend_Mail_Transport_Smtp('smtp.gmail.com',$config);
                    }
                     
                    Zend_Mail::setDefaultTransport($mailTransport);


                    $sqlCliente=new cliente_SQLlista();
                    $sqlCliente->whereID($clie_id);
                    $sqlCliente->setDatos();
                    $cliente=$sqlCliente->field('clie_razsocial');
                            
                    if($email){
                            $mail = new Zend_Mail();
         
                            $mail->setBodyHtml(utf8_decode("<b>Señor(es): ").$cliente." </b>		
                                                            <br>Cumplimos con remitir el Comprobante Electr&oacute;nico por el Producto/Servicio adquirido
                                                            <br><br>
                                                            <b>Enviado Desde:</b> Sistema de Informaci&oacute;n-".SIS_EMPRESA.
                                                             "<br><b>IMPORTANTE:</b> NO responda a este Mensaje")
                    
                                ->setFrom($email_from,'Ventas '.SIS_EMPRESA_SIGLAS)
                                ->setSubject('REMITO: '.$this->title.' '.$numero)
                                ->addTo($email, 'Cliente')
                                ->addAttachment($attachment1)
                                ->addAttachment($attachment2);
                            
                            //envia archivo de respuesta SUNAT
                            if ( file_exists($file_cdr) && $_SERVER['SERVER_NAME']=='clinicausat.mytienda.page' ){
                                $mail->addAttachment($attachment3);
                            }
                            
                            try {
                                $ahora=date('d/m/Y h:i:s');                                    
                                $mail->send();
                                $mensaje = "Envio exitoso de Comprobante de Pago al email: <b>$email</b>";
                                
                                $usua_id=getSession("sis_userid");
                                
                                if($op==2){//FACTURA,BOLETA,NOTA DE CREDITO
                                    
                                    $sql="INSERT INTO siscore.recaudaciones_envios
                                                (reca_id,
                                                reen_email,
                                                reen_fregistro,
                                                usua_id)
                                        VALUES ($id,
                                                '$email',
                                                '$ahora'::TIMESTAMP,    
                                                $usua_id)";
                                    
                                }elseif($op==3){//GUIA DE REMISION
                                    $sql="INSERT INTO siscore.guia_remision_envios
                                                (gure_id,
                                                gren_email,
                                                gren_fregistro,
                                                usua_id)
                                        VALUES ($id,
                                                '$email',
                                                '$ahora'::TIMESTAMP,    
                                                $usua_id)";                                    
                                }
                                $conn->execute($sql);
                                $error=$conn->error();
                                if($error){
                                    $mensaje = $error;
                                    //echo $error;
                                }
                                $ok=1;

                            } catch (Exception $e) {
                                    //$mensaje = 'Error al enviar el Correo...Por favor, Comunicarse con el Area de Soporte Informático  <br>
                                    //                    Su mensaje de error es: <br>
                                    //                    '.$e->getMessage();
                                    $mensaje =  substr($e->getMessage(),0,100);
                                    
                                    //$mensaje = $email_servidor.' + '.$email_gmail.' - '.$pass_email_gmail;
                                    $ok=0;
                                    //alert($mensaje);
                            }
                            //alert($mensaje);
                            //echo $mensaje;
                    }
                    
                    echo "<script>parent.content.refreshModalScreen($id,$ok,'$mensaje');
                          </script>";
                }elseif($op==3){

                }
                
	}
        
	function beginBlock($title="", $font_name="")
	{
		if(($this->maxYoff + $this->blockHeight) > $this->maxHeight)
		{	
			$this->AddPage();
			$this->maxYoff = $this->GetY();
		}
		$this->blockPosY = $this->maxYoff;

		$this->SetXY($this->blockPosX, $this->blockPosY);
		$this->Ln();

		if($title != "") {
			$this->_useFontDef($font_name);
			$this->Cell(0,$this->lineHeight,$title,0,0,'L',0);
			$this->Ln();
		}			
		
		$this->blockPosY = $this->GetY();
		$this->maxYoff = $this->blockPosY;
	}

	
	
	
	
	
	function GeneraPdf(){
		/* Seteo el PDF */
		$this->SeteoPdf();

		/*** Detalle del Reporte ***/ 
		$this->ImprimeDetalle();

		/*** Summary del Reporte ***/ 
		$this->Summary();
	}

	function IniciaFila(){
		global $rs;
		if($this->DatoGrupo1!=$rs->field($this->CampoGrupo1)){ // Si no estoy en el mismo grupo
			/* Reinicio las variables de grupo */ 
			$this->ReiniciaVariables(1); // Llamo a funci�n para reinicar variables al cambiar de grupo.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite				
			/* Cambio de grupo */			
			$this->CambiaGrupo(1); // Imprimo los campos que necesito en el t�tulo del grupo 1
		}
		if($this->DatoGrupo2!=$rs->field($this->CampoGrupo2)){ // Si no estoy en el mismo grupo
			/* Reinicio las variables de grupo */ 
			$this->ReiniciaVariables(2); // Llamo a funci�n para reinicar variables al cambiar de grupo.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite				
			/* Cambio de grupo */			
			$this->CambiaGrupo(2); // Imprimo los campos que necesito en el t�tulo del grupo 1
		}
		if($this->DatoGrupo3!=$rs->field($this->CampoGrupo3)){ // Si no estoy en el mismo grupo
			/* Reinicio las variables de grupo */ 
			$this->ReiniciaVariables(3); // Llamo a funci�n para reinicar variables al cambiar de grupo.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite				
			/* Cambio de grupo */			
			$this->CambiaGrupo(3); // Imprimo los campos que necesito en el t�tulo del grupo 1
		}
		if($this->DatoGrupo4!=$rs->field($this->CampoGrupo4)){ // Si no estoy en el mismo grupo
			/* Reinicio las variables de grupo */ 
			$this->ReiniciaVariables(4); // Llamo a funci�n para reinicar variables al cambiar de grupo.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite				
			/* Cambio de grupo */			
			$this->CambiaGrupo(4); // Imprimo los campos que necesito en el t�tulo del grupo 1
		}
		if($this->DatoGrupo5!=$rs->field($this->CampoGrupo5)){ // Si no estoy en el mismo grupo
			/* Reinicio las variables de grupo */ 
			$this->ReiniciaVariables(5); // Llamo a funci�n para reinicar variables al cambiar de grupo.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite				
			/* Cambio de grupo */			
			$this->CambiaGrupo(5); // Imprimo los campos que necesito en el t�tulo del grupo 1
		}

	}


	function ReiniciaVariables($Grupo){
		// Aqu� se reinician las variables por defecto creadas por la clase 
		switch ($Grupo)
		{
			case 1: // Grupo 1 
				$this->nCtaRegistroGrupo1=0; // Al cambiar el grupo inicializo el contador correspondiente del grupo

				$this->nCtaRegistroGrupo2=0; // Tambi�n debo inicializar el contador del grupo 2 
				$this->nCtaRegistroGrupo3=0; // Tambi�n debo inicializar el contador del grupo 3 
				$this->nCtaRegistroGrupo4=0; // Tambi�n debo inicializar el contador del grupo 3 
				$this->ReiniciaVariables(2); // Tambi�n Llamo a la funci�n para reinicar variables del grupo 2.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite				
				$this->ReiniciaVariables(3); // Tambi�n Llamo a la funci�n para reinicar variables del grupo 3.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite				
				$this->ReiniciaVariables(4); // Tambi�n Llamo a la funci�n para reinicar variables del grupo 4.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite
				$this->ReiniciaVariables(5); // Tambi�n Llamo a la funci�n para reinicar variables del grupo 5.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite

				//reinicia el array arimetico de grupos
				foreach($this->functions['CONT_GRUPO1'] as $key => $value ) 	
						$this->functions['CONT_GRUPO1'][$key]=0;
		
				if(is_array($this->functions['SUMA_GRUPO1']))
					foreach($this->functions['SUMA_GRUPO1'] as $key => $value ) 	
							$this->functions['SUMA_GRUPO1'][$key]=0;

				break;
			case 2: // Grupo 2
				$this->nCtaRegistroGrupo2=0; // Al cambiar el grupo inicializo el contador correspondiente del grupo

				$this->nCtaRegistroGrupo3=0; // Tambi�n debo inicializar el contador del grupo 3 
				$this->ReiniciaVariables(3); // Tambi�n Llamo a la funci�n para reinicar variables del grupo 3.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite				

				$this->nCtaRegistroGrupo4=0; // Tambi�n debo inicializar el contador del grupo 4 
				$this->ReiniciaVariables(4); // Tambi�n Llamo a la funci�n para reinicar variables del grupo 4.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite

				$this->nCtaRegistroGrupo5=0; // Tambi�n debo inicializar el contador del grupo 5 
				$this->ReiniciaVariables(5); // Tambi�n Llamo a la funci�n para reinicar variables del grupo 5.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite

				//reinicia el array arimetico de grupos
				foreach($this->functions['CONT_GRUPO2'] as $key => $value ) 	
						$this->functions['CONT_GRUPO2'][$key]=0;
		
				if(is_array($this->functions['SUMA_GRUPO2']))
					foreach($this->functions['SUMA_GRUPO2'] as $key => $value ) 	
							$this->functions['SUMA_GRUPO2'][$key]=0;

				break;
			case 3: // Grupo 3
				$this->nCtaRegistroGrupo3=0; // Al cambiar el grupo inicializo el contador correspondiente del grupo
				
				$this->nCtaRegistroGrupo4=0; // Tambi�n debo inicializar el contador del grupo 4 
				$this->ReiniciaVariables(4); // Tambi�n Llamo a la funci�n para reinicar variables del grupo 4.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite

				$this->nCtaRegistroGrupo5=0; // Tambi�n debo inicializar el contador del grupo 5 
				$this->ReiniciaVariables(5); // Tambi�n Llamo a la funci�n para reinicar variables del grupo 5.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite

				//reinicia el array arimetico de grupos
				foreach($this->functions['CONT_GRUPO3'] as $key => $value ) 	
						$this->functions['CONT_GRUPO3'][$key]=0;
		
				if(is_array($this->functions['SUMA_GRUPO3']))
					foreach($this->functions['SUMA_GRUPO3'] as $key => $value )
							$this->functions['SUMA_GRUPO3'][$key]=0;

				break;
			case 4: // Grupo 4
				$this->nCtaRegistroGrupo4=0; // Al cambiar el grupo inicializo el contador correspondiente del grupo
				
				$this->nCtaRegistroGrupo5=0; // Tambi�n debo inicializar el contador del grupo 5 
				$this->ReiniciaVariables(5); // Tambi�n Llamo a la funci�n para reinicar variables del grupo 5.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite

				//reinicia el array arimetico de grupos
				foreach($this->functions['CONT_GRUPO4'] as $key => $value ) 	
						$this->functions['CONT_GRUPO4'][$key]=0;
		
				if(is_array($this->functions['SUMA_GRUPO4']))
					foreach($this->functions['SUMA_GRUPO4'] as $key => $value )
							$this->functions['SUMA_GRUPO4'][$key]=0;

				break;
			case 5: // Grupo 5
				$this->nCtaRegistroGrupo5=0; // Al cambiar el grupo inicializo el contador correspondiente del grupo
				
				//reinicia el array arimetico de grupos
				foreach($this->functions['CONT_GRUPO5'] as $key => $value ) 	
						$this->functions['CONT_GRUPO5'][$key]=0;
		
				if(is_array($this->functions['SUMA_GRUPO5']))
					foreach($this->functions['SUMA_GRUPO5'] as $key => $value )
							$this->functions['SUMA_GRUPO5'][$key]=0;

				break;

		}
	}

	function CambiaGrupo($Grupo){
		global $rs;
		if(($this->maxHeight-$this->blockPosY)<20 and $this->nCuentaRegistro>0){ // Para evitar que se imprima el t�tulo del grupo solo al final de la p�gina y sus hijos o registros en la siguiente hoja
			$this->AddPage();
			$this->maxYoff = $this->GetY();
		}

		switch ($Grupo)
		{
			case 1: // Grupo 1
				if ($this->Grupo1NewPage){   // Verifico si cada grupo debe iniciar en una nueva p�gina
					if($this->maxYoff>$this->nyIniciaDetalle){ // Solo a�ade una p�gina nueva si no est� al inicio de imprimir el detalle ya en una p�gina nueva
							$this->AddPage();
							$this->maxYoff = $this->GetY();
					}
				}

				$this->TituloGrupo1(); // Imprimo los campos que necesito en el t�tulo del grupo 1
				$this->DatoGrupo1=$rs->field($this->CampoGrupo1); // Guardo en variable el dato del grupo para poder compararlo en el registro siguiente

				if($this->CampoGrupo2){ // Si tengo un Grupo 2 
					$this->TituloGrupo2(); // Imprimo los campos que necesito en el t�tulo del grupo 2
					$this->DatoGrupo2=$rs->field($this->CampoGrupo2); // Guardo en variable el dato del grupo para poder compararlo en el registro siguiente	
				}

				if($this->CampoGrupo3){ // Si tengo un Grupo 3 
					$this->TituloGrupo3(); // Imprimo los campos que necesito en el t�tulo del grupo 3
					$this->DatoGrupo3=$rs->field($this->CampoGrupo3); // Guardo en variable el dato del grupo para poder compararlo en el registro siguiente	
				}

				if($this->CampoGrupo4){ // Si tengo un Grupo 4 
					$this->TituloGrupo4(); // Imprimo los campos que necesito en el t�tulo del grupo 4
					$this->DatoGrupo4=$rs->field($this->CampoGrupo4); // Guardo en variable el dato del grupo para poder compararlo en el registro siguiente	
				}

				if($this->CampoGrupo5){ // Si tengo un Grupo 5 
					$this->TituloGrupo5(); // Imprimo los campos que necesito en el t�tulo del grupo 5
					$this->DatoGrupo5=$rs->field($this->CampoGrupo5); // Guardo en variable el dato del grupo para poder compararlo en el registro siguiente	
				}

				break;
			case 2: // Grupo 2
				if ($this->Grupo2NewPage){   // Verifico si cada grupo debe iniciar en una nueva p�gina
					if($this->maxYoff>$this->nyIniciaDetalle){ // Solo a�ade una p�gina nueva si no est� al inicio de imprimir el detalle ya en una p�gina nueva
						$this->AddPage();
						$this->maxYoff = $this->GetY();
					}
				}

				$this->TituloGrupo2(); // Imprimo los campos que necesito en el t�tulo del grupo 2
				$this->DatoGrupo2=$rs->field($this->CampoGrupo2); // Guardo en variable el dato del grupo para poder compararlo en el registro siguiente

				if($this->CampoGrupo3){ // Si tengo un Grupo 3 
					$this->TituloGrupo3(); // Imprimo los campos que necesito en el t�tulo del grupo 3
					$this->DatoGrupo3=$rs->field($this->CampoGrupo3); // Guardo en variable el dato del grupo para poder compararlo en el registro siguiente	
				}

				if($this->CampoGrupo4){ // Si tengo un Grupo 4 
					$this->TituloGrupo4(); // Imprimo los campos que necesito en el t�tulo del grupo 4
					$this->DatoGrupo4=$rs->field($this->CampoGrupo4); // Guardo en variable el dato del grupo para poder compararlo en el registro siguiente	
				}

				if($this->CampoGrupo5){ // Si tengo un Grupo 5 
					$this->TituloGrupo5(); // Imprimo los campos que necesito en el t�tulo del grupo 5
					$this->DatoGrupo5=$rs->field($this->CampoGrupo5); // Guardo en variable el dato del grupo para poder compararlo en el registro siguiente	
				}

				break;
			case 3: // Grupo 3
				if ($this->Grupo3NewPage){   // Verifico si cada grupo debe iniciar en una nueva p�gina
					if($this->maxYoff>$this->nyIniciaDetalle){ // Solo a�ade una p�gina nueva si no est� al inicio de imprimir el detalle ya en una p�gina nueva
							$this->AddPage();
							$this->maxYoff = $this->GetY();
					}
				}

				$this->TituloGrupo3(); // Imprimo los campos que necesito en el t�tulo del grupo 3
				$this->DatoGrupo3=$rs->field($this->CampoGrupo3); // Guardo en variable el dato del grupo para poder compararlo en el registro siguiente	

				if($this->CampoGrupo4){ // Si tengo un Grupo 4 
					$this->TituloGrupo4(); // Imprimo los campos que necesito en el t�tulo del grupo 4
					$this->DatoGrupo4=$rs->field($this->CampoGrupo4); // Guardo en variable el dato del grupo para poder compararlo en el registro siguiente	
				}

				if($this->CampoGrupo5){ // Si tengo un Grupo 5 
					$this->TituloGrupo5(); // Imprimo los campos que necesito en el t�tulo del grupo 5
					$this->DatoGrupo5=$rs->field($this->CampoGrupo5); // Guardo en variable el dato del grupo para poder compararlo en el registro siguiente	
				}

				break;
			case 4: // Grupo 4
				$this->TituloGrupo4(); // Imprimo los campos que necesito en el t�tulo del grupo 4
				$this->DatoGrupo4=$rs->field($this->CampoGrupo4); // Guardo en variable el dato del grupo para poder compararlo en el registro siguiente	

				if($this->CampoGrupo5){ // Si tengo un Grupo 5 
					$this->TituloGrupo5(); // Imprimo los campos que necesito en el t�tulo del grupo 5
					$this->DatoGrupo5=$rs->field($this->CampoGrupo5); // Guardo en variable el dato del grupo para poder compararlo en el registro siguiente	
				}

				break;
			case 5: // Grupo 5
				$this->TituloGrupo5(); // Imprimo los campos que necesito en el t�tulo del grupo 4
				$this->DatoGrupo5=$rs->field($this->CampoGrupo5); // Guardo en variable el dato del grupo para poder compararlo en el registro siguiente	

				break;
				
		}

	}

	function TituloGrupo1(){
	// Aqu� se imprimir� todos los campos que van en el t�tulo del grupo 1, esto se configura en cada reporte
	}
	function TituloGrupo2(){
	// Aqu� se imprimir� todos los campos que van en el t�tulo del grupo 2, esto se configura en cada reporte
	}
	function TituloGrupo3(){
	// Aqu� se imprimir� todos los campos que van en el t�tulo del grupo 3, esto se configura en cada reporte
	}
	function TituloGrupo4(){
	// Aqu� se imprimir� todos los campos que van en el t�tulo del grupo 4, esto se configura en cada reporte
	}
	function TituloGrupo5(){
	// Aqu� se imprimir� todos los campos que van en el t�tulo del grupo 4, esto se configura en cada reporte
	}

	function CierraFila(){
		global $rs;
		if($this->CampoGrupo1){ // Si tengo grupos en el reporte
			$rs->getrow(); // obtengo los datos del registro siguiente

			if($this->DatoGrupo1!=$rs->field($this->CampoGrupo1)){ // Si cambia de grupo
				$this->CierraGrupo(1);
			}elseif($this->DatoGrupo2!=$rs->field($this->CampoGrupo2)){
				$this->CierraGrupo(2);
			}elseif($this->DatoGrupo3!=$rs->field($this->CampoGrupo3)){
				$this->CierraGrupo(3);
			}elseif($this->DatoGrupo4!=$rs->field($this->CampoGrupo4)){
				$this->CierraGrupo(4);
			}elseif($this->DatoGrupo5!=$rs->field($this->CampoGrupo5)){
				$this->CierraGrupo(5);
			}
			
			$rs->skiprow($rs->curr_row-1); // Regreso al registro donde estaba, donde dej� seteado el propio objeto $rs
		}
	}

	function CierraGrupo($Grupo){
		global $rs;

		switch ($Grupo)
		{
			case 1: // Grupo 1 
				if($this->CampoGrupo5){ // Si tengo un Grupo 5 
					$this->PieGrupo5(); // Imprimo los campos que necesito en el pie del grupo 5
				}

				if($this->CampoGrupo4){ // Si tengo un Grupo 4 
					$this->PieGrupo4(); // Imprimo los campos que necesito en el pie del grupo 4
				}

				if($this->CampoGrupo3){ // Si tengo un Grupo 3 
					$this->PieGrupo3(); // Imprimo los campos que necesito en el pie del grupo 3
				}

				if($this->CampoGrupo2){ // Si tengo un Grupo 2 
					$this->PieGrupo2(); // Imprimo los campos que necesito en el pie del grupo 2
				}

				$this->PieGrupo1(); // Imprimo los campos que necesito en el pie del grupo 1

				break;

			case 2: // Grupo 2
				if($this->CampoGrupo5){ // Si tengo un Grupo 5 
					$this->PieGrupo5(); // Imprimo los campos que necesito en el pie del grupo 5
				}

				if($this->CampoGrupo4){ // Si tengo un Grupo 4 
					$this->PieGrupo4(); // Imprimo los campos que necesito en el pie del grupo 4
				}

				if($this->CampoGrupo3){ // Si tengo un Grupo 3 
					$this->PieGrupo3(); // Imprimo los campos que necesito en el pie del grupo 3
				}

				$this->PieGrupo2(); // Imprimo los campos que necesito en el pie del grupo 2

				break;

			case 3: // Grupo 3
				if($this->CampoGrupo5){ // Si tengo un Grupo 5 
					$this->PieGrupo5(); // Imprimo los campos que necesito en el pie del grupo 5
				}

				if($this->CampoGrupo4){ // Si tengo un Grupo 4 
					$this->PieGrupo4(); // Imprimo los campos que necesito en el pie del grupo 4
				}

				$this->PieGrupo3(); // Imprimo los campos que necesito en el pie del grupo 3

				break;

			case 4: // Grupo 4
				if($this->CampoGrupo5){ // Si tengo un Grupo 5 
					$this->PieGrupo5(); // Imprimo los campos que necesito en el pie del grupo 5
				}

				$this->PieGrupo4(); // Imprimo los campos que necesito en el pie del grupo 4

				break;

			case 5: // Grupo 5

				$this->PieGrupo5(); // Imprimo los campos que necesito en el pie del grupo 5

				break;

		}
	}

	function PieGrupo1(){
		// Imprimo una l�nea al final del grupo
//		$this->Line($this->blockPosX, $this->blockPosY+$this->lasth,$this->blockPosX+$this->WidthTotalCampos, $this->blockPosY+$this->lasth); // Imprimo L�nea al final de cada grupo
		// A partir de aqu� se imprimir� todos los campos que van en el pie del grupo 1, esto se configura en cada reporte
	}

	function PieGrupo2(){
	// Aqu� se imprimir� todos los campos que van en el pie del grupo 2, esto se configura en cada reporte
	}

	function PieGrupo3(){
	// Aqu� se imprimir� todos los campos que van en el pie del grupo 3, esto se configura en cada reporte
	}

	function PieGrupo4(){
	// Aqu� se imprimir� todos los campos que van en el pie del grupo 4, esto se configura en cada reporte
	}

	function PieGrupo5(){
	// Aqu� se imprimir� todos los campos que van en el pie del grupo 5, esto se configura en cada reporte
	}

	function ImprimeDetalle(){
		// Aqu� recorro todos los registros del recordset
		global $rs;
		while ($rs->getrow()){
			/* Inicio Fila */
			$this->IniciaFila();	

			/* C�lculo de variables */
			$this->nCuentaRegistro=$this->nCuentaRegistro+1; // Contador de registros general de todo el reporte
			$this->nCtaRegistroGrupo1=$this->nCtaRegistroGrupo1+1; // Contador de registros	del Grupo 1	
			$this->nCtaRegistroGrupo2=$this->nCtaRegistroGrupo2+1; // Contador de registros	del Grupo 2				
			$this->nCtaRegistroGrupo3=$this->nCtaRegistroGrupo3+1; // Contador de registros	del Grupo 3	
			$this->nCtaRegistroGrupo4=$this->nCtaRegistroGrupo4+1; // Contador de registros	del Grupo 4				
			$this->nCtaRegistroGrupo5=$this->nCtaRegistroGrupo5+1; // Contador de registros	del Grupo 5				

			/* Imprimo el detalle */ 
			$this->beginBlock(); // Creo un espacio en blanco para imprimir los campos del mismo grupo
			$this->Detalle(); // Imprimo los campos que van en la franja DETALLE
			
			/* Cierro la fila */
			$this->CierraFila();		
		}
	}

	function Detalle(){
	// Aqu� se imprimir� todos los campos que van en la franja DETALLE del reporte, esto se configura en cada reporte
	}

	function Summary(){
		// A partir de aqu� se imprimir� todos los campos que van en la franja SUMMARY del reporte, esto se configura en cada reporte
	}
        

function ImagePNG($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='', $isMask=false, $maskImg=0)
{
    //Put an image on the page
    if(!isset($this->images[$file]))
    {
        //First use of this image, get info
        if($type=='')
        {
            $pos=strrpos($file,'.');
            if(!$pos)
                $this->Error('Image file has no extension and no type was specified: '.$file);
            $type=substr($file,$pos+1);
        }
        $type=strtolower($type);
        if($type=='png'){
            $info=$this->_parsepng($file);
            if($info=='alpha')
                return $this->ImagePngWithAlpha($file,$x,$y,$w,$h,$link);
        }
        else
        {
            if($type=='jpeg')
                $type='jpg';
            $mtd='_parse'.$type;
            if(!method_exists($this,$mtd))
                $this->Error('Unsupported image type: '.$type);
            $info=$this->$mtd($file);
        }
        if($isMask){
            if(in_array($file,$this->tmpFiles))
                $info['cs']='DeviceGray'; //hack necessary as GD can't produce gray scale images
            if($info['cs']!='DeviceGray')
                $this->Error('Mask must be a gray scale image');
            if($this->PDFVersion<'1.4')
                $this->PDFVersion='1.4';
        }
        $info['i']=count($this->images)+1;
        if($maskImg>0)
            $info['masked'] = $maskImg;
        $this->images[$file]=$info;
    }
    else
        $info=$this->images[$file];
    //Automatic width and height calculation if needed
    if($w==0 && $h==0)
    {
        //Put image at 72 dpi
        $w=$info['w']/$this->k;
        $h=$info['h']/$this->k;
    }
    elseif($w==0)
        $w=$h*$info['w']/$info['h'];
    elseif($h==0)
        $h=$w*$info['h']/$info['w'];
    //Flowing mode
    if($y===null)
    {
        if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
        {
            //Automatic page break
            $x2=$this->x;
            $this->AddPage($this->CurOrientation,$this->CurPageFormat);
            $this->x=$x2;
        }
        $y=$this->y;
        $this->y+=$h;
    }
    if($x===null)
        $x=$this->x;
    if(!$isMask)
        $this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q',$w*$this->k,$h*$this->k,$x*$this->k,($this->h-($y+$h))*$this->k,$info['i']));
    if($link)
        $this->Link($x,$y,$w,$h,$link);
    return $info['i'];
}

// needs GD 2.x extension
// pixel-wise operation, not very fast
function ImagePngWithAlpha($file,$x,$y,$w=0,$h=0,$link='')
{
    $tmp_alpha = tempnam('.', 'mska');
    $this->tmpFiles[] = $tmp_alpha;
    $tmp_plain = tempnam('.', 'mskp');
    $this->tmpFiles[] = $tmp_plain;

    list($wpx, $hpx) = getimagesize($file);
    $img = imagecreatefrompng($file);
    $alpha_img = imagecreate( $wpx, $hpx );

    // generate gray scale pallete
    for($c=0;$c<256;$c++)
        ImageColorAllocate($alpha_img, $c, $c, $c);

    // extract alpha channel
    $xpx=0;
    while ($xpx<$wpx){
        $ypx = 0;
        while ($ypx<$hpx){
            $color_index = imagecolorat($img, $xpx, $ypx);
            $col = imagecolorsforindex($img, $color_index);
            imagesetpixel($alpha_img, $xpx, $ypx, $this->_gamma( (127-$col['alpha'])*255/127) );
            ++$ypx;
        }
        ++$xpx;
    }

    imagepng($alpha_img, $tmp_alpha);
    imagedestroy($alpha_img);

    // extract image without alpha channel
    $plain_img = imagecreatetruecolor ( $wpx, $hpx );
    imagecopy($plain_img, $img, 0, 0, 0, 0, $wpx, $hpx );
    imagepng($plain_img, $tmp_plain);
    imagedestroy($plain_img);
    
    //first embed mask image (w, h, x, will be ignored)
    $maskImg = $this->ImagePNG($tmp_alpha, 0,0,0,0, 'PNG', '', true); 
    
    //embed image, masked with previously embedded mask
    $this->ImagePNG($tmp_plain,$x,$y,$w,$h,'PNG',$link, false, $maskImg);
}

function Close()
{
    parent::Close();
    // clean up tmp files
    foreach($this->tmpFiles as $tmp)
        @unlink($tmp);
}

/*******************************************************************************
*                                                                              *
*                               Private methods                                *
*                                                                              *
*******************************************************************************/
function _putimages()
{
    $filter=($this->compress) ? '/Filter /FlateDecode ' : '';
    reset($this->images);
    foreach ($this->images as $file => $info)
    {
        $this->_newobj();
        $this->images[$file]['n']=$this->n;
        $this->_out('<</Type /XObject');
        $this->_out('/Subtype /Image');
        $this->_out('/Width '.$info['w']);
        $this->_out('/Height '.$info['h']);

        if(isset($info['masked']))
            $this->_out('/SMask '.($this->n-1).' 0 R');

        if($info['cs']=='Indexed')
            $this->_out('/ColorSpace [/Indexed /DeviceRGB '.(strlen($info['pal'])/3-1).' '.($this->n+1).' 0 R]');
        else
        {
            $this->_out('/ColorSpace /'.$info['cs']);
            if($info['cs']=='DeviceCMYK')
                $this->_out('/Decode [1 0 1 0 1 0 1 0]');
        }
        $this->_out('/BitsPerComponent '.$info['bpc']);
        if(isset($info['f']))
            $this->_out('/Filter /'.$info['f']);
        if(isset($info['parms']))
            $this->_out($info['parms']);
        if(isset($info['trns']) && is_array($info['trns']))
        {
            $trns='';
            for($i=0;$i<count($info['trns']);$i++)
                $trns.=$info['trns'][$i].' '.$info['trns'][$i].' ';
            $this->_out('/Mask ['.$trns.']');
        }
        $this->_out('/Length '.strlen($info['data']).'>>');
        $this->_putstream($info['data']);
        unset($this->images[$file]['data']);
        $this->_out('endobj');
        //Palette
        if($info['cs']=='Indexed')
        {
            $this->_newobj();
            $pal=($this->compress) ? gzcompress($info['pal']) : $info['pal'];
            $this->_out('<<'.$filter.'/Length '.strlen($pal).'>>');
            $this->_putstream($pal);
            $this->_out('endobj');
        }
    }
}

// GD seems to use a different gamma, this method is used to correct it again
function _gamma($v){
    return pow ($v/255, 2.2) * 255;
}

// this method overriding the original version is only needed to make the Image method support PNGs with alpha channels.
// if you only use the ImagePngWithAlpha method for such PNGs, you can remove it from this script.
function _parsepng($file)
{
    //Extract info from a PNG file
    $f=fopen($file,'rb');
    if(!$f)
        $this->Error('Can\'t open image file: '.$file);
    //Check signature
    if($this->_readstream($f,8)!=chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10))
        $this->Error('Not a PNG file: '.$file);
    //Read header chunk
    $this->_readstream($f,4);
    if($this->_readstream($f,4)!='IHDR')
        $this->Error('Incorrect PNG file: '.$file);
    $w=$this->_readint($f);
    $h=$this->_readint($f);
    $bpc=ord($this->_readstream($f,1));
    if($bpc>8)
        $this->Error('16-bit depth not supported: '.$file);
    $ct=ord($this->_readstream($f,1));
    if($ct==0)
        $colspace='DeviceGray';
    elseif($ct==2)
        $colspace='DeviceRGB';
    elseif($ct==3)
        $colspace='Indexed';
    else {
        fclose($f);      // the only changes are 
        return 'alpha';  // made in those 2 lines
    }
    if(ord($this->_readstream($f,1))!=0)
        $this->Error('Unknown compression method: '.$file);
    if(ord($this->_readstream($f,1))!=0)
        $this->Error('Unknown filter method: '.$file);
    if(ord($this->_readstream($f,1))!=0)
        $this->Error('Interlacing not supported: '.$file);
    $this->_readstream($f,4);
    $parms='/DecodeParms <</Predictor 15 /Colors '.($ct==2 ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w.'>>';
    //Scan chunks looking for palette, transparency and image data
    $pal='';
    $trns='';
    $data='';
    do
    {
        $n=$this->_readint($f);
        $type=$this->_readstream($f,4);
        if($type=='PLTE')
        {
            //Read palette
            $pal=$this->_readstream($f,$n);
            $this->_readstream($f,4);
        }
        elseif($type=='tRNS')
        {
            //Read transparency info
            $t=$this->_readstream($f,$n);
            if($ct==0)
                $trns=array(ord(substr($t,1,1)));
            elseif($ct==2)
                $trns=array(ord(substr($t,1,1)), ord(substr($t,3,1)), ord(substr($t,5,1)));
            else
            {
                $pos=strpos($t,chr(0));
                if($pos!==false)
                    $trns=array($pos);
            }
            $this->_readstream($f,4);
        }
        elseif($type=='IDAT')
        {
            //Read image data block
            $data.=$this->_readstream($f,$n);
            $this->_readstream($f,4);
        }
        elseif($type=='IEND')
            break;
        else
            $this->_readstream($f,$n+4);
    }
    while($n);
    if($colspace=='Indexed' && empty($pal))
        $this->Error('Missing palette in '.$file);
    fclose($f);
    return array('w'=>$w, 'h'=>$h, 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'FlateDecode', 'parms'=>$parms, 'pal'=>$pal, 'trns'=>$trns, 'data'=>$data);
}        

protected function _readstream($f, $n)
{
	// Read n bytes from stream
	$res = '';
	while($n>0 && !feof($f))
	{
		$s = fread($f,$n);
		if($s===false)
			$this->Error('Error while reading stream');
		$n -= strlen($s);
		$res .= $s;
	}
	if($n>0)
		$this->Error('Unexpected end of stream');
	return $res;
}
protected function _readint($f)
{
	// Read a 4-byte integer from stream
	$a = unpack('Ni',$this->_readstream($f,4));
	return $a['i'];
}

}