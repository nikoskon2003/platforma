<?php
session_start();
if(isset($_SESSION['type']) && isset($_POST['id'])){
    if($_SESSION['type'] !== 'TEACHER'){
        include '../../error.php';
        exit();
    }
    if(!is_numeric($_POST['id'])){
        include '../../error.php';
        exit();
    }

    $replyId = (int)$_POST['id'];

    include '../dbh.inc.php';

    $res = mysqli_query($conn, "SELECT * FROM test_responses WHERE response_id=$replyId");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }
    $row = $res->fetch_assoc();
    $data = json_decode(base64_decode($row['response_data']), JSON_UNESCAPED_UNICODE);

    $testId = (int)$row['response_test'];
    $res = mysqli_query($conn, "SELECT test_subject FROM tests WHERE test_id='$testId' LIMIT 1");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }
    $subjId = $res->fetch_assoc()['test_subject'];

    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_user='$username' AND link_usage='subject-teacher' AND link_used_id=$subjId");
    if($res->num_rows < 1){
        include '../error.php';
        exit();
    }


    $finalData = [];

    for($i = 0; $i < count($data); $i++){
        $finalData[$i] = $data[$i];

        if(isset($_POST['ans-' . $i])){
            if($_POST['ans-' . $i] == 'cor') $finalData[$i][1] = 1;
            elseif($_POST['ans-' . $i] == 'wro') $finalData[$i][1] = 2;
            else $finalData[$i][1] = 0;
        }
    }

    $dataStr = json_encode($finalData, JSON_UNESCAPED_UNICODE);
    $dataStr = mysqli_real_escape_string($conn, base64_encode($dataStr));
    
    mysqli_query($conn, "UPDATE test_responses SET response_data='$dataStr' WHERE response_id=$replyId");
    header("Location: ../../class/reply.php?r=$replyId");
    exit();
}
else {
    include '../../error.php';
    exit();
}