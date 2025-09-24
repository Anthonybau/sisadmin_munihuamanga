<?php
/* formulario de ingreso y modificaci�n */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
include("catalogosDependencias_class.php");
include("catalogosTipoExpediente_class.php");
include("../admin/adminUsuario_class.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente

$TipDocum=getParam('TipDocum');
if ($clear==1) {
	setSession("cadSearch","");
        $depe_id=getSession("sis_depeid");

        if (!getSession("SET_TODOS_USUARIOS")){
            $user_id=getSession("sis_userid");
        }
}

// Inicio Para Ajax
require_once("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->setCharEncoding('utf-8');
$xajax->registerFunction("subDependencia");
$xajax->registerFunction("getUsuarios");
$xajax->registerFunction("muestraSecuencia");
$xajax->registerFunction("compilaSecuencia");

function subDependencia($op,$depe_id_padre,$NameDiv)
{
    global $user_id,$depe_id;
    
    $objResponse = new xajaxResponse();

    $otable = new AddTableForm();
    $otable->setLabelWidth("20%");
    $otable->setDataWidth("80%");

    if(getSession("sis_userid")>1 && getSession("sis_level")!=3){
        $ver_todas_las_dependencias=0;
    }else{
        $ver_todas_las_dependencias=1;
    }
    
    if(!$ver_todas_las_dependencias){
        $sqlDependencia=new dependencia_SQLBox($depe_id);        
    }else{
        $sqlDependencia=new dependencia_SQLBox($depe_id_padre);                
        $depe_id='';
    }
    $sqlDependencia=$sqlDependencia->getSQL();

    $otable->addField("Sub Dependencia: ",listboxField("Sub Dependencia",$sqlDependencia,"nbusc_depe_id","$depe_id","-- Seleccione Sub Dependencia--","onChange=\"xajax_getUsuarios(1,this.value,'$user_id');onChange=xajax_muestraSecuencia(xajax.getFormValues('frm'),'DivTipDocs')\"","","class=\"my_select_box\" style=\"width:60%\""));
    
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
        $oForm->addField("Usuario: ",listboxField("Usuario",$sqlUsuarios,"nbusc_user_id",'',"-- vacio (Para toda la Dependencia) --","onChange=xajax_muestraSecuencia(xajax.getFormValues('frm'),'DivTipDocs')","","class=\"my_select_box\"")); 


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


function muestraSecuencia($formData,$NameDiv)
{
	global $conn;
	$objResponse = new xajaxResponse();
	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");


	$nbusc_peri_anno=$formData["nbusc_peri_anno"];
	$nbusc_depe_id=$formData["nbusc_depe_id"];
        $nbusc_tiex_id=$formData["nbusc_tiex_id"];
        $nbusc_user_id=$formData["nbusc_user_id"];

        $td=new clsTipExp_SQLlista();
        $td->whereID($nbusc_tiex_id);
        $td->setDatos();
        $td_secuencia=$td->field('tiex_secuencia');

        if(!$nbusc_user_id)
            $secuencia="gestdoc.corr_exp_".$td_secuencia.'_'.$nbusc_peri_anno.'_'.$nbusc_depe_id.'_'.$nbusc_tiex_id;
        else
            $secuencia="gestdoc.corr_exp_".$td_secuencia.'_'.$nbusc_peri_anno.'_'.$nbusc_tiex_id.'_'.$nbusc_user_id;

	$nextValue=$conn->currval($secuencia);

	$button = new Button;
	$button->setDiv(false);
	$button->addItem(" Actualizar ","if(ObligaCampos(frm)){xajax_compilaSecuencia('$secuencia',document.frm.nr_nextValor.value,'DivMuestraSecuencia')}","",2,0,"botonAgg","button");

	$oForm->addField("Valor Siguiente: ",numField("Valor Siguiente","nr_nextValor",$nextValue,8,8,0)."&nbsp;".$button->writeHTML());	
	$contenido_respuesta=$oForm->writeHTML();

	$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);	
        $objResponse->addClear('DivMuestraSecuencia','innerHTML');

	return $objResponse;
}

function compilaSecuencia($secuencia,$nextValue,$NameDiv)
{
	global $conn;

	$objResponse = new xajaxResponse();
	$objResponse->setCharEncoding('utf-8');	
	
	$conn->setval($secuencia,$nextValue);
	
	$error=$conn->error();
	if($error) {
		//si hay error se asume que no esta creada la secuencia, entonces se procede a crearla
		$conn->nextid($secuencia);			
		$error=$conn->error();
		if($error) {
			$objResponse->addAlert($error);
			return $objResponse;		
		}
		else{
			//la siguiente linea es recomendable para evitar incosistencias en la instruccion $conn->curval
			$conn->setval($secuencia,$nextValue);
	
			$error=$conn->error();
			if($error) {
				$objResponse->addAlert($error);
				return $objResponse;		
			}
		}
	}

	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");
	$oForm->addField("","ACTUALIZACION REALIZADA...!!");	   
	$contenido_respuesta=$oForm->writeHTML();


	$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);	

	return $objResponse;
}

$xajax->processRequests();
// fin para Ajax

?>
<html>
<head>
	<title>Correlativo de Documentos</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="<?php echo PATH_INC?>js/focus.js"></script>	
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>			
        
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.2.min.js"></script>   
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                        
        
	<script language='JavaScript'>
	function mivalidacion(frm) {  
		var sError="Mensajes del sistema: "+"\n\n"; 	
		var nErrTot=0; 	 
		if (frm.nr_nextValor.value<=0) {
			   foco="frm.nr_nextValor.focus()"			   
			   sError+="Valor del campo 'Valor Siguiente' no Valido "+"\n" 
			   nErrTot+=1;
		}

		if (nErrTot>0){ 		
			alert(sError)
			eval(foco)			
			return false
		}else
			return true			
	}
	
	function inicializa() {
		parent.content.document.frm.nbusc_depe_id.focus();
	}
	</script>
        <?php 
	$xajax->printJavascript(PATH_INC.'ajax/'); 
	verif_framework(); 
	?>			
</head>
<body class="contentBODY"  onLoad="inicializa()">
<?php
pageTitle("Inicializaci&oacute;n de N&uacute;mero de Documento","");
echo "<br><br>";
/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$nperiodoset=date('Y');
$sqlPeriodo = "SELECT peri_anno as id,peri_anno as descripcion FROM periodo order by peri_anno ";
$form->addField("Periodo: ",listboxField("Periodo",$sqlPeriodo,"nbusc_peri_anno",$nperiodoset,"","onChange=xajax_muestraSecuencia(xajax.getFormValues('frm'),'DivTipDocs')"));

/* Instancio la Dependencia */
$bd_depe_id=getSession("sis_depe_superior");
$sqlDependencia=new dependenciaSuperior_SQLBox(getSession("sis_depe_superior"));
$sqlDependencia=$sqlDependencia->getSQL();        
$form->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia,"tr_depe_id","$bd_depe_id","-- Seleccione Dependencia --","onChange=\"xajax_subDependencia(1,this.value,'divSubDependencia')\"","","class=\"my_select_box\""));        
//FIN OBTENGO

