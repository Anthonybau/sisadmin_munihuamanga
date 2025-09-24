<?php
require_once("../../library/clases/entidad.php");

$control=base64_decode($_GET['control']);
if($control){
    require_once("../../library/library.php");    
    /*	verificacion a nivel de usuario */
    verificaUsuario(1);
    verif_framework();

    $param= new manUrlv1();	
    $param->removePar('control');

    //	conexi�n a la BD 
    $conn = new db();
    $conn->open();

    switch($control){
            case 1: // Guardar
                    guardar();   
                    break;
    }
    //	cierra la conexi�n con la BD
    $conn->close();
}

function guardar(){
        global $conn,$param;

        $destinoUpdate = "sistemas_edicion.php".$param->buildPars(true);

        $sql="UPDATE admin.sistema 
                        SET sist_activo=9 
                        WHERE sist_id!='99ADMIN';
                UPDATE admin.sistema_modulo_opciones 
                        SET smop_activo=9 
                        WHERE smop_id NOT IN (SELECT a.smop_id 
                                                        FROM admin.sistema_modulo_opciones a 
                                                        LEFT JOIN admin.sistema_modulo b            ON a.simo_id=b.simo_id 
                                                        LEFT JOIN admin.sistema c                   ON b.sist_id=c.sist_id
                                                        WHERE c.sist_id='99ADMIN');
                UPDATE admin.aplicativo 
                            SET apli_gestdoc=0,
                                apli_gestleg=0,
                                apli_sislogal=0
                        WHERE apli_id=1
                 ";
        $conn->execute($sql);
        
        $sql = new UpdateSQL();
        $sel=$_POST["sel"];
        for($x=0;$x<=count($sel);$x++){
            $nivel=substr($sel[$x],0,1);
            $opcion=substr($sel[$x],2);

            if($nivel==1){
                $sql="UPDATE admin.sistema SET sist_activo=1 WHERE sist_id='$opcion'";
            }else{
                $sql="UPDATE admin.sistema_modulo_opciones SET smop_activo=1 WHERE smop_id='$opcion'";
            }
            $conn->execute($sql);
            $error=$conn->error();
            if($error){
                alert($error);				
            }   
            if($opcion=='03GESTDOC'){
                $sql="UPDATE admin.aplicativo 
                            SET apli_gestdoc=1 
                        WHERE apli_id=1";
                $conn->execute($sql);  
            }
            if($opcion=='04GESTLEG'){
                $sql="UPDATE admin.aplicativo 
                            SET apli_gestleg=1 
                        WHERE apli_id=1";
                $conn->execute($sql);  
            }            
            if($opcion=='17SIGLO'){
                $sql="UPDATE admin.aplicativo 
                            SET apli_sislogal=1 
                        WHERE apli_id=1";
                $conn->execute($sql);  
            }            
        }

        $destino=$destinoUpdate;
        redirect($destino,"content");							

}

class clsSistemas
{
    private $existe;
    private $title;
    private $field;

    function clsSueldoMinimo($id='',$title=''){
        $this->id=$id;
        $this->title=$title;	
    }

    function getSql(){
        $sql = new clsSistemas_SQLlista();
        $sql->whereNOTAdmin();
        $sql->whereID($this->id);
        $sql = $sql->getSQL();
        return($sql);
    }

    function setDatos(){
        global $conn;						

        $sql = $this->getSql();
        $rs = new query($conn, $sql);

        if ($this->field=$rs->getrow()){
                $this->existe=1;
        }
        else $this->existe=0;
    }

    function field($nameField){
        return $this->field["$nameField"];
    }

    function existeDatos(){
        return($this->existe);
    }


    function getTitle(){
        return $this->title;
    }

    function getNameFile()
    {
        return substr(__FILE__,intval(strrpos(str_replace("/","\\",__FILE__),"\\"))+1); 
    }	        
}

class clsSistemas_SQLlista extends selectSQL {
    function __construct(){
        $this->sql = "SELECT a.sist_id,
                             a.sist_descripcion,
                             a.sist_activo,
                             b.simo_descripcion AS modulo,
                             c.smop_id,
                             c.smop_descripcion,
                             c.smop_page,
                             c.smop_activo
                             FROM  admin.sistema a
                             LEFT JOIN admin.sistema_modulo           b ON a.sist_id=b.sist_id
                             LEFT JOIN admin.sistema_modulo_opciones  c ON b.simo_id=c.simo_id    
                        ";
    }

    function whereID($id){
        $this->addWhere("a.sist_id=$id");
    }

    function whereNOTAdmin(){
        $this->addWhere("a.sist_id!='99ADMIN'");
    }
    
    function whereNOTMiPerfil(){
        $this->addWhere("a.sist_id!='01MIPERFIL'");
    }
    
    function whereConTutorial(){
        $this->addWhere("a.sist_id IN (SELECT DISTINCT sist_id FROM admin.tutoriales)");
    }
    
    function whereActivo(){
        $this->addWhere("a.sist_activo=1");	
    }
    
    function orderUno(){
        $this->addOrder("a.sist_id,c.smop_id");		
    }
        
    function getSQL_cbox(){
		$sql="SELECT DISTINCT sist_id AS id,
                                sist_descripcion AS descripcion
				FROM (".$this->getSQL().") AS a 
                             ORDER BY 2";
		return $sql;
	}    
}

class clsTutoriales_SQLlista extends selectSQL {
    function __construct(){
        $this->sql = "SELECT a.tuto_id,
                             a.tuto_titulo,
                             a.tuto_descripcion,
                             TRIM(a.tuto_video_id) AS tuto_video_id
                             FROM  admin.tutoriales a
                        ";
    }

    function whereID($id){
        $this->addWhere("a.tuto_id=$id");
    }

    function wherePadreID($sist_id){
        $this->addWhere("a.sist_id='$sist_id'");
    }
    
    function whereVideoID($video_id){
        $search=sprintf("%s",$video_id);
        $array=explode(",",$search);
        $lista='';
        for($i=0; $i<count($array); $i++){
            $lista.=$lista!=''?" OR ":"";
            $lista.="TRIM(a.tuto_video_id)=TRIM('".$array[$i]."')";
        }
        $this->addWhere("(".$lista.")");                
    }    
    
    function whereDescripcion($cadena){
        $search=sprintf("%s",$cadena);
        $array=explode(" ",$search);
        $lista='';
        for($i=0; $i<count($array); $i++){
            $lista.=$lista!=''?" AND ":"";
            $lista.="(a.tuto_descripcion ILIKE '%".$array[$i]."%' OR a.tuto_titulo ILIKE '%".$array[$i]."%')";
        }
        $this->addWhere($lista);
    }   
    
    function orderUno(){
        $this->addOrder("a.tuto_id");		
    }
}