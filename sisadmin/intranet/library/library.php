<?php
session_name("SISADMIN");
session_start();

/* Datos de conección a la BD */
if($_SERVER["DOCUMENT_ROOT"]=='/srv/www/htdocsX'){
    define("RUN_MODE","production"); /* Está corriendo en modo desarrollo */
}else{
    define("RUN_MODE","developer"); /* Está corriendo en modo producción */
}

 ini_set('display_errors', 1);
 ini_set('display_startup_errors', 1);
 //error_reporting(E_ALL);
    
 error_reporting  (E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING); //PHP 5.4
//ini_set ('display_errors', true);
ini_set ("memory_limit","128M");
date_default_timezone_set('America/Lima');

//ini_set ("max_input_vars","10000");
//echo ini_get('error_reporting');


include($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/conexion.php");
include($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/config.php");



//class PDO_insert{
//    var $table;
//    var $data;
//    
//    function __construct($table, $data) {
//        $this->table = $table;
//	$this->data = $data;		
//    }
//        
//     function getSQL() {         
//         $fields = implode(", ",array_keys($this->data));
//         //#here is my way
//         $placeholder = substr(str_repeat('?,',count(array_keys($this->data))),0,-1);
//         $sql="INSERT INTO $this->table ($fields) VALUES($placeholder)";         
//         return ($sql);
//     }
//     
//     function values(){
//         return(array_values($this->data));
//     }   
//}

class PDO_insert{
    var $table;
    var $data;
    var $sqlfunctions;
    
    function __construct($table, $data, $sqlfunctions=array()) {
        $this->table = $table;
	$this->data = $data;		
        $this->sqlfunctions = $sqlfunctions;
    }
        
     function getSQL() {         
         
        $value_columns = array_keys($this->data);
        $sqlfunc_columns = array_keys($this->sqlfunctions);
        $columns = array_merge($value_columns, $sqlfunc_columns);

        // Only $values become ':paramname' PDO parameters.
        $value_parameters = array_map(function($col) {return (':' . $col);}, $value_columns);
        // SQL functions go straight in as strings.
        $sqlfunc_parameters = array_values($this->sqlfunctions);
        $parameters = array_merge($value_parameters, $sqlfunc_parameters);

        $column_list = join(', ', $columns);
        $parameter_list = join(', ', $parameters);

         
        $sql="INSERT INTO $this->table ($column_list) VALUES ($parameter_list)";
         
         
        return ($sql);
     }
     
     function values(){
         return(array_values($this->data));
     }   
}
//include(DB_DEFAULT);
//get_magic_quotes_gpc();
/*****************************************************************************************************
 Classe para montagem de express�es SQL de atualiza��o
 O m�todo getValue deve ser adaptado conforme o banco de dados utilizado.
 No futuro esta classe ser� mais generalizada
 */
class UpdateSQL {
	var $action;
	var $table;

	var $keyField;
	var $keyValue;
	var $keyType;

	var $updateFields;
	var $updateValues;
	var $updateTypes;

	/*
		Construtor
		theAction : INSERT, UPDATE, DELETE
		theTable : nome da tabela
		*/
	function __construct($theAction="", $theTable="") {
		$this->action = strtoupper($theAction);
		$this->table = $theTable;
	}

	/*
		Define a chave
		theField : nome do campo
		theValue : valor do campo
		theType : tipo do campo (Number, String, Date)
		*/
	function setKey($theField, $theValue, $theType) {
		$this->keyField = $theField;
		$this->keyValue = $theValue;
		$this->keyType = $theType;
	}

	/*
		Adiciona um campo na express�o SQL
		theField : nome do campo
		theValue : valor do campo
		theType : tipo do campo (Number, String, Date)
		*/
	function addField($theField, $theValue, $theType) {
		$this->updateFields[] = $theField;
		$this->updateValues[] = $theValue;
		$this->updateTypes[] = $theType;
	}

	/*
		Define a a��o da express�o SQL
		theAction : INSERT, UPDATE, DELETE
		*/
	function setAction($theAction) {
		$this->action = strtoupper($theAction);
	}

	/*
		Define a tabela que vai sofrer atualiza��o
		theTable : nome da tabela
		*/
	function setTable($theTable) {
		$this->table = $theTable;
	}

	/*
		Monta a express�o SQL e retorna como string
		*/
	function getSQL() {
		$sql = "";
		// adicion
		if ($this->action=="INSERT") {
			$sql .= "INSERT INTO " . $this->table . " (";
			$fieldlist = "";
			$valuelist = "";
			for ($i=0; $i<sizeof($this->updateFields); $i++) {
				$fieldlist .= $this->updateFields[$i] . ", ";
				$valuelist .= $this->getValue($this->updateValues[$i], $this->updateTypes[$i]) . ", ";
			}
			$fieldlist = substr($fieldlist,0,-2);
			$valuelist = substr($valuelist,0,-2);
			$sql .= $fieldlist . ") VALUES (" . $valuelist . ")";
		}

		// modificacion
		if ($this->action=="UPDATE") {
			$sql .= "UPDATE " . $this->table . " SET ";
			$updatelist = "";
			for ($i=0; $i<sizeof($this->updateFields); $i++) {
				$updatelist .= $this->updateFields[$i] . "=" .
				$this->getValue($this->updateValues[$i], $this->updateTypes[$i]) . ", ";
			}
			$updatelist = substr($updatelist,0,-2);
			$sql .= $updatelist . " WHERE " . $this->keyField . "=" . $this->getValue($this->keyValue, $this->keyType);
		}

		// eliminacion
		if ($this->action=="DELETE") {
			$sql .= "DELETE FROM " . $this->table . " WHERE " . $this->keyField . "=" . $this->getValue($this->keyValue, $this->keyType);
		}

		return $sql;
	}

	/*
		Formata o valor conforme o tipo
		value : valor do campo
		type : tipo do campo (Number, String, Date)
		*/
	function getValuexxxxx($value, $type) {
		if (!strlen($value)) {
			return "NULL";
		} else {
			if ($type == "Number") {
				//return str_replace (",", ".", doubleval($value));
				return str_replace (",", "", doubleval($value));
			} else {
//				if (get_magic_quotes_gpc() == 0) {
//					$value = str_replace("'","''",$value);
//					$value = str_replace("\\","\\\\",$value);
//				} else {
					//					$value=utf8_encode($value);
					//					$value = str_replace("'","\'",$value);
					$value = str_replace("\\'","''",$value);
					$value = str_replace("\\\"","\"",$value);

					$value = str_replace("\\'","''",$value); /* Descomentar para que funcione noticias */
//				}
				return "'" . $value . "'";
			}
		}
	}

	function getValue($value, $type) {
		if (!strlen($value)) {
			return "NULL";
		} else {
			if ($type == "Number") {
				//return str_replace (",", ".", doubleval($value));
				return str_replace (",", "", doubleval($value));
			} else {
//				if (get_magic_quotes_gpc() == 0) {
//					$value = addslashes($value);
//				}
				return "$$" . $value . "$$";
			}
		}
	}

}

/*****************************************************************************************************
 Clase para creación de formularios
 */
class Form {
	var $name;
	var $action;
	var $method;
	var $target;
	var $width;
	var $blockFields;
	var $blockHidden;
	var $focus;
	var $upload;
	var $labelWidth;
	var $dataWidth;
	var $tableMargin;
	var $classlabel;
	var $LabelFONT;
	var $classdata;
        var $classTable;

	// construtor
	// $name : identificador do formul�rio
	// $action : action do formul�rio
	// $method : m�todo a ser utilizado POST ou GET
	// $target : frame em que o action ser� executado
	// $width : largura do formul�rio
	// $focus : mecanismo de foco destacado, true ou false
	function __construct($name="frm", $action="", $method="POST", $target="controle", $width="100%", $focus=false) {
		$this->name = $name;
		$this->action = $action;
		$this->method = $method;
		$this->target = $target;
		$this->width = $width;
		$this->blockFields = "";
		$this->blockHidden = "";
		$this->focus = $focus;
		$this->labelWidth = "30%";
		$this->dataWidth = "70%";
		$this->tableMargin= true;
		$this->classlabel = "LabelTD";
		$this->LabelFONT = "LabelFONT";
		$this->classdata = "DataTD BackTD";
                $this->classTable="FormTABLE";
	}


	// define o tipo de documento
	function setUpload($fazUpload=false) {
		$this->upload = $fazUpload;
	}

	// define a largura da coluna label
	function setLabelWidth($valor) {
		$this->labelWidth = $valor;
	}

	// define a largura da coluna data
	function setDataWidth($valor) {
		$this->dataWidth = $valor;
	}

	// define o nome do formul�rio
	function setName($umNome) {
		$this->name = $umNome;
	}

	// define a a��o do formul�rio
	function setAction($umaAcao) {
		$this->action = $umaAcao;
	}

	// define o m�todo do formul�rio
	function setMethod($umMetodo) {
		$this->method = $umMetodo;
	}

	// define o target do formul�rio
	function setTarget($umTarget) {
		$this->target = $umTarget;
	}

	// define se campos ter�o highligth
	function setFocus($focus) {
		$this->focus = $focus;
	}

	// define a largura do formul�rio
	function setWidth($largura) {
		$this->width = $largura;
	}

	function setTabMargin($setmargin) {
		$this->tableMargin = $setmargin;
	}

	function setClassLabel($class) {
		$this->classlabel = $class;
	}

	function setClassLabelFont($class) {
		$this->LabelFONT = $class;
	}

	function setClassData($class) {
		$this->classdata = $class;
	}
	
        function setClassTable($class) {
		$this->classTable = $class;
	}

	// adiciona campo hidden ao formul�rio
	// $varName : nome do campo
	// $varValue : valor do campo
	function addHidden($varName, $varValue, $msjvalid='') {
		$this->blockHidden .= "<input type='hidden' name='".$varName."' value='".$varValue."' id='$msjvalid'>\n";
	}

	// adiciona campo al formul�rio
	// $label : t�tulo del campo
	// $field : expresi�n html que define al campo
	function addField($label="", $field, $title="") {
		$this->blockFields .= "<tr>";
		$this->blockFields .= "<td width='".$this->labelWidth."' class='".$this->classlabel."' nowrap><font class=".$this->LabelFONT.">".$label."</font></td>";
		$this->blockFields .= "<td width='".$this->dataWidth."' class='".$this->classdata."' title=\"$title\"><font class='".iif(strpos($field,"name="),">",0,"DataFONT","ValueFONT")."'>".$field."</font></td>";
		$this->blockFields .= "</tr>\n";
	}

	// adiciona una imagen dentro de un div
	// $label : t�tulo do campo
	// $field : express�o html que define o campo
	function addDivImage($DivImg) {
		$this->blockFields .= "<tr>";
		$this->blockFields .= "<td>";
		$this->blockFields .= $DivImg;
		$this->blockFields .= "</td>";
		$this->blockFields .= "</tr>\n";
	}

	// adiciona C�digo html en el form
	// $label : t�tulo do campo
	// $field : express�o html que define o campo
	function addHtml($CodHtml) {
		$this->blockFields .= $CodHtml;
	}

	function addLine($colspan='2'){
		$this->blockFields .= "<tr>";
		$this->blockFields .= "<td colspan='$colspan'>
		<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<div style=\"BACKGROUND:url(../../img/hlhx.gif)\"><img src=\"../../img/1.gif\" width=\"12\" height=\"1\"></div></table>
		</td>";
		$this->blockFields .= "</tr>\n";
	}

	// adiciona divis�ria ao formul�rio
	// $text : express�o que ser� mostrada dentro da quebra
	// $style : usar estilo predefinido? true ou false
	function addBreak($text="", $style=true, $colspan='2', $align='') {
		$this->blockFields .= "<tr>";
		if ($style) {
			$this->blockFields .= "<td class='RecordSeparatorTD' colspan='$colspan' align=\"$align\"><font class='RecordSeparatorFONT'>".$text."</font></td>";
		} else {
			$this->blockFields .= "<td colspan='$colspan' align=\"$align\">".$text."</td>";
		}
		$this->blockFields .= "</tr>\n";
	}

	// retorna bloco HTML com o formul�rio montado
	function writeHTML() {
		$out = "";
		if($this->tableMargin){
			$out .= "<table border='0' cellpadding='1' cellspacing='0' align='center' width='".$this->width."'>\n";
			$out .= "<tr><td>";
		}

		$enctype = "";
		if ($this->upload) $enctype = "enctype='multipart/form-data'";

		if ($this->focus) {
			$out .= "<form name='".$this->name."' id='".$this->name."' ".$enctype." action='".$this->action."' method='".$this->method."' target='".$this->target."' onKeyUp='highlight(event)' onClick='highlight(event)'>\n";
		} else {
			$out .= "<form name='".$this->name."' id='".$this->name."' ".$enctype." action='".$this->action."' method='".$this->method."' target='".$this->target."'>\n";
		}
		$out .= $this->blockHidden;
		$out .= "<table width='".$this->width."' class='".$this->classTable."' cellspacing=0>\n";
		$out .= $this->blockFields;
		$out .= "</table>\n";
		$out .= "</form>\n";

		if($this->tableMargin)
		$out .= "</td></tr></table>\n";
		return $out;
	}
}

/***********************************************************************************************************
 Clase para A�adir una tabla dentro de un formulario de Edici�n.  Esta tabla se crea con la misma estructura
 del formulario permitiendo entonces poder agregar m�s campos al formulario de manera transparente.
 ***********************************************************************************************************/
class AddTableForm {
	var $labelWidth;
	var $dataWidth;
	var $styRecordSeparatorTD;
	var $styRecordSeparatorFONT;
	var $styLabelTD;
	var $styLabelFONT;
	var $styDataTD;
	var $styBackTD;
	var $styDataFONT;
	var $styValueFONT;
	var $classlabel;
	var $LabelFONT;
	var $classdata;

	// construtor
	function __construct($width="100%")
	{
		$this->TableWidth = $width;
		$this->tableAlign = "L";
		$this->labelWidth = "30%";
		$this->dataWidth = "70%";
		$this->styRecordSeparatorTD='RecordSeparatorTD';
		$this->styRecordSeparatorFONT='RecordSeparatorFONT';
		$this->styLabelTD='LabelTD';
		$this->styLabelFONT='LabelFONT';
		$this->styDataTD='DataTD';
		$this->styBackTD='BackTD';
		$this->styDataFONT='DataFONT';
		$this->styDataTD='DataTD';
		$this->styValueFONT='ValueFONT';
		$this->classlabel = "LabelTD";
		$this->LabelFONT = "LabelFONT";
		$this->classdata = "DataTD BackTD";
                $this->blockHidden="";
	}

	//setea el stylo para el fondo de los separadores de seccion 'metodo BREAK'
	function setRecordSeparatorTD($style) {
		$this->styRecordSeparatorTD=$style;
	}
	//setea el stylo para las letras de los separadores de seccion 'metodo BREAK'
	function setRecordSeparatorFONT($style) {
		$this->styRecordSeparatorFONT=$style;
	}
	//setea el stylo para el fondo de las etiquetas de los forms
	function setLabelTD($style) {
		$this->styLabelTD=$style;
	}
	//setea el stylo para el texto de las etiquetas de los forms
	function setLabelFONT($style) {
		$this->styLabelFONT=$style;
	}
	//setea el stylo para las olumnas de los datos de los forms
	function setDataTD($style) {
		$this->styDataTD=$style;
	}
	//setea el stylo para el color de fondo de las columnas de los forms
	function setBackTD($style) {
		$this->styBackTD=$style;
	}
	//setea el stylo para el color de fondo de los datos de los forms
	function setDataFONT($style) {
		$this->styDataFONT=$style;
	}
	//setea el stylo para el color de valores de los datos (cuando no hay objetos)
	function setValueFONT($style) {
		$this->styValueFONT=$style;
	}
	// define a largura da coluna label
	function setLabelWidth($valor) {
		$this->labelWidth = $valor;
	}
	// define a largura da coluna data
	function setDataWidth($valor) {
		$this->dataWidth = $valor;
	}

	// define o alinhamento da tabela
	function setTableAlign($tableAlign) {
		$this->tableAlign = strtoupper($tableAlign);
	}

	function setClassLabel($class) {
		$this->classlabel = $class;
	}

	function setClassLabelFont($class) {
		$this->LabelFONT = $class;
	}

	function setClassData($class) {
		$this->classdata = $class;
	}

	// adiciona C�digo html en el form
	// $label : t�tulo do campo
	// $field : express�o html que define o campo
	function addHtml($CodHtml) {
		$this->blockFields .= $CodHtml;
	}

	function addLine(){
		$this->blockFields .= "<tr>";
		$this->blockFields .= "<td colspan='2'>
						<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
						<div style=\"BACKGROUND:url(../../img/hlhx.gif)\"><img src=\"../../img/1.gif\" width=\"12\" height=\"1\"></div></table>
					 	</td>";						
		$this->blockFields .= "</tr>\n";
	}

	// adiciona divis�ria ao formul�rio
	// $text : express�o que ser� mostrada dentro da quebra
	// $style : usar estilo predefinido? true ou false
	function addBreak($text="", $style=true, $colspan='2', $align='') {
		$this->blockFields .= "<tr>";
		if ($style) {
			$this->blockFields .= "<td class='$this->styRecordSeparatorTD' colspan='$colspan' align=\"$align\"><font class='$this->styRecordSeparatorFONT'>".$text."</font></td>";
		} else {
			$this->blockFields .= "<td colspan='$colspan' align=\"$align\">".$text."</td>";
		}
		$this->blockFields .= "</tr>\n";
	}

	// adiciona una imagen dentro de un div
	// $label : t�tulo do campo
	// $field : express�o html que define o campo
	function addDivImage($DivImg) {
		$this->blockFields .= $DivImg;
	}

	// adiciona campo ao formul�rio
	// $label : t�tulo do campo
	// $field : express�o html que define o campo
	function addField($label="", $field) {
		$this->blockFields .= "<tr>";
		$this->blockFields .= "<td width='".$this->labelWidth."' class='$this->styLabelTD' nowrap><font class='$this->styLabelFONT'>".$label."</font></td>";
		$this->blockFields .= "<td width='".$this->dataWidth."' class='$this->styDataTD $this->styBackTD' ><font class='".iif(strpos($field,"name="),">",0,"$this->styDataFONT","$this->styValueFONT")."'>".$field."</font></td>";
		$this->blockFields .= "</tr>\n";
	}

	// adiciona campo hidden ao formul�rio
	// $varName : nome do campo
	// $varValue : valor do campo
	function addHidden($varName, $varValue, $msjvalid='') {
		$this->blockHidden .= "<input type='hidden' name='$varName' value=\"$varValue\" id='$msjvalid'>\n";
	}

	// retorna bloco HTML com o formul�rio montado
	function writeHTML() {
		if ($this->tableAlign=="L") $ta = "<div align='left'>";
		if ($this->tableAlign=="C") $ta = "<div align='center'>";
		if ($this->tableAlign=="R") $ta = "<div align='right'>";
		$out = "";
		$out .= $this->blockHidden;
		$out .= "$ta<table width='".$this->TableWidth."' class='FormTABLE' border='0' cellpadding='0' cellspacing='0' >\n";
		$out .= $this->blockFields;
		$out .= "</table></div>\n";
		return $out;
	}
}

/*****************************************************************************************************
 Clase para generar tablas
 */
class Table {
	var $block;
	var $blockHead;
	var $title;
        var $jsTitle;
	var $width;
	var $row;
	var $columns;
	var $currcol;
	var $style;
	var $alternate = false;
	var $tableAlign;
	var $styAlternateBackTD;
	var $styBackTD;
	var $styAlternateDataTD;
	var $styDataTD;
	var $styDataFONT;
	var $FormTotalTD;
	var $styFormTotalFONT;
	var $styColumnTD;
	var $styColumnFontLink;
	var $styColumnFont;
	var $styRecordSeparatorTD;
	var $styRecordSeparatorFONT;
	var $styFormTABLE;
	var $styFormHeaderTD;
	var $styFormHeaderFONT;
	var $id;

	// Construtor
	// $title : t�tulo da tabela
	// $width : largura da tabela
	// $columns : quantidade de colunas na tabela
	// $style : usar estilo predefinido? true ou false
	// $id : id de la tabla
	function __construct($title="", $width="100%", $columns, $style=true, $id='') {
		$this->title = $title;
		$this->width = $width;
		$this->columns = $columns;
		$this->currcol = 1;
		$this->style = $style;
		$this->tableAlign = "L";
		$this->styAlternateBackTD='AlternateBackTD';
		$this->styBackTD='BackTD';
		$this->styAlternateDataTD='AlternateDataTD';
		$this->styDataTD='DataTD';
		$this->styDataFONT='DataFONT';
		$this->styFormTotalTD='FormTotalTD';
		$this->styFormTotalFONT='FormTotalFONT';
		$this->styColumnTD='ColumnTD';
		$this->styColumnFontLink='ColumnFontLink';
		$this->styColumnFont='ColumnFont';
		$this->styRecordSeparatorTD='RecordSeparatorTD';
		$this->styFormTABLE='FormTABLE';
		$this->styFormHeaderTD='FormHeaderTD';
		$this->styFormHeaderFONT='FormHeaderFONT';
		$this->styRecordSeparatorFONT='RecordSeparatorFONT';
		$this->id = $id;
	}
	//setea el segundo color de fondo para las filas de datos de las tablas
	function setAlternateBackTD($style) {
		$this->styAlternateBackTD=$style;
	}
        
	//setea el primer color de fondo para las filas de datos de las tablas
	function setBackTD($style) {
		$this->styBackTD=$style;
	}
	//setea el segundo stylo para las filas de datos de las tablas
	function setAlternateDataTD($style) {
		$this->styAlternateDataTD=$style;
	}
	//setea el primer stylo para las filas de datos de las tablas
	function setDataTD($style) {
		$this->styDataTD=$style;
	}
	//setea el tipo y color de letras para las filas de datos de las tablas
	function setDataFONT($style) {
		$this->styDataFONT=$style;
	}
        function setTitle($title) {
		$this->title=$title;
	}
        function setJsTitle($js) {
		$this->jsTitle=$js;
	}
	//setea el color de fondo para las filas de totales de las tablas
	function setFormTotalTD($style) {
		$this->styFormTotalTD=$style;
	}
	//setea el stylo de letra para las filas de totales de las tablas
	function setFormTotalFONT($style) {
		$this->styFormTotalFONT=$style;
	}
	//setea el stylo para el texto de ordenacion en las cebaceras de las tablas
	function setColumnFontLink($style) {
		$this->styColumnFontLink=$style;
	}
	//setea el stylo para el fondo de las cabeceras de las tablas
	function setColumnTD($style) {
		$this->styColumnTD=$style;
	}
	//setea el stylo para las letras de las cabeceras de las tablas
	function setColumnFont($style) {
		$this->styColumnFont=$style;
	}
	//setea el stylo para el fondo del titulo de las tablas
	function setFormHeaderTD($style) {
		$this->styFormHeaderTD=$style;
	}
	//setea el stylo para las letras del titulo de las tablas
	function setFormHeaderFONT($style) {
		$this->styFormHeaderFONT=$style;
	}
	//setea el stylo general de la tabla, por lo general no se modifica
	function setFormTABLE($style) {
		$this->styFormTABLE=$style;
	}
	//setea el stylo para el fondo de los separadores de seccion 'metodo BREAK'
	function setRecordSeparatorTD($style) {
		$this->styRecordSeparatorTD=$style;
	}
	//setea el stylo para las letras de los separadores de seccion 'metodo BREAK'
	function setRecordSeparatorFONT($style) {
		$this->styRecordSeparatorFONT=$style;
	}
	// agrupa c�lulas e adiciona na linha
	// recibe un estylo, para controlar el color solo de una fila, ejemplo: ANULADOS
	function addRow($style="",$selector=true,$js='') {
		$st = $this->alternate?$this->styAlternateBackTD:$this->styBackTD;
		$style=$style?$style:$st;

		if($selector==false)
                    $this->block .= "<tr class='$style' id='$style' $js>".$this->row."</tr>\n";
		else
                    
		$this->block .= "<tr class='$style' id='$style' onmouseover=\"MO(event,'TR')\" onmouseout=\"MU(event,'TR')\" $js>".$this->row."</tr>\n";

		$this->row = "";
		$this->currcol = 1;
		$this->alternate = !$this->alternate;
	}

	// Creo la fila que contendr� las celdas de cabecera de la tabla
	function addRowHead($style="") {
		$style=$style?$style:$st;
		$this->blockHead .= "<tr class='$style' id='$style' >".$this->row."</tr>\n";
		$this->row = "";
		$this->currcol = 1;
	}

	function addHtml($CodHtml) {
		$this->block .= $CodHtml;
	}

	// crea una celda
	// $data : conte�do dentro da c�lula
	// $align : alinhamento (L, C, R)
	function addData($data="&nbsp", $align="L", $id="", $js="", $title="") {
		$cs = $this->currcol;
		$align = strtoupper($align);
		if ($align=="L") $al = "align=left";
		if ($align=="C") $al = "align=center";
		if ($align=="R") $al = "align=right";
		if ($this->style) {
			$st = $this->alternate?$this->styAlternateDataTD:$this->styDataTD;
			$this->row .= "<td class='$st' $al title=\"$title\" id=\"$id\" ".str_replace('NCOL',$cs,$js)." ><font class='".$this->styDataFONT."'>".$data."</font></td>";
		} else {
			$this->row .= "<td $al>".$data."</td>";
		}
		$this->currcol++;
	}

	// crea una celda total
	// $data : contenido dentro da c�lula
	// $align : alinhamento (L, C, R)
	function addTotal($data="&nbsp", $align="R") {
		$align = strtoupper($align);
		if ($align=="L") $al = "align=left";
		if ($align=="C") $al = "align=center";
		if ($align=="R") $al = "align=right";
		$this->row .= "<td class='".$this->styFormTotalTD."' $al><font class='".$this->styFormTotalFONT."'>".$data."</font></td>";
	}

	// cria t�tulo da coluna
	// $title : t�tulo da coluna
	// $ord : ordenar? true ou false
	// $width : largura da coluna
	// $align : alinhamento (L, C, R)
	function addColumnHeader($title="&nbsp;", $ord=false, $width="1", $align="L", $js="", $alt="", $nowrap="") {
		global $form_sorting;
		$cs = $this->currcol;

		$align = strtoupper($align);
		if ($align=="L") $al = "align=left";
		if ($align=="C") $al = "align=center";
		if ($align=="R") $al = "align=right";

		$this->row .= "<td class='".$this->styColumnTD."' width='".$width."' $al $nowrap title=\"$alt\">";
		if ($ord) {
			if($js)
                            $this->row .= "<a title='Ordenar por $title' class='".$this->styColumnFontLink."' href='#' onClick=\"".str_replace('NCOL',$cs,$js)."\">".$title."</a>";
			else
                            $this->row .= "<a title='Ordenar por $title' class='".$this->styColumnFontLink."' href='".$_SERVER['PHP_SELF']."?Sorting=$cs&Sorted=$form_sorting'>".$title."</a>";
		} else {
			$this->row .= "<font class='".$this->styColumnFont."'>".$title."</font>";
		}
		$this->row .= "</td>";
		$this->alternate = true;
		$this->currcol++;
	}

	function addLine(){
		$this->blockFields .= "<tr>";
		$this->blockFields .= "<td colspan='$this->columns'>
		<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<div style=\"BACKGROUND:url(../../img/hlhx.gif)\"><img src=\"../../img/1.gif\" width=\"12\" height=\"1\"></div></table>
		</td>";
		$this->blockFields .= "</tr>\n";
	}
	// adiciona linha divis�ria na tabela
	// $title : express�o html que ser� exibida na quebra
	function addBreak($title="&nbsp", $style=true, $align='') {
		if (!$style) {
			$this->row .= "<td colspan='".$this->columns."' align=\"$align\">".$title."</td>";
		} else {
			$this->row .= "<td class='".$this->styRecordSeparatorTD."' colspan='".$this->columns."' align=\"$align\"><font class='".$this->styRecordSeparatorFONT."'>".$title."</font></td>";
		}
		$this->addRow();
		$this->alternate = false;
	}

	// define o alinhamento da tabela
	function setTableAlign($tableAlign) {
		$this->tableAlign = strtoupper($tableAlign);
	}

	// retorna o bloco HTML com a tabela montada
	function writeHTML() {
		if ($this->tableAlign=="L") $ta = "<div align='left'>";
		if ($this->tableAlign=="C") $ta = "<div align='center'>";
		if ($this->tableAlign=="R") $ta = "<div align='right'>";
		$out = "$ta<table border=0 cellspacing=0 cellpadding=1 width='".$this->width."'><tr><td vAlign='top' align='center'>";
		if ($this->style) {
			$out .= "<table id='$this->id' width='100%' class='".$this->styFormTABLE."' cellspacing=0>";
		} else {
			$out .= "<table id='$this->id' border='0'>";
		}
		$out .= "<thead>";
		$out .= $this->blockHead;
		$out .= "</thead>";
		if ($this->title != "") {
			if($this->jsTitle)
                            $out .= "<tr $this->jsTitle>";
                        else    
                            $out .= "<tr>";
                        
			$out .= "<td class='".$this->styFormHeaderTD."' colspan='".$this->columns."'>";
			$out .= "<font class='".$this->styFormHeaderFONT."'>".$this->title."</font>";
			$out .= "</td>";
			$out .= "</tr>";
		}
		$out .= $this->block;
		$out .= "</table>";
		$out .= "</td></tr></table></div>";
		return $out;
	}
        
        function setRow($row) {
            return $this->row=$row;
        }
        
        function getRow() {
            return $this->row;
        }
}

/*****************************************************************************************************
 Clase para generar tablas
 */
class TableSimple {
	var $block;
	var $blockHead;
	var $title;
	var $width;
	var $row;
	var $columns;
	var $id;
        var $styFormTotalTD;
        var $styFormTotalFONT;

	// Construtor
	// $title : t�tulo da tabela
	// $width : largura da tabela
	// $columns : quantidade de colunas na tabela
	// $style : usar estilo predefinido? true ou false
	// $id : id de la tabla
	function __construct($title="", $width="100%", $columns, $id='') {
		$this->title = $title;
		$this->width = $width;
		$this->columns = $columns;
		$this->currcol = 1;
		$this->id = $id;
                $this->styFormTotalTD='FormTotalTD';
		$this->styFormTotalFONT='FormTotalFONT';
	}

	// agrupa c�lulas e adiciona na linha
	// aaaa recibe un estylo, para controlar el color solo de una fila, ejemplo: ANULADOS
	function addRow($style='',$id='') {
		$this->block .= "<tr id='$id' class='$style' >".$this->row."</tr>\n";
		$this->row = "";
		$this->currcol = 1;
	}

	// Creo la fila que contendr� las celdas de cabecera de la tabla
	function addRowHead($id='') {
		$this->blockHead .= "<tr id='$id' >".$this->row."</tr>\n";
		$this->row = "";
		$this->currcol = 1;
	}
        
        // Creo la fila que contendr� las celdas de cabecera de la tabla
	function addRowFoot($id='') {
		$this->blockFoot .= "<tr id='$id' >".$this->row."</tr>\n";
		$this->row = "";
		$this->currcol = 1;
	}

	function addHtml($CodHtml) {
		$this->block .= $CodHtml;
	}

	// crea una celda
	// $data : conte�do dentro da c�lula
	// $align : alinhamento (L, C, R)
	function addData($data="&nbsp", $align="L", $id="", $title="" ) {
		$align = strtoupper($align);
		if ($align=="L") $al = "align=left";
		if ($align=="C") $al = "align=center";
		if ($align=="R") $al = "align=right";
		$this->row .= "<td $al id=\"$id\" title=\"$title\">$data</td>";
	}

	// cria t�tulo da coluna
	// $title : t�tulo da coluna
	// $ord : ordenar? true ou false
	// $width : largura da coluna
	// $align : alinhamento (L, C, R)
	function addColumnHeader($title="&nbsp;", $width="1", $align="L", $nowrap="") {
		$align = strtoupper($align);
		if ($align=="L") $al = "align=left";
		if ($align=="C") $al = "align=center";
		if ($align=="R") $al = "align=right";
		$this->row .= "<th width='$width' $al $nowrap>$title";
		$this->row .= "</th>";
	}

	function addLine(){
		$this->blockFields .= "<tr>";
		$this->blockFields .= "<td colspan='$this->columns'>
		<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<div style=\"BACKGROUND:url(../../img/hlhx.gif)\"><img src=\"../../img/1.gif\" width=\"12\" height=\"1\"></div></table>
		</td>";
		$this->blockFields .= "</tr>\n";
	}
	// adiciona linha divis�ria na tabela
	// $title : express�o html que ser� exibida na quebra
	function addBreak($title, $align='',$id='') {
		$this->row .= "<td class='break' id='$id' colspan='".$this->columns."' align=\"$align\">".$title."</td>";
		$this->addRow();
	}

        
        function addTotal($data="&nbsp", $align="R") {
		$align = strtoupper($align);
		if ($align=="L") $al = "align=left";
		if ($align=="C") $al = "align=center";
		if ($align=="R") $al = "align=right";
		$this->row .= "<td class='".$this->styFormTotalTD."' $al><font class='".$this->styFormTotalFONT."'>".$data."</font></td>";
	}
        
        function addColumnFoot($title, $align='', $id="") {
		$this->row .= "<th class='".$this->styColumnTD."'  id='$id' colspan='".$this->columns."' align=\"$align\"><font class='DataFONT'>".$title."</font>";
		$this->row .= "</th>";
	}
        
	// retorna o bloco HTML com a tabela montada
	function writeHTML() {
                $out = "";
		if ($this->title != "") {
			$out .="<div class='Bordeatabla' style='width:100%' align=center >$this->title</div>";
		}
		$out .= "<table id='$this->id' class='tablesorter' align=center width='$this->width' border=0 cellspacing=0 cellpadding=1>";
		$out .= "<thead>";
		$out .= $this->blockHead;
		$out .= "</thead>";
		$out .= $this->block;
		$out .= "</table>";
		return $out;
	}
}

/*****************************************************************************************************
 Classe para gerar campo lookup
 */
class Lookup {
	var $title;
	var $nomeCampoForm;
	var $captionCampoForm;
	var $valorCampoForm;
	var $nomeTabela;
	var $nomeCampoChave;
	var $nomeCampoExibicao;
	var $nomeCampoAuxiliar;
	var $valorCampoFormDummy;
	var $upCase;
	var $size;
	var $WinWidth;
	var $WinHeight;
	var $ListaInicial;
	var $sql;
	var $stringBusqueda;  // Se usa para indicar un string de búsqueda en especial al editar el registro.  Solo se usa cuando el lookup es el complejo est� basado en un string y no en una simple tabla
	var $readonly;
	var $NumForm; // N�mero del formulario donde se encuentra el objeto.  Por Default es 0

	function __construct() {
		// Establezco las medidas por defecto de ventana popup
		$this->WinWidth=500;
		$this->WinHeight=520;

		$this->upCase=true;
		$this->ListaInicial=1;
		$this->size=LOOKUP_FIELDSIZE;
		$this->readonly=false;
		$this->NumForm=0; // por defecto el objeto va en el formulario 0
	}

	// define el n�mero de formulario donde se encuetra el objeto que llamaré al lookup
	function setNumForm($NumForm) {
		$this->NumForm=$NumForm;
	}

	// define o nome do campo do formul�rio
	function setNomeCampoForm($caption,$umNome) {
		$this->captionCampoForm = $caption;
		$this->nomeCampoForm = $umNome;
	}

	// define o nome do campo auxiliar que ser� exibido no lookup
	function setNomeCampoAuxiliar($umNome) {
		$this->nomeCampoAuxiliar = $umNome;
	}

	// define o t�tulo que aparecer� na janela de lookup
	function setTitle($umTitulo) {
		$this->title = $umTitulo;
	}

	// define si el objeto es de solo lectura
	function readonly($readonly) {
		$this->readonly = $readonly;
	}

	// define un string especial de b�squeda si fuera necesario.  Esto es para cuando el string que genera la consulta
	// no es el mismo que se puede usar para efectuar la b�squeda al editar el registro.
	function setStringBusqueda($StringSql) {
		$this->stringBusqueda = $StringSql;
	}

	// define o valor inicial do campo do formul�rio
	function setValorCampoForm($umValor) {
		$this->valorCampoForm = $umValor;
		//		$sql = "select ".$this->nomeCampoExibicao.", ".$this->nomeCampoChave." from ".$this->nomeTabela
		//		     . " where ".$this->nomeCampoChave."=".$this->valorCampoForm;

		// He colocado comillas simples al dato '$this->valorCampoForm' para que funcione cuando el campo clave es varchar o text,
		// esto no afecta si el campo es integer o serial ya que Postgres lo interpreta como tal.

		$numCampo=0;

		//verifico si la tabla es un string sql
		if(getSession($this->nomeTabela)){
			if($this->stringBusqueda) // Si exsite un string de b�squeda especial para efectuar al editar el registro
			$sql = $this->stringBusqueda;
			else
			$sql = getSession($this->nomeTabela);

			if(strpos(strtoupper($sql),"WHERE"))
			$sql .= " and $this->nomeCampoChave='$this->valorCampoForm'";
			else
			$sql .= " where $this->nomeCampoChave='$this->valorCampoForm'";

			$numCampo=1;
		}
		else
		//estructura la consulta para obtener el campo de exibicion
		$sql = "select $this->nomeCampoExibicao,$this->nomeCampoChave from
		$this->nomeTabela where $this->nomeCampoChave='$this->valorCampoForm'";

		$this->sql = $sql;
		$this->valorCampoFormDummy = getDbValue($sql,$numCampo);
	}

	// define o nome da tabela que ser� exibida no lookup
	function setNomeTabela($umNome) {
		$this->nomeTabela = $umNome;
	}

	// define o nome do campo chave que ser� devolvido ao campo do formul�rio
	function setNomeCampoChave($umNome) {
		$this->nomeCampoChave = $umNome;
	}

	// define o nome do campo que ser� exibido no lookup
	function setNomeCampoExibicao($umNome) {
		$this->nomeCampoExibicao = $umNome;
	}
	// define si la caja de texto donde se busca solicita letras mayusculas
	function setUpCase($upCase) {
		$this->upCase=$upCase;
	}
	// define el tama�o de la caja de texto
	function setSize($size) {
		$this->size=$size;
	}

	// define el ancho de la ventana
	function setWidth($width) {
		$this->WinWidth=$width;
	}

	// define la altura de la ventana
	function setHeight($height) {
		$this->WinHeight=$height;
	}

	// define si muestra una lista inicial al cargar el popup,  Por defecto muestra la lista.  Si el valor es false solo se mostrar� la lista cuando se efect�e una b�squeda.
	function setListaInicial($ListaInicial) {
		$this->ListaInicial=$ListaInicial;
	}

	// retorna o bloco HTML que monta o campo lookup
	function writeHTML() {
		$out = "";
		$out .= "<input type='hidden' name='__Change_".$this->nomeCampoForm."' id='__Change_".$this->captionCampoForm."' value=0>"; // Para poder controlar un evnto Change en este objeto, ya que no es posible escribir nada en el.
		$out .= "<input type='hidden' name='".$this->nomeCampoForm."' id='".$this->captionCampoForm."' value='".$this->valorCampoForm."'>";
		$out .= "<input type='text' name='_Dummy".$this->nomeCampoForm."' id='".$this->captionCampoForm."' value='".$this->valorCampoFormDummy."' size='".$this->size."' readonly>";
		if($this->readonly){}
		else{
			$out .= "<img title=\"Clique aqui para abrir la lista de registros\" align='middle' style='cursor: pointer' src='". LOOKUP_IMAGEM ."' onClick=\"lookup(";
			$out .= "'".$this->nomeCampoForm."', '".$this->nomeTabela."', '".$this->nomeCampoChave."', '".$this->nomeCampoExibicao."', '".$this->nomeCampoAuxiliar."', '".$this->upCase."', '".$this->title."', ".$this->WinWidth.",".$this->WinHeight.",".$this->ListaInicial.",".$this->NumForm;
			$out .= ")\">";
		}
		return $out;
	}
}

//$this->upCase."', '".$this->title."',500";


/*****************************************************************************************************
 Classe para cria��o de abas
 */
class Abas {
	var $item;
	var $status;
	var $url;
	var $level;
	var $js;
        
        function  __construct(){
            
        }

	// adiciona uma aba
	// $nome : nome da aba
	// $status : ativa? true ou false
	// $url : link que ser� chamado (usar somente se inativa)
	// $level : n�vel de acesso m�nimo que o usu�rio deve ter para visualizar esta aba
	function addItem($nome="Geral", $status=false, $url="", $level=0, $js="") {
		$this->item[] = $nome;
		$this->status[] = $status;
		$this->url[] = $url;
		$this->level[] = $level;
		$this->js[] = $js;
	}

	// retorna bloco HTML que monta as abas
	function writeHTML() {
		$y = 2;
		$out  = "";
		$out .= "<table class='mytableAbas' cellpadding='2' cellspacing='0' width='100%' border='0'>";
		$out .= "<tr>";
		$out .= "<td class='FundoABA' width='10px'>&nbsp;</td>";
		for ($x = 0; $x < sizeof($this->item); $x++) {
			if (isValidUser($this->level[$x])) {
				if ($this->status[$x]) {
					$out .= "<td nowrap class='SelecionadaABA'><font class='SelecionadaFontABA'>&nbsp;" . $this->item[$x] . "&nbsp;</font></td>";
				} else {
					$out .= "<td nowrap class='NaoSelecionadaABA'>";
					$out .= "<font class='NaoSelecionadaFontABA'>&nbsp;";
					$out .= "<a href='".$this->url[$x]."' target='content' class='aba' ".$this->js[$x]." >";
					$out .= $this->item[$x];
					$out .= "</a>";
					$out .= "&nbsp;</font></td>";
				}
			}
			$out .= "<td class='FundoABA' width='1px'></td>";
			$y+=2;
		}
		$out .= "<td class='FundoABA' width='100%'>&nbsp;</td>";
		$out .= "</tr>";
		$out .= "<tr>";
		$out .= "<td colspan='$y' height='4px' class='SelecionadaABA'></td>";
		$out .= "</tr>";
		$out .= "</table>";
		return $out;
	}
}

/*****************************************************************************************************
 Classe para generar botones
 */
class Button {
	var $nome;
	var $url;
	var $target;
	var $level;
	var $iduser;
	var $align;
	var $style;
	var $type;
        var $js;
	var $styleAll;
	var $setDiv;
        var $id;
        var $blockFields;

	function __construct() {
		$this->styleAll='botao';
		$this->align="acoes";
		$this->setDiv=TRUE;
                $this->blockFields="";
	}

	/*
		Adiciona un item al set de botones
		$nome : Caption del Boton
		$url : url que se visitar�.  Si contiene la expresi�n 'javascri' se activa el onclick() para llamar una funci�n javascript
		$target="content" : Destino donde se cargar� el url
		$level=0 : n�vel de acesso m�nimo que el usuario debe tener para visualizar este bot�n
		0 --> Sin nivel
		1 --> Visitante
		2 --> Operador
		3 --> Supervisor
		$idUser=0 : Se envia el id del usuario para verifica que sea igual al id del usuario de registro, especial para el boton guardar.
		$style='' : Clase css que se aplicar� al bot�n.
		$type='' : tipo ejm. 'button'
		*/
	function addItem($nome, $url, $target="content", $level=0, $idUser=0, $style='', $type='', $js='', $id='') {
		$this->nome[] = $nome;
		$this->url[] = $url;
		$this->target[] = $target;
		$this->level[] = $level;
		$this->iduser[] = $idUser;
		$this->style[]=$style?$style:$this->styleAll;
		$this->type[] = $type;
                $this->js[] = $js;
                $this->id[] = $id?$id:trim($nome);
	}

	function align($align='') {
		if ($align=="C")
                    $this->align="acoescenter";
		elseif ($align=="L"){
			$this->align="acoesleft";
		}
                elseif ($align=="R"){
			$this->align="acoesright";
		}
		else
                    $this->align="acoes";
	}

        function addHtml($CodHtml) {
		$this->blockFields .= $CodHtml;
	}
        
	function setStyle($style){
		$this->styleAll=$style;
	}
	/*
	 funcion que setea el parametro div (el cual permite la alineaci�n de los botones)
		TRUE: coloca div
		FALSE: no coloca div
		*/
	function setDiv($setDiv){
		$this->setDiv=$setDiv;
	}

	/*
		Retorna o c�digo HTML com o deck de bot�es
		*/
	function writeHTML() {
                $out="";
                
		if($this->setDiv){
                    $out = "<div class='dropdown $this->align'>";
                }
                
                $out.=$this->blockFields;
		
                
		for ($x=0; $x<sizeof($this->nome); $x++) {
			// verifica el nivel de acceso
			if (isValidUser($this->level[$x]))
			//verifica si el id de usuario logeado=id de usuario de registro, especial para el boton gurdar

			if (($this->iduser[$x]==0)||(getSession("sis_userid")==$this->iduser[$x])){

                                switch($this->type[$x]){
                                    case 'btn-bootstrap':    
                                        $out .= "<button type=\"".$this->type[$x]."\" class=\"btn btn-default btn-sm\" id=\"".$this->id[$x]."\" onClick=\"".$this->url[$x]."\">";
                                            if(trim(strtoupper($this->nome[$x]))=='GUARDAR' || trim(strtoupper($this->nome[$x]))=='GUARDAR DERIVACIONES'){
                                                $out .= "<span class=\"glyphicon glyphicon-floppy-disk\" aria-hidden=\"true\"></span>&nbsp;";
                                                $out .=$this->nome[$x];
                                            }elseif(trim(strtoupper($this->nome[$x]))=='REGRESAR'){
                                                $out .= "<span class=\"glyphicon glyphicon-share-alt\" aria-hidden=\"true\"></span>&nbsp;";    
                                                $out .=$this->nome[$x];
                                            }elseif(trim(strtoupper($this->nome[$x]))=='VER DOCUMENTO'){
                                                $out .= "<span class=\"glyphicon glyphicon glyphicon-search\" aria-hidden=\"true\"></span>&nbsp;";    
                                                $out .=$this->nome[$x];
                                            }elseif(trim(strtoupper($this->nome[$x]))=='SUBIR E-FIRMA' || trim(strtoupper($this->nome[$x]))=='SUBIR Y VALIDAR XML' || trim(strtoupper($this->nome[$x]))=='SUBIR ARCHIVO'){
                                                $out .= "<span class=\"glyphicon glyphicon-cloud-upload\" aria-hidden=\"true\"></span>&nbsp;";    
                                                $out .=$this->nome[$x];
                                            }elseif(strtoupper(substr(trim($this->nome[$x]),0,8))=='IMPRIMIR'){
                                                $out .= "<span class=\"glyphicon glyphicon-print\" aria-hidden=\"true\"></span>&nbsp;";
                                                $out .=$this->nome[$x];
                                            }elseif(trim(strtoupper($this->nome[$x]))=='FIRMAR' || trim(strtoupper($this->nome[$x]))=='FINALIZAR'){
                                                $out .= "<span class=\"glyphicon glyphicon-pencil\" aria-hidden=\"true\"></span>&nbsp;";
                                                $out .=$this->nome[$x];
                                            }elseif(trim(strtoupper($this->nome[$x]))=='BORRAR'){
                                                $out .= "<span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span>&nbsp;";
                                                $out .=$this->nome[$x];
                                            }
                                        $out .="</button>";
                                        $out .= "&nbsp;";
                                        break;

                                    case 'button-dialog-min':
                                         $out .="<button type=\"button\" id=\"btnDialog-".$this->id[$x]."\" data-toggle=\"modal\" data-id=\"".$this->url[$x]."\" class=\"btn btn-btn-default btn-xs\" data-toggle=\"modal\" data-target=\"".$this->target[$x]."\" title=\"".$this->nome[$x]."\">";
                                         if(strtoupper($this->nome[$x])=='ELIMINAR'){
                                             $out .= "<span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span>";
                                         }elseif(strtoupper($this->nome[$x])=='EDITAR'){
                                             $out .= "<span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span>";
                                         }elseif(strtoupper($this->nome[$x])=='GUARDAR'){
                                             $out .= "<span class=\"glyphicon glyphicon-floppy-disk\" aria-hidden=\"true\"></span>&nbsp;";
                                         }elseif(strtoupper($this->nome[$x])=='DESHACER'){
                                             $out .= "<span class=\"glyphicon glyphicon-share-alt\" aria-hidden=\"true\"></span>&nbsp;";    
                                        }else{
                                            $out .=$this->nome[$x];
                                         }
                                         $out .= "</button>&nbsp;";
                                         break;
                                         
                                    case 'button-modal':
                                        $out .="<button type=\"button\" id=\"btnErrorCerrar-".$this->id[$x]."\" data-id=\"\" class=\"btn btn-default\" data-dismiss=\"modal\" >";
                                        if(strtoupper($this->nome[$x])==' CERRAR '){    
                                            $out .=$this->nome[$x];
                                        }
                                        $out .= "</button>";
                                        break;
                                        
                                    case 'button':
                                        $out .= "<input type=\"button\"  class=\"".trim($this->style[$x])."\" id=\"".$this->id[$x]."\" name=\"".$this->nome[$x]."\" value=\"".iif(strpos($this->nome[$x],'-'),">",0,substr($this->nome[$x],0,strpos($this->nome[$x],'-')),$this->nome[$x])."\" onClick=\"".$this->url[$x]."\">";
                                        break;

                                    case 'button2':
                                        $out .= "<button type=\"button\"  class=\"".trim($this->style[$x])."\" id=\"".$this->id[$x]."\" name=\"".$this->nome[$x]."\"  onClick=\"".$this->url[$x]."\">".iif(strpos($this->nome[$x],'-'),">",0,substr($this->nome[$x],0,strpos($this->nome[$x],'-')),$this->nome[$x])."</button>";
                                        break;
                                    
                                    case 'submit':
                                        $out .= "<input type=\"submit\"  class=\"".trim($this->style[$x])."\" id=\"".$this->id[$x]."\" name=\"".$this->nome[$x]."\" value=\"".iif(strpos($this->nome[$x],'-'),">",0,substr($this->nome[$x],0,strpos($this->nome[$x],'-')),$this->nome[$x])."\" onClick=\"".$this->url[$x]."\">";
                                        break;

                                    case 'thickbox':
                                        $out .= "&nbsp;";
                                        $out .= "<a class='".$this->style[$x]." thickbox'
                                                     href=\"".$this->url[$x]."\"".$this->js[$x]." >&nbsp;".$this->nome[$x]."&nbsp;</a>";
                                        break;
                                    
                                    default:
					$out .= "&nbsp;";
					if(strpos($this->url[$x],"avascript"))
                                            $out .= "<a class='".$this->style[$x]."'  href='#' onClick=\"".$this->url[$x]."\"";
					else
                                            $out .= "<a class='".$this->style[$x]."' href=\"".$this->url[$x]."\"";

					$out .= " id=\"".
					trim($this->id[$x]).
						"\" target='".
					$this->target[$x]."'>";
                                        
                                        if(!strpos($this->nome[$x],"img")) $out .= "&nbsp;";
						
					$out .= $this->nome[$x];
                                        
                                        if(!strpos($this->nome[$x],"img")) $out .= "&nbsp;";        
						$out .= "</a>";
                                        break;

                                }
			}

		}
                
		if($this->setDiv)
                    return $out.="</div>";

		return $out;
	}
}


/*****************************************************************************************************
 Classe para controlar errores da página
 */
class Erro {
	var $strErro;
	function addErro($erro='') {
		$this->strErro .= $erro . '\n';
	}
	function hasErro() {
		return strlen($this->strErro)>0;
	}
	function toString() {
		return $this->strErro;
	}
}

/*****************************************************************************************************
 funion para recuperar as vari�veis GET e POST
 */
function getParam($param_name) {
	$param_value = "";
	if (isset($_POST[$param_name])) {
		$param_value = $_POST[$param_name];
	} else if(isset($_GET[$param_name])) {
		$param_value = $_GET[$param_name];
	}
	return $param_value;
}

/*****************************************************************************************************
 funci�n para recuperar vari�veis de sess�o
 */
function getSession($param_name) {
        if(isset($_SESSION[$param_name]))
            return $_SESSION[$param_name];
        else
            return;
}

/*****************************************************************************************************
 funci�n para definir vari�veis de sess�o
 */
function setSession($param_name, $param_value) {
	$_SESSION[$param_name] = $param_value;
}


/*****************************************************************************************************
 gerador de listbox
 $sql : express�o sql que monta a lista (selecionar apenas 2 campos com os nomes "id" e "val"
 $name : nome do campo que ser� criado
 $default : valor inicial do campo
 $todos : texto indicativo, caso a lista permita valor null
 $js : express�o javascript
 $size : N�mero de elementos que se mostrar�n
 1 --> Se mostrar� como combo
 2 a m�s --> Se mostrar� como una lista
 $css : Para indicar la clase de estilos o alg�n estilo especial.
 ejm: "class='miclase'"
 "style='width:80%'"
 */
function listboxField($msjvalid, $sql, $name, $default, $todos="", $js="", $size=1, $css="", $disabled="") {
	global $conn;
        
        if($sql){
            $rs = new query($conn,$sql);
            $existen_registros=$rs->numrows();
        }else{
            $existen_registros=0;
        }
        
        
                                    
        if(strpos($css,"lass=")){
            $result="<select data-placeholder=\"$todos\" $disabled name=\"$name\" id=\"$msjvalid\" $css size=$size onKeyPress='return formato(event,form,this)' $js>\n";            
        }
        else {
            $result="<select $disabled name=\"$name\" id=\"$msjvalid\" $css size=$size onKeyPress='return formato(event,form,this)' $js>\n";
        }
        
        if(strpos($css,"lass=")){
            $result.= "<option value=''></option>";
        }
	elseif ($todos!="") {
		$result.= "<option $disabled value=\"\">$todos</option>\n";
	}
        
        
	if(is_array($sql)){ /* Si es un array */
		foreach($sql as $k => $v){
			$id = $k;
                        $val = substr($v,0,100);
                        if ($val){//si se retira la lista multiple deja valores extraños
                            $selected="";
                            if($default){
                                if(is_array($default)){

                                    for($i=0;$i<count($default);$i++){
                                        if ($default[$i] == $id) {
                                            $selected="selected";
                                            break;
                                        } else {
                                            $selected="";                                
                                        } 

                                    }

                                }else{
                                    if ($default == $id) {$selected="selected";} else {$selected="";} // Para que aparezca seleccionado un valor por defecto que se le envie o si se hace un submit al form
                                }
                            }
                            $result.="<option $disabled value=\"$id\" $selected>$val</option>\n";
                        }
		}
	}elseif($existen_registros>0){ /* Si es sentencia sql*/
            
		while ($rs->getrow()) {
			$id = $rs->field($rs->fieldname(0));
                        
                        if(strpos($css,"multiple style")){
                            $val = $rs->field($rs->fieldname(1));
                        }else{
                            $val = $rs->field($rs->fieldname(1));
                        }   
                        if(is_array($default)){

                            for($i=0;$i<count($default);$i++){
                                if ($default[$i] == $id) {
                                    $selected="selected";                                    
                                } else {
                                    $selected="";                                
                                } // Para que aparezca seleccionado un valor por defecto que se le envie o si se hace un submit al form
                                $result.="<option $disabled value=\"$id\" $selected>$val</option>\n";
                                
                            }
                            
                        }else{
                            if ($default == $id) {$selected="selected";} else {$selected="";} // Para que aparezca seleccionado un valor por defecto que se le envie o si se hace un submit al form
                            $result.="<option $disabled value=\"$id\" $selected>$val</option>\n";
                        }
		}
	}
        
	$result.="</select>\n";
        
	return $result;
}


/*****************************************************************************************************
 generador de listbox ajax
 $sql : express�o sql que monta a lista (selecionar apenas 2 campos com os nomes "id" e "val"
 $name : nome do campo que ser� criado
 $default : valor inicial do campo
 $todos : texto indicativo, caso a lista permita valor null
 $js : express�o javascript
 */
function listboxAjaxField($msjvalid, $sql, $name, $default=0, $todos="", $js="", $NameDiv="") {
	/* Obtengo el valor del control si se efect�a un submit al form */
	$idValSubmit=getParam($name);

	global $conn;
	$rs = new query($conn,$sql);

	$result="<span id=\"$NameDiv\" name=\"$NameDiv\" align=\"left\">";

	$result.="<select name=\"$name\" id=\"$msjvalid\" size=1 onKeyPress='return formato(event,form,this)' $js>\n";
	if ($todos!="") {
		$result.= "<option value=\"\">$todos</option>\n";
	}
	while ($rs->getrow()) {
		$id = $rs->field($rs->fieldname(0));
		$val = substr($rs->field($rs->fieldname(1)),0,60);
		if ($default == $id or $idValSubmit==$id) {$selected="selected";} else {$selected="";} // Para que aparezca seleccionado un valor por defecto que se le envie o si se hace un submit al form
		$result.="<option value=\"$id\" $selected>$val</option>\n";
	}
	$result.="</select></span>\n";
	return $result;
}

/*****************************************************************************************************
 verifica si el usuario puede acessar a la p�gina
 $nivel : valor numerico que define el n�vel jerarquico de acesso
 */
function verificaUsuario($nivel=0) {
	if ($nivel > 0) {
		$loginFile="../modulos/login.php";
		if(!@file("$loginFile")) $loginFile="../../modulos/login.php";
		if (getSession("sis_apl")!=SIS_APL_NAME) {
			redirect("$loginFile?querystring=".urlencode(getenv("QUERY_STRING"))."&ret_page=".urlencode(getenv("REQUEST_URI")));
			die();
		} else if ((!isset($_SESSION["sis_level"]) || getSession("sis_level") < $nivel)) {
			redirect("$loginFile?querystring=".urlencode(getenv("QUERY_STRING"))."&ret_page=".urlencode(getenv("REQUEST_URI")));
			die();
		}
	}
}

/*****************************************************************************************************
 funci�n que verifica se o usuario est� dentro do n�vel
 retorna boolean
 */
function isValidUser($level=0) {
	return (($level==0)||(getSession("sis_level")>=$level));
}


/*****************************************************************************************************
 retorna o valor de um campo atrav�s de express�o sql
 */
function getDbValue($sql,$numCampo=0,$conectar=0) {
	global $conn;
	if($conectar){
		$connTemp = new db();
		$connTemp->open();
		$rs = new query($connTemp, $sql);
	}else{
		$rs = new query($conn, $sql);
	}
	if($rs->numrows()<1) {
		$valor = "";
	} else {
		$rs->getrow();
		if(ctype_digit($numCampo)) //si son digitos
		$nomecampo = $rs->fieldname($numCampo);
		else //si son caracteres
		$nomecampo = $numCampo;
			
		$valor = $rs->field($nomecampo);
	}

	$rs->free();
	if($conectar){
		$connTemp->close();
	}
	return $valor;
}


/*****************************************************************************************************
 funci�n para gerar campos radio
 $arr : array de valores, cada elemento deve ter a chave e o label separados por v�rgula
 exemplo: {"1,Solteiro","2,Casado","3,Separado"}
 $name : nome do campo
 $sel : valor inicial do campo
 $js : express�o javascript
 $posi: 'V'-> VERTICAL, 'H'->HORIZONTAL
 */
function radioField($msjvalid, $arr, $name, $sel = "", $js="", $posi='V') {
	$out = "";
        foreach ($arr as $key => $val) {
            $string = explode(",",$val);
            $label = $string[1];
            $valor = $string[0];
            $select_v = (($sel || $sel=='0') && $valor == $sel)?" checked":"";
            $out .= "<input type=radio name=\"$name\" id='$msjvalid' value=\"$valor\" $select_v $js > $label"
            .iif($posi,'==','V','<br>','&nbsp;')."\n";
        }
                    
	return $out;
}

/*****************************************************************************************************
 funci�n para gerar campo de data com calend�rio popup
 $fieldname : nome do campo que ser� criado
 $fieldvalue : valor inicial do campo
 */
function dateField($msjvalid, $fieldname, $fieldvalue="", $js="") {
	$out = "";
	$out .= "<input type='text' id='$msjvalid' name='$fieldname' value='$fieldvalue' size='11' maxlength='10' $js>";
	$out .= "<a href=\"javascript:showCalendar('$fieldname')\">";
	$out .= "<img src='../library/calendario/calendario.gif' border='0'>";
	$out .= "</a>";
	return $out;
}

/*****************************************************************************************************
 funci�n para gerar campo de texto
 $fieldname : nome do campo que ser� criado
 $fieldvalue : valor inicial do campo
 $lenght : tamanho do campo
 $maxlenght : capacidade do campo
 $js : express�o javascript
 */
function textField($msjvalid, $fieldname, $fieldvalue="", $length=40, $maxlength=40, $js="") {
	$out = "";
        $fieldvalue=htmlspecialchars($fieldvalue,ENT_QUOTES);
        $out .= "<input type='text' name='$fieldname' id='$msjvalid' value=\"$fieldvalue\" size='$length' maxlength='$maxlength' onKeyPress='return formato(event,form,this,".$maxlength.")' $js>";
	return $out;
}


function searchField($msjvalid, $fieldname, $fieldvalue="", $length=40, $maxlength=40, $js="") {
	$out = "";
        $out .= "<input type='search' placeholder='Buscar...' name='$fieldname' id='$msjvalid' value=\"$fieldvalue\" size='$length' maxlength='$maxlength'  $js>";
	return $out;
}

function hiddenField($varName, $varValue, $msjvalid='') {
	$out .= "<input type=\"hidden\" name=\"$varName\" value=\"$varValue\" id=\"$msjvalid\">\n";
        return $out;
}

/*****************************************************************************************************
 funci�n para gerar campo de tipo numerico
 $fieldname : nome do campo que ser� criado
 $fieldvalue : valor inicial do campo
 $lenght : tamanho do campo
 $maxlenght : capacidade do campo
 $numdecimal: n�mero de decimales
 $coma: si coloca o no la coma
 $js : express�o javascript
 */
function numFieldx($msjvalid, $fieldname, $fieldvalue="", $length=40, $maxlength=40, $numdecimal=0, $coma=false, $js="") {
	$out = "";
	$out .= "<input type='number' name='$fieldname' id='$msjvalid' STYLE='text-align:right' value='$fieldvalue' size='$length' maxlength='$maxlength'"
	. " onFocus=\"replaceChars(this,',','')\"  onKeyPress='return formato(event,form,this,".$maxlength.",".$numdecimal.")' ";

	if($coma or strtoupper(substr($fieldname,0,1))=='Z')
	$out .= "onBlur=\"commaSplit(this,1,$maxlength,$numdecimal)\" ";

	$out .=  " $js>";

	return $out;
}

function numField($msjvalid, $fieldname, $fieldvalue="", $length=40, $maxlength=40, $numdecimal=0, $coma=false, $js="") {
	$out = "";
	$out .= "<input type='text' name='$fieldname' id='$msjvalid' STYLE='text-align:right' value='$fieldvalue' size='$length' maxlength='$maxlength'"
	. " onFocus=\"replaceChars(this,',','')\"  onKeyPress='return formato(event,form,this,$maxlength,$numdecimal)' ";

	if($coma or strtoupper(substr($fieldname,0,1))=='Z')
	$out .= "onBlur=\"commaSplit(this,1,$maxlength,$numdecimal)\" ";

	$out .=  " $js>";

	return $out;
}

/*****************************************************************************************************
 funci�n para gerar campo de password
 $fieldname : nome do campo que ser� criado
 $fieldvalue : valor inicial do campo
 $lenght : tamanho do campo
 $maxlenght : capacidade do campo
 $js : express�o javascript
 */
function passwordField($msjvalid, $fieldname, $fieldvalue="", $lenght=20, $maxlenght=20, $js="") {
	$out = "";
	$out .= "<input type='password' name='$fieldname' id='$msjvalid' value='$fieldvalue' size='$lenght' maxlenght='$maxlenght' $js>";
	return $out;
}

/*****************************************************************************************************
 funci�n para gerar campo de checkbox
 $fieldname : nome do campo que ser� criado
 $fieldvalue : valor inicial do campo
 $expr : express�o booleana que define se o checkbox est� marcado ou n�o
 $js : express�o javascript
 */
function checkboxField($msjvalid, $fieldname, $fieldvalue="", $expr, $js="") {
	$out = "";
	$checked = $expr?" checked":"";
	$out .= "<input type='checkbox' name='$fieldname' id='$msjvalid' value='$fieldvalue' $checked $js>";
	return $out;
}

/*****************************************************************************************************
 funcion para gerar campo file
 $fieldname : nome do campo que ser� criado
 $fieldvalue : valor inicial do campo
 $expr : express�o que retorna um boolean
 $js : express�o javascript
 */
function fileField($msjvalid, $fieldname, $fieldvalue="", $lenght=30, $js="",$path="") {
	$out = "";
	$out .= "<input type='hidden' name='Fi_".$fieldname."' value='$fieldvalue'>";
	$out .= "<input type='file' name='$fieldname' id='$msjvalid' size='$lenght' $js>";
	if (strlen(trim($fieldvalue))>0 && strpos($fieldvalue,"standar")==0) {
                if($path){
                    $out .= "<br><a download  href=\"".$path.$fieldvalue."\" ><b>".$fieldvalue."</b>&nbsp;</a>". "<input type='checkbox' name='".$fieldname."_excluir' value='1'> ".FILEFIELD_REMOVER;
                }else{
                    $out .= "<br><b>".$fieldvalue."</b>&nbsp;". "<input type='checkbox' name='".$fieldname."_excluir' value='1'> ".FILEFIELD_REMOVER;
                    
                }
	}
	return $out;
}


function fileField2($msjvalid, $fieldname, $fieldvalue="", $lenght=30, $js="", $multiple='multiple') {
	$out = "";
	$out .= "<input type='file' name='$fieldname' id='$fieldname' size='$lenght' $js $multiple>";
	return $out;
}

function autocompleteField($msjvalid, $fieldname, $style, $fieldvalue="") {
        $out  = "<ol><li id=\"facebook-list\" class=\"input-text\">";
        $out .= "<input type=\"text\" name=\"$fieldname\" id=\"$msjvalid\"  value=\"$fieldvalue\"  />";
        $out .= "<div id=\"$style\">"; //si hay mas de un est
        $out .= "<div class=\"default\"></div>";
        $out .= "<ul class=\"feed\"></ul>";
        $out .= "</div>";
        $out .= "</li></ol>";
	return $out;
}


/*****************************************************************************************************
 funci�n para gerar campo textarea com controle de caracteres via javascript
 $nome_campo : nome do campo que ser� criado
 $valor_inicial : valor inicial do campo
 $num_linhas : n�mero de linhas do campo
 $num_colunas : n�mero de colunas do campo
 $maximo : quantidade m�xima de caracteres
 */
function textAreaField($msjvalid, $nome_campo, $valor_inicial="", $num_linhas=5, $num_colunas=40, $maximo=200, $readonly='', $contador=1, $class='') {
	$str = "<textarea ".
	       "name='$nome_campo' ".
		   "id='$msjvalid' ".
                   "class='$class' ".
		   "rows='$num_linhas' ".
		   "cols='$num_colunas' ";

	if($contador==1){
		//			  	$str.=  "onKeyPress='return formato(event,form,this)' and textCounter(this,this.form._counter".$nome_campo.",$maximo);' ";
		$str.=  "onKeyPress='textCounter(this,this.form._counter".$nome_campo.",$maximo);' ";
		$str.=  "onKeyUp='textCounter(this,this.form._counter".$nome_campo.",$maximo);' ";
	}
	$str.=  " $readonly >".
	$valor_inicial.
		   "</textarea><br>";

	if($contador==1)
            $str.=
				   	"<input class='DataTD BackTD' ".
			   		"style='border: 0px; text-align: right' ".
				   	"type='text' ".
				   	"name='_counter".$nome_campo."' ".
				   	"maxlength='4' readonly size='4' value='".($maximo-strlen($valor_inicial))."'> ".TEXTAREA_RESTANTES;
	return $str;
}

function dateField2($msjvalid, $fieldname, $fieldvalue="",  $js="") {
	$out = "";
	$out .= "<input type='date' name='$fieldname' id='$msjvalid' value='$fieldvalue'  $js>";
	return $out;
}
/*****************************************************************************************************
 funci�n para gerar link html
 */
function addLink($titulo, $url, $alt="", $target="content", $class="link", $data_id="") {
        if(strpos($url,"avascript")){
            $out = "<a title=\"$alt\" class=\"$class\" href='#' onClick=\"$url\" target=\"$target\" >$titulo</a>";
        }
	else{
            $out = "<a title=\"$alt\" class=\"$class\" href=\"$url\" target=\"$target\" data-id=\"$data_id\">$titulo</a>";
        }
	return $out;
}



/*****************************************************************************************************
 Tratamento da data para formatos apenas num�ricos
 Recebe uma data no formato yyyymmdd, coloca as barras e ordena em dd/mm/yyyy
 */
function dtod($data) {
	$data_ano = substr($data,2,2);
	$data_mes = substr($data,5,2);
	$data_dia = substr($data,8,2);
	return $data_dia."/".$data_mes."/".$data_ano;
}

/*****************************************************************************************************
 Converte yyyy-mm-dd hh:mm:ss em dd/mm/yyyy hh:mm:ss
 funci�n auxiliar, use stod()
 */
function _stodt($str) {
	$aStr = explode(" ",$str);

	$d = $aStr[0];
	$t = $aStr[1];
	$aD = explode("-",$d);

	$datetime = $aD[2] . "/" . $aD[1] . "/" . $aD[0] . " " . $t;
	return $datetime;
}

/*****************************************************************************************************
 Converte dd/mm/yyyy hh:mm:ss em yyyy-mm-dd hh:mm:ss
 funci�n auxiliar, use dtos()
 */
function _dttos($datetime,$char='-',$corta=false) {
        if($datetime){
            $aDT = explode(" ",$datetime);
            $s = $aDT[0];
            $t = $aDT[1];
            $aS = explode($char,$s);
            if($corta){
                $str = $aS[2]. "/" . $aS[1] . "/" . substr($aS[0],2) . " " . substr($t,0,8);
            }else{
                $str = $aS[2] . "/" . $aS[1] . "/" . $aS[0] . " " . substr($t,0,8);
            }
        }else{
            $str = "";
        }
	return $str;
}

/*****************************************************************************************************
 converte DD/MM/AAAA en AAAA-MM-DD
 */
function stod($texto,$char="/") {
	if ($texto=="") return "";
	if (strlen($texto)>10) {
		return _stodt($texto);
	} else {
		$data = explode("/",$texto);
		return $data[2] . $char . $data[1] . $char . $data[0];
	}
}

/*****************************************************************************************************
 converte AAAA-MM-DD para DD/MM/AAAA
 */
function dtos($data,$char='/',$corta=false) {
	if ($data=="") return "";
	if (strlen($data)>10) {
		return _dttos($data,$char,$corta);
	} else {
		$texto = explode("-",$data); //en php las consultas con fechas, postgres las devuelve en yyyy-mm-dd (OJO CON EL GUION)
                if($corta)
                    return $texto[2] . $char . $texto[1] . $char . substr($texto[0],2,2);
                else
                    return $texto[2] . $char . $texto[1] . $char . $texto[0];
	}
}

function validateDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

/*****************************************************************************************************
 ayuda on-line
 Gera um �cone na p�gina que quando clicado abre uma janela popup
 $titulo : t�tulo da ayuda
 $msg : texto da ayuda
 */
function help($titulo="",$msg="",$op=1) {
	$out = "";
	$out .= "&nbsp;<img title=\"Clique aqui para obter ayuda\" style=\"cursor: pointer\" align=middle src=\"" . iif($op,"==",2,"../../img/help.gif",HELP_IMAGEM) . "\" ".
           "onclick=\"hint=window.open('', 'hint', 'width=400, height=300, resizable=no, scrollbars=yes, top=80, left=450');".
           "hint.document.write('<HTML><HEAD><TITLE>AYUDA</TITLE></HEAD><BODY onClickx=\'self.close();\' style=\'background-color: ".HELP_CORFUNDO."\'>');".
           "hint.document.write('<P style=\'font-size: ".HELP_TAMANHOTITULO."; font-weight: bold; color: ".HELP_CORTITULO."; font-family: ".HELP_FONTTITULO."\'>');".
           "hint.document.write( '$titulo' );".
           "hint.document.write('</P>');".
           "hint.document.write('<P style=\'font-size: ".HELP_TAMANHOTEXTO."; color: ".HELP_CORTEXTO."; font-family: ".HELP_FONTTEXTO."\'>');".
           "hint.document.write( '$msg' );".
           "hint.document.write('</P>');".
           "hint.document.write('</BODY></HTML>');".
           "\">&nbsp";
	return $out;
}

/*****************************************************************************************************
 Diseño de título da página
 */
function pageTitle($titulo,$subtitulo="",$img="",$classTitulo='titulo') {
	if ($titulo != "") {
		if($img)
                    echo "<div class='$classTitulo'><img src=\"$img\">&nbsp;$titulo</div>";
		else
                    echo "<div class='$classTitulo'>$titulo</div>";
	}
	if ($subtitulo != "") {
		echo "<div class='subtitulo'>$subtitulo</div>";
	}
	//echo "<hr noshade class='linha'>";
}

/*****************************************************************************************************
 muestra de alert en javascript (cuando el mensaje incluye comillas dobles)
 */
function alertx($msg,$exit=1) {
	$msg=str_replace("\n","\\n",$msg); // Para controlar los retornos de carro que devuelve el postgres
	$msg=str_replace("\"","\'",$msg); // Para controlar los retornos de carro que devuelve el postgres
	echo "<script language='JavaScript'>";
	echo "alert(\"$msg\");";
	echo "</script>";
	if($exit) // recibe $exit=0 para el caso donde se llama al alert y deseamos que se sigan ejecutando las siguientes l�neas. Ejm. AvanzLookup
	exit;
}

function alert($msg,$exit=1) {
        $msg=str_replace("\n","\\n",$msg); // Para controlar los retornos de carro que devuelve el postgres
	$msg=str_replace("\"","\'",$msg); // Para controlar los retornos de carro que devuelve el postgres


        //$posIni = strpos($msg, 'violates check constraint');
        $posFin = strpos($msg, 'CONTEXT');
        
        if($posFin <> false ){
            $msg = substr($msg, 0, $posFin);
        }else
           $msg=substr($msg,0,250);
        
	echo "<script language='JavaScript'>";
	echo "alert(\"".$msg."\");";
	echo "</script>";
	if($exit) // recibe $exit=0 para el caso donde se llama al alert y deseamos que se sigan ejecutando las siguientes líneas. Ejm. AvanzLookup
		exit;
}

/*****************************************************************************************************
 Provoca redirect via javascript
 */
function redirect($url, $target="content") {
	echo "<script language='JavaScript'>";
	echo "if(top==self) top.location='../modulos/index.php';";
	echo "else parent.$target.document.location='$url';";
	echo "</script>";
}

/*****************************************************************************************************
 crea un scroll, con los datos enviado
 */

function scrollBlock($id="", $contenido="", $altura="300px", $anchura="100%", $class="") {
	$out  = "<div id=\"$id\" class=\"$class\" style='height: $altura; width: $anchura; ";
	$out .= "overflow: auto; border: 0px; padding: 1px;'>";
	$out .= $contenido;
	$out .= "</div>";
	return $out;
}


/*****************************************************************************************************
 if lineal
 */
function iif($var1,$cond,$var2,$res1,$res2)
{
	$_eval="if(\"$var1\"". $cond ." \"$var2\") { \$solution = \$res1  ;} else { \$solution = \$res2 ;}";
	eval($_eval);
	return($solution);
}

/*****************************************************************************************************
 */
function dateFormat($input_date, $input_format, $output_format) {
	if(!$input_date)
	return '';

	preg_match("/^([\w]*)/i", $input_date, $regs);
	$sep = substr($input_date, strlen($regs[0]), 1);
	$label = explode($sep, $input_format);
	$value = explode($sep, $input_date);
	$array_date = array_combine($label, $value);
	if (in_array('Y', $label)) {
		$year = $array_date['Y'];
	} elseif (in_array('y', $label)) {
		$year = $year = $array_date['y'];
	} else {
		return false;
	}

	$output_date = date($output_format, mktime(0,0,0,$array_date['m'], $array_date['d'], $year));
	return $output_date;
}


/*****************************************************************************************************
 Classe para generar un arbol
 */
class Tree {
	var $title;
	var $nameCampoForm;
	var $captionCampoForm;
	var $valorCampoForm;
	var $nameTabla;
	var $nameCampoClave;
	var $nameCampoMuestra;
	var $nameCampoDepen;
	var $valorCampoFormDummy;
	var $ExcluNivelMax;
	var $iniStruct;
	var $setBuscar;
	var $size;
	var $width;
	var $height;
	var $sql;
	var $setTreeAvanz;
	var $page;

	function __construct() {
		$this->upCase=true;
		$this->setBuscar=true;
		$this->setTreeAvanz=false;
		$this->size=LOOKUP_FIELDSIZE;
		$this->width=500;
		$this->height=520;
	}

	function setTreeAvanz($set) {
		$this->setTreeAvanz=$set;
		$this->page="'/sisadmin/intranet/library/treeAvanz.php?p=0',";
	}

	// define o nome do campo do formul�rio
	function setNameCampoForm($caption,$umNome) {
		$this->captionCampoForm = $caption;
		$this->nameCampoForm = $umNome;
	}

	// define o nome do campo auxiliar que ser� exibido no lookup
	function setNameCampoDepen($umNome) {
		$this->nameCampoDepen = $umNome;
	}

	// define o t�tulo que aparecer� na janela de lookup
	function setTitle($umTitulo) {
		$this->title = $umTitulo;
	}

	// define o valor inicial do campo do formul�rio
	function setValorCampoForm($umValor,$Valor='') {
		$this->valorCampoForm = $umValor;
		if ($this->setTreeAvanz){ //si  utiliza el tree mejorado
			//			$this->valorCampoFormDummy = $Valor;
			$this->valorCampoFormDummy = htmlspecialchars($Valor,ENT_QUOTES); // para el problema de las comillas
		} else {
			// He colocado comillas simples al dato '$this->valorCampoForm' para que funcione cuando el campo clave es varchar o text,
			// esto no afecta si el campo es integer o serial ya que Postgres lo interpreta como tal.
			$sql = "select $this->nameCampoMuestra,$this->nameCampoClave from
			$this->nameTabla where $this->nameCampoClave='$this->valorCampoForm'";
			$this->sql = $sql;
			$this->valorCampoFormDummy = getDbValue($sql);
		}
	}

	// define o nome da tabela que ser� exibida no lookup
	function setNameTabla($umNome) {
		$this->nameTabla = $umNome;
	}

	// define o nome do campo chave que ser� devolvido ao campo do formul�rio
	function setNameCampoClave($umNome) {
		$this->nameCampoClave = $umNome;
	}

	// define o nome do campo que ser� exibido no lookup
	function setNameCampoMuestra($umNome) {
		$this->nameCampoMuestra = $umNome;
	}
	// define si en el arbol solo se elije el nivel m�s detallado.
	function setExcluNivelMax($nivelMax) {
		$this->ExcluNivelMax=$nivelMax;
	}
	// define desde donde se construye el arbol .
	function setIniStruct($Struct) {
		$this->iniStruct=$Struct;
	}
	// define si se muestra el icono de busqueda
	function setBuscar($Buscar) {
		$this->setBuscar=$Buscar;
	}
	// define el tama�o de la caja de texto
	function setSize($size) {
		$this->size=$size;
	}

	// define el ancho de la ventana
	function setWidth($width) {
		$this->width=$width;
	}


	// define la altura de la ventana
	function setHeight($height) {
		$this->height=$height;
	}

	// define el nombre de la p�gina
	function setNamePage($namePage) {
		$this->page=$namePage;
	}

	// retorna o bloco HTML que monta o campo lookup
	function writeHTML() {
		$out = "";
		$out .= "<input type='hidden' name='__Change_".$this->nameCampoForm."' id='__Change_".$this->captionCampoForm."' value=0>";
		$out .= "<input type='hidden' name='".$this->nameCampoForm."' id='".$this->captionCampoForm."' value='".$this->valorCampoForm."'>";
		$out .= "<input type='text' name='_Dummy".$this->nameCampoForm."' id='".$this->captionCampoForm."' value='".$this->valorCampoFormDummy."' size='".$this->size."' readonly>";
		if($this->setBuscar){
			$out .= "<img title=\"Clique aqui para abrir el arbol de registros\" align='middle' style='cursor: pointer' src='". LOOKUP_IMAGEM ."' onClick=\"tree(";
			$out .= $this->page;
			$out .= "'".$this->nameCampoForm."', '".$this->nameTabla."', '".$this->nameCampoClave."', '".$this->nameCampoMuestra."', '".$this->nameCampoDepen."', '".$this->ExcluNivelMax."', '".$this->iniStruct."', '".$this->title."','".$this->width."', '".$this->height."'";
			$out .= ")\">";
		}
		return $out;
	}
}



/*****************************************************************************************************
 coloca una imagen en un elemento div, de tal manera q pueda ubicarse en cualquier parte de la hoja
 $fieldname: nombre del campo
 $image: archivo de imagen
 $ImgWidth: ancho de la imagen
 $ImgHeight: alto de la imagen
 $DivTop: alto en q se colocara el DIV en ralcion al borde superior de la hoja
 $DivWidth: ancho del div
 $DivHeight:	alto del div
 $js : expresi�n javascript
 */
function divImage($fieldname, $image, $ImgWidth = 115, $ImgHeight = 130, $DivTop = 10, $DivWidth = 100, $Divleft = 180, $classFoto = "contenedorfoto", $js='',$path='') {
	if($image){	
	    $result.="<div class=\"$classFoto\" >".
				"<img src=\"$path$image\" id=\"div_img_$fieldname\" width=\"$ImgWidth\" height=\"$ImgHeight\"  style=\"border-color:#7F9DB9\">";
	}
	
	if($fieldname){
            $result.= fileField('fil_img_'.$fieldname,$fieldname, $image, 80, $js, $path);
        }

	$result.="</div>";

	return $result;
}

function divHtml($html, $DivTop, $DivWidth, $Divleft, $class='', $js='') {
	$result="<div align=\"center\" style=\"{position:absolute;  width:$DivWidth; left:$Divleft; margin-top:$DivTop}\">".
		"<div class=\"$class\" >".
	$html.
		"</div>";
	$result.="</div>";
	return $result;
}


function verif_framework(){
	//-- ASEGURA QUE LA PAGINA SE HAYA CARGADO DESDE EL INDEX.
	echo "<script language='JavaScript'>";
	echo "if(top==self) top.location='../index.php'";
	echo "</script>";
}


/*****************************************************************************************************
 Classe para gerar campo lookup avanzadp
 */
class AvanzLookup {
	var $title;
	var $nameCampoForm;
	var $nameHideCampoForm;
	var $captionCampoForm;
	var $valorCampoForm;
	var $valorCampoFormDummy;
	var $size;
	var $width;
	var $height;
	var $page; /* P�gina que se cargar� en la ventana popup */
	var $fieldID;
        var $readOnly;
	private $NewWin;
	private	$classThickbox;
	/* Controla el tipo de ventana que se presentar�
	 (default)false --> Ventana externa
	 true  --> Ventana interna (usa la librer�a http://jquery.com/demo/thickbox/)
	 */

	function __construct() {
		$this->width=500;
		$this->height=520;
		$this->size=LOOKUP_FIELDSIZE;
		$this->NewWin=false;
		$this->classThickbox='thickbox';
                $this->readOnly=false;
	}

	// define o nome do campo do formul�rio
	function setNameCampoForm($caption,$nameCampo) {
		$this->captionCampoForm = $caption;
		$this->nameCampoForm = $nameCampo;
	}

	// define un cmp
	function setHideNameCampoForm($nameHideCampo) {
		$this->nameHideCampoForm = $nameHideCampo;
	}

	// define o t�tulo que aparecer� na janela de lookup
	function setTitle($umTitulo) {
		$this->title = $umTitulo;
	}

	// define o valor inicial do campo do formul�rio
	function setValorCampoForm($umValor,$Valor) {
		$this->valorCampoForm = $umValor;
		//			$this->valorCampoFormDummy = $Valor;
		$this->valorCampoFormDummy = htmlspecialchars($Valor,ENT_QUOTES); // para el problema de las comillas
	}

	// define el nombre de la p�gina
	function setNamePage($namePage) {
		$this->page=$namePage;
	}

	// define el tama�o de la caja de texto
	function setSize($size) {
		$this->size=$size;
	}

	// define el ancho de la ventana
	function setWidth($width) {
		$this->width=$width;
	}

	// define la altura de la ventana
	function setHeight($height) {
		$this->height=$height;
	}
	//funciona q agrega un campo codigo al lookup
	function addFieldID($field) {
		$this->fieldID=$field;
	}

	// define tipo de ventana emergente
	function setNewWin($NewWin=false) {
		$this->NewWin = $NewWin;
	}
        function setReadOnly() {
		$this->readOnly = true;
	}
	// define el nombre de css a aplicarse en link q invoca al thickbox
	//este metodo se utiliza cuando se trabaja con xajax y hay necesidad de utilizarlo en varias funciones
	//ver ejemplo en la pagina sislogal/sislogalMovimientosOrdCompra_edicion.php
	function setClassThickbox($classThickbox='thickbox') {
		$this->classThickbox = $classThickbox;
	}
        
        function setClassBootstrap($classBootstrap='ls-modal') {
		$this->classThickbox = $classBootstrap;
	}

	// retorna o bloco HTML que monta o campo lookup
	function writeHTML() {
		$out = "";
		$out .= "<input type='hidden' name='__Change_".$this->nameCampoForm."' id='__Change_".$this->captionCampoForm."' value=0>"; // Para poder controlar un evnto Change en este objeto, ya que no es posible escribir nada en el.

		// si se adiciona un campo de busqueda, entonces se agrega un objeto DIV (le coloca el nombre del campo), para su funcionamiento con AJAX
		if(strlen($this->fieldID)){
			$out .= $this->fieldID."&nbsp;";
		}
		else{
			$out .= "<input type='hidden' name='".$this->nameCampoForm."' id='".$this->captionCampoForm."' value='".$this->valorCampoForm."'>"; //campo q contendra el valor a grabar
		}

		$out .= "<input type='text' name='_Dummy".$this->nameCampoForm."' id='_Dummy".$this->nameCampoForm."' value='".$this->valorCampoFormDummy."' size='".$this->size."' readonly>";
                
                if($this->readOnly==false){
                    if ($this->NewWin){ /* para uso de la ventana thinckbox */
                     $out .= "<a href=".PATH_INC."auxiliar.php?pag=$this->page,nomeCampoForm=$this->nameCampoForm&height=$this->height&width=$this->width class=\"$this->classThickbox\" >
                            <img title=\"Clique aqui para abrir la lista de registros\" align='middle' style='cursor: pointer; border:0px' src='".LOOKUP_IMAGEM."'/></a>";
                    }else{ /* Uso de ventana externa */
                            $out .= "<img title=\"Clique aqui para abrir la lista de registros\" align='middle' style='cursor: pointer' src='". LOOKUP_IMAGEM."'" ;
                            $out .= "onClick=\"abreJanelaAuxiliar(";
                            $out .= "'".$this->page.",nomeCampoForm=".$this->nameCampoForm.",titulo=".$this->title."','".$this->width."', '".$this->height."'";
                            $out .= ")\">";
                    }
                }
		return $out;
	}

}


/* para detectar el ip del visitante */
function detectar_ip()
{
	if(!empty($_SERVER['HTTP_X_FORWARDER_FOR']))
	$ip = $_SERVER['HTTP_X_FORWARDER_FOR'];

	elseif(!empty($_SERVER['HTTP_VIA']))
	$ip = $_SERVER['HTTP_VIA'];

	elseif(!empty($_SERVER['REMOTE_ADDR']))
	$ip = $_SERVER['REMOTE_ADDR'];

	else
	$ip = '1.1.1.1'; // Desconocido, no logr� identificarse su ip
	return $ip;
}


/* Para generar n�meros aleatorios */
function random(){
	mt_srand ((double)microtime()*1000000);
	$maxran = 1000000;
	$random_num = mt_rand(0, $maxran);
	return ($random_num);
}

/*  Para abrir un ventana popup desde PHP
 En la p�gina desde donde se llama la funci�n,
 debe crearse una funci�n en javascript AbreVentana()
 */
function AbreVentana($sURL,$Handle){
	echo "<"."script".">\n";
	echo "AbreVentana(".'"'.$sURL.'","'.$Handle.'"'.")\n";
	echo "<"."/script".">\n";
}

//funcion q suma fechas segun el numero de dias enviado,
//la fecha se recibe en formato d/m/y   y se devuelve en d/m/y
function sumaFechas($Fecha,$nDias,$operador='+'){
	$data = explode("/",$Fecha);
        if($operador=='+')
        {
            $next = mktime(0,0,0,$data[1],$data[0]+$nDias,$data[2]);
        }else{
            $next = mktime(0,0,0,$data[1],$data[0]-$nDias,$data[2]);            
        }
	return date("d/m/Y",$next);
}

//funcion q devuelve parte de una fecha
//la fecha se recibe en formato d/m/y   y se devuelve  d,m, y
function partFecha($Fecha,$parte){
	$data = explode("/",$Fecha);

	if($parte=='d')
            return $data[0];

	if($parte=='m')
            return $data[1];

	if($parte=='Y')
            return $data[2];
}


//funcion q calcaula el tiempo en a�os, meses y dias, especial para calculo de edad
//la fecha se recibe en y/m/d
function calcTiempo($Fecha,$style=1,$edad=''){
	if ($Fecha!=date('d/m/Y')){
            $edad=getDbValue("SELECT age(current_date,'$Fecha')");
	}
	
	if($style==1){
            $edad=str_replace('years','A&ntilde;os',str_replace('mons','Meses',str_replace('days','Dias',$edad)));
            $edad=str_replace('year','A&ntilde;o',str_replace('mon','Mes',str_replace('day','Dia',$edad)));
	}else{
            if($style==2){
                $edad=str_replace(' years ','A.',str_replace(' mons ','M.',str_replace(' days ','D.',$edad)));
                $edad=str_replace(' year ','A.',str_replace(' mon ','M.',str_replace(' day ','D.',$edad)));
                $edad=str_replace(' years','A.',str_replace(' mons','M.',str_replace(' days','D.',$edad)));
                $edad=str_replace(' year','A.',str_replace(' mon','M.',str_replace(' day','D.',$edad)));
            }else{
                $edad=str_replace('years','.',str_replace('mons','.',str_replace('days','',$edad)));
                $edad=str_replace('year','.',str_replace('mon','.',str_replace('day','',$edad)));
                $edad=explode(".",$edad);
                $edad=str_pad(trim($edad[0]),2,'0',STR_PAD_LEFT).'.'.str_pad(trim($edad[1]),2,'0',STR_PAD_LEFT).'.'.str_pad(trim($edad[2]),2,'0',STR_PAD_LEFT);
            }
        }
	return(trim($edad,'.'));
}


/*funcion que soluciona el problema de las comillas */
function especialChar($texto) {
	/*reemplaza la comilla doble x comilla simple ->&#039:comilla doble */
	return(str_replace("&#039;","\'",htmlspecialchars($texto,ENT_QUOTES)));

}

function mu_sort ($array, $key_sort, $asc_desc=0) { // start function

	$key_sorta = explode(",", $key_sort);
	$keys = array_keys($array[0]);
	// sets the $key_sort vars to the first
	for($m=0; $m < count($key_sorta); $m++){ $nkeys[$m] = trim($key_sorta[$m]); }

	$n += count($key_sorta);    // counter used inside loop

	// this loop is used for gathering the rest of the
	// key's up and putting them into the $nkeys array
	for($i=0; $i < count($keys); $i++){ // start loop

		// quick check to see if key is already used.
		if(!in_array($keys[$i], $key_sorta)){

			// set the key into $nkeys array
			$nkeys[$n] = $keys[$i];

			// add 1 to the internal counter
			$n += "1";

		} // end if check

	} // end loop

	// this loop is used to group the first array [$array]
	// into it's usual clumps
	for($u=0;$u<count($array); $u++){ // start loop #1

		// set array into var, for easier access.
		$arr = $array[$u];

		// this loop is used for setting all the new keys
		// and values into the new order
		for($s=0; $s<count($nkeys); $s++){

			// set key from $nkeys into $k to be passed into multidimensional array
			$k = $nkeys[$s];

			// sets up new multidimensional array with new key ordering
			$output[$u][$k] = $array[$u][$k];

		} // end loop #2

	} // end loop #1

	switch($asc_desc) {
		case "1":
			rsort($output); break;
		default:
			sort($output);
	}


	// return sorted array
	return $output;
}
/*
 funcion que devuelve el nombre del mes
 ejemplo: list_mes(11), devuelve NOVIEMBRE
 */

function list_mes($mes){
	switch($mes){
		case '1':
			$nameMes='ENERO';
			break;
		case '2':
			$nameMes='FEBRERO';
			break;
		case '3':
			$nameMes='MARZO';
			break;
		case '4':
			$nameMes='ABRIL';
			break;
		case '5':
			$nameMes='MAYO';
			break;
		case '6':
			$nameMes='JUNIO';
			break;
		case '7':
			$nameMes='JULIO';
			break;
		case '8':
			$nameMes='AGOSTO';
			break;
		case '9':
			$nameMes='SEPTIEMBRE';
			break;
		case '10':
			$nameMes='OCTUBRE';
			break;
		case '11':
			$nameMes='NOVIEMBRE';
			break;
		case '12':
			$nameMes='DICIEMBRE';
			break;
	}
	return($nameMes);
}

/*funcion que devuelve el ultimo dia de un mes en un periodo*/
function ultimo_dia($mes,$ano){
	return strftime("%d", mktime(0, 0, 0, $mes+1, 0, $ano));
}

/*
 funcion que devuelve el tiempo en formato HH:MM, se le envia la cantidad de minuitos
 ejemplo ->convHHMM(520), devuelve 08:40
 */
function convHHMM($mm=0){
	return(str_pad(intval($mm/60),2,"0",STR_PAD_LEFT).':'.str_pad($mm % 60,2,"0",STR_PAD_LEFT));
}

/*****************************************************************************************************
 Para mostrar u ocultar un Wait al entrar en un proceso
 txtwait --> Aqu� recibo el texto o c�digo html que deseo se muestre en el DIV 'procesando'
 Formas de llamar:
 wait('<img src="../img/ajax-loader.gif" />')  Para mostrar la animaci�n
 .........
 .........
 wait('')  Para eliminar la eliminaci�n, luego que termina el proceso

 */
function wait($txtwait="") {
	echo "<script language='JavaScript'>";
        echo "for(a=0;a<top.frames.length;a++){
                    if(top.frames[a].name=='menu_left')
                        break;
                }";
	echo "top.frames[a].document.getElementById('procesando').innerHTML = '$txtwait'";
	echo "</script>";
}


/**********************************************************************************
 funcion que devuelve una hora en formato de 00-23 a formato de 00-12, incluye AM/PM
 el parametro $hora es recibido asi: '23:24', se devuelve '11:24 PM'
 '13:15', se devuelve '01:15 PM'
 **********************************************************************************/
function getTurno($hora){
	//$hora=getDbValue("SELECT to_char(to_timestamp('05 Dec 2000 $hora', 'DD Mon YYYY HH:MI'),'HH12:MI:AM')");
	//return($hora);
	return(date('h:i A',strtotime($hora)));
}

/*****************************************************************************************************
 Para escribir en un archivo texto
 $archivo --> Archivo donde se va a escribir
 $txt --> Texto a excribir
 Ejmeplo:
 EscribeTxt("../modulos/debug_ajax.txt",$sqlpc);
 */
function EscribeTxt($archivo,$txt="") {
	// Abrimos el archivo
	$abre = fopen($archivo, "w");
	// Y reemplazamos por la nueva cantidad de visitas
	$grabar = fwrite($abre, $txt);
	// Cerramos la conexi�n al archivo
	fclose($abre);
}


/*tratamiento de variables pasadas por la url entre paginas*/
class manUrlv1 {
	var $url = array();

	function __construct() {
		//$this->url = ($_GET) ;
		$this->retrievCurrUrl();
	}

	function retrievCurrUrl() {
		$this->url = ($_GET) ;
		$this->removePar('id');//OJO no recibe el indice 'id'
	}

	//agrega un elemento
	function addParComplete($kiave,$valore){
		$this->url[$kiave]=$valore;
	}

	//retorna el valor de una clave
	function getValuePar($par) {
		reset($this->url);

		$array=$this->getUrl();
		foreach($array as $key => $value)
		if ($key == $par) return $value;
	}


	//remueve un elemento
	function removePar($par) {
		$num = $this->getPosPar($par);

		if (($num >=0) && (isset($num) != false)) {
			//print_r($this->url);
			//echo "trovato e cancellato";
			array_splice($this->url,$num,1);
		}

		//print_r($this->url);
	}

	//ritorna l'url costruito
	function getUrl() {
		return $this->url;
	}

	//prende un url esterno
	function setUrlExternal($p_url){
		$this->url =  $p_url;
	}


	//cicla l'array
	function loopArray(&$kiave,&$valore) {
		list($kiave,$valore)=each($this->url);
		return $kiave;
	}

	//riporta il cursore al primo elemento
	function resetPos() {
		reset($this->url);
	}

	//ritorna ad uno a uno tutti gli
	//elementi nel vettore
	function getNext() {
		$this->loopArray($kiave,$valore);
		$ret = array();
		if ($kiave != "") {
			$ret[] = $kiave;
			$ret[] = $valore;
		}else
		$ret = "";

		return $ret;
	}

	//ritorna la kiave di una valore
	function getKeyPar($par) {
		reset($this->url);

		$array=$this->getUrl();
		foreach($array as $key => $value)
		if ($value == $par) return (string)$key;
	}

	//ritorna la posizione di una chiave
	function getPosPar($par) {
		reset($this->url);
		$i=0;
		$array=$this->getUrl();
		foreach($array as $key => $value){
			if ($key == $par) return $i;
			$i++;
		}
	}

	//reemplaza un elemento
	function replaceParValue($par,$value) {
		$this->removePar($par);
		$this->addParComplete($par,$value);
	}



	//rimuove tutti gli elementi
	function removeAllPar($start=1) {
		array_splice($this->url,$start,count($this->url));
	}


	function buildPars($withAsk = true,$char='&') {
		reset($this->url);

		if ($withAsk == true) $query ="?";
		else  $query = "";

		$array=$this->getUrl();
		foreach($array as $key => $value)
		$query .= $key."=".$value.$char;

		$pos = strrpos($query,$char);
		if ($pos>0) $query=substr($query,0,$pos);
		return $query;
	}

};

function encodeArray($vector){
	$vector = serialize($vector);
	$vector = urlencode($vector);
	return $vector;
}

function decodeArray($vector){
	$vector = stripslashes($vector);
	$vector = urldecode($vector);
	$vector = unserialize($vector);
	return $vector;
}

function inlist($valor,$lista){
	$lista = explode(",",$lista);
	if (in_array ($valor, $lista))
            return true;
	else
            return false;

}

/*
 * se recibe la fecha en formato d/m/Y
 */
function diaSemana($date){
	$fecha = explode("/",$date); //en php las consultas con fechas, postgres las devuelve en yyyy-mm-dd (OJO CON EL GUION)
	$dia =$fecha[0];
	$mes =$fecha[1];
	$anno=$fecha[2];

	$semana = 	date("D", mktime(0, 0, 0, $mes, $dia, $anno));

	$semanaArray = array( "Mon" => "Lunes", "Tue" => "Martes", "Wed" => "Miercoles", "Thu" => "Jueves", "Fri" => "Viernes", "Sat" => "Sábado", "Sun" => "Domingo", );

	$mesReturn = strtolower(list_mes($mes));

	$semanaReturn = $semanaArray[$semana];

	return $semanaReturn." ".$dia." de ".$mesReturn." del ".$anno;
}


function formateacuenta($mvalor,$mspace,$char="&nbsp;"){
	/* Formatea el campo Cuenta
	 mvalor --> La cuenta que deseamos formatear
	 mspace --> true --> Coloca las sangrias correspondientes
	 false --> No coloca ninguna sangr�a, solo considera los puntos para el formateo
	 char   --> Caracter de espacio que se aplicar� a la izquierda de las cuentas
	 "&nbsp;" --> Se aplica para una impresion en HTML
	 " "      --> Se aplica para una impresi�n en PDF
	 */
	$x=strlen($mvalor);
	$mvalor2=$mvalor;
	$loNg=14;
	$_Space=2;

	if($x>5){
		$mvalor2 = substr($mvalor, 0, 4);
		for ($y = 4; $y <= $x-1; $y+=2) {
                        //$mvalor2 = $mvalor2.'.'.substr($mvalor, $y, 2);
			$mvalor2 = $mvalor2.''.substr($mvalor, $y, 2);
			$_Space = $_Space+3;
		}
	}elseif($x<=2)
            $_Space=0;

	if($mvalor and $mspace and $_Space){
		$mvalor2 = str_repeat($char, $_Space).$mvalor2;
	}elseif(strlen($mvalor2)>$loNg)
	$mvalor2 = substr($mvalor2, 0, $loNg);

	return $mvalor2;
}


function stock_formatxx($stock){
    $fraccion=explode(".",$stock);
    $len_decimal=strlen($fraccion[1]);
    //if($len_decimal>2) {$len_decimal=4;}
    return(number_format($stock,$len_decimal,'f',','));
}

function stock_format($stock){
    
    $haystack=$stock;
    $needle='.';
    $replace='f';
    
    $pos = strpos($haystack, $needle);
    if ($pos !== false) {
        $newstring = substr_replace($haystack, $replace, $pos, strlen($needle));
    } else{
        $newstring = $haystack;
    }
    return($newstring);
}

function calc_stock($p_numero,$p_numero_divisor){
    if($p_numero_divisor>1){
        $n_resultado=(string)intval($p_numero/$p_numero_divisor).
        iif($p_numero%$p_numero_divisor,">",0,'.'.(string)$p_numero%$p_numero_divisor,'');
    }else{
        $n_resultado=(string)$p_numero;
    }
    RETURN $n_resultado;
}


class colors{
    var $colors;
    
    function __construct(){
        $this->colors = array("*TITLE01*" => "Reds", 
                        '1' 	=> "#CD5C5C", //"Indian Red"
                        '2'         => "#F08080", //"Light Coral"
                        '3'         => "#FA8072", //"Salmon"
                        '4'         => "#E9967A", //"Dark Salmon"
                        '5'         => "#FFA07A", //"Light Salmon"
                        '6'         => "#DC143C", //"Crimson"
                        '7'         => "#FF0000", //"Red"
                        '8'         => "#B22222", //"Fire Brick"
                        '9'         => "#8B0000", //"Dark Red"

                    "*TITLE02*"	 => "Pinks",
                        '10'	 => "#FFC0CB", //"Pink"
                        '11'	 => "#FFB6C1", //"Light Pink"
                        '12'	 => "#FF69B4", //"Hot Pink"
                        '13'	 => "#FF1493", //"Deep Pink"
                        '14'	 => "#C71585", //"Medium Violet Red"
                        '15' 	 => "#DB7093", //"Pale Violet Red"

                    "*TITLE03*"	 => "Oranges",
                        '16'	 => "#FFA07A", //"Light Salmon"
                        '17'	 => "#FF7F50", //"Coral"
                        '18'	 => "#FF6347", //"Tomato"
                        '19'	 => "#FF4500", //"Orange Red"
                        '20'	 => "#FF8C00", //"Dark Orange"
                        '21'	 => "#FFA500", //"Orange"

                    "*TITLE04*"	 => "Yellows",
                        '22'	 => "#FFD700", //"Gold"
                        '23'	 => "#FFFF00", //"Yellow"
                        '24'	 => "#FFFFE0", //"Light Yellow"
                        '25'	 => "#FFFACD", //"Lemon Chiffon"
                        '26'         => "#FAFAD2", //"Light Goldenrod Yellow"
                        '27'	 => "#FFEFD5", //"Papaya Whip"
                        '28'	 => "#FFE4B5", //"Moccasin"
                        '29'	 => "#FFDAB9", //"Peach Puff"
                        '30'	 => "#EEE8AA", //"Pale Goldenrod"
                        '31'	 => "#F0E68C", //"Khaki"
                        '32'	 => "#BDB76B", //"Dark Khaki"

                    "*TITLE05*" 	 => "Purples",
                        '33' 	 => "#E6E6FA",  //"Lavender"
                        '34'	 => "#D8BFD8",  //"Thistle"
                        '35'	 => "#DDA0DD",  //"Plum" 
                        '36'	 => "#EE82EE",  //"Violet"
                        '37'	 => "#DA70D6",  //"Orchid"
                        '38'	 => "#FF00FF",  //"Fuchsia"
                        '39'	 => "#FF00FF",  //"Magenta"
                        '40'	 => "#BA55D3",  //"Medium Orchid"
                        '41'	 => "#9370DB",  //"Medium Purple"
                        '42'	 => "#8A2BE2",  //"Blue Violet"
                        '43'	 => "#9400D3",  //"Dark Violet"
                        '44'	 => "#9932CC",  //"Dark Orchid"
                        '45'	 => "#8B008B",  //"Dark Magenta"
                        '46'	 => "#800080",  //"Purple"
                        '47'	 => "#4B0082",  //"Indigo"
                        '48'	 => "#6A5ACD",  //"Slate Blue"
                        '49'	 => "#483D8B",  //"Dark Slate Blue"

                    "*TITLE06*"	 => "Greens",
                        '50'	 => "#ADFF2F", //"Green Yellow"
                        '51'	 => "#7FFF00", //"Chartreuse"
                        '52'	 => "#7CFC00", //"Lawn Green"
                        '53'	 => "#00FF00", //"Lime"
                        '54'	 => "#32CD32", //"Lime Green"
                        '55'	 => "#98FB98", //"Pale Green"
                        '56'	 => "#90EE90", //"Light	Green"
                        '57'    	 => "#00FA9A", //"Medium Spring Green"
                        '58'	 => "#00FF7F", //"Spring Green"
                        '59'	 => "#3CB371", //"Medium Sea Green"
                        '60'	 => "#2E8B57", //"Sea Green"
                        '61'	 => "#228B22", //"Forest Green"
                        '62'	 => "#008000", //"Green"
                        '63'	 => "#006400", //"Dark Green"
                        '64'	 => "#9ACD32", //"Yellow Green"
                        '65'	 => "#6B8E23", //"Olive Drab" 
                        '66'	 => "#808000", //"Olive"
                        '67'	 => "#556B2F", //"Dark Olive Green"
                        '68' 	 => "#66CDAA", //"Medium Aquamarine"
                        '69' 	 => "#8FBC8F", //"Dark Sea Green"
                        '70'	 => "#20B2AA", //"Light Sea Green"
                        '71'	 => "#008B8B", //"Dark Cyan"
                        '72'	 => "#008080", //"Teal"

                    "*TITLE07*"      => "Blues",
                        '73'	 => "#00FFFF",  //"Aqua"
                        '74'	 => "#00FFFF",  //"Cyan"
                        '75'	 => "#E0FFFF",  //"Light Cyan"
                        '76'	 => "#AFEEEE",  //"Pale Turquoise"
                        '77'	 => "#7FFFD4",  //"Aquamarine"
                        '78'	 => "#40E0D0",  //"Turquoise"
                        '79'	 => "#48D1CC", //"Medium Turquoise"
                        '80'	 => "#00CED1",  //"Dark Turquoise"
                        '81'	 => "#5F9EA0",  //"Cadet Blue"
                        '82'	 => "#4682B4",  //"Steel Blue"
                        '83'	 => "#B0C4DE",  //"Light Steel Blue"
                        '84'	 => "#B0E0E6",  //"Powder Blue"
                        '85'	 => "#ADD8E6",  //"Light Blue"
                        '86'	 => "#87CEEB",  //"Sky Blue"
                        '87'	 => "#87CEFA",  //"Light Sky Blue"
                        '88'	 => "#00BFFF",  //"Deep Sky Blue"
                        '89'	 => "#1E90FF",  //"Dodger Blue"
                        '90'	 => "#6495ED",  //"Cornflower Blue"
                        '91'	 => "#7B68EE",  //"Medium Slate Blue"
                        '92'	 => "#4169E1",  //"Royal Blue"
                        '93'	 => "#0000FF",  //"Blue"
                        '94'         => "#0000CD",  //"Medium Blue"
                        '95'	 => "#00008B",  //"Dark Blue"
                        '96'	 => "#000080",  //"Navy"
                        '97'	 => "#191970",  //"Midnight Blue"

                    "*TITLE08*"	 => "Browns",
                        '98'	 => "#FFF8DC", //"Cornsilk"
                        '99'	 => "#FFEBCD",  //"Blanched Almond"
                        '100'	 => "#FFE4C4", //"Bisque"
                        '101'	 => "#FFDEAD", //"Navajo White"
                        '102'	 => "#F5DEB3", //"Wheat"
                        '103'	 => "#DEB887", //"Burly Wood"
                        '104'	 => "#D2B48C", //"Tan"
                        '105'	 => "#BC8F8F", //"Rosy Brown"
                        '106'	 => "#F4A460", //"Sandy Brown"
                        '107'	 => "#DAA520", //"Goldenrod"
                        '108'	 => "#B8860B", //"Dark Goldenrod"
                        '109'	 => "#CD853F", //"Peru"
                        '110'	 => "#D2691E", //"Chocolate"
                        '111'	 => "#8B4513", //"Saddle Brown"
                        '112'	 => "#A0522D", //"Sienna"
                        '113'	 => "#A52A2A", //"Brown"
                        '114'	 => "#800000", //"Maroon"

                    "*TITLE09*"      => "Whites",
                        '115'        => "#FFFFFF", //"White"
                        '116'	 => "#FFFAFA", //"Snow"
                        '117'	 => "#F0FFF0", //"Honeydew"
                        '118'	 => "#F5FFFA", //"Mint Cream"
                        '119'	 => "#F0FFFF", //"Azure"
                        '120'	 => "#F0F8FF", //"Alice Blue"
                        '121'	 => "#F8F8FF", //"Ghost White"
                        '122'	 => "#F5F5F5", //"White Smoke"
                        '123'	 => "#FFF5EE", //"Seashell"
                        '124'	 => "#F5F5DC", //"Beige"
                        '125'	 => "#FDF5E6", //"Old Lace"
                        '126'	 => "#FFFAF0", //"Floral White"
                        '127'	 => "#FFFFF0", //"Ivory"
                        '128'	 => "#FAEBD7", //"Antique White"
                        '129'	 => "#FAF0E6", //"Linen"	
                        '130'	 => "#FFF0F5", //"Lavender Blush"
                        '131'	 => "#FFE4E1", //"Misty Rose"

                    "*TITLE10*"	 => "Greys",
                        '131'	 => "#DCDCDC", //"Gainsboro"
                        '132'	 => "#D3D3D3", //"Light Grey"
                        '133'	 => "#C0C0C0", //"Silver"
                        '134'	 => "#A9A9A9", //"Dark Gray"
                        '135'	 => "#808080", //"Gray"
                        '136'	 => "#696969", //"Dim Gray"
                        '137'	 => "#778899", //"Light Slate Gray"
                        '138'	 => "#708090", //"Slate Gray"
                        '139'	 => "#2F4F4F", //"Dark Slate Gray"
                        '140'	 => "#000000" //"Black"
                    );
        
    }
    
    function getColors(){
        return ($this->colors);
    }
}

function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
    $sort_col = array();
    foreach ($arr as $key=> $row) {
        $sort_col[$key] = $row[$col];
    }
    //uasort($sort_col, 'ordename');
    array_multisort($sort_col, $dir, $arr);
}

