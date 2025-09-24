<?php
/* formulario de ingreso y modificacion */
include("../../library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("PersonaContrato_class.php");
include("PersonaDatosLaborales_class.php");
include("../catalogos/catalogosTabla_class.php");
include("Persona_class.php");
include("PlantillaContrato_class.php");
include("../catalogos/catalogosDependencias_class.php");
include("../catalogos/CargoClasificado_class.php");
include("../siscopp/siscoppAperturasComponentes_clases.php");
include("../catalogos/RegimenLaboral_class.php");
include("../catalogos/RegimenPensionario_class.php");
include("../catalogos/CategoriaRemunerativa_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$id_relacion = getParam("id_relacion"); // captura la variable que viene del objeto lista

$persona=new clsPersona_SQLlista(2);
$persona->whereID($id_relacion);
$persona->setDatos();

$myClass = new clsPersonaContrato($id,'de Registro');

if ($id>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
            $bd_peco_id=$myClass->field("peco_id"); 
            $bd_peco_contractual=$myClass->field("peco_contractual"); 
            $bd_peco_documento=$myClass->field("peco_documento"); 
            $bd_rela_id=$myClass->field('rela_id');        
            $tabl_idsitlaboral=$myClass->field('tabl_idsitlaboral');        
            $bd_tabl_clasificacion=$myClass->field('tabl_clasificacion');        
            $bd_tabl_clasificacion_practicante=$myClass->field('tabl_clasificacion_practicante');                    
            $bd_peco_numero=$myClass->field("peco_numero"); 
            $bd_numero_contrato=$myClass->field("numero_contrato"); 
            $bd_plantilla=$myClass->field("plantilla"); 
            $bd_peco_periodo=$myClass->field("peco_periodo"); 
            $bd_plco_id=$myClass->field("plco_id"); 
            $bd_peco_monto=$myClass->field("peco_monto"); 
            $dr_peco_fcontrato=dtos($myClass->field("peco_fcontrato")); 
            $dr_peco_finicio=dtos($myClass->field("peco_finicio")); 
            $dr_peco_ftermino=dtos($myClass->field("peco_ftermino")); 
            $bd_peco_funciones=$myClass->field("peco_funciones"); 
            $bd_cacl_id=$myClass->field("cacl_id"); 
            $bd_depe_id=$myClass->field("depe_id"); 
            $bd_comp_id=$myClass->field("comp_id");
            
            $bd_care_id=$myClass->field("care_id");
            $bd_tabl_nivel_remunerativo=$myClass->field("tabl_nivel_remunerativo"); 
            $bd_peco_cargofuncional=$myClass->field("peco_cargofuncional"); 
            $bd_peco_lugar=$myClass->field("peco_lugar"); 
            $bd_peco_movimiento=$myClass->field("peco_movimiento"); 
            $bd_peco_concurso=$myClass->field("peco_concurso"); 
            $bd_peco_observaciones=$myClass->field("peco_observaciones"); 
            $bd_peco_iddesde=$myClass->field("peco_iddesde"); 
            $bd_peco_adjunto1=$myClass->field("peco_adjunto1"); 
            $bd_peco_tipo_fecha_culminacion=$myClass->field("peco_tipo_fecha_culminacion"); 
                    
            $bd_usua_id = $myClass->field("usua_id"); 
            $nameUsers= $myClass->field('username');
            $fregistro=$myClass->field('peco_fregistro');
            $nameUsersActual=$myClass->field('usernameactual');
            $fregistroActual=$myClass->field('peco_actualfecha');
        }
}else{
    /*obtengo los datos del ultimo registro*/
    $contrato=new clsPersonaContrato_SQLlista();
    $contrato->orderUno();
    $contrato->setDatos();
    //$dr_peco_fcontrato=dtos($contrato->field('peco_fcontrato'));
    //$dr_peco_finicio=dtos($contrato->field('peco_finicio'));
    //$dr_peco_ftermino=dtos($contrato->field('peco_ftermino'));

    if(SIS_ESCALAFON==1){
        $bd_peco_contractual=1;
    }else{
        $bd_peco_contractual=9;
    }    
    
    $bd_peco_movimiento=1;
    $bd_peco_periodo=date('Y');
    $bd_rela_id=$persona->field('rela_id');        
    $tabl_idsitlaboral=$persona->field('tabl_idsitlaboral');

    $bd_pers_fechaingreso=dtos($persona->field('pers_fechaingreso'));
    /*Si es nuevo Inicializa estos datos desde la persona*/
    $bd_depe_id=$persona->field('depe_id');
    $bd_cacl_id=$persona->field('cacl_id');
    $bd_tabl_nivel_remunerativo=$persona->field('tabl_nivel_remunerativo');
    $bd_peco_cargofuncional=$persona->field('pers_cargofuncional');
    $bd_comp_id=$persona->field('comp_id');
    $bd_peco_funciones=$persona->field('pers_funciones');
    $bd_peco_lugar=$persona->field('pers_lugar');
    $bd_plco_id=$persona->field('plco_id');
    $bd_peco_monto= $persona->field('remuneracion');
    $bd_tabl_clasificacion=$persona->field('tabl_clasificacion');        
    $bd_tabl_clasificacion_practicante=$persona->field('tabl_clasificacion_practicante');    
    if(!$bd_peco_lugar){
        $bd_peco_lugar=getDbValue("SELECT empr_lugar FROM admin.empresa WHERE empr_id=1");    
    }    
    $bd_peco_tipo_fecha_culminacion=1;
}
    


/* Archivos para calendario */
require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("tipoContrato");
$xajax->registerFunction("estadoLaboral");
$xajax->registerFunction("pideCategoria");
$xajax->registerFunction("pideFechaTermino");

function tipoContrato($op, $valor,$tabl_idsitlaboral,$NameDiv)
{
	global $calendar,$id,$bd_plco_id,$bd_peco_documento,$bd_peco_cargofuncional,$bd_peco_funciones,$bd_peco_monto,$bd_peco_periodo,
               $bd_cacl_id,$bd_depe_id,$bd_comp_id,$dr_peco_finicio,$dr_peco_ftermino,$bd_peco_lugar,$bd_peco_culminacion,$bd_peco_observaciones,
               $bd_peco_movimiento,$bd_peco_iddesde,$id_relacion,$bd_rela_id,$bd_peco_adjunto1;
        
	$objResponse = new xajaxResponse();
	
	$otable = new AddTableForm();
	$otable->setLabelWidth("20%");
	$otable->setDataWidth("80%");
        $calendar->calendar_correla=10;
                
        if (inlist($valor,'1,2')){//contractual desde plantilla, adenda
            $otable->addbreak("Per&iacute;odo de Vigencia");
            $otable->addField("Desde: ", $calendar->make_input_field('Desde',array(),array('name'=> 'Dr_peco_finicio','value'=>$dr_peco_finicio )).
            "&nbsp;&nbsp;<b>Hasta: </b>".$calendar->make_input_field('Hasta',array(),array('name'=> 'Dx_peco_ftermino','value'=>$dr_peco_ftermino )));
        }
        
        if ($valor==9){//otros
            $bd_peco_monto=$bd_peco_monto?$bd_peco_monto:0;
            //$lista_nivel = array("1,Inicio/Reincorporaci&oacute;n","2,Culminaci&oacute;n/Baja","3,Permanencia","9,Otros"); // definición de la lista para campo radio
            $lista_nivel = array("1,Inicio/Reincorporaci&oacute;n","2,Culminaci&oacute;n/Baja"); // definición de la lista para campo radio
            $otable->addField("Movimiento: ",radioField("Movimiento",$lista_nivel, "xr_peco_movimiento",$bd_peco_movimiento,"onChange=\"xajax_estadoLaboral(1,this.value,'$bd_peco_monto','divFechas')\"","H"));

            $otable->addHtml("<tr><td colspan=2><div id='divFechas'>\n");
            $otable->addHtml(estadoLaboral(2,$bd_peco_movimiento,$bd_peco_monto,'divFechas'));
            $otable->addHtml("</div></td></tr>\n");                    
        }
        
	if (inlist($valor,'1,2')){//contractual desde plantilla,addenda
            $plantilla=new clsPlantillaContrato_SQLlista();
            $plantilla->whereSitLaboral($tabl_idsitlaboral);
            if($id && $bd_plco_id) {//SI ES MODIFICACION
                $plantilla->whereActivo2($bd_plco_id);
            }else{
                $plantilla->whereActivo();
            }
            
            if($valor=='2'){//addenda
                $plantilla->whereAdenda();
            }else{
                $plantilla->whereNOAdenda();
            }
            
            $sqlTipoPlantilla=$plantilla->getSQL_cbox();
            $otable->addField("Plantilla: ",listboxField("Plantilla",$sqlTipoPlantilla,"tr_plco_id",$bd_plco_id));                                                
            
            if (inlist($valor,'2')){//adenda
                $contrato=new clsPersonaContrato_SQLlista();
                $contrato->wherePadreID($id_relacion);
                $contrato->whereNOAdenda();
                $sql=$contrato->getSQL_cbox();                
                
                $bd_peco_iddesde=$bd_peco_iddesde?$bd_peco_iddesde:getDbValue($sql.' LIMIT 1');                
                $otable->addField("Contrato Inicial:",listboxField("Contrato Inicial",$sql,"tr_peco_iddesde",$bd_peco_iddesde,"-- Seleccione Contrato Inicial --",""));                                                
            }
            

            $perfil=new clsTabla_SQLlista();    
            $perfil->whereID($tabl_idsitlaboral);//condicion laboral
            $perfil->setDatos();        
            $arPerfil= explode(",",$perfil->field('tabl_descripaux'));

            $tabla=new clsTabla_SQLlista();
            $tabla->whereTipo('CONDICION_LABORAL');
            $tabla->whereRelaID($bd_rela_id);
            $tabla->orderUno();
            $sqlSituLabo=$tabla->getSQL_cbox();
            $otable->addField("Condici&oacute;n Laboral: ",listboxField("Condici&oacute;n laboral",$sqlSituLabo, "tr_tabl_idsitlaboral",$tabl_idsitlaboral,"-- Seleccione Condici&oacute;n Laboral --","onChange=\"xajax_pideCategoria(1,this.value,'divCategoria')\""));
                        
            $otable->addHtml("<tr><td colspan=2><div id='divCategoria'>\n");
            $otable->addHtml(pideCategoria(2,$tabl_idsitlaboral,'divCategoria'));
            $otable->addHtml("</div></td></tr>\n");            
            
        } else{
            $otable->addHidden('tx_plco_id', null);
            //$otable->addHidden('tr_tabl_idsitlaboral', $tabl_idsitlaboral);
            
            $tabla=new clsTabla_SQLlista();
            $tabla->whereTipo('CONDICION_LABORAL');
            $tabla->whereRelaID($bd_rela_id);
            $tabla->orderUno();
            $sqlSituLabo=$tabla->getSQL_cbox();
            $otable->addField("Condici&oacute;n Laboral: ",listboxField("Condici&oacute;n laboral",$sqlSituLabo, "tr_tabl_idsitlaboral",$tabl_idsitlaboral,"-- Seleccione Condici&oacute;n Laboral --","onChange=\"xajax_pideCategoria(1,this.value,'divCategoria')\""));
            
            $perfil=new clsTabla_SQLlista();    
            $perfil->whereID($tabl_idsitlaboral);//condicion laboral
            $perfil->setDatos();        
            $arPerfil= explode(",",$perfil->field('tabl_descripaux'));
        
            $sqlDependencia=new dependencia_SQLlista();
            $sqlDependencia=$sqlDependencia->getSQL_cbox();
            $otable->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia, "tr_depe_id",$bd_depe_id,"-- Seleccione Dependencia --","","","class=\"my_select_box\""));

            if (in_array("CF", $arPerfil)) { //CARGO FUNCIONAL
                $otable->addField("Cargo Funcional: ",textField("Cargo Funcional","Sr_peco_cargofuncional",$bd_peco_cargofuncional,80,100));       
            }
        }
        
        
                        
	if ($valor==1){//contractual desde plantilla
//            $otable->addField("Periodo: ",numField("Periodo","nr_peco_periodo",$bd_peco_periodo,5,4,0));            
            $otable->addField("Funciones: ",textAreaField("Fuciones","ex_peco_funciones",$bd_peco_funciones,10,80,10000));
            
        }else{
            if ($valor!=2){//addenda
                $otable->addField("Documento:",textField("Documento","Sr_peco_documento",$bd_peco_documento,70,100));           
            }
        }

        if (inlist($valor,'1')){//contractual desde plantilla, contractual sin plantilla        
            $otable->addField("Remuneraci&oacute;n: ",numField("Remuneracion","nr_peco_monto",$bd_peco_monto,16,12,2));
            $otable->addField("Lugar de Trabajo:",textField("Lugar de Trabajo","Sx_peco_lugar",$bd_peco_lugar,60,100));           
        }

        $otable->addField("Observaciones/Motivo:",textField("Observaciones/Motivo","Sx_peco_observaciones",$bd_peco_observaciones,70,100));   

        
        if ($valor==9){//otros
            $otable->addHidden("postPath",'escalafon/');
            $otable->addField("Archivo: ",fileField("Archivo1","peco_adjunto1" ,$bd_peco_adjunto1,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));            
        }        
	$contenido_respuesta=$otable->writeHTML();
	
	if($op==1){
            $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);
            $objResponse->addScript("$('select').select2({
                                        placeholder: 'Seleccione un elemento de la lista',
                                        allowClear: true,
                                        width: '80%'
                                        });");                                                                                        
            
            return $objResponse;            
        }else{
            return $contenido_respuesta	;
        }
}


