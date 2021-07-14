let root_getconvo = '';
(function(){
    var scripts = document.getElementsByTagName('script');
    var path = scripts[scripts.length-1].src.split('?')[0];
    var mydir = path.split('/').slice(0, -1).join('/')+'/';
    root_getconvo = mydir + '../';
})();

const username = (new URLSearchParams(window.location.search)).get('u');
let mostRecent = null;
let mostOldest = null;

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
function isImage(name){
    let n = name.split('.');
    let ext = n[n.length-1];
    const imgs = ['gif', 'jpg', 'jpeg', 'png', 'webp', 'svg', 'bmp'];
    return imgs.indexOf(ext) > -1;
}


function initMessages(){
    var data = new FormData();
    data.append('u', username);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', root_getconvo + '/includes/messages/getconvo.inc.php', true);
    xhr.onload = function(e) {
        if(this.status == 200)
            handleMessages(e.currentTarget.responseText);   
    }
    xhr.send(data);
}

function getRecents(){
    var data = new FormData();
    data.append('u', username);
    data.append('act', 'new');
    data.append('id', mostRecent);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', root_getconvo + '/includes/messages/getconvo.inc.php', true);
    xhr.onload = function(e) {
        if(this.status == 200)
            handleMessages(e.currentTarget.responseText);
    }
    xhr.send(data);
}


