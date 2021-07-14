<?php  session_start();

if(!isset($_SESSION['type'])){
    include '../../error.php';
    exit();
}elseif($_SESSION['type'] !== 'ADMIN'){
    include '../../error.php';
    exit();
}

include_once '../../includes/config.php';

if(!isset($_GET['c'])){
    header("Location: .");
    exit();
}
if(!is_numeric($_GET['c'])){
    header("Location: .");
    exit();
}

$classId = (int)($_GET['c']);

include '../../includes/dbh.inc.php';
$res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$classId");
if($res->num_rows < 1){
    header("Location: .");
    exit();
}
$className = $res->fetch_assoc()['class_name'];

include_once '../../includes/extrasLoader.inc.php';
include_once '../../includes/enc.inc.php';
$monthNames = ["Ιανουάριος","Φεβρουάριος","Μάρτιος","Απρίλιος","Μάιος","Ιούνιος","Ιούλιος","Αύγουστος","Σεπτέμβριος","Οκτώβριος","Νοέμβριος","Δεκέμβριος"];

$edat = [];
$res = mysqli_query($conn, "SELECT * FROM calendar_events WHERE event_class=$classId AND event_subject IS NULL");
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
    <link rel="shortcut icon" href="../../favicon.ico" />
    <title><?= $siteName ?> | <?= $className; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/classes/class.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
    <?= LoadMathJax(); ?>

    <link rel="stylesheet" href="../../resources/img-viewer/lib/view-bigimg.css?v=<?= $pubFileVer; ?>">
    <script src="../../resources/img-viewer/lib/view-bigimg.js?v=<?= $pubFileVer; ?>"></script>
</head>

