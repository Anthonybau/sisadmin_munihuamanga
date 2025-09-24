<?php
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("catalogosProveedor_class.php"); 

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$clear=getParam("clear");
$myClass = new proveedor ($id,iif($clear,'==',3,"Edici&oacute;n de Auxiliar","Edici&oacute;n de Proveedor"));

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
		$bd_prov_cci= $myClass->field("prov_cci");		
		$bd_prov_rnpb = $myClass->field("prov_rnpb");		 
		$bd_prov_rnps = $myClass->field("prov_rnps");		
		$bd_prov_rnpbvigencia = dtos($myClass->field("prov_rnpbvigencia"));		
		$bd_prov_rnpsvigencia = dtos($myClass->field("prov_rnpsvigencia"));				
		$bd_prov_direccion= $myClass->field("prov_direccion");
		$bd_prov_lugar= $myClass->field("prov_lugar");
		$bd_prov_telefono= $myClass->field("prov_telefono");
		$bd_prov_email= $myClass->field("prov_email");
		$bd_prov_actividad= $myClass->field("prov_actividad");
		$bd_usua_id = $myClass->field("usua_id");
		$bd_prov_estado= $myClass->field("prov_estado");
		$bd_prov_fechanacimiento=dtos($myClass->field("prov_fechanacimiento"));
                $bd_prov_dobles= $myClass->field("prov_dobles");
		$nameUsers= $myClass->field("username");
		$usernameactual= $myClass->field("usernameactual");				
	}
}else{
    $bd_prov_dobles=0;
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
	global $bd_prov_razsocial,$bd_prov_codigo,
		$bd_prov_apellidos,$bd_prov_nombres,$bd_prov_cci,
		$bd_prov_direccion,$bd_prov_telefono,$bd_prov_email,$bd_prov_actividad,$calendar;

	$objResponse = new xajaxResponse();

	$otable = new AddTableForm();
	$otable->setLabelWidth("20%");
	$otable->setDataWidth("80%");

	switch($mivalor){
	   case 1: // Si es persona juridica
			$otable->addField("Raz&oacute;n Social: ",textField("Raz&oacute;n Social","Sr_prov_razsocial",$bd_prov_razsocial,95,95));	   
			$otable->addField("R.U.C.: ",textField("RUC","Sr_prov_codigo",$bd_prov_codigo,14,11));	   			
			break;
			
	   case 2: // persona natural
			$otable->addField("Apellidos: ",textField("Apellidos","Sr_prov_apellidos",$bd_prov_apellidos,40,35));
			$otable->addField("Nombres: ",textField("Nombres","Sr_prov_nombres",$bd_prov_nombres,40,35));
			$otable->addField("R.U.C.: ",textField("RUC","Sr_prov_codigo",$bd_prov_codigo,14,11));	   			
			break;
	   case 3: // Auxiliar (Para registrar nombres para entrega de PECOSAS)
			$otable->addField("Apellidos: ",textField("Apellidos","Sr_prov_apellidos",$bd_prov_apellidos,40,35));
			$otable->addField("Nombres: ",textField("Nombres","Sr_prov_nombres",$bd_prov_nombres,40,35));
			$otable->addField("DNI: ",textField("DNI","Sr_prov_codigo",$bd_prov_codigo,10,8));	   			
			break;
	}

	if($mivalor==1 or $mivalor==2){ /* Si es PJ o P.N. */
                //$otable->addField("C.C.I.",textField("C.C.I.","Sx_prov_cci",$bd_prov_cci,20,20));	
		//$otable->addField("RNP B",textField("RNP B","Sx_prov_rnpb",$bd_prov_rnpb,10,10));	
		//$otable->addField("Fecha de vigencia RNP B: ", $calendar->make_input_field('Fecha vigencia RNP B',array(),array('name'=> 'Dx_prov_rnpbvigencia','value'=> $bd_prov_rnpbvigencia)));		
		//$otable->addField("RNP S",textField("RNP S","Sx_prov_rnps",$bd_prov_rnps,10,10));	
		//$otable->addField("Fecha de vigencia RNP S: ", $calendar->make_input_field('Fecha vigencia RNP S',array(),array('name'=> 'Dx_prov_rnpsvigencia','value'=> $bd_prov_rnpsvigencia)));		
		$otable->addField("Direcci&oacute;n: ",textField("Direcci&oacute;n","Sx_prov_direccion",$bd_prov_direccion,95,95));
		$otable->addField("Tel&eacute;fono(s): ",textField("Tel&eacute;fono","Sx_prov_telefono",$bd_prov_telefono,40,35));
		$otable->addField("Email: ",textField("Email","cx_prov_email",$bd_prov_email,55,50));
		//$otable->addField("Actividad: ",textAreaField("Actividad","Sr_prov_actividad",$bd_prov_actividad,5,100,300));
	}else{
            	$otable->addField("Direcci&oacute;n: ",textField("Direcci&oacute;n","Sx_prov_direccion",$bd_prov_direccion,95,95));
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
	<title><?=$myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="JavaScript" src="<?=PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?=PATH_INC?>js/libjsgen.js"></script>
	
	<script language='JavaScript'>
	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			document.frm.target = "controle";
			document.frm.action = "<?=$myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
			document.frm.submit();
			
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
		/* valido Longitud del campo C�digo */ 
		if(frm.tr_tabl_tipoproveedor.value==3){ /* Auxiliar */
			if (frm.Sr_prov_codigo.value.length<8) {
			   frm.Sr_prov_codigo.focus();
			   sError+="DNI no valido"+"\n" 
			   nErrTot+=1;
			}
		}else{ /* PN o PJ */
			if (frm.Sr_prov_codigo.value.length<11) {
			   frm.Sr_prov_codigo.focus();
			   sError+="RUC no valido"+"\n" 
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
    <?php $xajax->printJavascript(PATH_INC.'ajax/'); 
       $calendar->load_files();
	 verif_framework(); ?>
</head>

<body class="contentBODY">
<?php
pageTitle($myClass->getTitle());

/* botones */
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2);
if($clear==1)
    $button->addItem(" Regresar ","catalogosProveedor_buscar.php".$param->buildPars(true));
else
    $button->addItem(" Salir sin Guardar ","javascript:if(confirm('Seguro de Salir sin Guardar?')){parent.parent.close()}","content");	


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

if(getParam("clear")==3)
    $sqltipo="SELECT tabl_codigo,tabl_descripcion FROM catalogos.tabla WHERE tabl_tipo='TIPO_PROVEEDOR' ORDER BY 1";
else
    $sqltipo="SELECT tabl_codigo,tabl_descripcion FROM catalogos.tabla WHERE tabl_tipo='TIPO_PROVEEDOR' AND tabl_codigo IN (1,2,3) ORDER BY 1";

$form->addField("Tipo",listboxField("Tipo ",$sqltipo,"tr_tabl_tipoproveedor",$bd_tabl_tipoproveedor,"-- Seleccione Tipo --","onChange=\"xajax_AjCondicional('DivAddTable',1,this.value);\""));

$form->addHtml("<tr><td colspan=2><div id='DivAddTable'>\n");
$form->addHtml(AjCondicional('DivAddTable',2,$bd_tabl_tipoproveedor));
$form->addHtml("</div></td></tr>\n");

$form->addField("Secuencia: ",numField("Secuencia","nr_prov_dobles",$bd_prov_dobles,3,3,0));    
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