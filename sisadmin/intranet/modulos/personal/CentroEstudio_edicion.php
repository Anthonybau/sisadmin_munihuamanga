<?
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("CentroEstudio_class.php"); 

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();
$param->removePar('relacionamento_id'); /* Remuevo el par�metro */

$id = getParam("id"); // captura la variable que viene del objeto lista
$clear = getParam("clear");

$myClass = new CentroEstudio($id,'Centros de Estudio');

if (strlen($id)>0) { // edici�n
	$myClass->setDatos();
	if($myClass->existeDatos()){
		$bd_cees_id = $myClass->field("cees_id");
                $bd_cees_ruc= $myClass->field("cees_ruc");
		$bd_cees_nombre = $myClass->field("cees_nombre");
		$bd_cees_nombrecorto = $myClass->field("cees_nombrecorto");
		$bd_cees_tipo = $myClass->field("cees_tipo");
		$bd_tabl_tipo_ce = $myClass->field("tabl_tipo_ce");
		$bd_ubig_id = $myClass->field("ubig_id");
		$bd_usua_id = $myClass->field("usua_id");	
                
                $nameUsers= $myClass->field('username');
                $fregistro=$myClass->field('cees_fecharegistro');
                $nameUsersActual=$myClass->field('usernameactual');
                $fregistroActual=$myClass->field('cees_actualfecha');                
	}
}



?>
<html>
<head>
	<title>Centros de Estudio-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="JavaScript" src="<?=PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?=PATH_INC?>js/libjsgen.js"></script>
        <script language="javascript" src="../../library/js/lookup2.js"></script>
        
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>   
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                	

	<script language='JavaScript'>
	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			document.frm.target = "controle";
			document.frm.action = "<?=$myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
			document.frm.submit();
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
		document.frm.Sr_cees_nombre.focus();
	}
	</script>
	<? verif_framework(); ?>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?
//echo $myClass->getNameFile2();
pageTitle("Edici&oacute;n de Centro de Estudio");

/* botones */
$button = new Button;
if($clear==1){
    $button->addItem(" Regresar ","CentroEstudio_buscar.php".$param->buildPars(true));
}
echo $button->writeHTML();


/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("f_id",$id); // clave primaria

$form->addField("Raz&oacute;n Social: ",textField("Razon Social","Sr_cees_nombre",$bd_cees_nombre,80,100));
$form->addField("R.U.C.: ",textField("RUC","Sr_cees_ruc",$bd_cees_ruc,14,11));

//$form->addField("Nombre corto: ",textField("Nombre corto","Sr_cees_nombrecorto",$bd_cees_nombrecorto,35,30));
$lista_clase = array("1,P&uacute;blica","2,Privada");
$form->addField("Clase: ",radioField("Clase",$lista_clase, "xr_cees_tipo",$bd_cees_tipo));


// agregado //
//$sqltMenu="SELECT tabl_id,tabl_descripcion FROM tabla WHERE tabl_tipo='TIPO_IE' ORDER BY 1 ";
//$form->addField("Tipo: ",listboxField("Tipo",$sqltMenu,"tr_tabl_tipo_ce",$bd_tabl_tipo_ce," Seleccione Clase CE  --",""));
// fin agregado //


$lugar = new Lookup();
$lugar->setTitle("Ubigeo");
$lugar->setNomeCampoForm("Lugar","sr_ubig_id");
$sql = "SELECT ubig_id,distrito
                FROM view_ubigeo ";

setSession("sqlLkupEmp", $sql);
$lugar->setNomeTabela("sqlLkupEmp");  //nombre de tabla
$lugar->setNomeCampoChave("ubig_id");  //campo clave
$lugar->setNomeCampoExibicao("distrito");
$lugar->setListaInicial(0);
$lugar->setUpCase(true);//para busquedas con texto en mayuscula
$lugar->readOnly(false);
$lugar->setSize(70);//tama�o del campo
$lugar->setValorCampoForm($bd_ubig_id);
$form->addField("Lugar: ",$lugar->writeHTML());

/* botones */
$button = new Button;
$button->setDiv(false);
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2,$bd_usua_id);
$form->addField("",$button->writeHTML());

//solo si es edicion se agrega los datos de auditoria
if(strlen($id)) {
    $form->addBreak("<b>Control</b>");
    $form->addField("Creado: ",$nameUsers.'/'.substr($fregistro,0,19));
    $form->addField("Actualizado: ",$nameUsersActual.'/'.substr($fregistroActual,0,19));
}        

echo $form->writeHTML();
?>
</body>
    <script>
    $("select").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true,
            width: '50%'
            });
    
    </script>  
</html>
<?
/* cierro la conexi�n a la BD */
$conn->close();