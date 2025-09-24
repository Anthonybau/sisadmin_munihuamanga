<?php
/*
	Est� p�gina muestra um modelo de asociaci�n de registros,
	donde uno o mas registros seleccionados son grabados
*/
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
/* Cargo mi clase Base */
include("perfilUsuario_class.php");

/*
	verificaci�n del n�vel de usuario
*/
verificaUsuario(1);

/*
	liga/desliga utilizacion de filtro
*/
$usaFiltro = true;

/*
	recupera clave, de existir
*/

/*recibo los parametro de la URL*/
$param= new manUrlv1();
$param->removePar('relacionamento_id'); /* Remuevo el par�metro */

$id = getParam("relacionamento_id");

/*
	establecer conexi�n con la BD
*/
$conn = new db();
$conn->open();

/*
	lista destino,
*/

$sql = "select a.sist_id as id,b.sist_descripcion as val ".
       "from perfilu_modulo a ".
       "left join sistema b on a.sist_id=b.sist_id ".
	   "where a.perf_id=".$id.
       " order by 1 ";

$rsDestino = new query($conn, $sql);

while ($rsDestino->getrow()) $aLista[] = "'".$rsDestino->field("id")."'";
$lista=is_array($aLista)?implode(",",$aLista):"'0'";

$rsDestino->skiprow(0); // Retorno al registro 0

$rsDestino->free();
$rsDestino = new query($conn, $sql);

/*
	lista origen,
*/
$sql = "SELECT distinct a.sist_id as id,a.sist_descripcion as val ".
       "FROM sistema a " .
	   "WHERE a.sist_id NOT IN ('50SISTRANS',$lista) " .
       "ORDER BY 1";


$rsOrigem = new query($conn, $sql);
?>
<html>
<head>
	<title>Usuario-Modulos</title>	
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>	
	<script language="JavaScript" src="<?php echo PATH_INC?>js/moveselect.js"></script>
	<?php if ($usaFiltro) { ?>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/filterlist.js"></script>
	<?php } ?>
	<script language="JavaScript">
	/*
		fun��o que chama a rotina de processamento,
		altere somente o nome da p�gina
	*/
	function salvar(idObj) {
		ocultarObj(idObj,10)
		selecionaObjetosAssociados();
		parent.content.document.frm.target = "controle";
		parent.content.document.frm.action = "perfilUsuario_modulosGuardar.php";
		parent.content.document.frm.submit();
	}
	
	function inicializa() {
		<?php if ($usaFiltro) { ?>
		parent.content.document.frm.searchOrigem.focus();
		<?php } ?>
	}
	</script>
	<?php verif_framework(); ?>		
</head>
<body class="contentBODY" onLoad="inicializa()">

<?php
pageTitle("M&oacute;dulos que corresponden a un Perfil de Usuario");

// botones de edici�n,
// configure conforme sua necessidade
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",3);
$button->addItem(" Regresar ","perfilUsuario_buscar.php?clear=1&busEmpty=1","content");
echo $button->writeHTML();

// Controle de abas,
// true, para el aba actual,
// false, para cualquier otro aba,
// configure conforme al ejemplo de abajo
$abas = new Abas();
$abas->addItem("General",false,"perfilUsuario_edicion.php?id=$id&".$param->buildPars(false));
$abas->addItem("M&oacute;dulos",true);
$abas->addItem("Permisos",false,"perfilUsuario_permisos.php?relacionamento_id=$id&".$param->buildPars(false));

echo $abas->writeHTML();

//datos de cabecera
$form = new Form("frmCaption", "", "POST", "controle", "100%",false);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->setTabMargin(false);

$myClass = new clsPerfil($id,'');
$myClass->setDatos();
$form->addField("Perfil: ",$myClass->field("perf_descripcion"));


echo $form->writeHTML();

?>

<form name="frm" method="post" onSubmit="disable(this)">
<!-- vari�vel de controle -->
<input type="hidden" name="rodou" value="s">
<!-- chave prim�ria -->
<input type="hidden" name="f_id" value="<?php echo $id ?>">

<?php

// montagem da lista de origen
if ($usaFiltro) {
	$montaOrigem  = "FILTRO&nbsp<input type=\"text\" name=\"searchOrigem\" size=\"30\" style=\"WIDTH: 92%\" onKeyUp=\"javascript:filterOrigem.set(this.value,document.frm.f_destino)\"><br>";	
} else {
	$montaOrigem = "";
}
$montaOrigem .= "<select name=\"f_origem[]\" id=\"f_origem\" multiple size=\"16\" style=\"WIDTH: 100%\" valign=\"top\">";
while ($rsOrigem->getrow()) {
	$montaOrigem .= "<option value='".$rsOrigem->field("id")."'>".$rsOrigem->field("val")."</option>";
}
$montaOrigem .= "</select>";

// montagem da lista de destino
if ($usaFiltro) {
	$montaDestino  = "FILTRO&nbsp<input type=\"text\" name=\"searchDestino\" size=\"30\" style=\"WIDTH: 92%\" onKeyUp=\"javascript:filterDestino.set(this.value,document.frm.f_origem) \"><br>";	
} else {
	$montaDestino = "";
}
$montaDestino .= "<select name=\"f_destino[]\" id=\"f_destino\" multiple size=\"16\" style=\"WIDTH: 100%;\">";
while ($rsDestino->getrow()) {
	$montaDestino .= "<option value='".$rsDestino->field("id")."'>".$rsDestino->field("val")."</option>";
}
$montaDestino .= "</select>";

// pinta botones
$montaBotoes  = "<input type=\"button\" onClick=\"if(move(parent.content.document.frm.f_origem,parent.content.document.frm.f_destino,parent.content.document.frm.searchDestino)){filterDestino.init()}\" value=\" >> \">";
$montaBotoes .= "<br><br>";
$montaBotoes .= "<input type=\"button\" onClick=\"if(move(parent.content.document.frm.f_destino,parent.content.document.frm.f_origem,parent.content.document.frm.searchOrigem)){filterOrigem.init()}\" value=\" << \">";


// layout
$table = new Table("","100%",3);
// cabecera
$table->addColumnHeader("M&oacute;dulos Disponibles",false,"47%","C");
$table->addColumnHeader("",false,"6%","C");
$table->addColumnHeader("M&oacute;dulos Seleccionados",false,"47%","C");
$table->addRow();
// caja de selecci�n
$table->addData($montaOrigem, "C");
$table->addData($montaBotoes, "C");
$table->addData($montaDestino, "C");
$table->addRow();
echo $table->writeHTML();

?>
<!-- layout do formul�rio -->
<? if ($usaFiltro) { ?>
<script>
var filterOrigem = new filterlist(document.frm.f_origem);
var filterDestino = new filterlist(document.frm.f_destino);
</script>
<? } ?>
</form>


</body>
</html>
<?php
$conn->close();
