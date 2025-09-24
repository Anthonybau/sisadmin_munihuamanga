<?
require_once("../../library/library.php");
require_once("../admin/adminUsuario_class.php");

$conn = new db();
$conn->open();

$userid=getSession("sis_userid");

if(isset($userid)){
    $usersONline=new clsEstoyenLinea($userid);
    $conn->execute($usersONline->getSQL());
    echo 1;
}else{
    echo 0;
}
$conn->close();