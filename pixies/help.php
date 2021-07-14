<?php session_start();
if(!isset($_SESSION['type']))
{
    if(isset($_COOKIE["autologin"]))
    {
        header("Location: ../includes/autologin.inc.php?r=pixies/help.php");
        exit();
    }
    else
    {
        header("Location: ../login.php?r=pixies/help.php");
        exit();
    }
}

include_once '../includes/extrasLoader.inc.php';
?>
<!DOCTYPE html>
<meta charset="utf-8" />
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="shortcut icon" href="icon.png" />
    <title>Pixies | Βοήθεια</title>
	<meta name="description" content="Pixies | Βοήθεια">
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="help.css?v=<?= $pubFileVer; ?>" type="text/css">
	<link rel="stylesheet" href="../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
    <script src="../scripts/getmessages.js?v=<?= $pubFileVer; ?>"></script>
</head>

<body>
<div id="container">
	<div id="header"><?= LoadTopNav(__FILE__); ?></div>
	<div id="body">
		<div id="title">Pixies - Βοήθεια</div>
		<div id="help-area">
			<h2><u>Τι είναι;</u></h2><br>
			<p>Το '<a href=".">Pixies</a>' είναι βασισμένο στο <a target="_blank" href="https://en.wikipedia.org/wiki/Place_(Reddit)">/r/place</a> του <a target="_blank" href="https://www.reddit.com/">Reddit</a> - Ένα κοινωνικό πείραμα που έλαβε χώρα την 1η Απριλίου 2017.<br>
			Κάθε χρήστης του Reddit είχε δικαίωμα περίπου κάθε 5 λεπτά να αλλάζει το χρώμα ενός pixel σε έναν καμβά 1000x1000 pixels. Έτσι μόνο ένας χρήστης δεν μπορούσε να δημιουργήσει κάτι μεγάλο μόνος του, αφού αργά ή γρήγορα η προσπάθειά του μπορούσε να αντικατασταθεί από άλλους χρήστες. Οπότε η συνεργσία πολλών χρηστών ήταν απαραίτητη.<br>
			<br>
			Το 'Pixies' ομοίως είναι ένας καμβάς 256x256 pixels.
			</p>
			<br>
			<h2><u>Πως λειτουργεί;</u></h2><br>
			<p>Στην σελίδα υπάρχουν 2 μεγάλα τετράγωνα, μια παλέτα 16 χρωμάτων και ένα κουμπί αποστολής.<br>
			Το πρώτο μεγάλο τετράγωνο εμφανίζει τη συνολική εικόνα και χρησιμεύει στη μεγένθυση μιας περιοχής μεγέθους 32x32 pixels. Η επιλεγμένη περιοχή είναι <i>περίπου</i> αυτή που εμπεριέχεται στο μικρό γκρι τετραγωνάκι. Κάνοντας <i>click</i> σε ένα σημείο του τετραγώνου, η επιλεγμένη περιοχή θα αλλάξει με κέντρο το σημείο του πατήματος.<br>
			Το δεύτερο μεγάλο τετράγωνο εμφανίζει τη μεγεθυμένη περιοχή. Κάνοντας <i>click</i> πάνω σε αυτό, θα εμφανιστεί ένα πολύχρωμο τετραγωνάκι το οποίο συμβολίζει το επιλεγμένο pixel στο οποίο θα θέλατε να του αλλάξετε το χρώμα.<br>
			Μόλις έχετε επιλέξει το επιθυμητό σας pixel, θα πρέπει να επιλέξετε το χρώμα στο οποίο θα αλλάξει το pixel. Η επιλογή του χρώματος γίνεται με την παλέτα. Όταν επιλέξετε το επιθυμητό σας χρώμα, θα εμφανιστεί ένα πολύχρωμο περίγραμμα, δείχνοντας έτσι ποιο χρώμα έχετε επιλέξει.<br>
			Τέλος, μένει μόνο να πατήσετε το κουμπί "Αποστολή" για να αλλάξει το χρώμα του pixel.<br>
			Μετά την αποστολή, το κουμπί μέσα του θα εμφανίζει τον χρόνο που θα πρέπει να περιμένετε μέχρι να μπορείτε να αλλάξετε κάποιο άλλο pixel.
			</p>
			<br>
			<h2><u>Ανανεώνεται αυτόματα η εικόνα;</u></h2><br>
			<p>Ναι! Η εικόνα κανονικά ανανεώνεται κάθε 2 δευτερόλεπτα.<br>Όμως σε αργές συνδέσεις, εντοπίζεται αυτόματα ο χρόνος λήψης των αλλαγών και γίνεται ο ανάλογος υπολογισμός του χρόνου ανανέωσης. Συνεπώς μπορεί η εικόνα να ανανεώνεται σε χρόνο μεγαλύτερο αυτού των 2 δευτερολέπτων.
			</p>
			<br>
			<h2><u>Μπορώ να κατεβάσω την εικόνα;</u></h2><br>
			<p>Ναι! Μεταξύ των τετραγώνων και της παλέτας υπάρχει το κουμπί 'Λήψη' με το οποίο μπορείτε να κατεβάσετε τη νεώτερη εικόνα. 
			</p>
		</div>
	</div>
	<div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>