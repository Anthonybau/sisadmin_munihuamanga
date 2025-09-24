<?php
require_once('selectSQL.php');

class entidad extends selectSQL{
	var $setTable; 		/* Nombre de la tabla */
	var $setKey; 		/* Nombre del campo PK */
	var $valueKey; 		/* Valor del campo PK */
	var $typeKey;		/* Tipo de dato del campo PK */
	var $destinoUpdate; /* Página que se cargara al Editar y actualizar un registro */
	var $destinoInsert; /* Página que se cargara al Insertar un registro */
	var $destinoDelete;	/* P�gina que se cargara al Eliminar un registro */
	var $existe;		/* Atributo que indica si la clase se ha creado con �xito al recibir un id */
	var $title; 		/* Título de la tabla */
	var $field;			/* Array que contiene todo un registro de la tabla */
	var $return_val; 	/* Valor que se retorna cuando la clase es cargada en un AvanzLookup */
	var $return_txt;  	/* Descripción que se retorna cuando la clase es cargada en un AvanzLookup */
	var $winWidth;  	/* Ancho de la ventana Thickbox cuando se cargue la claes en un Avanzlookup */
	var $is_thinckbox;  	/* Alto de la ventana Thickbox cuando se cargue la claes en un Avanzlookup */		
	var $winHeight;  	/* Alto de la ventana Thickbox cuando se cargue la claes en un Avanzlookup */	
	var $pagEdicion;    /* Página de edición de los datos de la clase */
	var $pagBuscar;    /* Página de edición de los datos de la clase */
	var $arrayNameVar; /*contiene el nombre de las variables a utilizarse*/
        var $setNivelAudita=0; //0->NO replica en tabla de auditoria;
                          //1->SI replica en tabla de auditoria ($setTable+.'_auditoria' )  guarda solo datos de auditoria ("pk_de_tabla_auditoria" SERIAL, "sql_type" SMALLINT, "sql_command" TEXT, "usua_id" INTEGER )*/
                          //2->SI replica cada campo en la tabla (la tabla de auditoria debe tener los campos "pk_de_tabla_auditoria" SERIAL, "sql_type" SMALLINT, "sql_command" TEXT+ los mismo campos q la tabla auditada );

	function __construct($id='',$title=''){
		/* Esto se modifica en cada subclase */
	}

