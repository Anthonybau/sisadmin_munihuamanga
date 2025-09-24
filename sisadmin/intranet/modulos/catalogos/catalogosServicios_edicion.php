<?php
/*
	formulario de ingreso y modificacion
*/
include("../../library/library.php");
include("catalogosServicios_class.php");
//include("../admin/datosEmpresaRUC_class.php");
include("../catalogos/catalogosServiciosPrecios_class.php");
include("../siscopp/siscoppAperturasAcumulados_clases.php");
include("../planillas/TipoPlanilla_class.php");
include("./catalogosTabla_class.php");
include("../catalogos/catalogosDependencias_class.php");


/*
	verificacion del nivel de usuarioprecio:
 * 
*/
verificaUsuario(1);

/*
	establecer conexion con la BD
*/
$conn = new db();
$conn->open();

/* Recibo los parametros con la clase de "paso de parametros por la URL"  */
$param= new manUrlv1();
$param->removePar('clear');

$myClass = new servicios ($id,"Edici&oacute;n de Bien/Servicio");

/*
	tratamiento de campos
*/
$id = getParam("id"); // captura la variable que viene del objeto lista

$id_clonar = getParam("id_clonar");
$id_clonar=$id_clonar?$id_clonar:0;

$clear = getParam("clear");
$busEmpty=getParam("busEmpty"); //permite o no buscar cadenas vacias (muestra todo los registros)
$numForm = getParam("numForm")?getParam("numForm"):0;//funciona solo con CLEAR=2, es el numero de formulario en el cual se encuentra el objeto desde donde fue llamado
$nBusc_grupo_id=getParam("nBusc_grupo_id"); 
$tipo=getParam("tipo"); 

if (strlen($id)>0 || $id_clonar>0) { // edicion

	$sql=new servicios_SQLlista();
        
        if($id){
            $sql->whereID($id);
        }else{
            $sql->whereID($id_clonar);
        }
        
        $sql=$sql->getSQL();
        
	$rs = new query($conn, $sql);
	if ($rs->getrow()) {
                if($id){
                    $bd_serv_id=$rs->field("serv_id");		
                    $bd_serv_codigo = $rs->field("serv_codigo");
                }
                
                $bd_depe_id = $rs->field("depe_id");
		$bd_segr_id = $rs->field("segr_id");
		$bd_sesg_id = $rs->field("sesg_id");
		$bd_serv_descripcion = $rs->field("serv_descripcion");
                $bd_serv_breve = $rs->field("serv_breve");
                $bd_serv_periodicidad = $rs->field("serv_periodicidad");
                $bd_tipl_id = $rs->field("tipl_id");
                $bd_tabl_vinculado = $rs->field("tabl_vinculado");
                $bd_tabl_tipoconcepto = $rs->field("tabl_tipoconcepto");
                $bd_serv_porcentaje=$rs->field("serv_porcentaje");
                $bd_serv_formula=$rs->field("serv_formula");
                $bd_serv_plame=$rs->field("serv_plame");

                $bd_serv_pensionable=$rs->field("serv_pensionable");
                $bd_serv_essalud=$rs->field("serv_essalud");
                $bd_serv_conafovicer=$rs->field("serv_conafovicer");
                $bd_serv_sctr=$rs->field("serv_sctr");
                $bd_serv_cts=$rs->field("serv_cts");
                $bd_serv_editable=$rs->field("serv_editable");
                $bd_serv_ir5ta=$rs->field("serv_ir5ta");
                $bd_serv_ir5ta_meses_proyecta=$rs->field("serv_ir5ta_meses_proyecta");
                $bd_serv_formula2=$rs->field("serv_formula2");
                $bd_serv_descripcion_cts=$rs->field("serv_descripcion_cts");
                $bd_serv_automatico=$rs->field("serv_automatico");
                $bd_serv_adjunto_id=$rs->field("serv_adjunto_id");
                        
		$bd_espe_id= $rs->field("espe_id");		
		$bd_exam_id= $rs->field("exam_id");				
                $bd_tabl_tipoprecio_dependencia=$rs->field("tabl_tipoprecio_dependencia");
                $bd_serv_precio = $rs->field("serv_precio");
                $bd_serv_preciofraccion = $rs->field("serv_preciofraccion");
                $bd_serv_preciofraccion = $bd_serv_preciofraccion?$bd_serv_preciofraccion:0;
		$bd_serv_estado = $rs->field("serv_estado2")==1?1:0;
                $bd_segr_vinculo= $rs->field("segr_vinculo");
		$bd_serv_estadopaciente = $rs->field("serv_estadopaciente");
                $bd_tabl_tipo_componente=$rs->field("tabl_tipo_componente");
                $bd_tabl_subtipo_componente=$rs->field("tabl_subtipo_componente");
                $bd_tabl_fila_componente=$rs->field("tabl_fila_componente");
                $bd_serv_sisteso=$rs->field("serv_sisteso");
                
                $bd_codigo_ant=$rs->field("codigo_ant");
                        
		$bd_clas_id = $rs->field("clas_id");
                $bd_emru_id= $rs->field("emru_id");
                $bd_serv_codigo_aux= $rs->field("serv_codigo_aux");
                        
                $bd_serv_umedida=$rs->field("serv_umedida");
                $bd_tabl_tipo_igv=$rs->field("tabl_tipo_igv");
                $bd_serv_aplica_ajuste=$rs->field("serv_aplica_ajuste");
                $bd_serv_muestra_min=$rs->field("serv_muestra_min");
                $destino=$rs->field("segr_destino");
                
                $bd_serv_porcen_utilidad=$rs->field("serv_porcen_utilidad");
                $bd_serv_equi_unidades=$rs->field("serv_equi_unidades");
                $bd_serv_stockminimo=$rs->field("serv_stockminimo");
                $bd_serv_preciocosto=$rs->field("serv_preciocosto");
                $comp_id=$rs->field("comp_id");
                $bd_serv_porcent_detraccion=$rs->field("serv_porcent_detraccion");
                $bd_serv_porcent_convenio=$rs->field("serv_porcent_convenio");
                $bd_serv_genera_contabilidad=$rs->field("serv_genera_contabilidad");
                $bd_tabl_farmacia_laboratorio=$rs->field("tabl_farmacia_laboratorio");
                $bd_tabl_marca=$rs->field("tabl_marca");
                $bd_serv_gratuito=$rs->field("serv_gratuito");
                $bd_serv_principio_reactivo=$rs->field("serv_principio_reactivo");
                $bd_serv_accion_farmacologica=$rs->field("serv_accion_farmacologica");
                $bd_serv_observaciones=$rs->field("serv_observaciones");
                $bd_tabl_ubicacion=$rs->field("tabl_ubicacion");
                $bd_tabl_umedida=$rs->field("tabl_umedida");
                $bd_serv_codigo_barras=$rs->field("serv_codigo_barras");
                $bd_serv_codigo_interoperabilidad=$rs->field("serv_codigo_interoperabilidad");
                
		$bd_usua_id = $rs->field("usua_id");		
                $nameUsers = $rs->field("username");
                $nameUsersActual = $rs->field("username_actual");
                
                $fregistro=$rs->field("serv_fregistro");
                $fregistroActual=$rs->field("serv_actualfecha");
        }
}
else {
    $bd_serv_estado = 1;
    $bd_tabl_tipo_igv=1;

    if(getSession("SET_DEPE_EMISOR")){
        $bd_depe_id=getSession("SET_DEPE_EMISOR");
    }else{
        $bd_depe_id=getSession("sis_depe_superior");
    }
    
//    $sqlRUC=new clsEmpresaRUC_SQLlista();
//    if(getSession('SET_EMRU_EMISOR')){
//        $sqlRUC->whereID(getSession('SET_EMRU_EMISOR'));
//    }
//    $sqlRUC->orderUno();
//    $sql=$sqlRUC->getSQL_cbox();
//    
//    $bd_emru_id= getDbValue("SELECT emru_id FROM (".$sqlRUC->getSQL().") AS a LIMIT 1");    
    
    //$bd_serv_equi_unidades=1;
    $bd_serv_stockminimo=1;
    $bd_serv_preciocosto=0;    
    $bd_serv_porcent_detraccion=0;
    $bd_serv_equi_unidades=1;
    $bd_serv_preciofraccion=0;
    $bd_serv_porcent_convenio=0;
        
}    

