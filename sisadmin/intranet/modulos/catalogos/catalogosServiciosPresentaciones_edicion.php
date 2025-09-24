<?php
/* formulario de ingreso y modificacion */
include("../../library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("catalogosServiciosPresentaciones_class.php");
include("catalogosServicios_class.php");
include("../catalogos/catalogosTabla_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$id_relacion = getParam("id_relacion"); // captura la variable que viene del objeto lista

$myClass = new clsServicioPresentaciones($id,'Presentaci&oacute;n del Producto');

if ($id>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
              $bd_sepr_id=$myClass->field("sepr_id");
              $bd_sepr_codigo_barras=$myClass->field("sepr_codigo_barras");
              $bd_tabl_umedida=$myClass->field("tabl_umedida");
              $bd_tabl_tipoprecio=$myClass->field("tabl_tipoprecio");
              $bd_sepr_umedida=$myClass->field("sepr_umedida");
              $bd_sepr_equi_unidades=$myClass->field("sepr_equi_unidades");
              $bd_sepr_precio=$myClass->field("sepr_precio");
              
              
              $bd_usua_id = $myClass->field("usua_id"); 
              $nameUsers= $myClass->field('username');
              $fregistro=$myClass->field('sepr_fregistro');
              $nameUsersActual=$myClass->field('usernameactual');
              $fregistroActual=$myClass->field('sepr_actualfecha');
        }
}


?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="<?php echo PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>

        <script type="text/javascript" src="../../library/jquery/jquery-1.11.2.min.js"></script>   
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                
	

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
		document.frm.tr_tabl_umedida.focus();
	}
	</script>
	<?php
            verif_framework();
        ?>

</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("Edici&oacute;n de ".$myClass->getTitle());


$myConcepto = new servicios_SQLlista();
$myConcepto->whereID($id_relacion);
$myConcepto->setDatos();

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$bd_sepr_id); // clave primaria
$form->addHidden("nr_serv_codigo",$id_relacion); // chave estrangeira do relacionamento
$form->addHidden("pagina",$pg); // numero de página que llamo
$form->addHidden("nr_depe_id",$myConcepto->field("depe_id"));

$form->addField("C&oacute;digo de Servicio: ",$myConcepto->field("serv_id"));
$form->addField("Descripci&oacute;n: ",$myConcepto->field("serv_descripcion"));
//$form->addField("Grupo: ",$myConcepto->field("segr_id")." ".$myConcepto->field("grupo"));
//$form->addField("Sub grupo: ",$myConcepto->field("sesg_id")." ".$myConcepto->field("sgrupo"));

$tabla=new clsTabla_SQLlista();
$tabla->whereTipo('UNIDAD_MEDIDA');
$tabla->orderUno();        
$sqlUnidades=$tabla->getSQL_cbox();
$form->addField("U.Medida: <font color=red>*</font>",listboxField("U.Medida",$sqlUnidades,"tr_tabl_umedida",$bd_tabl_umedida,"-- Seleccione Unidad de Medida --","", "","class=\"my_select_box\""));

if( SIS_EMPRESA_TIPO!=4 ) {//ALMACENES
    $tabla=new clsTabla_SQLlista();
    $tabla->whereTipo('TIPO_PRECIO');
    $tabla->whereNoPorcent();
    $tabla->orderUno();        
    $sqlTipoPrecio=$tabla->getSQL_cbox();
    $form->addField("Tipo Precio: <font color=red>*</font>",listboxField("Tipo Precio",$sqlTipoPrecio,"tr_tabl_tipoprecio",$bd_tabl_tipoprecio,"-- Seleccione Tipo de Precio --","", "","class=\"my_select_box\""));
    
}else{
    $form->addHidden("tr_tabl_tipoprecio",10);
}

$form->addField("Presentaci&oacute;n: <font color=red>*</font>",textField("Presentacion","Sr_sepr_umedida",$bd_sepr_umedida,20,20));

$form->addField("Equivalencia en Unidades: <font color=red>*</font>",numField("Equivalencia en Unidades","nr_sepr_equi_unidades",$bd_sepr_equi_unidades,10,10,3,false));

if( SIS_EMPRESA_TIPO!=4 ) {//ALMACENES
    $form->addField("Precio Venta: <font color=red>*</font>",numField("Precio Venta","nr_sepr_precio",$bd_sepr_precio,15,10,2,false));        
}

$form->addField("C&oacute;d. de Barras: ",textField("Cod. de Barras","Sx_sepr_codigo_barras",$bd_sepr_codigo_barras,30,30));

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
    $(".my_select_box").select2({
    placeholder: "Seleccione un elemento de la lista",
    allowClear: true,
    width: '100%' 
    });
</script>

</body>
</html>

<?php
/* cierro la conexión a la BD */
$conn->close();