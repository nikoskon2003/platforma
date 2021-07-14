<?php 
session_start();
if(isset($_SESSION['type']) && isset($_POST['id'])){
    if($_SESSION['type'] !== 'TEACHER'){
        include '../../error.php';
        exit();
    }
    if(!isset($_POST['name']) || !isset($_POST['year']) || !isset($_POST['month']) || !isset($_POST['day']) || !isset($_POST['hour']) || !isset($_POST['minute']) || !isset($_POST['visibility'])){
        include '../../error.php';
        exit();
    }
    if(!is_numeric($_POST['id']) || !is_numeric($_POST['year']) || !is_numeric($_POST['month']) || !is_numeric($_POST['day']) || !is_numeric($_POST['hour']) || !is_numeric($_POST['minute'])){
        include '../../error.php';
        exit();
    }

    $testId = (int)$_POST['id'];

    include '../dbh.inc.php';
    include '../enc.inc.php';

    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);

    $res = mysqli_query($conn, "SELECT * FROM tests WHERE test_id=$testId LIMIT 1");
    if($res->num_rows < 1){
        include '../../error.php';
        exit();
    }
    $subjId = (int)$res->fetch_assoc()['test_subject'];

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

    $vis = ($_POST['visibility'] == 'all') ? 1 : 0;

    mysqli_query($conn, "UPDATE tests SET test_user='$username', test_name='$name', test_expires='$expires', test_visibility='$vis' WHERE test_id=$testId LIMIT 1");

    header("Location: ../../class/test.php?id=$testId");
    exit();
}
else {
    include '../../error.php';
    exit();
}