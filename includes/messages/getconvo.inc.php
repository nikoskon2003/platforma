<?php
session_start();
if(isset($_SESSION['type']) && isset($_POST['u'])){
    include '../dbh.inc.php';
    include '../enc.inc.php';

    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
    $otherUser = mysqli_real_escape_string($conn, $_POST['u']);

    $res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$otherUser' LIMIT 1");
    if($res->num_rows < 1){
        echo '[]';
        exit();
    }
  
    $sql;
    if(isset($_POST['id'])){
        if(!isset($_POST['act'])){
            echo '[]';
            exit();
        }
        if(!is_numeric($_POST['id']) || ($_POST['act'] !== 'old' && $_POST['act'] !== 'new')){
            echo '[]';
            exit();
        }

        $pid = (int)$_POST['id'];

        if($_POST['act'] == 'old')
            $sql = "SELECT * FROM messages WHERE ((message_sender='$username' AND message_recipient='$otherUser') OR (message_sender='$otherUser' AND message_recipient='$username')) AND message_id<$pid ORDER BY message_date DESC LIMIT 20";
        elseif($_POST['act'] == 'new')
            $sql = "SELECT * FROM messages WHERE ((message_sender='$username' AND message_recipient='$otherUser') OR (message_sender='$otherUser' AND message_recipient='$username')) AND message_id>$pid ORDER BY message_date DESC";
        else {
            echo '[]';
            exit();
        }
    }
    else {
        $n = mysqli_query($conn, "SELECT * FROM messages WHERE message_sender='$otherUser' AND message_recipient='$username' AND message_opened=0")->num_rows;
        $n += 20; //how many extra to show
        $sql = "SELECT * FROM messages WHERE ((message_sender='$username' AND message_recipient='$otherUser') OR (message_sender='$otherUser' AND message_recipient='$username')) ORDER BY message_date DESC LIMIT $n";
    }

    $messages = [];

    $res = mysqli_query($conn, $sql);
    if($res->num_rows < 1){
        echo '[]';
        exit();
    }
    else while($row = $res->fetch_assoc()){
        $msgId = (int)$row['message_id'];
        $date = $row['message_date'];
        $other = 0;
        if($row['message_sender'] == $otherUser) $other = 1;
        $read = 0;
        if($other == 0 && $row['message_opened'] == 1) $read = 1;

        $extras = $row['message_type'];
        if($extras == 1){
            $file = mysqli_real_escape_string($conn, $row['message_content']);
            $resu = mysqli_query($conn, "SELECT * FROM files WHERE file_uid='$file'");
            if($resu->num_rows < 1) continue;
            $filename = $resu->fetch_assoc()['file_name'];

            $str = $msgId . '|file|' . base64_encode($file) . ',' . base64_encode($filename) . '|' . base64_encode($date) . '|' . $other . '|' . $read;
            array_push($messages, $str);
        }
        else {
            $text = decrypt($row['message_content']);

            $str = $msgId . '|text|' . base64_encode($text) . '|' . base64_encode($date) . '|' . $other . '|' . $read;
            array_push($messages, $str);
        }
    }
    
    echo json_encode($messages, JSON_UNESCAPED_UNICODE);

    if(!isset($_POST['id']))
        mysqli_query($conn, "UPDATE messages SET message_opened=1 WHERE message_sender='$otherUser' AND message_recipient='$username'");
    elseif($_POST['act'] == 'new')
        mysqli_query($conn, "UPDATE messages SET message_opened=1 WHERE message_sender='$otherUser' AND message_recipient='$username'");

}
else echo '[]';
