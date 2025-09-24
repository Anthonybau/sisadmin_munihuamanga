<?php
		//recorrido de campos			
		foreach($_POST as $variable=>$valor){ 
			
			$char_orig=substr($variable,0,1);
			$char_ini=strtoupper(substr($variable,0,1));
			$char_uno=strtoupper(substr($variable,1,1));
			$char_dos=strtoupper(substr($variable,2,1));
			//esta linea se utiliza  en las paginas de la libereria AnvazLokup
			if(substr($variable,3)==$campoExibe) $DatoReturn=getParam($variable);
			if ($char_dos=='_')
				switch($char_ini){
						case 'N'://DATO TIPO NUMERICO
							$sql->addField(substr($variable,3), str_replace(",","",getParam($variable)), "Number");				
                                                        break;	
                                                    
						case 'R'://DATO TIPO REAL CON TODOS LOS DECIMALES: ejm 1.000 SE RECOMIENDA TIPO DE DATO STRING POR EL LADO DE LA BD
							$sql->addField(substr($variable,3), str_replace(",","",getParam($variable)), "String");
							break;					
                                                    
						case 'D'://DATO TIPO FECHA
							$sql->addField(substr($variable,3), dtos(getParam($variable)), "Date");				
							break;
                                                    
						case 'H'://SE RECOMIENDA UTILIZAR LA FUNCION guardar_extend
						    break;
							
						case 'P'://DATO TIPO PASSWORD
							if (strcmp(getParam($variable),"******")){								
								$sql->addField(substr($variable,3), md5(getParam($variable)), "String");
								}					
							break;
						case 'F'://DATO TIPO FILE
						    
							//Subimos los Archivos al Server
							//PUBLICUPLOAD -> se define en config.inc.php
							$name_upload=$_FILES[substr($variable,3)]['tmp_name'];
							$name_file=$_FILES[substr($variable,3)]['name'];			
							
							//si se requiere grabar el archivco en una carpeta especifica, deber� existir una variable oculta 
							//en el formulario de nombre 'postPath'
                                                        if(getParam('postPath')){
								$nvoPath_file= PUBLICUPLOAD.getParam('postPath').'/';
                                                                //echo $nvoPath_file;
                                                                //exit(0);
                                                                if (!is_dir($nvoPath_file) && !mkdir(PUBLICUPLOAD.getParam('postPath'), 0755, true) && !chmod(PUBLICUPLOAD.getParam('postPath'), 0755)) {
                                                                    alert("Imposible crear la carpeta $nvoPath_file",1);
                                                                }                                                                
                                                        }else{
								$nvoPath_file=PUBLICUPLOAD;
                                                        }                                                        
							
							//si se requiere renombrar el archivo, debera existir una variable oculta 
							//en el formulario de nombre 'prefFile'
							if(getParam('prefFile')){
                                                            //refrescamos fecha+hora
//                                                          $nvoName_file=date("dmY").date("His").'_'.$name_file;
                                                            $nvoName_file=date("dmY").date("His").mt_rand().substr($name_file, -4); 
							}
							else{
                                                            $nvoName_file=$name_file;
                                                        }
							//si se ha dado click en REMOVER imagen				
							if(getParam(substr($variable,3)."_excluir")==1)
								if(file($nvoPath_file.getParam($variable)))
									unlink ($nvoPath_file.getParam($variable));

							//si ha elejido una nueva imagen, esta se sube
							if($name_file){
							    //alert($name_upload.' - '.$nvoPath_file.$nvoName_file);
                                                               if (!move_uploaded_file($name_upload,$nvoPath_file.$nvoName_file)) {
                                //                                   alert('aaa');
                                                                   print "ERROR: ocurrio un error al hacer upload al archivo";
                                                                   print_r($_FILES);
                                                                   exit;
                               } 			
                                                                if(getParam('postPath')){
                                                                    $nvoName_file= substr($nvoPath_file.$nvoName_file,strrpos($nvoPath_file.$nvoName_file,"/")+1);
                                                                }
								$sql->addField(substr($variable,3), $nvoName_file, "String");
								}
							else{
								//si se ha dado click en REMOVER imagen, se limpia tambien el registro				
								if(getParam(substr($variable,3)."_excluir")==1)
										$sql->addField(substr($variable,3), "", "String");							
								}
							break;

						case 'S'://DATO TIPO STRING
						case 'E'://DATO TIPO AREA						
							if ($char_orig=='s' or $char_orig=='e')
								$sql->addField(substr($variable,3), getParam($variable), "String");
							else
								$sql->addField(substr($variable,3), strtoupper(getParam($variable)), "String");
							break;

						case 'C'://DATO TIPO MAIL (EL TEXTO ES NORMAL, SI MAYUSCULAS)
							$sql->addField(substr($variable,3), getParam($variable), "String");
							break;

                                                case 'A'://DATO TIPO ARRAY (CHOSEN)
                                                        $limpiar=0;
                                                        
                                                        if(is_array(getParam($variable))){
                                                            $array=getParam($variable);
                                                            foreach ($array AS $valor){
                                                                if ($valor==9999){
                                                                    $limpiar=1;
                                                                }
                                                            }
                                                        }

                                                        if($limpiar==1){
                                                            $sql->addField(substr($variable,3), '', "String");                                                    
                                                        }else{
                                                            $sql->addField(substr($variable,3), implode(",", getParam($variable)), "String");                                                    
                                                        }
							break;                                                    
						case 'K': // FckEditor

							if ( get_magic_quotes_gpc() )
								$sql->addField(substr($variable,3), getParam($variable), "String");
							else
								$sql->addField(substr($variable,3), getParam($variable), "String");

							break;

						default:
							$sql->addField(substr($variable,3), strtoupper(getParam($variable)), "String");									
							break;				
				}
		}