require_once("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("getVinculo"); 
$xajax->registerFunction("getVinculo_otros"); 
$xajax->registerFunction("poneDescrip"); 
$xajax->registerFunction("getTipoPrecio");
$xajax->registerFunction("getSubtipoComponente"); 
$xajax->registerFunction("eligeTipoPrecio"); 
$xajax->registerFunction("pideTipoConcepto"); 
$xajax->registerFunction("pideMesesProyecta"); 
//$xajax->registerFunction("pideFormula2"); 
$xajax->registerFunction("ponePrecioFraccion"); 
        
//funcion que obliga a ingresar la especialidad
function getVinculo($op,$grupo,$bd_depe_id,$NameDiv){
				
	global $conn,$bd_espe_id,$bd_exam_id,$bd_sesg_id,$bd_tabl_tipo_componente,$bd_emru_id,
               $bd_tabl_farmacia_laboratorio,$bd_serv_principio_reactivo,$bd_serv_accion_farmacologica,$bd_tabl_marca;

	$objResponse = new xajaxResponse();

	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");
	
        
	$sqlSGrupo="SELECT sesg_id,sesg_descripcion FROM servicio_sgrupo WHERE segr_id=$grupo order by 2" ;	
	$oForm->addField("Sub Grupo: <font color=red>*</font>",listboxField("Sub Grupo",$sqlSGrupo,"nr_sesg_id",$bd_sesg_id,'-- Seleccione Sub Grupo --', "", "","class=\"my_select_box\""));

//        $sqlGrupo=new clsGrupoTTra_SQLlista();
//        $sqlGrupo->whereID($grupo);
//        $sqlGrupo->setDatos();        
//        $destino=$sqlGrupo->field("segr_destino");
        
//        if(strpos($segr_tipo,'T')>=0 && strpos($segr_tipo,'T')!== false){
//        if( inlist($destino,'1,2,3') ){
//            $sqlRUC=new clsEmpresaRUC_SQLlista();
//            if(getSession('SET_EMRU_EMISOR')){
//                $sqlRUC->whereID(getSession('SET_EMRU_EMISOR'));
//            }
//            $sqlRUC->orderUno();
//            $sqlRUC=$sqlRUC->getSQL_cbox();
//            $oForm->addField("RUC: ",listboxField("RUC",$sqlRUC,"nr_emru_id","$bd_emru_id"));
//        }
        
        
	$vinculo=getDbValue("SELECT segr_vinculo FROM catalogos.servicio_grupo WHERE segr_id=$grupo ");
        if($vinculo==1){ //vinculado a Productos con marca
                $sqlLaborat="SELECT tabl_id,tabl_descripcion FROM catalogos.tabla WHERE tabl_tipo='MARCA_PRODUCTO' ORDER BY 2";
                $oForm->addField("Marca: <font color=red>*</font>",listboxField("Marca",$sqlLaborat,"tx_tabl_marca",$bd_tabl_marca,"-- Ninguno --","", "","class=\"my_select_box\""));
        }elseif(inlist($vinculo,'2')){ //vinculado a especialidades, ecografias, endoscopias
		$sqlEspe="SELECT espe_id,espe_descripcion FROM gestmed.especialidad WHERE espe_estado  ORDER BY 2";
		$oForm->addField("Especialidad: <font color=red>*</font>",listboxField("Especialidad",$sqlEspe,"tr_espe_id",$bd_espe_id,"-- Seleccione Especialidad --","onChange=xajax_poneDescrip(this.value,1)", "","class=\"my_select_box\""));
	}elseif(inlist($vinculo,'7,10,11')){ //vinculado a especialidades, ecografias, endoscopias
		$sqlEspe="SELECT espe_id,espe_descripcion FROM gestmed.especialidad WHERE espe_estado  ORDER BY 2";
		$oForm->addField("Especialidad: <font color=red>*</font>",listboxField("Especialidad",$sqlEspe,"tr_espe_id",$bd_espe_id,"-- Seleccione Especialidad --","", "","class=\"my_select_box\""));
	}elseif($vinculo==3){ //vinculado a Farmacia
                $sqlLaborat="SELECT tabl_id,tabl_descripcion FROM catalogos.tabla WHERE tabl_tipo='LABORATORIO_MEDICAMENTO' ORDER BY 2";
                $oForm->addField("Laboratorio: ",listboxField("Laboratorio",$sqlLaborat,"tx_tabl_farmacia_laboratorio",$bd_tabl_farmacia_laboratorio,"-- Ninguno --","", "","class=\"my_select_box\""));
                
                //if( inlist(SIS_EMPRESA_RUC,'20601435277') ){//DROGUERIA AMEC PHARMA
                    //$oForm->addField("Principio Activo: ",textField("Principio Activo","Sx_serv_principio_reactivo",$bd_serv_principio_reactivo,80,150));                                                
                //}
                
        }elseif($vinculo==4){ //vinculado a componentes
                $sqltipoComp="SELECT tabl_codigo,tabl_descripcion FROM catalogos.tabla WHERE tabl_tipo='TIPO_COMPONENTE' ORDER BY 1";
                $oForm->addField("Tipo Componente: ",listboxField("Tipo Componente",$sqltipoComp,"tx_tabl_tipo_componente",$bd_tabl_tipo_componente,"-- Ninguno --","onChange=\"xajax_getSubtipoComponente(1,this.value,'divSubtipoComponente')\"", "","class=\"my_select_box\""));
        }else{
            $oForm->addHidden("___espe_id",NULL);
            $oForm->addHidden("___tabl_tipo_componente",NULL);
	}

        if(inlist($vinculo,'6,7,8,10,11')){ //vinculado a perfiles
            if($vinculo=='6'){//laboratorio
                $sqlPerfil="SELECT exam_id,LPAD(exam_id::TEXT,4,'0')||' '||exam_nombre FROM gestmed.examen WHERE exam_estado='1' AND exam_tipo_estudio='laboratorio' ORDER BY exam_nombre";
            }elseif($vinculo=='7'){//ecografia
                $sqlPerfil="SELECT exam_id,LPAD(exam_id::TEXT,4,'0')||' '||exam_nombre FROM gestmed.examen WHERE exam_estado='1' AND exam_tipo_estudio='ecografia' ORDER BY exam_nombre";
            }elseif($vinculo=='8'){//radiografia
                $sqlPerfil="SELECT exam_id,LPAD(exam_id::TEXT,4,'0')||' '||exam_nombre FROM gestmed.examen WHERE exam_estado='1' AND exam_tipo_estudio='radiografia' ORDER BY exam_nombre";
            }elseif($vinculo=='10'){//endoscopia
                $sqlPerfil="SELECT exam_id,LPAD(exam_id::TEXT,4,'0')||' '||exam_nombre FROM gestmed.examen WHERE exam_estado='1' AND exam_tipo_estudio='endoscopia' ORDER BY exam_nombre";
            }else{//patologia
                $sqlPerfil="SELECT exam_id,LPAD(exam_id::TEXT,4,'0')||' '||exam_nombre FROM gestmed.examen WHERE exam_estado='1' AND exam_tipo_estudio='patologia' ORDER BY exam_nombre";
            }
            $oForm->addField("Perfil: ",listboxField("Perfil",$sqlPerfil,"tx_exam_id",$bd_exam_id,"-- Seleccione Perfil --","onChange=xajax_poneDescrip(this.value,2)", "","class=\"my_select_box\"")); 
        } 

        
        $contenido_respuesta=$oForm->writeHTML();
        
	if($op==1){
		$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);	
                $objResponse->addScript("xajax_getVinculo_otros(1,'$grupo','$bd_depe_id','divDestino')");

                $objResponse->addScript("$('.my_select_box').select2({
                                        placeholder: 'Seleccione un elemento de la lista',
                                        allowClear: true,
                                        width: '90%',
                                    });");
                
		return $objResponse;
	
	}else{
		return $contenido_respuesta	;
	}

}


