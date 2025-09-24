<?php
require_once("../../library/clases/entidad.php");

class clsCategoriaRemunerativaJornales extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='categoria_remunerativa_jornales'; //nombre de la tabla
		$this->setKey='crjo_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;

		/* Destinos luego de actulizar, agregar o eliminar un registro */
		$this->destinoInsert='CategoriaRemunerativaJornales_buscar.php';
		$this->destinoUpdate='CategoriaRemunerativaJornales_buscar.php';
		$this->destinoDelete='CategoriaRemunerativaJornales_buscar.php';

		$this->arrayNameVar[0]='nomeCampoForm';
		$this->arrayNameVar[1]='busEmpty';
		$this->arrayNameVar[2]='cadena';
		$this->arrayNameVar[3]='pg';
		$this->arrayNameVar[4]='colSearch';
		$this->arrayNameVar[5]='numForm';

	}

        function getSql(){
		$sql=new clsCategoriaRemunerativaJornales_SQLlista();
		$sql->whereID($this->id);
		return($sql->getSQL());
	}

        function buscar($op,$formData,$arrayParam,$pg,$Nameobj='')
	{
		global $conn,$param,$nomeCampoForm;
		$objResponse = new xajaxResponse();
		
		$arrayParam=decodeArray($arrayParam);

		$paramFunction= new manUrlv1();
		$paramFunction->setUrlExternal($arrayParam);

		if($op==1 && !is_array($formData)) $formData=decodeArray($formData);
		
		$cadena=is_array($formData)?trim($formData['Sbusc_cadena']):$formData;
		
		$busEmpty=$paramFunction->getValuePar($paramFunction->getValuePar(1));
		$colSearch=$paramFunction->getValuePar($paramFunction->getValuePar(4));
		$numForm=$paramFunction->getValuePar($paramFunction->getValuePar(5));

				
		$pageEdit=$paramFunction->getValuePar('pageEdit');
                $relacionamiento_id=$paramFunction->getValuePar('relacionamento_id');
                
		$cadena=$cadena?$cadena:'';
	
			/* Creo my objeto Table */
			$otable = new TableSimple('',"100%",6,'tLista'); // T�tulo, ancho, Cantidad de columas,id de la tabla
			
			/* Guardo la p�gina actual en un campo oculto.  Para que al eliminar regrese a la p�gina donde estaba */
			$otable->addHtml("<input type='hidden' name='".$paramFunction->getValuePar(3)."' value='$pg'>\n");			

			$sql=new clsCategoriaRemunerativaJornales_SQLlista();
                        $sql->wherePadreID($relacionamiento_id);
                         switch($colSearch){
                            case 'codigo': // si se recibe el campo id
                                break;

                            default:// si se no se recibe ningun campo de busqueda
                                    if(ctype_digit($cadena)){ //si la cadena recibida son todos digitos
                                        //$sql->whereDNI($cadena);
                                    }
                                    elseif($cadena){
                                        $sql->whereDescrip($cadena);
                                    }
                                   break;
                        }

			$sql->orderUno();
				
			$sql=$sql->getSQL();

			//echo $sql;
									
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			$param->replaceParValue($paramFunction->getValuePar(2),$cadena); /* Agrego el par�metro */			
			
	
			$rs = new query($conn, strtoupper($sql),$pg,80);		
			
			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
					$otable->addColumnHeader("<input type=\"checkbox\" id=\"checkall\" >"); // Coluna com checkbox
					$otable->addColumnHeader("ID","5%", "L"); 
                                        $otable->addColumnHeader("Aplicable Desde","20%", "C"); 
					$otable->addColumnHeader("Jornal","35%","C");
                                        $otable->addColumnHeader("Reintegro","35%","C");
                                        $otable->addColumnHeader("Usuario","5%","C");
					$otable->addRowHead(); 					

					while ($rs->getrow()) {
						$id = $rs->field("id"); // captura la clave primaria del recordsource
                                                $otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" >");
                                                
                                                $otable->addData(addLink($id,"javascript:abreEdicion('$relacionamiento_id','$id')","Click aqu&iacute; para consultar o editar este registro"));	
                                                $otable->addData(dtos($rs->field("crjo_fdesde")),"C");
						$otable->addData($rs->field("crjo_jornal"),"R");
                                                $otable->addData($rs->field("crjo_reintegro"),"R");
                                                $otable->addData($rs->field("username"),"R");
        					$otable->addRow();
					}
				$contenido_respuesta=$button->writeHTML();
				$contenido_respuesta.=$otable->writeHTML();
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total Registros: ".$rs->numrows()."</div>";

			} else {
				$otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!","100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
				$otable->addRowHead(); 	
				$otable->addRow();	
				$contenido_respuesta=$otable->writeHTML();
			}

		if($op==1){//si es llamado para su funcionamiento en ajax con retornoa a un div
			$objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
			$objResponse->addscript("activaSorter()"); // Para activar el orden en la tabla 
			$objResponse->addscript("func_jquerytablas()"); // Para activar las funciones de css de la tabla
			return $objResponse;
			}
		else
			return $contenido_respuesta	;
	}       
        
        
	function guardar(){
		global $conn,$param;
		$nomeCampoForm=getParam("nomeCampoForm");

		$destinoUpdate=$this->destinoUpdate.$param->buildPars(true);	
		$pg=is_array($this->arrayNameVar)?$this->arrayNameVar[3]:'pg';
		$param->removePar($pg); /* Remuevo el par�metro p�gina */
		$destinoInsert=$this->destinoInsert.$param->buildPars(true);
		
		// objeto para instanciar la clase sql
		$sql = new UpdateSQL();
				
		$sql->setTable($this->setTable);
		$sql->setKey($this->setKey, $this->valueKey, $this->typeKey);
	
		include("../guardar_tipoDato.php");
	
		if ($this->valueKey) { // modificación
			$sql->setAction("UPDATE");
                        $sql_type=2;
		}else{
			$sql->setAction("INSERT");
                        $sql_type=1;
			$sql->addField('usua_id', getSession("sis_userid"), "Number");
		}


		/* Aqu� puedo agregar otros campos a la sentencia SQL */
		$this->addField($sql);

		/* Ejecuto el SQL */
                $sqlCommand=$sql->getSQL();
                //echo $sql->getSQL();
		$padre_id=$conn->execute("$sqlCommand  RETURNING $this->setKey");
                
		$error=$conn->error();
		if($error){ 
                         if(stristr($error,"duplicate key value")){
                             $x=substr($error,strpos($error,"constraint")+11,(strpos($error,"DETAIL")-strpos($error,"constraint"))-12);
                             $error="Valor Duplicado:".$x;
                         }
                                 
			alert($error);	/* Muestro el error y detengo la ejecuci�n */
		}else{
			/*  muestra mensaje noticia del la base de datos, pero no detiene la ejecucion	*/
			$notice=$conn->notice();
			if($notice){ 
				alert($notice,0);
                        }else{
                            echo "<"."script".">\n";
                            echo "javascript:top.close()\n";
                            echo "</"."script".">\n";
                            }
                }
	}
        
        
	/* Nombre del archivo php de la clase */
	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1);
	}
        
        
} /* Fin de la clase */

