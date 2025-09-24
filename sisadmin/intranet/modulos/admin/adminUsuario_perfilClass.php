<?php
require_once('../../library/clases/entidad.php');
require_once('../../library/clases/selectSQL.php');
//require_once('perfilUsuario_class.php');

class usuarioPerfil extends entidad {
	
	function __construct($id='',$title=''){
		$this->setTable='usuario_perfil'; //nombre de la tabla
		$this->setKey='uspe_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="String"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	
		$this->pagEdicion=$this->getNamePage('Edicion');		

		/*Ancho y Alto de Thickbox */
		$this->is_thinckbox=true;
		$this->winWidth=600;  	/* Ancho de la ventana Thickbox cuando se cargue la claes en un Avanzlookup */
		$this->winHeight=550;  	/* Alto de la ventana Thickbox cuando se cargue la claes en un Avanzlookup */
		
		/* Destinos luego de actulizar, agregar o eliminar un registro */
		$this->destinoInsert=$this->getNamePage('Lista1n');
		$this->destinoUpdate=$this->getNamePage('Lista1n');
		$this->destinoDelete=$this->getNamePage('Lista1n');

		$this->arrayNameVar[0]='nomeCampoForm';
		$this->arrayNameVar[1]='busEmpty';
		$this->arrayNameVar[2]='cadena2';
		$this->arrayNameVar[3]='pg2';
		$this->arrayNameVar[4]='colSearch';
		$this->arrayNameVar[5]='numForm';
	}

	function buscar($op,$formData,$arrayParam,$pg,$Nameobj='')
	{
		global $conn,$param,$nomeCampoForm;
		$objResponse = new xajaxResponse();
		
		$arrayParam=decodeArray($arrayParam);

		$paramFunction= new manUrlv1();
		$paramFunction->setUrlExternal($arrayParam);

		if($op==1 && !is_array($formData)) $formData=decodeArray($formData);
		
		$cadena=is_array($formData)?trim($formData['Sbusc_cadena']):$formData;
		
		$busEmpty=$paramFunction->getValuePar($paramFunction->getValuePar(1));
		$relacionamento_id=$paramFunction->getValuePar('relacionamento_id');
		$colSearch=$paramFunction->getValuePar($paramFunction->getValuePar(4));
		$numForm=$paramFunction->getValuePar($paramFunction->getValuePar(5));
		
		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	
			/* Creo my objeto Table */
			$otable = new TableSimple(iif($cadena,'!=','','RESULTADO DE: '.$cadena,''),"100%",6,'tLista'); // T�tulo, ancho, Cantidad de columas,id de la tabla			

			/* Guardo la p�gina actual en un campo oculto.  Para que al eliminar regrese a la p�gina donde estaba */
			$otable->addHtml("<input type='hidden' name='".$paramFunction->getValuePar(3)."' value='$pg'>\n");			
			
			$sql=new usuarioPerfil_SQLlista();
			$sql->whereUsers($relacionamento_id);
			
			switch($colSearch){
				case 'cbgr_codigo': // si se recibe el campo id
					
					break;
	
				default:// si se no se recibe ningun campo de busqueda
					if($cadena)
						$sql->whereDescrip($cadena);					
	
					break;
			}
			$sql->orderUno();
			//echo $sql->getSQL();
			//$objResponse->addAlert($sql->getSQL());
						
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) {
				$param->replaceParValue($paramFunction->getValuePar(2),$cadena); /* Agrego el par�metro */			
			}
			

