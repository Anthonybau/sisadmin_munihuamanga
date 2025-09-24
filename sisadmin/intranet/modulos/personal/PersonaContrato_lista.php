<?php
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificacion del nivel de usuario */
verificaUsuario(1);

include("PersonaContrato_class.php");
include("Persona_class.php");


/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

$id_relacion=getParam("id_relacion"); //variable q se recibe desde la opcion "ACTUALIZACION DE ESCALAFON"
$nbusc_char=getParam("nbusc_char");
$clear=getParam("clear");
$busEmpty=1;
$pg=1;

$param= new manUrlv1();
$param->removePar('clear');

$myClass = new clsPersonaContrato(0,"Altas y Bajas");


// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("generaSecuencia");
$xajax->registerFunction("eliminaSecuencia");
$xajax->registerFunction("listaContratos");
$xajax->registerExternalFunction(array("buscar", "clsPersonaContrato","buscar"),"");

function generaSecuencia($id){    
        global $conn;
	$objResponse = new xajaxResponse();

//        $personaContrato=new clsPersonaContrato_SQLlista();
//        $personaContrato->whereID($id);
//        $personaContrato->setDatos();
//
//
//       	$secuencia=trim('personal.corr_contrato_'.$personaContrato->field('peco_periodo').'_'.$personaContrato->field('tabl_tipdoc'));
//
//        $correla=$conn->currval($secuencia);        
//        /*CREA LA SECUENCIA*/
//	if($correla==0){
//                $sql="CREATE SEQUENCE $secuencia";
//                $conn->execute($sql);            
//                $correla=1;	
//        }
//        $siglas=getDbValue("SELECT empr_contrato_siglas FROM admin.empresa WHERE empr_id=1");
//        $peco_tipo=$personaContrato->field('tipo_documento_breve');

//        $sql="UPDATE personal.persona_contrato
//                     SET peco_tipo='$peco_tipo',
//                         peco_numero=$correla,
//                         peco_siglas='$siglas', 
//                         peco_documento=LPAD($correla::TEXT,4,'0')||'-'||peco_periodo::TEXT||'-'||'$siglas'::TEXT
//                            WHERE peco_id=$id";
        
        $sql="UPDATE personal.persona_contrato
                     SET peco_numero=NULL
                            WHERE peco_id=$id";
        
        //echo $DivId;
        $conn->execute($sql);
        $error=$conn->error();
        if($error){
            $objResponse->addAlert($error);
        }else{
            //$conn->setval($secuencia,intval($correla)+1); /* se suma 1 a la secuencia del documento */		
            $objResponse->addScript("parent.content.location.reload()");
        }
        return $objResponse;
}

