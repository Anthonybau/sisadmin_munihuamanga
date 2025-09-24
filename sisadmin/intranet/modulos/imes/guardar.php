<?php
/*	archivo comun/modificaci�n de registros */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/*	verificaci�n a nivel de usuario */
verificaUsuario(1);
verif_framework();

/* variable que se recibe de la opcion para datos de edicion */
$op = getParam("_op");
$op2= getParam("_op2");
$relacionamento_id = getParam("relacionamento_id");

//	conexion a la BD 
$conn = new db();
$conn->open();

$erro = new Erro();

if(!$op) $erro->addErro('No envio parametro de proceso.');   

switch($op){
   	/* -------------------------------*/
        //Organización del Menu Intranet  //
	/* ------------------------------*/
    case 'OrgMenuComp': // Organización de Menu/Componentes
        $setTable='sistema_modulo'; //nombre de la tabla
        $setKey='simo_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $typeKey="String"; //tipo  de dato del campo clave
        $saveUser=true;
        $destinoUpdate = "../admin/adminOrganizacionMenu_lista.php";
        $destinoInsert = "../admin/adminOrganizacionMenu_lista.php";
        break;

    case 'OrgMenuElem': // Organización de Menu/Componentes
        $setTable='sistema_modulo_opciones'; //nombre de la tabla
        $setKey='smop_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $typeKey="String"; //tipo  de dato del campo clave
        $saveUser=true;
        $id=getParam("id"); //valor del campo clave
        $destinoUpdate = "../admin/adminOrganizacionMenu_elementos.php?_id=$id";
        $destinoInsert = "../admin/adminOrganizacionMenu_elementos.php?_id=$id";
        break;

   	/* -------------------------------*/
        //   Administración de Portales   //
	/* ------------------------------*/

    case 'PortEntidad': // Registros - Institucional
        $setTable='dependencia'; //nombre de la tabla
        $setKey='depe_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;
        $destinoUpdate = "../portal/sisadportEntidad_edicion.php?nbusc_depe_id=".$valueKey;
        $destinoInsert = "../portal/sisadportEntidad_edicion.php";
        break;


    case 'PortalTipoMenu': // Portal / Estructuras / Menú
        $setTable='portal_menu'; //nombre de la tabla
        $setKey='pome_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;
        $destinoUpdate = "../portal/sisadportEstructurasMenuOp_lista.php";
        $destinoInsert = "../portal/sisadportEstructurasMenuOp_lista.php";
        break;

    case 'PortNoti': // Portal / Contenidos / Noticias
        $setTable='portal_noticia'; //nombre de la tabla
        $setKey='pono_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;
        $destinoUpdate = "../portal/sisadportContenidosNoticia_lista.php";
        $destinoInsert = "../portal/sisadportContenidosNoticia_edicion.php";
        break;

    case 'agrNotImg': // Portal / Estructuras / Noticias
        $setTable='portal_noticia_fotos'; //nombre de la tabla
        $setKey='pnfo_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $pono_id=getParam("___pono_id");
        $typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;
        $destinoUpdate = "../portal/sisadportContenidosNoticia_edicion.php?_id=".$pono_id;
        $destinoInsert = "../portal/sisadportContenidosNoticia_edicion.php?_id=".$pono_id;
        break;

    case 'PortEnc': // Portal / Contenidos / Encuestas
        $setTable='portal_encuesta'; //nombre de la tabla
        $setKey='poen_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;
        $destinoUpdate = "../portal/sisadportContenidosEncuesta_lista.php";
        $destinoInsert = "../portal/sisadportContenidosEncuesta_lista.php";
        break;

    case 'PortAccInf': // Portal / Contenidos / Encuestas
        $setTable='portal_acc_inf'; //nombre de la tabla
        $setKey='poai_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $busEmpty=getParam("busEmpty"); //muestra todos los registro ;
        $sigla=getParam("sigla"); //SIGLAS;
        $docBreve=getParam("docBreve"); //DOCUMENTO BREVE
        $tinfo=getParam("___poai_tipoinformacion"); //numero de formulario;
        $smop_id = getParam("smop_id"); // Id de la opción del menú, para poder obtener en las consultas la OPCION y el GRUPO al que pertenece.
        $typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;
        $destinoUpdate = "../portal/sisadportAccesoInformacion_buscar.php?busEmpty=$busEmpty&sigla=$sigla&docBreve=$docBreve&smop_id=$smop_id&tinfo=$tinfo";
        $destinoInsert = "../portal/sisadportAccesoInformacion_buscar.php?busEmpty=$busEmpty&sigla=$sigla&docBreve=$docBreve&smop_id=$smop_id&tinfo=$tinfo";
        break;

    case 'PortBanner': // Portal / Estructuras / Banners
        $setTable='portal_banner'; //nombre de la tabla
        $setKey='poba_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;
        $destinoUpdate = "../portal/sisadportEstructurasBanner_lista.php";
        $destinoInsert = "../portal/sisadportEstructurasBanner_lista.php";
        break;

    case 'PortalContenido': // Portal / Contenidos / Contenido
        $setTable='portal_contenido'; //nombre de la tabla
        $setKey='poco_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;
        $destinoUpdate = "../portal/sisadportContenidosTema_lista.php";
        $destinoInsert = "../portal/sisadportContenidosTema_lista.php";
        break;

    case 'PortEmergente': // Portal / Estructuras / Banners
        $setTable='portal_emergentes'; //nombre de la tabla
        $setKey='poem_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;
        $destinoUpdate = "../portal/sisadportEstructurasEmergente_lista.php";
        $destinoInsert = "../portal/sisadportEstructurasEmergente_lista.php";
        break;

    case 'solDonacion': // Portal / Estructuras / Banners
        $setTable='portal_donacion'; //nombre de la tabla
        $setKey='podo_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;
        $destinoUpdate = "../portal/sisadportEstructurasSolicitaDonaciones_lista.php";
        $destinoInsert = "../portal/sisadportEstructurasSolicitaDonaciones_edicion.php";
        break;

    case 'agrDonImg': // Portal / Estructuras / Donaciones
        $setTable='portal_donacion_fotos'; //nombre de la tabla
        $setKey='pdfo_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $podo_id=getParam("___podo_id");
        $typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;
        $destinoUpdate = "../portal/sisadportEstructurasSolicitaDonaciones_edicion.php?_id=".$podo_id;
        $destinoInsert = "../portal/sisadportEstructurasSolicitaDonaciones_edicion.php?_id=".$podo_id;
        break;


    case 'portInfraestruct': // Portal / Estructuras / infraestructura
        $setTable='portal_infraestructura'; //nombre de la tabla
        $setKey='poin_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;
        $smop_id=getParam("smop_id");
        $tinfra=getParam("tinfra");
        $destinoUpdate = "../portal/sisadportInfraestructura_lista.php?smop_id=$smop_id&tinfra=$tinfra";
        $destinoInsert = "../portal/sisadportInfraestructura_edicion.php?smop_id=$smop_id&tinfra=$tinfra";
        break;

    case 'agrInfraesImg': // Portal / Estructuras / Banners
        $setTable='portal_infraestructura_fotos'; //nombre de la tabla
        $setKey='pifo_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $poin_id=getParam("___poin_id");
        $typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;
        $smop_id=getParam("smop_id");
        $tinfra=getParam("tinfra");

        $destinoUpdate = "../portal/sisadportInfraestructura_edicion.php?_id=".$poin_id."&smop_id=$smop_id&tinfra=$tinfra";
        $destinoInsert = "../portal/sisadportInfraestructura_edicion.php?_id=".$poin_id."&smop_id=$smop_id&tinfra=$tinfra";
        break;

    case 'CatTabla': // Catalogos/paramtros
        $setTable='tabla'; //nombre de la tabla
        $setKey='tabl_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $busEmpty=getParam("busEmpty"); //muestra todos los registro ;
        $dbrev=getParam("dbrev"); //muestra o no el campo breve
        $setCodigo = getParam("setCodigo");
        
        $numForm=getParam("numForm"); //numero de formulario;
        $colOrden=getParam("colOrden"); //Columna a ordenar
        $typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;
        $destinoUpdate = "../catalogos/catalogosTablas_buscar.php?busEmpty=$busEmpty&colOrden=$colOrden&dbrev=$dbrev&setCodigo=$setCodigo";
        $destinoInsert = "../catalogos/catalogosTablas_buscar.php?busEmpty=$busEmpty&colOrden=$colOrden&dbrev=$dbrev&setCodigo=$setCodigo";
        if(getParam("nomeCampoForm")) {
        //$return_val-> configura el valor a retornar
        //$return_txt-> configura el texto a retornar
            $return_val='$last_id';//OJO CON LAS COMILLAS, tienen q ser simples
            $return_txt='$_POST[Sr_tabl_descripcion]'; //OJO CON LAS COMILLAS, tienen q ser simples
            $destinoInsert="return";
        }
        break;


    case 'agrDepenImg': // Portal / Estructuras / Datos de la entidad
        $setTable='dependencia_fotos'; //nombre de la tabla
        $setKey='defo_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $depe_id=getParam("___depe_id");
        $typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;

        $destinoUpdate = "../portal/sisadportEntidad_edicion.php?_id=".$depe_id;
        $destinoInsert = "../portal/sisadportEntidad_edicion.php?_id=".$depe_id;
        break;

    case 'mueveDependencia': // 
        $setTable='persona_desplazamiento'; //nombre de la tabla
        $setKey='pede_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;
        $destinoUpdate = "close";
        $destinoInsert = "close";
        break;
    
	/* --------------------------------*/
	// Gestion de Turnos //
	/* -------------------------------*/
   case 'Espec': //Catalogos:Especialidades
		$setTable='especialidad'; //nombre de la tabla
		$setKey='espe_id'; //campo clave
		$valueKey=getParam("f_id"); //valor del campo clave
		$busEmpty=getParam("busEmpty"); //muestra todos los registro ;
		$numForm=getParam("numForm"); //numero de formulario;
		$typeKey="Number"; //tipo  de dato del campo clave
		$saveUser=true;
		$destinoUpdate = "../gestmed/sisTurnosCatalogosEspecialidad_buscar.php?busEmpty=$busEmpty";
		$destinoInsert = "../gestmed/sisTurnosCatalogosEspecialidad_buscar.php?busEmpty=$busEmpty";
		if(getParam("nomeCampoForm")){
			//$return_val-> configura el valor a retornar
			//$return_txt-> configura el texto a retornar
			$return_val='$last_id';//OJO CON LAS COMILLAS, tienen q ser simples
			$return_txt='$_POST[Sr_espe_descripcion]'; //OJO CON LAS COMILLAS, tienen q ser simples
			$destinoInsert="return";
			}

		break;

	case 'Consult': //Catalogos:Consultorio
		$setTable='consultorio'; //nombre de la tabla
		$setKey='cons_id'; //campo clave
		$valueKey=getParam("f_id"); //valor del campo clave
		$busEmpty=getParam("busEmpty"); //muestra todos los registro ;
		$numForm=getParam("numForm"); //numero de formulario;
		$typeKey="Number"; //tipo  de dato del campo clave
		$saveUser=true;
		$destinoUpdate = "../gestmed/sisTurnosCatalogosConsultorio_buscar.php?busEmpty=$busEmpty";
		$destinoInsert = "../gestmed/sisTurnosCatalogosConsultorio_buscar.php?busEmpty=$busEmpty";
		if(getParam("nomeCampoForm")){
			//$return_val-> configura el valor a retornar
			//$return_txt-> configura el texto a retornar
			$return_val='$last_id';//OJO CON LAS COMILLAS, tienen q ser simples
			$return_txt='$_POST[Sr_cons_descripcion]'; //OJO CON LAS COMILLAS, tienen q ser simples
			$destinoInsert="return";
			}

		break;


	case 'Medic': //Catalogos:Medico
		$setTable='medico'; //nombre de la tabla
		$setKey='medi_id'; //campo clave
		$valueKey=getParam("f_id"); //valor del campo clave
		$busEmpty=getParam("busEmpty"); //muestra todos los registro ;
		$numForm=getParam("numForm"); //numero de formulario;
		$typeKey="Number"; //tipo  de dato del campo clave
		$saveUser=true;
		$destinoUpdate = "../gestmed/sisTurnosCatalogosMedico_buscar.php?busEmpty=$busEmpty";
		$destinoInsert = "../gestmed/sisTurnosCatalogosMedico_especialidad.php?clear=1&busEmpty=$busEmpty";
		if(getParam("nomeCampoForm")){
			//$return_val-> configura el valor a retornar
			//$return_txt-> configura el texto a retornar
			$return_val='$last_id';//OJO CON LAS COMILLAS, tienen q ser simples
			$return_txt='$_POST[Sr_medi_apellidos].\' \'.$_POST[Sr_medi_nombres]'; //OJO CON LAS COMILLAS, tienen q ser simples

			$destinoInsert="return";
			}
                /*creo la carpeta para la imagen de la firma del medico*/
                $location=$_SERVER['DOCUMENT_ROOT'] ."/docs/gestmed/".SIS_EMPRESA_RUC;
                if (!is_dir($location) && !mkdir($location, '0755', true)) {
                } 
		break;
        case 'RecHora': //Horario
                        $setTable='horario'; //nombre de la tabla
                        $setKey='hora_id'; //campo clave
                        $valueKey=getParam("f_id"); //valor del campo clave
                        $hora_tipo = getParam("hora_tipo");		
                        $typeKey="Number"; //tipo  de dato del campo clave
                        $saveUser=true;
                        $destinoUpdate = "../gestmed/sisRecaudacionCatalogosHorarios_lista.php?hora_tipo=$hora_tipo";
                        $destinoInsert = "../gestmed/sisRecaudacionCatalogosHorarios_lista.php?hora_tipo=$hora_tipo";
                        break;
    
	/* --------------------------------*/
	// Recaudaciones Centro Médico//
	/* -------------------------------*/
        case 'GrpoServ': // Catalogo/Grupos de Serfvicios Medicos  
                        $setTable='servicio_grupo'; //nombre de la tabla
                        $setKey='segr_id'; //campo clave
                        $valueKey=getParam("f_id"); //valor del campo clave
                        $tipo=getParam("tipo"); 
                        $typeKey="String"; //tipo  de dato del campo clave
                        $saveUser=true;
                        $destinoUpdate = "../catalogos/catalogosServiciosGrupos_lista.php?tipo=$tipo";
                        $destinoInsert = "../catalogos/catalogosServiciosGrupos_edicion.php?tipo=$tipo";
                        break;

        case 'SGrpoServ': // Catalogo/Grupos de Serfvicios Medicos/Sub Grupos
                        $setTable='servicio_sgrupo'; //nombre de la tabla
                        $setKey='sesg_id'; //campo clave
                        $valueKey=getParam("f_id"); //valor del campo clave
                        $typeKey="Number"; //tipo  de dato del campo clave
                        $saveUser=true;		
                        $destinoUpdate = "close";
                        $destinoInsert = "close";
                        break;


        case 'RecServ': //Recaudaciones/Servicios
                        $setTable='servicio'; //nombre de la tabla
                        $setKey='serv_codigo'; //campo clave
                        $valueKey=getParam("f_id"); //valor del campo clave
                        $busEmpty=getParam("busEmpty"); //muestra todos los registro ;
                        $numForm=getParam("numForm"); //numero de formulario;
                        $clear=getParam("clear");
                        $tipo=getParam("tipo");
                        $nBusc_grupo_id=getParam("nBusc_grupo_id");
                        $typeKey="String"; //tipo  de dato del campo clave
                        $saveUser=true;
                        $last_id=getParam("zr_serv_codigo");		
                        
                        //if(SIS_SISCORE_SISCONT==1){
                            $contador=2;
//                        }else{
//                            $contador=getDbValue("SELECT COUNT(*) FROM catalogos.tabla WHERE tabl_tipo='TIPO_PRECIO'");
//                        }
                        
                        if($contador>1){
                            $destinoUpdate = "../catalogos/catalogosServicios_edicion.php?id=$valueKey&busEmpty=$busEmpty&clear=$clear&nBusc_grupo_id=$nBusc_grupo_id&tipo=$tipo";
                            $destinoInsert = "../catalogos/catalogosServicios_edicion.php?id=$valueKey&busEmpty=$busEmpty&clear=$clear&nBusc_grupo_id=$nBusc_grupo_id&tipo=$tipo";                            
                        }else{
                            $destinoUpdate = "../catalogos/catalogosServicios_buscar.php?busEmpty=$busEmpty&clear=$clear&nBusc_grupo_id=$nBusc_grupo_id&tipo=$tipo";
                            $destinoInsert = "../catalogos/catalogosServicios_buscar.php?busEmpty=$busEmpty&clear=$clear&nBusc_grupo_id=$nBusc_grupo_id&tipo=$tipo";
                        }
                        if(getParam("nomeCampoForm")){
                                //$return_val-> configura el valor a retornar
                                //$return_txt-> configura el texto a retornar
                                $return_val='$last_id';//OJO CON LAS COMILLAS, tienen q ser simples
                                $return_txt='$_POST[Sr_serv_descripcion]'; //OJO CON LAS COMILLAS, tienen q ser simples
                                $destinoInsert="return";
                                }

                        break;
                        
          case 'CatVent': // Catalogo/Ventanillas
                        $setTable='ventanilla'; //nombre de la tabla
                        $setKey='vent_id'; //campo clave
                        $valueKey=getParam("f_id"); //valor del campo clave
                        $vent_tipo = getParam("vent_tipo");
                        $typeKey="String"; //tipo  de dato del campo clave
                        $saveUser=true;
                        $destinoUpdate = "../gestmed/sisRecaudacionCatalogosVentanillas_lista.php?vent_tipo=$vent_tipo";
                        $destinoInsert = "../gestmed/sisRecaudacionCatalogosVentanillas_lista.php?vent_tipo=$vent_tipo";
                        break;


           case 'RecCodCaja': // Códigos de Recaudacion
                        $setTable='caja_codigo'; //nombre de la tabla
                        $setKey='caco_id'; //campo clave
                        $valueKey=getParam("f_id"); //valor del campo clave
                        $caco_tipo = getParam("caco_tipo");		
                        $typeKey="Number"; //tipo  de dato del campo clave
                        $saveUser=false;
                        $destinoUpdate = "../gestmed/sisRecaudacionAperturasCodCaja_lista.php?caco_tipo=$caco_tipo";
                        $destinoInsert = "../gestmed/sisRecaudacionAperturasCodCaja_lista.php?caco_tipo=$caco_tipo";
                        break;                        
                    
           case 'RecIng': // Recaudacion centro medico/ingresos 
                    $setTable='caja_ingreso'; //nombre de la tabla
                    $setKey='cain_id'; //campo clave
                    $valueKey=getParam("f_id"); //valor del campo clave
                    $typeKey="Number"; //tipo  de dato del campo clave
                    $destinoUpdate = "../gestmed/sisRecaudacionCajaIngresos_lista.php"; 
                    break;

           case 'RecTesoIng': // Recaudacion tesoreria/ingresos 
                    $setTable='recaudaciones'; //nombre de la tabla
                    $setKey='reca_id'; //campo clave
                    $valueKey=getParam("f_id"); //valor del campo clave
                    $typeKey="Number"; //tipo  de dato del campo clave
                    $caco_tipo = getParam("caco_tipo");
                    $tipo_ingreso= getParam("tipo_ingreso");
                    $nbusc_reca_numero=getParam("nbusc_reca_numero");
                    $serie_numero=getParam("sbusc_serie_numero");
                    $destinoUpdate = "../siscore/siscoreCajaIngresos_lista.php?caco_tipo=$caco_tipo&tipo_ingreso=$tipo_ingreso&sbusc_serie_numero=$serie_numero&nbusc_reca_numero=$nbusc_reca_numero";
                    break;

           case 'ContCredito': // Aperturas/Contratos
                    $setTable='contrato_credito'; //nombre de la tabla
                    $setKey='cocr_id'; //campo clave
                    $valueKey=getParam("f_id"); //valor del campo clave
                    $typeKey="Number"; //tipo  de dato del campo clave
                    $destinoUpdate = "../siscore/contratoCredito_lista.php"; 
                    break;
                
                
           case 'ContPrestamo': // Aperturas/Contratos
                    $setTable='contrato_credito'; //nombre de la tabla
                    $setKey='cocr_id'; //campo clave
                    $valueKey=getParam("f_id"); //valor del campo clave
                    $typeKey="Number"; //tipo  de dato del campo clave
                    $destinoUpdate = "../siscore/contratoPrestamo_buscar.php?clear=1"; 
                    break;                    
	/* --------------------------------*/
	// Admisión //
	/* -------------------------------*/

     case 'HCPersona': // Historia Clinica:Datos Personales
		$setTable='historia_persona'; //nombre de la tabla
		$setKey='hipe_id'; //campo clave
		$valueKey=getParam("f_id"); //valor del campo clave
		$numForm=0;
                $pg=getParam("pg");
		$typeKey="Number"; //tipo  de dato del campo clave
		$saveUser=true;
		$destinoUpdate = "../gestmed/sisAdmisionHistClinica_persona.php?id=$valueKey&clear=1&pg=$pg";
		$destinoInsert = "../gestmed/sisAdmisionHistClinica_buscar.php?insert=1";

		//las siguientes lineas se utilizan en una ventana de busqueda avanzada (janela auxiliar)
		//haran regresar el control a la pagina de donde fue llamada, devolviendo los valores
		if(getParam("nomeCampoForm")){
			//$return_val-> configura el valor a retornar
			//$return_txt-> configura el texto a retornar
			$return_val='hipe_nhistoria';//EN ESTE CASO no devuelve nada (deberia devolver el numero de historia, pero cuando es nuevo el numero de historia aun no se genera), 
						   //ESTE CASO SE UTILIZA cuando por ejemplo se trabaja con un id de empresa + ruc de empresa, se busca por ruc pero se devuelve el id en el campo extra
			$return_extra='str_pad($last_id,8,0,STR_PAD_LEFT)';//OJO CON LAS COMILLAS, tienen q ser simples
			$return_txt='$_POST[Sr_hipe_apelpaterno].\' \'.$_POST[Sr_hipe_apelmaterno].\' \'.$_POST[Sr_hipe_nombres]'; //OJO CON LAS COMILLAS, tienen q ser simples
			$destinoInsert="return";
		}
		break;

    case 'AntGinObs1': // Historia Clinica:Antecedentes Gineco-Obstetricos 1
		$setTable='historia_antecedente_ginecobst'; //nombre de la tabla
		$setKey='hagi_id'; //campo clave
		$valueKey=getParam("f_id"); //valor del campo clave
		$last_id=$relacionamento_id ;//campo clave de la tabla padre
		$typeKey="Number"; //tipo  de dato del campo clave
		$saveUser=true;
		$clear=getParam("clear");
		$destinoUpdate = "../gestmed/sisAdmisionHistClinica_antGinecoObst.php?id=$last_id&clear=$clear";
		$destinoInsert = "../gestmed/sisAdmisionHistClinica_antGinecoObst.php?id=$last_id&clear=$clear";
		break;

   case 'AntGinObs2': // Historia Clinica:Antecedentes Gineco-Obstetricos 1
		$setTable='historia_antecedente_ginecobsta'; //nombre de la tabla
		$setKey='haga_id'; //campo clave
		$valueKey=getParam("f_id"); //valor del campo clave
		$last_id=$relacionamento_id ;//campo clave de la tabla padre
		$typeKey="Number"; //tipo  de dato del campo clave
		$saveUser=true;
		$clear=getParam("clear");
		$destinoInsert = "../gestmed/sisAdmisionHistClinica_antGinecoObst.php?id=$last_id&clear=$clear";
		break;

   case 'AntGinObs4': // Historia Clinica:Antecedentes Gineco-Obstetricos 1
		$setTable='historia_antecedente_ginecobstb'; //nombre de la tabla
		$setKey='hagb_id'; //campo clave
		$valueKey=getParam("f_id"); //valor del campo clave
		$last_id=$relacionamento_id ;//campo clave de la tabla padre
		$typeKey="Number"; //tipo  de dato del campo clave
		$saveUser=true;
		$clear=getParam("clear");
		$destinoInsert = "../gestmed/sisAdmisionHistClinica_antGinecoObst.php?id=$last_id&clear=$clear";
		break;

   case 'HCAntPersona': // Historia Clinica:Antecedentes Personales
		$setTable='historia_antecedente_personal'; //nombre de la tabla
		$setKey='hape_id'; //campo clave
		$last_id=$relacionamento_id ;//campo clave de la tabla padre
		$typeKey="Number"; //tipo  de dato del campo clave
		$saveUser=true;
		$clear=getParam("clear");		
		$destinoInsert = "../gestmed/sisAdmisionHistClinica_antPersonal.php?clear=$clear";
		break;

   case 'HCAntFamiliar': // Historia Clinica:Antecedentes Familiares
		$setTable='historia_antecedente_familiar'; //nombre de la tabla
		$setKey='hafa_id'; //campo clave
		$last_id=$relacionamento_id ;//campo clave de la tabla padre
		$typeKey="Number"; //tipo  de dato del campo clave
		$saveUser=true;
		$clear=getParam("clear");		
		$destinoInsert = "../gestmed/sisAdmisionHistClinica_antFamiliar.php?clear=$clear";
		break;

   case 'HCdatHistorico': // Historia Clinica:datos Historicos
		$setTable='historia_historico'; //nombre de la tabla
		$setKey='hihi_id'; //campo clave
		$valueKey=getParam("f_id"); //valor del campo clave
		$last_id=$relacionamento_id ;//campo clave de la tabla padre
		$typeKey="Number"; //tipo  de dato del campo clave
		$saveUser=true;
		$clear=getParam("clear");		
		$destinoUpdate = "../gestmed/sisAdmisionHistClinica_historico.php?id=$last_id&clear=$clear";
		$destinoInsert = "../gestmed/sisAdmisionHistClinica_historico.php?clear=$clear";
		break;
		
   case 'HCdatLaboral': // Historia Clinica:dato laboral
		$setTable='historia_dato_laboral'; //nombre de la tabla
		$setKey='hidl_id'; //campo clave
		$valueKey=getParam("f_id"); //valor del campo clave
		$last_id=$relacionamento_id ;//campo clave de la tabla padre
		$typeKey="Number"; //tipo  de dato del campo clave
		$saveUser=true;
		$clear=getParam("clear");		
		$destinoUpdate = "../gestmed/sisAdmisionHistClinica_datLaboral.php?id=$last_id&clear=$clear";
		$destinoInsert = "../gestmed/sisAdmisionHistClinica_datLaboral.php?clear=$clear";
		break;

   case 'HCdatLaboralRgo': // Historia Clinica:dato laboral riesgo
		$setTable='historia_dato_laboral_riesgo'; //nombre de la tabla
		$setKey='hdlr_id'; //campo clave
		$valueKey=getParam("f_id"); //valor del campo clave
		$last_id=$relacionamento_id ;//campo clave de la tabla padre
		$typeKey="Number"; //tipo  de dato del campo clave
		$saveUser=true;
		$clear=getParam("clear");
		$destinoInsert = "../gestmed/sisAdmisionHistClinica_datLaboral.php?clear=$clear";
		break;

   case 'HChabito': // Historia Clinica:Hábitos
		$setTable='historia_habito'; //nombre de la tabla
		$setKey='hiha_id'; //campo clave
		$valueKey=getParam("f_id"); //valor del campo clave
		$last_id=$relacionamento_id ;//campo clave de la tabla padre
		$typeKey="Number"; //tipo  de dato del campo clave
		$saveUser=true;
		$clear=getParam("clear");		
		$destinoInsert = "../gestmed/sisAdmisionHistClinica_habitos.php?clear=$clear";
		break;

   case 'HCalergia': // Historia Clinica:Hábitos
		$setTable='historia_alergia'; //nombre de la tabla
		$setKey='hial_id'; //campo clave
		$valueKey=getParam("f_id"); //valor del campo clave
		$last_id=$relacionamento_id ;//campo clave de la tabla padre
		$typeKey="Number"; //tipo  de dato del campo clave
		$saveUser=true;
		$clear=getParam("clear");		
		$destinoInsert = "../gestmed/sisAdmisionHistClinica_alergContrai.php?clear=$clear";
		break;


   case 'HCcontraind': // Historia Clinica:Contraindicaciones de medicamentos
		$setTable='historia_contraindicacion'; //nombre de la tabla
		$setKey='hico_id'; //campo clave
		$valueKey=getParam("f_id"); //valor del campo clave
		$last_id=$relacionamento_id ;//campo clave de la tabla padre
		$typeKey="Number"; //tipo  de dato del campo clave
		$saveUser=true;
		$clear=getParam("clear");				
		$destinoInsert = "../gestmed/sisAdmisionHistClinica_alergContrai.php?clear=$clear";
		break;

   case 'HCvacuna': // Historia Clinica:Vaunaciones
		$setTable='historia_vacuna'; //nombre de la tabla
		$setKey='hiva_id'; //campo clave
		$valueKey=getParam("f_id"); //valor del campo clave
		$last_id=$relacionamento_id ;//campo clave de la tabla padre
		$typeKey="Number"; //tipo  de dato del campo clave
		$saveUser=true;
		$clear=getParam("clear");				
		$destinoInsert = "../gestmed/sisAdmisionHistClinica_vacuna.php?clear=$clear";
		break;

   case 'HCConstVit': // Historia Clinica:Constantes vitales
		$setTable='historia_constante_vital'; //nombre de la tabla
		$setKey='hcvi_id'; //campo clave
		$valueKey=getParam("f_id"); //valor del campo clave
		$last_id=$relacionamento_id ;//campo clave de la tabla padre
		$typeKey="Number"; //tipo  de dato del campo clave
		$saveUser=true;
		$clear=getParam("clear");				
		$destinoInsert = "../gestmed/sisAdmisionHistClinica_consVit.php?clear=$clear";
		break;

   case 'HCproblema': // Historia Clinica:Problema
		$setTable='historia_problema'; //nombre de la tabla
		$setKey='hipo_id'; //campo clave
		$valueKey=getParam("f_id"); //valor del campo clave
		$last_id=$relacionamento_id ;//campo clave de la tabla padre
		$typeKey="Number"; //tipo  de dato del campo clave
		$saveUser=true;
		$clear=getParam("clear");						
		$nomeCampoForm=getParam("nomeCampoForm");
		$destinoInsert = "../gestmed/sisAdmisionHistClinica_problema.php?clear=$clear&nomeCampoForm=$nomeCampoForm";
		break;
            
            
            
            
    case 'HCCOnsultas': // Ficha Faltas
        $setTable='gestmed.historia_persona_consultas'; //nombre de la tabla
        $setKey='hpco_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;
        $destinoUpdate = "close";
        $destinoInsert = "close";
        break;
                
	/* --------------------------------*/
	// Farmacia //
	/* -------------------------------*/
        case 'FarMedi': //medicamentos
                        $setTable='catalogo_bienes'; //nombre de la tabla
                        $setKey='cabi_codigo'; //campo clave
                        $valueKey=getParam("f_id"); //valor del campo clave		
                        $typeKey="String"; //tipo  de dato del campo clave
                        $saveUser=true;
                        $destinoUpdate = "../farmacia/sisFarmaciaCatalogosMedicamentos_lista.php";
                        $destinoInsert = "../farmacia/sisFarmaciaCatalogosMedicamentos_lista.php";
                        break;

        case 'FarEntr': //Entradas/documento
                        $setTable='farmacia_entrada'; //nombre de la tabla
                        $setKey='faen_id'; //campo clave
                        $valueKey=getParam("f_id"); //valor del campo clave
                        $typeKey="Number"; //tipo  de dato del campo clave
                        $saveUser=true;
                        $destinoUpdate = "../farmacia/sisFarmaciaMovimientosEntradas_lista.php";
                        $destinoInsert = "../farmacia/sisFarmaciaMovimientosEntradas_productosLista1n.php?id=LAST_ID";
                        break;

        case 'FarEntProd': //Entradas/productos
                        $setTable='farmacia_entrada_movimiento'; //nombre de la tabla
                        $setKey='femo_id'; //campo clave
                        $valueKey=getParam("f_id"); //valor del campo clave
                        $typeKey="Number"; //tipo  de dato del campo clave
                        $saveUser=true;
                        $destinoUpdate = "close";
                        $destinoInsert = "close";
                        break;


        case 'PedidoAct': // Farmacia/actualizacion de pedidos
                        $setTable='farmacia_venta'; //nombre de la tabla
                        $setKey='fave_id'; //campo clave
                        $valueKey=getParam("f_id"); //valor del campo clave
                        $typeKey="Number"; //tipo  de dato del campo clave
                        /* Si la Clasificación de salida es SIS verifico que el paciente de la Historia elegida tenga código SIS */
                        $destinoUpdate = "../farmacia/sisFarmaciaMovimientosVentas_lista.php"; 
                        break;


        case 'PedidoNvo': //Pedidos/Nuevo
                        $setTable='farmacia_venta_detalle'; //nombre de la tabla
                        $setKey='fvde_id'; //campo clave
                        $valueKey=getParam("f_id"); //valor del campo clave
                        $typeKey="Number"; //tipo  de dato del campo clave
                        $saveUser=true;
                        $destinoUpdate = "close";
                        $destinoInsert = "close";
                        break;

	/* --------------------------------*/
	// Recaudaciones Tesorería//
	/* -------------------------------*/

        case 'PerfilVista': // Componentes de cementerio/Vistas
            $setTable='componentes_cementerio_vistas'; //nombre de la tabla
            $setKey='ccvi_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $last_id=$relacionamento_id ;//campo clave de la tabla padre
            $typeKey="Number"; //tipo  de dato del campo clave
            $saveUser=true;
            $destinoInsert = "../cementerio/catalogosComponentesCementerio_componentes.php?relacionamento_id=$last_id&clear=1";
            break;
 
        case 'agrServImg': // Servicio / Imagenes
            $setTable='servicio_imagenes'; //nombre de la tabla
            $setKey='seim_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $serv_codigo=getParam("___serv_codigo");
            $tipo=getParam("xxxtipo");
            $typeKey="Number"; //tipo  de dato del campo clave
            $saveUser=true;
            $destinoInsert = "../catalogos/catalogosServicios_imagenes.php?relacionamento_id=$serv_codigo&tipo=$tipo";
            break;                    

        case 'agrPublImg': // Servicio / Imagenes
            $setTable='siscore.publicar_imagenes'; //nombre de la tabla
            $setKey='puim_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $publ_id=getParam("___publ_id");

            $typeKey="Number"; //tipo  de dato del campo clave
            $saveUser=true;
            $destinoInsert = "../siscore/publicarImagenes_lista.php?relacionamento_id=$publ_id";
            
            $location=$_SERVER['DOCUMENT_ROOT'] ."/docs/siscore/publicar/".SIS_EMPRESA_RUC;
            if (!is_dir($location) && !mkdir($location, '0755', true)) {
            } 
            break;                    
        
	/* --------------------------------*/
	// Cementerio//
	/* -------------------------------*/
        
        case 'PediServCement': // Cementerio/Pedidos
            $setTable='pedidos.pedidos'; //nombre de la tabla
            $setKey='pedi_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $typeKey="Number"; //tipo  de dato del campo clave
            $destinoUpdate = "../cementerio/pedidos_lista.php"; 
            break;
        
	/* --------------------------------*/
	// SISCOPP //
	/* -------------------------------*/
        
        case 'catpptal': // Categoria Presupuestal
            $setTable='categoria_presupuestal'; //nombre de la tabla
            $setKey='capr_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $typeKey="String"; //tipo  de dato del campo clave
            $saveUser=true;
            $destinoUpdate = "../siscopp/siscoppCatalogosCategoriaPtal_lista.php";
            $destinoInsert = "../siscopp/siscoppCatalogosCategoriaPtal_lista.php";
            break;
        
        case 'progpptal': // Programa Presupuestal
            $setTable='programa_presupuestal'; //nombre de la tabla
            $setKey='prpr_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $typeKey="String"; //tipo  de dato del campo clave
            $saveUser=true;
            $destinoUpdate = "../siscopp/siscoppCatalogosProgramaPtal_lista.php";
            $destinoInsert = "../siscopp/siscoppCatalogosProgramaPtal_lista.php";
            break;
        
        case 'prodproypptal': // Producto Proyecto Presupuestal
            $setTable='productoproyecto_presupuestal'; //nombre de la tabla
            $setKey='prpr_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $typeKey="String"; //tipo  de dato del campo clave
            $saveUser=true;
            $destinoUpdate = "../siscopp/siscoppCatalogosProductoProyectoPtal_lista.php";
            $destinoInsert = "../siscopp/siscoppCatalogosProductoProyectoPtal_lista.php";
            break;        
        
        case 'actobraccpptal': // Actividad/Obra/Acci�n Presupuestal
            $setTable='actividadobraaccion_presupuestal'; //nombre de la tabla
            $setKey='acpr_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $typeKey="String"; //tipo  de dato del campo clave
            $saveUser=true;
            $destinoUpdate = "../siscopp/siscoppCatalogosActividadObraAccionPtal_lista.php";
            $destinoInsert = "../siscopp/siscoppCatalogosActividadObraAccionPtal_lista.php";
            break;
        
        case 'funcionp': // Funci�n Presupuestal
            $setTable='funcion'; //nombre de la tabla
            $setKey='func_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $typeKey="String"; //tipo  de dato del campo clave
            $saveUser=true;
            $destinoUpdate = "../siscopp/siscoppCatalogosFuncion_lista.php";
            $destinoInsert = "../siscopp/siscoppCatalogosFuncion_lista.php";
            break;        
        
        case 'divFuncional':
            $setTable='division'; //nombre de la tabla
            $setKey='divi_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $typeKey="String"; //tipo  de dato del campo clave
            $saveUser=true;
            $destinoUpdate = "../siscopp/siscoppCatalogosDivFuncional_lista.php";
            $destinoInsert = "../siscopp/siscoppCatalogosDivFuncional_lista.php";
            break;        
        
        case 'grupop': // Sub Programa Presupuestal
            $setTable='subprograma'; //nombre de la tabla
            $setKey='subp_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $typeKey="String"; //tipo  de dato del campo clave
            $saveUser=true;
            $destinoUpdate = "../siscopp/siscoppCatalogosGrpFuncional_lista.php";
            $destinoInsert = "../siscopp/siscoppCatalogosGrpFuncional_lista.php";
            break;        
        
        case 'componentep': // Componentes presupuestales
            $setTable='componente'; //nombre de la tabla
            $setKey='comp_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $typeKey="String"; //tipo  de dato del campo clave
            $saveUser=true;
            $destinoUpdate = "../siscopp/siscoppAperturasComponentes_lista.php";
            $destinoInsert = "../siscopp/siscoppAperturasComponentes_lista.php";
            break;
        
        case 'fueningre': // Fuentes de Ingreso
            $setTable='fuente_ingreso'; //nombre de la tabla
            $setKey='fuin_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $typeKey="String"; //tipo  de dato del campo clave
            $saveUser=true;
            $destinoUpdate = "../siscopp/siscoppCatalogosFuenIngre_lista.php";
            $destinoInsert = "../siscopp/siscoppCatalogosFuenIngre_lista.php";
            break;            
        
        case 'CodAcum': // Codigos de Acumulados Presupuestales
            $setTable='siscopp.acumulado_presupuestal'; //nombre de la tabla
            $setKey='acpr_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $tipoAcumulado=getParam("tip_acu");
            $typeKey="String"; //tipo  de dato del campo clave
            $saveUser=true;
            $destinoUpdate = "../siscopp/siscoppAperturasAcumulados_lista.php?tip_acu=$tipoAcumulado";
            $destinoInsert = "../siscopp/siscoppAperturasAcumulados_edicion.php?tip_acu=$tipoAcumulado";
            break;

        case 'AcumComp': // Acumulados Presupuestales/Componentes
            $setTable='siscopp.acumulado_presupuestal_componente'; //nombre de la tabla
            $setKey='apco_id'; //campo clave
            $valueKey=0; //valor del campo clave
            $typeKey="Number"; //tipo  de dato del campo clave
            $saveUser=true;
            $last_id=getParam("f_id");
            $tipoAcumulado=getParam("tip_acu");
            $destinoUpdate = "../siscopp/siscoppAperturasAcumulados_componentes.php?tip_acu=$tipoAcumulado";
            $destinoInsert = "../siscopp/siscoppAperturasAcumulados_componentes.php?tip_acu=$tipoAcumulado";
            break;


        case 'AcumCompPart': // Acumulados Presupuestales/Componentes/partidas
            $setTable='siscopp.acumulado_presupuestal_partida'; //nombre de la tabla
            $setKey='appa_id'; //campo clave
            $valueKey=0; //valor del campo clave
            $typeKey="Number"; //tipo  de dato del campo clave
            $saveUser=true;
            $last_id=getParam("f_id");
            $tipoAcumulado=getParam("tip_acu");
            $apco_id=getParam("apco_id");
            $destinoUpdate = "../siscopp/siscoppAperturasAcumulados_partidas.php?tip_acu=$tipoAcumulado&apco_id=$apco_id";
            $destinoInsert = "../siscopp/siscoppAperturasAcumulados_partidas.php?tip_acu=$tipoAcumulado&apco_id=$apco_id";
            //alert("xx");
            break;

        case 'tipCtaCont': // Codigos de Acumulados Presupuestales
            $setTable='siscont.plan_contable_tipo'; //nombre de la tabla
            $setKey='pcti_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave

            $typeKey="Number"; //tipo  de dato del campo clave
            $saveUser=true;
            $destinoUpdate = "../siscont/siscontCatalogosTiposCuentasContables_lista.php";
            $destinoInsert = "../siscont/siscontCatalogosTiposCuentasContables_lista.php";
            break;
        
        case 'clasCtaCont': // Acumulados Presupuestales/Componentes
            $setTable='siscont.plan_contable_clase'; //nombre de la tabla
            $setKey='pccl_id'; //campo clave
            $valueKey=0; //valor del campo clave
            $typeKey="Number"; //tipo  de dato del campo clave
            $saveUser=true;
            $last_id=getParam("f_id");
            $destinoUpdate = "../siscont/siscontCatalogosTiposCuentasContables_claseCuenta.php";
            $destinoInsert = "../siscont/siscontCatalogosTiposCuentasContables_claseCuenta.php";
            break;        
        
        case 'elemCtaCont': // Acumulados Presupuestales/Componentes/partidas
            $setTable='siscont.plan_contable_elemento'; //nombre de la tabla
            $setKey='pcel_id'; //campo clave
            $valueKey=0; //valor del campo clave
            $typeKey="Number"; //tipo  de dato del campo clave
            $saveUser=true;
            $last_id=getParam("f_id");
            $pccl_id=getParam("pccl_id");
            $destinoUpdate = "../siscont/siscontCatalogosTiposCuentasContables_elementos.php?pccl_id=$pccl_id";
            $destinoInsert = "../siscont/siscontCatalogosTiposCuentasContables_elementos.php?pccl_id=$pccl_id";
            break;
        
            /* --------------------------------*/
            // SOLICITUDES-Adjuntados //
            /* -------------------------------*/
        
        case 'agrAdjSol': // Solicitud / Ajuntados
            $setTable='pedidos_bbss_adjuntos'; //nombre de la tabla
            $setKey='pbad_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $pedi_id=getParam("___pedi_id");
            $typeKey="Number"; //tipo  de dato del campo clave
            $saveUser=true;
            $destinoInsert = "../solicitudes/movimientosSolicitud_edicionAdjunto.php?relacionamento_id=$pedi_id";
            break;                    

        /* --------------------------------*/
	// Servicios_entradas //
	/* -------------------------------*/
        case 'ServEntr': //Entradas/documento
            $setTable='siscore.servicio_entrada'; //nombre de la tabla
            $setKey='seen_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $typeKey="Number"; //tipo  de dato del campo clave
            $saveUser=true;
            $destinoUpdate = "../siscore/siscoreMovimientosEntradas_lista.php";
            $destinoInsert = "../siscore/siscoreMovimientosEntradas_productosLista1n.php?id=LAST_ID";
            break;

        case 'ServEntProd': //Entradas/productos
            $setTable='siscore.servicio_entrada_movimiento'; //nombre de la tabla
            $setKey='semo_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $relacionamento_id=getParam("nx_seen_id");
            $typeKey="Number"; //tipo  de dato del campo clave
            $saveUser=true;
            $contador=getDbValue("SELECT COUNT(*) FROM catalogos.tabla WHERE tabl_tipo='TIPO_PRECIO'");
            if($contador>1){
                $destinoUpdate = "../siscore/siscoreMovimientosEntradas_productosEdicion.php?id=$valueKey&relacionamento_id=$relacionamento_id";
                $destinoInsert = "../siscore/siscoreMovimientosEntradas_productosEdicion.php?id=$valueKey&relacionamento_id=$relacionamento_id";
            }else{
                $destinoUpdate = "close";
                $destinoInsert = "close";
            }                        
            break;

        case 'agrAdjDesp': // Despachos / Ajuntados
            $setTable='despachos_adjuntados'; //nombre de la tabla
            $setKey='dead_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $desp_id=getParam("___desp_id");
            $typeKey="Number"; //tipo  de dato del campo clave
            $saveUser=true;
            $clear=getParam("clear"); //valor del campo clave
            $destinoInsert = "../gestdoc/registroDespacho_edicionAdjuntos.php?relacionamento_id=$desp_id&clear=$clear";
            break;
    
        /* --------------------------------*/
	// LABORATORIO //
	/* -------------------------------*/    
        case 'Perfil': // Historia Clinica:Catalogos/Perfil
            $setTable='gestmed.examen'; //nombre de la tabla
            $setKey='exam_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $busEmpty=getParam("busEmpty"); //muestra todos los registro ;
            $tipo_estudio=getParam("tipo_estudio");
            $espe_id=getParam("espe_id");
            $typeKey="Number"; //tipo  de dato del campo clave
            $saveUser=true;
            $destinoUpdate = "../laboratorio/sisEstudiosCatalogosPerfil_buscar.php?busEmpty=$busEmpty&tipo_estudio=$tipo_estudio&espe_id=$espe_id";
            $destinoInsert = "../laboratorio/sisEstudiosCatalogosPerfil_edicion.php?busEmpty=$busEmpty&tipo_estudio=$tipo_estudio&espe_id=$espe_id";
            break;

   case 'PerfilGrp': // Historia Clinica:Catalogos/Perfil/Grupo
            $setTable='gestmed.examen_grupo'; //nombre de la tabla
            $setKey='exgr_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $busEmpty=getParam("busEmpty"); //muestra todos los registro ;
            $tipo_estudio=getParam("tipo_estudio");                
            $espe_id=getParam("espe_id");
            $last_id=$relacionamento_id ;//campo clave de la tabla padre
            $typeKey="Number"; //tipo  de dato del campo clave
            $saveUser=true;
            $destinoInsert = "../laboratorio/sisEstudiosCatalogosPerfil_edicion.php?busEmpty=$busEmpty&tipo_estudio=$tipo_estudio&espe_id=$espe_id";
            break;

   case 'PerfilGrpDet': // Historia Clinica:Catalogos/Perfil/Grupo/Detalle
            $setTable='gestmed.examen_grupo_detalle'; //nombre de la tabla
            $setKey='egde_id'; //campo clave
            $valueKey=getParam("f_id"); //valor del campo clave
            $busEmpty=getParam("busEmpty"); //muestra todos los registro ;
            $tipo_estudio=getParam("tipo_estudio");
            $espe_id=getParam("espe_id");
            $last_id=$relacionamento_id ;//campo clave de la tabla padre
            $PerfilGrpDet=getParam("PerfilGrpDet");
            $typeKey="Number"; //tipo  de dato del campo clave
            $saveUser=true;
            $destinoInsert = "../laboratorio/sisEstudiosCatalogosPerfil_edicion.php?busEmpty=$busEmpty&PerfilGrpDet=$PerfilGrpDet&tipo_estudio=$tipo_estudio&espe_id=$espe_id";
            break;

	/* --------------------------------*/
	// Atención Médica //
	/* -------------------------------*/
   case 'AtenMed': // Atención Médica
        $setTable='gestmed.atencion_medica'; //nombre de la tabla
        $setKey='atme_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave		$typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;
        $hipe_id=getParam("hipe_id");
        $clear=getParam("clear");
        $last_id=$valueKey ;//campo clave de la tabla padre		
        $destinoUpdate = "../gestmed/sisAtenMedAtenCitaConsulta_edicion.php?id=$last_id&hipe_id=$hipe_id&clear=$clear";
        $destinoInsert = "../gestmed/sisAtenMedAtenCitaConsulta_edicion.php?hipe_id=$hipe_id&clear=$clear";
        break;
    
    case 'AteMedEst': //Atención Médica estudios complemntarios
        $setTable='gestmed.atencion_medica_estudios'; //nombre de la tabla
        $setKey='ames_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;
        $atme_id=getParam("nx_atme_id");
        $hipe_id=getParam("hipe_id");
        $clear=getParam("clear");
        $last_id=$valueKey ;
        
        $destinoUpdate = "../gestmed/sisAtenMedAtenCitaConsulta_estudiosEdicion.php?id=$last_id&relacionamento_id=$atme_id&hipe_id=$hipe_id&clear=$clear";
        $destinoInsert = "../gestmed/sisAtenMedAtenCitaConsulta_estudiosEdicion.php?id=$last_id&relacionamento_id=$atme_id&hipe_id=$hipe_id&clear=$clear";

        break;

   case 'AteMedEstArch': // Atención Médica Estudios Archivos
        $setTable='gestmed.atencion_medica_estudios_archivo'; //nombre de la tabla
        $setKey='ames_id'; //campo clave

        $typeKey="Number"; //tipo  de dato del campo clave		
        $last_id=getParam("f_id") ;//campo clave de la tabla padre
        $saveUser=true;
        $atme_id=getParam("atme_id");
        $hipe_id=getParam("hipe_id");
        $clear=getParam("clear");
        $destinoInsert = "../gestmed/sisAtenMedAtenCitaConsulta_estudiosEdicion.php?id=$last_id&relacionamento_id=$atme_id&hipe_id=$hipe_id&clear=$clear";
        break;

   case 'AteMedTrat': //Atención Médica Tratamiento
        $setTable='gestmed.atencion_medica_tratamiento'; //nombre de la tabla
        $setKey='amtr_id'; //campo clave
        $valueKey=getParam("f_id"); //valor del campo clave
        $typeKey="Number"; //tipo  de dato del campo clave
        $saveUser=true;
        $atme_id=getParam("nx_atme_id");
        $hipe_id=getParam("hipe_id");
        $clear=getParam("clear");
        $last_id=$atme_id ;//campo clave de la tabla padre				
        $destinoUpdate = "../gestmed/sisAtenMedAtenCitaConsulta_tratamientoEdicion.php?id=$last_id&relacionamento_id=$atme_id&hipe_id=$hipe_id&clear=$clear";
        $destinoInsert = "../gestmed/sisAtenMedAtenCitaConsulta_tratamientoEdicion.php?id=$last_id&relacionamento_id=$atme_id&hipe_id=$hipe_id&clear=$clear";

        break;
    
}

