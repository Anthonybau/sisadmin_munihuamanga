<?php
/* Modelo de p�gina que apresenta um formulario con criterios de busqueda */
include("../../library/library.php");

/* verificación del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("./catalogosServicios_class.php");

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$relacionamento_id = getParam("id_relacion");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente

$param= new manUrlv1();
$param->removePar('clear');

$myServicio=new servicios_SQLlista();
$myServicio->whereID($relacionamento_id);
$myServicio->setDatos();

$myClass = new servicios(0,"Importar Asientos Contables ".$myServicio->field("serv_id").' '.$myServicio->field("serv_descripcion")." Desde...");


// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscarServicio", "servicios","buscarServicio"),"");
$xajax->registerFunction("eligeServicio");


function eligeServicio($id,$campoTexto_de_Retorno,$serv_unidades='',$serv_umedida=''){
    global $conn,$relacionamento_id;
    $objResponse = new xajaxResponse();
            
    $sSql="INSERT INTO catalogos.servicio_asientos_contables (
                                    serv_codigo,
                                    tabl_fase,
                                    tabl_tipo,
                                    plco_id_debe,
                                    plco_id_haber,
                                    tipl_id,
                                    afp_id,
                                    seac_actualfecha,
                                    seac_actualusua,
                                    usua_id)
                        SELECT 
                                    $relacionamento_id,
                                    tabl_fase,
                                    tabl_tipo,
                                    plco_id_debe,
                                    plco_id_haber,
                                    tipl_id,
                                    afp_id,
                                    NOW(),
                                    ".getSession("sis_userid").",
                                    ".getSession("sis_userid")."
                FROM catalogos.servicio_asientos_contables a
                        WHERE a.serv_codigo=$id
                            AND tabl_fase::TEXT||tabl_tipo::TEXT||plco_id_debe::TEXT||plco_id_haber::TEXT||tipl_id::TEXT||afp_id::TEXT
                                    NOT IN (SELECT tabl_fase::TEXT||tabl_tipo::TEXT||plco_id_debe::TEXT||plco_id_haber::TEXT||tipl_id::TEXT||afp_id::TEXT 
                                                FROM catalogos.servicio_asientos_contables WHERE serv_codigo=$relacionamento_id)

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
	<title><?php echo $myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>
        
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
        <?php 
            $xajax->printJavascript(PATH_INC.'ajax/'); 
            verif_framework(); 
        ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle($myClass->getTitle());

/* formul�rio de pesquisa */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");


$form->addField("C&oacute;digo Origen: ",textField("Codigo Origen","Sbusc_cadena",'',50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscarServicio(document.frm.Sbusc_cadena.value,0,0,1,'DivResultado')\" value=\"Buscar\">");

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");
$form->addHtml("</div></td></tr>\n");
echo  $form->writeHTML();

?>

</body>
</html>
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();