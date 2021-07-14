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
        include '../../../error.php';
        exit();
    }

    $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_id=$postId AND post_usage='class'");
    if($res->num_rows < 1){
        include '../../../error.php';
        exit();
    }
    $oldPostData = $res->fetch_assoc();

    $classId = $oldPostData['post_used_id'];

    mysqli_query($conn, "DELETE FROM posts WHERE post_id=$postId");

    header("Location: ../../../admin/classes/class.php?c=$classId");
    exit();
}
else
{
    include '../../../error.php';
    exit();
}