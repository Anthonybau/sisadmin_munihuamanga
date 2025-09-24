<?php
include("../library/library.php");
include("personal/Persona_class.php");
include('menu_class.php');
/*VERIFICA SI TIENE ACCESO A FACTURACION*/
$conn = new db();
$conn->open();

if (strlen(getSession("sis_username"))==0) {
        $usuario_logado = "";
	$link = "&nbsp;</font><a href='login.php' target='content' class='navegacao'><img src='../img/go-rt-on.gif' border='0'  title='Ingresar'></a>";
        $bd_pers_foto= "../img/standar_foto.jpg";
} else {
        $myPersona = new clsPersona_SQLlista();
        $myPersona->whereID(getSession("sis_persid"));
        $myPersona->setDatos();    
        
        $bd_pers_foto= iif($myPersona->field("pers_foto"),"==","","../img/standar_foto.jpg","../".PUBLICUPLOAD.'escalafon/'.SIS_EMPRESA_RUC.'/'.$myPersona->field("pers_foto"));        
        if( !file_exists($bd_pers_foto) ){
            $bd_pers_foto= "../img/standar_foto.jpg";
        }
        $usuario_logado = "<a href=\"javascript:carga_menu('01MIPERFIL','MI PERFIL')\" class='navegacao' title='Ingresar a Mi Perfil'>".getSession("sis_username_antiguo")."</a>"." | <font class='text'><b>". getSession("sis_username")."</b></font>";
	
        $link = "|&nbsp;<a href='logout.php' class='navegacao'>SALIR</a>";

}

