<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mensaje</title>
  </head>
  <body>
      <center>
          
      <h4><?php echo $_POST['mpv_mensaje'] ?></h4>
      
      <?php if( $_POST['mpv_video'] ){ ?>      
        <span>Video Tutorial para realizar el trámite en la Mesa de Partes Virtual</span><br>
        <iframe width="260" height="115" src="<?php echo $_POST['mpv_video'] ?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>      
      <?php } ?>

      </center>
        
  </body>  
</html>