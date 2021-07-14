<?php
session_start();

if(isset($_POST['text'])){
	$text = trim($_POST['text']);
	if($text == '' || $text == null){
		echo 'empty';
		exit();
	}
	
	include '../includes/dbh.inc.php';
	$text = mysqli_real_escape_string($conn, base64_encode($text));
	date_default_timezone_set('Europe/Athens');
    $date = date('Y-m-d H:i:s', time());
	
	mysqli_query($conn, "INSERT INTO radio_dir (dir_time, dir_text) VALUES ('$date', '$text')");
	echo 'ok';
}
else {
	echo 'empty';
	exit();
}