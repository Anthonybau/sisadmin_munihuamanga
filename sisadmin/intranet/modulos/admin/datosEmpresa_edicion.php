<?php
/* formulario de ingreso y modificacion */
include("../../library/library.php");

/* verificaci�n del nivel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("datosEmpresa_class.php"); 

$clear=getParam('clear');

/* establecer conexion con la BD */
$conn = new db();
$conn->open();


$myClass = new datosEmpresa(1,'DATOS DE EMPRESA');
$myClass->setDatos();
if($myClass->existeDatos()){
    $bd_empr_id = $myClass->field('empr_id');
    $bd_empr_razsocial= $myClass->field('empr_razsocial');
    $bd_empr_responsable= $myClass->field('empr_responsable');
    $bd_empr_breve= $myClass->field('empr_breve');    
    $bd_empr_siglas= $myClass->field('empr_siglas');
    $bd_empr_contrato_siglas= $myClass->field('empr_contrato_siglas');
    
    $bd_empr_ciudad= $myClass->field('empr_ciudad');
    $bd_empr_direccion= $myClass->field('empr_direccion');
    $bd_empr_telefono= $myClass->field('empr_telefono');
    $bd_empr_ruc= $myClass->field('empr_ruc');

    $bd_empr_email= $myClass->field('empr_email');
    $bd_empr_email_password= $myClass->field('empr_email_password');
    
    $bd_empr_cod_ubigeo= $myClass->field('empr_cod_ubigeo');
    $bd_empr_departamento= $myClass->field('empr_departamento');
    $bd_empr_provincia= $myClass->field('empr_provincia');
    $bd_empr_distrito= $myClass->field('empr_distrito');

    $bd_empr_representante= $myClass->field('empr_representante');
    $bd_empr_representante_dni= $myClass->field('empr_representante_dni');
    $bd_empr_representante_cargo= $myClass->field('empr_representante_cargo');
    
    $bd_empr_fcierre_nopago= str_replace("/","-",dtos($myClass->field('empr_fcierre_nopago'),"-"));
            
    $bd_empr_tipo= $myClass->field('empr_tipo');
    $bd_empr_actualfecha= $myClass->field('empr_actualfecha');
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
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT ?>">
	<script language="JavaScript" src="<?php echo PATH_INC ?>js/focus.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC ?>js/libjsgen.js"></script>
	
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
            if(document.frm.__password.value==""){
                sError='Ingrese Contraseña de Grabación';
                nErrTot=1;
                foco='document.frm.__password.focus()';
            }    
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
		document.frm.Sr_empr_razsocial.focus();
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
$form->addHidden("f_id",$bd_empr_id); // clave primaria
            
$form->addField("Raz&oacute;n Social:",textField("Razon Social","Sr_empr_razsocial",$bd_empr_razsocial,80,80));    
$form->addField("Breve:",textField("Breve","Sr_empr_breve",$bd_empr_breve,25,25));
$form->addField("Siglas:",textField("Siglas","Sr_empr_siglas",$bd_empr_siglas,20,20));
$form->addField("Siglas para Contratos:",textField("Siglas para Contratos","Sx_empr_contrato_siglas",$bd_empr_contrato_siglas,20,20));

$form->addField("Direcci&oacute;n: ",textField("Direccion","Sx_empr_direccion",$bd_empr_direccion,100,100));
$form->addField("Ciudad: ",textField("Ciudad","Sx_empr_ciudad",$bd_empr_ciudad,50,50));
$form->addField("RUC: ",textField("RUC","Sr_empr_ruc",$bd_empr_ruc,14,11));
$form->addField("Tel&eacute;fono(s): ",textField("Telefono","Sx_empr_telefono",$bd_empr_telefono,20,20));

//$form->addField("Email: ",textField("Email","cx_empr_email",$bd_empr_email,55,50));
//$form->addField("Contrase&ntilde;a Email: ",textField("Contrasena Email","sx_empr_email_password",$bd_empr_email_password,20,20));

//$form->addBreak("<b>UBICACION GEOGRAFICA</b>");        
//$form->addField("Departamento:",textField("Departamento","Sr_empr_departamento",$bd_empr_departamento,25,25));
//$form->addField("Provincia:",textField("Provincia","Sr_empr_provincia",$bd_empr_provincia,25,25));
//$form->addField("Distrito:",textField("Distrito","Sr_empr_distrito",$bd_empr_distrito,25,25));
//$form->addField("Cod.Ubigeo:",textField("Cod.Ubigeo","Sr_empr_cod_ubigeo",$bd_empr_cod_ubigeo,6,6));
    
$form->addBreak("<b>REPRESENTANTE</b>");
$form->addField("Representante: ",textField("Representante","Sx_empr_representante","$bd_empr_representante",80,80));
$form->addField("DNI: ",numField("DNI","nx_empr_representante_dni",$bd_empr_representante_dni,10,8,0));
$form->addField("Cargo: ",textField("Cargo","Sx_empr_representante_cargo","$bd_empr_representante_cargo",30,30));
        
$sql = array(1 => "PRIVADO",
             2 => "PUBLICO BASICO",
             3 => "PUBLICO BENEFICENCIA");
$form->addField("Tipo de Empresa: ", listboxField("Tipo de Empresa", $sql, "nr_empr_tipo","$bd_empr_tipo"));

$form->addField("Fecha y Hora de Cierre P/NO PAGO (dd-mm-yyyy hh:mm:ss):",textField("Fecha y Hora de Cierre P/NO PAGO","Sx_empr_fcierre_nopago",$bd_empr_fcierre_nopago ,19,19));


$form->addField("Contrase&ntilde;a de Grabaci&oacute;n: ",passwordField("Contraseña de Grabación","__password","",8,8));    
$form->addBreak("<b>CONTROL</b>");
$form->addField("Actualizado: ",substr($bd_empr_actualfecha,0,19));

//$button = new Button;
//$button->setDiv(false);
//$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",3);
//$form->addField("",$button->writeHTML());

//$form->addField("Email: ",textField("Email","cx_prov_email",$bd_prov_email,55,50));

echo $form->writeHTML();

?>
</body>
</html>

<?php
/* cierro la conexion a la BD */
$conn->close();