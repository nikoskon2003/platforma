<?php
session_start();
if(isset($_POST['delete']) && isset($_SESSION['type'])){
    if($_SESSION['type'] !== 'ADMIN'){
        include '../../../error.php';
        exit(); 
    }
    else {
        if(!isset($_POST['id'])){
            header("Location: ../../../");
            exit();
        }
        if(!is_numeric($_POST['id'])){
            header("Location: ../../../");
            exit();
        }

        require_once '../../dbh.inc.php';
        $id = (int)mysqli_real_escape_string($conn, $_POST['id']);

        $res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$id");
        if($res->num_rows < 1){
            header("Location: ../../../");
            exit();
        }

        mysqli_query($conn, "DELETE FROM classes WHERE class_id=$id");

        mysqli_query($conn, "UPDATE users SET user_class=NULL WHERE user_main_class=$id");
        mysqli_query($conn, "UPDATE subjects SET subject_class=NULL WHERE subject_class=$id");
        mysqli_query($conn, "DELETE FROM posts WHERE post_usage='class' AND post_used_id=$id");
        mysqli_query($conn, "DELETE FROM calendar_events WHERE event_class=$id");
        mysqli_query($conn, "DELETE FROM user_links WHERE link_usage='class-writer' AND link_used_id=$id");

        header("Location: ../../../admin/classes/");
        exit();
    }
}
else {
    include '../../../error.php';
    exit(); 
}