			$rs = new query($conn, strtoupper($sql->getSQL()));

			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,'".encodeArray($formData)."','".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,'".encodeArray($formData)."','".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
					$otable->addColumnHeader("<input type=\"checkbox\" id=\"checkall\" >"); // Coluna com checkbox
					$otable->addColumnHeader("");
					/* Agrego las cabeceras de mi tabla */
					$otable->addColumnHeader("C&oacute;digo","5%", "L"); 
					$otable->addColumnHeader("Descripci&oacute;n","85%", "L");
                                        $otable->addColumnHeader("Usuario","10%", "L");
					$otable->addRowHead(); 					
					$totReg=0;
					$rs->getrow();
					do{ 
						$id=$rs->field("id");		
						$id2=$rs->field("uspe_id");
						$otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id2\" >");						
						$otable->addData("<span id='fold_$id' style='cursor: pointer' onClick=\"javascript:openList('$id')\">&nbsp;+&nbsp;</span>","C");		
						$otable->addData($id);
						$otable->addData($rs->field("perf_descripcion"));
                                                $otable->addData($rs->field("usua_login"));
                                                
						$table_encadenada = new Table("","100%",5);
						$table_encadenada->addColumnHeader("<acronym title='modulos'>&nbsp;+&nbsp;</acronym>");	
						$table_encadenada->addColumnHeader("Men&uacute;",false,"100%","L");
						$table_encadenada->addRow();
						do{
							$sist_id=$rs->field("sist_id");
							$table_encadenada->addData("<span id='fold_$sist_id' style='cursor: pointer' onClick=\"javascript:openList('$sist_id')\">&nbsp;+&nbsp;</span>","C");
							$table_encadenada->addData($rs->field("modulo"));

							$table_encadenada2 = new Table("","100%",3);
							$table_encadenada2->addColumnHeader("Opci&oacute;n",false,"100%","L");
							$table_encadenada2->addRow();
							do{
								$table_encadenada2->addData($rs->field("opcion"));
								$table_encadenada2->addRow();
							}while ($rs->getrow() && $rs->field("sist_id")==$sist_id && $sist_id && $rs->field("id")==$id) ;
					
							$table_encadenada->addRow();
							$table_encadenada->addBreak("<div id=\"$sist_id\" style='visibility: hidden; display: none; margin-left:40px; margin-right:40px'>".$table_encadenada2->writeHTML()."</div>", false);			
						}while ($rs->field("id")==$id && $id) ;
		
