<?
/*
	Modelo de p�gina que apresenta una lista de registros
*/
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");


/*
	verifica el n�vel de usu�rio, si el usuario tiene acceso sobre el menu
*/
verificaUsuario(1);


/*
	establecer conexi�n con la BD
*/
$conn = new db();
$conn->open();

/*
	determina la p�gina a ser mostrada
*/
$pg = getParam("pagina");
if ($pg == "") $pg = 1;


/*
	limpia la ordenacion y filtro
*/
if (getParam("clear")==1) {
	setSession("sOrder","");
	setSession("where","");
	setSession("pagina_atual","");
}

/*
	graba el estado de la p�gina actual
*/
if ($_SERVER['PHP_SELF'] != $pagina_atual) {
	$misma_pagina = false;
	setSession("pagina_atual",$_SERVER['PHP_SELF']);
} else {
	$misma_pagina = true;
}


/*
	construye la ordenaci�n
*/
$iSort = getParam("Sorting");
$iSorted = getParam("Sorted");
if ((!$iSort)&&(!$misma_pagina)) {
	$form_sorting = "";
	$iSort = 1; // configuro la ordenacion inicial de la lista deacuerdo a la relacion de campos de la tabla
	$iSorted = ""; // si se inicia de forma ASCENDENTE O DESCENDENTE
}
if ($iSort) {
	if ($iSort == $iSorted) {
		$form_sorting = "";
		$sDirection = " desc";
		$sSortParams = "Sorting=" . $iSort . "&Sorted=" . $iSort . "&";
	} else {
		$form_sorting = $iSort;
		$sDirection = " asc";
		$sSortParams = "Sorting=" . $iSort . "&Sorted=" . "&";
	}
	/*
		definicion de columnas de ordenaci�n de acuerdo a las columnas de las tables
	*/
	if ($iSort == 1) setSession("sOrder"," order by 1" . $sDirection); 
	if ($iSort == 2) setSession("sOrder"," order by 2" . $sDirection); 
	if ($iSort == 3) setSession("sOrder"," order by 3" . $sDirection); 	
	if ($iSort == 4) setSession("sOrder"," order by 4" . $sDirection); 		

}



if (getParam("rodou")=="s") { // configuracion para filtros...
	$filtro = "";
	/*
		construye la cadena WHERE 
	*/
//	if (getParam("Sbusc_afp_nombre") != "") $filtro .= "and a.afp_nombre LIKE '%" . getParam("Sbusc_afp_nombre") . "%'";
//	setSession("where",$filtro);
}

/*
	flag para informar si se filtra o no
*/
if (strlen(getSession("where"))>0) {
	$filtrado = FILTRO_ATIVO;
} else {
	$filtrado = "";
}

/*
	expresion SQL que define la lista, construcccion libre, la cual concatena las
	sessions WHERE y sOrder, conforme al ejemplo  de abajo
*/


setSession("sist_id","97ADPORTAL");

$sql ="SELECT a.smop_id,
  			  a.smop_descripcion,
  			  a.smop_page,
			  b.simo_id,
			  b.simo_descripcion,
			  b.simo_page
       FROM sistema_modulo b
	   LEFT JOIN sistema_modulo_opciones a ON a.simo_id=b.simo_id
	   WHERE b.sist_id='".getSession("sist_id")."' ".
	   "ORDER BY b.sist_id,b.simo_id,a.smop_id";

//echo $sql ;
/*
	crea el recordset, elo �ltimo parametro corresponde a la cantidad de
	registros por p�gina
*/
$rs = new query($conn, $sql);

?>
<html>
<head>
	<title>Organizaci&oacute;n de Men&uacute;-Lista</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="javascript" src="<?=PATH_INC?>js/checkall.js"></script>
	<script language="JavaScript" src="<?=PATH_INC?>js/libjsgen.js"></script>			
	
	<script language="JavaScript">
	/*
		funcion que llama a la rutina d exclusion de registros, incluye el nombre de la p�gina a ser llamada
	*/
	function excluir() {
		if (confirm('Eliminar registros seleccionados?')) {
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "../imes/eliminar.php?_op=OrgMenuComp";
			parent.content.document.frm.submit();
		}
	}

	/*
		funcion para controlar la exibicion de una lista encadenada
	*/
	function openList(key) {
		var oKey = parent.content.document.getElementById(key);
		var icone = parent.content.document.getElementById('fold_'+key);
		if (oKey.style.visibility == "hidden"){
			oKey.style.visibility = "visible";
			oKey.style.display = "block";
			icone.innerHTML = "&nbsp;-&nbsp;";
			
		} else {
			oKey.style.visibility = "hidden";
			oKey.style.display = "none";
			icone.innerHTML = "&nbsp;+&nbsp;";
		}
	}
	</script>
	<? verif_framework(); ?>		
		
