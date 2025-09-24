<?php
/*
	formulario de ingreso y modificaci�n
*/
include("../../library/library.php");
include("../catalogos/catalogosTabla_class.php");

/*
	verificaci�n del n�vel de usuario
*/
verificaUsuario(1);

/*
	establecer conexion con la BD
*/
$conn = new db();
$conn->open();

/*
	tratamiento de campos
*/
$id = getParam("id"); // captura la variable que viene del objeto lista
$tipo=getParam("tipo");

if (strlen($id)>0) { // edici�n
	$sql = sprintf("SELECT a.*,"
                . "             b.usua_login AS login, "
                . "             c.usua_login AS login_actual "
                . " FROM servicio_grupo a "
                . " LEFT JOIN usuario b ON a.usua_id=b.usua_id "
                . " LEFT JOIN usuario c ON a.segr_actualusua=c.usua_id "
                . " WHERE a.segr_id=%d", $id) ;
        
	$rs = new query($conn, $sql);
	if ($rs->getrow()) {
		$bd_segr_id = $rs->field("segr_id");
		$bd_segr_descripcion = $rs->field("segr_descripcion");
		$bd_segr_vinculo = $rs->field("segr_vinculo");
                $bd_segr_convenio_porcent=$rs->field("segr_convenio_porcent");
                $bd_segr_estado=$rs->field("segr_estado");
                $bd_segr_lunes=$rs->field("segr_lunes");
                $bd_segr_martes=$rs->field("segr_martes");
                $bd_segr_miercoles=$rs->field("segr_miercoles");
                $bd_segr_jueves=$rs->field("segr_jueves");
                $bd_segr_viernes=$rs->field("segr_viernes");
                $bd_segr_sabado=$rs->field("segr_sabado");
                $bd_segr_domingo=$rs->field("segr_domingo");
                $bd_segr_tipo = $rs->field("segr_tipo");
                $bd_segr_destino= $rs->field("segr_destino");
                $bd_segr_almacen=$rs->field("segr_almacen");
                $bd_segr_solicita_ubigeo=$rs->field("segr_solicita_ubigeo");
                $bd_segr_solicita_ubigeo2=$rs->field("segr_solicita_ubigeo2");
                $bd_segr_solicita_tipo_cliente=$rs->field("segr_solicita_tipo_cliente");
                $bd_segr_solicita_medico=$rs->field("segr_solicita_medico");
                        
		$bd_usua_id = $rs->field("usua_id");		
                $login = $rs->field("login");	
                $login_actual = $rs->field("login_actual");
	}
}
else{
    $bd_segr_convenio_porcent=0;	    
    $bd_segr_vinculo=9;
    $bd_segr_tipo=$tipo;
    $bd_segr_lunes=1;
    $bd_segr_martes=1;
    $bd_segr_miercoles=1;
    $bd_segr_jueves=1;
    $bd_segr_viernes=1;
    $bd_segr_sabado=1;
    $bd_segr_domingo=1; 
}



?>
<html>
<head>
	<title>Grupo de Servicios-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="../../library/js/focus.js"></script>
	<script language="JavaScript" src="../../library/js/libjsgen.js"></script>	
	<script language='JavaScript'>
	/*
		funci�n guardar
	*/
	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "../imes/guardar.php?_op=GrpoServ&tipo=<?php echo $tipo?>";
			parent.content.document.frm.submit();
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
		parent.content.document.frm.Sr_segr_descripcion.focus();
	}
	</script>
	<?php
        verif_framework(); 
        ?>
	
</head>
<body class="contentBODY" onLoad="inicializa()">
<?php
//if($tipo=="M")
//    pageTitle("Edici&oacute;n de Grupo de Servicios M&eacute;dicos","");
//elseif(inlist($tipo,"T,S"))
    pageTitle("Edici&oacute;n de Grupo","");
//else
//    pageTitle("Edici&oacute;n de Grupo de Transacci&oacute;n","");
    
/*
	botones,
	configure conforme suas necessidades
*/
$retorno = $_SERVER['QUERY_STRING'];
 
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2);
$button->addItem(" Regresar ","catalogosServiciosGrupos_lista.php?$retorno","content");
echo $button->writeHTML();

