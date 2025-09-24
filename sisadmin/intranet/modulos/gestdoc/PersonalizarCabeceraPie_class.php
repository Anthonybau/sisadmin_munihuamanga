<?php
require_once("../../library/clases/entidad.php");

class personalizarCabeceraPie extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='gestdoc.personalizar_cabecera_pie'; //nombre de la tabla
		$this->setKey='pcpi_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

                $this->pagEdicion=$this->getNamePage('edicion');
                $this->pagBuscar=$this->getNamePage('buscar');

		/* Destinos luego de actulizar, agregar o eliminar un registro */
                $this->destinoUpdate = $this->pagEdicion;
                $this->destinoInsert = $this->pagBuscar;
                $this->destinoDelete = $this->pagBuscar;
	}

	function addField(&$sql){
            $sql->addField("pcpi_actualfecha", "now()", "String");
            $sql->addField("pcpi_actualusua", getSession("sis_userid"), "String");

            if ($_POST["hx_pcpi_estado"]){
                $sql->addField("pcpi_estado", 1, "Number");
            }else{
                $sql->addField("pcpi_estado", 0, "Number");
            }
                         
	}

	function getSql(){
		$sql=new personalizarCabeceraPie_SQLlista();
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
                $pageEdit=$paramFunction->getValuePar('pageEdit');				
                
                $pendientes=$formData['hx_busc_pendientes'];
                $pendientes=$pendientes?1:0;
                
                $tapa_id=$formData['nbus_tapa_id'];
                $tapa_id=$tapa_id?$intval($tapa_id):0;
                
                $depe_id=$formData['nbusc_depe_id'];
                $depe_id=$depe_id?$intval($depe_id):getSession("sis_depe_superior");
                
		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	
                    if($pendientes==0){//si NO solicita pendientes
                        $sql=new personalizarCabeceraPie_SQLlista();
                        $sql->whereDepeTodos($depe_id);
                        
                        if($tapa_id){
                            $sql->whereTapaID($tapa_id);
                        }

                        //se analiza la columna de busqueda
                        switch($colSearch){
                            case 'codigo': // si se recibe el campo id
                                    $sql->whereID($cadena);
                                    break;

                            default:// si se no se recibe ningun campo de busqueda
                                    if(ctype_digit($cadena)) //si la cadena recibida son todos digitos
                                            $sql->whereID($cadena);
                                    else
                                        if($cadena)
                                            $sql->whereDescrip($cadena);
                                    break;
                            }
                        $sql->addOrder('1 DESC');
                        
                        $sql=$sql->getSQL();

                    
                    }else{
                        $sql="SELECT depe_id,
                                     depe_nombre AS dependencia
                             FROM catalogos.func_treedependencia2($depe_id)
                             WHERE depe_id>1
                                   AND depe_superior=0
                                   AND depe_id NOT IN (SELECT a.depe_id 
                                                            FROM gestdoc.personalizar_cabecera_pie a
                                                            WHERE CASE WHEN $tapa_id>0 THEN a.tapa_id=$tapa_id
                                                                       ELSE TRUE
                                                                  END
                                                            )
                             ORDER BY 1
                             ";
                    }
			
			
//			$objResponse->addAlert($sql);
		
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) setSession("cadSearch",$cadena);			
	
                        $otable = new TableSimple("","100%",6,'tLista'); 
			$rs = new query($conn, strtoupper($sql));

			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
                                        $otable->addColumnHeader("<input type=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">","1%"); // Coluna com checkbox
                                        $otable->addColumnHeader("ID","1%", "C"); 
                                        $otable->addColumnHeader("Dependencia","30%", "C");
                                        $otable->addColumnHeader("Tam.P&aacute;g","10%", "C");
                                        $otable->addColumnHeader("Encabezado","27%", "C");
                                        $otable->addColumnHeader("Pie","27%", "C");
                                        $otable->addColumnHeader("Usuario","5%", "C"); 
                                        $otable->addRowHead();
					while ($rs->getrow()) {
						$id = $rs->field("pcpi_id"); // captura la clave primaria del recordsource
                                                
                                                if($id>0){
                                                    $otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");
                                                    $otable->addData(addLink($rs->field("pcpi_id"),"$pageEdit?id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
                                                }else{
                                                    $otable->addData("&nbsp;");
                                                    $otable->addData("&nbsp;");
                                                }
						
                                                $otable->addData($rs->field("depe_id").' '.$rs->field("dependencia"));
                                                $otable->addData($rs->field("tamano_pagina"));
                                                $otable->addData($rs->field("pcpi_cabecera"));
                                                $otable->addData($rs->field("pcpi_pie"));
                                                $otable->addData($rs->field("username"));
						$otable->addRow();
					}
                                $contenido_respuesta=$otable->writeHTML();                                        
                                $contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

			} else {
				$otable->addColumnHeader("NO SE ENCONTRARON DATOS...","100%", "C"); 
                                $otable->addRowHead();
                                $otable->addRow();
                                $contenido_respuesta=$otable->writeHTML();
			}
		}
	else
		$contenido_respuesta="";
	
		//se analiza el tipo de funcionamiento
                if($op==1) {//si es llamado para su funcionamiento en ajax con retornoa a un div
                    $objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
                    $objResponse->addscript("activaSorter()"); // Para activar el orden en la tabla
                    $objResponse->addscript("func_jquerytablas()"); // Para activar las funciones de css de la tabla
                    return $objResponse;
                }
                else
                    if($op==3) {//si es llamado para su funcionamiento en ajax, desde un una busqueda avanzada, con retorno a un objeto
                        if($Nameobj) {
                            $objResponse->addScript($Nameobj .' = "'.$campoTexto_de_Retorno.'";');
                            return $objResponse;
                        }
                        else
                            return $campoTexto_de_Retorno;
                    }
                    else//si es llamado como una simple funciona de PHP
                        return $contenido_respuesta	;
	}

        
	function guardar(){
		global $conn,$param;
		$nomeCampoForm=getParam("nomeCampoForm");

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
//                echo $sql->getSQL();
//                exit(0);
		$padre_id=$conn->execute("$sqlCommand  RETURNING $this->setKey");
                
		$error=$conn->error();
		if($error){ 
                         if(stristr($error,"duplicate key value")){
                             $x=substr($error,strpos($error,"constraint")+11,(strpos($error,"DETAIL")-strpos($error,"constraint"))-12);
                             $error="Valor Duplicado:".$x;
                         }
                                 
			alert(substr($error,0,300));	/* Muestro el error y detengo la ejecuci�n */
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
				$destinoUpdate.="&id=$last_id";
			else
				$destinoUpdate.="?id=$last_id";

			redirect($destinoUpdate,"content");			
							
		}else{ /* Inserci�n */
				/*a�ado el id del registro ingresado*/
				$last_id=$padre_id; /* Obtengo el Id del registro ingreado.  Ojo esto solo funciona si el campo clave es un serial (est� basado en una secuencia) */								
				if(strpos($destinoInsert, "?")>0){
                                    $destinoInsert.="&id=$last_id&clear=1";  
                                }else{
                                    $destinoInsert.="?id=$last_id&clear=1";
                                }
			
				/* Envio el "id" para cuando regreso a la misma p�gina de edici�n y el 
				"clear" para cuando regreso a la lista y deseo que se vea el �ltimo registro ingresado, 
				con el clear se limpia la variable "cadSearch" o "cadSearchhijo" */
				//echo $destinoInsert;	
				redirect($destinoInsert,"content");							
		}
	}
        
 	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	

        function getNamePage($accion) {
            return(str_replace('class',$accion,$this->getNameFile()));
        }        
}

