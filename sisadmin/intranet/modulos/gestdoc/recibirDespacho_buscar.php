<?php
/* Modelo de página que apresenta um formulario con criterios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("recibirDespacho_class.php");
include("registroDespacho_class.php");
include("../catalogos/catalogosDependencias_class.php");
include("../admin/adminUsuario_class.php");
include("../catalogos/catalogosTipoExpediente_class.php");
include("registroDespacho_edicionAdjuntosClass.php");
include("registroDespachoEnvios_class.php");
/* establecer conexión con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$busEmpty = 1; //getParam("busEmpty"); // para definir si se muestran los datos en el primer ingreso
$depe_id=getParam("nbusc_depe_id");
$user_id=getParam("nbusc_user_id");
$lista=getParam("lista");
$hh_recibe=getParam("hh_recibe");

$param= new manUrlv1();
$param->removePar('clear');
$param->removePar('hh_recibe');

$myClass = new despachoRecibir(0,NAME_EXPEDIENTE."s por Recibir");


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
$xajax->registerExternalFunction(array("buscar", "despachoRecibir","buscar"),"");
$xajax->registerExternalFunction(array("verDetalle", "despacho","verDetalle"),"");
$xajax->registerExternalFunction(array("clearDiv", "despacho","clearDiv"),"");

$xajax->registerFunction("getUsuarios");

function getUsuarios($op,$depe_id,$user_id,$arrayParam){
    
        $objResponse = new xajaxResponse();
        //$objResponse->addAlert($tipoDespacho);
        $oForm = new AddTableForm();
        $oForm->setLabelWidth("20%");
        $oForm->setDataWidth("80%");

//        $usuarios=new clsUsers_SQLlista();
//        $usuarios->whereDepeID($depe_id);
//        $usuarios->whereActivo();
//        $sqlUsuarios=$usuarios->getSQL_cbox();
        
        $usuarios=new clsUsersDatosLaborales_SQLlista();
        $usuarios->whereDepeID($depe_id);
        $usuarios->whereActivo();
        $sqlUsuarios=$usuarios->getSQL_cbox();
        $oForm->addField("Usuario: ",listboxField("Usuario",$sqlUsuarios,"nbusc_user_id",$user_id,"-- Todos los Usuarios --","onChange=\"xajax_buscar(1,xajax.getFormValues('frm'),'$arrayParam',1,'DivResultado')\""));


        $contenido_respuesta=$oForm->writeHTML();
	$objResponse->addAssign('divUsuarios','innerHTML', $contenido_respuesta);

        if($op==1){
		return $objResponse;
        }
	else
		return $contenido_respuesta	;
}

$xajax->processRequests();

// fin para Ajax
?>
<html>
<head>
	<title><?php echo $myClass->getTitle() ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT ?>">
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
        
	<script language="JavaScript">

		function inicializa() {
			document.frm.Sbusc_cadena.focus();
		}
	
		function recibir() {
                    if (confirm('Seguro de recibir registros seleccionados?')) {
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(2)."&".$param->buildPars(false) ?>";
			parent.content.document.frm.submit();
                    }
                }

                function AbreVentana(sURL){
                    var w=720, h=600;
                    venrepo=window.open(sURL,'rptSalida', "status=yes,resizable=yes,toolbar=no,scrollbars=yes,top=50,left=150,width=" + w + ",height=" + h, 1 );
                    venrepo.focus();
                }

                function abreConsulta(id){ 
                    AbreVentana('../../../gestdoc/controllers/procesar_data.php?nr_numTramite=' + id + '&vista=NoConsulta');
                }

	</script>
        <?php 
        $xajax->printJavascript(PATH_INC.'ajax/'); 
        verif_framework(); 
        ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php 
if($hh_recibe){
?>
    <DIV align="center" id="cuadro" style="position:absolute;top:0;width:500;height:15;background-color:lightyellow ">

    <font class="LabelFONT2"><?php echo NAME_EXPEDIENTE.":<b>".$lista."</b> Fecha y Hora de Recepci&oacute;n :<b>".$hh_recibe?></b></font>
    </div>
<?php
}

pageTitle("B&uacute;squedas de ".$myClass->getTitle());

/* botones */
$button = new Button;
//$button->addItem(" Nuevo ","catalogosArchivadores_edicion.php".$param->buildPars(true),"content");
$button->addItem(" Recibir ","javascript:recibir()","content");
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
$form->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia,"nbusc_depe_id",$depe_id,"$todos","onChange=\"xajax_getUsuarios(1,this.value,'$user_id','".encodeArray($paramFunction->getUrl())."');xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\"","","class=\"my_select_box\"")); 

$form->addHtml("<tr><td colspan=2><div id='divUsuarios' >\n"); //pide datos de afectacion presupuestal
$form->addHtml(getUsuarios(2,$depe_id,"$user_id",$arrayParam));
$form->addHtml("</div></td></tr>\n");

$texp=new clsTipExp_SQLlista();
$texp->orderUno();
$sqltipo=$texp->getSQL_cbox();
$form->addField("Tipo de Documento:",listboxField("Tipo de Documento",$sqltipo,"nbusc_tiex","","-- Todos --","","","class=\"my_select_box\"")); 

$form->addField("Exp/N&uacute;m.".NAME_EXPEDIENTE.": ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">");
$form->addHtml("<tr><td colspan=2><div id=\"divResultados\"></div></td></tr>");
$form->addHtml("<tr><td colspan=2>");
$form->addHtml("<table width=\"100%\"><tr valign=\"top\">");
$form->addHtml("<td><div id='DivResultado'>\n");
$form->addHtml($myClass->buscar(2,'',encodeArray($paramFunction->getUrl()),1,'DivResultado'));
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
        
        

    $(document).ready(function () {

        $('.download-link').on('click', function(e) {

            e.preventDefault();
            var $this = $(this);
            var id = $this.data('id');

            download(id);
        });


        function download(id){
            var data = new FormData();
            data.append('id', id);

            if( id ){

                    $.ajax({
                        url: "jswDescargarDocumento.php",        // Url to which the request is send
                        type: "POST",             // Type of request to be send, called as method
                        data: data, 			  // Data sent to server, a set of key/value pairs (i.e. form fields and values)
                        contentType: false,       // The content type used when sending data to the server.
                        cache: false,             // To unable request pages to be cached
                        processData:false,        // To send DOMDocument or non processed data file it is set to false
                        dataType:'json',
                        success: function(data)   // A function to be called if request succeeds
                        {
                           
                            if(data.success==true){                                      
                                AbreVentana(data.file);
                                //window.open(data.file, 'Resultado');
                                //location.href = 'http://httpbin.org/bytes/1024';
                            }else{
                               $("#divResultados").html('<center><p><small class="text-danger"><b>' + data.mensaje + '</b></small></p></center>');
                            }                         
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                          console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                        }
                    });

                }
            }
                    
                    
    })
                
</script>
</html>
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();