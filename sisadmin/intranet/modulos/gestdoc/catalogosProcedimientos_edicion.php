<?php
/* formulario de ingreso y modificacion */
include("../../library/library.php");

/* verificacion del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("catalogosProcedimientos_class.php");
include("PlantillaDespacho_class.php");
include("../catalogos/catalogosTabla_class.php");
include("../catalogos/catalogosTipoExpediente_class.php");
include("../catalogos/catalogosDependencias_class.php");
/* establecer conexion con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista

$myClass = new procedimiento($id,'Procedimiento');

if (strlen($id)>0) { // edición
    $myClass->setDatos();
    if($myClass->existeDatos()){
        $bd_proc_id= $myClass->field('proc_id');
        $bd_tabl_tipodespacho=$myClass->field('tabl_tipodespacho'); 
        $bd_proc_nombre=$myClass->field('proc_nombre');
        $bd_plde_id=$myClass->field('plde_id');
        $bd_tiex_id=$myClass->field('tiex_id');
        $bd_proc_modo_virtual=$myClass->field('proc_modo_virtual');
        $bd_proc_validar=$myClass->field('proc_validar');
        $bd_proc_plazo_dias=$myClass->field('proc_plazo_dias');
        $bd_proc_detalles=$myClass->field('proc_detalles');
        $bd_depe_id=$myClass->field('depe_id');
        $bd_depe_id_destinatario=$myClass->field('depe_id_destinatario');
        $bd_usua_id=$myClass->field('usua_id');
        $bd_proc_estado=$myClass->field('proc_estado');
        $username=$myClass->field("username");
        $bd_proc_fregistro=$myClass->field("proc_fregistro");
        $usernameactual=$myClass->field("usernameactual");
        $bd_proc_fregistroactual=$myClass->field("proc_actualfecha");        
    }
}else{
    $bd_depe_id = getSession("sis_depe_superior");
}

// Para Ajax
require_once("../../library/ajax/xajax.inc.php");

$xajax = new xajax();

//$xajax->debugOn();
$xajax->registerFunction("getTipoDespacho");
$xajax->registerFunction("pideDestinatario"); 

function getTipoDespacho($op,$valor,$NameDiv)
{
        global $bd_plde_id,$bd_tiex_id,$bd_proc_modo_virtual;
	$objResponse = new xajaxResponse();
	if($valor){
            $otable = new AddTableForm();
            $otable->setLabelWidth("20%");
            $otable->setDataWidth("80%");

            if(inlist($valor,'140,141')){//INSTITUCIONAL, //PERSONAL
//                $plantilla=new clsPlantillaDespacho_SQLlista();
//                $plantilla->whereNODepeID();
//                $plantilla->whereTipodespacho($valor);
//                $plantilla->whereActivo();
//                $sqlTipoPlantilla=$plantilla->getSQL_cbox();
//                $otable->addField("Plantilla: ",listboxField("Plantilla",$sqlTipoPlantilla,"nx_plde_id",$bd_plde_id,"-- Seleccione Plantilla--","","","class=\"my_select_box\"" ));                
            }else{
//                $texp=new clsTipExp_SQLlista();
//                $texp->orderUno();
//                $sqltipo=$texp->getSQL_cbox2();
//                $otable->addField("Tipo de Documento Inicial:",listboxField("Tipo de Documento Inicial",$sqltipo,"nr_tiex_id",$bd_tiex_id,"-- Todos --","","","class=\"my_select_box\""));
                $otable->addField("Habilitado P/Mesa de Partes Virtual:",checkboxField("Habilitado P/Mesa de Partes Virtual","hx_proc_modo_virtual",1,$bd_proc_modo_virtual==1,""));
            }

            $contenido_respuesta=$otable->writeHTML();
        }else{
            $contenido_respuesta='';
        }
        // Se devuelve el objeto, que este dara todas las instruccione JS para realizar la tarea
	if($op==1){
                $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);            
                $objResponse->script("$('.my_select_box').select2({
                                        placeholder: 'Seleccione un elemento de la lista',
                                        allowClear: true
                                    });");                
		return $objResponse;
	}else{
		return $contenido_respuesta;
	}		

}

function pideDestinatario($op,$depe_id,$NameDiv)
{
    global $bd_depe_id_destinatario;
    $objResponse = new xajaxResponse();

    $otable = new AddTableForm();
    $otable->setLabelWidth("20%");
    $otable->setDataWidth("80%");

    $sqlDependencia=new dependencia_SQLlista();
    $sqlDependencia->whereDepeTodos($depe_id);
    $sqlDependencia->whereNOMPVirtual();
    $sqlDependencia->whereHabilitado();
    $sqlDependencia=$sqlDependencia->getSQL_cbox();
    $otable->addField("DESTINATARIO: ",listboxField("Destinatario","$sqlDependencia","nr_depe_id_destinatario","$bd_depe_id_destinatario","-- Seleccione Destinatario --","","","class=\"my_select_box\"  "));
        
    $contenido_respuesta=$otable->writeHTML();
    
    if($op==1){
        $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);
        $objResponse->addScript("$('.my_select_box').select2({
                                        placeholder: 'Seleccione un elemento de la lista',
                                        allowClear: true,
                                        width: '80%'
                                    });");

        return $objResponse;
    }else{
        return $contenido_respuesta;
    }
}

$xajax->processRequests();
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="../../library/js/focus.js"></script>	
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>	
        
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>   
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>
        
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                
        <script language="JavaScript" src="../../library/js/textcounter.js"></script>	
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
                        en esta funci�n se puede personalizar la validaci�n del formulario
                        y se ejecuta al momento de gurdar los datos
                */
                function mivalidacion(frm) {
                        return true
                }
                
            /*
                funcion que define el foco inicial en el formulario
            */
            function inicializa() {
                    document.frm.er_proc_nombre.focus();
            }

	</script>
        <?php
            verif_framework(); 
            $xajax->printJavascript('../../library/ajax/');
	 ?>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("Edici&oacute;n de ".$myClass->getTitle());

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("f_id",$id); // clave primaria

