<?php session_start();
include_once 'includes/config.php';

if($_SERVER['SERVER_NAME'] === $siteDomain)
{
	$retVal = true;
	if (isset($_SERVER['HTTPS'])) $retVal = ($_SERVER['HTTPS'] !== 'off');
	else $retVal = ($_SERVER['SERVER_PORT'] == 443);
	if(!$retVal) header("Location: https://" . $siteDomain ."/");
}

if(!isset($_SESSION['type']) && isset($_COOKIE["autologin"])) header("Location: ./includes/autologin.inc.php");

$monthNames = ["Ιανουάριος","Φεβρουάριος","Μάρτιος","Απρίλιος","Μάιος","Ιούνιος","Ιούλιος","Αύγουστος","Σεπτέμβριος","Οκτώβριος","Νοέμβριος","Δεκέμβριος"];

include_once 'includes/enc.inc.php';
include 'includes/dbh.inc.php';
include_once 'includes/extrasLoader.inc.php';
if(isset($_SESSION['type']))
    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);

$edat = [];

if(isset($_SESSION['type'])) {
    if($_SESSION['type'] == 'STUDENT'){
        if(isset($_SESSION['user_class'])){
            $class = (int)$_SESSION['user_class'];
            $res = mysqli_query($conn, "SELECT * FROM calendar_events WHERE event_class=$class AND event_subject IS NULL");
            if($res->num_rows > 0)
            while($row = $res->fetch_assoc()){
                $d = $row["event_date"];
                if(!in_array($d, $edat)) $edat[] = $d;
            }

            $res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_class=$class");
            if($res->num_rows > 0)
            while($row = $res->fetch_assoc()){
                $id = (int)$row["subject_id"];
                
                $r = mysqli_query($conn, "SELECT * FROM calendar_events WHERE event_subject=$id AND event_class IS NULL");
                if($r->num_rows > 0)
                while($rowb = $r->fetch_assoc()){
                    $d = $rowb["event_date"];
                    if(!in_array($d, $edat)) $edat[] = $d;
                }
            }
        }

        $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
        $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='subject-student'");
        if($res->num_rows > 0)
        while($row = $res->fetch_assoc()){
            $id = (int)$row["link_used_id"];
            
            $r = mysqli_query($conn, "SELECT * FROM calendar_events WHERE event_subject=$id AND event_class IS NULL");
            if($r->num_rows > 0)
            while($rowb = $r->fetch_assoc()){
                $d = $rowb["event_date"];
                if(!in_array($d, $edat)) $edat[] = $d;
            }
        }
    }
    elseif($_SESSION['type'] == 'TEACHER'){
        $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
        $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='subject-teacher'");
        if($res->num_rows > 0)
        while($row = $res->fetch_assoc()){
            $id = (int)$row["link_used_id"];
            
            $r = mysqli_query($conn, "SELECT * FROM calendar_events WHERE event_subject=$id AND event_class IS NULL");
            if($r->num_rows > 0)
            while($rowb = $r->fetch_assoc()){
                $d = $rowb["event_date"];
                if(!in_array($d, $edat)) $edat[] = $d;
            }
        }

        $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='class-writer'");
        if($res->num_rows > 0)
        while($row = $res->fetch_assoc()){
            $id = (int)$row["link_used_id"];
            
            $r = mysqli_query($conn, "SELECT * FROM calendar_events WHERE event_class=$id AND event_subject IS NULL");
            if($r->num_rows > 0)
            while($rowb = $r->fetch_assoc()){
                $d = $rowb["event_date"];
                if(!in_array($d, $edat)) $edat[] = $d;
            }
        }
    }
    elseif($_SESSION['type'] !== 'ADMIN') {
        header("Location: ./includes/logout.inc.php");
        exit();
    }
}

