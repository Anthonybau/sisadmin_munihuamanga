<?php
/* Modelo de página que apresenta um formulario con crit�rios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("archivoDespacho_class.php");
include("registroDespacho_class.php");
include("../catalogos/catalogosDependencias_class.php");
include("../catalogos/catalogosArchivadores_class.php");
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

$myClass = new despachoArchivo(0,NAME_EXPEDIENTE."s Archivados");

if ($clear==1) {
	setSession("cadSearch","");
        $depe_id=getSession("sis_depeid");
        
        //if (!getSession("SET_TODOS_USUARIOS"))
        if(!$user_id)
            $user_id=getSession("sis_userid");
}

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "despachoArchivo","buscar"),"");
$xajax->registerExternalFunction(array("verDetalle", "despacho","verDetalle"),"");
$xajax->registerExternalFunction(array("clearDiv", "despacho","clearDiv"),"");
$xajax->registerFunction("getUsuarios");
$xajax->registerFunction("getArchivadores");
$xajax->registerFunction("limpiarCarrito");
$xajax->registerFunction("derivar");
$xajax->registerFunction("setSeleccionadosActivar");
$xajax->registerFunction("activar");

function getUsuarios($op,$depe_id,$user_id,$arrayParam){

    $objResponse = new xajaxResponse();
    //$objResponse->addAlert($tipoDespacho);
    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");

    $usuarios=new clsUsersDatosLaborales_SQLlista();
    $usuarios->whereDepeID($depe_id);
    $usuarios->whereNOTNull();
    $sqlUsuarios=$usuarios->getSQL_cbox();
    $oForm->addField("Usuario: ",listboxField("Usuario",$sqlUsuarios,"nbusc_user_id",$user_id,"-- Todos los Usuarios --","onChange=\"xajax_buscar(1,xajax.getFormValues('frm'),'$arrayParam',1,'DivResultado')\" "));

    $contenido_respuesta=$oForm->writeHTML();
    $objResponse->addAssign('divUsuarios','innerHTML', $contenido_respuesta);

    if($op==1){
        return $objResponse;
    }
    else
        return $contenido_respuesta	;
}

function getArchivadores($op,$depe_id,$user_id){

    $objResponse = new xajaxResponse();

    //$objResponse->addAlert($tipoDespacho);
    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");
    /*ojo el usuario que se recibe es el de la session actual*/
    $archivador=new archivador_SQLlista();
    $archivador->whereMisArchivadores($depe_id,$user_id);
    $sqlArchivador=$archivador->getSQL_cbox();

    $oForm->addField("Archivador: ",listboxField("Archivador",$sqlArchivador,"nbusc_arch_id","","-- Todos los Archivadores --",""));

    $contenido_respuesta=$oForm->writeHTML();
    $objResponse->addAssign('divArchivadores','innerHTML', $contenido_respuesta);

    if($op==1){
        return $objResponse;
    }
    else
        return $contenido_respuesta	;
}

