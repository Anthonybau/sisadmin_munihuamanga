<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class clsPersona extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='persona'; //nombre de la tabla
		$this->setKey='pers_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;

		/* Destinos luego de actulizar, agregar o eliminar un registro */
		$this->destinoInsert='personalDatosPersonales_buscar.php';
		$this->destinoUpdate='personalDatosPersonales_buscar.php';
		$this->destinoDelete='personalDatosPersonales_buscar.php';

		$this->arrayNameVar[0]='nomeCampoForm';
		$this->arrayNameVar[1]='busEmpty';
		$this->arrayNameVar[2]='cadena';
		$this->arrayNameVar[3]='pg';
		$this->arrayNameVar[4]='colSearch';
		$this->arrayNameVar[5]='numForm';

	}

        function getSql(){
		$sql=new clsPersona_SQLlista();
		$sql->whereID($this->id);
		return($sql->getSQL());
	}

        function addField(&$sql){
                $sql->addField("pers_tipo_persona", 2, "Number");
		$sql->addField("pers_actualfecha", date('d/m/Y').' '.date('H:i:s'), "String");
		$sql->addField("pers_actualusua", getSession("sis_userid"), "String");
	}

	function buscar($op,$formData,$arrayParam,$pg,$Nameobj='',$nbusc_char='')

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
                
                if(!$nbusc_char)
                    $nbusc_char=is_array($formData)?$formData['nbusc_char']:$paramFunction->getValuePar('nbusc_char');

                $param->removePar('nbusc_char');
                $param->addParComplete('nbusc_char',$nbusc_char);

		if(strlen($cadena)>0 or $busEmpty==1 or $nbusc_char){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias


			$sql=new clsPersona_SQLlista();
                        $sql->whereNOAdmin();
                        $sql->whereTipoPersona(2); //PERSONA EXTERNA
                        
                        if($nbusc_char)
                            $sql->whereChar($nbusc_char);
			else

                            //se analiza la columna de busqueda
                            switch($colSearch){
				case 'codigo': // si se recibe el campo id
					$sql->whereDNI($cadena);
					break;

				default:// si se no se recibe ningun campo de busqueda
					if(ctype_digit($cadena)) //si la cadena recibida son todos digitos
                                            $sql->whereDNI($cadena);
					else
                                            $sql->whereDescrip($cadena);
					break;
				}
			$sql->orderDos();

			$sql=$sql->getSQL();

//			$objResponse->addAlert($sql);

			//guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
			if ($op==1 && !$nomeCampoForm) setSession("cadSearch",$cadena);

			$rs = new query($conn, strtoupper($sql),$pg,40);
			$otable = new  Table("","100%",6, true, 'tLista');

			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;

			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");

			if ($rs->numrows()>0) {
					$otable->addColumnHeader("<input type=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">"); // Coluna com checkbox
					$colOrden=1;
					$paramFunction->replaceParValue('colOrden',$colOrden++);
					$otable->addColumnHeader("DNI",true,"5%", "L","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); // T�tulo, Ordenar?, ancho, alineaci�n
					$paramFunction->replaceParValue('colOrden',$colOrden++);
					$otable->addColumnHeader("Apellidos y Nombres",true,"95%", "L","javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($paramFunction->getUrl())."','$pg','$Nameobj')"); // T�tulo, Ordenar?, ancho, alineaci�n

					$otable->addRow(); // adiciona la linea (TR)
					while ($rs->getrow()) {
						$id = $rs->field("pers_id"); // captura la clave primaria del recordsource
						$campoTexto_de_Retorno = especialChar($rs->field("empleado"));

						$otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" onclick=\"checkform(frm,this)\">");
						$otable->addData(addLink($rs->field("pers_dni"),"personalDatosPersonales_edicion.php?id=$id&".$param->buildPars(false),"Click aqu&iacute; para consultar o editar el registro"));

						$otable->addData($rs->field("empleado"));
						$otable->addRow();
					}
				$contenido_respuesta=$button->writeHTML();
				$contenido_respuesta.=$otable->writeHTML();
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
				$contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";


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


	/* Nombre del archivo php de la clase */
	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1);
	}
} /* Fin de la clase */

class clsPersona_SQLlista extends selectSQL {
	function __construct(){
		$this->sql = "SELECT a.*,
                                   a.pers_apellpaterno||' '||a.pers_apellmaterno||' '||a.pers_nombres AS empleado,
                                   x.usua_login as username,
                                   y.usua_login as usernameactual
			FROM personal.persona a
                        LEFT JOIN admin.usuario x ON a.usua_id=x.usua_id
                        LEFT JOIN admin.usuario y ON a.pers_actualusua=y.usua_id

	";
	}

	function whereID($id){
		$this->addWhere("a.pers_id=$id");
	}

	function whereDNI($dni){
		$this->addWhere("a.pers_dni='$dni'");
	}
	function whereNOAdmin(){
		$this->addWhere("a.pers_id>1");
	}

	function whereDepeID($depe_id){
		$this->addWhere("a.depe_id=$depe_id");
	}

        function whereChar($char){
            $this->addWhere("SUBSTR(a.pers_apellpaterno,1,1)='$char'");
        }

	function whereDescrip($descrip){
		$this->addWhere("a.pers_apellpaterno||' '||a.pers_apellmaterno||' '||a.pers_nombres ILIKE '%$descrip%'");
	}

        function whereTipoPersona($tipo_persona){
		$this->addWhere("a.pers_tipo_persona=$tipo_persona");
	}
        
	function orderUno(){
		$this->addOrder("a.pers_apellpaterno,a.pers_apellmaterno,a.pers_nombres DESC");
	}
        
        function orderDos(){
		$this->addOrder("a.pers_id DESC");
	}
        
        function getSQL_cbox(){
            $sql="SELECT a.pers_id,a.empleado
				FROM (".$this->getSQL().") AS a
                         WHERE a.pers_id>1 
                    ORDER BY 2";
            return $sql;
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

            $dml=new clsPersona();
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

                    case 3: // Anular
                            $dml->anular('9');
                            break;

                    case 4: // Activar
                            $dml->anular('1');
                            break;

            }
            //	cierra la conexión con la BD
            $conn->close();
    }
}