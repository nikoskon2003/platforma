<?php
session_start();
if(isset($_SESSION['type']) && isset($_POST['tid'])){
    if($_SESSION['type'] !== 'TEACHER'){
        include '../../error.php';
        exit();
    }

    if(!isset($_POST['img']) || !isset($_POST['data'])){
        include '../../error.php';
        exit();
    }
    if(!is_numeric($_POST['tid']) || !is_numeric($_POST['img'])){
        include '../../error.php';
        exit();
    }
    if(substr($_POST['data'], 0, 11) !== "data:image/"){
        include '../../error.php';
        exit();
    }

    include '../dbh.inc.php';

    $testId = (int)$_POST['tid'];
    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);

    $res = mysqli_query($conn, "SELECT * FROM tests WHERE test_id=$testId AND test_user='$username' LIMIT 1");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }
    $testDat = json_decode(base64_decode($res->fetch_assoc()['test_data']), JSON_UNESCAPED_UNICODE);

    $found = false;
    $imgId = (int)$_POST['img'];

    foreach($testDat as $td)
        if($td['qd'] === $imgId){
            $found = true;
            break;
        }
    
    if(!$found){
        include '../../error.php';
        exit();
    }

    $img = explode(',', $_POST['data']);
    if(count($img) != 2){
        echo 'wrong data format';
        exit();
    }
    $img = base64_decode($img[1]);    
    $im = imagecreatefromstring($img);
    if($im === false){
        echo 'wrong image format';
        exit();
    }

    $data = $_POST['data'];

    $upPath = '../../uploads/tests/' . $testId;
    if (!file_exists($upPath)) mkdir($upPath, 0777, true);

    $filePath = $upPath . '/' . $imgId;
    file_put_contents($filePath, $data);

    echo 'ok';
    exit();
}
else {
    include '../../error.php';
    exit();
}