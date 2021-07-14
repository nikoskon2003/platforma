<?php
session_start();
include '../includes/config.php';

if(!isset($_SESSION['type']))
{
    if(isset($_COOKIE["autologin"]))
    {
        header("Location: ../includes/autologin.inc.php?r=messages");
        exit();
    }
    else
    {
        header("Location: ../login.php?r=messages");
        exit();
    }
}

include '../includes/dbh.inc.php';
include '../includes/enc.inc.php';

$usernames = array();
$names = array();
if($_SESSION['type'] == 'STUDENT'){
    if(isset($_SESSION['user_class'])){
        $class = (int)$_SESSION['user_class'];
        $res = mysqli_query($conn, "SELECT subject_id FROM subjects WHERE subject_class=$class");
        if($res->num_rows > 0)
        while($row = $res->fetch_assoc()){
            $subjId = (int)$row['subject_id'];
            $resu = mysqli_query($conn, "SELECT link_user FROM user_links WHERE link_usage='subject-teacher' AND link_used_id=$subjId");
            if($resu->num_rows > 0) 
            while($row = $resu->fetch_assoc()){
                $username = mysqli_real_escape_string($conn, $row['link_user']);
                if(!in_array($username, $usernames)){
                    $result = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username' LIMIT 1");
                    if($result->num_rows > 0){
                        $name = $result->fetch_assoc()['user_name'];
                        $name = htmlentities(decrypt($name));
                        array_push($usernames, $username);
                        array_push($names, $name);
                    }
                }
            }
        }
    }
}

$uname = mysqli_real_escape_string($conn, $_SESSION['user_username']);
$res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-student' AND link_user='$uname'");
if($res->num_rows > 0)
while($row = $res->fetch_assoc()){
    $subjId = (int)$row['link_used_id'];
    $resu = mysqli_query($conn, "SELECT link_user FROM user_links WHERE link_usage='subject-teacher' AND link_used_id=$subjId");
    if($resu->num_rows > 0)
    while($row = $resu->fetch_assoc()){
        $username = mysqli_real_escape_string($conn, $row['link_user']);
        if(!in_array($username, $usernames)){
            $result = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username' LIMIT 1");
            if($result->num_rows > 0){
                $name = $result->fetch_assoc()['user_name'];
                $name = htmlentities(decrypt($name));
                array_push($usernames, $username);
                array_push($names, $name);
            }
        }
    }
}

$res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='group-student' AND link_user='$uname'");
if($res->num_rows > 0)
while($row = $res->fetch_assoc()){
    $groupId = (int)$row['link_used_id'];
    $resu = mysqli_query($conn, "SELECT link_user FROM user_links WHERE link_usage='group-teacher' AND link_used_id=$groupId");
    if($resu->num_rows > 0)
    while($row = $resu->fetch_assoc()){
        $username = mysqli_real_escape_string($conn, $row['link_user']);
        if(!in_array($username, $usernames)){
            $result = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username' LIMIT 1");
            if($result->num_rows > 0){
                $name = $result->fetch_assoc()['user_name'];
                $name = htmlentities(decrypt($name));
                array_push($usernames, $username);
                array_push($names, $name);;
            }
        }
    }
}

include '../includes/extrasLoader.inc.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../favicon.ico" />
    <title><?= $siteName; ?> | Μηνύματα</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../styles/messages/index.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>

    <script src="../scripts/getmessages.js?v=<?= $pubFileVer; ?>"></script>
    <script src="../scripts/managerecent.js?v=<?= $pubFileVer; ?>"></script>
    <script src="../scripts/getonline.js?v=<?= $pubFileVer; ?>"></script>
</head>

