<?php
include("../../library/library.php");
include("PlantillaDespacho_class.php"); 
include("../catalogos/catalogosDependencias_class.php");
include("../catalogos/catalogosTabla_class.php");
include("../catalogos/catalogosTipoExpediente_class.php");
/*
	verifica nivel de usuario
*/
verificaUsuario(1);

/*
	establecer conexion con la BD
*/
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

/*
	tratamiento de campos
*/
$id = getParam("id"); // captura la variable que viene del objeto lista
$depe_id=getParam("nbusc_depe_id");

$myClass = new clsPlantillaDespacho($id,"Edici&oacute;n de Plantilla de Documento");

if (strlen($id)>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
		$bd_plde_id=$myClass->field("plde_id");
		$bd_plde_titulo=$myClass->field("plde_titulo");	
                $bd_tabl_tipodespacho=$myClass->field("tabl_tipodespacho");
                $bd_tabl_destino=$myClass->field("tabl_destino");
                $bd_depe_id=$myClass->field("depe_id");
                $bd_plde_todos=$myClass->field("plde_todos");
                $bd_depe_subdependencia=$myClass->field("depe_subdependencia");
                $bd_tiex_id=$myClass->field("tiex_id");
                $bd_plde_procedencia=$myClass->field("plde_procedencia");
                $bd_plde_mas_vistos=$myClass->field("plde_mas_vistos");
                $bd_plde_mas_firmas=$myClass->field("plde_mas_firmas");
                $bd_plde_destinatario=$myClass->field("plde_destinatario");
                $bd_plde_asunto=$myClass->field("plde_asunto");
                $bd_plde_ocultar_editor=$myClass->field("plde_ocultar_editor");
                $bd_plde_contenido=$myClass->field("plde_contenido");
                $bd_plde_formato=$myClass->field("plde_formato");
                $bd_plde_orientacion=$myClass->field("plde_orientacion");
                $bd_plde_imagen_fondo=$myClass->field("plde_imagen_fondo");
		$bd_usua_id= $myClass->field("usua_id");                
                $bd_plde_estado=$myClass->field('plde_estado');
                $username = $myClass->field('username');
                $usernameactual = $myClass->field('usernameactual');
                $bd_plde_fregistro=$myClass->field('plde_fregistro');
                $bd_plde_fregistroactual=$myClass->field('plde_actualfecha');
        }

}else{ // Si es nuevo
    $bd_tabl_tipodespacho=140;
    $bd_tiex_id=19;
    $bd_plde_formato=1;
    $bd_plde_orientacion=1;
    $bd_depe_id=getSession("sis_depe_superior");
}

