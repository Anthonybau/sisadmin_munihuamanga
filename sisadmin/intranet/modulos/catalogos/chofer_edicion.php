<?php
/* formulario de ingreso y modificacion */
include($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");
include("catalogosTabla_class.php");
/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("chofer_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$clear = getParam("clear"); 

$myClass = new chofer($id,"Transportista");

if (strlen($id)>0) { // edición
	$myClass->setDatos();
	if($myClass->existeDatos()){
            $bd_chof_id = $myClass->field('chof_id');
            $bd_chof_descripcion = $myClass->field('chof_descripcion');  
            $bd_tabl_tipopersona = $myClass->field('tabl_tipopersona');
            $bd_chof_dni = $myClass->field('chof_dni');
            $bd_chof_apellidos = $myClass->field('chof_apellidos');
            $bd_chof_nombres = $myClass->field('chof_nombres');
            $bd_chof_vehiculo = $myClass->field('chof_vehiculo');
            $bd_chof_placa = $myClass->field('chof_placa');
            $bd_chof_licencia = $myClass->field('chof_licencia');
            $bd_usua_id = $myClass->field('usua_id');
            $bd_chof_estado = $myClass->field("chof_estado");
            $bd_transp_razsocial= $myClass->field("transp_razsocial");
            $bd_transp_ruc= $myClass->field("transp_ruc");
            $bd_tipo= $myClass->field("tipo");
                    
            $bd_chof_fregistro = $myClass->field('chof_fregistro');
            $bd_chof_actualfecha = $myClass->field('chof_actualfecha');
            $bd_chof_actualusua = $myClass->field('chof_actualusua');
            $username= $myClass->field("username");
            $usernameactual= $myClass->field("usernameactual");		
	}
}else{
    $bd_tabl_tipopersona=2;
}


// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("pideTipoPersona");

function pideTipoPersona($op,$mivalor,$NameDiv)
{
    global  $bd_transp_razsocial,$bd_transp_ruc,
            $bd_chof_dni,$bd_chof_apellidos,$bd_chof_nombres,$bd_chof_licencia,
            $bd_chof_vehiculo,$bd_chof_placa;

	$objResponse = new xajaxResponse();

	$otable = new AddTableForm();
	$otable->setLabelWidth("20%");
	$otable->setDataWidth("80%");

	switch($mivalor){
	   case 1: // Si es persona juridica
                $otable->addField("RUC: ",textField("RUC","Sr_transp_ruc",$bd_transp_ruc,14,11));	   			
                $otable->addField("Raz&oacute;n Social: ",textField("Raz&oacute;n Social","Sr_transp_razsocial",$bd_transp_razsocial,100,120));	   
                break;
			
	   case 2: // persona natural
                $otable->addField("DNI: ",textField("DNI","Sr_chof_dni",$bd_chof_dni,10,8));
                $otable->addField("Apellidos: ",textField("Apellidos","Sr_chof_apellidos",$bd_chof_apellidos,40,35));
                $otable->addField("Nombres: ",textField("Nombres","Sr_chof_nombres",$bd_chof_nombres,40,35));
                $otable->addField("Licencia: ",textField("Licencia","Sr_chof_licencia",$bd_chof_licencia,20,20));
                $otable->addField("Veh&iacute;culo: ",textField("Vehiculo","Sr_chof_vehiculo",$bd_chof_vehiculo,30,30));
                $otable->addField("Placa: ",textField("Placa","Sr_chof_placa",$bd_chof_placa,20,20));
                break;
            
	   case 3: // Otros
                $otable->addField("Raz&oacute;n Social: ",textField("Raz&oacute;n Social","Sr_transp_razsocial",$bd_transp_razsocial,100,120));	   
                $otable->addField("Veh&iacute;culo: ",textField("Vehiculo","Sr_chof_vehiculo",$bd_chof_vehiculo,30,30));
            
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
	<title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="../../library/js/focus.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>
        
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>   
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                        
	<script type="text/javascript" src="../../library/jquery-autosize/jquery.autosize.js"></script>
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
		document.frm.Sr_chof_dni.focus();
	}
	</script>
        <?php 
        $xajax->printJavascript(PATH_INC.'ajax/'); 
        verif_framework(); 
        ?>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("Edici&oacute;n de ".$myClass->getTitle());


/* botones */
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2,$bd_usua_id);
$button->addItem(" Regresar ","chofer_buscar.php".$param->buildPars(true),"content");

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
$form->addHidden("f_id",$id); // clave primaria
if($id){
    $form->addField("C&oacute;digo: ",$bd_chof_id);
    $form->addField("Tipo",$bd_tipo);
}else{

    $tabla=new clsTabla_SQLlista();
    $tabla->whereTipo('TIPO_PROVEEDOR');
    $tabla->whereNoCodigo(3);
    $sqltipo=$tabla->getSQL_cboxCodigo();

    $form->addField("Tipo",listboxField("Tipo ",$sqltipo,"tr_tabl_tipopersona",$bd_tabl_tipopersona,"-- Seleccione Tipo --","onChange=\"xajax_pideTipoPersona(1,this.value,'divTipoPersona');\""));
}

$form->addHtml("<tr><td colspan=2><div id='divTipoPersona'>\n");
$form->addHtml(pideTipoPersona(2,$bd_tabl_tipopersona,'divTipoPersona'));
$form->addHtml("</div></td></tr>\n");



//$form->addField("Descripci&oacute;n: ",textField("Descripcion","Sx_chof_descripcion",$bd_chof_descripcion,50,50));

if(strlen($id)) {
        $form->addBreak("<b>Estado</b>");
        $form->addField("Activo: ",checkboxField("Activo","hx_chof_estado",1,$bd_chof_estado==1));    
	$form->addBreak("<b>Control</b>");
	$form->addField("Creado por: ",$username.' '.$bd_chof_fregistro);       
}else{
    $form->addHidden("hx_chof_estado",1);
}

echo $form->writeHTML();
?>
</body>
</html>

<?php
/* cierro la conexión a la BD */
$conn->close();