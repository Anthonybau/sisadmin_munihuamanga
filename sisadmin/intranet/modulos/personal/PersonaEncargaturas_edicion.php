<?
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("./PersonaEncargaturas_class.php");
include("../catalogos/catalogosTabla_class.php");
include("./Persona_class.php");
include("../catalogos/catalogosDependencias_class.php");
include("./PersonaDatosLaborales_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$id_relacion = getParam("id_relacion"); // captura la variable que viene del objeto lista

$myClass = new clsPersonaEncargaturas($id,'Encargatura');

if ($id>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
                $bd_peen_id=$myClass->field("peen_id");
		$bd_pers_id=$myClass->field("pers_id");
                $bd_peen_tipo= $myClass->field("peen_tipo");
		$bd_peen_documento= $myClass->field("peen_documento");
		$bd_peen_fecha= dtos($myClass->field("peen_fecha"));
                $bd_peen_remunerado= $myClass->field("peen_remunerado");
		$bd_peen_desde= dtos($myClass->field("peen_desde"));
		$bd_peen_hasta= dtos($myClass->field("peen_hasta"));
                $bd_depe_id= $myClass->field("depe_id");
                $bd_peen_cargo= $myClass->field("peen_cargo");
                $bd_pdla_id= $myClass->field("pdla_id");
		$bd_usua_id = $myClass->field("usua_id");

                $bd_peen_adjunto1=$myClass->field("peen_adjunto1");
                $bd_peen_adjunto2=$myClass->field("peen_adjunto2");
                
                $nameUsers= $myClass->field('username');
                $fregistro=$myClass->field('peen_fecharegistro');
                $nameUsersActual=$myClass->field('usernameactual');
                $fregistroActual=$myClass->field('peen_actualfecha');
        }
}else{
    //$bd_peen_fecha=date('d/m/Y');
    $bd_peen_remunerado=1;
    $bd_peen_tipo=1;
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
		document.frm.tr_depe_id.focus();
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
$form->addHidden("f_id",$bd_peen_id); // clave primaria
$form->addHidden("nx_pers_id",$id_relacion); // chave estrangeira do relacionamento
$form->addHidden("pagina",$pg); // numero de página que llamo
$form->addHidden("xr_peen_tipo",2);

$myPersona = new clsPersona($id_relacion);
$myPersona->setDatos();
$form->addField("Trabajador: ",$myPersona->field("empleado").' / '.$myPersona->field("pers_dni"));
//$form->addField("Dependencia Actual: ",$myPersona->field("depe_nombre"));

//$lista_nivel = array("1,DESIGNACION","2,ENCARGATURA"); // definición de la lista para campo radio
//$form->addField("Tipo: ",radioField("Tipo",$lista_nivel, "xr_peen_tipo",$bd_peen_tipo,"","H"));

$lista_nivel = array("1,SIN GOCE","2,CON GOCE"); // definición de la lista para campo radio
$form->addField("Remunerado: ",radioField("Remunerado",$lista_nivel, "xr_peen_remunerado",$bd_peen_remunerado,"","H"));

$sqlDependencia=new dependencia_SQLlista();
$sqlDependencia=$sqlDependencia->getSQL_cbox();
$form->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia, "tr_depe_id",$bd_depe_id,"-- Seleccione Dependencia --","", "",""));
$form->addField("Cargo: ",textField("Cargo","Sr_peen_cargo",$bd_peen_cargo,80,100));       

$form->addField("Fecha de Doc: ", $calendar->make_input_field('Fecha de Doc',array(),array('name'=> 'Dr_peen_fecha','value'=> $bd_peen_fecha)));
$form->addField("Documento: ",textField("Documento","Sr_peen_documento",$bd_peen_documento,90,90));
$form->addField("Desde: ", $calendar->make_input_field('Fecha Desde',array(),array('name'=> 'Dr_peen_desde','value'=> $bd_peen_desde)));
$form->addField("Hasta: ", $calendar->make_input_field('Fecha Hasta',array(),array('name'=> 'Dx_peen_hasta','value'=> $bd_peen_hasta)));

//$datosLaborales=new clsDatosLaborales_SQLlista();
//$datosLaborales->wherePersID($id_relacion);       
//$sqlDatosLaborales=$datosLaborales->getSQL_cbox2();
//$form->addField("Dependencia/Cargo Actual: ",listboxField("Dependencia/Cargo Actual",$sqlDatosLaborales, "tr_pdla_id",$bd_pdla_id));


$form->addHidden("postPath",'escalafon/');
$form->addField("Archivo: ",fileField("Archivo1","peen_adjunto1" ,$bd_peen_adjunto1,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));
//$form->addField("Archivo:",fileField("Archivo2","peen_adjunto2" ,$bd_peen_adjunto2,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));

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
            width: '80%'
            });
    
    </script>    
</body>
</html>

<?
/* cierro la conexión a la BD */
$conn->close();