// Inicio Para Ajax
require_once("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->setCharEncoding('utf-8');
$xajax->registerFunction("getEditor");
$xajax->registerFunction("getFormato");
$xajax->registerFunction("subDependencia");
$xajax->registerFunction("mas_firmas");

function getEditor($op,$tiex_id,$bd_plde_contenido,$bd_tabl_tipodespacho,$NameDiv){
    global $id,$username,$bd_plde_fregistro,$usernameactual,$bd_plde_fregistroactual,$bd_plde_formato;
    
    $objResponse = new xajaxResponse();

    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");
    
    $td=new clsTipExp_SQLlista();
    $td->whereID($tiex_id);
    $td->setDatos();
    $tiex_ocultar_editor=$td->field('tiex_ocultar_editor');
    $tiex_ocultar_editor=$tiex_ocultar_editor?$tiex_ocultar_editor:0;
    $bd_tiex_formato=$td->field('tiex_formato');
    $bd_tiex_formato=$bd_tiex_formato?$bd_tiex_formato:1;
    $bd_tiex_orientacion=$td->field('tiex_orientacion');
    $bd_tiex_orientacion=$bd_tiex_orientacion?$bd_tiex_orientacion:1;

    $bd_plde_orientacion=$bd_plde_orientacion?$bd_plde_orientacion:$bd_tiex_orientacion;
    $lista_orientacion=array("1,Vertical","2,Horizontal");
    $oForm->addField("Orientaci&oacute:n de Paginas: ",radioField("Orientacion",$lista_orientacion, "nr_plde_orientacion",$bd_plde_orientacion,"",'H'));    
    
    if($tiex_ocultar_editor==0 && inlist($bd_tabl_tipodespacho,'140,141,142') ){
        $bd_plde_formato=$bd_plde_formato?$bd_plde_formato:$bd_tiex_formato;
        $lista_formato=array("1,Doc.Administrativo",
                             "2,Area Total",
                             "3,Doc.de Gestión");
        $oForm->addField("Formato: ",radioField("Formato",$lista_formato, "nr_plde_formato",$bd_plde_formato,"onClick=\"xajax_getFormato(1,this.value,'divFormato')\"",'H'));
        $oForm->addHtml("<tr><td colspan=2><div id='divFormato'>\n");
        $oForm->addHtml(getFormato(2,$bd_plde_formato,'divFormato'));
        $oForm->addHtml("</div></td></tr>\n");


        //        $oForm->addHtml("<tr><td colspan=2>");
        //        $oForm->addHtml("<textarea name=\"K__plde_contenido\" id=\"K__plde_contenido\" rows=\"10\" cols=\"80\">
        //                        $bd_plde_contenido
        //                        </textarea>");
        //        $oForm->addHtml("</td></tr>\n");
        
    }
    
        //                    "<button type='button' data-toggle='modal' data-target='#myModal'>Etiquetas</button>");
    
    
    $contenido_respuesta=$oForm->writeHTML();    
    
    if($op==1){        
        $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);                
//        $objResponse->addScript("CKEDITOR.replace( 'K__plde_contenido', {
//                                        filebrowserBrowseUrl: '../../library/ckfinder/ckfinder.html',
//                                        filebrowserUploadUrl: '../../library/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files'
//                                } );
//                                ");
        
        $objResponse->addScript("$('.my_select_box').select2({
                                    placeholder: 'Seleccione un elemento de la lista',
                                    allowClear: true
                                })");        
        

//        $objResponse->addScript("$('#Orientacion').val('$bd_tiex_orientacion').change()");
//        $objResponse->addScript("$('#___plde_ocultar_editor').val($tiex_ocultar_editor)");
        return $objResponse;
    }
    else{
        return $contenido_respuesta;
    }
}

function getFormato($op,$valor,$NameDiv)
{
        global $bd_plde_imagen_fondo;
	$objResponse = new xajaxResponse();
		 
	$otable = new AddTableForm();
	$otable->setLabelWidth("20%");
	$otable->setDataWidth("80%");

	if($valor==2){
            $otable->addHidden("postPath",'gestdoc/fondos/'.SIS_EMPRESA_RUC.'/');    
            $otable->addField("Fondo:",fileField("Archivo","plde_imagen_fondo","$bd_plde_imagen_fondo",60,"onchange=validaextension(this,'jpg,JPG,jpeg,JPEG')"));   
	}	

	$contenido_respuesta=$otable->writeHTML();


        // Se devuelve el objeto, que este dara todas las instruccione JS para realizar la tarea
	if($op==1){
                $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);            
		return $objResponse;
	}else{
		return $contenido_respuesta	;
	}		

}

