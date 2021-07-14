<?php  session_start();

if(!isset($_SESSION['type'])){
    include '../../error.php';
    exit();
}elseif($_SESSION['type'] !== 'ADMIN'){
    include '../../error.php';
    exit();
}
if(!isset($_GET['u'])){
    header("Location: ./");
    exit();
}
include '../../includes/dbh.inc.php';
$username = mysqli_real_escape_string($conn, $_GET['u']);

$res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username'");
if($res->num_rows < 1){
    header("Location: ./");
    exit();
}
$userData = $res->fetch_assoc();

include_once '../../includes/enc.inc.php';
$name = decrypt($userData['user_name']);

include_once '../../includes/config.php';
include_once '../../includes/extrasLoader.inc.php';
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../favicon.ico" />
    <title><?= $siteName; ?> | Επεξεργασία <?= $name; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/users/edituser.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">
        <div class="desktop">
            <p class="title">Επεξεργασία <?= $name; ?></p><br>
            <div class="edit-form">
                <a href="./" class="back-button">Πίσω</a>
                <?php if($userData['user_type'] == 0): ?>
                <form name="editForm" action="../../includes/admin/users/editstudent.inc.php" method="POST" onsubmit="return validateForm();">
                    <p class="field-label">Όνομα</p>
                    <input type="text" name="name" placeholder="π.χ.: Νίκος" value='<?= $name; ?>'>
                    <p class="field-label">Ψευδώνυμο</p>
                    <input type="text" readonly value='<?= $userData['user_username']; ?>' style="color:dimgray">
                    <p class="field-label">Νέος Κωδικός</p>
                    <input type="password" name="password" placeholder="Αν δεν αλλάξει, αφήστε το κενό">
                    <p class="field-label">Επανάληψη Νέου Κωδικού</p>
                    <input type="password" name="password-again">
                    <p class="field-label">Τάξη</p>
                    <select class="class-select" name="class">
                        <?php 
                            if(is_null($userData['user_class'])){
                                echo '<option value="no">&lt;Καμία&gt;</option>';
                            }
                            else{
                                $class = $userData['user_class'];
                                $result = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$class");
                                if($result->num_rows > 0){
                                    echo "<option value='$class'>" . $result->fetch_assoc()['class_name'] .'</option>';
                                    echo '<option value="no">&lt;Καμία&gt;</option>';
                                }
                                else echo '<option value="no">&lt;Καμία&gt;</option>'; 
                            }

                            include '../../includes/dbh.inc.php';
                            $res = mysqli_query($conn, "SELECT * FROM classes");
                            if($res->num_rows > 0){
                                while($row = $res->fetch_assoc()){
                                    $cid = $row['class_id'];
                                    $cname = $row['class_name'];
                                    echo "<option value='$cid'>$cname</option>";
                                }
                            }
                        ?>
                    </select>
                    <input type="hidden" name="username" value='<?= $username; ?>'> 
                    <button class="button" type="submit" name="submit" value="submit">Υποβολή</button>
                </form>
                        
                <form action="../../includes/admin/users/deleteuser.inc.php" method="POST" onsubmit="if(!confirm('Θέλετε σίγουρα να διαγράψετε τον μαθητή;'))return false;document.getElementById('action-hider').style.display = 'block';">
                    <input type="hidden" name="username" value='<?= $username; ?>'>
                    <button class="del-button" type="submit" name="delete" value="delete">Διαγραφή</button>
                </form>
                <?php elseif($userData['user_type'] == 1): ?>
                <form name="editForm" action="../../includes/admin/users/editteacher.inc.php" method="POST" onsubmit="return validateForm();">
                    <p class="field-label">Όνομα</p>
                    <input type="text" name="name" placeholder="π.χ.: Νίκος" value='<?= $name; ?>'>
                    <p class="field-label">Ψευδώνυμο</p>
                    <input type="text" readonly value='<?= $userData['user_username']; ?>' style="color:dimgray">
                    <p class="field-label">Νέος Κωδικός</p>
                    <input type="password" name="password" placeholder="Αν δεν αλλάξει, αφήστε το κενό">
                    <p class="field-label">Επανάληψη Νέου Κωδικού</p>
                    <input type="password" name="password-again">
                    <input type="hidden" name="username" value='<?= $username; ?>'> 
                    <button class="button" type="submit" name="submit" value="submit">Υποβολή</button>
                </form>
                        
                <form action="../../includes/admin/users/deleteuser.inc.php" method="POST" onsubmit="if(!confirm('Θέλετε σίγουρα να διαγράψετε τον μαθητή;'))return false;document.getElementById('action-hider').style.display = 'block';">
                    <input type="hidden" name="username" value='<?= $username; ?>'>
                    <button class="del-button" type="submit" name="delete" value="delete">Διαγραφή</button>
                </form>

                <?php endif; ?>
            </div>
        </div>



        <div class="mobile">
            <br><p class="title">Επεξεργασία Χρήστη</p><br>
            <div class="edit-form-mb">
                <a href="./" class="back-button">Πίσω</a>
                <?php if($userData['user_type'] == 0): ?>
                <form name="editForm-mb" action="../../includes/admin/users/editstudent.inc.php" method="POST" onsubmit="return validateForm_mb();">
                    <p class="field-label">Όνομα</p>
                    <input type="text" name="name" placeholder="π.χ.: Νίκος" value='<?= $name; ?>'>
                    <p class="field-label">Ψευδώνυμο</p>
                    <input type="text" readonly value='<?= $userData['user_username']; ?>' style="color:dimgray">
                    <p class="field-label">Νέος Κωδικός</p>
                    <input type="password" name="password" placeholder="Αν δεν αλλάξει, αφήστε το κενό">
                    <p class="field-label">Επανάληψη Νέου Κωδικού</p>
                    <input type="password" name="password-again">
                    <p class="field-label">Τάξη</p>
                    <select class="class-select" name="class">
                        <?php 
                            if(is_null($userData['user_class'])){
                                echo '<option value="no">&lt;Καμία&gt;</option>';
                            }
                            else{
                                $class = $userData['user_class'];
                                $result = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$class");
                                if($result->num_rows > 0){
                                    echo "<option value='$class'>" . $result->fetch_assoc()['class_name'] .'</option>';
                                    echo '<option value="no">&lt;Καμία&gt;</option>';
                                }
                                else echo '<option value="no">&lt;Καμία&gt;</option>'; 
                            }

                            include '../../includes/dbh.inc.php';
                            $res = mysqli_query($conn, "SELECT * FROM classes");
                            if($res->num_rows > 0){
                                while($row = $res->fetch_assoc()){
                                    $cid = $row['class_id'];
                                    $cname = $row['class_name'];
                                    echo "<option value='$cid'>$cname</option>";
                                }
                            }
                        ?>
                    </select>
                    <input type="hidden" name="username" value='<?= $username; ?>'> 
                    <button class="button" type="submit" name="submit" value="submit">Υποβολή</button>
                </form>
                        
                <form action="../../includes/admin/users/deleteuser.inc.php" method="POST" onsubmit="if(!confirm('Θέλετε σίγουρα να διαγράψετε τον μαθητή;'))return false;document.getElementById('action-hider').style.display = 'block';">
                    <input type="hidden" name="username" value='<?= $username; ?>'>
                    <button class="del-button" type="submit" name="delete" value="delete">Διαγραφή</button>
                </form>
                <?php elseif($userData['user_type'] == 1): ?>
                <form name="editForm-mb" action="../../includes/admin/users/editteacher.inc.php" method="POST" onsubmit="return validateForm_mb();">
                    <p class="field-label">Όνομα</p>
                    <input type="text" name="name" placeholder="π.χ.: Νίκος" value='<?= $name; ?>'>
                    <p class="field-label">Ψευδώνυμο</p>
                    <input type="text" readonly value='<?= $userData['user_username']; ?>' style="color:dimgray">
                    <p class="field-label">Νέος Κωδικός</p>
                    <input type="password" name="password" placeholder="Αν δεν αλλάξει, αφήστε το κενό">
                    <p class="field-label">Επανάληψη Νέου Κωδικού</p>
                    <input type="password" name="password-again">
                    <input type="hidden" name="username" value='<?= $username; ?>'> 
                    <button class="button" type="submit" name="submit" value="submit">Υποβολή</button>
                </form>
                        
                <form action="../../includes/admin/users/deleteuser.inc.php" method="POST" onsubmit="if(!confirm('Θέλετε σίγουρα να διαγράψετε τον καθηγητή;'))return false;document.getElementById('action-hider').style.display = 'block';">
                    <input type="hidden" name="username" value='<?= $username; ?>'>
                    <button class="del-button" type="submit" name="delete" value="delete">Διαγραφή</button>
                </form>

                <?php endif; ?>
            </div>
        </div>
        <div id="action-hider">
    <img src="../../resources/loading.gif"><br>
    <p>Παρακαλώ περιμένετε..</p>
