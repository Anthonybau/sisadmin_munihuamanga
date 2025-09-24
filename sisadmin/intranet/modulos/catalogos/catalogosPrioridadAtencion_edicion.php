<?php
/* formulario de ingreso y modificación */
include($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");
include("../catalogos/catalogosTabla_class.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("catalogosPrioridadAtencion_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista

$myClass = new clsPrioriAtencion($id,'Prioridad de Atenci&oacute;n');

if (strlen($id)>0) { // edición
	$myClass->setDatos();
	if($myClass->existeDatos()){
		$bd_prat_id= $myClass->field('cod_pa');
		$bd_prat_descripcion= $myClass->field('prat_descripcion');
		$bd_prat_dias= $myClass->field('prat_dias');
                $bd_tabl_tipo_periodo= $myClass->field('tabl_tipo_periodo');
		$bd_usua_id	= $myClass->field('usua_id');
		$username= $myClass->field("username");
	}
}

?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="JavaScript" src="<?=PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?=PATH_INC?>js/libjsgen.js"></script>
	
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
		en esta función se puede personalizar la validaci�n del formulario
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
		función que define el foco inicial en el formulario
	*/
	function inicializa() {
		document.frm.Sr_prat_descripcion.focus();
	}
	</script>
	<?php verif_framework(); ?>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("Edici&oacute;n de ".$myClass->getTitle());


/* botones */
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2,$bd_usua_id);
$button->addItem(" Regresar ","catalogosPrioridadAtencion_buscar.php".$param->buildPars(true),"content");

echo $button->writeHTML();

/* Control de fichas */
$abas = new Abas();
$abas->addItem("General",true);
echo $abas->writeHTML();
echo "<br>";

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("f_id",$id); // clave primaria
if($id)
    $form->addField("C&oacute;digo: ",$bd_prat_id);

$periodo_prioridad=new clsTabla_SQLlista();
$periodo_prioridad->whereTipo('PERIODO_PRIORIDAD');
$sql_periodo_prioridad=$periodo_prioridad->getSQL_cbox();

$form->addField("Descripci&oacute;n: ",textField("Descripci&oacute;n","Sr_prat_descripcion",$bd_prat_descripcion,80,80));
$form->addField("Periodo de Vencimiento (d&iacute;as): ",numField("D&iacute;as de Vencimiento (d&iacute;as)","nr_prat_dias",$bd_prat_dias,4,4,0)
        .' '
        .listboxField("",$sql_periodo_prioridad,"tr_tabl_tipo_periodo",$bd_tabl_tipo_periodo));



//solo si es edicion se agrega los datos de auditoria
if(strlen($id)) {
	$form->addBreak("<b>Control</b>");
	$form->addField("Creado por: ",$username);
}

echo $form->writeHTML();
?>
</body>
</html>

<?php
/* cierro la conexión a la BD */
$conn->close();

