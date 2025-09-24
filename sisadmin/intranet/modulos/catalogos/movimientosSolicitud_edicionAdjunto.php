<?
/*
	formulario de ingreso y modificacion
*/
include("../../library/library.php");
include("movimientosSolicitud_class.php"); 
/*
	verificacion del nivel de usuario
*/
verificaUsuario(1);

/*
	establecer conexion con la BD
*/
$conn = new db();
$conn->open();

/* Recibo los parametros con la clase de "paso de parametros por la URL"  */
$param= new manUrlv1();
$param->removePar('clear');
/*
	tratamiento de campos
*/
$id = getParam("relacionamento_id"); // captura la variable que viene del objeto lista
$clear = getParam("clear");
$busEmpty=getParam("busEmpty"); //permite o no buscar cadenas vacias (muestra todo los registros)
$numForm = getParam("numForm")?getParam("numForm"):0;//funciona solo con CLEAR=2, es el numero de formulario en el cual se encuentra el objeto desde donde fue llamado
$nBusc_grupo_id=getParam("nBusc_grupo_id"); 
$tipo=getParam("tipo"); 

require_once("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("getVinculo"); 
$xajax->registerFunction("poneDescrip"); 
$xajax->registerFunction("getSubtipoComponente"); 

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

	$vinculo=getDbValue("SELECT segr_vinculo FROM servicio_grupo WHERE segr_id=$grupo ");
	if($vinculo==2){ //vinculado a especialidades
		$sqlEspe="SELECT espe_id,espe_descripcion FROM especialidad WHERE espe_estado  ORDER BY 2";
		$oForm->addField("Especialidad: ",listboxField("Especialidad",$sqlEspe,"tr_espe_id",$bd_espe_id,"-- Seleccione Especialidad --","onChange=xajax_poneDescrip(this.value,1)"));
	}elseif($vinculo==4){ //vinculado a componentes
                $sqltipoComp="SELECT tabl_codigo,tabl_descripcion FROM tabla WHERE tabl_tipo='TIPO_COMPONENTE' ORDER BY 1";
                $oForm->addField("Tipo Componente",listboxField("Tipo Componente",$sqltipoComp,"tx_tabl_tipo_componente",$bd_tabl_tipo_componente,"-- Ninguno --","onChange=\"xajax_getSubtipoComponente(1,this.value,'divSubtipoComponente')\""));
        }else{
            $oForm->addHidden("___espe_id",NULL);
            $oForm->addHidden("___tabl_tipo_componente",NULL);
            
            if($vinculo==3){ //vinculado a perfiles
		$sqlPerfil="SELECT exam_id,exam_nombre FROM examen WHERE exam_estado='1' ORDER BY 2";
		$oForm->addField("Perfil: ",listboxField("Perfil",$sqlPerfil,"tx_exam_id",$bd_exam_id,"-- Seleccione Perfil --","onChange=xajax_poneDescrip(this.value,2)"));
            }
            
	}
		
	$contenido_respuesta=$oForm->writeHTML();

	if($op==1){
		$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);	
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


//funcion que obliga a ingresar la especialidad
function getSubtipoComponente($op,$bd_tabl_tipo_componente,$NameDiv){
				
	global $conn,$bd_tabl_subtipo_componente,$bd_tabl_fila_componente;

	$objResponse = new xajaxResponse();

	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");
        if($bd_tabl_tipo_componente==1)//CUARTEL
            $nombreTipo='NICHO';
        else
            $nombreTipo=getDbValue("SELECT tabl_descripcion FROM tabla WHERE tabl_tipo='TIPO_COMPONENTE' AND tabl_codigo=$bd_tabl_tipo_componente");
	
        if($bd_tabl_tipo_componente==1 or $bd_tabl_tipo_componente==3){ //Cuartel/Tumba
		$sqlTipoNicho="SELECT tabl_codigo,tabl_descripcion FROM tabla WHERE tabl_tipo='SUBTIPO_COMPONENTE' AND tabl_descripcion ILIKE '%$nombreTipo%'  ORDER BY 1";
		$oForm->addField("Sub Tipo: ",listboxField("Sub Tipo Componente",$sqlTipoNicho,"tx_tabl_subtipo_componente",$bd_tabl_subtipo_componente,"-- Todos --"));
	}

        if($bd_tabl_tipo_componente==1){//CUARTEL        
            $sqltipoNivel="SELECT tabl_codigo,tabl_descripcion FROM tabla WHERE tabl_tipo='NIVELES_COMPONENTE' ORDER BY 1";
            $oForm->addField("Fila",listboxField("Fila",$sqltipoNivel,"tx_tabl_fila_componente",$bd_tabl_fila_componente));
        }
        
        if($bd_tabl_tipo_componente==1 or $bd_tabl_tipo_componente==3){} //Cuartel        
	else{
            $oForm->addHidden("___tabl_subtipo_componente",NULL);
	}
        if($bd_tabl_tipo_componente==1){//CUARTEL        
        }else{
            $oForm->addHidden("___tabl_fila_componente",NULL);
        }	
        
	$contenido_respuesta=$oForm->writeHTML();

	if($op==1){
		$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);	
		return $objResponse;
	
	}else{
		return $contenido_respuesta	;
	}

}

