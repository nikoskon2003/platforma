<?php
session_start();
if(isset($_POST['submit']) && isset($_SESSION['type'])){
    if($_SESSION['type'] !== 'ADMIN'){
        include '../../../error.php';
        exit();
    }
    else {
        if(!isset($_POST['name']) || !isset($_POST['class'])){
            header("Location: ../../../");
            exit();
        }

        require_once '../../dbh.inc.php';
        require_once '../../enc.inc.php';
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $class = mysqli_real_escape_string($conn, $_POST['class']);


        $preg = "/[^A-Za-z0-9α-ωΑ-ΩςίϊΐόάέύϋΰήώΈΎΫΊΪΌΆΏΉ _-]/u";
        $name = preg_replace($preg, '', $name);
        if($name == "" || $name == " "){
            header("Location: ../../../admin/subjects/newsubject.php?e=empty");
            exit();
        }

        $name = encrypt($name);

        if($class == "no") $class = 'NULL';
        else {
            $res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$class");
            if($res->num_rows < 1) $class = 'NULL';
        }

        mysqli_query($conn, "INSERT INTO subjects (subject_name, subject_class, subject_latest_update) VALUES ('$name', $class, '0000-00-00 00:00:00')");

        header("Location: ../../../admin/subjects/subject.php?s=" . $conn->insert_id);
        exit();
    }
}
else {
    include '../../../error.php';
    exit();
}
