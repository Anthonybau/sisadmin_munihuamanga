<?php
/* Modelo de p�gina que apresenta um formulario con crit�rios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("catalogosServicios_class.php");
include("catalogosServicios_movimientosClass.php"); 


/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

/* Recibo par�metros */
$relacionamento_id = getParam("relacionamento_id"); /* Recibo el dato de ralcionamiento entre la tabla padre e hijo */
$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente

/* Recibo los par�metros con la clase de "paso de par�metros por la URL"  */
$param= new manUrlv1();
$param->removePar('clear');

$servicio = new servicios($relacionamento_id);
$servicio->setDatos();

/* Instancio mi clase base */
$myClass = new serviciosMovimientos(0,"ACTUALIZACIONES DE SERVICIOS");

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
$xajax->registerExternalFunction(array("buscar", "serviciosMovimientos","buscar"),"");
$xajax->processRequests();

// fin para Ajax
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<link rel="stylesheet" href="<?php echo PATH_INC ?>thickbox/thickbox.css" type="text/css" media="screen" />
	<script type="text/javascript" src="<?php echo PATH_INC ?>jquery/jquerypack.js"></script>
	<script type="text/javascript" src="<?php echo PATH_INC ?>thickbox/thickbox.js"></script>
	<script type="text/javascript" src="<?php echo PATH_INC ?>js/libjsgen.js"></script>	
	<script type="text/javascript" src="<?php echo PATH_INC ?>tablesorter/jquery.tablesorter.js"></script>

	<script type="text/javascript" src="<?=PATH_INC?>js/jquerytablas.js"></script> <!-- Esta l�nea debe ir aqu� para luego de que se aplique el orden se refrescan los css de la tabla -->
    <?php $xajax->printJavascript(PATH_INC.'ajax/'); ?>
	<? verif_framework(); ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle($myClass->getTitle());

/* botones */
$button = new Button;
$button->addItem(" Regresar ","catalogosServicios_buscar.php".$param->buildPars(true));

echo $button->writeHTML();

/* Control de fichas, */
$abas = new Abas();
$abas->addItem("General",false,"catalogosServicios_edicion.php?id=$relacionamento_id&clear=1&".$param->buildPars(false));
$abas->addItem("Presentaciones",false,"catalogosServiciosPresentaciones_lista.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));        

if(inlist($servicio->field('segr_vinculo'),"5")){ //
        $abas->addItem("Imagenes",false,"catalogosServicios_imagenes.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));
}
$abas->addItem("Vinculados",false,"catalogosServiciosVinculados_lista.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));    

if(SIS_SISCONT==1){
    $abas->addItem("Asientos Contables",false,"catalogosServiciosCuentasContables_lista.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));        
}

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
$form->addField("C&oacute;digo:",$servicio->field('serv_id'));
$form->addField("Descripci&oacute;n: ",$servicio->field('serv_descripcion'));

$form->addHtml($myClass->buscar(2,$relacionamento_id));
echo  $form->writeHTML();
?>
</body>
</html>
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();