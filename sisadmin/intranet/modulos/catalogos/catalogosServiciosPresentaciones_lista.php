<?php
/* formulario de ingreso y modificacion */
include("../../library/library.php");

/* verificacion del nivel de usuario */
verificaUsuario(1);

include("catalogosServiciosPresentaciones_class.php");
include("catalogosServicios_class.php");

/* establecer conexion con la BD */
$conn = new db();
$conn->open();

$relacionamento_id=getParam("relacionamento_id"); //variable q se recibe desde la opcion "ACTUALIZACION DE ESCALAFON"
$clear=getParam("clear");

$param= new manUrlv1();
$param->removePar('clear');

$myClass = new clsServicioPresentaciones(0,"Presentaciones del Producto");

$padre = new servicios($relacionamento_id,"");
$padre->setDatos();


// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("colocaImporte");
$xajax->registerFunction("listaPresentaciones");
        
function colocaImporte($op,$id){
	$objResponse = new xajaxResponse();
        if($op==1){
            $sqlConceptos=new clsConceptos_SQLlista();
            $sqlConceptos->whereID($id);
            $sqlConceptos->setDatos();
            
            //$objResponse->addScript("document.frmMovimientos.nr_pppr_importe.value='".$sqlConceptos->field('conc_importe')."'");
            if($sqlConceptos->field("tabl_tipoconcepto")==120){//SIN FACTOR
                $contenido_respuesta='<input type=\'hidden\' name=\'___pecp_importe\' value=\'0\'><input type=\'hidden\' name=\'___pecp_porcentaje\' value=\'0\'>';
            }elseif($sqlConceptos->field("tabl_tipoconcepto")==121){//porcentual
                $porcentaje=$sqlConceptos->field('conc_porcentaje');
                $porcentaje=$porcentaje>0?$porcentaje:'';
                $contenido_respuesta='<b>%:</b>'.numField("%","nr_pecp_porcentaje",$porcentaje,10,5,2).'<input type=\'hidden\' name=\'___pecp_importe\' value=\'0\'>';
            }else{
                $importe=$sqlConceptos->field('conc_importe');
                $importe=$importe>0?$importe:'';
                $contenido_respuesta='<b>Imp:</b>'.numField("Importe","nr_pecp_importe",$importe,16,12,2).'<input type=\'hidden\' name=\'___pecp_porcentaje\' value=\'0\'>';
            }
            
            $objResponse->addAssign('divImporte','innerHTML', $contenido_respuesta);
            $objResponse->addScript("xajax_pideClasificador(1,document.frm.tr_tabl_tipoplanilla.value,document.frm.tr_conc_id.value)");
            
            if($sqlConceptos->field("tabl_tipoconcepto")==121){//porcentual
                $objResponse->addScript("document.frm.nr_pecp_porcentaje.focus()");       
            }else{
                $objResponse->addScript("document.frm.nr_pecp_importe.focus()");       
            }
                
        }
        return $objResponse;
}

