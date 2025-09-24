<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class despacho extends entidad {

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
		$sql=new despacho_SQLlista();
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
                
		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias

			$sql=new despacho2_SQLlista();
                        
                        //OJO NO FILTRAR POR DEPENDENCIA PORQUE EL USUARIO ELEGIDDO
                        //PODRIA HABER PROYECTAO DOCUMENOS PARA OTRA DEPENDENCIA QUE NO ESTA EN LA LISTA (SELECCIONADO POR EL UUARIO)
                        //POR TANTO ES MEJOR FILTRAR TODO LO DEL USUARIO
                        //EN CONCLUSION LA DEPENDENCIA SOLO SIRVE PARA MOSTRAR LOS USUARIOS                       
                        //$sql->whereDepeID($nbusc_depe_id);
                        
                        if($nbusc_depe_id){
                            $sql->whereDepeID($nbusc_depe_id);
                        }
                        
                        //if($nbusc_user_id){
                            $sql->whereUsuaID($nbusc_user_id); //si o si tiene que elegr los documentos de su usuario
                        //}
                        
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
			$sql->orderDos(); //ordena por el ID mas recientemente creado
			
			$sql=$sql->getSQL();
			//echo $sql;
			//$objResponse->addAlert($sql);
		
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) setSession("cadSearch",$cadena);			
	
			$rs = new query($conn, strtoupper($sql),$pg,40);
	
                        $otable = new  Table("","100%",11,true,'registados');
                        
			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {

					$otable->addColumnHeader("<input type=\"checkbox\" class=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">"); 
                                        $otable->addColumnHeader("","1%",false); 
					$otable->addColumnHeader(NAME_EXPEDIENTE,false,"5%", "L");
					$otable->addColumnHeader("Fecha",false,"5%", "C");
					$otable->addColumnHeader("TExp",false,"4%", "L");
					$otable->addColumnHeader("N&uacute;mero",false,"25%", "C");
					$otable->addColumnHeader("Asunto",false,"44%", "C");
                                        $otable->addColumnHeader("Der",false,"1%", "C");
                                        $otable->addColumnHeader("Rec",false,"1%", "C");
					$otable->addColumnHeader("Adjuntados",false,"10%", "C");
                                        $otable->addColumnHeader("",false,"5%", "L");
					$otable->addRow(); // adiciona la linea (TR)
					while ($rs->getrow()) {
						$id = $rs->field("id"); // captura la clave primaria del recordsource
                                                $acum_derivados = $rs->field("desp_acum_derivados")+$rs->field("desp_acum_recibidos");
                                                $bd_desp_expediente = $rs->field('desp_expediente');
                                                
						$campoTexto_de_Retorno = especialChar($rs->field("tiex_descripcion"));
                                                $otable->addData("<input type=\"checkbox\" class=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");

                                                if($rs->field('desp_estado')==1 && $rs->field("desp_procesador")==1){//abierto
                                                    $otable->addData("<img src=\"../../img/look_o.png\" border=0 align=absmiddle hspace=1 alt=\"Abierto\">");
                                                }elseif($rs->field('desp_estado')==2 && $rs->field("desp_procesador")==1){//abierto
                                                    $otable->addData("<img src=\"../../img/look_c.png\" border=0 align=absmiddle hspace=1 alt=\"Cerrado\">");
                                                }else{
                                                    $otable->addData("");
                                                }   
                                                //si es el mismo usuario que lo ha creado
                                                if(getSession("sis_userid")==$rs->field("usua_id")){
                                                    if($rs->field("desp_procesador")==0){
                                                        $otable->addData(addLink($id,"registroDespacho_edicion.php?id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
                                                    }else{
                                                        $otable->addData(addLink($id,"registroDespacho_edicionConFirma.php?id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
                                                    }
                                                }else{
                                                    //$otable->addData(addLink($id,"javascript:abreConsulta('$id')","Click aqu&iacute; para Seguimiento de registro"));
                                                    $otable->addData(addLink($id,"javascript:lectorPDF('/sisadmin/gestdoc/controllers/procesar_data.php?nr_numTramite=$id&vista=NoConsulta','Seguimiento')","Click aqu&iacute; para Seguimiento de registro"));                                                
                                                    //$otable->addData($id);
                                                }
                                                $otable->addData(dtos($rs->field("desp_fecha")));
						$otable->addData($rs->field("tiex_abreviado"));
						$otable->addData($rs->field("num_documento"));
                                                
                                                $depeid=getSession("sis_depeid");
						$otable->addData(addLink(substr($rs->field("desp_asunto"),0,40)."...<img src=\"../../img/mas_info.gif\" width=\"14\" height=\"9\" align=\"absmiddle\" border=\"0\">","javascript:xajax_verDetalle(1,'$id','default',$depeid,'',0);return false;","Click aqu&iacute; para ver detalles del registro"));
                                                $otable->addData($rs->field("desp_acum_derivados"),"C");
                                                $otable->addData($rs->field("desp_acum_recibidos"),"");
                                                $otable->addData($rs->field("desp_adjuntados_exp"));
                                                
                                                $otable->addData(btnRegistrados($id,$bd_desp_expediente,$acum_derivados));
                                                
                                                if($rs->field("desp_acum_recibidos")>0){
                                                    $otable->addRow('EN_ESPERA');
                                                }elseif($rs->field("desp_acum_derivados")>0){
                                                    $otable->addRow('ATENDIDO');
                                                }else{
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
		$contenido_respuesta="";
	
		//se analiza el tipo de funcionamiento
		if($op==1){//si es llamado para su funcionamiento en ajax con retornoa a un div
			$objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
			return $objResponse;
			}
		else
			return $contenido_respuesta	;
	}

	function buscarVista($op,$numExpe,$depeid=0,$Nameobj)
	{
		global $conn;
		$objResponse = new xajaxResponse();
		
		if($numExpe){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	

			$sql=new despacho_SQLlista();
			$sql->whereID($numExpe);
                        
                        //SI VIENE DE DOMINIO
//                        if(strpos($_SERVER['SERVER_NAME'],".gob.pe",0)>0){
//                            $sql->whereTipoDespacho(142);//OTRAS ENTIDADES
//                        }                        
                        
                        if($depeid==0){//viene desde una consulta externa
                            $sql->whereTipoDespacho(142);
                        }
                        
			$sql->orderUno();
			$sql=$sql->getSQL();
			//echo $sql;
			//$objResponse->addAlert($sql);
	
			$rs = new query($conn, strtoupper($sql));
			$otable = new  Table("","100%",6);
                        $total_folios=0;
			if ($rs->numrows()>0) {
                                        $otable->addColumnHeader("<acronym title='Derivaciones'>&nbsp;+&nbsp;</acronym>");	
					$otable->addColumnHeader(NAME_EXPEDIENTE,false,"5%", "L"); // T�tulo, Ordenar?, ancho, alineaci�n
					$otable->addColumnHeader("Fecha",false,"5%", "C"); 
					$otable->addColumnHeader("TExp",false,"4%", "L"); 
					$otable->addColumnHeader("N&uacute;mero",false,"30%", "C"); 
					$otable->addColumnHeader("Asunto",false,"55%", "C"); 
                                        $otable->addColumnHeader("FFolios",false,"1%", "C");
					$otable->addRow(); // adiciona la linea (TR)
					while ($rs->getrow()) {
                                                $hay_encadenados=false;
						$id = $rs->field("id"); // captura la clave primaria del recordsource
                                                $depeid=$rs->field("depe_id");

                                                if($rs->field("desp_adjuntados_exp")){
                                                    
                                                    $derivaciones=new despachoDerivacion_SQLlista();
                                                    $derivaciones->whereAdjuntados($id);
                                                    $sql=$derivaciones->getSQL();
                                                    //echo $sql;
                                                    $rsDerivaciones = new query($conn, strtoupper($sql));
                                                    if ($rsDerivaciones->numrows()>0) {
                                                        $verDocumentoPadre=conPermisoVerDocumento($rs->field("desp_id"),$rs->field("usua_id"),$rs->field("usua_idfirma"),0,$rs->field("desp_set_derivados"),$rs->field("tabl_tipodespacho"));
                                                        $hay_encadenados=true;
                                                        $table_encadeada = new Table("","100%",6);
//                                                        $table_encadeada->addColumnHeader("<acronym title='Derivaciones'>&nbsp;+&nbsp;</acronym>");	
//                                                        $table_encadeada->addColumnHeader(NAME_EXPEDIENTE,false,"5%", "L"); // T�tulo, Ordenar?, ancho, alineaci�n
//                                                        $table_encadeada->addColumnHeader("Fecha",false,"5%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
//                                                        $table_encadeada->addColumnHeader("TExp",false,"4%", "L"); // T�tulo, Ordenar?, ancho, alineaci�n
//                                                        $table_encadeada->addColumnHeader("N&uacute;mero",false,"30%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
//                                                        $table_encadeada->addColumnHeader("Asunto",false,"56%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
//                                                        $table_encadeada->addRow(); 
                                                        while ($rsDerivaciones->getrow()) {
                                                            $idDerivacion = $rsDerivaciones->field("id_padre"); // captura la clave primaria del recordsource
                                                            $depeidDerivacion=$rsDerivaciones->field("depe_id");
                                                            //DOS
                                                            $id2 = $rsDerivaciones->field("id_padre"); // captura la clave primaria del recordsource
                                                            $derivaciones2=new despachoDerivacion_SQLlista();
                                                            $derivaciones2->whereAdjuntados($id2);
                                                            $sql=$derivaciones2->getSQL();
                                                            $rsDerivaciones2 = new query($conn, strtoupper($sql));
                                                            $hay_encadenados2=false;
                                                            if ($rsDerivaciones2->numrows()>0) {
                                                                $hay_encadenados2=true;
                                                                $table_encadeada2 = new Table("","100%",6);
                                                                while ($rsDerivaciones2->getrow()) {
                                                                    $idDerivacion2 = $rsDerivaciones2->field("id_padre"); // captura la clave primaria del recordsource
                                                                    $depeidDerivacion2=$rsDerivaciones2->field("depe_id");
                                                                    //TRES                                                                    
                                                                    $id3 = $rsDerivaciones2->field("id_padre"); // captura la clave primaria del recordsource
                                                                    $derivaciones3=new despachoDerivacion_SQLlista();
                                                                    $derivaciones3->whereAdjuntados($id3);
                                                                    $sql=$derivaciones3->getSQL();
                                                                    $rsDerivaciones3 = new query($conn, strtoupper($sql));
                                                                    $hay_encadenados3=false;
                                                                    if ($rsDerivaciones3->numrows()>0) {
                                                                        $hay_encadenados3=true;
                                                                        $table_encadeada3 = new Table("","100%",6);
                                                                        while ($rsDerivaciones3->getrow()) {
                                                                            $idDerivacion3 = $rsDerivaciones3->field("id_padre"); // captura la clave primaria del recordsource
                                                                            $depeidDerivacion3=$rsDerivaciones3->field("depe_id");

                                                                            //CUATRO
                                                                            $id4 = $rsDerivaciones3->field("id_padre"); // captura la clave primaria del recordsource
                                                                            $derivaciones4=new despachoDerivacion_SQLlista();
                                                                            $derivaciones4->whereAdjuntados($id4);
                                                                            $sql=$derivaciones4->getSQL();
                                                                            $rsDerivaciones4 = new query($conn, strtoupper($sql));
                                                                            $hay_encadenados4=false;
                                                                            if ($rsDerivaciones4->numrows()>0) {
                                                                                $hay_encadenados4=true;
                                                                                $table_encadeada4 = new Table("","100%",6);
                                                                                while ($rsDerivaciones4->getrow()) {
                                                                                    $idDerivacion4 = $rsDerivaciones4->field("id_padre"); // captura la clave primaria del recordsource
                                                                                    $depeidDerivacion4=$rsDerivaciones4->field("depe_id");
                                                                                    
                                                                                    //CINCO
                                                                                    $id5 = $rsDerivaciones4->field("id_padre"); // captura la clave primaria del recordsource
                                                                                    $derivaciones5=new despachoDerivacion_SQLlista();
                                                                                    $derivaciones5->whereAdjuntados($id5);
                                                                                    $sql=$derivaciones5->getSQL();
                                                                                    $rsDerivaciones5 = new query($conn, strtoupper($sql));
                                                                                    $hay_encadenados5=false;
                                                                                    if ($rsDerivaciones5->numrows()>0) {
                                                                                        $hay_encadenados5=true;
                                                                                        $table_encadeada5 = new Table("","100%",6);
                                                                                        while ($rsDerivaciones5->getrow()) {
                                                                                            $idDerivacion5 = $rsDerivaciones5->field("id_padre"); // captura la clave primaria del recordsource
                                                                                            $depeidDerivacion5=$rsDerivaciones5->field("depe_id");
                                                                                            
                                                                                            //SEIS
                                                                                            $id6= $rsDerivaciones5->field("id_padre"); // captura la clave primaria del recordsource
                                                                                            $derivaciones6=new despachoDerivacion_SQLlista();
                                                                                            $derivaciones6->whereAdjuntados($id6);
                                                                                            $sql=$derivaciones6->getSQL();
                                                                                            $rsDerivaciones6 = new query($conn, strtoupper($sql));
                                                                                            $hay_encadenados6=false;
                                                                                            if ($rsDerivaciones6->numrows()>0) {
                                                                                                $hay_encadenados6=true;
                                                                                                $table_encadeada6 = new Table("","100%",6);
                                                                                                while ($rsDerivaciones6->getrow()) {
                                                                                                    $idDerivacion6 = $rsDerivaciones6->field("id_padre"); // captura la clave primaria del recordsource
                                                                                                    $depeidDerivacion6=$rsDerivaciones6->field("depe_id");
                                                                                                    
                                                                                                    /*SIETE*/
                                                                                                    $id7= $rsDerivaciones6->field("id_padre"); // captura la clave primaria del recordsource
                                                                                                    $derivaciones7=new despachoDerivacion_SQLlista();
                                                                                                    $derivaciones7->whereAdjuntados($id7);
                                                                                                    $sql=$derivaciones7->getSQL();
                                                                                                    $rsDerivaciones7 = new query($conn, strtoupper($sql));
                                                                                                    $hay_encadenados7=false;
                                                                                                    if ($rsDerivaciones7->numrows()>0) {
                                                                                                        $hay_encadenados7=true;
                                                                                                        $table_encadeada7 = new Table("","100%",6);
                                                                                                        while ($rsDerivaciones7->getrow()) {
                                                                                                            $idDerivacion7 = $rsDerivaciones7->field("id_padre"); // captura la clave primaria del recordsource
                                                                                                            $depeidDerivacion7=$rsDerivaciones7->field("depe_id");

                                                                                                            /*OCHO*/
                                                                                                            $id8= $rsDerivaciones7->field("id_padre"); // captura la clave primaria del recordsource
                                                                                                            $derivaciones8=new despachoDerivacion_SQLlista();
                                                                                                            $derivaciones8->whereAdjuntados($id8);
                                                                                                            $sql=$derivaciones8->getSQL();
                                                                                                            $rsDerivaciones8 = new query($conn, strtoupper($sql));
                                                                                                            $hay_encadenados8=false;
                                                                                                            if ($rsDerivaciones8->numrows()>0) {
                                                                                                                $hay_encadenados8=true;
                                                                                                                $table_encadeada8 = new Table("","100%",6);
                                                                                                                while ($rsDerivaciones8->getrow()) {
                                                                                                                    $idDerivacion8 = $rsDerivaciones8->field("id_padre"); // captura la clave primaria del recordsource
                                                                                                                    $depeidDerivacion8=$rsDerivaciones8->field("depe_id");

                                                                                                                    
                                                                                                                    /*NUEVE*/
                                                                                                                    $id9= $rsDerivaciones8->field("id_padre"); // captura la clave primaria del recordsource
                                                                                                                    $derivaciones9=new despachoDerivacion_SQLlista();
                                                                                                                    $derivaciones9->whereAdjuntados($id9);
                                                                                                                    $sql=$derivaciones9->getSQL();
                                                                                                                    $rsDerivaciones9 = new query($conn, strtoupper($sql));
                                                                                                                    $hay_encadenados9=false;
                                                                                                                    if ($rsDerivaciones9->numrows()>0) {
                                                                                                                        $hay_encadenados9=true;
                                                                                                                        $table_encadeada9 = new Table("","100%",6);
                                                                                                                        while ($rsDerivaciones9->getrow()) {
                                                                                                                            $idDerivacion9 = $rsDerivaciones9->field("id_padre"); // captura la clave primaria del recordsource
                                                                                                                            $depeidDerivacion9=$rsDerivaciones9->field("depe_id");
                                                                                                                            
                                                                                                                            //DIEZ
                                                                                                                            $id10= $rsDerivaciones9->field("id_padre"); // captura la clave primaria del recordsource
                                                                                                                            $derivaciones10=new despachoDerivacion_SQLlista();
                                                                                                                            $derivaciones10->whereAdjuntados($id10);
                                                                                                                            $sql=$derivaciones10->getSQL();
                                                                                                                            $rsDerivaciones10 = new query($conn, strtoupper($sql));
                                                                                                                            $hay_encadenados10=false;
                                                                                                                            if ($rsDerivaciones10->numrows()>0) {
                                                                                                                                $hay_encadenados10=true;
                                                                                                                                $table_encadeada10 = new Table("","100%",6);
                                                                                                                                while ($rsDerivaciones10->getrow()) {
                                                                                                                                    $idDerivacion10 = $rsDerivaciones10->field("id_padre"); // captura la clave primaria del recordsource
                                                                                                                                    $depeidDerivacion10=$rsDerivaciones10->field("depe_id");
                                                                                                                                    
                                                                                                                                    //ONCE
                                                                                                                                    $id11= $rsDerivaciones10->field("id_padre"); // captura la clave primaria del recordsource
                                                                                                                                    $derivaciones11=new despachoDerivacion_SQLlista();
                                                                                                                                    $derivaciones11->whereAdjuntados($id11);
                                                                                                                                    $sql=$derivaciones11->getSQL();
                                                                                                                                    $rsDerivaciones11 = new query($conn, strtoupper($sql));
                                                                                                                                    $hay_encadenados11=false;
                                                                                                                                    if ($rsDerivaciones11->numrows()>0) {
                                                                                                                                        $hay_encadenados11=true;
                                                                                                                                        $table_encadeada11 = new Table("","100%",6);
                                                                                                                                        while ($rsDerivaciones11->getrow()) {
                                                                                                                                            $idDerivacion11 = $rsDerivaciones11->field("id_padre"); // captura la clave primaria del recordsource
                                                                                                                                            $depeidDerivacion11=$rsDerivaciones11->field("depe_id");
                                                                                                                                            
                                                                                                                                            //DOCE
                                                                                                                                            $id12= $rsDerivaciones11->field("id_padre"); // captura la clave primaria del recordsource
                                                                                                                                            $derivaciones12=new despachoDerivacion_SQLlista();
                                                                                                                                            $derivaciones12->whereAdjuntados($id12);
                                                                                                                                            $sql=$derivaciones12->getSQL();
                                                                                                                                            $rsDerivaciones12 = new query($conn, strtoupper($sql));
                                                                                                                                            $hay_encadenados12=false;
                                                                                                                                            if ($rsDerivaciones12->numrows()>0) {
                                                                                                                                                $hay_encadenados12=true;
                                                                                                                                                $table_encadeada12 = new Table("","100%",6);
                                                                                                                                                while ($rsDerivaciones12->getrow()) {
                                                                                                                                                    $idDerivacion12 = $rsDerivaciones12->field("id_padre"); // captura la clave primaria del recordsource
                                                                                                                                                    $depeidDerivacion12=$rsDerivaciones12->field("depe_id");

                                                                                                                                                    $table_encadeada12->addData(addLink($idDerivacion12,"javascript:xajax_verDetalle(1,'$idDerivacion12','consulta','$depeidDerivacion12','',$verDocumentoPadre)","Click aqu&iacute; para consultar el registro","_self"),"L");
                                                                                                                                                    $table_encadeada12->addData(dtos($rsDerivaciones12->field("desp_fecha")),"L");
                                                                                                                                                    $table_encadeada12->addData($rsDerivaciones12->field("tiex_abreviado"),"L");
                                                                                                                                                    $table_encadeada12->addData($rsDerivaciones12->field("num_documento"),"L");
                                                                                                                                                    $table_encadeada12->addData(substr($rsDerivaciones12->field("desp_asunto"),0,40)."...","L");
                                                                                                                                                    $table_encadeada12->addRow();
                                                                                                                                                }                                                                                                                                                
                                                                                                                                            }
                                                                                                                                            if($hay_encadenados12){
                                                                                                                                                $table_encadeada11->addData("<span id='fold_$id12' style='cursor: pointer' onClick=\"javascript:openList('12_$id12')\">&nbsp;+&nbsp;</span>","C");
                                                                                                                                            }else {
                                                                                                                                                 $table_encadeada11->addData("&nbsp;");
                                                                                                                                            }

                                                                                                                                            $table_encadeada11->addData(addLink($idDerivacion11,"javascript:xajax_verDetalle(1,'$idDerivacion11','consulta','$depeidDerivacion11','',$verDocumentoPadre)","Click aqu&iacute; para consultar el registro","_self"),"L");
                                                                                                                                            $table_encadeada11->addData(dtos($rsDerivaciones11->field("desp_fecha")),"L");
                                                                                                                                            $table_encadeada11->addData($rsDerivaciones11->field("tiex_abreviado"),"L");
                                                                                                                                            $table_encadeada11->addData($rsDerivaciones11->field("num_documento"),"L");
                                                                                                                                            $table_encadeada11->addData(substr($rsDerivaciones11->field("desp_asunto"),0,40)."...","L");
                                                                                                                                            $table_encadeada11->addRow();
                                                                                                                                            
                                                                                                                                            if($hay_encadenados12) {
                                                                                                                                                $table_encadeada11->addBreak("<div id=\"12_$id12\" style='visibility: hidden; display: none; margin-left: 50px; width=100%'>".$table_encadeada12->writeHTML()."</div>", false);
                                                                                                                                            }
                                                                                                                                            /*FIN DOCE*/
                                                                                                                                        }
                                                                                                                                    }
                                                                                                                                    if($hay_encadenados11){
                                                                                                                                        $table_encadeada10->addData("<span id='fold_$id11' style='cursor: pointer' onClick=\"javascript:openList('11_$id11')\">&nbsp;+&nbsp;</span>","C");
                                                                                                                                    }else {
                                                                                                                                         $table_encadeada10->addData("&nbsp;");
                                                                                                                                    }  

                                                                                                                                    $table_encadeada10->addData(addLink($idDerivacion10,"javascript:xajax_verDetalle(1,'$idDerivacion10','consulta','$depeidDerivacion10','',$verDocumentoPadre)","Click aqu&iacute; para consultar el registro","_self"),"L");
                                                                                                                                    $table_encadeada10->addData(dtos($rsDerivaciones10->field("desp_fecha")),"L");
                                                                                                                                    $table_encadeada10->addData($rsDerivaciones10->field("tiex_abreviado"),"L");
                                                                                                                                    $table_encadeada10->addData($rsDerivaciones10->field("num_documento"),"L");
                                                                                                                                    $table_encadeada10->addData(substr($rsDerivaciones10->field("desp_asunto"),0,40)."...","L");
                                                                                                                                    $table_encadeada10->addRow();
                                                                                                                                    if($hay_encadenados11) {
                                                                                                                                        $table_encadeada10->addBreak("<div id=\"11_$id11\" style='visibility: hidden; display: none; margin-left: 50px; width=100%'>".$table_encadeada11->writeHTML()."</div>", false);
                                                                                                                                    }
                                                                                                                                    /*FIN ONCE*/
                                                                                                                                }
                                                                                                                            }
                                                                                                                            if($hay_encadenados10){
                                                                                                                                $table_encadeada9->addData("<span id='fold_$id10' style='cursor: pointer' onClick=\"javascript:openList('10_$id10')\">&nbsp;+&nbsp;</span>","C");
                                                                                                                            }else {
                                                                                                                                 $table_encadeada9->addData("&nbsp;");
                                                                                                                            }                                                                                                                            

                                                                                                                            $table_encadeada9->addData(addLink($idDerivacion9,"javascript:xajax_verDetalle(1,'$idDerivacion9','consulta','$depeidDerivacion9','',$verDocumentoPadre)","Click aqu&iacute; para consultar el registro","_self"),"L");
                                                                                                                            $table_encadeada9->addData(dtos($rsDerivaciones9->field("desp_fecha")),"L");
                                                                                                                            $table_encadeada9->addData($rsDerivaciones9->field("tiex_abreviado"),"L");
                                                                                                                            $table_encadeada9->addData($rsDerivaciones9->field("num_documento"),"L");
                                                                                                                            $table_encadeada9->addData(substr($rsDerivaciones9->field("desp_asunto"),0,40)."...","L");
                                                                                                                            $table_encadeada9->addRow();
                                                                                                                            if($hay_encadenados10) {
                                                                                                                                $table_encadeada9->addBreak("<div id=\"10_$id10\" style='visibility: hidden; display: none; margin-left: 50px; width=100%'>".$table_encadeada10->writeHTML()."</div>", false);
                                                                                                                            }
                                                                                                                            /*FIN DIEZ*/
                                                                                                                        }
                                                                                                                    }
                                                                                                                    if($hay_encadenados9){
                                                                                                                        $table_encadeada8->addData("<span id='fold_$id9' style='cursor: pointer' onClick=\"javascript:openList('9_$id9')\">&nbsp;+&nbsp;</span>","C");
                                                                                                                    }else {
                                                                                                                         $table_encadeada8->addData("&nbsp;");
                                                                                                                    }
                                                                                                            
                                                                                                                    $table_encadeada8->addData(addLink($idDerivacion8,"javascript:xajax_verDetalle(1,'$idDerivacion8','consulta','$depeidDerivacion8','',$verDocumentoPadre)","Click aqu&iacute; para consultar el registro","_self"),"L");
                                                                                                                    $table_encadeada8->addData(dtos($rsDerivaciones8->field("desp_fecha")),"L");
                                                                                                                    $table_encadeada8->addData($rsDerivaciones8->field("tiex_abreviado"),"L");
                                                                                                                    $table_encadeada8->addData($rsDerivaciones8->field("num_documento"),"L");
                                                                                                                    $table_encadeada8->addData(substr($rsDerivaciones8->field("desp_asunto"),0,40)."...","L");
                                                                                                                    $table_encadeada8->addRow();
                                                                                                                    if($hay_encadenados9) {
                                                                                                                        $table_encadeada8->addBreak("<div id=\"9_$id9\" style='visibility: hidden; display: none; margin-left: 50px; width=100%'>".$table_encadeada9->writeHTML()."</div>", false);
                                                                                                                    }
                                                                                                                    //FIN NUEVE
                                                                                                                }
                                                                                                            }
                                                                                                            
                                                                                                            if($hay_encadenados8){
                                                                                                                $table_encadeada7->addData("<span id='fold_$id8' style='cursor: pointer' onClick=\"javascript:openList('8_$id8')\">&nbsp;+&nbsp;</span>","C");
                                                                                                            }else {
                                                                                                                 $table_encadeada7->addData("&nbsp;");
                                                                                                            }
                                                                                                            
                                                                                                            
                                                                                                            $table_encadeada7->addData(addLink($idDerivacion7,"javascript:xajax_verDetalle(1,'$idDerivacion7','consulta','$depeidDerivacion7','',$verDocumentoPadre)","Click aqu&iacute; para consultar el registro","_self"),"L");
                                                                                                            $table_encadeada7->addData(dtos($rsDerivaciones7->field("desp_fecha")),"L");
                                                                                                            $table_encadeada7->addData($rsDerivaciones7->field("tiex_abreviado"),"L");
                                                                                                            $table_encadeada7->addData($rsDerivaciones7->field("num_documento"),"L");
                                                                                                            $table_encadeada7->addData(substr($rsDerivaciones7->field("desp_asunto"),0,40)."...","L");
                                                                                                            $table_encadeada7->addRow();
                                                                                                            if($hay_encadenados8) {
                                                                                                                $table_encadeada7->addBreak("<div id=\"8_$id8\" style='visibility: hidden; display: none; margin-left: 50px; width=100%'>".$table_encadeada8->writeHTML()."</div>", false);
                                                                                                            }
                                                                                                            //FIN OCHO
                                                                                                        }
                                                                                                    }
                                                                                                    
                                                                                                    if($hay_encadenados7){
                                                                                                        $table_encadeada6->addData("<span id='fold_$id7' style='cursor: pointer' onClick=\"javascript:openList('7_$id7')\">&nbsp;+&nbsp;</span>","C");
                                                                                                    }else {
                                                                                                         $table_encadeada6->addData("&nbsp;");
                                                                                                    }

                                                                                                    $table_encadeada6->addData(addLink($idDerivacion6,"javascript:xajax_verDetalle(1,'$idDerivacion6','consulta','$depeidDerivacion6','',$verDocumentoPadre)","Click aqu&iacute; para consultar el registro","_self"),"L");
                                                                                                    $table_encadeada6->addData(dtos($rsDerivaciones6->field("desp_fecha")),"L");
                                                                                                    $table_encadeada6->addData($rsDerivaciones6->field("tiex_abreviado"),"L");
                                                                                                    $table_encadeada6->addData($rsDerivaciones6->field("num_documento"),"L");
                                                                                                    $table_encadeada6->addData(substr($rsDerivaciones6->field("desp_asunto"),0,40)."...","L");
                                                                                                    $table_encadeada6->addRow();
                                                                                                    if($hay_encadenados7) {
                                                                                                        $table_encadeada6->addBreak("<div id=\"7_$id7\" style='visibility: hidden; display: none; margin-left: 50px; width=100%'>".$table_encadeada7->writeHTML()."</div>", false);
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                            
                                                                                            if($hay_encadenados6){
                                                                                                $table_encadeada5->addData("<span id='fold_$id6' style='cursor: pointer' onClick=\"javascript:openList('6_$id6')\">&nbsp;+&nbsp;</span>","C");
                                                                                             } else {
                                                                                                 $table_encadeada5->addData("&nbsp;");
                                                                                             }

                                                                                            $table_encadeada5->addData(addLink($idDerivacion5,"javascript:xajax_verDetalle(1,'$idDerivacion5','consulta','$depeidDerivacion5','',$verDocumentoPadre)","Click aqu&iacute; para consultar el registro","_self"),"L");
                                                                                            $table_encadeada5->addData(dtos($rsDerivaciones5->field("desp_fecha")),"L");
                                                                                            $table_encadeada5->addData($rsDerivaciones5->field("tiex_abreviado"),"L");
                                                                                            $table_encadeada5->addData($rsDerivaciones5->field("num_documento"),"L");
                                                                                            $table_encadeada5->addData(substr($rsDerivaciones5->field("desp_asunto"),0,40)."...","L");
                                                                                            $table_encadeada5->addRow();
                                                                                            if($hay_encadenados6) {
                                                                                                $table_encadeada5->addBreak("<div id=\"6_$id6\" style='visibility: hidden; display: none; margin-left: 50px; width=100%'>".$table_encadeada6->writeHTML()."</div>", false);
                                                                                            }
                                                                                            //FIN SEIS
                                                                                        }
                                                                                    }  
                                                                                    if($hay_encadenados5){
                                                                                        $table_encadeada4->addData("<span id='fold_$id5' style='cursor: pointer' onClick=\"javascript:openList('5_$id5')\">&nbsp;+&nbsp;</span>","C");
                                                                                     } else {
                                                                                         $table_encadeada4->addData("&nbsp;");
                                                                                     }
                                                                                    $table_encadeada4->addData(addLink($idDerivacion4,"javascript:xajax_verDetalle(1,'$idDerivacion4','consulta','$depeidDerivacion4','',$verDocumentoPadre)","Click aqu&iacute; para consultar el registro","_self"),"L");
                                                                                    $table_encadeada4->addData(dtos($rsDerivaciones4->field("desp_fecha")),"L");
                                                                                    $table_encadeada4->addData($rsDerivaciones4->field("tiex_abreviado"),"L");
                                                                                    $table_encadeada4->addData($rsDerivaciones4->field("num_documento"),"L");
                                                                                    $table_encadeada4->addData(substr($rsDerivaciones4->field("desp_asunto"),0,40)."...","L");
                                                                                    $table_encadeada4->addRow();
                                                                                    if($hay_encadenados5) {
                                                                                        $table_encadeada4->addBreak("<div id=\"5_$id5\" style='visibility: hidden; display: none; margin-left: 50px; width=100%'>".$table_encadeada5->writeHTML()."</div>", false);
                                                                                    }
                                                                                    //FIN CINCO
                                                                                }
                                                                            }  
                                                                            if($hay_encadenados4){
                                                                                $table_encadeada3->addData("<span id='fold_$id4' style='cursor: pointer' onClick=\"javascript:openList('4_$id4')\">&nbsp;+&nbsp;</span>","C");
                                                                             } else {
                                                                                 $table_encadeada3->addData("&nbsp;");
                                                                             }
                                                                             
                                                                            $table_encadeada3->addData(addLink($idDerivacion3,"javascript:xajax_verDetalle(1,'$idDerivacion3','consulta','$depeidDerivacion3','',$verDocumentoPadre)","Click aqu&iacute; para consultar el registro","_self"),"L");
                                                                            $table_encadeada3->addData(dtos($rsDerivaciones3->field("desp_fecha")),"L");
                                                                            $table_encadeada3->addData($rsDerivaciones3->field("tiex_abreviado"),"L");
                                                                            $table_encadeada3->addData($rsDerivaciones3->field("num_documento"),"L");
                                                                            $table_encadeada3->addData(substr($rsDerivaciones3->field("desp_asunto"),0,40)."...","L");
                                                                            $table_encadeada3->addRow();
                                                                            
                                                                            if($hay_encadenados4) {
                                                                                $table_encadeada3->addBreak("<div id=\"4_$id4\" style='visibility: hidden; display: none; margin-left: 40px; width=100%'>".$table_encadeada4->writeHTML()."</div>", false);
                                                                            }
                                                                            //FIN CUATRO
                                                                        }
                                                                    }
                                                                    if($hay_encadenados3){
                                                                       $table_encadeada2->addData("<span id='fold_$id3' style='cursor: pointer' onClick=\"javascript:openList('3_$id3')\">&nbsp;+&nbsp;</span>","C");
                                                                    } else {
                                                                        $table_encadeada2->addData("&nbsp;");
                                                                    }       
                                                                    $table_encadeada2->addData(addLink($idDerivacion2,"javascript:xajax_verDetalle(1,'$idDerivacion2','consulta','$depeidDerivacion2','',$verDocumentoPadre)","Click aqu&iacute; para consultar el registro","_self"),"L");
                                                                    $table_encadeada2->addData(dtos($rsDerivaciones2->field("desp_fecha")),"L");
                                                                    $table_encadeada2->addData($rsDerivaciones2->field("tiex_abreviado"),"L");
                                                                    $table_encadeada2->addData($rsDerivaciones2->field("num_documento"),"L");
                                                                    $table_encadeada2->addData(substr($rsDerivaciones2->field("desp_asunto"),0,40)."...","L");
                                                                    $table_encadeada2->addRow();
                                                                    if($hay_encadenados3) {
                                                                        $table_encadeada2->addBreak("<div id=\"3_$id3\" style='visibility: hidden; display: none; margin-left: 30px; width=100%'>".$table_encadeada3->writeHTML()."</div>", false);
                                                                    }
                                                                    //FIN TRES
                                                                }
                                                            }

                                                            if($hay_encadenados2){
                                                                $table_encadeada->addData("<span id='fold_$id2' style='cursor: pointer' onClick=\"javascript:openList('2_$id2')\">&nbsp;+&nbsp;</span>","C");
                                                            } else {
                                                                $table_encadeada->addData("&nbsp;");
                                                            }
                                                            $table_encadeada->addData(addLink($idDerivacion,"javascript:xajax_verDetalle(1,'$idDerivacion','consulta','$depeidDerivacion','',$verDocumentoPadre)","Click aqu&iacute; para consultar el registro","_self"),"L");
                                                            $table_encadeada->addData(dtos($rsDerivaciones->field("desp_fecha")),"L");
                                                            $table_encadeada->addData($rsDerivaciones->field("tiex_abreviado"),"L","L");
                                                            $table_encadeada->addData($rsDerivaciones->field("num_documento"),"L");
                                                            $table_encadeada->addData(substr($rsDerivaciones->field("desp_asunto"),0,40)."...","L");
                                                            $table_encadeada->addRow();
                                                            if($hay_encadenados2) {
                                                                $table_encadeada->addBreak("<div id=\"2_$id2\" style='visibility: hidden; display: none; margin-left: 20px; width=100%'>".$table_encadeada2->writeHTML()."</div>", false);
                                                            }
                                                            //FIN DOS
                                                        }
                                                    }
                                                }
                                                /*si hay lista encadenados*/
                                                if($hay_encadenados){
                                                    $otable->addData("<span id='fold_$id' style='cursor: pointer' onClick=\"javascript:openList('1_$id')\">&nbsp;+&nbsp;</span>","C");
                                                } else {
                                                    $otable->addData("&nbsp;");
                                                }

                                                $otable->addData(addLink($id,"javascript:xajax_verDetalle(1,'$id','consulta','$depeid','',0)","Click aqu&iacute; para consultar el registro","_self"),"L");
                                                $otable->addData(dtos($rs->field("desp_fecha")),"L");
						$otable->addData($rs->field("tiex_abreviado"),"L");
						$otable->addData($rs->field("num_documento"),"L");
						$otable->addData(substr($rs->field("desp_asunto"),0,40)."...","L");
                                                $otable->addData($rs->field("desp_folios"),"c");
						$otable->addRow();
                                                $total_folios=$total_folios+$rs->field("desp_folios");
                                                if($hay_encadenados) {
                                                    $otable->addBreak("<div id=\"1_$id\" style='visibility: hidden; display: none; margin-left: 10px; width=100%'>".$table_encadeada->writeHTML()."</div>", false);
                                                }
                                                
					}
				
                                if($total_folios>0){
                                    $otable->addTotal("&nbsp;");
                                    $otable->addTotal("&nbsp;");
                                    $otable->addTotal("&nbsp;");
                                    $otable->addTotal("&nbsp;");
                                    $otable->addTotal("&nbsp;");
                                    $otable->addTotal("Total de Registros: ".$rs->numrows(),"L");
                                    $otable->addTotal($total_folios,"C");
                                    $otable->addRow();                                    
                                }else{                                
                                    $contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>&nbsp;</div>";
                                    $contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";
                                }
                                $contenido_respuesta.=$otable->writeHTML();


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
        
	function verDetalle($op,$id,$vista='default',$depeid=0,$dede_id='',$permisodelPadre)
	{
		global $conn;
                $oForm = new AddTableForm();
                $oForm->setLabelWidth("20%");
                $oForm->setDataWidth("80%");
                $oForm->setLabelTD("LabelOrangeTD2");

                $sql=new despacho_SQLlista();
		$sql->whereID($id);
                
                //SI VIENE DE DOMINIO
//                if(strpos($_SERVER['SERVER_NAME'],".gob.pe",0)>0){
//                    $sql->whereTipoDespacho(142);//OTRAS ENTIDADES
//                }                        
                
                if($depeid==0){//viene desde una consulta externa
                    $sql->whereTipoDespacho(142);
                }
                $sql=$sql->getSQL();
                //echo $sql;
        	$rs = new query($conn, strtoupper($sql));
                $rs->getrow();

                if($rs->numrows()>0){
                    $id=$rs->field("id");
                    $modo_recepcion=$rs->field("modo_recepcion");
                    $procedimiento=$rs->field("procedimiento");
                    
                    $tabl_tipodespacho=$rs->field("tabl_tipodespacho");
                    $tipo_despacho=$rs->field("tipo_despacho");
                    $depe_nombre=$rs->field("depe_nombre").'/'.$rs->field("depe_superior_nombre");
                    $firma=$rs->field("desp_firma");
                    $cargo=$rs->field("desp_cargo");
                    $telefono=$rs->field("desp_telefono");
                    $email=$rs->field("desp_email");
                    
                    $desp_codigo=$rs->field("desp_codigo");
                    $desp_entidad_origen=$rs->field("desp_entidad_origen");
                    $desp_direccion=$rs->field("desp_direccion");

                    $prov_razsocial=$rs->field("prov_razsocial");
                    $desp_descripaux=$rs->field("desp_descripaux");

                    $fecha_documento=$rs->field("desp_fecha");
                    $tiex_id=$rs->field("tiex_id");
                    $tipo_expediente=$rs->field("tiex_descripcion");
                    $num_documento=$rs->field("num_documento");
                    $desp_adjuntados_exp=$rs->field("desp_adjuntados_exp");
                    $desp_procesador=$rs->field("desp_procesador");
                    
                    $asunto=$rs->field("desp_asunto");
                    $desp_expediente=$rs->field("desp_expediente");
                    $numero_folios=$rs->field("desp_folios");
                    $proyectado_por=$rs->field("desp_proyectadopor");
                    $prioridad_atencion=$rs->field("prat_descripcion");
                    $desp_trelacionado=$rs->field("desp_trelacionado");
                    $desp_notas=$rs->field("desp_notas");
                    $desp_rda=$rs->field("desp_rda");
                    $desp_legal=$rs->field("desp_legal");
                    $desp_exp_legal=$rs->field('desp_exp_legal');
                    $desp_demandante=$rs->field('desp_demandante');
                    $desp_demandado=$rs->field('desp_demandado');
                    $desp_resolucion=$rs->field('desp_resolucion');
                    $pedi_estado2=$rs->field('pedi_estado2');
                    $periodo=$rs->field('desp_anno');
                    $name_file=$rs->field('desp_file_firmado');    
                    $desp_url_mas_files=$rs->field("desp_url_mas_files");
                    $username=$rs->field("username");
                    $desp_fregistro=$rs->field("desp_fregistro");
                    $ahora=$rs->field("ahora");
                    $desp_set_derivados=$rs->field("desp_set_derivados");
                    
                    $verDocumento=conPermisoVerDocumento($id,$rs->field("usua_id"),$rs->field("usua_idfirma"),$permisodelPadre,$desp_set_derivados,$tabl_tipodespacho);
                    
                   // $oForm->setBackTD("BackOrangeTD");

                    if($vista=='default' || $vista=='misDerivaciones' || $vista=='recibe' || $vista=='busquedas' || $vista=='archivado'){
                        $oForm->addHtml("<tr><td colspan=2 class=\"BackOrangeTD\" align=\"left\">"
                                    .addLink("<img src=\"../../img/go-rt-on.gif\" align=\"absmiddle\" border=\"0\">","javascript:xajax_clearDiv('DivDetalles')","Click aqu&iacute; para Cerrar")
                                    ."</td>");
                    }
                    if($vista=='default'){
                        //$idx=addLink($id,"javascript:abreConsulta('$id')","Click aqu&iacute; para Seguimiento de registro");
                        $idx=addLink($id,"javascript:lectorPDF('/sisadmin/gestdoc/controllers/procesar_data.php?nr_numTramite=$id&vista=NoConsulta','Seguimiento')","Click aqu&iacute; para Seguimiento de registro");
                    }else{
                        $arExpediente=explode(".",$id);
                        if(is_array($arExpediente)){
                            $expediente=$arExpediente[0];
                            $idx=addLink($expediente,"javascript:document.location.href='/sisadmin/gestdoc/controllers/procesar_data.php?nr_numTramite=$expediente&vista=NoConsult'","Click aqu&iacute; para Seguimiento de registro","");
                            if(isset($arExpediente[1])){
                                $idx=$idx.'.'.$arExpediente[1];
                            }
                        }else{
                            $idx=$id;
                        }
                        
                    }        
                    
                    $oForm->addBreak("<font size=\"-1\"><b>N&uacute;mero de ".NAME_EXPEDIENTE.": </font>".$idx."</b>",true,2,"center" );

                    if($vista=='consulta'){
                        $fecha_consulta=_dttos($ahora);
                        $contultaTime="Consulta Realizada el ".$fecha_consulta;
                        $oForm->addBreak("<center>$contultaTime</center>",true);
                    }

                    //$oForm->addField("Modo Recepci&oacute;n/Envio",$modo_recepcion);
                    //$oForm->addField("Prioridad Atenci&oacute;n: ",$prioridad_atencion);
                    $oForm->addField("Tipo de ".NAME_EXPEDIENTE.": ",$tipo_despacho);

                    if($procedimiento){
                        $oForm->addField("Procedimiento: ",$procedimiento);
                    }
                            
                    if($tabl_tipodespacho==142){//otras entidades
                        
                        if($desp_codigo){
                            $oForm->addField("C&oacute;digo: ",$desp_codigo);
                        }
                        if($desp_entidad_origen){
                            $oForm->addField("Entidad Origen: ",$desp_entidad_origen);
                        }
                        
                        if($desp_direccion){
                            $oForm->addField("Direcci&oacute;n: ",$desp_direccion);
                        }
                        //$oForm->addField("Descipci&oacute;n Auxiliar:",$desp_descripaux);
                    }else{
                        $oForm->addField("Dependencia Origen: ",$depe_nombre);
                    }
                    if($firma){
                        $oForm->addField("Firma: ",$firma);
                    }
                    if($cargo){
                        $oForm->addField("Cargo: ",$cargo);
                    }
                    
                    if($telefono){
                        $oForm->addField("Tel&eacute;fono: ",$telefono);
                    }
                    
                    if($email){
                        $oForm->addField("Email: ",$email);
                    }
                    
                    $oForm->addField("Fecha de Documento: ",dtos($fecha_documento));
                    $oForm->addField("Tipo de Documento: ",$tipo_expediente);

                    $nameFileFullPath = "../../../docs/gestdoc/".SIS_EMPRESA_RUC."/$periodo/$id/$name_file";
                    //OJO la siguinete line se analiza en /sisadmin/gestdoc/controllers/procesar_data.php;
                    if( file_exists($nameFileFullPath) && $name_file && $verDocumento==1 ){
                        $link=addLink($num_documento,"#","Click aqu&iacute; para Ver Documento","controle","link download-link",$id);
                           
                        //$link=addLink($num_documento,"javascript:imprimir('$id')","Click aqu&iacute; para Ver Documento","controle");
                    }else{
                        $link=$num_documento;
                    }                    
                    
                    //$oForm->addField("N&uacute;mero: ",$num_documento);
                    if($num_documento){
                        $oForm->addField("N&uacute;mero: ","$link");
                        $oForm->addHtml("<tr><td colspan=2><div id=\"divResultados\"></div></td></tr>");
                    }
                    
                    if($desp_adjuntados_exp){
                        $oForm->addField("Registros Adjuntados: ",$desp_adjuntados_exp);
                    }
                    
                    if($asunto){
                        $oForm->addField("Asunto: ",$asunto);
                    }
                    
                    $exp=addLink($desp_expediente,"/sisadmin/gestdoc/controllers/procesar_data.php?nr_numTramite=$desp_expediente&vista=NoConsult","Click aqu&iacute; para Seguimiento de registro","");
                    $oForm->addField("Referencia (Expediente): ",$exp);
                    
                    if($numero_folios){
                        $oForm->addField("N&uacute;mero de Folios: ",$numero_folios);
                    }
                    //$oForm->addField("Proyectado Por: ",$proyectado_por);
                    //$oForm->addField(NAME_EXPEDIENTE." Relacionado: ",$desp_trelacionado);
                    if($desp_notas){
                        $oForm->addField("Observaciones: ",$desp_notas);
                    }
                    
                    /*REGISTRO DE DENUNCUAS AMBIENTALES*/
                    if( $desp_rda == 1 ){
                        $desp_tipo_denunciante=$rs->field('desp_tipo_denunciante');
                        $tipo_denunciante=$rs->field('tipo_denunciante');
                        $desp_denunciante_tipo_documento=$rs->field('tipo_denunciante_descripcion');
                        
                        $desp_denunciante_identificacion=$rs->field('desp_denunciante_identificacion');
                        
                        $denunciante=$rs->field('denunciante');
                        $denunciante_genero=$rs->field('denunciante_genero');

                        $desp_denunciante_representante_legal=$rs->field('desp_denunciante_representante_legal');
                        $desp_denunciante_direccion=$rs->field('desp_denunciante_direccion');
                        
                        $desp_denunciante_direccion_ubigeo=$rs->field('desp_denunciante_direccion_ubigeo');
                        $ubigeo_denunciante=$rs->field('ubigeo_denunciante');
                        $desp_denunciante_telefono_fijo=$rs->field('desp_denunciante_telefono_fijo');
                        $desp_denunciante_telefono_movil=$rs->field('desp_denunciante_telefono_movil');
                        $desp_denunciante_email=$rs->field('desp_denunciante_email');
                        $desp_denunciado_tipo_documento=$rs->field('desp_denunciado_tipo_documento');
                        $desp_denunciado_identificacion=$rs->field('desp_denunciado_identificacion');
                        
                        $denunciado=$rs->field('denunciado');
                        $denunciado_genero=$rs->field('denunciante_genero');

                        $desp_denunciado_representante_legal=$rs->field('desp_denunciado_representante_legal');
                        $desp_denunciado_direccion=$rs->field('desp_denunciado_direccion');
                        $desp_denunciado_direccion_ubigeo=$rs->field('desp_denunciado_direccion_ubigeo');
                        $ubigeo_denunciado=$rs->field('ubigeo_denunciado');

                        $desp_hechos_direccion=$rs->field('desp_hechos_direccion');
                        $desp_hechos_direccion_ubigeo=$rs->field('desp_hechos_direccion_ubigeo');
                        $ubigeo_hechos=$rs->field('ubigeo_hechos');

                        $desp_hechos_referencia=$rs->field('desp_hechos_referencia');
                        $desp_hechos_detalles=$rs->field('desp_hechos_detalles');                                                
                        
                        $oForm->addBreak("<b>DATOS DEL DENUNCIANTE</b>");
                        if($desp_tipo_denunciante==1){//ANONIMO
                            if ($vista=='consulta'){
                                $img='../../intranet/img/help.png';
                            }else{
                                $img='../../img/help.png';
                            }
                            $oForm->addField("Tipo Denunciante: ","<table><tr><td><img src='$img' /></td><td><font size=2px><b>$tipo_denunciante</b></font></td></tr></table>");
                        }else if($desp_tipo_denunciante==2){//CON RESERVA DE DATOS
                            //BUSCO AL JEFE DE LA OFICINA
                            
                            $dni_jefe_oficina= getDbValue("SELECT c.pers_dni
                                                              FROM catalogos.dependencia a
                                                              LEFT JOIN personal.persona_datos_laborales b ON a.pdla_id=b.pdla_id
                                                              LEFT JOIN personal.persona c                 ON b.pers_id=c.pers_id                                                              
                                                              WHERE a.depe_id=(SELECT depe_id_destinatario
                                                                                FROM gestdoc.procedimiento
                                                                                WHERE proc_id=9998)  /*PROCEDIMIENTO DE DENUNCIA*/
                                                            ");
                            
                            ///SOLO TIENE ACCESO EL JEFE DE OFICINA A DONDE PASAN LAS DENUNCIAS
                            if(getSession("sis_username")==$dni_jefe_oficina && $vista=='consulta'){
                                $oFormx = new AddTableForm();
                                $oFormx->setLabelWidth("20%");
                                $oFormx->setDataWidth("80%");

                                $oFormx->addField("Identificaci&oacute;n: ",$desp_denunciante_identificacion);
                                $oFormx->addField("Denunciante: ",$denunciante);
                                if($desp_denunciante_tipo_documento==1){//PN
                                    $oFormx->addField("G&eacute;nero: ",$denunciante_genero);                        
                                }elseif($desp_denunciante_tipo_documento==6){//PJ
                                    $oFormx->addField("Representante Legal: ",$desp_denunciante_representante_legal);
                                }
                                $oFormx->addField("Direcci&oacute;n: ",$desp_denunciante_direccion);
                                $oFormx->addField("Depto-Prov-Distrito: ",$ubigeo_denunciante);
                                $oFormx->addField("Tel&eacute;fono Fijo: ",$desp_denunciante_telefono_fijo);
                                $oFormx->addField("Tel&eacute;fono Movil: ",$desp_denunciante_telefono_movil);
                                $oFormx->addField("Correo Electr&oacute;nico: ",$desp_denunciante_email);                        
                                
                                $img='../../intranet/img/look_c.png';
                                $oForm->addField("Tipo Denunciante: ","<table><tr><td><img src='$img' /></td><td><font size=2px><b><div class='tooltip'>$tipo_denunciante<span class='tooltiptext'>".$oFormx->writeHTML()."</span></div></b></font></td></tr></table>");
                            }else{                            
                                if ($vista=='consulta'){
                                    $img='../../intranet/img/look_c.png';
                                }else{
                                    $img='../../img/look_c.png';
                                }
                                $oForm->addField("Tipo Denunciante: ","<table><tr><td><img src='$img' /></td><td><font size=2px><b>$tipo_denunciante</b></font></td></tr></table>");
                            }    
                            
                        }else if($desp_tipo_denunciante==3){//SIN RESERVA DE DATOS
                            if ($vista=='consulta'){
                                $img='../../intranet/img/look_o.png';
                            }else{
                                $img='../../img/look_o.png';
                            }
                            $oForm->addField("Tipo Denunciante: ","<table><tr><td><img src='$img' /></td><td><font size=2px><b>$tipo_denunciante</b></font></td></tr></table>");
                        }                        
                                                
                        if( $desp_tipo_denunciante==3 ){/*SIN RESERVA DE DATOS*/
                            $oForm->addField("Identificaci&oacute;n: ",$desp_denunciante_identificacion);
                            $oForm->addField("Denunciante: ",$denunciante);
                            if($desp_denunciante_tipo_documento==1){//PN
                                $oForm->addField("G&eacute;nero: ",$denunciante_genero);                        
                            }elseif($desp_denunciante_tipo_documento==6){//PJ
                                $oForm->addField("Representante Legal: ",$desp_denunciante_representante_legal);
                            }
                            $oForm->addField("Direcci&oacute;n: ",$desp_denunciante_direccion);
                            $oForm->addField("Depto-Provincia-Distrito: ",$ubigeo_denunciante);
                            $oForm->addField("Tel&eacute;fono Fijo: ",$desp_denunciante_telefono_fijo);
                            $oForm->addField("Tel&eacute;fono Movil: ",$desp_denunciante_telefono_movil);
                            $oForm->addField("Correo Electr&oacute;nico: ",$desp_denunciante_email);
                        }
                        
                        $oForm->addBreak("<b>DATOS DEL DENUNCIADO</b>");
                        $oForm->addField("Identificaci&oacute;n: ",$desp_denunciado_identificacion);
                        $oForm->addField("Denunciado: ",$denunciado);
                        
                        if($desp_denunciado_tipo_documento==1){//PN
                            $oForm->addField("G&eacute;nero: ",$denunciado_genero);
                        }elseif($desp_denunciado_tipo_documento==6){//PJ
                            $oForm->addField("Representante Legal: ",$desp_denunciado_representante_legal);
                        }
                        $oForm->addField("Direcci&oacute;n: ",$desp_denunciado_direccion);
                        $oForm->addField("Depto-Provincia-Distrito: ",$ubigeo_denunciado);
                        $oForm->addBreak("<b>HECHOS DENUNCIADOS</b>");
                        $oForm->addField("Direcci&oacute;n: ",$desp_hechos_direccion);
                        $oForm->addField("Depto-Provincia-Distrito: ",$ubigeo_hechos);
                        $oForm->addField("Referencia: ",$desp_hechos_referencia);
                        $count_lines=substr_count($desp_hechos_detalles, "\n");
                        $count_lines=$count_lines>0?$count_lines:0;
                        $count_lines=$count_lines+2;
                        $oForm->addField("Detalles: ",textAreaField("Asunto","Er_desp_asunto",$desp_hechos_detalles,$count_lines,80,0,'readonly',0));
                    /*FIN REGISTRO DE DENUNCUAS AMBIENTALES*/
                    }
                    if($desp_legal){
                        $oForm->addBreak("<b>Datos Judiciales</b>");
                        $oForm->addField("N&ordm; Exp.Legal: ",$desp_exp_legal);
                        $oForm->addField("Demandante: ",$desp_demandante);
                        $oForm->addField("Demandado: ",$desp_demandado);
                        $oForm->addField("Resoluci&oacute;n ",$desp_resolucion);
                    }
                    
                    if($desp_url_mas_files){
                        $oForm->addField("Enlace(URL)+Archivos: ",$desp_url_mas_files);
                    }
                    $oForm->addBreak("Creado Por el Usuario:  [Fecha y Hora de Registro]");
                    $oForm->addField($username,"[".date("d/m/Y H:i:s",strtotime($desp_fregistro))."]");
                    if($pedi_estado2==2){//AUTORIZADO
                        $oForm->addField("Situaci&oacute;n","<font class='OBLIGATORIO'><b>AUTORIZADO</b></font>");
                    }
                    
                    $otable = new  Table("","100%",10);
                    $otable->setColumnTD("ColumnBlueTD") ;
                    $otable->setColumnFont("ColumnWholeFont") ;
                    $otable->setFormTotalTD("FormTotalBlueTD");
                    $otable->setAlternateBackTD("AlternateBackBlueTD");
                    $otable->setDataFONT('DataFONT2');
                    if($vista=='default'){
                        $otable->addBreak("<div align='center' style='color:#000000'><b>:: DERIVACIONES ::</b></div>");
                        $otable->addColumnHeader("Dependencia/Usuario",false,"45%", "L"); // Título, Ordenar?, ancho, alineación
                        $otable->addColumnHeader("Proveido",false,"42%", "L"); // Título, Ordenar?, ancho, alineación
                        $otable->addColumnHeader("Dist",false,"5%", "L"); // Título, Ordenar?, ancho, alineación
                        $otable->addColumnHeader("Recibido",false,"8%", "L");
                        $otable->addRow(); // adiciona la linea (TR)

                        $sql=new despachoDerivacion_SQLlista();
                        $sql->wherePadreID($id);
                        $sql->orderUno();
                        $sql=$sql->getSQL();
                        //$objResponse->addAlert($sql);
                        $rs = new query($conn, strtoupper($sql));
                        while ($rs->getrow()) {
                            if($rs->field('usuario_destino'))
                                $otable->addData($rs->field('depe_nombrecorto_destino').'/'.$rs->field('depe_superior_nombre_destino').'['.$rs->field('usuario_destino').']');
                            else
                                $otable->addData($rs->field('depe_nombrecorto_destino').'/'.$rs->field('depe_superior_nombre_destino'));

                            $otable->addData($rs->field('dede_proveido'));
                            if($rs->field('dede_concopia'))
                               $otable->addData('Cc');
                            else
                                $otable->addData('');
                            
                            if($rs->field('dede_fecharecibe')){
                                $otable->addData(date("d/m/Y H:i:s",strtotime($rs->field('dede_fecharecibe'))).'['.$rs->field('usuario_recibe').']');
                                $otable->addRow('RECIBIDO');
                            }else{
                                $otable->addData("&nbsp;");
                                $otable->addRow();
                            }
                        }
                    }elseif($vista=='recibe'){
                        $otable->addBreak("<div align='center' style='color:#000000'><b>:: PROCEDENCIA ::</b></div>");
                        $otable->addColumnHeader("Fe.Registro",false,"10%", "L"); // Título, Ordenar?, ancho, alineación
                        $otable->addColumnHeader("Dependencia",false,"20%", "L"); // Título, Ordenar?, ancho, alineación
                        $otable->addColumnHeader("Usuario",false,"10%", "L"); // Título, Ordenar?, ancho, alineación
                        $otable->addColumnHeader("Proveido",false,"60%", "L"); // Título, Ordenar?, ancho, alineación
                        $otable->addRow(); // adiciona la linea (TR)

                        $sql=new despachoDerivacion_SQLlista();
                        $sql->whereID($dede_id);
                        $sql=$sql->getSQL();
                        //$objResponse->addAlert($sql);
                        $rs = new query($conn, $sql);
                        while ($rs->getrow()) {
                            $otable->addData(date("d/m/Y H:i:s",strtotime($rs->field('dede_fregistro'))));
                            $otable->addData($rs->field('depe_nombrecorto_origen').'/'.$rs->field('depe_superior_nombre_origen'));
                            $otable->addData($rs->field('usuario_origen'));
                            $otable->addData($rs->field('dede_proveido'));
                            $otable->addRow();
                        }
                    }
                    elseif($vista=='misDerivaciones'){

                        $sql=new despachoDerivacion_SQLlista();
                        $sql->whereID($dede_id);
                        $sql=$sql->getSQL();
                        //$objResponse->addAlert($sql);
                        $i=1;
                        $rs = new query($conn, $sql);
                        while ($rs->getrow()) {
                            if($i==1){//crea en el encabezado en el primer registro
                                if($rs->field('dede_estado')==7)//Activado
                                    $otable->addBreak("<div align='center' style='color:#000000'><b>:: PROCEDE/ACTIVADO ::</b></div>");
                                else
                                    $otable->addBreak("<div align='center' style='color:#000000'><b>:: PROCEDE/RECIBIDO ::</b></div>");

                                $otable->addColumnHeader("Dependencia",false,"20%", "L"); // Título, Ordenar?, ancho, alineación
                                $otable->addColumnHeader("Usuario",false,"10%", "L"); // Título, Ordenar?, ancho, alineación
                                $otable->addColumnHeader("Proveido",false,"60%", "L"); // Título, Ordenar?, ancho, alineación

                                if($rs->field('dede_estado')==7)//Activado
                                    $otable->addColumnHeader("Activado",false,"10%", "L"); // Título, Ordenar?, ancho, alineación
                                else
                                    $otable->addColumnHeader("Recibido",false,"10%", "L"); // Título, Ordenar?, ancho, alineación

                                $otable->addRow(); // adiciona la linea (TR)
                            }
                            $otable->addData($rs->field('depe_nombrecorto_origen').'/'.$rs->field('depe_superior_nombre_origen'));
                            $otable->addData($rs->field('usuario_origen'));
                            $otable->addData($rs->field('dede_proveido'));

                            if($rs->field('dede_estado')==7)//Activado
                                $otable->addData(date("d/m/Y H:i:s",strtotime($rs->field('dede_fechaactiva'))));
                            else
                                $otable->addData(date("d/m/Y H:i:s",strtotime($rs->field('dede_fecharecibe'))));

                            $otable->addRow();
                            $i++;
                        }

                        $otable->addBreak("<div align='center' style='color:#000000'><b>:: MIS ULTIMAS DERIVACIONES ::</b></div>");
                        $otable->addColumnHeader("Dependencia",false,"20%", "L"); // Título, Ordenar?, ancho, alineación
                        $otable->addColumnHeader("Usuario",false,"10%", "L"); // Título, Ordenar?, ancho, alineación
                        $otable->addColumnHeader("Proveido",false,"68%", "L"); // Título, Ordenar?, ancho, alineación
                        $otable->addColumnHeader("Dist",false,"2%", "L"); // Título, Ordenar?, ancho, alineación
                        $otable->addRow(); // adiciona la linea (TR)

                        $sql=new despachoDerivacion_SQLlista();
                        $sql->wherePadreID($id);
                        $sql->whereEstado(2);
                        $sql->whereUsuaIDCrea(getSession("sis_userid"));
                        $sql->orderUno();
                        $sql=$sql->getSQL();
                        //$objResponse->addAlert($sql);
                        $rs = new query($conn, strtoupper($sql));

                        while ($rs->getrow()) {
                            //if($rs->field('dede_estado')==2 && $rs->field('usua_idcrea')==getSession("sis_userid")) {
                                $otable->addData($rs->field('depe_nombrecorto_destino').'/'.$rs->field('depe_superior_nombre_destino'));
                                $otable->addData($rs->field('usuario_destino'));
                                $otable->addData($rs->field('dede_proveido'));

                                if($rs->field('dede_concopia'))
                                   $otable->addData('Cc');
                                else
                                    $otable->addData('');

                                $otable->addRow();
                            //}
                        }
                    }elseif($vista=='archivado'){
                        $otable->addBreak("<div align='center' style='color:#000000'><b>:: ARCHIVADO ::</b></div>");
                        $otable->addColumnHeader("Fecha",false,"10%", "L"); // Título, Ordenar?, ancho, alineación
                        $otable->addColumnHeader("Archivo",false,"30%", "L"); // Título, Ordenar?, ancho, alineación
                        $otable->addColumnHeader("Usuario",false,"10%", "L"); // Título, Ordenar?, ancho, alineación
                        $otable->addColumnHeader("Motivo",false,"50%", "L"); // Título, Ordenar?, ancho, alineación
                        $otable->addRow(); // adiciona la linea (TR)

                        $sql=new despachoDerivacion_SQLlista();
                        $sql->whereID($dede_id);
                        $sql=$sql->getSQL();
                        
                        //$objResponse->addAlert($sql);
                        $rs = new query($conn, $sql);
                        while ($rs->getrow()) {
                            $otable->addData(date("d/m/Y H:i:s",strtotime($rs->field('dede_fechaarchiva'))));
                            $otable->addData('ARCHIV.'.$rs->field('tipo_archivador').': '.$rs->field('archivador'));
                            $otable->addData($rs->field('usuario_archiva'));
                            $otable->addData($rs->field('dede_motivoarchiva'));
                            $otable->addRow();
                        }
                    }elseif($vista=='consulta'){
                        
                        //MUESTRO LOS FIRMANTES
                        if (SIS_GESTDOC_TIPO==2){ //CON FIRMA
                            $firmas=new despachoFirmas_SQLlista();
                            $firmas->wherePadreID($id);
                            $firmas->orderUno();
                            $sql=$firmas->getSQL();
                            $rsFirmas = new query($conn, $sql);
                            if($rsFirmas->numrows()>0){
                                    $otableFirmas = new Table("RELACION DE FIRMANTES","100%",5);
                                    $otableFirmas->addColumnHeader("Empleado",false,"25%", "C"); 
                                    $otableFirmas->addColumnHeader("Cargo",false,"20%", "C"); 
                                    $otableFirmas->addColumnHeader("Dependencia",false,"20%", "C"); 
                                    $otableFirmas->addColumnHeader("Tipo",false,"20%", "C"); 
                                    $otableFirmas->addColumnHeader("Proceso",false,"15%", "C"); 
                                    $otableFirmas->addRow();
                                    while ($rsFirmas->getrow()) {
                                        $otableFirmas->addData($rsFirmas->field("empleado").' / '.$rsFirmas->field("pers_dni"),"l");
                                        $otableFirmas->addData($rsFirmas->field("cargo"),"C");
                                        $otableFirmas->addData($rsFirmas->field("dependencia").'/'.$rsFirmas->field("depe_superior_nombre"),"C");
                                        $otableFirmas->addData($rsFirmas->field("tipo_firma"),"C");
                                        $otableFirmas->addData(substr($rsFirmas->field("defi_hfirma"),0,19),"C");
                                        $otableFirmas->addRow();
                                    }
                                    $oForm->addHtml("<tr><td colspan=2>".$otableFirmas->writeHTML()."</td></tr>");
                            }
                        }
                        
                        $otable->addBreak("<div align='center' style='color:#000000'><font size=2px><b>:: SEGUIMIENTO DEL DOCUMENTO ::</b></font></div>");
                        $otable->addHtml("<tr>
                                                <td class=\"ColumnBlueTD\" width=\"2%\" rowspan=\"2\"><font class=\"ColumnWholeFont\"><center>Ord</center></font></td>
                                                <td class=\"ColumnBlueTD\" width=\"1%\" rowspan=\"2\"><font class=\"ColumnWholeFont\"><center>Dis</center></font></td>
                                                <td class=\"ColumnBlueTD\" width=\"30%\" colspan=\"3\"><font class=\"ColumnWholeFont\"><center>Procedencia</center></font></td>
                                                <td class=\"ColumnBlueTD\" width=\"30%\" colspan=\"3\"><font class=\"ColumnWholeFont\"><center>Destino</center></font></td>
                                                <td class=\"ColumnBlueTD\" width=\"30%\" rowspan=\"2\"><font class=\"ColumnWholeFont\"><center>Proveido</center></font></td>
                                                <td class=\"ColumnBlueTD\" width=\"7%\" rowspan=\"2\"><font class=\"ColumnWholeFont\"><center>Estado</center></font></td>
                                            </tr>
                                            <tr>
                                                <td class=\"ColumnBlueTD\" width=\"5%\"><font class=\"ColumnWholeFont\"><center>Fe.Registro</center></font></td>
                                                <td class=\"ColumnBlueTD\"><font class=\"ColumnWholeFont\"><center>Dependencia</center></font></td>
                                                <td class=\"ColumnBlueTD\"><font class=\"ColumnWholeFont\"><center>Usuario</center></font></td>
                                                <td class=\"ColumnBlueTD\" width=\"5%\"><font class=\"ColumnWholeFont\"><center>Fe.Recibe</center></font></td>
                                                <td class=\"ColumnBlueTD\"><font class=\"ColumnWholeFont\"><center>Dependencia</center></font></td>
                                                <td class=\"ColumnBlueTD\"><font class=\"ColumnWholeFont\"><center>Usuario</center></font></td>
                                            </tr>
                                          ");
                                                //estados: 1 derivado, 2 en proceso, 3 archivado

                        $sql=new despachoDerivacion_SQLlista();
                        $sql->wherePadreID($id);
                        $sql->orderUno();
                        $sql=$sql->getSQL();
                        //echo $sql;
                        $rs = new query($conn, strtoupper($sql));
                        $i=1;
                        while ($rs->getrow()) {
                            $dede_id=$rs->field('dede_id');
                            $otable->addData("<div id=\"$dede_id\" style=\"text-decoration: none; font-size:\"><a name=\"$dede_id\" id=\"$dede_id\"></a>$i</div>","C");

                            if($rs->field('dede_concopia'))
                               $otable->addData('Cc');
                            else
                                $otable->addData("&nbsp;");

                            $otable->addData(date("d/m/Y H:i:s",strtotime($rs->field('dede_fregistro'))));
                            
                            if($depeid==$rs->field('depe_idorigen'))
                                 ////subraya la dependencia
                                $otable->addData("<font style=\"{text-decoration: underline}\">".$rs->field('depe_nombrecorto_origen').'/'.$rs->field('depe_superior_nombre_origen')."</font>");
                            else
                                $otable->addData($rs->field('depe_nombrecorto_origen').'/'.$rs->field('depe_superior_nombre_origen'));

                            $otable->addData($rs->field('usuario_origen'));

                            //fecha en que se recibe el documento
                            if($rs->field('dede_fecharecibe'))
                                $otable->addData(date("d/m/Y H:i:s",strtotime($rs->field('dede_fecharecibe'))));
                            else
                                $otable->addData("&nbsp;");

                            if($depeid==$rs->field('depe_iddestino'))//subraya la dependencia
                                $otable->addData("<font style=\"{text-decoration: underline}\">".$rs->field('depe_nombrecorto_destino').'/'.$rs->field('depe_superior_nombre_destino')."</font>");
                            else
                                $otable->addData($rs->field('depe_nombrecorto_destino').'/'.$rs->field('depe_superior_nombre_destino'));

                            //usuario que recibe eldocumento
                            if($rs->field('usuario_recibe')){
                               $otable->addData($rs->field('usuario_recibe'));
                            }
                            else{
                                $otable->addData($rs->field('usuario_destino'));
                            }

                            $otable->addData($rs->field('dede_proveido'));


                            if($rs->field('dede_estado')==3){//recibido
                                $adjuntadoID=$rs->field('desp_adjuntadoid');
                                if($adjuntadoID){
                                    $link=addLink($adjuntadoID,"javascript:xajax_buscarVista(1,$adjuntadoID,$depeid,'DivDetallesNew');xajax_verDetalle(1,'$adjuntadoID','consulta','$depeid','',$permisodelPadre)","Click aqu&iacute; para consultar el registro","_self");
                                    $otable->addData('ADJUNTADO AL REG:'.$link,"C");
                                }
                                else{
                                    $otable->addData($rs->field('estado'),"C");
                                }
                                $otable->addRow('ATENDIDO');
                            }
                            elseif($rs->field('dede_estado')==4){//derivado
                                $dede_idderivado=$rs->field('dede_idderivado');
                                $link="<a title=\"pulse para Seguimiento\" class=\"link2\" href=\"#$dede_idderivado\" onClick=\"javascript:blink('$dede_idderivado');\">".$rs->field('estado')."</a>";
                                $otable->addData($link,"C");
                                $otable->addRow('RECIBIDO');
                            }
                            elseif($rs->field('dede_estado')==6){//Archivado
                                $otable->addData($rs->field('estado').'<BR>'.iif($rs->field('tipo_archivador'),'!=','','['.$rs->field('tipo_archivador').']',''),"C");
                                $otable->addRow('ANULADO');
                                $otable->addHtml("<tr onmouseout=\"MU(event,'TR')\" onmouseover=\"MO(event,'TR')\" id=\"ANULADO\" class=\"ANULADO\">
                                                  <td colspan=\"10\"><font class=\"DataFONT2\">".
                                                    'DIA-HORA: '.date("d/m/Y-H:i:s",strtotime($rs->field('dede_fechaarchiva'))).iif($rs->field('tipo_archivador'),'!=','',' | ARCHIV.'.$rs->field('tipo_archivador').': '.$rs->field('archivador'),'').' | RESPONSABLE: '.$rs->field('usuario_archiva')
                                                    ."</font></td></tr>");
                                $otable->addHtml("<tr onmouseout=\"MU(event,'TR')\" onmouseover=\"MO(event,'TR')\" id=\"ANULADO\" class=\"ANULADO\">
                                                  <td colspan=\"10\"><font class=\"DataFONT2\">".
                                                    'MOTIVO: '.$rs->field('dede_motivoarchiva')
                                                    ."</font></td></tr>");

                            }
                            elseif($rs->field('dede_estado')==7){//Activado
                                $adjuntadoID=$rs->field('desp_adjuntadoid');
                                if($adjuntadoID){
                                    $link=addLink($adjuntadoID,"javascript:xajax_buscarVista(1,$adjuntadoID,$depeid,'DivDetallesNew');xajax_verDetalle(1,'$adjuntadoID','consulta','$depeid','',$permisodelPadre)","Click aqu&iacute; para consultar el registro","_self");
                                    $otable->addData('ADJUNTADO AL REG:'.$link,"C");
                                }
                                else{
                                    $otable->addData($rs->field('estado'),"C");
                                }
                                $otable->addRow('ATENDIDO');
                                $otable->addHtml("<tr onmouseout=\"MU(event,'TR')\" onmouseover=\"MO(event,'TR')\" id=\"ATENDIDO\" class=\"ATENDIDO\">
                                                  <td colspan=\"10\"><font class=\"DataFONT\">".
                                                    'DIA-HORA: '.date("d/m/Y-H:i:s",strtotime($rs->field('dede_fechaactiva'))).iif($rs->field('tipo_archivador'),'!=','',' | DESDE ARCHIV.'.$rs->field('tipo_archivador').': '.$rs->field('archivador'),'').' | RESPONSABLE: '.$rs->field('usuario_activa')
                                                    ."</font></td></tr>");
                                $otable->addHtml("<tr onmouseout=\"MU(event,'TR')\" onmouseover=\"MO(event,'TR')\" id=\"ATENDIDO\" class=\"ATENDIDO\">
                                                  <td colspan=\"10\"><font class=\"DataFONT\">".
                                                    'MOTIVO: '.$rs->field('dede_motivoactiva')
                                                    ."</font></td></tr>");

                            }
                            else{
                                $otable->addData($rs->field('estado'),"C");
                                $otable->addRow();
                            }
                            $i++;
                        }

                    }
                    
                    $oForm->addHtml("<tr><td colspan=2>".$otable->writeHTML()."</td></tr>");
                    
                    
                    //CARGO LOS ARCHIVOS ADJUNTOS
                    if($verDocumento==1){
                        $sql=new despachoAdjuntados_SQLlista();
                        $sql->wherePadreID($id);
                        $sql = $sql->getSQL();

                        $rsFiles = new query($conn, $sql);
                        if($rsFiles->numrows()>0){
                            $tableAdjuntos = new Table("LISTA DE ARCHIVOS ADJUNTOS","100%",3); // Título, Largura, Quantidade de colunas
                            while ($rsFiles->getrow()) {
                                    $dead_id=$rsFiles->field("dead_id");
                                    $name_file=$rsFiles->field("area_adjunto");
                                    $descripcion=$rsFiles->field("dead_descripcion");
                                    
//                                    $enlace=PUBLICUPLOAD."gestdoc/".SIS_EMPRESA_RUC."/$periodo/$id/$file";
//
//                                    if(strpos(strtoupper($file),'.PDF')>0){
//                                        $link=addLink($file,"javascript:verFile('$enlace')","Click aqu&iacute; para Ver Documento","controle");
//                                    }else{
//                                        $link=addLink($file,"$enlace","Click aqu&iacute; para Descargar Archivo","controle");
//                                    }

                                    $nameFileFullPath = "../../../docs/gestdoc/".SIS_EMPRESA_RUC."/$periodo/$id/$name_file";
                                    //OJO la siguinete line se analiza en /sisadmin/gestdoc/controllers/procesar_data.php;
                                    if( file_exists($nameFileFullPath) && $name_file ){
                                        $link=addLink($name_file,"#","Click aqu&iacute; para Ver Documento","controle","link download-link2",$dead_id);
                                    
                                        $tableAdjuntos->addData($link);
                                        $pos=strpos($name_file,$descripcion);
                                        if( $pos===false ) {
                                            $tableAdjuntos->addData($descripcion);
                                        }else{
                                            $tableAdjuntos->addData("");
                                        }
                                        $tableAdjuntos->addRow();
                                    }
                                }
                            $oForm->addBreak("&nbsp;");
                            $oForm->addHtml("<tr><td colspan=2>".$tableAdjuntos->writeHTML()."</td></tr>");
                        }
                    }                    
                    
                    
    
                    $oForm->addHtml("<tr><td colspan=2>".listaHistorialEnvios(2,$id)."</td></tr>");
    
    
                }else{
                    $oForm->addBreak("<center>!NO SE ENCONTRARON DATOS CON EL NUMERO DE ".NAME_EXPEDIENTE_UPPER." '$id'...!!</center>",true);
                    
                }


                $contenido_respuesta=$oForm->writeHTML();

		if($op==1){//si es llamado para su funcionamiento en ajax con retornoa a un div
                        $objResponse = new xajaxResponse();
                        //$objResponse->addClear("DivDetalles",'innerHTML');
			$objResponse->addAssign("DivDetalles",'innerHTML', $contenido_respuesta);
                        
                        $objResponse->addScript("$('.download-link').on('click', function(e) {
                                                        e.preventDefault();
                                                        var $"."this = $(this);
                                                        var id = $"."this.data('id');
                                                        download(id);
                                                    });
                                                    $('.download-link2').on('click', function(e) {
                                                        e.preventDefault();
                                                        var $"."this = $(this);
                                                        var id = $"."this.data('id');
                                                        download2(id);
                                                    });");
                
			return $objResponse;
		}else
			return $contenido_respuesta;
        }

	function clonar(){
		global $conn,$param;
		$destinoInsert=$this->destinoInsert."?clear=1";
		
		/* captura y prepara la lista de registros a ser eliminados */ 
		$arLista_elimina = getParam("sel");
        	if (!is_array($arLista_elimina)) {
                    return;
                }
                
                $tabl_tipodespacho=getDbValue("SELECT tabl_tipodespacho FROM gestdoc.despachos  WHERE desp_id=".$arLista_elimina[0]);
                
                switch($tabl_tipodespacho){
                    case 140://institucional
                        $secuencia=getDbValue("SELECT 'gestdoc.corr_exp_'||b.tiex_secuencia||'_'||desp_anno::TEXT||'_'||depe_id::TEXT||'_'||a.tiex_id::TEXT
                                                    FROM gestdoc.despachos a
                                                    LEFT JOIN tipo_expediente b ON a.tiex_id=b.tiex_id
                                                    WHERE desp_id=".$arLista_elimina[0]);
                            
                        $numDocum=$conn->currval($secuencia);
                        if($numDocum==0){ /* Si la secuencia no está creada */
                            $conn->nextid($secuencia); /* Creo la secuencia */
                            $numDocum=1; /* Asigno el número 1 */
                        }
                        $desp_numero=$numDocum;
                    break;

                    case 141://personal
                        $secuencia=getDbValue("SELECT 'gestdoc.corr_exp_'||b.tiex_secuencia||'_'||desp_anno::TEXT||'_'||a.tiex_id::TEXT||'_'||a.usua_id::TEXT 
                                                    FROM gestdoc.despachos a
                                                    LEFT JOIN tipo_expediente b ON a.tiex_id=b.tiex_id
                                                    WHERE desp_id=".$arLista_elimina[0]);
                            
                        $numDocum=$conn->currval($secuencia);
                        if($numDocum==0){ /* Si la secuencia no está creada */
                            $conn->nextid($secuencia); /* Creo la secuencia */
                            $numDocum=1; /* Asigno el número 1 */
                        }
                        $desp_numero=$numDocum;
                        
                        break;

                    case 142://otras entidades
                        $secuencia='';
                        $desp_numero=getDbValue("SELECT desp_numero+1 
                                                    FROM gestdoc.despachos a
                                                    WHERE desp_id=".$arLista_elimina[0]);
                        break;
                }
                
                $hoy=date('d/m/Y');
                /* Sql a ejecutar */
		$sqlCommand ="INSERT INTO gestdoc.despachos 
                                   (desp_secuencia_automatica,
                                    proc_id,
                                    depe_id, 
                                    usua_id,
                                    tiex_id, 
                                    plde_id,
                                    desp_numero, 
                                    desp_siglas, 
                                    tabl_tipodespacho, 
                                    desp_fecha, 
                                    desp_asunto, 
                                    desp_firma, 
                                    desp_cargo, 
                                    tabl_modorecepcion, 
                                    desp_folios, 
                                    desp_proyectadopor, 
                                    desp_expediente, 
                                    desp_notas, 
                                    desp_exp_legal, 
                                    desp_demandante, 
                                    desp_demandado, 
                                    desp_resolucion, 
                                    prat_id,
                                    desp_modingreso,
                                    exle_id,
                                    desp_para,
                                    desp_ccopia,
                                    desp_contenido,
                                    desp_procesador,
                                    pdla_firma,
                                    desp_proyectado,
                                    desp_vb,
                                    desp_exterior,
                                    desp_vistos,
                                    depe_idproyecta,
                                    desp_especbreve,
                                    desp_referencia,
                                    desp_para_destino,
                                    desp_para_cargo,
                                    desp_para_dependencia,
                                    desp_para_depe_id,
                                    desp_set_derivados,
                                    desp_para_pdla_id
                                  ) 
                                SELECT  desp_secuencia_automatica,
                                        proc_id,
                                        depe_id,". 
                                        getSession("sis_userid").",
                                        tiex_id,
                                        plde_id,
                                        $desp_numero, 
                                        desp_siglas, 
                                        tabl_tipodespacho, 
                                        '$hoy'::DATE, 
                                        desp_asunto, 
                                        desp_firma, 
                                        desp_cargo, 
                                        tabl_modorecepcion, 
                                        desp_folios, 
                                        desp_proyectadopor,
                                        CASE WHEN desp_expediente_control IS NOT NULL THEN NULL /*SI desp_expediente_control TIENE VALOR, ENTONCES NO TIENE NUM_EXPEDIENTE */
                                             ELSE desp_expediente END, 
                                        desp_notas, 
                                        desp_exp_legal, 
                                        desp_demandante, 
                                        desp_demandado, 
                                        desp_resolucion, 
                                        prat_id,
                                        desp_modingreso,
                                        exle_id,
                                        desp_para,
                                        desp_ccopia,
                                        desp_contenido,
                                        desp_procesador,
                                        pdla_firma,
                                        desp_proyectado,
                                        desp_vb,
                                        desp_exterior,
                                        desp_vistos,
                                        depe_idproyecta,
                                        desp_especbreve,
                                        desp_referencia,
                                        desp_para_destino,
                                        desp_para_cargo,
                                        desp_para_dependencia,
                                        desp_para_depe_id,
                                        0,
                                        desp_para_pdla_id
                                        FROM gestdoc.despachos
                                        WHERE desp_id=".$arLista_elimina[0].
                                " RETURNING desp_id ";
                
		/* Ejecuto la sentencia */
                //alert($sqlCommand);
		$padre_id=$conn->execute($sqlCommand);
		$error=$conn->error();		
		if($error) alert($error);
		else{
                        if($secuencia){
                            $conn->setval($secuencia,intval($desp_numero)+1);
                        }
                        
                            /*PASO LAS DERIVACIONES*/
                            $sqlCommand="INSERT INTO gestdoc.despachos_derivaciones (depe_idorigen, 
                                                                        usua_idorigen, 
                                                                        desp_id, 
                                                                        depe_iddestino, 
                                                                        usua_iddestino, 
                                                                        dede_concopia, 
                                                                        dede_proveido, 
                                                                        usua_idcrea) 
                                                             SELECT depe_idorigen, 
                                                                        usua_idorigen, 
                                                                        $padre_id, 
                                                                        depe_iddestino, 
                                                                        usua_iddestino, 
                                                                        dede_concopia, 
                                                                        dede_proveido,". 
                                                                        getSession("sis_userid")."
                                                             FROM gestdoc.despachos_derivaciones
                                                                    WHERE desp_id=".$arLista_elimina[0];
                                    
//                            $conn->execute($sqlCommand);
//                            $error=$conn->error();		
//                            if($error) alert($error);
//                            else{
                                redirect($destinoInsert,"content");							
//                            }
		}
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

class despacho_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.*,LPAD(a.desp_numero::TEXT,6,'0')||'-'||a.desp_anno||COALESCE('-'||a.desp_siglas,'') AS num_documento,
					a.desp_id::TEXT as id,
                                        a.desp_trelacionado::TEXT as desp_trelacionado,
                                        CASE WHEN NOW()<a.desp_notificacion_hasta THEN 1 ELSE 0 END AS notificacion_activo,
                                        b.tiex_abreviado,
                                        b.tiex_exigir_marcar_documento_final,
                                        b.tiex_descripcion,
                                        c.tabl_descripcion AS modo_recepcion,
                                        d.tabl_descripcion AS tipo_despacho,
                                        e.prat_descripcion,
                                        f.depe_nombre,
                                        (SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(a.depe_id)) AS depe_superior_nombre,
                                        g.pedi_estado2,
                                        h.proc_plazo_dias,
                                        h.proc_nombre AS procedimiento,
                                        h.proc_validar,
                                        i.tabl_descripcion AS tipo_persona,
                                        CASE WHEN a.desp_tipo_denunciante=1 THEN 'ANONIMO'
                                             WHEN a.desp_tipo_denunciante=2 THEN 'CON RESERVA DE DATOS'
                                             WHEN a.desp_tipo_denunciante=3 THEN 'SIN RESERVA DE DATOS'
                                        END AS tipo_denunciante,
                                        CASE WHEN a.desp_tipo_denunciante IN (2,3) AND a.desp_denunciante_tipo_documento=1 THEN desp_denunciante_nombres||' '||desp_denunciante_apellido_paterno ||' '||desp_denunciante_apellido_materno
                                             WHEN a.desp_tipo_denunciante IN (2,3) AND a.desp_denunciante_tipo_documento=6 THEN desp_denunciante_razon_social
                                             ELSE NULL::TEXT
                                        END AS denunciante,
                                        CASE WHEN a.desp_denunciante_tipo_genero='M' THEN 'MASCULINO'
                                             WHEN a.desp_denunciante_tipo_genero='F' THEN 'FEMENINO'
                                        END AS denunciante_genero,
                                        

                                        CASE WHEN a.desp_denunciado_tipo_documento=1 THEN desp_denunciado_nombres||' '||desp_denunciado_apellido_paterno ||' '||desp_denunciado_apellido_materno
                                             WHEN a.desp_denunciado_tipo_documento IN (6,9) THEN desp_denunciado_razon_social
                                             ELSE NULL::TEXT
                                        END AS denunciado,
                                        CASE WHEN a.desp_denunciado_tipo_genero='M' THEN 'MASCULINO'
                                             WHEN a.desp_denunciado_tipo_genero='F' THEN 'FEMENINO'
                                        END AS denunciado_genero,
                                        ub0.ubig_descripcion AS ubigeo,
                                        ub1.ubig_descripcion AS ubigeo_denunciante,
                                        ub2.ubig_descripcion AS ubigeo_denunciado,
                                        ub3.ubig_descripcion AS ubigeo_hechos,
                                        p.plde_titulo,
					x.usua_login AS username,
                                        x.usua_login||'-'||xxx.pers_apellpaterno||' '||SUBSTRING(xxx.pers_nombres,1,CASE WHEN POSITION(' ' IN xxx.pers_nombres)>0 THEN POSITION(' ' IN xxx.pers_nombres) ELSE 100 END)  AS usuario_crea,
                                        x.usua_login||'/'||ff.depe_nombrecorto AS poyectado_por,
                                        y.usua_login AS usernameactual,
                                        y.usua_login||'-'||yyy.pers_apellpaterno||' '||SUBSTRING(yyy.pers_nombres,1,CASE WHEN POSITION(' ' IN yyy.pers_nombres)>0 THEN POSITION(' ' IN yyy.pers_nombres) ELSE 100 END)  AS usuario_modifica,
                                        NOW() AS ahora
				FROM gestdoc.despachos a
                                LEFT JOIN catalogos.tipo_expediente b ON a.tiex_id=b.tiex_id
                                LEFT JOIN catalogos.tabla c ON a.tabl_modorecepcion=c.tabl_id AND c.tabl_tipo='MODO_RECEPCION'
                                LEFT JOIN catalogos.tabla d ON a.tabl_tipodespacho=d.tabl_id AND d.tabl_tipo='TIPO_DESPACHO'
                                LEFT JOIN gestdoc.prioridad_atencion e ON a.prat_id=e.prat_id
                                LEFT JOIN catalogos.dependencia f ON a.depe_id=f.depe_id
                                LEFT JOIN solicitudes.pedidos_bbss g ON a.desp_id=g.desp_id
                                LEFT JOIN gestdoc.procedimiento h ON a.proc_id=h.proc_id
                                LEFT JOIN catalogos.tabla i ON a.tabl_tipopersona=i.tabl_codigo AND i.tabl_tipo='TIPO_PERSONA'
                                LEFT JOIN gestdoc.plantilla_despacho p ON a.plde_id=p.plde_id
                                
                                LEFT JOIN admin.usuario x  ON a.usua_id=x.usua_id
                                LEFT JOIN personal.persona_datos_laborales xx on  x.pdla_id=xx.pdla_id
                                LEFT JOIN personal.persona xxx on  xx.pers_id=xxx.pers_id                                
                                LEFT JOIN catalogos.dependencia ff ON a.depe_id_proyectado=ff.depe_id                                

                                LEFT JOIN admin.usuario y ON a.desp_actualusua=y.usua_id
                                LEFT JOIN personal.persona_datos_laborales yy on  y.pdla_id=yy.pdla_id
                                LEFT JOIN personal.persona yyy on  yy.pers_id=yyy.pers_id                                
                                
                                LEFT JOIN catalogos.ubigeo ub0 ON ub0.ubig_id=a.ubig_id
                                LEFT JOIN catalogos.ubigeo ub1 ON ub1.ubig_id=a.desp_denunciante_direccion_ubigeo
                                LEFT JOIN catalogos.ubigeo ub2 ON ub2.ubig_id=a.desp_denunciado_direccion_ubigeo
                                LEFT JOIN catalogos.ubigeo ub3 ON ub3.ubig_id=a.desp_hechos_direccion_ubigeo
				";
	
	}
	
	function whereID($id){
                if($id>intval($id)){
                    $this->addWhere("a.desp_id=$id");
                }
                else{
                    $this->addWhere("a.desp_expediente=$id");
                }
                    //$this->addWhere("a.desp_id=$id");
                
	}
        
        function whereIDRand($id_rand){
		$this->addWhere("a.desp_id_rand=$id_rand");
	}

        function notificacionEstado($estado){
		$this->addWhere("a.desp_notificacion_estado=$estado");
	}
        
        function whereTipoDespacho($tipo_despacho){
		$this->addWhere("a.tabl_tipodespacho=$tipo_despacho");
	}

        function whereFechaDesde($fecha){
		$this->addWhere("a.desp_fecha>='$fecha'");
	}

        function whereFechaHasta($fecha){
		$this->addWhere("a.desp_fecha<='$fecha'");
	}

        function whereNumero($numero){
		$this->addWhere("a.desp_numero=$numero");
	}

        function whereCodigo($desp_codigo){
                $this->addWhere("a.desp_codigo='$desp_codigo'");
        }
        
        function whereDepeID($depe_id){
		$this->addWhere("a.depe_id=$depe_id");
	}

        function whereTiExpID($tiex_id){
		$this->addWhere("a.tiex_id=$tiex_id");
	}
        
        function whereUsuaID($usua_id){
		$this->addWhere("a.usua_id=$usua_id");
	}

	function whereDescrip($descrip){
		if($descrip) $this->addWhere("a.desp_asunto ILIKE '%$descrip%'");
	}

        function whereDescripVarios($descrip){
		if($descrip) $this->addWhere("(a.desp_asunto ILIKE '%$descrip%' OR a.desp_firma ILIKE '%$descrip%' OR a.desp_entidad_origen ILIKE '%$descrip%')");
	}        
        
        function getSQL_periodos(){
		$sql="SELECT DISTINCT 
                                desp_anno,
                                desp_anno
				FROM (".$this->getSQL().") AS a 
                                ORDER BY 1 DESC ";
		return $sql;
	}      
        
	function orderUno(){
		$this->addOrder("a.desp_id_orden DESC");
	}
        
	function orderDos(){
		$this->addOrder("a.desp_idx DESC");
	}

	
}


class despacho2_SQLlista extends selectSQL {

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
                                    a.desp_acum_derivados,
                                    a.desp_acum_recibidos,
                                    a.desp_adjuntados_exp,
                                    a.depe_id,
                                    a.usua_id
				FROM gestdoc.despachos a
                                LEFT JOIN catalogos.tipo_expediente b ON a.tiex_id=b.tiex_id

				";
	
	}
	
	function whereID($id){
                if($id>intval($id)){
                    $this->addWhere("a.desp_id=$id");
                }
                else{
                    $this->addWhere("a.desp_expediente=$id");
                }
                    //$this->addWhere("a.desp_id=$id");
                
	}
        
        
        function whereDepeID($depe_id){
		$this->addWhere("a.depe_id=$depe_id");
	}

        function whereUsuaID($usua_id){
		$this->addWhere("a.usua_id=$usua_id");
	}
        
        function whereTiExpID($tiex_id){
		$this->addWhere("a.tiex_id=$tiex_id");
	}


	function whereDescrip($descrip){
		if($descrip) $this->addWhere("a.desp_asunto ILIKE '%$descrip%'");
	}
        
        
	function orderDos(){
		$this->addOrder("a.desp_idx DESC");
	}

	
}

class despachoBuscarAjax_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.desp_id,
                                    TO_CHAR(a.desp_fecha,'DD/MM/YYYY') AS desp_fecha,
                                    a.desp_codigo,
                                    b.tiex_abreviado||' '||LPAD(a.desp_numero::TEXT,6,'0')||'-'||a.desp_anno||COALESCE('-'||a.desp_siglas,'') AS num_documento,
                                    a.desp_asunto
				FROM gestdoc.despachos a
                                LEFT JOIN catalogos.tipo_expediente b ON a.tiex_id=b.tiex_id

				";
	
	}
	
        
        function whereTipoDespacho($tipo_despacho){
		$this->addWhere("a.tabl_tipodespacho=$tipo_despacho");
	}

        function whereFechaDesde($fecha){
		$this->addWhere("a.desp_fecha>='$fecha'");
	}

        function whereFechaHasta($fecha){
		$this->addWhere("a.desp_fecha<='$fecha'");
	}

        function whereNumero($numero){
		$this->addWhere("a.desp_numero='$numero'");
	}

        function whereCodigo($desp_codigo){
                $this->addWhere("a.desp_codigo='$desp_codigo'");
        }
        
        function whereTiExpID($tiex_id){
		$this->addWhere("a.tiex_id=$tiex_id");
	}
        
        function whereDescripVarios($descrip){
		if($descrip) $this->addWhere("(a.desp_asunto ILIKE '%$descrip%' OR a.desp_firma ILIKE '%$descrip%' OR a.desp_entidad_origen ILIKE '%$descrip%')");
	}        
        
        
	function orderUno(){
		$this->addOrder("a.desp_id_orden DESC");
	}
        
	
}

class despachoFirmas_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.defi_id,
                                    a.desp_id,
                                    a.defi_tipo,
                                    a.defi_estado,
                                    a.defi_especbreve,
                                    a.pdla_id,
                                    a.defi_posicion,
                                    a.defi_autoriza_rehacer,
                                    c.pers_id,
                                    c.pers_dni,
                                    c.pers_apellpaterno,
                                    c.pers_apellmaterno,
                                    c.pers_nombres,
                                    c.pers_apellpaterno||' '||c.pers_apellmaterno||' '||c.pers_nombres AS empleado,
                                    c.pers_email,
                                    a.defi_cargo AS cargo,
                                    e.depe_nombre AS dependencia,
                                    CASE WHEN a.defi_tipo IN (1,4) THEN 'FIRMA'
                                         ELSE 'VISTO'
                                    END AS tipo_firma,
                                    a.defi_hfirma,
                                    bb.desp_id_orden,
                                    f.usua_id,
                                    f.usua_login,
                                    NOW() as fecha_hora
				FROM gestdoc.despachos_firmas a
                                LEFT JOIN personal.persona_datos_laborales b ON a.pdla_id=b.pdla_id 
                                LEFT JOIN personal.persona c ON b.pers_id=c.pers_id
                                LEFT JOIN catalogos.dependencia e ON a.depe_id=e.depe_id
                                LEFT JOIN admin.usuario f ON a.pdla_id=f.pdla_id AND f.usua_id>1
                                LEFT JOIN gestdoc.despachos bb ON a.desp_id=bb.desp_id
				";
	
	}
        
        function wherePadreID($id){
		$this->addWhere("a.desp_id=$id");
	}
        
        function wherePadreIDVarios($id_varios){
		$this->addWhere("a.desp_id IN ($id_varios)");
	}
        
        function whereID($id){
		$this->addWhere("a.defi_id=$id");
	}
        
        function whereIDVarios($id_varios){
		$this->addWhere("a.defi_id IN ($id_varios)");
	}
        
        function wherePdlaID($pdla_id){
		$this->addWhere("a.pdla_id=$pdla_id");
	}

        function wherePersID($pers_id){
		$this->addWhere("b.pers_id=$pers_id");
	}
        
        function wherePdlaIDVarios($pdla_id){
		$this->addWhere("a.pdla_id IN  (SELECT pdla_id
            					FROM personal.persona_datos_laborales
					            WHERE  pers_id=(SELECT pers_id
                                				FROM personal.persona_datos_laborales
                                				WHERE  pdla_id=$pdla_id))");
        }
                                        
        function whereEstado($estado){
            $this->addWhere("a.defi_estado=$estado");
	}
        
	function orderUno(){
		$this->addOrder("a.desp_id,a.defi_tipo DESC,c.pers_apellpaterno,c.pers_apellmaterno,c.pers_nombres");
	}

        function orderDos(){
		$this->addOrder("bb.desp_id_orden DESC");
	}
	
}

class despachoDerivacion_SQLlista extends selectSQL {

	function __construct($op=1){
		$this->sql="SELECT  a.*,
                                        a.depe_idorigen::TEXT||'_'||a.usua_idorigen::TEXT AS id_origen,
                                        a.depe_iddestino::TEXT||'_'||a.usua_iddestino::TEXT AS id_destino,
                                        COALESCE(a.dede_estado,2) AS dede_estado,
                                        b.desp_id::TEXT as id_padre,
                                        b.depe_id,
                                        b.desp_fecha,
                                        b.desp_folios,
                                        b.desp_codigo,
                                        b.desp_firma,
                                        b.desp_cargo,
                                        b.desp_asunto,
                                        b.desp_email,
                                        b.usua_id,
                                        b.desp_fregistro,
                                        b.desp_exp_legal,
                                        b.desp_demandante,
                                        b.desp_demandado,
                                        b.desp_resolucion,
                                        b.desp_expediente,
                                        LPAD(b.desp_numero::TEXT,6,'0')||'-'||b.desp_anno||COALESCE('-'||b.desp_siglas,'') AS num_documento,
                                        c.depe_nombrecorto as depe_nombrecorto_origen,c.depe_nombre as depe_nombre_origen,
                                        (SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(a.depe_idorigen)) AS depe_superior_nombre_origen,
                                        d.depe_nombrecorto as depe_nombrecorto_destino,
                                        d.depe_nombre as depe_nombre_destino,
                                        (SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(a.depe_iddestino)) AS depe_superior_nombre_destino,
                                        CASE WHEN e.usua_mesa_partes_virtual=1 THEN b.desp_codigo||' '||b.desp_firma
                                             ELSE e.usua_login||'-'||eee.pers_apellpaterno||' '||SUBSTRING(eee.pers_nombres,1,CASE WHEN POSITION(' ' IN eee.pers_nombres)>0 THEN POSITION(' ' IN eee.pers_nombres) ELSE 100 END) 
                                        END AS usuario_origen,
                                        
                                        f.usua_login||'-'||fff.pers_apellpaterno||' '||SUBSTRING(fff.pers_nombres,1,CASE WHEN POSITION(' ' IN fff.pers_nombres)>0 THEN POSITION(' ' IN fff.pers_nombres) ELSE 100 END) AS usuario_destino,
                                        g.usua_login AS login_recibe,
                                        g.usua_login||'-'||ggg.pers_apellpaterno||' '||SUBSTRING(ggg.pers_nombres,1,CASE WHEN POSITION(' ' IN ggg.pers_nombres)>0 THEN POSITION(' ' IN ggg.pers_nombres) ELSE 100 END) AS usuario_recibe,
                                        h.usua_login||'-'||hhh.pers_apellpaterno||' '||SUBSTRING(hhh.pers_nombres,1,CASE WHEN POSITION(' ' IN hhh.pers_nombres)>0 THEN POSITION(' ' IN hhh.pers_nombres) ELSE 100 END) AS usuario_archiva,
                                        i.arch_anno::TEXT||'-'||i.arch_descripcion AS archivador,
                                        j.tabl_descripcion AS tipo_archivador,
                                        k.usua_login||'-'||kkk.pers_apellpaterno||' '||SUBSTRING(kkk.pers_nombres,1,CASE WHEN POSITION(' ' IN kkk.pers_nombres)>0 THEN POSITION(' ' IN kkk.pers_nombres) ELSE 100 END)  AS usuario_del_archivador,
                                        l.usua_login||'-'||lll.pers_apellpaterno||' '||SUBSTRING(lll.pers_nombres,1,CASE WHEN POSITION(' ' IN lll.pers_nombres)>0 THEN POSITION(' ' IN lll.pers_nombres) ELSE 100 END)  AS usuario_activa,
                                        m.tabl_descripcion AS estado,
                                        n.tiex_abreviado,
                                        o.depe_nombre as depe_nombre_genera,
                                        (SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(b.depe_id)) AS depe_superior_nombre_genera,
                                        p.usua_login  as usuario_genera,
                                        q.pedi_estado2,
                                        x.usua_login as username,
                                        CASE WHEN a.dede_estado = 3 OR a.dede_estado = 7 THEN
                                                            NOW()::date - a.dede_fecharecibe::date
                                             ELSE 0 END AS dias_en_proceso
                                        
                                        ";
                 switch($op){
                     case 1:
                         $this->sql.="  FROM gestdoc.despachos_derivaciones a
                                        LEFT JOIN gestdoc.despachos b ON a.desp_id=b.desp_id ";
                         break;
                     
                     case 2:
                         $this->sql.="  FROM gestdoc.despachos b
                                        LEFT JOIN gestdoc.despachos_derivaciones a ON a.desp_id=b.desp_id ";                         
                         break;
                 }

                $this->sql.="   LEFT JOIN catalogos.dependencia c ON a.depe_idorigen=c.depe_id
                                LEFT JOIN catalogos.dependencia d ON a.depe_iddestino=d.depe_id
                                
                                LEFT JOIN admin.usuario e     ON a.usua_idorigen=e.usua_id
                                LEFT JOIN personal.persona_datos_laborales ee on  e.pdla_id=ee.pdla_id
                                LEFT JOIN personal.persona eee on  ee.pers_id=eee.pers_id                                

                                LEFT JOIN admin.usuario f     ON a.usua_iddestino=f.usua_id
                                LEFT JOIN personal.persona_datos_laborales ff on  f.pdla_id=ff.pdla_id
                                LEFT JOIN personal.persona fff on  ff.pers_id=fff.pers_id                                
                                
                                LEFT JOIN admin.usuario g     ON a.usua_idrecibe=g.usua_id
                                LEFT JOIN personal.persona_datos_laborales gg on  g.pdla_id=gg.pdla_id
                                LEFT JOIN personal.persona ggg on  gg.pers_id=ggg.pers_id                                

                                LEFT JOIN admin.usuario h     ON a.usua_idarchiva=h.usua_id
                                LEFT JOIN personal.persona_datos_laborales hh on  h.pdla_id=hh.pdla_id
                                LEFT JOIN personal.persona hhh on  hh.pers_id=hhh.pers_id                                
                                
                                LEFT JOIN catalogos.archivador i  ON a.arch_id=i.arch_id
                                LEFT JOIN catalogos.tabla j       ON j.tabl_tipo='TIPO_ARCHIVADOR' AND i.arch_tabltipoarchivador=j.tabl_id
                                
                                LEFT JOIN admin.usuario k     ON i.usua_id=k.usua_id
                                LEFT JOIN personal.persona_datos_laborales kk on  k.pdla_id=kk.pdla_id
                                LEFT JOIN personal.persona kkk on  kk.pers_id=kkk.pers_id                                


                                LEFT JOIN admin.usuario l     ON a.usua_idactiva=l.usua_id
                                LEFT JOIN personal.persona_datos_laborales ll on  l.pdla_id=ll.pdla_id
                                LEFT JOIN personal.persona lll on  ll.pers_id=lll.pers_id                                


                                LEFT JOIN catalogos.tabla m       ON m.tabl_tipo='ESTADO_DESPACHO' AND COALESCE(a.dede_estado,2)=m.tabl_codigo
                                LEFT JOIN catalogos.tipo_expediente n ON b.tiex_id=n.tiex_id
                                LEFT JOIN catalogos.dependencia o ON b.depe_id=o.depe_id
                                LEFT JOIN admin.usuario p     ON b.usua_id=p.usua_id
                                LEFT JOIN solicitudes.pedidos_bbss q ON a.desp_id=q.desp_id
                                
				LEFT JOIN admin.usuario x  ON a.usua_idcrea=x.usua_id
				";
	
	}
	function whereID($id){
		$this->addWhere("a.dede_id=$id");
	}

	function wherePadreID($id){
            //si se ha enviado con secuencia de expediente, es decir en decimal
            if($id>intval($id)){
                $this->addWhere("b.desp_id=$id");
            }else{
                $this->addWhere("b.desp_expediente=$id");
            }
	}

        function wherePadreUsuaID($usua_id){
		$this->addWhere("b.usua_id=$usua_id");
	}

        function whereTDespacho($tabl_tipodespacho){
                $this->addWhere("b.tabl_tipodespacho=$tabl_tipodespacho");
        }
        
        function whereTExpediente($tiex_id){
                $this->addWhere("b.tiex_id=$tiex_id");
        }

        function wherePrioridadAtencion($prat_id){
                $this->addWhere("b.prat_id=$prat_id");
        }

        function whereProcedimiento($proc_id){
                $this->addWhere("b.proc_id=$proc_id");
        }

        function whereCodigo($desp_codigo){
                $this->addWhere("b.desp_codigo='$desp_codigo'");
        }
        
        function whereEstado($estado){
		$this->addWhere("COALESCE(a.dede_estado,2)=$estado");
	}

        function whereRegistrados(){
		$this->addWhere("COALESCE(a.dede_donde_se_creo,0)=0");
	}

        function whereDocLegal(){
		$this->addWhere("b.desp_legal=1");
	}

        function whereRecibidos(){
		$this->addWhere("a.usua_idrecibe>0");
	}

        function whereEnProceso(){
		$this->addWhere("(a.dede_estado=3 OR a.dede_estado=7)");
	}

        function whereNOAdjuntados(){
		$this->addWhere("a.desp_adjuntadoid IS NULL");
	}        
        
        function whereUsuaIDCrea($usua_id){
		$this->addWhere("a.usua_idcrea=$usua_id");
	}

        function whereDepePadre($depe_id){
		$this->addWhere("b.depe_id=$depe_id");
	}

        function whereDepeOrigen($depe_id){
		$this->addWhere("a.depe_idorigen=$depe_id");
	}

        function whereDepeDestino($depe_id){
		$this->addWhere("a.depe_iddestino=$depe_id");
	}

        function whereDepeOrig_Destino($depe_id){
		$this->addWhere("(a.depe_idorigen=$depe_id or a.depe_iddestino=$depe_id or b.depe_id=$depe_id)");
	}

        function whereUsuaOrigen($usua_id){
		$this->addWhere("a.usua_idorigen=$usua_id");
	}

        function whereUsuaDestino($usua_id){
		$this->addWhere("a.usua_iddestino=$usua_id");
	}

        function whereUsuaRecibe($usua_id){
		$this->addWhere("a.usua_idrecibe=$usua_id");
	}

        function whereUsuaArchiva($usua_id){
		$this->addWhere("a.usua_idarchiva=$usua_id");
	}

        function whereArchivador($arch_id){
		$this->addWhere("a.arch_id=$arch_id");
	}

        function whereUsuaOrig_Destino($usua_id){
		$this->addWhere("(a.usua_idorigen=$usua_id or a.usua_iddestino=$usua_id or a.usua_idarchiva=$usua_id or a.usua_idrecibe=$usua_id or b.usua_id=$usua_id)");
	}

        function whereFechaDesde($fecha){
		$this->addWhere("b.desp_fecha>='$fecha'");
	}

        function whereFechaHasta($fecha){
		$this->addWhere("b.desp_fecha<='$fecha'");
	}

        function whereNumero($numero){
		$this->addWhere("b.desp_numero=$numero");
	}
        
        function whereFechaRecibeDesde($fecha){
		$this->addWhere("TO_CHAR(a.dede_fecharecibe,'DD/MM/YYYY')::date>='$fecha'");
	}

        function whereFechaRecibeHasta($fecha){
		$this->addWhere("TO_CHAR(a.dede_fecharecibe,'DD/MM/YYYY')::date<='$fecha'");
	}

        function whereFechaRegistro($fecha){
		$this->addWhere("TO_CHAR(a.dede_fregistro,'DD/MM/YYYY')::date='$fecha'");
	}
        
        function whereHoraDesde($hora){
		$this->addWhere("TO_CHAR(a.dede_fregistro,'HH24:MI')::TIME>='$hora'::TIME");
	}

        function whereHoraHasta($hora){
		$this->addWhere("TO_CHAR(a.dede_fregistro,'HH24:MI')::TIME<='$hora'::TIME");
	}
        
        function whereFechaRegistroDesde($fecha){
		$this->addWhere("TO_CHAR(b.desp_fregistro,'DD/MM/YYYY')::date>='$fecha'");
	}

        function whereFechaRegistroHasta($fecha){
		$this->addWhere("TO_CHAR(b.desp_fregistro,'DD/MM/YYYY')::date<='$fecha'");
	}

        function whereFechaArchivaDesde($fecha){
		$this->addWhere("TO_CHAR(a.dede_fechaarchiva,'DD/MM/YYYY')::date>='$fecha'");
	}

        function whereFechaArchivaHasta($fecha){
		$this->addWhere("TO_CHAR(a.dede_fechaarchiva,'DD/MM/YYYY')::date<='$fecha'");
	}

        function whereDescrip($descrip){
		if($descrip) $this->addWhere("b.desp_asunto ILIKE '%$descrip%'");
	}

	function whereDescripVarios($descrip){
		if($descrip) $this->addWhere("(b.desp_asunto ILIKE '%$descrip%' or b.desp_firma ILIKE '%$descrip%' or b.desp_entidad_origen ILIKE '%$descrip%')");
	}

        function whereDespachoDesde($desp_id){
		$this->addWhere("b.desp_id>=$desp_id");
	}

        function whereDespachoHasta($desp_id){
		$this->addWhere("b.desp_id<=$desp_id");
	}

        function whereMayDiasenProceso($dias){
		$this->addWhere("CASE WHEN a.dede_estado = 3 OR a.dede_estado = 7 THEN 
                                            NOW()::date - a.dede_fecharecibe::date
                                     ELSE 0  END>=$dias");
	}

        function whereAdjuntados($id){
		$this->addWhere("a.desp_adjuntadoid=$id");
	}
        
        
        function wherePendienteRecibido(){
		$this->addWhere("a.usua_idrecibe IS NULL");
	}
        
        function wherePendienteRecibido2(){
		$this->addWhere("a.dede_estado=2");
	}
        
        function orderUno(){
		$this->addOrder("a.dede_id");
	}

	function orderDos(){
		$this->addOrder("b.desp_fecha DESC,a.depe_iddestino,a.usua_idrecibe,b.desp_id_orden,a.dede_id");
	}

        function orderDos2(){
		$this->addOrder("a.depe_iddestino,a.usua_idrecibe,a.dede_fecharecibe DESC,a.dede_id");
	}
        
	function orderTres(){
		$this->addOrder("b.depe_id,b.usua_id,b.desp_id_orden,a.dede_id");
	}
        
	function orderCuatro(){
		$this->addOrder("a.depe_iddestino,a.arch_id,a.usua_idarchiva,b.desp_id_orden,a.dede_id");
	}

	function orderCinco(){
		$this->addOrder("dias_en_proceso,a.depe_iddestino,a.usua_idrecibe,b.desp_id_orden,a.dede_id");
	}

	function orderSeis(){
		$this->addOrder("a.depe_iddestino,a.usua_idrecibe,b.desp_fecha DESC,b.desp_id_orden,a.dede_id");
	}
        
        function orderSiete(){
		$this->addOrder("a.depe_idorigen,a.usua_idorigen,a.depe_iddestino,a.usua_iddestino,b.desp_id_orden,a.dede_id");
	}        
}


function conPermisoVerDocumento($id,$usua_id,$usua_idFirma,$permisodelPadre,$desp_set_derivados,$tabl_tipodespacho){
    //DETERMINO SI EL USUARIO TENDRA ACCESO AL DOCUMENTO COMPLETO
    $verDocumento=0;
    if(getSession("sis_userid")>0 && getSession("sis_level")>1){
         global $conn;
        //si es el usuario creador o firmante
        if($usua_id==getSession("sis_userid") 
                || $usua_idFirma==getSession("sis_userid") 
                || getSession("sis_userid")==1 //ADMIN
                || ((getSession("sis_level")==3 //SUPERVISOR
                    || $permisodelPadre==1)
                    && ($desp_set_derivados==1 || $tabl_tipodespacho==142 /*externo*/))
                ){ //SI HEREDA LOS PERMISOS DEL REGISTRO PADRE, PARA EL CASO DE LOS ADJUNTOS
            $verDocumento=1;
        }else{
            /*BUSCO EN LOS FIRMANTES*/
            $rsFirmas=new despachoFirmas_SQLlista();
            $rsFirmas->wherePadreID($id);
            //$rsFirmas->whereEstado(1);
            $rsFirmas->wherePdlaIDVarios(getSession("sis_pdlaid"));
            $rsFirmas->setDatos();
            if($rsFirmas->existeDatos()){
                $verDocumento=1;
            }else{
                /*BUSCO SI HA SIDO DERIVADO AL USUARIO QUE HACE LA CONSULTA*/
                $rsDerivaciones=new despachoDerivacion_SQLlista();
                $rsDerivaciones->wherePadreID($id);
                $rsDerivaciones->whereUsuaDestino(getSession("sis_userid"));
                $rsDerivaciones->setDatos();
                if($rsDerivaciones->field("usua_iddestino")==getSession("sis_userid")){
                    $verDocumento=1;
                }
                else{/*BUSCO SI HA SIDO RECIBIDO POR EL USUARIO QUE HACE LA CONSULTA*/
                    $rsDerivaciones=new despachoDerivacion_SQLlista();
                    $rsDerivaciones->wherePadreID($id);
                    $rsDerivaciones->whereUsuaRecibe(getSession("sis_userid"));
                    $rsDerivaciones->setDatos();
                    if($rsDerivaciones->field("usua_idrecibe")==getSession("sis_userid")){
                        $verDocumento=1;
                    }else{   

                        /*BUSCO EN LAS DERIVACIONES A NIVEL DE DEPENDENCIA*/
                        /*recorro todas las dependencias que tiene acceso el usuaio actual*/
                        $sql="SELECT DISTINCT a.depe_id
                                                  FROM personal.persona_datos_laborales a
                                                  LEFT JOIN catalogos.dependencia b on  a.depe_id=b.depe_id
                                                  WHERE a.pdla_estado=1 AND a.pers_id=".getSession("sis_persid");

                        $rsTodasDependencias = new query($conn, $sql);
                        while ($rsTodasDependencias->getrow()){
                            $rsDerivaciones=new despachoDerivacion_SQLlista();
                            $rsDerivaciones->wherePadreID($id);
                            $rsDerivaciones-> whereDepeDestino($rsTodasDependencias->field('depe_id'));
                            $rsDerivaciones->setDatos();
                            //OBTENGO EL JEFE DE LA OFICINA
                            $pdla_jefe=getDbValue("SELECT pdla_id FROM catalogos.dependencia WHERE depe_id=".$rsTodasDependencias->field('depe_id'));

                            //SI ES UNA OFICINA A LA CUAL TIENE ACCESO EL USUARIO QUE COSULTA
                            if($rsDerivaciones->field("depe_iddestino")==$rsTodasDependencias->field('depe_id')){

                                //1->SI EL QUE ESTA CONSULTANDO ES EL JEFE DE LA OFICINA QUE SE ESTA CONSULTANDO
                                //2 O TIENE ACCESO A MIRAR "TODOS" LOS DOCUMENTOS QUE LLEGAN A SU OICINA (SECETARIA)                        

                                if($pdla_jefe==getSession("sis_pdlaid") //SI ES JEFE DE OFICINA
                                        || getSession("SET_TODOS_USUARIOS")==1 //SECRETARIA
                                        ){

                                    $verDocumento=1;
                                    break;

                                }

                            }                    
                        }
                    }
                }
            }
        }
    }
    //FIN DETERMINO 
    return($verDocumento);
}                    

function seguir($id)
{
    global $conn;
    $objResponse = new xajaxResponse();
    $usua_id=getSession("sis_userid");
            
    $desp_id=getDbValue("SELECT desp_id FROM gestdoc.despachos_seguimiento WHERE desp_id=$id");
    
    if ($desp_id==""){
        $sql = "INSERT INTO gestdoc.despachos_seguimiento ( desp_id,
                                                            usua_id)
                                    VALUES ($id,
                                            $usua_id) ";


        $padre_id=$conn->execute($sql);
        $error=$conn->error();
        if($error){
            $conn->rollback();
            $objResponse->addAlert($error);
            return $objResponse;
        }else{
            $objResponse->addAlert("Seguimiento de $id realizado...");
        }
    }else{
            $objResponse->addAlert("Seguimiento de $id ya existe!");
    }

    return $objResponse;
}

function btnRegistrados($desp_id, $bd_desp_expediente, $acum_derivados){
    $botones="<div class=\"dropdown\">
                <button class=\"btn btn-default dropdown-toggle btn-sm\" type=\"button\" id=\"dropdownMenu1\" data-toggle=\"dropdown\"><span class=\"glyphicon glyphicon-cog\" aria-hidden=\"true\"></span> Opciones
                <span class=\"caret\"></span></button>
                <ul class=\"dropdown-menu dropdown-menu-right\" aria-labelledby=\"dropdownMenu1\">";

    $botones.="<li><a href=\"javascript:jClonar('$desp_id')\" target=\"content\">Clonar</a></li>";        
    
    if($acum_derivados>0){
        $botones.="<li><a href=\"javascript:AbreVentana('rptHT.php?id=$desp_id')\" target=\"content\">Imprimir Hoja de Tr&aacute;mite</a></li>";    
    }
    
    $botones.="<li><a href=\"#\" onClick=\"javascript:beforeEnviaEmail('$desp_id','$bd_desp_expediente')\" target=\"controle\">Enviar Correo Electrónico</a></li>";
    $botones.="<li><a href=\"#\" onClick=\"javascript:xajax_seguir('$desp_id')\" target=\"controle\">Seguir</a></li>";
    
    
    $botones.="</ul>
              </div>";
    return($botones);    
}

if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
            include($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");
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

            $dml=new despacho();

            switch($control){
                    case 1: // Guardar
                            $dml->guardar();
                            break;
                    case 2: // Eliminar
                            $dml->eliminar();
                            break;
                    case 3: // Clonar
                            $dml->clonar();
                            break;
                        
            }
            //	cierra la conexión con la BD
            $conn->close();
    }
}