<?php
session_start();
include_once '../includes/config.php';
include_once '../includes/dbh.inc.php';
include '../includes/extrasLoader.inc.php';

if(!isset($_SESSION['type'])){
	include '../error.php';
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../favicon.ico" />
    <title><?= $siteName ?> Web Radio</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="index.css?v=<?= $pubFileVer; ?>a" type="text/css">
    <link rel="stylesheet" href="../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
</head>

<body>
<div id="container">
	<div id="header"><?= LoadTopNav(__FILE__); ?></div>
	<div id="body">
		<div class="web-radio-par">
			<div class="interface-par">
			<!--
				<div class="radio-player-par">
					<p class="cont-title">Ραδιόφωνο</p>
					
					<div class="l-radio-button">
						<img src="play.png" id="live-radio-play" onclick="ToggleRadio();" />
					</div>
					<div class="l-radio-volume">
						<p class="volume-title">Ένταση Ήχου</p>
						<input type="range" value="90" min="0" max="100" step="1" oninput="updateVolume(this.value)" onchange="updateVolume(this.value)"/>
						<p class="volume-title" id="volume">90%</p>
					</div>
					<button onclick="connectToStream()">Reload</button>
					<audio id="play-music"></audio>
				</div>-->
				
				<?php
				if(isset($_SESSION['type'])){
					echo '<div class="download-par">
							<p class="download-title">Λήψη εκπομπών</p>
							<div class="brd-par">
								<div class="brd">
									<p class="brd-time">Ώρα: 00:00 - 00:00</p>
									<p class="brd-pres">Παρουσίαση: names removed</p>
									<a href="./download.php?id=1" target="_blank" class="brd-download">Λήψη Εκπομπής 📥</a>
								</div>
							</div>
							<div class="brd-par">
								<div class="brd">
									<p class="brd-time">Ώρα: 00:00 - 00:00</p>
									<p class="brd-title">Τίτλος: names removed</p>
									<p class="brd-pres">Παρουσίαση: names removed</p>
									<a href="./download.php?id=2" target="_blank" class="brd-download">Λήψη Εκπομπής 📥</a>
								</div>
							</div>
						</div>';
				}
				?>
		</div>
		<!--<script language="javascript" type="text/javascript">
			const audio = document.getElementById('play-music'); audio.volume = 0.9;
			const playIcon = document.getElementById('live-radio-play');
			let act = false;
			
			function updateVolume(num){
				document.getElementById('volume').innerHTML = num + '%';
				audio.volume = num/100;
			}
			
			function ToggleRadio(){
				if (!audio.getAttribute("src"))
				{
					connectToStream();
					audio.muted = false;
					playIcon.src = "./pause.png";
				}
				else if(!audio.muted){
					audio.muted = true;
					playIcon.src = "./play.png";
				}
				else{
					audio.muted = false;
					audio.play();
					playIcon.src = "./pause.png";
				}
			}

			setInterval(function(){
				if(audio.paused && act){
					act = false;
					connectToStream();
					setTimeout(function(e) { act = true; }, 5000);
				}
			}, 2000);
			
			audio.onended = function(e){ connectToStream(); }
			
			function connectToStream(){
				console.log('reloading source');
				audio.setAttribute("src", "http<?= empty($_SERVER['HTTPS'])?'':'s'?>://<?= $_SERVER['HTTP_HOST']; ?>:<?= empty($_SERVER['HTTPS'])?'8000':'8443'?>/stream");
				audio.load();
				audio.play();
				audio.paused = false;
				act = true;
			}
		</script>-->
	</div>
	<div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>
