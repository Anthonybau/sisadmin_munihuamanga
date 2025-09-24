<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class clsTipExp extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='tipo_expediente'; //nombre de la tabla
		$this->setKey='tiex_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

		/* Destinos luego de actulizar o agregar un registro */
		$this->destinoUpdate = "catalogosTipoExpediente_buscar.php";
		$this->destinoInsert = "catalogosTipoExpediente_buscar.php";
		$this->destinoDelete = "catalogosTipoExpediente_buscar.php";
	}

	function addField(&$sql){
		$sql->addField("tiex_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
		$sql->addField("tiex_actualusua", getSession("sis_userid"), "String");
                
                if ($_POST["hx_tiex_estado"]){
                    $sql->addField("tiex_estado", 1, "Number");
                }else{
                    $sql->addField("tiex_estado", 0, "Number");
                }

                
                if ($_POST["hx_tiex_tiporesolucion"]){
                    $sql->addField("tiex_tiporesolucion", 1, "Number");
                }else{
                    $sql->addField("tiex_tiporesolucion", 0, "Number");
                }
                
                if ($_POST["hx_tiex_tipojudicial"]){
                    $sql->addField("tiex_tipojudicial", 1, "Number");
                }else{
                    $sql->addField("tiex_tipojudicial", 0, "Number");
                }
                if ($_POST["hx_tiex_ocultar_editor"]){
                    $sql->addField("tiex_ocultar_editor", 1, "Number");
                }else{
                    $sql->addField("tiex_ocultar_editor", 0, "Number");
                }
                if ($_POST["hx_tiex_adjuntos_para_firma"]){
                    $sql->addField("tiex_adjuntos_para_firma", 1, "Number");
                }else{
                    $sql->addField("tiex_adjuntos_para_firma", 0, "Number");
                }
                
                if ($_POST["hx_tiex_habilitar_mas_firmas_empleado"]){
                    $sql->addField("tiex_habilitar_mas_firmas_empleado", 1, "Number");
                }else{
                    $sql->addField("tiex_habilitar_mas_firmas_empleado", 0, "Number");
                }             
        
                if ($_POST["hx_tiex_habilitar_mas_firmas_externo"]){
                    $sql->addField("tiex_habilitar_mas_firmas_externo", 1, "Number");
                }else{
                    $sql->addField("tiex_habilitar_mas_firmas_externo", 0, "Number");
                }
                
                if ($_POST["hx_tiex_exigir_marcar_documento_final"]){
                    $sql->addField("tiex_exigir_marcar_documento_final", 1, "Number");
                }else{
                    $sql->addField("tiex_exigir_marcar_documento_final", 0, "Number");
                }
                
                if ($_POST["hx_tiex_mesa_partes_virtual"]){
                    $sql->addField("tiex_mesa_partes_virtual", 1, "Number");
                }else{
                    $sql->addField("tiex_mesa_partes_virtual", 0, "Number");
                }
                
	}

	function getSql(){
		$sql=new clsTipExp_SQLlista();
		$sql->whereID($this->id);
		return($sql->getSQL());
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
				
		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	
			$otable = new  Table("","100%",6);

			$sql=new clsTipExp_SQLlista();
						
			//se analiza la columna de busqueda
			switch($colSearch){
				case 'codigo': // si se recibe el campo id
					$sql->whereID($cadena);
					break;
	
				default:// si se no se recibe ningun campo de busqueda
					if(ctype_digit($cadena)) //si la cadena recibida son todos digitos
						$sql->whereID($cadena);
					else
						$sql->whereDescrip($cadena);
					break;
				}
			//$sql->addOrder($colOrden);
			$sql->orderUno();
			$sql=$sql->getSQL();
			
//			$objResponse->addAlert($sql);
		
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) setSession("cadSearch",$cadena);			
	
			$rs = new query($conn, strtoupper($sql));						

			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
					$otable->addColumnHeader("<input type=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">"); // Coluna com checkbox
					$colOrden=1;
					$paramFunction->replaceParValue('colOrden',$colOrden++);
					$otable->addColumnHeader("C&oacute;d",true,"5%", "L","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); // T�tulo, Ordenar?, ancho, alineaci�n
					$paramFunction->replaceParValue('colOrden',$colOrden++);
					$otable->addColumnHeader("Descripci&oacute;n",true,"76%", "L","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); // T�tulo, Ordenar?, ancho, alineaci�n
					$paramFunction->replaceParValue('colOrden',$colOrden++);					
					$otable->addColumnHeader("Breve",true,"10%", "L","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); // T�tulo, Ordenar?, ancho, alineaci�n											
					$paramFunction->replaceParValue('colOrden',$colOrden++);					
					$otable->addColumnHeader("Secuencia",true,"9%", "L","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); // T�tulo, Ordenar?, ancho, alineaci�n											
                                        $paramFunction->replaceParValue('colOrden',$colOrden++);					
					$otable->addColumnHeader("Judic",true,"1%", "L","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); // T�tulo, Ordenar?, ancho, alineaci�n																
					$otable->addRow(); // adiciona la linea (TR)
					while ($rs->getrow()) {
						$id = $rs->field("tiex_id"); // captura la clave primaria del recordsource
						$campoTexto_de_Retorno = especialChar($rs->field("tiex_descripcion"));

						$otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");
						$otable->addData(addLink(str_pad($id,4,'0',STR_PAD_LEFT),"catalogosTipoExpediente_edicion.php?id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
						
						$otable->addData($rs->field("tiex_descripcion"));
						$otable->addData($rs->field("tiex_abreviado"));
						$otable->addData($rs->field("tiex_secuencia"));
                                                $otable->addData($rs->field("tipo_judicial"));
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

	function guardar(){
		global $conn,$param;
		$clear=getParam("clear");
		$destinoUpdate=$this->destinoUpdate.$param->buildPars(true);	
		$pg=is_array($this->arrayNameVar)?$this->arrayNameVar[3]:'pg';
		$param->removePar($pg); /* Remuevo el par�metro p�gina */
		$destinoInsert=$this->destinoInsert.$param->buildPars(true);
		
		// objeto para instanciar la clase sql
		$sql = new UpdateSQL();
				
		$sql->setTable($this->setTable);
		$sql->setKey($this->setKey, $this->valueKey, $this->typeKey);
	
		include("../guardar_tipoDato.php");
	
		if ($this->valueKey) { // modificación
			$sql->setAction("UPDATE");
                        $sql_type=2;
		}else{
			$sql->setAction("INSERT");
                        $sql_type=1;
			$sql->addField('usua_id', getSession("sis_userid"), "Number");
		}


		/* Aqu� puedo agregar otros campos a la sentencia SQL */
		$this->addField($sql);

		/* Ejecuto el SQL */
                $sqlCommand=$sql->getSQL();
		$padre_id=$conn->execute("$sqlCommand  RETURNING $this->setKey");
		$error=$conn->error();
		if($error){ 
			alert($error);	/* Muestro el error y detengo la ejecuci�n */
		}else{
			/*  muestra mensaje noticia del la base de datos, pero no detiene la ejecucion	*/
			$notice=$conn->notice();
			if($notice) 
                            alert($notice,0);
                        }
		
		/* */
		if ($this->valueKey) {// modificación
                    $last_id=$this->valueKey; 
                    if(strpos($destinoInsert, "?")>0)
                            $destinoUpdate.="&id=$last_id&clear=$clear";
                    else
                            $destinoUpdate.="?id=$last_id&clear=$clear";

                    redirect($destinoUpdate,"content");
							
		}else{ /* Inserci�n */
                    if(strpos($destinoInsert, "?")>0)
                        $destinoInsert.="&id=$padre_id&clear=$clear";  
                    else
                        $destinoInsert.="?id=$padre_id&clear=$clear";
                    
                    redirect($destinoInsert,"content");							
		}
	}
        
	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	
}

class clsTipExp_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.*,
					LPAD(a.tiex_id::TEXT,4,'0') as cod_td,
					LPAD(a.tiex_id::TEXT,4,'0')||' '||a.tiex_descripcion as td_nombre,
                                        CASE WHEN a.tiex_tipojudicial=1  THEN 'SI' ELSE 'NO' END AS tipo_judicial,
					x.usua_login as username,
					y.usua_login as usernameactual
				FROM catalogos.tipo_expediente a
				LEFT JOIN admin.usuario x ON a.usua_id=x.usua_id
				LEFT JOIN admin.usuario y ON a.tiex_actualusua=y.usua_id
				";
	
	}
	
	function whereID($id){
		$this->addWhere("a.tiex_id=$id");
	}

        
	function whereDescrip($descrip){
		if($descrip) $this->addWhere("a.tiex_descripcion ILIKE '%$descrip%'");
	}

	function whereAbreviado($tido_abreviado){
		if($tido_abreviado) $this->addWhere("a.tiex_abreviado IN ($tido_abreviado)");
	}

        function whereNOAbreviado($tido_abreviado){
		if($tido_abreviado) $this->addWhere("a.tiex_abreviado NOT IN ($tido_abreviado)");
	}
        
	function whereJudicial(){
		$this->addWhere("(a.tiex_tipojudicial=1)");
	}
        
        function whereMPVirtual(){
		$this->addWhere("(a.tiex_mesa_partes_virtual=1)");
	}
        
        function whereHabilitado(){
		$this->addWhere("(a.tiex_estado=1)");
	}
        
	function orderUno(){
		$this->addOrder("a.tiex_id DESC");
	}

	function orderDos(){
		$this->addOrder("a.tiex_abreviado,a.tiex_id");
	}

        
	function getSQL_cbox(){
		$sql="SELECT tiex_id,
                             LPAD(tiex_id::TEXT,3,'0')||' '||tiex_descripcion
				FROM (".$this->getSQL().") AS a ORDER BY tiex_descripcion";
		return $sql;
	}
	
        function getSQL_cbox2(){
		$sql="SELECT tiex_id,
                             tiex_descripcion
				FROM (".$this->getSQL().") AS a 
                            ORDER BY a.tiex_id=25 DESC,tiex_descripcion";
		return $sql;
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

            /* Recibo la p�gina actual de la lista y lo agrego como par�metro a ser enviado por la URL.  Este dato viene por POST */
            $pg = getParam("pg");
            $param->addParComplete('pg',$pg); /* Agrego el par�metro */

            //	conexión a la BD
            $conn = new db();
            $conn->open();

            $dml=new clsTipExp();

            switch($control){
                    case 1: // Guardar
                            $dml->guardar();
                            break;
                    case 2: // Eliminar
                            $dml->eliminar();
                            break;
            }
            //	cierra la conexi�n con la BD
            $conn->close();
    }
}