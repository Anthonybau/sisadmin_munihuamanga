<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class clsUsers extends entidad {
	
	function __construct($id='',$title=''){
		$this->setTable='usuario'; //nombre de la tabla
		$this->setKey='usua_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

		/* Puede usarse también de manera directa as�*/
//		$this->pagEdicion = "catalogosDependenciaAdquiriente_edicion.php";
//		$this->pagBuscar  = "catalogosDependenciaAdquiriente_buscar.php";		
		
		$this->pagEdicion=$this->getNamePage('edicion');
		
		$this->pagBuscar=$this->getNamePage('buscar');
		
		/* Destinos luego de actulizar, agregar o eliminar un registro */
		$this->destinoUpdate = $this->pagBuscar;
		$this->destinoInsert = $this->pagBuscar;
		$this->destinoDelete = $this->pagBuscar;

		$this->arrayNameVar[0]='nomeCampoForm';
		$this->arrayNameVar[1]='busEmpty';
		$this->arrayNameVar[2]='cadena';
		$this->arrayNameVar[3]='pg';
		$this->arrayNameVar[4]='colSearch';
		$this->arrayNameVar[5]='numForm';

	}

	function getSql(){
		$sql=new clsUsers_SQLlista();
		$sql->whereID($this->id);
		return($sql->getSQL());
	}

	function addField(&$sql){
            if ($_POST["hx_usua_set_gestdoc_todos"])
                $sql->addField("usua_set_gestdoc_todos", 1, "Number");
            else
                $sql->addField("usua_set_gestdoc_todos", 0, "Number");
            
            if ($_POST["hx_usua_set_jefe"])
                $sql->addField("usua_set_jefe", 1, "Number");
            else
                $sql->addField("usua_set_jefe", 0, "Number");
            
            if($_POST["hx_usua_set_depe_exclusivo"]){
                $sql->addField("usua_set_depe_exclusivo", 1, "Number");
            }else{
                $sql->addField("usua_set_depe_exclusivo", 0, "Number");
            }
            
            if ($_POST["hx_usua_set_autoriza_solicitudes"]){
                $sql->addField("usua_set_autoriza_solicitudes", 1, "Number");
            }else{
                $sql->addField("usua_set_autoriza_solicitudes", 0, "Number");
            }
            
            if ($_POST["hx_usua_set_aplica_ajustes_ventas"]){
                $sql->addField("usua_set_aplica_ajustes_ventas", 1, "Number");
            }else{
                $sql->addField("usua_set_aplica_ajustes_ventas", 0, "Number");
            }
            
            if ($_POST["hx_usua_set_edita_total"]){
                $sql->addField("usua_set_edita_total", 1, "Number");
            }else{
                $sql->addField("usua_set_edita_total", 0, "Number");
            }

            if ($_POST["hx_usua_set_edita_total2"]){
                $sql->addField("usua_set_edita_total2", 1, "Number");
            }else{
                $sql->addField("usua_set_edita_total2", 0, "Number");
            }
            
            if ($_POST["hx_usua_set_edita_impunitario"]){
                $sql->addField("usua_set_edita_impunitario", 1, "Number");
            }else{
                $sql->addField("usua_set_edita_impunitario", 0, "Number");
            }
            
            if ($_POST["hx_usua_set_edita_fecha"]){
                $sql->addField("usua_set_edita_fecha", 1, "Number");
            }else{
                $sql->addField("usua_set_edita_fecha", 0, "Number");
            }

            
//            if ($_POST["hx_usua_mesa_partes_virtual"]){
//                $sql->addField("usua_mesa_partes_virtual", 1, "Number");
//            }else{
//                $sql->addField("usua_mesa_partes_virtual", 0, "Number");
//            }

            if ($_POST["hx_usua_set_certificado"]){
                $sql->addField("usua_set_certificado", 1, "Number");
            }else{
                $sql->addField("usua_set_certificado", 0, "Number");
            }
            
            if ($_POST["ax_usua_ventas_acceso"]==""){
                $sql->addField("usua_ventas_acceso", "", "String");
            }
            
            if ($_POST["hx_usua_permitir_regularizaciones"]){
                $sql->addField("usua_permitir_regularizaciones", 1, "Number");
            }else{
                $sql->addField("usua_permitir_regularizaciones", 0, "Number");
            }

            if ($_POST["hx_usua_permite_cancelar_creditos"]){
                $sql->addField("usua_permite_cancelar_creditos", 1, "Number");
            }else{
                $sql->addField("usua_permite_cancelar_creditos", 0, "Number");
            }
            
            
            
	}

	function guardar(){
		global $conn,$param;
		$nomeCampoForm=getParam("nomeCampoForm");

		$destinoUpdate=$this->destinoUpdate.$param->buildPars(true);	
		$pg=is_array($this->arrayNameVar)?$this->arrayNameVar[3]:'pg';
		$param->removePar($pg); /* Remuevo el par�metro p�gina */
		$destinoInsert=$this->destinoInsert.$param->buildPars(true);
		
		// objeto para instanciar la clase sql
		$sql = new UpdateSQL();
				
		$sql->setTable($this->setTable);
		$sql->setKey($this->setKey, $this->valueKey, $this->typeKey);
	
		include("../guardar_tipoDato.php");
	
		if ($this->valueKey) { // modificaci�n
			$sql->setAction("UPDATE");
		}else{
			$sql->setAction("INSERT");
		}
	
		/* Aquí puedo agregar otros campos a la sentencia SQL */
		$this->addField($sql);
		//echo $sql->getSQL();
		/* Ejecuto el SQL */
		$conn->execute($sql->getSQL());
		$error=$conn->error();
		if($error){ 
			alert($error);	/* Muestro el error y detengo la ejecuci�n */
		}else{
			/*  muestra mensaje noticia del la base de datos, pero no detiene la ejecucion	*/
			$notice=$conn->notice();
			if($notice) 
				alert($notice,0);				
		}
		
		/* */
		if ($this->valueKey) {// modificaci�n
			if($this->is_thinckbox){ // Si es un Script javascript el que quiero que se ejecute luego de insertar un registro
				echo "<script language=\"javascript\">
						self.parent.tb_remove();
						self.parent.document.location='$destinoUpdate'
					</script>"; /* Al recargar la p�g. se cierra el Thickbox que he utilizado para editar o modificar */
			}
			$last_id=$this->valueKey; 
			if(strpos($destinoInsert, "?")>0)
				$destinoUpdate.="&id=$last_id";
			else
				$destinoUpdate.="?id=$last_id";

			redirect($destinoUpdate,"content");			
							
		}else{ /* Inserci�n */
			if($nomeCampoForm){//si se llama desde una ventana emergente (avanzlookup) para seleccionar un valor
				/* Datos que se retornan desde un (avanzlookup) */
				$return_val=$this->return_val; /* Valor que se devuelve */
				$return_txt=$this->return_txt; /* Texo que se devuelve */
	
				/* Comandos Javascript */		
				echo "<script language=\"javascript\">
						parent.parent.content.document.forms[0]._Dummy$nomeCampoForm.value = '$return_txt';
						parent.parent.content.document.forms[0].$nomeCampoForm.value = '$return_val';
						parent.parent.content.document.forms[0].__Change_$nomeCampoForm.value = 1;
						self.parent.tb_remove();
					</script>";
			}else{ /* Si se llama desde una p�gina normal */
				if($this->is_thinckbox){ // Si es un Script javascript el que quiero que se ejecute luego de insertar un registro
					echo "<script language=\"javascript\">
							self.parent.tb_remove();
							self.parent.document.location='$destinoInsert'
						</script>"; /* Al recargar la p�g. se cierra el Thickbox que he utilizado para editar o modificar */
					}
				/*a�ado el id del registro ingresado*/
				$last_id=$conn->lastid($this->setTable. '_' . $this->setKey . "_seq"); /* Obtengo el Id del registro ingreado.  Ojo esto solo funciona si el campo clave es un serial (est� basado en una secuencia) */								
				if(strpos($destinoInsert, "?")>0)
					$destinoInsert.="&id=$last_id&clear=1";  
				else
					$destinoInsert.="?id=$last_id&clear=1";
			
				/* Envio el "id" para cuando regreso a la misma p�gina de edici�n y el 
				"clear" para cuando regreso a la lista y deseo que se vea el �ltimo registro ingresado, 
				con el clear se limpia la variable "cadSearch" o "cadSearchhijo" */
				//echo $destinoInsert;	
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
		
		if(strtolower($this->typeKey)=='string'){
			/* debido a que el campo clave es char */
			$lista_elimina=ereg_replace(",","','",$lista_elimina); 
		}

		/* Sql a ejecutar */
		$sql="DELETE FROM $this->setTable 
				WHERE $this->setKey 
					IN (".iif(strtolower($this->typeKey),"==","string","'","").$lista_elimina.iif(strtolower($this->typeKey),"==","string","'","").") 
					AND usua_idcrea=".getSession("sis_userid");

		/* Ejecuto la sentencia */
		$conn->execute($sql);
		$error=$conn->error();		
		if($error) alert($error);
		else{ 		
			redirect($destinoDelete,"content");		
		}
	}
		
	function buscar($op,$formData,$arrayParam,$pg,$Nameobj='')
	{
		global $conn,$param,$nomeCampoForm;
		$objResponse = new xajaxResponse();
		$arrayParam=decodeArray($arrayParam);

		$paramFunction= new manUrlv1();
		$paramFunction->setUrlExternal($arrayParam);

		if($op==1 && !is_array($formData)) $formData=decodeArray($formData);
		
		$nbusc_origen=$formData['nbusc_origen'];
		
		$cadena=is_array($formData)?trim($formData['Sbusc_cadena']):$formData;
		
		$busEmpty=$paramFunction->getValuePar($paramFunction->getValuePar(1));
                
		$colSearch=$paramFunction->getValuePar($paramFunction->getValuePar(4));
		$numForm=$paramFunction->getValuePar($paramFunction->getValuePar(5));

				
		$pageEdit=$paramFunction->getValuePar('pageEdit');
                $depe_id=$formData['nbusc_depe_id']; 
		$sub_dependencia=$formData['tr_sub_dependencia'];
                $usua_activo=$formData['nbusc_usua_activo'];
                
		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	

			$sql=new clsUsers_SQLlista();
                        if($sub_dependencia){
                            $sql->whereDepeID($sub_dependencia);
                        }else{
                            if($depe_id){
                                $sql->whereDepeTodos($depe_id);
                            }else{
                                $sql->whereDepeTodos(getSession('sis_depe_superior'));                                
                            }
                        }
                        
//			if($nbusc_depe_id)
//				$sql->whereDepeID($nbusc_depe_id);
			
			if($nbusc_origen){
                            $sql->whereOrigen($nbusc_origen);
                        }
                        
                        if( $usua_activo!=10 ){
                            $sql->whereActivo($usua_activo);
                        }
			//se analiza la columna de busqueda
			switch($colSearch){
				case 'numero': // si se recibe el campo id
					$sql->whereID($cadena);
					break;
	
				default:// si se no se recibe ningun campo de busqueda
					if($cadena)
						$sql->whereDescrip($cadena);

					break;
				}
			$sql->orderUno();
			$sql=$sql->getSQL();
			
			
			//$objResponse->addAlert($sql);
			
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) {
				$param->replaceParValue($paramFunction->getValuePar(2),$cadena); /* Agrego el par�metro */			
			}
	
			$rs = new query($conn, strtoupper($sql),$pg, 80);

			/* Creo my objeto Table */
			$otable = new TableSimple("","100%",8,'tLista'); // Título, ancho, Cantidad de columas,id de la tabla

			/* Guardo la p�gina actual en un campo oculto.  Para que al eliminar regrese a la p�gina donde estaba */
			$otable->addHtml("<input type='hidden' name='".$paramFunction->getValuePar(3)."' value='$pg'>\n");			
                        
			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,'".encodeArray($formData)."','".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,'".encodeArray($formData)."','".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
					$otable->addColumnHeader(""); // Coluna com checkbox
					/* Agrego las cabeceras de mi tabla */
					$otable->addColumnHeader("Usuario","5%", "L"); // T�tulo, Ordenar?, ancho, alineaci�n
					$otable->addColumnHeader("Nombre Completo","22%","L");
                                        if(SIS_SISCORE==1){
                                            $otable->addColumnHeader("Ventanilla","20%","L");
                                            $otable->addColumnHeader("Dependencia","24%","L");
                                        }else{
                                            $otable->addColumnHeader("Dependencia","44%","L");
                                        }
                                        $otable->addColumnHeader("Superior","16%","L");
                                        $otable->addColumnHeader("Cargo","15%","L");

					$otable->addColumnHeader("Niv","5%","C");
					$otable->addColumnHeader("Act","2%","C");
					$otable->addColumnHeader("Fech.Alta","5%","C");
					$otable->addRowHead();
					 					
					while ($rs->getrow()) {
						$id = $rs->field("usua_id"); // captura la clave primaria del recordsource
						
						/* agrego pg como par�metro a ser enviado por la URL */
						$param->replaceParValue($paramFunction->getValuePar(3),$pg); /* Agrego el par�metro */
							
						$otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" >");

						$param->addParComplete("nbusc_depe_id",$nbusc_depe_id);
						$param->addParComplete("nbusc_origen",$nbusc_origen);
												
						$link=$pageEdit."?id=$id&".$param->buildPars(false);
						$otable->addData(addLink($rs->field("usua_login"),$link,"Click aqu&iacute; para consultar o editar este registro"));
						
						$otable->addData($rs->field("empleado"));
                                                if(SIS_SISCORE==1){
                                                    $otable->addData($rs->field("ventanilla"));
                                                }
						$otable->addData($rs->field("depe_nombre"));
                                                $otable->addData($rs->field("depe_superior_nombre"));
                                                $otable->addData($rs->field("pdla_cargofuncional"));
                                                
						if($rs->field("usua_acceso")==1){
							$cAcceso='Visitante';
                                                }else
							if($rs->field("usua_acceso")==2){
								$cAcceso='Operador';
                                                        }else
								if($rs->field("usua_acceso")==3){
									$cAcceso='Supervisor';
                                                                }else
									if($rs->field("usua_acceso")==4){
										$cAcceso='Administrador';
                                                                         }
                                                      
                                                
                                                $otable->addData(substr($cAcceso,0,3),"C");
						$otable->addData(substr($rs->field("estado_activo"),0,6),"C");
						$otable->addData(dateFormat($rs->field("usua_fecharegistro"),"Y-m-d","d/m/Y"),"C");
                                                
                                                if($rs->field("usua_activo")==9){
                                                    $otable->addRow('ANULADO');
                                                }else{
                                                    $otable->addRow();
                                                }
					}
					
				$contenido_respuesta=$button->writeHTML();
				$contenido_respuesta.=$otable->writeHTML();
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";
				//$contenido_respuesta=$sql;

			} else {
				$otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!","100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
				$otable->addRowHead(); 	
				$otable->addRow();	
				$contenido_respuesta=$otable->writeHTML();
			}
		}
	else{
		$contenido_respuesta="";
	}

	
	//$contenido_respuesta=$sql;	
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
					$objResponse->addScript($Nameobj .' = "'.$campoTexto_de_Retorno.'";');
					return $objResponse;
					}
				else
					return $campoTexto_de_Retorno;
				}
			else//si es llamado como una simple funciona de PHP
				return $contenido_respuesta	;
	}

        function buscarUsuario($op,$cadena,$Nameobj)
        {
                global $conn;

                $objResponse = new xajaxResponse();
                //$objResponse->setCharEncoding('utf-8');	

                $cadena=trim($cadena);

                if($cadena){

                        $otable = new  Table("","100%",9);
                        $sql=new clsUsers_SQLlista();


                        if(strlen($cadena)<3){
                                $objResponse->addAssign($Nameobj,'innerHTML', '');
                                $objResponse->addAlert('se espera al menos 3 caracteres de busqueda...');
                                return $objResponse;
                        }
                            else{
                                $sql->whereDescrip($cadena);
                            }


                        $sql->orderUno();
                        $sql=$sql->getSQL();
        //	  echo $sql;
        //	  $objResponse->addAlert($sql);

                                $btnFocus="";
                                $rs = new query($conn, strtoupper($sql));
                                if ($rs->numrows()>0) {
                                                $otable->addColumnHeader("Usuario","13%", "L"); 
                                                $otable->addColumnHeader("Nombre Completo","35%","L");
                                                $otable->addColumnHeader("Dependencia","30%","L");
                                                $otable->addColumnHeader("Cargo","15%","L");

                                                $otable->addRow(); // adiciona la linea (TR)
                                                while ($rs->getrow()) {
                                                        $id = $rs->field("usua_id");
                                                        $campoTexto_de_Retorno = especialChar($rs->field("empleado"));


                                                        $button = new Button;
                                                        $button->setDiv(FALSE);
                                                        $button->setStyle("");
                                                        $button->addItem("Aceptar","javascript:xajax_ejigeUsuario('$id','$campoTexto_de_Retorno')","content",2,0,"botonAgg","button","","btn_$id");
                                                        $otable->addData($button->writeHTML());				                                

                                                        $otable->addData($rs->field("empleado"));
                                                        $otable->addData($rs->field("depe_nombre"));
                                                        $otable->addData($rs->field("pdla_cargofuncional"));

                                                        $otable->addRow();
                                                        $btnFocus=$btnFocus?$btnFocus:"btn_$id";
                                                }

                                        $contenido_respuesta=$otable->writeHTML();
                                        $contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

                                } else {
                                        $otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
                                        $otable->addRow();
                                        $contenido_respuesta=$otable->writeHTML();
                                }
                        }
                else
                        $contenido_respuesta="";

            $objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
            $objResponse->addScript("document.frm.$btnFocus.focus()");

            return $objResponse;

        }
        
	/* Nombre del archivo php de la clase */
	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	

	function getNamePage($accion)
	{
		return(str_replace('class',$accion,$this->getNameFile()));
	}	

} /* Fin de la clase */