function getVinculo_otros($op,$grupo,$bd_depe_id,$NameDiv){
				
	global $conn,$bd_serv_umedida,$bd_serv_precio,$bd_serv_codigo,
                $bd_serv_codigo_aux,$bd_tabl_tipo_igv,
                $bd_serv_aplica_ajuste,$bd_clas_id,$bd_serv_equi_unidades,
                $bd_serv_stockminimo,$bd_serv_preciocosto,$bd_tabl_tipoprecio,
                $bd_serv_porcen_utilidad,$comp_id,$bd_serv_porcent_detraccion,
                $bd_serv_breve,$bd_serv_periodicidad,$bd_serv_adjunto_id,$bd_tipl_id,
                $bd_tabl_vinculado,$bd_tabl_tipoconcepto,$bd_segr_id,$bd_serv_automatico,
                $bd_serv_plame,$bd_serv_preciofraccion,$bd_serv_porcent_convenio,$bd_serv_gratuito,
                $bd_tabl_tipoprecio_dependencia,$bd_serv_principio_reactivo,$bd_serv_accion_farmacologica,
                $bd_tabl_umedida,$bd_tabl_ubicacion,$bd_serv_codigo_barras;

	$objResponse = new xajaxResponse();

	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");

        $vinculo=getDbValue("SELECT segr_vinculo FROM catalogos.servicio_grupo WHERE segr_id=$grupo ");
        if($vinculo==3){ //vinculado a Farmacia
            $oForm->addField("Principio Activo: ",textField("Principio Activo","Sx_serv_principio_reactivo",$bd_serv_principio_reactivo,80,150));
            $oForm->addField("Acci&oacute;n Farmacol&oacute;gica: ",textField("Accion Farmacologica","Sx_serv_accion_farmacologica",$bd_serv_accion_farmacologica,80,150));
            
        }
        
        $sqlGrupo=new clsGrupoTTra_SQLlista();
        $sqlGrupo->whereID($grupo);
        $sqlGrupo->setDatos();        
        $destino=$sqlGrupo->field("segr_destino");
        $segr_almacen=$sqlGrupo->field("segr_almacen");
        $segr_tipo=$sqlGrupo->field("segr_tipo");

        if(strpos($segr_tipo,'H')>=0 && strpos($segr_tipo,'H')!== false){ //Haberes

            $oForm->addField("Breve: ",textField("Breve","Sr_serv_breve",$bd_serv_breve,45,40));

            $lista = array("1,PERMANENTE","2,UNICA VEZ");         
            $bd_serv_periodicidad=$bd_serv_periodicidad?$bd_serv_periodicidad:1;
            $oForm->addField("Periodicidad: <font color=red>*</font>",radioField("Periodicidad",$lista, "tr_serv_periodicidad","$bd_serv_periodicidad","",'H'));

            $sqlConceptos=new servicios_SQLlista();
            $sqlConceptos->whereTipo('H');
            $sqlConceptos->whereNOautomatico();        
            $sqlConceptos->whereActivo();
            $sqlConceptos=$sqlConceptos->getSQL_servicio();
            $oForm->addField("Concepto Adjunto: ",listboxField("Concepto Adjunto",$sqlConceptos,"tx_serv_adjunto_id",$bd_serv_adjunto_id,"-- Seleccione Concepto Adjunto--","", "","class=\"my_select_box\""));    

            $tabla=new tipoPlanilla_SQLlista();
            $tabla->whereActivo();
            $tabla->orderUno();        
            $sqlTipoPlla=$tabla->getSQL_cbox();        
            $oForm->addField("Tipo de Planilla:",listboxField("Tipo de Planilla",$sqlTipoPlla,"tx_tipl_id",$bd_tipl_id,"-- Todas las Planillas --","onChange=\"xajax_pideClasificador(1,this.value,'divClasificador');\"", "","class=\"my_select_box\""));    

            $tabla=new clsTabla_SQLlista();
            $tabla->whereTipo('VINCULO_CONCEPTO');
            $tabla->orderUno();        
            $sqlVinculado=$tabla->getSQL_cbox();
            $oForm->addField("Vinculado: <font color=red>*</font>",listboxField("Vinculado",$sqlVinculado,"tr_tabl_vinculado",$bd_tabl_vinculado,"-- Seleccione Vinculo --", "", "","class=\"my_select_box\""));

            //$sqlTConcep="select tabl_id,tabl_descripcion as descripcion from tabla where tabl_tipo='TIPO_CONCEPTO' order by 1";        	
            $tabla=new clsTabla_SQLlista();
            $tabla->whereTipo('TIPO_CONCEPTO');
            $tabla->orderUno();        
            $sqlTConcep=$tabla->getSQL_cbox();
            $oForm->addField("Tipo: <font color=red>*</font>",listboxField("Tipo ",$sqlTConcep,"tr_tabl_tipoconcepto",$bd_tabl_tipoconcepto,"-- Seleccione Tipo --","onChange=\"xajax_pideTipoConcepto(1,'divTipoConcepto',this.value,document.frm.nr_segr_id.value);\"", "","class=\"my_select_box\""));

            $oForm->addHtml("<tr><td colspan=2><div id='divTipoConcepto'>\n");
            $bd_segr_id=$bd_segr_id?$bd_segr_id:$grupo;
            $oForm->addHtml(pideTipoConcepto(2,'divTipoConcepto',$bd_tabl_tipoconcepto,$bd_segr_id));
            $oForm->addHtml("</div></td></tr>\n");

            $oForm->addBreak("TIPO DE CALCULO");    
            $oForm->addField("Inserci&oacute;n Autom&aacute;tica a Plla.: ",checkboxField("Insercion Automatica a Plla.","hx_serv_automatico",1,$bd_serv_automatico==1));
                    //$form->addField("Hacer Editable (No aplica Ing/Autom/Formulas): ",checkboxField("Hacer Editable","hx_conc_editable",1,$bd_conc_editable==1));                

            $oForm->addField("C&oacute;d.PLAME:",numField("Cod.PLAME","zx_serv_plame",$bd_serv_plame,4,4,0,false));       
        }
        
        if(inlist($destino,'1,3')) {//INGRESOS
            
            if(inlist(SIS_EFACT,'0,1') || SIS_EMPRESA_TIPO==4){//Empresa tipo Almacen
                
                $tabla=new clsTabla_SQLlista();
                $tabla->whereTipo('UNIDAD_MEDIDA');
                $tabla->orderUno();        
                $sqlUnidades=$tabla->getSQL_cbox();
                $oForm->addField("U.Medida: <font color=red>*</font>",listboxField("U.Medida",$sqlUnidades,"tr_tabl_umedida",$bd_tabl_umedida,"-- Seleccione Unidad de Medida --","", "","class=\"my_select_box\""));
                $oForm->addField("Presentaci&oacute;n: <font color=red>*</font>",textField("Presentacion","Sr_serv_umedida",$bd_serv_umedida,20,20));
            }
        

            $oForm->addHtml("<tr><td colspan=2><div id='getTipoPrecio'>\n");
            $oForm->addHtml(getTipoPrecio(2,$bd_depe_id,$grupo,'getTipoPrecio'));
            $oForm->addHtml("</div></td></tr>\n");
            
            //$oForm->addField("Precio: ",numField("Precio","nr_serv_precio",$bd_serv_precio,15,10,2,true));
            
             if(inlist(SIS_EFACT,'0,1')){
                $sql = "SELECT tabl_codigo, tabl_descripcion FROM tabla WHERE tabl_tipo='TIPO_IGV' ORDER BY 1";
                $rs = new query($conn, $sql);
                while ($rs->getrow()) {
                    $lista_tipo_igv[].=$rs->field("tabl_codigo").",".  $rs->field("tabl_descripcion");
                }   

                $oForm->addField("Tipo IGV: <font color=red>*</font>",radioField("Tipo IGV",$lista_tipo_igv, "xr_tabl_tipo_igv",$bd_tabl_tipo_igv,"","H"));        
                //$oForm->addField("Permitir Descuento: ",checkboxField("Permitir Descuento","hx_serv_aplica_ajuste",1,$bd_serv_aplica_ajuste==1));
             }
             
            if(inlist(SIS_EMPRESA_TIPO,'1,4')){//Privada
                $oForm->addField("C&oacute;digo Auxiliar: ",textField("Cod.Auxiliar","Sx_serv_codigo_aux",$bd_serv_codigo_aux,20,20));            
            }
            
            $sqlEspecifica="SELECT clas_id,clas_id||' '||clas_descripcion
                                            FROM clasificador
                                            WHERE clas_tipo='1' AND clas_especifica=1  
                                            ORDER BY clas_id  ";
            
        }else{
            $sqlEspecifica="SELECT clas_id,clas_id||' '||clas_descripcion
                                            FROM clasificador
                                            WHERE clas_tipo='2' AND clas_especifica=1  
                                            ORDER BY clas_id  ";

//            if($destino==2) {//egresos
//                $oForm->addHidden("___tabl_tipoprecio",10);
//            }
            $oForm->addHtml("<tr><td colspan=2><div id='getTipoPrecio'>\n");
            $oForm->addHtml(getTipoPrecio(2,$bd_depe_id,$grupo,'getTipoPrecio'));
            $oForm->addHtml("</div></td></tr>\n");
            
        }
        
        
        if(inlist(SIS_EMPRESA_TIPO,'2,3') && inlist($destino,'1,2,3')){//empresas publicas, beneficencias, destino ingresos/egresos
            
            $oForm->addField("Clasificador: <font color=red>*</font>",listboxField("Clasificador",$sqlEspecifica,"tr_clas_id",$bd_clas_id,"-- Seleccione Espec&iacute;fica --","", "","class=\"my_select_box\""));
            
            if(inlist($destino,'1,3')){//INGRESOS
                $sqlComponente=new clsComponente_SQLlista();
                $sqlComponente=$sqlComponente->getSQL_componente();
                $oForm->addField("Componente: <font color=red>*</font>",listboxField("Componente:",$sqlComponente,"tr_comp_id",$comp_id,"-- Seleccione Componente --","","","class=\"my_select_box\""));        
            }
        }

        if(inlist($destino,'1,3')) {//INGRESOS
            
            $oForm->addField("Equivalencia en Unidades: <font color=red>*</font>",numField("Equivalencia en Unidades","nr_serv_equi_unidades",$bd_serv_equi_unidades,10,10,3,false,"onChange=xajax_ponePrecioFraccion(document.frm.nr_serv_precio.value,document.frm.nr_serv_preciofraccion.value,this.value)"));        
            
            if (SIS_EFACT==1){
                $oForm->addField("Precio Fracci&oacute;n: <font color=red>*</font>",numField("Precio Fraccion","nr_serv_preciofraccion",$bd_serv_preciofraccion,15,10,2,false));        
                $oForm->addField("% Detracci&oacute;n: ",numField("% Detraccion","nx_serv_porcent_detraccion","$bd_serv_porcent_detraccion",6,6,0,false,""));
                $oForm->addField("% Convenio: ",numField("% Convenio","nx_serv_porcent_convenio","$bd_serv_porcent_convenio",10,9,6,false,""));
                $oForm->addField("Establecer como Gratuito: ",checkboxField("Establecer como Gratuito","hx_serv_gratuito",1,$bd_serv_gratuito==1));
            }
        }
        
        if($segr_almacen==1){
            $oForm->addField("Stock M&iacute;nimo: ",numField("Stock Mínimo","nx_serv_stockminimo",$bd_serv_stockminimo,6,6,2));
            
            if(SIS_EMPRESA_TIPO!=4){
                $oForm->addField("Precio costo: ",numField("Precio costo","nx_serv_preciocosto",$bd_serv_preciocosto,14,14,2));
            }
            
        }
        
        if(inlist($destino,'1,3')) {//INGRESOS
           $oForm->addField("C&oacute;d. de Barras: ",textField("Cod. de Barras","Sx_serv_codigo_barras",$bd_serv_codigo_barras,30,30));             
        }
        
        if(inlist($destino,'1,3') && SIS_PIDE_UBICACION_PRODUCTO==1){
            $tabla=new clsTabla_SQLlista();
            $tabla->whereTipo('LUGAR_UBICACION_PRODUCTO');
            $tabla->orderUno();        
            $sqlVinculado=$tabla->getSQL_cbox();
            $oForm->addField("Ubicaci&oacute;n: <font color=red>*</font>",listboxField("Ubicacion",$sqlVinculado,"tr_tabl_ubicacion",$bd_tabl_ubicacion,"-- Seleccione Ubicaci&oacute;n --","", "","class=\"my_select_box\""));
        }

        $contenido_respuesta=$oForm->writeHTML();
        
	if($op==1){
		$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);	
                $objResponse->addScript("$('.my_select_box').select2({
                                        placeholder: 'Seleccione un elemento de la lista',
                                        allowClear: true,
                                        width: '90%',
                                    });");
        
		return $objResponse;
	
	}else{
		return $contenido_respuesta	;
	}

}

