<?php
/* Modelo de p�gina que apresenta um formulario con criterios de busqueda */
include("../../library/library.php");

/* verificación del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("Persona_class.php");
include("../catalogos/catalogosTabla_class.php");
include("PersonaContrato_class.php");
include("../catalogos/catalogosDependencias_class.php");

/* establecer conexion con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$busEmpty = 1; //getParam("busEmpty"); // para definir si se muestran los datos en el primer ingreso
$nbusc_char = getParam("nbusc_char");
$nbusc_sitlaboral = getParam("nbusc_sitlaboral");
$nbusc_categoria = getParam("nbusc_categoria");
$nbusc_clasificacion = getParam("nbusc_clasificacion");
$nbusc_periodo_alta = getParam("nbusc_periodo_alta");
$nbusc_plan_activo = getParam("nbusc_plan_activo");
$nbusc_plan_activo=$nbusc_plan_activo?$nbusc_plan_activo:1;

$param= new manUrlv1();
$param->removePar('clear');
$param->removePar('id_relacion');
$myClass = new clsPersona(0,"Datos Personales");


if ($clear==1) {
    setSession("cadSearch","");
    $nbusc_char='';    
}

$nbusc_depe_id=getParam('nbusc_depe_id');
if(!$nbusc_depe_id) $nbusc_depe_id=getSession("sis_depe_superior");        

/* Archivos para calendario */
require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "clsPersona","buscar"),"");
$xajax->registerFunction("pideCategoria");
$xajax->registerFunction("imprimir");
$xajax->registerFunction("setMover");

function pideCategoria($op,$value,$NameDiv)
{
	global $conn,$bd_care_id,$nbusc_categoria;

	$objResponse = new xajaxResponse();

	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");

        $sqlCategoria = "SELECT care_id as id, care_descripcion FROM categoria_remunerativa WHERE tabl_idsitlaboral=$value ORDER BY 2";
        $oForm->addField("Categor&iacute;a: ",listboxField("Categoria",$sqlCategoria, "nbusc_categoria","$nbusc_categoria","-- Todas las Categorias --",""));

        $contenido_respuesta=$oForm->writeHTML();
        

	if($op==1){
                $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);                            
		return $objResponse;
        }else
		return $contenido_respuesta;

}