						$otable->addRow();	
						$otable->addBreak("<div id=\"$id\" style='visibility: hidden; display: none; margin-left: 60px;margin-right:60px'>".$table_encadenada->writeHTML()."</div>", false);
						$totReg++;
					}while ($rs->field("id"));
					
				$contenido_respuesta=$button->writeHTML();
				$contenido_respuesta.=$otable->writeHTML();
				$contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: $totReg</div>";

			} else {
				$otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!","100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
				$otable->addRowHead(); 	
				$otable->addRow();	
				$contenido_respuesta=$otable->writeHTML();
			}
		}
	else
		$contenido_respuesta="";
	
		//se analiza el tipo de funcionamiento
		if($op==1){//si es llamado para su funcionamiento en ajax con retornoa a un div
			$objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
			$objResponse->addscript("activaSorter()"); // Para activar el orden en la tabla 
			$objResponse->addscript("func_jquerytablas()"); // Para activar las funciones de css de la tabla
			return $objResponse;
			}
		else
			if($op==3){//si es llamado para su funcionamiento en ajax, desde un una busqueda avanzada, con retorno a un objeto
				if($Nameobj){
					$objResponse->addScript($Nameobj .' = "'.$campoTexto_de_Retorno.'";');
					return $objResponse;
					}
				else
					return $campoTexto_de_Retorno;
				}
			else//si es llamado como una simple funciona de PHP
				return $contenido_respuesta	;
	}

	function busAgregar($op,$formData,$arrayParam,$pg,$Nameobj='')
	{
		global $conn,$param,$nomeCampoForm;
		$objResponse = new xajaxResponse();
		
		$arrayParam=decodeArray($arrayParam);

		$paramFunction= new manUrlv1();
		$paramFunction->setUrlExternal($arrayParam);

		if($op==1 && !is_array($formData)) $formData=decodeArray($formData);
		
		$cadena=is_array($formData)?trim($formData['Sbusc_cadena']):$formData;
		
		$busEmpty=$paramFunction->getValuePar($paramFunction->getValuePar(1));
		$relacionamento_id=$paramFunction->getValuePar('relacionamento_id');
		$colSearch=$paramFunction->getValuePar($paramFunction->getValuePar(4));
		$numForm=$paramFunction->getValuePar($paramFunction->getValuePar(5));

				
		$pageEdit=$paramFunction->getValuePar('pageEdit');

		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	
			/* Creo my objeto Table */
			$otable = new TableSimple("","100%",8,'tLista'); // T�tulo, ancho, Cantidad de columas,id de la tabla			

			/* Guardo la p�gina actual en un campo oculto.  Para que al eliminar regrese a la p�gina donde estaba */
			$otable->addHtml("<input type='hidden' name='".$paramFunction->getValuePar(3)."' value='$pg'>\n");			

			$sql=new clsPerfilMenu_SQLlista();
			$sql->whereUsuaID($relacionamento_id);
			
			//se analiza la columna de busqueda
			switch($colSearch){
				case 'numero': // si se recibe el campo id
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
			
			//echo $sql;
			//$objResponse->addAlert($sql);
									
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) {
				$param->replaceParValue($paramFunction->getValuePar(2),$cadena); /* Agrego el par�metro */			
			}
	
			$rs = new query($conn, strtoupper($sql));			

			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,'".encodeArray($formData)."','".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,'".encodeArray($formData)."','".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
					/* Agrego las cabeceras de mi tabla */
					$otable->addColumnHeader("");
					$otable->addColumnHeader("C&oacute;d","5%", "L"); // T�tulo, Ordenar?, ancho, alineaci�n
					$otable->addColumnHeader("Perfil","95%","L");
					$otable->addColumnHeader("");					
					$otable->addRowHead();

					$rs->getrow();
					do{ 
						$id=$rs->field("id");		
						$otable->addData("<span id='fold_$id' style='cursor: pointer' onClick=\"javascript:openList('$id')\">&nbsp;+&nbsp;</span>","C");		
						$otable->addData($id);
						$otable->addData($rs->field("perf_descripcion"));
						$otable->addData("<input type=\"button\" class=\"botonAgg\" value=\"Agregar\" id=\"adi_$id\" onClick=\"xajax_addPerfil('$id')\">");
																		
						$table_encadenada = new Table("","100%",5);
						$table_encadenada->addColumnHeader("<acronym title='modulos'>&nbsp;+&nbsp;</acronym>");	
						$table_encadenada->addColumnHeader("Men&uacute;",false,"100%","L");
						$table_encadenada->addRow();
						do{
							$sist_id=$rs->field("sist_id");
							$table_encadenada->addData("<span id='fold_$sist_id' style='cursor: pointer' onClick=\"javascript:openList('$sist_id')\">&nbsp;+&nbsp;</span>","C");
							$table_encadenada->addData($rs->field("modulo"));

							$table_encadenada2 = new Table("","100%",3);
							$table_encadenada2->addColumnHeader("Opci&oacute;n",false,"100%","L");
							$table_encadenada2->addRow();
							do{
								$table_encadenada2->addData($rs->field("opcion"));
								$table_encadenada2->addRow();
							}while ($rs->getrow() && $rs->field("sist_id")==$sist_id && $sist_id && $rs->field("id")==$id) ;
					
							$table_encadenada->addRow();
							$table_encadenada->addBreak("<div id=\"$sist_id\" style='visibility: hidden; display: none; margin-left:40px; margin-right:40px'>".$table_encadenada2->writeHTML()."</div>", false);			
						}while ($rs->field("id")==$id && $id) ;
		
						$otable->addRow();	
						$otable->addBreak("<div id=\"$id\" style='visibility: hidden; display: none; margin-left: 60px;margin-right:60px'>".$table_encadenada->writeHTML()."</div>", false);
					}while ($rs->field("id"));
					
					$contenido_respuesta=$button->writeHTML();
					$contenido_respuesta.=$otable->writeHTML();
//					$contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

			} else {
				$otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!","100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
				$otable->addRowHead(); 	
				$otable->addRow();	
				$contenido_respuesta=$otable->writeHTML();
			}
		}
	   else
		$contenido_respuesta="";
	
		//se analiza el tipo de funcionamiento
		if($op==1){//si es llamado para su funcionamiento en ajax con retornoa a un div
			$objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
			$objResponse->addscript("activaSorter()"); // Para activar el orden en la tabla 
			$objResponse->addscript("func_jquerytablas()"); // Para activar las funciones de css de la tabla
			return $objResponse;
			}
		else
			if($op==3){//si es llamado para su funcionamiento en ajax, desde un una busqueda avanzada, con retorno a un objeto
				if($Nameobj){
					$objResponse->addScript($Nameobj .' = "'.$campoTexto_de_Retorno.'";');
					return $objResponse;
					}
				else
					return $campoTexto_de_Retorno;
				}
			else//si es llamado como una simple funciona de PHP
				return $contenido_respuesta	;
	}
				

	function eliminar(){
		global $conn,$param;

		$destinoDelete=$this->destinoDelete.$param->buildPars(true);		
		
		/* captura y prepara la lista de registros a ser eliminados */ 
		$arLista_elimina = getParam("sel");
		if (is_array($arLista_elimina)) {
		 $lista_elimina = implode(",",$arLista_elimina);
		}
		
		if(strtolower($this->typeKey)=='string'){
			/* debido a que el campo clave es char */
			$lista_elimina=str_replace(",","','",$lista_elimina); 
		}

		/* Sql a ejecutar */
		$sql="DELETE FROM $this->setTable  WHERE $this->setKey IN (".iif(strtolower($this->typeKey),"==","string","'","").$lista_elimina.iif(strtolower($this->typeKey),"==","string","'","").") AND usua_idcrea=".getSession("sis_userid");
                //echo $sql;
                $conn->execute($sql);
		$error=$conn->error();		
		if($error) alert($error);
		else{ 		
			redirect($destinoDelete,"content");		
		}
	}
	
	/* Nombre del archivo php de la clase */
	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	

	function getNamePage($accion)
	{
		return(str_replace('Class',$accion,$this->getNameFile()));
	}	
	
		
} /* Fin de la clase */

