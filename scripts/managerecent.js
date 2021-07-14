let root_managerecent = '';
(function(){
    var scripts = document.getElementsByTagName('script');
    var path = scripts[scripts.length-1].src.split('?')[0];
    var mydir = path.split('/').slice(0, -1).join('/')+'/';
    root_managerecent = mydir + '../';
})();

function fetchRecentsData(){
    var xhr = new XMLHttpRequest();

    xhr.open('POST', root_getmessages + '/includes/messages/getrecent.inc.php', true);
    xhr.onload = function(e) {
        if(this.status == 200)
            handleRecentsRequest(e.currentTarget.responseText);
    }
    xhr.send();
}

function handleRecentsRequest(raw){
    
    let data = JSON.parse(raw);

    let users = [];

    for(let i = 0; i < data.length; i++){
        let parts = data[i].split('|');

        if(parts.length != 4) continue;

        let username = b64Decode(parts[0]);
        let name = b64Decode(parts[1]);
        let mcou = parseInt(parts[2]);
        let date = b64Decode(parts[3]);
        
        users.push([username, name, mcou, date]);        
    }

    manageUsers(users);

    setTimeout(fetchRecentsData, 5000);
}
function manageUsers(users){
    let parent = document.getElementById('recent-users');
    let mbparent = document.getElementById('recent-users-mb');


    if(users.length == 0){
        parent.innerHTML = "<div id='no-recent-msgs' class='sub-title'>Δεν υπάρχουν πρόσφατα</div>";
        mbparent.innerHTML = "<div id='no-recent-msgs-mb' class='sub-title'>Δεν υπάρχουν πρόσφατα</div>";
    }
    else
    {
        let b = document.getElementById('no-recent-msgs');
        if(b != null)b.remove();
        b = document.getElementById('no-recent-msgs-mb');
        if(b != null)b.remove();
    }

    for(let i = 0; i < users.length; i++){

        let username = users[i][0];
        let name = users[i][1];
        let messageCount = users[i][2];
        let date = users[i][3];

        let bol = '';
        let msgtxt = '&nbsp;';
        if(messageCount > 0){
            bol = 'style="font-weight: bold;"';
            if(messageCount == 1) msgtxt = '1 Νέο Μήνυμα';
            else msgtxt = messageCount + ' Νέα Μηνύματα';
        }

        let datetxt = date.split(' ')[0]; //BETTER

        var today = new Date();
        var dd = String(today.getDate()).padStart(2, '0');
        var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
        var yyyy = today.getFullYear();

        today = yyyy + '-' + mm + '-' + dd;
        
        if(today == datetxt) datetxt = date.split(' ')[1];
        else datetxt = datetxt.split('-')[2] + '/' + datetxt.split('-')[1] + '/' + datetxt.split('-')[0];

        let dis = '';
        if(prevOnline.includes(username)) dis = 'style="display:block"';

        let innerHtml = 
        '<div class="user-online"><div class="online-dot online-status-' + encodeURI(username) + '" ' + dis + '></div></div><div class="user-details" ' + bol + '>' +
        '<div class="user-name">' + name + '</div>' + 
        '<div class="user-messages">' + msgtxt + '</div> ' +
        '</div><div class="user-date">' + datetxt + '</div>';
        let mbinnerHtml = 
        '<div class="user-online"><div class="online-dot online-status-mb-' + encodeURI(username) + '" ' + dis + '></div></div><div class="user-details" ' + bol + '>' +
        '<div class="user-name">' + name + '</div>' + 
        '<div class="user-messages">' + msgtxt + '</div> ' +
        '</div><div class="user-date">' + datetxt + '</div>';

        let element = document.getElementById('rec-user-' + encodeURI(username));
        let mbelement = document.getElementById('rec-user-mb-' + encodeURI(username));

        if(element == null){
            let htmldata = '<div id="rec-user-' + encodeURI(username) + '" class="user" onclick="window.location.href = \'./messages.php?u=' + encodeURI(username) + '\';">' + innerHtml + '</div>';
            let newElement = strToElement(htmldata);

            parent.insertBefore(newElement, parent.children[i]);
        }
        else
        {
            element.innerHTML = innerHtml;
            parent.insertBefore(element, parent.children[i]);
        }

        if(mbelement == null){
            let htmldata = '<div id="rec-user-mb-' + encodeURI(username) + '" class="user" onclick="window.location.href = \'./messages.php?u=' + encodeURI(username) + '\';">' + mbinnerHtml + '</div>';
            let newElement = strToElement(htmldata);

            mbparent.insertBefore(newElement, mbparent.children[i]);
        }
        else
        {
            mbelement.innerHTML = mbinnerHtml;
            mbparent.insertBefore(mbelement, mbparent.children[i]);
        }

        element = document.getElementById('all-user-' + encodeURI(username));
        if(element != null) element.innerHTML = innerHtml;

        element = document.getElementById('all-user-mb-' + encodeURI(username));
        if(element != null) element.innerHTML = mbinnerHtml;

    }
}


window.addEventListener("load", (e) => { fetchRecentsData(); });

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
