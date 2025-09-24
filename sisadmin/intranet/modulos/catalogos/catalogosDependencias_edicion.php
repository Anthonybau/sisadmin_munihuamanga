<?php
/*
	formulario de ingreso y modificacion
*/
include("../../library/library.php");
include("catalogosDependencias_class.php");
include("../personal/personalDatosLaborales_class.php");
include("../catalogos/catalogosTabla_class.php");
include("../admin/datosEmpresaRUC_class.php");
/*
	verificación del nível de usuario
*/
verificaUsuario(1);

/*
	establecer conexión con la BD
*/
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();
$param->removePar('relacionamento_id'); /* Remuevo el parámetro */
/*
	tratamiento de campos
*/
$id = getParam("id"); // captura la variable que viene del objeto lista
$myClass = new dependencia($id,'Edici&oacute;n de Dependencia');

if (strlen($id)>0) { // edición
    $myClass->setDatos();
    if($myClass->existeDatos()){
        $bd_depe_id = $myClass->field('depe_id');
        $bd_depe_nombre = $myClass->field('depe_nombre');
	$bd_depe_nombrecorto=$myClass->field('depe_nombrecorto');
        $bd_depe_direccion=$myClass->field('depe_direccion');
        $bd_depe_telefonos=$myClass->field('depe_telefonos');
        $bd_depe_siglasdoc= $myClass->field('depe_siglasdoc');
        $bd_depe_siglasresolucion= $myClass->field('depe_siglasresolucion');
	$bd_depe_depeid=$myClass->field('depe_depeid');
	$bd_depe_habiltado=$myClass->field('depe_habiltado');
        $bd_depe_almacen=$myClass->field('depe_almacen');
        $bd_almacen_ventas= $myClass->field('depe_almacen_ventas');
        $bd_depe_centro_medico = $myClass->field('depe_centro_medico');
        $bd_depe_logistica= $myClass->field('depe_logistica');
        $bd_depe_administracion= $myClass->field('depe_administracion');
        $bd_depe_contrataciones= $myClass->field('depe_contrataciones');
        $bd_depe_max_x_recibir = $myClass->field('depe_max_x_recibir');
        $bd_depe_max_dias_x_recibir=$myClass->field('depe_max_dias_x_recibir');
        $bd_depe_max_doc_proceso = $myClass->field('depe_max_doc_proceso');       
        $bd_depe_max_dias_doc_proceso = $myClass->field('depe_max_dias_doc_proceso');  
        $bd_depe_nutricion=$myClass->field('depe_nutricion');  
        $bd_depe_rindente=$myClass->field('depe_rindente');
        $bd_depe_superior=$myClass->field('depe_superior');  
        $bd_depe_cuenta_patron=$myClass->field('depe_cuenta_patron');
        $bd_depe_mesa_partes=$myClass->field('depe_mesa_partes');
        $bd_depe_mesa_partes_virtual=$myClass->field('depe_mesa_partes_virtual');

        $bd_depe_rrhh=$myClass->field('depe_rrhh');
        
        $bd_depe_lunes_viernes_desde=$myClass->field('depe_lunes_viernes_desde');
        $bd_depe_lunes_viernes_hasta=$myClass->field('depe_lunes_viernes_hasta');
                        
        $bd_depe_sabado_desde=$myClass->field('depe_sabado_desde');
        $bd_depe_sabado_hasta=$myClass->field('depe_sabado_hasta');        
        
        $bd_depe_domingo_desde=$myClass->field('depe_domingo_desde');
        $bd_depe_domingo_hasta=$myClass->field('depe_domingo_hasta');        
        
        $bd_depe_mpv_mensaje_externo=$myClass->field('depe_mpv_mensaje_externo');
        $bd_depe_mpv_mensaje_registro=$myClass->field('depe_mpv_mensaje_registro');
        $bd_depe_mpv_mensaje_notificaciones=$myClass->field('depe_mpv_mensaje_notificaciones');
                
        $bd_depe_video_mpv=$myClass->field('depe_video_mpv');
                
        $bd_pdla_id= $myClass->field('pdla_id');
        $bd_jefe=$myClass->field('jefe');
        if($bd_jefe){
            $bd_jefe_dni=$myClass->field('jefe').'/'.$myClass->field('jefe_dni');
        }else{
            $bd_jefe_dni='';
        }
        
        $bd_pdla_cargofuncional= $myClass->field('pdla_cargofuncional');
        $bd_emru_id= $myClass->field('emru_id');
        $bd_depe_serie= $myClass->field('depe_serie');
        $bd_file_logo= $myClass->field('file_logo');
        
	$bd_usua_id = $myClass->field('usua_id');
        $username= $myClass->field('username').' '.$myClass->field('depe_fecharegistro');
        $usernameactual= $myClass->field('usernameactual').' '.$myClass->field('depe_actualfecha');
    }
}else{
        $bd_depe_max_x_recibir = 999999;
        $bd_depe_max_dias_x_recibir = 999999;
        $bd_depe_max_doc_proceso = 999999;
        $bd_depe_max_dias_doc_proceso = 999999;
        $bd_depe_superior = 0;
}

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerExternalFunction(array("buscarDepLegal", "dependenciaLegal","buscarDepLegal"),"");
$xajax->registerFunction("pedirRUC");
$xajax->registerFunction("pedirSerie");

