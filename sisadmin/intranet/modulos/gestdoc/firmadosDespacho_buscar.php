<?php
/* Modelo de página que apresenta um formulario con criterios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/modulos/gestdoc/registroDespacho_edicionAdjuntosClass.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("../catalogos/catalogosDependencias_class.php");
include("../admin/adminUsuario_class.php");
include("../catalogos/catalogosTipoExpediente_class.php");
include("firmadosDespacho_class.php");
include("registroDespacho_class.php");
include("firmar_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$busEmpty = 1; //getParam("busEmpty"); // para definir si se muestran los datos en el primer ingreso
$depe_id=getParam("nbusc_depe_id");
$user_id=getParam("nbusc_user_id");

$param= new manUrlv1();
$param->removePar('clear');

$myClass = new firmados(0,NAME_EXPEDIENTE." x Firmar/Firmados ");


if ($clear==1) {
    setSession("cadSearch","");
}

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "firmados","buscar"),"");
$xajax->registerExternalFunction(array("verDetalle", "despacho","verDetalle"),"");
$xajax->registerExternalFunction(array("clearDiv", "despacho","clearDiv"),"");
$xajax->registerExternalFunction(array("beforeFirma", "Firmar","beforeFirma"),"");

$xajax->registerFunction("closeModal");
$xajax->registerFunction("borrarFirma");
$xajax->registerFunction("borrarFirmaEjecutar");
$xajax->registerFunction("autorizaRehacerDocumento");
$xajax->registerFunction("setFirma");

function closeModal()
{
    global $conn;
        //ELIMINA ARCHIVOS ZIP GENERADOS    
    $signerDelete=new signer_SQLlista();
    $signerDelete->whereUsuaID(getSession("sis_userid"));
    $signerDelete->whereHoy();    
    $sqlSignerDelete=$signerDelete->getSQL();
    $rsSignerDelete = new query($conn, $sqlSignerDelete);
    while ($rsSignerDelete->getrow()){
        $file=$_SERVER[DOCUMENT_ROOT]."/firmar/df_".$rsSignerDelete->field('sign_id').".zip";
        if(file_exists($file)){
            unlink($file);
        }
    }
    
    $signer=new signer();    
    $signer->desbloquear();
    $objResponse = new xajaxResponse();
    $objResponse->addScript("parent.content.location.reload()");
    return $objResponse;
}

function borrarFirma($defi_id,$NameDiv)
{
    global $id;
    $objResponse = new xajaxResponse();

    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");

    $firmas=new despachoFirmas_SQLlista();
    $firmas->whereID($defi_id);
    $firmas->setDatos();
    
    //$oForm->addBreak($firmas->field('empleado').' ('.$firmas->field('cargo').'-'.$firmas->field('dependencia').")",true,2,"Center");
    $oForm->addBreak($firmas->field('empleado'),true,2,"Center");
    $oForm->addField("Usuario:",$firmas->field('usua_login'));        

    //$oForm->addField("",$firmas->field('cargo'));        
    //$oForm->addField("",$firmas->field('dependencia'));        
    $oForm->addField("Contrase&ntilde;a:",passwordField("password","sx_senha","",20,50));
    $oForm->addBreak("<font color=red>ASEGURESE DE TENER GRABADO EL DOCUMENTO</font>",true,2,"Center");
            
    $button = new Button;
    $button->addItem(" Cerrar ","","",0,0,"","button-modal");
    $button->addItem(" Borrar ","javascript:if(document.frm.sx_senha.value==''){alert('Campo Contrase\u00f1a es obligatorio');return false;}else{ocultarObj('id_firmar',10);xajax_borrarFirmaEjecutar(1,'$id','$defi_id',document.frm.sx_senha.value);}","content",0,0,"","btn-bootstrap","","id_firmar");
            
    $contenido_respuesta=$oForm->writeHTML();
    $contenido_respuesta.="<div class=\"modal-footer\">";
    $contenido_respuesta.=$button->writeHTML();
    $contenido_respuesta.="</div>";
    $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);
    $objResponse->addScript("$('#password').focus();");
    return $objResponse;
}

function borrarFirmaEjecutar($op,$id,$defi_id,$senha)
{
    global $conn;
    $objResponse = new xajaxResponse();
    if($op==1){
        $_senha = md5(addslashes($senha));
    }else{
        $_senha = '';
    }
    
    // Armo strng a ejecutar
    $sSql="SELECT gestdoc.func_borrarfirma($op,'$defi_id','$_senha')";

    // Ejecuto el string
    $conn->execute($sSql);
    $error=$conn->error();

    if($error){ 
        $objResponse->addAlert($error);
    }else{
        unset($_SESSION["ocarrito"]);
        $destino="firmadosDespacho_buscar.php?clear=1&busEmpty=1";
        $objResponse->addRedirect($destino);
    }
    return $objResponse;
}

function autorizaRehacerDocumento($defi_id)
{
    global $conn;
    $objResponse = new xajaxResponse();
    
    // Armo strng a ejecutar
    $sSql=" UPDATE gestdoc.despachos_firmas
                SET defi_autoriza_rehacer=1
                WHERE defi_id=$defi_id";

    // Ejecuto el string
    $conn->execute($sSql);
    $error=$conn->error();

    if($error){ 
        $objResponse->addAlert($error);
    }else{
        unset($_SESSION["ocarrito"]);
        $destino="firmadosDespacho_buscar.php?clear=1&busEmpty=1";
        $objResponse->addRedirect($destino);
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
	<script language="javascript" src="../../library/js/checkall.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>
        <script type="text/javascript" src="../../library/jquery/jquery-1.9.js"></script>   
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>                
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                
        <script type="text/javascript" src="../../library/tablesorter/jquery.tablesorter.js"></script>        
        <style>
            #lectorPDF{
              width: 95% !important;
            }
        </style>
    
	<script language="JavaScript">
        
        <?php echo $myClass->jsSorter($nomeCampoForm);?>
            
        function inicializa() {
                document.frm.Sbusc_cadena.focus();
        }

        function AbreVentana(sURL){
                var w=720, h=600;
                venrepo=window.open(sURL,'rptSalida', "status=yes,resizable=yes,toolbar=no,scrollbars=yes,top=50,left=150,width=" + w + ",height=" + h, 1 );
                venrepo.focus();
        }


        function abreConsulta(id) {
            AbreVentana('../../../portal/gestdoc/consultarTramiteProceso.php?nr_numTramite=' + id+'&vista=NoConsulta');
        }
        
        function imprimir(id) {
            AbreVentana('rptDocumento.php?id=' + id);
        }        
        
        function verFile(file) {
            AbreVentana(file);
        }   
        
        function beforeFirmarVarios() {
            regSel=$("#tLista tbody input[type=checkbox]").is(":checked");
            if(regSel){
                var checked = []
                $("input[name='sel[]']:checked").each(function ()
                {
                    checked.push($(this).val());
                });
                xajax_beforeFirma(checked,0);
                
            } else {
                $('#msg-myModalAviso').text( "Seleccione un Registro");
                $('#myModalAviso').modal('show');                                                                                            
            }
        }


        function setFirmaElectronica(id, id_firma){  
            jQuery("#chk-error").html('<center><p><small class="text-success"><b>Espere, procesando...</b></small></p></center>');
            var data = new FormData();
            data.append('id', id);
            data.append('id_firma',id_firma)

            jQuery.ajax({
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();         
                    xhr.upload.addEventListener("progress", function(element) {
                        if (element.lengthComputable) {
                            var percentComplete = ((element.loaded / element.total) * 100);
                            $("#file-progress-bar").width(percentComplete + '%');
                            $("#file-progress-bar").html(percentComplete+'%');
                        }
                    }, false);
                    return xhr;
                },
                url: "ponerFirmaElectronica.php",        // Url to which the request is send
                type: "POST",             // Type of request to be send, called as method
                data: data, 			  // Data sent to server, a set of key/value pairs (i.e. form fields and values)
                contentType: false,       // The content type used when sending data to the server.
                cache: false,             // To unable request pages to be cached
                processData:false,        // To send DOMDocument or non processed data file it is set to false
                dataType:'json',
                beforeSend: function(){
                    $("#file-progress-bar").width('0%');
                },
                success: function(data)   // A function to be called if request succeeds
                {
                   if(data.success==true){
                       xajax_setFirma(id_firma);
                   }else{
                       $('#msg-myModalFirma').text( data.mensaje );
                       $('#myModalFirma').modal('show');
                   }    
                },
                error: function (xhr, ajaxOptions, thrownError) {
                  console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                  jQuery('#Guardar').show();
                }
            });
        }
                
        function beforeBorrarFirma(id) {
            $( "#title-myModalScreen" ).addClass( "glyphicon glyphicon-pencil" );
            $( "#title-myModalScreen" ).text( " BorrarFirma");
            xajax_borrarFirma(id,'msg-myModalScreen');
            $('#myModalScreen').modal('show');
            $('#password').focus();
        }         

            function openList(key) {
		var oKey = parent.content.document.getElementById(key);
		var icone = parent.content.document.getElementById('fold_'+key);
		if (oKey.style.visibility == "hidden"){
			oKey.style.visibility = "visible";
			oKey.style.display = "block";
			icone.innerHTML = "&nbsp;-&nbsp;";
			
		} else {
			oKey.style.visibility = "hidden";
			oKey.style.display = "none";
			icone.innerHTML = "&nbsp;+&nbsp;";
		}
            }            

	</script>
        <script type="text/javascript" src="../../library/js/jquerytablas3.js"></script>        
        
        <style type="text/css">
            <!--
            .DataFONT {
                font-size: 8pt;
                color: #000000;
                font-family: Verdana, Arial, Tahoma, Helvetica
                }

            input, textarea, select{
                    font-family: Verdana, Arial, Helvetica;
                    font-size: 8pt;
                    color: #000000;
            }
            .checkbox{
                    margin: 1pt;
            }
            -->
            
        </style>
    <?php 
        $xajax->printJavascript(PATH_INC.'ajax/');
	verif_framework(); 
        ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("B&uacute;squedas de ".$myClass->getTitle());
/* botones */
$button = new Button;
$button->addItem(" Firma Masiva ","javascript:beforeFirmarVarios()","content");
echo $button->writeHTML();

