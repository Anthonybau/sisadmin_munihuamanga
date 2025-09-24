<?php
/*
	formulario de solicitud de parametros
*/
include("../../library/library.php");
include("../catalogos/catalogosTabla_class.php");

verificaUsuario(1);

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();
// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->setCharEncoding('utf-8');
$xajax->registerFunction("pideCateoria");

function pideCateoria($op,$value,$NameDiv)
{
	global $conn,$bd_care_id;

	$objResponse = new xajaxResponse();

	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");

        $sqlCategoria = "SELECT care_id as id, care_descripcion FROM categoria_remunerativa WHERE tabl_idsitlaboral=$value ORDER BY 2";
        $oForm->addField("Categor&iacute;a: ",listboxField("Categoria",$sqlCategoria, "nbusc_categoria",'',"-- Todas las Categorias --",""));

        $contenido_respuesta=$oForm->writeHTML();
        
	$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);

	if($op==1)
		return $objResponse;
	else
		return $contenido_respuesta;

}
$xajax->processRequests();
?>
<html>
<head>
	<title>Reportes-Index</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="<?php echo PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>
        
	<script language='JavaScript'>
            function mivalidacion(frm) {  
                    return true			
            }

            function inicializa() {
            }


            function imprimir(sURL,op) {
                    parent.content.document.frm.target = "controle";
                    parent.content.document.frm.action = sURL+'?destino='+op;
                    parent.content.document.frm.submit();
            }

	</script>
	<?php
        verif_framework(); 
        $xajax->printJavascript(PATH_INC.'ajax/');
        ?>		
    
</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("Par&aacute;metros de Impresi&oacute;n de Listado de Personal","");


/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",false);

$form->setLabelWidth("20%");
$form->setDataWidth("80%");


$form->addHidden("_titulo","LISTADO DE PERSONAL"); // Campo oculto con el T�tulo del reporte
$sUrl="rptListadoTrabajadores.php";

$sqlSituLabo = "SELECT tabl_id, tabl_descripcion as Descripcion
                    FROM catalogos.tabla WHERE tabl_tipo='CONDICION_LABORAL' ORDER BY 1 ";

$form->addField("Condici&oacute;n laboral:",listboxField("Situaci&oacute;n laboral",$sqlSituLabo, "nbusc_sitlaboral","","-- Todos --",""));


$lista_nivel = array("1,ACTIVO","9,DE BAJA"); // definici�n de la lista para campo radio
$form->addField("Estado: ",radioField("Estado",$lista_nivel, "xr_pers_activo",1,"","H"));

$form->addField("","<a href=\"#\" onClick=\"javascript:imprimir('$sUrl',1)\" ><img src='../../img/pdf.png'  border='0' title='Imprimir en PDF'></a>
                    &nbsp;&nbsp;
                    <a href=\"#\" onClick=\"javascript:imprimir('$sUrl',2)\" ><img src='../../img/xls.png'  border='0' title='Exportar a XLS'></a>");


echo $form->writeHTML();

?>
</body>
</html>
<?php
/* cierro la conexión a la BD */
$conn->close();