function pedirRUC($op,$valor,$divName){
        global $conn,$bd_emru_id;
        $objResponse = new xajaxResponse();

        $oForm = new AddTableForm();
        $oForm->setLabelWidth("20%");
        $oForm->setDataWidth("80%");
        
        /*si se ha activado el check*/
        if(SIS_SISCORE==1 && ($valor==="true" || $valor==1)) {
            $sqlRUC=new clsEmpresaRUC_SQLlista();
            $sqlRUC->orderUno();
            $sql=$sqlRUC->getSQL_cbox();
//            if(!$bd_emru_id){
//                $bd_emru_id=getDbValue("SELECT emru_id FROM ($sql) AS a ORDER BY 1 LIMIT 1 ");
//            }
            $oForm->addField("RUC: ",listboxField("RUC",$sql,"tr_emru_id","$bd_emru_id","-- Seleccione RUC --"));
        }else{
            $oForm->addHidden("tx_emru_id",NULL);
        }
        

        $contenido_respuesta=$oForm->writeHTML();
        if($op==1){
            $objResponse->addAssign($divName,'innerHTML', $contenido_respuesta);            
            return $objResponse;
        }else{
            return $contenido_respuesta;
        }
}

function pedirSerie($op,$valor,$divName){
        global $bd_depe_serie;
                
        $objResponse = new xajaxResponse();

        $oForm = new AddTableForm();
        $oForm->setLabelWidth("20%");
        $oForm->setDataWidth("80%");
        
        if($valor==="true" || $valor==1) {
            $oForm->addField("Serie de Documentos:",textField("Serie de Documentos","Sr_depe_serie",$bd_depe_serie,5,3));        
        }else{
            $oForm->addHidden("Sr_depe_serie",NULL);
        }
        

        $contenido_respuesta=$oForm->writeHTML();
        
        if($op==1){
            $objResponse->addAssign($divName,'innerHTML', $contenido_respuesta);            
            return $objResponse;
        }else{
            return $contenido_respuesta;
        }
}

$xajax->processRequests();
// fin para Ajax

?>
<html>
    <head>
        <title>Dependencia-edici&oacute;n</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
      <script language="JavaScript" src="../../library/js/janela.js"></script>
        <script language="javascript" src="../../library/js/tree.js"></script>
        <script language="JavaScript" src="../../library/js/focus.js"></script>
        <script language="JavaScript" src="../../library/js/libjsgen.js"></script>
        <script language="javascript" src="../../library/js/lookup2.js"></script>
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>        
        <script language='JavaScript'>
            var patron = new Array(2,2,2)
            
            function salvar(idObj) {
                    if (ObligaCampos(frm)){
                            ocultarObj('Guardar',10)
                            document.frm.target = "content";
                            document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
                            document.frm.submit();
                    }
            }
            
            /*
                se invoca desde la funcion obligacampos (libjsgen.js)
                en esta función se puede personalizar la validación del formulario
                y se ejecuta al momento de gurdar los datos
             */
            function mivalidacion(frm) {
                return true
            }
            /*
                funci�n que define el foco inicial en el formulario
             */
            function inicializa() {
                parent.content.document.frm.Sr_depe_nombre.focus();
            }

            function RefreshFoto(name) {
                var formData = new FormData();
                        var files = $('#fil_img_'+name)[0].files[0];
                        formData.append('file',files);
                        $.ajax({
                            url: '../upload_tmp.php',
                            type: 'post',
                            data: formData,
                            contentType: false,
                            processData: false,
                            success: function(response) {
                                if (response != 0) {
                                    $("#div_img_"+name).attr("src", response);
                                } else {
                                    alert('Formato de imagen incorrecto.');
                                }
                            }
                        });        
            }
            //function submit() {
            //    parent.content.document.frm.submit();
            //}


        </script>
        <?php
        verif_framework(); 
        $xajax->printJavascript(PATH_INC.'ajax/');         
        ?>

    </head>
    <body class="contentBODY" onLoad="inicializa()" >
        <?php
        
