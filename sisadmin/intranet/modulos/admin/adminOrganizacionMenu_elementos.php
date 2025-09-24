<?
/*
	Modelo de p�gina que apresenta uma lista de registros
*/
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");


/*
	verifica��o do n�vel do usu�rio, altere conforme sua necessidade, quanto maior o valor,
	maior a restri��o
*/
verificaUsuario(1);


/*
	conexi�n con la base de datos
*/
$conn = new db();
$conn->open();

/*
	tratamiento de campos
*/
$id = getParam("id"); // captura la variable que viene del objeto lista
if(strlen($id)==0) $id = getParam("_id");
/*
	determina a p�gina a ser exibida, n�o precisa alterar
*/
$pg = getParam("pagina");
if ($pg == "") $pg = 1;


/*
	Limpa ordena��o e filtro, n�o deve ser alterado
*/
if (getParam("clear")==1) {
	setSession("sOrder","");
	setSession("where","");
	setSession("pagina_atual","");
}

/*
	Salva o status da p�gina atual, n�o deve ser alterado
*/
if ($_SERVER['PHP_SELF'] != $pagina_atual) {
	$mesma_pagina = false;
	setSession("pagina_atual",$_SERVER['PHP_SELF']);
} else {
	$mesma_pagina = true;
}

/*
	expresi�n SQL que define a la lista
*/
$sql ="SELECT a.*,b.simo_descripcion
       FROM sistema_modulo_opciones  a
	   LEFT JOIN sistema_modulo b ON a.simo_id=b.simo_id
	   WHERE  b.simo_id='$id' 
	   ORDER BY 1";
//	   echo $sql ;
/*
	cria��o do recordset, altere somente o �ltimo par�metro que	corresponde a quantidade de
	registros por p�gina
*/
$rs = new query($conn, $sql);
?>
<html>
<head>
	<title>Organizaci&oacute;n de Menu-Componentes Lista/Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="javascript" src="<?=PATH_INC?>js/checkall.js"></script>
	<script language="JavaScript" src="<?=PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?=PATH_INC?>js/libjsgen.js"></script>	
	<script language="JavaScript">

	/*
		funci�n guardar
	*/
	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			document.frm.target = "controle";
			document.frm.action = "../imes/guardar.php?_op=OrgMenuElem&id=<?=$id?>";
			document.frm.submit();
		}
	}
	/*
		se invoca desde la funcion obligacampos (libjsgen.js)
		en esta funci�n se puede personalizar la validaci�n del formulario
		y se ejecuta al momento de gurdar los datos
	*/	
	function mivalidacion(frm) {  
            var sError="Mensajes del sistema: "+"\n\n";
            var nErrTot=0;
	  /* valido que el campo passwors sea >= 4 digitos */ 
/*		if (frm.nr_pemo_importe.value=="0.00") {
		   frm.nr_pemo_importe.focus();
		   sError+="Campo Importe debe ser mayor a cero (0)"+"\n" 
		   nErrTot+=1;
		}
*/		if (nErrTot>0){ 		
			alert(sError)
			return false
		}else
			return true			
	}

	function excluir() {
		if (confirm('Eliminar registros seleccionados?')) {
			document.frmLista.target = "controle";
			document.frmLista.action = "../imes/eliminar.php?_op=OrgMenuElem&id=<?=$id?>";
			document.frmLista.submit();
		}
	}
	

	/*
		tratamento para capturar tecla enter
	*/
	function trataEvent(e) {
		if( !e ) { //verifica se � IE
			if( window.event ) {
				e = window.event;
			} else {
				//falha, n�o tem como capturar o evento
				return;
			}
		}
		if( typeof( e.keyCode ) == 'number'  ) { //IE, NS 6+, Mozilla 0.9+
			e = e.keyCode;
		} else {
			//falha, n�o tem como obter o c�digo da tecla
			return;
		}
		if (e==13) {
			pesquisar();
		}
	}
	

	</script>

	<? verif_framework(); ?>			
	
</head>
<body class="contentBODY">

<?
pageTitle("Edici&oacute;n de Elementos de Componente");

/*
	botones de edici�n
*/
$retorno = $_SERVER['QUERY_STRING'];

$buttonEdit = new Button;
$buttonEdit->addItem(" Agregar ","javascript:salvar('Agregar')","content",2);
$buttonEdit->addItem(" Regresar ","adminOrganizacionMenu_lista.php?$retorno","content");
echo $buttonEdit->writeHTML();

/*
	Control de fichas,
*/
$abas = new Abas();
$abas->addItem("Componente",false,"adminOrganizacionMenu_edicion.php?id=$id");
$abas->addItem("Elementos",true);

echo $abas->writeHTML();
echo "<br>";

/*
	formul�rio de edicion
*/

$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("f_id",''); // clave primaria
$form->addHidden("___simo_id",$id); // clave primaria
$nameComponente=GetDbValue("SELECT simo_descripcion FROM sistema_modulo WHERE simo_id='$id'");
$form->addField("Componente: ",$nameComponente);

$form->addField("C&oacute;digo: ",textField("C&oacute;digo","sr_smop_id",'',20,20));
$form->addField("Elemento: ",textField("Elemento","sr_smop_descripcion",'',60,60));
$form->addField("Enlace: ",textField("Enlace","sx_smop_page",'',120,120));

echo $form->writeHTML();

pageTitle("","Lista");


/*
	botones
*/
$button = new Button;
/*
	botones de navegacion de la lista
*/
$pg_ant = $pg-1;
$pg_prox = $pg+1;
if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,$_SERVER['PHP_SELF']."?pagina=$pg_ant" ,"content");
if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,$_SERVER['PHP_SELF']."?pagina=$pg_prox","content");

/*
	botones de la lista
*/
$button->addItem("Eliminar","javascript:excluir()","content",2);
echo $button->writeHTML();
?>

<!-- Lista -->
<div align="center">
<form name="frmLista" method="post">
<input type="hidden" name='f_id' value='<?=$id?>'>
<?
/* inicializaci�n de la tabla */
$table = new Table("","100%",8); // T�tulo, Largura, Quantidade de colunas

/* Configuraci�n de las columnas de las tablas */
$table->addColumnHeader("<input type=\"checkbox\" name=\"checkall\"  onclick=\"checkform(frmLista,this)\">"); // Coluna com checkbox
$table->addColumnHeader("C&oacute;digo",false,"10%", "L"); // T�tulo, Ordenar?, ancho, alineaci�n
$table->addColumnHeader("Descripci&oacute;n",false,"40%", "L"); // T�tulo, Ordenar?, ancho, alineaci�n
$table->addColumnHeader("Enlace",false,"50%", "L"); // T�tulo, Ordenar?, ancho, alineaci�n

$table->addRow(); // adiciona linea (TR)

while ($rs->getrow()) {
	$id = $rs->field("smop_id"); // captura a chave prim�ria do recordset

	if($rs->field("plem_modingreso")==0)
		$table->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frmLista,this)\">");
	else
		$table->addData("");
		
	$table->addData($id );
	$table->addData($rs->field("smop_descripcion"));
	$table->addData($rs->field("smop_page"));
	$table->addRow();
}


/*
	Desenha a tabela
*/
if ($rs->numrows()>0) {
	echo $table->writeHTML();
	echo "<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";
	//echo "<div class='DataFONT'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
} else {
	echo "<div class='DataFONT'>Ning&uacute;n registro encontrado!</div>";
}
?>
</form>
</div>

</body>
</html>
<?
/*
	cierra la conexion con la base de datos
*/
$conn->close();
?>