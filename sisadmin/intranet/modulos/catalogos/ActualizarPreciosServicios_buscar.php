<?php
/*
	Modelo de p�gina que apresenta um formulario con crit�rios de busqueda
*/
include("../../library/library.php");
include("catalogosServicios_class.php");
include("catalogosTabla_class.php");
//include("../admin/datosEmpresaRUC_class.php");
include("../catalogos/catalogosDependencias_class.php");


/*
	verificaci�n del n�vel de usu�rio
*/
verificaUsuario(1);

/*
	establecer conexi�n con la BD
*/
$conn = new db();
$conn->open();

$id = getParam("id"); // captura la variable que viene del objeto nuevo
$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$nuevo = getParam("nuevo");//habilita o no el boton 'nuevo'
$busEmpty=getParam("busEmpty")?1:0; //permite (1) � NO (0) mostrar todos los registros de la tabla
$colOrden = getParam("colOrden")?getParam("colOrden"):'1,2';//columna por la cual se ordenara la consulta
$numForm = getParam("numForm")?getParam("numForm"):0;//funciona solo con CLEAR=2, es el numero de formulario en el cual se encuentra el objeto desde donde fue llamado

$tipo_precio = getParam("tipo_precio");

$nBusc_grupo_id=getParam("nBusc_grupo_id");
/*
	limpia la cadena de filtro
	si clear=1 -> esta pagina es llamada desde el menu
	si clear=2 -> esta pagina es llamada desde la busqueda avanzada (AvanzLookup)
*/
if ($clear==1) {
    setSession("cadSearch","");
    if(getSession("SET_DEPE_EMISOR")){
        $bd_depe_id=getSession("SET_DEPE_EMISOR");
    }else{
        $bd_depe_id=getSession("sis_depe_superior");
    }
}

$myClass = new servicios(0,"Servicios");

// Inicio Para Ajax
require_once("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("busqueda_Servicio");
$xajax->registerFunction("muestra_subGrupo");
$xajax->registerFunction("graba_precio");

$xajax->registerExternalFunction(array("buscarActualizarPrecio", "servicios","buscarActualizarPrecio"),"");

//funcion que obliga a ingresar la especialidad
function muestra_subGrupo($op,$valor,$NameDiv){
				
	global $conn;

	$objResponse = new xajaxResponse();

	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");


	$sqlSGrupo="SELECT sesg_id,sesg_descripcion FROM servicio_sgrupo WHERE segr_id=$valor ORDER BY 2" ;	
	$oForm->addField("Sub Grupo: ",listboxField("Sub Grupo",$sqlSGrupo,"nBusc_sgrupo_id","",'-- Seleccione Sub Grupo de Servicio --'));

	$contenido_respuesta=$oForm->writeHTML();

	if($op==1){
            $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);	            
            $objResponse->addScript("$('.my_select_box').chosen({
                                        disable_search_threshold: 5, //SE DESHABILITA SI SOLO HAY 5 REGISTROS
                                        allow_single_deselect: true,
                                        search_contains: true,
                                        no_results_text: 'Oops, No Encontrado!',
                                        width: '80%'
                                        })");
            
            return $objResponse;	
	}else{
            return $contenido_respuesta	;
	}
}

function graba_precio($DivId,$Oper,$precio)
{
	global $conn, $id, $tipo_precio;
        
	$objResponse = new xajaxResponse();

	if($Oper==1){ // Edita

		$contenido="<div id=\"edt_$DivId\">".numField("Precio","nr_precio",$precio,12,10,2,false)."</div>";

		$contenido2="<a class=\"link\" href=\"#\" onClick=\"xajax_graba_precio($DivId,2,document.frm.nr_precio.value)\"><img src=\"../../img/guardar.gif\" border=0 align=absmiddle hspace=1 alt=\"Guardar\"></a>";

	}else{ // Guarda
		// Armo strng a ejecutar


                $precio=$precio?$precio:0;

                $sSql="UPDATE catalogos.servicio_precios
                            SET sepr_precio=$precio
                        WHERE sepr_id=$DivId;";
        
                if($tipo_precio==10){//precio general, actualiza tambien en la tabla padre
                    $sSql.="UPDATE catalogos.servicio
                                SET serv_precio=$precio
                                WHERE serv_codigo=(SELECT serv_codigo FROM catalogos.servicio_precios WHERE sepr_id=$DivId);"; 
                }
                //$objResponse->addAlert($sSql);
		// Ejecuto el string
		$conn->execute($sSql);
		$error=$conn->error();
                if($error){
                    $objResponse->addAlert($error);
                }

		$contenido="<div id=\"imp_$DivId\">".$precio."</div>";
		$contenido2="<a class=\"link\" href=\"#\" onClick=\"xajax_graba_precio($DivId,1,$precio)\"><img src=\"../../img/editar.gif\" border=0 align=absmiddle hspace=1 alt=\"Editar\"></a>";

	}
	$Div='Precio_'.$DivId;
	$Div2='img_'.$DivId;


	$objResponse->addAssign($Div,"innerHTML",$contenido);
	$objResponse->addAssign($Div2,"innerHTML",$contenido2);

	return $objResponse;
}
$xajax->processRequests();
// fin para Ajax

