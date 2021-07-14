<?php session_start();
include_once '../includes/config.php';

if(!isset($_SESSION['type'])){
    include '../error.php';
    exit();
}
elseif($_SESSION['type'] !== 'TEACHER' && $_SESSION['type'] !== 'STUDENT'){
    include '../error.php';
    exit();
}


if(!isset($_GET['id'])){
    header("Location: ./");
    exit();
}
if(!is_numeric($_GET['id'])){
    header("Location: ./");
    exit();
}

include '../includes/enc.inc.php';
include '../includes/dbh.inc.php';
$testId = (int)mysqli_real_escape_string($conn, $_GET['id']);

$res = mysqli_query($conn, "SELECT * FROM tests WHERE test_id=$testId LIMIT 1");
if($res->num_rows < 1){
    include '../error.php';
    exit();
}
$row = $res->fetch_assoc();

$testName = htmlentities(decrypt($row['test_name']));
$testSubject = (int)$row['test_subject'];
$testVis = (int)$row['test_visibility'];

$username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
if($_SESSION['type'] == 'STUDENT'){

    if($testVis == 0) {
        include '../error.php';
        exit();
    }

    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='subject-student' AND link_used_id=$testSubject");
    if($res->num_rows < 1){
        if(!is_null($_SESSION['user_class'])){
            $res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$testSubject LIMIT 1");
            if($res->num_rows < 1){
                include '../error.php';
                exit();
            }
            $subjClass = $res->fetch_assoc()['subject_class'];
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
    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='subject-teacher' AND link_used_id=$testSubject");
    if($res->num_rows < 1){
        include '../error.php';
        exit();
    }
}

$exp = new DateTime($row['test_expires']);
$now = new DateTime(date('Y-m-d H:i:s', time()));
$expText = $exp->format("d/m/Y H:i");

$testData = json_decode(base64_decode($row['test_data']), JSON_UNESCAPED_UNICODE);
if(json_last_error() != JSON_ERROR_NONE){
    echo 'Υπάρχει πρόβλημα με το test!';
    exit();
}

include '../includes/extrasLoader.inc.php';
?>


<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../favicon.ico" />
    <title><?= $siteName; ?> | <?= $testName; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../styles/class/test.css?v=<?= $pubFileVer; ?>" type="text/css">
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
        <?php if($_SESSION['type'] == 'TEACHER'): ?>
        <div class="desktop">
            <p class="title"><?= $testName ?></p>
            <div class="test-info">
                <a href="./subject.php?s=<?= $testSubject; ?>" class="button">Πίσω</a><br><br>
                <form action="../includes/class/edittestinfo.inc.php" method="POST" onsubmit="if(!confirm('Είστε σίγουροι ότι θέλετε να αλλάξετε τις πληροφορίες του test;'))return false;document.getElementById('action-hider').style.display = 'block';">
                    <p class="field-name-text">Όνομα Test</p>
                    <input type="text" name="name" placeholder="Όνομα" value="<?= $testName; ?>"/>
                    <p class="field-name-text">Λήξη Προθεσμίας Υποβολών</p>
                    <div class="time">
                        <div class="time-cont">
                            <p class="time-title">Χρονιά</p>
                            <input type="number" name="year" placeholder="<?= (int)$exp->format("Y"); ?>" value="<?= (int)$exp->format("Y"); ?>"/>
                        </div>

                        <div class="time-cont">
                            <p class="time-title">Μήνας</p>
                            <input type="number" name="month" placeholder="<?= (int)$exp->format("m"); ?>" value="<?= (int)$exp->format("m"); ?>" min="1" max="12"/>
                        </div>

                        <div class="time-cont">
                            <p class="time-title">Ημέρα</p>
                            <input type="number" name="day" placeholder="<?= (int)$exp->format("d"); ?>" value="<?= (int)$exp->format("d"); ?>" min="1" max="31"/>
                        </div>

                        <div class="time-cont">
                            <p class="time-title">Ώρα</p>
                            <input type="number" name="hour" placeholder="<?= (int)$exp->format("H"); ?>" value="<?= (int)$exp->format("H"); ?>" min="0" max="23"/>
                        </div>

                        <div class="time-cont">
                            <p class="time-title">Λεπτό</p>
                            <input type="number" name="minute" placeholder="<?= (int)$exp->format("i"); ?>" value="<?= (int)$exp->format("i"); ?>" min="0" max="59"/>
                        </div>
                    </div>
                    <div class="field-name-text">Ορατότητα</div>
                    <div class="radio-holder">
                        <label class="radio-cont-vis">
                            <input type="radio" <?= ($testVis == 1) ? 'checked="checked"' : '' ?> name="visibility" value="all">
                            <div class="radio-child">Όλοι</div>
                        </label>
                        <label class="radio-cont-vis">
                            <input type="radio" <?= ($testVis == 0) ? 'checked="checked"' : '' ?> name="visibility" value="none">
                            <div class="radio-child">Κανένας</div>
                        </label>
                    </div>
                    <input type="hidden" name="id" value="<?= $testId; ?>"/>
                    <button type="submit" class="button">Υποβολή</button>
                </form>
            </div>

            <div class="test-display">
                <div class="test-buttons">
                    <a href="./reply.php?id=<?= $testId; ?>" class="button">Απαντήσεις</a>
                    <a href="./edittest.php?id=<?= $testId; ?>" class="button">Επεξεργασία<img src="../resources/edit-icon.png" /></a>
                </div>
                <?php
                    foreach($testData as $question){
                        $q = (int)$question['q'];
                        $a = (int)$question['a'];

                        $qDisp = '';
                        $aDisp = '';

                        if($q == 0){
                            $qd = decrypt($question['qd']);
                            $qd = str_replace('<br>', " \\n ", $qd);
                            $qd = htmlspecialchars($qd);
                            $qd = formatText($qd);
                            $qd = str_replace('\\n', '<br>', $qd);

                            $qDisp = '<div class="question-text"><p>' . $qd . '</p></div>';
                        }
                        elseif($q == 1){
                            $qd = (int)$question['qd'];
                            $outUrl = '../resources/icons/error.png';
                            $filePath = '../uploads/tests/' . $testId . '/' . $qd;
                            if(file_exists($filePath)){
                                $outUrl = file_get_contents($filePath);
                            }

                            $qDisp = '<div class="question-image">
                                <img id="img-' . $qd . '" src="' . $outUrl . '">
                                <script>
                                    document.getElementById("img-' . $qd . '").onclick = function (e) {
                                        viewer.show(e.target.src);
                                        document.getElementById("header").style.display = "none";
                                    }
                                </script>
                            </div>';
                        }
                        else continue;

                        if($a == 0){
                            $rads = '';
                            foreach($question['ad'] as $ad){
                                $text = decrypt($ad);
                                $text = str_replace('<br>', " \\n ", $text);
                                $text = htmlspecialchars($text);
                                $text = formatText($text);
                                $text = str_replace('\\n', '<br>', $text);

                                $rads .= '<label class="radio"><input type="radio" disabled="disabled"><p class="radio-text">' . $text . '</p></label>';
                            }
                            $aDisp = '<div class="question-answer">' . $rads . '</div>';
                        }
                        elseif($a == 1){
                            $chbx = '';
                            foreach($question['ad'] as $ad){
                                $text = decrypt($ad);
                                $text = str_replace('<br>', " \\n ", $text);
                                $text = htmlspecialchars($text);
                                $text = formatText($text);
                                $text = str_replace('\\n', '<br>', $text);

                                $chbx .= '<label class="radio"><input type="checkbox" disabled="disabled"><p class="radio-text">' . $text . '</p></label>';
                            }
                            $aDisp = '<div class="question-answer">' . $chbx . '</div>';
                        }
                        elseif($a == 2){
                            $aDisp = '<div class="question-answer"><textarea disabled="disabled" placeholder="Απάντηση..."></textarea></div>';
                        }
                        else continue;

                        echo '<div class="question">' . $qDisp . $aDisp . '</div>';
                    }
                ?>
                <form action="../includes/class/deletetest.inc.php" method="POST" onsubmit="if(!confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε το test και όλα τα δεδομένα του;'))return false;document.getElementById('action-hider').style.display = 'block';">
                    <input type="hidden" name="id" value="<?= $testId; ?>"/>
                    <button type="submit" class="button" style="color:red">Διαγραφή</button>
                </form>
            </div>
        </div>

        <div class="mobile">
        <br><br>
        <p class="title"><?= $testName ?></p>
            <div class="test-info">
                <a href="./subject.php?s=<?= $testSubject; ?>" class="button">Πίσω</a><br><br>
                <form action="../includes/class/edittestinfo.inc.php" method="POST" onsubmit="if(!confirm('Είστε σίγουροι ότι θέλετε να αλλάξετε τις πληροφορίες του test;'))return false;document.getElementById('action-hider').style.display = 'block';">
                    <p class="field-name-text">Όνομα Test</p>
                    <input type="text" name="name" placeholder="Όνομα" value="<?= $testName; ?>"/>
                    <p class="field-name-text">Λήξη Προθεσμίας Υποβολών</p>
                    <div class="time">
                        <div class="time-cont">
                            <p class="time-title">Χρονιά</p>
                            <input type="number" name="year" placeholder="<?= (int)$exp->format("Y"); ?>" value="<?= (int)$exp->format("Y"); ?>"/>
                        </div>

                        <div class="time-cont">
                            <p class="time-title">Μήνας</p>
                            <input type="number" name="month" placeholder="<?= (int)$exp->format("m"); ?>" value="<?= (int)$exp->format("m"); ?>" min="1" max="12"/>
                        </div>

                        <div class="time-cont">
                            <p class="time-title">Ημέρα</p>
                            <input type="number" name="day" placeholder="<?= (int)$exp->format("d"); ?>" value="<?= (int)$exp->format("d"); ?>" min="1" max="31"/>
                        </div>

                        <div class="time-cont">
                            <p class="time-title">Ώρα</p>
                            <input type="number" name="hour" placeholder="<?= (int)$exp->format("H"); ?>" value="<?= (int)$exp->format("H"); ?>" min="0" max="23"/>
                        </div>

                        <div class="time-cont">
                            <p class="time-title">Λεπτό</p>
                            <input type="number" name="minute" placeholder="<?= (int)$exp->format("i"); ?>" value="<?= (int)$exp->format("i"); ?>" min="0" max="59"/>
                        </div>
                    </div>
                    <div class="field-name-text">Ορατότητα</div>
                    <div class="radio-holder">
                        <label class="radio-cont-vis">
                            <input type="radio" <?= ($testVis == 1) ? 'checked="checked"' : '' ?> name="visibility" value="all">
                            <div class="radio-child">Όλοι</div>
                        </label>
                        <label class="radio-cont-vis">
                            <input type="radio" <?= ($testVis == 0) ? 'checked="checked"' : '' ?> name="visibility" value="none">
                            <div class="radio-child">Κανένας</div>
                        </label>
                    </div>
                    <input type="hidden" name="id" value="<?= $testId; ?>"/>
                    <button type="submit" class="button">Υποβολή</button>
                </form>
            </div>

            <div class="test-display">
                <div class="test-buttons">
                    <a href="./reply.php?id=<?= $testId; ?>" class="button">Απαντήσεις</a>
                    <a href="./edittest.php?id=<?= $testId; ?>" class="button">Επεξεργασία<img src="../resources/edit-icon.png" /></a>
                </div>
                <?php
                    foreach($testData as $question){
                        $q = (int)$question['q'];
                        $a = (int)$question['a'];

                        $qDisp = '';
                        $aDisp = '';

                        if($q == 0){
                            $qd = decrypt($question['qd']);
                            $qd = str_replace('<br>', " \\n ", $qd);
                            $qd = htmlspecialchars($qd);
                            $qd = formatText($qd);
                            $qd = str_replace('\\n', '<br>', $qd);

                            $qDisp = '<div class="question-text"><p>' . $qd . '</p></div>';
                        }
                        elseif($q == 1){
                            $qd = (int)$question['qd'];
                            $outUrl = '../resources/icons/error.png';
                            $filePath = '../uploads/tests/' . $testId . '/' . $qd;
                            if(file_exists($filePath)){
                                $outUrl = file_get_contents($filePath);
                            }

                            $qDisp = '<div class="question-image">
                                <img id="mb-img-' . $qd . '" src="' . $outUrl . '">
                                <script>
                                    document.getElementById("mb-img-' . $qd . '").onclick = function (e) {
                                        viewer.show(e.target.src);
                                        document.getElementById("header").style.display = "none";
                                    }
                                </script>
                            </div>';
                        }
                        else continue;

                        if($a == 0){
                            $rads = '';
                            foreach($question['ad'] as $ad){
                                $text = decrypt($ad);
                                $text = str_replace('<br>', " \\n ", $text);
                                $text = htmlspecialchars($text);
                                $text = formatText($text);
                                $text = str_replace('\\n', '<br>', $text);

                                $rads .= '<label class="radio"><input type="radio" disabled="disabled"><p class="radio-text">' . $text . '</p></label>';
                            }
                            $aDisp = '<div class="question-answer">' . $rads . '</div>';
                        }
                        elseif($a == 1){
                            $chbx = '';
                            foreach($question['ad'] as $ad){
                                $text = decrypt($ad);
                                $text = str_replace('<br>', " \\n ", $text);
                                $text = htmlspecialchars($text);
                                $text = formatText($text);
                                $text = str_replace('\\n', '<br>', $text);

                                $chbx .= '<label class="radio"><input type="checkbox" disabled="disabled"><p class="radio-text">' . $text . '</p></label>';
                            }
                            $aDisp = '<div class="question-answer">' . $chbx . '</div>';
                        }
                        elseif($a == 2){
                            $aDisp = '<div class="question-answer"><textarea disabled="disabled" placeholder="Απάντηση..."></textarea></div>';
                        }
                        else continue;

                        echo '<div class="question">' . $qDisp . $aDisp . '</div>';
                    }
                ?>
                <form action="../includes/class/deletetest.inc.php" method="POST" onsubmit="if(!confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε το test και όλα τα δεδομένα του;'))return false;document.getElementById('action-hider').style.display = 'block';">
                    <input type="hidden" name="id" value="<?= $testId; ?>"/>
                    <button type="submit" class="button" style="color:red">Διαγραφή</button>
                </form>
            </div>
        </div>
        <?php else: ?>

            <?php
                if($now > $exp){
                    header("Location: ./reply.php?id=$testId");
                    exit();
                }
                else {
                    $res = mysqli_query($conn, "SELECT * FROM test_responses WHERE response_test=$testId AND response_user='$username' LIMIT 1");
                    if($res->num_rows > 0){
                        if(is_null($res->fetch_assoc()['response_data'])){
                            header("Location: ./taketest.php?id=$testId");
                            exit();
                        }
                        else {
                            echo '<div class="desktop"><p class="title">' . $testName . '</p><div class="test-info">';

                            echo '<div class="student-msg">Παρακαλώ περιμένετε να λήξει η προθεσμία του test για να δείτε τα αποτελέσματά σας.</div>';
                            echo '<br><br><a href="./subject.php?s=' . $testSubject . '" class="button">Πίσω</a>';
                        
                            echo '</div></div><div class="mobile"><br><p class="title">' . $testName . '</p><div class="test-info">';

                            echo '<div class="student-msg">Παρακαλώ περιμένετε να λήξει η προθεσμία του test για να δείτε τα αποτελέσματά σας.</div>';
                            echo '<br><br><a href="./subject.php?s=' . $testSubject . '" class="button">Πίσω</a>';

                            echo '</div></div>';
                        }
                    }
                    else {
                        echo '<div class="desktop"><p class="title">' . $testName . '</p><div class="test-info">';

                        echo '<div class="student-msg">Προσοχή! Με το που ξεκινήσετε το test, θα χρονομετρηθεί η ώρα που θα σας πάρει να το ολοκληρώσετε και θα είναι ορατή στον καθηγητή!</div>';
                        echo '<br><br><a href="./subject.php?s=' . $testSubject . '" class="button">Πίσω</a>&nbsp;&nbsp;&nbsp;<a href="./taketest.php?id=' . $testId . '" class="button">Εκκίνηση</a>';
                    
                        echo '</div></div><div class="mobile"><br><p class="title">' . $testName . '</p><div class="test-info">';

                        echo '<div class="student-msg">Προσοχή! Με το που ξεκινήσετε το test, θα χρονομετρηθεί η ώρα που θα σας πάρει να το ολοκληρώσετε και θα είναι ορατή στον καθηγητή!</div>';
                        echo '<br><br><a href="./subject.php?s=' . $testSubject . '" class="button">Πίσω</a>&nbsp;&nbsp;&nbsp;<a href="./taketest.php?id=' . $testId . '" class="button">Εκκίνηση</a>';

                        echo '</div></div>';
                    }
                }
            ?>
        <?php endif; ?>

        <div id="action-hider">
    <img src="../../resources/loading.gif"><br>
    <p>Παρακαλώ περιμένετε..</p>
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