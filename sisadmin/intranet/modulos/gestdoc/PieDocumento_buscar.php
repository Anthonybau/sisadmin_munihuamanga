<?php
include("../../library/library.php");
/* Cargo mi clase Base */
include("PieDocumento_class.php"); 


/* verificacion a nivel de usuario */
verificaUsuario(1);

/* establecer conexion con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente

$param= new manUrlv1();
$param->removePar('clear');

$myClass = new PieDocumento(0,"B&uacute;queda de Pie de Documentos");

$nomeCampoForm=getParam($myClass->getArrayNameVarID(0));
$busEmpty = getParam($myClass->getArrayNameVarID(1)); // 1->en la primera llamada se muestran los registros 0->en la primera llamada no se muestran los registros 
$cadena= getParam('Sbusc_cadena');
$periodo= getParam('periodo');
$pg = getParam($myClass->getArrayNameVarID(3)); // Tipo de Clase 
$pg = $pg?$pg:1;

/*
	limpia la cadena de filtro
	si clear=1 -> esta pagina es llamada desde el menu
	si clear=2 -> esta pagina es llamada desde la busqueda avanzada (AvanzLookup)
*/
if ($clear==1) {
    setSession("cadSearch","");
    // DEFINO MIS VARIABLES PREDETERMINADAS
}


// Inicio Para Ajax
require_once("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "PieDocumento","buscar"),"");

$xajax->processRequests();

// fin para Ajax
?>
<html>
    <head>
        <title><?php echo $myClass->getTitle()?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT ?>">
        <script type="text/javascript" src="../../library/js/libjsgen.js"></script>
        <script language="javascript" src="../../library/js/checkall.js"></script>
        
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>           
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>
        
        <link rel="stylesheet" href="<?php echo PATH_INC ?>select2/dist/css/select2.css">
        <script src="<?php echo PATH_INC ?>select2/dist/js/select2.js" type="text/javascript"></script>                

                <script language="JavaScript">

                    /* funcion que define el foco inicial del formulario */
                    function inicializa() {
                        document.frm.Sbusc_cadena.focus();
                    }

                    function excluir() {
                        regSel=$("#tLista tbody input[type=checkbox]").is(":checked");
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


                    function mivalidacion(frm) {
                        return true
                    }

                    $(document).ready(function() {
                        $('.ls-modal').on('click', function(e){
                            e.preventDefault();
                            $('#myModal').modal('show').find('.modal-body').load($(this).attr('href'));
                        }); 
                    });            

                    function nuevo(){
                        document.location = "PieDocumento_edicion.php<?php echo $param->buildPars(true)?>";
                    }   

                    function activar() {
                            if (confirm('Activar/Desactivar registros seleccionados?')) {
                                parent.content.document.frm.target = "controle";
                                parent.content.document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(3)."&".$param->buildPars(false)?>";
                                parent.content.document.frm.submit();
                            }
                    }

        </script>
        <script type="text/javascript" src="../../library/js/jquerytablas3.js"></script> <!-- Esta l?nea debe ir aqu? para luego de que se aplique el orden se refrescan los css de la tabla -->

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
        $button->addItem("Agregar Plantilla","javascript:nuevo()","content",2);
        $button->addItem("Eliminar","javascript:excluir()","content",2);
        $button->addItem("Activar/Desactivar","javascript:activar()","content",2);
        echo $button->writeHTML();

/* formul?rio de pesquisa */
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
        $paramFunction->addParComplete('clear',$clear);
        $paramFunction->addParComplete('busEmpty',$busEmpty);
        $paramFunction->addParComplete('numForm',0);
        $paramFunction->addParComplete('pageEdit',$myClass->getPageEdicion());

        $array=$myClass->getArrayNameVar();
        foreach($array as $k => $v) {$paramFunction->addParComplete($k,$v);}
        
        $form->addField("N&uacute;mero/Descripci&oacute;n: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",$cadena ,50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">");

        $form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");

    /* Creo array $formData con valores necesarios para filtrar la tabla */
            $formData['Sbusc_cadena']=$cadena ;

            $form->addHtml($myClass->buscar(2,$formData,encodeArray($paramFunction->getUrl()),$pg,'DivResultado'));

        $form->addHtml("</div></td></tr>\n");
        echo  $form->writeHTML();
        ?>
        
        <div id="myModal" class="modal fade">   
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close"  data-dismiss="modal" aria-hidden="true">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>Loading...</p>
                        </div>

                </div>
            </div>    
        </div>                        
    </body>
</html>
    <script>
        $(".my_select_box").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true
            });
    </script>    
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();