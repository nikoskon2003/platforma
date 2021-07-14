<?php session_start(); 

if(!isset($_SESSION['type'])){
    include '../../error.php';
    exit();
}elseif($_SESSION['type'] !== 'ADMIN'){
    include '../../error.php';
    exit();
}

include_once '../../includes/config.php';

if(!isset($_GET['c'])){
    header("Location: .");
    exit();
}
if(!is_numeric($_GET['c'])){
    header("Location: .");
    exit();
}

$classId = (int)($_GET['c']);

include '../../includes/dbh.inc.php';
$res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id=$classId");
if($res->num_rows < 1){
    header("Location: .");
    exit();
}
$className = htmlentities($res->fetch_assoc()['class_name']);

date_default_timezone_set('Europe/Athens');
$now = date('d/m/Y H:i:s', time());

include_once '../../includes/enc.inc.php';
include_once '../../includes/extrasLoader.inc.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../favicon.ico" />
    <title><?= $siteName; ?> | Νέα Ανακοίνωση - <?= $className; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/classes/newpost.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">

    <link rel="stylesheet" href="../../styles/upload-view.css?v=<?= $pubFileVer; ?>" type="text/css">
    <script type="text/javascript" src="../../scripts/extension-icon.js?v=<?= $pubFileVer ?>"></script>
    <?= LoadBackground(__FILE__); ?>
</head>

