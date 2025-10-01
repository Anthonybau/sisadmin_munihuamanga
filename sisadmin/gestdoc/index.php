<?php
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Expedientes</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        html, body {
            height: 100%;
        }
        body {
            display: flex;
            flex-direction: column;
            background-color: #f0f2f5;
        }
        .content-wrap {
            flex: 1 0 auto;
        }
        .search-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

    <?php include("layout/page-header.php"); ?>

    <main class="content-wrap py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card search-card">
                        <div class="card-body p-4 p-md-5">
                            <h2 class="card-title text-center h3 mb-4">Consulta de Expedientes</h2>
                            
                            <form name="frm" id="frm" action="controllers/procesar_data.php" method="post" target="controle">
                                
                                <div class="mb-3">
                                    <label for="nr_numTramite" class="form-label">Número de <?php echo NAME_EXPEDIENTE; ?>:</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-lg" name="nr_numTramite" id="nr_numTramite" placeholder="Ej: 21123" required>
                                        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-search me-2"></i>CONSULTAR</button>
                                    </div>
                                    <div class="form-text">
                                        Ingresa el Número de Trámite o Expediente para ver su estado.
                                    </div>
                                </div>
                                
                                <div class="g-recaptcha my-3" data-sitekey="<?php echo KEY_SITIOWEB; ?>"></div>
                                
                                <div class="accordion" id="accordionAdvancedSearch">
                                    <div class="accordion-item border-0">
                                        <h2 class="accordion-header" id="headingOne">
                                            <button class="accordion-button collapsed bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdvancedSearch" aria-expanded="false" aria-controls="collapseAdvancedSearch">
                                                ¿No recuerdas tu número de registro? Haz clic aquí para más opciones
                                            </button>
                                        </h2>
                                        <div id="collapseAdvancedSearch" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionAdvancedSearch">
                                            <div class="accordion-body border-top">
                                                <p class="text-muted">Busca tu documento usando los siguientes criterios:</p>
                                                
                                                <div class="row g-3">
                                                    <div class="col-md-12">
                                                        <label for="documento_tipodocumento" class="form-label">Tipo de Documento:</label>
                                                        <select class="form-select" name="documento_tipodocumento" id="documento_tipodocumento" style="width: 100%"></select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="dbusc_fdesde" class="form-label">Fecha Desde:</label>
                                                        <input type="date" class="form-control" id="dbusc_fdesde" name="dbusc_fdesde">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="dbusc_fhasta" class="form-label">Fecha Hasta:</label>
                                                        <input type="date" class="form-control" id="dbusc_fhasta" name="dbusc_fhasta">
                                                    </div>
                                                    <div class="col-12">
                                                        <label for="nbusc_dni_ruc" class="form-label">DNI/RUC/Carnét de Extranjería:</label>
                                                        <input type="text" class="form-control" name="nbusc_dni_ruc" id="nbusc_dni_ruc">
                                                    </div>
                                                    <div class="col-12">
                                                        <label for="nbusc_numero" class="form-label">Número de Documento:</label>
                                                        <input type="text" class="form-control" name="nbusc_numero" id="nbusc_numero">
                                                    </div>
                                                     <div class="col-12">
                                                        <label for="Sbusc_cadena" class="form-label">Asunto/Firma/Entidad:</label>
                                                        <input type="text" class="form-control" name="Sbusc_cadena" id="Sbusc_cadena">
                                                    </div>
                                                    <div class="col-12 d-grid">
                                                         <button type="button" class="btn btn-secondary mt-2" id="btn_search"><i class="bi bi-funnel-fill me-2"></i>Buscar por Filtros</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mt-5" id="divTable" style="display: none;">
                <div class="col-lg-12">
                     <div class="card search-card">
                         <div class="card-body p-4">
                            <h3 class="h5 mb-3">Resultados de la Búsqueda</h3>
                            <div class="table-responsive">
                                <table id="tablResultados" class="table table-striped table-hover" style="width:100%">
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
                                </table>
                            </div>
                         </div>
                     </div>
                </div>
            </div>

        </div>
    </main>
    
    <?php
    // Incluimos el footer
    if (file_exists('layout/page-footer.php')) {
        include 'layout/page-footer.php';
    }
    ?>
    
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script src="js/tramite_interaccion.js"></script>
    
    <script>
        $(document).ready(function () {
            // Inicializar Select2 con el tema de Bootstrap 5
            $('#documento_tipodocumento').select2({
                theme: "bootstrap-5",
                placeholder: "Pulsa aquí para abrir la lista"
            });

            // Lógica para el reCAPTCHA (si es necesario)
            window.onload = function() {
                var $recaptcha = document.querySelector('#g-recaptcha-response');
                if ($recaptcha) {
                    $recaptcha.setAttribute("required", "required");
                }
            };
        });
    </script>

</body>
</html>