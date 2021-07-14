<?php
session_start();
if(isset($_POST['delete']) && isset($_SESSION['type'])){
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

        require_once '../../dbh.inc.php';
        $id = (int)mysqli_real_escape_string($conn, $_POST['id']);

        $res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$id");
        if($res->num_rows < 1){
            header("Location: ../../../");
            exit();
        }

        mysqli_query($conn, "DELETE FROM subjects WHERE subject_id=$id");

        mysqli_query($conn, "DELETE FROM user_links WHERE link_usage='subject-student' AND link_used_id=$id");
        mysqli_query($conn, "DELETE FROM user_links WHERE link_usage='subject-teacher' AND link_used_id=$id");
        mysqli_query($conn, "DELETE FROM posts WHERE post_usage='subject' AND post_used_id=$id");
        mysqli_query($conn, "DELETE FROM calendar_events WHERE event_subject=$id");

        $res = mysqli_query($conn, "SELECT * FROM tests WHERE test_subject=$id");
        if($res->num_rows > 0) while($row = $res->fetch_assoc()){
            $testId = (int)$row['test_id'];
            deleteDir('../../../uploads/tests/' . $testId);
            mysqli_query($conn, "DELETE FROM test_responses WHERE response_test=$testId");
        }
        mysqli_query($conn, "DELETE FROM tests WHERE test_subject=$id");

        $res = mysqli_query($conn, "SELECT * FROM assignments WHERE assignment_subject=$id");
        if($res->num_rows > 0) while($row = $res->fetch_assoc()){
            $assignmentId = (int)$row['assignment_id'];

            $resu = mysqli_query($conn, "SELECT * FROM assignment_responses WHERE response_assignment=$assignmentId");
            if($resu->num_rows > 0) while($row = $resu->fetch_assoc()){
                $user = $row['response_user'];
                $fileId = $row['response_file'];

                $filePath = '../../../uploads/assignments/' . rawurlencode($user) . '/' . $fileId;
                unlink($filePath);
            }
            mysqli_query($conn, "DELETE FROM assignment_responses WHERE response_assignment=$assignmentId");
        }
        mysqli_query($conn, "DELETE FROM assignments WHERE assignment_subject=$id");

        header("Location: ../../../admin/subjects/");
        exit();
    }
}
else {
    include '../../../error.php';
    exit(); 
}

function deleteDir($dirPath) {
    if (!is_dir($dirPath)) return;
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') $dirPath .= '/';
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) deleteDir($file);
        else unlink($file);
    }
    rmdir($dirPath);
}