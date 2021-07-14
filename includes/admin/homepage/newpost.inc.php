<?php
session_start();
if(isset($_SESSION['type']) && isset($_POST['submit'])){

    if(!isset($_POST['title']) || !isset($_POST['text']) || !isset($_POST['files']) || !isset($_POST['visibility']) || !isset($_POST['notification'])){
        include '../../../error.php';
        exit();
    }

    include '../../config.php';
    include '../../enc.inc.php';
    include '../../dbh.inc.php';
    include '../../extrasLoader.inc.php';

    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
    if($_SESSION['type'] !== 'ADMIN'){
        $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='homepage-author' AND link_user='$username'");
        if($res->num_rows < 1){
            include '../../../error.php';
            exit();
        }
    }


    $title = trim($_POST['title']);
    
    $text = trim($_POST['text']);
    $text = preg_replace('/(\r\n)|\r|\n/', '<br>', $text);
    $text = str_replace('\\n', '<br>', $text);
    
    if($title == '' || $text == ''){
        header("Location: ../../../admin/homepage/newpost.php?e=empty");
        exit();
    }

    $title = mysqli_real_escape_string($conn, encrypt($title));
    $text = mysqli_real_escape_string($conn, encrypt($text));

    $fileArr = explode(',', $_POST['files']);
    $files = '';

    for($i = 0; $i < count($fileArr); $i++){
        $file = mysqli_real_escape_string($conn, $fileArr[$i]);
        $res = mysqli_query($conn, "SELECT * FROM files WHERE file_uid='$file' AND file_owner='$username' LIMIT 1");
        if($res->num_rows > 0) $files .= $file . ',';
    }

    $files = mysqli_real_escape_string($conn, mb_substr($files, 0, -1));

    $visibility = ($_POST['visibility'] == 'none') ? 0 : (($_POST['visibility'] == 'users') ? 2 : 1);
    
    date_default_timezone_set('Europe/Athens');
    $date = date('Y-m-d H:i:s', time());

    mysqli_query($conn, "INSERT INTO posts (post_usage, post_author, post_date, post_visibility, post_title, post_text, post_files) VALUES ('homepage', '$username', '$date', $visibility, '$title', '$text', '$files')");

    header("Location: ../../../admin/homepage/");
    connection_close();

    if($visibility > 0 && $_POST['notification'] == 'yes'){
        include '../../notifications/sendnotification.inc.php';
        sendNotification(null, 'Νέα Ανακοίνωση στην Αρχική Σελίδα');
    }
    
    exit();
}
else
{
    include '../../../error.php';
    exit();
}