<body>
<div id="container">
	<div id="header"><?= LoadTopNav(__FILE__); ?></div>
	<div id="body">

    <div class="desktop">
        <div class="title">Μηνύματα</div>
        <div class="people-cont">
            <a class="select-button-latest" onclick="openRecent();">Πρόσφατα</a><a class="select-button-all" onclick="openAll();">Όλα</a>
            <div class="people-container">
                <div id="recent-users"></div>
                <div id="all-users">
                <?php
                    if($_SESSION['type'] === 'STUDENT'){
                        for($i = 0; $i < count($names); $i++){
                            echo '<div id="all-user-' . rawurlencode($usernames[$i]) .'" class="user" onclick="window.location.href = \'./messages.php?u=' . rawurlencode($usernames[$i]) . '\';"><div class="user-online"><div class="online-dot online-status-' . rawurlencode($usernames[$i]) . '"></div></div><div class="user-details">
                            <div class="user-name">' . $names[$i] . '</div>
                            <div class="user-messages">&nbsp;</div>
                            </div><div class="user-date"></div></div>';
                        }
                    }
                    elseif($_SESSION['type'] === 'TEACHER'){
                        $checked = array();
                        $subids = array();

                        $others = false;

                        $uname = mysqli_real_escape_string($conn, $_SESSION['user_username']);
                        $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-teacher' AND link_user='$uname'");
                        if($res->num_rows > 0)
                        while($row = $res->fetch_assoc()){
                            $subjId = (int)$row['link_used_id'];
                            $resu = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$subjId LIMIT 1");
                            if($resu->num_rows > 0){
                                $row = $resu->fetch_assoc();
                                if(isset($row['subject_class'])){
                                    $classId = (int)$row['subject_class'];

                                    $resu = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$classId");
                                    if($resu->num_rows < 1) continue;
                                    $className = $resu->fetch_assoc()['class_name'];

                                    $resu = mysqli_query($conn, "SELECT * FROM users WHERE user_class=$classId");
                                    if($resu->num_rows > 0){
                                        echo '<div class="sub-title">' . $className . '</div>';
                                        while($row = $resu->fetch_assoc()){
                                            $username = mysqli_real_escape_string($conn, $row['user_username']);
                                            if(!in_array($username, $checked)){
                                                $name = $row['user_name'];
                                                $name = htmlentities(decrypt($name));
                                                array_push($checked, $username);
                                                echo '<div id="all-user-' . rawurlencode($username) .'" class="user" onclick="window.location.href = \'./messages.php?u=' . rawurlencode($username) . '\';"><div class="user-online"><div class="online-dot online-status-' . rawurlencode($username) . '"></div></div><div class="user-details">
                                                <div class="user-name">' . $name . '</div>
                                                <div class="user-messages">&nbsp;</div>
                                                </div><div class="user-date"></div></div>';
                                            }
                                        }
                                    }
                                }
                                array_push($subids, $subjId);
                            }
                        }

                        if(count($checked) < 1) $others = true;

                        for($i = 0; $i < count($subids); $i++){
                            $subjId = (int)$subids[$i];
                            $resu = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-student' AND link_used_id=$subjId");
                            if($resu->num_rows > 0){
                                while($row = $resu->fetch_assoc()){
                                    $username = mysqli_real_escape_string($conn, $row['link_user']);
                                    if(!in_array($username, $checked)){
                                        $result = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username' LIMIT 1");
                                        if($result->num_rows > 0){
                                            if(!$others){
                                                echo '<div class="sub-title">Άλλοι μαθητές</div>';
                                                $others = true;
                                            }
                                            $name = $result->fetch_assoc()['user_name'];
                                            $name = htmlentities(decrypt($name));

                                            array_push($checked, $username);
                                            echo '<div id="all-user-' . rawurlencode($username) .'" class="user" onclick="window.location.href = \'./messages.php?u=' . rawurlencode($username) . '\';"><div class="user-online"><div class="online-dot online-status-' . rawurlencode($username) . '"></div></div><div class="user-details">
                                            <div class="user-name">' . $name . '</div>
                                            <div class="user-messages">&nbsp;</div>
                                            </div><div class="user-date"></div></div>';
                                        }
                                    }
                                }
                            }
                        }

                        $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='group-teacher' AND link_user='$uname'");
                        if($res->num_rows > 0)
                        while($row = $res->fetch_assoc()){
                            $groupId = (int)$row['link_used_id'];
                            $resu = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='group-student' AND link_used_id=$groupId");
                            if($resu->num_rows > 0){
                                while($row = $resu->fetch_assoc()){
                                    $username = mysqli_real_escape_string($conn, $row['link_user']);
                                    if(!in_array($username, $checked)){
                                        $result = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username' LIMIT 1");
                                        if($result->num_rows > 0){
                                            if(!$others){
                                                echo '<div class="sub-title">Άλλοι μαθητές</div>';
                                                $others = true;
                                            }
                                            $name = $result->fetch_assoc()['user_name'];
                                            $name = htmlentities(decrypt($name));

                                            array_push($checked, $username);
                                            echo '<div id="all-user-' . rawurlencode($username) .'" class="user" onclick="window.location.href = \'./messages.php?u=' . rawurlencode($username) . '\';"><div class="user-online"><div class="online-dot online-status-' . rawurlencode($username) . '"></div></div><div class="user-details">
                                            <div class="user-name">' . $name . '</div>
                                            <div class="user-messages">&nbsp;</div>
                                            </div><div class="user-date"></div></div>';
                                        }
                                    }
                                }
                            }
                        }
                    }
                ?>
            </div>
        </div>
        </div>
        </div>

        <div class="mobile">
        <br>
        <div class="title">Μηνύματα</div>
        <div class="people-cont-mb">
            <a class="select-button-latest-mb" onclick="openRecent_mb();">Πρόσφατα</a><a class="select-button-all-mb" onclick="openAll_mb();">Όλα</a>
            <div class="people-container">
                <div id="recent-users-mb"></div>

                <div id="all-users-mb">
                <?php
                    include '../includes/dbh.inc.php';
                    if($_SESSION['type'] === 'STUDENT'){
                        for($i = 0; $i < count($names); $i++){
                            echo '<div id="all-user-mb-' . rawurlencode($usernames[$i]) .'" class="user" onclick="window.location.href = \'./messages.php?u=' . rawurlencode($usernames[$i]) . '\';"><div class="user-online"><div class="online-dot online-status-' . rawurlencode($usernames[$i]) . '"></div></div><div class="user-details">
                            <div class="user-name">' . $names[$i] . '</div>
                            <div class="user-messages">&nbsp;</div>
                            </div><div class="user-date"></div></div>';
                        }
                    }
                    elseif($_SESSION['type'] === 'TEACHER'){
                        $checked = array();
                        $subids = array();

                        $others = false;

                        $uname = mysqli_real_escape_string($conn, $_SESSION['user_username']);
                        $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-teacher' AND link_user='$uname'");
                        if($res->num_rows > 0)
                        while($row = $res->fetch_assoc()){
                            $subjId = (int)$row['link_used_id'];
                            $resu = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$subjId LIMIT 1");
                            if($resu->num_rows > 0){
                                $row = $resu->fetch_assoc();
                                if(isset($row['subject_class'])){
                                    $classId = (int)$row['subject_class'];

                                    $resu = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$classId");
                                    if($resu->num_rows < 1) continue;
                                    $className = $resu->fetch_assoc()['class_name'];

                                    $resu = mysqli_query($conn, "SELECT * FROM users WHERE user_class=$classId");
                                    if($resu->num_rows > 0){
                                        echo '<div class="sub-title">' . $className . '</div>';
                                        while($row = $resu->fetch_assoc()){
                                            $username = mysqli_real_escape_string($conn, $row['user_username']);
                                            if(!in_array($username, $checked)){
                                                $name = $row['user_name'];
                                                $name = htmlentities(decrypt($name));
                                                array_push($checked, $username);
                                                echo '<div id="all-user-mb-' . rawurlencode($username) .'" class="user" onclick="window.location.href = \'./messages.php?u=' . rawurlencode($username) . '\';"><div class="user-online"><div class="online-dot online-status-' . rawurlencode($username) . '"></div></div><div class="user-details">
                                                <div class="user-name">' . $name . '</div>
                                                <div class="user-messages">&nbsp;</div>
                                                </div><div class="user-date"></div></div>';
                                            }
                                        }
                                    }
                                }
                                array_push($subids, $subjId);
                            }
                        }

                        if(count($checked) < 1) $others = true;

                        for($i = 0; $i < count($subids); $i++){
                            $subjId = (int)$subids[$i];
                            $resu = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='subject-student' AND link_used_id=$subjId");
                            if($resu->num_rows > 0){
                                while($row = $resu->fetch_assoc()){
                                    $username = mysqli_real_escape_string($conn, $row['link_user']);
                                    if(!in_array($username, $checked)){
                                        $result = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username' LIMIT 1");
                                        if($result->num_rows > 0){
                                            if(!$others){
                                                echo '<div class="sub-title">Άλλοι μαθητές</div>';
                                                $others = true;
                                            }
                                            $name = $result->fetch_assoc()['user_name'];
                                            $name = htmlentities(decrypt($name));

                                            array_push($checked, $username);
                                            echo '<div id="all-user-mb-' . rawurlencode($username) .'" class="user" onclick="window.location.href = \'./messages.php?u=' . rawurlencode($username) . '\';"><div class="user-online"><div class="online-dot online-status-' . rawurlencode($username) . '"></div></div><div class="user-details">
                                            <div class="user-name">' . $name . '</div>
                                            <div class="user-messages">&nbsp;</div>
                                            </div><div class="user-date"></div></div>';
                                        }
                                    }
                                }
                            }
                        }

                        $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='group-teacher' AND link_user='$uname'");
                        if($res->num_rows > 0)
                        while($row = $res->fetch_assoc()){
                            $groupId = (int)$row['link_used_id'];
                            $resu = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='group-student' AND link_used_id=$groupId");
                            if($resu->num_rows > 0){
                                while($row = $resu->fetch_assoc()){
                                    $username = mysqli_real_escape_string($conn, $row['link_user']);
                                    if(!in_array($username, $checked)){
                                        $result = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$username' LIMIT 1");
                                        if($result->num_rows > 0){
                                            if(!$others){
                                                echo '<div class="sub-title">Άλλοι μαθητές</div>';
                                                $others = true;
                                            }
                                            $name = $result->fetch_assoc()['user_name'];
                                            $name = htmlentities(decrypt($name));
                                            
                                            array_push($checked, $username);
                                            echo '<div id="all-user-mb-' . rawurlencode($username) .'" class="user" onclick="window.location.href = \'./messages.php?u=' . rawurlencode($username) . '\';"><div class="user-online"><div class="online-dot online-status-' . rawurlencode($username) . '"></div></div><div class="user-details">
                                            <div class="user-name">' . $name . '</div>
                                            <div class="user-messages">&nbsp;</div>
                                            </div><div class="user-date"></div></div>';
                                        }
                                    }
                                }
                            }
                        }
                    }
                ?>
            </div>
        </div>
    </div>
    </div>

    <script>
        function openRecent(){
            document.getElementById('recent-users').style.display = "block";
            document.getElementById('all-users').style.display = "none";

            document.getElementsByClassName('select-button-latest')[0].style.backgroundColor = "#007bb4";
            document.getElementsByClassName('select-button-all')[0].style.backgroundColor = "#2ebafc";
        }
        function openAll(){
            document.getElementById('recent-users').style.display = "none";
            document.getElementById('all-users').style.display = "block";

            document.getElementsByClassName('select-button-latest')[0].style.backgroundColor  = "#2ebafc";
            document.getElementsByClassName('select-button-all')[0].style.backgroundColor = "#007bb4";
        }

        function openRecent_mb(){
            document.getElementById('recent-users-mb').style.display = "block";
            document.getElementById('all-users-mb').style.display = "none";

            document.getElementsByClassName('select-button-latest-mb')[0].style.backgroundColor = "#007bb4";
            document.getElementsByClassName('select-button-all-mb')[0].style.backgroundColor = "#2ebafc";
        }
        function openAll_mb(){
            document.getElementById('recent-users-mb').style.display = "none";
            document.getElementById('all-users-mb').style.display = "block";

            document.getElementsByClassName('select-button-latest-mb')[0].style.backgroundColor  = "#2ebafc";
            document.getElementsByClassName('select-button-all-mb')[0].style.backgroundColor = "#007bb4";
        }
        openRecent();
        openRecent_mb();
    </script>
	</div>
	<div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>