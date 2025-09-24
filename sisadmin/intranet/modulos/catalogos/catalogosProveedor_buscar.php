<?
/* Modelo de p�gina que apresenta um formulario con crit�rios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("catalogosProveedor_class.php"); 

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$nomeCampoForm=getParam("nomeCampoForm");
$TipClase = getParam("TipClase"); // Tipo de Clase 
$busEmpty = getParam("busEmpty"); // Tipo de Clase 
$numForm = getParam("numForm"); 
$numForm = $numForm?$numForm:0;

$pg = getParam("pg"); // Tipo de Clase 
$pg = $pg?$pg:1;

$param= new manUrlv1();
if($clear!=3)
    $param->removePar('clear');

$myClass = new proveedor(0,"B&uacute;squedas de Proveedores");

/*
	limpia la cadena de filtro
	si clear=1 -> esta pagina es llamada desde el menu
	si clear=2 -> esta pagina es llamada desde la busqueda avanzada (AvanzLookup)
*/
if ($clear==1) {
	setSession("cadSearch","");
}

//echo $clear;
// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "proveedor","buscar"),"");
$xajax->processRequests();
//../../library/
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
            
		<?php
                if($clear==3){
                    echo $myClass->jsDevolver2($nomeCampoForm);
                }else{
                    echo $myClass->jsDevolver($nomeCampoForm);
                }
                
                    echo $myClass->jsSorter($nomeCampoForm);                
                ?>		

		/* funcion que define el foco inicial del formulario */
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

                function imprimir() {
			document.frm.target = "controle";
			document.frm.action = "../sislogal/rptCatalogoProveedores.php";
			document.frm.submit();
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


if ($nomeCampoForm)
	$button->addItem(LOOKUP_RESET,"javascript:update('','','',0)","content",0,0,"link"); //cambio el stylo solo a este boton
	$button->addItem(" Nuevo ","catalogosProveedor_edicion.php".$param->buildPars(true),"content");

$button->addItem("Imprimir","javascript:imprimir()","content",2);			

if (!$nomeCampoForm)
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
$paramFunction->addParComplete('busEmpty',$busEmpty);
$paramFunction->addParComplete('numForm',$numForm);

$form->addField("C&oacute;digo/Descripci&oacute;n: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',$pg,'DivResultado')\" value=\"Buscar\">");

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");

/* Creo array $formData con valores necesarios para filtrar la tabla */
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