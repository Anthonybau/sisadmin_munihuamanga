<?php
/*	archivo comun/modificaci�n de registros */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/*	verificaci�n a nivel de usuario */
verificaUsuario(1);
verif_framework();

/* variable que se recibe la opcion  de datos para eliminaci�n */
$op = getParam("_op");
$op2= getParam("_op2");

/* captura y prepara la lista de registros */
$relacionamento_id = getParam("relacionamento_id");
$arLista_elimina = getParam("sel");
if (is_array($arLista_elimina)) {
	$lista_elimina = implode(",",$arLista_elimina);
}

$erro = new Erro();

switch($op){
        /* --------------------------------*/
        // Organización del Menu Intranet  //
        /* -------------------------------*/
	case 'OrgMenuComp': //
		$sql="DELETE FROM sistema_modulo WHERE simo_id in ('".$lista_elimina."') AND usua_id=".getSession("sis_userid");
		$destino = "../admin/adminOrganizacionMenu_lista.php";
		break;

        case 'OrgMenuElem': // Organización de Menu/Componentes
                $id=getParam("id"); //valor del campo clave
		$sql="DELETE FROM sistema_modulo_opciones WHERE smop_id IN ('".$lista_elimina."') AND usua_id=".getSession("sis_userid");
		$destino = "../admin/adminOrganizacionMenu_elementos.php?_id=$id";
		break;

	//cadena de eliminación
        /* ----------------------------*/
        // Administración de Portales  //
        /* ---------------------------*/
	case 'PortalTipoMenu': //
		$sql="DELETE FROM portal_menu WHERE pome_id in (".$lista_elimina.") AND usua_id=".getSession("sis_userid");
		$destino = "../portal/sisadportEstructurasMenuOp_lista.php";
		break;

	case 'PortalEncuesta': //
		$sql="DELETE FROM portal_encuesta WHERE poen_id in (".$lista_elimina.") AND usua_id=".getSession("sis_userid");
		$destino = "../portal/sisadportContenidosEncuesta_lista.php";
		break;

	case 'PortalNews': //
		$sql="DELETE FROM portal_noticia WHERE pono_id in (".$lista_elimina.") AND usua_id=".getSession("sis_userid");
		$destino = "../portal/sisadportContenidosNoticia_lista.php";
		break;

        case 'excNotImg': // Noticias - imagenes
		$sql="DELETE FROM portal_noticia_fotos WHERE pnfo_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../portal/sisadportContenidosNoticia_edicion.php?id=$relacionamento_id";
		break;

	case 'PortAccInf': // Catalogos/parametros
		$busEmpty=getParam("busEmpty");
		$sigla=getParam("sigla"); //numero de formulario;
		$tinfo = getParam("tinfo");//nombre del tipo de tabla, este dato es del tipo STRING
		$smop_id = getParam("smop_id"); // Id de la opción del menú, para poder obtener en las consultas la OPCION y el GRUPO al que pertenece.
		$sql="DELETE FROM portal_acc_inf WHERE poai_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../portal/sisadportAccesoInformacion_buscar.php?busEmpty=$busEmpty&sigla=$sigla&tinfo=$tinfo&smop_id=$smop_id";
		break;

	case 'PortalBanner': // Catalogos/parametros
		$busEmpty=getParam("busEmpty");
		$sigla=getParam("sigla"); //numero de formulario;
		$sql="DELETE FROM portal_banner WHERE poba_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../portal/sisadportEstructurasBanner_lista.php?busEmpty=$busEmpty&sigla=$sigla";
		break;

	case 'PortalEmergente': // Catalogos/parametros
		$busEmpty=getParam("busEmpty");
		$sigla=getParam("sigla"); //numero de formulario;
		$sql="DELETE FROM portal_emergentes WHERE poem_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../portal/sisadportEstructurasEmergente_lista.php?busEmpty=$busEmpty&sigla=$sigla";
		break;

	case 'PortalTema': //
		$sql="DELETE FROM portal_contenido WHERE poco_id in (".$lista_elimina.") AND usua_id=".getSession("sis_userid");
		$destino = "../portal/sisadportContenidosTema_lista.php";
		break;

	case 'solDonacion': // Solicitud de Donacion
		$busEmpty=getParam("busEmpty");
		$sql="DELETE FROM portal_donacion WHERE podo_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../portal/sisadportEstructurasSolicitaDonaciones_lista.php?busEmpty=$busEmpty";
		break;

	case 'agrDonImg': // Solicitud de Donacion - imagenes
                $busEmpty=getParam("busEmpty");
		$sql="DELETE FROM portal_donacion_fotos WHERE pdfo_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../portal/sisadportEstructurasSolicitaDonaciones_edicion.php?id=$relacionamento_id&busEmpty=$busEmpty";
		break;

	case 'portInfraestruct': // Infraestructura de la entidad, programas sociales, edificios, etc
		$busEmpty=getParam("busEmpty");
                $smop_id=getParam("smop_id");
                $tinfra=getParam("tinfra");
		$sql="DELETE FROM portal_infraestructura WHERE poin_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../portal/sisadportInfraestructura_lista.php?busEmpty=$busEmpty&smop_id=$smop_id&tinfra=$tinfra";
		break;

	case 'excInfraesImg': // Infraestructura - imagenes
                $smop_id=getParam("smop_id");
                $tinfra=getParam("tinfra");
		$sql="DELETE FROM portal_infraestructura_fotos WHERE pifo_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../portal/sisadportInfraestructura_edicion.php?_id=$relacionamento_id&smop_id=$smop_id&tinfra=$tinfra";
		break;

	case 'excDepenImg': // Dependencia - imagenes
		$sql="DELETE FROM dependencia_fotos WHERE defo_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../portal/sisadportEntidad_edicion.php?_id=$relacionamento_id";
		break;

        case 'CatTabla': // Catalogos/parametros
		$busEmpty=getParam("busEmpty");
		$dbrev=getParam("dbrev");
                $colOrden=getParam("colOrden"); //Columna a ordenar
                $setCodigo = getParam("setCodigo");
		$sql="delete from tabla where tabl_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../catalogos/catalogosTablas_buscar.php?clear=1&busEmpty=$busEmpty&colOrden=$colOrden&dbrev=$dbrev&setCodigo=$setCodigo";
		break;

        case 'mueveDependencia': // Escalafon/mu
		$sitLab= getParam("sitLab");
		$pg=getParam("pagina");
		$sql="DELETE FROM persona_desplazamiento WHERE pers_id=$relacionamento_id and pede_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../personal/personalFichaEscalafon_mueveDependenciaLista.php?id=$relacionamento_id&op=$sitLab&pagina=$pg&";
		break;

	/* --------------------------------*/
	// Gestion de Turnos //
	/* -------------------------------*/
        case 'Espec': // Catalogos/Especialidades
            $busEmpty=getParam("busEmpty");
            $sql="delete from especialidad where espe_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
            $destino = "../gestmed/sisTurnosCatalogosEspecialidad_buscar.php?clear=1&busEmpty=$busEmpty";
            break;

        case 'Medic': // Catalogos/Medicos
            $busEmpty=getParam("busEmpty");
            $sql="delete from medico where medi_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
            $destino = "../gestme/sisTurnosCatalogosMedico_buscar.php?clear=1&busEmpty=$busEmpty";
            break;

        case 'Consult': // Catalogos/Consultorios
            $busEmpty=getParam("busEmpty");
            $sql="delete from consultorio where espe_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
            $destino = "../gestmed/sisTurnosCatalogosConsultorio_buscar.php?clear=1&busEmpty=$busEmpty";
            break;
        
        case 'RecHora': // Horarios
   	    $hora_tipo = getParam("hora_tipo");
	    $sql="delete from horario where hora_id in ('".$lista_elimina."') and usua_id=".getSession("sis_userid");
	    $destino = "../gestmed/sisRecaudacionCatalogosHorarios_lista.php?hora_tipo=$hora_tipo";
	    break;
	/* --------------------------------*/
	//  Recaudacion Centro Medico//
	/* -------------------------------*/
        case 'GrpoServ': // Catalogo/Grupos de Servicios Medicos
               	$tipo = getParam("tipo");
                $sql="delete from servicio_grupo where segr_id in ('".$lista_elimina."') and usua_id=".getSession("sis_userid");
                $destino = "../catalogos/catalogosServiciosGrupos_lista.php?tipo=$tipo";
                break;

        case 'SGrpoServ': //  Tarifas de Servicios Medicos-> lista encadenada
                $sql="DELETE FROM servicio_sgrupo WHERE sesg_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");	   
                $destino = "../catalogos/catalogosServiciosGrupos_sGrupoLista1n.php?id=$relacionamento_id"; 
                break;

        case 'RecServ': // Servicios
                $busEmpty=getParam("busEmpty");
                $tipo=getParam("tipo");
                //$lista_elimina=str_replace(",","','",$lista_elimina); //debido a que el campo calve es char	   
                $sql="delete from servicio where serv_codigo in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
                $destino = "../catalogos/catalogosServicios_buscar.php?clear=1&busEmpty=$busEmpty&tipo=$tipo";
                break;
            
            
        case 'PublicServ': // Limpiar Servicios Publicados
                $busEmpty=getParam("busEmpty");
                $tipo=getParam("tipo");
                //$lista_elimina=str_replace(",","','",$lista_elimina); //debido a que el campo calve es char	   
                $sql="UPDATE catalogos.servicio SET serv_publicar=0 WHERE serv_codigo IN (".$lista_elimina.") ";
                $destino = "../catalogos/catalogosServicios_buscar.php?clear=1&busEmpty=$busEmpty&tipo=$tipo";
                break;
            
         case 'CatVent': // Catalogo/Ventanillas
                   $vent_tipo = getParam("vent_tipo");
                   $sql="delete from ventanilla where vent_id in ('".$lista_elimina."') and usua_id=".getSession("sis_userid");
                   $destino = "../gestmed/sisRecaudacionCatalogosVentanillas_lista.php?vent_tipo=$vent_tipo";
                   break;

        case 'RecCodCaja': // Códigos de Recaudacion
                   $caco_tipo = getParam("caco_tipo");		   
                   $sql="delete from caja_codigo where caco_id in ('".$lista_elimina."') and usua_id=".getSession("sis_userid");
                   $destino = "../gestmed/sisRecaudacionAperturasCodCaja_lista.php?caco_tipo=$caco_tipo";
                   break;            

	case 'HCCOnsultas': // Sescala - Faltas y Tardanzas
		$pg=getParam("pagina");
		$sql="DELETE FROM gestmed.historia_persona_consultas WHERE hpco_id IN (".$lista_elimina.")  AND usua_id=".getSession("sis_userid");
		$destino = "../gestmed/sisAdmisionHistClinica_persona_consultasLista.php?id=$relacionamento_id&pagina=$pg&";
		break;
               
	/* --------------------------------*/
	//  Farmacia //
	/* -------------------------------*/
        case 'FarMedi': // Medicamentos
                $pg = getParam("pagina");	
                $lista_elimina=str_replace(",","','",$lista_elimina); //debido a que el campo calve es char	   
                $sql="delete from catalogo_bienes where cabi_codigo in ('".$lista_elimina."') and usua_id=".getSession("sis_userid");
                $destino = "../farmacia/sisFarmaciaCatalogosMedicamentos_lista.php?pagina=$pg";
                break;

        case 'FarEntr': //Entradas/documentos
                $sql="delete from farmacia_entrada where faen_id in ('".$lista_elimina."') and usua_id=".getSession("sis_userid");
                $destino = "../farmacia/sisFarmaciaMovimientosEntradas_lista.php";
                break;

        case 'FarEntProd': //Entradas/productos
                $sql="delete from farmacia_entrada_movimiento where femo_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
                $destino = "../farmacia/sisFarmaciaMovimientosEntradas_productosLista1n.php?id=$relacionamento_id";
                break;

        case 'LabEntProd': //Entradas/productos
                $sql="delete from laboratorio_entrada_movimiento where lemo_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
                $destino = "../farmacia/sisLaboratorioMovimientosEntradas_productosLista1n.php?id=$relacionamento_id";
                break;

        case 'PedidoAct': // Farmacia/Pedidos Actualizacion
                $lista_elimina=str_replace(",","','",$lista_elimina); //debido a que el campo calve es char
                $id = getParam("f_id"); // captura la variable id del plem_id
                $sql="delete from farmacia_venta_detalle where fvde_id in ('".$lista_elimina."')";
                $destino = "../farmacia/sisFarmaciaMovimientosVentas_actual.php?id=".$id; 
                break;
	/* --------------------------------*/
	//  Recaudaciones //
	/* -------------------------------*/
        case 'servNotImg': // Servicios - imagenes
                $tipo=getParam("xxxtipo");
		$sql="DELETE FROM servicio_imagenes WHERE seim_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../catalogos/catalogosServicios_imagenes.php?relacionamento_id=$relacionamento_id&tipo=$tipo";
		break;
            
        case 'publNotImg': // Servicios - imagenes
		$sql="DELETE FROM siscore.publicar_imagenes WHERE puim_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../siscore/publicarImagenes_lista.php?relacionamento_id=$relacionamento_id";
		break;            
	/* --------------------------------*/
	//  SISCOPP //
	/* -------------------------------*/
	case 'catpptal': // Categoria Presupuestal
		$lista_elimina=str_replace(",","','",$lista_elimina); //debido a que el campo calve es char
		$sql="DELETE FROM siscopp.categoria_presupuestal WHERE capr_id in ('".$lista_elimina."') and usua_id=".getSession("sis_userid");
		$destino = "../siscopp/siscoppCatalogosCategoriaPtal_lista.php";
		break;            
            
	case 'progpptal': // Programa Presupuestal
		$lista_elimina=str_replace(",","','",$lista_elimina); //debido a que el campo calve es char
		$sql="DELETE FROM programa_presupuestal WHERE prpr_id in ('".$lista_elimina."') and usua_id=".getSession("sis_userid");
		$destino = "../siscopp/siscoppCatalogosProgramaPtal_lista.php";
		break;                        
            
	case 'prodproypptal': // Producto/proyecto Presupuestal
		$lista_elimina=str_replace(",","','",$lista_elimina); //debido a que el campo calve es char
		$sql="DELETE FROM productoproyecto_presupuestal where prpr_id in ('".$lista_elimina."') and usua_id=".getSession("sis_userid");
		$destino = "../siscopp/siscoppCatalogosProductoProyectoPtal_lista.php";
		break;    

	case 'actobraccpptal': // Producto/proyecto Presupuestal
		$lista_elimina=str_replace(",","','",$lista_elimina); //debido a que el campo calve es char
		$sql="DELETE FROM actividadobraaccion_presupuestal where acpr_id in ('".$lista_elimina."') and usua_id=".getSession("sis_userid");
		$destino = "../siscopp/siscoppCatalogosActividadObraAccionPtal_lista.php";
		break;
            
	case 'funcionp': // funciones
		$lista_elimina=str_replace(",","','",$lista_elimina); //debido a que el campo calve es char
		$sql="delete from funcion where func_id in ('".$lista_elimina."') and usua_id=".getSession("sis_userid");
		$destino = "../siscopp/siscoppCatalogosFuncion_lista.php";
		break;
            
	case 'divFuncional': // programa presupuestal
		$lista_elimina=str_replace(",","','",$lista_elimina); //debido a que el campo calve es char
		$sql="delete from division where divi_id in ('".$lista_elimina."') and usua_id=".getSession("sis_userid");
		$destino = "../siscopp/siscoppCatalogosDivFuncional_lista.php";
		break;
            
	case 'grupop': // sub programa presupuestal
		$lista_elimina=str_replace(",","','",$lista_elimina); //debido a que el campo calve es char
		$sql="delete from subprograma where subp_id in ('".$lista_elimina."') and usua_id=".getSession("sis_userid");
		$destino = "../siscopp/siscoppCatalogosGrpFuncional_lista.php";
		break;         
            
	case 'componentep': // Componentes presupuestales
		$lista_elimina=str_replace(",","','",$lista_elimina); //debido a que el campo calve es char
		$sql="delete from componente where comp_id in ('".$lista_elimina."') and usua_id=".getSession("sis_userid");
		$destino = "../siscopp/siscoppAperturasComponentes_lista.php";
		break;
            
	case 'fueningre': // Fuentes de Ingreso
		$lista_elimina=str_replace(",","','",$lista_elimina); //debido a que el campo clave es char
		$sql="delete from fuente_ingreso where fuin_id in ('".$lista_elimina."') and usua_id=".getSession("sis_userid");
		$destino = "../siscopp/siscoppCatalogosFuenIngre_lista.php";
		break;
            
	case 'CodAcum': // C�digos de Acumulados presupuestales
		$tipoAcumulado=getParam("tip_acu");
		$sql="delete from acumulado_presupuestal where acpr_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../siscopp/siscoppAperturasAcumulados_lista.php?tip_acu=$tipoAcumulado";
		break;

	case 'AcumComp': // Acumulados presupuestales/Componentes
		$tipoAcumulado=getParam("tip_acu");
		$id = getParam("f_id"); // captura la variable id de la plaza
		$sql="delete from acumulado_presupuestal_componente where apco_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../siscopp/siscoppAperturasAcumulados_componentes.php?id=$id&tip_acu=$tipoAcumulado";
		break;

	case 'AcumCompPart': // Acumulados presupuestales/Componentes/Partidas
		$tipoAcumulado=getParam("tip_acu");
		$apco_id=getParam("apco_id");
		$id = getParam("f_id"); // captura la variable id de la plaza
		$sql="delete from acumulado_presupuestal_partida where appa_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../siscopp/siscoppAperturasAcumulados_partidas.php?id=$id&tip_acu=$tipoAcumulado&apco_id=$apco_id";
		break;            

	case 'AcumComp': // Acumulados presupuestales/Componentes
		$tipoAcumulado=getParam("tip_acu");
		$id = getParam("f_id"); // captura la variable id de la plaza
		$sql="delete from acumulado_presupuestal_componente where apco_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../siscopp/siscoppAperturasAcumulados_componentes.php?id=$id&tip_acu=$tipoAcumulado";
		break;
            
	case 'AcumCompPart': // Acumulados presupuestales/Componentes/Partidas
		$tipoAcumulado=getParam("tip_acu");
		$apco_id=getParam("apco_id");
		$id = getParam("f_id"); // captura la variable id de la plaza
		$sql="delete from acumulado_presupuestal_partida where appa_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../siscopp/siscoppAperturasAcumulados_partidas.php?id=$id&tip_acu=$tipoAcumulado&apco_id=$apco_id";
		break;
            
	case 'tipCtaCont': // Plan Contable - Tipos
		$sql="DELETE FROM siscont.plan_contable_tipo WHERE pcti_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../siscont/siscontCatalogosTiposCuentasContables_lista.php";
		break;

	case 'clasCtaCont': // Plan Contable - Clases de cuentas
		$id = getParam("f_id"); // captura la variable id de la plaza
		$sql="delete FROM  siscont.plan_contable_clase WHERE pccl_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../siscont/siscontCatalogosTiposCuentasContables_claseCuenta.php?id=$id";
		break;

	case 'elemCtaCont': // Plan Contable - Elementos
		$pccl_id=getParam("pccl_id");
		$id = getParam("f_id"); // captura la variable id de la plaza
		$sql="delete FROM siscont.plan_contable_elemento WHERE pcel_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../siscont/siscontCatalogosTiposCuentasContables_elementos.php?id=$id&pccl_id=$pccl_id";
		break;
            
	/* --------------------------------*/
	//  Recaudaciones-Adjuntados //
	/* -------------------------------*/
        case 'agrAdjSol': // Servicios - imagenes
		$sql="DELETE FROM pedidos_bbss_adjuntos WHERE pbad_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
		$destino = "../solicitudes/movimientosSolicitud_edicionAdjunto.php?relacionamento_id=$relacionamento_id";
		break;

	/* --------------------------------*/
	//  Servicio_producto //
	/* -------------------------------*/
        case 'ServEntProd': //Entradas/productos
                $sql="delete from siscore.servicio_entrada_movimiento where semo_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
                $destino = "../siscore/siscoreMovimientosEntradas_productosLista1n.php?id=$relacionamento_id";
                break;

	/* --------------------------------*/
	//  Gesdoc-Adjuntados //
	/* -------------------------------*/
        case 'elimAdjDesp': // Servicios - imagenes
                $clear=getParam("clear"); //valor del campo clave
		$sql="DELETE FROM gestdoc.despachos_adjuntados 
                         WHERE dead_id = $lista_elimina 
                            AND usua_id=".getSession("sis_userid");
                
                $destino = "../gestdoc/registroDespacho_edicionAdjuntos.php?relacionamento_id=$relacionamento_id&clear=$clear";
		break;

	/* --------------------------------*/
	// Laboratorio //
	/* -------------------------------*/
            
    case 'Perfil': // Perfil de examen auxiliar
 	   $busEmpty=getParam("busEmpty");
           $tipo_estudio=getParam("tipo_estudio");
           $espe_id=getParam("espe_id");
	   $sql="DELETE FROM examen WHERE exam_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");
	   $destino = "../laboratorio/sisEstudiosCatalogosPerfil_buscar.php?clear=1&busEmpty=$busEmpty&tipo_estudio=$tipo_estudio&espe_id=$espe_id";
	   break;       
       
	/* --------------------------------*/
	// Atención Médica //
	/* -------------------------------*/

   case 'AteMedEst': //  atencion_medica-> Lista de Estudios
	   $hipe_id=getParam("hipe_id");
	   $clear=getParam("clear");
	   $sql="DELETE FROM atencion_medica_estudios WHERE ames_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");	   
	   $destino =  "../gestmed/sisAtenMedAtenCitaConsulta_estudiosLista.php?id=$relacionamento_id&hipe_id=$hipe_id&clear=$clear";
	   break;

   case 'AteMedTrat': //  atencion_medica-> Tratamiento
	   $hipe_id=getParam("hipe_id");
	   $clear=getParam("clear");
	   $sql="DELETE FROM atencion_medica_tratamiento WHERE amtr_id in (".$lista_elimina.") and usua_id=".getSession("sis_userid");	   
	   $destino =  "../gestmed/sisAtenMedAtenCitaConsulta_tratamientoLista.php?id=$relacionamento_id&hipe_id=$hipe_id&clear=$clear";
	   break;        
       
   case 'AteMedEstArch': // Servicios - imagenes
           $clear=getParam("clear"); //valor del campo clave
           $hipe_id=getParam("hipe_id");
           $ames_id=getParam("ames_id");
           $relacionamento_id=getParam("relacionamento_id");
           $sql="DELETE FROM gestmed.atencion_medica_estudios_archivo 
                 WHERE amea_id = $lista_elimina 
                    AND usua_id=".getSession("sis_userid");

           $destino = "../gestmed/sisAtenMedAtenCitaConsulta_estudiosEdicion.php?id=$ames_id&relacionamento_id=$relacionamento_id&hipe_id=$hipe_id&clear=$clear";
           break;

    case 'AteMedHCEstArch': // Servicios - imagenes
           $hipe_id=getParam("hipe_id");
           $sql="DELETE FROM gestmed.historia_estudios_archivo 
                 WHERE hear_id = $lista_elimina 
                    AND usua_id=".getSession("sis_userid");
           $destino = "../gestmed/sisAtenMedExamenesAuxiliares_lista.php?relacionamento_id=$hipe_id&clear=5";
           break;       
}

