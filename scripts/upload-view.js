const monthNames = ["Ιανουάριος","Φεβρουάριος","Μάρτιος","Απρίλιος","Μάιος","Ιούνιος","Ιούλιος","Αύγουστος","Σεπτέμβριος","Οκτώβριος","Νοέμβριος","Δεκέμβριος"];

let root_upload_view = '';
(function(){
    var scripts = document.getElementsByTagName('script');
    var path = scripts[scripts.length-1].src.split('?')[0];
    var mydir = path.split('/').slice(0, -1).join('/')+'/';
    root_upload_view = mydir + '../';
})();

function escapeHtml(unsafe) {
    return unsafe
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
function b64Decode(str) {
    return decodeURIComponent(atob(str).split('').map(function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));
}
function strToElement(str) {
    var tmp = document.createElement('div');
    tmp.innerHTML = str.trim();
    return tmp.firstChild;
}

let formFileInput = null;
let formFileInputMb = null;

let enableFileUpload = false;
let fileToUpload = null;
function initFileSystem(outInput, outInputMb){

    formFileInput = outInput;
    formFileInputMb = outInputMb;

    enableFileUpload = true;

    let fileInput = document.getElementById('file-select-input');
    let fileInputButton = document.getElementById('file-select-input-button');
    let smallUploadUI = document.getElementById('bottom-upload-area');
    let uploadPercentageNum = document.getElementById('upload-load-percentage');
    let uploadPercentageInd = document.getElementById('upload-load-bar-ind');
    //let uploadStartButton = document.getElementById('upload-file-confirm');


    let fileInputMb = document.getElementById('file-select-input-mb');
    let fileInputButtonMb = document.getElementById('file-select-input-button-mb');
    let smallUploadUIMb = document.getElementById('bottom-upload-area-mb');
    let uploadPercentageNumMb = document.getElementById('upload-load-percentage-mb');
    let uploadPercentageIndMb = document.getElementById('upload-load-bar-ind-mb');
    //let uploadStartButtonMb = document.getElementById('upload-file-confirm-mb');


    fileInputButton.onclick = () => {if(enableFileUpload) fileInput.click()};
    fileInput.onchange = (e) => {
        let files = e.target.files;
        files = Array.prototype.slice.call(files);
        if(files.length == 0){
            fileToUpload = null;
            smallUploadUI.style.display = 'none';
            smallUploadUIMb.style.display = 'none';
        }
        else {
            fileToUpload = files[0];
            smallUploadUI.style.display = 'inline-block';
            uploadPercentageNum.innerHTML = '-';
            uploadPercentageInd.style.width = '0';

            smallUploadUIMb.style.display = 'inline-block';
            uploadPercentageNumMb.innerHTML = '-';
            uploadPercentageIndMb.style.width = '0';
			
			if(enableFileUpload && fileToUpload != null){
				uploadFile(fileToUpload, uploadPercentageNum, uploadPercentageInd, uploadPercentageNumMb, uploadPercentageIndMb);
				enableFileUpload = false;
			}
        }
    }

    /*uploadStartButton.onclick = () => {
        if(enableFileUpload && fileToUpload != null){
            uploadFile(fileToUpload, uploadPercentageNum, uploadPercentageInd, uploadPercentageNumMb, uploadPercentageIndMb);
            enableFileUpload = false;
        }
    };*/
    

    fileInputButtonMb.onclick = () => {if(enableFileUpload) fileInputMb.click()};
    fileInputMb.onchange = (e) => {
        let files = e.target.files;
        files = Array.prototype.slice.call(files);
        if(files.length == 0){
            fileToUpload = null;
            smallUploadUI.style.display = 'none';
            smallUploadUIMb.style.display = 'none';
        }
        else {
            fileToUpload = files[0];
            smallUploadUI.style.display = 'inline-block';
            uploadPercentageNum.innerHTML = '-';
            uploadPercentageInd.style.width = '0';

            smallUploadUIMb.style.display = 'inline-block';
            uploadPercentageNumMb.innerHTML = '-';
            uploadPercentageIndMb.style.width = '0';
			
			if(enableFileUpload && fileToUpload != null){
				uploadFile(fileToUpload, uploadPercentageNum, uploadPercentageInd, uploadPercentageNumMb, uploadPercentageIndMb);
				enableFileUpload = false;
			}
        }
    }

    /*uploadStartButtonMb.onclick = () => {
        if(enableFileUpload && fileToUpload != null){
            uploadFile(fileToUpload, uploadPercentageNum, uploadPercentageInd, uploadPercentageNumMb, uploadPercentageIndMb);
            enableFileUpload = false;
        }
    };*/


    var xhr = new XMLHttpRequest();
    xhr.open('POST', root_upload_view + 'includes/upload/getfiles.inc.php', true);
    xhr.onload = function(e) {
        let files = e.currentTarget.responseText;
        files = JSON.parse(files);
        parseFiles(files);
    }
    xhr.send();

}

function uploadFile(file, indNum = null, indBar = null, indNumMb = null, indBarMb = null){
    let data = new FormData();
    data.append('f[]', file);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', root_upload_view + 'includes/upload/upload.inc.php', true);

    xhr.onload = function(e) {
        enableFileUpload = true;

        fileToUpload = null;

        let fileDat = e.currentTarget.responseText;
        fileDat = JSON.parse(fileDat);
        parseNewFile(fileDat[0]);
    }
    xhr.upload.addEventListener("progress", function(e) {
        if (e.lengthComputable) {
            var percentage = Math.round((e.loaded / e.total) * 1000)/10;
            if(indNum != null) indNum.innerHTML = percentage + '%';
            if(indBar != null) indBar.style.width = percentage + '%';
            if(indNumMb != null) indNumMb.innerHTML = percentage + '%';
            if(indBarMb != null) indBarMb.style.width = percentage + '%';
        }
    }, false);

    xhr.onerror= function(e) {
        enableFileUpload = true;
    };

    xhr.send(data);
}

let foldersArray = [];
let dispFilesArray = [];
function parseFiles(files){
    if(!(files instanceof Array)){
        console.warn('Bad file format!');
        return;
    }

    let filesystem = document.getElementById('filesystem');
    let home = document.getElementById('fs-home');

    let filesystemMb = document.getElementById('filesystem-mb');
    let homeMb = document.getElementById('fs-home-mb');


    for(let i = 0; i < files.length; i++){
        let uid = files[i].uid;
        let name = escapeHtml(b64Decode(files[i].name));
        let date = b64Decode(files[i].date);
        let fav = files[i].fav;

        let f = false;
        for(let j = 0; j < dispFilesArray.length; j++)
            if(dispFilesArray[j].uid == uid){
                f = true;
                break;
            }
        if(f) continue;

        dispFilesArray.push(files[i]);

        let year = parseInt(date.split('-')[0]);
        let month = parseInt(date.split('-')[1]);
        let day = parseInt(date.split(' ')[0].split('-')[2]);
        
        if(foldersArray.indexOf(year + '-' + month) < 0){
            foldersArray.push(year + '-' + month);

            let ht = '<div class="select-file-cont" onclick="backToRoot()"><img src="' + root_upload_view + 'resources/icons/back-folder.png"/><p class="back-folder-text">Πίσω</p></div>';
            let div = document.createElement('div');
            div.id = 'fs-' + year + '-' + month;
            div.innerHTML = ht;
            div.style.display = 'none';
            filesystem.insertBefore(div, null);
            let homeFolder = strToElement('<div class="select-file-cont" onclick="openFolder(\'' + year + '-' + month + '\')"><img src="' + root_upload_view + 'resources/icons/folder.png"/><p>' + monthNames[month-1] + '<br>' + year + '</p></div>');
            home.insertBefore(homeFolder, null);


            let divMb = document.createElement('div');
            divMb.id = 'fs-' + year + '-' + month + '-mb';
            divMb.innerHTML = ht;
            divMb.style.display = 'none';
            filesystemMb.insertBefore(divMb, null);
            let homeFolderMb = strToElement('<div class="select-file-cont" onclick="openFolder(\'' + year + '-' + month + '\')"><img src="' + root_upload_view + 'resources/icons/folder.png"/><p>' + monthNames[month-1] + '<br>' + year + '</p></div>');
            homeMb.insertBefore(homeFolderMb, null);
        }

        let htm = '<div class="select-file-cont" uid="' + uid + '" title="' + name + '" onclick="openFile(\'' + uid + '\');"><img class="select-file-image" src="' + root_upload_view + 'resources/icons/' + iconFromExtension(name) + '.png"/><p class="file-text">' + name + '</p><p class="file-date">' + day + '/' + month + '/' + year + '</p></div>';
        let file = strToElement(htm);
        document.getElementById('fs-' + year + '-' + month).insertBefore(file, null);

        let fileMb = strToElement(htm);
        document.getElementById('fs-' + year + '-' + month + '-mb').insertBefore(fileMb, null);


        if(fav == 1){
            let fileF = strToElement(htm);
            document.getElementById('fs-favs').insertBefore(fileF, null);

            let fileFMb = strToElement(htm);
            document.getElementById('fs-favs-mb').insertBefore(fileFMb, null);
        }
    }
}
function parseNewFile(file){
    let filesystem = document.getElementById('filesystem');
    let home = document.getElementById('fs-home');

    let filesystemMb = document.getElementById('filesystem-mb');
    let homeMb = document.getElementById('fs-home-mb');

    let uid = file.uid;
    let name = escapeHtml(b64Decode(file.name));
    let date = b64Decode(file.date);

    for(let j = 0; j < dispFilesArray.length; j++)
        if(dispFilesArray[j].uid == uid) return;

    dispFilesArray.push(file);

    let year = parseInt(date.split('-')[0]);
    let month = parseInt(date.split('-')[1]);
    let day = parseInt(date.split(' ')[0].split('-')[2]);
    
    if(foldersArray.indexOf(year + '-' + month) < 0){
        foldersArray.push(year + '-' + month);

        let ht = '<div class="select-file-cont" onclick="backToRoot()"><img src="' + root_upload_view + 'resources/icons/back-folder.png"/><p class="back-folder-text">Πίσω</p></div>';

        let div = document.createElement('div');
        div.id = 'fs-' + year + '-' + month;
        div.innerHTML = ht;
        div.style.display = 'none';
        filesystem.insertBefore(div, null);
        let homeFolder = strToElement('<div class="select-file-cont" onclick="openFolder(\'' + year + '-' + month + '\')"><img src="' + root_upload_view + 'resources/icons/folder.png"/><p>' + monthNames[month-1] + '<br>' + year + '</p></div>');
        home.insertBefore(homeFolder, null);


        let divMb = document.createElement('div');
        divMb.id = 'fs-' + year + '-' + month + '-mb';
        divMb.innerHTML = ht;
        divMb.style.display = 'none';
        filesystemMb.insertBefore(divMb, null);
        let homeFolderMb = strToElement('<div class="select-file-cont" onclick="openFolder(\'' + year + '-' + month + '\')"><img src="' + root_upload_view + 'resources/icons/folder.png"/><p>' + monthNames[month-1] + '<br>' + year + '</p></div>');
        homeMb.insertBefore(homeFolderMb, null);
    }

    let htm = '<div class="select-file-cont" uid="' + uid + '" title="' + name + '" onclick="openFile(\'' + uid + '\');"><img class="select-file-image" src="' + root_upload_view + 'resources/icons/' + iconFromExtension(name) + '.png"/><p class="file-text">' + name + '</p><p class="file-date">' + day + '/' + month + '/' + year + '</p></div>';
    let fileM = strToElement(htm);
    let p = document.getElementById('fs-' + year + '-' + month);
    p.insertBefore(fileM, p.childNodes[1]);
    let fileH = strToElement(htm);
    home.insertBefore(fileH, null);


    let fileMMb = strToElement(htm);
    let pMb = document.getElementById('fs-' + year + '-' + month + '-mb');
    pMb.insertBefore(fileMMb, pMb.childNodes[1]);
    let fileHMb = strToElement(htm);
    homeMb.insertBefore(fileHMb, null);
	
	
    selectedFilesArray.push(uid);
    let ht = '<div class="selected-file" title="' + name + '" onclick="removeFile(\'' + uid + '\')"><p>' + name  + '</p><img src="' + root_upload_view + 'resources/cancel.png" /></div>';
    let el = strToElement(ht);
    document.getElementById('selected-files-list').insertBefore(el, null);
    let elMb = strToElement(ht);
    document.getElementById('selected-files-list-mb').insertBefore(elMb, null);
}

let lastOpenFolder = null;
function openFolder(name){
    let d = document.getElementById('fs-' + name);
    let dMb = document.getElementById('fs-' + name + '-mb');
    if(d == null || dMb == null) return;

    document.getElementById('fs-home').style.display = 'none';
    d.style.display = 'unset';

    document.getElementById('fs-home-mb').style.display = 'none';
    dMb.style.display = 'unset';

    lastOpenFolder = name;
}
function backToRoot(){
    if(lastOpenFolder == null) return;

    let d = document.getElementById('fs-' + lastOpenFolder);
    let dMb = document.getElementById('fs-' + lastOpenFolder + '-mb');
    if(d == null || dMb == null) return;

    d.style.display = 'none';
    document.getElementById('fs-home').style.display = 'unset';

    dMb.style.display = 'none';
    document.getElementById('fs-home-mb').style.display = 'unset';

    lastOpenFolder = null;
}

let lastOpenFile = null;
function openFile(uid){
    let file = null;
    for(let i = 0; i < dispFilesArray.length; i++){
        if(dispFilesArray[i].uid == uid){
            file = dispFilesArray[i];
            break;
        }
    }
    if(file == null) return;

    document.getElementById('filesystem').style.display = 'none';

    document.getElementById('file-info-name').innerHTML = escapeHtml(b64Decode(file.name));
    let date = b64Decode(file.date).split(' ');
    date = date[0].split('-')[2] + '/' + date[0].split('-')[1] + '/' + date[0].split('-')[0] + ' ' + date[1];
    document.getElementById('file-info-date').innerHTML = date;
    document.getElementById('file-info-size').innerHTML = file.size;

    document.getElementById('file-info').style.display = 'block';
    lastOpenFile = file;

    if(file.fav == 1) document.getElementById('fav-file-button').innerHTML = 'Αφαίρεση από τα Αγαπημένα';
    else document.getElementById('fav-file-button').innerHTML = 'Προσθήκη στα Αγαπημένα';
    document.getElementById('download-file').href = root_upload_view + 'file.php?id=' + file.uid;



    document.getElementById('filesystem-mb').style.display = 'none';

    document.getElementById('file-info-name-mb').innerHTML = escapeHtml(b64Decode(file.name));
    document.getElementById('file-info-date-mb').innerHTML = date;
    document.getElementById('file-info-size-mb').innerHTML = file.size;

    document.getElementById('file-info-mb').style.display = 'block';
    lastOpenFile = file;

    if(file.fav == 1) document.getElementById('fav-file-button-mb').innerHTML = 'Αφαίρεση από τα Αγαπημένα';
    else document.getElementById('fav-file-button-mb').innerHTML = 'Προσθήκη στα Αγαπημένα';
    document.getElementById('download-file-mb').href = root_upload_view + 'file.php?id=' + file.uid;
}
function closeFile(){
    document.getElementById('file-info').style.display = 'none';
    document.getElementById('filesystem').style.display = 'block';

    document.getElementById('file-info-mb').style.display = 'none';
    document.getElementById('filesystem-mb').style.display = 'block';
    lastOpenFile = null;
}

let selectedFilesArray = [];
function selectFile(){
    if(lastOpenFile == null) return;
    if(selectedFilesArray.indexOf(lastOpenFile.uid) > -1) return;
    selectedFilesArray.push(lastOpenFile.uid);

    let ht = '<div class="selected-file" title="' + escapeHtml(b64Decode(lastOpenFile.name)) + '" onclick="removeFile(\'' + lastOpenFile.uid + '\')"><p>' + escapeHtml(b64Decode(lastOpenFile.name))  + '</p><img src="' + root_upload_view + 'resources/cancel.png" /></div>';
    let el = strToElement(ht);
    document.getElementById('selected-files-list').insertBefore(el, null);

    let elMb = strToElement(ht);
    document.getElementById('selected-files-list-mb').insertBefore(elMb, null);
}
function removeFile(uid){
    let indx = selectedFilesArray.indexOf(uid);
    if(indx < 0) return;

    selectedFilesArray.splice(indx, 1);
    document.getElementById('selected-files-list').childNodes[indx + 1].remove();
    document.getElementById('selected-files-list-mb').childNodes[indx + 1].remove();
}

function toggleFav(){
    if(!confirm('Είστε σίγουροι;')) return;
    let uid = lastOpenFile.uid;
    let action = 'add';
    if(lastOpenFile.fav == 1) action = 'remove';
    let data = new FormData();
    data.append('uid', uid);
    data.append('action', action);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', root_upload_view + 'includes/upload/setfav.inc.php', true);
    xhr.onload = function(e) {
        let res = e.currentTarget.responseText;

        if(res == 'ok'){

            lastOpenFile.fav = (action == 'add') ? 1 : 0;
            for(let i = 0; i < dispFilesArray.length; i++)
                if(dispFilesArray[i].uid == uid){
                    dispFilesArray[i].fav = (action == 'add') ? 1 : 0;
                    break;
                }

            if(action == 'remove'){
                let elms = document.querySelectorAll('[uid="' + uid + '"]');
                for(let i = 0; i < elms.length; i++){
                    if(elms[i].parentElement.id == 'fs-favs' || elms[i].parentElement.id == 'fs-favs-mb'){
                        elms[i].remove();
                    }
                }
                document.getElementById('fav-file-button').innerHTML = 'Προσθήκη στα Αγαπημένα';
                document.getElementById('fav-file-button-mb').innerHTML = 'Προσθήκη στα Αγαπημένα';
            }
            else {

                let name = escapeHtml(b64Decode(lastOpenFile.name));
                let date = b64Decode(lastOpenFile.date);
                let year = parseInt(date.split('-')[0]);
                let month = parseInt(date.split('-')[1]);
                let day = parseInt(date.split(' ')[0].split('-')[2]);

                let htm = '<div class="select-file-cont" uid="' + uid + '" title="' + name + '" onclick="openFile(\'' + uid + '\');"><img class="select-file-image" src="' + root_upload_view + 'resources/icons/' + iconFromExtension(name) + '.png"/><p class="file-text">' + name + '</p><p class="file-date">' + day + '/' + month + '/' + year + '</p></div>';
                let fileF = strToElement(htm);
                document.getElementById('fs-favs').insertBefore(fileF, null);

                let fileFMb = strToElement(htm);
                document.getElementById('fs-favs-mb').insertBefore(fileFMb, null);

                document.getElementById('fav-file-button').innerHTML = 'Αφαίρεση από τα Αγαπημένα';
                document.getElementById('fav-file-button-mb').innerHTML = 'Αφαίρεση από τα Αγαπημένα';
            }
        }
    }
    xhr.send(data);
}

function deleteFile(){
    if(!confirm('Είστε σίγουροι;')) return;
    let uid = lastOpenFile.uid;
    let data = new FormData();
    data.append('uid', uid);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', root_upload_view + 'includes/upload/delete.inc.php', true);
    xhr.onload = function(e) {
        let res = e.currentTarget.responseText;

        if(res == 'ok'){

            for(let i = 0; i < dispFilesArray.length; i++)
                if(dispFilesArray[i].uid == uid){
                    dispFilesArray.splice(i, 1);
                    break;
                }

            let elms = document.querySelectorAll('[uid="' + uid + '"]');
            for(let i = 0; i < elms.length; i++)
                elms[i].remove();

            removeFile(uid);
            closeFile();
        }
    }
    xhr.send(data);
}

function openFileSelectView(){
    document.getElementById('select-files-container').style.display = 'unset';
    document.getElementById('select-files-container-mb').style.display = 'unset';
}
function closeFileSelectView(){
    document.getElementById('select-files-container').style.display = 'none';
    document.getElementById('select-files-container-mb').style.display = 'none';
    formFileInput.value = selectedFilesArray.join(',');
    formFileInputMb.value = selectedFilesArray.join(',');

    let names = [];
    for(let i = 0; i < selectedFilesArray.length; i++)
        for(let j = 0; j < dispFilesArray.length; j++)
            if(selectedFilesArray[i] == dispFilesArray[j].uid) {
                names.push(b64Decode(dispFilesArray[j].name));
                break;
            }

    document.querySelectorAll('[display-files="true"]').forEach(e => {
        while(e.childElementCount > 1) e.removeChild(e.lastChild);
        names.forEach(n => {
            let elemstr = '<div class="template-post-file" target="_blank" title="' + escapeHtml(n) + '"><img src="../../resources/icons/' + iconFromExtension(n)  + '.png"/><p>' + escapeHtml(n) + '</p></div>';
            e.insertBefore(strToElement(elemstr), null);
        });
    });
}