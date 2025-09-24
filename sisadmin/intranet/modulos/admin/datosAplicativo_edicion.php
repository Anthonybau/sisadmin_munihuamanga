<?php
/* formulario de ingreso y modificacion */
include("../../library/library.php");

/* verificaci�n del nivel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("datosAplicativo_class.php"); 

$clear=getParam('clear');

/* establecer conexion con la BD */
$conn = new db();
$conn->open();

require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);


$myClass = new datosAplicativo(1,'ACTIVAR APLICACIONES');
$myClass->setDatos();
if($myClass->existeDatos()){
    $bd_apli_id = $myClass->field('apli_id');
    $bd_apli_nombre = $myClass->field('apli_nombre');
    $bd_apli_acronimo = $myClass->field('apli_acronimo');
    $bd_apli_version = $myClass->field('apli_version');
    $bd_apli_piereporte = $myClass->field('apli_piereporte');

    $bd_apli_gestdoc = $myClass->field('apli_gestdoc');
    $bd_apli_gestleg = $myClass->field('apli_gestleg');
    $bd_apli_gestleg_modo = $myClass->field('apli_gestleg_modo');
    $bd_apli_sislogal = $myClass->field('apli_sislogal');
    $bd_apli_siaf = $myClass->field('apli_siaf');
    $bd_apli_max_meses_serfin = $myClass->field('apli_max_meses_serfin');
    $bd_apli_max_cita_anula = $myClass->field('apli_max_cita_anula');
    $bd_apli_firma1_orden = $myClass->field('apli_firma1_orden');
    $bd_apli_firma2_orden = $myClass->field('apli_firma2_orden');
    $bd_apli_firma3_orden = $myClass->field('apli_firma3_orden');
    $bd_apli_mancomunado_orden = $myClass->field('apli_mancomunado_orden');
    $bd_apli_control_igv = $myClass->field('apli_control_igv');
    $bd_apli_facum_patrim = dtos($myClass->field('apli_facum_patrim'));
    $bd_apli_gestcne = $myClass->field('apli_gestcne');
    $bd_apli_efact = $myClass->field('apli_efact');
    $bd_apli_efact_modo = $myClass->field('apli_efact_modo');
    $bd_apli_efact_email_from = $myClass->field('apli_efact_email_from');
    $bd_apli_sunat_sol_usuario = $myClass->field('apli_sunat_sol_usuario');
    $bd_apli_sunat_sol_password = $myClass->field('apli_sunat_sol_password');
    $bd_apli_sunat_resolucion = $myClass->field('apli_sunat_resolucion');
    $bd_apli_pin_certificado = $myClass->field('apli_pin_certificado');
    $bd_apli_siscore = $myClass->field('apli_siscore');
    $bd_apli_siscore_pedidos = $myClass->field('apli_siscore_pedidos');
    $bd_apli_efact_url_consulta = $myClass->field('apli_efact_url_consulta');
    $bd_apli_siscore_despliega_conceptos = $myClass->field('apli_siscore_despliega_conceptos');
    $bd_apli_efact_tamano_papel = $myClass->field('apli_efact_tamano_papel');
    $bd_apli_siscore_elige_conceptos_min = $myClass->field('apli_siscore_elige_conceptos_min');
    $bd_apli_laboratorio = $myClass->field('apli_laboratorio');
    $bd_apli_pedidos = $myClass->field('apli_pedidos');
    $bd_apli_max_diasenviosunat = $myClass->field('apli_max_diasenviosunat');
            
    $bd_apli_imprime_credenciales_laboratorio = $myClass->field('apli_imprime_credenciales_laboratorio');
    $bd_apli_tolerancia_publica_resultados_laboratorio = $myClass->field('apli_tolerancia_publica_resultados_laboratorio');
    $bd_apli_actualfecha = $myClass->field('apli_actualfecha');
}
    

/*recibo los parametro de la URL*/
$param= new manUrlv1();

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();


$xajax->processRequests();
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="<?php echo PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>
	
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
            if(document.frm.__password.value==""){
                sError='Ingrese Contraseña de Grabación';
                nErrTot=1;
                foco='document.frm.__password.focus()';
            }    
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
		document.frm.Sr_empr_razsocial.focus();
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
pageTitle($myClass->getTitle());

$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",3);
echo $button->writeHTML();

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("f_id",$bd_apli_id); // clave primaria
        
