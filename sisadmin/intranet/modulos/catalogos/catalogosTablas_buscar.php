<?php
/*
	Modelo de página que apresenta um formulario con crit�rios de busqueda
*/
include($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");
include("ajaxExternalFunction.php");

/*
	verificaci�n del n�vel de usu�rio
*/
verificaUsuario(1);

/*
	establecer conexi�n con la BD
*/
$conn = new db();
$conn->open();

$id = getParam("id"); // captura la variable que viene del objeto nuevo
$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$table = getParam("table");//nombre del tipo de tabla, este dato es del tipo numerico
$nuevo = getParam("nuevo");//habilita o no el boton 'nuevo'
$busEmpty=getParam("busEmpty")?1:0; //permite (1) � NO (0) mostrar todos los registros de la tabla
$dbrev = getParam("dbrev")?1:0;//muestra (1) � NO(0) no el campo 'descripcion breve'
$colOrden = getParam("colOrden")?getParam("colOrden"):2;//columna por la cual se ordenara la consulta
$setCodigo = getParam("setCodigo")?1:0;
$numForm = getParam("numForm")?getParam("numForm"):0;//funciona solo con CLEAR=2, es el numero de formulario en el cual se encuentra el objeto desde donde fue llamado

/*
	limpia la cadena de filtro
	si clear=1 -> esta pagina es llamada desde el menu
	si clear=2 -> esta pagina es llamada desde la busqueda avanzada (AvanzLookup)
*/
if ($clear==1) {
	setSession("cadSearch","");
}

if ($table) {
	setSession("table",$table);	
}

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("busqueda_Parametro"); 
$xajax->processRequests();
// fin para Ajax


?>

<html>
<head>
	<title>Tablas-Buscar</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT ?>">
	<script language="javascript" src="<?php echo PATH_INC ?>js/checkall.js"></script>		
	<script language="JavaScript" src="<?php echo PATH_INC ?>js/libjsgen.js"></script>
	<script language="JavaScript">

	<?php
	if ($clear==2) { 
		/* funcion q se activa si esta pagina es llamada desde la busqueda avanzada (AvanzLookup) */
		echo "function update(valor, descricao, numForm) {
				parent.opener.parent.parent.content.document.forms[numForm]._Dummy".getParam("nomeCampoForm").".value = descricao;
				parent.opener.parent.parent.content.document.forms[numForm].".getParam("nomeCampoForm").".value = valor;
				parent.opener.parent.parent.content.document.forms[numForm].__Change_".getParam("nomeCampoForm").".value = 1; 				
				parent.parent.close();
				}";
	} 
	?>
	/*
		funcion que define el foco inicial del formulario
	*/
	function inicializa() {
		if (document.captureEvents && Event.KEYUP) {
			document.captureEvents( Event.KEYUP);
		}
		document.onkeyup = trataEvent;

		// inicia el foco en el primer campo
		parent.content.document.frm.Sbusc_cadena.focus();
	}

	function excluir() {
		if (confirm('Eliminar registros seleccionados?')) {
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "../imes/eliminar.php?_op=CatTabla&busEmpty=<?php echo $busEmpty?>&colOrden=<?php $colOrden?>&dbrev=<?php $dbrev?>&setCodigo=<?php $setCodigo?>";
			parent.content.document.frm.submit();
			}
		}

	/*
		tratamento para capturar tecla enter
	*/
	function trataEvent(e) {
		if( !e ) { //verifica se � IE
			if( window.event ) {
				e = window.event;
			} else {
				//falha, n�o tem como capturar o evento
				return;
			}
		}
		if( typeof( e.keyCode ) == 'number'  ) { //IE, NS 6+, Mozilla 0.9+
			e = e.keyCode;
		} else {
			//falha, n�o tem como obter o c�digo da tecla
			return;
		}
/*		if (e==13) {
			buscar();
		} */	
	}
	</script>
    <?php $xajax->printJavascript(PATH_INC.'ajax/');
	  verif_framework(); ?>	
	
</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
switch(getSession("table")){
    case '9':
        $nameTable='SITUACION LABORAL';
        break;

    default :
        $nameTable=getSession("table");
        break;
}

//$nameTable=getDbValue("select tabl_tiponombre from tabla where tabl_tipo=".getSession("table"));
pageTitle("Tabla: ".$nameTable);

/*
	botones,
*/
$button = new Button;
if ($clear==2) 
	$button->addItem(LOOKUP_RESET,"javascript:update('','',$numForm)","content",0,0,"link"); //cambio el stylo solo a este boton

$valClear=$clear?$clear:1;
if($valClear==1 or ($valClear==2 && $nuevo==1))
	$button->addItem(" Nuevo C&oacute;digo ","catalogosTablas_edicion.php?clear=$valClear&nomeCampoForm=".getParam("nomeCampoForm")."&busEmpty=$busEmpty&colOrden=$colOrden&dbrev=$dbrev&setCodigo=$setCodigo&numForm=$numForm","content",2);

if($valClear==1) //el eliminar solo funciona cuando es llamado desde euna opcion del menu
	$button->addItem("Eliminar","javascript:excluir()","content",2);


echo $button->writeHTML();

// define la expresi�n SQL para la funci�n lista visualizada en un emergente

/* formul�rio de pesquisa */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s");


$form->addField("C&oacute;digo/Descripci&oacute;n: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',30,50)."&nbsp;<input type=\"button\" onClick=\"xajax_busqueda_Parametro(1,document.frm.Sbusc_cadena.value,'',$colOrden,$busEmpty,$numForm,'DivResultado')\" value=\"Buscar\">");

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");

if($clear==2){//si es llamada de una busqueda avanzada
	$form->addHtml(busqueda_Parametro(2,'','',$colOrden,$busEmpty,$numForm,'DivResultado'));	
}else{
	$form->addHtml(busqueda_Parametro(2,getSession("cadSearch"),'',$colOrden,$busEmpty,$numForm,'DivResultado'));
}
$form->addHtml("</div></td></tr>\n");
echo  $form->writeHTML();

?>
</body>
</html>

<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();