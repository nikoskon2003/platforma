<?php
session_start();
if(isset($_SESSION['user_username'])){
    include '../dbh.inc.php';

    $files = [];
    $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);

    if(isset($_POST['limit'])){
        if($_POST['limit'] == 'fav'){
            $res = mysqli_query($conn, "SELECT * FROM files WHERE file_fav=1 AND file_owner='$username' ORDER BY file_id DESC");
            while($row = $res->fetch_assoc()){
                $name = base64_encode($row['file_name']);
                $uid = $row['file_uid'];
                $date = base64_encode($row['file_date']);
                $size = $row['file_size'];
                $fav = (int)$row['file_fav'];

                $files[] = ['name' => $name, 'uid' => $uid, 'date' => $date, 'size' => $size, 'fav' => $fav];
            }
        }
        else {
            $req = explode('-', $_POST['limit']);
            if(count($req) != 2){
                include '../../error.php';
                exit();
            }
            elseif(is_nan($req[0]) || is_nan($req[1])){
                include '../../error.php';
                exit();
            }

            $year = (int)mysqli_real_escape_string($conn, $req[0]);
            $month = (int)mysqli_real_escape_string($conn, $req[1]);

            $res = mysqli_query($conn, "SELECT * FROM files WHERE YEAR(file_date)='$year' AND MONTH(file_date)='$month' AND file_owner='$username' ORDER BY file_id DESC");
            while($row = $res->fetch_assoc()){
                $name = base64_encode($row['file_name']);
                $uid = $row['file_uid'];
                $date = base64_encode($row['file_date']);
                $size = $row['file_size'];
                $fav = (int)$row['file_fav'];

                $files[] = ['name' => $name, 'uid' => $uid, 'date' => $date, 'size' => $size, 'fav' => $fav];
            }
        }
    }
    else {
        $res = mysqli_query($conn, "SELECT * FROM files WHERE file_owner='$username' ORDER BY file_id DESC");
        while($row = $res->fetch_assoc()){
            $name = base64_encode($row['file_name']);
            $uid = $row['file_uid'];
            $date = base64_encode($row['file_date']);
            $size = $row['file_size'];
            $fav = (int)$row['file_fav'];

            $files[] = ['name' => $name, 'uid' => $uid, 'date' => $date, 'size' => $size, 'fav' => $fav];
        }
    }

    echo json_encode($files, JSON_UNESCAPED_UNICODE);
}
else {
    include '../../error.php';
    exit();
}