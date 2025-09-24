<?php
//OJO con los subgrupos 24,25,26,27, estos estan reservados por el sistema
require_once($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/clases/entidad.php");

class servicios extends entidad {
	
	function __construct($id='',$title=''){
		$this->setTable='servicio'; //nombre de la tabla
		$this->setKey='serv_codigo'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	
                
		/* Destinos luego de actulizar o agregar un registro */
		$this->destinoUpdate = "catalogosServicios_edicion.php";
		$this->destinoInsert = "catalogosServicios_edicion.php";
		$this->destinoDelete = "catalogosServicios_buscar.php";
                

	}
	
	function addField(&$sql){
            
                if ($_POST["hx_serv_estado"]){
                    $sql->addField("serv_estado", 'true', "String");
                }else{
                    $sql->addField("serv_estado", 'false', "String");
                }

                if ($_POST["hx_serv_aplica_ajuste"]){
                    $sql->addField("serv_aplica_ajuste", 1, "Number");
                }else{
                    $sql->addField("serv_aplica_ajuste", 0, "Number");
                }
                

                if ($_POST["hx_serv_pensionable"]){
                    $sql->addField("serv_pensionable", 1, "Number");                   
                }else{
                    $sql->addField("serv_pensionable", 0, "Number");                                       
                }                
                
                if ($_POST["hx_serv_essalud"]){
                    $sql->addField("serv_essalud", 1, "Number");                   
                }else{
                    $sql->addField("serv_essalud", 0, "Number");                                       
                }
                

                if ($_POST["hx_serv_sctr"]){
                    $sql->addField("serv_sctr", 1, "Number");                   
                }else{
                    $sql->addField("serv_sctr", 0, "Number");                                       
                }                                
                
                if ($_POST["hx_serv_conafovicer"]){
                    $sql->addField("serv_conafovicer", 1, "Number");                   
                }else{
                    $sql->addField("serv_conafovicer", 0, "Number");                                       
                }
                
                if ($_POST["hx_serv_ir5ta"]){
                    $sql->addField("serv_ir5ta", 1, "Number");                   
                }else{
                    $sql->addField("serv_ir5ta", 0, "Number");                                       
                }                
                
                if ($_POST["hx_serv_cts"]){
                    $sql->addField("serv_cts", 1, "Number");                   
                }else{
                    $sql->addField("serv_cts", 0, "Number");                                       
                }                
                
                if ($_POST["hx_serv_automatico"]){
                    $sql->addField("serv_automatico", 1, "Number");                   
                }else{
                    $sql->addField("serv_automatico", 0, "Number");                                       
                }   
                
                if ($_POST["hx_serv_editable"]){
                    $sql->addField("serv_editable", 1, "Number");                   
                }else{
                    $sql->addField("serv_editable", 0, "Number");                                       
                }
                
                if ($_POST["hx_serv_genera_contabilidad"]){
                    $sql->addField("serv_genera_contabilidad", 1, "Number");                   
                }else{
                    $sql->addField("serv_genera_contabilidad", 0, "Number");                                       
                }
                
                if ($_POST["hx_serv_gratuito"]){
                    $sql->addField("serv_gratuito", 1, "Number");                   
                }else{
                    $sql->addField("serv_gratuito", 0, "Number");                                       
                }
                
//                if ($_POST["hx_serv_muestra_min"]){
//                    $sql->addField("serv_muestra_min", 1, "Number");
//                }else{
//                    $sql->addField("serv_muestra_min", 0, "Number");
//                }
                /* ADICION DATOS DE AUDITORIA */
                $sql->addField("serv_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
                $sql->addField("serv_actualusua", getSession("sis_userid"), "String");	            
	}

        
	function getSQL(){
                $sql=new servicios_SQLlista();
                $sql->whereID($this->id);
		return($sql->getSQL());
	}
        
        function buscar($op,$cadena='',$bd_depeid,$ruc_id='',$grupo='',$sgrupo='',$tipo_igv,$sin_asientos,$sin_componente,$sin_tipo_asiento,$fase_asiento,$serv_activo,$colSearch='',$tipo,$colOrden=1,$busEmpty=0,$numForm=0, $pg=1,$Nameobj='')
        {
                global $conn;

                $objResponse = new xajaxResponse();

                $cadena=trim(strtoupper($cadena));
                

                if(strlen($cadena)>0 || $busEmpty==1 || $grupo ){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
                        $sql = new servicios_SQLlista();
                        $sql->whereAcceso();

                        if($bd_depeid){
                            $sql->whereDepeID(intval($bd_depeid));
                        }
                       
                        if($serv_activo==1){
                            $sql->whereActivo();
                        }elseif($serv_activo==9){
                            $sql->whereNOActivo();
                        }
//                        else{
//                            $sql->whereDepeID(getSession("sis_depe_superior"));
//                        }
//                        OJO. LA BUSQUEA POR DESCRIPCION FUNCIONA MAL
//                        else{
//                            $sql->whereDepeTodos2(getSession("sis_depe_superior"));
//                        }
                        if($tipo) $sql->whereTipo($tipo);
                        if($ruc_id) $sql->whereRUCID($ruc_id);
                        if($grupo) $sql->whereGrupoID($grupo);
                        if($sgrupo) $sql->whereSubGrupoID($sgrupo);
                        if($tipo_igv) $sql->whereTipoIGV($tipo_igv);
                        if($sin_asientos==1 || $sin_asientos=='true') {$sql->whereSinAsiento();}
                        if($sin_componente==1 || $sin_componente=='true') {$sql->whereSinComponente();}

                        if($sin_tipo_asiento!='') {
                            $in_out=explode("_",$sin_tipo_asiento);
                            if($in_out[1]==1){//INCLUIDO
                                $sql->whereConTipoAsiento($in_out[0]);    
                            }else{//NO INCLUIDO
                                $sql->whereSinTipoAsiento($in_out[0]);    
                            }
                            
                        }
                        
                        if($fase_asiento!='') {
                            $in_out=explode("_",$fase_asiento);
                            if($in_out[1]==1){//INCLUIDO
                                $sql->whereConFaseAsiento($in_out[0]);    
                            }else{//NO INCLUIDO
                                $sql->whereSinFaseAsiento($in_out[0]);    
                            }
                            
                        }
                        
                        //se analiza la columna de busqueda
                        if($cadena){

                            switch($colSearch){
                                case 'serv_id': // si se recibe el campo id
                                        $sql->whereID($cadena);
                                        break;

                                default:// si no se recibe ningun campo de busqueda
                                    if($cadena){
                                        if(ctype_digit($cadena)){ //si la cadena recibida son todos digitos
                                            $sql->whereID($cadena);
                                        }else{
                                            $sql->whereDescrip($cadena);
                                        }
                                    }            
                                    break;
                                }
                        }
                        $sql->orderDos();

                        $sql= $sql->getSQL();
                        
                        //guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
                        if ($op==1 && getParam("clear")!=2)
                                setSession("cadSearch",$cadena);

                                $rs = new query($conn, strtoupper($sql),$pg,40);
                                //$otable = new  Table("$sql","100%",10);
                                $otable = new  Table("","100%",22,true,'tLista');

                                
                                $button = new Button;
                                $pg_ant = $pg-1;
                                $pg_prox = $pg+1;
                                if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,'$cadena','$bd_depeid','$ruc_id','$grupo','$sgrupo','$tipo_igv','$sin_asientos','$sin_componente','$sin_tipo_asiento','$fase_asiento',$serv_activo,'$colSearch','$tipo','$colOrden','$busEmpty','$numForm','$pg_ant','$Nameobj')","content");
                                if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,'$cadena','$bd_depeid','$ruc_id','$grupo','$sgrupo','$tipo_igv','$sin_asientos','$sin_componente','$sin_tipo_asiento','$fase_asiento',$serv_activo,'$colSearch','$tipo','$colOrden','$busEmpty','$numForm','$pg_prox','$Nameobj')","content");
                                
                                if ($rs->numrows()>0) {
                                                if (getParam("clear")==2){}
                                                else{
                                                    $otable->addColumnHeader("<input type=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">"); // Coluna com checkbox
                                                }
                                                $otable->addColumnHeader("C&oacute;d",false,"3%", "L"); 
                                                $otable->addColumnHeader("Grupo",false,"5%", "L"); 
                                                $otable->addColumnHeader("SGrp",false,"5%", "L","","Sub Grupo"); 
                                                
                                                if (SIS_EMPRESA_TIPO==4){//Empresa tipo Almacen
                                                    $otable->addColumnHeader("Descripci&oacute;n",false,"63%", "L"); 
                                                    $otable->addColumnHeader("Presen",false,"10%", "L","","Presentaci&oacute;n");
                                                    $otable->addColumnHeader("U.Medida",false,"10%", "L");
                                                }else{
                                                    if(inlist(SIS_EMPRESA_TIPO,'2,3')){//empresas publicas, beneficencias
                                                        $otable->addColumnHeader("Descripci&oacute;n",false,"35%", "L"); 
                                                    }else{
                                                        $otable->addColumnHeader("Descripci&oacute;n",false,"46%", "L"); 
                                                    }
                                                    $otable->addColumnHeader("Presen",false,"5%", "L","","Presentaci&oacute;n");
                                                    $otable->addColumnHeader("U.Med",false,"5%", "L");
                                                    $otable->addColumnHeader("Referencia",false,"13%", "L");
                                                    $otable->addColumnHeader("T/IGV",false,"1%", "L");
                                                    $otable->addColumnHeader("Pre/Imp",false,"5%", "C"); 
                                                    $otable->addColumnHeader("Frac",false,"4%", "C"); 
                                                    $otable->addColumnHeader("RUC",false,"5%", "C");
                                                }
                                                
                                                $otable->addColumnHeader("C.Aux",false,"3%", "C");
                                                
                                                if(inlist(SIS_EMPRESA_TIPO,'2,3')){//empresas publicas, beneficencias
                                                    $otable->addColumnHeader("Comp.pptal",true,"5%", "C");                                                    
                                                    $otable->addColumnHeader("Espec&iacute;fica",true,"5%", "C"); 
                                                    if(SIS_EMPRESA_TIPO==3 ){//beneficencias
                                                        if(SIS_CIUDAD=='CHICLAYO'){
                                                            $otable->addColumnHeader("SIST",true,"1%", "L"); 
                                                        }
                                                    }
                                                    $otable->addColumnHeader("Est",true,"1%", "C");
                                                }else{
                                                    $otable->addColumnHeader("Est",true,"1%", "C");
                                                }
                                                
                                                $otable->addRow(); // adiciona la linea (TR)
                                                while ($rs->getrow()) {
                                                        $id = $rs->field("serv_codigo"); // captura la clave primaria del recordsource
                                                        $serv_id=$rs->field("serv_id");
                                                        // fin de definicion

                                                        $campoTexto_de_Retorno = especialChar($rs->field("serv_descripcion"));

                                                        //si la llamada no es desde la busqueda avanzada (AvanzLookup)
                                                        if (getParam("clear")==2){
                                                            $otable->addData(addLink($id,"javascript:update('$id','$campoTexto_de_Retorno',$numForm)","Click aqu&iacute; para seleccionar el registro"));
                                                        }
                                                        else{
                                                            $otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");
                                                        }
                                                        $page="catalogosServicios_edicion.php?id=$id&clear=1&busEmpty=$busEmpty&nBusc_grupo_id=$grupo&tipo=$tipo";
                                                        $otable->addData(addLink($serv_id,$page,"Click aqu&iacute; para consultar o editar el registro"));
                                                        $otable->addData($rs->field("grupo"));
                                                        $otable->addData($rs->field("sgrupo_breve"));
                                                        $otable->addData(addLink($rs->field("serv_descripcion").' '.$rs->field("laboratorio_catalogo"),$page,"Click aqu&iacute; para consultar o editar el registro"));
                                                        $otable->addData($rs->field("serv_umedida"));
                                                        $otable->addData($rs->field("unidad_medida"));
                                                        
                                                        if (SIS_EMPRESA_TIPO!=4){//ALMACENES
                                                            $otable->addData($rs->field("referencia"));							                                                        
                                                            $otable->addData(substr($rs->field("tipo_igv"),0,3),"C");
                                                            $otable->addData($rs->field("serv_precio"),"R");
                                                            $otable->addData($rs->field("serv_preciofraccion"),"R");
                                                            $otable->addData($rs->field("emru_ruc"),"C");
                                                        }
                                                        
                                                        if(SIS_EMPRESA_RUC=='20480027494'){//PERUANOESPANOL
                                                            $otable->addData($rs->field("codigo_ant"),"C");
                                                        }else{
                                                            $otable->addData($rs->field("serv_codigo_aux"),"C");
                                                        }
                                                        
                                                        if(inlist(SIS_EMPRESA_TIPO,'2,3')){//empresas publicas, beneficencias
                                                            $otable->addData($rs->field("mnemonico"),"C");
                                                            $otable->addData($rs->field("clas_id"),"C");
                                                            if(SIS_EMPRESA_TIPO==3){//beneficencias
                                                                if(SIS_CIUDAD=='CHICLAYO'){
                                                                    $otable->addData($rs->field("serv_sisteso"),"C");
                                                                }
                                                            }
                                                            $otable->addData($rs->field("serv_estado")=='t'?'ACT':'INA',"C");
                                                        }else{
                                                            $otable->addData($rs->field("serv_estado")=='t'?'ACT':'INA',"C");
                                                        }
                                                        if($rs->field("serv_estado")=='t'){
                                                            $otable->addRow();
                                                        }else{
                                                            $otable->addRow('ANULADO');
                                                        }
                                                }
                                        $contenido_respuesta=$button->writeHTML();
                                        $contenido_respuesta.=$otable->writeHTML();
                                        
                                        $contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";


                                } else {
                                        $otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
                                        $otable->addRow();
                                        $contenido_respuesta=$otable->writeHTML();
                                }
                        }
                else
                        $contenido_respuesta="";


        //  $objResponse->addAlert($Nameobj);
                //se analiza el tipo de funcionamiento
                if($op==1){//si es llamado para su funcionamiento en ajax con retornoa a un div
                        $objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
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

	function buscarServicio($cadena,$grupo_id,$sgrupo_id,$pg,$Nameobj='') 
        {
                global $conn;
		$objResponse = new xajaxResponse();
                
                $sql = new servicios_SQLlista();
                if(getSession('SET_EMRU_EMISOR')){
                    $sql->whereRUCID(getSession('SET_EMRU_EMISOR'));
                }                
                if($grupo_id){
                    $sql->whereGrupoID($grupo_id);
                }
                if($sgrupo_id){
                    $sql->whereSubGrupoID($sgrupo_id);
                }
                if(ctype_digit($cadena)){ //si la cadena recibida son todos digitos
                    $sql->whereID($cadena);
                }
                else{
                    if(strlen($cadena)<3){
                        $objResponse->addAssign($Nameobj,'innerHTML', '');
                        $objResponse->addAlert('se espera al menos 3 caracteres de busqueda...');
                        return $objResponse;
                    }
                    $sql->whereDescrip($cadena);
                }    
                    
                $sql->orderUno();

                $sql= $sql->getSQL();
                        
                     //echo $sql;
                     //$objResponse->addAlert($sql);

                        //guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
                        $rs = new query($conn, strtoupper($sql),$pg,80);

                        $otable = new  Table("","100%",8);
                        $button = new Button;
                        $pg_ant = $pg-1;
                        $pg_prox = $pg+1;

                        if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscarTitular(1,$cadena,$pg_ant','$Nameobj')","content");
                        if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscarTitular(1,$cadena,'$pg_prox','$Nameobj')","content");
                                if ($rs->numrows()>0) {
                                        $link=addLink("Cerrar","javascript:document.getElementById('$Nameobj').innerHTML=''");
                                        $otable->addColumnHeader("$link",false,"1%","C");
                                        //$otable->addColumnHeader("",false,"2%","C"); // Titulo, Ordenar?, ancho, alineacion
                                        $otable->addColumnHeader("C&oacute;d",true,"3%", "L"); 
                                        $otable->addColumnHeader("Grupo",true,"10%", "L"); 
                                        $otable->addColumnHeader("Sub.Grupo",true,"10%", "L"); 
                                        $otable->addColumnHeader("Descripci&oacute;n",true,"40%", "L");                                         
                                        $otable->addColumnHeader("Referencia",true,"15%", "L"); 
                                        $otable->addColumnHeader("Espec&iacute;fica",true,"5%", "L"); 
                                        $otable->addColumnHeader("Precio",true,"5%", "L"); 
                                                
                                        $otable->addRow(); // adiciona la linea (TR)
                                        //$rs->getrow();
                                        $btnFocus='';
                                        while ($rs->getrow()){
                                                $id = $rs->field("serv_codigo"); // captura la clave primaria del recordsource
                                                $campoTexto_de_Retorno = especialChar($rs->field("serv_descripcion").' '.$rs->field("laboratorio_catalogo"));
                                                $serv_unidades=$rs->field("serv_equi_unidades");
                                                $serv_umedida=$rs->field("serv_umedida");
                                                /* botones */
                                                $button2 = new Button;
                                                $button2->setDiv(FALSE);
                                                $button2->setStyle("");
                                                $button2->addItem("Elegir"
                                                            ,"javascript:xajax_eligeServicio('$id','$campoTexto_de_Retorno','$serv_unidades','$serv_umedida')","content",0,0,"botonAgg","button","","btn_$id");

                                                $otable->addData($button2->writeHTML());				
                                                $otable->addData($rs->field("serv_id"));
                                                $otable->addData($rs->field("grupo"));
                                                $otable->addData($rs->field("sgrupo_breve"));							
                                                $otable->addData($rs->field("serv_descripcion").' '.$rs->field("laboratorio_catalogo").'-'.$rs->field("serv_umedida"));                                                
                                                $otable->addData($rs->field("referencia"));							
                                                $otable->addData($rs->field("clas_id"),"C");
                                                $otable->addData(number_format($rs->field("serv_precio"), 2, '.', ','),"R");
                                                $otable->addRow();
                                                
                                                $btnFocus=$btnFocus?$btnFocus:"btn_$id";

                                        }
                                $contenido_respuesta=$button->writeHTML();
                                $contenido_respuesta.=$otable->writeHTML();
                                $contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: $j</div>";


                    } else {
                            $otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); // Tï¿½tulo, Ordenar?, ancho, alineaciï¿½n
                            $otable->addRow();
                            $contenido_respuesta=$otable->writeHTML();
                    }

                    $objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
                    $objResponse->addScript("document.frm.$btnFocus.focus()");
                    return $objResponse;
        }
        

	function buscarServicioCobertura($cadena,$grupo_id,$sgrupo_id,$pg,$Nameobj='') 
        {
                global $relacionamento_id,$conn;
		$objResponse = new xajaxResponse();
                
                $sql = new servicios_SQLlista();
                $sql->whereActivo();
                //$sql->whereConPrecio();
                $sql->whereNOSeleccionados($relacionamento_id);
                
                if($grupo_id){
                    $sql->whereGrupoID($grupo_id);
                }
        
                if($sgrupo_id){
                    $sql->whereSubGrupoID($sgrupo_id);
                }
                
                if(ctype_digit($cadena)){ //si la cadena recibida son todos digitos
                    $sql->whereID($cadena);
                }
                else{
                    if(strlen($cadena)<3){
                        $objResponse->addAssign($Nameobj,'innerHTML', '');
                        $objResponse->addAlert('se espera al menos 3 caracteres de busqueda...');
                        return $objResponse;
                    }
                    $sql->whereDescrip($cadena);
                }    
                    
                $sql->orderUno();

                $sql= $sql->getSQL();
                        
                     //echo $sql;
                     //$objResponse->addAlert($sql);

                        //guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
                        $rs = new query($conn, strtoupper($sql),$pg,40);

                        $otable = new  Table('',"100%",8);
                        $button = new Button;
                        $pg_ant = $pg-1;
                        $pg_prox = $pg+1;

                        if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscarTitular(1,$cadena,$pg_ant','$Nameobj')","content");
                        if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscarTitular(1,$cadena,'$pg_prox','$Nameobj')","content");
                                if ($rs->numrows()>0) {
                                        $link=addLink("Cerrar","javascript:document.getElementById('$Nameobj').innerHTML=''");
                                        $otable->addColumnHeader("$link",false,"1%","C");
                                        //$otable->addColumnHeader("",false,"2%","C"); // Titulo, Ordenar?, ancho, alineacion
                                        $otable->addColumnHeader("Condici&oacute;n","10%", "L");
                                        $otable->addColumnHeader("Tipo Precio","10%", "L");
                                        $otable->addColumnHeader("C&oacute;d",true,"3%", "L"); 
                                        $otable->addColumnHeader("Grupo",true,"10%", "L"); 
                                        $otable->addColumnHeader("Sub.Grupo",true,"10%", "L"); 
                                        $otable->addColumnHeader("Descripci&oacute;n",true,"48%", "L");                                         
                                        $otable->addColumnHeader("Referencia",true,"15%", "L"); 
                                        $otable->addColumnHeader("Espec&iacute;fica",true,"5%", "L"); 
                                        $otable->addColumnHeader("Precio",true,"5%", "L"); 
                                                
                                        $otable->addRow(); // adiciona la linea (TR)
                                        //$rs->getrow();
                                        $btnFocus='';
                                        while ($rs->getrow()){
                                                $id = $rs->field("serv_codigo"); // captura la clave primaria del recordsource
                                                $campoTexto_de_Retorno = especialChar($rs->field("serv_descripcion"));
                                                
                                                /* botones */
                                                $button2 = new Button;
                                                $button2->setDiv(FALSE);
                                                $button2->setStyle("");
//                                                $button2->addItem("Agregar"
//                                                            ,"javascript:if(document.frm.tr_tipo_$id.value==''){if(confirm('No ha Seleccionado Tipo, Seguro de Continuar?')){xajax_eligeServicio('$id',document.frm.tr_tipo_$id.value,'$cadena')}}else{xajax_eligeServicio('$id',document.frm.tr_tipo_$id.value,'$cadena')}","content",2,0,"botonAgg","button","","btn_$id");

                                                $button2->addItem("Agregar"
                                                            ,"javascript:
                                                        if(document.frm.tr_tipo_$id.value==''){
                                                            alert('Seleccione Condicion')
                                                        } else {if(document.frm.tr_tabl_tipoprecio_$id.value==''){
                                                                        alert('Seleccione Tipo de Precio')
                                                                      } else {
                                                                            xajax_eligeServicio('$id',document.frm.tr_tipo_$id.value,document.frm.tr_tabl_tipoprecio_$id.value,'$cadena')
                                                                        }
                                                                      }","content",2,0,"botonAgg","button","","btn_$id");
                                                
                                                $otable->addData($button2->writeHTML());				
                                                
                                                
                                                $sqlTipo = array(1 => "Obligatorio",
                                                                 2 => "Selectivo");
                                                $otable->addData(listboxField("Condicion", $sqlTipo, "tr_tipo_$id", 0,"--Elija Condición--"));
                                                
                                                
                                                $sqltipoPrecio="SELECT tabl_id AS id,
                                                                       tabl_descripcion AS descripcion 
                                                             FROM catalogos.tabla
                                                             WHERE tabl_tipo='TIPO_PRECIO' 
                                                                    AND tabl_porcent IS NULL 
                                                             ORDER BY 1";
                                                
                                                $otable->addData(listboxField("Tipo Precio",$sqltipoPrecio,"tr_tabl_tipoprecio_$id","","--Elija Tipo de Precio--","onChange=\"xajax_eligeTipoPrecio(1,this.value,'$id')\""));
            
                                                $otable->addData($rs->field("serv_id"));
                                                $otable->addData($rs->field("grupo"));
                                                $otable->addData($rs->field("sgrupo"));							
                                                $otable->addData($rs->field("serv_descripcion"));                                                
                                                $otable->addData($rs->field("referencia"));							
                                                $otable->addData($rs->field("clas_id"),"C");
                                                
                                                $otable->addData("<div id='precio_$id'></div>","R");
                                                $otable->addRow();
                                                
                                                $btnFocus=$btnFocus?$btnFocus:"btn_$id";

                                        }
                                $contenido_respuesta=$button->writeHTML();
                                $contenido_respuesta.=$otable->writeHTML();
                                $contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: $j</div>";


                    } else {
                            $otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); // Tï¿½tulo, Ordenar?, ancho, alineaciï¿½n
                            $otable->addRow();
                            $contenido_respuesta=$otable->writeHTML();
                    }

                    $objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
                    $objResponse->addScript("document.frm.$btnFocus.focus()");
                    return $objResponse;
        }


	function buscarServicioVinculado($cadena,$Nameobj='') 
        {
                global $relacionamento_id,$conn;
		$objResponse = new xajaxResponse();
                
                $sql = new servicios_SQLlista();
                $sql->whereActivo();
                //$sql->whereConPrecio();
                //$sql->whereNOSeleccionadosVinculados($relacionamento_id);                
                
                if(ctype_digit($cadena)){ //si la cadena recibida son todos digitos
                    $sql->whereID($cadena);
                }
                else{
                    if(strlen($cadena)<3){
                        $objResponse->addAssign($Nameobj,'innerHTML', '');
                        $objResponse->addAlert('se espera al menos 3 caracteres de busqueda...');
                        return $objResponse;
                    }
                    $sql->whereDescrip($cadena);
                }    
                    
                $sql->orderUno();

                $sql= $sql->getSQL();
                        
                     //echo $sql;
                     //$objResponse->addAlert($sql);

                        //guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
                        $rs = new query($conn, strtoupper($sql));

                        $otable = new  Table('',"100%",8);
                        $button = new Button;
                        $pg_ant = $pg-1;
                        $pg_prox = $pg+1;

                        if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscarTitular(1,$cadena,$pg_ant','$Nameobj')","content");
                        if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscarTitular(1,$cadena,'$pg_prox','$Nameobj')","content");
                                if ($rs->numrows()>0) {
                                        $link=addLink("Cerrar","javascript:document.getElementById('$Nameobj').innerHTML=''");
                                        $otable->addColumnHeader("$link",false,"1%","C");
                                        //$otable->addColumnHeader("",false,"2%","C"); // Titulo, Ordenar?, ancho, alineacion
                                        $otable->addColumnHeader("Tipo Precio",true,"5%", "L"); 
                                        $otable->addColumnHeader("C&oacute;d",true,"3%", "L"); 
                                        $otable->addColumnHeader("Grupo",true,"10%", "L"); 
                                        $otable->addColumnHeader("Sub.Grupo",true,"10%", "L"); 
                                        $otable->addColumnHeader("Descripci&oacute;n",true,"45%", "L");                                         
                                        $otable->addColumnHeader("Referencia",true,"15%", "L"); 
                                        $otable->addColumnHeader("Espec&iacute;fica",true,"5%", "L"); 
                                        $otable->addColumnHeader("Precio",true,"5%", "L"); 
                                                
                                        $otable->addRow(); // adiciona la linea (TR)
                                        //$rs->getrow();
                                        $btnFocus='';
                                        while ($rs->getrow()){
                                                $id = $rs->field("serv_codigo"); // captura la clave primaria del recordsource
                                                $campoTexto_de_Retorno = especialChar($rs->field("serv_descripcion"));
                                                
                                                /* botones */
                                                $button2 = new Button;
                                                $button2->setDiv(FALSE);
                                                $button2->setStyle("");
                                                $button2->addItem("Agregar"
                                                            ,"javascript:if(!document.frm.tr_tabl_tipoprecio_$id.value){alert('seleccione tipo de precio...')}else{ xajax_eligeServicio('$id','$cadena',document.frm.tr_tabl_tipoprecio_$id.value)}","content",2,0,"botonAgg","button","","btn_$id");

                                                $otable->addData($button2->writeHTML());	
                                                
                                                $sqltipoPrecio="SELECT tabl_id,tabl_descripcion 
                                                             FROM catalogos.tabla
                                                             WHERE tabl_tipo='TIPO_PRECIO' AND tabl_porcent IS NULL ORDER BY 1";
                                                $otable->addData(listboxField("Tipo Precio",$sqltipoPrecio,"tr_tabl_tipoprecio_$id","","--Elija Tipo de Precio--","onChange=\"xajax_eligeTipoPrecio(1,this.value,'$id')\""));
            
                                                $otable->addData($rs->field("serv_id"));
                                                $otable->addData($rs->field("grupo"));
                                                $otable->addData($rs->field("sgrupo"));							
                                                $otable->addData($rs->field("serv_descripcion"));
                                                $otable->addData($rs->field("referencia"));							                                                
                                                $otable->addData($rs->field("clas_id"),"C");
                                                
                                                $otable->addData("<div id='precio_$id'></div>","R");
                                                
                                                $otable->addRow();
                                                
                                                $btnFocus=$btnFocus?$btnFocus:"btn_$id";

                                        }
                                $contenido_respuesta=$button->writeHTML();
                                $contenido_respuesta.=$otable->writeHTML();
                                $contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: $j</div>";


                    } else {
                            $otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); // Tï¿½tulo, Ordenar?, ancho, alineaciï¿½n
                            $otable->addRow();
                            $contenido_respuesta=$otable->writeHTML();
                    }

                    $objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
                    $objResponse->addScript("document.frm.$btnFocus.focus()");
                    return $objResponse;
        }
        
        

        function buscarServicioVinculado2($cadena,$Nameobj='') 
        {
                global $relacionamento_id,$conn;
		$objResponse = new xajaxResponse();
                
                $sql = new servicios_SQLlista();
                $sql->whereActivo();
                //$sql->whereConPrecio();
                $sql->whereNOSeleccionadosVinculados2($relacionamento_id);                
                
                if(ctype_digit($cadena)){ //si la cadena recibida son todos digitos
                    $sql->whereID($cadena);
                }
                else{
                    if(strlen($cadena)<3){
                        $objResponse->addAssign($Nameobj,'innerHTML', '');
                        $objResponse->addAlert('se espera al menos 3 caracteres de busqueda...');
                        return $objResponse;
                    }
                    $sql->whereDescrip($cadena);
                }    
                    
                $sql->orderUno();

                $sql= $sql->getSQL();
                        
                     //echo $sql;
                     //$objResponse->addAlert($sql);

                        //guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
                        $rs = new query($conn, strtoupper($sql));

                        $otable = new  Table("","100%",8);
                        $button = new Button;
                        $pg_ant = $pg-1;
                        $pg_prox = $pg+1;

                        if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscarTitular(1,$cadena,$pg_ant','$Nameobj')","content");
                        if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscarTitular(1,$cadena,'$pg_prox','$Nameobj')","content");
                                if ($rs->numrows()>0) {
                                        $link=addLink("Cerrar","javascript:document.getElementById('$Nameobj').innerHTML=''");
                                        $otable->addColumnHeader("$link",false,"1%","C");
                                        //$otable->addColumnHeader("",false,"2%","C"); // Titulo, Ordenar?, ancho, alineacion
                                        $otable->addColumnHeader("Tipo Precio",true,"5%", "L"); 
                                        $otable->addColumnHeader("C&oacute;d",true,"3%", "L"); 
                                        $otable->addColumnHeader("Grupo",true,"10%", "L"); 
                                        $otable->addColumnHeader("Sub.Grupo",true,"10%", "L"); 
                                        $otable->addColumnHeader("Descripci&oacute;n",true,"45%", "L");                                         
                                        $otable->addColumnHeader("Referencia",true,"15%", "L"); 
                                        $otable->addColumnHeader("Espec&iacute;fica",true,"5%", "L"); 
                                        $otable->addColumnHeader("Precio",true,"5%", "L"); 
                                                
                                        $otable->addRow(); // adiciona la linea (TR)
                                        //$rs->getrow();
                                        $btnFocus='';
                                        while ($rs->getrow()){
                                                $id = $rs->field("serv_codigo"); // captura la clave primaria del recordsource
                                                $campoTexto_de_Retorno = especialChar($rs->field("serv_descripcion").' '.$rs->field("laboratorio_catalogo"));
                                                
                                                /* botones */
                                                $button2 = new Button;
                                                $button2->setDiv(FALSE);
                                                $button2->setStyle("");
                                                $button2->addItem("Agregar"
                                                            ,"javascript:if(!document.frm.tr_tabl_tipoprecio_$id.value){alert('seleccione tipo de precio...')}else{ xajax_eligeServicio('$id','$cadena',document.frm.tr_tabl_tipoprecio_$id.value)}","content",2,0,"botonAgg","button","","btn_$id");

                                                $otable->addData($button2->writeHTML());	
                                                
                                                $sqltipoPrecio="SELECT tabl_id,tabl_descripcion 
                                                             FROM catalogos.tabla
                                                             WHERE tabl_tipo='TIPO_PRECIO' AND tabl_porcent IS NULL ORDER BY 1";
                                                $otable->addData(listboxField("Tipo Precio",$sqltipoPrecio,"tr_tabl_tipoprecio_$id","","--Elija Tipo de Precio--","onChange=\"xajax_eligeTipoPrecio(1,this.value,'$id')\""));
            
                                                $otable->addData($rs->field("serv_id"));
                                                $otable->addData($rs->field("grupo"));
                                                $otable->addData($rs->field("sgrupo"));							
                                                $otable->addData($rs->field("serv_descripcion"));
                                                $otable->addData($rs->field("referencia"));							                                                
                                                $otable->addData($rs->field("clas_id"),"C");
                                                
                                                $otable->addData("<div id='precio_$id'></div>","R");
                                                
                                                $otable->addRow();
                                                
                                                $btnFocus=$btnFocus?$btnFocus:"btn_$id";

                                        }
                                $contenido_respuesta=$button->writeHTML();
                                $contenido_respuesta.=$otable->writeHTML();
                                $contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: $j</div>";


                    } else {
                            $otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); // Tï¿½tulo, Ordenar?, ancho, alineaciï¿½n
                            $otable->addRow();
                            $contenido_respuesta=$otable->writeHTML();
                    }

                    $objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
                    $objResponse->addScript("document.frm.$btnFocus.focus()");
                    return $objResponse;
        }        

	function buscar_TipTraAcumMov($op,$formData,$arrayParam,$pg=1,$Nameobj='')
	{
		global $conn,$param,$nomeCampoForm;;
		$objResponse = new xajaxResponse();
		
		$arrayParam=decodeArray($arrayParam);

		$paramFunction= new manUrlv1();
		$paramFunction->setUrlExternal($arrayParam);

		$cadena=is_array($formData)?trim(strtoupper($formData['Sbusc_cadena'])):$formData;
		if(!$cadena && $op==2) $cadena=getSession("cadSearch");
		
		$colSearch=$paramFunction->getValuePar('colSearch');
		$colOrden=$paramFunction->getValuePar('colOrden');
		$numForm=$paramFunction->getValuePar('numForm');		
		$busEmpty=$paramFunction->getValuePar('busEmpty');
		$apco_id=$paramFunction->getValuePar('apco_id');
		$caga_id=$paramFunction->getValuePar('caga_id');
		
		$grupo=is_array($formData)?$formData['Sbusc_grupo']:0;
						
		if(strlen($cadena)>0 or $grupo>1 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	
			$otable = new TableSimple(iif($cadena,'!=','','RESULTADO DE: '.$cadena,''),"100%",6,'tLista'); // T�tulo, ancho, Cantidad de columas,id de la tabla									

			$sql= new servicios_SQLlista();

			if($caga_id!='9'){
				$sql->whereCatGto($caga_id);
				$sql->wherePartidaComponente($apco_id);
			}
			
			$sql->whereGrupoID($grupo) ;

			//se analiza la columna de busqueda
			switch($colSearch){
				case 'codigo': // si se recibe el campo id
					$sql->whereID($cadena);
					break;
	
				default:// si se no se recibe ningun campo de busqueda
					if(ctype_digit($cadena)) //si la cadena recibida son todos digitos
						$sql->whereID($cadena);
					else
						$sql->whereDescrip($cadena);
	
					break;
				}

							
			$sql->orderUno();

			//2.3.21.22

			$sql=$sql->getSQL();
			//echo $sql; 
			//$objResponse->addAlert($sql);
		
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) {setSession("cadSearch",$cadena);}			
	
			$rs = new query($conn, strtoupper($sql));			

			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
					/* Agrego las cabeceras de mi tabla */
					$otable->addColumnHeader("C&oacute;digo","5%", "L"); // T�tulo, ancho, alineaci�n											
					$otable->addColumnHeader("Partida","5%", "L"); // T�tulo, ancho, alineaci�n						
					$otable->addColumnHeader("Concepto","90%", "L"); // T�tulo, ancho, alineaci�n						
					$otable->addRowHead(); 					

					while ($rs->getrow()) {
					
						$id = $rs->field("serv_id");// captura la clave primaria del recordsource

						$campoTexto_de_Retorno = $rs->field("clas_id").' '.especialChar($rs->field("serv_descripcion"));

						if ($nomeCampoForm){ /* si la llamada es desde la busqueda avanzada (AvanzLookup) */
							$otable->addData(addLink($rs->field("serv_id"),"javascript:update('$id','$campoTexto_de_Retorno','$numForm')","Click aqu&iacute; para seleccionar el registro"));
						}elseif($op!=3){  /* Si estoy en la p�gina normal */ 
							/* agrego pg como par�metro a ser enviado por la URL */
							$param->removePar('pg'); /* Remuevo el par�metro */
							$param->addParComplete('pg',$pg); /* Agrego el par�metro */
							
							$otable->addData($rs->field("clas_id"));
						}
						$otable->addData($rs->field("clas_id"));						
						$otable->addData($rs->field("serv_descripcion"));
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
					$objResponse->addScript($Nameobj .' = "'.$campoTexto_de_Retorno.'";');
					return $objResponse;
					}
				else
					return $campoTexto_de_Retorno;
				}
			else//si es llamado como una simple funciona de PHP
				return $contenido_respuesta	;
	}



        function buscarActualizarPrecio($op,$cadena='',$bd_depeid,$tipo_precio=10,$grupo='',$sgrupo='',$colSearch='',$colOrden=1,$busEmpty=0,$numForm=0, $pg=1,$Nameobj)
        {
                global $conn;

                $objResponse = new xajaxResponse();

                $cadena=trim(strtoupper($cadena));


                if(strlen($cadena)>0 || $busEmpty==1 || $grupo ){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
                        $sql = new serviciosActualizaPrecios_SQLlista();
                        $sql->whereAcceso();
                        $sql->whereTipoPrecio($tipo_precio);
                        
                        if($bd_depeid){
                            $sql->whereDepeID(intval($bd_depeid));
                        }else{
                            $sql->whereDepeID(getSession("sis_depe_superior"));
                        }
//                        OJO. LA BUSQUEA POR DESCRIPCION FUNCIONA MAL
//                        else{
//                            $sql->whereDepeTodos2(getSession("sis_depe_superior"));
//                        }
                        $sql->whereGrupoID($grupo);
                        $sql->whereSubGrupoID($sgrupo);

                        //se analiza la columna de busqueda
                        if($cadena){

                            switch($colSearch){
                                case 'serv_id': // si se recibe el campo id
                                        $sql->whereID($cadena);
                                        break;

                                default:// si no se recibe ningun campo de busqueda
                                    if($cadena){
                                        if(ctype_digit($cadena)){ //si la cadena recibida son todos digitos
                                            $sql->whereID($cadena);
                                        }else{
                                            $sql->whereDescrip($cadena);
                                        }
                                    }            
                                    break;
                                }
                        }
                        $sql->orderUno();

                        $sql= $sql->getSQL();
                        
                        //guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
                        if ($op==1 && getParam("clear")!=2)
                                setSession("cadSearch",$cadena);

                                $rs = new query($conn, strtoupper($sql),$pg,150);
                                //$otable = new  Table("$sql","100%",10);
                                $otable = new  Table("","100%",9);

                                
                                $button = new Button;
                                $pg_ant = $pg-1;
                                $pg_prox = $pg+1;
                                if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscarActualizarPrecio(1,'$cadena','$bd_depeid','$tipo_precio','$grupo','$sgrupo','','$colOrden','$busEmpty','$numForm','$pg_ant','$Nameobj')","content");
                                if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscarActualizarPrecio(1,'$cadena','$bd_depeid','$tipo_precio','$grupo','$sgrupo','','$colOrden','$busEmpty','$numForm','$pg_prox','$Nameobj')","content");
                                
                                if ($rs->numrows()>0) {
                                                $otable->addColumnHeader("C&oacute;d",true,"3%", "L"); 
                                                $otable->addColumnHeader("Grupo",true,"10%", "L"); 
                                                $otable->addColumnHeader("Sub.Grupo",true,"10%", "L"); 
                                                $otable->addColumnHeader("Descripci&oacute;n",true,"55%", "L"); 
                                                $otable->addColumnHeader("Presentaci&oacute;n",true,"5%", "L");
                                                $otable->addColumnHeader("",true,"1%", "C"); 
                                                $otable->addColumnHeader("Precio",true,"5%", "C"); 
                                                $otable->addColumnHeader("RUC",true,"5%", "C");
                                                $otable->addColumnHeader("Est",true,"7%", "C"); 
                                                $otable->addRow(); // adiciona la linea (TR)
                                                while ($rs->getrow()) {
                                                        $id = $rs->field("sepr_id"); // captura la clave primaria del recordsource
                                                        $serv_id=$rs->field("serv_id");
                                                        // fin de definicion

                                                        $campoTexto_de_Retorno = especialChar($rs->field("serv_descripcion"));

                                                        $otable->addData($serv_id);
                                                        $otable->addData($rs->field("grupo"));
                                                        $otable->addData($rs->field("sgrupo_breve"));							
                                                        $otable->addData($rs->field("serv_descripcion"));
                                                        $otable->addData($rs->field("serv_umedida"));
                                                        
                                                        $precio=$rs->field("precio");
                                                        if(getSession("sis_level")==1) {//visitante
                                                            $otable->addData("");
                                                        }
                                                        else {
                                                            $otable->addData("<div id=\"img_$id\"><a class=\"link\" href=\"#\" onClick=\"xajax_graba_precio('$id',1,$precio)\"><img src=\"../../img/editar.gif\" border=0 align=absmiddle hspace=1 alt=\"Editar\"></a></div>");
                                                        }
                                                        
                                                        $otable->addData("<div id=\"Precio_$id\">".$precio."</div>","R");
                                                

                                                        
                                                        $otable->addData($rs->field("emru_ruc"),"C");
                                                        
                                                        $otable->addData($rs->field("serv_estado")=='t'?'ACT':'INACT',"C");
                                                        $otable->addRow();
                                                }
                                        $contenido_respuesta=$button->writeHTML();
                                        $contenido_respuesta.=$otable->writeHTML();
                                        
                                        $contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";


                                } else {
                                        $otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
                                        $otable->addRow();
                                        $contenido_respuesta=$otable->writeHTML();
                                }
                        }
                else
                        $contenido_respuesta="";


        //  $objResponse->addAlert($Nameobj);
                //se analiza el tipo de funcionamiento
                if($op==1){//si es llamado para su funcionamiento en ajax con retornoa a un div
                        $objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
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
        
        
	function guardar(){
		global $conn,$param;
		$clear=getParam("clear");
		$destinoUpdate=$this->destinoUpdate.$param->buildPars(true);	
		$pg=is_array($this->arrayNameVar)?$this->arrayNameVar[3]:'pg';
		$param->removePar($pg); /* Remuevo el par�metro p�gina */
		$destinoInsert=$this->destinoInsert.$param->buildPars(true);
		
		// objeto para instanciar la clase sql
		$sql = new UpdateSQL();
				
		$sql->setTable($this->setTable);
		$sql->setKey($this->setKey, $this->valueKey, $this->typeKey);
	
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
                
		$padre_id=$conn->execute("$sqlCommand  RETURNING $this->setKey");
                
//                if ($_POST["f_id"]=='00379'){
//                    echo "XX $sqlCommand  RETURNING $this->setKey";
//                    exit(0);
//                }
                
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
		if ($this->valueKey) {// modificación
                    $last_id=$this->valueKey; 
                    if(strpos($destinoInsert, "?")>0)
                            $destinoUpdate.="&id=$last_id&clear=$clear";
                    else
                            $destinoUpdate.="?id=$last_id&clear=$clear";

                    redirect($destinoUpdate,"content");
							
		}else{ /* Inserci�n */
                    if(strpos($destinoInsert, "?")>0)
                        $destinoInsert.="&id=$padre_id&clear=$clear";  
                    else
                        $destinoInsert.="?id=$padre_id&clear=$clear";
                    
                    redirect($destinoInsert,"content");							
		}
	}
        
//        function guardar2(){
//		global $conn,$param;
//		$nomeCampoForm=getParam("nomeCampoForm");
//	
//		$param->removePar('pg'); /* Remuevo el parametro página */
//		$destinoUpdate=$this->destinoUpdate.$param->buildPars(true);
//		$destinoInsert=$this->destinoInsert.$param->buildPars(true);
//		
//		// objeto para instanciar la clase sql
//		$sql = new UpdateSQL();
//				
//		$sql->setTable($this->setTable);
//		$sql->setKey($this->setKey, $this->valueKey, $this->typeKey);
//	
//		include("../guardar_tipoDato.php");
//	
//
//                $sql->setAction("INSERT");
//		$sql->addField('usua_id', getSession("sis_userid"), "Number");							
//	
//		/* Aquí puedo agregar otros campos a la sentencia SQL */
//		$this->addField($sql);
//
//		/* Ejecuto el SQL */
//                $sqlCommand=$sql->getSQL();
//		$padre_id=$conn->execute("$sqlCommand  RETURNING $this->setKey");                
//                //alert($sqlCommand);
//		//$conn->execute($sql->getSQL());
//		$error=$conn->error();
//		if($error){ 
//                    alert($error);	/* Muestro el error y detengo la ejecución */
//		}else{
//                    /*  muestra mensaje noticia del la base de datos, pero no detiene la ejecucion	*/
//                    $notice=$conn->notice();
//                    if($notice){
//                        alert($notice,0);
//                    }
//		}
//                /* Datos que se retornan desde un (avanzlookup) */
//                $serv_codigo=$padre_id;
//                $umedida=getParam('Sr_serv_umedida');
//                $equi_unidades=getParam('nr_serv_equi_unidades');
//                $cadena=getParam('Sr_serv_descripcion');
//                /* Comandos Javascript */		
//                echo "<script language=\"javascript\">
//                                parent.parent.content.document.forms[0].Sx_serv_codigo.value = '$serv_codigo';
//				parent.parent.content.document.forms[0].Sbusc_medida.value = '$umedida';
//				parent.parent.content.document.forms[0].xxxequi_unidades.value = $equi_unidades;
//				parent.parent.content.document.forms[0].Sbusc_cadena.value='$cadena';
//				parent.parent.content.cerrar();
//                        </script>";
//
//        }
                    
	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	
}

class servicios_SQLlista extends selectSQL {
	function __construct(){
		$this->sql="SELECT 
                                a.depe_id,
				a.segr_id,
                                a.sesg_id,
                                a.espe_id,
                                LPAD(a.serv_codigo::TEXT,5,'0') AS serv_id,
				a.serv_codigo,
				a.serv_descripcion,
                                a.serv_breve,
                                a.serv_precio,
                                a.serv_preciofraccion,
				CASE WHEN a.serv_estado=TRUE THEN 1 ELSE 0 END AS serv_estado2,
                                a.serv_estado,
                                a.tabl_tipo_componente,
                                a.tabl_subtipo_componente,
                                a.tabl_fila_componente,
                                a.serv_sisteso,
                                a.serv_porcentaje,
                                a.serv_umedida,
                                a.tabl_tipo_igv,
                                a.serv_aplica_ajuste,
                                a.emru_id,
                                a.exam_id,
                                a.serv_codigo_aux,
                                a.tabl_tipoprecio,
                                a.serv_porcen_utilidad,
                                a.codigo_ant,
				b.segr_descripcion as grupo, 
                                b.segr_vinculo,
                                b.segr_destino,
                                b.segr_tipo,
                                b.segr_almacen,
				e.sesg_descripcion as sgrupo,
                                e.sesg_descripbreve as sgrupo_breve,
				a.clas_id, ";
            if(SIS_EMPRESA_TIPO==3){
                $this->sql.="
                                comp.comp_mnemonico::TEXT||'-'||comp.peri_anno::TEXT AS mnemonico,
                             ";
            }                                
                $this->sql.="   a.serv_equi_unidades,
                                a.serv_stockminimo,
                                a.serv_preciocosto,
                                a.comp_id,
                                a.serv_periodicidad,
                                a.tipl_id,
                                a.tabl_vinculado,
                                a.tabl_tipoconcepto,
                                a.serv_formula,
                                a.serv_pensionable,
                                a.serv_conafovicer,
                                a.serv_sctr,
                                a.serv_cts,
                                a.serv_editable,
                                a.serv_ir5ta,
                                a.serv_ir5ta_meses_proyecta,
                                a.serv_formula2,
                                a.serv_descripcion_cts,
                                a.serv_automatico,
                                a.serv_plame,
                                a.serv_adjunto_id,
                                a.serv_porcent_detraccion,
                                a.serv_porcent_convenio,
                                a.serv_fregistro,
                                a.serv_actualfecha,
                                a.serv_genera_contabilidad,
                                a.tabl_farmacia_laboratorio,
                                a.tabl_marca,
                                a.serv_essalud,
                                a.serv_publicar,
                                a.serv_gratuito,
                                a.tabl_tipoprecio_dependencia,
                                a.serv_principio_reactivo,
                                a.serv_accion_farmacologica,
                                a.serv_observaciones,
                                a.tabl_ubicacion,
                                a.tabl_umedida,
                                a.serv_codigo_barras,
                                a.serv_codigo_interoperabilidad,
                                d.clas_descripcion,
                                dd.tabl_descripcion AS unidad_medida,
                                ";
            if(SIS_GESTMED==1){                
                $this->sql.="   f.exam_nombre,
                                CASE WHEN a.espe_id IS NOT NULL THEN g.espe_descripcion 
                                     WHEN a.tabl_tipo_componente IS NOT NULL AND a.tabl_subtipo_componente IS NOT NULL AND a.tabl_fila_componente IS NOT NULL 
                                            THEN h.tabl_descripcion||'/'||i.tabl_descripcion||'/'||j.tabl_descripcion
                                     WHEN a.tabl_tipo_componente IS NOT NULL AND a.tabl_subtipo_componente IS NOT NULL
                                            THEN h.tabl_descripcion||'/'||i.tabl_descripcion
                                     WHEN a.tabl_tipo_componente IS NOT NULL
                                            THEN h.tabl_descripcion
                                     ELSE '' 
                                END AS referencia, ";
            }else{
                    $this->sql.="   NULL AS exam_nombre,
                                CASE WHEN a.tabl_tipo_componente IS NOT NULL AND a.tabl_subtipo_componente IS NOT NULL AND a.tabl_fila_componente IS NOT NULL 
                                            THEN h.tabl_descripcion||'/'||i.tabl_descripcion||'/'||j.tabl_descripcion
                                     WHEN a.tabl_tipo_componente IS NOT NULL AND a.tabl_subtipo_componente IS NOT NULL
                                            THEN h.tabl_descripcion||'/'||i.tabl_descripcion
                                     WHEN a.tabl_tipo_componente IS NOT NULL
                                            THEN h.tabl_descripcion
                                     ELSE '' 
                                END AS referencia, ";                    
            }                
                $this->sql.="   w.depe_ruc AS emru_ruc,
                                k.tabl_descripcion AS tipo_igv,
                                
                                CASE WHEN a.serv_estado=true THEN 'ACTIVO' ELSE 'INACTIVO' END AS estado,
                                CASE WHEN a.serv_pensionable=1 THEN 'SI' ELSE 'NO' END AS pensionable,
                                CASE WHEN a.serv_ir5ta=1 THEN 'SI'||'('||serv_ir5ta_meses_proyecta::TEXT||')' ELSE 'NO' END AS afecto_ir5ta,
                                CASE WHEN a.serv_automatico=1 THEN 'SI' ELSE 'NO' END AS automatico,
                                CASE WHEN a.serv_periodicidad=1 THEN 'PERMANENTE' ELSE 'UNICA VEZ' END AS periodicidad,
                                hh.tabl_descripcion AS laboratorio_catalogo,
                                l.tabl_descripcion AS vinculado, 
                                m.tabl_descripaux AS tipo_estudio,
                                x.usua_login as username,
                                y.usua_login as username_actual
                            FROM catalogos.servicio a 
                            LEFT JOIN catalogos.servicio_grupo b on a.segr_id=b.segr_id				
			    LEFT JOIN catalogos.servicio_sgrupo e on a.sesg_id=e.sesg_id								
			    LEFT JOIN catalogos.clasificador d on a.clas_id=d.clas_id	";
            if(SIS_GESTMED==1){     
                $this->sql.="                            
                            LEFT JOIN gestmed.examen f on a.exam_id=f.exam_id
                            LEFT JOIN gestmed.especialidad g ON a.espe_id=g.espe_id ";
            }
                
            if(SIS_EMPRESA_TIPO==3){
                $this->sql.="                            
                            LEFT JOIN siscopp.componente comp on a.comp_id=comp.comp_id
                             ";
            }
                $this->sql.="
                            LEFT JOIN catalogos.tabla h ON a.tabl_tipo_componente=h.tabl_codigo AND h.tabl_tipo='TIPO_COMPONENTE'
                            LEFT JOIN catalogos.tabla i ON a.tabl_subtipo_componente=i.tabl_codigo AND i.tabl_tipo='SUBTIPO_COMPONENTE'
                            LEFT JOIN catalogos.tabla j ON a.tabl_fila_componente=j.tabl_codigo AND j.tabl_tipo='NIVELES_COMPONENTE' 
                            LEFT JOIN catalogos.tabla k ON a.tabl_tipo_igv=k.tabl_codigo AND k.tabl_tipo='TIPO_IGV' 
                            LEFT JOIN catalogos.tabla l ON a.tabl_vinculado=l.tabl_id
                            LEFT JOIN catalogos.tabla m ON b.segr_vinculo=m.tabl_codigo AND m.tabl_tipo='VINCULO_GRUPO_SERVICIO'                             
                            LEFT JOIN catalogos.tabla  hh ON a.tabl_farmacia_laboratorio=hh.tabl_id      
                            LEFT JOIN catalogos.tabla  dd ON a.tabl_umedida=dd.tabl_id
                            LEFT JOIN catalogos.dependencia w ON  a.depe_id=w.depe_id 
                            LEFT JOIN admin.usuario x ON  a.usua_id=x.usua_id 
                            LEFT JOIN admin.usuario y ON  a.serv_actualusua=y.usua_id ";
	}

        
	function whereID($id){
                $this->addWhere("a.serv_codigo=$id");
                                
		//$this->addWhere(sprintf("a.serv_codigo='%s'",$id));	
	}

        function whereID2($id){
                $this->addWhere("(a.serv_codigo=$id OR serv_descripcion ILIKE '%$id%')");
                                
		//$this->addWhere(sprintf("a.serv_codigo='%s'",$id));	
	}
        
        function whereDepeID($depe_id) {
                $this->addWhere(sprintf("a.depe_id='%s'", $depe_id));
        }
        
        function whereDepeTodos($depe_id) {
            $this->addWhere("a.depe_id IN (SELECT depe_id
                                                FROM catalogos.func_treedependencia2($depe_id)
                                                WHERE depe_id>0)");
        }
        
        function whereDepeTodos2($depe_id) {
            $this->addWhere("a.depe_id IS NULL OR a.depe_id IN (SELECT depe_id
                                                FROM catalogos.func_treedependencia2($depe_id)
                                                WHERE depe_id>0)");
        }
        
	function whereIDVarios($id_varios){
                $this->addWhere("a.serv_codigo IN ($id_varios)");
		//$this->addWhere(sprintf("a.serv_codigo='%s'",$id));	
	}
        
        function whereTipo($tipo){
		$this->addWhere("segr_tipo ilike '%$tipo%'");
	}

        function whereEspeID($espe_id) {
                $this->addWhere("a.espe_id=$espe_id");
        }
        
        function whereTipoEstudio($tipo_estudio) {
                $this->addWhere(sprintf("m.tabl_descripaux='%s'", $tipo_estudio));
        }
        
        function whereNotExamID() {
                $this->addWhere("a.exam_id IS NULL");
        }
        
        function whereConExamID($exam_id) {
                $this->addWhere("(a.exam_id IS NULL OR a.exam_id=$exam_id)");
        }        
        
        function whereRUCID($emru_id){
		$this->addWhere(sprintf("a.emru_id='%s'",$emru_id));	
	}       
        
        function whereGrupo($segr_id){
		$this->addWhere(sprintf("a.segr_id='%s'",$segr_id));
	}
        
        function whereActivo(){
		$this->addWhere(sprintf("a.serv_estado='%s'",true));	
	}
        
        function whereNOActivo(){
		$this->addWhere("a.serv_estado=false");	
	}
        
        function whereConPrecio(){
		$this->addWhere('a.serv_precio>0');	
	}
        
        function wherePublicar(){
		$this->addWhere('a.serv_publicar=1');	
	}

        function whereConFaseAsiento($tabl_fase_asiento){
		$this->addWhere("a.serv_codigo IN (SELECT a.serv_codigo
                                    FROM catalogos.servicio_asientos_contables a         
	                            WHERE a.tabl_fase=$tabl_fase_asiento)");
        }

        function whereSinFaseAsiento($tabl_fase_asiento){
		$this->addWhere("a.serv_codigo NOT IN (SELECT a.serv_codigo
                                    FROM catalogos.servicio_asientos_contables a         
	                            WHERE a.tabl_fase=$tabl_fase_asiento)");
        }
        
        function whereConTipoAsiento($tabl_tipo_asiento){
		$this->addWhere("a.serv_codigo IN (SELECT a.serv_codigo
                                    FROM catalogos.servicio_asientos_contables a         
	                            WHERE a.tabl_tipo=$tabl_tipo_asiento)");
        }

        function whereSinTipoAsiento($tabl_tipo_asiento){
		$this->addWhere("a.serv_codigo NOT IN (SELECT a.serv_codigo
                                    FROM catalogos.servicio_asientos_contables a         
	                            WHERE a.tabl_tipo=$tabl_tipo_asiento)");
        }
        
        function whereNOSeleccionados($caco_id){
		$this->addWhere("a.serv_codigo NOT IN (SELECT a.serv_codigo 
                                    FROM serfin.categoria_contrato_servicio a         
	                            WHERE a.caco_id=$caco_id)");	
        }
        
        function whereNOSeleccionadosVinculados($serv_codigo){
		$this->addWhere("a.serv_codigo NOT IN (SELECT a.serv_codigo_vinculado 
                                    FROM catalogos.servicio_vinculados a         
	                            WHERE a.serv_codigo=$serv_codigo)");	
        }
        
        function whereNOSeleccionadosVinculados2($publ_id){
		$this->addWhere("a.serv_codigo NOT IN (SELECT a.serv_codigo
                                    FROM siscore.publicar_servicios a         
	                            WHERE a.publ_id=$publ_id)");	
        }
        
	function whereCatGto($caga_id){
		if($caga_id=='6'){
                    $this->addWhere("(SUBSTR(a.clas_id,1,3)='2.4' OR SUBSTR(a.clas_id,1,3)>='2.6')");
		}
		else{
                    $this->addWhere("SUBSTR(a.clas_id,1,3)<='2.5'");			
		}
	}
        
	function wherePartidaComponente($apco_id){
		$this->addWhere("a.clas_id
					IN (SELECT appa_partida
						FROM siscopp.acumulado_presupuestal_partida
						WHERE apco_id=$apco_id)");	
	}
      
        
        function whereGrupoID($grupo_id){
		if($grupo_id) $this->addWhere("a.segr_id=$grupo_id");	
	}       
        
        function whereSubGrupoID($sgrupo_id){
		if($sgrupo_id) $this->addWhere("a.sesg_id=$sgrupo_id");	
	}               

        function whereTipoIGV($tipo_igv){
		if($tipo_igv) $this->addWhere("a.tabl_tipo_igv=$tipo_igv");
	}

        function whereSinAsiento(){
		$this->addWhere("a.serv_acum_asientos=0");
	}
        
        function whereSinComponente(){
		$this->addWhere("a.comp_id IS NULL");
	}
        
        
        function whereDescrip($cadena){
            if($cadena){
                $array=explode(" ",$cadena);
                $lista='';
                for($i=0; $i<count($array); $i++){
                    $lista.=$lista?" AND ":"";
                    $lista.="LPAD(a.serv_codigo::TEXT,4,'0')||
                                    ' '||b.segr_descripcion||
                                    ' '||e.sesg_descripcion||
                                    ' '||COALESCE(a.clas_id,'')|| 
                                    ' '||a.serv_descripcion ILIKE '%".$array[$i]."%'";
                }

                $this->addWhere("($lista)");
            }
        }

        
        function whereModulo($modulo){
            if($modulo) $this->addWhere("a.segr_id IN (
						SELECT a.segr_id 
                                                FROM catalogos.servicio_grupo a 
						WHERE a.segr_tipo ILIKE '%$modulo%')");
	}


        function whereAcceso(){
                $this->addWhere("b.segr_vinculo IN (SELECT UNNEST(CASE  WHEN usua.usua_ventas_acceso IS NOT NULL THEN
                                                                                        '{'||usua.usua_ventas_acceso||'}' 
                                                                        ELSE '{'||b.segr_vinculo||'}' 
                                                                  END::SMALLINT[])
                                    FROM admin.usuario usua
                                    WHERE usua_id=".getSession('sis_userid')
                                    .")"
                                    );
        }
        
        function wherePartida($partida){
            $this->addWhere("a.clas_id='$partida'");
        }
        
        function wherePartidaLike($partida){
            $this->addWhere("SUBSTRING(a.clas_id,1,LENGTH('$partida')) ='$partida'");
        }        
        
        function whereNotLista($lista){ //incluye detracciones
            if($lista) $this->addWhere("a.serv_codigo NOT IN ($lista)");
	}
        
        
        function whereNOautomatico(){
		$this->addWhere("COALESCE(a.serv_automatico,0)=0");
	}        
        
        function whereNoTipoVinculo($tipo_vinculo){
		$this->addWhere("COALESCE(a.tabl_vinculado,0) NOT IN ($tipo_vinculo)");
	}
        
	function orderUno(){
		$this->addOrder("a.serv_codigo DESC");		
	}	
        
        function orderDos(){
		$this->addOrder("CASE WHEN a.serv_codigo>=8950 then 1
                                      ELSE a.serv_codigo
                                 END DESC
                                 ");		
	}
        
	function getSQL_servicio(){
		$sql="SELECT a.serv_codigo,LPAD(a.serv_codigo::TEXT,5,'0')||COALESCE(' ['||clas_id||'] ','')||serv_descripcion 
					FROM (".$this->getSQL().") AS a 
                            WHERE clas_id IS NOT NULL ORDER BY 1 ";
		return $sql;
	}
        
        
	function getSQL_servicio2(){
		$sql="SELECT a.serv_codigo,'['||clas_id||'] '||SUBSTR(serv_descripcion,1,30)
					FROM (".$this->getSQL().") AS a 
                            WHERE clas_id IS NOT NULL ORDER BY 1 ";
		return $sql;
	}        
        
	function getSQL_servicio3(){
                $sql="SELECT serv_codigo,LPAD(serv_codigo::TEXT,5,'0')
                                ||' ['||SUBSTR(grupo,1,2)||'] '||serv_descripcion
				FROM (".$this->getSQL().") AS a 
                                ORDER BY segr_id,
                                         serv_descripcion";
		return $sql;
	}                
        
        function getSQL_cbox(){
                $sql="SELECT serv_codigo AS id,
                                serv_descripcion AS descripcion
				FROM (".$this->getSQL().") AS a 
                                ORDER BY segr_id,
                                         serv_descripcion";
		return $sql;
	}
        
}

class serviciosGrupo_SQLlista extends selectSQL {
	function __construct($grupo_id,$subgrupo_id,$cces_id,$es_credito){
            //echo $grupo_id;
		$this->sql="SELECT LPAD(a.serv_codigo::TEXT,4,'0') AS serv_id,
                                   a.serv_descripcion AS serv_descripcion
                           FROM catalogos.servicio a 
                           LEFT JOIN catalogos.servicio_grupo b ON a.segr_id=b.segr_id
                           LEFT JOIN catalogos.servicio_sgrupo c ON a.sesg_id=c.sesg_id
                           WHERE a.segr_id=$grupo_id
                                  AND a.sesg_id=$subgrupo_id 
                                  AND a.serv_estado 
                                  AND CASE WHEN $es_credito>1 THEN /*SI ES CREDITO/PEDIDOS/SERFIN ENTONCES FILTRA LOS QUE TIENE PRECIO*/
                                            a.serv_precio>0 
                                           ELSE TRUE
                                      END 
                                  AND CASE WHEN $es_credito=3 AND '".SIS_CIUDAD."'='CHICLAYO' THEN /*PEDIDOS*/
                                                        c.sesg_cementerio=1 
                                                        
                                           ELSE c.sesg_cementerio>=0
                                      END  
                                  AND (a.tabl_tipo_componente ISNULL 
                                   OR a.tabl_tipo_componente::TEXT||a.tabl_subtipo_componente::TEXT||a.tabl_fila_componente::TEXT 
                                       IN (SELECT c.tabl_tipo_componente::TEXT||c.tabl_subtipo_componente::TEXT||a.cces_fila_num::TEXT 
                                            FROM cementerio.componentes_cementerio_estructuras a
                                            LEFT JOIN cementerio.componentes_cementerio_vistas b ON a.ccvi_id=b.ccvi_id 
                                            LEFT JOIN cementerio.componentes_cementerio c ON b.coce_id=c.coce_id 
                                            WHERE a.cces_id=$cces_id 
                                          UNION ALL
                                          SELECT c.tabl_tipo_componente::TEXT||c.tabl_subtipo_componente::TEXT||COALESCE(a.cces_fila_num,0)::TEXT 
                                            FROM cementerio.componentes_cementerio_estructuras a
                                            LEFT JOIN cementerio.componentes_cementerio_vistas b ON a.ccvi_id=b.ccvi_id 
                                            LEFT JOIN cementerio.componentes_cementerio c ON b.coce_id=c.coce_id 
                                            WHERE a.cces_id=$cces_id)
                                    OR a.tabl_tipo_componente::TEXT||COALESCE(a.tabl_subtipo_componente,0)::TEXT||COALESCE(a.tabl_fila_componente,0)::TEXT 
                                        IN (SELECT c.tabl_tipo_componente||COALESCE(c.tabl_subtipo_componente,0)::TEXT||0::TEXT 
                                            FROM cementerio.componentes_cementerio_estructuras a
                                            LEFT JOIN cementerio.componentes_cementerio_vistas b ON a.ccvi_id=b.ccvi_id 
                                            LEFT JOIN cementerio.componentes_cementerio c ON b.coce_id=c.coce_id 
                                            WHERE a.cces_id=$cces_id)
                                    OR a.tabl_tipo_componente::TEXT||COALESCE(a.tabl_subtipo_componente,0)::TEXT
                                        IN (SELECT c.tabl_tipo_componente||0::TEXT 
                                            FROM cementerio.componentes_cementerio_estructuras a
                                            LEFT JOIN cementerio.componentes_cementerio_vistas b ON a.ccvi_id=b.ccvi_id 
                                            LEFT JOIN cementerio.componentes_cementerio c ON b.coce_id=c.coce_id 
                                            WHERE a.cces_id=$cces_id) 
                                        )
                            ORDER BY 1";
	}
}

/*clase para obtener los grupos de Transacci�n*/
class clsGrupoTTra_SQLlista extends selectSQL {

	function __construct(){
		$this->sql=  "SELECT a.*,
                                     b.tabl_descripaux
				FROM catalogos.servicio_grupo a  
                                LEFT JOIN catalogos.tabla b ON a.segr_vinculo=b.tabl_codigo AND b.tabl_tipo='VINCULO_GRUPO_SERVICIO' 
                                ";	
	}
	
	function whereID($id){
		$this->addWhere("a.segr_id=$id");	
	}
	
	function whereDescrip($descrip){
		if($descrip) $this->addWhere("a.segr_descripcion ILIKE '%$descrip%'" );	
	}
	
	function whereModulo($modulo){
		if($modulo) $this->addWhere("a.segr_id IN (
						SELECT a.segr_id 
                                                                WHERE a.segr_tipo ILIKE '%$modulo%')");
		}

	function whereModuloRet($modulo){
		if($modulo) $this->addWhere("a.segr_id IN (
						SELECT a.segr_id 
								WHERE a.segr_tipo='$modulo' OR a.segr_tipo='R' /*retencion* /OR a.segr_tipo='D' /*detraccion*)");
		}
				

        function whereAcceso(){
                $this->addWhere("a.segr_vinculo IN (SELECT UNNEST(CASE  WHEN usua.usua_ventas_acceso IS NOT NULL THEN
                                                                                        '{'||usua.usua_ventas_acceso||'}' 
                                                                        ELSE '{'||a.segr_vinculo||'}' 
                                                                  END::SMALLINT[])
                                    FROM admin.usuario usua
                                    WHERE usua_id=".getSession('sis_userid')
                                    .")"
                                    );
        }

	function whereLabortorioDiagImgenes(){
		$this->addWhere("b.tabl_descripaux IN ('laboratorio','radiografia','ecografia')");
        }
        
	function orderUno(){
		$this->addOrder("a.segr_id,
                                 a.segr_descripcion");	
	}
        
        function orderDos(){
		$this->addOrder("a.segr_descripcion");	
	}

	//metodo que devuelve el sql de los tipos de documentos segun id abreviado
	function getSQL_tranModulo($modulo){
		$this->whereModulo("$modulo");
		$this->orderUno();
		$sql=$this->getSQL();
		return ($sql);		
	}

        function getSQL_cbox(){
                $sql="SELECT a.segr_id,
                             a.segr_descripcion
                            FROM (".$this->getSQL().") AS a
                            WHERE a.segr_vinculo IN (SELECT UNNEST(CASE  WHEN usua.usua_ventas_acceso IS NOT NULL THEN
                                                                                        '{'||usua.usua_ventas_acceso||'}' 
                                                                        ELSE '{'||a.segr_vinculo||'}' 
                                                                  END::SMALLINT[])
                                    FROM admin.usuario usua
                                    WHERE usua_id=".getSession('sis_userid')
                                    .")                                
                            ORDER BY 1";
		return $sql;
	}
        
	function getSQL_servicioGrupo($tipo){
		$sql="SELECT a.segr_id,
                             a.segr_descripcion 
			FROM (".$this->getSQL().") AS a 
                        WHERE COALESCE(segr_tipo,'') ILIKE '%$tipo%'                                        
                        ORDER BY 2 ";
		return $sql;
	}


} // End class



class serviciosActualizaPrecios_SQLlista extends selectSQL {
	function __construct(){
		$this->sql="SELECT aa.sepr_id,
                                a.depe_id,
				a.segr_id,
                                a.sesg_id,
                                a.espe_id,
                                LPAD(a.serv_codigo::TEXT,5,'0') AS serv_id,
				a.serv_codigo,
				a.serv_descripcion,
                                a.serv_breve,
                                aa.sepr_precio AS precio,
                                a.serv_preciofraccion,
				a.serv_estado,
                                a.serv_umedida,
                                a.emru_id,
                                a.tabl_tipoprecio,
				b.segr_descripcion as grupo, 
				e.sesg_descripcion as sgrupo,
                                e.sesg_descripbreve as sgrupo_breve,
                                a.tabl_tipoconcepto,
                                a.serv_fregistro,
                                a.serv_actualfecha,
                                w.depe_ruc AS emru_ruc,                                
                                x.usua_login as username,
                                y.usua_login as username_actual
                            FROM catalogos.servicio_precios aa
                            LEFT JOIN catalogos.servicio a ON a.serv_codigo=aa.serv_codigo
                            LEFT JOIN catalogos.servicio_grupo b on a.segr_id=b.segr_id				
			    LEFT JOIN catalogos.servicio_sgrupo e on a.sesg_id=e.sesg_id								
                
                            LEFT JOIN catalogos.dependencia w ON  a.depe_id=w.depe_id 
                            LEFT JOIN admin.usuario x ON  a.usua_id=x.usua_id 
                            LEFT JOIN admin.usuario y ON  a.serv_actualusua=y.usua_id ";
	}

        
	function whereID($id){
                $this->addWhere("a.serv_codigo=$id");                                
		//$this->addWhere(sprintf("a.serv_codigo='%s'",$id));	
	}

        function whereDepeID($depe_id) {
                $this->addWhere(sprintf("a.depe_id='%s'", $depe_id));
        }
        
        function whereDepeTodos($depe_id) {
            $this->addWhere("a.depe_id IN (SELECT depe_id
                                                FROM catalogos.func_treedependencia2($depe_id)
                                                WHERE depe_id>0)");
        }
        
        function whereDepeTodos2($depe_id) {
            $this->addWhere("a.depe_id IS NULL OR a.depe_id IN (SELECT depe_id
                                                FROM catalogos.func_treedependencia2($depe_id)
                                                WHERE depe_id>0)");
        }
        
	function whereIDVarios($id_varios){
                $this->addWhere("a.serv_codigo IN ($id_varios)");
		//$this->addWhere(sprintf("a.serv_codigo='%s'",$id));	
	}
        
        
        function whereRUCID($emru_id){
		$this->addWhere(sprintf("a.emru_id='%s'",$emru_id));	
	}       
        
        function whereTipoPrecio($tabl_tipoprecio){
		$this->addWhere(sprintf("aa.tabl_tipoprecio='%s'",$tabl_tipoprecio));
	}
        
        function whereGrupo($segr_id){
		$this->addWhere(sprintf("a.segr_id='%s'",$segr_id));
	}
        
        function whereActivo(){
		$this->addWhere(sprintf("a.serv_estado='%s'",true));	
	}                

        function whereAcceso(){
                $this->addWhere("b.segr_vinculo IN (SELECT UNNEST(CASE  WHEN usua.usua_ventas_acceso IS NOT NULL THEN
                                                                                        '{'||usua.usua_ventas_acceso||'}' 
                                                                        ELSE '{'||b.segr_vinculo||'}' 
                                                                  END::SMALLINT[])
                                    FROM admin.usuario usua
                                    WHERE usua_id=".getSession('sis_userid')
                                    .")"
                                    );
        }
        
        function whereGrupoID($grupo_id){
		if($grupo_id) $this->addWhere("a.segr_id=$grupo_id");	
	}       
        
        function whereSubGrupoID($sgrupo_id){
		if($sgrupo_id) $this->addWhere("a.sesg_id=$sgrupo_id");	
	}               

        
	function whereDescrip($cadena){
            if($cadena){
                $array=explode(" ",$cadena);
                $lista='';
                for($i=0; $i<count($array); $i++){
                    $lista.=$lista?" AND ":"";
                    $lista.="LPAD(a.serv_codigo::TEXT,4,'0')||
                                    ' '||b.segr_descripcion||
                                    ' '||e.sesg_descripcion||
                                    ' '||COALESCE(a.clas_id,'')|| 
                                    ' '||a.serv_descripcion ILIKE '%".$array[$i]."%'";
                }
                $this->addWhere("($lista)");
            }
        }

        
	function orderUno(){
		$this->addOrder("a.serv_codigo DESC");		
	}	
                
}


if( isset($_GET['control']) ){
	
	$control=base64_decode($_GET['control']);
	if($control){
		include("../../library/library.php");
		/*	verificacion a nivel de usuario */
		verificaUsuario(1);
		verif_framework();
		
		$param= new manUrlv1();	
		$param->removePar('control');
		$param->removePar('relacionamento_id');
		$param->removePar('pg'); /* Remuevo el parametro */

		/* Recibo la p�gina actual de la lista y lo agrego como par�metro a ser enviado por la URL.  Este dato viene por POST */
		$pg = getParam("pg");	
		$param->addParComplete('pg',$pg); /* Agrego el par�metro */		
		
		//	conexi�n a la BD 
		$conn = new db();
		$conn->open();

		$dml=new servicios();

		switch($control){
                        case 1: // Guardar
                            $dml->guardar();
                            break;
                     
//                        case 10: // Guardar
//                            $dml->guardar2();   
//                            break;
		}
		//	cierra la conexi�n con la BD
		$conn->close();
	}
}