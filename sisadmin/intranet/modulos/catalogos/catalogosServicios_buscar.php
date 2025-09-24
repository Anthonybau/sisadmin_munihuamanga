<?php
/*
	Modelo de pagina que apresenta um formulario con crit�rios de busqueda
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

$tipo = getParam("tipo");

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
$xajax->registerFunction("imprimir");
$xajax->registerFunction("getVinculo");
$xajax->registerExternalFunction(array("buscar", "servicios","buscar"),"");

//funcion que obliga a ingresar la especialidad
function muestra_subGrupo($op,$valor,$NameDiv){
				
	global $conn;

	$objResponse = new xajaxResponse();

	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");

        $gripo=new clsGrupoTTra_SQLlista();
        $gripo->whereID($valor);
        $gripo->setDatos();
        $segr_tipo=$gripo->field('segr_tipo');
        if(strpos($segr_tipo,'T')>=0 && strpos($segr_tipo,'T')!== false){

            
//            $bd_emru_id=getSession('SET_EMRU_EMISOR');
//            $sqlRUC=new clsEmpresaRUC_SQLlista();
//            if($bd_emru_id){
//                $sqlRUC->whereID($bd_emru_id);
//            }
//            $sqlRUC->orderUno();
//            $sql=$sqlRUC->getSQL_cbox();
//
//            if($bd_emru_id){
//                $oForm->addField("RUC: ",listboxField("RUC",$sql,"nBusc_ruc_id","$bd_emru_id"));
//            }else{
//                $oForm->addField("RUC: ",listboxField("RUC",$sql,"nBusc_ruc_id","$bd_emru_id",'-- Todos --',"","","class=\"my_select_box\""));
//            }
            

            $oForm->addHidden("nBusc_ruc_id","");
        }else{
            $oForm->addHidden("nBusc_ruc_id","");
            //$oForm->addHidden("nBusc_tipo_igv","");
            //$oForm->addHidden("hx_sin_asientos","");
            //$oForm->addHidden("hx_sin_componente","");
        }



	$sqlSGrupo="SELECT sesg_id,sesg_descripcion FROM servicio_sgrupo WHERE segr_id=$valor ORDER BY 2" ;	
	$oForm->addField("Sub Grupo: ",listboxField("Sub Grupo",$sqlSGrupo,"nBusc_sgrupo_id","",'-- Seleccione Sub Grupo de Servicio --',"","","class=\"my_select_box\""));        

	$contenido_respuesta=$oForm->writeHTML();

	if($op==1){
            $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);	            
            $objResponse->addScript("$('.my_select_box').select2({
                                        placeholder: 'Seleccione un elemento de la lista',
                                        allowClear: true,
                                        width: '90%',
                                    });");
            
            return $objResponse;	
	}else{
            return $contenido_respuesta	;
	}
}


function imprimir($NameDiv)
{
    global $conn,$nbusc_depe_id;
    $objResponse = new xajaxResponse();

    $objResponse->addScript('$( "#title-myModal" ).text( " Imprimir")');            
    $form = new Form("frmImp", "", "POST", "controle", "100%",true);
    $form->setLabelWidth("20%");
    $form->setDataWidth("80%");

    $bd_depeid=$nbusc_depe_id;

    /* Instancio la Dependencia */
    $sqlDependencia=new dependenciaSuperior_SQLBox(getSession("sis_depe_superior"));
    $sqlDependencia=$sqlDependencia->getSQL();        

    //FIN OBTENGO
    $form->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia,"nbusc_depe_id",$bd_depeid,"-- Todas las Dependencia --","","","class=\"my_select_box\""));        
    
    $sqlGServicio=new clsGrupoTTra_SQLlista();
    $sqlGServicio=$sqlGServicio->getSQL();
    $form->addField("Grupo: ",listboxField("Grupo",$sqlGServicio,"nBusc_grupo_id","",'-- Seleccione Grupo de Servicio --',"onChange=\"xajax_getVinculo(1,this.value,'DivEspecialidad');\"","","class=\"my_select_box\""));    

    $form->addHtml("<tr><td colspan=2><div id='DivEspecialidad'>\n");
    $form->addHtml(getVinculo(2,'','DivEspecialidad'));
    $form->addHtml("</div></td></tr>\n");

    if(SIS_EMPRESA_TIPO!=4){//diferente a empresa de almacenes
        $form->addField("Ocultar Precios: ",checkboxField("Ocultar Precios","hx_ocultar_precios",1,0));
    }else{
        $form->addHidden("hx_ocultar_precios",1);
    }
    
    $form->addField("","<a href=\"#\" onClick=\"javascript:proceder( 'Proceder','catalogosServicios_imprimir.php?destino=1' )\" ><img src='../../img/pdf.png' border='0' title='Listar en PDF'></a>&nbsp;".
                       "<a href=\"#\" onClick=\"javascript:proceder( 'Proceder','catalogosServicios_imprimir.php?destino=2' )\" ><img src='../../img/xls.png' border='0' title='Descargar en XLS'></a>");

    $contenido_respuesta=$form->writeHTML();
    $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);   
    $objResponse->addScript("$('.my_select_box').select2({
                                        placeholder: 'Seleccione un elemento de la lista',
                                        allowClear: true,
                                        width: '90%',
                                    });");
                    
    return $objResponse;
}

