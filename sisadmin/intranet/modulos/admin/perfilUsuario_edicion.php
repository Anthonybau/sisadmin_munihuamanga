<?php
/* formulario de ingreso y modificaci�n */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("perfilUsuario_class.php");

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();
$param->removePar('relacionamento_id'); /* Remuevo el par�metro */

$id = getParam("id"); // captura la variable que viene del objeto lista

$myClass = new clsPerfil($id,'Perfil de Usuario');


if (strlen($id)>0) { // edici�n
	$myClass->setDatos();
	if($myClass->existeDatos()){
		$bd_perf_id= $myClass->field('perf_id');
		$bd_perf_descripcion= $myClass->field('perf_descripcion');
		
		$bd_usua_id=$myClass->field('usua_id');
	}
}

?>
<html>
<head>
<title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
<script language="JavaScript" src="<?php echo PATH_INC?>js/focus.js"></script>
<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>
<script type="text/javascript" src="<?php echo PATH_INC?>jquery/jquerypack.js"></script>
	
<script language='JavaScript'>

	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			document.frm.target = "controle";
			document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
			document.frm.submit();
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
		document.frm.Sr_perf_descripcion.focus();
	}
	</script>

<?php

verif_framework();

?>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("Edici&oacute;n de Perfil de Usuario");

/* botones */
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",3,$bd_usua_id);
$button->addItem(" Regresar ",$myClass->getPageBuscar().$param->buildPars(true));
echo $button->writeHTML();

$abas = new Abas();
$abas->addItem("General",true);

if (strlen($id)>0) { // si es edición 
	$abas->addItem("M&oacute;dulos",false,"perfilUsuario_modulos.php?relacionamento_id=$id&".$param->buildPars(false));
	$abas->addItem("Permisos",false,"perfilUsuario_permisos.php?relacionamento_id=$id&".$param->buildPars(false));
}

echo $abas->writeHTML();

echo "<br>";

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("f_id",$id); // clave primaria

$form->addField("Descripci&oacute;n: ",textField("Descripcion","Sr_perf_descripcion",$bd_perf_descripcion,80,80));

//solo si es edicion se agrega los datos de auditoria
if(strlen($id)) {
	$nameUsers=getDbValue("select usua_login from usuario where usua_id=$bd_usua_id");
	$form->addBreak("<b>Control</b>");
	$form->addField("Responsable: ",$nameUsers);
}

echo $form->writeHTML();
?>
</body>
</html>

<?php
/* cierro la conexión a la BD */
$conn->close();