/*
	Control de abas,
	true, se for a aba da p�gina atual,
	false, se for qualquer outra aba,
	configure conforme al exemplo de abajo
*/
$abas = new Abas();
$abas->addItem("General",true);
if ($id>0) { // si es edici�n 
	$abas->addItem("Sub Grupos",false,"catalogosServiciosGrupos_sGrupoLista1n.php?id=$id&busEmpty=$busEmpty&tipo=$tipo");
}

echo $abas->writeHTML();

echo "<br>";

/*
	Formulario
*/
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$form->addHidden("rodou","s"); // variable de control
$form->addHidden("f_id",$id); // clave primaria
$form->addHidden("pagina",getParam("pagina")); // numero de p�gina que llamo

if($id){
    $form->addField("Grupo: ",$id);
}

$form->addField("Descripci&oacute;n: ",textField("Descripci&oacute;n","Sr_segr_descripcion",$bd_segr_descripcion,50,50));

//$lista_nivel = array("1, Ninguno","2,Especialidades","3,Perfiles de An&aacute;lisis ");
    
//if(strpos($bd_segr_tipo,'M')!==false){ //servicios MEDICOS
//    $form->addHidden("___segr_tipo",$bd_segr_tipo); // clave primaria
//    $lista_nivel = array("1, Ninguno","2,Especialidades");
//    $form->addField("Vinculado a: ",radioField("Vinculado a",$lista_nivel, "xr_segr_vinculo",$bd_segr_vinculo));
//    $form->addField("% Convenio", numField('% Convenio',"zr_segr_convenio_porcent",$bd_segr_convenio_porcent,6,4,0));
//    $form->addField("D&iacute;as Disponibles:","Lunes".checkboxField("lunes","hx_segr_lunes",1,$bd_segr_lunes==1).
//    " Martes: ".checkboxField("martes","hx_segr_martes",1,$bd_segr_martes==1).
//    " Mi&eacute;rcoles: ".checkboxField("miercoles","hx_segr_miercoles",1,$bd_segr_miercoles==1).
//    " Jueves: ".checkboxField("jueves","hx_segr_jueves",1,$bd_segr_jueves==1).
//    " Viernes: ".checkboxField("viernes","hx_segr_viernes",1,$bd_segr_viernes==1).
//    " S&aacute;bado: ".checkboxField("sabado","hx_segr_sabado",1,$bd_segr_sabado==1).
//    " Domingo: ".checkboxField("domingo","hx_segr_domingo",1,$bd_segr_domingo==1));    
//}elseif(strpos($bd_segr_tipo,'T')!==false) {//servicios RECAUDACIONES
    
    $form->addHidden("___segr_tipo",$bd_segr_tipo); // clave primaria
