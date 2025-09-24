<?php
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
unset($_SESSION["ocarrito"]);

/* verificacion del nivel de usuario */
verificaUsuario(1);
include("registroDespacho_class.php");
include("registroDespacho_edicionAdjuntosClass.php"); 


/* establecer conexion con la BD */
$conn = new db();
$conn->open();

/* Recibo parametros */
$relacionamento_id = getParam("relacionamento_id"); /* Recibo el dato de ralcionamiento entre la tabla padre e hijo */
$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente

$despacho = new despacho($relacionamento_id);
$despacho->setDatos();


/* Instancio mi clase base */
$myClass = new despachoAdjuntados(0,"Archivos Adjuntos");



?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
        <script language="JavaScript" src="../../library/js/focus.js"></script>        
	<script type="text/javascript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>	
        <script language="javascript" src="<?php echo PATH_INC?>js/checkall.js"></script>
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>           
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>        

        <style>
            #lectorPDF{
              width: 95% !important;
            }
        </style>        
	<script language='JavaScript'>
            
	function mivalidacion(){
            var sError="Mensajes del sistema: "+"\n\n";
            var nErrTot=0;
            if(document.frmImg.exap_adjunto.value==''){ /*DIFERENTE A OTRAS ENTIDADES*/
                sError+="Campo 'Archivo' es obligatorio\n";
                foco='document.frmImg.exap_adjunto';
                nErrTot++                        
            }

            if (nErrTot>0){
                alert(sError)
		eval(foco)
		return false
            }else{
		return true
            }
	}

        
        function upload(desp_id){  
                    
                    $('#Adjuntar').hide()
                    jQuery("#chk-error").html('<center><p><small class="text-success"><b>Espere, procesando...</b></small></p></center>');
                    var inputFile = document.getElementById("exap_adjunto");

                    var data = new FormData();

                    [].forEach.call(inputFile.files, function (file) {
                        data.append('fileToUpload[]', file);
                    });
                    data.append('desp_id', desp_id);
                    data.append('detalle', document.getElementById("Detalles").value);

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
                            url: "upload.php",        // Url to which the request is send
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
                                   parent.content.location.reload();
                                }else{
                                   jQuery("#chk-error").html('<center><p><small class="text-danger"><b>' + data.mensaje + '</b></small></p></center>');
                                   //alert(data.mensaje.substring(0,100));
                                   $('#Adjuntar').show();
                                }                         
                            },
                            error: function (xhr, ajaxOptions, thrownError) {
                              console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                            }
                        });
        }

	function excluir(sel) {
		if (confirm('Eliminar registros seleccionados?')) {
			parent.content.document.frmLista.target = "controle";
			parent.content.document.frmLista.action = "../imes/eliminar.php?_op=elimAdjDesp&sel[]="+sel+"&relacionamento_id=<?php echo $relacionamento_id?>&clear=<?php echo $clear?>";
			parent.content.document.frmLista.submit();
		}
	}

        function AbreVentana(sURL){
            var w=720, h=650;
            venrepo=window.open(sURL,'rptDocumento', "status=yes,resizable=yes,toolbar=no,scrollbars=yes,top=50,left=150,width=" + w + ",height=" + h, 1 );
            venrepo.focus();
        }

        function verFile(file) {
            AbreVentana(file);
	}
        
        function inicializa() {
            document.frmImg.Sr_descripcion.focus();
	}
	</script>
	<? verif_framework(); ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle($myClass->getTitle());

/* Control de fichas, */
$abas = new Abas();
if($clear==1){
    $abas->addItem(" Edici&oacute;n de Documento ",false,"registroDespacho_edicion.php?id=$relacionamento_id&clear=1");
}else{
    $abas->addItem(" Edici&oacute;n de Documento ",false,"registroDespacho_edicionConFirma.php?id=$relacionamento_id&clear=1");
}
$abas->addItem(" Archivos Adjuntos ",true);    
echo $abas->writeHTML();


$form = new Form("frm", "", "POST", "controle", "100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s");
/* botones */
$button = new Button;
$button->addItem(" Regresar ","javascript:if(confirm('\u00BFSeguro de Regresar?')){
                                        document.location='registroDespacho_buscar.php?clear=1'}");
$form->addHtml($button->writeHTML());

$form->addHidden("rodou","s");
//$form->addField(NAME_EXPEDIENTE.": ",$relacionamento_id);
if($despacho->field('desp_estado')==1){//ABIERTO
    $ico_candado="<img src=\"../../img/look_o.png\" border=0 align=absmiddle hspace=1 alt=\"Abierto\">";
}else{
    $ico_candado="<img src=\"../../img/look_c.png\" border=0 align=absmiddle hspace=1 alt=\"Cerrado\">";
}        
$form->addField(NAME_EXPEDIENTE.": ",addLink($ico_candado.$relacionamento_id,"javascript:lectorPDF('../../../portal/gestdoc/consultarTramiteProceso.php?nr_numTramite=$relacionamento_id&vista=NoConsulta','Seguimiento')","Click aqu&iacute; para Seguimiento de registro","","h4").iif($id,">",0,"&nbsp;&nbsp;<b><font color=green>Ult.Grabaci&oacute;n: ".  $usernameactual.' '.substr(stod($bd_desp_actualfecha),0,19)."</font></b>",""));
$form->addField("Asunto: ",$despacho->field('desp_asunto'));

