<?php
/* formulario de ingreso y modificacion */
include("../../library/library.php");

/* verificacion del nivel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("catalogosProcedimientos_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();


/* Recibo parametros */
$relacionamento_id = getParam("relacionamento_id"); /* Recibo el dato de ralcionamiento entre la tabla padre e hijo */
$clear = getParam("clear");//SI CLEAR=1, se comporta como una pagina normal, CLEAR=2 se llama en una ventana emergente

/* Recibo los parametros con la clase de "paso de parametros por la URL"  */
$param= new manUrlv1();
$param->removePar('clear');

/* Instancio mi clase base */
$myClass = new procedimiento($relacionamento_id,'Requisitos del Procedimiento');
$myClass->setDatos();
$tabl_tipo_componente=$myClass->field("tabl_tipo_componente");
$tabl_subtipo_componente= $myClass->field("tabl_subtipo_componente");
// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("elementos");
$xajax->registerFunction("agregarElemento");
$xajax->registerFunction("eliminaElemento");
$xajax->registerFunction("modiElemento");


function elementos($op,$id)
{
	global $conn;
	$objResponse = new xajaxResponse();
        if($id){
            $elementos = new procedimientoRequisitos_SQLlista();
            $elementos->wherePadreID($id);
            $elementos->orderUno();
            $sqlElemntos = $elementos->getSQL();
            
            $rs = new query($conn, $sqlElemntos);
            
            $otable = new  Table("","100%",7); 	
            $otable->addColumnHeader("Orden",false,"5%", "C");            
            $otable->addColumnHeader("Descripci&oacute;n",false,"85%", "C"); 
            //$otable->addColumnHeader("Solicita",false,"20%", "C"); 
            //$otable->addColumnHeader("Obligatorio",false,"5", "C","","Negrita"); 
            $otable->addColumnHeader("Usuario",false,"5%", "C");
            $otable->addColumnHeader("Opci&oacute;n",false,"5%", "C");            
            
            $otable->addRow();
            while ($rs->getrow()) {
                    $hijo_id=$rs->field("prre_id");
                    $orden=$rs->field("prre_orden");                    
                    $descripcion=$rs->field("prre_descripcion");
                    $prre_objeto=$rs->field("prre_objeto");
                    $objeto=$rs->field("objeto");
                    $prre_obligatorio=$rs->field("prre_obligatorio");
                    $obligatorio=$rs->field("obligatorio");                    
                    $usuario=$rs->field("usuario");
                    
                    $otable->addData("<div id=\"orden_".$hijo_id."\">".                    
                                        $orden.
                                        "</div>","C");
                    $otable->addData("<div id=\"descripcion_".$hijo_id."\">".                    
                                        textAreaField("Requisitos","xxxdescripcion","$descripcion",4,100,0,"READONLY",0).
                                        "</div>","L");
//                    $otable->addData("<div id=\"objeto_".$hijo_id."\">".                    
//                                        $objeto.
//                                        "</div>","L");
//                    $otable->addData("<div id=\"obligatorio_".$hijo_id."\">".                    
//                                        $obligatorio.
//                                        "</div>","C");
                    $otable->addData($usuario,"C");
                    
                    if(getSession("sis_userid")==$rs->field("usua_id") or getSession("sis_userid")==1){//solo permite eliminar el registro al usuario que ha editado el registro		
                        $otable->addData(
                                    "<div style='float: left' id=\"img_".$hijo_id."\"><a class=\"link\" href=\"#\" onClick=\"xajax_modiElemento(1,'$hijo_id','$id','$orden','','$prre_objeto','$prre_obligatorio')\"><img src=\"../../img/editar.gif\" border=0 align=absmiddle hspace=1 alt=\"Editar\"></a></div>".
                                    "<div style='float: left' id=\"eli_".$hijo_id."\"><a class=\"link\" href=\"#\" onClick=\"if(confirm('Seguro de Eliminar Item?')){xajax_eliminaElemento($id,$hijo_id)}\"><img src=\"../../img/delete.gif\" border=0 align=absmiddle hspace=1 alt=\"Eliminar\"></a></div>"
                                    ,'C');	

                    }else{
                        $otable->addData("&nbsp;");
                    }
                    
                    $otable->addRow();
            }

            $otable->addData(numField("Orden","nr_orden",($orden+1),3,2,0),"C");            
            $otable->addData(textAreaField("Requisitos","ex_descripcion",'',4,100,10000000));
//            $otable->addData(textField("Descripcion","Sr_descripcion",'',80,120),"L");
//            $sqlFormato = array(1 => "Texto",
//                                2 => "Archivo",
//                                3 => "Check de Confirmaci&oacute;n",
//                                4 => "Fecha",
//                                9 => "Ninguno");
//            $otable->addData(listboxField("Solicita", $sqlFormato, "nr_objeto", 1));            
//            $otable->addData(checkboxField("Obligatorio","hx_obligatorio",1,1),"C");
            $otable->addData("");
            $button = new Button;
            $button->setDiv(false);
            $button->addItem("Agregar",
                        "javascript:if(ObligaCampos(frm)){ocultarObj('Agregar',7); xajax_agregarElemento($id,document.frm.nr_orden.value,document.frm.ex_descripcion.value,0,0)}",
                         "",2,0,"botonAgg","button");
            $otable->addData($button->writeHTML());
            $otable->addRow();
            
            $contenido_respuesta=$otable->writeHTML();
	}
	else{
            $contenido_respuesta="";
        }
	
        // Se devuelve el objeto, que este dara todas las instruccione JS para realizar la tarea
	if($op==1){
                $objResponse->addAssign("divElementos",'innerHTML', $contenido_respuesta);
                $objResponse->addScript("$('.my_select_box').select2({
                                            placeholder: 'Seleccione un elemento de la lista',
                                            allowClear: true
                                        })");
		return $objResponse;
	}else{
		return $contenido_respuesta	;
	}		

}

