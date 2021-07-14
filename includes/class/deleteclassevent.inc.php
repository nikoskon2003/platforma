<?php
session_start();
if(isset($_POST['id']) && isset($_SESSION['type'])){
    if(!is_numeric($_POST['id'])){
        header("Location: ../../");
        exit();
    }

    $eid = (int)($_POST['id']);

    require_once '../dbh.inc.php';
    require_once '../enc.inc.php';

    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);

    $res = mysqli_query($conn, "SELECT * FROM calendar_events WHERE event_id=$eid AND event_user='$username'");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }
    $classId = $res->fetch_assoc()['event_class'];
    if(is_null($classId)){
        include '../../error.php';
        exit();
    }

    $res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$classId");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }

    if($_SESSION['type'] == 'STUDENT'){
        if($_SESSION['user_class'] != $classId){
            include '../../error.php';
            exit();
        }
        $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='class-writer' AND link_used_id=$classId");
        if($res->num_rows < 1){
            include '../../error.php';
            exit();
        }
    }
    elseif($_SESSION['type'] == 'TEACHER'){
        $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='class-writer' AND link_used_id=$classId");
        if($res->num_rows < 1){
            include '../../error.php';
            exit();
        }
    }
    else {
        include '../../error.php';
        exit();
    }

    mysqli_query($conn, "DELETE FROM calendar_events WHERE event_id=$eid");
    
    echo 'ok';
}
else {
    include '../../error.php';
    exit();
}