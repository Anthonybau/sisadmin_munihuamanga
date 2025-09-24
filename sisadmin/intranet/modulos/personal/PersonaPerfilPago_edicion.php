<?php
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("./PersonaPerfilPago_class.php");
include("../catalogos/catalogosServicios_class.php");
include("../siscopp/siscoppCatalogosClasificador_class.php");
include("../personal/personalDatosLaborales_class.php");
include("../catalogos/catalogosTabla_class.php");
include("./Persona_class.php");
include("../planillas/TipoPlanilla_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$id_relacion = getParam("id_relacion"); // captura la variable que viene del objeto lista

$myClass = new clsPerfilPago($id,'Perfil de Pago');

if ($id>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
              $bd_pppa_id= $myClass->field("pppa_id"); 
              $bd_tipl_id= $myClass->field("tipl_id"); 
              $bd_serv_codigo= $myClass->field("serv_codigo"); 
              $bd_clas_id = $myClass->field("clas_id"); 
              $bd_pdla_id = $myClass->field("pdla_id"); 
              $bd_pppa_leyenda = $myClass->field("pppa_leyenda"); 
              $bd_pppa_porcentaje = $myClass->field("pppa_porcentaje"); 
              $bd_pppa_importe = $myClass->field("pppa_importe"); 
              $bd_tabl_mesini = $myClass->field("tabl_mesini"); 
              $bd_tabl_mesfin = $myClass->field("tabl_mesfin"); 
              $bd_pppa_caducidad=$myClass->field("pppa_caducidad"); 
              $bd_pppa_exclusivo=$myClass->field("pppa_exclusivo"); 
              $bd_pppa_periodicidad=$myClass->field("pppa_periodicidad"); 
              $bd_pppa_ncuota_ini= $myClass->field("pppa_ncuota_ini"); 
              $bd_pppa_ncuota_fin= $myClass->field("pppa_ncuota_fin"); 
              $bd_pppa_anno= $myClass->field("pppa_anno"); 
              $bd_pppa_minutos= $myClass->field("pppa_minutos"); 
              $bd_tipl_id_excepcion= $myClass->field("tipl_id_excepcion"); 
              $bd_usua_id = $myClass->field("usua_id"); 
              $nameUsers= $myClass->field('username');
              $fregistro=$myClass->field('pppa_fregistro');
              $nameUsersActual=$myClass->field('usernameactual');
              $fregistroActual=$myClass->field('pppa_actualfecha');
        }
}else{
            $persona=new clsPersona_SQLlista();
            $persona->whereID($id_relacion);
            $persona->setDatos();

            $tabla=new tipoPlanilla_SQLlista();
            $tabla->whereSitLaboral($persona->field('tabl_idsitlaboral'));
            $tabla->orderUno();
            $tabla->setDatos();
    
            $bd_tipl_id=$tabla->field('tipl_id');
            
}


/* Archivos para calendario */
require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("pideConcepto");
$xajax->registerFunction("pideImporte");
$xajax->registerFunction("pidePeriodo");
$xajax->registerFunction("pideAnno");

function pideConcepto($op,$tipoPlanilla){
    global $bd_serv_codigo,$id_relacion;
	$objResponse = new xajaxResponse();

	$otable = new AddTableForm();
	$otable->setLabelWidth("20%");
	$otable->setDataWidth("80%");
        
        $bd_serv_codigo=$bd_serv_codigo?$bd_serv_codigo:0;
        $sqlConceptos=new servicios_SQLlista();
        $sqlConceptos->whereTipo('H');
        //$sqlConceptos->whereNOautomatico();
        $sqlConceptos->whereNoTipoVinculo(129);//archivo de terceros
        $sqlConceptos->whereActivo();
        $sqlConceptos->whereNotLista("SELECT a.serv_codigo
                                            FROM personal.persona_perfil_pago a
                                            LEFT JOIN catalogos.servicio        b ON a.serv_codigo=b.serv_codigo
                                            LEFT JOIN catalogos.servicio_sgrupo c ON b.sesg_id=c.sesg_id
                                            WHERE a.pers_id=$id_relacion 
                                                  /*AND b.sesg_id!=157  DESCUENTOS DE LEY' */
                                                  AND a.serv_codigo!=$bd_serv_codigo
                                                  AND b.tabl_vinculado!=128
                                       ");


        $sqlConceptos=$sqlConceptos->getSQL_servicio3();
        $otable->addField("Concepto: ",listboxField("Concepto",$sqlConceptos,"tr_serv_codigo",$bd_serv_codigo,"-- Seleccione Concepto --","onChange=\"xajax_pideImporte(1,this.value,'$tipoPlanilla')\"", "","class=\"my_select_box\""));

                
	$contenido_respuesta=$otable->writeHTML();


        // Se devuelve el objeto, que este dara todas las instruccione JS para realizar la tarea
	if($op==1){
                $objResponse->addAssign('divConcepto','innerHTML', $contenido_respuesta);            
                $objResponse->addScript("  $('.my_select_box').select2({
                                            placeholder: 'Seleccione un elemento de la lista',
                                            allowClear: true
                                          });");                                            
                $objResponse->addScript("document.frm.tr_serv_codigo.focus()");                
		return $objResponse;
	}else{
		return $contenido_respuesta	;
	}		
}

