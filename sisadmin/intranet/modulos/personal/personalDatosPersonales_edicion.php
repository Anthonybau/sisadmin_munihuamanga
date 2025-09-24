<?php
/* formulario de ingreso y modificaci�n */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("personalDatosPersonales_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista

$myClass = new clsPersona($id,'Datos Personales');

if (strlen($id)>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
		$bd_pers_id = $myClass->field("pers_id");
		$bd_pers_apellpaterno = $myClass->field("pers_apellpaterno");
		$bd_pers_apellmaterno = $myClass->field("pers_apellmaterno");
		$bd_pers_nombres = $myClass->field("pers_nombres");
		$bd_pers_nacefecha = dateFormat($myClass->field("pers_nacefecha"),"Y-m-d","d/m/Y");

		$bd_pers_sexo = $myClass->field("pers_sexo");
		$bd_pers_nacionalidad = $myClass->field("pers_nacionalidad");
		$bd_tabl_idnacionalidad=$myClass->field("tabl_idnacionalidad");

		$bd_pers_libmilitar = $myClass->field("pers_libmilitar");
		$bd_pers_dni = $myClass->field("pers_dni");
		$bd_pers_ruc = $myClass->field("pers_ruc");
		$bd_pers_brevete = $myClass->field("pers_brevete");
		$bd_pers_codessalud = $myClass->field("pers_codessalud");
		$bd_pers_cus = $myClass->field("pers_cus");
		$bd_ubig_iddireccion = $myClass->field("ubig_iddireccion");
		$bd_pers_direccion = $myClass->field("pers_direccion");
		$bd_pers_telefono = $myClass->field("pers_telefono");
		$bd_pers_movil = $myClass->field("pers_movil");
		$bd_tabl_idestadocivil = $myClass->field("tabl_idestadocivil");
		$bd_pers_email = $myClass->field("pers_email");
		$bd_pers_foto= iif($myClass->field("pers_foto"),"==","","../../img/standar_foto.jpg",PUBLICUPLOAD.'escalafon/'.$myClass->field("pers_foto"));
                $bd_pers_activo=$myClass->field('pers_activo');


                $username= $myClass->field('username');
                $usernameactual= $myClass->field('usernameactual');
		$bd_usua_id = $myClass->field("usua_id");
        }

}else{ // Si es nuevo
	$bd_pers_sexo = 'M';
	$bd_pers_foto="../../img/standar_foto.jpg";
	$bd_pers_tipdoc=1;
        $bd_pers_activo=1;
}

/* Archivos para calendario */
require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("addEmpleado");
function addEmpleado($formdata)
{
    $objResponse = new xajaxResponse();
    
    $apellidop=strtoupper(substr($formdata['paterno'],0,35));
    $apellidom=strtoupper(substr($formdata['materno'],0,35));
    $nombres=strtoupper(substr($formdata['nombre'],0,40));
    
    if($formdata['sexo']){
        $sexo=substr($formdata['sexo'],0,1);
    }else{
        $sexo='';
    }
    
    $fnacimiento=$formdata['nacimiento'];    
    
    $objResponse->addClear("divBuscarDNIEmpleado",'innerHTML');
    $objResponse->addScript("document.frm.Sr_pers_apellpaterno.value='$apellidop'");
    $objResponse->addScript("document.frm.Sr_pers_apellmaterno.value='$apellidom'");
    $objResponse->addScript("document.frm.Sr_pers_nombres.value='$nombres'");
    
    if($fnacimiento){
        $fnacimiento=dtos($fnacimiento);
        $objResponse->addScript("document.frm.Dr_pers_nacefecha.value='$fnacimiento'");
    }
    
    if($sexo){
        $objResponse->addScript("document.frm.xr_pers_sexo.value='$sexo'");
    }
    
    return $objResponse;
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

        function consultar_DNI(codigo){
                $.ajax({
                url : '<?php echo SIS_URL_SUNAT_RENIEC ?>',
                method :  'POST',
                dataType : "json",
                data: {'codigo' : codigo }
                        }).then(function(data){
                            if(data.success == true) {
                                xajax_addEmpleado(data.result);
                            }else{
                                document.getElementById('divBuscarDNIEmpleado').innerHTML = '<font color=red>'+data.message+'</font>'
                            }
                        }, function(reason){
                            alert(reason.responseText);
                            //console.log(reason);
                        });

         }        
         
	/*
		funci�n que define el foco inicial en el formulario
	*/
	function inicializa() {
		document.frm.Sr_pers_dni.focus();
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


/* botones */
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2,$bd_usua_id);
$button->addItem(" Regresar ","personalDatosPersonales_buscar.php".$param->buildPars(true),"content");

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
$form->setUpload(true);
$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$bd_pers_id); // clave primaria
$form->addHidden("pagina",$pg); // numero de página que llamo

