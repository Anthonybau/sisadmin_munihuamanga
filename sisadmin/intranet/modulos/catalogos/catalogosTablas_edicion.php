<?php
/*
	formulario de ingreso y modificaci�n
*/
include($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");
include("catalogosTabla_class.php");
include("../catalogos/RegimenLaboral_class.php");
/*
	verificación del nivel de usuario
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
$id = getParam("id"); // captura la variable que viene del objeto lista
$clear = getParam("clear");
$busEmpty=getParam("busEmpty"); //permite o no buscar cadenas vacias (muestra todo los registros)
$dbrev = getParam("dbrev");
$setCodigo = getParam("setCodigo");
$colOrden= getParam("colOrden");
$numForm = getParam("numForm")?getParam("numForm"):0;//funciona solo con CLEAR=2, es el numero de formulario en el cual se encuentra el objeto desde donde fue llamado

if (strlen($id)>0) { // edici�n
	$sql = sprintf("select * from tabla where tabl_id='%s'",$id);
	$rs = new query($conn, $sql);
	if ($rs->getrow()) {
		$bd_tabl_id = $rs->field("tabl_id");
                $bd_tabl_codigo= $rs->field("tabl_codigo");
		$bd_tabl_descripcion = $rs->field("tabl_descripcion");
		$bd_tabl_descripaux = $rs->field("tabl_descripaux");
                $bd_tabl_codigoauxiliar= $rs->field("tabl_codigoauxiliar");
                $bd_tabl_configuracion= $rs->field("tabl_configuracion");
                $bd_tabl_adenda= $rs->field("tabl_adenda");
                $bd_tabl_porcent= $rs->field("tabl_porcent");
                $bd_tabl_char= $rs->field("tabl_char");
                $bd_rela_id=$rs->field("rela_id");
                $bd_depe_id= $rs->field("depe_id");
		$bd_usua_id = $rs->field("usua_id");		
	}
}



?>
<html>
<head>
	<title>Tablas-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="<?php echo PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>
        <script language="javascript" src="<?php echo PATH_INC?>js/tree.js"></script>
	<script language='JavaScript'>
	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "../imes/guardar.php?_op=CatTabla&nomeCampoForm=<?php echo getParam("nomeCampoForm")?>&busEmpty=<?php echo $busEmpty?>&dbrev=<?php echo $dbrev?>&colOrden=<?php echo $colOrden?>&setCodigo=<?php echo $setCodigo?>&numForm=<?php echo $numForm?>";
			parent.content.document.frm.submit();
		}
	}
	/*
		se invoca desde la funcion obligacampos (libjsgen.js)
		en esta funci�n se puede personalizar la validaci�n del formulario
		y se ejecuta al momento de gurdar los datos
	*/	
	function mivalidacion(frm) {  
		return true			
	}
	
	/*
		funci�n que define el foco inicial en el formulario
	*/
	function inicializa() {
		parent.content.document.frm.Sr_tabl_descripcion.focus();
	}
	</script>
	<?php 
        verif_framework(); 
        ?>		
		
</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
//$nameTable=getDbValue("select tabl_tiponombre from tabla where tabl_tipo=".getSession("table"));

$nameTable=getSession("table");
pageTitle("Edici&oacute;n de Tabla: ".$nameTable);
/*
	botones,
	configure conforme suas necesidades
*/
$retorno = $_SERVER['QUERY_STRING'];
$button = new Button;
//$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2,$bd_usua_id);
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2,$bd_usua_id);

if($clear==1){
    $button->addItem(" Regresar ","catalogosTablas_buscar.php?busEmpty=$busEmpty&colOrden=$colOrden&dbrev=$dbrev&setCodigo=$setCodigo","content");
}else{
    $button->addItem(" Salir sin Guardar ","javascript:if(confirm('Seguro de Salir sin Guardar?')){parent.parent.close()}","content");	
}

echo $button->writeHTML();

$abas = new Abas();
$abas->addItem("General",true);
echo $abas->writeHTML();

echo "<br>";