function derivar($formdata)
{
    global $conn;
    $objResponse = new xajaxResponse();
    $objResponse->setCharEncoding('utf-8');

    if($formdata['reg_seleccionados']==''){
        $objResponse->addAlert('Proceso cancelado, No existen registros seleccionados para procesar... ');
        return $objResponse;
    }

    if($_SESSION["ocarrito"]->getConteo()==0){
        $objResponse->addAlert('Proceso cancelado, Sin registros para procesar... ');
        return $objResponse;
    }
    
    /*genero un array con los registros seleccionados*/
    $regSeleccionados=$formdata['reg_seleccionados'];
    $nvoArraySeleccionados=explode(",",$regSeleccionados);

    // objeto para instanciar la clase sql
    $setTable='despachos_derivaciones';
    $setKey='dede_id';
    $typeKey='Number';

    /********* INICIO PROCESO DE GRABACION EN EL HIJO *********/
    $array=$_SESSION["ocarrito"]->getArray();
    foreach($array as $arrItem){

        $depe_iddestino=$arrItem['depe_id'];
        $usua_iddestino=$arrItem['usua_id'];
        $dede_concopia=$arrItem['cc']=='Cc'?1:0;
        $dede_proveido=$arrItem['proveido'];

        $depe_idorigen=getSession("sis_depeid");
        $usua_idorigen=getSession("sis_userid");
        $usua_idcrea=getSession("sis_userid"); /* Id del usuario que graba el registro */


	

        for ($i = 0; $i < count($nvoArraySeleccionados); $i++) {
            $sql = new UpdateSQL();

            $sql->setTable($setTable);
            $sql->setKey($setKey,0,$typeKey);
            $sql->setAction("INSERT"); /* Operación */

            /* Campos */
            //obtengo el ID del padre y el Id del Hijo, esto se construye en buscar de procesoDespachoClass
            $arrayPadreHijo=explode('_',$nvoArraySeleccionados[$i]);
            $desp_id=$arrayPadreHijo[0];
            $dede_idrelacionado=$arrayPadreHijo[1];
            //$objResponse->addAlert($arrayPadreHijo[1]);
            
            $sql->addField('desp_id',$desp_id, "Number");
            $sql->addField('dede_idrelacionado', $dede_idrelacionado, "Number");

            $sql->addField('depe_idorigen', $depe_idorigen, "Number");
            $sql->addField('usua_idorigen', $usua_idorigen, "Number");

            $sql->addField('depe_iddestino', $depe_iddestino, "Number");
            $sql->addField('usua_iddestino', $usua_iddestino, "Number");
            $sql->addField('dede_concopia', $dede_concopia, "Number");
            $sql->addField('dede_proveido', $dede_proveido, "String");
            $sql->addField('dede_donde_se_creo', 1, "Number");
            $sql->addField('usua_idcrea', $usua_idcrea, "Number");


            $sql=$sql->getSQL();

            $sql= strtoupper($sql);
            

            $conn->execute($sql);
            $error=$conn->error();
            if($error){
                $objResponse->addAlert($error);
                return $objResponse;
            }
       }
    }
    /********* FIN PROCESO DE GRABACION EN EL HIJO *********/
    $objResponse->addScript("limpiarDatos()");
    $objResponse->addScript("tb_remove()");

    $paramFunction= new manUrlv1();
    $paramFunction->removeAllPar();
    $paramFunction->addParComplete('colSearch','');
    $paramFunction->addParComplete('colOrden','1');
    $paramFunction->addParComplete('busEmpty',1);

    $objResponse->addScript("xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')");
    return $objResponse;
}

function setSeleccionadosActivar($form){

    $objResponse = new xajaxResponse();

    $array=$form['sel'];
    if(is_array($array)){
        $regSeleccionado=implode(",",$array);
    }

    $objResponse->addScript("document.frmActivar.reg_seleccionados.value='".$regSeleccionado."'");

    return $objResponse;

}

