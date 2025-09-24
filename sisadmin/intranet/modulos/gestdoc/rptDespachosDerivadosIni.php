<?php
/* Modelo de página que apresenta um formulario con crit�rios de busqueda */
include($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("busquedaDespacho_class.php");
include("registroDespacho_class.php");
include("../catalogos/catalogosTabla_class.php");
include("../catalogos/catalogosDependencias_class.php");
include("../catalogos/catalogosTipoExpediente_class.php");
include("../catalogos/catalogosArchivadores_class.php");
include("../admin/adminUsuario_class.php");

/* Archivos para calendario */
require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$busEmpty = 1; //getParam("busEmpty"); // para definir si se muestran los datos en el primer ingreso
$depe_id=getParam("nbusc_depe_id");
$user_id=getParam("nbusc_user_id");

$bd_depe_id=getSession("sis_depeid");
$bd_user_id=getSession("sis_userid");

$param= new manUrlv1();
$param->removePar('clear');

$myClass = new despachoBusqueda(0,NAME_EXPEDIENTE_UPPER.'S DERIVADOS');


$depe_id=getSession("sis_depeid");

if (!getSession("SET_TODOS_USUARIOS"))
    $user_id=getSession("sis_userid");


// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "despachoBusqueda","buscar"),"");
$xajax->registerExternalFunction(array("verDetalle", "despacho","verDetalle"),"");
$xajax->registerExternalFunction(array("clearDiv", "despacho","clearDiv"),"");
$xajax->registerFunction("getUsuarios");
$xajax->registerFunction("getUsuarios2");

function getUsuarios($op,$depe_id,$user_id){

    $objResponse = new xajaxResponse();
    //$objResponse->addAlert($tipoDespacho);
    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");

//    $usuarios=new clsUsers_SQLlista();
//    $usuarios->whereDepeID($depe_id);
//    $sqlUsuarios=$usuarios->getSQL_cbox2();
    
    $usuarios=new clsUsersDatosLaborales_SQLlista();
    $usuarios->whereDepeID($depe_id);
    $usuarios->whereActivo();
    $sqlUsuarios=$usuarios->getSQL_cbox();    
    
    $oForm->addField("Usuario Origen: ",listboxField("Usuario Recibe",$sqlUsuarios,"nbusc_user_id",$user_id,"-- Todos los Usuarios --","","","class=\"my_select_box\"")); 

    $contenido_respuesta=$oForm->writeHTML();
    $objResponse->addAssign('divUsuarios','innerHTML', $contenido_respuesta);

    if($op==1){
        $objResponse->addScript("$('.my_select_box').select2({
                                            placeholder: 'Seleccione un elemento de la lista',
                                            allowClear: true
                                        });");
        return $objResponse;
    }
    else
        return $contenido_respuesta	;
}

function getUsuarios2($op,$depe_id,$user_id){

    $objResponse = new xajaxResponse();
    //$objResponse->addAlert($tipoDespacho);
    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");

//    $usuarios=new clsUsers_SQLlista();
//    $usuarios->whereDepeID($depe_id);
//    $sqlUsuarios=$usuarios->getSQL_cbox2();
    
    $usuarios=new clsUsersDatosLaborales_SQLlista();
    $usuarios->whereDepeID($depe_id);
    $usuarios->whereActivo();
    $sqlUsuarios=$usuarios->getSQL_cbox();    
    
    $oForm->addField("Usuario Destino: ",listboxField("Usuario Destino",$sqlUsuarios,"nbusc_user_id_destino",$user_id,"-- Todos los Usuarios --","","","class=\"my_select_box\"")); 

    $contenido_respuesta=$oForm->writeHTML();
    $objResponse->addAssign('divUsuarios2','innerHTML', $contenido_respuesta);

    if($op==1){
        $objResponse->addScript("$('.my_select_box').select2({
                                            placeholder: 'Seleccione un elemento de la lista',
                                            allowClear: true
                                        });");
        return $objResponse;
    }
    else
        return $contenido_respuesta	;
}
$xajax->processRequests();
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT ?>">
        
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>
        
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>

	<script language='JavaScript'>

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

	function imprimir(sURL,op) {
                //alert(sURL);
		parent.content.document.frm.target = "controle";
		parent.content.document.frm.action = sURL+'?destino='+op;
		parent.content.document.frm.submit();
	}


	</script>
        

    <?php 
    $xajax->printJavascript(PATH_INC.'ajax/');
    verif_framework(); 
    ?>

