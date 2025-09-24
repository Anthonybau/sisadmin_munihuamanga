<?php
/* Modelo de página que apresenta um formulario con crit�rios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("busquedaDespacho_class.php");
include("registroDespacho_class.php");
include("../catalogos/catalogosDependencias_class.php");
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

$myClass = new despachoBusqueda(0,'DESCARGA DE ARCHIVO DIGITAL');

$depe_id=getSession("sis_depeid");

require_once("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->setCharEncoding('utf-8');
$xajax->registerFunction("subDependencia");
$xajax->registerFunction("getUsuarios");

function subDependencia($op,$depe_id_padre,$NameDiv)
{
    global $user_id,$depe_id;
    
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

    $otable->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia,"nr_busc_depe_id","$depe_id","-- Todas las Dependencias --","onChange=\"xajax_getUsuarios(1,this.value,'$user_id')\"","","class=\"my_select_box\" style=\"width:60%\""));

    
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

function getUsuarios($op,$depe_id,$user_id){

        $objResponse = new xajaxResponse();
        //$objResponse->addAlert($tipoDespacho);
        $oForm = new AddTableForm();
        $oForm->setLabelWidth("20%");
        $oForm->setDataWidth("80%");
        
        $usuarios=new clsUsers_SQLlista();
        $usuarios->whereDepeID($depe_id);
        $sqlUsuarios=$usuarios->getSQL_cbox2();
        $oForm->addField("Usuario: ",listboxField("Usuario",$sqlUsuarios,"nbusc_user_id",'',"-- Todos los Usuarios --","","","class=\"my_select_box\"")); 


        $contenido_respuesta=$oForm->writeHTML();
	$objResponse->addAssign('divUsuarios','innerHTML', $contenido_respuesta);
        if($op==1){
                $objResponse->addScript("$('.my_select_box').select2({
                                            placeholder: 'Seleccione un elemento de la lista',
                                            allowClear: true,
                                            width:'90%'
                                        });");
            return $objResponse;
        }
	else
            return $contenido_respuesta;
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
            }else{
                return true
            }

	}

	function proceder(sURL) {
            if (ObligaCampos(frm)){            
                //alert(sURL);
                ocultarObj('Descargar',10);
                //$('#Descargar').hide();
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
pageTitle($myClass->getTitle());

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",false);

$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("rodou","s");

$form->addHidden("_titulo",$myClass->getTitle()); // Campo oculto con el T�tulo del reporte
$sUrl="descargarArchivoDigital.php";


/* Instancio la Dependencia */
$bd_depe_id=getSession("sis_depe_superior");
$sqlDependencia=new dependenciaSuperior_SQLBox3(getSession("sis_depe_superior"));
$sqlDependencia=$sqlDependencia->getSQL();        
$form->addField("Dependencia Superior: ",listboxField("Dependencia Superior",$sqlDependencia,"tr_depe_id","$bd_depe_id","-- Seleccione Dependencia --","onChange=\"xajax_subDependencia(1,this.value,'divSubDependencia')\"","","class=\"my_select_box\""));        
//FIN OBTENGO

$form->addHtml("<tr><td colspan=2><div id='divSubDependencia'>\n");
$form->addHtml(subDependencia(2,$bd_depe_id,'divSubDependencia'));
$form->addHtml("</div></td></tr>\n");

$form->addHtml("<tr><td colspan=2><div id='divUsuarios' >\n"); //pide datos de afectacion presupuestal
$form->addHtml(getUsuarios(2,$depe_id,"$user_id"));
$form->addHtml("</div></td></tr>\n");

$fecha_ini=date('Y-m').'-01';
$hoy=date('Y-m-d');
$form->addField("Periodo Desde:",dateField2("Periodo Desde","nr_busc_fdesde","$fecha_ini",""));
$form->addField("Hasta:",dateField2("Recibidos Hasta","nr_busc_fhasta","$hoy",""));


$button = new Button;
$button->setDiv(FALSE);
$button->addItem("Descargar","javascript:proceder('$sUrl')","content",2,0,'','','','Descargar');
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