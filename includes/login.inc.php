<?php
session_start();
if(isset($_POST['submit'])){

    if(!isset($_POST['username']) || !isset($_POST['password'])){
        header("Location: ../login.php?login=empty");
        exit();
    }

    require_once 'config.php';
    require_once 'dbh.inc.php';
    require_once 'enc.inc.php';
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));

    if($username == "" || $username == " " || $password == "" || $password == " "){
        header("Location: ../login.php?login=empty");
        exit();
    }
    
    if($username === "admin"){
		
		$h = hash('sha256', $password);
		$e = encrypt($password);
		$f = (int)base_convert($h[0], 36, 10) + 1;
		$encPass = substr_replace($h, $e, $f, 0);
		
        if($encPass === $adminPasswordHash){
            $_SESSION['user_name'] = 'Administrator';
            $_SESSION['user_username'] = 'admin';
            $_SESSION['type'] = 'ADMIN';
            if(isset($_POST['r'])) header("Location: ../" . $_POST['r']);
            else header("Location: ../");
            exit();
        }
    }

    $res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username'");
    if($res->num_rows < 1)
    {
        header("Location: ../login.php?login=empty");
        exit();
    }
    else {
        $data = $res->fetch_assoc();
		
		$h = hash('sha256', $password);
		$e = encrypt($password);
		$f = (int)base_convert($h[0], 36, 10) + 1;
		$encPass = substr_replace($h, $e, $f, 0);

        if($encPass !== $data['user_password']){
            header("Location: ../login.php?login=empty");
            exit();
        }
        else 
        {
            $_SESSION['user_id'] = $data['user_id'];
            $_SESSION['user_name'] = decrypt($data['user_name']);
            $_SESSION['user_username'] = $username;
            $_SESSION['user_class'] = $data['user_class'];
            $_SESSION['type'] = ($data['user_type'] == 1) ? 'TEACHER' : (($data['user_type'] == 0) ? 'STUDENT' : '');

            //set cookie if selected
            if(isset($_POST['autologin']))
                if($_POST['autologin'] == 'on'){
                    $secret = $data['user_secret'];

                    if(!isset($secret)) $secret = randomString(128);
                    if(strlen($secret) < 128 ) $secret = randomString(128);

                    $tosave = encrypt(encrypt($username) . '|' . $secret . '|' . hash("sha256", $username . $secret));

                    setcookie('autologin', $tosave, time() + 2.592e+6, '/'); //30 days
                    mysqli_query($conn, "UPDATE users SET user_secret='$secret' WHERE user_username='$username'");
                }
            
            if(isset($_POST['r'])) header("Location: ../" . $_POST['r']);
            else header("Location: ../");
            exit();
        }
    }
    
    header("Location: ../");
    exit();
}
else
{
    header("Location: ../");
    exit();
}