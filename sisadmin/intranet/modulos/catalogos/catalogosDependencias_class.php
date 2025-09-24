<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class dependencia extends entidad {
	
	function __construct($id='',$title=''){
		$this->setTable='dependencia'; //nombre de la tabla
		$this->setKey='depe_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Integer"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;

		/* Destinos luego de actulizar, agregar o eliminar un registro */
		$this->destinoInsert='catalogosDependencias_lista.php?clear=1';
		$this->destinoUpdate='catalogosDependencias_lista.php?clear=1';
		$this->destinoDelete='catalogosDependencias_lista.php';

	}
	
	function getSql(){
		$sql=new dependencia_SQLlista();
		$sql->whereID($this->id);
		return($sql->getSQL());
	}

	function addField(&$sql){
		$sql->addField("depe_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
		$sql->addField("depe_actualusua", getSession("sis_userid"), "String");
                
                if ($_POST["hx_depe_almacen"]){
                    $sql->addField("depe_almacen", 1, "Number");
                }else{
                    $sql->addField("depe_almacen", 0, "Number");
                }
                
                if ($_POST["hx_depe_almacen_ventas"]){
                    $sql->addField("depe_almacen_ventas", 1, "Number");
                }else{
                    $sql->addField("depe_almacen_ventas", 0, "Number");
                }
                
                if ($_POST["hx_depe_centro_medico"]){
                    $sql->addField("depe_centro_medico", 1, "Number");
                }else{
                    $sql->addField("depe_centro_medico", 0, "Number");
                }
                
                if ($_POST["hx_depe_logistica"]){
                    $sql->addField("depe_logistica", 1, "Number");
                }else{
                    $sql->addField("depe_logistica", 0, "Number");
                }
                
                if ($_POST["hx_depe_administracion"]){
                    $sql->addField("depe_administracion", 1, "Number");
                }else{
                    $sql->addField("depe_administracion", 0, "Number");
                }
                
                if ($_POST["hx_depe_contrataciones"]){
                    $sql->addField("depe_contrataciones", 1, "Number");
                }else{
                    $sql->addField("depe_contrataciones", 0, "Number");
                }
                
                if ($_POST["hx_depe_nutricion"]){
                    $sql->addField("depe_nutricion", 1, "Number");
                }else{
                    $sql->addField("depe_nutricion", 0, "Number");
                }
                
                if ($_POST["hx_depe_superior"]){
                    $sql->addField("depe_superior", 1, "Number");
                }else{
                    $sql->addField("depe_superior", 0, "Number");                
                }
                
                if ($_POST["hx_depe_rindente"]){
                    $sql->addField("depe_rindente", 1, "Number");
                }else{
                    $sql->addField("depe_rindente", 0, "Number");
                }
                
                if ($_POST["hx_depe_habiltado"]){
                    $sql->addField("depe_habiltado", 1, "Number");
                }else{
                    $sql->addField("depe_habiltado", 0, "Number");
                }
                
                if ($_POST["hx_depe_mesa_partes"]){
                    $sql->addField("depe_mesa_partes", 1, "Number");
                }else{
                    $sql->addField("depe_mesa_partes", 0, "Number");
                }
                
//                if ($_POST["hx_depe_mesa_partes_virtual"]){
//                    $sql->addField("depe_mesa_partes_virtual", 1, "Number");
//                }else{
//                    $sql->addField("depe_mesa_partes_virtual", 0, "Number");
//                }
                
                if ($_POST["hx_depe_rrhh"]){
                    $sql->addField("depe_rrhh", 1, "Number");
                }else{
                    $sql->addField("depe_rrhh", 0, "Number");
                }
	}

	function jsDevolverx($nomeCampoForm){
		if($nomeCampoForm)
                    //PARA EJECUTAR UNA FUNCION DEPENDNIENTE DEL VALOR ELEGIDO EN LA VENTANA DE BUSQUEDA
                    //SE REQUIERE  DE self.parent.NOMBRE_DE_FUNCION_JAVASCRIPT(paramenteres) ejem:self.parent.xajax_cargaGrupo(valor,1)
                    return ("function update(valor, descricao, numForm) {
					parent.parent.content.document.forms[numForm]._Dummy$nomeCampoForm.value = descricao;
					parent.parent.content.document.forms[numForm].$nomeCampoForm.value = valor;
					parent.parent.content.document.forms[numForm].__Change_$nomeCampoForm.value = 1;
					self.parent.xajax_vistaInventario(1,self.parent.xajax.getFormValues('frm'),'divvistaInventario')
					}");
	}
        
	function buscar($op,$formData,$arrayParam,$pg=1,$Nameobj='')
	{
		global $conn,$param,$nomeCampoForm;
		$objResponse = new xajaxResponse();
		
		$arrayParam=decodeArray($arrayParam);

		$paramFunction= new manUrlv1();
		$paramFunction->setUrlExternal($arrayParam);

		$cadena=is_array($formData)?trim(strtoupper($formData['Sbusc_cadena'])):$formData;
						
		if(!$cadena && $op==2) $cadena=getSession("cadSearch");
		
		$busEmpty=$paramFunction->getValuePar('busEmpty');
		$colSearch=$paramFunction->getValuePar('colSearch');
		$numForm=$paramFunction->getValuePar('numForm');
		
		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	
			
			/* Consulta sql a mostrar */
			$sql=new dependencia_SQLlista();
			$sql->whereNotDos();
			//se analiza la columna de bÃºsqueda
			switch($colSearch){
				case 'codigo': // si se recibe el campo id
					$sql->whereID($cadena);								
					break;
	
				default:// si se no se recibe ningun campo de busqueda
                                    if($cadena){
					if(ctype_digit($cadena)) //si la cadena recibida son todos digitos
						$sql->whereID($cadena);
					else
						$sql->whereDescrip($cadena);
                                    }
                                    break;
				}
			
			$sql->orderUno();
			$sql=$sql->getSQL();
                        //echo $sql;
                        //$objResponse->addAlert($sql);

			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) setSession("cadSearch",$cadena);
	
			//echo $sql;
			$rs = new query($conn, strtoupper($sql));

			/* Creo my objeto Table */
			$otable = new TableSimple(iif($cadena,'!=','','RESULTADO DE: '.$cadena,''),"100%",6,'tLista'); // TÃ­tulo, ancho, Cantidad de columas,id de la tabla						

			/* Guardo la pÃ¡gina actual en un campo oculto.  Para que al eliminar regrese a la pÃ¡gina donde estaba */
			$otable->addHtml("<input type='hidden' name='pg' value='$pg'>\n");

			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
					/* Agrego las cabeceras de mi tabla */
					$otable->addColumnHeader("C&oacute;d","5%", "L"); // TÃ­tulo, ancho, alineaciÃ³n
					$otable->addColumnHeader("Descripci&oacute;n","95%", "L"); // TÃ­tulo, ancho, alineaciÃ³n						
					$otable->addRowHead(); 					

					while ($rs->getrow()) {
					
						$id = $rs->field("depe_id");// captura la clave primaria del recordsource

						$id2 = str_pad($rs->field("depe_id"),5,'0',STR_PAD_LEFT); // captura la clave primaria del recordsource						
						$campoTexto_de_Retorno = especialChar($rs->field("depe_nombre"));

						if ($nomeCampoForm){ /* si la llamada es desde la busqueda avanzada (AvanzLookup) */
							$otable->addData(addLink($id2,"javascript:update('$id','$campoTexto_de_Retorno',$numForm)","Click aqu&iacute; para seleccionar el registro"));
						}elseif($op!=3){  /* Si estoy en la pÃ¡gina normal */ 
							/* agrego pg como parÃ¡metro a ser enviado por la URL */
							$param->removePar('pg'); /* Remuevo el parÃ¡metro */
							$param->addParComplete('pg',$pg); /* Agrego el parÃ¡metro */
							
							$otable->addData($id2);
						}
						
						$otable->addData($campoTexto_de_Retorno);
						$otable->addRow();
					}
				$contenido_respuesta=$button->writeHTML();
				$contenido_respuesta.=$otable->writeHTML();
				if($rs->totalpages()>0){
					$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
					$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";
				}
				else{
					$contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";				
				}

			} else {
				$otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!","100%", "C"); // TÃ­tulo, Ordenar?, ancho, alineaciÃ³n
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

        function guardar(){
		global $conn,$param;

		$destinoUpdate=$this->destinoUpdate.$param->buildPars(true);	
		$pg=is_array($this->arrayNameVar)?$this->arrayNameVar[3]:'pg';
		$param->removePar($pg); /* Remuevo el parï¿½metro pï¿½gina */
		$destinoInsert=$this->destinoInsert.$param->buildPars(true);
		
		// objeto para instanciar la clase sql
		$sql = new UpdateSQL();
				
		$sql->setTable($this->setTable);
		$sql->setKey($this->setKey, $this->valueKey, $this->typeKey);
	
		include("../guardar_tipoDato.php");
	
		if ($this->valueKey) { // modificaciÃ³n
			$sql->setAction("UPDATE");
                        $sql_type=2;
		}else{
			$sql->setAction("INSERT");
                        $sql_type=1;
			$sql->addField('usua_id', getSession("sis_userid"), "Number");
		}


		/* Aquï¿½ puedo agregar otros campos a la sentencia SQL */
		$this->addField($sql);

		/* Ejecuto el SQL */
                $sqlCommand=$sql->getSQL();
		$padre_id=$conn->execute("$sqlCommand  RETURNING $this->setKey");
                
		$error=$conn->error();
		if($error){ 
                         if(stristr($error,"duplicate key value")){
                             $x=substr($error,strpos($error,"constraint")+11,(strpos($error,"DETAIL")-strpos($error,"constraint"))-12);
                             $error="Valor Duplicado:".$x;
                         }
                                 
			alert(substr($error,0,300));	/* Muestro el error y detengo la ejecuciï¿½n */
		}else{
			/*  muestra mensaje noticia del la base de datos, pero no detiene la ejecucion	*/
//			$notice=$conn->notice();
//			if($notice){
//                            alert($notice,0);
//                        }
		
                        /* */
                        if ($this->valueKey) {// modificaciÃ³n

                                $last_id=$this->valueKey; 
                                if(strpos($destinoInsert, "?")>0)
                                        $destinoUpdate.="&id=$last_id";
                                else
                                        $destinoUpdate.="?id=$last_id";

                                redirect($destinoUpdate,"content");			

                        }else{ /* Insercion */


                                /*aÃ‘ado el id del registro ingresado*/
                                $last_id=$padre_id; /* Obtengo el Id del registro ingreado.  Ojo esto solo funciona si el campo clave es un serial (estï¿½ basado en una secuencia) */								
                                if(strpos($destinoInsert, "?")>0){
                                        $destinoInsert.="&id=$last_id&clear=1";  
                                }else{
                                        $destinoInsert.="?id=$last_id&clear=1";
                                }
                                redirect($destinoInsert,"content");										
                        }
                }
	}
	
	/* Nombre del archivo php de la clase */
	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	

} /* Fin de la clase */

class dependencia_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT a.*,
                                    b.pdla_cargofuncional,
                                    (SELECT depe_id FROM catalogos.func_treeunidsuperior2(a.depe_id)) AS depe_superior_id,
                                    (SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(a.depe_id)) AS depe_superior_nombre,
                                    c.pers_dni AS jefe_dni,
                                    c.pers_apellpaterno||' '||c.pers_apellmaterno||' '||c.pers_nombres AS jefe,
                                    d.emru_ruc,
                                    d.emru_razon_social,
                                    x.usua_login as username,
                                    y.usua_login as usernameactual
                                    FROM catalogos.dependencia a
                                    LEFT JOIN personal.persona_datos_laborales b ON a.pdla_id=b.pdla_id
                                    LEFT JOIN personal.persona c ON b.pers_id=c.pers_id
                                    LEFT JOIN admin.empresa_ruc d ON a.emru_id=d.emru_id
                                    LEFT JOIN admin.usuario x ON a.usua_id=x.usua_id
                                    LEFT JOIN admin.usuario y ON a.depe_actualusua=y.usua_id
                            ";
	}

	function whereID($id){
		$this->addWhere("a.depe_id='$id'");
	}

        function whereIDVarios($id){
		$this->addWhere("a.depe_id IN ($id)");
	}
        
	function whereVarios($pers_id){
		$this->addWhere("a.depe_id IN (SELECT DISTINCT a.depe_id
		 			  FROM personal.persona_datos_laborales a
		 			  LEFT JOIN catalogos.dependencia b on  a.depe_id=b.depe_id
                                          WHERE a.pdla_estado=1 AND a.pers_id=$pers_id)");
	}

	function whereNotCero(){
		$this->addWhere("a.depe_id>0");
	}

	function wherePdlaID($pers_id){
                $this->addWhere("a.pdla_id IN (SELECT DISTINCT a.pdla_id
                          FROM personal.persona_datos_laborales a
                          LEFT JOIN catalogos.dependencia b on  a.depe_id=b.depe_id
                          WHERE a.pdla_estado=1 AND a.pers_id=$pers_id)");

	}
      
        function whereDepeTodos($depe_id) {
            $this->addWhere("a.depe_id IN (SELECT depe_id
                                                FROM catalogos.func_treedependencia2($depe_id)
                                                WHERE depe_id>0)");
        }
        
	function whereNotUno(){
		$this->addWhere("a.depe_id>2");
	}
        
        function whereNotDos(){
		$this->addWhere("a.depe_id>2");
	}
	
        function whereDepenSuperior(){
		$this->addWhere("a.depe_superior=1");
	}

        function whereNODepenSuperior2(){
		$this->addWhere("(a.depe_superior=0 OR a.depe_id=1)");
	}
                
        function whereHabilitado(){
		$this->addWhere("a.depe_habiltado=1");
	}
        
        function whereMPVirtual(){
		$this->addWhere("(a.depe_mesa_partes_virtual=1)");
	}
        
        function whereNOMPVirtual(){
		$this->addWhere("(a.depe_mesa_partes_virtual=0)");
	}
        
        function wheredepealmacen(){
		$this->addWhere("a.depe_almacen=1");	
	}
        
        function wheredepeAlmacenVentas(){
		$this->addWhere("a.depe_almacen_ventas=1");	
	}

        function wheredepeRindente(){
		$this->addWhere("a.depe_rindente=1");	
	}
        
        function whereDerivaSolicitudBS(){
		$this->addWhere("(a.depe_logistica=1 OR depe_administracion=1)");	
	}
        
        function whereDescrip($descrip){
		$this->addWhere("a.depe_nombre ILIKE '%$descrip%'");
	}
	
	function orderUno(){
		$this->addOrder("a.depe_id");
	}

	function getSQL_cbox(){
		$sql="SELECT a.depe_id,
                             LPAD(a.depe_id::TEXT,3,'0')||' '||a.depe_nombre||'/'||a.depe_superior_nombre
				FROM (".$this->getSQL().") AS a
                      WHERE a.depe_id>2
                      ORDER BY a.depe_nombre";
		return $sql;
	}
        
        function getSQL_cbox2(){
		$sql="SELECT a.depe_id,
                             LPAD(a.depe_id::TEXT,3,'0')||' '||a.depe_nombre||COALESCE('/'||a.depe_superior_nombre,'')
				FROM (".$this->getSQL().") AS a
                      WHERE a.depe_id>1
                      ORDER BY 1";
		return $sql;
	}        
        
        function getSQL_cbox2B(){
		$sql="SELECT a.depe_id,
                             LPAD(a.depe_id::TEXT,3,'0')||' '||a.depe_nombre||COALESCE('/'||a.depe_superior_nombre,'')
				FROM (".$this->getSQL().") AS a
                      WHERE a.depe_id>=1
                      ORDER BY 1";
		return $sql;
	}
        
	function getSQL_cbox3(){
		$sql="SELECT a.depe_id AS id,
                             a.depe_nombre AS descripcion
				FROM (".$this->getSQL().") AS a
                      WHERE a.depe_id>1
                      ORDER BY a.depe_nombre";
		return $sql;
	}        
}


