<?php
/*
	Modelo de p�gina que apresenta una lista de registros
*/
include("../../library/library.php");


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

$tipo=getParam("tipo");

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


$sql = "SELECT  a.segr_id,
                a.segr_descripcion,
		b.tabl_descripcion AS vinculo,
                CASE WHEN a.segr_almacen=1 THEN 'SI' ELSE 'NO' END AS es_almacen,
                CASE WHEN a.segr_destino=1 THEN 'INGRESO'
                     WHEN a.segr_destino=2 THEN 'EGRESO'
                     WHEN a.segr_destino=3 THEN 'INGRESO/EGRESO'
                END AS destino,
                a.segr_convenio_porcent,
                CASE WHEN a.segr_estado=1 THEN 'ACTIVO' ELSE 'INACTIVO' END AS estado
	    FROM  catalogos.servicio_grupo a
            LEFT JOIN catalogos.tabla b ON a.segr_vinculo=b.tabl_codigo AND b.tabl_tipo='VINCULO_GRUPO_SERVICIO'
            WHERE COALESCE(segr_tipo,'') ILIKE '%$tipo%'
            ORDER BY 1 DESC
            ";

//echo $sql;

/*
	crea el recordset, elo �ltimo parametro corresponde a la cantidad de
	registros por p�gina
*/
$rs = new query($conn, $sql, $pg, 40);

?>
<html>
<head>
	<title>Grupos de Servicios M&eacute;dicos-Lista</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="javascript" src="../../library/js/checkall.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>			
	
	<script language="JavaScript">
	/*
		funcion que llama a la rutina d exclusion de registros, incluye el nombre de la p�gina a ser llamada
	*/
	function excluir() {
		if (confirm('Eliminar registros selecionados?')) {
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "../imes/eliminar.php?_op=GrpoServ&tipo=<?php echo $tipo ?>";
			parent.content.document.frm.submit();
		}
	}
	</script>
	<?php 
        verif_framework(); 
        ?>		
		
</head>
<body class="contentBODY">

<?php
//if($tipo=="M")
//    pageTitle("Grupo de Servicios M&eacute;dicos","");
//elseif(inlist($tipo,"T,S"))
    pageTitle("Grupo de Bienes/Servicios","");
//else
//    pageTitle("Grupo de Transacciones","");
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
$button->addItem("Nuevo","catalogosServiciosGrupos_edicion.php?tipo=$tipo","content",2);
$button->addItem("Eliminar","javascript:excluir()","content",2);
echo $button->writeHTML();
?>

<!-- Lista -->
<div align="center">
<form name="frm" method="post">
<?php
/*
	inicializaci�n de la tabla
*/
$table = new Table("","100%",6); // T�tulo, ancho, Cantidad de columas

/*
	configuraci�n de las columnas de las tablas
*/

$table->addColumnHeader("<input type=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">"); // Coluna com checkbox
$table->addColumnHeader("C&oacute;digo",false,"2%", "C"); 
$table->addColumnHeader("Descripci&oacute;n",false,"58%", "C"); 
$table->addColumnHeader("Vinculo",false,"20%", "C"); 
$table->addColumnHeader("Ctrl.Alm",false,"5%", "C"); 
$table->addColumnHeader("Destino",false,"5%", "C"); 
if(SIS_EMPRESA_TIPO==3){//beneficencias
    $table->addColumnHeader("% Convenio",false,"9%", "C"); 
    $table->addColumnHeader("Est",false,"1%", "C"); 
}else{
    $table->addColumnHeader("Est",false,"10%", "C"); 
}
$table->addRow(); // adiciona la linea (TR)

while ($rs->getrow()) {
	$id = $rs->field("segr_id"); // captura la clave primaria del recordsource
	$table->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");				
        $table->addData(addLink($id,"catalogosServiciosGrupos_edicion.php?id=$id&pagina=$pg&tipo=$tipo","Clique para consultar o editar registro"));        
	$table->addData(addLink($rs->field("segr_descripcion"),"catalogosServiciosGrupos_edicion.php?id=$id&pagina=$pg&tipo=$tipo","Clique para consultar o editar registro"));
	$table->addData($rs->field("vinculo"));	
        $table->addData($rs->field("es_almacen"),"C");
        $table->addData($rs->field("destino"),"C");
        if(SIS_EMPRESA_TIPO==3){//beneficencias
            $table->addData($rs->field("segr_convenio_porcent"),"C");	
        }
        $table->addData(substr($rs->field("estado"),0,3),"C");	
	$table->addRow();
}

echo "<div class='DataFONT' align='right'><b>$filtrado</b></div>";

/*
	escribe la tabla
*/
if ($rs->numrows()>0) {
	echo $table->writeHTML();
	echo "<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
	echo "<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

} else {
	echo "<div class='DataFONT'>Ningun registro encontrado!</div>";
}
?>
</form>
</div>

</body>
</html>
<?php
/*
	cierra la conexion a la BD, no debe ser alterado
*/
$conn->close();