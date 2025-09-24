<?php
/* formulario de ingreso y modificación */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);
$id = getParam("id"); // captura la variable que viene del objeto lista
$ffhh = getParam("ffhh"); // captura la variable que viene del objeto
$op = getParam("op");
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>mensaje</title>
<style type="text/css">
<!--
.Estilo1 {
    color:#000000;
    font-family:Tahoma,Verdana,Arial,Helvetica;
    font-size:14px;
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

function imprimir(file) {
    AbreVentana(file);
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
                        <div align="center" class="Estilo1">NUMERO DE <?php echo NAME_EXPEDIENTE_UPPER.":<br><a class=\"link\" href=\"javascript:imprimir('rptHT.php?id=$id');\"><font size=18px color=000 >$id</font></a><br>Fecha y Hora de Registro: $ffhh"?>
                        </div></td>
                </tr>
                <tr><td>
                        <a class="link" target="content" href="<?php echo iif($op,"==",2,'registroDespacho_edicionConFirma.php?clear=1','registroDespacho_edicion.php?clear=1')?>">Nuevo Registro <img src="../../img/mas_info.gif" width="14" height="9" align="absmiddle" border=0></img>
                        </a>
                        <a class="link" target="content" href="<?php echo iif($op,"==",2,'registroDespacho_edicion.php?id='.$id,'registroDespacho_edicion.php?id='.$id)?>">Editar Registro <img src="../../img/mas_info.gif" width="14" height="9" align="absmiddle" border=0></img>
                        </a>
                        <a class="link" target="content" href="javascript:imprimir('../../../portal/gestdoc/consultarTramiteProceso.php?nr_numTramite=<?php echo $id?>&vista=NoConsulta')">Hacer Seguimiento
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>



