<?php
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("./PersonaEstudios_class.php");
include("../catalogos/catalogosTabla_class.php");
include("./Persona_class.php");
include("./CentroEstudio_class.php");
include("./Especialidades_class.php");
include("./GradosTitulos_class.php");
include("./Situacion_academica.php");
/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$id_relacion = getParam("id_relacion"); // captura la variable que viene del objeto lista

$myClass = new clsPersonaEstudios($id,'Estudios');

if ($id>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
                $bd_pees_id=$myClass->field("pees_id");
		$bd_pers_id=$myClass->field("pers_id");
                
		$bd_cees_id= $myClass->field("cees_id");
		$bd_siac_id= $myClass->field("siac_id");
		$bd_gres_id= $myClass->field("gres_id");
                $bd_pees_otro_ie=$myClass->field("pees_otro_ie");
		$bd_espe_id = $myClass->field("espe_id");		
		$bd_pees_otespecialidades= $myClass->field("pees_otespecialidades");
		$bd_pees_ntitulo= $myClass->field("pees_ntitulo");
		$bd_pees_ncolegiatura = $myClass->field("pees_ncolegiatura");
		$bd_pees_lugar= $myClass->field("pees_lugar");		
		$bd_pees_observaciones=$myClass->field("pees_observaciones");
		$bd_pees_fegresado= dtos($myClass->field("pees_fegresado"));
		$bd_pees_fgradotitulo= dtos($myClass->field("pees_fgradotitulo"));
		$bd_pees_situatitulo= $myClass->field("pees_situatitulo");
		$bd_pees_id= $myClass->field("pees_id");
		$bd_tabl_gradoinstruccion = $myClass->field("tabl_gradoinstruccion");
                
                $bd_usua_id = $myClass->field("usua_id");
                $bd_pees_adjunto1=$myClass->field("pees_adjunto1");
                $bd_pees_adjunto2=$myClass->field("pees_adjunto2");
                
                $nameUsers= $myClass->field('username');
                $fregistro=$myClass->field('pees_fecharegistro');
                $nameUsersActual=$myClass->field('usernameactual');
                $fregistroActual=$myClass->field('pees_actualfecha');
              
        }
}


/* Archivos para calendario */
require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("getCentroEstudio"); 
$xajax->registerFunction("eligeCentroEstudio");
$xajax->registerFunction("getGradoTitulo");
$xajax->registerFunction("eligeGradoTitulo");
$xajax->registerFunction("getEspecialidad");
$xajax->registerFunction("eligeEspecialidad");
$xajax->registerFunction("clearDiv");

$xajax->registerExternalFunction(array("buscarCentroEstudio", "CentroEstudio","buscarCentroEstudio"),"");
$xajax->registerExternalFunction(array("buscarGradoTitulo", "GradoTitulo","buscarGradoTitulo"),"");
$xajax->registerExternalFunction(array("buscarEspecialidad", "Especialidades","buscarEspecialidad"),"");

