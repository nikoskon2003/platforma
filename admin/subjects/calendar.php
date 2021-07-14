<?php  session_start();

if(!isset($_SESSION['type'])){
    include '../../error.php';
    exit();
}elseif($_SESSION['type'] !== 'ADMIN'){
    include '../../error.php';
    exit();
}

if(!isset($_GET['s']) || !isset($_GET['d']) || !isset($_GET['m']) || !isset($_GET['y'])){
    header("Location: .");
    exit();
}
if(!is_numeric($_GET['s']) || !is_numeric($_GET['d']) || !is_numeric($_GET['m']) || !is_numeric($_GET['y'])){
    header("Location: .");
    exit();
}

date_default_timezone_set('Europe/Athens');
$subjectId = (int)($_GET['s']);
$selMonth = (int)($_GET['m']);
$selMonth = min(max($selMonth, 1), 12);
$selYear = (int)($_GET['y']);

$dim = date('t', strtotime($selYear . '-' . $selMonth . '-01'));

$selDay = (int)($_GET['d']);
$selDay = min(max($selDay, 1), $dim);

include_once '../../includes/enc.inc.php';
include '../../includes/dbh.inc.php';
$res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$subjectId");
if($res->num_rows < 1){
    header("Location: .");
    exit();
}
$subjectName = htmlentities(decrypt($res->fetch_assoc()['subject_name']));

include_once '../../includes/config.php';
include_once '../../includes/extrasLoader.inc.php';

$monthNames = ["Ιανουάριος","Φεβρουάριος","Μάρτιος","Απρίλιος","Μάιος","Ιούνιος","Ιούλιος","Αύγουστος","Σεπτέμβριος","Οκτώβριος","Νοέμβριος","Δεκέμβριος"];
$monthNamesDay = ["Ιανουαρίου","Φεβρουαρίου","Μαρτίου","Απριλίου","Μαΐου","Ιουνίου","Ιουλίου","Αυγούστου","Σεπτεμβρίου","Οκτωβρίου","Νοεμβρίου","Δεκεμβρίου"];
$dayNames = ["Δευτέρα","Τρίτη","Τετάρτη","Πέμπτη","Παρασκευή","Σάββατο","Κυριακή"];

$selEvents = [];

