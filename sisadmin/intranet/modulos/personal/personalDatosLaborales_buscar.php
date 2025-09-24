<?php
/* Modelo de p�gina que apresenta um formulario con criterios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("personalDatosLaborales_class.php");
include("../catalogos/catalogosDependencias_class.php");
include("../catalogos/catalogosTabla_class.php");

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$busEmpty = 1; //getParam("busEmpty"); // para definir si se muestran los datos en el primer ingreso
$nbusc_sitlaboral = getParam("nbusc_sitlaboral");
$nbusc_char= getParam("nbusc_char");

$sub_dependencia=getParam("tr_sub_dependencia");

$param= new manUrlv1();
$param->removePar('clear');
$param->removePar('relacionamento_id');

$myClass = new clsDatosLaborales(0,"Datos Laborales");


if ($clear==1) {
	setSession("cadSearch","");
        //$nbusc_sitlaboral=1;
        $nbusc_char='';
}

$nbusc_depe_id=getParam('nbusc_depe_id');
if(!$nbusc_depe_id) $nbusc_depe_id=getSession("sis_depe_superior");        

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "clsDatosLaborales","buscar"),"");
$xajax->registerFunction("establecerJefe");
$xajax->registerFunction("deshacerJefe");
$xajax->registerFunction("darBaja");
$xajax->registerFunction("habilitarEmpleado");
$xajax->registerFunction("subDependencia");

function establecerJefe($id)
{
    global $conn;
    $objResponse = new xajaxResponse();
    
    $datos_laborales=new clsDatosLaborales_SQLlista();
    $datos_laborales->whereID($id);
    $datos_laborales->setDatos();
    $error=0;
    $usua_id=getSession('sis_userid');
    if($datos_laborales->existeDatos()){
        if($datos_laborales->field('pdla_estado')==1){ //HABILITADO
            $depe_id=$datos_laborales->field('depe_id');
            // Armo strng a ejecutar
            $sSql="UPDATE catalogos.dependencia 
                        SET pdla_id=$id,
                            usua_id_establecer_jefe=$usua_id,
                            depe_fregistro_establecer_jefe=NOW()    
                   WHERE depe_id=$depe_id;
                       
                   /*GERENERA HISTORICO*/    
                   INSERT INTO catalogos.dependencia_jefe 
                                            (pdla_id,
                                             depe_id,
                                             usua_id,
                                             deje_estado)
                           VALUES($id,
                                  $depe_id,
                                  $usua_id,
                                  1 /*ALTA*/
                               );
                    ";

            // Ejecuto el string
            $conn->execute($sSql);
            $error=$conn->error();
        }else{
            $objResponse->addAlert('Estado del Registro No Habilitado..');
        }
    }else{
        $objResponse->addAlert('Sin datos para procesar...');
    }
    
    if($error){ 
        $objResponse->addAlert($error);
    }else{     
        $objResponse->addScript("parent.content.location.reload()");
    }
    return $objResponse;
}

function deshacerJefe($id)
{
    global $conn;
    $objResponse = new xajaxResponse();
    
    $datos_laborales=new clsDatosLaborales_SQLlista();
    $datos_laborales->whereID($id);
    $datos_laborales->setDatos();
    $error=0;
    $usua_id=getSession('sis_userid');
    if($datos_laborales->existeDatos()){
        if($datos_laborales->field('pdla_estado')==1){ //HABILITADO
            $depe_id=$datos_laborales->field('depe_id');
            // Armo strng a ejecutar
            $sSql="UPDATE catalogos.dependencia 
                        SET pdla_id=NULL,
                            usua_id_establecer_jefe=NULL,
                            depe_fregistro_establecer_jefe=NULL
                   WHERE depe_id=$depe_id;
                       
                   /*GERENERA HISTORICO*/    
                   INSERT INTO catalogos.dependencia_jefe 
                                            (pdla_id,
                                             depe_id,
                                             usua_id,
                                             deje_estado)
                           VALUES($id,
                                  $depe_id,
                                  $usua_id,
                                  2 /*BAJA*/
                               );
                    ";

            // Ejecuto el string
            $conn->execute($sSql);
            $error=$conn->error();
        }else{
            $objResponse->addAlert('Estado del Registro No Habilitado..');
        }
    }else{
        $objResponse->addAlert('Sin datos para procesar...');
    }
    
    if($error){ 
        $objResponse->addAlert($error);
    }else{     
        $objResponse->addScript("parent.content.location.reload()");
    }
    return $objResponse;
}