function eliminaSecuencia($id){    
        global $conn;
	$objResponse = new xajaxResponse();

        $personaContrato=new clsPersonaContrato_SQLlista();
        $personaContrato->whereID($id);
        $personaContrato->setDatos();

       	$secuencia=trim('personal.corr_contrato_'.$personaContrato->field('peco_periodo').'_'.$personaContrato->field('tabl_tipdoc'));
        $correla=$personaContrato->field('peco_numero');

        $conn->setval($secuencia,intval($correla)); /* se suma deja la secuencia con el numero eliminado */		
        
        $sql="UPDATE personal.persona_contrato
                     SET peco_numero=0,
                         peco_siglas=NULL,
                         peco_documento=NULL
                            WHERE peco_id=$id";
        
        
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

function listaContratos($op){
    global $conn,$id_relacion;
    $objResponse = new xajaxResponse();
    
    $perfilPago=new clsPersonaContrato_SQLlista();
    $perfilPago->wherePadreID($id_relacion);
    $perfilPago->orderUno();
    $sql=$perfilPago->getSQL();
    //echo $sql;
    $rs = new query($conn, $sql);

    /* inicializo tabla */
    $table = new Table("","100%",8); 

    /* construccion de cabezera de tabla */
    $table->addColumnHeader("<input type=\"checkbox\" name=\"checkall\"  onclick=\"checkform(frm,this)\">");
    $table->addColumnHeader("C&oacute;d",false,"2%");
    $table->addColumnHeader("Fecha",false,"5%");
    $table->addColumnHeader("N&uacute;mero",false,"15%");
    $table->addColumnHeader("Plantilla",false,"15%");
    $table->addColumnHeader("Dependencia",false,"15%");
    $table->addColumnHeader("Cargo",false,"10%");
    $table->addColumnHeader("Sit.Laboral",false,"8%");
    $table->addColumnHeader("Periodo",false,"2%");
    $table->addColumnHeader("Desde",false,"1%");
    $table->addColumnHeader("Hasta",false,"1%");
    $table->addColumnHeader("Monto",false,"5%");
    $table->addColumnHeader("Mov",false,"1%","","","Movimiento");    
    $table->addColumnHeader("Est",false,"2%");
    $table->addColumnHeader("Actualizado",false,"15%");    
    $table->addColumnHeader("",false,"3%");
    $table->addRow();
    $ingresos=0;
    $descuentos=0;
    while ($rs->getrow()) {
            $id=$rs->field("peco_id");
            $pers_id=$rs->field("pers_id");
            $usua_id=$rs->field("usua_id");
            $documento=$rs->field("peco_documento");
            $peco_contractual=$rs->field("peco_contractual");
            $numero_contrato=$rs->field("numero_contrato");
            /* adiciona columnas */
            $table->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");                
            
            //sif(!$numero_contrato){
                $table->addData(addLink($rs->field("pecoid"),PATH_INC."auxiliar.php?pag=../../modulos/personal/PersonaContrato_edicion.php?id=$id,id_relacion=$id_relacion","Click aqu&iacute; para consultar o editar este registro","content","ls-modal"));	
            //}else{
              //  $table->addData($rs->field("pecoid"));	
            //}
            
            $table->addData(dtos($rs->field("peco_fcontrato")));	
            
            if($numero_contrato){
                $table->addData(addLink($documento,"javascript:imprimir('$id')","Click aqu&iacute; para Imprimir Contrato","content",""));	                
            }else{
                $table->addData($documento);	
            }
            
            $table->addData($rs->field("plantilla"));	
            $table->addData($rs->field("dependencia"));	
            if($rs->field("peco_cargofuncional")){
                $table->addData($rs->field("peco_cargofuncional"));	
            }else{
                $table->addData($rs->field("cargo_clasificado"));	
            }
            
            $table->addData($rs->field("sit_laboral"));
            
            $table->addData($rs->field("peco_periodo"));
            $table->addData(dtos($rs->field("peco_finicio")));
            $table->addData(dtos($rs->field("peco_ftermino")));
            $table->addData(number_format($rs->field("peco_monto"),2,'.',','),"R");                
            $table->addData(substr($rs->field("movimiento"),0,3),"R","","",$rs->field("movimiento"));
            $table->addData(substr($rs->field("estado"),0,3));
            $table->addData($rs->field("usernameactual").'/'.$rs->field("peco_actualfecha"));
            
            if($rs->field("peco_estado")==9){
                $table->addData('&nbsp;');                
                $table->addRow('ANULADO'); // adiciona linea
            }else{
                 
                    $botones="<div class=\"dropdown\">
                                <button class=\"btn btn-default dropdown-toggle btn-sm\" type=\"button\" id=\"dropdownMenu1\" data-toggle=\"dropdown\"><span class=\"glyphicon glyphicon-cog\" aria-hidden=\"true\"></span> Opciones
                                <span class=\"caret\"></span></button>
                                <ul class=\"dropdown-menu dropdown-menu-right\" aria-labelledby=\"dropdownMenu1\">";
                    if(inlist($peco_contractual,'1,2')){//contractual desde plantilla
                    //if($usua_id==getSession('sis_userid')){
                            if(!$numero_contrato){
                                $botones.="<li><a href=\"#\" onClick=\"javascript:xajax_generaSecuencia('$id')\" target=\"controle\">".iif($peco_contractual,'==','1',"Generar Contrato","Generar Adenda")."</a></li>";
                            }else{
                                //$botones.="<li><a href=\"#\" onClick=\"javascript:imprimir('$id')\" target=\"controle\">Imprimir</a></li>";
                                $botones.="<li><a href=\"PersonaContrato_edicionContrato.php?id=$id&id_relacion=$id_relacion\"  target=\"content\">Editar</a></li>";
                                $botones.="<li><a href=\"#\" onClick=\"javascript:xajax_eliminaSecuencia('$id')\" target=\"controle\">".iif($peco_contractual,'==','1',"Eliminar Contrato","Eliminar Adenda")."</a></li>";
                            }
                    }
                            $botones.="<li><a href=\"#\" onClick=\"javascript:imprimir2('$pers_id')\" target=\"controle\">Imprimir Historial Laboral</a></li>";
                    //}

                    $botones.="</ul>
                              </div>";
                    $table->addData($botones);                
                //}else{
                //    $table->addData("&nbsp;");
                //}
                $table->addRow(); // adiciona linea                
            }

    }
    $contenido_respuesta=$table->writeHTML();
    //echo "<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total Registros: ".$rs->numrows()."</div>";            

    if($op==1){
        $objResponse->addAssign('divConceptos','innerHTML', $contenido_respuesta);
        $objResponse->addScript("$(document).ready(function() {
                                    $('.ls-modal').on('click', function(e){
                                        e.preventDefault();
                                        $('#myModal').modal('show').find('.modal-body').load($(this).attr('href'));
                                    }); 
                                  });");
        return $objResponse;
    }else{
        return $contenido_respuesta	;
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
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>	
	<script language="javascript" src="<?php echo PATH_INC?>js/checkall.js"></script>        
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>   
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                
	
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>
        
	<script language="JavaScript">
            
        function inicializa() {
            document.frm.Sbusc_cadena.focus();
        }            
	/*
		funcion que llama a la rutina d exclusion de registros, incluye el nombre de la p�gina a ser llamada
	*/
	function excluir(id) {
        	if (confirm('Eliminar registros seleccionados?')) {
                parent.content.document.frm.target = "controle";
		parent.content.document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(2)."&".$param->buildPars(false)?>";
		parent.content.document.frm.submit();
		}
	}


        $(document).ready(function() {
            $('.ls-modal').on('click', function(e){
                e.preventDefault();
                $('#myModal').modal('show').find('.modal-body').load($(this).attr('href'));
            }); 
        });            

        window.cerrar = function(){
            $('#myModal').modal('toggle');
        }; 

	function salvar() {
		if (ObligaCampos(frm)){
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
			parent.content.document.frm.submit();
		}
	}

	function refrescar() {
            parent.content.location.reload();
	}

	function mivalidacion(frm) {  
            var sError="Mensajes del sistema: "+"\n\n"; 	
            var nErrTot=0; 	 

		if (nErrTot>0){ 		
			alert(sError)
			return false
		}else
			return true			
	}
        
	function activar() {
		if (confirm('Activar/Anular registros seleccionados?')) {
                    parent.content.document.frm.target = "content";
                    parent.content.document.frm.action = "<?php $myClass->getNameFile()."?control=".base64_encode(3)."&".$param->buildPars(false)?>";
                    parent.content.document.frm.submit();
		}
	}
        
        function AbreVentana(sURL){
            var w=720, h=650;
            venrepo=window.open(sURL,'rptSalida', "status=yes,resizable=yes,toolbar=no,scrollbars=yes,top=50,left=150,width=" + w + ",height=" + h, 1 );
            venrepo.focus();
        }

        
        function imprimir(id) {
            AbreVentana('rptContrato.php?id=' + id);
	}        
        function imprimir2(id) {
            AbreVentana('rptHistorialLaboral.php?_titulo=HISTORIAL LABORAL POR TRABAJADOR&nx_pers_id=' + id);
	}   
	</script>
        <script type="text/javascript" src="../../library/js/jquerytablas3.js"></script>
        <?php 
        $xajax->printJavascript(PATH_INC.'ajax/');   
	verif_framework(); ?>		
</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle($myClass->getTitle());

/* Botones de accion */
$button = new Button;
$button->addItem("Agregar Registro",PATH_INC."auxiliar.php?pag=../../modulos/personal/PersonaContrato_edicion.php?id_relacion=$id_relacion","content",2,0,"ls-modal botao");	
//$button->addItem("Eliminar","javascript:excluir()","content",2);
$button->addItem("Anular/Activar","javascript:activar()","content",2);
$button->addItem("Refrescar","javascript:refrescar()","content",2);

$button->addItem("Imprimir","javascript:AbreVentana('rptHistorialLaboral.php?nx_pers_id=$id_relacion&_titulo=HISTORIAL LABORAL POR TRABAJADOR')","content");    

$button->addItem("Ir a Lista de Personas","Persona_buscar.php".$param->buildPars(true),"content");

//echo $button->writeHTML();

$botones=btnMenuEscalafon('Opciones',$id_relacion,$param);
  
echo "<table width='100%' colspan=0><tr><td width='80%'>".$button->writeHTML()."</td><td width='20%' align=right>".$botones."</td></table>";        

/* formulario */
$form = new Form("frm");
$form->setMethod("POST");
$form->setTarget("content");
$form->setWidth("100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("rodou","s");
$myPersona = new clsPersona($id_relacion);
$myPersona->setDatos();
$form->addField("Personal: ",$myPersona->field("empleado").' / '.$myPersona->field("pers_dni"));
$form->addField("Condici&oacute;n Laboral: ",$myPersona->field("sit_laboral_larga"));   
$form->addField("Documento: ",$myPersona->field("pers_documento"));
//array de parametros que se ingresaran a la funcion de busqueda de ajax
$paramFunction= new manUrlv1();
$paramFunction->removeAllPar();
$paramFunction->addParComplete('colSearch','');
$paramFunction->addParComplete('clear',$clear);
$paramFunction->addParComplete('busEmpty',$busEmpty);
$paramFunction->addParComplete('numForm',0);
$paramFunction->addParComplete('pageEdit',$myClass->getPageEdicion());

$array=$myClass->getArrayNameVar();
foreach($array as $k => $v) {$paramFunction->addParComplete($k,$v);}

$lista_nivel = array("1,ACTIVO","9,ANULADO"); // definicion de la lista para campo radio
$form->addField("Estado: ",radioField("Estado",$lista_nivel, "Sbusc_estado",1,"","H"));        
$form->addField("Concepto/Descripci&oacute;n: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",$cadena ,50,50)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."',1,'DivResultado')\" value=\"Buscar\">");

$form->addHtml("<tr><td colspan=2><div id='DivResultado'>\n");

/* Creo array $formData con valores necesarios para filtrar la tabla */
$formData['Sbusc_cadena']=$cadena ;
$formData['Sbusc_estado']=1 ;
$form->addHtml($myClass->buscar(2,$formData,encodeArray($paramFunction->getUrl()),$pg,'DivResultado'));

$form->addHtml("</div></td></tr>\n");
echo  $form->writeHTML();
?>
        
    <div id="myModal" class="modal fade">   
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close"  data-dismiss="modal" aria-hidden="true">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>Loading...</p>
                    </div>

            </div>
        </div>    
    </div>
</body>
</html>
<script>
        $("select").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true,
            width: '30%'
            });
</script>    
<?php
/* cierra la conexion a la BD, no debe ser alterado */
$conn->close();