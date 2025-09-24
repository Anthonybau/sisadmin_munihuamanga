<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class archivador extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='archivador'; //nombre de la tabla
		$this->setKey='arch_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

		/* Destinos luego de actulizar o agregar un registro */
		$this->destinoUpdate = "catalogosArchivadores_buscar.php";
		$this->destinoInsert = "catalogosArchivadores_buscar.php";
		$this->destinoDelete = "catalogosArchivadores_buscar.php";
	}

	function addField(&$sql){
            	//$sql->addField("depe_id",getSession("sis_depeid"),"Number");
		$sql->addField("arch_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
		$sql->addField("arch_actualusua", getSession("sis_userid"), "String");
                
                if ($_POST["hx_arch_personal"])
                    $sql->addField("arch_personal", 1, "Number");
                else
                    $sql->addField("arch_personal", 0, "Number");

                if ($_POST["hx_arch_disponible"])
                    $sql->addField("arch_disponible", 1, "Number");
                else
                    $sql->addField("arch_disponible", 0, "Number");
	}

	function getSql(){
		$sql=new archivador_SQLlista();
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
		$nbusc_depe_id=is_array($formData)?$formData['nbusc_depe_id']:$paramFunction->getValuePar('nbusc_depe_id');

                $param->removePar('clear');
                $param->addParComplete("nbusc_depe_id", $nbusc_depe_id);

		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	
			$otable = new  Table("","100%",6);

			$sql=new archivador_SQLlista();
			$sql->depeID($nbusc_depe_id);

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
			
			//$objResponse->addAlert($sql);
		
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
					$otable->addColumnHeader("C&oacute;d",true,"5%", "L","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); // T�tulo, Ordenar?, ancho, alineaci�n
					$paramFunction->replaceParValue('colOrden',$colOrden++);
					$otable->addColumnHeader("Tipo",true,"10%", "L","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); // T�tulo, Ordenar?, ancho, alineaci�n
					$paramFunction->replaceParValue('colOrden',$colOrden++);
					$otable->addColumnHeader("A&ntilde;o",true,"5%", "L","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); // T�tulo, Ordenar?, ancho, alineaci�n
					$paramFunction->replaceParValue('colOrden',$colOrden++);					
					$otable->addColumnHeader("Descripci&oacute;n",true,"50%", "L","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); // T�tulo, Ordenar?, ancho, alineaci�n
					$paramFunction->replaceParValue('colOrden',$colOrden++);					
					$otable->addColumnHeader("&iquest;Personal?",true,"10%", "L","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); // T�tulo, Ordenar?, ancho, alineaci�n
					$paramFunction->replaceParValue('colOrden',$colOrden++);
					$otable->addColumnHeader("Habilitado",true,"10%", "L","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); // T�tulo, Ordenar?, ancho, alineaci�n
					$paramFunction->replaceParValue('colOrden',$colOrden++);
					$otable->addColumnHeader("Usuario",true,"10%", "L","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); // T�tulo, Ordenar?, ancho, alineaci�n
					
					$otable->addRow(); // adiciona la linea (TR)
					while ($rs->getrow()) {
						$id = $rs->field("arch_id"); // captura la clave primaria del recordsource
						$campoTexto_de_Retorno = especialChar($rs->field("tiex_descripcion"));

						$otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");
						$otable->addData(addLink(str_pad($id,4,'0',STR_PAD_LEFT),"catalogosArchivadores_edicion.php?id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
						$otable->addData($rs->field("tipo"));
						$otable->addData($rs->field("arch_anno"));
						$otable->addData($rs->field("arch_descripcion"));

                                                if($rs->field("arch_personal")==0)
                                                    $otable->addData("NO");
                                                else
                                                    $otable->addData("SI");

                                                if($rs->field("arch_disponible")==0)
                                                    $otable->addData("NO");
                                                else
                                                    $otable->addData("SI");

						$otable->addData($rs->field("username"));
						$otable->addRow();
					}
				$contenido_respuesta=$button->writeHTML();
				$contenido_respuesta.=$otable->writeHTML();
				$contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

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

class archivador_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.*,
					LPAD(a.arch_id::TEXT,4,'0') as id,
					LPAD(a.arch_id::TEXT,4,'0')||' '||a.arch_descripcion as archivador,
                                        b.tabl_descripcion as tipo,
                                        c.depe_nombre as dependencia,
					x.usua_login as username,
					y.usua_login as usernameactual
				FROM archivador a
                                LEFT JOIN tabla b ON b.tabl_tipo='TIPO_ARCHIVADOR' AND a.arch_tabltipoarchivador=b.tabl_id
                                LEFT JOIN dependencia c ON a.depe_id=c.depe_id 
				LEFT JOIN usuario x ON a.usua_id=x.usua_id
				LEFT JOIN usuario y ON a.arch_actualusua=y.usua_id
				";
	
	}
	
	function whereID($id){
		$this->addWhere("a.arch_id=$id");
	}

        function depeID($depe_id){
		$this->addWhere("a.depe_id=$depe_id");
	}

        function whereAnno($anno){
		$this->addWhere("a.arch_anno=$anno");
	}

        function whereDisponible(){
		$this->addWhere("a.arch_disponible=1");
	}

        function whereMisArchivadores($depe_id,$usua_id){
                $this->addWhere("((a.depe_id=$depe_id AND arch_personal=0) OR (a.usua_id=$usua_id AND a.arch_personal=1))");
        }


	function whereDescrip($descrip){
		if($descrip) $this->addWhere("a.arch_descripcion ILIKE '%$descrip%'");
	}

	
	function orderUno(){
		$this->addOrder("a.arch_id DESC");
	}


	function getSQL_cbox(){
		$sql="SELECT arch_id,LPAD(a.arch_id::TEXT,4,'0')||' '||arch_anno::TEXT||'-'||arch_descripcion||' ['||substr(tipo,1,4)||']'||
                                CASE WHEN arch_personal=1 THEN '['||username||']'
                                     ELSE '' END
				FROM (".$this->getSQL().") AS a ORDER BY 1";
		return $sql;
	}
	
}


if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
            include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
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

            $dml=new archivador();

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
