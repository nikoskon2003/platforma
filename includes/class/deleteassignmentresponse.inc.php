<?php
session_start();
if(isset($_SESSION['type']) && isset($_POST['id'])){
    if($_SESSION['type'] != 'STUDENT'){
        include '../../error.php';
        exit();
    }

    include '../dbh.inc.php';
    include '../enc.inc.php';

    $fileId = mysqli_real_escape_string($conn, $_POST['id']);
    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);

    $res = mysqli_query($conn, "SELECT * FROM assignment_responses WHERE response_file='$fileId' AND response_user='$username'");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }
    $assignmentId = (int)$res->fetch_assoc()['response_assignment'];
    
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

    $filePath = '../../uploads/assignments/' . rawurlencode($username) . '/' . $fileId;
    unlink($filePath);
    mysqli_query($conn, "DELETE FROM assignment_responses WHERE response_file='$fileId'");
    
    header("Location: ../../class/assignments.php?s=$subjId&a=$assignmentId");
}
else {
    include '../../error.php';
    exit();
}