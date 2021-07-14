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

include '../includes/enc.inc.php';
include '../includes/dbh.inc.php';
$replyId = -1;
$replyRawData = [];
$replyUserName = '';

$testId = -1;
if(isset($_GET['id'])){
    if(!is_numeric($_GET['id'])){
        include '../error.php';
        exit();
    }
    $testId = (int)$_GET['id'];
}
elseif(isset($_GET['r'])){
    if($_SESSION['type'] == 'STUDENT'){  
        include '../error.php';
        exit();
    }
    if(!is_numeric($_GET['r'])){
        include '../error.php';
        exit();
    }
    $replyId = (int)$_GET['r'];

    $res = mysqli_query($conn, "SELECT * FROM test_responses WHERE response_id=$replyId LIMIT 1");
    if($res->num_rows < 1){
        include '../error.php';
        exit();
    }
    $replyRawData = $res->fetch_assoc();
    $testId = (int)$replyRawData['response_test'];

    $replyUser = $replyRawData['response_user'];
    $res = mysqli_query($conn, "SELECT user_name FROM users WHERE user_username='$replyUser' LIMIT 1");
    if($res->num_rows > 0) $replyUserName = htmlentities(decrypt($res->fetch_assoc()['user_name']));
}
else {
    include '../error.php';
    exit();
}

$res = mysqli_query($conn, "SELECT * FROM tests WHERE test_id=$testId LIMIT 1");
if($res->num_rows < 1){
    header("Location: ./");
    exit();
}
$row = $res->fetch_assoc();
$testName = htmlentities(decrypt($row['test_name']));

$exp = new DateTime($row['test_expires']);
$now = new DateTime(date('Y-m-d H:i:s', time()));

$testSubject = (int)$row['test_subject'];
$testVis = (int)$row['test_visibility'];
$testData = json_decode(base64_decode($row['test_data']), JSON_UNESCAPED_UNICODE);
if(json_last_error() != JSON_ERROR_NONE){
    echo 'Υπάρχει πρόβλημα με το test!';
    exit();
}

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

$userDidNotReply = false;
$replyData = null;

if(isset($replyRawData['response_data']))
    $replyData = json_decode(base64_decode($replyRawData['response_data']), JSON_UNESCAPED_UNICODE);
