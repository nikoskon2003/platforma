<?php
session_start();
if(isset($_SESSION['type']) && isset($_POST['id'])){

    include '../../config.php';
    include '../../enc.inc.php';
    include '../../dbh.inc.php';
    include '../../extrasLoader.inc.php';

    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
    if($_SESSION['type'] !== 'ADMIN' && $_SESSION['type'] !== 'TEACHER'){
		include '../../../error.php';
		exit();
    }
	
	$pollId = (int)$_POST['id'];
	$res = mysqli_query($conn, "SELECT * FROM user_polls WHERE poll_id=$pollId");
	if($res->num_rows < 1){
		include '../../../error.php';
		exit();
	}
	$pollData = $res->fetch_assoc();
	
	if($_SESSION['type'] !== 'ADMIN' && $pollData['poll_by'] !== $username){
		include '../../../error.php';
		exit();
	}

	mysqli_query($conn, "DELETE FROM user_polls WHERE poll_id=$pollId");
	
	mysqli_query($conn, "DELETE FROM user_poll_ans WHERE ans_poll=$pollId");
	
    header("Location: ../../../admin/homepage/polls.php");   
    exit();
}
else
{
    include '../../../error.php';
    exit();
}