<?php
/* Modelo de p�gina que apresenta um formulario con crit�rios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("catalogosArchivadores_class.php");
include("../catalogos/catalogosDependencias_class.php");


/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$busEmpty = 1; //getParam("busEmpty"); // para definir si se muestran los datos en el primer ingreso
$depeid=getParam("nbusc_depe_id");

$param= new manUrlv1();
$param->removePar('clear');

$myClass = new archivador(0,"Archivadores");


if ($clear==1) {
    setSession("cadSearch","");
}

if(!$depeid)
    $depeid=getSession("sis_depeid");

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "archivador","buscar"),"");
$xajax->processRequests();

// fin para Ajax
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="javascript" src="../../library/js/checkall.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>
        
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>
        
	<script language="JavaScript">

		function inicializa() {
			document.frm.Sbusc_cadena.focus();
		}
	
		function excluir() {
			if (confirm('Eliminar registros seleccionados?')) {
				parent.content.document.frm.target = "controle";
				parent.content.document.frm.action = "<?=$myClass->getNameFile()."?control=".base64_encode(2)."&".$param->buildPars(false)?>";
				parent.content.document.frm.submit();
				}
			}
	</script>
    <?php $xajax->printJavascript(PATH_INC.'ajax/'); 
	verif_framework(); ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("B&uacute;squedas de ".$myClass->getTitle());

/* botones */
$button = new Button;

$button->addItem(" Nuevo ","catalogosArchivadores_edicion.php".$param->buildPars(true),"content");
$button->addItem("Eliminar","javascript:excluir()","content",2);

echo $button->writeHTML();

/* formulario de pesquisa */
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
$paramFunction->addParComplete('nbusc_depe_id',$depeid);


/* Instancio la Dependencia */
$dependencia=new dependencia_SQLlista();
if(getSession("sis_userid")>1){
    $dependencia->whereVarios(getSession("sis_persid"));    
    //$dependencia->whereID($depeid);
    $todos="";
}else{
    $todos="--Seleccione Dependencia--";
}
$sqlDependencia=$dependencia->getSQL_cbox();

//FIN OBTENGO
$form->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia,"nbusc_depe_id",$depeid,"$todos","onChange=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" ","","class=\"my_select_box\"")); 

$form->addField("C&oacute;digo/Descripci&oacute;n: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">");

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");

$form->addHtml($myClass->buscar(2,'',encodeArray($paramFunction->getUrl()),1,'DivResultado'));

$form->addHtml("</div></td></tr>\n");
echo  $form->writeHTML();
?>
</body>
<script>
        $(".my_select_box").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true
        });        
</script>
</html>
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();