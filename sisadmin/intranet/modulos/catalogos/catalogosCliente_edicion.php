<?php
/* formulario de ingreso y modificaci�n */
include($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");

/* verificacion del nivel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("catalogosCliente_class.php"); 
include("../catalogos/catalogosTabla_class.php");
include("../catalogos/catalogosUbigeo_class.php");
include("../siscore/catalogosParametrosCalculo_class.php");

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$clear=getParam("clear");
$myClass = new cliente ($id,"Edici&oacute;n de ".iif(SIS_EMPRESA_TIPO,'==',4,'Beneficiarios','Clientes'));

if (strlen($id)>0) { // edici�n
	$myClass->setDatos();
	if($myClass->existeDatos()){
		$bd_clie_id= $myClass->field("clie_id");
		$bd_tabl_tipocliente= $myClass->field("tabl_tipocliente");
		$bd_clie_apellidos= $myClass->field("clie_apellidos");		
		$bd_clie_nombres= $myClass->field("clie_nombres");
		$bd_clie_razsocial= $myClass->field("clie_razsocial");
		$bd_clie_nomcomercial= $myClass->field("clie_nomcomercial");
                $bd_clie_nombre_comercial= $myClass->field("clie_nombre_comercial");
                
		$bd_clie_codigo= $myClass->field("clie_codigo");
                $bd_clie_dni= $myClass->field("clie_dni");
		$bd_clie_direccion= $myClass->field("clie_direccion");
                $bd_clie_zona= $myClass->field("clie_zona");
		$bd_clie_telefono= $myClass->field("clie_telefono");
		$bd_clie_email= $myClass->field("clie_email");
                $bd_tabl_ubigeo= $myClass->field("tabl_ubigeo");
                $bd_ubig_id= $myClass->field("ubig_id");
                $bd_clie_historia_clinica= $myClass->field("clie_historia_clinica");
		$bd_usua_id = $myClass->field("usua_id");
		$bd_clie_estado = $myClass->field("clie_estado");
                $bd_tabl_tipo_negocio= $myClass->field("tabl_tipo_negocio");
                $bd_clie_agente_retencion =$myClass->field("clie_agente_retencion");
                $bd_clie_porcent_retencion = $myClass->field("clie_porcent_retencion");
                $bd_codigo_ant = $myClass->field("codigo_ant");
                $bd_paca_id = $myClass->field("paca_id");
                $bd_tabl_calificacion= $myClass->field("tabl_calificacion");
		$nameUsers= $myClass->field("username").' / '.$myClass->field("clie_fregistro");
		$usernameactual= $myClass->field("usernameactual").' / '.$myClass->field("clie_actualfecha");
	}
}

/* Archivos para calendario */
require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("AjCondicional");
$xajax->registerFunction("pidePorcent");

function AjCondicional($NameDiv,$op,$mivalor)
{
	global $bd_clie_razsocial,$bd_clie_codigo,
		$bd_clie_apellidos,$bd_clie_nombres,
		$bd_clie_direccion,$bd_clie_zona,$bd_clie_telefono,
                $bd_clie_email,$bd_clie_dni,$bd_clie_nombre_comercial;

	$objResponse = new xajaxResponse();

	$otable = new AddTableForm();
    	$otable->setLabelWidth("20%");
	$otable->setDataWidth("80%");

	switch($mivalor){
	   case 1: // Si es persona juridica
                $otable->addField("Raz&oacute;n Social: ",textField("Raz&oacute;n Social","Sr_clie_razsocial",$bd_clie_razsocial,90,95));
                $otable->addField("R.U.C.: ",textField("RUC","Sx_clie_codigo",$bd_clie_codigo,14,11));	   			
                break;
			
	   case 2: // persona natural
                $otable->addField("Apellidos: ",textField("Apellidos","Sr_clie_apellidos",$bd_clie_apellidos,40,35));
                $otable->addField("Nombres: ",textField("Nombres","Sr_clie_nombres",$bd_clie_nombres,40,35));
                if(!$bd_clie_apellidos && !$bd_clie_nombres && $bd_clie_razsocial){
                $otable->addField("Cliente: ",$bd_clie_razsocial);	                   
                }
                $otable->addField("R.U.C.: ",textField("RUC","Sx_clie_codigo",$bd_clie_codigo,14,11));	   			
                $otable->addField("D.N.I.: ",textField("DNI","Sx_clie_dni",$bd_clie_dni,10,8));
                break;
                
	   case 3: // auxiliar
                $otable->addField("Apellidos: ",textField("Apellidos","Sr_clie_apellidos",$bd_clie_apellidos,40,35));
                $otable->addField("Nombres: ",textField("Nombres","Sr_clie_nombres",$bd_clie_nombres,40,35));
                $otable->addField("D.N.I.: ",textField("DNI","Sr_clie_dni",$bd_clie_dni,10,8));
                break;
                
	}

	if($mivalor==1 or $mivalor==2 or $mivalor==3){ /* Si es PJ o P.N. */
		$otable->addField("Direcci&oacute;n: ",textField("Direcci&oacute;n","Sx_clie_direccion",$bd_clie_direccion,90,120));
                $otable->addField("Zona: ",textField("Zona","Sx_clie_zona",$bd_clie_zona,30,30));
		$otable->addField("Tel&eacute;fono(s): ",textField("Tel&eacute;fono","Sx_clie_telefono",$bd_clie_telefono,40,35));
		$otable->addField("Email: ",textField("Email","cx_clie_email",$bd_clie_email,55,50));
		//$otable->addField("Actividad: ",textAreaField("Actividad","Sr_clie_actividad",$bd_clie_actividad,5,100,300));
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

function pidePorcent($op,$valor,$divName){
        global $bd_clie_porcent_retencion;
                
        $objResponse = new xajaxResponse();

        $oForm = new AddTableForm();
        $oForm->setLabelWidth("20%");
        $oForm->setDataWidth("80%");
        
        if($valor==="true" || $valor==1) {
            $oForm->addField("% Retenci&oacute;n: ",numField("% Retencion","nx_clie_porcent_retencion","$bd_clie_porcent_retencion",4,2,0,false,""));
        }else{
            $oForm->addHidden("nx_clie_porcent_retencion",NULL);
        }
        

        $contenido_respuesta=$oForm->writeHTML();
        
        if($op==1){
            $objResponse->addAssign($divName,'innerHTML', $contenido_respuesta);            
            return $objResponse;
        }else{
            return $contenido_respuesta;
        }
}
$xajax->processRequests();
// fin para Ajax

?>

<html>
<head>
	<title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT ?>">
	<script language="JavaScript" src="<?php echo PATH_INC ?>js/focus.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC ?>js/libjsgen.js"></script>
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.2.min.js"></script>
        <link rel="stylesheet" href="../../library/jquery-chosen/chosen.css">
        <script src="../../library/jquery-chosen/chosen.jquery.js" type="text/javascript"></script>	
	<script language='JavaScript'>
	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			document.frm.target = "content";
			document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
			document.frm.submit();
			
		}
	}
	/*
		se invoca desde la funcion obligacampos (libjsgen.js)
		en esta funcion se puede personalizar la validacion del formulario
		y se ejecuta al momento de gurdar los datos
	*/
	function mivalidacion(){
            var sError="Mensajes del sistema: "+"\n\n";
            var nErrTot=0;
		/* valido Longitud del campo Código */ 
		if(frm.tr_tabl_tipocliente.value==1){ /* PERSONA JURIDICA */
                        if (frm.Sx_clie_codigo.value=='X') {
			   frm.Sx_clie_codigo.focus();
			   sError+="RUC es obligatorio"+"\n" 
			   nErrTot+=1;
			}
			else
                            if (frm.Sx_clie_codigo.value.length>1 && frm.Sx_clie_codigo.value.length<11) {
                                frm.Sx_clie_codigo.focus();
                                sError+="RUC no valido"+"\n" 
                                nErrTot+=1;
                             }
            	}else if(frm.tr_tabl_tipocliente.value==2){ /* PERSONA NATURAL */
			if (frm.Sx_clie_codigo.value && frm.Sx_clie_codigo.value.length<11) {
                            frm.Sx_clie_codigo.focus();
                            sError+="RUC no valido"+"\n" 
                            nErrTot+=1;
			}else if(frm.Sx_clie_dni.value && frm.Sx_clie_dni.value.length<8) {
                                frm.Sx_clie_dni.focus();
                                sError+="DNI no valido"+"\n" 
                                nErrTot+=1;
                             }
		}
                
		if (nErrTot>0){
			alert(sError)
			return false
		}else
			return true

	}

	/* funci�n que define el foco inicial en el formulario */
	function inicializa() {
		document.frm.Sr_clie_razsocial.focus();
	}
	</script>

	</script>
    <?php 
        $xajax->printJavascript(PATH_INC.'ajax/'); 
        $calendar->load_files();
	verif_framework(); 
     ?>
</head>

<body class="contentBODY">
<?php
pageTitle($myClass->getTitle());

/* botones */
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2);

