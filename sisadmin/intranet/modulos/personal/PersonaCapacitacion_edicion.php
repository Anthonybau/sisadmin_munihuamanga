<?
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("./PersonaCapacitacion_class.php");
include("../catalogos/catalogosTabla_class.php");
include("./Persona_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$id_relacion = getParam("id_relacion"); // captura la variable que viene del objeto lista

$myClass = new clsPersonaCapacitacion($id,'Capacitaci&oacute;n');

if ($id>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
                $bd_peca_id=$myClass->field("peca_id");
		$bd_pers_id=$myClass->field("pers_id");
                
		$bd_peca_fechadocu= dtos($myClass->field("peca_fechadocu"));		
		$bd_peca_nombrecurso= $myClass->field("peca_nombrecurso");
		$bd_peca_organizador= $myClass->field("peca_organizador");
		$bd_peca_fechainicio= dtos($myClass->field("peca_fechainicio"));
		$bd_peca_fechafin= dtos($myClass->field("peca_fechafin"));
		$bd_peca_horas= $myClass->field("peca_horas");
		$bd_peca_dias= $myClass->field("peca_dias");
		$bd_tabl_tparticipante= $myClass->field("tabl_tparticipante");
		$bd_peca_observaciones= $myClass->field("peca_observaciones");
		$bd_usua_id= $myClass->field("usua_id");
		$bd_peca_lugar= $myClass->field("peca_lugar");
		$bd_tabl_tevento= $myClass->field("tabl_tevento");
		$bd_peca_valido= $myClass->field("peca_valido");
                $bd_peca_adjunto1=$myClass->field("peca_adjunto1");
                $bd_peca_adjunto2=$myClass->field("peca_adjunto2");
                
                $nameUsers= $myClass->field('username');
                $fregistro=$myClass->field('peca_fecharegistro');
                $nameUsersActual=$myClass->field('usernameactual');
                $fregistroActual=$myClass->field('peca_actualfecha');
              
        }
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
		document.frm.tr_tabl_tevento.focus();
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
$form->addHidden("f_id",$bd_peca_id); // clave primaria
$form->addHidden("nx_pers_id",$id_relacion); // chave estrangeira do relacionamento
$form->addHidden("pagina",$pg); // numero de página que llamo

$myPersona = new clsPersona($id_relacion);
$myPersona->setDatos();
$form->addField("Trabajador: ",$myPersona->field("empleado").' / '.$myPersona->field("pers_dni"));

$tabla=new clsTabla_SQLlista();
$tabla->whereTipo('TIPO_EVENTO');
$tabla->orderUno();
$sqlTipoEvento=$tabla->getSQL_cbox();
$form->addField("Tipo Evento: ",listboxField("Tipo Evento",$sqlTipoEvento, "tr_tabl_tevento",$bd_tabl_tevento,"-- Seleccione Valor --",""));

$tabla=new clsTabla_SQLlista();
$tabla->whereTipo('TIPO_PARTICIPANTE');
$tabla->orderUno();
$sqlTipoParticipante=$tabla->getSQL_cbox();
$form->addField("Tipo Participante: ",listboxField("Tipo Participante",$sqlTipoParticipante, "tr_tabl_tparticipante",$bd_tabl_tparticipante,"-- Seleccione Valor --",""));

$form->addField("Fecha de Doc: ", $calendar->make_input_field('Fecha de Doc',array(),array('name'=> 'Dr_peca_fechadocu','value'=> $bd_peca_fechadocu)));
$form->addField("Inst.Organizadora: ",textField("Inst.Organizadora","Sr_peca_organizador",$bd_peca_organizador,80,80));
$form->addField("Curso: ",textAreaField("Curso","Er_peca_nombrecurso",$bd_peca_nombrecurso,3,80,200));
$form->addField("Lugar: ",textField("Lugar","Sx_peca_lugar",$bd_peca_lugar,80,80));
$form->addField("Fecha de Inicio: ", $calendar->make_input_field('Fecha de Inicio',array(),array('name'=> 'Dx_peca_fechainicio','value'=> $bd_peca_fechainicio)));
$form->addField("Fecha de Finalizaci&oacute;n: ", $calendar->make_input_field('Fecha de Finalizacion',array(),array('name'=> 'Dx_peca_fechafin','value'=> $bd_peca_fechafin)));

$form->addField("Duraci&oacute;n en Horas: ",numField("Duracion en Horas","nx_peca_horas",$bd_peca_horas,4,4,0));			
$form->addField("Duraci&oacute;n en D&iacute;as: ",numField("Duracion en Dias","nx_peca_dias",$bd_peca_dias,4,4,0));			

$form->addField("Acorde Al Puesto que Ocupa: ",checkboxField("Acorde Al Puesto que Ocupa","hx_peca_valido",1,$bd_peca_valido==1));

$form->addHidden("postPath",'escalafon/');
$form->addField("Archivo: ",fileField("Archivo1","peca_adjunto1" ,$bd_peca_adjunto1,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));
$form->addField("Archivo:",fileField("Archivo2","peca_adjunto2" ,$bd_peca_adjunto2,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));

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