/*
	botones,
	configure conforme suas necessidades
*/
        $retorno = $_SERVER['QUERY_STRING'];

        $button = new Button;
        if(strlen($id)>0 && $id == 0) {}
        else{
            $button->addItem("Guardar","javascript:salvar()","content",2);
        }
        
        $button->addItem(" Regresar ","catalogosDependencias_lista.php?clear=1","content");
        echo $button->writeHTML();

/*
	Control de abas,
	true, se for a aba da página atual,
	false, se for qualquer outra aba,
	configure conforme al exemplo de abajo
*/
        $abas = new Abas();
        $abas->addItem("General",true);


        echo $abas->writeHTML();

        echo "<br>";

/*
	Formulario
*/
        $form = new Form("frm", "", "POST", "controle", "100%", true);
        $form->setLabelWidth("20%");
        $form->setDataWidth("80%");
        $form->setUpload(true);

        $form->addHidden("rodou","s"); // variable de control
        $form->addHidden("f_id",$bd_depe_id); // clave primaria
        $form->addHidden("pagina",getParam("pagina")); // numero de página que llamo

        if($bd_depe_id){
            $form->addField("C&oacute;digo:","<font size=2px>".$bd_depe_id."</font>");
        }
            
        $form->addField("Dependencia:",textField("Dependencia","Sr_depe_nombre",$bd_depe_nombre,80,80));
        $form->addField("Nombre Breve:",textField("Nombre Breve","Sr_depe_nombrecorto",$bd_depe_nombrecorto,30,30));
        $form->addField("Direcci&oacute;n:",textField("Direccion","Sx_depe_direccion",$bd_depe_direccion,80,100));
                
        if($bd_depe_superior==1 && $bd_depe_id){
            $form->addHidden("postPath",'catalogos/'.SIS_EMPRESA_RUC.'/'.$bd_depe_id);
            $form->addDivImage(divImage("file_logo",iif($bd_file_logo,"==","","standar_image.jpg",$bd_file_logo), 140,120, 120, 200, 500, "contenedorDependencia", "onchange=\"validaextension(this,'PNG');RefreshFoto(this.name)\"",iif($bd_file_logo,"==","","../../img/",PUBLICUPLOAD.'catalogos/'.SIS_EMPRESA_RUC.'/'.$bd_depe_id.'/')));
        }
        
        if(SIS_GESTDOC==1){
            $form->addField("Siglas P/Doc.Emitidos:",textField("Siglas P/Doc.Emitidos","Sr_depe_siglasdoc",$bd_depe_siglasdoc,30,30));
            //$form->addField("Siglas P/Resoluciones:",textField("Siglas P/Resoluciones","Sx_depe_siglasresolucion",$bd_depe_siglasresolucion,30,30));
        }

        // definición de arbol para dependencia superior
        $depen = new Tree();
        $depen->setTitle("Jerarqu&iacute;a de Dependencias");
        $depen->setTreeAvanz(true); //utiliza el tree mejorado
        $depen->setNameCampoForm("Dependencia Superior","nr_depe_depeid");
        $depe_id=getSession("sis_depe_superior");
        $depen->setNameTabla("func_treedependencia($depe_id,_SEARCH_)");  //nombre de la funcion ->OJO ABRE Y ENVO UN PARAMETRO, DEBIDO A QUE SE TIENE QUE INCLUIR EL VALOR DE BUSUQUEDA
        $valor=getDbValue("SELECT depe_nombre FROM dependencia WHERE depe_id=$bd_depe_depeid");
        $depen->setValorCampoForm($bd_depe_depeid,$valor) ;
        //FIN definición de arbol

        $form->addField("Dependencia Superior: ",$depen->writeHTML());

        if(SIS_SISLOGAL==1 && $bd_depe_superior==0){
            $form->addField(checkboxField("Es 'Almacen'","hx_depe_almacen",1,$bd_depe_almacen==1),"Establecer 'es Almac&eacute;n' para Recibir Ordenes de Compra");
        }        

        if(SIS_SISCORE==1 && $bd_depe_superior==0){ //Ingresos
            if (SIS_EMPRESA_TIPO==4){//Empresa tipo Almacen
                //$form->addField(checkboxField("Es 'Establecimiento P/Almac&eacute;n'","hx_depe_almacen_ventas",1,$bd_almacen_ventas==1),"Establecer 'es Establecimiento' para Almac&eacute;n");
                $form->addField(checkboxField("Es 'Establecimiento P/Almac&eacute;n'","hx_depe_almacen_ventas",1,$bd_almacen_ventas==1,"onClick=\"xajax_pedirSerie(1,this.checked,'divSerie')\""),"Establecer 'es Establecimiento' para Almac&eacute;n");
                
                $form->addHtml("<tr><td colspan=2><div id='divSerie' >\n"); //pide serie
                $form->addHtml(pedirSerie(2,$bd_almacen_ventas,'divSerie'));
                $form->addHtml("</div></td></tr>\n");            

            }else{
                $form->addField(checkboxField("Es 'Establecimiento P/Compras/Ventas'","hx_depe_almacen_ventas",1,$bd_almacen_ventas==1),"Establecer 'es Establecimiento' para Compras/Ventas");
            }
        }

        if(SIS_GESTCNE==1 && $bd_depe_superior==0){ //cuadro de necesidades
            if(SIS_GESTMED==1){
                $form->addField(checkboxField("Es 'Centro Medico'","hx_depe_centro_medico",1,$bd_depe_centro_medico==1),"Establecer 'es Centro M&eacute;dico' para Generar Solicitudes de Servicios de M&eacute;dicos");
            }

            $form->addField(checkboxField("Es 'Abastecimiento'","hx_depe_logistica",1,$bd_depe_logistica==1),"Establecer 'es Abastecimiento' para Autorizar Solicitudes de B&S");
            $form->addField(checkboxField("Es 'Administracion/Gerencia'","hx_depe_administracion",1,$bd_depe_administracion==1),"Establecer 'es Administraci&oacute;n/Gerencia' para Autorizar Solicitudes de B&S");
            $form->addField(checkboxField("Es 'Area de Apoyo a Contrataciones'","hx_depe_contrataciones",1,$bd_depe_contrataciones==1),"Establecer 'es Area de Apoyo a Contrataciones' para Establecerlo como Lugar de Evaluaciones");            

            if(SIS_EMPRESA_TIPO==3){ //beneficencia
                $form->addField(checkboxField("Es 'Area de Nutricion'","hx_depe_nutricion",1,$bd_depe_nutricion==1),"Establecer 'es Area de Nutrici&oacute;n'");
            }
        }

        if(SIS_SISCAJA==1 && $bd_depe_superior==0){ //caja Fondo para pagos
            $form->addField(checkboxField("Es 'Rindente'","hx_depe_rindente",1,$bd_depe_rindente==1),"Establecer 'es Dependencia Rindente' de Caja");
        }

        if(SIS_PLANILLAS==1 && $bd_depe_superior==0){ //caja Fondo para pagos
            $form->addField(checkboxField("Es 'Recursos Humanos'","hx_depe_rrhh",1,$bd_depe_rrhh==1),"Establecer 'es Recursos Humanos' para Firma de Boletas de Pagos");
        }

        if(SIS_GESTDOC==1 && $bd_depe_superior==0){//si es sistema de tamite
            $form->addField(checkboxField("Es 'Mesa de Partes/Tr&aacute;mite Documentario'","hx_depe_mesa_partes",1,$bd_depe_mesa_partes==1),"Establecer 'es Mesa de Partes/Tr&aacute;mite Documentario' ");
            //$form->addField(checkboxField("Es 'Mesa de Partes Virtual'","hx_depe_mesa_partes_virtual",1,$bd_depe_mesa_partes_virtual==1),"Establecer  'es Mesa de Partes Virtual' ");
        }

        $form->addField(checkboxField("Es 'Dependencia de Nivel Superior'","hx_depe_superior",1,$bd_depe_superior==1,"onClick=\"xajax_pedirRUC(1,this.checked,'divRUC')\""),"Establecer 'es Dependencia de Nivel Superior'");        
        $form->addHtml("<tr><td colspan=2><div id='divRUC' >\n"); //pide datos judiciales
        $form->addHtml(pedirRUC(2,$bd_depe_superior,'divRUC'));
        $form->addHtml("</div></td></tr>\n");            

        if (SIS_EMPRESA_TIPO!=4 && strlen($id)>0){//EMPRESA para ALMACEN
            $form->addField("Jefe/Responsable: ",$bd_jefe_dni);        
            if($bd_pdla_cargofuncional){
                $form->addField("Cargo: ",$bd_pdla_cargofuncional);
            }
        }
        //$form->addField("Tel&eacute;fono: ",textField("Tel&eacute;fono","Sx_depe_telefonos",$bd_depe_telefonos,12,12));

