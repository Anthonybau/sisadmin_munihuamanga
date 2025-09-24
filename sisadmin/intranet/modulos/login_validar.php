<?php
/*
    Valida los dados ingresados en el formulario login.
*/

/*Bibioteca de funciones y configuraciones*/
include("../library/library.php");

/*guarda los mensajes de validacioes */
$errores = new Erro();
/*inicializa el flujo de pagina*/
$destino = "../modulos/login.php";

# Comprobamos RECAPTCHA
//if (!isset($_POST["g-recaptcha-response"]) || empty($_POST["g-recaptcha-response"])) {
//    $errores->addErro('Debe completar el captcha!');
//}else{
    #vemos si resolvieron el captcha
    //$token = $_POST["g-recaptcha-response"];
    //$verificado = verificarToken($token, KEY_SECRETA);
    $verificado = 1;
    # Si no ha pasado la Verificación
    if ($verificado) {
    
        include("admin/adminUsuario_class.php");

        /* establecer conexión con la BD */
        try{
            $arHost= explode(":",DB_HOST);
            $connection = new PDO("pgsql:host=$arHost[0];port=$arHost[1];dbname=".DB_DATABASE, DB_USER, DB_PASSWORD);          
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        }catch(PDOException $x) { 
            $errores->addErro('No existe conexion a Base de Datos!');
        }

        if (!$errores->hasErro()) {
                    
            /*  tratamiento de datos */
            $username = strtoupper(addslashes(getParam("Sr_username")));
            $password = md5(addslashes(getParam("sx_senha")));

            if ($username == "") $errores->addErro('Ingrese nombre de usuario!');
            if (getParam("sx_senha") == "") $errores->addErro('Ingrese password!');

            // si estan los datos completos, pasan a validación...
            if (!$errores->hasErro()) {

                    $sql=new clsUsers_SQLlista();
                    $sql->whereUserLogin2(':user_login');
                    $sql->whereUserPassword2(':user_password');

                    $statement = $connection->prepare($sql->getSQL());
                    $statement->bindParam(':user_login', $username , PDO::PARAM_STR);
                    $statement->bindParam(':user_password', $password , PDO::PARAM_STR);
                    $statement->execute();
                    $rs=$statement->fetch(PDO::FETCH_OBJ);
                    
                    // si ingresa...
                    if ( $statement->rowCount()>0 ) {	

                            if($rs->usua_activo==1) {

                                    if (addslashes($rs->usua_login)==$username){

                                            setSession("sis_userid",$rs->usua_id);			///id de usuario
                                            setSession("sis_username",$rs->usua_login); //nombre de usuario
                                            setSession("sis_username_antiguo",$rs->empleado_breve);  //para guardarlo en un cookies

                                            setSession("sis_level",$rs->usua_acceso);   //nivel de usuario
                                            setSession("sis_apl", SIS_APL_NAME); // nombre de la aplicacion
                                            setSession("sis_iporigen",detectar_ip()); // IP de la pc que esta accediendo al sistema
                                            setSession("sis_depeid",$rs->depe_id);			///id de usuario
                                            setSession("sis_depe_superior",$rs->depe_superior_id);
                                            setSession("sis_persid",$rs->pers_id);
                                            setSession("sis_pdlaid",$rs->pdla_id);//persona/datos_laborales
                                            setSession("sis_medi_id",$rs->medi_id);
                                            setSession("sis_empresa_ruc",SIS_EMPRESA_RUC);

                                            setSession("SET_ESJEFE",$rs->es_jefe);
                                            setSession("SET_AUTORIZA_SOLICITUD",$rs->usua_set_autoriza_solicitudes);
                                            setSession("SET_TODOS_USUARIOS",$rs->usua_set_gestdoc_todos);
                                            setSession("SET_TIPO_DESPACHO",$rs->tabl_settipodespacho);
                                            setSession("SET_CERTIFICADO",$rs->usua_set_certificado);
                                            setSession("SET_DEPE_EMISOR",$rs->depe_id_set);
                                            setSession("SET_DEPE_EMISOR_EXCLUSIVO",$rs->usua_set_depe_exclusivo);
                                            setSession("SET_DEPE_COMPRAS_VENTAS",$rs->depe_id_almacen);
                                            setSession("SET_VENT_ID",$rs->vent_id);
                                            setSession("SET_APLICA_DESCUENTO",$rs->usua_set_aplica_ajustes_ventas);
                                            setSession("SET_EDITA_TOTAL",$rs->usua_set_edita_total);
                                            setSession("SET_EDITA_TOTAL2",$rs->usua_set_edita_total2);
                                            setSession("SET_EDITA_IMPUNITARIO",$rs->usua_set_edita_impunitario);
                                            setSession("SET_EDITA_FECHA",$rs->usua_set_edita_fecha);
                                            $destino = "modulos.php";

                                            // Obtengo el nombre de la dependencia
                                            if(inlist($rs->usua_tipo,'1,2')){ // Si es usuario normal del sistema
            //                                        if($rs->field("depe_almacen")){
            //                                            setSession("sis_depename",$rs->field("depe_nombrecorto").'/ALM:'.$rs->field("depe_almacen"));	//nombre de la dependencia
            //                                        }else{
                                                        if($rs->depe_superior_nombre && $rs->usua_id>1){//ADMIN
                                                            setSession("sis_depename",$rs->depe_nombre.'/'.$rs->depe_superior_nombre);	//nombre de la dependencia                                                
                                                        }else{
                                                            setSession("sis_depename",$rs->depe_nombre);                                                
                                                        }
            //                                        }
                                            }
                                    } else {
                                        $errores->addErro('Error Cr&iacute;tico, Denegado el acceso!');
                                    }
                            }elseif(inlist($rs->usua_activo,'2,3')) {//en proceso de transición
                                $destino = "login_cambiarcontrasena.php?user_name=$username";                    
                            }else {
                                $errores->addErro('Su cuenta esta desactivada, consulte con el WEB-ADMIN del sistema!');
                            }
                    // si no ingresa...
                    } else {
                            $errores->addErro('Nombre de usuario o contrase\u00f1a no son correctos!');
                    }
            // los datos no pasan la validación...
            } 

            $connection = null;
        }
    } else {
        $errores->addErro('Lo siento, parece que eres un robot');
    }
    
//} 

if($errores->hasErro()){
    redirect($destino."?message=".$errores->toString(),"content");
}else{
    echo "<script language='JavaScript'>";
    echo "top.header.document.location.reload();";
    echo "</script>";
    
    redirect($destino,"content");
}