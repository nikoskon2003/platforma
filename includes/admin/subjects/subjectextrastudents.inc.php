<?php
session_start();
if(isset($_POST['submit']) && isset($_SESSION['type'])){
    if($_SESSION['type'] !== 'ADMIN'){
        include '../../../error.php';
        exit();
    }
    else {
        if(!isset($_POST['id'])){
            header("Location: ../../../");
            exit();
        }
        if(!is_numeric($_POST['id'])){
            header("Location: ../../../");
            exit();
        }

        require_once '../dbh.inc.php';
        $id = (int)mysqli_real_escape_string($conn, $_POST['id']);

        $res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$id");
        if($res->num_rows < 1){
            header("Location: ../../../");
            exit();
        }

        $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-extra-user' AND link_column_id=$id");
        if($res->num_rows < 1){
            if(isset($_POST['students'])){
                for($i = 0; $i < count($_POST['students']); $i++){
                    $username = $_POST['students'][$i];
                    $username = mysqli_real_escape_string($conn, $username);
                    $resu = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username' AND user_type='STUDENT'");
                    if($resu->num_rows > 0){
                        mysqli_query($conn, "INSERT INTO user_links (link_usage, link_column_id, link_user) VALUES ('subject-extra-user', $id, '$username')");
                    }
                }
            }
        }
        else if(!isset($_POST['students'])){
            mysqli_query($conn, "DELETE FROM user_links WHERE link_usage='subject-extra-user' AND link_column_id=$id");
        }
        else {
            $toAdd = [];
            $toRemove = [];
            $reqUsers = $_POST['students'];
            $exiUsers = [];

            while($row = $res->fetch_assoc()){
                $username = $row['link_user'];
                $username = mysqli_real_escape_string($conn, $username);
                $resu = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username' AND user_type='STUDENT'");
                if($resu->num_rows > 0){
                    if(!in_array($username, $reqUsers))
                        array_push($toRemove, $username);
                }
                array_push($exiUsers, $username);
            }

            for($i=0; $i < count($reqUsers); $i++)
                if(!in_array($reqUsers[$i], $exiUsers)){
                    $username = mysqli_real_escape_string($conn, $reqUsers[$i]);
                    $resu = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username' AND user_type='STUDENT'");
                    if($resu->num_rows > 0)
                        array_push($toAdd, $username);
                }

            for($i=0; $i<count($toAdd); $i++){
                $username = mysqli_real_escape_string($conn, $toAdd[$i]);
                mysqli_query($conn, "INSERT INTO user_links (link_usage, link_column_id, link_user) VALUES ('subject-extra-user', $id, '$username')");
            }
            for($i=0; $i<count($toRemove); $i++){
                $username = mysqli_real_escape_string($conn, $toRemove[$i]);
                mysqli_query($conn, "DELETE FROM user_links WHERE link_usage='subject-extra-user' AND link_column_id=$id AND link_user='$username'");
            }
        }        

        header("Location: ../../../admin/subjects/");
        exit();
    }
}
else {
    include '../../../error.php';
    exit(); 
}