</head>
<body class="contentBODY">
<?php
pageTitle("Parametros de Reporte/".$myClass->getTitle());

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",false);

$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("rodou","s");

$form->addHidden("_titulo",$myClass->getTitle()); // Campo oculto con el T�tulo del reporte
$sUrl="rptDespachosDerivados.php";

//
if(getSession("sis_userid")>1 && getSession("sis_level")!=3)
    $ver_todas_las_dependencias=0;
else
    $ver_todas_las_dependencias=1;

/* Instancio la Dependencia */
$dependencia=new dependencia_SQLlista();
if(!$ver_todas_las_dependencias){
//    $dependencia->whereID($depe_id);
    $dependencia->whereVarios(getSession("sis_persid"));    
}

$sqlDependencia=$dependencia->getSQL_cbox();
//FIN OBTENGO

$form->addField("Dependencia Origen: ",listboxField("Dependencia Origen",$sqlDependencia,"nbusc_depe_id",$depe_id,"-- Todas las Dependencias --","onChange=\"xajax_getUsuarios(1,this.value,'$user_id');\"","","class=\"my_select_box\"")); 

$form->addHtml("<tr><td colspan=2><div id='divUsuarios' >\n"); //pide datos de afectacion presupuestal
$form->addHtml(getUsuarios(2,$depe_id,"$user_id"));
$form->addHtml("</div></td></tr>\n");

$texp=new clsTipExp_SQLlista();
$texp->orderUno();
$sqltipo=$texp->getSQL_cbox();
$form->addField("Tipo de Documento",listboxField("Tipo de Documento",$sqltipo,"nbusc_tiex_id",$bd_tiex_id,"-- Todos los Tipos de Documentos --","","","class=\"my_select_box\"")); 

$dependencia=new dependencia_SQLlista();
$dependencia->orderUno();
$sqlDependencia=$dependencia->getSQL_cbox();
$form->addField("Dependencia Destino: ",listboxField("Dependencia Destino",$sqlDependencia,"nbusc_depe_destinoid",'',"-- Todas las Dependencias --","onChange=\"xajax_getUsuarios2(1,this.value,'$user_id');\"","","class=\"my_select_box\"")); 
$form->addHtml("<tr><td colspan=2><div id='divUsuarios2' >\n"); //pide datos de afectacion presupuestal
$form->addHtml(getUsuarios2(2,"",""));
$form->addHtml("</div></td></tr>\n");


$form->addField("Derivados Desde:",dateField2("Derivados Desde","nrbusc_fdesde","",""));
$form->addField("Hasta:",dateField2("Derivados Hasta","nrbusc_fhasta","",""));

$sql = array(1 => "TODOS",
             2 => "RECIBIDOS",
             3 => "PENDIENTES DE RECEPCION");
$form->addField("Filtro: ", listboxField("Filtro", $sql, "tipo_filtro",1));

$form->addField("","<a href=\"#\" onClick=\"javascript:if(ObligaCampos(frm)){imprimir('$sUrl',1)}\" ><img src='../../img/pdf.png' border='0' title='Listar en PDF'></a>&nbsp;&nbsp;
                    <a href=\"#\" onClick=\"javascript:if(ObligaCampos(frm)){imprimir('$sUrl',2)}\" ><img src='../../img/xls.png' border='0' title='Exportar a XLS'></a>");


echo  $form->writeHTML();
?>
</body>
    <script>
            $(".my_select_box").select2({
                placeholder: "Seleccione un elemento de la lista",
                allowClear: true,
                width:'90%'
            });        
    </script>
</html>
<?php
/* cierro la conexión a la BD */
$conn->close();