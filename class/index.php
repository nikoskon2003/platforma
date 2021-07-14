<?php session_start();
include_once '../includes/config.php';

if(!isset($_SESSION['type']))
{
    if(isset($_COOKIE["autologin"]))
    {
        header("Location: ../includes/autologin.inc.php?r=class");
        exit();
    }
    else
    {
        header("Location: ../login.php?r=class");
        exit();
    }
}

if($_SESSION['type'] === 'ADMIN'){
    header("Location: ../admin/subjects/");
    exit();
}
elseif($_SESSION['type'] !== 'STUDENT' && $_SESSION['type'] !== 'TEACHER'){
    include '../error.php';
    exit();
}

include '../includes/dbh.inc.php';
include_once '../includes/extrasLoader.inc.php';
include_once '../includes/enc.inc.php';
$monthNames = ["Ιανουάριος","Φεβρουάριος","Μάρτιος","Απρίλιος","Μάιος","Ιούνιος","Ιούλιος","Αύγουστος","Σεπτέμβριος","Οκτώβριος","Νοέμβριος","Δεκέμβριος"];
$username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
date_default_timezone_set('Europe/Athens');

$year = date("Y", time());
$month = date("m", time());
$day = date("d", time());
$dim = date('t', strtotime($year . '-' . (int)$month . '-01'));
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../favicon.ico" />
    <title><?= $siteName; ?> | Τάξη</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../styles/class/index.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">

    <?= LoadBackground(__FILE__); ?>
    <script src="../scripts/getmessages.js?v=<?= $pubFileVer; ?>"></script>
    <?= LoadMathJax(); ?>

    <link rel="stylesheet" href="../resources/img-viewer/lib/view-bigimg.css?v=<?= $pubFileVer; ?>">
    <script src="../resources/img-viewer/lib/view-bigimg.js?v=<?= $pubFileVer; ?>"></script>
</head>