</div>
    </div>
    <div id="footer"><?= LoadFooter(); ?></div>
</div>

<script>
function validateForm(){
        let n = document.forms["editForm"]["name"].value;
        let p = document.forms["editForm"]["password"].value;
        let pa = document.forms["editForm"]["password-again"].value;

        const preg = /^[A-Za-z0-9α-ωΑ-ΩςίϊΐόάέύϋΰήώΈΎΫΊΪΌΆΏΉ _-]+$/u;

        if(n == "" || n == " "){
            alert("Το όνομα δεν μπορεί να είναι κενό");
            return false;
        }
        if(!n.match(preg)){
            alert("Μόνο Ελληνικά, Αγγλικά, Νούμερα και οι χαρακτήρες ' _-' επιτρέπονται για το όνομα!");
            return false;
        }
        if(p != pa){
            alert("Οι κωδικοί δεν ταιριάζουν");
            return false;
        }
        document.getElementById('action-hider').style.display = 'block';
        return true;
    }

    function validateForm_mb(){
        let n = document.forms["editForm-mb"]["name"].value;
        let p = document.forms["editForm-mb"]["password"].value;
        let pa = document.forms["editForm-mb"]["password-again"].value;

        const preg = /^[A-Za-z0-9α-ωΑ-ΩςίϊΐόάέύϋΰήώΈΎΫΊΪΌΆΏΉ _-]+$/u;

        if(n == "" || n == " "){
            alert("Το όνομα δεν μπορεί να είναι κενό");
            return false;
        }
        if(!n.match(preg)){
            alert("Μόνο Ελληνικά, Αγγλικά, Νούμερα και οι χαρακτήρες ' _-' επιτρέπονται για το όνομα!");
            return false;
        }
        if(p != pa){
            alert("Οι κωδικοί δεν ταιριάζουν");
            return false;
        }
        document.getElementById('action-hider').style.display = 'block';
        return true;
    }
</script>

</body>
</html>