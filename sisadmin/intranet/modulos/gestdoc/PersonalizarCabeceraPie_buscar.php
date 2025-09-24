<?php
/* Modelo de pagina que apresenta um formulario con criterios de busqueda */
include("../../library/library.php");

/* verificacion del nivel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("PersonalizarCabeceraPie_class.php");
include("tamanoPagina_class.php");
include("../catalogos/catalogosDependencias_class.php");


/* establecer conexion con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$busEmpty = 1; //getParam("busEmpty"); // para definir si se muestran los datos en el primer ingreso

$param= new manUrlv1();
$param->removePar('clear');

$myClass = new personalizarCabeceraPie(0,"Personalizar Encabezado y Pie de P&aacute;gina");


if ($clear==1) {
	setSession("cadSearch","");
}

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "personalizarCabeceraPie","buscar"),"");
$xajax->processRequests();

// fin para Ajax
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<title><?php echo $myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

        <link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">        
        <script type="text/javascript" src="../../library/js/libjsgen.js"></script>
        <script type="text/javascript" src="../../library/js/checkall.js"></script>
        
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">        
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>       

        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">        
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>
        
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                
        
        <script type="text/javascript" src="../../library/tablesorter/jquery.tablesorter.js"></script>
        <script language="JavaScript">

                <?php echo $myClass->jsSorter(''); ?>

		function inicializa() {
			document.frm.Sbusc_cadena.focus();
		}
                
                function beforeEliminar() {
                        regSel=$("#tLista tbody input[type=checkbox]").is(":checked");
                        if(regSel){
                            $('#myModalElimina').modal('show');
                            $("#btnConfirmAceptar-myModalElimina").focus();
                        } else {
                            $('#msg-myModalAviso').text( "Seleccione un Registro");
                            $('#myModalAviso').modal('show');                                                                                            
                        }
                }
                    
                $(document).on("click", "#btnConfirmAceptar-myModalElimina", function () {
                        document.frm.target = "contole";
                        document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(2)."&".$param->buildPars(false) ?>";
                        document.frm.submit();
                });
                
	
	</script>
        <script type="text/javascript" src="../../library/js/jquerytablas3.js"></script>         
        <?php 
            $xajax->printJavascript(PATH_INC.'ajax/');
            verif_framework(); 
        ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle($myClass->getTitle());


/* formulario de busqueda */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->addHidden("rodou","s");

/* botones */
$button = new Button;
$button->addItem(" Nuevo ","PersonalizarCabeceraPie_edicion.php".$param->buildPars(true),"content");
//$button->addItem("Eliminar","javascript:beforeEliminar()","content",2);
$form->addHtml("<div class='row'>".$button->writeHTML().'</div>');

/* Instancio la Dependencia */
$sqlDependencia=new dependenciaSuperior_SQLBox3(getSession("sis_depe_superior"));
$sqlDependencia=$sqlDependencia->getSQL();        

$form->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia,"nbusc_depe_id","","-- Todas las Dependencia --","","","class=\"my_select_box\""));        
/* Fin Instancio */

$tamanoPagina=new tamanoPagina_SQLlista();
$tamanoPagina->orderUno();
$sqlTamanoPagina=$tamanoPagina->getSQL();
$form->addField("Tamaño de P&aacute;gina: ",listboxField("Tamaño de Pagina",$sqlTamanoPagina,"nbus_tapa_id","",'-- Tamaño de P&aacute;gina --',"","","class=\"my_select_box\"  "));

$form->addField("Pendientes de Personalizar: ",checkboxField("Pendientes de Personalizar","hx_busc_pendientes",1,0));

//array de parametros que se ingresaran a la funcion de busqueda de ajax
$paramFunction= new manUrlv1();
$paramFunction->removeAllPar();
$paramFunction->addParComplete('colSearch','');
$paramFunction->addParComplete('colOrden','1');
$paramFunction->addParComplete('busEmpty',$busEmpty);
$paramFunction->addParComplete('pageEdit',$myClass->getPageEdicion());

$button = new Button;
$button->setDiv(false);
$button->addItem("Buscar","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado');document.getElementById('DivResultado').innerHTML = 'Espere, Procesando...'","",2,0,"botonAgg","button");

$form->addField("C&oacute;digo/Descripci&oacute;n: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'' ,50,50)."&nbsp;".$button->writeHTML());

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");
$form->addHtml($myClass->buscar(2,'',encodeArray($paramFunction->getUrl()),1,'DivResultado'));
$form->addHtml("</div></td></tr>\n");

$dialog=new Dialog("myModalAviso","warning");
$dialog->setModal("modal-sm");//mediano
$form->addHtml($dialog->writeHTML());        

$dialog=new Dialog("myModalElimina","confirm");
$dialog->setModal("modal-sm");//mdiano
$dialog->addMessage("Seguro de Elimnar Registro?");
$form->addHtml($dialog->writeHTML());

echo  $form->writeHTML();
?>
</body>
</html>
    <script>
        $(".my_select_box").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true,
            width:'90%'
            });
    </script>

<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();