<?php
session_start();
if(isset($_POST['text']) && isset($_SESSION['type'])){
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

        $eid = (int)($_POST['id']);

        require_once '../../dbh.inc.php';
        require_once '../../enc.inc.php';

        $text = mysqli_real_escape_string($conn, encrypt($_POST['text']));
        $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);

        $res = mysqli_query($conn, "SELECT * FROM calendar_events WHERE event_id=$eid");
        if($res->num_rows < 1){
            header("Location: ../../../admin/classes/");
            exit();
        }

        $a = mysqli_query($conn, "UPDATE calendar_events SET event_text='$text' WHERE event_id=$eid");

        echo 'ok';
    }
}
else {
    include '../../../error.php';
    exit();
}