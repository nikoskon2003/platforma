<?php
function LoadBackground($callFilePath)
{
    $rootPath = realpath(str_replace('\\', '/', dirname(__FILE__)) . "/../");
    $callFilePath = str_replace('\\', '/', $callFilePath);
    $callFilePath = substr($callFilePath, strlen($rootPath) + 1);
    $pathToRoot = '';
    for($i = 0; $i <= substr_count($callFilePath, '/') - 1; $i++) $pathToRoot .= '../';
	
    return '<style>body{background-image: url("' . $pathToRoot . 'resources/bg.jpg");background-repeat: repeat;}</style><!-- Made by Nikolaos Konstantinou -->';
}

function LoadFooter(){
    include 'config.php';

    $ft = htmlentities($footer);
    $ft = str_replace('**b**', '<br>', $ft);
    $ft = str_replace('**y**', date('Y', time()), $ft);
    $ft = str_replace('**s**', '&nbsp;', $ft);
    $ft = str_replace('**m**', '<a href="mailto:' . htmlentities($contactEmail) . '">' . htmlentities($contactEmail) . '</a>', $ft);

    $f = "<div class='bottom'><p>" . $ft . "</p></div>";
    return $f;
}

function LoadTopNav($callFilePath)
{
    $rootPath = realpath(str_replace('\\', '/', dirname(__FILE__)) . "/../");
    $callFilePath = str_replace('\\', '/', $callFilePath);
    $callFilePath = substr($callFilePath, strlen($rootPath) + 1);
    $pathToRoot = './';
    for($i = 0; $i <= substr_count($callFilePath, '/') - 1; $i++) $pathToRoot .= '../';

    //echo $_SERVER['REQUEST_URI'];
    return LoadDesktopTopNav($callFilePath, $pathToRoot) . LoadMobileTopNav($callFilePath, $pathToRoot);
}

