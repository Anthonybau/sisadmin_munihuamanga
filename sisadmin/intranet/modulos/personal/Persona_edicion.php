<?php
/* formulario de ingreso y modificacion */
include("../../library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("Persona_class.php");
include("../siscopp/siscoppAperturasAcumulados_clases.php");
include("../catalogos/AFP_class.php");
include("../catalogos/RegimenLaboral_class.php");
include("../catalogos/RegimenPensionario_class.php");
include("../catalogos/catalogosTabla_class.php");
include("../catalogos/CategoriaRemunerativa_class.php");
include("../catalogos/catalogosDependencias_class.php");
include("../catalogos/CargoClasificado_class.php");
include("../catalogos/catalogosUbigeo_class.php"); 
/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id_relacion"); 
$clear = getParam("clear"); 
$param->removePar('clear');

$myClass = new clsPersona($id,'Datos Personales');

if (strlen($id)>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
		$bd_pers_id = $myClass->field("pers_id");
		$bd_pers_apellpaterno = $myClass->field("pers_apellpaterno");
		$bd_pers_apellmaterno = $myClass->field("pers_apellmaterno");
		$bd_pers_nombres = $myClass->field("pers_nombres");
		$bd_pers_nacefecha = dtos($myClass->field("pers_nacefecha"));

		$bd_pers_sexo = $myClass->field("pers_sexo");
		$bd_pers_nacionalidad = $myClass->field("pers_nacionalidad");
		$bd_tabl_idnacionalidad=$myClass->field("tabl_idnacionalidad");
                $bd_tabl_idtipodocumento=$myClass->field("tabl_idtipodocumento");
		$bd_pers_dni = $myClass->field("pers_dni");
		$bd_pers_ruc = $myClass->field("pers_ruc");
		$bd_pers_brevete = $myClass->field("pers_brevete");
		$bd_pers_codessalud = $myClass->field("pers_codessalud");
		$bd_ubig_iddireccion = $myClass->field("ubig_iddireccion");
		$bd_pers_direccion = $myClass->field("pers_direccion");
                $bd_pers_direccion2 = $myClass->field("pers_direccion2");
                $bd_ubig_id_direccion = $myClass->field("ubig_id_direccion");
		$bd_pers_telefono = $myClass->field("pers_telefono");
		$bd_pers_movil = $myClass->field("pers_movil");
		$bd_tabl_idestadocivil = $myClass->field("tabl_idestadocivil");
		$bd_pers_email = $myClass->field("pers_email");
		$bd_pers_foto= $myClass->field("pers_foto");
                $bd_pers_activo=$myClass->field('pers_activo');
                $bd_pers_cant_hijos=$myClass->field('pers_cant_hijos');
                $bd_tabl_bancoid=$myClass->field('tabl_bancoid');
                $bd_pers_cuentadeposito=$myClass->field('pers_cuentadeposito');
                $bd_tabl_idsitlaboral=$myClass->field('tabl_idsitlaboral');
                $bd_comp_id=$myClass->field('comp_id');
                $bd_depe_id=$myClass->field('depe_id');
                $bd_care_id=$myClass->field('care_id');
                $bd_rela_id=$myClass->field('rela_id');
                $bd_repe_id=$myClass->field('repe_id');
                $bd_afp_id=$myClass->field('afp_id');
                $bd_afp_nombre=$myClass->field('afp_nombre');
                $bd_tabl_tipocomision=$myClass->field('tabl_tipocomision');
                $bd_pers_afpcus=$myClass->field('pers_afpcus');
                $bd_pers_afpafiliacion=dtos($myClass->field('pers_afpafiliacion'));
                $bd_pers_fechaingreso=dtos($myClass->field('pers_fechaingreso'));
                $bd_pers_fechacese=dtos($myClass->field('pers_fechacese'));
                $bd_pers_falta=dtos($myClass->field('pers_falta'));
                $bd_pers_documento=$myClass->field('pers_documento');
                $bd_pers_documento_baja=$myClass->field('pers_documento_baja');
                $bd_pers_cargofuncional=$myClass->field('pers_cargofuncional');
                $bd_cacl_id=$myClass->field('cacl_id');
                $bd_pers_tope_descuento=$myClass->field('pers_tope_descuento');
                $bd_tabl_clasificacion=$myClass->field('tabl_clasificacion');
                $bd_tabl_clasificacion_practicante=$myClass->field('tabl_clasificacion_practicante');
                $bd_pers_descripcion=$myClass->field("pers_descripcion"); 
                $bd_tabl_nivel_remunerativo=$myClass->field("tabl_nivel_remunerativo");
                $bd_pers_adjunto1=$myClass->field("pers_adjunto1");
                $bd_pers_adjunto2=$myClass->field("pers_adjunto2");
                
                $bd_regimen_laboral=$myClass->field("regimen_laboral");
                $bd_estado=$myClass->field("estado");
                $bd_tipo_documento_id=$myClass->field("tipo_documento_id");
                $bd_pers_acum_grabacion=$myClass->field("pers_acum_grabacion");
                $bd_pers_envia_email_fregistro=$myClass->field("pers_envia_email_fregistro");
                
                $nameUsers= $myClass->field('username');
                $fregistro=$myClass->field('pers_fecharegistro');
                $nameUsersActual=$myClass->field('usernameactual');
                $fregistroActual=$myClass->field('pers_actualfecha');
         }

}else{ // Si es nuevo
	$bd_pers_sexo = 'M';
	//$bd_pers_foto="standar_foto.jpg";
	$bd_tabl_idtipodocumento=80;
        $bd_pers_activo=1;
        $bd_depe_id=  getDbValue("SELECT depe_id FROM dependencia WHERE depe_proyecto=1 ORDER BY 1 DESC LIMIT 1");
        $bd_pers_tope_descuento=0;
        $bd_tabl_clasificacion_practicante=155;
        $bd_tabl_clasificacion=99;
}
$max_grabaciones=20;

