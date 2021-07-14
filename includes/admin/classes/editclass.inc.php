<?php
session_start();
if(isset($_POST['submit']) && isset($_SESSION['type'])){
    if($_SESSION['type'] !== 'ADMIN'){
        include '../../../error.php';
        exit();
    }
    else {
        if(!isset($_POST['name']) || !isset($_POST['id'])){
            header("Location: ../../../");
            exit();
        }
        if(!is_numeric($_POST['id'])){
            header("Location: ../../../");
            exit();
        }

        require_once '../../dbh.inc.php';
        $id = (int)mysqli_real_escape_string($conn, $_POST['id']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        
        $preg = "/[^A-Za-z0-9α-ωΑ-ΩςίϊΐόάέύϋΰήώΈΎΫΊΪΌΆΏΉ _-]/";
        $name = preg_replace($preg, '', $name);

        $res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$id");
        if($res->num_rows < 1){
            header("Location: ../../../");
            exit();
        }
        $prevName = $res->fetch_assoc()['class_name'];

        if($name == "" || $name == " "){
            header("Location: ../../../admin/classes/class.php?c=$id&e=empty");
            exit();
        }
        if($name == $prevName) {
            header("Location: ../../../admin/classes/class.php?c=" . $id);
            exit();
        }

        $res = mysqli_query($conn, "SELECT * FROM classes WHERE class_name='$name'");
        if($res->num_rows > 0){
            header("Location: ../../../admin/classes/class.php?c=$id&e=exists");
            exit();
        }

        mysqli_query($conn, "UPDATE classes SET class_name='$name' WHERE class_id=$id");

        header("Location: ../../../admin/classes/class.php?c=" . $id);
        exit();
    }
}
else {
    include '../../../error.php';
    exit(); 
}