<?php
session_start();
if(isset($_POST['name']) && isset($_SESSION['type'])){
    if($_SESSION['type'] !== 'ADMIN'){
        include '../../../error.php';
        exit();
    }
    else {
        if(!isset($_POST['id']) || !isset($_POST['year']) || !isset($_POST['month']) || !isset($_POST['day']) || !isset($_POST['hour']) || !isset($_POST['minute'])){
            header("Location: ../../../error.php");
            exit();
        }
        if(!is_numeric($_POST['id']) || !is_numeric($_POST['year']) || !is_numeric($_POST['month']) || !is_numeric($_POST['day']) || !is_numeric($_POST['hour']) || !is_numeric($_POST['minute'])){
            header("Location: ../../../error.php");
            exit();
        }

        date_default_timezone_set('Europe/Athens');

        $assignmentId = (int)$_POST['id'];

        $year = (int)$_POST['year'];
        $month = (int)$_POST['month'];
        $day = (int)$_POST['day'];
        $hour = (int)$_POST['hour'];
        $minute = (int)$_POST['minute'];

        $dim = date('t', strtotime($year . '-' . $month . '-01'));

        if($month < 1 || $month > 12 || $day < 1 || $day > $dim || $hour < 0 || $hour > 23 || $minute < 0 || $minute > 59){
            header("Location: ../../../error.php");
            exit();
        }

        $date = $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':59';

        require_once '../../dbh.inc.php';
        require_once '../../enc.inc.php';

        $name = mysqli_real_escape_string($conn, encrypt(trim($_POST['name'])));
        $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);

        $res = mysqli_query($conn, "SELECT * FROM assignments WHERE assignment_id=$assignmentId");
        if($res->num_rows < 1){
            header("Location: ../../../admin/subjects/");
            exit();
        }
        $subjectId = (int)$res->fetch_assoc()['assignment_subject'];

        mysqli_query($conn, "UPDATE assignments SET assignment_name='$name', assignment_expires='$date', assignment_user='$username' WHERE assignment_id=$assignmentId");

        header("Location: ../../../admin/subjects/assignments.php?s=$subjectId&a=$assignmentId");
        exit();
    }
}
else {
    include '../../../error.php';
    exit();
}