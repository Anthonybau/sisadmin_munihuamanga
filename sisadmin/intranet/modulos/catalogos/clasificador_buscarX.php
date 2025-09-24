<?
/* Modelo de p�gina que apresenta um formulario con crit�rios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
/* Cargo mi clase Base */
include("clasificador_class.php"); 

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$busEmpty = getParam("busEmpty"); // para definir si se muestran los datos en el primer ingreso
$pg = getParam("pg"); // Tipo de Clase 
$pg = $pg?$pg:1;

$param= new manUrlv1();
$param->removePar('clear');

$myClass = new clsClasificador(0,"B&uacute;squedas de Clasificador de Ingresos");

if ($clear==1) {
	setSession("cadSearch","");
}

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "clsClasificador","buscar"),"");
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
	<script type="text/javascript" src="<?=PATH_INC?>jquery/jquerypack.js"></script>
	<script type="text/javascript" src="<?=PATH_INC?>tablesorter/jquery.tablesorter.js"></script>
	<script language="JavaScript">
		<?=$myClass->jsDevolver($nomeCampoForm);?>
		<?=$myClass->jsSorter($nomeCampoForm);?>		

		function inicializa() {
			document.frm.Sbusc_cadena.focus();
		}
	
		function excluir() {
			regSel=$("#tLista tbody input[@type=checkbox]").is(":checked");
			if(regSel){ 
				if (confirm('Desea Eliminar el(los) registro(s) selecionado(s)?')) {
					document.frm.target = "controle";
					document.frm.action = "<?=$myClass->getNameFile()."?control=".base64_encode(2)."&".$param->buildPars(false)?>";
					document.frm.submit();
				}
			}else{
				alert('Seleccione el(los) registro(s) que desea eliminar')
			}
		}
	
		function proceso(direc) {
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = direc;	
			parent.content.document.frm.submit();
		}

		function imprimir() {
				parent.content.document.frm.target = "controle";
				parent.content.document.frm.action = "rptClasificador.php";
				parent.content.document.frm.submit();
		}

	</script>
	<script type="text/javascript" src="<?=PATH_INC?>js/jquerytablas.js"></script> <!-- Esta l�nea debe ir aqu� para luego de que se aplique el orden se refrescan los css de la tabla -->	
    <?php $xajax->printJavascript(PATH_INC.'ajax/'); ?>
	<? verif_framework(); ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?
pageTitle($myClass->getTitle());

/* botones */
$button = new Button;
$button->addItem(" Nuevo ","clasificador_edicion.php?clear=$valClear&nomeCampoForm=".getParam("nomeCampoForm")."&busEmpty=$busEmpty&numForm=$numForm&fieldExtra=$fieldExtra","content");

if(!$nomeCampoForm){//si es llamada de una busqueda avanzada
	$button->addItem("Actualizar Espec&iacute;fica","javascript:proceso('../imes/proceso.php?_op=Especificas&pagina=$pg')","content",2);
	$button->addItem("Eliminar","javascript:excluir()","content",2);
}

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


$form->addField("C&oacute;digo/Descripci&oacute;n: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',$pg,'DivResultado')\" value=\"Buscar\">");

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");

if($nomeCampoForm){//si es llamada de una busqueda avanzada
	$form->addHtml($myClass->buscar(2,'',encodeArray($paramFunction->getUrl()),$pg,'DivResultado'));
}else{
	$form->addHtml($myClass->buscar(2,'',encodeArray($paramFunction->getUrl()),$pg,'DivResultado'));
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
?>

