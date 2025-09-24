<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class cliente extends entidad {
	
	function __construct($id='',$title=''){
		$this->setTable='catalogos.cliente'; //nombre de la tabla
		$this->setKey='clie_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

		/* Destinos luego de actulizar, agregar o eliminar un registro */
		$this->destinoUpdate = "catalogosCliente_buscar.php";
		$this->destinoInsert = "catalogosCliente_buscar.php";
		$this->destinoDelete = "catalogosCliente_buscar.php";		

		/* Datos que se retorna cuando la clase es cargada en una AvanzLookup */
		if(getParam('tr_tabl_tipocliente')==1){//person juridica
			$this->return_txt=strtoupper(getParam('Sr_clie_razsocial')); /* Texo que se devuelve */
                        $this->return_val=getParam('Sx_clie_codigo'); /* Valor que se devuelve */
                }
		else{
			$this->return_txt=strtoupper(getParam('Sr_clie_apellidos').' '.getParam('Sr_clie_nombres')); /* Texo que se devuelve */
                        $this->return_val=getParam('Sx_clie_codigo'); /* Valor que se devuelve */
                        if(!$this->return_val){
                            $this->return_val=getParam('Sx_clie_dni'); /* Valor que se devuelve */
                        }
                }					
		
		
	}

	function guardar(){
		global $conn,$param;
		$nomeCampoForm=getParam("nomeCampoForm");
                $nomeDireccion=getParam("fieldExtra")=='direccion'?1:0;
                
                $clear=getParam("clear");
                
                if( $nomeDireccion==1 ){
                    $direccion=getParam("Sx_clie_direccion");
                }

                $nomeDireccionInquilino=getParam("fieldExtra")=='direccion_inquilino'?1:0;
                if( $nomeDireccionInquilino==1 ){
                    $direccion_inquilino=getParam("Sx_clie_direccion");
                }                
	
		$param->removePar('pg'); /* Remuevo el par�metro p�gina */
		$destinoUpdate=$this->destinoUpdate.$param->buildPars(true);
		$destinoInsert=$this->destinoInsert.$param->buildPars(true);
		
		// objeto para instanciar la clase sql
		$sql = new UpdateSQL();
				
		$sql->setTable($this->setTable);
		$sql->setKey($this->setKey, $this->valueKey, $this->typeKey);
	
		include("../guardar_tipoDato.php");
	
		if ($this->valueKey) { // modificacion
			$sql->setAction("UPDATE");
		}else{
			$sql->setAction("INSERT");
			$sql->addField('usua_id', getSession("sis_userid"), "Number");							
		}
	
		/* Aqu� puedo agregar otros campos a la sentencia SQL */
		$this->addField($sql);

		/* Ejecuto el SQL */
                $sqlCommand=$sql->getSQL();
		$padre_id=$conn->execute("$sqlCommand  RETURNING $this->setKey");                
		//$conn->execute($sql->getSQL());
		$error=$conn->error();
		if($error){ 
			alert($error);	/* Muestro el error y detengo la ejecuci�n */
		}else{
			/*  muestra mensaje noticia del la base de datos, pero no detiene la ejecucion	*/
			$notice=$conn->notice();
			if($notice){
				alert($notice,0);
                        }
		}
		
		/* */
			//alert($nomeCampoForm);
                        if($nomeCampoForm){//si se llama desde una ventana emergente (avanzlookup) para seleccionar un valor
				/* Datos que se retornan desde un (avanzlookup) */
				$return_val=$this->return_val; /* Valor que se devuelve */
				$return_txt=$this->return_txt; /* Texo que se devuelve */
                                
                                if($nomeDireccion==1){
                                    $direccion=getParam("Sx_clie_direccion");                                    
                                }
                                
                                if($nomeDireccionInquilino==1){
                                    $direccion_inquilino=getParam("Sx_clie_direccion");                                    
                                }
                                //alert($clear);
                                //alert($nomeCampoForm);
				/* Comandos Javascript */
                                
                                if ($clear==3){//si es llamado desde siscoreCajaIngresos_edicion.php
                                    echo "<script language=\"javascript\">
						parent.opener.parent.parent.content.document.forms[0]._Dummy$nomeCampoForm.value = '$return_txt';
						parent.opener.parent.parent.content.document.forms[0].$nomeCampoForm.value = '$return_val';
						parent.opener.parent.parent.content.document.forms[0].tx_clie_id.value = ".$padre_id.";
                                                    
                                                if( $nomeDireccion==1 ){
                                                    parent.opener.parent.parent.content.document.forms[0].Sx_pedido_direccion.value = '$direccion';
                                                }
                                                
                                                if( $nomeDireccionInquilino==1 ){
                                                    parent.opener.parent.parent.content.document.forms[0].Sr_cont_cliente_direccion.value = '$direccion_inquilino';
                                                }

						parent.parent.close();
					</script>";                                    
                                }else{
                                    echo "<script language=\"javascript\">
						parent.opener.parent.parent.content.document.forms[0]._Dummy$nomeCampoForm.value = '$return_txt';
						parent.opener.parent.parent.content.document.forms[0].$nomeCampoForm.value = '$return_val';
						parent.opener.parent.parent.content.document.forms[0].__Change$nomeCampoForm.value = 1;
						parent.opener.parent.parent.content.document.forms[0].tr_clie_id.value = ".$padre_id.";
                                                    
                                                if( $nomeDireccion==1 ){
                                                    parent.opener.parent.parent.content.document.forms[0].Sx_pedido_direccion.value = '$direccion';
                                                }
                                                
                                                if( $nomeDireccionInquilino==1 ){
                                                    parent.opener.parent.parent.content.document.forms[0].Sr_cont_cliente_direccion.value = '$direccion_inquilino';
                                                }

						parent.parent.close();
					</script>";
                                }
			}else{ /* Si se llama desde una p�gina normal */
                                if ($this->valueKey) {// modificaci�n

                                        if(stripos($destinoUpdate,"javascript")){ // Si es un Script javascript el que quiero que se ejecute luego de insertar un registro
                                            echo $destinoUpdate;
                                            exit;				
                                        }
                                        if(strpos($destinoInsert, "?")>0){
                                            $destinoUpdate.="&id=$padre_id";
                                        }else{
                                            $destinoUpdate.="?id=$padre_id";
                                        }
                                        redirect($destinoUpdate,"content");							
                                }else{ /* Inserci�n */
                                        if(stripos($destinoInsert,"javascript")){ // Si es un Script javascript el que quiero que se ejecute luego de insertar un registro
                                                echo $destinoInsert;
                                                exit;					
                                        }
                                        /*a�ado el id del registro ingresado*/
                                        if(strpos($destinoInsert, "?")>0){
                                            $destinoInsert.="&id=$padre_id";
                                        }else{
                                            $destinoInsert.="?id=$padre_id";
                                        }
                                        redirect($destinoInsert,"content");							
                                }
                            }
                    }


 function eliminar(){
		global $conn,$param;

		$destinoDelete=$this->destinoDelete.$param->buildPars(true);		
		
		/* captura y prepara la lista de registros a ser eliminados */ 
		$arLista_elimina = getParam("sel");
		if (is_array($arLista_elimina)) {
		 $lista_elimina = implode(",",$arLista_elimina);
		}
		if(!$lista_elimina) return;

		if(strtolower($this->typeKey)=='string'){
			/* debido a que el campo clave es char */
			$lista_elimina=str_replace(",","','",$lista_elimina);
		}

		/* Sql a ejecutar */
		$sqlCommand ="DELETE FROM $this->setTable WHERE $this->setKey";
		$sqlCommand.=" IN (".iif(strtolower($this->typeKey),"==","string","'","").$lista_elimina.iif(strtolower($this->typeKey),"==","string","'","").") " ;
                
                if ( getSession("sis_userid")==1 || getSession("sis_level")>2 ){   
                }else{
                    $sqlCommand.=" AND usua_id=".getSession("sis_userid");
                }
                $sqlCommand.=" RETURNING $this->setKey ";

		/* Ejecuto la sentencia */
                //alert($sqlCommand);
		$padre_id=$conn->execute($sqlCommand);
		$error=$conn->error();		
		if($error) alert($error);
		else{
			redirect($destinoDelete,"content");		
		}
	}                   
 
	//incorporo otros campos
	function addField(&$sql){
		if (getParam("hx_clie_estado")){
                    $sql->addField("clie_estado", '1', "Number");
                }else{
                    $sql->addField("clie_estado", '0', "Number");
                }
                if (getParam("hx_clie_agente_retencion")){
                    $sql->addField("clie_agente_retencion", '1', "Number");
                }else{
                    $sql->addField("clie_agente_retencion", '0', "Number");
                }                
		$sql->addField("clie_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
		$sql->addField("clie_actualusua", getSession("sis_userid"), "String");				

	}

	function getSql(){
		$sql=new cliente_SQLlista();
		$sql->whereID($this->id);
		return($sql->getSQL());
	}

	function jsDevolver($nomeCampoForm){
			if($nomeCampoForm)
			//PARA EJECUTAR UNA FUNCION DEPENDNIENTE DEL VALOR ELEGIDO EN LA VENTANA DE BUSQUEDA
			//SE REQUIERE  DE self.parent.NOMBRE_DE_FUNCION_JAVASCRIPT(paramenteres) ejem:self.parent.xajax_cargaGrupo(valor,1)
			return ("function update(valor,ruc, descricao, numForm) {
					parent.parent.content.document.forms[numForm]._Dummy$nomeCampoForm.value = descricao;
					parent.parent.content.document.forms[numForm].$nomeCampoForm.value = ruc;
					parent.parent.content.document.forms[numForm].__Change_$nomeCampoForm.value = 1;
					parent.parent.content.document.forms[numForm].tr_clie_id.value = valor;					
					self.parent.tb_remove();
					}");
	}
	
	function buscar($op,$formData='',$arrayParam='',$pg=1,$Nameobj='')
	{
		global $conn,$param,$nomeCampoForm,$clear;
		$objResponse = new xajaxResponse();
		
		$arrayParam=decodeArray($arrayParam);

		$paramFunction= new manUrlv1();
		$paramFunction->setUrlExternal($arrayParam);

		$cadena=is_array($formData)?trim(strtoupper($formData['Sbusc_cadena'])):$formData;		
		
		if(!$cadena && $op==2) $cadena=getSession("cadSearch");
		
		$busEmpty=$paramFunction->getValuePar('busEmpty');
		$colSearch=$paramFunction->getValuePar('colSearch');
		$numForm=$paramFunction->getValuePar('numForm');
                
                $busc_ubigeo_pedido=$formData['nx_busc_ubigeo_pedido'];
                $busc_ubig_id=$formData['sx_busc_ubig_id'];
                $busc_tipo_negocio=$formData['nx_busc_tipo_negocio'];
                $grupo_id=$formData['nBusc_grupo_id'];
                
		//$objResponse->addAlert('hola');
		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	
			
			/* Consulta sql a mostrar */
			$sql=new cliente_SQLlista();
			
                        if ($busc_ubigeo_pedido>0){
                            $sql->whereTablUbigeo($busc_ubigeo_pedido);
                            
                        }
                        if ($busc_ubig_id>0){
                            $sql->whereUbigeoID($busc_ubig_id);
                        }
        
                        if ($busc_tipo_negocio>0){
                            $sql->whereTipoNegocio($busc_tipo_negocio);
                            
                        }
                        
                        if ($grupo_id>0){
                            $sql->whereGrupoId($grupo_id);
                        }
                        
			//se analiza la columna de b�squeda
			switch($colSearch){
				case 'clie_id': // si se recibe el campo id
					$sql->whereID($cadena);								
					break;
				
				case 'codigo': // si se recibe el campo id
					$sql->whereCodigo($cadena);
					break;
	
				default:// si se no se recibe ningun campo de busqueda
					//if(ctype_digit($cadena)){ //si la cadena recibida son todos digitos
                                        //    $sql->whereCodigo($cadena);
                                        //}else{
                                            $sql->whereDescrip($cadena);
                                        //}
					break;
                        }
			
			//$sql->orderUno();
			$sql=$sql->getSQL().' ORDER BY a.clie_id DESC ';
			//$objResponse->addAlert($sql);
			//echo $sql; 
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) setSession("cadSearch",$cadena);
	
			$rs = new query($conn, strtoupper($sql),$pg,40);

                        $button = new Button;
                        $button->addItem(" Imprimir B&uacute;squeda ","javascript:Imprimir()","content");
                        $button->setDiv(FALSE);

			/* Creo my objeto Table */
			$otable = new TableSimple(iif($cadena,'!=','','RESULTADO DE: '.$cadena,'')." ".$button->writeHTML(),"100%",6,'tLista'); // T�tulo, ancho, Cantidad de columas,id de la tabla						

			/* Guardo la p�gina actual en un campo oculto.  Para que al eliminar regrese a la p�gina donde estaba */
			$otable->addHtml("<input type='hidden' name='pg' value='$pg'>\n");
                        
			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
                            
					if (!$nomeCampoForm){ /* Si no estoy en una b�squeda avanzada (AvanzLookup) */
						$otable->addColumnHeader("<input type=\"checkbox\" id=\"checkall\" >"); // Coluna com checkbox
                                        }
                                        
					/* Agrego las cabeceras de mi tabla */
                                        $otable->addColumnHeader("ID","5%", "L"); 
					$otable->addColumnHeader("C&oacute;digo","5%", "L"); 
                                        $otable->addColumnHeader("DNI","5%", "L"); 
					$otable->addColumnHeader("Raz&oacute;n Social","28%", "L");
                                        $otable->addColumnHeader("Direcci&oacute;n","27%", "L");
                                        $otable->addColumnHeader("Email","15%", "L");                                         
					$otable->addColumnHeader("Tipo","15%", "L"); 
					$otable->addRowHead(); 					
					while ($rs->getrow()) {
						$id = $rs->field("clie_id");// captura la clave primaria del recordsource
						$clie_codigo = $rs->field("clie_codigo");// 
						$campoTexto_de_Retorno = especialChar($rs->field("clie_razsocial"));

						if ($nomeCampoForm){ /* si la llamada es desde la busqueda avanzada (AvanzLookup) */
							$otable->addData(addLink($rs->field("clieid"),"javascript:update('$id','$clie_codigo','$campoTexto_de_Retorno',$numForm)","Click aqu&iacute; para seleccionar el registro"));
						}elseif($op!=3){  /* Si estoy en la p�gina normal */ 
							/* agrego pg como par�metro a ser enviado por la URL */
							$param->removePar('pg'); /* Remuevo el par�metro */
							$param->addParComplete('pg',$pg); /* Agrego el par�metro */
							
							$otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" >");
							$otable->addData(addLink($rs->field("clieid"),"catalogosCliente_edicion.php?id=$id&clear=$clear&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
						}
						$otable->addData($rs->field("clie_codigo"));
                                                $otable->addData($rs->field("clie_dni"));
						$otable->addData($rs->field("clie_razsocial"));
                                                $otable->addData($rs->field("clie_direccion"));
                                                $otable->addData($rs->field("clie_email"));
						$otable->addData($rs->field("tipo"));						
						
						$otable->addRow();
					}
				$contenido_respuesta=$button->writeHTML();
				$contenido_respuesta.=$otable->writeHTML();
				if($rs->totalpages()>0){
					$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
					$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";
				}
				else{
					$contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";				
				}

			} else {
				$otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!","100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
				$otable->addRowHead(); 	
				$otable->addRow();	
				$contenido_respuesta=$otable->writeHTML();
			}
		}
	else
		$contenido_respuesta="";
	
		//se analiza el tipo de funcionamiento
		if($op==1){//si es llamado para su funcionamiento en ajax con retornoa a un div
			$objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
			$objResponse->addscript("activaSorter()"); // Para activar el orden en la tabla 
			$objResponse->addscript("func_jquerytablas()"); // Para activar las funciones de css de la tabla
			return $objResponse;
			}
		else
			if($op==3){//si es llamado para su funcionamiento en ajax, desde un una busqueda avanzada, con retorno a un objeto
				if($Nameobj){
					//$objResponse->addScript("$('#_Dummytr_ruc_id').attr('value','".$campoTexto_de_Retorno."')");
					$objResponse->addScript($Nameobj ." = '".html_entity_decode($campoTexto_de_Retorno)."';");

					$objResponse->addScript('document.frm.tr_clie_id.value = '.$id);					
					return $objResponse;
					}
				else
					return stripslashes(html_entity_decode($campoTexto_de_Retorno));
				}
			else {//si es llamado como una simple funciona de PHP
				return $contenido_respuesta;
                        }
	}

        function buscarCliente($op,$cadena,$colSearch,$colOrden=1,$Nameobj,$accion=1)
        {
                global $conn;

                $objResponse = new xajaxResponse();
                //$objResponse->setCharEncoding('utf-8');	

                $cadena=trim($cadena);

                if($cadena){

                        $sql=new cliente_SQLlista();

                        //se analiza la columna de busqueda
                        switch($colSearch){
                                case 'clie_id': // si se recibe el campo id
					$sql->whereID($cadena);								
					break;
                                case 'clie_codigo': // si se recibe el campo id
					$sql->whereCodigo($cadena);
					break;
                                case 'clie_historia': // si se recibe el campo id
					$sql->whereHistoria($cadena);
					break;                                    
                                default:// si se no se recibe ningun campo de busqueda
//                                        if(ctype_digit($cadena)) //si la cadena recibida son todos digitos
//                                                $sql->whereCodigo($cadena);
//                                        else
                                                if(strlen($cadena)<3){
                                                        $objResponse->addAssign($Nameobj,'innerHTML', '');
                                                        $objResponse->addAlert('se espera al menos 3 caracteres de busqueda...');
                                                        return $objResponse;
                                                }
                                                else{
                                                        $sql->whereDescrip($cadena);
                                                }
                                        break;
                        }

                        //$sql->orderUno();
                        $sql=$sql->getSQL().' ORDER BY a.clie_id DESC ';;
                        //  echo $sql;
                        //$objResponse->addAlert($sql);

                        $btnFocus="";
                        $rs = new query($conn, strtoupper($sql));
                        
                        $otable = new  Table("","100%",9);
                        
                        if ($rs->numrows()==1) {
                            $rs->getrow();
                            $id = $rs->field("clie_id");
                            if(trim($rs->field("clie_codigo"))){
                                $clie_codigo = trim($rs->field("clie_codigo"));// 
                            }else{
                                $clie_codigo = trim($rs->field("clie_dni"));// 
                            }
                            $campoTexto_de_Retorno = $rs->field("clie_razsocial");
                            
                            $validar=new miValidacionString();
                            $campoTexto_de_Retorno=$validar->replace_invalid_caracters($campoTexto_de_Retorno);
                                                
                            //$clie_direccion=$rs->field("clie_direccion");
                            $clie_direccion=$validar->replace_invalid_caracters($rs->field("clie_direccion"));

                            if(SIS_TIPO_UBIGEO_CLIENTE==1){
                                $tabl_ubigeo=$rs->field('tabl_ubigeo');
                            }
                            
                            if(SIS_TIPO_UBIGEO_CLIENTE==2){
                                $tabl_ubigeo=$rs->field('ubig_id');
                            }
                            

                            $clie_historia_clinica=$rs->field("clie_historia_clinica");
                            
                            $tabl_tipo_cliente=$rs->field('tabl_tipo_cliente').'_'.$rs->field('clie_telefono').'_'.$rs->field('tabl_tipo_negocio').'_'.$rs->field('paca_id');
                            
                            $objResponse->addScript("xajax_eligeCliente($id,\"$campoTexto_de_Retorno\",'$clie_codigo','$clie_direccion','$tabl_ubigeo','$tabl_tipo_cliente','$clie_historia_clinica','$accion')");
                            return $objResponse;                                    
                        }elseif ($rs->numrows()>0) {

                                $link=addLink("Cerrar","javascript:xajax_clearDiv('$Nameobj')");        
                                $otable->addColumnHeader("$link",false,"1%","C"); 
                                $otable->addColumnHeader("C&oacute;digo",false,"8%", "L");
                                $otable->addColumnHeader("DNI",false,"8%", "L"); 
                                $otable->addColumnHeader("Raz&oacute;n Social",false,"40%", "L"); 
                                $otable->addColumnHeader("Direcci&oacute;n",false,"30%", "L"); 
                                $otable->addColumnHeader("Email",false,"11%", "L"); 
                                $otable->addColumnHeader("Tipo",false,"1%", "L");
                                $otable->addColumnHeader("",false,"1%","C"); 

                                $otable->addRow(); // adiciona la linea (TR)
                                while ($rs->getrow()) {
                                        $id = $rs->field("clie_id");// captura la clave primaria del recordsource
                                        if(trim($rs->field("clie_codigo"))){
                                            $clie_codigo = $rs->field("clie_codigo");// 
                                        }else{
                                            $clie_codigo = $rs->field("clie_dni");// 
                                        }
                                        $campoTexto_de_Retorno = $rs->field("clie_razsocial");
                                        $validar=new miValidacionString();
                                        $campoTexto_de_Retorno=$validar->replace_invalid_caracters($campoTexto_de_Retorno);
                            
                                        //$clie_direccion=str_replace('"','',$rs->field("clie_direccion"));
                                        $clie_direccion=$validar->replace_invalid_caracters($rs->field("clie_direccion"));
                                        
                                        if(SIS_TIPO_UBIGEO_CLIENTE==1){
                                            $tabl_ubigeo=$rs->field('tabl_ubigeo');
                                        }

                                        if(SIS_TIPO_UBIGEO_CLIENTE==2){
                                            $tabl_ubigeo=$rs->field('ubig_id');
                                        }


                                        
                                        $clie_historia_clinica=$rs->field("clie_historia_clinica");
                                        $tabl_tipo_cliente=$rs->field('tabl_tipo_cliente').'_'.$rs->field('clie_telefono').'_'.$rs->field('tabl_tipo_negocio').'_'.$rs->field('paca_id');
                                        $button = new Button;
                                        $button->setDiv(FALSE);
                                        $button->setStyle("");
                                        $button->addItem("Aceptar","javascript:xajax_eligeCliente($id,'$campoTexto_de_Retorno','$clie_codigo','$clie_direccion','$tabl_ubigeo','$tabl_tipo_cliente','$clie_historia_clinica','$accion')","content",2,0,"botonAgg","button","","btn_$id");
                                        $otable->addData($button->writeHTML());	

                                        $otable->addData($rs->field("clie_codigo"));
                                        $otable->addData($rs->field("clie_dni"));
                                        $otable->addData($rs->field("clie_razsocial"));
                                        $otable->addData($rs->field("clie_direccion"));
                                        $otable->addData($rs->field("clie_email"));
                                        $otable->addData($rs->field("tabl_descripaux"));						
                                        if($accion==1){
                                            $otable->addData("<a class=\"link\" href=\"#\" onClick=\"javascript:modiCliente($id)\"><img src=\"../../img/editar.gif\" border=0 align=absmiddle hspace=1 alt=\"Editar\"></a>");
                                        }
                                        else {
                                               $otable->addData("&nbsp;");
                                        }
                                        $otable->addRow();
                                        $btnFocus=$btnFocus?$btnFocus:"btn_$id";
                                }

                                $contenido_respuesta=$otable->writeHTML();
                                $contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

                        } else {
                               if($op==3){//si se envia consulta x web
                                    $objResponse->addScript("consultar_RUC_DNI('$cadena',1)");
                                    return $objResponse;
                               }else{ 
                                    $otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
                                    $otable->addRow();
                                    $contenido_respuesta=$otable->writeHTML();
                               }
                        }
               }else{
                    $contenido_respuesta="";
               }
               
                $objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
                $objResponse->addScript("document.frm.$btnFocus.focus()");

                return $objResponse;
        }

        
	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	
}


