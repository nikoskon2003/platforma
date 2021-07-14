<?php
session_start();
if(isset($_SESSION['type']) && isset($_POST['id'])){
    if($_SESSION['type'] !== 'TEACHER'){
        include '../../error.php';
        exit();
    }

    if(!isset($_POST['data'])){
        include '../../error.php';
        exit();
    }
    if(!is_numeric($_POST['id'])){
        include '../../error.php';
        exit();
    }

    $testId = (int)$_POST['id'];

    include '../dbh.inc.php';
    include '../enc.inc.php';

    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);

    $res = mysqli_query($conn, "SELECT * FROM tests WHERE test_id=$testId");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }
    $row = $res->fetch_assoc();

    $subjId = $row['test_subject'];
    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='subject-teacher' AND link_used_id=$subjId");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }

    $orignalData = $row['test_data'];
    $orignalData = base64_decode($orignalData);
    $orignalData = json_decode($orignalData, JSON_UNESCAPED_UNICODE);

    $newData = json_decode($_POST['data'], JSON_UNESCAPED_UNICODE);
    if(json_last_error() != JSON_ERROR_NONE){
        include '../../error.php';
        exit();
    }
    $safeData = [];

    if(count($orignalData) != count($newData)){
        include '../../error.php';
        exit();
    }

    for($i = 0; $i < count($orignalData); $i++){
        $dt = $orignalData[$i];
        $q = (int)$dt['q'];
        $a = (int)$dt['a'];

        $qd;
        $ad;

        if($q == 0){
            if(isset($newData[$i]['qd'])){
                $qd = trim($newData[$i]['qd']);
                $qd = preg_replace('/(\r\n)|\r|\n/', '<br>', $qd);
                $qd = str_replace('\\n', '<br>', $qd);
                $qd = encrypt($qd);
            }
        }
        else $qd = $dt['qd']; //new = old

        if($a == 0 || $a == 1){
            if(!is_array($dt['ad'])) continue;
            if(!is_array($newData[$i]['ad'])) continue;

            if(count($dt['ad']) != count($newData[$i]['ad'])) continue;

            $goodArr = [];
            $ad = (array)$newData[$i]['ad'];
            foreach($ad as $inp){
                $text = trim($inp);
                $text = preg_replace('/(\r\n)|\r|\n/', '<br>', $text);
                $text = str_replace('\\n', '<br>', $text);
                $goodArr[] = encrypt($text);
            }
            $ad = $goodArr;
        }
        else $ad = $dt['ad']; //new = old

        $safeData[] = ["q" => $q, "qd" => $qd, "a" => $a, "ad" => $ad];
    }

    $dataStr = json_encode($safeData, JSON_UNESCAPED_UNICODE);
    $dataStr = mysqli_real_escape_string($conn, base64_encode($dataStr));
    
    mysqli_query($conn, "UPDATE tests SET test_data='$dataStr', test_user='$username' WHERE test_id=$testId");
    echo 'ok';
    exit();
}
else {
    include '../../error.php';
    exit();
}