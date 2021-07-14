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
    $subjectId = $res->fetch_assoc()['event_subject'];
    if(is_null($subjectId)){
        include '../../error.php';
        exit();
    }

    $res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$subjectId");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }

    if($_SESSION['type'] == 'TEACHER'){
        $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='subject-teacher' AND link_used_id=$subjectId");
        if($res->num_rows < 1){
            include '../../error.php';
            exit();
        }
    }
    else {
        include '../../error.php';
        exit();
    }

    mysqli_query($conn, "UPDATE calendar_events SET event_text='$text' WHERE event_id=$eid");

    echo 'ok';
    
}
else {
    include '../../error.php';
    exit();
}