<?php
session_start();
if(isset($_POST['submit']) && isset($_SESSION['type'])){
    if(!isset($_POST['text']) || !isset($_POST['recipient']) || !isset($_POST['sender'])){
        include '../../error.php';
        exit();
    }

    if($_SESSION['user_username'] !== $_POST['sender']){
        include '../../error.php';
        exit();
    }

    include '../dbh.inc.php';
    include '../enc.inc.php';

    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
    $recipient = mysqli_real_escape_string($conn, $_POST['recipient']);

    $text = $_POST['text'];
    $text = preg_replace('/(\r\n)|\r|\n/', '<br>', $text);
    $text = str_replace('\\n', '<br>', $text);
    $text = mysqli_real_escape_string($conn, encrypt($text));

    $res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$recipient'");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }

    date_default_timezone_set('Europe/Athens');
    $date = date('Y-m-d H:i:s', time());
	$res = mysqli_query($conn, "SELECT * FROM messages WHERE message_sender='$username' AND message_recipient='$recipient' ORDER BY message_date DESC LIMIT 1");

    mysqli_query($conn, "INSERT INTO messages 
    (message_sender, message_recipient, message_date, message_content, message_type, message_opened) VALUES
    ('$username', '$recipient', '$date', '$text', 0, 0)");
	
	$diff = 10000;

	if($res->num_rows > 0){
		$ldate = $res->fetch_assoc()['message_date'];
		
		$now = new DateTime($date);
		$prev = new DateTime($ldate);
		$diff = $now->getTimestamp() - $prev->getTimestamp();
	}

	if($diff > 60*2){
		include_once "../notifications/sendnotification.inc.php";
		SendNotification($recipient, $_SESSION['user_name'] . ": Νέο Μήνυμα", 'messages/messages.php?u=' . rawurlencode($username));
	}

    echo 'Sent!';
    exit();
}
else{
    include '../../error.php';
    exit();
}