function LoadDesktopTopNav($callFilePath, $pathToRoot)
{
    include 'dbh.inc.php';

    $out = '<img src="' . $pathToRoot . 'icon.png" alt="Icon" id="top-icon"><!-- Made by Nikolaos Konstantinou -->';

    if($callFilePath == 'index.php') $out .= '<a class="active" href=".">Αρχική</a>';
    else $out .= '<a href="' . $pathToRoot . '">Αρχική</a>';

    if(isset($_SESSION['type']))
    {
        $out .= '<a class="logout-button" href="' . $pathToRoot .'includes/logout.inc.php">Αποσύνδεση</a>';

        if($_SESSION['type'] != 'ADMIN')
            $out .= '<a href="'. $pathToRoot .'messages/" id="message-button"><img src="' . $pathToRoot .'resources/message.png"><div class="pulseObject"></div><div class="message-notification" onclick="document.getElementsByClassName(\'message-notification\')[0].style.display=\'none\';return false;"><div class="notification-x"></div><p class="message-notification-text"></p></div></a>';
        
        if($_SESSION['type'] == 'STUDENT')
        {
            if($callFilePath == 'class/index.php') $out .= '<a href="" class="active">Τάξη</a>';
            else $out .= '<a href="' . $pathToRoot . 'class/">Τάξη</a>';
            
            if($callFilePath == 'groups/index.php') $out .= '<a href="' . $pathToRoot .'groups/" class="active">Όμιλοι</a>';
            else
            {
                $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
                $result = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='group-student' AND link_user='$username'");
                if($result->num_rows > 0)
                    $out .= '<a href="' . $pathToRoot .'groups/">Όμιλοι</a>';
            }

        } 
        elseif($_SESSION['type'] == 'ADMIN')
        {
            if($callFilePath == 'admin/users/index.php') $out .= '<a href="' . $pathToRoot .'admin/users/" class="active">Χρήστες</a>';
            else $out .= '<a href="' . $pathToRoot .'admin/users/">Χρήστες</a>';

            if($callFilePath == 'admin/classes/index.php') $out .= '<a href="' . $pathToRoot .'admin/classes/" class="active">Τάξεις</a>';
            else $out .= '<a href="' . $pathToRoot .'admin/classes/">Τάξεις</a>';

            if($callFilePath == 'admin/subjects/index.php') $out .= '<a href="' . $pathToRoot .'admin/subjects/" class="active">Μαθήματα</a>';
            else $out .= '<a href="' . $pathToRoot .'admin/subjects/">Μαθήματα</a>';

			//hahahhahahahaha never made this lmaoooooooo
            /*if($callFilePath == 'groups/index.php') $out .= '<a href="' . $pathToRoot .'groups/" class="active">Όμιλοι</a>';
            else $out .= '<a href="' . $pathToRoot .'groups">Όμιλοι</a>';*/
        }
		elseif($_SESSION['type'] == 'TEACHER')
		{
			if($callFilePath == 'class/index.php') $out .= '<a href="' . $pathToRoot .'lessons/" class="active">Τάξεις</a>';
            else $out .= '<a href="' . $pathToRoot .'class/">Τάξεις</a>';

            if($callFilePath == 'groups/index.php') $out .= '<a href="' . $pathToRoot .'groups/" class="active">Όμιλοι</a>';
            else 
            {
                $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
                $result = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='group-teacher' AND link_user='$username'");
                if($result->num_rows > 0)
                    $out .='<a href="' . $pathToRoot .'groups/">Όμιλοι</a>';
            }
        }
    }
    else if($callFilePath != 'login.php') $out .= '<a class="login-button" href="' . $pathToRoot .'login.php">Σύνδεση</a>';
    
    return '<div class="desktop topnav">' . $out . '</div>';
}
function LoadMobileTopNav($callFilePath, $pathToRoot)
{
    include 'dbh.inc.php';

    $top = '<img src="' . $pathToRoot . 'icon.png" alt="Icon" id="mobile-top-icon">';
    $inside = '';

    if(isset($_SESSION['type']))
    {
        $inside .= '<a href="javascript:void(0)" id="mobile-menu-close-btn" onclick="closeNav()">&times;</a>
            <a href="' . $pathToRoot .'">Αρχική</a>';

        if($_SESSION['type'] == 'STUDENT')
        {
            $inside .= '<a href="' . $pathToRoot .'class/">Τάξη</a>';

            $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
            $result = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='group-student' AND link_user='$username'");
            if($result->num_rows > 0)
                $inside .= '<a href="' . $pathToRoot .'groups/">Όμιλοι</a>';
        }
        elseif($_SESSION['type'] == 'TEACHER')
        {
            $inside .= '<a href="' . $pathToRoot .'class/">Τάξεις</a>';

            $username = mysqli_real_escape_string($conn, $_SESSION['user_username']);
            $result = mysqli_query($conn, "SELECT * FROM user_links WHERE link_usage='group-teacher' AND link_user='$username'");
            if($result->num_rows > 0)
                $inside .= '<a href="' . $pathToRoot .'groups/">Όμιλοι</a>';
        }
        elseif($_SESSION['type'] == 'ADMIN')
        {
            $inside .= '<a href="' . $pathToRoot .'admin/classes/">Τάξεις</a>';
            $inside .= '<a href="' . $pathToRoot .'admin/users/">Χρήστες</a>';
            $inside .= '<a href="' . $pathToRoot .'admin/subjects/">Μαθήματα</a>';
            //$inside .= '<a href="' . $pathToRoot . 'groups/">Όμιλοι</a>'; //yeeeeeeeeeeeeee
        }
            
        $inside .= '<a href="' . $pathToRoot .'includes/logout.inc.php" id="mobile-logout" style="color:rgb(219, 61, 61)">Αποσύνδεση</a>';
        
        if($_SESSION['type'] != 'ADMIN') {
			$inside .= '<a href="'. $pathToRoot .'messages/" id="mobile-message-button"><img src="' . $pathToRoot .'resources/white-message.png"><div class="pulseObject"></div></a>';
		}

        $top .= '<span id="mobile-menu-open" onclick="openNav()">&#9776;</span>';
        $top .= '<script>function openNav(){document.getElementById("mobile-nav-menu").style.width="75%";}function closeNav(){document.getElementById("mobile-nav-menu").style.width="0";}</script>';    
    }
    else
    {
        if($callFilePath != 'login.php')
            $top .= '<a class="mobile-login-button" href="' . $pathToRoot .'login.php">Σύνδεση</a>';
        else  $top .= '<a class="mobile-login-button" href="' . $pathToRoot . '">Αρχική</a>';
    }
    
    return '<div class="mobile topnav">' . $top . '</div><div class="mobile" id="mobile-nav-menu">' . $inside . '</div>';
}

