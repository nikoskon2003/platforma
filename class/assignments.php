<?php  session_start();
include_once '../includes/config.php';

if(!isset($_SESSION['type'])){
    include '../error.php';
    exit();
}

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

$res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$subjId");
if($res->num_rows < 1){
    header("Location: ./");
    exit();
}
$row = $res->fetch_assoc();
$subjName = htmlentities(decrypt($row['subject_name']));
$subjClass = $row['subject_class'];

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
else {
    include '../error.php';
    exit();
}

if(isset($_GET['a'])){
    if(!is_numeric($_GET['a']) && $_GET['a'] !== 'new'){
        header("Location: ./assignments.php?s=$subjId");
        exit();
    }
}

$act = isset($_GET['a']) ? $_GET['a'] : '';

if($act == 'new' && $_SESSION['type'] != 'TEACHER'){
    header("Location: ./assignments.php?s=$subjId");
    exit();
}

$assignmentId;
$assignmentData;
$expireDate;
$contTitle = '';
$dateArr = [];
$expired = false;

$now = new DateTime(date('Y-m-d H:i:s', time()));

if(is_numeric($act)){
    $assignmentId = (int)$_GET['a'];
    $res =  mysqli_query($conn, "SELECT * FROM assignments WHERE assignment_id=$assignmentId");
    if($res->num_rows < 1){
        header("Location: ./assignments.php?s=$subjId");
        exit();
    }
    
    $assignmentData = $res->fetch_assoc();
    $contTitle = htmlentities(decrypt($assignmentData['assignment_name']));

    $expireDate = $assignmentData['assignment_expires'];
    $aSplit = explode(' ', $expireDate);
    $bSplit = explode('-', $aSplit[0]);
    $cSplit = explode(':', $aSplit[1]);

    $dateArr[] = $bSplit[0];
    $dateArr[] = $bSplit[1];
    $dateArr[] = $bSplit[2];
    $dateArr[] = $cSplit[0];
    $dateArr[] = $cSplit[1];

    $expireDate = new DateTime($expireDate);
    $expired = $expireDate < $now;
}

