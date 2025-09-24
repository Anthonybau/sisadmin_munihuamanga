<?
/* formulario de ingreso y modificaci?n */
include("../../library/library.php");

/* verificaci?n del n?vel de usuario */
verificaUsuario(1);

/* establecer conexi?n con la BD */
$conn = new db();
$conn->open();

$TipDocum=getParam('TipDocum');

// Inicio Para Ajax
require_once("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->setCharEncoding('utf-8');
$xajax->registerFunction("cargar");
$xajax->registerFunction("muestraSecuencia");
$xajax->registerFunction("compilaSecuencia");

function cargar($op,$periodo,$NameDiv)
{
	global $conn,$TipDocum;
	$objResponse = new xajaxResponse();
	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");
	
	//$periodo=$formData["nr_peri_anno"];
	
	$sqlDocumentos="SELECT * FROM catalogos.func_secuencias($periodo,'$TipDocum')";
        //ECHO $sqlDocumentos;
	$oForm->addField("Correlativo: ",listboxField("Correlativo",$sqlDocumentos,"nbusc_correla",'',"-- Seleccione Correlativo --","onChange=xajax_muestraSecuencia(this.value,'DivMuestraSecuencia',1)"));

	$contenido_respuesta=$oForm->writeHTML();

	$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);	
        
        if($op==1)
            return $objResponse;
        else 
            return $contenido_respuesta;
}

function muestraSecuencia($secuencia,$NameDiv)
{
	global $conn;
	$objResponse = new xajaxResponse();
	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");
	$button = new Button;
	$button->setDiv(false);
	$button->addItem(" Actualizar ","if(ObligaCampos(frm)){xajax_compilaSecuencia('$secuencia',document.frm.nr_nextValor.value,'DivMuestraSecuencia')}","",2,0,"botonAgg","button");
	//alert($secuencia);
	$nextValue=$conn->currval($secuencia);
	
	$oForm->addField("Valor Siguiente: ",numField("Valor Siguiente","nr_nextValor",$nextValue,8,8,0)."&nbsp;".$button->writeHTML());	
	$contenido_respuesta=$oForm->writeHTML();

	$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);	

	return $objResponse;
}

function compilaSecuencia($secuencia,$nextValue,$NameDiv)
{
	global $conn;

	$objResponse = new xajaxResponse();
	$objResponse->setCharEncoding('utf-8');	
	
	$conn->setval($secuencia,$nextValue);
	
	$error=$conn->error();
	if($error) {
		//si hay error se asume que no esta creada la secuencia, entonces se procede a crearla
		$conn->nextid($secuencia);			
		$error=$conn->error();
		if($error) {
			$objResponse->addAlert($error);
			return $objResponse;		
		}
		else{
			//la siguiente linea es recomendable para evitar incosistencias en la instruccion $conn->curval
			$conn->setval($secuencia,$nextValue);
	
			$error=$conn->error();
			if($error) {
				$objResponse->addAlert($error);
				return $objResponse;		
			}
		}
	}

	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");
	$oForm->addField("","ACTUALIZACION REALIZADA...!!");	   
	$contenido_respuesta=$oForm->writeHTML();


	$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);	

	return $objResponse;
}

$xajax->processRequests();
// fin para Ajax

?>
<html>
<head>
	<title>Correlativo de Documentos</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="JavaScript" src="<?=PATH_INC?>js/focus.js"></script>	
	<script language="JavaScript" src="<?=PATH_INC?>js/libjsgen.js"></script>			
	<script language='JavaScript'>
	function mivalidacion(frm) {  
		var sError="Mensajes del sistema: "+"\n\n"; 	
		var nErrTot=0; 	 
		if (frm.nr_nextValor.value<=0) {
			   foco="frm.nr_nextValor.focus()"			   
			   sError+="Valor del campo 'Valor Siguiente' no Valido "+"\n" 
			   nErrTot+=1;
		}

		if (nErrTot>0){ 		
			alert(sError)
			eval(foco)			
			return false
		}else
			return true			
	}
	
	function inicializa() {
		parent.content.document.frm.tr_depe_id.focus();
	}
	</script>
    <? 
	$xajax->printJavascript(PATH_INC.'ajax/'); 
	verif_framework(); 
	?>			
</head>
<body class="contentBODY"  onLoad="inicializa()">
<? 
pageTitle("Inicializaci&oacute;n de N&uacute;mero de Documento","");
echo "<br><br>";
/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$nperiodoset=getDbValue("select peri_anno from periodo where peri_set='*'"); // Obtengo el periodo actual seteado para el sistema
$sqlPeriodo = "select peri_anno as id,peri_anno as descripcion from periodo order by peri_anno ";
$form->addField("Periodo: ",listboxField("Periodo",$sqlPeriodo,"nr_peri_anno",$nperiodoset,"-- Seleccione Periodo --","onChange=xajax_cargar(1,this.value,'DivTipDocs')"));

$form->addHtml("<tr><td colspan=2><div id='DivTipDocs'>\n");
$form->addHtml(cargar(2,"$nperiodoset",'DivTipDocs'));
$form->addHtml("</div></td></tr>\n");

$form->addHtml("<tr><td colspan=2><div id='DivMuestraSecuencia'>\n");
$form->addHtml("</div></td></tr>\n");
echo $form->writeHTML();
?>
</body>
</html>
<?
/* cierro la conexi?n a la BD */
$conn->close();
wait('');