class clsUsers_SQLlista extends selectSQL {
	function __construct(){
		$this->sql = "SELECT            CASE WHEN a.usua_online>=(current_timestamp - interval '00:00:08') THEN 1
                                                     ELSE 0 
                                                END AS ord_online,
                                                a.*,
                                                CASE WHEN a.usua_activo=1 THEN 'ACTIVO'
                                                     WHEN a.usua_activo=2 THEN 'NUEVO' /*SE CREO DESDE ESCALAFON*/
                                                     WHEN a.usua_activo=3 THEN 'MANTENIMIENTO' /*SOLICITO CAMBIO DE CONTRASEÑA*/
                                                     WHEN a.usua_activo=9 THEN 'BAJA'
                                                END AS estado_activo,
                                                b.depe_id,
                                                b.pers_id,
                                                b.pdla_cargofuncional,
			 			c.pers_apellpaterno||' '||c.pers_apellmaterno||' '||c.pers_nombres AS empleado,
                                                c.pers_apellpaterno||' '||SUBSTRING(c.pers_nombres,1,CASE WHEN POSITION(' ' IN c.pers_nombres)>0 THEN POSITION(' ' IN c.pers_nombres) ELSE 100 END)  AS empleado_breve,
                                                c.pers_email,
                                                c.pers_dni,
                                                c.pers_nacefecha,
                                                d.depe_nombre,
                                                d.depe_nombrecorto,
                                                CASE WHEN a.usua_id=1 /*SI ES ADMINISTRADOR*/ THEN 0
                                                     ELSE COALESCE(ds.depe_superior_id,0)
                                                END AS depe_superior_id,
                                                ds2.depe_nombrecorto AS depe_superior_nombre,
                                                f.tabl_descripcion AS origen_usuario,
                                                h.vent_descripcion||COALESCE('-'||h.vent_direccion,'') AS ventanilla,
                                                (SELECT  CASE WHEN COUNT(x.depe_id)>0 THEN 1 ELSE 0 END
                                                                FROM catalogos.dependencia x
                                                                LEFT JOIN  personal.persona_datos_laborales y ON x.pdla_id=y.pdla_id
                                                                WHERE y.pers_id=b.pers_id) AS es_jefe,
						x.usua_login AS usua_crea
		 			  FROM admin.usuario a 
		 			  LEFT JOIN personal.persona_datos_laborales b on  a.pdla_id=b.pdla_id
                                          LEFT JOIN personal.persona c      on  b.pers_id=c.pers_id
		 			  LEFT JOIN catalogos.dependencia d on  b.depe_id=d.depe_id
				   	  LEFT JOIN catalogos.tabla   f     ON  a.usua_tipo=f.tabl_codigo AND f.tabl_tipo='TIPO_USUARIO'
                                          
                                          LEFT JOIN catalogos.ventanilla h  on  a.vent_id=h.vent_id
                                          LEFT JOIN (SELECT a.depe_id,
                                                     (SELECT depe_id FROM catalogos.func_treeunidsuperior2(a.depe_id)) AS depe_superior_id
                                                      FROM catalogos.dependencia a) ds  ON b.depe_id=ds.depe_id 
                                          LEFT JOIN catalogos.dependencia ds2           ON ds.depe_superior_id=ds2.depe_id                                                      
		 			  LEFT JOIN admin.usuario x                     ON  a.usua_idcrea=x.usua_id
					";
	}

