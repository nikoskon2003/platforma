<?php
session_start();
if(isset($_POST['text']) && isset($_SESSION['type'])){
    if(!isset($_POST['id'])){
        include '../../error.php';
        exit();
    }
    if(!is_numeric($_POST['id'])){
        include '../../error.php';
        exit();
    }

    $eid = (int)($_POST['id']);

    require_once '../dbh.inc.php';
    require_once '../enc.inc.php';

    $text = mysqli_real_escape_string($conn, encrypt($_POST['text']));
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

    $a = mysqli_query($conn, "UPDATE calendar_events SET event_text='$text' WHERE event_id=$eid");

    echo 'ok';
    
}
else {
    include '../../error.php';
    exit();
}