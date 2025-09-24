<?php
/* Modelo de pagina que apresenta um formulario con criterios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificacion del nivel de usuario */
verificaUsuario(1);
include("catalogosServicios_class.php");
include("catalogosServicios_imagenesClass.php"); 


/* establecer conexion con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

/* Recibo parametros */
$relacionamento_id = getParam("relacionamento_id"); /* Recibo el dato de ralcionamiento entre la tabla padre e hijo */
$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$tipo = getParam("tipo");
        
/* Recibo los parametros con la clase de "paso de par�metros por la URL"  */
$param= new manUrlv1();
$param->removePar('clear');

$servicio = new servicios($relacionamento_id);
$servicio->setDatos();


/* Instancio mi clase base */
$myClass = new serviciosImagenes(0,"IMAGENES DE SERVICIOS");


$nomeCampoForm=getParam($myClass->getArrayNameVarID(0));
$busEmpty = getParam($myClass->getArrayNameVarID(1)); // 1->en la primera llamada se muestran los registros 0->en la primera llamada no se muestran los registros 
$cadena= getParam($myClass->getArrayNameVarID(2)); // cadena de busqueda
$pg = getParam($myClass->getArrayNameVarID(3)); // Tipo de Clase 
$pg = $pg?$pg:1;


/*	limpia la cadena de filtro
	si clear=1 -> esta pagina es llamada desde el menu
	si clear=2 -> esta pagina es llamada desde la busqueda avanzada (AvanzLookup)
*/
if ($clear==1) {
    $cadena="";
}


?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script type="text/javascript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>	
        <script language="javascript" src="<?php echo PATH_INC?>js/checkall.js"></script>
	<script language='JavaScript'>
	/*
		funci�n guardar
	*/
	
	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "../imes/guardar.php?_op=PortNoti";
			parent.content.document.frm.submit();
		}
	}
	/*
		se invoca desde la funcion obligacampos (libjsgen.js)
		en esta funcion se puede personalizar la validacion del formulario
		y se ejecuta al momento de gurdar los datos
	*/	
	function mivalidacion(frm) {  
		return true			
	}


	function submit() {
		parent.content.document.frm.submit();		
	}

	function agregar(idObj) {
		if (ObligaCampos(frmImg)){
			ocultarObj(idObj,10)
			parent.content.document.frmImg.target = "content";
			parent.content.document.frmImg.action = "../imes/guardar.php?_op=agrServImg";
			parent.content.document.frmImg.submit();
		}
	}

	function excluirImg() {
		if (confirm('Eliminar registros seleccionados?')) {
			parent.content.document.frmLista.target = "controle";
			parent.content.document.frmLista.action = "../imes/eliminar.php?_op=servNotImg&relacionamento_id=<?php echo $relacionamento_id?>&xxxtipo=<?php echo $tipo?>";
			parent.content.document.frmLista.submit();
		}
	}

	</script>
	<?php verif_framework(); ?>

</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle($myClass->getTitle());

/* botones */
$button = new Button;
$button->addItem(" Regresar ","catalogosServicios_buscar.php".$param->buildPars(true));

echo $button->writeHTML();

/* Control de fichas, */
$abas = new Abas();
$abas->addItem("General",false,"catalogosServicios_edicion.php?id=$relacionamento_id&clear=1&".$param->buildPars(false));
$abas->addItem("Presentaciones",false,"catalogosServiciosPresentaciones_lista.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));        

$abas->addItem("Imaganes",true);
$abas->addItem("Vinculados",false,"catalogosServiciosVinculados_lista.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));    

if(SIS_SISCONT==1){
    $abas->addItem("Asientos Contables",false,"catalogosServiciosCuentasContables_lista.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));        
}
$abas->addItem("Movimientos",false,"catalogosServicios_movimientos.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));
echo $abas->writeHTML();

/* formul�rio de pesquisa */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s");
$form->addField("C&oacute;digo:",$servicio->field('serv_id'));
$form->addField("Descripci&oacute;n: ",$servicio->field('serv_descripcion'));
echo  $form->writeHTML();


        $buttonEdit = new Button;
        $buttonEdit->align("L");
        $buttonEdit->addItem(" Agregar ","javascript:agregar('Agregar')","content",2);



	$formCon = new Form("frmImg", "", "POST", "controle", "100%",true);
        $formCon->setUpload(true);
	$formCon->setLabelWidth("20%");
	$formCon->setDataWidth("80%");
	$formCon->addHidden("___serv_codigo",$relacionamento_id); // clave primaria
        $formCon->addHidden("xxxtipo",$tipo); // clave primaria

        $formCon->addHidden("postPath",'servicios/');

	// definición de lookup
	$formCon->addBreak("<b>AGREGAR IMAGENES</b>");
        $formCon->addField("Imagen:",fileField("Imagen","area_imagen" ,'',60,"onchange=validaextension(this,'GIF,JPG,PNG')"));
        $formCon->addField("",$buttonEdit->writeHTML());

	echo $formCon->writeHTML();


        
        $sql=new serviciosImagenes_SQLlista();
        $sql->wherePadreID($relacionamento_id);
        
        $sql = $sql->getSQL();
        
	$rs = new query($conn, $sql);
        if($rs->numrows()>0){
            /*
                    botones
            */
            $button = new Button;
            $button->align("L");
            $button->addItem("Eliminar Imagen","javascript:excluirImg()","content",2);
            echo $button->writeHTML();
?>    
            <!-- Lista -->
            <div align="center">
            <form name="frmLista" method="post">
<?php
            $table = new Table("LISTADO DE IMAGENES","50%",4); // Título, Largura, Quantidade de colunas
            $table->addColumnHeader("<input type=\"checkbox\" name=\"checkall\"  onclick=\"checkform(frmLista,this)\">"); // Coluna com checkbox
            $table->addColumnHeader("Imagen",false,"70%", "L"); // Título, Ordenar?, ancho, alineación
            $table->addColumnHeader("Creado Por",false,"20%", "L"); // Título, Ordenar?, ancho, alineación
            $table->addColumnHeader("C&oacute;digo",false,"10%", "L"); // Título, Ordenar?, ancho, alineación
            $table->addRow();

            while ($rs->getrow()) {
                    $bd_seim_id = $rs->field("seim_id");
                    $bd_area_imagen=$rs->field("area_imagen");
                    $table->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$bd_seim_id\" onclick=\"checkform(frmLista,this)\">");
                    $table->addData("<img width=90 height=80 src='".PUBLICUPLOAD."/servicios/$bd_area_imagen' alt='' border=1/>");
                    $table->addData($rs->field("usua_login"));
                    $table->addData(str_pad($bd_seim_id,4,'0',STR_PAD_LEFT));

                    $table->addRow();
            }
            echo $table->writeHTML();
        }
?>
        </form>
        </div>

</body>
</html>
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();