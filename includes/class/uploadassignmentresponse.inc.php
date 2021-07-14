<?php
session_start();
if(isset($_SESSION['type']) && isset($_POST['assignment'])){
    if($_SESSION['type'] != 'STUDENT'){
        include '../../error.php';
        exit();
    }
    if(!is_numeric($_POST['assignment'])){
        include '../../error.php';
        exit();
    }
    if(!isset($_FILES["file"])){
        include '../../error.php';
        exit();
    }
    
    $assignmentId = (int)$_POST['assignment'];

    include '../dbh.inc.php';
    include '../enc.inc.php';

    $res =  mysqli_query($conn, "SELECT * FROM assignments WHERE assignment_id=$assignmentId");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }
    $row = $res->fetch_assoc();
    $subjId = (int)$row['assignment_subject'];
    $expires = new DateTime($row['assignment_expires']);

    date_default_timezone_set('Europe/Athens');
    $date = date('Y-m-d H:i:s', time());

    /*if(new DateTime($date) > $expires){
        header("Location: ../../class/assignments.php?s=$subjId&a=$assignmentId");
        exit();
    }*/
    if($_FILES["file"]["tmp_name"] == ""){
        header("Location: ../../class/assignments.php?s=$subjId&a=$assignmentId");
        exit();
    }

    $res =  mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$subjId");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }
    $subjClass = (int)$res->fetch_assoc()['subject_class'];

    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='subject-student' AND link_used_id=$subjId");
    if($res->num_rows < 1){
        if(!is_null($_SESSION['user_class'])){
            if($_SESSION['user_class'] != $subjClass){
                include '../../error.php';
                exit();
            }
        }
        else {
            include '../../error.php';
            exit();
        }
    }

    
    $rnd = mysqli_real_escape_string($conn, randomString(random_int(30, 40)));
    while(true){
        if(mysqli_query($conn, "SELECT * FROM assignment_responses WHERE response_file='$rnd'")->num_rows > 0)
            $rnd = mysqli_real_escape_string($conn, randomString(random_int(30, 50)));
        else break;
    }

    $fileName = mysqli_real_escape_string($conn, $_FILES["file"]["name"]);

    $upPath = '../../uploads/assignments/' . rawurlencode($username);

    if (!file_exists($upPath)) mkdir($upPath, 0777, true);

    $filePath = $upPath . '/' . $rnd;

    if(move_uploaded_file($_FILES["file"]["tmp_name"], $filePath))
        mysqli_query($conn, "INSERT INTO assignment_responses (response_user, response_assignment, response_date, response_file, response_file_name) VALUES ('$username', '$assignmentId', '$date', '$rnd', '$fileName')");

    
    header("Location: ../../class/assignments.php?s=$subjId&a=$assignmentId");
}
else {
    include '../../error.php';
    exit();
}
