<?php
include("$_SERVER[DOCUMENT_ROOT]/sisadmin/intranet/library/library.php");
$userId = getSession("sis_userid");
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<title>.: <?php echo SIS_EMPRESA?> - Intranet :.</title>

	<script type="text/javascript" src="<?php echo PATH_INC?>layout/jquery.js"></script>
	<script type="text/javascript" src="<?php echo PATH_INC?>layout/jquery.ui.all.js"></script>
	<script type="text/javascript" src="<?php echo PATH_INC?>layout/jquery.layout.js"></script>

	<script>
            function loadIframePage (pane, $Pane) {
                    if (!$Pane) $Pane = $('.ui-layout-'+ pane);
                    var $Iframe = $Pane.attr('tagName')=='IFRAME' ? $Pane : $Pane.find('IFRAME:first');
                    if (!$Iframe.length) return; // no iframe
                    var
                            src  = $Iframe.attr('src')
                    ,	page = $Iframe.attr('longdesc')
                    ;
                    if (page && src != page) $Iframe.attr('src',page);
            }

            var outerLayout, middleLayout, innerLayout;

            $(document).ready(function () {

                    outerLayout = $('body').layout({//PARA TODOS LOS PANELES
                            center__paneSelector:	".outer-center"
                    ,	west__paneSelector:	".outer-west"
                    ,	east__paneSelector:	".outer-east"
                    ,	west__size:		125
                    ,	east__size:		125
                    ,	spacing_open:		8// ALL panes
                    ,	spacing_closed:		12 // ALL panes
                    //,	north__spacing_open:	0
                    //,	south__spacing_open:	0
                    ,	north__maxSize:		200
                    ,	south__maxSize:		200
                    ,	center__onresize:	"middleLayout.resizeAll"
                    ,       east__resizeWhileDragging: true	// slow with a page full of iframes!

                    });

                    middleLayout = $('div.outer-center').layout({ //PANEL MENU
                        center__paneSelector:	".middle-center"
                    ,	west__paneSelector:	".middle-west"
                    ,	east__paneSelector:	".middle-east"
                    ,	west__size:		100
                    ,	east__size:		100
                    ,	spacing_open:		8  // ALL panes
                    ,	spacing_closed:		20
                    ,	togglerLength_closed:	100
                    ,	togglerContent_closed:  "M<BR>E<BR>N<BR>U"
                    ,	togglerTip_closed:	"Abrir el Menú y anclar"
                    ,	center__onresize:	"innerLayout.resizeAll"
                    ,	resizable: 		false
                    });

                    innerLayout = $('div.middle-center').layout({ //PANELES ARRIBA Y ABAJO
                            center__paneSelector:	".inner-center"
                    ,	west__paneSelector:	".inner-west"
                    ,	east__paneSelector:	".inner-east"
                    ,	west__size:		75
                    ,	east__size:		75
                    ,	spacing_open:		0  // ALL panes
                    ,	spacing_closed:		0  // ALL panes
                    ,	west__spacing_closed:	0
                    ,	east__spacing_closed:	0
                    });

                    middleLayout.sizePane("west", 210);
                    middleLayout.close("west");
                    innerLayout.sizePane("north", 60);
                    innerLayout.sizePane("south", 0);

            });


	</script>

	<style type="text/css">

	.ui-layout-pane { /* all 'panes' */
		padding:		0px;
		background:		#FFF;
		/*border-top:		1px solid #BBB;
		border-bottom:          1px solid #BBB;*/
		overflow:		hidden;
		}
		.ui-layout-pane-north ,
		.ui-layout-pane-south {
			/*border: 1px solid #BBB;*/
		}
		.ui-layout-pane-west {
			border-left: 1px solid #BBB;
		}
		.ui-layout-pane-east {
			border-right: 1px solid #BBB;
		}
		.ui-layout-pane-center {
			border-left:  0;
			border-right: 0;
			}
			.inner-center {
				/*border: 1px solid #BBB;*/
			}

		.outer-west ,
		.outer-east {
			background-color: #EEE;
		}
		.middle-west ,
		.middle-east {
			background-color: #F8F8F8;
		}

	.ui-layout-resizer { /* all 'resizer-bars' */
		background: #015C8A;
		}
		.ui-layout-resizer:hover { /* all 'resizer-bars' */
			background: #E5EDF2;
		}
		.ui-layout-resizer-west {
			border-left: 1px solid #BBB;
		}
		.ui-layout-resizer-east {
			border-right: 1px solid #BBB;
		}

	.ui-layout-toggler { /* all 'toggler-buttons' */
		background: #AAA;
		}
		.ui-layout-toggler-closed { /* closed toggler-button */
			background: #CCC;
			border-bottom: 1px solid #BBB;
		}
		.ui-layout-toggler .content { /* toggler-text */
			font: 14px bold Verdana, Verdana, Arial, Helvetica, sans-serif;
		}
		.ui-layout-toggler:hover { /* mouse-over */
			}
			.ui-layout-toggler:hover .content { /* mouse-over */
				}

	.outer-center ,
	.middle-center {
		/* center pane that are 'containers' for a nested layout */
		padding: 0;
		border: 0;
	}

	</style>

</head>

<body>

<div class="outer-center">

    <div class="middle-center">
        <!-- cabecera-->
	<div class="ui-layout-north">
           <iframe id="header" name="header" align="top"
           width="100%" height="60" frameborder="0" scrolling="no"
           src="header.php">
           </iframe>
        </div>

        <!-- Contenidos -->
        <div class="inner-center">
            <?php if( SIS_GESTDOC==1 && SIS_CIUDAD=='CHICLAYO'){ ?>        
                <!--div id="chat_online" style="position:absolute; top:95%; width:230px; margin-left:82%;  height:30px; visibility:visible; z-index:-0">
                    <iframe id="chat" name="chat" 
                            width="100%" height="100%" frameborder="0" scrolling="auto"
                            src="chat/muestraUsuariosEnLinea.php"></iframe>
                </div-->
            <?php }?>
        <iframe id="content" name="content" class="ui-layout-center"
	width="100%" height="100%" frameborder="0" scrolling="auto"
	src="<?php echo iif($userId,'!=','','modulos.php','login.php')?>"></iframe>
        </div>
        
        <!-- Control, ubicar este codigo: [innerLayout.sizePane("south", 0)] y cambiar el 0 x 50, finalmente refrescar la página -->
	<div class="ui-layout-south">
        <iframe id="controle" name="controle" src="oculto.php" align="top" width="100%"  frameborder="0" scrolling="auto">
	</iframe>
        </div>

    </div>

    <!-- Menu -->
    <iframe id="menu_left" name="menu_left" width="210" height="100%" class="middle-west" src="menu.php" frameborder="0" scrolling="no"></iframe>

</div>
</body>
</html>