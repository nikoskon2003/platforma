<?php
session_start();
if(isset($_SESSION['type']) && isset($_POST['id'])){

    if(!is_numeric($_POST['id'])){
        include '../../error.php';
        exit();
    }
    $postId = (int)$_POST['id'];

    include '../config.php';
    include '../enc.inc.php';
    include '../dbh.inc.php';

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

    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='subject-teacher' AND link_used_id=$subjId");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }

    mysqli_query($conn, "DELETE FROM posts WHERE post_id=$postId");

    $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_usage='subject' AND post_used_id=$subjId AND post_visibility>0 ORDER BY post_date DESC LIMIT 1");
    if($res->num_rows < 1) mysqli_query($conn, "UPDATE subjects SET subject_latest_update='0000-00-00 00:00:00' WHERE subject_id=$subjId");
    else{
        $latestDate = $res->fetch_assoc()['post_date'];
        mysqli_query($conn, "UPDATE subjects SET subject_latest_update='$latestDate' WHERE subject_id=$subjId");
    }

    header("Location: ../../class/subject.php?s=$subjId");
    exit();
}
else
{
    include '../../error.php';
    exit();
}