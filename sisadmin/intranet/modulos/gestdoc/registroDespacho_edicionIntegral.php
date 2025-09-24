<?
/* formulario de ingreso y modificación */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* Cargo mi clase Base */
include("registroDespacho_class.php");
include("../catalogos/catalogosDependencias_class.php");
include("../catalogos/catalogosTabla_class.php");
include("../catalogos/catalogosDependenciaExterna_class.php");
include("../catalogos/catalogosPrioridadAtencion_class.php");
include("../catalogos/catalogosTipoExpediente_class.php");
include("../personal/personalDatosLaborales_class.php");
include("../admin/adminUsuario_class.php");
include("getAcumDespacho_class.php");
include("../../library/fckeditor/fckeditor.php") ;

class carDeriva_class {
    function __construct(){
       $this->cuenta=0;
       $this->carrito=array();
       $this->id=array();
       //$this->eliminados=array();
    }

    function Add($contenido){
        $hallado=array_search($contenido['id'], $this->id);
        if(!strlen($hallado)) {
            $this->carrito[]=$contenido;
            $this->id[]=$contenido['id'];
            $this->cuenta++;
            return(1);
        }else{
             return(0);

        }

    }

    function Mod($id,$contenido){
        $this->carrito[$id]=$contenido;
    }

    //elimina un producto del carrito. recibe el id del registro a eliminar
    function Del($id){
        /* Agrego al array de eliminados el id del registro a eliminar.  El array eliminados se usa al editar el registro  */
	if($this->carrito[$id]['tx_hijo_id'])
            $this->eliminados[]=$this->carrito[$id]['tx_hijo_id'];

        unset($this->id[$id]);
	unset($this->carrito[$id]);	/* Elimino el registro del array */
	$this->id--;
        $this->cuenta--;
    }

    function getConteo(){
        return ($this->cuenta);
    }

    function getArray(){
        return ($this->carrito);
    }

    function getArrayEliminados(){
        return ($this->eliminados);
    }


}
/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista
$clear=getParam("clear");
$array=getParam("sel");
$regSeleccionadosExp='';
if(is_array($array)) {
    for ($i = 0; $i < count($array); $i++) {
            $arrayPadreHijo=explode('_',$array[$i]);    
            $regSeleccionadosExp.=$arrayPadreHijo[0].',';
            $regSeleccionadosId.=$arrayPadreHijo[1].',';
            $referencia=explode('.',$arrayPadreHijo[0]);    
            $bd_desp_expediente=$bd_desp_expediente?$bd_desp_expediente:$referencia[0];
    }        
    $regSeleccionadosExp=trim($regSeleccionadosExp,',');
    $regSeleccionadosId=trim($regSeleccionadosId,',');
}

$myClass = new despacho($id,NAME_EXPEDIENTE.' de Documentos');

if (!isset($_SESSION["ocarrito"])){
    $_SESSION["ocarrito"] = new carDeriva_class();
    /*variable que cuenta los registos archivados del despacho*/
    $cuenta_archivados=0;
    $cuenta_derivaciones=0;
    $cuenta_recibidos=0;
    if (strlen($id)) { // edición
        $myClass->setDatos();
            if($myClass->existeDatos()){
                    $bd_id_despacho= $myClass->field('id');
                    $bd_depe_id = $myClass->field('depe_id');
                    $bd_prat_descripcion = $myClass->field('prat_descripcion');
                    $bd_tabl_modorecepcion = $myClass->field('tabl_modorecepcion');
                    $bd_modo_recepcion= $myClass->field('modo_recepcion');
                    $bd_tabl_tipodespacho = $myClass->field('tabl_tipodespacho');
                    $bd_tipo_despacho = $myClass->field('tipo_despacho');
                    $tipo_despacho = $myClass->field("tipo_despacho");
                    $num_documento = $myClass->field("num_documento");
                    $bd_desp_fecha = dtos($myClass->field('desp_fecha'));
                    $bd_tiex_id = $myClass->field('tiex_id');
                    $tiex_descripcion = $myClass->field('tiex_descripcion');
                    $bd_desp_numero = $myClass->field('desp_numero');
                    $bd_desp_anno = $myClass->field('desp_anno');
                    $depe_nombre = $myClass->field('depe_nombre');
                    //$bd_prov_id= $myClass->field('prov_id');
                    $bd_desp_descripaux= $myClass->field('desp_descripaux');
                    $bd_desp_firma = $myClass->field('desp_firma');

                    $bd_desp_codigo= $myClass->field('desp_codigo');
                    $bd_desp_entidad_origen= $myClass->field('desp_entidad_origen');

                    $bd_desp_cargo = $myClass->field('desp_cargo');
                    $bd_desp_siglas = $myClass->field('desp_siglas');
                    $bd_desp_asunto = $myClass->field('desp_asunto');
                    $bd_desp_folios = $myClass->field('desp_folios');
                    $bd_desp_proyectadopor = $myClass->field('desp_proyectadopor');
                    //$bd_desp_trelacionado= $myClass->field('desp_trelacionado');
                    $bd_desp_expediente= $myClass->field('desp_expediente');
                    $bd_desp_notas= $myClass->field('desp_notas');
                    $bd_prat_id = $myClass->field('prat_id');
                    $bd_desp_exp_legal=$myClass->field('desp_exp_legal');
                    $bd_desp_demandante=$myClass->field('desp_demandante');
                    $bd_desp_demandado=$myClass->field('desp_demandado');
                    $bd_desp_resolucion=$myClass->field('desp_resolucion');
                    $bd_exle_id=$myClass->field('exle_id');
                    $regSeleccionadosExp=$myClass->field('desp_adjuntados_exp');
                    $regSeleccionadosId=$myClass->field('desp_adjuntados_id');
                    $bd_usua_id	= $myClass->field('usua_id');
                    $username= $myClass->field("username");
                    $usernameactual= $myClass->field("usernameactual");

                    $derivacion= new despachoDerivacion_SQLlista();
                    $derivacion->wherePadreID($id);
                    $derivacion->whereUsuaIDCrea($bd_usua_id);
                    $derivacion->orderUno();
                    $sql=$derivacion->getSQL();
                    $rs = new query($conn, $sql);

                    /*recorre las derivaciones*/
                    while ($rs->getrow()) {
                            if($rs->field('usua_iddestino')){
                                $idDeriva=$rs->field('depe_iddestino').'_'.$rs->field('usua_iddestino');
                                $elemento=$rs->field("depe_iddestino")."_".$rs->field("usua_iddestino")."_ ".$rs->field("depe_nombrecorto_destino")." [".$rs->field("usuario_destino")."]";
                            }else{
                                $idDeriva=$rs->field('depe_iddestino');
                                $elemento=$rs->field("depe_iddestino")."_ ".$rs->field("depe_nombrecorto_destino");
                            }

                            if($rs->field('dede_concopia')==1){
                                $idDeriva.='C';
                                $cc='Cc';
                            }else{
                                $cc='';
                            }


                            $arrayEdit['tx_hijo_id']=$rs->field('dede_id');
                            $arrayEdit['id']=$idDeriva;
                            $arrayEdit['depe_id']=$rs->field('depe_iddestino');
                            $arrayEdit['usua_id']=$rs->field('usua_iddestino');
                            $arrayEdit['elemento']=$elemento;
                            $arrayEdit['proveido']=$rs->field('dede_proveido');
                            $arrayEdit['cc']=$cc;
                            $arrayEdit['usua_idcrea']=$rs->field('usua_idcrea');
                            $arrayEdit['dede_estado']=$rs->field('dede_estado');

                            $_SESSION["ocarrito"]->Add($arrayEdit);

                            if($rs->field('dede_estado')==6) //archivado
                                $cuenta_archivados++;

                            $cuenta_derivaciones++;
                            
                            if($rs->field('usua_idrecibe')) //recibidos
                                $cuenta_recibidos++;
                    }
                    /*fin recorre las derivaciones*/
            }
    }
}

