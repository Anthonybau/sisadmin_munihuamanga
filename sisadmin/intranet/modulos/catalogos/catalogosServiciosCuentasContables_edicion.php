<?php
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("catalogosServiciosCuentasContables_class.php");
include("catalogosServicios_class.php");
include("../catalogos/catalogosTabla_class.php");
include("../siscont/siscontCatalogosPlanContable_class.php");
include("../planillas/TipoPlanilla_class.php");
include("../catalogos/AFP_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$id_relacion = getParam("id_relacion"); // captura la variable que viene del objeto lista

$myClass = new clsServicioCuentasContables($id,'Cuentas Contables');

if ($id>0) { // edición
        $myClass->setDatos();
	if($myClass->existeDatos()){
              $bd_seac_id=$myClass->field("seac_id");
              $bd_tabl_fase=$myClass->field("tabl_fase");
              $bd_tabl_tipo=$myClass->field("tabl_tipo");              
              $bd_tipl_id=$myClass->field("tipl_id");
              $bd_afp_id=$myClass->field("afp_id");
              $bd_tabl_modpago=$myClass->field("tabl_modpago");
              $bd_plco_id_debe=$myClass->field("plco_id_debe");
              $bd_plco_id_haber=$myClass->field("plco_id_haber");
              
              
              $bd_usua_id = $myClass->field("usua_id"); 
              $nameUsers= $myClass->field('username');
              $fregistro=$myClass->field('seac_fecharegistro');
              $nameUsersActual=$myClass->field('usernameactual');
              $fregistroActual=$myClass->field('seac_actualfecha');
        }
}


?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="<?php echo PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>

        <script type="text/javascript" src="../../library/jquery/jquery-1.11.2.min.js"></script>   
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
		document.frm.tx_tabl_modulo.focus();
	}
	</script>
	<?php
            verif_framework();
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
$form->addHidden("f_id",$bd_seac_id); // clave primaria
$form->addHidden("nx_serv_codigo",$id_relacion); // chave estrangeira do relacionamento
$form->addHidden("pagina",$pg); // numero de página que llamo

$myConcepto = new servicios_SQLlista();
$myConcepto->whereID($id_relacion);
$myConcepto->setDatos();

$form->addField("C&oacute;digo de Servicio: ",$myConcepto->field("serv_id"));
$form->addField("Descripci&oacute;n: ",$myConcepto->field("serv_descripcion"));
$form->addField("Grupo: ",$myConcepto->field("segr_id")." ".$myConcepto->field("grupo"));
$form->addField("Sub grupo: ",$myConcepto->field("sesg_id")." ".$myConcepto->field("sgrupo"));
$depe_id=$myConcepto->field('depe_id');
$depe_id=$depe_id?$depe_id:2;
//$tabla=new clsTabla_SQLlista();
//$tabla->whereTipo('TIPO_MODULO');
//$tabla->orderUno();
//$sqlTipoModulo=$tabla->getSQL_cbox();   
//$form->addField("M&oacute;dulo: ",listboxField("Modulo",$sqlTipoModulo,"tx_tabl_modulo",$bd_tabl_modulo,"",""));

$tabla=new clsTabla_SQLlista();
$tabla->whereTipo('FASE_ASIENTO_CONTABLE');
//switch($myConcepto->field("tabl_categoria")){
//    case 214://RECAUDACION
//        $tabla->whereID(313);
//        break;
//    case 215://DEPOSITO    
//        $tabla->whereID(314);
//        break;
//    case 216://FONDO DE CAJA
//        $tabla->whereID(355);
//        break;    
//}
$tabla->orderUno();
$sqlFaseContable=$tabla->getSQL_cbox();   
$form->addField("Fase: ",listboxField("Fase",$sqlFaseContable,"tr_tabl_fase",$bd_tabl_fase,"-- Seleccione Fase --",""));

$tabla=new clsTabla_SQLlista();
$tabla->whereTipo('TIPO_ASIENTO_CONTABLE');
$tabla->orderUno();
$sqlClaseContable=$tabla->getSQL_cbox();   
$form->addField("Tipo: ",listboxField("Tipo",$sqlClaseContable,"tr_tabl_tipo",$bd_tabl_tipo,"-- Seleccione Tipo --",""));

//SI ES CONCEPTO DE HABERES
if( strpos($myConcepto->field("segr_tipo"),"H") !== false )
{
    $tabla=new tipoPlanilla_SQLlista();
    $tabla->whereActivo();
    $tabla->orderUno();        
    $sqlTipoPlla=$tabla->getSQL_cbox();        
    $form->addField("Tipo de Planilla:",listboxField("Tipo de Planilla",$sqlTipoPlla,"tx_tipl_id",$bd_tipl_id,"-- Ninguno --",""));
    
    $sqlAfp=new clsAFP_SQLlista();
    $sqlAfp=$sqlAfp->getSQL_cbox();                
    $form->addField("AFP : ",listboxField("AFP Actual",$sqlAfp, "tx_afp_id",$bd_afp_id,"-- Ninguno --",""));
}

//SI ES CONCEPTO DE RECAUDACIONES
if( strpos($myConcepto->field("segr_tipo"),"T") !== false )
{
    $tabla=new clsTabla_SQLlista();
    $tabla->whereTipo('MODALIDAD_PAGO_RECAUDACION');
    $tabla->whereNoCodigo('2,3,4'); //N/C
    $tabla->orderUno();
    $sqlFaseContable=$tabla->getSQL_cboxCodigo();   
    $form->addField("Modalidad: ",listboxField("Modalidad",$sqlFaseContable,"tx_tabl_modpago",$bd_tabl_modpago,"-- Todos --",""));
}

if($bd_plco_id_debe){
    $cuentaContable=new clsCuenta_SQLlista();
    $cuentaContable->whereID($bd_plco_id_debe);
    $sqlCuentaD=$cuentaContable->getSQL_cuentas2();
}
$form->addField("Debe: ",listboxField("Debe",$sqlCuentaD,"tx_plco_id_debe",$bd_plco_id_debe,"Escriba cuenta contable","", "","class=\"my_select_box\""));
if($bd_plco_id_haber){
    $cuentaContable=new clsCuenta_SQLlista();
    $cuentaContable->whereID($bd_plco_id_haber);
    $sqlCuentaH=$cuentaContable->getSQL_cuentas2();
}
$form->addField("Haber: ",listboxField("Haber",$sqlCuentaH,"tx_plco_id_haber",$bd_plco_id_haber,"Escriba cuenta contable","", "","class=\"my_select_box\""));

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
    $(".my_select_box").select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true,
            width: '80%',
            ajax: {
                      url: '../siscont/jswBuscarCuentaContable.php',
                      dataType: 'json',
                      delay: 250,
                      data: function (params) {
                            var queryParameters = {
                              q: params.term,
                              depe_id: <?php echo $depe_id ?>
                            }
                            return queryParameters;
                      },
                      processResults: function (data) {
                        return {
                          results: data
                        };
                      },
                      cache: true
                   }
            });
    
    </script>    
</body>
</html>

<?php
/* cierro la conexión a la BD */
$conn->close();