class dependencia_SQLBox extends selectSQL {
	function __construct($depe_id){
		$this->sql="SELECT depe_id AS tree_id,
                                   LPAD(depe_id::TEXT,3,'0')||' '||depe_nombre AS tree_textaux 
                            FROM catalogos.func_treedependencia2($depe_id)
                            WHERE depe_id>0
                            ";
	}    
}

class dependenciaJefe_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.depe_id,
                                    a.depe_habiltado,
                                    a.depe_rrhh,
                                    a.depe_nombre,
                                    a.depe_nombrecorto,
                                    a.pdla_id,
                                    a.depe_superior,
                                   (SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(a.depe_id)) AS depe_superior_nombre,
                                    b.pdla_especbreve,
                                    c.pers_dni,
                                    c.pers_id,
                                    TRIM(c.pers_apellpaterno)||' '||TRIM(c.pers_apellmaterno)||' '||TRIM(c.pers_nombres) AS jefe,
                                    TRIM(COALESCE(b.pdla_cargofuncional,''))||CASE WHEN b.pdla_encargado=1 THEN '(E)' ELSE '' END AS cargo
                            FROM catalogos.dependencia a
                            LEFT JOIN personal.persona_datos_laborales b ON a.pdla_id=b.pdla_id 
                            LEFT JOIN personal.persona c                 ON b.pers_id=c.pers_id
                            ";
	}

        function whereID($id){
		$this->addWhere("a.depe_id='$id'");
	}
        
        function whereIDVarios($id_varios){
		$this->addWhere("a.depe_id IN ($id_varios)");
	}
        
        function wherePdlaID($pdla_id){
                $this->addWhere("a.pdla_id=$pdla_id");
        }
        
        function whereHabilitado(){
		$this->addWhere("(a.depe_habiltado=1 AND b.pdla_estado=1) ");
	}

        function whereRRH(){
		$this->addWhere("a.depe_rrhh=1");
	}
                
                
	function orderUno(){
		$this->addOrder("a.depe_id");
	}
        
	function getSQL_cbox(){
		$sql="SELECT    a.depe_id,
                                LPAD(a.depe_id::TEXT,3,'0')||' '||a.depe_nombre||'/'||a.depe_superior_nombre||' ('||COALESCE(jefe,'** SIN JEFE**')||')'
				FROM (".$this->getSQL().") AS a
                      WHERE a.depe_id>2 AND jefe IS NOT NULL
                      ORDER BY a.depe_nombre";
		return $sql;
	}

	function getSQL_cbox2($depe_id){
		$sql="SELECT a.depe_id::text,
                             LPAD(a.depe_id::TEXT,3,'0')||' '||a.depe_nombre||' ('||especialidad||' '||jefe||')'
				FROM (".$this->getSQL().") AS a
                      WHERE a.depe_id>2
                        AND a.depe_id!=$depe_id
                      UNION ALL
                      SELECT d.depe_id::text||'_'||a.usua_id::text,
                             LPAD(d.depe_id::TEXT,3,'0')||' '||d.depe_nombre||' ('||b.pdla_especbreve||' '||c.pers_apellpaterno||' '||c.pers_apellmaterno||' '||c.pers_nombres||')' 
                      FROM admin.usuario a
                      LEFT JOIN personal.persona_datos_laborales b ON a.pdla_id=b.pdla_id 
                      LEFT JOIN personal.persona c                 ON b.pers_id=c.pers_id
                      LEFT JOIN catalogos.dependencia d ON b.depe_id=d.depe_id 
                      WHERE b.depe_id=$depe_id
                      ORDER BY 2";
		return $sql;
	}        

	function getSQL_cbox3($depe_id){
		$sql="SELECT a.depe_id::text,LPAD(a.depe_id::TEXT,3,'0')||' '||a.depe_nombre||'/'||a.depe_superior_nombre
				FROM (".$this->getSQL().") AS a
                      WHERE a.depe_superior=0
                        AND a.depe_id!=$depe_id
                      UNION ALL
                        SELECT d.depe_id::text||'_'||x.usua_id::text,LPAD(d.depe_id::TEXT,3,'0')||' '||d.depe_nombre||'-'||c.pers_apellpaterno||' '||c.pers_nombres||' ('||x.usua_login||')' 
                        FROM personal.persona_datos_laborales  b
                        LEFT JOIN personal.persona c      ON b.pers_id=c.pers_id
                        LEFT JOIN catalogos.dependencia d ON b.depe_id=d.depe_id 
                        LEFT JOIN (SELECT a.pers_id,
                                          b.usua_id,
                                          b.usua_login
                                      FROM personal.persona_datos_laborales a 
                                      LEFT JOIN admin.usuario b ON a.pdla_id=b.pdla_id
                                      WHERE b.usua_id IS NOT NULL) AS x ON b.pers_id=x.pers_id
                        WHERE b.pdla_estado=1 
                                AND b.depe_id=$depe_id                                
                                            
                      UNION ALL
                      SELECT a.grde_id::text||'@',LPAD(a.grde_id::TEXT,3,'0')||' GRUPO:'||grde_descripcion 
                      FROM gestdoc.grupos_derivaciones a
                      ORDER BY 2";
		return $sql;
	}                

        
        function getSQL_cbox4(){
		$sql="SELECT a.depe_id, 
                                LPAD(a.depe_id::TEXT,3,'0')||' '||COALESCE(jefe,'** SIN JEFE**')||' ('||COALESCE(cargo,'')||'-'||a.depe_nombre||'/'||a.depe_superior_nombre||')'AS descripcion
				FROM (".$this->getSQL().") AS a
                      WHERE a.depe_id>2 AND jefe IS NOT NULL
                      ORDER BY a.depe_nombre ";
		return $sql;
	}
        
        function getSQL_cbox5($usua_id){
                    $sql="SELECT  DISTINCT  a.pdla_id,
                                            a.jefe||' ('||a.pers_dni||')-'||a.cargo AS descripcion
                            FROM (".$this->getSQL().") AS a
                            WHERE a.pers_id IN 
                            ( SELECT b.pers_id
                                 FROM admin.usuario a
                                 LEFT JOIN personal.persona_datos_laborales b ON a.pdla_id=b.pdla_id
                                 WHERE a.usua_id='$usua_id'
                             ) 
                          ORDER BY 1";
                    
		return $sql;
        }
        
	function getSQL_lista(){
		$sql="SELECT text_concat(a.depe_id::text||',') AS lista
                        FROM catalogos.dependencia a 
                      WHERE a.depe_id>2 AND a.depe_habilitado=1
                      ORDER BY 1";
		return $sql;
	}        
}
/* Llamando a la subclase */



