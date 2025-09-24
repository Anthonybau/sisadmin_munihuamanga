<?php
/*
	P�gina que presenta el arbol
	NO DEBE SER ALTERADO
*/
include_once($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");

// captura argumentos pasados
if (getParam("clear") == 1) {
	setSession("campo_form", getParam("nameCampoForm"));
	setSession("tabla", getParam("nameTabla"));
	setSession("ExcluNivelMax", getParam("ExcluNivelMax"));	
	setSession("titulo_lista", getParam("titulo"));
	setSession("busqueda", '');	
} else {
	if(getParam("Busca")){
		setSession("busqueda", strtoupper(getParam("Sx_txtPesquisa")));
        $tree_expand=1; //expande el resultado de la busqueda
        $tree_visible=1; //expande el resultado de la busqueda
	}	
}

// conexion con la base de datos
$conn = new db();
$conn->open();

?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	<title>Seleccione un registro</title>
	<script language="JavaScript" src="js/libjsgen.js"></script>		
	<script language="javascript" type="text/javascript">
		function update(valor, descricao) {
			opener.parent.content.document.forms[0].<?php echo "_Dummy".getSession("campo_form")?>.value = descricao;
			opener.parent.content.document.forms[0].<?php echo getSession("campo_form")?>.value = valor;
			// Campo oculto para controlar onchage de los lookup, funciona con la funci�n onChangeLookup()
			opener.parent.content.document.forms[0].__Change_<?php echo getSession("campo_form")?>.value = 1; 
			window.self.close();
		}
	
	</script>
	
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_TREEMENU?>">		
</head>

<body class="contentBODY" >

<!-- identificaci�n da p�gina -->
<?php
pageTitle(getSession("titulo_lista"),"Seleccione un Registro del Arbol");
?>
<div class="acoes">
	<form name="frm" action="<?php echo $_SERVER['PHP_SELF']?>" method="get">
		<font class="LabelFont"><?php echo LOOKUP_TITULO_PESQUISA?></font>
		<input type="text" size="15" name="Sx_txtPesquisa" value="" onKeyPress='return formato(event,form,this,50)'>
		<input type="submit" name="Busca" value=" Ok " >
	</form>
</div>
<div class="acoes">
	<a class="link" href="javascript:update('','')"><?php echo LOOKUP_RESET?></a>
</div>

<!-- Lista -->
<?php

$sql = "SELECT tree_id,tree_text||'|javascript:update('||tree_id||',\''||tree_textaux||'\')'||COALESCE('||../img/'||tree_icon,'') AS tree_text ".
			   "FROM " .str_replace("_SEARCH_","'".strtoupper(getSession("busqueda"))."'",getSession("tabla")) ;

$rs = new query($conn, $sql);

require "../library/treemenu.php";
?>
</body>
</html>
<?php
/* cierra la conexion  */
$conn->close();