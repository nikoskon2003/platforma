<?php

include '../includes/dbh.inc.php';
if(!isset($_POST['start_id'])){
	
	$outp = '';
	
	$res = mysqli_query($conn, "SELECT * FROM radio_messages ORDER BY message_id ASC LIMIT 50");
	while($row = $res->fetch_assoc()){
		$date = preg_split('/ /', $row["message_time"]);
		$date = preg_split('/-/', $date[0])[2] . '/' . preg_split('/-/', $date[0])[1] . '/' . preg_split('/-/', $date[0])[0] . ' ' . $date[1];
		$date = str_replace('00:00:00', '', $date);
	
		$outp .= $row['message_id'] . '|' . base64_encode($date) . '|' . $row['message_name'] . '|' . $row['message_text'] . ',';
	}
	if($outp != '') $outp = mb_substr($outp, 0, -1);
	echo $outp;
	exit();
}
else {
	if(!is_numeric($_POST['start_id'])){
		echo 'bad';
		exit();
	}
	$id = (int)intval($_POST['start_id']);
	
	$outp = '';
	$res = mysqli_query($conn, "SELECT * FROM radio_messages WHERE message_id>$id ORDER BY message_id ASC");
	while($row = $res->fetch_assoc()){
		$date = preg_split('/ /', $row["message_time"]);
		$date = preg_split('/-/', $date[0])[2] . '/' . preg_split('/-/', $date[0])[1] . '/' . preg_split('/-/', $date[0])[0] . ' ' . $date[1];
		$date = str_replace('00:00:00', '', $date);
	
		$outp .= $row['message_id'] . '|' . base64_encode($date) . '|' . $row['message_name'] . '|' . $row['message_text'] . ',';
	}
	if($outp != '') $outp = mb_substr($outp, 0, -1);
	echo $outp;
	exit();
}