	function whereID($id){
		$this->addWhere("a.usua_id=$id");	
	}

        function whereDNI($dni){
		$this->addWhere("c.pers_dni='$dni'");	
	}        
        
        function wherePersID($pers_id){
		$this->addWhere("b.pers_id=$pers_id");	
	}        
        
	function whereActivo($usua_activo){
		$this->addWhere("a.usua_activo=$usua_activo");	
	}
        
        function whereMPVirtual(){
		$this->addWhere("a.usua_mesa_partes_virtual=1");
	}
        
	function whereNotID($id){
		$this->addWhere("a.usua_id!=$id");
	}
        
        function whereONLine(){
		$this->addWhere("a.usua_online>=(current_timestamp - interval '00:00:08')");
	}
        
        function whereNOTONLine(){
		$this->addWhere("a.usua_online=0");
	}
        
	function whereDepeID($depe_id){
		$this->addWhere("b.depe_id=$depe_id");
	}
	
        function whereDepeTodos($depe_id) {
            $this->addWhere("b.depe_id IN (SELECT depe_id
                                                FROM catalogos.func_treedependencia2($depe_id)
                                                WHERE depe_id>0)");
        }
        
	function whereUserLogin($userLogin){
                $this->addWhere(sprintf("a.usua_login='%s'", $userLogin));
	}        
                
