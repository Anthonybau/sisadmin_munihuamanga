<?php
require_once($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/clases/entidad.php");

class despachoProceso extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='gestdoc.despachos_derivaciones'; //nombre de la tabla
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

                if($op==5){
                    
                    $cadena=trim(strtoupper($formData));
                    $nbusc_depe_id=$arrayParam;
                    $incluir_registrados=0;
                    
                }else{
                    $arrayParam=decodeArray($arrayParam);
                    $paramFunction= new manUrlv1();
                    $paramFunction->setUrlExternal($arrayParam);

                    $cadena=is_array($formData)?trim(strtoupper($formData['Sbusc_cadena'])):$formData;
                    if(!$cadena && $op==2) $cadena=getSession("cadSearch");

                    $colSearch=$paramFunction->getValuePar('colSearch');
                    $colOrden=$paramFunction->getValuePar('colOrden');
                    $busEmpty=$paramFunction->getValuePar('busEmpty');
                    $nbusc_depe_id=is_array($formData)?$formData['nbusc_depe_id']:$paramFunction->getValuePar('nbusc_depe_id');
                    $nbusc_user_id=is_array($formData)?$formData['nbusc_user_id']:$paramFunction->getValuePar('nbusc_user_id');
                    $incluir_registrados=is_array($formData)?$formData['hx_incluir_registrados']:$paramFunction->getValuePar('hx_incluir_registrados');


                    if($incluir_registrados==1 || $incluir_registrados==='true'){
                        $incluir_registrados=1;
                    }else{
                        $incluir_registrados=0;
                    }

                    $param->removePar('clear');
                    $param->removePar('nbusc_depe_id');
                    $param->addParComplete("nbusc_depe_id", $nbusc_depe_id);
                    $param->removePar('nbusc_user_id');
                    $param->addParComplete("nbusc_user_id", $nbusc_user_id);
                    $tiex_id=is_array($formData)?$formData['nbusc_tiex']:$paramFunction->getValuePar('nbusc_tiex');
                    $indicador=is_array($formData)?$formData['nbusc_indicador']:$paramFunction->getValuePar('nbusc_indicador');
                }

		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	
			$sql=new despachoProceso2_SQLlista();
			$sql->whereDepeDestinoID($nbusc_depe_id);
                        $sql->whereNOAdjuntados();
                        //$sql->whereEstado(3);
                        $sql->whereProceso();
                        
                        if($nbusc_user_id){
                            $sql->whereUsuaRecibeID($nbusc_user_id);
			}
                        if($indicador==1){
                            $sql->whereSemaforo1();
                        }elseif($indicador==2){
                            $sql->whereSemaforo2();
                        }elseif($indicador==3){
                            $sql->whereSemaforo3();
                        }
                        
			if($tiex_id){
			    $sql->whereTiExpID($tiex_id);
			}

			//se analiza la columna de busqueda
                        if($cadena){
                            switch($colSearch){
				case 'codigo': // si se recibe el campo id
					$sql->whereID($cadena);
					break;
	
				default:// si se no se recibe ningun campo de busqueda
					if(is_numeric($cadena)){ //si la cadena recibida son todos digitos
                                            $sql->whereID($cadena);
                                        }elseif(strpos($cadena,",")>0){
                                            $sql->whereIDPadreVarios($cadena);
                                        }else{
                                            $sql->whereDescrip($cadena);
                                        }
					break;
				}
                        }
			$sql->orderUno();
			
                        if($op==3 || $incluir_registrados==1){
                            if(is_numeric($cadena)){                         
                                $sql=$sql->getSQL_enProceso($cadena,1);
                            }elseif(strpos($cadena,",")>0){
                                $sql=$sql->getSQL_enProceso($cadena,2);
                            }else{
                                $sql=$sql->getSQL_enProceso(0,1);
                            }
                        }else{
                            $sql=$sql->getSQL();
                        }
					
			//$objResponse->addAlert($sql);
		
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) setSession("cadSearch",$cadena);			
	
			$rs = new query($conn, strtoupper($sql),$pg, 40);
			
                        $otable = new  Table("","100%",12,true,'en_proceso');
                        
			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
                                        if($op==5){
                                            $otable->addColumnHeader("");                                            
                                        }else{
                                            $otable->addColumnHeader("<input type=\"checkbox\" class=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">"); // Coluna com checkbox
                                        }
					$otable->addColumnHeader(NAME_EXPEDIENTE,false,"4%", "L",""); 
                                        $otable->addColumnHeader("I",false,"1%", "C","","Indicador"); 
					$otable->addColumnHeader("Di",false,"2%", "L",""); 
					$otable->addColumnHeader("Fecha",false,"5%", "C",""); 
					$otable->addColumnHeader("TExp",false,"4%", "L",""); 
					$otable->addColumnHeader("N&uacute;mero",false,"20%", "C",""); 
                                        $otable->addColumnHeader("Procedencia:",false,"20%", "C"); 
					$otable->addColumnHeader("Asunto",false,"40%", "C","");
                                        $otable->addColumnHeader("Adjuntados",false,"10%", "C",""); 
					$otable->addColumnHeader("Recibe",false,"4%", "L",""); 
					$otable->addColumnHeader("",false,"5%", "L");
					$otable->addRow(); // adiciona la linea (TR)
					while ($rs->getrow()) {
						$idPadre = $rs->field("id"); // captura la clave de la tabla padre
                                                $desp_expediente = $rs->field("desp_expediente");
                                                $id=$rs->field("dede_id");
                                                $idPedido=$rs->field("pedi_id");
						$campoTexto_de_Retorno = especialChar($rs->field("tiex_descripcion"));
                                                $registro=$idPadre."_".$id;
                                                $proc_validar=$rs->field("proc_validar");
                                                $dias_en_proceso=$rs->field("dias_en_proceso");
                                                
                                                if($op==5){
                                                    $buttonx = new Button;
                                                    $buttonx->setDiv(FALSE);
                                                    $buttonx->setStyle("");
                                                    $buttonx->addItem("Elegir","javascript:xajax_elijeExpediente('$registro','$desp_expediente',1)","content",2,0,"botonAgg","button","","btn_$id");
                                                    $otable->addData($buttonx->writeHTML());	
                                                    $otable->addData($idPadre);
                                                }else{
                                                    $otable->addData("<input type=\"checkbox\" class=\"checkbox\" name=\"sel[]\" value=\"$registro\" onclick=\"checkform(frm,this)\">");
                                                    $otable->addData(addLink($idPadre,"javascript:lectorPDF('/sisadmin/gestdoc/controllers/procesar_data.php?nr_numTramite=$idPadre&vista=NoConsulta','Seguimiento')","Click aqu&iacute; para Seguimiento de registro"));
                                                    //$otable->addData(addLink($idPadre,"javascript:abreConsulta('$idPadre')","Click aqu&iacute; para Seguimiento de registro"));
                                                }
                                                
                                                
                                                if($rs->field("semaforo1")>0){
                                                    $otable->addData("<button type=\"button\" class=\"btn btn-success btn-xs\">$dias_en_proceso</button>","C","","","D&iacute;as em Proceso");
                                                }elseif($rs->field("semaforo2")>0){
                                                    $otable->addData("<button type=\"button\" class=\"btn btn-warning btn-xs\">$dias_en_proceso</button>","C","","","D&iacute;as em Proceso");
                                                }elseif($rs->field("semaforo3")>0){
                                                    $otable->addData("<button type=\"button\" class=\"btn btn-danger btn-xs\">$dias_en_proceso</button>","C","","","D&iacute;as em Proceso");
                                                }else{
                                                    $otable->addData("");
                                                }

                                                
                                                if($rs->field("dede_concopia")){
                                                    $otable->addData("Cc");
                                                }else{
                                                    $otable->addData("");
                                                }
                                                
                                                $otable->addData(dtos($rs->field("desp_fecha")));
						$otable->addData($rs->field("tiex_abreviado"));
                                                
                                                if(inlist($rs->field('tiex_abreviado'),"SB,SS,SSM")){
                                                    $otable->addData(addLink($rs->field("num_documento"),"javascript:imprimirPedido('$idPedido')","Click aqu&iacute; para Seguimiento de Solicitud"));
                                                }else{        
                                                    $otable->addData($rs->field("num_documento"));
                                                }
                                                $otable->addData($rs->field("depe_nombrecorto_origen"));

                                                $depeid=getSession("sis_depeid");
                                                if($op==5){
                                                    $otable->addData(substr($rs->field("desp_asunto"),0,40));    
                                                }else{
                                                    $otable->addData(addLink(substr($rs->field("desp_asunto"),0,40)."...<img src=\"../../img/mas_info.gif\" width=\"14\" height=\"9\" align=\"absmiddle\" border=\"0\">","javascript:xajax_verDetalle(1,'$idPadre','misDerivaciones',$depeid,'$id',0)","Click aqu&iacute; para ver detalles del registro"));
                                                }
                                                $otable->addData($rs->field("desp_adjuntados_exp"));
                                                $otable->addData($rs->field("usuario_recibe2"));
                                                
                                                if($op==5 || $rs->field("orden")==2){
                                                    $otable->addData("&nbsp;");
                                                }else{
                                                    $otable->addData(btnDespacho($registro,$idPadre,$nbusc_depe_id,$id,$desp_expediente,$proc_validar));
                                                }
                                                
                                                if($rs->field("dede_acum_derivaciones")>0){
                                                    $otable->addRow('ATENDIDO');
                                                }else{
                                                    if($rs->field("pedi_estado2")==2){
                                                        $otable->addRow('OBLIGATORIO');
                                                    }else{
                                                        $otable->addRow();
                                                     }
                                                }
					}
				$contenido_respuesta=$button->writeHTML();
				$contenido_respuesta.=$otable->writeHTML();
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

			} else {
				$otable->addColumnHeader("!NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); 
				$otable->addRow();
				$contenido_respuesta=$otable->writeHTML();
			}
		}
	else
		$contenido_respuesta="";
	
		//se analiza el tipo de funcionamiento
		if($op==1 || $op==3 || $op==5){///si es llamado para su funcionamiento en ajax con retornoa a un div
                    $objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
                    $objResponse->addScript("tb_init('a.thickbox');");
                    return $objResponse;
		}
		else{
                    return $contenido_respuesta;
                }
	}

        function eliminarMisDerivaciones(){
		global $conn,$param;
                $nbusc_user_id=getParam("nbusc_user_id");
                $param->removePar('nbusc_user_id');
                $param->addParComplete("nbusc_user_id", $nbusc_user_id);

		$destinoDelete=$this->destinoDelete.$param->buildPars(true)."&clear=1";

		/* captura y prepara la lista de registros a ser eliminados */
		$arLista_elimina = getParam("sel");
		
                if(!is_array($arLista_elimina)) {
                    alert('Sin registros seleccionados para procesar...');
                    return;
                }

                $lista_elimina='';
                for ($i = 0; $i < count($arLista_elimina); $i++) {
                    $arrayPadreHijo=explode('_',$arLista_elimina[$i]);
                    $lista_elimina.=iif($lista_elimina,'==','','',',').$arrayPadreHijo[0];
                }


		/* Sql a ejecutar */
		$sqlCommand ="DELETE FROM $this->setTable WHERE desp_id ";
		$sqlCommand.=" IN ($lista_elimina) " ;
		$sqlCommand.=" AND usua_idcrea=".getSession("sis_userid"). " AND dede_estado=2 AND dede_donde_se_creo=1" ;
                $sqlCommand.=" RETURNING $this->setKey ";

		/* Ejecuto la sentencia */
                //alert($sqlCommand);
		$padre_id=$conn->execute($sqlCommand);
		$error=$conn->error();
		if($error) alert($error);
		else{
                        if ($this->setNivelAudita>0){
                            //ejecuto el insert en la tabla auditoria
                            $sql = new UpdateSQL();
                            $sql->setTable($this->setTable.'_auditoria');
                            $sql->setAction("INSERT");
                            $sql->addField('sql_type',3,"Number");
                            $sql->addField('sql_command',$sqlCommand);
                            $sql->addField('usua_id', getSession("sis_userid"), "Number");
                            $conn->execute($sql->getSQL());
                            $error=$conn->error();
                            if($error){alert($error);}/* Muestro el error y detengo la ejecución */
                        }
                         //alert($destinoDelete);
			redirect($destinoDelete,"content");
		}
	}

	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	
}

