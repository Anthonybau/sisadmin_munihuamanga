<?
/*
	Esta página presenta el menu del sistema en base a la configuraci�n del
	archivo menu.inc.php, e pode ser substituido por qualquer outro mecanismo
	de menu. No modifique, no toque nada.
*/
include("../../library/library.php");

$conn = new db();
$conn->open();
$userid=getSession("sis_userid");
if(!$userid) exit(0);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>Menu</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?=CSS_MENU?>">
        <link rel="stylesheet" type="text/css" href="<?=CSS_CONTENT?>">
        <link type="text/css" rel="stylesheet" media="all" href="../chat/css/chat.css" />

        <script type="text/javascript" src="../chat/js/jquery.js"></script>
        <script type="text/javascript" src="../chat/js/chat.js"></script>        
	<script language="JavaScript">
                function max_min(){
                    div=parent.document.getElementById('chat_online');
                    if(div.style.top=='46%') {
                        div.style.top='95%';
                        div.style.height='30px';
                        div.style.width= '230px';
                        div.style.marginLeft = '82%';
                    }
                    else {
                        div.style.top='46%';
                        div.style.height='353px'; //318
                        div.style.width= '95%';
                        div.style.marginLeft = '5%';
                    }
                }

                estoyUsuariosONLine();
                verUsuariosONLine();
                /* se actualiza cada 5 seg : 3 seconds (3000 milliseconds)*/
                setInterval("estoyUsuariosONLine()",5000)

                setInterval("verUsuariosONLine()",8000)
                                                  
                function verUsuariosONLine(){
                    $.ajax({
                       type: "POST",
                       url: "consultaUsuariosEnLinea.php",
                       data: "",
                       success: function(msg){
                           document.getElementById('usuariosONLine').innerHTML=msg;
                       }
                     });
                }
                
                function estoyUsuariosONLine(){
                    $.ajax({
                       type: "POST",
                       url: "estoyUsuarioEnLinea.php"
                     });
                }

	</script>

</head>

<body>
<div id="usuariosONLine"></div>
</body>
</html>
<?
$conn->close();