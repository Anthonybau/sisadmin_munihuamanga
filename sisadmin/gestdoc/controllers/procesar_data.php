<?php
include("../../intranet/library/library.php");
include("../../intranet/modulos/gestdoc/registroDespacho_class.php");
include("../../intranet/modulos/gestdoc/registroDespacho_edicionAdjuntosClass.php");
include("../../intranet/modulos/gestdoc/registroDespachoEnvios_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

$numTramite=getParam("nr_numTramite");
$vista=getParam("vista");
$vista=$vista?$vista:'consulta';
$depeid=getSession("sis_depeid")?getSession("sis_depeid"):0;

include("../../intranet/library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("verDetalle", "despacho","verDetalle"),"");
$xajax->registerExternalFunction(array("buscarVista", "despacho","buscarVista"),"");
$xajax->registerExternalFunction(array("clearDiv", "despacho","clearDiv"),"");
$xajax->processRequests();
?>

<head>
<title><?php echo "Consulta de ".NAME_EXPEDIENTE;?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">

<script language="JavaScript" src="../../intranet/library/js/libjsgen.js"></script>
<script language="JavaScript" src="../../intranet/library/bootstrap4/jquery-3.2.1.min.js"></script>

<style>
    .sistema {
	font-family: Trebuchet MS, Verdana, Arial, Helvetica;
	font-size: 13pt;
	font-weight: bold;
	color: #293C7E;
	text-decoration: none;
	text-align: left;
    }

    /* Tooltip container */
    .tooltip {
      position: relative;
      display: inline-block;
      border-bottom: 1px dotted black; /* If you want dots under the hoverable text */
    }

    /* Tooltip text */
    .tooltip .tooltiptext {
      visibility: hidden;
      width: 420px;
      background-color: black;
      color: #fff;
      text-align: center;
      padding: 5px 0;
      border-radius: 6px;

      /* Position the tooltip text - see examples below! */
      position: absolute;
      z-index: 1;
    }

    /* Show the tooltip text when you mouse over the tooltip container */
    .tooltip:hover .tooltiptext {
      visibility: visible;
    }


    .tooltip .tooltiptext {
      opacity: 0;
      transition: opacity 1s;
    }

    .tooltip:hover .tooltiptext {
      opacity: 1;
    }

</style>
<script type="text/javascript">

    function blink(nameDiv){
        if(nameDiv=='') {return;}

        var e=document.getElementsByTagName("div");
        for(var i=0;i<e.length;i++){
            e[i].style.fontSize='7pt';
            e[i].style.textDecoration='none'
        }

        document.getElementById(nameDiv).style.textDecoration='blink';
        document.getElementById(nameDiv).style.fontSize='10pt';
    }

    function ShowDiv(ObjName){
            dObj=document.getElementById(ObjName);
            dObj.style.top = document.body.scrollTop;
            dObj.style.left= (document.body.scrollWidth - 85);
            setTimeout("ShowDiv('"+ObjName+"')",1);
    }

    function ocultarObj(idObj,timeOutSecs){
            // luego de timeOutSecs segundos, el bot�n se habilitar� de nuevo,
            // para el caso de que el servidor deje de responder y el usuario
            // necesite volver a submitir.
            myID = document.getElementById(idObj);
            myID.style.display = 'none';
            document.body.style.cursor = 'wait'; // relojito
            setTimeout(function(){myID.style.display = 'inline';document.body.style.cursor = 'default';},timeOutSecs*1000)
    }
    
    function openList(key) {
		var oKey = document.getElementById(key);
		var icone = document.getElementById('fold_'+key);
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
        
    function AbreVentana(sURL){
        var w=720, h=650;
        venrepo=window.open(sURL,'rptDocumento', "status=yes,resizable=yes,toolbar=no,scrollbars=yes,top=50,left=150,width=" + w + ",height=" + h, 1 );
        venrepo.focus();
    }

    function imprimir(id) {
        AbreVentana('../../intranet/modulos/gestdoc/rptDocumento.php?id=' + id);
    }        
        
    function verFile(file) {
        AbreVentana(file);
    }

    
</script>
<?php
    $xajax->printJavascript(PATH_INC.'ajax/');
?>

</head>
<body style="margin-top: 0px" onload="ShowDiv('cuadro')">
<div id="cuadro" class="oculto" style="position: absolute; top: 0pt; right: 0pt" >
<?php if($vista=='consulta'){?>
    <img src="../../intranet/img/printer.png" id="printer" alt="Imprimir" onclick="ocultarObj('printer',50);javascript:print();" style="cursor: pointer;" height="40" width="40"> 
<?php } ?>
</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td  width="100%" height="100%" valign="top" align="left">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <?php if($vista=='consulta')
                        {
                        ?>
                        <tr>
                            <td width="3%">

                            </td>
                            <td width="1%" align="left" >
                                <img src="../../intranet/img/logo_<?php echo strtolower(SIS_EMPRESA_SIGLAS)?>.png" width="40" height="40" border="0">
                            </td>
                            <td width="53%" align="left" >    
                                <font class='sistema'><?php echo SIS_EMPRESA ?></font>
                            </td>
                            <td width="43%" colspan="2">&nbsp;</td>
                        </tr>
                        <tr height="40px">
                            <td width="3%">&nbsp;</td>
                            <td width="20%" align="left" colspan="2">
                                <a href="../index.php" class="link" target="_parent"  title="Regresar"><img  alt="Regresar" src="../../intranet/img/regresar.png" border="0">Volver a Consultar</a>
                            </td>
                            <td width="76%" align="right" ><img src="../../intranet/img/consulta_tramite_resultado.jpg"></td> 
                            <td width="3%">&nbsp;</td>
                        </tr>
                        <?php 
                        }
                        ?>
                         <tr>
                             <td width="3%">&nbsp;</td>
                             <td colspan="3" >
                                <?php
                                $despacho=new despacho();
                                $form = new Form("frmResulConsulta", "", "POST", "", "100%",false);
                                $form->setLabelWidth("40%");
                                $form->setDataWidth("60%");
                                $form->addHtml("<tr><td colspan=2><div id='DivDetallesNew' >\n"); //pide datos de afectacion presupuestal
                                //si se ha enviado con secuencia de expediente, es decir en decimal
                                if($numTramite>intval($numTramite)){
                                    $form->addHtml($despacho->buscarVista(2,$numTramite,$depeid,'DivDetallesNew'));
                                }
                                else{
                                    $form->addHtml($despacho->buscarVista(2,$numTramite,$depeid,'DivDetallesNew'));
                                    //$form->addHtml($despacho->verDetalle(2,$numTramite,'consulta',$depeid));
                                }
                                $form->addHtml("</div></td></tr>\n");

                                $form->addHtml("<tr><td colspan=2><div id='DivDetalles' >\n"); //pide datos de afectacion presupuestal
                                if($numTramite>intval($numTramite)){
                                    $form->addHtml($despacho->verDetalle(2,$numTramite,'consulta',$depeid,'',0));
                                }
                                $form->addHtml("</div></td></tr>\n");
                                
                                echo $form->writeHTML();
                                 ?>
                             </td>
                             <td width="3%">&nbsp;</td>
                         </tr>
                        <?php if($vista=='consulta')
                        {
                        ?>
                        <tr>
                            <td colspan="4" width="80%" align="right">
                                <a href="../index.php" class="link" target="_parent"  title="Regresar"><img  alt="Regresar" src="../../intranet/img/regresar.png" border="0">Volver a Consultar</a>
                            </td>
                            <td  width="20%">&nbsp;</td>
                        </tr>
                        <?php                         
                        }
                        ?>
                    </table>
            </td>
        </tr>
    </table>

        <script type="text/javascript">

                
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
                                url: "../../intranet/modulos/gestdoc/jswDescargarDocumento.php",        // Url to which the request is send
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
                    
                    
                $('.download-link2').on('click', function(e) {
                    e.preventDefault();
                    var $this = $(this);
                    var id = $this.data('id');
                    download2(id);
                });
                
                function download2(id){
                    var data = new FormData();
                    data.append('id', id);

                    if( id ){

                            $.ajax({
                                url: "../../intranet/modulos/gestdoc/jswDescargarDocumentoAdjunto.php",        // Url to which the request is send
                                type: "POST",             // Type of request to be send, called as method
                                data: data, 			  // Data sent to server, a set of key/value pairs (i.e. form fields and values)
                                contentType: false,       // The content type used when sending data to the server.
                                cache: false,             // To unable request pages to be cached
                                processData:false,        // To send DOMDocument or non processed data file it is set to false
                                dataType:'json',
                                success: function(data)   // A function to be called if request succeeds
                                {
                                    if(data.success==true){
                                        
                                        if( data.file.toUpperCase().indexOf(".PDF")>0) {                                       
                                            AbreVentana(data.file);
                                        }else{
                                            location.href = data.file;
                                        }
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
                
                    
                
            
        </script>
</body>
</html>
<?php
$conn->close();