function handleMessages(convo){
    let msgContainer = document.getElementById("message-container");
    let loadOlder = document.getElementById("load-older");
    let msgContainer_mb = document.getElementById("message-container-mb");
    let loadOlder_mb = document.getElementById("load-older-mb");

    let data = JSON.parse(convo);

    msgContainer.insertBefore(loadOlder, msgContainer.childNodes[0]);
    msgContainer_mb.insertBefore(loadOlder_mb, msgContainer_mb.childNodes[0]);

    if(data.length < 1 && mostRecent == null) {
        //display a msg or whatever
        loadOlder.style.display = 'none';
        loadOlder_mb.style.display = 'none';
        setTimeout(initMessages, 1000);
        return;
    }
    else if(data.length < 1 && mostRecent != null){
        setTimeout(getRecents, 1000);
        return;
    }
    else {
        //clear the message?
    }   

    if(data.length < 10 && mostRecent == null){
        loadOlder.style.display = 'none';
        loadOlder_mb.style.display = 'none';
    }

    if(mostRecent == null){
        data.forEach(msg => {
            let parts = msg.split('|');
            if(parts.length != 6) return;
            let id = parseInt(parts[0]);
            let msgtype = parts[1];
            if(msgtype != 'text' && msgtype != 'file') return;
            let date = b64Decode(parts[3]);
            let sendByOther = parseInt(parts[4]);
            let seen = parseInt(parts[5]); //¯\_(ツ)_/¯

            if(mostRecent == null) mostRecent = id;
            else if(mostRecent < id) mostRecent = id;
            if(mostOldest == null) mostOldest = id;
            else if(mostOldest > id) mostOldest = id;

            let frm = 'him';
            if(sendByOther == 0) frm = 'me';

            let str = '';
            let strmb = '';
            let hashName = null;
            let img = false;

            if(msgtype == 'text'){
                let text = b64Decode(parts[2]);
                text = text.replace(/<br>/g, "\\n");
                text = escapeHtml(text);
                text = text.replace(/\\n/g, "<br>");

                str = '<div id="msg-'+id+'" class="'+frm+'" title="'+date+'">'+text+'</div>';
                strmb = '<div id="msg-mb-'+id+'" class="'+frm+'" title="'+date+'">'+text+'</div>';
            }
            else if(msgtype == 'file'){
                let fileDat = parts[2].split(',');

                if(fileDat.length != 2) return;

                hashName = b64Decode(fileDat[0]);
                let fileName = b64Decode(fileDat[1]);
                //img = isImage(fileName);

                if(img){
                    str = '<div id="msg-'+id+'" class="'+frm+' file" title="'+date+'"><img src="../file.php?id='+hashName+'" id="src-'+hashName+'"/><p class="subtitle">'+escapeHtml(fileName)+'</p></div>';
                    strmb = '<div id="msg-mb-'+id+'" class="'+frm+' file" title="'+date+'"><img src="../file.php?id='+hashName+'" id="src-mb-'+hashName+'"/><p class="subtitle">'+escapeHtml(fileName)+'</p></div>';
                }
                else{
                    let fileex = iconFromExtension(fileName);
                    str = '<div id="msg-'+id+'" class="'+frm+' file"title="' + date +'" onclick="window.location.href = \'../file.php?id=' + hashName + '\'"><img src="../resources/icons/' + fileex + '.png"/><p class="subtitle">' + escapeHtml(fileName) + '</p></div>';
                    strmb = '<div id="msg-mb-'+id+'" class="'+frm+' file"title="' + date +'" onclick="window.location.href = \'../file.php?id=' + hashName + '\'"><img src="../resources/icons/' + fileex + '.png"/><p class="subtitle">' + escapeHtml(fileName) + '</p></div>';
                }
            }
            let message = strToElement(str);
            msgContainer.insertBefore(message, msgContainer.childNodes[1]);
            let message_mb = strToElement(strmb);
            msgContainer_mb.insertBefore(message_mb, msgContainer_mb.childNodes[1]);

            if(hashName != null && img){
                var viewer = new ViewBigimg();
                var wrap = document.getElementById("msg-"+id);
                wrap.onclick = function (e) {
                    viewer.show(document.getElementById("src-"+hashName).src);
                    document.getElementById("header").style.display = "none";
                }

                var viewermb = new ViewBigimg();
                var wrapmb = document.getElementById("msg-mb-"+id);
                wrapmb.onclick = function (e) {
                    viewermb.show(document.getElementById("src-mb-"+hashName).src);
                    document.getElementById("header").style.display = "none";
                }
            }
        });
    }
    else
    {
        for(let i = 0; i < data.length; i++) {
            let parts = data[i].split('|');
            if(parts.length != 6) return;
            let id = parseInt(parts[0]);
            let msgtype = parts[1];
            if(msgtype != 'text' && msgtype != 'file') return;
            let date = b64Decode(parts[3]);
            let sendByOther = parseInt(parts[4]);
            let seen = parseInt(parts[5]); //¯\_(ツ)_/¯

            if(mostRecent == null) mostRecent = id;
            else if(mostRecent < id) mostRecent = id;
            if(mostOldest == null) mostOldest = id;
            else if(mostOldest > id) mostOldest = id;

            let frm = 'him';
            if(sendByOther == 0) frm = 'me';

            let str = '';
            let strmb = '';
            let hashName = null;
            let img = false;

            if(msgtype == 'text'){
                let text = b64Decode(parts[2]);
                text = text.replace(/<br>/g, "\\n");
                text = escapeHtml(text);
                text = text.replace(/\\n/g, "<br>");

                str = '<div id="msg-'+id+'" class="'+frm+'" title="'+date+'">'+text+'</div>';
                strmb = '<div id="msg-mb-'+id+'" class="'+frm+'" title="'+date+'">'+text+'</div>';
            }
            else if(msgtype == 'file'){
                let fileDat = parts[2].split(',');

                if(fileDat.length != 2) return;

                hashName = b64Decode(fileDat[0]);
                let fileName = b64Decode(fileDat[1]);
                //img = isImage(fileName);

                if(img){
                    str = '<div id="msg-'+id+'" class="'+frm+' file" title="'+date+'"><img src="../file.php?id='+hashName+'" id="src-'+hashName+'"/><p class="subtitle">'+escapeHtml(fileName)+'</p></div>';
                    strmb = '<div id="msg-mb-'+id+'" class="'+frm+' file" title="'+date+'"><img src="../file.php?id='+hashName+'" id="src-mb-'+hashName+'"/><p class="subtitle">'+escapeHtml(fileName)+'</p></div>';
                }
                else{
                    let fileex = iconFromExtension(fileName);
                    str = '<div id="msg-'+id+'" class="'+frm+' file"title="' + date +'" onclick="window.location.href = \'../file.php?id=' + hashName + '\'"><img src="../resources/icons/' + fileex + '.png"/><p class="subtitle">' + escapeHtml(fileName) + '</p></div>';
                    strmb = '<div id="msg-mb-'+id+'" class="'+frm+' file"title="' + date +'" onclick="window.location.href = \'../file.php?id=' + hashName + '\'"><img src="../resources/icons/' + fileex + '.png"/><p class="subtitle">' + escapeHtml(fileName) + '</p></div>';
                }
            }

            let message = strToElement(str);
            msgContainer.insertBefore(message, msgContainer.childNodes[msgContainer.childNodes.length - i]);
            let message_mb = strToElement(strmb);
            msgContainer_mb.insertBefore(message_mb, msgContainer_mb.childNodes[msgContainer_mb.childNodes.length - i]);

            if(hashName != null && img){
                var viewer = new ViewBigimg();
                var wrap = document.getElementById("msg-"+id);
                wrap.onclick = function (e) {
                    viewer.show(document.getElementById("src-"+hashName).src);
                    document.getElementById("header").style.display = "none";
                }

                var viewermb = new ViewBigimg();
                var wrapmb = document.getElementById("msg-mb-"+id);
                wrapmb.onclick = function (e) {
                    viewermb.show(document.getElementById("src-mb-"+hashName).src);
                    document.getElementById("header").style.display = "none";
                }
            }
        }
    }
    
    msgContainer.scrollTop = msgContainer.scrollHeight;
    msgContainer_mb.scrollTop = msgContainer_mb.scrollHeight;
    setTimeout(getRecents, 1000);
    var iv = document.getElementsByClassName("iv-close");
    for(var i = 0; i < iv.length; i++) 
        iv[i].onclick = function (e) {document.getElementById("header").style.display = "inline";}

    if (typeof MathJax !== 'undefined') {
        MathJax.typeset();
    }
}

