let root_getmessages = '';
(function(){
    var scripts = document.getElementsByTagName('script');
    var path = scripts[scripts.length-1].src.split('?')[0];
    var mydir = path.split('/').slice(0, -1).join('/')+'/';
    root_getmessages = mydir + '../';
})();

let prevMessages = 0;

function fetchMessageCountdata(){
    var xhr = new XMLHttpRequest();

    xhr.open('POST', root_getmessages + '/includes/messages/getmessagecount.inc.php', true);
    xhr.onload = function(e) {
        if(this.status == 200)
            handleMessageCountRequest(parseInt(e.currentTarget.responseText));
    }
    xhr.send();
}

function handleMessageCountRequest(messages){
    if(messages == -1) return;

    if(messages != prevMessages && messages > 0){
        document.getElementsByClassName('message-notification')[0].style.display = 'block';
		document.querySelectorAll('.pulseObject').forEach(el => el.style.display = 'block');
		
        if(messages == 1)
            document.getElementsByClassName('message-notification-text')[0].innerHTML = "Έχετε " + messages + "<br>νέο μήνυμα";
        else
            document.getElementsByClassName('message-notification-text')[0].innerHTML = "Έχετε " + messages + "<br>νέα μηνύματα";
    }
    else if(messages == 0){
        document.querySelectorAll('.pulseObject').forEach(el => el.style.display = 'none');
        document.getElementsByClassName('message-notification')[0].style.display = 'none';
    }

    prevMessages = messages;
    setTimeout(fetchMessageCountdata, 5000);
}

window.addEventListener("load", (e) => { fetchMessageCountdata(); });