function getVinculo($op,$grupo,$NameDiv){
				
	global $conn;
        
	$objResponse = new xajaxResponse();

	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");
	
        
	$sqlSGrupo="SELECT sesg_id,sesg_descripcion FROM servicio_sgrupo WHERE segr_id=$grupo order by 2" ;	
	$oForm->addField("Sub Grupo: ",listboxField("Sub Grupo",$sqlSGrupo,"nBusc_sgrupo_id","",'-- Seleccione Sub Grupo --',"","","class=\"my_select_box\""));    

        $contenido_respuesta=$oForm->writeHTML();
        
	if($op==1){
		$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);	
                $objResponse->addScript("$('.my_select_box').select2({
                                        placeholder: 'Seleccione un elemento de la lista',
                                        allowClear: true,
                                        width: '90%',
                                    });");
                
		return $objResponse;
	
	}else{
		return $contenido_respuesta	;
	}

}
$xajax->processRequests();
// fin para Ajax

?>

<html>
<head>
	<title>Busquedas de Productos/Servicios</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="javascript" src="../../library/js/checkall.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>
        <script type="text/javascript" src="../../library/jquery/jquery-1.9.js"></script>
        <script type="text/javascript" src="../../library/jquery/jquery-ui.js"></script>
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>
        
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap4/bootstrap.min.css">
        <script src="../../library/bootstrap4/bootstrap.bundle.min.js"></script>        
                
	<script language="JavaScript">

	<?php
	if ($clear==2) {
		/* funcion q se activa si esta pagina es llamada desde la busqueda avanzada (AvanzLookup) */
		echo "function update(valor, descricao, numForm) {
				parent.opener.parent.parent.content.document.forms[numForm]._Dummy".getParam("nomeCampoForm").".value = descricao;
				parent.opener.parent.parent.content.document.forms[numForm].".getParam("nomeCampoForm").".value = valor;
				parent.opener.parent.parent.content.document.forms[numForm].__Change_".getParam("nomeCampoForm").".value = 1;
				parent.parent.close();
				}";
	}
	?>
	/*
		funcion que define el foco inicial del formulario
	*/
	function inicializa() {
		// inicia el foco en el primer campo
		parent.content.document.frm.Sbusc_cadena.focus();
	}

	function excluir() {
		if (confirm('Eliminar registros selecionados?')) {
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "../imes/eliminar.php?_op=RecServ&busEmpty=<?=$busEmpty?>&dbrev=<?=$dbrev?>&tipo=<?=$tipo?>";
			parent.content.document.frm.submit();
			}
		}
	
        function clonar() {
                regSel=$("#tLista tbody input[type=checkbox]").is(":checked");
                if(regSel){
                    var checked = []                    
                    $("input[name='sel[]']:checked").each(function ()
                    {
                        checked.push(parseInt($(this).val()));
                    });
                    parent.content.document.frm.target = "content";
                    parent.content.document.frm.action = 'catalogosServicios_edicion.php?id_clonar='+checked[0]+'&clear=1';
                    parent.content.document.frm.submit();
                } else {
                    alert('Seleccione un registro');
                }
            }
        
        function limpiar_publicados() {
		if (confirm('Eliminar registros Publicados?')) {
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "../imes/eliminar.php?_op=PublicServ&busEmpty=<?=$busEmpty?>&dbrev=<?=$dbrev?>&tipo=<?=$tipo?>";
			parent.content.document.frm.submit();
			}
		}
                
	function openList(key) {
		var oKey = parent.content.document.getElementById(key);
		var icone = parent.content.document.getElementById('fold_'+key);
		if (oKey.style.visibility == "hidden"){
			oKey.style.visibility = "visible";
			oKey.style.display = "block";
			icone.innerHTML = "&nbsp;-&nbsp;";
			
		} else {
			oKey.style.visibility = "hidden";
			oKey.style.display = "none";
			icone.innerHTML = "&nbsp;+&nbsp;";
		}
	}

        function beforeImprimir() {
            xajax_imprimir('msg-myModalImp');
            $('#myModalImp').modal('show');                    
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
        
        function proceder(idObj,rptPage) {
            if (ObligaCampos(frmImp)){
                //ocultarObj(idObj,10);
                parent.content.document.frmImp.target = "controle";
                parent.content.document.frmImp.action = rptPage;
                parent.content.document.frmImp.submit();
            }   
        }
                
	</script>
    <?php $xajax->printJavascript(PATH_INC.'ajax/'); ?>
	<?php verif_framework(); ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php

    pageTitle("B&uacute;squeda de Productos/Servicios","");

/*
	botones,
*/
$button = new Button;
if ($clear==2){
	$button->addItem(LOOKUP_RESET,"javascript:update('','',$numForm)","content",0,0,"link"); //cambio el stylo solo a este boton
}
$valClear=$clear?$clear:1;
if($valClear==1 or ($valClear==2 && $nuevo==1))
//          if(inlist($tipo,"M,T,S"))
            $nuevo="Nuevo";
//        else
//            $nuevo="Nueva Transacci&oacute;n";

        if(SIS_EMPRESA_RUC=='20487663230'){//CLINICA USAT
            $button->addItem("Actualizar desde USAT","javascript:alert('Proceso Terminado!')","content",2);
        }else{
            $button->addItem(" $nuevo ","catalogosServicios_edicion.php?clear=$valClear&nomeCampoForm=".getParam("nomeCampoForm")."&busEmpty=$busEmpty&numForm=$numForm&tipo=$tipo","content");
        }
//if($valClear==1) //el eliminar solo funciona cuando es llamado desde euna opcion del menu
        $button->addItem("Clonar","javascript:clonar()","content",2);
	$button->addItem("Eliminar","javascript:excluir()","content",2);
        
        //$button->addItem("Limpiar Publicados","javascript:limpiar_publicados()","content",2);
        
        $button->addItem(" Imprimir ","javascript:beforeImprimir()","content");
        //$button->addItem(" XLS ","javascript:Imprimir(2)","content");
        
	//$button->addItem('Imprimir', '#', "", "","", "", "", "windowOpen") ;

echo $button->writeHTML();


$form = new Form("frm");
$form->setMethod("POST");
//$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s");

$sqlDependencia=new dependenciaSuperior_SQLBox2(getSession("SET_DEPE_EMISOR"));
$sqlDependencia=$sqlDependencia->getSQL();        

//if(!$bd_depe_id){    
//    $bd_depe_id=getDbValue("SELECT id FROM ($sqlDependencia) AS x ORDER BY 1 LIMIT 1");     
//}

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

//if(SIS_PEDIDOS>0){
//    $form->addField("Filtrar Publicados: ",checkboxField("Publicar","hx_busc_publicados",0,0));
//}else{
//    $form->addHidden("hx_busc_publicados","");
//}

if(SIS_SISCONT>99){    
    $tabla=new clsTabla_SQLlista();
    $tabla->whereTipo('FASE_ASIENTO_CONTABLE');
    $sqlGrupoContable=$tabla->getSQL_cbox3();   
    $form->addField("Fase de Asiento: ",listboxField("Fase de Asiento",$sqlGrupoContable,"nBusc_fase_asiento","","-- Seleccione Fase de asiento--","","","class=\"my_select_box\""));
    
    $tabla=new clsTabla_SQLlista();
    $tabla->whereTipo('TIPO_ASIENTO_CONTABLE');
    $sqlClaseContable=$tabla->getSQL_cbox3();   
    $form->addField("Tipo de Asiento: ",listboxField("Tipo de Asiento",$sqlClaseContable,"nBusc_tipo_asiento","","-- Seleccione Tipo de asiento--","","","class=\"my_select_box\""));
}else{
    $form->addHidden("nBusc_fase_asiento","");
    $form->addHidden("nBusc_tipo_asiento","");
}
if(SIS_EFACT==99){
    $tblTipoIGV=new clsTabla_SQLlista();
    $tblTipoIGV->whereTipo('TIPO_IGV');
    $tblTipoIGV->orderUno();
    $sql=$tblTipoIGV->getSQL_cboxCodigo();
    $form->addField("Tipo IGV: ",listboxField("Tipo IGV",$sql,"nBusc_tipo_igv","",'-- Todos --',""));
}else{
    $form->addHidden("nBusc_tipo_igv","");
}

if(SIS_SISCONT>99){                
            $form->addField("Sin Asientos Contables&nbsp;",checkboxField("Sin Asientos Contables","hx_sin_asientos",1,0)
                    ." Sin Componente Pptal.:".checkboxField("Sin Componente Pptal","hx_sin_componente",1,0));
}else{
    $form->addHidden("hx_sin_asientos",0);
    $form->addHidden("hx_sin_componente",0);   
}

$lista_nivel = array("1,ACTIVOS","9,INACTIVOS", "10, TODOS "); 
$nbusc_serv_activo=1;
$form->addField("Estado:",radioField("Estado",$lista_nivel, "nbusc_serv_activo",$nbusc_serv_activo,"","H"));

$js="xajax_buscar(1,document.frm.Sbusc_cadena.value,
                    document.frm.nbusc_depe_id.value,
                    document.frm.nBusc_ruc_id.value,
                    document.frm.nBusc_grupo_id.value,
                    document.frm.nBusc_sgrupo_id.value,
                    document.frm.nBusc_tipo_igv.value,
                    document.frm.hx_sin_asientos.checked,
                    document.frm.hx_sin_componente.checked,
                    document.frm.nBusc_tipo_asiento.value,
                    document.frm.nBusc_fase_asiento.value,
                    document.frm.nbusc_serv_activo.value,
                    '',
                    '$tipo',
                    '$colOrden',
                    '$busEmpty',
                    '$numForm',
                     1,
                     'DivResultado');document.getElementById('DivResultado').innerHTML = 'Espere, Buscando...'";
