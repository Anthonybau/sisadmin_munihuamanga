<?php
/*	archivo comun/modificaci�n de registros */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/*	verificaci�n a nivel de usuario */
verificaUsuario(1);

/* variable que se recibe de la opcion para datos de edicion */
$op = getParam("_op");
$relacionamento_id = getParam("relacionamento_id");
$erro = new Erro();

if(!$op) $erro->addErro('No envio parámetro de proceso.');

//	conexi�n a la BD 
$conn = new db();
$conn->open();

$content="content";

if (!$erro->hasErro()) { // verifico si pasa la validaci�n

    switch($op) {
         case 'NotiEstado': // Activa/Desactiva Items en Portal Web
			$pg = getParam("pagina");
			$arLista_elimina = getParam("sel");
			$estado =getParam("estado")=='Act'?'1':'2';
			if (!is_array($arLista_elimina)==0) { // se no existen registros seleccionados
					$error="Ning\u00fan registro selecionado!";
				}
			else { // si existe registro seleccionados
				// Armo strng a ejecutar
				$sSql="update portal_noticia set pono_estado='$estado' where pono_id=".$arLista_elimina[0];

				// Ejecuto el string
				$conn->execute($sSql);
				$error=$conn->error();
				}

			$destino = "../portal/sisadportContenidosNoticia_lista.php?pagina=$pg";

	   		break;

	   case 'BannEstado': // Activa/Desactiva Items en Portal Web
			$pg = getParam("pagina");
			$arLista_elimina = getParam("sel");
			$estado =getParam("estado")=='Act'?'1':'2';
			if (!is_array($arLista_elimina)==0) { // se no existen registros seleccionados
					$error="Ning\u00fan registro selecionado!";
				}
			else { // si existe registro seleccionados
				// Armo strng a ejecutar
				$sSql="update portal_banner set poba_estado='$estado' where poba_id=".$arLista_elimina[0];

				// Ejecuto el string
				$conn->execute($sSql);
				$error=$conn->error();
				}

			$destino = "../portal/sisadportEstructurasBanner_lista.php?pagina=$pg";

	   		break;

	   case 'EmergEstado': // Activa/Desactiva Items en Portal Web
			$pg = getParam("pagina");
			$arLista_elimina = getParam("sel");
			$estado =getParam("estado")=='Act'?'1':'2';
			if (!is_array($arLista_elimina)==0) { // se no existen registros seleccionados
					$error="Ning\u00fan registro selecionado!";
				}
			else { // si existe registro seleccionados
				// Armo strng a ejecutar
				$sSql="update portal_emergentes set poem_estado='$estado' where poem_id=".$arLista_elimina[0];

				// Ejecuto el string
				$conn->execute($sSql);
				$error=$conn->error();
				}

			$destino = "../portal/sisadportEstructurasEmergente_lista.php?pagina=$pg";

	   		break;

	   case 'EncEstado': // Activa/Desactiva Items en Portal Web
			$pg = getParam("pagina");
			$arLista_elimina = getParam("sel");
			$estado =getParam("estado")=='Act'?'1':'2';
			if (!is_array($arLista_elimina)==0) { // se no existen registros seleccionados
					$error="Ning\u00fan registro selecionado!";
				}
			else { // si existe registro seleccionados
				// Armo strng a ejecutar
				$sSql="update portal_encuesta set poen_estado='$estado' where poen_id=".$arLista_elimina[0];

				// Ejecuto el string
				$conn->execute($sSql);
				$error=$conn->error();
				}

			$destino = "../portal/sisadportContenidoEncuesta_lista.php?pagina=$pg";

	   		break;

	   case 'TemaEstado': // Activa/Desactiva Items en Portal Web
			$pg = getParam("pagina");
			$arLista_elimina = getParam("sel");
			$estado =getParam("estado")=='Act'?'1':'2';
			if (!is_array($arLista_elimina)==0) { // se no existen registros seleccionados
					$error="Ning\u00fan registro selecionado!";
				}
			else { // si existe registro seleccionados
				// Armo strng a ejecutar
				$sSql="update portal_contenido set poco_estado='$estado' where poco_id=".$arLista_elimina[0];

				// Ejecuto el string
				$conn->execute($sSql);
				$error=$conn->error();
				}

			$destino = "../portal/sisadportContenidosTema_lista.php?pagina=$pg";

	   		break;

	   case 'solDonacion': // Activa/Desactiva Items de Solicitudes de Donación
			$pg = getParam("pagina");
			$arLista_elimina = getParam("sel");
                        
			$estado =getParam("estado")=='Act'?'1':'2';
			if (!is_array($arLista_elimina)) { // se no existen registros seleccionados
					$error="Ning\u00fan registro selecionado!";
			}
			else { // si existe registro seleccionados
				// Armo strng a ejecutar
				$sSql="update portal_donacion set podo_estado='$estado' where podo_id=".$arLista_elimina[0];

				// Ejecuto el string
				$conn->execute($sSql);
				$error=$conn->error();
				}

			$destino = "../portal/sisadportEstructurasSolicitaDonaciones_lista.php?pagina=$pg";

	   		break;


            case 'camsena': // Cambio de Contraseña
            // addslashes permite colocar caracteres especiales como contrase�a
                $_senha = md5(addslashes(getParam("sr_senha")));
                $_senhanueva = md5(addslashes(getParam("sr_senhanueva")));
                $_senharepite = md5(addslashes(getParam("sr_senharepite")));

                // Armo strng a ejecutar
                $sSql="select admin.func_cambiacontrasena(".getSession("sis_userid").",'$_senha','$_senhanueva','$_senharepite')";

                // Ejecuto el string
                $conn->execute($sSql);
                $error=$conn->error();

                $destino = "close";

                break;

            case 'Especificas': //habilitar específicas en clasificador presupuestal
	 	
                $pg    = getParam("pagina");
		$sSql="SELECT siscopp.func_set_esppresupuestal()";

		// Ejecuto el string
		$conn->execute($sSql);
		$error=$conn->error();

		$notice=$conn->notice();

	   	break;	   
            
	   case 'ProCodCaj': // Cerrar/Abrir Codigo de caja
			$coca_id = getParam("nr_coca_id");
                        $caco_tipo= getParam("caco_tipo");
			$tip_mov =getParam("tipo")=='Cer'?'1':'2';
			$sSql="select gestmed.func_cerrar_codcaja ('$caco_tipo',$coca_id,$tip_mov)";
                        //alert($sSql);
			// Ejecuto el string
			$conn->execute($sSql);
			$error=$conn->error();
			$notice=$conn->notice();

			$destino = "../gestmed/sisRecaudacionProcesosCierreCodCaja.php?caco_tipo=$caco_tipo";
	   		break;

	   case 'EstDocCajIng': // Anula documentos de Ingresos, siempre que el documento no se encuentre procesado y pertenezca al usuario que realiza la accion
			$pg    = getParam("pagina");
                        $idHora = getParam("idHora");
			$arLista_procesa = getParam("sel");
			$estado = getParam("estado");
                        $codCaja= getParam("CodCajaActivo");
			if (!is_array($arLista_procesa)) { // se no existen registros selecionados
					$error="Ning\u00fan  registro seleccionado!";
				}
			else { // se existe registro selecionados
				if (is_array($arLista_procesa))
					for ($i=0; $i<sizeof($arLista_procesa); $i++) {
						$id=substr($arLista_procesa[$i],strpos($arLista_procesa[$i],'_')+1);
                                                $error='';
						/*el supervisor puede anular cualquier documento*/
						//if(getSession("sis_level")>=4)
						//	$sSql="UPDATE caja_ingreso SET cain_estado='$estado' WHERE cain_id=$id ";
						//else{
                                                        /*CONTROLA LAS ANULACIONES*/
                                                        if($estado==9){
                                                            $fecha=date('d/m/Y');
                                                            $acuAnula=getDbValue("SELECT cian_max_anula
                                                                        FROM caja_ingreso_anulaciones 
                                                                        WHERE cian_fecha='$fecha' 
                                                                              AND hora_id=$idHora 
                                                                              AND usua_id=".getSession("sis_userid"));
                                                            if($acuAnula)
                                                                $sSql="UPDATE caja_ingreso_anulaciones  
                                                                                    SET cian_acu_anula=cian_acu_anula+1
                                                                              WHERE cian_fecha='$fecha' 
                                                                                AND hora_id=$idHora 
                                                                                AND usua_id=".getSession("sis_userid");                                                                
                                                            else
                                                                $sSql="INSERT INTO  caja_ingreso_anulaciones  
                                                                                    (cian_fecha,
                                                                                    cian_acu_anula,
                                                                                    cian_max_anula,
                                                                                    hora_id,
                                                                                    usua_id)
                                                                               VALUES('$fecha',
                                                                                       1,".
                                                                                       SIS_ANULA_XTUR.",
                                                                                       $idHora,".
                                                                                        getSession("sis_userid").
                                                                                    ")";
                                                            // Ejecuto el string
                                                            $conn->execute($sSql);
                                                            $error=$conn->error();

                                                        //}
							$sSql="UPDATE caja_ingreso SET cain_estado='$estado' WHERE cain_id=$id and usua_id=".getSession("sis_userid")." and cain_proceso='0' AND caco_id=$codCaja";
                                                }
                                                if(!$error){//hay error cuando el contador de anulacines intenta superar el maximo de anulacines permitidos (se activa el check de la BD)
                                                    // Ejecuto el string
                                                    $conn->execute($sSql);
                                                    $error=$conn->error();
                                                }else{
                                                    $error="Proceso Cancelado, Usted ha superado el maximo de Anulaciones Permitidas ($acuAnula) ";
                                                }
					}
				// Armo strng a ejecutar
				}

			$destino = "../gestmed/sisRecaudacionCajaIngresos_lista.php?pagina=$pg";
	   		break;           
                        
	   case 'DevDocCajIng': // Genera Devoluci�n
			$pg    = getParam("pagina");
                        $idHora = getParam("idHora");
			$arLista_procesa = getParam("sel");
			$vent_id =getParam("IDVent");
                        $CodCajaActivo=getParam("CodCajaActivo");
			if (!is_array($arLista_procesa)) { // se no existen registros selecionados
					$error="Ning\u00fan  registro seleccionado!";
				}
			else { // se existe registro selecionados
                            if($CodCajaActivo && $vent_id){
					$id=substr($arLista_procesa[0],strpos($arLista_procesa[0],'_')+1);
                                        if($id){//inicio la grabación de la DEVOLUCION
                                                /*VERIFICO SI TIENE OPCION A GENERAR LA DEVOLUCION*/
                                                /*CONTROLA DE DEVOLUCIONES*/
                                                $fecha=date('d/m/Y');
                                                $acuDevuelve=getDbValue("SELECT cide_max_devuelve 
                                                            FROM caja_ingreso_devoluciones 
                                                            WHERE cide_fecha='$fecha' 
                                                                  AND hora_id=$idHora 
                                                                  AND usua_id=".getSession("sis_userid"));
                                                if($acuDevuelve)
                                                    $sSql="UPDATE caja_ingreso_devoluciones
                                                                        SET cide_acu_devuelve=cide_acu_devuelve+1
                                                                  WHERE cide_fecha='$fecha' 
                                                                    AND hora_id=$idHora 
                                                                    AND usua_id=".getSession("sis_userid");                                                                
                                                else
                                                    $sSql="INSERT INTO caja_ingreso_devoluciones  
                                                                        (cide_fecha,
                                                                        cide_acu_devuelve,
                                                                        cide_max_devuelve,
                                                                        hora_id,
                                                                        usua_id)
                                                                   VALUES('$fecha',
                                                                           1,".
                                                                           SIS_ANULA_XTUR.",
                                                                           $idHora,".
                                                                            getSession("sis_userid").
                                                                        ")";
                                                // Ejecuto el string
                                                $conn->execute($sSql);
                                                $error=$conn->error();
                                                if($error){//hay error cuando el contador de devoluciones intenta superar el maximo de devoluciones permitidas (se activa el check de la BD)
                                                    $error="Proceso Cancelado, Usted ha superado el maximo de Devoluciones Permitidas ($acuDevuelve) ";
                                                }
                                                else{
                                                    // Ejecuto el proceso de anulación
                                                    $serie=getDbValue("SELECT vent_serie FROM ventanilla WHERE vent_id=$vent_id");
                                                    $TipDoc=171; //DEVOLUCIONES
                                                    $secuencia=trim('gestmed.corr_td_'.str_pad($TipDoc,3,'0',STR_PAD_LEFT).'_'.str_pad($serie,3,'0',STR_PAD_LEFT));
                                                    $correla=$conn->currval($secuencia);        
                                                    /*CREA LA SECUENCIA*/
                                                    if($correla==0){
                                                        $correla=$conn->nextid($secuencia);	
                                                    }

                                                    $sSql="INSERT INTO caja_ingreso (hipe_id,
                                                                  cain_fecha,
                                                                  cain_razsoc,
                                                                  tabl_tipdoc,
                                                                  cain_serie,
                                                                  cain_numero,
                                                                  cain_total,
                                                                  cain_igv,
                                                                  cain_neto,
                                                                  caco_id,
                                                                  cain_modpago,
                                                                  cain_fecha_origen,
                                                                  tabl_tipdoc_origen,
                                                                  cain_serie_origen,
                                                                  cain_numero_origen,
                                                                  cain_id_origen,
                                                                  cain_estado,
                                                                  usua_id)
                                                    (SELECT hipe_id,
                                                                  '".date('d/m/Y')."',
                                                                  cain_razsoc,
                                                                  $TipDoc,
                                                                  '$serie',
                                                                  '$correla',
                                                                  cain_total*-1,
                                                                  cain_igv*-1,
                                                                  cain_neto*-1,
                                                                  $CodCajaActivo,
                                                                  cain_modpago,
                                                                  cain_fecha,
                                                                  tabl_tipdoc,
                                                                  cain_serie,
                                                                  cain_numero,
                                                                  cain_id,
                                                                  0,".
                                                                  getSession("sis_userid")."
                                                    FROM caja_ingreso WHERE cain_id=$id) RETURNING cain_id";

                                                    // Ejecuto el string
                                                    $nvoIng=$conn->execute($sSql);
                                                    $error=$conn->error();
                                                    if(!$error){
                                                        $conn->setval($secuencia,intval($correla)+1);

                                                        $sSql="INSERT INTO caja_ingreso_detalle (cain_id,
                                                                                      serv_codigo,
                                                                                      cide_cantidad,
                                                                                      cide_impunit,
                                                                                      cide_exonera,
                                                                                      cide_subtotal,
                                                                                      cide_porcen1,
                                                                                      cide_impconv1,
                                                                                      cide_porcen2,
                                                                                      cide_impconv2,
                                                                                      cide_id_origen,
                                                                                      usua_id)
                                                                         (SELECT $nvoIng,
                                                                                      serv_codigo,
                                                                                      cide_cantidad*-1,
                                                                                      cide_impunit*-1,
                                                                                      cide_exonera*-1,
                                                                                      cide_subtotal*-1,
                                                                                      cide_porcen1,
                                                                                      cide_impconv1*-1,
                                                                                      cide_porcen2,
                                                                                      cide_impconv2*-1,
                                                                                      cide_id,".
                                                                                      getSession("sis_userid")." 
                                                                            FROM caja_ingreso_detalle
                                                                            WHERE cain_id=$id)";

                                                        // Ejecuto el string
                                                        $conn->execute($sSql);
                                                        $error=$conn->error();
                                                        //ACTUALIZO EL ESTADO DEL DOCUMENTO PARA QUE SE ACTIVE EL TRIGGER Y ANULE LA CITA
                                                        $conn->execute("UPDATE caja_ingreso SET cain_estado=1 WHERE cain_id=$nvoIng");
                                                        $error=$conn->error();
                                                        setSession("where",'');
                                                    }else{
                                                            /*SI HAY ERROR NO SE REALIZA LA DEVOLUCION 
                                                             ANTES SE HABIA INCREMENTADO, POR TANTO SE TIENE QUE RESTAR 1*/
                                                            $error_aux=$error; //guardo el error
                                                            $sSql="UPDATE caja_ingreso_devoluciones
                                                                        SET cide_acu_devuelve=cide_acu_devuelve-1
                                                                    WHERE cide_fecha='$fecha' 
                                                                        AND hora_id=$idHora 
                                                                        AND usua_id=".getSession("sis_userid");                                                         
                                                            $conn->execute($sSql);
                                                            $error=$error_aux; //devuelvo el error
                                                    }
                                                }
                                                /*FIN VERIFICO*/
                                                
                                      /*fin de la grabación de la anulación*/  
                                        }else{
                                            $error="No se encontro Numero de Registro";
                                        }
                            }else{
                                $error="La Caja no se encuentra Abierta";
                            }
                                        
                        }
			$destino = "../gestmed/sisRecaudacionCajaIngresos_lista.php?pagina=$pg";
	   		break;                    
                        
	   case 'impDocCajIng': // Imprime documento de caja de ingresos
			$pg    = getParam("pagina");
			$arLista_procesa = getParam("sel");
			$idDisposit=getParam("idDisposit");

			if (!is_array($arLista_procesa)) { // se no existen registros selecionados
					$error="Ning\u00fan registro seleccionado!";
				}
			else { // se existe registro selecionados
				$nameTD=substr($arLista_procesa[0],0,strpos($arLista_procesa[0],'_'));

				$id=substr($arLista_procesa[0],strpos($arLista_procesa[0],'_')+1);
				
				if(stristr($nameTD,'FACT')){
                                    $destino="../reportes/rptsisRecaudacion_facturatxt.php?id=$id&idDisposit=$idDisposit";
				}
				else
                                    if($nameTD=='B/V'){
                                        $destino="../reportes/rptsisRecaudacion_boletatxt.php?id=$id&idDisposit=$idDisposit";
                                    }else{
                                        $destino="../gestmed/rptsisRecaudacion_recibotxt.php?id=$id";
                                    }

				$content = "controle";
			}
	   		break;

	   case 'EstDocFarVta': // Anula ventas de Farmacia
			$pg    = getParam("pagina");
			$arLista_procesa = getParam("sel");
			$estado=getParam("estado");
			if (!is_array($arLista_procesa)) { // se no existen registros selecionados
                            $error="Ning\u00fan registro seleccionado!";
			}
			else { // se existe registro selecionados
				if (is_array($arLista_procesa))
					for ($i=0; $i<sizeof($arLista_procesa); $i++) {
                                                $id=substr($arLista_procesa[$i],strpos($arLista_procesa[$i],'_')+1);
						/* Para asegurarme si alguien vuelve a anular un registro ya nulado no se ejecute la sentencia UPDATE */
						if($estado==9) // Si estoy anulando un registro 
							$MyWhere="fave_estado=1 AND ";
						else /* estoy Deshaciendo una anulación */
							$MyWhere="fave_estado=9 AND ";
						
						/*el supervisor puede anular cualquier documento*/
						if(getSession("sis_level")>=3)
							$sSql="UPDATE farmacia_venta SET fave_estado=$estado WHERE $MyWhere fave_id=$id ";
						else
							$sSql="UPDATE farmacia_venta SET fave_estado=$estado WHERE $MyWhere fave_id=$id AND usua_id=".getSession("sis_userid");
                                                //echo         $sSql;
                                                //exit(0);
						// Ejecuto el string
						$conn->execute($sSql);
						$error=$conn->error();
							}
				// Armo strng a ejecutar
				}

			$destino = "../farmacia/sisFarmaciaMovimientosVentas_lista.php?pagina=$pg";
	   		break;
                        
	   case 'impDocFarmacia': // Imprime documento de caja de ingresos
			$pg    = getParam("pagina");
			$arLista_procesa = getParam("sel");
			$idDisposit=getParam("idDisposit");

			if (!is_array($arLista_procesa)) { // se no existen registros selecionados
					$error="Ning\u00fan registro seleccionado!";
				}
			else { // se existe registro selecionados
				$nameTD=substr($arLista_procesa[0],0,strpos($arLista_procesa[0],'_'));

				$id=substr($arLista_procesa[0],strpos($arLista_procesa[0],'_')+1);

				if(stristr($nameTD,'FACT')){
                                    $destino="../reportes/rptsisFarmacia_facturatxt.php?id=$id&idDisposit";
				}
				else
                                    if($nameTD=='B/V'){
                                        $destino="../farmacia/rptsisFarmacia_boletatxt.php?id=$id";
                                    }elseif($nameTD=='O/C'){
                                        $destino="../farmacia/rptsisFarmacia_ordenCredito.php?id=$id";
                                    }elseif($nameTD=='G/R'){
                                        $destino="../farmacia/rptsisFarmacia_guiaRemisiontxt.php?id=$id";
                                    }else{
                                        $destino="../farmacia/rptsisFarmacia_recibotxt.php?id=$id";
                                        //$destino="../farmacia/rptsisFarmacia_recibo.php?id=$id";
                                    }

				$content = "controle";
			}
	   		break;

	   case 'EstContrato': // Anula Contratos
			$pg    = getParam("pagina");
			$arLista_procesa = getParam("sel");
			$estado=getParam("estado");
                        $fecha_anula=getParam("___fechaanula");
                        $motivo_anula=  strtoupper(getParam("___motivonula"));
			if (!is_array($arLista_procesa)) { // se no existen registros selecionados
                            $error="Ning\u00fan registro seleccionado!";
			}
			else { // se existe registro selecionados
				if (is_array($arLista_procesa))
					for ($i=0; $i<sizeof($arLista_procesa); $i++) {
                                                $id=$arLista_procesa[$i];
						/* Para asegurarme si alguien vuelve a anular un registro ya nulado no se ejecute la sentencia UPDATE */
						if($estado==9){ // Si estoy anulando un registro 
                                                    $sSql="UPDATE serfin.contrato 
                                                                SET cont_estado=$estado,
                                                                    cont_fechaanula='$fecha_anula',
                                                                    cont_motivoanula='".$motivo_anula."', 
                                                                    cont_usuaanula=".getSession("sis_userid")." 
                                                                WHERE cont_estado=1 
                                                                    AND cont_id=$id ";
                                                }
						elseif($estado==2){ // Si estoy cancelando un registro 
                                                    $sSql="UPDATE serfin.contrato 
                                                                SET cont_estado=$estado,
                                                                    cont_fechacancela='$fecha_anula',
                                                                    cont_doccancela='".$motivo_anula."', 
                                                                    cont_usuacancela=".getSession("sis_userid")." 
                                                                WHERE cont_estado=1 
                                                                    AND cont_id=$id ";
                                                }
						else {/* estoy Deshaciendo una anulación */
                                                     $sSql="UPDATE serfin.contrato 
                                                                    SET cont_estado=$estado,
                                                                        cont_usuaanula=".getSession("sis_userid")." 
                                                                    WHERE cont_estado IN (2,9) 
                                                                           AND cont_id=$id ";
                                                }
                                                //echo $sSql;
                                                //exit(0);
						// Ejecuto el string
						$conn->execute($sSql);
						$error=$conn->error();
							}
				// Armo strng a ejecutar
				}

			$destino = "../serfin/contratos_buscar.php?pagina=$pg";
	   		break;

	   case 'EstDocSERFINapo': // Anula aportes del SERFIN
			$pg    = getParam("pagina");
			$arLista_procesa = getParam("sel");
			$estado=getParam("estado");
                        $fecha_anula=getParam("___fechaanula");
                        $motivo_anula=  strtoupper(getParam("___motivonula"));
                        
			if (!is_array($arLista_procesa)) { // se no existen registros selecionados
                            $error="Ning\u00fan registro seleccionado!";
			}
			else { // se existe registro selecionados
				if (is_array($arLista_procesa))
					for ($i=0; $i<sizeof($arLista_procesa); $i++) {
                                                $id=substr($arLista_procesa[$i],strpos($arLista_procesa[$i],'_')+1);
						/* Para asegurarme si alguien vuelve a anular un registro ya nulado no se ejecute la sentencia UPDATE */
						if($estado==9){ // Si estoy anulando un registro 
                                                        if(getSession("sis_level")>=3)
                                                            $sSql="UPDATE aportes SET apor_estado=$estado,apor_fechaanula='$fecha_anula',apor_motivoanula='$motivo_anula',usua_anula=".getSession("sis_userid")." WHERE apor_estado=1 AND apor_id=$id ";
                                                        else
                                                            $sSql="UPDATE aportes SET apor_estado=$estado,apor_fechaanula='$fecha_anula',apor_motivoanula='$motivo_anula',usua_anula=".getSession("sis_userid")." WHERE apor_estado=1 AND apor_id=$id AND usua_id=".getSession("sis_userid");
                                                }
						else {/* estoy Deshaciendo una anulación */
                                                        if(getSession("sis_level")>=3)
                                                            $sSql="UPDATE aportes SET apor_estado=$estado WHERE apor_estado=9 AND apor_id=$id ";
                                                        else
                                                            $sSql="UPDATE aportes SET apor_estado=$estado WHERE apor_estado=9 AND apor_id=$id AND usua_id=".getSession("sis_userid");
                                                        
                                                }
                                                //echo         $sSql;
                                                //exit(0);
						// Ejecuto el string
						$conn->execute($sSql);
						$error=$conn->error();
							}
				// Armo strng a ejecutar
				}

			$destino = "../serfin/aportes_lista.php?pagina=$pg";
	   		break;
                        
	   case 'EstDocSERFINaten': // Anula Atenciones del SERFIN
			$pg    = getParam("pagina");
			$arLista_procesa = getParam("sel");
			$estado=getParam("estado");
                        $fecha_anula=getParam("___fechaanula");
                        $motivo_anula=  strtoupper(getParam("___motivonula"));
                        
			if (!is_array($arLista_procesa)) { // se no existen registros selecionados
                            $error="Ning\u00fan registro seleccionado!";
			}
			else { // se existe registro selecionados
				if (is_array($arLista_procesa))
					for ($i=0; $i<sizeof($arLista_procesa); $i++) {
                                                $id=$arLista_procesa[$i];
						/* Para asegurarme si alguien vuelve a anular un registro ya nulado no se ejecute la sentencia UPDATE */
						if($estado==9){ // Si estoy anulando un registro 
                                                        if(getSession("sis_level")>=3)
                                                            $sSql="UPDATE atenciones SET aten_estado=$estado,aten_fechaanula='$fecha_anula',aten_motivoanula='$motivo_anula',usua_anula=".getSession("sis_userid")." WHERE aten_estado=1 AND aten_id=$id ";
                                                        else
                                                            $sSql="UPDATE atenciones SET aten_estado=$estado,aten_fechaanula='$fecha_anula',aten_motivoanula='$motivo_anula',usua_anula=".getSession("sis_userid")." WHERE aten_estado=1 AND aten_id=$id AND usua_id=".getSession("sis_userid");
                                                }
						else {/* estoy Deshaciendo una anulación */
                                                        if(getSession("sis_level")>=3)
                                                            $sSql="UPDATE atenciones SET aten_estado=$estado WHERE aten_estado=9 AND aten_id=$id ";
                                                        else
                                                            $sSql="UPDATE atenciones SET aten_estado=$estado WHERE aten_estado=9 AND aten_id=$id AND usua_id=".getSession("sis_userid");
                                                        
                                                }
                                                //echo         $sSql;
                                                //exit(0);
						// Ejecuto el string
						$conn->execute($sSql);
						$error=$conn->error();
							}
				// Armo strng a ejecutar
				}

			$destino = "../serfin/atenciones_lista.php?pagina=$pg";
	   		break;                        
                        
            case 'ActTitAtencion': // Actualiza titular en Atención
			$pg    = getParam("pagina");
			$arLista_procesa = getParam("sel");
                        
			if (!is_array($arLista_procesa)) { // se no existen registros selecionados
                            $error="Ning\u00fan registro seleccionado!";
			}
			else { // se existe registro selecionados
				if (is_array($arLista_procesa))
					for ($i=0; $i<sizeof($arLista_procesa); $i++) {
                                                $id=$arLista_procesa[$i];
                                                $sSql="UPDATE serfin.atenciones 
                                                        SET titu_id=
                                                           (SELECT titu_id
                                                            FROM serfin.contrato 
                                                            WHERE cont_id IN 
                                                                (SELECT a.cont_id
                                                                    FROM serfin.atenciones a
                                                                    WHERE a.aten_id=$id)) 
                                                         WHERE      aten_estado=1 
                                                                AND aten_id=$id";
                                                
						$conn->execute($sSql);
						$error=$conn->error();
                                                if($error){//hay error cuando el contador de devoluciones intenta superar el maximo de devoluciones permitidas (se activa el check de la BD)
                                                    alert($sSql);
                                                }                                                
					}
				}

			$destino = "../serfin/atenciones_lista.php?pagina=$pg";
	   		break;
                        
            case 'impDocSERFIN': // Imprime documento de caja de ingresos
			$pg    = getParam("pagina");
			$arLista_procesa = getParam("sel");

			if (!is_array($arLista_procesa)) { // se no existen registros selecionados
					$error="Ning\u00fan registro seleccionado!";
				}
			else { // se existe registro selecionados
				$nameTD=substr($arLista_procesa[0],0,strpos($arLista_procesa[0],'_'));

				$id=substr($arLista_procesa[0],strpos($arLista_procesa[0],'_')+1);
				
				if(stristr($nameTD,'RECI')){
                                    $destino="../serfin/rptSerfin_reciboAportetxt.php?id=$id";
				}
				else
                                    if($nameTD=='INS'){
                                        $destino="../serfin/rptSerfin_reciboInscripciontxt.php?id=$id";
                                    }
				$content = "controle";
			}
	   		break;                        
                        
	   case 'PerfilVista': // Catalogos/Perfil/Vista
		   $id=getParam("id");
                   $sSql="DELETE FROM cementerio.componentes_cementerio_vistas WHERE ccvi_id=$id ";
		   //$sSql="DELETE FROM cementerio.componentes_cementerio_vistas WHERE ccvi_id=$id and usua_id=".getSession("sis_userid");

			// Ejecuto el string
                        //alert($sSql);
			$conn->execute($sSql);
			$error=$conn->error();
			$notice=$conn->notice();

		   $destino = "../cementerio/catalogosComponentesCementerio_componentes.php?relacionamento_id=$relacionamento_id";
		   break;
    
	   case 'EstDocRecauda': // Anula documentos de Recaudaciones, siempre que el documento no se encuentre procesado y pertenezca al usuario que realiza la accion
			$pg    = getParam("pagina");
                        $idHora = getParam("idHora");
			$arLista_procesa = getParam("sel");
			$estado =getParam("estado");
			if (!is_array($arLista_procesa)) { // se no existen registros selecionados
					$error="Ning\u00fan  registro seleccionado!";
				}
			else { // se existe registro selecionados
				if (is_array($arLista_procesa))
					for ($i=0; $i<sizeof($arLista_procesa); $i++) {
						$id=substr($arLista_procesa[$i],strpos($arLista_procesa[$i],'_')+1);
						/*el supervisor puede anular cualquier documento*/
						if(getSession("sis_level")>=3)
							$sSql="UPDATE recaudaciones SET reca_estado='$estado' WHERE reca_id=$id ";
						else{
							$sSql="UPDATE recaudaciones SET reca_estado='$estado' WHERE reca_id=$id and usua_id=".getSession("sis_userid")." AND reca_proceso='0'";
                                                }
                                                    // Ejecuto el string
                                                $conn->execute($sSql);
                                                $error=$conn->error();
					}
				// Armo strng a ejecutar
				}

			$destino = "../siscore/siscoreCajaIngresos_lista.php?pagina=$pg";
	   		break;           

	   case 'impDocRecauda': // Imprime documento de Tesoreria-Recaudaciones
			$pg    = getParam("pagina");
			$arLista_procesa = getParam("sel");

			if (!is_array($arLista_procesa)) { // se no existen registros selecionados
                            $error="Ning\u00fan registro seleccionado!";
                        }
			else { // se existe registro selecionados
				$id=$arLista_procesa[0];
                                //alert($id);
                                $nameTD=getDbValue("SELECT b.tabl_descripaux FROM siscore.recaudaciones a
                                                                    LEFT JOIN catalogos.tabla b ON a.tabl_tipdoc=b.tabl_id
                                                            WHERE a.reca_id=$id");
                                //alert($nameTD);
				if(stristr($nameTD,'FACT')){
                                    $destino="../siscore/rptSiscore_factura.php?sel=$id";
				}elseif($nameTD=='B/V'){
                                    $destino="../siscore/rptSiscore_boleta.php?sel=$id";
                                }elseif($nameTD=='RECI'){
                                    $destino="../siscore/rptSiscore_recibo.php?sel=$id";
                                }elseif($nameTD=='N/C'){
                                    $destino="../siscore/rptSiscore_ncredito.php?sel=$id";
                                }elseif($nameTD=='N/D'){
                                    $destino="../siscore/rptSiscore_ndebito.php?sel=$id";                                    
                                }

				$content = "controle";
			}
	   		break;

	   case 'impPedido': // Imprime Pedido
			$pg    = getParam("pagina");
			$arLista_procesa = getParam("sel");

			if (!is_array($arLista_procesa)) { // se no existen registros selecionados
                            $error="Ning\u00fan registro seleccionado!";
                        }
			else { // se existe registro selecionados
				$id=$arLista_procesa[0];
                                $destino="../cementerio/rptPedido.php?sel=$id";
				$content = "controle";
			}
	   		break;

	   case 'impAtenciones': // Imprime Atencion
			$pg    = getParam("pagina");
			$arLista_procesa = getParam("sel");

			if (!is_array($arLista_procesa)) { // se no existen registros selecionados
                            $error="Ning\u00fan registro seleccionado!";
                        }
			else { // se existe registro selecionados
				$id=$arLista_procesa[0];
                                $destino="../serfin/rptCartaGarantia.php?sel=$id";
				$content = "controle";
			}
	   		break;
                        
	   case 'actoJudicial': // Anula Expediente Judicial
			$pg    = getParam("pagina");
                        $relacionamento_id = getParam("relacionamento_id");
			$arLista_procesa = getParam("sel");
			$estado=getParam("estado");
                        $fecha_anula=getParam("___fechaanula");
                        $motivo_anula=  strtoupper(getParam("___motivonula"));
			if (!is_array($arLista_procesa)) { // se no existen registros selecionados
                            $error="Ning\u00fan registro seleccionado!";
			}
			else { // se existe registro selecionados
				if (is_array($arLista_procesa))
					for ($i=0; $i<sizeof($arLista_procesa); $i++) {
                                                $id=$arLista_procesa[$i];
						/* Para asegurarme si alguien vuelve a anular un registro ya nulado no se ejecute la sentencia UPDATE */
						if($estado==9){ // Si estoy anulando un registro 
                                                    $sSql="UPDATE gestleg.expediente_acto_procesal 
                                                                SET exap_estado=$estado,
                                                                    exap_fechaanula='$fecha_anula',
                                                                    exap_motivoanula='".$motivo_anula."', 
                                                                    exap_usuaanula=".getSession("sis_userid")." 
                                                                WHERE exap_estado=1
                                                                           AND exap_id=$id ";
                                                }
						else {/* estoy Deshaciendo una anulación */
                                                     $sSql="UPDATE gestleg.expediente_acto_procesal 
                                                                    SET exap_estado=$estado,
                                                                        exap_usuaanula=".getSession("sis_userid")." 
                                                                    WHERE exap_estado=9
                                                                           AND exap_id=$id ";
                                                }
                                                //echo $sSql;
                                                //exit(0);
						// Ejecuto el string
						$conn->execute($sSql);
						$error=$conn->error();
							}
				// Armo strng a ejecutar
				}

			$destino = "../gestleg/expedienteLegalActos_lista.php?relacionamento_id=$relacionamento_id&clear=1&pagina=$pg";
	   		break;

	   case 'pagoSentencia': // Anula Pago de Sentencia Judicial
			$pg    = getParam("pagina");
                        $relacionamento_id = getParam("relacionamento_id");
			$arLista_procesa = getParam("sel");
			$estado=getParam("estado");
                        $fecha_anula=getParam("___fechaanula");
                        $motivo_anula=  strtoupper(getParam("___motivonula"));
			if (!is_array($arLista_procesa)) { // se no existen registros selecionados
                            $error="Ning\u00fan registro seleccionado!";
			}
			else { // se existe registro selecionados
				if (is_array($arLista_procesa))
					for ($i=0; $i<sizeof($arLista_procesa); $i++) {
                                                $id=$arLista_procesa[$i];
						/* Para asegurarme si alguien vuelve a anular un registro ya nulado no se ejecute la sentencia UPDATE */
						if($estado==9){ // Si estoy anulando un registro 
                                                    $sSql="UPDATE gestleg.expediente_pago 
                                                                SET expa_estado=$estado,
                                                                    expa_monto_ejecutado=0,
                                                                    expa_fechaanula='$fecha_anula',
                                                                    expa_motivoanula='".$motivo_anula."', 
                                                                    expa_usuaanula=".getSession("sis_userid")." 
                                                                WHERE expa_estado=1
                                                                           AND expa_id=$id ";
                                                }
						else {/* estoy Deshaciendo una anulación */
                                                     $sSql="UPDATE gestleg.expediente_pago
                                                                    SET expa_estado=$estado,
                                                                        expa_usuaanula=".getSession("sis_userid")." 
                                                                    WHERE expa_estado=9
                                                                           AND expa_id=$id ";
                                                }
                                                //echo $sSql;
                                                //exit(0);
						// Ejecuto el string
						$conn->execute($sSql);
						$error=$conn->error();
							}
				// Armo strng a ejecutar
				}

			$destino = "../gestleg/expedienteLegalPagos_lista.php?relacionamento_id=$relacionamento_id&clear=1&pagina=$pg";
	   		break;
                        
	 case 'PlanContable': //habilitar divisionarias en plan contable
	 	
			$pg    = getParam("pagina");
			$sSql="SELECT func_set_divcontable()";

			// Ejecuto el string
			$conn->execute($sSql);
			$error=$conn->error();

			$notice=$conn->notice();

			if($notice) 
				alert($notice,0); //MUESTRA EL MENSAJE Y CONTINUA LA EJECUCION

			//DEBIDO A QUE SE EJECUTA EN EL FRAME 'controle' NO NECESITA REGRESAR A LA PAGINA DE DONDE FUE LLAMADA
			//FUNCIONA SOLO CUANDO LA PAGINA DONDE FUE LLAMADA NO SUFRE CAMBIO ALGUNO
			//$destino = "../modulos/siscontCatalogosPlanContable_buscarx.php?pagina=$pg";
		   
	   		break;	                          
                        
                        
	 case 'Especificas': //habilitar específicas en clasificador presupuestal
	 	
			$pg    = getParam("pagina");
			$sSql="SELECT func_set_esppresupuestal()";

			// Ejecuto el string
			$conn->execute($sSql);
			$error=$conn->error();

			$notice=$conn->notice();

			if($notice) 
				alert($notice,0); //MUESTRA EL MENSAJE Y CONTINUA LA EJECUCION

			//DEBIDO A QUE SE EJECUTA EN EL FRAME 'controle' NO NECESITA REGRESAR A LA PAGINA DE DONDE FUE LLAMADA
			//FUNCIONA SOLO CUANDO LA PAGINA DONDE FUE LLAMADA NO SUFRE CAMBIO ALGUNO
			//$destino = "../modulos/siscontCatalogosPlanContable_buscarx.php?pagina=$pg";
		   
	   		break;	   
	   		
	   case 'AbrAcuPptal': // Abrir Acumulado Presupuestal
			$pg    = getParam("pagina");
			$arLista_elimina = getParam("sel");
			$tipoAcumulado=getParam("tip_acu");
			if (!is_array($arLista_elimina)) { // se no existen registros selecionados
					$error="Sin registro seleccionado para procesar!";
				} 
			else { // se existe registro selecionados
				// Armo strng a ejecutar
				$sSql="update acumulado_presupuestal set acpr_estado=1 where acpr_id=".$arLista_elimina[0];
	
				// Ejecuto el string
				$conn->execute($sSql);
				$error=$conn->error();
				}
	
			$destino = "../siscopp/siscoppAperturasAcumulados_lista.php?tip_acu=$tipoAcumulado&pagina=$pg"; 
				   
	   		break;	   

	   case 'CerAcuPptal': // Cerrar Acumulado Presupuestal
			$pg    = getParam("pagina");
			$arLista_elimina = getParam("sel");
			$tipoAcumulado=getParam("tip_acu");			
			if (!is_array($arLista_elimina)) { // se no existen registros selecionados
                            $error="Sin registro seleccionado para procesar!";
			} 
			else { // se existe registro selecionados
				// Armo strng a ejecutar
				$sSql="update siscopp.acumulado_presupuestal set acpr_estado=0 where acpr_id=".$arLista_elimina[0];
	
				// Ejecuto el string
				$conn->execute($sSql);
				$error=$conn->error();
                                
				}
	
			$destino = "../siscopp/siscoppAperturasAcumulados_lista.php?tip_acu=$tipoAcumulado&pagina=$pg"; 
				   
	   		break;	                           
                        
	   case 'PerfilGrp': // Catalogos/Perfil/Grupo
		   $id=getParam("id");
   		   $busEmpty=getParam("busEmpty"); //muestra todos los registro ;
                   $tipo_estudio=getParam("tipo_estudio");
                   $espe_id=getParam("espe_id");
                   //$sSql="DELETE FROM gestmed.examen_grupo WHERE exgr_id=$id and usua_id=".getSession("sis_userid");                   
		   $sSql="DELETE FROM gestmed.examen_grupo WHERE exgr_id=$id ";

			// Ejecuto el string
			$conn->execute($sSql);
			$error=$conn->error();
			$notice=$conn->notice();

		   $destino = "../laboratorio/sisEstudiosCatalogosPerfil_edicion.php?busEmpty=$busEmpty&id=$relacionamento_id&tipo_estudio=$tipo_estudio&espe_id=$espe_id";
		   break;
               
	   case 'PerfilGrpDet': // Catalogos/Perfil/Grupo/Detalle
		   $id=getParam("id");
   		   $busEmpty=getParam("busEmpty"); //muestra todos los registro ;
		   $PerfilGrpDet=getParam("PerfilGrpDet");
                   $tipo_estudio=getParam("tipo_estudio");
                   $espe_id=getParam("espe_id");
                   //$sSql="DELETE FROM gestmed.examen_grupo_detalle WHERE egde_id=$id and usua_id=".getSession("sis_userid");                   
		   $sSql="DELETE FROM gestmed.examen_grupo_detalle WHERE egde_id=$id ";

			// Ejecuto el string
			$conn->execute($sSql);
			$error=$conn->error();
			$notice=$conn->notice();

		   $destino = "../laboratorio/sisEstudiosCatalogosPerfil_edicion.php?busEmpty=$busEmpty&id=$relacionamento_id&PerfilGrpDet=$PerfilGrpDet&tipo_estudio=$tipo_estudio&espe_id=$espe_id";
		   break;

                        
	   case 'AteMedEstArch': // Atención medica estudios/archivos
		   $id=getParam("id");

		   $last_id=getParam("f_id") ;//campo clave de la tabla padre
		   $atme_id=getParam("atme_id");
		   $hipe_id=getParam("hipe_id");
		   $clear=getParam("clear");
		   $sSql="DELETE FROM gestmed.atencion_medica_estudios_archivo WHERE amea_id=$id and usua_id=".getSession("sis_userid");

			// Ejecuto el string
			$conn->execute($sSql);
			$error=$conn->error();
			$notice=$conn->notice();

   		   $destino = "../gestmed/sisAtenMedAtenCitaConsulta_estudiosEdicion.php?id=$last_id&relacionamento_id=$atme_id&hipe_id=$hipe_id&clear=$clear";
		   break;
               
               

	   case 'AntGinObs1': // HISTORIA-aAntecedentes GINECOLOGICOS PARTE 6
		   $id=getParam("id");
		   $clear=getParam("clear");
 		   $sSql="DELETE FROM historia_antecedente_ginecobsta WHERE haga_id=$id and usua_id=".getSession("sis_userid");
			// Ejecuto el string
			$conn->execute($sSql);
			$error=$conn->error();
			$notice=$conn->notice();

		   $destino = "../gestmed/sisAdmisionHistClinica_antGinecoObst.php?id=$relacionamento_id&clear=$clear";
		   break;

	   case 'AntGinObs2': // HISTORIA-aAntecedentes GINECOLOGICOS PARTE 7
		    $id=getParam("id");
		    $clear=getParam("clear");			
		    $sSql="DELETE FROM historia_antecedente_ginecobstb WHERE hagb_id=$id and usua_id=".getSession("sis_userid");
			// Ejecuto el string
			$conn->execute($sSql);
			$error=$conn->error();
			$notice=$conn->notice();

		   $destino = "../gestmed/sisAdmisionHistClinica_antGinecoObst.php?id=$relacionamento_id&clear=$clear";
		   break;

	   case 'HCAntPersona': // Historia Clinica:Antecedentes Personales
		   $id=getParam("id");
		   $clear=getParam("clear");
		   $sSql="DELETE FROM historia_antecedente_personal WHERE hape_id=$id and usua_id=".getSession("sis_userid");

			// Ejecuto el string
			$conn->execute($sSql);
			$error=$conn->error();
			$notice=$conn->notice();

		   $destino = "../gestmed/sisAdmisionHistClinica_antPersonal.php?id=$relacionamento_id&clear=$clear";
		   break;

	   case 'HCAntFamiliar': // Historia Clinica:Antecedentes Familiar
		   $id=getParam("id");
		   $clear=getParam("clear");		   
		   $sSql="DELETE FROM historia_antecedente_familiar WHERE hafa_id=$id and usua_id=".getSession("sis_userid");

			// Ejecuto el string
			$conn->execute($sSql);
			$error=$conn->error();
			$notice=$conn->notice();

		   $destino = "../gestmed/sisAdmisionHistClinica_antFamiliar.php?id=$relacionamento_id&clear=$clear";
		   break;

	   case 'HCdatLaboralRgo': // Historia Clinica:Datos Laborales riesgo
		   $id=getParam("id");
		   $clear=getParam("clear");		   
		   $sSql="DELETE FROM historia_dato_laboral_riesgo WHERE hdlr_id=$id and usua_id=".getSession("sis_userid");

			// Ejecuto el string
			$conn->execute($sSql);
			$error=$conn->error();
			$notice=$conn->notice();

		   $destino = "../gestmed/sisAdmisionHistClinica_datLaboral.php?id=$relacionamento_id&clear=$clear";
		   break;

	   case 'HChabito': // Historia Clinica:Datos Laborales riesgo
		   $id=getParam("id");
		   $clear=getParam("clear");		   
		   $sSql="DELETE FROM historia_habito WHERE hiha_id=$id and usua_id=".getSession("sis_userid");

			// Ejecuto el string
			$conn->execute($sSql);
			$error=$conn->error();
			$notice=$conn->notice();

		   $destino = "../gestmed/sisAdmisionHistClinica_habitos.php?id=$relacionamento_id&clear=$clear";
		   break;


	   case 'HCalergia': // Historia Clinica:Alergias
		   $id=getParam("id");
		   $clear=getParam("clear");		   
		   $sSql="DELETE FROM historia_alergia WHERE hial_id=$id and usua_id=".getSession("sis_userid");

			// Ejecuto el string
			$conn->execute($sSql);
			$error=$conn->error();
			$notice=$conn->notice();

		   $destino = "../gestmed/sisAdmisionHistClinica_alergContrai.php?id=$relacionamento_id&clear=$clear";
		   break;


	   case 'HCcontraind': // Historia Clinica:Contraindicaciones
		   $id=getParam("id");
		   $clear=getParam("clear");		   
		   $sSql="DELETE FROM historia_contraindicacion WHERE hico_id=$id and usua_id=".getSession("sis_userid");

			// Ejecuto el string
			$conn->execute($sSql);
			$error=$conn->error();
			$notice=$conn->notice();

		   $destino = "../gestmed/sisAdmisionHistClinica_alergContrai.php?id=$relacionamento_id&clear=$clear";
		   break;

	   case 'HCvacuna': // Historia Clinica:vacuna
		   $id=getParam("id");
		   $clear=getParam("clear");		   
		   $sSql="DELETE FROM historia_vacuna WHERE hiva_id=$id and usua_id=".getSession("sis_userid");

			// Ejecuto el string
			$conn->execute($sSql);
			$error=$conn->error();
			$notice=$conn->notice();

		   $destino = "../gestmed/sisAdmisionHistClinica_vacuna.php?id=$relacionamento_id&clear=$clear";
		   break;

	   case 'HCConstVit': // Historia Clinica:vacuna
		   $id=getParam("id");
		   $clear=getParam("clear");		   
		   $sSql="DELETE FROM historia_constante_vital WHERE hcvi_id=$id and usua_id=".getSession("sis_userid");

			// Ejecuto el string
			$conn->execute($sSql);
			$error=$conn->error();
			$notice=$conn->notice();

		   $destino = "../gestmed/sisAdmisionHistClinica_consVit.php?id=$relacionamento_id&clear=$clear";
		   break;		   


   	   case 'HCproblema': // // Historia Clinica:Problema
		   $id=getParam("id");
		   $clear=getParam("clear");		   
		   $sSql="DELETE FROM historia_problema WHERE hipr_id=$id and usua_id=".getSession("sis_userid");

			// Ejecuto el string
			$conn->execute($sSql);
			$error=$conn->error();
			$notice=$conn->notice();

		   $destino = "../gestmed/sisAdmisionHistClinica_problema.php?id=$relacionamento_id&clear=$clear";
		   break;
               
                        
    } // Fin del switch

    if($error)
        alert($error);
    else {
		/*  muestra mensaje noticia del la base de datos, pero no detiene la ejecucion	*/
        $notice=$conn->notice();
        if($notice)
            alert($notice,0);

        // si es una ventana emergente desde donde se llama al guardar
        if(strcmp("close",$destino)==0) {
            echo "<"."script".">\n";
            if ($ventana == "thickbox") {
                echo "self.parent.document.location.reload()";
            } else {
                echo "javascript:top.close()\n";
            }
            echo "</"."script".">\n";
        }
        else {
        // redirecciona segun la variable $destino
            if($destino) // Si existeun destino al que deseo ir después del proceso			
                redirect($destino,$content);

        }
    } // Fin del else

} else { // si no pasa la validaci�n
    alert('Mensajes de errores!\n\n'.$erro->toString());
}

//	cierra la conexi�n con la BD
$conn->close();