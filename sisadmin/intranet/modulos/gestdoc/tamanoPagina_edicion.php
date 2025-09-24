<?php
/* formulario de ingreso y modificaci�n */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("tamanoPagina_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$clear = getParam("clear"); 

$myClass = new tamanoPagina($id,'Tamaño de P&aacute;gina');

if (strlen($id)>0) { // edición
	$myClass->setDatos();
	if($myClass->existeDatos()){
            $bd_tapa_id = $myClass->field('tapa_id');
            $bd_tapa_nombre = $myClass->field('tapa_nombre');
            $bd_tapa_ancho = $myClass->field('tapa_ancho');
            $bd_tapa_alto = $myClass->field('tapa_alto');
            $bd_tapa_top = $myClass->field('tapa_top');
            $bd_tapa_left = $myClass->field('tapa_left');
            $bd_tapa_right = $myClass->field('tapa_right');
            $bd_tapa_botom = $myClass->field('tapa_botom');
            $bd_tapa_header = $myClass->field('tapa_header');
            $bd_tapa_footer = $myClass->field('tapa_footer');
            $bd_tapa_header_img = $myClass->field('tapa_header_img');
            $bd_tapa_footer_img = $myClass->field('tapa_footer_img');            
            $bd_tapa_fregistro = $myClass->field('tapa_fregistro');
            $bd_usua_id = $myClass->field('usua_id');
            $bd_tapa_fregistroactual = $myClass->field('tapa_actualfecha');
            $bd_tapa_actualusua = $myClass->field('tapa_actualusua');
                    
            $username= $myClass->field("username");
            $usernameactual= $myClass->field("usernameactual");		
	}
}

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
		document.frm.Sr_tapa_nombre.focus();
	}
	</script>
	<?php
        verif_framework(); 
        ?>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("Edici&oacute;n de ".$myClass->getTitle());


/* botones */
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2);
$button->addItem(" Regresar ","tamanoPagina_buscar.php".$param->buildPars(true),"content");

echo $button->writeHTML();

echo "<br>";


/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->setUpload(true);

$form->addHidden("f_id",$id); // clave primaria
if($id){
    $form->addField("C&oacute;digo: ",$bd_tapa_id);
}

$form->addField("Nombre: ",textField("Nombre","Sr_tapa_nombre",$bd_tapa_nombre,80,120));

$form->addBreak("<b>TAMAÑO (en mm) </b>");
$form->addField("Ancho: ",numField("Ancho","nr_tapa_ancho",$bd_tapa_ancho,10,5,2));
$form->addField("Alto: ",numField("Alto","nr_tapa_alto",$bd_tapa_alto,10,5,2));

$form->addBreak("<b>MARGENES (en mm) </b>");
$form->addField("Superior: ",numField("Superior","nr_tapa_top",$bd_tapa_top,10,5,2));
$form->addField("Inferior: ",numField("Inferior","nr_tapa_botom",$bd_tapa_botom,10,5,2));
$form->addField("Izquierda: ",numField("Izquierda","nr_tapa_left",$bd_tapa_left,10,5,2));
$form->addField("Derecha: ",numField("Derecha","nr_tapa_right",$bd_tapa_right,10,5,2));

$form->addBreak("<b>ENCABEZADO</b>");
$form->addField("Alto (en mm): ",numField("Alto de cabecera","nr_tapa_header",$bd_tapa_header,10,5,2));
$form->addHidden("postPath",'gestdoc/margenes/'.SIS_EMPRESA_RUC.'/');    
$form->addField("Imagen JPG:",fileField("Imagen Cabecera","tapa_header_img" ,"$bd_tapa_header_img",60,"onchange=validaextension(this,'JPG')",iif($bd_tapa_header_img,"==","","",PUBLICUPLOAD.'/gestdoc/margenes/'.SIS_EMPRESA_RUC.'/')));

$form->addBreak("<b>PIE</b>");
$form->addField("Alto (en mm): ",numField("Alto de Pie","nr_tapa_footer",$bd_tapa_footer,10,5,2));
$form->addHidden("postPath",'gestdoc/margenes/'.SIS_EMPRESA_RUC.'/');    
$form->addField("Imagen JPG:",fileField("Imagen Pie","tapa_footer_img" ,"$bd_tapa_footer_img",60,"onchange=validaextension(this,'JPG')",iif($bd_tapa_footer_img,"==","","",PUBLICUPLOAD.'/gestdoc/margenes/'.SIS_EMPRESA_RUC.'/')));

if(strlen($id)) {
    $form->addBreak("<b>Control</b>");
    //$form->addField("Creado por: ",$username.' '.$bd_tapa_fregistro);
    $form->addField("Creado por: ",$username.' '.$bd_tapa_fregistro.' / '." Actualizado por: ".$usernameactual.'/'.$bd_tapa_fregistroactual);
}

echo $form->writeHTML();
?>
</body>
</html>

<?php
/* cierro la conexión a la BD */
$conn->close();