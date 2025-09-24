<?
/*
	formulario de solicitud de parametros
*/
include("../../library/library.php");

verificaUsuario(1);

include("../catalogos/Tabla_class.php");
include("./Situacion_academica.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->setCharEncoding('utf-8');

$xajax->processRequests();
?>
<html>
<head>
	<title>Reportes-Index</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="JavaScript" src="<?=PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?=PATH_INC?>js/libjsgen.js"></script>
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>            
	<script language='JavaScript'>
	function mivalidacion(frm) {  
		return true			
	}
	
	function inicializa() {
		parent.content.document.frm.tr_id_planilla.focus();
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
        
	function imprimir(sURL) {
                if (ObligaCampos(frm)){
                    parent.content.document.frm.target = "controle";
                    parent.content.document.frm.action = sURL;
                    parent.content.document.frm.submit();
                }
	}

	</script>
	<? 
        verif_framework(); 
        $calendar->load_files();
        $xajax->printJavascript(PATH_INC.'ajax/');
        ?>		
    
</head>
<body class="contentBODY" onLoad="inicializa()">
<? 
pageTitle("Par&aacute;metros de Impresi&oacute;n: Personal Con Estudios","");


/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",false);

$form->setLabelWidth("20%");
$form->setDataWidth("80%");


$form->addHidden("_titulo","RELACION DE PERSONAL CON ESTUDIOS"); // Campo oculto con el T�tulo del reporte
$sUrl=str_replace("_ini","",substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1));

$bd_fechadesde = date("d/m/Y");
$bd_fechahasta = date("d/m/Y");
$form->addField("Fecha Egresado Desde: ", $calendar->make_input_field('Fecha Egresado Desde', array(), array('name' => 'Dr_fechadesde', 'value' => $bd_fechadesde,  'onKeyPress' => "return formato(event,form,this)")));
$form->addField("Fecha Egresado Hasta: ", $calendar->make_input_field('Fecha Egresado Hasta', array(), array('name' => 'Dr_fechahasta', 'value' => $bd_fechahasta)));

$tabla=new clsTabla_SQLlista();
$tabla->whereTipo('GRADO_INSTRUCCION');
$tabla->orderUno();
$sql=$tabla->getSQL_cbox();
$form->addField("Grado Instrucci&oacute;n: ",listboxField("Grado Instruccion",$sql,"tx_tabl_gradoinstruccion","","-- Todos--"));

$situacionAcademica=new SituacionAcademica_SQLlista();
$situacionAcademica->orderUno();
$sql=$situacionAcademica->getSQL_cbox();
$form->addField("Situaci&oacute;n Academica: ",listboxField("Situacion Academica",$sql,"tx_siac_id","","-- Todos--"));

$form->addField("Filtro: ",textField("Filtro","sx_filtro",'',90,90));

$button = new Button;
$button->addItem("<img src='../../img/pdf.png' border='0'>&nbsp;","javascript:imprimir('$sUrl?destino=1')","");		
//$button->addItem("<img src='../../img/xls.png' border='0'>&nbsp;","javascript:imprimir('$sUrl?destino=2')","");		

$form->addField("",$button->writeHTML());

echo $form->writeHTML();
?>
    <script>
        $("select").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true
        });
    </script>    
</body>
</html>
<?
/* cierro la conexión a la BD */
$conn->close();