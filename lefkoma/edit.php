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

if(!isset($_GET['u'])){
	include '../error.php';
    exit();
}

include '../includes/dbh.inc.php';
include '../includes/enc.inc.php';

$ousername = mysqli_real_escape_string($conn, $_GET['u']);

$res = mysqli_query($conn, "SELECT user_name,user_class FROM users WHERE user_username='$ousername' AND user_type=0");
if($res->num_rows <= 0){
	include '../error.php';
    exit();
}
$row = $res->fetch_assoc();
if((int)$row['user_class'] < 9){
	include '../error.php';
    exit();
}
$oname = decrypt($row['user_name']);

$myusername = mysqli_real_escape_string($conn, $_SESSION['user_username']);
$txt = '';
$res = mysqli_query($conn, "SELECT * FROM lefkoma_comments WHERE comm_from='$myusername' AND comm_to='$ousername' LIMIT 1");
if($res->num_rows > 0) $txt = htmlentities(base64_decode($res->fetch_assoc()['comm_text']));



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
				<a class="back-button" href="."><button>Πίσω</button></a><br>
				<p class="class-name"><?= $oname ?></p>
				<form action="./submit.php" method="POST" id="forma">
					<input type="hidden" name="u" value="<?= $ousername ?>"/>
					<?php if($myusername == $ousername): ?>
					<textarea maxlength="150" placeholder="Easter egg!" disabled>Στον εαυτό σου καλέ;</textarea>
					<?php else: ?>
					<textarea form="forma" name="text" maxlength="150" placeholder="Κείμενο"><?= $txt; ?></textarea><br>
					<button type="subbmit" name="submit" value="submit">Ενημέρωση Σχολίου</button>
					<?php endif; ?>
				</form>
			</div>
        </div>

        <div class="mobile">
			<div class="student-list-cont" style="background-color:white">
				<a class="back-button" href="."><button>Πίσω</button></a><br>
				<p class="class-name"><?= $oname ?></p>
				<form action="./submit.php" method="POST" id="formam">
					<input type="hidden" name="u" value="<?= $ousername ?>"/>
					<?php if($myusername == $ousername): ?>
					<textarea maxlength="150" placeholder="Easter egg!" disabled>Στον εαυτό σου καλέ;</textarea>
					<?php else: ?>
					<textarea form="formam" name="text" maxlength="150" placeholder="Κείμενο"><?= $txt; ?></textarea><br>
					<button type="subbmit" name="submit" value="submit">Ενημέρωση Σχολίου</button>
					<?php endif; ?>
				</form>
			</div>
		</div>
    </div>
  
    <div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>