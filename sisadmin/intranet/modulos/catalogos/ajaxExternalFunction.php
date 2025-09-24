<?php
//BUSQUEDA EN TABLAS
function busqueda_Parametro($op,$cadena='',$colSearch='',$colOrden=1,$busEmpty=0,$numForm=0,$Nameobj)
{
	global $conn,$dbrev,$setCodigo;

	$objResponse = new xajaxResponse();

	$cadena=trim(strtoupper($cadena));

	if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias

		$otable = new  Table("","100%",6);
		$sql = "SELECT a.tabl_id,a.tabl_codigo,a.tabl_descripcion,a.tabl_descripaux "
			 . "FROM  tabla a WHERE a.tabl_tipo='".getSession("table")."' " ;


		//se analiza la columna de busqueda
		switch($colSearch){
			case 'tabl_id': // si se recibe el campo id
				$sql .= "AND a.tabl_id=$cadena ";
				break;

			default:// si se no se recibe ningun campo de busqueda
				if(ctype_digit($cadena)) //si la cadena recibida son todos digitos
					$sql .= "AND a.tabl_id=$cadena ";
				else
					$sql .= "AND a.tabl_descripcion LIKE '%$cadena%' ";

				break;
			}
		$sql .= "ORDER BY a.tabl_codigo,$colOrden ";


		//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
		if ($op==1 && getParam("clear")!=2)
			setSession("cadSearch",$cadena);

			$rs = new query($conn, $sql);
			if ($rs->numrows()>0) {
					if (getParam("clear")==2){}
					else
						$otable->addColumnHeader("<input type=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">"); // Coluna com checkbox

					if($setCodigo) //si muestra el código
						$otable->addColumnHeader("C&oacute;d",true,"1%", "C","xajax_busqueda_Parametro(1,'$cadena','$colSearch','2',$busEmpty,$numForm,'DivResultado')"); // T�tulo, Ordenar?, ancho, alineaci�n
                                                
					$otable->addColumnHeader("Descripci&oacute;n",true,iif($dbrev,'==',1,'80%','100%'), "L","xajax_busqueda_Parametro(1,'$cadena','$colSearch','3',$busEmpty,$numForm,'DivResultado')"); // T�tulo, Ordenar?, ancho, alineaci�n


					if($dbrev) //si muestra descripcion breve
						$otable->addColumnHeader("Breve",true,"20%", "L","xajax_busqueda_Parametro(1,'$cadena','$colSearch','4',$busEmpty,$numForm,'DivResultado')"); // T�tulo, Ordenar?, ancho, alineaci�n

					$otable->addRow(); // adiciona la linea (TR)
					while ($rs->getrow()) {
						$id = $rs->field("tabl_id"); // captura la clave primaria del recordsource

						$campoTexto_de_Retorno = $rs->field("tabl_descripcion");

						//si la llamada no es desde llamada desde la busqueda avanzada (AvanzLookup)
						if (getParam("clear")==2){
							$otable->addData(addLink($campoTexto_de_Retorno,"javascript:update('$id','$campoTexto_de_Retorno',$numForm)","Click aqu&iacute; para seleccionar el registro"));
							}
						else{
							$otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");
                                                        
                                                        if($setCodigo) //si muestra el código                                                        
                                                            $otable->addData($rs->field("tabl_codigo"),"C");
                                                        
                                                        $otable->addData(addLink($campoTexto_de_Retorno,"catalogosTablas_edicion.php?id=$id&clear=1&busEmpty=$busEmpty&colOrden=$colOrden&dbrev=$dbrev&setCodigo=$setCodigo","Click aqu&iacute; para consultar o editar el registro"));
						}
                                                        
						if($dbrev)
							$otable->addData($rs->field("tabl_descripaux"));
                                                
						$otable->addRow();
					}

				$contenido_respuesta=$otable->writeHTML();
				$contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";


			} else {
				$otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
				$otable->addRow();
				$contenido_respuesta=$otable->writeHTML();
			}
		}
	else
		$contenido_respuesta="";


//	  $objResponse->addAlert($Nameobj);
	//se analiza el tipo de funcionamiento
	if($op==1){//si es llamado para su funcionamiento en ajax con retornoa a un div
		$objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
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
