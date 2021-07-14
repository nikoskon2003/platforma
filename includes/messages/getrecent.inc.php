<?php
session_start();
if(isset($_SESSION['type'])){
    if($_SESSION['type'] !== 'ADMIN'){
        include '../dbh.inc.php';
        include '../enc.inc.php';

        $users = [];
        $count = [];

        $thisuser = mysqli_real_escape_string($conn, $_SESSION['user_username']);
        $res = mysqli_query($conn, "SELECT * FROM messages WHERE message_recipient='$thisuser' OR message_sender='$thisuser' ORDER BY message_date DESC");
        
        if($res->num_rows > 0)
        while($row = $res->fetch_assoc()){
            $username = $row['message_sender'];
            $date = base64_encode($row['message_date']);
            if($thisuser != $username){
                $c = ($row['message_opened'] == 0) ? 1 : 0;
                if(!in_array($username, $users)){
                    $u = mysqli_real_escape_string($conn, $username);
                    $resu = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$u'");
                    if($resu->num_rows > 0){
                        $name = $resu->fetch_assoc()['user_name'];                        
                        $name = decrypt($name);

                        array_push($users, $username);
                        array_push($count, base64_encode($username) . '|' . base64_encode($name) . '|' . $c . '|' . $date);
                    }
                }
                else{
                    for($i = 0; $i < count($count); $i++){
                        $data = explode('|', $count[$i]);
                        $uname = base64_decode($data[0]);
                        $nm = $data[1];
                        $tims = (int)$data[2];
                        $dt = $data[3];
    
                        if($uname == $username){
                            $tims += $c;
                            $count[$i] = base64_encode($username) . '|' . $nm . '|' . $tims . '|' . $dt;
                        }
                    }
                }
            }
            else {
                $username = $row['message_recipient'];
                if(!in_array($username, $users)){
                    $u = mysqli_real_escape_string($conn, $username);
                    $resu = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$u'");
                    if($resu->num_rows > 0){
                        $name = $resu->fetch_assoc()['user_name'];
                        $name = decrypt($name);
                        array_push($users, $username);
                        array_push($count, base64_encode($username) . '|' . base64_encode($name) . '|0|' . $date);
                    }
                }
            }
        }

        echo json_encode($count, JSON_UNESCAPED_UNICODE);
    }
    else echo -1;
}
else echo -1;