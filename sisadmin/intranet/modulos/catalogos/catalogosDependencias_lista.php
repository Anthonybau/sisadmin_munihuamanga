<?php
/*
	Modelo de página que apresenta una lista de registros
*/
include($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");
include("catalogosDependencias_class.php");

/*
	verifica el nível de usuario, si el usuario tiene acceso sobre el menu
*/
verificaUsuario(1);

/*
	establecer conexión con la BD
*/
$conn = new db();
$conn->open();

$param= new manUrlv1();
//$param->removePar('clear');

$myClass = new dependencia('','C&aacute;talogo de Dependencias');

/* limpia la ordenacion y filtro */
if (getParam("clear")==1) 
	setSession("where","");
else
	if(getParam("Sbusc_depe_descripcion")){
		setSession("where",getParam("Sbusc_depe_descripcion"));
		    $tree_expand=1; //expande el resultado de la busqueda
                    $tree_visible=1; //expande el resultado de la busqueda
}
?>
<html>
<head>
	<title>Catalogo de Dispositivos-lista</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_TREEMENU?>">
        <script type="text/javascript" src="<?php echo PATH_INC?>jquery/jquerypack.js"></script>
	<script language="JavaScript">
	/*
		funcion que llama a la rutina d exclusion de registros, incluye el nombre de la p�gina a ser llamada
	*/
        function excluir() {
                regSel=$("#tLista tbody input[@type=checkbox]").is(":checked");
                if(regSel){
                        if (confirm('Desea Eliminar el(los) registro(s) selecionado(s)?')) {
                                document.frm.target = "controle";
                                document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(2)."&".$param->buildPars(false)?>";
                                document.frm.submit();
                        }
                }else{
                        alert('Seleccione el(los) registro(s) que desea eliminar')
                }
        }

        </script>
	
	<?php 
        verif_framework(); 
        ?>	
		
</head>
<body class="contentBODY" >

<?php

$filtro = getSession("where");

/*	flag para informar si se filtra o no */
 if (strlen($filtro)>0) {
	$filtrado = FILTRO_ATIVO;

} else {
	$filtrado = ""; 
}



/*
	botones de accion
*/
$button = new Button;

/*
	botones de accion de la lista, altere deacuerdo a sus necesidades
*/
$button->addItem("Nueva Dependencia","catalogosDependencias_edicion.php","content",2);
$button->addItem("Buscar","catalogosDependencias_buscar.php","content");
$button->addItem("Eliminar","javascript:excluir()","content",2);

$button->addItem("Imprimir","rptCatalogosDependencias_imprimir.php","controle");
echo $button->writeHTML();

echo "<div class='DataFONT' align='right'><b>$filtrado</b></div>";
?>


<!-- Lista -->
<form name="frm" method="post">
<?php

$depe_id=getSession("sis_depe_superior");

$sql = "SELECT tree_id,
                tree_text||' ('||tree_id::text||')'||'|catalogosDependencias_edicion.php?id='||tree_id AS tree_text
                from func_treedependencia($depe_id,'$filtro')";

$rs = new query($conn, $sql);

$deletedTree=1;//muestra el check de eliminacion
//echo getParam("clear");
if(getParam("clear")==1){
    $tree_expand=1;
    $tree_visible=1;
}


require "../../library/treemenu.php";
?>
</form>

</body>
</html>
<?php
/*
	cierra la conexion a la BD, no debe ser alterado
*/
$conn->close();
//wait('');