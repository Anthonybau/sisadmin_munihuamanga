<?php
    include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Consulte su Solicitud</title>

	<!-- Global stylesheets -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
	<link href="assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
	<link href="assets/css/icons/fontawesome/styles.min.css" rel="stylesheet" type="text/css">
	<link href="assets/css/bootstrap.css" rel="stylesheet" type="text/css">
	<link href="assets/css/core.css" rel="stylesheet" type="text/css">
	<link href="assets/css/components.css" rel="stylesheet" type="text/css">
	<link href="assets/css/colors.css" rel="stylesheet" type="text/css">
	<!-- /global stylesheets -->

	<!-- Core JS files -->
	<script type="text/javascript" src="assets/js/plugins/loaders/pace.min.js"></script>
	<script type="text/javascript" src="assets/js/core/libraries/jquery.min.js"></script>
	<script type="text/javascript" src="assets/js/core/libraries/bootstrap.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/loaders/blockui.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/ui/nicescroll.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/ui/drilldown.js"></script>
	<!-- /core JS files -->

</head>

<body>
    <style>
        iframe {
            display: block;       /* iframes are inline by default */
            border: none;         /* Reset default border */
            height: 100vh;        /* Viewport-relative units */
            width: 100vw;
        }

    	.card {
    		width: 70%;
    		margin: 0 auto;
    	}
        
    </style>
    
    <header>
        <?php include("layout/page-header.php"); ?>
    </header>            
    
    <div class="container">
        <div class="card text-center">
            <div class="card-header">
                <h2>Consulte su Solicitud</h2>
            </div>
            <div class="card-body">
                <div class="row">
                                        
                        <form id='myForm' class="form-horizontal" role="form">
                          <div class="form-group">
                            <label for="numero_tramite" class="col-lg-3 control-label">Número de Trámite:</label>
                            <div class="col-lg-9">
                              <input type="text" class="form-control" id="id_despacho" name="id_despacho" 
                                                        placeholder="Digite número de trámite" required aria-describedby="textHelp">
                            </div>
                          </div>


                          <div class="form-group">

                              <button type="submit" class="btn btn-success btn-block"><span class="glyphicon glyphicon glyphicon-search" aria-hidden="true"></span> Buscar</button>

                          </div>
                        </form>                    

                    </div>
                </div>
            </div>
        </div>

    <div>
        <iframe id="myIframe" scrolling="no" frameborder="0" marginwidth="0" marginheight="0" width="100%" height="100%" allowfullscreen></iframe>                    
    </div>

    <?php //include("layout/page-Footer.php"); ?>    
</body>
</html>

    <script>

    $(document).ready(function () {
    
        
        $('#id_validacion').focus();
    
        $("#myForm").on('submit', function (evt) {
            evt.preventDefault();

            if (this.checkValidity() === false) {
                return false;
            } else {
                consultarTramite();
            }
        
        });
    
    });

    function consultarTramite(){

        var id_despacho = $('#id_despacho').val();

        var n = id_despacho.indexOf(".");
        
        if(n>0){        
            var url ="/sisadmin/intranet/modulos/gestdoc/rptConsultarTramite.php?id_despacho=" + id_despacho + "&destino=1";
            if(id_despacho){
                $('#myIframe').attr("src", url);
            }
        }else{
            alert('Ingrese un Dato Correcto!');
        }
        
    
    }
        
  </script>