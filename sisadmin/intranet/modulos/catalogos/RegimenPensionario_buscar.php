<?
/* Modelo de p�gina que apresenta um formulario con crit�rios de busqueda */
include("../../library/library.php");

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("RegimenPensionario_class.php");


/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$busEmpty = 1; //getParam("busEmpty"); // para definir si se muestran los datos en el primer ingreso

$param= new manUrlv1();
$param->removePar('clear');

$myClass = new clsRegimenPensionario(0,"R&eacute;gimen Pensionario");


if ($clear==1) {
	setSession("cadSearch","");
}

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "clsRegimenPensionario","buscar"),"");
$xajax->processRequests();

// fin para Ajax
?>
<html>
<head>
	<title><?=$myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="javascript" src="<?=PATH_INC?>js/checkall.js"></script>
	<script language="JavaScript" src="<?=PATH_INC?>js/libjsgen.js"></script>
	<script language="JavaScript">

		function inicializa() {
			document.frm.Sbusc_cadena.focus();
		}
	
		function excluir() {
			if (confirm('Eliminar registros seleccionados?')) {
				parent.content.document.frm.target = "controle";
				parent.content.document.frm.action = "<?=$myClass->getNameFile()."?control=".base64_encode(2)."&".$param->buildPars(false)?>";
				parent.content.document.frm.submit();
				}
			}
	</script>
    <?php $xajax->printJavascript(PATH_INC.'ajax/'); ?>
	<? verif_framework(); ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?
pageTitle("B&uacute;squedas de ".$myClass->getTitle());

/* botones */
$button = new Button;

$button->addItem("Nuevo",$myClass->getPageEdicion().$param->buildPars(true),"content");
$button->addItem("Eliminar","javascript:excluir()","content",2);

echo $button->writeHTML();

/* formul�rio de pesquisa */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s");

//array de parametros que se ingresaran a la funcion de busqueda de ajax
$paramFunction= new manUrlv1();
$paramFunction->removeAllPar();
$paramFunction->addParComplete('colSearch','');
$paramFunction->addParComplete('colOrden','1');
$paramFunction->addParComplete('busEmpty',$busEmpty);
$paramFunction->addParComplete('pagEdit',$myClass->getPageEdicion());

//$form->addField("C&oacute;digo/Nombre: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">");

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");

$form->addHtml($myClass->buscar(2,'',encodeArray($paramFunction->getUrl()),1,'DivResultado'));

$form->addHtml("</div></td></tr>\n");
echo  $form->writeHTML();
?>
</body>
</html>
<?
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();
?>
