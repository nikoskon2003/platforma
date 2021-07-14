<?php
session_start();
include '../includes/config.php';

if(!isset($_GET['u'])){
    include '../error.php';
    exit();
}

include '../includes/dbh.inc.php';
include '../includes/enc.inc.php';
$otherUser = mysqli_real_escape_string($conn, $_GET['u']);

//I tried once writing to myself. Nothing too crazy happened... other than not having many friends
if($otherUser == $_SESSION['user_username']){
	include '../error.php';
	exit();
}

if(!isset($_SESSION['type']))
{
    if(isset($_COOKIE["autologin"]))
    {
        header("Location: ../includes/autologin.inc.php?r=messages%2Fmessages.php%3Fu%3D" . rawurlencode($otherUser));
        exit();
    }
    else
    {
        header("Location: ../login.php?r=messages%2Fmessages.php%3Fu%3D" . rawurlencode($otherUser));
        exit();
    }
}

if($_SESSION['type'] == "ADMIN"){
	include '../error.php';
	exit();
}

$res = mysqli_query($conn, "SELECT * FROM users WHERE user_username='$otherUser'");
if($res->num_rows < 1){
    include '../error.php';
    exit();
}
$row = $res->fetch_assoc();
$name = htmlentities(decrypt($row['user_name']));

include '../includes/extrasLoader.inc.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../favicon.ico" />
    <title><?= $siteName; ?> | <?= $name ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../styles/messages/messages.css?v=<?= $pubFileVer; ?>" type="text/css">
    <?= LoadBackground(__FILE__); ?>
    <?= LoadMathJax(); ?>

    <link rel="stylesheet" href="../resources/img-viewer/lib/view-bigimg.css?v=<?= $pubFileVer; ?>">
    <script src="../resources/img-viewer/lib/view-bigimg.js?v=<?= $pubFileVer; ?>"></script>
    <script type="text/javascript" src="../scripts/extension-icon.js?v=<?= $pubFileVer ?>"></script>
    <link rel="stylesheet" href="../styles/upload-view.css?v=<?= $pubFileVer; ?>" type="text/css">

    <script src="../scripts/getonline.js?v=<?= $pubFileVer; ?>"></script>
    <script src="../scripts/getconvo.js?v=<?= $pubFileVer; ?>"></script>
    <script src="../scripts/getmessages.js?v=<?= $pubFileVer; ?>"></script>
    <script src="../scripts/getlastread.js?v=<?= $pubFileVer; ?>"></script>
</head>