include("guardar_execute.php");


//	cierra la conexion con la BD
$conn->close();

function guardar_extend($op,$sql){
	switch($op){
            case 'users':
			//EJEMPLO DE CODIGO EXTENDIDO PARA GUARDAR VALORES DE CAMPOS CHECK
			if ($_POST["hx_usua_activo"]){
                            $sql->addField("usua_activo", 1, "Number");							
                        }else{
                            $sql->addField("usua_activo", 0, "Number");							
                        }
			break;
            case 'HCPersona':
			/* ADICION DATOS DE AUDITORIA */
			$sql->addField("hipe_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
			$sql->addField("hipe_actualusua", getSession("sis_userid"), "String");				
			break;

            case 'PortEmergente':
                        if ($_POST["hx_poem_resaltar"])
                            $sql->addField("poem_resaltar", 1, "Number");
                        else
                            $sql->addField("poem_resaltar", 0, "Number");

                        /*if ($_POST["hx_poem_emergente"])
                            $sql->addField("poem_emergente", 1, "Number");
                        else
                            $sql->addField("poem_emergente", 0, "Number");
                         * */
			break;

            case 'PortEntidad': // Estructuras/Entidad
                $sql->addField("depe_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
                $sql->addField("depe_actualusua", getSession("sis_userid"), "String");
                break;


            case 'portInfraestruct': // Contenidos/Programas de Apoyo Social
                $sql->addField("poin_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
                $sql->addField("poin_actualusua", getSession("sis_userid"), "String");
                break;

            case 'PortalContenido':
                $sql->addField("poco_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
                $sql->addField("poco_actualusua", getSession("sis_userid"), "String");
                break;

            case 'Espec':
                //EJEMPLO DE CODIGO EXTENDIDO PARA GUARDAR VALORES DE CAMPOS CHECK
                if ($_POST["hx_espe_estado"])
                    $sql->addField("espe_estado", true, "String");
                else
                    $sql->addField("espe_estado", false, "String");
                
                if ($_POST["hx_espe_solicita_hc"])                
                    $sql->addField("espe_solicita_hc", 1, "Number");
                else
                    $sql->addField("espe_solicita_hc", 0, "Number");

                
                //if ($_POST["hx_espe_citavirtual"])
                //    $sql->addField("espe_citavirtual", true, "String");
                //else
                //    $sql->addField("espe_citavirtual", false, "String");
                break;

            case 'Medic':
                $sql->addField("medi_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
                $sql->addField("medi_actualusua", getSession("sis_userid"), "String");				

                //EJEMPLO DE CODIGO EXTENDIDO PARA GUARDAR VALORES DE CAMPOS CHECK
                if ($_POST["hx_medi_estado"])
                        $sql->addField("medi_estado", true, "String");
                else
                        $sql->addField("medi_estado", false, "String");
                break;

            case 'Consult':
                //EJEMPLO DE CODIGO EXTENDIDO PARA GUARDAR VALORES DE CAMPOS CHECK
                if ($_POST["hx_cons_estado"])
                        $sql->addField("cons_estado", true, "String");
                else
                        $sql->addField("cons_estado", false, "String");
                break;

            case 'CatVent': // Catalogo/Ventanillas
                $sql->addField("vent_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");  
                $sql->addField("vent_actualusua", getSession("sis_userid"), "String");				                
                break;
            
            case 'RecServ':
                //EJEMPLO DE CODIGO EXTENDIDO PARA GUARDAR VALORES DE CAMPOS CHECK
                if ($_POST["hx_serv_estado"]){
                    $sql->addField("serv_estado", true, "String");
                }else{
                    $sql->addField("serv_estado", false, "String");
                }

                if ($_POST["hx_serv_aplica_ajuste"]){
                    $sql->addField("serv_aplica_ajuste", 1, "Number");
                }else{
                    $sql->addField("serv_aplica_ajuste", 0, "Number");
                }
                

                if ($_POST["hx_serv_pensionable"]){
                    $sql->addField("serv_pensionable", 1, "Number");                   
                }else{
                    $sql->addField("serv_pensionable", 0, "Number");                                       
                }                
                
                if ($_POST["hx_serv_essalud"]){
                    $sql->addField("serv_essalud", 1, "Number");                   
                }else{
                    $sql->addField("serv_essalud", 0, "Number");                                       
                }
                

                if ($_POST["hx_serv_sctr"]){
                    $sql->addField("serv_sctr", 1, "Number");                   
                }else{
                    $sql->addField("serv_sctr", 0, "Number");                                       
                }                                
                
                if ($_POST["hx_serv_conafovicer"]){
                    $sql->addField("serv_conafovicer", 1, "Number");                   
                }else{
                    $sql->addField("serv_conafovicer", 0, "Number");                                       
                }
                
                if ($_POST["hx_serv_ir5ta"]){
                    $sql->addField("serv_ir5ta", 1, "Number");                   
                }else{
                    $sql->addField("serv_ir5ta", 0, "Number");                                       
                }                
                
                if ($_POST["hx_serv_cts"]){
                    $sql->addField("serv_cts", 1, "Number");                   
                }else{
                    $sql->addField("serv_cts", 0, "Number");                                       
                }                
                
                if ($_POST["hx_serv_automatico"]){
                    $sql->addField("serv_automatico", 1, "Number");                   
                }else{
                    $sql->addField("serv_automatico", 0, "Number");                                       
                }   
                
                if ($_POST["hx_serv_editable"]){
                    $sql->addField("serv_editable", 1, "Number");                   
                }else{
                    $sql->addField("serv_editable", 0, "Number");                                       
                }
                
                if ($_POST["hx_serv_genera_contabilidad"]){
                    $sql->addField("serv_genera_contabilidad", 1, "Number");                   
                }else{
                    $sql->addField("serv_genera_contabilidad", 0, "Number");                                       
                }
                
                if ($_POST["hx_serv_gratuito"]){
                    $sql->addField("serv_gratuito", 1, "Number");                   
                }else{
                    $sql->addField("serv_gratuito", 0, "Number");                                       
                }
                
//                if ($_POST["hx_serv_muestra_min"]){
//                    $sql->addField("serv_muestra_min", 1, "Number");
//                }else{
//                    $sql->addField("serv_muestra_min", 0, "Number");
//                }
                /* ADICION DATOS DE AUDITORIA */
                $sql->addField("serv_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
                $sql->addField("serv_actualusua", getSession("sis_userid"), "String");				
                break;
            
	   case 'PedidoAct': // Farmacia/Pedidos/Actualizacion
                /* ADICION DATOS DE AUDITORIA */
                $sql->addField("fave_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
                $sql->addField("fave_actualusua", getSession("sis_userid"), "String");				
                break;
            
            case 'FarMedi': //medicamentos
                if ($_POST["hx_cabi_estado"])
                    $sql->addField("cabi_estado", 1, "Number");
                else
                    $sql->addField("cabi_estado", 0, "Number");            
                break;
                
            case 'GrpoServ': //Grupo de Servicios
                if ($_POST["hx_segr_estado"]){
                        $sql->addField("segr_estado", 1, "Number");
                }else{
                        $sql->addField("segr_estado", 0, "Number");            
                }
                
                if ($_POST["hx_segr_lunes"]){
                        $sql->addField("segr_lunes", 1, "Number");
                }else{
                        $sql->addField("segr_lunes", 0, "Number");            
                }
                
                if ($_POST["hx_segr_martes"]){
                        $sql->addField("segr_martes", 1, "Number");
                }else{
                        $sql->addField("segr_martes", 0, "Number");            
                }
                
                if ($_POST["hx_segr_miercoles"]){
                        $sql->addField("segr_miercoles", 1, "Number");
                }else{
                        $sql->addField("segr_miercoles", 0, "Number");            
                }
                
                if ($_POST["hx_segr_jueves"]){
                        $sql->addField("segr_jueves", 1, "Number");
                }else{
                        $sql->addField("segr_jueves", 0, "Number");            
                }
                
                if ($_POST["hx_segr_viernes"]){
                        $sql->addField("segr_viernes", 1, "Number");
                }else{
                        $sql->addField("segr_viernes", 0, "Number");                            
                }
                
                if ($_POST["hx_segr_sabado"]){
                        $sql->addField("segr_sabado", 1, "Number");
                }else{
                        $sql->addField("segr_sabado", 0, "Number");                            
                }
                
                if ($_POST["hx_segr_domingo"]){
                        $sql->addField("segr_domingo", 1, "Number");
                }else{
                        $sql->addField("segr_domingo", 0, "Number");                                            
                }
                
                if ($_POST["hx_segr_almacen"]){
                        $sql->addField("segr_almacen", 1, "Number");
                }else{
                        $sql->addField("segr_almacen", 0, "Number");                                            
                }
                
                if ($_POST["hx_segr_solicita_ubigeo"]){
                        $sql->addField("segr_solicita_ubigeo", 1, "Number");
                }else{
                        $sql->addField("segr_solicita_ubigeo", 0, "Number");                                            
                }

                if ($_POST["hx_segr_solicita_ubigeo2"]){
                        $sql->addField("segr_solicita_ubigeo2", 1, "Number");
                }else{
                        $sql->addField("segr_solicita_ubigeo2", 0, "Number");                                            
                }
                
                if ($_POST["hx_segr_solicita_tipo_cliente"]){
                        $sql->addField("segr_solicita_tipo_cliente", 1, "Number");
                }else{
                        $sql->addField("segr_solicita_tipo_cliente", 0, "Number");                                            
                }
                
                if ($_POST["hx_segr_solicita_medico"]){
                        $sql->addField("segr_solicita_medico", 1, "Number");
                }else{
                        $sql->addField("segr_solicita_medico", 0, "Number");                                            
                }
                
                /* ADICION DATOS DE AUDITORIA */
                $sql->addField("segr_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
                $sql->addField("segr_actualusua", getSession("sis_userid"), "String");				
                
                break;                
                
           case 'SGrpoServ':
                if ($_POST["hx_sesg_cementerio"])
                        $sql->addField("sesg_cementerio", 1, "Number");
                else
                        $sql->addField("sesg_cementerio", 0, "Number");                                                           
                break;                
                
           case 'ContCredito': // Aperturas/Contratos
                /* ADICION DATOS DE AUDITORIA */
                $sql->addField("cocr_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
                $sql->addField("cocr_actualusua", getSession("sis_userid"), "String");				
                break;
            
            case 'RecTesoIng': // Estructuras/Entidad
                $sql->addField("reca_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
                $sql->addField("reca_actualusua", getSession("sis_userid"), "String");
                break;
            
            case 'Perfil':
                $sql->addField("exam_actualfecha", "NOW()", "String");
                $sql->addField("exam_actualusua", getSession("sis_userid"), "String");
                
                if ($_POST["hx_exam_estado"]=='1'){
                    $sql->addField("exam_estado", '1', "String");
                }else{
                    $sql->addField("exam_estado", '0', "String");
                }
                
		//EJEMPLO DE CODIGO EXTENDIDO PARA GUARDAR VALORES DE CAMPOS CHECK
		if ($_POST["hx_exam_imp_nombre_perfil"]){
                    $sql->addField("exam_imp_nombre_perfil", 1, "Number");
                }else{
                    $sql->addField("exam_imp_nombre_perfil", 0, "Number");
                }
                
                if ($_POST["hx_exam_imp_metodo"]){
                    $sql->addField("exam_imp_metodo", 1, "Number");
                }else{
                    $sql->addField("exam_imp_metodo", 0, "Number");
                }
                 
                if ($_POST["hx_exam_quiebre"]){
                    $sql->addField("exam_quiebre", 1, "Number");
                }else{
                    $sql->addField("exam_quiebre", 0, "Number");
                }
                
		break;

            case 'PerfilGrp':
                $sql->addField("exgr_actualfecha", "NOW()", "String");
                $sql->addField("exgr_actualusua", getSession("sis_userid"), "String");                                
		break;

            case 'PerfilGrpDet':
                $sql->addField("egde_factualiza", "NOW()", "String");
                $sql->addField("egde_actualusua", getSession("sis_userid"), "String");                                
		break;
            
            case 'AtenMed': // Atención Médica            
                $sql->addField("atme_actualfecha", 'now()', "String");
                $sql->addField("atme_actualusua", getSession("sis_userid"), "String");
                break;

            case 'AteMedEst': // Atención MédicaEstudios
                $sql->addField("ames_actualfecha", 'now()', "String");
                $sql->addField("ames_actualusua", getSession("sis_userid"), "String");
                break;
            
            case 'AteMedTrat': // Atención Médica Tratamiento            
                $sql->addField("amtr_actualfecha", 'now()', "String");
                $sql->addField("amtr_actualusua", getSession("sis_userid"), "String");
                break;
                
	}
}