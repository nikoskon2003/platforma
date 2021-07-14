<?php session_start();
include_once '../includes/config.php';

if(!isset($_SESSION['type'])){
    include '../error.php';
    exit();
}
elseif($_SESSION['type'] !== 'STUDENT'){
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

$exp = new DateTime($row['test_expires']);
$now = new DateTime(date('Y-m-d H:i:s', time()));
$expText = $exp->format("d/m/Y H:i");

if($now > $exp){
    header("Location: ./reply.php?id=$testId");
    exit();
}

$username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
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

$testData = json_decode(base64_decode($row['test_data']), JSON_UNESCAPED_UNICODE);
if(json_last_error() != JSON_ERROR_NONE){
    echo 'Υπάρχει πρόβλημα με το test!';
    exit();
}

date_default_timezone_set('Europe/Athens');
$startedTime = time();

$res = mysqli_query($conn, "SELECT * FROM test_responses WHERE response_test=$testId AND response_user='$username'");
if($res->num_rows > 0){
    $row = $res->fetch_assoc();
    if(is_null($row['response_data'])){
        $startedTime = (int)$row['response_start'];
    }
    else {
        header("Location: ./test.php?id=$testId");
        exit();
    }
}
else mysqli_query($conn, "INSERT INTO test_responses (response_test, response_user, response_start) VALUES ($testId, '$username', $startedTime)");

include '../includes/extrasLoader.inc.php';
?>


<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../favicon.ico" />
    <title><?= $siteName; ?> | <?= $testName; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../styles/class/taketest.css?v=<?= $pubFileVer; ?>" type="text/css">
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
            <p class="title"><?= $testName ?></p>
                <div class="test-display">
                <form id="desktopForm" action="../includes/class/submittest.inc.php" method="POST" onsubmit="if(!confirm('Με την υποβολή του test η δυνατότητα επεξεργασίας των απαντήσεων δεν θα είναι διαθέσημη!'))return false;document.getElementById('action-hider').style.display = 'block';">
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

                                $rads .= '<label class="radio"><input type="radio" name="ans-' . $i . '" value="' . ($j+1)  . '"><p class="radio-text">' . $text . '</p></label>';
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

                                $chbx .= '<label class="radio"><input type="checkbox" name="ans-' . $i . '[]" value="' . ($j+1)  . '"><p class="radio-text">' . $text . '</p></label>';
                            }
                            $aDisp = '<div class="question-answer">' . $chbx . '</div>';
                        }
                        elseif($a == 2){
                            $aDisp = '<div class="question-answer"><textarea form="desktopForm" name="ans-' . $i . '" placeholder="Απάντηση..."></textarea></div>';
                        }
                        else continue;

                        echo '<div class="question">' . $qDisp . $aDisp . '</div>';
                    }
                ?>
                    <input type="hidden" name="id" value="<?= $testId; ?>"/>
                    <button class="button" type="submit">Υποβολή</button>
                </form>
            </div>
        </div>

        <div class="mobile">
            <br><br>
            <p class="title"><?= $testName ?></p>
            <div class="test-display">
            <form id="mobileForm" action="../includes/class/submittest.inc.php" method="POST" onsubmit="if(!confirm('Με την υποβολή του test η δυνατότητα επεξεργασίας των απαντήσεων δεν θα είναι διαθέσημη!'))return false;document.getElementById('action-hider').style.display = 'block';">
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

                            $rads .= '<label class="radio"><input type="radio" name="ans-' . $i . '" value="' . ($j+1)  . '"><p class="radio-text">' . $text . '</p></label>';
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

                            $chbx .= '<label class="radio"><input type="checkbox" name="ans-' . $i . '[]" value="' . ($j+1)  . '"><p class="radio-text">' . $text . '</p></label>';
                        }
                        $aDisp = '<div class="question-answer">' . $chbx . '</div>';
                    }
                    elseif($a == 2){
                        $aDisp = '<div class="question-answer"><textarea form="mobileForm" name="ans-' . $i . '" placeholder="Απάντηση..."></textarea></div>';
                    }
                    else continue;

                    echo '<div class="question">' . $qDisp . $aDisp . '</div>';
                }
            ?>
                <input type="hidden" name="id" value="<?= $testId; ?>"/>
                <button class="button" type="submit">Υποβολή</button>
            </form>
        </div>
        <div id="action-hider">
            <img src="../../resources/loading.gif"><br>
            <p>Παρακαλώ περιμένετε..</p>
        </div>
        <div id="counter"></div>
    </div>
    
    <script>
        const startTime = <?= $startedTime; ?>;
        function displayTime(){
            let now = new Date();
            let nowTime = Math.round(now.getTime() / 1000);
            let diff = nowTime - startTime;

            let hours = Math.floor(diff/(60*60));
            diff %= 60*60;
            let minutes = Math.floor(diff/60);
            diff %= 60;
            
            document.getElementById("counter").innerHTML = hours + ':' + ((minutes < 10) ? '0' : '') + minutes + ':' + ((diff < 10) ? '0' : '') + diff;

            setTimeout(displayTime, 1000, window);
        }
        document.getElementById("counter").style.display = 'block';
        displayTime();
    </script>
    <script>
        let viewer = new ViewBigimg();
        document.querySelectorAll(".iv-close").forEach(el => el.onclick = function (e) {document.getElementById("header").style.display = "inline";});
    </script>
    <div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>