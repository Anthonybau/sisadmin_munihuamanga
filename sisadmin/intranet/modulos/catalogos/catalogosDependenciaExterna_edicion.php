<?php
/* formulario de ingreso y modificaci�n */
include($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("catalogosDependenciaExterna_class.php");

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista

$myClass = new proveedor ($id,"Dependencias Externas");

if (strlen($id)>0) { // edici�n
	$myClass->setDatos();
	if($myClass->existeDatos()){
		$bd_prov_id= $myClass->field("prov_id");
		$bd_tabl_tipoproveedor= $myClass->field("tabl_tipoproveedor");
		$bd_prov_apellidos= $myClass->field("prov_apellidos");		
		$bd_prov_nombres= $myClass->field("prov_nombres");
		$bd_prov_razsocial= $myClass->field("prov_razsocial");
		$bd_prov_nomcomercial= $myClass->field("prov_nomcomercial");
		$bd_prov_replegal= $myClass->field("prov_replegal");
		$bd_prov_codigo= $myClass->field("prov_codigo");
		$bd_prov_direccion= $myClass->field("prov_direccion");
		$bd_prov_lugar= $myClass->field("prov_lugar");
		$bd_prov_telefono= $myClass->field("prov_telefono");
		$bd_prov_email= $myClass->field("prov_email");
		$bd_prov_actividad= $myClass->field("prov_actividad");
		$bd_usua_id = $myClass->field("usua_id");
		$bd_prov_estado= $myClass->field("prov_estado");
		$bd_prov_fechanacimiento=dtos($myClass->field("prov_fechanacimiento"));
		$nameUsers= $myClass->field("username");
		$usernameactual= $myClass->field("usernameactual");				
	}
}

/* Archivos para calendario */
require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("AjCondicional");

function AjCondicional($NameDiv,$op,$mivalor)
{
	global $calendar,$bd_prov_razsocial,$bd_prov_codigo,
			$bd_prov_apellidos,$bd_prov_nombres,
			$bd_prov_nomcomercial,$bd_prov_replegal,
			$bd_prov_direccion,$bd_prov_lugar,$bd_prov_telefono,$bd_prov_email,$bd_prov_fechanacimiento,$bd_prov_actividad;

	$objResponse = new xajaxResponse();

	$otable = new AddTableForm();
	$otable->setLabelWidth("20%");
	$otable->setDataWidth("80%");

	switch($mivalor){
	   case 1: // Si es persona juridica
			$otable->addField("Raz&oacute;n Social: <font color=red>*</font>",textField("Raz&oacute;n Social","Sr_prov_razsocial",$bd_prov_razsocial,95,95));	   
			//$otable->addField("Representante Legal: ",textField("Representante Legal","Sx_prov_replegal",$bd_prov_replegal,60,60));
			//OJO no se recomienda llenar con ceros el campo, debido a que deja de funcionar la busqueda avanzada
			$otable->addField("R.U.C.: ",textField("RUC","Sx_prov_codigo",$bd_prov_codigo,11,11));	   			
			break;
			
	   case 2: // persona natural
			$otable->addField("Apellidos: <font color=red>*</font>",textField("Apellidos","Sr_prov_apellidos",$bd_prov_apellidos,40,35));
			$otable->addField("Nombres: <font color=red>*</font>",textField("Nombres","Sr_prov_nombres",$bd_prov_nombres,40,35));
			//$otable->addField("Nombre Comercial: ",textField("Nombre Comercial","Sx_prov_nomcomercial",$bd_prov_nomcomercial,60,50));
			//$otable->addField("Fecha de nacimiento: ", $calendar->make_input_field('Fecha de nacimiento',array(),array('name'=> 'Dr_prov_fechanacimiento','value'=> $bd_prov_fechanacimiento)));
			//OJO no se recomienda llenar con ceros el campo, debido a que deja de funcionar la busqueda avanzada			
			//este campo se especifica en el archivo guardar.php
			$otable->addField("R.U.C.: ",textField("RUC","Sx_prov_codigo",$bd_prov_codigo,11,11));	   			
			break;
	   case 3: // Auxiliar (Para registrar nombres para entrega de PECOSAS)
			$otable->addField("Apellidos: <font color=red>*</font>",textField("Apellidos","Sr_prov_apellidos",$bd_prov_apellidos,40,35));
			$otable->addField("Nombres: <font color=red>*</font>",textField("Nombres","Sr_prov_nombres",$bd_prov_nombres,40,35));
			//$otable->addField("Fecha de nacimiento: ", $calendar->make_input_field('Fecha de nacimiento',array(),array('name'=> 'Dr_prov_fechanacimiento','value'=> $bd_prov_fechanacimiento)));
			//OJO no se recomienda llenar con ceros el campo, debido a que deja de funcionar la busqueda avanzada			
			//este campo se especifica en el archivo guardar.php
			$otable->addField("DNI: <font color=red>*</font>",textField("DNI","Sx_prov_codigo",$bd_prov_codigo,8,8));	   			
			break;
	}

	if($mivalor==1 or $mivalor==2){ /* Si es PJ o P.N. */

		$otable->addField("Direcci&oacute;n: <font color=red>*</font>",textField("Direcci&oacute;n","Sr_prov_direccion",$bd_prov_direccion,95,95));
		$otable->addField("Lugar: ",textField("Lugar","Sx_prov_lugar",$bd_prov_lugar,50,50));
		$otable->addField("Tel&eacute;fono(s): ",textField("Tel&eacute;fono","Sx_prov_telefono",$bd_prov_telefono,40,35));
		$otable->addField("Email: ",textField("Email","cx_prov_email",$bd_prov_email,55,50));
	}

	$contenido_respuesta=$otable->writeHTML();

    $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);

        // Se devuelve el objeto, que este dara todas las instruccione JS para realizar la tarea
	if($op==1){
		return $objResponse;
	}else{
		return $contenido_respuesta	;
	}		

}

