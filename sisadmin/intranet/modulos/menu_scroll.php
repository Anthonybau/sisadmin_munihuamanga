<?php
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
include('menu_class.php');
/*
verificación del nível de usuario
*/
$SistId=getSession("sist_id")?getSession("sist_id"):0;
$usersId=getSession("sis_userid");
($usersId>0) or die("Imposible Continuar, no se encontro registro de usuario...");

//$SistId=0;
//$usersId=1;
/* establecer conexión con la BD */
$conn = new db();
$conn->open();
?>

<title>Menu Scroll</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=7" />

<style type="text/css">
@import url("<?php echo PATH_INC?>jquey-scrollpane/hp.css");

.Estilo17 {
        font-family: Verdana, Arial, Helvetica, sans-serif;
       	font-size: 0.8em;
	font-weight: bold;
	text-transform: uppercase;
	margin: 0; padding: 1px;
	background: #EFF4F8;
	border-top: 1px solid #fff;
        color: #000;
        position:absolute;
        z-index:1000;
}
</style>



		<link rel="stylesheet" type="text/css" media="all" href="<?php echo PATH_INC?>jquey-scrollpane/demoStyles.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo PATH_INC?>jquey-scrollpane/jScrollPane.css" />
		<script type="text/javascript" src="<?php echo PATH_INC?>jquey-scrollpane/jquery-1.2.6.min.js"></script>
		<script type="text/javascript" src="<?php echo PATH_INC?>jquey-scrollpane/jScrollPane.js"></script>

		<script type="text/javascript">

			$(function()
			{
				// this initialises the demo scollpanes on the page.
				$('#pane1').jScrollPane();
			});


                        function carga_menu(sist_id,sist_breve){
                            
                           var frames = top.frames;
                            var i
                            for (i = 0; i < frames.length; i++) {
                                if(frames[i].name=='menu_left'){
                                    top.frames[i].location="menu.php?sist_id="+sist_id+"&sist_breve="+sist_breve
                                    break;
                                }
                              //frames[i].location="menu.php?sist_id="+sist_id+"&sist_breve="+sist_breve;
                            }                            
                            //top.middleLayout.open('west')
                        }

		</script>
</head>

<body >
    <div id="wrapper">

                        <div id="wideinfopane" class="parent chrome1 single1 wideinfo">
                            <div class="child c1 first">
                                <div class="wideslide layout1">
		<div class="holder">
			<div id="pane1" class="scroll-pane">



                                    <div class="halfslide">

                                       <?php
                                       
                                       $modulo=new modulos_SQLlista();
                                       $modulo->whereActivo();
                                       $modulo->whereUser($usersId);

                                       $sql="SELECT a.* FROM (".$modulo->getSQL().") AS a  ORDER BY a.sist_id='$SistId' DESC,1";

                                       $rs = new query($conn, $sql);

                                       echo "<ul class=\"thumbimg\">";
                                       while ($rs->getrow()){
                                            $id=$rs->field("sist_id");
                                            $sist_image_on=$rs->field("sist_image_on");
                                            $sist_image_off=$rs->field("sist_image_off")	;
                                            $sist_breve=trim($rs->field("sist_breve"));
                                            $sist_descripcion=$rs->field("sist_descripcion");
                                               echo "<li>";
                                               echo "<a href=\"javascript:carga_menu('$id','$sist_breve')\">";
                                               echo "<img src=\"../img/$sist_image_off\" width=\"165\" height=\"50\" alt=\"$sist_descripcion\" />";
                                               echo "</a>";
                                               echo "</li>";

                                          }
                                        echo  "</ul>";
                                        ?>
                                   
                                    </div>
			</div>
		</div>
                        </div></div>
                                </div>
                            </div>


    
</body>
</html>
<?php
$conn->close();
?>