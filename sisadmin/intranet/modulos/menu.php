<?php
/*
	Esta página presenta el menu del sistema en base a la configuraci�n del
	archivo menu.inc.php, e pode ser substituido por qualquer outro mecanismo
	de menu. No modifique, no toque nada.
*/
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
include('menu_class.php');

$conn = new db();
$conn->open();

//defino mi variable de sesion con el id del sistema
$sistemaId = getParam("sist_id");
$sistBreve = getParam("sist_breve");

if ($sistemaId) {
    setSession("sist_id",$sistemaId);
    setSession("sist_breve",$sistBreve);
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>Menu</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_MENU?>">
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">

        <script type="text/javascript" src="../library/layout/jquery.js"></script>
	<script language="JavaScript" src="../library/js/libjsgen.js"></script>
	<script language="JavaScript">
		if (document.getElementById){
			document.write('<style type="text/css">\n')
			document.write('.submenu{display: none;}\n')
			document.write('</style>\n')
		}

		function SwitchMenu(obj){
			if(document.getElementById){
				var el = document.getElementById(obj);
				var ar = document.getElementById("masterdiv").getElementsByTagName("span");
				if(el.style.display != "block"){
					for (var i=0; i<ar.length; i++){
						if (ar[i].className=="submenu")
							ar[i].style.display = "none";
					}
					el.style.display = "block";
				}else{
					el.style.display = "none";
				}
			}
		}
		function goto(sist_id){
			top.frames[3].location='menu.php?sist_id='+sist_id
		}
                
                function max_min(){
                    div=document.getElementById('menuScroll');
                    if(div.style.height=='60px') {
                        div.style.height='500px'
                        div.innerHTML="<iframe src=\"menu_scroll.php\" align=left name=header id=header frameborder=0 marginwidth=0 marginheight=0 width=100% height=100% scrolling=no></iframe>";
                    }
                    else {
                        div.style.height='60px'
                    }
                }

	</script>
        <style type="text/css">
        .Estilo17 {
                font-family: Verdana, Arial, Helvetica, sans-serif;
                font-size: 0.6em;
                font-weight: bold;
                text-transform: uppercase;
                text-align:center;
                margin: 0; padding: 1px;
                background: #EFF4F8;
                border-top: 1px solid #fff;
                color: #000;
                position:relative;
                width:100%;
                z-index:1000;
        }
        </style>

	<?php verif_framework(); ?>
</head>
<?php
function isValidRow($l) {
	$retorno = true;
	if (substr($l,0,1)=="#") $retorno = false;
	if (strlen(trim($l))==0) $retorno = false;
	return $retorno;
}


$SistId=getSession("sist_id")?getSession("sist_id"):0;
$usersId=getSession("sis_userid");
$sistBreve=getSession("sist_breve")?getSession("sist_breve"):'';

$opcion=new opcionModulo_SQLlista();
$opcion->whereActivo();
$opcion->whereNOTAcceso("'8'");
if($SistId=='01MIPERFIL'){
    $opcion->whereUser(1,$SistId);
}else{
    $opcion->whereUser($usersId,$SistId);
}
$opcion->orderUno();
$sql=$opcion->getSQL();
//echo $sql;

$rs = new query($conn, $sql);

//OPCION INICIAL DEL MENU
$id_modulo[] = 'mod_00';
$titulo_modulo[] = 'Inicio';
if($usersId)
	$url_modulo[] = '../modulos/modulos.php';
else
	$url_modulo[] = '../modulos/login.php';

$target_modulo[] = 'content';
$level_modulo[] = 0 ;
$idModulo='';

$item=null;
while ($rs->getrow()) {
	// agrupando los módulos
	if(strcmp($idModulo,$rs->field("simo_id"))){
		$id_modulo[] = $rs->field("simo_id");
		$titulo_modulo[] = $rs->field("modulo");
		$url_modulo[] = '';
		$target_modulo[] = 'content';
		$level_modulo[] = 0 ;
		$idModulo= $rs->field("simo_id");
		}
	// agrupando los items
		$id_modulo_item[] = $rs->field("simo_id");
		$item[] = $rs->field("smop_descripcion");
		if(stripos($rs->field("simo_id"),'ADPORTAL')) /* Para el mVdulo del portal es necesario enviar como parámetro el id de la opción del menú */
			$url[] = trim($rs->field("smop_page")).'&smop_id='.$rs->field("smop_id");
		else
			$url[] = $rs->field("smop_page");
		$target[] = 'content';
		$level[] = 0 ;

}

?>
<body class="menuBODY">
<div id='procesando' align="right" style="position:absolute;width:90%"></div>

<div class="Estilo17"><a href="#" class="link" onclick="javascript:max_min()">MENU DE OPCIONES</a></div>

<div id="menuScroll" style="position:static;width:100%;margin-top:2px;height:60px">
 <iframe src="menu_scroll.php" align="left" name="header" id="header" frameborder=0 marginwidth=0 marginheight=0 width=100% height=100% scrolling="no">
 </iframe>
</div>



<div id="masterdiv" class="menuTABLE" >
	<?php
	for ($i=0; $i<sizeof($id_modulo); $i++) {
		//verifico tambien el nivel del modulo
		if (isValidUser($level_modulo[$i])){
		$d=0;
		for ($x=0; $x<sizeof($item); $x++) {
			if ($id_modulo_item[$x]==$id_modulo[$i]) {
				if (isValidUser($level[$x])) $d++;
			}
		}
		$abreMenu = ($d>0)?"SwitchMenu('sub$i')":"";
	?>
	<div class="modulo" onClick="<?=$abreMenu?>">
	<?php
	if (strlen($url_modulo[$i])>0) {
		echo "<a class='menu' href='" . $url_modulo[$i] . "' target='" . $target_modulo[$i] . "'>" . $titulo_modulo[$i] . "</a>";
	} else {
		echo $titulo_modulo[$i];
	}
	?>
	</div>
	<span class="submenu" id="sub<?=$i?>">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<?php
			$c=0;

			for ($x=0; $x<sizeof($item); $x++) {
				if ($id_modulo_item[$x]==$id_modulo[$i]) {
					if (isValidUser($level[$x])) {
			?>
			<tr><td valign="top">&nbsp;&nbsp;</td><td class="menuTD"><a class="menu" href="<?=$url[$x]?>" onClick="wait('ajax-loader.gif')" target="<?=$target[$x]?>"><?=$item[$x]?></a></td></tr>
			<?php
						$c++;
					}
				}
			}
			}
			?>
		</table>
		<br>
	</span>
	<?php
	}
	?>
</div>
<?php
$conn->close();
?>
</body>
<?php
//PARA USUARIO DEL TIPO ESCALAFONARIO

/*
if (getSession("sis_usertipo")==3) {
	echo "<script>
		SwitchMenu('sub1');
		 </script>";

	redirect("sescalaFichaEscalafon_foto.php","menubottom");
}
*/
if ($SistId) {
	echo "<script>
		SwitchMenu('sub1');

                function unSelect() {
                $('body').enableSelection();
                    document.body.focus();
                }
            </script>";
}


?>
</html>