//    if(SIS_EMPRESA_TIPO==3){//beneficencias
//        
//        if(SIS_GESTMED==1){
//            $lista_nivel = array("1, Ninguno","2,Especialidades","3,Farmacia","4,Componentes de Cementerio","5,Imagenes");            
//        }else{
//            $lista_nivel = array("1, Ninguno","","4,Componentes de Cementerio","5,Imagenes");            
//        }
//
//    }else{
//        if(SIS_GESTMED==1){
//            $lista_nivel = array("1, Ninguno","2,Especialidades","3,Farmacia","5,Imagenes");            
//        }else{
//            $lista_nivel = array("1, Ninguno","5,Imagenes");            
//        }
//
//    }
//    
    if(SIS_EMPRESA_TIPO==4){//Tipo Almacen
        $form->addHidden("hx_segr_lunes",1); //lunes activo
        $form->addHidden("hx_segr_martes",1); //martes activo
        $form->addHidden("hx_segr_miercoles",1); //miercoles activo
        $form->addHidden("hx_segr_jueves",1); //jueves activo
        $form->addHidden("hx_segr_viernes",1); //viernes activo
        $form->addHidden("hx_segr_sabado",1);
        $form->addHidden("hx_segr_domingo",1);
                
        $form->addHidden("hx_segr_almacen",1);//grupo para almacen
        $form->addHidden("nr_segr_destino",3);//ingresos y egresos
        $form->addHidden("hx_segr_solicita_ubigeo2",1);//UBIGEO-2
        
    }else{

        $tblVinculos=new clsTabla_SQLlista();
        $tblVinculos->whereTipo('VINCULO_GRUPO_SERVICIO');
        $tblVinculos->whereActivo();
        $tblVinculos->orderUno();
        $sqlVinculos=$tblVinculos->getSQL_cboxCodigo();
        $rs = new query($conn, $sqlVinculos);
        while ($rs->getrow()) {
            $lista_vinculo[].=$rs->field("tabl_codigo").",".  $rs->field("tabl_descripcion");
        }    

        $form->addField("Vinculado a: ",radioField("Vinculado a",$lista_vinculo, "xr_segr_vinculo",$bd_segr_vinculo));
        $form->addField("% Convenio", numField('% Convenio',"zr_segr_convenio_porcent",$bd_segr_convenio_porcent,6,4,0));    

        $form->addField("D&iacute;as Disponibles:","Lunes".checkboxField("lunes","hx_segr_lunes",1,$bd_segr_lunes==1).
            " Martes: ".checkboxField("martes","hx_segr_martes",1,$bd_segr_martes==1).
            " Mi&eacute;rcoles: ".checkboxField("miercoles","hx_segr_miercoles",1,$bd_segr_miercoles==1).
            " Jueves: ".checkboxField("jueves","hx_segr_jueves",1,$bd_segr_jueves==1).
            " Viernes: ".checkboxField("viernes","hx_segr_viernes",1,$bd_segr_viernes==1).
            " S&aacute;bado: ".checkboxField("sabado","hx_segr_sabado",1,$bd_segr_sabado==1).
            " Domingo: ".checkboxField("domingo","hx_segr_domingo",1,$bd_segr_domingo==1));        
    
        $form->addField("Control de Almac&eacute;n:",checkboxField("Control Almacen","hx_segr_almacen",1,$bd_segr_almacen==1));        
        $form->addField("Solicitar UBIGEO:",checkboxField("Solicitar UBIGEO","hx_segr_solicita_ubigeo",1,$bd_segr_solicita_ubigeo==1));    
        $form->addField("Solicitar UBIGEO-2:",checkboxField("Solicitar UBIGEO","hx_segr_solicita_ubigeo2",1,$bd_segr_solicita_ubigeo2==1));    
        $form->addField("Solicitar Tipo de Cliente:",checkboxField("Solicitar Tipo de Cliente","hx_segr_solicita_tipo_cliente",1,$bd_segr_solicita_tipo_cliente==1));    
        $form->addField("Solicitar M&eacute;dico:",checkboxField("Solicitar Medico","hx_segr_solicita_medico",1,$bd_segr_solicita_medico==1));    

    //}elseif(strpos($bd_segr_tipo,'H')!==false) {//servicios RECAUDACIONES
    //    $form->addHidden("___segr_tipo",$bd_segr_tipo); // clave primaria    
    //}else{
    //    $form->addField("Tipo: ",textField("Tipo","Sx_segr_tipo",$bd_segr_tipo,5,5));
    //}

    //if(strpos($bd_segr_tipo,'H')===false){//haberes
        //PARA SOLICITAR CLASIFICADOR, COMPONENTE Y OTROS EN LOS CONCEPTOS
        $sqlDestino = array(1 => "Ingreso/Venta/Recaudaci&oacute;n", 2 => "Egreso/Compra/Gasto",  3 => "Ambos (Ingresos/Egresos)", 9 => "Otros"); 
        $form->addField("Destino: ", listboxField("Destino ", $sqlDestino, "nr_segr_destino",$bd_segr_destino));
    //}
    }
            
//solo si es edicion se agrega los datos de auditoria
if($id) {
        $form->addField("Activo: ",checkboxField("Activo","hx_segr_estado",1,$bd_segr_estado==1));

	$form->addBreak("<b>Control</b>");
	$form->addField("Creado por: ",$login);
        $form->addField("Actualizado por: ",$login_actual);
}else{
        $form->addHidden("hx_segr_estado",1); // numero de p�gina que llamo    

}


echo $form->writeHTML();
?>
</body>
</html>
<?php
/*
	cierro la conexi�n a la BD
*/
$conn->close();