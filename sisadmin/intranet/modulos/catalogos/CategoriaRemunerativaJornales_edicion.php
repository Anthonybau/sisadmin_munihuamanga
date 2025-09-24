<?
/* Modelo de p�gina que apresenta um formulario con criterios de busqueda */
include("../../library/library.php");

/* verificación del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("CategoriaRemunerativaJornales_class.php");

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$id= getParam("id_relacion");
$relacionamento_id = getParam("id");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente

/* Archivos para calendario */
require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);

$param= new manUrlv1();
$param->removePar('clear');

$myClass=new clsCategoriaRemunerativaJornales($id,"Editar Jornal");
$myClass->setDatos();
// fin para Ajax
?>
<html>
<head>
	<title><?=$myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="javascript" src="<?=PATH_INC?>js/checkall.js"></script>
	<script language="JavaScript" src="<?=PATH_INC?>js/libjsgen.js"></script>
        <script language="JavaScript" src="<?=PATH_INC?>js/textcounter.js"></script>        
	<script language="JavaScript">
        function salvar(idObj) {
                    if (ObligaCampos(frm)){
                            ocultarObj(idObj,10)
                            document.frm.target = "controle";
                            document.frm.action = "<?=$myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
                            document.frm.submit();
                    }
        }
        
        function mivalidacion(frm) {
            return true
        }
        
	function inicializa() {
            document.frm.nr_crjo_jornal.focus();
	}
	
	</script>
	<? 
            verif_framework(); 
            $calendar->load_files();	        
        ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?
pageTitle("Edici&oacute;n de Jornal");

$button = new Button;
$button->addItem("Guardar","javascript:salvar('Guardar')","content",2);
echo $button->writeHTML();

/* formul�rio de pesquisa */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s");
$form->addHidden('___care_id',$relacionamento_id);
$form->addHidden("f_id",$id); // clave primaria

//array de parametros que se ingresaran a la funcion de busqueda de ajax
$paramFunction= new manUrlv1();
$paramFunction->removeAllPar();
$paramFunction->addParComplete('colSearch','');
$paramFunction->addParComplete('colOrden','1');
$paramFunction->addParComplete('busEmpty',$busEmpty);
$paramFunction->addParComplete('relacionamento_id',$relacionamento_id);
$fechaDesde=dtos($myClass->field('care_fdesde'));
$fechaDesde=$fechaDesde?$fechaDesde:date('d/m/Y');

$reintegro=$myClass->field('crjo_reintegro')>0?$myClass->field('crjo_reintegro'):0;
$form->addField("Fecha Desde: ", $calendar->make_input_field('Fecha Desde',array(),array('name'=> 'Dr_crjo_fdesde','value'=> $fechaDesde)));
$form->addField("Jornal Diario:",numField("Jornal Diario","nr_crjo_jornal",$myClass->field('crjo_jornal'),10,8,2,false));
$form->addField("Reintegro:",numField("Reintegro","nr_crjo_reintegro",$reintegro,10,8,2,false));

echo  $form->writeHTML();

?>
</body>
</html>
<?
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();