<body>
<div id="container">
	<div id="header"><?= LoadTopNav(__FILE__); ?></div>
	<div id="body">
        <div class="desktop">

            <?php
            $classIds = [];
            if($_SESSION['type'] == 'STUDENT'){
                $classIds[] = (int)$_SESSION['user_class'];
            }
            elseif($_SESSION['type'] == 'TEACHER'){
                $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='class-writer'");
                while($row = $res->fetch_assoc()) $classIds[] = (int)$row['link_used_id'];
            }

            foreach($classIds as $classId){
                $res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$classId");
                if($res->num_rows > 0){
                    $className = htmlentities($res->fetch_assoc()['class_name']);
                    $resEditor = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='class-writer' AND link_used_id=$classId");
                    $isEditor = $resEditor->num_rows > 0;

                    echo '<div class="class">
                    <div class="title" onclick="toggleClassDisplay(this);">' . $className . '</div>
                    <div class="class-content">
                        <div class="class-posts">';

                        if($isEditor) echo '<a href="./newclasspost.php?c=' . $classId . '" class="new-post-button">Νέα Ανακοινώση<img src="../resources/new.png"/></a>';                

                        $resPosts = mysqli_query($conn, "SELECT * FROM posts WHERE post_usage='class' AND post_used_id=$classId ORDER BY post_date DESC");
                        if($resPosts->num_rows < 1) echo '<p style="text-align:center;width:100%">Δεν υπάρχουν ανακοινώσεις</p>';
                        else while($row = $resPosts->fetch_assoc())
                        {
                            $visibility = $row['post_visibility'];
                            $col = 'hsl(120, 80%, 80%)';
                            if($visibility == 0) $col = '#FFC4C4';

                            if($visibility == 0 && $row['post_author'] != $username) continue;

                            $id = (int)$row["post_id"];

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
                                $sql = "SELECT user_name FROM users WHERE user_username='$author'";
                                $result = mysqli_query($conn, $sql);
                                if($result->num_rows > 0)
                                    $author = decrypt($result->fetch_assoc()['user_name']);
                            }
                            elseif($isEditor) $author = 'Administrator';
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
                                        if($username == $uname && $isEditor) echo '<a class="post-edit" href="./editclasspost.php?id=' . $id . '">Επεξεργασία<img src="../resources/edit-icon.png"/></a>';
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
                                        if($username == $uname && $isEditor) echo '<a class="post-edit" href="./editclasspost.php?id=' . $id . '">Επεξεργασία<img src="../resources/edit-icon.png"/></a>';
                                        echo '<div class="post-title">' . $title . '</div>
                                        <div class="post-date">' . $date . '</div>
                                        <div class="post-user">' . $author . '</div>
                                        <div class="post-line"></div>
                                        <div class="post-text">' . $text . '</div>
                                    </div>';
                            }
                        }

                    echo '</div>';

                    $edat = [];
                    $resCal = mysqli_query($conn, "SELECT * FROM calendar_events WHERE event_class=$classId AND event_subject IS NULL");
                    if($resCal->num_rows > 0)
                    while($row = $resCal->fetch_assoc()){
                        $d = $row["event_date"];
                        if(!in_array($d, $edat)) $edat[] = $d;
                    }
                    $eventDat = json_encode($edat, JSON_UNESCAPED_UNICODE);
                    $eventDat = str_replace('"', '\'', $eventDat);

                    echo '<div class="class-calendar">
                        <div class="calendar-title">Ημερολόγιο</div>
                        <div class="calendar">';

                        echo '<div class="calendar-top">
                                <div class="calendar-top-left" title="Προηγούμενος Μήνας" onclick="prevMonth();"><b>&lt;</b></div>
                                <div class="calendar-top-month">' . $monthNames[(int)$month-1] . ' ' . $year . '</div>
                                <div class="calendar-top-right" title="Επόμενος Μήνας" onclick="nextMonth();"><b>&gt;</b></div>
                            </div>
                            <div class="calendar__date" event-dat="' . $eventDat . '" class-id="' . $classId . '">
                                <div class="calendar__day">Δε</div>
                                <div class="calendar__day">Τρ</div>
                                <div class="calendar__day">Τε</div>
                                <div class="calendar__day">Πε</div>
                                <div class="calendar__day">Πα</div>
                                <div class="calendar__day">Σα</div>
                                <div class="calendar__day">Κυ</div>';

                                $firstDay = date('N', strtotime($year . '-' . (int)$month . '-01'));
                                while($firstDay > 1){
                                    echo '<div class="calendar__number empty"></div>';
                                    $firstDay--;
                                }
                                for($i = 1; $i <= $dim; $i++){
                                    if($i == $day){
                                        if(in_array($year . '-' . (int)($month) . '-' . $i, $edat))
                                        echo '<a class="calendar__number today hasevent" href="./classcalendar.php?c=' . $classId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                        else echo '<a class="calendar__number today" href="./classcalendar.php?c=' . $classId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                    }
                                    else {
                                        if(in_array($year . '-' . (int)($month) . '-' . $i, $edat))
                                        echo '<a class="calendar__number hasevent" href="./classcalendar.php?c=' . $classId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                        else echo '<a class="calendar__number" href="./classcalendar.php?c=' . $classId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                    }
                                }
                                
                    echo '</div>
                        </div>
                    </div>';
                    
                    
                    echo '</div></div>';
                }
            }
            ?>

            <div class="subj-title">Μαθήματα:</div>
            <?php 
                if($_SESSION['type'] == 'STUDENT'){
                    $classId = (int)$_SESSION['user_class'];
                    $otherSubjs = '';

                    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-student' AND link_user='$username'");
                    while($row = $res->fetch_assoc())
                    {
                        $id = (int)$row['link_used_id'];
                        $otherSubjs .= " OR subject_id=" . $id;
                    }

                    $sql;
                    if($classId != null)
                        $sql = "SELECT * FROM subjects WHERE subject_class=$classId $otherSubjs ORDER BY subject_latest_update DESC";
                    else
                        $sql = "SELECT * FROM subjects WHERE false $otherSubjs ORDER BY subject_latest_update DESC";

                    $res = mysqli_query($conn, $sql);
                    if($res->num_rows < 1) echo '<div class="no-subjects">Δεν υπάρχουν μαθήματα!</div>';
                    else {
                        echo '<div class="subjects-holder">';
                        while($row = $res->fetch_assoc()){
                            $subjId = $row['subject_id'];
                            $subjName = decrypt($row['subject_name']);
                            $subjLU = $row['subject_latest_update'];

                            $parts = explode(' ', $subjLU);
                            if($parts[0] == "0000-00-00") $subjLU = "";
                            else {
                                $t = explode('-',$parts[0]);
                                $subjLU = $t[2] . '/' . $t[1] . '/' . $t[0];
                            }

                            echo "<a href='./subject.php?s=$subjId' class='subject'>
                                <p class='subject-name'>$subjName</p>
                                <p class='subject-latest-update'>$subjLU</p>
                            </a>";
                        }
                        echo '</div>';
                    }
                }
                elseif($_SESSION['type'] == 'TEACHER'){

                    $otherSubjs = '';

                    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-teacher' AND link_user='$username'");
                    while($row = $res->fetch_assoc())
                    {
                        $id = (int)$row['link_used_id'];
                        $otherSubjs .= " OR subject_id=" . $id;
                    }

                    $res = mysqli_query($conn, "SELECT * FROM subjects WHERE false $otherSubjs ORDER BY subject_latest_update DESC");
                    if($res->num_rows < 1) echo '<div class="no-subjects">Δεν υπάρχουν μαθήματα!</div>';
                    else {
                        echo '<div class="subjects-holder">';
                        while($row = $res->fetch_assoc()){
                            $subjId = $row['subject_id'];
                            $subjName = decrypt($row['subject_name']);
                            $subjLU = $row['subject_latest_update'];

                            $parts = explode(' ', $subjLU);
                            if($parts[0] == "0000-00-00") $subjLU = "";
                            else {
                                $t = explode('-',$parts[0]);
                                $subjLU = $t[2] . '/' . $t[1] . '/' . $t[0];
                            }

                            $className = '';
                            $subjClass = (int)$row['subject_class'];
                            $resb = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$subjClass LIMIT 1");
                            if($resb->num_rows > 0) $className = 'Τάξη: ' . htmlentities($resb->fetch_assoc()['class_name']);

                            echo "<a href='./subject.php?s=$subjId' class='subject'>
                                <p class='subject-name'>$subjName</p>
                                <p class='subject-class'>$className</p>
                                <p class='subject-latest-update'>$subjLU</p>
                            </a>";
                        }
                        echo '</div>';
                    }
                }
            ?>
        </div>

        <div class="mobile">
            <?php

            $classIds = [];
            if($_SESSION['type'] == 'STUDENT'){
                $classIds[] = (int)$_SESSION['user_class'];
            }
            elseif($_SESSION['type'] == 'TEACHER'){
                $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='class-writer'");
                while($row = $res->fetch_assoc()) $classIds[] = (int)$row['link_used_id'];
            }

            if(count($classIds) > 0){
                if($_SESSION['type'] == 'STUDENT'){
                    echo '<div class="sub-navigation" id="sub-nav-bar">
                        <div class="to-classes" onclick="openClasses();">Τάξη</div>
                        <div class="to-subjects" onclick="openSubjects();">Μαθήματα</div>
                    </div>';
                }
                elseif($_SESSION['type'] == 'TEACHER'){
                    echo '<div class="sub-navigation" id="sub-nav-bar">
                        <div class="to-classes" onclick="openClasses();">Τάξεις</div>
                        <div class="to-subjects" onclick="openSubjects();">Μαθήματα</div>
                    </div>';
                }
            }

            echo '<div id="classes-cont">';
            foreach($classIds as $classId){
                $res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$classId");
                if($res->num_rows > 0){
                    $className = htmlentities($res->fetch_assoc()['class_name']);
                    $resEditor = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='class-writer' AND link_used_id=$classId");
                    $isEditor = $resEditor->num_rows > 0;

                    echo '<br><br><div class="class-mb">
                        <div class="title" onclick="toggleClassDisplay(this);">' . $className . '</div>
                        <div class="class-content">';

                        $edat = [];
                        $resCal = mysqli_query($conn, "SELECT * FROM calendar_events WHERE event_class=$classId AND event_subject IS NULL");
                        if($resCal->num_rows > 0)
                        while($row = $resCal->fetch_assoc()){
                            $d = $row["event_date"];
                            if(!in_array($d, $edat)) $edat[] = $d;
                        }
                        $eventDat = json_encode($edat, JSON_UNESCAPED_UNICODE);
                        $eventDat = str_replace('"', '\'', $eventDat);

                        echo '<div class="class-calendar-mb">
                            <div class="calendar-title">Ημερολόγιο</div>
                            <div class="calendar">';

                        echo '<div class="calendar-top">
                                <div class="calendar-top-left" title="Προηγούμενος Μήνας" onclick="prevMonth();"><b>&lt;</b></div>
                                <div class="calendar-top-month">' . $monthNames[(int)$month-1] . ' ' . $year . '</div>
                                <div class="calendar-top-right" title="Επόμενος Μήνας" onclick="nextMonth();"><b>&gt;</b></div>
                            </div>
                            <div class="calendar__date" event-dat="' . $eventDat . '" class-id="' . $classId . '">
                                <div class="calendar__day">Δε</div>
                                <div class="calendar__day">Τρ</div>
                                <div class="calendar__day">Τε</div>
                                <div class="calendar__day">Πε</div>
                                <div class="calendar__day">Πα</div>
                                <div class="calendar__day">Σα</div>
                                <div class="calendar__day">Κυ</div>';

                        $firstDay = date('N', strtotime($year . '-' . (int)$month . '-01'));
                        while($firstDay > 1){
                            echo '<div class="calendar__number empty"></div>';
                            $firstDay--;
                        }
                        for($i = 1; $i <= $dim; $i++){
                            if($i == $day){
                                if(in_array($year . '-' . (int)($month) . '-' . $i, $edat))
                                echo '<a class="calendar__number today hasevent" href="./classcalendar.php?c=' . $classId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                else echo '<a class="calendar__number today" href="./classcalendar.php?c=' . $classId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                            }
                            else {
                                if(in_array($year . '-' . (int)($month) . '-' . $i, $edat))
                                echo '<a class="calendar__number hasevent" href="./classcalendar.php?c=' . $classId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                else echo '<a class="calendar__number" href="./classcalendar.php?c=' . $classId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                            }
                        }
                        echo '</div>'; //calendar__date

                        echo '</div></div>'; //calendar-mb, class-calendar-mb

                        echo '<div class="class-posts-mb">';

                        if($isEditor) echo '<a href="./newclasspost.php?c=' . $classId . '" class="new-post-button">Νέα Ανακοινώση<img src="../resources/new.png"/></a>';                

                        $resPosts = mysqli_query($conn, "SELECT * FROM posts WHERE post_usage='class' AND post_used_id=$classId ORDER BY post_date DESC");
                        if($resPosts->num_rows < 1) echo '<p style="text-align:center;width:100%">Δεν υπάρχουν ανακοινώσεις</p>';
                        else while($row = $resPosts->fetch_assoc())
                        {
                            $visibility = $row['post_visibility'];
                            $col = 'hsl(120, 80%, 80%)';
                            if($visibility == 0) $col = '#FFC4C4';

                            if($visibility == 0 && $row['post_author'] != $username) continue;

                            $id = (int)$row["post_id"];

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
                                $sql = "SELECT user_name FROM users WHERE user_username='$author'";
                                $result = mysqli_query($conn, $sql);
                                if($result->num_rows > 0)
                                    $author = decrypt($result->fetch_assoc()['user_name']);
                            }
                            elseif($isEditor) $author = 'Administrator';
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
                                        if($username == $uname && $isEditor) echo '<a class="post-edit" href="./editclasspost.php?id=' . $id . '">Επεξεργασία<img src="../resources/edit-icon.png"/></a>';
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
                                        if($username == $uname && $isEditor) echo '<a class="post-edit" href="./editclasspost.php?id=' . $id . '">Επεξεργασία<img src="../resources/edit-icon.png"/></a>';
                                        echo '<div class="post-title">' . $title . '</div>
                                        <div class="post-date">' . $date . '</div>
                                        <div class="post-user">' . $author . '</div>
                                        <div class="post-line"></div>
                                        <div class="post-text">' . $text . '</div>
                                    </div>';
                            }
                        }

                    echo '</div>'; //class-posts
                    echo '</div>'; //class-content
                    echo '</div>'; //class-mb
                }
            }
            echo '</div>'; //class-cont
            ?>
            
            <div id="subjects-cont">
            <br><br><br>
            <?php 
                if($_SESSION['type'] == 'STUDENT'){
                    $classId = (int)$_SESSION['user_class'];
                    $otherSubjs = '';

                    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-student' AND link_user='$username'");
                    while($row = $res->fetch_assoc())
                    {
                        $id = (int)$row['link_used_id'];
                        $otherSubjs .= " OR subject_id=" . $id;
                    }

                    $sql;
                    if($classId != null)
                        $sql = "SELECT * FROM subjects WHERE subject_class=$classId $otherSubjs ORDER BY subject_latest_update DESC";
                    else
                        $sql = "SELECT * FROM subjects WHERE false $otherSubjs ORDER BY subject_latest_update DESC";

                    $res = mysqli_query($conn, $sql);
                    if($res->num_rows < 1) echo '<div class="no-subjects">Δεν υπάρχουν μαθήματα!</div>';
                    else {
                        echo '<div class="subjects-holder">';
                        while($row = $res->fetch_assoc()){
                            $subjId = $row['subject_id'];
                            $subjName = decrypt($row['subject_name']);
                            $subjLU = $row['subject_latest_update'];

                            $parts = explode(' ', $subjLU);
                            if($parts[0] == "0000-00-00") $subjLU = "";
                            else {
                                $t = explode('-',$parts[0]);
                                $subjLU = $t[2] . '/' . $t[1] . '/' . $t[0];
                            }

                            echo "<a href='./subject.php?s=$subjId' class='subject-mb'>
                                <p class='subject-name-mb'>$subjName</p>
                                <p class='subject-latest-update'>$subjLU</p>
                            </a>";
                        }
                        echo '</div>';
                    }
                }
                elseif($_SESSION['type'] == 'TEACHER'){

                    $otherSubjs = '';

                    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-teacher' AND link_user='$username'");
                    while($row = $res->fetch_assoc())
                    {
                        $id = (int)$row['link_used_id'];
                        $otherSubjs .= " OR subject_id=" . $id;
                    }

                    $res = mysqli_query($conn, "SELECT * FROM subjects WHERE false $otherSubjs ORDER BY subject_latest_update DESC");
                    if($res->num_rows < 1) echo '<div class="no-subjects">Δεν υπάρχουν μαθήματα!</div>';
                    else {
                        echo '<div class="subjects-holder">';
                        while($row = $res->fetch_assoc()){
                            $subjId = $row['subject_id'];
                            $subjName = decrypt($row['subject_name']);
                            $subjLU = $row['subject_latest_update'];

                            $parts = explode(' ', $subjLU);
                            if($parts[0] == "0000-00-00") $subjLU = "";
                            else {
                                $t = explode('-',$parts[0]);
                                $subjLU = $t[2] . '/' . $t[1] . '/' . $t[0];
                            }

                            $className = '';
                            $subjClass = (int)$row['subject_class'];
                            $resb = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$subjClass LIMIT 1");
                            if($resb->num_rows > 0) $className = 'Τάξη: ' . htmlentities($resb->fetch_assoc()['class_name']);

                            echo "<a href='./subject.php?s=$subjId' class='subject-mb'>
                                <p class='subject-name-mb'>$subjName</p>
                                <p class='subject-class'>$className</p>
                                <p class='subject-latest-update'>$subjLU</p>
                            </a>";
                        }
                        echo '</div>';
                    }
                }
            ?>
            </div>
        </div>

        <script>
            const monthNames = ["Ιανουάριος","Φεβρουάριος","Μάρτιος","Απρίλιος","Μάιος","Ιούνιος","Ιούλιος","Αύγουστος","Σεπτέμβριος","Οκτώβριος","Νοέμβριος","Δεκέμβριος"];

            let phpDate = "<?= date('Y/m/d H:m:s', time()) ?>";
            let serverDate = new Date(phpDate);
            let monthToday = serverDate.getMonth();
            let today = serverDate.getDate();
            let yearToday = serverDate.getFullYear();

            let tmpyear = yearToday;
            let tmpmonth = monthToday;

            function showMonth(month, year){
                month = Math.min(Math.max(month, 0), 11);

                let disp = document.querySelectorAll(".calendar-top-month");
                disp.forEach(e => e.innerHTML = monthNames[month] + " " + year);

                let cal = document.querySelectorAll(".calendar__date");
                cal.forEach(e => {
                    let events = JSON.parse(e.getAttribute('event-dat').replace(/'/g, '"'));
                    let c = e.getAttribute('class-id');

                    while(e.childElementCount > 7){
                        e.lastElementChild.remove();
                    }
                    let dim = new Date(year, month + 1, 0).getDate();
                    let firstDay = new Date(year, month, 1).getDay();
                    firstDay = firstDay == 0 ? 7 : firstDay; 
                    while(firstDay > 1){
                        let el = document.createElement("div");
                        el.innerHTML = "";
                        el.classList.add("calendar__number");
                        el.classList.add("empty");
                        e.appendChild(el);
                        firstDay--;
                    }
                    for(let i = 1; i <= dim; i++){
                        let el = document.createElement("a");
                        el.innerHTML = i;
                        el.href = "./classcalendar.php?c=" + c + "&d=" + i + "&m=" + (tmpmonth+1) + "&y=" + tmpyear;
                        el.classList.add("calendar__number");
                        if(year == yearToday && month == monthToday && i == today) el.classList.add("today");
                        if(events.indexOf(year + "-" + (month+1) + "-" + i) >= 0) el.classList.add("hasevent");
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
        <script>
            let classes = document.getElementById('classes-cont');
            let subjects = document.getElementById('subjects-cont');
            let navbar = document.getElementById('sub-nav-bar');

            function openClasses(){
                classes.style.display = 'block';
                subjects.style.display = 'none';
                navbar.style.backgroundColor = "hsl(125, 85%, 64%)";
            }
            function openSubjects(){
                classes.style.display = 'none';
                subjects.style.display = 'block';
                navbar.style.backgroundColor = "hsl(200, 95%, 64%)";
            }
            if(navbar != null) openSubjects();
        </script>
        <script>
            let viewer = new ViewBigimg();
            document.querySelectorAll(".iv-close").forEach(el => el.onclick = function (e) {document.getElementById("header").style.display = "inline";});
            function toggleClassDisplay(el){
                let next = el.nextElementSibling;
                if(next.style.display == 'none'){
                    next.style.display = 'block';
                    el.style.borderBottomLeftRadius = '0';
                    el.style.borderBottomRightRadius = '0'; 
                }
                else {
                    next.style.display = 'none';
                    el.style.borderBottomLeftRadius = '10px';
                    el.style.borderBottomRightRadius = '10px'; 
                }
            }
        </script>
	</div>
	<div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>