//funcion que obliga a ingresar la especialidad
function getTipoPrecio($op,$bd_depe_id,$grupo,$NameDiv){
				
	global $conn,$bd_tabl_tipoprecio_dependencia,$bd_serv_codigo,$segr_almacen;

	$objResponse = new xajaxResponse();

	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");

        $sqlGrupo=new clsGrupoTTra_SQLlista();
        $sqlGrupo->whereID($grupo);
        $sqlGrupo->setDatos();        
        $destino=$sqlGrupo->field("segr_destino");
	
            if(inlist($destino,'1,3') && SIS_EMPRESA_TIPO!=4) {//INGRESOS && ALMACENES
            
        
                $depe_idx=$bd_depe_id?$bd_depe_id:getSession('sis_depe_superior');

                $sqltipoPrecio="SELECT  a.tabl_id::TEXT||'_'||b.depe_id::TEXT AS id,
                                        a.tabl_descripcion||'/'||LPAD(b.depe_id::TEXT,3,'0')||' '||b.depe_nombrecorto AS descripcion
                                        FROM catalogos.tabla a, catalogos.dependencia b 
                                        WHERE a.tabl_tipo='TIPO_PRECIO' 
                                                AND a.tabl_porcent IS NULL 
                                              AND b.depe_id IN (SELECT depe_id
                                                                        FROM catalogos.func_treedependencia2($depe_idx)
                                                                        WHERE depe_superior=1
                                                                        )
                                        ORDER BY 1";
                
                if($op==1){
                    $bd_tabl_tipoprecio_dependencia='10_'.$depe_idx;
                }
                 
                $oForm->addField("Tipo de Precio: <font color=red>*</font>",listboxField("Tipo Precio",$sqltipoPrecio,"tr_tabl_tipoprecio_dependencia",$bd_tabl_tipoprecio_dependencia,"","onChange=\"xajax_eligeTipoPrecio(1,this.value,'$bd_serv_codigo','divTipoPrecio')\""));

                $oForm->addHtml("<tr><td colspan=2><div id='divTipoPrecio'>\n");
                $oForm->addHtml(eligeTipoPrecio(2,$bd_tabl_tipoprecio_dependencia,$bd_serv_codigo,'divTipoPrecio'));
                $oForm->addHtml("</div></td></tr>\n");
                
            }else{
                $depe_idx=$bd_depe_id?$bd_depe_id:getSession('sis_depe_superior');
                
                $bd_tabl_tipoprecio_dependencia='10_'.$depe_idx;
                $oForm->addHidden("tr_tabl_tipoprecio_dependencia",$bd_tabl_tipoprecio_dependencia);
                
            }
        $contenido_respuesta=$oForm->writeHTML();
        
	if($op==1){
		$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);	
                
                $objResponse->addScript("$('.my_select_box').select2({
                                        placeholder: 'Seleccione un elemento de la lista',
                                        allowClear: true,
                                        width: '90%',
                                    });");
                
		return $objResponse;
	
	}else{
		return $contenido_respuesta	;
	}

}

