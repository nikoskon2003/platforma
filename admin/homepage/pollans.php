<?php session_start(); 

if(!isset($_SESSION['type'])){
    include '../../error.php';
    exit();
}
if(!isset($_GET['id'])){
    include '../../error.php';
    exit();
}
if(!is_numeric($_GET['id'])){
    include '../../error.php';
    exit();
}

include '../../includes/config.php';
include '../../includes/dbh.inc.php';
$username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
if($_SESSION['type'] !== 'ADMIN' && $_SESSION['type'] !== 'TEACHER'){
	include '../../error.php';
	exit();
}

$pollId = (int)$_GET['id'];
$res = mysqli_query($conn, "SELECT * FROM user_polls WHERE poll_id=$pollId");
if($res->num_rows < 1){
    header("Location: ./polls.php");
    exit();
}
$pollData = $res->fetch_assoc();

if($_SESSION['type'] !== 'ADMIN' && $pollData['poll_by'] !== $username){
    header("Location: ./polls.php");
    exit();
}

date_default_timezone_set('Europe/Athens');
$now = date('d/m/Y H:i:s', time());

include_once '../../includes/extrasLoader.inc.php';
include_once '../../includes/enc.inc.php';

$options = base64_decode($pollData['poll_options']);
$options = json_decode($options, JSON_UNESCAPED_UNICODE);

for($i=0;$i<count($options);$i++){
	echo '<b>' . htmlentities(base64_decode($options[$i])) . '</b>';
	$res = mysqli_query($conn, "SELECT * FROM user_poll_ans WHERE ans_poll=$pollId AND ans_val=$i");
	echo " (" . $res->num_rows . "):";
	while($row = $res->fetch_assoc()){
		$uname = mysqli_real_escape_string($conn, $row['ans_user']);
		$resb = mysqli_query($conn, "SELECT user_name FROM users WHERE user_username='$uname'");
		if($resb->num_rows > 0){
			echo '<br>&nbsp;' . decrypt($resb->fetch_assoc()['user_name']);
		}
	}
	
	echo '<br><br><br><br>';
}
