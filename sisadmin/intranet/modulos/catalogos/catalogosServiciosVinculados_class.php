<?php
require_once('../../library/clases/entidad.php');
require_once('../../library/clases/selectSQL.php');

class servicioVinculados extends entidad {
	
	function __construct($id='',$title=''){
		$this->setTable='servicio_vinculados'; //nombre de la tabla
		$this->setKey='sevi_id'; //campo clave
		$this->valueKey=getParam("f_id"); //valor del campo clave
		$this->typeKey="Number"; //tipo  de dato del campo clave
		$this->id=$id;
		$this->title=$title;	
		$this->pagEdicion=$this->getNamePage('edicion');
		
		/* Ancho y Alto de Thickbox */
		$this->is_thinckbox=true;		
		$this->winWidth=650;  	/* Ancho de la ventana Thickbox cuando se cargue la claes en un Avanzlookup */
		$this->winHeight=400;  	/* Alto de la ventana Thickbox cuando se cargue la claes en un Avanzlookup */
		
		/* Destinos luego de actulizar, agregar o eliminar un registro */
		$this->destinoInsert=$this->getNamePage('lista');
		$this->destinoUpdate=$this->getNamePage('lista');
		$this->destinoDelete=$this->getNamePage('lista');

		$this->arrayNameVar[0]='nomeCampoForm';
		$this->arrayNameVar[1]='busEmpty';
		$this->arrayNameVar[2]='cadena2';
		$this->arrayNameVar[3]='pg2';
		$this->arrayNameVar[4]='colSearch';
		$this->arrayNameVar[5]='numForm';
	}

	function buscar($op,$cadena,$Nameobj='')
	{
		global $conn;
		$objResponse = new xajaxResponse();

			/* Creo my objeto Table */
			$otable = new TableSimple('',"100%",6,'tLista'); // T�tulo, ancho, Cantidad de columas,id de la tabla						

			
			/* Consulta sql a mostrar */
			$sql=new servicioVinculados_SQLlista();
                        $sql->wherePadreID($cadena);
			$sql->orderUno();
			$sql=$sql->getSQL();
			//$objResponse->addAlert($sql);
			//echo $sql; 
	
			$rs = new query($conn, strtoupper($sql));

			if ($rs->numrows()>0) {
					$otable->addColumnHeader("<input type=\"checkbox\" id=\"checkall\" >"); // Coluna com checkbox
			
					/* Agrego las cabeceras de mi tabla */
					$otable->addColumnHeader("Cod","5%", "L"); 
                                        $otable->addColumnHeader("Grupo","20%", "L"); 
                                        $otable->addColumnHeader("Sub.Grupo","20%", "L"); 
                                        $otable->addColumnHeader("Descripci&oacute;n","50%","L"); 
                                        $otable->addColumnHeader("Precio",true,"5%", "L"); 
                                        $otable->addColumnHeader("Dest.Ocup",true,"5%", "L");
                                        $otable->addColumnHeader("Usuario",true,"5%", "L");                                         
					$otable->addRowHead(); 					
					while ($rs->getrow()) {
						$id = $rs->field("sevi_id");// captura la clave primaria del recordsource
                                                $destino_ocupante = $rs->field("tabl_destino_ocupante");
                                                $usua_id = $rs->field("usua_id");
                                                
                                                $otable->addData("<input type=\"checkbox\" name=\"sel[]\" value=\"$id\" >");
                                                $otable->addData($rs->field("serv_vinculado_codigo"));
                                                $otable->addData($rs->field("serv_vinculado_grupo"));
                                                $otable->addData($rs->field("serv_vinculado_sgrupo"));
						$otable->addData($rs->field("serv_vinculado_descripcion"));
                                                $otable->addData(number_format($rs->field("serv_vinculado_precio"), 2, '.', ','),"R");
                                                
                                                if ($usua_id==getSession("sis_userid") && inlist($rs->field("segr_id"),'28,30') ){//INHUMACIONES,EXHUMACIONES 
                                                        $sqltipoDestino="SELECT  tabl_codigo AS id,
                                                                                tabl_descripcion AS descripcion 
                                                                            FROM catalogos.tabla
                                                                            WHERE tabl_tipo='DESTINO_OCUPANTE'
                                                                            UNION ALL
                                                                            SELECT  0 AS id,
                                                                                    'TODOS' AS descripcion 
                                                                            FROM catalogos.tabla
                                                                            WHERE tabl_tipo='DESTINO_OCUPANTE'
                                                                                    AND tabl_codigo=1
                                                                            ORDER BY 1";
                                                
                                                        $otable->addData(listboxField("Destino Ocupante",$sqltipoDestino,"tr_tabl_tipodestino_$id","$destino_ocupante","--Elija Destino--","onChange=\"xajax_updateDestino(this.value,'$id')\""));
                                                }else{
                                                    $otable->addData("$destino_ocupante","C","","0->todos");
                                                }
                                                
                                                $otable->addData($rs->field("usua_login"));
                                                $otable->addRow();
					}
				$contenido_respuesta.=$otable->writeHTML();
				$contenido_respuesta.="<div class='Bordeatabla' style='width:100%;float:right' align='right'>Total de Registros: ".$rs->numrows()."</div>";				
			} else {
				$otable->addColumnHeader("NO SE ENCONTRARON DATOS...!!","100%", "C"); // T�tulo, Ordenar?, ancho, alineaci�n
				$otable->addRowHead(); 	
				$otable->addRow();	
				$contenido_respuesta=$otable->writeHTML();
			}
	
		//se analiza el tipo de funcionamiento
		if($op==1){//si es llamado para su funcionamiento en ajax con retornoa a un div
			$objResponse->addAssign($Nameobj,'innerHTML', $contenido_respuesta);
			$objResponse->addscript("activaSorter()"); // Para activar el orden en la tabla 
			$objResponse->addscript("func_jquerytablas()"); // Para activar las funciones de css de la tabla
			return $objResponse;
		}
		else
                    return $contenido_respuesta;
	}

        
	/* Nombre del archivo php de la clase */
	function getNameFile()
	{
		return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
	}

