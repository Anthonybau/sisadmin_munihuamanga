<?php
require_once("../../library/clases/entidad.php");

class clsPersonaContrato extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='personal.persona_contrato'; //nombre de la tabla
		$this->setKey='peco_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;

		/* Destinos luego de actulizar, agregar o eliminar un registro */
		$this->destinoInsert='PersonaContrato_lista.php';
		$this->destinoUpdate='PersonaContrato_lista.php';
		$this->destinoDelete='PersonaContrato_lista.php';

		$this->arrayNameVar[0]='nomeCampoForm';
		$this->arrayNameVar[1]='busEmpty';
		$this->arrayNameVar[2]='cadena';
		$this->arrayNameVar[3]='pg';
		$this->arrayNameVar[4]='colSearch';
		$this->arrayNameVar[5]='numForm';

	}

        function getSql(){
		$sql=new clsPersonaContrato_SQLlista();
		$sql->whereID($this->id);
		return($sql->getSQL());
	}

        function addField(&$sql){
		$sql->addField("peco_actualfecha", 'NOW()', "String");
		$sql->addField("peco_actualusua", getSession("sis_userid"), "String");                
	}

        function buscar($op,$formData,$arrayParam,$pg,$Nameobj='') {
                global $conn,$param,$nomeCampoForm,$id_relacion;
                $objResponse = new xajaxResponse();

                $arrayParam=decodeArray($arrayParam);

                $paramFunction= new manUrlv1();
                $paramFunction->setUrlExternal($arrayParam);

                if($op==1 && !is_array($formData)) $formData=decodeArray($formData);

                $cadena=is_array($formData)?trim($formData['Sbusc_cadena']):$formData;

                $busEmpty=$paramFunction->getValuePar('busEmpty');
                $colSearch=$paramFunction->getValuePar($paramFunction->getValuePar(4));
                $numForm=$paramFunction->getValuePar($paramFunction->getValuePar(5));

                $clear=$paramFunction->getValuePar('clear');
                $pageEdit=$paramFunction->getValuePar('pageEdit');
                $estado=$formData['Sbusc_estado'];

                if($busEmpty==1) { //el proceso solo se ejecuta si la cadena contiene valor o se permite cadenas vacias

                    $sql=new clsPersonaContrato_SQLlista();
                    $sql->wherePadreID($id_relacion);
                    $sql->whereEstado($estado);

                    //se analiza la columna de busqueda
                    switch($colSearch) {
                        case 'numero': // si se recibe el campo id
                            break;

                        default:// si se no se recibe ningun campo de busqueda
                            if($cadena)
                                if(ctype_digit($cadena)){ //si la cadena recibida son todos digitos
                                    $sql->whereID($cadena);
                                }else{
                                    $sql->whereDescrip($cadena);
                                }

                            break;
                    }
                    $sql->orderUno();
                    $sql=$sql->getSQL();
                    //echo $sql;
                    //guardo la cadena en una session, solo si la llamada es desde la pagina de busqueda normal
                    if ($op==1 && !$nomeCampoForm) {
                        $param->replaceParValue($paramFunction->getValuePar(2),$cadena); /* Agrego el par�metro */
                    }

                    $rs = new query($conn, $sql);

                    $otable = new TableSimple("","100%",16,'tLista'); // T�tulo, ancho, Cantidad de columas,id de la tabla
                                /* Guardo la pagina actual en un campo oculto.  Para que al eliminar regrese a la p�gina donde estaba */
                    $otable->addHtml("<input type='hidden' name='".$paramFunction->getValuePar(3)."' value='$pg'>\n");

                    $button = new Button;
                    $pg_ant = $pg-1;
                    $pg_prox = $pg+1;
                    if ($pg>1)                 $button->addItem(LISTA_ANTERIOR,"javascript:xajax_buscar(1,'".encodeArray($formData)."','".encodeArray($arrayParam)."','$pg_ant','$Nameobj')","content");
                    if ($pg<$rs->totalpages()) $button->addItem(LISTA_PROXIMO ,"javascript:xajax_buscar(1,'".encodeArray($formData)."','".encodeArray($arrayParam)."','$pg_prox','$Nameobj')","content");

                    if ($rs->numrows()>0) {
                        /* Agrego las cabeceras de mi tabla */
                        $otable->addColumnHeader("<input type=\"checkbox\" name=\"checkall\" onclick=\"checkform(frm,this)\">"); // Coluna com checkbox
                        $otable->addColumnHeader("C&oacute;d","2%", "C"); 
                        $otable->addColumnHeader("Fecha","5%");
                        $otable->addColumnHeader("N&uacute;mero","15%");
                        $otable->addColumnHeader("Plantilla","15%");
                        $otable->addColumnHeader("Dependencia","15%");
                        $otable->addColumnHeader("Cargo","10%");
                        $otable->addColumnHeader("Sit.Laboral","8%");
                        $otable->addColumnHeader("Periodo","2%");
                        $otable->addColumnHeader("Desde","1%");
                        $otable->addColumnHeader("Hasta","1%");
                        $otable->addColumnHeader("Monto","5%");
                        $otable->addColumnHeader("Mov","1%","","","Movimiento");    
                        $otable->addColumnHeader("Est","2%");
                        $otable->addColumnHeader("Actualizado","15%");    
                        $otable->addColumnHeader("","3%");
                        $otable->addRowHead();

                        while ($rs->getrow()) {
                            $id=$rs->field("peco_id");
                            $pers_id=$rs->field("pers_id");
                            $usua_id=$rs->field("usua_id");
                            $documento=$rs->field("peco_documento");
                            $peco_contractual=$rs->field("peco_contractual");
                            $numero_contrato=$rs->field("numero_contrato");
                            
                            $otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" >");


                            //sif(!$numero_contrato){
                                $otable->addData(addLink($rs->field("pecoid"),PATH_INC."auxiliar.php?pag=../../modulos/personal/PersonaContrato_edicion.php?id=$id,id_relacion=$id_relacion","Click aqu&iacute; para consultar o editar este registro","content","ls-modal"));	
                            //}else{
                              //  $table->addData($rs->field("pecoid"));	
                            //}

                            $otable->addData(dtos($rs->field("peco_fcontrato")));	

                            if($numero_contrato){
                                $otable->addData(addLink($documento,"javascript:imprimir('$id')","Click aqu&iacute; para Imprimir Contrato","content",""));	                
                            }else{
                                $otable->addData($documento);	
                            }

                            $otable->addData($rs->field("plantilla"));	
                            $otable->addData($rs->field("dependencia"));	
                            if($rs->field("peco_cargofuncional")){
                                $otable->addData($rs->field("peco_cargofuncional"));	
                            }else{
                                $otable->addData($rs->field("cargo_clasificado"));	
                            }

                            $otable->addData($rs->field("sit_laboral"));

                            $otable->addData($rs->field("peco_periodo"));
                            $otable->addData(dtos($rs->field("peco_finicio")));
                            $otable->addData(dtos($rs->field("peco_ftermino")));
                            $otable->addData(number_format($rs->field("peco_monto"),2,'.',','),"R");                
                            $otable->addData(substr($rs->field("movimiento"),0,3),"R","",$rs->field("movimiento"));
                            $otable->addData(substr($rs->field("estado"),0,3),"R","",$rs->field("estado"));
                            $otable->addData($rs->field("usernameactual").'/'.$rs->field("peco_actualfecha"));

                            if($rs->field("peco_estado")==9){
                                $otable->addData('&nbsp;');                
                                $otable->addRow('ANULADO'); // adiciona linea
                            }else{

                                    $botones="<div class=\"dropdown\">
                                                <button class=\"btn btn-default dropdown-toggle btn-sm\" type=\"button\" id=\"dropdownMenu1\" data-toggle=\"dropdown\"><span class=\"glyphicon glyphicon-cog\" aria-hidden=\"true\"></span> Opciones
                                                <span class=\"caret\"></span></button>
                                                <ul class=\"dropdown-menu dropdown-menu-right\" aria-labelledby=\"dropdownMenu1\">";
                                    if(inlist($peco_contractual,'1,2')){//contractual desde plantilla
                                    //if($usua_id==getSession('sis_userid')){
                                            if(!$numero_contrato){
                                                $botones.="<li><a href=\"#\" onClick=\"javascript:xajax_generaSecuencia('$id')\" target=\"controle\">".iif($peco_contractual,'==','1',"Generar Contrato","Generar Adenda")."</a></li>";
                                            }else{
                                                //$botones.="<li><a href=\"#\" onClick=\"javascript:imprimir('$id')\" target=\"controle\">Imprimir</a></li>";
                                                $botones.="<li><a href=\"PersonaContrato_edicionContrato.php?id=$id&id_relacion=$id_relacion\"  target=\"content\">Editar</a></li>";
                                                $botones.="<li><a href=\"#\" onClick=\"javascript:xajax_eliminaSecuencia('$id')\" target=\"controle\">".iif($peco_contractual,'==','1',"Eliminar Contrato","Eliminar Adenda")."</a></li>";
                                            }
                                    }
                                            $botones.="<li><a href=\"#\" onClick=\"javascript:imprimir2('$pers_id')\" target=\"controle\">Imprimir Historial Laboral</a></li>";
                                    //}

                                    $botones.="</ul>
                                              </div>";
                                    $otable->addData($botones);                
                                //}else{
                                //    $table->addData("&nbsp;");
                                //}
                                $otable->addRow(); // adiciona linea                
                            }
                        }

                        $contenido_respuesta=$button->writeHTML();
                        $contenido_respuesta.=$otable->writeHTML();
                        $contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:left' align='left'>P&aacute;gina ".$pg." de ".$rs->totalpages()."</div>";
                        $contenido_respuesta.="<div class='Bordeatabla' style='width:50%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";

                    } else {
                        $otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!","100%", "C"); 
                        $otable->addRowHead();
                        $otable->addRow();
                        $contenido_respuesta=$otable->writeHTML();
                    }
                }
                else
                    $contenido_respuesta="";

                //se analiza el tipo de funcionamiento
                if($op==1) {//si es llamado para su funcionamiento en ajax con retornoa a un div
                    $objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
                    $objResponse->addscript("activaSorter()"); // Para activar el orden en la tabla
                    $objResponse->addscript("func_jquerytablas()"); // Para activar las funciones de css de la tabla
                    $objResponse->addscript("$(document).ready(function() {
                                                $('.ls-modal').on('click', function(e){
                                                    e.preventDefault();
                                                    $('#myModal').modal('show').find('.modal-body').load($(this).attr('href'));
                                                }); 
                                            });");
                    return $objResponse;
                }
                else
                    if($op==3) {//si es llamado para su funcionamiento en ajax, desde un una busqueda avanzada, con retorno a un objeto
                        if($Nameobj) {
                            $objResponse->addScript($Nameobj .' = "'.$campoTexto_de_Retorno.'";');
                            return $objResponse;
                        }
                        else
                            return $campoTexto_de_Retorno;
                    }
                    else//si es llamado como una simple funciona de PHP
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
                    if($notice) 
                    	alert($notice,0);
                }
		
		/* */
		if ($this->valueKey) {// modificación
			$last_id=$this->valueKey; 
			if(strpos($destinoInsert, "?")>0)
				$destinoUpdate.="&id=$last_id";
			else
				$destinoUpdate.="?id=$last_id";

		}else{ /* Inserci�n */
			$last_id=$conn->lastid($this->setTable. '_' . $this->setKey . "_seq"); /* Obtengo el Id del registro ingreado.  Ojo esto solo funciona si el campo clave es un serial (est� basado en una secuencia) */								
			if(strpos($destinoInsert, "?")>0)
				$destinoInsert.="&id=$last_id&clear=1";  
			else
				$destinoInsert.="?id=$last_id&clear=1";
		}

                echo "<"."script".">\n";
		echo "parent.parent.content.cerrar();\n";
                echo "parent.parent.content.location.reload();\n";
		echo "</"."script".">\n";

	}


	function guardarContrato(){
		global $conn;
		
		// objeto para instanciar la clase sql
		$sql = new UpdateSQL();
				
		$sql->setTable($this->setTable);
		$sql->setKey($this->setKey, $this->valueKey, $this->typeKey);
	
		include("../guardar_tipoDato.php");
	
		$sql->setAction("UPDATE");

                $sql->addField("peco_actualfecha", 'NOW()', "String");
		$sql->addField("peco_actualusua", getSession("sis_userid"), "String");                
                
		/* Ejecuto el SQL */
                $sqlCommand=$sql->getSQL();
		$conn->execute(" SET session_replication_role = replica; $sqlCommand; SET session_replication_role = DEFAULT;");
                
		$error=$conn->error();
		if($error){                              
                    alert($error);	/* Muestro el error y detengo la ejecucion */
		}else{
                    echo "<"."script".">\n";
                    echo "parent.content.location.reload();\n";
                    echo "</"."script".">\n";
                }

	}
        
	function activar(){
		global $conn,$param;
		
		/* captura y prepara la lista de registros a ser eliminados */ 
		$arLista_anular = getParam("sel");
		if (is_array($arLista_anular)) {
		 $lista_anular = implode(",",$arLista_anular);
		}
		
		if(strtolower($this->typeKey)=='string'){
			/* debido a que el campo clave es char */
			$lista_anular=str_replace(",","','",$lista_anular); 
		}

		/* Sql a ejecutar */
		$sqlCommand="UPDATE $this->setTable SET peco_estado=CASE WHEN peco_estado=1 THEN 9 ELSE 1 END ";
		$sqlCommand.=" WHERE $this->setKey ";
		$sqlCommand.=" IN ($lista_anular) ";
		//$sqlCommand.=" AND usua_id=".getSession("sis_userid");
		$sqlCommand.=" RETURNING $this->setKey ";
                            
		/* Ejecuto la sentencia */
		$padre_id=$conn->execute($sqlCommand);
		$error=$conn->error();		
		if($error) alert($error);
		else{
                    redirect($this->destinoDelete.$param->buildPars(true),"content");		
		}
	}
        

	/* Nombre del archivo php de la clase */
	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1);
	}
           
} /* Fin de la clase */


