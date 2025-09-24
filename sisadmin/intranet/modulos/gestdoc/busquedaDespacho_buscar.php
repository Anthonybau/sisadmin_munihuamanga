<?php
/* Modelo de página que apresenta um formulario con criterios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("busquedaDespacho_class.php");
include("registroDespacho_class.php");
include("../catalogos/catalogosTabla_class.php");
include("../catalogos/catalogosDependencias_class.php");
include("../catalogos/catalogosTipoExpediente_class.php");
include("../catalogos/catalogosArchivadores_class.php");
include("catalogosProcedimientos_class.php");
include("../admin/adminUsuario_class.php");
include("registroDespacho_edicionAdjuntosClass.php");
include("registroDespachoEnvios_class.php");

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

$myClass = new despachoBusqueda(0,'Registros');


if ($clear==1) {
	setSession("cadSearch","");
        $depe_id=getSession("sis_depeid");
        
        if (!getSession("SET_TODOS_USUARIOS")){
            $user_id=getSession("sis_userid");
        }
}

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "despachoBusqueda","buscar"),"");
$xajax->registerExternalFunction(array("verDetalle", "despacho","verDetalle"),"");
$xajax->registerExternalFunction(array("clearDiv", "despacho","clearDiv"),"");
$xajax->registerFunction("getUsuarios");
$xajax->registerFunction("seguir");

function getUsuarios($op,$depe_id,$user_id){

    $objResponse = new xajaxResponse();
    //$objResponse->addAlert($tipoDespacho);
    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");

    $usuarios=new clsUsers_SQLlista();
    $usuarios->whereNotID(1);
    //$usuarios->whereDepeID($depe_id);
    $sqlUsuarios=$usuarios->getSQL_cbox();
    $oForm->addField("Usuario (Procede o Destino): ",listboxField("Usuario (Procede o Destino)",$sqlUsuarios,"nbusc_user_id","","-- Todos los Usuarios --","","","class=\"my_select_box\"")); 
    $oForm->addField("Usuario Procede: ",listboxField("Usuario Procede",$sqlUsuarios,"nbusc_user_id_origen","","-- Todos los Usuarios --","","","class=\"my_select_box\"")); 
    $oForm->addField("Usuario Destino: ",listboxField("Usuario Destino",$sqlUsuarios,"nbusc_user_id_destino","","-- Todos los Usuarios --","","","class=\"my_select_box\"")); 

    $contenido_respuesta=$oForm->writeHTML();
    $objResponse->addAssign('divUsuarios','innerHTML', $contenido_respuesta);

    if($op==1){
        return $objResponse;
    }
    else{
        return $contenido_respuesta;
    }
}
$xajax->processRequests();
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="javascript" src="../../library/js/checkall.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>
        
        <script language="JavaScript" src="../../library/bootstrap4/jquery-3.2.1.min.js"></script>
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>
        
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>
        
        <style>
            #lectorPDF{
              width: 95% !important;
            }
        </style>        
	<script language='JavaScript'>

                function AbreVentana(sURL){
                    var w=720, h=600;
                    venrepo=window.open(sURL,'rptSalida', "status=yes,resizable=yes,toolbar=no,scrollbars=yes,top=50,left=150,width=" + w + ",height=" + h, 1 );
                    venrepo.focus();
                }

                function abreConsultaxxxxx(id) {
                    AbreVentana('../../../portal/gestdoc/consultarTramiteProceso.php?nr_numTramite=' + id+'&vista=NoConsulta');
                }

		function inicializa() {
			document.frm.Sbusc_cadena.focus();
		}
                
                function imprimir(id) {
                    AbreVentana('../gestdoc/rptDocumento.php?id=' + id);
                }

	</script>

    <?php 
        $xajax->printJavascript(PATH_INC.'ajax/'); 
	verif_framework(); 
    ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("B&uacute;squedas de ".$myClass->getTitle());



/* formulario de pesquisa */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s");

$desp_tipo=new clsTabla_SQLlista();
$desp_tipo->whereTipo('TIPO_DESPACHO');
$desp_tipo->orderUno();

$rs = new query($conn, $desp_tipo->getSQL());

$lista_nivel=array();
$bd_tabl_tipodespacho=getSession("SET_TIPO_DESPACHO");
while ($rs->getrow()) {
    $lista_nivel[].=$rs->field("tabl_id").",".$rs->field("tabl_descripcion");
}
$lista_nivel[].="0,TODOS";
$form->addField("Tipo de ".NAME_EXPEDIENTE.": ",radioField("Tipo de ".NAME_EXPEDIENTE,$lista_nivel, "nbusc_tipo_despacho",0,"","H"));

