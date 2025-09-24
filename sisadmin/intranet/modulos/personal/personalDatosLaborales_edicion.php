<?php
/* formulario de ingreso y modificaci�n */
include($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("personalDatosLaborales_class.php");
include("../catalogos/CargoClasificado_class.php");
include("../catalogos/catalogosDependencias_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista

$myClass = new clsDatosLaborales($id,'Datos Laborales');

if (strlen($id)>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){

            $bd_pdla_id=$myClass->field("pdla_id");
            $bd_pers_id=$myClass->field("pers_id");
            $bd_depe_id=$myClass->field("depe_id");
            $bd_tabl_idsitlaboral=$myClass->field("tabl_idsitlaboral");
            $bd_tabl_cargoestructural=$myClass->field("tabl_cargoestructural");
            $bd_tabl_profesion=$myClass->field("tabl_profesion");
            $bd_pdla_cargofuncional=$myClass->field("pdla_cargofuncional");
            $bd_pdla_resolingresoestado=$myClass->field("pdla_resolingresoestado");
            $bd_pdla_fecharesolingreso=dtos($myClass->field("pdla_fecharesolingreso"));
            $bd_pdla_fechaingreso=dtos($myClass->field("pdla_fechaingreso"));
            $bd_rela_id=$myClass->field("rela_id");
            $bd_repe_id=$myClass->field("repe_id");
            $bd_cate_id=$myClass->field("cate_id");
            $bd_cacl_id=$myClass->field("cacl_id");
            $bd_afp_id=$myClass->field("afp_id");
            $bd_pdla_cus=$myClass->field("pdla_cus");
            $bd_pdla_afpafiliacion=$myClass->field("pdla_afpafiliacion");
            $bd_pdla_fdesafiliacion=$myClass->field("pdla_fdesafiliacion");
            $bd_pdla_resdesafiliacion=$myClass->field("pdla_resdesafiliacion");
            $bd_tabl_idtipopension=$myClass->field("tabl_idtipopension");
            $bd_pdla_cargodecese=$myClass->field("pdla_cargodecese");
            $bd_tabl_parentesco=$myClass->field("tabl_parentesco");
            $bd_pers_persid=$myClass->field("pers_persid");
            $bd_pdla_estado=$myClass->field("pdla_estado");
            $bd_pdla_firma=$myClass->field("pdla_firma");
                    
            $bd_pdla_fechadocumento=dtos($myClass->field("pdla_fechadocumento"));
            $bd_pdla_documento=$myClass->field("pdla_documento");
                    

            $bd_pdla_fecharegistro=$myClass->field("pdla_fecharegistro");
            $bd_pdla_actualusua=$myClass->field("pdla_actualusua");
            $bd_pdla_actualfecha=$myClass->field("pdla_actualfecha");
            $bd_depe_nombre=$myClass->field("depe_nombre");
            $bd_empleado=$myClass->field("empleado");
            $bd_pers_dni=$myClass->field("pers_dni");
            
            $bd_sit_laboral=$myClass->field("sit_laboral");
            $bd_cargo_estructural=$myClass->field("cargo_estructural");
            $bd_pdla_encargado=$myClass->field("pdla_encargado");
            $username= $myClass->field('username');
            $usernameactual= $myClass->field('usernameactual');
            $bd_usua_id = $myClass->field("usua_id");
        }

}else{ // Si es nuevo
        $bd_tabl_idsitlaboral=1;
        $bd_pdla_estado=1;
}

/* Archivos para calendario */
require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("pideDatosLaborales");
$xajax->registerFunction("pideSPP");
$xajax->registerFunction("pideVinculo");

function pideDatosLaborales($op,$sitLab,$NameDiv)
{
	global $calendar,$conn,$id,$bd_cate_id,$bd_cacl_id,$bd_comp_id,$bd_rela_id,$bd_repe_id,$bd_comp_id,
               $bd_tabl_cargoestructural,$bd_cargo_estructural,$bd_pdla_resolingresoestado,$bd_pdla_fecharesolingreso,
               $bd_pdla_cargofuncional,$bd_pdla_fechadocumento,$bd_pdla_documento;

	$objResponse = new xajaxResponse();

	$oForm = new AddTableForm();
	$oForm->setLabelWidth("20%");
	$oForm->setDataWidth("80%");
	$calendar->calendar_correla=5;

        switch($sitLab){
            case 9:// pensionistas
                //$sqlTPens = "SELECT tabl_id, tabl_descripcion FROM tabla WHERE tabl_tipo='TIPOS_DE_PENSION' ORDER BY 1 "; //diferente al tipo de pension 'JUBILACION'
		//$oForm->addField("Tipo de Pensi&oacute;n: ",listboxField("Tipo de Pensión",$sqlTPens,"tr_tabl_idtipopension",$bd_tabl_idtipopension,"-- Seleccione Valor --","onChange=\"xajax_pideVinculo(1,this.value,'DivVinculo')\""));
		//$oForm->addHtml("<tr><td colspan=2><div id='DivVinculo'>\n");
		//$oForm->addHtml(pideVinculo(2,$bd_tabl_idtipopension,'DivVinculo'));
		//$oForm->addHtml("</div></td></tr>\n");
                break;

            case 35://invitado
                break;
            
            default:
                //case 1: //NOMBRADO
                //case 2: //CARGO DE CONFIANZA
                //case 4: //CONTRAT.SERV.PERSONALES
                //case 5: //CONTRAT.SERV.NO PERSON.
                //case 8: //MIEMBROS DEL DIRECTORIO
                //case 15: //CAS
//                $sqlCargoEstr = "SELECT tabl_id, tabl_descripcion as Descripcion
//                                    FROM tabla WHERE tabl_tipo='CARGO_ESTRUCTURAL' ORDER BY 2 ";
//                $oForm->addField("Cargo Estructural: ",listboxField("Cargo Estructural",$sqlCargoEstr, "tr_tabl_cargoestructural",$bd_tabl_cargoestructural));


                //oForm->addField("Fecha Efectiva de Ingreso: ", $calendar->make_input_field('Fecha Efectiva de Ingreso',array(),array('name'=> 'Dx_pers_fechaingreso','value'=> $bd_pers_fechaingreso)));
                
//                $cargo=new clsCargoClasificado_SQLlista();
//                $sqlCargoClasificado=$cargo->getSQL_cbox();
//                $oForm->addField("Cargo Clasificado: ",listboxField("Cargo Clasificado",$sqlCargoClasificado,"tr_cacl_id",$bd_cacl_id,"-- Seleccione Cargo Clasificado --","", "","class=\"my_select_box\""));
                    
                $oForm->addField("Cargo Funcional: <font color=red>*</font>",textField("Cargo Funcional","Sr_pdla_cargofuncional",$bd_pdla_cargofuncional,80,80));

                $oForm->addField("Fecha de Documento: ", $calendar->make_input_field('Fecha Documento',array(),array('name'=> 'Dx_pdla_fechadocumento','value'=> $bd_pdla_fechadocumento)));
                $oForm->addField("Documento: ",textField("Documento","Sx_pdla_documento",$bd_pdla_documento,80,80));
		// definición de lookup
		//$cat = new Lookup();
		//$cat->setTitle("Categor&iacute;as Remunerativas");
		//$cat->setNomeCampoForm("Cat.Remunerativa","nr_cate_id");
		//$cat->setNomeTabela("categoria");  //nombre de tabla
		//$cat->setNomeCampoChave("cate_id");  //campo clave
		//$cat->setNomeCampoExibicao("cate_descricorta");
		//$cat->setNomeCampoAuxiliar("cate_descripcion"); // opcional
		//$cat->setUpCase(true);//para busquedas con texto en mayuscula
		//$cat->setValorCampoForm($bd_cate_id);
		//$cat->setListaInicial(0); // Para que no se muestre una Lista Inicial al cargar el popup
		//$oForm->addField("Cat.Remunerativa: ",$cat->writeHTML());

		// definición de lookup Cargo Clasificado
		//$cargo = new Lookup();
		//$cargo->setTitle("Cargos Clasificados");
		//$cargo->setNomeCampoForm("Cargo Clasificado","nr_cacl_id");
		//$cargo->setNomeTabela("cargo_clasificado");  //nombre de tabla
		//$cargo->setNomeCampoChave("cacl_id");  //campo clave
		//$cargo->setNomeCampoExibicao("cacl_descripcion");
		//$cargo->setNomeCampoAuxiliar("cacl_codigo"); // opcional
		//$cargo->setUpCase(true);//para busquedas con texto en mayuscula
		//$cargo->setValorCampoForm($bd_cacl_id);
		//$oForm->addField("Cargo Clasificado: ",$cargo->writeHTML());

		// definición de lookup Componente presupuestal
		//$comp = new Lookup();
		//$comp->setTitle("Componente Presupuestal");
		//$comp->setNomeCampoChave("comp_id");   // campo clave
		//$comp->setNomeCampoForm("Componente","nr_comp_id");
		//$sql = "select a.comp_id,'['||a.comp_mnemonico||'-'||a.peri_anno||'] '||a.comp_cadena,a.comp_descripcion,b.depe_nombrecorto "
                //    . "from componente a "
                //    . "left join dependencia b on b.depe_id=a.depe_ejecutora ";

                //if(getSession("sis_userid")!=1){//si no es administrador
                //    $sql = $sql . "where a.depe_ejecutora=".getSession("sis_ejecid")." and a.peri_anno=".date("Y");
		//}
		//else
                //    $sql = $sql . "where a.peri_anno=".date("Y");

		//setSession("sqlLkupComp", $sql);
		//$comp->setNomeTabela("sqlLkupComp");  //nombre de tabla
		//$comp->setNomeCampoExibicao("b.depe_nombrecorto,a.comp_mnemonico,a.comp_descripcion");
		//$comp->setUpCase(true);//para busquedas con texto en mayuscula
		//$comp->setValorCampoForm($bd_comp_id);
		//$comp->setSize(60);
		//$comp->setWidth(700);
		//$oForm->addField("Componente Presupuestal: ",$comp->writeHTML());

		//$sqlRegLaboral = "SELECT rela_id as id, rela_descripcion as Descripcion FROM regimen_laboral ORDER BY rela_descripcion";
		//$oForm->addField("R&eacute;gimen Laboral: ",listboxField("R&eacute;gimen Laboral",$sqlRegLaboral, "tr_rela_id",$bd_rela_id,"",""));

                //$sqlRegPension = "SELECT repe_id as id, repe_descripcion as Descripcion FROM regimen_pensionario ORDER BY repe_descripcion";
		//$oForm->addField("R&eacute;gimen pensionario: ",listboxField("R&eacute;gimen pensionario",$sqlRegPension, "tr_repe_id",$bd_repe_id,"-- Seleccione R&eacute;gimen Pensionario--","onChange=\"xajax_pideSPP(1,this.value,'DivSPP')\""));
                break;
        }

        $contenido_respuesta=$oForm->writeHTML();

	$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);

	if($op==1)
		return $objResponse;
	else
		return $contenido_respuesta	;

}