function pideImporte($op,$bd_serv_codigo,$tipoPlanilla){
    global $bd_pppa_porcentaje,$bd_pppa_importe,$bd_pdla_id,$bd_pppa_leyenda,$bd_pppa_periodicidad,
           $bd_pppa_ncuota_ini,$bd_pppa_ncuota_fin,$bd_pppa_minutos,$bd_clas_id,$bd_tipl_id_excepcion,$bd_pppa_exclusivo;
    
	$objResponse = new xajaxResponse();
	$otable = new AddTableForm();
	$otable->setLabelWidth("20%");
	$otable->setDataWidth("80%");
                        
        if($bd_serv_codigo){
            $concepto=new servicios_SQLlista();
            $concepto->whereID($bd_serv_codigo);
            $concepto->setDatos();
            if($concepto->field("tipl_id")){
                $tipo_planilla= getDbValue("SELECT tipl_descripcion FROM planillas.tipo_planilla WHERE tipl_id=".$concepto->field("tipl_id"));
                $otable->addField("Tipo de Planilla: ",$tipo_planilla);      
            }
            $otable->addField("Grupo/Sub Grupo: ",$concepto->field("grupo").'/'.$concepto->field("sgrupo"));      
            
            if($concepto->field("tabl_vinculado")==128){//JUDICIAL
                $persona=new clsDatosLaborales_SQLlista();
                if($bd_pdla_id){
                    $persona->whereID($bd_pdla_id);
                    //$persona->whereSitLaboral(42);//judicial
                }else{
                    $persona->whereSitLaboral(42);//judicial
                }
                $sqlJudicial=$persona->getSQL_cbox();
                $otable->addField("Judicial:",listboxField("Judicial",$sqlJudicial,"tr_pdla_id",$bd_pdla_id,"-- Seleccione Judicial --", "","","class=\"my_select_box\""));                    
            }
            
            if($concepto->field("tabl_tipoconcepto")==121){//PORCENTUAL
                $bd_pppa_porcentaje=$bd_pppa_porcentaje?$bd_pppa_porcentaje:$concepto->field("conc_porcentaje");
                $bd_pppa_porcentaje=$bd_pppa_porcentaje>0?$bd_pppa_porcentaje:'';
                $otable->addField("%: ",numField("%","nr_pppa_porcentaje",$bd_pppa_porcentaje,10,5,2));        
                $obj="nr_pppa_porcentaje";
            }
            elseif($concepto->field("tabl_tipoconcepto")==122){//IMPORTE MANUAL    
                $bd_pppa_importe=$bd_pppa_importe>0?$bd_pppa_importe:$concepto->field("conc_importe");
                $bd_pppa_importe=$bd_pppa_importe>0?$bd_pppa_importe:'';
                $otable->addField("Importe: ",numField("Importe","nr_pppa_importe",$bd_pppa_importe,16,12,2));
                $obj="nr_pppa_importe";
            }
            if($concepto->field("tabl_vinculado")==127){//DESCRIPCION
                $otable->addField("Leyenda: ",textField("Leyenda","Sx_pppa_leyenda",$bd_pppa_leyenda,80,100));       
            }elseif($concepto->field("tabl_vinculado")==128){//JUDICIAL
                $otable->addField("Documento:",textField("Documento","Sx_pppa_leyenda",$bd_pppa_leyenda,80,100));   
            }elseif($concepto->field("tabl_vinculado")==125){//PAGO X CUOTAS
                $otable->addField("Cuota Inicial: ",numField("Cuota Inicial","nr_pppa_ncuota_ini",$bd_pppa_ncuota_ini,5,5,0));                
                $otable->addField("Final: ",numField("Cuota Final","nr_pppa_ncuota_fin",$bd_pppa_ncuota_fin,5,5,0));                
            }elseif($concepto->field("tabl_vinculado")==126){//MINUTOS
                $otable->addField("Minutos: ",numField("Minutos","nr_pppa_minutos",$bd_pppa_minutos,12,12,4,false));
                $obj="nr_pppa_minutos";
            }
        
            if($concepto->field("tabl_vinculado")!=125){//SI NO ES PAGO X CUOTAS
                $lista = array($concepto->field("serv_periodicidad").",".$concepto->field("periodicidad")); 
                $bd_pppa_periodicidad=$concepto->field("serv_periodicidad");
                $otable->addField("Periodicidad: ",radioField("Periodicidad",$lista, "xr_pppa_periodicidad",$bd_pppa_periodicidad,"onClick=\"xajax_pidePeriodo(1,this.value,'divPeriodo')\" READONLY",'H'));
                $otable->addHtml("<tr><td colspan=2><div id='divPeriodo'>\n");
                $otable->addHtml(pidePeriodo(2,$bd_pppa_periodicidad,0));
                $otable->addHtml("</div></td></tr>\n");
                
                //$bd_tabl_mesini=$bd_tabl_mesini?$bd_tabl_mesini:1;
                //$bd_tabl_mesfin=$bd_tabl_mesfin?$bd_tabl_mesfin:12;

                //$tabla=new clsTabla_SQLlista();
                //$tabla->whereTipo('MES');
                //$sqlTipo=$tabla->getSQL_cbox();
                //$otable->addField("Mes Desde:",listboxField("Mes Desde",$sqlTipo,"tr_tabl_mesini",$bd_tabl_mesini)."<b> Hasta:</b>".listboxField("Mes Hasta",$sqlTipo,"tr_tabl_mesfin",$bd_tabl_mesfin));
            }
            
            if(!$bd_clas_id){
                $bd_clas_id=$concepto->field("clas_id");                    
                if(!$bd_clas_id){//si no hay clasificador, lo obtiene del tipo de planilla
                    $tabla=new clsTabla_SQLlista();
                    $tabla->whereID($tipoPlanilla);
                    $tabla->setDatos();
                    $bd_clas_id=$tabla->field('clas_id');                    
                }
            }            
            if( inlist(SIS_EMPRESA_TIPO,'2,3') ){
                $sqlClasificador=new clsClasificador_SQLlista();
                $sqlClasificador->whereTipo(2);//gastos
                $sqlClasificador->whereEspecifica();
                $sqlClasificador=$sqlClasificador->getSQL_cbox();
                $otable->addField("Clasificador: ",listboxField("Clasificador",$sqlClasificador,"tr_clas_id",$bd_clas_id,"-- Seleccione Clasificador --", "","","class=\"my_select_box\""));
            }
            
            if($concepto->field("tabl_vinculado")==128){//JUDICIAL                
                /*SOLICITA PLANILLA EXCEPCION*/
                $tabla=new tipoPlanilla_SQLlista();
                $tabla->whereActivo();
                $tabla->orderUno();
                $sqlTipoPlla=$tabla->getSQL_cbox();
                $otable->addField("Planilla Excepci&oacute;n: ",listboxField("Planilla Excepcion",$sqlTipoPlla,"tx_tipl_id_excepcion",$bd_tipl_id_excepcion,"-- Ning&uacute;na --",""));
                $otable->addField("",checkboxField("Exclusivo para este Tipo de Planilla Seleccionada","hx_pppa_exclusivo",1,$bd_pppa_exclusivo).'&nbsp;&nbsp;<b>Exclusivo para este Tipo de Planilla Seleccionada</b>');
            }
        }

	$contenido_respuesta=$otable->writeHTML();            
        
        if($op==1){
            $objResponse->addAssign('divImporte','innerHTML', $contenido_respuesta);            
            $objResponse->addScript("  $('.my_select_box').select2({
                                            placeholder: 'Seleccione un elemento de la lista',
                                            allowClear: true
                                          });");                                            

            if($obj){
                $objResponse->addScript("document.frm.$obj.focus()");                
            }            
            return $objResponse;
	}else{
            return $contenido_respuesta	;
	}		

}

