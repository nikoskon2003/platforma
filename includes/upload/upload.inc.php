<?php
session_start();

if(isset($_SESSION['user_username']) && isset($_FILES['f'])){

    $files = [];

    if(is_array($_FILES['f']['name']))
        for($i = 0; $i < count($_FILES['f']['name']); $i++){
            $n = uploadFile($_FILES['f']['tmp_name'][$i], $_FILES['f']['name'][$i], $_SESSION['user_username']);
            if(!is_null($n)) $files[] = $n;
        }
    elseif(is_string($_FILES['f']['name'])){
        $n = uploadFile($_FILES['f']['tmp_name'], $_FILES['f']['name'], $_SESSION['user_username']);
        if(!is_null($n)) $files[] = $n;
    }

    echo json_encode($files, JSON_UNESCAPED_UNICODE);
}
else {
    echo 'error';
    exit();
}

function uploadFile($tmpPath, $name, $user){
    include '../dbh.inc.php';
    include_once '../enc.inc.php';
    $rnd = mysqli_real_escape_string($conn, randomString(random_int(30, 40)));
    while(true){
        if(mysqli_query($conn, "SELECT * FROM files WHERE file_uid='$rnd'")->num_rows > 0)
            $rnd = mysqli_real_escape_string($conn, randomString(random_int(30, 50)));
        else break;
    }

    $username = mysqli_real_escape_string($conn, rawurlencode($user));
    $fileName = mysqli_real_escape_string($conn, $name);

    $upPath = '../../uploads/users/' . $user;

    if (!file_exists($upPath)) mkdir($upPath, 0777, true);

    $filePath = $upPath . '/' . $rnd;
    date_default_timezone_set('Europe/Athens');
    $date = date('Y-m-d H:i:s', time());

    if(move_uploaded_file($tmpPath, $filePath)){
        $b = mysqli_real_escape_string($conn, human_filesize(filesize($filePath)));
        $r = mysqli_query($conn, "INSERT INTO files (file_uid, file_name, file_owner, file_date, file_size, file_fav) VALUES ('$rnd', '$fileName', '$username', '$date', '$b', 0)");
        if($r) return ['name' => base64_encode($fileName), 'uid' => $rnd, 'date' => base64_encode($date), 'size' => $b, 'fav' => 0];
    }

    return null;
}

function human_filesize($bytes, $decimals = 2) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor] . (($factor != 0) ? 'B' : '');
}