/*para envir/recibir arrays x la URL*/
function array_envia($array) { 
    $tmp = serialize($array); 
    $tmp = urlencode($tmp); 
    return $tmp; 
}

function array_recibe($url_array) { 
    $tmp = stripslashes($url_array); 
    $tmp = urldecode($tmp); 
    $tmp = unserialize($tmp); 
   return $tmp; 
}

class Dialog {
        var $id;
	var $type;
        var $mesage;
        var $objets;
        var $closeModal;
        var $button;
        var $modal;
        var $class_body;
        var $title;
        var $titleIcon;
        
	function __construct($id="", $type="warning") {
                $this->id = $id;
		$this->type = $type;
                $this->closeModal = false;
	}

	function addMessage($mesage) {
		$this->mesage .= $mesage;
        }
        
        function addObjets($objets) {
		$this->objets .= $objets;
        }
        
        function setCloseModal() {
		$this->closeModal = true;
        }
        
        function setModal($modal) {
		$this->modal = $modal;  //modal-sm   modal-lg ó no pasarle nada
        }
        
        function setTitle($title,$icon) {
		$this->title = $title; 
                $this->titleIcon=$icon;
        }
        
        
	function writeHTML() {
		$out  = "<div class=\"modal fade\" id=\"$this->id\" role=\"dialog\">";

                switch($this->type){
                    case 'screen':
                    case 'confirm':
                        $out .= "<div class=\"modal-dialog ".$this->modal." alert alert-info\">";
                        $this->class_body="modal-body";
                        break;
                    
                    case 'warning':
                        $out .= "<div class=\"modal-dialog ".$this->modal." alert alert-warning\">";
                        $this->class_body="modal-body";
                        break;
                    
                    case 'error':
                        $out .= "<div class=\"modal-dialog ".$this->modal." alert alert-danger\">";
                        $this->class_body="modal-body alert alert-danger";
                        break;                    
                }

                $out .= "<div class=\"modal-content\">";
                $out .= "  <div class=\"modal-header\">";
                
                if($this->closeModal == true){
                    $out .= "    <button type=\"button\" class=\"close\" data-dismiss=\"modal\" onClick=\"javascript:xajax_closeModal()\" >&times;</button>";
                }else{
                    $out .= "    <button type=\"button\" class=\"close\" data-dismiss=\"modal\" >&times;</button>";
                }
                
                switch($this->type){
                    case 'confirm':
                        $this->title=$this->title?$this->title:'Confirmar';
                        $out   .= " <h4 class=\"modal-title\"><span class=\"glyphicon glyphicon-saved\" aria-hidden=\"true\"></span>&nbsp;$this->title</h4>";
                        $this->button = "<button type=\"button\" id=\"btnConfirmCancelar-".$this->id."\" data-id=\"\" class=\"btn btn-default\" data-dismiss=\"modal\">
                                            <span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span>&nbsp;Cancelar
                                            </button>
                                           <button type=\"button\" id=\"btnConfirmAceptar-".$this->id."\" data-id=\"\" class=\"btn btn-default\" data-dismiss=\"modal\" autofocus>
                                            <span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span>&nbsp;Aceptar&nbsp;
                                           </button>";
                        break;
                    
                    case 'warning':
                        $this->title=$this->title?$this->title:'Advertencia';
                        $out .=  "<h4 class=\"modal-title\"><span class=\"glyphicon glyphicon-warning-sign\" aria-hidden=\"true\"></span>&nbsp;$this->title</h4>";
                        $this->button = "<button type=\"button\" id=\"btnWarningCerrar-".$this->id."\" data-id=\"\" class=\"btn btn-default\" data-dismiss=\"modal\" autofocus>
                                            Cerrar
                                         </button>";
                        break;
                    
                    case 'error':
                        $this->title=$this->title?$this->title:'Alto';
                        $out .=  "<h4 class=\"modal-title\"><span class=\"glyphicon glyphicon-remove-circle\" aria-hidden=\"true\"></span>&nbsp;$this->title</h4>";
                        $this->button = "<button type=\"button\" id=\"btnErrorCerrar-".$this->id."\" data-id=\"\" class=\"btn btn-default\" data-dismiss=\"modal\" autofocus>
                                            Cerrar
                                         </button>";
                        break;

                    case 'screen':
                        $this->title=$this->title?$this->title:'';
                        $out .=  "<h4 class=\"modal-title\"><span id=\"title-".$this->id."\" class=\"$this->titleIcon\" aria-hidden=\"true\"></span>&nbsp;$this->title</h4>";                    
                        
                        if($this->closeModal == true){
                            $this->button = "<button type=\"button\" onClick=\"javascript:xajax_closeModal()\" id=\"btnErrorCerrar-".$this->id."\" data-id=\"\" class=\"btn btn-default\" data-dismiss=\"modal\" autofocus>
                                            Cerrar
                                         </button>";
                        }
                        break;
                }
                
                $out .= "  </div>";
                $out .= "  <div id=\"msg-$this->id\" class=\"$this->class_body\">";

                $out .= "    <p>$this->mesage</p> ";
                
                $out .= "    <div> ";
                $out .= $this->objets;
                $out .= "    </div> ";
                
                $out .= "  </div>";
                
                if($this->button){
                    $out .= "  <div class=\"modal-footer\">";
                    $out .= $this->button;
                    $out .= "  </div>";
                }
                
                $out .= " </div>";
                $out .= "</div>";
                $out .= "</div>";               
                
		return $out;
	}        
}

