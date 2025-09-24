<?
/* formulario de ingreso y modificacion */
include("../../library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("./PersonaCuentaBancaria_class.php");
include("../catalogos/catalogosTabla_class.php");
include("./Persona_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$id_relacion = getParam("id_relacion"); // captura la variable que viene del objeto lista

$myClass = new clsPersonaCuentaBancaria($id,'Cuenta Bancaria');

if ($id>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
                $bd_pecb_id=$myClass->field("pecb_id");
		$bd_pers_id=$myClass->field("pers_id");
                $bd_pecb_fecha=dtos($myClass->field("pecb_fecha"));
		$bd_tabl_bancoid=$myClass->field("tabl_bancoid");
		$bd_pecb_cuentadeposito=$myClass->field("pecb_cuentadeposito");
                $bd_pecb_tipo=$myClass->field("pecb_tipo");
                        
                $nameUsers= $myClass->field('username');
                $fregistro=$myClass->field('pecb_fecharegistro');
                $nameUsersActual=$myClass->field('usernameactual');
                $fregistroActual=$myClass->field('pecb_actualfecha');
              
        }
}else{
    $bd_pecb_fecha=date('d/m/Y');
    $bd_pecb_tipo=1;
}


/* Archivos para calendario */
require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->processRequests();
?>
<html>
<head>
	<title><?=$myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="JavaScript" src="<?=PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?=PATH_INC?>js/libjsgen.js"></script>

        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>   
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                
	

	<script language='JavaScript'>
	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "<?=$myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
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
		document.frm.tr_tabl_bancoid.focus();
	}
	</script>
	<?
            $xajax->printJavascript(PATH_INC.'ajax/');
            verif_framework();
            $calendar->load_files();	
        ?>

</head>

<body class="contentBODY" onLoad="inicializa()">
<?
pageTitle("Edici&oacute;n de ".$myClass->getTitle());



/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setUpload(true);

$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$bd_pecb_id); // clave primaria
$form->addHidden("nx_pers_id",$id_relacion); // chave estrangeira do relacionamento
$form->addHidden("pagina",$pg); // numero de página que llamo

$myPersona = new clsPersona($id_relacion);
$myPersona->setDatos();
$form->addField("Trabajador: ",$myPersona->field("empleado").' / '.$myPersona->field("pers_dni"));

$lista_nivel = array("1,Haberes","2,CTS"); // definición de la lista para campo radio
$form->addField("Tipo: ",radioField("Tipo",$lista_nivel, "xr_pecb_tipo",$bd_pecb_tipo,"","H"));
            
$form->addField("Fecha: ", $calendar->make_input_field('Fecha',array(),array('name'=> 'Dr_pecb_fecha','value'=> $bd_pecb_fecha)));
$tabla=new clsTabla_SQLlista();
$tabla->whereTipo('BANCOS');
$tabla->orderUno();
$sqlBanco=$tabla->getSQL_cbox();

$form->addField("Banco: ",listboxField("Banco",$sqlBanco,"tr_tabl_bancoid",$bd_tabl_bancoid,"-- Seleccione Banco --"));
$msg='Ingrese cero (0) para indicar que no tiene cuenta';
$form->addField("Cuenta Dep&oacute;sito: ",textField("Cuenta Deposito","Sr_pecb_cuentadeposito",$bd_pecb_cuentadeposito,25,25).' '.help("Informaci&oacute;n",$msg,2));

/* botones */
$button = new Button;
$button->setDiv(false);
$button->addItem("Guardar","javascript:salvar('Guardar')","content",2);
$form->addField("",$button->writeHTML());

if(strlen($id)) {
    $form->addBreak("<b>Control</b>");
    $form->addField("Creado: ",$nameUsers.'/'.substr($fregistro,0,19));
    $form->addField("Actualizado: ",$nameUsersActual.'/'.substr($fregistroActual,0,19));
}        

echo $form->writeHTML();
?>
    <script>
    $("select").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true,
            width: '50%'
            });
    
    </script>    
</body>
</html>

<?
/* cierro la conexión a la BD */
$conn->close();