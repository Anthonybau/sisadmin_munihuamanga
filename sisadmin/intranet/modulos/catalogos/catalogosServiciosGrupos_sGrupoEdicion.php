<?php
/*
	formulario de ingreso y modificaci�n
*/
include("../../library/library.php");

/*
	verificaci�n del n�vel de usuario
*/
verificaUsuario(1);

/*
	establecer conexi�n con la BD
*/
$conn = new db();
$conn->open();

/*
	tratamiento de campos
*/
$relacionamento_id = getParam("relacionamento_id"); // captura a chave estrangeira do relacionamento
$id = getParam("id");

if ($id>0) {
	$sql = sprintf("SELECT * FROM  servicio_sgrupo WHERE sesg_id=%d",$id);
	
	$rs = new query($conn, $sql);
	if ($rs->getrow()) {
		$bd_sesg_id = $rs->field("sesg_id");
		$bd_segr_id = $rs->field("segr_id");
		$bd_sesg_descripcion = $rs->field("sesg_descripcion");		
		$bd_sesg_descripbreve= $rs->field("sesg_descripbreve");		
                $bd_sesg_convenio_porcent= $rs->field("sesg_convenio_porcent");
                $bd_sesg_cementerio= $rs->field("sesg_cementerio");
		$bd_usua_id = $rs->field("usua_id");				
                
	}
}else{
    $bd_sesg_convenio_porcent=0;
}
?>
<html>
<head>
	<title>Grupos de Servicios M&eacute;dicos/Sub Grupos-Edicion</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="../../library/js/focus.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>		
	<script language='JavaScript'>
	/*
		fun��o que chama a rotina de salvamento, altere somente o nome da p�gina
	*/
	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "../imes/guardar.php?_op=SGrpoServ";
			parent.content.document.frm.submit();
			//javascript:top.close();
		}
	}
	/*
		se invoca desde la funcion obligacampos (libjsgen.js)
		en esta funci�n se puede personalizar la validaci�n del formulario
		y se ejecuta al momento de gurdar los datos
	*/	
	function mivalidacion(frm) {  
            var sError="Mensajes del sistema: "+"\n\n"; 	
            var nErrTot=0; 	 
		if (nErrTot>0){ 		
			alert(sError)
			return false
		}else
			return true			
	}
		
	/*
		funcion que define el foco inicial del formulario
	*/
	function inicializa() {
		parent.content.document.frm.Sr_sesg_descripcion.focus();
	}
	
	function finaliza() {
		top.opener.location.reload();
	}
	</script>
	<?php
        verif_framework(); 
        ?>	
</head>
<body class="contentBODY" onLoad="inicializa()" onUnload="finaliza()">

<?php

//pageTitle("Edici&oacute;n de Sub Grupos de Servicios M&egrave;dicos","");
pageTitle("Edici&oacute;n de Sub Grupo");

/*
	botones,
	configure conforme sus necesidades
*/
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2,$bd_usua_id);
$button->addItem(" Regresar ","javascript:top.close()","content");
echo $button->writeHTML();

echo "<br>";
/*
	Formulario
*/
$form = new Form("frm", "objeto_salvar.php", "POST", "controle", "100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s"); // vari�vel de controle
$form->addHidden("f_id",$id); // chave prim�ria
$form->addHidden("sx_segr_id",$relacionamento_id); // chave estrangeira do relacionamento
$form->addHidden("pagina",getParam("pagina")); // n�mero da p�gina que chamou

/*
	crea el recordset, elo �ltimo parametro corresponde a la cantidad de
	registros por p�gina
*/
$nameGrupo=getDbValue("SELECT segr_descripcion FROM servicio_grupo WHERE segr_id='$relacionamento_id'");
$form->addField("Grupo: ",$nameGrupo);
$form->addField("Sub Grupo: ",textField("Sub Grupo","Sr_sesg_descripcion",$bd_sesg_descripcion,50,50));
$form->addField("Breve: ",textField("Breve","Sr_sesg_descripbreve",$bd_sesg_descripbreve,20,20));

$tipo=getDbValue("SELECT segr_tipo FROM servicio_grupo WHERE segr_id='$relacionamento_id'");
if($tipo=='T'){
    if(SIS_EMPRESA_TIPO==3){//beneficencias
        $form->addField("Exclusivo P/Ventas en Cementerio: ",checkboxField("Exclusivo P/Ventas en Cementerio","hx_sesg_cementerio",1,$bd_sesg_cementerio==1));
    }
}
//solo si es edicion se agrega los datos de auditoria
if($id) {
	$nameUsers=getDbValue("select usua_login from usuario where usua_id=$bd_usua_id");
	$form->addBreak("<b>Control</b>");
	$form->addField("Responsable: ",$nameUsers);
}

echo $form->writeHTML();
?>
</body>
</html>
<?php
/*
	encerra a conex�o com o banco de dados
*/
$conn->close();