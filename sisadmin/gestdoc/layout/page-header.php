<?php
// layout/header-componente.php
?>

<style>
    /* ==========================================================================
       ESTILOS PARA EL HEADER (Componente Reutilizable)
       ========================================================================== */

    .main-header {
        /* ✨ CAMBIO: Color de fondo actualizado */
        background-color: #005b89;
        padding: 10px 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Sombra para dar profundidad */
        position: sticky;
        top: 0;
        z-index: 1020;
    }

    /* ✨ CAMBIO: Ajuste de color de texto para contraste */
    .main-header .navbar-brand {
        color: #ffffff; /* Texto blanco */
        padding: 0;
        transition: opacity 0.2s ease-in-out;
    }
    
    .main-header .navbar-brand:hover {
        opacity: 0.9;
    }
    
    /* Ajuste para el subtítulo para que no sea tan prominente */
    .main-header .navbar-brand .fw-light {
        color: #e9ecef; /* Un blanco un poco más tenue */
    }
</style>

<header class="main-header">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="../intranet/img/logo_<?php echo strtolower(SIS_EMPRESA_SIGLAS); ?>.png" 
                 alt="Logo de <?php echo SIS_EMPRESA; ?>" 
                 height="50" 
                 class="d-inline-block">
            
            <div class="ms-3">
                <span class="fw-light d-block" style="font-size: 0.9rem;"><?php echo SIS_EMPRESA; ?></span>
                <span class="fw-bold d-block" style="font-size: 1.1rem;">Consulta de Trámite Documentario</span>
            </div>
        </a>
    </div>
</header>