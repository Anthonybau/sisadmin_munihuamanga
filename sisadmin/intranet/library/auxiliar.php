<?php
/* Janela auxiliar 
   No debe alterarse
*/
include_once($_SERVER['DOCUMENT_ROOT']."/sisadmin/intranet/library/library.php");


$arg = explode("?",getParam("pag"));
$pag = $arg[0];
$parm = str_replace(",","&",$arg[1]);
$post = strpos(getParam("pag"),"height");

if($post>0){
    $height=substr(getParam("pag"),$post+7,2);
}else{
    $height='90';   
}
//alert($height);
?>
<html>
<head>
<title>Ventana Auxiliar</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT ?>">
</head>
<body class="contentBODY" >
<iframe src="<?php echo $pag ?>?<?php echo $parm ?>" width="100%" height="<?php echo $height ?>%" id='content' frameborder="0" name="content" scrolling="none"></iframe>
<iframe src=""  width="100%" height="0" id='controle' frameborder="0" name="controle" scrolling="no"></iframe>
</body>
</html>