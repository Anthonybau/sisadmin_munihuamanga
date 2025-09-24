<?php
/*
	formulario de ingreso y modificaci�n
*/
include("../../library/library.php");
include("catalogosServicios_class.php");
include("../admin/datosEmpresaRUC_class.php");
include("../catalogos/catalogosServiciosPrecios_class.php");
include("../siscopp/siscoppAperturasAcumulados_clases.php");
include("./catalogosTabla_class.php");
/*
	verificacion del nivel de usuarioprecio:
 * 
*/
verificaUsuario(1);

/*
	establecer conexi�n con la BD
*/
$conn = new db();
$conn->open();

/* Recibo los par�metros con la clase de "paso de par�metros por la URL"  */
$param= new manUrlv1();
$param->removePar('clear');
$myClass = new servicios ($id,"Edici&oacute;n de Bien/Servicio");
/*
	tratamiento de campos
*/
$id = getParam("id"); // captura la variable que viene del objeto lista
$clear = getParam("clear");
$busEmpty=getParam("busEmpty"); //permite o no buscar cadenas vacias (muestra todo los registros)
$numForm = getParam("numForm")?getParam("numForm"):0;//funciona solo con CLEAR=2, es el numero de formulario en el cual se encuentra el objeto desde donde fue llamado
$nBusc_grupo_id=getParam("nBusc_grupo_id"); 
$tipo=getParam("tipo"); 

if (strlen($id)>0) { // edici�n

	$sql=new servicios_SQLlista();
	$sql->whereID($id);
        $sql=$sql->getSQL();
        
	$rs = new query($conn, $sql);
	if ($rs->getrow()) {
                $bd_serv_id=$rs->field("serv_id");
		$bd_serv_codigo = $rs->field("serv_codigo");
		$bd_segr_id = $rs->field("segr_id");
		$bd_sesg_id = $rs->field("sesg_id");
		$bd_serv_descripcion = $rs->field("serv_descripcion");
                $bd_serv_breve = $rs->field("serv_breve");
                $bd_serv_periodicidad = $rs->field("serv_periodicidad");
                $bd_tipl_id = $rs->field("tipl_id");
                $bd_tabl_vinculado = $rs->field("tabl_vinculado");
                $bd_tabl_tipoconcepto = $rs->field("tabl_tipoconcepto");
                $bd_serv_porcentaje=$rs->field("serv_porcentaje");
                $bd_serv_formula=$rs->field("serv_formula");
                $bd_serv_plame=$rs->field("serv_plame");

                $bd_serv_pensionable=$rs->field("serv_pensionable");
                $bd_serv_conafovicer=$rs->field("serv_conafovicer");
                $bd_serv_sctr=$rs->field("serv_sctr");
                $bd_serv_cts=$rs->field("serv_cts");
                $bd_serv_editable=$rs->field("serv_editable");
                $bd_serv_ir5ta=$rs->field("serv_ir5ta");
                $bd_serv_ir5ta_meses_proyecta=$rs->field("serv_ir5ta_meses_proyecta");
                $bd_serv_formula2=$rs->field("serv_formula2");
                $bd_serv_descripcion_cts=$rs->field("serv_descripcion_cts");
                $bd_serv_automatico=$rs->field("serv_automatico");
                $bd_serv_adjunto_id=$rs->field("serv_adjunto_id");
                        
		$bd_espe_id= $rs->field("espe_id");		
		$bd_exam_id= $rs->field("exam_id");				
                $bd_tabl_tipoprecio=$rs->field("tabl_tipoprecio");
                $bd_serv_precio = $rs->field("serv_precio");
		$bd_serv_estado = $rs->field("serv_estado")?1:0;		
                $bd_segr_vinculo= $rs->field("segr_vinculo");
		$bd_serv_estadopaciente = $rs->field("serv_estadopaciente");
                $bd_tabl_tipo_componente=$rs->field("tabl_tipo_componente");
                $bd_tabl_subtipo_componente=$rs->field("tabl_subtipo_componente");
                $bd_tabl_fila_componente=$rs->field("tabl_fila_componente");
                $bd_serv_sisteso=$rs->field("serv_sisteso");
		$bd_clas_id = $rs->field("clas_id");
                $bd_emru_id= $rs->field("emru_id");
                $bd_serv_codigo_aux= $rs->field("serv_codigo_aux");
                        
                $bd_serv_umedida=$rs->field("serv_umedida");
                $bd_tabl_tipo_igv=$rs->field("tabl_tipo_igv");
                $bd_serv_aplica_ajuste=$rs->field("serv_aplica_ajuste");
                $bd_serv_muestra_min=$rs->field("serv_muestra_min");
                
                $bd_serv_porcen_utilidad=$rs->field("serv_porcen_utilidad");
                $bd_serv_equi_unidades=$rs->field("serv_equi_unidades");
                $bd_serv_stockminimo=$rs->field("serv_stockminimo");
                $bd_serv_preciocosto=$rs->field("serv_preciocosto");
                $comp_id=$rs->field("comp_id");
                $bd_serv_porcent_detraccion=$rs->field("serv_porcent_detraccion");
                
		$bd_usua_id = $rs->field("usua_id");		
                $nameUsers = $rs->field("username");
                $nameUsersActual = $rs->field("username_actual");
                
                $fregistro=$rs->field("serv_fregistro");
                $fregistroActual=$rs->field("serv_actualfecha");
        }
}
else {
    $bd_serv_estado = 1;
    $bd_tabl_tipo_igv=1;

    $sqlRUC=new clsEmpresaRUC_SQLlista();
    if(getSession('SET_EMRU_EMISOR')){
        $sqlRUC->whereID(getSession('SET_EMRU_EMISOR'));
    }
    $sqlRUC->orderUno();
    $sql=$sqlRUC->getSQL_cbox();
    
    $bd_emru_id= getDbValue("SELECT emru_id FROM (".$sqlRUC->getSQL().") AS a LIMIT 1");    
    
    //$bd_serv_equi_unidades=1;
    $bd_serv_stockminimo=1;
    $bd_serv_preciocosto=0;    
    $bd_serv_porcent_detraccion=0;     
}    