function pideCategoria($op,$value,$NameDiv)
{
	global  $conn,$bd_care_id,$bd_comp_id,$bd_rela_id,$bd_tabl_nivel_remunerativo,
                $bd_cacl_id,$bd_peco_cargofuncional,$bd_depe_id,$bd_peco_concurso,
                $bd_tabl_clasificacion,$bd_tabl_clasificacion_practicante;

	$objResponse = new xajaxResponse();

        $oForm = new AddTableForm();
        $oForm->setLabelWidth("20%");
        $oForm->setDataWidth("80%");
        
        $perfil=new clsTabla_SQLlista();    
        $perfil->whereID($value);//condicion laboral
        $perfil->setDatos();        
        $arPerfil= explode(",",$perfil->field('tabl_descripaux'));
        
//       if (in_array("RL", $arPerfil)) { //REGIMEN LABORAL
//            $sqlRegLaboral= new clsRegimenLaboral_SQLlista();
//            $sqlRegLaboral=$sqlRegLaboral->getSQL_cbox();            
//            $oForm->addField("R&eacute;gimen Laboral: ",listboxField("Regimen Laboral",$sqlRegLaboral, "tr_rela_id",$bd_rela_id,"-- Seleccione Reg.Laboral --",""));
//        }

        if ($value==37) { //PRACTICANTE
                $sqltipoPracticante=new clsTabla_SQLlista();
                $sqltipoPracticante->whereTipo('CLASIFICACION_PRACTICANTE');
                $sqltipoPracticante->orderUno();
                $sqltipoPracticante=$sqltipoPracticante->getSQL_cbox();
                $oForm->addField("Tipo de Practicas:",listboxField("Tipo de Practicas",$sqltipoPracticante,"tr_tabl_clasificacion_practicante",$bd_tabl_clasificacion_practicante,"-- Seleccione Tipo de Practicas --",""));
        }
        
        if (in_array("CR", $arPerfil)) { //CATEGORIA REMUNERATIVA        
            $sqlCategoria=new clsCategoriaRemunerativa_SQLlista();
            $sqlCategoria->whereSitLaboral($value);
            $sqlCategoria=$sqlCategoria->getSQL_cbox();
            $oForm->addField("Categor&iacute;a: ",listboxField("Categoria",$sqlCategoria, "tr_care_id",$bd_care_id,"-- Seleccione Categoria --",""));
        }

        if (in_array("NR", $arPerfil)) {//NIVEL REMUNERATIVO
            $sqlNivelRemunerativo=new clsTabla_SQLlista();
            $sqlNivelRemunerativo->whereTipo('NIVEL_REMUNERATIVO');
            $sqlNivelRemunerativo->orderUno();
            $sqlNivelRemunerativo=$sqlNivelRemunerativo->getSQL_cbox();
            $oForm->addField("Nivel Remunerativo: ",listboxField("Categoria",$sqlNivelRemunerativo, "tr_tabl_nivel_remunerativo",$bd_tabl_nivel_remunerativo,"-- Seleccione Nivel Remunerativo --",""));
        }        
        if (in_array("CP", $arPerfil)) { //CADENA PRESUPUESTAL
            $sqlCadena=new clsComponentes_SQLlista();
            $sqlCadena=$sqlCadena->getSQL_cboxPlanillas();
            $oForm->addField("Cadena Presupuestal: ",listboxField("Cadena Presupuestal",$sqlCadena, "tr_comp_id",$bd_comp_id,"-- Seleccione Cadena Presupuestal --","", "","class=\"my_select_box\""));
        }

        $sqlDependencia=new dependencia_SQLlista();
        $sqlDependencia=$sqlDependencia->getSQL_cbox();
        $oForm->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia, "tr_depe_id",$bd_depe_id,"-- Seleccione Dependencia --","","","class=\"my_select_box\""));

        if (in_array("CL", $arPerfil)) { //CLASIFICACION            
            $tablaClasificacion=new clsTabla_SQLlista();
            $tablaClasificacion->whereTipo('CLASIFICACION_PERSONAL');
            $tablaClasificacion->orderUno();
            $sqlClasificacion=$tablaClasificacion->getSQL_cbox();
            $oForm->addField("Clasificaci&oacute;n:",listboxField("Clasificación",$sqlClasificacion,"tr_tabl_clasificacion",$bd_tabl_clasificacion,"-- Seleccione Clasificaci&oacute;n --",""));                
        }
        
        if (in_array("CC", $arPerfil)) { //CARGO CLASIFICADO
            $tabla=new clsCargoClasificado_SQLlista();
            $sqlCargoClasificado=$tabla->getSQL_cbox();
            $oForm->addField("Cargo Clasificado: ",listboxField("Cargo Clasificado",$sqlCargoClasificado,"tr_cacl_id",$bd_cacl_id,"-- Seleccione Cargo Clasificado --","", "","class=\"my_select_box\""));
        }
        
        if (in_array("CF", $arPerfil)) { //CARGO FUNCIONAL
            $oForm->addField("Cargo Funcional: ",textField("Cargo Funcional","Sr_peco_cargofuncional",$bd_peco_cargofuncional,80,100));       
        }        
               
        if($value==201){//CONTRATO DE SUPLENCIA
            $oForm->addField("N&deg; de Concurso:",textField("Num.Concurso","Sx_peco_concurso",$bd_peco_concurso,70,100));
        }
        
        $contenido_respuesta=$oForm->writeHTML();
       
	$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);

	if($op==1){
            $objResponse->addScript("$('select').select2({
                                        placeholder: 'Seleccione un elemento de la lista',
                                        allowClear: true,
                                        width: '80%'
                                        });");                                            
            
            return $objResponse;
        
        }else{
            return $contenido_respuesta;
        }
}

