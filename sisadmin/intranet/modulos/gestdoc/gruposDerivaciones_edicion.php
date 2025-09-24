<?php
/* formulario de ingreso y modificación */
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");

/* verificación del nível de usuario */
verificaUsuario(1);

/* Cargo mi clase Base */
include("gruposDerivaciones_class.php");
include("../catalogos/catalogosDependencias_class.php");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

/*recibo los parametro de la URL*/
$param= new manUrlv1();

$id = getParam("id"); // captura la variable que viene del objeto lista

$myClass = new clsGruposDerivaciones($id,'Grupos de Derivaciones');

if (strlen($id)>0) { // edición
    $myClass->setDatos();
    if($myClass->existeDatos()){
        $bd_grde_id= $myClass->field('cod_gd');
        $bd_grde_descripcion= $myClass->field('grde_descripcion');
        $bd_grde_tipo = $myClass->field('grde_tipo');
        $bd_grde_grupo = $myClass->field('grde_grupo');
        $bd_grde_estado = $myClass->field('grde_estado');
        $bd_usua_id = $myClass->field('usua_id');
        $username=$myClass->field("username");
        $bd_grde_fregistro=$myClass->field("grde_fregistro");
        $usernameactual=$myClass->field("usernameactual");
        $bd_grde_fregistroactual=$myClass->field("grde_actualfecha");        
        
    }
}else{
    $bd_grde_tipo=1;
}

?>
<html>
<head>
	<title><?php echo $myClass->getTitle()?>-Edici&oacute;n</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
	<script language="JavaScript" src="<?php echo PATH_INC?>js/focus.js"></script>
	<script language="JavaScript" src="<?php echo PATH_INC?>js/libjsgen.js"></script>
        
        <script type="text/javascript" src="../../library/jquery/jquery-1.11.3.min.js"></script>            
        <link rel="stylesheet" href="../../library/select2/dist/css/select2.css">
        <script src="../../library/select2/dist/js/select2.js" type="text/javascript"></script>                
        
	<script language='JavaScript'>
	function salvar(idObj) {
		if (ObligaCampos(frm)){
			ocultarObj(idObj,10)
			parent.content.document.frm.target = "controle";
			parent.content.document.frm.action = "<?php echo $myClass->getNameFile()."?control=".base64_encode(1)."&".$param->buildPars(false)?>";
			parent.content.document.frm.submit();
		}
	}
	/*
		se invoca desde la funcion obligacampos (libjsgen.js)
		en esta función se puede personalizar la validaci�n del formulario
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

	/*
		función que define el foco inicial en el formulario
	*/
	function inicializa() {
		document.frm.Sr_grde_descripcion.focus();
	}
	</script>
        
        <style>
            .select2-rendered__match {
                text-decoration : underline;
             }
        </style>
        
	<?php
        verif_framework(); 
        ?>
</head>

<body class="contentBODY" onLoad="inicializa()">
<?php
pageTitle("Edici&oacute;n de ".$myClass->getTitle());


/* botones */
$button = new Button;
$button->addItem(" Guardar ","javascript:salvar('Guardar')","content",2);
$button->addItem(" Regresar ","gruposDerivaciones_buscar.php".$param->buildPars(true),"content");

echo $button->writeHTML();

/* Control de fichas */
$abas = new Abas();
$abas->addItem("General",true);
echo $abas->writeHTML();
echo "<br>";

/* Formulario */
$form = new Form("frm", "", "POST", "controle", "100%",true);
$form->setLabelWidth("20%");
$form->setDataWidth("80%");
$form->addHidden("f_id",$id); // clave primaria
if($id){
    $form->addField("C&oacute;digo: ",$bd_grde_id);
}

$form->addField("Descripci&oacute;n: ",textField("Descripci&oacute;n","Sr_grde_descripcion",$bd_grde_descripcion,80,80));

$sql = array(1 => "Agrupar Dependencias",
             2 => "Agrupar Empleados");

$sqlDependencia=new dependenciasBuscarTodos_SQLlista($bd_grde_grupo);
$sqlDependencia=$sqlDependencia->getSQL();    

$form->addField(listboxField("Tipo_registro", $sql, "Sr_grde_tipo",$bd_grde_tipo).':',listboxField("Agrupar","$sqlDependencia","Srxgrupos[]","","seleccione Dependencia","","","class=\"my_select_box\" multiple \""));

//solo si es edicion se agrega los datos de auditoria
if(strlen($id)) {
        $form->addField("Activo: ",checkboxField("Activo","hx_grde_estado",1,$bd_grde_estado==1));               
	$form->addBreak("<b>Control</b>");
        $form->addField("Creado por: ",$username.' '.substr($bd_grde_fregistro,0,19).' / '." Actualizado por: ".$usernameactual.'/'.substr($bd_grde_fregistroactual,0,19));
}

echo $form->writeHTML();
    ?>
    
    <script>
//        
//            $('.my_select_box').select2({
//                placeholder: 'Seleccione un elemento de la lista',
//                allowClear: true,
//                width: '90%',
//            });        
        
        var query = {};
        var $element = $('.my_select_box');

        function markMatch (text, term) {
          // Find where the match is
          var match = text.toUpperCase().indexOf(term.toUpperCase());

          var $result = $('<span></span>');

          // If there is no match, move on
          if (match < 0) {
            return $result.text(text);
          }

          // Put in whatever text is before the match
          $result.text(text.substring(0, match));

          // Mark the match
          var $match = $('<span class="select2-rendered__match"></span>');
          $match.text(text.substring(match, match + term.length));

          // Append the matching text
          $result.append($match);

          // Put in whatever is after the match
          $result.append(text.substring(match + term.length));

          return $result;
        }
        
        $element.select2({
            placeholder: "Seleccione un elemento de la lista",
            allowClear: true,
            width: '90%',
            ajax: {     
                      url: function () {
                          return getURL();
                      },
                      dataType: 'json',
                      delay: 250,
                        data: function (params) {
                              return {
                                q: params.term // search term
                              };
                            },                      
                      processResults: function (data) {
                        return {
                          results: data
                        };
                      },
                      cache: true
                   },      
            templateResult: function (item) {
              // No need to template the searching text
              if (item.loading) {
                return item.text;
              }

              var term = query.term || '';
              var $result = markMatch(item.text, term);

              return $result;
            },
            language: {
              searching: function (params) {
                // Intercept the query as it is happening
                query = params;

                // Change this to be appropriate for your application
                return 'Searching…';
              }
            }
          });
    
          function getURL() {
              if($("#Tipo_registro").val()==1){ //dependencias
                return '../catalogos/jswDependenciasAjax.php';
              }else{
                return '../catalogos/jswDependenciasEmpleadosAjax.php';
              }
          }

    
        

        <?php
        if($bd_grde_grupo!=""){
            $bd_grde_grupo=str_replace(",","','",$bd_grde_grupo);
            echo "$('.my_select_box').val(['$bd_grde_grupo']).trigger('change');";
        }
        ?>
        
    </script>
</body>
</html>

<?php
/* cierro la conexión a la BD */
$conn->close();
