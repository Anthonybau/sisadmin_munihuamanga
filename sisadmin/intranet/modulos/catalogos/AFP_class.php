<?php
require_once("../../library/clases/entidad.php");


class clsAFP extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='afp'; //nombre de la tabla
		$this->setKey='afp_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

		/* Destinos luego de actulizar o agregar un registro */
		$this->destinoUpdate = "AFP_buscar.php";
		$this->destinoInsert = "AFP_buscar.php";
		$this->destinoDelete = "AFP_buscar.php";
                $this->pagEdicion ="AFP_edicion.php";
                $this->pagBuscar="AFP_buscar.php";
	}

	function addField(&$sql){
	}

	function getSql(){
		$sql=new clsAFP_SQLlista();
		$sql->whereID($this->id);
		return($sql->getSQL());
	}

	function buscar($op,$formData,$arrayParam,$pg,$Nameobj='')

	{
		global $conn,$param;
		$objResponse = new xajaxResponse();
		
		$arrayParam=decodeArray($arrayParam);

		$paramFunction= new manUrlv1();
		$paramFunction->setUrlExternal($arrayParam);

		$cadena=is_array($formData)?trim(strtoupper($formData['Sbusc_cadena'])):$formData;
		if(!$cadena && $op==2) $cadena=getSession("cadSearch");
		
		$colSearch=$paramFunction->getValuePar('colSearch');
		$colOrden=$paramFunction->getValuePar('colOrden');
		$busEmpty=$paramFunction->getValuePar('busEmpty');
                $pagEdit=$paramFunction->getValuePar('pagEdit');        				                
				
		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	
			$otable = new  Table("","100%",6);

			$sql=new clsAFP_SQLlista();
						
			//se analiza la columna de busqueda
			switch($colSearch){
				case 'codigo': // si se recibe el campo id
					$sql->whereID($cadena);
					break;
	
				default:// si se no se recibe ningun campo de busqueda
					if(ctype_digit($cadena)) //si la cadena recibida son todos digitos
						$sql->whereID($cadena);
					else
						$sql->whereDescrip($cadena);
					break;
				}
			$sql->orderUno();
			
			$sql=$sql->getSQL();
			
//			$objResponse->addAlert($sql);
		//echo $sql;
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) setSession("cadSearch",$cadena);			
	
			$rs = new query($conn, strtoupper($sql));						

			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
					$otable->addColumnHeader("<input type=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">"); // Coluna com checkbox
					$colOrden=1;
					$paramFunction->replaceParValue('colOrden',$colOrden++);
					$otable->addColumnHeader("C&oacute;d",true,"5%", "L","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); 
					$paramFunction->replaceParValue('colOrden',$colOrden++);
					$otable->addColumnHeader("Descripci&oacute;n",true,"40%", "L","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')");
					$paramFunction->replaceParValue('colOrden',$colOrden++);
					$otable->addColumnHeader("Comisi&oacute;n &Uacute;nica",true,"10%", "C","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); 
                                        $paramFunction->replaceParValue('colOrden',$colOrden++);
					$otable->addColumnHeader("Comisi&oacute;n Mixta",true,"10%", "C","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); 
					$paramFunction->replaceParValue('colOrden',$colOrden++);
					$otable->addColumnHeader("Prima",true,"10%", "C","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); 
					$paramFunction->replaceParValue('colOrden',$colOrden++);
					$otable->addColumnHeader("Aporte",true,"10%", "C","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')");
					$paramFunction->replaceParValue('colOrden',$colOrden++);
					$otable->addColumnHeader("Rem.Max.Aseg.",true,"10%", "C","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); 
                                        $paramFunction->replaceParValue('colOrden',$colOrden++);
					$otable->addColumnHeader("Usuario",true,"5%", "C","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); 					
					$otable->addRow(); // adiciona la linea (TR)
					while ($rs->getrow()) {
						$id = $rs->field("afp_id"); // captura la clave primaria del recordsource
						$campoTexto_de_Retorno = especialChar($rs->field("afp_nombre"));

						$otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");
						$otable->addData(addLink(str_pad($id,4,'0',STR_PAD_LEFT),"AFPFactores_buscar.php?relacionamento_id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
						$otable->addData(addLink($rs->field("afp_nombre"),"AFPFactores_buscar.php?relacionamento_id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
						//$otable->addData($rs->field("afp_nombre"));
                                                $otable->addData($rs->field("afp_comision"),"R");
                                                $otable->addData($rs->field("afp_comision_mixta"),"R");
                                                $otable->addData($rs->field("afp_prima"),"R");
                                                $otable->addData($rs->field("afp_aporte"),"R");
                                                $otable->addData($rs->field("afp_tope"),"R");
                                                $otable->addData($rs->field("username"),"R");
						$otable->addRow();
					}
				$contenido_respuesta=$button->writeHTML();
				$contenido_respuesta.=$otable->writeHTML();
				$contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total Registros: ".$rs->numrows()."</div>";

			} else {
				$otable->addColumnHeader("!NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
				$otable->addRow();
				$contenido_respuesta=$otable->writeHTML();
			}
		}
	else
		$contenido_respuesta="";
	
		//se analiza el tipo de funcionamiento
		if($op==1){//si es llamado para su funcionamiento en ajax con retornoa a un div
			$objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
			return $objResponse;
			}
		else
			return $contenido_respuesta	;
	}

	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	
}

class clsAFP_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.*,
					x.usua_login as username
				FROM catalogos.afp a
				LEFT JOIN admin.usuario x ON a.usua_id=x.usua_id
				";
	
	}
	
	function whereID($id){
		$this->addWhere("a.afp_id=$id");
	}

	function whereDescrip($descrip){
		if($descrip) $this->addWhere("a.afp_nombre ILIKE '%$descrip%'");
	}

	
	function orderUno(){
		$this->addOrder("a.afp_id DESC");
	}


	function getSQL_cbox(){
		$sql="SELECT afp_id,afp_nombre
				FROM (".$this->getSQL().") AS a ORDER BY 2";
		return $sql;
	}
	
}


if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
            include("../../library/library.php");
            /*	verificaci�n a nivel de usuario */
            verificaUsuario(1);
            verif_framework();

            $param= new manUrlv1();
            $param->removePar('control');
            $param->removePar('relacionamento_id');
            $param->removePar('pg'); /* Remuevo el par�metro */

            /* Recibo la p�gina actual de la lista y lo agrego como par�metro a ser enviado por la URL.  Este dato viene por POST */
            $pg = getParam("pg");
            $param->addParComplete('pg',$pg); /* Agrego el par�metro */

            //	conexión a la BD
            $conn = new db();
            $conn->open();

            $dml=new clsAFP();

            switch($control){

                    case 1: // Guardar
                            $dml->guardar();
                            break;
                    case 2: // Eliminar
                            $dml->eliminar();
                            break;
            }
            //	cierra la conexi�n con la BD
            $conn->close();
    }
}