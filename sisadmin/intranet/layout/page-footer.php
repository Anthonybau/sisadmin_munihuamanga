<?php
// layout/footer-minimalista.php
?>

<style>
    /* ==========================================================================
       ESTILOS PARA FOOTER MINIMALISTA (Componente Reutilizable)
       ========================================================================== */

    /* --- Solución Sticky Footer --- */
    /* Estas reglas aseguran que el footer se mantenga siempre al final de la página */
    html,
    body {
        height: 100%;
    }

    body {
        display: flex;
        flex-direction: column;
    }

    /* Debes asegurarte de que tu contenedor de contenido principal tenga esta clase */
    .content-wrap {
        flex: 1 0 auto;
    }

    /* --- Estilos del Footer Minimalista --- */
    .footer-moderno {
        flex-shrink: 0; /* Parte de la solución sticky */
        background-color: #2c3e50; /* Fondo oscuro elegante */
        color: #bdc3c7; /* Texto gris claro */
        padding: 20px 0;
        border-top: 4px solid #005B89; /* Borde superior con tu color de marca */
    }

    .footer-moderno .footer-copy {
        text-align: center;
        font-size: 14px;
        color: #bdc3c7;
    }
</style>

<footer class="footer-moderno">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="footer-copy">
                    &copy; <?php echo date('Y'); ?> Municipalidad Provincial de Huamanga. Todos los derechos reservados.
                </div>
            </div>
        </div>
    </div>
</footer>