if($clear==1){
    $button->addItem(" Regresar ","catalogosCliente_buscar.php".$param->buildPars(true));
}else{
    $button->addItem(" Salir sin Guardar ","javascript:if(confirm('Seguro de Salir sin Guardar?')){parent.parent.close()}","content");	
}


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
$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$id); // clave primaria

if(SIS_EMPRESA_RUC=='20480027494'){//PERUANOESPANOL
        $sqltipo="SELECT tabl_codigo,tabl_descripcion FROM tabla WHERE tabl_tipo='TIPO_PROVEEDOR' ORDER BY 1";
        if(!$id){//si es nuevo
            $bd_tabl_tipocliente=3; //auxiliar
        }
}else{
    $sqltipo="SELECT tabl_codigo,tabl_descripcion FROM tabla WHERE tabl_tipo='TIPO_PROVEEDOR' AND tabl_codigo!=3 ORDER BY 1";
}

$form->addField("Tipo",listboxField("Tipo ",$sqltipo,"tr_tabl_tipocliente",$bd_tabl_tipocliente,"-- Seleccione Tipo --","onChange=\"xajax_AjCondicional('DivAddTable',1,this.value);\""));

$form->addHtml("<tr><td colspan=2><div id='DivAddTable'>\n");
$form->addHtml(AjCondicional('DivAddTable',2,$bd_tabl_tipocliente));
$form->addHtml("</div></td></tr>\n");