function estadoLaboral($op,$valor, $bd_peco_monto,$NameDiv)
{
	global $calendar,$dr_peco_finicio,$dr_peco_ftermino,$bd_peco_lugar,$tabl_idsitlaboral,
               $bd_depe_id,$bd_rela_id,$bd_peco_iddesde,$id_relacion,$bd_pers_fechaingreso,$bd_peco_tipo_fecha_culminacion;
        
	$objResponse = new xajaxResponse();
	
	$otable = new AddTableForm();
	$otable->setLabelWidth("20%");
	$otable->setDataWidth("80%");
        $calendar->calendar_correla=10;        

//        if(inlist($valor,'1,9')){
//            $perfil=new clsTabla_SQLlista();    
//            $perfil->whereID($tabl_idsitlaboral);//condicion laboral
//            $perfil->setDatos();        
//            $arPerfil= explode(",",$perfil->field('tabl_descripaux'));
//
//            $tabla=new clsTabla_SQLlista();
//            $tabla->whereTipo('CONDICION_LABORAL');
//            $tabla->whereRelaID($bd_rela_id);
//            $tabla->orderUno();
//            $sqlSituLabo=$tabla->getSQL_cbox();
//            $otable->addField("Condici&oacute;n Laboral: ",listboxField("Condici&oacute;n laboral",$sqlSituLabo, "tr_tabl_idsitlaboral",$tabl_idsitlaboral,"-- Seleccione Condici&oacute;n Laboral --","onChange=\"xajax_pideCategoria(1,this.value,'divCategoria')\""));
//                        
//            $otable->addHtml("<tr><td colspan=2><div id='divCategoria'>\n");
//            $otable->addHtml(pideCategoria(2,$tabl_idsitlaboral,'divCategoria'));
//            $otable->addHtml("</div></td></tr>\n");            
//        }

        switch($valor){//
            case 1://inicia                
                $dr_peco_finicio=$dr_peco_finicio?$dr_peco_finicio:$bd_pers_fechaingreso;
                $otable->addField("Fecha de Inicio/Reincorporaci&oacute;n: ", $calendar->make_input_field('Fecha de Inicio/Reincorporacion',array(),array('name'=> 'Dr_peco_finicio','value'=>$dr_peco_finicio )));
                //$otable->addField("Fecha de Culminaci&oacute;n/Baja: ", $calendar->make_input_field('Fecha Culminacion/Baja',array(),array('name'=> 'Dx_peco_ftermino','value'=>$dr_peco_ftermino )));                
                if(SIS_PLANILLAS==1){
                    $otable->addField("Remuneraci&oacute;n: ",numField("Remuneracion","nr_peco_monto",$bd_peco_monto,16,12,2));
                }    
                //$otable->addField("Lugar de Trabajo:",textField("Lugar de Trabajo","Sx_peco_lugar",$bd_peco_lugar,60,100));
                
                //$lista_nivel = array("1,Fecha de Culminaci&oacute;n","2,Fecha de Culminaci&oacute;n Indefinida","3,Ning&uacute;no"); // definición de la lista para campo radio
                $lista_nivel = array("1,Fecha de Culminaci&oacute;n","2,Fecha de Culminaci&oacute;n Indefinida"); // definición de la lista para campo radio                
                $otable->addField("Agregar: ",radioField("Movimiento",$lista_nivel, "xr_peco_tipo_fecha_culminacion",$bd_peco_tipo_fecha_culminacion,"onChange=\"xajax_pideFechaTermino(1,this.value,'$dr_peco_ftermino','divFechaTermino')\"","H"));
                
                $otable->addHtml("<tr><td colspan=2><div id='divFechaTermino'>\n");
                $otable->addHtml(pideFechaTermino(2,$bd_peco_tipo_fecha_culminacion,$dr_peco_ftermino,'divFechaTermino'));
                $otable->addHtml("</div></td></tr>\n");                    
            
                
                break;
            
            case 2://termina
                $otable->addField("Fecha de Culminaci&oacute;n/Baja: ", $calendar->make_input_field('Fecha Culminacion/Baja',array(),array('name'=> 'Dr_peco_ftermino','value'=>$dr_peco_ftermino )));
                
                if(SIS_ESCALAFON==1){
                    $personaContrato=new clsPersonaContrato_SQLlista();
                    $personaContrato->whereActivo();
                    $personaContrato->wherePadreID($id_relacion);
                    $sqlMovLiquidar=$personaContrato->getSQL_cbox2();
                    $otable->addField("Registro a Liquidar: ",listboxField("Registro a Liquidar",$sqlMovLiquidar, "tx_peco_iddesde",$bd_peco_iddesde,"-- Seleccione Movimiento a Liquidar --",""));    
                }
                $objResponse->addAssign('divCategoria','innerHTML', '');
                
                break;            
            case 3://Permanencia
                $otable->addbreak("Permanencia");
                $otable->addField("Desde: ", $calendar->make_input_field('Desde',array(),array('name'=> 'Dr_peco_finicio','value'=>$dr_peco_finicio )).
                "&nbsp;&nbsp;<b>Hasta: </b>".$calendar->make_input_field('Hasta',array(),array('name'=> 'Dx_peco_ftermino','value'=>$dr_peco_ftermino )));
                $objResponse->addAssign('divCategoria','innerHTML', '');
                //$otable->addField("Remuneraci&oacute;n: ",numField("Remuneracion","nr_peco_monto",$bd_peco_monto,16,12,2));
                //$otable->addField("Lugar de Trabajo:",textField("Lugar de Trabajo","Sx_peco_lugar",$bd_peco_lugar,60,100));
                break;            
            default:
                $objResponse->addAssign('divCategoria','innerHTML', '');
                break;            
            
        }
	
        $contenido_respuesta=$otable->writeHTML();
	            
        if($op==1){
            $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);
            $objResponse->addScript("$('select').select2({
                                                    placeholder: 'Seleccione un elemento de la lista',
                                                    allowClear: true,
                                                    width: '80%'
                                                    });");            
            return $objResponse;            
        }else{
            return $contenido_respuesta	;
        }

}


