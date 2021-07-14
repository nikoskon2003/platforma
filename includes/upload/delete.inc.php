<?php
session_start();
if(isset($_SESSION['user_username']) && isset($_POST['uid'])){
    include '../dbh.inc.php';

    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
    $uid = mysqli_real_escape_string($conn, $_POST['uid']);

    $res = mysqli_query($conn, "SELECT * FROM files WHERE file_uid='$uid' AND file_owner='$username'");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }

    if(unlink("../../uploads/users/" . $username . '/' . $uid)){
        mysqli_query($conn, "DELETE FROM files WHERE file_uid='$uid' AND file_owner='$username'");
        echo 'ok';
    }
    else echo 'bad action';
}
else {
    include '../../error.php';
    exit();
}