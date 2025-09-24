<?php
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
include("registroDespacho_class.php");

$conn = new db();
$conn->open();

$id = getParam("id");

/*obtengo los datos del registro padre*/
$sql=new despacho_SQLlista();
$sql->whereID($id);
$sql->orderUno();
$sql=$sql->getSQL();
//echo $sql ;
/* creo el recordset */
$rsPadre = new query($conn, $sql);

if ($rsPadre->numrows()==0){
	alert("No existen registros para procesar...!");
}
$rsPadre->getrow();

/*obtengo los datos de las derivaciones*/
$sql=new despachoDerivacion_SQLlista();
$sql->wherePadreID($id);
$sql->orderUno();
$sql=$sql->getSQL();
//echo $sql ;
/* creo el recordset */
$rs = new query($conn, $sql);
$rs->getrow();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">	

<title>Hoja de Tr&aacute;mite</title>
<style>
P.breakhere { page-break-before:always; border:0px; margin:0px; background:#FFFF00; }
body {
    font-size: 9pt;
    font-family:Verdana, Arial, Helvetica, sans-serif;
    }

</style>
<script>
function ShowDiv(ObjName)
	{
	dObj=document.getElementById(ObjName);
	dObj.style.top = document.body.scrollTop;
	dObj.style.left= (document.body.scrollWidth - 85);					
	setTimeout("ShowDiv('"+ObjName+"')",1);
}

function ocultarObj(idObj,timeOutSecs){
	// luego de timeOutSecs segundos, el bot�n se habilitar� de nuevo, 
	// para el caso de que el servidor deje de responder y el usuario 
	// necesite volver a submitir. 
	myID = document.getElementById(idObj);
	myID.style.display = 'none';
	document.body.style.cursor = 'wait'; // relojito
	setTimeout(function(){myID.style.display = 'inline';document.body.style.cursor = 'default';},timeOutSecs*1000)
}

</script>
</head>
<body onload="ShowDiv('cuadro')">
<div id="cuadro" class="oculto" style="position: absolute; top: 0pt; right: 0pt" >
<br><img src="../../img/printer.gif" id="printer" alt="Imprimir" onclick="ocultarObj('printer',50);javascript:print();" style="cursor: pointer;" height="40" width="40">
</div>

<table border="0" width="585">
  <tbody><tr>
    <td colspan="6"><?php echo SIS_EMPRESA?></td>
  </tr>
  <tr>
    <td colspan="6"></td>
  </tr>
  <tr>
      <td colspan="6"><div align="center"><strong><font style="font-size: 18px">HOJA DE TRAMITE</font></strong></div></td>
  </tr>
  <tr>
      <td width="101"><div align="right"><strong>N&deg; de registro:</strong></div></td>
      <td width="96"><B><?php echo $rsPadre->field('id')?></B></td>
    <td width="59"><div align="right"><strong>Fecha:</strong></div></td>
    <td width="117"><?php echo dtos($rsPadre->field('desp_fecha'))?></td>
    <td width="51"><div align="right"><strong>Folios:</strong></div></td>
    <td width="121"><?php echo str_pad($rsPadre->field('desp_folios'),4,0,STR_PAD_LEFT)?></td>
  </tr>
  <tr>
    <td height="22"><div align="right"><strong>Remitente:</strong></div></td>
    <td colspan="5"><?php echo $rsPadre->field('desp_firma')?></td>
  </tr>
  <tr>
    <td><div align="right"><strong>Documento:</strong></div></td>
    <td colspan="5"><?php echo $rsPadre->field('tiex_abreviado').' '.$rsPadre->field('num_documento')?></td>
  </tr>
  <tr>
    <td valign="top"><div align="right"><strong>Asunto:</strong></div></td>
    <td colspan="5"><?php echo $rsPadre->field('desp_asunto')?></td>
  </tr>
</tbody></table>
&nbsp;
<table border="1" cellpadding="0" cellspacing="0" height="160" width="586">
  <tbody><tr>
    <td colspan="5"><div align="center">DEL REMITENTE </div></td>
  </tr>
  <tr>
    <td width="63"><div align="center">De</div></td>
    <td width="83"><div align="center">Pase a</div></td>
    <td width="72"><div align="center">Folios</div></td>
    <td width="243"><div align="center">Proveido</div></td>
    <td width="91"><div align="center">Firma</div></td>
  </tr>
  <tr>
    <td height="20"><?php echo $rs->field('depe_nombrecorto_origen')?></td>
    <td><?php echo $rs->field('depe_nombrecorto_destino')?></td>
    <td>&nbsp;</td>
    <td><?php echo $rs->field('depe_proveido')?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="21">&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="21">&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="21">&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="21">&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="21">&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="21">&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</tbody></table>
<br clear="ALL"><br><p class="breakhere"></p>
</body></html>
<?php
$conn->close();
