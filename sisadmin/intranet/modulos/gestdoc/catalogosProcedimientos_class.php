<?php
require_once("../../library/clases/entidad.php");

class procedimiento extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='gestdoc.procedimiento'; //nombre de la tabla
		$this->setKey='proc_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

                $this->pagEdicion=$this->getNamePage('edicion');
                $this->pagBuscar=$this->getNamePage('buscar');

		/* Destinos luego de actulizar, agregar o eliminar un registro */
                $this->destinoUpdate = $this->pagEdicion;
                $this->destinoInsert = $this->pagEdicion;
                $this->destinoDelete = $this->pagBuscar;
	}

	function addField(&$sql){
            $sql->addField("proc_actualfecha", "now()", "String");
            $sql->addField("proc_actualusua", getSession("sis_userid"), "String");

                
            if ($_POST["hx_proc_modo_virtual"]){
                $sql->addField("proc_modo_virtual", 1, "Number");
            }else{
                $sql->addField("proc_modo_virtual", 0, "Number");
            }
            
            if ($_POST["hx_proc_validar"]){
                $sql->addField("proc_validar", 1, "Number");
            }else{
                $sql->addField("proc_validar", 0, "Number");
            }

           if ($_POST["hx_proc_estado"]){
                $sql->addField("proc_estado", 1, "Number");
           }else{
                $sql->addField("proc_estado", 0, "Number");
           }
             
	}

	function getSql(){
		$sql=new procedimiento_SQLlista();
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
                
                $depe_id=$formData['nbusc_depe_id'];
		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	

                    $sql=new procedimiento_SQLlista();
                    if($depe_id){
                        $sql->whereDepeID(intval($depe_id));                            
                    }else{
                        $sql->whereDepeTodos(getSession("sis_depe_superior"));
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
                                        $otable->addColumnHeader("C&oacute;d","5%", "C"); 
                                        $otable->addColumnHeader("Nombre","35%", "C");
                                        $otable->addColumnHeader("Dependencia","20%", "C");
                                        $otable->addColumnHeader("Destinatario","20%", "C");
                                        $otable->addColumnHeader("Plazo Total (dias)","15%", "C");
                                        $otable->addColumnHeader("Usuario","5%", "C"); 
                                        $otable->addRowHead();
					while ($rs->getrow()) {
						$id = $rs->field("proc_id"); // captura la clave primaria del recordsource

						$otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");
						$otable->addData(addLink($rs->field("procid"),"$pageEdit?id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
						
						$otable->addData($rs->field("proc_nombre"));
                                                $otable->addData($rs->field("depe_id").' '.$rs->field("dependencia"));
                                                $otable->addData($rs->field("depe_id_destinatario").' '.$rs->field("destinatario"));
                                                $otable->addData($rs->field("proc_plazo_dias"),"C");
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
			if($nomeCampoForm){//si se llama desde una ventana emergente (avanzlookup) para seleccionar un valor
				/* Datos que se retornan desde un (avanzlookup) */
				$return_val=$this->return_val; /* Valor que se devuelve */
				$return_txt=$this->return_txt; /* Texo que se devuelve */
	
			}else{ /* Si se llama desde una p�gina normal */
				/*a�ado el id del registro ingresado*/
				$last_id=$padre_id; /* Obtengo el Id del registro ingreado.  Ojo esto solo funciona si el campo clave es un serial (est� basado en una secuencia) */								
				if(strpos($destinoInsert, "?")>0)
					$destinoInsert.="&id=$last_id&clear=1";  
				else
					$destinoInsert.="?id=$last_id&clear=1";
			
				/* Envio el "id" para cuando regreso a la misma p�gina de edici�n y el 
				"clear" para cuando regreso a la lista y deseo que se vea el �ltimo registro ingresado, 
				con el clear se limpia la variable "cadSearch" o "cadSearchhijo" */
				//echo $destinoInsert;	
				redirect($destinoInsert,"content");							
			}
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

class procedimiento_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.*,
                                    LPAD(proc_id::TEXT,4,'0') AS procid,
                                    b.tabl_descripcion AS tipo_despacho,
                                    c.depe_nombre AS dependencia,
                                    d.depe_nombre AS destinatario,
                                    x.usua_login as username,
                                    y.usua_login as usernameactual
				FROM gestdoc.procedimiento a
                                LEFT JOIN catalogos.tabla b ON a.tabl_tipodespacho=b.tabl_id
                                LEFT JOIN catalogos.dependencia c ON a.depe_id = c.depe_id
                                LEFT JOIN catalogos.dependencia d ON a.depe_id_destinatario = d.depe_id                                
				LEFT JOIN admin.usuario x ON a.usua_id=x.usua_id      
                                LEFT JOIN admin.usuario y ON a.proc_actualusua=y.usua_id      
				";
	
	}
	
	function whereID($id){
		$this->addWhere("a.proc_id=$id");
	}


	function whereDepeID($depe_id){
		$this->addWhere("a.depe_id=$depe_id");	
	}
        
        function whereDepeTodos($depe_id) {
            $this->addWhere("a.depe_id IN (SELECT depe_id
                                                FROM catalogos.func_treedependencia2($depe_id)
                                                WHERE depe_id>0)");
        }
        
	function whereDescrip($descrip){
		if($descrip) $this->addWhere("a.proc_nombre ILIKE '%$descrip%'");
	}

	function whereEstado($estado){
		$this->addWhere("a.proc_estado=$estado");
	}
        
        function whereModoVirtual(){
		$this->addWhere("a.proc_modo_virtual=1");
	}        
        
        function whereDestinatario(){
		$this->addWhere("a.depe_id IS NOT NULL");
	}
        
        function whereTipoDespacho($tabl_tipodespacho){
		$this->addWhere("a.tabl_tipodespacho=$tabl_tipodespacho");
	}
        
	function orderUno(){
		$this->addOrder("a.proc_id");
	}

        
	function getSQL_cbox(){
		$sql="SELECT proc_id AS id,
                                procid||' '||proc_nombre AS descripcion
				FROM (".$this->getSQL().") AS a 
                             ORDER BY a.proc_nombre";
		return $sql;
	}
	
        function getSQL_cbox2(){
		$sql="SELECT proc_id AS id,
                                proc_nombre AS descripcion
				FROM (".$this->getSQL().") AS a 
                             ORDER BY a.proc_nombre";
		return $sql;
	}
}


class procedimientoRuta_SQLlista extends selectSQL {

	function __construct(){
               $this->sql="SELECT   a.prru_id,
                                    LPAD(a.proc_id::TEXT, 4, '0') AS padre_id,
                                    a.prru_secuencia,
                                    a.prru_secuencia_tipo,
                                    a.depe_id,
                                    a.prru_plazo,
                                    a.prru_accion,
                                    a.usua_id,
                                    b.proc_nombre,
                                    b.proc_plazo_dias,
                                    b.tabl_tipodespacho,
                                    b.plde_id,
                                    b.tiex_id,
                                    CASE WHEN a.prru_secuencia_tipo = 1 THEN 'OBLIGATORIO'
                                      ELSE 'ALTERNATIVO'
                                    END AS tipo_ruta,
                                    c.depe_nombre AS dependencia,
                                    d.depe_nombre AS destinatario,
                                    b.proc_modo_virtual,
                                    x.usua_login as usuario
                             FROM gestdoc.procedimiento_ruta a
                                  LEFT JOIN gestdoc.procedimiento b ON a.proc_id = b.proc_id
                                  LEFT JOIN catalogos.dependencia c ON b.depe_id = c.depe_id
                                  LEFT JOIN catalogos.dependencia d ON b.depe_id_destinatario = d.depe_id
                                  LEFT JOIN admin.usuario x ON a.usua_id = x.usua_id
                                                         ";
	}
	
	function whereID($id){
		$this->addWhere("a.prru_id=$id");
	}

	function wherePadreID($proc_id){
		$this->addWhere("a.proc_id=$proc_id");
	}
        
	function orderUno(){
		$this->addOrder("   b.proc_id,
                                    a.prru_id");
                }
}


class procedimientoRequisitos_SQLlista extends selectSQL {

	function __construct(){
               $this->sql="SELECT   a.prre_id,
                                    LPAD(a.proc_id::TEXT, 4, '0') AS padre_id,
                                    a.prre_orden,
                                    a.prre_descripcion,
                                    a.prre_objeto,
                                    a.prre_obligatorio,
                                    a.usua_id,
                                    b.proc_nombre,
                                    CASE WHEN a.prre_objeto = 1 THEN 'TEXTO'
                                         WHEN a.prre_objeto = 2 THEN 'ARCHIVO'
                                         WHEN a.prre_objeto = 3 THEN 'CHECK'
                                         WHEN a.prre_objeto = 4 THEN 'FECHA'
                                      ELSE 'NINGUNO'
                                    END AS objeto,
                                    CASE WHEN a.prre_obligatorio=1 THEN 'SI' ELSE 'NO' END AS obligatorio,
                                    x.usua_login as usuario
                             FROM gestdoc.procedimiento_requisitos a
                                  LEFT JOIN gestdoc.procedimiento b ON a.proc_id = b.proc_id
                                  LEFT JOIN admin.usuario x ON a.usua_id = x.usua_id
                                                         ";
	}
	
	function whereID($id){
		$this->addWhere("a.prre_id=$id");
	}

	function wherePadreID($proc_id){
		$this->addWhere("a.proc_id=$proc_id");
	}
        
	function orderUno(){
		$this->addOrder("   b.proc_id,
                                    a.prre_id");
                }
                
        function orderDos(){
		$this->addOrder("   a.prre_orden");
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

            $dml=new procedimiento();

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