function pidePeriodo($op,$valor,$nameDiv){
    global $bd_tabl_mesini,$bd_pppa_caducidad;
    
	$objResponse = new xajaxResponse();
	$otable = new AddTableForm();
	$otable->setLabelWidth("20%");
	$otable->setDataWidth("80%");
                        
        
        if($valor==2){//UNICA VEZ
                $tabla=new clsTabla_SQLlista();
                $tabla->whereTipo('MES');
                $sqlTipo=$tabla->getSQL_cboxCodigo();
                $bd_tabl_mesini=$bd_tabl_mesini?$bd_tabl_mesini:date('m');
                //$bd_tabl_mesini=date('m');
                $otable->addField("Mes:",listboxField("Mes",$sqlTipo,"tr_tabl_mesini",$bd_tabl_mesini));
                $lista = array("1,Solo Un A&ntilde;o","2,Todos los A&ntilde;os"); 
                $bd_pppa_caducidad=$bd_pppa_caducidad?$bd_pppa_caducidad:1;
                $otable->addField("Caducidad: ",radioField("Caducidad",$lista, "xr_pppa_caducidad",$bd_pppa_caducidad,"onClick=\"xajax_pideAnno(1,this.value,'divCaducidad')\"",'H'));                
                $otable->addHtml("<tr><td colspan=2><div id='divCaducidad'>\n");
                $otable->addHtml(pideAnno(2,$bd_pppa_caducidad,0));
                $otable->addHtml("</div></td></tr>\n");
                
        }

	$contenido_respuesta=$otable->writeHTML();            
        
        if($op==1){
            $objResponse->addAssign($nameDiv,'innerHTML', $contenido_respuesta);            
            return $objResponse;
	}else{
            return $contenido_respuesta	;
	}		

}