function agregarElemento($id,$orden,$descripcion,$objeto,$obligatorio)
{
    global $conn;	
        
    $objResponse = new xajaxResponse();
    
    $orden=$orden>0?$orden:0;    
    
    $descripcion=$descripcion!=''?$descripcion:'';        

    $obligatorio=$obligatorio=='true'?1:0;
    
    $sql="INSERT INTO gestdoc.procedimiento_requisitos (   proc_id,
                                                           prre_orden,  
                                                           prre_descripcion,
                                                           prre_objeto,
                                                           prre_obligatorio,
                                                           usua_id)
                                            VALUES($id,                                                
                                                   $orden,
                                                   $$$descripcion$$,
                                                   $objeto,
                                                   $obligatorio,".
                                                   getSession("sis_userid").") "
            . " RETURNING prre_id ";

    $conn->execute($sql);
    $error=$conn->error();
    if($error){
       $objResponse->addAlert($error);
       return $objResponse;
    }else{
        $objResponse->addScript("xajax_elementos(1,'$id')");
    }
                
    return $objResponse;
}

function eliminaElemento($id,$prru_id)
{
    global $conn;	
        
    $objResponse = new xajaxResponse();
         
    $sql="DELETE FROM gestdoc.procedimiento_requisitos
            WHERE prre_id=$prru_id";

    $conn->execute($sql);
    $error=$conn->error();
    if($error){
       $objResponse->addAlert($error);
       return $objResponse;
    }else{
        $objResponse->addScript("xajax_elementos(1,'$id')");        
                    
    }                
    return $objResponse;
}

