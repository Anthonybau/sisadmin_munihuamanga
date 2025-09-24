<?php
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("./PersonaMovimientos_class.php");
include("../catalogos/catalogosTabla_class.php");
include("./Persona_class.php");
include("../catalogos/CargoClasificado_class.php");
include("../siscopp/siscoppAperturasAcumulados_clases.php");
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

$myClass = new clsPersonaMovimientos($id,'Movimientos');

$myPersona = new clsPersona($id_relacion);
$myPersona->setDatos();

if ($id>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
                $bd_pemo_id=$myClass->field("pemo_id");
		$bd_pers_id=$myClass->field("pers_id");
                $bd_pemo_fecha=dtos($myClass->field("pemo_fecha"));
                $bd_pemo_documento=$myClass->field("pemo_documento");
                $bd_tabl_clasificacion_practicante=$myClass->field("tabl_clasificacion_practicante");
                $bd_care_id=$myClass->field("care_id");
                $bd_tabl_nivel_remunerativo=$myClass->field("tabl_nivel_remunerativo");
                $bd_comp_id=$myClass->field("comp_id");
                $bd_tabl_clasificacion=$myClass->field("tabl_clasificacion");
                $bd_cacl_id=$myClass->field("cacl_id");
                $bd_pemo_cargofuncional=$myClass->field("pemo_cargofuncional");
                
                $bd_pemo_adjunto1=$myClass->field("pemo_adjunto1");
                $bd_pemo_adjunto2=$myClass->field("pemo_adjunto2");
                
                $nameUsers= $myClass->field('username');
                $fregistro=$myClass->field('pemo_fecharegistro');
                $nameUsersActual=$myClass->field('usernameactual');
                $fregistroActual=$myClass->field('pemo_actualfecha');
        }
}else{
    $bd_pemo_fecha=date('d/m/Y');
    $bd_tabl_clasificacion_practicante=$myPersona->field("tabl_clasificacion_practicante");
    $bd_care_id=$myPersona->field("care_id");
    $bd_tabl_nivel_remunerativo=$myPersona->field("tabl_nivel_remunerativo");
    $bd_comp_id=$myPersona->field("comp_id");
    $bd_tabl_clasificacion=$myPersona->field("tabl_clasificacion");
    $bd_cacl_id=$myPersona->field("cacl_id");
    $bd_pemo_cargofuncional=$myPersona->field("pers_cargofuncional");    
}


/* Archivos para calendario */
require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
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
		en esta funci�n se puede personalizar la validaci�n del formulario
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
		document.frm.tr_tabl_tipo_documento.focus();
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

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setUpload(true);

$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$bd_peod_id); // clave primaria
$form->addHidden("nx_pers_id",$id_relacion); // chave estrangeira do relacionamento
$form->addHidden("pagina",$pg); // numero de página que llamo
            
$form->addField("Trabajador: ",$myPersona->field("empleado").' / '.$myPersona->field("pers_dni"));
$form->addField("Condici&oacute;n Laboral: ",$myPersona->field("sit_laboral_larga"));   

$form->addField("Fecha: ", $calendar->make_input_field('Fecha',array(),array('name'=> 'Dr_pemo_fecha','value'=> $bd_pemo_fecha)));
$form->addField("Documento: ",textField("Documento","Sr_pemo_documento",$bd_pemo_documento,90,90));

$perfil=new clsTabla_SQLlista();    
$perfil->whereID($myPersona->field("tabl_idsitlaboral"));//condicion laboral
$perfil->setDatos();        
$arPerfil= explode(",",$perfil->field('tabl_configuracion'));

if ($value==41) { //PRACTICANTE
    $sqltipoPracticante=new clsTabla_SQLlista();
    $sqltipoPracticante->whereTipo('CLASIFICACION_PRACTICANTE');
    $sqltipoPracticante->orderUno();
    $sqltipoPracticante=$sqltipoPracticante->getSQL_cbox();
    $form->addField("Tipo de Practicas:",listboxField("Tipo de Practicas",$sqltipoPracticante,"tr_tabl_clasificacion_practicante",$bd_tabl_clasificacion_practicante,"-- Seleccione Tipo de Practicas --",""));
}

