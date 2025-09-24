<?
require_once("../../../../library/library.php");
require_once("../../../../modulos/gestdoc/registroDespacho_class.php");
require_once("../../../../library/ajax/xajax.inc.php");

$conn = new db();
$conn->open();

    
$xajax = new xajax();
$xajax->setCharEncoding('iso-8859-1');
$xajax->registerFunction("buscar");
$xajax->registerFunction("elegir");

function elegir($desp_id)
{
    $objResponse = new xajaxResponse();
    $text=new despacho_SQLlista();
    $text->whereID($desp_id);
    $text->setDatos();
    $contenido=$text->field('desp_contenido');

    $objResponse->script("oEditor.FCKUndo.SaveUndoStep();
                            FCK.SetData( '$contenido' ) ;
                            window.parent.Cancel( true ) ;
                            oEditor.focus();
                            ");
    
    return $objResponse;
}

function buscar($cadena,$tiex_id,$nbusc_depe_id)
{
    global $conn;
    
    $objResponse = new xajaxResponse();

    if(strlen($cadena)>0 ){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias

	$sql=new despacho_SQLlista();
        $sql->whereDepeID($nbusc_depe_id);
        $sql->whereTiExpID($tiex_id);
                        
	if(is_numeric($cadena)) //si la cadena recibida son todos digitos
            $sql->whereID($cadena);
        else
            $sql->whereDescrip($cadena);
				}
	$sql->orderDos(); //ordena por el ID mas recientemente creado
			
	$sql=$sql->getSQL();
	
	$rs = new query($conn, strtoupper(utf8_decode($sql)));

	if ($rs->numrows()>0) {
                $rs->getrow();
                $otable = new  Table("BUSQUEDAS EN ".$rs->field('tiex_descripcion').' DE '.$rs->field('depe_nombre'),"100%",6);
                $rs->skiprow(0);
                $otable->addColumnHeader("&nbsp;");
                $otable->addColumnHeader(NAME_EXPEDIENTE,false,"5%");
                $otable->addColumnHeader("Fecha",false,"5%"); 
                $otable->addColumnHeader("TExp",false,"4%"); 
                $otable->addColumnHeader("N&uacute;mero",false,"30%"); 
                $otable->addColumnHeader("Asunto",false,"46%"); 
                $otable->addRow(); // adiciona la linea (TR)
                while ($rs->getrow()) {

                        $id = $rs->field("id"); // captura la clave primaria del recordsource
                        $otable->addData("<input type=\"button\" class=\"botonAgg\" value=\"Elegir\" onClick=\"xajax_elegir('$id');\">");

                        //si es el mismo usuario que lo ha creado
                        if(getSession("sis_userid")==$rs->field("usua_id") || getSession("sis_userid")==$rs->field("usua_idfirma")){
                            if($rs->field("desp_procesador")==0){
                               $otable->addData(addLink($id,"registroDespacho_edicionSinFirma.php?id=$id&","Click aqu&iacute; para consultar o editar el registro"));    
                            }else{
                                $otable->addData(addLink($id,"registroDespacho_edicionConFirma.php?id=$id&","Click aqu&iacute; para consultar o editar el registro"));
                            }
                        }else{
                            $otable->addData($id);
                        }
                        $otable->addData(dtos($rs->field("desp_fecha")));
                        $otable->addData($rs->field("tiex_abreviado"));

                        if($rs->field('desp_procesador')==1){
                            $otable->addData(addLink($rs->field("num_documento"),"javascript:imprimir('$id')","Click aqu&iacute; para Ver Documento","controle"));
                        }else{        
                            $otable->addData($rs->field("num_documento"));
                        }

                        $depeid=getSession("sis_depeid");
                        $otable->addData(substr($rs->field("desp_asunto"),0,40)."...");
                        $otable->addData($rs->field("desp_adjuntados_exp"));

                        $otable->addRow();
                }
                $contenido_respuesta.=$otable->writeHTML();
                $contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";
	} else {
            $otable = new  Table("","100%",6);
            $otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); 
            $otable->addRow();
            $contenido_respuesta=$otable->writeHTML();
	}
    $objResponse->addAssign('divResultado','innerHTML', utf8_encode($contenido_respuesta));
    return $objResponse;
}

        
$xajax->processRequests();

?>    
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<!--
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Hidden Field dialog window.
-->
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title>Hidden Field Properties</title>
        <link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta content="noindex, nofollow" name="robots" />
	<script src="common/fck_dialog_common.js" type="text/javascript"></script>
        <script language="JavaScript" src="../../../../library/js/libjsgen.js"></script>
	<script type="text/javascript">

var oEditor = window.parent.InnerDialogLoaded() ;
var FCK = oEditor.FCK ;

// Gets the document DOM
var oDOM = FCK.EditorDocument ;

// Get the selected flash embed (if available).
var oFakeImage = FCK.Selection.GetSelectedElement() ;
var oActiveEl ;

if ( oFakeImage )
{
	if ( oFakeImage.tagName == 'IMG' && oFakeImage.getAttribute('_fckinputhidden') )
		oActiveEl = FCK.GetRealElement( oFakeImage ) ;
	else
		oFakeImage = null ;
}

window.onload = function()
{
	// First of all, translate the dialog box texts
	oEditor.FCKLanguageManager.TranslatePage(document) ;
        document.frm.Sbusc_cadena.focus();
	//window.parent.SetOkButton( true ) ;
}


function Ok()
{
        //xajax_buscar(parent.opener.parent.parent.content.document.frm.tr_tiex_id.value);
}

function AbreVentana(sURL){
        var w=720, h=600;
        venrepo=window.open(sURL,'rptSalida', "status=yes,resizable=yes,toolbar=no,scrollbars=yes,top=50,left=150,width=" + w + ",height=" + h, 1 );
        venrepo.focus();
}

function imprimir(id) {
    AbreVentana('../../../../modulos/gestdoc/rptDocumento.php?id=' + id);
}        

	</script>
        <?
        $xajax->printJavascript(PATH_INC.'ajax/');
        ?>
</head>

<body style="overflow: hidden" scroll="yes">
	<table width="100%">
		<tr>
						<td>
                                                    <?
                                                $form = new Form("frm");
                                                $form->setMethod("POST");
                                                $form->setTarget("content");
                                                $form->setWidth("100%");
                                                $form->setLabelWidth("20%");
                                                $form->setDataWidth("80%");                                                    
                                                $form->addField("Exp/N&uacute;m.".NAME_EXPEDIENTE."/Asunto: ",textField("Cadena de B&uacute;squeda","Sbusc_cadena",'',30,30)."&nbsp;<input type=\"button\" onClick=\"xajax_buscar(document.frm.Sbusc_cadena.value,parent.opener.parent.parent.content.document.frm.tr_tiex_id.value,parent.opener.parent.parent.content.document.frm.nr_depe_id.value)\" value=\"Buscar\">");
                                                $form->addHtml("<tr><td colspan=2><div id='divResultado'>\n");
                                                $form->addHtml("</td></tr></div>");
                                                echo  $form->writeHTML();                                                    
                                                    ?>
						</td>
		</tr>
	</table>
</body>
<?
    $conn->close();
?>
</html>
