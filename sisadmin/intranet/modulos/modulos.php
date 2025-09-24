<?php
/*
	Esta es la primera página presentada por el sistema
*/
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
include('menu_class.php');
//elimino la variable de control en las clases

verificaUsuario(0);

$conn = new db();
$conn->open();				

$usersId=getSession("sis_userid");
($usersId>0) or die("Imposible Continuar, no se encontro registro de usuario...");

$modulo=new modulos_SQLlista();
$modulo->whereActivo();
$modulo->whereUser($usersId);
$modulo->whereNOTModulo("'01MIPERFIL'");
$modulo->orderUno();
$sql=$modulo->getSQL();
$rs = new query($conn, $sql);

$image_inicio = "../img/inicio_".strtolower(SIS_EMPRESA_SIGLAS).".jpg";    

if(!file_exists($image_inicio)){ 
    $image_inicio = "../img/inicio_".strtolower(SIS_EMPRESA_SIGLAS).".png";
    if(!file($image_inicio)){
        $image_inicio = "../img/logo_".strtolower(SIS_EMPRESA_SIGLAS).".png";
        if(!file($image_inicio)){ 
            $image_inicio = "../img/inicio_demo.jpg";
        }
    }
}

?>
<html>
<head>
<title>Content</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="<?php echo CSS_CONTENT?>">
<script language="JavaScript" src="../library/js/janela.js"></script>
<script language="JavaScript" src="../library/js/libjsgen.js"></script>
<script>

    function abreCambio() {
            // la extensión, o separador "&" debe ser substituido por coma ","
            abreJanelaAuxiliar('../modulos/cambia_Contrasena.php',400,250);
    }

   function AbreVentana(sURL){
        var w=720, h=600;
        venrepo=window.open(sURL,'viewManual', "status=yes,resizable=yes,toolbar=no,scrollbars=yes,top=50,left=150,width=" + w + ",height=" + h, 1 );
        venrepo.focus();
   }

    function modulo_on(e,id,sist_image_on){
        var e=window.event;
        document.getElementById(id).src="../img/"+sist_image_on
    }
    function modulo_off(e,id,sist_image_off){
        var e=window.event;
        document.getElementById(id).src="../img/"+sist_image_off
    }

    function carga_menu(sist_id,sist_breve){
        if(sist_id=='15PEDIDOS'){
            
            for(i=0;i<top.frames.length;i++){
                if(top.frames[i].name=='content'){
                    top.frames[i].location='cementerio/pedidos_lista.php?clear=1'
                    break;
                }
            }
            
        }else{
            top.middleLayout.open('west')            
        }


         //alert(top.frames[3].name);
        //OJO: si colocamos el nombre del iframe, no funciona el location
        for(a=0;a<top.frames.length;a++){
            if(top.frames[a].name=='menu_left'){
                break;
            }
        }

        top.frames[a].location="menu.php?sist_id="+sist_id+"&sist_breve="+sist_breve
    

    }

    function abreJanelaAuxiliar(pagina,nWidth,nHeight){
            eval('janela = window.open("../library/auxiliar.php?pag=' +  pagina +
                '","janela","width='+nWidth+',height='+nHeight+',top=50,left=150' +
                    ',scrollbars=no,hscroll=0,dependent=yes,toolbar=no")');
            janela.focus();
    }

</script>
<?php verif_framework(); ?>	
<style type="text/css">
<!--
.Estilo1 {
        font-family: Trebuchet MS, Verdana, Arial, Helvetica;
	font-size: 13pt;
	font-weight: bold;
	color: #878787;
	text-decoration: none;
}

-->
</style>
</head>

<body class="contentBODY">
<!---->
<table width="600px" height="70%" border="0" align="center">
  <tr>
      <td colspan="3" align="center">
            <?php
                if(strpos($image_inicio, "logo")>0 || strpos($image_inicio, ".png")>0){
                    echo "<img  src=\"$image_inicio\" >";
                }else{
                    echo "
                    <table width=\"70%\" height=\"50%\" class=\"table_content\" align=\"center\">
                        <tr>
                            <td align=\"center\">
                                        <img  src=\"$image_inicio\" width=\"100%\" height=\"200\">
                            </td>    
                        </tr>
                    </table>
                    ";
                }                            
            ?>
                    
    </td>
  </tr>

  <tr>
      <td width="33%" align="left">
    </td>
    <td width="33%" align="center">
    </td>
    <td width="34%" align="right">
    </td>
  </tr>

  <tr>
    <td colspan="3"><div align="center" class="Estilo1" >
	<?php
        if($rs->numrows()>0){
            echo "Seleccione su opci&oacute;n:";
        }
	?>
	</div></td>
  </tr>
  <tr valign="top" align="center">
    <td colspan="3">
        <?php
        if($rs->numrows()>0)
        {
        ?>

        <table width="100%" border="1" cellspacing="20" bordercolor="#878787" bgcolor="#E8EEF7">
            <tr>
            <?php
            $i=1;
            while ($rs->getrow()){
                $id=$rs->field("sist_id");
                if($id!='01MIPERFIL'){
                    if($id!='99ADMIN' || ($id=='99ADMIN' && getSession("sis_level")>2)){
                        $sist_image_on=$rs->field("sist_image_on");
                        $sist_image_off=$rs->field("sist_image_off")	;
                        $sist_breve=trim($rs->field("sist_breve"));

                        echo "<td align=\"center\" onmouseover=\"modulo_on(event,'div_$id','$sist_image_on')\" onmouseout=\"modulo_off(event,'div_$id','$sist_image_off')\">\n";
                        echo "<a class='linkWhole' style='font-size: 10pt' href=\"javascript:carga_menu('$id','$sist_breve')\"><img id='div_$id' src=\"../img/$sist_image_off\" width=220px height=60px border=0></a>";
                        echo "</td>\n";

                        if(($i%3)==0 && $i>0 && $i<$rs->numrows()){
                            echo "</tr><tr>\n";
                        }
                        $i++;
                    }
                        
                }
            }
            ?>
            </tr>
        </table>
        <?php
        }
        ?>
    </td>
  </tr>
  
</table>
<?php 
$conn->close();?>			

<!---->
</body>
<script>
    parent.menu_left.location="menu.php?sist_id=01MIPERFIL&sist_breve=MI PERFIL";    
</script>
</html>