<body>
<div id="container">
	<div id="header"><?= LoadTopNav(__FILE__); ?></div>
	<div id="body">

    <div class="desktop">
        <div class="backdrop">
            <div class="info-bar">
                <div class="user-online"><div class="online-dot online-status-<?= str_replace(' ', '%20', rawurldecode($otherUser)); ?>"></div></div>
                <div class="user-name"><?= $name ?></div>
            </div>
            <div id="message-container">
                <div id="load-older" class="load-older" onclick="requestOlder();"><div>Φόρτωση Παλαιότερων</div></div>
                <div id="latest-read" class="latest-read"><div class="line-read"></div>Τελευταία Ανάγνωση<div class="line-read"></div></div>
            </div>
            <div class="text-bar">
                <div class="file-input" title="Επισύναψη Αρχείων" onclick="openFileSelectView();"><img id="send-file" src="../resources/attach.png" /></div>
                <div class="file-display" id="files-popup">
                    <div class="file-text" display-files="true"><div style="display: none">a</div></div>
                    <div class="send-input" title="Αποστολή Αρχείων" onclick="sendFiles();"><img id="send-files-img" src="../resources/send.png" /></div>
                    <div class="cancel-input" title="Ακύρωση" onclick="closeFileConfirm();"><img id="cancel-files-img" src="../resources/cancel.png" /></div>
                </div>
                <div class="text-input"><textarea id="text-area" class="tex2jax_ignore" placeholder="Μήνυμα...">!Δεν υπάρχει δυνατότητα αποστολής/λήψης μηνυμάτων!</textarea></div>
                <div class="send-input" title="Αποστολή Μηνύματος" onclick="sendMessage();"><img id="send-img" src="../resources/send.png" /></div>
            </div>
        </div>
    </div>

    <div class="mobile">
        <div class="backdrop-mb">
            <div class="info-bar">
                <div class="user-online"><div class="online-dot online-status-<?= str_replace(' ', '%20', rawurldecode($otherUser)); ?>"></div></div>
                <div class="user-name"><?= $name ?></div>
            </div>
            <div id="message-container-mb">
                <div id="load-older-mb" class="load-older" onclick="requestOlder();"><div>Φόρτωση Παλαιότερων</div></div>
                <div id="latest-read-mb" class="latest-read"><div class="line-read"></div>Τελευταία Ανάγνωση<div class="line-read"></div></div>
            </div>
            <div class="text-bar">
                <div class="file-input" title="Επισύναψη Αρχείων" onclick="openFileSelectView();"><img id="send-file-mb" src="../resources/attach.png" /></div>
                <div class="file-display" id="files-popup-mb">
                    <div class="file-text" display-files="true"><div style="display: none">a</div></div>
                    <div class="send-input" title="Αποστολή Αρχείων" onclick="sendFiles();"><img id="send-files-img-mb" src="../resources/send.png" /></div>
                    <div class="cancel-input" title="Ακύρωση" onclick="closeFileConfirm();"><img id="cancel-files-img-mb" src="../resources/cancel.png" /></div>
                </div>
                <div class="text-input"><textarea id="text-area-mb" class="tex2jax_ignore" placeholder="Μήνυμα...">!Δεν υπάρχει δυνατότητα αποστολής/λήψης μηνυμάτων!</textarea></div>
                <div class="send-input" title="Αποστολή Μηνύματος" onclick="sendMessage_mb();"><img id="send-img-mb" src="../resources/send.png" /></div>
            </div>
        </div>
    </div>

    <input type="hidden" id="filesData" name="files" value=""/>
    
    <script>
        const textarea = document.getElementById('text-area');
        const textarea_mb = document.getElementById('text-area-mb');
        textarea.innerHTML = '';
        textarea_mb.innerHTML = '';

        textarea.addEventListener('keydown', (e) => {
            if (e.keyCode == 13 && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
            autosize(e.target);
        });
        textarea_mb.addEventListener('keydown', (e) => {
            if (e.keyCode == 13 && !e.shiftKey) {
                e.preventDefault();
                sendMessage_mb();
            }
            autosize(e.target);
        });
                    
        function autosize(el){ 
            setTimeout(function(){
                el.style.cssText = 'height:auto;';
                el.style.cssText = 'height:' + Math.min(el.scrollHeight, 130) + 'px';
            }, 1);
        }
        Array.from(document.getElementsByClassName('load-older')).forEach(element => element.style.display = 'block');
    </script>

    <script>
        let sending = false;
        function sendMessage(){
            if(sending) return;

            let textdata = textarea.value;
            
            if(!(/\S/.test(textdata))){
                alert("Το μήνυμα δέν μπορεί να είναι κενό!");
                return;
            }                
            var data = new FormData();
 
            data.append('submit', 'yes');
            data.append('text', textdata.replace(new RegExp('\r?\n','g'), '<br>'));
            data.append('recipient', '<?= $otherUser; ?>');
            data.append('sender', '<?= $_SESSION['user_username']; ?>');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../includes/messages/sendmessage.inc.php', true);
            xhr.onload = function(e) {
                resetAll();
                sending = false;

                if(this.status == 200) {
                    console.log(e.currentTarget.responseText);
                    textarea.value = '';
                    autosize(textarea);
                }
                else {
                    alert('Υπήρξε κάποιο πρόβλημα και το μήνυμα δεν στάλθηκε');
                }
            }
            xhr.onloadstart = function(e)
            {
                sending = true;
                loadAll();
            }

            xhr.send(data);
        }
        function sendMessage_mb(){
            if(sending) return;

            let textdata = textarea_mb.value;
            
            if(!(/\S/.test(textdata))){
                alert("Το μήνυμα δέν μπορεί να είναι κενό!");
                return;
            }                
            var data = new FormData();
 
            data.append('submit', 'yes');
            data.append('text', textdata.replace(new RegExp('\r?\n','g'), '<br>'));
            data.append('recipient', '<?= $otherUser; ?>');
            data.append('sender', '<?= $_SESSION['user_username']; ?>');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../includes/messages/sendmessage.inc.php', true);
            xhr.onload = function(e) {
                resetAll();
                sending = false;

                if(this.status == 200) {
                    console.log(e.currentTarget.responseText);
                    textarea_mb.value = '';
                    autosize(textarea_mb);
                }
                else {
                    alert('Υπήρξε κάποιο πρόβλημα και το μήνυμα δεν στάλθηκε');
                }
            }
            xhr.onloadstart = function(e)
            {
                sending = true;
                loadAll();
            }

            xhr.send(data);
        }

        function openFileConfirm(){
            let inp = document.getElementById('filesData');
            if(inp.value.trim() == '') return;

            document.getElementById('files-popup').style.display = 'block';
            document.getElementById('files-popup-mb').style.display = 'block';

        }

        function closeFileConfirm(){
            document.getElementById('files-popup').style.display = 'none';
            document.getElementById('files-popup-mb').style.display = 'none';
        }

        function sendFiles(){
            if(sending) return;         
            var data = new FormData();
            data.append('files', document.getElementById('filesData').value);
            data.append('recipient', '<?= $otherUser; ?>');
            data.append('sender', '<?= $_SESSION['user_username']; ?>');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../includes/messages/sendfiles.inc.php', true);
            xhr.onload = function(e) {
                resetAll();
                closeFileConfirm();
                sending = false;

                if(this.status == 200) {
                    console.log(e.currentTarget.responseText);
                }
                else {
                    alert('Υπήρξε κάποιο πρόβλημα και τα αρχεία δεν στάλθηκαν');
                }
            }
            xhr.onloadstart = function(e)
            {
                sending = true;
                loadAll();
            }

            xhr.send(data);
        }


        function loadAll(){
            document.getElementById('send-img').src = '../resources/loading-white.gif';
            document.getElementById('send-img').style.paddingLeft = '3px';
            document.getElementById('cancel-files-img').src = '../resources/loading-white.gif';
            document.getElementById('cancel-files-img').style.paddingLeft = '3px';
            document.getElementById('send-files-img').src = '../resources/loading-white.gif';
            document.getElementById('send-files-img').style.paddingLeft = '3px';
            document.getElementById('send-file').src = '../resources/loading-white.gif';
            document.getElementById('send-file').style.paddingLeft = '3px';

            document.getElementById('send-img-mb').src = '../resources/loading-white.gif';
            document.getElementById('send-img-mb').style.paddingLeft = '3px';
            document.getElementById('cancel-files-img-mb').src = '../resources/loading-white.gif';
            document.getElementById('cancel-files-img-mb').style.paddingLeft = '3px';
            document.getElementById('send-files-img-mb').src = '../resources/loading-white.gif';
            document.getElementById('send-files-img-mb').style.paddingLeft = '3px';
            document.getElementById('send-file-mb').src = '../resources/loading-white.gif';
            document.getElementById('send-file-mb').style.paddingLeft = '3px';
        }
        function resetAll(){
            document.getElementById('send-img').src = '../resources/send.png';
            document.getElementById('send-img').style.paddingLeft = '0';
            document.getElementById('cancel-files-img').src = '../resources/cancel.png';
            document.getElementById('cancel-files-img').style.paddingLeft = 'unset';
            document.getElementById('send-files-img').src = '../resources/send.png';
            document.getElementById('send-files-img').style.paddingLeft = 'unset';
            document.getElementById('send-file').src = '../resources/attach.png';
            document.getElementById('send-file').style.paddingLeft = 'unset';

            document.getElementById('send-img-mb').src = '../resources/send.png';
            document.getElementById('send-img-mb').style.paddingLeft = '0';
            document.getElementById('cancel-files-img-mb').src = '../resources/cancel.png';
            document.getElementById('cancel-files-img-mb').style.paddingLeft = 'unset';
            document.getElementById('send-files-img-mb').src = '../resources/send.png';
            document.getElementById('send-files-img-mb').style.paddingLeft = 'unset';
            document.getElementById('send-file-mb').src = '../resources/attach.png';
            document.getElementById('send-file-mb').style.paddingLeft = 'unset';
        }
    </script>

<div>
    <element class="desktop">
        <div id="select-files-container">
            <p class="file-limit"><?= 'Όριο: ' . ini_get('upload_max_filesize') . 'B/Αρχείο'; ?></p>
            <div class="select-left">
                <div id="filesystem">
                    <div id="fs-home"><div class="select-file-cont" onclick="openFolder('favs')"><img src="../resources/icons/fav-folder.png"/><p class="star-folder-text">Αγαπημένα</p></div></div>
                    <div id="fs-favs" style="display: none;"><div class="select-file-cont" onclick="backToRoot()"><img src="../resources/icons/back-folder.png"/><p class="back-folder-text">Πίσω</p></div></div>
                </div>
                <div id="file-info">
                    <div class="select-file-cont" onclick="closeFile()"><img src="../resources/icons/back-folder.png"/><p class="back-folder-text">Πίσω</p></div>
                    <a id="download-file" href="." target="_blank" title="Λήψη Αρχείου"><img src="../resources/download.png"/></a>
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
                        <img src="../resources/upload.png" />
                        <input type="file" name="f" id="file-select-input" style="display: none;" />
                    </div>
                    <div id="bottom-upload-area" style="display: none;">
                        <div id="upload-file-confirm">Υποβολή</div>
                        <div class="upload-load-bar"><p id="upload-load-percentage">-</p><div id="upload-load-bar-ind"></div></div>
                    </div>
                    <div class="bottom-close" onclick="closeFileSelectView(); openFileConfirm();"><b>OK</b></div>
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
                        <div id="fs-home-mb"><div class="select-file-cont" onclick="openFolder('favs')"><img src="../resources/icons/fav-folder.png"/><p class="star-folder-text">Αγαπημένα</p></div></div>
                        <div id="fs-favs-mb" style="display: none;"><div class="select-file-cont" onclick="backToRoot()"><img src="../resources/icons/back-folder.png"/><p class="back-folder-text">Πίσω</p></div></div>
                    </div>

                    <div id="file-info-mb">
                        <div class="select-file-cont" onclick="closeFile()"><img src="../resources/icons/back-folder.png"/><p class="back-folder-text">Πίσω</p></div>
                        <a id="download-file-mb" href="." target="_blank" title="Λήψη Αρχείου"><img src="../resources/download.png"/></a>
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
                    <img src="../resources/upload.png" />
                    <input type="file" name="f" id="file-select-input-mb" style="display: none;" />
                </div>
                <div id="bottom-upload-area-mb" style="display: none;">
                    <div id="upload-file-confirm-mb">Υποβολή</div>
                    <div class="upload-load-bar"><p id="upload-load-percentage-mb">-</p><div id="upload-load-bar-ind-mb"></div></div>
                </div>
                <div class="bottom-close" onclick="closeFileSelectView(); openFileConfirm();"><b>OK</b></div>
            </div>
            <script>function openFileSystem(){document.getElementById('all-file-cont-mb').style.display='block';document.getElementById('selected-files-list-mb').style.display='none';document.getElementsByClassName('to-file-content')[0].classList.add('active');document.getElementsByClassName('to-selected')[0].classList.remove('active');} function openSelectedFiles(){document.getElementById('all-file-cont-mb').style.display='none';document.getElementById('selected-files-list-mb').style.display='block';document.getElementsByClassName('to-file-content')[0].classList.remove('active');document.getElementsByClassName('to-selected')[0].classList.add('active');}openFileSystem();</script>
        </div>
    </element>
    </div>
    <script type="text/javascript" src="../scripts/upload-view.js?v=<?= $pubFileVer ?>"></script>
    <script>
        window.addEventListener('load', () => {
            initFileSystem(document.getElementById('filesData'), document.getElementById('filesData'));
        });
    </script>


	</div>
	<div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>