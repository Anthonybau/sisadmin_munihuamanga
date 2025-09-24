<?php
include("personalDatosLaborales_class.php");
function buscarEmpleadoLabora($op,$cadena,$procedure,$Nameobj)
{
        global $conn,$param;
        $objResponse = new xajaxResponse();

        //$objResponse->addAlert($nbusc_sitlaboral);
        if(strlen($cadena)>0 ){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias

                $otable = new  Table("","100%",6);

                $sql=new clsDatosLaborales_SQLlista();

                if(ctype_digit($cadena)) //si la cadena recibida son todos digitos
                    $sql->whereDNI($cadena);
                else
                    $sql->whereDescrip($cadena);

                $sql->orderUno();

                $sql=$sql->getSQL();

//			$objResponse->addAlert($sql);

                $rs = new query($conn, strtoupper($sql));

                if ($rs->numrows()>0) {
                                $otable->addColumnHeader("",false,"1%","C");
                                $otable->addColumnHeader("DNI",true,"5%", "L"); 
                                $otable->addColumnHeader("Apellidos y Nombres",true,"40%", "L");
                                $otable->addColumnHeader("Dependencia",true,"20%", "L");
                                $otable->addColumnHeader("Sit.Laboral",true,"15%", "L"); 
                                $otable->addColumnHeader("Cargo Est.",true,"20%", "L"); 

                                $otable->addRow(); // adiciona la linea (TR)
                                $btnFocus="";
                                while ($rs->getrow()) {
                                        $id = $rs->field("pdla_id"); // captura la clave primaria del recordsource
                                        $campoTexto_de_Retorno = especialChar($rs->field("empleado"));
                                        
                                        $button = new Button;
                                        $button->setDiv(FALSE);
                                        $button->setStyle("");
                                        $button->addItem("Aceptar","javascript:xajax_$procedure('$id','$campoTexto_de_Retorno')","content",2,0,"botonAgg","button","","btn_$id");
                                        $otable->addData($button->writeHTML());	
                                        
                                        $otable->addData($rs->field("pers_dni"));
                                        $otable->addData($rs->field("empleado"));
                                        $otable->addData($rs->field("depe_nombrecorto"));
                                        $otable->addData($rs->field("sit_laboral"));
                                        $otable->addData($rs->field("cargo_estructural"));
                                        $otable->addRow();
                                        $btnFocus=$btnFocus?$btnFocus:"btn_$id";
                                }
                        $contenido_respuesta.=$otable->writeHTML();
                        $contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";


                } else {
                        $otable->addColumnHeader("!NO SE ENCONTRARON DATOS...!!",false,"100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
                        $otable->addRow();
                        $contenido_respuesta=$otable->writeHTML();
                }
    }
    else{
        $contenido_respuesta="";
    }
    $objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
    $objResponse->addScript("document.frm.$btnFocus.focus()");
    return $objResponse;
}

?>