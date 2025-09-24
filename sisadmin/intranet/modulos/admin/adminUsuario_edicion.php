<?php
/* formulario de ingreso y modificación */
include("../../library/library.php");
include("../catalogos/catalogosDependencias_class.php");
include("../catalogos/catalogosTabla_class.php");
include("../gestmed/sisRecaudacionCatalogosVentanillas_class.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("adminUsuario_class.php");
include("../personal/personalDatosLaborales_class.php");
include("../admin/datosEmpresaRUC_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();
$param->removePar('relacionamento_id'); /* Remuevo el par�metro */

$id = getParam("id"); // captura la variable que viene del objeto lista

$myClass = new clsUsers($id,'Edici&oacute;n de Usuario');

/* Archivos para calendario */
require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);

// Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("cargar");
$xajax->registerFunction("getFechBaja");
$xajax->registerFunction("pideEstablecimiento");

function cargar($IdPadre,$IdHijo,$NameDiv,$op)
{
        $response = new xajaxResponse();
		$sqlUndEje = "select  a.depe_id as id,b.depe_nombre as descripcion from func_depenhijos($IdPadre) a left join dependencia b on a.depe_id=b.depe_id where b.deni_id=1 order by 2";
		$contenido_respuesta=listboxAjaxField("Unidad Ejecutora",$sqlUndEje,"tx_depe_idejecutora",$IdHijo,"-- Seleccione Unidad Ejecutora --","",$NameDiv);

        // Esta funcion lo que hace es borrar el contenido y agregar lo que pongamos aqui
        // Se le pasan 3 valores: 1�) El id del elemento donde se va a insertar
        //                        2�) La propiedad JS del elemento en este caso innerHTML
        //                        3�) El valor que se va a insertar, para no tener problemas
        //                            con las tildes codificamos el string en utf8
        $response->addAssign($NameDiv,'innerHTML', $contenido_respuesta);

        // Se devuelve el objeto, que este dara todas las instruccione JS para realizar la tarea
		if($op==1){
	        return $response;
		}else{
			return $contenido_respuesta	;
		}		
}

function getFechBaja($op,$value,$NameDiv)
{
	global $calendar,$bd_usua_fvigencia;
	$objResponse = new xajaxResponse();
	
	$otable = new AddTableForm();
	$otable->setLabelWidth("20%");
	$otable->setDataWidth("80%");
	
	if($value==9){
		$otable->addField("Fecha de Baja: ", $calendar->make_input_field('Fecha de Baja',array(),array('name'=> 'Dr_usua_fvigencia','value'=> $bd_usua_fvigencia)));
	}
	else{
		$otable->addHidden("Dx_usua_fvigencia",""); // clave primaria
	}
	
	$contenido_respuesta=$otable->writeHTML();

    // Se devuelve el objeto, que este dara todas las instruccione JS para realizar la tarea
	if($op==1){
                $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);
		return $objResponse;
	}else{
		return $contenido_respuesta	;
	}		

}

function pideEstablecimiento($op,$depe_id,$NameDiv)
{
	global $bd_depe_id_almacen;
	$objResponse = new xajaxResponse();
	
	$otable = new AddTableForm();
	$otable->setLabelWidth("20%");
	$otable->setDataWidth("80%");

        $depen=new dependencia_SQLlista();
        $depen->wheredepeAlmacenVentas();
        $depen->whereDepeTodos($depe_id);
        $sql=$depen->getSQL_cbox2();
        $otable->addField("Establecimiento ".iif(SIS_EMPRESA_TIPO,'!=',4,'P/Compras/Ventas','').": ",listboxField("Establecimiento",$sql,"ax_depe_id_almacen[]",$bd_depe_id_almacen,"-- Todos --","","","class=\"my_select_box\" multiple style=\"width:500px;\" "));
	$contenido_respuesta=$otable->writeHTML();

	if($op==1){
            $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);
            $objResponse->addScript("$('.my_select_box').select2({
                                        placeholder: 'Seleccione un elemento de la lista',
                                        allowClear: true,
                                        width:'90%'
                                        });");
            return $objResponse;
	}else{
            return $contenido_respuesta	;
	}		

}
$xajax->processRequests();

