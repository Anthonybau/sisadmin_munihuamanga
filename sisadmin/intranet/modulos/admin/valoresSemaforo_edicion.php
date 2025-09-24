<?php
/* formulario de ingreso y modificacion */
include("../../library/library.php");

/* verificaci�n del nivel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("valoresSemaforo_class.php"); 

$clear=getParam('clear');

/* establecer conexion con la BD */
$conn = new db();
$conn->open();


$myClass = new datosAplicativo(1,'VALORES PARA SEMAFORO');
$myClass->setDatos();
if($myClass->existeDatos()){
    $bd_apli_id = $myClass->field('apli_id');
    $bd_apli_max_semaforo1 = $myClass->field('apli_max_semaforo1');
    $bd_apli_max_semaforo2 = $myClass->field('apli_max_semaforo2');    
    $bd_apli_actualfecha = $myClass->field('apli_actualfecha');    
}
    

/*recibo los parametro de la URL*/
$param= new manUrlv1();

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();


$xajax->processRequests();
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="<?php echo PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>
	
	<script language='JavaScript'>
	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
			parent.content.document.frm.submit();
		}
	}
	/*
		se invoca desde la funcion obligacampos (libjsgen.js)
		en esta funci�n se puede personalizar la validaci�n del formulario
		y se ejecuta al momento de gurdar los datos
	*/
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
		funci�n que define el foco inicial en el formulario
	*/
	function inicializa() {
		document.frm.nr_apli_max_semaforo1.focus();
	}
	</script>
        
        <?php
        $xajax->printJavascript(PATH_INC.'ajax/'); 
        verif_framework(); 
        ?>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle($myClass->getTitle());

$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",3);
echo $button->writeHTML();

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("f_id",$bd_apli_id); // clave primaria

$form->addField("Valor M&aacute;ximo P/Sem&aacute;foro en el Nivel Optimo:",numField("Valor Maximo P/Semaforo en Nivel Optimo","nr_apli_max_semaforo1",$bd_apli_max_semaforo1,6,6,0));
$form->addField("Valor M&aacute;ximo P/Sem&aacute;foro en el Nivel Intermedio:",numField("Valor Maximo P/Sem&aacute;foro en Nivel Intermedio","nr_apli_max_semaforo2",$bd_apli_max_semaforo2,6,6,0));

$form->addBreak("<b>CONTROL</b>");
$form->addField("Actualizado: ",substr($bd_apli_actualfecha,0,19));

echo $form->writeHTML();

?>
</body>
</html>

<?php
/* cierro la conexion a la BD */
$conn->close();