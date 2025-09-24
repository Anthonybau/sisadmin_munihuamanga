<?php
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("ValorUIT_class.php"); 

/* establecer conexion con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista

$myClass = new clsValorUIT($id,'Valor de UIT');

$myClass->setDatos();
if($myClass->existeDatos()){
    $bd_vaui_id= $myClass->field('vaui_id');	
    $bd_vaui_valor= $myClass->field('vaui_valor');
    $bd_vaui_anno= $myClass->field('vaui_anno');
    $bd_usua_id	= $myClass->field('usua_id');
}

?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT ?>">
	<script language="JavaScript" src="<?php echo PATH_INC ?>js/focus.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC ?>js/libjsgen.js"></script>
	
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
		document.frm.nr_vaui_anno.focus();
	}
	</script>
	<?php
        verif_framework(); 
        ?>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle($myClass->getTitle());


/* botones */
$button = new Button;
$button->addItem("Guardar","javascript:salvar('Guardar')","content",2,$bd_usua_id);
$button->addItem("Regresar",$myClass->getPageBuscar(),"content");

echo $button->writeHTML();

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("f_id",$bd_vaui_id); // clave primaria

$form->addField("A&ntilde;o: ",numField("Ano","nr_vaui_anno","$bd_vaui_anno",4,4,0));
$form->addField("Importe: ",numField("Importe","nr_vaui_valor","$bd_vaui_valor",14,10,2));
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
/* cierro la conexi�n a la BD */
$conn->close();