function getCentroEstudio($op,$valor,$NameDiv)
{
        global $conn;
        
	$objResponse = new xajaxResponse();
	$otable = new AddTableForm();
	$otable->setLabelWidth("20%");
	$otable->setDataWidth("80%");
        
        if($valor){
            $centroEstudios=new CentroEstudio_SQLlista();
            $centroEstudios->whereID($valor);
            $centroEstudios->setDatos();
            $bd_ruc_centro_estudio=$centroEstudios->field('cees_ruc');
            $bd__Dummyruc_centro_estudio=$centroEstudios->field('cees_nombre');
        }
        
        $otable->addBreak("CENTRO DE ESTUDIO");
        //SOLICITA EL PROVEEDOR Y MUESTRA A LA VEZ EL BOTON NUEVO
        $otable->addField("RUC: ",numField("RUC","ruc_centro_estudio","$bd_ruc_centro_estudio",12,12,0,false,"readonly"));
        //echo $tipo;
        $nvoCentroEstudio="<a class=\"link\" href=\"javascript:nuevoCentroEstudio()\" title=\"Ingresar Nuevo Centro de Estudio\"><b>Nuevo<b></a>";
        $btnBuscar="<input type=\"button\" onClick=\"xajax_buscarCentroEstudio(1,document.frm._Dummyruc_centro_estudio.value,'','2,3,4','divResultado',1);document.getElementById('divResultado').innerHTML = 'Espere, Buscando...'\" value=\"Buscar\">";
        $otable->addField("Centro de Estudio: ",textField("Centro de Estudio","_Dummyruc_centro_estudio","$bd__Dummyruc_centro_estudio",70,100)."&nbsp;$btnBuscar&nbsp;$nvoCentroEstudio");
        $otable->addHidden("tr_cees_id","$valor",'Centro de Estudio');
        $otable->addHtml("<tr><td colspan=2><div id='divResultado'>\n");
        $otable->addHtml("</div></td></tr>\n");
        
        $contenido_respuesta=$otable->writeHTML();
	$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);

        if($op==1){
                $objResponse->addScript("document.frm._DummySx_centro_estudio.focus()");
		return $objResponse;
	}else{
		return $contenido_respuesta;
	}
}


function eligeCentroEstudio($cees_id,$cees_nombre,$cees_ruc,$accion){
	$objResponse = new xajaxResponse();
        
        if($accion==1){
            $objResponse->addScript("document.frm.Guardar.focus()");
            $objResponse->addClear("divResultado",'innerHTML');
        }else{
            $otable = new AddTableForm();
            $otable->setLabelWidth("20%");
            $otable->setDataWidth("80%");
            
            $otable->addField("RUC:","<b>$cees_ruc</b>");
            $otable->addField("Raz&oacute;n Social:",$cees_nombre);
        }
        $objResponse->addScript("document.frm.tr_cees_id.value=$cees_id");
        $objResponse->addScript("document.frm.ruc_centro_estudio.value='$cees_ruc'");
        $objResponse->addScript("document.frm._Dummyruc_centro_estudio.value='$cees_nombre'");        
        
	return $objResponse;
}

function getGradoTitulo($op,$valor,$NameDiv)
{
        global $conn;
        
	$objResponse = new xajaxResponse();
	$otable = new AddTableForm();
	$otable->setLabelWidth("20%");
	$otable->setDataWidth("80%");
        
        if($valor){
            $gradoTitulo=new GradoTitulo_SQLlista();
            $gradoTitulo->whereID($valor);
            $gradoTitulo->setDatos();
            $bd__Dummytx_gres_id=$gradoTitulo->field('gres_descripcion');
        }
        
        $otable->addBreak("GRADO O TITULO");
        //echo $tipo;
        $nvoCentroEstudio="<a class=\"link\" href=\"javascript:nuevoGradoTitulo()\" title=\"Ingresar Nuevo Grado o Titulo\"><b>Nuevo<b></a>";
        $btnBuscar="<input type=\"button\" onClick=\"xajax_buscarGradoTitulo(1,document.frm._Dummytx_gres_id.value,'','2,3,4','divResultado2',1);document.getElementById('divResultado2').innerHTML = 'Espere, Buscando...'\" value=\"Buscar\">";
        $otable->addField("Grado o T&iacute;tulo: ",textField("Grado o Titulo","_Dummytx_gres_id","$bd__Dummytx_gres_id",70,100)."&nbsp;$btnBuscar&nbsp;$nvoCentroEstudio");
        $otable->addHidden("tx_gres_id","$valor",'Grado o Titulo');
        $otable->addHtml("<tr><td colspan=2><div id='divResultado2'>\n");
        $otable->addHtml("</div></td></tr>\n");
        
        $contenido_respuesta=$otable->writeHTML();
	$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);

        if($op==1){
                $objResponse->addScript("document.frm._Dummytx_gres_id.focus()");
		return $objResponse;
	}else{
		return $contenido_respuesta;
	}
}

