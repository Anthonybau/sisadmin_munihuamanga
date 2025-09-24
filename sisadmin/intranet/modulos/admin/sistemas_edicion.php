<?
/* formulario de ingreso y modificaci�n */
include("../../library/library.php");

/* verificaci�n del n�vel de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("sistemas_class.php"); 

/* establecer conexi�n con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$myClass = new clsSistemas($id,'Sistemas Activos');

?>
<html>
<head>
	<title><?=$myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
	<script language="JavaScript" src="<?=PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?=PATH_INC?>js/libjsgen.js"></script>
        <script type="text/javascript" src="<?=PATH_INC?>jquery/jquerypack.js"></script>
        <script type="text/javascript" src="<?=PATH_INC?>tablesorter/jquery.tablesorter.js"></script>	
	<script language='JavaScript'>
            function salvar(idObj) {
                    if (ObligaCampos(frm)){
                            parent.content.document.frm.target = "controle";
                            parent.content.document.frm.action = "<?=$myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
                            parent.content.document.frm.submit();
                    }
            }
            /*
                    se invoca desde la funcion obligacampos (libjsgen.js)
                    en esta funci�n se puede personalizar la validaci�n del formulario
                    y se ejecuta al momento de gurdar los datos
            */
            function mivalidacion(){
                var sError="Mensajes del sistema: "+"\n\n";
                var nErrTot=0;

                    if (nErrTot>0){
                            alert(sError)
                            eval(foco)
                            return false
                    }else
                            return true

            }

            function openList(key) {
                    var oKey = parent.content.document.getElementById(key);
                    var icone = parent.content.document.getElementById('fold_'+key);
                    if (oKey.style.visibility == "hidden"){
                            oKey.style.visibility = "visible";
                            oKey.style.display = "block";
                            icone.innerHTML = "&nbsp;-&nbsp;";

                    } else {
                            oKey.style.visibility = "hidden";
                            oKey.style.display = "none";
                            icone.innerHTML = "&nbsp;+&nbsp;";
                    }
            }


	/*
		funci�n que define el foco inicial en el formulario
	*/
	function inicializa() {
		/*document.frm.nr_sumi_valor.focus();*/
	}
	</script>
        <script type="text/javascript" src="<?=PATH_INC?>js/jquerytablas.js"></script>        
	<? verif_framework(); ?>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?
pageTitle($myClass->getTitle(),"");

/* botones */
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",3);
echo $button->writeHTML();


/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%");
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$sistemas=new clsSistemas_SQLlista();
$sistemas->whereNOTAdmin();
$sistemas->orderUno();
$sql=$sistemas->getSQL();

$rs = new query($conn, strtoupper($sql));
if ($rs->numrows()>0) {
    $otable = new TableSimple("","100%",8,'tLista'); 
    $otable->addColumnHeader("");
    $otable->addColumnHeader("");
    $otable->addColumnHeader("C&oacute;digo","5%", "L"); 
    $otable->addColumnHeader("Sistema","95%", "L"); 
    $otable->addRowHead();
    
        $rs->getrow();
	$i=-1;
	do{
		$id=$rs->field("sist_id");
		// definici?n de lista encadenas
		$hay_encadenados=false;
		if($rs->field("smop_id")){
			$hay_encadenados=true;
                        $table_encadeada = new TableSimple("","100%",8,'tLista2'); 
                        $table_encadeada->addColumnHeader("");
			$table_encadeada->addColumnHeader("C&oacute;digo",false,"10%","L");
                        $table_encadeada->addColumnHeader("M&oacute;dulo",false,"10%","L");
			$table_encadeada->addColumnHeader("Descripci&oacute;n",false,"30%","C");		
			$table_encadeada->addColumnHeader("P&aacute;gina",false,"50%", "C"); 
			$table_encadeada->addRow();
			do{
                                $smop_id=$rs->field("smop_id");
                                if($rs->field("smop_activo")==1){
                                    $table_encadeada->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"2_$smop_id\" checked>");					
                                }else{
                                    $table_encadeada->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"2_$smop_id\" >");					
                                }
				$table_encadeada->addData($smop_id);
                                $table_encadeada->addData($rs->field("modulo"));
				$table_encadeada->addData($rs->field("smop_descripcion"));
                                $table_encadeada->addData($rs->field("smop_page"));
				$table_encadeada->addRow();
				$i++;
			}
			while ($rs->getrow() && $rs->field("sist_id")==$id) ;
			$rs->skiprow($i);
			$rs->getrow();
		}
		else $i++;
		
		if($rs->field("sist_activo")==1){
                    $otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"1_$id\" checked>");
                }else{
                    $otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"1_$id\" >");					
                }

		
		/*si hay lista encadenados*/
		if ($hay_encadenados) {
			$otable->addData("<span id='fold_$id' style='cursor: pointer' onClick=\"javascript:openList('$id')\">&nbsp;+&nbsp;</span>","C");
		} else 	$otable->addData("&nbsp;");

	
                $otable->addData($id);
                $otable->addData($rs->field("sist_descripcion"));

		if($id)
                    $otable->addRow();
		else
                    $otable->row = "";
		
		/*si hay lista encadenados*/
		if ($hay_encadenados) {
			$otable->addBreak("<div id=\"$id\" style='visibility: hidden; display: none; margin-left: 60px'>".$table_encadeada->writeHTML()."</div>", false);
		}
		
        }
        while ($rs->getrow());    
        $form->addHtml($otable->writeHTML());
        $form->addHtml("<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>");
}
echo $form->writeHTML();
?>
</body>
<script language='JavaScript'>

    var ss = $("#tLista tbody input[@type=checkbox]");
    ss.each(function(){
        $(this) // añadimos o eliminamos una clase en el registro que lo contiene 
            .parents("tr")[ this.checked ? "addClass" : "removeClass" ]("selected"); 
	});                   
		
</script>    
</html>

<?
/* cierro la conexi�n a la BD */
$conn->close();