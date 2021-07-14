<?php session_start();
include_once '../includes/config.php';
date_default_timezone_set('Europe/Athens');

if(!isset($_SESSION['type']))
{
    if(isset($_COOKIE["autologin"]))
    {
        header("Location: ../includes/autologin.inc.php?r=battleship");
        exit();
    }
    else
    {
        header("Location: ../login.php?r=battleship");
        exit();
    }
}
elseif($_SESSION['type'] === 'ADMIN'){
	header("Location: ../");
	exit();
}

$secret = substr(md5($_SESSION['user_username']), 0, 32);  //must be 32 char length
$iv = substr(md5($secret), 0, 16);
$name = openssl_encrypt(base64_encode($_SESSION['user_name']), "AES-256-CBC", $secret, 0, $iv);
$battleshipVersion = '1.5'; //this and the server's version MUST match

include_once '../includes/extrasLoader.inc.php';
?>

<!DOCTYPE html>
<meta charset="utf-8" />
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="shortcut icon" href="../favicon.ico" />
    <title><?= $siteName; ?> | ΝΑΥΜΑΧΙΑ</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="index.css?v=<?= $pubFileVer; ?>" type="text/css">
	<link rel="stylesheet" href="../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
	<?= LoadBackground(__FILE__); ?>
	<!--<script src="../scripts/getmessages.js?v=<?= $pubFileVer; ?>"></script>-->
</head>

<body>
<div id="container">
	<div id="header"><?= LoadTopNav(__FILE__); ?></div>
	<div id="body">
	<p style="width: 100%; font-family: 'Noto Sans'; font-size: 30px; text-align: center;margin-top: 10px; margin-bottom: -5px">Ναυμαχία</p>
		<div id="game-cont">
			<p class="timeleft"></p>

			<div class="grid-cont">
				<div class="own-grid-hider"></div>
				<div id="own-grid" class="grid"></div>
				<p class="online-now"></p>
			</div>

			<div class="grid-cont">
				<div id="messages-cont">
					<p class="status-title">Σύνδεση...</p>
					<a class="gotohome" href="../">Αρχική Σελίδα</a>
					<div class="placed-ships">
						<p class="ship-name">Αεροπλανοφόρα: </p>
						<p class="ship-cnt carrier">0/1</p><br>
						<p class="ship-name">Θωρηκτά: </p>
						<p class="ship-cnt battleship">0/2</p><br>
						<p class="ship-name">Υποβρύχια: </p>
						<p class="ship-cnt submarine">0/3</p><br>
						<p class="ship-name">Περιπολικά: </p>
						<p class="ship-cnt patrol">0/4</p><br>
						<button class="cleargridbutton" onclick="clearGrid();">Εκκαθάριση</button>
					</div>
					<div class="control-buttons">
						<input type="checkbox" name="sendname" checked="checked" /><p>&nbsp;Εμφάνιση ονόματος στον αντίπαλο</p><br>
						<input type="checkbox" name="savegrid" checked="checked" /><p>&nbsp;Αποθήκευση πλοίων</p><br>
						<button class="enterqueuebutton" onclick="enterQueue();">Εύρεση Αντιπάλου</button>
						<div class="room-buttons">
							<button class="createroombutton" onclick="roomButtonAction();">Νέο Δωμάτιο</button>
							<input type="text" class="inputroomcode" placeholder="Δωμάτιο"/>
						</div>
					</div>
					<button class="exitqueuebutton" onclick="exitQueue();">Ακύρωση</button>
					<button class="gotostartbutton" onclick="goToStart();">Επόμενο</button>
				</div>

				<div id="opp-grid" class="grid"></div>
				<p class="opponent-name"></p>
			</div>
			<div class="stats">
				<div class="own-stats"></div>
				<table class="leaderboard"></table>
				<p style="font-family: 'Noto Sans'; font-size: 12px; color: #525252; margin-top: 10px;">*Η κατάταξη του κάθε ατόμου στο πίνακα υπολογίζεται από τη σχέση:<br>Μονάδες = Νίκες x %Νίκης</p>
			</div>
			
			<script src="./bs-logic.js?v=<?= $pubFileVer; ?>"></script>
			<?php /*Note: In "example.com/battleshipws", the 'battleshipws' part is actally a reverse-proxy pointing to the nodejs server! */ ?>
			<script src="http<?= empty($_SERVER['HTTPS'])?'':'s' ?>://<?= $_SERVER['HTTP_HOST']; ?>/battleshipws/socket.io/socket.io.js"></script>
			<script>
				const serverloc = 'http<?= empty($_SERVER['HTTPS'])?'':'s' ?>://<?= $_SERVER['HTTP_HOST']; ?>/';
				const authuname = '<?= $_SESSION['user_username']; ?>'
				const authcred = '<?= md5($_SESSION['user_id'] . $_SESSION['user_username'] . date("Y-n-j") . $battleshipVersion); ?>';
				const authencname = '<?= $name ?>';
			</script>
			<script src="./socket-manager.js?v=<?= $pubFileVer; ?>"></script>
		</div>
	</div>
	<div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>