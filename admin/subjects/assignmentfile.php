<?php
session_start();
if(!isset($_GET['id']) || !isset($_SESSION['type'])){
    include '../../error.php';
    exit();
}
else {
    include_once '../../includes/dbh.inc.php';
    $uid = mysqli_real_escape_string($conn, $_GET['id']);
    
    $filename = 'error';

    $res = mysqli_query($conn, "SELECT * FROM assignment_responses WHERE response_file='$uid' LIMIT 1");
    if($res->num_rows < 1){
        header("Content-Type: image/png");
        readfile('../../resources/icons/error.png');
        exit();
    }
    $data = $res->fetch_assoc();
    $filepath = '../../uploads/assignments/' . rawurlencode($data['response_user']) . '/' . $data['response_file'];
    $filename = $data['response_file_name'];
    

    if(!file_exists($filepath)){
        header("Content-Type: image/png");
        readfile('../../resources/icons/error.png');
        exit();
    }

    $mime = mime_content_type($filepath);

    header('title: File Transfer');
    header("Content-Type: $mime");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Content-Length: ' . filesize($filepath));
    
    $chunkSize = 1024 * 1024;
    $handle = fopen($filepath, 'rb');
    while (!feof($handle))
    {
        $buffer = fread($handle, $chunkSize);
        echo $buffer;
        ob_flush();
        flush();
    }
    fclose($handle);
}