<?php
/* Modelo de p�gina que apresenta um formulario con criterios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificación del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("personalDatosPersonales_class.php");


/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$busEmpty = 1; //getParam("busEmpty"); // para definir si se muestran los datos en el primer ingreso
$nbusc_char= getParam("nbusc_char");

$param= new manUrlv1();
$param->removePar('clear');

$myClass = new clsPersona(0,"Datos Personales");


if ($clear==1) {
    setSession("cadSearch","");
    $nbusc_char='';
}

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "clsPersona","buscar"),"");
$xajax->registerFunction("setMover");

function setMover($array_id){
    global $conn;
    
    $objResponse = new xajaxResponse();
    
    $usua_id=getSession('sis_userid');
    
    if(is_array($array_id)){
        $id=implode(",",$array_id);
    
        $sql="UPDATE personal.persona
                                 SET pers_tipo_persona=1,
                                     pers_move_fregistro=NOW(),
                                     pers_move_usua_id=$usua_id    
                                 WHERE pers_id IN ($id)";
                
        $conn->execute($sql);
        $error=$conn->error();
        if($error){
            $objResponse->addAlert($error);        
        }else{
            $objResponse->addScript("parent.content.location.reload()");
        }
    }
    return $objResponse;
}

$xajax->processRequests();

// fin para Ajax
?>
<html>
<head>
	<title><?php echo $myClass->getTitle() ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo  CSS_CONTENT?>">
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>   
	<script language="javascript" src="<?php echo PATH_INC?>js/checkall.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>
	<script language="JavaScript">

		function inicializa() {
                    document.frm.Sbusc_cadena.focus();
		}
	
		function excluir() {
                    if (confirm('Eliminar registros seleccionados?')) {
                        parent.content.document.frm.target = "controle";
                        parent.content.document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(2)."&".$param->buildPars(false)?>";
                        parent.content.document.frm.submit();
                    }
                }
                
                function mover(){                
                    regSel=$("#tLista tbody input[type=checkbox]").is(":checked");
                    if(regSel){
                        if (confirm('¿Seguro de mover registros seleccionados?')) {
                            var checked = []                    
                            $("input[name='sel[]']:checked").each(function ()
                            {
                                checked.push(parseInt($(this).val()));
                            });

                            xajax_setMover(checked);
                        }

                    } else {
                        alert('Seleccione un registro');
                    }
                }


	</script>
    <?php 
        $xajax->printJavascript(PATH_INC.'ajax/');
	verif_framework(); 
        ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("B&uacute;squeda de Personas");

/* botones */
$button = new Button;

$button->addItem(" Nuevo ","personalDatosPersonales_edicion.php".$param->buildPars(true),"content");
$button->addItem("Mover a Internos","javascript:mover()","content",2);
$button->addItem("Eliminar","javascript:excluir()","content",2);

echo $button->writeHTML();

/* formul�rio de pesquisa */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s");

//array de parametros que se ingresaran a la funcion de busqueda de ajax
$paramFunction= new manUrlv1();
$paramFunction->removeAllPar();
$paramFunction->addParComplete('colSearch','');
$paramFunction->addParComplete('colOrden','1');
$paramFunction->addParComplete('busEmpty',$busEmpty);
$paramFunction->addParComplete('nbusc_char',$nbusc_char);

$form->addField("DNI/Apellidos/Nombres: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">");

$cad="<TR><TD colspan=6><table border=0 cellpadding=0 cellspacing=0><tr>";
        for($i=65;$i<=90;$i++)
            $cad.="<td><input type=\"button\" style=\"font_size:6px\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado','".chr($i)."')\" value=\"".chr($i)."\">"."</td>";
        $cad.="</tr></table></TD></TR>";

$form->addHtml($cad);

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");

$form->addHtml($myClass->buscar(2,'',encodeArray($paramFunction->getUrl()),1,'DivResultado'));

$form->addHtml("</div></td></tr>\n");
echo  $form->writeHTML();
?>
</body>
</html>
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();