function LoadMathJax(){
    return '<!--<script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>-->
        <script>MathJax = {loader: {load: [\'input/asciimath\', \'output/chtml\', \'ui/menu\']},asciimath: {delimiters: [[\'[math]\',\'[/math]\']]}};</script>
        <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script><!-- Made by Nikolaos Konstantinou -->';
}

function formatText($str){
    if (preg_match_all("#(^|\s|\()((http(s?)://)|(www\.))(\w+[^\s\)\<]+)#i", $str, $matches)){
        for ($i = 0; $i < count($matches['0']); $i++){
            $period = '';
            if (preg_match("|\.$|", $matches['6'][$i])){
                $period = '.';
                $matches['6'][$i] = substr($matches['6'][$i], 0, -1);
            }
            $str = str_replace($matches['0'][$i],
                $matches['1'][$i].'<a href="http'.
                $matches['4'][$i].'://'.
                $matches['5'][$i].
                $matches['6'][$i].'" target=\"_blank\">http'.
                $matches['4'][$i].'://'.
                $matches['5'][$i].
                $matches['6'][$i].'</a>'.
                $period, $str);
        }
    }
    return $str;
}

function iconFromExtension($filename){
    $ext = explode('.', $filename);
    $ext = end($ext);
    $ext = mb_strtolower($ext);
    
    $imageExt = ["ase","art","bmp","blp","cd5","cit","cpt","cr2","cut","dds","dib","djvu","egt","exif","gif","gpl","grf","icns","ico","iff","jng","jpeg","jpg","jfif","jp2","jps","lbm","max","miff","mng","msp","nitf","ota","pbm","pc1","pc2","pc3","pcf","pcx","pdn","pgm","PI1","PI2","PI3","pict","pct","pnm","pns","ppm","psb","psd","pdd","psp","px","pxm","pxr","qfx","raw","rle","sct","sgi","rgb","int","bw","tga","tiff","tif","vtf","xbm","xcf","xpm","3dv","amf","ai","awg","cgm","cdr","cmx","dxf","e2d","egt","eps","fs","gbr","odg","svg","stl","vrml","x3d","sxd","v2d","vnd","wmf","emf","art","xar","png","webp","jxr","hdp","wdp","cur","ecw","iff","lbm","liff","nrrd","pam","pcx","pgf","sgi","rgb","rgba","bw","int","inta","sid","ras","sun","tga"];
    $zipExt = ["7z","aar","ace","arj","apk","arc","ark","br","bz","bz2","cab","chm","deb","dmg","ear","egg","epub","gz","jar","lha","lrz","lz","lz4","lzh","lzma","lzo","lzop","mar","par2","pea","pet","pkg","rar","rpm","rz","s7z","shar","sit","sitx","tbz","tbz2","tgz","tlz","txz","tzo","war","whl","xpi","xz","z","zip","zipx","zoo","zpaq","zst"];
    $videoExt = ["3g2","3gp","aaf","asf","avchd","avi","drc","flv","m2v","m4v","mkv","mng","mov","mp4","mpe","mpeg","mpg","mpv","mxf","nsv","ogv","qt","rm","rmvb","roq","svi","vob","webm","wmv","yuv"];
    $audioExt =  ["wav","bwf","raw","aiff","flac","m4a","pac","tta","wv","ast","aac","mp2","mp3","amr","s3m","act","au","dct","dss","gsm","m4p","mmf","mpc","ogg","oga","opus","ra","sln","vox"];
    $powerpointExt = ["ppt","pot","pps","pptx","pptm","potx","potm","ppam","ppsx","ppsm","sldx","sldm"];
    $excelExt = ["xls","xlt","xlm","xlsx","xlsm","xltx","xltm","xlsb","xla","xlam","xll","xlw"];
    $wordExt = ["doc","dot","wbk","docx","docm","dotx","dotm","docb"];
    $webExt = ["html","css","js","php"];
    $glossaExt = ["psg","glo"];
    $pythonExt = "py";
    $pdfExt = "pdf";

    if($ext == $pdfExt) return 'pdf';
    if($ext == $pythonExt) return 'python';
    if(in_array($ext, $glossaExt)) return 'glossa';
    if(in_array($ext, $webExt)) return 'web';
    if(in_array($ext, $wordExt)) return 'word';
    if(in_array($ext, $excelExt)) return 'excel';
    if(in_array($ext, $powerpointExt)) return 'powerpoint';
    if(in_array($ext, $audioExt)) return 'audio';
    if(in_array($ext, $videoExt)) return 'video';
    if(in_array($ext, $zipExt)) return 'zip';
    if(in_array($ext, $imageExt)) return 'image';
    return 'file';
}

//wtf is this?????
function connsettings(/*$name, (optional)$val=$arg_list[1]*/){
    static $settings= array();
    $numargs        = func_num_args();
    $arg_list        = func_get_args();
    $name            = $arg_list[0];
   
    if($numargs === 1)
        return $settings[$name] ?? null;
   
    $oldVal            = $settings[$name] ?? null;
    $settings[$name]= $arg_list[1];

    return $oldVal;
}
function connection_close(){
    session_write_close();
    if(!headers_sent())
    {
        set_time_limit(0);
        ignore_user_abort(true);
        ob_end_flush();
        if(connsettings('compression') === false)
            header("Content-Encoding: none");
        header('Content-Length: ' . ob_get_length());
        header('Connection: close');
        if(ob_get_level() > 0)
            ob_flush();
        flush();
    }
}