$form->addField("Nombre de Aplicativo:",textField("Nombre de Aplicativo","sr_apli_nombre",$bd_apli_nombre,80,80));    
$form->addField("Acronimo:",textField("Acronimo","sr_apli_acronimo",$bd_apli_acronimo,20,20));
$form->addField("Versi&oacute;n:",textField("Version","sr_apli_version",$bd_apli_version ,10,10));
$form->addField("Pie-Reportes:",textField("Pie-Reportes","sr_apli_piereporte",$bd_apli_piereporte,80,80));    
$form->addField(checkboxField("Activar Modulo de Gestion Documental","hx_apli_gestdoc",1,$bd_apli_gestdoc==1),"Activar M&oacute;dulo de Gesti&oacute;n Documental");
$form->addField(checkboxField("Activar Modulo de Gestion Legal","hx_apli_gestleg",1,$bd_apli_gestleg==1),"Activar M&oacute;dulo de Gesti&oacute;n Legal");
        
$sql = array(0 => "SIN MODO",
             1 => "MODO ESTATAL",
             2 => "MODO PROCURADURIA");
$form->addField("Modo Gesti&oacute;n Legal:", listboxField("Modo Gestion Legal", $sql, "nx_apli_gestleg_modo","$bd_apli_gestleg_modo"));

$form->addField(checkboxField("Activar Modulo de Gestion Logistica","hx_apli_sislogal",1,$bd_apli_sislogal==1),"Activar M&oacute;dulo de Gesti&oacute;n Logistica");
$form->addField("Cargo de Primer Funcionario P/Firma de Orden:",textField("Cargo de Primer Funcionario P/Firma de Orden","Sx_apli_firma1_orden",$bd_apli_firma1_orden,80,80));
$form->addField("Cargo de Segundo Funcionario P/Firma de Orden:",textField("Cargo de Segundo Funcionario P/Firma de Orden","Sx_apli_firma2_orden",$bd_apli_firma2_orden,80,80));
$form->addField("Cargo de Tercer Funcionario P/Firma de Orden:",textField("Cargo de Tercer Funcionario P/Firma de Orden","Sx_apli_firma3_orden",$bd_apli_firma3_orden,80,80));
$form->addField("Cargo Mancomunado P/Firma de Orden:",textField("Cargo Mancomunado P/Firma de Orden","Sx_apli_mancomunado_orden",$bd_apli_mancomunado_orden,80,80));
$form->addField(checkboxField("Aplicar Control de IGV en Almacen","hx_apli_control_igv",1,$bd_apli_control_igv==1),"Aplicar Control de IGV en Almac&eacute;n");

$form->addField(checkboxField("Activar Modulo de Gestion de Cuadro de Necesidades","hx_apli_gestcne",1,$bd_apli_gestcne==1),"Activar M&oacute;dulo de Gesti&oacute;n de Cuadro de Necesidades");

$form->addField(checkboxField("Solicitar Registro SIAF","hx_apli_siaf",1,$bd_apli_siaf==1),"Solicitar Registro SIAF");
$form->addField("Cantidad de Meses P/Atenci&oacute;n SERFIN:",numField("Cantidad de Meses P/Atencion SERFIN","nr_apli_max_meses_serfin",$bd_apli_max_meses_serfin,6,6,0));
$form->addField("Cantidad de Citas Anuladas en el d&iacute;a:",numField("Cantidad de Citas Anuladas en el Dia ","nr_apli_max_cita_anula",$bd_apli_max_cita_anula,6,6,0));

$form->addField("Fecha de Registro de Inventario Patrimonial: ", $calendar->make_input_field('Fecha de Registro de Inventario Patrimonial',array(),array('name'=> 'Dx_apli_facum_patrim','value'=> $bd_apli_facum_patrim)));
     
$form->addField(checkboxField("Activar Modulo de Recaudaciones:","hx_apli_siscore",1,$bd_apli_siscore==1),"Activar M&oacute;dulo de Recaudaciones");
$form->addField("Modo de Pedidos en Recaudaciones:",numField("Modo de Pedidos en Recaudaciones","nr_apli_siscore_pedidos",$bd_apli_siscore_pedidos,6,6,0).'<b>'."&nbsp;1:Dos ventas horizontales, 2:con Menú Barra".'</b>');
//$form->addField(checkboxField("Desplegar Conceptos al Iniciar una Venta","hx_apli_siscore_despliega_conceptos",1,$bd_apli_siscore_despliega_conceptos==1),"Desplegar Conceptos al Iniciar una Venta");
//$form->addField(checkboxField("Ocultar Detalle de Concepto al Realizar una Venta","hx_apli_siscore_elige_conceptos_min",1,$bd_apli_siscore_elige_conceptos_min==1),"Ocultar Detalle de Concepto al Realizar una Venta");