function pideSPP($op,$value,$NameDiv)
{
	global $conn,$calendar,$bd_pdla_cus,$bd_afp_id,$bd_pdla_afpafiliacion,$bd_pdla_resdesafiliacion,$bd_pdla_fdesafiliacion;

	$objResponse = new xajaxResponse();

	if($value==1){ /*si es afp*/
		$calendar->calendar_correla=6;
		$oForm = new AddTableForm();
		$oForm->setLabelWidth("20%");
		$oForm->setDataWidth("80%");

		$sqlAfp = "SELECT afp_id as id, afp_nombre FROM afp ORDER BY 2";
		$oForm->addField("AFP Actual: <font color=red>*</font>",listboxField("AFP Actual",$sqlAfp, "tr_afp_id",$bd_afp_id,"-- Seleccione AFP --",""));

		$oForm->addField("C&oacute;digo &uacute;nico SPP: ",textField("C&oacute;digo &uacute;nico SPP","Sx_pdla_cus",$bd_pdla_cus,20,15));
		$oForm->addField("Fecha Inicial de Afiliciaci&oacute;n: ", $calendar->make_input_field('Fecha Inicial de Afiliación',array(),array('name'=> 'Dx_pdla_afpafiliacion','value'=> $bd_pdla_afpafiliacion )));

		$contenido_respuesta=$oForm->writeHTML();
	}
	elseif($value==3){ /*si es 19990*/
		$calendar->calendar_correla=6;
		$oForm = new AddTableForm();
		$oForm->setLabelWidth("20%");
		$oForm->setDataWidth("80%");

		$oForm->addField("Fecha de desafiliciaci&oacute;n: ", $calendar->make_input_field('Fecha de desafiliación',array(),array('name'=> 'Dx_pdla_fdesafiliacion','value'=> $bd_pdla_fdesafiliacion)));
		$oForm->addField("Documento de desafiliciaci&oacute;n: ",textField("Documento de desafiliación","Sx_pdla_resdesafiliacion",$bd_pdla_resdesafiliacion,50,50));

		$contenido_respuesta=$oForm->writeHTML();
	}
	else
		$contenido_respuesta="";

	$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);

	if($op==1)
		return $objResponse;
	else
		return $contenido_respuesta	;
}