/* Archivos para calendario */
require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);


// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("pideCategoria");
$xajax->registerFunction("pideSPP");
$xajax->registerFunction("pideCondicionLaboral");
$xajax->registerFunction("addEmpleado");
$xajax->registerFunction("eligeUbigeo");
$xajax->registerExternalFunction(array("buscarUbigeo", "ubigeo","buscarUbigeo"),"");


function eligeUbigeo($ubig_id,$lugar,$accion){
	$objResponse = new xajaxResponse();
        $objResponse->addScript("document.frm.sr_ubig_id_direccion.value='$ubig_id'");
        $objResponse->addScript("document.frm._DummySx_Ubigeo.value='$lugar'");
        $objResponse->addClear("divResultado",'innerHTML');        
	return $objResponse;
}

function pideCategoria($op,$value,$NameDiv)
{
	global $conn,$calendar,$bd_tabl_bancoid,$bd_pers_cuentadeposito,$bd_care_id,
               $bd_pers_fechaingreso,$bd_pers_fechacese,$bd_comp_id,$bd_depe_id,
               $bd_repe_id,$bd_pers_documento,$bd_pers_cargofuncional,$bd_cacl_id,
               $bd_tabl_clasificacion,$bd_tabl_clasificacion_practicante,$bd_pers_cant_hijos,
               $bd_pers_tope_descuento,$bd_tabl_nivel_remunerativo,$id,$bd_afp_nombre,
               $bd_tabl_tipocomision,$bd_pers_afpcus,$bd_pers_afpafiliacion,$bd_pers_acum_grabacion,$max_grabaciones;
        
        $calendar->calendar_correla=6;
	$objResponse = new xajaxResponse();

        $oForm = new AddTableForm();
        $oForm->setLabelWidth("20%");
        $oForm->setDataWidth("80%");

        $perfil=new clsTabla_SQLlista();    
        $perfil->whereID($value);//condicion laboral
        $perfil->setDatos();        
        $arPerfil= explode(",",$perfil->field('tabl_configuracion'));
        
        if(SIS_ESCALAFON==1){
            
            if ($value==37) { //PRACTICANTE
                if($id && $bd_pers_acum_grabacion>$max_grabaciones){
                    $tablaClasificacion=new clsTabla_SQLlista();
                    $tablaClasificacion->whereID($bd_tabl_clasificacion_practicante);
                    $tablaClasificacion->setDatos();
                    $oForm->addField("Tipo de Practicas:",$tablaClasificacion->field('tabl_descripcion'));

                }else{
                    $tablaClasificacion=new clsTabla_SQLlista();
                    $tablaClasificacion->whereTipo('CLASIFICACION_PRACTICANTE');
                    $tablaClasificacion->orderUno();
                    $sql = $tablaClasificacion->getSQL();
                    $rs = new query($conn, $sql);
                    while ($rs->getrow()) {
                        $lista[].=$rs->field("tabl_id").",".$rs->field("tabl_descripcion");
                    }
                    $oForm->addField("Tipo de Practicas: <font color=red>*</font>",radioField("Tipo de Practicas",$lista, "tr_tabl_clasificacion_practicante","$bd_tabl_clasificacion_practicante","",'H'));                        
                }
            }


    //       if (in_array("RL", $arPerfil)) { //REGIMEN LABORAL
    //            $sqlRegLaboral= new clsRegimenLaboral_SQLlista();
    //            $sqlRegLaboral=$sqlRegLaboral->getSQL_cbox();            
    //            $oForm->addField("R&eacute;gimen Laboral: ",listboxField("Regimen Laboral",$sqlRegLaboral, "tr_rela_id",$bd_rela_id,"",""));
    //        }

            if (in_array("CR", $arPerfil)) { //CATEGORIA REMUNERATIVA        
                if($id && $bd_pers_acum_grabacion>$max_grabaciones){
                    $sqlCategoria=new clsCategoriaRemunerativa_SQLlista();
                    $sqlCategoria->whereID($bd_care_id);
                    $sqlCategoria->setDatos();
                    $oForm->addField("Categor&iacute;a: ",$sqlCategoria->field('care_descripcion'));
                }else{
                    $sqlCategoria=new clsCategoriaRemunerativa_SQLlista();
                    $sqlCategoria->whereSitLaboral($value);
                    $sqlCategoria=$sqlCategoria->getSQL_cbox();
                    $oForm->addField("Categor&iacute;a: <font color=red>*</font>",listboxField("Categoria",$sqlCategoria, "tr_care_id",$bd_care_id,"-- Seleccione Categoria --",""));
                }
            }

            if (in_array("NR", $arPerfil)) {//NIVEL REMUNERATIVO
                if($id && $bd_pers_acum_grabacion>$max_grabaciones){
                    $tabla=new clsTabla_SQLlista();
                    $tabla->whereID($bd_tabl_nivel_remunerativo);
                    $tabla->setDatos();
                    $oForm->addField("Nivel Remunerativo: ",$tabla->field('tabl_descripcion'));
                }else{            
                    $sqlNivelRemunerativo=new clsTabla_SQLlista();
                    $sqlNivelRemunerativo->whereTipo('NIVEL_REMUNERATIVO');
                    $sqlNivelRemunerativo->orderUno();
                    $sqlNivelRemunerativo=$sqlNivelRemunerativo->getSQL_cbox();
                    $oForm->addField("Nivel Remunerativo: <font color=red>*</font>",listboxField("Nivel Remunerativo",$sqlNivelRemunerativo, "tr_tabl_nivel_remunerativo",$bd_tabl_nivel_remunerativo,"-- Seleccione Nivel Remunerativo --",""));
                }
            }
            if (inlist(SIS_EMPRESA_TIPO,'2,3')){//PUBLICA
                if (in_array("CP", $arPerfil)) { //CADENA PRESUPUESTAL
                    if($id && $bd_pers_acum_grabacion>$max_grabaciones){
                        $sqlCadena=new clsComponente_SQLlista();
                        $sqlCadena->whereID($bd_comp_id);
                        $sqlCadena->setDatos();
                        $oForm->addField("Cadena Presupuestal: ",$sqlCadena->field('cadena'));
                    }else{            
                        $peri_anno=date('Y');
                        $sqlCadena=new clsComponente_SQLlista();
                        $sqlCadena->whereAnno($peri_anno);
                        $sqlCadena=$sqlCadena->getSQL_componente();
                        $oForm->addField("Cadena Presupuestal: <font color=red>*</font>",listboxField("Cadena Presupuestal",$sqlCadena, "tr_comp_id",$bd_comp_id,"-- Seleccione Cadena Presupuestal --","", "","class=\"my_select_box\""));
                    }
                }
            }
        }
            if($id && $bd_pers_acum_grabacion>$max_grabaciones){
                $sqlDependencia=new dependencia_SQLlista();
                $sqlDependencia->whereID($bd_depe_id);
                $sqlDependencia->setDatos();
                $oForm->addField("Dependencia: ",$sqlDependencia->field('depe_id').' '.$sqlDependencia->field('depe_nombre').'/'.$sqlDependencia->field('depe_superior_nombre'));
            }else{
                $sqlDependencia=new dependencia_SQLlista();
                $sqlDependencia->whereDepeTodos(getSession("sis_depe_superior"));
                $sqlDependencia->whereNODepenSuperior2();
                $sqlDependencia->whereHabilitado();                
                $sqlDependencia=$sqlDependencia->getSQL_cbox2B();
                $oForm->addField("Dependencia: <font color=red>*</font> ",listboxField("Dependencia",$sqlDependencia, "tr_depe_id",$bd_depe_id,"-- Seleccione Dependencia --","", "","class=\"my_select_box\""));
            }
            if (in_array("CL", $arPerfil)) { //CLASIFICACION            
                if($id && $bd_pers_acum_grabacion>$max_grabaciones){
                    $tabla=new clsTabla_SQLlista();
                    $tabla->whereID($bd_tabl_clasificacion);
                    $tabla->setDatos();
                    $oForm->addField("Clasificaci&oacute;n:",$tabla->field('tabl_descripcion'));
                }else{                        
                    $tablaClasificacion=new clsTabla_SQLlista();
                    $tablaClasificacion->whereTipo('CLASIFICACION_PERSONAL');
                    $tablaClasificacion->orderUno();
                    $sql = $tablaClasificacion->getSQL();
                    $rs = new query($conn, $sql);
                    while ($rs->getrow()) {
                        $lista[].=$rs->field("tabl_id").",".$rs->field("tabl_descripcion");
                    }
                    $oForm->addField("Clasificaci&oacute;n: <font color=red>*</font>",radioField("Clasificación",$lista, "tr_tabl_clasificacion","$bd_tabl_clasificacion","",'H'));                        
                }
            }
            
       if(SIS_ESCALAFON==1){     
            if (in_array("CO", $arPerfil)) { //CONTRATO
                if($id && $bd_pers_acum_grabacion>$max_grabaciones){
                    $oForm->addField("Contrato: ",$bd_pers_documento);       
                }else{
                    $oForm->addField("Contrato: <font color=red>*</font>",textField("Contrato","Sr_pers_documento",$bd_pers_documento,80,100));       
                }
            }

            if (in_array("RE", $arPerfil)) { //RESOLUCION
                if($id && $bd_pers_acum_grabacion>$max_grabaciones){
                    $oForm->addField("Resoluci&oacute;n: ",$bd_pers_documento);       
                }else{
                    $oForm->addField("Resoluci&oacute;n: <font color=red>*</font>",textField("Resolucion","Sr_pers_documento",$bd_pers_documento,80,100));       
                }
            }

            if (in_array("DO", $arPerfil)) { //DOCUMENTO
                if($id && $bd_pers_acum_grabacion>$max_grabaciones){
                    $oForm->addField("Documento: ",$bd_pers_documento);       
                }else{
                    $oForm->addField("Documento: ",textField("Documento","Sx_pers_documento",$bd_pers_documento,80,100));       
                }
            }

            if (in_array("FI", $arPerfil)) { //FECHA DE INGRESO
                if($id && $bd_pers_acum_grabacion>$max_grabaciones){
                    $oForm->addField("Fecha de Ingreso: ",$bd_pers_fechaingreso);
                }else{
                    $oForm->addField("Fecha de Ingreso: <font color=red>*</font>", $calendar->make_input_field('Fecha de Ingreso',array(),array('name'=> 'Dr_pers_fechaingreso','value'=> $bd_pers_fechaingreso)));
                }
            }

            if (in_array("FT", $arPerfil)) { //FECHA DE TERMINO
                if($id && $bd_pers_acum_grabacion>$max_grabaciones){
                    $oForm->addField("Fecha de Termino: ",$bd_pers_fechacese);
                }else{
                    $oForm->addField("Fecha de Termino:",$calendar->make_input_field('Fecha de Termino',array(),array('name'=> 'Dx_pers_fechacese','value'=> $bd_pers_fechacese)));                
                }
            }

            if (in_array("FC", $arPerfil)) { //FECHA DE CESE
                if($id && $bd_pers_acum_grabacion>$max_grabaciones){
                    $oForm->addField("Fecha Cese: ",$bd_pers_fechacese);
                }else{                                    
                    $oForm->addField("Fecha Cese: <font color=red>*</font>",$calendar->make_input_field('Fecha Cese',array(),array('name'=> 'Dr_pers_fechacese','value'=> $bd_pers_fechacese)));                
                }
            }
        
            if (in_array("CC", $arPerfil)) { //CARGO CLASIFICADO
                if($id && $bd_pers_acum_grabacion>$max_grabaciones){
                    $cargo=new clsCargoClasificado_SQLlista();
                    $cargo->whereID($bd_cacl_id);
                    $cargo->setDatos();
                    $oForm->addField("Cargo Clasificado: ",$cargo->field('cacl_descripcion'));
                }else{                        
                    $cargo=new clsCargoClasificado_SQLlista();
                    $sqlCargoClasificado=$cargo->getSQL_cbox();
                    $oForm->addField("Cargo Clasificado: <font color=red>*</font>",listboxField("Cargo Clasificado",$sqlCargoClasificado,"tr_cacl_id",$bd_cacl_id,"-- Seleccione Cargo Clasificado --","", "","class=\"my_select_box\""));
                }
            }
       }
       
            if (in_array("CF", $arPerfil)) { //CARGO FUNCIONAL
                if($id && $bd_pers_acum_grabacion>$max_grabaciones){
                    $oForm->addField("Cargo Funcional: ",$bd_pers_cargofuncional);       
                }else{                        
                    $oForm->addField("Cargo Funcional: <font color=red>*</font>",textField("Cargo Funcional","Sr_pers_cargofuncional",$bd_pers_cargofuncional,80,100));       
                }
            }        

        if(SIS_ESCALAFON==1){    
            $profesion= getDbValue("SELECT LEFT(func_get_estudios,length(func_get_estudios)-1) FROM personal.func_get_estudios($id,1,0)");
            if($profesion){
                $oForm->addField("Profesi&oacute;n:","<b>$profesion</b>");
            }

            if (in_array("RP", $arPerfil)) { //REGIMEN PENSIONARIO
                if($id && $bd_pers_acum_grabacion>$max_grabaciones){
                    $sqlRegPensionario= new clsRegimenPensionario_SQLlista();
                    $sqlRegPensionario->whereID($bd_repe_id);
                    $sqlRegPensionario->setDatos();
                    $oForm->addField("R&eacute;gimen Pensionario: ",$sqlRegPensionario->field('repe_descripcion'));               

                    if($bd_repe_id==1){
                        $oForm->addField("AFP Actual: ",$bd_afp_nombre);
                        $tabla=new clsTabla_SQLlista();
                        $tabla->whereID($bd_tabl_tipocomision);
                        $tabla->setDatos();
                        $oForm->addField("Tipo Comisi&oacute;n: ",$tabla->field('tabl_descripcion')); 
                        $oForm->addField("C&oacute;digo &uacute;nico SPP: ",$bd_pers_afpcus);
                        $oForm->addField("Fecha de Afiliciaci&oacute;n: ",$bd_pers_afpafiliacion);
                    }

                }else{
                    $sqlRegPensionario= new clsRegimenPensionario_SQLlista();
                    //$sqlRegPensionario->whereNotID('99');
                    $sqlRegPensionario=$sqlRegPensionario->getSQL_cbox();

                    $bd_repe_id=$bd_repe_id?$bd_repe_id:3;

                    $oForm->addField("R&eacute;gimen Pensionario: <font color=red>*</font>",listboxField("Regimen Pensionario",$sqlRegPensionario, "tr_repe_id",$bd_repe_id,"","onChange=\"xajax_pideSPP(1,this.value,'divRegimenPensionario')\""));

                    $oForm->addHtml("<tr><td colspan=2><div id='divRegimenPensionario'>\n");
                    $oForm->addHtml(pideSPP(2,$bd_repe_id,'divRegimenPensionario'));
                    $oForm->addHtml("</div></td></tr>\n");                        
                }   
            }

            if (in_array("NH", $arPerfil)) { //REGIMEN PENSIONARIO
                $oForm->addField("N&uacute;m.de Hijos: <font color=red>*</font>",numField("Num.Hijos","Sr_pers_cant_hijos",$bd_pers_cant_hijos,4,4,0));                        
            }

            if (in_array("BA", $arPerfil)) { //BANCO
                if($id && $bd_pers_acum_grabacion>$max_grabaciones){
                    $tabla=new clsTabla_SQLlista();
                    $tabla->whereID($bd_tabl_bancoid);
                    $tabla->setDatos();
                    $oForm->addField("Banco: ",$tabla->field('tabl_descripcion'));
                    $oForm->addField("Cuenta Dep&oacute;sito: ",$bd_pers_cuentadeposito);
                }else{            
                    $msg='Ingrese cero (0) para indicar que no tiene cuenta';
                    $tabla=new clsTabla_SQLlista();
                    $tabla->whereTipo('BANCOS');
                    $tabla->orderUno();
                    $sqlBanco=$tabla->getSQL_cbox();

                    $oForm->addField("Banco: <font color=red>*</font>",listboxField("Banco",$sqlBanco,"tr_tabl_bancoid",$bd_tabl_bancoid,"-- Seleccione Banco --"));
                    $oForm->addField("Cuenta Dep&oacute;sito: <font color=red>*</font>",textField("Cuenta Deposito","Sr_pers_cuentadeposito",$bd_pers_cuentadeposito,25,25).' '.help("Informaci&oacute;n",$msg,2));
                }
            }

            if (in_array("TD", $arPerfil)) { //TOPE DE DESCUENTO
                $oForm->addField("Tope Porcentual Dscto: <font color=red>*</font>",numField("Tope Porcentual Dscto","nr_pers_tope_descuento",$bd_pers_tope_descuento,4,3,0));                        
            }
        }
        $contenido_respuesta=$oForm->writeHTML();
	if($op==1){
            $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);            
            $objResponse->addScript("  $('.my_select_box').select2({
                                            placeholder: 'Seleccione un elemento de la lista',
                                            allowClear: true
                                          });");                                            
            
            return $objResponse;
        
        }else{
            return $contenido_respuesta;
        }
}