if (in_array("CR", $arPerfil)) { //CATEGORIA REMUNERATIVA        
    $sqlCategoria=new clsCategoriaRemunerativa_SQLlista();
    $sqlCategoria->whereSitLaboral($myPersona->field("tabl_idsitlaboral"));
    $sqlCategoria=$sqlCategoria->getSQL_cbox();
    $form->addField("Categor&iacute;a: ",listboxField("Categoria",$sqlCategoria, "tr_care_id",$bd_care_id,"-- Seleccione Categoria --",""));
}

if (in_array("NR", $arPerfil)) {//NIVEL REMUNERATIVO
    $sqlNivelRemunerativo=new clsTabla_SQLlista();
    $sqlNivelRemunerativo->whereTipo('NIVEL_REMUNERATIVO');
    $sqlNivelRemunerativo->orderUno();
    $sqlNivelRemunerativo=$sqlNivelRemunerativo->getSQL_cbox();
    $form->addField("Nivel Remunerativo: ",listboxField("Categoria",$sqlNivelRemunerativo, "tr_tabl_nivel_remunerativo",$bd_tabl_nivel_remunerativo,"-- Seleccione Nivel Remunerativo --",""));
}        
if (in_array("CP", $arPerfil)) { //CADENA PRESUPUESTAL
    $peri_anno=date('Y');
    $sqlCadena=new clsComponente_SQLlista();
    $sqlCadena->whereAnno($peri_anno);
    $sqlCadena=$sqlCadena->getSQL_componente();
    $form->addField("Cadena Presupuestal: ",listboxField("Cadena Presupuestal",$sqlCadena, "tr_comp_id",$bd_comp_id,"-- Seleccione Cadena Presupuestal --","", "","class=\"my_select_box\""));
}

if (in_array("CL", $arPerfil)) { //CLASIFICACION            
    $tablaClasificacion=new clsTabla_SQLlista();
    $tablaClasificacion->whereTipo('CLASIFICACION_PERSONAL');
    $tablaClasificacion->orderUno();
    $sqlClasificacion=$tablaClasificacion->getSQL_cbox();
    $form->addField("Clasificaci&oacute;n:",listboxField("Clasificación",$sqlClasificacion,"tr_tabl_clasificacion",$bd_tabl_clasificacion,"-- Seleccione Clasificaci&oacute;n --",""));                
}

if (in_array("CC", $arPerfil)) { //CARGO CLASIFICADO
    $tabla=new clsCargoClasificado_SQLlista();
    $sqlCargoClasificado=$tabla->getSQL_cbox();
    $form->addField("Cargo Clasificado: ",listboxField("Cargo Clasificado",$sqlCargoClasificado,"tr_cacl_id",$bd_cacl_id,"-- Seleccione Cargo Clasificado --","", "","class=\"my_select_box\""));
}

if (in_array("CF", $arPerfil)) { //CARGO FUNCIONAL
    $form->addField("Cargo Funcional: ",textField("Cargo Funcional","Sr_pemo_cargofuncional",$bd_pemo_cargofuncional,80,100));       
}        

$form->addHidden("postPath",'escalafon/');
$form->addField("Archivo: ",fileField("Archivo1","pemo_adjunto1" ,$bd_pemo_adjunto1,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));
//$form->addField("Archivo:",fileField("Archivo2","pemo_adjunto2" ,$bd_pemo_adjunto2,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));

/* botones */
$button = new Button;
$button->setDiv(false);
$button->addItem("Guardar","javascript:salvar('Guardar')","content",2);
$form->addField("",$button->writeHTML());

if(strlen($id)) {
    $form->addBreak("<b>Control</b>");
    $form->addField("Creado: ",$nameUsers.'/'.substr($fregistro,0,19));
    $form->addField("Actualizado: ",$nameUsersActual.'/'.substr($fregistroActual,0,19));
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