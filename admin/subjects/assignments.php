<?php  session_start();
include_once '../../includes/config.php';

if(!isset($_SESSION['type'])){
    include '../../error.php';
    exit();
}elseif($_SESSION['type'] !== 'ADMIN'){
    include '../../error.php';
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

include '../../includes/enc.inc.php';
include '../../includes/dbh.inc.php';
$subjId = (int)mysqli_real_escape_string($conn, $_GET['s']);

$res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$subjId");
if($res->num_rows < 1){
    header("Location: ./");
    exit();
}
$row = $res->fetch_assoc();
$subjName = htmlentities(decrypt($row['subject_name']));
$subjClass = $row['subject_class'];

if(isset($_GET['a'])){
    if(!is_numeric($_GET['a']) && $_GET['a'] !== 'new'){
        header("Location: ./assignments.php?s=$subjId");
        exit();
    }
}

$act = isset($_GET['a']) ? $_GET['a'] : '';

$assignmentId;
$assignmentData;
$expireDate;
$contTitle = '';
$dateArr = [];

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
}

date_default_timezone_set('Europe/Athens');
include_once '../../includes/extrasLoader.inc.php';
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../favicon.ico" />
    <title><?= $siteName; ?> | ?????????????? ???????????????? - <?= $subjName; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/subjects/assignments.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
    <style>
        .assignments-cont::before {content: "<?= html_entity_decode($contTitle); ?>" !important;}
        #assignment-cont::before {content: "<?= html_entity_decode($contTitle); ?>" !important;}
    </style>
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">
        <div class="desktop">
            <p class="title">?????????????? ???????????????? - <?= $subjName; ?></p>
            <div class="assignments-cont">
                <div class="left-side">
                    <div class="new-assignment"><a href="./assignments.php?s=<?= $subjId; ?>&a=new">?????? ??????????????<img src="../../resources/new.png"/></a></div>
                    <?php 

                    $res = mysqli_query($conn, "SELECT * FROM assignments WHERE assignment_subject=$subjId ORDER BY assignment_expires DESC");
                    if($res->num_rows < 1) echo '<p style="width:100%;font-family:\'Noto Sans\';text-align:center">?????? ???????????????? ????????????????</p>';
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
                                <p class="assignment-end">??????????????????: ' . $date . '</p>
                            </a>';
                        }
                        else {
                            echo '
                            <a class="assignment" href="./assignments.php?s=' . $subjId . '&a=' . $aid . '">
                                <p class="assignment-name">' . $name . '</p>
                                <p class="assignment-end">??????????????????: ' . $date . '</p>
                            </a>';
                        }
                    }
                    ?>
                </div>
                <div class="right-side">
                    <?php if($act === ''): ?>
                        <div class="non-selected-text">???????????????? ?????? ??????????????!</div>
                    <?php elseif($act === 'new'): ?>
                    <p class="new-assignment-title">?????? ??????????????</p>
                    <div class="new-assignment-form">
                        <form id="new-form-desktop" action="../../includes/admin/subjects/newassignment.inc.php" method="POST" onsubmit="return verifyForm(0);">
                            <p class="field-name-text">?????????? ????????????????</p>
                            <input type="text" name="name" placeholder="??????????"/><br><br>
                            <p class="field-name-text">???????? ???????????????????? ????????????????</p>
                            <div class="time">
                                <div class="time-cont">
                                    <p class="time-title">????????????</p>
                                    <input type="number" name="year" placeholder="<?= (int)date("Y", time()); ?>"/>
                                </div>

                                <div class="time-cont">
                                    <p class="time-title">??????????</p>
                                    <input type="number" name="month" placeholder="<?= (int)date("m", time()); ?>" min="1" max="12"/>
                                </div>

                                <div class="time-cont">
                                    <p class="time-title">??????????</p>
                                    <input type="number" name="day" placeholder="<?= (int)date("d", time()); ?>" min="1" max="31"/>
                                </div>

                                <div class="time-cont">
                                    <p class="time-title">??????</p>
                                    <input type="number" name="hour" placeholder="<?= (int)date("H", time()); ?>" min="0" max="23"/>
                                </div>

                                <div class="time-cont">
                                    <p class="time-title">??????????</p>
                                    <input type="number" name="minute" placeholder="<?= (int)date("i", time()); ?>" min="0" max="59"/>
                                </div>
                            </div>
                            <input type="hidden" name="s" value="<?= $subjId; ?>"/>
                            <button class="submit-button" type="submit" name="submit" value="submit">??????????????</button>
                        </form>
                    </div>
                    <?php else: ?>
                        <label class="assignment-edit-area">
                            <p class="edit-text-title">?????????????????????? ????????????????</p>
                            <input type="checkbox" class="toggle-switch"/>
                            <div class="edit-area-cont">
                                <form id="edit-form-desktop" action="../../includes/admin/subjects/editassignment.inc.php" method="POST" onsubmit="return verifyForm(2);">
                                    <p class="field-name-text">?????????? ????????????????</p>
                                    <input type="text" name="name" placeholder="??????????" value="<?= $contTitle; ?>"/><br><br>
                                    <p class="field-name-text">???????? ???????????????????? ????????????????</p>
                                    <div class="time">
                                        <div class="time-cont">
                                            <p class="time-title">????????????</p>
                                            <input type="number" name="year" placeholder="<?= (int)date("Y", time()); ?>" value="<?= $dateArr[0] ?>"/>
                                        </div>

                                        <div class="time-cont">
                                            <p class="time-title">??????????</p>
                                            <input type="number" name="month" placeholder="<?= (int)date("m", time()); ?>" value="<?= $dateArr[1] ?>" min="1" max="12"/>
                                        </div>

                                        <div class="time-cont">
                                            <p class="time-title">??????????</p>
                                            <input type="number" name="day" placeholder="<?= (int)date("d", time()); ?>" value="<?= $dateArr[2] ?>" min="1" max="31"/>
                                        </div>

                                        <div class="time-cont">
                                            <p class="time-title">??????</p>
                                            <input type="number" name="hour" placeholder="<?= (int)date("H", time()); ?>" value="<?= $dateArr[3] ?>" min="0" max="23"/>
                                        </div>

                                        <div class="time-cont">
                                            <p class="time-title">??????????</p>
                                            <input type="number" name="minute" placeholder="<?= (int)date("i", time()); ?>" value="<?= $dateArr[4] ?>" min="0" max="59"/>
                                        </div>
                                    </div>
                                    <input type="hidden" name="id" value="<?= $assignmentId; ?>"/>
                                    <button class="submit-button" type="submit" name="submit" value="submit">??????????????</button>
                                </form>
                                <form action="../../includes/admin/subjects/deleteassignment.inc.php" method="POST" onsubmit="if(!confirm('?????????? ???????????????? ?????? ???????????? ???? ???????????????????? ?????????? ?????? ??????????????; ???? ???????????????????? ?????? ???? ???????????????? ?????????????? ???? ??????????!'))return false;document.getElementById('action-hider').style.display = 'block';">
                                    <input type="hidden" name="id" value="<?= $assignmentId; ?>"/>
                                    <button class="submit-button" type="submit" name="submit" value="submit" style="color:red">????????????????</button>
                                </form>
                            </div>
                        </label>
                        
                        <div class="student-replies">
                            <?php
                                $users = [];
                                $files = [];
                                $res = mysqli_query($conn, "SELECT * FROM assignment_responses WHERE response_assignment=$assignmentId ORDER BY response_id ASC");
                                if($res->num_rows < 1) echo '<p style="font-family: \'Noto Sans\'">?????? ???????????????? ????????????????</p>';
                                else while($row = $res->fetch_assoc()){
                                    $user = $row['response_user'];
                                    $fileId = $row['response_file'];
                                    $fileName = $row['response_file_name'];
                                    $fileDate = $row['response_date'];

                                    if(!in_array($user, $users)) $users[] = $user;
                                    $files[$user][] = ["id" => $fileId, "name" => $fileName, "date" => $fileDate];
                                }

                                foreach($users as $user){
                                    $uName = mysqli_real_escape_string($conn, $user);
                                    $res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$uName' LIMIT 1");
                                    if($res->num_rows > 0) $uName = htmlentities(decrypt($res->fetch_assoc()['user_name']));
                                    else $uName = htmlentities($uName);

                                    echo '<label class="reply">
                                        <p class="reply-name">' . $uName . '</p>
                                        <input type="checkbox" class="reply-toggle"/>
                                        <div class="reply-files">';

                                    foreach($files[$user] as $file){
                                        $fileId = $file["id"];
                                        $fileName = htmlentities($file["name"]);
                                        $fileExt = iconFromExtension($fileName);
                                        $date = new DateTime($file["date"]);
                                        $fileDate = htmlentities($date->format("d/m/Y H:i:s"));

                                        echo '<a class="file' . (($date > $expireDate) ? ' exp' : '') . '"  href="./assignmentfile.php?id=' . $fileId . '" target="_blank" title="???????? ??????????????"><img src="../../resources/icons/' . $fileExt . '.png"/><p class="file-name" title="' . $fileName . '">' . $fileName . '</p><p class="file-date">' . $fileDate . '</p></a>';
                                    }

                                    echo '<p style="font-size:0px">&nbsp;</p></div></label>';
                                }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mobile">
            <p class="title-mb">?????????????? ???????????????? - <?= $subjName; ?></p>
            <div class="assignments-cont-mb">
            <?php if($act == ''): ?>
                <div id="assignments-list">
                    <div class="new-assignment"><a href="./assignments.php?s=<?= $subjId; ?>&a=new">?????? ??????????????<img src="../../resources/new.png"/></a></div>
                    <?php
                    $res = mysqli_query($conn, "SELECT * FROM assignments WHERE assignment_subject=$subjId ORDER BY assignment_expires DESC");
                    if($res->num_rows < 1) echo '<p style="width:100%;font-family:\'Noto Sans\';text-align:center">?????? ???????????????? ????????????????</p>';
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
                                <p class="assignment-end">??????????????????: ' . $date . '</p>
                            </a>';
                        }
                        else {
                            echo '
                            <a class="assignment" href="./assignments.php?s=' . $subjId . '&a=' . $aid . '">
                                <p class="assignment-name">' . $name . '</p>
                                <p class="assignment-end">??????????????????: ' . $date . '</p>
                            </a>';
                        }
                    }
                    ?>
                </div>
            <?php else: ?>
                <div class="sub-navigation">
                    <div class="clicked-cont" onclick="openList();" id="to-list">?????????? ????????????????</div>
                    <div onclick="openAssignment();" id="to-assignment"><?= ($act == 'new') ? '?????? ??????????????' : '??????????????' ?></div>
                </div>

                <div id="assignments-list">
                    <div class="new-assignment"><a href="./assignments.php?s=<?= $subjId; ?>&a=new">?????? ??????????????<img src="../../resources/new.png"/></a></div>
                    <?php 

                    $res = mysqli_query($conn, "SELECT * FROM assignments WHERE assignment_subject=$subjId ORDER BY assignment_expires DESC");
                    if($res->num_rows < 1) echo '<p style="width:100%;font-family:\'Noto Sans\';text-align:center">?????? ???????????????? ????????????????</p>';
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
                                <p class="assignment-end">??????????????????: ' . $date . '</p>
                            </a>';
                        }
                        else {
                            echo '
                            <a class="assignment" href="./assignments.php?s=' . $subjId . '&a=' . $aid . '">
                                <p class="assignment-name">' . $name . '</p>
                                <p class="assignment-end">??????????????????: ' . $date . '</p>
                            </a>';
                        }
                    }
                    ?>
                </div>

                <div id="assignment-cont">
                    <?php if($act == 'new'): ?>
                        <p class="new-assignment-title">?????? ??????????????</p>
                        <div class="new-assignment-form">
                            <form id="new-form-mobile" action="../../includes/admin/subjects/newassignment.inc.php" method="POST" onsubmit="return verifyForm(1);">
                                <p class="field-name-text">?????????? ????????????????</p>
                                <input type="text" name="name" placeholder="??????????"/><br><br>
                                <p class="field-name-text">???????? ???????????????????? ????????????????</p>
                                <div class="time">
                                    <div class="time-cont">
                                        <p class="time-title">????????????</p>
                                        <input type="number" name="year" placeholder="<?= (int)date("Y", time()); ?>"/>
                                    </div>

                                    <div class="time-cont">
                                        <p class="time-title">??????????</p>
                                        <input type="number" name="month" placeholder="<?= (int)date("m", time()); ?>" min="1" max="12"/>
                                    </div>

                                    <div class="time-cont">
                                        <p class="time-title">??????????</p>
                                        <input type="number" name="day" placeholder="<?= (int)date("d", time()); ?>" min="1" max="31"/>
                                    </div>

                                    <div class="time-cont">
                                        <p class="time-title">??????</p>
                                        <input type="number" name="hour" placeholder="<?= (int)date("H", time()); ?>" min="0" max="23"/>
                                    </div>

                                    <div class="time-cont">
                                        <p class="time-title">??????????</p>
                                        <input type="number" name="minute" placeholder="<?= (int)date("i", time()); ?>" min="0" max="59"/>
                                    </div>
                                </div>
                                <input type="hidden" name="s" value="<?= $subjId; ?>"/>
                                <button class="submit-button" type="submit" name="submit" value="submit">??????????????</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <label class="assignment-edit-area">
                            <p class="edit-text-title">?????????????????????? ????????????????</p>
                            <input type="checkbox" class="toggle-switch"/>
                            <div class="edit-area-cont-mb">
                                <form id="edit-form-mobile" action="../../includes/admin/subjects/editassignment.inc.php" method="POST" onsubmit="return verifyForm(3);">
                                    <p class="field-name-text">?????????? ????????????????</p>
                                    <input type="text" name="name" placeholder="??????????" value="<?= $contTitle; ?>"/><br><br>
                                    <p class="field-name-text">???????? ???????????????????? ????????????????</p>
                                    <div class="time">
                                        <div class="time-cont">
                                            <p class="time-title">????????????</p>
                                            <input type="number" name="year" placeholder="<?= (int)date("Y", time()); ?>" value="<?= $dateArr[0] ?>"/>
                                        </div>

                                        <div class="time-cont">
                                            <p class="time-title">??????????</p>
                                            <input type="number" name="month" placeholder="<?= (int)date("m", time()); ?>" value="<?= $dateArr[1] ?>" min="1" max="12"/>
                                        </div>

                                        <div class="time-cont">
                                            <p class="time-title">??????????</p>
                                            <input type="number" name="day" placeholder="<?= (int)date("d", time()); ?>" value="<?= $dateArr[2] ?>" min="1" max="31"/>
                                        </div>

                                        <div class="time-cont">
                                            <p class="time-title">??????</p>
                                            <input type="number" name="hour" placeholder="<?= (int)date("H", time()); ?>" value="<?= $dateArr[3] ?>" min="0" max="23"/>
                                        </div>

                                        <div class="time-cont">
                                            <p class="time-title">??????????</p>
                                            <input type="number" name="minute" placeholder="<?= (int)date("i", time()); ?>" value="<?= $dateArr[4] ?>" min="0" max="59"/>
                                        </div>
                                    </div>
                                    <input type="hidden" name="id" value="<?= $assignmentId; ?>"/>
                                    <button class="submit-button" type="submit" name="submit" value="submit">??????????????</button>
                                </form>
                                <form action="../../includes/admin/subjects/deleteassignment.inc.php" method="POST" onsubmit="if(!confirm('?????????? ???????????????? ?????? ???????????? ???? ???????????????????? ?????????? ?????? ??????????????; ???? ???????????????????? ?????? ???? ???????????????? ?????????????? ???? ??????????!'))return false;document.getElementById('action-hider').style.display = 'block';">
                                    <input type="hidden" name="id" value="<?= $assignmentId; ?>"/>
                                    <button class="submit-button" type="submit" name="submit" value="submit" style="color:red">????????????????</button>
                                </form>
                            </div>
                        </label>                  
                        <div class="student-replies">
                            <?php
                                $users = [];
                                $files = [];
                                $res = mysqli_query($conn, "SELECT * FROM assignment_responses WHERE response_assignment=$assignmentId ORDER BY response_id ASC");
                                if($res->num_rows < 1) echo '<p style="font-family: \'Noto Sans\'">?????? ???????????????? ????????????????</p>';
                                else while($row = $res->fetch_assoc()){
                                    $user = $row['response_user'];
                                    $fileId = $row['response_file'];
                                    $fileName = $row['response_file_name'];
                                    $fileDate = $row['response_date'];

                                    if(!in_array($user, $users)) $users[] = $user;
                                    $files[$user][] = ["id" => $fileId, "name" => $fileName, "date" => $fileDate];
                                }

                                foreach($users as $user){
                                    $uName = mysqli_real_escape_string($conn, $user);
                                    $res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$uName' LIMIT 1");
                                    if($res->num_rows > 0) $uName = htmlentities(decrypt($res->fetch_assoc()['user_name']));
                                    else $uName = htmlentities($uName);

                                    echo '<label class="reply">
                                        <p class="reply-name">' . $uName . '</p>
                                        <input type="checkbox" class="reply-toggle"/>
                                        <div class="reply-files">';

                                    foreach($files[$user] as $file){
                                        $fileId = $file["id"];
                                        $fileName = htmlentities($file["name"]);
                                        $fileExt = iconFromExtension($fileName);
                                        $date = new DateTime($file["date"]);
                                        $fileDate = htmlentities($date->format("d/m/Y H:i:s"));

                                        echo '<a class="file' . (($date > $expireDate) ? ' exp' : '') . '"  href="./assignmentfile.php?id=' . $fileId . '" target="_blank" title="???????? ??????????????"><img src="../../resources/icons/' . $fileExt . '.png"/><p class="file-name" title="' . $fileName . '">' . $fileName . '</p><p class="file-date">' . $fileDate . '</p></a>';
                                    }

                                    echo '<p style="font-size:0px">&nbsp;</p></div></label>';
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
                    setTimeout(function() { alert("???? ?????????? ?????? ???????????? ???? ?????????? ????????!"); }, 5);
                    return false;
                }

                name = name.value.trim();

                if(isNaN(year.value) || year.value == ''){
                    redElem(year);
                    year.value = '';
                    setTimeout(function() { alert("?? ???????????? ?????? ???????????? ???? ?????????? ????????!"); }, 5);
                    return false;
                }

                year = parseInt(year.value);

                if(isNaN(month.value) || month.value == ''){
                    redElem(month);
                    month.value = '';
                    setTimeout(function() { alert("?? ?????????? ?????? ???????????? ???? ?????????? ??????????!"); }, 5);
                    return false;
                }
                if(parseInt(month.value) < 1 || parseInt(month.value) > 12){
                    redElem(month);
                    setTimeout(function() { alert("?? ?????????? ???????????? ???? ?????????? ???????????? 1 (????????????????????) ?????? 12 (????????????????????)!"); }, 5);
                    return false;
                }

                month = parseInt(month.value);

                if(isNaN(day.value) || day.value == ''){
                    redElem(day);
                    day.value = '';
                    setTimeout(function() { alert("?? ?????????? ?????? ???????????? ???? ?????????? ????????!"); }, 5);
                    return false;
                }
                let dim = parseInt(new Date(year, month, 0).getDate());
                if(parseInt(day.value) < 1 || parseInt(day.value) > dim){
                    redElem(day);
                    setTimeout(function() { alert("?? ?????????? ???????????? ???? ?????????? ???????????? 1 ?????? " + dim + "!"); }, 5);
                    return false;
                }

                if(isNaN(hour.value) || hour.value == ''){
                    redElem(hour);
                    hour.value = '';
                    setTimeout(function() { alert("?? ?????? ?????? ???????????? ???? ?????????? ????????!"); }, 5);
                    return false;
                }
                if(parseInt(hour.value) < 0 || parseInt(hour.value) > 23){
                    redElem(hour);
                    setTimeout(function() { alert("?? ?????? ???????????? ???? ?????????? ???????????? 0 ?????? 23!"); }, 5);
                    return false;
                }

                if(isNaN(minute.value) || minute.value == ''){
                    redElem(minute);
                    minute.value = '';
                    setTimeout(function() { alert("???? ?????????? ?????? ???????????? ???? ?????????? ????????!"); }, 5);
                    return false;
                }
                if(parseInt(minute.value) < 0 || parseInt(minute.value) > 59){
                    redElem(minute);
                    setTimeout(function() { alert("???? ?????????? ???????????? ???? ?????????? ???????????? 0 ?????? 59!"); }, 5);
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
    <img src="../../resources/loading.gif"><br>
    <p>???????????????? ????????????????????..</p>
</div>


    </div>
    <div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>
