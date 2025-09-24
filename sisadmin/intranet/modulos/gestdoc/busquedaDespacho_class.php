<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class despachoBusqueda extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='despachos_derivaciones'; //nombre de la tabla
		$this->setKey='dede_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

		/* Destinos luego de actulizar o agregar un registro */
		$this->destinoUpdate = "procesoDespacho_buscar.php";
		$this->destinoInsert = "procesoDespacho_buscar.php";
		$this->destinoDelete = "procesoDespacho_buscar.php";
	}


	function buscar($op,$formData,$arrayParam,$pg,$Nameobj='')

	{
		global $conn,$param;
		$objResponse = new xajaxResponse();
		$arrayParam=decodeArray($arrayParam);

		$paramFunction= new manUrlv1();
		$paramFunction->setUrlExternal($arrayParam);

		$cadena=is_array($formData)?trim(strtoupper($formData['Sbusc_cadena'])):$formData;
		if(!$cadena && $op==2) $cadena=getSession("cadSearch");
		
		$colSearch=$paramFunction->getValuePar('colSearch');
		$colOrden=$paramFunction->getValuePar('colOrden');
		$busEmpty=$paramFunction->getValuePar('busEmpty');

                $nbusc_tipo_despacho=is_array($formData)?$formData['nbusc_tipo_despacho']:$paramFunction->getValuePar('nbusc_tipo_despacho');
		$nbusc_depe_id=is_array($formData)?$formData['nbusc_depe_id']:$paramFunction->getValuePar('nbusc_depe_id');
                
                $nbusc_depe_id_procede=is_array($formData)?$formData['nbusc_depe_id_procede']:$paramFunction->getValuePar('nbusc_depe_id_procede');
                $nbusc_depe_id_destino=is_array($formData)?$formData['nbusc_depe_id_destino']:$paramFunction->getValuePar('nbusc_depe_id_destino');
                
                $nbusc_proc_id=is_array($formData)?$formData['nbusc_proc_id']:$paramFunction->getValuePar('nbusc_proc_id');
                $nbusc_user_id=is_array($formData)?$formData['nbusc_user_id']:$paramFunction->getValuePar('nbusc_user_id');
                
                $nbusc_user_id_origen=is_array($formData)?$formData['nbusc_user_id_origen']:$paramFunction->getValuePar('nbusc_user_id_origen');
                $nbusc_user_id_destino=is_array($formData)?$formData['nbusc_user_id_destino']:$paramFunction->getValuePar('nbusc_user_id_destino');
                
                $nbusc_tiex_id=is_array($formData)?$formData['nbusc_tiex_id']:$paramFunction->getValuePar('nbusc_tiex_id');

                $nbusc_estado=is_array($formData)?$formData['nbusc_estado']:$paramFunction->getValuePar('nbusc_estado');
                $tipo_formato=is_array($formData)?$formData['tipo_formato']:$paramFunction->getValuePar('tipo_formato');
                
                $nbusc_fdesde=is_array($formData)?$formData['nbusc_fdesde']:$paramFunction->getValuePar('nbusc_fdesde');
                $nbusc_fhasta=is_array($formData)?$formData['nbusc_fhasta']:$paramFunction->getValuePar('nbusc_fhasta');
                $nbusc_numero=is_array($formData)?$formData['nbusc_numero']:$paramFunction->getValuePar('nbusc_numero');

                $param->removePar('clear');
                $param->removePar('nbusc_tipo_despacho');
                $param->addParComplete("nbusc_tipo_despacho", $nbusc_tipo_despacho);
                $param->removePar('nbusc_depe_id');
                $param->addParComplete("nbusc_depe_id", $nbusc_depe_id);
                
                $param->removePar('nbusc_depe_id_procede');
                $param->addParComplete("nbusc_depe_id_procede", $nbusc_depe_id_procede);
                
                $param->removePar('nbusc_depe_id_destino');
                $param->addParComplete("nbusc_depe_id_destino", $nbusc_depe_id_destino);
                
                $param->removePar('nbusc_proc_id');
                $param->addParComplete("nbusc_proc_id", $nbusc_proc_id);
                $param->removePar('nbusc_tiex_id');
                $param->addParComplete("nbusc_tiex_id", $nbusc_tiex_id);
                $param->removePar('nbusc_estado');
                $param->addParComplete("nbusc_estado", $nbusc_estado);

		if(strlen($cadena)>0 || 
                        $nbusc_depe_id>0 || 
                        $nbusc_depe_id_procede>0 || 
                        $nbusc_depe_id_destino>0 || 
                        $nbusc_user_id>0 || 
                        $nbusc_user_id_origen>0 ||
                        $nbusc_user_id_destino>0 ||
                        $nbusc_estado || 
                        $nbusc_fdesde || 
                        $nbusc_fhasta || 
                        $nbusc_proc_id){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	
                        //ojo esta funcion se encuntra en 'registroDespacho_class.php'
			$sql=new despachoDerivacion_SQLlista(2);
                        
                        if($nbusc_tipo_despacho){
                            $sql->whereTDespacho($nbusc_tipo_despacho);
                        }
                        
                        
                        if($nbusc_depe_id_procede){
                            $sql->whereDepeOrigen($nbusc_depe_id_procede);
                        }
                        
                        if($nbusc_depe_id_destino){
                            $sql->whereDepeDestino($nbusc_depe_id_destino);
                        }

                        if($nbusc_depe_id){
                            $sql->whereDepeOrig_Destino($nbusc_depe_id);
                        }
                        
                        if($nbusc_proc_id){
                            $sql->whereProcedimiento($nbusc_proc_id);
                        }
                            
                        if($nbusc_user_id){
                            $sql->whereUsuaOrig_Destino($nbusc_user_id);
                        }
                        
                        
                        if($nbusc_user_id_origen){
                            $sql->whereUsuaOrigen($nbusc_user_id_origen);
                        }
                        
                        if($nbusc_user_id_destino){
                            $sql->whereUsuaDestino($nbusc_user_id_destino);
                        }
                        
                        if($nbusc_tiex_id){
                            $sql->whereTExpediente($nbusc_tiex_id);
                        }
                        
                        if($nbusc_estado){
                            $sql->whereEstado($nbusc_estado);
                        }
                        
                        if($nbusc_fdesde){
                            $sql->whereFechaDesde($nbusc_fdesde);
                        }
                        
                        if($nbusc_fhasta){
                            $sql->whereFechaHasta($nbusc_fhasta);
                        }
                        
                        if($nbusc_numero){
                            $sql->whereNumero($nbusc_numero);
                        }                        
			//se analiza la columna de busqueda
                        if($cadena){
                            switch($colSearch){
                                    case 'codigo': // si se recibe el campo id
                                            $sql->whereID($cadena);
                                            break;

                                    default:// si se no se recibe ningun campo de busqueda
                                            if(is_numeric($cadena)) //si la cadena recibida son todos digitos
                                                $sql->wherePadreID($cadena);
                                            else
                                                $sql->whereDescripVarios($cadena);
                                            break;
                            }
                        }
			
			
			
                        if($tipo_formato==1){//FORMATO RESUMEN
                            $sql="SELECT DISTINCT a.id_padre,
                                                 a.desp_fecha,
                                                 a.tiex_abreviado,
                                                 a.num_documento,
                                                 a.desp_asunto,
                                                 a.desp_firma
                                   FROM (".$sql->getSQL()." ) AS a
                                   ORDER BY a.desp_fecha DESC
                                   ";


                        }else{
                            $sql->orderDos();                            
                            $sql=$sql->getSQL();
                        }
                        //echo $sql;
                        
			//$objResponse->addAlert($sql);
		
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) setSession("cadSearch",$cadena);			
	
			$rs = new query($conn, strtoupper($sql),$pg,80);

			$otable = new  Table("","100%",10);
                        //$otable->setDataFONT('DataFONT2');

			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
					$otable->addColumnHeader("<input type=\"checkbox\" class=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">"); // Coluna com checkbox
					$otable->addColumnHeader(NAME_EXPEDIENTE,false,"4%", "L");
					$otable->addColumnHeader("Di",false,"1%", "L");
                                        $otable->addColumnHeader("Fecha",false,"5%", "L");
					$otable->addColumnHeader("TExp",false,"4%", "L"); 
					$otable->addColumnHeader("N&uacute;mero",false,"20%", "L"); 
					$otable->addColumnHeader("Asunto",false,"20%", "L");
                                        $otable->addColumnHeader("Firma",false,"15%", "L");
					$otable->addColumnHeader("Procedencia",false,"14%", "L");
					$otable->addColumnHeader("Destino",false,"14%", "L");
					$otable->addColumnHeader("Estado",false,"2%", "L"); 
                                        $otable->addColumnHeader("",false,"1%", "L"); 
					
					$otable->addRow(); // adiciona la linea (TR)
					while ($rs->getrow()) {
						$idPadre = $rs->field("id_padre"); // captura la clave de la tabla padre

                                                $id=$rs->field("dede_id");
						$campoTexto_de_Retorno = especialChar($rs->field("tiex_descripcion"));
                                                $registro=$idPadre."_".$id;
						$otable->addData("<input type=\"checkbox\" class=\"checkbox\" name=\"sel[]\" value=\"$registro\" onclick=\"checkform(frm,this)\">");

                                                //$otable->addData(addLink($idPadre,"javascript:abreConsulta('$idPadre')","Click aqu&iacute; para Seguimiento de registro"));
                                                $otable->addData(addLink($idPadre,"javascript:lectorPDF('/sisadmin/gestdoc/controllers/procesar_data.php?nr_numTramite=$idPadre&vista=NoConsulta','Seguimiento')","Click aqu&iacute; para Seguimiento de registro"));                                                
                                                
                                                
                                                if($rs->field("dede_concopia")){
                                                    $otable->addData("Cc");
                                                }else{
                                                    $otable->addData("");
                                                }
                                                
						$otable->addData(dtos($rs->field("desp_fecha")));

						$otable->addData($rs->field("tiex_abreviado"));
						$otable->addData($rs->field("num_documento"));

                                                //$depeid=getSession("sis_depeid");
						$otable->addData(addLink(substr($rs->field("desp_asunto"),0,40)."...<img src=\"../../img/mas_info.gif\" width=\"14\" height=\"9\" align=\"absmiddle\" border=\"0\">","javascript:xajax_verDetalle(1,'$idPadre','busquedas','1','',0);return false;","Click aqu&iacute; para ver detalles del registro"));
                                                $otable->addData($rs->field("desp_firma"));

                                                //$otable->addData($rs->field("desp_asunto"));
                                                if($rs->field("depe_nombrecorto_origen")){
                                                    $otable->addData($rs->field("depe_nombrecorto_origen").'/'.$rs->field("depe_superior_nombre_origen"));
                                                }else{
                                                    $otable->addData("&nbsp;");
                                                }
                                                if($rs->field("depe_nombrecorto_destino")){
                                                    $otable->addData($rs->field("depe_nombrecorto_destino").'/'.$rs->field("depe_superior_nombre_destino"));
                                                }else{
                                                    $otable->addData("&nbsp;");
                                                }
                                                $adjuntadoID=$rs->field('desp_adjuntadoid');
                                                if($adjuntadoID){
                                                    $otable->addData('ADJUNT.AL REG:'.$adjuntadoID,"C");
                                                }else{                                                
                                                    $otable->addData($rs->field('estado'),"C");
                                                }
                                                
                                                $otable->addData(btnSeguimientos($idPadre));
                                                
                                                if($rs->field('dede_estado')==3){//recibido
                                                    $otable->addRow('ATENDIDO');
                                                }
                                                elseif($rs->field('dede_estado')==4){//derivado
                                                    $otable->addRow('RECIBIDO');
                                                }
                                                elseif($rs->field('dede_estado')==6){//Archivado
                                                    $otable->addRow('ANULADO');
                                                }
                                                elseif($rs->field('dede_estado')==7){//Activado
                                                    $otable->addRow('ATENDIDO');
                                                }
                                                else{
                                                    $otable->addRow();
                                                }
					}
				$contenido_respuesta=$button->writeHTML();
				$contenido_respuesta.=$otable->writeHTML();
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

			} else {
				$otable->addColumnHeader("!NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
				$otable->addRow();
				$contenido_respuesta=$otable->writeHTML();
			}
		}
	else
		$contenido_respuesta="Seleccione Criterio de B&uacute;squeda...";
	
		//se analiza el tipo de funcionamiento
		if($op==1){//si es llamado para su funcionamiento en ajax con retornoa a un div
			$objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
			return $objResponse;
			}
		else
			return $contenido_respuesta	;
	}


	function buscarPublico($op,$formData,$arrayParam,$pg,$Nameobj='')
	{
		global $conn;
		$objResponse = new xajaxResponse();

                $nbusc_tipo_despacho=$formData['nbusc_tipo_despacho'];
		$nbusc_depe_id=$formData['nbusc_depe_id'];
                $nbusc_tiex_id=$formData['nbusc_tiex_id'];

                $nbusc_fdesde=$formData['nbusc_fdesde'];
                $nbusc_fhasta=$formData['nbusc_fhasta'];
                $nbusc_dni_ruc=$formData['nbusc_dni_ruc'];
                
                $nbusc_numero=$formData['nbusc_numero'];
                $cadena=$formData['Sbusc_cadena'];    
                //echo $formData['nbusc_numero'];
		if(strlen($cadena)>0 or $nbusc_depe_id>0 or $nbusc_fdesde or $nbusc_fhasta or $nbusc_dni_ruc or $nbusc_numero>0){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	
                        //ojo esta funcion se encuntra en 'registroDespacho_class.php'
			$sql=new despachoDerivacion_SQLlista(2);
                        
                        //sies publico, entonces solo externos
                        
                        //SI VIENE DE DOMINIKO
//                        if(strpos($_SERVER['SERVER_NAME'],".gob.pe",0)>0){
                            $sql->whereTDespacho(142); //OTRAS ENTIDADES (EXTERNO)
//                        }
                       
                        if($nbusc_tiex_id){
                            $sql->whereTExpediente($nbusc_tiex_id);
                        }
                        
                        if($nbusc_fdesde){
                            $sql->whereFechaDesde($nbusc_fdesde);
                        }
                        
                        if($nbusc_fhasta){
                            $sql->whereFechaHasta($nbusc_fhasta);
                        }
                        
                        if($nbusc_dni_ruc){
                            $sql->whereCodigo($nbusc_dni_ruc);
                        }
                                
                        if($nbusc_numero){
                            $sql->whereNumero($nbusc_numero);
                        }
                        
                                
                                
			//se analiza la columna de busqueda
                        if($cadena)
                        {
                            if(is_numeric($cadena))
                            {
                                $sql->wherePadreID($cadena);
                            }
                            else
                            {
                                $sql->whereDescripVarios($cadena);
                            }
			}
                        
			$sql->orderDos();
			
			$sql=$sql->getSQL();
			
			$rs = new query($conn, strtoupper($sql),$pg,80);
			$otable = new  Table("","100%",7,true);
                        //$otable->setFormTABLE("table-responsive");
                        //$otable->setDataFONT('DataFONT2');


			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
                                        $otable->addColumnHeader("",false,"1%", "L"); 
					$otable->addColumnHeader(NAME_EXPEDIENTE,false,"4%", "L"); 
                                        $otable->addColumnHeader("Fecha",false,"5%", "L");
					$otable->addColumnHeader("TExp",false,"4%", "L"); 
					$otable->addColumnHeader("N&uacute;mero",false,"30%"); 
					$otable->addColumnHeader("Asunto",false,"60%", "L"); 
					$otable->addColumnHeader("Est",false,"2%", "L"); 
					$otable->addRow(); // adiciona la linea (TR)
					while ($rs->getrow()) {
						$idPadre = $rs->field("id_padre"); // captura la clave de la tabla padre
                                                $id=$rs->field("dede_id");
                                                $otable->addData("<input type=\"button\" value=\"Ver\" id=\"adi_$id\" onClick=\"javascript:abreConsulta('$idPadre')\">");

                                                $otable->addData($idPadre);
                                                
						$otable->addData(dtos($rs->field("desp_fecha")));

						$otable->addData($rs->field("tiex_abreviado"));
						$otable->addData($rs->field("num_documento"));

                                                //$depeid=getSession("sis_depeid");
						$otable->addData(substr($rs->field("desp_asunto"),0,40)."...<img src=\"../../img/mas_info.gif\" width=\"14\" height=\"9\" align=\"absmiddle\" border=\"0\">");

                                                $adjuntadoID=$rs->field('desp_adjuntadoid');
                                                if($adjuntadoID){
                                                    $otable->addData('ADJUNT.AL REG:'.$adjuntadoID,"C");
                                                }else{                                                
                                                    $otable->addData($rs->field('estado'),"C");
                                                }
                                                
                                                if($rs->field('dede_estado')==3){//recibido
                                                    $otable->addRow('ATENDIDO');
                                                }
                                                elseif($rs->field('dede_estado')==4){//derivado
                                                    $otable->addRow('RECIBIDO');
                                                }
                                                elseif($rs->field('dede_estado')==6){//Archivado
                                                    $otable->addRow('ANULADO');
                                                }
                                                elseif($rs->field('dede_estado')==7){//Activado
                                                    $otable->addRow('ATENDIDO');
                                                }
                                                else{
                                                    $otable->addRow();
                                                }
					}
				$contenido_respuesta=$button->writeHTML();
				$contenido_respuesta.=$otable->writeHTML();
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

			} else {
				$otable->addColumnHeader("!NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
				$otable->addRow();
				$contenido_respuesta=$otable->writeHTML();
			}
		}
	else
		$contenido_respuesta="Seleccione Criterio de B&uacute;squeda...";
	
		//se analiza el tipo de funcionamiento
		if($op==1){//si es llamado para su funcionamiento en ajax con retornoa a un div
			$objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
			return $objResponse;
			}
		else
			return $contenido_respuesta	;
	}
        
	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	
}