function imprimir($op,$page,$NameDiv)
{
    global $conn,$calendar ;
    $objResponse = new xajaxResponse();

    switch($op){
        case '010520ESCALAFON'://trabajadores por condición laboral
            $objResponse->addScript('$( "#title-myModal" ).text( " Imprimir")');            
            $form = new Form("frmImp", "", "POST", "controle", "100%",true);
            $form->setLabelWidth("20%");
            $form->setDataWidth("80%");
            
            $tabCondLaboral=new clsTabla_SQLlista();
            $tabCondLaboral->whereTipo('CONDICION_LABORAL');
            $form->addField("Condici&oacute;n Laboral: ",listboxField("Condicion Laboral", $tabCondLaboral->getSQL_cbox(1), "nbusc_condlaboral", "","-- Todas las Condiciones --",""));
            
            $tabla=new clsTabla_SQLlista();
            $tabla->whereTipo('CLASIFICACION_PERSONAL');
            $sqlTipo=$tabla->getSQL_cbox2();
            $form->addField("Clasificaci&oacute;n:",listboxField("Clasificaci&oacute;n",$sqlTipo,"tr_tabl_clasificacion","99","",""));        

            $grupo = array("1,Componente Presupuestal","2, Dependencia Laboral","9,NINGUNO"); 
            $form->addField("Agrupado Por: ",radioField("Estado",$grupo, "xr_tipGrupo",'9',"","H"));

            
            $lista_nivel = array("1,ACTIVO","9,DE BAJA","99,TODOS"); 
            $form->addField("Estado: ",radioField("Estado",$lista_nivel, "xr_pers_activo",'1',"","H"));
            
            $button = new Button;
            $button->setDiv(false);
            $button->addItem("<img src='../../img/pdf.png' border='0'>&nbsp;"."Imprimir","javascript:proceder('Imprimir','$page?destino=1' )","content",2);
            $button->addItem("<img src='../../img/xls.png' border='0'>&nbsp;"."Exportar","javascript:proceder('Exportar','$page?destino=2' )","content",2);

            $form->addField("",$button->writeHTML());            
            
            $contenido_respuesta=$form->writeHTML();

            $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);            
            break;

        case '010521ESCALAFON'://trabajadores por edad
            $objResponse->addScript('$( "#title-myModal" ).text( " Imprimir")');            
            $form = new Form("frmImp", "", "POST", "controle", "100%",true);
            $form->setLabelWidth("20%");
            $form->setDataWidth("80%");
            
            $tabCondLaboral=new clsTabla_SQLlista();
            $tabCondLaboral->whereTipo('CONDICION_LABORAL');
            $form->addField("Condici&oacute;ns Laboral: ",listboxField("Condicion Laboral", $tabCondLaboral->getSQL_cbox(1), "nbusc_condlaboral", "","-- Todas las Condiciones --",""));
            $tabla=new clsTabla_SQLlista();
            $tabla->whereTipo('CLASIFICACION_PERSONAL');
            $sqlTipo=$tabla->getSQL_cbox2();
            $form->addField("Clasificaci&oacute;n:",listboxField("Clasificaci&oacute;n",$sqlTipo,"tr_tabl_clasificacion","99","",""));        

            $lista_nivel = array("1,ACTIVO","9,DE BAJA","99,TODOS"); 
            $form->addField("Estado: ",radioField("Estado",$lista_nivel, "xr_pers_activo",'1',"","H"));
            
            $form->addField("Años (>=):",numField("Años","nr_busc_annos","",4,4,0,false));    

            $button = new Button;
            $button->setDiv(false);
            $button->addItem("<img src='../../img/check.gif' border='0'>&nbsp;"."Proceder","javascript:proceder('Proceder','$page' )","content",2);
            $form->addField("",$button->writeHTML());            
            
            $contenido_respuesta=$form->writeHTML();

            $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);            
            break;

        case '010522ESCALAFON'://trabajadores por Perfil de Estudio
            $objResponse->addScript('$( "#title-myModal" ).text( " Imprimir")');            
            $form = new Form("frmImp", "", "POST", "controle", "100%",true);
            $form->setLabelWidth("20%");
            $form->setDataWidth("80%");
            
            $tabCondLaboral=new clsTabla_SQLlista();
            $tabCondLaboral->whereTipo('CONDICION_LABORAL');
            $form->addField("Condici&oacute;ns Laboral: ",listboxField("Condicion Laboral", $tabCondLaboral->getSQL_cbox(1), "nbusc_condlaboral", "","-- Todas las Condiciones --",""));
            $tabla=new clsTabla_SQLlista();
            $tabla->whereTipo('CLASIFICACION_PERSONAL');
            $sqlTipo=$tabla->getSQL_cbox2();
            $form->addField("Clasificaci&oacute;n:",listboxField("Clasificaci&oacute;n",$sqlTipo,"tr_tabl_clasificacion","99","",""));        

            $lista_nivel = array("1,ACTIVO","9,DE BAJA","99,TODOS"); 
            $form->addField("Estado: ",radioField("Estado",$lista_nivel, "xr_pers_activo",'1',"","H"));
            
            $tabla=new clsTabla_SQLlista();
            $tabla->whereTipo('GRADO_INSTRUCCION');
            $tabla->orderUno();
            $sql=$tabla->getSQL_cbox();
            $form->addField("Grado Instrucci&oacute;n: ",listboxField("Grado Instruccion",$sql,"tx_tabl_gradoinstruccion","","-- Todos--"));

            $form->addField("Filtro: ",textField("Filtro","sx_filtro",'',90,90));
            
            $button = new Button;
            $button->setDiv(false);
            $button->addItem("<img src='../../img/check.gif' border='0'>&nbsp;"."Proceder","javascript:proceder('Proceder','$page' )","content",2);
            $form->addField("",$button->writeHTML());            
            
            $contenido_respuesta=$form->writeHTML();

            $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);            
            break;
        
        case '010528ESCALAFON'://trabajadores por Fecha de culminación de Contrato
            $objResponse->addScript('$( "#title-myModal" ).text( " Imprimir")');            
            $form = new Form("frmImp", "", "POST", "controle", "100%",true);
            $form->setLabelWidth("20%");
            $form->setDataWidth("80%");
            
            $tabCondLaboral=new clsTabla_SQLlista();
            $tabCondLaboral->whereTipo('CONDICION_LABORAL');
            $form->addField("Condici&oacute;n Laboral: ",listboxField("Condicion Laboral", $tabCondLaboral->getSQL_cbox(1), "nbusc_condlaboral", "","-- Todas las Condiciones --",""));
            
            $tabla=new clsTabla_SQLlista();
            $tabla->whereTipo('CLASIFICACION_PERSONAL');
            $sqlTipo=$tabla->getSQL_cbox2();
            $form->addField("Clasificaci&oacute;n:",listboxField("Clasificaci&oacute;n",$sqlTipo,"nbusc_clasificacion","99","",""));        

            
            $calendar->calendar_correla=10;
            $form->addField("Fecha de Culminación: ", $calendar->make_input_field('Fecha de Culminación',array(),array('name'=> 'Dr_fecha_termina','value'=> '')));
            $button = new Button;
            $button->setDiv(false);
            $button->addItem("<img src='../../img/check.gif' border='0'>&nbsp;"."Proceder","javascript:proceder('Proceder','$page' )","content",2);
            $form->addField("",$button->writeHTML());            
            
            $contenido_respuesta=$form->writeHTML();

            $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);            
            break;

        case '010529ESCALAFON'://trabajadores por ultimo contrato
            $objResponse->addScript('$( "#title-myModal" ).text( " Imprimir")');            
            $form = new Form("frmImp", "", "POST", "controle", "100%",true);
            $form->setLabelWidth("20%");
            $form->setDataWidth("80%");
            
            $tabCondLaboral=new clsTabla_SQLlista();
            $tabCondLaboral->whereTipo('CONDICION_LABORAL');
            $form->addField("Condici&oacute;n Laboral: ",listboxField("Condicion Laboral", $tabCondLaboral->getSQL_cbox(1), "nbusc_condlaboral", "","-- Todas las Condiciones --",""));

            $tabla=new clsTabla_SQLlista();
            $tabla->whereTipo('CLASIFICACION_PERSONAL');
            $sqlTipo=$tabla->getSQL_cbox2();
            $form->addField("Clasificaci&oacute;n:",listboxField("Clasificaci&oacute;n",$sqlTipo,"nbusc_clasificacion","99","",""));        
            
            $button = new Button;
            $button->setDiv(false);
            $button->addItem("<img src='../../img/check.gif' border='0'>&nbsp;"."Proceder","javascript:proceder('Proceder','$page' )","content",2);
            
            $form->addField("",$button->writeHTML());            
            
            $contenido_respuesta=$form->writeHTML();

            $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);            
            break;
        
        case '010530ESCALAFON'://trabajadores por cargo
            $objResponse->addScript('$( "#title-myModal" ).text( " Imprimir")');            
            $form = new Form("frmImp", "", "POST", "controle", "100%",true);
            $form->setLabelWidth("20%");
            $form->setDataWidth("80%");
            
            $tabCondLaboral=new clsTabla_SQLlista();
            $tabCondLaboral->whereTipo('CONDICION_LABORAL');
            $form->addField("Condici&oacute;n Laboral: ",listboxField("Condicion Laboral", $tabCondLaboral->getSQL_cbox(1), "nbusc_condlaboral", "","-- Todas las Condiciones --",""));
            
            $tabla=new clsTabla_SQLlista();
            $tabla->whereTipo('CLASIFICACION_PERSONAL');
            $sqlTipo=$tabla->getSQL_cbox2();
            $form->addField("Clasificaci&oacute;n:",listboxField("Clasificaci&oacute;n",$sqlTipo,"tr_tabl_clasificacion","99","",""));        

            $lista_nivel = array("1,ACTIVO","9,DE BAJA","99,TODOS"); 
            $form->addField("Estado: ",radioField("Estado",$lista_nivel, "xr_pers_activo",'1',"","H"));
            
            $button = new Button;
            $button->setDiv(false);
            $button->addItem("<img src='../../img/pdf.png' border='0'>&nbsp;"."Imprimir","javascript:proceder('Imprimir','$page?destino=1' )","content",2);
            $button->addItem("<img src='../../img/xls.png' border='0'>&nbsp;"."Exportar","javascript:proceder('Exportar','$page?destino=2' )","content",2);

            $form->addField("",$button->writeHTML());            
            
            $contenido_respuesta=$form->writeHTML();

            $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);            
            break;
        
        
    }
    $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);    
    return $objResponse;
}

