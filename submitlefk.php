<?php
session_start();
if(!isset($_SESSION['user_username'])){
 include './error.php';
 exit();
}
elseif(isset($_POST["submit"])){
 if(!isset($_FILES["file"])){
  header("Location: .");
  exit();
 }
 include './includes/dbh.inc.php';
 include './includes/enc.inc.php';

 if($_FILES["file"]["tmp_name"] == ""){
  header("Location: .");
  exit();
 }

 $rnd = mysqli_real_escape_string($conn, randomString(random_int(30, 40)));
 while(true){
  if(mysqli_query($conn, "SELECT * FROM lefkoma WHERE uid='$rnd'")->num_rows > 0)
   $rnd = mysqli_real_escape_string($conn, randomString(random_int(30, 50)));
  else break;
 }

 $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);

 $fileName = mysqli_real_escape_string($conn, $_FILES["file"]["name"]);
 $upPath = './uploads/lefk/' . rawurlencode($username);

 if (!file_exists($upPath)) mkdir($upPath, 0777, true);
 $filePath = $upPath . '/' . $rnd;
 if(move_uploaded_file($_FILES["file"]["tmp_name"], $filePath))
  mysqli_query($conn, "INSERT INTO lefkoma (uid, owner, name) VALUES ('$rnd', '$username', '$fileName')");
 header("Location: .");
 exit();
}
elseif(isset($_GET["i"])){
 include './includes/dbh.inc.php';
 $uid = mysqli_real_escape_string($conn, $_GET['i']);
 $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
 $res = mysqli_query($conn, "SELECT * FROM lefkoma WHERE uid='$uid' LIMIT 1");
 if($res->num_rows < 1){
  include './error.php';
  exit();
 }
 $filepath = './uploads/lefk/' . rawurlencode($username) . '/' . rawurlencode($uid);
 $filename = $res->fetch_assoc()['name'];

 if(!file_exists($filepath)){
  include './error.php';
  exit();
 }
 $mime = mime_content_type($filepath);

 header('title: File Transfer');
 header("Content-Type: $mime");
 header("Content-Disposition: attachment; filename=\"$filename\"");
 header('Content-Length: ' . filesize($filepath));

 $chunkSize = 10 * 1024 * 1024;
 $handle = fopen($filepath, 'rb');
 while (!feof($handle)){
  $buffer = fread($handle, $chunkSize);
  echo $buffer;
  ob_flush();
  flush();
  }
  fclose($handle);
  exit();
}
elseif(isset($_GET["d"])){
 include './includes/dbh.inc.php';
 $uid = mysqli_real_escape_string($conn, $_GET['d']);
 $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
 $newun = mysqli_real_escape_string($conn, $username . '-deleted-' . randomString(10)); //to lazy to implement proper deletion. Έδινα Πανελλήνιες, ΟΚ;
 mysqli_query($conn, "UPDATE lefkoma SET owner='$newun' WHERE uid='$uid' AND owner='$username' LIMIT 1");
 header("Location: .");
}
else {
 include './error.php';
 exit();
}
