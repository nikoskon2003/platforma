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
    <title><?= $siteName; ?> | Μαθητές <?= $subjName; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/subjects/students.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">
    <div class="desktop">
        <div class="title">Μαθητές <?= $subjName; ?></div>
        <div class="action-holder">
            <a href="./subject.php?s=<?= $subjId; ?>" class="back-button">Πίσω</a><br>
            <form action="../../includes/admin/subjects/students.inc.php" method="POST" onsubmit="document.getElementById('action-hider').style.display = 'block';">
                <div class="students-holder">
                <?php 
                    if($subjClass != null)
                        $res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id<>$subjClass");
                    else 
                        $res = mysqli_query($conn, "SELECT * FROM classes");
                    if($res->num_rows > 0){
                        while($row = $res->fetch_assoc()){
                            $clId = (int)mysqli_real_escape_string($conn, $row['class_id']);
                            $clName = $row['class_name'];
                            $result = mysqli_query($conn, "SELECT * FROM users WHERE user_type=0 AND user_class=$clId");
                            if($result->num_rows > 0){
                                echo '<p class="field-label">' . $clName . '</p>';
                                while($rowb = $result->fetch_assoc()){
                                    $username = $rowb['user_username'];
                                    $username = htmlentities($username);
                                    $ch = '';

                                    $resu = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-student' AND link_used_id=$subjId AND link_user='$username'");
                                    if($resu->num_rows > 0) $ch = 'checked="checked"';

                                    $name = htmlentities(decrypt($rowb['user_name']));

                                    echo '<label class="student" title="' . $name . ' - ' . $username . '">
                                        <input type="checkbox" name="students[]" value="' . $username . '" '. $ch .'>    
                                        <p>' . $name . '</p>
                                    </label>';
                                }
                            }
                        }
                    }

                    $res = mysqli_query($conn, "SELECT * FROM users WHERE user_type=0 AND user_class IS NULL");
                    if($res->num_rows > 0){
                        echo '<p class="field-label">Άλλοι Μαθητές:</p>';
                        while($row = $res->fetch_assoc()){
                            $username = $row['user_username'];
                            $username = htmlentities($username);
                            $ch = '';

                            $result = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-student' AND link_used_id=$subjId AND link_user='$username'");
                            if($result->num_rows > 0) $ch = 'checked="checked"';

                            $name = htmlentities(decrypt($row['user_name']));

                            echo '<label class="student" title="' . $name . ' - ' . $username . '">
                                <input type="checkbox" name="students[]" value="' . $username . '" '. $ch .'>
                                <p>' . $name . '</p>
                            </label>';
                        }
                    }
                ?>
                </div>
                <input type="hidden" name="id" value="<?= $subjId; ?>">
                <button type="submit" name="submit" value="submit" class="button">Υποβολή</button>
            </form>
            <a href="./subject.php?s=<?= $subjId; ?>" class="back-button">Πίσω</a>            
        </div>
    </div>


    <div class="mobile">
        <br><div class="title">+Μαθητές<br><?= $subjName; ?></div>
        <div class="action-holder-mb">
        <a href="./subject.php?s=<?= $subjId; ?>" class="back-button">Πίσω</a> <br>
            <form action="../../includes/admin/subjects/students.inc.php" method="POST" onsubmit="document.getElementById('action-hider').style.display = 'block';">
                <div class="students-holder">
                <?php 
                    if($subjClass != null)
                        $res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id<>$subjClass");
                    else 
                        $res = mysqli_query($conn, "SELECT * FROM classes");
                    if($res->num_rows > 0){
                        while($row = $res->fetch_assoc()){
                            $clId = (int)mysqli_real_escape_string($conn, $row['class_id']);
                            $clName = $row['class_name'];
                            $result = mysqli_query($conn, "SELECT * FROM users WHERE user_type=0 AND user_class=$clId");
                            if($result->num_rows > 0){
                                echo '<p class="field-label">' . $clName . '</p>';
                                while($rowb = $result->fetch_assoc()){
                                    $username = $rowb['user_username'];
                                    $username = htmlentities($username);
                                    $ch = '';

                                    $resu = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-student' AND link_used_id=$subjId AND link_user='$username'");
                                    if($resu->num_rows > 0) $ch = 'checked="checked"';

                                    $name = htmlentities(decrypt($rowb['user_name']));

                                    echo '<label class="student" title="' . $name . ' - ' . $username . '">
                                        <input type="checkbox" name="students[]" value="' . $username . '" '. $ch .'>    
                                        <p>' . $name . '</p>
                                    </label>';
                                }
                            }
                        }
                    }

                    $res = mysqli_query($conn, "SELECT * FROM users WHERE user_type=0 AND user_class IS NULL");
                    if($res->num_rows > 0){
                        echo '<p class="field-label">Άλλοι Μαθητές:</p>';
                        while($row = $res->fetch_assoc()){
                            $username = $row['user_username'];
                            $username = htmlentities($username);
                            $ch = '';

                            $result = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-student' AND link_used_id=$subjId AND link_user='$username'");
                            if($result->num_rows > 0) $ch = 'checked="checked"';

                            $name = htmlentities(decrypt($row['user_name']));

                            echo '<label class="student" title="' . $name . ' - ' . $username . '">
                                <input type="checkbox" name="students[]" value="' . $username . '" '. $ch .'>    
                                <p>' . $name . '</p>
                            </label>';
                        }
                    }
                ?>
                </div>
                <input type="hidden" name="id" value="<?= $subjId; ?>">
                <button type="submit" name="submit" value="submit" class="button">Υποβολή</button>
            </form>
            <a href="./subject.php?s=<?= $subjId; ?>" class="back-button">Πίσω</a>            
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