function pideSPP($op,$value,$NameDiv)
{
	global $conn,$calendar,$bd_afp_id,$bd_pers_afpcus,$bd_pers_afpafiliacion,$bd_tabl_tipocomision,$id,$bd_pers_acum_grabacion,$max_grabaciones;

	$objResponse = new xajaxResponse();
	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");

	if($value==1){ /*si es afp*/
		$calendar->calendar_correla=12;
                if($id && $bd_pers_acum_grabacion>$max_grabaciones){
                    $sqlAfp=new clsAFP_SQLlista();
                    $sqlAfp->whereID($bd_afp_id);
                    $sqlAfp->setDatos();
                    $oForm->addField("AFP Actual: ",$sqlAfp->field('afp_nombre'));
                    $tabla=new clsTabla_SQLlista();
                    $tabla->whereID($bd_tabl_tipocomision);
                    $tabla->setDatos();
                    $oForm->addField("Tipo Comisi&oacute;n: ",$tabla->field('tabl_descripcion')); 
                    $oForm->addField("C&oacute;digo &uacute;nico SPP: ",$bd_pers_afpcus);
                    $oForm->addField("Fecha de Afiliciaci&oacute;n: ",$bd_pers_afpafiliacion);
                }else{
                    $sqlAfp=new clsAFP_SQLlista();
                    $sqlAfp=$sqlAfp->getSQL_cbox();                
                    $oForm->addField("AFP Actual: <font color=red>*</font>",listboxField("AFP Actual",$sqlAfp, "tr_afp_id",$bd_afp_id,"-- Seleccione AFP --",""));

                    $tabla=new clsTabla_SQLlista();
                    $tabla->whereTipo('TIPO_COMISION_AFP');
                    $tabla->orderUno();
                    $sql=$tabla->getSQL();

                    $rs = new query($conn, $sql);
                    while ($rs->getrow()) {
                        $lista_nivel[].=$rs->field("tabl_id").",". $rs->field("tabl_descripcion");
                    }
                    $bd_tabl_tipocomision=$bd_tabl_tipocomision?$bd_tabl_tipocomision:111; //TIPO DE AFP:UNICA,         
                    $oForm->addField("Tipo Comisi&oacute;n: <font color=red>*</font>",radioField("Tipo Comision",$lista_nivel, "xr_tabl_tipocomision",$bd_tabl_tipocomision,"","H"));                
                    $oForm->addField("C&oacute;digo &uacute;nico SPP: ",textField("C&oacute;digo &uacute;nico SPP","Sx_pers_afpcus",$bd_pers_afpcus,20,15));
                    $oForm->addField("Fecha de Afiliciaci&oacute;n: ", $calendar->make_input_field('Fecha de Afiliación',array(),array('name'=> 'Dx_pers_afpafiliacion','value'=> $bd_pers_afpafiliacion )));
                }
	}
	else {/*si es 19990*/
                $oForm->addHidden('___afp_id', null);
                $oForm->addHidden('___pers_afpcus',null);
                $oForm->addHidden('___pers_afpafiliacion',null);
        }
        
        $contenido_respuesta=$oForm->writeHTML();

	if($op==1){
                $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);            
		return $objResponse;
        }else{
		return $contenido_respuesta	;
        }
}

