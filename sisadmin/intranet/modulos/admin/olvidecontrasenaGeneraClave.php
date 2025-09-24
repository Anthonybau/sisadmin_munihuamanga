<?php
/* formulario de ingreso y modificaci�n */
include($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");

/* Recibo par�metros */
$usua_id = base64_decode(getParam("pass")); 
$cs = base64_decode(getParam("cs")); 

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

?>
<html>
<head>
	<title>Genera Contraseña</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language='JavaScript'>

	</script>
</head>
<body class="contentBODY" >
<?php
pageTitle("SISADMIN: Sistema Integrado Administrativo","Generaci&oacute;n de Contrase&ntilde;a");

echo '<br>';

/* Verifico c�dgigo de seguridad */
$csBd = getDbValue("SELECT usua_codigoseguridad FROM admin.usuario WHERE usua_id = '$usua_id'");

if($csBd == $cs) {
    /* Cambio la contrase�a del usuario recibido */
    $nvaClave = mt_rand(1000, 9000);
    $sSql="UPDATE admin.usuario 
                SET usua_password = md5('$nvaClave'),
                    usua_activo=3  /*obligado a cambiar contraseña*/
    		WHERE usua_id = $usua_id";
    
    $conn->execute($sSql);
    $error=$conn->error();
    if ($error) {
    	echo 'Error en proceso de Generaci&oacute;n de contrase&ntilde;a.';
    } else {
        echo "<b>Generaci&oacute;n de contrase&ntilde;a exitosa.</b><BR>
        		Su nueva contrase&ntilde;a es: <b><h3> $nvaClave </h3></b> ";
    }
} else {
    echo 'Error en c&oacute;digo de seguridad.  No ha sido posible generar su nueva contrase&ntilde;a.<br>
    	Vuela a solicitar el proceso de Generaci&oacute;n de contrase&ntilde;a';    
}
?>
<BR>
</body>
</html>
<?php
$conn->close(); 
