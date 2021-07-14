<?php session_start();
if(!isset($_SESSION['type']))
{
    if(isset($_COOKIE["autologin"]))
    {
        header("Location: ../includes/autologin.inc.php?r=pixies/");
        exit();
    }
    else
    {
        header("Location: ../login.php?r=pixies/");
        exit();
    }
}

include_once '../includes/extrasLoader.inc.php';
?>
<!DOCTYPE html>
<meta charset="utf-8" />
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="shortcut icon" href="icon.png" />
    <title>Pixies</title>
	<meta name="description" content="Pixies">
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="index.css?v=<?= $pubFileVer; ?>" type="text/css">
	<link rel="stylesheet" href="../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
    <script src="../scripts/getmessages.js?v=<?= $pubFileVer; ?>"></script>
</head>

<body>
<div id="container">
	<div id="header"><?= LoadTopNav(__FILE__); ?></div>
	
	<div id="body">
		<a id="help" href="./help.php">?</a>
		<div id="place-container">
			<p id="title">Pixies</p>
			<img id="img" src="img.png" width="256" height="256" onclick="moveLens(event);" draggable="false"/>
			<div id="img-2" class="zoom-result" onclick="getSecImagePos(event);">
				<div id="selector"></div>
			</div>
			
			<div id="download"><a href="img.png" target="_blank" download="">Λήψη📥</a></div>

			<p id="col-title">Επιλογή Χρώματος</p>
			<div id="color-selector">
				<div class="col" style="background-color: #222222;" onclick="selectColor(0);"></div>
				<div class="col" style="background-color: #FFFFFF;" onclick="selectColor(1);"></div>
				<div class="col" style="background-color: #888888;" onclick="selectColor(2);"></div>
				<div class="col" style="background-color: #E4E4E4;" onclick="selectColor(3);"></div>
				<div class="col" style="background-color: #E80000;" onclick="selectColor(4);"></div>
				<div class="col" style="background-color: #FFA6D1;" onclick="selectColor(5);"></div>
				<div class="col" style="background-color: #E23EFF;" onclick="selectColor(6);"></div>
				<div class="col" style="background-color: #830082;" onclick="selectColor(7);"></div>
				<div class="col" style="background-color: #0000EE;" onclick="selectColor(8);"></div>
				<div class="col" style="background-color: #0082CA;" onclick="selectColor(9);"></div>
				<div class="col" style="background-color: #00E5F2;" onclick="selectColor(10);"></div>
				<div class="col" style="background-color: #92E234;" onclick="selectColor(11);"></div>
				<div class="col" style="background-color: #00C000;" onclick="selectColor(12);"></div>
				<div class="col" style="background-color: #A16A3E;" onclick="selectColor(13);"></div>
				<div class="col" style="background-color: #E79600;" onclick="selectColor(14);"></div>
				<div class="col" style="background-color: #E6DB00;" onclick="selectColor(15);"></div>
			</div>
			<br>
			<button id="submitButton" onclick="submitColor();">Αποστολή</button>
		</div>

		<script src="index.js"></script>
		<script>fetchMessageCountdata();</script>
	</div>
	
	<div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>