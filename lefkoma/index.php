<?php session_start();
include_once '../includes/config.php';

if(!isset($_SESSION['type'])){
    include '../error.php';
    exit();
}
elseif($_SESSION['type'] !== 'STUDENT'){
    include '../error.php';
    exit();
}elseif((int)$_SESSION['user_class'] < 9){ //9 = Γ1
	include '../error.php';
    exit();
}
include '../includes/dbh.inc.php';
include '../includes/enc.inc.php';
$myusername = mysqli_real_escape_string($conn, $_SESSION['user_username']);
$commentedOn;
$res = mysqli_query($conn, "SELECT * FROM lefkoma_comments WHERE comm_from='$myusername'");
while($row = $res->fetch_assoc()) $commentedOn[$row['comm_to']] = base64_decode($row['comm_text']);

include '../includes/extrasLoader.inc.php';
?>
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../favicon.ico" />
    <title><?= $siteName; ?> | Λεύκωμα</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="./index.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>

    <script src="../scripts/getmessages.js?v=<?= $pubFileVer; ?>"></script>
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">
        <div class="desktop">
            <div class="student-list-cont">
				<p class="notice"><span style="color:orange">*Πορτοκαλί:</span> Έχουν γραφεί σχόλια για το άτομο.<br><span style="color:lime">*Πράσινο:</span> Έχετε γράψει εσείς σχόλιο για το άτομο.</p><br>
				<?php

				$cres = mysqli_query($conn, "SELECT * FROM classes WHERE class_id>=9");
				while($crow = $cres->fetch_assoc()){
					echo '<p class="class-name">' . htmlentities($crow['class_name']) . '</p><div class="student-name-holder"><table>
					<tr><th>Ονοματεπώνυμο</th><th>Σχόλιό σας</th><th><img class="edit-icon" src="../resources/edit-icon.png"></th></tr>';
					
					$cid = (int)$crow['class_id'];
					$res = mysqli_query($conn, "SELECT user_name,user_username FROM users WHERE user_class='$cid'");
					while($row = $res->fetch_assoc()){
						$col = '';
						
						$ousername = mysqli_real_escape_string($conn, $row['user_username']); 
						$asns = mysqli_query($conn, "SELECT * FROM lefkoma_comments WHERE comm_to='$ousername' LIMIT 1");
						$name = htmlentities(decrypt($row['user_name']));
						$txt = '';
						if($asns->num_rows > 0) $col = 'style="background-color: orange"';
						if(isset($commentedOn[$ousername])){
							$txt = htmlentities($commentedOn[$ousername]);
							$col = 'style="background-color: lime"';
						}
						echo '<tr '.$col.'><td>'.$name.'</td><td>'.$txt.'</td><td><a href="./edit.php?u='.urlencode($ousername).'"><img class="go-icon" src="../resources/up.png"></a></td></tr>';
					}
						
					echo '</table></div>';
				}
				?>
			</div>
        </div>

        <div class="mobile">
			<div class="student-list-cont">
				<p class="notice"><span style="color:orange">*Πορτοκαλί:</span> Έχουν γραφεί σχόλια για το άτομο.<br><span style="color:lime">*Πράσινο:</span> Έχετε γράψει εσείς σχόλιο για το άτομο.</p><br>
				<?php
				$cres = mysqli_query($conn, "SELECT * FROM classes WHERE class_id>=9");
				while($crow = $cres->fetch_assoc()){
					echo '<p class="class-name">' . htmlentities($crow['class_name']) . '</p><div class="student-name-holder"><table>
					<tr><th>Ονοματεπώνυμο</th><th>Σχόλιό σας</th><th><img class="edit-icon" src="../resources/edit-icon.png"></th></tr>';
					
					$cid = (int)$crow['class_id'];
					$res = mysqli_query($conn, "SELECT user_name,user_username FROM users WHERE user_class='$cid'");
					while($row = $res->fetch_assoc()){
						$col = '';
						
						$ousername = mysqli_real_escape_string($conn, $row['user_username']); 
						$asns = mysqli_query($conn, "SELECT * FROM lefkoma_comments WHERE comm_to='$ousername' LIMIT 1");
						$name = htmlentities(decrypt($row['user_name']));
						$txt = '';
						if($asns->num_rows > 0) $col = 'style="background-color: orange"';
						if(isset($commentedOn[$ousername])){
							$txt = htmlentities($commentedOn[$ousername]);
							$col = 'style="background-color: lime"';
						}
						echo '<tr '.$col.'><td>'.$name.'</td><td>'.$txt.'</td><td><a href="./edit.php?u='.urlencode($ousername).'"><img class="go-icon" src="../resources/up.png"></a></td></tr>';
					}
						
					echo '</table></div>';
				}
				?>
			</div>
		</div>
    </div>
  
    <div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>