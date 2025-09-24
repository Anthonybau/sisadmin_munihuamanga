<?php
require_once("../../library/clases/entidad.php");

class encargaturas extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='gestdoc.encargaturas'; //nombre de la tabla
		$this->setKey='enca_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

                $this->pagEdicion=$this->getNamePage('edicion');
                $this->pagBuscar=$this->getNamePage('buscar');

		/* Destinos luego de actulizar, agregar o eliminar un registro */
                $this->destinoUpdate = $this->pagBuscar;
                $this->destinoInsert = $this->pagBuscar;
                $this->destinoDelete = $this->pagBuscar;
	}

	function addField(&$sql){
            if ($_POST["hx_enca_set_encargado"]){
                $sql->addField("enca_encargado", 1, "Number");
            }else{
                $sql->addField("enca_encargado", 0, "Number");
            }

	}

	function getSql(){
		$sql=new encargaturas_SQLlista();
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
                $pageEdit=$paramFunction->getValuePar('pageEdit');				
                
		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	

                    $sql=new encargaturas_SQLlista();
                    $sql->whereUsuaID(getSession('sis_userid'));

                    //se analiza la columna de busqueda
                    switch($colSearch){
                        case 'codigo': // si se recibe el campo id
                                $sql->whereID($cadena);
                                break;

                        default:// si se no se recibe ningun campo de busqueda
                                if(ctype_digit($cadena)) //si la cadena recibida son todos digitos
                                        $sql->whereID($cadena);
                                else
                                    if($cadena){
                                        $sql->whereDescrip($cadena);
                                    }
                                break;
                        }
                    $sql->addOrder('1 DESC');
			
                    $sql=$sql->getSQL();
			
//			$objResponse->addAlert($sql);
		
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) setSession("cadSearch",$cadena);			
	
                        $otable = new TableSimple("","100%",6,'tLista'); 
			$rs = new query($conn, strtoupper($sql));						

			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
                                        $otable->addColumnHeader("<input type=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">","1%"); // Coluna com checkbox
                                        $otable->addColumnHeader("C&oacute;d","5%", "C"); 
                                        $otable->addColumnHeader("Delegado A:","30%", "C");
                                        $otable->addColumnHeader("Dependencia","25%", "C");
                                        $otable->addColumnHeader("Motivo","24%", "C");
                                        $otable->addColumnHeader("Encarg","1%", "C");
                                        $otable->addColumnHeader("Registro","15%", "C"); 
                                        $otable->addRowHead();
					while ($rs->getrow()) {
						$id = $rs->field("enca_id"); // captura la clave primaria del recordsource

						$otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");
						$otable->addData(addLink($rs->field("encaid"),"$pageEdit?id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
						
						$otable->addData($rs->field("encargado"));
                                                $otable->addData($rs->field("depe_nombre"));
                                                $otable->addData($rs->field("enca_motivo"));
                                                $otable->addData($rs->field("tipo_encargado"),"C");
                                                $otable->addData($rs->field("username").'<br>'.substr($rs->field("enca_fregistro"),0,19));
						$otable->addRow();
					}
                                $contenido_respuesta=$otable->writeHTML();                                        
                                $contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

			} else {
				$otable->addColumnHeader("NO SE ENCONTRARON DATOS...","100%", "C"); 
                                $otable->addRowHead();
                                $otable->addRow();
                                $contenido_respuesta=$otable->writeHTML();
			}
		}
	else
		$contenido_respuesta="";
	
		//se analiza el tipo de funcionamiento
		if($op==1){//si es llamado para su funcionamiento en ajax con retornoa a un div
			$objResponse->addAssign($Nameobj,'innerHTML', utf8_encode($contenido_respuesta));
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
                                 
			alert(substr($error,0,300));	/* Muestro el error y detengo la ejecuci�n */
		}else{
		
		/* */
		if ($this->valueKey) {// modificación
			$last_id=$this->valueKey; 
			if(strpos($destinoInsert, "?")>0)
				$destinoUpdate.="&id=$last_id";
			else
				$destinoUpdate.="?id=$last_id";

			redirect($destinoUpdate,"content");			
							
		}else{ /* Inserci�n */
                        /*a�ado el id del registro ingresado*/
                        $last_id=$padre_id; /* Obtengo el Id del registro ingreado.  Ojo esto solo funciona si el campo clave es un serial (est� basado en una secuencia) */								
                        if(strpos($destinoInsert, "?")>0)
                                $destinoInsert.="&id=$last_id&clear=1";  
                        else
                                $destinoInsert.="?id=$last_id&clear=1";

                        redirect($destinoInsert,"content");										}
		}
	}
        
 	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	

        function getNamePage($accion) {
            return(str_replace('class',$accion,$this->getNameFile()));
        }        
}

class encargaturas_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.*,
                                    LPAD(a.enca_id::TEXT,4,'0') AS encaid,
                                    TRIM(c.pers_apellpaterno)||' '||TRIM(c.pers_apellmaterno)||' '||TRIM(c.pers_nombres) AS encargado,
                                    CASE WHEN a.enca_encargado=1 THEN '(E)' ELSE '' END AS tipo_encargado,
                                    d.depe_nombre,
                                    x.usua_login as username
				FROM gestdoc.encargaturas a
                                LEFT JOIN personal.persona c                  ON a.pers_id=c.pers_id
                                LEFT JOIN catalogos.dependencia   d           ON a.depe_id=d.depe_id
				LEFT JOIN admin.usuario x ON a.usua_id=x.usua_id
				";
	
	}
	
	function whereID($id){
		$this->addWhere("a.enca_id=$id");
	}

        function whereUsuaID($usua_id){
		$this->addWhere("a.usua_id=$usua_id");
	}
        
	function whereDescrip($descrip){
		if($descrip) $this->addWhere("(a.enca_motivo ILIKE '%$descrip%' 
                                                OR TRIM(c.pers_apellpaterno)||' '||TRIM(c.pers_apellmaterno)||' '||TRIM(c.pers_nombres) ILIKE '%$descrip%' 
                                                OR TRIM(c.pers_nombres)||' '||TRIM(c.pers_apellpaterno)||' '||TRIM(c.pers_apellmaterno) ILIKE '%$descrip%'     
                                                )");
	}

	
	function orderUno(){
		$this->addOrder("a.enca_id");
	}

}


if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
            include("../../library/library.php");
            /*	verificacion a nivel de usuario */
            verificaUsuario(1);
            verif_framework();

            $param= new manUrlv1();
            $param->removePar('control');
            $param->removePar('relacionamento_id');
            $param->removePar('pg'); /* Remuevo el parametro */

            /* Recibo la pagina actual de la lista y lo agrego como parametro a ser enviado por la URL.  Este dato viene por POST */
            $pg = getParam("pg");
            $param->addParComplete('pg',$pg); /* Agrego el parametro */

            //	conexion a la BD
            $conn = new db();
            $conn->open();

            $dml=new encargaturas();

            switch($control){
                case 1: // Guardar
                    $dml->guardar();
                    break;
                case 2: // Eliminar
                    $dml->eliminar();
                    break;
            }
            //	cierra la conexion con la BD
            $conn->close();
    }
}