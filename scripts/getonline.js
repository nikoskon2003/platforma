let root_getonline = '';
(function(){
    var scripts = document.getElementsByTagName('script');
    var path = scripts[scripts.length-1].src.split('?')[0];
    var mydir = path.split('/').slice(0, -1).join('/')+'/';
    root_getonline = mydir + '../';
})();

function fetchOnlineData(){
    var xhr = new XMLHttpRequest();

    xhr.open('POST', root_getonline + '/includes/messages/getonline.inc.php', true);
    xhr.onload = function(e) {
        if(this.status == 200)
            handleOnlineData(e.currentTarget.responseText);
    }
    xhr.send();
}

let prevOnline = [];
function handleOnlineData(raw){
    let online = JSON.parse(raw);

    for(let i = 0; i < online.length; i++){
        if(!prevOnline.includes(online[i])){
            let username = encodeURI(online[i]);
            let elements = document.getElementsByClassName('online-status-' + username);
            for(let j = 0; j < elements.length; j++){
                elements[j].style.display = 'block';
            }
        }
    }
    for(let i = 0; i < prevOnline.length; i++){
        if(!online.includes(prevOnline[i])){
            let username = encodeURI(prevOnline[i]);
            let elements = document.getElementsByClassName('online-status-' + username);
            for(let j = 0; j < elements.length; j++){
                elements[j].style.display = "none";
            }
        }
    }

    prevOnline = online;
    setTimeout(fetchOnlineData, 4000);
}

window.addEventListener("load", (e) => { fetchOnlineData(); });
