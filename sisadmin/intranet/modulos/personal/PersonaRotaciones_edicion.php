<?php
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("./PersonaRotaciones_class.php");
include("../catalogos/catalogosTabla_class.php");
include("../catalogos/catalogosDependencias_class.php");
include("./Persona_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$id_relacion = getParam("id_relacion"); // captura la variable que viene del objeto lista

$myClass = new clsPersonaRotaciones($id,'Rotaciones');

if ($id>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
                $bd_pero_id=$myClass->field("pero_id");
		$bd_pers_id=$myClass->field("pers_id");
                $bd_pero_fecha=dtos($myClass->field("pero_fecha"));
                $bd_pero_desde=dtos($myClass->field("pero_desde"));
                $bd_pero_hasta=dtos($myClass->field("pero_hasta"));
                $bd_pero_cargofuncional=$myClass->field("pero_cargofuncional");
                $bd_pero_documento=$myClass->field("pero_documento");
                $bd_depe_id=$myClass->field("depe_id");
		$bd_dependencia=$myClass->field("dependencia");
                $bd_peod_adjunto1=$myClass->field("pero_adjunto1");
                $bd_peod_adjunto2=$myClass->field("pero_adjunto2");
                
                $nameUsers= $myClass->field('username');
                $fregistro=$myClass->field('pero_fecharegistro');
                $nameUsersActual=$myClass->field('usernameactual');
                $fregistroActual=$myClass->field('pero_actualfecha');
        }
}else{
    $bd_pero_fecha=date('d/m/Y');
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
$form->addHidden("f_id",$bd_pero_id); // clave primaria
$form->addHidden("nx_pers_id",$id_relacion); // chave estrangeira do relacionamento
$form->addHidden("pagina",$pg); // numero de página que llamo
            
$myPersona = new clsPersona($id_relacion);
$myPersona->setDatos();
$form->addField("Trabajador: ",$myPersona->field("empleado").' / '.$myPersona->field("pers_dni"));
$form->addField("Dependencia Actual: ",$myPersona->field("depe_nombre"));

$form->addField("Fecha: ", $calendar->make_input_field('Fecha',array(),array('name'=> 'Dr_pero_fecha','value'=> $bd_pero_fecha)));
$form->addField("Documento: ",textField("Documento","Sr_pero_documento",$bd_pero_documento,90,90));
$sqlDependencia=new dependencia_SQLlista();
$sqlDependencia=$sqlDependencia->getSQL_cbox();
$form->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia, "tr_depe_id",$bd_depe_id,"-- Seleccione Dependencia --","", "",""));

$form->addField("Desde: ", $calendar->make_input_field('Fecha Desde',array(),array('name'=> 'Dr_pero_desde','value'=> $bd_pero_desde)));
$form->addField("Hasta: ", $calendar->make_input_field('Fecha Hasta',array(),array('name'=> 'Dx_pero_hasta','value'=> $bd_pero_hasta)));

$form->addField("Cargo Funcional: ",textField("Cargo Funcional","Sr_pero_cargofuncional",$bd_pero_cargofuncional,80,100));       

$form->addHidden("postPath",'escalafon/');
$form->addField("Archivo: ",fileField("Archivo1","pero_adjunto1" ,$bd_peod_adjunto1,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));


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
            width: '98%'
            });
    
    </script>    
</body>
</html>

<?php
/* cierro la conexión a la BD */
$conn->close();