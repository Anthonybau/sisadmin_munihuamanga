<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/selectSQL.php");

class clsDespachosEnvios_SQLlista extends selectSQL {

	function __construct(){
		$this->sql= "SELECT a.*,
                                    x.usua_login
				FROM gestdoc.despachos_envios a 
				LEFT JOIN admin.usuario x         ON a.usua_id=x.usua_id 					
                            ";

					
	}

	function whereID($id){
		$this->addWhere("a.deen_id=$id");	
	}

	function wherePadreID($padre_id){
		$this->addWhere("a.desp_id=$padre_id");	
	}
        
	function orderUno(){
		$this->addOrder("a.deen_id DESC");
	}

}


function enviar_email($op,$desp_id,$desp_expediente,$NameDiv)
{
    global $conn;
    $objResponse = new xajaxResponse();
    $sql="SELECT b.tiex_descripcion||' '||LPAD(a.desp_numero::TEXT,6,'0')||'-'||a.desp_anno||COALESCE('-'||a.desp_siglas,'') AS num_documento,
                 a.desp_email,
                 a.desp_anno,
                 a.desp_procesador,
                 a.desp_file_firmado,
                 a.desp_firma
           FROM gestdoc.despachos a 
           LEFT JOIN catalogos.tipo_expediente b ON a.tiex_id=b.tiex_id ";
    
    if($desp_id){
        $sql.=" WHERE a.desp_id='$desp_id' ";            
        
    }else{
        $sql.=" WHERE a.desp_expediente=$desp_expediente ";
        
    }    
           $sql.=" ORDER BY a.desp_id 
                   LIMIT 1";
    
    $rsDespacho = new query($conn, $sql);
    $rsDespacho->getrow();
    $periodo=$rsDespacho->field('desp_anno');
    $name_file=$rsDespacho->field('desp_file_firmado');    
    $desp_email=$rsDespacho->field('desp_email');
    $desp_email=$desp_email?$desp_email:'';          
    $num_documento=$rsDespacho->field('num_documento');
    $desp_firma=$rsDespacho->field('desp_firma');
    
    $otable = new AddTableForm();
    $otable->addBreak("<b>INGRESE CORREO PARA ENVIO DE REGISTRO $desp_id</b>");
//    $sqlProveedor=new proveedor_SQLlista();
//    $sqlProveedor->whereID($prov_id);
//    $sqlProveedor->setDatos();
    
//    $bd_prov_email=$sqlProveedor->field('prov_email');

    $otable->addField("Email: ",textField("Email","cx_prov_email",$desp_email,70,100));
    
    if($op==3){//PREPARA MENSAJE, VALIDACION DE REQUISTOS
        $procedimientoRequisitos=new despachoRequisitos_SQLlista();
        $procedimientoRequisitos->wherePadreID("$desp_id");
        $procedimientoRequisitos->whereNoCumple();
        $procedimientoRequisitos->orderUno();
        $sql=$procedimientoRequisitos->getSQL();
        $rs = new query($conn, $sql);
        
        if($rs->numrows()>0){
            $mensaje="$desp_firma";
            $mensaje.="<br>";
            $mensaje_notificaciones = getDbValue("SELECT depe_mpv_mensaje_notificaciones FROM catalogos.dependencia WHERE depe_mesa_partes_virtual=1");
            if($mensaje_notificaciones){
                $mensaje.=$mensaje_notificaciones;
                $mensaje.="<br>";    
            }else{
                $max_horas_respuesta = getDbValue("SELECT apli_gestdoc_max_horas_respuesta FROM admin.aplicativo WHERE apli_id=1");
                $mensaje.="<b>Cumpla con remitirnos en una plazo de $max_horas_respuesta horas, los documentos que corrijan las observaciones realizadas a los requisitos presentados en su solicitud:</b><br>";
            }
            
            $mensaje.="<table width='98%' border='1' align='center'>"
                    . "<tr>"
                    . "<th width='50%' style='text-align:center'>REQUISITO</th>"
                    . "<th width='50%' style='text-align:center'>OBSERVACION</th>"
                    . "</tr>";
            while ($rs->getrow()) {
                $mensaje.="<tr>";
                $mensaje.="<td>".trim($rs->field('dere_descripcion'))."</td>";
                $mensaje.="<td>".trim($rs->field('dere_observacion'))."</td>";
                $mensaje.="</tr>";
            }
            $mensaje.="</table>";
            
            $otable->addHtml("<tr><td colspan=2>");
            $otable->addHtml($mensaje);
            $otable->addHtml("</td></tr>\n");
            
            $otable->addHidden("Ex_detalle","$mensaje");
        }else{
            $objResponse->addClear($NameDiv,'innerHTML');
            $objResponse->addAlert("Sin registros para notificar...");
            return $objResponse;
        }
    }else{    
        $otable->addField("Mensaje: ",textAreaField("Mensaje","Ex_detalle","$mensaje",5,70,5000));
    }

    $button = new Button;
    //$button->addItem(" Cerrar ","","",0,0,"","button-modal");
    $button->addItem(" Enviar Correo ","if (/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/.test(document.frm.cx_prov_email.value)){
                                            $('#btn_envia_email').hide();javascript:enviarEmailProveedor('$op','$desp_id',document.frm.cx_prov_email.value,document.frm.Ex_detalle.value)
                                        }else{
					   alert('Correo NO Valido...')
                                        }","content","","","","","","btn_envia_email");
            
    if(inlist($op,'1,2')){//DESDE LA OPCION DE REGISTRO DE DOCUMENTOS O DOCUMENTOS EN PROCESO
        $tableAdjuntos = new Table("LISTA DE ARCHIVOS DISPONIBLES PARA ENVIO","100%",3); // Título, Largura, Quantidade de colunas
        $tableAdjuntos->addColumnHeader("",false,"1%"); // Coluna com checkbox
        $tableAdjuntos->addColumnHeader("Archivo",false,"60%", "L");
        $tableAdjuntos->addColumnHeader("Descripci&oacute;n",false,"39%", "L");
        $tableAdjuntos->addRow();
        
        $enlace=PUBLICUPLOAD."gestdoc/".SIS_EMPRESA_RUC."/$periodo/$desp_id/$name_file";
        $descripcion=$num_documento;
        if(file_exists($enlace) && $name_file){
            $link=addLink($desp_id,"javascript:verFile('$enlace')","Click aqu&iacute; para Ver Documento","controle");
            $tableAdjuntos->addData("<input type=\"checkbox\" name=\"sel_adjunto[]\" value=\"1\" checked=true\">");
            $tableAdjuntos->addData($link);
            $tableAdjuntos->addData($descripcion);
            $tableAdjuntos->addRow();
        }
                
        $sql=new despachoAdjuntados_SQLlista();
        $sql->wherePadreID($desp_id);
        $sql = $sql->getSQL();
        $rsFiles = new query($conn, $sql);
        if($rsFiles->numrows()>0){        
            while ($rsFiles->getrow()) {                                    
                $dead_id = $rsFiles->field("dead_id");
                $file=$rsFiles->field("area_adjunto");
                $descripcion=$rsFiles->field("dead_descripcion");
                $enlace=PUBLICUPLOAD."gestdoc/".SIS_EMPRESA_RUC."/$periodo/$desp_id/$file";                
                if(file_exists($enlace) && $file){

                    if($rsFiles->field("dead_signer")==1){//si es adjunto firmado
                        $tableAdjuntos->addData("<input type=\"checkbox\" name=\"sel_adjunto[]\" value=\"$dead_id\" checked=true\">");
                    }else{
                        $tableAdjuntos->addData("<input type=\"checkbox\" name=\"sel_adjunto[]\" value=\"$dead_id\" \">");
                    }
                    
                    if(strpos(strtoupper($file),'.PDF')>0){
                        $link=addLink($file,"javascript:verFile('$enlace')","Click aqu&iacute; para Ver Documento","controle");
                    }else{
                        $link=addLink($file,"$enlace","Click aqu&iacute; para Descargar Archivo","controle");
                    }                    
                    $tableAdjuntos->addData($link);

                    if($file!=$descripcion){
                        $tableAdjuntos->addData($descripcion);
                    }else{
                        $tableAdjuntos->addData("");
                    }
                    
                }else{
                    $tableAdjuntos->addData("");
                    $tableAdjuntos->addData("");
                    $tableAdjuntos->addData("");
                }

                $tableAdjuntos->addRow();
            }     
        }
    
        $otable->addHtml("<tr><td colspan=2>\n"); 
        $otable->addHtml($tableAdjuntos->writeHTML());
        $otable->addHtml("</td></tr>\n");            
    }
    
    $otable->addHtml("<tr><td colspan=2><div class='modal-footer'>\n"); //pide datos de afectacion presupuestal
    $otable->addHtml($button->writeHTML());
    $otable->addHtml("</div></td></tr>\n");

    $otable->addHtml("<tr><td colspan=2>\n"); 
    $otable->addHtml("<span id='email-chk-error'></span><div class='progress'><div id='email-progress-bar' class='progress-bar'></div>");
    $otable->addHtml("</td></tr>\n");

    $contenido_respuesta=$otable->writeHTML();       
    $contenido_respuesta.="<div id=\"historial-envios\">";
    $contenido_respuesta.=listaHistorialEnvios(2,$desp_id);
    $contenido_respuesta.="</div>";
      
    $objResponse->addAssign($NameDiv,'innerHTML', $contenido_respuesta);
    
    return $objResponse;
}