/* formulario de pesquisa */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s");


$texp=new clsTipExp_SQLlista();
$texp->orderUno();
$sqltipo=$texp->getSQL_cbox();
$form->addField("Tipo de Documento:",listboxField("Tipo de Documento",$sqltipo,"nbusc_tiex","","-- Todos --","","","class=\"my_select_box\"")); 

$estado_firma=array("0,<img src=\"../../img/ico_signer.png\" width=15 height=18  border=0 align=absmiddle hspace=1 alt=\"Registros x Firmar\" > (x Fimar)","1,<img src=\"../../img/ico_signer_check.png\" width=15 height=18  border=0 align=absmiddle hspace=1 alt=\"Registros Firmadoos\"> (Firmados)");
$form->addField("Estado: ",radioField("Estado",$estado_firma, "nbusc_estado_firma",0,"",'H'));

//array de parametros que se ingresaran a la funcion de busqueda de ajax
$paramFunction= new manUrlv1();
$paramFunction->removeAllPar();
$paramFunction->addParComplete('colSearch','');
$paramFunction->addParComplete('colOrden','1');
$paramFunction->addParComplete('busEmpty',$busEmpty);
$paramFunction->addParComplete('nbusc_depe_id',$depe_id);
$paramFunction->addParComplete('nbusc_user_id',$user_id);

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

