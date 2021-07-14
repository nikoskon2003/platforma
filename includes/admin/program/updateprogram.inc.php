<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

if(isset($_POST['submit']) && isset($_SESSION['type']))
{
    include_once '../../config.php';
    include '../../dbh.inc.php';
    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
    if($_SESSION['type'] !== 'ADMIN'){
        $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='program-editor' AND link_user='$username'");
        if($res->num_rows < 1){
            include '../../../error.php';
            exit();
        }
    }

    if(!isset($_POST["program-type"]) || !isset($_POST["student-url"]) || !isset($_POST["teacher-url"]) || !isset($_POST["program-text"])){
        include '../../../error.php';
        exit();
    }

    $res = mysqli_query($conn, "SELECT * FROM options WHERE option_name='program-text' LIMIT 1");
    if($res->num_rows < 1){
        mysqli_query($conn, "INSERT INTO options (option_name, option_value) VALUES ('program-students', '')");
        mysqli_query($conn, "INSERT INTO options (option_name, option_value) VALUES ('program-teachers', '')");
        mysqli_query($conn, "INSERT INTO options (option_name, option_value) VALUES ('program-text', '')");
        mysqli_query($conn, "INSERT INTO options (option_name, option_value) VALUES ('program-students-file', '')");
        mysqli_query($conn, "INSERT INTO options (option_name, option_value) VALUES ('program-teachers-file', '')");
    }

    include '../../enc.inc.php';
    include '../../extrasLoader.inc.php';

    $studentUrl = mysqli_real_escape_string($conn, $_POST['student-url']);
    $teacherUrl = mysqli_real_escape_string($conn, $_POST['teacher-url']);

    $text = $_POST['program-text'];
    $text = preg_replace('/(\r\n)|\r|\n/', '<br>', $text);
    $text = str_replace('\\n', '<br>', $text);
    $text = mysqli_real_escape_string($conn, encrypt($text));
    mysqli_query($conn, "UPDATE options SET option_value='$text' WHERE option_name='program-text'");

    if($_POST["program-type"] == 'file'){
        if(isset($_FILES['student-file'])){
            if($_FILES['student-file']["tmp_name"] != ''){
                $name = mysqli_real_escape_string($conn, $_FILES['student-file']["name"]);
                if(file_exists("../../../uploads/program/students")) unlink("../../../uploads/program/students");
                if(move_uploaded_file($_FILES['student-file']["tmp_name"], "../../../uploads/program/students")){
                    mysqli_query($conn, "UPDATE options SET option_value='file.php?id=program-students' WHERE option_name='program-students'");
                    mysqli_query($conn, "UPDATE options SET option_value='$name' WHERE option_name='program-students-file'");
                }
            }
        }

        if(isset($_FILES['teacher-file'])){
            if($_FILES['teacher-file']["tmp_name"] != ''){
                $name = mysqli_real_escape_string($conn, $_FILES['teacher-file']["name"]);
                if(file_exists("../../../uploads/program/teachers")) unlink("../../../uploads/program/teachers");
                if(move_uploaded_file($_FILES['teacher-file']["tmp_name"], "../../../uploads/program/teachers")){
                    mysqli_query($conn, "UPDATE options SET option_value='file.php?id=program-teachers' WHERE option_name='program-teachers'");
                    mysqli_query($conn, "UPDATE options SET option_value='$name' WHERE option_name='program-teachers-file'");
                }
            }
        }
    }
    else {
        if($studentUrl != 'prev-file' && trim($studentUrl) != '')
            mysqli_query($conn, "UPDATE options SET option_value='$studentUrl' WHERE option_name='program-students'");
        if($teacherUrl != 'prev-file' && trim($teacherUrl) != '')
            mysqli_query($conn, "UPDATE options SET option_value='$teacherUrl' WHERE option_name='program-teachers'");
    }

    header("Location: ../../../");
    connection_close();

    if(isset($_POST['notif']))
        if($_POST['notif'] == 'yes'){
            include '../../notifications/sendnotification.inc.php';
            sendNotification(null, 'Ενημέρωση Προγράμματος');
        }

    exit();
}
else
{
    include '../../../error.php';
    exit();
}