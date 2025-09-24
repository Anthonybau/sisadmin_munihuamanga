<?php
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("catalogosDocReferencia_class.php");

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista


$myClass = new clsDocRefer($id,"Documentos en Referencia");

if (strlen($id)>0) { // edici�n
	$myClass->setDatos();
	if($myClass->existeDatos()){
		$bd_tder_id=$myClass->field("tder_id");
		$bd_tder_descripcion=$myClass->field("tder_descripcion");
		$bd_tder_abreviado=$myClass->field("tder_abreviado");
		$bd_usua_id= $myClass->field("usua_id");
		$bd_tder_tipo= $myClass->field("tder_tipo");
		$nameUsers= $myClass->field("usua_login");
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

	/* funci�n que define el foco inicial en el formulario */
	function inicializa() {
		document.frm.Sr_dead_razon_social.focus();
	}
	</script>
	<? verif_framework(); ?>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("Edici&oacute;n de ".$myClass->getTitle());

/* botones */
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2,$bd_usua_id);
$button->addItem(" Regresar ",$myClass->getPageBuscar().$param->buildPars(true));
echo $button->writeHTML();

echo "<br>";

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("f_id",$id); // clave primaria

if($id){
    $form->addField("C&oacute;digo: ",$myClass->field("lpad_numero"));	   
}

$form->addField("Descripci&oacute;n: ",textField("Descripcion","Sr_tder_descripcion",$bd_tder_descripcion,90,90));
$form->addField("Breve: ",textField("Breve","Sr_tder_abreviado",$bd_tder_abreviado,10,10));

$lista_nivel = array("B,Bienes","S,Servicios");
$form->addField("Origen: ",radioField("Origen",$lista_nivel, "xr_tder_tipo",$bd_tder_tipo));

//solo si es edicion se agrega los datos de auditoria
if($id) {
	$form->addBreak("<b>Control</b>");
	$form->addField("Responsable: ",$nameUsers);
}
echo $form->writeHTML();
?>
</body>
</html>

<?php
/* cierro la conexi�n a la BD */
$conn->close();

