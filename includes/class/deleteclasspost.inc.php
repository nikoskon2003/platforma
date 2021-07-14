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
    include '../extrasLoader.inc.php';

    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);

    $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_id=$postId AND post_usage='class' AND post_author='$username'");
    if($res->num_rows < 1){
        include '../../class/';
        exit();
    }
    $oldPostData = $res->fetch_assoc();

    $classId = $oldPostData['post_used_id'];

    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='class-writer' AND link_used_id=$classId");
    if($res->num_rows < 1){
        header("Location: ../../class/");
        exit();
    }

    mysqli_query($conn, "DELETE FROM posts WHERE post_id=$postId");

    header("Location: ../../class/");
    exit();
}
else
{
    include '../../../error.php';
    exit();
}