var socket;
var waiting = false;
var gamestarted = false;
var meplaying = true;
var stats = {};
let ltstart = 0;
let roomgame = false;
let exists = false;
try {
    socket = io.connect(serverloc, {path: '/battleshipws/socket.io'});
}
catch(err){
    document.querySelectorAll('.status-title').forEach(el => el.style.display = 'inline-block');
    document.querySelectorAll('.status-title').forEach(el => el.innerHTML = 'Σφάλμα σύνδεσης');
    document.querySelectorAll('.own-grid-hider').forEach(el => el.style.display = 'block');
    document.querySelectorAll('.gotohome').forEach(el => el.style.display = 'inline-block');
    document.querySelectorAll('.placed-ships').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.control-buttons').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.exitqueuebutton').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.gotostartbutton').forEach(el => el.style.display = 'none');
    document.getElementById('messages-cont').style.display = 'block';
    throw err;
}

socket.on('connect', function() {

    socket.emit('auth', {username: authuname, cred: authcred, name: authencname});

    socket.on('authed', function (data) {          
        if(data.status != 'ok'){
            document.querySelectorAll('.status-title').forEach(el => el.innerHTML = 'Αδυναμία σύνδεσης.<br>Προσπαθήστε ξανά σε λίγα λεπτά');
            document.querySelectorAll('.gotohome').forEach(el => el.style.display = 'inline-block');
            exists = (data.status == 'exists');
            socket.disconnect();
        }
        else {
            document.querySelectorAll('.own-grid-hider').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.status-title').forEach(el => el.style.display = 'inline-block');
            document.querySelectorAll('.status-title').forEach(el => el.innerHTML = 'Επεξεργασία πλοίων');
            document.querySelectorAll('.gotohome').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.placed-ships').forEach(el => el.style.display = 'block');
            document.querySelectorAll('.control-buttons').forEach(el => el.style.display = 'block');
            document.querySelectorAll('.exitqueuebutton').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.gotostartbutton').forEach(el => el.style.display = 'none');
            document.getElementById('messages-cont').style.display = 'block';
            document.querySelectorAll('.timeleft').forEach(el => el.innerHTML = '');

            document.querySelector("input[name=\"sendname\"]").checked = data.sendname;
            document.querySelector("input[name=\"savegrid\"]").checked = data.savegrid;

            stats.wins = data.wins;
            stats.losses = data.losses;
            
            if(stats.wins == 1 && stats.losses == 1) document.querySelectorAll('.own-stats').forEach(el => el.innerHTML = 'Τα στατιστικά μου: 1 Νίκη, 1 Ήττα');
            else if(stats.wins == 1) document.querySelectorAll('.own-stats').forEach(el => el.innerHTML = 'Τα στατιστικά μου: 1 Νίκη, ' + stats.losses + ' Ήττες');
            else if(stats.losses == 1) document.querySelectorAll('.own-stats').forEach(el => el.innerHTML = 'Τα στατιστικά μου: ' + stats.wins + ' Νίκες, 1 Ήττα');
            else document.querySelectorAll('.own-stats').forEach(el => el.innerHTML = 'Τα στατιστικά μου: ' + stats.wins + ' Νίκες, ' + stats.losses + ' Ήττες');

            if(data.grid != null) placeGridFromBinary(data.grid);

            window.onbeforeunload = function() { return ""; }
        }
        resetGrids();
    });

    socket.on('queue', function (data) {
        if(data.status == "waiting"){
            document.querySelectorAll('.own-grid-hider').forEach(el => el.style.display = 'block');
            document.querySelectorAll('.placed-ships').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.control-buttons').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.exitqueuebutton').forEach(el => el.style.display = 'block');
            document.querySelectorAll('.exitqueuebutton').forEach(el => el.disabled = false);
            waiting = true;
            animWaiting(3);
        }
        else if(data.status == "left" && waiting){
            waiting = false;
            document.querySelectorAll('.own-grid-hider').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.status-title').forEach(el => el.innerHTML = 'Επεξεργασία πλοίων');
            document.querySelectorAll('.placed-ships').forEach(el => el.style.display = 'block');
            document.querySelectorAll('.control-buttons').forEach(el => el.style.display = 'block');
            document.querySelectorAll('.exitqueuebutton').forEach(el => el.style.display = 'none');
        }
    });

    socket.on('otherLeft', function (data) {
        if(gamestarted){
            document.querySelectorAll('.own-grid-hider').forEach(el => el.style.display = 'block');
            document.querySelectorAll('.placed-ships').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.control-buttons').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.exitqueuebutton').forEach(el => el.style.display = 'none');
            document.getElementById('messages-cont').style.display = 'block';
            document.querySelectorAll('.status-title').forEach(el => el.style.display = 'inline-block');
            document.querySelectorAll('.status-title').forEach(el => el.innerHTML = 'Νίκη!');
            document.querySelectorAll('.gotostartbutton').forEach(el => el.style.display = 'block');
            document.querySelectorAll('.timeleft').forEach(el => el.innerHTML = '');

            gamestarted = false;
            if(!roomgame){
                stats.wins++;
                document.querySelectorAll('.own-stats').forEach(el => el.innerHTML = 'Τα στατιστικά μου: ' + stats.wins + ' Νίκες, ' + stats.losses + ' Ήττες');
            }
            roomgame = false;
        }
    });

    socket.on('startGame', function (data) {
        waiting = false;
        gamestarted = true;
        document.querySelectorAll('.own-grid-hider').forEach(el => el.style.display = 'block');
        document.querySelectorAll('.status-title').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.placed-ships').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.control-buttons').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.exitqueuebutton').forEach(el => el.style.display = 'none');

        document.querySelectorAll('.opponent-name').forEach(el => el.innerHTML = 'Αντίπαλος: ' + data.other);

        if(data.side == 0){
            meplaying = true;
            document.querySelectorAll('.own-grid-hider').forEach(el => el.style.opacity = '1');
            document.getElementById('messages-cont').style.display = 'none';
        }
        else {
            meplaying = false;
            document.querySelectorAll('.own-grid-hider').forEach(el => el.style.opacity = '0');
            document.getElementById('messages-cont').style.display = 'block';
        }

        ltstart = Date.now();
        timeLeftAnim(ltstart);
    });

    socket.on('attack', function(data) {
        if(!data.side){
            awknowledgeAttack(data.tile);
            if(data.hit){
                meplaying = true;
                document.querySelectorAll('.own-grid-hider').forEach(el => el.style.opacity = '1');
                document.getElementById('messages-cont').style.display = 'none';

                document.getElementById('opp-grid').childNodes[data.tile].className = 'tile hit';
            }
            else {
                meplaying = false;
                document.querySelectorAll('.own-grid-hider').forEach(el => el.style.opacity = '0');
                document.getElementById('messages-cont').style.display = 'block';

                document.getElementById('opp-grid').childNodes[data.tile].className = 'tile miss';
            }
            if(data.sunk){
                for(let i = 0; i < data.sunkarr.length; i++){
                    sinkTile(data.sunkarr[i], document.getElementById('opp-grid'), true);
                }
            }
        }
        else {
            if(data.hit){
                meplaying = false;
                document.querySelectorAll('.own-grid-hider').forEach(el => el.style.opacity = '0');
                document.getElementById('messages-cont').style.display = 'block';

                document.getElementById('own-grid').childNodes[data.tile].className = 'tile hit';
            }
            else {
                meplaying = true;
                document.querySelectorAll('.own-grid-hider').forEach(el => el.style.opacity = '1');
                document.getElementById('messages-cont').style.display = 'none';

                document.getElementById('own-grid').childNodes[data.tile].className = 'tile miss';
            }
            if(data.sunk){
                for(let i = 0; i < data.sunkarr.length; i++){
                    sinkTile(data.sunkarr[i], document.getElementById('own-grid'), false);
                }
            }
        }

        ltstart = Date.now();
        timeLeftAnim(ltstart);
    });

    socket.on('attackstat', function (data) {
        if(data == 'again'){
            meplaying = true;
            document.querySelectorAll('.own-grid-hider').forEach(el => el.style.opacity = '1');
            document.getElementById('messages-cont').style.display = 'none';
        }
        else if(data == 'other'){
            meplaying = false;
            document.querySelectorAll('.own-grid-hider').forEach(el => el.style.opacity = '0');
            document.getElementById('messages-cont').style.display = 'block';
        }
    });

    socket.on('gamefinish', function (data) {
        if(gamestarted){
            gamestarted = false;

            document.querySelectorAll('.own-grid-hider').forEach(el => el.style.display = 'block');
            document.querySelectorAll('.placed-ships').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.control-buttons').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.exitqueuebutton').forEach(el => el.style.display = 'none');
            document.getElementById('messages-cont').style.display = 'block';
            document.querySelectorAll('.status-title').forEach(el => el.style.display = 'inline-block');
            document.querySelectorAll('.gotostartbutton').forEach(el => el.style.display = 'block');
            

            if(data){
                document.querySelectorAll('.status-title').forEach(el => el.innerHTML = 'Νίκη!');
                if(!roomgame) stats.wins++;
            }
            else {
                document.querySelectorAll('.status-title').forEach(el => el.innerHTML = 'Ήττα');
                if(!roomgame) stats.losses++;
            }
            if(!roomgame) document.querySelectorAll('.own-stats').forEach(el => el.innerHTML = 'Τα στατιστικά μου: ' + stats.wins + ' Νίκες, ' + stats.losses + ' Ήττες');
            roomgame = false;
        }
    });

    socket.on('timeleft', function (data) {
        if(gamestarted){
            ltstart = Date.now() - data*1000;
            timeLeftAnim(ltstart);
        }
    });

    socket.on('roomcreated', function (data) {
        if(!gamestarted && !waiting){
            roomgame = true;
            if(data.status == "ok"){
                document.querySelectorAll('.own-grid-hider').forEach(el => el.style.display = 'block');
                document.querySelectorAll('.placed-ships').forEach(el => el.style.display = 'none');
                document.querySelectorAll('.control-buttons').forEach(el => el.style.display = 'none');
                document.querySelectorAll('.exitqueuebutton').forEach(el => el.style.display = 'block');
                document.querySelectorAll('.exitqueuebutton').forEach(el => el.disabled = false);
                document.querySelectorAll('.status-title').forEach(el => el.innerHTML = 'Κωδικός Δωματίου:<br>' + data.room);
                waiting = true;
            }
            else if(data.status == "left" && waiting){
                waiting = false;
                document.querySelectorAll('.own-grid-hider').forEach(el => el.style.display = 'none');
                document.querySelectorAll('.status-title').forEach(el => el.innerHTML = 'Επεξεργασία πλοίων');
                document.querySelectorAll('.placed-ships').forEach(el => el.style.display = 'block');
                document.querySelectorAll('.control-buttons').forEach(el => el.style.display = 'block');
                document.querySelectorAll('.exitqueuebutton').forEach(el => el.style.display = 'none');
            }
        }
        
    });

    socket.on('online', function (data) {
        document.querySelectorAll('.online-now').forEach(el => el.innerHTML = 'Ενεργοί χρήστες: ' + data);
    });

    socket.on('leaderboards', function (data) {
        document.querySelectorAll('.leaderboard').forEach(el => {
            let ih = '<tr><th>#</th><th>Όνομα</th><th>Νίκες</th><th>%</th></tr>';
            for(let i = 0; i < data.length; i++)
                ih += '<tr><td>' + (i+1) + '</td><td>' + data[i].name + '</td><td>' + data[i].wins + '</td><td>' + data[i].per + '</td></tr>';
            el.innerHTML = ih;
        });
    });

    socket.on('disconnect', function(){
        document.querySelectorAll('.status-title').forEach(el => el.style.display = 'inline-block');
        if(exists) document.querySelectorAll('.status-title').forEach(el => el.innerHTML = 'Υπάρχει ήδη σύνδεση στη ναυμαχία από τον λογαριασμό σας!');
        else document.querySelectorAll('.status-title').forEach(el => el.innerHTML = 'Αδυναμία σύνδεσης.<br>Προσπαθήστε ξανά σε λίγα λεπτά');
        document.querySelectorAll('.own-grid-hider').forEach(el => el.style.display = 'block');
        document.querySelectorAll('.gotohome').forEach(el => el.style.display = 'inline-block');
        document.querySelectorAll('.placed-ships').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.control-buttons').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.exitqueuebutton').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.gotostartbutton').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.timeleft').forEach(el => el.innerHTML = '');
        document.getElementById('messages-cont').style.display = 'block';
        waiting = false;
        gamestarted = false;
    });
});

