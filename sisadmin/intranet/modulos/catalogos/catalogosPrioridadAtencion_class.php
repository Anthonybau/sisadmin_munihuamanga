<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class clsPrioriAtencion extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='prioridad_atencion'; //nombre de la tabla
		$this->setKey='prat_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

		/* Destinos luego de actulizar o agregar un registro */
		$this->destinoUpdate = "catalogosPrioridadAtencion_buscar.php";
		$this->destinoInsert = "catalogosPrioridadAtencion_buscar.php";
		$this->destinoDelete = "catalogosPrioridadAtencion_buscar.php";
	}


	function getSql(){
		$sql=new clsPrioriAtencion_SQLlista();
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
				
		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	
			$otable = new  Table("","100%",6);

			$sql=new clsPrioriAtencion_SQLlista();
						
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
					$otable->addColumnHeader("Descripci&oacute;n",true,"75%", "L","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); // T�tulo, Ordenar?, ancho, alineaci�n
					$paramFunction->replaceParValue('colOrden',$colOrden++);					
					$otable->addColumnHeader("Periodo de Vencimiento",true,"20%", "L","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); // T�tulo, Ordenar?, ancho, alineaci�n
					
					$otable->addRow(); // adiciona la linea (TR)
					while ($rs->getrow()) {
						$id = $rs->field("prat_id"); // captura la clave primaria del recordsource
						$campoTexto_de_Retorno = especialChar($rs->field("prat_descripcion"));

						$otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");
						$otable->addData(addLink(str_pad($id,4,'0',STR_PAD_LEFT),"catalogosPrioridadAtencion_edicion.php?id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));
						
						$otable->addData($rs->field("prat_descripcion"));
						$otable->addData($rs->field("prat_dias").' '.$rs->field("tipo_periodo"));
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

class clsPrioriAtencion_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.*,LPAD(a.prat_id::TEXT,4,'0') as cod_pa,
                                        a.prat_descripcion||CASE WHEN a.prat_dias>0 THEN ' ['||a.prat_dias||' '||b.tabl_descripcion||']'
                                                            ELSE '' END AS prat_descripcion_larga,
					x.usua_login as username,
                                        b.tabl_descripcion as tipo_periodo
				FROM prioridad_atencion a
                                LEFT JOIN tabla b ON a.tabl_tipo_periodo=b.tabl_id
				LEFT JOIN usuario x ON a.usua_id=x.usua_id
				";
	
	}
	
	function whereID($id){
		$this->addWhere("a.prat_id=$id");
	}

	function whereDescrip($descrip){
		if($descrip) $this->addWhere("a.prat_descripcion ILIKE '%$descrip%'");
	}

	function orderUno(){
		$this->addOrder("a.prat_id DESC");
	}

	function getSQL_cbox(){
		$sql="SELECT prat_id,prat_descripcion_larga
				FROM (".$this->getSQL().") AS a ORDER BY 1";
		return $sql;
	}
	
}

function saca_dias_excedidos($nrbusc_fproceso,$fecha_recibe,$prioridad_atencion_dias){
    $dias_excedidos=0;
    //$prioridad_atencion_dias=4;
    if ($prioridad_atencion_dias>0){
        //$array_fecha_recibe  = explode("/",$fecha_recibe);
        //$anno_recibe=$array_fecha_recibe[2];
        //$mes_recibe=$array_fecha_recibe[1];

        //$array_fecha_proceso = explode("/",$nrbusc_fproceso);
        //$anno_proceso=$array_fecha_proceso[2];
        //$mes_proceso=$array_fecha_proceso[1];


        //for($anno_recibe;$anno_recibe<=$anno_proceso;$anno_recibe++){
        //    if($anno_recibe==$anno_proceso){
        //
        //    }
        //    for($mes_recibe;$mes_recibe<=$mes_proceso;$mes_recibe++){
        //    }
        //    $mes_recibe=1;
        //    $mes_proceso=12;
        //}
        //checkdate  ( int $month  , int $day  , int $year  )

        $dias_dif=getDbValue("SELECT CAST('$nrbusc_fproceso' AS DATE) - CAST('$fecha_recibe' AS DATE)");

        $dias_excedidos=$dias_dif-$prioridad_atencion_dias;

        if($dias_excedidos<0) $dias_excedidos=0;

        //$d = mktime(0,0,0,$mes,1,$anno);
        //$dias_excedidos=$fecha[2] .'-'.$fecha[1] .'-'. $fecha[0];

    }
    return ($dias_excedidos);
}

if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
            include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
            /*	verificación a nivel de usuario */
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

            $dml=new clsPrioriAtencion();

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
?>