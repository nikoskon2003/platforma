<?php
session_start();
if(isset($_POST['submit'])){
    if($_SESSION['type'] !== "ADMIN"){
        include '../../../error.php';
        exit();
    }
    if(!isset($_POST['name']) || !isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['password-again']) || !isset($_POST['class'])){
        header("location: ../../../");
        exit();
    }

    include_once '../../dbh.inc.php';
    include_once '../../enc.inc.php';
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));
    $passwordAgain = mysqli_real_escape_string($conn, trim($_POST['password-again']));
    $class = mysqli_real_escape_string($conn, $_POST['class']);

    $preg = "/^[A-Za-z0-9α-ωΑ-ΩςίϊΐόάέύϋΰήώΈΎΫΊΪΌΆΏΉ _-]+$/u";

    if(!preg_match($preg, $name)){
        header("location: ../../../admin/users/newuser.php?e=chars");
        exit();
    }

    $name = encrypt($name);

    $res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username'");
    if($res->num_rows < 1){
        header("location: ../../../admin/users/");
        exit();
    }
    if($password != $passwordAgain && $password != '' && $password != ' '){
        header("location: ../../../admin/users/");
        exit();
    }

    $h = hash('sha256', $password);
	$e = encrypt($password);
	$f = (int)base_convert($h[0], 36, 10) + 1;
	$encPass = substr_replace($h, $e, $f, 0);

    if($class == "no") $class = 'NULL';
    else {
        $res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id='$class'");
        if($res->num_rows < 1) $class = 'NULL';
    }

    if($password != '' && $password != ' '){
		$sec = randomString(128);
        mysqli_query($conn, "UPDATE users SET user_password='$encPass', user_secret='$sec', user_name='$name', user_class=$class WHERE user_username='$username'");
	}
	else 
        mysqli_query($conn, "UPDATE users SET user_name='$name', user_class=$class WHERE user_username='$username'");

    header("location: ../../../admin/users/");
    exit();
}
else{
    include '../../../error.php';
    exit(); 
}