function enterQueue(){
    if(socket.connected){
        let ships = getShipCount();
        if(ships[0] == 4 && ships[1] == 3 && ships[2] == 2 && ships[3] == 1){
            let senddat = {};
            senddat.sendname = document.querySelector("input[name=\"sendname\"]").checked;
            senddat.savegrid = document.querySelector("input[name=\"savegrid\"]").checked;
            senddat.griddata = gridToBinary();

            socket.emit('enterqueue', senddat);
            roomgame = false;
        }
    }
}

function exitQueue(){
    if(waiting){
        socket.emit('exitqueue', 'yes');
        document.querySelectorAll('.exitqueuebutton').forEach(el => el.disabled = true);
    }
}

function sendAttack(tile){
    if(gamestarted && meplaying){
        socket.emit('attack', tile);
        meplaying = false;
    }
}

function goToStart(){
    if(!gamestarted){
        document.querySelectorAll('.own-grid-hider').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.status-title').forEach(el => el.style.display = 'inline-block');
        document.querySelectorAll('.status-title').forEach(el => el.innerHTML = 'Επεξεργασία πλοίων');
        document.querySelectorAll('.placed-ships').forEach(el => el.style.display = 'block');
        document.querySelectorAll('.control-buttons').forEach(el => el.style.display = 'block');
        document.querySelectorAll('.exitqueuebutton').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.gotostartbutton').forEach(el => el.style.display = 'none');
        document.getElementById('messages-cont').style.display = 'block';
        document.querySelectorAll('.opponent-name').forEach(el => el.innerHTML = '');
        document.querySelectorAll('.timeleft').forEach(el => el.innerHTML = '');

        resetGrids();
    }
}