class clsPersonaContrato_SQLlista extends selectSQL {
	function __construct(){
		$this->sql = "SELECT a.*,
                                    CASE WHEN a.peco_contractual=1 THEN 'CONTRATO'
                                         WHEN a.peco_contractual=2 THEN 'ADENDA'
                                         ELSE ''
                                    END AS tipo_contractual,
                                    LPAD(a.peco_id::TEXT,5,'0') AS pecoid,
                                    CASE WHEN a.peco_numero>0 THEN LPAD(a.peco_numero::TEXT,4,'0')||'-'||a.peco_periodo::TEXT||'-'||a.peco_siglas 
                                         ELSE NULL
                                    END AS numero_contrato,
                                    CASE WHEN a.peco_movimiento=1 THEN 'INGRESO' 
                                         WHEN a.peco_movimiento=2 THEN 'TERMINO' 
                                         WHEN a.peco_movimiento=3 THEN 'PERMANENCIA' 
                                         ELSE 'OTRO' 
                                    END AS movimiento,
                                    b.plco_titulo AS plantilla,
                                    b.tabl_tipdoc,
                                    c.tabl_descripcion AS tipo_documento,
                                    c.tabl_descripaux AS tipo_documento_breve,
                                    d.depe_nombrecorto AS dependencia,
                                   i.pers_apellpaterno||' '||i.pers_apellmaterno||' '||i.pers_nombres AS empleado,
                                   i.pers_dni AS dni,
                                   i.pers_direccion,
                                   i.rela_id,
                                   j.cacl_descripcion AS cargo_clasificado,
                                   k.rela_descripcion AS regimen_laboral,
                                   l.tabl_descripaux AS sit_laboral,
                                   m.care_descripcion AS categoria_remunerativa,
                                   COALESCE(n.cacl_descripcion,a.peco_cargofuncional) AS cargo,
                                   CASE WHEN a.peco_estado=1 THEN 'ACTIVO'
                                        ELSE 'ANULADO'
                                   END AS estado,
                                   x.usua_login	AS username,
                                   y.usua_login	AS usernameactual
			FROM personal.persona_contrato a
                        LEFT JOIN personal.plantilla_contrato b        ON a.plco_id=b.plco_id                        
                        LEFT JOIN catalogos.tabla c                     ON b.tabl_tipdoc=c.tabl_id
                        LEFT JOIN catalogos.dependencia d               ON a.depe_id=d.depe_id
                        LEFT JOIN personal.persona i                    ON a.pers_id=i.pers_id                           
                        LEFT JOIN catalogos.cargo_clasificado j         ON a.cacl_id=j.cacl_id
                        LEFT JOIN catalogos.regimen_laboral k           ON a.rela_id=k.rela_id
                        LEFT JOIN catalogos.tabla l                     ON a.tabl_idsitlaboral=l.tabl_id
                        LEFT JOIN catalogos.categoria_remunerativa  m   ON a.care_id=m.care_id
                        LEFT JOIN catalogos.cargo_clasificado   n       ON a.cacl_id=n.cacl_id
                        LEFT JOIN admin.usuario x                       ON a.usua_id=x.usua_id 
                        LEFT JOIN admin.usuario y                       ON a.peco_actualusua=y.usua_id 
	";
	}