function pideCondicionLaboral($op,$rela_id,$NameDiv)
{
	global $conn,$bd_tabl_idsitlaboral,$id,$bd_pers_acum_grabacion,$max_grabaciones;

	$objResponse = new xajaxResponse();
	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");

        if($id && $bd_pers_acum_grabacion>$max_grabaciones){
            $tabla=new clsTabla_SQLlista();
            $tabla->whereID($bd_tabl_idsitlaboral);
            $tabla->setDatos();
            $oForm->addField("Condici&oacute;n Laboral: ",$tabla->field('tabl_descripcion'));
        }else{
            $tabla=new clsTabla_SQLlista();
            $tabla->whereTipo('CONDICION_LABORAL');
            $tabla->whereRelaID($rela_id);
            $tabla->orderUno();
            $sqlSituLabo=$tabla->getSQL_cbox();
            $oForm->addField("Condici&oacute;n Laboral: <font color=red>*</font>",listboxField("Condici&oacute;n laboral",$sqlSituLabo, "tr_tabl_idsitlaboral",$bd_tabl_idsitlaboral,"-- Seleccione Condici&oacute;n Laboral --","onChange=\"xajax_pideCategoria(1,this.value,'divCategoria')\""));
        }
        $oForm->addHtml("<tr><td colspan=2><div id='divCategoria'>\n");
        $oForm->addHtml(pideCategoria(2,$bd_tabl_idsitlaboral,'divCategoria'));
        $oForm->addHtml("</div></td></tr>\n");
        
        $contenido_respuesta=$oForm->writeHTML();

	if($op==1){
                $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);            
                $objResponse->addScript("  $('.my_select_box').select2({
                                            placeholder: 'Seleccione un elemento de la lista',
                                            allowClear: true
                                          });");                                            
		return $objResponse;
        }else{
		return $contenido_respuesta	;
        }
}