function btnSeguimientos($desp_id){
    $botones="<div class=\"dropdown\">
                <button class=\"btn btn-default dropdown-toggle btn-sm\" type=\"button\" id=\"dropdownMenu1\" data-toggle=\"dropdown\"><span class=\"glyphicon glyphicon-cog\" aria-hidden=\"true\"></span> Opciones
                <span class=\"caret\"></span></button>
                <ul class=\"dropdown-menu dropdown-menu-right\" aria-labelledby=\"dropdownMenu1\">";

    $botones.="<li><a href=\"#\" onClick=\"javascript:xajax_seguir('$desp_id')\" target=\"controle\">Seguir</a></li>";
    
    
    $botones.="</ul>
              </div>";
    return($botones);    
}

if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
            include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
            /*	verificaci�n a nivel de usuario */
            verificaUsuario(1);
            verif_framework();

            $param= new manUrlv1();
            $param->removePar('control');
            $param->removePar('relacionamento_id');
            $param->removePar('pg'); /* Remuevo el par�metro */

            /* Recibo la página actual de la lista y lo agrego como par�metro a ser enviado por la URL.  Este dato viene por POST */
            $pg = getParam("pg");
            $param->addParComplete('pg',$pg); /* Agrego el par�metro */

            //	conexión a la BD
            $conn = new db();
            $conn->open();

            $dml=new despachoBusqueda();

            switch($control){
                    case 1: // Guardar
                            $dml->guardar();
                            break;

                    case 2: // Eliminar
                            $dml->eliminarMisDerivaciones(); //eliminar mis derivaciones
                            break;
            }
            //	cierra la conexión con la BD
            $conn->close();
    }
}