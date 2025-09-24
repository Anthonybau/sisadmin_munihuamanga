<?
/*
	Modelo de p�gina que apresenta um formulario con crit�rios de busqueda
*/
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/*
	verificación del nível de usuario
*/
verificaUsuario(1);

/*
	establecer conexi�n con la BD
*/
$conn = new db();
$conn->open();

?>
<html>
<head>
	<title>Catalogos de Dependencia-Buscar</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="JavaScript" src="<?=PATH_INC?>js/libjsgen.js"></script>
	<script language="javascript" src="<?=PATH_INC?>js/lookup.js"></script>		
	<script language="JavaScript">
	/*
		fun��o que chama a p�gina de lista ou relat�rio,
		enviando os par�metros de pesquisa,
		altere somente o nome da p�gina
	*/
	function buscar() {
		parent.content.document.frm.target = "content";
		parent.content.document.frm.action = "catalogosDependencias_lista.php";
		parent.content.document.frm.submit();
	}
	
	/*
		fun��o que define o foco inicial do formul�rio
	*/
	function inicializa() {
		if (document.captureEvents && Event.KEYUP) {
			document.captureEvents( Event.KEYUP);
		}
		document.onkeyup = trataEvent;

		// inicia o foco no primeiro campo
		parent.content.document.frm.Sbusc_eqdi_descripcion.focus();
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
			buscar();
		}
	}
	</script>
	<? verif_framework(); ?>	
</head>
<body class="contentBODY" onLoad="inicializa()">

<?
pageTitle("Busqueda de Dependencia");

/*
	botones,
	configure conforme suas necessidades
*/

$button = new Button;
$button->addItem(" Aceptar ","javascript:buscar()");
$button->addItem(" Regresar ","catalogosDependencias_lista.php?clear=1","content");
echo $button->writeHTML();

?>

<br>
<?


/*
	formulario de pesquisa
*/
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s");
$form->addField("Dependencia: ",textField("Dependencia","Sbusc_depe_descripcion",$bd_depe_descripcion,60,60));

echo $form->writeHTML();
?>
</body>
</html>

<?
/*
	cierra la conexion a la BD, no debe ser alterado
*/
$conn->close();
