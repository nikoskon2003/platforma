<?php
session_start();
if(isset($_POST['submit']) && isset($_SESSION['type'])){
    if($_SESSION['type'] !== 'ADMIN'){
        include '../../../error.php';
        exit(); 
    }
    else {
        if(!isset($_POST['name'])){
            header("Location: ../../../");
            exit();
        }

        require_once '../../dbh.inc.php';
        $name = mysqli_real_escape_string($conn, $_POST['name']);

        $preg = "/[^A-Za-z0-9α-ωΑ-ΩςίϊΐόάέύϋΰήώΈΎΫΊΪΌΆΏΉ _-]/";
        $name = preg_replace($preg, '', $name);

        if($name == "" || $name == " "){
            header("Location: ../../../admin/classes/newclass.php?e=empty");
            exit();
        }

        $res = mysqli_query($conn, "SELECT * FROM classes WHERE class_name='$name'");
        if($res->num_rows > 0){
            header("Location: ../../../admin/classes/newclass.php?e=exists");
            exit();
        }

        mysqli_query($conn, "INSERT INTO classes (class_name) VALUES ('$name')");

        header("Location: ../../../admin/classes/");
        exit();
    }
}
else {
    include '../../../error.php';
    exit();
}