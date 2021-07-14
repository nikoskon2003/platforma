<?php
session_start();
if(isset($_SESSION['type']) && (isset($_POST['submit']) || isset($_POST['delete']))){

    if($_SESSION['type'] !== 'ADMIN' || !isset($_POST['username'])){
        include '../../../error.php';
        exit();   
    }

    include_once '../../dbh.inc.php';

    $username = mysqli_real_escape_string($conn, $_POST['username']);

    if(isset($_POST['delete'])){
        mysqli_query($conn, "DELETE FROM user_links WHERE link_usage='program-editor' AND link_user='$username'");
        header("Location: ../../../admin/program/editors.php");
        exit();
    }
    else
    {
        $res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username'");
        if($res->num_rows < 1){
            header("Location: ../../../admin/program/editors.php");
            exit();
        }
        else
        {
            $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='program-editor' AND link_user='$username'");
            if($res->num_rows > 0){
                header("Location: ../../../admin/program/editors.php");
                exit();
            }
            else
            {
                mysqli_query($conn, "INSERT INTO user_links (link_usage, link_user) VALUES ('program-editor', '$username')");
                header("Location: ../../../admin/program/editors.php");
                exit();
            }
        }
    }

    header("Location: ../../../admin/program/editors.php");
    exit();

}
else{
    include '../../../error.php';
    exit();   
}