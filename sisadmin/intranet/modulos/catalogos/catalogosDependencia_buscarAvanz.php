<?
/* Modelo de p?gina que apresenta um formulario con crit?rios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificaci?n del n?vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("catalogosDependencias_class.php"); 


/* establecer conexi?n con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$nomeCampoForm=getParam("nomeCampoForm");

$TipClase = getParam("TipClase"); // Tipo de Clase 
$busEmpty = getParam("busEmpty"); // Tipo de Clase 
$pg = getParam("pg"); // Tipo de Clase 
$pg = $pg?$pg:1;

$param= new manUrlv1();
$param->removePar('clear');

$myClass = new dependencia(0,"Catalogo de Dependencias");

if ($clear==1) {
	setSession("cadSearch","");
	// DEFINO MIS VARIABLES PREDETERMINADAS

}

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "dependencia","buscar"),"");

$xajax->processRequests();

// fin para Ajax
?>
<html>
<head>
	<title><?=$myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script type="text/javascript" src="<?=PATH_INC?>js/libjsgen.js"></script>	
	<script type="text/javascript" src="<?=PATH_INC?>jquery/jquerypack.js"></script>
	<script type="text/javascript" src="<?=PATH_INC?>tablesorter/jquery.tablesorter.js"></script>
	<script language="JavaScript">
		<?=$myClass->jsDevolver($nomeCampoForm);?>
		<?=$myClass->jsSorter($nomeCampoForm);?>		

		/* funcion que define el foco inicial del formulario */
		function inicializa() {
			document.frm.Sbusc_cadena.focus();
		}
	
			
	</script>
	<script type="text/javascript" src="<?=PATH_INC?>js/jquerytablas.js"></script> <!-- Esta l?nea debe ir aqu? para luego de que se aplique el orden se refrescan los css de la tabla -->

    <?php $xajax->printJavascript(PATH_INC.'ajax/'); ?>
	<? verif_framework(); ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?
pageTitle("B&uacute;squedas de ".$myClass->getTitle());

/* formul?rio de pesquisa */
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
$paramFunction->addParComplete('numForm',0);
$paramFunction->addParComplete('busEmpty',$busEmpty);

$form->addField("Nombre: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">");

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");

$formData['Sbusc_cadena']=getSession("cadSearch");

if($nomeCampoForm){//si es llamada de una busqueda avanzada
	$form->addHtml($myClass->buscar(2,$formData,encodeArray($paramFunction->getUrl()),$pg,'DivResultado'));
}else{
	$form->addHtml($myClass->buscar(2,$formData,encodeArray($paramFunction->getUrl()),$pg,'DivResultado'));
}


$form->addHtml("</div></td></tr>\n");
echo  $form->writeHTML();
?>
</body>
</html>
<?
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();
if($clear==1) wait('');