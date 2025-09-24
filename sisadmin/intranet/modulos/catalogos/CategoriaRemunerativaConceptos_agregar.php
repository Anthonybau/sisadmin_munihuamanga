<?php
include("../../library/library.php");

/* verificación del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("CategoriaRemunerativaConceptos_class.php");
include("../catalogos/catalogosServicios_class.php");
include("../catalogos/catalogosTabla_class.php");
include("../siscopp/siscoppCatalogosClasificador_class.php");

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$id= getParam("id_relacion");
$relacionamento_id = getParam("id");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente

$param= new manUrlv1();
$param->removePar('clear');

$myClass=new clsCategoriaRemunerativaConceptos($id,"Agregar Concepto Vinculado");
$myClass->setDatos();

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "clsCategoriaRemunerativaConceptos","buscarAgregar"),"");
$xajax->registerFunction("elegir");
$xajax->registerFunction("agregar");

function elegir($care_id,$conc_id,$cantDiv,$NameDiv){
				
	global $conn;

	$objResponse = new xajaxResponse();
        
        //limpio todos los div
        for ($i=0;$i<=$cantDiv;$i++){
            $objResponse->addClear('addDatos_'.$i,'innerHTML');
        }

        $oForm = new AddTableForm();
        $oForm->setLabelWidth("20%");
        $oForm->setDataWidth("80%");
        $oForm->setLabelTD("LabelOrangeTD");
        $oForm->setBackTD("BackOrangeTD");
        
        $concepto=new servicios_SQLlista();
        $concepto->whereID($conc_id);
        $concepto->setDatos();

        $campo='';
        switch($concepto->field("tabl_tipoconcepto")){
            case 121:/*porcentual*/
                $campo='nr_crco_porcentaje';
                $oForm->addField("%: ",numField("%",$campo,$concepto->field("conc_porcentaje"),10,5,2));
                break;
            case 122:/*importe*/
                $campo='nr_crco_importe';
                $oForm->addField("Importe: ",numField("Importe",$campo,$concepto->field("conc_importe"),10,10,2));
                break;
        }
        $tabla=new clsTabla_SQLlista();
        $tabla->whereTipo('MES');
        $tabla->orderUno();
        $sqlTipo=$tabla->getSQL_cbox();
        $oForm->addField("Mes Desde:",listboxField("Mes Desde",$sqlTipo,"tr_tabl_mesdesde",1)."<b> Hasta:</b>".listboxField("Mes Hasta",$sqlTipo,"tr_tabl_meshasta",12));

        $bd_clas_id=$concepto->field("clas_id");
        if(!$bd_clas_id){
            $tabla=new clsTabla_SQLlista();
            $tabla->whereID(48);//JORNALES
            $tabla->setDatos();
            $bd_clas_id=$tabla->field('clas_id');                    
        }
        
        $sqlClasificador=new clsClasificador_SQLlista();
        $sqlClasificador->whereTipo(2);//gastos
        $sqlClasificador->whereEspecifica();
        $sqlClasificador=$sqlClasificador->getSQL_cbox();
        $oForm->addField("Clasificador:",listboxField("Clasificador",$sqlClasificador,"tr_clas_id",$bd_clas_id,"-- Seleccione Clasificador --","","","class=\"my_select_box\""));
        
        $button = new Button;
        $button->setDiv(false);
        $button->addItem("Agregar","javascript:if(ObligaCampos(frm)){xajax_agregar('$care_id','$conc_id',xajax.getFormValues('frm'))}","",2,0,"botonAgg","button");

        $oForm->addHtml("<tr><td colspan=\"8\" align=\"center\" >".$button->writeHTML()."</td></tr>");			

        $contenido_respuesta=$oForm->writeHTML();

        $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);
        //anclo los objetos
        $ndiv=intval($ndiv)-1;
        $objResponse->addScript("document.location='#ancDatos$ndiv'");
        
        $objResponse->addScript("document.frm.$campo.focus()");		
        
        $objResponse->addScript("$('.my_select_box').chosen({
                                    disable_search_threshold: 5, //SE DESHABILITA SI SOLO HAY 5 REGISTROS
                                    allow_single_deselect: true,
                                    search_contains: true,
                                    no_results_text: 'Oops, No Encontrado!',
                                    width: '70%'
                                    });");
        return $objResponse;

}

