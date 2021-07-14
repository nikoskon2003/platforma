<?php
session_start();

if(!isset($_SESSION['type'])){
    include '../error.php';
    exit();
}
elseif($_SESSION['type'] !== 'STUDENT'){
    include '../error.php';
    exit();
}elseif((int)$_SESSION['user_class'] < 9){
	include '../error.php';
    exit();
}

if(!isset($_POST['u'])){
	include '../error.php';
    exit();
}

include '../includes/dbh.inc.php';
include '../includes/enc.inc.php';

$ousername = mysqli_real_escape_string($conn, $_POST['u']);

$res = mysqli_query($conn, "SELECT user_class FROM users WHERE user_username='$ousername' AND user_type=0");
if($res->num_rows <= 0){
	include '../error.php';
    exit();
}
$row = $res->fetch_assoc();
if((int)$row['user_class'] < 9){
	include '../error.php';
    exit();
}

//technically someone could write to himself... eh, too late now i guess
$myusername = mysqli_real_escape_string($conn, $_SESSION['user_username']);
$txt = trim($_POST['text']);
if($txt == ''){
	mysqli_query($conn, "DELETE FROM lefkoma_comments WHERE comm_from='$myusername' AND comm_to='$ousername' LIMIT 1");
	header("Location: .");
	exit();
}
$txt = mysqli_real_escape_string($conn, base64_encode($txt));
$res = mysqli_query($conn, "SELECT * FROM lefkoma_comments WHERE comm_from='$myusername' AND comm_to='$ousername' LIMIT 1");
if($res->num_rows > 0){
	mysqli_query($conn, "UPDATE lefkoma_comments SET comm_text='$txt' WHERE comm_from='$myusername' AND comm_to='$ousername' LIMIT 1");
	header("Location: .");
	exit();
}
else {
	mysqli_query($conn, "INSERT INTO lefkoma_comments (comm_from, comm_to, comm_text) VALUES ('$myusername', '$ousername', '$txt');");
	header("Location: .");
	exit();
}
header("Location: .");
exit();