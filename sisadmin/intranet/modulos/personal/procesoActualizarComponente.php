<?php
/*
	Modelo de p?gina que apresenta um formulario con crit?rios de busqueda
*/
include("../../library/library.php");

/* verificaci?n del n?vel de usu?rio */
verificaUsuario(1);

include("../catalogos/catalogosTabla_class.php");

/* establecer conexi?n con la BD */
$conn = new db();
$conn->open();


// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("Proceso");


function Proceso($formData)
{
	global $conn;

	$objResponse = new xajaxResponse();

        $anno=$formData['nr_periodo'];
	$usua_id=getSession("sis_userid");
        

        $sSql="SELECT personal.func_actualizar_componente('$anno','$usua_id')";
        $conn->execute($sSql);
        $error=$conn->error();
            
        
        //$objResponse->addAlert(utf8_encode($sSql));
            
	if($error){
		$objResponse->addAlert($error);
	}else{
		$notice=$conn->notice();
		if($notice) 
                    $objResponse->addAlert($notice);
		else		
                    $objResponse->addAlert(utf8_encode('El proceso se ha efectuado con Exito'));
	}

	$objResponse->addScript("$('#DivBus').hide()");	
	return $objResponse;
}

$xajax->processRequests();
// fin para Ajax


?>
<html>
<head>
	<title>Procesar Registro de Compras</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
        <script language="JavaScript" src="../../library/js/libjsgen.js"></script>
	<script type="text/javascript" src="../../library/jquery/jquerypack.js"></script>
        <script language="JavaScript">
		
	/*
		fun??o que define o foco inicial do formul?rio
	*/
	function inicializa() {
		if (document.captureEvents && Event.KEYUP) {
			document.captureEvents( Event.KEYUP);
		}
		document.onkeyup = trataEvent;

		// inicia o foco no primeiro campo
		parent.content.document.frm.nbusc_depe_id.focus();
	}

        function procesar(idObj) {
            if (ObligaCampos(frm)){
                ocultarObj(idObj,5);
                $('#DivBus').show();
                xajax_Proceso(xajax.getFormValues('frm'));
            }
	}
        
	function mivalidacion(){
            var sError="Mensajes del sistema: "+"\n\n";
            var nErrTot=0;

		if (nErrTot>0){
                    alert(sError)
                    eval(foco)
                    return false
		}else
                    return true
	}


	/*
		tratamento para capturar tecla enter
	*/
	function trataEvent(e) {
		if( !e ) { //verifica se ? IE
			if( window.event ) {
				e = window.event;
			} else {
				//falha, n?o tem como capturar o evento
				return;
			}
		}
		if( typeof( e.keyCode ) == 'number'  ) { //IE, NS 6+, Mozilla 0.9+
			e = e.keyCode;
		} else {
			//falha, n?o tem como obter o c?digo da tecla
			return;
		}
		if (e==13) {
			buscar();			
		}
	}
	
	
	
	</script>
    <!-- Este es la impresion de las rutinas JS que necesita Xajax para funcionar -->
        <?php 
        $xajax->printJavascript(PATH_INC.'ajax/');
	verif_framework(); 
	?>
		

</head>
<body class="contentBODY" onLoad="inicializa()" >
<div id='DivBus' align="right" style="width:100;background-color:#FFF1A8;font-size:16px; position:fixed; display:none " >Procesando....</div>
<?php
pageTitle("Actualizar Componentes en Empleados");

?>
<br>
<?php
/* formul?rio */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");


$sqlPeriodo = "SELECT peri_anno as id,peri_anno as descripcion FROM periodo order by peri_anno ";
$nbusc_periodo=getDbValue("SELECT peri_anno from periodo where peri_set='*' ");
$form->addField("Nuevo Periodo: ",listboxField("Nuevo Periodo",$sqlPeriodo,"nr_periodo","$nbusc_periodo","",""));

/* botones */
$button = new Button;
$button->setDiv(FALSE);
$button->addItem(" Proceder ","javascript:procesar('Proceder');");
$form->addField("",$button->writeHTML());

echo $form->writeHTML();
?>
</body>
</html>

<?php
/*
	cierra la conexion a la BD, no debe ser alterado
*/
$conn->close();