function pideVinculo($op,$value,$NameDiv)
{
	global $conn,$bd_cate_id,$bd_pers_cargodecese,$bd_tabl_idparentesco,$bd_pers_persid,$readonly;

	$objResponse = new xajaxResponse();
	switch ($value){
		case 150://Jubilación
			$oForm = new AddTableForm();
			$oForm->setLabelWidth("20%");
			$oForm->setDataWidth("80%");

			// definición de lookup
			$cat = new Lookup();
			$cat->setTitle("Categor&iacute;as Remunerativas");
			$cat->setNomeCampoForm("Cat.Remunerativa de Cese","nr_cate_id");
			$cat->setNomeTabela("categoria");  //nombre de tabla
			$cat->setNomeCampoChave("cate_id");  //campo clave
			$cat->setNomeCampoExibicao("cate_descricorta");
			$cat->setNomeCampoAuxiliar("cate_descripcion"); // opcional
			$cat->setUpCase(true);//para busquedas con texto en mayuscula
			$cat->readOnly(iif($readonly,'==','readonly',true,false));//para hacer el campo solo de lectura
			$cat->setValorCampoForm($bd_cate_id);
			$cat->setListaInicial(0); // Para que no se muestre una Lista Inicial al cargar el popup
			$oForm->addField("Cat.Remunerativa de Cese: <font color=red>*</font>",$cat->writeHTML());

			$oForm->addField("Cargo Clasificado de Cese: ",textField("Cargo Clasificado de Cese","Sx_pdla_cargodecese",$bd_pdla_cargodecese,50,50));

			$contenido_respuesta=$oForm->writeHTML();
			break;

		case 151://viudez
			$oForm = new AddTableForm();
			$oForm->setLabelWidth("20%");
			$oForm->setDataWidth("80%");

			// definición de lookup Empleados
			$emp = new Lookup();
			$emp->setTitle("Empleados");
			$emp->setNomeCampoChave("pers_id");
			$emp->setNomeCampoForm("Empleado","nx_pers_persid");
			$sql = "select pers_id,pers_apellpaterno || ' ' || pers_apellmaterno || ' ' || pers_nombres as nombres "
				 . "from persona a "
				 . "where a.pers_id!=1 ";

			$emp->setStringBusqueda("select pers_id,pers_apellpaterno || ' ' || pers_apellmaterno || ' ' || pers_nombres as nombres from persona");  // Basta solo con esta línea ya que el where se coloca en la clase, por lo que es necesario siempre setear $emp->setNomeCampoChave("pers_id");
			setSession("sqlLkupEmp", $sql);
			$emp->setNomeTabela("sqlLkupEmp");  //nombre de tabla
			$emp->setNomeCampoExibicao("pers_apellpaterno,pers_apellmaterno,pers_nombres");  // Campos en los que deseo se efectúe la búsqueda.
			$emp->setUpCase(true);//para busquedas con texto en mayuscula
			$emp->setListaInicial(0); // Para que no se muestre una Lista Inicial al cargar el popup
			$emp->setValorCampoForm($bd_pers_persid);
			$oForm->addField("Titular: ",$emp->writeHTML());
			$contenido_respuesta=$oForm->writeHTML();
			break;

		default://orfandad,de gracia,montepio,etc
			$oForm = new AddTableForm();
			$oForm->setLabelWidth("20%");
			$oForm->setDataWidth("80%");

			// definición de lookup Empleados
			$emp = new Lookup();
			$emp->setTitle("Empleados");
			$emp->setNomeCampoChave("pers_id");
			$emp->setNomeCampoForm("Empleado","nx_pers_persid");
			$sql = "select pers_id,pers_apellpaterno || ' ' || pers_apellmaterno || ' ' || pers_nombres as nombres "
				 . "from persona a "
				 . "where a.pers_id!=1  ";

			$emp->setStringBusqueda("select pers_id,pers_apellpaterno || ' ' || pers_apellmaterno || ' ' || pers_nombres as nombres from persona");  // Basta solo con esta línea ya que el where se coloca en la clase, por lo que es necesario siempre setear $emp->setNomeCampoChave("pers_id");
			setSession("sqlLkupEmp", $sql);
			$emp->setNomeTabela("sqlLkupEmp");  //nombre de tabla
			$emp->setNomeCampoExibicao("pers_apellpaterno,pers_apellmaterno,pers_nombres");  // Campos en los que deseo se efectúe la búsqueda.
			$emp->setUpCase(true);//para busquedas con texto en mayuscula
			$emp->setListaInicial(0); // Para que no se muestre una Lista Inicial al cargar el popup
			$emp->setValorCampoForm($bd_pers_persid);
			$oForm->addField("Titular: ",$emp->writeHTML());

			$sqlParentesco = "SELECT tabl_id as id, tabl_descripcion as Descripcion FROM tabla WHERE tabl_tipo='PARENTESCOS' ORDER BY tabl_descripcion ";
			$oForm->addField("Parentesco: ",listboxField("Parentesco",$sqlParentesco, "tx_tabl_idparentesco",$bd_tabl_idparentesco,"-- Seleccione Valor --",""));
			$contenido_respuesta=$oForm->writeHTML();
			break;

	}
	$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);

	if($op==1)
		return $objResponse;
	else
		return $contenido_respuesta	;
}


