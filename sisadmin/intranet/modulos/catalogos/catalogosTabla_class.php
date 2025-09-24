<?php
require_once("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/clases/entidad.php");

class clsTabla extends entidad {
	
	function __construct($id='',$title=''){
		$this->setTable='tabla'; //nombre de la tabla
		$this->setKey='tabl_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	

		$this->pagEdicion=$this->getNamePage('edicion');
		$this->pagBuscar=$this->getNamePage('buscar')	;	
		
		/* Destinos luego de actulizar, agregar o eliminar un registro */
		$this->destinoUpdate = $this->pagBuscar;
		$this->destinoInsert = $this->pagBuscar;
		$this->destinoDelete = $this->pagBuscar;

		$this->arrayNameVar[0]='nomeCampoForm';
		$this->arrayNameVar[1]='busEmpty';
		$this->arrayNameVar[2]='cadena';
		$this->arrayNameVar[3]='pg';
		$this->arrayNameVar[4]='colSearch';
		$this->arrayNameVar[5]='numForm';
		
	}

	function getNamePage($accion)
	{
		return(str_replace('class',$accion,$this->getNameFile()));
	}	

} /* Fin de la clase */


class clsTabla_SQLlista extends selectSQL {

	function __construct(){
		$this->sql="SELECT  a.*,
					LPAD(a.tabl_id::TEXT,5,'0') AS lpad_id,
					x.usua_login
					FROM catalogos.tabla a
					LEFT JOIN admin.usuario x ON a.usua_id=x.usua_id
					";
	}

	function whereID($id){
		$this->addWhere("a.tabl_id=$id");	
	}

        
	function whereDescrip($descrip){
		if($descrip) $this->addWhere("(a.tabl_descripcion ILIKE '%$descrip%')");
	}

	function whereTipo($tipo){
		if($tipo) {
                    $this->addWhere("a.tabl_tipo='$tipo'");
                }
	}

        function whereNoPorcent(){
            $this->addWhere("a.tabl_porcent IS NULL");
        }
        
        function whereNoCodigo($codigo){
		if($codigo) $this->addWhere("a.tabl_codigo NOT IN ($codigo)");
	}

        function whereNoID($id){
		if($id) $this->addWhere("a.tabl_id NOT IN ($id)");
	}
        
        function whereCodigo($codigo){
		if($codigo) $this->addWhere("a.tabl_codigo IN ($codigo)");
	}
        
        function whereCodigoAux($codigo_aux){
		if($codigo_aux) $this->addWhere("a.tabl_codigoauxiliar=$codigo_aux");
	}
        
        function whereActivo(){
		$this->addWhere("a.tabl_activo=1");
	}
        
        function whereRelaID($rela_id){
		 $this->addWhere("a.rela_id=$rela_id");
	}
        
	function orderUno(){
		$this->addOrder("a.tabl_id");		
	}

	function orderDos(){
		$this->addOrder("a.tabl_codigo");		
	}
	
        function orderTres(){
		$this->addOrder("a.tabl_descripcion");		
	}
        
	function getSQL_cbox(){
		$sql="SELECT tabl_id,
                             tabl_descripcion
				FROM (".$this->getSQL().") AS a ORDER BY 1";
		return $sql;
	}

	function getSQL_cbox2(){
		$sql="SELECT tabl_id,
                             tabl_descripcion
			FROM (".$this->getSQL().") AS a 
                        ORDER BY 2";
		return $sql;
	}
        
        function getSQL_cbox3(){
		$sql="SELECT tabl_id::TEXT||'_1' AS id,tabl_descripcion||' (INC)' AS descripcion
			FROM (".$this->getSQL().") AS a 
                      UNION ALL
                      SELECT tabl_id::TEXT||'_2' AS id,tabl_descripcion||' (NO INC)' AS descripcion
			FROM (".$this->getSQL().") AS a 
                      ORDER BY 1";
		return $sql;
	}

	function getSQL_cbox4(){
		$sql="SELECT tabl_id,SUBSTR(tabl_descripcion,1,3) AS descripcion
				FROM (".$this->getSQL().") AS a
                       ORDER BY 1";
		return $sql;
	}
        
	function getSQL_cboxCodigo(){
		$sql="SELECT tabl_codigo,
                             tabl_descripcion
				FROM (".$this->getSQL().") AS a
                      ORDER BY 1";
		return $sql;
	}
	//meodo que devuelve el sql de la áreas de empresas
	function getSQL_cboxDependencia(){
		$this->whereTipo('DEPENDENCIAS_ATENCION');
		$this->orderUno();
		$sql=$this->getSQL_cbox();
		return ($sql);		
	}

	//meodo que devuelve el sql de las modalidades de pago de los C/P
	function getSQL_cboxTema(){
		$this->whereTipo('TEMAS_CONSULTAS');
		$this->orderUno();
		$sql=$this->getSQL_cbox();
		return ($sql);
	}

}