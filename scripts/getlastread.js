let root_getlastread = '';
(function(){
    var scripts = document.getElementsByTagName('script');
    var path = scripts[scripts.length-1].src.split('?')[0];
    var mydir = path.split('/').slice(0, -1).join('/')+'/';
    root_getlastread = mydir + '../';
})();

const ousername = (new URLSearchParams(window.location.search)).get('u');
let prevReadId = null;

function getPrevId(){
    var data = new FormData();
    data.append('u', ousername);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', root_getlastread + '/includes/messages/getlastread.inc.php', true);
    xhr.onload = function(e) {
        if(this.status == 200)
        handelPrevId(e.currentTarget.responseText);
    }
    xhr.send(data);
}


function handelPrevId(msgid){
    msgid = parseInt(msgid);

    let indicator = document.getElementById('latest-read');
    let indicator_mb = document.getElementById('latest-read-mb');

    if(msgid < 0){
        indicator.style.display = 'none';
        indicator_mb.style.display = 'none';
        setTimeout(getPrevId, 4000);
        return;
    }


    let msg = document.getElementById('msg-' + msgid);
    let msg_mb = document.getElementById('msg-mb-' + msgid);

    if(msg != null){
        msg.parentNode.insertBefore(indicator, msg.nextSibling);
        indicator.style.display = 'block';
        indicator_mb.style.display = 'block';
    }
    if(msg_mb != null){
        msg_mb.parentNode.insertBefore(indicator_mb, msg_mb.nextSibling);
        indicator.style.display = 'block';
        indicator_mb.style.display = 'block';
    }

    setTimeout(getPrevId, 4000);
}

window.addEventListener("load", (e) => { getPrevId(); });