<body>
<div id="container">
    <script>let viewer = new ViewBigimg();</script>
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">

    <div class="desktop">
        <div class="title"><?= $className; ?></div>
        <div class="action-holder">
            <a href="./" class="back-button">Πίσω</a>
            <form action="../../includes/admin/classes/editclass.inc.php" method="POST" onsubmit="document.getElementById('action-hider').style.display = 'block';">
                <p class="field-label">Όνομα Τάξης</p>
                <input type="hidden" name="id" value="<?= $classId; ?>"/>
                <input type="text" name="name" value="<?= $className; ?>"/>
                <button type="submit" name="submit" value="submit" class="button">Υποβολή</button>
            </form>
            
            <?php
                if(isset($_GET['e'])){
                    if($_GET['e'] == 'exists')
                        echo '<p class="error">Η τάξη υπάρχει ήδη</p>';
                    elseif($_GET['e'] == 'empty')
                        echo '<p class="error">Το όνομα της τάξης δεν μπορέι να είναι κενό</p>';
                }
            ?>
            <br>
            <form action="../../includes/admin/classes/deleteclass.inc.php" method="POST" onsubmit="if(!confirm('!!!Με τη διαγραφή της τάξης θα ΔΙΑΓΡΑΦΟΥΝ ΟΛΕΣ οι ΑΝΑΚΟΙΝΩΣΕΙΣ και τα μαθήματα/χρήστες δεν θα ανοίκουν σε κάποια τάξη πλέον!!!')) return false; else document.getElementById('action-hider').style.display = 'block';">
                <input type="hidden" name="id" value="<?= $classId; ?>"/>
                <button type="submit" name="delete" value="delete" class="del-button">Διαγραφή</button>
            </form>
        </div>

        <div class="authors">
            <a href="./writers.php?c=<?= $classId; ?>">Επεξεργασία Συντακτών<img src="../../resources/edit-icon.png"/></a>
        </div>

        <div class="class-content">
            <div class="class-posts">
                <a href="./newpost.php?c=<?= $classId; ?>" class="new-post-button">Νέα Ανακοινώση<img src="../../resources/new.png"/></a>
                <?php

                $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_usage='class' AND post_used_id=$classId ORDER BY post_date DESC");
                if($res->num_rows < 1) echo '<p style="text-align:center;width:100%">Δεν υπάρχουν ανακοινώσεις</p>';
                else while($row = $res->fetch_assoc())
                {
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
                    else $author = 'Administrator';

                    $visibility = $row['post_visibility'];
                    $col = 'hsl(120, 80%, 80%)';
                    if($visibility == 0) $col = '#FFC4C4';

                    $files = explode(',', $row['post_files']);

                    $outfiles = '';
                    if(!empty($files) && $files[0] != '')
                    {
                        for($i = 0; $i < sizeof($files); $i++)
                        {
                            if(empty($files[$i])) continue;
                            $file = mysqli_real_escape_string($conn, $files[$i]);
                            $uppath = '../../file.php?id=' . $file;
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
                                $outfiles .= '<a class="post-file" href="../../file.php?id=' . $file . '" target="_blank" title="' . $safeName . '"><img src="../../resources/icons/' . $fileIcon . '.png"/><p>' . $safeName . '</p></a>';
                            }
                        }
                        echo '<div class="post" style="background-color: ' . $col .'">';
                                if($_SESSION['type'] == 'ADMIN' || $username == $uname) echo '<a class="post-edit" href="./editpost.php?id=' . $id . '">Επεξεργασία<img src="../../resources/edit-icon.png"/></a>';
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
                                if($_SESSION['type'] == 'ADMIN' || $username == $uname) echo '<a class="post-edit" href="./editpost.php?id=' . $id . '">Επεξεργασία<img src="../../resources/edit-icon.png"/></a>';
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

            <div class="class-calendar">
                <div class="calendar-title">Ημερολόγιο</div>
                <div class="calendar">
                    <script>
                        const monthNames = ["Ιανουάριος","Φεβρουάριος","Μάρτιος","Απρίλιος","Μάιος","Ιούνιος","Ιούλιος","Αύγουστος","Σεπτέμβριος","Οκτώβριος","Νοέμβριος","Δεκέμβριος"];

                        let events = <?= $eventDat; ?>;
                        let c = <?= $classId ?>;

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
                                    el.href = './calendar.php?c=' + c + '&d=' + i + '&m=' + (tmpmonth+1) + '&y=' + tmpyear;
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
                                    echo '<a class="calendar__number today hasevent" href="./calendar.php?c=' . $classId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                    else echo '<a class="calendar__number today" href="./calendar.php?c=' . $classId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                }
                                else {
                                    if(in_array($year . '-' . (int)($month) . '-' . $i, $edat))
                                    echo '<a class="calendar__number hasevent" href="./calendar.php?c=' . $classId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                    else echo '<a class="calendar__number" href="./calendar.php?c=' . $classId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                                }
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mobile">
        <div class="sub-navigation" id="sub-nav-bar">
            <div class="to-edit" onclick="openEdit();">Επεξεργασία</div>
            <div class="to-posts" onclick="openPosts();">Ανακοινώσεις</div>
            <div class="to-calendar" onclick="openCalendar();">Ημερολόγιο</div>
        </div>

        <br><br><br><div class="title"><?= $className; ?></div>
        <a href="./" class="back-button">Πίσω</a><br>

        <div class="action-holder-mb" id="edit-mb">
            <form action="../../includes/admin/classes/editclass.inc.php" method="POST">
                <p class="field-label">Όνομα Τάξης</p>
                <input type="hidden" name="id" value="<?= $classId; ?>"/>
                <input type="text" name="name" value="<?= $className; ?>"/>
                <button type="submit" name="submit" value="submit" class="button">Υποβολή</button>
            </form>
            
            <?php
                if(isset($_GET['e'])){
                    if($_GET['e'] == 'exists')
                        echo '<p class="error">Η τάξη υπάρχει ήδη</p>';
                    elseif($_GET['e'] == 'empty')
                        echo '<p class="error">Το όνομα της τάξης δεν μπορέι να είναι κενό</p>';
                }
            ?>
            <br>
            <form action="../../includes/admin/classes/deleteclass.inc.php" method="POST" onsubmit="return confirm('!!!Με τη διαγραφή της τάξης θα ΔΙΑΓΡΑΦΟΥΝ ΟΛΕΣ οι ΑΝΑΚΟΙΝΩΣΕΙΣ και τα μαθήματα/χρήστες δεν θα ανοίκουν σε κάποια τάξη πλέον!!!');">
                <input type="hidden" name="id" value="<?= $classId; ?>"/>
                <button type="submit" name="delete" value="delete" class="del-button">Διαγραφή</button>
            </form>
            <br>
            <div class="authors">
                <a href="./writers.php?c=<?= $classId; ?>">Επεξεργασία Συντακτών<img src="../../resources/edit-icon.png"/></a>
            </div>
        </div>
        <div class="class-posts-mb" id="posts-mb">
            <a href="./newpost.php?c=<?= $classId; ?>" class="new-post-button">Νέα Ανακοινώση<img src="../../resources/new.png"/></a>
            <?php

            $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_usage='class' AND post_used_id=$classId ORDER BY post_date DESC");
            if($res->num_rows < 1) echo '<p style="text-align:center;width:100%">Δεν υπάρχουν ανακοινώσεις</p>';
            else while($row = $res->fetch_assoc())
            {
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
                else $author = 'Administrator';

                $visibility = $row['post_visibility'];
                $col = 'hsl(120, 80%, 80%)';
                if($visibility == 0) $col = '#FFC4C4';

                $files = explode(',', $row['post_files']);

                $outfiles = '';
                if(!empty($files) && $files[0] != '')
                {
                    for($i = 0; $i < sizeof($files); $i++)
                    {
                        if(empty($files[$i])) continue;
                        $file = mysqli_real_escape_string($conn, $files[$i]);
                        $uppath = '../../file.php?id=' . $file;
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
                            $outfiles .= '<a class="post-file" href="../../file.php?id=' . $file . '" target="_blank" title="' . $safeName . '"><img src="../../resources/icons/' . $fileIcon . '.png"/><p>' . $safeName . '</p></a>';
                        }
                    }
                    echo '<div class="post" style="background-color: ' . $col .'">';
                            if($_SESSION['type'] == 'ADMIN' || $username == $uname) echo '<a class="post-edit" href="./editpost.php?id=' . $id . '">Επεξεργασία<img src="../../resources/edit-icon.png"/></a>';
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
                            if($_SESSION['type'] == 'ADMIN' || $username == $uname) echo '<a class="post-edit" href="./editpost.php?id=' . $id . '">Επεξεργασία<img src="../../resources/edit-icon.png"/></a>';
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
        <div class="calendar-mb" id="cld-mb">
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
                            echo '<a class="calendar__number today hasevent" href="./calendar.php?c=' . $classId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                            else echo '<a class="calendar__number today" href="./calendar.php?c=' . $classId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                        }
                        else {
                            if(in_array($year . '-' . (int)($month) . '-' . $i, $edat))
                            echo '<a class="calendar__number hasevent" href="./calendar.php?c=' . $classId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                            else echo '<a class="calendar__number" href="./calendar.php?c=' . $classId . '&d=' . $i . '&m=' . (int)$month . '&y=' . $year . '">' . $i . '</a>';
                        }
                    }
                ?>
            </div>
        </div>

        <script>
            let edit = document.getElementById('edit-mb');
            let posts = document.getElementById('posts-mb');
            let calendar = document.getElementById('cld-mb');
            let navbar = document.getElementById('sub-nav-bar');

            function openEdit(){
                edit.style.display = 'block';
                posts.style.display = 'none';
                calendar.style.display = 'none';
                navbar.style.backgroundColor = "hsl(0, 100%, 64%)";
            }
            function openPosts(){
                edit.style.display = 'none';
                posts.style.display = 'block';
                calendar.style.display = 'none';
                navbar.style.backgroundColor = "hsl(125, 85%, 64%)";
            }
            function openCalendar(){
                edit.style.display = 'none';
                posts.style.display = 'none';
                calendar.style.display = 'block';
                navbar.style.backgroundColor = "hsl(200, 95%, 64%)";
            }
            openEdit();
        </script>
    </div>

    <div id="action-hider">
        <img src="../../resources/loading.gif"><br>
        <p>Παρακαλώ περιμένετε..</p>
    </div>

    </div>
    <div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>
