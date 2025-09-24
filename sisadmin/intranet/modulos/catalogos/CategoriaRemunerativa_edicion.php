<?php
/*
	formulario de ingreso y modificaci�n
*/
include("../../library/library.php");
include("CategoriaRemunerativa_class.php");
/*
	verificación del nível de usuario
*/
verificaUsuario(1);

/*
	establecer conexión con la BD
*/
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();
$param->removePar('relacionamento_id'); /* Remuevo el parámetro */
/*
	tratamiento de campos
*/
$id = getParam("id"); // captura la variable que viene del objeto lista
$myClass = new clsCategoriaRemunerativa($id,'Categor&iacute;a de Obrero');

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("actFactores");
$xajax->registerFunction("pideCateoria");

if (strlen($id)>0) { // edición
    $myClass->setDatos();
    if($myClass->existeDatos()){
        $bd_care_id = $myClass->field('care_id');
        $bd_care_descripcion = $myClass->field('care_descripcion');
        $bd_tabl_idsitlaboral= $myClass->field('tabl_idsitlaboral');
        $bd_care_fdesde= $myClass->field('care_fdesde');
        $bd_care_jornal= $myClass->field('care_jornal');
        $bd_care_reintegro= $myClass->field('care_reintegro');
	$bd_usua_id = $myClass->field('usua_id');
        $username= $myClass->field('username');
    }
}

function pideCateoria($op,$value,$NameDiv)
{
	global $conn,$id,$param,$bd_care_fdesde,$bd_care_jornal,$bd_care_reintegro;

	$objResponse = new xajaxResponse();

        if($value==38){ /*si es Obrero*/
		$oForm = new AddTableForm();
		$oForm->setLabelWidth("20%");
		$oForm->setDataWidth("80%");
                $oForm->addField("Aplicable Desde: ",dtos($bd_care_fdesde));    
                $oForm->addField("Jornal Diario: ",number_format($bd_care_jornal,2,'.',','));                
                
                if($bd_care_reintegro>0){
                    $oForm->addField("Reingtegro Diario: ",number_format($bd_care_reintegro,2,'.',','));                
                }
                if($id){
                    $button = new Button;
                    $button->setDiv(false);
                    $button->addItem("Actualizar Jornal","CategoriaRemunerativaJornales_buscar.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));            
                    $oForm->addField("",$button->writeHTML());            
                }

		$contenido_respuesta=$oForm->writeHTML();
	}
	else
		$contenido_respuesta="";
        
	$objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);

	if($op==1)
		return $objResponse;
	else
		return $contenido_respuesta;

}

function actFactores($jornal){
    $objResponse = new xajaxResponse();

    $dominical=round($jornal/6,4);
    $objResponse->addScript("document.frm.nr_care_dominical.value='$dominical'");
    $hora=round($jornal/8,4);
    $objResponse->addScript("document.frm.nr_care_hora.value='$hora'");
    return $objResponse;
}

$xajax->processRequests();
?>

<html>
    <head>
        <title><?php echo $myClass->getTitle()?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
        <script language="JavaScript" src="<?php echo PATH_INC?>js/focus.js"></script>
        <script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>

        <script language='JavaScript'>
            /*
                función guardar
             */

            function salvar(idObj) {
                    if (ObligaCampos(frm)){
                            ocultarObj(idObj,10)
                            document.frm.target = "controle";
                            document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false) ?>";
                            document.frm.submit();
                    }
            }
            /*
                se invoca desde la funcion obligacampos (libjsgen.js)
                en esta función se puede personalizar la validación del formulario
                y se ejecuta al momento de gurdar los datos
             */
            function mivalidacion(frm) {
                return true
            }
            /*
                funci�n que define el foco inicial en el formulario
             */
            function inicializa() {
                parent.content.document.frm.Sr_care_descripcion.focus();
            }

            function submit() {
                parent.content.document.frm.submit();
            }


        </script>
        <?php 
            $xajax->printJavascript(PATH_INC.'ajax/');
            verif_framework(); 
        ?>

    </head>
    <body class="contentBODY" onLoad="inicializa()" >
        <?php
        pageTitle($myClass->getTitle());

/*
	botones,
	configure conforme suas necessidades
*/
        $retorno = $_SERVER['QUERY_STRING'];

        $button = new Button;
        $button->addItem("Guardar","javascript:salvar('Guardar')","content",2,$bd_usua_id);
        if($id){
                $button->addItem("Conceptos Vinculados","CategoriaRemunerativaConceptos_buscar.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));
        }

        
        $button->addItem("Regresar",$myClass->getPageBuscar(),"content");
        echo $button->writeHTML();


        echo "<br>";

/*
	Formulario
*/
        $form = new Form("frm", "", "POST", "content", "100%",true);
        $form->setLabelWidth("20%");
        $form->setDataWidth("80%");

        $form->addHidden("rodou","s"); // variable de control
        $form->addHidden("f_id",$bd_care_id); // clave primaria
        $form->addHidden("pagina",getParam("pagina")); // numero de página que llamo

        $sqlSituLabo = "SELECT tabl_id, tabl_descripcion as Descripcion
                        FROM tabla WHERE tabl_tipo='CONDICION_LABORAL' ORDER BY 1 ";

        $form->addField("Condici&oacute;n Laboral: ",listboxField("Condici&oacute;n laboral",$sqlSituLabo, "tr_tabl_idsitlaboral",$bd_tabl_idsitlaboral,"-- Seleccione Condici&oacute;n --","onChange=\"xajax_pideCateoria(1,this.value,'divCategoria')\""));
        $form->addField("Categor&iacute;a:",textField("Categoria","Sr_care_descripcion",$bd_care_descripcion,80,80));        
        
        $form->addHtml("<tr><td colspan=2><div id='divCategoria'>\n");
        $form->addHtml(pideCateoria(2,$bd_tabl_idsitlaboral,'divCategoria'));
        $form->addHtml("</div></td></tr>\n");
        
       
        echo $form->writeHTML();
        ?>
    </body>
</html>


<?php
/*
	cierro la conexión a la BD
*/
$conn->close();