require_once("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("getVinculo"); 
$xajax->registerFunction("getVinculo_otros"); 
$xajax->registerFunction("poneDescrip"); 
$xajax->registerFunction("eligeTipoPrecio"); 

//funcion que obliga a ingresar la especialidad
function getVinculo($op,$grupo,$NameDiv){
				
	global $conn,$bd_espe_id,$bd_exam_id,$bd_sesg_id,$bd_tabl_tipo_componente;

	$objResponse = new xajaxResponse();

	//if($op==1){
	//  $codServ=getDbValue("SELECT lpad($grupo::TEXT,2,'0')||lpad(COALESCE(max(substr(serv_codigo,3)),'0')::integer+1::TEXT,3,'0')
	//					  FROM servicio
  	//		  			  WHERE segr_id=$grupo");
	//  $objResponse->addScript("document.frm.zr_serv_codigo.value='".$codServ."'");
	//}
	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");
	
	$sqlSGrupo="SELECT sesg_id,sesg_descripcion FROM servicio_sgrupo WHERE segr_id=$grupo order by 2" ;	
	$oForm->addField("Sub Grupo: ",listboxField("Sub Grupo",$sqlSGrupo,"nr_sesg_id",$bd_sesg_id,'-- Seleccione Sub Grupo de Servicio --'));

        
        $contenido_respuesta=$oForm->writeHTML();
        
	if($op==1){
		$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);	
        
		return $objResponse;
	
	}else{
		return $contenido_respuesta	;
	}

}


