<?php
session_start();
if(isset($_POST['submit'])){
    if($_SESSION['type'] !== "ADMIN"){
        include '../../../error.php';
        exit();
    }
    if(!isset($_POST['name']) || !isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['password-again'])){
        header("location: ../../../");
        exit();
    }

    include_once '../../dbh.inc.php';
    include_once '../../enc.inc.php';
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));
    $passwordAgain = mysqli_real_escape_string($conn, trim($_POST['password-again']));

    $preg = "/^[A-Za-z0-9α-ωΑ-ΩςίϊΐόάέύϋΰήώΈΎΫΊΪΌΆΏΉ _-]+$/u";

    if(!preg_match($preg, $username) || !preg_match($preg, $name)){
        header("location: ../../../admin/users/newuser.php?e=chars");
        exit();
    }

    $res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username'");
    if($res->num_rows > 0){
        header("location: ../../../admin/users/newuser.php?e=exists");
        exit();
    }
    if($password != $passwordAgain){
        header("location: ../../../admin/users/newuser.php");
        exit();
    }

    $name = encrypt($name);
	
    $h = hash('sha256', $password);
	$e = encrypt($password);
	$f = (int)base_convert($h[0], 36, 10) + 1;
	$encPass = substr_replace($h, $e, $f, 0);

    mysqli_query($conn, "INSERT INTO users
        (user_username, user_password, user_type, user_name) VALUES
        ('$username', '$encPass', 1, '$name')");

    header("location: ../../../admin/users/");
    exit();

}
else{
    include '../../../error.php';
    exit(); 
}