if(SIS_EFACT==1 && getSession("sis_userid")>1){//SI ha iniciado sesion
    $facturacion=new opcionModulo_SQLlista();
    $facturacion->wherePerfilUsuario(getSession("sis_userid"));
    $facturacion->wherePageOpcion('siscoreCajaIngresos_lista');
    $facturacion->setDatos();
    //echo $facturacion->getSQL();
    $sis_facturacion=$facturacion->existeDatos();
    
    if($facturacion->existeDatos()){
	$sis_facturacion=1;
    }else{
	$sis_facturacion=0;
    }    
    
}elseif(SIS_EFACT==1 && getSession("sis_userid")==1){//ADMIN
    $sis_facturacion=1;
}else{
    $sis_facturacion=0;
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<title>Header</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <!--META HTTP-EQUIV="Refresh" CONTENT="1800;../modulos/header.php"-->
        <script language="JavaScript" src="../library/js/janela.js"></script>
        <script language="JavaScript" src="../library/js/libjsgen.js"></script>

        <script type="text/javascript" src="../library/jquery/jquery.notifications.js"></script>
        <script type="text/javascript" src="../library/jquery/jquery-1.11.3.min.js"></script>   
        <link rel="stylesheet" type="text/css" href="../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../library/bootstrap/js/bootstrap.min.js"></script>        

        <script type="text/javascript" src="../library/notifications/sweet_alert.min.js"></script>

        <link rel="stylesheet" type="text/css" href="<?php echo CSS_HEADER?>">
        <link rel="stylesheet" type="text/css" href="../css/components.css">

        <script type="text/javascript">
        <?php if(SIS_GESTDOC==1){?>        
                
            verNvosxRecibir()
            setInterval("verNvosxRecibir()",50000); // 50 segundos
            
            verNvosxFirmar()
            setInterval("verNvosxFirmar()",50000);

            verNvosColaborativo()
            setInterval("verNvosColaborativo()",50000);
            
            //verSemaforo()
            //setInterval("verSemaforo()",10000);

        <?php }?>
            
         <?php if($sis_facturacion==1){ ?>   
             verFacturacionSUNAT();
             setInterval("verFacturacionSUNAT()",50000);
         <?php }?>     
            
        <?php if(SIS_FCIERRE_NOPAGO){?>        
            timeNOPAGO();
            //setInterval("timeNOPAGO()",10000);
        <?php }?>    
            
         function timeNOPAGO(){
            $.ajax({
               type: "POST",
               url: "gestdoc/timeNOPAGO.php",
               dataType : "json",
               data: "",
               success: function(data){
                    $("#respuesta_proceso").html('<div align=center class="alert alert-danger alert-styled-left alert-bordered">\
                                                    <button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">Close</span></button>\
                                                    ' + data.mensaje + '.\
                                                </div>');
               }
             });
        }
         
        function verAcumDespachos(){
            $.ajax({
               type: "POST",
               url: "gestdoc/verAcumDespachos.php",
               data: "",
               success: function(msg){
                   document.getElementById('acumDespachos').innerHTML=msg;
               }
             });
        }

        function verNvosxRecibir(){
            $.ajax({
               type: "POST",
               url: "gestdoc/verNvosxRecibir.php",
               dataType: "json",
               success: function(data){

                   if( data["acum_xrecibir"]>0 ){                       
                        var enlance='<button class="btn btn-xs btn-success" type="button" onclick="javascript:abrir_xRecibir()" >xReci <span class="badge">' + data["acum_xrecibir"] + '</span></button>';
                        $( "div.notificationxRecibir" ).html( enlance );
                    }else{
                        $( "div.notificationxRecibir" ).empty();
                    }
               }
             });
        }

        function verNvosxFirmar(){
            $.ajax({
               type: "POST",
               url: "gestdoc/verNvosxFirmar.php",
               dataType: "json",
               success: function(data){
                   if(data["acum_xfirmar"]>0){
                        var enlance='<button class="btn btn-xs btn-info" type="button" onclick="javascript:abrir_xFirmar()" >xFirm <span class="badge">' + data["acum_xfirmar"] + '</span></button>';
                        $( "div.notificationxFirmar" ).html( enlance );
                    }else{
                        $( "div.notificationxFirmar" ).empty();
                    }
               }
             });
        }


        function verNvosColaborativo(){
            $.ajax({
               type: "POST",
               url: "gestdoc/verNvosColaborativo.php",
               dataType: "json",
               success: function(data){
                   if(data["acum_colaborativo"]>0){
                        var enlance='<button class="btn btn-xs btn-warning" type="button" onclick="javascript:abrir_colaborativo()" >Colab <span class="badge">' + data["acum_colaborativo"] + '</span></button>';
                        $( "div.notificationColaborativo" ).html( enlance );
                    }else{
                        $( "div.notificationColaborativo" ).empty();
                    }
               }
             });
        }
        

        function verSemaforo(){
            $.ajax({
               type: "POST",
               url: "gestdoc/verSemaforo.php",
               dataType: "json",
               success: function(data){
                   if(data["acum_semaforo1"]>0){
                        var enlance='<button class="btn btn-xs btn-success" type="button" onclick="javascript:abrir_semaforo(1)" data-toggle="tooltip" data-html="true" title="Documentos en Proceso por al menos ' + data["valor_semaforo1"] + ' días"><span class="badge">' + data["acum_semaforo1"] + '</span></button>';
                        $( "div.semaforo1" ).html( enlance );
                    }else{
                        $( "div.semaforo1" ).empty();
                    }


                   if(data["acum_semaforo2"]>0){
                        var enlance='<button class="btn btn-xs btn-warning" type="button" onclick="javascript:abrir_semaforo(2)" data-toggle="tooltip" data-html="true" title="Documentos en Proceso por más de ' + data["valor_semaforo1"] + ' y hasta ' + data["valor_semaforo2"] + ' días"><span class="badge">' + data["acum_semaforo2"] + '</span></button>';
                        $( "div.semaforo2" ).html( enlance );
                    }else{
                        $( "div.semaforo2" ).empty();
                    }

                   if(data["acum_semaforo3"]>0){
                        var enlance='<button class="btn btn-xs btn-danger" type="button" onclick="javascript:abrir_semaforo(3)" data-toggle="tooltip" data-html="true" title="Documentos en Proceso por más de ' + data["valor_semaforo2"] + ' días"><span class="badge">' + data["acum_semaforo3"] + '</span></button>';
                        $( "div.semaforo3" ).html( enlance );
                    }else{
                        $( "div.semaforo3" ).empty();
                    }

               }
             });
        }
        
        function verFacturacionSUNAT(){
            $.ajax({
               type: "POST",
               url: "siscore/verFacturacionSUNAT.php",
               dataType: "json",
               success: function(data){
                   if(data["acum_rechazados"]>0){
                        var enlance='<button class="btn btn-xs btn-danger" type="button" onclick="javascript:abrir_rechazados()" >Rech <span class="badge">' + data["acum_rechazados"] + '</span></button>';
                        $( "div.notificationRechazados" ).html( enlance );
                   }else{
                        $( "div.notificationRechazados" ).empty();
                   }

                   if(data["ult_envio_info"]!=''){
                        $( "div.ult_envio" ).html( data["ult_envio_info"] );
                   }else{
                        $( "div.ult_envio" ).empty();
                   }
                   
                   if(data["acum_xcobrar"]>0){
                        var enlance='<button class="btn btn-xs btn-warning" type="button" onclick="javascript:abrir_xCobrar()" >xCob <span class="badge">' + data["acum_xcobrar"] + '</span></button>';
                        $( "div.notificationxCobrar" ).html( enlance );
                   }else{
                        $( "div.notificationxCobrar" ).empty();
                   }
               }
             });
	}
                
        <?php if(SIS_GESTMED==9){ ?>

        verHistoriasenConsultorios()
        setInterval("verHistoriasenConsultorios()",50000);
            
        function verHistoriasenConsultorios(){
            $.ajax({
               type: "POST",
               url: "gestmed/verHistoriasenConsultorios.php",
               dataType: "json",
               success: function(data){

                   if( data["historias_en_consultorio"]>0 ){                       
                       var enlance='<button class="btn btn-xs btn-warning" type="button" onclick="javascript:abrir_ubicacion_historia()">enCons <span class="badge">' + data["historias_en_consultorio"] + '</span></button>';
                        $( "div.divHistoriasenConsultorio" ).html( enlance );
                    }else{
                        $( "div.divHistoriasenConsultorio" ).empty();
                    }
               }
             });
        }
        
        <?php } ?>

        
    	function abreConsulta(id) {
            if(id=='' || id==0){
                alert('Ingrese N\u00FAmero de Tr\u00E1mite');
                document.frmConsultas.nr_numTramite.focus();
                return false
            }
		// la extensión, o separador "&" debe ser substituido por coma ","
            abreJanelaAuxiliar('/sisadmin/gestdoc/controllers/procesar_data.php?nr_numTramite=' + id+',vista=NoConsulta',820,700);
	}


        function abreJanelaAuxiliar(pagina,nWidth,nHeight){
                eval('janela = window.open("../library/auxiliar.php?pag=' +  pagina +
                    '","janela","width='+nWidth+',height='+nHeight+',top=50,left=150' +
                        ',scrollbars=no,hscroll=0,dependent=yes,toolbar=no")');
                janela.focus();
        }
        
        function carga_menu(sist_id,sist_breve){
            for(a=0;a<top.frames.length;a++){
                if(top.frames[a].name=='menu_left'){
                    break;
                }
            }
            top.frames[a].location="menu.php?sist_id="+sist_id+"&sist_breve="+sist_breve
            top.frames[a].location="menu.php?sist_id="+sist_id+"&sist_breve="+sist_breve
            top.middleLayout.open('west')  
        }

        function abrir_xRecibir(){
            top.content.location.href = "gestdoc/recibirDespacho_buscar.php?clear=1&busEmpty=1";
        }
        
        function abrir_xFirmar(){
            top.content.location.href = "gestdoc/firmadosDespacho_buscar.php?clear=1&busEmpty=1";
        }
        
        function abrir_colaborativo(){
            top.content.location.href = "gestdoc/colaborativoDespacho_buscar.php?clear=1&busEmpty=1";
        }
        
        function abrir_semaforo(op){
            top.content.location.href = "gestdoc/procesoDespacho_buscar.php?clear=1&busEmpty=1&semaforo="+op;
        }
        
        function abrir_rechazados(){
            top.content.location.href = "siscore/siscoreCajaIngresos_lista.php?clear=1&tr_procesados=3";
        }
        
        function abrir_xCobrar(){
            top.content.location.href = "siscore/siscoreCajaIngresos_lista.php?clear=1&tr_procesados=6";
        }
        
        function abrir_ubicacion_historia(){
            top.content.location.href = "gestmed/consultarCitas_buscar.php?clear=1&nBusc_ubicacion=2";
        }
        
</script>
</head>
<?php
verif_framework(); 
?>

<body class="headerBODY">
<div class="col-md-12" id="respuesta_proceso"></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" >
	<tr>
		<!--td width="120px">&nbsp;</td-->
		<td width="2%">&nbsp;</td>
                <td width="40%" class="siglas"><?php echo SIS_EMPRESA_BREVE."</font>".
		iif(RUN_MODE,'==','developerx',' --- '.DB_HOST.'//'.DB_DATABASE,'').
                iif(SIS_EFACT_MODO,'==',3,"<font class='slogan'>(MP)</font>",'') 
                ?></td>

                <td width="33%" ROWSPAN="2" valign="top">
                            <table width='100%' cellspacing="0" border="0" cellpadding="0">
                                <tr>
                                    <td align="center" valign="top" width='38%' >
                                        <?php if(SIS_GESTDOC==1 && getSession("sis_userid")){
                                         ?>
                                            <form name='frmConsultas' id='frmConsultas'  action='' method='POST' target='controle'> 
                                                <input type='search' placeholder="# <?php echo NAME_EXPEDIENTE ?>" name='nr_numTramite' id='numero_expediente' STYLE='text-align:right' value='' size='10' maxlength='14'  onKeyPress='return formato(event,form,this,10,3);document.frm.Consultar.focus();'>
                                                <button type="button"  class="btn btn-xs" id="Consultar" name="Consultar"  onClick="javascript:abreConsulta(document.frmConsultas.nr_numTramite.value)"><span class="glyphicon glyphicon glyphicon-search" aria-hidden="true"></span></button>
                                            </form>                                        
                                            <?php	
                                            }
                                            ?>                                        
                                    </td>

				    <?php
                                    if(SIS_GESTDOC==1){
                                    ?>
                                            <!--NOTIFICADOR-->                                    
                                            <td width='16%' valign="top" >                                                    
                                                <div class="notificationxRecibir"></div> 
                                                <div class="notificationxFirmar"></div>
                                                <div class="notificationColaborativo"></div>
                                            </td>                            
                                            <!--FIN NOTIFICADOR-->    
                                           
                                            <td width='2%' valign="top" >                                 
                                                <div class="semaforo1"></div> 
                                                <div class="semaforo2"></div>
                                                <div class="semaforo3"></div>
                                            </td>                                            
				    <?php
                                     }                                     
                                     if($sis_facturacion==1){                                         
                                     ?>
                                            <td width='24%' align="center" valign="top">
                                                <font class='LabelFONT'><div class="ult_envio"></div></font>
                                            </td> 
                                            <td width='18%' align="center" valign="top">
                                                <div class="notificationRechazados"></div> 
                                                <div class="notificationxCobrar"></div>
                                                <?php
                                                if(SIS_GESTMED==1){
                                                    echo "<div class='divHistoriasenConsultorio'></div>";
                                                }                       
                                                ?>
                                            </td> 

                                    <?php                                     
                                        }
                                     ?>
                                </tr>
                            </table>
                </td>

                <td width="24%" align="right">
                    <?php 
                    if( getSession("sis_userid") ){
			echo $usuario_logado.$link;
                    }
                    ?>
		</td>
                <td width="1%" rowspan="2" align="center" valign="top">
                    
                    <?php 
                    if( getSession("sis_userid") ){
			echo "<img src=\"$bd_pers_foto\" class='img-circle' >";
                    }
                    ?>                    

                </td>        
	</tr>
        <tr valign="top">
            <td>&nbsp;</td>
            <td class="slogan">Gesti&oacute;n/Transparencia en WEB</td>
            <td align="right">
                <div class="text-dependencia">
       		<?php
                    if(getSession("sis_depename")){ // Si es usuario de Sistema
                        echo "<b>".getSession("sis_depename")."&nbsp;</b>";
                    }else{ // Es usuario de Convenio de Planillas
                        echo "<b>".getSession("sis_provname")."</b>";
                    }
                ?>
                </div>
            </td>
        </tr>
</table>
</body>
</html>