class dependenciasBuscarAjax_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.id,
                                    a.text
                            FROM (SELECT  a.depe_id::TEXT AS id,
                                                a.depe_id::TEXT ||' '||a.depe_nombre||COALESCE('/'||(SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(a.depe_id)),'') AS text
                                        FROM catalogos.dependencia a
                                            WHERE a.depe_superior=0
                                                AND a.depe_habiltado=1
                                                AND a.depe_id>1                                        
                                  ) AS a
                            ";
	}

	function whereID($id){
            $this->addWhere("a.id='$id'");
	}
        
        function whereBuscar($search){
            if( ctype_digit($search) ){
		if($search!='') {
                    $this->addWhere("(a.id='$search' OR a.text ILIKE '%$search%')");
                }
            }else{
                if($search!='') {
                    $array=explode(" ",$search);
                    $lista="";
                    for($i=0; $i<count($array); $i++){
                        if(trim($array[$i])!=''){
                            $lista.=$lista?" AND ":"";
                            $lista.="a.text ILIKE '%".trim($array[$i])."%'";
                        }
                    }       
                    $this->addWhere($lista);
                }
            }
	}
        
        function wherePersID($pers_id){
		$this->addWhere("a.id IN (SELECT DISTINCT a.depe_id::TEXT
		 			  FROM personal.persona_datos_laborales a
		 			  LEFT JOIN catalogos.dependencia b on  a.depe_id=b.depe_id
                                          WHERE a.pdla_estado=1 AND a.pers_id=$pers_id)");
	}        
        
        function orderUno(){
		$this->addOrder("2");
	}
}


