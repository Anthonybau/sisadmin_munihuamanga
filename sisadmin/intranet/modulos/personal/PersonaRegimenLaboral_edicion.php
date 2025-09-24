<?php
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("./PersonaRegimenLaboral_class.php");
include("../catalogos/catalogosTabla_class.php");
include("../catalogos/RegimenLaboral_class.php");
include("./Persona_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$id_relacion = getParam("id_relacion"); // captura la variable que viene del objeto lista

$myClass = new clsPersonaRegimenLaboral($id,'R&eacute;gimen Laboral');

if ($id>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
                $bd_perl_id=$myClass->field("perl_id");
		$bd_pers_id=$myClass->field("pers_id");
                $bd_perl_fecha=dtos($myClass->field("perl_fecha"));
		$bd_tabl_idsitlaboral=$myClass->field("tabl_idsitlaboral");
		$bd_rela_id=$myClass->field("rela_id");
		$bd_perl_documento=$myClass->field("perl_documento");                
                $bd_perl_adjunto1=$myClass->field("perl_adjunto1");
                $bd_perl_adjunto2=$myClass->field("perl_adjunto2");
                
                $nameUsers= $myClass->field('username');
                $fregistro=$myClass->field('perl_fecharegistro');
                $nameUsersActual=$myClass->field('usernameactual');
                $fregistroActual=$myClass->field('perl_actualfecha');
              
        }
}else{
    $bd_perl_fecha=date('d/m/Y');
}


/* Archivos para calendario */
require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("pideCondicionLaboral");

function pideCondicionLaboral($op,$rela_id,$NameDiv)
{
	global $conn,$bd_tabl_idsitlaboral,$id;

	$objResponse = new xajaxResponse();
	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");

        $tabla=new clsTabla_SQLlista();
        $tabla->whereTipo('CONDICION_LABORAL');
        $tabla->whereRelaID($rela_id);
        $tabla->orderUno();
        $sqlSituLabo=$tabla->getSQL_cbox();
        $oForm->addField("Condici&oacute;n Laboral: ",listboxField("Condici&oacute;n laboral",$sqlSituLabo, "tr_tabl_idsitlaboral",$bd_tabl_idsitlaboral,"-- Seleccione Condici&oacute;n Laboral --",""));
        
        $contenido_respuesta=$oForm->writeHTML();

	if($op==1){
                $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);            
                $objResponse->addScript("$('select').select2({
                                        placeholder: 'Seleccione un elemento de la lista',
                                        allowClear: true,
                                        width: '50%'
                                        });");                                                            
		return $objResponse;
        }else{
		return $contenido_respuesta	;
        }
}

$xajax->processRequests();
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="<?php echo PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>

        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>   
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                
	

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
		document.frm.tr_rela_id.focus();
	}
	</script>
	<?php
            $xajax->printJavascript(PATH_INC.'ajax/');
            verif_framework();
            $calendar->load_files();	
        ?>

</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("Edici&oacute;n de ".$myClass->getTitle());



/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setUpload(true);

$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$bd_perl_id); // clave primaria
$form->addHidden("nx_pers_id",$id_relacion); // chave estrangeira do relacionamento
$form->addHidden("pagina",$pg); // numero de página que llamo

$myPersona = new clsPersona($id_relacion);
$myPersona->setDatos();
$form->addField("Trabajador: ",$myPersona->field("empleado").' / '.$myPersona->field("pers_dni"));

$sqlRegLaboral= new clsRegimenLaboral_SQLlista();
$sqlRegLaboral=$sqlRegLaboral->getSQL_cbox();            
$form->addField("R&eacute;gimen Laboral: ",listboxField("Regimen Laboral",$sqlRegLaboral, "tr_rela_id",$bd_rela_id,"-- Seleccione Regimen Laboral --","onChange=\"xajax_pideCondicionLaboral(1,this.value,'divCondicionLaboral')\""));

$form->addHtml("<tr><td colspan=2><div id='divCondicionLaboral'>\n");
$form->addHtml(pideCondicionLaboral(2,$bd_rela_id,'divCondicionLaboral'));
$form->addHtml("</div></td></tr>\n");

    
$form->addField("Fecha Documento: ", $calendar->make_input_field('Fecha Documento',array(),array('name'=> 'Dr_perl_fecha','value'=> $bd_perl_fecha)));
$form->addField("Documento:",textField("Documento","Sr_perl_documento",$bd_perl_documento,80,120));   

$form->addHidden("postPath",'escalafon/');
$form->addField("Archivo: ",fileField("Archivo1","perl_adjunto1" ,$bd_perl_adjunto1,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));
//$form->addField("Archivo:",fileField("Archivo2","perl_adjunto2" ,$bd_perl_adjunto2,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));

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

<?php
/* cierro la conexión a la BD */
$conn->close();