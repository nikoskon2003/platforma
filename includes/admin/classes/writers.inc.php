<?php
session_start();
if(isset($_SESSION['type']) && (isset($_POST['submit']) || isset($_POST['delete']))){

    if($_SESSION['type'] !== 'ADMIN' || !isset($_POST['username'])){
        include '../../../error.php';
        exit();
    }

    if(!isset($_POST['class-id'])) {
        include '../../../error.php';
        exit();
    }

    if(!is_numeric($_POST['class-id'])){
        include '../../../error.php';
        exit();
    }

    $classId = (int)$_POST['class-id'];

    include_once '../../dbh.inc.php';

    $res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$classId");
    if($res->num_rows < 1){
        include '../../../error.php';
        exit();
    }

    $username = mysqli_real_escape_string($conn, $_POST['username']);

    if(isset($_POST['delete'])){
        mysqli_query($conn, "DELETE FROM user_links WHERE link_usage='class-writer' AND link_user='$username' AND link_used_id=$classId");
        header("Location: ../../../admin/classes/writers.php?c=$classId");
        exit();
    }
    else
    {
        $res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username'");
        if($res->num_rows < 1){
            header("Location: ../../../admin/classes/writers.php?c=$classId");
            exit();
        }
        else
        {
            $userData = $res->fetch_assoc();

            if((int)$userData['user_type'] == 0){
                if(!isset($userData['user_class'])){
                    header("Location: ../../../admin/classes/writers.php?c=$classId&e=noclass");
                    exit();
                }
                elseif((int)$userData['user_class'] != $classId){
                    header("Location: ../../../admin/classes/writers.php?c=$classId&e=noclass");
                    exit();
                }
            }


            $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='class-writer' AND link_user='$username' AND link_used_id=$classId");
            if($res->num_rows > 0){
                header("Location: ../../../admin/classes/writers.php?c=$classId");
                exit();
            }
            else
            {
                mysqli_query($conn, "INSERT INTO user_links (link_usage, link_user, link_used_id) VALUES ('class-writer', '$username', $classId)");
                header("Location: ../../../admin/classes/writers.php?c=$classId");
                exit();
            }
        }
    }

    header("Location: ../../../admin/classes/writers.php?c=$classId");
    exit();

}
else{
    include '../../../error.php';
    exit();   
}