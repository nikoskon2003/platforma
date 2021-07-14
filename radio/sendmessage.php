<?php
session_start();
if(isset($_SESSION['user_name']) && isset($_POST['text'])){
	include '../includes/dbh.inc.php';
	
	$text = trim($_POST['text']);
	if($text == '' || $text == null){
		echo 'empty';
		exit();
	}
	
	$text = mysqli_real_escape_string($conn, base64_encode($text));
	$name = mysqli_real_escape_string($conn, base64_encode($_SESSION['user_name']));
	date_default_timezone_set('Europe/Athens');
    $date = date('Y-m-d H:i:s', time());
	
	mysqli_query($conn, "INSERT INTO radio_messages (message_time, message_name, message_text) VALUES ('$date', '$name', '$text')");
	echo 'ok';
}
else {
	echo 'noauth';
	exit();
}