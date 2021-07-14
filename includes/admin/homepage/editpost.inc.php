<?php
session_start();
if(isset($_SESSION['type']) && isset($_POST['id'])){

    if(!isset($_POST['title']) || !isset($_POST['text']) || !isset($_POST['files']) || !isset($_POST['visibility']) || !isset($_POST['notification'])){
        include '../../../error.php';
        exit();
    }
    if(!is_numeric($_POST['id'])){
        include '../../../error.php';
        exit();
    }
    $postId = (int)$_POST['id'];

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

    $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_id=$postId AND post_usage='homepage'");
    if($res->num_rows < 1){
        include '../../../error.php';
        exit();
    }
    $oldPostData = $res->fetch_assoc();

    if($_SESSION['type'] !== 'ADMIN' && $oldPostData['post_author'] !== $_SESSION['user_username']){
        include '../../../error.php';
        exit();
    }


    $title = trim($_POST['title']);
    
    $text = trim($_POST['text']);
    $text = preg_replace('/(\r\n)|\r|\n/', '<br>', $text);
    $text = str_replace('\\n', '<br>', $text);
    
    if($title == '' || $text == ''){
        header("Location: ../../../admin/homepage/editpost.php?e=empty");
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

    mysqli_query($conn, "UPDATE posts SET post_author='$username', post_date='$date', post_visibility=$visibility, post_title='$title', post_text='$text', post_files='$files' WHERE post_id=$postId");

    header("Location: ../../../admin/homepage/");
    connection_close();

    if($visibility > 0 && $_POST['notification'] == 'yes'){
        //send push notification :)
        include '../../notifications/sendnotification.inc.php';
        sendNotification(null, 'Ενημέρωση Ανακοίνωσης στην Αρχική Σελίδα');
    }

    exit();
}
else
{
    include '../../../error.php';
    exit();
}