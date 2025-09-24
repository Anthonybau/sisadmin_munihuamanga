<?php
/* formulario de ingreso y modificaci�n */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("catalogosTipoExpediente_class.php");
include("../gestdoc/tamanoPagina_class.php");
include("../gestdoc/CabeceraDocumento_class.php");
include("../gestdoc/PieDocumento_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$clear = getParam("clear"); 

$myClass = new clsTipExp($id,'Tipos de Documentos');

if (strlen($id)>0) { // edición
	$myClass->setDatos();
	if($myClass->existeDatos()){
		$bd_tiex_id = $myClass->field('tiex_id');
                $bd_cod_td = $myClass->field('cod_td');
		$bd_tiex_descripcion = $myClass->field('tiex_descripcion');
		$bd_tiex_abreviado = $myClass->field('tiex_abreviado');
		$bd_tiex_secuencia = $myClass->field('tiex_secuencia');
		$bd_usua_id = $myClass->field('usua_id');
                $bd_tiex_tiporesolucion = $myClass->field('tiex_tiporesolucion');
                $bd_tiex_tipojudicial = $myClass->field('tiex_tipojudicial');
                $bd_tiex_ocultar_editor = $myClass->field('tiex_ocultar_editor');
                $bd_tiex_mesa_partes_virtual = $myClass->field('tiex_mesa_partes_virtual');
                
                $bd_tapa_id = $myClass->field('tapa_id');
                $bd_cado_id = $myClass->field('cado_id');
                $bd_pido_id = $myClass->field('pido_id');
                $bd_tiex_formato = $myClass->field('tiex_formato');
                $bd_tiex_orientacion = $myClass->field('tiex_orientacion');
        
                $bd_tiex_adjuntos_para_firma = $myClass->field('tiex_adjuntos_para_firma');
                $bd_tiex_habilitar_mas_firmas_empleado = $myClass->field('tiex_habilitar_mas_firmas_empleado');
                $bd_tiex_habilitar_mas_firmas_externo= $myClass->field('tiex_habilitar_mas_firmas_externo');
                $bd_tiex_exigir_marcar_documento_final= $myClass->field('tiex_exigir_marcar_documento_final');
                $bd_tiex_estado = $myClass->field('tiex_estado');
		$username = $myClass->field("username");
		$usernameactual = $myClass->field("usernameactual");		
	}
}else{
    if($clear==2){
        $bd_tiex_tipojudicial=1;
    }
    $bd_tiex_orientacion=1;
    $bd_tiex_formato=1;
}

?>
<html>
<head>
	<title><?php echo $myClass->getTitle() ?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT ?>">
	<script language="JavaScript" src="<?php echo PATH_INC ?>js/focus.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC ?>js/libjsgen.js"></script>
	
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
            }else{
                    return true
            }
	}

	/*
		funci�n que define el foco inicial en el formulario
	*/
	function inicializa() {
		document.frm.Sr_tiex_descripcion.focus();
	}
	</script>
	<?php 
        verif_framework(); 
        ?>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("Edici&oacute;n de ".$myClass->getTitle());


/* botones */
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2);
$button->addItem(" Regresar ","catalogosTipoExpediente_buscar.php".$param->buildPars(true),"content");

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
	$form->addField("C&oacute;digo: ",$bd_cod_td);
}

$form->addField("Descripci&oacute;n: ",textField("Descripci&oacute;n","Sr_tiex_descripcion",$bd_tiex_descripcion,80,80));
$form->addField("Breve: ",textField("Breve","Sr_tiex_abreviado",$bd_tiex_abreviado,5,5));
$form->addField("Id.Correlativo: ",textField("Id.Correlativo","Sx_tiex_secuencia",$bd_tiex_secuencia,6,6)." Si llena este campo, la numeración es autom&aacute;tica; en Blanco la numeración es manual");
//if($clear==1){
//    $form->addField("Tipo Resoluci&oacute;n ",checkboxField("Tipo Resoluci&oacute;n","hx_tiex_tiporesolucion",1,$bd_tiex_tiporesolucion==1)." El n&uacute;mero de documento se completa con las 'Siglas P/Resoluciones' definidas en la Dependencia ");
//}