	function wherePadreID($padre_id){
		$this->addWhere("a.pers_id=$padre_id");
	}

	function whereID($id){
		$this->addWhere("a.peco_id=$id");
	}
        
        function whereActivo(){
		$this->addWhere("a.peco_estado=1");
	}
        
        function whereDescrip($descrip){
		$this->addWhere("(a. peco_documento ILIKE '%$descrip%' OR a.peco_siglas ILIKE '%$descrip%')");
	}
        
        function whereAdenda(){
		$this->addWhere("COALESCE(c.tabl_adenda,0)=1");
	}
        
        function whereNOAdenda(){
		$this->addWhere("COALESCE(c.tabl_adenda,0)=0");
	}
        
        function whereEstado($estado){
		$this->addWhere("a.peco_estado=$estado");
	}
        
	function orderUno(){
		$this->addOrder("a.peco_id DESC");
	}
        
        function orderDos(){
		$this->addOrder("a.pers_id,peco_fcontrato,peco_id");
	}

	function getSQL_cbox(){
		$sql="SELECT a.peco_id,a.numero_contrato
				FROM (".$this->getSQL().") AS a 
                        WHERE plco_id IS NOT NULL  /*si tiene plantilla*/
                                AND a.numero_contrato IS NOT NULL
                        ORDER BY 1 DESC";
                                
		return $sql;
	}

