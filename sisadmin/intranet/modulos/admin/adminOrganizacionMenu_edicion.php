<?
/*
	formulario de ingreso y modificaci�n
*/
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/*
	verificaci�n del n�vel de usuario
*/
verificaUsuario(1);

/*
	establecer conexi�n con la BD
*/
$conn = new db();
$conn->open();

/*
	tratamiento de campos
*/
$id = getParam("id"); // captura la variable que viene del objeto lista

if (strlen($id)>0) { // edici�n
	$sql = "SELECT * FROM  sistema_modulo WHERE simo_id='$id'" ;
	$rs = new query($conn, $sql);
	if ($rs->getrow()) {
		$bd_simo_id = $rs->field("simo_id");
		$bd_simo_descripcion = $rs->field("simo_descripcion");
		$bd_simo_page = $rs->field("simo_page");
		$bd_usua_id = $rs->field("usua_id");		
		$bd_sist_id= $rs->field("sist_id");		
	}
}
else $bd_sist_id=getSession("sist_id");

?>
<html>
<head>
	<title>Componentes de M&oacute;dulo-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="JavaScript" src="<?=PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?=PATH_INC?>js/libjsgen.js"></script>	
	<script language='JavaScript'>
	/*
		funci�n guardar
	*/
	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "../imes/guardar.php?_op=OrgMenuComp";
			parent.content.document.frm.submit();
		}
	}
	/*
		se invoca desde la funcion obligacampos (libjsgen.js)
		en esta funci�n se puede personalizar la validaci�n del formulario
		y se ejecuta al momento de gurdar los datos
	*/	
	function mivalidacion(frm) {  
		return true			
	}
	
	/*
		funci�n que define el foco inicial en el formulario
	*/
	function inicializa() {
		parent.content.document.frm.sr_simo_id.focus();
	}
	</script>
	<? verif_framework(); ?>		
	
</head>
<body class="contentBODY" onLoad="inicializa()">
<? 
$nameModulo=GetDbValue("SELECT sist_breve FROM sistema WHERE sist_id='".getSession("sist_id")."' ");
pageTitle("Edici&oacute;n de Componente del M&oacute;dulo: ".$nameModulo);


/*
	botones,
	configure conforme suas necessidades
*/
$retorno = $_SERVER['QUERY_STRING'];

$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2,$bd_usua_id);
$button->addItem(" Regresar ","adminOrganizacionMenu_lista.php?$retorno","content");
echo $button->writeHTML();

/*
	Control de abas,
	true, se for a aba da p�gina atual,
	false, se for qualquer outra aba,
	configure conforme al exemplo de abajo
*/
$abas = new Abas();
$abas->addItem("Componente",true);
if ($id>0) { // si es edici�n 
	$abas->addItem("Elementos",false,"adminOrganizacionMenu_elementos.php?id=$id");
}

echo $abas->writeHTML();

echo "<br>";

/*
	Formulario
*/
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$id); // clave primaria
$form->addHidden("sx_sist_id",$bd_sist_id); // clave primaria
$form->addHidden("pagina",getParam("pagina")); // numero de página que llamo
$form->addField("C&oacute;digo: ",textField("C&oacute;digo","sr_simo_id",$bd_simo_id,20,20));
$form->addField("Componente: ",textField("Componente","sr_simo_descripcion",$bd_simo_descripcion,60,60));
//$form->addField("Enlace: ",textField("Enlace","sx_simo_page",$bd_simo_page,120,120));

//solo si es edicion se agrega los datos de auditoria
if($id) {
	$nameUsers=getDbValue("select usua_login from usuario where usua_id=$bd_usua_id");
	$form->addBreak("<b>Control</b>");
	$form->addField("Responsable: ",$nameUsers);
}

echo $form->writeHTML();
?>
</body>
</html>
<?
/*
	cierro la conexi�n a la BD
*/
$conn->close();
?>
