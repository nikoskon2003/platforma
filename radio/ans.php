<?php
include '../includes/dbh.inc.php';

$res = mysqli_query($conn, "SELECT * FROM radio_dir ORDER BY dir_id DESC");

while($row = $res->fetch_assoc()){
	echo '<p>' . $row['dir_time'] . ' | ' . htmlentities(base64_decode($row['dir_text'])) . '</p>';
}