if( SIS_NOMBR_COMERCIAL_CLIENTE==1 ){
    $form->addField("Nombre Comercial: ",textField("Nombre Comercial","Sx_clie_nombre_comercial",$bd_clie_nombre_comercial,90,120));  
}

if( SIS_TIPO_UBIGEO_CLIENTE==1 ){//INVERSIONES DEL NORTE, GAMONAL
    $tblUbigeoPedidos=new clsTabla_SQLlista();
    $tblUbigeoPedidos->whereTipo('UBIGEO_PEDIDOS');
    $tblUbigeoPedidos->orderUno();
    $sql=$tblUbigeoPedidos->getSQL_cbox();
    $form->addField("Ubigeo: ",listboxField("Ubigeo",$sql,"nr_tabl_ubigeo","$bd_tabl_ubigeo","-- Seleccione UBIGEO --","", "","class=\"my_select_box\""));

}else if( SIS_TIPO_UBIGEO_CLIENTE==2 ){
    $ubigeo=new ubigeo_SQLlista();
    $sqlUbigeo=$ubigeo->getSQL_cbox();
    $form->addField("UBIGEO: ",listboxField("UBIGEO",$sqlUbigeo, "sr_ubig_id",$bd_ubig_id,"-- Seleccione UBIGEO --","", "","class=\"my_select_box\""));
}