class personalizarCabeceraPie_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.pcpi_id,
                                    a.tapa_id,
                                    a.depe_id,  
                                    a.pcpi_cabecera,
                                    a.pcpi_pie,
                                    a.usua_id,
                                    a.pcpi_estado,
                                    a.pcpi_fregistro,
                                    a.pcpi_actualfecha,
                                    a.pcpi_actualusua,
                                    c.depe_nombre AS dependencia,
                                    d.tapa_nombre AS tamano_pagina,
                                    x.usua_login as username,
                                    y.usua_login as usernameactual
				FROM gestdoc.personalizar_cabecera_pie a
                                LEFT JOIN catalogos.dependencia c ON a.depe_id = c.depe_id
                                LEFT JOIN gestdoc.tamano_pagina d ON a.tapa_id = d.tapa_id
				LEFT JOIN admin.usuario x ON a.usua_id=x.usua_id      
                                LEFT JOIN admin.usuario y ON a.pcpi_actualusua=y.usua_id      
				";
	
	}
	
	function whereID($id){
		$this->addWhere("a.pcpi_id=$id");
	}


	function whereDepeID($depe_id){
		$this->addWhere("a.depe_id=$depe_id");	
	}

	function whereTapaID($tapa_id){
		$this->addWhere("a.tapa_id=$tapa_id");	
	}
        
        function whereDepeTodos($depe_id) {
            $this->addWhere("a.depe_id IN (SELECT depe_id
                                                FROM catalogos.func_treedependencia2($depe_id)
                                                WHERE depe_id>0)");
        }        
        
	function whereDescrip($descrip){
		if($descrip) $this->addWhere("c.depe_nombre ILIKE '%$descrip%'");
	}

        
	function orderUno(){
		$this->addOrder("a.pcpi_id");
	}

        
}



if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
            include("../../library/library.php");
            /*	verificacion a nivel de usuario */
            verificaUsuario(1);
            verif_framework();

            $param= new manUrlv1();
            $param->removePar('control');
            $param->removePar('relacionamento_id');
            $param->removePar('pg'); /* Remuevo el parametro */

            /* Recibo la pagina actual de la lista y lo agrego como parametro a ser enviado por la URL.  Este dato viene por POST */
            $pg = getParam("pg");
            $param->addParComplete('pg',$pg); /* Agrego el parametro */

            //	conexion a la BD
            $conn = new db();
            $conn->open();

            $dml=new personalizarCabeceraPie();

            switch($control){
                case 1: // Guardar
                    $dml->guardar();
                    break;
                case 2: // Eliminar
                    $dml->eliminar();
                    break;
            }
            //	cierra la conexion con la BD
            $conn->close();
    }
}