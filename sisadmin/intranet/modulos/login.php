<?php
/* Esta de formul�rio login */
include("../library/library.php");

/*	verificación a nivel de usuario */
verificaUsuario(0);
verif_framework();


?>
<html>
<head>
<title>login</title>
<meta http-equiv="content-type" content="text/html; charset=es-utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" type="text/css" href="<?php echo CSS_LOGIN?>">
<script language="JavaScript" src="../library/js/libjsgen.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="../library/bootstrap4/bootstrap.min.css">
<script src="../library/bootstrap4/jquery-3.2.1.min.js"></script>        

<script src="../library/bootstrap4/bootstrap.min.js"></script>        
<!--script src="https://www.google.com/recaptcha/api.js" async defer></script-->
<style type="text/css">
    	.card {
            width: 70%;
            margin: 0 auto;
            background-color: rgba(0, 0, 0, 0.6); 
    	}        
        
</style>
<script type="text/javascript">
	
        $(document).ready(function() {
                    $('.ls-modal').on('click', function(e){
                        e.preventDefault();
                        $('#myModal').modal('show').find('.modal-body').load($(this).attr('href'));
                    }); 
                });
                
	function inicializa() {
            document.frm.Sr_username.focus();
	}
        
        function mostrarPassword(){
		var cambio = document.getElementById("Contrasena");
		if(cambio.type == "password"){
			cambio.type = "text";
			$('.icon').removeClass('fa fa-eye-slash').addClass('fa fa-eye');
		}else{
			cambio.type = "password";
			$('.icon').removeClass('fa fa-eye').addClass('fa fa-eye-slash');
		}
	}        
	</script>
</head>
<style>
    .black-background {background-color:#A0A0A0;}
</style>

<body onLoad="inicializa()" onfocus="$('.modal-body').empty();">
<!--font style="color:red;font-size: 18px"><center><b><blink>** COMUNICADO **<BR>SE PONE DE CONOCIMIENTO A LOS USUARIOS DEL SISTEMA INFORMATICO DE TRAMITE DOCUMENTARIO QUE SOLO POR HOY DIA SE SUSPENDERA EL SERVICIO A PARTIR DE LAS 6:00 PM HASTA LAS 10:PM, POR LO QUE DEBERAN TOMAR LAS MEDIDAS DEL CASO. <br>ATT. Resp.del Sistema</blink></b></center></font-->


<div class="container">
    <center>
            <?php
                $file_login="../img/logo_".strtolower(SIS_EMPRESA_SIGLAS).".png";
                if(!file_exists($file_login)){
                   // $file_login="../img/logo_".strtolower(SIS_EMPRESA_SIGLAS).".jpg";
                }
                if(!file_exists($file_login)){
                    $file_login="../img/login.png";
                }                
            ?>
        
            <img src="<?php echo $file_login ?>" class="img-fluid">
        
            <h2 class="text-black">Inicia sesión para continuar</h2>
    </center>
        
            
    <div class="card text-center">
      <div class="card-body">
            <form name="frm" id="frm" role="form" action="login_validar.php" method="post"  class="form-horizontal" target="controle">

                    <?php
                        $message=getParam('message');
                        if($message){
                            echo "<div class=\"alert alert-danger\">                       
                                    <strong>$message</strong> 
                                  </div>";                        
                        }
                    ?>                       
                <div class="form-group">
                    <div class="input-group">
                          <div class="input-group">
                                <!--https://fontawesome.com/v4.7/icons/!-->
                             <span class="input-group-text" id="inputGroup-sizing-sm">
                                 <span class="fa fa-user fa-lg"></span>
                             </span>
                              <input type='text' class="form-control" name='Sr_username' id='Usuario' placeholder="Escriba su Usuario" onKeyPress='return formato(event,form,this,20)' >
                          </div>          
                    </div>                    
                </div>
                
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-text" id="inputGroup-sizing-sm">
                            <span class="fa fa-lock fa-lg"></span>
                        </span>
                        <input type='password' class="form-control" name='sx_senha' id='Contrasena' placeholder="Escriba su contraseña" onKeyPress="javascript:if(event.keyCode==13){ parent.content.document.frm.submit() }">
                        <div class="input-group-append">
                            <button id="show_password" class="btn btn-primary" type="button" onclick="mostrarPassword()" > <span class="fa fa-eye-slash icon"></span> </button>
                        </div>                                
                    </div>
                </div>

                <div class="form-group">
                            <div
                                class="g-recaptcha"
                                data-sitekey="<?php echo  KEY_SITIOWEB ?>">
                            </div>
                </div>    

                        <div class="form-group">
                            <button type="button" id="ingresar" class="btn btn-primary btn-block" onclick="JavaScript:parent.content.document.frm.submit()"><span class="fa fa-sign-in fa-2x" aria-hidden="true"></span> Ingresar</button>
                        </div> 

                        <div class="form-group">
                            <a href=<?php echo PATH_INC?>auxiliar.php?pag=../modulos/admin/olvidecontrasena.php?x=1,height=50 class='ls-modal'><p class="text-white">¿Olvid&eacute; mi contraseña?</p></a>
                        </div>             
            </form>
      </div>
    </div>
            
	    
</div>

<div id="myModal" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" onclick="$('.modal-body').empty();">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Loading...</p>
                </div>

        </div>
    </div>    
</div>   

</body>
</html>