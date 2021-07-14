<?php session_start();

if(!isset($_SESSION['type'])){
    include '../../error.php';
    exit();
}

include_once '../../includes/config.php';
include '../../includes/dbh.inc.php';
$username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
if($_SESSION['type'] !== 'ADMIN' && $_SESSION['type'] !== 'TEACHER'){
	include '../../error.php';
	exit();
}

date_default_timezone_set('Europe/Athens');
$now = date('d/m/Y H:i:s', time());

include_once '../../includes/extrasLoader.inc.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../favicon.ico" />
    <title><?= $siteName; ?> | Νέα Ψηφοφορία</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/homepage/newpoll.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">

    <?= LoadBackground(__FILE__); ?>
    <script src="../../scripts/getmessages.js?v=<?= $pubFileVer; ?>"></script>
</head>

<body>
<div id="container">
	<div id="header"><?= LoadTopNav(__FILE__); ?></div>
	<div id="body">
        <div class="home">

            <div class="title">Νέα Ψηφοφορία</div>
            <div id="box-container">
                <form id="desktop-form" action="../../includes/admin/homepage/newpoll.inc.php" method="POST" onsubmit="return validateForm();">
                    <div class="template-post">
                        <div class="template-post-date"><?= $now; ?></div>
                        <div class="template-post-user"><?= $_SESSION['user_name']; ?></div>
                        <div class="template-post-line"></div>
                        <div class="template-post-text"><textarea id="text" form="desktop-form" name="text" placeholder="Κείμενο (Υποχρεωτικό)"></textarea></div>
                        <div class="template-post-line"></div>
                        <div class="template-post-options-container">
                            <div class="option-add-button" onclick="addOption()">Προσθήκη Επιλογής<img src="../../resources/new.png" /></div>
							<div id="anss"></div>
                        </div>
                    </div>
                    <div class="end-parent">
						<div class="available-classes">
						<?php
						$classes = [];
						$res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='subject-teacher'");
						while($row = $res->fetch_assoc()){
							$subj = (int)$row['link_used_id'];
							
							$resb = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id='$subj' AND subject_class IS NOT NULL");
							if($resb->num_rows > 0){
								$cl = (int)$resb->fetch_assoc()['subject_class'];
								$classes[] = $cl;
							}
						}
						$res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='class-writer' AND link_user='$username'");
						while($row = $res->fetch_assoc()){
							$classes[] = (int)$row['link_used_id'];
						}

						for($i = 0; $i <= 14; $i++){
							if(in_array($i, $classes)){
								$res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$i");
								$name = htmlentities($res->fetch_assoc()['class_name']);
								echo '<label class="class">
									<input type="checkbox" name="classes[]" value="' . $i . '">    
									<p>' . $name . '</p>
								</label>';
							}
						}
						?>
						
						</div>
                        <div class="bottom-buttons">
							<input type="hidden" name="options" value=""/>
                            <button type="submit" name="submit" value="submit" class="submit-button">Υποβολή</button>
                            <a href="./polls.php" class="cancel-button">Άκυρο</a>
                        </div>
                    </div>
                </form>
            </div>

            <script>
				function addOption(){
					let anss = document.getElementById("anss");
					let el = document.createElement('div');
					el.classList.add('answ');
					el.innerHTML = '<img src="../../resources/delete.png" onclick="deleteOption(this)"><input type="text" class="opt-text" placeholder="Κείμενο"/>';
					anss.insertBefore(el, null);
				}
				function deleteOption(elm){
					elm.parentNode.parentNode.removeChild(elm.parentNode);
				}
			
                function validateForm(){
                    let txt = document.getElementById('text');
					if(txt.value.trim() == ''){
						alert('Το κείμενο δεν μπορεί να είναι κενό!');
						return false;
					}
					txt.value = txt.value.trim().replace(new RegExp('\r?\n','g'), '<br>');
					
					let anss = document.getElementById("anss");
					if(anss.childElementCount < 1){
						alert('Θα πρέπει να υπάρχει τουλάχιστον μια επιλογή');
						return false;
					}
					
					let options = [];
					let f = false;
					anss.querySelectorAll('input').forEach(el => {
						if(f)return;
						txt = el.value.trim();
						if(txt == ''){
							alert('Το κείμενο επιλογής δεν μπορεί να είναι κενό!');
							f = true;
							return;
						}
						options.push(txt);
					});
					if(f) return false;
					document.querySelector('input[name="options"]').value = JSON.stringify(options);
					return true;
                }
            </script>
        </div>
    </div>
	<div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>