function listaPresentaciones($op,$nbusc_sepr_activo){
    global $conn,$relacionamento_id;
    $objResponse = new xajaxResponse();
   
    $presentaciones=new clsServicioPresentaciones_SQLlista();
    $presentaciones->wherePadreID($relacionamento_id);
    if($nbusc_sepr_activo==1){
        $presentaciones->whereActivo();
    }elseif($nbusc_sepr_activo==9){
        $presentaciones->whereNOActivo();
    }    
    $presentaciones->orderUno();
    $sql=$presentaciones->getSQL();
    //echo $sql;
    $rs = new query($conn, $sql);

    /* inicializo tabla */
    $table = new Table("","100%",8); 
    if ($rs->numrows()>0) {
        /* construccion de cabezera de tabla */
        $table->addColumnHeader("<input type=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">");
        $table->addColumnHeader("C&oacute;d",false,"5%");
        $table->addColumnHeader("C.Barras",false,"10%");
        
        if( SIS_EMPRESA_TIPO!=4 ) {//ALMACENES
            $table->addColumnHeader("Presentaci&oacute;n",false,"44%");        
            $table->addColumnHeader("U.Medida",false,"10%");
            $table->addColumnHeader("Equivalencia",false,"5%");    
            $table->addColumnHeader("Tipo Precio",false,"15%");
            $table->addColumnHeader("Precio",false,"10%");
        }else{
            $table->addColumnHeader("Presentaci&oacute;n",false,"59%");        
            $table->addColumnHeader("U.Medida",false,"20%");
            $table->addColumnHeader("Equivalencia",false,"5%");    
        }
        
        $table->addColumnHeader("Ori",false,"1%","C","","Origen");
        $table->addColumnHeader("Usuario",false,"10%");

        $table->addRow();        
        while ($rs->getrow()) {
                $id=$rs->field("sepr_id");
                /* adiciona columnas */
                $table->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");
                if ($rs->field("sepr_origen")==0){//si es el registro origen
                     $table->addData($id);
                }else{
                    $table->addData(addLink($id,PATH_INC."auxiliar.php?pag=../../modulos/catalogos/catalogosServiciosPresentaciones_edicion.php?id=$id,id_relacion=$relacionamento_id","Click aqu&iacute; para consultar o editar este registro","content","ls-modal"));	
                }
                $table->addData($rs->field("sepr_codigo_barras"));	
                $table->addData($rs->field("sepr_umedida"));
                $table->addData($rs->field("umedida"));
                $table->addData($rs->field("sepr_equi_unidades"),"C");
                
                if( SIS_EMPRESA_TIPO!=4 ) {//ALMACENES
                    $table->addData($rs->field("tipo_precio"));
                    $table->addData($rs->field("sepr_precio"),"R");
                }
                
                $table->addData($rs->field("sepr_origen"),"C");
                $table->addData($rs->field("username"));

                if($rs->field("sepr_estado")==9){
                    $table->addRow('ANULADO');
                }else{
                    $table->addRow(); // adiciona linea
                }
      }
    }else{
        $table->addColumnHeader("NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); 
        $table->addRowHead(); 	
	$table->addRow();	
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
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.2.min.js"></script>   
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                
	
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap4/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap4/bootstrap-theme.min.css">
        <script src="../../library/bootstrap4/bootstrap.min.js"></script>
        
        <!-- para el modal -->
        <style>
        .modal-dialog,
        .modal-content {
            /* 80% of window height */
            height: 90%;
        }

        .modal-body {
            /* 100% = dialog height, 120px = header + footer */
            max-height: 100%;
        }    
        </style>
            <script language="JavaScript">
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
                    if (confirm('Activar/Desactivar registros seleccionados?')) {
                        parent.content.document.frm.target = "controle";
                        parent.content.document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(3)."&".$param->buildPars(false)?>";
                        parent.content.document.frm.submit();
                    }
            }

            function refrescar() {
                parent.content.location.reload();
            }
        
	</script>
        <?php 
            $xajax->printJavascript(PATH_INC.'ajax/');
            verif_framework(); 
         ?>
</head>
<body class="contentBODY" >
<?php
pageTitle($myClass->getTitle());

/* Botones de accion */
$button = new Button;
$button->addItem("Agregar Presentaci&oacute;n",PATH_INC."auxiliar.php?pag=../../modulos/catalogos/catalogosServiciosPresentaciones_edicion.php?id_relacion=$relacionamento_id","content",2,0,"ls-modal botao");	
$button->addItem("Activar/Desactivar","javascript:activar()","content",2);
$button->addItem("Eliminar","javascript:excluir()","content",2);
echo $button->writeHTML();


/* Control de fichas, */
$abas = new Abas();
$abas->addItem("General",false,"catalogosServicios_edicion.php?id=$relacionamento_id&clear=1&".$param->buildPars(false));
$abas->addItem("Presentaciones",true);        

if(inlist($padre->field('segr_vinculo'),"5")){ //
    $abas->addItem("Imagenes",false,"catalogosServicios_imagenes.php?relacionamento_id=$relacionamento_id&clear=1&".$param->buildPars(false));
}

$abas->addItem("Vinculados",false,"catalogosServiciosVinculados_lista.php?relacionamento_id=$relacionamento_id&clear=1&".$param->buildPars(false));    

if(SIS_SISCONT==1){
    $abas->addItem("Asientos Contables",false,"catalogosServiciosCuentasContables_lista.php?relacionamento_id=$relacionamento_id&clear=1&".$param->buildPars(false));
}
$abas->addItem("Movimientos",false,"catalogosServicios_movimientos.php?relacionamento_id=$relacionamento_id&clear=1&".$param->buildPars(false));
echo $abas->writeHTML();

//muestra los datos del concepto
$myConcepto = new servicios_SQLlista();
$myConcepto->whereID($relacionamento_id);
$myConcepto->setDatos();
$odatos = new AddTableForm();
$odatos->setLabelWidth("20%");
$odatos->setDataWidth("80%");

$odatos->addField("C&oacute;digo: ",$myConcepto->field("serv_id"));
$odatos->addField("Descripci&oacute;n: ",$myConcepto->field("serv_descripcion"));
$odatos->addField("Grupoa: ",$myConcepto->field("segr_id")." ".$myConcepto->field("grupo"));

$lista_nivel = array("1,ACTIVOS","9,INACTIVOS", "10, TODOS "); 
$nbusc_sepr_activo=1;
$odatos->addField("Estado:",radioField("Estado",$lista_nivel, "nbusc_sepr_activo",$nbusc_sepr_activo,"onChange=\"javascript:xajax_listaPresentaciones(1,this.value)\"","H"));

echo $odatos->writeHTML();			


?>

<!-- Lista -->
<div align="center">
<form name="frm" method="post" target='content'>
<input type="hidden" name="refresh" value=0>
<div id="divConceptos">
<?php
    echo listaPresentaciones(2,$nbusc_sepr_activo);
?>
</div>

</form>
</div>
</body>

<script>
    $(".my_select_box").select2({
    placeholder: "Seleccione un elemento de la lista",
    allowClear: true,
    width: '100%' 
    });
</script>

<div id="myModal" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p></p>
      </div>
    </div>
  </div>
</div>

</html>
<?php
$conn->close();