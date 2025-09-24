<?
/* formulario de ingreso y modificaci�n */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
/* Cargo mi clase Base */
include("clasificador_class.php"); 

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista

$myClass = new clsClasificador($id,"Edici&oacute;n de Clasificador de Ingresos");

if (strlen($id)>0) { // edici�n
	$myClass->setDatos();
	if($myClass->existeDatos()){
		$bd_clas_id = $myClass->field('clas_id');
		$bd_clas_descripcion = $myClass->field('clas_descripcion');
		$nameUsers=$myClass->field('username');
	}
}

?>
<html>
<head>
	<title><?=$myClass->getTitle()?>-Edici&oacute;n</title>
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

	function inicializa() {
		parent.content.document.frm.Sr_clas_id.focus();
	}
	</script>
	<? verif_framework(); ?>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?
pageTitle($myClass->getTitle());


/* botones */
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2,$bd_usua_id);
$button->addItem(" Regresar ","clasificador_buscar.php".$param->buildPars(true),"content");

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

$form->addField("C&oacute;digo: ",textField("C&oacute;digo","Sr_clas_id",$bd_clas_id,12,12));
$form->addField("Descripci&oacute;n: ",textField("Descripci&oacute;n","Sr_clas_descripcion",$bd_clas_descripcion,60,60));

//solo si es edicion se agrega los datos de auditoria
if(strlen($id)) {
	$form->addBreak("<b>Control</b>");
	$form->addField("Responsable: ",$nameUsers);
}

echo $form->writeHTML();
?>
</body>
</html>
<?
/* cierro la conexi�n a la BD */
$conn->close();