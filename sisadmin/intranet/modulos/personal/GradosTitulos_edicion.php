<?
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("GradosTitulos_class.php"); 

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();
$param->removePar('relacionamento_id'); /* Remuevo el par�metro */

$id = getParam("id"); // captura la variable que viene del objeto lista

$myClass = new GradoTitulo($id,'Edici&oacute;n de Grado o Titulo');

if (strlen($id)>0) { // edici�n
	$myClass->setDatos();
	if($myClass->existeDatos()){
		$bd_gres_id = $myClass->field("gres_id");
		$bd_gres_descripcion = $myClass->field("gres_descripcion");
		$bd_usua_id = $myClass->field("usua_id");	
                
                $nameUsers= $myClass->field('username');
                $fregistro=$myClass->field('gres_fecharegistro');
                $nameUsersActual=$myClass->field('usernameactual');
                $fregistroActual=$myClass->field('gres_actualfecha');                                
	}
}



?>
<html>
<head>
	<title><?=$myClass->getTitle()?></title>
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
		document.frm.Sr_gres_descripcion.focus();
	}
	</script>
	<? verif_framework(); ?>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?
//echo $myClass->getNameFile2();
pageTitle($myClass->getTitle());

/* botones */
$button = new Button;
if($clear==1){
    $button->addItem(" Regresar ","GradosTitulos_buscar.php".$param->buildPars(true));
}
echo $button->writeHTML();


/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("f_id",$id); // clave primaria

$form->addField("Grado o T&iacute;tulo: ",textField("Nombre","Sr_gres_descripcion",$bd_gres_descripcion,80,100));

$button = new Button;
$button->setDiv(false);
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2);
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