$xajax->processRequests();
// fin para Ajax


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
		$bd_espe_id= $rs->field("espe_id");		
		$bd_exam_id= $rs->field("exam_id");				
                $bd_serv_precio = $rs->field("serv_precio");
		$bd_serv_estado = $rs->field("serv_estado")?1:0;		
                $bd_segr_vinculo= $rs->field("segr_vinculo");
		$bd_serv_estadopaciente = $rs->field("serv_estadopaciente");
                $bd_tabl_tipo_componente=$rs->field("tabl_tipo_componente");
                $bd_tabl_subtipo_componente=$rs->field("tabl_subtipo_componente");
                $bd_tabl_fila_componente=$rs->field("tabl_fila_componente");
                $bd_serv_sisteso=$rs->field("serv_sisteso");
		$bd_clas_id = $rs->field("clas_id");
		$bd_usua_id = $rs->field("usua_id");		
                $bd_username = $rs->field("username");
                $bd_username_actual = $rs->field("username_actual");
                
        }
}
else 	$bd_serv_estado = 1;

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
			document.frm.action = "../imes/guardar.php?_op=RecServ&nomeCampoForm=<?=getParam("nomeCampoForm")?>&busEmpty=<?=$busEmpty?>&numForm=<?=$numForm?>&clear=<?=$clear?>&nBusc_grupo_id=<?=$nBusc_grupo_id?>&tipo=<?=$tipo?>";			
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
<? 
if($tipo=="M")
    pageTitle("Edici&oacute;n de Servicio M&eacute;dico","");
elseif(inlist($tipo,"T,S"))
    pageTitle("Edici&oacute;n de Servicios","");
else
    pageTitle("Edici&oacute;n de Transacci&oacute;n","");

/*
	botones,
	configure conforme suas necessidades
*/
$retorno = $_SERVER['QUERY_STRING'];

$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2);

if($clear==1)
	$button->addItem(" Regresar ","catalogosServicios_buscar.php?busEmpty=$busEmpty&nBusc_grupo_id=$nBusc_grupo_id&tipo=$tipo","content");
else
	$button->addItem(" Salir sin Guardar ","javascript:if(confirm('Seguro de Salir sin Guardar?')){parent.parent.close()}","content");
	
echo $button->writeHTML();

/*
	Control de abas,
	true, se for a aba da pagina atual,
	false, se for qualquer outra aba,
	configure conforme al exemplo de abajo
*/

$abas = new Abas();
$abas->addItem("General",false,"movimientosSolicitud_edicion.php?id=$relacionamento_id&".$param->buildPars(false));

$abas->addItem("Detalles de Productos",true);

echo $abas->writeHTML();

echo "<br>";

/*
	Formulario
*/
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$bd_serv_codigo); // clave primaria
$form->addHidden("pagina",getParam("pagina")); // numero de p�gina que llamo

// definici�n de lookup avanzada


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
$form->addField("Grupo: ",listboxField("Grupo del Servicio",$sqlGServicio,"nr_segr_id",$bd_segr_id,'-- Seleccione Grupo de Servicio --',"onChange=\"xajax_getVinculo(1,this.value,'DivEspecialidad')\"","","class=\"my_select_box\""));

$form->addHtml("<tr><td colspan=2><div id='DivEspecialidad'>\n");
$form->addHtml(getVinculo(2,$bd_segr_id,$bd_serv_codigo,'DivEspecialidad'));
$form->addHtml("</div></td></tr>\n");

$form->addHtml("<tr><td colspan=2><div id='divSubtipoComponente'>\n");
$form->addHtml(getSubtipoComponente(2,$bd_tabl_tipo_componente,'divSubtipoComponente'));
$form->addHtml("</div></td></tr>\n");
                
$form->addField("Descripci&oacute;n: ",textField("Descripci&oacute;n","Sr_serv_descripcion",$bd_serv_descripcion,80,80));

if(inlist($tipo,"M,T,S")){
    $form->addField("Precio: ",numField("Precio","nr_serv_precio",$bd_serv_precio,15,10,2,true));
    $form->addField("C&oacute;digo SISTESO: ",numField("Cod.SISTESO","zr_serv_sisteso",$bd_serv_sisteso,5,5,0,false));
    
    $sqlEspeIng="SELECT clas_id,clas_id||' '||clas_descripcion
			FROM clasificador
			WHERE clas_tipo='1' AND clas_especifica=1  
			ORDER BY clas_id  ";
    
}else{
    if(!$tipo)
        $form->addField("C&oacute;digo SISTESO: ",numField("Cod.SISTESO","zr_serv_sisteso",$bd_serv_sisteso,5,5,0,false));    
    
    $sqlEspeIng="SELECT clas_id,clas_id||' '||clas_descripcion
			FROM clasificador
			WHERE clas_tipo='2' AND clas_especifica=1  
			ORDER BY clas_id  ";
}			

//$form->addField("Espec&iacute;fica: ",listboxField("Espec&iacute;fica",$sqlEspeIng,"tr_clas_id",$bd_clas_id,"-- Seleccione Espec&iacute;fica --"));
$form->addField("Espec&iacute;fica: ",listboxField("Espec&iacute;fica",$sqlEspeIng,"tr_clas_id",$bd_clas_id,"-- Seleccione Espec&iacute;fica --","", "","class=\"my_select_box\""));


//solo si es edicion se agrega los datos de auditoria
if($id) {
        $form->addBreak("<b>Estado</b>");
        $form->addField("Activo: ",checkboxField("Activo","hx_serv_estado",1,$bd_serv_estado==1));
	$form->addBreak("<b>Control</b>");
	$form->addField("Responsable: ",$bd_username);
	$form->addField("Actualizado: ",$bd_username_actual);        
}else{
        $form->addHidden("hx_serv_estado",1); // numero de p�gina que llamo    
}

echo $form->writeHTML();
?>
    
<script>    
    $('.my_select_box').chosen({
        disable_search_threshold: 5, //SE DESHABILITA SI SOLO HAY 5 REGISTROS
        allow_single_deselect: true,
        search_contains: true,
        no_results_text: 'Oops, No Encontrado!'
        });
</script>                                                        
</body>
</html>
<?
/*
	cierro la conexi�n a la BD
*/
$conn->close();