class clsCategoriaRemunerativaJornales_SQLlista extends selectSQL {
	function __construct(){
		$this->sql = "SELECT LPAD(a.crjo_id::TEXT,4,'0') AS id,
                                    a.crjo_fdesde,
                                    a.crjo_jornal,
                                    a.crjo_reintegro,
                                    b.care_descripcion,
                                    x.usua_login AS username
			FROM catalogos.categoria_remunerativa_jornales a
                        LEFT JOIN catalogos.categoria_remunerativa b ON a.care_id=b.care_id
                        LEFT JOIN usuario x ON a.usua_id=x.usua_id                        
                        ";
	}

	function wherePadreID($padre_id){
		$this->addWhere("a.care_id=$padre_id");
	}

        
	function whereID($id){
		$this->addWhere("a.crjo_id=$id");
	}
        
        
        function whereDescrip($descrip){
		$this->addWhere("TO_CHAR(a.crjo_fdesde,'DD/MM/YYYY')::TEXT ILIKE '%$descrip%'");
	}

	function orderUno(){
		$this->addOrder("a.crjo_id DESC");
	}
}


////////

/* Llamando a la subclase */
if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
            require_once($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");

            /*	verificación a nivel de usuario */
            verificaUsuario(1);
            verif_framework();

            $param= new manUrlv1();
            $param->removePar('control');

            //	conexión a la BD
            $conn = new db();
            $conn->open();

            $dml=new clsCategoriaRemunerativaJornales();
            /* Recibo la p�gina actual de la lista y lo agrego como par�metro a ser enviado por la URL.  Este dato viene por POST */
            $pg = getParam($dml->getArrayNameVarID(3));
            $param->replaceParValue($dml->getArrayNameVarID(3),$pg); /* Agrego el par�metro */

            switch($control){
                    case 1: // Guardar
                            $dml->guardar();
                            break;

                    case 2: // Eliminar
                            $dml->eliminar();
                            break;

            }
            //	cierra la conexión con la BD
            $conn->close();
    }
}