$eventDat = json_encode($edat, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="favicon.ico" />
    <title><?= $siteName; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="styles/index.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">

    <?= LoadMathJax(); ?>

    <link rel="stylesheet" href="./resources/img-viewer/lib/view-bigimg.css?v=<?= $pubFileVer; ?>">
    <script src="./resources/img-viewer/lib/view-bigimg.js?v=<?= $pubFileVer; ?>"></script>

    <?php if(isset($_SESSION['type'])): ?>
        <script type="text/javascript" src="scripts/loadnotif.php?v=<?= $pubFileVer; ?>"></script>
    <?php endif ?>
    <script src="./scripts/getmessages.js?v=<?= $pubFileVer; ?>"></script>

    <?= LoadBackground(__FILE__); ?> 
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">

        <div class="desktop">
        <?php 

        if(!isset($_SESSION['type'])){
            echo '<div class="main-content">
            <div class="main-left-column">';
                $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_visibility=1 AND post_usage='homepage' ORDER BY post_date DESC");
                if($res->num_rows < 1) echo '<p style="text-align:center;width:100%">Δεν υπάρχουν ανακοινώσεις</p>';
                else while($row = $res->fetch_assoc())
                {
                    $id = (int)$row["post_id"];

                    $title = decrypt($row['post_title']);
                    $title = htmlentities($title);

                    $text = decrypt($row['post_text']);
                    $text = str_replace('<br>', " \\n ", $text);
                    $text = htmlentities($text);
                    $text = formatText($text);
                    $text = str_replace('\\n', '<br>', $text);

                    $date = preg_split('/ /', $row["post_date"]);
                    $date = preg_split('/-/', $date[0])[2] . '/' . preg_split('/-/', $date[0])[1] . '/' . preg_split('/-/', $date[0])[0] . ' ' . $date[1];
                    $date = str_replace('00:00:00', '', $date);

                    $author = mysqli_real_escape_string($conn, $row['post_author']);
                    $uname = $author;
                    if($author !== 'admin')
                    {
                        $sql = "SELECT user_name FROM users WHERE user_username='$author'";
                        $result = mysqli_query($conn, $sql);
                        if($result->num_rows > 0)
                            $author = decrypt($result->fetch_assoc()['user_name']);
                    }
                    else $author = '';

                    $files = explode(',', $row['post_files']);

                    $outfiles = '';
                    if(!empty($files) && $files[0] != '')
                    {
                        for($i = 0; $i < sizeof($files); $i++)
                        {
                            if(empty($files[$i])) continue;
                            $file = mysqli_real_escape_string($conn, $files[$i]);
                            $uppath = './file.php?id=' . $file;
                            $filename = '';

                            $result = mysqli_query($conn, "SELECT * FROM files WHERE file_uid='$file' AND file_owner='$uname'");
                            if($result->num_rows < 1) continue;
                            else $filename = $result->fetch_assoc()['file_name'];

                            $imgs = array('gif', 'jpg', 'jpeg', 'png', 'webp', 'svg', 'bmp');
                            
                            $safeName = htmlentities($filename);

                            $ext = explode('.', $filename);
                            $ext = end($ext);
                            $ext = mb_strtolower($ext);

                            if(in_array($ext, $imgs) && false)
                            {
                                //<a class="post-file" href="./file.php?id=a" target="_blank" title="filename"><img src="./resources/icons/empty.png"/><p>filename</p></a>
                                $outfiles .= '
                                    <a class="homepage-post-file image" title="' . $safeName . '" id="img' . $file . $id . '">
                                        <img src="' . $uppath . '" id="src' . $file . $id . '">
                                        <p>' . $safeName. '</p>
                                        <script>
                                            document.getElementById("img' . $file . $id . '").onclick = function (e) {
                                                viewer.show(document.getElementById("src' . $file  . $id . '").src);
                                                document.getElementById("header").style.display = "none";
                                            }
                                        </script>
                                    </a>
                                ';
                            }
                            else {
                                $fileIcon = iconFromExtension($filename);
                                $outfiles .= '<a class="homepage-post-file" href="./file.php?id=' . $file . '" target="_blank" title="' . $safeName . '"><img src="./resources/icons/' . $fileIcon . '.png"/><p>' . $safeName . '</p></a>';
                            }
                        }
                        echo '<div class="homepage-post">
                                <div class="homepage-post-title">' . $title . '</div>
                                <div class="homepage-post-date">' . $date . '</div>
                                <div class="homepage-post-user">' . $author . '</div>
                                <div class="homepage-post-line"></div>
                                <div class="homepage-post-text">' . $text . '</div>';
                                if($outfiles != '')echo '<div class="homepage-post-line"></div>
                                <div class="homepage-post-file-container">' . $outfiles . '</div>';
                            echo '</div>';
                    }
                    else
                    {
                        echo '<div class="homepage-post">
                                <div class="homepage-post-title">' . $title . '</div>
                                <div class="homepage-post-date">' . $date . '</div>
                                <div class="homepage-post-user">' . $author . '</div>
                                <div class="homepage-post-line"></div>
                                <div class="homepage-post-text">' . $text . '</div>
                            </div>';
                    }
                }
            echo '</div>';

            echo '<div class="right-containter">
                    <div class="other-right-container">
                        <div class="right-cont">
                        <form action="includes/login.inc.php" method="post" class="login">
                            <p>Σύνδεση</p>
                            <input type="text" placeholder="Ψευδώνυμο" name="username"/><br>
                            <input type="password" placeholder="Κωδικός" name="password"/><br>
                            <input type="checkbox" placeholder="autologin" name="autologin" id="autologin"/>
                            <label for="autologin" class="autologin-text">Να με θυμάσαι</label><br>
                            <button type="submit" name="submit">Σύνδεση</button>
                        </form>
                        </div>
                    </div>
                </div>';

            echo '</div>';
        }
        elseif($_SESSION['type'] == 'ADMIN'){
            echo '<div class="main-content">
            <div class="main-left-column">
                <a class="post-editor-button" href="./admin/homepage/">Επεξεργασία<img src="./resources/edit-icon.png" /></a>';
				
				echo '<a class="post-editor-button" href="./admin/homepage/polls.php">Ψηφοφορίες<img src="./resources/edit-icon.png" /></a>';
				
                $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_visibility>0 AND post_usage='homepage' ORDER BY post_date DESC");
                if($res->num_rows < 1) echo '<p style="text-align:center;width:100%">Δεν υπάρχουν ανακοινώσεις</p>';
                else while($row = $res->fetch_assoc())
                {
                    $id = (int)$row["post_id"];

                    $title = decrypt($row['post_title']);
                    $title = htmlentities($title);

                    $text = decrypt($row['post_text']);
                    $text = str_replace('<br>', " \\n ", $text);
                    $text = htmlentities($text);
                    $text = formatText($text);
                    $text = str_replace('\\n', '<br>', $text);

                    $date = preg_split('/ /', $row["post_date"]);
                    $date = preg_split('/-/', $date[0])[2] . '/' . preg_split('/-/', $date[0])[1] . '/' . preg_split('/-/', $date[0])[0] . ' ' . $date[1];
                    $date = str_replace('00:00:00', '', $date);

                    $author = mysqli_real_escape_string($conn, $row['post_author']);
                    $uname = $author;
                    if($author !== 'admin')
                    {
                        $sql = "SELECT user_name FROM users WHERE user_username='$author'";
                        $result = mysqli_query($conn, $sql);
                        if($result->num_rows > 0)
                            $author = decrypt($result->fetch_assoc()['user_name']);
                    }
                    else $author = '';

                    $visibility = $row['post_visibility'];
                    $col = 'hsl(120, 80%, 80%)';
                    if($visibility == 0) $col = '#FFC4C4';
                    elseif($visibility == 2) $col = 'yellow';

                    $files = explode(',', $row['post_files']);

                    $outfiles = '';
                    if(!empty($files) && $files[0] != '')
                    {
                        for($i = 0; $i < sizeof($files); $i++)
                        {
                            if(empty($files[$i])) continue;
                            $file = mysqli_real_escape_string($conn, $files[$i]);
                            $uppath = './file.php?id=' . $file;
                            $filename = '';

                            $result = mysqli_query($conn, "SELECT * FROM files WHERE file_uid='$file' AND file_owner='$uname'");
                            if($result->num_rows < 1) continue;
                            else $filename = $result->fetch_assoc()['file_name'];

                            $imgs = array('gif', 'jpg', 'jpeg', 'png', 'webp', 'svg', 'bmp');
                            
                            $safeName = htmlentities($filename);

                            $ext = explode('.', $filename);
                            $ext = end($ext);
                            $ext = mb_strtolower($ext);

                            if(in_array($ext, $imgs) && false)
                            {
                                //<a class="post-file" href="./file.php?id=a" target="_blank" title="filename"><img src="./resources/icons/empty.png"/><p>filename</p></a>
                                $outfiles .= '
                                    <a class="homepage-post-file image" title="' . $safeName . '" id="img' . $file . $id . '">
                                        <img src="' . $uppath . '" id="src' . $file . $id . '">
                                        <p>' . $safeName. '</p>
                                        <script>
                                            document.getElementById("img' . $file . $id . '").onclick = function (e) {
                                                viewer.show(document.getElementById("src' . $file  . $id . '").src);
                                                document.getElementById("header").style.display = "none";
                                            }
                                        </script>
                                    </a>
                                ';
                            }
                            else {
                                $fileIcon = iconFromExtension($filename);
                                $outfiles .= '<a class="homepage-post-file" href="./file.php?id=' . $file . '" target="_blank" title="' . $safeName . '"><img src="./resources/icons/' . $fileIcon . '.png"/><p>' . $safeName . '</p></a>';
                            }
                        }
                        echo '<div class="homepage-post">
                                <div class="homepage-post-title">' . $title . '</div>
                                <div class="homepage-post-date">' . $date . '</div>
                                <div class="homepage-post-user">' . $author . '</div>
                                <div class="homepage-post-line"></div>
                                <div class="homepage-post-text">' . $text . '</div>';
                                if($outfiles != '')echo '<div class="homepage-post-line"></div>
                                <div class="homepage-post-file-container">' . $outfiles . '</div>';
                            echo '</div>';
                    }
                    else
                    {
                        echo '<div class="homepage-post">
                                <div class="homepage-post-title">' . $title . '</div>
                                <div class="homepage-post-date">' . $date . '</div>
                                <div class="homepage-post-user">' . $author . '</div>
                                <div class="homepage-post-line"></div>
                                <div class="homepage-post-text">' . $text . '</div>
                            </div>';
                    }
                }
            echo '</div>';

            echo '<div class="right-containter">';
                    if($enableProgram){

                        $studentUrl = '.';
                        $teacherUrl = '.';
                        $reasonText = '';

                        $res = mysqli_query($conn, "SELECT * FROM options WHERE option_name='program-students'");
                        if($res->num_rows > 0) $studentUrl = $res->fetch_assoc()['option_value'];
                        $res = mysqli_query($conn, "SELECT * FROM options WHERE option_name='program-teachers'");
                        if($res->num_rows > 0) $teacherUrl = $res->fetch_assoc()['option_value'];
                        $res = mysqli_query($conn, "SELECT * FROM options WHERE option_name='program-text'");
                        if($res->num_rows > 0) $reasonText = decrypt($res->fetch_assoc()['option_value']);

                        $reasonText = str_replace('<br>', " \\n ", $reasonText);
                        $reasonText = htmlentities($reasonText);
                        $reasonText = formatText($reasonText);
                        $reasonText = str_replace(" \\n ", "<br>", $reasonText);

                        echo '<div class="program-right">
                            <p class="title">Πρόγραμμα</p>
                            <a class="edit-program-button" href="./admin/program/">Επεξεργασία<img src="./resources/edit-icon.png"/></a>
                            <a class="program-itself" target="_blank" href="' . $studentUrl . '"><img src="./resources/program-icon.png"/><p>Άνοιγμα</p></a>
                            <a class="program-itself" target="_blank" href="' . $teacherUrl . '"><img src="./resources/program-icon.png"/><p>Άνοιγμα</p></a>
                            <p class="program-reason">' . $reasonText . '</p>
                        </div>
                        <br>';
                    }
                    
					echo '<div class="other-right-container">
                        <div class="right-cont">
							<div class="util-title">Υπολογισμός Μορίων</div>
							<div class="util-ins">
								<a href="./calc.php"><img src="resources/calculator-icon.png"/><div class="util-link">Είσοδος</div></a>
							</div>
						</div>
						
						<div class="right-cont">
							<div class="util-title">Αρχείο Εκπομπών</div>
							<div class="util-ins">
								<a href="./radio/"><img src="resources/radio/radio.gif"/><div class="util-link">Είσοδος</div></a>
							</div>
						</div>
						
                    </div>';

            echo '</div></div>';
        }
        elseif($_SESSION['type'] == 'STUDENT' || $_SESSION['type'] == 'TEACHER'){
            echo '<div class="main-content">
            <div class="main-left-column">';
                
                $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='homepage-author' AND link_user='$username'");
                if($res->num_rows > 0) echo '<a class="post-editor-button" href="./admin/homepage/">Επεξεργασία<img src="./resources/edit-icon.png" /></a>';
				
				if($_SESSION['type'] == 'TEACHER') echo '<a class="post-editor-button" href="./admin/homepage/polls.php">Ψηφοφορίες<img src="./resources/edit-icon.png" /></a>';
				
				if($_SESSION['type'] == 'STUDENT' && isset($_SESSION['user_class'])){
					$nowun = mysqli_real_escape_string($conn, $_SESSION['user_username']);
					$class = (int)$_SESSION['user_class'];
					$res = mysqli_query($conn, "SELECT * FROM user_polls WHERE poll_shown!='' ORDER BY poll_date DESC");
					while($row = $res->fetch_assoc()){
						
						$classes = base64_decode($row['poll_shown']);
						$classes = explode(',', trim($classes));
						for($i=0;$i<count($classes);$i++) $classes[$i] = (int)$classes[$i];
						
						if(in_array($class, $classes)){
							
							$id = (int)$row["poll_id"];

							$text = decrypt($row['poll_text']);
							$text = str_replace('<br>', " \\n ", $text);
							$text = htmlspecialchars($text);
							$text = formatText($text);
							$text = str_replace('\\n', '<br>', $text);

							$date = preg_split('/ /', $row["poll_date"]);
							$date = preg_split('/-/', $date[0])[2] . '/' . preg_split('/-/', $date[0])[1] . '/' . preg_split('/-/', $date[0])[0] . ' ' . $date[1];
							$date = str_replace('00:00:00', '', $date);

							$author = mysqli_real_escape_string($conn, $row['poll_by']);
							if($author !== 'admin')
							{
								$sql = "SELECT user_name FROM users WHERE user_username='$author'";
								$result = mysqli_query($conn, $sql);
								if($result->num_rows > 0)
									$author = decrypt($result->fetch_assoc()['user_name']);
							}
							else $author = '';
							
							$optxt = '';
							$val = -1;
							$ans = mysqli_query($conn, "SELECT ans_val FROM user_poll_ans WHERE ans_poll=$id AND ans_user='$nowun' LIMIT 1");
							if($ans->num_rows > 0) $val = $ans->fetch_assoc()['ans_val'];
							
							$options = base64_decode($row['poll_options']);
							$options = json_decode($options, JSON_UNESCAPED_UNICODE);
							for($i=0; $i<count($options);$i++){
								$c = '';
								if($i == $val) $c = 'style="background-color:green"';
								$optxt .= '<a class="poll-ans-button" href="./includes/subans.inc.php?id='.$id.'&a='.$i.'" '.$c.'>' . htmlentities(base64_decode($options[$i])) . '</a>';
							}
							
							echo '<div class="homepage-post" style="background-color: hsl(80, 80%, 80%)">
								<div class="homepage-post-date">' . $date . '</div>
								<div class="homepage-post-user">' . $author . '</div>
								<div class="homepage-post-line"></div>
								<div class="homepage-post-text">' . $text . '</div>
								<div class="homepage-post-line"></div>
								<div class="homepage-post-file-container">' . $optxt . '</div>
							</div>';
						}
					}
				}
				

                $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_visibility>0 AND post_usage='homepage' ORDER BY post_date DESC");
                if($res->num_rows < 1) echo '<p style="text-align:center;width:100%">Δεν υπάρχουν ανακοινώσεις</p>';
                else while($row = $res->fetch_assoc())
                {
                    $id = (int)$row["post_id"];

                    $title = decrypt($row['post_title']);
                    $title = htmlentities($title);

                    $text = decrypt($row['post_text']);
                    $text = str_replace('<br>', " \\n ", $text);
                    $text = htmlentities($text);
                    $text = formatText($text);
                    $text = str_replace('\\n', '<br>', $text);

                    $date = preg_split('/ /', $row["post_date"]);
                    $date = preg_split('/-/', $date[0])[2] . '/' . preg_split('/-/', $date[0])[1] . '/' . preg_split('/-/', $date[0])[0] . ' ' . $date[1];
                    $date = str_replace('00:00:00', '', $date);

                    $author = mysqli_real_escape_string($conn, $row['post_author']);
                    $uname = $author;
                    if($author !== 'admin')
                    {
                        $sql = "SELECT user_name FROM users WHERE user_username='$author'";
                        $result = mysqli_query($conn, $sql);
                        if($result->num_rows > 0)
                            $author = decrypt($result->fetch_assoc()['user_name']);
                    }
                    else $author = '';

                    $visibility = $row['post_visibility'];
                    $col = 'hsl(120, 80%, 80%)';
                    if($visibility == 0) $col = '#FFC4C4';
                    elseif($visibility == 2) $col = 'yellow';

                    $files = explode(',', $row['post_files']);

                    $outfiles = '';
                    if(!empty($files) && $files[0] != '')
                    {
                        for($i = 0; $i < sizeof($files); $i++)
                        {
                            if(empty($files[$i])) continue;
                            $file = mysqli_real_escape_string($conn, $files[$i]);
                            $uppath = './file.php?id=' . $file;
                            $filename = '';

                            $result = mysqli_query($conn, "SELECT * FROM files WHERE file_uid='$file' AND file_owner='$uname'");
                            if($result->num_rows < 1) continue;
                            else $filename = $result->fetch_assoc()['file_name'];

                            $imgs = array('gif', 'jpg', 'jpeg', 'png', 'webp', 'svg', 'bmp');
                            
                            $safeName = htmlentities($filename);

                            $ext = explode('.', $filename);
                            $ext = end($ext);
                            $ext = mb_strtolower($ext);

                            if(in_array($ext, $imgs) && false)
                            {
                                //<a class="post-file" href="./file.php?id=a" target="_blank" title="filename"><img src="./resources/icons/empty.png"/><p>filename</p></a>
                                $outfiles .= '
                                    <a class="homepage-post-file image" title="' . $safeName . '" id="img' . $file . $id . '">
                                        <img src="' . $uppath . '" id="src' . $file . $id . '">
                                        <p>' . $safeName. '</p>
                                        <script>
                                            document.getElementById("img' . $file . $id . '").onclick = function (e) {
                                                viewer.show(document.getElementById("src' . $file  . $id . '").src);
                                                document.getElementById("header").style.display = "none";
                                            }
                                        </script>
                                    </a>
                                ';
                            }
                            else {
                                $fileIcon = iconFromExtension($filename);
                                $outfiles .= '<a class="homepage-post-file" href="./file.php?id=' . $file . '" target="_blank" title="' . $safeName . '"><img src="./resources/icons/' . $fileIcon . '.png"/><p>' . $safeName . '</p></a>';
                            }
                        }
                        echo '<div class="homepage-post">
                                <div class="homepage-post-title">' . $title . '</div>
                                <div class="homepage-post-date">' . $date . '</div>
                                <div class="homepage-post-user">' . $author . '</div>
                                <div class="homepage-post-line"></div>
                                <div class="homepage-post-text">' . $text . '</div>';
                                if($outfiles != '')echo '<div class="homepage-post-line"></div>
                                <div class="homepage-post-file-container">' . $outfiles . '</div>';
                            echo '</div>';
                    }
                    else
                    {
                        echo '<div class="homepage-post">
                                <div class="homepage-post-title">' . $title . '</div>
                                <div class="homepage-post-date">' . $date . '</div>
                                <div class="homepage-post-user">' . $author . '</div>
                                <div class="homepage-post-line"></div>
                                <div class="homepage-post-text">' . $text . '</div>
                            </div>';
                    }
                }
            echo '</div>';

            echo '<div class="right-containter">';

            if($enableProgram){

                $reasonText = '';
                $res = mysqli_query($conn, "SELECT * FROM options WHERE option_name='program-text'");
                if($res->num_rows > 0) $reasonText = decrypt($res->fetch_assoc()['option_value']);
                $reasonText = str_replace('<br>', " \\n ", $reasonText);
                $reasonText = htmlentities($reasonText);
                $reasonText = formatText($reasonText);
                $reasonText = str_replace(" \\n ", "<br>", $reasonText);

                $url = '.';
                if($_SESSION['type'] == 'STUDENT')
                    $res = mysqli_query($conn, "SELECT * FROM options WHERE option_name='program-students'");
                elseif($_SESSION['type'] == 'TEACHER')
                    $res = mysqli_query($conn, "SELECT * FROM options WHERE option_name='program-teachers'");

                if($res->num_rows > 0) $url = $res->fetch_assoc()['option_value'];

                echo '<div class="program-right">
                    <p class="title">Πρόγραμμα</p>';

                $res = $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='program-editor' AND link_user='$username' LIMIT 1");
                if($res->num_rows > 0) echo '<a class="edit-program-button" href="./admin/program/">Επεξεργασία<img src="./resources/edit-icon.png"/></a>';
                
                echo '<a class="program-itself" target="_blank" href="' . $url . '"><img src="./resources/program-icon.png"/><p>Άνοιγμα</p></a>
                    <p class="program-reason">' . $reasonText . '</p>
                </div>
                <br>';
            }

            ?>  
                <div class="other-right-container">
                    <div class="right-cont">
                        <p class="title">Ημερολόγιο</p>
                        <div class="calendar">
                            <script>
                                const monthNames = ["Ιανουάριος","Φεβρουάριος","Μάρτιος","Απρίλιος","Μάιος","Ιούνιος","Ιούλιος","Αύγουστος","Σεπτέμβριος","Οκτώβριος","Νοέμβριος","Δεκέμβριος"];

                                let events = <?= $eventDat; ?>;

                                let phpDate = '<?php date_default_timezone_set('Europe/Athens'); echo date('Y/m/d H:m:s', time()); ?>';
                                let serverDate = new Date(phpDate);
                                let monthToday = serverDate.getMonth();
                                let today = serverDate.getDate();
                                let yearToday = serverDate.getFullYear();

                                let tmpyear = yearToday;
                                let tmpmonth = monthToday;

                                function showMonth(month, year){
                                    month = Math.min(Math.max(month, 0), 11);

                                    let disp = document.querySelectorAll('.calendar-top-month');
                                    disp.forEach(e => e.innerHTML = monthNames[month] + " " + year);

                                    let cal = document.querySelectorAll('.calendar__date');
                                    cal.forEach(e => {
                                        while(e.childElementCount > 7){
                                            e.lastElementChild.remove();
                                        }
                                        let dim = new Date(year, month + 1, 0).getDate();
                                        let firstDay = new Date(year, month, 1).getDay();
                                        firstDay = firstDay == 0 ? 7 : firstDay; 
                                        while(firstDay > 1){
                                            let el = document.createElement('div');
                                            el.innerHTML = '';
                                            el.classList.add('calendar__number');
                                            el.classList.add('empty');
                                            e.appendChild(el);
                                            firstDay--;
                                        }
                                        for(let i = 1; i <= dim; i++){
                                            let el = document.createElement('a');
                                            el.innerHTML = i;
                                            el.href = './calendar.php?d=' + i + '&m=' + (tmpmonth+1) + '&y=' + tmpyear;
                                            el.classList.add('calendar__number');
                                            if(year == yearToday && month == monthToday && i == today) el.classList.add('today');
                                            if(events.indexOf(year + '-' + (month+1) + '-' + i) >= 0) el.classList.add('hasevent');
                                            e.appendChild(el);
                                        }
                                    });
                                }

                                function prevMonth(){
                                    if(tmpmonth - 1 < 0){
                                        tmpmonth = 11;
                                        tmpyear--;
                                    }
                                    else tmpmonth--;

                                    showMonth(tmpmonth, tmpyear);
                                }
                                function nextMonth(){
                                    if(tmpmonth + 1 > 11){
                                        tmpmonth = 0;
                                        tmpyear++;
                                    }
                                    else tmpmonth++;

                                    showMonth(tmpmonth, tmpyear);
                                }

                            </script>

                            <?php
                                $year = date("Y", time());
                                $month = date("m", time());
                                $day = date("d", time());
                                $dim = date('t', strtotime($year . '-' . (int)$month . '-01'));
                                $firstDay = date('N', strtotime($year . '-' . (int)$month . '-01'));           
                            ?>
                            <div class="calendar-top">
                                <div class="calendar-top-left" title="Προηγούμενος Μήνας" onclick="prevMonth();"><b>&lt;</b></div>
                                <div class="calendar-top-month"><?= $monthNames[(int)$month-1] . ' ' . $year ?></div>
                                <div class="calendar-top-right" title="Επόμενος Μήνας" onclick="nextMonth();"><b>&gt;</b></div>
                            </div>
                            <div class="calendar__date">
                                <div class="calendar__day">Δε</div>
                                <div class="calendar__day">Τρ</div>
                                <div class="calendar__day">Τε</div>
                                <div class="calendar__day">Πε</div>
                                <div class="calendar__day">Πα</div>
                                <div class="calendar__day">Σα</div>
                                <div class="calendar__day">Κυ</div>
                                <?php
                                    while($firstDay > 1){
                                        echo '<div class="calendar__number empty"></div>';
                                        $firstDay--;
                                    }
                                    for($i = 1; $i <= $dim; $i++){
                                        if($i == $day){
                                            if(in_array($year . '-' . (int)($month) . '-' . $i, $edat))
                                            echo '<a class="calendar__number today hasevent" href="./calendar.php?d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                            else echo '<a class="calendar__number today" href="./calendar.php?d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                        }
                                        else {
                                            if(in_array($year . '-' . (int)($month) . '-' . $i, $edat))
                                            echo '<a class="calendar__number hasevent" href="./calendar.php?d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                            else echo '<a class="calendar__number" href="./calendar.php?d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                        }
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
					
					<div class="right-cont">
						<div class="util-title">Υπολογισμός Μορίων</div>
						<div class="util-ins">
							<a href="./calc.php"><img src="resources/calculator-icon.png"/><div class="util-link">Είσοδος</div></a>
						</div>
					</div>
					
					<div class="right-cont">
						<div class="util-title">Ναυμαχία</div>
						<div class="util-ins">
							<a href="./battleship/"><img style="margin-top:0;margin-bottom:10px;" src="resources/ship-icon.png"/><div class="util-link">Είσοδος</div></a>
						</div>
					</div>
					
					<div class="right-cont">
						<div class="util-title">Αρχείο Εκπομπών</div>
						<div class="util-ins">
							<a href="./radio/"><img src="resources/radio/radio.gif"/><div class="util-link">Είσοδος</div></a>
						</div>
					</div>
					
                </div>
            </div>
               
            <?php echo '</div>';
        }
        else {
            header("Location: ./includes/logout.inc.php");
            exit();
        }

        ?>
        </div>

        <div class="mobile">
            <?php

            if(!isset($_SESSION['type'])){
                echo '<div class="posts-cont-mb">';
                $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_visibility=1 AND post_usage='homepage' ORDER BY post_date DESC");
                if($res->num_rows < 1) echo '<p style="text-align:center;width:100%">Δεν υπάρχουν ανακοινώσεις</p>';
                else while($row = $res->fetch_assoc())
                {
                    $id = (int)$row["post_id"];

                    $title = decrypt($row['post_title']);
                    $title = htmlentities($title);

                    $text = decrypt($row['post_text']);
                    $text = str_replace('<br>', " \\n ", $text);
                    $text = htmlentities($text);
                    $text = formatText($text);
                    $text = str_replace('\\n', '<br>', $text);

                    $date = preg_split('/ /', $row["post_date"]);
                    $date = preg_split('/-/', $date[0])[2] . '/' . preg_split('/-/', $date[0])[1] . '/' . preg_split('/-/', $date[0])[0] . ' ' . $date[1];
                    $date = str_replace('00:00:00', '', $date);

                    $author = mysqli_real_escape_string($conn, $row['post_author']);
                    $uname = $author;
                    if($author !== 'admin')
                    {
                        $sql = "SELECT user_name FROM users WHERE user_username='$author'";
                        $result = mysqli_query($conn, $sql);
                        if($result->num_rows > 0)
                            $author = decrypt($result->fetch_assoc()['user_name']);
                    }
                    else $author = '';

                    $files = explode(',', $row['post_files']);

                    $outfiles = '';
                    if(!empty($files) && $files[0] != '')
                    {
                        for($i = 0; $i < sizeof($files); $i++)
                        {
                            if(empty($files[$i])) continue;
                            $file = mysqli_real_escape_string($conn, $files[$i]);
                            $uppath = './file.php?id=' . $file;
                            $filename = '';

                            $result = mysqli_query($conn, "SELECT * FROM files WHERE file_uid='$file' AND file_owner='$uname'");
                            if($result->num_rows < 1) continue;
                            else $filename = $result->fetch_assoc()['file_name'];

                            $imgs = array('gif', 'jpg', 'jpeg', 'png', 'webp', 'svg', 'bmp');
                            
                            $safeName = htmlentities($filename);

                            $ext = explode('.', $filename);
                            $ext = end($ext);
                            $ext = mb_strtolower($ext);

                            if(in_array($ext, $imgs) && false)
                            {
                                //<a class="post-file" href="./file.php?id=a" target="_blank" title="filename"><img src="./resources/icons/empty.png"/><p>filename</p></a>
                                $outfiles .= '
                                    <a class="homepage-post-file image" title="' . $safeName . '" id="img' . $file . $id . '">
                                        <img src="' . $uppath . '" id="src' . $file . $id . '">
                                        <p>' . $safeName. '</p>
                                        <script>
                                            document.getElementById("img' . $file . $id . '").onclick = function (e) {
                                                viewer.show(document.getElementById("src' . $file  . $id . '").src);
                                                document.getElementById("header").style.display = "none";
                                            }
                                        </script>
                                    </a>
                                ';
                            }
                            else {
                                $fileIcon = iconFromExtension($filename);
                                $outfiles .= '<a class="homepage-post-file" href="./file.php?id=' . $file . '" target="_blank" title="' . $safeName . '"><img src="./resources/icons/' . $fileIcon . '.png"/><p>' . $safeName . '</p></a>';
                            }
                        }
                        echo '<div class="homepage-post">
                                <div class="homepage-post-title">' . $title . '</div>
                                <div class="homepage-post-date">' . $date . '</div>
                                <div class="homepage-post-user">' . $author . '</div>
                                <div class="homepage-post-line"></div>
                                <div class="homepage-post-text">' . $text . '</div>';
                                if($outfiles != '')echo '<div class="homepage-post-line"></div>
                                <div class="homepage-post-file-container">' . $outfiles . '</div>';
                            echo '</div>';
                    }
                    else
                    {
                        echo '<div class="homepage-post">
                                <div class="homepage-post-title">' . $title . '</div>
                                <div class="homepage-post-date">' . $date . '</div>
                                <div class="homepage-post-user">' . $author . '</div>
                                <div class="homepage-post-line"></div>
                                <div class="homepage-post-text">' . $text . '</div>
                            </div>';
                    }
                }
                echo '</div>';
            }
            elseif($_SESSION['type'] == 'ADMIN'){

                echo '<div class="sub-navigation" id="sub-nav-bar">
                    <div class="to-calendar-bg" onclick="openCalendar();">Χρήσιμα</div>
                    <div class="to-posts-bg" onclick="openPosts();">Ανακοινώσεις</div>
                    <div class="to-program-bg" onclick="openProgram();">Πρόγραμμα</div>
                </div>';
				
				echo '<div id="calendar-mb">';
					echo '<div class="utilcont">
						<div class="util-title">Υπολογισμός Μορίων</div>
						<div class="util-ins">
							<a href="./calc.php"><img src="resources/calculator-icon.png"/><div class="util-link">Είσοδος</div></a>
						</div>
					</div>';
					
					echo '<div class="utilcont">
						<div class="util-title">Αρχείο Εκπομπών</div>
						<div class="util-ins">
							<a href="./radio/"><img src="resources/radio/radio.gif"/><div class="util-link">Είσοδος</div></a>
						</div>
					</div>';
				echo '</div>';
                
                echo '<div id="posts-cont" style="padding-top:20px">';
                echo '<br><br><a class="post-editor-button" href="./admin/homepage/">Επεξεργασία<img src="./resources/edit-icon.png" /></a>';
				
				echo '<a class="post-editor-button" href="./admin/homepage/polls.php">Ψηφοφορίες<img src="./resources/edit-icon.png" /></a>';
				
                $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_visibility>0 AND post_usage='homepage' ORDER BY post_date DESC");
                if($res->num_rows < 1) echo '<p style="text-align:center;width:100%">Δεν υπάρχουν ανακοινώσεις</p>';
                else while($row = $res->fetch_assoc())
                {
                    $id = (int)$row["post_id"];

                    $title = decrypt($row['post_title']);
                    $title = htmlentities($title);

                    $text = decrypt($row['post_text']);
                    $text = str_replace('<br>', " \\n ", $text);
                    $text = htmlentities($text);
                    $text = formatText($text);
                    $text = str_replace('\\n', '<br>', $text);

                    $date = preg_split('/ /', $row["post_date"]);
                    $date = preg_split('/-/', $date[0])[2] . '/' . preg_split('/-/', $date[0])[1] . '/' . preg_split('/-/', $date[0])[0] . ' ' . $date[1];
                    $date = str_replace('00:00:00', '', $date);

                    $author = mysqli_real_escape_string($conn, $row['post_author']);
                    $uname = $author;
                    if($author !== 'admin')
                    {
                        $sql = "SELECT user_name FROM users WHERE user_username='$author'";
                        $result = mysqli_query($conn, $sql);
                        if($result->num_rows > 0)
                            $author = decrypt($result->fetch_assoc()['user_name']);
                    }
                    else $author = '';

                    $files = explode(',', $row['post_files']);

                    $outfiles = '';
                    if(!empty($files) && $files[0] != '')
                    {
                        for($i = 0; $i < sizeof($files); $i++)
                        {
                            if(empty($files[$i])) continue;
                            $file = mysqli_real_escape_string($conn, $files[$i]);
                            $uppath = './file.php?id=' . $file;
                            $filename = '';

                            $result = mysqli_query($conn, "SELECT * FROM files WHERE file_uid='$file' AND file_owner='$uname'");
                            if($result->num_rows < 1) continue;
                            else $filename = $result->fetch_assoc()['file_name'];

                            $imgs = array('gif', 'jpg', 'jpeg', 'png', 'webp', 'svg', 'bmp');
                            
                            $safeName = htmlentities($filename);

                            $ext = explode('.', $filename);
                            $ext = end($ext);
                            $ext = mb_strtolower($ext);

                            if(in_array($ext, $imgs) && false)
                            {
                                $outfiles .= '
                                    <a class="homepage-post-file image" title="' . $safeName . '" id="img' . $file . $id . '">
                                        <img src="' . $uppath . '" id="src' . $file . $id . '">
                                        <p>' . $safeName. '</p>
                                        <script>
                                            document.getElementById("img' . $file . $id . '").onclick = function (e) {
                                                viewer.show(document.getElementById("src' . $file  . $id . '").src);
                                                document.getElementById("header").style.display = "none";
                                            }
                                        </script>
                                    </a>
                                ';
                            }
                            else {
                                $fileIcon = iconFromExtension($filename);
                                $outfiles .= '<a class="homepage-post-file" href="./file.php?id=' . $file . '" target="_blank" title="' . $safeName . '"><img src="./resources/icons/' . $fileIcon . '.png"/><p>' . $safeName . '</p></a>';
                            }
                        }
                        echo '<div class="homepage-post">
                                <div class="homepage-post-title">' . $title . '</div>
                                <div class="homepage-post-date">' . $date . '</div>
                                <div class="homepage-post-user">' . $author . '</div>
                                <div class="homepage-post-line"></div>
                                <div class="homepage-post-text">' . $text . '</div>';
                                if($outfiles != '')echo '<div class="homepage-post-line"></div>
                                <div class="homepage-post-file-container">' . $outfiles . '</div>';
                            echo '</div>';
                    }
                    else
                    {
                        echo '<div class="homepage-post">
                                <div class="homepage-post-title">' . $title . '</div>
                                <div class="homepage-post-date">' . $date . '</div>
                                <div class="homepage-post-user">' . $author . '</div>
                                <div class="homepage-post-line"></div>
                                <div class="homepage-post-text">' . $text . '</div>
                            </div>';
                    }
                }
                echo '</div>';

                if($enableProgram){
                    echo '<div id="program-cont" class="program-mb">';
                
                    $studentUrl = '.';
                    $teacherUrl = '.';
                    $reasonText = '';

                    $res = mysqli_query($conn, "SELECT * FROM options WHERE option_name='program-students'");
                    if($res->num_rows > 0) $studentUrl = $res->fetch_assoc()['option_value'];
                    $res = mysqli_query($conn, "SELECT * FROM options WHERE option_name='program-teachers'");
                    if($res->num_rows > 0) $teacherUrl = $res->fetch_assoc()['option_value'];
                    $res = mysqli_query($conn, "SELECT * FROM options WHERE option_name='program-text'");
                    if($res->num_rows > 0) $reasonText = decrypt($res->fetch_assoc()['option_value']);

                    $reasonText = str_replace('<br>', " \\n ", $reasonText);
                    $reasonText = htmlentities($reasonText);
                    $reasonText = formatText($reasonText);
                    $reasonText = str_replace(" \\n ", "<br>", $reasonText);

                    echo '<a class="edit-program-button" href="./admin/program/">Επεξεργασία<img src="./resources/edit-icon.png"/></a>
                        <a class="program-itself" target="_blank" href="' . $studentUrl . '"><img src="./resources/program-icon.png"/><p>Άνοιγμα</p></a>
                        <a class="program-itself" target="_blank" href="' . $teacherUrl . '"><img src="./resources/program-icon.png"/><p>Άνοιγμα</p></a>
                        <p class="program-reason">' . $reasonText . '</p>';
                
                    echo '</div>';
                }

                echo '<script>
                    let posts = document.getElementById("posts-cont");
                    let program = document.getElementById("program-cont");
                    let navbar = document.getElementById("sub-nav-bar");
                    let calendar = document.getElementById("calendar-mb");
        
                    function openPosts(){
                        posts.style.display = "block";
                        program.style.display = "none";
                        calendar.style.display = "none";
                        navbar.style.backgroundColor = "hsl(125, 85%, 64%)";
                    }
                    function openProgram(){
                        posts.style.display = "none";
                        program.style.display = "block";
                        calendar.style.display = "none";
                        navbar.style.backgroundColor = "hsl(0, 100%, 64%)";
                    }
                    function openCalendar(){
                        posts.style.display = "none";
                        program.style.display = "none";
                        calendar.style.display = "block";
                        navbar.style.backgroundColor = "hsl(200, 95%, 64%)";
                    }
                    if(navbar != null) openPosts();
                </script>';
            }
            else if($enableProgram){
                echo '<div class="sub-navigation" id="sub-nav-bar">
                    <div class="to-calendar-bg" onclick="openCalendar();">Χρήσιμα</div>
                    <div class="to-posts-bg" onclick="openPosts();">Ανακοινώσεις</div>
                    <div class="to-program-bg" onclick="openProgram();">Πρόγραμμα</div>
                </div>';

                echo '<div id="calendar-mb"><div class="program-cont">';
                
                $year = date("Y", time());
                $month = date("m", time());
                $day = date("d", time());
                $dim = date('t', strtotime($year . '-' . (int)$month . '-01'));
                $firstDay = date('N', strtotime($year . '-' . (int)$month . '-01'));
                ?>
                <div class="calendar-top">
                    <div class="calendar-top-left" title="Προηγούμενος Μήνας" onclick="prevMonth();"><b>&lt;</b></div>
                    <div class="calendar-top-month"><?= $monthNames[(int)$month-1] . ' ' . $year ?></div>
                    <div class="calendar-top-right" title="Επόμενος Μήνας" onclick="nextMonth();"><b>&gt;</b></div>
                </div>
                <div class="calendar__date">
                    <div class="calendar__day">Δε</div>
                    <div class="calendar__day">Τρ</div>
                    <div class="calendar__day">Τε</div>
                    <div class="calendar__day">Πε</div>
                    <div class="calendar__day">Πα</div>
                    <div class="calendar__day">Σα</div>
                    <div class="calendar__day">Κυ</div>
                    <?php
                        while($firstDay > 1){
                            echo '<div class="calendar__number empty"></div>';
                            $firstDay--;
                        }
                        for($i = 1; $i <= $dim; $i++){
                            if($i == $day){
                                if(in_array($year . '-' . (int)($month) . '-' . $i, $edat))
                                echo '<a class="calendar__number today hasevent" href="./calendar.php?d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                else echo '<a class="calendar__number today" href="./calendar.php?d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                            }
                            else {
                                if(in_array($year . '-' . (int)($month) . '-' . $i, $edat))
                                echo '<a class="calendar__number hasevent" href="./calendar.php?d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                else echo '<a class="calendar__number" href="./calendar.php?d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                            }
                        }
                    ?>
                </div>
                <?php
                echo '</div>';
				echo '<div class="utilcont">
					<div class="util-title">Υπολογισμός Μορίων</div>
					<div class="util-ins">
						<a href="./calc.php"><img src="resources/calculator-icon.png"/><div class="util-link">Είσοδος</div></a>
					</div>
				</div>';
				echo '<div class="utilcont">
					<div class="util-title">Ναυμαχία</div>
					<div class="util-ins">
						<a href="./battleship/"><img style="margin-top:0;margin-bottom:10px;" src="resources/ship-icon.png"/><div class="util-link">Είσοδος</div></a>
					</div>
				</div>';
				
				echo '<div class="utilcont">
					<div class="util-title">Αρχείο Εκπομπών</div>
					<div class="util-ins">
						<a href="./radio/"><img src="resources/radio/radio.gif"/><div class="util-link">Είσοδος</div></a>
					</div>
				</div>';
				echo '</div>';
                
                echo '<div id="posts-cont">';
                $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='homepage-author' AND link_user='$username'");
                if($res->num_rows > 0) echo '<a class="post-editor-button" href="./admin/homepage/">Επεξεργασία<img src="./resources/edit-icon.png" /></a>';
				
				if($_SESSION['type'] == 'TEACHER') echo '<a class="post-editor-button" href="./admin/homepage/polls.php">Ψηφοφορίες<img src="./resources/edit-icon.png" /></a>';
				
				if($_SESSION['type'] == 'STUDENT' && isset($_SESSION['user_class'])){
					$nowun = mysqli_real_escape_string($conn, $_SESSION['user_username']);
					$class = (int)$_SESSION['user_class'];
					$res = mysqli_query($conn, "SELECT * FROM user_polls WHERE poll_shown!='' ORDER BY poll_date DESC");
					while($row = $res->fetch_assoc()){
						
						$classes = base64_decode($row['poll_shown']);
						$classes = explode(',', trim($classes));
						for($i=0;$i<count($classes);$i++) $classes[$i] = (int)$classes[$i];
						
						if(in_array($class, $classes)){
							
							$id = (int)$row["poll_id"];

							$text = decrypt($row['poll_text']);
							$text = str_replace('<br>', " \\n ", $text);
							$text = htmlspecialchars($text);
							$text = formatText($text);
							$text = str_replace('\\n', '<br>', $text);

							$date = preg_split('/ /', $row["poll_date"]);
							$date = preg_split('/-/', $date[0])[2] . '/' . preg_split('/-/', $date[0])[1] . '/' . preg_split('/-/', $date[0])[0] . ' ' . $date[1];
							$date = str_replace('00:00:00', '', $date);

							$author = mysqli_real_escape_string($conn, $row['poll_by']);
							if($author !== 'admin')
							{
								$sql = "SELECT user_name FROM users WHERE user_username='$author'";
								$result = mysqli_query($conn, $sql);
								if($result->num_rows > 0)
									$author = decrypt($result->fetch_assoc()['user_name']);
							}
							else $author = '';
							
							$optxt = '';
							$val = -1;
							$ans = mysqli_query($conn, "SELECT ans_val FROM user_poll_ans WHERE ans_poll=$id AND ans_user='$nowun' LIMIT 1");
							if($ans->num_rows > 0) $val = $ans->fetch_assoc()['ans_val'];
							
							$options = base64_decode($row['poll_options']);
							$options = json_decode($options, JSON_UNESCAPED_UNICODE);
							for($i=0; $i<count($options);$i++){
								$c = '';
								if($i == $val) $c = 'style="background-color:green"';
								$optxt .= '<a class="poll-ans-button" href="./includes/subans.inc.php?id='.$id.'&a='.$i.'" '.$c.'>' . htmlentities(base64_decode($options[$i])) . '</a>';
							}
							
							echo '<div class="homepage-post" style="background-color: hsl(80, 80%, 80%)">
								<div class="homepage-post-date">' . $date . '</div>
								<div class="homepage-post-user">' . $author . '</div>
								<div class="homepage-post-line"></div>
								<div class="homepage-post-text">' . $text . '</div>
								<div class="homepage-post-line"></div>
								<div class="homepage-post-file-container">' . $optxt . '</div>
							</div>';
						}
					}
				}
				
                $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_visibility>0 AND post_usage='homepage' ORDER BY post_date DESC");
                if($res->num_rows < 1) echo '<p style="text-align:center;width:100%">Δεν υπάρχουν ανακοινώσεις</p>';
                else while($row = $res->fetch_assoc())
                {
                    $id = (int)$row["post_id"];

                    $title = decrypt($row['post_title']);
                    $title = htmlentities($title);

                    $text = decrypt($row['post_text']);
                    $text = str_replace('<br>', " \\n ", $text);
                    $text = htmlentities($text);
                    $text = formatText($text);
                    $text = str_replace('\\n', '<br>', $text);

                    $date = preg_split('/ /', $row["post_date"]);
                    $date = preg_split('/-/', $date[0])[2] . '/' . preg_split('/-/', $date[0])[1] . '/' . preg_split('/-/', $date[0])[0] . ' ' . $date[1];
                    $date = str_replace('00:00:00', '', $date);

                    $author = mysqli_real_escape_string($conn, $row['post_author']);
                    $uname = $author;
                    if($author !== 'admin')
                    {
                        $sql = "SELECT user_name FROM users WHERE user_username='$author'";
                        $result = mysqli_query($conn, $sql);
                        if($result->num_rows > 0)
                            $author = decrypt($result->fetch_assoc()['user_name']);
                    }
                    else $author = '';

                    $files = explode(',', $row['post_files']);

                    $outfiles = '';
                    if(!empty($files) && $files[0] != '')
                    {
                        for($i = 0; $i < sizeof($files); $i++)
                        {
                            if(empty($files[$i])) continue;
                            $file = mysqli_real_escape_string($conn, $files[$i]);
                            $uppath = './file.php?id=' . $file;
                            $filename = '';

                            $result = mysqli_query($conn, "SELECT * FROM files WHERE file_uid='$file' AND file_owner='$uname'");
                            if($result->num_rows < 1) continue;
                            else $filename = $result->fetch_assoc()['file_name'];

                            $imgs = array('gif', 'jpg', 'jpeg', 'png', 'webp', 'svg', 'bmp');
                            
                            $safeName = htmlentities($filename);

                            $ext = explode('.', $filename);
                            $ext = end($ext);
                            $ext = mb_strtolower($ext);

                            if(in_array($ext, $imgs) && false)
                            {
                                $outfiles .= '
                                    <a class="homepage-post-file image" title="' . $safeName . '" id="img' . $file . $id . '">
                                        <img src="' . $uppath . '" id="src' . $file . $id . '">
                                        <p>' . $safeName. '</p>
                                        <script>
                                            document.getElementById("img' . $file . $id . '").onclick = function (e) {
                                                viewer.show(document.getElementById("src' . $file  . $id . '").src);
                                                document.getElementById("header").style.display = "none";
                                            }
                                        </script>
                                    </a>
                                ';
                            }
                            else {
                                $fileIcon = iconFromExtension($filename);
                                $outfiles .= '<a class="homepage-post-file" href="./file.php?id=' . $file . '" target="_blank" title="' . $safeName . '"><img src="./resources/icons/' . $fileIcon . '.png"/><p>' . $safeName . '</p></a>';
                            }
                        }
                        echo '<div class="homepage-post">
                                <div class="homepage-post-title">' . $title . '</div>
                                <div class="homepage-post-date">' . $date . '</div>
                                <div class="homepage-post-user">' . $author . '</div>
                                <div class="homepage-post-line"></div>
                                <div class="homepage-post-text">' . $text . '</div>';
                                if($outfiles != '')echo '<div class="homepage-post-line"></div>
                                <div class="homepage-post-file-container">' . $outfiles . '</div>';
                            echo '</div>';
                    }
                    else
                    {
                        echo '<div class="homepage-post">
                                <div class="homepage-post-title">' . $title . '</div>
                                <div class="homepage-post-date">' . $date . '</div>
                                <div class="homepage-post-user">' . $author . '</div>
                                <div class="homepage-post-line"></div>
                                <div class="homepage-post-text">' . $text . '</div>
                            </div>';
                    }
                }
                echo '</div>';

                echo '<div id="program-cont" class="program-mb">';
            
                $url = '.';
                $reasonText = '';

                $type = strtolower($_SESSION['type']) . 's';

                $res = mysqli_query($conn, "SELECT * FROM options WHERE option_name='program-$type'");
                if($res->num_rows > 0) $url = $res->fetch_assoc()['option_value'];
                $res = mysqli_query($conn, "SELECT * FROM options WHERE option_name='program-text'");
                if($res->num_rows > 0) $reasonText = decrypt($res->fetch_assoc()['option_value']);

                $reasonText = str_replace('<br>', " \\n ", $reasonText);
                $reasonText = htmlentities($reasonText);
                $reasonText = formatText($reasonText);
                $reasonText = str_replace(" \\n ", "<br>", $reasonText);

                $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='program-editor' AND link_user='$username'");
                if($res->num_rows > 0) echo '<a class="edit-program-button" href="./admin/program/">Επεξεργασία<img src="./resources/edit-icon.png"/></a>';

                echo '<a class="program-itself" target="_blank" href="' . $url . '"><img src="./resources/program-icon.png"/><p>Άνοιγμα</p></a>
                    <p class="program-reason">' . $reasonText . '</p>';
            
                echo '</div>';
                

                echo '<script>
                    let posts = document.getElementById("posts-cont");
                    let program = document.getElementById("program-cont");
                    let navbar = document.getElementById("sub-nav-bar");
                    let calendar = document.getElementById("calendar-mb");
        
                    function openPosts(){
                        posts.style.display = "block";
                        program.style.display = "none";
                        calendar.style.display = "none";
                        navbar.style.backgroundColor = "hsl(125, 85%, 64%)";
                    }
                    function openProgram(){
                        posts.style.display = "none";
                        program.style.display = "block";
                        calendar.style.display = "none";
                        navbar.style.backgroundColor = "hsl(0, 100%, 64%)";
                    }
                    function openCalendar(){
                        posts.style.display = "none";
                        program.style.display = "none";
                        calendar.style.display = "block";
                        navbar.style.backgroundColor = "hsl(200, 95%, 64%)";
                    }
                    if(navbar != null) openPosts();
                </script>';
            }
            else {
                echo '<div class="sub-navigation" id="sub-nav-bar">
                    <div class="to-posts" onclick="openPosts();">Ανακοινώσεις</div>
                    <div class="to-program" onclick="openCalendar();">Χρήσιμα</div>
                </div>';
                
                echo '<div id="posts-cont">';
                $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='homepage-author' AND link_user='$username'");
                if($res->num_rows > 0) echo '<a class="post-editor-button" href="./admin/homepage/">Επεξεργασία<img src="./resources/edit-icon.png" /></a>';
				
				if($_SESSION['type'] == 'TEACHER') echo '<a class="post-editor-button" href="./admin/homepage/polls.php">Ψηφοφορίες<img src="./resources/edit-icon.png" /></a>';
				
				if($_SESSION['type'] == 'STUDENT' && isset($_SESSION['user_class'])){
					$nowun = mysqli_real_escape_string($conn, $_SESSION['user_username']);
					$class = (int)$_SESSION['user_class'];
					$res = mysqli_query($conn, "SELECT * FROM user_polls WHERE poll_shown!='' ORDER BY poll_date DESC");
					while($row = $res->fetch_assoc()){
						
						$classes = base64_decode($row['poll_shown']);
						$classes = explode(',', trim($classes));
						for($i=0;$i<count($classes);$i++) $classes[$i] = (int)$classes[$i];
						
						if(in_array($class, $classes)){
							
							$id = (int)$row["poll_id"];

							$text = decrypt($row['poll_text']);
							$text = str_replace('<br>', " \\n ", $text);
							$text = htmlspecialchars($text);
							$text = formatText($text);
							$text = str_replace('\\n', '<br>', $text);

							$date = preg_split('/ /', $row["poll_date"]);
							$date = preg_split('/-/', $date[0])[2] . '/' . preg_split('/-/', $date[0])[1] . '/' . preg_split('/-/', $date[0])[0] . ' ' . $date[1];
							$date = str_replace('00:00:00', '', $date);

							$author = mysqli_real_escape_string($conn, $row['poll_by']);
							if($author !== 'admin')
							{
								$sql = "SELECT user_name FROM users WHERE user_username='$author'";
								$result = mysqli_query($conn, $sql);
								if($result->num_rows > 0)
									$author = decrypt($result->fetch_assoc()['user_name']);
							}
							else $author = '';
							
							$optxt = '';
							$val = -1;
							$ans = mysqli_query($conn, "SELECT ans_val FROM user_poll_ans WHERE ans_poll=$id AND ans_user='$nowun' LIMIT 1");
							if($ans->num_rows > 0) $val = $ans->fetch_assoc()['ans_val'];
							
							$options = base64_decode($row['poll_options']);
							$options = json_decode($options, JSON_UNESCAPED_UNICODE);
							for($i=0; $i<count($options);$i++){
								$c = '';
								if($i == $val) $c = 'style="background-color:green"';
								$optxt .= '<a class="poll-ans-button" href="./includes/subans.inc.php?id='.$id.'&a='.$i.'" '.$c.'>' . htmlentities(base64_decode($options[$i])) . '</a>';
							}
							
							echo '<div class="homepage-post" style="background-color: hsl(80, 80%, 80%)">
								<div class="homepage-post-date">' . $date . '</div>
								<div class="homepage-post-user">' . $author . '</div>
								<div class="homepage-post-line"></div>
								<div class="homepage-post-text">' . $text . '</div>
								<div class="homepage-post-line"></div>
								<div class="homepage-post-file-container">' . $optxt . '</div>
							</div>';
						}
					}
				}
				
                $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_visibility>0 AND post_usage='homepage' ORDER BY post_date DESC");
                if($res->num_rows < 1) echo '<p style="text-align:center;width:100%">Δεν υπάρχουν ανακοινώσεις</p>';
                else while($row = $res->fetch_assoc())
                {
                    $id = (int)$row["post_id"];

                    $title = decrypt($row['post_title']);
                    $title = htmlentities($title);

                    $text = decrypt($row['post_text']);
                    $text = str_replace('<br>', " \\n ", $text);
                    $text = htmlentities($text);
                    $text = formatText($text);
                    $text = str_replace('\\n', '<br>', $text);

                    $date = preg_split('/ /', $row["post_date"]);
                    $date = preg_split('/-/', $date[0])[2] . '/' . preg_split('/-/', $date[0])[1] . '/' . preg_split('/-/', $date[0])[0] . ' ' . $date[1];
                    $date = str_replace('00:00:00', '', $date);

                    $author = mysqli_real_escape_string($conn, $row['post_author']);
                    $uname = $author;
                    if($author !== 'admin')
                    {
                        $sql = "SELECT user_name FROM users WHERE user_username='$author'";
                        $result = mysqli_query($conn, $sql);
                        if($result->num_rows > 0)
                            $author = decrypt($result->fetch_assoc()['user_name']);
                    }
                    else $author = '';

                    $files = explode(',', $row['post_files']);

                    $outfiles = '';
                    if(!empty($files) && $files[0] != '')
                    {
                        for($i = 0; $i < sizeof($files); $i++)
                        {
                            if(empty($files[$i])) continue;
                            $file = mysqli_real_escape_string($conn, $files[$i]);
                            $uppath = './file.php?id=' . $file;
                            $filename = '';

                            $result = mysqli_query($conn, "SELECT * FROM files WHERE file_uid='$file' AND file_owner='$uname'");
                            if($result->num_rows < 1) continue;
                            else $filename = $result->fetch_assoc()['file_name'];

                            $imgs = array('gif', 'jpg', 'jpeg', 'png', 'webp', 'svg', 'bmp');
                            
                            $safeName = htmlentities($filename);

                            $ext = explode('.', $filename);
                            $ext = end($ext);
                            $ext = mb_strtolower($ext);

                            if(in_array($ext, $imgs) && false)
                            {
                                $outfiles .= '
                                    <a class="homepage-post-file image" title="' . $safeName . '" id="img' . $file . $id . '">
                                        <img src="' . $uppath . '" id="src' . $file . $id . '">
                                        <p>' . $safeName. '</p>
                                        <script>
                                            document.getElementById("img' . $file . $id . '").onclick = function (e) {
                                                viewer.show(document.getElementById("src' . $file  . $id . '").src);
                                                document.getElementById("header").style.display = "none";
                                            }
                                        </script>
                                    </a>
                                ';
                            }
                            else {
                                $fileIcon = iconFromExtension($filename);
                                $outfiles .= '<a class="homepage-post-file" href="./file.php?id=' . $file . '" target="_blank" title="' . $safeName . '"><img src="./resources/icons/' . $fileIcon . '.png"/><p>' . $safeName . '</p></a>';
                            }
                        }
                        echo '<div class="homepage-post">
                                <div class="homepage-post-title">' . $title . '</div>
                                <div class="homepage-post-date">' . $date . '</div>
                                <div class="homepage-post-user">' . $author . '</div>
                                <div class="homepage-post-line"></div>
                                <div class="homepage-post-text">' . $text . '</div>';
                                if($outfiles != '')echo '<div class="homepage-post-line"></div>
                                <div class="homepage-post-file-container">' . $outfiles . '</div>';
                            echo '</div>';
                    }
                    else
                    {
                        echo '<div class="homepage-post">
                                <div class="homepage-post-title">' . $title . '</div>
                                <div class="homepage-post-date">' . $date . '</div>
                                <div class="homepage-post-user">' . $author . '</div>
                                <div class="homepage-post-line"></div>
                                <div class="homepage-post-text">' . $text . '</div>
                            </div>';
                    }
                }
                echo '</div>';

                echo '<div id="calendar-mb"><div class="program-cont">';
                
                $year = date("Y", time());
                $month = date("m", time());
                $day = date("d", time());
                $dim = date('t', strtotime($year . '-' . (int)$month . '-01'));
                $firstDay = date('N', strtotime($year . '-' . (int)$month . '-01'));
                ?>
                <div class="calendar-top">
                    <div class="calendar-top-left" title="Προηγούμενος Μήνας" onclick="prevMonth();"><b>&lt;</b></div>
                    <div class="calendar-top-month"><?= $monthNames[(int)$month-1] . ' ' . $year ?></div>
                    <div class="calendar-top-right" title="Επόμενος Μήνας" onclick="nextMonth();"><b>&gt;</b></div>
                </div>
                <div class="calendar__date">
                    <div class="calendar__day">Δε</div>
                    <div class="calendar__day">Τρ</div>
                    <div class="calendar__day">Τε</div>
                    <div class="calendar__day">Πε</div>
                    <div class="calendar__day">Πα</div>
                    <div class="calendar__day">Σα</div>
                    <div class="calendar__day">Κυ</div>
                    <?php
                        while($firstDay > 1){
                            echo '<div class="calendar__number empty"></div>';
                            $firstDay--;
                        }
                        for($i = 1; $i <= $dim; $i++){
                            if($i == $day){
                                if(in_array($year . '-' . (int)($month) . '-' . $i, $edat))
                                echo '<a class="calendar__number today hasevent" href="./calendar.php?d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                else echo '<a class="calendar__number today" href="./calendar.php?d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                            }
                            else {
                                if(in_array($year . '-' . (int)($month) . '-' . $i, $edat))
                                echo '<a class="calendar__number hasevent" href="./calendar.php?d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                else echo '<a class="calendar__number" href="./calendar.php?d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                            }
                        }
                    ?>
                </div>
                <?php
                echo '</div>';
				
				echo '<div class="utilcont">
					<div class="util-title">Υπολογισμός Μορίων</div>
					<div class="util-ins">
						<a href="./calc.php"><img src="resources/calculator-icon.png"/><div class="util-link">Είσοδος</div></a>
					</div>
				</div>';
				echo '<div class="utilcont">
					<div class="util-title">Ναυμαχία</div>
					<div class="util-ins">
						<a href="./battleship/"><img style="margin-top:0;margin-bottom:10px;" src="resources/ship-icon.png"/><div class="util-link">Είσοδος</div></a>
					</div>
				</div>';
				
				echo '<div class="utilcont">
					<div class="util-title">Αρχείο Εκπομπών</div>
					<div class="util-ins">
						<a href="./radio/"><img src="resources/radio/radio.gif"/><div class="util-link">Είσοδος</div></a>
					</div>
				</div>';
				
				echo '</div>';

                echo '<script>
                    let posts = document.getElementById("posts-cont");
                    let navbar = document.getElementById("sub-nav-bar");
                    let calendar = document.getElementById("calendar-mb");
        
                    function openPosts(){
                        posts.style.display = "block";
                        calendar.style.display = "none";
                        navbar.style.backgroundColor = "hsl(125, 85%, 64%)";
                    }
                    function openCalendar(){
                        posts.style.display = "none";
                        calendar.style.display = "block";
                        navbar.style.backgroundColor = "hsl(0, 100%, 64%)";
                    }
                    if(navbar != null) openPosts();
                </script>';
            }

            ?>
        </div>
		
		<?php
//The best feature of this whole project
/*echo '<a href="./nsfw/" target="_blank" class="l-radio-holder">
			<div class="l-radio-img">
				<img src="./nsfw/ditto.png" />
			</div>
			<style>
				.l-radio-holder{position:fixed;left:2px;bottom:-5px;z-index:99999;user-select:none;border-top-right-radius:6px;}
				.l-radio-img{width:150px;height:150px;text-align:center;display:inline-block;vertical-align:top;}
				.l-radio-img img{width:100%;height:100%;image-rendering:pixelated;image-rendering:crisp-edges;}
			</style>
		</a>';*/		?>

    </div>
    <script>
        let viewer = new ViewBigimg();
        document.querySelectorAll(".iv-close").forEach(el => el.onclick = function (e) {document.getElementById("header").style.display = "inline";});
    </script>

    <div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
