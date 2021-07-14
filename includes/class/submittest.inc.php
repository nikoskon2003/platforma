<?php
session_start();
if(isset($_SESSION['type']) && isset($_POST['id'])){
    if($_SESSION['type'] !== 'STUDENT'){
        include '../../error.php';
        exit();
    }
    if(!is_numeric($_POST['id'])){
        include '../../error.php';
        exit();
    }

    $testId = (int)$_POST['id'];

    include '../dbh.inc.php';

    $res = mysqli_query($conn, "SELECT * FROM tests WHERE test_id=$testId LIMIT 1");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }
    $row = $res->fetch_assoc();
    $testVis = (int)$row['test_visibility'];
    $testData = $row['test_data'];
    $testSubject = (int)$row['test_subject'];
    $exp = new DateTime($row['test_expires']);

    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);

    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='subject-student' AND link_used_id=$testSubject");
    if($res->num_rows < 1){
        if(!is_null($_SESSION['user_class'])){
            $res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$testSubject LIMIT 1");
            if($res->num_rows < 1){
                include '../../error.php';
                exit();
            }
            $subjClass = $res->fetch_assoc()['subject_class'];
            if($_SESSION['user_class'] != $subjClass){
                include '../../error.php';
                exit();
            }
        }
        else {
            include '../../error.php';
            exit();
        }
    }

    $res = mysqli_query($conn, "SELECT * FROM test_responses WHERE response_test=$testId AND response_user='$username'");
    if($res->num_rows > 0){
        $row = $res->fetch_assoc();
        if(!is_null($row['response_data'])){
            header("Location: ../../class/test.php?id=$testId");
            exit();
        }
    }
    else {
        header("Location: ../../class/test.php?id=$testId");
        exit();
    }

    $now = new DateTime(date('Y-m-d H:i:s', time()));
    $exp->add(new DateInterval('PT1H')); //gives one more hour just in case :)

    if($now > $exp){
        header("Location: ../../class/test.php?id=$testId");
        exit();
    }

    $testData = json_decode(base64_decode($testData), JSON_UNESCAPED_UNICODE);
    if(json_last_error() != JSON_ERROR_NONE){
        echo 'Υπάρχει πρόβλημα με το test!';
        exit();
    }

    $safeData = [];
    for($i = 0; $i < count($testData); $i++){
        $a = $testData[$i]['a'];
        $ad = $testData[$i]['ad'];

        if($a == 0){ //radio
            if(!isset($_POST['ans-' . $i])){
                $safeData[] = [0, 0];
                continue;
            }
            elseif(!is_numeric($_POST['ans-' . $i])){
                $safeData[] = [0, 0];
                continue;
            }
            
            $sel = (int)$_POST['ans-' . $i];
            if(count($ad) < $sel){
                $safeData[] = [0, 0];
                continue;
            }

            $safeData[] = [$sel, 0];
        }
        elseif($a == 1){
            if(!isset($_POST['ans-' . $i])){
                $safeData[] = [[], 0];
                continue;
            }
            elseif(!is_array($_POST['ans-' . $i])){
                $safeData[] = [[], 0];
                continue;
            }
            
            $sel = (array)$_POST['ans-' . $i];
            if(count($ad) < count($sel)){
                $safeData[] = [[], 0];
                continue;
            }

            $validSel = [];
            foreach($sel as $opt){
                if(!is_numeric($opt)) continue;
                $op = (int)$opt;
                if(count($ad) <= ($op-1)) continue;
                if(in_array($op, $validSel)) continue;
                $validSel[] = $op;
            }

            $safeData[] = [$validSel, 0];
        }
        elseif($a == 2){
            if(!isset($_POST['ans-' . $i])){
                $safeData[] = ["", 0];
                continue;
            }
            if(!is_string($_POST['ans-' . $i])){
                $safeData[] = ["", 0];
                continue;
            }

            $text = trim($_POST['ans-' . $i]);
            $text = preg_replace('/(\r\n)|\r|\n/', '<br>', $text);
            $text = str_replace('\\n', '<br>', $text);

            $safeData[] = [$text, 0];
        }
        else $safeData[] = [0, 0];
    }

    $now = time();

    $dataStr = json_encode($safeData, JSON_UNESCAPED_UNICODE);
    $dataStr = mysqli_real_escape_string($conn, base64_encode($dataStr));
    
    mysqli_query($conn, "UPDATE test_responses SET response_end=$now, response_data='$dataStr' WHERE response_test=$testId AND response_user='$username'");
    header("Location: ../../class/test.php?id=$testId");
    exit();
}
else {
    include '../../error.php';
    exit();
}