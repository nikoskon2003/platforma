<?php
session_start();
if(!isset($_GET['id']) || !isset($_SESSION['type'])){
    include '../error.php';
    exit();
}
else {
    $id = (int)intval($_GET['id']);
	
	if($id < 1 || $id > 10) {
        header("Content-Type: image/png");
        readfile('../resources/icons/error.png');
        exit();
    }
    $filepath = './files/ekp' . $id . '.mp3';  //None of these files exist!
    if(!file_exists($filepath)){
        header("Content-Type: image/png");
        readfile('../resources/icons/error.png');
        exit();
    }
    header('title: File Transfer');
    header("Content-Type: audio/mpeg");
    header("Content-Disposition: attachment; filename=\"ekp-" . $id . ".mp3\"");
    header('Content-Length: ' . filesize($filepath));
    
    $chunkSize = 10 * 1024 * 1024;
    $handle = @fopen($filepath, 'rb');
    while (!feof($handle))
    {
        $buffer = fread($handle, $chunkSize);
        echo $buffer;
        ob_flush();
        flush();
    }
    fclose($handle);
}