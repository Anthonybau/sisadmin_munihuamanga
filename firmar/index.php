<?php
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/modulos/signer/signer.php");
ini_set('session.gc_maxlifetime', 86400); /* Tiempo de vida de la session 24 horas (24*60*60).  POr alguna raz?n 9 horas no es suficiente. */
ini_set('session.cookie_lifetime', 0); /* Se borra la cookie de la session al cerrar el navegador */
?>
<!DOCTYPE html>
<html>
	<script>
		//Esta funcion es para poder crear unas cookies
		function setCookie(c_name,value,exdays)
		{
		var exdate=new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
		document.cookie=c_name + "=" + c_value;
		}
		setCookie('bit4id-sign','sign',1)
	</script>
        
        <?php
//            verif_framework();
        ?>
        
	<head>
		<meta charset="utf-8" />
		<title>Firma Digital de un Documento en Local</title>
	</head>
	<body>
	<?php
                
		//$folders=Lista de carpetas necesarias para llegar al fichero sign-ok.php. Tiene que ser el path absoluto de la url.
		//$documentName=Nombre del documento a firmar.
		//$documentID=Identificador del documento. Sirve para relacionar el proceso dado que se env?a por m?todo POST al destino.
		//$tipo=Tipo de firma a realizar. Puede ser PAdES, XAdES y CAdES. 
		//$url_4identity_server_sign=Url del script con las funciones del 4identity server.
                
                $conn = new db();
                $conn->open();
                
                $id=$_GET['sign_id'];                
		$file_sign_id="df_".$id;  /*desp_id*/
                
                //BUSCO EL ARCHIVO A FIRMAR
                if (!is_file($file_sign_id.'.zip')){
                    echo "<font color=red>Archivo $file_sign_id.zip No Hallado para Firma!</font>";
                    exit;
                }
                //SI ES MODO PRUEBAS
                
                $signer=new signer_SQLlista();
                $signer->whereID($id);
                $signer->setDatos();
                $bloqueo=$signer->field('sign_bloqueo'); 
                $dni=$signer->field('sign_dni'); 
                

                if($bloqueo==0){//SI NO ESTA BLOQUEADO
                    echo "<font color=red>Proceso Cancelado!<br> Estado del Documento NO preparado para firma!</font>";
                    exit;
                }
                
                if(!$dni){
                    echo "<font color=red>Proceso Cancelado!<br> NO se encontraron datos de identificaci&oacute;n del firmante...</font>";
                    exit;
                }       
                
               
                
                $posicion=$signer->field('sign_posicion'); 
                $tipo_razon=$signer->field('sign_tipo_razon'); 
                $job=$signer->field('sign_job'); 
                
                $conn->close();
                
                if($tipo_razon==2){//V°B°
                    $format="Firma digital de:";
                    $razon="Apruebo el documento";
                    $pos_x1=1;
                    $pos_x2=55;
                    
                    $pos_y1=772-(30*$posicion)-iif($posicion,'>',1,10,0);
                    $pos_y2=808-(30*$posicion)-iif($posicion,'>',1,10,0);
                   
                    $file_img='vb_'.strtolower(SIS_EMPRESA_SIGLAS).'.png';
                }else{                    
                    $format="Firmado digitalmente por:";
                    $razon="Soy el autor del documento";
                    $pos_x1=430;
                    $pos_x2=585;
                    
                    $pos_y1=832-(30*$posicion)-iif($posicion,'>',0,10,0);
                    $pos_y2=868-(30*$posicion)-iif($posicion,'>',0,10,0);
                
                    $file_img='logo_'.strtolower(SIS_EMPRESA_SIGLAS).'.png';
                }
                
//                $pos_x=800-(20*$orden);
//                $pos_y=820-(20*$orden);
		$folders="firmar/";
		$documentName="$file_sign_id.zip";
		$documentID="_01_local";
		$tipo="PAdES";
	?>	
                <script>
                    var xmlHttp = new XMLHttpRequest();
                        xmlHttp.onreadystatechange = function() {
                            if( xmlHttp.readyState==4 && xmlHttp.status==200 ){
                                var respons =  JSON.parse(xmlHttp.responseText)
                                if (respons['success']=='ok'){
                                    document.write(respons['respons']);
                                }else{
                                    document.write(respons['mensaje']);
                                }
                            }
                        }
                    
                    xmlHttp.open( "GET", "https://fullcomputercenter.com/sisadmin/signer_identity.php", false ); // false for synchronous request
                    xmlHttp.send( null );
                    
                </script>
                
		<form class="bit4id-sign" method="post" action="<?php echo $folders; ?>sign-ok.php"> <!-- En action tiene que venir el path incluido -->
                        <br>
			<br>
			<input type="submit" disabled="disabled" value="Procesando..." >
                        
                        <div style="visibility: hidden" class="bit4id-signReq" >
                            <div class="bit4id-localFile">NO</div>

                            <div class="bit4id-documentName"><?php echo $documentName?></div> 
                            <div class="bit4id-documentID"><?php echo $documentID.'-'.$id ?></div>
                            <div class="bit4id-document"><?php echo PATH_FIRMA ?>firmar/<?php echo $file_sign_id?>.zip</div>
                            <div class="bit4id-bundle">YES</div>
                            <div class="bit4id-preview">NO</div>
                            <div class="bit4id-signatureType"><?php echo $tipo; ?></div> <!-- Sirve para indicar el tipo de firma a realizar. -->
                            <div class="bit4id-position">[<?php echo $pos_x1 ?>,<?php echo $pos_y1?>,<?php echo $pos_x2 ?>,<?php echo $pos_y2?>]</div>
                            <div class="bit4id-image"><?php echo PATH_FIRMA ?>sisadmin/intranet/img/<?php echo $file_img ?></div>
                            <div class="bit4id-reason"><?php echo $razon ?></div>
                            <?php if(SIS_EFACT_MODO==3000){//MODO PRUEBAS?>
                                <div class="bit4id-subjectFilter">serialNumber=IDCPE-<?php echo $dni ?>|serialNumber=PNOPE-<?php echo $dni ?>|serialNumber=DNI-<?php echo $dni ?></div>
                            <?php
                            }
                            ?>
                            <div class="bit4id-location"><?php echo $job ?></div>
                            <div class="bit4id-paragraphFormat">[{ "font" :
                                                                                                    ["Universal",18], "align":"right",
                                                                                                    "data_format":{"timezone":"America/Lima",
                                                                                                    "strtime":"%d/%m/%Y  %H:%M:%S%z"}, 
                                                                                                    "format": ["<?php echo $format ?>",
                                                                                                    "$(CN)s",
                                                                                                    "$(Location)s",
                                                                                                    "Motivo: $(Reason)s", 
                                                                                                    "Fecha: $(date)s", ]}]</div>
                            
                        </div>

			<div id="bit4id-status">loading</div> <!-- Este div debe de contener la palabra loading. Cuando cambie a connected se podr? firmar. -->
			
		</form>
                
                
                <script>

                //        Datos para Bit4id
                    window.onload = function () {

                        var interval = setInterval(function () {
                            if (document.getElementsByClassName("bit4-link")[0] != undefined) {
                                window.location.href = document.getElementsByClassName("bit4-link")[0].href; //event.preventDefault(); return false; //propio
                                clearInterval(interval);
                            }
                        }, 600);

                    };
                </script>
	</body>
</html>