<?
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("./PersonaFamilia_class.php");
include("../catalogos/catalogosTabla_class.php");
include("./Persona_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$id_relacion = getParam("id_relacion"); // captura la variable que viene del objeto lista

$myClass = new clsPersonaFamilia($id,'Familia');

if ($id>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
                $bd_pefa_id=$myClass->field("pefa_id");
		$bd_pers_id=$myClass->field("pers_id");
		$bd_pefa_apellpaterno=$myClass->field("pefa_apellpaterno");
		$bd_pefa_apellmaterno=$myClass->field("pefa_apellmaterno");
                $bd_pefa_nacefecha=dtos($myClass->field("pefa_nacefecha"));
		$bd_pefa_nombres=$myClass->field("pefa_nombres");
                $bd_tabl_idtipodocumento=$myClass->field("tabl_idtipodocumento");
                $bd_pefa_dni=$myClass->field("pefa_dni");
		$bd_pefa_sexo=$myClass->field("pefa_sexo");
		$bd_pefa_vive=$myClass->field("pefa_vive");		
		$bd_tabl_idestadocivil=$myClass->field("tabl_idestadocivil");
		$bd_tabl_idparentesco=$myClass->field("tabl_idparentesco");
                $bd_pefa_codessalud=$myClass->field("pefa_codessalud");
                $bd_pefa_ocupacion=$myClass->field("pefa_ocupacion");
                $bd_pefa_adjunto1=$myClass->field("pefa_adjunto1");
                $bd_pefa_adjunto2=$myClass->field("pefa_adjunto2");
                
                $nameUsers= $myClass->field('username');
                $fregistro=$myClass->field('pefa_fecharegistro');
                $nameUsersActual=$myClass->field('usernameactual');
                $fregistroActual=$myClass->field('pefa_actualfecha');
              
        }
}else{
    $bd_pefa_vive='SI';
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
		document.frm.tr_conc_id.focus();
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
$form->addHidden("f_id",$bd_pefa_id); // clave primaria
$form->addHidden("nx_pers_id",$id_relacion); // chave estrangeira do relacionamento
$form->addHidden("pagina",$pg); // numero de página que llamo

$myPersona = new clsPersona($id_relacion);
$myPersona->setDatos();
$form->addField("Trabajador: ",$myPersona->field("empleado").' / '.$myPersona->field("pers_dni"));

$tabla=new clsTabla_SQLlista();
$tabla->whereTipo('PARENTESCO');
$tabla->orderUno();
$sqlParentesco=$tabla->getSQL_cbox();
$form->addField("Parentesco: ",listboxField("Parentesco",$sqlParentesco, "tx_tabl_idparentesco",$bd_tabl_idparentesco,"-- Seleccione Valor --",""));

$form->addField("Apellido paterno: ",textField("Apellido paterno","Sr_pefa_apellpaterno",$bd_pefa_apellpaterno,35,30));
$form->addField("Apellido materno: ",textField("Apellido materno","Sr_pefa_apellmaterno",$bd_pefa_apellmaterno,35,30));
$form->addField("Nombres: ",textField("Nombres","Sr_pefa_nombres",$bd_pefa_nombres,35,30));
$form->addField("Fecha de nacimiento: ", $calendar->make_input_field('Fecha de nacimiento',array(),array('name'=> 'Dx_pefa_nacefecha','value'=> $bd_pefa_nacefecha, $readonly=> true)));

$lista_nivel = array("M,Masculino","F,Femenino"); // definición de la lista para campo radio
$form->addField("Sexo: ",radioField("Sexo",$lista_nivel, "xr_pefa_sexo",$bd_pefa_sexo));

$tabla=new clsTabla_SQLlista();
$tabla->whereTipo('TIPO_DOC_IDENTIDAD');
$tabla->orderUno();
$sqlTDoc=$tabla->getSQL_cbox();
$form->addField("Tipo Doc.: ",listboxField("Tipo Documento",$sqlTDoc,"tr_tabl_idtipodocumento",$bd_tabl_idtipodocumento,"",""));
$form->addField("N&uacute;mero: ",numField("N&ordm; Doc.","Sx_pefa_dni",$bd_pefa_dni,10,8,0));

$tabla=new clsTabla_SQLlista();
$tabla->whereTipo('ESTADO_CIVIL');
$tabla->orderUno();
$sqlEstcivil=$tabla->getSQL_cbox();
$form->addField("Estado Civil: ",listboxField("Estado civil",$sqlEstcivil, "tr_tabl_idestadocivil",$bd_tabl_idestadocivil,"",""));

$form->addField("C&oacute;d ESSALUD: ",textField("Cod.ESSALUD","Sx_pefa_codessalud",$bd_pefa_codessalud,15,15));

$form->addField("Ocupaci&oacute;n: ",textField("Ocupación","Sx_pefa_ocupacion",$bd_pefa_ocupacion,80,100));

$lista_nivel = array("SI,SI","NO,NO"); // definición de la lista para campo radio
$form->addField("Vive: ",radioField("Vive",$lista_nivel, "xr_pefa_vive",$bd_pefa_vive));

$form->addHidden("postPath",'escalafon/');
$form->addField("Archivo: ",fileField("Archivo1","pefa_adjunto1" ,$bd_pefa_adjunto1,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));
//$form->addField("Archivo:",fileField("Archivo2","pefa_adjunto2" ,$bd_pefa_adjunto2,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));

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