$form->addBreak("<b>FACTURACION ELECTRONICA</b>");        
$form->addField(checkboxField("Activar Modulo de Facturacion Electronica","hx_apli_efact",1,$bd_apli_efact==1),"Activar M&oacute;dulo de Facturaci&oacute;n Electr&oacute;nica"); 

$sql = array(1 => "MODO PRODUCCION",
             3 => "MODO PRUEBAS");
$form->addField("Modo de Facturador:", listboxField("Modo de Facturador", $sql, "nx_apli_efact_modo","$bd_apli_efact_modo"));

$form->addField("Email Referrencial P/envio de Comprobantes Electr&oacute;nicos: ",textField("Email Referrencial P/envio de Comprobantes Electronicos","cx_apli_efact_email_from",$bd_apli_efact_email_from,55,50));     
//$form->addField("Usuario SOL-SUNAT:",textField("Usuario SOL-SUNAT","Sx_apli_sunat_sol_usuario",$bd_apli_sunat_sol_usuario,25,25));     
//$form->addField("Contrase&ntilde;a SOL-SUNAT:",textField("Contrasena SOL-SUNAT","sx_apli_sunat_sol_password",$bd_apli_sunat_sol_password,25,25));          
//$form->addField("Resoluc&oacute;n de Autorizaci&oacute;n SUNAT:",textField("Resolucion de Autorizacion","Sx_apli_sunat_resolucion",$bd_apli_sunat_resolucion,50,50));               
//$form->addField("PIN Certificado SUNAT:",textField("PIN Certificado SUNAT","sx_apli_pin_certificado",$bd_apli_pin_certificado,32,35));
$form->addField("URL P/Consultas de Comprobantes SUNAT:",textField("URL P/Consultas de Comprobantes SUNAT","sx_apli_efact_url_consulta",$bd_apli_efact_url_consulta,80,80));    
    
//$sql = array(1 => "Tamaño A4",
//             2 => "Tamaño 80mm (rollo)");
//$form->addField("Tama&ntilde;o de Papel P/Emisi&oacute;n de Comprobantes SUNAT:", listboxField("Tamano de Papel P/Emision de Comprobantes SUNAT", $sql, "nx_apli_efact_tamano_papel","$bd_apli_efact_tamano_papel"));

$form->addField(checkboxField("Activar Modulo de Laboratorio","hx_apli_laboratorio",1,$bd_apli_laboratorio==1),"Activar M&oacute;dulo de Laboratorio");

//TNER EN CUENTA QUE AL ACIVAR, HAY EMPRESAS CON VALOR 2 EN ESTE DATO
//MODIFICAR PARA PONER UNA LISTA
//$form->addField(checkboxField("Activar Modulo de Pedidos","hx_apli_pedidos",1,$bd_apli_pedidos==1),"Activar M&oacute;dulo de Pedidos");

$form->addField(checkboxField("Activar Imprimir Credenciales de Laboratorio","hx_apli_imprime_credenciales_laboratorio",1,$bd_apli_imprime_credenciales_laboratorio==1),"Activar Imprimir Credenciales de Laboratorio");
$form->addField("Tolerancia en Minutos para Publicar Resultados de Laboratorio:",numField("Tolerancia en Minutos para Publicar Resultados de Laboratorio","nr_apli_tolerancia_publica_resultados_laboratorio",$bd_apli_tolerancia_publica_resultados_laboratorio,6,6,0));
$form->addField("M&aacute;x.de D&iacute;as para Envio a SUNAT:",numField("Maximo de Dias para envio a SUNAT","nr_apli_max_diasenviosunat",$bd_apli_max_diasenviosunat,6,6,0));

$form->addField("Contrase&ntilde;a de Grabaci&oacute;n: ",passwordField("Contraseña de Grabación","__password","",8,8));                
$form->addBreak("<b>CONTROL</b>");
$form->addField("Actualizado: ",substr($bd_apli_actualfecha,0,19));

echo $form->writeHTML();

//$button = new Button;
//$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",3);
//echo $button->writeHTML();
?>
</body>
</html>

<?php
/* cierro la conexion a la BD */
$conn->close();