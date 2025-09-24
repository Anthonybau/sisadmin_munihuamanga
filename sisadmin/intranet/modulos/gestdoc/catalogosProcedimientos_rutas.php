<?
/* formulario de ingreso y modificacion */
include("../../library/library.php");

/* verificacion del nivel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("catalogosProcedimientos_class.php");
include("../catalogos/catalogosDependencias_class.php");

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
$myClass = new procedimiento($relacionamento_id,'RUTA DEL EXPEDIENTE');
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
            $elementos = new procedimientoRuta_SQLlista();
            $elementos->wherePadreID($id);
            $elementos->orderUno();
            $sqlElemntos = $elementos->getSQL();
            
            $rs = new query($conn, $sqlElemntos);
            
            $otable = new  Table("","100%",7); 	
            $otable->addColumnHeader("Secuencia",false,"5%", "C");            
            $otable->addColumnHeader("Tipo",false,"10%", "C"); 
            $otable->addColumnHeader("Dependencia",false,"40%", "C"); 
            $otable->addColumnHeader("Plazo(dias)",false,"5", "C","","Negrita"); 
            $otable->addColumnHeader("Acci&oacute;n",false,"30%", "C"); 
            $otable->addColumnHeader("Usuario",false,"5%", "C");
            $otable->addColumnHeader("Opci&oacute;n",false,"5%", "C");            
            $otable->addRow();
            $tt_plazo=0;
            while ($rs->getrow()) {
                    $hijo_id=$rs->field("prru_id");
                    $secuencia=$rs->field("prru_secuencia");
                    $tipo_ruta=$rs->field("tipo_ruta");
                    $depe_id=$rs->field("depe_id");
                    $dependencia=$rs->field("dependencia");                    
                    $prru_plazo=$rs->field("prru_plazo");                    
                    $accion=$rs->field("prru_accion");
                    $usuario=$rs->field("usuario");
                    
                    $otable->addData("<div id=\"secuencia_".$hijo_id."\">".                    
                                        $secuencia.
                                        "</div>","C");
                                        
                    $otable->addData("<div id=\"tipo_ruta_".$hijo_id."\">".                    
                                        $tipo_ruta.
                                        "</div>","C");
                    
                    $otable->addData("<div id=\"dependencia_".$hijo_id."\">".                    
                                        $dependencia.
                                        "</div>","L");
                    $otable->addData("<div id=\"plazo_".$hijo_id."\">".                    
                                        $prru_plazo.
                                        "</div>","C");
                    $otable->addData("<div id=\"accion_".$hijo_id."\">".                    
                                        $accion.
                                        "</div>","L");
                    $otable->addData($usuario,"C");
                    
                    if(getSession("sis_userid")==$rs->field("usua_id") or getSession("sis_userid")==1){//solo permite eliminar el registro al usuario que ha editado el registro		
                        $otable->addData(
                                    "<div style='float: left' id=\"img_".$hijo_id."\"><a class=\"link\" href=\"#\" onClick=\"xajax_modiElemento(1,'$hijo_id','$id','$secuencia','$tipo_ruta','$depe_id','$prru_plazo','$accion')\"><img src=\"../../img/editar.gif\" border=0 align=absmiddle hspace=1 alt=\"Editar\"></a></div>".
                                    "<div style='float: left' id=\"eli_".$hijo_id."\"><a class=\"link\" href=\"#\" onClick=\"if(confirm('Seguro de Eliminar Item?')){xajax_eliminaElemento($id,$hijo_id)}\"><img src=\"../../img/delete.gif\" border=0 align=absmiddle hspace=1 alt=\"Eliminar\"></a></div>"
                                    ,'C');	

                    }else{
                        $otable->addData("&nbsp;");
                    }
                    
                    $tt_plazo=$tt_plazo+$prru_plazo;
                    $otable->addRow();
            }

                $otable->addTotal("&nbsp;");
                $otable->addTotal("&nbsp;");
                $otable->addTotal("&nbsp;");
                $otable->addTotal("$tt_plazo","C");                
                $otable->addTotal("&nbsp;");
                $otable->addTotal("&nbsp;");
                $otable->addTotal("&nbsp;");
                $otable->addTotal("&nbsp;");
                $otable->addData("&nbsp;");
                $otable->addRow();
                    
            
            $otable->addData(numField("Secuencia","nr_secuencia",($secuencia+1),3,2,0),"C");
            
            $sqlFormato = array(1 => "Obligatorio",
                                2 => "Alternativo");
            $otable->addData(listboxField("Tipo", $sqlFormato, "nr_tipo_ruta", 1));
            
            $sqlDependencia=new dependencia_SQLlista();
            $sqlDependencia->whereNotDos();
            $sqlDependencia->orderUno();
            $sqlDependencia=$sqlDependencia->getSQL_cbox();
            $otable->addData(listboxField("Dependencia",$sqlDependencia,"tr_depe_id","","-- Dependencia --","","","class=\"my_select_box\""));        
        
            $otable->addData(numField("Plazo","Sr_plazo","",3,3,0,false),"C");
            $otable->addData(textField("Accion","Sr_accion",'',40,100),"L");
            $otable->addData("");
            $button = new Button;
            $button->setDiv(false);
            $button->addItem("Agregar",
                        "javascript:if(ObligaCampos(frm)){ocultarObj('Agregar',7); xajax_agregarElemento($id,document.frm.nr_secuencia.value,document.frm.nr_tipo_ruta.value,document.frm.tr_depe_id.value,document.frm.Sr_plazo.value,document.frm.Sr_accion.value)}",
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

function agregarElemento($id,$secuencia,$tipo_ruta,$depe_id,$plazo,$accion)
                            
{
    global $conn;	
        
    $objResponse = new xajaxResponse();
    $secuencia=$secuencia>0?$secuencia:0;    
    $plazo=$plazo?$plazo:0;
    $accion=$accion?$accion:'';                
    
    $sql="INSERT INTO gestdoc.procedimiento_ruta ( proc_id,
                                                    prru_secuencia,
                                                    prru_secuencia_tipo,
                                                    depe_id,
                                                    prru_plazo,
                                                    prru_accion,
                                                    usua_id)
                                            VALUES($id,                                                
                                                   $secuencia,
                                                   $tipo_ruta,
                                                   $depe_id,
                                                   $plazo,
                                                   $$$accion$$,".
                                                   getSession("sis_userid").") "
            . " RETURNING prru_id ";

    $conn->execute($sql);
    $error=$conn->error();
    if($error){
       $objResponse->addAlert($error);
       return $objResponse;
    }else{
        //$objResponse->addScript("xajax_muestraCriterios(1,'$cegr_id','$grupo','divCriterios')");
        $objResponse->addScript("xajax_elementos(1,'$id')");
                    
    }
                
    return $objResponse;
}

function eliminaElemento($id,$prru_id)
{
    global $conn;	
        
    $objResponse = new xajaxResponse();
         
    $sql="DELETE FROM gestdoc.procedimiento_ruta 
            WHERE prru_id=$prru_id";

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

function modiElemento($op,$prru_id,$id,$secuencia,$tipo_ruta,$depe_id,$plazo,$accion)
{
	global $conn;	

        $objResponse = new xajaxResponse();

	if($op==1){ // Edita
		$contenido1=numField("Secuencia","nr_secuenciax",$secuencia,3,2,0);
                $sqlFormato = array(1 => "Obligatorio",
                                2 => "Alternativo");
                $contenido2=listboxField("Tipo", $sqlFormato, "nr_tipo_rutax", $tipo_ruta);   
                
                $sqlDependencia=new dependencia_SQLlista();
                $sqlDependencia->whereNotDos();
                $sqlDependencia->orderUno();
                $sqlDependencia=$sqlDependencia->getSQL_cbox();
                $contenido3=listboxField("Dependencia",$sqlDependencia,"tr_depe_idx","$depe_id","-- Dependencia --","","","class=\"my_select_box\"");                
                $contenido4=numField("Plazo","Sr_plazox","$plazo",3,3,0,false);
                $contenido5=textField("Accion","Sr_accionx","$accion",40,100);
            
                $contenido6="<a class=\"link\" href=\"#\" onClick=\"xajax_modiElemento(2,'$prru_id','$id',document.frm.nr_secuenciax.value,document.frm.nr_tipo_rutax.value,document.frm.tr_depe_idx.value,document.frm.Sr_plazox.value,document.frm.Sr_accionx.value)\"><img src=\"../../img/guardar.gif\" border=0 align=absmiddle hspace=1 alt=\"Guardar\">";
                $contenido7="<a class=\"link\" href=\"#\" onClick=\"xajax_elementos(1,'$id')\"><img src=\"../../img/regresar.bmp\" border=0 align=absmiddle hspace=1 alt=\"Refrescar\">";
                
		$Div1='secuencia_'.$prru_id;
                $Div2='tipo_ruta_'.$prru_id;
                $Div3='dependencia_'.$prru_id;
                $Div4='plazo_'.$prru_id;
                $Div5='accion_'.$prru_id;
                $Div6='img_'.$prru_id;
                $Div7='eli_'.$prru_id;
                
		$objResponse->addAssign($Div1,"innerHTML",$contenido1);
                $objResponse->addAssign($Div2,"innerHTML",$contenido2);
                $objResponse->addAssign($Div3,"innerHTML",$contenido3);
                $objResponse->addAssign($Div4,"innerHTML",$contenido4);
                $objResponse->addAssign($Div5,"innerHTML",$contenido5);
                $objResponse->addAssign($Div6,"innerHTML",$contenido6);
                $objResponse->addAssign($Div7,"innerHTML",$contenido7);
                $objResponse->addScript("document.frm.nr_secuenciax.focus()");
                $objResponse->addScript("$('.my_select_box').select2({
                                            placeholder: 'Seleccione un elemento de la lista',
                                            allowClear: true
                                        })");                
		return $objResponse;
		
	}else{ // Guardar
                $secuencia=$secuencia>0?$secuencia:0;
                $plazo=$plazo?$plazo:0;
                $accion=$accion?$accion:'';            
                
                $sql="UPDATE gestdoc.procedimiento_ruta 
                                           SET      prru_secuencia=$secuencia,
                                                    prru_secuencia_tipo=$tipo_ruta,
                                                    depe_id=$depe_id,
                                                    prru_plazo=$plazo,
                                                    prru_accion=$$$accion$$,
                                                    prru_actualfecha=NOW(),
                                                    prru_actualusua=".getSession("sis_userid")."    
                                            WHERE prru_id=$prru_id";
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



/* Control de fichas, */
$abas = new Abas();
$abas->addItem(" PROCEDIMIENTO ",false,"catalogosProcedimientos_edicion.php?id=$relacionamento_id");
$abas->addItem(" RUTA DEL PROCEDIMIENTO ",true);
$abas->addItem(" REQUISITOS ",false,"catalogosProcedimientos_requisitos.php?relacionamento_id=$id&clear=1&".$param->buildPars(false));

echo $abas->writeHTML();
echo "<br>";
/* formul�rio de pesquisa */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->addHidden("tr_proc_id", $relacionamento_id);

/* botones */
$button = new Button;
$button->addItem(" Regresar ","catalogosProcedimiento_buscar.php?clear=1");
$form->addHtml($button->writeHTML());


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