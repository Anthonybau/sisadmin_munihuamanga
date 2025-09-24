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
include("../catalogos/catalogosPrioridadAtencion_class.php");
include("../admin/adminUsuario_class.php");


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

$myClass = new despachoBusqueda(0,NAME_EXPEDIENTE_UPPER.'S POR PRIORIDAD DE ATENCION');


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
    
    $oForm->addField("Usuario: ",listboxField("Usuario",$sqlUsuarios,"nbusc_user_id",$user_id,"-- Todos los Usuarios --","","","class=\"my_select_box\"","","","class=\"my_select_box\"","","","class=\"my_select_box\"")); 

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
$xajax->processRequests();
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
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

	function imprimir(idObj,sURL) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
                        parent.content.document.frm.target = "controle";
                        parent.content.document.frm.action = sURL;
        		parent.content.document.frm.submit();
		}
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
$sUrl="rptDespachosPrioridadAtencion.php";


$periodoAtencion=new clsPrioriAtencion_SQLlista();
$periodoAtencion->orderUno();
$sql_periodoAtencion=$periodoAtencion->getSQL_cbox();
$form->addField("Prioridad de Atenci&oacute;n:",listboxField("Prioridad de Atenci&oacute;n",$sql_periodoAtencion,"nbusc_prat_id",$bd_prat_id,"","","class=\"my_select_box\"")); 

//
if(getSession("sis_userid")>1 && getSession("sis_level")!=3)
    $ver_todas_las_dependencias=0;
else
    $ver_todas_las_dependencias=1;

/* Instancio la Dependencia */
$dependencia=new dependencia_SQLlista();
if(!$ver_todas_las_dependencias){
    //$dependencia->whereID($depe_id);
    $dependencia->whereVarios(getSession("sis_persid"));
}

$sqlDependencia=$dependencia->getSQL_cbox();

//FIN OBTENGO

$form->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia,"nbusc_depe_id",$depe_id,iif($ver_todas_las_dependencias,"==",1,"-- Todas las Dependencias --",""),"onChange=\"xajax_getUsuarios(1,this.value,'$user_id');\"","","","class=\"my_select_box\"")); 

$form->addHtml("<tr><td colspan=2><div id='divUsuarios' >\n"); //pide datos de afectacion presupuestal
$form->addHtml(getUsuarios(2,$depe_id,"$user_id"));
$form->addHtml("</div></td></tr>\n");

$texp=new clsTipExp_SQLlista();
$texp->orderUno();
$sqltipo=$texp->getSQL_cbox();
$form->addField("Tipo de Documento",listboxField("Tipo de Documento",$sqltipo,"nbusc_tiex_id",$bd_tiex_id,"-- Todos los Tipos de Documentos --","","","class=\"my_select_box\"")); 

//$desp_estado=new clsTabla_SQLlista();
//$desp_estado->whereTipo('ESTADO_DESPACHO');
//$desp_estado->orderDos();
//$sqlEstado=$desp_estado->getSQL_cboxCodigo();
//$form->addField("Estado de Registro: ",listboxField("Estado",$sqlEstado,"nbusc_estado",$depe_id,"-- Todas los Estados --"));

$form->addField("N&uacute;m de ".NAME_EXPEDIENTE." Desde",numField("N&uacute;m de ".NAME_EXPEDIENTE." Desde","nrbusc_NTdesde",'',10,10,0) );
$form->addField("N&uacute;m de ".NAME_EXPEDIENTE." Hasta",numField("N&uacute;m de ".NAME_EXPEDIENTE." Hasta","nrbusc_NThasta",'',10,10,0) );

$form->addField("Procesar Hasta:",dateField2("Procesar Hasta","nrbusc_fproceso","",""));

$button = new Button;
$button->setDiv(FALSE);
$button->addItem(" Imprimir ","javascript:imprimir('Imprimir','$sUrl')","content");

$form->addField("",$button->writeHTML());

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