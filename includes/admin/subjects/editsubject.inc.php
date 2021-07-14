<?php
session_start();

if(isset($_POST['submit']) && isset($_SESSION['type'])){
    if($_SESSION['type'] !== 'ADMIN'){
        include '../../../error.php';
        exit();
    }

    if(!isset($_POST['name']) || !isset($_POST['class']) || !isset($_POST['id'])){
        header("Location: ../../../");
        exit();
    }
    if(!is_numeric($_POST['id'])){
        header("Location: ../../../");
        exit();
    }

    $subjId = (int)$_POST['id'];

    require_once '../../dbh.inc.php';
    require_once '../../enc.inc.php';
    $name = $_POST['name'];
    $class = mysqli_real_escape_string($conn, $_POST['class']);

    $res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$subjId");
    if($res->num_rows < 1){
        header("Location: ../../../");
        exit();
    }

    $preg = "/[^A-Za-z0-9α-ωΑ-ΩςίϊΐόάέύϋΰήώΈΎΫΊΪΌΆΏΉ _-]/u";
    $name = preg_replace($preg, '', $name);
    $name = trim($name);
    if($name == "" || $name == " "){
        header("Location: ../../../admin/subjects/editsubject.php?s=$subjId&e=empty");
        exit();
    }
    $name = mysqli_real_escape_string($conn, encrypt($name));

    if($class == "no") $class = 'NULL';
    else {
        $res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$class");
        if($res->num_rows < 1) $class = 'NULL';
    }

    mysqli_query($conn, "UPDATE subjects SET subject_name='$name', subject_class=$class WHERE subject_id=$subjId");  
    
    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-teacher' AND link_used_id=$subjId");
    if($res->num_rows < 1){
        if(isset($_POST['teachers'])){
            for($i = 0; $i < count($_POST['teachers']); $i++){
                $username = $_POST['teachers'][$i];
                $username = mysqli_real_escape_string($conn, $username);
                $resu = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username' AND user_type=1");
                if($resu->num_rows > 0)
                    mysqli_query($conn, "INSERT INTO user_links (link_user, link_usage, link_used_id) VALUES ('$username', 'subject-teacher', $subjId)");
                
            }
        }
    }
    else if(!isset($_POST['teachers'])){
        mysqli_query($conn, "DELETE FROM user_links WHERE link_usage='subject-teacher' AND link_used_id=$subjId");
    }
    else {
        $toAdd = [];
        $toRemove = [];
        $reqTeachers = $_POST['teachers'];
        $exiUsers = [];

        while($row = $res->fetch_assoc()){
            $username = $row['link_user'];
            $username = mysqli_real_escape_string($conn, $username);
            $resu = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username' AND user_type=1");
            if($resu->num_rows > 0){
                if(!in_array($username, $reqTeachers))
                    array_push($toRemove, $username);
            }
            array_push($exiUsers, $username);
        }

        for($i=0; $i < count($reqTeachers); $i++)
            if(!in_array($reqTeachers[$i], $exiUsers)){
                $username = mysqli_real_escape_string($conn, $reqTeachers[$i]);
                $resu = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username' AND user_type=1");
                if($resu->num_rows > 0)
                    array_push($toAdd, $username);
            }

        for($i=0; $i<count($toAdd); $i++){
            $username = mysqli_real_escape_string($conn, $toAdd[$i]);
            mysqli_query($conn, "INSERT INTO user_links (link_user, link_usage, link_used_id) VALUES ('$username', 'subject-teacher', $subjId)");
        }
        for($i=0; $i<count($toRemove); $i++){
            $username = mysqli_real_escape_string($conn, $toRemove[$i]);
            mysqli_query($conn, "DELETE FROM user_links WHERE link_usage='subject-teacher' AND link_used_id=$subjId AND link_user='$username'");
        }
    }


    header("Location: ../../../admin/subjects/subject.php?s=$subjId");
    exit();
}
else {
    include '../../../error.php';
    exit(); 
}