function eligeGradoTitulo($gres_id,$gres_nombre,$accion){
	$objResponse = new xajaxResponse();
        
        if($accion==1){
            $objResponse->addScript("document.frm.Guardar.focus()");
            $objResponse->addClear("divResultado2",'innerHTML');
        }else{
            $otable = new AddTableForm();
            $otable->setLabelWidth("20%");
            $otable->setDataWidth("80%");
            
            $otable->addField("Grado o T&iacute;tulo:",$gres_nombre);
        }
        $objResponse->addScript("document.frm.tx_gres_id.value=$gres_id");
        $objResponse->addScript("document.frm._Dummytx_gres_id.value='$gres_nombre'");        
        
	return $objResponse;
}


function getEspecialidad($op,$valor,$NameDiv)
{
        global $conn;
        
	$objResponse = new xajaxResponse();
	$otable = new AddTableForm();
	$otable->setLabelWidth("20%");
	$otable->setDataWidth("80%");
        
        if($valor){
            $gradoTitulo=new Especialidades_SQLlista();
            $gradoTitulo->whereID($valor);
            $gradoTitulo->setDatos();
            $bd__Dummytx_espe_id=$gradoTitulo->field('espe_descripcion');
        }
        
        $otable->addBreak("ESPECIALIDAD");
        //echo $tipo;
        $nvaEspecialidad="<a class=\"link\" href=\"javascript:nuevaEspecialidad()\" title=\"Ingresar Nueva Especialidad\"><b>Nuevo<b></a>";
        $btnBuscar="<input type=\"button\" onClick=\"xajax_buscarEspecialidad(1,document.frm._Dummytx_espe_id.value,'','2,3,4','divResultado3',1);document.getElementById('divResultado3').innerHTML = 'Espere, Buscando...'\" value=\"Buscar\">";
        $otable->addField("Especialidad: ",textField("Especialidad","_Dummytx_espe_id","$bd__Dummytx_espe_id",70,100)."&nbsp;$btnBuscar&nbsp;$nvaEspecialidad");
        $otable->addHidden("tx_espe_id","$valor",'Especialidad');
        $otable->addHtml("<tr><td colspan=2><div id='divResultado3'>\n");
        $otable->addHtml("</div></td></tr>\n");
        
        $contenido_respuesta=$otable->writeHTML();
	$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);

        if($op==1){
                $objResponse->addScript("document.frm._Dummytx_espe_id.focus()");
		return $objResponse;
	}else{
		return $contenido_respuesta;
	}
}

function eligeEspecialidad($espe_id,$espe_nombre,$accion){
	$objResponse = new xajaxResponse();
        
        if($accion==1){
            $objResponse->addScript("document.frm.Guardar.focus()");
            $objResponse->addClear("divResultado3",'innerHTML');
        }else{
            $otable = new AddTableForm();
            $otable->setLabelWidth("20%");
            $otable->setDataWidth("80%");
            
            $otable->addField("Especialidad:",$espe_nombre);
        }
        $objResponse->addScript("document.frm.tx_espe_id.value=$espe_id");
        $objResponse->addScript("document.frm._Dummytx_espe_id.value='$espe_nombre'");        
        
	return $objResponse;
}

function clearDiv($NameDiv){
	$objResponse = new xajaxResponse();
	//limpio el div
	$objResponse->addClear($NameDiv,'innerHTML');
	return $objResponse;		
}