function agregar($care_id,$conc_id,$formData){
    global $conn;
    $objResponse = new xajaxResponse();
    $mesDesde=$formData['tr_tabl_mesdesde'];
    $mesHasta=$formData['tr_tabl_meshasta'];
    
    $clas_id=$formData['tr_clas_id'];
    $crcoPorcentaje=$formData['nr_crco_porcentaje'];
    $crcoPorcentaje=$crcoPorcentaje?$crcoPorcentaje:0;
    
    $crcoImporte=$formData['nr_crco_importe'];
    $crcoImporte=$crcoImporte?$crcoImporte:0;    
    
    $sSql="INSERT INTO catalogos.categoria_remunerativa_conceptos (
                                        care_id,
                                        serv_codigo,
                                        tabl_mesini,
                                        tabl_mesfin,
                                        clas_id,
                                        crco_porcentaje,
                                        crco_importe,
                                        usua_id)
                       VALUES($care_id,
                              $conc_id,
                              $mesDesde,
                              $mesHasta,
                              '$clas_id',
                              $crcoPorcentaje,    
                              $crcoImporte,
                              ".getSession("sis_userid").")";

    // Ejecuto el string
    $conn->execute($sSql);
    $error=$conn->error();
    if($error){
        $objResponse->addAlert($error);		 			 
    }else{
        
        $paramFunction= new manUrlv1();
        $paramFunction->removeAllPar();
        $paramFunction->addParComplete('colSearch','');
        $paramFunction->addParComplete('colOrden','1');
        $paramFunction->addParComplete('busEmpty',1);
        $paramFunction->addParComplete('relacionamento_id',$care_id);

        $objResponse->addScript("xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')");
        //$objResponse->addScript("xajax_buscar(1,'$relacionamento_id','$tabl_etapa_proceso',1,'DivResultado')");
    }
    return $objResponse;
}        

$xajax->processRequests();

// fin para Ajax
?>
<html>
<head>
	<title><?php echo $myClass->getTitle() ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT ?>">
	<script language="javascript" src="<?php echo PATH_INC ?>js/checkall.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC ?>js/libjsgen.js"></script>
        <script language="JavaScript" src="<?php echo PATH_INC ?>js/textcounter.js"></script>        
        <script type="text/javascript" src="../../library/jquery/jquery-1.9.js"></script>
        <script type="text/javascript" src="../../library/jquery/jquery-ui.js"></script>
        <link rel="stylesheet" href="../../library/jquery-chosen/chosen.css">
        <script src="../../library/jquery-chosen/chosen.jquery.js" type="text/javascript"></script>                        
        
	<script language="JavaScript">
        function salvar(idObj) {
                    if (ObligaCampos(frm)){
                            ocultarObj(idObj,10)
                            document.frm.target = "controle";
                            document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
                            document.frm.submit();
                    }
        }
        
        function mivalidacion(frm) {
            return true
        }
        
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

//$button = new Button;
//$button->addItem("<img src='../../img/save.gif' border='0'>&nbsp;"."Guardar","javascript:salvar('Guardar')","content",2);
//echo $button->writeHTML();

/* formul�rio de pesquisa */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s");
$form->addHidden('___caob_id',$relacionamento_id);
$form->addHidden("f_id",$id); // clave primaria

//array de parametros que se ingresaran a la funcion de busqueda de ajax
$paramFunction= new manUrlv1();
$paramFunction->removeAllPar();
$paramFunction->addParComplete('colSearch','');
$paramFunction->addParComplete('colOrden','1');
$paramFunction->addParComplete('busEmpty',1);
$paramFunction->addParComplete('relacionamento_id',$relacionamento_id);

$form->addField("Concepto: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">");

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");
$form->addHtml($myClass->buscarAgregar(2,'',encodeArray($paramFunction->getUrl()),1,'DivResultado'));
$form->addHtml("</div></td></tr>\n");


echo  $form->writeHTML();

?>
    
<script>    
    $('.my_select_box').chosen({
        disable_search_threshold: 5, //SE DESHABILITA SI SOLO HAY 5 REGISTROS
        allow_single_deselect: true,
        search_contains: true,
        no_results_text: 'Oops, No Encontrado!',
        width: '70%'
        });
</script>                                                                
</body>
</html>
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();
