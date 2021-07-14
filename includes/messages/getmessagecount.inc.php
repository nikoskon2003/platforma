<?php
session_start();
if(isset($_SESSION['type'])){
    if($_SESSION['type'] !== 'ADMIN'){
        include '../dbh.inc.php';
        $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
        $time = (int)time();
        mysqli_query($conn, "UPDATE users SET user_last_ping=$time WHERE user_username='$username'"); //used in getonline.inc.php

        $res = mysqli_query($conn, "SELECT * FROM messages WHERE message_recipient='$username' AND message_opened=0");
        $count = $res->num_rows;

        echo $count;
    }
    else echo -1;
}
else echo -1;