$edat = [];
$res = mysqli_query($conn, "SELECT * FROM calendar_events WHERE event_subject=$subjectId AND event_class IS NULL ORDER BY event_id DESC");
if($res->num_rows > 0)
while($row = $res->fetch_assoc()){
    $d = $row["event_date"];
    if(!in_array($d, $edat)) $edat[] = $d;

    if($row["event_date"] == ($selYear . '-' . $selMonth . '-' . $selDay))
        $selEvents[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../favicon.ico" />
    <title><?= $siteName ?> | Ημερολόγιο <?= $subjectName; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/subjects/calendar.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">

    <div class="desktop">
        <div class="title">Ημερολόγιο - <?= $subjectName; ?></div>
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
                            if($selMonth - 1 < 1) echo "./calendar.php?s=$subjectId&d=1&m=12&y=" . ($selYear-1);
                            else echo "./calendar.php?s=$subjectId&d=1&m=" . ($selMonth-1) . "&y=$selYear";
                        ?>"><b>&lt;</b></a>
                        <div class="calendar-top-month"><?= $monthNames[$selMonth-1] . ' ' . $selYear ?></div>
                        <a class="calendar-top-right" title="Επόμενος Μήνας" href="<?php
                            if($selMonth + 1 > 12) echo "./calendar.php?s=$subjectId&d=1&m=1&y=" . ($selYear+1);
                            else echo "./calendar.php?s=$subjectId&d=1&m=" . ($selMonth+1) . "&y=$selYear";
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
                                    echo '<a class="calendar__number today hasevent" href="./calendar.php?s=' . $subjectId . '&d=' . $i . '&m=' . $selMonth . '&y=' . $selYear . '">' . $i . '</a>';
                                    else echo '<a class="calendar__number today" href="./calendar.php?s=' . $subjectId . '&d=' . $i . '&m=' . $month . '&y=' . $year . '">' . $i . '</a>';
                                }
                                else {
                                    if(in_array($selYear . '-' . $selMonth . '-' . $i, $edat))
                                    echo '<a class="calendar__number hasevent" href="./calendar.php?s=' . $subjectId . '&d=' . $i . '&m=' . $selMonth . '&y=' . $selYear . '">' . $i . '</a>';
                                    else echo '<a class="calendar__number" href="./calendar.php?s=' . $subjectId . '&d=' . $i . '&m=' . $selMonth . '&y=' . $selYear . '">' . $i . '</a>';
                                }
                            }
                        ?>
                    </div>
                </div>
            </div>
            <div class="selected-day-events">
                <div class="selected-day-name"><?= $dayNames[$dayIdx-1] . " " . $selDay . " " . $monthNamesDay[$selMonth-1] . " " . $selYear; ?></div>
                <div class="new-event-area">
                    <div class="new-event-button">Νέο Συμβάν<img src="../../resources/new.png" /></div>
                    <div class="new-event-inp">
                        <textarea class="new-event-text" placeholder="Κείμενο συμβάντος"></textarea><br>
                        <div class="send-button">Υποβολή</div>
                        <div class="cancel-button">Άκυρο</div>
                    </div>
                </div>
                <div class="day-event-list">
                <?php

                    if(count($selEvents) < 1){
                        echo '<div class="no-event">Δεν υπάρχουν συμβάντα</div>';
                    }

                    for($i = 0; $i < count($selEvents); $i++){

                        $text = $selEvents[$i]["event_text"];
                        $text = decrypt($text);
                        $text = str_replace('<br>', " \\n ", $text);
                        $text = htmlspecialchars($text);
                        $text = str_replace('\\n', '<br>', $text);

                        $subj = $selEvents[$i]["event_subject"];
                        $class = $selEvents[$i]["event_class"];

                        $pntName = "";
                        if(isset($class) && !isset($subj)){
                            $res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$class");
                            if($res->num_rows > 0) $pntName = $res->fetch_assoc()["class_name"] . ' -';
                        }
                        elseif(isset($subj)){
                            $res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$subj");
                            if($res->num_rows > 0) $pntName = decrypt($res->fetch_assoc()["subject_name"]) . ' -';
                        }

                        $user = $selEvents[$i]["event_user"];
                        $name = $user;
                        $res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$user'");
                        if($res->num_rows > 0) $name = htmlentities(decrypt($res->fetch_assoc()["user_name"]));

                        echo '<div class="event" eventid="' . $selEvents[$i]["event_id"] . '">
                            <div class="edit-event" onclick="editEvent(' . $selEvents[$i]["event_id"] . ');">Επεξεργασία</div>
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
        <br><div class="title">Ημερολόγιο - <?= $subjectName; ?></div>
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
                    if($selMonth - 1 < 1) echo "./calendar.php?s=$subjectId&d=1&m=12&y=" . ($selYear-1);
                    else echo "./calendar.php?s=$subjectId&d=1&m=" . ($selMonth-1) . "&y=$selYear";
                ?>"><b>&lt;</b></a>
                <div class="calendar-top-month"><?= $monthNames[$selMonth-1] . ' ' . $selYear ?></div>
                <a class="calendar-top-right" title="Επόμενος Μήνας" href="<?php
                    if($selMonth + 1 > 12) echo "./calendar.php?s=$subjectId&d=1&m=1&y=" . ($selYear+1);
                    else echo "./calendar.php?s=$subjectId&d=1&m=" . ($selMonth+1) . "&y=$selYear";
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
                            echo '<a class="calendar__number today hasevent" href="./calendar.php?s=' . $subjectId . '&d=' . $i . '&m=' . $selMonth . '&y=' . $selYear . '">' . $i . '</a>';
                            else echo '<a class="calendar__number today" href="./calendar.php?s=' . $subjectId . '&d=' . $i . '&m=' . $month . '&y=' . $year . '">' . $i . '</a>';
                        }
                        else {
                            if(in_array($selYear . '-' . $selMonth . '-' . $i, $edat))
                            echo '<a class="calendar__number hasevent" href="./calendar.php?s=' . $subjectId . '&d=' . $i . '&m=' . $selMonth . '&y=' . $selYear . '">' . $i . '</a>';
                            else echo '<a class="calendar__number" href="./calendar.php?s=' . $subjectId . '&d=' . $i . '&m=' . $selMonth . '&y=' . $selYear . '">' . $i . '</a>';
                        }
                    }
                ?>
            </div>
        </div>

        <div class="selected-day-events-mb">
                <div class="selected-day-name"><?= $dayNames[$dayIdx-1] . " " . $selDay . " " . $monthNamesDay[$selMonth-1] . " " . $selYear; ?></div>
                <div class="new-event-area">
                    <div class="new-event-button">Νέο Συμβάν<img src="../../resources/new.png" /></div>
                    <div class="new-event-inp">
                        <textarea class="new-event-text" placeholder="Κείμενο συμβάντος"></textarea><br>
                        <div class="send-button">Υποβολή</div>
                        <div class="cancel-button">Άκυρο</div>
                    </div>
                </div>
                <div class="day-event-list">
                <?php

                    if(count($selEvents) < 1){
                        echo '<div class="no-event">Δεν υπάρχουν συμβάντα</div>';
                    }

                    for($i = 0; $i < count($selEvents); $i++){

                        $text = $selEvents[$i]["event_text"];
                        $text = decrypt($text);
                        $text = str_replace('<br>', " \\n ", $text);
                        $text = htmlspecialchars($text);
                        $text = str_replace('\\n', '<br>', $text);

                        $subj = $selEvents[$i]["event_subject"];
                        $class = $selEvents[$i]["event_class"];

                        $pntName = "";
                        if(isset($class) && !isset($subj)){
                            $res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$class");
                            if($res->num_rows > 0) $pntName = $res->fetch_assoc()["class_name"] . ' -';
                        }
                        elseif(isset($subj)){
                            $res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$subj");
                            if($res->num_rows > 0) $pntName = decrypt($res->fetch_assoc()["subject_name"]) . ' -';
                        }

                        $user = $selEvents[$i]["event_user"];
                        $name = $user;
                        $res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$user'");
                        if($res->num_rows > 0) $name = htmlentities(decrypt($res->fetch_assoc()["user_name"]));

                        echo '<div class="event" eventid="' . $selEvents[$i]["event_id"] . '">
                            <div class="edit-event" onclick="editEvent(' . $selEvents[$i]["event_id"] . ');">Επεξεργασία</div>
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

    <script>
        let newbuttons = document.querySelectorAll(".new-event-button");
        let newevinps = document.querySelectorAll(".new-event-inp");

        newbuttons.forEach(el => el.style.display = "inline-block");
        newbuttons.forEach(el => el.addEventListener("click", (e) => {
            newbuttons.forEach(ela => ela.style.display = "none");
            newevinps.forEach(ela => ela.style.display = "block");
        }));

        document.querySelectorAll(".cancel-button").forEach(el => el.addEventListener("click", (e) => {
            newbuttons.forEach(ela => ela.style.display = "inline-block");
            newevinps.forEach(ela => ela.style.display = "none");
        }));


        document.querySelectorAll(".send-button").forEach(el => el.addEventListener("click", (e) => {
            let text = "";
            document.querySelectorAll(".new-event-text").forEach(ela => {
                if(ela.value.length > text.length) text = ela.value;
            });

            if(text.trim().length == 0) return;
            if(!confirm("Είστε σίγουροι ότι θέλετε να δημιουργήσετε αυτό το συμβάν;")) return;
            newevinps.forEach(ela => ela.style.display = "none");

            let data = new FormData();
            data.append('text', text.trim().replace(new RegExp('\r?\n','g'), '<br>'));
            data.append('s', '<?= $subjectId; ?>');
            data.append('d', '<?= $selDay; ?>');
            data.append('m', '<?= $selMonth; ?>');
            data.append('y', '<?= $selYear; ?>');

            let xhr = new XMLHttpRequest();
            xhr.open('POST', '../../includes/admin/subjects/newevent.inc.php', true);
            xhr.onload = function(e) {
                location.reload();
            }
            document.getElementById('action-hider').style.display = 'block';
            xhr.send(data);
        }));

        let editingEventId = -1;
        let editingOriginalText = '';
        function editEvent(eid){
            if(editingEventId > -1) cancelEditEvent();
            editingEventId = eid;
            let eventConts = document.querySelectorAll('[eventid="' + eid + '"]');
            eventConts.forEach(e => {
                let textCont = e.querySelector('.event-text');
                editingOriginalText = textCont.innerHTML;
                textCont.contentEditable = 'true';
                textCont.classList.add('editable-text');

                let di = document.createElement('div');
                di.classList.add('edit-buttons');
                di.innerHTML = '<div class="edit-send-button" onclick="submitEditEvent();">Υποβολή</div><div class="edit-cancel-button" onclick="cancelEditEvent();">Άκυρο</div><div class="edit-delete-button" onclick="deleteEvent();">Διαγραφή</div>';
                e.insertBefore(di, null);

            });
        }

        function submitEditEvent(){
            if(editingEventId < 0) return;

            let text = '';
            let editingAreas = document.querySelectorAll('.editable-text');
            editingAreas.forEach(e => {
                if(e.textContent.trim() != editingOriginalText.trim())
                    text = convert(e);
            });
            if(text == ''){
                cancelEditEvent();
                return;
            }

            if(!confirm("Είστε σίγουροι ότι θέλετε να ενημερώσετε αυτό το συμβάν;")) return;

            let data = new FormData();
            data.append('text', text.replace(new RegExp('\r?\n','g'), '<br>'));
            data.append('id', editingEventId);

            let xhr = new XMLHttpRequest();
            xhr.open('POST', '../../includes/admin/subjects/editevent.inc.php', true);
            xhr.onload = function(e) {
                location.reload();
            }
            document.getElementById('action-hider').style.display = 'block';
            xhr.send(data);
        }
        function cancelEditEvent(){
            editingEventId = -1;
            let editingAreas = document.querySelectorAll('.editable-text');
            editingAreas.forEach(e => {
                e.innerHTML = editingOriginalText;
                e.contentEditable = 'false';
                e.classList.remove('editable-text');
            });
            let editingButtons = document.querySelectorAll('.edit-buttons');
            editingButtons.forEach(e => e.remove());
        }
        function deleteEvent(){
            if(editingEventId < 0) return;
            if(!confirm("Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το συμβάν;")) return;

            let data = new FormData();
            data.append('id', editingEventId);

            let xhr = new XMLHttpRequest();
            xhr.open('POST', '../../includes/admin/subjects/deleteevent.inc.php', true);
            xhr.onload = function(e) {
                location.reload();
            }
            document.getElementById('action-hider').style.display = 'block';
            xhr.send(data);
        }

        var convert = (function() {var convertElement = function(element) {switch(element.tagName) {case "BR": return "\n"; case "P": case "DIV": return (element.previousSibling ? "\n" : "") + [].map.call(element.childNodes, convertElement).join(""); default: return element.textContent;}};return function(element) {return [].map.call(element.childNodes, convertElement).join("");};})();
    </script>

<div id="action-hider">
    <img src="../../resources/loading.gif"><br>
    <p>Παρακαλώ περιμένετε..</p>
</div>


    </div>
    <div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>