<?php
session_start();
if(isset($_POST['u']) && isset($_SESSION['type'])){
    include '../dbh.inc.php';

    $thisUser = mysqli_real_escape_string($conn, $_SESSION['user_username']);
    $otherUser = mysqli_real_escape_string($conn, $_POST['u']);

    $res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$otherUser' LIMIT 1");
    if($res->num_rows < 1){
        echo '-1';
        exit();
    }

    $fromMe = -1;
    $fromOther = -1;

    $res = mysqli_query($conn, "SELECT * FROM messages WHERE message_sender='$thisUser' AND message_recipient='$otherUser' AND message_opened=1 ORDER BY message_date DESC LIMIT 1");
    if($res->num_rows < 1) $fromMe = -1;
    else $fromMe = (int)$res->fetch_assoc()['message_id'];

    $res = mysqli_query($conn, "SELECT * FROM messages WHERE message_sender='$otherUser' AND message_recipient='$thisUser' ORDER BY message_date DESC LIMIT 1");
    if($res->num_rows < 1) $fromOther = -1;
    else $fromOther = (int)$res->fetch_assoc()['message_id'];


    echo max($fromMe, $fromOther);
    exit();
}
else{
    echo '-1';
    exit();
}