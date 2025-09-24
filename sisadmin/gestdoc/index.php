<?php
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Consulta de Expedientes</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.11.2/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.11.2/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.2/js/dataTables.bootstrap4.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

<script src="js/tramite_interaccion.js"></script>
<link rel="stylesheet" type="text/css" href="../intranet/css/contenidos.css">

<!--script src="https://www.google.com/recaptcha/api.js" async defer></script-->
</head>
<header>
    <style>
        .card { 
            width: 70%;
            margin: 0 auto;
            background-color: rgba(0, 0, 0, 0.6); 
            opacity: 0.8;
        }
        
        body {
            background-image: linear-gradient(to bottom, rgba(256,256,256,0.3) 0%,rgba(256,256,256,0.3) 100%), url('assets/imagenes/consulta_tramite_<?php echo strtolower(SIS_EMPRESA_SIGLAS)?>.jpg');
            background-attachment: fixed;
            background-repeat:no-repeat;
            background-size:cover;
            background-position:center; 
        }
        
        #g-recaptcha-response {
            display: block !important;
            position: absolute;
            margin: -78px 0 0 0 !important;
            width: 302px !important;
            height: 76px !important;
            z-index: -999999;
            opacity: 0;
        }        
         
    </style>
    
    <?php include("layout/page-header.php"); ?>
    
</header>            
<body>    
    <div class="container">
            </br>
            <div class="card text-white">
              <div class="card-body">
                  <form name="frm" id="frm" role="form" action="controllers/procesar_data.php" method="post"  class="form-horizontal" target="controle">
                        <!--https://fontawesome.com/v4.7/icons/!-->
                        <div class="form-group">
                            <label for="numeroExpediente">N&uacute;mero de <?php echo NAME_EXPEDIENTE ?>:</label>
                            <input type='text' class="form-control" name='nr_numTramite' id='nr_numTramite' placeholder="Escriba su Número de <?php echo NAME_EXPEDIENTE ?>" onKeyPress='return formato(event,form,this,20)' required="true">
                        </div>
                        <h5><i class="fa fa-info" aria-hidden="true"></i>nformación: <br><small>Para consultar el estado de tu solicitud, Ingresa el Número de Trámite o Expediente. Ejm: 21123</small></h5>                        

                        <div class="form-group">
                            <div
                                class="g-recaptcha"
                                data-sitekey="<?php echo  KEY_SITIOWEB ?>">
                            </div>
                        </div>

                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block" ><span class="fa fa-check-square-o fa-2x" aria-hidden="true"></span> CONSULTAR </button>
                        </div> 

                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="checkMasOpciones" >
                            <label class="form-check-label" for="checkMasOpciones">¿NO RECUERDAS tu Registro? Pulsa Aqu&iacute;</label>
                        </div>                        
                        
                        <div id="divMasOpciones">
                            <h5><i class="fa fa-info" aria-hidden="true"></i>nformación: <br><small>Busca tu documento haciendo uso de los siguientes criterios:</small></h5>                                                    
                            <div class="form-group">
                                <label> Tipo de Documento: </label>
                                <select title="Selecciona Tipo de Documento" class="select" name="documento_tipodocumento" id="documento_tipodocumento" placeholder="Pulsa aquí para abrir la Lista" style="width: 100%"><                                    
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="fechaDesde">Fecha Desde:</label>
                                <input type="date" class="form-control" placeholder="Seleccione Fecha" id="dbusc_fdesde" name="dbusc_fdesde" value = "" >
                            </div>
                            
                            <div class="form-group">
                                <label for="fechaHasta">Fecha Hasta:</label>
                                <input type="date" class="form-control" placeholder="Seleccione Fecha" id="dbusc_fhasta" name="dbusc_fhasta" value = "" >
                            </div>
                            
                            <div class="form-group">
                                <label for="numeroIdentificacion">DNI/RUC/Carnét de Extranjería:</label>
                                <input type='text' class="form-control" name='nbusc_dni_ruc' id='nbusc_dni_ruc' placeholder="Escriba aquí su número de DNI, RUC o Carnét de Extranjería"  >
                            </div>
                            
                            <div class="form-group">
                                <label for="numeroDocumento">Número de Documento:</label>
                                <input type='text' class="form-control" name='nbusc_numero' id='nbusc_numero' placeholder="Escriba aquí su número de Documento"  >
                            </div>
                            
                            <div class="form-group">
                                <label for="numeroExpediente">Asunto/Firma/Entidad:</label>
                                <input type='text' class="form-control" name='Sbusc_cadena' id='Sbusc_cadena' placeholder="Escriba aquí Asunto, Firma o Entidad"  >
                            </div>
                            
                            
                            <div class="form-group">
                                <button type="button" class="btn btn-light btn-block" id="btn_search" name="btn_search"><span class="fa fa-search fa-2x" aria-hidden="true"></span> Buscar </button>
                            </div> 

                        </div>
                        
                    </form>
              </div>
            </div>
            
            <div id="divTable">
                
                <table id="tablResultados" class="table table-striped table-hover dt-responsive" style="width:100%">                
                    <thead>
                        <tr>
                            <th></th>
                            <th>Trámite</th>
                            <th>Fecha</th>
                            <th>Identificación</th>
                            <th>Documento</th>
                            <th width="40%">Asunto</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th></th>
                            <th>Trámite</th>
                            <th>Fecha</th>
                            <th>Identificación</th>
                            <th>Documento</th>
                            <th width="40%">Asunto</th>
                        </tr>
                    </tfoot>
                </table>                
            </div>
            
    </div>
    
       
    <script>

        $(document).ready(function () {
                window.onload = function() {
                    var $recaptcha = document.querySelector('#g-recaptcha-response');

                    if($recaptcha) {
                        $recaptcha.setAttribute("required", "required");
                    }
                };
         
            $( '#divMasOpciones' ).hide();
            $( '#divTable' ).hide();
        });


    </script>       
</body>
</html>