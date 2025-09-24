<?php
/* Creo constante con las rutas para cargar o llamar archivos */
define("DB_DEFAULT",$_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/DB/pg.class.php");

//
define("NAME_EXPEDIENTE","Tr&aacute;mite");
define("NAME_EXPEDIENTE_UPPER","TRAMITE");

// Dsatos sobre la autenticación del usuario
define("AUTH_TABLA","usuario");
define("AUTH_ID","usua_id");
define("AUTH_USERNAME","usua_login");
define("AUTH_PASSWORD","usua_password");
define("AUTH_LEVEL","usua_acceso");
define("AUTH_CRIPT",true); // MD5

// Altura do frame de controle
define("FRAME_CONTROLE_ALTURA","50"); // utilizar en produccion


include(DB_DEFAULT);

$conn = new db();
$conn->open();
$sql = "SELECT * FROM admin.empresa";
$rs = new query($conn, $sql);
$rs->getrow();
$raz_soc=$rs->field("empr_razsocial");
$rs_breve=$rs->field("empr_breve");

$raz_soc="MUNICIPALIDAD PROVINCIAL DE HUAMANGA";
$rs_breve="MUNI.PROV.DE HUAMANGA";


$respons=$rs->field("empr_responsable");
$email=$rs->field("empr_email");
$email_password=$rs->field("empr_email_password");
$email_servidor=$rs->field("empr_email_servidor");

$rs_siglas=$rs->field("empr_siglas");
//$atencionxpaciente=$rs->field("empr_atencionxpaciente");
$emp_representante = $rs->field("empr_representante");
$emp_representante_dni = $rs->field("empr_representante_dni");
$emp_representante_cargo = $rs->field("empr_representante_cargo");
$emp_representante_documento = $rs->field("empr_representante_documento");
$emp_ruc = $rs->field("empr_ruc");
$emp_ciudad = $rs->field("empr_ciudad");
$emp_direccion = $rs->field("empr_direccion");
$emp_telefono = $rs->field("empr_telefono");
$empr_domain = $rs->field("empr_domain");

//SE AGREGA ESTA CONSTANTE EN config_extend.php
if($empr_domain){
    define("PATH_PORT",$empr_domain); 
}else{
    define("PATH_PORT","http://localhost/sisadmin/"); 
}

$empr_tipo = $rs->field("empr_tipo");
$empr_fcierre_nopago = $rs->field("empr_fcierre_nopago");
$empr_url_resultados_laboratorio = $rs->field("empr_url_resultados_laboratorio");

//$empr_efact_tipo_funcionalidad=$empr_efact_tipo_funcionalidad?$empr_efact_tipo_funcionalidad:1;
//echo $empr_efact_tipo_funcionalidad;
    
$sql = "SELECT * FROM admin.aplicativo";
$rs = new query($conn, $sql);
$rs->getrow();
$app_name=$rs->field("apli_nombre");
$app_version=$rs->field("apli_version");
$app_acronimo=$rs->field("apli_acronimo");
$app_piereporte=$rs->field("apli_piereporte");
$app_max_meses_serfin = $rs->field("apli_max_meses_serfin");
$app_max_cita_anula = $rs->field("apli_max_cita_anula");
$app_gestdoc=$rs->field("apli_gestdoc");
$app_gestleg=$rs->field("apli_gestleg");
$app_gestleg_modo=$rs->field("apli_gestleg_modo");
$app_sislogal=$rs->field("apli_sislogal");
$app_siscore=$rs->field("apli_siscore");
$app_cotizaciones=$rs->field("apli_cotizaciones");
$app_siscopp=$rs->field("apli_siscopp");
$app_escalafon=$rs->field("apli_escalfon");
$app_gestmed=$rs->field("apli_gestmed");
$app_sislogal_solicita_referencias=$rs->field("apli_sislogal_solicita_referencias");
$app_siscaja=$rs->field("apli_siscaja");
$app_sispat=$rs->field("apli_sispat");
$app_siscore_secuencia_automatico=$rs->field("apli_siscore_secuencia_automatico");
$app_siscore_siscont=$rs->field("apli_siscore_siscont");
$apli_siscore_pedidos=$rs->field("apli_siscore_pedidos");
$apli_planillas=$rs->field("apli_planillas");
$app_siscont=$rs->field("apli_siscont");

$app_siaf=$rs->field("apli_siaf");
$app_gestcne=$rs->field("apli_gestcne");
$app_firma1_orden = $rs->field("apli_firma1_orden");
$app_firma2_orden = $rs->field("apli_firma2_orden");
$app_firma3_orden = $rs->field("apli_firma3_orden");
$app_mancomunado_orden = $rs->field("apli_mancomunado_orden");
$app_efact = $rs->field("apli_efact");
$app_efact_modo = $rs->field("apli_efact_modo");
$app_efact_email_from = $rs->field("apli_efact_email_from");
$app_efact_url_consulta = $rs->field("apli_efact_url_consulta");
$app_pedidos = $rs->field("apli_pedidos");
$app_efact_tipo_funcionalidad = $rs->field("apli_efact_tipo_funcionalidad"); //1.>Envio de comprobantes a SUNAT Manual, 2->Envio de comprobantes a SUNAT Automatica
$app_gestdoc_tipo = $rs->field("apli_gestdoc_tipo"); //1->SIN FIRMA 2->CON FIRMA
$app_laboratorio = $rs->field("apli_laboratorio");
$app_imprime_credenciales_laboratorio = $rs->field("apli_imprime_credenciales_laboratorio");
$app_tolerancia_publica_resultados_laboratorio = $rs->field("apli_tolerancia_publica_resultados_laboratorio");
$app_mpv = $rs->field("apli_mpv");
$app_tipo_ubigeo_cliente = $rs->field("apli_tipo_ubigeo_cliente");
$app_tipo_negocio_cliente = $rs->field("apli_tipo_negocio_cliente");
$app_nombre_comercial_cliente = $rs->field("apli_nombre_comercial_cliente");
$app_iniciar_pedido_con_cliente = $rs->field("apli_iniciar_pedido_con_cliente");
$app_firma_personal_electronica = $rs->field("apli_firma_personal_electronica");
$app_pide_ubicacion_producto = $rs->field("apli_pide_ubicacion_producto");
$app_varios_almacenes = $rs->field("apli_varios_almacenes");

$igv=getDbValue("SELECT igv_valor FROM catalogos.igv ORDER BY igv_fecharegistro DESC LIMIT 1 ");

$conn->close();

//Datos da la Empresa
define("SIS_EMPRESA",$raz_soc);
define("SIS_EMPRESA_BREVE",$rs_breve);
define("SIS_EMPRESA_SIGLAS",$rs_siglas);

 
define("SIS_NOMBR_RESPONSABLE",$respons);
define("SIS_EMAIL_RESPONSABLE",$email);
define("SIS_CIUDAD",$emp_ciudad);//ciudad
define("SIS_EMPRESA_RUC",$emp_ruc);//ciudad
define("SIS_EMPRESA_DIRECC",$emp_direccion);//ciudad
define("SIS_EMPRESA_TELEF",$emp_telefono);//ciudad

define("SIS_EMAIL_GMAIL",$email);
define("SIS_PASS_EMAIL_GMAIL",$email_password);
define("SIS_EMAIL_SERVIDOR",$email_servidor);
define("SIS_URL_RESULTADOS_LABORATORIO",$empr_url_resultados_laboratorio);

define("SIS_EFACT",$app_efact);
define("SIS_EFACT_MODO",$app_efact_modo); //1->PRODUCCION, 2->HOMOLOGACION, 3->PRUEBAS
define("SIS_EFACT_URL_CONSULTA",$app_efact_url_consulta);

define("SIS_EFACT_EMAIL_FROM",$app_efact_email_from);
define("SIS_EFACT_TIPO_FUNCIONALIDAD",$app_efact_tipo_funcionalidad);


define("SIS_EMPRESA_TIPO",$empr_tipo);//1->PRIVADO, 2->PUBLICO BASICO, 3->PUBLICO BENEFICENCIA
define("SIS_FCIERRE_NOPAGO",$empr_fcierre_nopago);
        
define("SIS_IGV",$igv); //% de IGV

define("SIS_CITAS_XMES",99999);//numero de citas por paciente en el mes
define("SIS_ANULA_XTUR",$app_max_cita_anula);//numero de citas maximas a anular en el día y por turno
define("SIS_REPRESENTA",$emp_representante);//representante
define("SIS_REPRESENTA_DNI",$emp_representante_dni);//DNI representante
define("SIS_REPRESENTA_CARGO",$emp_representante_cargo);//cargo representante
define("SIS_REPRESENTA_DOC",$emp_representante_documento);
define("SIS_MESES_SERFIN",$app_max_meses_serfin); //maximo de meses para atencions de contratos serfin

define("SIS_FIRMA1_ORDEN",$app_firma1_orden);
define("SIS_FIRMA2_ORDEN",$app_firma2_orden);
define("SIS_FIRMA3_ORDEN",$app_firma3_orden);
define("SIS_FIRMA_MACOMUNADO_ORDEN",$app_mancomunado_orden);


//Datos de la Aplicación
define("SIS_APL_NAME",$app_acronimo);
define("SIS_VERSION",$app_acronimo.' '.$app_version);
define("SIS_TITULO",$app_name);
define("SIS_PIELEFT_REPORTE",$app_piereporte);
define("SIS_GESTDOC",$app_gestdoc);
define("SIS_GESTLEG",$app_gestleg);
define("SIS_GESTLEG_MODO",$app_gestleg_modo);
define("SIS_SISLOGAL",$app_sislogal);
define("SIS_SIAF",$app_siaf);        
define("SIS_GESTCNE",$app_gestcne);
define("SIS_SISCORE",$app_siscore);
define("SIS_COTIZACIONES",$app_cotizaciones);
define("SIS_PEDIDOS",$app_pedidos);
define("SIS_SISCOPP",$app_siscopp);
define("SIS_ESCALAFON",$app_escalafon);
define("SIS_GESTMED",$app_gestmed);
define("SIS_SISLOGAL_SOLICITA_REFERENCIAS",$app_sislogal_solicita_referencias);
define("SIS_SISCAJA",$app_siscaja);
define("SIS_SISPAT",$app_sispat);
define("SIS_SISCORE_SECUENCIA_AUTOMATICA",$app_siscore_secuencia_automatico);
define("SIS_SISCORE_SISCONT",$app_siscore_siscont);
define("SIS_SISCORE_PEDIDOS",$apli_siscore_pedidos);
define("SIS_PLANILLAS",$apli_planillas);
define("SIS_SISCONT",$app_siscont);
define("SIS_GESTDOC_TIPO",$app_gestdoc_tipo);
define("SIS_LABORATORIO",$app_laboratorio);
define("SIS_MPV",$app_mpv);

define("SIS_IMPRIME_CREDENCIALES_LABORATORIO",$app_imprime_credenciales_laboratorio);
define("SIS_TOLERANCIA_LABORATORIO",$app_tolerancia_publica_resultados_laboratorio);

define("SIS_TIPO_UBIGEO_CLIENTE",$app_tipo_ubigeo_cliente);
define("SIS_TIPO_NEGOCIO_CLIENTE",$app_tipo_negocio_cliente);
define("SIS_NOMBR_COMERCIAL_CLIENTE",$app_nombre_comercial_cliente);
define("SIS_INICIAR_PEDIDO_SOLICITANDO_CLIENTE",$app_iniciar_pedido_con_cliente);
define("SIS_FIRMA_PERSONAL_ELECTRONICA",$app_firma_personal_electronica);        
define("SIS_PIDE_UBICACION_PRODUCTO",$app_pide_ubicacion_producto);
define("SIS_VARIOS_ALMACENES",$app_varios_almacenes);
        
define("SIS_FULLSCREEN",false);

/* otros datos de configuracion */
// Página de login
define("LOGIN_TITULO","Bienvenidos, Ingrese Usuario y Contrase&ntilde;a");

// Página de acesso negado
define("LOGIN_ACESSONEGADO","Necesita de permisos<br>para acessar a esta p&aacute;gina");

// Datos para la ventana emergente de busqueda 
define("LOOKUP_MAX_REC",300);
define("LOOKUP_TITULO_PESQUISA","Localizar:");
define("LOOKUP_SUBTITULO","Seleccione o localice un registro");
define("LOOKUP_RESET","&raquo; Insertar vacio ");
define("LOOKUP_FIELDSIZE",40);
define("LOOKUP_IMAGEM","/sisadmin/intranet/img/smallsearch.gif");

// Altura del frame cabecara
define("FRAME_HEADER_ALTURA","30");

// Largura del frame menu
define("FRAME_MENU_LARGURA","210");

// Menu
define("MENU_EMPTY","No hay items disponibles para este m&oacute;dulo");

// TextAreaField
define("TEXTAREA_RESTANTES","caracteres restantes");

// FileField
define("FILEFIELD_ARQUIVOATUAL","");
define("FILEFIELD_REMOVER","remover");

// DateField
define("DATEFIELD_IMAGEM","../img/icon-calen.gif");

// Filtro ativo em listas
define("FILTRO_ATIVO","B&uacute;squeda Activa [" . "<a class='link' href='".$_SERVER['PHP_SELF']."?clear=1"."'>Limpiar</a>]");

//  cone de help
define("HELP_IMAGEM","../img/help.gif");
define("HELP_CORFUNDO","#FFFFDE");
define("HELP_CORTITULO","#006699");
define("HELP_CORTEXTO","#000000");
define("HELP_FONTTITULO","Tahoma, Verdana, Arial, Helvetica");
define("HELP_FONTTEXTO","Tahoma, Verdana, Arial, Helvetica");
define("HELP_TAMANHOTITULO","10pt");
define("HELP_TAMANHOTEXTO","8pt");

// Elementos de las páginas
define("LISTA_ANTERIOR","&laquo; Anterior");
define("LISTA_PROXIMO","Siguiente &raquo;");
define("LOGIN_MENSAGEM",SIS_EMPRESA.'<br>'.SIS_NOMBR_RESPONSABLE);


include('config_extend.php');