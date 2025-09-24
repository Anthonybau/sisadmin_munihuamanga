<?php
/* Modelo de p�gina que apresenta um formulario con crit�rios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("catalogosCliente_class.php"); 
include("catalogosUbigeo_class.php");
include("catalogosTabla_class.php");
include("catalogosServicios_class.php");

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$nomeCampoForm=getParam("nomeCampoForm");
$TipClase = getParam("TipClase"); // Tipo de Clase 
$busEmpty = getParam("busEmpty"); // Tipo de Clase 
$pg = getParam("pg"); // Tipo de Clase 
$pg = $pg?$pg:1;

$param= new manUrlv1();
$param->removePar('clear');

$myClass = new cliente(0,"B&uacute;squedas de ".iif(SIS_EMPRESA_TIPO,'==',4,'Beneficiarios','Clientes'));

/*
	limpia la cadena de filtro
	si clear=1 -> esta pagina es llamada desde el menu
	si clear=2 -> esta pagina es llamada desde la busqueda avanzada (AvanzLookup)
*/
if ($clear==1) {
	setSession("cadSearch","");
}

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "cliente","buscar"),"");
$xajax->registerFunction("imprimir");
$xajax->registerFunction("getUnir");
$xajax->registerFunction("guardar_unir");

function imprimir($NameDiv)
{
    global $conn,$nbusc_depe_id;
    $objResponse = new xajaxResponse();

    $form = new Form("frmImp", "", "POST", "controle", "100%",true);
    $form->setLabelWidth("20%");
    $form->setDataWidth("80%");

//    $lista_formato=array('1'=>'AGRUPADO POR UBIGEO',
//                         '2'=>'AGRUPADO POR UBIGEO-2',
//                         '9'=>'NINGUNO');
//    
//    $form->addField("Formato: ",listboxField("Formato",$lista_formato, "nbusc_formato",9));

    $form->addField("","<a href=\"#\" onClick=\"javascript:proceder( 'Proceder','catalogosClientes_imprimir.php?destino=1' )\" ><img src='../../img/pdf.png' border='0' title='Listar en PDF'></a>&nbsp;".
                       "<a href=\"#\" onClick=\"javascript:proceder( 'Proceder','catalogosClientes_imprimir.php?destino=2' )\" ><img src='../../img/xls.png' border='0' title='Listar en PDF'></a>");

    $contenido_respuesta=$form->writeHTML();
    $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);   
                    
    return $objResponse;
}


function getUnir($ar_registros_seleccionados,$nameDiv){
    global $bd_usua_id;
    $objResponse = new xajaxResponse();

    if(!is_array($ar_registros_seleccionados)){
        $objResponse->addAlert("Sin registros seleccionados para proceder...");
    }elseif(count($array)==1){
            $objResponse->addAlert("Para Unir seleccione por lo menos dos(2) registros...");
        }else{

            $list_registros_seleccionados= implode(",", $ar_registros_seleccionados);

            $form = new Form("frmUnir", "", "POST", "controle", "100%",false);
            $form->setLabelWidth("20%");
            $form->setDataWidth("80%");
            $form->addBreak("UNIR EN:", true);
            

            $clientes=new cliente_SQLlista();
            $clientes->whereIDVarios($list_registros_seleccionados);
            $sqlClientes=$clientes->getSQL_resumen();
            $form->addField("Registros Seleccionados: ",listboxField("Unir todos En",$sqlClientes,"nx_unir","","-- Seleccione Registro --"));
            $form->addField("<font color=red>Importante: </font>","<font color=red><b>Este proceso es irreversible, luego de unidos los clientes NO hay manera de REVERTIR la unión!</b></font>");
            /* botones */
            $button = new Button;

            $button->addItem("UNIR","javascript:if(document.frmUnir.nx_unir.value==''){alert('Seleccione un registro')}else{ if(confirm('Seguro de unir todo en este registro?')) {xajax_guardar_unir(document.frmUnir.nx_unir.value,'$list_registros_seleccionados')}}","content",2,$bd_usua_id,'botao','button');
            $button->align('L');
            $form->addField("",$button->writeHTML());
            $contenido_respuesta=$form->writeHTML();
            
            $objResponse->addAssign($nameDiv,'innerHTML', $contenido_respuesta);

            $objResponse->addScript("$('#myModalOpc').modal('show');");            
            
        }
        
    return $objResponse;    
}