class usuarioPerfil_SQLlista extends selectSQL {

	function __construct(){
		$this->sql= "SELECT a.uspe_id,
							lpad(a.perf_id::TEXT, 4, '0') AS id,
                                                        x.perf_descripcion,
							b.sist_id, 
							e.sist_descripcion as modulo,
							c.smop_id,
							g.simo_descripcion||': '||f.smop_descripcion as opcion,
                                                        a.usua_id,
                                                        j.pers_nombres||' '||j.pers_apellpaterno||' '||j.pers_apellmaterno AS empleado,
                                                        j.pers_dni,
                                                        xx.usua_login
		 			  FROM admin.usuario_perfil a 
		 			  LEFT JOIN admin.perfilu x				ON a.perf_id=x.perf_id
		 			  LEFT JOIN admin.perfilu_modulo b 	    ON a.perf_id=b.perf_id 
		 			  LEFT JOIN admin.perfilu_modulo_menu c   ON b.pemo_id=c.pemo_id		 			  
					  LEFT JOIN admin.sistema e 	 			ON b.sist_id=e.sist_id
					  LEFT JOIN admin.sistema_modulo_opciones f ON c.smop_id=f.smop_id 		 			  
					  LEFT JOIN admin.sistema_modulo			g ON f.simo_id=g.simo_id 
                                          LEFT JOIN admin.usuario h                   ON a.usua_id=h.usua_id
                                          LEFT JOIN personal.persona_datos_laborales i   ON  h.pdla_id=i.pdla_id
                                          LEFT JOIN personal.persona j                   ON  i.pers_id=j.pers_id
                                          LEFT JOIN admin.usuario xx ON a.usua_idcrea=xx.usua_id
					";
	}	
					  	
