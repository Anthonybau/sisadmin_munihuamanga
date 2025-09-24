<?php
/*
	formulario de ingreso y modificaci�n
*/
include("../../library/library.php");
include("AFP_class.php");
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
$myClass = new clsAFP($id,'Edici&oacute;n de AFP');

if (strlen($id)>0) { // edición
    $myClass->setDatos();
    if($myClass->existeDatos()){
        $bd_afp_id = $myClass->field('afp_id');
        $bd_afp_nombre = $myClass->field('afp_nombre');
	$bd_afp_nombrecorto = $myClass->field('afp_nombrecorto');
        $bd_afp_fdesde=$myClass->field('afp_fdesde');
        $bd_afp_comision=$myClass->field('afp_comision');
        $bd_afp_comision_mixta=$myClass->field('afp_comision_mixta');
        $bd_afp_prima=$myClass->field('afp_prima');        
        $bd_afp_aporte=$myClass->field('afp_aporte'); 
        $bd_afp_tope=$myClass->field('afp_tope'); 
	$bd_usua_id = $myClass->field('usua_id');
        $username= $myClass->field('username');
    }
}


?>
<html>
    <head>
        <title><?php echo $myClass->getTitle()?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT ?>">
        <script language="JavaScript" src="<?php echo PATH_INC ?>js/focus.js"></script>
        <script language="JavaScript" src="<?php echo PATH_INC ?>js/libjsgen.js"></script>

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
                parent.content.document.frm.Sr_afp_nombre.focus();
            }

            function submit() {
                parent.content.document.frm.submit();
            }


        </script>
        <?php 
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
                $button->addItem("Actualizar Factores","AFPFactores_buscar.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));
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
        $form->addHidden("f_id",$bd_afp_id); // clave primaria
        $form->addHidden("pagina",getParam("pagina")); // numero de página que llamo

        $form->addField("Nombre de AFP:",textField("Nombre de AFP","Sr_afp_nombre",$bd_afp_nombre,80,80));
        $form->addField("Nombre Breve:",textField("Nombre Breve","Sr_afp_nombrecorto",$bd_afp_nombrecorto,30,30));
        
        if (strlen($id)>0) { // edición
                $form->addField("Aplicable Desde: ",dtos($bd_afp_fdesde));    
            	$form->addField("Comisi&oacute;n &Uacute;nica: ",$bd_afp_comision);
                $form->addField("Comisi&oacute;n Mixta: ",$bd_afp_comision_mixta);
                $form->addField("Prima de Seguros: ",$bd_afp_prima);
                $form->addField("Aporte Obligatorio: ",$bd_afp_aporte);		
                $form->addField("Remuneraci&oacute; M&aacute;x Asegurable: ",number_format($bd_afp_tope,2,'.',','));
                $form->addField("Creado por: ",$username);        
        }
	//$form->addField("Comisi&oacute;n Variable: ",numField("Comisi&oacute;n Variable","nr_afp_comision","$bd_afp_comision",14,10,2));
	//$form->addField("Prima de Seguros: ",numField("Prima de Seguros","nr_afp_prima","$bd_afp_prima",14,10,2));
	//$form->addField("Aporte Obligatorio: ",numField("Aporte Obligatorio","nr_afp_aporte","$bd_afp_aporte",14,10,2));		
	//$form->addField("Remuneraci&oacute; M&aacute;x Asegurable: ",numField("Rem.Max.Asegurable","nr_afp_tope","$bd_afp_tope",14,10,2));		
        echo $form->writeHTML();
        ?>
    </body>
</html>
<?php
/*
	cierro la conexión a la BD
*/
$conn->close();