<?
require_once("../../library/library.php");
require_once("../admin/adminUsuario_class.php");

$conn = new db();
$conn->open();

$userid=getSession("sis_userid");

if(isset($userid)){
    $otable = new  Table("","200px",1);
    $otable->setJsTitle("onClick=\"javascript:max_min();\"");
    $otable->setTableAlign("R");
    
    $usersONline=new clsUsers_SQLlista();
    $usersONline->whereActivo();
    $usersONline->orderTres();
    $sql=$usersONline->getSQL();
    $tot_online=0;
    $rs = new query($conn, $sql);
    if ($rs->numrows()>0) {

        while ($rs->getrow()) {
            $link="<a class='menu' href=\"javascript:void(0)\" onclick=\"javascript:chatWith('".$rs->field("usua_login")."')\">". $rs->field("usua_login").' '.trim(substr(strtolower($rs->field("empleado")),0,15)).'...'."</a>";
            $otable->addData("<img src=".iif($rs->field("ord_online"),'==',1,'../../img/activo.jpg','../../img/inactivo.jpg')." width=6 height=7 border='0'>".' '.$link);
            $otable->addRow();
            if($rs->field("ord_online")==1) $tot_online++;
        }
    }
    $otable->setTitle("USUARIOS EN LINEA ($tot_online)");
    $contenido_respuesta=$otable->writeHTML();
    echo $contenido_respuesta;
}else{
    echo "";
}
$conn->close();