class dependenciasBuscarAjax2_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.id,
                                    a.text
                            FROM (SELECT  a.depe_id::TEXT AS id,
                                    a.depe_id::TEXT||' '||a.depe_nombre AS text
                                    FROM catalogos.dependencia a
                                        WHERE a.depe_superior=0
                                            AND a.depe_habiltado=1
                                            AND a.depe_id>1) AS a                                            

                            ";
	}

	function whereID($id){
            $this->addWhere("a.id='$id'");
	}
        
        function whereBuscar($search){
            if( ctype_digit($search) ){
		if($search!='') {
                    $this->addWhere("(a.id='$search' OR a.text ILIKE '%$search%')");
                }
            }else{
                if($search!='') {
                    $array=explode(" ",$search);
                    $lista="";
                    for($i=0; $i<count($array); $i++){
                        if(trim($array[$i])!=''){
                            $lista.=$lista?" AND ":"";
                            $lista.="a.text ILIKE '%".trim($array[$i])."%'";
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

class dependenciasJefeBuscarAjax_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  id,
                                    text
                            FROM    (SELECT a.depe_id::TEXT AS id,
                                            a.depe_id::TEXT||' '||a.depe_nombre
                                            ||'/'||COALESCE((SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(a.depe_id)),'')
                                            ||' ('||COALESCE(TRIM(c.pers_apellpaterno)||' '||TRIM(c.pers_apellmaterno)||' '||TRIM(c.pers_nombres),'** SIN JEFE**')||')' AS text
                                        FROM catalogos.dependencia a
                                        LEFT JOIN personal.persona_datos_laborales b ON a.pdla_id=b.pdla_id
                                        LEFT JOIN personal.persona c                 ON b.pers_id=c.pers_id
                                        WHERE a.depe_superior=0
                                              AND a.depe_habiltado=1
                                              AND a.depe_id>2  
                                              AND a.pdla_id IS NOT NULL) AS a
                            ";
	}

	function whereID($id){
            $this->addWhere("a.id='$id'");
	}

        
        function wherePersID($pers_id){
		$this->addWhere("a.id IN (SELECT DISTINCT a.depe_id::TEXT
		 			  FROM personal.persona_datos_laborales a
		 			  LEFT JOIN catalogos.dependencia b on  a.depe_id=b.depe_id
                                          WHERE a.pdla_estado=1 AND a.pers_id=$pers_id)");
	}
        
        function whereBuscar($search){
            if( ctype_digit($search) ){
		if($search!='') {
                    $this->addWhere("(a.id='$search' OR a.text ILIKE '%$search%')");
                }
            }else{
                if($search!='') {
                    $array=explode(" ",$search);
                    $lista="";
                    for($i=0; $i<count($array); $i++){
                        if(trim($array[$i])!=''){
                            $lista.=$lista?" AND ":"";
                            $lista.="a.text ILIKE '%".trim($array[$i])."%'";
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


class dependenciasEmpleadosBuscarAjax_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.id,
                                    a.text
                            FROM (SELECT b.depe_id::TEXT||'_'||x.usua_id::TEXT AS id,
                                         c.pers_apellpaterno||' '||c.pers_apellmaterno||' '||c.pers_nombres||' ('||x.usua_login||') '||d.depe_nombre||COALESCE('/'||(SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(b.depe_id)),'') AS text
                                    FROM personal.persona_datos_laborales  b
                                    LEFT JOIN personal.persona c      ON b.pers_id=c.pers_id
                                    LEFT JOIN catalogos.dependencia d ON b.depe_id=d.depe_id 
                                    LEFT JOIN (SELECT a.pers_id,
                                                      b.usua_id,
                                                      b.usua_login
                                                  FROM personal.persona_datos_laborales a 
                                                  LEFT JOIN admin.usuario b ON a.pdla_id=b.pdla_id
                                                  WHERE b.usua_id IS NOT NULL) AS x ON b.pers_id=x.pers_id
                                    WHERE   b.pdla_estado=1
                                            AND d.depe_superior=0
                                            AND d.depe_habiltado=1
                                            AND d.depe_id>1
                                            AND c.pers_tipo_persona=1
                                        ) AS a
                                  ";
	}

	function whereID($id){
            $this->addWhere("a.id='$id'");
	}
        
        function whereBuscar($search){
            if( ctype_digit($search) ){
		if($search!='') {
                    $this->addWhere("(a.id='$search' OR a.text ILIKE '%$search%')");
                }
            }else{
                if($search!='') {
                    $array=explode(" ",$search);
                    $lista="";
                    for($i=0; $i<count($array); $i++){
                        if(trim($array[$i])!=''){
                            $lista.=$lista?" AND ":"";
                            $lista.="a.text ILIKE '%".trim($array[$i])."%'";
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


class dependenciasEmpleados2BuscarAjax_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.id,
                                    a.text
                            FROM (SELECT b.pdla_id::TEXT AS id,
                                         c.pers_apellpaterno||' '||c.pers_apellmaterno||' '||c.pers_nombres||' ('||x.usua_login||') '||d.depe_nombre||COALESCE('/'||(SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(b.depe_id)),'') AS text
                                    FROM personal.persona_datos_laborales  b
                                    LEFT JOIN personal.persona c      ON b.pers_id=c.pers_id
                                    LEFT JOIN catalogos.dependencia d ON b.depe_id=d.depe_id 
                                    LEFT JOIN (SELECT a.pers_id,
                                                      b.usua_id,
                                                      b.usua_login
                                                  FROM personal.persona_datos_laborales a 
                                                  LEFT JOIN admin.usuario b ON a.pdla_id=b.pdla_id
                                                  WHERE b.usua_id IS NOT NULL) AS x ON b.pers_id=x.pers_id
                                    WHERE   b.pdla_estado=1
                                            AND d.depe_superior=0
                                            AND d.depe_habiltado=1
                                            AND d.depe_id>1
                                            AND c.pers_tipo_persona=1
                                        ) AS a
                                  ";
	}

	function whereID($id){
            $this->addWhere("a.id='$id'");
	}
        
        function whereBuscar($search){
            if( ctype_digit($search) ){
		if($search!='') {
                    $this->addWhere("(a.id='$search' OR a.text ILIKE '%$search%')");
                }
            }else{
                if($search!='') {
                    $array=explode(" ",$search);
                    $lista="";
                    for($i=0; $i<count($array); $i++){
                        if(trim($array[$i])!=''){
                            $lista.=$lista?" AND ":"";
                            $lista.="a.text ILIKE '%".trim($array[$i])."%'";
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

class dependenciasBuscarTodos_SQLlista extends selectSQL {

	function __construct($id_varios){
		$this->sql="SELECT  a.id,
                                    a.text
                            FROM (SELECT  a.depe_id::TEXT AS id,
                                                a.depe_nombre||COALESCE('/'||(SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(a.depe_id)),'') AS text
                                        FROM catalogos.dependencia a
                                            WHERE a.depe_superior=0
                                                AND a.depe_habiltado=1
                                                AND a.depe_id>1
                                                AND a.depe_id::TEXT IN (SELECT UNNEST(string_to_array('$id_varios',',')))
                                   UNION ALL
                                    SELECT d.depe_id::TEXT||'_'||x.usua_id::TEXT AS id,
                                                c.pers_apellpaterno||' '||c.pers_apellmaterno||' '||c.pers_nombres||' ('||x.usua_login||') '||d.depe_nombre||COALESCE('/'||(SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(b.depe_id)),'') AS text
                                            FROM personal.persona_datos_laborales  b
                                            LEFT JOIN personal.persona c      ON b.pers_id=c.pers_id
                                            LEFT JOIN catalogos.dependencia d ON b.depe_id=d.depe_id 
                                            LEFT JOIN (SELECT a.pers_id,
                                                              b.usua_id,
                                                              b.usua_login
                                                          FROM personal.persona_datos_laborales a 
                                                          LEFT JOIN admin.usuario b ON a.pdla_id=b.pdla_id
                                                          WHERE b.usua_id IS NOT NULL) AS x ON b.pers_id=x.pers_id
                                            WHERE   b.pdla_estado=1
                                                    AND d.depe_superior=0
                                                    AND d.depe_habiltado=1
                                                    AND d.depe_id>1     
                                                    AND c.pers_tipo_persona=1
                                                    AND d.depe_id::TEXT||'_'||x.usua_id::TEXT IN (SELECT UNNEST(string_to_array('$id_varios',','))) 
                                  ) AS a
                                 ORDER BY ";
                
                                 $order_by="";
                                 $ar_id_varios=explode(",",$id_varios);
                                 if(is_array($ar_id_varios)){
                                    for($x=0;$x<count($ar_id_varios);$x++){
                                        $order_by.=$order_by!=""?",":"";
                                        $order_by.="a.id='".$ar_id_varios[$x]."' DESC ";
                                    } 
                                 }
                                 $this->sql.=$order_by;
                
                            
	}
                
}


class dependenciaSuperior_SQLBox extends selectSQL {
	function __construct($depe_id){
		$this->sql="SELECT a.depe_id AS tree_id,
                                   LPAD(a.depe_id::TEXT,3,'0')||' '||a.depe_nombre||COALESCE(' ['||c.emru_ruc||']','') AS tree_textaux 
                                FROM catalogos.func_treedependencia2($depe_id) AS a
                                LEFT JOIN admin.empresa_ruc c ON a.emru_id=c.emru_id
                            WHERE a.depe_id>0 
                                AND a.depe_superior=1  
                                AND c.emru_habilitado=1
                            ";
	}    
}

class dependenciaSuperior_SQLBox2 extends selectSQL {
	function __construct($depe_id){
		$this->sql="SELECT a.depe_id AS id,
                                   LPAD(a.depe_id::TEXT,3,'0')||' '||a.depe_nombre||COALESCE(' ['||c.emru_ruc||']','') AS descripcion 
                                FROM catalogos.dependencia AS a
                                LEFT JOIN admin.empresa_ruc c ON a.emru_id=c.emru_id
                            WHERE   a.depe_superior=1
                                AND c.emru_habilitado=1
                            ";
                if($depe_id>0){
                    $this->sql.= " AND a.depe_id=$depe_id ";
                }
                
                $this->sql.= " ORDER BY 1 ";
	}    
}


class dependenciaSuperior_SQLBox3 extends selectSQL {
	function __construct($depe_id){
		$this->sql="SELECT a.depe_id AS tree_id,
                                   LPAD(a.depe_id::TEXT,3,'0')||' '||a.depe_nombre AS tree_textaux 
                                FROM catalogos.func_treedependencia2($depe_id) AS a
                            WHERE a.depe_id>0 
                                AND a.depe_superior=1  
                            ";
	}    
}

class dependenciaEstablecimiento extends selectSQL {
	function __construct($depe_id,$depe_id_almacen){
		$this->sql="SELECT  a.depe_id,
                                    LPAD(a.depe_id::TEXT,3,'0')||' '||a.depe_nombre
                                    FROM catalogos.func_treedependencia2($depe_id) AS a
                                    WHERE a.depe_id>0
                                        AND a.depe_almacen_ventas=1
                                    ";
                                        
                if($depe_id_almacen!=''){
                    $this->sql.= " AND a.depe_id IN ($depe_id_almacen) ";
                }
                
                $this->sql.= " ORDER BY 1 ";
	}    
}

class dependenciaEstablecimiento2 extends selectSQL {
	function __construct($depe_id,$depe_id_almacen){
            
		$this->sql="SELECT  a.depe_id,
                                    LPAD(a.depe_id::TEXT,3,'0')||' '||a.depe_nombre
                                    FROM catalogos.func_treedependencia2($depe_id) AS a
                                    WHERE a.depe_id>0
                                        AND a.depe_almacen_ventas=1
                                        AND a.depe_id!=$depe_id_almacen 
                                    ORDER BY 1 ";
	}    
}

if (isset($_GET["control"])){
    $control=base64_decode($_GET["control"]);
    if($control){
            require_once("../../library/library.php");

            /*	verificaciï¿½n a nivel de usuario */
            verificaUsuario(1);
            verif_framework();

            $param= new manUrlv1();
            $param->removePar('control');
            $param->removePar('relacionamento_id');
            $param->removePar('pg'); /* Remuevo el parÃ¡metro */

            /* Recibo la pï¿½gina actual de la lista y lo agrego como parÃ¡metro a ser enviado por la URL.  Este dato viene por POST */
            $pg = getParam("pg");
            $param->addParComplete('pg',$pg); /* Agrego el parÃ¡metro */

            //	conexiÃ³n a la BD
            $conn = new db();
            $conn->open();

            $dml=new dependencia();

            switch($control){
                    case 1: // Guardar
                            $dml->guardar();
                            break;
                    case 2: // Eliminar
                            $dml->eliminar();
                            break;
            }
            //	cierra la conexiÃ³n con la BD
            $conn->close();
    }
}