if (!$id){
    $bd_depe_id=getSession("sis_depeid");
    $bd_desp_fecha=date('d/m/Y');
    $bd_tabl_modorecepcion=145; //DIRECTA
    $bd_tiex_id=1;//INFORME x default
    $bd_desp_asunto='';
    $bd_desp_folios='';
    $bd_desp_proyectadopor='';
    $bd_desp_trelacionado='';
    $bd_prat_id=0;
    $bd_tabl_tipodespacho=getSession("SET_TIPO_DESPACHO");
}

/* verificación del nível de usuario */
verificaUsuario(1);

/* Archivos para calendario */
require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);


// Inicio Para Ajax
require_once("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->setCharEncoding('utf-8');

$xajax->registerFunction("getInicia");
$xajax->registerFunction("getDatosFirma");
$xajax->registerFunction("addCarrito");
$xajax->registerFunction("elimCarrito");
$xajax->registerFunction("modiCarrito");
$xajax->registerFunction("verCarrito");
$xajax->registerFunction("conCopia");
$xajax->registerFunction("btnManten");
$xajax->registerFunction("getSecuencia");
$xajax->registerFunction("getDocJudicial");
$xajax->registerFunction("guardar");
$xajax->registerFunction("getEntidad");

//$xajax->registerExternalFunction(array("buscarProveedor", "proveedor","buscar"),"");

function getInicia($op,$tipoDespacho){
        global $bd_prov_id,$bd_desp_descripaux,$bd_desp_firma,
                $bd_desp_cargo,$bd_desp_codigo,$bd_desp_entidad_origen,$bd_depe_id;
        
        $objResponse = new xajaxResponse();
        //$objResponse->addAlert($tipoDespacho);
        $oForm = new AddTableForm();
        $oForm->setLabelWidth("20%");
        $oForm->setDataWidth("80%");


        switch ($tipoDespacho){
                case 140://institucional
                    $dependencia=new dependencia_SQLlista();
                    //$dependencia->whereID(getSession("sis_depeid"));
                    $dependencia->whereVarios(getSession("sis_persid"));    
                    $sqlDependencia=$dependencia->getSQL_cbox();
                    //$dependencia->setDatos();


                    $oForm->addField("Dependencia Origen: ",listboxField("Dependencia Origen",$sqlDependencia,"nr_depe_id",$bd_depe_id,"","onChange=\"xajax_getDatosFirma(1,this.value);xajax_getSecuencia(1,document.frm.tr_tiex_id.value,document.frm.Dr_desp_fecha.value,document.frm.___tabl_tipodespacho.value,this.value)\""));
                    //$oForm->addField("Dependencia Origen: ",$dependencia->field("depe_nombre"));
                    
                    //$oForm->addField("Firma: ",$firma);
                    //$oForm->addField("Cargo: ",$cargo);
                    $oForm->addHidden("___tabl_tipodespacho",$tipoDespacho); // clave primaria
                    //$oForm->addHidden("Sr_desp_firma",$firma,"Firma");
                    //$oForm->addHidden("Sx_desp_cargo",$cargo,"Cargo");

                    $oForm->addHtml("<tr><td colspan=2><div id='divDatosFirma'>\n"); //pide datos de afectacion presupuestal
                    $oForm->addHtml(getDatosFirma(2,$bd_depe_id));
                    $oForm->addHtml("</div></td></tr>\n");
                    
                    break;

                case 141://personal
                    $dependencia=new dependencia_SQLlista();
                    $dependencia->whereID(getSession("sis_depeid"));
                    $dependencia->setDatos();

                    $empleado=new clsDatosLaborales_SQLlista();
                    $empleado->whereID(getSession("sis_pdlaid"));
                    $empleado->setDatos();
                    $firma=$empleado->field('empleado');
                    $cargo=$empleado->field('pdla_cargofuncional_ext');
                    $oForm->addField("Dependencia Origen: ",$dependencia->field("depe_nombre"));
                    $oForm->addField("Firma: ",$firma);
                    $oForm->addField("Cargo: ",$cargo);
                    $oForm->addHidden("___tabl_tipodespacho",$tipoDespacho); // clave primaria
                    $oForm->addHidden("Sr_desp_firma",$firma,"Firma");
                    $oForm->addHidden("Sx_desp_cargo",$cargo,"Cargo");
                    $oForm->addHidden("nr_depe_id",$bd_depe_id,"Dependencia Origen");
                    //$objResponse->addClear('divDatosFirma','innerHTML');
                    break;

                case 142://otras entidades

                    ////////////////////////////
                    /* Proveedor */
                    // definición de lookup avanzada
                    ///$prov=new proveedor();
                    ///$provSQL=new proveedor_SQLlista();
                    ///$provSQL->whereID($bd_prov_id);
                    ///$provSQL->setDatos();

                    ///$proveedor= new AvanzLookup();
                    ///$proveedor->setNamePage("../catalogos/catalogosDependenciaExterna_buscar.php?clear=2,busEmpty=0");  //nombre de la página que se cargará, puede agregar parametros pero separados por comas (,)en lugar de '&'
                    ///$proveedor->setNameCampoForm("","tr_ruc_id"); //campo donde se guardara el valor ingresado o buscado

                    ///$paramFunction= new manUrlv1();
                    ///$paramFunction->removeAllPar();
                    ///$paramFunction->addParComplete('colSearch','codigo');
                    ///$paramFunction->addParComplete('colOrden',1);
                    ///$proveedor->addFieldID(textField("Entidad",$proveedor->nameCampoForm,$provSQL->field('prov_codigo'),11,11,"onChange=\"xajax_buscarProveedor(3,this.value,'".encodeArray($paramFunction->getUrl())."',1,'document.frm._Dummy$proveedor->nameCampoForm.value')\"")); //adiciona campo de busqueda (para buscar por id/codigo)//
                    //cambio el nombre de la columna de busqueda
                    ///$paramFunction->removePar('colSearch');
                    ///$paramFunction->addParComplete('colSearch','prov_id');

                    ///$proveedor->setValorCampoForm("",$prov->buscar(3,$bd_prov_id,encodeArray($paramFunction->getUrl())));//metodo de busqueda, se activa en modo edicion

                    ///$proveedor->setSize(70);//ancho del campo texto donde se almacenara el texto retornado
                    ///$proveedor->setWidth(750);//ancho de la ventana
                    ///$proveedor->setHeight(500);// define la altura de la ventana
                    ///$proveedor->setNewWin(true); // Ventana interna
                    //$proveedor->setClassThickbox('thickbox2'); //renombro el css del thickbox para q no haya conflicto con la funcion 'getAfectacion' donde tambien se utilizan
                    ///$oForm->addField("Entidad Origen: ",$proveedor->writeHTML());
                    ///$oForm->addHidden("tr_prov_id",$bd_prov_id,"Entidad");
                    ///////////////////
                    
                    $oForm->addField("C&oacute;digo/DNI: ",textField("C&oacute;digo/DNI","sx_desp_codigo",$bd_desp_codigo,11,11)."&nbsp;<input type=\"button\" onClick=\"xajax_getEntidad(document.frm.sx_desp_codigo.value,document.frm.Sx_desp_entidad_origen.value,document.frm.Sr_desp_firma.value,document.frm.Sx_desp_cargo.value)\" value=\"Buscar\">");

                    //$oForm->addField("Descripci&oacute;n Auxiliar: ",textField("Auxiliar","Sx_desp_descripaux",$bd_desp_descripaux,80,80));
                    $oForm->addField("Entidad Origen: ",textField("Entidad Origen","Sx_desp_entidad_origen",$bd_desp_entidad_origen,80,120));
                    $oForm->addField("Firma: ",textField("Firma","Sr_desp_firma",$bd_desp_firma,80,80));
                    $oForm->addField("Cargo: ",textField("Cargo","Sx_desp_cargo",$bd_desp_cargo,80,80));
                    $oForm->addHidden("___tabl_tipodespacho",$tipoDespacho); // clave primaria
                    $oForm->addHidden("nr_depe_id",$bd_depe_id,"Dependencia Origen");
                    //$objResponse->addClear('divDatosFirma','innerHTML');
                    break;

                default:
                    $objResponse->addAlert('Proceso cancelado, Seleccione opción');
                    $oForm->addHidden("___tabl_tipodespacho",NULL); // clave primaria
                    if($op==1){
                        return $objResponse;
                    }else{
			$oForm->addBreak("!NO SE ENCONTRARON DATOS...!!");
                    }
                    break;
           }
        $contenido_respuesta=$oForm->writeHTML();

        $objResponse->addAssign('divDatosIniciales','innerHTML', $contenido_respuesta);
        $objResponse->addScript("tb_init('a.thickbox')");

        if($op==1){
            $objResponse->script("xajax_getSecuencia(1,document.frm.tr_tiex_id.value,document.frm.Dr_desp_fecha.value,document.frm.___tabl_tipodespacho.value,document.frm.nr_depe_id.value)");
            return $objResponse;
        }
	else{
            return $contenido_respuesta;
        };
}

function getDatosFirma($op,$depe_id){

    $objResponse = new xajaxResponse();
    //$objResponse->addAlert($tipoDespacho);
    $oForm = new AddTableForm();
    $oForm->setLabelWidth("20%");
    $oForm->setDataWidth("80%");

    $dependencia=new dependencia_SQLlista();
    $dependencia->whereID($depe_id);
    $dependencia->setDatos();

    $jefe=new clsDatosLaborales_SQLlista();
    $jefe->whereID($dependencia->field("pdla_id"));
    $jefe->setDatos();
    $firma=$jefe->field('empleado');
    $cargo=$jefe->field('pdla_cargofuncional_ext');
                    
    $oForm->addField("Firma: ",$firma);
    $oForm->addField("Cargo: ",$cargo);
    $oForm->addHidden("Sr_desp_firma",$firma,"Firma");
    $oForm->addHidden("Sx_desp_cargo",$cargo,"Cargo");

    $contenido_respuesta=$oForm->writeHTML();
    $objResponse->addAssign('divDatosFirma','innerHTML', $contenido_respuesta);

    if($op==1){
        return $objResponse;
    }
    else
        return $contenido_respuesta	;
}

function addCarrito($deriva,$concopia,$proveido)
{
    $objResponse = new xajaxResponse();
    
    $usua_idcrea=getSession("sis_userid");

    $nvoArrayDeriva=explode(';',$deriva);
    $nvoArrayConCopia=explode(';',$concopia);

    if(!$nvoArrayDeriva[0] && !$nvoArrayConCopia[0]){
        $objResponse->addAlert('Proceso cancelado, Ingrese dependencia a Derivar ');
        $objResponse->addScript("document.frm.Sx_dependencia.focus()");
	return $objResponse;
    }

    if(!$proveido){
        $objResponse->addAlert('Proceso cancelado, Ingrese Proveido de Atención');
        $objResponse->addScript("document.frm.Sx_desp_proveido.focus()");
	return $objResponse;
    }

    $data= new manUrlv1();
    $data->removeAllPar(0);
    //derivación a dependencias
    if($nvoArrayDeriva[0])
    foreach ($nvoArrayDeriva as $i => $value) {
         $arrayDepeUser=explode('_',$value);
         $idDeriva=$arrayDepeUser[0];
         $depe_id=$arrayDepeUser[0];//dependencia
         $elemento=$value;
         $usua_id='';
         if($arrayDepeUser[1]>0){
             $usua_id=$arrayDepeUser[1];//usuario
             $idDeriva.='_'.$usua_id;
         }

        $data->addParComplete('id', $idDeriva);
        $data->addParComplete('depe_id', $depe_id);
        $data->addParComplete('usua_id', $usua_id);
        $data->addParComplete('elemento',$elemento);
        $data->addParComplete('proveido',$proveido);
        $data->addParComplete('cc','');
        $data->addParComplete('usua_idcrea',$usua_idcrea);
        $data->addParComplete('dede_estado',2);

       $_SESSION["ocarrito"]->Add($data->getUrl());
    }

    $data= new manUrlv1();
    $data->removeAllPar(0);
    //derivación con Copia
    if($nvoArrayConCopia[0])
        foreach ($nvoArrayConCopia as $i => $value) {
             $arrayDepeUser=explode('_',$value);

             $idDeriva=$arrayDepeUser[0];
             $depe_id=$arrayDepeUser[0];//dependencia
             $elemento=$value;
             $usua_id='';
             if($arrayDepeUser[1]>0){
                 $usua_id=$arrayDepeUser[1];//usuario
                 $idDeriva.='_'.$usua_id;
             }

            $data->addParComplete('id', $idDeriva.'C');
            $data->addParComplete('depe_id', $depe_id);
            $data->addParComplete('usua_id', $usua_id);
            $data->addParComplete('elemento',$elemento);
            $data->addParComplete('proveido',$proveido);
            $data->addParComplete('cc','Cc');
            $data->addParComplete('usua_idcrea',$usua_idcrea);
            $data->addParComplete('dede_estado',2);

            $_SESSION["ocarrito"]->Add($data->getUrl());
        }
    $objResponse->addScript("tlist2.removeAllBox()");
    $objResponse->addScript("document.frm.Sx_desp_proveido.value=''");
    $objResponse->addScript("tlist3.removeAllBox()");
    $objResponse->addScript("xajax_verCarrito()");
    return $objResponse;


}

function verCarrito($op=1)
{
	$objResponse = new xajaxResponse();

	$otable = new  Table("","100%",6);
	$otable->setColumnTD("ColumnBlueTD") ;
	$otable->setColumnFont("ColumnWholeFont") ;
	$otable->setFormTotalTD("FormTotalBlueTD");
	$otable->setAlternateBackTD("AlternateBackBlueTD");

	$otable->addBreak("<div align='center' style='color:#000000'><b>:: DERIVACIONES REALIZADAS ::</b></div>");
	$otable->addColumnHeader("El"); // Título, Ordenar?, ancho, alineación
	$otable->addColumnHeader("Ed"); // Título, Ordenar?, ancho, alineación        
	$otable->addColumnHeader("Dependencia/Usuario Destino",false,"45%", "L"); // Título, Ordenar?, ancho, alineación
	$otable->addColumnHeader("Proveido",false,"45%", "L"); // Título, Ordenar?, ancho, alineación
	$otable->addColumnHeader("Dist",false,"5%", "L"); // Título, Ordenar?, ancho, alineación
        $otable->addColumnHeader("Creado Por",false,"5%", "L"); // Título, Ordenar?, ancho, alineación
	$otable->addRow(); // adiciona la linea (TR)

	$array=$_SESSION["ocarrito"]->getArray();
	foreach($array as $arrItem) {
		$items=key($array); /* Para guardar el key del array padre */
                $usua_idcrea=$arrItem['usua_idcrea'];
                $dede_estado=$arrItem['dede_estado'];
                
                //si el registro esta derivado (no recibido) y el usuario que esta editando es el que lo ha creado
                if($dede_estado==2 && $usua_idcrea==getSession("sis_userid")){
                    $otable->addData("<a class=\"link\" href=\"#\" onClick=\"javascript:if(confirm('Eliminar este registro?')) {xajax_elimCarrito($items)}\"><img src=\"../../img/delete.gif\" border=0 align=absmiddle hspace=1 alt=\"Eliminar\"></a>");
                    if($arrItem['cc']){
                        $otable->addData("<a class=\"link\" href=\"#\" onClick=\"javascript:tlist3.add('".$arrItem['elemento']."');document.frm.Sx_desp_proveido.value='".$arrItem['proveido']."';xajax_btnManten(1,2,$items,'divBtnManten')\"><img src=\"../../img/editar.gif\" border=0 align=absmiddle hspace=1 alt=\"Editar\"></a>");
                    }else{
                        $otable->addData("<a class=\"link\" href=\"#\" onClick=\"javascript:tlist2.add('".$arrItem['elemento']."');document.frm.Sx_desp_proveido.value='".$arrItem['proveido']."';xajax_btnManten(1,2,$items,'divBtnManten')\"><img src=\"../../img/editar.gif\" border=0 align=absmiddle hspace=1 alt=\"Editar\"></a>");
                    }
                }
                else{
                    $otable->addData("&nbsp;");
                    $otable->addData("&nbsp;");
                }


		$otable->addData($arrItem['elemento']);
		$otable->addData($arrItem['proveido']);
		$otable->addData($arrItem['cc']);

                $usua_idCrea=$arrItem['usua_idcrea'];
                $nameUsuaCrea=getDbValue("SELECT usua_login FROM usuario WHERE usua_id=$usua_idCrea");

                if($arrItem['tx_hijo_id']){
                    $otable->addData($nameUsuaCrea,"L","","",$arrItem['tx_hijo_id']);
                }
                else{
                    $otable->addData($nameUsuaCrea);
                }
                //$otable->addData($items);

		$otable->addRow();
		next($array); /* voy al siguiente registro del array padre */
	}

	$contenido_respuesta.=$otable->writeHTML();
	$contenido_respuesta.="<div class='BordeatablaBlue' style='width:50%;float:left' align='left'><a name=\"ancDatos0\">&nbsp;</a></div>";
	$contenido_respuesta.="<div class='BordeatablaBlue' style='width:50%;float:right' align='right'>Total Items: ".$_SESSION["ocarrito"]->getConteo()."</div>";

        if ($op==1){
            $objResponse->addScript("document.location='#ancDatos0'");
            $objResponse->addAssign('divDerivacion','innerHTML', $contenido_respuesta);
            return $objResponse;
        }else{
            return $contenido_respuesta;
        }
}

//funcion que elimina un item al carrito
function elimCarrito($id)
{
    $objResponse = new xajaxResponse();
    $_SESSION["ocarrito"]->Del($id);
    //alert($_SESSION["ocarrito"]->getConteo());
    return(verCarrito());
}

function modiCarrito($id,$deriva,$concopia,$proveido)
{
    $objResponse = new xajaxResponse();
    $_SESSION["ocarrito"]->Del($id);
    return (addCarrito($deriva,$concopia,$proveido));
}
//ojo esta funcion no puede ser modificada porq se utiliza en las paginas de edicion
function conCopia()
{
    $objresponse = new xajaxResponse();

    $otable = new AddTableForm();
    $otable->setLabelWidth("20%");
    $otable->setDataWidth("80%");

    $contenido_respuesta=$otable->writeHTML();

    $objresponse->addAssign('ccopia','innerHTML', $contenido_respuesta);

    return $objresponse;
}

function btnManten($op,$insert,$id,$div){
    $objResponse = new xajaxResponse();

    $otable = new AddTableForm();
    $otable->setLabelWidth("20%");
    $otable->setDataWidth("80%");
    $otable->setLabelTD("LabelOrangeTD");
    
    /* botones */
    $button = new Button;
    //javascript:tlist2.add('HOLA');
    if ($insert==1)
        $button->addItem(" Agregar ","xajax_addCarrito(tlist2.bits.getValues().join(';'),tlist3.bits.getValues().join(';'),document.frm.Sx_desp_proveido.value);ocultarObj(' Agregar ',3);","content",2,'','botao','button');
    else
        $button->addItem(" Actualizar ","xajax_modiCarrito('$id',tlist2.bits.getValues().join(';'),tlist3.bits.getValues().join(';'),document.frm.Sx_desp_proveido.value);xajax_btnManten(1,1,0,'$div');","content",2,'','botao','button');

    $button->align('L');
    $otable->addField("",$button->writeHTML());
    $contenido_respuesta=$otable->writeHTML();

    if ($op==1){
        $objResponse->addAssign($div,'innerHTML', $contenido_respuesta);
        return $objResponse;
    }else{
        return $contenido_respuesta;
    }

}

function getSecuencia($op,$tipoExpediente,$fechaDoc,$tipoDespacho,$depe_id){
        global $conn,$id,$bd_desp_numero,$bd_desp_siglas,$num_documento;

        $objResponse = new xajaxResponse();
        //$objResponse->addAlert($tipoExpediente);

        //si no se ha seleccionado tipo de expediente
        if(!$tipoExpediente) {
            if($op==1){
                return $objResponse;
            }else{
                return;
            }
        }

        $oForm = new AddTableForm();
        $oForm->setLabelWidth("20%");
        $oForm->setDataWidth("80%");
        $annoExplode=explode("/",$fechaDoc);
        $anno=$annoExplode[2];
        $td_secuencia='';
        switch ($tipoDespacho){
                case 140://institucional
                    if(!$id){
                        $td=new clsTipExp_SQLlista();
                        $td->whereID($tipoExpediente);
                        $td->setDatos();
                        $td_secuencia=$td->field('tiex_secuencia');

                        $siglas=new dependencia_SQLlista();
                        $siglas->whereID($depe_id);
                        $siglas->setDatos();

                        //si el expediente es tipo resolucion
                        if($td->field('tiex_tiporesolucion')==1){
                            $bd_desp_siglas=$siglas->field('depe_siglasresolucion');
                        }
                        else{
                            $bd_desp_siglas=$siglas->field('depe_siglasdoc');
                        }

                        
                        $secuencia="gestdoc.corr_exp_".$td_secuencia.'_'.$anno.'_'.$depe_id.'_'.$tipoExpediente;

                        $bd_desp_numero=$conn->currval($secuencia);

                        if($bd_desp_numero==0){ /* Si la secuencia no está creada */
                            $bd_desp_numero=1; /* Asigno el número 1 */
                        }
                        $bd_desp_numero=str_pad($bd_desp_numero,6,'0',STR_PAD_LEFT);
                        
                        $num_documento="$bd_desp_numero-$anno-$bd_desp_siglas";
                    }
                    
                    
                    $oForm->addField("N&uacute;mero-A&ntilde;o-Siglas: ","<font size=\"-1\">$num_documento</font>");
                    $oForm->addHidden('nx_desp_numero',$bd_desp_numero,"N&uacute;mero");
                    $oForm->addHidden('nx_anno_nume_doc',$anno);
                    $oForm->addHidden('Sx_desp_siglas',$bd_desp_siglas,"Siglas");
                    $oForm->addHidden('___td_secuencia',$td_secuencia);
                    break;

                case 141://personal
                    if(!$id){
                        $siglas=new dependencia_SQLlista();
                        $siglas->whereID(getSession("sis_depeid"));
                        $siglas->setDatos();
                        $bd_desp_siglas=$siglas->field('depe_siglasdoc');

                        $siglas=new clsUsers_SQLlista();
                        $siglas->whereID(getSession("sis_userid"));
                        $siglas->setDatos();
                        $siglasPers=$siglas->field('usua_iniciales');

                        $td=new clsTipExp_SQLlista();
                        $td->whereID($tipoExpediente);
                        $td->setDatos();
                        $td_secuencia=$td->field('tiex_secuencia');

                        $secuencia="gestdoc.corr_exp_".$td_secuencia.'_'.$anno.'_'.$tipoExpediente.'_'.getSession("sis_userid");

                        //$objResponse->addAlert($secuencia);

                        $bd_desp_numero=$conn->currval($secuencia);

                        if($bd_desp_numero==0){ /* Si la secuencia no está creada */
                            $bd_desp_numero=1; /* Asigno el número 1 */
                        }
                        $bd_desp_numero=str_pad($bd_desp_numero,6,'0',STR_PAD_LEFT);
                        $bd_desp_siglas=$bd_desp_siglas.'-'.$siglasPers;
                        $num_documento="$bd_desp_numero-$anno-$bd_desp_siglas";
                    }

                    $oForm->addField("N&uacute;mero-A&ntilde;o-Siglas: ","<font size=\"-1\">$num_documento</font>");
                    $oForm->addHidden('nx_desp_numero',$bd_desp_numero,"N&uacute;mero");
                    $oForm->addHidden('nx_anno_nume_doc',$anno);
                    $oForm->addHidden('Sx_desp_siglas',$bd_desp_siglas,"Siglas");
                    $oForm->addHidden('___td_secuencia',$td_secuencia);
                    break;

                case 142://otras entidades
                    $oForm->addField("N&uacute;mero-A&ntilde;o-Siglas: ",numField("N&uacute;mero","nx_desp_numero",$bd_desp_numero,6,6,0)
                            ."<font size=\"-1\">-$anno-</font>"
                            .textField("Siglas","Sx_desp_siglas",$bd_desp_siglas,30,30)
                            );
                    $oForm->addHidden('nx_anno_nume_doc',$anno);//guardo el numero para q funcione en las actualizaciones
                    break;


                default:
                    if($op==1){
                        $objResponse->addAlert('Proceso cancelado, Seleccione opción'.$tipoDespacho);
                        return $objResponse;
                    }else{
			$oForm->addBreak("!NO SE ENCONTRARON DATOS...!!");
                    }

                    break;
           }



        $contenido_respuesta=$oForm->writeHTML();
	$objResponse->addAssign('divNumeroDoc','innerHTML', $contenido_respuesta);

        if($op==1)
            return $objResponse;
	else
            return $contenido_respuesta;
}

function getEntidad($codigo,$entidad_origen,$firma,$cargo)
{
    global $conn;
    $objResponse = new xajaxResponse();
    $sql="SELECT desp_entidad_origen,desp_firma,desp_cargo
                FROM despachos
                WHERE desp_codigo='$codigo'
                ORDER BY desp_id DESC
                LIMIT 1 ";

     $rs = new query($conn, $sql);
     if($rs->numrows()>0){
            //$objResponse->addAlert($sql);
            $rs->getrow();
            $desp_entidad_origen=$rs->field('desp_entidad_origen');
            $desp_firma=$rs->field('desp_firma');
            $desp_cargo=$rs->field('desp_cargo');

            $objResponse->addScript("document.frm.Sx_desp_entidad_origen.value='".$desp_entidad_origen."'");
            $objResponse->addScript("document.frm.Sr_desp_firma.value='".$desp_firma."'");
            $objResponse->addScript("document.frm.Sx_desp_cargo.value='".$desp_cargo."'");
     }

    //$objResponse->addAlert($codigo);

    return $objResponse;
}

function getDocJudicial($op,$tipoExpediente,$cuenta_recibidos,$divName){
        global $conn,$bd_desp_exp_legal,$bd_desp_demandante,$bd_desp_demandado,$bd_desp_resolucion,$bd_exle_id;

        $objResponse = new xajaxResponse();
        //$objResponse->addAlert($tipoExpediente);

        //si no se ha seleccionado tipo de expediente
        if(!$tipoExpediente) {
            if($op==1){
                return $objResponse;
            }else{
                return;
            }
        }

        $oForm = new AddTableForm();
        $oForm->setLabelWidth("20%");
        $oForm->setDataWidth("80%");

        $td=new clsTipExp_SQLlista();
        $td->whereID($tipoExpediente);
        $td->setDatos();
        if($td->field('tiex_tipojudicial')==1){
            $oForm->addBreak("<b>Datos Judiciales</b>");    
//            $sqlLegal="SELECT a.exle_id,
//                        LPAD(a.exle_numero::TEXT,6,'0')||'-'||LPAD(exle_anno::TEXT,4,'0')||COALESCE('-'||exle_siglas,'') AS exle_expediente 
//                        FROM gestleg.expediente_legal a
//                    ORDER BY exle_anno DESC,exle_numero ";
//            $oForm->addField("Expediente Legal:",listboxField("Expediente Legal",$sqlLegal,"tx_exle_id",$bd_exle_id,"-- Seleccione Expediente --"));
            
            if($cuenta_recibidos>0){
                $oForm->addField("N&ordm; Exp.Legal: ",$bd_desp_exp_legal);
                $oForm->addField("Demandante: ",$bd_desp_demandante);
                $oForm->addField("Demandado: ",$bd_desp_demandado);
                $oForm->addField("Resoluci&oacute;n ",$bd_desp_resolucion);
            }else{
                $oForm->addField("N&ordm; Exp.Legal: ",textField("No Exp.Legal","Sx_desp_exp_legal",$bd_desp_exp_legal,50,50));
                $oForm->addField("Demandante: ",textField("Demandante","Sx_desp_demandante",$bd_desp_demandante,80,80));
                $oForm->addField("Demandado: ",textField("Demandado","Sx_desp_demandado",$bd_desp_demandado,80,80));
                $oForm->addField("Resoluci&oacute;n ",textField("Resolución","Sx_desp_resolucion",$bd_desp_resolucion,80,80));
            }
        }

        $contenido_respuesta=$oForm->writeHTML();
	$objResponse->addAssign($divName,'innerHTML', $contenido_respuesta);

        if($op==1)
            return $objResponse;
	else
            return $contenido_respuesta;
}

function guardar($formdata,$regSeleccionadosExp,$regSeleccionadosId)
{
	global $conn,$param;

	$objResponse = new xajaxResponse();
	$objResponse->setCharEncoding('utf-8');

	$usua_id=getSession("sis_userid"); /* Id del usuario que graba el registro */
        $pers_id=getSession("sis_persid"); /* Id del usuario que graba el registro */
        //$depe_id=getSession("sis_depeid");

        //asigno la variable de edición
        $edita=$formdata['f_id'];
        $padre_id=$edita; //variable creada solo para el update;

	/* Recibo campos */
        $depe_id=$formdata['nr_depe_id'];
	$tabl_tipodespacho=$formdata['___tabl_tipodespacho'];		/*campo id de la tabla en caso de modificacion*/
        $desp_fecha=$formdata['Dr_desp_fecha'];
        $tiex_id=$formdata['tr_tiex_id'];
        $desp_asunto=$formdata['Er_desp_asunto'];
        $desp_firma=$formdata['Sr_desp_firma'];
        $desp_cargo=$formdata['Sx_desp_cargo'];
        $tabl_modorecepcion=$formdata['tr_tabl_modorecepcion'];
        $desp_folios=$formdata['nr_desp_folios'];
        $desp_proyectadopor=$formdata['Sx_desp_proyectadopor'];
        //$desp_trelacionado=$formdata['nx_desp_trelacionado'];
        $desp_expediente=$formdata['nx_desp_expediente'];
        $desp_notas=$formdata['Sx_desp_notas'];

        $bd_exle_id=$formdata['tx_exle_id'];
        $desp_exp_legal=$formdata['Sx_desp_exp_legal'];
        $desp_demandante=$formdata['Sx_desp_demandante'];
        $desp_demandado=$formdata['Sx_desp_demandado'];
        $desp_resolucion=$formdata['Sx_desp_resolucion'];
        $si_adjuntar=$formdata['hx_asjuntar'];
        
        $prat_id=$formdata['tx_prat_id'];
        //$prov_id=$formdata['tr_prov_id'];
        //$prov_id=$prov_id?$prov_id:null;

        $annoExplode=explode("/",$desp_fecha);
        $anno=$annoExplode[2];
        $td_secuencia=$formdata['___td_secuencia'];

        switch($tabl_tipodespacho){
            case 140://institucional
                if($edita) break;

                $secuencia="gestdoc.corr_exp_".$td_secuencia.'_'.$anno.'_'.$depe_id.'_'.$tiex_id;
                $numDocum=$conn->currval($secuencia);
                if($numDocum==0){ /* Si la secuencia no está creada */
                    $conn->nextid($secuencia); /* Creo la secuencia */
                    $numDocum=1; /* Asigno el número 1 */
                }
                $desp_numero=$numDocum;

                $desp_anno=$formdata['nx_anno_nume_doc'];
                $desp_siglas=$formdata['Sx_desp_siglas'];
                break;

            case 141://personal
                if($edita) break;

                $secuencia="gestdoc.corr_exp_".$td_secuencia.'_'.$anno.'_'.$tiex_id.'_'.$usua_id;
                //$objResponse->addAlert($secuencia);

                $numDocum=$conn->currval($secuencia);
                if($numDocum==0){ /* Si la secuencia no está creada */
                    $conn->nextid($secuencia); /* Creo la secuencia */
                    $numDocum=1; /* Asigno el número 1 */
                }
                $desp_numero=$numDocum;
                
                $desp_anno=$formdata['nx_anno_nume_doc'];
                $desp_siglas=$formdata['Sx_desp_siglas'];
                break;

            case 142://otras entidades
                //$desp_descripaux=$formdata['Sx_desp_descripaux'];
                $desp_codigo=$formdata['sx_desp_codigo'];
                $desp_entidad_origen=$formdata['Sx_desp_entidad_origen'];

                $desp_numero=$formdata['nx_desp_numero'];
                $desp_anno=$formdata['nx_anno_nume_doc'];
                $desp_siglas=$formdata['Sx_desp_siglas'];
                break;
        }

	/********* INICIO PROCESO DE GRABACION EN EL PADRE *********/
	$conn->begin();

	// objeto para instanciar la clase sql
	$setTable='despachos';
	$setKey='desp_id';
	$typeKey='Number';

	$sql = new UpdateSQL();

	$sql->setTable($setTable);
	$sql->setKey($setKey,$padre_id,$typeKey);
        if(!$edita){
            $sql->setAction("INSERT"); /* Operación */
            $sql->addField('depe_id',$depe_id, "Number");
            $sql->addField('usua_id',$usua_id, "Number");
            $sql->addField('tiex_id',$tiex_id, "Number");
            $sql->addField('desp_numero',$desp_numero, "Number");
            $sql->addField('desp_anno',$desp_anno, "Number");
            $sql->addField('desp_siglas',$desp_siglas, "String");
        }else{
            $sql->setAction("UPDATE"); /* Operación */
            $sql->addField("desp_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
            $sql->addField("desp_actualusua", getSession("sis_userid"), "String");

            if($tabl_tipodespacho==142){ //OTRAS ENTIDADES
                $sql->addField('tiex_id',$tiex_id, "Number");
                $sql->addField('desp_numero',$desp_numero, "Number");
                $sql->addField('desp_anno',$desp_anno, "Number");
                $sql->addField('desp_siglas',$desp_siglas, "String");
            }
        }
        
        $sql->addField('tabl_tipodespacho',$tabl_tipodespacho, "Number");
        $sql->addField('desp_fecha',$desp_fecha, "String");

        $sql->addField('desp_asunto',$desp_asunto, "String");
        $sql->addField('desp_firma',$desp_firma, "String");
        $sql->addField('desp_cargo',$desp_cargo, "String");
        $sql->addField('tabl_modorecepcion',$tabl_modorecepcion, "Number");
        $sql->addField('desp_folios',$desp_folios, "Number");
        
        $sql->addField('desp_proyectadopor',$desp_proyectadopor, "String");
        //$sql->addField('desp_trelacionado',$desp_trelacionado, "Number");
        $sql->addField('desp_expediente',$desp_expediente, "Number");
        $sql->addField('desp_notas',$desp_notas, "String");

        if($si_adjuntar==1 && $regSeleccionadosExp){
            $sql->addField('desp_adjuntados_exp',$regSeleccionadosExp, "String"); 
            $sql->addField('desp_adjuntados_id' ,$regSeleccionadosId, "String"); 
            $sql->addField('desp_adjuntados', 1 , "Number");
        }else{
            $sql->addField('desp_adjuntados_exp',null, "String"); 
            $sql->addField('desp_adjuntados_id' ,null, "String"); 
            $sql->addField('desp_adjuntados', 0 , "Number");
        }
        
        $sql->addField('exle_id',$bd_exle_id, "Number");
        
        $sql->addField('desp_exp_legal',$desp_exp_legal, "String");
        $sql->addField('desp_demandante',$desp_demandante, "String");
        $sql->addField('desp_demandado',$desp_demandado, "String");
        $sql->addField('desp_resolucion',$desp_resolucion, "String");
        
        $sql->addField('prat_id',$prat_id, "Number");

        //$sql->addField('prov_id',$prov_id, "Number");
        //$sql->addField('desp_descripaux',$desp_descripaux,"String");

        if($tabl_tipodespacho==142){ //OTRAS ENTIDADES
            $sql->addField('desp_codigo',$desp_codigo, "String");
            $sql->addField('desp_entidad_origen',$desp_entidad_origen, "String");
        }

	$sql=$sql->getSQL()." RETURNING desp_id::text||'_'||TO_CHAR(desp_fregistro,'DD/MM/YYYY HH:MI:SS AM')";

	$sql= strtoupper($sql);
	$return=$conn->execute($sql); //obtengo el id del registro generado
        $nvoReturn=explode('_',$return);

        $padre_id=$nvoReturn[0];
        $ff_hh=$nvoReturn[1];

        //$objResponse->addAlert($padre_id);

	$error=$conn->error();
	if($error){
		 $conn-> rollback();
		 $objResponse->addAlert($error);
	 }
	/********* FIN PROCESO DE GRABACION EN EL PADRE *********/
	else{

		/********* INICIO PROCESO DE GRABACION EN EL HIJO *********/
                $nvasDerivaciones='';
		$array=$_SESSION["ocarrito"]->getArray();
		foreach($array as $arrItem){
                        //$objResponse->addAlert($arrItem['tx_hijo_id']);
			if($arrItem['tx_hijo_id']) continue; /* Si es Edición, regresa y no ejecuta el update  */

                        $depe_iddestino=$arrItem['depe_id'];
                        $usua_iddestino=$arrItem['usua_id'];
                        $dede_concopia=$arrItem['cc']=='Cc'?1:0;
                        $dede_proveido=$arrItem['proveido'];

                        $depe_idorigen=$depe_id; //getSession("sis_depeid");
                        $usua_idorigen=getSession("sis_userid");
                        $usua_idcrea=getSession("sis_userid"); /* Id del usuario que graba el registro */

			// objeto para instanciar la clase sql
			$setTable='despachos_derivaciones';
			$setKey='dede_id';
			$typeKey='Number';
                        $sql = new UpdateSQL();
                        $sql->setTable($setTable);
                        $sql->setKey($setKey,$hijo_id,$typeKey);
                        $sql->setAction("INSERT"); /* Operación */

                        /* Campos */
                        $sql->addField('depe_idorigen', $depe_idorigen, "Number");
                        $sql->addField('usua_idorigen', $usua_idorigen, "Number");

                        $sql->addField('desp_id', $padre_id, "Number");

                        $sql->addField('depe_iddestino', $depe_iddestino, "Number");
                        $sql->addField('usua_iddestino', $usua_iddestino, "Number");
                        $sql->addField('dede_concopia', $dede_concopia, "Number");
                        $sql->addField('dede_proveido', $dede_proveido, "String");

                        $sql->addField('usua_idcrea', $usua_idcrea, "Number");

                        $sql=$sql->getSQL()." RETURNING dede_id::text ";
                        $return=$conn->execute($sql); //obtengo el id del registro generado

                        $error=$conn->error();
                        if($error){
                                $conn-> rollback();
                                $objResponse->addAlert($error);
                                break;
                         }
		}
		/********* FIN PROCESO DE GRABACION EN EL HIJO *********/
                if(!$error){
                        if($regSeleccionadosId){
                            if($si_adjuntar==1){
                                $sql="UPDATE despachos_derivaciones
                                        SET desp_adjuntadoid=$padre_id
                                        WHERE desp_id!=$padre_id 
                                            AND dede_id IN ($regSeleccionadosId) ";
                            }else{
                                $sql="UPDATE despachos_derivaciones
                                            SET desp_adjuntadoid=NULL
                                            WHERE desp_adjuntadoid=$padre_id; ";
                            }
                            //alert($sql);
                            $conn->execute($sql);
                            $error=$conn->error();
                            if($error){
                                $conn-> rollback();
                                $objResponse->addAlert($error);
                            }
                        }
                        if(!$error){
                        /********* FIN ACTUALIZA LOS NUEVOS REGISTROS DE DERIVACIONES GENERADOS *********/
                            
                                /*-------- INICIO PROCESO DE ELIMINACION EN EL HIJO -------*/
                                $arLista_elimina=$_SESSION["ocarrito"]->getArrayEliminados();
                                if (is_array($arLista_elimina)) { /* Si existe array de registros eliminados */
                                         $lista_elimina = implode(",",$arLista_elimina);

                                        if(strtolower($typeKey)=='string'){
                                                /* debido a que el campo clave es char */
                                            $lista_elimina=ereg_replace(",","','",$lista_elimina);
                                        }

                                        $setTable='despachos_derivaciones';
                                        $setKey='dede_id';
                                        $typeKey='Number';

                                        /* Sql a ejecutar */
                                        $sql="DELETE FROM $setTable ";
                                        $sql.=" WHERE $setKey ";
                                        $sql.="     IN (".iif(strtolower($typeKey),"==","string","'","").$lista_elimina.iif(strtolower($typeKey),"==","string","'","").") ";
                                        $sql.="     AND usua_idcrea=".getSession("sis_userid");

                                        //$objResponse->addAlert($sql);
                                        $conn->execute($sql);
                                        $error=$conn->error();
                                        if($error){
                                                 $conn-> rollback();
                                                 $objResponse->addAlert($error);
                                         }
                                }
                                /*----------- FIN PROCESO DE ELIMINACION EN EL HIJO -----------*/
                         }
		}
	}

	if($error){
		return $objResponse;
	}else{
            $conn->commit(); /* termino transacción */
            if(!$edita){
                $conn->setval($secuencia,intval($desp_numero)+1); /* se suma 1 a la secuencia del documento */
                $destino="mensaje.php?id=".$padre_id.'&ffhh='.$ff_hh;
            }else{
                $destino='registroDespacho_buscar.php?clear=1&busEmpty=1';
            }
	}
        //$objResponse->addAlert('Nuevo Despacho:'.str_pad($desp_numero,8,'0',STR_PAD_LEFT));
	$objResponse->addRedirect($destino);
	return $objResponse;
}

$xajax->processRequests();
// fin para Ajax

?>
<html>
<head>
    <title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">

    <script language="JavaScript" src="../../library/js/focus.js"></script>
    <script language="JavaScript" src="../../library/js/libjsgen.js"></script>
    <script language="JavaScript" src="../../library/js/textcounter.js"></script>

        <script type="text/javascript" src="../../library/jquery/jquery-1.9.js"></script>
        <script type="text/javascript" src="../../library/jquery/jquery-ui.js"></script>
        <link rel="stylesheet" href="../../library/jquery-chosen/chosen.css">
        <script src="../../library/jquery-chosen/chosen.jquery.js" type="text/javascript"></script>                        

    
	<script language='JavaScript'>

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
		función que define el foco inicial en el formulario
	*/
	function inicializa() {
		document.frm.Dr_desp_fecha.focus();
	}
	</script>
	<?php echo  verif_framework();
   	$calendar->load_files();
        $xajax->printJavascript(PATH_INC.'ajax/');
        ?>


</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
if(!strlen($id))
    pageTitle("Nuevo ".$myClass->getTitle());
else
    pageTitle("Edici&oacute;n de ".$myClass->getTitle());

//APLICA FILTROS
//controla las estisticas
$EstadDespDepen=new getAcumDespachosDependencia();
$EstadDespDepen->whereDepeID($bd_depe_id);
$EstadDespDepen->setDatos();
$depe_acum_despachos_porrecibir=$EstadDespDepen->field("depe_acum_despachos_porrecibir");
$depe_max_x_recibir=$EstadDespDepen->field("depe_max_x_recibir");

$depe_acum_despachos_enproceso=$EstadDespDepen->field("depe_acum_despachos_enproceso");
$depe_max_doc_proceso=$EstadDespDepen->field("depe_max_doc_proceso");
$depe_max_dias_doc_proceso=$EstadDespDepen->field("depe_max_dias_doc_proceso");

if($depe_acum_despachos_porrecibir>$depe_max_x_recibir){
    $titulo="IMPOSIBLE CONTINUAR...!!!";
    $msj=getSession("sis_depename"). " tiene $depe_acum_despachos_porrecibir Documento por Recibir <br>Supera el Maximo Permitido ($depe_max_x_recibir)";
    $destino="mensajeArgumento.php?titulo=$titulo&msj=$msj";
    redirect($destino);
}

if($depe_acum_despachos_enproceso>$depe_max_doc_proceso){
    $titulo="IMPOSIBLE CONTINUAR...!!!";
    $msj=getSession("sis_depename"). " tiene $depe_acum_despachos_enproceso Documento en Proceso <br>Supera el Maximo Permitido ($depe_max_doc_proceso)";
    $destino="mensajeArgumento.php?titulo=$titulo&msj=$msj";
    redirect($destino);
}

//APLICA EL FILTRO SOLO SI EL VALOR HA SIDO CAMBIADO
if($depe_max_dias_doc_proceso<999999){
    $EstadMaxDiasenProceso=new getAcumDespachosMaxDiasProceso($bd_depe_id,$depe_max_dias_doc_proceso);
    $EstadMaxDiasenProceso->setDatos();
    $acum_max_dias_proceso=$EstadMaxDiasenProceso->field("acum_max_dias_proceso");
    if($acum_max_dias_proceso>0){
        $titulo="IMPOSIBLE CONTINUAR...!!!";
        $msj=getSession("sis_depename"). " tiene $acum_max_dias_proceso Documento(s) en Proceso de Muchos dias<br>Supera el Maximo de dias Permitido ($depe_max_dias_doc_proceso)";
        $destino="mensajeArgumento.php?titulo=$titulo&msj=$msj";
        redirect($destino);    
    }
}
//FIN DE FILTROS
/*si todos los derivados estan archivados entonces el registro no debe modicarse*/
if(($cuenta_archivados!=$cuenta_derivaciones) or $cuenta_derivaciones==0 or !$id){
    /* botones */
    $button = new Button;
    $button->addItem(" Guardar ","javascript:if(ObligaCampos(frm)){ocultarObj('Guardar',10);xajax_guardar(xajax.getFormValues('frm'),'$regSeleccionadosExp','$regSeleccionadosId')}",'content',2,getSession("sis_userid"));
    $button->addItem(" Deshacer ","javascript:if(confirm('\u00BFSeguro de Deshacer el Registro?')){
                                        document.location='registroDespacho_buscar.php?clear=1'}","content");

    echo $button->writeHTML();
}else{
    echo "<br>";
}
/* Control de fichas 
$abas = new Abas();
$abas->addItem("General",true);
echo $abas->writeHTML();
echo "<br>";
*/

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("f_id",$id); // clave primaria

if (strlen($id)>0) { // edición
    $form->addField("Tipo de ".NAME_EXPEDIENTE.": ",$tipo_despacho);
}
else{
    $desp_tipo=new clsTabla_SQLlista();
    $desp_tipo->whereTipo('TIPO_DESPACHO');
    $desp_tipo->orderUno();
    $rs = new query($conn, $desp_tipo->getSQL());

    $lista_nivel='';
    //$bd_tabl_tipodespacho=0;
    while ($rs->getrow()) {
        //$bd_tabl_tipodespacho=$bd_tabl_tipodespacho?$bd_tabl_tipodespacho:$rs->field("tabl_id");
        $lista_nivel[].=$rs->field("tabl_id").",".$rs->field("tabl_descripcion");
    }

    $form->addField("Tipo de ".NAME_EXPEDIENTE.": ",radioField("Tipo de ".NAME_EXPEDIENTE,$lista_nivel, "xxtipo_despacho",$bd_tabl_tipodespacho,"onChange=\"xajax_getInicia(1,this.value)\"","H"));
}

$form->addHtml("<tr><td colspan=2><div id='divDatosIniciales'>\n"); //pide datos de afectacion presupuestal
$form->addHtml(getInicia(2,$bd_tabl_tipodespacho));
$form->addHtml("</div></td></tr>\n");


if (strlen($id)>0 && $bd_tabl_tipodespacho!=142) { // edición
    $form->addField("Tipo de Documento",$tiex_descripcion);
    $form->addHidden("tr_tiex_id",$bd_tiex_id);
}else{
    $texp=new clsTipExp_SQLlista();
    $texp->whereNOAbreviado("'SB','SS','SSM'");
    $texp->orderUno();
    $sqltipo=$texp->getSQL_cbox2();
    $form->addField("Tipo de Documento:",listboxField("Tipo de Documento",$sqltipo,"tr_tiex_id",$bd_tiex_id,"-- Seleccione Tipo de Documento --","onChange=\"xajax_getSecuencia(1,this.value,document.frm.Dr_desp_fecha.value,document.frm.___tabl_tipodespacho.value,document.frm.nr_depe_id.value);xajax_getDocJudicial(1,this.value,'$cuenta_recibidos','divDatosJudiciales')\"","","class=\"my_select_box\""));        
}

if($cuenta_recibidos>0){
    $form->addField("Fecha de Documento: ", $bd_desp_fecha);
    $readonly="readonly";
}
else{    
    $form->addField("Fecha de Documento: ", $calendar->make_input_field('Fecha de Documento',array(),array('name'=> 'Dr_desp_fecha','value'=> $bd_desp_fecha,'onChange'=>"xajax_getSecuencia(1,document.frm.tr_tiex_id.value,this.value,document.frm.___tabl_tipodespacho.value,document.frm.nr_depe_id.value)")));
    $readonly='';
}

$form->addHtml("<tr><td colspan=2><div id='divNumeroDoc' >\n"); //muestra numero de documento
$form->addHtml(getSecuencia(2,$bd_tiex_id,$bd_desp_fecha,$bd_tabl_tipodespacho,$bd_depe_id));
$form->addHtml("</div></td></tr>\n");

if($regSeleccionadosExp){
    $form->addField("<font color=red>Registros Adjuntados:</font>",checkboxField("Adjuntar a este documento","hx_asjuntar",1,1)."&nbsp;<font color=red><b>".$regSeleccionadosExp."</b></font>");
}

$sqlDependencia=new dependenciaJefe_SQLlista();
$sqlDependencia->orderUno();
$sqlDependencia->whereHabilitado();
$sqlDependencia=$sqlDependencia->getSQL_cbox();
$form->addField("Para: ",listboxField("Para",$sqlDependencia,"nrxpara","","seleccione Destino","","","class=\"my_select_box\" multiple style=\"width:500px;\""));        

$sqlDependencia=new dependencia_SQLlista();
$sqlDependencia->whereHabilitado();
$sqlDependencia->orderUno();
$sqlDependencia=$sqlDependencia->getSQL_cbox();
$form->addField("Cc: ",listboxField("Cc",$sqlDependencia,"nxxconc","","seleccione Con Copia","","","class=\"my_select_box\" multiple style=\"width:500px;\""));        


$form->addField("Asunto: ",textAreaField("Asunto","Er_desp_asunto",$bd_desp_asunto,3,80,300,$readonly));

if (strlen($id)>0){
    $form->addField("<font colore=Red>Referencia (Expediente): </font>","<b>$bd_desp_expediente</b>");
    $form->addHidden("nx_desp_expediente",$bd_desp_expediente);
}
else{
    $form->addField(iif($bd_desp_expediente,'!=','','<font color=Red>','')."Referencia (Expediente):".iif($bd_desp_expediente,'!=','','</font>',''),numField("Referencia (Expediente)","nx_desp_expediente",$bd_desp_expediente,8,8,0));
}

/* Editor html */
$sBasePath = '../../library/fckeditor/' ;
$oFCKeditor = new FCKeditor('K__poco_contenido') ;
$oFCKeditor->BasePath	= $sBasePath ;
$oFCKeditor->Config['SkinPath'] ='/sisadmin/intranet/library/fckeditor/editor/skins/office2003/' ;
$oFCKeditor->Height = '600' ;
$oFCKeditor->Value = $bd_poco_contenido ;
$form->addHtml("<tr><td colspan=2>");
$form->addHtml($oFCKeditor->Create());
$form->addHtml("</td></tr>\n");

$form->addBreak("<b>ADJUNTAR ARCHIVO: <font color=red>(Utilice como nombre de archivo: TipoDocumento_numero_siglas)</font></b>");
$form->addHidden("postPath",'exp_legales/');

$form->addField("Adjunto:",fileField("Adjunto","exap_adjunto" ,"$bd_exap_adjunto",60,"onchange=validaextension(this,'GIF,JPG,PNG,DOC,DOCX,XLS,XLSX,PPT,PPTX,ODT,ODS,ODP,PDF')"));



echo $form->writeHTML();

if(($cuenta_archivados!=$cuenta_derivaciones) or $cuenta_derivaciones==0 or !$id){
    $button = new Button;
    $button->addItem(" :: Guardar :: ","javascript:if(ObligaCampos(frm)){ocultarObj(':: Guardar ::',10);xajax_guardar(xajax.getFormValues('frm'),'$regSeleccionadosExp','$regSeleccionadosId')}",'content',2,getSession("sis_userid"));
    echo $button->writeHTML();
}else{
    echo "<br>";
}
    echo "<br>";
    echo "<br>";
?>
    <script>
        $('.my_select_box').chosen({
            disable_search_threshold: 5, //SE DESHABILITA SI SOLO HAY 5 REGISTROS
            allow_single_deselect: true,
            search_contains: true,
            no_results_text: 'Oops, No Encontrado!'
            });

        $(".my_select_box").val([<?php echo $bd_exle_materia?>]).trigger("chosen:updated");

    </script>
    
    
</body>
</html>

<?php
/* cierro la conexión a la BD */
$conn->close();

unset($_SESSION["ocarrito"]);

//wait('');