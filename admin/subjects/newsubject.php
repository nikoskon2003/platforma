<?php  session_start();

if(!isset($_SESSION['type'])){
    include '../../error.php';
    exit();
}elseif($_SESSION['type'] !== 'ADMIN'){
    include '../../error.php';
    exit();
}

include_once '../../includes/config.php';
include_once '../../includes/extrasLoader.inc.php';
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../favicon.ico" />
    <title><?= $siteName ?> | Νέο Μάθημα</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/subjects/newsubject.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">
    <div class="desktop">
        <div class="title">Νέο Μάθημα</div>
        <div class="action-holder">
            <form action="../../includes/admin/subjects/newsubject.inc.php" method="POST" onsubmit="document.getElementById('action-hider').style.display = 'block';">
                <p class="field-label">Όνομα Μαθήματος</p>
                <input type="text" name="name" placeholder="πχ: Άλγεβρα"><br>
                <p class="field-label">Τάξη</p>
                <select class="class-select" name="class">
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
                </select><br>
                <button type="submit" name="submit" value="submit" class="submit-button">Υποβολή</button>
            </form>
            <a href="./" class="back-button">Πίσω</a>            
        </div>
    </div>
    <div class="mobile">
        <br><div class="title">Νέο Μάθημα</div>
        <div class="action-holder-mb">
            <form action="../../includes/admin/subjects/newsubject.inc.php" method="POST" onsubmit="document.getElementById('action-hider').style.display = 'block';">
                <p class="field-label">Όνομα Μαθήματος</p>
                <input type="text" name="name" placeholder="πχ: Άλγεβρα"><br>
                <p class="field-label">Τάξη</p>
                <select class="class-select" name="class">
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
                </select><br>
                <button type="submit" name="submit" value="submit" class="submit-button">Υποβολή</button>
            </form>
            <a href="./" class="back-button">Πίσω</a>            
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