elseif($_SESSION['type'] == 'STUDENT'){
    $res = mysqli_query($conn, "SELECT * FROM test_responses WHERE response_test=$testId AND response_user='$username'");
    if($res->num_rows < 1){
        if($exp > $now){
            header("Location: ./test.php?id=$testId");
            exit();
        }
        else {
            $userDidNotReply = true;
        }
    }
    $replyRawData = $res->fetch_assoc();

    if(!$userDidNotReply){

        if(is_null($replyRawData['response_end'])){
            if($exp > $now){
                header("Location: ./taketest.php?id=$testId");
                exit();
            }
            else {
                $userDidNotReply = true;
            }
        }

        $replyUser = $replyRawData['response_user'];
        $res = mysqli_query($conn, "SELECT user_name FROM users WHERE user_username='$replyUser' LIMIT 1");
        if($res->num_rows > 0) $replyUserName = htmlentities(decrypt($res->fetch_assoc()['user_name']));

        if(!$userDidNotReply)
            $replyData = json_decode(base64_decode($replyRawData['response_data']), JSON_UNESCAPED_UNICODE);
    }
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
    <link rel="stylesheet" href="../styles/class/reply.css?v=<?= $pubFileVer; ?>" type="text/css">
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
            <?php if($replyData == null && $_SESSION['type'] == 'TEACHER'): ?>
                <p class="title"><?= $testName ?></p>
                <div class="replies-list">
                    <a href="./test.php?id=<?= $testId; ?>" class="button">Πίσω</a>
                    <p class="cat-title">Ολοκληρωμένες Απαντήσεις</p>
                    <?php
                        $res = mysqli_query($conn, "SELECT response_id,response_user FROM test_responses WHERE response_test=$testId AND response_data IS NOT NULL ORDER BY response_end ASC");
                        if($res->num_rows < 1) echo '<p>Δεν υπάρχουν ολοκληρωμένες απαντήσεις!</p>';
                        else while($row = $res->fetch_assoc()){
                            $replyId = (int)$row['response_id'];
                            $replyUser = mysqli_real_escape_string($conn, $row['response_user']);
                            $name = htmlentities($replyUser);
                            $resu = mysqli_query($conn, "SELECT user_name FROM users WHERE user_username='$replyUser' LIMIT 1");
                            if($resu->num_rows > 0) $name = htmlentities(decrypt($resu->fetch_assoc()['user_name']));
                            echo '<a href="./reply.php?r=' . $replyId . '" class="reply">' . $name . '</a>';
                        }
                    ?>
                    <br>
                    <p class="cat-title">Απαντώνται τώρα</p>
                    <?php
                        $res = mysqli_query($conn, "SELECT response_id,response_user FROM test_responses WHERE response_test=$testId AND response_data IS NULL ORDER BY response_start ASC");
                        if($res->num_rows < 1) echo '<p>Δεν υπάρχουν ενεργές απαντήσεις!</p>';
                        else while($row = $res->fetch_assoc()){
                            $replyId = (int)$row['response_id'];
                            $replyUser = mysqli_real_escape_string($conn, $row['response_user']);
                            $name = htmlentities($replyUser);
                            $resu = mysqli_query($conn, "SELECT user_name FROM users WHERE user_username='$replyUser' LIMIT 1");
                            if($resu->num_rows > 0) $name = htmlentities(decrypt($resu->fetch_assoc()['user_name']));
                            echo '<a class="inactive-reply">' . $name . '</a>';
                        }
                    ?>
                </div>
            <?php elseif($_SESSION['type'] == 'STUDENT' && $userDidNotReply): ?>
                <p class="title"><?= $testName ?></p>
                <div class="replies-list">
                    <p class="cat-title">Δυστυχώς δεν απαντήσατε εγκαίρως στο test.</p>
                    <a href="./subject.php?s=<?= $testSubject; ?>" class="button">Πίσω</a>
                </div>
            <?php elseif($_SESSION['type'] == 'TEACHER'): ?>
            <p class="title"><?= $testName ?> - <?= $replyUserName; ?></p>
            <div class="test-display">
                <div class="reply-info">
                    <?php
                    $corr = 0;
                    foreach($replyData as $d){
                        if($d[1] == 0){
                            $corr = -1;
                            echo '<p class="info-title" style="color: orange">Η βαθμολόγηση δεν έχει ολοκληρωθεί!</p>';
                            break;
                        }
                        if($d[1] == 1) $corr++;
                    }
                    if($exp > $now)
                        echo '<p class="info-text" style="color: red;">*Ο μαθητής/τρια θα μπορεί να δει τα αποτελέσματά του μετά τη λήξη του test</p><br>';
                    
                    ?>
                    <a href="./reply.php?id=<?= $testId; ?>" class="button">Πίσω</a><br>
                    <div class="info-cont">
                        <p class="info-title">Ώρα Εκκίνησης:</p>
                        <p class="info-text"><?php
                            date_default_timezone_set('Europe/Athens');
                            echo date('d/m/Y H:i:s', (int)$replyRawData['response_start']);
                        ?></p>
                    </div>
                    <div class="info-cont">
                        <p class="info-title">Ώρα Υποβολής:<p>
                        <p class="info-text"><?php
                            date_default_timezone_set('Europe/Athens');
                            echo date('d/m/Y H:i:s', (int)$replyRawData['response_end']);
                        ?></p>
                    </div>
                    <div class="info-cont">
                        <p class="info-title">Χρόνος Απάντησης:</p>
                        <p class="info-text"><?php
                            date_default_timezone_set('Europe/Athens');
                            $diff = (int)$replyRawData['response_end'] - (int)$replyRawData['response_start'];
                            $days = floor($diff/(60*60*24));
                            $diff %= 60*60*24;
                            $hours = floor($diff/(60*60));
                            $diff %= 60*60;
                            $minutes = floor($diff/60);
                            $seconds = $diff % 60;
                            if($days > 0) echo $days . 'με. ';
                            if($hours > 0) echo $hours . 'ωρ. ';
                            if($minutes > 0) echo $minutes . 'λε. ';
                            if($seconds > 0) echo $seconds . 'δε.';
                        ?></p>
                    </div>
                    <?php
                    if($corr >= 0){
                        echo '<div class="info-cont">
                        <p class="info-title">Βαθμολογία:<p>
                        <p class="info-text">' . $corr . '/' . count($replyData) . '&nbsp;(' . round(($corr/count($replyData)) * 1000)/10 . '%)</p></div>';
                    } 
                    ?>
                </div>
                <form action="../includes/class/ratetest.inc.php" method="POST" onsubmit="document.getElementById('action-hider').style.display = 'block';">
                <?php
                    for($i = 0; $i < count($testData); $i++){
                        $question = $testData[$i];
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
                            for($j = 0; $j < count($question['ad']); $j++){
                                $text = decrypt($question['ad'][$j]);
                                $text = str_replace('<br>', " \\n ", $text);
                                $text = htmlspecialchars($text);
                                $text = formatText($text);
                                $text = str_replace('\\n', '<br>', $text);

                                $rads .= '<label class="radio"><input type="radio" ' . (($j === ((int)$replyData[$i][0]-1)) ? 'checked="checked"' : '') . ' disabled="disabled"><p class="radio-text">' . $text . '</p></label>';
                            }
                            $aDisp = '<div class="question-answer">' . $rads . '</div>';
                        }
                        elseif($a == 1){
                            $chbx = '';
                            for($j = 0; $j < count($question['ad']); $j++){
                                $text = decrypt($question['ad'][$j]);
                                $text = str_replace('<br>', " \\n ", $text);
                                $text = htmlspecialchars($text);
                                $text = formatText($text);
                                $text = str_replace('\\n', '<br>', $text);

                                $chbx .= '<label class="radio"><input type="checkbox" ' . ((in_array($j+1, (array)$replyData[$i][0])) ? 'checked="checked"' : '') . ' disabled="disabled"><p class="radio-text">' . $text . '</p></label>';
                            }
                            $aDisp = '<div class="question-answer">' . $chbx . '</div>';
                        }
                        elseif($a == 2){
                            $text = $replyData[$i][0];
                            $text = str_replace('<br>', " \\n ", $text);
                            $text = htmlspecialchars($text);
                            $text = formatText($text);
                            $text = str_replace('\\n', '<br>', $text);

                            $aDisp = '<div class="question-answer"><p>' . $text . '</p></div>';
                        }
                        else continue;

                        $rateInput = '<div class="grade-cont"><div class="radio-holder">
                            <label class="radio-cont-vis">
                                <input type="radio" ' . (($replyData[$i][1] == 1) ? 'checked="checked"' : '') . ' name="ans-' . $i . '" value="cor">
                                <div class="radio-child cor">Σωστό</div>
                            </label>
                            <label class="radio-cont-vis">
                                <input type="radio" ' . (($replyData[$i][1] == 0) ? 'checked="checked"' : '') . ' name="ans-' . $i . '" value="na">
                                <div class="radio-child na">-</div>
                            </label>
                            <label class="radio-cont-vis">
                                <input type="radio" ' . (($replyData[$i][1] == 2) ? 'checked="checked"' : '') . ' name="ans-' . $i . '" value="wro">
                                <div class="radio-child wro">Λάθος</div>
                            </label>
                        </div></div>';

                        $col = '';
                        if($replyData[$i][1] == 1) $col = 'style="background-color: #ecffe7;"';
                        if($replyData[$i][1] == 2) $col = 'style="background-color: #ffe7e7;"';

                        echo '<div class="question" ' . $col . '>' . $qDisp . $aDisp . $rateInput . '</div>';
                    }
                ?>
                <input type="hidden" name="id" value="<?= $replyId; ?>"/>
                <button type="submit" class="button">Υποβολή</button>
                </form>
            </div>
            <?php elseif($_SESSION['type'] == 'STUDENT'): ?>
            <p class="title"><?= $testName ?></p>
            <div class="test-display">
                <div class="reply-info">
                    <?php
                    $corr = 0;
                    foreach($replyData as $d){
                        if($d[1] == 0){
                            $corr = -1;
                            echo '<p class="info-title" style="color: orange">Η βαθμολόγηση δεν έχει ολοκληρωθεί!</p>';
                            break;
                        }
                        if($d[1] == 1) $corr++;
                    }                    
                    ?>
                    <a href="./subject.php?s=<?= $testSubject; ?>" class="button">Πίσω</a><br>
                    <div class="info-cont">
                        <p class="info-title">Ώρα Εκκίνησης:</p>
                        <p class="info-text"><?php
                            date_default_timezone_set('Europe/Athens');
                            echo date('d/m/Y H:i:s', (int)$replyRawData['response_start']);
                        ?></p>
                    </div>
                    <div class="info-cont">
                        <p class="info-title">Ώρα Υποβολής:<p>
                        <p class="info-text"><?php
                            date_default_timezone_set('Europe/Athens');
                            echo date('d/m/Y H:i:s', (int)$replyRawData['response_end']);
                        ?></p>
                    </div>
                    <div class="info-cont">
                        <p class="info-title">Χρόνος Απάντησης:</p>
                        <p class="info-text"><?php
                            date_default_timezone_set('Europe/Athens');
                            $diff = (int)$replyRawData['response_end'] - (int)$replyRawData['response_start'];
                            $days = floor($diff/(60*60*24));
                            $diff %= 60*60*24;
                            $hours = floor($diff/(60*60));
                            $diff %= 60*60;
                            $minutes = floor($diff/60);
                            $seconds = $diff % 60;
                            if($days > 0) echo $days . 'με. ';
                            if($hours > 0) echo $hours . 'ωρ. ';
                            if($minutes > 0) echo $minutes . 'λε. ';
                            if($seconds > 0) echo $seconds . 'δε.';
                        ?></p>
                    </div>
                    <?php
                    if($corr >= 0){
                        echo '<div class="info-cont">
                        <p class="info-title">Βαθμολογία:<p>
                        <p class="info-text">' . $corr . '/' . count($replyData) . '&nbsp;(' . round(($corr/count($replyData)) * 1000)/10 . '%)</p></div>';
                    } 
                    ?>
                </div>

                <?php
                    for($i = 0; $i < count($testData); $i++){
                        $question = $testData[$i];
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
                            for($j = 0; $j < count($question['ad']); $j++){
                                $text = decrypt($question['ad'][$j]);
                                $text = str_replace('<br>', " \\n ", $text);
                                $text = htmlspecialchars($text);
                                $text = formatText($text);
                                $text = str_replace('\\n', '<br>', $text);

                                $rads .= '<label class="radio"><input type="radio" ' . (($j === ((int)$replyData[$i][0]-1)) ? 'checked="checked"' : '') . ' disabled="disabled"><p class="radio-text">' . $text . '</p></label>';
                            }
                            $aDisp = '<div class="question-answer">' . $rads . '</div>';
                        }
                        elseif($a == 1){
                            $chbx = '';
                            for($j = 0; $j < count($question['ad']); $j++){
                                $text = decrypt($question['ad'][$j]);
                                $text = str_replace('<br>', " \\n ", $text);
                                $text = htmlspecialchars($text);
                                $text = formatText($text);
                                $text = str_replace('\\n', '<br>', $text);

                                $chbx .= '<label class="radio"><input type="checkbox" ' . ((in_array($j+1, (array)$replyData[$i][0])) ? 'checked="checked"' : '') . ' disabled="disabled"><p class="radio-text">' . $text . '</p></label>';
                            }
                            $aDisp = '<div class="question-answer">' . $chbx . '</div>';
                        }
                        elseif($a == 2){
                            $text = $replyData[$i][0];
                            $text = str_replace('<br>', " \\n ", $text);
                            $text = htmlspecialchars($text);
                            $text = formatText($text);
                            $text = str_replace('\\n', '<br>', $text);

                            $aDisp = '<div class="question-answer"><p>' . $text . '</p></div>';
                        }
                        else continue;

                        $col = '';
                        if($replyData[$i][1] == 1) $col = 'style="background-color: #ecffe7;"';
                        if($replyData[$i][1] == 2) $col = 'style="background-color: #ffe7e7;"';

                        echo '<div class="question" ' . $col . '>' . $qDisp . $aDisp . '</div>';
                    }
                ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="mobile">
        <br><br>
        <?php if($replyData == null && $_SESSION['type'] == 'TEACHER'): ?>
                <p class="title"><?= $testName ?></p>
                <div class="replies-list">
                    <a href="./test.php?id=<?= $testId; ?>" class="button">Πίσω</a>
                    <p class="cat-title">Ολοκληρωμένες Απαντήσεις</p>
                    <?php
                        $res = mysqli_query($conn, "SELECT response_id,response_user FROM test_responses WHERE response_test=$testId AND response_data IS NOT NULL ORDER BY response_end ASC");
                        if($res->num_rows < 1) echo '<p>Δεν υπάρχουν ολοκληρωμένες απαντήσεις!</p>';
                        else while($row = $res->fetch_assoc()){
                            $replyId = (int)$row['response_id'];
                            $replyUser = mysqli_real_escape_string($conn, $row['response_user']);
                            $name = htmlentities($replyUser);
                            $resu = mysqli_query($conn, "SELECT user_name FROM users WHERE user_username='$replyUser' LIMIT 1");
                            if($resu->num_rows > 0) $name = htmlentities(decrypt($resu->fetch_assoc()['user_name']));
                            echo '<a href="./reply.php?r=' . $replyId . '" class="reply">' . $name . '</a>';
                        }
                    ?>
                    <br>
                    <p class="cat-title">Απαντώνται τώρα</p>
                    <?php
                        $res = mysqli_query($conn, "SELECT response_id,response_user FROM test_responses WHERE response_test=$testId AND response_data IS NULL ORDER BY response_start ASC");
                        if($res->num_rows < 1) echo '<p>Δεν υπάρχουν ενεργές απαντήσεις!</p>';
                        else while($row = $res->fetch_assoc()){
                            $replyId = (int)$row['response_id'];
                            $replyUser = mysqli_real_escape_string($conn, $row['response_user']);
                            $name = htmlentities($replyUser);
                            $resu = mysqli_query($conn, "SELECT user_name FROM users WHERE user_username='$replyUser' LIMIT 1");
                            if($resu->num_rows > 0) $name = htmlentities(decrypt($resu->fetch_assoc()['user_name']));
                            echo '<a class="inactive-reply">' . $name . '</a>';
                        }
                    ?>
                </div>
            <?php elseif($_SESSION['type'] == 'STUDENT' && $userDidNotReply): ?>
                <p class="title"><?= $testName ?></p>
                <div class="replies-list">
                    <p class="cat-title">Δυστυχώς δεν απαντήσατε εγκαίρως στο test.</p>
                    <a href="./subject.php?s=<?= $testSubject; ?>" class="button">Πίσω</a>
                </div>
            <?php elseif($_SESSION['type'] == 'TEACHER'): ?>
            <p class="title"><?= $testName ?> - <?= $replyUserName; ?></p>
            <div class="test-display">
                <div class="reply-info">
                    <?php
                    $corr = 0;
                    foreach($replyData as $d){
                        if($d[1] == 0){
                            $corr = -1;
                            echo '<p class="info-title" style="color: orange">Η βαθμολόγηση δεν έχει ολοκληρωθεί!</p>';
                            break;
                        }
                        if($d[1] == 1) $corr++;
                    }
                    if($exp > $now)
                        echo '<p class="info-text" style="color: red;">*Ο μαθητής/τρια θα μπορεί να δει τα αποτελέσματά του μετά τη λήξη του test</p><br>';
                    
                    ?>
                    <a href="./reply.php?id=<?= $testId; ?>" class="button">Πίσω</a><br>
                    <div class="info-cont">
                        <p class="info-title">Ώρα Εκκίνησης:</p>
                        <p class="info-text"><?php
                            date_default_timezone_set('Europe/Athens');
                            echo date('d/m/Y H:i:s', (int)$replyRawData['response_start']);
                        ?></p>
                    </div>
                    <div class="info-cont">
                        <p class="info-title">Ώρα Υποβολής:<p>
                        <p class="info-text"><?php
                            date_default_timezone_set('Europe/Athens');
                            echo date('d/m/Y H:i:s', (int)$replyRawData['response_end']);
                        ?></p>
                    </div>
                    <div class="info-cont">
                        <p class="info-title">Χρόνος Απάντησης:</p>
                        <p class="info-text"><?php
                            date_default_timezone_set('Europe/Athens');
                            $diff = (int)$replyRawData['response_end'] - (int)$replyRawData['response_start'];
                            $days = floor($diff/(60*60*24));
                            $diff %= 60*60*24;
                            $hours = floor($diff/(60*60));
                            $diff %= 60*60;
                            $minutes = floor($diff/60);
                            $seconds = $diff % 60;
                            if($days > 0) echo $days . 'με. ';
                            if($hours > 0) echo $hours . 'ωρ. ';
                            if($minutes > 0) echo $minutes . 'λε. ';
                            if($seconds > 0) echo $seconds . 'δε.';
                        ?></p>
                    </div>
                    <?php
                    if($corr >= 0){
                        echo '<div class="info-cont">
                        <p class="info-title">Βαθμολογία:<p>
                        <p class="info-text">' . $corr . '/' . count($replyData) . '&nbsp;(' . round(($corr/count($replyData)) * 1000)/10 . '%)</p></div>';
                    } 
                    ?>
                </div>
                <form action="../includes/class/ratetest.inc.php" method="POST" onsubmit="document.getElementById('action-hider').style.display = 'block';">
                <?php
                    for($i = 0; $i < count($testData); $i++){
                        $question = $testData[$i];
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
                            for($j = 0; $j < count($question['ad']); $j++){
                                $text = decrypt($question['ad'][$j]);
                                $text = str_replace('<br>', " \\n ", $text);
                                $text = htmlspecialchars($text);
                                $text = formatText($text);
                                $text = str_replace('\\n', '<br>', $text);

                                $rads .= '<label class="radio"><input type="radio" ' . (($j === ((int)$replyData[$i][0]-1)) ? 'checked="checked"' : '') . ' disabled="disabled"><p class="radio-text">' . $text . '</p></label>';
                            }
                            $aDisp = '<div class="question-answer">' . $rads . '</div>';
                        }
                        elseif($a == 1){
                            $chbx = '';
                            for($j = 0; $j < count($question['ad']); $j++){
                                $text = decrypt($question['ad'][$j]);
                                $text = str_replace('<br>', " \\n ", $text);
                                $text = htmlspecialchars($text);
                                $text = formatText($text);
                                $text = str_replace('\\n', '<br>', $text);

                                $chbx .= '<label class="radio"><input type="checkbox" ' . ((in_array($j+1, (array)$replyData[$i][0])) ? 'checked="checked"' : '') . ' disabled="disabled"><p class="radio-text">' . $text . '</p></label>';
                            }
                            $aDisp = '<div class="question-answer">' . $chbx . '</div>';
                        }
                        elseif($a == 2){
                            $text = $replyData[$i][0];
                            $text = str_replace('<br>', " \\n ", $text);
                            $text = htmlspecialchars($text);
                            $text = formatText($text);
                            $text = str_replace('\\n', '<br>', $text);

                            $aDisp = '<div class="question-answer"><p>' . $text . '</p></div>';
                        }
                        else continue;

                        $rateInput = '<div class="grade-cont"><div class="radio-holder">
                            <label class="radio-cont-vis">
                                <input type="radio" ' . (($replyData[$i][1] == 1) ? 'checked="checked"' : '') . ' name="ans-' . $i . '" value="cor">
                                <div class="radio-child cor">Σωστό</div>
                            </label>
                            <label class="radio-cont-vis">
                                <input type="radio" ' . (($replyData[$i][1] == 0) ? 'checked="checked"' : '') . ' name="ans-' . $i . '" value="na">
                                <div class="radio-child na">-</div>
                            </label>
                            <label class="radio-cont-vis">
                                <input type="radio" ' . (($replyData[$i][1] == 2) ? 'checked="checked"' : '') . ' name="ans-' . $i . '" value="wro">
                                <div class="radio-child wro">Λάθος</div>
                            </label>
                        </div></div>';

                        $col = '';
                        if($replyData[$i][1] == 1) $col = 'style="background-color: #ecffe7;"';
                        if($replyData[$i][1] == 2) $col = 'style="background-color: #ffe7e7;"';

                        echo '<div class="question" ' . $col . '>' . $qDisp . $aDisp . $rateInput . '</div>';
                    }
                ?>
                <input type="hidden" name="id" value="<?= $replyId; ?>"/>
                <button type="submit" class="button">Υποβολή</button>
                </form>
            </div>
            <?php elseif($_SESSION['type'] == 'STUDENT'): ?>
            <p class="title"><?= $testName ?></p>
            <div class="test-display">
                <div class="reply-info">
                    <?php
                    $corr = 0;
                    foreach($replyData as $d){
                        if($d[1] == 0){
                            $corr = -1;
                            echo '<p class="info-title" style="color: orange">Η βαθμολόγηση δεν έχει ολοκληρωθεί!</p>';
                            break;
                        }
                        if($d[1] == 1) $corr++;
                    }                    
                    ?>
                    <a href="./subject.php?s=<?= $testSubject; ?>" class="button">Πίσω</a><br>
                    <div class="info-cont">
                        <p class="info-title">Ώρα Εκκίνησης:</p>
                        <p class="info-text"><?php
                            date_default_timezone_set('Europe/Athens');
                            echo date('d/m/Y H:i:s', (int)$replyRawData['response_start']);
                        ?></p>
                    </div>
                    <div class="info-cont">
                        <p class="info-title">Ώρα Υποβολής:<p>
                        <p class="info-text"><?php
                            date_default_timezone_set('Europe/Athens');
                            echo date('d/m/Y H:i:s', (int)$replyRawData['response_end']);
                        ?></p>
                    </div>
                    <div class="info-cont">
                        <p class="info-title">Χρόνος Απάντησης:</p>
                        <p class="info-text"><?php
                            date_default_timezone_set('Europe/Athens');
                            $diff = (int)$replyRawData['response_end'] - (int)$replyRawData['response_start'];
                            $days = floor($diff/(60*60*24));
                            $diff %= 60*60*24;
                            $hours = floor($diff/(60*60));
                            $diff %= 60*60;
                            $minutes = floor($diff/60);
                            $seconds = $diff % 60;
                            if($days > 0) echo $days . 'με. ';
                            if($hours > 0) echo $hours . 'ωρ. ';
                            if($minutes > 0) echo $minutes . 'λε. ';
                            if($seconds > 0) echo $seconds . 'δε.';
                        ?></p>
                    </div>
                    <?php
                    if($corr >= 0){
                        echo '<div class="info-cont">
                        <p class="info-title">Βαθμολογία:<p>
                        <p class="info-text">' . $corr . '/' . count($replyData) . '&nbsp;(' . round(($corr/count($replyData)) * 1000)/10 . '%)</p></div>';
                    } 
                    ?>
                </div>

                <?php
                    for($i = 0; $i < count($testData); $i++){
                        $question = $testData[$i];
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
                            for($j = 0; $j < count($question['ad']); $j++){
                                $text = decrypt($question['ad'][$j]);
                                $text = str_replace('<br>', " \\n ", $text);
                                $text = htmlspecialchars($text);
                                $text = formatText($text);
                                $text = str_replace('\\n', '<br>', $text);

                                $rads .= '<label class="radio"><input type="radio" ' . (($j === ((int)$replyData[$i][0]-1)) ? 'checked="checked"' : '') . ' disabled="disabled"><p class="radio-text">' . $text . '</p></label>';
                            }
                            $aDisp = '<div class="question-answer">' . $rads . '</div>';
                        }
                        elseif($a == 1){
                            $chbx = '';
                            for($j = 0; $j < count($question['ad']); $j++){
                                $text = decrypt($question['ad'][$j]);
                                $text = str_replace('<br>', " \\n ", $text);
                                $text = htmlspecialchars($text);
                                $text = formatText($text);
                                $text = str_replace('\\n', '<br>', $text);

                                $chbx .= '<label class="radio"><input type="checkbox" ' . ((in_array($j+1, (array)$replyData[$i][0])) ? 'checked="checked"' : '') . ' disabled="disabled"><p class="radio-text">' . $text . '</p></label>';
                            }
                            $aDisp = '<div class="question-answer">' . $chbx . '</div>';
                        }
                        elseif($a == 2){
                            $text = $replyData[$i][0];
                            $text = str_replace('<br>', " \\n ", $text);
                            $text = htmlspecialchars($text);
                            $text = formatText($text);
                            $text = str_replace('\\n', '<br>', $text);

                            $aDisp = '<div class="question-answer"><p>' . $text . '</p></div>';
                        }
                        else continue;

                        $col = '';
                        if($replyData[$i][1] == 1) $col = 'style="background-color: #ecffe7;"';
                        if($replyData[$i][1] == 2) $col = 'style="background-color: #ffe7e7;"';

                        echo '<div class="question" ' . $col . '>' . $qDisp . $aDisp . '</div>';
                    }
                ?>
            </div>
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