function addEmpleado($formdata)
{
    $objResponse = new xajaxResponse();
    
    $apellidop=strtoupper(substr($formdata['paterno'],0,35));
    $apellidom=strtoupper(substr($formdata['materno'],0,35));
    $nombres=strtoupper(substr($formdata['nombre'],0,40));
    
    if($formdata['sexo']){
        $sexo=substr($formdata['sexo'],0,1);
    }else{
        $sexo='';
    }
    
    if($formdata['nacimiento']){
        $fnacimiento=dtos($formdata['nacimiento']);    
    }
    
    $objResponse->addClear("divBuscarDNIEmpleado",'innerHTML');
    $objResponse->addScript("document.frm.Sr_pers_apellpaterno.value='$apellidop'");
    $objResponse->addScript("document.frm.Sr_pers_apellmaterno.value='$apellidom'");
    $objResponse->addScript("document.frm.Sr_pers_nombres.value='$nombres'");
    
    if($fnacimiento){
        $objResponse->addScript("document.frm.Dr_pers_nacefecha.value='$fnacimiento'");
    }
    
    if($sexo){
        $objResponse->addScript("document.frm.xr_pers_sexo.value='$sexo'");
    }
    
    return $objResponse;
}
$xajax->processRequests();


?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="../../library/js/focus.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>
        <script language="javascript" src="../../library/js/lookup2.js"></script>

        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>        

        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                

        <script language='JavaScript'>
	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			parent.content.document.frm.target = "content";
			parent.content.document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
			parent.content.document.frm.submit();
		}
	}
	/*
		se invoca desde la funcion obligacampos (libjsgen.js)
		en esta funci�n se puede personalizar la validaci�n del formulario
		y se ejecuta al momento de gurdar los datos
	*/
	function mivalidacion(){
                var sError="Mensajes del sistema: "+"\n\n";
                var nErrTot=0;

		if(frm.tr_tabl_idtipodocumento.value==80 && frm.Sr_pers_dni.value.length<8) {
		   frm.Sr_pers_dni.focus();
		   sError+="Campo DNI debe contener 8 digitos\n" 
		   nErrTot+=1;
		}
                //var fecha = new Date();
                //var fechaNacimiento=frm.Dr_pers_nacefecha.value;
                //if(fecha.getFullYear()-fechaNacimiento.substr(6,4)>=100) {
		//   alert('Adertencia: Usted ha ingresado una Fecha de Nacimiento Muy Antigua '+frm.Dr_pers_nacefecha.value)
		//}

		if (nErrTot>0){
			alert(sError)
			eval(foco)
			return false
		}else
			return true

	}

        function AbreVentana(sURL) {
            var w=800, h=600;
            venrepo=window.open(sURL,'rptSalida', "status=yes,resizable=yes,toolbar=no,scrollbars=yes,top=0,left=0,width=" + w + ",height=" + h, 1 );
            venrepo.focus();
	}

	/*
		funci�n que define el foco inicial en el formulario
	*/
	function inicializa() {
            document.frm.Sr_pers_dni.focus();
	}
        
        function consultar_DNI(codigo){
                $.ajax({
                url : '<?php echo SIS_URL_SUNAT_RENIEC ?>',
                method :  'POST',
                dataType : "json",
                data: {'codigo' : codigo }
                        }).then(function(data){
                            if(data.success == true) {
                                xajax_addEmpleado(data.result);
                            }else{
                                document.getElementById('divBuscarDNIEmpleado').innerHTML = '<font color=red>'+data.message+'</font>'
                            }
                        }, function(reason){
                            alert(reason.responseText);
                            //console.log(reason);
                        });

         }
         
         
        function RefreshFoto(name) {
            var formData = new FormData();
                    var files = $('#fil_img_'+name)[0].files[0];
                    formData.append('file',files);
                    $.ajax({
                        url: '../upload_tmp.php',
                        type: 'post',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            if (response != 0) {
                                $("#div_img_"+name).attr("src", response);
                            } else {
                                alert('Formato de imagen incorrecto.');
                            }
                        }
                    });        
        }
        
	</script>
	
        <?php
            $xajax->printJavascript(PATH_INC.'ajax/');        
            verif_framework();
            $calendar->load_files();	
        ?>

