<?php
session_start();
if(isset($_SESSION['type']) && isset($_GET['id']) && isset($_GET['a'])){
	
	if($_SESSION['type'] != 'STUDENT' || !isset($_SESSION['user_class'])){
		include '../error.php';
		exit();
	}
	
	include 'dbh.inc.php';
	
	$pollId = (int)$_GET['id'];
	$ansVal = (int)$_GET['a'];
	$un = mysqli_real_escape_string($conn, $_SESSION['user_username']);
	$class = (int)$_SESSION['user_class'];
	
	$res = mysqli_query($conn, "SELECT * FROM user_polls WHERE poll_id=$pollId");
	if($res->num_rows < 1){
		include '../error.php';
		exit();
	}
	$pollData = $res->fetch_assoc();
	
	$classes = base64_decode($pollData['poll_shown']);
	$classes = explode(',', trim($classes));
	for($i=0;$i<count($classes);$i++) $classes[$i] = (int)$classes[$i];
	
	if(!in_array($class, $classes)){
		include '../error.php';
		exit();
	}
	
	if(mysqli_query($conn, "SELECT * FROM user_poll_ans WHERE ans_poll=$pollId AND ans_user='$un'")->num_rows > 0){
		mysqli_query($conn, "UPDATE user_poll_ans SET ans_val=$ansVal WHERE ans_poll=$pollId AND ans_user='$un'");
		header("Location: ../");
		exit();
	}
	else mysqli_query($conn, "INSERT INTO user_poll_ans (ans_poll, ans_user, ans_val) VALUES ($pollId, '$un', $ansVal)");
	header("Location: ../");
	exit();
}
else {
	header("Location: ../");
	exit();
}