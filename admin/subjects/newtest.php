<?php session_start(); 

if(!isset($_SESSION['type'])){
    include '../../error.php';
    exit();
}elseif($_SESSION['type'] !== 'ADMIN'){
    include '../../error.php';
    exit();
}

include_once '../../includes/config.php';

if(!isset($_GET['s'])){
    header("Location: .");
    exit();
}
if(!is_numeric($_GET['s'])){
    header("Location: .");
    exit();
}

$subjId = (int)($_GET['s']);

include_once '../../includes/enc.inc.php';
include '../../includes/dbh.inc.php';
$res = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id=$subjId");
if($res->num_rows < 1){
    header("Location: .");
    exit();
}
$subjName = htmlentities(decrypt($res->fetch_assoc()['subject_name']));

date_default_timezone_set('Europe/Athens');
$now = date('d/m/Y H:i:s', time());

include_once '../../includes/extrasLoader.inc.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../favicon.ico" />
    <title><?= $siteName; ?> | Νέο Test - <?= $subjName; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../styles/admin/subjects/newtest.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/topnav.css?v=<?= $pubFileVer; ?>" type="text/css">
    <link rel="stylesheet" href="../../styles/footer.css?v=<?= $pubFileVer; ?>" type="text/css">

    <?= LoadBackground(__FILE__); ?>
</head>