$xajax->processRequests();
?>
<html>
<head>
	<title><?php echo $myClass->getTitle() ?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="<?php echo PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>
	<script language="JavaScript" src="../../library/js/janela.js"></script>

        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>   
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

            if((document.frm.tr_siac_id.value == 1 /*EGRESADO CON TITULO*/
                    || document.frm.tr_siac_id.value == 2 /*EGRESADO CON GRADO*/)
                    && document.frm.Dx_pees_fegresado.value==''){
                    nErrTot=nErrTot+1;
                    sError='Ingrese Fecha de Egresado';
                    foco='document.frm.Dx_pees_fegresado.focus()';
            }else
                if((document.frm.tr_siac_id.value == 1 /*EGRESADO CON TITULO*/
                        || document.frm.tr_siac_id.value == 2 /*EGRESADO CON GRADO*/)
                        && document.frm.tx_gres_id.value==''){
                        nErrTot=nErrTot+1;
                        sError='Seleccione Grado o Titulo';
                        foco='document.frm._Dummytx_gres_id.focus()';
                }    
                else    
                if((document.frm.tr_siac_id.value == 1 /*EGRESADO CON TITULO*/
                        || document.frm.tr_siac_id.value == 2 /*EGRESADO CON GRADO*/)
                        && document.frm.Dx_pees_fgradotitulo.value==''){
                        nErrTot=nErrTot+1;
                        sError='Ingrese Fecha de Grado o Titulo';
                        foco='document.frm.Dx_pees_fgradotitulo.focus()';
                }
                else
                if(document.frm.tr_siac_id.value == 1 /*EGRESADO CON TITULO*/
                        && document.frm.Sx_pees_ntitulo.value==''){
                        nErrTot=nErrTot+1;
                        sError='Ingrese Numero de Titulo';
                        foco='document.frm.Sx_pees_ntitulo.focus()';
                }
                else
                if(document.frm.tr_siac_id.value == 1 /*EGRESADO CON TITULO*/
                        && document.frm.tx_espe_id.value==''){
                        nErrTot=nErrTot+1;
                        sError='Seleccione Especialidad';
                        foco='document.frm._Dummytx_espe_id.focus()';
                }
                else
                if((document.frm.tr_siac_id.value == 1 /*EGRESADO CON TITULO*/
                        || document.frm.tr_siac_id.value == 2 /*EGRESADO CON GRADO*/)
                        && document.frm.tx_pees_situatitulo.value==''){
                        nErrTot=nErrTot+1;
                        sError='Seleccione Situacion de Grado o Titulo';
                        foco='document.frm.tx_pees_situatitulo.focus()';
                }
            
            if (nErrTot>0){
                    alert(sError)
                    eval(foco)
                    return false
            }else
                    return true

	}

        function nuevoCentroEstudio(){
            abreJanelaAuxiliar('../modulos/personal/CentroEstudio_edicion.php?clear=2,nomeCampoForm=ruc_centro_estudio,fieldExtra=tr_cees_id,nbusc_cadena='+document.frm._Dummyruc_centro_estudio.value,820,600)
        }

        function modiCentroEstudio(id){
            abreJanelaAuxiliar('../modulos/personal/CentroEstudio_edicion.php?id='+id+',clear=2,nomeCampoForm=ruc_centro_estudio,fieldExtra=tr_cees_id,nbusc_cadena='+document.frm._Dummyruc_centro_estudio.value,820,600)
        }
        
        function nuevoGradoTitulo(){
            abreJanelaAuxiliar('../modulos/personal/GradosTitulos_edicion.php?clear=2,nomeCampoForm=tx_gres_id,nbusc_cadena='+document.frm._Dummytx_gres_id.value,820,600)
        }

        function modiGradoTitulo(id){
            abreJanelaAuxiliar('../modulos/personal/GradosTitulos_edicion.php?id='+id+',clear=2,nomeCampoForm=tx_gres_id,nbusc_cadena='+document.frm._Dummytx_gres_id.value,820,600)
        }
        
        function nuevaEspecialidad(){
            abreJanelaAuxiliar('../modulos/personal/Especialidades_edicion.php?clear=2,nomeCampoForm=tx_espe_id,nbusc_cadena='+document.frm._Dummytx_espe_id.value,820,600)
        }

        function modiEspecialidad(id){
            abreJanelaAuxiliar('../modulos/personal/Especialidades_edicion.php?id='+id+',clear=2,nomeCampoForm=tx_espe_id,nbusc_cadena='+document.frm._Dummytx_espe_id.value,820,600)
        }        
	/*
		funci�n que define el foco inicial en el formulario
	*/
	function inicializa() {
		document.frm._Dummyruc_centro_estudio.focus();
	}
	</script>
	<?
            $xajax->printJavascript(PATH_INC.'ajax/');
            verif_framework();
            $calendar->load_files();	
        ?>