$xajax->processRequests();

?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="<?php echo PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>
	<script language="javascript" src="<?php echo PATH_INC?>js/lookup2.js"></script>
	<script language="javascript" src="<?php echo PATH_INC?>js/tree.js"></script>
	
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                

        
	<script language='JavaScript'>
	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			parent.content.document.frm.target = "content";
			parent.content.document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
			parent.content.document.frm.submit();
		}
	}

	function refrescar(idObj,ID) {
            ocultarObj(idObj,5)
            parent.content.document.frm.target = "controle";
            parent.content.document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(3)."&ID="?>"+ID;
            parent.content.document.frm.submit();
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
            document.frm.tr_tabl_idsitlaboral.focus();
	}

	</script>
	<?php
            $xajax->printJavascript(PATH_INC.'ajax/');
            verif_framework();
            $calendar->load_files();	
        ?>

</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("Edici&oacute;n de ".$myClass->getTitle());


/* botones */
$button = new Button;
if($id){
    //$button->addItem(" Refrescar Notificador ","javascript:refrescar('Refrescar Notificador','$id')","content",2);
}
//$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2,$bd_usua_id);

$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2);
$button->addItem(" Regresar ","personalDatosLaborales_buscar.php".$param->buildPars(true),"content");

echo $button->writeHTML();

