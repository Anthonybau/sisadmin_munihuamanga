<?php
/* formulario de ingreso y modificación */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("registrosDespacho_class.php");
include("../catalogos/catalogosDependencias_class.php");
include("../catalogos/catalogosTabla_class.php");
include("../catalogos/catalogosDependenciaExterna_class.php");
include("../catalogos/catalogosPrioridadAtencion_class.php");
include("../catalogos/catalogosTipoExpediente_class.php");
include("../personal/personalDatosLaborales_class.php");
include("../admin/adminUsuario_class.php");

class carDeriva_class {
    function __construct(){
       $this->cuenta=0;
       $this->carrito=array();
       $this->id=array();
       $this->eliminados=array();
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
        $this->eliminados[]=$this->id[$id];

        unset($this->id[$id]);
        unset($this->carrito[$id]);	/* Elimino el registro del array */
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

/* Archivos para calendario */
require_once ('../../library/calendario/calendar.php');
$calendar = new DHTML_Calendar('../../library/calendario/', 'es', 'skins/aqua/theme', false);

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista

$myClass = new despacho($id,'Despacho de Documentos');

if (!isset($_SESSION["ocarrito"])){
    $_SESSION["ocarrito"] = new carDeriva_class();
}

if (strlen($id)>0) { // edición
	$myClass->setDatos();
	if($myClass->existeDatos()){
		$bd_arch_id= $myClass->field('arch_id');
		$bd_arch_anno= $myClass->field('arch_anno');
		$bd_arch_descripcion= $myClass->field('arch_descripcion');
		$bd_depe_id= $myClass->field('depe_id');
                $bd_arch_personal= $myClass->field('arch_personal');
		$bd_usua_id	= $myClass->field('usua_id');
		$username= $myClass->field("username");
		$usernameactual= $myClass->field("usernameactual");		
	}
}
else{
    $bd_depe_id=getSession("sis_depeid");
    $bd_desp_fecha=date('d/m/Y');
    $bd_tabl_formarecepcion=145; //DIRECTA
    $bd_tiex_id=1;
}

// Inicio Para Ajax
require_once("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->setCharEncoding('utf-8');

$xajax->registerFunction("getInicia");
$xajax->registerFunction("addCarrito");
$xajax->registerFunction("elimCarrito");
$xajax->registerFunction("verCarrito");
$xajax->registerFunction("conCopia");
$xajax->registerFunction("getSecuencia");
$xajax->registerFunction("guardar");
$xajax->registerExternalFunction(array("buscarProveedor", "proveedor","buscar"),"");

function getInicia($op,$tipoDespacho){

        $objResponse = new xajaxResponse();
        //$objResponse->addAlert($tipoDespacho);
        $oForm = new AddTableForm();
        $oForm->setLabelWidth("20%");
        $oForm->setDataWidth("80%");


        switch ($tipoDespacho){
                case 140://institucional
                    $dependencia=new dependencia_SQLlista();
                    $dependencia->whereID(getSession("sis_depeid"));
                    $dependencia->setDatos();

                    $jefe=new clsDatosLaborales_SQLlista();
                    $jefe->whereID($dependencia->field("pdla_id"));
                    $jefe->setDatos();
                    $firma=$jefe->field('empleado');
                    $cargo=$jefe->field('pdla_cargofuncional_ext');
                    $oForm->addField("Dependencia: ",$dependencia->field("depe_nombre"));
                    $oForm->addField("Firma: ",$firma);
                    $oForm->addField("Cargo: ",$cargo);
                    $oForm->addHidden("___tabl_tipo_despacho",$tipoDespacho); // clave primaria
                    $oForm->addHidden("Sr_desp_firma",$firma,"Firma");
                    $oForm->addHidden("Sr_desp_cargo",$cargo,"Cargo");

                    break;

                case 141://personal
                    $empleado=new clsDatosLaborales_SQLlista();
                    $empleado->whereID(getSession("sis_pdlaid"));
                    $empleado->setDatos();
                    $firma=$empleado->field('empleado');
                    $cargo=$empleado->field('pdla_cargofuncional_ext');
                    $oForm->addField("Firma: ",$firma);
                    $oForm->addField("Cargo: ",$cargo);
                    $oForm->addHidden("___tabl_tipo_despacho",$tipoDespacho); // clave primaria
                    $oForm->addHidden("Sr_desp_firma",$firma,"Firma");
                    $oForm->addHidden("Sr_desp_cargo",$cargo,"Cargo");

                    break;

                case 142://otras entidades

                    ////////////////////////////
                    /* Proveedor */
                    // definición de lookup avanzada
                    //$prov=new proveedor();

                    //$tr_prov_id=$formdata['tr_prov_id'];
                    //$provSQL=new proveedor_SQLlista();
                    //$provSQL->whereID($tr_prov_id);
                    //$provSQL->setDatos();

                    $proveedor= new AvanzLookup();
                    $proveedor->setNamePage("../catalogos/catalogosDependenciaExterna_buscar.php?clear=2,busEmpty=0");  //nombre de la página que se cargará, puede agregar parametros pero separados por comas (,)en lugar de '&'
                    $proveedor->setNameCampoForm("","tr_ruc_id"); //campo donde se guardara el valor ingresado o buscado

                    $paramFunction= new manUrlv1();
                    $paramFunction->removeAllPar();
                    $paramFunction->addParComplete('colSearch','codigo');
                    $paramFunction->addParComplete('colOrden',1);
                    //$proveedor->addFieldID(textField("Proveedor",$proveedor->nameCampoForm,$provSQL->field('prov_codigo'),11,11,"onChange=\"xajax_buscarProveedor(3,this.value,'".encodeArray($paramFunction->getUrl())."',1,'document.frm._Dummy$proveedor->nameCampoForm.value')\"")); //adiciona campo de busqueda (para buscar por id/codigo)
                    $proveedor->addFieldID(textField("Proveedor",$proveedor->nameCampoForm,'',11,11,"onChange=\"xajax_buscarProveedor(3,this.value,'".encodeArray($paramFunction->getUrl())."',1,'document.frm._Dummy$proveedor->nameCampoForm.value')\"")); //adiciona campo de busqueda (para buscar por id/codigo)//
                    //cambio el nombre de la columna de busqueda
                    $paramFunction->removePar('colSearch');
                    $paramFunction->addParComplete('colSearch','prov_id');

                    //$proveedor->setValorCampoForm("",$prov->buscar(3,$tr_prov_id,encodeArray($paramFunction->getUrl())));//metodo de busqueda, se activa en modo edicion

                    $proveedor->setSize(70);//ancho del campo texto donde se almacenara el texto retornado
                    $proveedor->setWidth(750);//ancho de la ventana
                    $proveedor->setHeight(500);// define la altura de la ventana
                    $proveedor->setNewWin(true); // Ventana interna
                    $proveedor->setClassThickbox('thickbox2'); //renombro el css del thickbox para q no haya conflicto con la funcion 'getAfectacion' donde tambien se utilizan
                    $oForm->addField("Entidad: ",$proveedor->writeHTML());
                    $oForm->addHidden("tr_prov_id",$tr_prov_id,"Proveedor");
                    ///////////////////
                    $oForm->addField("Descipci&oacute;n Auxiliar: ",textField("Auxiliar","Sx_desp_descripaux",$bd_desp_descripaux,80,80));
                    $oForm->addField("Firma: ",textField("Firma","Sr_desp_firma",$bd_pers_cus,80,80));
                    $oForm->addField("Cargo: ",textField("Cargo","Sr_desp_cargo",$bd_pers_cus,80,80));
                    $oForm->addHidden("___tabl_tipo_despacho",$tipoDespacho); // clave primaria

                    break;

                    
                default:
                    $objResponse->addAlert('Proceso cancelado, Seleccione opción');
                    $oForm->addHidden("___tabl_tipo_despacho",NULL); // clave primaria
                    return $objResponse;
                    break;
           }
        $contenido_respuesta=$oForm->writeHTML();
	$objResponse->addAssign('divDatosIniciales','innerHTML', $contenido_respuesta);

        if($op==1){
                $objResponse->script("xajax_getSecuencia(1,document.frm.tr_tiex_id.value,document.frm.Dr_desp_fecha.value,document.frm.___tabl_tipo_despacho.value)");
                $objResponse->addScript("tb_init('a.thickbox2')");
		return $objResponse;
        }
	else
		return $contenido_respuesta	;
}

function addCarrito($deriva,$concopia,$proveido)
{
    $objResponse = new xajaxResponse();
  
    $nvoArrayDeriva=explode(',',$deriva);
    $nvoArrayConCopia=explode(',',$concopia);

    if(!$nvoArrayDeriva[0]){
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
    foreach ($nvoArrayDeriva as $i => $value) {
         $arrayDepeUser=explode('.',$value);

         $id=$arrayDepeUser[0];
         $depe_id=$arrayDepeUser[0];//dependencia
         $elemento=$value;
         $usua_id='';
         if($arrayDepeUser[1]>0){
             $usua_id=$arrayDepeUser[1];//usuario
             $id.='.'.$usua_id;
         }

        $data->addParComplete('id', $id);
        $data->addParComplete('depe_id', $depe_id);
        $data->addParComplete('usua_id', $usua_id);
        $data->addParComplete('elemento',$elemento);
        $data->addParComplete('proveido',$proveido);
        $data->addParComplete('cc','');
        $_SESSION["ocarrito"]->Add($data->getUrl());
    }

    $data= new manUrlv1();
    $data->removeAllPar(0);
    //derivación con Copia
    if($nvoArrayConCopia[0])
        foreach ($nvoArrayConCopia as $i => $value) {
             $arrayDepeUser=explode('.',$value);

             $id=$arrayDepeUser[0];
             $depe_id=$arrayDepeUser[0];//dependencia
             $elemento=$value;
             $usua_id='';
             if($arrayDepeUser[1]>0){
                 $usua_id=$arrayDepeUser[1];//usuario
                 $id.='.'.$usua_id;
             }

            $data->addParComplete('id', $id.'C');
            $data->addParComplete('depe_id', $depe_id);
            $data->addParComplete('usua_id', $usua_id);
            $data->addParComplete('elemento',$elemento);
            $data->addParComplete('proveido',$proveido);
            $data->addParComplete('cc','*');
            $_SESSION["ocarrito"]->Add($data->getUrl());
        }
    $objResponse->addScript("tlist2.removeAllBox()");
    $objResponse->addScript("document.frm.Sx_desp_proveido.value=''");
    $objResponse->addScript("tlist3.removeAllBox()");
    $objResponse->addScript("xajax_verCarrito()");
    return $objResponse;


}

function verCarrito()
{
	$objResponse = new xajaxResponse();

        
	$otable = new  Table("","100%",5);
	$otable->setColumnTD("ColumnBlueTD") ;
	$otable->setColumnFont("ColumnWholeFont") ;
	$otable->setFormTotalTD("FormTotalBlueTD");
	$otable->setAlternateBackTD("AlternateBackBlueTD");

	$otable->addBreak("<div align='center' style='color:#000000'><b>:: DERIVACIONES REALIZADAS ::</b></div>");
	$otable->addColumnHeader("Eli"); // Título, Ordenar?, ancho, alineación
	$otable->addColumnHeader("Dependencia/Usuario",false,"50%", "L"); // Título, Ordenar?, ancho, alineación
	$otable->addColumnHeader("Proveido",false,"45%", "L"); // Título, Ordenar?, ancho, alineación
	$otable->addColumnHeader("Cc",false,"5%", "L"); // Título, Ordenar?, ancho, alineación
	$otable->addRow(); // adiciona la linea (TR)

	$array=$_SESSION["ocarrito"]->getArray();
	foreach($array as $arrItem) {
		$items=key($array); /* Para guardar el key del array padre */

                $otable->addData("<a class=\"link\" href=\"#\" onClick=\"javascript:if(confirm('Eliminar este registro?')) {xajax_elimCarrito($items)}\"><img src=\"../../img/delete.gif\" border=0 align=absmiddle hspace=1 alt=\"Eliminar\"></a>");


		$otable->addData($arrItem['elemento']);
		$otable->addData($arrItem['proveido']);
		$otable->addData($arrItem['cc']);
                //$otable->addData($items);

		$otable->addRow();
		next($array); /* voy al siguiente registro del array padre */
	}


	$contenido_respuesta.=$otable->writeHTML();
	$contenido_respuesta.="<div class='BordeatablaBlue' style='width:50%;float:left' align='left'>&nbsp;</div>";
	$contenido_respuesta.="<div class='BordeatablaBlue' style='width:50%;float:right' align='right'>Total Items: ".$_SESSION["ocarrito"]->getConteo()."</div>";


	$objResponse->addAssign('divDerivacion','innerHTML', $contenido_respuesta);
	return $objResponse;
}

//funcion que elimina un item al carrito
function elimCarrito($id)
{
    $objResponse = new xajaxResponse();
    $_SESSION["ocarrito"]->Del($id);
    return(verCarrito());
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

function getSecuencia($op,$tipoExpediente,$fechaDoc,$tipoDespacho){
        global $conn;
        $objResponse = new xajaxResponse();
        //$objResponse->addAlert($tipoExpediente);

        if(!$tipoExpediente) return $objResponse;

        $oForm = new AddTableForm();
        $oForm->setLabelWidth("20%");
        $oForm->setDataWidth("80%");
        $annoExplode=explode("/",$fechaDoc);
        $anno=$annoExplode[2];
        switch ($tipoDespacho){
                case 140://institucional
                    $siglas=new dependencia_SQLlista();
                    $siglas->whereID(getSession("sis_depeid"));
                    $siglas->setDatos();
                    $siglasDoc=$siglas->field('depe_siglasdoc');

                    $td=new clsTipExp_SQLlista();
                    $td->whereID($tipoExpediente);
                    $td->setDatos();
                    $td_secuencia=$td->field('tiex_secuencia');

                    $secuencia="gestdoc.corr_exp_".$td_secuencia.'_'.$anno.'_'.getSession("sis_depeid").'_'.$tipoExpediente;

                    $numDocum=$conn->currval($secuencia);
                    if($numDocum==0){ /* Si la secuencia no está creada */
                        $numDocum=1; /* Asigno el número 1 */
                    }
                    $numDocum=str_pad($numDocum,6,'0',STR_PAD_LEFT);
                    $oForm->addField("N&uacute;mero-A&ntilde;o-Siglas: ","<font size=\"-1\">$numDocum-$anno-$siglasDoc</font>");
                    $oForm->addHidden('nx_anno_nume_doc',$anno);//guardo el numero para q funcione en las actualizaciones
                    $oForm->addHidden('Sr_desp_siglas',$siglasDoc,"Siglas");//guardo el numero para q funcione en las actualizaciones
                    $oForm->addHidden('___td_secuencia',$td_secuencia);//guardo el numero para q funcione en las actualizaciones
                    break;

                case 141://personal
                    $siglas=new dependencia_SQLlista();
                    $siglas->whereID(getSession("sis_depeid"));
                    $siglas->setDatos();
                    $siglasDoc=$siglas->field('depe_siglasdoc');

                    $siglas=new clsUsers_SQLlista();
                    $siglas->whereID(getSession("sis_userid"));
                    $siglas->setDatos();
                    $siglasPers=$siglas->field('usua_iniciales');

                    $td=new clsTipExp_SQLlista();
                    $td->whereID($tipoExpediente);
                    $td->setDatos();
                    $td_secuencia=$td->field('tiex_secuencia');

                    $secuencia="gestdoc.corr_exp_".$td_secuencia.'_'.$anno.'_'.getSession("sis_depeid").'_'.$tipoExpediente.'_'.getSession("sis_persid");
                    $numDocum=$conn->currval($secuencia);
                    
                    if($numDocum==0){ /* Si la secuencia no está creada */
                        $numDocum=1; /* Asigno el número 1 */
                    }
                    $numDocum=str_pad($numDocum,6,'0',STR_PAD_LEFT);
                    $oForm->addField("N&uacute;mero-A&ntilde;o-Siglas: ","<font size=\"-1\">$numDocum-$anno-$siglasDoc-$siglasPers</font>");
                    $oForm->addHidden('nx_anno_nume_doc',$anno);//guardo el numero para q funcione en las actualizaciones
                    $oForm->addHidden('Sr_desp_siglas',$siglasDoc,"Siglas");//guardo el numero para q funcione en las actualizaciones
                    $oForm->addHidden('___td_secuencia',$td_secuencia);//guardo el numero para q funcione en las actualizaciones
                    break;

                case 142://otras entidades
                    $oForm->addField("N&uacute;mero-A&ntilde;o-Siglas: ",numField("N&uacute;mero","nr_nume_doc",$numDocum,6,6,0)
                            ."<font size=\"-1\">-$anno-</font>"
                            .textField("Siglas","Sr_desp_siglas",'',30,30)
                            );
                    $oForm->addHidden('nx_anno_nume_doc',$anno);//guardo el numero para q funcione en las actualizaciones
                    break;


                default:
                    $objResponse->addAlert('Proceso cancelado, Seleccione opción'.$tipoDespacho);
                    return $objResponse;
                    break;
           }



        $contenido_respuesta=$oForm->writeHTML();
	$objResponse->addAssign('divNumeroDoc','innerHTML', $contenido_respuesta);

        if($op==1)
            return $objResponse;
	else
            return $contenido_respuesta;
}

function guardar($formdata)
{
	global $conn,$param;

	$objResponse = new xajaxResponse();
	$objResponse->setCharEncoding('utf-8');

	$usua_id=getSession("sis_userid"); /* Id del usuario que graba el registro */
        $pers_id=getSession("sis_persid"); /* Id del usuario que graba el registro */
        $depe_id=getSession("sis_depeid");

	/* Recibo campos */
	$tabl_tipo_despacho=$formdata['___tabl_tipo_despacho'];		/*campo id de la tabla en caso de modificacion*/
        $desp_fecha=$formdata['Dr_desp_fecha'];
        $tiex_id=$formdata['tr_tiex_id'];
        $desp_asunto=$formdata['Er_desp_asunto'];
        $desp_firma=$formdata['Sr_desp_firma'];
        $desp_cargo=$formdata['Sr_desp_cargo'];        
        $tabl_formarecepcion=$formdata['tr_tabl_modorecepcion'];
        $desp_folios=$formdata['nr_desp_folios'];
        $desp_proyectadopor=$formdata['Sx_desp_proyectadopor'];

        $prat_id=$formdata['tx_prat_id'];
        $prov_id=$formdata['tr_prov_id'];
        $prov_id=$prov_id?$prov_id:null;

        $annoExplode=explode("/",$desp_fecha);
        $anno=$annoExplode[2];
        $td_secuencia=$formdata['___td_secuencia'];

        switch($tabl_tipo_despacho){
            case 140://institucional
                $secuencia="gestdoc.corr_exp_".$td_secuencia.'_'.$anno.'_'.$depe_id.'_'.$tiex_id;
                $numDocum=$conn->currval($secuencia);
                if($numDocum==0){ /* Si la secuencia no está creada */
                    $conn->nextid($secuencia); /* Creo la secuencia */
                    $numDocum=1; /* Asigno el número 1 */
                }
                $desp_numero=$numDocum;
                $desp_anno=$formdata['nx_anno_nume_doc'];
                $desp_siglas=$formdata['Sr_desp_siglas'];
                break;

            case 141://personal
                $secuencia="gestdoc.corr_exp_".$td_secuencia.'_'.$anno.'_'.$depe_id.'_'.$tiex_id.'_'.$pers_id;
                $numDocum=$conn->currval($secuencia);
                if($numDocum==0){ /* Si la secuencia no está creada */
                    $conn->nextid($secuencia); /* Creo la secuencia */
                    $numDocum=1; /* Asigno el número 1 */
                }
                $desp_numero=$numDocum;
                $desp_anno=$formdata['nx_anno_nume_doc'];
                $desp_siglas=$formdata['Sr_desp_siglas'];
                break;

            case 142://otras entidades
                $desp_numero=$formdata['nr_nume_doc'];
                $desp_anno=$formdata['nx_anno_nume_doc'];
                $desp_siglas=$formdata['Sr_desp_siglas'];
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
	$sql->setAction("INSERT"); /* Operación */

        $sql->addField('tabl_tipo_despacho',$tabl_tipo_despacho, "Number");
        $sql->addField('desp_fecha',$desp_fecha, "String");
        $sql->addField('tiex_id',$tiex_id, "Number");
        $sql->addField('desp_numero',$desp_numero, "Number");
        $sql->addField('desp_anno',$desp_anno, "Number");
        $sql->addField('desp_siglas',$desp_siglas, "String");
        $sql->addField('desp_asunto',$desp_asunto, "String");
        $sql->addField('desp_firma',$desp_firma, "String");
        $sql->addField('desp_cargo',$desp_cargo, "String");
        $sql->addField('tabl_modorecepcion',$tabl_formarecepcion, "Number");
        $sql->addField('depe_id',$depe_id, "Number");
        $sql->addField('desp_folios',$desp_folios, "Number");
        $sql->addField('desp_proyectadopor',$desp_proyectadopor, "String");
        $sql->addField('prat_id',$prat_id, "Number");
        $sql->addField('prov_id',$prov_id, "Number");
        $sql->addField('usua_id',$usua_id, "Number");


	$sql=$sql->getSQL()." RETURNING desp_id";

	$sql= strtoupper($sql);

	$padre_id=$conn->execute($sql); //obtengo el id del registro generado


	$error=$conn->error();
	if($error){
		 $conn-> rollback();
		 $objResponse->addAlert($error);
	 }
	/********* FIN PROCESO DE GRABACION EN EL PADRE *********/
	else{

		/********* INICIO PROCESO DE GRABACION EN EL HIJO *********/
		$array=$_SESSION["ocarrito"]->getArray();
		foreach($array as $arrItem){
                         $depe_id=$arrItem['depe_id'];
                         $usua_id=$arrItem['usua_id'];
                         $dede_concopia=$arrItem['cc']=='*'?1:0;
                         $dede_proveido=$arrItem['proveido'];
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
			$sql->addField('desp_id', $padre_id, "Number");
			$sql->addField('depe_id', $depe_id, "Number");
			$sql->addField('usua_id', $usua_id, "Number");
			$sql->addField('dede_concopia', $dede_concopia, "Number");
			$sql->addField('dede_proveido', $dede_proveido, "String");
			$sql->addField('usua_idcrea', $usua_idcrea, "Number");
			$sql=$sql->getSQL();


                        $sql= strtoupper($sql);
			//$objResponse->addAlert($sql);

			$conn->execute($sql);
			$error=$conn->error();
			if($error){
				$conn-> rollback();
				$objResponse->addAlert($error);
				 break;
			 }
		}
		/********* FIN PROCESO DE GRABACION EN EL HIJO *********/

	}

	if($error){
		return $objResponse;
	}else{
	       $conn->commit(); /* termino transacción */
	       $conn->setval($secuencia,intval($desp_numero)+1); /* se suma 1 a la secuencia del documento */
               
	}
        //$objResponse->addAlert('Nuevo Despacho:'.str_pad($desp_numero,8,'0',STR_PAD_LEFT));
	$objResponse->addRedirect("mensaje.php?id=$desp_numero");
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


    <script language="JavaScript" src="<?php echo PATH_INC?>js/focus.js"></script>
    <script language="JavaScript" src="<?php echo  PATH_INC?>js/libjsgen.js"></script>
    <script language="JavaScript" src="<?php echo PATH_INC?>js/textcounter.js"></script>

    <link rel="stylesheet" href="<?php echo PATH_INC?>thickbox/thickbox.css" type="text/css" media="screen" />
    <script type="text/javascript" src="<?php echo PATH_INC?>jquery/jquerypack.js"></script>
    <script type="text/javascript" src="<?php echo PATH_INC?>thickbox/thickbox2.js"></script>

    <link rel="stylesheet" href="<?php echo PATH_INC?>textboxlist/test.css" type="text/css" media="screen" title="Test Stylesheet" charset="utf-8" />
    <script src="<?php echo PATH_INC?>textboxlist/mootools-beta-1.2b1.js" type="text/javascript" charset="utf-8"></script>
    <script src="<?php echo PATH_INC?>textboxlist/textboxlist.compressed.js" type="text/javascript" charset="utf-8"></script>
    <script src="<?php echo PATH_INC?>textboxlist/test.js" type="text/javascript" charset="utf-8"></script>
    
	<script language='JavaScript'>

        var tlist2,tlist3

        window.addEvent('domready', function() {
          // init

          tlist2 = new FacebookList('Dependencia', 'facebook-auto');  //Derivar_A: nombre del objeto, definido lineas abajo ; facebook-auto: nombre del estilo aplicado al objeto
          /*si hay mas de un objeto autocompletar*/
          tlist3 = new FacebookList('ConCopia a', 'facebook-auto2');

          // fetch and feed
          new Request.JSON({'url': 'JSONdependencias.php?depe_id=<?php echo $bd_depe_id?>', 'onComplete': function(j) {
                j.each(tlist2.autoFeed, tlist2);
                /*si hay mas de un objeto autocompletar*/
                j.each(tlist3.autoFeed, tlist3);
          }}).send();


        });


        function ver_datos(){
            alert(tlist2.bits.getValues());
            alert(tlist3.bits.getValues());
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
        
        function abreJanelaAuxiliar(pagina,nWidth,nHeight){
                eval('janela = window.open("../../library/auxiliar.php?pag=' +  pagina +
                     '","janela","width='+nWidth+',height='+nHeight+',top=50,left=150' +
                          ',scrollbars=no,hscroll=0,dependent=yes,toolbar=no")');
                janela.focus();
        }
	/*
		función que define el foco inicial en el formulario
	*/
	function inicializa() {
		document.frm.Dr_desp_fecha.focus();
	}
	</script>
	<?php 
        verif_framework();
   	$calendar->load_files();
        $xajax->printJavascript(PATH_INC.'ajax/');
        ?>

    <style type="text/css">
        /*este estilo se crea solo si hay mas de un objeto autocompletar*/

        #facebook-auto2 { display: none; position: absolute; width: 512px; background: #eee; }
        #facebook-auto2 .default { padding: 5px 7px; border: 1px solid #ccc; border-width: 0 1px 1px; }
        #facebook-auto2 ul { display: none; margin: 0; padding: 0; }
        #facebook-auto2 ul li { padding: 5px 12px; margin: 0; list-style-type: none; border: 1px solid #ccc; border-width: 0 1px 1px; font: 11px "Lucida Grande", "Verdana"; }
        #facebook-auto2 ul li em { font-weight: bold; font-style: normal; background: #ccc; }
        #facebook-auto2 ul li.auto-focus { background: #4173CC; color: #fff; }
        #facebook-auto2 ul li.auto-focus em { background: none; }
    </style>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
if(!strlen($id))
    pageTitle("Nuevo ".$myClass->getTitle());
else
    pageTitle("Edici&oacute;n de ".$myClass->getTitle());


/* botones */
$button = new Button;
$button->addItem(" Guardar ","javascript:if(ObligaCampos(frm)){ocultarObj('Guardar',10);xajax_guardar(xajax.getFormValues('frm'))}",'content',2,getSession("sis_userid"));
$button->addItem(" Deshacer ","javascript:if(confirm('\u00BFSeguro de Deshacer el Registro?')){
                                    document.location='registrosDespacho_buscar.php'}","content");

echo $button->writeHTML();

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
        
$modo_recep=new clsTabla_SQLlista();
$modo_recep->whereTipo('MODO_RECEPCION');
$sqlmodo_recepcion=$modo_recep->getSQL_cbox();
$form->addField("Modo de Recepci&oacute;n/Envio",listboxField("Modo de Recepci&oacute;n/Envio",$sqlmodo_recepcion,"tr_tabl_modorecepcion",$bd_tabl_formarecepcion));

$desp_tipo=new clsTabla_SQLlista();
$desp_tipo->whereTipo('TIPO_DESPACHO');
$rs = new query($conn, $desp_tipo->getSQL());

$tipo_despacho=0;
while ($rs->getrow()) {
    $tipo_despacho=$tipo_despacho?$tipo_despacho:$rs->field("tabl_id");
    $lista_nivel[].=$rs->field("tabl_id").",".$rs->field("tabl_descripcion");
}

$form->addField("Tipo de Despacho: ",radioField("Tipo de Despacho",$lista_nivel, "xxtipo_despacho",$tipo_despacho,"onChange=\"xajax_getInicia(1,this.value)\"","H"));
$form->addHtml("<tr><td colspan=2><div id='divDatosIniciales'>\n"); //pide datos de afectacion presupuestal
$form->addHtml(getInicia(2,$tipo_despacho));
$form->addHtml("</div></td></tr>\n");

$form->addField("Fecha de Documento: ", $calendar->make_input_field('Fecha de Documento',array(),array('name'=> 'Dr_desp_fecha','value'=> $bd_desp_fecha,'onChange'=>"xajax_getSecuencia(1,document.frm.tr_tiex_id.value,this.value,document.frm.___tabl_tipo_despacho.value)")));

$texp=new clsTipExp_SQLlista();
$texp->orderUno();
$sqltipo=$texp->getSQL_cbox();
$form->addField("Tipo de Expediente",listboxField("Tipo de Expediente",$sqltipo,"tr_tiex_id",$bd_tiex_id,"-- Seleccione Tipo de Expediente --","onChange=\"xajax_getSecuencia(1,this.value,document.frm.Dr_desp_fecha.value,document.frm.___tabl_tipo_despacho.value);\""));
$form->addHtml("<tr><td colspan=2><div id='divNumeroDoc' >\n"); //pide datos de afectacion presupuestal
$form->addHtml(getSecuencia(2,$bd_tiex_id,$bd_desp_fecha,$tipo_despacho));

$form->addHtml("</div></td></tr>\n");
$form->addField("Asunto: ",textAreaField("Asunto","Er_desp_asunto",$bd_desp_asunto,3,80,300));
$form->addField("N&uacute;mero de Folios: ",numField("N&uacute;mero de Folios","nr_desp_folios",$bd_desp_folios,6,6,0));
$form->addField("Proyectado Por: ",textField("Proyectado Por","Sx_desp_proyectadopor",$bd_desp_proyectadopor,30,30));
$periodoAtencion=new clsPrioriAtencion_SQLlista();
$periodoAtencion->orderUno();
$sql_periodoAtencion=$periodoAtencion->getSQL_cbox();
$form->addField("Prioridad de Atenci&oacute;n",listboxField("Prioridad de Atenci&oacute;n",$sql_periodoAtencion,"tx_prat_id",$bd_prat_id,"-- Sin Prioridad de Atenci&oacute;n --"));
$form->addBreak("Derivaci&oacute;n", true);


$otable = new AddTableForm();
$otable->setLabelWidth("20%");
$otable->setDataWidth("80%");
$otable->setLabelTD("LabelOrangeTD");

$link="<a title=\"pulse para ingresar con Copia a\" class=\"link\" href=\"#\" onClick=\"javascript:document.getElementById('ccopia').style.display='inline'\">ccopia</a>";
$otable->addField("Dependencia: <br>$link",autocompleteField("Dependencia","Sx_dependencia","facebook-auto",""));

$otable2 = new AddTableForm();
$otable2->setLabelWidth("20%");
$otable2->setDataWidth("80%");
$otable2->setLabelTD("LabelOrangeTD");

$otable2->addField("Con Copia",autocompleteField("ConCopia a","Sx_concopia","facebook-auto2",""));
$otable->addHtml("<tr><td colspan=2><div id=\"ccopia\" style=\"display: none\">\n"); //pide datos de inicio
$otable->addHtml($otable2->writeHTML());
$otable->addHtml("</div></td></tr>\n"); //pide datos de inicio

$otable->addField("Proveido de Atenci&oacute;n: ",textField("Proveido","Sx_desp_proveido",$bd_desp_proveido,100,100));

/* botones */
$button = new Button;
$button->addItem(" Agregar ","javascript:xajax_addCarrito(tlist2.bits.getValues().join(),tlist3.bits.getValues().join(),document.frm.Sx_desp_proveido.value);ocultarObj(' Agregar ',3);","content",2,$bd_usua_id,'botao','button');
$button->align('L');
$otable->addField("",$button->writeHTML());
$form->addHtml("<tr><td colspan=2>".$otable->writeHTML()."</td></tr>");

$form->addHtml("<tr><td colspan=2><div id='divDerivacion' >\n"); //pide datos de afectacion presupuestal
$form->addHtml("</div></td></tr>\n");

//solo si es edicion se agrega los datos de auditoria
if(strlen($id)) {
	$form->addBreak("<b>Control</b>");
	$form->addField("Creado por: ",$username);
	//$form->addField("Actualizado por: ",$usernameactual);
}

echo $form->writeHTML();
?>
</body>
</html>

<?php
/* cierro la conexión a la BD */
$conn->close();
unset($_SESSION["ocarrito"]);