function listaHistorialEnvios($op,$id){
    global $conn;	
    
    $objResponse = new xajaxResponse();
        
    /*obtengo datos familiares*/
    $listEnvios=new clsDespachosEnvios_SQLlista();
    $listEnvios->wherePadreID($id);
    $listEnvios->orderUno();
    $sql=$listEnvios->getSQL();
    //echo $sql;
    $rs = new query($conn, $sql);
    
    if($rs->numrows()>0){

        /* inicializo tabla */
        $table = new Table("HISTORIAL DE CORREOS ENVIADOS","100%",5); // Titulo, Largura, Quantidade de colunas

        /* construccion de cabezera de tabla */
        $table->addColumnHeader("Email",false,"20%","c");
        $table->addColumnHeader("Texto",false,"30%","c");
        $table->addColumnHeader("Adjuntos",false,"20%","c");
        $table->addColumnHeader("Usuario",false,"10%","c");
        $table->addColumnHeader("Envio",false,"20%","c");
        $table->addRow();

        while ($rs->getrow()) {
                /* adiciona columnas */
                $table->addData($rs->field("deen_email"));	
                $table->addData($rs->field("deen_mensaje"));	
                $table->addData($rs->field("deen_files_adjuntos"));
                $table->addData($rs->field("usua_login"));
                $table->addData($rs->field("deen_fregistro"));
                $table->addRow(); // adiciona linea

        }
        $table->addBreak("Total Envios: ".$rs->numrows(),true,"left");

        $contenido_respuesta=$table->writeHTML();
    }else{
        $contenido_respuesta="";
    }

    // Se devuelve el objeto, que este dara todas las instruccione JS para realizar la tarea
    if($op==1){
        $objResponse->addAssign('historial-envios','innerHTML', $contenido_respuesta);
        $objResponse->addScript("$('#btn_envia_email').show()");
        return $objResponse;
    }else{
        return $contenido_respuesta;
    }	    
}