<?php  session_start();
include_once '../includes/config.php';

if(!isset($_GET['s'])){
    header("Location: ./");
    exit();
}
if(!is_numeric($_GET['s'])){
    header("Location: ./");
    exit();
}

include '../includes/enc.inc.php';
include '../includes/dbh.inc.php';
$subjId = (int)mysqli_real_escape_string($conn, $_GET['s']);

if(!isset($_SESSION['type']))
{
    if(isset($_COOKIE["autologin"]))
    {
        header("Location: ../includes/autologin.inc.php?r=class%2Fsubject.php%3Fs%3D$subjId");
        exit();
    }
    else
    {
        header("Location: ../login.php?r=class/subject.php?class%2Fsubject.php%3Fs%3D$subjId");
        exit();
    }
}

$res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$subjId LIMIT 1");
if($res->num_rows < 1){
    header("Location: ./");
    exit();
}
$row = $res->fetch_assoc();
$subjName = htmlentities(decrypt($row['subject_name']));
$subjClass = $row['subject_class'];
$subjClassName = "&lt;Καμία τάξη&gt;";
if($subjClass != null){
    $res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$subjClass LIMIT 1");
    if($res->num_rows > 0) $subjClassName = $res->fetch_assoc()['class_name'];
}

$username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
if($_SESSION['type'] == 'STUDENT'){
    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='subject-student' AND link_used_id=$subjId");
    if($res->num_rows < 1){
        if(!is_null($_SESSION['user_class'])){
            if($_SESSION['user_class'] != $subjClass){
                include '../error.php';
                exit();
            }
        }
        else {
            include '../error.php';
            exit();
        }
    }
}
elseif($_SESSION['type'] == 'TEACHER'){
    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='subject-teacher' AND link_used_id=$subjId");
    if($res->num_rows < 1){
        include '../error.php';
        exit();
    }
}

include_once '../includes/extrasLoader.inc.php';

$monthNames = ["Ιανουάριος","Φεβρουάριος","Μάρτιος","Απρίλιος","Μάιος","Ιούνιος","Ιούλιος","Αύγουστος","Σεπτέμβριος","Οκτώβριος","Νοέμβριος","Δεκέμβριος"];

