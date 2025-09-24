<?php
/*
	formulario de ingreso y modificaci�n
*/
include("../../library/library.php");

/*
	verificaci�n del n�vel de usuario
*/
verificaUsuario(1);

/*
	establecer conexi�n con la BD
*/
$conn = new db();
$conn->open();

/*
	se a lista faz parte de um relacionamento 1:N
*/
$relacionamento_id = getParam("id");
$busEmpty=getParam("busEmpty"); //permite o no buscar cadenas vacias (muestra todo los registros)
$tipo=getParam("tipo");

/*
	determina a p�gina a ser exibida
*/
$pg = getParam("pagina");
if ($pg == "") $pg = 1;

/*
	expresion SQL que define la lista 1n
*/




$sql= sprintf("SELECT sesg_id,sesg_descripcion,sesg_descripbreve " .
	 	"FROM servicio_sgrupo a " .
		"WHERE segr_id='%s' " .
		"ORDER BY 1 ",$relacionamento_id);
	  
/*
	crea el recordset, elo �ltimo parametro corresponde a la cantidad de
	registros por p�gina
*/
//$rs = new query($conn, $sql, $pg, 40);
$rs = new query($conn, $sql);
?>
<html>
<head>
	<title>Grupos de Servicios M&eacute;dicos/Sub Grupos-Lista</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="../../library/js/janela.js"></script>
	<script language="javascript" src="../../library/js/checkall.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>	
		
	<script language="JavaScript">
	/*
		funcion que llama a la rutina d exclusion de registros, incluye el nombre de la p�gina a ser llamada
	*/
	function excluir() {
		if (confirm('Eliminar registros selecionados?')) {
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "../imes/eliminar.php?_op=SGrpoServ&relacionamento_id=<?php echo $relacionamento_id?>";
			parent.content.document.frm.submit();
		}
	}
	
	function abreEdicion(id) {
		// la extensi�n, o separador "&" debe ser substituido por coma ","
		abreJanelaAuxiliar('../modulos/catalogos/catalogosServiciosGrupos_sGrupoEdicion.php?relacionamento_id=<?php echo $relacionamento_id?>,id=' + id,600,500); 
	} 

        function abreJanelaAuxiliar(pagina,nWidth,nHeight){
                eval('janela = window.open("../../library/auxiliar.php?pag=' +  pagina +
                     '","janela","width='+nWidth+',height='+nHeight+',top=50,left=150' +
                          ',scrollbars=no,hscroll=0,dependent=yes,toolbar=no")');
                janela.focus();
        }

	</script>
	<?php 
        verif_framework(); 
        ?>		
</head>
<body class="contentBODY">

<?php
//pageTitle("Sub Grupos de Servicios M&eacute;dicos");
pageTitle("Sub Grupos");

/*
	botones de accion
*/
$button = new Button;

/*
	botones de navegaci�n de la lista, no debe ser alterada
*/
$pg_ant = $pg-1;
$pg_prox = $pg+1;
if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,$_SERVER['PHP_SELF']."?pagina=$pg_ant&id=$relacionamento_id" ,"content");
if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,$_SERVER['PHP_SELF']."?pagina=$pg_prox&id=$relacionamento_id","content");

/*
	botones de accion de la lista, altere deacuerdo a sus necesidades
*/
$button->addItem("Nuevo Sub Grupo","javascript:abreEdicion(0)","content",2);
$button->addItem("Eliminar","javascript:excluir()","content",2);
echo $button->writeHTML();

/*
	Control de fichas,
*/
$abas = new Abas();
$abas->addItem("General",false,"catalogosServiciosGrupos_edicion.php?id=$relacionamento_id&clear=1&busEmpty=$busEmpty&tipo=$tipo");
$abas->addItem("Sub Grupos",true);
echo $abas->writeHTML();

$odatos = new AddTableForm();
$odatos->setLabelWidth("20%");
$odatos->setDataWidth("80%");
$nameGrupo=getDbValue("SELECT segr_id::TEXT||' '||segr_descripcion FROM servicio_grupo WHERE segr_id='$relacionamento_id'");
$odatos->addField("Grupo: ",$nameGrupo);
echo $odatos->writeHTML();
?>

<!-- Lista -->
<div align="center">
<form name="frm" method="post">
<input type="hidden" name="relacionamento_id" value="<?=$relacionamento_id?>">
<?php
/*
	inicializaci� da tabla
*/


$table = new Table("","100%",4); // T�tulo, Largura, Quantidade de colunas

/*
	construcci�n de cabezera de tabla
*/
$table->addColumnHeader("<input type=\"checkbox\" name=\"checkall\"  onclick=\"checkform(frm,this)\">");
$table->addColumnHeader("C&oacute;digo",false,"2%");
$table->addColumnHeader("Descripci&oacute;n",false,"78%");
$table->addColumnHeader("Breve",false,"20%");
$table->addRow();

while ($rs->getrow()) {
	$id = $rs->field("sesg_id"); // captura la clave primaria
 	
	/*
		adiciona columnas
	*/
	$table->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");
	$table->addData(addLink($id,"javascript:abreEdicion($id)","Click aqu&iacute; para consultar o editar este registro"));        
	$table->addData(addLink($rs->field("sesg_descripcion"),"javascript:abreEdicion($id)","Click aqu&iacute; para consultar o editar este registro"));
	$table->addData($rs->field("sesg_descripbreve"));
	$table->addRow(); // adiciona linea
}

/*
	Desenha a tabela
*/
if ($rs->numrows()>0) {
	echo $table->writeHTML();
//	echo "<div class='DataFONT'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
	echo "<div class='Bordeatabla' style='width:50%;float:left' align='left'>&nbsp;</div>";
	echo "<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

} else {
	echo "<div class='DataFONT'>Ning&uacute;n registro encontrado!</div>";
}
?>

</form>
</div>

</body>
</html>
<?php
/*
	fecha a conex�o com o banco de dados
*/
$conn->close();
