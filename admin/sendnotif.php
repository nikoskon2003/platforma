<?php
session_start();
if(!isset($_SESSION['type'])){ 
	include '../error.php';
	exit();
}
if($_SESSION['type'] !== 'ADMIN'){ 
	include '../error.php';
	exit();
}

if(isset($_POST['user']) && isset($_POST['payload'])){
	if(trim($_POST['user']) !== '' || trim($_POST['payload']) !== ''){
		echo 'Sent!<br><br>';
		include '../includes/notifications/sendnotification.inc.php';
		
		if(isset($_POST['all'])){
			if($_POST['all'] === 'all' && $_POST['user'] === 'all')
				sendNotification(null, $_POST['payload']);
		}
		else{
			$usr = array_map('trim', explode(',', $_POST['user']));
			sendNotification($usr, $_POST['payload']);
		}
	}
}

echo '<form action="./sendnotif.php" method="POST">
<input type="text" name="user" placeholder="username"/><br>
<input type="text" name="payload" placeholder="payload"><br>
<input type="checkbox" name="all" value="all"/>Προς Όλους<br><br>
<input type="submit" name="s">
</form>';