//funci�n que filtra y muestra los datos segun el tipo de paciente
function poneDescrip($value,$op)
{
	global $conn;
	
	$objResponse = new xajaxResponse();
	if($op==1){//especialidad
		$descrip=GetDbValue("SELECT espe_descripcion FROM especialidad WHERE espe_id=$value ");		
        }else{
		$descrip=GetDbValue("SELECT exam_nombre FROM laboratorio.examen WHERE exam_id=$value ");	
        }
	$objResponse->addScript("document.frm.Sr_serv_descripcion.value=!document.frm.Sr_serv_descripcion.value?document.frm.Sr_serv_descripcion.value='".$descrip."':document.frm.Sr_serv_descripcion.value");	
    // Se devuelve el objeto, que este dara todas las instruccione JS para realizar la tarea
	return $objResponse;

}


//funcion que obliga a ingresar la especialidad
function getSubtipoComponente($op,$bd_tabl_tipo_componente,$NameDiv){
				
	global $conn,$bd_tabl_subtipo_componente,$bd_tabl_fila_componente;

	$objResponse = new xajaxResponse();

	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");
        if($bd_tabl_tipo_componente==1)//CUARTEL
            $nombreTipo='NICHO';
        else
            $nombreTipo=getDbValue("SELECT tabl_descripcion FROM tabla WHERE tabl_tipo='TIPO_COMPONENTE' AND tabl_codigo=$bd_tabl_tipo_componente");
	
        if($bd_tabl_tipo_componente==1 or $bd_tabl_tipo_componente==3){ //Cuartel/Tumba
		$sqlTipoNicho="SELECT tabl_codigo,tabl_descripcion FROM tabla WHERE tabl_tipo='SUBTIPO_COMPONENTE' AND tabl_descripcion ILIKE '%$nombreTipo%'  ORDER BY 1";
		$oForm->addField("Sub Tipo: ",listboxField("Sub Tipo Componente",$sqlTipoNicho,"tx_tabl_subtipo_componente",$bd_tabl_subtipo_componente,"-- Todos --", "", "","class=\"my_select_box\""));
	}

        if($bd_tabl_tipo_componente==1){//CUARTEL        
            $sqltipoNivel="SELECT tabl_codigo,tabl_descripcion FROM tabla WHERE tabl_tipo='NIVELES_COMPONENTE' ORDER BY 1";
            $oForm->addField("Fila",listboxField("Fila",$sqltipoNivel,"tx_tabl_fila_componente",$bd_tabl_fila_componente));
        }
        
        if($bd_tabl_tipo_componente==1 or $bd_tabl_tipo_componente==3){} //Cuartel        
	else{
            $oForm->addHidden("___tabl_subtipo_componente",NULL);
	}
        if($bd_tabl_tipo_componente==1){//CUARTEL        
        }else{
            $oForm->addHidden("___tabl_fila_componente",NULL);
        }	
        
	$contenido_respuesta=$oForm->writeHTML();

	if($op==1){
		$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);	
		return $objResponse;
	
	}else{
		return $contenido_respuesta	;
	}

}


