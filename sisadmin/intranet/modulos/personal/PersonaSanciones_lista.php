<?
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificacion del nivel de usuario */
verificaUsuario(1);

include("PersonaSanciones_class.php");
include("Persona_class.php");

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

$id_relacion=getParam("id_relacion"); //variable q se recibe desde la opcion "ACTUALIZACION DE ESCALAFON"
$nbusc_char=getParam("nbusc_char");
$clear=getParam("clear");
$busEmpty=1;
$pg=1;

$param= new manUrlv1();
$param->removePar('clear');

$myClass = new clsPersonaSanciones(0,"Sanciones");


// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "clsPersonaSanciones","buscar"),"");


$xajax->processRequests();
// fin para Ajax
?>
<html>
<head>
	<title><?$myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="JavaScript" src="<?=PATH_INC?>js/libjsgen.js"></script>	
	<script language="javascript" src="<?=PATH_INC?>js/checkall.js"></script>        
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>   
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                
	
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>
        
	<script language="JavaScript">
        function inicializa() {
            document.frm.Sbusc_cadena.focus();
        }
                    
	/*
		funcion que llama a la rutina d exclusion de registros, incluye el nombre de la p�gina a ser llamada
	*/
	function excluir(id) {
        	if (confirm('Eliminar registros seleccionados?')) {
                parent.content.document.frm.target = "controle";
		parent.content.document.frm.action = "<?=$myClass->getNameFile()."?control=".base64_encode(2)."&".$param->buildPars(false)?>";
		parent.content.document.frm.submit();
		}
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

	function salvar() {
		if (ObligaCampos(frm)){
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "<?=$myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
			parent.content.document.frm.submit();
		}
	}

	function mivalidacion(frm) {  
            var sError="Mensajes del sistema: "+"\n\n"; 	
            var nErrTot=0; 	 

		if (nErrTot>0){ 		
			alert(sError)
			return false
		}else
			return true			
	}
        
        
	function refrescar() {
            parent.content.location.reload();
	}
        
	</script>
        <script type="text/javascript" src="../../library/js/jquerytablas3.js"></script> <!-- Esta l?nea debe ir aqu? para luego de que se aplique el orden se refrescan los css de la tabla -->
        <?php $xajax->printJavascript(PATH_INC.'ajax/'); ?>                
	<? verif_framework(); ?>		
</head>
<body class="contentBODY" onLoad="inicializa()">
<?
pageTitle($myClass->getTitle());

/* botones */
        /* Botones de accion */
$button = new Button;
$button->addItem("Agregar Registro",PATH_INC."auxiliar.php?pag=../../modulos/personal/PersonaSanciones_edicion.php?id_relacion=$id_relacion","content",2,0,"ls-modal botao");	
$button->addItem("Eliminar","javascript:excluir()","content",2);
$button->addItem("Refrescar","javascript:refrescar()","content",2);
$button->addItem("Imprimir","javascript:AbreVentana('rptPersonaSanciones.php?id_relacion=$id_relacion')","content");    
$button->addItem("Ir a Lista de Personas","Persona_buscar.php".$param->buildPars(true),"content");

//echo $button->writeHTML();

$botones=btnMenuEscalafon('Opciones',$id_relacion,$param);
   
echo "<table width='100%' colspan=0><tr><td width='80%'>".$button->writeHTML()."</td><td width='20%' align=right>".$botones."</td></table>";        

/* formulario */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("rodou","s");
$myPersona = new clsPersona($id_relacion);
$myPersona->setDatos();
$form->addField("Personal: ",$myPersona->field("empleado").' / '.$myPersona->field("pers_dni"));
$form->addField("Condici&oacute;n Laboral: ",$myPersona->field("sit_laboral_larga"));   

//array de parametros que se ingresaran a la funcion de busqueda de ajax
$paramFunction= new manUrlv1();
$paramFunction->removeAllPar();
$paramFunction->addParComplete('colSearch','');
$paramFunction->addParComplete('clear',$clear);
$paramFunction->addParComplete('busEmpty',$busEmpty);
$paramFunction->addParComplete('numForm',0);
$paramFunction->addParComplete('pageEdit',$myClass->getPageEdicion());

$array=$myClass->getArrayNameVar();
foreach($array as $k => $v) {$paramFunction->addParComplete($k,$v);}

$form->addField("Concepto/Descripci&oacute;n: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",$cadena ,50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">");

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");

/* Creo array $formData con valores necesarios para filtrar la tabla */
$formData['Sbusc_cadena']=$cadena ;
$form->addHtml($myClass->buscar(2,$formData,encodeArray($paramFunction->getUrl()),$pg,'DivResultado'));

$form->addHtml("</div></td></tr>\n");
echo  $form->writeHTML();
?>
        
    <div id="myModal" class="modal fade">   
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close"  data-dismiss="modal" aria-hidden="true">&times;</button>
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
        $("select").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true,
            width: '30%'
            });
</script>    
<?
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();