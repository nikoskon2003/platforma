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
include '../includes/dbh.inc.php';
include '../includes/enc.inc.php';
echo "<style>table, th, td {border: 1px solid black; border-collapse: collapse;} th, td {padding: 5px;} tr:nth-child(even){background-color:#e0e0e0;}</style>";

$namecache;

echo "<table>";

$res = mysqli_query($conn, "SELECT * FROM users WHERE user_class >= 9 ORDER BY user_class ASC");
while($row = $res->fetch_assoc()){
	$username = mysqli_real_escape_string($conn, $row['user_username']);
	if(!isset($namecache[$username])) $namecache[$username] = decrypt($row['user_name']);
	echo "<tr><td>" . $namecache[$username] . "</td><td></td><td></td></tr>";
	
	$resb = mysqli_query($conn, "SELECT * FROM lefkoma_comments WHERE comm_to='$username'");
	while($rowb = $resb->fetch_assoc()){
		$fromun = mysqli_real_escape_string($conn, $rowb["comm_from"]);
		if(!isset($namecache[$fromun])){
			$nenc = mysqli_query($conn, "SELECT user_name FROM users WHERE user_username='$fromun' LIMIT 1")->fetch_assoc()["user_name"];
			$namecache[$fromun] = decrypt($nenc);
		}
		echo "<tr><td>&nbsp;</td><td>" . htmlentities(base64_decode($rowb["comm_text"])) . "</td><td>" . $namecache[$fromun] . "</td></tr>";
	}
}

echo "</table><br><br>";

/*$res = mysqli_query($conn,"SELECT * FROM lefkoma_comments");
if($res->num_rows > 0){
	echo "<table>";
	while($row = $res->fetch_assoc())
		echo "<tr><td>" . $row['comm_from'] . "</td><td>" . $row['comm_to'] . "</td><td>" . htmlentities(base64_decode($row["comm_text"])) . "</td></tr>";
	echo "</table><br><br>";
}*/
