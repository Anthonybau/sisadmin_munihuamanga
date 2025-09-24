<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class proveedor extends entidad {
	
	function __construct($id='',$title=''){
		$this->setTable='proveedor'; //nombre de la tabla
		$this->setKey='prov_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

		/* Destinos luego de actulizar, agregar o eliminar un registro */
		$this->destinoUpdate = "catalogosDependenciaExterna_buscar.php";
		$this->destinoInsert = "catalogosDependenciaExterna_buscar.php";
		$this->destinoDelete = "catalogosDependenciaExterna_buscar.php";		

		/* Datos que se retorna cuando la clase es cargada en una AvanzLookup */
		if(getParam('tr_tabl_tipoproveedor')==1)//person juridica
			$this->return_txt=strtoupper(getParam('Sr_prov_razsocial')); /* Texo que se devuelve */
		else
			$this->return_txt=strtoupper(getParam('Sr_prov_apellidos').' '.getParam('Sr_prov_nombres')); /* Texo que se devuelve */
							
		$this->return_val=getParam('Sr_prov_codigo'); /* Valor que se devuelve */
		
	}

	function guardar(){
		global $conn,$param;

		$nomeCampoForm=getParam("nomeCampoForm");
	
		$param->removePar('pg'); /* Remuevo el par�metro p�gina */
		$destinoUpdate=$this->destinoUpdate.$param->buildPars(true);
		$destinoInsert=$this->destinoInsert.$param->buildPars(true);
		
		// objeto para instanciar la clase sql
		$sql = new UpdateSQL();
				
		$sql->setTable($this->setTable);
		$sql->setKey($this->setKey, $this->valueKey, $this->typeKey);
	
		include("../guardar_tipoDato.php");
	
		if ($this->valueKey) { // modificaci�n
			$sql->setAction("UPDATE");
		}else{
			$sql->setAction("INSERT");
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
		
			if($nomeCampoForm){//si se llama desde una ventana emergente (avanzlookup) para seleccionar un valor
				/* Datos que se retornan desde un (avanzlookup) */
				$return_val=$this->return_val; /* Valor que se devuelve */
				$return_txt=$this->return_txt; /* Texo que se devuelve */
				/* Comandos Javascript */		
                                //parent.parent.opener.parent.content.document.forms[0]._Dummy$nomeCampoForm.value = '$return_txt';
                                if($param->getValuePar('clear')==3){
                                    echo "<script language=\"javascript\">
                                                    parent.parent.opener.parent.content.document.forms[0]._Dummy$nomeCampoForm.value = '$return_txt';
                                                    parent.parent.opener.parent.content.document.forms[0].$nomeCampoForm.value = '$return_val';
                                                    parent.parent.opener.parent.content.document.forms[0].__Change_$nomeCampoForm.value = 1;
                                                    parent.parent.opener.parent.content.document.forms[0].tx_prov_id.value = ".$padre_id.";
                                                    parent.parent.close();
                                            </script>";                                    
                                }else{
                                    echo "<script language=\"javascript\">
                                                    parent.opener.parent.parent.content.document.forms[0]._Dummy$nomeCampoForm.value = '$return_txt';
                                                    parent.opener.parent.parent.content.document.forms[0].$nomeCampoForm.value = '$return_val';
                                                    parent.opener.parent.parent.content.document.forms[0].__Change_$nomeCampoForm.value = 1;
                                                    parent.opener.parent.parent.content.document.forms[0].tx_prov_id.value = ".$padre_id.";
                                                    parent.parent.close();
                                            </script>";
                                }
			}else{ /* Si se llama desde una p�gina normal */
                            if ($this->valueKey) {// modificaci�n

                                        if(stripos($destinoUpdate,"javascript")){ // Si es un Script javascript el que quiero que se ejecute luego de insertar un registro
                                                echo $destinoUpdate;
                                                exit;				
                                        }
                                        if(strpos($destinoInsert, "?")>0)
                                                $destinoUpdate.="&id=$padre_id";
                                        else
                                                $destinoUpdate.="?id=$padre_id";

                                        redirect($destinoUpdate,"content");							
                            }else{ /* Inserci�n */
                                        if(stripos($destinoInsert,"javascript")){ // Si es un Script javascript el que quiero que se ejecute luego de insertar un registro
                                                echo $destinoInsert;
                                                exit;					
                                        }
                                        /*a�ado el id del registro ingresado*/
                                        if(strpos($destinoInsert, "?")>0)
                                                $destinoInsert.="&id=$padre_id";
                                        else
                                                $destinoInsert.="?id=$padre_id";

                                        redirect($destinoInsert,"content");							
                            }
                        }
	}

	
	//incorporo otros campos
	function addField(&$sql){
		if (getParam("hx_prov_estado"))
			$sql->addField("prov_estado", '1', "String");
		else
			$sql->addField("prov_estado", '0', "String");

		$sql->addField("prov_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
		$sql->addField("prov_actualusua", getSession("sis_userid"), "String");				

	}

	function getSql(){
		$sql=new proveedor_SQLlista();
		$sql->whereID($this->id);
		return($sql->getSQL());
	}

	function jsDevolver($nomeCampoForm){
			if($nomeCampoForm)
			//PARA EJECUTAR UNA FUNCION DEPENDNIENTE DEL VALOR ELEGIDO EN LA VENTANA DE BUSQUEDA
			//SE REQUIERE  DE self.parent.NOMBRE_DE_FUNCION_JAVASCRIPT(paramenteres) ejem:self.parent.xajax_cargaGrupo(valor,1)
                            					//self.parent.tb_remove();
			return ("function update(valor,ruc, descricao, numForm) {
					parent.parent.content.document.forms[numForm]._Dummy$nomeCampoForm.value = descricao;
					parent.parent.content.document.forms[numForm].$nomeCampoForm.value = ruc;
					parent.parent.content.document.forms[numForm].__Change_$nomeCampoForm.value = 1;
					parent.parent.content.document.forms[numForm].tr_prov_id.value = valor;
                                        self.parent.tb_remove();
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
	
			/* Creo my objeto Table */
			$otable = new TableSimple(iif($cadena,'!=','','RESULTADO DE: '.$cadena,''),"100%",6,'tLista'); // T�tulo, ancho, Cantidad de columas,id de la tabla						

			/* Guardo la p�gina actual en un campo oculto.  Para que al eliminar regrese a la p�gina donde estaba */
			$otable->addHtml("<input type='hidden' name='pg' value='$pg'>\n");
			
			/* Consulta sql a mostrar */
			$sql=new proveedor_SQLlista();
			
			//se analiza la columna de b�squeda
			switch($colSearch){
				case 'prov_id': // si se recibe el campo id
					$sql->whereID($cadena);								
					break;
				
				case 'codigo': // si se recibe el campo id
					$sql->whereRUC($cadena);								
					break;
	
				default:// si se no se recibe ningun campo de busqueda
//					if(ctype_digit($cadena)) //si la cadena recibida son todos digitos
//						$sql->whereID($cadena);
//					else
						$sql->whereDescrip($cadena);
	
					break;
				}
			
			$sql->orderUno();
			$sql=$sql->getSQL();
			//$objResponse->addAlert($sql);
			//echo $sql;
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) setSession("cadSearch",$cadena);
	
			$rs = new query($conn, strtoupper($sql));			

			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
					if (!$nomeCampoForm) /* Si no estoy en una b�squeda avanzada (AvanzLookup) */
						$otable->addColumnHeader("<input type=\"checkbox\" id=\"checkall\" >"); // Coluna com checkbox
			
					/* Agrego las cabeceras de mi tabla */
					$otable->addColumnHeader("C&oacute;digo","10%", "L"); 
					//$otable->addColumnHeader("CCI","10%", "L"); 
					$otable->addColumnHeader("Raz&oacute;n Social","75%", "L"); 
					$otable->addColumnHeader("Tipo","15%", "L"); 
					$otable->addRowHead(); 					
					while ($rs->getrow()) {
						$id = $rs->field("prov_id");// captura la clave primaria del recordsource
						$prov_codigo = $rs->field("prov_codigo");// 
						$campoTexto_de_Retorno = especialChar($rs->field("prov_razsocial"));

						if ($nomeCampoForm){ /* si la llamada es desde la busqueda avanzada (AvanzLookup) */
							$otable->addData(addLink($rs->field("prov_codigo"),"javascript:update('$id','$prov_codigo','$campoTexto_de_Retorno',$numForm)","Click aqu&iacute; para seleccionar el registro"));
						}elseif($op!=3){  /* Si estoy en la p�gina normal */ 
							/* agrego pg como par�metro a ser enviado por la URL */
							$param->removePar('pg'); /* Remuevo el par�metro */
							$param->addParComplete('pg',$pg); /* Agrego el par�metro */
							
							$otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" >");
							$otable->addData(addLink($rs->field("prov_codigo"),"catalogosDependenciaExterna_edicion.php?id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
						}
						
						//$otable->addData($rs->field("prov_cci"));
                                                $otable->addData(addLink($rs->field("prov_razsocial"),"catalogosDependenciaExterna_edicion.php?id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
						$otable->addData($rs->field("tipo"));						
						
						$otable->addRow();
					}
				$contenido_respuesta=$button->writeHTML();
				$contenido_respuesta.=$otable->writeHTML();
				if($rs->totalpages()>0){
					$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P�gina ".$pg." de ".$rs->totalpages()."</div>";
					$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";
				}
				else{
					$contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";				
				}

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
					//$objResponse->addScript("$('#_Dummytr_ruc_id').attr('value','".$campoTexto_de_Retorno."')");
					$objResponse->addScript($Nameobj ." = '".html_entity_decode($campoTexto_de_Retorno)."';");

					$objResponse->addScript('document.frm.tr_prov_id.value = '.$id);					
					return $objResponse;
					}
				else
					return stripslashes(html_entity_decode($campoTexto_de_Retorno));
				}
			else//si es llamado como una simple funciona de PHP
				return $contenido_respuesta	;
	}

	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	
}

class proveedor_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT a.*,
							 COALESCE(b.tabl_descripaux,b.tabl_descripcion) AS tipo,
							x.usua_login as username,
							y.usua_login as usernameactual
					FROM proveedor a
					LEFT JOIN tabla b on a.tabl_tipoproveedor=b.tabl_codigo AND b.tabl_tipo='16'
					LEFT JOIN usuario x ON a.usua_id=x.usua_id
					LEFT JOIN usuario y ON a.prov_actualusua=y.usua_id	
					 ";
	}

	function whereID($id){
		$this->addWhere("a.prov_id=$id");	
	}

	function whereRUC($ruc){
		$this->addWhere("a.prov_codigo='$ruc'");	
	}

	function whereDescrip($descrip){
		if($descrip) $this->addWhere("(a.prov_codigo ILIKE '%$descrip%'  OR a.prov_razsocial ILIKE '%$descrip%')");	
	}
	
	function whereProvee_tipo(){
		$this->addWhere("(a.tabl_tipoproveedor=1 OR a.tabl_tipoproveedor=2)");	
	}
	
	function orderUno(){
		$this->addOrder("a.prov_id DESC");		
	}	
}

if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
            include("../../library/library.php");
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

            //	conexi�n a la BD
            $conn = new db();
            $conn->open();

            $dml=new proveedor();

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