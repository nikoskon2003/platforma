<?php session_start();
include_once '../includes/config.php';

if(!isset($_SESSION['type'])){
    include '../error.php';
    exit();
}
elseif($_SESSION['type'] !== 'TEACHER'){
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
    header("Location: ./");
    exit();
}
$row = $res->fetch_assoc();

$testName = htmlentities(decrypt($row['test_name']));
$testSubject = (int)$row['test_subject'];

$username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
$res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='subject-teacher' AND link_used_id=$testSubject");
if($res->num_rows < 1){
    include '../error.php';
    exit();
}

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
    <title><?= $siteName; ?> | Επεξεργασία <?= $testName; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../styles/class/edittest.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>

    <script src="../scripts/getmessages.js?v=<?= $pubFileVer; ?>"></script>
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">
        <div class="desktop dt">
            <p class="title"><?= $testName ?></p>
            <div class="test-display">
                <div class="test-buttons">
                    <a href="./test.php?id=<?= $testId; ?>" class="button" style="color: red;">Πίσω</a>
                </div>
                <?php
                    $uid = 1;
                    foreach($testData as $question){
                        $q = (int)$question['q'];
                        $a = (int)$question['a'];

                        $qDisp = '';
                        $aDisp = '';

                        if($q == 0){
                            $qd = decrypt($question['qd']);
                            $qd = str_replace('<br>', "\\n ", $qd);
                            $qd = htmlspecialchars($qd);
                            $qd = str_replace("\\n ", "\n", $qd);

                            $qDisp = '<div class="question-text" uid="' . ($uid++) . '"><textarea>' . $qd . '</textarea></div>';
                        }
                        elseif($q == 1){
                            $qd = (int)$question['qd'];
                            $outUrl = '../resources/icons/error.png';
                            $filePath = '../uploads/tests/' . $testId . '/' . $qd;
                            if(file_exists($filePath)){
                                $outUrl = file_get_contents($filePath);
                            }

                            $qDisp = '<div class="question-image" uid="' . ($uid++) . '" ogid="' . $qd . '"><label class="file-label"><input onchange="loadImage(this);" type="file" accept="image/*" style="display: none">Επιλογή Φωτογραφίας</label><img src="' . $outUrl . '"></div>';
                        }
                        else continue;

                        if($a == 0){
                            $rads = '';
                            foreach($question['ad'] as $ad){
                                $text = decrypt($ad);
                                $text = str_replace('<br>', "\\n ", $text);
                                $text = htmlspecialchars($text);
                                $text = str_replace("\\n ", "\n", $text);

                                $rads .= '<label class="radio"><input type="radio" disabled="disabled"><textarea class="radio-text">' . $text . '</textarea></label>';
                            }
                            $aDisp = '<div class="question-answer" uid="' . ($uid++) . '">' . $rads . '</div>';
                        }
                        elseif($a == 1){
                            $chbx = '';
                            foreach($question['ad'] as $ad){
                                $text = decrypt($ad);
                                $text = str_replace('<br>', "\\n ", $text);
                                $text = htmlspecialchars($text);
                                $text = str_replace("\\n ", "\n", $text);

                                $chbx .= '<label class="radio"><input type="checkbox" disabled="disabled"><textarea class="radio-text">' . $text . '</textarea></label>';
                            }
                            $aDisp = '<div class="question-answer" uid="' . ($uid++) . '">' . $chbx . '</div>';
                        }
                        elseif($a == 2){
                            $aDisp = '<div class="question-answer" uid="' . ($uid++) . '"><p>Η λειτουργία κειμένου θα εμφανιστεί αυτόματα στους μαθητές.</p></div>';
                        }
                        else continue;

                        echo '<div class="question">' . $qDisp . $aDisp . '</div>';
                    }
                ?>
                <a class="button" onclick="submitTest(0);">Υποβολή</a>
                <a href="./test.php?id=<?= $testId; ?>" class="button" style="color: red;">Άκυρο</a>
            </div>
        </div>
        
        <div class="mobile dt">
        <br><br>
        <p class="title"><?= $testName ?></p>
            <div class="test-display">
                <div class="test-buttons">
                    <a href="./test.php?id=<?= $testId; ?>" class="button" style="color: red;">Πίσω</a>
                </div>
                <?php
                    $uid = 1;
                    foreach($testData as $question){
                        $q = (int)$question['q'];
                        $a = (int)$question['a'];

                        $qDisp = '';
                        $aDisp = '';

                        if($q == 0){
                            $qd = decrypt($question['qd']);
                            $qd = str_replace('<br>', "\\n ", $qd);
                            $qd = htmlspecialchars($qd);
                            $qd = str_replace("\\n ", "\n", $qd);

                            $qDisp = '<div class="question-text" uid="' . ($uid++) . '"><textarea>' . $qd . '</textarea></div>';
                        }
                        elseif($q == 1){
                            $qd = (int)$question['qd'];
                            $outUrl = '../resources/icons/error.png';
                            $filePath = '../uploads/tests/' . $testId . '/' . $qd;
                            if(file_exists($filePath)){
                                $outUrl = file_get_contents($filePath);
                            }

                            $qDisp = '<div class="question-image" uid="' . ($uid++) . '" ogid="' . $qd . '"><label class="file-label"><input onchange="loadImage(this);" type="file" accept="image/*" style="display: none">Επιλογή Φωτογραφίας</label><img src="' . $outUrl . '"></div>';
                        }
                        else continue;

                        if($a == 0){
                            $rads = '';
                            foreach($question['ad'] as $ad){
                                $text = decrypt($ad);
                                $text = str_replace('<br>', "\\n ", $text);
                                $text = htmlspecialchars($text);
                                $text = str_replace("\\n ", "\n", $text);

                                $rads .= '<label class="radio"><input type="radio" disabled="disabled"><textarea class="radio-text">' . $text . '</textarea></label>';
                            }
                            $aDisp = '<div class="question-answer" uid="' . ($uid++) . '">' . $rads . '</div>';
                        }
                        elseif($a == 1){
                            $chbx = '';
                            foreach($question['ad'] as $ad){
                                $text = decrypt($ad);
                                $text = str_replace('<br>', "\\n ", $text);
                                $text = htmlspecialchars($text);
                                $text = str_replace("\\n ", "\n", $text);

                                $chbx .= '<label class="radio"><input type="checkbox" disabled="disabled"><textarea class="radio-text">' . $text . '</textarea></label>';
                            }
                            $aDisp = '<div class="question-answer" uid="' . ($uid++) . '">' . $chbx . '</div>';
                        }
                        elseif($a == 2){
                            $aDisp = '<div class="question-answer" uid="' . ($uid++) . '"><p>Η λειτουργία κειμένου θα εμφανιστεί αυτόματα στους μαθητές.</p></div>';
                        }
                        else continue;

                        echo '<div class="question">' . $qDisp . $aDisp . '</div>';
                    }
                ?>
                <a class="button" onclick="submitTest(1);">Υποβολή</a>
                <a href="./test.php?id=<?= $testId; ?>" class="button" style="color: red;">Άκυρο</a>
            </div>
        </div>

        <div id="action-hider">
            <img src="../resources/loading.gif"><br>
            <p>Υποβολή test...</p>
        </div>

        <script>
            function loadImage(elem) {
                let uid = parseInt(elem.parentElement.parentElement.getAttribute("uid"));
                if(isNaN(uid)) return;

                let filesToUpload = elem.files;

                if(filesToUpload.length < 1){
                    document.querySelectorAll('[uid="' + uid + '"].question-image').forEach(el => {
                        el.querySelector('img').src = '../resources/icons/image.png';
                        el.querySelector('img').classList.add('edited');
                    });
                    return;
                }

                var file = filesToUpload[0];

                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = new Image();

                    img.onload = function(ie){

                        var canvas = document.createElement("canvas");
                        var ctx = canvas.getContext("2d");
                        ctx.drawImage(img, 0, 0);

                        var MAX_WIDTH = 300;
                        var MAX_HEIGHT = 300;

                        var width = img.width;
                        var height = img.height;

                        canvas.width = width;
                        canvas.height = height;
                        var ctx = canvas.getContext("2d");
                        ctx.drawImage(img, 0, 0, width, height);

                        var dataurl = canvas.toDataURL("image/png");
                        document.querySelectorAll('[uid="' + uid + '"].question-image').forEach(el => {
                            el.querySelector('img').src = dataurl;
                            el.querySelector('img').classList.add('edited');
                        });
                    }
                    img.src = e.target.result;
                }

                reader.readAsDataURL(file);
            }

            let finalTestData = [];

            function processTest(from){
                document.querySelectorAll('.test-display').forEach(elm => elm.querySelectorAll(':not(.whatever-idk)').forEach(el => el.removeAttribute('style')));
                let par;
                if(from == 0)
                    par = document.querySelector('.desktop.dt').querySelector('.test-display');
                else if(from == 1)
                    par = document.querySelector('.mobile.dt').querySelector('.test-display');

                let out = [];
                let ret = false;
                par.querySelectorAll('.question').forEach(el => {
                    if(ret) return;

                    let tmpl = {qd: '', ad: []};

                    let txt = el.querySelector('.question-text');
                    if(txt != null){
                        let elm = txt.querySelector('textarea');
                        if(elm == null) return;
                        let text = elm.value.trim().replace(new RegExp('\r?\n','g'), '<br>');

                        if(text == null || text == ''){
                            elm.style.border = '2px solid red';
                            alert('Το κείμενο δεν μπορεί να είναι κενό!');
                            ret = true;
                            return;
                        }

                        tmpl.qd = text;
                    }
                    else {
                        let img = el.querySelector('.question-image');
                        if(img != null){
                            let elm = img.querySelector('img');
                            if(elm == null) return;
                            let src = elm.src;

                            if(src.endsWith('/image.png') || !src.startsWith('data:image/')) {
                                elm.parentElement.style.border = '2px solid red';
                                alert('Η φωτογραφία δεν μπορεί να είναι κενή!');
                                ret = true;
                                return;
                            }

                            tmpl.qd = parseInt(img.getAttribute('ogid'));
                        }
                        else return;
                    }

                    let ans = el.querySelector('.question-answer');
                    let rads = ans.querySelectorAll('.radio');
                    if(rads.length > 0) rads.forEach(el => {
                        if(ret) return;

                        let elme = el.querySelector('textarea');
                        if(elme == null) return;
                        let text = elme.value.trim().replace(new RegExp('\r?\n','g'), '<br>');

                        if(text == null || text == ''){
                            elme.style.border = '2px solid red';
                            alert('Το κείμενο δεν μπορεί να είναι κενό!');
                            ret = true;
                            return;
                        }

                        tmpl.ad.push(text);
                    });
                    if(ret) return;

                    out.push(tmpl);
                });
                if(ret) return false;

                finalTestData = out;
                return true;
            }

            function submitTest(from){
                if(!confirm("Θέλετε σίγουρα να επεξεργαστείτε το test;")) return;
                if(!processTest(from)) return;

                let data = new FormData();
                data.append('id', '<?= $testId; ?>');
                data.append('data', JSON.stringify(finalTestData));

                let xhr = new XMLHttpRequest();
                xhr.open('POST', '../includes/class/edittestdata.inc.php', true);
                xhr.onload = function(e) {
                    if(e.currentTarget.responseText.startsWith('ok')){
                        let imgs = document.querySelector('.test-display').querySelectorAll('.question-image');
                        let arr = [];
                        imgs.forEach(el => arr.push(parseInt(el.getAttribute('ogid'))));
                        uploadImages(arr);
                    }
                    else window.location = './test.php?id=<?= $testId; ?>';
                }

                let blocker = document.getElementById('action-hider');
                blocker.style.display = 'block';
                let dispP = blocker.querySelector('p');
                dispP.innerHTML = 'Μεταφόρτωση στοιχείων test...';

                xhr.upload.addEventListener("progress", (e) => {
                    let prc = Math.round(e.loaded / e.total * 1000)/10;
                    dispP.innerHTML = 'Μεταφόρτωση στοιχείων test (' + prc + '%)';
                }, true);

                xhr.send(data);
            }

            function uploadImages(images){
                if(images.length < 1){ 
                    window.location = './test.php?id=<?= $testId; ?>';
                    return;
                }

                let dispP = document.getElementById('action-hider').querySelector('p');
                dispP.innerHTML = 'Μεταφόρτωση φωτογραφιών (Απομένουν: ' + images.length + ')...';

                let iid = images.shift();

                let image = document.querySelector('[ogid="' + iid + '"].question-image');
                if(image == null){
                    uploadImages(images);
                    return;
                }
                image = image.querySelector('img.edited');
                if(image == null){
                    uploadImages(images);
                    return;
                }
                if(image.src.endsWith('/image.png') || !image.src.startsWith('data:image/')) {
                    uploadImages(images);
                    return;
                }

                let data = new FormData();
                data.append('tid', <?= $testId; ?>);
                data.append('img', iid);
                data.append('data', image.src);

                let xhr = new XMLHttpRequest();
                xhr.open('POST', '../includes/class/uploadtestimg.inc.php', true);
                xhr.onload = function(e) {
                    uploadImages(images);
                }

                xhr.upload.addEventListener("progress", (e) => {
                    let prc = Math.round(e.loaded / e.total * 1000)/10;
                    dispP.innerHTML = 'Μεταφόρτωση φωτογραφιών (' + prc + '%) (Απομένουν: ' + (images.length+1) + ')';
                });

                xhr.send(data);
            }
        </script>

    </div>
    <div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>