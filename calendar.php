<?php  session_start();

if(!isset($_SESSION['type'])){
    include 'error.php';
    exit();
}

if(!isset($_GET['d']) || !isset($_GET['m']) || !isset($_GET['y'])){
    header("Location: .");
    exit();
}
if(!is_numeric($_GET['d']) || !is_numeric($_GET['m']) || !is_numeric($_GET['y'])){
    header("Location: .");
    exit();
}

date_default_timezone_set('Europe/Athens');
$selMonth = (int)($_GET['m']);
$selMonth = min(max($selMonth, 1), 12);
$selYear = (int)($_GET['y']);

$dim = date('t', strtotime($selYear . '-' . $selMonth . '-01'));

$selDay = (int)($_GET['d']);
$selDay = min(max($selDay, 1), $dim);

include 'includes/dbh.inc.php';
include_once 'includes/config.php';
include_once 'includes/extrasLoader.inc.php';
include_once 'includes/enc.inc.php';

$monthNames = ["Ιανουάριος","Φεβρουάριος","Μάρτιος","Απρίλιος","Μάιος","Ιούνιος","Ιούλιος","Αύγουστος","Σεπτέμβριος","Οκτώβριος","Νοέμβριος","Δεκέμβριος"];
$monthNamesDay = ["Ιανουαρίου","Φεβρουαρίου","Μαρτίου","Απριλίου","Μαΐου","Ιουνίου","Ιουλίου","Αυγούστου","Σεπτεμβρίου","Οκτωβρίου","Νοεμβρίου","Δεκεμβρίου"];
$dayNames = ["Δευτέρα","Τρίτη","Τετάρτη","Πέμπτη","Παρασκευή","Σάββατο","Κυριακή"];

$selEvents = [];

