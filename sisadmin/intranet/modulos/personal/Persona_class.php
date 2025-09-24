<?php
require_once($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/clases/entidad.php");
require_once($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/modulos/admin/sistemaModuloOpciones_class.php");

class clsPersona extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='personal.persona'; //nombre de la tabla
		$this->setKey='pers_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;

		/* Destinos luego de actulizar, agregar o eliminar un registro */
		$this->destinoInsert='Persona_buscar.php';
		$this->destinoUpdate='Persona_edicion.php';
		$this->destinoDelete='Persona_buscar.php';

		$this->arrayNameVar[0]='nomeCampoForm';
		$this->arrayNameVar[1]='busEmpty';
		$this->arrayNameVar[2]='cadena';
		$this->arrayNameVar[3]='pg';
		$this->arrayNameVar[4]='colSearch';
		$this->arrayNameVar[5]='numForm';

	}

        function getSql(){
		$sql=new clsPersona_SQLlista();
		$sql->whereID($this->id);
		return($sql->getSQL());
	}

        function addField(&$sql){
		$sql->addField("pers_actualfecha", 'NOW()', "String");
		$sql->addField("pers_actualusua", getSession("sis_userid"), "String");
	}

	function buscar($op,$formData,$arrayParam,$pg,$Nameobj='',$nbusc_char='')
	{
		global $conn,$param,$clear;
		$objResponse = new xajaxResponse();

		$arrayParam=decodeArray($arrayParam);

		$paramFunction= new manUrlv1();
		$paramFunction->setUrlExternal($arrayParam);

		$cadena=is_array($formData)?trim(strtoupper($formData['Sbusc_cadena'])):$formData;
		if ($op==1 && !$nomeCampoForm) setSession("cadSearch",$cadena);

		$colSearch=$paramFunction->getValuePar('colSearch');
		$colOrden=$paramFunction->getValuePar('colOrden');
		$busEmpty=$paramFunction->getValuePar('busEmpty');
                $pagEdit=$paramFunction->getValuePar('pagEdit');                
                
                $nbusc_sitlaboral=is_array($formData)?$formData['nbusc_sitlaboral']:$paramFunction->getValuePar('nbusc_sitlaboral');
                $nbusc_categoria=is_array($formData)?$formData['nbusc_categoria']:$paramFunction->getValuePar('nbusc_categoria');
                $nbusc_clasificacion=is_array($formData)?$formData['nbusc_clasificacion']:$paramFunction->getValuePar('nbusc_clasificacion');
                $nbusc_plan_activo=is_array($formData)?$formData['nbusc_plan_activo']:$paramFunction->getValuePar('nbusc_plan_activo');
                
                
                if(!$nbusc_char){
                    $nbusc_char=is_array($formData)?$formData['nbusc_char']:$paramFunction->getValuePar('nbusc_char');
                }
                
                $periodo_alta=is_array($formData)?$formData['nbusc_periodo_alta']:$paramFunction->getValuePar('nbusc_periodo_alta');
                $periodo_cese=is_array($formData)?$formData['nbusc_periodo_cese']:$paramFunction->getValuePar('nbusc_periodo_cese');
                
                $ftermino=is_array($formData)?$formData['Dbusc_ftermino']:$paramFunction->getValuePar('Dbusc_ftermino');
                
                $param->removePar('nbusc_sitlaboral');
                $param->removePar('nbusc_char');
                $param->addParComplete('nbusc_sitlaboral',$nbusc_sitlaboral);
                $param->addParComplete('nbusc_categoria',$nbusc_categoria);                
                $param->addParComplete('nbusc_periodo_alta',$periodo_alta);
                $param->addParComplete('nbusc_clasificacion',$nbusc_clasificacion);
                $param->addParComplete('nbusc_plan_activo',$nbusc_plan_activo);
                $param->addParComplete('nbusc_char',$nbusc_char);

                $depe_id=$formData['nbusc_depe_id']; 
                                
		if(strlen($cadena)>0 or $busEmpty==1 or $nbusc_char){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias

                        $char=substr(strtoupper($cadena),0,2);
                        if(!$nbusc_char && inlist($char,'C ')){//CONCEPTO
                            $textBusc=substr($cadena,2);
                            require_once('PersonaPerfilPago_class.php');                
                            
                            $sql=new clsPerfilPago_SQLlista();
                            $sql->whereTipoPersona(1); //EMPLEADO
                            if($depe_id){
                                $sql->whereDepeTodos($depe_id);
                            }else{
                                $sql->whereDepeTodos(getSession('sis_depe_superior'));                                
                            }
                            
                            if(ctype_digit($textBusc)){ //si la cadena recibida son todos digitos
                                $sql->whereConcID($textBusc);
                            }else{
                                $sql->whereDescrip($textBusc);
                            }

                            $sql->orderTres();
                            
                        }else{
                            $char='';
                            $sql=new clsPersona_SQLlista(2);
                            $sql->whereTipoPersona(1); //EMPLEADO
                            
                            if($depe_id){
                                $sql->whereDepeTodos($depe_id);
                            }else{
                                $sql->whereDepeTodos(getSession('sis_depe_superior'));                                
                            }
                            $sql->whereNOAdmin();
                            
                            if($nbusc_sitlaboral){
                                $sql->whereSitLaboral($nbusc_sitlaboral);
                            }
                            if($nbusc_categoria){
                                $sql->whereCategoria($nbusc_categoria);
                            }
                            
                            if($nbusc_clasificacion){
                                $sql->whereClasificacion($nbusc_clasificacion);
                            }
                            
                            if(inlist($nbusc_plan_activo,'1,9')) {
                                $sql->whereActivo($nbusc_plan_activo);                                
                            }
                            
                            if($periodo_alta){
                                $sql->wherePeriodoAlta($periodo_alta);                                                       
                            }
                            if($periodo_cese){
                                $sql->wherePeriodoTermino($periodo_cese); 
                            }
                            
                            if($ftermino){
                                $sql->whereFechaTermino($ftermino);
                            }
                            if($nbusc_char)
                                $sql->whereChar($nbusc_char);
                            else

                                //se analiza la columna de busqueda
                                switch($colSearch){
                                    case 'codigo': // si se recibe el campo id
                                            $sql->whereDNI($cadena);
                                            break;

                                    default:// si se no se recibe ningun campo de busqueda
                                            if(($cadena))
                                                if(ctype_digit($cadena)) //si la cadena recibida son todos digitos
                                                    $sql->whereDNI($cadena);
                                                else
                                                    $sql->whereDescrip($cadena);
                                                break;
                                    }
                            if($nbusc_char || $nbusc_sitlaboral ||  $nbusc_clasificacion || $clear==2){
                                $sql->orderUno();
                            }else{
                                $sql->orderDos();
                            }
                        } 
                        
                        $sql=$sql->getSQL();                        
                        
//			$objResponse->addAlert($sql);

			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) setSession("cadSearch",$cadena);

			$rs = new query($conn, strtoupper($sql),$pg,100);

			//$otable = new  Table("","100%",6);
                        $otable = new TableSimple("","100%",18,'tLista'); 
                        
			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;

			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj','$nbusc_char')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj','$nbusc_char')","content");

			if ($rs->numrows()>0) {
                            if(inlist($char,'C ')){//CONCEPTO
                                        $otable->addColumnHeader("DNI","7%","C");
                                        $otable->addColumnHeader("Apellidos y Nombres","22%","C");
                                        $otable->addColumnHeader("C&oacute;d","2%","C");
                                        $otable->addColumnHeader("Concepto","18%","C");
                                        $otable->addColumnHeader("Categoria","9%","C");
                                        $otable->addColumnHeader("Espec&iacute;f","2%","C");
                                        $otable->addColumnHeader("ConAd","1%","C");
                                        $otable->addColumnHeader("%","1%","C","C");
                                        $otable->addColumnHeader("Importe","5%","C");
                                        $otable->addColumnHeader("Cuotas","1%","C");
                                        $otable->addColumnHeader("Minut","2%","C");
                                        $otable->addColumnHeader("Meses","5%","C");
                                        $otable->addColumnHeader("A&ntilde;o","1%","C");
                                        $otable->addColumnHeader("Tipo de Planilla","9%","C");
                                        $otable->addColumnHeader("Est","1%","C");
                                        $otable->addColumnHeader("Actualizado","10%","C");
                                        //$otable->addColumnHeader("","2%", "C");
                                        $otable->addRowHead(); 					
                                        while ($rs->getrow()) {
                                                /* adiciona columnas */
                                                $id=$rs->field("pers_id");
                                                
                                                $otable->addData(addLink($rs->field("dni"),"Persona_edicion.php?id=$id&clear=1&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
                                                $otable->addData(addLink($rs->field("empleado"),"Persona_edicion.php?id=$id&clear=1&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
                                                $otable->addData($rs->field("cod_concepto"));	
                                                $otable->addData($rs->field("concepto"));	
                                                $otable->addData(substr($rs->field("categoria_concepto"),0,15));
                                                $otable->addData($rs->field("clas_id"));
                                                $otable->addData($rs->field("cod_concepto_adjunto"));

                                                if($rs->field("tabl_tipoconcepto")==121){//porcentaje
                                                    $otable->addData($rs->field("pppa_porcentaje"),"C");
                                                }else{
                                                    $otable->addData("&nbsp");
                                                }
                                                if($rs->field("tabl_tipoconcepto")==122){//importe manual
                                                    $otable->addData(number_format($rs->field("pppa_importe"),2,'.',','),"R");                

                                                    if(inlist($rs->field("categoria_concepto_id"),'2,3')){ /*DESCUENTOS*/
                                                        $descuentos=$descuentos+$rs->field("pppa_importe");
                                                    }else{
                                                        $ingresos=$ingresos+$rs->field("pppa_importe");
                                                    }

                                                }else{
                                                    $otable->addData("&nbsp");
                                                }
                                                if($rs->field("pppa_ncuota_ini")){
                                                    $otable->addData($rs->field("pppa_ncuota_ini").'-'.$rs->field("pppa_ncuota_fin"),"C");
                                                }else{
                                                    $otable->addData("&nbsp;");
                                                }
                                                $otable->addData($rs->field("pppa_minutos"));                
                                                if($rs->field("mes_desde")){
                                                    $otable->addData(substr($rs->field("mes_desde"),0,3).'-'.substr($rs->field("mes_hasta"),0,3));                
                                                }else{
                                                    $otable->addData("&nbsp;");
                                                }
                                                $otable->addData($rs->field("pppa_anno"));                
                                                $otable->addData(substr($rs->field("tipo_planilla"),0,14));
                                                $otable->addData(substr($rs->field("estado"),0,3));
                                                $otable->addData($rs->field("usernameactual").'/'.$rs->field("pppa_actualfecha"));                
                                                //$otable->addData(addLink("<img src='../../img/checklist.png' border='0'>&nbsp;"."Perf.Pago","PersonaPerfilPago_lista.php?id_relacion=$id&clear=1&".$param->buildPars(false),"Click aqu&iacute; para Ingresar a Perfil de Pago"));
                                                
                                                if($rs->field("pppa_estado")==9){
                                                    $otable->addRow('ANULADO'); // adiciona linea
                                                }else{
                                                    if($rs->field("categoria_concepto_id")==2){ /*DESCUENTOS DE LEY*/
                                                        $otable->addRow('EN_PROCESO'); // adiciona linea                
                                                    }elseif($rs->field("categoria_concepto_id")==3){/*OTROS DESCUENTOS */
                                                        $otable->addRow('RECIBIDO'); // adiciona linea                                
                                                    }elseif($rs->field("categoria_concepto_id")==4){/*APORTE*/
                                                        $otable->addRow('EN_ESPERA'); // adiciona linea                                
                                                    }else{
                                                        $otable->addRow('ATENDIDO'); // adiciona linea
                                                    }
                                                }
                                                //$table->addRow(); // adiciona linea
                                        }
                                        $contenido_respuesta=$button->writeHTML();
                                        $contenido_respuesta.=$otable->writeHTML();
                                        $contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
                                        $contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total Registros: ".$rs->numrows()."</div>";

                            }else{
                                        if($clear==2){}
                                        else{
                                            $otable->addColumnHeader("<input type=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">"); // Coluna com checkbox
                                        }
					$otable->addColumnHeader("","1%"); 
					$otable->addColumnHeader("DNI","5%","C");
					$otable->addColumnHeader("Apellidos y Nombres","27%", "L","C");
					$otable->addColumnHeader("Dependencia","12%", "C");
                                        $otable->addColumnHeader("Superior","9%", "C");
					$otable->addColumnHeader("Cond.Laboral","10%", "C"); 
                                        $otable->addColumnHeader("FIngreso","3%", "C");
                                        $otable->addColumnHeader("FTerm","3%", "C");
                                        $otable->addColumnHeader("Doc/Contrato","5%", "C");
                                        $otable->addColumnHeader("Categor&iacute;a","5%", "C"); 
					$otable->addColumnHeader("Car.Funcional","14%", "C");
                                        $otable->addColumnHeader("Componen","4%", "C");
                                        $otable->addColumnHeader("R.Lab","1%", "C");
//                                        $otable->addColumnHeader("R.Pen","1%", "C");
//                                        $otable->addColumnHeader("AFP","4%", "C");
//                                        $otable->addColumnHeader("REM","4%", "C");
                                        $otable->addColumnHeader("Est","1%", "C");
                                        //$otable->addColumnHeader("","2%", "C");

					$otable->addRowHead(); 					
                                        /**/
					while ($rs->getrow()) {
						$id = $rs->field("pers_id"); // captura la clave primaria del recordsource
						$campoTexto_de_Retorno = $rs->field("empleado");
                                                //$botones=btnMenuEscalafon($rs->field("pers_dni"),$id,$param);

                                                if($clear==2){}
                                                else{
                                                    $otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");
                                                }
                                                
						if($rs->field("pers_sexo")=='M')
                                                    $otable->addData("<img src=\"../../img/spm.gif\" border=0 align=absmiddle hspace=1 alt=\"Masculino\">");
						else
                                                    $otable->addData("<img src=\"../../img/spf.gif\" border=0 align=absmiddle hspace=1 alt=\"Femenino\">");
                                                
                                                if($clear==2){
                                                    $otable->addData(addLink($rs->field("pers_dni"),"javascript:update('$id','$campoTexto_de_Retorno',0)","Click aqu&iacute; para seleccionar el registro"));   
                                                    $otable->addData(addLink($rs->field("empleado"),"javascript:update('$id','$campoTexto_de_Retorno',0)","Click aqu&iacute; para seleccionar el registro"));   
                                                }else{
                                                    if($pagEdit){
                                                        $otable->addData(addLink($rs->field("pers_dni"),"$pagEdit?id_relacion=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
                                                        $otable->addData(addLink($rs->field("empleado"),"$pagEdit?id_relacion=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
                                                    }else{
                                                        $otable->addData(addLink($rs->field("pers_dni"),"Persona_edicion.php?id_relacion=$id&clear=1&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
                                                        $otable->addData(addLink($rs->field("empleado"),"Persona_edicion.php?id_relacion=$id&clear=1&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
                                                    }
                                                }
                                                
						//$otable->addData($rs->field("empleado"));
                                                $otable->addData($rs->field("depe_id").' '.substr($rs->field("depe_nombrecorto"),0,18),"L","",$rs->field("depe_nombrecorto"));
                                                $otable->addData($rs->field("depe_superior_nombre"));
                                                $otable->addData(substr($rs->field("sit_laboral"),0,12),"L","",$rs->field("sit_laboral"));
                                                $otable->addData(dtos($rs->field("pers_fechaingreso")),"C");
                                                $otable->addData(dtos($rs->field("pers_fechacese")),"C");
                                                $otable->addData(substr($rs->field("pers_documento"),0,10),"L","",$rs->field("pers_documento"));
                                                $otable->addData($rs->field("categoria_remunerativa"));
                                                $otable->addData(substr($rs->field("pers_cargofuncional"),0,25));
                                                $otable->addData($rs->field("cadena_empleado_corta"),"C","",$rs->field("cadena_empleado"));
                                                $otable->addData(substr(str_replace('DEC. LEG.','',$rs->field("regimen_laboral")),0,5),"C","",$rs->field("regimen_laboral"));
//                                                $otable->addData(substr(str_replace('LEY','',$rs->field("regimen_pensionario")),0,5),"C","",$rs->field("regimen_pensionario"));
//                                                $otable->addData(substr(str_replace('AFP','',$rs->field("afp_nombre")),0,5),"C","",$rs->field("afp_nombre"));
//                                                $otable->addData(number_format($rs->field("remuneracion"),2,'.',','),"R");                                                                
                                                $otable->addData(substr($rs->field("estado"),0,3));
                                                
//                                                    $botones="<div class=\"dropdown\">
//                                                                <button class=\"btn btn-default dropdown-toggle btn-sm\" type=\"button\" id=\"dropdownMenu1\" data-toggle=\"dropdown\"><span class=\"glyphicon glyphicon-cog\" aria-hidden=\"true\"></span> Opciones
//                                                                <span class=\"caret\"></span></button>
//                                                                <ul class=\"dropdown-menu dropdown-menu-right\" aria-labelledby=\"dropdownMenu1\">";
//
//                                                                $botones.="<li><a href=\"".PATH_INC."auxiliar.php?pag=../../modulos/personal/PersonaPerfilPago_lista.php?id_relacion=$id&clear=1&".$param->buildPars(false)."\"  class=\"ls-modal\" target=\"content\">Perfil de Pagos</a></li>";
//                                                                $botones.="<li><a href=\"".PATH_INC."auxiliar.php?pag=../../modulos/personal/PersonaContrato_lista.php?id_relacion=$id&clear=1&".$param->buildPars(false)."\"  class=\"ls-modal\" target=\"content\">Documentos</a></li>";
//
//                                                    $botones.="</ul>
//                                                              </div>";
//                                                    $otable->addData($botones);                                                                
                                                    
                                                //$otable->addData(addLink("<img src='../../img/checklist.png' border='0'>&nbsp;"."Perf.Pago","PersonaPerfilPago_lista.php?id_relacion=$id&clear=1&".$param->buildPars(false),"Click aqu&iacute; para Ingresar a Perfil de Pago"));
                                                if($rs->field("pers_activo")==9)
                                                    $otable->addRow('ANULADO');
                                                else
                                                    $otable->addRow();
					}
				$contenido_respuesta=$button->writeHTML();
				$contenido_respuesta.=$otable->writeHTML();
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total Registros: ".$rs->numrows()."</div>";
                            }

			} else {
				$otable->addColumnHeader("!NO SE ENCONTRARON DATOS...!!","100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
				$otable->addRow();
				$contenido_respuesta=$otable->writeHTML();
			}
		}
	else{
		$contenido_respuesta="";
        }
		//se analiza el tipo de funcionamiento
		if($op==1){//si es llamado para su funcionamiento en ajax con retornoa a un div
			$objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
			$objResponse->addscript("activaSorter()"); // Para activar el orden en la tabla 
			$objResponse->addscript("func_jquerytablas()"); // Para activar las funciones de css de la tabla
                        
                        $objResponse->addScript("$(document).ready(function() {
                                                        $('.ls-modal').on('click', function(e){
                                                            e.preventDefault();
                                                            $('#myModal').modal('show').find('.modal-body').load($(this).attr('href'));
                                                        }); 
                                                    });            

                                                    window.cerrar = function(){
                                                        $('#myModal').modal('toggle');
                                                    };");                        
			return $objResponse;
			}
		else
			return $contenido_respuesta	;
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
	
                $location=$_SERVER['DOCUMENT_ROOT'] ."/docs/escalafon/".SIS_EMPRESA_RUC;
                if (!is_dir($location) && !mkdir($location, '0755', true)) {
                }
                
		include("../guardar_tipoDato.php");
	
		if ($this->valueKey) { // modificación
			$sql->setAction("UPDATE");
                        $sql_type=2;
		}else{
			$sql->setAction("INSERT");
                        $sql_type=1;
			$sql->addField('usua_id', getSession("sis_userid"), "Number");
		}


		/* Aqu� puedo agregar otros campos a la sentencia SQL */
		$this->addField($sql);
                
		/* Ejecuto el SQL */
                $sqlCommand=$sql->getSQL();
                //echo $sql->getSQL();
		$pers_id=$conn->execute("$sqlCommand  RETURNING $this->setKey");
                
		$error=$conn->error();
		if($error){ 
                         if(stristr($error,"duplicate key value")){
                             $x=substr($error,strpos($error,"constraint")+11,(strpos($error,"DETAIL")-strpos($error,"constraint"))-12);
                             $error="Valor Duplicado:".$x;
                         }
			 alert($error);	/* Muestro el error y detengo la ejecuci�n */
		}else{
                                    
                    if ($this->valueKey) {// modificación
                            if(getParam('clear')==3){//si se llama desde una ventana emergente (avanzlookup) para seleccionar un valor
                                    /* Comandos Javascript */		
                                    echo "<script language=\"javascript\">
                                                javascript:top.close();
                                         </script>";

                            }else{

                                 //include("./enviaEmail.php");

                                $last_id=$pers_id; 
                                if(strpos($destinoInsert, "?")>0)
                                    $destinoUpdate.="&id=$last_id";
                                else
                                    $destinoUpdate.="?id=$last_id";

                                redirect($destinoUpdate,"content");
                            }

                    }else{ /* Insercion */
                            /*actualuza el id de datos laboales EN LA PERSONA*/
                             $pdla_id=getDbValue("SELECT pdla_id FROM personal.persona WHERE pers_id=$pers_id");

                             if(!$pdla_id){
                                $conn->execute("  UPDATE personal.persona 
                                                    SET pdla_id=(SELECT MAX(pdla_id)
                                                                    FROM personal.persona_datos_laborales
                                                                    WHERE pers_id=$pers_id) 
                                                    WHERE pers_id=$pers_id ");
                                $error=$conn->error();
                                if($error){ 
                                    alert('2'. $error);
                                }
                             }            

                            if(getParam('clear')==2){//si se llama desde una ventana emergente (avanzlookup) para seleccionar un valor
                                    $dni=getParam('Sr_pers_dni');
                                    /* Comandos Javascript */		
                                    echo "<script language=\"javascript\">
                                                    parent.parent.content.document.forms[0].Sbusc_cadena.value = '$dni';
                                                    parent.parent.content.document.forms[0].___pers_id.value='$pers_id';
                                                    parent.parent.content.cerrar();
                                            </script>";

                            }else{ /* Si se llama desde una pagina normal */

                                    //include("./enviaEmail.php");

                                    $last_id=$pers_id; /* Obtengo el Id del registro ingreado.  Ojo esto solo funciona si el campo clave es un serial (est� basado en una secuencia) */								
                                    if(strpos($destinoInsert, "?")>0)
                                        $destinoInsert.="&id=$last_id&clear=1";  
                                    else
                                        $destinoInsert.="?id=$last_id&clear=1";

                                    redirect($destinoInsert,"content");	

                            }
                    }
                }
	}

        
        function asign_password(){
		global $conn,$param;

		$destinoDelete=$this->destinoDelete.$param->buildPars(true);		
		
		/* captura y prepara la lista de registros a ser eliminados */ 
		$arLista_asigna = getParam("sel");
		if (is_array($arLista_asigna)) {
		 $lista_asigna = implode(",",$arLista_asigna);
		}
		if(!$lista_asigna) return;

		/* Sql a ejecutar */
		$sqlCommand ="UPDATE persona SET pers_password=md5(pers_dni),usua_idpassword=".getSession("sis_userid").",usua_fechaasigna=NOW() ";
		$sqlCommand.=" WHERE $this->setKey IN ($lista_asigna) " ;

		/* Ejecuto la sentencia */
                //alert($sqlCommand);
		$padre_id=$conn->execute($sqlCommand);
		$error=$conn->error();		
		if($error) alert($error);
		else{
                    alert("Proceso Terminado!",0);	
                    redirect($destinoDelete,"content");		
		}
	}

	/* Nombre del archivo php de la clase */
	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1);
	}
} /* Fin de la clase */

class clsPersona_SQLlista extends selectSQL {
	function __construct($op=0){
		$this->sql = "SELECT a.*,
                                   a.pers_apellpaterno||' '||a.pers_apellmaterno||' '||a.pers_nombres AS empleado, ";
                if($op==2){
                    $this->sql .=  "b.basica AS remuneracion, ";
                }                           
                
                $this->sql .= " CASE WHEN a.pers_activo=1 THEN 'ACTIVO' ELSE 'DE BAJA' END AS estado,
                                   c.tabl_descripcion AS sit_laboral,
                                   c.tabl_descripcion AS sit_laboral_larga,
                                   e.cacl_descripcion AS cargo_clasificado,
                                   d.depe_nombre,d.depe_nombrecorto,
                                   (SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(a.depe_id)) AS depe_superior_nombre,
                                   f.care_descripcion AS categoria_remunerativa,
                                   g.tabl_descripcion AS tipo_documento_id,
                                   h.rela_descripcion AS regimen_laboral,
                                   i.repe_descripcion AS regimen_pensionario,
                                   j.afp_nombre,
                                   jj.comp_mnemonico||'-'||jj.peri_anno::TEXT AS cadena_empleado_corta,
                                   jj.comp_mnemonico||'-'||jj.peri_anno::TEXT||' '||jj.comp_cadena||' '||jj.comp_descripcion AS cadena_empleado,
                                   k.distrito,
                                   LPAD(a.hora_id::TEXT,3,'0')||'-'||cc.hora_descripcion as horario,
                                   x.usua_login as username,
                                   y.usua_login as usernameactual
			FROM personal.persona a ";
                
                if($op==2){
                    $this->sql .= " LEFT JOIN (SELECT SUM(a.pppa_importe) AS basica,
                                                    a.pers_id
                                                  FROM personal.persona_perfil_pago a
                                                  LEFT JOIN catalogos.servicio        b ON a.serv_codigo=b.serv_codigo
                                                  LEFT JOIN catalogos.servicio_sgrupo c ON b.sesg_id=c.sesg_id
                                                  WHERE b.sesg_id=146  /* c.sesg_descripcion='BASICA' se considera remuneracion los conceptos que pertenecen a la BASICA*/
                                                        AND a.pppa_estado=1
                                                  GROUP BY a.pers_id) b         ON a.pers_id=b.pers_id                        
                            ";
                }
                
                $this->sql .= " LEFT JOIN catalogos.tabla   c                 ON a.tabl_idsitlaboral=c.tabl_id
                                LEFT JOIN catalogos.dependencia d             ON a.depe_id=d.depe_id
                                LEFT JOIN catalogos.cargo_clasificado   e     ON a.cacl_id=e.cacl_id
                                LEFT JOIN catalogos.categoria_remunerativa f  ON a.care_id=f.care_id
                                LEFT JOIN catalogos.tabla g                   ON a.tabl_idtipodocumento=g.tabl_id
                                LEFT JOIN catalogos.regimen_laboral   h       ON a.rela_id=h.rela_id
                                LEFT JOIN catalogos.regimen_pensionario i     ON a.repe_id=i.repe_id
                                LEFT JOIN catalogos.afp j                     ON a.afp_id=j.afp_id
                                LEFT JOIN siscopp.componente                jj ON a.comp_id=jj.comp_id 
                                LEFT JOIN catalogos.view_ubigeo              k ON k.ubig_id=a.ubig_id_direccion
                                LEFT JOIN asistencia.horario                cc ON a.hora_id=cc.hora_id
                                LEFT JOIN admin.usuario x ON a.usua_id=x.usua_id
                                LEFT JOIN admin.usuario y ON a.pers_actualusua=y.usua_id
                    ";
                
                if($op==2){
                    $this->addWhere("a.pers_id>1");
                }
	}

	function whereNOAdmin(){
		$this->addWhere("a.pers_id>1");
	}

	function whereSitLaboral($sit_laboral){
		$this->addWhere("a.tabl_idsitlaboral=$sit_laboral");
	}

        function whereTipoPersona($tipo_persona){
		$this->addWhere("a.pers_tipo_persona=$tipo_persona");
	}
        
	function whereCategoria($care_id){
                if($care_id) {$this->addWhere("a.care_id=$care_id");}
	}
        
	function whereClasificacion($tabl_clasificacion){
                if($tabl_clasificacion) {$this->addWhere("a.tabl_clasificacion=$tabl_clasificacion");}
	}
        
	function whereActivo($activo=1){
		$this->addWhere("a.pers_activo=$activo");
	}
        
        function wherePeriodoAlta($periodo){
            	$this->addWhere("to_char(a.pers_fechaingreso,'YYYY')='$periodo'");
        }

        function wherePeriodoTermino($periodo){
            	$this->addWhere("to_char(a.pers_fechacese,'YYYY')='$periodo'");
        }

        function whereFechaTermino($fecha){
            	$this->addWhere("a.pers_fechacese='$fecha'");
        }
        
	function whereID($id){
		$this->addWhere("a.pers_id=$id");
	}

	function whereDNI($dni){
		$this->addWhere("a.pers_dni='$dni'");
	}
        

	function whereDepeID($depe_id){
		$this->addWhere("a.depe_id=$depe_id");
	}
        
        function whereDepeTodos($depe_id) {
            $this->addWhere("a.depe_id IN (SELECT depe_id
                                                FROM catalogos.func_treedependencia2($depe_id)
                                                WHERE depe_id>0)");
        }


        function whereChar($char){
            $this->addWhere("SUBSTR(a.pers_apellpaterno,1,1)='$char'");
        }

	function whereDescrip($descrip){
		$this->addWhere("( TRIM(a.pers_apellpaterno)||' '||TRIM(a.pers_apellmaterno)||' '||TRIM(a.pers_nombres) ILIKE '%$descrip%'
                                    OR TRIM(a.pers_nombres)||' '||TRIM(a.pers_apellpaterno)||' '||TRIM(a.pers_apellmaterno) ILIKE '%$descrip%'
                                    OR e.cacl_descripcion ILIKE '%$descrip%'
                                    OR a.pers_cargofuncional ILIKE '%$descrip%'
                                    )");
	}

	function orderUno(){
		$this->addOrder("a.pers_apellpaterno,a.pers_apellmaterno,a.pers_nombres DESC");
	}
        
        function orderDos(){
		$this->addOrder("a.pers_id DESC");
	}

        function orderTres(){
		$this->addOrder("a.tabl_idsitlaboral,a.pers_apellpaterno,a.pers_apellmaterno,a.pers_nombres");
	}
        
        function getSQL_cbox(){
            $sql="SELECT DISTINCT a.pers_id AS id,a.pers_apellpaterno||' '||a.pers_apellmaterno||' '||a.pers_nombres||' ('||a.pers_dni||')' AS descripcion 
				FROM (".$this->getSQL().") AS a ORDER BY 2 DESC ";
            return $sql;
        }
        
        function getSQL_cboxPeriodoAlta(){
            $sql="SELECT DISTINCT to_char(a.pers_fechaingreso,'YYYY') AS id,to_char(a.pers_fechaingreso,'YYYY') as descripcion 
				FROM (".$this->getSQL().") AS a 
                    WHERE a.pers_fechaingreso IS NOT NULL 
                    ORDER BY 1 DESC";
            return $sql;
        }            
        
        function getSQL_cboxPeriodoTermino(){
            $sql="SELECT DISTINCT to_char(a.pers_fechacese,'YYYY') AS id,to_char(a.pers_fechacese,'YYYY') as descripcion 
				FROM (".$this->getSQL().") AS a 
                    WHERE a.pers_fechacese IS NOT NULL 
                    ORDER BY 1 DESC";
            return $sql;
        }                            

}

function btnImprimirPersona(){

    global $conn;
    /*OBTENGO EL SQL DE LAS OPCIONES DEL MENU ESCALAFON*/
    $sistemaModulo=new sistemaModuloOpciones(2);
    $sistemaModulo->whereSistID('01ESCALAFO');
    $sistemaModulo->whereAcceso(7);
    if(getSession("sis_userid")>1){
        $sistemaModulo->whereUserID(getSession("sis_userid"));
    }
    $sistemaModulo->orderUno();
    $sqlImprimir=$sistemaModulo->getSQL();
    $rsImprimir=new query($conn, $sqlImprimir);
    $botones="";
    
    if($rsImprimir->numrows()>0){
        $botones="<div class=\"dropdown\">
                <button class=\"btn btn-default dropdown-toggle btn-sm\" type=\"button\" id=\"dropdownMenu1\" data-toggle=\"dropdown\"><span class=\"glyphicon glyphicon-print\" aria-hidden=\"true\"></span> Imprimir
                <span class=\"caret\"></span></button>
                <ul class=\"dropdown-menu dropdown-menu-right\" aria-labelledby=\"dropdownMenu1\">";

        while ($rsImprimir->getrow()) {
            $botones.="<li><a href=\"#\" onClick=\"javascript:beforeImprimir('".$rsImprimir->field('smop_id')."','".$rsImprimir->field('smop_page')."')\" target=\"controle\">".$rsImprimir->field('smop_descripcion')."</a></li>";
        }

        $botones.="</ul>
                   </div>";
    }          
    
    $rsImprimir->free();
    return($botones);
}

////////


function btnMenuEscalafon($titulo_boton,$id,$param){

    global $conn;
    /*OBTENGO EL SQL DE LAS OPCIONES DEL MENU ESCALAFON*/
    $sistemaModulo=new sistemaModuloOpciones(2);
    $sistemaModulo->whereSistID('02ESCALAFO');
    $sistemaModulo->whereAcceso(8);
    if(getSession("sis_userid")>1){
        $sistemaModulo->whereUserID2(getSession("sis_userid"));
    }
    $sistemaModulo->orderUno();
    $sqlOpciones=$sistemaModulo->getSQL();
    //ECHO $sqlOpciones;
    $rsOpciones=new query($conn, $sqlOpciones);
    
    if($rsOpciones->numrows()>0){
        $botones="<div class=\"dropdown\">
                    <button class=\"btn btn-default dropdown-toggle btn-sm\" type=\"button\" id=\"dropdownMenu1\" data-toggle=\"dropdown\">&nbsp;&nbsp;".$titulo_boton."&nbsp;&nbsp;
                    <span class=\"caret\"></span></button>
                    <ul class=\"dropdown-menu dropdown-menu-right\" aria-labelledby=\"dropdownMenu1\">";

        while ($rsOpciones->getrow()) {
            $botones.="<li><a href=\"".$rsOpciones->field('smop_page')."?id_relacion=$id&clear=1&".$param->buildPars(false)."\"  target=\"content\">".$rsOpciones->field('smop_descripcion')."</a></li>";
        }

        $botones.="</ul>
                   </div>";
    }else{
        $botones=$titulo_boton;
    }          
    
    $rsOpciones->free();
    return($botones);
}

/* Llamando a la subclase */
if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
            require_once($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");

            /*	verificación a nivel de usuario */
            verificaUsuario(1);
            verif_framework();

            $param= new manUrlv1();
            $param->removePar('control');

            //	conexión a la BD
            $conn = new db();
            $conn->open();

            $dml=new clsPersona();
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

                    case 4: // Eliminar
                            $dml->asign_password();
                            break;
                        
            }
            //	cierra la conexión con la BD
            $conn->close();
    }
}