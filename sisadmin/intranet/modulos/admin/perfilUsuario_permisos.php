<?php
/*
	Est� p�gina muestra um modelo de asociaci�n de registros,
	donde uno o mas registros seleccionados son grabados
*/
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
include("perfilUsuario_class.php");

/*
	verificaci�n del n�vel de usuario
*/
verificaUsuario(1);

/*
	liga/desliga utilizacion de filtro
*/
$usaFiltro = true;

/*recibo los parametro de la URL*/
$param= new manUrlv1();
$param->removePar('relacionamento_id'); /* Remuevo el par�metro */

/*
	recupera clave, de existir
*/
$id = getParam("relacionamento_id");


/*
	establecer conexi�n con la BD
*/
$conn = new db();
$conn->open();


// Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("cargar");

function cargar($pemo_id)
{
	global $conn,$id,$usaFiltro,$rsUser;
        $response = new xajaxResponse();


		$otable = new AddTableForm();
		$otable->setLabelWidth("20%");
		$otable->setDataWidth("80%");
		$otable->addHidden("_pemo_id",$pemo_id);
		/*
			lista destino,
		*/
		$sql = "SELECT a.smop_id as id,c.simo_descripcion||': '||b.smop_descripcion as val 
		        FROM perfilu_modulo_menu a 
		        LEFT JOIN sistema_modulo_opciones b ON a.smop_id=b.smop_id 
		        LEFT JOIN sistema_modulo c ON b.simo_id=c.simo_id 
			    WHERE a.pemo_id=$pemo_id 
		        ORDER BY 1 ";

//		echo $sql;
		
		$rsDestino = new query($conn, $sql);
		
		while ($rsDestino->getrow()) $aLista[] = "'".$rsDestino->field("id")."'";
		$lista=is_array($aLista)?implode(",",$aLista):"'0'";
		
		$rsDestino->skiprow(0); // Retorno al registro 0
		
		$rsDestino->free();
		$rsDestino = new query($conn, $sql);
		
		
		$sql = "SELECT a.smop_id as id,b.simo_descripcion||': '||a.smop_descripcion as val 
		       FROM sistema_modulo_opciones a 
		       LEFT JOIN sistema_modulo b on a.simo_id=b.simo_id 
			   WHERE b.sist_id=(SELECT sist_id FROM perfilu_modulo WHERE pemo_id=$pemo_id) AND a.smop_id NOT IN ($lista) 
		       ORDER by 1";
		 
		$rsOrigem = new query($conn, $sql);
        
        // montagem da lista de origen
		if ($usaFiltro) {
			$montaOrigem  = "FILTRO&nbsp<input type=\"text\" name=\"searchOrigem\" size=\"30\" style=\"WIDTH: 92%\" onKeyUp=\"javascript:filterOrigem.set(this.value,document.frm.f_destino)\"><br>";	
		} else {
			$montaOrigem = "";
		}
		$montaOrigem .= "<select name=\"f_origem[]\" id=\"f_origem\" multiple size=\"16\" style=\"WIDTH: 100%\" valign=\"top\">";
		while ($rsOrigem->getrow()) {
			$montaOrigem .= "<option value='".$rsOrigem->field("id")."'>".$rsOrigem->field("val")."</option>";
		}
		$montaOrigem .= "</select>";
		
		// montagem da lista de destino
		if ($usaFiltro) {
			$montaDestino  = "FILTRO&nbsp<input type=\"text\" name=\"searchDestino\" size=\"30\" style=\"WIDTH: 92%\" onKeyUp=\"javascript:filterDestino.set(this.value,document.frm.f_origem) \"><br>";	
		} else {
			$montaDestino = "";
		}
		$montaDestino .= "<select name=\"f_destino[]\" id=\"f_destino\" multiple size=\"16\" style=\"WIDTH: 100%;\">";
		while ($rsDestino->getrow()) {
			$montaDestino .= "<option value='".$rsDestino->field("id")."'>".$rsDestino->field("val")."</option>";
		}
		$montaDestino .= "</select>";
		
		// pinta botones
		$montaBotoes  = "<input type=\"button\" onClick=\"if(move(parent.content.document.frm.f_origem,parent.content.document.frm.f_destino,parent.content.document.frm.searchDestino)){filterDestino.init()}\" value=\" >> \">";
		$montaBotoes .= "<br><br>";
		$montaBotoes .= "<input type=\"button\" onClick=\"if(move(parent.content.document.frm.f_destino,parent.content.document.frm.f_origem,parent.content.document.frm.searchOrigem)){filterOrigem.init()}\" value=\" << \">";
		
		
		// layout
		$table = new Table("","100%",3);
		// cabecera
		$table->addColumnHeader("Opciones de Menu Disponibles",false,"47%","C");
		$table->addColumnHeader("",false,"6%","C");
		$table->addColumnHeader("Opciones de Menu Seleccionados",false,"47%","C");
		$table->addRow();
		// caja de selecci�n
		$table->addData($montaOrigem, "C");
		$table->addData($montaBotoes, "C");
		$table->addData($montaDestino, "C");
		$table->addRow();
		$contenido_respuesta.=$otable->writeHTML().'<br>';
		$contenido_respuesta.=$table->writeHTML();
		$contenido_respuesta.=help("Permisos","Mantenga la tecla CTRL presionada para efetuar selecci&oacute;n m�ltipla de registros.",2);

        $response->addAssign('divPermisos','innerHTML', $contenido_respuesta);
        return $response;
}

