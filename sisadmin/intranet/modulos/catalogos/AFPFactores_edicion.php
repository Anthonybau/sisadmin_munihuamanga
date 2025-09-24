<?php
/* Modelo de p�gina que apresenta um formulario con criterios de busqueda */
include("../../library/library.php");

/* verificación del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("AFPFactores_class.php");
include("AFP_class.php");

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

$afp=new clsAFP_SQLlista();
$afp->whereID($relacionamento_id);
$afp->setDatos();

$myClass=new clsAFPFactores($id,"Editar Factor - ".$afp->field('afp_nombre'));
$myClass->setDatos();
// fin para Ajax
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="javascript" src="<?php echo PATH_INC?>js/checkall.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>
        <script language="JavaScript" src="<?php echo PATH_INC?>js/textcounter.js"></script>        
	<script language="JavaScript">
        function salvar(idObj) {
                    if (ObligaCampos(frm)){
                            ocultarObj(idObj,10)
                            document.frm.target = "controle";
                            document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
                            document.frm.submit();
                    }
        }
        
        function mivalidacion(frm) {
            return true
        }
        
	function inicializa() {
            document.frm.nr_affa_comision.focus();
	}
	
	</script>
	<?php
            verif_framework(); 
            $calendar->load_files();	        
        ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle($myClass->getTitle());


/* formul�rio de pesquisa */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s");
$form->addHidden('___afp_id',$relacionamento_id);
$form->addHidden("f_id",$id); // clave primaria

//array de parametros que se ingresaran a la funcion de busqueda de ajax
$paramFunction= new manUrlv1();
$paramFunction->removeAllPar();
$paramFunction->addParComplete('colSearch','');
$paramFunction->addParComplete('colOrden','1');
$paramFunction->addParComplete('busEmpty',$busEmpty);
$paramFunction->addParComplete('relacionamento_id',$relacionamento_id);
$fechaDesde=dtos($myClass->field('affa_fdesde'));
$fechaDesde=$fechaDesde?$fechaDesde:date('d/m/Y');

$form->addField("Fecha Desde: ", $calendar->make_input_field('Fecha Desde',array(),array('name'=> 'Dr_affa_fdesde','value'=> $fechaDesde)));
$form->addField("Comisi&oacute;n Flujo: ",numField("Comisi&oacute;n Flujo","nr_affa_comision",$myClass->field('affa_comision'),14,10,2));
$form->addField("Comisi&oacute;n Mixta: ",numField("Comisi&oacute;n Mixta","nr_affa_comision_mixta",$myClass->field('affa_comision_mixta'),14,10,2));
$form->addField("Prima de Seguros: ",numField("Prima de Seguros","nr_affa_prima",$myClass->field('affa_prima'),14,10,2));
$form->addField("Aporte Obligatorio: ",numField("Aporte Obligatorio","nr_affa_aporte",$myClass->field('affa_aporte'),14,10,2));		
$form->addField("Remuneraci&oacute; M&aacute;x Asegurable: ",numField("Rem.Max.Asegurable","nr_affa_tope",$myClass->field('affa_tope'),14,10,2));		

$button = new Button;
$button->setDiv(FALSE);
$button->addItem("Guardar","javascript:salvar('Guardar')","content",2);
$form->addField("",$button->writeHTML());

echo  $form->writeHTML();

?>
</body>
</html>
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();