function guardar_unir($id,$list_registros_seleccionados)
{
    global $conn;
    $objResponse = new xajaxResponse();
    
        
    $sql= " SET session_replication_role = replica;
            UPDATE siscore.recaudaciones
                SET clie_id=$id 
                WHERE clie_id!=$id
                    AND clie_id IN ($list_registros_seleccionados);
                        
            UPDATE siscore.guia_remision
                SET clie_id=$id 
                WHERE clie_id!=$id
                    AND clie_id IN ($list_registros_seleccionados);
                        

            UPDATE pedidos.pedidos
                SET clie_id=$id 
                WHERE clie_id!=$id
                    AND clie_id IN ($list_registros_seleccionados);


            UPDATE siscore.contrato_credito
                SET clie_id=$id 
                WHERE clie_id!=$id
                    AND clie_id IN ($list_registros_seleccionados);
              
            SET session_replication_role = DEFAULT;
            
            DELETE FROM catalogos.cliente
                WHERE clie_id!=$id
                    AND clie_id IN ($list_registros_seleccionados);
            
            ";

    $conn->execute($sql);
    $error=$conn->error();
    if($error){
        $objResponse->addAlert($error);
        return $objResponse;
    }    
        

    $objResponse->addScript("parent.content.location.reload()");

    return $objResponse;
}
$xajax->processRequests();

//../../library/
// fin para Ajax
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT ?>">
	<script type="text/javascript" src="<?php echo PATH_INC ?>js/libjsgen.js"></script>	
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>

        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">        
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>
        
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>        
        
	<script language="JavaScript">

		/* funcion que define el foco inicial del formulario */
		function inicializa() {
			document.frm.Sbusc_cadena.focus();
		}
	
		function excluir() {
			regSel=$("#tLista tbody input[type=checkbox]").is(":checked");
			if(regSel){ 
				if (confirm('Desea Eliminar el(los) registro(s) selecionado(s)?')) {
					document.frm.target = "controle";
					document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(2)."&".$param->buildPars(false) ?>";
					document.frm.submit();
				}
			}else{
				alert('Seleccione el(los) registro(s) que desea eliminar')
			}
		}
                
                
                
                function beforeUnir() {
                    regSel=$("#tLista input[type=checkbox]").is(":checked");
                    if(regSel){
                        var checked = []                    
                        $("input[name='sel[]']:checked").each(function ()
                        {
                            checked.push($(this).val());
                        });
                        $("#title-myModalOpc").addClass("glyphicon glyphicon-random");
                        $("#title-myModalOpc").html("&nbsp;<b>UNIR</b>");
                        xajax_getUnir(checked,'msg-myModalOpc');

                    } else {
                        alert('Seleccione un registro');
                    }
                }
                
                function beforeImprimir() {
                    xajax_imprimir('msg-myModalImp');
                    //$( "#title-myModal" ).text( "Imprimir")                
                    $('#myModalImp').modal('show');                    
                }


                function Imprimir() {
                    document.frm.target = "controle";
                    document.frm.action = "catalogosClientes_imprimir.php";
                    document.frm.submit();
                }

                function proceder(idObj,rptPage) {
                        parent.content.document.frmImp.target = "controle";
                        parent.content.document.frmImp.action = rptPage;
                        parent.content.document.frmImp.submit();
                }
			
	</script>
	<script type="text/javascript" src="<?php echo PATH_INC ?>js/jquerytablas3.js"></script> <!-- Esta l�nea debe ir aqu� para luego de que se aplique el orden se refrescan los css de la tabla -->

    <?php 
    $xajax->printJavascript(PATH_INC.'ajax/'); 
    verif_framework(); 
    ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle($myClass->getTitle());

/* botones */
$button = new Button;

if ($nomeCampoForm){
	$button->addItem(LOOKUP_RESET,"javascript:update('','',0)","content",0,0,"link"); //cambio el stylo solo a este boton
}

$button->addItem(" Nuevo ","catalogosCliente_edicion.php".$param->buildPars(true),"content");

if (!$nomeCampoForm){
    $button->addItem("Eliminar","javascript:excluir()","content",2);
    $button->addItem("Unir","javascript:beforeUnir()","content",2);
}

$button->addItem(" Imprimir ","javascript:beforeImprimir()","content");

