<?php
session_start();
if(isset($_COOKIE['autologin']))
{
    
    include 'dbh.inc.php';
    include 'enc.inc.php';

    $rawLoginData = $_COOKIE['autologin'];
    $decLoginData = decrypt($rawLoginData);
    $loginDataParts = explode('|', $decLoginData);

    if(count($loginDataParts) != 3){
        setcookie('autologin', 'BAD DATA', time() - 100);
        header("Location: ./logout.inc.php");
        exit();
    }
    else
    {
        $username = mysqli_real_escape_string($conn, decrypt($loginDataParts[0]));
        $secret = mysqli_real_escape_string($conn, $loginDataParts[1]);
        $verify = hash("sha256", $username . $secret);

        if($verify != $loginDataParts[2]){
            setcookie('autologin', 'BAD DATA', time() - 100);
            header("Location: ./logout.inc.php");
            exit();
        }

        $res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username' LIMIT 1");
        if($res->num_rows < 1){
            setcookie('autologin', 'BAD DATA', time() - 100);
            header("Location: ./logout.inc.php");
            exit();
        }
        else {
            $userData = $res->fetch_assoc();
            
            $userSecret = $userData['user_secret'];

            if($userSecret != $secret || hash("sha256", $username . $userSecret) != $loginDataParts[2]){
                setcookie('autologin', 'BAD DATA', time() - 100);
                header("Location: ./logout.inc.php");
                exit();
            }
            else{
                $_SESSION['user_id'] = $userData['user_id'];
                $_SESSION['user_name'] = decrypt($userData['user_name']);
                $_SESSION['user_username'] = $username;
                $_SESSION['user_class'] = $userData['user_class'];
                $_SESSION['type'] = ($userData['user_type'] == 1) ? 'TEACHER' : (($userData['user_type'] == 0) ? 'STUDENT' : '');

                setcookie('autologin', $rawLoginData, time() + 2.592e+6, '/'); //30 days
                
                if(isset($_GET['r'])) header("Location: ../" . $_GET['r']);
                else header("Location: ../");
                exit();
            }
        }
    }
}
else
{
    header("Location: ../");
    exit();
}