<?php
/* formulario de ingreso y modificación */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);
$titulo = getParam("titulo"); // captura la variable que viene del objeto lista
$msj = getParam("msj"); // captura la variable que viene del objeto lista

?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>mensaje</title>
<style type="text/css">
<!--
.Estilo1 {
    color:red;
    font-family:Tahoma,Verdana,Arial,Helvetica;
    font-size:16px;
    font-weight:bold;
}
a.link:link {
    color:#006699;
    font-family:Verdana,Arial,Helvetica;
    font-size:10pt;
    font-style:normal;
    text-decoration:none;
}
a.link:active {
    color:#006699;
    font-family:Verdana,Arial,Helvetica;
    font-size:10pt;
    font-style:normal;
    text-decoration:none;
}
a.link:visited {
    color:#006699;
    font-family:Verdana,Arial,Helvetica;
    font-size:10pt;
    font-style:normal;
    text-decoration:none;
}
a.link:hover {
    color:#006699;
    font-family:Verdana,Arial,Helvetica;
    font-size:10pt;
    font-style:normal;
    text-decoration:underline;
}

-->
</style>
<script language="JavaScript">

function AbreVentana(sURL){
    var w=720, h=600;
    venrepo=window.open(sURL,'rptSalida', "status=yes,resizable=yes,toolbar=no,scrollbars=yes,top=50,left=150,width=" + w + ",height=" + h, 1 );
    venrepo.focus();
}

function imprimir(id) {
    AbreVentana('rptHT.php?id='+id);
}
</script>
<?php verif_framework();?>
</head>

<body>
<table width="100%" height="100%" border="0">
    <tr>
        <td width=100% align=center valign=center>
            <table width=450 border=2 bgcolor="lightyellow">
                <tr>
                    <td height="72">
                        <div align="center" class="Estilo1"><br><?php echo $titulo?><br><br><font size=3px color=000 ><?php $msj?></font><br><br>
                        </div></td>
                </tr>
                <tr><td>&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>