function pideFechaTermino($op,$bd_peco_tipo_fecha_culminacion,$dr_peco_ftermino,$NameDiv)
{
	global $calendar;
        
	$objResponse = new xajaxResponse();
	
	$otable = new AddTableForm();
	$otable->setLabelWidth("20%");
	$otable->setDataWidth("80%");
        $calendar->calendar_correla=11;

        switch($bd_peco_tipo_fecha_culminacion){//
            case 1://Registrar Fecha de Culminación
                $otable->addField("Fecha de Culminaci&oacute;n/Baja: ", $calendar->make_input_field('Fecha Culminacion/Baja',array(),array('name'=> 'Dr_peco_ftermino','value'=>$dr_peco_ftermino )));                
                break;            
            case 2://Fecha de Culminación Indefinida
                $otable->addField("", "<font color=red>La Fecha de Culminación quedará Vacia</font>");
                break;                        
        }
        $contenido_respuesta=$otable->writeHTML();
	            
        if($op==1){
            $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);
            return $objResponse;            
        }else{
            return $contenido_respuesta	;
        }

}
$xajax->processRequests();
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="<?php echo PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>

        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>   
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                
	

	<script language='JavaScript'>
	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
			parent.content.document.frm.submit();
		}
	}
	/*
		se invoca desde la funcion obligacampos (libjsgen.js)
		en esta funci�n se puede personalizar la validacion del formulario
		y se ejecuta al momento de gurdar los datos
	*/
	function mivalidacion(){
            var sError="Mensajes del sistema: "+"\n\n";
            var nErrTot=0;

		if (nErrTot>0){
			alert(sError)
			eval(foco)
			return false
		}else
			return true

	}


	/*
		funci�n que define el foco inicial en el formulario
	*/
	function inicializa() {
		document.frm.tr_plco_id.focus();
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
pageTitle("Edici&oacute;n ".$myClass->getTitle());



/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setUpload(true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$bd_peco_id); // clave primaria
$form->addHidden("nx_pers_id",$id_relacion); // chave estrangeira do relacionamento

$myPersona = new clsPersona($id_relacion);
$myPersona->setDatos();
$form->addField("Trabajador: ",$persona->field('empleado').' / '.$persona->field("pers_dni"));
$form->addField("Condici&oacute;n: ",$persona->field('sit_laboral_larga'));
        
$form->addField("Fecha de Documento: ", $calendar->make_input_field('Fecha de Documento',array(),array('name'=> 'Dr_peco_fcontrato','value'=>$dr_peco_fcontrato )));

if($bd_numero_contrato){
    $form->addField("N&uacute;mero:",numField("Numero","nr_peco_numero",$bd_peco_numero,5,4,0,false,""));    
}

if($bd_peco_numero){
    if($bd_peco_automatico==1){
        $form->addField("N&uacute;mero : ",$bd_numero_contrato);
        if($bd_plantilla){
            $form->addField("Plantilla: ",$bd_plantilla);
        }        
    }else{
        $form->addField("Documento:",textField("Documento","Sr_peco_documento",$bd_peco_documento,70,100));           
    }
}else{
    
    if(SIS_ESCALAFON==1){
        $lista_tipo= array("1,Contrato con Plantilla","2,Adenda","9,Otros"); 
    }else{
        $lista_tipo= array("9,Otros"); 
    }
    $form->addField("Tipo: ",radioField("Tipo",$lista_tipo, "xr_peco_contractual",$bd_peco_contractual,"onChange=\"xajax_tipoContrato(1,this.value,'$tabl_idsitlaboral','divTipoContrato')\"",'H'));
    $form->addHtml("<tr><td colspan=2><div id='divTipoContrato'>\n");
    $form->addHtml(tipoContrato(2,$bd_peco_contractual,$tabl_idsitlaboral,'divTipoContrato'));		
    $form->addHtml("</div></td></tr>\n");        
}

     /*detalle  mensualizado los gastos de planillas de personal det.indeterminado.case :
         haberes
         beneficios sociales
         cts
         x trabajador y x actividad*/


/* botones */
$button = new Button;
$button->setDiv(false);
$button->addItem("Guardar","javascript:salvar('Guardar')","content");
$form->addField("",$button->writeHTML());


if(strlen($id)) {
    $form->addBreak("<b>Control</b>");
    $form->addField("Creado: ",$nameUsers.'/'.substr($fregistro,0,19));
    if($fregistroActual){
        $form->addField("Actualizado: ",$nameUsersActual.'/'.substr($fregistroActual,0,19));
    }
}        

echo $form->writeHTML();
?>
    <script>
    $("select").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true,
            width: '80%'
            });
    
    </script>    
</body>
</html>

<?php
/* cierro la conexión a la BD */
$conn->close();