function setMover($array_id){
    global $conn;
    
    $objResponse = new xajaxResponse();
    
    $usua_id=getSession('sis_userid');
    
    if(is_array($array_id)){
        $id=implode(",",$array_id);
    
        $sql="UPDATE personal.persona
                                 SET pers_tipo_persona=2,
                                     pers_move_fregistro=NOW(),
                                     pers_move_usua_id=$usua_id    
                                 WHERE pers_id IN ($id)";
                
        $conn->execute($sql);
        $error=$conn->error();
        if($error){
            $objResponse->addAlert($error);        
        }else{
            $objResponse->addScript("parent.content.location.reload()");
        }
    }
    return $objResponse;
}

$xajax->processRequests();

// fin para Ajax
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="javascript" src="../../library/js/checkall.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>           
        
        <link rel="stylesheet" href="<?php echo PATH_INC?>select2/dist/css/select2.css">
        <script src="<?php echo PATH_INC?>select2/dist/js/select2.js" type="text/javascript"></script>                
        
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>        
        <script type="text/javascript" src="../../library/tablesorter/jquery.tablesorter.js"></script>        
	<script language="JavaScript">
    
                function update(valor, descricao, numForm) {
                    parent.parent.content.document.forms[numForm]._Dummynx_pers_id.value = descricao;
                    parent.parent.content.document.forms[numForm].nx_pers_id.value = valor;
                    parent.parent.parent.content.cerrar();
		}
                
                                        
                function activaSorter(){
                    $(function() {
                        $(".tablesorter").tablesorter({ 

                            headers: { 
                                // asignamos a la columna cero (Iniciamos contando desde cero) 
                                0: { 
                                    // Para que la columna no sea ordenable 
                                    sorter: false 
                                    } 
                             }    
                        }); 				
                    });	
                }

                activaSorter();                    
		function inicializa() {
                    document.frm.Sbusc_cadena.focus();
		}
	
                function beforeImprimir(op,page) {
                    xajax_imprimir(op,page,'msg-myModal');
                    $('#myModalImp').modal('show');
                }
        
                function mover(){                
                    regSel=$("#tLista tbody input[type=checkbox]").is(":checked");
                    if(regSel){
                        if (confirm('¿Seguro de mover registros seleccionados?')) {
                            var checked = []                    
                            $("input[name='sel[]']:checked").each(function ()
                            {
                                checked.push(parseInt($(this).val()));
                            });

                            xajax_setMover(checked);
                        }

                    } else {
                        alert('Seleccione un registro');
                    }
                }
                
		function excluir() {
			if (confirm('Eliminar registros seleccionados?')) {
				parent.content.document.frm.target = "controle";
				parent.content.document.frm.action = "<?=$myClass->getNameFile()."?control=".base64_encode(2)."&".$param->buildPars(false)?>";
				parent.content.document.frm.submit();
			}
		}
                        
                function proceder(idObj,rptPage) {
                    if (ObligaCampos(frmImp)){
                            ocultarObj(idObj,10);
                            parent.content.document.frmImp.target = "controle";
                            parent.content.document.frmImp.action = rptPage;
                            parent.content.document.frmImp.submit();
                    }   
                }
                        
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
                
                $(document).ready(function() {
                    $('.ls-modal').on('click', function(e){
                        e.preventDefault();
                        $('#myModal').modal('show').find('.modal-body').load($(this).attr('href'));
                    }); 
                });            

                window.cerrar = function(){
                    $('#myModal').modal('toggle');
                }; 

		function refrescar() {
                    parent.content.location.reload();
		}

	</script>
        <script type="text/javascript" src="../../library/js/jquerytablas3.js"></script>
        <?php 
        $xajax->printJavascript(PATH_INC.'ajax/');
	verif_framework(); 
        $calendar->load_files();	                
        ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("Datos Personales: B&uacute;squeda de Personas");

