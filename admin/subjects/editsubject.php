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
$subjClassName = "&lt;Καμία τάξη&gt;";
if($subjClass != null){
    $res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$subjClass");
    if($res->num_rows > 0) $subjClassName = $res->fetch_assoc()['class_name'];
}

include_once '../../includes/extrasLoader.inc.php';
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../favicon.ico" />
    <title><?= $siteName; ?> | Επεξεργασία <?= $subjName; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/subjects/editsubject.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">
    <div class="desktop">
        <div class="title">Επεξεργασία <?= $subjName; ?></div>
        <div class="action-holder">
            <a href="./subject.php?s=<?= $subjId; ?>" class="back-button">Πίσω</a>
            <form action="../../includes/admin/subjects/editsubject.inc.php" method="POST" onsubmit="document.getElementById('action-hider').style.display = 'block';">
                <p class="field-label">Όνομα Μαθήματος</p>
                <input type="text" name="name" placeholder="πχ: Άλγεβρα" value="<?= $subjName; ?>"><br>
                <p class="field-label">Τάξη</p>
                    <select class="class-select" name="class">
                        <option value="<?= $subjClass; ?>"><?= $subjClassName; ?></option>
                        <option value="no">&lt;Καμία&gt;</option>
                        <?php
                            include '../../includes/dbh.inc.php';
                            $res = mysqli_query($conn, "SELECT * FROM classes");
                            if($res->num_rows > 0){
                                while($row = $res->fetch_assoc()){
                                    $cid = $row['class_id'];
                                    $cname = htmlspecialchars($row['class_name']);
                                    echo "<option value='$cid'>$cname</option>";
                                }
                            }
                        ?>
                    </select>
                    <br><br>
                    <div class="teacher-holder">
                        <?php 
                            $res = mysqli_query($conn, "SELECT * FROM users WHERE user_type=1");
                            if($res->num_rows > 0){
                                echo '<p class="field-label">Καθηγητές</p>';
                                while($row = $res->fetch_assoc()){
                                    $username = $row['user_username'];
                                    $username = htmlentities($username);
                                    $ch = '';

                                    $result = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-teacher' AND link_used_id=$subjId AND link_user='$username'");
                                    if($result->num_rows > 0) $ch = 'checked="checked"';

                                    $name = htmlentities(decrypt($row['user_name']));

                                    echo '<label class="teacher" title="' . $name . ' - ' . $username . '">
                                        <input type="checkbox" name="teachers[]" value="' . $username . '" '. $ch .'>    
                                        <p>' . $name . '</p>
                                    </label>';
                                }
                            }
                        ?>
                    </div>
                <input type="hidden" name="id" value="<?= $subjId; ?>">
                <button type="submit" name="submit" value="submit" class="button">Υποβολή</button>
            </form>
            <form action="../../includes/admin/subjects/deletesubject.inc.php" method="POST" onsubmit="if(!confirm('!!!Με τη διαγραφή του μαθήματος θα ΔΙΑΓΡΑΦΟΥΝ ΟΛΕΣ οι ΑΝΑΚΟΙΝΩΣΕΙΣ!!!'))return false;document.getElementById('action-hider').style.display = 'block';">
                <input type="hidden" name="id" value="<?= $subjId; ?>"/>
                <button type="submit" name="delete" value="delete" class="back-button">Διαγραφή</button>
            </form>
        </div>
    </div>

    <div class="mobile">
        <br><div class="title">Επεξεργασία <?= $subjName; ?></div>
        <div class="action-holder-mb">
            <a href="./subject.php?s=<?= $subjId; ?>" class="back-button">Πίσω</a>
            <form action="../../includes/admin/subjects/editsubject.inc.php" method="POST" onsubmit="document.getElementById('action-hider').style.display = 'block';">
                <p class="field-label">Όνομα Μαθήματος</p>
                <input type="text" name="name" placeholder="πχ: Άλγεβρα" value="<?= $subjName; ?>"><br>
                <p class="field-label">Τάξη</p>
                    <select class="class-select" name="class">
                        <option value="<?= $subjClass; ?>"><?= $subjClassName; ?></option>
                        <option value="no">&lt;Καμία&gt;</option>
                        <?php
                            include '../../includes/dbh.inc.php';
                            $res = mysqli_query($conn, "SELECT * FROM classes");
                            if($res->num_rows > 0){
                                while($row = $res->fetch_assoc()){
                                    $cid = $row['class_id'];
                                    $cname = htmlspecialchars($row['class_name']);
                                    echo "<option value='$cid'>$cname</option>";
                                }
                            }
                        ?>
                    </select>
                    <br><br>
                    <div class="teacher-holder">
                        <?php 
                            $res = mysqli_query($conn, "SELECT * FROM users WHERE user_type=1");
                            if($res->num_rows > 0){
                                echo '<p class="field-label">Καθηγητές</p>';
                                while($row = $res->fetch_assoc()){
                                    $username = $row['user_username'];
                                    $username = htmlentities($username);
                                    $ch = '';

                                    $result = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-teacher' AND link_used_id=$subjId AND link_user='$username'");
                                    if($result->num_rows > 0) $ch = 'checked="checked"';

                                    $name = htmlentities(decrypt($row['user_name']));

                                    echo '<label class="teacher" title="' . $name . ' - ' . $username . '">
                                        <input type="checkbox" name="teachers[]" value="' . $username . '" '. $ch .'>    
                                        <p>' . $name . '</p>
                                    </label>';
                                }
                            }
                        ?>
                    </div>
                <input type="hidden" name="id" value="<?= $subjId; ?>">
                <button type="submit" name="submit" value="submit" class="button">Υποβολή</button>
            </form>
            <form action="../../includes/admin/subjects/deletesubject.inc.php" method="POST" onsubmit="if(!confirm('!!!Με τη διαγραφή του μαθήματος θα ΔΙΑΓΡΑΦΟΥΝ ΟΛΕΣ οι ΑΝΑΚΟΙΝΩΣΕΙΣ!!!'))return false;document.getElementById('action-hider').style.display = 'block';">
                <input type="hidden" name="id" value="<?= $subjId; ?>"/>
                <button type="submit" name="delete" value="delete" class="back-button">Διαγραφή</button>
            </form>
        </div>
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