if( SIS_TIPO_NEGOCIO_CLIENTE==1 ){//INVERSIONES DEL NORTE        
    $tblTipoNegocio=new clsTabla_SQLlista();
    $tblTipoNegocio->whereTipo('TIPO_NEGOCIO');
    $tblTipoNegocio->orderUno();
    $sql=$tblTipoNegocio->getSQL_cbox();
    $form->addField("Tipo de Negocio: ",listboxField("Tipo de Negocio",$sql,"nr_tabl_tipo_negocio","$bd_tabl_tipo_negocio","-- Seleccione Tipo de Negocio --","", "","class=\"my_select_box\""));
}


if(SIS_GESTMED==1 && $_SERVER['SERVER_NAME']=='clinicausat.mytienda.page'){//sistema DE GESTION MEDICA
    $form->addField("Historia Cl&iacute;nica: ",numField("Historia","nx_clie_historia_clinica","$bd_clie_historia_clinica",12,12,0,false));
}

if(SIS_EMPRESA_TIPO!=4){
//    $form->addField(checkboxField("Es 'Agente de Retencion'","hx_clie_agente_retencion",1,$bd_clie_agente_retencion==1,"onClick=\"xajax_pidePorcent(1,this.checked,'divPorcent')\""),"Establecer 'es Agente de Retenci&oacute;n' ");
//    $form->addHtml("<tr><td colspan=2><div id='divPorcent' >\n"); //pide serie
//    $form->addHtml(pidePorcent(2,$bd_clie_agente_retencion,'divPorcent'));
//    $form->addHtml("</div></td></tr>\n");
}

if(SIS_EMPRESA_RUC=='20480027494'){//PERUANOESPANOL
    $form->addField("C&oacute;d.Sistema: ",textField("Cod.Sistema","Sx_codigo_ant",$bd_codigo_ant,10,8));
}

$parametros_calculo=new clsParametroCalculo_SQLlista();
$parametros_calculo->whereTipoOperacion(1); //ventas
$parametros_calculo->whereActivo();
$sql=$parametros_calculo->getSQL_cbox();

$form->addField("Tipo de C&aacute;lculo: <br>(P/Ventas al Cr&eacute;dito)",listboxField("Tipo de Calculo",$sql,"tx_paca_id","$bd_paca_id","-- Seleccione Opci&oacute;n --","", "","class=\"my_select_box\""));    

$tblCalificacion=new clsTabla_SQLlista();
$tblCalificacion->whereTipo('CALIFICACION_CLIENTE');
$tblCalificacion->orderUno();
$sql=$tblCalificacion->getSQL_cbox();
$form->addField("Calificaci&oacute;n: ",listboxField("Calificacion",$sql,"nx_tabl_calificacion","$bd_tabl_calificacion","-- Seleccione Calificaci&oacute;n --","", "","class=\"my_select_box\""));

//solo si es edicion se agrega los datos de auditoria
if($id) {
    $form->addField("Activo: ",checkboxField("Activo","hx_clie_estado",1,$bd_clie_estado==1));
    $form->addBreak("<b>Control</b>");
    $form->addField("Creado por: ",$nameUsers);
    if(trim($usernameactual)!='/'){
        $form->addField("Actualizado por: ",$usernameactual);
    }

}else{
    $form->addHidden("hx_clie_estado",1); // clave primaria
}

echo $form->writeHTML();
?>
    <SCRIPT>
    $('.my_select_box').chosen({
        disable_search_threshold: 5, //SE DESHABILITA SI SOLO HAY 5 REGISTROS
        allow_single_deselect: true,
        search_contains: true,
        width:'80%',
        no_results_text: 'Oops, No Encontrado!'
    });
    </SCRIPT>
</body>
</html>

<?php
/* cierro la conexion a la BD */
$conn->close();