</head>
<body class="contentBODY">

<?
$nameModulo=GetDbValue("SELECT sist_breve FROM sistema WHERE sist_id='".getSession("sist_id")."' ");
pageTitle("Componentes del M&oacute;dulo: ".$nameModulo);

/*
	botones de accion
*/
$button = new Button;

/*
	botones de navegaci�n de la lista, no debe ser alterada
*/
$pg_ant = $pg-1;
$pg_prox = $pg+1;
if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,$_SERVER['PHP_SELF']."?pagina=$pg_ant" ,"content");
if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,$_SERVER['PHP_SELF']."?pagina=$pg_prox","content");

/*
	botones de accion de la lista, altere deacuerdo a sus necesidades
*/
$button->addItem("Nuevo","adminOrganizacionMenu_edicion.php","content",2);
$button->addItem("Eliminar","javascript:excluir()","content",2);
echo $button->writeHTML();
?>

<!-- Lista -->
<div align="center">
<form name="frm" method="post">
<?
/*
	inicializaci�n de la tabla
*/
$table = new Table("","100%",6); // T�tulo, ancho, Cantidad de columas

/*
	configuraci�n de las columnas de las tablas
*/

$table->addColumnHeader("<input type=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">"); // Coluna com checkbox
$table->addColumnHeader("<acronym title='Tipos de Tarifas'>&nbsp;+&nbsp;</acronym>");
$table->addColumnHeader("Componente",true,"70%", "L"); // T�tulo, Ordenar?, ancho, alineaci�n
$table->addColumnHeader("Enlace",true,"20%", "L"); // T�tulo, Ordenar?, ancho, alineaci�n
$table->addRow(); // adiciona la linea (TR)
$rs->getrow();
$i=-1;
do{
	$id = $rs->field("simo_id"); // captura la clave primaria del recordsource
	// definici�n de lista encadenas
	$hay_encadenados=false;
	if($rs->field("smop_descripcion")){
		$hay_encadenados=true;
        }
		$table_encadeada = new Table("","100%",2);
		$table_encadeada->addColumnHeader("Elemento",false,"40%","L");
		$table_encadeada->addColumnHeader("Enlace",false,"60%","L");
		$table_encadeada->addRow();
		do{
			$table_encadeada->addData($rs->field("smop_descripcion"));
			$table_encadeada->addData($rs->field("smop_page"));			
			$table_encadeada->addRow();
			$i++;
 		}
		while ($rs->getrow() && $rs->field("simo_id")==$id) ;
		$rs->skiprow($i);
		$rs->getrow();
        //}
		
		$table->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");				

		/*si hay lista encadenados*/
		if ($hay_encadenados) {
			$table->addData("<span id='fold_$id' style='cursor: pointer' onClick=\"javascript:openList('$id')\">&nbsp;+&nbsp;</span>","C");
		} else 	$table->addData("&nbsp;");
	
		$table->addData(addLink($rs->field("simo_descripcion"),"adminOrganizacionMenu_edicion.php?id=$id&pagina=$pg","Clique para consultar o editar registro"));
		$table->addData($rs->field("simo_page"));		
		$table->addRow();
		/*si hay lista encadenados*/
		if ($hay_encadenados) {
			$table->addBreak("<div id=\"$id\" style='visibility: hidden; display: none; margin-left: 60px'>".$table_encadeada->writeHTML()."</div>", false);
		}
		
}
while ($rs->getrow());






echo "<div class='DataFONT' align='right'><b>$filtrado</b></div>";

/*
	escribe la tabla
*/
if ($rs->numrows()>0) {
	echo $table->writeHTML();
	echo "<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

} else {
	echo "<div class='DataFONT'>Ningun registro encontrado!</div>";
}
?>
</form>
</div>

</body>
</html>
<?
/*
	cierra la conexion a la BD, no debe ser alterado
*/

$conn->close();
wait('');
?>