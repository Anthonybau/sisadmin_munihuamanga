<?php
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);


$clear=getParam('clear');

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

// Inicio Para Ajax
include("../../library/ajax/xajax.inc.php");
$xajax = new xajax();
$xajax->registerFunction("clonarComponentes");

function clonarComponentes($peri_anno)
{
	global $conn;

	$objResponse = new xajaxResponse();
        $usua_id=getSession("sis_userid");
        $sql ="INSERT INTO siscopp.componente   
                (peri_anno,
                      capr_id,
                      prpr_id,
                      prod_id,
                      acti_id,
                      func_id,
                      divi_id,
                      subp_id,
                      comp_meta,
                      comp_descripcion,
                      comp_mnemonico,
                      comp_cadena,
                      comp_id_anterior,
                      usua_id,
                      comp_actualusua,
                      comp_actualfecha,
                      mnem_sisteso,
                      proy_sisteso)
                SELECT $peri_anno,
                      capr_id,
                      prpr_id,
                      prod_id,
                      acti_id,
                      func_id,
                      divi_id,
                      subp_id,
                      comp_meta,
                      comp_descripcion,
                      comp_mnemonico,
                      comp_cadena,
                      comp_id,
                      $usua_id,
                      $usua_id,
                      NOW(),
                      mnem_sisteso,
                      proy_sisteso
                      FROM siscopp.componente 
                      WHERE peri_anno=($peri_anno::numeric-1)";
	
	$conn->execute($sql);
	$error=$conn->error();
	if($error) 
            $objResponse->addAlert($error);				
	else {
            $objResponse->addAlert('Proceso Realizado con Exito...');
	}
    
    return $objResponse;
}

$xajax->processRequests();
?>
<html>
<head>
	<title>Clonar Componentes</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="JavaScript" src="<?=PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?=PATH_INC?>js/libjsgen.js"></script>
	
	<script language='JavaScript'>

	/*
		funci�n que define el foco inicial en el formulario
	*/
	function inicializa() {
		document.frm.nr_periodo.focus();
	}
	</script>
	<? $xajax->printJavascript(PATH_INC.'ajax/'); ?>
	<? verif_framework(); ?>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("CLONAR COMPONENTES PRESUPUSTALES");

echo "<br>";

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");

$sqlPeriodo = "SELECT peri_anno,
                    peri_anno || ' ' || COALESCE(peri_set, '') AS descripcion
                    FROM admin.periodo
                    WHERE peri_anno >
                          (
                            SELECT peri_anno
                            FROM admin.periodo
                            WHERE peri_set = '*'
                          )
                    ORDER BY 1
                    LIMIT 1";
$peri_desde=getDBValue("SELECT peri_anno
                            FROM admin.periodo
                            WHERE peri_set = '*'");
$form->addField("Clonar Del: ","$peri_desde");
$form->addField("Al: ",listboxField("Nuevo Periodo",$sqlPeriodo, "nr_periodo",""));
/* botones */
$button = new Button;
$button->setDiv(false);
$button->addItem(" Proceder ","javascript:if(confirm('Seguro de Clonar?')){xajax_clonarComponentes(document.frm.nr_periodo.value)}","content",2);

$form->addField("",$button->writeHTML());

echo $form->writeHTML();
wait('');
?>
</body>
</html>

<?php
/* cierro la conexi�n a la BD */
$conn->close();