if (strlen($id)>0) { // edición
	$myClass->setDatos();
	if($myClass->existeDatos()){
		$bd_usua_id = $myClass->field("usua_id");
		$bd_usua_login = $myClass->field("usua_login");
		$bd_usua_iniciales = $myClass->field("usua_iniciales");
                $bd_usua_password = "******" ;
	        $bd_usua_acesso = $myClass->field("usua_acceso");
		$bd_pdla_id = $myClass->field("pdla_id");
		$bd_depe_id = $myClass->field("depe_id");
		$bd_usua_tipo= $myClass->field("usua_tipo");
		$bd_usua_detalle = $myClass->field("usua_detalle");
		$bd_usua_activo = $myClass->field("usua_activo");
		$bd_origen = $myClass->field("origen_usuario"); 
		$bd_usua_idcrea = $myClass->field("usua_idcrea");	
		$bd_usua_fvigencia= dtos($myClass->field("usua_fvigencia"));
		$bd_usua_fecharegistro = $myClass->field("usua_fecharegistro");
		$bd_usua_mensaje = $myClass->field("usua_mensaje");
		$bd_pers_cargo = $myClass->field("pdla_cargofuncional");
                $bd_depe_nombre = $myClass->field("depe_nombre");
                $bd_depe_nombre_superior = $myClass->field("depe_superior_nombre");
                $bd_usua_set_gestdoc_todos= $myClass->field("usua_set_gestdoc_todos");
                $bd_tabl_settipodespacho= $myClass->field("tabl_settipodespacho");
                $bd_usua_set_jefe= $myClass->field("usua_set_jefe");
                $bd_usua_set_depe_exclusivo=$myClass->field("usua_set_depe_exclusivo");
                $bd_usua_set_autoriza_solicitudes=$myClass->field("usua_set_autoriza_solicitudes");
                $bd_usua_set_aplica_ajustes_ventas=$myClass->field("usua_set_aplica_ajustes_ventas");
                $bd_emru_id=$myClass->field("emru_id");
                $bd_depe_id_almacen=$myClass->field("depe_id_almacen");
                $bd_usua_set_certificado=$myClass->field("usua_set_certificado");
                $bd_usua_mesa_partes_virtual=$myClass->field("usua_mesa_partes_virtual");
                $bd_usua_set_edita_total=$myClass->field("usua_set_edita_total");
                $bd_usua_set_edita_total2=$myClass->field("usua_set_edita_total2");
                $bd_usua_set_edita_impunitario=$myClass->field("usua_set_edita_impunitario");
                $bd_usua_set_edita_fecha=$myClass->field("usua_set_edita_fecha");
                $bd_medi_id=$myClass->field("medi_id");
                $bd_vent_id=$myClass->field("vent_id");
		$bd_usuaCrea=$myClass->field("usua_crea");
                $bd_usua_ventas_acceso=$myClass->field("usua_ventas_acceso");
                $bd_depe_id_set=$myClass->field("depe_id_set");
                $bd_usua_permitir_regularizaciones=$myClass->field("usua_permitir_regularizaciones");
                $bd_usua_permite_cancelar_creditos=$myClass->field("usua_permite_cancelar_creditos");
                $bd_usua_page_ini=$myClass->field("usua_page_ini");
	}
} else { // si no es edición
	$bd_usua_activo = 1;
	$bd_usua_acesso = 1;
	$bd_depe_idejecutora=0; // para cuando se llama al combo Ajax
	$bd_usua_idcrea=getSession("sis_userid");	
        $bd_tabl_settipodespacho=0;
        $bd_usua_set_certificado=1;
}




// definición de la lista para campo radio
$lista_nivel = array("1,Visitante","2,Operador","3,Supervisor");
?>
<html>
<head>
<title><?=$myClass->getTitle()?>-Edici&oacute;n</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
<script language="javascript" src="../../library/calendario/popcalendar.js"></script>
<!--script language="javascript" src="../../library/js/lookup2.js"></script-->
<!--script language="javascript" src="../../library/js/tree.js"></script-->	
<script language="JavaScript" src="../../library/js/focus.js"></script>
<script language="JavaScript" src="../../library/js/textcounter.js"></script>
<script language="JavaScript" src="../../library/js/libjsgen.js"></script>	

<script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>
<link rel="stylesheet" href="../../library/select2/dist/css/select2.css">        
<script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>

<script language='JavaScript'>

	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			document.frm.target = "controle";
			document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
			document.frm.submit();
		}
	}
	/*
		se invoca desde la funcion obligacampos (libjsgen.js)
		en esta funci�n se puede personalizar la validaci�n del formulario
		y se ejecuta al momento de gurdar los datos
	*/
	function mivalidacion(){
            var sError="Mensajes del sistema: "+"\n\n";
            var nErrTot=0;

		if (nErrTot>0){
			alert(sError)
			eval(foco)
			return false
		}else
			return true

	}

	/*
		funci�n que define el foco inicial en el formulario
	*/
	function inicializa() {
		document.frm.Sr_usua_login.focus();
	}
	</script>

