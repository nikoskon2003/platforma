<?php
session_start();
if(isset($_SESSION['type']) && isset($_POST['id'])){

    if(!isset($_POST['title']) || !isset($_POST['text']) || !isset($_POST['files']) || !isset($_POST['visibility']) || !isset($_POST['notification'])){
        include '../../error.php';
        exit();
    }
    if(!is_numeric($_POST['id'])){
        include '../../error.php';
        exit();
    }
    $postId = (int)$_POST['id'];

    include '../config.php';
    include '../enc.inc.php';
    include '../dbh.inc.php';
    include '../extrasLoader.inc.php';

    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
    if($_SESSION['type'] !== 'TEACHER'){
        include '../../error.php';
        exit();
    }

    $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_id=$postId AND post_usage='subject' AND post_author='$username'");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }
    $oldPostData = $res->fetch_assoc();

    $subjId = $oldPostData['post_used_id'];

    $res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$subjId");
    if($res->num_rows < 1) {
        include '../../error.php';
        exit();
    }
    $row = $res->fetch_assoc();
    $classId = $row['subject_class'];
    $subjectName = decrypt($row['subject_name']);

    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='subject-teacher' AND link_used_id=$subjId");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }

    $title = trim($_POST['title']);
    
    $text = trim($_POST['text']);
    $text = preg_replace('/(\r\n)|\r|\n/', '<br>', $text);
    $text = str_replace('\\n', '<br>', $text);
    
    if($title == '' || $text == ''){
        header("Location: ../../class/editsubjectpost.php?id=$postId&e=empty");
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

    $visibility = ($_POST['visibility'] == 'none') ? 0 : 1;
    
    date_default_timezone_set('Europe/Athens');
    $date = date('Y-m-d H:i:s', time());

    mysqli_query($conn, "UPDATE posts SET post_author='$username', post_date='$date', post_visibility=$visibility, post_title='$title', post_text='$text', post_files='$files' WHERE post_id=$postId");

    if($visibility > 0) mysqli_query($conn, "UPDATE subjects SET subject_latest_update='$date' WHERE subject_id=$subjId");
    else {
        $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_usage='subject' AND post_used_id=$subjId AND post_visibility>0 ORDER BY post_date DESC LIMIT 1");
        if($res->num_rows < 1) mysqli_query($conn, "UPDATE subjects SET subject_latest_update='0000-00-00 00:00:00' WHERE subject_id=$subjId");
        else{
            $latestDate = $res->fetch_assoc()['post_date'];
            mysqli_query($conn, "UPDATE subjects SET subject_latest_update='$latestDate' WHERE subject_id=$subjId");
        }
    }

    header("Location: ../../class/subject.php?s=$subjId");
    connection_close();

    if($visibility > 0 && $_POST['notification'] == 'yes'){
        include '../notifications/sendnotification.inc.php';
        
        $userList = [];

        if(isset($classId)){
            $res = mysqli_query($conn, "SELECT user_username FROM users WHERE user_class=$classId");
            if($res->num_rows > 0)
                while($row = $res->fetch_assoc())
                    if(!in_array($row['user_username'], $userList))
                        $userList[] = $row['user_username'];
        }

        $res = mysqli_query($conn, "SELECT link_user FROM user_links WHERE link_usage='subject-teacher' AND link_used_id=$subjId");
        if($res->num_rows > 0)
            while($row = $res->fetch_assoc())
                if(!in_array($row['link_user'], $userList))
                    $userList[] = $row['link_user'];

        $res = mysqli_query($conn, "SELECT link_user FROM user_links WHERE link_usage='subject-student' AND link_used_id=$subjId");
        if($res->num_rows > 0)
            while($row = $res->fetch_assoc())
                if(!in_array($row['link_user'], $userList))
                    $userList[] = $row['link_user'];

        sendNotification($userList, 'Νέα Ανακοίνωση στο μάθημα ' . $subjectName, 'class/subject.php?s=' . $subjId);
    }

    exit();
}
else
{
    include '../../error.php';
    exit();
}