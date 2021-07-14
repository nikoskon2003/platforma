<?php
session_start();
include_once '../includes/config.php';
include_once '../includes/dbh.inc.php';
include '../includes/extrasLoader.inc.php';

//turned out https had problems... :(
function isSecure() {
  return
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || $_SERVER['SERVER_PORT'] == 443;
}
if(isSecure()){
	header("Location: http://" . $siteDomain . "/radio");
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
    <link rel="stylesheet" href="index.css?v=<?= $pubFileVer; ?>" type="text/css">
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
				</div>
				<div class="program-par">
					<p class="cont-title">Πρόγραμμα</p>
					<div class="program-cont">
						<label class="broadcast">
							<p class="broadcast-title">00:00 - 00:00</p>
							<input type="checkbox" class="bc-tgl"/>
							<div class="broadcast-data">
								<p class="broadcast-time">Παρουσίαση: names removed</p>
							</div>
						</label>
						<label class="broadcast">
							<p class="broadcast-title">00:00 - 00:00</p>
							<input type="checkbox" class="bc-tgl"/>
							<div class="broadcast-data">
								<p class="broadcast-desc">Τίτλος: names removed</p>
								<p class="broadcast-time">Παρουσίαση: names removed</p>
							</div>
						</label>
					</div>
				</div>
			<div class="chat-par">
				<?php
				if(isset($_SESSION['type'])) echo '
					<div class="text-input">
						<input type="text" class="text-area" id="text-inp" placeholder="Μήνυμα" />
						<button class="send-button" onclick="sendMessage()">Αποστολή</button>
					</div>';
				?>
				<div class="messages-cont" id="msg-par"></div>
			</div>
			
			<div class="dir-msg-cont" style="background-color: white;width: min(400px, 85%);margin: 0 auto;padding-bottom: 10px;">
				<p style="font-family: 'Noto Sans';font-size: 20px;">Μήνυμα προς τους παρουσιαστές</p>
				<textarea id="dir-txt" style="width: 367px; max-width: 90%; min-width: 90%; margin: 0px; height: 98px; min-height: 65px; padding: 2px" placeholder="Μήνυμα..."></textarea>
				<button class="send-button" onclick="sendDir();">Αποστολή</button>
			</div>
		</div>
		<script language="javascript" type="text/javascript">
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
		</script>
		<script language="javascript" type="text/javascript">
			let sending = false;		
			function sendMessage(){
				if(sending) return;
				
				let txtelm = document.getElementById('text-inp');
				let text = txtelm.value;
				txtelm.value = '';
				text = text.trim();
				
				if(text == '' || text == null) return;
				
				sending = true;
				
				var data = new FormData();
				data.append('text', text);
				var xhr = new XMLHttpRequest();
				xhr.open('POST', './sendmessage.php', true);
				xhr.onload = function(e) {
					sending = false;
				}
				xhr.send(data);
			}
			function sendDir(){
				if(sending) return;
				
				let txtelm = document.getElementById('dir-txt');
				let text = txtelm.value;
				txtelm.value = '';
				text = text.trim();
				txtelm.placeholder = 'Μήνυμα...';
				
				if(text == '' || text == null) return;
				
				sending = true;
				
				var data = new FormData();
				data.append('text', text);
				var xhr = new XMLHttpRequest();
				xhr.open('POST', './senddir.php', true);
				xhr.onload = function(e) {
					sending = false;
					txtelm.placeholder = 'Επιτυχής αποστολή μηνύματος';
				}
				xhr.send(data);
			}
			
			let lastmsgid = 0;
			function initMessages(){
				var xhr = new XMLHttpRequest();
				xhr.open('POST', './getmessages.php', true);
				xhr.onload = function(e) {
					if(this.status == 200)
						addMessages(e.currentTarget.responseText);
						
					setInterval(getLastMessages, 7500);
				}
				xhr.send();
			}
			function getLastMessages(){
				var data = new FormData();
				data.append('start_id', lastmsgid);
				var xhr = new XMLHttpRequest();
				xhr.open('POST', './getmessages.php', true);
				xhr.onload = function(e) {
					if(this.status == 200)
						addMessages(e.currentTarget.responseText);
				}
				xhr.send(data);
			}
			function addMessages(dat){
				dat = dat.trim();
				if(dat == '') return;
				
				let par = document.getElementById('msg-par');
				let messages = dat.split(',');
				messages.forEach(msg => {
					let parts = msg.split('|');
					let id = parseInt(parts[0]);
					let date = escapeHtml(b64Decode(parts[1]));
					let name = escapeHtml(b64Decode(parts[2]));
					let text = escapeHtml(b64Decode(parts[3]));
					if(id > lastmsgid) lastmsgid = id;
					else return;
					
					let el = document.createElement("div");
					el.id = 'msg-' + id;
					el.classList.add('message');
					el.innerHTML = '<p class="time-sent">' + date + '</p><p class="name-sent">' + name + '</p><p class="text-sent">' + text + '</p>';
					par.insertBefore(el, par.firstChild);
				});
			}
			
			function b64Decode(str) {
				return decodeURIComponent(atob(str).split('').map(function(c) {
					return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
				}).join(''));
			}
			function escapeHtml(unsafe) {
				return unsafe
				.replace(/&/g, "&amp;")
				.replace(/</g, "&lt;")
				.replace(/>/g, "&gt;")
				.replace(/"/g, "&quot;")
				.replace(/'/g, "&#039;");
			}
			setTimeout(function(){ initMessages(); }, 1000);
		</script>
	</div>
	<div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>
