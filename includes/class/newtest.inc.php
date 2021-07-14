<?php
session_start();
if(isset($_SESSION['type']) && isset($_POST['s'])){
    if($_SESSION['type'] !== 'TEACHER'){
        include '../../error.php';
        exit();
    }

    if(!isset($_POST['name']) || !isset($_POST['year']) || !isset($_POST['month']) || !isset($_POST['day']) || !isset($_POST['hour']) || !isset($_POST['minute']) || !isset($_POST['vis']) || !isset($_POST['data'])){
        include '../../error.php';
        exit();
    }
    if(!is_numeric($_POST['s']) || !is_numeric($_POST['year']) || !is_numeric($_POST['month']) || !is_numeric($_POST['day']) || !is_numeric($_POST['hour']) || !is_numeric($_POST['minute']) || !is_numeric($_POST['vis'])){
        include '../../error.php';
        exit();
    }

    $subjId = (int)$_POST['s'];

    include '../dbh.inc.php';
    include '../enc.inc.php';

    $res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$subjId");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }

    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='subject-teacher' AND link_used_id=$subjId");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }

    $name = mysqli_real_escape_string($conn, encrypt($_POST['name']));

    $year = (int)$_POST['year'];
    $month = min(max((int)$_POST['month'], 1), 12);
    $dim = date('t', strtotime($year . '-' . $month . '-01'));
    $day = min(max((int)$_POST['day'], 1), $dim);
    $hour = min(max((int)$_POST['hour'], 0), 23);
    $minute = min(max((int)$_POST['minute'], 0), 59);
    $expires = $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':59';

    $vis = ($_POST['vis'] == 1) ? 1 : 0;

    $data = json_decode($_POST['data'], JSON_UNESCAPED_UNICODE);
    if(json_last_error() != JSON_ERROR_NONE){
        include '../../error.php';
        exit();
    }

    $safeData = [];
    foreach($data as $dt){
        if(!isset($dt['q'])) continue;
        if(!isset($dt['qd'])) continue;
        if(!isset($dt['a'])) continue;
        if(!isset($dt['ad'])) continue;

        if(!is_numeric($dt['q'])) continue;
        if(!is_numeric($dt['a'])) continue;

        $q = (int)$dt['q'];
        $a = (int)$dt['a'];

        $qd;
        $ad;

        if($q == 0){
            $qd = trim($dt['qd']);
            $qd = preg_replace('/(\r\n)|\r|\n/', '<br>', $qd);
            $qd = str_replace('\\n', '<br>', $qd);
            $qd = encrypt($qd);
        }
        elseif($q == 1){
            if(!is_numeric($dt['qd'])) continue;
            $qd = (int)$dt['qd'];
        }
        else {
            $q = 0;
            $qd = encrypt('<error>');
        }

        if($a == 0 || $a == 1){
            if(!is_array($dt['ad'])) continue;
            $goodArr = [];
            $ad = (array)$dt['ad'];
            foreach($ad as $inp){
                $text = trim($inp);
                $text = preg_replace('/(\r\n)|\r|\n/', '<br>', $text);
                $text = str_replace('\\n', '<br>', $text);
                $goodArr[] = encrypt($text);
            }
            $ad = $goodArr;
        }
        elseif($a == 2) $ad = 'k';
        else {
            $a = 2;
            $ad = 'err';
        }

        $safeData[] = ["q" => $q, "qd" => $qd, "a" => $a, "ad" => $ad];
    }

    $dataStr = json_encode($safeData, JSON_UNESCAPED_UNICODE);
    $dataStr = mysqli_real_escape_string($conn, base64_encode($dataStr));
    
    mysqli_query($conn, "INSERT INTO tests (test_user, test_subject, test_name, test_data, test_expires, test_visibility) VALUES ('$username', $subjId, '$name', '$dataStr', '$expires', $vis)");

    $tId = $conn->insert_id;
    echo 'ok-' . $tId;
    exit();
}
else {
    include '../../error.php';
    exit();
}