echo $button->writeHTML();


/* formul�rio de pesquisa */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s");


if(SIS_TIPO_UBIGEO_CLIENTE==1){
    $tblUbigeoPedidos=new clsTabla_SQLlista();
    $tblUbigeoPedidos->whereTipo('UBIGEO_PEDIDOS');
    $tblUbigeoPedidos->orderTres();
    $sql=$tblUbigeoPedidos->getSQL_cbox();
    $form->addField("Ubigeo: ",listboxField("Ubigeo",$sql,"nx_busc_ubigeo_pedido","","-- Seleccione UBIGEO --","","","class=\"my_select_box\"")); 
}else{
    $form->addHidden("nx_busc_ubigeo_pedido",0);
}

if(SIS_TIPO_UBIGEO_CLIENTE==2){
    $ubigeo=new ubigeo_SQLlista();
    $sqlUbigeo=$ubigeo->getSQL_cbox();
    $form->addField("UBIGEO: ",listboxField("UBIGEO",$sqlUbigeo, "sx_busc_ubig_id","","-- Seleccione UBIGEO --","", "","class=\"my_select_box\""));
}else{
    $form->addHidden("sx_busc_ubig_id",0);
}

if( SIS_TIPO_NEGOCIO_CLIENTE==1){
    $tblUbigeoPedidos=new clsTabla_SQLlista();
    $tblUbigeoPedidos->whereTipo('TIPO_NEGOCIO');
    $tblUbigeoPedidos->orderUno();
    $sql=$tblUbigeoPedidos->getSQL_cbox();
    $form->addField("Tipo de Negocio: ",listboxField("Tipo de Negocio",$sql,"nx_busc_tipo_negocio","","-- Seleccione Tipo de Negocio --","","","class=\"my_select_box\""));
}else{
    $form->addHidden("nx_busc_tipo_negocio",0);
}

$sqlGServicio=new clsGrupoTTra_SQLlista();
$sqlGServicio=$sqlGServicio->getSQL_servicioGrupo($tipo);
$form->addField("Grupo de Bien/Servicio: ",listboxField("Grupo de Bien/Servicio",$sqlGServicio,"nBusc_grupo_id","","-- Todos --", "","","class=\"my_select_box\""));


//array de parametros que se ingresaran a la funcion de busqueda de ajax
$paramFunction= new manUrlv1();
$paramFunction->removeAllPar();
$paramFunction->addParComplete('colSearch','');
$paramFunction->addParComplete('busEmpty',$busEmpty);
$paramFunction->addParComplete('numForm',0);

$js="xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',$pg,'DivResultado')";
$form->addField("C&oacute;digo/Descripci&oacute;n: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',50,50)."&nbsp;<input type=\"button\" onClick=\"javascript:$js\" value=\"Buscar\">");

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");

/* Creo array $formData con valores necesarios para filtrar la tabla */
$formData['Sbusc_cadena']=getSession("cadSearch");
$formData['nx_busc_ubigeo_pedido']=0;
$formData['sx_busc_ubig_id']=0;
$formData['nx_busc_tipo_negocio']=0;

if($nomeCampoForm){//si es llamada de una busqueda avanzada
	$form->addHtml($myClass->buscar(2,$formData,encodeArray($paramFunction->getUrl()),$pg,'DivResultado'));
}else{
	$form->addHtml($myClass->buscar(2,$formData,encodeArray($paramFunction->getUrl()),$pg,'DivResultado'));
}

$form->addHtml("</div></td></tr>\n");


echo  $form->writeHTML();
?>
    <div id="myModalImp" class="modal fade">
        <div class="modal-dialog modal-mg">
            <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><span id="title-myModalImp" class="" aria-hidden="true">Imprimir Clientes</span></h4>                    
                    </div>
                    <div id="msg-myModalImp" class="modal-body">
                        <p>Loading...</p>
                    </div>

            </div>
        </div>    
    </div>   
</body>

<div id="myModalOpc" class="modal fade" >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><span id="title-myModalOpc" class="" aria-hidden="true">&nbsp;</span></h4>                    
                    </div>
                    <div id="msg-myModalOpc" class="modal-body">
                        <p>Loading...</p>
                    </div>

            </div>
        </div>    
</div>    
<script>
        $(".my_select_box").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true,
            width:'100%'
        });        
</script>
</html>
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();