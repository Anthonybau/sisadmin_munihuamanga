<?
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
include("../catalogos/catalogosDependencias_class.php");
include("../admin/adminUsuario_class.php");

//$depe_id=getParam("depe_id");

$depe_id=getSession("sis_depeid");
$usua_id=getSession("sis_userid");

/* establecer conexión con la BD */
$conn = new db();
$conn->open();

$dependencia=new dependencia_SQLlista();
$dependencia->whereNotUno();
$dependencia->orderUno();
$sql=$dependencia->getSQL();

$rs = new query($conn, $sql);

$out="";
while ($rs->getrow()) {
    if(!$out)   $out.="[";
    else    $out.=",";
    
    if($rs->field("depe_id")==$depe_id){
        $usuarios=new clsUsers_SQLlista();
        $usuarios->whereDepeID($depe_id); //obtiene los usuarios de la dependencia que esta en sesion
        $usuarios->whereActivo();
        //$usuarios->whereNotID($usua_id); //NO obtiene el usuario que esta en sesion
        $usuarios->orderUno();
        
        $sql=$usuarios->getSQL();
        $rsUsers = new query($conn, $sql);
        //if($rsUsers->numrows()>0){
            $out2="";
            while ($rsUsers->getrow()) {
                if($out2)  $out2.=",";
                $out2.='"'.$rs->field("depe_id")."_".$rsUsers->field("usua_id")."_ ".$rs->field("depe_nombre")." [".$rsUsers->field("usua_login").'-'.$rsUsers->field("empleado_breve").']"';
            }
            $out.=$out2;
        //}else{
        //    $out.='"'.$rs->field("depe_id").". ".$rs->field("depe_nombre").'"';
        //}
    }else{
        $out.='"'.$rs->field("depe_id")."_ ".$rs->field("depe_nombre").'"';
    }
    
    
            
}
$out.=']';
echo $out;


$conn->close();