$edat = [];
if($_SESSION['type'] == 'STUDENT'){
    if(isset($_SESSION['user_class'])){
        $class = (int)$_SESSION['user_class'];
        $res = mysqli_query($conn, "SELECT * FROM calendar_events WHERE event_class=$class AND event_subject IS NULL");
        if($res->num_rows > 0)
        while($row = $res->fetch_assoc()){
            $d = $row["event_date"];
            if(!in_array($d, $edat)) $edat[] = $d;

            if($row["event_date"] == ($selYear . '-' . $selMonth . '-' . $selDay))
                $selEvents[] = $row;
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

                if($rowb["event_date"] == ($selYear . '-' . $selMonth . '-' . $selDay))
                $selEvents[] = $rowb;
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

            if($rowb["event_date"] == ($selYear . '-' . $selMonth . '-' . $selDay))
                $selEvents[] = $rowb;
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

            if($rowb["event_date"] == ($selYear . '-' . $selMonth . '-' . $selDay))
                $selEvents[] = $rowb;
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

            if($rowb["event_date"] == ($selYear . '-' . $selMonth . '-' . $selDay))
                $selEvents[] = $rowb;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="favicon.ico" />
    <title><?= $siteName ?> | Ημερολόγιο</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="styles/calendar.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
    <script src="../scripts/getmessages.js?v=<?= $pubFileVer; ?>"></script>
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">

    <div class="desktop">
        <div class="title">Ημερολόγιο</div>
        <div class="class-content">
            <div class="class-calendar">
                <div class="calendar">
                    <?php
                        $year = date("Y", time());
                        $month = date("m", time());
                        $day = date("d", time());
                        
                        $firstDay = date('N', strtotime($selYear . '-' . $selMonth . '-01'));
                        $dayIdx = (int)date("N", strtotime($selYear . '-' . $selMonth . '-' . $selDay));
                    ?>
                    <div class="calendar-top">
                        <a class="calendar-top-left" title="Προηγούμενος Μήνας" href="<?php
                            if($selMonth - 1 < 1) echo "./calendar.php?d=1&m=12&y=" . ($selYear-1);
                            else echo "./calendar.php?d=1&m=" . ($selMonth-1) . "&y=$selYear";
                        ?>"><b>&lt;</b></a>
                        <div class="calendar-top-month"><?= $monthNames[$selMonth-1] . ' ' . $selYear ?></div>
                        <a class="calendar-top-right" title="Επόμενος Μήνας" href="<?php
                            if($selMonth + 1 > 12) echo "./calendar.php?d=1&m=1&y=" . ($selYear+1);
                            else echo "./calendar.php?&d=1&m=" . ($selMonth+1) . "&y=$selYear";
                        ?>"><b>&gt;</b></a>
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
                                if($i == $selDay){
                                    if(in_array($selYear . '-' . $selMonth . '-' . $i, $edat))
                                    echo '<a class="calendar__number selected hasevent">' . $i . '</a>';
                                    else echo '<a class="calendar__number selected">' . $i . '</a>';
                                }
                                elseif($i == $day && $year == $selYear && $month == $selMonth){
                                    if(in_array($selYear . '-' . $selMonth . '-' . $i, $edat))
                                    echo '<a class="calendar__number today hasevent" href="./calendar.php?d=' . $i . '&m=' . $selMonth . '&y=' . $selYear . '">' . $i . '</a>';
                                    else echo '<a class="calendar__number today" href="./calendar.php?d=' . $i . '&m=' . $month . '&y=' . $year . '">' . $i . '</a>';
                                }
                                else {
                                    if(in_array($selYear . '-' . $selMonth . '-' . $i, $edat))
                                    echo '<a class="calendar__number hasevent" href="./calendar.php?d=' . $i . '&m=' . $selMonth . '&y=' . $selYear . '">' . $i . '</a>';
                                    else echo '<a class="calendar__number" href="./calendar.php?d=' . $i . '&m=' . $selMonth . '&y=' . $selYear . '">' . $i . '</a>';
                                }
                            }
                        ?>
                    </div>
                </div>
            </div>
            <div class="selected-day-events">
                <div class="selected-day-name"><?= $dayNames[$dayIdx-1] . " " . $selDay . " " . $monthNamesDay[$selMonth-1] . " " . $selYear; ?></div>
                <div class="day-event-list">
                <?php

                    if(count($selEvents) < 1){
                        echo '<div class="no-event">Δεν υπάρχουν συμβάντα</div>';
                    }

                    for($i = 0; $i < count($selEvents); $i++){

                        $text = $selEvents[$i]["event_text"];
                        $text = decrypt($text);
                        $text = str_replace('<br>', "\\n", $text);
                        $text = htmlspecialchars($text);
                        $text = str_replace('\\n', '<br>', $text);

                        $subj = $selEvents[$i]["event_subject"];
                        $class = $selEvents[$i]["event_class"];

                        $pntName = "";
                        if(isset($class) && !isset($subj)){
                            $res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$class");
                            if($res->num_rows > 0) $pntName = $res->fetch_assoc()["class_name"];
                        }
                        elseif(isset($subj)){
                            $res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$subj");
                            if($res->num_rows > 0) $pntName = decrypt($res->fetch_assoc()["subject_name"]);
                        }

                        $user = $selEvents[$i]["event_user"];
                        $name = $user;
                        $res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$user'");
                        if($res->num_rows > 0) $name = ' - ' . htmlentities(decrypt($res->fetch_assoc()["user_name"]));
                        else $name = '';

                        echo '<div class="event" eventid="' . $selEvents[$i]["event_id"] . '">
                            <div class="event-pointer">' . $pntName .'</div>
                            <div class="event-user">' . $name . '</div>
                            <div class="event-line"></div>
                            <div class="event-text">' . $text . '</div>
                        </div>';
                    }
                ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mobile">
        <br><div class="title">Ημερολόγιο</div>
        <div class="calendar-mb">
            <?php
                $year = date("Y", time());
                $month = date("m", time());
                $day = date("d", time());
                
                $firstDay = date('N', strtotime($selYear . '-' . $selMonth . '-01'));
                $dayIdx = (int)date("N", strtotime($selYear . '-' . $selMonth . '-' . $selDay));
            ?>
            <div class="calendar-top">
                <a class="calendar-top-left" title="Προηγούμενος Μήνας" href="<?php
                    if($selMonth - 1 < 1) echo "./calendar.php?d=1&m=12&y=" . ($selYear-1);
                    else echo "./calendar.php?d=1&m=" . ($selMonth-1) . "&y=$selYear";
                ?>"><b>&lt;</b></a>
                <div class="calendar-top-month"><?= $monthNames[$selMonth-1] . ' ' . $selYear ?></div>
                <a class="calendar-top-right" title="Επόμενος Μήνας" href="<?php
                    if($selMonth + 1 > 12) echo "./calendar.php?d=1&m=1&y=" . ($selYear+1);
                    else echo "./calendar.php?d=1&m=" . ($selMonth+1) . "&y=$selYear";
                ?>"><b>&gt;</b></a>
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
                        if($i == $selDay){
                            if(in_array($selYear . '-' . $selMonth . '-' . $i, $edat))
                            echo '<a class="calendar__number selected hasevent">' . $i . '</a>';
                            else echo '<a class="calendar__number selected">' . $i . '</a>';
                        }
                        elseif($i == $day && $year == $selYear && $month == $selMonth){
                            if(in_array($selYear . '-' . $selMonth . '-' . $i, $edat))
                            echo '<a class="calendar__number today hasevent" href="./calendar.php?d=' . $i . '&m=' . $selMonth . '&y=' . $selYear . '">' . $i . '</a>';
                            else echo '<a class="calendar__number today" href="./calendar.php?d=' . $i . '&m=' . $month . '&y=' . $year . '">' . $i . '</a>';
                        }
                        else {
                            if(in_array($selYear . '-' . $selMonth . '-' . $i, $edat))
                            echo '<a class="calendar__number hasevent" href="./calendar.php?d=' . $i . '&m=' . $selMonth . '&y=' . $selYear . '">' . $i . '</a>';
                            else echo '<a class="calendar__number" href="./calendar.php?d=' . $i . '&m=' . $selMonth . '&y=' . $selYear . '">' . $i . '</a>';
                        }
                    }
                ?>
            </div>
        </div>

        <div class="selected-day-events-mb">
                <div class="selected-day-name"><?= $dayNames[$dayIdx-1] . " " . $selDay . " " . $monthNamesDay[$selMonth-1] . " " . $selYear; ?></div>
                <div class="day-event-list">
                <?php

                    if(count($selEvents) < 1){
                        echo '<div class="no-event">Δεν υπάρχουν συμβάντα</div>';
                    }

                    for($i = 0; $i < count($selEvents); $i++){

                        $text = $selEvents[$i]["event_text"];
                        $text = decrypt($text);
                        $text = str_replace('<br>', "\\n", $text);
                        $text = htmlspecialchars($text);
                        $text = str_replace('\\n', '<br>', $text);

                        $subj = $selEvents[$i]["event_subject"];
                        $class = $selEvents[$i]["event_class"];

                        $pntName = "";
                        if(isset($class) && !isset($subj)){
                            $res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$class");
                            if($res->num_rows > 0) $pntName = $res->fetch_assoc()["class_name"];
                        }
                        elseif(isset($subj)){
                            $res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$subj");
                            if($res->num_rows > 0) $pntName = decrypt($res->fetch_assoc()["subject_name"]);
                        }

                        $user = $selEvents[$i]["event_user"];
                        $name = $user;
                        $res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$user'");
                        if($res->num_rows > 0) $name = ' - ' . htmlentities(decrypt($res->fetch_assoc()["user_name"]));
                        else $name = '';

                        echo '<div class="event" eventid="' . $selEvents[$i]["event_id"] . '">
                            <div class="event-pointer">' . $pntName .'</div>
                            <div class="event-user">' . $name . '</div>
                            <div class="event-line"></div>
                            <div class="event-text">' . $text . '</div>
                        </div>';
                    }
                ?>
                </div>
            </div>
        </div>
    </div>
    <div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>