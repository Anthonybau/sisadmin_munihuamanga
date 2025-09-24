<?php
/* Modelo de p�gina que apresenta um formulario con crit�rios de busqueda */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
/* Cargo mi clase Base */
include("adminUsuario_class.php");
include("../catalogos/catalogosDependencias_class.php");

/*elimino la variable de session */
if (isset($_SESSION["ocarrito"])) unset($_SESSION["ocarrito"]);

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente
$id = getParam("id");
 
$param= new manUrlv1();
$param->removePar('clear');
$param->removePar('nbusc_depe_id');
$param->removePar('nbusc_origen');

$myClass = new clsUsers(0,"Administraci&oacute;n de Usuarios");

$nomeCampoForm=getParam($myClass->getArrayNameVarID(0));
$busEmpty = getParam($myClass->getArrayNameVarID(1)); // 1->en la primera llamada se muestran los registros 0->en la primera llamada no se muestran los registros
$cadena= getParam($myClass->getArrayNameVarID(2)); // cadena de busqueda

$periodo= getParam('periodo');
$depe_id= getParam('nbusc_depe_id');
$origen= getParam('nbusc_origen');

$pg = getParam($myClass->getArrayNameVarID(3)); // Tipo de Clase
$pg = $pg?$pg:1;


if ($clear==1) {
    setSession("cadSearch","");
}

// Inicio Para Ajax
require_once("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscar", "clsUsers","buscar"),"");
$xajax->registerFunction("obligarCambioContrasena");
$xajax->registerFunction("subDependencia");
$xajax->registerFunction("imprimir");

function obligarCambioContrasena(){    
        global $conn;
	$objResponse = new xajaxResponse();

        $sql="UPDATE admin.usuario
                     SET usua_activo=3 /*obligado a cambiar contraseña*/
                     WHERE usua_activo=1 AND usua_id>1 ";
        
        //echo $DivId;
        $conn->execute($sql);
        $error=$conn->error();
        if($error){
            $objResponse->addAlert($error);
        }else{
            $objResponse->addScript("parent.content.location.reload()");
        }
        return $objResponse;
}

function subDependencia($op,$depe_id,$sub_dependencia,$NameDiv)
{
    $objResponse = new xajaxResponse();

    $otable = new AddTableForm();
    $otable->setLabelWidth("20%");
    $otable->setDataWidth("80%");

    $sub_dependencia=$sub_dependencia?$sub_dependencia:$depe_id;
            
    $sqlDependencia=new dependencia_SQLBox($depe_id);
    $sqlDependencia=$sqlDependencia->getSQL();

    $otable->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia,"tr_sub_dependencia","","-- Todos --","","","class=\"my_select_box\""));        
    $contenido_respuesta=$otable->writeHTML();
    
    if($op==1){
        $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);
        $objResponse->addScript("$('.my_select_box').select2({
                                        placeholder: 'Seleccione un elemento de la lista',
                                        allowClear: true,
                                        width:'90%'
                                        });");  
        
        return $objResponse;
    }else{
        return $contenido_respuesta;
    }
}   

function imprimir($NameDiv)
{
    global $conn,$nbusc_depe_id;
    $objResponse = new xajaxResponse();

    $objResponse->addScript('$( "#title-myModal" ).text( " Imprimir")');            
    $form = new Form("frmImp", "", "POST", "controle", "100%",true);
    $form->setLabelWidth("20%");
    $form->setDataWidth("80%");

    $depeid=$nbusc_depe_id;
        
    /* Instancio la Dependencia */
    $sqlDependencia=new dependenciaSuperior_SQLBox(getSession("sis_depe_superior"));
    $sqlDependencia=$sqlDependencia->getSQL();        
    //FIN OBTENGO

    $form->addField("Dependencia Superior: ",listboxField("Dependencia Superior",$sqlDependencia,"nbusc_depe_id",$depeid,"","onChange=\"xajax_subDependencia(1,this.value,'','divSubDependencia')\"","","class=\"my_select_box\""));        

    $form->addHtml("<tr><td colspan=2><div id='divSubDependencia'>\n");
    $form->addHtml(subDependencia(2,$depeid,"",'divSubDependencia'));
    $form->addHtml("</div></td></tr>\n");

//    $form->addField("","<a href=\"#\" onClick=\"javascript:proceder( 'rptUsuarios.php?destino=1' )\" ><img src='../../img/pdf.png' border='0' title='Listar en PDF'></a>");

    $form->addField("","<a href=\"#\" onClick=\"javascript:proceder( 'rptUsuarios.php?destino=1' )\" ><img src='../../img/pdf.png'  border='0' title='Imprimir en PDF'></a>
                        &nbsp;&nbsp;
                        <a href=\"#\" onClick=\"javascript:proceder( 'rptUsuarios.php?destino=2' )\" ><img src='../../img/xls.png'  border='0' title='Exportar a XLS'></a>");
    
    $contenido_respuesta=$form->writeHTML();
    $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);   
    $objResponse->addScript("$('.my_select_box').select2({
                            placeholder: 'Seleccione un elemento de la lista',
                            allowClear: true,
                            width:'90%'
                            });");
                    
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
<script type="text/javascript" src="../../library/js/libjsgen.js"></script>

<script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>
<link rel="stylesheet" href="../../library/select2/dist/css/select2.css">        
<script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script> 

<link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
<script src="../../library/bootstrap/js/bootstrap.min.js"></script>        
        