<body>
<div id="container">
	<div id="header"><?= LoadTopNav(__FILE__); ?></div>

	<div id="body">
        <div class="home">
            <div class="desktop">
            <div class="title">Νέα Ανακοίνωση - <?= $className; ?></div>
            <div id="box-container">
                <form id="desktop-form" action="../../includes/admin/classes/newpost.inc.php" method="POST" onsubmit="return validateForm('desktop');">
                    <div class="template-post">
                        <div class="template-post-title"><textarea form="desktop-form" name="title" placeholder="Τίτλος (Υποχρεωτικός)"></textarea></div>
                        <div class="template-post-date"><?= $now; ?></div>
                        <div class="template-post-user"><?= $_SESSION['user_name']; ?></div>
                        <div class="template-post-line"></div>
                        <div class="template-post-text"><textarea form="desktop-form" name="text" placeholder="Κείμενο (Υποχρεωτικό)"></textarea></div>
                        <div class="template-post-line"></div>
                        <div class="template-post-file-container" display-files="true" style="display: none;">
                            <div class="file-select-button" onclick="openFileSelectView()">Επιλογή Αρχείων<img src="../../resources/edit-icon.png" /></div>                            
                        </div>
                        <div style="width: 100%;font-family:'Noto Sans';text-align:center;margin-top:10px;" no-js-text>Για την επιλογή αρχείων, παρακαλώ ενεργοποιήστε την JavaScript</div> 
                        <input type="hidden" name="files" value="" id="file-input" />
                    </div>
                    <div class="options-parent">
                        <div class="left-options">
                            <div class="category-name">Ορατότητα</div>
                            <div class="radio-holder">
                                <label class="radio-cont-vis">
                                    <input type="radio" checked="checked" name="visibility" value="all">
                                    <div class="radio-child">Όλοι</div>
                                </label>
                                <label class="radio-cont-vis">
                                    <input type="radio"name="visibility" value="none">
                                    <div class="radio-child">Κανένας</div>
                                </label>
                            </div>
                        </div>
                        <div class="right-options">
                            <div class="category-name">Αποστολή Ειδοποίησης</div>
                            <div class="radio-holder">
                                <label class="radio-cont-notif">
                                    <input type="radio" checked="checked" name="notification" value="yes">
                                    <div class="radio-child">Ναι</div>
                                </label>
                                <label class="radio-cont-notif">
                                    <input type="radio" name="notification" value="no">
                                    <div class="radio-child">Όχι</div>
                                </label>
                            </div>
                        </div>
                        <div class="bottom-buttons">
                            <input type="hidden" name="class-id" value="<?= $classId;?>"/>
                            <button type="submit" name="submit" value="submit" class="submit-button">Υποβολή</button>
                            <a href="./class.php?c=<?= $classId; ?>" class="cancel-button">Άκυρο</a>
                        </div>
                    </div>
                </form>
            </div>
            </div>

            <div class="mobile">
            <div class="title">Νέα Ανακοίνωση - <?= $className; ?></div>
            <div id="box-container">
                <form id="mobile-form" action="../../includes/admin/classes/newpost.inc.php" method="POST" onsubmit="return validateForm('mobile');">
                    <div class="template-post">
                        <div class="template-post-title"><textarea form="mobile-form" name="title" placeholder="Τίτλος (Υποχρεωτικός)"></textarea></div>
                        <div class="template-post-date"><?= $now; ?></div>
                        <div class="template-post-user"><?= $_SESSION['user_name']; ?></div>
                        <div class="template-post-line"></div>
                        <div class="template-post-text"><textarea form="mobile-form" name="text" placeholder="Κείμενο (Υποχρεωτικό)"></textarea></div>
                        <div class="template-post-line"></div>
                        <div class="template-post-file-container" display-files="true" style="display: none;">
                            <div class="file-select-button" onclick="openFileSelectView()">Επιλογή Αρχείων<img src="../../resources/edit-icon.png" /></div>                            
                        </div>
                        <div style="width: 100%;font-family:'Noto Sans';text-align:center;margin-top:10px;" no-js-text>Για την επιλογή αρχείων, παρακαλώ ενεργοποιήστε την JavaScript</div> 
                        <input type="hidden" name="files" value="" id="file-input-mb" />
                    </div>
                    <div class="options-parent">
                        <div class="bottom-buttons">

                            <div class="category-name">Ορατότητα</div>
                            <div class="radio-holder">
                                <label class="radio-cont-vis">
                                    <input type="radio" checked="checked" name="visibility" value="all">
                                    <div class="radio-child">Όλοι</div>
                                </label>
                                <label class="radio-cont-vis">
                                    <input type="radio"name="visibility" value="none">
                                    <div class="radio-child">Κανένας</div>
                                </label>
                            </div>
                            <br><br>
                            <div class="category-name">Αποστολή Ειδοποίησης</div>
                            <div class="radio-holder">
                                <label class="radio-cont-notif">
                                    <input type="radio" checked="checked" name="notification" value="yes">
                                    <div class="radio-child">Ναι</div>
                                </label>
                                <label class="radio-cont-notif">
                                    <input type="radio" name="notification" value="no">
                                    <div class="radio-child">Όχι</div>
                                </label>
                            </div>
                            <br><br><br>
                            <input type="hidden" name="class-id" value="<?= $classId;?>"/>
                            <button type="submit" name="submit" value="submit" class="submit-button">Υποβολή</button>
                            <a href="./class.php?c=<?= $classId; ?>" class="cancel-button">Άκυρο</a>
                        </div>
                    </div>
                </form>
            </div>
            </div>

            <script>
                function validateForm(ver){
                    if(ver == 'desktop'){
                        let title = document.querySelector('[form="desktop-form"][name="title"]').value;
                        let text = document.querySelector('[form="desktop-form"][name="text"]').value;

                        if(title.trim() == '' && text.trim() == ''){
                            alert('Ο τίτλος και το κείμενο δεν μπορούν να είναι κενά!');
                            return false;
                        }
                        else if(title.trim() == ''){
                            alert('Ο τίτλος δεν μπορεί να είναι κενός!');
                            return false;
                        }
                        else if(text.trim() == ''){
                            alert('Το κείμενο δεν μπορεί να είναι κενό!');
                            return false;
                        }
                        document.getElementById('action-hider').style.display = 'block';
                        return true;
                    }
                    else if(ver == 'mobile'){
                        let title = document.querySelector('[form="mobile-form"][name="title"]').value;
                        let text = document.querySelector('[form="mobile-form"][name="title"]').value;

                        if(title.trim() == '' && text.trim() == ''){
                            alert('Ο τίτλος και το κείμενο δεν μπορούν να είναι κενά!');
                            return false;
                        }
                        else if(title.trim() == ''){
                            alert('Ο τίτλος δεν μπορεί να είναι κενός!');
                            return false;
                        }
                        else if(title.trim() == ''){
                            alert('Το κείμενο δεν μπορεί να είναι κενό!');
                            return false;
                        }
                        document.getElementById('action-hider').style.display = 'block';
                        return true;
                    }
                    return false;
                }
            </script>

            <div>
            <element class="desktop">
                <div id="select-files-container">
                    <p class="file-limit"><?= 'Όριο: ' . ini_get('upload_max_filesize') . 'B/Αρχείο'; ?></p>
                    <div class="select-left">
                        <div id="filesystem">
                            <div id="fs-home"><div class="select-file-cont" onclick="openFolder('favs')"><img src="../../resources/icons/fav-folder.png"/><p class="star-folder-text">Αγαπημένα</p></div></div>
                            <div id="fs-favs" style="display: none;"><div class="select-file-cont" onclick="backToRoot()"><img src="../../resources/icons/back-folder.png"/><p class="back-folder-text">Πίσω</p></div></div>
                        </div>
                        <div id="file-info">
                            <div class="select-file-cont" onclick="closeFile()"><img src="../../resources/icons/back-folder.png"/><p class="back-folder-text">Πίσω</p></div>
                            <a id="download-file" href="." target="_blank" title="Λήψη Αρχείου"><img src="../../resources/download.png"/></a>
                            <p class="file-info-stat">Όνομα:</p>
                            <p id="file-info-name">-</p>
                            <p class="file-info-stat">Ημερομηνια Μεταφόρτωσης:</p>
                            <p id="file-info-date">-</p>
                            <p class="file-info-stat">Μέγεθος:</p>
                            <p id="file-info-size">-</p>
                            <div class="file-info-button" id="select-file-button" onclick="selectFile()">Επιλογή Αρχείου</div><br>
                            <div class="file-info-button" id="fav-file-button" onclick="toggleFav()">Προσθήκη στα Αγαπημένα</div><br>
                            <div class="file-info-button" id="delete-file-button" onclick="deleteFile()">Διαγραφή Αρχείου</div><br>
                        </div>
                    </div>
                    <div class="select-right">
                        <div class="right-top-part" id="selected-files-list"><div class="selected-files-title">Επιλεγμένα Αρχεία</div></div>
                        <div class="right-bottom-part">
                            <div class="bottom-upload-icon" title="Μεταφόρτωση Αρχείου" id="file-select-input-button">
                                <img src="../../resources/upload.png" />
                                <input type="file" name="f" id="file-select-input" style="display: none;" />
                            </div>
                            <div id="bottom-upload-area" style="display: none;">
                                <div id="upload-file-confirm">Υποβολή</div>
                                <div class="upload-load-bar"><p id="upload-load-percentage">-</p><div id="upload-load-bar-ind"></div></div>
                            </div>
                            <div class="bottom-close" onclick="closeFileSelectView()"><b>OK</b></div>
                        </div>
                    </div>
                
                </div>
            </element>
            <element class="mobile">
                <div id="select-files-container-mb">
                    <p class="file-limit"><?= ini_get('upload_max_filesize') . 'B/Αρχείο'; ?></p>

                    <div class="file-nav-buttons">
                        <div class="to-file-content active" onclick="openFileSystem();">Αρχεία</div>
                        <div class="to-selected" onclick="openSelectedFiles();">Επιλεγμένα</div>
                    </div>
                
                    <div class="file-content">
                        <div id="all-file-cont-mb" style="display: block; height: 100%;">
                            <div id="filesystem-mb">
                                <div id="fs-home-mb"><div class="select-file-cont" onclick="openFolder('favs')"><img src="../../resources/icons/fav-folder.png"/><p class="star-folder-text">Αγαπημένα</p></div></div>
                                <div id="fs-favs-mb" style="display: none;"><div class="select-file-cont" onclick="backToRoot()"><img src="../../resources/icons/back-folder.png"/><p class="back-folder-text">Πίσω</p></div></div>
                            </div>

                            <div id="file-info-mb">
                                <div class="select-file-cont" onclick="closeFile()"><img src="../../resources/icons/back-folder.png"/><p class="back-folder-text">Πίσω</p></div>
                                <a id="download-file-mb" href="." target="_blank" title="Λήψη Αρχείου"><img src="../../resources/download.png"/></a>
                                <p class="file-info-stat">Όνομα:</p>
                                <p id="file-info-name-mb">-</p>
                                <p class="file-info-stat">Ημερομηνια Μεταφόρτωσης:</p>
                                <p id="file-info-date-mb">-</p>
                                <p class="file-info-stat">Μέγεθος:</p>
                                <p id="file-info-size-mb">-</p>
                                <div class="file-info-button" id="select-file-button-mb" onclick="selectFile()">Επιλογή Αρχείου</div><br>
                                <div class="file-info-button" id="fav-file-button-mb" onclick="toggleFav()">Προσθήκη στα Αγαπημένα</div><br>
                                <div class="file-info-button" id="delete-file-button-mb" onclick="deleteFile()">Διαγραφή Αρχείου</div><br>
                            </div>
                        </div>

                        <div class="selected-files-mb" id="selected-files-list-mb"><div class="selected-files-title">Επιλεγμένα Αρχεία</div></div>

                    </div>

                    <div class="file-bottom-mb">
                        <div class="bottom-upload-icon" title="Μεταφόρτωση Αρχείου" id="file-select-input-button-mb">
                            <img src="../../resources/upload.png" />
                            <input type="file" name="f" id="file-select-input-mb" style="display: none;" />
                        </div>
                        <div id="bottom-upload-area-mb" style="display: none;">
                            <div id="upload-file-confirm-mb">Υποβολή</div>
                            <div class="upload-load-bar"><p id="upload-load-percentage-mb">-</p><div id="upload-load-bar-ind-mb"></div></div>
                        </div>
                        <div class="bottom-close" onclick="closeFileSelectView()"><b>OK</b></div>
                    </div>
                    <script>function openFileSystem(){document.getElementById('all-file-cont-mb').style.display='block';document.getElementById('selected-files-list-mb').style.display='none';document.getElementsByClassName('to-file-content')[0].classList.add('active');document.getElementsByClassName('to-selected')[0].classList.remove('active');} function openSelectedFiles(){document.getElementById('all-file-cont-mb').style.display='none';document.getElementById('selected-files-list-mb').style.display='block';document.getElementsByClassName('to-file-content')[0].classList.remove('active');document.getElementsByClassName('to-selected')[0].classList.add('active');}openFileSystem();</script>
                </div>
            </element>
            </div>
            <script type="text/javascript" src="../../scripts/upload-view.js?v=<?= $pubFileVer ?>"></script>
            <script>
                window.addEventListener('load', () => {
                    initFileSystem(document.getElementById('file-input'), document.getElementById('file-input-mb'));

                    document.querySelectorAll("[no-js-text]").forEach(e => e.style.display = "none");
                    document.querySelectorAll("[display-files]").forEach(e => e.style.display = "block");
                });
            </script>


        </div>

        <div id="action-hider">
            <img src="../../resources/loading.gif"><br>
            <p>Παρακαλώ περιμένετε..</p>
        </div>
    </div>
	<div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>