<?php

$xajax->printJavascript(PATH_INC.'ajax/'); 
verif_framework(); 
$calendar->load_files();		


?>
</head>

<body class="contentBODY" <?php echo iif(strlen($id),">","0","","onLoad=\"inicializa()\"")?>>
<?php
pageTitle($myClass->getTitle());

/* botones */
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",3);
$button->addItem(" Regresar ",$myClass->getPageBuscar().$param->buildPars(true));
echo $button->writeHTML();

$abas = new Abas();
$abas->addItem("General",true);

if (strlen($id)>0) { // si es edición
	$abas->addItem("Perfiles",false,"adminUsuario_perfilLista1n.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));	
}

echo $abas->writeHTML();

echo "<br>";

/* Formulario */
$form = new Form("frm", "", "POST", "content", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s"); // variable de control

$form->addHidden("f_id",$id); // clave primaria
$form->addHidden("pagina",getParam("pagina")); // numero de p�gina que llamo

//
// si es edicion el usuario no es editable
if($bd_usua_login) $form->addField("Usuario: ",$bd_usua_login);
else $form->addField("Usuario: ",textField("Usuario","Sr_usua_login",$bd_usua_login,20,20));

$form->addField("Password: ",passwordField("Password","pr_usua_password",$bd_usua_password,20,20) . help("Ayuda","Se recomienda colocar una contraseña cuya longitud sea mayor a 4 d&iacute;gitos",2));

$form->addField("Iniciales: ",textField("Iniciales","Sr_usua_iniciales",$bd_usua_iniciales,8,8));
// si no es el administrador... solicita el nivel de acceso                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
if($id!=1){
	$form->addField("Nivel de acceso: ",radioField("Nivel de acceso",$lista_nivel, "xr_usua_acceso",$bd_usua_acesso));
}

// definición de lookup Empleados
$empleado= new Lookup();
$empleado->setTitle("Empleado/Invitado");
$empleado->setNomeCampoChave("pdla_id");
$empleado->setNomeCampoForm("Empleado","nr_pdla_id");
$sqlEmpleado=new clsDatosLaborales_SQLlista(0,0,0);
        $sql = "SELECT pdla_id,
                        empleado,
                        depe_nombrecorto,
                        sit_laboral,
                        pdla_cargofuncional AS cargo_estructural,
                        pers_dni
                FROM (".$sqlEmpleado->getSQL().") AS  a";

setSession("sqlLkupEmp", $sql);
$empleado->setNomeTabela("sqlLkupEmp");  //nombre de tabla
$empleado->setNomeCampoExibicao("empleado,pers_dni");  // Campos en los que deseo se efect�e la b�squeda.
$empleado->setUpCase(true);//para busquedas con texto en mayuscula
$empleado->setListaInicial(0);	
$empleado->setSize(70);
$empleado->setValorCampoForm($bd_pdla_id);

$form->addField("Empleado/Invitado: ",$empleado->writeHTML());
// FIN definición de lookup Empleados



if (strlen($id)>0){
       $form->addBreak("<b>Datos Laborales</b>");
       $form->addField("Dependencia: ",$bd_depe_nombre.'/'.$bd_depe_nombre_superior);
       if($bd_pers_cargo)
            $form->addField("Cargo: ",$bd_pers_cargo);

	$form->addBreak("<b>Estado</b>");
	
	$lista_nivel = array("1,Activo","3,Obligar Cambio de Contraseña","9,de Baja");
	$form->addField("Estado: ",radioField("Estado",$lista_nivel, "xx_usua_activo",$bd_usua_activo,"onChange=\"xajax_getFechBaja(1,this.value,'divFechBaja')\"","H"));
	
	
	$form->addHtml("<tr><td colspan=2><div id='divFechBaja'>\n"); //guarda campos ocultos con datos de inicio
	$form->addHtml(getFechBaja(2,$bd_usua_activo,'divFechBaja'));
	$form->addHtml("</div></td></tr>\n");	
	
	//$form->addField("Mensaje Alerta: ",textAreaField("Mensaje Alerta","ex_usua_mensaje",$bd_usua_mensaje,5,100,600));
}
if(SIS_GESTDOC==1){
    $form->addField(checkboxField("Establecer 'Todos los Usuarios'","hx_usua_set_gestdoc_todos",1,$bd_usua_set_gestdoc_todos==1),"Establecer 'Todos los Usuarios' por defecto para Recibir y Procesar despachos");

    $desp_tipo=new clsTabla_SQLlista();
    $desp_tipo->whereTipo('TIPO_DESPACHO');
    $desp_tipo->whereActivo();
    $desp_tipo->orderUno();
    $rs = new query($conn, $desp_tipo->getSQL());

    $lista_nivel=array();
    while ($rs->getrow()) {
        $bd_tabl_settipodespacho=$bd_tabl_settipodespacho?$bd_tabl_settipodespacho:$rs->field("tabl_id");
        $lista_nivel[].=$rs->field("tabl_id").",".$rs->field("tabl_descripcion");
    }
    $form->addField("Establecer Tipo de ".NAME_EXPEDIENTE.": ",radioField("Establecer Tipo de ".NAME_EXPEDIENTE,$lista_nivel, "tx_tabl_settipodespacho",$bd_tabl_settipodespacho,"","H"));
    $form->addField(checkboxField("Firmar haciendo uso de Certificados Digitales","hx_usua_set_certificado",1,$bd_usua_set_certificado==1),"Firmar haciendo uso de Certificados Digitales");    
    //$form->addField(checkboxField("Es Usuario P/Mesa de Partes Virtual","hx_usua_mesa_partes_virtual",1,$bd_usua_mesa_partes_virtual==1),"Es Usuario P/Mesa de Partes Virtual");
}

if(SIS_GESTCNE==1){//cuadro de necesidades
    //$form->addField(checkboxField("Es Responsable de Unidad Org&aacute;nica","hx_usua_set_jefe",1,$bd_usua_set_jefe==1),"Es Responsable de Unidad Org&aacute;nica P/Cuadro de Necesidades");
    $form->addField(checkboxField("Autoriza Solicitudes de Bienes y Servicios","hx_usua_set_autoriza_solicitudes",1,$bd_usua_set_autoriza_solicitudes==1),"Autoriza Solicitudes de Bienes y Servicios");
}


if(SIS_SISCORE==1){//RECAUDACIONES
    $form->addBreak("<b>INGRESOS/VENTAS</b>");
    /* Instancio la Dependencia */
    $sqlDependencia=new dependenciaSuperior_SQLBox(getSession("sis_depe_superior"));
    $sqlDependencia=$sqlDependencia->getSQL();        

    //FIN OBTENGO
    $form->addField("Dependencia Predeterminada: ",listboxField("Dependencia Predeterminada",$sqlDependencia,"tx_depe_id_set","$bd_depe_id_set","-- Todas --","onChange=\"xajax_pideEstablecimiento(1,this.value,'divEstablecimiento')\"","","class=\"my_select_box\""));        
    $form->addHtml("<tr><td colspan=2><div id='divEstablecimiento'>\n"); //guarda campos ocultos con datos de inicio
    $form->addHtml(pideEstablecimiento(2,$bd_depe_id_set,'divEstablecimiento'));
    $form->addHtml("</div></td></tr>\n");    

    $form->addField(checkboxField("Elaborar documentos exclusivamente con la Dependencia Predeterminada","hx_usua_set_depe_exclusivo",1,$bd_usua_set_depe_exclusivo==1),"Elaborar documentos exclusivamente con la Dependencia Predeterminada");        
    
    
    if ( SIS_EMPRESA_TIPO!=4 ){//diferente a Empresa tipo Almacén
        $form->addField(checkboxField("Permitir hacer Descuentos en las Ventas","hx_usua_set_aplica_ajustes_ventas",1,$bd_usua_set_aplica_ajustes_ventas==1),"Permitir Hacer Descuentos en las Ventas");    
        $form->addField(checkboxField("Permitir modificar el Total en las Ventas","hx_usua_set_edita_total",1,$bd_usua_set_edita_total==1),"Permitir Modificar el Total en las Ventas (Ajusta las Cantidades autom&aacute;ticamente)");
        $form->addField(checkboxField("Permitir modificar el Total en las Ventas-2","hx_usua_set_edita_total2",1,$bd_usua_set_edita_total2==1),"Permitir Modificar el Total en las Ventas-2 (NO Ajusta las Cantidades)");
        $form->addField(checkboxField("Permitir editar la Fecha de Emision en las Ventas","hx_usua_set_edita_fecha",1,$bd_usua_set_edita_fecha==1),"Permitir Editar la Fecha de Emisi&oacute;n en las Ventas");
        $form->addField(checkboxField("Permitir modificar el Importe Unitario en las Ventas","hx_usua_set_edita_impunitario",1,$bd_usua_set_edita_impunitario==1),"Permitir Modificar el Importe Unitario en las Ventas (en Ventas con Detalle)");
        $form->addField(checkboxField("Permitir Cancelar las Ventas al Cr&eacute;dito","hx_usua_permite_cancelar_creditos",1,$bd_usua_permite_cancelar_creditos==1),"Permitir Cancelar las Ventas al Cr&eacute;dito");    
    }
     
    $form->addField(checkboxField("Permitir hacer Regularizaciones/Ajustes de Inv.en Almac&eacute;nes","hx_usua_permitir_regularizaciones",1,$bd_usua_permitir_regularizaciones==1),"Permitir hacer Regularizaciones/Ajustes de Inventarios en Almac&eacute;nes");

    
    
    //$sqlVentanilla="SELECT vent_id,vent_descripcion||COALESCE('-'||vent_direccion,'') FROM ventanilla where vent_tipo='T' order by vent_id";	
    
    
    if ( SIS_EMPRESA_TIPO!=4 ){//diferente a Empresa tipo Almacén
        $vent = new  ventanilla_SQLlista(); 
        $vent->whereTipo('T');
        $sqlVentanilla=$vent->getSQL_cbox();
        $form->addField("Ventanilla: ",listboxField("Ventanilla",$sqlVentanilla,"nx_vent_id",$bd_vent_id,"-- Seleccione Ventanilla --"));

        $tblVinculos=new clsTabla_SQLlista();
        $tblVinculos->whereTipo('VINCULO_GRUPO_SERVICIO');

        $sqlVinculos="SELECT a.tabl_codigo,
                             a.tabl_descripcion 
                      FROM (".$tblVinculos->getSQL().") AS a
                      UNION ALL
                      SELECT 9999,
                             'PERMITIR TODOS'
                      FROM admin.empresa
                      WHERE empr_id=1
                      ORDER BY 1 ";

        $tblVinculos->getSQL_cboxCodigo();
        $form->addField("Permitir Solo Ventas de : ",listboxField("Permitir_ventas",$sqlVinculos,"ax_usua_ventas_acceso[]","$bd_usua_ventas_acceso","seleccione Filtro de Servicios","","","class=\"my_select_box\" multiple style=\"width:500px;\" "));                
    }
}

if(SIS_GESTMED==1){//GESTMED
    $sqlMed="SELECT a.medi_id,
                      a.medi_apellidos||' '||a.medi_nombres ||' / '|| d.espe_descripcion
                      FROM medico a
                      LEFT JOIN gestmed.medico_especialidad c ON a.medi_id=c.medi_id
                      LEFT JOIN especialidad d ON c.espe_id=d.espe_id 
                      ORDER BY 2";

    $form->addField("M&eacute;dico: ",listboxField("Médico",$sqlMed,"tx_medi_id",$bd_medi_id,"-- Seleccione Médico --"));
}

$form->addBreak("<b>Otros</b>");
$form->addField("Detalles: ",textAreaField("Detalles","Ex_usua_detalle",$bd_usua_detalle,3,50,200));

if ( SIS_SISCORE==1 && SIS_EMPRESA_TIPO!=4 ){//diferente a Empresa tipo Almacén
    $form->addBreak("<b>SISADMIN2</b>");
    $sqlMed="SELECT       a.smop_page2,
                          c.sist_descripcion||'/'||b.simo_descripcion||'/'||a.smop_descripcion AS descripcion
                          FROM admin.sistema_modulo_opciones a
                          LEFT JOIN admin.sistema_modulo b ON a.simo_id = b.simo_id
                          LEFT JOIN admin.sistema c ON b.sist_id = c.sist_id
                          WHERE a.smop_page2 IS NOT NULL
                          ORDER BY c.sist_id,
                                    b.simo_id,
                                    a.smop_id";

    $form->addField("P&aacute;gina de Inicio: ",listboxField("Pagina de Inicio",$sqlMed,"sx_usua_page_ini",$bd_usua_page_ini,"-- Seleccione P&aacute;gina --"));
}

if (strlen($id)>0) {
	$form->addBreak("<b>Control</b>");
	$form->addField("Creado por: ",$bd_usuaCrea.' / '.$bd_usua_fecharegistro);
	$form->addField("Origen: ",$bd_origen);	
}

echo $form->writeHTML();
?>
    <script>

        $('.my_select_box').select2({
            placeholder: 'Seleccione un elemento de la lista',
            allowClear: true,
            width:'90%'
            });
        
        <?php 
        if($bd_usua_ventas_acceso){
            echo "$('#Permitir_ventas').val([$bd_usua_ventas_acceso]).trigger('change');";
        }
        if($bd_depe_id_almacen){
            echo "$('#Establecimiento').val([$bd_depe_id_almacen]).trigger('change');";
        }
        ?>
    </script>    
</body>
</html>

<?php
/* cierro la conexión a la BD */
$conn->close();