	function whereId($id){
		$this->addWhere("a.uspe_id=$id");	
	}
	
        function whereSistId($sist_id){
		$this->addWhere("b.sist_id IN ($sist_id)");	
	}
        
	function whereUsers($usua_id){
		$this->addWhere("a.usua_id=$usua_id");	
	}

        function whereActivo(){
        	$this->addWhere("h.usua_activo=1");    
        }
        
        function whereActivo2($usua_id){
        	$this->addWhere("(h.usua_activo=1 OR a.usua_id=$usua_id)");    
        }
        
	function whereDescrip($descrip){
		$this->addWhere("(x.perf_descripcion ILIKE '%$descrip%' OR e.sist_descripcion ILIKE '%$descrip%' OR g.simo_descripcion ILIKE '%$descrip%' OR f.smop_descripcion ILIKE '%$descrip%')");	
	}
	
	function orderUno(){
		$this->addOrder("a.perf_id DESC,b.sist_id,c.smop_id");		
	}	
        
        function getUsuarios(){
            $sql="SELECT DISTINCT a.usua_id,
                                  a.empleado||' ('||a.pers_dni||')'
                    FROM (".$this->getSQL().") AS a
                    WHERE a.usua_id>1
                    ORDER BY 2
                ";

            return $sql;
        }
}


class clsPerfilMenu_SQLlista extends selectSQL {
	function __construct(){
		$this->sql = "SELECT a.*,lpad(a.perf_id::TEXT, 4, '0') AS id,
							b.sist_id, 
							e.sist_descripcion as modulo,
							c.smop_id,
							g.simo_descripcion||': '||f.smop_descripcion as opcion 
		 			  FROM perfilu a
		 			  LEFT JOIN perfilu_modulo b 	    ON a.perf_id=b.perf_id 
		 			  LEFT JOIN perfilu_modulo_menu c   ON b.pemo_id=c.pemo_id		 			  
					  LEFT JOIN sistema e 	 			ON b.sist_id=e.sist_id
					  LEFT JOIN sistema_modulo_opciones f ON c.smop_id=f.smop_id 		 			  
					  LEFT JOIN sistema_modulo			g ON f.simo_id=g.simo_id					  
					";
	}
	
	function whereID($id){
		$this->addWhere("a.perf_id=$id");	
	}
	
	function whereDescrip($descrip){
		if($descrip) $this->addWhere("(a.perf_descripcion ILIKE '%$descrip%' OR e.sist_descripcion ILIKE '%$descrip%' OR g.simo_descripcion ILIKE '%$descrip%' OR f.smop_descripcion ILIKE '%$descrip%')");
	}
	
	function whereUsuaID($usua_id){
		$this->addWhere("a.perf_id NOT IN (SELECT perf_id FROM usuario_perfil WHERE usua_id=$usua_id)");
	}
	
	function orderUno(){
		$this->addOrder("a.perf_id DESC,b.sist_id,c.smop_id");		
	}

}

/* Llamando a la subclase */
if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
            require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
            /*	verificaci�n a nivel de usuario */
            verificaUsuario(1);
            verif_framework();

            $param= new manUrlv1();
            $param->removePar('control');

            //	conexi�n a la BD
            $conn = new db();
            $conn->open();

            $dml=new usuarioPerfil();
            /* Recibo la p�gina actual de la lista y lo agrego como par�metro a ser enviado por la URL.  Este dato viene por POST */
            $pg = getParam($dml->getArrayNameVarID(3));
            $param->replaceParValue($dml->getArrayNameVarID(3),$pg); /* Agrego el par�metro */

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