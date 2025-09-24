<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class ubigeo extends entidad {
	
	function __construct($id='',$title=''){
		
		
	}

        
        function buscarUbigeo($op,$cadena,$colSearch,$colOrden=1,$Nameobj,$accion=1)
        {
                global $conn;

                $objResponse = new xajaxResponse();
                //$objResponse->setCharEncoding('utf-8');	

                $cadena=trim($cadena);

                if($cadena){


                        $sql=new ubigeo_SQLlista();

                        if(ctype_digit($cadena)){ //si la cadena recibida son todos digitos
                            $sql->whereID($cadena);
                        }else{
                            $sql->whereDescrip($cadena);
                        }
                        
			$sql->orderUno();
			$sql=$sql->getSQL();
                        
                    //	  echo $sql;
                    //	  $objResponse->addAlert($sql);
                        $otable = new  Table("","100%",4);
                        $btnFocus="";
                        $rs = new query($conn, strtoupper($sql));
                        
                                if ($rs->numrows()==1) {
                                    $rs->getrow();
                                    $id = $rs->field("ubig_id");
                                    $campoTexto_de_Retorno = especialChar($rs->field("distrito"));
                                    $objResponse->addScript("xajax_eligeUbigeo('$id','$campoTexto_de_Retorno',$accion)");
                                    return $objResponse;
                                }elseif ($rs->numrows()>0) {
                                                $link=addLink("Cerrar","javascript:xajax_clearDiv('$Nameobj')");        
                                                $otable->addColumnHeader("$link",false,"1%","C"); 
                                                $otable->addColumnHeader("C&oacute;digo","9%", "L");
                                                $otable->addColumnHeader("Departamento/Provincia/Distrito","90%", "L"); 
                                                $otable->addColumnHeader("",false,"1%","C"); 

                                                $otable->addRow(); // adiciona la linea (TR)
                                                while ($rs->getrow()) {
                                                        $id = $rs->field("ubig_id");// captura la clave primaria del recordsource
                                                        $campoTexto_de_Retorno = especialChar($rs->field("distrito"));
                                                                                                                
                                                        $button = new Button;
                                                        $button->setDiv(FALSE);
                                                        $button->setStyle("");
                                                        $button->addItem("Aceptar","javascript:xajax_eligeUbigeo('$id','$campoTexto_de_Retorno',$accion)","content",2,0,"botonAgg","button","","btn_$id");
                                                        $otable->addData($button->writeHTML());	
                                                        
                                                        $otable->addData($rs->field("ubig_id"));
                                                        $otable->addData($rs->field("distrito"));
                                                        if($accion==1){
                                                            $otable->addData("<a class=\"link\" href=\"#\" onClick=\"javascript:modiProveedor($id)\"><img src=\"../../img/editar.gif\" border=0 align=absmiddle hspace=1 alt=\"Editar\"></a>");
                                                        }
                                                        else {
                                                               $otable->addData("&nbsp;");
                                                        }
                                                        $otable->addRow();
                                                        $btnFocus=$btnFocus?$btnFocus:"btn_$id";
                                                }

                                        $contenido_respuesta=$otable->writeHTML();
                                        $contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

                                } else {
                                        $otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); 
                                        $otable->addRow();
                                        $contenido_respuesta=$otable->writeHTML();
                                }
                        }
                else
                        $contenido_respuesta="";

            $objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
            $objResponse->addScript("document.frm.$btnFocus.focus()");

            return $objResponse;

        }

	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	
}


class ubigeo_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT a.ubig_id,
                                   a.distrito
                            FROM catalogos.view_ubigeo a
				";
	}

	function whereID($id){
            $this->addWhere("a.ubig_id='$id'");
	}

	function whereUbigID($ubig_id){
            $this->addWhere("LEFT(a.ubig_id,LENGTH('$ubig_id'))='$ubig_id'");
        }
        
	function whereDescrip($descrip){
            if($descrip) $this->addWhere("a.distrito ILIKE '%$descrip%'");	
	}
	
	function orderUno(){
            $this->addOrder("a.ubig_id");		
	}
        
	function getSQL_cbox(){
		$sql="SELECT a.ubig_id AS id,
                             a.distrito AS descripcion
                            FROM (".$this->getSQL().") AS a
                       ORDER BY 1";
		return $sql;
	}
        
}
