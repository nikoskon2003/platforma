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
    <title><?= $siteName; ?> | Νέα Τάξη</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/classes/newclass.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">
    <div class="desktop">
        <div class="title">Νέα Τάξη</div>
        <div class="action-holder">
            <form action="../../includes/admin/classes/newclass.inc.php" method="POST" onsubmit="document.getElementById('action-hider').style.display = 'block';">
                <p class="field-label">Όνομα Τάξης</p>
                <input type="text" name="name" placeholder="πχ: Α1"><br>
                <button type="submit" name="submit" value="submit" class="button">Υποβολή</button>
            </form>
            <a href="./" class="back-button">Πίσω</a>
            <?php
                if(isset($_GET['e'])){
                    if($_GET['e'] == 'exists')
                        echo '<p class="error">Η τάξη υπάρχει ήδη</p>';
                    elseif($_GET['e'] == 'empty')
                        echo '<p class="error">Το όνομα της τάξης δεν μπορέι να είναι κενό</p>';
                }
            ?>
        </div>
    </div>
    
    <div class="mobile">
        <br><div class="title">Νέα Τάξη</div>
        <div class="action-holder-mb">
            <form action="../../includes/admin/classes/newclass.inc.php" method="POST" onsubmit="document.getElementById('action-hider').style.display = 'block';">
                <p class="field-label">Όνομα Τάξης</p>
                <input type="text" name="name" placeholder="πχ: Α1"><br>
                <button type="submit" name="submit" value="submit" class="button">Υποβολή</button>
            </form>
            <a href="./" class="back-button">Πίσω</a>
            <?php
                if(isset($_GET['e'])){
                    if($_GET['e'] == 'exists')
                        echo '<p class="error">Η τάξη υπάρχει ήδη</p>';
                    elseif($_GET['e'] == 'empty')
                        echo '<p class="error">Το όνομα της τάξης δεν μπορέι να είναι κενό</p>';
                }
            ?>
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