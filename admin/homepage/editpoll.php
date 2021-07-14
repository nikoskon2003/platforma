<?php session_start(); 

if(!isset($_SESSION['type'])){
    include '../../error.php';
    exit();
}
if(!isset($_GET['id'])){
    include '../../error.php';
    exit();
}
if(!is_numeric($_GET['id'])){
    include '../../error.php';
    exit();
}

include '../../includes/config.php';
include '../../includes/dbh.inc.php';
$username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
if($_SESSION['type'] !== 'ADMIN' && $_SESSION['type'] !== 'TEACHER'){
	include '../../error.php';
	exit();
}

$pollId = (int)$_GET['id'];
$res = mysqli_query($conn, "SELECT * FROM user_polls WHERE poll_id=$pollId");
if($res->num_rows < 1){
    header("Location: ./polls.php");
    exit();
}
$pollData = $res->fetch_assoc();

if($_SESSION['type'] !== 'ADMIN' && $pollData['poll_by'] !== $username){
    header("Location: ./polls.php");
    exit();
}

date_default_timezone_set('Europe/Athens');
$now = date('d/m/Y H:i:s', time());


include_once '../../includes/extrasLoader.inc.php';
include_once '../../includes/enc.inc.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../favicon.ico" />
    <title><?= $siteName; ?> | Επεξεργασία Ψηφοφορίας</title>
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

            <div class="title">Επεξεργασία Ψηφοφορίας</div>
            <div id="box-container">
                <form id="desktop-form" action="../../includes/admin/homepage/editpoll.inc.php" method="POST" onsubmit="return validateForm();">
                    <div class="template-post">
                        <div class="template-post-date"><?= $now; ?></div>
                        <div class="template-post-user"><?= $_SESSION['user_name']; ?></div>
                        <div class="template-post-line"></div>
                        <div class="template-post-text"><textarea id="text" form="desktop-form" name="text" placeholder="Κείμενο (Υποχρεωτικό)"><?= htmlentities(decrypt($pollData['poll_text'])); ?></textarea></div>
                        <div class="template-post-line"></div>
						<div class="template-post-options-container">
							<div id="anss"><?php
							$options = base64_decode($pollData['poll_options']);
							$options = json_decode($options, JSON_UNESCAPED_UNICODE);
							
							foreach($options as $opt){
								$opt = htmlentities(base64_decode($opt));
								echo '<div class="answ"><input type="text" class="opt-text" placeholder="Κείμενο" value="' . $opt . '"></div>';
							}							
							?></div>
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
						
						$gcl = base64_decode($pollData['poll_shown']);
						$gcl = explode(',', $gcl);

						for($i = 0; $i <= 14; $i++){
							if(in_array($i, $classes)){
								$res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$i");
								$name = htmlentities($res->fetch_assoc()['class_name']);
								
								$chx = '';
								if(in_array($i,$gcl)) $chx = 'checked="checked"';								
								echo '<label class="class">
									<input type="checkbox" name="classes[]" value="' . $i . '" ' . $chx . '>    
									<p>' . $name . '</p>
								</label>';
							}
						}
						?>
						
						</div>
                        <div class="bottom-buttons">
							<input type="hidden" name="options" value=""/>
							<input type="hidden" name="id" value="<?= $pollId; ?>"/>
                            <button type="submit" name="submit" value="submit" class="submit-button">Υποβολή</button>
                            <a href="./polls.php" class="cancel-button">Άκυρο</a>
                        </div>
                    </div>
                </form>
                <br>
                <form action="../../includes/admin/homepage/deletepoll.inc.php" method="POST" onsubmit="if(!confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε τη ψηφοφορία;'))return false;document.getElementById('action-hider').style.display = 'block';" style="text-align: center;padding-bottom:5px;margin-top: 10px;">
                    <input type="hidden" name="id" value="<?= $pollId; ?>" />
                    <button type="submit" name="delete" value="delete" class="cancel-button">Διαγραφή</button>
                </form>
            </div>

            <script>		
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