</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("Edici&oacute;n de ".$myClass->getTitle());

/* botones */
$button = new Button;
$sistemaModulo=new sistemaModuloOpciones();
$sistemaModulo->whereID('020110ESCALAFON');
if(getSession("sis_userid")>$max_grabaciones){
    $sistemaModulo->whereUserID(getSession("sis_userid"));
}
$sistemaModulo->setDatos();
    
if($sistemaModulo->existeDatos()){//SI TIENE ACCESO A EDICION DE DATOS
    $button->addItem("Guardar","javascript:salvar('Guardar')","controle",2);
}

if( $clear!=2 ){
    //$button->addItem("<img src='../../img/checklist.png' border='0'>&nbsp;"."Perfil de Pago","PersonaPerfilPago_lista.php?id_relacion=$id&clear=1&".$param->buildPars(false));        
    //$button->addItem("<img src='../../img/nuevo.gif' border='0'>&nbsp;"."Documentos","PersonaContrato_lista.php?id_relacion=$id&clear=1&".$param->buildPars(false));        
    $button->addItem("Imprimir","javascript:AbreVentana('rptFichaPersona.php?id=$id')","content");    
    $button->addItem("Ir a Lista de Personas","Persona_buscar.php".$param->buildPars(true),"content");
}
//echo $button->writeHTML();
if($id) {
    $botones=btnMenuEscalafon('Opciones',$id,$param);
}else{
    $botones="";
}

//echo $button->writeHTML();

echo "<table width='100%' colspan=0><tr><td width='80%'>".$button->writeHTML()."</td><td width='20%' align=right>".$botones."</td></table>";        

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("25%");
$form->setDataWidth("75%");
$form->setUpload(true);
$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$bd_pers_id); // clave primaria

$form->addHidden("pagina",$pg); // numero de página que llamo
//$form->addHidden("postPath",'escalafon/');
$form->addHidden("postPath",'escalafon/'.SIS_EMPRESA_RUC.'/');    