function animWaiting(t){
    t = t % 4;
    if(waiting){
        let d = '';
        for(let i=0; i<t; i++) d += '.';
        document.querySelectorAll('.status-title').forEach(el => el.innerHTML = 'Αναζήτηση αντιπάλου' + d);
        setTimeout(() => {animWaiting(t+1)}, 1000);
    }
}

function timeLeftAnim(tstart){
    if(gamestarted && ltstart == tstart){
        let dt = Math.ceil((Date.now()-tstart)/1000);
        document.querySelectorAll('.timeleft').forEach(el => el.innerHTML = (40-dt));
        if(dt >= 40) socket.emit('checktime', tstart);
        else setTimeout(() => {timeLeftAnim(tstart)}, 998);
    }
}

document.querySelectorAll('.inputroomcode').forEach(el => el.addEventListener('input', (e) => changeRoomInput(el)));

let roomcode = '';
function changeRoomInput(el){
    roomcode = el.value;
    if(roomcode == '') document.querySelectorAll('.createroombutton').forEach(el => el.innerHTML = 'Νέο Δωμάτιο');
    else document.querySelectorAll('.createroombutton').forEach(el => el.innerHTML = 'Είσοδος');
}

function roomButtonAction(){
    if(socket.connected){
        let ships = getShipCount();
        if(ships[0] == 4 && ships[1] == 3 && ships[2] == 2 && ships[3] == 1){
            let senddat = {};
            senddat.sendname = document.querySelector("input[name=\"sendname\"]").checked;
            senddat.savegrid = document.querySelector("input[name=\"savegrid\"]").checked;
            senddat.griddata = gridToBinary();

            if(roomcode == '') socket.emit('createroom', senddat);
            else {
                senddat.room = roomcode.toLowerCase();
                socket.emit('joinroom', senddat);
                roomgame = true;
            }
        }
    }
}