$form->addHidden("postPath",'escalafon/');
$form->addDivImage(divImage("pers_foto",$bd_pers_foto, $ImgWidth=115,$ImgHeight=130, $DivTop=10, $DivWidth=200, $Divleft=500,  $classFoto="contenedorfoto", "onchange=\"RefreshFoto('DivImage',frm.pers_foto)\""));

$lista_nivel = array("1,ACTIVO","2,DE BAJA"); // definici�n de la lista para campo radio
$form->addField("Estado: ",radioField("Estado",$lista_nivel, "xr_pers_activo",$bd_pers_activo,"","H"));

$form->addBreak("<b>Datos Personales</b>");
$sqlTDoc = "SELECT tabl_codigo,tabl_descripcion FROM tabla WHERE tabl_tipo='TIPO_DOC_IDENTIDAD' ORDER BY 1 "; //tipos de documentos de identidad
$form->addField("Tipo de Documento: ",listboxField("Tipo de Documento",$sqlTDoc,"tr_pers_tipdoc",$bd_pers_tipdoc));
$btnBuscarCodigo="<input type=\"button\" onClick=\"javascript:consultar_DNI(document.frm.Sr_pers_dni.value);document.getElementById('divBuscarDNIEmpleado').innerHTML = 'Espere, Buscando...'\" value=\"Buscar\">";
$form->addField("N&uacute;mero: ",numField("N&ordm; Doc.","Sr_pers_dni",$bd_pers_dni,10,8,0,false)."&nbsp;$btnBuscarCodigo&nbsp");    
$form->addHtml("<tr><td colspan=2><div id='divBuscarDNIEmpleado'></div></td></tr>\n");

$form->addField("Apellido paterno: ",textField("Apellido paterno","Sr_pers_apellpaterno",$bd_pers_apellpaterno,35,35));
$form->addField("Apellido materno: ",textField("Apellido materno","Sr_pers_apellmaterno",$bd_pers_apellmaterno,35,35));
$form->addField("Nombres: ",textField("Nombres","Sr_pers_nombres",$bd_pers_nombres,40,40));
$form->addField("Fecha de nacimiento: ", $calendar->make_input_field('Fecha de nacimiento',array(),array('name'=> 'Dr_pers_nacefecha','value'=> $bd_pers_nacefecha )));

if($id){
    $form->addField("Edad: ",calcTiempo(stod($bd_pers_nacefecha)));
}

$lista_nivel = array("M,Masculino","F,Femenino"); // definici�n de la lista para campo radio

$form->addField("Sexo: ",radioField("Sexo",$lista_nivel, "xr_pers_sexo",$bd_pers_sexo,"","H"));
$sqlNacionalidad = "SELECT tabl_id as id, tabl_descripcion as Descripcion FROM tabla WHERE tabl_tipo='NACIONALIDAD' ORDER BY 1 ";
$form->addField("Nacionalidad: ",listboxField("Nacionalidad",$sqlNacionalidad, "tr_tabl_idnacionalidad",$bd_tabl_idnacionalidad,"",""));

$form->addField("Direcci&oacute;n: ",textField("Direcci&oacute;n","Sr_pers_direccion",$bd_pers_direccion,80,80));
$form->addField("Tel&eacute;fono: ",textField("Tel&eacute;fono","Sx_pers_telefono",$bd_pers_telefono,12,12));
$form->addField("M&oacute;vil: ",textField("M&oacute;vil","Sx_pers_movil",$bd_pers_movil,12,12));
$form->addField("Email: ",textField("Email","cx_pers_email",$bd_pers_email,55,50));

//$sqlProfesion = "SELECT tabl_id, tabl_descripcion as Descripcion
//                    FROM tabla WHERE tabl_tipo='PROFESION' ORDER BY tabl_id ";
//$form->addField("Profesi&oacute;n: ",listboxField("Profesi&oacute;n",$sqlProfesion, "tr_tabl_profesion",$bd_tabl_profesion));




//solo si es edicion se agrega los datos de auditoria
if(strlen($id)) {
    $form->addBreak("<b>Control</b>");
    $form->addField("Creado por: ",$username);
    $form->addField("Actualizado por: ",$usernameactual);
}else{
    $form->addHidden("nr_depe_id",2); 
    $form->addHidden("nr_tabl_idsitlaboral",43); 
    $form->addHidden("nr_rela_id",9); 
}

echo $form->writeHTML();
?>
</body>
</html>

<?php
/* cierro la conexión a la BD */
$conn->close();