$dialogFirma=new Dialog("myModalFirma","screen");
if(MOTOR_FIRMA=='2'){//REFIRMA
    $dialogFirma->setModal("modal-lg");//largo
    $dialogFirma->setCloseModal();
    $dialogFirma->addObjets("<iframe id=\"myIframe\" scrolling=\"no\" frameborder=\"0\" marginwidth=\"0\" marginheight=\"0\" width=\"100%\" height=\"40%\" allowfullscreen></iframe>");       
}else{
    $dialogFirma->setModal("modal-sm");//largo
    $dialogFirma->setCloseModal();
    $dialogFirma->addObjets("<iframe id=\"myIframe\" scrolling=\"no\" frameborder=\"0\" marginwidth=\"0\" marginheight=\"0\" width=\"100%\" height=\"20%\" allowfullscreen></iframe>");    
}
$form->addHtml($dialogFirma->writeHTML());


$dialog=new Dialog("myModalAviso","warning");
$dialog->setModal("modal-sm");//mediano
$form->addHtml($dialog->writeHTML());        

$dialog=new Dialog("myModalScreen","screen");
$dialog->setModal("modal-ms");//largo
$form->addHtml($dialog->writeHTML());

$dialog=new Dialog("myModalConfirm","confirm");
$dialog->setModal("modal-sm");//mediano
$form->addHtml($dialog->writeHTML());        

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