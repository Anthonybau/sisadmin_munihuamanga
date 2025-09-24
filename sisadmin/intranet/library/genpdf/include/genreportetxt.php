<?php
//--------------------------------------------------------------------
// PHP GenReportetxt Class
//--------------------------------------------------------------------
//$this->maxHeight=                       $this->FilasxHoja
//$this->maxYoff=$this->blockPosY=        $this->NumFilaActual
// $this->nyIniciaDetalle      =          $this->FilaIniciaDetalle

class GenReportetxt 
{
	var $Txt;	// Variable donde se guardar� todo el texto a ser impreso
	var $NumFilasxHoja;	// N�mero de filas que se imprimira por hoja
	var $NumFilaActual;	// N�mero de fila actual en que se encuentra la impresi�n
	var $nameFile='reporte.txt'; // Nombre del archivo txt que ser� generado	

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

	function __construct($NumFilasxHoja=30)
	{
		// Configuro datos de Cabecera y pie de p�gina
		$this->NumFilasxHoja=$NumFilasxHoja;	
		$this->footLeft=SIS_PIELEFT_REPORTE;	
		$this->HeadLeft=SIS_EMPRESA;
		$this->footRight=SIS_VERSION;	
		$this->NumFilaActual=1;
	}
	

	function Header()
	{
		$this->title(); // Imprimo el t�tulo

		$this->Cabecera(); // Imprimo la cabecera (los t�tulos de los campos)

		$this->FilaIniciaDetalle=$this->NumFilaActual; // Guardo la fila donde inicia el detalle

	}

	function Cabecera(){
	// Aqu� se imprimir� todos los campos que van como cabecera, esto se configura en cada reporte
	}

	function PrintField($valor,$nLongitud,$cAlign='L',$nRetorno=0,$cRelleno=" "){
		$nDifLong=$nLongitud-strlen($valor);
	
		if($cAlign=='L'){ // Si alineaci�n es a la izquierda
			if($nDifLong>=0){
				$VarRet=$valor.str_repeat($cRelleno,$nDifLong);
			}else{ // Si es negativo
				$VarRet=substr($valor,0,$nDifLong);	
			}
		}elseif($cAlign=='R'){ // Si alineaci�n es a la derecha 
			if($nDifLong>=0){
				$VarRet=str_repeat($cRelleno,$nDifLong).$valor;
			}else{ // Si es negativo
				$VarRet=substr($valor,0,$nDifLong);	
			}
		}

		/* Verifico si debo agregar retorno de carro */
		if($nRetorno){ 
			$VarRet .= "\r\n";
			$this->NumFilaActual++; /* Sumo 1 a la fila actual */
		}
			
		$this->Txt .= $VarRet;

	}

	function SeteaFont($caracter){
		$this->Txt .= $caracter;
	}

	function ImprimirTxt()
	{
		//exec("TYPE $this->nameFile >PRN");
            exec("lpr -#2 -sP $this->nameFile");
	}

	function EliminarTxt()
	{
		unlink($this->nameFile);		
	}
	
	function setNameFile($nameFile)
	{
		$this->nameFile=$nameFile;	
	}
	