$lectorPDF=new lectorPDF();
$form->addHtml($lectorPDF->writeHTML());
echo  $form->writeHTML();

/*CONSULTO LOS ARCHIVOS ADJUNTDOS*/
$sql=new despachoAdjuntados_SQLlista();
$sql->wherePadreID($relacionamento_id);
$sql = $sql->getSQL();
        
$rs = new query($conn, $sql);
//if($rs->numrows()==0){
if(($cuenta_firmantes>0 && $cuenta_firmantes==$cuenta_firmados)
    && ($cuenta_recibidos==0 || $cuenta_recibidos<$cuenta_derivaciones)){
    
}
    //if($despacho->field('desp_cont_firmados')==0 && $despacho->field('desp_acum_recibidos')==0){

	$formCon = new Form("frmImg", "", "POST", "controle", "100%",true);
        $formCon->setUpload(true);
	$formCon->setLabelWidth("20%");
	$formCon->setDataWidth("80%");
	$formCon->addHidden("___desp_id",$relacionamento_id); // clave primaria


        // definición de lookup
        $max_filesize=ini_get('upload_max_filesize');
        $formCon->addBreak("<b>ADJUNTAR ARCHIVO: <font color=red>(Tama&ntilde;o M&aacute;ximo $max_filesize)</font></b>");
        $formCon->addField("Detalles: <font color=red>*</font>",  textField("Detalles","Sr_descripcion","",80,120));        
        $formCon->addField("Archivo: <font color=red>*</font>",fileField2("Archivo","exap_adjunto" ,"",60,"onchange=validaextension(this,'GIF,JPG,PNG,DOC,DOCX,XLS,XLSX,PPT,PPTX,ODT,ODS,ODP,ZIP,RAR,PDF')"));
        $buttonEdit = new Button;
        $buttonEdit->align("L");
        $buttonEdit->addItem("Adjuntar","javascript:if ( ObligaCampos(frmImg) ){ upload( '$relacionamento_id' ) }","content",2);
        $formCon->addField("",$buttonEdit->writeHTML());                
        $formCon->addHtml("<tr><td colspan=2><span id='chk-error'></span></td></tr>");
        $formCon->addHtml("<tr><td colspan=2><div class='progress'><div id='file-progress-bar' class='progress-bar'></div></td></tr>");
        

        
	echo $formCon->writeHTML();
    //}
//}
        
 
        if($rs->numrows()>0){
?>    
            <!-- Lista -->
            <div align="center">
            <form name="frmLista" method="post">
<?php
            $table = new Table("LISTADO DE ARCHIVOS ADJUNTOS","90%",4); // Título, Largura, Quantidade de colunas
            $table->setTableAlign("C");
            $table->addColumnHeader(""); // Coluna com checkbox
            $table->addColumnHeader("Detalles",false,"60%", "C"); 
            $table->addColumnHeader("Archivo",false,"20%", "C"); 
            $table->addColumnHeader("Creado Por",false,"20%", "L"); // Título, Ordenar?, ancho, alineación
            $table->addRow();

            while ($rs->getrow()) {
                $bd_dead_id = $rs->field("dead_id");
                $area_adjunto = $rs->field("area_adjunto");
                $periodo = $rs->field("periodo");
                $id = $rs->field("desp_id");
                $usua_id = $rs->field("usua_id");
                //if($despacho->field('desp_cont_firmados')==0 && $despacho->field('desp_acum_recibidos')==0){
                if($usua_id==getSession("sis_userid")){
                    $table->addData("<a class=\"link\" href=\"#\" onClick=\"javascript:excluir('$bd_dead_id')\"><img src=\"../../img/delete.gif\" border=0 align=absmiddle hspace=1 alt=\"Eliminar\"></a>");                
                }else{
                    $table->addData("");
                }
                
                $enlace= PUBLICUPLOAD."gestdoc/".SIS_EMPRESA_RUC."/$periodo/".$id."/".$rs->field("area_adjunto");
                
                if(strpos(strtoupper($enlace),'.PDF')>0){
                    $link=addLink($rs->field("dead_descripcion"),"javascript:verFile('$enlace')","Click aqu&iacute; para Ver Documento","controle");
                }else{
                    $link=addLink($rs->field("dead_descripcion"),"$enlace","Click aqu&iacute; para Descargar Archivo","controle");
                }
                
                $table->addData($link);
                $table->addData($rs->field("area_adjunto"));
                $table->addData($rs->field("usua_login"));
                
                if($rs->field("dead_firmar")==1){
                    $table->addRow('ATENDIDO');
                }else{
                    $table->addRow();
                }
            }
            echo $table->writeHTML();
        }
?>
        </form>
        </div>

</body>
</html>
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();