function getVinculo_otros($op,$grupo,$NameDiv){
				
	global $conn,$bd_serv_umedida,$bd_serv_precio,$bd_serv_codigo,
                $bd_serv_codigo_aux,$bd_tabl_tipo_igv,
                $bd_serv_aplica_ajuste,$bd_clas_id,$bd_serv_equi_unidades,
                $bd_serv_stockminimo,$bd_serv_preciocosto,$bd_tabl_tipoprecio,
                $bd_serv_porcen_utilidad,$comp_id;

	$objResponse = new xajaxResponse();

	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");

        $sqlGrupo=new clsGrupoTTra_SQLlista();
        $sqlGrupo->whereID($grupo);
        $sqlGrupo->setDatos();
        
        $destino=$sqlGrupo->field("segr_destino");
        $segr_almacen=$sqlGrupo->field("segr_almacen");
        
        if($destino==1) {//INGRESOS

            $oForm->addField("U.Medida: ",textField("U.Medida","Sr_serv_umedida",$bd_serv_umedida,20,20));
            
            $sqltipoPrecio="SELECT tabl_id,tabl_descripcion FROM tabla WHERE tabl_tipo='TIPO_PRECIO' ORDER BY 1";
            $oForm->addField("Tipo de Precio: ",listboxField("Tipo Precio",$sqltipoPrecio,"tr_tabl_tipoprecio",$bd_tabl_tipoprecio,"","onChange=\"xajax_eligeTipoPrecio(1,this.value,'$bd_serv_codigo','$segr_almacen','divTipoPrecio')\""));

            
            $sql = "SELECT tabl_codigo, tabl_descripcion FROM tabla WHERE tabl_tipo='TIPO_IGV' ORDER BY 1";
            $rs = new query($conn, $sql);
            while ($rs->getrow()) {
                $lista_tipo_igv[].=$rs->field("tabl_codigo").",".  $rs->field("tabl_descripcion");
            }   

            $oForm->addField("Tipo IGV: ",radioField("Tipo IGV",$lista_tipo_igv, "xr_tabl_tipo_igv",$bd_tabl_tipo_igv,"","H"));        
            
            if(SIS_EMPRESA_TIPO==1){//Privada
                $oForm->addField("C&oacute;digo Auxiliar: ",textField("Cod.Auxiliar","Sx_serv_codigo_aux",$bd_serv_codigo_aux,20,20));            
            }
            
            $sqlEspecifica="SELECT clas_id,clas_id||' '||clas_descripcion
                                            FROM clasificador
                                            WHERE clas_tipo='1' AND clas_especifica=1  
                                            ORDER BY clas_id  ";
            
        }else{
            $sqlEspecifica="SELECT clas_id,clas_id||' '||clas_descripcion
                                            FROM clasificador
                                            WHERE clas_tipo='2' AND clas_especifica=1  
                                            ORDER BY clas_id  ";

        }
        
        
        if(inlist(SIS_EMPRESA_TIPO,'2,3') && inlist($destino,'1,2')){//empresas publicas, beneficencias, destino ingresos/egresos
            
            $oForm->addField("Clasificador: ",listboxField("Clasificador",$sqlEspecifica,"tr_clas_id",$bd_clas_id,"-- Seleccione Espec&iacute;fica --","", "","class=\"my_select_box\""));
            
            if($destino=='1'){//INGRESOS
                $sqlComponente=new clsComponente_SQLlista();
                $sqlComponente=$sqlComponente->getSQL_componente();
                $oForm->addField("Componente: ",listboxField("Componente:",$sqlComponente,"tr_comp_id",$comp_id,"-- Seleccione Componente --","","","class=\"my_select_box\""));        
            }
        }

        if($destino==1) {//INGRESOS
            $oForm->addField("Equivalencia en Unidades: ",numField("Equivalencia en Unidades","nr_serv_equi_unidades",$bd_serv_equi_unidades,6,6,0));
        }
        
        if($segr_almacen==1){
            $oForm->addField("Stock M&iacute;nimo: ",numField("Stock Mínimo","nx_serv_stockminimo",$bd_serv_stockminimo,6,6,2));
        }
        
        $contenido_respuesta=$oForm->writeHTML();
        
	if($op==1){
		$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);	
                $objResponse->addScript("$('.my_select_box').chosen({
                        disable_search_threshold: 5, //SE DESHABILITA SI SOLO HAY 5 REGISTROS
                        allow_single_deselect: true,
                        search_contains: true,
                        no_results_text: 'Oops, No Encontrado!',
                        width: '50%'
                        });");
        
		return $objResponse;
	
	}else{
		return $contenido_respuesta	;
	}

}

//funci�n que filtra y muestra los datos segun el tipo de paciente
function poneDescrip($value,$op)
{
	global $conn;
	
	$objResponse = new xajaxResponse();
	if($op==1)//especialidad
		$descrip=GetDbValue("SELECT espe_descripcion FROM especialidad WHERE espe_id=$value ");		
	else
		$descrip=GetDbValue("SELECT exam_nombre FROM examen WHERE exam_id=$value ");	
	
	$objResponse->addScript("document.frm.Sr_serv_descripcion.value=!document.frm.Sr_serv_descripcion.value?document.frm.Sr_serv_descripcion.value='".$descrip."':document.frm.Sr_serv_descripcion.value");	
    // Se devuelve el objeto, que este dara todas las instruccione JS para realizar la tarea
	return $objResponse;

}


$xajax->processRequests();
// fin para Ajax