$form->addField("Tipo Judicial",checkboxField("Tipo Judicial","hx_tiex_tipojudicial",1,$bd_tiex_tipojudicial==1)." Solicita datos de Expediente Judicial");
$form->addField("P/MPVirtual",checkboxField("P/MPVirtual","hx_tiex_mesa_partes_virtual",1,$bd_tiex_mesa_partes_virtual==1)." Para Uso en Mesa de Partes Virtual ");

if(inlist(SIS_GESTDOC_TIPO,'2,3')){/*CON FIRMA/MIXTO*/
    $form->addBreak("Integraci&oacute;n a FIRMA");

    $tamanoPagina=new tamanoPagina_SQLlista();
    $tamanoPagina->orderUno();
    $sqlTamanoPagina=$tamanoPagina->getSQL();
    $form->addField("Tamaño de P&aacute;gina: ",listboxField("Tamaño de Pagina",$sqlTamanoPagina,"nx_tapa_id",$bd_tapa_id,'-- Tamaño de P&aacute;gina --',"","",""));

    $CabeceraDocumento=new CabeceraDocumento_SQLlista();
    $CabeceraDocumento->orderUno();
    $sqlCabeceraDocumento=$CabeceraDocumento->getSQL();
    $form->addField("Encabezado de Documento: ",listboxField("Encabezado de Documento",$sqlCabeceraDocumento,"nx_cado_id",$bd_cado_id,'-- Encabezado de Documento --',"","",""));

    $PieDocumento=new PieDocumento_SQLlista();
    $PieDocumento->orderUno();
    $sqlPieDocumento=$PieDocumento->getSQL();
    $form->addField("Pie de Documento: ",listboxField("Pie de Documento",$sqlPieDocumento,"nx_pido_id",$bd_pido_id,'-- Pie de Documento --',"","",""));
    
    $form->addField("Ocultar editor de documento",checkboxField("Ocultar editor de documento","hx_tiex_ocultar_editor",1,$bd_tiex_ocultar_editor==1)." Ocultar editor de documento");
    $lista_orientacion=array("1,Vertical","2,Horizontal");
    $form->addField("Orientaci&oacute:n de Paginas: ",radioField("Orientacion",$lista_orientacion, "nr_tiex_orientacion",$bd_tiex_orientacion,"",'H'));

    $lista_formato=array("1,Doc.Administrativo",
                                 "2,Area Total",
                                 "3,Doc.de Gestión");
    $form->addField("Formato: ",radioField("Formato",$lista_formato, "nr_tiex_formato",$bd_tiex_formato,"onClick=\"xajax_getFormato(1,this.value,'divFormato')\"",'H'));

    $form->addField("Pasar Archivos Adjuntos P/Firma",checkboxField("Subir Adjuntos para Firma","hx_tiex_adjuntos_para_firma",1,$bd_tiex_adjuntos_para_firma==1)." Todos los archivos subidos serán marcadoss para Firma ");
    $form->addField("Habilitar '+Firmas' P/Trabajadores",checkboxField("Habilitar +Firmas para Trabajadores","hx_tiex_habilitar_mas_firmas_empleado",1,$bd_tiex_habilitar_mas_firmas_empleado==1)." Solicitar mas Firmas de Trabajadores");

    $form->addField("Habilitar '+Firmas' P/Externos",checkboxField("Habilitar +Firmas para Externos","hx_tiex_habilitar_mas_firmas_externo",1,$bd_tiex_habilitar_mas_firmas_externo==1)." Solicitar mas Firmas de Externos");    
    
    $form->addField("Exigir el Cierre del documento",checkboxField("Exigir Marcar Documento Final","hx_tiex_exigir_marcar_documento_final",1,$bd_tiex_exigir_marcar_documento_final==1)." Forzar Marcar el Documento como Final (en Doc.Externos despliega Plantillas y Editor)");    
    
}
//solo si es edicion se agrega los datos de auditoria
if(strlen($id)) {
	$form->addBreak("<b>Control</b>");
        $form->addField("Activo: ",checkboxField("Activo","hx_tiex_estado",1,$bd_tiex_estado==1));            
	$form->addField("Creado por: ",$username);
	$form->addField("Actualizado por: ",$usernameactual);
}else{
       $form->addHidden("hx_tiex_estado",1); // clave primaria
}

echo $form->writeHTML();
?>
</body>
</html>

<?php
/* cierro la conexión a la BD */
$conn->close();