class cliente_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT a.*,
                                    LPAD(clie_id::TEXT,5,'0') AS clieid,
                                    COALESCE(a.clie_dni,a.clie_codigo) AS codigo,
                                    b.tabl_descripcion AS tipo,
                                    c.tabl_descripcion AS ubigeo,
                                    b.tabl_descripaux,
                                    ubigeo.ubig_descripcion AS ubigeo2,
                                    split_part(ubigeo.ubig_descripcion::TEXT,'-', 1) AS departamento,
                                    split_part(ubigeo.ubig_descripcion::TEXT,'-', 2) AS provincia,
                                    split_part(ubigeo.ubig_descripcion::TEXT,'-', 3) AS distrito,
                                    e.tabl_descripcion AS tipo_negocio,
                                    f.tabl_descripcion AS calificacion,
                                    x.usua_login as username,
                                    y.usua_login as usernameactual
				FROM catalogos.cliente a
				LEFT JOIN catalogos.tabla b         ON a.tabl_tipocliente=b.tabl_codigo AND b.tabl_tipo='TIPO_PROVEEDOR' 
				LEFT JOIN catalogos.tabla c         ON a.tabl_ubigeo=c.tabl_id 
                                LEFT JOIN catalogos.ubigeo  ubigeo  ON a.ubig_id=ubigeo.ubig_id
                                LEFT JOIN catalogos.tabla e         ON a.tabl_tipo_negocio=e.tabl_id
                                LEFT JOIN catalogos.tabla f         ON a.tabl_calificacion=f.tabl_id
				LEFT JOIN admin.usuario x ON a.usua_id=x.usua_id
				LEFT JOIN admin.usuario y ON a.clie_actualusua=y.usua_id	
				";
	}

	function whereID($id){
		$this->addWhere("a.clie_id=$id");	
	}

        function whereIDVarios($varios){
                    $this->addWhere("a.clie_id IN ($varios)");
	}
        
	function whereCodigo($codigo){
		$this->addWhere("(a.clie_codigo='$codigo' OR a.clie_dni='$codigo' OR a.clie_historia_clinica=$codigo)");	
	}
        
        function whereHistoria($historia){
		$this->addWhere("a.clie_historia_clinica=$historia");	
	}

        function whereHipe($hipe_id){
		$this->addWhere("a.hipe_id=$hipe_id");	
	}
        
        function whereActivo(){
            $this->addWhere("a.clie_estado=1");	
        }
        
        function whereDNI($dni){
		$this->addWhere("a.clie_dni='$dni'");	
	}
        
        function whereDescrip($cadena){
                if($cadena) {
                        $array=explode(" ",$cadena);
                        for($i=0; $i<count($array); $i++){
                            $lista.=$lista?" AND ":"";
                            $lista.="a.clie_razsocial ILIKE '%".$array[$i]."%'";
                        }
                    
                        $this->addWhere("( a.clie_codigo='$cadena' 
                                            OR a.clie_dni='$cadena' 
                                            OR a.clie_historia_clinica::TEXT='$cadena' 
                                            OR a.clie_direccion ILIKE '%$cadena%' 
                                            OR ($lista) 
                                              ) 
                                        ");
                }
	}
		
        function whereTablUbigeo($tabl_ubigeo){
		$this->addWhere("a.tabl_ubigeo=$tabl_ubigeo");	
	}
        
        function whereUbigeoID($ubig_id){
		$this->addWhere("a.ubig_id=$ubig_id");	
	}

        function whereTipoNegocio($tabl_tipo_negocio){
		$this->addWhere("a.tabl_tipo_negocio=$tabl_tipo_negocio");	
	}
        
        function whereGrupoID($grupo_id){
		$this->addWhere("a.clie_id IN (SELECT DISTINCT a.clie_id 
                                                        FROM siscore.recaudaciones a
                                                        LEFT JOIN siscore.recaudaciones_detalle b ON a.reca_id=b.reca_id
                                                        LEFT JOIN catalogos.servicio c ON b.serv_codigo=c.serv_codigo
                                                        WHERE a.reca_estado!=9
                                                        AND a.reca_modingreso!=20
                                                        AND c.segr_id=$grupo_id
                                                )");	
	}
                        
	function orderUno(){
		$this->addOrder("a.clie_id DESC");		
	}
        
        function orderDos(){
		$this->addOrder("a.tabl_ubigeo,a.clie_apellidos,a.clie_nombres");		
	}
        
         function getSQL_resumen() {
                $sql="SELECT DISTINCT 
                             a.clie_id,
                             a.clie_id::TEXT||' ['||a.codigo::TEXT||'] '||a.clie_razsocial
                        FROM (".$this->getSQL().") AS a 
                        ORDER BY 1 "; 
                return($sql);
        }
}

if( isset($_GET['control']) ){
	
	$control=base64_decode($_GET['control']);
	if($control){
		include("../../library/library.php");
		/*	verificaci�n a nivel de usuario */
		verificaUsuario(1);
		verif_framework();
		
		$param= new manUrlv1();	
		$param->removePar('control');
		$param->removePar('relacionamento_id');
		$param->removePar('pg'); /* Remuevo el par�metro */

		/* Recibo la p�gina actual de la lista y lo agrego como par�metro a ser enviado por la URL.  Este dato viene por POST */
		$pg = getParam("pg");	
		$param->addParComplete('pg',$pg); /* Agrego el par�metro */		
		
		//	conexi�n a la BD 
		$conn = new db();
		$conn->open();

		$dml=new cliente();

		switch($control){
			case 1: // Guardar
				$dml->guardar();   
				break;
			case 2: // Eliminar
				$dml->eliminar();
				break;		
		}
		//	cierra la conexi�n con la BD
		$conn->close();
	}
}