?>

<html>
<head>
	<title>Busquedas de Productos/Servicios</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT ?>">
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>
        <script type="text/javascript" src="../../library/jquery/jquery-1.9.js"></script>
	<script type="text/javascript" src="../../library/jquery/interface.js"></script>
        <script type="text/javascript" src="../../library/jquery/jquery-ui.js"></script>
        <link rel="stylesheet" href="../../library/jquery-chosen/chosen.css">
        <script src="../../library/jquery-chosen/chosen.jquery.js" type="text/javascript"></script>
        
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>        
	<script language="JavaScript">
	
	/*
		funcion que define el foco inicial del formulario
	*/
	function inicializa() {
		// inicia el foco en el primer campo
		parent.content.document.frm.Sbusc_cadena.focus();
	}

                
	</script>
        <?php 
        $xajax->printJavascript(PATH_INC.'ajax/');
        verif_framework(); 
        ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
$nombre_tipo_precio=getDbValue("SELECT tabl_descripcion FROM catalogos.tabla WHERE tabl_id=$tipo_precio");
pageTitle("Actualizar Precio del Tipo: $nombre_tipo_precio","");



// define la expresi�n SQL para la funci�n lista visualizada en un emergente


/*
	formul�rio de pesquisa
*/
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s");

$sqlDependencia=new dependenciaSuperior_SQLBox2(getSession("SET_DEPE_EMISOR"));
$sqlDependencia=$sqlDependencia->getSQL();        
if(!$bd_depe_id){    
    $bd_depe_id=getDbValue("SELECT id FROM ($sqlDependencia) AS x ORDER BY 1 LIMIT 1");     
}

$form->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia,"nbusc_depe_id","$bd_depe_id","-- Todas las Dependencias --","","","class=\"my_select_box\""));        

/* Instancio la Dependencia */
//$sqlDependencia=new dependenciaSuperior_SQLBox(getSession("sis_depe_superior"));
//$sqlDependencia=$sqlDependencia->getSQL();        

//FIN OBTENGO
//$form->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia,"nbusc_depe_id","","-- Todas las Dependencia --","","","class=\"my_select_box\""));        


$sqlGServicio=new clsGrupoTTra_SQLlista();
$sqlGServicio=$sqlGServicio->getSQL_servicioGrupo($tipo);
//$nBusc_grupo_id=$nBusc_grupo_id?$nBusc_grupo_id:getDbValue("SELECT a.segr_id FROM ($sqlGServicio) AS a ORDER BY 1 LIMIT 1") ;
$form->addField("Grupo: ",listboxField("Grupo",$sqlGServicio,"nBusc_grupo_id","","-- Todos --", "onChange=\"xajax_muestra_subGrupo(1,this.value,'DivSubGrupo')\"","","class=\"my_select_box\""));
$form->addHtml("<tr><td colspan=2><div id='DivSubGrupo'>\n");
$form->addHtml(muestra_subGrupo(2,"$nBusc_grupo_id",'DivSubGrupo'));
$form->addHtml("</div></td></tr>\n");



$buttom="<input type=\"button\" onClick=\"xajax_buscarActualizarPrecio(1,document.frm.Sbusc_cadena.value,document.frm.nbusc_depe_id.value,'$tipo_precio',document.frm.nBusc_grupo_id.value,document.frm.nBusc_sgrupo_id.value,'','$colOrden','$busEmpty','$numForm',1,'DivResultado');document.getElementById('DivResultado').innerHTML = 'Espere, Buscando...'\"\" value=\"Buscar\">";        
$form->addField("C&oacute;digo/Descripci&oacute;n: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',50,50)."&nbsp;$buttom");

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");

$form->addHtml($myClass->buscarActualizarPrecio(2,getSession("cadSearch"),"$bd_depe_id","$tipo_precio","$nBusc_grupo_id",'','',$colOrden,$busEmpty,$numForm,1,'DivResultado'));

$form->addHtml("</div></td></tr>\n");
echo  $form->writeHTML();

?>
    
<script>    
    $('.my_select_box').chosen({
        disable_search_threshold: 5, //SE DESHABILITA SI SOLO HAY 5 REGISTROS
        allow_single_deselect: true,
        search_contains: true,
        no_results_text: 'Oops, No Encontrado!',
        width: '80%'
        });
</script>                                                            
</body>
</html>

<?php
/*
	cierra la conexion a la BD, no debe ser alterado
*/
$conn->close();