	function whereUserPassword($userPassword){
                $this->addWhere(sprintf("a.usua_password='%s'", $userPassword));
	}

        function whereUserLogin2($userLogin){
                $this->addWhere(sprintf("a.usua_login=%s", $userLogin));
	}        
                
	function whereUserPassword2($userPassword){
                $this->addWhere(sprintf("a.usua_password=%s", $userPassword));
	}
        
	function whereOrigen($origen){
		$this->addWhere("a.usua_tipo=$origen");		
	}
	
	function whereDescrip($descrip){
		if($descrip) $this->addWhere("( TRIM(c.pers_apellpaterno)||' '||TRIM(c.pers_apellmaterno)||' '||TRIM(c.pers_nombres) ILIKE '%$descrip%' 
                                                OR TRIM(c.pers_nombres)||' '||TRIM(c.pers_apellpaterno)||' '||TRIM(c.pers_apellmaterno) ILIKE '%$descrip%'
                                                OR a.usua_login ILIKE '%$descrip%' 
                                                OR d.depe_nombre ILIKE '%$descrip%')
                                                ");
	}

	function getSQL_cbox(){
		$sql="SELECT a.usua_id,a.usua_login||' '||empleado_breve
				FROM (".$this->getSQL().") AS a
                      ORDER BY 1";
		return $sql;
	}