$form->addHtml("<tr><td colspan=2><div id='divSubDependencia'>\n");
$form->addHtml(subDependencia(2,$bd_depe_id,'divSubDependencia'));
$form->addHtml("</div></td></tr>\n");


$texp=new clsTipExp_SQLlista();
$texp->orderUno();
$sqltipo=$texp->getSQL_cbox();
$form->addField("Tipo de Documento",listboxField("Tipo de Documento",$sqltipo,"nbusc_tiex_id",$bd_tiex_id,'-- Seleccione tipo de expediente --',"onChange=xajax_muestraSecuencia(xajax.getFormValues('frm'),'DivTipDocs')","","class=\"my_select_box\" style=\"width:60%\""));

$form->addHtml("<tr><td colspan=2><div id='divUsuarios' >\n"); //pide datos de afectacion presupuestal
$form->addHtml(getUsuarios(2,$depe_id,"$user_id"));
$form->addHtml("</div></td></tr>\n");


//$form->addField("Dependencia: ",listboxField("Dependencia",$sql,"tr_depe_id",'',"-- Seleccione Dependencia --","onChange=xajax_cargar(xajax.getFormValues('frm'),'DivTipDocs')"));

$form->addHtml("<tr><td colspan=2><div id='DivTipDocs'>\n");
$form->addHtml("</div></td></tr>\n");
$form->addHtml("<tr><td colspan=2><div id='DivMuestraSecuencia'>\n");
$form->addHtml("</div></td></tr>\n");
echo $form->writeHTML();
?>
    
<script>
    $('.my_select_box').select2({
        placeholder: "Seleccione un elemento de la lista",
        allowClear: true,
        width:'90%'
    });
  
</script>

</body>
</html>
<?php
/* cierro la conexión a la BD */
$conn->close();