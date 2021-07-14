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
    <title><?= $siteName; ?> | Νέος Χρήστης</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/users/newuser.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">

        <div class="desktop">
            <p class="title">Νέος Χρήστης</p><br>
            <div class="user-type">
                <p class="field-label">Είδος χρήστη:</p>
                <button class="new-student" onclick="newStudent();">Μαθητής</button>
                <button class="new-teacher" onclick="newTeacher();">Καθηγητής</button>
                <a href="./" class="back-button">Πίσω</a>
            </div>

            <div class="student-form">
                <p class="field-label" style="font-size:25px;">Νέος Μαθητής</p>
                <form name="newStudentForm" action="../../includes/admin/users/newstudent.inc.php" method="POST" onsubmit="return validateForm('newStudentForm');">
                    <p class="field-label">Όνομα</p>
                    <input type="text" name="name" placeholder="π.χ.: Νίκος">
                    <p class="field-label">Ψευδώνυμο</p>
                    <input type="text" name="username" placeholder="π.χ.: nikos">
                    <p class="field-label">Κωδικός</p>
                    <input type="password" name="password" placeholder="π.χ.: 12345">
                    <p class="field-label">Επανάληψη Κωδικού</p>
                    <input type="password" name="password-again">
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
                    </select>
                    <button class="button" type="submit" name="submit" value="submit">Υποβολή</button>
                </form><br>
                <a onclick="resetForm();" class="back-button">Πίσω</a>
            </div>
            <div class="teacher-form">
            <p class="field-label" style="font-size:25px;">Νέος Καθηγητής</p>
                <form name="newTeacherForm" action="../../includes/admin/users/newteacher.inc.php" method="POST" onsubmit="return validateForm('newTeacherForm');">
                    <p class="field-label">Όνομα</p>
                    <input type="text" name="name" placeholder="π.χ.: Νίκος">
                    <p class="field-label">Ψευδώνυμο</p>
                    <input type="text" name="username" placeholder="π.χ.: nikos">
                    <p class="field-label">Κωδικός</p>
                    <input type="password" name="password" placeholder="π.χ.: 12345">
                    <p class="field-label">Επανάληψη Κωδικού</p>
                    <input type="password" name="password-again">
                    <button class="button" type="submit" name="submit" value="submit">Υποβολή</button>
                </form><br>
                <a onclick="resetForm();" class="back-button">Πίσω</a>
            </div>
        </div>



        <div class="mobile">
        <br><p class="title">Νέος Χρήστης</p><br>
            <div class="user-type-mb">
                <p class="field-label">Είδος χρήστη:</p>
                <button class="new-student" onclick="newStudent_mb();">Μαθητής</button>
                <button class="new-teacher" onclick="newTeacher_mb();">Καθηγητής</button>
                <a href="./" class="back-button">Πίσω</a>
            </div>

            <div class="student-form-mb">
                <p class="field-label" style="font-size:25px;">Νέος Μαθητής</p>
                <form name="newStudentForm-mb" action="../../includes/admin/users/newstudent.inc.php" method="POST" onsubmit="return validateForm('newStudentForm-mb');">
                    <p class="field-label">Όνομα</p>
                    <input type="text" name="name" placeholder="π.χ.: Νίκος">
                    <p class="field-label">Ψευδώνυμο</p>
                    <input type="text" name="username" placeholder="π.χ.: nikos">
                    <p class="field-label">Κωδικός</p>
                    <input type="password" name="password" placeholder="π.χ.: 12345">
                    <p class="field-label">Επανάληψη Κωδικού</p>
                    <input type="password" name="password-again">
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
                    </select>
                    <button class="button" type="submit" name="submit" value="submit">Υποβολή</button>
                </form><br>
                <a onclick="resetForm_mb();" class="back-button">Πίσω</a>
            </div>
            <div class="teacher-form-mb">
            <p class="field-label" style="font-size:25px;">Νέος Καθηγητής</p>
                <form name="newTeacherForm-mb" action="../../includes/admin/users/newteacher.inc.php" method="POST" onsubmit="return validateForm('newTeacherForm-mb');">
                    <p class="field-label">Όνομα</p>
                    <input type="text" name="name" placeholder="π.χ.: Νίκος">
                    <p class="field-label">Ψευδώνυμο</p>
                    <input type="text" name="username" placeholder="π.χ.: nikos">
                    <p class="field-label">Κωδικός</p>
                    <input type="password" name="password" placeholder="π.χ.: 12345">
                    <p class="field-label">Επανάληψη Κωδικού</p>
                    <input type="password" name="password-again">
                    <button class="button" type="submit" name="submit" value="submit">Υποβολή</button>
                </form><br>
                <a onclick="resetForm_mb();" class="back-button">Πίσω</a>
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
    function newStudent(){
        var o = document.getElementsByClassName('user-type')[0];
        o.style.display = "none";
        var s = document.getElementsByClassName('student-form')[0];
        s.style.display = "block";
    }
    function newTeacher(){
        var o = document.getElementsByClassName('user-type')[0];
        o.style.display = "none";
        var t = document.getElementsByClassName('teacher-form')[0];
        t.style.display = "block";
    }
    function resetForm(){
        var o = document.getElementsByClassName('user-type')[0];
        o.style.display = "block";
        var s = document.getElementsByClassName('teacher-form')[0];
        s.style.display = "none";
        var t = document.getElementsByClassName('student-form')[0];
        t.style.display = "none";
    }

    function newStudent_mb(){
        var o = document.getElementsByClassName('user-type-mb')[0];
        o.style.display = "none";
        var s = document.getElementsByClassName('student-form-mb')[0];
        s.style.display = "block";
    }
    function newTeacher_mb(){
        var o = document.getElementsByClassName('user-type-mb')[0];
        o.style.display = "none";
        var t = document.getElementsByClassName('teacher-form-mb')[0];
        t.style.display = "block";
    }
    function resetForm_mb(){
        var o = document.getElementsByClassName('user-type-mb')[0];
        o.style.display = "block";
        var s = document.getElementsByClassName('teacher-form-mb')[0];
        s.style.display = "none";
        var t = document.getElementsByClassName('student-form-mb')[0];
        t.style.display = "none";
    }

    function validateForm(name){
        let n = document.forms[name]["name"].value;
        let un = document.forms[name]["username"].value;
        let p = document.forms[name]["password"].value;
        let pa = document.forms[name]["password-again"].value;

        const preg = /^[A-Za-z0-9α-ωΑ-ΩςίϊΐόάέύϋΰήώΈΎΫΊΪΌΆΏΉ _-]+$/u;

        if(n == "" || n == " "){
            alert("Το όνομα δεν μπορεί να είναι κενό");
            return false;
        }
        if(!n.match(preg)){
            alert("Μόνο Ελληνικά, Αγγλικά, Νούμερα και οι χαρακτήρες ' _-' επιτρέπονται για το όνομα!");
            return false;
        }
        if(un == "" || un == " "){
            alert("Το ψευδώνυμο δεν μπορεί να είναι κενό");
            return false;
        }
        if(!un.match(preg)){
            alert("Μόνο Ελληνικά, Αγγλικά, Νούμερα και οι χαρακτήρες ' _-' επιτρέπονται για το ψευδώνυμο!");
            return false;
        }
        if(p == "" || p == " "){
            alert("Ο κωδικός δεν μπορεί να είναι κενός");
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