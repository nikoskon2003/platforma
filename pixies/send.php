<?php
$delayInSeconds = 30;

session_start();
if(isset($_POST['col']) && isset($_POST['x']) && isset($_POST['y']) && isset($_SESSION['user_username'])){
	if(!is_numeric($_POST['col']) || !is_numeric($_POST['x']) || !is_numeric($_POST['y'])){
		echo '-1';
		exit();
	}

	$conn = mysqli_connect('hostname', 'username', 'password', 'database');

	$found = false;
	$tim;
	$username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
	$res = mysqli_query($conn, "SELECT * FROM user_cooldowns WHERE user_username='$username' LIMIT 1");
	if($res->num_rows > 0){
		$found = true;
		$tim = $res->fetch_assoc()['cooldown_time'];

		if(time() - $delayInSeconds < $tim){
			echo $delayInSeconds - (time() - $tim);
			exit();
		}
	}

	$col = (int)$_POST['col'];
	$x = (int)$_POST['x'];
	$y = (int)$_POST['y'];

	if($col == -1 && $x == -1 && $y == -1){
		if($found){
			echo $delayInSeconds - (time() - $tim);
			exit();
		}
		else {
			echo '-1';
			exit();
		}
	}

	if($col < 0 || $col > 15){
		echo '-1';
		exit();
	}
	if($x < 0 || $x > 255 || $y < 0 || $y > 255){
		echo '-1';
		exit();
	}
	
	$img = @imagecreatefrompng('img.png');

	$r = [034, 255, 136, 228, 232, 255, 226, 131, 000, 000, 000, 146, 000, 161, 231, 230];
	$g = [034, 255, 136, 228, 000, 166, 062, 000, 000, 130, 229, 226, 192, 106, 150, 219];
	$b = [034, 255, 136, 228, 000, 209, 255, 130, 238, 202, 242, 052, 000, 062, 000, 000];

	$color = imagecolorallocate($img, $r[$col], $g[$col], $b[$col]);
	imagesetpixel($img, $x, $y, $color);
	imagepng($img, 'img.png');

	$tim = time() - 1;


	if($found)
		mysqli_query($conn, "UPDATE user_cooldowns SET cooldown_time='$tim' WHERE user_username='$username'");
	else
		mysqli_query($conn, "INSERT INTO user_cooldowns (user_username, cooldown_time) VALUES ('$username', '$tim')");

	echo $delayInSeconds;
	exit();
}
else {
	echo '-1';
	exit();
}