function modiElemento($op,$prre_id,$id,$orden,$descripcion,$objeto,$obligatorio)
{
	global $conn;	

        $objResponse = new xajaxResponse();

	if($op==1){ // Edita
                $elementos = new procedimientoRequisitos_SQLlista();
                $elementos->whereID($prre_id);
                $elementos->setDatos();
                $descripcion=$elementos->field('prre_descripcion');
                        
		$contenido1=numField("Orden","nr_ordenx",$orden,3,2,0);
                $contenido2=textAreaField("Requisitos","ex_descripcionx","$descripcion",4,100,10000000);
                //$contenido2=textField("Descripcion","Sr_descripcionx","$descripcion",80,120); 
                
//                $sqlFormato = array(1 => "Texto",
//                                2 => "Archivo",
//                                3 => "Check de Confirmaci&oacute;n",
//                                4 => "Fecha",
//                                9 => "Ninguno");
//                $contenido3=listboxField("Solicita", $sqlFormato, "nr_objetox", $objeto); 
//                $contenido4=checkboxField("Obligatorio","hx_obligatoriox",$obligatorio,$obligatorio);
            
                $contenido5="<a class=\"link\" href=\"#\" onClick=\"xajax_modiElemento(2,'$prre_id','$id',document.frm.nr_ordenx.value,document.frm.ex_descripcionx.value,'','')\"><img src=\"../../img/guardar.gif\" border=0 align=absmiddle hspace=1 alt=\"Guardar\">";
                $contenido6="<a class=\"link\" href=\"#\" onClick=\"xajax_elementos(1,'$id')\"><img src=\"../../img/regresar.bmp\" border=0 align=absmiddle hspace=1 alt=\"Refrescar\">";
       
		$Div1='orden_'.$prre_id;
                $Div2='descripcion_'.$prre_id;
                $Div3='objeto_'.$prre_id;
                $Div4='obligatorio_'.$prre_id;
                $Div5='img_'.$prre_id;
                $Div6='eli_'.$prre_id;
                
		$objResponse->addAssign($Div1,"innerHTML",$contenido1);
                $objResponse->addAssign($Div2,"innerHTML",$contenido2);
                $objResponse->addAssign($Div3,"innerHTML",$contenido3);
                $objResponse->addAssign($Div4,"innerHTML",$contenido4);
                $objResponse->addAssign($Div5,"innerHTML",$contenido5);
                $objResponse->addAssign($Div6,"innerHTML",$contenido6);

                $objResponse->addScript("document.frm.nr_ordenx.focus()");
                $objResponse->addScript("$('.my_select_box').select2({
                                            placeholder: 'Seleccione un elemento de la lista',
                                            allowClear: true
                                        })");                
		return $objResponse;
		
	}else{ // Guardar
        
                $orden=$orden>0?$orden:0;    
                $descripcion=$descripcion?$descripcion:'';        
                $obligatorio=$obligatorio=='true'?1:0;            
                
                $sql="UPDATE gestdoc.procedimiento_requisitos
                                           SET      prre_orden=$orden,
                                                    prre_descripcion=$$$descripcion$$,
                                                    /*prre_objeto=$objeto,
                                                    prre_obligatorio=$obligatorio, */
                                                    prre_actualfecha=NOW(),
                                                    prre_actualusua=".getSession("sis_userid")."    
                                            WHERE prre_id=$prre_id";
                $conn->execute($sql);
                $error=$conn->error();
                if($error){
                   $objResponse->addAlert($error);
                   return $objResponse;
        	}else{
                    $objResponse->addScript("xajax_elementos(1,'$id')");
                    return $objResponse;
                }
	}
}


$xajax->processRequests();

// fin para Ajax
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="../../library/js/focus.js"></script>	
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>	
        
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>   
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>        
        <script language="JavaScript" src="../../library/js/textcounter.js"></script>	
	<script language="JavaScript">
            function salvar(direc,idObj,objFrm) {//pide obliga campos
                if (ObligaCampos(objFrm)){
                        ocultarObj(idObj,10)
                        objFrm.target = "controle";
                        objFrm.action = direc;
                        objFrm.submit();
                }
           }

           function mivalidacion(frm) {  
                var sError="Mensajes del sistema: "+"\n\n"; 	
                var nErrTot=0; 	 
		
                if (nErrTot>0){ 		
			alert(sError)
			eval(foco)			
			return false
		}else
			return true			

	}
		

	</script>
    <?php $xajax->printJavascript(PATH_INC.'ajax/'); ?>
    <?php verif_framework(); ?>

</head>
<body class="contentBODY">
<?php
pageTitle($myClass->getTitle());


/* botones */
$button = new Button;
$button->addItem(" Regresar ","catalogosProcedimientos_buscar.php?clear=1");
echo $button->writeHTML();


/* Control de fichas, */
$abas = new Abas();
$abas->addItem(" PROCEDIMIENTO ",false,"catalogosProcedimientos_edicion.php?id=$relacionamento_id");
//$abas->addItem(" RUTA DEL PROCEDIMIENTO ",false,"catalogosProcedimientos_rutas.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));
$abas->addItem(" REQUISITOS ",true);

echo $abas->writeHTML();

/* formul�rio de pesquisa */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->addHidden("tr_proc_id", $relacionamento_id);

$form->addField("Nombre de Procedimiento: ",$myClass->field("proc_nombre"));
$form->addField("Usuario: ",$myClass->field("username"));

$form->addHtml("<tr><td colspan=2><div id='divElementos'>\n");
$form->addHtml(elementos(2,$relacionamento_id));
$form->addHtml("</div></td></tr>\n");

        
echo  $form->writeHTML();


    
?>
    
    <script>
        $(".my_select_box").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true
        });
    </script>
</body>
</html>
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();