/* Control de fichas */
$abas = new Abas();
$abas->addItem("General",true);
if (strlen($id)>0) { // si es edición
	$abas->addItem("Movimientos",false,"personalDatosLaborales_movimientosLista1n.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));
}
echo $abas->writeHTML();
echo "<br>";

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->setUpload(true);
$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$bd_pdla_id); // clave primaria
$form->addHidden("pagina",$pg); // numero de página que llamo

if($id){
    $form->addField("Persona: ",$bd_empleado.'/'.$bd_pers_dni);
}
else{
    // definición de lookup Empleados
    $empleado= new Lookup();
    $empleado->setTitle("Empleados");
    $empleado->setNomeCampoChave("pers_id");
    $empleado->setNomeCampoForm("Empleado","nr_pers_id");
    $sql = "SELECT pers_id,pers_apellpaterno || ' ' || pers_apellmaterno || ' ' || pers_nombres as nombres,pers_dni "
             . "FROM persona a ";

    setSession("sqlLkupEmp", $sql);
    $empleado->setNomeTabela("sqlLkupEmp");  //nombre de tabla
    $empleado->setNomeCampoExibicao("trim(pers_apellpaterno),trim(pers_apellmaterno),trim(pers_nombres),pers_dni");  // Campos en los que deseo se efect�e la b�squeda.
    $empleado->setUpCase(true);//para busquedas con texto en mayuscula
    $empleado->setListaInicial(0);
    $empleado->setSize(70);
    $empleado->setValorCampoForm($bd_pers_id);
    $form->addField("Persona: <font color=red>*</font>",$empleado->writeHTML());
}

    $sqlSituLabo = "SELECT tabl_id, tabl_descripcion as Descripcion
                    FROM catalogos.tabla WHERE tabl_tipo='CONDICION_LABORAL' ORDER BY tabl_codigo ";

    $form->addField("Condici&oacute;n Laboral: <font color=red>*</font>",listboxField("Situaci&oacute;n laboral",$sqlSituLabo, "tr_tabl_idsitlaboral",$bd_tabl_idsitlaboral,"","onChange=\"xajax_pideDatosLaborales(1,this.value,'divPideDatosLaborales')\"","", "","class=\"my_select_box\""));

    $sqlDependencia=new dependencia_SQLlista();
    $sqlDependencia->whereDepeTodos(getSession("sis_depe_superior"));
    $sqlDependencia=$sqlDependencia->getSQL_cbox2B();
    $form->addField("Dependencia: <font color=red>*</font>",listboxField("Dependencia",$sqlDependencia, "tr_depe_id",$bd_depe_id,"-- Seleccione Dependencia --","", "","class=\"my_select_box\""));
                
    // definición de arbol
    // definición de arbol para dependencia superior
