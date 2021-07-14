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
    <title><?= $siteName; ?> | Τάξεις</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/classes/index.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">

    <div class="desktop">
        <div class="title">Τάξεις</div>
        <div class="class-holder">
            <div class="new-class">
                <a href="./newclass.php">Νέα Τάξη<img src="../../resources/new.png"/></a>
            </div>
            <div class="main-classes">
                <?php 
                include '../../includes/dbh.inc.php';
                $res = mysqli_query($conn, "SELECT * FROM classes");
                if($res->num_rows > 0)
                    while($row = $res->fetch_assoc()){
                        $id = $row['class_id'];
                        $name = htmlentities($row['class_name']);
                        echo "<a class='class' title='$name' href='./class.php?c=$id'>$name</a>";
                    }
                ?>

            </div>
        </div>
    </div>


    <div class="mobile">
        <br><div class="title">Τάξεις</div>
        <div class="action-holder-mb">
            <div class="new-class">
                <a href="./newclass.php">Νέα Τάξη<img src="../../resources/new.png"/></a>
            </div>
            <div class="main-classes">
                <?php 
                include '../../includes/dbh.inc.php';
                $res = mysqli_query($conn, "SELECT * FROM classes");
                if($res->num_rows > 0)
                    while($row = $res->fetch_assoc()){
                        $id = $row['class_id'];
                        $name = htmlentities($row['class_name']);
                        echo "<a class='class' title='$name' href='./class.php?c=$id'>$name</a>";
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