function pideAnno($op,$valor,$nameDiv){
    global $bd_pppa_anno;
	$objResponse = new xajaxResponse();

	$otable = new AddTableForm();
	$otable->setLabelWidth("20%");
	$otable->setDataWidth("80%");
        
        
        if($valor==1){//SOLO ESTE AÑO
            //$bd_pppa_anno=$bd_pppa_anno?$bd_pppa_anno:date('Y');
            $bd_pppa_anno=date('Y');
            $otable->addField("A&ntilde;o:",numField("Periodo","zr_pppa_anno",$bd_pppa_anno,4,4,0,false));   
        }         
                
	$contenido_respuesta=$otable->writeHTML();


        // Se devuelve el objeto, que este dara todas las instruccione JS para realizar la tarea
	if($op==1){
                $objResponse->addAssign($nameDiv,'innerHTML', $contenido_respuesta);            
		return $objResponse;
	}else{
		return $contenido_respuesta	;
	}		
}
$xajax->processRequests();
?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="<?php echo PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>

        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>   
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                
	

	<script language='JavaScript'>
	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
			parent.content.document.frm.submit();
		}
	}
	/*
		se invoca desde la funcion obligacampos (libjsgen.js)
		en esta funci�n se puede personalizar la validaci�n del formulario
		y se ejecuta al momento de gurdar los datos
	*/
	function mivalidacion(){
            var sError="Mensajes del sistema: "+"\n\n";
            var nErrTot=0;

		if (nErrTot>0){
			alert(sError)
			eval(foco)
			return false
		}else
			return true

	}


	/*
		funci�n que define el foco inicial en el formulario
	*/
	function inicializa() {
		document.frm.tr_serv_codigo.focus();
	}
	</script>
	<?
            $xajax->printJavascript(PATH_INC.'ajax/');
            verif_framework();
            $calendar->load_files();	
        ?>

</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("Edici&oacute;n de ".$myClass->getTitle());



/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$bd_pppa_id); // clave primaria
$form->addHidden("nx_pers_id",$id_relacion); // chave estrangeira do relacionamento
$form->addHidden("pagina",$pg); // numero de página que llamo

$myPersona = new clsPersona($id_relacion);
$myPersona->setDatos();
$form->addField("Trabajador: ",$myPersona->field("empleado").' / '.$myPersona->field("pers_dni"));

$persona=new clsPersona_SQLlista();
$persona->whereID($id_relacion);
$persona->setDatos();
$form->addField("Personal: ",$persona->field('empleado'));
   

//$sqlTipoPlla="select tabl_id,tabl_descripcion as descripcion from tabla where tabl_tipo='TIPO_PLANILLA' AND tabl_id!=48 order by 1";
$tabla=new tipoPlanilla_SQLlista();
$tabla->whereActivo();
$tabla->orderUno();
$sqlTipoPlla=$tabla->getSQL_cbox();

$form->addField("Tipo Planilla: ",listboxField("Tipo Planilla",$sqlTipoPlla,"tr_tipl_id",$bd_tipl_id,"-- Seleccione Tipo de Planilla --","onChange=\"xajax_pideConcepto(1,this.value);\"", "",""));

$form->addHtml("<tr><td colspan=2><div id='divConcepto'>\n");
$form->addHtml(pideConcepto(2,$bd_tipl_id));
$form->addHtml("</div></td></tr>\n");
            
$form->addHtml("<tr><td colspan=2><div id='divImporte'>\n");
$form->addHtml(pideImporte(2,$bd_serv_codigo,$bd_tipl_id));
$form->addHtml("</div></td></tr>\n");


/* botones */
$button = new Button;
$button->setDiv(false);
$button->addItem("Guardar","javascript:salvar('Guardar')","content",2);
$form->addField("",$button->writeHTML());


if(strlen($id)) {
    $form->addBreak("<b>Control</b>");
    $form->addField("Creado: ",$nameUsers.'/'.substr($fregistro,0,19));
    $form->addField("Actualizado: ",$nameUsersActual.'/'.substr($fregistroActual,0,19));
}        

echo $form->writeHTML();
?>
    <script>
    $("select").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true,
            width: '50%'
            });
    
    </script>    
</body>
</html>

<?php
/* cierro la conexión a la BD */
$conn->close();