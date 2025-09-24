<?php
/* Modelo de p�gina que apresenta um formulario con crit�rios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("catalogosServiciosVinculados_class.php"); 
include("catalogosServicios_class.php"); 

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

/* Recibo par�metros */
$relacionamento_id = getParam("relacionamento_id"); /* Recibo el dato de ralcionamiento entre la tabla padre e hijo */

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente

/* Recibo los par�metros con la clase de "paso de par�metros por la URL"  */
$param= new manUrlv1();
$param->removePar('clear');
$param->removePar('clear');
$param->removePar('relacionamento_id');

/* Instancio mi clase base */
$myClass = new servicioVinculados(0,"Lista de Servicios Vinculados");

/* Instancio la clase padre */
$padre = new servicios($relacionamento_id,"");
$padre->setDatos();

/*	limpia la cadena de filtro
	si clear=1 -> esta pagina es llamada desde el menu
	si clear=2 -> esta pagina es llamada desde la busqueda avanzada (AvanzLookup)
*/
if ($clear==1) {
    $cadena="";
}

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "servicioVinculados","buscar"),"");
$xajax->registerExternalFunction(array("buscarServicioVinculado", "servicios","buscarServicioVinculado"),"");
$xajax->registerFunction("eligeServicio");
$xajax->registerFunction("eligeTipoPrecio");
$xajax->registerFunction("updateDestino");

function eligeServicio($serv_id,$cadena,$tabl_tipoprecio){
    global $conn,$relacionamento_id;

    $objResponse = new xajaxResponse();
    $sql="INSERT INTO catalogos.servicio_vinculados (serv_codigo,
                                serv_codigo_vinculado,
                                tabl_tipoprecio,
                                usua_id)
                        VALUES ($relacionamento_id,
                                $serv_id,
                                $tabl_tipoprecio,".
                                getSession("sis_userid").")";

    //echo $sql;
    $conn->execute($sql);
    $error=$conn->error();		
    if($error){ 
        $objResponse->addAlert($error);
    }
    else{
        //$objResponse->addScript("document.frm._DummySx_servicio.value=''");
        $objResponse->addScript("document.frm.tr_serv_codigo.value=0");
        //$objResponse->addAssign('divResultado1','innerHTML', "");
        $objResponse->addScript("xajax_buscar(1,'$relacionamento_id','DivResultado')");
        $objResponse->addScript("xajax_buscarServicioVinculado('$cadena','divResultado1')");

    }
    return $objResponse;                
}

