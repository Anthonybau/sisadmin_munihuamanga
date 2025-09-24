<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");
require_once("getAcumDespacho_class.php");

class despachoRecibir extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='despachos_derivaciones'; //nombre de la tabla
		$this->setKey='dede_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

		/* Destinos luego de actulizar o agregar un registro */
		$this->destinoUpdate = "recibirDespacho_buscar.php";
		$this->destinoInsert = "recibirDespacho_buscar.php";
		$this->destinoDelete = "recibirDespacho_buscar.php";
	}


	function buscar($op,$formData,$arrayParam,$pg,$Nameobj='')

	{
		global $conn,$param;
		$objResponse = new xajaxResponse();
		
		$arrayParam=decodeArray($arrayParam);

		$paramFunction= new manUrlv1();
		$paramFunction->setUrlExternal($arrayParam);

		$cadena=is_array($formData)?trim(strtoupper($formData['Sbusc_cadena'])):$formData;
		if(!$cadena && $op==2) $cadena=getSession("cadSearch");
		
		$colSearch=$paramFunction->getValuePar('colSearch');
		$colOrden=$paramFunction->getValuePar('colOrden');
		$busEmpty=$paramFunction->getValuePar('busEmpty');
		$nbusc_depe_id=is_array($formData)?$formData['nbusc_depe_id']:$paramFunction->getValuePar('nbusc_depe_id');
                $nbusc_user_id=is_array($formData)?$formData['nbusc_user_id']:$paramFunction->getValuePar('nbusc_user_id');

                $param->removePar('clear');
                $param->removePar('nbusc_depe_id');
                $param->removePar('nbusc_user_id');
                
                $param->addParComplete("nbusc_depe_id", $nbusc_depe_id);
                $param->addParComplete("nbusc_user_id", $nbusc_user_id);
                $tiex_id=is_array($formData)?$formData['nbusc_tiex']:$paramFunction->getValuePar('nbusc_tiex');

		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	

			$sql=new despachoRecibir_SQLlista();
			$sql->whereDepeDestinoID($nbusc_depe_id);
                        $sql->whereEstado(2);//derivados
                        
                        if($nbusc_user_id){
                            $sql->whereUsuaDestinoID($nbusc_user_id);
                        }

                        if($tiex_id){
			    $sql->whereTiExpID($tiex_id);
			}
			//se analiza la columna de busqueda
                        if($cadena)
			switch($colSearch){
				case 'codigo': // si se recibe el campo id
					$sql->whereID($cadena);
					break;
	
				default:// si se no se recibe ningun campo de busqueda
					if(is_numeric($cadena)){ //si la cadena recibida son todos digitos
                                            $sql->whereID($cadena);
                                        }else{
                                            //$sql->whereDescrip($cadena);
                                        }
                                        break;
				}
			$sql->orderDos();
			
			$sql=$sql->getSQL();
			
			//$objResponse->addAlert($sql);
		
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) setSession("cadSearch",$cadena);			
	
			$rs = new query($conn, strtoupper($sql), $pg, 40);
                        
			$otable = new  Table("","100%",6);
                        
			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
                        
                        
			if ($rs->numrows()>0) {
					$otable->addColumnHeader("<input type=\"checkbox\" class=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">"); // Coluna com checkbox
					$otable->addColumnHeader(NAME_EXPEDIENTE,false,"4%", "L");
                                        //$otable->addColumnHeader("Di",false,"2%", "L");
                                        $otable->addColumnHeader("Fecha",false,"5%", "C"); 
					$otable->addColumnHeader("TExp",false,"4%", "L");
					$otable->addColumnHeader("N&uacute;mero",false,"20%", "C");
                                        $otable->addColumnHeader("Procedencia:",false,"22%", "C");
					$otable->addColumnHeader("Asunto",false,"40%", "C");
                                        $otable->addColumnHeader("Adjuntados",false,"10%", "C");
                                        $otable->addColumnHeader("Usu.Destino",false,"5%", "L");
					$otable->addRow(); // adiciona la linea (TR)
					while ($rs->getrow()) {
						$idPadre = $rs->field("id"); // captura la clave de la tabla padre
                                                //$idAdjuntados = $rs->field("desp_adjuntados_nvos");
                                                //$idAdjuntados=$idAdjuntados?','.$idAdjuntados:'';
                                                $id=$rs->field("dede_id");
						$otable->addData("<input type=\"checkbox\" class=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");
                                                                                                
                                                $otable->addData(addLink($idPadre,"javascript:lectorPDF('/sisadmin/gestdoc/controllers/procesar_data.php?nr_numTramite=$idPadre&vista=NoConsulta','Seguimiento')","Click aqu&iacute; para Seguimiento de registro"));                                                
                                                //$otable->addData(addLink($idPadre,"javascript:abreConsulta('$idPadre')","Click aqu&iacute; para Seguimiento de registro"));

//                                                if($rs->field("dede_concopia")) 
//                                                    $otable->addData("Cc");
//                                                else
//                                                    $otable->addData("");

                                                $otable->addData(dtos($rs->field("desp_fecha")));
                                                $otable->addData($rs->field("tiex_abreviado"));
                                                
                                                $num_documento=$rs->field("num_documento");
//                                                $periodo=$rs->field('desp_anno');
//                                                $name_file=$rs->field('desp_file_firmado');
//                                                $nameFileFullPath = "../../../../docs/gestdoc/".SIS_EMPRESA_RUC."/$periodo/$idPadre/$name_file";
//                                                $verDocumento=conPermisoVerDocumento($idPadre,$rs->field("usua_id"),0,0,0,0);
                                                  //echo file_exists($nameFileFullPath);
//                                                if( file_exists($nameFileFullPath) && $name_file && $verDocumento==1 ){
//                                                    $link=addLink($num_documento,"#","Click aqu&iacute; para Ver Documento","controle","link download-link",$idPadre);
//
//                                                    //$link=addLink($num_documento,"javascript:imprimir('$id')","Click aqu&iacute; para Ver Documento","controle");
//                                                }else{
                                                    $link=$num_documento;
//                                                }
                    
						$otable->addData($link);
                                                $otable->addData($rs->field("depe_nombrecorto_origen").'/'.$rs->field("depe_superior_nombre_origen"));
                                                
                                                $depeid=getSession("sis_depeid");
                                                $otable->addData(addLink(substr($rs->field("desp_asunto"),0,40)."...<img src=\"../../img/mas_info.gif\" width=\"14\" height=\"9\" align=\"absmiddle\" border=\"0\">","javascript:xajax_verDetalle(1,'$idPadre','recibe',$depeid,'$id',0)","Click aqu&iacute; para ver detalles del registro"));
                                                //$otable->addData(substr($rs->field("desp_asunto"),0,80));
                                                $otable->addData($rs->field("desp_adjuntados_exp"));
                                                $otable->addData($rs->field("usuario_destino"));
						$otable->addRow();
					}
                                        
				$contenido_respuesta=$button->writeHTML();                                        
				$contenido_respuesta.=$otable->writeHTML();
				$contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

			} else {
				$otable->addColumnHeader("!NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
				$otable->addRow();
				$contenido_respuesta=$otable->writeHTML();
			}
		}
	else
		$contenido_respuesta="";
	
		//se analiza el tipo de funcionamiento
		if($op==1){//si es llamado para su funcionamiento en ajax con retornoa a un div
			$objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
			return $objResponse;
			}
		else
			return $contenido_respuesta	;
	}

        function recibir(){
		global $conn,$param;
                $param->removePar('clear');

		$destinoRecibir=$this->destinoUpdate.$param->buildPars(true)."&clear=1";

		/* captura y prepara la lista de registros a ser eliminados */
		$arLista = getParam("sel");
		if (is_array($arLista)) {
		 $lista = implode(",",$arLista);
		}else{
                    alert("Sin Registros Seleccionados para procesar...");
                    return;
                }
                
                $EstadDespDepen=new getAcumDespachosDependencia();
                $EstadDespDepen->whereDepeID(getSession("sis_depeid"));
                $EstadDespDepen->setDatos();

                $depe_acum_despachos_enproceso=$EstadDespDepen->field("depe_acum_despachos_enproceso");
                $depe_max_doc_proceso=$EstadDespDepen->field("depe_max_doc_proceso");
                $depe_max_dias_doc_proceso=$EstadDespDepen->field("depe_max_dias_doc_proceso");
                $ok=1;
                if($depe_acum_despachos_enproceso>$depe_max_doc_proceso){
                    $titulo="IMPOSIBLE CONTINUAR...!!!";
                    $msj=getSession("sis_depename"). " tiene $depe_acum_despachos_enproceso Documento en Proceso <br>Supera el Maximo Permitido ($depe_max_doc_proceso)";
                    $destino="mensajeArgumento.php?titulo=$titulo&msj=$msj";
                    redirect($destino);
                    $ok=0;
                }

                //APLICA EL FILTRO SOLO SI EL VALOR HA SIDO CAMBIADO
                if($depe_max_dias_doc_proceso<999999){
                        $EstadMaxDiasenProceso=new getAcumDespachosMaxDiasProceso(getSession("sis_depeid"),$depe_max_dias_doc_proceso);
                        $EstadMaxDiasenProceso->setDatos();
                        $acum_max_dias_proceso=$EstadMaxDiasenProceso->field("acum_max_dias_proceso");
                        if($acum_max_dias_proceso>0){
                            $titulo="IMPOSIBLE CONTINUAR...!!!";
                            $msj=getSession("sis_depename"). " tiene $acum_max_dias_proceso Documento(s) en Proceso de Muchos dias<br>Supera el Maximo de dias Permitido ($depe_max_dias_doc_proceso)";
                            $destino="mensajeArgumento.php?titulo=$titulo&msj=$msj";
                            redirect($destino);    
                            $ok=0;
                        }
                }
                
                if($ok==1){
                    if(strtolower($this->typeKey)=='string'){
                            /* debido a que el campo clave es char */
                            $lista=str_replace(",","','",$lista);
                    }
                    $hh_recibe=date('d/m/Y').' '.date('H:i:s');
                    /* Sql a ejecutar */
                    /* en procesoDespacho_buscar.php se encuentra el procedimiento para archivar con codigo parecido a este*/
                    $sqlCommand ="UPDATE $this->setTable SET dede_estado=3,
                                    usua_idrecibe=".getSession("sis_userid").",dede_fecharecibe=NOW()
                                    WHERE dede_id IN ($lista) 
                                          AND (usua_iddestino IS NULL OR usua_iddestino=".getSession("sis_userid").") 
                                    RETURNING dede_fecharecibe" ;

                    /* Ejecuto la sentencia */
                    $dede_fecharecibe=$conn->execute($sqlCommand);
                    $dede_fecharecibe=_dttos($dede_fecharecibe);
                    
                    $error=$conn->error();
                    if($error) alert($error);
                    else{
                        if($dede_fecharecibe){
                            
                            foreach ($arLista as $id) {

                                $sql="SELECT b.desp_id,
                                             b.desp_procesador,
                                             b.desp_firma,
                                             b.desp_email,
                                             c.proc_plazo_dias
                                        FROM gestdoc.despachos_derivaciones a
                                        LEFT JOIN gestdoc.despachos b ON a.desp_id=b.desp_id
                                        LEFT JOIN gestdoc.procedimiento c ON b.proc_id=c.proc_id
                                        WHERE a.dede_id=$id;";

                                $despacho = new query($conn, $sql);
                                $despacho->getrow();
                                        
                                if( $despacho->field('desp_procesador')==2 ){
                                    $desp_idx = $despacho->field('desp_id');
                                    $persona_nombre = $despacho->field('desp_firma');
                                    $persona_email = $despacho->field('desp_email');
                                    $proc_plazo_dias = $despacho->field('proc_plazo_dias');
                                    
                                    if( $proc_plazo_dias>0 ){
                                        $mensaje="Sera atendido en plazo máximo de $proc_plazo_dias días.";
                                    }else{
                                        $mensaje="";
                                    }
                                    $mensaje.="<BR><a href='".PATH_PORT."gestdoc/index.php' target='_blank'>Consulte el estado de su Solicitud Aquí</a>";
                                    //
                                    //
                                    //ENVIO DE CORREO
                                    $posDomain = stripos($_SERVER['SERVER_NAME'], 'mytienda.page');    

                                    if($posDomain === false) { 
                                        set_include_path(get_include_path().
                                                PATH_SEPARATOR.$_SERVER['DOCUMENT_ROOT']."/library");


                                    }else{

                                        defined('APPLICATION_PATH')
                                                || define('APPLICATION_PATH', '/home/lguevara/zfappMytienda'); 

                                        set_include_path(implode(PATH_SEPARATOR, array(
                                                realpath(APPLICATION_PATH . '/library'),
                                                get_include_path(),
                                        )));
                                    }

                                    require_once 'Zend/Loader/Autoloader.php';
                                    $loader = Zend_Loader_Autoloader::getInstance();
                                    $loader->setFallbackAutoloader(true);
                                    $loader->suppressNotFoundWarnings(false);

                                    $email_gmail=trim(SIS_EMAIL_GMAIL);
                                    $pass_email_gmail=trim(SIS_PASS_EMAIL_GMAIL);
                                    $email_servidor=trim(SIS_EMAIL_SERVIDOR);
                                    $email_from=trim(SIS_EFACT_EMAIL_FROM);

                                    $posGmail = stripos($email_gmail, 'gmail');    

                                    if($posGmail === false) { /* Si no se está usando el Gmail */

                                        $config = array('auth' => 'login',
                                                'username' => $email_gmail,
                                                'password' => $pass_email_gmail,'ssl' => 'tls','port' => 587);
                                        $mailTransport = new Zend_Mail_Transport_Smtp($email_servidor,$config);
                                    } else {

                                        $config = array('auth' => 'login',
                                                'username' => $email_gmail,
                                                // in case of Gmail username also acts as mandatory value of FROM header
                                                'password' => $pass_email_gmail,'ssl' => 'tls','port' => 587);
                                        $mailTransport = new Zend_Mail_Transport_Smtp('smtp.gmail.com',$config);
                                    }

                                    Zend_Mail::setDefaultTransport($mailTransport);

                                    $subject_flujo=utf8_decode("SOLICITUD RECIBIDA! N° de Trámite $desp_idx");

                                    $email=$persona_email;
                                    $empleado=$persona_nombre;
                                    if($email){
                                        $mail = new Zend_Mail();

                                        $mail->setBodyHtml(utf8_decode($empleado." </b>		
                                                                                <br>Tu solicitud ha generado el número de registro <b>$desp_idx</b> y ha sido recibido el día $dede_fecharecibe 
                                                                                <br>$mensaje
                                                                                <br><br>
                                                                                <b>Enviado Desde:</b> Sistema de Informaci&oacute;n-".SIS_EMPRESA.
                                                                                 "<br><b>IMPORTANTE:</b> NO responda a este Mensaje"))

                                            ->setFrom($email_from,'SISADMIN '.SIS_EMPRESA_SIGLAS)
                                            ->setSubject($subject_flujo)
                                            ->addTo($email, 'Solicitante');


                                        try {
                                            $ahora=date('d/m/Y h:i:s');                                    
                                            $mail->send();
                                        } catch (Exception $e) {
                                                //$mensaje = 'Error al enviar el Correo...Por favor, Comunicarse con el Area de Soporte Informático  <br>
                                                //                    Su mensaje de error es: <br>
                                                //                    '.$e->getMessage();
                                                $mensaje =  $e->getMessage();
                                                //alert($mensaje);
                                        }
                                        //alert($mensaje);
                                        //echo $mensaje;
                                    }
                                    //FIN DE ENVIO DE CORREO                                    
                                    
                                }                                
                            }
                            
                            redirect($destinoRecibir.'&lista='.$lista.'&hh_recibe='.$dede_fecharecibe,"content");
                        }else{
                            alert('Registro no puede ser recibido por Usted!!');
                        }
                    }
                }
            }
	

	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	
}