function requestOlder(){
    var data = new FormData();
    data.append('u', username);
    data.append('act', 'old');
    data.append('id', mostOldest);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', root_getconvo + '/includes/messages/getconvo.inc.php', true);
    xhr.onload = function(e) {
        if(this.status == 200)
            handleOldMessages(e.currentTarget.responseText);
    }
    xhr.send(data);
}

function handleOldMessages(convo){
    let msgContainer = document.getElementById("message-container");
    let loadOlder = document.getElementById("load-older");
    let msgContainer_mb = document.getElementById("message-container-mb");
    let loadOlder_mb = document.getElementById("load-older-mb");

    let ogTop = msgContainer.scrollHeight;
    let ogTopmb = msgContainer_mb.scrollHeight;

    let data = JSON.parse(convo);

    if(data.length < 10){
        loadOlder.style.display = 'none';
        loadOlder_mb.style.display = 'none';
    }
        

    data.forEach(msg => {
        let parts = msg.split('|');
        if(parts.length != 6) return;
        let id = parseInt(parts[0]);
        let msgtype = parts[1];
        if(msgtype != 'text' && msgtype != 'file') return;
        let date = b64Decode(parts[3]);
        let sendByOther = parseInt(parts[4]);
        let seen = parseInt(parts[5]); //¯\_(ツ)_/¯

        if(mostRecent == null) mostRecent = id;
        else if(mostRecent < id) mostRecent = id;
        if(mostOldest == null) mostOldest = id;
        else if(mostOldest > id) mostOldest = id;

        let frm = 'him';
        if(sendByOther == 0) frm = 'me';

        let str = '';
        let strmb = '';
        let hashName = null;
        let img = false;

        if(msgtype == 'text'){
            let text = b64Decode(parts[2]);
            text = text.replace(/<br>/g, "\\n");
            text = escapeHtml(text);
            text = text.replace(/\\n/g, "<br>");

            str = '<div id="msg-'+id+'" class="'+frm+'" title="'+date+'">'+text+'</div>';
            strmb = '<div id="msg-mb-'+id+'" class="'+frm+'" title="'+date+'">'+text+'</div>';
        }
        else if(msgtype == 'file'){
            let fileDat = parts[2].split(',');

            if(fileDat.length != 2) return;

            hashName = b64Decode(fileDat[0]);
            let fileName = b64Decode(fileDat[1]);
            //img = isImage(fileName);

            if(img){
				str = '<div id="msg-'+id+'" class="'+frm+' file" title="'+date+'"><img src="../file.php?id='+hashName+'" id="src-'+hashName+'"/><p class="subtitle">'+escapeHtml(fileName)+'</p></div>';
				strmb = '<div id="msg-mb-'+id+'" class="'+frm+' file" title="'+date+'"><img src="../file.php?id='+hashName+'" id="src-mb-'+hashName+'"/><p class="subtitle">'+escapeHtml(fileName)+'</p></div>';
			}
			else{
				let fileex = iconFromExtension(fileName);
				str = '<div id="msg-'+id+'" class="'+frm+' file"title="' + date +'" onclick="window.location.href = \'../file.php?id=' + hashName + '\'"><img src="../resources/icons/' + fileex + '.png"/><p class="subtitle">' + escapeHtml(fileName) + '</p></div>';
				strmb = '<div id="msg-mb-'+id+'" class="'+frm+' file"title="' + date +'" onclick="window.location.href = \'../file.php?id=' + hashName + '\'"><img src="../resources/icons/' + fileex + '.png"/><p class="subtitle">' + escapeHtml(fileName) + '</p></div>';
			}
        }
        let message = strToElement(str);
        msgContainer.insertBefore(message, msgContainer.childNodes[1]);
        let message_mb = strToElement(strmb);
        msgContainer_mb.insertBefore(message_mb, msgContainer_mb.childNodes[1]);

        if(hashName != null && img){
            var viewer = new ViewBigimg();
            var wrap = document.getElementById("msg-"+id);
            wrap.onclick = function (e) {
                viewer.show(document.getElementById("src-"+hashName).src);
                document.getElementById("header").style.display = "none";
            }

            var viewermb = new ViewBigimg();
            var wrapmb = document.getElementById("msg-mb-"+id);
            wrapmb.onclick = function (e) {
                viewermb.show(document.getElementById("src-mb-"+hashName).src);
                document.getElementById("header").style.display = "none";
            }
        }
    });

    msgContainer.scrollTop = msgContainer.scrollHeight - ogTop;
    msgContainer_mb.scrollTop = msgContainer_mb.scrollHeight - ogTopmb;

    var iv = document.getElementsByClassName("iv-close");
    for(var i = 0; i < iv.length; i++) 
        iv[i].onclick = function (e) {document.getElementById("header").style.display = "inline";}
}

window.addEventListener("load", (e) => { initMessages(); });