function activar($formdata)
{
    global $conn;
    $objResponse = new xajaxResponse();
    $arch_id=$formdata['nx_archivador'];
    $dede_motivoactiva=$formdata['Sx_motivo'];

    if($formdata['reg_seleccionados']==''){
        $objResponse->addAlert('Proceso cancelado, No existen registros seleccionados para procesar... ');
        return $objResponse;
    }


    if($dede_motivoactiva==''){
        $objResponse->addAlert('Proceso cancelado, Ingrese Motivo... ');
        $objResponse->addScript("document.frmActivar.Sx_motivo.focus()");
        return $objResponse;
    }

    /*genero un array con los registros seleccionados*/
    $regSeleccionados=$formdata['reg_seleccionados'];
    $nvoArraySeleccionados=explode(",",$regSeleccionados);
    $reg_seleccionados='';
    for ($i = 0; $i < count($nvoArraySeleccionados); $i++) {
            //obtengo el ID del padre y el Id del Hijo, esto se construye en buscar de procesoDespachoClass
            $arrayPadreHijo=explode('_',$nvoArraySeleccionados[$i]);
            //$reg_seleccionados.=iif($reg_seleccionados,'===','','',',').$arrayPadreHijo[1];

            //OJO solo puede activarlo el usuario que lo ha archivado
            $sql ="UPDATE despachos_derivaciones SET dede_estado=7,";
            $sql.="  usua_idactiva=".getSession("sis_userid").",dede_fechaactiva='".date('d/m/Y').' '.date('H:i:s')."',";
            $sql.="  dede_motivoactiva='".addslashes($dede_motivoactiva)."' ";
            $sql.="WHERE dede_id=".$arrayPadreHijo[1]." AND  usua_idarchiva=".getSession("sis_userid")." RETURNING dede_id";

            $sql= strtoupper($sql);
            //$objResponse->addAlert($sql);

            $dede_id=$conn->execute($sql);
            $error=$conn->error();
            if($error){
                $objResponse->addAlert($error);
            }elseif(!$dede_id){
                $objResponse->addAlert("Imposible Activar Registro ".$nvoArraySeleccionados[$i].", probablemente usted no lo ha archivado!");
            }

    }


    $objResponse->addScript("limpiarDatosActiva()");
    $objResponse->addScript("tb_remove()");

    $paramFunction= new manUrlv1();
    $paramFunction->removeAllPar();
    $paramFunction->addParComplete('colSearch','');
    $paramFunction->addParComplete('colOrden','1');
    $paramFunction->addParComplete('busEmpty',1);

    $objResponse->addScript("xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')");
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
	<script language="javascript" src="<?php echo PATH_INC?>js/checkall.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>

        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>           
        <script type="text/javascript" src="../../library/jquery/jquery-ui.js"></script>
        <link rel="stylesheet" href="../../library/jquery-chosen/chosen.css">
        <script src="../../library/jquery-chosen/chosen.jquery.js" type="text/javascript"></script>
        
        <link rel="stylesheet" href="<?php echo PATH_INC?>thickbox/thickbox.css" type="text/css" media="screen" />
        <script type="text/javascript" src="<?php echo PATH_INC?>jquery/jquerypack.js"></script>
        <script type="text/javascript" src="<?php echo PATH_INC?>thickbox/thickbox2.js"></script>

    
	<script language='JavaScript'>

                function limpiarDatosActiva(){
                    document.frmActivar.Sx_motivo.value='';
                }

                function AbreVentana(sURL){
                    var w=720, h=600;
                    venrepo=window.open(sURL,'rptSalida', "status=yes,resizable=yes,toolbar=no,scrollbars=yes,top=50,left=150,width=" + w + ",height=" + h, 1 );
                    venrepo.focus();
                }

                function abreConsulta(id) {
                    AbreVentana('../../../portal/gestdoc/consultarTramiteProceso.php?nr_numTramite=' + id+'&vista=NoConsulta');
                }

		function inicializa() {
			document.frm.Sbusc_cadena.focus();
		}

                function imprimir(id) {
                    AbreVentana('../gestdoc/rptDocumento.php?id=' + id);
                }
	</script>

    <?php $xajax->printJavascript(PATH_INC.'ajax/'); ?>
	<?php verif_framework(); ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("B&uacute;squedas de ".$myClass->getTitle());

/* botones */
$button = new Button;

$button->addItem("Activar Registro (devuelve a 'en Proceso')","#TB_inline?x=1&height=400&width=700&inlineId=divActivar",
        "",2,0,'botao thickbox','thickbox',"onClick=\"javascript:xajax_setSeleccionadosActivar(xajax.getFormValues('frm'))\"");

echo $button->writeHTML();


/* formulario de pesquisa */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s");

//array de parametros que se ingresaran a la funcion de busqueda de ajax
$paramFunction= new manUrlv1();
$paramFunction->removeAllPar();
$paramFunction->addParComplete('colSearch','');
$paramFunction->addParComplete('colOrden','1');
$paramFunction->addParComplete('busEmpty',$busEmpty);
$paramFunction->addParComplete('nbusc_depe_id',$depe_id);
$paramFunction->addParComplete('nbusc_user_id',$user_id);
$paramFunction->addParComplete('nbusc_arch_id',0);

/* Instancio la Dependencia */
$dependencia=new dependencia_SQLlista();
if(getSession("sis_userid")>1){
    $dependencia->whereVarios(getSession("sis_persid"));    
    //$dependencia->whereID($depe_id);
    $todos="";
}else{
    $todos="--Seleccione Dependencia--";
}

$sqlDependencia=$dependencia->getSQL_cbox();
//FIN OBTENGO
$form->addField("Dependencia: ",listboxField("Dependencia.",$sqlDependencia,"nbusc_depe_id",$depe_id,"$todos","onChange=\"xajax_getUsuarios(1,this.value,'$user_id','".encodeArray($paramFunction->getUrl())."');xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado');xajax_getArchivadores(1,this.value,'".getSession("sis_userid")."')\" ","","","class=\"my_select_box\""));

$form->addHtml("<tr><td colspan=2><div id='divUsuarios' >\n"); //pide datos de afectacion presupuestal
$form->addHtml(getUsuarios(2,$depe_id,$user_id,$arrayParam));
$form->addHtml("</div></td></tr>\n");

$form->addHtml("<tr><td colspan=2><div id='divArchivadores' >\n"); //pide datos de afectacion presupuestal
//ojo se pasa el usuario de la session actual
$userid=getSession("sis_userid");
$form->addHtml(getArchivadores(2,$depe_id,$userid));
$form->addHtml("</div></td></tr>\n");

$form->addField("Exp/N&uacute;m.".NAME_EXPEDIENTE."/Asunto: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">");

$form->addHtml("<tr><td colspan=2>");
$form->addHtml("<table width=\"100%\"><tr valign=\"top\">");
$form->addHtml("<td><div id='DivResultado'>\n");
$form->addHtml($myClass->buscar(2,'',encodeArray($paramFunction->getUrl()),1,'DivResultado'));
$form->addHtml("</div></td>");
$form->addHtml("<td><div id='DivDetalles'>");
$form->addHtml("</div></td>");
$form->addHtml("</tr></table>\n");
$form->addHtml("</td></tr>");
echo  $form->writeHTML();
?>

<div style="visibility: hidden; position: static" id="divActivar">
<?php
$button = new Button;
$button->addItem("Activar","javascript:ocultarObj('Activar',3);xajax_activar(xajax.getFormValues('frmActivar'))","content",2);
echo $button->writeHTML();
echo "<BR>";
/* Formulario */
$form = new Form("frmActivar", "", "POST", "controle", "100%",false);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHtml("<tr><td colspan=2><div id='divSeleccionados' >\n"); //pide datos de afectacion presupuestal
$form->addHtml("<table class=\"FormTABLE\"><tr><td class=\"LabelOrangeTD\" width=\"22%\"><font class=\"LabelFONT\">Reg. Seleccionados:</font></td>");
$form->addHtml("<td class=\"DataTD BackTD\" width=\"78%\"><font class=\"DataFONT\"><input type=\"text\" name=\"reg_seleccionados\" size=\"80\" value=\"\" readonly></font></td></tr></table>");
$form->addHtml("</div></td></tr>\n");

$form->addField("Motivo: ",textField("Motivo","Sx_motivo",'',80,150));

 echo $form->writeHTML();
?>
</div>
</body>
        <script>
            $('.my_select_box').chosen({
                disable_search_threshold: 5, //SE DESHABILITA SI SOLO HAY 5 REGISTROS
                allow_single_deselect: true,
                search_contains: true,
                no_results_text: 'Oops, No Encontrado!'
                });
        </script>
</html>
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();