function darBaja($id)
{
    global $conn;
    $objResponse = new xajaxResponse();
    
    $datos_laborales=new clsDatosLaborales_SQLlista();
    $datos_laborales->whereID($id);
    $datos_laborales->setDatos();
    $error=0;
    $usua_id=getSession('sis_userid');
    if($datos_laborales->existeDatos()){
        if($datos_laborales->field('pdla_estado')==1){ //HABILITADO
            // Armo strng a ejecutar
            $sSql="UPDATE personal.persona_datos_laborales
                        SET pdla_estado=9,
                            usua_id_dar_baja=$usua_id,
                            pdla_fregistro_dar_baja=NOW()    
                   WHERE pdla_id=$id ";

            // Ejecuto el string
            $conn->execute($sSql);
            $error=$conn->error();
        }else{
            $objResponse->addAlert('Estado del Registro No Habilitado..');
        }
    }else{
        $objResponse->addAlert('Sin datos para procesar...');
    }
    
    if($error){ 
        $objResponse->addAlert($error);
    }else{     
        $objResponse->addScript("parent.content.location.reload()");
    }
    return $objResponse;
}

function habilitarEmpleado($id)
{
    global $conn;
    $objResponse = new xajaxResponse();
    
    $datos_laborales=new clsDatosLaborales_SQLlista();
    $datos_laborales->whereID($id);
    $datos_laborales->setDatos();
    $error=0;
    $usua_id=getSession('sis_userid');
    if($datos_laborales->existeDatos()){
        if($datos_laborales->field('pdla_estado')==9){ //DE BAJA
            // Armo strng a ejecutar
            $sSql="UPDATE personal.persona_datos_laborales
                        SET pdla_estado=1,
                            usua_id_habilita=$usua_id,
                            pdla_fregistro_habilita=NOW()    
                   WHERE pdla_id=$id ";

            // Ejecuto el string
            $conn->execute($sSql);
            $error=$conn->error();
        }else{
            $objResponse->addAlert('Estado del Registro No se encuentra de baja..');
        }
    }else{
        $objResponse->addAlert('Sin datos para procesar...');
    }
    
    if($error){ 
        $objResponse->addAlert($error);
    }else{     
        $objResponse->addScript("parent.content.location.reload()");
    }
    return $objResponse;
}


function subDependencia($op,$depe_id,$sub_dependencia,$NameDiv)
{
    $objResponse = new xajaxResponse();

    $otable = new AddTableForm();
    $otable->setLabelWidth("20%");
    $otable->setDataWidth("80%");

    //$sub_dependencia=$sub_dependencia?$sub_dependencia:$depe_id;
            
    $sqlDependencia=new dependencia_SQLBox($depe_id);
    $sqlDependencia=$sqlDependencia->getSQL();

    $otable->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia,"tr_sub_dependencia","$sub_dependencia","-- Todos --","","","class=\"my_select_box\""));        
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
        return $contenido_respuesta;
    }
}
$xajax->processRequests();

// fin para Ajax
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="javascript" src="<?php echo PATH_INC?>js/checkall.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.2.min.js"></script>   
        
        <link rel="stylesheet" href="<?php echo PATH_INC?>select2/dist/css/select2.css">
        <script src="<?php echo PATH_INC?>select2/dist/js/select2.js" type="text/javascript"></script>                
        
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>
        
	<script language="JavaScript">

		function inicializa() {
			document.frm.Sbusc_cadena.focus();
		}
	
		function excluir() {
			if (confirm('Eliminar registros seleccionados?')) {
				parent.content.document.frm.target = "controle";
				parent.content.document.frm.action = "<?=$myClass->getNameFile()."?control=".base64_encode(2)."&".$param->buildPars(false)?>";
				parent.content.document.frm.submit();
				}
			}
	</script>
    <?php $xajax->printJavascript(PATH_INC.'ajax/'); ?>
	<? verif_framework(); ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("B&uacute;squeda de Personas con ".$myClass->getTitle());

