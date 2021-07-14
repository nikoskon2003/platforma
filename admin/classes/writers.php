<?php  session_start();

if(!isset($_SESSION['type'])){
    include '../../error.php';
    exit();
}elseif($_SESSION['type'] !== 'ADMIN'){
    include '../../error.php';
    exit();
}

include_once '../../includes/config.php';

if(!isset($_GET['c'])){
    header("Location: .");
    exit();
}
if(!is_numeric($_GET['c'])){
    header("Location: .");
    exit();
}

$classId = (int)($_GET['c']);

include '../../includes/dbh.inc.php';
$res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$classId");
if($res->num_rows < 1){
    header("Location: .");
    exit();
}
$className = $res->fetch_assoc()['class_name'];

include '../../includes/enc.inc.php';
include_once '../../includes/extrasLoader.inc.php';
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../favicon.ico" />
    <title><?= $siteName; ?> | Συντάκτες - <?= $className; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/homepage/authors.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">
        <div class="desktop">
            <p class="title">Συντάκτες - <?= $className; ?></p>
            <div class="authors-cont">
                <a href="./class.php?c=<?= $classId; ?>" class="back-button">Πίσω</a>
                <p class="field-label">Νέος Συντάκτης</p>
                <form action="../../includes/admin/classes/writers.inc.php" method="POST" onsubmit="document.getElementById('action-hider').style.display = 'block';">
                    <input type="hidden" name="class-id" value="<?= $classId; ?>"/>
                    <input type="text" name="username" placeholder="Ψευδώνυμο"/>
                    <button class="button" name="submit" value="submit">Υποβολή</button>
                </form><?php 
                    if(isset($_GET['e'])) if($_GET['e'] == 'noclass'){
                        echo '<p class="field-label" style="color: red;font-size: 14px;">Ο χρήστης (μαθητής) δεν ανήκει στην τάξη</p>';
                    } 
                ?><br><br>

                <p class="field-label">Συντάκτες</p><br>
                <div class="user-holder">
                <?php
                    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='class-writer' AND link_used_id=$classId");
                    if($res->num_rows < 1) echo '<p class="field-label">Κανένας συντάκτης</p>';
                    else while($row = $res->fetch_assoc()){
                        $username = mysqli_real_escape_string($conn, $row['link_user']);
                        $result = mysqli_query($conn, "SELECT user_name FROM users WHERE user_username='$username'");
                        if($result->num_rows > 0)
                        {
                            $name = decrypt($result->fetch_assoc()['user_name']);
                            $name = htmlentities($name);
                            $username = htmlentities($username);
                            
                            echo "<form class='user' action='../../includes/admin/classes/writers.inc.php' method='POST' onsubmit=\"if(!confirm('Θέλετε σίγουρα να διαγράψετε αυτόν το συντάκτη;'))return false;document.getElementById('action-hider').style.display = 'block';\">
                                <button title='$name - $username' class='user-name' name='delete' value='delete'>$name</button>
                                <input type='hidden' name='username' value='$username'/>
                                <input type='hidden' name='class-id' value='$classId'/>
                            </form>";
                        }
                    }
                ?>
                </div>
            </div>
        </div>



        <div class="mobile">
        <br><p class="title">Συντάκτες - <?= $className; ?></p>
            <div class="authors-cont-mb">
                <a href="./class.php?c=<?= $classId; ?>" class="back-button">Πίσω</a>
                <p class="field-label">Νέος Συντάκτης</p>
                <form action="../../includes/admin/classes/writers.inc.php" method="POST" onsubmit="document.getElementById('action-hider').style.display = 'block';">
                    <input type="hidden" name="class-id" value="<?= $classId; ?>"/>
                    <input type="text" name="username" placeholder="Ψευδώνυμο"/>
                    <button class="button" name="submit" value="submit">Υποβολή</button>
                </form><?php 
                    if(isset($_GET['e'])) if($_GET['e'] == 'noclass'){
                        echo '<p class="field-label" style="color: red;font-size: 14px;">Ο χρήστης (μαθητής) δεν ανήκει στην τάξη</p>';
                    } 
                ?><br><br>

                <p class="field-label">Συντάκτες</p><br>
                <div class="user-holder">
                <?php
                    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='class-writer' AND link_used_id=$classId");
                    if($res->num_rows < 1) echo '<p class="field-label">Κανένας συντάκτης</p>';
                    else while($row = $res->fetch_assoc()){
                        $username = mysqli_real_escape_string($conn, $row['link_user']);
                        $result = mysqli_query($conn, "SELECT user_name FROM users WHERE user_username='$username'");
                        if($result->num_rows > 0)
                        {
                            $name = decrypt($result->fetch_assoc()['user_name']);
                            $name = htmlentities($name);
                            $username = htmlentities($username);
                            
                            echo "<form class='user' action='../../includes/admin/classes/writers.inc.php' method='POST' onsubmit=\"if(!confirm('Θέλετε σίγουρα να διαγράψετε αυτόν το συντάκτη;'))return false;document.getElementById('action-hider').style.display = 'block';\">
                                <button title='$name - $username' class='user-name' name='delete' value='delete'>$name</button>
                                <input type='hidden' name='username' value='$username'/>
                                <input type='hidden' name='class-id' value='$classId'/>
                            </form>";
                        }
                    }
                ?>
                </div>

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