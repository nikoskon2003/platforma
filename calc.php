<?php
session_start();

if(!isset($_SESSION['type']) && isset($_COOKIE["autologin"])) header("Location: ./includes/autologin.inc.php?r=calc.php");

include_once './includes/config.php';
include './includes/extrasLoader.inc.php';

//anyone who has the link can access this page!

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="favicon.ico" />
    <title><?= $siteName; ?> | Υπολογισμός Μορίων</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="styles/login.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
    <script src="../scripts/getmessages.js?v=<?= $pubFileVer; ?>"></script>
</head>

<body>
<div id="container">
	<div id="header"><?= LoadTopNav(__FILE__); ?></div>
	<div id="body">
        <div class="home">
            <br>
            <div class="box center">
                <center>
                <p>Υπολογισμός Μορίων</p>
                <br>
                <div class="login-container" style="font-family: 'Noto Sans'">
                    <div id="direction">
						<input type="radio" name="dir" value="0" id="r-0" checked="checked"><label for="r-0">Θετική</label>&nbsp;
						<input type="radio" name="dir" value="1" id="r-1"><label for="r-1">Θεωρητική</label><br>
						<input type="radio" name="dir" value="2" id="r-2"><label for="r-2">Υγείας</label>&nbsp;
						<input type="radio" name="dir" value="3" id="r-3"><label for="r-3">Οικονομικά</label>
					</div><br>
					<div id="marks" style="text-align: left;padding:10px">
						<input type="number" step="0.5" min="0" max="100" id="m-0" style="width: 50px">/100&nbsp;&nbsp;<label for="m-0" id="dn-0">Μαθηματικά</label><br>
						<input type="number" step="0.5" min="0" max="100" id="m-1" style="width: 50px">/100&nbsp;&nbsp;<label for="m-1" id="dn-1">Φυσική</label><br>
						<input type="number" step="0.5" min="0" max="100" id="m-2" style="width: 50px">/100&nbsp;&nbsp;<label for="m-2" id="dn-2">Χημεία</label><br>
						<input type="number" step="0.5" min="0" max="100" id="m-3" style="width: 50px">/100&nbsp;&nbsp;<label for="m-3" id="dn-3">Έκθεση</label>
					</div>
					<?php
					$st = "-"; //put a funny message here, or whatever
					echo '<p id="m-output">'.$st.'</p>';
					?>
					<script>
						document.getElementById('r-0').addEventListener('click', e => {
							document.getElementById("dn-0").innerHTML = "Μαθηματικά";
							document.getElementById("dn-1").innerHTML = "Φυσική";
							document.getElementById("dn-2").innerHTML = "Χημεία";
						});
						document.getElementById('r-1').addEventListener('click', e => {
							document.getElementById("dn-0").innerHTML = "Αρχαία";
							document.getElementById("dn-1").innerHTML = "Ιστορία";
							document.getElementById("dn-2").innerHTML = "Κοινωνιολογία";
						});
						document.getElementById('r-2').addEventListener('click', e => {
							document.getElementById("dn-0").innerHTML = "Βιολογία";
							document.getElementById("dn-1").innerHTML = "Χημεία";
							document.getElementById("dn-2").innerHTML = "Φυσική";
						});
						document.getElementById('r-3').addEventListener('click', e => {
							document.getElementById("dn-0").innerHTML = "Μαθηματικά";
							document.getElementById("dn-1").innerHTML = "ΑΟΘ";
							document.getElementById("dn-2").innerHTML = "ΑΕΠΠ";
						});

						document.getElementById('m-0').addEventListener('input', e => {calcMarks();});
						document.getElementById('m-1').addEventListener('input', e => {calcMarks();});
						document.getElementById('m-2').addEventListener('input', e => {calcMarks();});
						document.getElementById('m-3').addEventListener('input', e => {calcMarks();});

						function calcMarks(){
							let a = document.getElementById('m-0').value;
							let b = document.getElementById('m-1').value;
							let c = document.getElementById('m-2').value;
							let d = document.getElementById('m-3').value;

							if(a.trim() == '' || b.trim() == '' || c.trim() == '' || d.trim() == ''){
								document.getElementById('m-output').innerHTML = '<?= $st; ?>';
								return;
							}
							if(isNaN(a) || isNaN(b) || isNaN(c) || isNaN(d)) {
								document.getElementById('m-output').innerHTML = '<?= $st; ?>';
								return;
							}
							
							a = Math.min(100, Math.max(0, parseFloat(a)))/5.0;
							b = Math.min(100, Math.max(0, parseFloat(b)))/5.0;
							c = Math.min(100, Math.max(0, parseFloat(c)))/5.0;
							d = Math.min(100, Math.max(0, parseFloat(d)))/5.0;
							
							let f = Math.round(((a+b+c+d)*2 + 1.3*a + 0.7*b)*100);
							document.getElementById('m-output').innerHTML = f;
						}
					</script>
                </div>
                </center>
            </div>
        </div>
	</div>
	<div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>
