<?
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("./PersonaLicencias_class.php");
include("../catalogos/catalogosTabla_class.php");
include("./Persona_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$id_relacion = getParam("id_relacion"); // captura la variable que viene del objeto lista

$myClass = new clsPersonaLicencias($id,'Licencia');

if ($id>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
                $bd_peli_id=$myClass->field("peli_id");
		$bd_pers_id=$myClass->field("pers_id");
		$bd_peli_documento= $myClass->field("peli_documento");
		$bd_peli_fecha= dtos($myClass->field("peli_fecha"));
		$bd_tabl_tipo_licencia= $myClass->field("tabl_tipo_licencia");
                $bd_peli_remunerado= $myClass->field("peli_remunerado");
		$bd_peli_desde= dtos($myClass->field("peli_desde"));
		$bd_peli_hasta= dtos($myClass->field("peli_hasta"));
		$bd_peli_dias= $myClass->field("peli_dias");
		$bd_peli_subvencion= $myClass->field("peli_subvencion");
		$bd_peli_emisor= $myClass->field("peli_emisor");
		$bd_peli_observacion= $myClass->field("peli_observacion");			
		$bd_peli_fecharegistro = $myClass->field("peli_fecharegistro ");
		$bd_usua_id = $myClass->field("usua_id");

                $bd_peli_adjunto1=$myClass->field("peli_adjunto1");
                $bd_peli_adjunto2=$myClass->field("peli_adjunto2");
                
                $nameUsers= $myClass->field('username');
                $fregistro=$myClass->field('peli_fecharegistro');
                $nameUsersActual=$myClass->field('usernameactual');
                $fregistroActual=$myClass->field('peli_actualfecha');
        }
}else{
    //$bd_peli_fecha=date('d/m/Y');
    $bd_peli_remunerado=1;
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
		document.frm.tr_tabl_tipo_licencia.focus();
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
$form->addHidden("f_id",$bd_peli_id); // clave primaria
$form->addHidden("nx_pers_id",$id_relacion); // chave estrangeira do relacionamento
$form->addHidden("pagina",$pg); // numero de página que llamo

$myPersona = new clsPersona($id_relacion);
$myPersona->setDatos();
$form->addField("Trabajador: ",$myPersona->field("empleado").' / '.$myPersona->field("pers_dni"));

$tabla=new clsTabla_SQLlista();
$tabla->whereTipo('TIPO_LICENCIA');
$tabla->orderUno();
$sqlBanco=$tabla->getSQL_cbox();
$form->addField("Tipo de Licencia: ",listboxField("Tipo de Licencia",$sqlBanco,"tr_tabl_tipo_licencia",$bd_tabl_tipo_licencia,"-- Seleccione Tipo --"));
$lista_nivel = array("1,SIN GOCE","2,CON GOCE"); // definición de la lista para campo radio
$form->addField("Remunerado: ",radioField("Remunerado",$lista_nivel, "xr_peli_remunerado",$bd_peli_remunerado,"","H"));

$form->addField("Documento: ",textField("Documento","Sr_peli_documento",$bd_peli_documento,90,90));
$form->addField("Fecha de Doc: ", $calendar->make_input_field('Fecha de Doc',array(),array('name'=> 'Dr_peli_fecha','value'=> $bd_peli_fecha)));
$form->addField("Vigencia Desde: ", $calendar->make_input_field('Fecha Desde',array(),array('name'=> 'Dx_peli_desde','value'=> $bd_peli_desde)));
$form->addField("Hasta: ", $calendar->make_input_field('Fecha Hasta',array(),array('name'=> 'Dx_peli_hasta','value'=> $bd_peli_hasta)));
$form->addField("Total D&iacute;as: ",numField("Total Dias","nx_peli_dias",$bd_peli_dias,8,8,0));
$form->addField("Subvenci&oacute;n (en D&iacute;as): ",numField("Subvencion","nx_peli_subvencion",$bd_peli_subvencion,8,8,0));
$form->addField("Emisor: ",textField("Emisor","Sx_peli_emisor",$bd_peli_emisor,90,90));
$form->addField("Observaci&oacute;n: ",textField("Observacion","Sx_peli_observacion",$bd_peli_observacion,90,90));			

$form->addHidden("postPath",'escalafon/');
$form->addField("Archivo: ",fileField("Archivo1","peli_adjunto1" ,$bd_peli_adjunto1,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));
//$form->addField("Archivo:",fileField("Archivo2","peli_adjunto2" ,$bd_peli_adjunto2,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));

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