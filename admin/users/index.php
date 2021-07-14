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
    <title><?= $siteName; ?> | Χρήστες</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/users/index.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">
        <div class="desktop">
            <p class="title">Χρήστες</p><br>
            <div class="user-type">
                <br>
                <div class="new-user">
                    <a href="./newuser.php">Νέος Χρήστης<img src="../../resources/new.png"/></a>
                </div>
                <a class="button" href="./students.php">Μαθητές</a>
                <a class="button" href="./teachers.php">Καθηγητές</a>
            </div>
        </div>



        <div class="mobile">
            <br><p class="title">Χρήστες</p><br>
            <div class="user-type-mb">
                <br>
                <div class="new-user">
                    <a href="./newuser.php">Νέος Χρήστης<img src="../../resources/new.png"/></a>
                </div>
                <a class="button" href="./students.php">Μαθητές</a>
                <a class="button" href="./teachers.php">Καθηγητές</a>
            </div>
        </div>

    </div>
    <div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>