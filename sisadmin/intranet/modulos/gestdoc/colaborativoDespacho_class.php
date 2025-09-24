<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class colaborativo extends entidad {

	function __construct($id='',$title=''){
		$this->id=$id;
		$this->title=$title;	            
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
		$nbusc_depe_id=is_array($formData)?$formData['nbusc_depe_id']:$paramFunction->getValuePar('nbusc_depe_id');
                $nbusc_user_id=is_array($formData)?$formData['nbusc_user_id']:$paramFunction->getValuePar('nbusc_user_id');

                $param->removePar('clear');
                $param->removePar('nbusc_depe_id');
                $param->addParComplete("nbusc_depe_id", $nbusc_depe_id);
                $param->removePar('nbusc_user_id');
                $param->addParComplete("nbusc_user_id", $nbusc_user_id);
                $tiex_id=is_array($formData)?$formData['nbusc_tiex']:$paramFunction->getValuePar('nbusc_tiex');
                
		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias

			$sql=new colaborativo_SQLlista();
                        $sql->whereAbierto();
                        $sql->wherePdlaID(getSession("sis_persid"));
                        
                        if($tiex_id){
			    $sql->whereTiExpID($tiex_id);
			}
                        
			//se analiza la columna de busqueda
			switch($colSearch){
				case 'codigo': // si se recibe el campo id
					$sql->whereID($cadena);
					break;
	
				default:// si se no se recibe ningun campo de busqueda
					if(is_numeric($cadena)) //si la cadena recibida son todos digitos
						$sql->whereID($cadena);
					else
						$sql->whereDescrip($cadena);
					break;
				}
			$sql->orderUno(); //ordena por el ID mas recientemente creado
			
			$sql=$sql->getSQL();
			//echo $sql;
			//$objResponse->addAlert($sql);
		
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) setSession("cadSearch",$cadena);			
	
			$rs = new query($conn, strtoupper($sql),$pg,40);
	
			$otable = new  Table("","100%",8);
                        
			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {

					$otable->addColumnHeader("<input type=\"checkbox\" class=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">"); 
                                        $otable->addColumnHeader("","1%",false); 
					$otable->addColumnHeader(NAME_EXPEDIENTE,true,"5%", "L");
					$otable->addColumnHeader("Fecha",true,"5%", "C");
					$otable->addColumnHeader("TExp",true,"4%", "L");
					$otable->addColumnHeader("N&uacute;mero",true,"30%", "C");
					$otable->addColumnHeader("Asunto",true,"46%", "C");
					$otable->addColumnHeader("Adjuntados",true,"10%", "C");
					$otable->addRow(); // adiciona la linea (TR)
					while ($rs->getrow()) {
						$id = $rs->field("id"); // captura la clave primaria del recordsource
						$campoTexto_de_Retorno = especialChar($rs->field("tiex_descripcion"));
                                                $otable->addData("<input type=\"checkbox\" class=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");

                                                if($rs->field('desp_estado')==1 && $rs->field("desp_procesador")==1){//abierto
                                                    $otable->addData("<img src=\"../../img/look_o.gif\" border=0 align=absmiddle hspace=1 alt=\"Abierto\">");
                                                }elseif($rs->field('desp_estado')==2 && $rs->field("desp_procesador")==1){//abierto
                                                    $otable->addData("<img src=\"../../img/look_c.gif\" border=0 align=absmiddle hspace=1 alt=\"Cerrado\">");
                                                }else{
                                                    $otable->addData("");
                                                }   
                                                //si es el mismo usuario que lo ha creado
                                                if($rs->field("desp_procesador")==0){
                                                    $otable->addData(addLink($id,"registroDespacho_edicion.php?id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
                                                }else{
                                                    $otable->addData(addLink($id,"registroDespacho_edicionConFirma.php?id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
                                                }
                                                $otable->addData(dtos($rs->field("desp_fecha")));
						$otable->addData($rs->field("tiex_abreviado"));
						$otable->addData($rs->field("num_documento"));
                                                
                                                $depeid=getSession("sis_depeid");
						$otable->addData(addLink(substr($rs->field("desp_asunto"),0,40)."...<img src=\"../../img/mas_info.gif\" width=\"14\" height=\"9\" align=\"absmiddle\" border=\"0\">","javascript:xajax_verDetalle(1,'$id','default',$depeid,'',0)","Click aqu&iacute; para ver detalles del registro"));
                                                $otable->addData($rs->field("desp_adjuntados_exp"));
						$otable->addRow();
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
		$contenido_respuesta="";
	
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

class colaborativo_SQLlista extends selectSQL {

	function __construct(){
                    $this->sql="SELECT  a.desp_id::TEXT as id,
                                        a.desp_idx,
                                        a.desp_id,
                                        a.desp_expediente,
                                        a.tiex_id,
                                        b.tiex_descripcion,
                                        a.desp_estado,
                                        a.desp_procesador,
                                        a.desp_fecha,
                                        b.tiex_abreviado,
                                        LPAD(a.desp_numero::TEXT,6,'0')||'-'||a.desp_anno||COALESCE('-'||a.desp_siglas,'') AS num_documento,
                                        a.desp_asunto,
                                        a.desp_adjuntados_exp,
                                        a.depe_id,
                                        a.usua_id,
                                        a.desp_trelacionado::TEXT as desp_trelacionado,
                                        b.tiex_abreviado,
                                        b.tiex_descripcion,
                                        c.tabl_descripcion AS modo_recepcion,
                                        d.tabl_descripcion AS tipo_despacho,
                                        e.prat_descripcion,
                                        f.depe_nombre,
                                        g.pedi_estado2,
                                        h.proc_plazo_dias,
					x.usua_login AS username,
                                        x.usua_login||'-'||xxxx.pers_apellpaterno||' '||SUBSTRING(xxxx.pers_nombres,1,CASE WHEN POSITION(' ' IN xxxx.pers_nombres)>0 THEN POSITION(' ' IN xxxx.pers_nombres) ELSE 100 END)  AS usuario_crea,
                                        x.usua_login||'/'||ff.depe_nombrecorto AS poyectado_por,
                                        y.usua_login AS usernameactual
				FROM gestdoc.despachos a
                                LEFT JOIN catalogos.tipo_expediente b ON a.tiex_id=b.tiex_id
                                LEFT JOIN catalogos.tabla c ON a.tabl_modorecepcion=c.tabl_id AND c.tabl_tipo='MODO_RECEPCION'
                                LEFT JOIN catalogos.tabla d ON a.tabl_tipodespacho=d.tabl_id AND d.tabl_tipo='TIPO_DESPACHO'
                                LEFT JOIN gestdoc.prioridad_atencion e ON a.prat_id=e.prat_id
                                LEFT JOIN catalogos.dependencia f ON a.depe_id=f.depe_id
                                LEFT JOIN solicitudes.pedidos_bbss g ON a.desp_id=g.desp_id
                                LEFT JOIN gestdoc.procedimiento h ON a.proc_id=h.proc_id
                                
                                LEFT JOIN admin.usuario xx  ON a.usua_id=xx.usua_id
                                LEFT JOIN personal.persona_datos_laborales xxx on  xx.pdla_id=xxx.pdla_id
                                LEFT JOIN personal.persona xxxx on  xxx.pers_id=xxxx.pers_id                                
                                LEFT JOIN catalogos.dependencia ff ON a.depe_id_proyectado=ff.depe_id
                                
				LEFT JOIN admin.usuario x ON a.usua_id=x.usua_id
                                LEFT JOIN admin.usuario y ON a.desp_actualusua=y.usua_id
				";
	
	}
	
        function wherePdlaID($pers_id){
            $this->addWhere("a.desp_id IN (SELECT a.desp_id 
                                            FROM gestdoc.despachos_colaborativos a
                                            WHERE a.deco_permiso=1 /*HABILITADO PARA EDICION*/
                                                  AND a.pdla_id IN (SELECT a.pdla_id
                                                                        FROM personal.persona_datos_laborales a
                                                                        WHERE a.pers_id=$pers_id
                                                                    )    
                                            )");
        }
        
        function whereAbierto(){
		$this->addWhere("a.desp_estado=1");
	}
        
        
        function whereTiExpID($tiex_id){
		$this->addWhere("b.tiex_id=$tiex_id");
	}
        
	function whereDescrip($descrip){
		if($descrip) $this->addWhere("a.desp_asunto ILIKE '%$descrip%'");
	}

	function orderUno(){
		$this->addOrder("a.desp_id_orden DESC");
	}
        
	function orderDos(){
		$this->addOrder("a.desp_idx DESC");
	}
	
}
