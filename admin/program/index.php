<?php session_start();

if(!isset($_SESSION['type'])){
    include '../../error.php';
    exit();
}

include_once '../../includes/config.php';
include '../../includes/dbh.inc.php';
$username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
if($_SESSION['type'] !== 'ADMIN'){
    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='program-editor' AND link_user='$username' LIMIT 1");
    if($res->num_rows < 1){
        include '../../error.php';
    exit();
    }
}

include '../../includes/enc.inc.php';

$studentsUrl = '';
$teachersUrl = '';
$programText = '';

$res = mysqli_query($conn, "SELECT * FROM options WHERE option_name='program-students'");
if($res->num_rows > 0) $studentsUrl = htmlentities($res->fetch_assoc()['option_value']);

$res = mysqli_query($conn, "SELECT * FROM options WHERE option_name='program-teachers'");
if($res->num_rows > 0) $teachersUrl = htmlentities($res->fetch_assoc()['option_value']);

$res = mysqli_query($conn, "SELECT * FROM options WHERE option_name='program-text'");
if($res->num_rows > 0) $programText = decrypt($res->fetch_assoc()['option_value']);

$programText = str_replace('<br>', "\n", $programText);
$programText = htmlentities($programText);

$isFile = ($studentsUrl == 'file.php?id=program-students') && ($teachersUrl == 'file.php?id=program-teachers');
include_once '../../includes/extrasLoader.inc.php';
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../favicon.ico" />
    <title><?= $siteName; ?> | Πρόγραμμα</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/program/index.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>

    <script src="../../scripts/getmessages.js?v=<?= $pubFileVer; ?>"></script>
</head>

<body>

<div id="container">
    <div id="header"> <?= LoadTopNav(__FILE__); ?></div>
    
    <div id="body">
        <div class="desktop">
            <p class="title">Πρόγραμμα</p>

            <?php if($_SESSION['type'] === 'ADMIN'): ?>
            <div class="editors">
                <a href="./editors.php">Επεξεργασία Συντακτών<img src="../../resources/edit-icon.png"/></a>
            </div>
            <?php endif; ?>

            <div class="program-container">
                <form id="form-desktop" action="../../includes/admin/program/updateprogram.inc.php" method="POST" enctype="multipart/form-data" onsubmit="document.getElementById('action-hider').style.display = 'block';">
                    <p class="category-name">Τύπος Προγράμματος</p>
                    <div class="program-type">
                        <label class="radio-cont">
                            <input type="radio" name="program-type" value="url" checked="checked"onclick="toUrl();">
                            <div class="radio-child">Υπερσύνδεσμος</div>
                        </label>
                        <label class="radio-cont">
                            <input type="radio"name="program-type" value="file" onclick="toFile();">
                            <div class="radio-child">Αρχείο</div>
                        </label>
                    </div>

                    <div class="url-input-cont">
                        <p class="category-name">Μαθητές</p>
                        <input type="text" name="student-url" placeholder="Υπερσύνδεσμος" value="<?= ($studentsUrl == 'file.php?id=program-students') ? 'prev-file' : $studentsUrl ?>" />
                        <p class="category-name">Καθηγητές</p>
                        <input type="text" name="teacher-url" placeholder="Υπερσύνδεσμος" value="<?= ($teachersUrl == 'file.php?id=program-teachers') ? 'prev-file' : $teachersUrl ?>" />
                    </div>
                    <div class="file-input-cont" style="display: none;">
                        <p class="category-name">Μαθητές</p>
                        <input type="file" name="student-file"/>
                        <p class="category-name">Καθηγητές</p>
                        <input type="file" name="teacher-file"/>
                    </div>

                    <p class="category-name">Κείμενο</p>
                    <textarea form="form-desktop" name="program-text" placeholder="Κείμενο Προγράμματος"><?= $programText ?></textarea>

                    <p class="category-name">Αποστολή Ειδοποίησης</p>
                    <div class="program-type">
                        <label class="radio-cont">
                            <input type="radio" name="notif" value="yes" checked="checked">
                            <div class="radio-child">Ναι</div>
                        </label>
                        <label class="radio-cont">
                            <input type="radio"name="notif" value="no">
                            <div class="radio-child">Όχι</div>
                        </label>
                    </div>

                    <button class="submit-button" name="submit" value="submit">Υποβολή</button>
                    <a class="cancel-button" href="../../">Πίσω</a>
                </form>
            </div>
        </div>

        <div class="mobile">
            <br><p class="title">Πρόγραμμα</p>

            <?php if($_SESSION['type'] === 'ADMIN'): ?>
            <div class="editors">
                <a href="./editors.php">Επεξεργασία Συντακτών<img src="../../resources/edit-icon.png"/></a>
            </div>
            <?php endif; ?>

            <div class="program-container-mb">
            <form id="form-mobile" action="../../includes/admin/program/updateprogram.inc.php" method="POST" enctype="multipart/form-data" onsubmit="document.getElementById('action-hider').style.display = 'block';">
                    <p class="category-name">Τύπος Προγράμματος</p>
                    <div class="program-type">
                        <label class="radio-cont">
                            <input type="radio" name="program-type" value="url" checked="checked"onclick="toUrl();">
                            <div class="radio-child">Υπερσύνδεσμος</div>
                        </label>
                        <label class="radio-cont">
                            <input type="radio"name="program-type" value="file" onclick="toFile();">
                            <div class="radio-child">Αρχείο</div>
                        </label>
                    </div>

                    <div class="url-input-cont">
                        <p class="category-name">Μαθητές</p>
                        <input type="text" name="student-url" placeholder="Υπερσύνδεσμος" value="<?= ($studentsUrl == 'file.php?id=program-students') ? 'prev-file' : $studentsUrl ?>" />
                        <p class="category-name">Καθηγητές</p>
                        <input type="text" name="teacher-url" placeholder="Υπερσύνδεσμος" value="<?= ($teachersUrl == 'file.php?id=program-teachers') ? 'prev-file' : $teachersUrl ?>" />
                    </div>
                    <div class="file-input-cont" style="display: none;">
                        <p class="category-name">Μαθητές</p>
                        <input type="file" name="student-file"/>
                        <p class="category-name">Καθηγητές</p>
                        <input type="file" name="teacher-file"/>
                    </div>

                    <p class="category-name">Κείμενο</p>
                    <textarea form="form-mobile" name="program-text" placeholder="Κείμενο Προγράμματος"><?= $programText ?></textarea>

                    <p class="category-name">Αποστολή Ειδοποίησης</p>
                    <div class="program-type">
                        <label class="radio-cont">
                            <input type="radio" name="notif" value="yes" checked="checked">
                            <div class="radio-child">Ναι</div>
                        </label>
                        <label class="radio-cont">
                            <input type="radio"name="notif" value="no">
                            <div class="radio-child">Όχι</div>
                        </label>
                    </div>

                    <button class="submit-button" name="submit" value="submit">Υποβολή</button>
                    <a class="cancel-button" href="../../">Πίσω</a>
                </form>                
            </div>
        </div>

        <script>
            function toUrl(){
                document.querySelectorAll(".url-input-cont").forEach(e => e.style.display = 'block');
                document.querySelectorAll(".file-input-cont").forEach(e => e.style.display = 'none');
            }
            function toFile(){
                document.querySelectorAll(".url-input-cont").forEach(e => e.style.display = 'none');
                document.querySelectorAll(".file-input-cont").forEach(e => e.style.display = 'block');
            }
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