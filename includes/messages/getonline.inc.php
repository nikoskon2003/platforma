<?php
session_start();
if(isset($_SESSION['type'])){
    include '../dbh.inc.php';
    if($_SESSION['type'] == 'STUDENT'){
        $checked = [];
        $online = [];
        if(isset($_SESSION['user_class'])){
            $class = (int)$_SESSION['user_class'];
            $res = mysqli_query($conn, "SELECT subject_id FROM subjects WHERE subject_class=$class");
            if($res->num_rows > 0)
            while($row = $res->fetch_assoc()){
                $subjId = (int)$row['subject_id'];
                $resu = mysqli_query($conn, "SELECT link_user FROM user_links WHERE link_usage='subject-teacher' AND link_used_id=$subjId");
                if($resu->num_rows > 0) while($row = $resu->fetch_assoc()){
                    $username = mysqli_real_escape_string($conn, $row['link_user']);
                    if(!in_array($username, $checked)){
                        array_push($checked, $username);
                        $result = mysqli_query($conn, "SELECT user_last_ping FROM users WHERE user_username='$username' LIMIT 1");
                        if($result->num_rows > 0){
                            $ping = (int)$result->fetch_assoc()['user_last_ping'];
                            $time = time();
                            if($time - $ping < 90){ //online within 90 seconds
                                array_push($online, $username);
                            }
                        }
                    }
                }
            }
        }
        $uname = mysqli_real_escape_string($conn, $_SESSION['user_username']);
        $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-student' AND link_user='$uname'");
        if($res->num_rows > 0)
        while($row = $res->fetch_assoc()){
            $subjId = (int)$row['link_used_id'];
            $resu = mysqli_query($conn, "SELECT link_user FROM user_links WHERE link_usage='subject-teacher' AND link_used_id=$subjId");
            if($resu->num_rows > 0)
            while($row = $resu->fetch_assoc()){
                $username = mysqli_real_escape_string($conn, $row['link_user']);
                if(!in_array($username, $checked)){
                    array_push($checked, $username);
                    $result = mysqli_query($conn, "SELECT user_last_ping FROM users WHERE user_username='$username' LIMIT 1");
                    if($result->num_rows > 0){
                        $ping = (int)$result->fetch_assoc()['user_last_ping'];
                        $time = time();
                        if($time - $ping < 90){ //online within 90 seconds
                            array_push($online, $username);
                        }
                    }
                }
            }
        }

        $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='group-student' AND link_user='$uname'");
        if($res->num_rows > 0)
        while($row = $res->fetch_assoc()){
            $groupId = (int)mysqli_real_escape_string($conn, $row['link_used_id']);
            $resu = mysqli_query($conn, "SELECT link_user FROM user_links WHERE link_usage='group-teacher' AND link_used_id=$groupId");
            if($resu->num_rows > 0)
            while($row = $resu->fetch_assoc()){
                $username = mysqli_real_escape_string($conn, $row['link_user']);
                if(!in_array($username, $checked)){
                    array_push($checked, $username);
                    $result = mysqli_query($conn, "SELECT user_last_ping FROM users WHERE user_username='$username' LIMIT 1");
                    if($result->num_rows > 0){
                        $ping = (int)$result->fetch_assoc()['user_last_ping'];
                        $time = time();
                        if($time - $ping < 90){ //online within 90 seconds
                            array_push($online, $username);
                        }
                    }
                }
            }
        }

        $res = mysqli_query($conn, "SELECT message_sender FROM messages WHERE message_recipient='$uname'");
        if($res->num_rows > 0)
        while($row = $res->fetch_assoc()){
            $username = mysqli_real_escape_string($conn, $row['message_sender']);
            if(!in_array($username, $checked)){
                array_push($checked, $username);
                $result = mysqli_query($conn, "SELECT user_last_ping FROM users WHERE user_username='$username' LIMIT 1");
                if($result->num_rows > 0){
                    $ping = (int)$result->fetch_assoc()['user_last_ping'];
                    $time = time();
                    if($time - $ping < 90){ //online within 90 seconds
                        array_push($online, $username);
                    }
                }
            }
        }

        echo json_encode($online, JSON_UNESCAPED_UNICODE);
        exit();
    }
    elseif($_SESSION['type'] === 'TEACHER'){
        $checked = [];
        $online = [];
        $uname = mysqli_real_escape_string($conn, $_SESSION['user_username']);
        $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-teacher' AND link_user='$uname'");
        if($res->num_rows > 0)
        while($row = $res->fetch_assoc()){
            $subjId = mysqli_real_escape_string($conn, $row['link_used_id']);
            $resu = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$subjId LIMIT 1");
            if($resu->num_rows > 0){
                $row = $resu->fetch_assoc();
                if(isset($row['subject_class'])){
                    $classId = (int)$row['subject_class'];
                    $resu = mysqli_query($conn, "SELECT * FROM users WHERE user_class=$classId");
                    if($resu->num_rows > 0)
                    while($row = $resu->fetch_assoc()){
                        $username = mysqli_real_escape_string($conn, $row['user_username']);
                        if(!in_array($username, $checked)){
                            array_push($checked, $username);
                            $ping = (int)$row['user_last_ping'];
                            $time = time();
                            if($time - $ping < 90){ //online within 90 seconds
                                array_push($online, $username);
                            }
                        }
                    }
                }

                $resu = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-student' AND link_used_id=$subjId");
                if($resu->num_rows > 0)
                while($row = $resu->fetch_assoc()){
                    $username = mysqli_real_escape_string($conn, $row['link_user']);
                    if(!in_array($username, $checked)){
                        array_push($checked, $username);
                        $result = mysqli_query($conn, "SELECT user_last_ping FROM users WHERE user_username='$username' LIMIT 1");
                        if($result->num_rows > 0){
                            $ping = (int)$result->fetch_assoc()['user_last_ping'];
                            $time = time();
                            if($time - $ping < 90){ //online within 90 seconds
                                array_push($online, $username);
                            }
                        }
                    }
                }
            }
        }

        $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='group-teacher' AND link_user='$uname'");
        if($res->num_rows > 0)
        while($row = $res->fetch_assoc()){
            $groupId = (int)$row['link_used_id'];
            $resu = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='group-user' AND link_used_id=$groupId");
            if($resu->num_rows > 0)
            while($row = $resu->fetch_assoc()){
                $username = mysqli_real_escape_string($conn, $row['link_user']);
                if(!in_array($username, $checked)){
                    array_push($checked, $username);
                    $result = mysqli_query($conn, "SELECT user_last_ping FROM users WHERE user_username='$username' LIMIT 1");
                    if($result->num_rows > 0){
                        $ping = (int)$result->fetch_assoc()['user_last_ping'];
                        $time = time();
                        if($time - $ping < 90){ //online within 90 seconds
                            array_push($online, $username);
                        }
                    }
                }
            }
        }

        $res = mysqli_query($conn, "SELECT message_sender FROM messages WHERE message_recipient='$uname'");
        if($res->num_rows > 0)
        while($row = $res->fetch_assoc()){
            $username = mysqli_real_escape_string($conn, $row['message_sender']);
            if(!in_array($username, $checked)){
                array_push($checked, $username);
                $result = mysqli_query($conn, "SELECT user_last_ping FROM users WHERE user_username='$username' LIMIT 1");
                if($result->num_rows > 0){
                    $ping = (int)$result->fetch_assoc()['user_last_ping'];
                    $time = time();
                    if($time - $ping < 90){ //online within 90 seconds
                        array_push($online, $username);
                    }
                }
            }
        }

        echo json_encode($online, JSON_UNESCAPED_UNICODE);
        exit();
    }
}
else echo '[]';