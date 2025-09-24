<?php
/* Modelo de página que apresenta um formulario con criterios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);


include("misSeguimientos_class.php");
include("../catalogos/catalogosTipoExpediente_class.php");
include("registroDespacho_class.php");
include("registroDespacho_edicionAdjuntosClass.php");
include("registroDespachoEnvios_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$busEmpty = 1; //getParam("busEmpty"); // para definir si se muestran los datos en el primer ingreso

$param= new manUrlv1();
$param->removePar('clear');

$myClass = new misSeguimientos(0,"Mis Seguimientos de ".NAME_EXPEDIENTE);


if ($clear==1) {
    setSession("cadSearch","");
}

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "misSeguimientos","buscar"),"");
$xajax->registerExternalFunction(array("verDetalle", "despacho","verDetalle"),"");
$xajax->registerExternalFunction(array("seguir", "despacho","seguir"),"");

$xajax->registerFunction("eliminarSeguimiento");

function eliminarSeguimiento($id)
{
    global $conn;
    $objResponse = new xajaxResponse();
    
    $usua_id=getSession("sis_userid");
    
    $sSql="DELETE FROM gestdoc.despachos_seguimiento WHERE desp_id=$id AND usua_id=$usua_id";

    // Ejecuto el string
    $conn->execute($sSql);
    $error=$conn->error();

    if($error){ 
        $objResponse->addAlert($error);
    }else{
        $objResponse->addAlert("Seguimiento $id eliminado!");
        $objResponse->addScript("parent.content.location.reload()");
    }
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
	<script language="javascript" src="../../library/js/checkall.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>
        <script type="text/javascript" src="../../library/jquery/jquery-1.9.js"></script>   
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>                
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                
        <script type="text/javascript" src="../../library/tablesorter/jquery.tablesorter.js"></script>        
        <style>
            #lectorPDF{
              width: 95% !important;
            }
        </style>
    
	<script language="JavaScript">
        
        <?php echo $myClass->jsSorter($nomeCampoForm);?>
            
        function inicializa() {
                document.frm.Sbusc_cadena.focus();
        }

        function AbreVentana(sURL){
                var w=720, h=600;
                venrepo=window.open(sURL,'rptSalida', "status=yes,resizable=yes,toolbar=no,scrollbars=yes,top=50,left=150,width=" + w + ",height=" + h, 1 );
                venrepo.focus();
        }


        function abreConsulta(id) {
            AbreVentana('../../../portal/gestdoc/consultarTramiteProceso.php?nr_numTramite=' + id+'&vista=NoConsulta');
        }
        
        function imprimir(id) {
            AbreVentana('rptDocumento.php?id=' + id);
        }        
        
        function verFile(file) {
            AbreVentana(file);
        }   
        
       

	</script>
        <script type="text/javascript" src="../../library/js/jquerytablas3.js"></script>        
        
        <style type="text/css">
            <!--
            .DataFONT {
                font-size: 8pt;
                color: #000000;
                font-family: Verdana, Arial, Tahoma, Helvetica
                }

            input, textarea, select{
                    font-family: Verdana, Arial, Helvetica;
                    font-size: 8pt;
                    color: #000000;
            }
            .checkbox{
                    margin: 1pt;
            }
            -->
            
        </style>
    <?php 
        $xajax->printJavascript(PATH_INC.'ajax/');
	verif_framework(); 
        ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle($myClass->getTitle());
/* botones */

/* formulario de pesquisa */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s");


$texp=new clsTipExp_SQLlista();
$texp->orderUno();
$sqltipo=$texp->getSQL_cbox();
$form->addField("Tipo de Documento:",listboxField("Tipo de Documento",$sqltipo,"nbusc_tiex","","-- Todos --","","","class=\"my_select_box\"")); 


//array de parametros que se ingresaran a la funcion de busqueda de ajax
$paramFunction= new manUrlv1();
$paramFunction->removeAllPar();
$paramFunction->addParComplete('colSearch','');
$paramFunction->addParComplete('colOrden','1');
$paramFunction->addParComplete('busEmpty',$busEmpty);
$paramFunction->addParComplete('nbusc_depe_id',$depe_id);
$paramFunction->addParComplete('nbusc_user_id',$user_id);

$form->addField("Exp/N&uacute;m.".NAME_EXPEDIENTE."/Asunto: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">");

$form->addHtml("<tr><td colspan=2>");
$form->addHtml("<table width=\"100%\"><tr valign=\"top\">");
$form->addHtml("<td><div id='DivResultado'>\n");
$form->addHtml($myClass->buscar(2,'',encodeArray($paramFunction->getUrl()),1,'DivResultado'));
$form->addHtml("</div></td>");
$form->addHtml("<td><div id='DivDetalles'>");
$form->addHtml("</div></td>");
$form->addHtml("</tr></table>\n");
$form->addHtml("</td></tr>");


$lectorPDF=new lectorPDF();
$form->addHtml($lectorPDF->writeHTML());

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