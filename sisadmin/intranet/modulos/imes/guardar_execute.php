<?php
if (!$erro->hasErro()) { // verifico si pasa la validacion
			// objeto para instanciar la clase sql
			$sql = new UpdateSQL();
			
			$sql->setTable($setTable);
			$sql->setKey($setKey, $valueKey, $typeKey);
			include("../guardar_tipoDato.php");
			// permite ampliar el contenido de la cadena de datos a grabar
			guardar_extend($op,$sql);

			if ($valueKey) { // modificación
				$sql->setAction("UPDATE");
                                //ECHO $sql->getSQL();
                                //exit(0);
				$conn->execute($sql->getSQL());
				$error=$conn->error();
                                

				if($error) 
					alert($error);				
				else {
				
					// muestra mensaje noticia del la base de datos, pero no detiene la ejecucion						
//					$notice=$conn->notice();
//					if($notice){ 
//						alert($notice,0);				
//                                        }
					//GUARDA LA SECUENCIA
					if(strlen($secuencia)>0 && $correla>0){
					    $conn->setval($secuencia,$correla);		
						$error=$conn->error();
						if($error) {
							//si hay error se asume que no esta creada la secuencia, entonces se procede a crearla
							$conn->nextid($secuencia);			
							$error=$conn->error();
							if($error) {
								alert($error);
							}
							else{
								//la siguiente linea es recomendable para evitar incosistencias en la instruccion $conn->curval
								$conn->setval($secuencia,$correla);
						
								$error=$conn->error();
								if($error) {
									alert($error);				
								}
							}
						}
					}//FIN GUARDAR SECUENCIA
							
					// si es una ventana emergente desde donde se llama al guardar				
					if(strcmp("close",$destinoInsert)==0){
   						echo "<"."script".">\n";
					    if ($ventana == "thickbox") {
                                                echo "self.parent.document.location.reload()"; 
					    } else {
    						echo "javascript:top.close()\n";
					    }
    					echo "</"."script".">\n";
					}
						else {
							$destino = $destinoUpdate;
							
							// redirecciona segun la variable $destino
							redirect($destino,"content");
							if($linkAux) redirect($linkAux,$destAux);											
							}
					}
			} else { // inserción
				//adiciono el id de usuario responsable de la creacion del registro
				if($saveUser) $sql->addField('usua_id', getSession("sis_userid"), "Number");							
				
				$sql->setAction("INSERT");

				$conn->execute($sql->getSQL());
                                //alert($sql->getSQL());    
                                //echo $sql->getSQL();
                                //exit(0);

				$error=$conn->error();
				// muestra error y detiene la ejecucion
				if($error) 
                                        if(strpos($error,"llave duplicada viola")>0 && strpos($error,"unicidad")>0){
                                                $pos_ini=strpos($error,'unicidad')+10;
                                                $pos_fin=strpos($error,'DETAIL')-$pos_ini-2;
                                                alert(substr($error,$pos_ini,$pos_fin));
                                        }
                                        elseif(strpos($error,"duplicate key violate")>0 && strpos($error,"unique")>0){  
                                                $pos_ini=strpos($error,'unique')+6;
                                                $pos_fin=strpos($error,'DETAIL')-$pos_ini-2;
                                                alert(substr($error,$pos_ini,$pos_fin));
                                        }   
                                        else        
                                            alert($error);	
				else {
					// muestra mensaje noticia del la base de datos, pero no detiene la ejecucion						
					$notice=$conn->notice();
					if($notice) 
						alert($notice,0);				

					$last_id = $last_id?$last_id:$conn->lastid();					
					// si es una ventana emergente desde donde se llama al guardar
					if(strcmp("close",$destinoInsert)==0){
   						echo "<"."script".">\n";
					    if ($ventana == "thickbox") {
                                                echo "self.parent.document.location.reload()"; 
					    } else {
    						echo "javascript:top.close()\n";
					    }
    					echo "</"."script".">\n";
					}
					else 
						if(strcmp("return",$destinoInsert)==0){
                                                        
                                                        $return_valx=$return_val;
                                                        
                                                        if($return_val=='hipe_nhistoria'){
                                                            $sql="SELECT clie_id,
                                                                         clie_razsocial,
                                                                         clie_dni
                                                                    FROM catalogos.cliente
                                                                    WHERE hipe_id=$last_id";
                                                            
                                                            $cliente = new query($conn, $sql);
                                                            $cliente->getrow();
                                                            
                                                            $clie_id=$cliente->field('clie_id');                                                                    
                                                            $clie_dni=$cliente->field('clie_dni');
                                                            $clie_razsocial=$cliente->field('clie_razsocial');
                                                                    
                                                            $return_val=getDbValue("SELECT $return_val FROM $setTable WHERE $setKey=$last_id");
                                                        }
                                                        
							eval("\$return_val = $return_val;");
							eval("\$return_txt = $return_txt;");
							
							echo "<script language=\"javascript\" type=\"text/javascript\">\n";
							echo "parent.opener.parent.parent.content.document.forms[$numForm]._Dummy".getParam("nomeCampoForm").".value = '$return_txt'\n";
							echo "parent.opener.parent.parent.content.document.forms[$numForm].".getParam("nomeCampoForm").".value = '$return_val'\n";

							//solo si existe campo adicional o extra
							if(getParam("fieldExtra")){
								eval("\$return_extra = $return_extra;");
								echo "parent.opener.parent.parent.content.document.forms[$numForm].".getParam("fieldExtra").".value = '$return_extra'\n";
							}
		
							echo "parent.opener.parent.parent.content.document.forms[$numForm].__Change_".getParam("nomeCampoForm").".value = 1\n"; 

                                                        if($return_valx=='hipe_nhistoria'){
                                                            echo "parent.opener.parent.parent.content.document.forms[$numForm].tx_clie_id.value = $clie_id\n";
                                                            echo "parent.opener.parent.parent.content.document.forms[$numForm].Sx_cliente.value = '$clie_dni'\n";
                                                            echo "parent.opener.parent.parent.content.document.forms[$numForm]._DummySx_cliente.value = '$clie_razsocial'\n";
                                                        }
                                                        
							echo "parent.parent.close()\n";
							echo "</script>\n";
							} 
						else {
							if(strpos($destinoInsert, "?")>0)
								$destino = $destinoInsert."&id=$last_id";
							else
								$destino = $destinoInsert."?id=$last_id";

							//GUARDA LA SECUENCIA
							if(strlen($secuencia)>0 && $correla>0){
							    $conn->setval($secuencia,$correla);		
								$error=$conn->error();
								if($error) {
									//si hay error se asume que no esta creada la secuencia, entonces se procede a crearla
									$conn->nextid($secuencia);			
									$error=$conn->error();
									if($error) {
										alert($error);	
									}			
									else{
										//la siguiente linea es recomendable para evitar incosistencias en la instruccion $conn->curval
										$conn->setval($secuencia,$correla);
								
										$error=$conn->error();
										if($error) {
											alert($error);				
										}
									}
								}
							}//FIN GUARDAR SECUENCIA
							
							// redirecciona segun la variable $destino
							redirect($destino,"content");					
							if($linkAux) redirect($linkAux,$destAux);
							
							}
					}					
			}		

} else { // si no pasa la validacion
	alert('Mensajes de errores!\n\n'.$erro->toString());
//		redirect($destinoUpdate,"content");
}