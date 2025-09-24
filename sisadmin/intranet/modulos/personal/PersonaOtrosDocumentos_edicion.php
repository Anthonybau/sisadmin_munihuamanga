<?
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("./PersonaOtrosDocumentos_class.php");
include("../catalogos/catalogosTabla_class.php");
include("./Persona_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$id_relacion = getParam("id_relacion"); // captura la variable que viene del objeto lista

$myClass = new clsPersonaOtrosDocumentos($id,'Otros Documentos');

if ($id>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
                $bd_peod_id=$myClass->field("peod_id");
		$bd_pers_id=$myClass->field("pers_id");
                $bd_peod_fecha=dtos($myClass->field("peod_fecha"));
		$bd_tabl_tipo_documento=$myClass->field("tabl_tipo_documento");
                $bd_peod_documento=$myClass->field("peod_documento");
		$bd_peod_contenido=$myClass->field("peod_contenido");
                $bd_peod_adjunto1=$myClass->field("peod_adjunto1");
                $bd_peod_adjunto2=$myClass->field("peod_adjunto2");
                
                $nameUsers= $myClass->field('username');
                $fregistro=$myClass->field('peod_fecharegistro');
                $nameUsersActual=$myClass->field('usernameactual');
                $fregistroActual=$myClass->field('peod_actualfecha');
        }
}else{
    $bd_peod_fecha=date('d/m/Y');
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
		document.frm.tr_tabl_tipo_documento.focus();
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
$form->addHidden("f_id",$bd_peod_id); // clave primaria
$form->addHidden("nx_pers_id",$id_relacion); // chave estrangeira do relacionamento
$form->addHidden("pagina",$pg); // numero de página que llamo
            
$myPersona = new clsPersona($id_relacion);
$myPersona->setDatos();
$form->addField("Trabajador: ",$myPersona->field("empleado").' / '.$myPersona->field("pers_dni"));

$tabla=new clsTabla_SQLlista();
$tabla->whereTipo('TIPO_DOCUMENTO_ESCALAFON');
$tabla->orderUno();
$sqlBanco=$tabla->getSQL_cbox();
$form->addField("Tipo Documento: ",listboxField("Tipo Documento",$sqlBanco,"tr_tabl_tipo_documento",$bd_tabl_tipo_documento,"-- Seleccione Tipo --"));
$form->addField("Fecha: ", $calendar->make_input_field('Fecha',array(),array('name'=> 'Dr_peod_fecha','value'=> $bd_peod_fecha)));
$form->addField("Documento: ",textField("Documento","Sr_peod_documento",$bd_peod_documento,90,90));
$form->addField("Contenido: ",textAreaField("Contenido","Er_peod_contenido",$bd_peod_contenido,3,80,200));

$form->addHidden("postPath",'escalafon/');
$form->addField("Archivo: ",fileField("Archivo1","peod_adjunto1" ,$bd_peod_adjunto1,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));
$form->addField("Archivo:",fileField("Archivo2","peod_adjunto2" ,$bd_peod_adjunto2,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));

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