</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("Edici&oacute;n de ".$myClass->getTitle());



/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setUpload(true);

$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$bd_pees_id); // clave primaria
$form->addHidden("nx_pers_id",$id_relacion); // chave estrangeira do relacionamento
$form->addHidden("pagina",$pg); // numero de página que llamo

$tabla=new clsTabla_SQLlista();
$tabla->whereTipo('GRADO_INSTRUCCION');
$tabla->orderUno();
$sql=$tabla->getSQL_cbox();
$form->addField("Grado Instrucci&oacute;n: ",listboxField("Grado Instruccion",$sql,"tr_tabl_gradoinstruccion",$bd_tabl_gradoinstruccion,"-- Seleccione Grado de Instrucci&oacute;n --"));

$form->addHtml("<tr><td colspan=2><div id='divCentroEstudio'>\n");
$form->addHtml(getCentroEstudio(2,"$bd_cees_id",'divCentroEstudio'));
$form->addHtml("</div></td></tr>\n");

//$form->addField("Otro Cent.Estudios: ",textField("Otros Cent.Estudios","Sx_pees_otro_ie",$bd_pees_otro_ie,80,100));

$situacionAcademica=new SituacionAcademica_SQLlista();
$situacionAcademica->orderUno();
$sql=$situacionAcademica->getSQL_cbox();
$form->addField("Situaci&oacute;n Academica: ",listboxField("Situacion Academica",$sql,"tr_siac_id",$bd_siac_id,"-- Seleccione Sit.Academica --"));

$form->addField("Fecha de Egresado: ", $calendar->make_input_field('Fecha de Egresado',array(),array('name'=> 'Dx_pees_fegresado','value'=> $bd_pees_fegresado)));

$form->addHtml("<tr><td colspan=2><div id='divGradoTitulo'>\n");
$form->addHtml(getGradoTitulo(2,"$bd_gres_id",'divGradoTitulo'));
$form->addHtml("</div></td></tr>\n");

$form->addField("Fecha de Grado o Titulo: ", $calendar->make_input_field('Fecha de Grado o Titulo',array(),array('name'=> 'Dx_pees_fgradotitulo','value'=> $bd_pees_fgradotitulo)));
$form->addField("N&ordm; de Titulo: ",numField("N&ordm; de Titulo","Sx_pees_ntitulo",$bd_pees_ntitulo,15,15,0));

$form->addHtml("<tr><td colspan=2><div id='divEspecialidad'>\n");
$form->addHtml(getEspecialidad(2,"$bd_espe_id",'divEspecialidad'));
$form->addHtml("</div></td></tr>\n");

//$form->addField("Otras Especialidades: ",textField("Otras Especialidades","Sx_pees_otespecialidades",$bd_pees_otespecialidades,100,100));

$form->addField("N&ordm; de Colegiatura: ",numField("N&ordm; de Colegiatura","Sx_pees_ncolegiatura",$bd_pees_ncolegiatura,15,15,0));
$sql=array(1=>"HABILITADO",2=>"INHABILITADO");
$form->addField("Situaci&oacute;n Grado o T&iacute;tulo: ",listboxField("Situacion Grado o Titulo",$sql,"tx_pees_situatitulo",$bd_pees_situatitulo,'-- Seleccione Situaci&oacute;n --'));
$form->addField("Lugar de Estudio: ",textField("Lugar de Estudio","Sr_pees_lugar",$bd_pees_lugar,60,60));

$form->addHidden("postPath",'escalafon/');
$form->addField("Archivo:",fileField("Archivo1","pees_adjunto1" ,$bd_pees_adjunto1,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));
$form->addField("Archivo:",fileField("Archivo2","pees_adjunto2" ,$bd_pees_adjunto2,80,"onchange=validaextension(this,'PDF')",PUBLICUPLOAD.'escalafon/'));

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
    $("select").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true,
            width: '50%'
            });
    
    </script>    
</body>
</html>

<?php
/* cierro la conexión a la BD */
$conn->close();