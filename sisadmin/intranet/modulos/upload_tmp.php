<?php
if (($_FILES["file"]["type"] == "image/pjpeg")
    || ($_FILES["file"]["type"] == "image/jpeg")
    || ($_FILES["file"]["type"] == "image/jpg")            
    || ($_FILES["file"]["type"] == "image/png")
    || ($_FILES["file"]["type"] == "image/gif")) {
    $path_image_tmp="../../../docs/reportes/";
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $path_image_tmp.$_FILES['file']['name'])) {
        //more code here...
        echo '../'.$path_image_tmp.$_FILES['file']['name'];
    } else {
        echo 0;
    }
} else {
    echo 0;
}