class despachoRecibir_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT      b.desp_id::TEXT as id,
                                        a.*,
                                        LPAD(b.desp_numero::TEXT,6,'0')||'-'||b.desp_anno||COALESCE('-'||b.desp_siglas,'') AS num_documento,
                                        b.desp_anno,
                                        b.desp_fecha,
                                        b.desp_asunto,
                                        b.desp_adjuntados_exp,
                                        b.desp_adjuntados_id,
                                        b.desp_file_firmado,
                                        c.tiex_abreviado,
                                        c.tiex_descripcion,
                                        d.depe_nombrecorto as depe_nombrecorto_origen,d.depe_nombre as depe_nombre_origen,
                                        (SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(a.depe_idorigen)) AS depe_superior_nombre_origen,
                                        CASE WHEN e.usua_mesa_partes_virtual=1 THEN b.desp_codigo||' '||b.desp_firma
                                             ELSE e.usua_login||'-'||eee.pers_apellpaterno||' '||SUBSTRING(eee.pers_nombres,1,CASE WHEN POSITION(' ' IN eee.pers_nombres)>0 THEN POSITION(' ' IN eee.pers_nombres) ELSE 100 END) 
                                        END AS usuario_origen,
                                        
                                        f.usua_login||'-'||fff.pers_apellpaterno||' '||SUBSTRING(fff.pers_nombres,1,CASE WHEN POSITION(' ' IN fff.pers_nombres)>0 THEN POSITION(' ' IN fff.pers_nombres) ELSE 100 END) AS usuario_destino,
					x.usua_login as username
				FROM gestdoc.despachos_derivaciones a
                                LEFT JOIN gestdoc.despachos b ON a.desp_id=b.desp_id
                                LEFT JOIN catalogos.tipo_expediente c ON b.tiex_id=c.tiex_id
                                LEFT JOIN catalogos.dependencia d ON a.depe_idorigen=d.depe_id
                                
                                LEFT JOIN admin.usuario e  ON a.usua_idorigen=e.usua_id
                                LEFT JOIN personal.persona_datos_laborales ee on  e.pdla_id=ee.pdla_id
                                LEFT JOIN personal.persona eee on  ee.pers_id=eee.pers_id                                
                                
                                LEFT JOIN admin.usuario f  ON a.usua_iddestino=f.usua_id
                                LEFT JOIN personal.persona_datos_laborales ff on  f.pdla_id=ff.pdla_id
                                LEFT JOIN personal.persona fff on  ff.pers_id=fff.pers_id                                
                                
				LEFT JOIN admin.usuario x ON a.usua_idcrea=x.usua_id
				";
	
	}
	
	function whereID($id){
                //si se ha enviado con secuencia de expediente, es decir en decimal
                if($id>intval($id))
                    $this->addWhere("b.desp_id=$id");
                else 
                    $this->addWhere("b.desp_expediente=$id");
	}

        function whereEstado($estado){
		$this->addWhere("a.dede_estado=$estado");
	}

        function whereDepeDestinoID($depe_id){
		$this->addWhere("a.depe_iddestino=$depe_id");
	}

	function whereTiExpID($tiex_id){
		$this->addWhere("b.tiex_id=$tiex_id");
	}

        function whereUsuaDestinoID($usua_id){
		$this->addWhere("a.usua_iddestino=$usua_id");
	}

        function orderUno(){
		$this->addOrder("b.desp_id_orden DESC");
	}
        
        function orderDos(){
		$this->addOrder("a.dede_id DESC,b.desp_id_orden DESC");
	}
        
	function orderUnox(){
		$this->addOrder("b.desp_fecha DESC,b.desp_id DESC");
	}

}

if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
            include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
            /*	verificaci�n a nivel de usuario */
            verificaUsuario(1);
            verif_framework();

            $param= new manUrlv1();
            $param->removePar('control');
            $param->removePar('relacionamento_id');
            $param->removePar('pg'); /* Remuevo el par�metro */

            /* Recibo la página actual de la lista y lo agrego como par�metro a ser enviado por la URL.  Este dato viene por POST */
            $pg = getParam("pg");
            $param->addParComplete('pg',$pg); /* Agrego el par�metro */

            //	conexión a la BD
            $conn = new db();
            $conn->open();

            $dml=new despachoRecibir();

            switch($control){
                    case 1: // Guardar
                            $dml->guardar();
                            break;
                    case 2: // Recibir
                            $dml->recibir();
                            break;
            }
            //	cierra la conexión con la BD
            $conn->close();
    }
}