//        $depen = new Tree();
//        $depen->setTitle("Jerarqu&iacute;a de Dependencias");
//        $depen->setTreeAvanz(true); //utiliza el tree mejorado
//        $depen->setNameCampoForm("Dependencia:","nr_depe_id");
//        $depen->setNameTabla("func_treedependencia(0,_SEARCH_)");  //nombre de la funcion ->OJO ABRE Y ENVO UN PARAMETRO, DEBIDO A QUE SE TIENE QUE INCLUIR EL VALOR DE BUSUQUEDA
//        $valor=getDbValue("SELECT depe_id::text||' '||depe_nombre FROM dependencia WHERE depe_id=$bd_depe_id");
//        $depen->setValorCampoForm($bd_depe_id,$valor);
//        
//    $form->addField("Dependencia: ",$depen->writeHTML());


    $form->addHtml("<tr><td colspan=2><div id='divPideDatosLaborales'>\n");
    $form->addHtml(pideDatosLaborales(2,$bd_tabl_idsitlaboral,'divPideDatosLaborales'));
    $form->addHtml("</div></td></tr>\n");

    $form->addHtml("<tr><td colspan=2><div id='DivSPP'>\n");
    $form->addHtml(pideSPP(2,$bd_repe_id,'DivSPP'));
    $form->addHtml("</div></td></tr>\n");

    $form->addField(checkboxField("Puesto como encargado(a)","hx_pdla_set_encargado",1,$bd_pdla_encargado==1),"Puesto como encargado(a)");

    
    $form->addHidden("postPath",'datos_laborales/'.SIS_EMPRESA_RUC.'/');    
    $form->addField("Img Firma: ",fileField("Img Firma","pdla_firma" ,$bd_pdla_firma,80,"onchange=validaextension(this,'JPG,PNG')",PUBLICUPLOAD.'datos_laborales/'.SIS_EMPRESA_RUC.'/'));
    
if($id){
    $sqlEstado = array(1 => "ACTIVO",
                       9 => "DE BAJA");
    $form->addField("Estado: ", listboxField("Estado ", $sqlEstado, "nr_pdla_estado", $bd_pdla_estado));

    $form->addBreak("<b>Control</b>");
    $form->addField("Creado por: ",$username.'-'.$bd_pdla_fecharegistro);
    
    if($usernameactual){
        $form->addField("Actualizado por: ",$usernameactual.'-'.$bd_pdla_actualfecha);
    }
}

echo $form->writeHTML();
?>
</body>
</html>
    <script>
    $(".my_select_box").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true
            });
    
    </script>    
<?php
/* cierro la conexión a la BD */
$conn->close();