function subDependencia($op,$bd_plde_todos,$depe_id,$bd_depe_subdependencia,$NameDiv)
{
    $objResponse = new xajaxResponse();

    $otable = new AddTableForm();
    $otable->setLabelWidth("20%");
    $otable->setDataWidth("80%");

    //$sub_dependencia=$sub_dependencia?$sub_dependencia:$depe_id;
    if($bd_plde_todos==1 || $bd_plde_todos==="true"){
        $otable->addHidden("tx_depe_subdependencia","");
    }else{        
        $sqlDependencia=new dependencia_SQLBox($depe_id);
        $sqlDependencia=$sqlDependencia->getSQL();

        $otable->addField("EXCLUSVIO P/Sub Dependencia: ",listboxField("Sub Dependencia",$sqlDependencia,"tx_depe_subdependencia","$bd_depe_subdependencia","-- Todos --","","","class=\"my_select_box\""));        
    }
    
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

function mas_firmas($op,$depe_id,$bd_plde_mas_vistos,$bd_plde_mas_firmas,$bd_plde_destinatario,$NameDiv)
{
    $objResponse = new xajaxResponse();

    $otable = new AddTableForm();
    $otable->setLabelWidth("20%");
    $otable->setDataWidth("80%");
    
    $dependencia=new dependencia_SQLlista($depe_id);
    $dependencia->whereHabilitado();
    $sqlDependencia=$dependencia->getSQL_cbox();
    
    if($bd_plde_mas_vistos){
        $bd_plde_mas_vistos=explode(",",$bd_plde_mas_vistos);
    }else{
        $bd_plde_mas_vistos="";
    }
    $otable->addField("+Vistos: ",listboxField("mas_vistos",$sqlDependencia,"ax_plde_mas_vistos[]",$bd_plde_mas_vistos,"seleccione mas Vistos","","","class=\"my_select_box\" multiple "));
    
    if($bd_plde_mas_firmas){
        $bd_plde_mas_firmas=explode(",",$bd_plde_mas_firmas);
    }else{
        $bd_plde_mas_firmas="";
    }
    $otable->addField("+Firmas: ",listboxField("mas_firmas",$sqlDependencia,"ax_plde_mas_firmas[]",$bd_plde_mas_firmas,"seleccione mas Firnas","","","class=\"my_select_box\" multiple "));
    
    
    if($bd_plde_destinatario){
        $bd_plde_destinatario=explode(",",$bd_plde_destinatario);
    }else{
        $bd_plde_destinatario="";
    }
    $otable->addField("Destinatario(s):",listboxField("Destinatario",$sqlDependencia,"ax_plde_destinatario[]",$bd_plde_destinatario,"seleccione Destinatario","","","class=\"my_select_box\" multiple  "));        
    
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

$xajax->processRequests();
?>
<html>
<head>
	<title><?php echo $myClass->getTitle() ?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT ?>">

	<script language="JavaScript" src="../../library/js/focus.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>
        
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap.css">
        <link rel="stylesheet" type="text/css" href="../../library/bootstrap/css/bootstrap-theme.min.css">        
        <script src="../../library/bootstrap/js/bootstrap.min.js"></script>
        
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                

        <script src="../../library/ckeditor/ckeditor.js"></script>
        <script>CKEDITOR.dtd.$removeEmpty['span'] = false;</script>
        
	<script language='JavaScript'>
            
                function salvar(idObj) {
                    if (ObligaCampos(frm)){
                            ocultarObj(idObj,10)
                            document.frm.target = "controle";
                            document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
                            document.frm.submit();

                    }
                }
                /*
                        se invoca desde la funcion obligacampos (libjsgen.js)
                        en esta funci�n se puede personalizar la validaci�n del formulario
                        y se ejecuta al momento de gurdar los datos
                */
                function mivalidacion(frm) {
                        return true
                }

                /*
                        funci�n que define el foco inicial en el formulario
                */
                function inicializa() {
                        parent.content.document.frm.Sr_plde_titulo.focus();
                }
                
                function imprimir(id) {
                    AbreVentana('rptDocumento.php?id=' + id +'&op=2');
                }        
                
                </script>
        <?php
        $xajax->printJavascript(PATH_INC.'ajax/');
	verif_framework();
	?>


</head>

<body class="contentBODY" onload="inicializa()">

 <?php
 
//$nameSLab=getDbValue("select tabl_descripcion from tabla where tabl_tipo=9 and tabl_codigoauxiliar=$sitLab");
pageTitle($myClass->getTitle());

/* botones*/
$button = new Button;
$button->addItem("Guardar","javascript:salvar('Guardar')","content",2);
if($id){
    if($bd_plde_ocultar_editor==0){
        $button->addItem("Vista Previa","javascript:imprimir('$id')","content",0,0,"","");
    }
}
$button->addItem("Regresar a Lista de Registros",$myClass->getPageBuscar().'?clear=1&'.$param->buildPars(false),"content");
echo $button->writeHTML();


$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setUpload(true);
$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$id); // clave primaria
$form->addHidden("___plde_ocultar_editor",$bd_plde_ocultar_editor,'___plde_ocultar_editor'); // clave primaria

if (strlen($id)>0) { // edición
    $form->addField("C&oacute;digo: ",$id);
}
$form->addField("T&iacute;tulo: ",textField("Titulo","Sr_plde_titulo",$bd_plde_titulo,80,80));
                     //help("Utilice las siguientes Etiquetas:","{principal_firmante} Nombres y Apellidos del firmante principal del documento.<br>{principal_cargo} Cargo del firmante principal del documento.<br>{principal_dependencia} Dependencia del firmante principal del documento.",2));

$desp_tipo=new clsTabla_SQLlista();
$desp_tipo->whereTipo('TIPO_DESPACHO');
//$desp_tipo->whereNoID(142); //externo
$desp_tipo->orderUno();
$rs = new query($conn, $desp_tipo->getSQL());

$lista_nivel = array();
while ($rs->getrow()) {
    $lista_nivel[].=$rs->field("tabl_id").",".$rs->field("tabl_descripcion");
}

$form->addField("Tipo de ".NAME_EXPEDIENTE.": ",radioField("Tipo de ".NAME_EXPEDIENTE,$lista_nivel, "nr_tabl_tipodespacho",$bd_tabl_tipodespacho,"","H")); 

//$modo_para=new clsTabla_SQLlista();
//$modo_para->whereTipo('MODO_PARA');
//$sqlmodo_para=$modo_para->getSQL_cboxCodigo();
//$form->addField("Destino",listboxField("Destino",$sqlmodo_para,"nr_tabl_destino",$bd_tabl_destino,"",""));

//$dependencia=new dependencia_SQLlista();
//$dependencia->whereHabilitado();
//$sqlDependencia=$dependencia->getSQL_cbox();
//$form->addField("EXCLUSIVO para esta Dependencia: ",listboxField("Dependencia",$sqlDependencia,"nx_depe_id","$bd_depe_id","-- Todas --","","","class=\"my_select_box\"")); 


/* Instancio la Dependencia */
$sqlDependencia=new dependenciaSuperior_SQLBox3(getSession("sis_depe_superior"));
$sqlDependencia=$sqlDependencia->getSQL();        
$form->addField("Dependencia: ",listboxField("Dependencia",$sqlDependencia,"tr_depe_id","$bd_depe_id","-- Seleccione Dependencia --","onChange=\"xajax_subDependencia(1,document.frm.hx_plde_todos.checked,this.value,document.frm.tx_depe_subdependencia.value,'divSubDependencia');xajax_mas_firmas(1,this.value,'$bd_plde_mas_vistos','$bd_plde_mas_firmas','$bd_plde_destinatario','divMasFirmas')\"","","class=\"my_select_box\""));        
//FIN OBTENGO
$form->addField(checkboxField("Disponible P/Todas las Sub Dependencias","hx_plde_todos",1,$bd_plde_todos==1,"onClick=\"xajax_subDependencia(1,this.checked,document.frm.tr_depe_id.value,document.frm.tx_depe_subdependencia.value,'divSubDependencia')\""),"Disponible P/Todas las Sub Dependencias");        

$form->addHtml("<tr><td colspan=2><div id='divSubDependencia'>\n");
$form->addHtml(subDependencia(2,$bd_plde_todos,$bd_depe_id,"$bd_depe_subdependencia",'divSubDependencia'));
$form->addHtml("</div></td></tr>\n");

        
$texp=new clsTipExp_SQLlista();
$texp->orderUno();
$sqltipo=$texp->getSQL_cbox2();
$form->addField("Tipo de Documento:",listboxField("Tipo de Documento",$sqltipo,"tx_tiex_id",$bd_tiex_id,"-- Todos --","onChange=javascript:if(document.frm.___plde_ocultar_editor.value==0){xajax_getEditor(1,this.value,CKEDITOR.instances['K__plde_contenido'].getData(),document.frm.nr_tabl_tipodespacho.value,'divEditor')}else{xajax_getEditor(1,this.value,'',document.frm.nr_tabl_tipodespacho.value,'divEditor')}","","class=\"my_select_box\"")); 
    
$form->addHtml("<tr><td colspan=2><div id='divMasFirmas'>\n");
$form->addHtml(mas_firmas(2,$depe_id,$bd_plde_mas_vistos,$bd_plde_mas_firmas,$bd_plde_destinatario,'divMasFirmas'));
$form->addHtml("</div></td></tr>\n");


//$bd_depe_id=$id?$bd_depe_id:'';
//$form->addField("Procedencia (Firma del Jefe): ",listboxField("Procedencia",$sqlDependencia,"nx_plde_procedencia","$bd_plde_procedencia","-- Seleccione Procedencia --","","","class=\"my_select_box\""));
$form->addField("Asunto: ",textAreaField("Asunto","Ex_plde_asunto",$bd_plde_asunto,2,80,3000,"",0,"normal"));


if (strlen($id)>0) { // edición
    $form->addField("Activo: ",checkboxField("Activo","hx_plde_estado",1,$bd_plde_estado==1));
    $form->addField("Creado por: ",$username.'/'.$bd_plde_fregistro.' / '." Actualizado por: ".$usernameactual.'/'.$bd_plde_fregistroactual);
}else{
    $form->addHidden("hx_plde_estado",1); // clave primaria
}

$form->addHtml("<tr><td colspan=2><div id='divEditor' >\n"); //muestra numero de documento
$form->addHtml(getEditor(2,"$bd_tiex_id","$bd_plde_contenido","$bd_tabl_tipodespacho",'divEditor'));
$form->addHtml("</div></td></tr>\n");
$form->addHtml("<tr><td colspan=2><div id='divContenido' style=\"display:none\"></div></td></tr>\n");
if($bd_plde_ocultar_editor==0) {            
    $form->addHtml("<tr><td colspan=2>");
    $form->addHtml("<textarea name=\"K__plde_contenido\" id=\"K__plde_contenido\" rows=\"10\" cols=\"80\">
                    $bd_plde_contenido
                    </textarea>");
    $form->addHtml("</td></tr>\n");
}
echo $form->writeHTML();
?>
    
    
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-sm">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Modal Header</h4>
      </div>
      <div class="modal-body">
          <p><b>{fecha_documento} </b></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>    
    
</body>
  
    <script>
        $(".my_select_box").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true,
            width:'90%'            
        });        
        
        
        // Replace the <textarea id="editor1"> with a CKEditor
        // instance, using default configuration.
        <?php 
          if($bd_plde_ocultar_editor==0) {            
        ?>

        CKEDITOR.replace( 'K__plde_contenido', {
                            filebrowserBrowseUrl: '../../library/ckfinder/ckfinder.html',
                            filebrowserUploadUrl: '../../library/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files'
                    } );
                    
        <?php        
          }
//            if($bd_plde_mas_firmas){
//                echo "$('#mas_firmas').val([$bd_plde_mas_firmas]).trigger('change');";
//            }
//            if($bd_plde_destinatario){
//                echo "$('#Destinatario').val([$bd_plde_destinatario]).trigger('change');";
//            }
        ?>
        $('.normal').autosize({append:''});    
    </script>

</html>

<?php
/*
	cierro la conexion a la BD
*/
$conn->close();