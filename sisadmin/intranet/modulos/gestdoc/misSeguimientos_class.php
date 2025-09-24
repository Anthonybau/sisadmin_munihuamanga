<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class misSeguimientos extends entidad {

	function __construct($id='',$title=''){

	}


	function getSql(){
		$sql=new misSeguimientos_SQLlista();
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
                $tiex_id=is_array($formData)?$formData['nbusc_tiex']:$paramFunction->getValuePar('nbusc_tiex');
                $param->removePar('clear');
		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias	

                        $sql=new misSeguimientos_SQLlista();

                        $sql->whereUsuaID(getSession("sis_userid"));
                        
                        if($tiex_id){
			    $sql->whereTiExpID($tiex_id);
			}
                        
			//se analiza la columna de busqueda
			switch($colSearch){
				case 'codigo': // si se recibe el campo id
					$sql->whereID($cadena);
					break;
	
				default:// si se no se recibe ningun campo de busqueda
					if(is_numeric($cadena)){ //si la cadena recibida son todos digitos
                                            $sql->whereID($cadena);
                                        }else{
                                            $sql->whereDescrip($cadena);
                                        }
					break;
				}
			$sql->orderUno(); //ordena por el ID mas recientemente creado
			
			$sql=$sql->getSQL();
			//echo $sql;
			//$objResponse->addAlert($sql);
		
			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) {
                            setSession("cadSearch",$cadena);			
                        }
                        
			$rs = new query($conn, strtoupper($sql),$pg,40);
			$otable = new  Table("","100%",10,true,'tLista');


                        
			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
					
					$otable->addColumnHeader(NAME_EXPEDIENTE,false,"5%", "L");
					$otable->addColumnHeader("Fecha",false,"5%", "C");
					$otable->addColumnHeader("TExp",false,"4%", "L");
					$otable->addColumnHeader("N&uacute;mero",false,"25%", "C");
					$otable->addColumnHeader("Asunto",false,"57%", "C");
                                        $otable->addColumnHeader("",false,"5%", "L");
					$otable->addRow(); // adiciona la linea (TR)
					while ($rs->getrow()) {
						$id = $rs->field("id"); // captura la clave primaria del recordsource
                                                $depeid=$rs->field("depe_id");
						$campoTexto_de_Retorno = especialChar($rs->field("tiex_descripcion"));

                                                    //$otable->addData(addLink($id,"javascript:abreConsulta('$id')","Click aqu&iacute; para Seguimiento de registro"));
                                                $otable->addData(addLink($id,"javascript:lectorPDF('/sisadmin/gestdoc/controllers/procesar_data.php?nr_numTramite=$id&vista=NoConsulta','Seguimiento')","Click aqu&iacute; para Seguimiento de registro"));                                                
                                                
                                                $otable->addData(dtos($rs->field("desp_fecha")));
						$otable->addData($rs->field("tiex_abreviado"));
						$otable->addData($rs->field("num_documento"));
                                                
						$otable->addData(addLink(substr($rs->field("desp_asunto"),0,40)."...<img src=\"../../img/mas_info.gif\" width=\"14\" height=\"9\" align=\"absmiddle\" border=\"0\">","javascript:xajax_verDetalle(1,'$id','default',$depeid,'',0);return false;","Click aqu&iacute; para ver detalles del registro"));
                                                $otable->addData(btnSeguimientos($id));
                                                
                                                $otable->addRow();                             

					}
				$contenido_respuesta=$button->writeHTML();
				$contenido_respuesta.=$otable->writeHTML();
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";


			} else {
				$otable->addColumnHeader("!NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); 
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
		else{
			return $contenido_respuesta;
                }
	}


	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	
}

class misSeguimientos_SQLlista extends selectSQL {

	function __construct(){
            
                    $this->sql="SELECT  a.dese_id,
                                        a.usua_id,
                                        b.desp_id::TEXT as id,
                                        c.tiex_descripcion,
                                        b.desp_fecha,
                                        b.depe_id,
                                        b.desp_id_orden,
                                        c.tiex_abreviado,
                                        LPAD(b.desp_numero::TEXT,6,'0')||'-'||b.desp_anno||COALESCE('-'||b.desp_siglas,'') AS num_documento,
                                        b.desp_asunto
				FROM gestdoc.despachos_seguimiento a
                                LEFT JOIN gestdoc.despachos b ON a.desp_id=b.desp_id
                                LEFT JOIN catalogos.tipo_expediente c ON b.tiex_id=c.tiex_id
				";
	
	}
	
	function whereID($id){
                if($id>intval($id)){
                    $this->addWhere("b.desp_id=$id");
                }
                else 
                    $this->addWhere("b.desp_expediente=$id");
                    
                    //$this->addWhere("a.desp_id=$id");
                
	}


        function whereTiExpID($tiex_id){
		$this->addWhere("b.tiex_id=$tiex_id");
	}
        
        function whereUsuaID($usua_id){
		$this->addWhere("a.usua_id=$usua_id");
	}

        
	function whereDescrip($descrip){
		if($descrip) $this->addWhere("b.desp_asunto ILIKE '%$descrip%'");
	}

	function orderUno(){
		$this->addOrder("a.dese_id DESC");
	}
        	
}


function btnSeguimientos($desp_id){
    $botones="<div class=\"dropdown\">
                <button class=\"btn btn-default dropdown-toggle btn-sm\" type=\"button\" id=\"dropdownMenu1\" data-toggle=\"dropdown\"><span class=\"glyphicon glyphicon-cog\" aria-hidden=\"true\"></span> Opciones
                <span class=\"caret\"></span></button>
                <ul class=\"dropdown-menu dropdown-menu-right\" aria-labelledby=\"dropdownMenu1\">";

    $botones.="<li><a href=\"javascript:xajax_eliminarSeguimiento('$desp_id')\" target=\"content\">Eliminar Seguimiento</a></li>";        
    
    
    $botones.="</ul>
              </div>";
    return($botones);    
}
