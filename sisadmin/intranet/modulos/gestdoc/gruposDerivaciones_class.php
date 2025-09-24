<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class clsGruposDerivaciones extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='gestdoc.grupos_derivaciones'; //nombre de la tabla
		$this->setKey='grde_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

		/* Destinos luego de actulizar o agregar un registro */
		$this->destinoUpdate = "gruposDerivaciones_buscar.php";
		$this->destinoInsert = "gruposDerivaciones_buscar.php";
		$this->destinoDelete = "gruposDerivaciones_buscar.php";
	}


	function getSql(){
		$sql=new clsGruposDerivaciones_SQLlista();
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
	

			$sql=new clsGruposDerivaciones_SQLlista();
						
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
			$sql->orderUno();
			
			$sql=$sql->getSQL();
			
//			$objResponse->addAlert($sql);
		
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) setSession("cadSearch",$cadena);			
	
			$rs = new query($conn, strtoupper($sql));						

			$otable = new  Table("","100%",6);
                        
			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
					$otable->addColumnHeader("<input type=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">"); // Coluna com checkbox
					$otable->addColumnHeader("C&oacute;d",false,"5%", "L");
					$otable->addColumnHeader("Descripci&oacute;n",true,"95%", "L"); 
					
					$otable->addRow(); // adiciona la linea (TR)
					while ($rs->getrow()) {
						$id = $rs->field("grde_id"); // captura la clave primaria del recordsource
						$otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");
						$otable->addData(addLink(str_pad($id,4,'0',STR_PAD_LEFT),"gruposDerivaciones_edicion.php?id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
						
						$otable->addData($rs->field("grde_descripcion"));
						$otable->addRow();
					}
				$contenido_respuesta=$button->writeHTML();
				$contenido_respuesta.=$otable->writeHTML();
				$contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

			} else {
				$otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
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

	function addField(&$sql){
            $sql->addField("grde_actualfecha", "now()", "String");
            $sql->addField("grde_actualusua", getSession("sis_userid"), "String");
        }
        
	function guardar(){
		global $conn,$param;
                //alert($nomeCampoForm);
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
		}else{
			$sql->setAction("INSERT");
			$sql->addField('usua_id', getSession("sis_userid"), "Number");
		}
                
                $grde_grupo=implode(",", getParam('Srxgrupos'));
                $sql->addField('grde_grupo', $grde_grupo, "String");

                if ($_POST["hx_grde_estado"]){
                    $sql->addField("grde_estado", 1, "Number");
                }else{
                    $sql->addField("grde_estado", 0, "Number");
                }
                
		/* Aqui puedo agregar otros campos a la sentencia SQL */
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
				$destinoUpdate.="&id=$last_id";
			else
				$destinoUpdate.="?id=$last_id";
			redirect($destinoUpdate,"content");			
							
		}else{ /* Inserci�n */
                    if(strpos($destinoInsert, "?")>0)
                            $destinoInsert.="&id=$padre_id&clear=1";  
                    else
                            $destinoInsert.="?id=$padre_id&clear=1";
                    redirect($destinoInsert,"content");							
		}
	}

        
	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	
}

class clsGruposDerivaciones_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.*,LPAD(a.grde_id::TEXT,4,'0') as cod_gd,
					x.usua_login as username,
                                        y.usua_login as usernameactual
				FROM gestdoc.grupos_derivaciones a
				LEFT JOIN admin.usuario x ON a.usua_id=x.usua_id
                                LEFT JOIN admin.usuario y ON a.grde_actualusua=y.usua_id      
                                
				";
	
	}
	
	function whereID($id){
		$this->addWhere("a.grde_id=$id");
	}

	function whereDescrip($descrip){
		if($descrip) $this->addWhere("a.grde_descripcion ILIKE '%$descrip%'");
	}

        function whereActivo(){
            $this->addWhere("a.grde_estado=1");
	}
        
	function orderUno(){
		$this->addOrder("a.grde_id DESC");
	}
	
	function getSQL_cbox(){
		$sql="SELECT    a.grde_id AS id,
                                a.grde_id::TEXT||' '||a.grde_descripcion AS descripcion
				FROM (".$this->getSQL().") AS a 
                             ORDER BY 2 ";
		return $sql;
	}        
}

class clsGruposDerivacionesExplode_SQLlista extends selectSQL {
	function __construct($grde_id){
		$this->sql="SELECT b.depe_id
                                FROM 
                                (SELECT UNNEST(regexp_split_to_array(a.grde_grupo, ','))::INTEGER AS grupo
                                    FROM gestdoc.grupos_derivaciones a
                                 WHERE a.grde_id=$grde_id) AS a
                                 LEFT JOIN catalogos.dependencia b ON a.grupo=b.depe_id 
                             ORDER BY 1
                            ";
	
	}
}

class gruposBuscar_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.grde_id::TEXT||'@' AS id,
                                    a.grde_id::TEXT||' GRUPO:'||a.grde_descripcion AS text
                            FROM gestdoc.grupos_derivaciones a
                            ";
	}

	function whereID($id){
            $this->addWhere("a.grde_id::TEXT||'@'='$id'");
	}

	function whereActivo(){
            $this->addWhere("a.grde_estado=1");
	}
        
        function whereBuscar($search){
            if( ctype_digit($search) ){
		if($search!='') {
                    $this->addWhere("a.grde_id='$search'");
                }
            }else{
                if($search!='') {
                    $array=explode(" ",$search);
                    $lista="";
                    for($i=0; $i<count($array); $i++){
                        if(trim($array[$i])!=''){
                            $lista.=$lista?" AND ":"";
                            $lista.="'GRUPO:'||a.grde_descripcion ILIKE '%".trim($array[$i])."%'";
                        }
                    }       
                    $this->addWhere($lista);
                }
            }
	}
        
        
        
        function orderUno(){
		$this->addOrder("2");
	}
}

if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
            include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
            /*	verificación a nivel de usuario */
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

            $dml=new clsGruposDerivaciones();

            switch($control){
                    case 1: // Guardar
                            $dml->guardar();
                            break;
                    case 2: // Eliminar
                            $dml->eliminar();
                            break;
            }
            //	cierra la conexión con la BD
            $conn->close();
    }
}