if(getSession("sis_userid")>1 && getSession("sis_level")!=3){
    $ver_todas_las_dependencias=0;
}else{
    $ver_todas_las_dependencias=1;
}
/* Instancio la Dependencia */
$dependencia=new dependencia_SQLlista();
//if(!$ver_todas_las_dependencias){
//    $dependencia->whereVarios(getSession("sis_persid"));    
//    //$dependencia->whereID($depe_id);
//}

$sqlDependencia=$dependencia->getSQL_cbox();

//FIN OBTENGO
$form->addField("Dependencia (Procede o Destino): ",listboxField("Dependencia (Procede o Destino)",$sqlDependencia,"nbusc_depe_id","","-- Todas las Dependencias --","","","class=\"my_select_box\"")); 
$form->addField("Dependencia de Procedencia: ",listboxField("Dependencia de Procedencia",$sqlDependencia,"nbusc_depe_id_procede","","-- Todas las Dependencias --","","","class=\"my_select_box\"")); 
$form->addField("Dependencia Destino: ",listboxField("Dependencia Destino",$sqlDependencia,"nbusc_depe_id_destino","","-- Todas las Dependencias --","","","class=\"my_select_box\"")); 


$form->addHtml("<tr><td colspan=2><div id='divUsuarios' >\n"); //pide datos de afectacion presupuestal
$form->addHtml(getUsuarios(2,$depe_id,"$user_id"));
$form->addHtml("</div></td></tr>\n");

$texp=new clsTipExp_SQLlista();
$texp->orderUno();
$sqltipo=$texp->getSQL_cbox();
$form->addField("Tipo de Documento:",listboxField("Tipo de Documento",$sqltipo,"nbusc_tiex_id",$bd_tiex_id,"-- Todos los Tipos de Documentos --","","","class=\"my_select_box\"")); 

if (SIN_PROCEDIMIENTOS==0) {
    $procedimiento = new procedimiento_SQLlista();
    $procedimiento->whereEstado(1);
    //$procedimiento->whereTipoDespacho($tipoDespacho);
    $sqlProcedimiento = $procedimiento->getSQL_cbox();
    $form->addField("Procedimiento: ",listboxField("Procedimiento",$sqlProcedimiento,"nbusc_proc_id",$bd_proc_id,"-- Seleccione Procedimiento--","","","class=\"my_select_box\"" ));
}

$desp_estado=new clsTabla_SQLlista();
$desp_estado->whereTipo('ESTADO_DESPACHO');
$desp_estado->orderDos();
$sqlEstado=$desp_estado->getSQL_cboxCodigo();
$form->addField("Estado de Registro: ",listboxField("Estado",$sqlEstado,"nbusc_estado","","-- Todas los Estados --"));

$sql = array(1 => "RESUMEN",
             2 => "DETALLADO");
$form->addField("Formato: ", listboxField("Formato", $sql, "tipo_formato",2));


$form->addField("Fecha Desde:",dateField2("Fecha Desde","nbusc_fdesde","",""));
$form->addField("Fecha Hasta:",dateField2("Fecha Hasta","nbusc_fhasta","",""));

$form->addField("N&uacute;m.de Documento: ",numField("Numero de Documento","nbusc_numero","",6,6,0));
//array de parametros que se ingresaran a la funcion de busqueda de ajax
$paramFunction= new manUrlv1();
$paramFunction->removeAllPar();
$paramFunction->addParComplete('colSearch','');
$paramFunction->addParComplete('colOrden','1');
$paramFunction->addParComplete('busEmpty',$busEmpty);
$paramFunction->addParComplete('nbusc_depe_id',$depe_id);
$paramFunction->addParComplete('nbusc_user_id',$user_id);
$paramFunction->addParComplete('nbusc_estado','');
$paramFunction->addParComplete('nbusc_fdesde','');
$paramFunction->addParComplete('nbusc_fhasta','');

$form->addField("Exp/N&uacute;m.".substr(NAME_EXPEDIENTE,0,2)."/Asun/Firm/Enti: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado');document.getElementById('DivResultado').innerHTML='Espere procesando...'\" value=\"Buscar\">");

$form->addHtml("<tr><td colspan=2>");
$form->addHtml("<table width=\"100%\"><tr valign=\"top\">");
$form->addHtml("<td><div id='DivResultado'>\n");
$form->addHtml("");
$form->addHtml("</div></td>");
$form->addHtml("<td><div id='DivDetalles'>");
$form->addHtml("</div></td>");
$form->addHtml("</tr></table>\n");
$form->addHtml("</td></tr>");

$lectorPDF=new lectorPDF();
$form->addHtml($lectorPDF->writeHTML());

echo  $form->writeHTML();
?>

</body>

<script>
        $(".my_select_box").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true
        });        
</script>
</html>
<?php

/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();