<?php
session_start();
if(isset($_POST['delete'])){
    if($_SESSION['type'] !== 'ADMIN'){
        include '../../../error.php';
        exit(); 
    }

    include '../../dbh.inc.php';
    include '../../enc.inc.php';
    $username = mysqli_real_escape_string($conn, $_POST['username']);

    $res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username'");
    if($res->num_rows > 0){
        mysqli_query($conn, "DELETE FROM users WHERE user_username='$username'");
        mysqli_query($conn, "DELETE FROM user_links WHERE link_user='$username'");
        
        $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_author='$username'");
        if($res->num_rows > 0)
        while($row = $res->fetch_assoc()){
            $postId = (int)mysqli_real_escape_string($conn, $row['post_id']);
            mysqli_query($conn, "DELETE FROM posts WHERE post_id=$postId");

            if($row['post_type'] == 'subject-post'){
                $subjId = (int)mysqli_real_escape_string($conn, $row['post_type_id']);
                $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_type='subject-post' AND post_type_id=$subjId AND post_visibility=0 ORDER BY post_date DESC LIMIT 1");
                if($res->num_rows < 1) mysqli_query($conn, "UPDATE subjects SET subject_latest_update='0000-00-00 00:00:00' WHERE subject_id=$subjId");
                else {
                    $time = $res->fetch_assoc()['post_date'];
                    mysqli_query($conn, "UPDATE subjects SET subject_latest_update='$time' WHERE subject_id=$subjId");
                }
            }
        }

        
        mysqli_query($conn, "DELETE FROM messages WHERE message_sender='$username' OR message_recipient='$username'");
        mysqli_query($conn, "DELETE FROM notif_subs WHERE subscription_username='$username'");

        mysqli_query($conn, "DELETE FROM files WHERE file_owner='$username'");
        deleteDir('../../../uploads/' . $username . '/');
		
		mysqli_query($conn, "DELETE FROM assignment_responses WHERE response_user='$username'");
        deleteDir('../../../assignments/' . $username . '/');

        /////TESTS/////

		////Yeeeeeeee..... A LOT of things should be deleted.... too lazy to implement now...
        

    }

    header("Location: ../../../admin/users/");
    exit();
}
else{
    include '../../../error.php';
    exit(); 
}

function deleteDir($dirPath) {
    if (!is_dir($dirPath)) return;
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') $dirPath .= '/';
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) deleteDir($file);
        else unlink($file);
    }
    rmdir($dirPath);
}