	function GeneraTxt(){
		/* Seteo el TXT */
		$this->SeteoTxt();

		/*** Detalle del Reporte ***/ 
		$this->ImprimeDetalle();

		/*** Summary del Reporte ***/ 
		$this->Summary();
		
		/* Genero el archivo plano */
//		$this->nameFile=rand(1000,1000000).'.txt';
//		$this->nameFile='reporte.txt';
		$f1 = fopen($this->nameFile,"w+"); // Abro el archivo texto
		fwrite($f1,$this->Txt);  // Escribo en el archivo texto
		fclose($f1);  // Cierro el archvo texo 
                exec("chmod 777 $this->nameFile");                
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

				break;
			case 2: // Grupo 2
				$this->nCtaRegistroGrupo2=0; // Al cambiar el grupo inicializo el contador correspondiente del grupo

				$this->nCtaRegistroGrupo3=0; // Tambi�n debo inicializar el contador del grupo 3 
				$this->ReiniciaVariables(3); // Tambi�n Llamo a la funci�n para reinicar variables del grupo 3.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite				

				$this->nCtaRegistroGrupo4=0; // Tambi�n debo inicializar el contador del grupo 4 
				$this->ReiniciaVariables(4); // Tambi�n Llamo a la funci�n para reinicar variables del grupo 4.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite

				$this->nCtaRegistroGrupo5=0; // Tambi�n debo inicializar el contador del grupo 5 
				$this->ReiniciaVariables(5); // Tambi�n Llamo a la funci�n para reinicar variables del grupo 5.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite

				break;
			case 3: // Grupo 3
				$this->nCtaRegistroGrupo3=0; // Al cambiar el grupo inicializo el contador correspondiente del grupo
				
				$this->nCtaRegistroGrupo4=0; // Tambi�n debo inicializar el contador del grupo 4 
				$this->ReiniciaVariables(4); // Tambi�n Llamo a la funci�n para reinicar variables del grupo 4.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite

				$this->nCtaRegistroGrupo5=0; // Tambi�n debo inicializar el contador del grupo 5 
				$this->ReiniciaVariables(5); // Tambi�n Llamo a la funci�n para reinicar variables del grupo 5.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite

				break;
			case 4: // Grupo 4
				$this->nCtaRegistroGrupo4=0; // Al cambiar el grupo inicializo el contador correspondiente del grupo
				
				$this->nCtaRegistroGrupo5=0; // Tambi�n debo inicializar el contador del grupo 5 
				$this->ReiniciaVariables(5); // Tambi�n Llamo a la funci�n para reinicar variables del grupo 5.  Esta funci�n debe manejarse en cada reporte seg�n lo que se necesite

				break;
			case 5: // Grupo 5
				$this->nCtaRegistroGrupo5=0; // Al cambiar el grupo inicializo el contador correspondiente del grupo
				
				break;
		}
	}

	function AddPage()
	{
		/* Imprimo el n�mero de retornos de carro para que pase a la siguiente p�gina */
		while ($this->NumFilaActual<=$this->NumFilasxHoja){
			$this->Txt .= "\r\n";
			$this->NumFilaActual++;
		}
		
		/* Seteo el n�mero de fila actual en 1 para que inicie en la siguiente p�gina*/
		$this->NumFilaActual=1;
		
		/* Imprimo el Header */
		$this->Header();		
	}

	function Eject()
	{
		/* Imprimo el n�mero de retornos de carro para que pase a la siguiente p�gina */
		while ($this->NumFilaActual<=$this->NumFilasxHoja){
			$this->Txt .= "\r\n";
			$this->NumFilaActual++;
		}
	}

	function CambiaGrupo($Grupo){
		global $rs;

		if(($this->NumFilasxHoja-$this->NumFilaActual)<5 and $this->nCuentaRegistro>0){ // Para evitar que se imprima el t�tulo del grupo solo al final de la p�gina y sus hijos o registros en la siguiente hoja
			$this->AddPage();
		}

		switch ($Grupo)
		{
			case 1: // Grupo 1
				if ($this->Grupo1NewPage){   // Verifico si cada grupo debe iniciar en una nueva p�gina
					if($this->NumFilaActual>$this->FilaIniciaDetalle){ // Solo a�ade una p�gina nueva si no est� al inicio de imprimir el detalle ya en una p�gina nueva
							$this->AddPage();
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
					if($this->NumFilaActual>$this->FilaIniciaDetalle){ // Solo a�ade una p�gina nueva si no est� al inicio de imprimir el detalle ya en una p�gina nueva
						$this->AddPage();
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
					if($this->NumFilaActual>$this->FilaIniciaDetalle){ // Solo a�ade una p�gina nueva si no est� al inicio de imprimir el detalle ya en una p�gina nueva
						$this->AddPage();
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

	function Ln($NumSaltos=1){
		$i = 1;
		while ($i<=$NumSaltos){
			$this->Txt .= "\r\n";
			$i++;
			$this->NumFilaActual++; /* Sumo 1 a la fila actual */

			if($this->NumFilaActual >= $this->NumFilasxHoja)
				$this->AddPage();

		}
	}

	function Salto($NumFila){
		while ($this->NumFilaActual<=$NumFila){
			$this->Txt .= "\r\n";
			$this->NumFilaActual++; /* Sumo 1 a la fila actual */
		}
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

} // End class
?>
