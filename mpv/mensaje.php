<?php
    include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Respuesta-Mesa de Partes Virtual</title>

	<!-- Global stylesheets -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
	<link href="assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">

	<link href="assets/css/bootstrap.css" rel="stylesheet" type="text/css">
	<link href="assets/css/core.css" rel="stylesheet" type="text/css">
	<link href="assets/css/components.css" rel="stylesheet" type="text/css">
	<link href="assets/css/colors.css" rel="stylesheet" type="text/css">
	<!-- /global stylesheets -->

	<!-- Core JS files -->

	<script type="text/javascript" src="assets/js/core/libraries/jquery.min.js"></script>
	<script type="text/javascript" src="assets/js/core/libraries/bootstrap.min.js"></script>
	<!-- /core JS files -->

	<!-- Theme JS files -->
	<script type="text/javascript" src="assets/js/plugins/notifications/sweet_alert.min.js"></script>

	<!-- /theme JS files -->

</head>

<body class="navbar-bottom">

        <header>
            <?php 
            
                $conn = new db();
                $conn->open();

                include("layout/page-header.php"); 
            
                $id=getSession('id');
            
                if(!isset($_SESSION['respuesta'])){
                    $respuesta=false;
                    $mensaje="<span>SIN MENSAJE DE RESUPUESTA";
                }else{
                    $respuesta=getSession('respuesta');
                    $mensaje=getSession('mensaje');
                    
                }

                $page="index.php";
                if($id!=""){
                    $page.="?id=$id";
                }


                $mensaje.=" <BR><a href=\"$page\"> Volver al Inicio </a></span>";
            
                $conn->close();
            ?>
            
            <!-- Second navbar -->
            <div class="navbar navbar-inverse navbar-transparent <?php echo iif($respuesta, '==', true, 'bg-success','bg-danger')?>">
                <h5 class="text-white"><i class="glyphicon glyphicon-<?php echo iif($respuesta, '==', true, 'ok','remove')?>"></i> RESPUESTA A SU SOLICITUD</h5>
            </div>
            <!-- /second navbar -->                        
        </header>    
        
	<!-- Page container -->
	<div class="page-container">

		<!-- Page content -->
		<div class="page-content">

			<!-- Main content -->
			<div class="content-wrapper">

				<!-- content -->
				<div class="row">
                                    <fieldset class="content-group">
                                                <div class="col-md-12" id="respuesta_proceso">
                                                    <center>
                                                    <?php 
                                                        if($respuesta){
                                                            echo '<div class="alert alert-success alert-styled-left alert-arrow-left alert-bordered">Su solicitud se ha procesado correctamente!<br><br>'.$mensaje.'<br/><br/></div>';
                                                        }else{
                                                            echo '<div class="alert alert-danger alert-styled-left alert-bordered">'.$mensaje.'</div>';
                                                        }
                                                     ?>
                                                     </center> 
                                                </div>
                                    </fieldset>
				</div>
				<!-- /content -->

			</div>
			<!-- /main content -->

		</div>
		<!-- /page content -->

	</div>
	<!-- /page container -->
        
</body>
</html>

<?php 
unset($_SESSION['id']); 
unset($_SESSION['respuesta']);
unset($_SESSION['mensaje']); 