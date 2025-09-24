<?php
/* Modelo de p?gina que apresenta um formulario con crit?rios de busqueda */
include("../../library/library.php");
/* Cargo mi clase Base */
include("ValorUIT_class.php");


/* verificaci?n del n?vel de usuario */
verificaUsuario(1);

/* establecer conexi?n con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente

$param= new manUrlv1();
$param->removePar('clear');
$param->removePar('relacionamento_id');

$myClass = new clsValorUIT(0,"Valores UIT");

$nomeCampoForm=getParam($myClass->getArrayNameVarID(0));
$busEmpty = getParam($myClass->getArrayNameVarID(1)); // 1->en la primera llamada se muestran los registros 0->en la primera llamada no se muestran los registros
$cadena= getParam($myClass->getArrayNameVarID(2)); // cadena de busqueda

$pg = getParam($myClass->getArrayNameVarID(3)); // Tipo de Clase
$pg = $pg?$pg:1;


/*
 limpia la cadena de filtro
 si clear=1 -> esta pagina es llamada desde el menu
 si clear=2 -> esta pagina es llamada desde la busqueda avanzada (AvanzLookup)
 */
if ($clear==1) {

}

// Inicio Para Ajax
require_once("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "clsValorUIT","buscar"),"");
$xajax->processRequests();

// fin para Ajax
?>
<html>
<head>
<title><?php echo $myClass->getTitle() ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT ?>">
<script type="text/javascript" src="<?php echo PATH_INC ?>js/libjsgen.js"></script>
<script type="text/javascript" src="<?php echo PATH_INC ?>jquery/jquerypack.js"></script>
<script type="text/javascript"
	src="<?php echo PATH_INC ?>tablesorter/jquery.tablesorter.js"></script>
<script language="JavaScript">
		<?php echo $myClass->jsDevolver($nomeCampoForm);?>
		<?php echo $myClass->jsSorter($nomeCampoForm);?>		

		/* funcion que define el foco inicial del formulario */
		function inicializa() {
			document.frm.Sbusc_cadena.focus();
		}
	

		function excluir() {
			regSel=$("#tLista tbody input[@type=checkbox]").is(":checked");
			if(regSel){ 
				if (confirm('Desea Eliminar el(los) registro(s) selecionado(s)?')) {
					document.frm.target = "controle";
					document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(2)."&".$param->buildPars(false) ?>";
					document.frm.submit();
				}
			}else{
				alert('Seleccione el(los) registro(s) que desea eliminar')
			}
		}


		
		var SoloUnCheck=true;	
	</script>
<script type="text/javascript" src="<?php echo PATH_INC ?>js/jquerytablas.js"></script>
<!-- Esta l?nea debe ir aqu? para luego de que se aplique el orden se refrescan los css de la tabla -->

		<?php 
                $xajax->printJavascript(PATH_INC.'ajax/'); 
		verif_framework(); 
                ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
		<?php

		pageTitle($myClass->getTitle());

		/* botones */
		$button = new Button;

                $button->addItem("Nuevo",$myClass->getPageEdicion().$param->buildPars(true),"content");
                $button->addItem("Eliminar","javascript:excluir()","content",2);

	
		echo $button->writeHTML();

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
		$paramFunction->addParComplete('busEmpty',1);
		$paramFunction->addParComplete('numForm',0);
		$paramFunction->addParComplete('TipDocum',$TipDocum);
		$paramFunction->addParComplete('pageEdit',$myClass->getPageEdicion());

		$array=$myClass->getArrayNameVar();
		foreach($array as $k => $v) {$paramFunction->addParComplete($k,$v);}


		$form->addHtml($myClass->buscar(2,$formData,encodeArray($paramFunction->getUrl()),$pg,'DivResultado'));

		$form->addHtml("</div></td></tr>\n");
		echo  $form->writeHTML();
		?>
</body>
</html>
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();