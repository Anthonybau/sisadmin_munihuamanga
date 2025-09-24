<?php
$filePathZIP=$_GET['zip_file'];
$fileZIP=$_GET['zip_name'];

header("Content-type: application/octet-stream");
header("Content-disposition: attachment; filename=$fileZIP");



/*Read the size of the file */
readfile($filePathZIP);

/* Cierrro la conexion */
unlink($filePathZIP);