<body>
<div id="container">
	<div id="header"><?= LoadTopNav(__FILE__); ?></div>

	<div id="body">

        <div class="desktop dt">
            <p class="title">Νέο Test - <?= $subjName; ?></p>
            <div class="main-cont" usg="no-js" style="background-color: white;">
                <p style="padding-top: 20px; font-size: 18px; font-family: 'Noto Sans';">Παρακαλώ ενεργοποιήστε την JavaScript!</p>
                <a href="./subject.php?s=<?= $subjId; ?>" class="button">Πίσω</a><br><br>
            </div>
            <div class="main-cont" usg="test-maker" style="display: none">
                <div class="questions-holder"></div>
                <div class="control-buttons">
                    <a class="button" onclick="openNewSelect();">Προσθήκη Ερώτησης<img src="../../resources/new.png" /></a>
                    <a class="button" onclick="goToFinalInfo();">Επόμενο</a>
                    <a href="./subject.php?s=<?= $subjId; ?>" class="button" style="color:red" onclick="return confirm('Είστε σίγουροι ότι θέλετε να επιστρέψετε;');">Άκυρο</a>
                </div>
                <div class="new-question-selector">
                    <div class="q-type-cont">
                        <p class="new-q-title">Τύπος Ερώτησης</p>
                        <label class="new-q-option" onclick="qSelectText();">
                            <input type="radio" name="q-type-option" checked="checked" />
                            <div class="new-q-option-display">Κείμενο</div>
                        </label>
                        <label class="new-q-option" onclick="qSelectImage();">
                            <input type="radio" name="q-type-option" />
                            <div class="new-q-option-display">Εικόνα</div>
                        </label>
                    </div>
                    <div class="q-type-cont">
                        <p class="new-q-title">Τύπος Απάντησης</p>    
                        <label class="new-q-option" onclick="aSelectSingle();">
                            <input type="radio" name="a-type-option" checked="checked" />
                            <div class="new-q-option-display">Μιάς Επιλογής</div>
                        </label>
                        <label class="new-q-option" onclick="aSelectMultiple();">
                            <input type="radio" name="a-type-option" />
                            <div class="new-q-option-display">Πολλών Επιλογών</div>
                        </label>
                        <label class="new-q-option" onclick="aSelectText();">
                            <input type="radio" name="a-type-option" />
                            <div class="new-q-option-display">Κείμενο</div>
                        </label>
                    </div>
                    <a class="button" onclick="createQuestion();">Δημιουργία</a>
                    <a class="button" style="color:red" onclick="closeNewSelect();">Άκυρο</a>
                </div>
            </div>
            <div class="main-cont" usg="final-info" style="display: none;">
                <p class="field-name-text">Όνομα Test</p>
                <input type="text" name="name" placeholder="Όνομα"/>
                <p class="field-name-text">Λήξη Προθεσμίας Υποβολών</p>
                <div class="time">
                    <div class="time-cont">
                        <p class="time-title">Χρονιά</p>
                        <input type="number" name="year" placeholder="<?= (int)date("Y", time()); ?>"/>
                    </div>

                    <div class="time-cont">
                        <p class="time-title">Μήνας</p>
                        <input type="number" name="month" placeholder="<?= (int)date("m", time()); ?>" min="1" max="12"/>
                    </div>

                    <div class="time-cont">
                        <p class="time-title">Ημέρα</p>
                        <input type="number" name="day" placeholder="<?= (int)date("d", time()); ?>" min="1" max="31"/>
                    </div>

                    <div class="time-cont">
                        <p class="time-title">Ώρα</p>
                        <input type="number" name="hour" placeholder="<?= (int)date("H", time()); ?>" min="0" max="23"/>
                    </div>

                    <div class="time-cont">
                        <p class="time-title">Λεπτό</p>
                        <input type="number" name="minute" placeholder="<?= (int)date("i", time()); ?>" min="0" max="59"/>
                    </div>
                </div>
                <div class="field-name-text">Ορατότητα</div>
                <div class="radio-holder">
                    <label class="radio-cont-vis">
                        <input type="radio" checked="checked" name="visibility" value="all">
                        <div class="radio-child">Όλοι</div>
                    </label>
                    <label class="radio-cont-vis">
                        <input type="radio" name="visibility" value="none">
                        <div class="radio-child">Κανένας</div>
                    </label>
                </div>

                <a class="button" onclick="submitTest(0);">Υποβολή</a>
                <br>
            </div>
        </div>

        <div class="mobile dt">
        <br>
        <p class="title">Νέο Test - <?= $subjName; ?></p>
            <div class="main-cont-mb" usg="no-js" style="background-color: white;">
                <p style="padding-top: 20px; font-size: 18px; font-family: 'Noto Sans';">Παρακαλώ ενεργοποιήστε την JavaScript!</p>
                <a href="./subject.php?s=<?= $subjId; ?>" class="button">Πίσω</a><br><br>
            </div>
            <div class="main-cont-mb" usg="test-maker" style="display: none">
                <div class="questions-holder"></div>
                <div class="control-buttons">
                    <a class="button" onclick="openNewSelect();">Προσθήκη Ερώτησης<img src="../../resources/new.png" /></a>
                    <a class="button" onclick="goToFinalInfo();">Επόμενο</a>
                    <a href="./subject.php?s=<?= $subjId; ?>" class="button" style="color:red" onclick="return confirm('Είστε σίγουροι ότι θέλετε να επιστρέψετε;');">Άκυρο</a>
                </div>
                <div class="new-question-selector">
                    <div class="q-type-cont">
                        <p class="new-q-title">Τύπος Ερώτησης</p>
                        <label class="new-q-option" onclick="qSelectText();">
                            <input type="radio" name="q-type-option-mb" checked="checked" />
                            <div class="new-q-option-display">Κείμενο</div>
                        </label>
                        <label class="new-q-option" onclick="qSelectImage();">
                            <input type="radio" name="q-type-option-mb" />
                            <div class="new-q-option-display">Εικόνα</div>
                        </label>
                    </div>
                    <div class="q-type-cont">
                        <p class="new-q-title">Τύπος Απάντησης</p>    
                        <label class="new-q-option" onclick="aSelectSingle();">
                            <input type="radio" name="a-type-option-mb" checked="checked" />
                            <div class="new-q-option-display">Μιάς Επιλογής</div>
                        </label>
                        <label class="new-q-option" onclick="aSelectMultiple();">
                            <input type="radio" name="a-type-option-mb" />
                            <div class="new-q-option-display">Πολλών Επιλογών</div>
                        </label>
                        <label class="new-q-option" onclick="aSelectText();">
                            <input type="radio" name="a-type-option-mb" />
                            <div class="new-q-option-display">Κείμενο</div>
                        </label>
                    </div>
                    <a class="button" onclick="createQuestion();">Δημιουργία</a>
                    <a class="button" style="color:red" onclick="closeNewSelect();">Άκυρο</a>
                </div>
            </div>
            <div class="main-cont-mb" usg="final-info" style="display: none;">
                <p class="field-name-text">Όνομα Test</p>
                <input type="text" name="name" placeholder="Όνομα"/>
                <p class="field-name-text">Λήξη Προθεσμίας Υποβολών</p>
                <div class="time">
                    <div class="time-cont">
                        <p class="time-title">Χρονιά</p>
                        <input type="number" name="year" placeholder="<?= (int)date("Y", time()); ?>"/>
                    </div>

                    <div class="time-cont">
                        <p class="time-title">Μήνας</p>
                        <input type="number" name="month" placeholder="<?= (int)date("m", time()); ?>" min="1" max="12"/>
                    </div>

                    <div class="time-cont">
                        <p class="time-title">Ημέρα</p>
                        <input type="number" name="day" placeholder="<?= (int)date("d", time()); ?>" min="1" max="31"/>
                    </div>

                    <div class="time-cont">
                        <p class="time-title">Ώρα</p>
                        <input type="number" name="hour" placeholder="<?= (int)date("H", time()); ?>" min="0" max="23"/>
                    </div>

                    <div class="time-cont">
                        <p class="time-title">Λεπτό</p>
                        <input type="number" name="minute" placeholder="<?= (int)date("i", time()); ?>" min="0" max="59"/>
                    </div>
                </div>
                <div class="field-name-text">Ορατότητα</div>
                <div class="radio-holder">
                    <label class="radio-cont-vis">
                        <input type="radio" checked="checked" name="visibility-mb" value="all">
                        <div class="radio-child">Όλοι</div>
                    </label>
                    <label class="radio-cont-vis">
                        <input type="radio" name="visibility-mb" value="none">
                        <div class="radio-child">Κανένας</div>
                    </label>
                </div>

                <a class="button" onclick="submitTest(1);">Υποβολή</a>
                <br>
            </div>
        </div>

        <div id="action-hider">
            <img src="../../resources/loading.gif"><br>
            <p>Υποβολή test...</p>
        </div>

        <script>
            window.addEventListener('load', (e) => {
                document.querySelectorAll('[usg="no-js"]').forEach(el => el.style.display = 'none');
                document.querySelectorAll('[usg="test-maker"]').forEach(el => el.style.display = 'block');
            });
        </script>

        <script>
            function openNewSelect(){
                document.querySelectorAll(".new-question-selector").forEach(el => {
                    el.style.display = 'block';
                });
            }
            function closeNewSelect(){
                document.querySelectorAll(".new-question-selector").forEach(el => {
                    el.style.display = 'none';
                });
            }

            let newQuestionType = 0;
            let newAnswerType = 0;

            let cuId = 0;
            let questionsArr = [];
            let finalTestData = null;
    
            function qSelectText(){
                newQuestionType = 0;
            }
            function qSelectImage(){
                newQuestionType = 1;
            }
            function aSelectSingle(){
                newAnswerType = 0;
            }
            function aSelectMultiple(){
                newAnswerType = 1;
            }
            function aSelectText(){
                newAnswerType = 2;
            }

            function createQuestion(){
                closeNewSelect();

                let questionId = cuId++;

                let questionDispId = cuId++;
                let qStr = '';
                if(newQuestionType == 0){
                    qStr = '<div class="question-text" cuid="' + questionDispId + '"><textarea placeholder="Κείμενο Ερώτησης"></textarea></div>';
                }
                else if(newQuestionType == 1){
                    qStr = '<div class="question-image" cuid="' + questionDispId + '"><label class="file-label"><input onchange="loadImage(this);" type="file" accept="image/*" style="display: none">Επιλογή Φωτογραφίας</label><img src="../../resources/icons/image.png"/></div>';
                }
                else return;

                let answerId = cuId++;
                let aStr = '';
                if(newAnswerType == 0){
                    aStr = '<div class="question-answer" cuid="' + answerId + '"><a class="button" onclick="addRadio(' + answerId + ');">Προσθήκη Επιλογής<img src="../../resources/new.png" /></a></div>';
                }
                else if(newAnswerType == 1){
                    aStr = '<div class="question-answer" cuid="' + answerId + '"><a class="button" onclick="addCheckbox(' + answerId + ');">Προσθήκη Επιλογής<img src="../../resources/new.png" /></a></div>';
                }
                else if(newAnswerType == 2){
                    aStr = '<div class="question-answer" cuid="' + answerId + '">Η λειτουργία κειμένου θα εμφανιστεί αυτόματα στους μαθητές.</div>';
                }
                else return;

                document.querySelectorAll(".questions-holder").forEach(el => {
                    let div = document.createElement('div');
                    div.classList.add('question');
                    div.setAttribute("cuid", questionId);
                    div.innerHTML = '<div class="question-edit-buttons"><div class="up-button" title="Μετακίνηση πάνω" onclick="questionMoveUp(' + questionId + ');"><img src="../../resources/up.png"></div><div class="del-button" title="Διαγραφή" onclick="deleteQuestion(' + questionId + ');"><img src="../../resources/delete.png"></div><div class="down-button" title="Μετακίνηση κάτω" onclick="questionMoveDown(' + questionId + ');"><img src="../../resources/up.png"></div></div>' + qStr + aStr;
                    el.insertBefore(div, null);
                
                    if(newQuestionType == 0) {
                        div.querySelector('[cuid="' + questionDispId + '"].question-text').querySelector('textarea').addEventListener('input', (e) => {
                            document.querySelectorAll('[cuid="' + questionDispId + '"].question-text').forEach(el => el.querySelector('textarea').value = e.srcElement.value);
                        });
                    }
                });

                questionsArr.push({id: questionId, questionDisp: questionDispId, questionDispType: newQuestionType, answer: answerId, answerType: newAnswerType});
            }

            function questionMoveUp(id){
                let idx = getQuestionIdx(id);
                if(idx < 1) return;

                document.querySelectorAll('.questions-holder').forEach(el => {
                    let selElm = el.querySelector('[cuid="' + id + '"]');
                    el.insertBefore(selElm, selElm.previousElementSibling);
                });

                let tmp = questionsArr[idx];
                questionsArr[idx] = questionsArr[idx-1];
                questionsArr[idx-1] = tmp;
            }
            function deleteQuestion(id){
                let idx = getQuestionIdx(id);
                if(idx < 0) return;

                if(!confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτήν την ερώτηση;')) return;

                document.querySelectorAll('.questions-holder').forEach(el => {
                    let selElm = el.querySelector('[cuid="' + id + '"]');
                    selElm.remove();
                });

                questionsArr.splice(idx, 1);
            }
            function questionMoveDown(id){
                let idx = getQuestionIdx(id);
                if(idx >= questionsArr.length - 1 || idx < 0) return;

                document.querySelectorAll('.questions-holder').forEach(el => {
                    let selElm = el.querySelector('[cuid="' + id + '"]');
                    el.insertBefore(selElm.nextElementSibling, selElm);
                });

                let tmp = questionsArr[idx];
                questionsArr[idx] = questionsArr[idx+1];
                questionsArr[idx+1] = tmp;
            }

            function addRadio(id){
                let radioId = cuId++;
                let rStr = '<div class="edit-cont"><div class="up-button" title="Μετακίνηση πάνω" onclick="radioMoveUp(' + radioId + ')"><img src="../../resources/up.png"></div><br><div class="del-button" title="Διαγραφή" onclick="deleteRadio(' + radioId + ')"><img src="../../resources/delete.png"></div><br><div class="down-button" title="Μετακίνηση κάτω" onclick="radioMoveDown(' + radioId + ')"><img src="../../resources/up.png"></div></div><input type="radio" disabled="disabled"><textarea class="radio-text" placeholder="Κείμενο απάντησης"></textarea>';
                document.querySelectorAll('[cuid="' + id + '"].question-answer').forEach(el => {
                    let radio = document.createElement('label');
                    radio.classList.add('radio');
                    radio.setAttribute("cuid", radioId);
                    radio.innerHTML = rStr;
                    el.insertBefore(radio, el.lastChild);

                    radio.querySelector('textarea').addEventListener('input', (e) => {
                        document.querySelectorAll('[cuid="' + radioId + '"].radio').forEach(el => el.querySelector('textarea').value = e.srcElement.value);
                    });
                });
            }
            function addCheckbox(id){
                let checkboxId = cuId++;
                let rStr = '<div class="edit-cont"><div class="up-button" title="Μετακίνηση πάνω" onclick="radioMoveUp(' + checkboxId + ')"><img src="../../resources/up.png"></div><br><div class="del-button" title="Διαγραφή" onclick="deleteRadio(' + checkboxId + ')"><img src="../../resources/delete.png"></div><br><div class="down-button" title="Μετακίνηση κάτω" onclick="radioMoveDown(' + checkboxId + ')"><img src="../../resources/up.png"></div></div><input type="checkbox" disabled="disabled"><textarea class="radio-text" placeholder="Κείμενο απάντησης"></textarea>';
                document.querySelectorAll('[cuid="' + id + '"].question-answer').forEach(el => {
                    let cbx = document.createElement('label');
                    cbx.classList.add('radio');
                    cbx.setAttribute("cuid", checkboxId);
                    cbx.innerHTML = rStr;
                    el.insertBefore(cbx, el.lastChild);

                    cbx.querySelector('textarea').addEventListener('input', (e) => {
                        document.querySelectorAll('[cuid="' + checkboxId + '"].radio').forEach(el => el.querySelector('textarea').value = e.srcElement.value);
                    });
                });
            }
            function radioMoveUp(id){
                document.querySelectorAll('[cuid="' + id + '"].radio').forEach(el => {
                    let prev = el.previousElementSibling;
                    if(prev == null) return;

                    el.parentElement.insertBefore(el, prev);
                });
            }
            function deleteRadio(id) {
                if(!confirm("Είστε σίγουροι ότι θέλετε να διαγράψετε την επιλογή;")) return;
                document.querySelectorAll('[cuid="' + id + '"].radio').forEach(el => el.remove());
            }
            function radioMoveDown(id){
                document.querySelectorAll('[cuid="' + id + '"].radio').forEach(el => {
                    let nxt = el.nextElementSibling;
                    if(nxt == null || nxt.classList.contains('button')) return;

                    el.parentElement.insertBefore(nxt, el);
                });
            }

            function loadImage(elem) {
                let cuid = parseInt(elem.parentElement.parentElement.getAttribute("cuid"));
                if(isNaN(cuid)) return;

                let filesToUpload = elem.files;

                if(filesToUpload.length < 1){
                    document.querySelectorAll('[cuid="' + cuid + '"].question-image').forEach(el => {
                        el.querySelector('img').src = '../../resources/icons/image.png';
                    });
                    return;
                }

                var file = filesToUpload[0];

                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = new Image();

                    img.onload = function(ie){

                        var canvas = document.createElement("canvas");
                        var ctx = canvas.getContext("2d");
                        ctx.drawImage(img, 0, 0);

                        var MAX_WIDTH = 300;
                        var MAX_HEIGHT = 300;

                        var width = img.width;
                        var height = img.height;

                        //Limited by css :)
                        /*if (width > height) {
                            if (width != MAX_WIDTH) {
                                height *= MAX_WIDTH / width;
                                width = MAX_WIDTH;
                            }
                        } 
                        else {
                            if (height != MAX_HEIGHT) {
                                width *= MAX_HEIGHT / height;
                                height = MAX_HEIGHT;
                            }
                        }*/

                        canvas.width = width;
                        canvas.height = height;
                        var ctx = canvas.getContext("2d");
                        ctx.drawImage(img, 0, 0, width, height);

                        var dataurl = canvas.toDataURL("image/png");
                        document.querySelectorAll('[cuid="' + cuid + '"].question-image').forEach(el => {
                            el.querySelector('img').src = dataurl;
                        });
                    }
                    img.src = e.target.result;
                }

                reader.readAsDataURL(file);
            }

            function processTest(){
                document.querySelectorAll('.questions-holder').forEach(elm => elm.querySelectorAll(':not(.whatever-idk)').forEach(el => el.removeAttribute('style')));

                let out = [];
                for(let i = 0; i < questionsArr.length; i++){
                    let tmpl = {q: questionsArr[i].questionDispType, qd: '', a: questionsArr[i].answerType, ad: null};

                    if(tmpl.q == 0){
                        let elm = document.querySelector('[cuid="' + questionsArr[i].questionDisp + '"].question-text');
                        if(elm == null) continue;
                        elm = elm.querySelector('textarea');
                        if(elm == null) continue;
                        let text = elm.value.trim().replace(new RegExp('\r?\n','g'), '<br>');

                        if(text == null || text == ''){
                            elm.style.border = '2px solid red';
                            alert('Το κείμενο δεν μπορεί να είναι κενό!');
                            return false;
                        }

                        tmpl.qd = text;
                    }
                    else if(tmpl.q == 1){
                        let elm = document.querySelector('[cuid="' + questionsArr[i].questionDisp + '"].question-image');
                        if(elm == null) continue;
                        elm = elm.querySelector('img');
                        if(elm == null) continue;
                        let src = elm.src;

                        if(src.endsWith('/image.png') || !src.startsWith('data:image/')) {
                            elm.parentElement.style.border = '2px solid red';
                            alert('Η φωτογραφία δεν μπορεί να είναι κενή!');
                            return false;
                        }

                        tmpl.qd = questionsArr[i].questionDisp;
                    }
                    else continue;

                    if(tmpl.a == 0 || tmpl.a == 1){
                        let elm = document.querySelector('[cuid="' + questionsArr[i].answer + '"].question-answer');
                        if(elm == null) continue;

                        let cnt = 0;
                        let rdat = [];
                        let ret = false;
                        elm.querySelectorAll('.radio').forEach(el => {
                            if(ret) return;
                            cnt++;

                            let elme = el.querySelector('textarea');
                            if(elme == null) return;
                            let text = elme.value.trim().replace(new RegExp('\r?\n','g'), '<br>');

                            if(text == null || text == ''){
                                elme.style.border = '2px solid red';
                                alert('Το κείμενο δεν μπορεί να είναι κενό!');
                                ret = true;
                                return;
                            }

                            rdat.push(text);
                        });
                        if(ret) return false;

                        if(cnt < 1){
                            elm.style.border = '2px solid red';
                            alert('Πρέπει να υπάρχει τουλάχιστον μια επιλογή!');
                            return false;
                        }

                        tmpl.ad = rdat;
                    }
                    else if(tmpl.a == 2){
                        tmpl.ad = 'eh';
                    }
                    else continue;

                    out.push(tmpl);
                }

                if(out.length < 1){
                    alert("Το test δεν μπορεί να είναι κενό!");
                    return false;
                }

                finalTestData = out;
                return true;
            }

            function goToFinalInfo(){
                if(!confirm('Είστε σίγουροι ότι θέλετε να ολοκληρώσετε το test και να μεταβείτε στις τελικές ρυθμήσεις;')) return;
                if(!processTest()) return;

                document.querySelectorAll('[usg="test-maker"]').forEach(el => el.style.display = 'none');
                document.querySelectorAll('[usg="final-info"]').forEach(el => el.style.display = 'block');
            }

            function submitTest(from){
                let elm;
                if(from == 0) elm = document.querySelector('.desktop.dt');
                else if(from == 1) elm = document.querySelector('.mobile.dt');
                else return;

                if(!confirm("Θέλετε σίγουρα να δημιουργήσετε το test;")) return;

                let name = elm.querySelector('[name="name"]').value.trim();

                if(name == '' || name == null){
                    alert("Το όνομα δεν μπορεί να είναι κενό!");
                    return;
                }

                let year = elm.querySelector('[name="year"]').value;
                let month = elm.querySelector('[name="month"]').value;
                let day = elm.querySelector('[name="day"]').value;
                let hour = elm.querySelector('[name="hour"]').value;
                let minute = elm.querySelector('[name="minute"]').value;

                if(!verifyDate(year, month, day, hour, minute)) return;

                let vis = 0;
                if(from == 0) vis = elm.querySelector('[name="visibility"]:checked').value;
                else vis = elm.querySelector('[name="visibility-mb"]:checked').value;

                let data = new FormData();
                data.append('s', '<?= $subjId; ?>');
                data.append('name', name);

                data.append('year', year);
                data.append('month', month);
                data.append('day', day);
                data.append('hour', hour);
                data.append('minute', minute);

                data.append('vis', (vis == 'all') ? 1 : 0);

                data.append('data', JSON.stringify(finalTestData));

                let xhr = new XMLHttpRequest();
                xhr.open('POST', '../../includes/admin/subjects/newtest.inc.php', true);
                xhr.onload = function(e) {
                    if(e.currentTarget.responseText.startsWith('ok')){
                        let testId = e.currentTarget.responseText.split('-')[1];

                        let imgs = document.querySelector('.questions-holder').querySelectorAll('.question-image');
                        let arr = [];
                        imgs.forEach(el => arr.push(parseInt(el.getAttribute('cuid'))));
                        uploadImages(testId, arr);
                    }
                    else window.location = './subject.php?s=<?= $subjId; ?>';
                }

                let blocker = document.getElementById('action-hider');
                blocker.style.display = 'block';
                let dispP = blocker.querySelector('p');
                dispP.innerHTML = 'Μεταφόρτωση στοιχείων test...';

                xhr.upload.addEventListener("progress", (e) => {
                    let prc = Math.round(e.loaded / e.total * 1000)/10;
                    dispP.innerHTML = 'Μεταφόρτωση στοιχείων test (' + prc + '%)';
                }, true);

                xhr.send(data);
            }

            function uploadImages(tid, images){
                if(images.length < 1){ 
                    window.location = './subject.php?s=<?= $subjId; ?>';
                    return;
                }

                let dispP = document.getElementById('action-hider').querySelector('p');
                dispP.innerHTML = 'Μεταφόρτωση φωτογραφιών (Απομένουν: ' + images.length + ')...';

                let iid = images.shift();

                let image = document.querySelector('[cuid="' + iid + '"].question-image');
                if(image == null){
                    uploadImages(tid, images);
                    return;
                }
                image = image.querySelector('img');
                if(image == null){
                    uploadImages(tid, images);
                    return;
                }
                if(image.src.endsWith('/image.png') || !image.src.startsWith('data:image/')) {
                    uploadImages(tid, images);
                    return;
                }

                let data = new FormData();
                data.append('tid', tid);
                data.append('img', iid);
                data.append('data', image.src);

                let xhr = new XMLHttpRequest();
                xhr.open('POST', '../../includes/admin/subjects/uploadtestimg.inc.php', true);
                xhr.onload = function(e) {
                    uploadImages(tid, images);
                }

                xhr.upload.addEventListener("progress", (e) => {
                    let prc = Math.round(e.loaded / e.total * 1000)/10;
                    dispP.innerHTML = 'Μεταφόρτωση φωτογραφιών (' + prc + '%) (Απομένουν: ' + (images.length+1) + ')';
                });

                xhr.send(data);
            }

            function verifyDate(year, month, day, hour, minute){
                if(isNaN(year) || year == ''){
                    alert("Η χρονιά δεν μπορεί να είναι κενή!");
                    return false;
                }
                year = parseInt(year);

                if(isNaN(month) || month == ''){
                    alert("Ο μήνας δεν μπορεί να είναι κενός!");
                    return false;
                }

                month = parseInt(month);
                if(parseInt(month) < 1 || parseInt(month) > 12){
                    redElem(month);
                    setTimeout(function() { alert("Ο μήνας πρέπει να είναι μεταξύ 1 (Ιανουάριος) και 12 (Δεκέμβριος)!"); }, 5);
                    return false;
                }

                if(isNaN(day) || day == ''){
                    alert("Η ημέρα δεν μπορεί να είναι κενή!");
                    return false;
                }
                day = parseInt(day);
                let dim = parseInt(new Date(year, month, 0).getDate());
                if(day < 1 || day > dim){
                    alert("Η ημέρα πρέπει να είναι μεταξύ 1 και " + dim + "!");
                    return false;
                }

                if(isNaN(hour) || hour == ''){
                    alert("Η ώρα δεν μπορεί να είναι κενή!");
                    return false;
                }
                hour = parseInt(hour);
                if(hour < 0 || hour > 23){
                    alert("Η ώρα πρέπει να είναι μεταξύ 0 και 23!");
                    return false;
                }

                if(isNaN(minute) || minute == ''){
                    alert("Τα λεπτά δεν μπορεί να είναι κενά!");
                    return false;
                }
                minute = parseInt(minute);
                if(minute < 0 || minute > 59){
                    alert("Τα λεπτά πρέπει να είναι μεταξύ 0 και 59!");
                    return false;
                }

                return true;
            }

            function getQuestionIdx(id){
                for(let i = 0; i < questionsArr.length; i++)
                    if(questionsArr[i].id == id)
                        return i;
                return -1;
            }
        </script>

    </div>
	<div id="footer"><?= LoadFooter(); ?></div>
</div>
</body>
</html>