if($sistemaModulo->existeDatos()){//SI TIENE ACCESO A EDICION DE DATOS
    //$form->addDivImage(divImage("pers_foto",iif($bd_pers_foto,"==","","standar_foto.jpg",$bd_pers_foto), $ImgWidth=115,$ImgHeight=130, $DivTop=10, $DivWidth=200, $Divleft=500,  $classFoto="contenedorfoto", "onchange=\"RefreshFoto('DivImage',frm.pers_foto)\""));
    
    $form->addDivImage(divImage("pers_foto",iif($bd_pers_foto,"==","","standar_foto.jpg",$bd_pers_foto), 115,130, 100, 200, 500, "contenedorfoto", "onchange=\"RefreshFoto(this.name)\"",iif($bd_pers_foto,"==","","../../img/",PUBLICUPLOAD.'escalafon/'.SIS_EMPRESA_RUC.'/')));

    //$lista_nivel = array("1,ACTIVO","9,DE BAJA"); // definici�n de la lista para campo radio
    //$form->addField("Estado: ",radioField("Estado",$lista_nivel, "xr_pers_activo",$bd_pers_activo,"","H"));
    if($bd_pers_activo==1){
        if($id) {
            $form->addField("Estado: ","<B>ACTIVO</B>&nbsp;&nbsp;"."<b><font color=green>Actualizado por ".$nameUsersActual.' el '.substr(dtos($fregistroActual,"-"),0,19)."</font></B>");            
        }else{
            $form->addField("Estado: ","<B>ACTIVO</B>");
        }
        
    }else{
        $form->addField("Estado: ","<font color=Red><B>DE BAJA</B> / FECHA: $bd_pers_fechacese / DOCUMENTO: $bd_pers_documento_baja</font>");
    }
    $form->addBreak("<b>DATOS PERSONALES</b>");
    
    $tabla=new clsTabla_SQLlista();
    $tabla->whereTipo('TIPO_DOC_IDENTIDAD');
    $tabla->orderUno();
    $sqlTDoc=$tabla->getSQL_cbox();
    $form->addField("Tipo Doc.: <font color=red>*</font>",listboxField("Tipo Doc.",$sqlTDoc,"tr_tabl_idtipodocumento",$bd_tabl_idtipodocumento));
    
    $btnBuscarCodigo="<input type=\"button\" onClick=\"javascript:consultar_DNI(document.frm.Sr_pers_dni.value);document.getElementById('divBuscarDNIEmpleado').innerHTML = 'Espere, Buscando...'\" value=\"Buscar\">";
    $form->addField("N&uacute;mero: <font color=red>*</font>",numField("N&ordm; Doc.","Sr_pers_dni",$bd_pers_dni,10,8,0,false)."&nbsp;$btnBuscarCodigo&nbsp");    
    $form->addHtml("<tr><td colspan=2><div id='divBuscarDNIEmpleado'></div></td></tr>\n");
    $form->addField("Apellido paterno: <font color=red>*</font>",textField("Apellido paterno","Sr_pers_apellpaterno",$bd_pers_apellpaterno,35,35));
    $form->addField("Apellido materno: <font color=red>*</font>",textField("Apellido materno","Sr_pers_apellmaterno",$bd_pers_apellmaterno,35,35));
    $form->addField("Nombres: ",textField("Nombres","Sr_pers_nombres",$bd_pers_nombres,40,40));
    if($id) {
        $form->addField("Fech.Nacimiento: <font color=red>*</font>", $calendar->make_input_field('Fecha de Nacimiento',array(),array('name'=> 'Dr_pers_nacefecha','value'=> $bd_pers_nacefecha )).
                "&nbsp;&nbsp;<b>Edad: ".calcTiempo(stod($bd_pers_nacefecha))."</b>");
    }else{
        $form->addField("Fech.Nacimiento: <font color=red>*</font>", $calendar->make_input_field('Fecha de Nacimiento',array(),array('name'=> 'Dr_pers_nacefecha','value'=> $bd_pers_nacefecha )));
    }


    $lista_nivel = array("M,Masculino","F,Femenino"); // definici�n de la lista para campo radio
    $form->addField("Sexo: <font color=red>*</font>",radioField("Sexo",$lista_nivel, "xr_pers_sexo",$bd_pers_sexo,"","H"));
    //$sqlNacionalidad = "SELECT tabl_id as id, tabl_descripcion as Descripcion FROM tabla WHERE tabl_tipo='NACIONALIDAD' ORDER BY 1 ";
    //$form->addField("Nacionalidad: ",listboxField("Nacionalidad",$sqlNacionalidad, "tr_tabl_idnacionalidad",$bd_tabl_idnacionalidad,"",""));

    $form->addField("RUC: ",numField("RUC.","Sx_pers_ruc",$bd_pers_ruc,12,11,0));
    $form->addField("Brevete: ",textField("Brevete","Sx_pers_brevete",$bd_pers_brevete,20,20)); 
    $form->addField("C&oacute;d ESSALUD: ",textField("Cod.ESSALUD","Sx_pers_codessalud",$bd_pers_codessalud,15,15));
    
    
//    $lugar = new Lookup();
//    $lugar->setTitle("Ubigeo");
//    $lugar->setNomeCampoForm("Lugar","sr_ubig_id_direccion");
//    $sql = "SELECT ubig_id,distrito
//                    FROM catalogos.view_ubigeo ";
//
//    setSession("sqlLkupEmp", $sql);
//    $lugar->setNomeTabela("sqlLkupEmp");  //nombre de tabla
//    $lugar->setNomeCampoChave("ubig_id");  //campo clave
//    $lugar->setNomeCampoExibicao("distrito");
//    $lugar->setListaInicial(0);
//    $lugar->setUpCase(true);//para busquedas con texto en mayuscula
//    $lugar->readOnly(false);
//    $lugar->setSize(70);//tamaño del campo
//    
//    $lugar->setValorCampoForm($bd_ubig_id_direccion);
//    $form->addField("Lugar: ",$lugar->writeHTML());
    
    if($bd_ubig_id_direccion){
        $ubigeo=new ubigeo_SQLlista();
        $ubigeo->whereID($bd_ubig_id_direccion);
        $ubigeo->setDatos();
        $nombre_ubigeo=$ubigeo->field('distrito');
    }    

    $form->addField("Direcci&oacute;n: <font color=red>*</font>",textField("Direccion","Sr_pers_direccion",$bd_pers_direccion,80,80));
    
    $ubigeo=new ubigeo_SQLlista();
    $sqlUbigeo=$ubigeo->getSQL_cbox();
    $form->addField("UBIGEO: <font color=red>*</font>",listboxField("UBIGEO",$sqlUbigeo, "sr_ubig_id_direccion",$bd_ubig_id_direccion,"-- Seleccione UBIGEO --","", "","class=\"my_select_box\""));
                
    
    $form->addField("Tel&eacute;fono: ",textField("Tel&eacute;fono","Sx_pers_telefono",$bd_pers_telefono,12,12));
    $form->addField("M&oacute;vil: ",textField("M&oacute;vil","Sx_pers_movil",$bd_pers_movil,12,12));
    $form->addField("Email: ",textField("Email","cx_pers_email",$bd_pers_email,55,50));

    $sqlEstcivil = "SELECT tabl_id, tabl_descripcion FROM tabla WHERE tabl_tipo='ESTADO_CIVIL' ORDER BY tabl_descripcion ";
    $form->addField("Estado Civil: <font color=red>*</font>",listboxField("Estado civil",$sqlEstcivil, "tr_tabl_idestadocivil",$bd_tabl_idestadocivil,"",""));


    $form->addBreak("<b>DATOS LABORALES:</b>");   
    //$bd_pers_falta=$bd_pers_falta?$bd_pers_falta:date('d/m/Y');
    //$form->addField("Fecha Alta: ", $calendar->make_input_field('Fecha Alta',array(),array('name'=> 'Dx_pers_falta','value'=> $bd_pers_falta)));


    if($id){
        $form->addField("R&eacute;gimen Laboral: ", "$bd_regimen_laboral");   
    }else{
        $sqlRegLaboral= new clsRegimenLaboral_SQLlista();
        $sqlRegLaboral=$sqlRegLaboral->getSQL_cbox();            
        $form->addField("R&eacute;gimen Laboral: <font color=red>*</font>",listboxField("Regimen Laboral",$sqlRegLaboral, "tr_rela_id",$bd_rela_id,"-- Seleccione Regimen Laboral --","onChange=\"xajax_pideCondicionLaboral(1,this.value,'divCondicionLaboral')\""));
    }

    $form->addHtml("<tr><td colspan=2><div id='divCondicionLaboral'>\n");
    $form->addHtml(pideCondicionLaboral(2,$bd_rela_id,'divCondicionLaboral'));
    $form->addHtml("</div></td></tr>\n");

    $form->addField("Descripciones:",textField("Descripciones","Sx_pers_descripcion",$bd_pers_descripcion,70,100));   
    //$sqlMovi = "SELECT tabl_id, tabl_descripcion FROM tabla WHERE tabl_tipo='ESTADO_EMPLEADO' ORDER BY 1";
    //$form->addField("Estado: ",listboxField("Estado",$sqlMovi, "tr_tabl_estado",$bd_tabl_estado));
    //$form->addField("Descripci&oacute;n: ",textField("Descripci&oacute;n","Sx_pdla_detalles",$bd_pdla_detalles,100,100));
    $form->addHidden("postPath",'escalafon/'.SIS_EMPRESA_RUC.'/');
    $form->addField("Archivo: ",fileField("Archivo1","pers_adjunto1" ,$bd_pers_adjunto1,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'.SIS_EMPRESA_RUC.'/'));
    $form->addField("Archivo: ",fileField("Archivo2","pers_adjunto2" ,$bd_pers_adjunto2,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'.SIS_EMPRESA_RUC.'/'));
    
    if($bd_pers_envia_email_fregistro){
        //$form->addField("Envio de correo efectuado:",$bd_pers_envia_email_fregistro);
    }
}else{
    $form->addField("Estado: ",$bd_estado);
    $form->addBreak("<b>DATOS PERSONALES</b>");
    $form->addField("Apellido paterno: ","<b>$bd_pers_apellpaterno</b>");
    $form->addField("Apellido materno: ","<b>$bd_pers_apellmaterno</b>");
    $form->addField("Nombres: ","<b>$bd_pers_nombres</b>");
    $form->addField("N&uacute;mero $bd_tipo_documento_id: ","<b>$bd_pers_dni</b>");    
    $form->addField("Fech.Nacimiento: ","<b>$bd_pers_nacefecha</b>");    
    $form->addField("Sexo: ","<b>$bd_pers_sexo</b>");    
    
}
if(strlen($id)) {
    $form->addBreak("<b>Control</b>");
    $form->addField("Creado: ",$nameUsers.'/'.substr($fregistro,0,19));
    //$form->addField("Actualizado: ",$nameUsersActual.'/'.substr($fregistroActual,0,19));
}
echo $form->writeHTML();
echo "<table width='100%' colspan=0><tr><td width='80%'>".$button->writeHTML()."</td><td width='20%' align=right></td></table>";        
//echo $button->writeHTML();
?>
    <script>
        $(".my_select_box").select2({
                placeholder: "Seleccione un elemento de la lista",
                allowClear: true
                });    
    </script>    
</body>
</html>

<?php
/* cierro la conexión a la BD */
$conn->close();