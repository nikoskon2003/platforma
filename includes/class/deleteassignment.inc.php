<?php
session_start();
if(isset($_POST['id']) && isset($_SESSION['type'])){
    if($_SESSION['type'] != 'TEACHER'){
        include '../../error.php';
        exit();
    }
    else {
        if(!is_numeric($_POST['id'])){
            header("Location: ../../error.php");
            exit();
        }

        date_default_timezone_set('Europe/Athens');

        $assignmentId = (int)$_POST['id'];

        require_once '../dbh.inc.php';
        require_once '../enc.inc.php';

        $res = mysqli_query($conn, "SELECT * FROM assignments WHERE assignment_id=$assignmentId");
        if($res->num_rows < 1){
            header("Location: ../../class/");
            exit();
        }
        $subjectId = (int)$res->fetch_assoc()['assignment_subject'];

        $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
        $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='subject-teacher' AND link_used_id=$subjectId");
        if($res->num_rows < 1){
            include '../../error.php';
            exit();
        }

        mysqli_query($conn, "DELETE FROM assignments WHERE assignment_id=$assignmentId");


        $res = mysqli_query($conn, "SELECT * FROM assignment_responses WHERE response_assignment=$assignmentId");
        if($res->num_rows > 0) while($row = $res->fetch_assoc()){
            $user = $row['response_user'];
            $fileId = $row['response_file'];

            $filepath = '../../uploads/assignments/' . rawurlencode($user) . '/' . $fileId;
            unlink($filepath);
        }
        mysqli_query($conn, "DELETE FROM assignment_responses WHERE response_assignment=$assignmentId");

        header("Location: ../../class/assignments.php?s=$subjectId");
        exit();
    }
}
else {
    include '../../error.php';
    exit();
}