$buttom="<input type=\"button\" onClick=\"javascript:$js\"\" value=\"Buscar\">";        
$form->addField("C&oacute;digo/Descripci&oacute;n: ",searchField("Cadena de B&uacute;squeda","Sbusc_cadena",'',40,40,"onKeyPress=\"javascript:if(event.keyCode==13 && this.value!=''){ $js }\"")."&nbsp;$buttom");

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");

$form->addHtml($myClass->buscar(2,getSession("cadSearch"),
                "$bd_depe_id",
                "$bd_emru_id",
                "$nBusc_grupo_id",
                '',
                '',
                '',
                '',
                '',
                '',
                1,
                '',
                $tipo,
                $colOrden,
                $busEmpty,
                $numForm,1,'DivResultado'));

$form->addHtml("</div></td></tr>\n");
echo  $form->writeHTML();

?>
<div id="myModalImp" class="modal fade">
        <div class="modal-dialog modal-mg">
            <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><span id="title-myModalImp" class="" aria-hidden="true">&nbsp;</span></h4>                    
                    </div>
                    <div id="msg-myModalImp" class="modal-body">
                        <p>Loading...</p>
                    </div>

            </div>
        </div>    
</div>    
    
<script>    
    $('.my_select_box').select2({
            placeholder: 'Seleccione un elemento de la lista',
            allowClear: true,
            width: '90%',
    });
</script>                                                            
</body>
</html>

<?php
/*
	cierra la conexion a la BD, no debe ser alterado
*/
$conn->close();