$edat = [];
$res = mysqli_query($conn, "SELECT * FROM calendar_events WHERE event_subject=$subjId AND event_class IS NULL");
if($res->num_rows > 0)
while($row = $res->fetch_assoc()){
    $d = $row["event_date"];
    if(!in_array($d, $edat)) $edat[] = $d;
}
$eventDat = json_encode($edat, JSON_UNESCAPED_UNICODE);
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../favicon.ico" />
    <title><?= $siteName; ?> | <?= $subjName; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../styles/class/subject.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
    <?= LoadMathJax(); ?>

    <script src="../scripts/getmessages.js?v=<?= $pubFileVer; ?>"></script>
    <link rel="stylesheet" href="../resources/img-viewer/lib/view-bigimg.css?v=<?= $pubFileVer; ?>">
    <script src="../resources/img-viewer/lib/view-bigimg.js?v=<?= $pubFileVer; ?>"></script>
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">
        <div class="desktop">
            <p class="title"><?= $subjName; ?></p>
			<?php
				if($subjClass != null && $_SESSION['type'] == 'TEACHER') echo '<p class="subtitle">' . $subjClassName . '</p>';
			?>

            <div class="main-content">
                <div class="main-left-column">

                    <?php if($_SESSION['type'] == 'TEACHER'): ?>
                    <a href="./newsubjectpost.php?s=<?= $subjId; ?>" class="new-post-button">Νέα Ανακοίνωση<img src="../resources/new.png" /></a>
                    <?php endif; ?>

                    <?php

					//all this loading is time-consuming......... (read line 513)
                    $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_usage='subject' AND post_used_id=$subjId ORDER BY post_date DESC");
                    if($res->num_rows < 1) echo '<p style="text-align:center;width:100%;font-size:20px;font-family:\'Noto Sans\'">Δεν υπάρχουν ανακοινώσεις</p>';
                    else while($row = $res->fetch_assoc())
                    {
                        $id = (int)$row["post_id"];

                        $visibility = $row['post_visibility'];
                        $col = 'hsl(120, 80%, 80%)';
                        if($visibility == 0) $col = '#FFC4C4';

                        if($visibility == 0 && $_SESSION['type'] == 'STUDENT') continue;

                        $title = decrypt($row['post_title']);
                        $title = htmlentities($title);

                        $text = decrypt($row['post_text']);
                        $text = str_replace('<br>', " \\n ", $text);
                        $text = htmlspecialchars($text);
                        $text = formatText($text);
                        $text = str_replace('\\n', '<br>', $text);

                        $date = preg_split('/ /', $row["post_date"]);
                        $date = preg_split('/-/', $date[0])[2] . '/' . preg_split('/-/', $date[0])[1] . '/' . preg_split('/-/', $date[0])[0] . ' ' . $date[1];
                        $date = str_replace('00:00:00', '', $date);

                        $author = mysqli_real_escape_string($conn, $row['post_author']);
                        $uname = $author;
                        if($author !== 'admin')
                        {
                            $sql = "SELECT user_name FROM users WHERE user_username='$author' LIMIT 1";
                            $result = mysqli_query($conn, $sql);
                            if($result->num_rows > 0)
                                $author = decrypt($result->fetch_assoc()['user_name']);
                        }
                        elseif($_SESSION['type'] == 'TEACHER') $author = 'Administrator';
                        else $author = '';

                        $files = explode(',', $row['post_files']);

                        $outfiles = '';
                        if(!empty($files) && $files[0] != '')
                        {
                            for($i = 0; $i < sizeof($files); $i++)
                            {
                                if(empty($files[$i])) continue;
                                $file = mysqli_real_escape_string($conn, $files[$i]);
                                $uppath = '../file.php?id=' . $file;
                                $filename = '';

                                $result = mysqli_query($conn, "SELECT * FROM files WHERE file_uid='$file' AND file_owner='$uname' LIMIT 1");
                                if($result->num_rows < 1) continue;
                                else $filename = $result->fetch_assoc()['file_name'];

                                $imgs = array('gif', 'jpg', 'jpeg', 'png', 'webp', 'svg', 'bmp');
                                
                                $safeName = htmlentities($filename);

                                $ext = explode('.', $filename);
                                $ext = end($ext);
                                $ext = mb_strtolower($ext);

                                if(in_array($ext, $imgs) && false)
                                {
                                    //<a class="post-file" href="./file.php?id=a" target="_blank" title="filename"><img src="resources/icons/empty.png"/><p>filename</p></a>
                                    $outfiles .= '
                                        <a class="post-file image" title="' . $safeName . '" id="img' . $file . $id . '">
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
                                    $outfiles .= '<a class="post-file" href="../file.php?id=' . $file . '" target="_blank" title="' . $safeName . '"><img src="../resources/icons/' . $fileIcon . '.png"/><p>' . $safeName . '</p></a>';
                                }
                            }
                            echo '<div class="post" style="background-color: ' . $col .'">';
                                    if($_SESSION['type'] == 'TEACHER' && $username == $uname) echo '<a class="post-edit" href="./editsubjectpost.php?id=' . $id . '">Επεξεργασία<img src="../resources/edit-icon.png"/></a>';
                                    echo '<div class="post-title">' . $title . '</div>
                                    <div class="post-date">' . $date . '</div>
                                    <div class="post-user">' . $author . '</div>
                                    <div class="post-line"></div>
                                    <div class="post-text">' . $text . '</div>
                                    <div class="post-line"></div>
                                    <div class="post-file-container">' . $outfiles . '</div>
                                </div>';
                        }
                        else
                        {
                            echo '<div class="post" style="background-color: ' . $col .'">';
                                    if($_SESSION['type'] == 'TEACHER' && $username == $uname) echo '<a class="post-edit" href="./editsubjectpost.php?id=' . $id . '">Επεξεργασία<img src="../resources/edit-icon.png"/></a>';
                                    echo '<div class="post-title">' . $title . '</div>
                                    <div class="post-date">' . $date . '</div>
                                    <div class="post-user">' . $author . '</div>
                                    <div class="post-line"></div>
                                    <div class="post-text">' . $text . '</div>
                                </div>';
                        }
                    }
                    ?>
                </div>

                <div class="main-right-column">
                    <div class="right-content">
                        <p class="right-title">Ημερολόγιο</p>
                        <div class="calendar">
                            <script>
                                const monthNames = ["Ιανουάριος","Φεβρουάριος","Μάρτιος","Απρίλιος","Μάιος","Ιούνιος","Ιούλιος","Αύγουστος","Σεπτέμβριος","Οκτώβριος","Νοέμβριος","Δεκέμβριος"];

                                let events = <?= $eventDat; ?>;
                                let s = <?= $subjId; ?>;

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
                                            el.href = './subjectcalendar.php?s=' + s + '&d=' + i + '&m=' + (tmpmonth+1) + '&y=' + tmpyear;
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
                                            echo '<a class="calendar__number today hasevent" href="./subjectcalendar.php?s=' . $subjId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                            else echo '<a class="calendar__number today" href="./subjectcalendar.php?s=' . $subjId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                        }
                                        else {
                                            if(in_array($year . '-' . (int)($month) . '-' . $i, $edat))
                                            echo '<a class="calendar__number hasevent" href="./subjectcalendar.php?s=' . $subjId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                            else echo '<a class="calendar__number" href="./subjectcalendar.php?s=' . $subjId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                        }
                                    }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="right-content">
                        <p class="right-title">Φάκελος Εργασιών</p>
                        <a class="assigments-folder" href="./assignments.php?s=<?= $subjId; ?>"><p>Είσοδος</p><img src="../resources/icons/folder.png"/></a>
                        <?php
                            $res = mysqli_query($conn, "SELECT * FROM assignments WHERE assignment_subject=$subjId AND assignment_expires>'" . date('Y-m-d\TH:m:s', time()) . "'");
                            if($res->num_rows < 1)
                                echo '<p class="assigments-count">Καμία εκκρεμής εργασία</p>';
                            elseif($res->num_rows == 1)
                                echo '<p class="assigments-count">1 εκκρεμής εργασία</p>';
                            else
                                echo '<p class="assigments-count">' . $res->num_rows . ' εκκρεμείς εργασίες</p>';
                        ?>
                    </div>

                    <div class="right-content">
                        <p class="right-title">Tests</p>
                        <?php if($_SESSION['type'] == 'TEACHER'): ?>
                            <a href="./newtest.php?s=<?= $subjId; ?>" class="new-test-button">Νέο Τέστ<img src="../resources/new.png" /></a><br>
                        <?php endif; ?>

                        <div class="test-container">
                            <?php
                                if($_SESSION['type'] == 'TEACHER'){
                                    $res = mysqli_query($conn, "SELECT * FROM tests WHERE test_subject=$subjId ORDER BY test_expires DESC");
                                    if($res->num_rows < 1) echo '<p>Δεν υπάρχουν tests</p>';
                                    else while($row = $res->fetch_assoc()){
                                        $testId = (int)$row['test_id'];
                                        $name = htmlentities(decrypt($row['test_name']));

                                        $expires = $row['test_expires'];

                                        $exp = new DateTime($expires);
                                        $now = new DateTime(date('Y-m-d H:i:s', time()));
                
                                        $date = $exp->format("d/m/Y H:i");
                                        $c = ($exp < $now) ? 'ended' : '';

                                        echo '
                                        <a class="test ' . $c . '" href="./test.php?id=' . $testId . '">
                                            <p class="test-name">' . $name . '</p>
                                            <p class="test-end">Προθεσμία: ' . $date . '</p>
                                        </a>';
                                    }
                                }
                                else {
                                    $res = mysqli_query($conn, "SELECT * FROM tests WHERE test_subject=$subjId AND test_visibility=1 ORDER BY test_expires DESC");
                                    if($res->num_rows < 1) echo '<p>Δεν υπάρχουν tests</p>';
                                    
                                    while($row = $res->fetch_assoc()){
                                        $testId = (int)$row['test_id'];

                                        $name = htmlentities(decrypt($row['test_name']));

                                        $expires = $row['test_expires'];

                                        $exp = new DateTime($expires);
                                        $now = new DateTime(date('Y-m-d H:i:s', time()));
                
                                        $date = $exp->format("d/m/Y H:i");
                                        $c = ($exp < $now) ? 'ended' : '';

                                        echo '
                                        <a class="test ' . $c . '" href="./test.php?id=' . $testId . '">
                                            <p class="test-name">' . $name . '</p>
                                            <p class="test-end">Προθεσμία: ' . $date . '</p>
                                        </a>';
                                    }
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mobile">
            <br><br><br><p class="title"><?= $subjName; ?></p>
			<?php
				if($subjClass != null && $_SESSION['type'] == 'TEACHER') echo '<p class="subtitle">' . $subjClassName . '</p>';
			?>

            <div class="sub-navigation" id="sub-nav-bar">
                <div class="to-others" onclick="openOthers();">Εργασίες</div>
                <div class="to-posts" onclick="openPosts();">Ανακοινώσεις</div>
                <div class="to-calendar" onclick="openCalendar();">Ημερολόγιο</div>
            </div>

            <div id="subject-others">
                <div class="right-content">
                    <p class="right-title">Φάκελος Εργασιών</p>
                    <a class="assigments-folder" href="./assignments.php?s=<?= $subjId; ?>"><p>Είσοδος</p><img src="../resources/icons/folder.png"/></a>
                    <?php
                        $res = mysqli_query($conn, "SELECT * FROM assignments WHERE assignment_subject=$subjId AND assignment_expires>'" . date('Y-m-d\TH:m:s', time()) . "'");
                        if($res->num_rows < 1)
                            echo '<p class="assigments-count">Καμία εκκρεμής εργασία</p>';
                        elseif($res->num_rows == 1)
                            echo '<p class="assigments-count">1 εκκρεμής εργασία</p>';
                        else
                            echo '<p class="assigments-count">' . $res->num_rows . ' εκκρεμείς εργασίες</p>';
                    ?>
                </div>

                <div class="right-content">
                    <p class="right-title">Tests</p>
                    <?php if($_SESSION['type'] == 'TEACHER'): ?>
                        <a href="./newtest.php?s=<?= $subjId; ?>" class="new-test-button">Νέο Τέστ<img src="../resources/new.png" /></a><br>
                    <?php endif; ?>

                    <div class="test-container">
                        <?php
                            if($_SESSION['type'] == 'TEACHER'){
                                $res = mysqli_query($conn, "SELECT * FROM tests WHERE test_subject=$subjId ORDER BY test_expires DESC");
                                if($res->num_rows < 1) echo '<p>Δεν υπάρχουν tests</p>';
                                else while($row = $res->fetch_assoc()){
                                    $testId = (int)$row['test_id'];
                                    $name = htmlentities(decrypt($row['test_name']));

                                    $expires = $row['test_expires'];

                                    $exp = new DateTime($expires);
                                    $now = new DateTime(date('Y-m-d H:i:s', time()));
            
                                    $date = $exp->format("d/m/Y H:i");
                                    $c = ($exp < $now) ? 'ended' : '';

                                    echo '
                                    <a class="test ' . $c . '" href="./test.php?id=' . $testId . '">
                                        <p class="test-name">' . $name . '</p>
                                        <p class="test-end">Προθεσμία: ' . $date . '</p>
                                    </a>';
                                }
                            }
                            else {
                                $res = mysqli_query($conn, "SELECT * FROM tests WHERE test_subject=$subjId AND test_visibility=1 ORDER BY test_expires DESC");
                                if($res->num_rows < 1) echo '<p>Δεν υπάρχουν tests</p>';
                                
                                while($row = $res->fetch_assoc()){
                                    $testId = (int)$row['test_id'];

                                    $name = htmlentities(decrypt($row['test_name']));

                                    $expires = $row['test_expires'];

                                    $exp = new DateTime($expires);
                                    $now = new DateTime(date('Y-m-d H:i:s', time()));
            
                                    $date = $exp->format("d/m/Y H:i");
                                    $c = ($exp < $now) ? 'ended' : '';

                                    echo '
                                    <a class="test ' . $c . '" href="./test.php?id=' . $testId . '">
                                        <p class="test-name">' . $name . '</p>
                                        <p class="test-end">Προθεσμία: ' . $date . '</p>
                                    </a>';
                                }
                            }
                        ?>
                    </div>
                </div>
            </div>

            <div id="subject-posts">
                <?php if($_SESSION['type'] == 'TEACHER'): ?>
                    <a href="./newsubjectpost.php?s=<?= $subjId; ?>" class="new-post-button">Νέα Ανακοίνωση<img src="../resources/new.png" /></a>
                <?php endif; ?>

                <?php

				// AND WE ARE DOING IT TWICE AAAAAAAAAAAAA
                $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_usage='subject' AND post_used_id=$subjId ORDER BY post_date DESC");
                if($res->num_rows < 1) echo '<p style="text-align:center;width:100%;font-size:20px;font-family:\'Noto Sans\'">Δεν υπάρχουν ανακοινώσεις</p>';
                else while($row = $res->fetch_assoc())
                {
                    $id = (int)$row["post_id"];

                    $visibility = $row['post_visibility'];
                    $col = 'hsl(120, 80%, 80%)';
                    if($visibility == 0) $col = '#FFC4C4';

                    if($visibility == 0 && $_SESSION['type'] == 'STUDENT') continue;

                    $title = decrypt($row['post_title']);
                    $title = htmlentities($title);

                    $text = decrypt($row['post_text']);
                    $text = str_replace('<br>', " \\n ", $text);
                    $text = htmlspecialchars($text);
                    $text = formatText($text);
                    $text = str_replace('\\n', '<br>', $text);

                    $date = preg_split('/ /', $row["post_date"]);
                    $date = preg_split('/-/', $date[0])[2] . '/' . preg_split('/-/', $date[0])[1] . '/' . preg_split('/-/', $date[0])[0] . ' ' . $date[1];
                    $date = str_replace('00:00:00', '', $date);

                    $author = mysqli_real_escape_string($conn, $row['post_author']);
                    $uname = $author;
                    if($author !== 'admin')
                    {
                        $sql = "SELECT user_name FROM users WHERE user_username='$author' LIMIT 1";
                        $result = mysqli_query($conn, $sql);
                        if($result->num_rows > 0)
                            $author = decrypt($result->fetch_assoc()['user_name']);
                    }
                    elseif($_SESSION['type'] == 'TEACHER') $author = 'Administrator';
                    else $author = '';

                    $files = explode(',', $row['post_files']);

                    $outfiles = '';
                    if(!empty($files) && $files[0] != '')
                    {
                        for($i = 0; $i < sizeof($files); $i++)
                        {
                            if(empty($files[$i])) continue;
                            $file = mysqli_real_escape_string($conn, $files[$i]);
                            $uppath = '../file.php?id=' . $file;
                            $filename = '';

                            $result = mysqli_query($conn, "SELECT * FROM files WHERE file_uid='$file' AND file_owner='$uname' LIMIT 1");
                            if($result->num_rows < 1) continue;
                            else $filename = $result->fetch_assoc()['file_name'];

                            $imgs = array('gif', 'jpg', 'jpeg', 'png', 'webp', 'svg', 'bmp');
                            
                            $safeName = htmlentities($filename);

                            $ext = explode('.', $filename);
                            $ext = end($ext);
                            $ext = mb_strtolower($ext);

                            if(in_array($ext, $imgs) && false)
                            {
                                //<a class="post-file" href="./file.php?id=a" target="_blank" title="filename"><img src="resources/icons/empty.png"/><p>filename</p></a>
                                $outfiles .= '
                                    <a class="post-file image" title="' . $safeName . '" id="img' . $file . $id . 'mb">
                                        <img src="' . $uppath . '" id="src' . $file . $id . 'mb">
                                        <p>' . $safeName. '</p>
                                        <script>
                                            document.getElementById("img' . $file . $id . 'mb").onclick = function (e) {
                                                viewer.show(document.getElementById("src' . $file  . $id . 'mb").src);
                                                document.getElementById("header").style.display = "none";
                                            }
                                        </script>
                                    </a>
                                ';
                            }
                            else {
                                $fileIcon = iconFromExtension($filename);
                                $outfiles .= '<a class="post-file" href="../file.php?id=' . $file . '" target="_blank" title="' . $safeName . '"><img src="../resources/icons/' . $fileIcon . '.png"/><p>' . $safeName . '</p></a>';
                            }
                        }
                        echo '<div class="post" style="background-color: ' . $col .'">';
                                if($_SESSION['type'] == 'TEACHER' && $username == $uname) echo '<a class="post-edit" href="./editsubjectpost.php?id=' . $id . '">Επεξεργασία<img src="../resources/edit-icon.png"/></a>';
                                echo '<div class="post-title">' . $title . '</div>
                                <div class="post-date">' . $date . '</div>
                                <div class="post-user">' . $author . '</div>
                                <div class="post-line"></div>
                                <div class="post-text">' . $text . '</div>
                                <div class="post-line"></div>
                                <div class="post-file-container">' . $outfiles . '</div>
                            </div>';
                    }
                    else
                    {
                        echo '<div class="post" style="background-color: ' . $col .'">';
                                if($_SESSION['type'] == 'TEACHER' && $username == $uname) echo '<a class="post-edit" href="./editsubjectpost.php?id=' . $id . '">Επεξεργασία<img src="../resources/edit-icon.png"/></a>';
                                echo '<div class="post-title">' . $title . '</div>
                                <div class="post-date">' . $date . '</div>
                                <div class="post-user">' . $author . '</div>
                                <div class="post-line"></div>
                                <div class="post-text">' . $text . '</div>
                            </div>';
                    }
                }
                ?>
            </div>

            <div id="subject-calendar">
                <div class="calendar-mb">
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
                                    echo '<a class="calendar__number today hasevent" href="./subjectcalendar.php?s=' . $subjId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                    else echo '<a class="calendar__number today" href="./subjectcalendar.php?s=' . $subjId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                }
                                else {
                                    if(in_array($year . '-' . (int)($month) . '-' . $i, $edat))
                                    echo '<a class="calendar__number hasevent" href="./subjectcalendar.php?s=' . $subjId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                    else echo '<a class="calendar__number" href="./subjectcalendar.php?s=' . $subjId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                }
                            }
                        ?>
                    </div>
                </div>
            </div>

            <script>
                function openOthers(){
                    document.getElementById('subject-others').style.display = 'block';
                    document.getElementById('subject-posts').style.display = 'none';
                    document.getElementById('subject-calendar').style.display = 'none';
                    document.getElementById('sub-nav-bar').style.backgroundColor = 'hsl(200, 95%, 64%)';
                }
                function openPosts(){
                    document.getElementById('subject-others').style.display = 'none';
                    document.getElementById('subject-posts').style.display = 'block';
                    document.getElementById('subject-calendar').style.display = 'none';
                    document.getElementById('sub-nav-bar').style.backgroundColor = 'hsl(125, 85%, 64%)';
                }
                function openCalendar(){
                    document.getElementById('subject-others').style.display = 'none';
                    document.getElementById('subject-posts').style.display = 'none';
                    document.getElementById('subject-calendar').style.display = 'block';
                    document.getElementById('sub-nav-bar').style.backgroundColor = 'hsl(0, 100%, 64%)';
                }
                openPosts();
            </script>
            
        </div>

    </div>
    <script>
        let viewer = new ViewBigimg();
        document.querySelectorAll(".iv-close").forEach(el => el.onclick = function (e) {document.getElementById("header").style.display = "inline";});
    </script>
    <div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>
