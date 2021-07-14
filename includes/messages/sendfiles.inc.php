<?php
session_start();
if(isset($_POST['files']) && isset($_SESSION['type'])){
    if(!isset($_POST['recipient']) || !isset($_POST['sender'])){
        include '../../error.php';
        exit();
    }

    if($_SESSION['user_username'] !== $_POST['sender']){
        include '../../error.php';
        exit();
    }

    include '../dbh.inc.php';
    include '../enc.inc.php';

    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
    $recipient = mysqli_real_escape_string($conn, $_POST['recipient']);

    $res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$recipient'");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }

    date_default_timezone_set('Europe/Athens');
    
    $files = explode(',', $_POST['files']);
    foreach($files as $fl){
        $file = mysqli_real_escape_string($conn, $fl);
        $res = mysqli_query($conn, "SELECT * FROM files WHERE file_uid='$file' AND file_owner='$username' LIMIT 1");
        if($res->num_rows > 0){
            $date = date('Y-m-d H:i:s', time());
            mysqli_query($conn, "INSERT INTO messages 
            (message_sender, message_recipient, message_date, message_content, message_type, message_opened) VALUES
            ('$username', '$recipient', '$date', '$file', 1, 0)");
        }
    }

    include_once "../notifications/sendnotification.inc.php";
    SendNotification($recipient, $_SESSION['user_name'] . ": Νέο Μήνυμα", 'messages/messages.php?u=' . rawurlencode($username));

    echo 'Sent!';
    exit();
}
else{
    include '../../error.php';
    exit();
}