$xajax->processRequests();
// fin para Ajax


?>
<html>
<head>
	<title>Usuario-Permisos</title>	
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>	
	<script language="JavaScript" src="<?php echo PATH_INC?>js/moveselect.js"></script>
	<?php if ($usaFiltro) { ?>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/filterlist.js"></script>
	<?php } ?>
	<script language="JavaScript">
	/*
		fun��o que chama a rotina de processamento,
		altere somente o nome da p�gina
	*/
	function salvar(idObj) {
		ocultarObj(idObj,10)
		selecionaObjetosAssociados();
		parent.content.document.frm.target = "controle";
		parent.content.document.frm.action = "perfilUsuario_permisosGuardar.php";
		parent.content.document.frm.submit();
	}
	
	function inicializa() {
		<?php if ($usaFiltro) { ?>
		parent.content.document.frm.searchOrigem.focus();
		<?php } ?>
	}
	</script>
    <!-- Este es la impresion de las rutinas JS que necesita Xajax para funcionar -->
    <?php 
    $xajax->printJavascript(PATH_INC.'ajax/'); 
    verif_framework(); 
    ?>	
			
</head>
<!--body class="contentBODY" onLoad="inicializa()"-->
<body class="contentBODY" >
<?php
pageTitle("Permisos asignados a un M&oacute;dulo de Perfil de Usuario");


// botones de edici�n,
// configure conforme sua necessidade
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",4);
$button->addItem(" Regresar ","perfilUsuario_buscar.php?clear=1&busEmpty=1","content");
echo $button->writeHTML();

// Controle de abas,
// true, para el aba actual,
// false, para cualquier otro aba,
// configure conforme al ejemplo de abajo
$abas = new Abas();
$abas->addItem("General",false,"perfilUsuario_edicion.php?id=$id&".$param->buildPars(false));
$abas->addItem("M&oacute;dulos",false,"perfilUsuario_modulos.php?relacionamento_id=$id&".$param->buildPars(false));
$abas->addItem("Permisos",true);

echo $abas->writeHTML();

//datos de cabecera
//$form = new Form("frmCaption", "", "POST", "controle", "100%",false);
$form = new AddTableForm();
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
	
$myClass = new clsPerfil($id,'');
$myClass->setDatos();
$form->addField("Perfil: ",$myClass->field("perf_descripcion"));


$sqlMod = "SELECT a.pemo_id,b.sist_descripcion
				   FROM perfilu_modulo a
				   LEFT JOIN sistema b ON a.sist_id=b.sist_id
				   WHERE a.perf_id=$id
				   ORDER BY 1 "; //tipos de documentos de identidad

$form->addField("M&oacute;dulo: ",listboxField("M&oacute;dulo",$sqlMod,"tr_modulo","","-- Seleccione M&oacute;dulo --","onChange=xajax_cargar(this.value);document.getElementById('divPermisos').innerHTML = 'Cargando....';"));

?>

<form name="frm" method="post" onSubmit="disable(this)">
<!-- vari�vel de controle -->
<input type="hidden" name="rodou" value="s">
<!-- chave prim�ria -->
<input type="hidden" name="f_id" value="<?php echo $id?>">

<?php 
echo $form->writeHTML()
?>

<div id='divPermisos'></div>

<!-- layout do formul�rio -->
<?php
if ($usaFiltro) { ?>
<script>
var filterOrigem = new filterlist(document.frm.f_origem);
var filterDestino = new filterlist(document.frm.f_destino);
</script>
<?php } ?>
</form>


</body>
</html>
<?php
$conn->close();