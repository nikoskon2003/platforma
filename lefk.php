<?php
session_start();

if(!isset($_SESSION['type'])){
    include 'error.php';
    exit();
}

include 'includes/dbh.inc.php';

echo '<div style="width:100%;background-color:white;margin-top:10px;border-radius:2px;font-family:\'Noto Sans\'">
<form method="POST" action="./submitlefk.php" enctype="multipart/form-data">
<input type="file" name="file" /><br>
<button name="submit" type="submit" value="1">Υποβολή</button>
</form><br>';
$nowun = mysqli_real_escape_string($conn, $_SESSION['user_username']);
$res = mysqli_query($conn, "SELECT * FROM lefkoma WHERE owner='$nowun' ORDER BY id DESC");
while($row = $res->fetch_assoc())
echo '<div style="width:100%;background-color:#e5e5e5;display:block;text-align:left;border-top:1px solid #111;">
<a style="display:inline-block;overflow:hidden;max-width:calc(100% - 80px)" href="./submitlefk.php?i=' . $row["uid"] . '" target="_blank">' . htmlentities($row["name"]) . '</a>
<a href="./submitlefk.php?d=' . $row["uid"] . '" style="float:right;color:red;" onclick="return confirm(\'Είστε σίγουροι ότι θέλετε να διαγράψετε το αρχείο;\')">Διαγραφή</a>
</div>';
echo '</div></div>';