/* botones */
$button = new Button;

/*VERIFICO SI TIENE ACCESO A EDICION DE DATOS*/
$sistemaModulo=new sistemaModuloOpciones();
$sistemaModulo->whereID('020110ESCALAFON');
if(getSession("sis_userid")>1){
        $sistemaModulo->whereUserID(getSession("sis_userid"));
}
$sistemaModulo->setDatos();
    
//if($sistemaModulo->existeDatos()){//SI TIENE ACCESO A EDICION DE DATOS
    $button->addItem("Nuevo","Persona_edicion.php?clear=1&".$param->buildPars(false),"content");
    $button->addItem("Mover a Externos","javascript:mover()","content",2);    
    $button->addItem("Eliminar","javascript:excluir()","content",2);
//}

$button->addItem("Refrescar","javascript:refrescar()","content",2);

if($clear==2){
}else{
    echo $button->writeHTML();
}
//if($clear==2){
//    
//}else{
//    echo "<table width='100%' colspan=0><tr><td width='80%'>".$button->writeHTML()."</td><td width='20%' align=right>".  btnImprimirPersona()."</td></table>";        
//}
/* formulario de pesquisa */
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
$paramFunction->addParComplete('nbusc_char',$nbusc_char);

$depeid=$nbusc_depe_id;
$cadena=getSession("cadSearch");

