<?php
/* Modelo de página que apresenta um formulario con crit�rios de busqueda */
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
include("../admin/adminUsuario_class.php");

$depe_id=getSession("sis_depeid");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

$myClass = new despachoBusqueda(0,'TOP DE '.NAME_EXPEDIENTE_UPPER.'S EN PROCESO');

require_once("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->setCharEncoding('utf-8');
$xajax->registerFunction("subDependencia");
$xajax->registerFunction("getUsuarios");

function subDependencia($op,$depe_id_padre,$NameDiv)
{
    global $depe_id;
    
    $objResponse = new xajaxResponse();

    $otable = new AddTableForm();
    $otable->setLabelWidth("20%");
    $otable->setDataWidth("80%");
    //
    if(getSession("sis_userid")>1 && getSession("sis_level")!=3){
        $ver_todas_las_dependencias=0;
    }else{
        $ver_todas_las_dependencias=1;
    }
    /* Instancio la Dependencia */
    if(!$ver_todas_las_dependencias){
        $sqlDependencia=new dependencia_SQLBox($depe_id);        
    }else{
        $sqlDependencia=new dependencia_SQLBox($depe_id_padre);                
        $depe_id='';
    }
    
    $sqlDependencia=$sqlDependencia->getSQL();

    $otable->addField("Sub Dependencia: ",listboxField("Sub Dependencia",$sqlDependencia,"nbusc_depe_id","$depe_id","-- Todas las Dependencias --","","","class=\"my_select_box\" style=\"width:60%\""));

    
    $contenido_respuesta=$otable->writeHTML();
    
    if($op==1){
        $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);
        $objResponse->addScript("$('.my_select_box').select2({
                                        placeholder: 'Seleccione un elemento de la lista',
                                        allowClear: true,
                                        width:'90%'
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
        
        <style type="text/css">
        </style>
	
        <?php 
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
$sUrl="rptTopDespachosenProceso.php";

/* Instancio la Dependencia */
$bd_depe_id=getSession("sis_depe_superior");
$sqlDependencia=new dependenciaSuperior_SQLBox3(getSession("sis_depe_superior"));
$sqlDependencia=$sqlDependencia->getSQL();        
$form->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia,"tr_depe_id","$bd_depe_id","-- Seleccione Dependencia --","onChange=\"xajax_subDependencia(1,this.value,'divSubDependencia')\"","","class=\"my_select_box\""));        
//FIN OBTENGO

$form->addHtml("<tr><td colspan=2><div id='divSubDependencia'>\n");
$form->addHtml(subDependencia(2,$bd_depe_id,'divSubDependencia'));
$form->addHtml("</div></td></tr>\n");


$form->addField("Ingresados a 'En Proceso' Desde:",dateField2("En Proceso Desde","nrbusc_fdesde","",""));
$form->addField("Hasta:",dateField2("En Proceso Hasta","nrbusc_fhasta","",""));

$form->addField("Mayor a N d&iacute;as: ",numField("Mayor a N dias","Sx_mayor_dias",0,6,6,0));

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