?>
<html>
<head>
	<title>Servicio M&eacute;dico-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="javascript" src="../../library/js/lookup.js"></script>
	<script language="javascript" src="../../library/js/janela.js"></script>	
	<script language="JavaScript" src="../../library/js/focus.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>	
        <script type="text/javascript" src="../../library/jquery/jquery-1.9.js"></script>
        <script type="text/javascript" src="../../library/jquery/jquery-ui.js"></script>
        <link rel="stylesheet" href="../../library/jquery-chosen/chosen.css">
        <script src="../../library/jquery-chosen/chosen.jquery.js" type="text/javascript"></script>                
	<script language='JavaScript'>
	/*
		funci�n guardar
	*/
        
        function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			document.frm.target = "controle";
			document.frm.action = "<?=$myClass->getNameFile()."?control=".base64_encode(10)."&".$param->buildPars(false)?>";
			document.frm.submit();
			
		}
	}        
        
	/*
		se invoca desde la funcion obligacampos (libjsgen.js)
		en esta funci�n se puede personalizar la validaci�n del formulario
		y se ejecuta al momento de gurdar los datos
	*/	
	function mivalidacion(frm) {  
		return true			
	}
	
	/*
		funci�n que define el foco inicial en el formulario
	*/
	function inicializa() {
		parent.content.document.frm.zr_serv_codigo.focus();
	}
                
	</script>
    <?php $xajax->printJavascript(PATH_INC.'ajax/'); ?>
	<? verif_framework(); ?>		
	
</head>
<body class="contentBODY">
<?php
pageTitle($myClass->getTitle());

/*
	botones,
	configure conforme suas necessidades
*/
$retorno = $_SERVER['QUERY_STRING'];

$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2);
	
echo $button->writeHTML();

/*
	Formulario
*/
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s"); // variable de control

// definici�n de lookup avanzada

if($tipo=='T'){
    $sqlRUC=new clsEmpresaRUC_SQLlista();
    if(getSession('SET_EMRU_EMISOR')){
        $sqlRUC->whereID(getSession('SET_EMRU_EMISOR'));
    }
    $sqlRUC->orderUno();
    $sqlRUC=$sqlRUC->getSQL_cbox();
    $form->addField("RUC: ",listboxField("RUC",$sqlRUC,"nr_emru_id","$bd_emru_id",'-- Todos --',"","","class=\"my_select_box\""));
}

if ($id>0){  // si es edici�n 
	$form->addField("C&oacute;digo: ",$bd_serv_id);
//	$nameGrupo=getDbValue("SELECT segr_descripcion FROM servicio_grupo WHERE segr_id=$bd_segr_id");	
//	$form->addField("Grupo: ",$nameGrupo);
}
else {
//	$form->addField("C&oacute;digo: ",numField("C&oacute;digo","zr_serv_codigo",$bd_serv_codigo,10,5,0));
}

$sqlGServicio=new clsGrupoTTra_SQLlista();
$sqlGServicio=$sqlGServicio->getSQL_servicioGrupo($tipo);
$form->addField("Grupo: ",listboxField("Grupo",$sqlGServicio,"nr_segr_id",$bd_segr_id,'-- Seleccione Grupo de Servicio --',"onChange=\"xajax_getVinculo(1,this.value,'DivEspecialidad');xajax_getVinculo_otros(1,this.value,'divDestino')\"","","class=\"my_select_box\""));

$form->addHtml("<tr><td colspan=2><div id='DivEspecialidad'>\n");
$form->addHtml(getVinculo(2,$bd_segr_id,$bd_serv_codigo,'DivEspecialidad'));
$form->addHtml("</div></td></tr>\n");
                
$form->addField("Descripci&oacute;n: ",textField("Descripci&oacute;n","Sr_serv_descripcion",$bd_serv_descripcion,100,120));


$form->addHtml("<tr><td colspan=2><div id='divDestino'>\n");
$form->addHtml(getVinculo_otros(2,$bd_segr_id,$bd_serv_codigo,'divDestino'));
$form->addHtml("</div></td></tr>\n");


//solo si es edicion se agrega los datos de auditoria
if($id) {
        $form->addBreak("<b>Estado</b>");
        $form->addField("Activo: ",checkboxField("Activo","hx_serv_estado",1,$bd_serv_estado==1));
	$form->addBreak("<b>Control</b>");
        $form->addField("Creado: ",$nameUsers.'/'.substr($fregistro,0,19));
        $form->addField("Actualizado: ",$nameUsersActual.'/'.substr($fregistroActual,0,19));        

}else{
        $form->addHidden("hx_serv_estado",1); // numero de p�gina que llamo    
}

echo $form->writeHTML();
echo $button->writeHTML();

?>
    
<script>    
    $('.my_select_box').chosen({
        disable_search_threshold: 5, //SE DESHABILITA SI SOLO HAY 5 REGISTROS
        allow_single_deselect: true,
        search_contains: true,
        no_results_text: 'Oops, No Encontrado!',
        width: '50%'
        });
</script>                                                        
</body>
</html>
<?php
/*
	cierro la conexi�n a la BD
*/
$conn->close();