date_default_timezone_set('Europe/Athens');
include_once '../includes/extrasLoader.inc.php';
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../favicon.ico" />
    <title><?= $siteName; ?> | Φάκελος Εργασιών - <?= $subjName; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../styles/class/assignments.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
    <style>
        .assignments-cont::before {content: "<?= html_entity_decode($contTitle); ?>" !important;}
        #assignment-cont::before {content: "<?= html_entity_decode($contTitle); ?>" !important;}
    </style>
    <script src="../scripts/getmessages.js?v=<?= $pubFileVer; ?>"></script>
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">
        <div class="desktop">
            <p class="title">Φάκελος Εργασιών - <?= $subjName; ?></p>
            <div class="assignments-cont">
                <div class="left-side">
                    <?php

                    if($_SESSION['type'] == 'TEACHER')
                        echo '<div class="new-assignment"><a href="./assignments.php?s=' .  $subjId . '&a=new">Νέα Εργασία<img src="../resources/new.png"/></a></div>';
                    else echo '<br>';

                    $res = mysqli_query($conn, "SELECT * FROM assignments WHERE assignment_subject=$subjId ORDER BY assignment_expires DESC");
                    if($res->num_rows < 1) echo '<p style="width:100%;font-family:\'Noto Sans\';text-align:center">Δεν υπάρχουν εργασίες</p>';
                    else while($row = $res->fetch_assoc()){
                        $aid = (int)$row['assignment_id'];
                        $name = htmlentities(decrypt($row['assignment_name']));

                        $user = mysqli_real_escape_string($conn, $row['assignment_user']);
                        $expires = $row['assignment_expires'];

                        $exp = new DateTime($expires);

                        $date = $exp->format("d/m/Y H:i");

                        if($exp < $now){
                            echo '
                            <a class="assignment" href="./assignments.php?s=' . $subjId . '&a=' . $aid . '" style="color: #fa3320;">
                                <p class="assignment-name">' . $name . '</p>
                                <p class="assignment-end">Προθεσμία: ' . $date . '</p>
                            </a>';
                        }
                        else {
                            echo '
                            <a class="assignment" href="./assignments.php?s=' . $subjId . '&a=' . $aid . '">
                                <p class="assignment-name">' . $name . '</p>
                                <p class="assignment-end">Προθεσμία: ' . $date . '</p>
                            </a>';
                        }
                    }
                    ?>
                    
                </div>
                <div class="right-side">
                    <?php if($act === ''): ?>
                        <div class="non-selected-text">Επιλέξτε μια εργασία!</div>
                    <?php elseif($act === 'new'): ?>
                    <p class="new-assignment-title">Νέα Εργασία</p>
                    <div class="new-assignment-form">
                        <form id="new-form-desktop" action="../includes/class/newassignment.inc.php" method="POST" onsubmit="return verifyForm(0);">
                            <p class="field-name-text">Όνομα Εργασίας</p>
                            <input type="text" name="name" placeholder="Όνομα"/><br><br>
                            <p class="field-name-text">Λήξη Προθεσμίας Υποβολών</p>
                            <div class="time">
                                <div class="time-cont">
                                    <p class="time-title">Χρονιά</p>
                                    <input type="number" name="year" placeholder="<?= (int)date("Y", time()); ?>"/>
                                </div>

                                <div class="time-cont">
                                    <p class="time-title">Μήνας</p>
                                    <input type="number" name="month" placeholder="<?= (int)date("m", time()); ?>" min="1" max="12"/>
                                </div>

                                <div class="time-cont">
                                    <p class="time-title">Ημέρα</p>
                                    <input type="number" name="day" placeholder="<?= (int)date("d", time()); ?>" min="1" max="31"/>
                                </div>

                                <div class="time-cont">
                                    <p class="time-title">Ώρα</p>
                                    <input type="number" name="hour" placeholder="<?= (int)date("H", time()); ?>" min="0" max="23"/>
                                </div>

                                <div class="time-cont">
                                    <p class="time-title">Λεπτό</p>
                                    <input type="number" name="minute" placeholder="<?= (int)date("i", time()); ?>" min="0" max="59"/>
                                </div>
                            </div>
                            <input type="hidden" name="s" value="<?= $subjId; ?>"/>
                            <button class="submit-button" type="submit" name="submit" value="submit">Υποβολή</button>
                        </form>
                    </div>
                    <?php elseif($_SESSION['type'] == 'TEACHER'): ?>
                        <label class="assignment-edit-area">
                            <p class="edit-text-title">Επεξεργασία Εργασίας</p>
                            <input type="checkbox" class="toggle-switch"/>
                            <div class="edit-area-cont">
                                <form id="edit-form-desktop" action="../includes/class/editassignment.inc.php" method="POST" onsubmit="return verifyForm(2);">
                                    <p class="field-name-text">Όνομα Εργασίας</p>
                                    <input type="text" name="name" placeholder="Όνομα" value="<?= $contTitle; ?>"/><br><br>
                                    <p class="field-name-text">Λήξη Προθεσμίας Υποβολών</p>
                                    <div class="time">
                                        <div class="time-cont">
                                            <p class="time-title">Χρονιά</p>
                                            <input type="number" name="year" placeholder="<?= (int)date("Y", time()); ?>" value="<?= $dateArr[0] ?>"/>
                                        </div>

                                        <div class="time-cont">
                                            <p class="time-title">Μήνας</p>
                                            <input type="number" name="month" placeholder="<?= (int)date("m", time()); ?>" value="<?= $dateArr[1] ?>" min="1" max="12"/>
                                        </div>

                                        <div class="time-cont">
                                            <p class="time-title">Ημέρα</p>
                                            <input type="number" name="day" placeholder="<?= (int)date("d", time()); ?>" value="<?= $dateArr[2] ?>" min="1" max="31"/>
                                        </div>

                                        <div class="time-cont">
                                            <p class="time-title">Ώρα</p>
                                            <input type="number" name="hour" placeholder="<?= (int)date("H", time()); ?>" value="<?= $dateArr[3] ?>" min="0" max="23"/>
                                        </div>

                                        <div class="time-cont">
                                            <p class="time-title">Λεπτό</p>
                                            <input type="number" name="minute" placeholder="<?= (int)date("i", time()); ?>" value="<?= $dateArr[4] ?>" min="0" max="59"/>
                                        </div>
                                    </div>
                                    <input type="hidden" name="id" value="<?= $assignmentId; ?>"/>
                                    <button class="submit-button" type="submit" name="submit" value="submit">Υποβολή</button>
                                </form>
                                <form action="../includes/class/deleteassignment.inc.php" method="POST" onsubmit="if(!confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτήν την εργασία; Θα διαγραφούν όλα τα δεδομένα σχετικά με αυτήν!'))return false;document.getElementById('action-hider').style.display = 'block';">
                                    <input type="hidden" name="id" value="<?= $assignmentId; ?>"/>
                                    <button class="submit-button" type="submit" name="submit" value="submit" style="color:red">Διαγραφή</button>
                                </form>
                            </div>
                        </label>
                        
                        <div class="student-replies">
                            <?php
                                $users = [];
                                $files = [];
                                $res = mysqli_query($conn, "SELECT * FROM assignment_responses WHERE response_assignment=$assignmentId ORDER BY response_id ASC");
                                if($res->num_rows < 1) echo '<p style="font-family: \'Noto Sans\'">Δεν υπάρχουν εργασίες</p>';
                                else while($row = $res->fetch_assoc()){
                                    $user = $row['response_user'];
                                    $fileId = $row['response_file'];
                                    $fileName = $row['response_file_name'];
                                    $fileDate = $row['response_date'];

                                    if(!in_array($user, $users)) $users[] = $user;
                                    $files[$user][] = ["id" => $fileId, "name" => $fileName, "date" => $fileDate];
                                }

                                foreach($users as $user){
				    $uName = "";
                                    $usName = mysqli_real_escape_string($conn, $user);
                                    $res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$usName' LIMIT 1");
                                    if($res->num_rows > 0) $uName = htmlentities(decrypt($res->fetch_assoc()['user_name']));
                                    else $uName = htmlentities($usName);

                                    echo '<label class="reply">
                                        <p class="reply-name">' . $uName . '</p>
					<a class="reply-msg" href="../messages/messages.php?u=' . urlencode($usName) . '" target="_blank">
                                            <img src="../resources/message.png"/>
                                        </a>
                                        <input type="checkbox" class="reply-toggle"/>
                                        <div class="reply-files">';

                                    foreach($files[$user] as $file){
                                        $fileId = $file["id"];
                                        $fileName = htmlentities($file["name"]);
                                        $fileExt = iconFromExtension($fileName);
                                        $date = new DateTime($file["date"]);
                                        $fileDate = htmlentities($date->format("d/m/Y H:i:s"));

                                        echo '<a class="file' . (($date > $expireDate) ? ' exp' : '') . '"  href="./assignmentfile.php?id=' . $fileId . '" target="_blank" title="Λήψη Αρχείου"><img src="../resources/icons/' . $fileExt . '.png"/><p class="file-name" title="' . $fileName . '">' . $fileName . '</p><p class="file-date">' . $fileDate . '</p></a>';
                                    }

                                    echo '<p style="font-size:0px">&nbsp;</p></div></label>';
                                }
                            ?>
                        </div>
                    <?php else: ?>
                        <?php if(!$expired || true): ?>
                        <div class="new-reply-cont">
                            <div class="new-reply-title">Προσθήκη αρχείου εργασίας</div>
                            <form action="../includes/class/uploadassignmentresponse.inc.php" method="POST" enctype="multipart/form-data" onsubmit="document.getElementById('action-hider').style.display = 'block';">
                                <input type="hidden" name="assignment" value="<?= $assignmentId ?>"/>
                                <input type="file" name="file" /><br>
                                <button type="submit" class="new-button">Υποβολή</button>
                            </form>
                        </div>
                        <?php endif; ?>

                        <div class="reply-files-cont">
                            <?php
                                $res = mysqli_query($conn, "SELECT * FROM assignment_responses WHERE response_assignment=$assignmentId AND response_user='$username' ORDER BY response_id ASC");
                                if($res->num_rows < 1) echo '<p style="width: 100%; font-family: \'Noto Sans\';text-align:center">Δεν έχετε υποβάλει αρχεία</p>';
                                else while($row = $res->fetch_assoc()){
                                    $fileId = $row["response_file"];
                                    $fileName = htmlentities($row["response_file_name"]);
                                    $fileExt = iconFromExtension($fileName);
                                    $date = new DateTime($row["response_date"]);
                                    $fileDate = htmlentities($date->format("d/m/Y H:i:s"));

                                    echo '<a class="file' . (($date > $expireDate) ? ' exp' : '') . '"  href="./assignmentfile.php?id=' . $fileId . '" target="_blank" title="Λήψη Αρχείου"><img src="../resources/icons/' . $fileExt . '.png"/><p class="file-name" title="' . $fileName . '">' . $fileName . '</p><p class="file-date">' . $fileDate . '</p>';
                                        if(!$expired || true) echo '<div class="delete-file">
                                            <form action="../includes/class/deleteassignmentresponse.inc.php" method="POST" onsubmit="if(!confirm(\'Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το αρχείο;\'))return false;document.getElementById(\'action-hider\').style.display = \'block\';">
                                                <input type="hidden" name="id" value="' . urlencode($fileId) . '"/>
                                                <button type="submit" title="Διαγραφή Αρχείου" class="delete-response">&times;</button>
                                            </form>
                                        </div>';
                                    echo '</a>';
                                }
				//echo '<br><p style="margin-top: 10px">Εργασίες: ' . $res->num_rows . '</p>';
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mobile">
            <p class="title-mb">Φάκελος Εργασιών - <?= $subjName; ?></p>
            <div class="assignments-cont-mb">
            <?php if($act == ''): ?>
                <div id="assignments-list">
                    <?php
                    if($_SESSION['type'] == 'TEACHER') echo '<div class="new-assignment"><a href="./assignments.php?s=' . $subjId . '&a=new">Νέα Εργασία<img src="../resources/new.png"/></a></div>';

                    $res = mysqli_query($conn, "SELECT * FROM assignments WHERE assignment_subject=$subjId ORDER BY assignment_expires DESC");
                    if($res->num_rows < 1) echo '<p style="width:100%;font-family:\'Noto Sans\';text-align:center">Δεν υπάρχουν εργασίες</p>';
                    else while($row = $res->fetch_assoc()){
                        $aid = (int)$row['assignment_id'];
                        $name = htmlentities(decrypt($row['assignment_name']));

                        $user = mysqli_real_escape_string($conn, $row['assignment_user']);
                        $expires = $row['assignment_expires'];

                        $exp = new DateTime($expires);
                        $now = new DateTime(date('Y-m-d H:i:s', time()));

                        $date = $exp->format("d/m/Y H:i");

                        if($exp < $now){
                            echo '
                            <a class="assignment" href="./assignments.php?s=' . $subjId . '&a=' . $aid . '" style="color: #fa3320;">
                                <p class="assignment-name">' . $name . '</p>
                                <p class="assignment-end">Προθεσμία: ' . $date . '</p>
                            </a>';
                        }
                        else {
                            echo '
                            <a class="assignment" href="./assignments.php?s=' . $subjId . '&a=' . $aid . '">
                                <p class="assignment-name">' . $name . '</p>
                                <p class="assignment-end">Προθεσμία: ' . $date . '</p>
                            </a>';
                        }
                    }
                    ?>
                </div>
            <?php else: ?>
                <div class="sub-navigation">
                    <div class="clicked-cont" onclick="openList();" id="to-list">Λίστα Εργασιών</div>
                    <div onclick="openAssignment();" id="to-assignment"><?= ($act == 'new') ? 'Νέα Εργασία' : 'Εργασία' ?></div>
                </div>

                <div id="assignments-list">
                    <?php
                    if($_SESSION['type'] == 'TEACHER') echo '<div class="new-assignment"><a href="./assignments.php?s=' . $subjId . '&a=new">Νέα Εργασία<img src="../resources/new.png"/></a></div>';

                    $res = mysqli_query($conn, "SELECT * FROM assignments WHERE assignment_subject=$subjId ORDER BY assignment_expires DESC");
                    if($res->num_rows < 1) echo '<p style="width:100%;font-family:\'Noto Sans\';text-align:center">Δεν υπάρχουν εργασίες</p>';
                    else while($row = $res->fetch_assoc()){
                        $aid = (int)$row['assignment_id'];
                        $name = htmlentities(decrypt($row['assignment_name']));

                        $user = mysqli_real_escape_string($conn, $row['assignment_user']);
                        $expires = $row['assignment_expires'];

                        $exp = new DateTime($expires);
                        $now = new DateTime(date('Y-m-d H:i:s', time()));

                        $date = $exp->format("d/m/Y H:i");

                        if($exp < $now){
                            echo '
                            <a class="assignment" href="./assignments.php?s=' . $subjId . '&a=' . $aid . '" style="color: #fa3320;">
                                <p class="assignment-name">' . $name . '</p>
                                <p class="assignment-end">Προθεσμία: ' . $date . '</p>
                            </a>';
                        }
                        else {
                            echo '
                            <a class="assignment" href="./assignments.php?s=' . $subjId . '&a=' . $aid . '">
                                <p class="assignment-name">' . $name . '</p>
                                <p class="assignment-end">Προθεσμία: ' . $date . '</p>
                            </a>';
                        }
                    }
                    ?>
                </div>

                <div id="assignment-cont">
                    <?php if($act == 'new'): ?>
                        <p class="new-assignment-title">Νέα Εργασία</p>
                        <div class="new-assignment-form">
                            <form id="new-form-mobile" action="../includes/class/newassignment.inc.php" method="POST" onsubmit="return verifyForm(1);">
                                <p class="field-name-text">Όνομα Εργασίας</p>
                                <input type="text" name="name" placeholder="Όνομα"/><br><br>
                                <p class="field-name-text">Λήξη Προθεσμίας Υποβολών</p>
                                <div class="time">
                                    <div class="time-cont">
                                        <p class="time-title">Χρονιά</p>
                                        <input type="number" name="year" placeholder="<?= (int)date("Y", time()); ?>"/>
                                    </div>

                                    <div class="time-cont">
                                        <p class="time-title">Μήνας</p>
                                        <input type="number" name="month" placeholder="<?= (int)date("m", time()); ?>" min="1" max="12"/>
                                    </div>

                                    <div class="time-cont">
                                        <p class="time-title">Ημέρα</p>
                                        <input type="number" name="day" placeholder="<?= (int)date("d", time()); ?>" min="1" max="31"/>
                                    </div>

                                    <div class="time-cont">
                                        <p class="time-title">Ώρα</p>
                                        <input type="number" name="hour" placeholder="<?= (int)date("H", time()); ?>" min="0" max="23"/>
                                    </div>

                                    <div class="time-cont">
                                        <p class="time-title">Λεπτό</p>
                                        <input type="number" name="minute" placeholder="<?= (int)date("i", time()); ?>" min="0" max="59"/>
                                    </div>
                                </div>
                                <input type="hidden" name="s" value="<?= $subjId; ?>"/>
                                <button class="submit-button" type="submit" name="submit" value="submit">Υποβολή</button>
                            </form>
                        </div>
                    <?php elseif($_SESSION['type'] == 'TEACHER'): ?>
                        <label class="assignment-edit-area">
                            <p class="edit-text-title">Επεξεργασία Εργασίας</p>
                            <input type="checkbox" class="toggle-switch"/>
                            <div class="edit-area-cont-mb">
                                <form id="edit-form-mobile" action="../includes/class/editassignment.inc.php" method="POST" onsubmit="return verifyForm(3);">
                                    <p class="field-name-text">Όνομα Εργασίας</p>
                                    <input type="text" name="name" placeholder="Όνομα" value="<?= $contTitle; ?>"/><br><br>
                                    <p class="field-name-text">Λήξη Προθεσμίας Υποβολών</p>
                                    <div class="time">
                                        <div class="time-cont">
                                            <p class="time-title">Χρονιά</p>
                                            <input type="number" name="year" placeholder="<?= (int)date("Y", time()); ?>" value="<?= $dateArr[0] ?>"/>
                                        </div>

                                        <div class="time-cont">
                                            <p class="time-title">Μήνας</p>
                                            <input type="number" name="month" placeholder="<?= (int)date("m", time()); ?>" value="<?= $dateArr[1] ?>" min="1" max="12"/>
                                        </div>

                                        <div class="time-cont">
                                            <p class="time-title">Ημέρα</p>
                                            <input type="number" name="day" placeholder="<?= (int)date("d", time()); ?>" value="<?= $dateArr[2] ?>" min="1" max="31"/>
                                        </div>

                                        <div class="time-cont">
                                            <p class="time-title">Ώρα</p>
                                            <input type="number" name="hour" placeholder="<?= (int)date("H", time()); ?>" value="<?= $dateArr[3] ?>" min="0" max="23"/>
                                        </div>

                                        <div class="time-cont">
                                            <p class="time-title">Λεπτό</p>
                                            <input type="number" name="minute" placeholder="<?= (int)date("i", time()); ?>" value="<?= $dateArr[4] ?>" min="0" max="59"/>
                                        </div>
                                    </div>
                                    <input type="hidden" name="id" value="<?= $assignmentId; ?>"/>
                                    <button class="submit-button" type="submit" name="submit" value="submit">Υποβολή</button>
                                </form>
                                <form action="../includes/class/deleteassignment.inc.php" method="POST" onsubmit="if(!confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτήν την εργασία; Θα διαγραφούν όλα τα δεδομένα σχετικά με αυτήν!'))return false;document.getElementById('action-hider').style.display = 'block';">
                                    <input type="hidden" name="id" value="<?= $assignmentId; ?>"/>
                                    <button class="submit-button" type="submit" name="submit" value="submit" style="color:red">Διαγραφή</button>
                                </form>
                            </div>
                        </label>
                        <div class="student-replies">
                            <?php
                                $users = [];
                                $files = [];
                                $res = mysqli_query($conn, "SELECT * FROM assignment_responses WHERE response_assignment=$assignmentId ORDER BY response_id ASC");
                                if($res->num_rows < 1) echo '<p style="font-family: \'Noto Sans\'">Δεν υπάρχουν εργασίες</p>';
                                else while($row = $res->fetch_assoc()){
                                    $user = $row['response_user'];
                                    $fileId = $row['response_file'];
                                    $fileName = $row['response_file_name'];
                                    $fileDate = $row['response_date'];

                                    if(!in_array($user, $users)) $users[] = $user;
                                    $files[$user][] = ["id" => $fileId, "name" => $fileName, "date" => $fileDate];
                                }

                                foreach($users as $user){
				    $uName = "";
                                    $usName = mysqli_real_escape_string($conn, $user);
                                    $res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$usName' LIMIT 1");
                                    if($res->num_rows > 0) $uName = htmlentities(decrypt($res->fetch_assoc()['user_name']));
                                    else $uName = htmlentities($usName);

                                    echo '<label class="reply">
                                        <p class="reply-name">' . $uName . '</p>
					<a class="reply-msg" href="../messages/messages.php?u=' . urlencode($usName) . '" target="_blank">
                                            <img src="../resources/message.png"/>
                                        </a>
                                        <input type="checkbox" class="reply-toggle"/>
                                        <div class="reply-files">';

                                    foreach($files[$user] as $file){
                                        $fileId = $file["id"];
                                        $fileName = htmlentities($file["name"]);
                                        $fileExt = iconFromExtension($fileName);
                                        $date = new DateTime($file["date"]);
                                        $fileDate = htmlentities($date->format("d/m/Y H:i:s"));

                                        echo '<a class="file' . (($date > $expireDate) ? ' exp' : '') . '"  href="./assignmentfile.php?id=' . $fileId . '" target="_blank" title="Λήψη Αρχείου"><img src="../resources/icons/' . $fileExt . '.png"/><p class="file-name" title="' . $fileName . '">' . $fileName . '</p><p class="file-date">' . $fileDate . '</p></a>';
                                    }

                                    echo '<p style="font-size:0px">&nbsp;</p></div></label>';
                                }
                            ?>
                        </div>

                    <?php else: ?>
                        <?php if(!$expired || true): ?>
                        <div class="new-reply-cont">
                            <div class="new-reply-title">Προσθήκη αρχείου εργασίας</div>
                            <form action="../includes/class/uploadassignmentresponse.inc.php" method="POST" enctype="multipart/form-data" onsubmit="document.getElementById('action-hider').style.display = 'block';">
                                <input type="hidden" name="assignment" value="<?= $assignmentId ?>"/>
                                <input type="file" name="file" /><br>
                                <button type="submit" class="new-button">Υποβολή</button>
                            </form>
                        </div>
                        <?php endif; ?>

                        <div class="reply-files-cont">
                            <?php
                                $res = mysqli_query($conn, "SELECT * FROM assignment_responses WHERE response_assignment=$assignmentId AND response_user='$username' ORDER BY response_id ASC");
                                if($res->num_rows < 1) echo '<p style="width: 100%; font-family: \'Noto Sans\';text-align:center">Δεν έχετε υποβάλει αρχεία</p>';
                                else while($row = $res->fetch_assoc()){
                                    $fileId = $row["response_file"];
                                    $fileName = htmlentities($row["response_file_name"]);
                                    $fileExt = iconFromExtension($fileName);
                                    $date = new DateTime($row["response_date"]);
                                    $fileDate = htmlentities($date->format("d/m/Y H:i:s"));

                                    echo '<a class="file' . (($date > $expireDate) ? ' exp' : '') . '"  href="./assignmentfile.php?id=' . $fileId . '" target="_blank" title="Λήψη Αρχείου"><img src="../resources/icons/' . $fileExt . '.png"/><p class="file-name" title="' . $fileName . '">' . $fileName . '</p><p class="file-date">' . $fileDate . '</p>';
                                        if(!$expired || true) echo '<div class="delete-file">
                                            <form action="../includes/class/deleteassignmentresponse.inc.php" method="POST" onsubmit="if(!confirm(\'Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το αρχείο;\'))return false;document.getElementById(\'action-hider\').style.display = \'block\';">
                                                <input type="hidden" name="id" value="' . urlencode($fileId) . '"/>
                                                <button type="submit" title="Διαγραφή Αρχείου" class="delete-response">&times;</button>
                                            </form>
                                        </div>';
                                    echo '</a>';
                                }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            </div>
        </div>

        <?php if($act != ''): ?>
        <script>
            function openList(){
                document.getElementById('assignments-list').style.display = 'block';
                document.getElementById('assignment-cont').style.display = 'none';
                
                document.getElementById('to-list').classList.add('clicked-cont');
                document.getElementById('to-assignment').classList.remove('clicked-cont');
            }
            function openAssignment(){
                document.getElementById('assignments-list').style.display = 'none';
                document.getElementById('assignment-cont').style.display = 'block';
                
                document.getElementById('to-list').classList.remove('clicked-cont');
                document.getElementById('to-assignment').classList.add('clicked-cont');
            }
            openAssignment();
        </script>
        <?php endif; ?>

        <script>
            function verifyForm(type){
                let form;
                if(type == 0)
                    form = document.getElementById("new-form-desktop");
                else if(type == 1)
                    form = document.getElementById("new-form-mobile");
                else if(type == 2)
                    form = document.getElementById("edit-form-desktop");
                else if(type == 3)
                    form = document.getElementById("edit-form-mobile");
                else return false;

                let name = form.querySelector('[name="name"]');
                let year = form.querySelector('[name="year"]');
                let month = form.querySelector('[name="month"]');
                let day = form.querySelector('[name="day"]');
                let hour = form.querySelector('[name="hour"]');
                let minute = form.querySelector('[name="minute"]');

                if(name.value.trim() == ''){
                    redElem(name);
                    name.value = '';
                    setTimeout(function() { alert("Το όνομα δεν μπορεί να είναι κενό!"); }, 5);
                    return false;
                }

                name = name.value.trim();

                if(isNaN(year.value) || year.value == ''){
                    redElem(year);
                    year.value = '';
                    setTimeout(function() { alert("Η χρονιά δεν μπορεί να είναι κενή!"); }, 5);
                    return false;
                }

                year = parseInt(year.value);

                if(isNaN(month.value) || month.value == ''){
                    redElem(month);
                    month.value = '';
                    setTimeout(function() { alert("Ο μήνας δεν μπορεί να είναι κενός!"); }, 5);
                    return false;
                }
                if(parseInt(month.value) < 1 || parseInt(month.value) > 12){
                    redElem(month);
                    setTimeout(function() { alert("Ο μήνας πρέπει να είναι μεταξύ 1 (Ιανουάριος) και 12 (Δεκέμβριος)!"); }, 5);
                    return false;
                }

                month = parseInt(month.value);

                if(isNaN(day.value) || day.value == ''){
                    redElem(day);
                    day.value = '';
                    setTimeout(function() { alert("Η ημέρα δεν μπορεί να είναι κενή!"); }, 5);
                    return false;
                }
                let dim = parseInt(new Date(year, month, 0).getDate());
                if(parseInt(day.value) < 1 || parseInt(day.value) > dim){
                    redElem(day);
                    setTimeout(function() { alert("Η ημέρα πρέπει να είναι μεταξύ 1 και " + dim + "!"); }, 5);
                    return false;
                }

                if(isNaN(hour.value) || hour.value == ''){
                    redElem(hour);
                    hour.value = '';
                    setTimeout(function() { alert("Η ώρα δεν μπορεί να είναι κενή!"); }, 5);
                    return false;
                }
                if(parseInt(hour.value) < 0 || parseInt(hour.value) > 23){
                    redElem(hour);
                    setTimeout(function() { alert("Η ώρα πρέπει να είναι μεταξύ 0 και 23!"); }, 5);
                    return false;
                }

                if(isNaN(minute.value) || minute.value == ''){
                    redElem(minute);
                    minute.value = '';
                    setTimeout(function() { alert("Τα λεπτά δεν μπορεί να είναι κενά!"); }, 5);
                    return false;
                }
                if(parseInt(minute.value) < 0 || parseInt(minute.value) > 59){
                    redElem(minute);
                    setTimeout(function() { alert("Τα λεπτά πρέπει να είναι μεταξύ 0 και 59!"); }, 5);
                    return false;
                }

                document.getElementById('action-hider').style.display = 'block';
                return true;
            }

            function redElem(el){
                el.style.borderColor = 'red';
                el.style.borderWidth = '2px';
                el.style.borderRadius = '5px';

                el.addEventListener('click', (e) => {
                    el.style.borderColor = 'rgb(63, 64, 65)';
                    el.style.borderWidth = '1px';
                    el.style.borderRadius = '2px';
                });
            }
        </script>
<div id="action-hider">
    <img src="../resources/loading.gif"><br>
    <p>Παρακαλώ περιμένετε..</p>
</div>
    </div>
    <div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>