/* botones */
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2);
$button->addItem(" Regresar ","catalogosProcedimientos_buscar.php".$param->buildPars(true),"content");
echo $button->writeHTML();

$abas = new Abas();
$abas->addItem("PROCEDIMIENTO",true);
    
if ($id){ // si es edicion y contiene factores de ev.tecnica
//    $abas->addItem(" RUTA DEL PROCEDIMIENTO ",false,"catalogosProcedimientos_rutas.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));
    $abas->addItem(" REQUISITOS ",false,"catalogosProcedimientos_requisitos.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));
}
    
echo $abas->writeHTML();


if($id){
    $form->addField("C&oacute;digo: ",str_pad($bd_proc_id,3,'0',STR_PAD_LEFT));
}

/* Instancio la Dependencia */
$sqlDependencia=new dependenciaSuperior_SQLBox3(getSession("sis_depe_superior"));
$sqlDependencia=$sqlDependencia->getSQL();        

$form->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia,"tr_depe_id",$bd_depe_id,"-- Seleccione Dependencia --","onChange=\"xajax_pideDestinatario(1,this.value,'divDestinatario')\"","","class=\"my_select_box\"  "));
/* Fin Instancio */

$form->addField("Nombre: ",textAreaField("Nombre","er_proc_nombre",$bd_proc_nombre,5,100,0,"",0));

$form->addField("Plazo en Dias: ",numField("Plazo en Dias","nr_proc_plazo_dias",$bd_proc_plazo_dias,6,6,0));    
$form->addField("",checkboxField("Habilitado P/Mesa de Partes Virtual","hx_proc_modo_virtual",1,$bd_proc_modo_virtual==1,"")."<B>Habilitar P/Mesa de Partes Virtual</B>");
$form->addField("",checkboxField("Validar Requisitos","hx_proc_validar",1,$bd_proc_validar==1,"")."<B>Validar Requisitos</B>");

$form->addHtml("<tr><td colspan=2><div id='divDestinatario'>\n");
$form->addHtml(pideDestinatario(2,$bd_depe_id,'divDestinatario'));
$form->addHtml("</div></td></tr>\n");        

if (strlen($id)>0) { // edición
    $form->addField("Activo: ",checkboxField("Activo","hx_proc_estado",1,$bd_proc_estado==1));
    $form->addField("Creado por: ",$username.' '.$bd_proc_fregistro.' / '." Actualizado por: ".$usernameactual.'/'.$bd_proc_fregistroactual);
}else{
    $form->addHidden("hx_proc_estado",1); // clave primaria
}

echo $form->writeHTML();


?>
    
    
    <script>
        $(".my_select_box").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true,
            width: '80%'
        });        
    </script>
</body>
</html>

<?php
/* cierro la conexion a la BD */
$conn->close();