//	conexión a la BD
$conn = new db();
$conn->open();
if (!$erro->hasErro()) { // verifico si pasa la validacion
	if (strlen($lista_elimina)==0) { // se no existen registros seleccionados
		alert("Ning\u00fan registro seleccionado!");
	} else { // se existe registro seleccionados
            
                switch($op){
                        case 'elimAdjDesp': // Servicios - imagenes
                            $desp_id=getDbValue("SELECT desp_id
                                                        FROM gestdoc.despachos_adjuntados 
                                                            WHERE dead_id = $lista_elimina ");
                            
                            $periodo=getDbValue("SELECT desp_anno AS periodo,
                                                        FROM gestdoc.despachos 
                                                            WHERE desp_id = $desp_id ");
                            
                            $nombreFile=getDbValue("SELECT area_adjunto
                                                        FROM gestdoc.despachos_adjuntados 
                                                            WHERE dead_id = $lista_elimina ");

                            break;
                    
                        case 'AteMedEstArch':
                            $ames_id=getDbValue("SELECT ames_id
                                                        FROM gestmed.atencion_medica_estudios_archivo 
                                                            WHERE amea_id = $lista_elimina ");
                            
                            $periodo=getDbValue("SELECT EXTRACT('YEAR' FROM ames_fecha) AS periodo 
                                                        FROM gestmed.atencion_medica_estudios 
                                                            WHERE ames_id = $ames_id ");
                            
                            $nombreFile=getDbValue("SELECT area_archivo
                                                        FROM gestmed.atencion_medica_estudios_archivo 
                                                            WHERE amea_id = $lista_elimina ");

                            break;                            
                }            
                
		$conn->execute($sql);
		$error=$conn->error();
		if($error) alert($error);
		else{            
                        switch($op){
                                case 'elimAdjDesp': // Servicios - imagenes
                                       $filePath=PUBLICUPLOAD."gestdoc/".SIS_EMPRESA_RUC."/$periodo/".$desp_id."/".$nombreFile;
                                       if (is_file($filePath)) {
                                            unlink($filePath);
                                       }
                                break;
                                
                                case 'AteMedEstArch': // Servicios - imagenes
                                       $filePath=PUBLICUPLOAD."gestmed/".SIS_EMPRESA_RUC."/$periodo/".$ames_id."/".$nombreFile;
                                       if (is_file($filePath)) {
                                            unlink($filePath);
                                       }
                                break;
                        }            
                    
                    redirect($destino,"content");
                    if($linkAux) redirect($linkAux,$destAux);
		}
	}

} else { // si no pasa la validaci�n
	alert('Mensajes de errores!\n\n'.$erro->toString());
	redirect($destino,"content");
	if($linkAux) redirect($linkAux,$destAux);
}
//	cierra la conexi�n con la BD
$conn->close();