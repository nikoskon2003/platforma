<?php
session_start();
if(isset($_SESSION['type']) && isset($_POST['id'])){

    if(!is_numeric($_POST['id'])){
        include '../../../error.php';
        exit();
    }
    $postId = (int)$_POST['id'];

    include '../../config.php';
    include '../../enc.inc.php';
    include '../../dbh.inc.php';

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

    mysqli_query($conn, "DELETE FROM posts WHERE post_id=$postId");

    header("Location: ../../../admin/homepage/");
    exit();
}
else
{
    include '../../../error.php';
    exit();
}