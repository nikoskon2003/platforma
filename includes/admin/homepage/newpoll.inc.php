<?php
session_start();
if(isset($_SESSION['type']) && isset($_POST['submit'])){

    if(!isset($_POST['text']) || !isset($_POST['options'])){
        include '../../../error.php';
        exit();
    }

    include '../../config.php';
    include '../../enc.inc.php';
    include '../../dbh.inc.php';
    include '../../extrasLoader.inc.php';

    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
    if($_SESSION['type'] !== 'ADMIN' && $_SESSION['type'] !== 'TEACHER'){
		include '../../../error.php';
		exit();
    }

    $text = trim($_POST['text']);
    $text = preg_replace('/(\r\n)|\r|\n/', '<br>', $text);
    $text = str_replace('\\n', '<br>', $text);
    if($text == ''){
        header("Location: ../../../admin/homepage/newpoll.php?e=empty");
        exit();
    }
    $text = mysqli_real_escape_string($conn, encrypt($text));

    date_default_timezone_set('Europe/Athens');
    $date = date('Y-m-d H:i:s', time());
	
	
	$imp = '';
	if(isset($_POST['classes'])){
		if(!is_array($_POST['classes'])){
			header("Location: ../../../admin/homepage/newpoll.php?c=empty");
			exit();
		}
		$allClasses = [];
		$res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='subject-teacher'");
		while($row = $res->fetch_assoc()){
			$subj = (int)$row['link_used_id'];
			
			$resb = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id='$subj' AND subject_class IS NOT NULL");
			if($resb->num_rows > 0){
				$cl = (int)$resb->fetch_assoc()['subject_class'];
				$allClasses[] = $cl;
			}
		}
		$res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='class-writer' AND link_user='$username'");
		while($row = $res->fetch_assoc()){
			$allClasses[] = (int)$row['link_used_id'];
		}
		
		foreach($_POST['classes'] as $cl){
			$cl = (int)$cl;
			if(!in_array($cl, $allClasses)){
				header("Location: ../../../admin/homepage/newpoll.php?c=empty");
				exit();
			}
		}
		$imp = implode(',', $_POST['classes']);
	}
	$cls = mysqli_real_escape_string($conn, base64_encode($imp));
	
	$options = json_decode($_POST['options'], JSON_UNESCAPED_UNICODE);
	if(!is_array($options)){
		header("Location: ../../../admin/homepage/newpoll.php?o=empty");
        exit();
	}
	$ota = [];
	foreach($options as $o){
		$o = trim($o);
		if($o != '') $ota[] = base64_encode($o);
	}
	if(count($ota) < 1){
		header("Location: ../../../admin/homepage/newpoll.php?o=empty");
        exit();
	}
	
	$opt = mysqli_real_escape_string($conn, base64_encode(json_encode($ota, JSON_UNESCAPED_UNICODE)));
	mysqli_query($conn, "INSERT INTO user_polls (poll_by, poll_date, poll_shown, poll_text, poll_options) VALUES ('$username', '$date', '$cls', '$text', '$opt')");

    header("Location: ../../../admin/homepage/polls.php");   
    exit();
}
else
{
    include '../../../error.php';
    exit();
}