//funcion que obliga a ingresar la especialidad
function eligeTipoPrecio($op,$bd_tabl_tipoprecio_dependencia,$serv_codigo,$NameDiv){
				
	global $conn,$bd_serv_preciofraccion,$bd_serv_precio;

	$objResponse = new xajaxResponse();

	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");
        
        $tipo_precio=explode("_",$bd_tabl_tipoprecio_dependencia);

        if($serv_codigo){
            $servicioPrecio=new serviciosPrecios_SQLlista();
            $servicioPrecio->whereCodServicio($serv_codigo);
            $servicioPrecio->whereTipoPrecio($tipo_precio[0]);
            $servicioPrecio->whereDepeID($tipo_precio[1]);
            $servicioPrecio->whereOrigen(0);
            $servicioPrecio->setDatos();
            $existen_datos=$servicioPrecio->existeDatos();
        }else{
            $existen_datos=0;
        }

        if($existen_datos){
            $bd_serv_precio=$servicioPrecio->field("sepr_precio");
            $bd_serv_porcen_utilidad=$servicioPrecio->field("sepr_porcen_utilidad");
                    
            $bd_sepr_actualfecha=stod($servicioPrecio->field("sepr_actualfecha"));
            $bd_semo_porcen_utilidad=$servicioPrecio->field("sepr_porcen_utilidad");
            $bd_semo_precioventax=$servicioPrecio->field("sepr_precio");
            $bd_semo_preciofraccionx=$servicioPrecio->field("sepr_preciofraccion");
            $oForm->addField("<font color=red>Valores Al $bd_sepr_actualfecha</font: ","<font color=red>Utilidad: $bd_semo_porcen_utilidad % / P.VENTA: $bd_semo_precioventax / P.FRACCION: $bd_semo_preciofraccionx</font>");
        }else{
            if(SIS_EFACT==0){
                $bd_serv_precio=0;
            }else{
                $bd_serv_precio='';
            }
            $bd_serv_porcen_utilidad='';
        }


       //if($segr_almacen==1){
            //$oForm->addField("% Utilidad (referencial): ",numField("% Utilidad","nX_serv_porcen_utilidad","$bd_serv_porcen_utilidad",6,6,0,false,""));
       //}
       
        if(inlist(SIS_EFACT,'0,1')){
            $oForm->addField("Precio Venta: <font color=red>*</font>",numField("Precio Venta","nr_serv_precio",$bd_serv_precio,15,10,2,false,"onChange=xajax_ponePrecioFraccion(this.value,document.frm.nr_serv_preciofraccion.value,document.frm.nr_serv_equi_unidades.value)"));        
        }
        
	$contenido_respuesta=$oForm->writeHTML();

	if($op==1){
            $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);
            $objResponse->addScript("xajax_ponePrecioFraccion(0,$bd_semo_preciofraccionx,0)");
            return $objResponse;
	}else{
            return $contenido_respuesta	;
	}

}

function pideTipoConcepto($op,$NameDiv,$tipo,$categoria)
{
        global $bd_serv_porcentaje,$bd_serv_pensionable,$bd_serv_conafovicer,
                $bd_serv_ir5ta,$bd_serv_precio,$bd_serv_formula,$bd_serv_sctr,
                $bd_serv_cts,$bd_serv_editable,$bd_serv_essalud;
                        
	$objResponse = new xajaxResponse();

	$otable = new AddTableForm();
	$otable->setLabelWidth("20%");
	$otable->setDataWidth("80%");

            // NOTA: Estos valores deben estar acorde con el Trigger creado en Dependencia "trig_set_valores_dependencia"
            switch($tipo){
               case 120: // Si se ha elegido el tipo de concepto 'Permanenete'
                    $otable->addHidden("nx_serv_porcentaje",NULL); 
                    $otable->addHidden("nx_serv_precio",0); 

                    break;                      

               case 121: // Si se ha elegido el tipo de concepto 'Variable'
                    $otable->addField("%: ",numField("%","nx_serv_porcentaje",$bd_serv_porcentaje,10,5,2));
                    $otable->addHidden("nx_serv_precio",0); 
                    //if($categoria==106 || $categoria==108){ /*descuento de ley/aportes*/
                        //$otable->addField("%: ",numField("%","nx_conc_porcentaje",$bd_conc_porcentaje,10,5,2));
                    //}
                    break;

               case 122: // Si se ha elegido el tipo de concepto 'Importe Manual'                   
                    $otable->addField("Importe: ",numField("Importe","nx_serv_precio",$bd_serv_precio,14,10,2));
                    $otable->addHidden("nx_serv_porcentaje",NULL); 
                    break;            
            }
            

                $otable->addField("Formula: ",textAreaField("Formula","ex_serv_formula",$bd_serv_formula,4,100,600).
                //$otable->addField("Formula:",textField("Formula","Sx_conc_formula",$bd_conc_formula,80,80).
                                    help("Utilice","<b>Operadores:</b><br>+ Suma<br>- Resta<br>* Multiplica<br>/ Divide<br><br><b>Etiquetas:</b><br>{N} C&oacute;digo de Concepto de la planilla actual (Especifique el valor en N, tambi&eacute; puede utilizar {N,M} o {N}+{M} )</b><br>{PPN} C&oacute;digo de Concepto del Perfil de Pago(Especifique el valor en N, Utilice {PPN}+{PPM})<br>{PLN} C&oacute;digo de Concepto de la Planilla Base(Especifique el valor en N, Utilice {PLN}+{PLM})<br>{JD} Jornal Diar&iacute;o<br>{DT} D&iacute;as Trabajados<br>{DEF} D&iacute;as Efectivos<br>{RMIN} Remuneraci&oacute;n M&iacute;nima<br>{IMP} Importe (del concepto donde aplica la formula)<br>{%} Valor Porcentual (del concepto donde aplica la formula)<br>{MIN} Valor de Minutos (del concepto donde aplica la formula)<br>{TPEN} Monto Total Pensionable (ONP/AFP)<br>{IMES} Monto Imponible ESSALUD<br>{TPEN&TOPE} Monto Tope Pensionable (si el monto es mayor,toma el tope )<br>{TING} Monto Total de Ingresos<br>{TDLY} Monto Total de Descuentos de Ley<br>{TCFV} Monto Total de Conafovicer<br>{TSCTR} Monto Total de Seguro Complementario de Trabajo de Riesgo<br>{TCTS} Monto Total Computable para calculo de CTS<br>{TSAN} Años de Tiempo de Servicio <br>{TSME} Meses de Tiempo de Servicio <br>{TSDI} Dias de Tiempo de Servicio <br>{CTSDNL} Dias No Laborados, registrado en Planilla CTS<br>/* Agregar Comentario<br><br><b>ejemplo:</b><br>({JD}/6)*{DT}+{%}",2));


                if($categoria==88){ /*haberes*/
                    $otable->addBreak("AFECTO A:");
                    $otable->addField("ONP/AFP: ",checkboxField("ONP/AFP","hx_serv_pensionable",1,$bd_serv_pensionable=='1'));
                    $otable->addField("ESSALUD: ",checkboxField("ESSALUD","hx_serv_essalud",1,$bd_serv_essalud=='1'));
                    $otable->addField("SCTR: ",checkboxField("SCTR","hx_serv_sctr",1,$bd_serv_sctr=='1'));
                    $otable->addField("CONAFOVICER: ",checkboxField("CONAFOVICER","hx_serv_conafovicer",1,$bd_serv_conafovicer=='1'));
                    $otable->addField("RENTA 5TA CAT: ",checkboxField("IR 5ta Cat","hx_serv_ir5ta",1,$bd_serv_ir5ta=='1',"onClick=\"xajax_pideMesesProyecta(1,this.checked,'divMesesAfecto');\""));
                    $otable->addHtml("<tr><td colspan=2><div id='divMesesAfecto'>\n");
                    $otable->addHtml(pideMesesProyecta(2,$bd_serv_ir5ta,'divMesesAfecto'));
                    $otable->addHtml("</div></td></tr>\n");

                    $otable->addField("CTS",checkboxField("CTS","hx_serv_cts",1,$bd_serv_cts==1,"onClick=\"xajax_pideFormula2(1,this.checked,'divAfecto');\""));
                    $otable->addHtml("<tr><td colspan=2><div id='divAfecto'>\n");
                    //$otable->addHtml(pideFormula2(2,$bd_serv_cts,'divAfecto'));
                    $otable->addHtml("</div></td></tr>\n");
                }//else{
                 //   if($tabl_descriaux==3 && $tipo==122){ /*descuento manual*/
                 //       $otable->addField("Hacer Editable: ",checkboxField("Hacer Editable","hx_conc_editable_cts",1,$bd_conc_editable_cts==1));                
                 //   }
                //}
                $otable->addField("Hacer Editable: ",checkboxField("Hacer Editable","hx_serv_editable",1,$bd_serv_editable==1));                

                
	$contenido_respuesta=$otable->writeHTML();

        $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);

        // Se devuelve el objeto, que este dara todas las instruccione JS para realizar la tarea
	if($op==1){
		return $objResponse;
	}else{
		return $contenido_respuesta	;
	}		
}