class lectorPDF{
    
    function __construct(){        
    }
    
    function writeHTML() {
        $out="<div class=\"modal fade\" id=\"modalLectorPDF\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"myModalLabel\" aria-hidden=\"true\">
                <div class=\"modal-dialog\" id=\"lectorPDF\">
                  <div class=\"modal-content\">
                    <div class=\"modal-header\">
                      <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>                      
                      <!--h4 class=\"modal-title\" id=\"myModalLabel\">Lector de PDF</h4-->
                    </div>
                    <div class=\"modal-body\">
                        <!-- AQUI VA NUESTRO CONTENIDO -->
                        <embed id=\"myIframelectorPDF\" src=\"#\" frameborder=\"0\" style=\"overflow:hidden;height:78%;width:100%\" height=\"100%\" width=\"78%\">                     
                    </div>
                  </div>
                </div>
              </div>";
        return $out;             
    }
}

class lectorPDF2{
    
    function __construct(){        
    }
    
    function writeHTML() {
        
        $out="  <div class=\"modal fade\" id=\"modalLectorPDF\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"myModalLabel\" aria-hidden=\"true\">
                 <div class=\"modal-dialog modal-xl\" id=\"lectorPDF\">
                   <div class=\"modal-content\" style=\"height:80%\">
                     <div class=\"modal-header\">
                       <!--h5 class=\"modal-title\">Modal title</h5-->
                       <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                         <span aria-hidden=\"true\">&times;</span>
                       </button>
                     </div>
                     <div class=\"modal-body\" >
                        <!-- AQUI VA NUESTRO CONTENIDO -->
                        <embed id=\"myIframelectorPDF\" src=\"#\" frameborder=\"0\" style=\"overflow:hidden;height:100%;width:100%\" height=\"100%\" width=\"78%\">                     
                    </div>
                   </div>
                 </div>
               </div> ";

        return $out;             
    }
}