class despachoProceso_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.*,LPAD(b.desp_numero::TEXT,6,'0')||'-'||b.desp_anno||COALESCE('-'||b.desp_siglas,'') AS num_documento,
                                        CASE WHEN a.dede_estado = 3 OR a.dede_estado = 7 THEN
                                                            NOW()::date - a.dede_fecharecibe::date
                                        ELSE 0 END AS dias_en_proceso,
                                        apli.apli_max_semaforo1 AS valor_semaforo1,
                                        CASE WHEN apli.apli_max_semaforo1>0 AND (a.dede_estado = 3 OR a.dede_estado = 7) AND
                                                                (NOW()::date - a.dede_fecharecibe::date)<=apli.apli_max_semaforo1 THEN 1
                                        	  ELSE 0 
                                        END AS semaforo1,
                                        apli.apli_max_semaforo2 AS valor_semaforo2,
                                        CASE WHEN apli.apli_max_semaforo1>0 AND (a.dede_estado = 3 OR a.dede_estado = 7) AND
                                        (NOW()::date - a.dede_fecharecibe::date)>apli.apli_max_semaforo1 
                                        AND (NOW()::date - a.dede_fecharecibe::date)<=apli.apli_max_semaforo2  THEN 1
                                        	  ELSE 0 
                                        END AS semaforo2,
                                        CASE WHEN apli.apli_max_semaforo2>0 AND (a.dede_estado = 3 OR a.dede_estado = 7) AND
                                                                (NOW()::date - a.dede_fecharecibe::date)>apli.apli_max_semaforo2 THEN 1
                                        	  ELSE 0 
                                        END AS semaforo3,
					b.desp_id::TEXT as id,
                                        b.desp_asunto,
                                        b.desp_adjuntados_exp,
                                        b.desp_adjuntados_id,
                                        b.desp_adjuntados,
                                        b.desp_fecha,
                                        b.desp_firma,
                                        b.desp_id_orden,
                                        b.desp_expediente,
                                        b.proc_id,
                                        b.desp_anno,
                                        b.desp_email,
                                        b.desp_notificacion_estado,
                                        c.tiex_abreviado,
                                        c.tiex_descripcion,
                                        d.depe_nombrecorto as depe_nombrecorto_origen,
                                        d.depe_nombre as depe_nombre_origen,
                                        (SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(a.depe_idorigen)) AS depe_superior_nombre_origen,

                                        CASE WHEN e.usua_mesa_partes_virtual=1 THEN b.desp_codigo||' '||b.desp_firma
                                             ELSE e.usua_login||'-'||eee.pers_apellpaterno||' '||SUBSTRING(eee.pers_nombres,1,CASE WHEN POSITION(' ' IN eee.pers_nombres)>0 THEN POSITION(' ' IN eee.pers_nombres) ELSE 100 END) 
                                        END AS usuario_origen,                                        
                                        
                                        f.usua_login||'-'||fff.pers_apellpaterno||' '||SUBSTRING(fff.pers_nombres,1,CASE WHEN POSITION(' ' IN fff.pers_nombres)>0 THEN  POSITION(' ' IN fff.pers_nombres) ELSE 100 END) AS usuario_recibe,
                                        f.usua_login as usuario_recibe2,
                                        g.pedi_id,
                                        g.pedi_estado2,
                                        h.proc_plazo_dias,
                                        h.proc_nombre AS procedimiento,
                                        h.proc_validar,
					x.usua_login as username
				FROM gestdoc.despachos_derivaciones a
                                LEFT JOIN gestdoc.despachos b ON a.desp_id=b.desp_id
                                LEFT JOIN catalogos.tipo_expediente c ON b.tiex_id=c.tiex_id
                                LEFT JOIN catalogos.dependencia d ON a.depe_idorigen=d.depe_id
                                
                                LEFT JOIN admin.usuario e  ON a.usua_idorigen=e.usua_id
                                LEFT JOIN personal.persona_datos_laborales ee on  e.pdla_id=ee.pdla_id
                                LEFT JOIN personal.persona eee on  ee.pers_id=eee.pers_id                                
                                
                                LEFT JOIN admin.usuario f  ON a.usua_idrecibe=f.usua_id
                                LEFT JOIN personal.persona_datos_laborales ff on  f.pdla_id=ff.pdla_id
                                LEFT JOIN personal.persona fff on  ff.pers_id=fff.pers_id                                
                                
                                LEFT JOIN gestdoc.procedimiento h ON b.proc_id=h.proc_id

                                LEFT JOIN solicitudes.pedidos_bbss g ON a.desp_id=g.desp_id
				LEFT JOIN admin.usuario x ON a.usua_idcrea=x.usua_id
                                LEFT JOIN admin.aplicativo apli ON apli.apli_id=1
				";

	}

	function whereID($id){
                //si se ha enviado con secuencia de expediente, es decir en decimal
                if($id>intval($id))
                    $this->addWhere("b.desp_id=$id");
                else 
                    $this->addWhere("b.desp_expediente=$id");
	}

	function whereIDPadreVarios($varios){
                    $this->addWhere("b.desp_expediente IN ($varios)");
	}
        
        function whereIDUno($id){
                $this->addWhere("a.dede_id=$id");
	}
        
	function whereIDVarios($id){
                $this->addWhere("a.dede_id IN ($id)");
	}        

        function whereEstado($estado){
		$this->addWhere("a.dede_estado=$estado");
	}

        function whereProceso(){
		$this->addWhere("(a.dede_estado=3 OR a.dede_estado=7)");
	}

        function whereDepeDestinoID($depe_id){
		$this->addWhere("a.depe_iddestino=$depe_id");
	}

	function whereTiExpID($tiex_id){
		$this->addWhere("b.tiex_id=$tiex_id");
	}

        function whereNOAdjuntados(){
		$this->addWhere("a.desp_adjuntadoid IS NULL");
	}        
        
        function whereSemaforo1(){
		$this->addWhere("CASE WHEN (a.dede_estado = 3 OR a.dede_estado = 7) AND
                                                                (NOW()::date - a.dede_fecharecibe::date)<=apli.apli_max_semaforo1 THEN 1
                                        	  ELSE 0 
                                        END=1");
	}
        
        function whereSemaforo2(){
		$this->addWhere("CASE WHEN (a.dede_estado = 3 OR a.dede_estado = 7) AND
                                        (NOW()::date - a.dede_fecharecibe::date)>apli.apli_max_semaforo1 
                                        AND (NOW()::date - a.dede_fecharecibe::date)<=apli.apli_max_semaforo2  THEN 1
                                        	  ELSE 0 
                                        END=1");
	}
        
        function whereSemaforo3(){
		$this->addWhere("CASE WHEN (a.dede_estado = 3 OR a.dede_estado = 7) AND
                                                                (NOW()::date - a.dede_fecharecibe::date)>apli.apli_max_semaforo2 THEN 1
                                        	  ELSE 0 
                                        END=1 ");
	}
        
        function whereUsuaDestinoID($usua_id){
		$this->addWhere("a.usua_iddestino=$usua_id");
	}

        function whereUsuaRecibeID($usua_id){
		$this->addWhere("a.usua_idrecibe=$usua_id");
	}

	function whereDescrip($descrip){
		if($descrip) $this->addWhere("(b.desp_asunto ILIKE '%$descrip%' OR b.desp_firma ILIKE '%$descrip%')");
	}
        
        function orderUno(){
		$this->addOrder("b.desp_id_orden DESC");
	}
        
        function orderUno2(){
		$this->addOrder("a.dede_fecharecibe DESC,b.desp_id_orden DESC");
	}
        
	function orderUnox(){
		$this->addOrder("a.desp_id DESC");
	}
        function getSQL_resumen() {
                $sql="SELECT DISTINCT 
                             a.desp_id,
                             a.desp_id 
                        FROM (".$this->getSQL().") AS a 
                        ORDER BY 1 DESC"; 
                return($sql);
        }
        
        function getSQL_resumen2($regSeleccionadosId) {
                $usua_id= getSession("sis_userid");
                $sql="SELECT DISTINCT 
                             a.desp_id,
                             a.desp_id 
                        FROM (".$this->getSQL().") AS a 
                        UNION ALL
                        SELECT  a.desp_id,
                                a.desp_id 
                             FROM gestdoc.despachos a
                             WHERE a.usua_id=$usua_id 
                                    AND a.desp_id IN ($regSeleccionadosId)
                        ORDER BY 1 "; 
                return($sql);
        }
        
      

}



class despachoProceso2_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT      a.dede_id,
                                        a.dede_estado,
                                        a.dede_fecharecibe,
                                        a.depe_iddestino,
                                        a.usua_iddestino,
                                        a.usua_idrecibe,
                                        a.dede_acum_derivaciones,
                                        a.dede_concopia,
                                        LPAD(b.desp_numero::TEXT,6,'0')||'-'||b.desp_anno||COALESCE('-'||b.desp_siglas,'') AS num_documento,
                                        CASE WHEN a.dede_estado = 3 OR a.dede_estado = 7 THEN
                                                            NOW()::date - a.dede_fecharecibe::date
                                        ELSE 0 END AS dias_en_proceso,
                                        apli.apli_max_semaforo1 AS valor_semaforo1,
                                        CASE WHEN apli.apli_max_semaforo1>0 AND (a.dede_estado = 3 OR a.dede_estado = 7) AND
                                                                (NOW()::date - a.dede_fecharecibe::date)<=apli.apli_max_semaforo1 THEN 1
                                        	  ELSE 0 
                                        END AS semaforo1,
                                        apli.apli_max_semaforo2 AS valor_semaforo2,
                                        CASE WHEN apli.apli_max_semaforo1>0 AND (a.dede_estado = 3 OR a.dede_estado = 7) AND
                                        (NOW()::date - a.dede_fecharecibe::date)>apli.apli_max_semaforo1 
                                        AND (NOW()::date - a.dede_fecharecibe::date)<=apli.apli_max_semaforo2  THEN 1
                                        	  ELSE 0 
                                        END AS semaforo2,
                                        CASE WHEN apli.apli_max_semaforo2>0 AND (a.dede_estado = 3 OR a.dede_estado = 7) AND
                                                                (NOW()::date - a.dede_fecharecibe::date)>apli.apli_max_semaforo2 THEN 1
                                        	  ELSE 0 
                                        END AS semaforo3,
					b.desp_id::TEXT as id,
                                        b.desp_asunto,
                                        b.desp_adjuntados_exp,
                                        b.desp_adjuntados_id,
                                        b.desp_adjuntados,
                                        b.desp_fecha,
                                        b.desp_firma,
                                        b.desp_id_orden,
                                        b.desp_expediente,
                                        b.proc_id,
                                        b.desp_anno,
                                        b.desp_email,
                                        b.desp_notificacion_estado,
                                        c.tiex_abreviado,
                                        c.tiex_descripcion,
                                        d.depe_nombrecorto as depe_nombrecorto_origen,
                                        d.depe_nombre as depe_nombre_origen,

                                        
                                        f.usua_login||'-'||fff.pers_apellpaterno||' '||SUBSTRING(fff.pers_nombres,1,CASE WHEN POSITION(' ' IN fff.pers_nombres)>0 THEN  POSITION(' ' IN fff.pers_nombres) ELSE 100 END) AS usuario_recibe,
                                        f.usua_login as usuario_recibe2,
                                        g.pedi_id,
                                        g.pedi_estado2,
                                        h.proc_plazo_dias,
                                        h.proc_nombre AS procedimiento,
                                        h.proc_validar,
					x.usua_login as username
				FROM gestdoc.despachos_derivaciones a
                                LEFT JOIN gestdoc.despachos b ON a.desp_id=b.desp_id
                                LEFT JOIN catalogos.tipo_expediente c ON b.tiex_id=c.tiex_id
                                LEFT JOIN catalogos.dependencia d ON a.depe_idorigen=d.depe_id
                                
                                
                                LEFT JOIN admin.usuario f  ON a.usua_idrecibe=f.usua_id
                                LEFT JOIN personal.persona_datos_laborales ff on  f.pdla_id=ff.pdla_id
                                LEFT JOIN personal.persona fff on  ff.pers_id=fff.pers_id                                
                                
                                LEFT JOIN gestdoc.procedimiento h ON b.proc_id=h.proc_id

                                LEFT JOIN solicitudes.pedidos_bbss g ON a.desp_id=g.desp_id
				LEFT JOIN admin.usuario x ON a.usua_idcrea=x.usua_id
                                LEFT JOIN admin.aplicativo apli ON apli.apli_id=1
				";

	}

	function whereID($id){
                //si se ha enviado con secuencia de expediente, es decir en decimal
                if($id>intval($id))
                    $this->addWhere("b.desp_id=$id");
                else 
                    $this->addWhere("b.desp_expediente=$id");
	}

	function whereIDPadreVarios($varios){
                    $this->addWhere("b.desp_expediente IN ($varios)");
	}
        
        function whereIDUno($id){
                $this->addWhere("a.dede_id=$id");
	}
        
	function whereIDVarios($id){
                $this->addWhere("a.dede_id IN ($id)");
	}        

        function whereEstado($estado){
		$this->addWhere("a.dede_estado=$estado");
	}

        function whereProceso(){
		$this->addWhere("(a.dede_estado=3 OR a.dede_estado=7)");
	}

        function whereDepeDestinoID($depe_id){
		$this->addWhere("a.depe_iddestino=$depe_id");
	}

	function whereTiExpID($tiex_id){
		$this->addWhere("b.tiex_id=$tiex_id");
	}

        function whereNOAdjuntados(){
		$this->addWhere("a.desp_adjuntadoid IS NULL");
	}        
        
        function whereSemaforo1(){
		$this->addWhere("CASE WHEN (a.dede_estado = 3 OR a.dede_estado = 7) AND
                                                                (NOW()::date - a.dede_fecharecibe::date)<=apli.apli_max_semaforo1 THEN 1
                                        	  ELSE 0 
                                        END=1");
	}
        
        function whereSemaforo2(){
		$this->addWhere("CASE WHEN (a.dede_estado = 3 OR a.dede_estado = 7) AND
                                        (NOW()::date - a.dede_fecharecibe::date)>apli.apli_max_semaforo1 
                                        AND (NOW()::date - a.dede_fecharecibe::date)<=apli.apli_max_semaforo2  THEN 1
                                        	  ELSE 0 
                                        END=1");
	}
        
        function whereSemaforo3(){
		$this->addWhere("CASE WHEN (a.dede_estado = 3 OR a.dede_estado = 7) AND
                                                                (NOW()::date - a.dede_fecharecibe::date)>apli.apli_max_semaforo2 THEN 1
                                        	  ELSE 0 
                                        END=1 ");
	}
        
        function whereUsuaDestinoID($usua_id){
		$this->addWhere("a.usua_iddestino=$usua_id");
	}

        function whereUsuaRecibeID($usua_id){
		$this->addWhere("a.usua_idrecibe=$usua_id");
	}

	function whereDescrip($descrip){
		if($descrip) $this->addWhere("(b.desp_asunto ILIKE '%$descrip%' OR b.desp_firma ILIKE '%$descrip%')");
	}
        
        
        function orderUno(){
		$this->addOrder("a.dede_fecharecibe DESC,b.desp_id_orden DESC");
	}
        
        
        function getSQL_enProceso($busc_id=0,$op=1) {
            
            
                $usua_id= getSession("sis_userid");
                
                if($op==1){
                    $busc_id=$busc_id?$busc_id:0;
                }

                $sql="SELECT  1 AS orden, 
                              a.id,
                              a.dede_id,

                              a.dias_en_proceso,
                              a.valor_semaforo1,
                              a.semaforo1,
                              a.valor_semaforo2,
                              a.semaforo2,
                              a.semaforo3,
                                        
                              a.tiex_descripcion,
                              a.dede_concopia,
                              a.desp_fecha,
                              a.tiex_abreviado,
                              a.num_documento,
                              a.depe_nombrecorto_origen,
                              a.desp_asunto,
                              a.desp_adjuntados_exp,
                              a.usuario_recibe,
                              a.dede_acum_derivaciones,
                              a.desp_id_orden
                        FROM (".$this->getSQL().") AS a 
                        UNION ALL
                        SELECT 2 AS orden,
                                    a.id,
                                    a.dede_id,
                                    
                                    0 AS dias_en_proceso,
                                    0 AS valor_semaforo1,
                                    0 AS semaforo1,
                                    0 AS valor_semaforo2,
                                    0 AS semaforo2,
                                    0 AS semaforo3,
                              
                                    a.tiex_descripcion,
                                    a.dede_concopia,
                                    a.desp_fecha,
                                    a.tiex_abreviado,
                                    a.num_documento,
                                    a.depe_nombrecorto,
                                    a.desp_asunto,
                                    a.desp_adjuntados_exp,
                                    a.usuario_recibe,
                                    a.dede_acum_derivaciones,
                                    a.desp_id_orden
                             FROM (SELECT 
                                    a.desp_id::TEXT as id,
                                    a.desp_id AS dede_id,
                                    b.tiex_descripcion,
                                    NULL::INTEGER AS dede_concopia,
                                    a.desp_fecha,
                                    b.tiex_abreviado,
                                    LPAD(a.desp_numero::TEXT, 6, '0') || '-' || a.desp_anno || '-' ||
                                      COALESCE(a.desp_siglas, '') AS num_documento,
                                    f.depe_nombrecorto,
                                    a.desp_asunto,
                                    a.desp_adjuntados_exp,
                                    NULL::VARCHAR AS usuario_recibe,
                                    NULL::INTEGER AS dede_acum_derivaciones,
                                    a.desp_id_orden
                             FROM gestdoc.despachos a
                                  LEFT JOIN catalogos.tipo_expediente b ON a.tiex_id = b.tiex_id
                                  LEFT JOIN catalogos.dependencia f ON a.depe_id = f.depe_id
                             WHERE a.usua_id=$usua_id     ";
                
                if($op==1){//UN EXPEDIENTE
                         
                        $sql.=     "       AND CASE  WHEN $busc_id>$busc_id::INTEGER THEN $busc_id=a.desp_id
                                                     WHEN $busc_id>0 THEN $busc_id=a.desp_expediente
                                                     ELSE TRUE 
                                                END ";
                         
                }elseif($op==2){//VARIOS EXPEDIENTES
                    
                         $sql.=     " AND a.desp_expediente IN ($busc_id) ";
                         
                }
                         //$sql.=     " AND a.desp_expediente NOT IN (SELECT a.desp_expediente FROM gestdoc.despachos_derivaciones a WHERE a.desp_adjuntadoid IS NOT NULL) ";
                                            
                        $sql.=     " ORDER BY a.desp_idx DESC
                                        LIMIT 15 OFFSET 0) AS a
                                   " ; 
               
                return($sql);
        }             

}

function btnDespacho($registro,$idPadre,$depe_id, $dede_id,$desp_expediente,$proc_validar){
    $botones="<div class=\"dropdown\">
                <button class=\"btn btn-default dropdown-toggle btn-sm\" type=\"button\" id=\"dropdownMenu1\" data-toggle=\"dropdown\"><span class=\"glyphicon glyphicon-cog\" aria-hidden=\"true\"></span> Opciones
                <span class=\"caret\"></span></button>
                <ul class=\"dropdown-menu dropdown-menu-right\" aria-labelledby=\"dropdownMenu1\">";

//    $array = array('sel'=>array(1=>$registro));
//    $formdata=json_encode($array);
    $formdata="{'sel': {0:'$registro'}}";
    $botones.="<li><a href=\"javascript:derivar('$registro')\" target=\"content\">Derivar</a></li>";    
    $botones.="<li><a href=\"javascript:archivar('$registro','')\" target=\"content\">Archivar</a></li>";    
    $botones.="<li><a href=\"javascript:if(confirm('Seguro de eliminar Derivaciones del registro $idPadre?')) {xajax_elimDerivar($formdata)}\" target=\"content\">Eliminar mis Derivaciones</a></li>";        
    $botones.="<li><a href=\"javascript:beforeUpload('$idPadre','$dede_id')\" target=\"content\">Agregar Archivo</a></li>";    
    $botones.="<li><a href=\"#\" onClick=\"javascript:beforeEnviaEmail(2,'$idPadre','$desp_expediente')\" target=\"controle\">Enviar Correo Electrónico</a></li>";
    
    if($proc_validar==1){
        $botones.="<li><a href=\"javascript:validarRequisitos('$registro')\" target=\"content\">Validar Requisitos</a></li>";    
    }
    
    $botones.="<li><a href=\"javascript:javascript:xajax_imprimir($formdata)\" target=\"content\">Imprimir HT</a></li>";    
    $botones.="<li><a href=\"#\" onClick=\"javascript:xajax_seguir('$idPadre')\" target=\"controle\">Seguir</a></li>";
    
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

            $dml=new despachoProceso();

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