function pideMesesProyecta($op,$valor,$NameDiv)
{
	global $bd_serv_ir5ta_meses_proyecta;
        $response = new xajaxResponse();
        $otable = new AddTableForm();
        $otable->setLabelWidth("20%");
        $otable->setDataWidth("80%");

        if($valor==1 || $valor=='true'){            
            $otable->addField("Meses Proyectados: <font color=red>*</font>",numField("Meses Proyectados","nr_serv_ir5ta_meses_proyecta",$bd_serv_ir5ta_meses_proyecta,5,5,0,false));   
        }else{
            $otable->addHidden("nr_serv_ir5ta_meses_proyecta", 0);
        }
        $contenido_respuesta=$otable->writeHTML();        

        // Se devuelve el objeto, que este dara todas las instruccione JS para realizar la tarea
	if($op==1){
            $response->addAssign($NameDiv,'innerHTML', $contenido_respuesta);                    
	    return $response;
	}else{
            return $contenido_respuesta	;
	}		
}


function ponePrecioFraccion($precio_venta,$precio_ventafraccion,$serv_equi_unidades)
{
        $response = new xajaxResponse();

        if($precio_venta>0 && $serv_equi_unidades>0){
            $precio_ventafraccion=ROUND($precio_venta/$serv_equi_unidades,2);
        }
            
        $response->addScript("document.frm.nr_serv_preciofraccion.value=$precio_ventafraccion");
        
	return $response;
}

        
$xajax->processRequests();
// fin para Ajax




?>
<html>
<head>
	<title>Servicio M&eacute;dico-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT ?>">
	<script language="javascript" src="../../library/js/lookup.js"></script>
	<script language="javascript" src="../../library/js/janela.js"></script>	
	<script language="JavaScript" src="../../library/js/focus.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>	
        <script type="text/javascript" src="../../library/jquery/jquery-1.9.js"></script>
        <script type="text/javascript" src="../../library/jquery/jquery-ui.js"></script>
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>
	<script language='JavaScript'>
	/*
		funcion guardar
	*/
       
        function salvar(idObj) {
            if (ObligaCampos(frm)){
                    ocultarObj(idObj,10)
                    document.frm.target = "controle";
                    document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
                    document.frm.submit();
            }
        }
        
        
	function salvarxx(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			document.frm.target = "controle";
			document.frm.action = "../imes/guardar.php?_op=RecServ&nomeCampoForm=<?php echo getParam("nomeCampoForm")?>&busEmpty=<?php echo $busEmpty?>&numForm=<?php echo $numForm?>&clear=<?php echo $clear?>&nBusc_grupo_id=<?php echo $nBusc_grupo_id?>&tipo=<?php echo $tipo?>";			
			document.frm.submit();
		}
	}
        
	/*
		se invoca desde la funcion obligacampos (libjsgen.js)
		en esta funcion se puede personalizar la validacion del formulario
		y se ejecuta al momento de gurdar los datos
	*/	
	function mivalidacion(frm) {  
		return true;
	}
	
	/*
		funci�n que define el foco inicial en el formulario
	*/
	function inicializa() {
		parent.content.document.frm.zr_serv_codigo.focus();
	}
                
	</script>
    <?php 
        $xajax->printJavascript(PATH_INC.'ajax/'); 
        verif_framework(); 
     ?>		
	
</head>
<body class="contentBODY">
<?php
//if($tipo=="M")
//    pageTitle("Edici&oacute;n de Servicio M&eacute;dico","");
//elseif(inlist($tipo,"T,S"))
    pageTitle("Edici&oacute;n de Producto/Servicio","");
//else
//    pageTitle("Edici&oacute;n de Transacci&oacute;n","");

/*
	botones,
	configure conforme suas necessidades
*/
$retorno = $_SERVER['QUERY_STRING'];

$button = new Button;

    if(SIS_EMPRESA_RUC=='20487663230'){//CLINICA USAT

    }else{
        $button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2);
    }
    
