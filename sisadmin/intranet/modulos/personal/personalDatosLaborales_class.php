<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");
//if(SIS_GESTDOC==1){
//    require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/modulos/gestdoc/setAcumDespacho_class.php");
//}
class clsDatosLaborales extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='personal.persona_datos_laborales'; //nombre de la tabla
		$this->setKey='pdla_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;
                $this->setNivelAudita=9; //SI replica cada campo en la tabla (la tabla de auditoria debe tener los campos "sql_type" SMALLINT, "sql_command" TEXT+ los mismo campos q la tabla auditada ); false->guarda solo datos de auditoria ("sql_type" SMALLINT, "sql_command" TEXT)*/
                

		/* Destinos luego de actulizar, agregar o eliminar un registro */
		$this->destinoInsert='personalDatosLaborales_buscar.php';
		$this->destinoUpdate='personalDatosLaborales_buscar.php';
		$this->destinoDelete='personalDatosLaborales_buscar.php';

		$this->arrayNameVar[0]='nomeCampoForm';
		$this->arrayNameVar[1]='busEmpty';
		$this->arrayNameVar[2]='cadena';
		$this->arrayNameVar[3]='pg';
		$this->arrayNameVar[4]='colSearch';
		$this->arrayNameVar[5]='numForm';

	}

        function getSql(){
		$sql=new clsDatosLaborales_SQLlista();
		$sql->whereID($this->id);
		return($sql->getSQL());
	}

        function addField(&$sql){
		$sql->addField("pdla_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
		$sql->addField("pdla_actualusua", getSession("sis_userid"), "String");
                
                if ($_POST["hx_pdla_set_encargado"]){
                    $sql->addField("pdla_encargado", 1, "Number");
                }else{
                    $sql->addField("pdla_encargado", 0, "Number");
                }
	}

	function buscar($op,$formData,$arrayParam,$pg,$Nameobj='',$nbusc_char='')

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

                $nbusc_sitlaboral=is_array($formData)?$formData['nbusc_sitlaboral']:$paramFunction->getValuePar('nbusc_sitlaboral');

                if(!$nbusc_char){
                    $nbusc_char=is_array($formData)?$formData['nbusc_char']:$paramFunction->getValuePar('nbusc_char');
                }
                
                $sub_dependencia=is_array($formData)?$formData['tr_sub_dependencia']:$paramFunction->getValuePar('tr_sub_dependencia');
                
                //$objResponse->addAlert($nbusc_char);
                $param->removePar('nbusc_sitlaboral');
                $param->removePar('nbusc_char');
                $param->removePar('tr_sub_dependencia');
                $param->addParComplete('nbusc_sitlaboral',$nbusc_sitlaboral);
                $param->addParComplete('nbusc_char',$nbusc_char);
                $param->addParComplete('tr_sub_dependencia',$sub_dependencia);
                
                $estado=$formData['nbusc_estado'];
                $filtrar_jefes=$formData['nbusc_filtrar_jefes'];
                $depe_id=$formData['nbusc_depe_id'];
                
                //$objResponse->addAlert($nbusc_sitlaboral);
		if(strlen($cadena)>0 or $busEmpty==1 or $nbusc_sitlaboral or $nbusc_char){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias

			$sql=new clsDatosLaborales_SQLlista();
                        $sql->whereTipoPersona(1);//solo empleados
                        
                        if($sub_dependencia){
                            $sql->whereDepeID($sub_dependencia);
                        }else{
                            if($depe_id){
                                $sql->whereDepeTodos($depe_id);
                            }else{
                                $sql->whereDepeTodos(getSession('sis_depe_superior'));                                
                            }
                        }
                        
                        $sql->whereNOAdmin();
                        
                        if($nbusc_sitlaboral){
                            $sql->whereSitLaboral($nbusc_sitlaboral);
                        }
                        
                        if($estado>0){
                            $sql->whereEstado($estado);
                        }
                        
                        if($filtrar_jefes>0){
                            $sql->whereJefe();
                        }
                        
                        if($nbusc_char)
                            $sql->whereChar($nbusc_char);
			else

                            //se analiza la columna de busqueda
                            switch($colSearch){
				case 'codigo': // si se recibe el campo id
					$sql->whereDNI($cadena);
					break;

				default:// si se no se recibe ningun campo de busqueda
					if(ctype_digit($cadena)) //si la cadena recibida son todos digitos
                                            $sql->whereDNI($cadena);
					else
                                            $sql->whereDescrip($cadena);
					break;
				}
			$sql->orderDos();

			$sql=$sql->getSQL();
                        //echo $sql;
//			$objResponse->addAlert($sql);

			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) setSession("cadSearch",$cadena);

			$rs = new query($conn, strtoupper($sql),$pg,80);
			
                        $otable = new  Table("","100%",14);
                        
			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;

			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");

			if ($rs->numrows()>0) {
					$otable->addColumnHeader("<input type=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">"); // Coluna com checkbox
					$otable->addColumnHeader("DNI",true,"3%", "L");
					$otable->addColumnHeader("Apellidos y Nombres",true,"27%", "L");
					$otable->addColumnHeader("Dependencia",true,"17%", "L");
                                        $otable->addColumnHeader("Superior",true,"10%", "L");
					$otable->addColumnHeader("Cond.Laboral",true,"15%", "L");
					$otable->addColumnHeader("Cargo Funcional",true,"15%", "L");
                                        $otable->addColumnHeader("Jefe",false,"1%", "C");
                                        $otable->addColumnHeader("Encarg",false,"1%", "C");
                                        $otable->addColumnHeader("F.Doc",true,"3%", "L");
                                        $otable->addColumnHeader("O",false,"1%", "L","","Origen del Registro");                                        
					$otable->addColumnHeader("Est","1%", "L");
                                        $otable->addColumnHeader("Usuario",false,"5%", "L");
                                        $otable->addColumnHeader("",false,"1%","L");
                                        

					$otable->addRow(); // adiciona la linea (TR)
					while ($rs->getrow()) {
						$id = $rs->field("pdla_id"); // captura la clave primaria del recordsource
						$campoTexto_de_Retorno = especialChar($rs->field("empleado"));
                                                $empleado_cargo=$rs->field("empleado").' '.$rs->field("pdla_cargofuncional");
                                                $jefe=$rs->field("jefe").'-'.$rs->field("jefe_cargo");
                                                $es_jefe=$rs->field("es_jefe");
                                                $estado=$rs->field("pdla_estado");
                                                $pdla_origen=$rs->field("pdla_origen");
                                                
						$otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");
						$otable->addData(addLink($rs->field("pers_dni"),"personalDatosLaborales_edicion.php?id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
						$otable->addData($rs->field("empleado"));
                                                $otable->addData($rs->field("depe_id").' '.$rs->field("depe_nombrecorto"),"L","","","$jefe");
                                                $otable->addData($rs->field("depe_superior_nombre"),"L");
                                                $otable->addData($rs->field("sit_laboral"));
                                                $otable->addData($rs->field("pdla_cargofuncional"));
                                                $otable->addData(iif($es_jefe,"==",1,"SI",""),"C");
                                                $otable->addData($rs->field("tipo_encargado"),"C");
                                                $otable->addData(dtos($rs->field("pdla_fechadocumento")));
                                                $otable->addData($rs->field("pdla_origen"),"C","","",$rs->field("origen"));
                                                $otable->addData(substr($rs->field("estado"),0,6),"","",$id);
                                                $otable->addData($rs->field("username"));                                                
                                                $otable->addData(btnHerramientasDatosLaborales($id,$estado,$empleado_cargo,$es_jefe,$pdla_origen));
                                                
                                                if($estado==9){
                                                    $otable->addRow('ANULADO');
                                                }else{
                                                    $otable->addRow();
                                                }
					}
				$contenido_respuesta=$button->writeHTML();
				$contenido_respuesta.=$otable->writeHTML();
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";


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

        function buscarEmpleadoLabora($op,$cadena,$procedure,$Nameobj,$sitlaboral='')
        {
                global $conn,$param;
                $objResponse = new xajaxResponse();

                //$objResponse->addAlert($nbusc_sitlaboral);
                if(strlen($cadena)>0 || $sitlaboral){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias

                        $otable = new  Table("","100%",6);

                        $sql=new clsDatosLaborales_SQLlista();
                        if($sitlaboral){$sql->whereSitLaboral($sitlaboral);}
                        
                        if(ctype_digit($cadena)) //si la cadena recibida son todos digitos
                            $sql->whereDNI($cadena);
			else
                            $sql->whereDescrip($cadena);

			$sql->orderUno();

			$sql=$sql->getSQL();


                        $rs = new query($conn, strtoupper($sql));

                        if ($rs->numrows()>0) {
                                        $otable->addColumnHeader(""); 
                                        $otable->addColumnHeader("DNI","5%","L");
                                        $otable->addColumnHeader("Apellidos y Nombres","40%","L");
                                        $otable->addColumnHeader("Dependencia","20%","L");
                                        $otable->addColumnHeader("Sit.Laboral","15%","L");
                                        $otable->addColumnHeader("Categor&iacute;a","20%","L");

                                        $otable->addRow(); // adiciona la linea (TR)
                                        $btnFocus="";
                                        while ($rs->getrow()) {
                                                $id = $rs->field("pdla_id"); // captura la clave primaria del recordsource
                                                $campoTexto_de_Retorno = especialChar($rs->field("empleado"));

                                                $button = new Button;
                                                $button->setDiv(FALSE);
                                                $button->setStyle("");
                                                $button->addItem("Aceptar","javascript:xajax_$procedure('$id','$campoTexto_de_Retorno','$Nameobj')","content",2,0,"botonAgg","button","","btn_$id");
                                                $otable->addData($button->writeHTML());	                                                
                                               
                                                $otable->addData($rs->field("pers_dni"));                                                    
                                                $otable->addData($rs->field("empleado"));
                                                $otable->addData(substr($rs->field("depe_nombrecorto"),0,25));
                                                $otable->addData(substr($rs->field("sit_laboral"),0,20));
                                                $otable->addData($rs->field("categoria_obrero"));

                                                $otable->addRow();
                                                $btnFocus=$btnFocus?$btnFocus:"btn_$id";
                                        }
                                $contenido_respuesta.=$otable->writeHTML();
                                $contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";


                        } else {
                                $otable->addColumnHeader("!NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
                                $otable->addRow();
                                $contenido_respuesta=$otable->writeHTML();
                        }
            }
            else{
                $contenido_respuesta="";
            }
            $objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
            $objResponse->addScript("document.frmAgregar.$btnFocus.focus()");
            return $objResponse;
        }        
        
        function refrescar($id){
		global $conn;

		/* Sql a ejecutar */
                $setNoti=new setNotiDespachosEnProceso();
                $setNoti->whereID($id);
                //$setNoti->NOAdmin();

                $setNotiZero=new setNotiDespachosEnProceso_zero();
                $setNotiZero->whereID($id);
                //$setNotiZero->NOAdmin();

                $sqlCommand =$setNoti->getSQL().";";
                $sqlCommand .=$setNotiZero->getSQL().";";

                /* Ejecuto la sentencia */
                //alert($id);
                //echo $sqlCommand;
		$padre_id=$conn->execute($sqlCommand);
		$error=$conn->error();
		if($error) alert($error);
                else alert('Proceso Terminado!');
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
	
                $location=$_SERVER['DOCUMENT_ROOT'] ."/docs/datos_laborales/".SIS_EMPRESA_RUC;
                if (!is_dir($location) && !mkdir($location, '0755', true)) {
                }
                
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
                //echo $sql->getSQL();
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
			if($notice){
                            alert($notice,0);
                        }
                        //proceso de auditoria de tablas
                        //SI replica cada campo en la tabla (la tabla de auditoria debe tener los campos "sql_type" SMALLINT, "sql_command" TEXT+ los mismo campos q la tabla auditada ); false->guarda solo datos de auditoria ("sql_type" SMALLINT, "sql_command" TEXT)*/
                         switch($this->setNivelAudita){
                                case 1: //la tabla de auditoria tiene el campo usuario, que graba el registro de auditoria
                                    $sql = new UpdateSQL();
                                    $sql->setTable($this->setTable.'_auditoria');
                                    $sql->setAction("INSERT");
                                    $sql->addField('sql_type',$sql_type,"Number");
                                    $sql->addField('sql_command',addslashes($sqlCommand),"String");
                                    $sql->addField($this->setKey,$padre_id,"Number");
                                    $sql->addField('usua_id', getSession("sis_userid"), "Number");
                                    $conn->execute($sql->getSQL());
                                    $error=$conn->error();
                                    if($error){alert($error);}/* Muestro el error y detengo la ejecución */
                                    break;

                                case 2:  //la tabla de auditoria NO tiene el campo usuario
                                    $sql = new UpdateSQL();
                                    $sql->setTable($this->setTable.'_auditoria');
                                    $sql->setAction("INSERT");
                                    $sql->addField('sql_type',$sql_type,"Number");
                                    $sql->addField('sql_command',addslashes($sqlCommand),"String");
                                    $sql->addField($this->setKey,$padre_id,"Number");
                                    $conn->execute($sql->getSQL());
                                    //echo $sql->getSQL();
                                    $error=$conn->error();
                                    if($error){
                                        //echo $sql->getSQL();
                                        alert($error);
                                        
                                    }/* Muestro el error y detengo la ejecución */
                                    break;
                            }

                        }
		
		/* */
		if ($this->valueKey) {// modificación

			$last_id=$this->valueKey; 
			if(strpos($destinoInsert, "?")>0){
				$destinoUpdate.="&id=$last_id";
                        }else{
				$destinoUpdate.="?id=$last_id";
                        }
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
        
        function refrescarx($id){
		global $conn;

		/* Sql a ejecutar */
                $sqlCommand ="UPDATE usuario set usua_acum_despachos_enproceso=
                                    (SELECT  COUNT(a.desp_id)
                                    FROM despachos_derivaciones a
                                    WHERE  (a.dede_estado=3 OR a.dede_estado=7)
                                    AND a.usua_idrecibe=usuario.usua_id
                                    GROUP BY a.usua_idrecibe)
                                WHERE usua_id in (SELECT usua_id FROM usuario WHERE pdla_id=$id);";

               /*PARA COLOCAR CERO (0) EN CASO NO HAYA DOCUMENTOS*/
                $sqlCommand  .="UPDATE usuario SET usua_acum_despachos_enproceso=0 WHERE 
                                    usua_acum_despachos_enproceso IS NULL AND usua_id IN (SELECT usua_id FROM usuario WHERE pdla_id=$id); ";

                /* Ejecuto la sentencia */
                //alert($id);
                //echo $sqlCommand;
		$padre_id=$conn->execute($sqlCommand);
		$error=$conn->error();
		if($error) alert($error);
                else alert('Proceso Terminado!');
	}

	/* Nombre del archivo php de la clase */
	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1);
	}
} /* Fin de la clase */

class clsDatosLaborales_SQLlista extends selectSQL {
	function __construct(){
		$this->sql = "SELECT a.*,
                                   TRIM(a.pdla_cargofuncional)||CASE WHEN a.pdla_encargado=1 THEN '(E)' ELSE '' END AS pdla_cargofuncional_ext,
                                   CASE WHEN a.pdla_encargado=1 THEN '(E)' ELSE '' END AS tipo_encargado,
                                   b.pers_dni,
                                   b.pers_apellpaterno||' '||b.pers_apellmaterno||' '||b.pers_nombres AS empleado,
                                   b.pers_direccion,
                                   b.pers_afpcus,
                                   b.pers_cant_hijos,
                                   b.pers_fechaingreso,
                                   b.pers_cuentadeposito,
                                   CASE WHEN b.pers_activo=1 THEN 'ACTIVO' ELSE 'DE BAJA' END AS estado_persona,
                                   h.rela_descripcion AS regimen_laboral,
                                   i.repe_descripcion AS regimen_pensionario,
                                   j.afp_nombre AS afp_nombre,
                                   COALESCE(c.tabl_descripaux,c.tabl_descripcion) AS sit_laboral,
                                   c.tabl_descripcion AS sit_laboral_larga,
                                   d.depe_nombre,
                                   d.depe_nombrecorto,
                                   (SELECT depe_nombrecorto FROM catalogos.func_treeunidsuperior2(a.depe_id)) AS depe_superior_nombre,
                                   CASE WHEN COALESCE(d.pdla_id,0)=a.pdla_id THEN 1
                                        ELSE 0
                                   END AS es_jefe,
                                   CASE WHEN a.pdla_estado=9 THEN 'DE BAJA' ELSE 'ACTIVO' END AS estado,
                                   TRIM(cc.pers_apellpaterno)||' '||TRIM(cc.pers_apellmaterno)||' '||TRIM(cc.pers_nombres) AS jefe,
                                   TRIM(bb.pdla_cargofuncional)||CASE WHEN bb.pdla_encargado=1 THEN '(E)' ELSE '' END AS jefe_cargo,
                                   CASE WHEN a.pdla_origen=0 THEN 'ORIGEN: DESDE DATOS PERSONALES' 
                                        WHEN a.pdla_origen=1 THEN 'ORIGEN: DESDE DATOS LABORALES'
                                        WHEN a.pdla_origen=2 THEN 'ORIGEN: DESDE DELEGAR MI CARGO'
                                   END AS origen,
                                   x.usua_login as username,
                                   y.usua_login as usernameactual
			FROM personal.persona_datos_laborales a
                        LEFT JOIN personal.persona b ON a.pers_id=b.pers_id
                        LEFT JOIN catalogos.tabla   c ON a.tabl_idsitlaboral=c.tabl_id
                        LEFT JOIN catalogos.dependencia   d ON a.depe_id=d.depe_id
                        LEFT JOIN catalogos.regimen_laboral   h         ON a.rela_id=h.rela_id
                        LEFT JOIN catalogos.regimen_pensionario   i     ON b.repe_id=i.repe_id
                        LEFT JOIN catalogos.afp                   j     ON b.afp_id=j.afp_id
                        LEFT JOIN personal.persona_datos_laborales bb ON d.pdla_id=bb.pdla_id
                        LEFT JOIN personal.persona cc                 ON bb.pers_id=cc.pers_id
                        LEFT JOIN admin.usuario x ON a.usua_id=x.usua_id
                        LEFT JOIN admin.usuario y ON a.pdla_actualusua=y.usua_id

	";
	}

	function whereID($id){
            $this->addWhere("a.pdla_id=$id");
	}

        function whereIDVarios($id_varios){
            $this->addWhere("a.pdla_id IN ($id_varios)");
	}        
        
	function whereDNI($dni){
            $this->addWhere("b.pers_dni='$dni'");
	}

        function whereTipoPersona($tipo_persona){
            $this->addWhere("b.pers_tipo_persona=$tipo_persona");
	}
        
        function whereNOAdmin(){
            $this->addWhere("a.pdla_id>1");
	}
        
        function PersID($pers_id){
            $this->addWhere("a.pers_id=$pers_id");
	}
        
        function whereEstado($estado){
            $this->addWhere("a.pdla_estado=$estado");
	}
        
	function whereDepeID($depe_id){
            $this->addWhere("a.depe_id=$depe_id");
	}

        function whereDepeTodos($depe_id) {
            $this->addWhere("a.depe_id IN (SELECT depe_id
                                                FROM catalogos.func_treedependencia2($depe_id)
                                                WHERE depe_id>0)");
        }
        
        function whereJefe(){
		$this->addWhere("COALESCE(d.pdla_id,0)=a.pdla_id");
	}
        
        function whereNoJefe(){
		$this->addWhere("a.pdla_id NOT IN 
                                        (SELECT a.pdla_id
                                            FROM catalogos.dependencia  a
                                            WHERE pdla_id IS NOT NULL)");
	}
        
        function whereActivo(){
                $this->addWhere("a.pdla_estado!=9");
        }
        
	function whereSitLaboral($sit_laboral){
            $this->addWhere("a.tabl_idsitlaboral=$sit_laboral");
	}

        function whereOrigen($origen){
            $this->addWhere("a.pdla_origen=$origen");
        }
        
        function whereChar($char){
            $this->addWhere("SUBSTR(b.pers_apellpaterno,1,1)='$char'");
        }

	function whereDescrip($descrip){
            $this->addWhere("( TRIM(b.pers_apellpaterno)||' '||TRIM(b.pers_apellmaterno)||' '||TRIM(b.pers_nombres) ILIKE '%$descrip%'
                              OR TRIM(b.pers_nombres)||' '||TRIM(b.pers_apellpaterno)||' '||TRIM(b.pers_apellmaterno) ILIKE '%$descrip%'
                              OR a.pdla_cargofuncional ILIKE '%$descrip%'
                              OR d.depe_nombre ILIKE '%$descrip%'    
                              ) ");
	}

	function orderUno(){
            $this->addOrder("b.pers_apellpaterno,b.pers_apellmaterno,b.pers_nombres DESC");
	}

	function orderDos(){
            $this->addOrder("a.pdla_id DESC");
	}        
        
        function orderTres(){
		$this->addOrder("a.tabl_idsitlaboral,b.pers_apellpaterno,b.pers_apellmaterno,b.pers_nombres DESC");
	}

	function getSQL_cbox(){
		$sql="SELECT    a.pdla_id,
                                LPAD(a.pdla_id::TEXT,3,'0')||' '||empleado||' ('||TRIM(COALESCE(a.pdla_cargofuncional,''))||'-'||TRIM(COALESCE(a.depe_nombre,''))||')'
                      FROM (".$this->getSQL().") AS a
                      WHERE a.pdla_id>1
                      ORDER BY a.empleado";
		return $sql;
	}        
        
        
}


class esJefe extends selectSQL {
    	function __construct($pers_id){
            $this->sql = "SELECT depe_id
                                FROM catalogos.dependencia
                                WHERE pdla_id IN 
                                (SELECT a.pdla_id
                                FROM personal.persona_datos_laborales a
                                WHERE a.pers_id=$pers_id
                                AND pdla_estado=1)
                                AND depe_habiltado=1
                                LIMIT 1";
        }    
}


class personaDatosLaborales_buscar extends selectSQL {

	function __construct($depe_id){
		$this->sql="SELECT  a.id,
                                    a.text
                            FROM (SELECT a.pdla_id AS id,
                                         b.pers_apellpaterno||' '||b.pers_apellmaterno||' '||b.pers_nombres||' ('||a.pdla_cargofuncional||') '||d.depe_nombre AS text
                                    FROM personal.persona_datos_laborales  a
                                    LEFT JOIN personal.persona b      ON a.pers_id=b.pers_id
                                    LEFT JOIN catalogos.dependencia d ON a.depe_id=d.depe_id 
                                    LEFT JOIN (SELECT a.pers_id,
                                                      b.usua_id,
                                                      b.usua_login
                                                  FROM personal.persona_datos_laborales a 
                                                  LEFT JOIN admin.usuario b ON a.pdla_id=b.pdla_id
                                                  WHERE b.usua_id IS NOT NULL) AS x ON b.pers_id=x.pers_id
                                    WHERE   a.pdla_estado=1
                                            AND d.depe_superior=0
                                            AND d.depe_habiltado=1
                                            AND b.pers_tipo_persona=1
                                            AND a.depe_id>1 ";
                
                    if($depe_id>0){
                        $this->sql.="                AND a.depe_id IN (SELECT depe_id
                                                                FROM catalogos.func_treedependencia2($depe_id)
                                                                WHERE depe_id>1) ";
                    }
                    
                $this->sql.="                       ) AS a
                                  ";
	}

	function whereID($id){
            $this->addWhere("a.id=$id");
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


class personasExternas_buscar extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.id,
                                    a.text
                            FROM (SELECT a.pdla_id AS id,
                                         b.pers_apellpaterno||' '||b.pers_apellmaterno||' '||b.pers_nombres||' '||d.depe_nombre AS text
                                    FROM personal.persona_datos_laborales  a
                                    LEFT JOIN personal.persona b      ON a.pers_id=b.pers_id
                                    LEFT JOIN catalogos.dependencia d ON a.depe_id=d.depe_id 
                                    LEFT JOIN (SELECT a.pers_id,
                                                      b.usua_id,
                                                      b.usua_login
                                                  FROM personal.persona_datos_laborales a 
                                                  LEFT JOIN admin.usuario b ON a.pdla_id=b.pdla_id
                                                  WHERE b.usua_id IS NOT NULL) AS x ON b.pers_id=x.pers_id
                                    WHERE   a.pdla_estado=1
                                            AND b.pers_tipo_persona=2
                                            AND a.depe_id=2
                                  ) AS a ";
                
   
	}

	function whereID($id){
            $this->addWhere("a.id=$id");
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

function btnHerramientasDatosLaborales($id,$estado,$empleado_cargo,$es_jefe,$pdla_origen){
    $botones="<div class=\"dropdown\">
                <button class=\"btn btn-default dropdown-toggle btn-sm\" type=\"button\" id=\"dropdownMenu1\" data-toggle=\"dropdown\"><span class=\"glyphicon glyphicon-cog\" aria-hidden=\"true\"></span> Herramientas
                <span class=\"caret\"></span></button>
                <ul class=\"dropdown-menu dropdown-menu-right\" aria-labelledby=\"dropdownMenu1\">";


    if($estado==1){
        if($es_jefe==0){
            $botones.= "<li><a href=\"javascript:getConfirm('Seguro de Establecer como Jefe?',function(result) {xajax_establecerJefe('$id')})\">Establecer como Jefe</a></li>";   
        }else{
            $botones.= "<li><a href=\"javascript:getConfirm('Seguro de Deshacer como Jefe?',function(result) {xajax_deshacerJefe('$id')})\">Deshacer como Jefe</a></li>";   
        }
        
        if ($pdla_origen!=0){//NO PERMITE DAR DE BAJA SI ES EL REGISTRO INICIAL, EN ESTE CASO SE HACE ROTACION
            $botones.= "<li><a href=\"javascript:getConfirm('Seguro de dar de baja al Empleado?',function(result) {xajax_darBaja('$id')})\">Dar de Baja</a></li>";           
        }
        
    }else{
        $botones.= "<li><a href=\"javascript:getConfirm('Seguro de Activar Empleado?',function(result) {xajax_habilitarEmpleado('$id')})\">Habilitar</a></li>";           
    }
    
    $botones.="</ul>
              </div>";
    return($botones);    
}


////////

/* Llamando a la subclase */
if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
            require_once($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");

            /*	verificación a nivel de usuario */
            verificaUsuario(1);
            verif_framework();

            $param= new manUrlv1();
            $param->removePar('control');

            //	conexión a la BD
            $conn = new db();
            $conn->open();

            $dml=new clsDatosLaborales();
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

                    case 3: // Refrescar
                            $id_padre=getParam("ID");
                            $dml->refrescar($id_padre);
                            break;



            }
            //	cierra la conexión con la BD
            $conn->close();
    }
}