        function getSQL_cbox2(){
		$sql="SELECT a.peco_id,tipo_contractual||' '||COALESCE(a.peco_documento,'')||' DEL '||TO_CHAR(a.peco_fcontrato,'DD/MM/YYYY')::TEXT
				FROM (".$this->getSQL().") AS a 
                        WHERE peco_movimiento<>2 /*si no tiene contrato y no es movimiento de Baja*/
                        ORDER BY a.peco_fcontrato DESC";
                                
		return $sql;
	}        
}


class clsPersonaContratoUltimo_SQLlista extends selectSQL {
	function __construct($tabl_idsitlaboral,$tabl_clasificacion){
                $tabl_idsitlaboral=$tabl_idsitlaboral?$tabl_idsitlaboral:0;
                $tabl_clasificacion=$tabl_clasificacion!=99?$tabl_clasificacion:0;
                $tabl_clasificacion=$tabl_clasificacion?$tabl_clasificacion:0;
                
		$this->sql = "SELECT i.pers_dni,
                                     i.pers_apellpaterno||' '||i.pers_apellmaterno||' '||i.pers_nombres AS empleado,
                                     i.tabl_idsitlaboral,
                                     LPAD(a.peco_numero::TEXT,4,'0')||'-'||a.peco_periodo::TEXT||'-'||a.peco_siglas AS numero_contrato,
                                     l.tabl_descripcion AS sit_laboral_larga,
                                     a.peco_finicio,
                                     a.peco_ftermino
			FROM personal.persona_contrato a
                        LEFT JOIN personal.plantilla_contrato b        ON a.plco_id=b.plco_id                        
                        LEFT JOIN catalogos.tabla c                     ON b.tabl_tipdoc=c.tabl_id
                        LEFT JOIN catalogos.dependencia d               ON a.depe_id=d.depe_id
                        LEFT JOIN personal.persona i                    ON a.pers_id=i.pers_id                           
                        LEFT JOIN catalogos.cargo_clasificado j         ON a.cacl_id=j.cacl_id
                        LEFT JOIN catalogos.regimen_laboral k           ON a.rela_id=k.rela_id
                        LEFT JOIN catalogos.tabla l                     ON a.tabl_idsitlaboral=l.tabl_id
                        LEFT JOIN catalogos.categoria_remunerativa  m   ON a.care_id=m.care_id
                        LEFT JOIN catalogos.cargo_clasificado   n       ON a.cacl_id=n.cacl_id
                        WHERE CASE WHEN $tabl_idsitlaboral >0 THEN i.tabl_idsitlaboral=$tabl_idsitlaboral 
                                   ELSE TRUE
                              END
                              AND
                              CASE WHEN $tabl_clasificacion >0 THEN i.tabl_clasificacion=$tabl_clasificacion 
                                   ELSE TRUE
                              END
                              AND peco_id IN (SELECT MAX(peco_id) FROM personal.persona_contrato WHERE peco_contractual=1 AND peco_estado=1 GROUP BY pers_id)
                        ORDER BY i.tabl_idsitlaboral,
                                 a.peco_numero,
                                 i.pers_apellpaterno,
                                 i.pers_apellmaterno,
                                 i.pers_nombres
	";
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

            $dml=new clsPersonaContrato();
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

                    case 3: // Activar/Descativar
                            $dml->activar();
                            break;                        
                        
                    case 4: // Guardar Contrato
                            $dml->guardarContrato();
                            break;
                        
            }
            //	cierra la conexión con la BD
            $conn->close();
    }
}