//if($clear==1){
    $button->addItem(" Regresar ","catalogosServicios_buscar.php?busEmpty=$busEmpty&nBusc_grupo_id=$nBusc_grupo_id&tipo=$tipo","content");
//}else{
//    $button->addItem(" Salir sin Guardar ","javascript:if(confirm('Seguro de Salir sin Guardar?')){parent.parent.close()}","content");
//}
echo $button->writeHTML();

/*
	Control de abas,
	true, se for a aba da p�gina atual,
	false, se for qualquer outra aba,
	configure conforme al exemplo de abajo
*/
$abas = new Abas();
$abas->addItem("General",true);
if($id){
    $abas->addItem("Presentaciones",false,"catalogosServiciosPresentaciones_lista.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));        
    
    if(inlist($bd_segr_vinculo,"5")) {//
        $abas->addItem("Imagenes",false,"catalogosServicios_imagenes.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));
    }
    $abas->addItem("Vinculados",false,"catalogosServiciosVinculados_lista.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));    
    
    if(SIS_SISCONT==1){
        $abas->addItem("Asientos Contables",false,"catalogosServiciosCuentasContables_lista.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));        
    }
    
    $abas->addItem("Movimientos",false,"catalogosServicios_movimientos.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));
}

echo $abas->writeHTML();

echo "<br>";

/*
	Formulario
*/
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$bd_serv_codigo); // clave primaria
$form->addHidden("pagina",getParam("pagina")); // numero de p�gina que llamo

// definici�n de lookup avanzada


if ($id>0){  // si es edici�n 
	$form->addField("C&oacute;digo: ",$bd_serv_id);
//	$nameGrupo=getDbValue("SELECT segr_descripcion FROM servicio_grupo WHERE segr_id=$bd_segr_id");	
//	$form->addField("Grupo: ",$nameGrupo);
}
else {
//	$form->addField("C&oacute;digo: ",numField("C&oacute;digo","zr_serv_codigo",$bd_serv_codigo,10,5,0));
}



/* Instancio la Dependencia */
//$sqlDependencia=new dependenciaSuperior_SQLBox(getSession("sis_depe_superior"));
//$sqlDependencia=$sqlDependencia->getSQL();        

$sqlDependencia=new dependenciaSuperior_SQLBox2(getSession("SET_DEPE_EMISOR"));
$sqlDependencia=$sqlDependencia->getSQL();        
if(!$bd_depe_id){
    $bd_depe_id=getDbValue("SELECT id FROM ($sqlDependencia) AS x ORDER BY 1 LIMIT 1");
}
//FIN OBTENGO
$form->addField("Dependencia: <font color=red>*</font>",listboxField("Dependencia",$sqlDependencia,"tr_depe_id",$bd_depe_id,"-- Todos --","onChange=\"xajax_getTipoPrecio(1,this.value,document.frm.nr_segr_id.value,'getTipoPrecio')\"","","class=\"my_select_box\""));        

$sqlGServicio=new clsGrupoTTra_SQLlista();
$sqlGServicio->whereAcceso();
if($tipo){
    $sqlGServicio=$sqlGServicio->getSQL_servicioGrupo($tipo);
}else{    
    $sqlGServicio->orderDos();
    $sqlGServicio=$sqlGServicio->getSQL();
}
$form->addField("Grupo: <font color=red>*</font>",listboxField("Grupo",$sqlGServicio,"nr_segr_id",$bd_segr_id,'-- Seleccione Grupo de Servicio --',"onChange=\"xajax_getVinculo(1,this.value,document.frm.tr_depe_id.value,'DivEspecialidad');\"","","class=\"my_select_box\""));

$form->addHtml("<tr><td colspan=2><div id='DivEspecialidad'>\n");
$form->addHtml(getVinculo(2,$bd_segr_id,$bd_depe_id,'DivEspecialidad'));
$form->addHtml("</div></td></tr>\n");

$form->addHtml("<tr><td colspan=2><div id='divSubtipoComponente'>\n");
$form->addHtml(getSubtipoComponente(2,$bd_tabl_tipo_componente,'divSubtipoComponente'));
$form->addHtml("</div></td></tr>\n");

if(SIS_EMPRESA_TIPO==4){//Almacenes
    $form->addField("Nombre/Descripci&oacute;n: <font color=red>*</font>",textField("Nombre/Descripcion","Sr_serv_descripcion",$bd_serv_descripcion,100,220));    
}else{
    $form->addField("Nombre/Descripci&oacute;n: <font color=red>*</font>",textField("Nombre/Descripcion","Sr_serv_descripcion",$bd_serv_descripcion,100,120));
}

$form->addHtml("<tr><td colspan=2><div id='divDestino'>\n");
$form->addHtml(getVinculo_otros(2,$bd_segr_id,$bd_depe_id,'divDestino'));
$form->addHtml("</div></td></tr>\n");


if(SIS_EMPRESA_TIPO==3){//beneficencias
    if(SIS_CIUDAD=='CHICLAYO'){
        $form->addField("C&oacute;digo SISTESO: ",numField("Cod.SISTESO","zx_serv_sisteso",$bd_serv_sisteso,5,5,0,false));
    }
}


if(SIS_EMPRESA_RUC=='20480027494'){//PERUANOESPANOL
    $form->addField("C&oacute;digo Sistema: ",$bd_codigo_ant);    
}

if($bd_serv_codigo_interoperabilidad){
    $form->addField("C&oacute;digo de Interoperabilidad: ",$bd_serv_codigo_interoperabilidad);
}

if(SIS_SISCORE_SISCONT==1){
    $form->addField("Genera Asientos Contables: ",checkboxField("Genera Asientos Contables","hx_serv_genera_contabilidad",1,$bd_serv_genera_contabilidad==1));
}
      
$form->addField("Observaciones: ",textField("Observacionesn","Sx_serv_observaciones",$bd_serv_observaciones,100,120));
      
//solo si es edicion se agrega los datos de auditoria
if($id) {
            
        $form->addBreak("<b>Estado</b>");
        $form->addField("Activo: ",checkboxField("Activo","hx_serv_estado",1,$bd_serv_estado==1));
	$form->addBreak("<b>Control</b>");
        $form->addField("Creado: ",$nameUsers.'/'.substr($fregistro,0,19));
        $form->addField("Actualizado: ",$nameUsersActual.'/'.substr($fregistroActual,0,19));        

}else{
        $form->addHidden("hx_serv_estado",1); // numero de p�gina que llamo    
        //$form->addHidden("hx_serv_genera_contabilidad",1);
}

echo $form->writeHTML();
echo $button->writeHTML();

?>
    
<script>    
    $('.my_select_box').select2({
            placeholder: 'Seleccione un elemento de la lista',
            allowClear: true,
            width: '90%',
    });
</script>                                                        
</body>
</html>
<?php
/*
	cierro la conexion a la BD
*/
$conn->close();
