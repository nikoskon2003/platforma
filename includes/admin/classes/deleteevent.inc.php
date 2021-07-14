<?php
session_start();
if(isset($_POST['id']) && isset($_SESSION['type'])){
    if($_SESSION['type'] !== 'ADMIN'){
        include '../../../error.php';
        exit(); 
    }
    else {
        if(!is_numeric($_POST['id'])){
            header("Location: ../../../");
            exit();
        }

        $eid = (int)($_POST['id']);

        require_once '../../dbh.inc.php';
        require_once '../../enc.inc.php';

        $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);//needed for teacher

        $res = mysqli_query($conn, "SELECT * FROM calendar_events WHERE event_id=$eid");
        if($res->num_rows < 1){
            header("Location: ../../../admin/classes/");
            exit();
        }

        mysqli_query($conn, "DELETE FROM calendar_events WHERE event_id=$eid");
        
        echo 'ok';
    }
}
else {
    include '../../../error.php';
    exit();
}