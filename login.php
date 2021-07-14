<?php
session_start();
include_once './includes/config.php';

if(isset($_SESSION['type']))
{
    header("Location: .");
    exit();
}

include './includes/extrasLoader.inc.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="favicon.ico" />
    <title><?= $siteName; ?> | Σύνδεση</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="styles/login.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
</head>

<body>
<div id="container">
	<div id="header"><?= LoadTopNav(__FILE__); ?></div>
	<div id="body">
        <div class="home">
            <br>
            <div class="box center">
                <center>
                <p>Σύνδεση</p>
                <br>
                <div class="login-container">
                    <form action="includes/login.inc.php" method="post">
                        <input type="text" placeholder="Ψευδώνυμο" name="username">
                        <br><br>
                        <input type="password" placeholder="Κωδικός" name="password">
                        <br><br>
                        <input type="checkbox" placeholder="autologin" name="autologin">
                        <p style="display:inline;font-size:16px">Να με θυμάσαι</p>
                        <br><br>
                        <button type="submit" name="submit" id="submit">Σύνδεση</button>
                        <?php
                            if(isset($_GET['r'])) echo '<input type="hidden" name="r" value="' . $_GET['r'] .'">';
                        ?>
                    </form>
                    <?php
                    if(isset($_GET['login']))
                    {
                        if($_GET['login'] == 'empty')
                        {
                            echo '<br><p class="error" style="color: rgb(195, 40, 40); font-size: 15px;">Λάθος ψευδώνυμο ή κωδικός</p>';
                        }
                        elseif($_GET['login'] == 'error')
                        {
                            echo '<br><p class="error" style="color: rgb(195, 40, 40); font-size: 15px;">Υπήρξε κάποιο πρόβλημα<br>Παρακαλώ προσπαθήστε αργότερα</p>';
                        }
                    }
                ?>
                </div>
                </center>
            </div>
        </div>
	</div>
	<div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>
