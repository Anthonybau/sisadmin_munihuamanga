<?php
/* Modelo de p�gina que apresenta um formulario con criterios de busqueda */
include("../../library/library.php");

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("AFP_class.php");
include("AFPFactores_class.php");

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
$myClass = new clsAFPFactores(0,"Factores por AFP");

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
$xajax->registerExternalFunction(array("buscar", "clsAFPFactores","buscar"),"");
$xajax->processRequests();

// fin para Ajax
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script type="text/javascript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/janela.js"></script>        
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
	

                function abreEdicion(id_padre,id_relacion) {
                        parent.content.document.frm.refresh.value=1;
                        // la extensi�n, o separador "&" debe ser substituido por coma ","
                        abreJanelaAuxiliar('../modulos/catalogos/AFPFactores_edicion.php?id='+id_padre+',id_relacion='+id_relacion,600,550); 
                } 	

                function onRefresh(){
                        if(parent.content.document.frm.refresh.value==1){
                                parent.content.document.frm.refresh.value=0;
                                parent.content.location.reload()			
                        }
                }
                
		function excluir() {
			if (confirm('Eliminar registros seleccionados?')) {
				parent.content.document.frm.target = "controle";
				parent.content.document.frm.action = "<?=$myClass->getNameFile()."?control=".base64_encode(2)."&".$param->buildPars(false)?>";
				parent.content.document.frm.submit();
				}
		}
                
	</script>
	<script type="text/javascript" src="<?=PATH_INC?>js/jquerytablas.js"></script> <!-- Esta l�nea debe ir aqu� para luego de que se aplique el orden se refrescan los css de la tabla -->
        <?php $xajax->printJavascript(PATH_INC.'ajax/'); ?>
	<? verif_framework(); ?>

</head>
<body class="contentBODY" onLoad="inicializa()" onFocus="onRefresh()">
<?php
pageTitle($myClass->getTitle());

/* botones */
$button = new Button;
$button->addItem("Nuevo Factor","javascript:abreEdicion($relacionamento_id,0)","content",2);
/*INCORPORAR ACTIVAR/DESACTIVAR, en la BD todos estan activos*/
//$button->addItem("<img src='../../img/delete.gif' border='0'>&nbsp;"."Eliminar","javascript:excluir()","content",2);
$button->addItem("Datos de AFP","AFP_edicion.php?id=$relacionamento_id&".$param->buildPars(false));
$button->addItem("Ir a Lista de AFPs","AFP_buscar.php?clear=1");
echo $button->writeHTML();

//muestra los datos de la persona
$myPeriodo = new clsAFP($relacionamento_id);
$myPeriodo->setDatos();
$odatos = new AddTableForm();
$odatos->setLabelWidth("20%");
$odatos->setDataWidth("80%");
$odatos->addField("AFP: ",$myPeriodo->field("afp_nombre"));


echo $odatos->writeHTML();			


/* formul�rio de pesquisa */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s");
$form->addHidden("refresh",0);


//array de parametros que se ingresaran a la funcion de busqueda de ajax
$paramFunction= new manUrlv1();
$paramFunction->removeAllPar();
$paramFunction->addParComplete('colSearch','');
$paramFunction->addParComplete('busEmpty',1);
$paramFunction->addParComplete('numForm',0);
$paramFunction->addParComplete('relacionamento_id',$relacionamento_id);
$array=$myClass->getArrayNameVar();
foreach($array as $k => $v) {$paramFunction->addParComplete($k,$v);}


$form->addField("Fecha: ",textField("Fecha","Sbusc_cadena",$cadena,50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">");

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");

/* Creo array $formData con valores necesarios para filtrar la tabla */
$formData['Sbusc_cadena']=$cadena ;

$form->addHtml($myClass->buscar(2,$formData,encodeArray($paramFunction->getUrl()),$pg,'DivResultado'));


$form->addHtml("</div></td></tr>\n");
echo  $form->writeHTML();
?>
</body>
</html>
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();