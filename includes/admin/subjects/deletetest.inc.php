<?php
session_start();
if(isset($_SESSION['type']) && isset($_POST['id'])){
    if($_SESSION['type'] !== 'ADMIN'){
        include '../../../error.php';
        exit();
    }
    if(!is_numeric($_POST['id'])){
        include '../../../error.php';
        exit();
    }

    $testId = (int)$_POST['id'];

    include '../../dbh.inc.php';

    $res = mysqli_query($conn, "SELECT * FROM tests WHERE test_id=$testId LIMIT 1");
    if($res->num_rows < 1){
        include '../../../error.php';
        exit();
    }
    $subject = (int)$res->fetch_assoc()['test_subject'];

    deleteDir('../../../uploads/tests/' . $testId);

    mysqli_query($conn, "DELETE FROM test_responses WHERE response_test=$testId");
    mysqli_query($conn, "DELETE FROM tests WHERE test_id=$testId");

    header("Location: ../../../admin/subjects/subject.php?s=$subject");
    exit();
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