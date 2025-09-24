<?php
/* Modelo de p�gina que apresenta um formulario con criterios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("personalDatosLaborales_movimientosClass.php");

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

/* Recibo parmetros */
$relacionamento_id = getParam("relacionamento_id"); /* Recibo el dato de ralcionamiento entre la tabla padre e hijo */
$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente

/* Recibo los par�metros con la clase de "paso de par�metros por la URL"  */
$param= new manUrlv1();
$param->removePar('clear');

/* Instancio mi clase */
$myClass = new clsDatosLaborales_movimientos($relacionamento_id,"Movimientos");

$nomeCampoForm=getParam($myClass->getArrayNameVarID(0));
$busEmpty = getParam($myClass->getArrayNameVarID(1)); // 1->en la primera llamada se muestran los registros 0->en la primera llamada no se muestran los registros 
$cadena= getParam($myClass->getArrayNameVarID(2)); // cadena de busqueda
$pg = getParam($myClass->getArrayNameVarID(3)); // Tipo de Clase 
$pg = $pg?$pg:1;


/*	limpia la cadena de filtro
	si clear=1 -> esta pagina es llamada desde el menu
	si clear=2 -> esta pagina es llamada desde la busqueda avanzada (AvanzLookup)
*/
if ($clear==1) {
	$cadena="";
}

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "clsDatosLaborales_movimientos","buscar"),"");
$xajax->processRequests();

// fin para Ajax
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script type="text/javascript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>
	<script type="text/javascript" src="<?php echo PATH_INC?>jquery/jquerypack.js"></script>
	<script type="text/javascript" src="<?php echo PATH_INC?>tablesorter/jquery.tablesorter.js"></script>
	<script language="JavaScript">
		<?php echo $myClass->jsDevolver($nomeCampoForm);?>
		<?php echo $myClass->jsSorter($nomeCampoForm);?>		

		/* funcion que define el foco inicial del formulario */
		function inicializa() {
			$("#tLista tbody input[@type=checkbox]").removeAttr("checked"); /* Desmarco todos los checkbox, esto porque al insertar o editar pueden quedar algunos marcados, por eso al refrescar la p�g. se desmarcan todos */
			document.frm.Sbusc_cadena.focus();
		}
	
		
	</script>
	<script type="text/javascript" src="<?php echo PATH_INC?>js/jquerytablas.js"></script> <!-- Esta l�nea debe ir aqu� para luego de que se aplique el orden se refrescan los css de la tabla -->
        <?php $xajax->printJavascript(PATH_INC.'ajax/'); ?>
	<?php verif_framework(); ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle($myClass->getTitle());

/* botones */
echo "<br>";

/* Control de fichas, */
$abas = new Abas();
$abas->addItem("General",false,"personalDatosLaborales_edicion.php?id=$relacionamento_id&".$param->buildPars(false));
$abas->addItem("Movimientos",true);
echo $abas->writeHTML();

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
$paramFunction->addParComplete('busEmpty',1);
$paramFunction->addParComplete('numForm',0);
$paramFunction->addParComplete('relacionamento_id',$relacionamento_id);
$array=$myClass->getArrayNameVar();
foreach($array as $k => $v) {$paramFunction->addParComplete($k,$v);}

/* Título de la lista */
$myClass->setDatos();
$form->addBreak("<b>Movimientos de : ".$myClass->field('empleado').'-'.$myClass->field('pers_dni').'/'.$myClass->field('sit_laboral')."</b>\n",true,'6','center');

//$form->addField("C&oacute;digo/Descripci&oacute;n: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",$cadena,50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">");

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");

/* Creo array $formData con valores necesarios para filtrar la tabla */
$formData['Sbusc_cadena']=$cadena ;

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
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();
