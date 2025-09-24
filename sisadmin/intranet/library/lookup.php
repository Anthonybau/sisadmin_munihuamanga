<?php
/*
	P�gina que apresenta lista de seleccion,
*/
include_once($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");
// captura argumentos passados
if (getParam("rodou_pesq") != "sim") {
	setSession("campo_form", getParam("nomeCampoForm"));
	setSession("tabela", getParam("nomeTabela"));
	setSession("campo_chave", getParam("nomeCampoChave"));
	setSession("campo_exibicao", getParam("nomeCampoExibicao"));
	setSession("campo_auxiliar", getParam("nomeCampoAuxiliar"));
	setSession("upcase", getParam("upCase"));	
	setSession("ListaInicial", getParam("ListaInicial"));
	setSession("NumForm", getParam("NumForm"));	
	setSession("titulo_lista", getParam("titulo"));
	$strWhere = "";
} 

// conex�o com o banco de dados
$conn = new db();
$conn->open();

// express�o SQL que monta a lista
if (strlen(trim(getSession("campo_auxiliar")))>0) {
	$c = ", ".getSession("campo_auxiliar")." AS auxiliar ";
} else {
	$c = "";
}

$_tabla=getSession("tabela"); // Recibo la tabla que se mostrar�
$_tabla=getSession($_tabla); // Verifico si se ha creado una variable de sesi�n donde se envia una string de consulta como tabla. 

if(getParam("rodou_pesq")) { // Si se ha presionado el bot�n Buscar
	$_campoBus=getSession("campo_exibicao"); // obtengo el campo por el que se buscar� 
	$_campoBus=str_replace(",","||' '||",$_campoBus); // reemplazo las comas por simbolo de concatenaci�n, para cuando se desea la b�squeda a la vez por varios campos 
	if(stripos(strtoupper($_tabla), 'WHERE')){ // Si se ha enviado un string de consulta con un WHERE 
		$strWhere  = " AND ".$_campoBus." ILIKE '%".strtoupper(getParam("Sx_txtPesquisa"))."%' "; // Armo cadena Where
	}else{
		$strWhere  = " WHERE ".$_campoBus." ILIKE '%".strtoupper(getParam("Sx_txtPesquisa"))."%' "; // Armo cadena Where
	}

	$nIniGroup=stripos(strtoupper($_tabla),'GROUP');
	if($nIniGroup){ // Si se ha enviado un string de consulta con un GROUP BY 
		$strGroup = substr($_tabla,$nIniGroup);
		$_tabla = substr($_tabla,0,$nIniGroup);
	}
}

if(substr(strtoupper($_tabla),0,6)=='SELECT' and stripos(strtoupper($_tabla), 'FROM')){ // Si $_tabla contiene un string de consulta
	$sql = $_tabla.$strWhere.$strGroup." ORDER BY " . getSession("campo_exibicao"); // concateno el string de la consulta con el where y el Order 
}else{ // $_tabla contiene el nombre de la tabla que deseo mostrar
	$sql = "SELECT " . getSession("campo_chave") . " AS chave, " . getSession("campo_exibicao") . " AS label " .$c.
       "FROM " . getSession("tabela") . " " .
       $strWhere .
       "ORDER BY " . getSession("campo_exibicao");
}

if(getSession('ListaInicial') or getParam('rodou_pesq')) // Si deseo que se muestre una lista inicial o se ha presionado el bot�n buscar
	$mostrarDatos=true;
//ECHO $sql;
//$rs = new query($conn, $sql, 1, LOOKUP_MAX_REC);
if($mostrarDatos) 
	$rs = new query($conn, $sql);
	
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	<title>Selecione un registro</title>
	<script language="JavaScript" src="js/libjsgen.js"></script>		
	<script language="javascript" type="text/javascript">
	function update(valor, descricao) {
		if(valor==0) {
                    valor=''; /* Para cuando es llamado desde 'Insertar vac�o' */
                }
		opener.parent.content.document.forms[<?php echo getSession("NumForm")?>].<?php echo "_Dummy".getSession("campo_form")?>.value = descricao;
		opener.parent.content.document.forms[<?php echo getSession("NumForm")?>].<?php echo getSession("campo_form")?>.value = valor;
		// Campo oculto para controlar onchage de los lookup, funciona con la funci�n onChangeLookup() ejm. adminUsuario_edicion.php 
		opener.parent.content.document.forms[<?php echo getSession("NumForm")?>].__Change_<?php echo getSession("campo_form")?>.value = 1; 
		window.self.close();
	}
	</script>
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
</head>

<body class="contentBODY" onLoad="javascript:document.forms[0].Sx_txtPesquisa.focus();">

<!-- identificaci�n da p�gina -->
<?php
pageTitle(getSession("titulo_lista"),LOOKUP_SUBTITULO);
?>
<div class="acoes">
	<form name="frm" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="get">
		<font class="LabelFont"><?php echo LOOKUP_TITULO_PESQUISA ?></font>
		<?php if(getSession("upcase")==true)
				echo "<input type=\"text\" size=\"15\" name=\"Sx_txtPesquisa\" value=\"\" onKeyPress='return formato(event,form,this,50)'>";
			else
				echo "<input type=\"text\" size=\"15\" name=\"Sx_txtPesquisa\">";
		?>
		<input type="submit" name="Busca" value=" Ok ">
		<input type="hidden" name="rodou_pesq" value="sim">
	</form>
</div>
<div class="acoes">
	<a class="link" href="javascript:update(0,'')"><?php echo LOOKUP_RESET?></a>
</div>
<?php
// C�digo Actualizado para soportar consultas sql
if($mostrarDatos){
	$table = new Table("","100%",$col);
	$fnum = $rs->numfields();
	while ($rs->getrow()) {
		$table->addData("<a class=\"link\" href=\"javascript:update('".$rs->field(0)."','".str_replace("&#039;","\'",htmlspecialchars($rs->field(1),ENT_QUOTES))."')\">".$rs->field(1)."</a>",'','','nowrap');
		for ($x = 2; $x < $fnum; $x++) {
			$table->addData($rs->field($x));	
		}
		$table->addRow();
	}
	echo $table->writeHTML();
}
// encerra a conex�o com o banco de dados
$conn->close();
?>
</body>
</html>