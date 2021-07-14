<?php
if(!isset($_GET['id'])){
    include './error.php';
    exit();
}
else {
    include_once './includes/dbh.inc.php';
    $uid = mysqli_real_escape_string($conn, $_GET['id']);
    $filename = 'error';

    if($uid == 'program-students'){
        $filepath = './uploads/program/students';
        $res = mysqli_query($conn, "SELECT * FROM options WHERE option_name='program-students-file' LIMIT 1");
        if($res->num_rows > 0) $filename = $res->fetch_assoc()['option_value'];
        else {
            header("Content-Type: image/png");
            readfile('resources/icons/error.png');
            exit();
        }
    }
    elseif($uid == 'program-teachers'){
        $filepath = './uploads/program/teachers';
        $res = mysqli_query($conn, "SELECT * FROM options WHERE option_name='program-teachers-file' LIMIT 1");
        if($res->num_rows > 0) $filename = $res->fetch_assoc()['option_value'];
        else {
            header("Content-Type: image/png");
            readfile('resources/icons/error.png');
            exit();
        }
    }
    else {
        $res = mysqli_query($conn, "SELECT * FROM files WHERE file_uid='$uid' LIMIT 1");
        if($res->num_rows < 1){
            header("Content-Type: image/png");
            readfile('resources/icons/error.png');
            exit();
        }
        $data = $res->fetch_assoc();
        $filepath = './uploads/users/' . rawurlencode($data['file_owner']) . '/' . $data['file_uid'];
        $filename = $data['file_name'];
    }

    if(!file_exists($filepath)){
        header("Content-Type: image/png");
        readfile('resources/icons/error.png');
        exit();
    }

    $mime = mime_content_type($filepath);

    header('title: File Transfer');
    header("Content-Type: $mime");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Content-Length: ' . filesize($filepath));
    
    $chunkSize = 10 * 1024 * 1024;
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