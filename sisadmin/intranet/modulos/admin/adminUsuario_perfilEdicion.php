<?php
/* formulario de ingreso y modificaci�n */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("adminUsuario_perfilClass.php"); 
include("adminUsuario_class.php");

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

/* Recibo par�metros */
$relacionamento_id = getParam("relacionamento_id"); /* Recibo el dato de ralcionamiento entre la tabla padre e hijo */
$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente

/* Recibo los par�metros con la clase de "paso de par�metros por la URL"  */
$param= new manUrlv1();
$param->removePar('clear');

/* Instancio mi clase */
$myClass = new usuarioPerfil(0,"Agregar Perfil a Usuario");


$nomeCampoForm=getParam($myClass->getArrayNameVarID(0));
$busEmpty = getParam($myClass->getArrayNameVarID(1)); // 1->en la primera llamada se muestran los registros 0->en la primera llamada no se muestran los registros 
$cadena= getParam($myClass->getArrayNameVarID(2)); // cadena de busqueda
$pg = getParam($myClass->getArrayNameVarID(3)); // Tipo de Clase 
$pg = $pg?$pg:1;


// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("busAgregar", "usuarioPerfil","busAgregar"),"");
$xajax->registerFunction("addPerfil");

function addPerfil($perf_id)
{
	global $param,$conn,$relacionamento_id;
	
	$objResponse = new xajaxResponse();
	
	$usua_id=getSession("sis_userid");
	
	$sql = "INSERT INTO usuario_perfil (usua_id,perf_id,usua_idcrea)
						VALUES ($relacionamento_id,$perf_id,$usua_id)";
	$conn->execute($sql);
	$error=$conn->error();
	if($error){ 
		$objResponse->alert($error);	/* Muestro el error y detengo la ejecuci�n */
	}
	
	$objResponse->addScript("self.parent.tb_remove();");
        $objResponse->addScript("self.parent.document.location='adminUsuario_perfilLista1n.php?relacionamento_id=$relacionamento_id&clear=1&".$param->buildPars(false)."'");

	return $objResponse;	
}

$xajax->processRequests();

// fin para Ajax
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script type="text/javascript" src="<?php echo PATH_INC?>jquery/jquerypack.js"></script>
	<script type="text/javascript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>	
	
	<script language='JavaScript'>
	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
			parent.content.document.frm.submit();
		}
	}
	/* se invoca desde la funcion obligacampos (libjsgen.js) */
	function mivalidacion(){
		return true
	}

	function openList(key) {
		var oKey = parent.content.document.getElementById(key);
		var icone = parent.content.document.getElementById('fold_'+key);
		if (oKey.style.visibility == "hidden"){
			oKey.style.visibility = "visible";
			oKey.style.display = "block";
			icone.innerHTML = "&nbsp;-&nbsp;";
			
		} else {
			oKey.style.visibility = "hidden";
			oKey.style.display = "none";
			icone.innerHTML = "&nbsp;+&nbsp;";
		}
	}
	
	</script>
	<script type="text/javascript" src="<?php echo PATH_INC?>js/jquerytablas.js"></script>	
	<?php $xajax->printJavascript(PATH_INC.'ajax/'); ?>	
	<?php verif_framework(); ?>
</head>
<body class="contentBODY"  >
<?php
pageTitle($myClass->getTitle(),"Edici&oacute;n");

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

/* T�tulo de la lista */
$users= new clsUsers($relacionamento_id);
$users->setDatos();
$namePadre=$users->field('usua_login');
$form->addBreak("<b>Lista de Perfiles Disponibles para $namePadre</b>\n",true,'6','center');

$form->addField("C&oacute;digo/Descripci&oacute;n: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",$cadena,50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_busAgregar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">");

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");

/* Creo array $formData con valores necesarios para filtrar la tabla */
$formData['Sbusc_cadena']=$cadena ;

$form->addHtml($myClass->busAgregar(2,$formData,encodeArray($paramFunction->getUrl()),$pg,'DivResultado'));

$form->addHtml("</div></td></tr>\n");
echo  $form->writeHTML();

?>
</body>
</html>
<?php
/* cierro la conexi�n a la BD */
$conn->close();
