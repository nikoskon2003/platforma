<?php session_start();

if(!isset($_SESSION['type'])){
    include '../../error.php';
    exit();
}
elseif($_SESSION['type'] !== 'ADMIN'){
    include '../../error.php';
    exit();
}

include_once '../../includes/config.php';
include_once '../../includes/extrasLoader.inc.php';
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../favicon.ico" />
    <title><?= $siteName ?> | Μαθήματα</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/subjects/index.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
</head>

<body>
<div id="container">
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">
        <div class="desktop">
            <p class="title">Μαθήματα</p>
            <div class="new-subject">
                <a href="./newsubject.php">Νέο Μάθημα<img src="../../resources/new.png"/></a>
            </div>
            <?php 
                include '../../includes/dbh.inc.php';
                include '../../includes/enc.inc.php';
                $res = mysqli_query($conn, "SELECT * FROM classes");
                if($res->num_rows > 0)
                    while($row = $res->fetch_assoc()){
                        $cid = $row['class_id'];
                        $cname = $row['class_name'];
                        
                        $result = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_class=$cid");
                        if($result->num_rows > 0){
                            echo '<p class="subtitle">' . $cname . '</p><div class="subjects-holder">';
                            while($row = $result->fetch_assoc()){
                                $subjId = $row['subject_id'];
                                $subjName = decrypt($row['subject_name']);
                                $subjLU = $row['subject_latest_update'];

                                $parts = explode(' ', $subjLU);
                                if($parts[0] == "0000-00-00") $subjLU = "&lt;Ποτέ&gt;";
                                else {
                                    $t = explode('-',$parts[0]);
                                    $subjLU = $t[2] . '/' . $t[1] . '/' . $t[0];
                                }

                                echo "<a href='./subject.php?s=$subjId' class='subject'>
                                <p class='subject-name'>$subjName</p>
                                <p class='subject-latest-update'>$subjLU</p>
                            </a>";
                            }
                            echo '</div>';
                        }
                    }
                $res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_class IS NULL");
                if($res->num_rows > 0){
                    echo '<p class="subtitle">Άλλα μαθήματα:</p><div class="subjects-holder">';
                    while($row = $res->fetch_assoc()){
                        $subjId = $row['subject_id'];
                        $subjName = decrypt($row['subject_name']);
                        $subjLU = $row['subject_latest_update'];

                        $parts = explode(' ', $subjLU);
                        if($parts[0] == "0000-00-00") $subjLU = "&lt;Ποτέ&gt;";
                        else {
                            $t = explode('-',$parts[0]);
                            $subjLU = $t[2] . '/' . $t[1] . '/' . $t[0];
                        }

                        echo "<a href='./subject.php?s=$subjId' class='subject'>
                        <p class='subject-name'>$subjName</p>
                        <p class='subject-latest-update'>$subjLU</p>
                    </a>";
                    }
                    echo '</div>';
                }
            ?>
        </div>



        <div class="mobile">
            <br><p class="title">Μαθήματα</p>
            <div class="new-subject">
                <a href="./newsubject.php">Νέο Μάθημα<img src="../../resources/new.png"/></a>
            </div>
            <?php 
                include '../../includes/dbh.inc.php';
                $res = mysqli_query($conn, "SELECT * FROM classes");
                if($res->num_rows > 0)
                    while($row = $res->fetch_assoc()){
                        $cid = $row['class_id'];
                        $cname = $row['class_name'];
                        
                        $result = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_class=$cid");
                        if($result->num_rows > 0){
                            echo '<p class="subtitle">' . $cname . '</p><div class="subjects-holder">';
                            while($row = $result->fetch_assoc()){
                                $subjId = $row['subject_id'];
                                $subjName = decrypt($row['subject_name']);
                                $subjLU = $row['subject_latest_update'];

                                $parts = explode(' ', $subjLU);
                                if($parts[0] == "0000-00-00") $subjLU = "&lt;Ποτέ&gt;";
                                else {
                                    $t = explode('-',$parts[0]);
                                    $subjLU = $t[2] . '/' . $t[1] . '/' . $t[0];
                                }

                                echo "<a href='./subject.php?s=$subjId' class='subject-mb'>
                                <p class='subject-name-mb'>$subjName</p>
                                <p class='subject-latest-update'>$subjLU</p>
                            </a>";
                            }
                            echo '</div>';
                        }
                    }
                $res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_class IS NULL");
                if($res->num_rows > 0){
                    echo '<p class="subtitle">Άλλα μαθήματα:</p><div class="subjects-holder">';
                    while($row = $res->fetch_assoc()){
                        $subjId = $row['subject_id'];
                        $subjName = decrypt($row['subject_name']);
                        $subjLU = $row['subject_latest_update'];

                        $parts = explode(' ', $subjLU);
                        if($parts[0] == "0000-00-00") $subjLU = "&lt;Ποτέ&gt;";
                        else {
                            $t = explode('-',$parts[0]);
                            $subjLU = $t[2] . '/' . $t[1] . '/' . $t[0];
                        }

                        echo "<a href='./subject.php?s=$subjId' class='subject-mb'>
                        <p class='subject-name-mb'>$subjName</p>
                        <p class='subject-latest-update'>$subjLU</p>
                    </a>";
                    }
                    echo '</div>';
                }
            ?>
        </div>

    </div>
    <div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>