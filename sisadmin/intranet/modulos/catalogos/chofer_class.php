<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class chofer extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='catalogos.chofer'; //nombre de la tabla
		$this->setKey='chof_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

		/* Destinos luego de actulizar o agregar un registro */
		$this->destinoUpdate = "chofer_buscar.php";
		$this->destinoInsert = "chofer_buscar.php";
		$this->destinoDelete = "chofer_buscar.php";
	}

	function addField(&$sql){
            
                if ($_POST["hx_chof_estado"]){
                    $sql->addField("chof_estado", '1', "number");
                }else{
                    $sql->addField("chof_estado", '0', "number");
                }

		$sql->addField("chof_actualfecha", "now()", "String");
		$sql->addField("chof_actualusua", getSession("sis_userid"), "String");

	}

	function getSql(){
		$sql=new chofer_SQLlista();
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

			$sql=new chofer_SQLlista();
						
			//se analiza la columna de busqueda
			switch($colSearch){
				case 'codigo': // si se recibe el campo id
					$sql->whereID($cadena);
					break;
	
				default:// si se no se recibe ningun campo de busqueda
					if($cadena){
                                            if(ctype_digit($cadena)){ //si la cadena recibida son todos digitos
                                                    $sql->whereID($cadena);
                                            }else{
                                                    $sql->whereDescrip($cadena);
                                            }
                                        }
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
					$otable->addColumnHeader("<input type=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">",false,"1%"); // Coluna com checkbox
					$otable->addColumnHeader("ID",false,"1%", "L");
                                        $otable->addColumnHeader("TIPO",false,"5%", "L");
                                        $otable->addColumnHeader("RUC/DNI",false,"5%", "L");
                                        $otable->addColumnHeader("Raz&oacute;n Social",false,"20%", "L");
					$otable->addColumnHeader("Apellidos",false,"17%", "L");
                                        $otable->addColumnHeader("Nombres",false,"17%", "L");
                                        //$otable->addColumnHeader("Descripci&oacute;n",false,"20%", "L");
                                        $otable->addColumnHeader("Vehiculo",false,"20%", "L");
                                        $otable->addColumnHeader("Placa",false,"10%", "L");
                                        $otable->addColumnHeader("Licencia",false,"15%", "L");
					$otable->addRow(); // adiciona la linea (TR)
					while ($rs->getrow()) {
						$id = $rs->field("chof_id"); // captura la clave primaria del recordsource
                                                
                                                if($rs->field("tabl_tipopersona")==1){
                                                    $codigo = $rs->field("transp_ruc");
                                                }else{
                                                    $codigo = $rs->field("chof_dni");
                                                }
                                                
                                                $otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");
                                                $otable->addData(addLink($id,"chofer_edicion.php?id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
                                                $otable->addData($rs->field("tipo"));
						$otable->addData(addLink($codigo,"chofer_edicion.php?id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
						$otable->addData($rs->field("transp_razsocial"));
						$otable->addData($rs->field("chof_apellidos"));
                                                $otable->addData($rs->field("chof_nombres"));
                                                //$otable->addData($rs->field("chof_descripcion"));
                                                $otable->addData($rs->field("chof_vehiculo"));
                                                $otable->addData($rs->field("chof_placa"));
                                                $otable->addData($rs->field("chof_licencia"));
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

class chofer_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  b.tabl_descripcion AS tipo,
                                    a.tabl_tipopersona,
                                    a.transp_ruc,
                                    a.transp_razsocial,
                                    a.chof_id,
                                    a.chof_descripcion,  
                                    a.chof_dni,
                                    a.chof_apellidos,
                                    a.chof_nombres,
                                    a.chof_vehiculo,
                                    a.chof_placa,
                                    a.chof_licencia,
                                    a.usua_id,
                                    a.chof_fregistro,
                                    a.chof_actualfecha,
                                    a.chof_actualusua,
                                    a.chof_estado,
                                    x.usua_login as username,
                                    y.usua_login as usernameactual
				FROM catalogos.chofer a
                                LEFT JOIN catalogos.tabla b on a.tabl_tipopersona=b.tabl_codigo AND b.tabl_tipo='TIPO_PROVEEDOR' 
				LEFT JOIN admin.usuario x ON a.usua_id=x.usua_id
				LEFT JOIN admin.usuario y ON a.chof_actualusua=y.usua_id

				";
	
	}
	
	function whereID($id){
		$this->addWhere("a.chof_id=$id");
	}

        
	function whereDescrip($descrip){
		if($descrip) {
                    $this->addWhere("a.chof_descripcion ILIKE '%$descrip%'");
                }
	}

        
	function orderUno(){
		$this->addOrder("a.chof_id DESC");
	}


        
	function getSQL_cbox(){
		$sql="SELECT    a.chof_id AS id,
                                CASE WHEN tabl_tipopersona=1 THEN a.transp_razsocial||' - '||a.transp_ruc
                                     WHEN tabl_tipopersona=3 THEN COALESCE(a.chof_vehiculo,'')
                                     ELSE a.chof_apellidos||' '||a.chof_nombres||' - '||a.chof_dni||' ('||COALESCE(a.chof_vehiculo,'')||')' 
                                END AS descripcion
				FROM (".$this->getSQL().") AS a
                                WHERE a.chof_estado=1    
                                ORDER BY 1 DESC ";
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

            $dml=new chofer();

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