/*
	Formulario
*/
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$bd_tabl_id); // clave primaria
$form->addHidden("xx_tabl_tipo",getSession("table")); // tipo 1->horario de recaudaci�n

    if($setCodigo==1){
        $form->addField("C&oacute;digo: ",numField("C&oacute;digo","nr_tabl_codigo",$bd_tabl_codigo,6,6,0));
    }
//if (substr(getSession("table"),0,4)=='TEMA'){
//    //acepta valores en minuscula
    $form->addField("Descripci&oacute;n: ",textField("Descripci&oacute;n","Sr_tabl_descripcion",$bd_tabl_descripcion,50,100));
//}
//else{
//    //acepta valores en mayuscula
//    $form->addField("Descripci&oacute;n: ",textField("Descripci&oacute;n","Sr_tabl_descripcion",$bd_tabl_descripcion,60,60));
//}

if($dbrev){
	$form->addField("Descripci&oacute;n Breve: ",textField("Descripci&oacute;n Breve","sx_tabl_descripaux",$bd_tabl_descripaux,30,30));
}

if($nameTable=='CONDICION_LABORAL'){
        $form->addField("Configuraci&oacute;n: ",textField("Configuracion","sx_tabl_configuracion",$bd_tabl_configuracion,35,35)."&nbsp;".
            help("Utilice en Configuraci&oacute;n (separado por comas)","Configuraci&oacute;n de Datos Laborales:<br><br> <B>CR:</B>CATEGORIA REMUNERATIVA<BR><B>CP:</B>CADENA PRESUPUESTAL<BR><B>FI:</B>FECHA DE INICIO<BR><B>FT:</B>FECHA DE TERMINO<BR><B>FC:</B>FECHA DE CESE<BR><B>CO:</B>CONTRATO<BR><B>RE:</B>RESOLUCION<BR><B>DO:</B>DOCUMENTO<BR><B>BA:</B>BANCO<BR><B>RL:</B>REGIMEN LABORAL<BR><B>RP:</B>REGIMEN PENSIONARIO<BR><B>NH:</B>NUMERO DE HIJOS<BR><B>CC:</B>CARGO CLASIFICADO<BR><B>CF:</B>CARGO FUNCIONAL<BR><B>CL:</B>CLASIFICACION EN PLANILLA<BR>",2));

        $sqlRegLaboral= new clsRegimenLaboral_SQLlista();
        $sqlRegLaboral=$sqlRegLaboral->getSQL_cbox();            
        $form->addField("R&eacute;gimen Laboral: ",listboxField("Regimen Laboral",$sqlRegLaboral, "tr_rela_id",$bd_rela_id,"",""));
            
}elseif($nameTable=='TIPO_PARTE_PROCESAL'){
    $sqlTipo = array(1=> "DEMANDANTE/AGRAVIADO",
                     2=> "DEMANDADO/INCULPADO",
                     3=> "TERCERO CIVILMENTE RESPONSABLE");
    $form->addField("Tipo", listboxField("Tipo", $sqlTipo, "nr_tabl_codigo", $bd_tabl_codigo));
}

if($nameTable=='ESTADO_PROPUESTA'){
    $sqlTipo = array(1=> "ACUMULA PUNTAJE",
                     2=> "NO ACUMULA PUNTAJE");
    $form->addField("Tipo", listboxField("Tipo", $sqlTipo, "nr_tabl_codigo", $bd_tabl_codigo));
}

if(inlist($nameTable,'TIPO_DOCUMENTO_CONTRATO')){
    $form->addField(checkboxField("Tipo Adenda","hx_tabl_adenda",1,$bd_tabl_adenda==1),"Tipo Adenda");
}
 
if(inlist($nameTable,'VISTA_COMPONENTE')){
    $form->addField("C&oacute;d. P/Todas Filas: ",textField("Cod. P/Todas Filas","sx_tabl_char",$bd_tabl_char,2,1));
}

if(inlist($nameTable,'TIPO_PRECIO')){
    $form->addField("% Descuento: ",numField("% Descuento","nx_tabl_porcent","$bd_tabl_porcent",6,6,2,false,""));
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
	cierro la conexi�n a la BD
*/
$conn->close();