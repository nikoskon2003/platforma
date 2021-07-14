<?php  session_start();

if(!isset($_SESSION['type'])){
    include '../../error.php';
    exit();
}
elseif($_SESSION['type'] !== 'ADMIN'){
    include '../../error.php';
    exit();
}


include '../../includes/config.php';
include '../../includes/extrasLoader.inc.php';

include '../../includes/dbh.inc.php';
include '../../includes/enc.inc.php';
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../favicon.ico" />
    <title><?= $siteName; ?> | Μαθητές</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/users/students.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">
        <div class="desktop">
            <p class="title">Μαθητές</p><br>
            <div class="content-holder">
                <a href="./" class="back-button">Πίσω</a>
                <br>
                <div class="students">
                <?php 
                $res = mysqli_query($conn, "SELECT * FROM classes");
                if($res->num_rows > 0)
                    while($row = $res->fetch_assoc()){
                        $class = $row['class_id'];
                        $className= $row['class_name'];
                        $result = mysqli_query($conn, "SELECT * FROM users WHERE user_class=$class AND user_type=0");
                        if($result->num_rows > 0){
                            echo "<p class='field-label'>$className</p>";
                            while($r = $result->fetch_assoc()){
                                $username = $r['user_username'];
                                $urlUsername = urlencode($username);
                                $disUsername = htmlentities($username);
                                $name = htmlentities(decrypt($r['user_name']));
                                $dis = $name;
                                echo "<a class='student' title='$name - $disUsername' href='./edituser.php?u=$urlUsername'>$name</a>";
                            }
                        } 
                    }

                $res = mysqli_query($conn, "SELECT * FROM users WHERE user_type=0 AND user_class IS NULL");
                if($res->num_rows > 0){
                    echo "<p class='field-label'>Άλλοι Μαθητές:</p>";
                    while($r = $res->fetch_assoc()){
                        $username = $r['user_username'];
                        $urlUsername = urlencode($username);
                        $disUsername = htmlentities($username);
                        $name = htmlentities(decrypt($r['user_name']));
                        $dis = $name;
                        echo "<a class='student' title='$name - $disUsername' href='./edituser.php?u=$urlUsername'>$name</a>";
                    }
                }
                ?>
                </div>
            </div>
        </div>



        <div class="mobile">
            <br><p class="title">Μαθητές</p><br>
            <div class="content-holder-mb">
                <a href="./" class="back-button">Πίσω</a>
                <br>
                <div class="students">
                <?php 
                $res = mysqli_query($conn, "SELECT * FROM classes");
                if($res->num_rows > 0)
                    while($row = $res->fetch_assoc()){
                        $class = $row['class_id'];
                        $className= $row['class_name'];
                        $result = mysqli_query($conn, "SELECT * FROM users WHERE user_class=$class AND user_type=0");
                        if($result->num_rows > 0){
                            echo "<p class='field-label'>$className</p>";
                            while($r = $result->fetch_assoc()){
                                $username = $r['user_username'];
                                $urlUsername = urlencode($username);
                                $disUsername = htmlentities($username);
                                $name = htmlentities(decrypt($r['user_name']));
                                $dis = $name;
                                echo "<a class='student' title='$name - $disUsername' href='./edituser.php?u=$urlUsername'>$name</a>";
                            }
                        } 
                    }

                $res = mysqli_query($conn, "SELECT * FROM users WHERE user_type=0 AND user_class IS NULL");
                if($res->num_rows > 0){
                    echo "<p class='field-label'>Άλλοι Μαθητές:</p>";
                    while($r = $res->fetch_assoc()){
                        $username = $r['user_username'];
                        $urlUsername = urlencode($username);
                        $disUsername = htmlentities($username);
                        $name = htmlentities(decrypt($r['user_name']));
                        $dis = $name;
                        echo "<a class='student' title='$name - $disUsername' href='./edituser.php?u=$urlUsername'>$name</a>";
                    }
                }
                ?>
                </div>
            </div>
        </div>

    </div>
    <div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>