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

include_once '../../includes/extrasLoader.inc.php';
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../favicon.ico" />
    <title><?= $siteName; ?> | Ψηφοφορίες</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/homepage/polls.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
    <?= LoadMathJax(); ?>

    <script src="../../scripts/getmessages.js?v=<?= $pubFileVer; ?>"></script>
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">
		<p class="title">Ψηφοφορίες</p>
		<div class="posts-container">
			<?php if($_SESSION['type'] == 'TEACHER') echo '<a href="./newpoll.php" class="new-post-button">Νέα Ψηφοφορία<img src="../../resources/new.png"/></a>'; ?>
			<div class="posts-content">
					<?php
					include_once '../../includes/enc.inc.php';

					if($_SESSION['type'] == 'TEACHER'){
						$res = mysqli_query($conn, "SELECT * FROM user_polls WHERE poll_by='$username' ORDER BY poll_date DESC");
						if($res->num_rows < 1) echo '<p style="text-align:center;width:100%">Δεν υπάρχουν ψηφοφορίες</p>';
						else while($row = $res->fetch_assoc()) {
							$id = (int)$row["poll_id"];

							$text = decrypt($row['poll_text']);
							$text = str_replace('<br>', " \\n ", $text);
							$text = htmlspecialchars($text);
							$text = formatText($text);
							$text = str_replace('\\n', '<br>', $text);

							$date = preg_split('/ /', $row["poll_date"]);
							$date = preg_split('/-/', $date[0])[2] . '/' . preg_split('/-/', $date[0])[1] . '/' . preg_split('/-/', $date[0])[0] . ' ' . $date[1];
							$date = str_replace('00:00:00', '', $date);

							$author = $_SESSION['user_name'];
							
							$classes = base64_decode($row['poll_shown']);
							$classes = explode(',', trim($classes));
							$clsname = [];
							foreach($classes as $cl){
								$cl = (int)$cl;							
								$ress = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$cl");
								if($ress->num_rows > 0) $clsname[] = htmlentities($ress->fetch_assoc()['class_name']);
							}
							
							$options = base64_decode($row['poll_options']);
							$options = json_decode($options, JSON_UNESCAPED_UNICODE);
							for($i=0; $i<count($options);$i++)
								$options[$i] = htmlentities(base64_decode($options[$i]));
							
							$opt = implode('</i>, <i>', $options);							

							echo '<div class="post">
								<div class="post-date">' . $date . '</div>
								<div class="post-user">' . $author . '</div>
								<div class="post-line"></div>
								<div class="post-text">' . $text . '</div>
								<div class="post-line"></div>
								<div class="post-text">Εμφανίζεται για τις τάξεις: ' . implode(', ', $clsname) . '<br>Επιλογές: <i>' . $opt . '</i></div>
								<div class="post-text"><a target="_blank" href="./pollans.php?id=' . $id . '">Απαντήσεις</a>&nbsp;&nbsp;<a href="./editpoll.php?id=' . $id . '">Επεξεργασία<img src="../../resources/edit-icon.png"/></a></div>
							</div>';
						}
					}
					elseif($_SESSION['type'] == 'ADMIN'){
						$res = mysqli_query($conn, "SELECT * FROM user_polls ORDER BY poll_date DESC");
						if($res->num_rows < 1) echo '<p style="text-align:center;width:100%">Δεν υπάρχουν ψηφοφορίες</p>';
						else while($row = $res->fetch_assoc()) {
							$id = (int)$row["poll_id"];

							$text = decrypt($row['poll_text']);
							$text = str_replace('<br>', " \\n ", $text);
							$text = htmlspecialchars($text);
							$text = formatText($text);
							$text = str_replace('\\n', '<br>', $text);

							$date = preg_split('/ /', $row["poll_date"]);
							$date = preg_split('/-/', $date[0])[2] . '/' . preg_split('/-/', $date[0])[1] . '/' . preg_split('/-/', $date[0])[0] . ' ' . $date[1];
							$date = str_replace('00:00:00', '', $date);

							$author = htmlentities($row['poll_by']);
							
							$classes = base64_decode($row['poll_shown']);
							$classes = explode(',', trim($classes));
							$clsname = [];
							foreach($classes as $cl){
								$cl = (int)$cl;							
								$ress = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$cl");
								if($ress->num_rows > 0) $clsname[] = htmlentities($ress->fetch_assoc()['class_name']);
							}
							
							$options = base64_decode($row['poll_options']);
							$options = json_decode($options, JSON_UNESCAPED_UNICODE);
							for($i=0; $i<count($options);$i++)
								$options[$i] = htmlentities(base64_decode($options[$i]));
							
							$opt = implode('</i>, <i>', $options);							

							echo '<div class="post">
								<div class="post-date">' . $date . '</div>
								<div class="post-user">' . $author . '</div>
								<div class="post-line"></div>
								<div class="post-text">' . $text . '</div>
								<div class="post-line"></div>
								<div class="post-text">Εμφανίζεται για τις τάξεις: ' . implode(', ', $clsname) . '<br>Επιλογές: <i>' . $opt . '</i></div>
								<div class="post-text"><a target="_blank" href="./pollans.php?id=' . $id . '">Απαντήσεις</a></div>
							</div>';
						}
					}

					?>
			</div>
		</div>
    </div>
    <div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>