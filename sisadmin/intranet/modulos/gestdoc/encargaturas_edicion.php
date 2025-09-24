<?php
/* formulario de ingreso y modificacion */
include("../../library/library.php");

/* verificacion del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("encargaturas_class.php");
include("../catalogos/catalogosDependencias_class.php");
include("registroDespacho_class.php");

/* establecer conexion con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista

$myClass = new encargaturas($id,'DELEGAR MI CARGO');

if (strlen($id)>0) { // edición
    $myClass->setDatos();
    if($myClass->existeDatos()){
        $bd_enca_id= $myClass->field('enca_id');
        $bd_enca_motivo=$myClass->field('enca_motivo');
        $bd_pers_id=$myClass->field('pers_id');
        $bd_depe_id=$myClass->field('depe_id');
        $bd_dependencia=$myClass->field('depe_nombre');
        $bd_empleado=$myClass->field('encargado');
        $bd_cargo=$myClass->field('encargado_cargo');
        $bd_enca_encargado=$myClass->field('enca_encargado');
        $bd_usua_id=$myClass->field('usua_id');
        $username= $myClass->field("username");
    }
}else{
    $bd_cant_columnas=1;
}

// Para Ajax
require_once("../../library/ajax/xajax.inc.php");

$xajax = new xajax();
$xajax->registerFunction("getExpediente");

function getExpediente($op,$exp_id) {
    global $conn;

    $objResponse = new xajaxResponse();
    
    $otable = new AddTableForm();
    $otable->setLabelWidth("20%");
    $otable->setDataWidth("80%");
    
    if($exp_id>intval($exp_id)){   
        $expediente = new despacho_SQLlista($exp_id);
        $expediente->whereID($exp_id);
        $expediente->setDatos();
        $button2 = new Button;        
        if($expediente->existeDatos()){
            $otable->addField("Documento: ",$expediente->field('tiex_abreviado').' '.$expediente->field('num_documento'));
            $otable->addField("Fecha: ",dtos($expediente->field('desp_fecha')));
            $otable->addField("Asunto: ",$expediente->field('desp_asunto'));

        }else{
            $otable->addField("","<font color=red>NUMERO DE ".NAME_EXPEDIENTE_UPPER." NO HALLADO</font>");
        }
        $contenido_respuesta = $otable->writeHTML();

    }else{
        $otable->addField("","<font color=red>INGRESE EL NUMERO COMPLETO</font>");
        $contenido_respuesta = $otable->writeHTML();
    }  
    if($op==1){
        $objResponse->addAssign('divExpediente','innerHTML', $contenido_respuesta);
        return $objResponse;
    }else{
        return $contenido_respuesta;
    }
    


}
$xajax->processRequests();
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="../../library/js/focus.js"></script>	
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>
        <script language="javascript" src="<?php echo PATH_INC?>js/lookup2.js"></script>      
        
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>   
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>
	
	<script language='JavaScript'>
            
            function salvar(idObj) {
                    if (ObligaCampos(frm)){
                            ocultarObj(idObj,10)
                            parent.content.document.frm.target = "controle";
                            parent.content.document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
                            parent.content.document.frm.submit();
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

            /*
                funcion que define el foco inicial en el formulario
            */
            function inicializa() {
                    document.frm.Sr_enca_motivo.focus();
            }

	</script>
        <?php
            verif_framework(); 
            $xajax->printJavascript('../../library/ajax/');
	 ?>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("EDICION DE ".$myClass->getTitle());

/* Formulario */
//$form = new Form("frm", $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false), "POST", "controle", "100%",false);
$form = new Form("frm", "", "POST", "controle", "100%");
$form->addHidden("f_id",$id); // clave primaria

/* botones */
$button = new Button;
$button->addItem(" Regresar ","encargaturas_buscar.php".$param->buildPars(true),"content");
$form->addHtml($button->writeHTML());


if($id){
    $form->addField("C&oacute;digo: ",str_pad($bd_enca_id,3,'0',STR_PAD_LEFT));
}

$form->addField("Motivo: ",textField("Motivo","Sr_enca_motivo",$bd_enca_motivo,80,120));
$form->addField("Referencia Num.".NAME_EXPEDIENTE.": ",numField("Referencia_Expediente","nx_desp_id","$bd_desp_id",12,12,3,"","onChange=\"xajax_getExpediente(1,this.value)\""));
$form->addHtml("<tr><td colspan=2><div id='divExpediente'>\n");

if($bd_desp_id){
    $form->addHtml(getExpediente(2,"$bd_desp_id"));
}

$form->addHtml("</div></td></tr>\n");                
if(!$id){
    /* Instancio la Dependencia */
    $dependencia=new dependencia_SQLlista();
    $dependencia->wherePdlaID(getSession("sis_persid"));
    $dependencia->whereHabilitado();
    $sqlDependencia=$dependencia->getSQL_cbox();

    //FIN OBTENGO
    $form->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia,"nr_depe_id",$bd_depe_id,"",""));

    // definición de lookup Empleados
    $empleado= new Lookup();
    $empleado->setTitle("Empleados");
    $empleado->setNomeCampoChave("pers_id");
    $empleado->setNomeCampoForm("Empleado","nr_pers_id");
    $sql = "SELECT  pers_id,
                    pers_apellpaterno || ' ' || pers_apellmaterno || ' ' || pers_nombres as nombres,
                    pers_dni 
                    FROM persona a ";

    setSession("sqlLkupEmp", $sql);
    $empleado->setNomeTabela("sqlLkupEmp");  //nombre de tabla
    $empleado->setNomeCampoExibicao("trim(pers_apellpaterno),trim(pers_apellmaterno),trim(pers_nombres)");  // Campos en los que deseo se efect�e la b�squeda.
    $empleado->setUpCase(true);//para busquedas con texto en mayuscula
    $empleado->setListaInicial(0);
    $empleado->setSize(70);
    $empleado->setValorCampoForm($bd_pers_id);
    $form->addField("Delegar A: ",$empleado->writeHTML());    

}else{    
    $form->addField("Dependencia: ",$bd_dependencia);
    $form->addField("Delegado A: ", $bd_empleado);
}
$form->addField(checkboxField("Puesto como encargado(a)","hx_enca_set_encargado",1,$bd_enca_encargado==1),"Puesto como encargado(a)");

$button = new Button;
$button->align("L");
$button->addItem(" Delegar ","javascript:salvar('Delegar')","content",2);
//$button->addItem(" Delegar ","",'',2,"$usua_id","","submit");
$form->addField("",$button->writeHTML());

//if(strlen($id)) {
//	$form->addBreak("<b>Control</b>");
//	$form->addField("Creado por: ",$username);
//}

echo $form->writeHTML();
?>
</body>
</html>

<?php
/* cierro la conexion a la BD */
$conn->close();