<?php
session_start();

if(isset($_POST['submit']) && isset($_SESSION['type'])){
    if($_SESSION['type'] !== 'ADMIN'){
        include '../../../error.php';
        exit();
    }

    if(!isset($_POST['id'])){
        header("Location: ../../../");
        exit();
    }
    if(!is_numeric($_POST['id'])){
        header("Location: ../../../");
        exit();
    }

    $subjId = (int)$_POST['id'];

    require_once '../../dbh.inc.php';

    $res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$subjId");
    if($res->num_rows < 1){
        header("Location: ../../../");
        exit();
    }

    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-student' AND link_used_id=$subjId");
    if($res->num_rows < 1){
        if(isset($_POST['students'])){
            for($i = 0; $i < count($_POST['students']); $i++){
                $username = $_POST['students'][$i];
                $username = mysqli_real_escape_string($conn, $username);
                $resu = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username' AND user_type=0");
                if($resu->num_rows > 0)
                    mysqli_query($conn, "INSERT INTO user_links (link_user, link_usage, link_used_id) VALUES ('$username', 'subject-student', $subjId)");
                
            }
        }
    }
    else if(!isset($_POST['students'])){
        mysqli_query($conn, "DELETE FROM user_links WHERE link_usage='subject-student' AND link_used_id=$subjId");
    }
    else {
        $toAdd = [];
        $toRemove = [];
        $reqStudents = $_POST['students'];
        $exiUsers = [];

        while($row = $res->fetch_assoc()){
            $username = $row['link_user'];
            $username = mysqli_real_escape_string($conn, $username);
            $resu = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username' AND user_type=0");
            if($resu->num_rows > 0){
                if(!in_array($username, $reqStudents))
                    array_push($toRemove, $username);
            }
            array_push($exiUsers, $username);
        }

        for($i=0; $i < count($reqStudents); $i++)
            if(!in_array($reqStudents[$i], $exiUsers)){
                $username = mysqli_real_escape_string($conn, $reqStudents[$i]);
                $resu = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username' AND user_type=0");
                if($resu->num_rows > 0)
                    array_push($toAdd, $username);
            }

        for($i=0; $i<count($toAdd); $i++){
            $username = mysqli_real_escape_string($conn, $toAdd[$i]);
            mysqli_query($conn, "INSERT INTO user_links (link_user, link_usage, link_used_id) VALUES ('$username', 'subject-student', $subjId)");
        }
        for($i=0; $i<count($toRemove); $i++){
            $username = mysqli_real_escape_string($conn, $toRemove[$i]);
            mysqli_query($conn, "DELETE FROM user_links WHERE link_usage='subject-student' AND link_used_id=$subjId AND link_user='$username'");
        }
    }


    header("Location: ../../../admin/subjects/subject.php?s=$subjId");
    exit();
}
else {
    include '../../../error.php';
    exit(); 
}