	function buscar($op,$formData,$arrayParam,$pg,$Nameobj='')
	{
		/* Esto se modifica en cada subclase */
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
                //echo $sql->getSQL();
                //exit(0);
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
                                    $sql->setTable($this->setTable.'_auditoria');
                                    $sql->setAction("INSERT");
                                    $sql->addField('sql_type',$sql_type,"Number");
                                    $sql->addField('sql_command',addslashes($sqlCommand),"String");
                                    $sql->addField($this->setKey,$padre_id,"Number");
                                    $conn->execute($sql->getSQL());
                                    //echo $sql->getSQL();
                                    $error=$conn->error();
                                    if($error){alert($error);}/* Muestro el error y detengo la ejecución */
                                    break;
                            }

                        }
		
		/* */
		if ($this->valueKey) {// modificación
			if($this->is_thinckbox){ // Si es un Script javascript el que quiero que se ejecute luego de insertar un registro
				echo "<script language=\"javascript\">
						self.parent.tb_remove();
						self.parent.document.location='$destinoUpdate'
					</script>"; /* Al recargar la p�g. se cierra el Thickbox que he utilizado para editar o modificar */
			}
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
	
				/* Comandos Javascript */		
				echo "<script language=\"javascript\">
						parent.parent.content.document.forms[0]._Dummy$nomeCampoForm.value = '$return_txt';
						parent.parent.content.document.forms[0].$nomeCampoForm.value = '$return_val';
						parent.parent.content.document.forms[0].__Change_$nomeCampoForm.value = 1;
						self.parent.tb_remove();
					</script>";
			}else{ /* Si se llama desde una p�gina normal */
				if($this->is_thinckbox){ // Si es un Script javascript el que quiero que se ejecute luego de insertar un registro
					echo "<script language=\"javascript\">
							self.parent.tb_remove();
							self.parent.document.location='$destinoInsert'
						</script>"; /* Al recargar la p�g. se cierra el Thickbox que he utilizado para editar o modificar */
					}
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

	//metodo para incorporar otros campos
	function addField(&$sql){
		}
	
	function anular($op){
		global $conn,$param;
		
		/* captura y prepara la lista de registros a ser eliminados */ 
		$arLista_anular = getParam("sel");
		if (is_array($arLista_anular)) {
		 $lista_anular = implode(",",$arLista_anular);
		}
		
		if(strtolower($this->typeKey)=='string'){
			/* debido a que el campo clave es char */
			$lista_anular=str_replace(",","','",$lista_anular); 
		}

		/* Sql a ejecutar */
		$sqlCommand="UPDATE $this->setTable SET ".str_replace('_id','_estado',$this->setKey)."=$op ";
		$sqlCommand.=" WHERE $this->setKey ";
		$sqlCommand.=" IN (".iif(strtolower($this->typeKey),"==","string","'","").$lista_anular.iif(strtolower($this->typeKey),"==","string","'","").") ";
		$sqlCommand.=" AND usua_id=".getSession("sis_userid");
		$sqlCommand.=" RETURNING $this->setKey ";
                            
		/* Ejecuto la sentencia */
		$padre_id=$conn->execute($sqlCommand);
		$error=$conn->error();		
		if($error) alert($error);
		else{
                        if ($this->setNivelAudita>0){
                            //ejecuto el insert en la tabla auditoria
                            $sql->setTable($this->setTable.'_auditoria');
                            $sql->setAction("INSERT");
                            $sql->addField('sql_type',2,"Number");
                            $sql->addField('sql_command',$sqlCommand);
                            $sql->addField($this->setKey,$padre_id,"Number");
                            $sql->addField('usua_id', getSession("sis_userid"), "Number");
                            $conn->execute($sql->getSQL());
                            $error=$conn->error();
                            if($error){alert($error);}/* Muestro el error y detengo la ejecución */
                        }

			if(stripos($this->destinoDelete,"javascript")){ // Si es un Script javascript el que quiero que se ejecute luego de eliminar el(los) registro(s)
				echo $this->destinoDelete;
				exit;
			}
			redirect($this->destinoDelete.$param->buildPars(true),"content");		
		}
	}

	function eliminar(){
		global $conn,$param;

		$destinoDelete=$this->destinoDelete.$param->buildPars(true);		
		
		/* captura y prepara la lista de registros a ser eliminados */ 
		$arLista_elimina = getParam("sel");
		if (is_array($arLista_elimina)) {
		 $lista_elimina = implode(",",$arLista_elimina);
		}
		if(!$lista_elimina) return;

		if(strtolower($this->typeKey)=='string'){
			/* debido a que el campo clave es char */
			$lista_elimina=str_replace(",","','",$lista_elimina);
		}

		/* Sql a ejecutar */
		$sqlCommand ="DELETE FROM $this->setTable WHERE $this->setKey";
		$sqlCommand.=" IN (".iif(strtolower($this->typeKey),"==","string","'","").$lista_elimina.iif(strtolower($this->typeKey),"==","string","'","").") " ;
		$sqlCommand.=" AND usua_id=".getSession("sis_userid");
                $sqlCommand.=" RETURNING $this->setKey ";

		/* Ejecuto la sentencia */
                //alert($sqlCommand);
		$padre_id=$conn->execute($sqlCommand);
		$error=$conn->error();		
		if($error) alert($error);
		else{

                        if ($this->setNivelAudita>0){
                            //ejecuto el insert en la tabla auditoria
                            $sql = new UpdateSQL();
                            $sql->setTable($this->setTable.'_auditoria');
                            $sql->setAction("INSERT");
                            $sql->addField('sql_type',3,"Number");
                            $sql->addField('sql_command',$sqlCommand,"String");
                            $sql->addField('usua_id', getSession("sis_userid"), "Number");
                            $conn->execute($sql->getSQL());
                            $error=$conn->error();
                            if($error){alert($error);}/* Muestro el error y detengo la ejecución */
                        }
			redirect($destinoDelete,"content");		
		}
	}
	
	function getSQL(){
		$this->sql = "SELECT  a.*,
							x.usua_login
				FROM $this->setTable a 
				LEFT JOIN usuario x  ON a.usua_id=x.usua_id
				WHERE a.$this->setKey=".$this->getValue($this->id, $this->typeKey);

		return($this->sql);
	}

	function jsDevolver($nomeCampoForm){
			if($nomeCampoForm)
			//PARA EJECUTAR UNA FUNCION DEPENDNIENTE DEL VALOR ELEGIDO EN LA VENTANA DE BUSQUEDA
			//SE REQUIERE  DE self.parent.NOMBRE_DE_FUNCION_JAVASCRIPT(paramenteres) ejem:self.parent.xajax_cargaGrupo(valor,1)
			return ("function update(valor, descricao, numForm) {
					parent.parent.content.document.forms[numForm]._Dummy$nomeCampoForm.value = descricao;
					parent.parent.content.document.forms[numForm].$nomeCampoForm.value = valor;
					parent.parent.content.document.forms[numForm].__Change_$nomeCampoForm.value = 1;
					self.parent.tb_remove();
					}");
	}

	function jsSorter($nomeCampoForm=''){
		// Funci�n Javascript que activa la librer�a Sorter en la tabla mostrada
		if($nomeCampoForm){
			return (" function activaSorter(){
							$(function() {		
								$(\".tablesorter\").tablesorter({});
							});	
					}
	
					activaSorter()");
		}else{
			return (" function activaSorter(){
							$(function() {
            					$(\".tablesorter\").tablesorter({ 
            				        headers: { 
            				            // asignamos a la columna cero (Iniciamos contando desde cero) 
            				            0: { 
            				                // Para que la columna no sea ordenable 
            				                sorter: false 
            				            } 
            				        } 
            				    }); 				
							});	
					}
	
					activaSorter()");
		}

	}

	function getValue($value, $type) {
		if (!strlen($value)) {
			return "NULL";
		} else {
			if ($type == "Number") {
				//return str_replace (",", ".", doubleval($value));
				return str_replace (",", "", doubleval($value));
			} else {
//				if (get_magic_quotes_gpc() == 0) {
//					$value = str_replace("'","''",$value);
//					$value = str_replace("\\","\\\\",$value);
//				} else {
					$value = str_replace("\\'","''",$value);
					$value = str_replace("\\\"","\"",$value);
				//}
				return "'" . $value . "'";
			}
		}
	}

	function getTitle(){
		return $this->title;
	}

	/* Nombre del archivo php de la clase */
	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	

	function getNamePage($accion){
	}	

	function getPageEdicion(){
		return $this->pagEdicion;
	}

	function getPageBuscar(){
		return $this->pagBuscar;
	}

	function getArrayNameVar(){
		return $this->arrayNameVar;
	}
	
	function getArrayNameVarID($i){
		return $this->arrayNameVar[$i];
	
	}
}