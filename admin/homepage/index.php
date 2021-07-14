<?php session_start();

if(!isset($_SESSION['type'])){
    include '../../error.php';
    exit();
}

include_once '../../includes/config.php';
include '../../includes/dbh.inc.php';
$username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
if($_SESSION['type'] !== 'ADMIN'){
    $res = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='homepage-author' AND link_user='$username'");
    if($res->num_rows < 1){
        include '../../error.php';
    exit();
    }
}

include_once '../../includes/extrasLoader.inc.php';
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../favicon.ico" />
    <title><?= $siteName; ?> | Ανακοινώσεις Αρχικής Σελίδας</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/homepage/index.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
    <?= LoadMathJax(); ?>

    <link rel="stylesheet" href="../../resources/img-viewer/lib/view-bigimg.css?v=<?= $pubFileVer; ?>">
    <script src="../../resources/img-viewer/lib/view-bigimg.js?v=<?= $pubFileVer; ?>"></script>

    <script src="../../scripts/getmessages.js?v=<?= $pubFileVer; ?>"></script>
</head>

<body>
<div id="container">
    <script>let viewer = new ViewBigimg();</script>
    
    <div id="header"><?= LoadTopNav(__FILE__); ?></div>
    <div id="body">
        <div class="desktop">
            <p class="title">Ανακοινώσεις Αρχικής Σελίδας</p>

            <?php if($_SESSION['type'] === 'ADMIN'): ?>
            <div class="authors">
                <a href="./authors.php">Επεξεργασία Συντακτών<img src="../../resources/edit-icon.png"/></a>
            </div>
            <?php endif; ?>

            <div class="posts-container">
                <a href="./newpost.php" class="new-post-button">Νέα Ανακοινώση<img src="../../resources/new.png"/></a>
                
                <div class="posts-content">
                    
                        <?php
                        include_once '../../includes/enc.inc.php';

                        $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_usage='homepage' ORDER BY post_date DESC");
                        if($res->num_rows < 1) echo '<p style="text-align:center;width:100%">Δεν υπάρχουν ανακοινώσεις</p>';
                        else while($row = $res->fetch_assoc())
                        {

                            $id = (int)$row["post_id"];

                            $title = decrypt($row['post_title']);
                            $title = htmlentities($title);

                            $text = decrypt($row['post_text']);
                            $text = str_replace('<br>', " \\n ", $text);
                            $text = htmlspecialchars($text);
                            $text = formatText($text);
                            $text = str_replace('\\n', '<br>', $text);

                            $date = preg_split('/ /', $row["post_date"]);
                            $date = preg_split('/-/', $date[0])[2] . '/' . preg_split('/-/', $date[0])[1] . '/' . preg_split('/-/', $date[0])[0] . ' ' . $date[1];
                            $date = str_replace('00:00:00', '', $date);

                            $author = mysqli_real_escape_string($conn, $row['post_author']);
                            $uname = $author;
                            if($author !== 'admin')
                            {
                                $sql = "SELECT user_name FROM users WHERE user_username='$author'";
                                $result = mysqli_query($conn, $sql);
                                if($result->num_rows > 0)
                                    $author = decrypt($result->fetch_assoc()['user_name']);
                            }
                            else $author = 'Administrator';

                            $visibility = $row['post_visibility'];
                            $col = 'hsl(120, 80%, 80%)';
                            if($visibility == 0) $col = '#FFC4C4';
                            elseif($visibility == 2) $col = 'yellow';

                            $files = explode(',', $row['post_files']);

                            $outfiles = '';
                            if(!empty($files) && $files[0] != '')
                            {
                                for($i = 0; $i < sizeof($files); $i++)
                                {
                                    if(empty($files[$i])) continue;
                                    $file = mysqli_real_escape_string($conn, $files[$i]);
                                    $uppath = '../../file.php?id=' . $file;
                                    $filename = '';

                                    $result = mysqli_query($conn, "SELECT * FROM files WHERE file_uid='$file' AND file_owner='$uname'");
                                    if($result->num_rows < 1) continue;
                                    else $filename = $result->fetch_assoc()['file_name'];

                                    $imgs = array('gif', 'jpg', 'jpeg', 'png', 'webp', 'svg', 'bmp');
                                    
                                    $safeName = htmlentities($filename);

                                    $ext = explode('.', $filename);
                                    $ext = end($ext);
                                    $ext = mb_strtolower($ext);

                                    if(in_array($ext, $imgs) && false)
                                    {
                                        //<a class="post-file" href="./file.php?id=a" target="_blank" title="filename"><img src="resources/icons/empty.png"/><p>filename</p></a>
                                        $outfiles .= '
                                            <a class="post-file image" title="' . $safeName . '" id="img' . $file . $id . '">
                                                <img src="' . $uppath . '" id="src' . $file . $id . '">
                                                <p>' . $safeName. '</p>
                                                <script>
                                                    document.getElementById("img' . $file . $id . '").onclick = function (e) {
                                                        viewer.show(document.getElementById("src' . $file  . $id . '").src);
                                                        document.getElementById("header").style.display = "none";
                                                    }
                                                </script>
                                            </a>
                                        ';
                                    }
                                    else {
                                        $fileIcon = iconFromExtension($filename);
                                        $outfiles .= '<a class="post-file" href="../../file.php?id=' . $file . '" target="_blank" title="' . $safeName . '"><img src="../../resources/icons/' . $fileIcon . '.png"/><p>' . $safeName . '</p></a>';
                                    }
                                }
                                echo '<div class="post" style="background-color: ' . $col .'">';
                                        if($_SESSION['type'] == 'ADMIN' || $username == $uname) echo '<a class="post-edit" href="./editpost.php?id=' . $id . '">Επεξεργασία<img src="../../resources/edit-icon.png"/></a>';
                                        echo '<div class="post-title">' . $title . '</div>
                                        <div class="post-date">' . $date . '</div>
                                        <div class="post-user">' . $author . '</div>
                                        <div class="post-line"></div>
                                        <div class="post-text">' . $text . '</div>
                                        <div class="post-line"></div>
                                        <div class="post-file-container">' . $outfiles . '</div>
                                    </div>';
                            }
                            else
                            {
                                echo '<div class="post" style="background-color: ' . $col .'">';
                                        if($_SESSION['type'] == 'ADMIN' || $username == $uname) echo '<a class="post-edit" href="./editpost.php?id=' . $id . '">Επεξεργασία<img src="../../resources/edit-icon.png"/></a>';
                                        echo '<div class="post-title">' . $title . '</div>
                                        <div class="post-date">' . $date . '</div>
                                        <div class="post-user">' . $author . '</div>
                                        <div class="post-line"></div>
                                        <div class="post-text">' . $text . '</div>
                                    </div>';
                            }
                        }

                        ?>
                </div>
            </div>
            
        </div>



        <div class="mobile">
            <br><p class="title">Ανακοινώσεις Αρχικής Σελίδας</p>

            <?php if($_SESSION['type'] === 'ADMIN'): ?>
            <div class="authors">
                <a href="./authors.php">Επεξεργασία Συντακτών<img src="../../resources/edit-icon.png"/></a>
            </div>
            <?php endif; ?>

            <div class="posts-container-mb">
                <a href="./newpost.php" class="new-post-button">Νέα Ανακοινώση<img src="../../resources/new.png"/></a>
                
                <div class="posts-content">
            
                    <?php
                    include_once '../../includes/enc.inc.php';

                    $res = mysqli_query($conn, "SELECT * FROM posts WHERE post_usage='homepage' ORDER BY post_date DESC");
                    if($res->num_rows < 1) echo '<p style="text-align:center;width:100%">Δεν υπάρχουν ανακοινώσεις</p>';
                    else while($row = $res->fetch_assoc())
                    {

                        $id = (int)$row["post_id"];

                        $title = decrypt($row['post_title']);
                        $title = htmlentities($title);

                        $text = decrypt($row['post_text']);
                        $text = str_replace('<br>', " \\n ", $text);
                        $text = htmlspecialchars($text);
                        $text = formatText($text);
                        $text = str_replace('\\n', '<br>', $text);

                        $date = preg_split('/ /', $row["post_date"]);
                        $date = preg_split('/-/', $date[0])[2] . '/' . preg_split('/-/', $date[0])[1] . '/' . preg_split('/-/', $date[0])[0] . ' ' . $date[1];
                        $date = str_replace('00:00:00', '', $date);

                        $author = mysqli_real_escape_string($conn, $row['post_author']);
                        $uname = $author;
                        if($author !== 'admin')
                        {
                            $sql = "SELECT user_name FROM users WHERE user_username='$author'";
                            $result = mysqli_query($conn, $sql);
                            if($result->num_rows > 0)
                                $author = decrypt($result->fetch_assoc()['user_name']);
                        }
                        else $author = 'Administrator';

                        $visibility = $row['post_visibility'];
                        $col = 'hsl(120, 80%, 80%)';
                        if($visibility == 0) $col = '#FFC4C4';
                        elseif($visibility == 2) $col = 'yellow';

                        $files = explode(',', $row['post_files']);

                        $outfiles = '';
                        if(!empty($files) && $files[0] != '')
                        {
                            for($i = 0; $i < sizeof($files); $i++)
                            {
                                if(empty($files[$i])) continue;
                                $file = mysqli_real_escape_string($conn, $files[$i]);
                                $uppath = '../../file.php?id=' . $file;
                                $filename = '';

                                $result = mysqli_query($conn, "SELECT * FROM files WHERE file_uid='$file' AND file_owner='$uname'");
                                if($result->num_rows < 1) continue;
                                else $filename = $result->fetch_assoc()['file_name'];

                                $imgs = array('gif', 'jpg', 'jpeg', 'png', 'webp', 'svg', 'bmp');
                                
                                $safeName = htmlentities($filename);

                                $ext = explode('.', $filename);
                                $ext = end($ext);
                                $ext = mb_strtolower($ext);

                                if(in_array($ext, $imgs) && false)
                                {
                                    //<a class="post-file" href="./file.php?id=a" target="_blank" title="filename"><img src="resources/icons/empty.png"/><p>filename</p></a>
                                    $outfiles .= '
                                        <a class="post-file image" title="' . $safeName . '" id="mbimg' . $file . $id . '">
                                            <img src="' . $uppath . '" id="mbsrc' . $file . $id . '">
                                            <p>' . $safeName. '</p>
                                            <script>
                                                document.getElementById("mbimg' . $file . $id . '").onclick = function (e) {
                                                    viewer.show(document.getElementById("mbsrc' . $file  . $id . '").src);
                                                    document.getElementById("header").style.display = "none";
                                                }
                                            </script>
                                        </a>
                                    ';
                                }
                                else {
                                    $fileIcon = iconFromExtension($filename);
                                    $outfiles .= '<a class="post-file" href="../../file.php?id=' . $file . '" target="_blank" title="' . $safeName . '"><img src="../../resources/icons/' . $fileIcon . '.png"/><p>' . $safeName . '</p></a>';
                                }
                            }
                            echo '<div class="post" style="background-color: ' . $col .'">';
                                    if($_SESSION['type'] == 'ADMIN' || $username == $uname) echo '<a class="post-edit" href="./editpost.php?id=' . $id . '">Επεξεργασία<img src="../../resources/edit-icon.png"/></a>';
                                    echo '<div class="post-title">' . $title . '</div>
                                    <div class="post-date">' . $date . '</div>
                                    <div class="post-user">' . $author . '</div>
                                    <div class="post-line"></div>
                                    <div class="post-text">' . $text . '</div>
                                    <div class="post-line"></div>
                                    <div class="post-file-container">' . $outfiles . '</div>
                                </div>';
                        }
                        else
                        {
                            echo '<div class="post" style="background-color: ' . $col .'">';
                                    if($_SESSION['type'] == 'ADMIN' || $username == $uname) echo '<a class="post-edit" href="./editpost.php?id=' . $id . '">Επεξεργασία<img src="../../resources/edit-icon.png"/></a>';
                                    echo '<div class="post-title">' . $title . '</div>
                                    <div class="post-date">' . $date . '</div>
                                    <div class="post-user">' . $author . '</div>
                                    <div class="post-line"></div>
                                    <div class="post-text">' . $text . '</div>
                                </div>';
                        }
                    }

                    ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        var iv = document.getElementsByClassName("iv-close");
        for(var i = 0; i < iv.length; i++) 
            iv[i].onclick = function (e) {
                document.getElementById("header").style.display = "inline";
            }
    </script>
    <div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>