<?

include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
include("Persona_class.php");
include("PersonaEstudios_class.php");
include("PersonaCapacitacion_class.php");

include("../siscopp/siscoppAperturasAcumulados_clases.php");
include("../catalogos/AFP_class.php");
include("../catalogos/RegimenLaboral_class.php");
include("../catalogos/RegimenPensionario_class.php");
include("../catalogos/catalogosTabla_class.php");
include("../catalogos/CategoriaRemunerativa_class.php");
include("../catalogos/catalogosDependencias_class.php");
include("../catalogos/CargoClasificado_class.php");

require_once('../../library/tcpdf/tcpdf.php');

class MYPDF extends TCPDF {

    //Page header
    public function Header() {
        // Set font
        
        $this->SetFont('helvetica', 'B', 8);
        // Title
        $this->Cell(0, 5, '', 0, 1, 'C', 0, '', false);
        $this->Cell(165, 10, SIS_EMPRESA, 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(20, 10, getSession("sis_username"), 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(0, 8, '', 0, 1, 'C', 0, '', false);
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(175, 0, 'FICHA DE DATOS ESCALAFONARIO', 1, false, 'C', 0, '', 1);
        $this->Cell(0, 8, '', 0, 1, 'C', 0, '', false);

        
    }

    // Page footer
    public function Footer() {

        $this->SetY(-10);
        $this->SetFont('helvetica', 'N', 6);
        // Page number
        $this->Cell(0, 10, 'Fecha/Hora de Impresión: '.date("d/m/Y").' '.date("H:i:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
    


}


$conn = new db();
$conn->open();

$id = getParam("id");

/* obtengo los datos del documento */
$persona= new clsPersona_SQLlista(2);
$persona->whereID($id);

$sql = $persona->getSQL();

/* creo el recordset */
$rsDoc = new query($conn, $sql);

if ($rsDoc->numrows() == 0) {
    alert("No existen registros para procesar...!");
}

$rsDoc->getrow();
$bd_pers_foto= $rsDoc->field("pers_foto");
$bd_pers_foto=iif($bd_pers_foto,"==","","../../img/standar_foto.jpg",PUBLICUPLOAD."escalafon/$bd_pers_foto");
        
/*CREO LA TABLA*/
/* Formulario */
$contenido="
<style>
table, td, th {
    border: 1px solid black;
    height: 20px;
    vertical-align: middle;
}

table {
    border-collapse: collapse;
    width: 100%;
}

</style>    
<table>
                <tr>
                    <td height=\"50\" colspan=\"3\" align=\"center\" valign=\"middle\"><BR><BR><b>DATOS PERSONALES</b></td>
                </tr>
                <tr>
                    <td height=\"30\" align=\"right\">Apellido Paterno:</td>
                    <td height=\"28\" ><b>".$rsDoc->field("pers_apellpaterno")."</b></td>
                    <td height=\"28\" rowspan=\"9\" align=\"center\" valign=\"middle\"><img src=\"$bd_pers_foto\" id=\"DivImage\" width=\"160px\" height=\"180px\"  style=\"border-color:#7F9DB9\"></td>
                </tr>
                <tr>
                    <td align=\"right\">Materno:</td>
                    <td><b>".$rsDoc->field("pers_apellmaterno")."</b></td>
                </tr>
                <tr>
                    <td align=\"right\">Nombres:</td>
                    <td><b>".$rsDoc->field("pers_nombres")."</b></td>
                </tr>
                <tr>
                    <td align=\"right\">Edad:</td>
                    <td><b>".calcTiempo(stod($rsDoc->field("pers_nacefecha")))."</b></td>
                </tr>    
                <tr>
                    <td align=\"right\">Sexo:</td>
                    <td><b>".iif($rsDoc->field("pers_sexo"),"==","M","Masculino","Femenino")."</b></td>
                </tr>    
                <tr>
                    <td align=\"right\">DNI:</td>
                    <td><b>".$rsDoc->field("pers_dni")."</b></td>
                </tr>                
                <tr>
                    <td align=\"right\">RUC:</td>
                    <td><b>".$rsDoc->field("pers_ruc")."</b></td>
                </tr>                
                <tr>
                    <td align=\"right\">Brevete:</td>
                    <td><b>".$rsDoc->field("pers_brevete")."</b></td>
                </tr>                
                <tr>
                    <td align=\"right\">C&oacute;d.ESSALUD:</td>
                    <td><b>".$rsDoc->field("pers_codessalud")."</b></td>
                </tr>                                
                <tr>
                    <td align=\"right\">Dirección:</td>
                    <td colspan=\"2\"><b>".$rsDoc->field("pers_direccion")."</b></td>
                </tr>
                <tr>
                    <td align=\"right\">Lugar:</td>
                    <td colspan=\"2\"><b>".$rsDoc->field("distrito")."</b></td>
                </tr>
                <tr>
                    <td align=\"right\">Teléfono Fijo:</td>
                    <td colspan=\"2\"><b>".$rsDoc->field("pers_telefono")."</b></td>
                </tr>                
                <tr>
                    <td align=\"right\">Teléfono Movil:</td>
                    <td colspan=\"2\"><b>".$rsDoc->field("pers_movil")."</b></td>
                </tr>
                <tr>
                    <td align=\"right\">eMail:</td>
                    <td colspan=\"2\"><b>".$rsDoc->field("pers_email")."</b></td>
                </tr>
                
                <tr>
                    <td height=\"50\" colspan=\"3\" align=\"center\"><BR><BR><b>DATOS LABORALES</b></td>
                </tr>
                
                <tr>
                    <td align=\"right\">Régimen Laboral:</td>
                    <td colspan=\"2\"><b>".$rsDoc->field("regimen_laboral")."</b></td>
                </tr>
                <tr>
                    <td align=\"right\">Condición:</td>
                    <td colspan=\"2\"><b>".$rsDoc->field("sit_laboral")."</b></td>
                </tr>
                ";

        $perfil=new clsTabla_SQLlista();    
        $perfil->whereID($rsDoc->field("tabl_idsitlaboral"));//condicion laboral
        $perfil->setDatos();        
        $arPerfil= explode(",",$perfil->field('tabl_descripaux'));
        
        if ($rsDoc->field("tabl_idsitlaboral")==41) { //PRACTICANTE
            $tablaClasificacion=new clsTabla_SQLlista();
            $tablaClasificacion->whereID($rsDoc->field("tabl_clasificacion_practicante"));
            $tablaClasificacion->setDatos();            
            $contenido.="<tr>
                        <td align=\"right\">Tipo de Practicas:</td>
                        <td colspan=\"2\"><b>".$tablaClasificacion->field('tabl_descripcion')."</b></td>
                        </tr>";
        }
         
        if (in_array("CR", $arPerfil)) { //CATEGORIA REMUNERATIVA        
                $sqlCategoria=new clsCategoriaRemunerativa_SQLlista();
                $sqlCategoria->whereID($rsDoc->field("care_id"));
                $sqlCategoria->setDatos();
                $contenido.="<tr>
                        <td align=\"right\">Categoría:</td>
                        <td colspan=\"2\"><b>".$sqlCategoria->field('cate_descripcion')."</b></td>
                        </tr>";
        }
        
        if (in_array("NR", $arPerfil)) {//NIVEL REMUNERATIVO
                $tabla=new clsTabla_SQLlista();
                $tabla->whereID($rsDoc->field("tabl_nivel_remunerativo"));
                $tabla->setDatos();
                $contenido.="<tr>
                        <td align=\"right\">Nivel Remunerativo:</td>
                        <td colspan=\"2\"><b>".$tabla->field('tabl_descripcion')."</b></td>
                        </tr>";                
        }
        
        if (in_array("CP", $arPerfil)) { //CADENA PRESUPUESTAL
                $sqlCadena=new clsComponentes_SQLlista();
                $sqlCadena->whereID($rsDoc->field("comp_id"));
                $sqlCadena->setDatos();
                $contenido.="<tr>
                        <td align=\"right\">Cadena Presupuestal:</td>
                        <td colspan=\"2\"><b>".$sqlCadena->field('cadena_planilla')."</b></td>
                        </tr>";
        }

        $sqlDependencia=new dependencia_SQLlista();
        $sqlDependencia->whereID($rsDoc->field("depe_id"));
        $sqlDependencia->setDatos();
        $contenido.="<tr>
                    <td align=\"right\">Dependencia:</td>
                    <td colspan=\"2\"><b>".$sqlDependencia->field('depe_nombre')."</b></td>
                    </tr>";
        
        if (in_array("CL", $arPerfil)) { //CLASIFICACION            
                $tabla=new clsTabla_SQLlista();
                $tabla->whereID($rsDoc->field("tabl_clasificacion"));
                $tabla->setDatos();
                $contenido.="<tr>
                    <td align=\"right\">Clasificación:</td>
                    <td colspan=\"2\"><b>".$tabla->field('tabl_descripcion')."</b></td>
                    </tr>";                
        }
        
        if (in_array("CO", $arPerfil)) { //CONTRATO
            $contenido.="<tr>
                    <td align=\"right\">Contrato:</td>
                    <td colspan=\"2\"><b>".$rsDoc->field("pers_documento")."</b></td>
                    </tr>";                

        }

        if (in_array("RE", $arPerfil)) { //RESOLUCION
            $contenido.="<tr>
                    <td align=\"right\">Resolución:</td>
                    <td colspan=\"2\"><b>".$rsDoc->field("pers_documento")."</b></td>
                    </tr>";                
        }

        if (in_array("DO", $arPerfil)) { //DOCUMENTO
            $contenido.="<tr>
                    <td align=\"right\">Documento:</td>
                    <td colspan=\"2\"><b>".$rsDoc->field("pers_documento")."</b></td>
                    </tr>";            
        }
        
        if (in_array("FI", $arPerfil)) { //FECHA DE INGRESO
            $contenido.="<tr>
                    <td align=\"right\">Fecha de Ingreso:</td>
                    <td colspan=\"2\"><b>".dtos($rsDoc->field("pers_fechaingreso"))."</b></td>
                    </tr>";            
        }
        
        if (in_array("FT", $arPerfil)) { //FECHA DE TERMINO
            $contenido.="<tr>
                    <td align=\"right\">Fecha de Termino:</td>
                    <td colspan=\"2\"><b>".dtos($rsDoc->field("pers_fechacese"))."</b></td>
                    </tr>";
        }
        
        if (in_array("FC", $arPerfil)) { //FECHA DE CESE
            $contenido.="<tr>
                    <td align=\"right\">Fecha Cese:</td>
                    <td colspan=\"2\"><b>".dtos($rsDoc->field("pers_fechacese"))."</b></td>
                    </tr>";            
        }

        if (in_array("CC", $arPerfil)) { //CARGO CLASIFICADO
                $cargo=new clsCargoClasificado_SQLlista();
                $cargo->whereID($rsDoc->field("cacl_id"));
                $cargo->setDatos();
                $contenido.="<tr>
                    <td align=\"right\">Cargo Clasificado:</td>
                    <td colspan=\"2\"><b>".$cargo->field('cacl_descripcion')."</b></td>
                    </tr>";
        }
        
        if (in_array("CF", $arPerfil)) { //CARGO FUNCIONAL
            $contenido.="<tr>
                    <td align=\"right\">Cargo Funcional:</td>
                    <td colspan=\"2\"><b>".$rsDoc->field("pers_cargofuncional")."</b></td>
                    </tr>";                
        }        


        $profesion= getDbValue("SELECT left(func_get_estudios,length(func_get_estudios)-1) FROM personal.func_get_estudios($id,1,0)");
        if($profesion){
            $contenido.="<tr>
                    <td align=\"right\">Profesión:</td>
                    <td colspan=\"2\"><b>$profesion</b></td>
                    </tr>";                
        }
    
        if (in_array("RP", $arPerfil)) { //REGIMEN PENSIONARIO
                $sqlRegPensionario= new clsRegimenPensionario_SQLlista();
                $sqlRegPensionario->whereID($rsDoc->field("repe_id"));
                $sqlRegPensionario->setDatos();
                $contenido.="<tr>
                    <td align=\"right\">Régimen Pensionario:</td>
                    <td colspan=\"2\"><b>".$sqlRegPensionario->field('repe_descripcion')."</b></td>
                    </tr>";
                
                
                if($rsDoc->field("repe_id")==1){
                    $contenido.="<tr>
                    <td align=\"right\">AFP:</td>
                    <td colspan=\"2\"><b>".$rsDoc->field("afp_nombre")."</b></td>
                    </tr>";
                    
                    $tabla=new clsTabla_SQLlista();
                    $tabla->whereID($rsDoc->field("tabl_tipocomision"));
                    $tabla->setDatos();
                    $contenido.="<tr>
                                    <td align=\"right\">Tipo Comisión:</td>
                                    <td colspan=\"2\"><b>".$tabla->field('tabl_descripcion')."</b></td>
                                </tr>";

                    $contenido.="<tr>
                                    <td align=\"right\">Código Unico SPP:</td>
                                    <td colspan=\"2\"><b>".$rsDoc->field("pers_afpcus")."</b></td>
                                </tr>";
                    
                    $contenido.="<tr>
                                    <td align=\"right\">Fecha de Afiliación:</td>
                                    <td colspan=\"2\"><b>".dtos($rsDoc->field("pers_afpafiliacion"))."</b></td>
                                </tr>";
                }
        }
        
        if (in_array("NH", $arPerfil)) { //NUMERO DE HIJOS
                $contenido.="<tr>
                    <td align=\"right\">Num. Hijos:</td>
                    <td colspan=\"2\"><b>".$rsDoc->field("pers_cant_hijos")."</b></td>
                    </tr>";            
        }
        
        if (in_array("BA", $arPerfil)) { //BANCO
                $tabla=new clsTabla_SQLlista();
                $tabla->whereID($rsDoc->field("tabl_bancoid"));
                $tabla->setDatos();
                
                $contenido.="<tr>
                    <td align=\"right\">Banco:</td>
                    <td colspan=\"2\"><b>".$tabla->field('tabl_descripcion')."</b></td>
                    </tr>";
                
                $contenido.="<tr>
                    <td align=\"right\">Cuenta Depósito:</td>
                    <td colspan=\"2\"><b>".$rsDoc->field("pers_cuentadeposito")."</b></td>
                    </tr>";
        }
        
        $contenido.="<tr>
                    <td align=\"right\">Remuneración Básica:</td>
                    <td colspan=\"2\"><b>".number_format($rsDoc->field("remuneracion"),2,'.',',')."</b></td>
                    </tr>";        
        if($rsDoc->field("pers_activo")==9){//BAJA
            $contenido.="<tr>
                    <td align=\"right\">Estado:</td>
                    <td colspan=\"2\"><b>BAJA / FECHA: ".dtos($rsDoc->field("pers_fechacese"))." / DOCUMENTO: ".$rsDoc->field("pers_documento_baja")."</b></td>
                    </tr>";        
        }
        
$contenido.="</table>";



/* Imprimo Documento */
//define ('K_PATH_IMAGES', '../../img/');
// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);



// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT+5, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// add a page
$pdf->AddPage();

// set font
$pdf->SetFont('helvetica', '', 10);

// create some HTML content

$html = $contenido;

// output the HTML content
$pdf->writeHTML($html, true, 0, true, true);


//ESTUDIOS
$sql=new clsPersonaEstudios_SQLlista();
$sql->wherePadreID($id);
//$sql->wherePeriodo($fechIni,$fechFin);
$sql->orderDos();
$sql=$sql->getSQL();
$rs = new query($conn, $sql);        
if($rs->numrows()>0){

    $pdf->AddPage();

    $titulo="<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
                    <tr>
                        <td height=\"20\" align=\"center\" valign=\"middle\">ESTUDIOS</td>
                    </tr>
             </table>";
    // set font
    $pdf->SetFont('helvetica', 'B', 12);
    // output the HTML content
    $pdf->writeHTML($titulo, true, 0, true, true);
    $contenido="<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
            <tr>
                <td height=\"10\" align=\"center\">GRADO DE INSTRUCCION</td>
                <td height=\"10\" align=\"center\">CENTO DE ESTUDIOS</td>
                <td height=\"10\" align=\"center\">SITUACION ACADEMICA</td>
                <td height=\"10\" align=\"center\">FECHA DE EGRESADO</td>
                <td height=\"10\" align=\"center\">GRADO O TITULO</td>
                <td height=\"10\" align=\"center\">FECHA DE GRADO/TIT</td>
                <td height=\"10\" align=\"center\">N° DE TITULO</td>
                <td height=\"10\" align=\"center\">ESPECIALIDAD</td>
                <td height=\"10\" align=\"center\">N° DE COLEGIATURA</td>
            </tr>
                    ";


            while ($rs->getrow()) {    
                $contenido.="<tr>
                            <td>".$rs->field("grado_instruccion")."</td>
                            <td>".$rs->field("centro_estudio")."</td>
                            <td>".$rs->field("situacion_academica")."</td>
                            <td align=\"center\">".dtos($rs->field("pees_fegresado"))."</td>
                            <td>".$rs->field("grado_estudio")."</td>
                            <td align=\"center\">".dtos($rs->field("pees_fgradotitulo"))."</td>
                            <td align=\"center\">".$rs->field("pees_ntitulo")."</td>
                            <td>".$rs->field("especialidad")."</td>
                            <td align=\"center\">".$rs->field("pees_ncolegiatura")."</td>
                            </tr>";

            }

    $contenido.="</table>";

    $pdf->SetFont('helvetica', '', 8);
    $pdf->writeHTML($contenido, true, 0, true, true);
}


//CAPACITACIONES
$fechIni=date('d').'/'.date('m').'/'.(date('Y')-5);
$fechFin=date('d/m/Y');


$sql=new clsPersonaCapacitacion_SQLlista();
$sql->wherePadreID($id);
$sql->wherePeriodo($fechIni,$fechFin);
$sql->orderDos();
$sql=$sql->getSQL();
$rs = new query($conn, $sql);        
if($rs->numrows()>0){

    $pdf->AddPage();

    $titulo="<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
                    <tr>
                        <td height=\"20\" align=\"center\" valign=\"middle\">CAPACITACIONES DESDE: ".$fechIni.'  '."HASTA:".$fechFin."</td>
                    </tr>
             </table>";
    // set font
    $pdf->SetFont('helvetica', 'B', 12);
    // output the HTML content
    $pdf->writeHTML($titulo, true, 0, true, true);
    $contenido="<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
            <tr>
                <td height=\"10\" align=\"center\">TIPO DE EVENTO</td>
                <td height=\"10\" align=\"center\">TIPO DE PARTICIPANTE</td>
                <td height=\"10\" align=\"center\">FECHA DE DOC</td>
                <td height=\"10\" align=\"center\">INSTITUCION ORGANIZADORA</td>
                <td height=\"10\" align=\"center\">CURSO</td>
                <td height=\"10\" align=\"center\">FECHA DE INICIO/TIT</td>
                <td height=\"10\" align=\"center\">FECHA DE FINALIZACION</td>
                <td height=\"10\" align=\"center\">DURACION EN HORAS</td>
                <td height=\"10\" align=\"center\">DURACION EN DIAS</td>
            </tr>
                    ";


            while ($rs->getrow()) {    
                $contenido.="<tr>
                            <td>".$rs->field("tevento")."</td>
                            <td>".$rs->field("tparticipante")."</td>
                            <td>".dtos($rs->field("peca_fechadocu"))."</td>
                            <td align=\"center\">".$rs->field("peca_organizador")."</td>
                            <td>".$rs->field("peca_nombrecurso")."</td>
                            <td align=\"center\">".dtos($rs->field("peca_fechainicio"))."</td>
                            <td align=\"center\">".dtos($rs->field("peca_fechafin"))."</td>
                            <td>".$rs->field("peca_horas")."</td>
                            <td align=\"center\">".$rs->field("peca_dias")."</td>
                            </tr>";

            }

    $contenido.="</table>";

    $pdf->SetFont('helvetica', '', 8);
    $pdf->writeHTML($contenido, true, 0, true, true);
}

// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//$nameFile = "../../../../D/$id.pdf";
$nameFile=$_SERVER['DOCUMENT_ROOT'] .'/docs/reportes/rpt'.rand(1000,1000000).'.pdf';                            
//Close and output PDF document

$pdf->Output($nameFile, 'FI');  /* genera y veo el archivo en el navegador */


/* Fin: Imprimo Documento */

$conn->close();