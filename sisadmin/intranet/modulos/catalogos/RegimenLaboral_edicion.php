<?php
/*
	formulario de ingreso y modificaci�n
*/
include("../../library/library.php");
include("RegimenLaboral_class.php");
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
$myClass = new clsRegimenLaboral($id,'Edici&oacute;n de R&eacute;gimen Laboral');

if (strlen($id)>0) { // edición
    $myClass->setDatos();
    if($myClass->existeDatos()){
        $bd_rela_id = $myClass->field('rela_id');
        $bd_rela_descripcion = $myClass->field('rela_descripcion');
	$bd_usua_id = $myClass->field('usua_id');
        $username= $myClass->field('username');
    }
}


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
                            document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
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
                parent.content.document.frm.Sr_rela_descripcion.focus();
            }

            function submit() {
                parent.content.document.frm.submit();
            }


        </script>
        <?php  verif_framework(); ?>

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

        $button->addItem("Regresar",$myClass->getPageBuscar(),"content");
        echo $button->writeHTML();

/*
	Formulario
*/
        $form = new Form("frm", "", "POST", "content", "100%",true);
        $form->setLabelWidth("20%");
        $form->setDataWidth("80%");

        $form->addHidden("rodou","s"); // variable de control
        $form->addHidden("f_id",$bd_rela_id); // clave primaria
        $form->addHidden("pagina",getParam("pagina")); // numero de página que llamo

        $form->addField("R&eacute;gimen Laboral:",textField("Regimen Laboral","Sr_rela_descripcion",$bd_rela_descripcion,80,80));
        
        if($id) {
            $form->addField("Creado por: ",$username);        
        }    
        echo $form->writeHTML();
        ?>
    </body>
</html>
<?php
/*
	cierro la conexión a la BD
*/
$conn->close();