	function getNamePage($accion)
	{
		return(str_replace('class',$accion,$this->getNameFile()));
	}	
		
	
} /* Fin de la clase */

class servicioVinculados_SQLlista extends selectSQL {
	function __construct(){
		$this->sql= "SELECT a.sevi_id,
                                    LPAD(a.serv_codigo::TEXT,4,'0') AS serv_codigo,
                                    a.tabl_tipoprecio,
                                    a.tabl_destino_ocupante,
                                    a.usua_id,
                                    b.serv_descripcion,
                                    b.segr_id,
                                    LPAD(c.serv_codigo::TEXT,4,'0') AS serv_vinculado_codigo,
                                    c.segr_id AS segr_vinculado,
                                    c.sesg_id AS sesg_vinculado,
                                    LPAD(c.segr_id::TEXT,4,'0')||'_'||LPAD(c.sesg_id::TEXT,4,'0') AS grupo_subgrupo_vinculado,
                                    c.serv_descripcion as serv_vinculado_descripcion,
                                    f.sepr_precio as serv_vinculado_precio,
                                    d.segr_descripcion AS serv_vinculado_grupo, 
                                    d.segr_vinculo AS vinculado_vinculo,
                                    e.sesg_descripcion as serv_vinculado_sgrupo,
                                    x.usua_login
                                    FROM catalogos.servicio_vinculados a 
                                    LEFT JOIN catalogos.servicio            b ON a.serv_codigo=b.serv_codigo 
                                    LEFT JOIN catalogos.servicio            c ON a.serv_codigo_vinculado=c.serv_codigo
                                    LEFT JOIN catalogos.servicio_grupo      d on c.segr_id=d.segr_id				
                                    LEFT JOIN catalogos.servicio_sgrupo     e on c.sesg_id=e.sesg_id    
                                    LEFT JOIN catalogos.servicio_precios    f on a.serv_codigo_vinculado=f.serv_codigo AND a.tabl_tipoprecio=f.tabl_tipoprecio
                                    LEFT JOIN admin.usuario                 x ON a.usua_id=x.usua_id 					
                            ";
					
	}

	function whereID($id){
            $this->addWhere("a.sevi_id=$id");	
	}
        
	function wherePadreID($padre_id){
            $this->addWhere("a.serv_codigo=$padre_id");	
	}
        
        function whereSubGrupo($grupo_id){
            $this->addWhere("c.sesg_id=$grupo_id");	
	}
        
        function whereLista($lista){
            if($lista) {$this->addWhere("a.serv_codigo IN ('$lista')");}	
	}
        
        function whereNOTlista($lista){
            if($lista) {$this->addWhere("a.serv_codigo_vinculado NOT IN ('$lista')");}	
	}
        

        function whereDestinoOcupanteTodos(){
            $this->addWhere("a.tabl_destino_ocupante=0");	
	}
        
	function orderUno(){
            $this->addOrder("a.sevi_id DESC");
	}
        
        function orderDos(){
            $this->addOrder("c.sesg_id=24 DESC"); 
        }
        
        function getContador(){
                $sql="SELECT COUNT(a.serv_codigo) 
                        FROM (".$this->getSQL().") AS a";
                
                return($sql);
        }        
}



/* Llamando a la subclase */
if (isset($_GET["control"])){
    $control=base64_decode($_GET['control']);
    if($control){
        include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");        

	/*	verificaci�n a nivel de usuario */
	verificaUsuario(1);
	verif_framework();

	$param= new manUrlv1();	
	$param->removePar('control');

	//	conexi�n a la BD 
	$conn = new db();
	$conn->open();

	$dml=new servicioVinculados();

	switch($control){
		case 2: // Eliminar
			$dml->eliminar();
			break;		
                   
	}
	//	cierra la conexi�n con la BD
	$conn->close();
    }
}
