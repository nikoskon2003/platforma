<?php
session_start();
if(isset($_POST['text']) && isset($_SESSION['type'])){
    if($_SESSION['type'] !== 'ADMIN'){
        include '../../../error.php';
        exit(); 
    }
    else {
        if(!isset($_POST['s']) || !isset($_POST['d']) || !isset($_POST['m']) || !isset($_POST['y'])){
            header("Location: ../../../");
            exit();
        }
        if(!is_numeric($_POST['s']) || !is_numeric($_POST['d']) || !is_numeric($_POST['m']) || !is_numeric($_POST['y'])){
            header("Location: ../../../");
            exit();
        }

        date_default_timezone_set('Europe/Athens');

        $subjectId = (int)($_POST['s']);
        $selMonth = (int)($_POST['m']);
        $selMonth = min(max($selMonth, 1), 12);
        $selYear = (int)($_POST['y']);

        $dim = date('t', strtotime($selYear . '-' . $selMonth . '-01'));

        $selDay = (int)($_POST['d']);
        $selDay = min(max($selDay, 1), $dim);

        $date = $selYear . '-' . $selMonth . '-' . $selDay;

        require_once '../../dbh.inc.php';
        require_once '../../enc.inc.php';

        $text = mysqli_real_escape_string($conn, encrypt($_POST['text']));
        $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);

        $res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$subjectId");
        if($res->num_rows < 1){
            header("Location: ../../../admin/subjects/");
            exit();
        }

        mysqli_query($conn, "INSERT INTO calendar_events (event_subject, event_date, event_user, event_text) VALUES ($subjectId, '$date', '$username', '$text')");
        
        echo 'ok';
    }
}
else {
    include '../../../error.php';
    exit();
}