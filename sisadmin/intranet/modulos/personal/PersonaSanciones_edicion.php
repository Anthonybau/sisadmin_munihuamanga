<?
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("./PersonaSanciones_class.php");
include("./Persona_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$id_relacion = getParam("id_relacion"); // captura la variable que viene del objeto lista

$myClass = new clsPersonaSanciones($id,'Sanciones');

if ($id>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
                $bd_pesa_id=$myClass->field("pesa_id");
		$bd_pers_id=$myClass->field("pers_id");
		$bd_pesa_documento= $myClass->field("pesa_documento");
		$bd_pesa_fecha= dtos($myClass->field("pesa_fecha"));
                $bd_pesa_destitucion= $myClass->field("pesa_destitucion");
		$bd_pesa_desde= dtos($myClass->field("pesa_desde"));
		$bd_pesa_hasta= dtos($myClass->field("pesa_hasta"));
                $bd_pesa_motivo=$myClass->field("pesa_motivo");
		$bd_pesa_observacion= $myClass->field("pesa_observacion");			
		$bd_pesa_fecharegistro = $myClass->field("pesa_fecharegistro ");
		$bd_usua_id = $myClass->field("usua_id");

                $bd_pesa_adjunto1=$myClass->field("pesa_adjunto1");
                $bd_pesa_adjunto2=$myClass->field("pesa_adjunto2");
                
                $nameUsers= $myClass->field('username');
                $fregistro=$myClass->field('pesa_fecharegistro');
                $nameUsersActual=$myClass->field('usernameactual');
                $fregistroActual=$myClass->field('pesa_actualfecha');
        }
}else{
    //$bd_pesa_fecha=date('d/m/Y');
    $bd_pesa_destitucion=1;
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
	<title><?=$myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="JavaScript" src="<?=PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?=PATH_INC?>js/libjsgen.js"></script>

        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>   
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                
	

	<script language='JavaScript'>
	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "<?=$myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
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
		document.frm.Sr_pesa_documento.focus();
	}
	</script>
	<?
            $xajax->printJavascript(PATH_INC.'ajax/');
            verif_framework();
            $calendar->load_files();	
        ?>

</head>

<body class="contentBODY" onLoad="inicializa()">
<?
pageTitle("Edici&oacute;n de ".$myClass->getTitle());

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setUpload(true);

$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$bd_pesa_id); // clave primaria
$form->addHidden("nx_pers_id",$id_relacion); // chave estrangeira do relacionamento
$form->addHidden("pagina",$pg); // numero de página que llamo

$myPersona = new clsPersona($id_relacion);
$myPersona->setDatos();
$form->addField("Trabajador: ",$myPersona->field("empleado").' / '.$myPersona->field("pers_dni"));

$lista_nivel = array("1,SIN DESTITUCION","2,CON DESTITUCION"); // definición de la lista para campo radio
$form->addField("Tipo Sanci&oacute;n: ",radioField("Tipo Sancion",$lista_nivel, "xr_pesa_destitucion",$bd_pesa_destitucion,"","H"));
$form->addField("Fecha de Doc: ", $calendar->make_input_field('Fecha de Doc',array(),array('name'=> 'Dr_pesa_fecha','value'=> $bd_pesa_fecha)));
$form->addField("Documento: ",textField("Documento","Sr_pesa_documento",$bd_pesa_documento,90,90));

$form->addField("Sanci&oacute;n Desde: ", $calendar->make_input_field('Sancion Desde',array(),array('name'=> 'Dr_pesa_desde','value'=> $bd_pesa_desde)));
$form->addField("Hasta: ", $calendar->make_input_field('Fecha Hasta',array(),array('name'=> 'Dx_pesa_hasta','value'=> $bd_pesa_hasta)));
$form->addField("Motivo: ",textAreaField("Motivo","Er_pesa_motivo",$bd_pesa_motivo,3,80,200));
$form->addField("Observaci&oacute;n: ",textField("Observacion","Sx_pesa_observacion",$bd_pesa_observacion,90,90));			

$form->addHidden("postPath",'escalafon/');
$form->addField("Archivo: ",fileField("Archivo1","pesa_adjunto1" ,$bd_pesa_adjunto1,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));
//$form->addField("Archivo:",fileField("Archivo2","pesa_adjunto2" ,$bd_pesa_adjunto2,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));

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
            width: '50%'
            });
    
    </script>    
</body>
</html>

<?
/* cierro la conexión a la BD */
$conn->close();