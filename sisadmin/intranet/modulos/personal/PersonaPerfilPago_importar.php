<?
/* Modelo de p�gina que apresenta um formulario con criterios de busqueda */
include("../../library/library.php");

/* verificación del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("PersonaPerfilPago_class.php");
include("../personal/personalDatosLaborales_class.php");
include("../personal/Persona_class.php");
include("../catalogos/catalogosTabla_class.php");

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$relacionamento_id = getParam("id_relacion");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente

$param= new manUrlv1();
$param->removePar('clear');

$myPersona=new clsPersona_SQLlista();
$myPersona->whereID($relacionamento_id);
$myPersona->setDatos();

$myClass = new clsPerfilPago(0,"Importar Perfil de Pago Para ".$myPersona->field("empleado").'-'.$myPersona->field("pers_dni")." Desde...");


// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscarEmpleadoLabora", "clsDatosLaborales","buscarEmpleadoLabora"),"");
$xajax->registerFunction("agregar");

function agregar($id,$campoTexto_de_Retorno,$Nameobj){
    global $conn,$relacionamento_id;
    $objResponse = new xajaxResponse();
            
    $sSql="INSERT INTO personal.persona_perfil_pago (
                                    pers_id,
                                    serv_codigo,
                                    tipl_id,
                                    clas_id,
                                    pppa_importe,
                                    pppa_porcentaje,
                                    pdla_id,
                                    pppa_leyenda,
                                    pppa_ncuota_ini,
                                    pppa_ncuota_fin,
                                    pppr_id,
                                    pppa_minutos,
                                    pppa_periodicidad,
                                    pppa_caducidad,
                                    pppa_estado,
                                    pppa_actualfecha,
                                    pppa_actualusua,
                                    usua_id)
                SELECT 
                                    $relacionamento_id,
                                    serv_codigo,
                                    tipl_id,
                                    clas_id,
                                    pppa_importe,
                                    pppa_porcentaje,
                                    pdla_id,
                                    pppa_leyenda,
                                    pppa_ncuota_ini,
                                    pppa_ncuota_fin,
                                    pppr_id,
                                    pppa_minutos,
                                    pppa_periodicidad,
                                    pppa_caducidad,
                                    pppa_estado,
                                    NOW(),
                                    ".getSession("sis_userid").",
                                    ".getSession("sis_userid")."
                FROM personal.persona_perfil_pago a
                        WHERE a.pers_id=(SELECT pers_id FROM personal.persona_datos_laborales WHERE pdla_id=$id)
                            AND serv_codigo NOT IN (SELECT serv_codigo FROM personal.persona_perfil_pago WHERE pers_id=$relacionamento_id)
                            AND pppa_estado=1
            ";

    // Ejecuto el string
    $conn->execute($sSql);
    $error=$conn->error();
    if($error){
        $objResponse->addAlert($error);		 			 
    }else{
        $objResponse->addScript("parent.parent.parent.content.cerrar();
                         parent.parent.parent.content.location.reload();");

    }
    return $objResponse;
}        


$xajax->processRequests();

// fin para Ajax
?>
<html>
<head>
	<title><?=$myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="JavaScript" src="<?=PATH_INC?>js/libjsgen.js"></script>
        
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>           
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>
        <style>
            .div_Content{
                display: inline-block;
                white-space: nowrap;
            }
        </style>
        
	<script language="JavaScript">
        
	function inicializa() {
		document.frm.Sbusc_cadena.focus();
	}


	</script>
        <?php $xajax->printJavascript(PATH_INC.'ajax/'); ?>
	<? 
            verif_framework(); 
        ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?
pageTitle($myClass->getTitle());

/* formul�rio de pesquisa */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$tabla=new clsTabla_SQLlista();
$tabla->whereTipo('CONDICION_LABORAL');
$tabla->orderUno();
$sqlSituLabo=$tabla->getSQL_cbox();
$nbusc_sitlaboral=$myPersona->field("tabl_idsitlaboral");

$form->addField("Condici&oacute;n laboral: ",listboxField("Condici&oacute;n laboral",$sqlSituLabo, "nbusc_sitlaboral","$nbusc_sitlaboral","-- Toda Condici&oacute;n Laboral --"));
$form->addField("DNI/Apellidos/Nombres: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscarEmpleadoLabora(1,document.frm.Sbusc_cadena.value,'agregar','DivResultado',document.frm.nbusc_sitlaboral.value)\" value=\"Buscar\">");

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");
$form->addHtml("</div></td></tr>\n");
echo  $form->writeHTML();

?>

</body>
</html>
<?
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();