<?php
require_once("../../library/clases/entidad.php");

class clsServicioCuentasContables extends entidad {

	function __construct($id='',$title=''){
		$this->setTable='catalogos.servicio_asientos_contables'; //nombre de la tabla
		$this->setKey='seac_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;

		/* Destinos luego de actulizar, agregar o eliminar un registro */
		$this->destinoInsert='catalogosServiciosCuentasContables_lista.php';
		$this->destinoUpdate='catalogosServiciosCuentasContables_lista.php';
		$this->destinoDelete='catalogosServiciosCuentasContables_lista.php';

		$this->arrayNameVar[0]='nomeCampoForm';
		$this->arrayNameVar[1]='busEmpty';
		$this->arrayNameVar[2]='cadena';
		$this->arrayNameVar[3]='pg';
		$this->arrayNameVar[4]='colSearch';
		$this->arrayNameVar[5]='numForm';

	}

        function getSql(){
		$sql=new clsServicioCuentasContables_SQLlista();
		$sql->whereID($this->id);
		return($sql->getSQL());
	}

        function addField(&$sql){
		$sql->addField("seac_actualfecha", 'NOW()', "String");
		$sql->addField("seac_actualusua", getSession("sis_userid"), "String");                
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
		$sqlCommand="UPDATE $this->setTable SET seac_estado=CASE WHEN seac_estado=1 THEN 9 ELSE 1 END ";
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


class clsServicioCuentasContables_SQLlista extends selectSQL {
	function __construct(){
		$this->sql = "SELECT a.*,
                                   LPAD(a.serv_codigo::TEXT,5,'0') AS codigo,
                                   LPAD(a.serv_codigo::TEXT,5,'0') AS cod_concepto,
                                   b.serv_descripcion as concepto,
                                   b.segr_id,
                                   c.plco_codigo AS plco_codigo_debex,
                                   c.plco_descrilarga AS descrilarga_debe,
                                   c.plco_describreve AS describreve_debe,
                                   d.plco_codigo AS plco_codigo_haberx,
                                   d.plco_descrilarga AS descrilarga_haber,
                                   d.plco_describreve AS describreve_haber,          
                                   e.tabl_descripcion AS fase,
                                   f.tabl_descripcion AS tipo,
                                   h.tabl_descripcion AS modalidad_pago_recaudacion,
                                   g.afp_nombre AS afp,
                                   p.tipl_descripcion AS planilla,
                                   x.usua_login	AS username,
                                   y.usua_login	AS usernameactual
			FROM catalogos.servicio_asientos_contables a
			LEFT JOIN catalogos.servicio b              ON a.serv_codigo=b.serv_codigo
                        LEFT JOIN siscont.plan_contable c            ON a.plco_id_debe=c.plco_id
                        LEFT JOIN siscont.plan_contable d            ON a.plco_id_haber=d.plco_id
                        LEFT JOIN planillas.tipo_planilla p          ON a.tipl_id=p.tipl_id
                        LEFT JOIN catalogos.afp g                    ON a.afp_id=g.afp_id                        
                        LEFT JOIN catalogos.tabla e                  ON a.tabl_fase=e.tabl_id 
                        LEFT JOIN catalogos.tabla f                  ON a.tabl_tipo=f.tabl_id
                        LEFT JOIN catalogos.tabla h                  ON a.tabl_modpago=h.tabl_codigo AND h.tabl_tipo='MODALIDAD_PAGO_RECAUDACION'
                        LEFT JOIN admin.usuario x                    ON a.usua_id=x.usua_id 
                        LEFT JOIN admin.usuario y                    ON a.seac_actualusua=y.usua_id 
	";
	}

	function wherePadreID($padre_id){
		$this->addWhere("a.serv_codigo=$padre_id");
	}

	function whereID($id){
		$this->addWhere("a.seac_id=$id");
	}
        
	function orderUno(){
		$this->addOrder("a.seac_id DESC");
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

            $dml=new clsServicioCuentasContables();
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
                        
            }
            //	cierra la conexión con la BD
            $conn->close();
    }
}