function getIconFile($file){
    $file=strtoupper($file);
    if(strpos($file,'.ZIP')>0){ //si existe zip
        $icon_files='ico_zip.png';
    }elseif(strpos($file,'.PDF')>0){ //si existe zip
        $icon_files='ico_pdf.png'; 
    }elseif(strpos($file,'.DOC')>0){ //si existe zip
        $icon_files='ico_doc.png'; 
    }elseif(strpos($file,'.XLS')>0){ //si existe zip
        $icon_files='ico_xls.png'; 
    }elseif(strpos($file,'.PPT')>0){ //si existe zip
        $icon_files='ico_ppt.png'; 
    }elseif(strpos($file,'.JPG')>0 || strpos($file,'.PNG')>0 || strpos($file,'.GIF')>0){ //si existe zip
        $icon_files='ico_img.png'; 
    }else{
        $icon_files='ico_file.png'; 
    }
    return ($icon_files);
}

class miValidacionString {

	public function __construct() {
	}
	
	public function replace_invalid_caracters($cadena) {
	    $cadena = str_replace("'", "", $cadena);
	    $cadena = str_replace("#", "", $cadena);
	    $cadena = str_replace("$", "", $cadena);
	    $cadena = str_replace("%", "", $cadena);
	    $cadena = str_replace("&", "", $cadena);
	    $cadena = str_replace("'", "", $cadena);
	    $cadena = str_replace("(", "", $cadena);
	    $cadena = str_replace(")", "", $cadena);
	    $cadena = str_replace("*", "", $cadena);
	    $cadena = str_replace("+", "", $cadena);
	    $cadena = str_replace("-", "", $cadena);
	    $cadena = str_replace(".", "", $cadena);
	    $cadena = str_replace("/", "", $cadena);
	    $cadena = str_replace("<", "", $cadena);
	    $cadena = str_replace("=", "", $cadena);
	    $cadena = str_replace(">", "", $cadena);
	    $cadena = str_replace("?", "", $cadena);
	    $cadena = str_replace("@", "", $cadena);
	    $cadena = str_replace("[", "", $cadena);
	    $cadena = str_replace("\\", "", $cadena);
	    $cadena = str_replace("]", "", $cadena);
	    $cadena = str_replace("^", "", $cadena);
	    $cadena = str_replace("_", "", $cadena);
	    $cadena = str_replace("`", "", $cadena);
	    $cadena = str_replace("{", "", $cadena);
	    $cadena = str_replace("|", "", $cadena);
	    $cadena = str_replace("}", "", $cadena);
	    $cadena = str_replace("~", "", $cadena);
	    $cadena = str_replace("¡", "", $cadena);
	    $cadena = str_replace("¢", "", $cadena);
	    $cadena = str_replace("£", "", $cadena);
	    $cadena = str_replace("¤", "", $cadena);
	    $cadena = str_replace("¥", "", $cadena);
	    $cadena = str_replace("¦", "", $cadena);
	    $cadena = str_replace("§", "", $cadena);
	    $cadena = str_replace("¨", "", $cadena);
	    $cadena = str_replace("©", "", $cadena);
	    $cadena = str_replace("ª", "", $cadena);
	    $cadena = str_replace("«", "", $cadena);
	    $cadena = str_replace("¬", "", $cadena);
	    $cadena = str_replace("®", "", $cadena);
	    $cadena = str_replace("±", "", $cadena);
	    $cadena = str_replace("²", "", $cadena);
	    $cadena = str_replace("³", "", $cadena);
	    $cadena = str_replace("´", "", $cadena);
	    $cadena = str_replace("µ", "", $cadena);
	    $cadena = str_replace("¶", "", $cadena);
	    $cadena = str_replace("·", "", $cadena);
	    $cadena = str_replace("¸", "", $cadena);
	    $cadena = str_replace("¹", "", $cadena);
	    $cadena = str_replace("º", "", $cadena);
	    $cadena = str_replace("»", "", $cadena);
	    $cadena = str_replace("¼", "", $cadena);
	    $cadena = str_replace("½", "", $cadena);
	    $cadena = str_replace("¾", "", $cadena);
	    $cadena = str_replace("¿", "", $cadena);
	    $cadena = str_replace("À", "A", $cadena);
	    $cadena = str_replace("Â", "A", $cadena);
	    $cadena = str_replace("Ã", "A", $cadena);
	    $cadena = str_replace("Ä", "A", $cadena);
	    $cadena = str_replace("Å", "A", $cadena);
	    $cadena = str_replace("Æ", "", $cadena);
	    $cadena = str_replace("Ç", "", $cadena);
	    $cadena = str_replace("È", "E", $cadena);
	    $cadena = str_replace("Ê", "E", $cadena);
	    $cadena = str_replace("Ë", "E", $cadena);
	    $cadena = str_replace("Ì", "I", $cadena);
	    $cadena = str_replace("Î", "I", $cadena);
	    $cadena = str_replace("Ï", "I", $cadena);
	    $cadena = str_replace("Ð", "", $cadena);
	    $cadena = str_replace("Ò", "O", $cadena);
	    $cadena = str_replace("Ô", "O", $cadena);
	    $cadena = str_replace("Õ", "O", $cadena);
	    $cadena = str_replace("Ö", "O", $cadena);
	    $cadena = str_replace("×", "", $cadena);
	    $cadena = str_replace("Ø", "", $cadena);
	    $cadena = str_replace("Ù", "U", $cadena);
	    $cadena = str_replace("Û", "U", $cadena);
	    $cadena = str_replace("Ü", "U", $cadena);
	    $cadena = str_replace("Ý", "Y", $cadena);
	    $cadena = str_replace("Þ", "", $cadena);
	    $cadena = str_replace("ß", "", $cadena);
	    $cadena = str_replace("à", "a", $cadena);
	    $cadena = str_replace("â", "a", $cadena);
	    $cadena = str_replace("ã", "a", $cadena);
	    $cadena = str_replace("ä", "a", $cadena);
	    $cadena = str_replace("å", "a", $cadena);
	    $cadena = str_replace("æ", "", $cadena);
	    $cadena = str_replace("ç", "", $cadena);
	    $cadena = str_replace("è", "e", $cadena);
	    $cadena = str_replace("ê", "e", $cadena);
	    $cadena = str_replace("ë", "e", $cadena);
	    $cadena = str_replace("ì", "i", $cadena);
	    $cadena = str_replace("î", "i", $cadena);
	    $cadena = str_replace("ï", "i", $cadena);
	    $cadena = str_replace("ð", "o", $cadena);
	    $cadena = str_replace("ò", "o", $cadena);
	    $cadena = str_replace("ô", "o", $cadena);
	    $cadena = str_replace("õ", "o", $cadena);
	    $cadena = str_replace("ö", "o", $cadena);
	    $cadena = str_replace("÷", "", $cadena);
	    $cadena = str_replace("ø", "", $cadena);
	    $cadena = str_replace("ù", "u", $cadena);
	    $cadena = str_replace("û", "u", $cadena);
	    $cadena = str_replace("ü", "u", $cadena);
	    $cadena = str_replace("ý", "y", $cadena);
	    $cadena = str_replace("þ", "", $cadena);
	    $cadena = str_replace("ÿ", "", $cadena);
	    $cadena = str_replace("Œ", "", $cadena);
	    $cadena = str_replace("œ", "", $cadena);
	    $cadena = str_replace("Š", "", $cadena);
	    $cadena = str_replace("š", "", $cadena);
	    $cadena = str_replace("Ÿ", "", $cadena);
	    $cadena = str_replace("ƒ", "", $cadena);
	    $cadena = str_replace("–", "", $cadena);
	    $cadena = str_replace("—", "", $cadena);
	    $cadena = str_replace("‘", "", $cadena);
	    $cadena = str_replace("’", "", $cadena);
	    $cadena = str_replace("‚", "", $cadena);
	    $cadena = str_replace("“", "", $cadena);
	    $cadena = str_replace("”", "", $cadena);
	    $cadena = str_replace("„", "", $cadena);
	    $cadena = str_replace("†", "", $cadena);
	    $cadena = str_replace("‡", "", $cadena);
	    $cadena = str_replace("•", "", $cadena);
	    $cadena = str_replace("…", "", $cadena);
	    $cadena = str_replace("‰", "", $cadena);
	    $cadena = str_replace("€", "", $cadena);
	    $cadena = str_replace("™", "", $cadena);
            $cadena = str_replace("\n", " ", $cadena);
	    return $cadena;
	}
        
        
	public function replace_nameFile($cadena) {
	    $cadena = str_replace("'", "", $cadena);
	    $cadena = str_replace("#", "", $cadena);
	    $cadena = str_replace("$", "", $cadena);
	    $cadena = str_replace("%", "", $cadena);
	    $cadena = str_replace("&", "", $cadena);
	    $cadena = str_replace("'", "", $cadena);
	    $cadena = str_replace("*", "", $cadena);
	    $cadena = str_replace("+", "", $cadena);
	    $cadena = str_replace("-", "", $cadena);
	    $cadena = str_replace("/", "", $cadena);
	    $cadena = str_replace("<", "", $cadena);
	    $cadena = str_replace("=", "", $cadena);
	    $cadena = str_replace(">", "", $cadena);
	    $cadena = str_replace("?", "", $cadena);
	    $cadena = str_replace("@", "", $cadena);
	    $cadena = str_replace("[", "", $cadena);
	    $cadena = str_replace("\\", "", $cadena);
	    $cadena = str_replace("]", "", $cadena);
	    $cadena = str_replace("^", "", $cadena);
	    $cadena = str_replace("_", "", $cadena);
	    $cadena = str_replace("`", "", $cadena);
	    $cadena = str_replace("{", "", $cadena);
	    $cadena = str_replace("|", "", $cadena);
	    $cadena = str_replace("}", "", $cadena);
	    $cadena = str_replace("~", "", $cadena);
	    $cadena = str_replace("¡", "", $cadena);
	    $cadena = str_replace("¢", "", $cadena);
	    $cadena = str_replace("£", "", $cadena);
	    $cadena = str_replace("¤", "", $cadena);
	    $cadena = str_replace("¥", "", $cadena);
	    $cadena = str_replace("¦", "", $cadena);
	    $cadena = str_replace("§", "", $cadena);
	    $cadena = str_replace("¨", "", $cadena);
	    $cadena = str_replace("©", "", $cadena);
	    $cadena = str_replace("ª", "", $cadena);
	    $cadena = str_replace("«", "", $cadena);
	    $cadena = str_replace("¬", "", $cadena);
	    $cadena = str_replace("®", "", $cadena);
	    $cadena = str_replace("°", "", $cadena);
	    $cadena = str_replace("±", "", $cadena);
	    $cadena = str_replace("²", "", $cadena);
	    $cadena = str_replace("³", "", $cadena);
	    $cadena = str_replace("´", "", $cadena);
	    $cadena = str_replace("µ", "", $cadena);
	    $cadena = str_replace("¶", "", $cadena);
	    $cadena = str_replace("·", "", $cadena);
	    $cadena = str_replace("¸", "", $cadena);
	    $cadena = str_replace("¹", "", $cadena);
	    $cadena = str_replace("º", "", $cadena);
	    $cadena = str_replace("»", "", $cadena);
	    $cadena = str_replace("¼", "", $cadena);
	    $cadena = str_replace("½", "", $cadena);
	    $cadena = str_replace("¾", "", $cadena);
	    $cadena = str_replace("¿", "", $cadena);
	    $cadena = str_replace("À", "A", $cadena);
	    $cadena = str_replace("Á", "A", $cadena);
	    $cadena = str_replace("Â", "A", $cadena);
	    $cadena = str_replace("Ã", "A", $cadena);
	    $cadena = str_replace("Ä", "A", $cadena);
	    $cadena = str_replace("Å", "A", $cadena);
	    $cadena = str_replace("Æ", "", $cadena);
	    $cadena = str_replace("Ç", "", $cadena);
	    $cadena = str_replace("È", "E", $cadena);
	    $cadena = str_replace("É", "E", $cadena);
	    $cadena = str_replace("Ê", "E", $cadena);
	    $cadena = str_replace("Ë", "E", $cadena);
	    $cadena = str_replace("Ì", "I", $cadena);
	    $cadena = str_replace("Í", "I", $cadena);
	    $cadena = str_replace("Î", "I", $cadena);
	    $cadena = str_replace("Ï", "I", $cadena);
	    $cadena = str_replace("Ð", "", $cadena);
	    $cadena = str_replace("Ñ", "N", $cadena);
	    $cadena = str_replace("Ò", "O", $cadena);
	    $cadena = str_replace("Ó", "O", $cadena);
	    $cadena = str_replace("Ô", "O", $cadena);
	    $cadena = str_replace("Õ", "O", $cadena);
	    $cadena = str_replace("Ö", "O", $cadena);
	    $cadena = str_replace("×", "", $cadena);
	    $cadena = str_replace("Ø", "", $cadena);
	    $cadena = str_replace("Ù", "U", $cadena);
	    $cadena = str_replace("Ú", "U", $cadena);
	    $cadena = str_replace("Û", "U", $cadena);
	    $cadena = str_replace("Ü", "U", $cadena);
	    $cadena = str_replace("Ý", "Y", $cadena);
	    $cadena = str_replace("Þ", "", $cadena);
	    $cadena = str_replace("ß", "", $cadena);
	    $cadena = str_replace("à", "a", $cadena);
	    $cadena = str_replace("á", "a", $cadena);
	    $cadena = str_replace("â", "a", $cadena);
	    $cadena = str_replace("ã", "a", $cadena);
	    $cadena = str_replace("ä", "a", $cadena);
	    $cadena = str_replace("å", "a", $cadena);
	    $cadena = str_replace("æ", "", $cadena);
	    $cadena = str_replace("ç", "", $cadena);
	    $cadena = str_replace("è", "e", $cadena);
	    $cadena = str_replace("é", "e", $cadena);
	    $cadena = str_replace("ê", "e", $cadena);
	    $cadena = str_replace("ë", "e", $cadena);
	    $cadena = str_replace("ì", "i", $cadena);
	    $cadena = str_replace("í", "i", $cadena);
	    $cadena = str_replace("î", "i", $cadena);
	    $cadena = str_replace("ï", "i", $cadena);
	    $cadena = str_replace("ð", "o", $cadena);
	    $cadena = str_replace("ñ", "n", $cadena);
	    $cadena = str_replace("ò", "o", $cadena);
	    $cadena = str_replace("ó", "o", $cadena);
	    $cadena = str_replace("ô", "o", $cadena);
	    $cadena = str_replace("õ", "o", $cadena);
	    $cadena = str_replace("ö", "o", $cadena);
	    $cadena = str_replace("÷", "", $cadena);
	    $cadena = str_replace("ø", "", $cadena);
	    $cadena = str_replace("ù", "u", $cadena);
	    $cadena = str_replace("ú", "u", $cadena);
	    $cadena = str_replace("û", "u", $cadena);
	    $cadena = str_replace("ü", "u", $cadena);
	    $cadena = str_replace("ý", "y", $cadena);
	    $cadena = str_replace("þ", "", $cadena);
	    $cadena = str_replace("ÿ", "", $cadena);
	    $cadena = str_replace("Œ", "", $cadena);
	    $cadena = str_replace("œ", "", $cadena);
	    $cadena = str_replace("Š", "", $cadena);
	    $cadena = str_replace("š", "", $cadena);
	    $cadena = str_replace("Ÿ", "", $cadena);
	    $cadena = str_replace("ƒ", "", $cadena);
	    $cadena = str_replace("–", "", $cadena);
	    $cadena = str_replace("—", "", $cadena);
	    $cadena = str_replace("‘", "", $cadena);
	    $cadena = str_replace("’", "", $cadena);
	    $cadena = str_replace("‚", "", $cadena);
	    $cadena = str_replace("“", "", $cadena);
	    $cadena = str_replace("”", "", $cadena);
	    $cadena = str_replace("„", "", $cadena);
	    $cadena = str_replace("†", "", $cadena);
	    $cadena = str_replace("‡", "", $cadena);
	    $cadena = str_replace("•", "", $cadena);
	    $cadena = str_replace("…", "", $cadena);
	    $cadena = str_replace("‰", "", $cadena);
	    $cadena = str_replace("€", "", $cadena);
	    $cadena = str_replace("™", "", $cadena);
            $cadena = str_replace("\n", " ", $cadena);
	    return $cadena;
	}
}

