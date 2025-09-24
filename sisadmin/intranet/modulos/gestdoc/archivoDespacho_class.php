<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class despachoArchivo extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='despachos_derivaciones'; //nombre de la tabla
		$this->setKey='dede_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

		/* Destinos luego de actulizar o agregar un registro */
		$this->destinoUpdate = "procesoDespacho_buscar.php";
		$this->destinoInsert = "procesoDespacho_buscar.php";
		$this->destinoDelete = "procesoDespacho_buscar.php";
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
                $nbusc_user_id=is_array($formData)?$formData['nbusc_user_id']:$paramFunction->getValuePar('nbusc_user_id');
                $nbusc_arch_id=is_array($formData)?$formData['nbusc_arch_id']:$paramFunction->getValuePar('nbusc_arch_id');

                $param->removePar('clear');
                $param->removePar('nbusc_depe_id');
                $param->addParComplete("nbusc_depe_id", $nbusc_depe_id);
                $param->removePar('nbusc_user_id');
                $param->addParComplete("nbusc_user_id", $nbusc_user_id);
                $param->addParComplete("nbusc_arch_id", $nbusc_arch_id);

		if(strlen($cadena)>0 or $busEmpty==1){ //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias
	
			$otable = new  Table("","100%",6);

			$sql=new despachoArchivo_SQLlista();
			$sql->whereDepeDestinoID($nbusc_depe_id);
                        $sql->whereEstado(6);

                        if($nbusc_user_id)
                            $sql->whereUsuaArchivaID($nbusc_user_id);

                        if($nbusc_arch_id)
                            $sql->whereArchID($nbusc_arch_id);
                        
			//se analiza la columna de busqueda
                        if($cadena)
			switch($colSearch){
				case 'codigo': // si se recibe el campo id
					$sql->whereID($cadena);
					break;
	
				default:// si se no se recibe ningun campo de busqueda
					if(is_numeric($cadena)) //si la cadena recibida son todos digitos
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
	
			$rs = new query($conn, strtoupper($sql),$pg,40);

			$button = new Button;
			$pg_ant = $pg-1;
			$pg_prox = $pg+1;
			
			if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
			if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,xajax.getFormValues('frm'),'".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");
			
			if ($rs->numrows()>0) {
					$otable->addColumnHeader("<input type=\"checkbox\" class=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">"); // Coluna com checkbox
					$otable->addColumnHeader(NAME_EXPEDIENTE,true,"4%", "L");
					$otable->addColumnHeader("Di",true,"2%", "L");
					$otable->addColumnHeader("Fecha",true,"5%", "C"); 
					$otable->addColumnHeader("TExp",true,"4%", "L"); 
					$otable->addColumnHeader("N&uacute;mero",true,"20%", "C"); 
					$otable->addColumnHeader("Asunto",true,"45%", "C"); 
                                        $otable->addColumnHeader("Archivador",false,"15%", "C"); 
                                        $otable->addColumnHeader("Respons",false,"5%", "L");
					
					$otable->addRow(); // adiciona la linea (TR)
					while ($rs->getrow()) {
						$idPadre = $rs->field("id"); // captura la clave de la tabla padre

                                                $id=$rs->field("dede_id");
						$campoTexto_de_Retorno = especialChar($rs->field("tiex_descripcion"));
                                                $registro=$idPadre."_".$id;
						$otable->addData("<input type=\"checkbox\" class=\"checkbox\" name=\"sel[]\" value=\"$registro\" onclick=\"checkform(frm,this)\">");

                                                $otable->addData(addLink($idPadre,"javascript:abreConsulta('$idPadre')","Click aqu&iacute; para Seguimiento de registro"));

                                                if($rs->field("dede_concopia")) 
                                                    $otable->addData("Cc");
                                                else
                                                    $otable->addData("");
                                                $otable->addData(dtos($rs->field("desp_fecha")));
						$otable->addData($rs->field("tiex_abreviado"));
						$otable->addData($rs->field("num_documento"));
						$otable->addData(addLink(substr($rs->field("desp_asunto"),0,40)."...<img src=\"../../img/mas_info.gif\" width=\"14\" height=\"9\" align=\"absmiddle\" border=\"0\">","javascript:xajax_verDetalle(1,'$idPadre','archivado','$nbusc_depe_id','$id',0)","Click aqu&iacute; para ver detalles del registro"));
                                                $otable->addData($rs->field("archivador"));
                                                $otable->addData($rs->field("usuario_archiva"));
                                                if($rs->field("dede_acum_derivaciones")>0)
                                                   $otable->addRow('ATENDIDO');
                                                else
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

        function eliminarMisDerivaciones(){
		global $conn,$param;
                $nbusc_user_id=getParam("nbusc_user_id");
                $param->removePar('nbusc_user_id');
                $param->addParComplete("nbusc_user_id", $nbusc_user_id);

		$destinoDelete=$this->destinoDelete.$param->buildPars(true)."&clear=1";

		/* captura y prepara la lista de registros a ser eliminados */
		$arLista_elimina = getParam("sel");
		
                if(!is_array($arLista_elimina)) {
                    alert('Sin registros seleccionados para procesar...');
                    return;
                }

                $lista_elimina='';
                for ($i = 0; $i < count($arLista_elimina); $i++) {
                    $arrayPadreHijo=explode('_',$arLista_elimina[$i]);
                    $lista_elimina.=iif($lista_elimina,'==','','',',').$arrayPadreHijo[0];
                }


		/* Sql a ejecutar */
		$sqlCommand ="DELETE FROM $this->setTable WHERE desp_id ";
		$sqlCommand.=" IN ($lista_elimina) " ;
		$sqlCommand.=" AND usua_idcrea=".getSession("sis_userid"). " AND dede_estado=2 AND dede_donde_se_creo=1" ;
                $sqlCommand.=" RETURNING $this->setKey ";

		/* Ejecuto la sentencia */
                //alert($sqlCommand);
		$padre_id=$conn->execute($sqlCommand);
		$error=$conn->error();
		if($error) alert($error);
		else{
                        if ($this->setNivelAudita>0){
                            //ejecuto el insert en la tabla auditoria
                            $sql = new UpdateSQL();
                            $sql->setTable($this->setTable.'_auditoria');
                            $sql->setAction("INSERT");
                            $sql->addField('sql_type',3,"Number");
                            $sql->addField('sql_command',$sqlCommand);
                            $sql->addField('usua_id', getSession("sis_userid"), "Number");
                            $conn->execute($sql->getSQL());
                            $error=$conn->error();
                            if($error){alert($error);}/* Muestro el error y detengo la ejecución */
                        }
                         //alert($destinoDelete);
			redirect($destinoDelete,"content");
		}
	}

	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}	
}

class despachoArchivo_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.*,LPAD(b.desp_numero::TEXT,6,'0')||'-'||b.desp_anno||COALESCE('-'||b.desp_siglas,'') AS num_documento,
					b.desp_id::TEXT as id,
                                        b.desp_fecha,
                                        b.desp_asunto,
                                        c.tiex_abreviado,
                                        c.tiex_descripcion,
                                        d.depe_nombrecorto as depe_nombrecorto_origen,d.depe_nombre as depe_nombre_origen,

                                        e.usua_login||'-'||eee.pers_apellpaterno||' '||SUBSTRING(eee.pers_nombres,1,CASE WHEN POSITION(' ' IN eee.pers_nombres)>0 THEN POSITION(' ' IN eee.pers_nombres) ELSE 100 END) AS usuario_origen,
                                        f.usua_login||'-'||fff.pers_apellpaterno||' '||SUBSTRING(fff.pers_nombres,1,CASE WHEN POSITION(' ' IN fff.pers_nombres)>0 THEN POSITION(' ' IN fff.pers_nombres) ELSE 100 END) AS usuario_archiva,

                                        g.arch_anno::TEXT||'-'||g.arch_descripcion AS archivador,
                                        h.tabl_descripcion AS tipo_archivador,
					x.usua_login as username
				FROM despachos_derivaciones a
                                LEFT JOIN despachos b ON a.desp_id=b.desp_id
                                LEFT JOIN tipo_expediente c ON b.tiex_id=c.tiex_id
                                LEFT JOIN dependencia d ON a.depe_idorigen=d.depe_id
                                LEFT JOIN usuario e  ON a.usua_idorigen=e.usua_id
                                LEFT JOIN personal.persona_datos_laborales ee on  e.pdla_id=ee.pdla_id
                                LEFT JOIN personal.persona eee on  ee.pers_id=eee.pers_id                                
                                
                                LEFT JOIN usuario f  ON a.usua_idarchiva=f.usua_id
                                LEFT JOIN personal.persona_datos_laborales ff on  f.pdla_id=ff.pdla_id
                                LEFT JOIN personal.persona fff on  ff.pers_id=fff.pers_id                                
                                
                                LEFT JOIN archivador g  ON a.arch_id=g.arch_id
                                LEFT JOIN tabla h ON h.tabl_tipo='TIPO_ARCHIVADOR' AND g.arch_tabltipoarchivador=h.tabl_id
				LEFT JOIN usuario x ON a.usua_idcrea=x.usua_id
				";

	}

	function whereID($id){
                //si se ha enviado con secuencia de expediente, es decir en decimal
                if($id>intval($id))
                    $this->addWhere("b.desp_id=$id");
                else 
                    $this->addWhere("b.desp_expediente=$id");
	}


        function whereEstado($estado){
		$this->addWhere("a.dede_estado=$estado");
	}

        function whereDepeDestinoID($depe_id){
		$this->addWhere("a.depe_iddestino=$depe_id");
	}

        function whereArchID($arch_id){
		$this->addWhere("a.arch_id=$arch_id");
	}

        function whereUsuaDestinoID($usua_id){
		$this->addWhere("a.usua_iddestino=$usua_id");
	}

        function whereUsuaArchivaID($usua_id){
		$this->addWhere("a.usua_idarchiva=$usua_id");
	}

	function whereDescrip($descrip){
		if($descrip) $this->addWhere("b.desp_asunto ILIKE '%$descrip%'");
	}

        function orderUno(){
		$this->addOrder("b.desp_id_orden DESC");
	}
        
	function orderUnox(){
		$this->addOrder("a.desp_id DESC");
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

            /* Recibo la página actual de la lista y lo agrego como par�metro a ser enviado por la URL.  Este dato viene por POST */
            $pg = getParam("pg");
            $param->addParComplete('pg',$pg); /* Agrego el par�metro */

            //	conexión a la BD
            $conn = new db();
            $conn->open();

            $dml=new despachoArchivo();

            switch($control){
                    case 1: // Guardar
                            $dml->guardar();
                            break;

                    case 2: // Eliminar
                            $dml->eliminarMisDerivaciones(); //eliminar mis derivaciones
                            break;
            }
            //	cierra la conexión con la BD
            $conn->close();
    }
}