        function getSQL_cbox2(){
		$sql="SELECT a.usua_id,a.usua_login||' '||empleado
				FROM (".$this->getSQL().") AS a
                      ORDER BY empleado";
		return $sql;
	}
        
	function orderUno(){
		$this->addOrder("a.usua_id DESC");		
	}
	
        function orderDos(){
		$this->addOrder("a.usua_login");		
	}
        function orderTres(){
		$this->addOrder("1 DESC,a.usua_login");		
	}
}

class clsUsersDatosLaborales_SQLlista extends selectSQL {
	function __construct(){
		$this->sql = "SELECT a.depe_id,
                                     d.depe_nombre,
                                     c.pers_apellpaterno||' '||c.pers_apellmaterno||' '||c.pers_nombres AS empleado,
                                     c.pers_apellpaterno||' '||SUBSTRING(c.pers_nombres,1,CASE WHEN POSITION(' ' IN c.pers_nombres)>0 THEN POSITION(' ' IN c.pers_nombres) ELSE 100 END)  AS empleado_breve,
                                     x.usua_id,
                                     x.usua_login
                              FROM personal.persona_datos_laborales  a
                              LEFT JOIN personal.persona c      ON a.pers_id=c.pers_id
                              LEFT JOIN catalogos.dependencia d ON a.depe_id=d.depe_id 
                              LEFT JOIN (SELECT a.pers_id,
                                                b.usua_id,
                                                b.usua_login,
                                                b.usua_activo
                                         FROM personal.persona_datos_laborales a 
                                         LEFT JOIN admin.usuario b ON a.pdla_id=b.pdla_id
                                         ) AS x ON a.pers_id=x.pers_id
                              ";
	}

