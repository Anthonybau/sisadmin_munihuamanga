<?php
/* formulario de ingreso y modificacion */
include("../../library/library.php");

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("setearPeriodo_class.php"); 

$clear=getParam('clear');

$myClass = new setPeriodo(0,'Setar Periodo');
/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("muestraValores");

function muestraValores($clear,$id,$NameDiv)
{
	global $conn;

	$objResponse = new xajaxResponse();

        if($clear==1){
            $bd_periodo_nombre=getDbValue("SELECT peri_anno_nombre FROM periodo WHERE peri_anno=$id");
            $objResponse->addScript("document.frm.Sx_periodo_nombre.value='$bd_periodo_nombre'");
        }
        
        $objResponse->addScript("document.frm.f_id.value=$id");
        
    return $objResponse;
}

$xajax->processRequests();
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
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
		document.frm.xrxperiodo.focus();
	}
	
        </script>
	<?php
        $xajax->printJavascript(PATH_INC.'ajax/');
        verif_framework(); 
        ?>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("SELECCIONAR PERIODO POR DEFECTO");

echo "<br>";

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");


if($clear==1){
    $sqlPeriodo = "SELECT peri_anno, peri_anno||' '||COALESCE(peri_set,'') AS descripcion FROM periodo ORDER BY 1 ";
    $bd_periodo=getDbValue("SELECT peri_anno FROM periodo WHERE peri_set='*'");
    $bd_periodo_nombre=getDbValue("SELECT peri_anno_nombre FROM periodo WHERE peri_set='*'");
    $form->addField("Periodo: ",listboxField("Periodo",$sqlPeriodo, "xrxperiodo",$bd_periodo,'--Seleccione Periodo--',"onChange=\"xajax_muestraValores('$clear',this.value,'DivResultado')\""));
    $form->addField("Nombre del Año: ",textField("Nombre del annno","Sx_periodo_nombre",$bd_periodo_nombre,100,120));
    
}elseif($clear==2){//PERIODO PARA INVENTARIOS
    $sqlPeriodo = "SELECT peri_anno, peri_anno||' '||COALESCE(peri_set_inventario,'') AS descripcion FROM periodo ORDER BY 1 ";
    $bd_periodo=getDbValue("SELECT peri_anno FROM periodo WHERE peri_set_inventario='*'");
    $form->addField("Periodo: ",listboxField("Periodo",$sqlPeriodo, "xrxperiodo",$bd_periodo,'--Seleccione Periodo--',"onChange=\"xajax_muestraValores('$clear',this.value,'DivResultado')\""));
    
}elseif($clear==3){//PERIODO PARA CUADRO DE NECESIDADES
    $sqlPeriodo = "SELECT peri_anno, peri_anno||' '||COALESCE(peri_set_cdronec,'') AS descripcion FROM periodo ORDER BY 1 ";
    $bd_periodo=getDbValue("SELECT peri_anno FROM periodo WHERE peri_set_cdronec='*'");
    $form->addField("Periodo: ",listboxField("Periodo",$sqlPeriodo, "xrxperiodo",$bd_periodo,'--Seleccione Periodo--',"onChange=\"xajax_muestraValores('$clear',this.value,'DivResultado')\""));
}

$form->addHidden("f_id",$bd_periodo); // clave primaria
$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");
$form->addHtml("</div></td></tr>\n");

/* botones */
$button = new Button;
$button->setDiv(false);
$button->addItem(" Marcar por Defecto ","javascript:salvar('Marcar por Defecto')","content",2);

$form->addField("",$button->writeHTML());

        
echo $form->writeHTML();
?>
</body>
</html>

<?php
/* cierro la conexion a la BD */
$conn->close();