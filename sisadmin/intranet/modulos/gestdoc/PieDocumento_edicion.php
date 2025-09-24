<?php
include("../../library/library.php");
include("PieDocumento_class.php"); 
/*
	verifica nivel de usuario
*/
verificaUsuario(1);

/*
	establecer conexion con la BD
*/
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

/*
	tratamiento de campos
*/
$id = getParam("id"); // captura la variable que viene del objeto lista
$depe_id=getParam("nbusc_depe_id");

$myClass = new PieDocumento($id,"Edici&oacute;n de Pie de Documento");

if (strlen($id)>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
		$bd_pido_id=$myClass->field("pido_id");
		$bd_pido_nombre=$myClass->field("pido_nombre");	
                $bd_pido_contenido=$myClass->field("pido_contenido");
		$bd_usua_id= $myClass->field("usua_id");                
                $bd_pido_estado=$myClass->field('pido_estado');
                $bd_pido_incluir_oficina_origen=$myClass->field('pido_incluir_oficina_origen');
                $bd_pido_incluir_nombre_anno=$myClass->field('pido_incluir_nombre_anno');
                $username = $myClass->field('username');
                $usernameactual = $myClass->field('usernameactual');
                $bd_pido_fregistro=$myClass->field('pido_fregistro');
                $bd_pido_fregistroactual=$myClass->field('pido_actualfecha');
        }

}else{ // Si es nuevo
}

?>
<html>
<head>
	<title><?php echo $myClass->getTitle() ?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">

	<script language="JavaScript" src="../../library/js/focus.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>
        

        <script src="../../library/ckeditor/ckeditor.js"></script>
        <script>CKEDITOR.dtd.$removeEmpty['span'] = false;</script>
        
	<script language='JavaScript'>
            
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
                        en esta funci�n se puede personalizar la validaci�n del formulario
                        y se ejecuta al momento de gurdar los datos
                */
                function mivalidacion(frm) {
                        return true
                }

                /*
                        funci�n que define el foco inicial en el formulario
                */
                function inicializa() {
                        parent.content.document.frm.Sr_pido_nombre.focus();
                }
                
                
                </script>
        <?php
	verif_framework();
	?>


</head>

<body class="contentBODY" onload="inicializa()">

 <?php
 
//$nameSLab=getDbValue("select tabl_descripcion from tabla where tabl_tipo=9 and tabl_codigoauxiliar=$sitLab");
pageTitle($myClass->getTitle());

/* botones*/
$button = new Button;
$button->addItem("Guardar","javascript:salvar('Guardar')","content",2);

$button->addItem("Regresar a Lista de Registros",$myClass->getPageBuscar().'?clear=1&'.$param->buildPars(false),"content");
echo $button->writeHTML();


$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$id); // clave primaria

if (strlen($id)>0) { // edición
    $form->addField("C&oacute;digo: ",$id);
}
$form->addField("Nombre: ",textField("Nombre","Sr_pido_nombre",$bd_pido_nombre,80,80).
                     help("Utilice las siguientes Etiquetas:","{fecha} Fecha del Documento.<br>{DNI} N&uacute;mero de documento de identidad.",2));

if (strlen($id)>0) { // edición
    $form->addField("Activo: ",checkboxField("Activo","hx_pido_estado",1,$bd_pido_estado==1));
    $form->addField("Creado por: ",$username.'/'.$bd_pido_fregistro.' / '." Actualizado por: ".$usernameactual.'/'.$bd_pido_fregistroactual);
}else{
    $form->addHidden("hx_pido_estado",1); // clave primaria
}

$form->addHtml("<tr><td colspan=2>");
$form->addHtml("<textarea name=\"K__pido_contenido\" id=\"K__pido_contenido\" rows=\"10\" cols=\"80\">
                $bd_pido_contenido
                </textarea>");
$form->addHtml("</td></tr>\n");

echo $form->writeHTML();
?>
    
    
    
</body>
  
    <script>

        CKEDITOR.replace( 'K__pido_contenido', {
                            filebrowserBrowseUrl: '../../library/ckfinder/ckfinder.html',
                            filebrowserUploadUrl: '../../library/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files'
        });
                    
    </script>

</html>

<?php
/*
	cierro la conexion a la BD
*/
$conn->close();