	function whereDepeID($depe_id){
		$this->addWhere("a.depe_id=$depe_id");
	}
        
        function whereActivo(){
		$this->addWhere("(x.usua_id IS NOT NULL AND x.usua_activo=1 AND a.pdla_estado=1)");	
	}
        
        function whereNOTNull(){
		$this->addWhere("x.usua_id IS NOT NULL");	
	}
        
	function getSQL_cbox(){
		$sql="SELECT a.usua_id,a.usua_login||' '||empleado
				FROM (".$this->getSQL().") AS a
                      ORDER BY 1";
		return $sql;
	}	
}


class clsEstoyenLinea extends selectSQL {
    function __construct($usua_id_session){
		$this->sql = "UPDATE admin.usuario SET usua_online=NOW() WHERE usua_id=$usua_id_session ";
	}    
}
 
////////

/* Llamando a la subclase */
if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
            require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

            /*	verificaci�n a nivel de usuario */
            verificaUsuario(1);
            verif_framework();

            $param= new manUrlv1();
            $param->removePar('control');

            //	conexi�n a la BD
            $conn = new db();
            $conn->open();

            $dml=new clsUsers();
            /* Recibo la p�gina actual de la lista y lo agrego como par�metro a ser enviado por la URL.  Este dato viene por POST */
            $pg = getParam($dml->getArrayNameVarID(3));
            $param->replaceParValue($dml->getArrayNameVarID(3),$pg); /* Agrego el par�metro */

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