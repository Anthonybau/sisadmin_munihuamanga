<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class firmados extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='despachos'; //nombre de la tabla
		$this->setKey='desp_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

		/* Destinos luego de actulizar o agregar un registro */
		$this->destinoUpdate = "registroDespacho_buscar.php";
		$this->destinoInsert = "registroDespacho_buscar.php";
		$this->destinoDelete = "registroDespacho_buscar.php";
	}

	function addField(&$sql){
            	//$sql->addField("depe_id",getSession("sis_depeid"),"Number");

		$sql->addField("desp_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
		$sql->addField("desp_actualusua", getSession("sis_userid"), "String");
                
                //if ($_POST["hx_arch_personal"])
                //    $sql->addField("arch_personal", 1, "Number");
                //else
                //    $sql->addField("arch_personal", 0, "Number");

	}

	function getSql(){
		$sql=new firmados_SQLlista();
		$sql->whereID($this->id);
		return($sql->getSQL());
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
                $estado_firma=is_array($formData)?$formData['nbusc_estado_firma']:$paramFunction->getValuePar('nbusc_estado_firma');
                $estado_firma=$estado_firma?$estado_firma:0;
		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias	

                        $sql=new firmados_SQLlista();

                        //$sql->whereUsuaID(getSession("sis_userid"));
                        $sql->wherePersID(getSession("sis_persid"),"$estado_firma");
                        
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
			$otable = new  Table("","100%",10,true,'tLista');


                        
			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
					$otable->addColumnHeader("<input type=\"checkbox\" class=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">"); // Coluna com checkbox
                                        $otable->addColumnHeader("");
                                        $otable->addColumnHeader("");
					$otable->addColumnHeader(NAME_EXPEDIENTE,false,"5%", "L"); 
					$otable->addColumnHeader("Fecha",false,"5%", "C");
					$otable->addColumnHeader("TExp",false,"4%", "L");
					$otable->addColumnHeader("N&uacute;mero",false,"20%", "C"); 
					$otable->addColumnHeader("Asunto",false,"40%", "C");
					$otable->addColumnHeader("Pos/Tipo/Cargo",false,"21%", "C","","Posici&oacute;n de la Firma/Tipo de Firma/Cargo del Firmante");
					$otable->addColumnHeader("Firmas",false,"5%", "C"); 
					$otable->addRow(); // adiciona la linea (TR)
                                        $rs->getrow();
                                        $i=-1;
					do{
						$id = $rs->field("desp_id"); // captura la clave primaria del recordsource
                                                $periodo=$rs->field("desp_anno");
                                                $bd_tabl_tipodespacho=$rs->field("tabl_tipodespacho");
                                                
                                                $hay_encadenados=false;
                                                        
                                                if($rs->field("area_adjunto"))
                                                {
                                                    $hay_encadenados=true;
                                                    $table_encadeada = new Table("","100%",1);
                                                    do{
                                                        $name_file=$rs->field("area_adjunto");
                                                        $icon_files=getIconFile($name_file);
                                                        $enlace=PUBLICUPLOAD."gestdoc/".SIS_EMPRESA_RUC."/$periodo/".$id."/".$name_file;
                    
                                                        if(strpos(strtoupper($enlace),'.PDF')>0){
                                                            $link=addLink("<img src=\"../../img/$icon_files\" border=0 align=absmiddle hspace=1 alt=\"Archivo\">".$name_file,"javascript:lectorPDF('$enlace','Lector de PDF')","Click aqu&iacute; para Ver Documento","controle");
                                                        }else{
                                                            $link=addLink("<img src=\"../../img/$icon_files\" border=0 align=absmiddle hspace=1 alt=\"Archivo\">".$name_file,"$enlace","Click aqu&iacute; para Descargar Archivo","controle");
                                                        }
                    
                                                        //SI ESTA FIRMADO
                                                        if($rs->field("dead_signer")==1){
                                                             $table_encadeada->addData("<img src=\"../../img/ico_signer_check.png\" border=0 align=absmiddle hspace=1 alt=\"Archivo\">");
                                                        }else{
                                                             $table_encadeada->addData("&nbsp;");
                                                        }
                    
                                                        $table_encadeada->addData($link);                                                            
                                                                                                                
                                                        $table_encadeada->addRow();
                                                        $i++;
                                                    }
                                                    while ($rs->getrow() && $rs->field("desp_id")==$id) ;
                                                    $rs->skiprow($i);
                                                    $rs->getrow();
                                                }
                                                else{
                                                    $i++;  
                                                }
                                                
                                                $otable->addData("<input type=\"checkbox\" class=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");
                                                
                                                /*si hay lista encadenados*/
                                                if ( $hay_encadenados )
                                                {
                                                    $otable->addData("<span id='fold_$id' style='cursor: pointer' onClick=\"javascript:openList('$id')\">&nbsp;+&nbsp;</span>","C");
                                                }
                                                else 	
                                                {
                                                    $otable->addData("&nbsp;");                                                
                                                }
                                                
                                                if( $estado_firma==1 ){//FIRMADOS
                                                    $otable->addData("<img src=\"../../img/ico_signer_check.png\" width=13 height=15 border=0 align=absmiddle hspace=1 alt=\"Registro Firmado\">");
                                                }else{
                                                    $otable->addData("<img src=\"../../img/ico_signer.png\" width=13 height=15  border=0 align=absmiddle hspace=1 alt=\"Registro x Firmar\">");
                                                }
                                                
                                                
                                                $otable->addData(addLink($id,"javascript:lectorPDF('/sisadmin/gestdoc/controllers/procesar_data.php?nr_numTramite=$id&vista=NoConsulta','Seguimiento')","Click aqu&iacute; para Seguimiento de registro"));

                                                $otable->addData(dtos($rs->field("desp_fecha")));
						$otable->addData($rs->field("tiex_abreviado"));
                                                if($rs->field('desp_procesador')==1){
                                                    $otable->addData(addLink($rs->field("num_documento"),"javascript:lectorPDF('rptDocumento.php?id=$id','Lector de PDF')","Click aqu&iacute; para Ver Documento"));
                                                    
                                                }else{        
                                                    $otable->addData($rs->field("num_documento"));
                                                }
                                                
						$otable->addData(substr($rs->field("desp_asunto"),0,40)."...");
                                                //$otable->addData($rs->field("desp_adjuntados_exp"));
                                                
                                                    /* botones */
                                                    $button2 = new Button;
                                                    $blocksLi='';
                                                    $ultTipo='';
                                                    $posicion_firma='';                                   
                                                    //SI ES LA FIRMA DEL MISMO USUARIO 
//                                                    $users=new clsUsers_SQLlista();
//                                                    $users->whereID(getSession("sis_userid"));
//                                                    $users->setDatos();
                                                    
                                                    $firmas=new despachoFirmas_SQLlista();
                                                    $firmas->wherePadreID($id);
                                                    $firmas->whereEstado($estado_firma);
                                                    //$firmas->whereEstado($estado_firma); //0->X FIMAR, 1->FIRMADOS
                                                    $firmas->orderUno();
                                                    $sql=$firmas->getSQL();
                                                    $rsFirmar = new query($conn, $sql);
                                                    while ($rsFirmar->getrow()) {
                                                        $idFirma=$rsFirmar->field('defi_id');
                                                        $defi_tipo=$rs->field('defi_tipo');
                                                        
                                                        if($ultTipo!='' && $ultTipo!=$rsFirmar->field('defi_tipo')){
                                                            $blocksLi.= "<li role=\"separator\" class=\"divider\"></li>";
                                                            $ultTipo=$rsFirmar->field('defi_tipo');
                                                        }
                                                        
                                                        if($rsFirmar->field('pers_id')==getSession("sis_persid")){
                                                            $posicion_firma=$rsFirmar->field('defi_posicion');
                                                            $tipo_firma=$rsFirmar->field('tipo_firma');
                                                            $cargo=$rsFirmar->field('cargo');
                                                        }        
                                                        if($rsFirmar->field('defi_estado')==0){//X FIRMAR
                                                            if($rsFirmar->field('pers_id')==getSession("sis_persid")){
                                                                /*si es documento personal*/
                                                                if ($bd_tabl_tipodespacho==141 && SIS_FIRMA_PERSONAL_ELECTRONICA==1){
                                                                    if(inlist($defi_tipo,'2')){//VB
                                                                        $tipo_firma="VB ELECTRÓNICO";
                                                                    }else{
                                                                        $tipo_firma="FIMA ELECTRÓNICA";
                                                                    }
                                                                    $blocksLi.= "<li><a href=\"#\" onClick=\"javascript:getConfirm('Seguro de Poner Firma Electrónica?',function(result) {setFirmaElectronica('$id','$idFirma')})\" >".ucfirst(strtolower($rsFirmar->field('defi_especbreve'))).' '.ucfirst(strtolower($rsFirmar->field('pers_apellpaterno'))).' '.ucfirst(strtolower($rsFirmar->field('pers_apellmaterno'))).' '.ucfirst(strtolower($rsFirmar->field('nombres'))).'('.ucfirst(strtolower($rsFirmar->field('cargo'))).")"." ** $tipo_firma **"."</a></li>";   
                                                                }else{                                                                
                                                                    if(inlist($defi_tipo,'2')){//VB
                                                                        $tipo_firma="VB";
                                                                    }else{
                                                                        $tipo_firma="";
                                                                    }
                                                                    $blocksLi.= "<li><a href=\"#\" onClick=\"javascript:xajax_beforeFirma( '$id','$idFirma')\">".ucfirst(strtolower($rsFirmar->field('defi_especbreve'))).' '.ucfirst(strtolower($rsFirmar->field('pers_apellpaterno'))).' '.ucfirst(strtolower($rsFirmar->field('pers_apellmaterno'))).' '.ucfirst(strtolower($rsFirmar->field('nombres'))).' ('.ucfirst(strtolower($rsFirmar->field('cargo'))).'-'.ucfirst(strtolower($rsFirmar->field('dependencia'))).") $tipo_firma </a></li>";   
                                                                    $blocksLi.= "<li role=\"separator\" class=\"divider\"></li>";
                                                                }
                                                                //$blocksLi.= "<li><a href=\"#\">Hacer Observaci&oacute;n</a></li>";   
                                                            }else{
                                                                $blocksLi.= "<li><span class=\"glyphicon glyphicon-ban-circle\" aria-hidden=\"true\"></span>&nbsp;".ucfirst(strtolower($rsFirmar->field('defi_especbreve'))).' '.ucfirst(strtolower($rsFirmar->field('pers_apellpaterno'))).' '.ucfirst(strtolower($rsFirmar->field('pers_apellmaterno'))).' '.ucfirst(strtolower($rsFirmar->field('nombres'))).' ('.ucfirst(strtolower($rsFirmar->field('cargo'))).'-'.ucfirst(strtolower($rsFirmar->field('dependencia'))).")</a></li>";                           
                                                            }
                                                        }elseif($rsFirmar->field('defi_estado')==1){//FIRMADO
                                                            if($rsFirmar->field('pers_id')==getSession("sis_persid")
                                                                    && $rs->field("desp_acum_recibidos")==0 ){ //SI NO SE HA RECIBIDO DERIVACION ALGUNA Y NO HAY DERIVCIONES                                                                
                                                                
                                                                    //$blocksLi.= "<li><a href=\"javascript:getConfirm('Seguro de Eliminar Firma?',function(result) {xajax_borrarFirmaEjecutar(2,'$id','$idFirma','')})\"><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span>&nbsp;".ucfirst(strtolower($rsFirmar->field('defi_especbreve'))).' '.ucfirst(strtolower($rsFirmar->field('pers_apellpaterno'))).' '.ucfirst(strtolower($rsFirmar->field('pers_apellmaterno'))).' '.ucfirst(strtolower($rsFirmar->field('nombres'))).' ('.ucfirst(strtolower($rsFirmar->field('cargo'))).'-'.ucfirst(strtolower($rsFirmar->field('dependencia'))).")</a></li>";   
                                                                    $blocksLi.= "<li><a href=\"#\"><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span>&nbsp;".ucfirst(strtolower($rsFirmar->field('defi_especbreve'))).' '.ucfirst(strtolower($rsFirmar->field('pers_apellpaterno'))).' '.ucfirst(strtolower($rsFirmar->field('pers_apellmaterno'))).' '.ucfirst(strtolower($rsFirmar->field('nombres'))).' ('.ucfirst(strtolower($rsFirmar->field('cargo'))).'-'.ucfirst(strtolower($rsFirmar->field('dependencia'))).")</a></li>";   
                                                                    
                                                                    if($rsFirmar->field('defi_autoriza_rehacer')==0){
                                                                        $blocksLi.= "<li><a href=\"javascript:getConfirm('Seguro de Autorizar para Rehacer Documento?',function(result) {xajax_autorizaRehacerDocumento('$idFirma','')})\"><span class=\"glyphicon glyphicon-repeat\" aria-hidden=\"true\"></span>&nbsp;Autorizar Rehacer Documento</a></li>";   
                                                                    }elseif($rsFirmar->field('defi_autoriza_rehacer')==1){
                                                                        $blocksLi.= "<li><a href=\"#\"><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span>&nbsp;Autorizado para Rehacer Documento</a></li>";       
                                                                    }
                                                                    
                                                            }else{//SI HAY ALGUN DOCUMENTO RECIBIDO, IMPOSIBLE BORRAR FIRMAS
                                                                $blocksLi.= "<li><a href=\"#\"><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span>&nbsp;".ucfirst(strtolower($rsFirmar->field('defi_especbreve'))).' '.ucfirst(strtolower($rsFirmar->field('pers_apellpaterno'))).' '.ucfirst(strtolower($rsFirmar->field('pers_apellmaterno'))).' '.ucfirst(strtolower($rsFirmar->field('nombres'))).' ('.ucfirst(strtolower($rsFirmar->field('cargo'))).'-'.ucfirst(strtolower($rsFirmar->field('dependencia'))).")</a></li>";   
                                                            }
                                                        }
                                                        if($ultTipo==''){
                                                            $ultTipo=$rsFirmar->field('defi_tipo');
                                                        }
                                                    }
                                                    $otable->addData("$posicion_firma/$tipo_firma/$cargo","C");
                                                            
                                                    $button2->addHtml("<button class=\"btn btn-default dropdown-toggle btn-xs\" type=\"button\" id=\"dropdownMenu$id\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"true\">
                                                                        <span class=\"glyphicon glyphicon glyphicon-pencil\" aria-hidden=\"true\"></span>&nbsp;Fimas
                                                                        <span class=\"caret\"></span>
                                                                      </button>
                                                                      <ul class=\"dropdown-menu dropdown-menu-right\" aria-labelledby=\"dropdownMenu$id\">
                                                                        $blocksLi
                                                                      </ul>");

                                                    $otable->addData($button2->writeHTML());
                                                ///
                                                
						$otable->addRow();
                                                if ($hay_encadenados) {
                                                    $otable->addBreak("<div id=\"$id\" style='visibility: hidden; display: none; margin-left: 60px'>".$table_encadeada->writeHTML()."</div>", false);
                                                }
					}while ($rs->getrow());
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

        
        function clearDiv($NameDiv){
                $objResponse = new xajaxResponse();

                //limpio el div
                $objResponse->addClear($NameDiv,'innerHTML');
                return $objResponse;
        }


	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	
}

class firmados_SQLlista extends selectSQL {

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
                                        a.desp_anno,
                                        b.tiex_abreviado,
                                        LPAD(a.desp_numero::TEXT,6,'0')||'-'||a.desp_anno||COALESCE('-'||a.desp_siglas,'') AS num_documento,
                                        a.desp_asunto,
                                        a.desp_acum_derivados,
                                        a.desp_acum_recibidos,
                                        a.desp_adjuntados_exp,
                                        a.depe_id,
                                        a.usua_id,                    
                                        a.desp_trelacionado::TEXT as desp_trelacionado,
                                        a.tabl_tipodespacho,
                                        b.tiex_abreviado,
                                        b.tiex_descripcion,
                                        cc.area_adjunto,
                                        c.tabl_descripcion AS modo_recepcion,
                                        d.tabl_descripcion AS tipo_despacho,
                                        e.prat_descripcion,
                                        f.depe_nombre,
					x.usua_login AS username,
                                        y.usua_login AS usernameactual
				FROM gestdoc.despachos a
                                LEFT JOIN catalogos.tipo_expediente b ON a.tiex_id=b.tiex_id
                                LEFT JOIN gestdoc.despachos_adjuntados cc ON a.desp_id=cc.desp_id AND cc.dead_signer=1 /*xFIRMAR*/
                                LEFT JOIN catalogos.tabla c ON a.tabl_modorecepcion=c.tabl_id AND c.tabl_tipo='MODO_RECEPCION'
                                LEFT JOIN catalogos.tabla d ON a.tabl_tipodespacho=d.tabl_id AND d.tabl_tipo='TIPO_DESPACHO'
                                LEFT JOIN gestdoc.prioridad_atencion e ON a.prat_id=e.prat_id
                                LEFT JOIN catalogos.dependencia f ON a.depe_id=f.depe_id
				LEFT JOIN admin.usuario x ON a.usua_id=x.usua_id
                                LEFT JOIN admin.usuario y ON a.desp_actualusua=y.usua_id
				";
	
	}
	
	function whereID($id){
                if($id>intval($id)){
                    $this->addWhere("a.desp_id=$id");
                }
                else 
                    $this->addWhere("a.desp_expediente=$id");
                    
                    //$this->addWhere("a.desp_id=$id");
                
	}
        function whereTipoDespacho($tipo_despacho){
		$this->addWhere("a.tabl_tipodespacho=$tipo_despacho");
	}


        function whereTiExpID($tiex_id){
		$this->addWhere("b.tiex_id=$tiex_id");
	}
        
        function whereUsuaID($usua_id){
		$this->addWhere("a.desp_id IN (SELECT DISTINCT a.desp_id
                                                FROM gestdoc.despachos_firmas a
                                                LEFT JOIN admin.usuario b ON a.pdla_id=b.pdla_id
                                                WHERE defi_estado=1 /*FIRMADOS*/
                                                    AND b.usua_id=$usua_id)");
	}

        function wherePersID($pers_id,$estado_firma){
		$this->addWhere("a.desp_id IN (SELECT DISTINCT a.desp_id
                                                FROM gestdoc.despachos_firmas a
                                                LEFT JOIN personal.persona_datos_laborales b on  a.pdla_id=b.pdla_id
                                                LEFT JOIN personal.persona c on  b.pers_id=c.pers_id
                                                WHERE defi_estado=$estado_firma /*0->PENDIENTES DE FIRMA, 1->FIRMADOS*/
                                                    AND c.pers_id='$pers_id')");
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

            $dml=new firmados();

            switch($control){
                    case 2: // Eliminar
                            $dml->eliminar();
                            break;
                        
            }
            //	cierra la conexión con la BD
            $conn->close();
    }
}