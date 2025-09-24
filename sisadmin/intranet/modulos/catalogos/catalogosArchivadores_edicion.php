<?
/* formulario de ingreso y modificaci�n */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("catalogosArchivadores_class.php");
include("../catalogos/catalogosDependencias_class.php");
include("../catalogos/catalogosTabla_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista

$myClass = new archivador($id,'Archivadores');

if (strlen($id)>0) { // edición
	$myClass->setDatos();
	if($myClass->existeDatos()){
		$bd_arch_id= $myClass->field('arch_id');
		$bd_arch_anno= $myClass->field('arch_anno');
		$bd_arch_descripcion= $myClass->field('arch_descripcion');
		$bd_depe_id= $myClass->field('depe_id');
                $bd_arch_personal= $myClass->field('arch_personal');
                $bd_arch_disponible= $myClass->field('arch_disponible');
                $bd_dependencia= $myClass->field('dependencia');
		$bd_usua_id	= $myClass->field('usua_id');
                $bd_arch_tabltipoarchivador= $myClass->field('arch_tabltipoarchivador');
		$username= $myClass->field("username");
		$usernameactual= $myClass->field("usernameactual");		
	}
}
else{
    $bd_depe_id=getSession("sis_depeid");
}


?>
<html>
<head>
	<title><?=$myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="JavaScript" src="<?=PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?=PATH_INC?>js/libjsgen.js"></script>
	
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
		document.frm.nr_arch_anno.focus();
	}
	</script>
	<? verif_framework(); ?>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?
pageTitle("Edici&oacute;n de ".$myClass->getTitle());


/* botones */
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2);
$button->addItem(" Regresar ","catalogosArchivadores_buscar.php".$param->buildPars(true),"content");

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

/* Instancio la Dependencia */
$dependencia=new dependencia_SQLlista();
if(getSession("sis_userid")>1){
    $dependencia->whereVarios(getSession("sis_persid"));    
    //$dependencia->whereID($depeid);
}
$sqlDependencia=$dependencia->getSQL_cbox();

if(strlen($id)) {
    $form->addField("Dependencia: ",$bd_dependencia);    
}else{
    $form->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia,"nr_depe_id",$depeid,"",""));
}




$tarch=new clsTabla_SQLlista();
$tarch->orderUno();
$tarch->whereTipo('TIPO_ARCHIVADOR');
$sqltipo=$tarch->getSQL_cbox();
$form->addField("Tipo de Archivador",listboxField("Tipo de Archivador",$sqltipo,"tr_arch_tabltipoarchivador",$bd_arch_tabltipoarchivador,"",""));

$form->addField("A&ntilde;o: ",numField("A&ntilde;o","nr_arch_anno",$bd_arch_anno,4,4,0));
$form->addField("Descripci&oacute;n: ",textField("Descripci&oacute;n","Sr_arch_descripcion",$bd_arch_descripcion,80,80));
$form->addField("&iquest;Uso Personal? ",checkboxField("Personal","hx_arch_personal",1,$bd_arch_personal==1));

//solo si es edicion se agrega los datos de auditoria
if(strlen($id)) {
        $form->addField("Habilitado",checkboxField("Disponible","hx_arch_disponible",1,$bd_arch_disponible==1));
	$form->addBreak("<b>Control</b>");
	$form->addField("Creado por: ",$username);
	$form->addField("Actualizado por: ",$usernameactual);
}else{
    $form->addHidden("hx_arch_disponible",1); // clave primaria
}

echo $form->writeHTML();
?>
</body>
</html>

<?
/* cierro la conexión a la BD */
$conn->close();