function eligeTipoPrecio($op, $valor, $serv_codigo){
    global $conn;

    $serv_codigo= intval($serv_codigo);
    $objResponse = new xajaxResponse();
    $sepr_precio=getDbValue("SELECT sepr_precio
                                FROM catalogos.servicio_precios
                                WHERE tabl_tipoprecio=$valor
                                    AND serv_codigo=$serv_codigo");

    $objResponse->addAssign('precio_'.$serv_codigo,'innerHTML',$sepr_precio);
    return $objResponse;                
}


function updateDestino($tabl_tipodestino,$sevi_id){
    global $conn;

    $objResponse = new xajaxResponse();
    $tabl_tipodestino=intval($tabl_tipodestino);
    
    if($tabl_tipodestino>=0){
        $sql="UPDATE catalogos.servicio_vinculados 
                SET tabl_destino_ocupante=$tabl_tipodestino
                WHERE sevi_id=$sevi_id";

        //echo $sql;
        $conn->execute($sql);        
    }
    
    $objResponse->addScript("parent.content.location.reload();");
    return $objResponse;                
}

$xajax->processRequests();

// fin para Ajax
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script type="text/javascript" src="<?php echo PATH_INC?>jquery/jquerypack.js"></script>
	<script type="text/javascript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>	
        <script language="javascript" src="<?php echo PATH_INC?>/js/janela.js"></script>			        
	<script type="text/javascript" src="<?php echo PATH_INC?>tablesorter/jquery.tablesorter.js"></script>
	<script language="JavaScript">
		<?php echo $myClass->jsDevolver($nomeCampoForm);?>
		<?php echo $myClass->jsSorter($nomeCampoForm);?>		

		/* funcion que define el foco inicial del formulario */
		function inicializa() {
			$("#tLista tbody input[@type=checkbox]").removeAttr("checked"); /* Desmarco todos los checkbox, esto porque al insertar o editar pueden quedar algunos marcados, por eso al refrescar la p�g. se desmarcan todos */
			document.frm._DummySx_servicio.focus();
		}

                function mivalidacion(){
                    var sError="Mensajes del sistema: "+"\n\n";
                    var nErrTot=0;
                    
                    if (nErrTot>0){
                        alert(sError)
                        return false
                    }else
                        return true
                }

		function excluir() {
			regSel=$("#tLista tbody input[@type=checkbox]").is(":checked");
			if(regSel){ 
				if (confirm('Desea Eliminar el(los) registro(s) selecionado(s)?')) {
					document.frm.target = "controle";
					document.frm.action = "<?=$myClass->getNameFile()."?control=".base64_encode(2)."&relacionamento_id=$relacionamento_id&".$param->buildPars(false)?>";
					document.frm.submit();
				}
			}else{
				alert('Seleccione el(los) registro(s) que desea eliminar')
			}
		}
                


	</script>
	<script type="text/javascript" src="<?php echo PATH_INC?>js/jquerytablas.js"></script> <!-- Esta l�nea debe ir aqu� para luego de que se aplique el orden se refrescan los css de la tabla -->

    <?php 
    $xajax->printJavascript(PATH_INC.'ajax/'); 
    verif_framework(); 
    ?>

</head>
<body class="contentBODY" onLoad="inicializa()" >
<?php
pageTitle($myClass->getTitle());

/* botones */
$button = new Button;
$button->addItem(" Eliminar ","javascript:excluir()");
$button->addItem(" Regresar ","catalogosServicios_buscar.php".$param->buildPars(true));
echo $button->writeHTML();


/* Control de fichas, */
$abas = new Abas();
$abas->addItem("General",false,"catalogosServicios_edicion.php?id=$relacionamento_id&clear=1&".$param->buildPars(false));
$abas->addItem("Presentaciones",false,"catalogosServiciosPresentaciones_lista.php?relacionamento_id=$relacionamento_id&clear=1&".$param->buildPars(false));        
if(inlist($padre->field('segr_vinculo'),"5")){ //
        $abas->addItem("Imagenes",false,"catalogosServicios_imagenes.php?relacionamento_id=$relacionamento_id&clear=1&".$param->buildPars(false));
}
$abas->addItem("Vinculados",true); 

if(SIS_SISCONT==1){
    $abas->addItem("Asientos Contables",false,"catalogosServiciosCuentasContables_lista.php?relacionamento_id=$relacionamento_id&clear=1&".$param->buildPars(false));
}

$abas->addItem("Movimientos",false,"catalogosServicios_movimientos.php?relacionamento_id=$relacionamento_id&clear=1&".$param->buildPars(false));
echo $abas->writeHTML();

/* formul�rio de pesquisa */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s");

$form->addField("C&oacute;digo:",$padre->field('serv_codigo'));
$form->addField("Descripci&oacute;n: ",$padre->field('serv_descripcion'));
$form->addField("Grupo: ",$padre->field('segr_id').' '.$padre->field('grupo'));
$form->addLine();
$form->addLine();
$form->addBreak("AGREGAR SERVICIO");
$form->addField("Servicio: ",textField("Cadena de B&uacute;squeda","_DummySx_servicio","",60,80)."&nbsp;<input type=\"button\" onClick=\"xajax_buscarServicioVinculado(document.frm._DummySx_servicio.value,'divResultado1');document.getElementById('divResultado1').innerHTML = 'Espere, Buscando...'\" value=\"Buscar\">");
$form->addHidden("tr_serv_codigo",0);             
$form->addHtml("<tr><td colspan=2><div id='divResultado1'>\n");
$form->addHtml("</div></td></tr>\n");

//
//$form->addField("C&oacute;digo/Apellidos: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">");
//
$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");
$form->addHtml($myClass->buscar(2,"$relacionamento_id",'DivResultado'));
$form->addHtml("</div></td></tr>\n");

echo  $form->writeHTML();
?>
</body>
</html>
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();