function sumarHoras($ar_horas) {
    $total = 0;
    foreach($ar_horas as $h) {
        $parts = explode(":", $h);
        $total += $parts[2] + $parts[1]*60 + $parts[0]*3600;        
    }   
    return gmdate("H:i:s", $total);
}

function verificarToken($token, $claveSecreta)
{
    # La API en donde verificamos el token
    $url = "https://www.google.com/recaptcha/api/siteverify";
    # Los datos que enviamos a Google
    $datos = [
        "secret" => $claveSecreta,
        "response" => $token,
    ];
    // Crear opciones de la petición HTTP
    $opciones = array(
        "http" => array(
            "header" => "Content-type: application/x-www-form-urlencoded\r\n",
            "method" => "POST",
            "content" => http_build_query($datos), # Agregar el contenido definido antes
        ),
    );
    # Preparar petición
    $contexto = stream_context_create($opciones);
    # Hacerla
    $resultado = file_get_contents($url, false, $contexto);
    # Si hay problemas con la petición (por ejemplo, que no hay internet o algo así)
    # entonces se regresa false. Este NO es un problema con el captcha, sino con la conexión
    # al servidor de Google
    if ($resultado === false) {
        # Error haciendo petición
        return false;
    }

    # En caso de que no haya regresado false, decodificamos con JSON
    # https://parzibyte.me/blog/2018/12/26/codificar-decodificar-json-php/

    $resultado = json_decode($resultado);
    # La variable que nos interesa para saber si el usuario pasó o no la prueba
    # está en success
    $pruebaPasada = $resultado->success;
    # Regresamos ese valor, y listo (sí, ya sé que se podría regresar $resultado->success)
    return $pruebaPasada;
}


function isOsWin()
{
        return '\\' === \DIRECTORY_SEPARATOR;
}