/* botones */
$button = new Button;

$button->addItem(" Nuevo ","personalDatosLaborales_edicion.php".$param->buildPars(true),"content");
//$button->addItem("Eliminar","javascript:excluir()","content",2);

echo $button->writeHTML();

/* formul�rio de pesquisa */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s");

//array de parametros que se ingresaran a la funcion de busqueda de ajax
$paramFunction= new manUrlv1();
$paramFunction->removeAllPar();
$paramFunction->addParComplete('colSearch','');
$paramFunction->addParComplete('colOrden','1');
$paramFunction->addParComplete('busEmpty',$busEmpty);
$paramFunction->addParComplete('busEmpty',$busEmpty);
$paramFunction->addParComplete('nbusc_sitlaboral',$nbusc_sitlaboral);
$paramFunction->addParComplete('nbusc_char',$nbusc_char);
$paramFunction->addParComplete('tr_sub_dependencia',$sub_dependencia);
$depeid=$nbusc_depe_id;
$cadena=getSession("cadSearch");

/* Instancio la Dependencia */
$sqlDependencia=new dependenciaSuperior_SQLBox3(getSession("sis_depe_superior"));
$sqlDependencia=$sqlDependencia->getSQL();        

//FIN OBTENGO
$form->addField("Dependencia Superior: ",listboxField("Dependencia Superior",$sqlDependencia,"nbusc_depe_id",$depeid,"-- Todas las Dependencia --","onChange=\"xajax_subDependencia(1,this.value,'','divSubDependencia')\"","","class=\"my_select_box\""));        

$form->addHtml("<tr><td colspan=2><div id='divSubDependencia'>\n");
$form->addHtml(subDependencia(2,$depeid,"$sub_dependencia",'divSubDependencia'));
$form->addHtml("</div></td></tr>\n");
 
$tabla=new clsTabla_SQLlista();
$tabla->whereTipo('CONDICION_LABORAL');
$tabla->orderUno();
$sqlSituLabo=$tabla->getSQL_cbox();
$form->addField("Condici&oacute;n laboral: ",listboxField("Condici&oacute;n laboral",$sqlSituLabo, "nbusc_sitlaboral",$nbusc_sitlaboral,"-- Toda Condici&oacute;n Laboral --","","","class=\"my_select_box\""));

$lista_nivel=array("1,Activos","9,De Baja","0,Todos");
$form->addField("Estado: ",radioField("Estado",$lista_nivel, "nbusc_estado",1,"1",'H'));

$form->addField(checkboxField("Filtrar Jefes'","nbusc_filtrar_jefes",1,0),"Filtrar Jefes");

$form->addField("DNI/Apellidos/Nombres: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">");

$cad="<TR><TD colspan=6><table border=0 cellpadding=0 cellspacing=0><tr>";
        for($i=65;$i<=90;$i++)
            $cad.="<td><input type=\"button\" id=\"nbusc_char\" style=\"font_size:6px\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado','".chr($i)."')\" value=\"".chr($i)."\">"."</td>";
        $cad.="</tr></table></TD></TR>";

$form->addHtml($cad);

$formData['nbusc_estado']=1;
$formData['nbusc_depe_id']=$depeid;
$formData['tr_sub_dependencia']=$sub_dependencia;

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");
$form->addHtml($myClass->buscar(2,$formData,encodeArray($paramFunction->getUrl()),1,'DivResultado'));
$form->addHtml("</div></td></tr>\n");

$dialog=new Dialog("myModalConfirm","confirm");
$dialog->setModal("modal-ms");//mediano
$form->addHtml($dialog->writeHTML());        

echo  $form->writeHTML();
?>
    
    <div id="myModal" class="modal fade">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>Loading...</p>
                    </div>

            </div>
        </div>    
    </div>
    
</body>
</html>

    <script>
        $(".my_select_box").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true,
            width:'90%'
            });
    </script>
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();