<script type="text/javascript" src="../../library/tablesorter/jquery.tablesorter.js"></script>
	
<script language="JavaScript">
		<?php echo $myClass->jsDevolver($nomeCampoForm);?>
		<?php echo $myClass->jsSorter($nomeCampoForm);?>		

		/* funcion que define el foco inicial del formulario */
		function inicializa() {
			document.frm.Sbusc_cadena.focus();
		}
		
		function excluir() {
			regSel=$("#tLista tbody input[type=checkbox]").is(":checked");
			if(regSel){ 
				if (confirm('Desea Eliminar el(los) registro(s) selecionado(s)?')) {
					document.frm.target = "controle";
					document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(2)."&".$param->buildPars(false)?>";
					document.frm.submit();
				}
			}else{
				alert('Seleccione el(los) registro(s) que desea eliminar')
			}
		}

                function beforeImprimir() {
                    xajax_imprimir('msg-myModalImp');
                    $('#myModalImp').modal('show');                    
                }
		
                function proceder(rptPage) {
                        parent.content.document.frmImp.target = "controle";
                        parent.content.document.frmImp.action = rptPage;
                        parent.content.document.frmImp.submit();
                }
                
		var SoloUnCheck=true;	
</script>
<script type="text/javascript" src="<?php echo PATH_INC?>js/jquerytablas3.js"></script>


<!-- Esta l�nea debe ir aqu� para luego de que se aplique el orden se refrescan los css de la tabla -->

		<?php 
                    $xajax->printJavascript(PATH_INC.'ajax/');
                    verif_framework(); ?>

</head>

<body class="contentBODY" onLoad="inicializa()">
<?php 
		pageTitle($myClass->getTitle());
		
		/* botones */
		$button = new Button;
		$button->addItem("Nuevo Usuario",$myClass->getPageEdicion().$param->buildPars(true),"content");
		$button->addItem("Eliminar","javascript:excluir()","content",2);
                $button->addItem("Obligar Cambio de Contraseña","javascript:if(confirm('Todos los Usuarios deberan Cambiar sus contraseñas, esta seguro?')){xajax_obligarCambioContrasena()}","content",2);                
                $button->addItem("Imprimir","javascript:beforeImprimir()","content");
			
		echo $button->writeHTML();
		echo "<BR>";
		
		/* formul�rio de pesquisa */
		$form = new Form("frm");
		$form->setMethod("POST");
		$form->setTarget("content");
		$form->setWidth("100%");
		$form->setLabelWidth("20%");
		$form->setDataWidth("80%");

		$form->addHidden("rodou","s");

                //$cadena=getSession("cadSearch");

                $depeid=getSession("sis_depe_superior");
                /* Instancio la Dependencia */
                $sqlDependencia=new dependenciaSuperior_SQLBox3(getSession("sis_depe_superior"));
                $sqlDependencia=$sqlDependencia->getSQL();        
                //FIN OBTENGO
                
                $form->addField("Dependencia Superior: ",listboxField("Dependencia Superior",$sqlDependencia,"nbusc_depe_id","$depeid","-- Todos --","onChange=\"xajax_subDependencia(1,this.value,'','divSubDependencia')\"","","class=\"my_select_box\""));        

                $form->addHtml("<tr><td colspan=2><div id='divSubDependencia'>\n");
                $form->addHtml(subDependencia(2,$depeid,"",'divSubDependencia'));
                $form->addHtml("</div></td></tr>\n");
                
                $lista_nivel = array("1,ACTIVOS","9,DE BAJA", "10, TODOS "); 
                $nbusc_usua_activo=1;
                $form->addField("Estado:",radioField("Estado",$lista_nivel, "nbusc_usua_activo",$nbusc_usua_activo,"","H"));

                
		//array de parametros que se ingresaran a la funcion de busqueda de ajax
		$paramFunction= new manUrlv1();
		$paramFunction->removeAllPar();
		$paramFunction->addParComplete('colSearch','');
		$paramFunction->addParComplete('busEmpty',$busEmpty);
		$paramFunction->addParComplete('numForm',0);
		$paramFunction->addParComplete('pageEdit',$myClass->getPageEdicion());
		
		$array=$myClass->getArrayNameVar();
		foreach($array as $k => $v) {$paramFunction->addParComplete($k,$v);}
		
		
		$form->addField("Pers/Usua/Depen: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",$cadena ,50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'divResultado')\" value=\"Buscar\">");

		$form->addHtml("<tr><td colspan=2><div id='divResultado'>\n");

		/* Creo array $formData con valores necesarios para filtrar la tabla */
		$formData['Sbusc_cadena']=$cadena ;
                $formData['nbusc_depe_id']=$depeid;
                $formData['nbusc_usua_activo']=$nbusc_usua_activo;

		$form->addHtml($myClass->buscar(2,$formData,encodeArray($paramFunction->getUrl()),$pg,'divResultado'));

		$form->addHtml("</div></td></tr>\n");
		echo  $form->writeHTML();
?>
<div id="myModalImp" class="modal fade">
        <div class="modal-dialog modal-mg">
            <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><span id="title-myModalImp" class="" aria-hidden="true">&nbsp;</span></h4>                    
                    </div>
                    <div id="msg-myModalImp" class="modal-body">
                        <p>Loading...</p>
                    </div>

            </div>
        </div>    
</div>    

</body>
</html>

    <script>
        $(".my_select_box").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true,
            width:'90%'
            });
    </script>

<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();