$xajax->processRequests();
// fin para Ajax

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
			document.frm.target = "content";
			document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
			document.frm.submit();
			
		}
	}
	/*
		se invoca desde la funcion obligacampos (libjsgen.js)
		en esta funci�n se puede personalizar la validaci�n del formulario
		y se ejecuta al momento de gurdar los datos
	*/
        function mivalidacion(){
            return true;
        }
        
	function mivalidacionx(){
            var sError="Mensajes del sistema: "+"\n\n";
            var nErrTot=0;
		/* valido Longitud del campo Código */
		if(frm.tr_tabl_tipoproveedor.value==3){ /* Auxiliar */
			if (frm.Sr_prov_codigo.value.length<8) {
			   frm.Sr_prov_codigo.focus();
			   sError+="DNI no v\u00e1lido"+"\n"
			   nErrTot+=1;
			}
		}else{ /* PN o PJ */
			if (frm.Sr_prov_codigo.value.length<11) {
			   frm.Sr_prov_codigo.focus();
			   sError+="RUC no v\u00e1lido"+"\n"
			   nErrTot+=1;
			}
		}
		if (nErrTot>0){
			alert(sError)
			return false
		}else
			return true

	}

	/* funci�n que define el foco inicial en el formulario */
	function inicializa() {
		document.frm.Sr_prov_razsocial.focus();
	}
	</script>
    
    <?php 
        $xajax->printJavascript(PATH_INC.'ajax/'); 
       $calendar->load_files();
	 verif_framework();
     ?>
</head>

<body class="contentBODY">
<?php
pageTitle("Edici&oacute;n de ".$myClass->getTitle());

/* botones */
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2);
$button->addItem(" Regresar ","catalogosDependenciaExterna_buscar.php".$param->buildPars(true));
echo $button->writeHTML();

/* Control de fichas */
$abas = new Abas();
$abas->addItem("General",true);
echo $abas->writeHTML();
echo "<br>";

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$id); // clave primaria


//if (strlen($id)>0) { // edici�n
//	$Tipo=getDbValue("SELECT tabl_descripcion FROM tabla WHERE tabl_codigo=$bd_tabl_tipoproveedor AND tabl_tipo='16' ");
//	$form->addField("Tipo",$Tipo);
//	$form->addHidden("tr_tabl_tipoproveedor",$bd_tabl_tipoproveedor); // clave primaria	
//	}
//else {
	$sqltipo="SELECT tabl_codigo,tabl_descripcion FROM tabla WHERE tabl_tipo='16' ORDER BY 1";
	$form->addField("Tipo: <font color=red>*</font>",listboxField("Tipo ",$sqltipo,"tr_tabl_tipoproveedor",$bd_tabl_tipoproveedor,"-- Seleccione Tipo --","onChange=\"xajax_AjCondicional('DivAddTable',1,this.value);\""));
//	}

$form->addHtml("<tr><td colspan=2><div id='DivAddTable'>\n");
$form->addHtml(AjCondicional('DivAddTable',2,$bd_tabl_tipoproveedor));
$form->addHtml("</div></td></tr>\n");

//solo si es edicion se agrega los datos de auditoria
if($id) {
	$form->addField("Activo: ",checkboxField("Activo","hx_prov_estado",1,$bd_prov_estado==1));
	$form->addBreak("<b>Control</b>");
	$form->addField("Creado por: ",$nameUsers);
	$form->addField("Actualizado por: ",$usernameactual);

}else{
    $form->addHidden("hx_prov_estado",1); 
}
echo $form->writeHTML();
?>
</body>
</html>

<?php
/* cierro la conexi�n a la BD */
$conn->close();