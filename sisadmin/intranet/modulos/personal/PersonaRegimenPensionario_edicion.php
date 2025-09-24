<?
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("./PersonaRegimenPensionario_class.php");
include("../catalogos/catalogosTabla_class.php");
include("../catalogos/RegimenPensionario_class.php");
include("../catalogos/AFP_class.php");
include("./Persona_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$id_relacion = getParam("id_relacion"); // captura la variable que viene del objeto lista

$myClass = new clsPersonaRegimenPensionario($id,'R&eacute;gimen Pensionario');

if ($id>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
                $bd_perp_id=$myClass->field("perp_id");
		$bd_pers_id=$myClass->field("pers_id");
                
                $bd_repe_id=$myClass->field("repe_id");
                $bd_afp_id=$myClass->field("afp_id");
                $bd_perp_afpcus=$myClass->field("perp_afpcus");
                $bd_perp_afpafiliacion=dtos($myClass->field("perp_afpafiliacion"));
                $bd_tabl_tipocomision=$myClass->field("tabl_tipocomision");
                
                $bd_perp_adjunto1=$myClass->field("perp_adjunto1");
                $bd_perp_adjunto2=$myClass->field("perp_adjunto2");
                
                $nameUsers= $myClass->field('username');
                $fregistro=$myClass->field('perp_fecharegistro');
                $nameUsersActual=$myClass->field('usernameactual');
                $fregistroActual=$myClass->field('perp_actualfecha');
              
        }
}else{
    $myPersona = new clsPersona($id_relacion);
    $myPersona->setDatos();
    $bd_perp_afpcus=$myPersona->field('pers_afpcus');
    $bd_tabl_tipocomision=$myPersona->field('tabl_tipocomision');
}



/* Archivos para calendario */
require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("pideSPP");

function pideSPP($op,$value,$NameDiv)
{
	global $conn,$calendar,$bd_afp_id,$bd_perp_afpcus,$bd_tabl_tipocomision;

	$objResponse = new xajaxResponse();
	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");

	if($value==1){ /*si es afp*/
		$calendar->calendar_correla=12;

                $sqlAfp=new clsAFP_SQLlista();
                $sqlAfp=$sqlAfp->getSQL_cbox();                
		$oForm->addField("AFP Actual: ",listboxField("AFP Actual",$sqlAfp, "tr_afp_id",$bd_afp_id,"-- Seleccione AFP --",""));
                
                $tabla=new clsTabla_SQLlista();
                $tabla->whereTipo('TIPO_COMISION_AFP');
                $tabla->orderUno();
                $sql=$tabla->getSQL();
                
                $rs = new query($conn, $sql);
                while ($rs->getrow()) {
                    $lista_nivel[].=$rs->field("tabl_id").",". $rs->field("tabl_descripcion");
                }
                $bd_tabl_tipocomision=$bd_tabl_tipocomision?$bd_tabl_tipocomision:160; //TIPO DE AFP:UNICA,         
                $oForm->addField("Tipo Comisi&oacute;n: ",radioField("Tipo Comision",$lista_nivel, "xr_tabl_tipocomision",$bd_tabl_tipocomision,"","H"));                
		$oForm->addField("C&oacute;digo &uacute;nico SPP: ",textField("C&oacute;digo &uacute;nico SPP","Sx_perp_afpcus",$bd_perp_afpcus,20,15));

	}
	else {/*si es 19990*/
                $oForm->addHidden('___afp_id', null);
                $oForm->addHidden('___perp_afpcus',null);

        }
        
        $contenido_respuesta=$oForm->writeHTML();

	if($op==1){
                $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);            
		return $objResponse;
        }else{
		return $contenido_respuesta	;
        }
}
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
		document.frm.tr_repe_id.focus();
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
$form->addHidden("f_id",$bd_perp_id); 
$form->addHidden("nx_pers_id",$id_relacion); // chave estrangeira do relacionamento
$form->addHidden("pagina",$pg); // numero de página que llamo

$myPersona = new clsPersona($id_relacion);
$myPersona->setDatos();
$form->addField("Trabajador: ",$myPersona->field("empleado").' / '.$myPersona->field("pers_dni"));

$sqlRegPensionario= new clsRegimenPensionario_SQLlista();
$sqlRegPensionario=$sqlRegPensionario->getSQL_cbox();
            
$bd_repe_id=$bd_repe_id?$bd_repe_id:1; //LEY 25897
            
$form->addField("R&eacute;gimen Pensionario: ",listboxField("Regimen Pensionario",$sqlRegPensionario, "tr_repe_id",$bd_repe_id,"","onChange=\"xajax_pideSPP(1,this.value,'divRegimenPensionario')\""));
            
$form->addHtml("<tr><td colspan=2><div id='divRegimenPensionario'>\n");
$form->addHtml(pideSPP(2,$bd_repe_id,'divRegimenPensionario'));
$form->addHtml("</div></td></tr>\n");                        
$form->addField("Fecha de Afiliciaci&oacute;n: ", $calendar->make_input_field('Fecha de Afiliación',array(),array('name'=> 'Dx_perp_afpafiliacion','value'=> $bd_perp_afpafiliacion )));

$form->addHidden("postPath",'escalafon/');
$form->addField("Archivo: ",fileField("Archivo1","perp_adjunto1" ,$bd_perp_adjunto1,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));
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

<?
/* cierro la conexión a la BD */
$conn->close();