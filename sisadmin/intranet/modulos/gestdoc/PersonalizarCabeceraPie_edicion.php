<?php
/* formulario de ingreso y modificacion */
include("../../library/library.php");

/* verificacion del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("PersonalizarCabeceraPie_class.php");
include("tamanoPagina_class.php");
include("../catalogos/catalogosDependencias_class.php");
/* establecer conexion con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista

$myClass = new personalizarCabeceraPie($id,'Personalizar Encabezado y P&iacute;e de P&aacute;gina');

if (strlen($id)>0) { // edición
    $myClass->setDatos();
    if($myClass->existeDatos()){
        $bd_pcpi_id= $myClass->field('pcpi_id');
        $bd_tapa_id=$myClass->field('tapa_id'); 
        $bd_depe_id=$myClass->field('depe_id');
        $bd_dependencia=$myClass->field('dependencia');
        $bd_pcpi_cabecera=$myClass->field('pcpi_cabecera');
        $bd_pcpi_pie=$myClass->field('pcpi_pie');
        $bd_usua_id=$myClass->field('usua_id');
        $bd_pcpi_estado=$myClass->field('pcpi_estado');
        $username=$myClass->field("username");
        $bd_pcpi_fregistro=$myClass->field("pcpi_fregistro");
        $usernameactual=$myClass->field("usernameactual");
        $bd_pcpi_actualfecha=$myClass->field("pcpi_actualfecha");        
    }
}

// Para Ajax
require_once("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("setPath");

function setPath($valor)
{
	$objResponse = new xajaxResponse();
        $path='catalogos/'.SIS_EMPRESA_RUC."/".$valor;
        $objResponse->addScript("document.frm.postPath.value='$path'");
	return $objResponse;

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
        
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>   
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>
        
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                
        <script language="JavaScript" src="../../library/js/textcounter.js"></script>	
	<script language='JavaScript'>
                function salvar(idObj) {
                    if (ObligaCampos(frm)){
                            ocultarObj(idObj,10)
                            document.frm.target = "content";
                            document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
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
                funcion que define el foco inicial en el formulario
            */
            function inicializa() {
                    document.frm.tr_depe_id.focus();
            }

	</script>
        <?php
            verif_framework(); 
            $xajax->printJavascript('../../library/ajax/');
	 ?>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle($myClass->getTitle());

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setUpload(true);

$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("f_id",$id); // clave primaria

/* botones */
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2);
$button->addItem(" Regresar ","PersonalizarCabeceraPie_buscar.php".$param->buildPars(true),"content");
echo $button->writeHTML();


if($id){
    $form->addField("ID: ",str_pad($bd_pcpi_id,3,'0',STR_PAD_LEFT));
    $form->addField("Dependencia: ",$bd_depe_id.' '.$bd_dependencia);
    
}else{
    /* Instancio la Dependencia */
    $sqlDependencia=new dependencia_SQLBox(getSession("sis_depe_superior"));
    $sqlDependencia=$sqlDependencia->getSQL();        

    $form->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia,"tr_depe_id",$bd_depe_id,"-- Seleccione Dependencia --","onChange=\"xajax_setPath(this.value)\"","","class=\"my_select_box\"  "));
    /* Fin Instancio */
}

$tamanoPagina=new tamanoPagina_SQLlista();
$tamanoPagina->orderUno();
$sqlTamanoPagina=$tamanoPagina->getSQL();
$form->addField("Tamaño de P&aacute;gina: ",listboxField("Tamaño de Pagina",$sqlTamanoPagina,"nr_tapa_id",$bd_tapa_id,'-- Tamaño de P&aacute;gina --',"","","class=\"my_select_box\"  "));


$form->addHidden("postPath","catalogos/".SIS_EMPRESA_RUC."/".$bd_depe_id);
    
$form->addField("Encabezado:",fileField("Encabezado","pcpi_cabecera" ,"$bd_pcpi_cabecera",60,"onchange=validaextension(this,'PNG')",iif($bd_pcpi_cabecera,"==","","",PUBLICUPLOAD.'/catalogos/'.SIS_EMPRESA_RUC.'/'.$bd_depe_id.'/')));
$form->addField("Pie:",fileField("Pie","pcpi_pie" ,"$bd_pcpi_pie",60,"onchange=validaextension(this,'PNG')",iif($bd_pcpi_pie,"==","","",PUBLICUPLOAD.'/catalogos/'.SIS_EMPRESA_RUC.'/'.$bd_depe_id.'/')));

        
if (strlen($id)>0) { // edición
    $form->addField("Activo: ",checkboxField("Activo","hx_pcpi_estado",1,$bd_pcpi_estado==1));
    $form->addField("Creado por: ",$username.' '.$bd_pcpi_fregistro.' / '." Actualizado por: ".$usernameactual.'/'.$bd_pcpi_actualfecha);
}else{
    $form->addHidden("hx_pcpi_estado",1); // clave primaria
}

echo $form->writeHTML();


?>
    
    
    <script>
        $(".my_select_box").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true,
            width: '80%'
        });        
    </script>
</body>
</html>

<?php
/* cierro la conexion a la BD */
$conn->close();