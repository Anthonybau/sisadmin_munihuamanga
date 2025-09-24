<?php
/* Modelo de p�gina que apresenta um formulario con crit�rios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("adminUsuario_perfilClass.php"); 
include("adminUsuario_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/* Recibo parámetros */
$relacionamento_id = getParam("relacionamento_id"); /* Recibo el dato de ralcionamiento entre la tabla padre e hijo */
$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente

/* Recibo los par�metros con la clase de "paso de par�metros por la URL"  */
$param= new manUrlv1();
$param->removePar('clear');

/* Instancio mi clase */
$myClass = new usuarioPerfil(0,"Perfiles de Usuario");


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
$xajax->registerExternalFunction(array("buscar", "usuarioPerfil","buscar"),"");
$xajax->processRequests();

// fin para Ajax
?>
<html>
<head>
	<title><?php echo $myClass->getTitle() ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT ?>">
	<link rel="stylesheet" href="../../library/thickbox/thickbox.css" type="text/css" media="screen" />
	<script type="text/javascript" src="../../library/jquery/jquerypack.js"></script>
	<script type="text/javascript" src="../../library/thickbox/thickbox.js"></script>
	<script type="text/javascript" src="../../library/js/libjsgen.js"></script>	
	<script type="text/javascript" src="../../library/tablesorter/jquery.tablesorter.js"></script>
	<script language="JavaScript">
		<?php echo $myClass->jsDevolver($nomeCampoForm); ?>
		<?php echo $myClass->jsSorter($nomeCampoForm); ?>		

		/* funcion que define el foco inicial del formulario */
		function inicializa() {
			$("#tLista tbody input[@type=checkbox]").removeAttr("checked"); /* Desmarco todos los checkbox, esto porque al insertar o editar pueden quedar algunos marcados, por eso al refrescar la p�g. se desmarcan todos */
			document.frm.Sbusc_cadena.focus();
		}
	
		function excluir() {
			regSel=$("#tLista tbody input[@type=checkbox]").is(":checked");
			if(regSel){ 
				if (confirm('Desea Eliminar el(los) registro(s) selecionado(s)?')) {
					document.frm.target = "controle";
					document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(2)."&".$param->buildPars(false)?>";
					document.frm.submit();
				}
			}else{
				alert('Seleccione el(los) registro(s) que desea eliminar')
			}
		}
		
		function abreEdicion(id,param) {
			/* Puedo agregar m�s par�metros separador por comas (,) antes de &height=500 */
			tb_show('', '../../library/auxiliar.php?pag=<?php echo $myClass->pagEdicion ?>'+param+',id='+id+'&height=<?php echo $myClass->winHeight ?>&width=<?php echo $myClass->winWidth ?>', null);			
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
	<script type="text/javascript" src="../../library/js/jquerytablas.js"></script> 
	<!-- Esta línea debe ir aquí para luego de que se aplique el orden se refrescan los css de la tabla -->
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

//CREO ESTA SEGUNDA VARIABLE, PARA ELIMINAR LA CADENA DE BUSQUEDA
$param2= new manUrlv1();
$param2->removePar('clear');
$param2->removePar('cadena');

$button->addItem("Agregar Perfil","javascript:abreEdicion(0,'".$param2->buildPars(true,',')."')","content",2);
$button->addItem("Eliminar","javascript:excluir()","content",2);
$button->addItem(" Regresar ",'adminUsuario_buscar.php'.$param->buildPars(true));

echo $button->writeHTML();

/* Control de fichas, */
$abas = new Abas();
$abas->addItem("General",false,"adminUsuario_edicion.php?id=$relacionamento_id&".$param->buildPars(false));
$abas->addItem("Perfiles",true);
echo $abas->writeHTML();
echo "<br>";
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
$paramFunction->addParComplete('busEmpty',1 );
$paramFunction->addParComplete('numForm',0);
$paramFunction->addParComplete('relacionamento_id',$relacionamento_id);
$array=$myClass->getArrayNameVar();
foreach($array as $k => $v) {$paramFunction->addParComplete($k,$v);}

/* T�tulo de la lista */
$users= new clsUsers_SQLlista();
$users->whereID($relacionamento_id);
$users->setDatos();
$empleado=$users->field('empleado');
$namePadre=$users->field('usua_login');
$form->addBreak("<b>Lista de Perfiles Para: $empleado [$namePadre]</b>\n",true,'6','center');

$form->addField("C&oacute;digo/Descripci&oacute;n: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",$cadena,50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">");

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
