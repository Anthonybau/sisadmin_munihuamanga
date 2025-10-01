<?php
/* Esta de formulário login */
include("../library/library.php");

/* verificación a nivel de usuario */
verificaUsuario(0);
verif_framework();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* --- Estilos para el Login Moderno --- */
        html, body {
            height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
            background-color: #f0f2f5; /* Un fondo gris claro y suave */
        }

        /* Clase para la solución de sticky footer */
        .content-wrap {
            flex: 1 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 30px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }

        .login-logo {
            max-width: 200px;
            margin-bottom: 1.5rem;
        }

        .form-floating-group .input-group-text {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .form-floating-group .form-control {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        
        .btn-toggle-password {
            border-left: 0;
        }
        
        .forgot-password-link {
            text-decoration: none;
        }
        
        .forgot-password-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body onload="document.frm.Sr_username.focus()">

    <main class="content-wrap">
        <div class="login-card">
            <div class="text-center">
                <?php
                    $file_login = "../img/logo_" . strtolower(SIS_EMPRESA_SIGLAS) . ".png";
                    if (!file_exists($file_login)) {
                        $file_login = "../img/login.png";
                    }
                ?>
                <img src="<?php echo $file_login; ?>" alt="Logo de la Empresa" class="login-logo img-fluid">
                <h2 class="h4 mb-4 fw-normal">Inicia sesión para continuar</h2>
            </div>

            <form name="frm" id="frm" action="login_validar.php" method="post" target="controle">

                <?php
                    $message = getParam('message');
                    if ($message) {
                        echo "<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">
                                " . htmlspecialchars($message) . "
                                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
                              </div>";
                    }
                ?>

                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <div class="form-floating flex-grow-1">
                        <input type="text" class="form-control" name="Sr_username" id="Usuario" placeholder="Usuario" required>
                        <label for="Usuario">Nombre de Usuario</label>
                    </div>
                </div>

                <div class="input-group mb-3">
                     <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <div class="form-floating flex-grow-1">
                        <input type="password" class="form-control" name="sx_senha" id="Contrasena" placeholder="Contraseña" required onkeypress="if(event.keyCode==13) document.frm.submit()">
                        <label for="Contrasena">Contraseña</label>
                    </div>
                    <button class="btn btn-outline-secondary btn-toggle-password" type="button" onclick="mostrarPassword()">
                        <i class="bi bi-eye-slash" id="toggleIcon"></i>
                    </button>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
                    </button>
                </div>

                <div class="text-center">
                    <a href="<?php echo PATH_INC; ?>auxiliar.php?pag=../modulos/admin/olvidecontrasena.php?x=1,height=50" class="forgot-password-link ls-modal">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
            </form>
        </div>
    </main>

    <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Recuperar Contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    </div>
            </div>
        </div>
    </div>

    <?php
    // Incluimos el componente del footer minimalista
    // Asegúrate de que la ruta sea correcta
    if (file_exists('../layout/page-footer.php')) {
        include '../layout/page-footer.php';
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Función para mostrar/ocultar contraseña (actualizada para Bootstrap Icons)
        function mostrarPassword() {
            var cambio = document.getElementById("Contrasena");
            var icon = document.getElementById("toggleIcon");

            if (cambio.type === "password") {
                cambio.type = "text";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            } else {
                cambio.type = "password";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            }
        }

        // Script para manejar el modal (actualizado para Bootstrap 5)
        $(document).ready(function() {
            var myModal = new bootstrap.Modal(document.getElementById('myModal'));

            $('.ls-modal').on('click', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                
                // Limpiamos el cuerpo del modal y cargamos el nuevo contenido
                $('#myModal .modal-body').html('<p class="text-center">Cargando...</p>').load(url, function() {
                    myModal.show(); // Mostramos el modal
                });
            });

            // Limpia el modal cuando se cierra para evitar contenido cacheado
            $('#myModal').on('hidden.bs.modal', function () {
                $(this).find('.modal-body').empty();
            });
        });

        // Script para el submit del formulario (se mantiene)
        // He movido el onkeypress del input al botón para mejor práctica
        document.getElementById('ingresar').addEventListener('click', function() {
            document.frm.submit();
        });
    </script>
</body>
</html>