//        // definición de lookup Empleados
//        $empleado= new Lookup();
//        $empleado->setTitle("Empleados");
//        $empleado->setNomeCampoChave("pdla_id");
//        $empleado->setNomeCampoForm("Jefe","nx_pdla_id");
//        $sqlEmpleado=new clsDatosLaborales_SQLlista();
//        $sql = "SELECT pdla_id,empleado,sit_laboral,pdla_cargofuncional
//                FROM (".$sqlEmpleado->getSQL().") AS  a";
//        setSession("sqlLkupEmp", $sql);
//        $empleado->setNomeTabela("sqlLkupEmp");  //nombre de tabla
//        $empleado->setNomeCampoExibicao("empleado");  // Campos en los que deseo se efect�e la b�squeda.
//        $empleado->setUpCase(true);//para busquedas con texto en mayuscula
//        $empleado->setListaInicial(0);
//        $empleado->setSize(70);
//        $empleado->setValorCampoForm($bd_pdla_id);

//        $form->addField("Jefe/Responsable: ",$empleado->writeHTML());
        // FIN definición de lookup Empleados



        if($bd_depe_id==2){//DATOS DE LA EMPRESA
//            $form->addField("Imagen de Cabecera:",fileField("Imagen de Cabecera","file_cabecera" ,$bd_depe_imagen_cabecera,60,"onchange=validaextension(this,'GIF,JPG,PNG')"));
//            $form->addField("T&iacute;tulo: ",textField("Titulo","Sx_depe_titulo",$bd_depe_titulo,80,80,""));
//            $form->addField("Sub T&iacute;tulo: ",textField("Sub Titulo","Sx_depe_sub_titulo",$bd_depe_sub_titulo,80,80,""));
//            $form->addField("Fecha y Hora de Cierre (dd-mm-yyyy hh:mm:ss):",textField("Fecha y Hora de Cierre ","Sx_depe_fcierre",$bd_depe_fcierre ,19,19));
        }

        if(SIS_GESTDOC==1){
            $form->addField("M&aacute;x.de doc 'por recibir': ",numField("Max.doc.por recibir","nr_depe_max_x_recibir",$bd_depe_max_x_recibir,6,6,0));
            $form->addField("M&aacute;x.de d&iacute;as de doc 'por recibir': ",numField("Max.dias.doc.sin recibir","nr_depe_max_dias_x_recibir",$bd_depe_max_dias_x_recibir,6,6,0));

            $form->addField("M&aacute;x.de doc 'en proceso': ",numField("Max.doc.en proceso","nr_depe_max_doc_proceso",$bd_depe_max_doc_proceso,6,6,0));
            $form->addField("M&aacute;x.de d&iacute;as de doc 'en proceso': ",numField("Max.dias doc.en proceso","nr_depe_max_dias_doc_proceso",$bd_depe_max_dias_doc_proceso,6,6,0));

        }

        
        
        if(SIS_MPV==1 && $bd_depe_superior==1){//SI ES MESA DE PARTES VIRTUAL            

            $form->addBreak("<b>PROGAMACION DE VIGENCIA-MPV (Formato 24H)</b>");
            $form->addField("Lunes-Viernes: ",textField("Lunes-Viernes Desde","Sx_depe_lunes_viernes_desde",$bd_depe_lunes_viernes_desde,8,8,"onkeyup=mascara(this,':',patron,true) ").
                                        "&nbsp;-&nbsp;".textField("Lunes-Viernes Hasta","Sx_depe_lunes_viernes_hasta",$bd_depe_lunes_viernes_hasta,8,8,"onkeyup=mascara(this,':',patron,true)"));
                        
            $form->addField("S&aacute;bado: ",textField("Sabado Desde","Sx_depe_sabado_desde",$bd_depe_sabado_desde,8,8,"onkeyup=mascara(this,':',patron,true) ").
                                        "&nbsp;-&nbsp;".textField("Sabado Hasta","Sx_depe_sabado_hasta",$bd_depe_sabado_hasta,8,8,"onkeyup=mascara(this,':',patron,true)"));   
            
            
            $form->addField("Domingo: ",textField("Domingo Desde","Sx_depe_domingo_desde",$bd_depe_domingo_desde,8,8,"onkeyup=mascara(this,':',patron,true) ").
                                        "&nbsp;-&nbsp;".textField("Domingo Hasta","Sx_depe_domingo_hasta",$bd_depe_domingo_hasta,8,8,"onkeyup=mascara(this,':',patron,true)"));               
           
            $form->addField("Mensaje de Horario Cierre: ",textAreaField("Mensaje de Horario Cierre","er_depe_mpv_mensaje_externo",$bd_depe_mpv_mensaje_externo,5,100,0,"",0));
            
            $form->addField("Mensaje P/Despu&eacute;s de Registro: ",textAreaField("Mensaje P/Despues de Registro","er_depe_mpv_mensaje_registro",$bd_depe_mpv_mensaje_registro,5,100,0,"",0));            
                    
            $form->addBreak("<b>MENSAJE PARA NOTIFICACIONES (e-mail)</b>");
            $form->addField("",textAreaField("Mensaje para Notificaciones","ex_depe_mpv_mensaje_notificaciones",$bd_depe_mpv_mensaje_notificaciones,5,100,0,"",0));
        
            $form->addField("URL video Tutorial:",textField("URL video Tutorial","sx_depe_video_mpv",$bd_depe_video_mpv,80,80));            
        }
                
        if(SIS_SISCORE==1 && SIS_SISCONT==1){        
            $tabla=new clsTabla_SQLlista();
            $tabla->whereTipo('TIPO_BASE_PLE');
            $tabla->orderUno();
            $sqlTipoBase = $tabla->getSQL_cbox();
            $form->addField("Tipo Base-IGV: ", listboxField("Tipo Base-IGV", $sqlTipoBase, "nx_tabl_tipo_base", $myClass->field("tabl_tipo_base"),"Seleccione Tipo Base-IGV"));
        }

        if(SIS_SISCONT==1){
            $form->addBreak("<b>INTEGRACION A CONTABILIDAD</b>");            
            $form->addField("Cuenta Contable Patr&oacute;n:",textField("Cuenta Contable Patron","Sx_depe_cuenta_patron",$bd_depe_cuenta_patron,10,10));
        }
        

        
        if(strlen($id)>0) {
            $form->addField("Habilitado",checkboxField("Habilitado","hx_depe_habiltado",1,$bd_depe_habiltado==1));
            
            $form->addBreak("<b>Control</b>");
            
            if (SIS_EMPRESA_TIPO!=4){//EMPRESA para ALMACEN
                $form->addField("Encript: ", base64_encode($id));
            }
            
            $form->addField("Creado por: ",$username);
            $form->addField("Actualizado por: ",$usernameactual);
        }else{
            $form->addHidden("hx_depe_habiltado",1);
        }

        echo $form->writeHTML();
        ?>
    </body>
</html>
<?php
/*
	cierro la conexión a la BD
*/
$conn->close();