/* Instancio la Dependencia */
$sqlDependencia=new dependenciaSuperior_SQLBox3(getSession("sis_depe_superior"));
$sqlDependencia=$sqlDependencia->getSQL();        

$form->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia,"nbusc_depe_id",$depeid,"-- Todas las Dependencia --","","","class=\"my_select_box\""));        
/*FIN Instancio*/


$tabla=new clsTabla_SQLlista();
$tabla->whereTipo('CONDICION_LABORAL');
$tabla->orderUno();
$sqlSituLabo=$tabla->getSQL_cbox();
$form->addField("Condici&oacute;n laboral: ",listboxField("Condici&oacute;n laboral",$sqlSituLabo, "nbusc_sitlaboral",$nbusc_sitlaboral,"-- Toda Condici&oacute;n Laboral --","onChange=\"xajax_pideCategoria(1,this.value,'divCategoria')\"","","class=\"my_select_box\""));

$form->addHtml("<tr><td colspan=2><div id='divCategoria'>\n");
$form->addHtml(pideCategoria(2,$nbusc_sitlaboral,'divCategoria'));
$form->addHtml("</div></td></tr>\n");

$sqlPeriodo = new clsPersona_SQLlista();
$sqlPeriodo->whereTipoPersona(1);//personal de la entidad
$sqlPeriodoAlta=$sqlPeriodo->getSQL_cboxPeriodoAlta();
$sqlPeriodoCese=$sqlPeriodo->getSQL_cboxPeriodoTermino();
$form->addField("Periodo Ingreso: ",listboxField("Periodo Ingreso",$sqlPeriodoAlta,"nbusc_periodo_alta","$nbusc_periodo_alta","-- Todos --","").
        "&nbsp;<b>Cese/Termino:</b>".listboxField("Periodo Cese_Termino",$sqlPeriodoCese,"nbusc_periodo_cese","","-- Todos --","").
        $calendar->make_input_field('Fecha Termino',array(),array('name'=> 'Dbusc_ftermino','value'=>'' ))
        );

$lista_nivel = array("1,ACTIVO","9,DE BAJA", "10, TODOS "); 
$form->addField("Estado:",radioField("Estado",$lista_nivel, "nbusc_plan_activo",$nbusc_plan_activo,"","H"));

$form->addField("DNI/Apellidos/Nombres: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena","$cadena",65,65)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">");

$cad="<TR><TD colspan=6><table border=0 cellpadding=0 cellspacing=0 class='FormTABLE'><tr>";
        for($i=65;$i<=90;$i++)
            $cad.="<td class='LabelTD'><font class='DataFONT'><input type=\"button\" style=\"font_size:6px\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado','".chr($i)."')\" value=\"".chr($i)."\">"."</font></td>";
        $cad.="</tr></table></TD></TR>";

$form->addHtml($cad);

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");
$formData['Sbusc_cadena']=$cadena;
$formData['nbusc_sitlaboral']=$nbusc_sitlaboral;
$formData['nbusc_categoria']=$nbusc_categoria;
$formData['nbusc_periodo_alta']=$nbusc_periodo_alta;
$formData['nbusc_clasificacion']=$nbusc_clasificacion;
$formData['nbusc_plan_activo']=$nbusc_plan_activo;
$formData['nbusc_depe_id']=$depeid;

$form->addHtml($myClass->buscar(2,$formData,encodeArray($paramFunction->getUrl()),1,'DivResultado'));

$form->addHtml("</div></td></tr>\n");
echo  $form->writeHTML();
?>  
    <div id="myModalImp" class="modal fade">
    <div class="modal-dialog modal-mg">
        <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><span id="title-myModal" class="glyphicon glyphicon-print" aria-hidden="true"></span>&nbsp;</h4>                    
                </div>
                <div id="msg-myModal" class="modal-body">
                    <p>Loading...</p>
                </div>

        </div>
    </div>    
    </div>     
    
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