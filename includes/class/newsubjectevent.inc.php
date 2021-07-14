<?php
session_start();
if(isset($_POST['text']) && isset($_SESSION['type'])){
    if(!isset($_POST['s']) || !isset($_POST['d']) || !isset($_POST['m']) || !isset($_POST['y'])){
        include '../../error.php';
        exit();
    }
    if(!is_numeric($_POST['s']) || !is_numeric($_POST['d']) || !is_numeric($_POST['m']) || !is_numeric($_POST['y'])){
        include '../../error.php';
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

    require_once '../dbh.inc.php';
    require_once '../enc.inc.php';

    $text = mysqli_real_escape_string($conn, encrypt($_POST['text']));
    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);

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

    mysqli_query($conn, "INSERT INTO calendar_events (event_subject, event_date, event_user, event_text) VALUES ($subjectId, '$date', '$username', '$text')");
    
	/*
	if($dateOfEvent > $now){
		include '../notifications/sendnotification.inc.php';
        
        $userList = [];

        if(isset($classId)){
            $res = mysqli_query($conn, "SELECT user_username FROM users WHERE user_class=$classId");
            if($res->num_rows > 0)
                while($row = $res->fetch_assoc())
                    if(!in_array($row['user_username'], $userList))
                        $userList[] = $row['user_username'];
        }

        $res = mysqli_query($conn, "SELECT link_user FROM user_links WHERE link_usage='subject-teacher' AND link_used_id=$subjId");
        if($res->num_rows > 0)
            while($row = $res->fetch_assoc())
                if(!in_array($row['link_user'], $userList))
                    $userList[] = $row['link_user'];

        $res = mysqli_query($conn, "SELECT link_user FROM user_links WHERE link_usage='subject-student' AND link_used_id=$subjId");
        if($res->num_rows > 0)
            while($row = $res->fetch_assoc())
                if(!in_array($row['link_user'], $userList))
                    $userList[] = $row['link_user'];

        sendNotification($userList, 'Νέο Συμβάν στο μάθημα ' . $subjectName, 'class/subject.php?s=' . $subjId);
	}
	*/
    echo 'ok';
}
else {
    include '../../error.php';
    exit();
}