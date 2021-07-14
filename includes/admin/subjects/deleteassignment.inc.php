<?php
session_start();
if(isset($_POST['id']) && isset($_SESSION['type'])){
    if($_SESSION['type'] !== 'ADMIN'){
        include '../../../error.php';
        exit();
    }
    else {
        if(!is_numeric($_POST['id'])){
            header("Location: ../../../error.php");
            exit();
        }

        date_default_timezone_set('Europe/Athens');

        $assignmentId = (int)$_POST['id'];

        require_once '../../dbh.inc.php';
        require_once '../../enc.inc.php';

        $res = mysqli_query($conn, "SELECT * FROM assignments WHERE assignment_id=$assignmentId");
        if($res->num_rows < 1){
            header("Location: ../../../admin/subjects/");
            exit();
        }
        $subjectId = (int)$res->fetch_assoc()['assignment_subject'];

        mysqli_query($conn, "DELETE FROM assignments WHERE assignment_id=$assignmentId");

        $res = mysqli_query($conn, "SELECT * FROM assignment_responses WHERE response_assignment=$assignmentId");
        if($res->num_rows > 0) while($row = $res->fetch_assoc()){
            $user = $row['response_user'];
            $fileId = $row['response_file'];

            $filePath = '../../../uploads/assignments/' . rawurlencode($user) . '/' . $fileId;
            unlink($filePath);
        }
        mysqli_query($conn, "DELETE FROM assignment_responses WHERE response_assignment=$assignmentId");

        header("Location: ../../../admin/subjects/assignments.php?s=$subjectId");
        exit();
    }
}
else {
    include '../../../error.php';
    exit();
}