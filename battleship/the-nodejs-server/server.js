const app = require('express')();
const http = require('http').Server(app);
let io = require('socket.io')(http, { pingInterval: 15000, pingTimeout: 10000 });

const CryptoJS = require("crypto-js");

const mysql = require('mysql');
//the database where the user accounts are stored
let maindb = mysql.createConnection({host: "hostname", user: "username", password: "password", database : 'database', socketPath: '/path/to/socketfile'});
maindb.connect(function(err) {if (err) throw err;console.log("Connected to Main Database");});

//the database to store the game data
let gamedb = mysql.createConnection({host: "hostname", user: "username", password: "password", database : 'database', socketPath: '/path/to/socketfile'});
gamedb.connect(function(err) {if (err) throw err;console.log("Connected to Game Database");});


const battleshipVersion = '1.5'; //this and the client's version MUST match
let users = {}; // 'username': {name: '', socket: socket, game: null, sendname: true, grid: null, prevgamewith: ''}
let socketToUser = {};
let games = {}; // {player1: '', player2: '', player1Grid: [], player2Grid: [], player1Hits: 0, player2Hits: 0, playing: 0, lastMove: 0, gametype: 0, moveLog: []}
let waitingUsers = []; //[username1, username2, ...];
let rooms = {}; //room: [username1, username2]
let userToRoom = {}// username: room

io.sockets.on('connection', function (socket) {
  socket.on('auth', function (data) {
	if(data == undefined){
      socket.emit('authed', {status:"bad"});
      socket.disconnect();
      return;
    }

    if(data.username == undefined || data.cred == undefined || data.name == undefined){
      socket.emit('authed', {status:"bad"});
      socket.disconnect();
      return;
    }

    let username = data.username;
    let decname = decryptName(data.name.toString(), username);
    let un = maindb.escape(username);
    maindb.query("SELECT * FROM users WHERE user_username=" + un, function (err, result) {
      if (err) throw err;
      if(result.length < 1){
        socket.emit('authed', {status:"bad"});
        socket.disconnect();
        return;
      }

      let day = (new Date().getFullYear()) + '-' + (new Date().getMonth() + 1) + '-' + new Date().getDate();
      let c = CryptoJS.MD5(result[0]['user_id'] + result[0]['user_username'] + day + battleshipVersion).toString();

      if(c === data.cred){
        if(users[username] != undefined){
          socket.emit('authed', {status:"exists"});
          socket.disconnect();
          return;
        }

        gamedb.query("SELECT * FROM users WHERE user_username=" + un, function (err, result) {
          if (err) throw err;
          if(result.length > 0){
            let sdata = {};
            sdata.status = "ok";
            sdata.sendname = (parseInt(result[0]['user_sendname']) == 1);
            sdata.savegrid = (parseInt(result[0]['user_savegrid']) == 1);
            sdata.grid = (sdata.savegrid) ? result[0]['user_grid'] : null;
            sdata.wins = result[0]['user_wins'];
            sdata.losses = result[0]['user_losses'];

            socket.emit('authed', sdata);

            users[username].prevgamewith = result[0]['user_lastgamewith'];

            gamedb.query("UPDATE users SET user_name=" + gamedb.escape(decname) + " WHERE user_username=" + un, function (erra, resulta) {if (erra) throw erra;});
          }
          else {
            gamedb.query("INSERT INTO users SET user_username=" + un + ', user_name=' + gamedb.escape(decname), function (erra, resulta) {
              if (erra) throw erra;
              socket.emit('authed', {status: "ok", sendname: true, savegrid: true, grid: null, wins: 0, losses: 0});
            });
          }
        });

        users[username] = {name: decname, socket: socket, game: null, sendname: true, grid: null, prevgamewith: ''};
        socketToUser[socket.id] = username;

        io.emit("online", Object.keys(users).length);
        updateLeaderboards();
      }
      else socket.emit('authed', {status:"bad"});
    });
  });

  socket.on('enterqueue', function (data) {
    if(data == undefined){
      socket.emit('queue', {status: "bad"});
      return;
    }

    if(data.sendname == undefined || data.savegrid == undefined || data.griddata == undefined){
      socket.emit('queue', {status: "bad"});
      return;
    }

    if (typeof data.sendname !== "boolean" || typeof data.savegrid !== "boolean"){
      socket.emit('queue', {status: "bad"});
      return;
    }

    let username = socketToUser[socket.id];

    if(username == undefined){
      socket.emit('queue', {status: "notauthed"});
      socket.disconnect();
      return;
    }

    if(users[username].game != null){
      socket.emit('queue', {status: "ingame"});
      return;
    }

    if(!validateGrid(data.griddata)){
      socket.emit('queue', {status: "badgrid"});
      return;
    }

    users[username].sendname = data.sendname;
    users[username].grid = binaryToGrid(data.griddata);
    gamedb.query("UPDATE users SET user_sendname=" + ((data.sendname) ? 1 : 0) + ", user_savegrid=" + ((data.savegrid) ? 1 : 0) + ", user_grid=" + gamedb.escape(data.griddata) + " WHERE user_username=" + gamedb.escape(username), function (err, result) {
      if (err) throw err;
    });

    if(waitingUsers.indexOf(username) >= 0){
      socket.emit('queue', {status: "waiting"});
      return;
    }
    else for(let i = 0; i < waitingUsers.length; i++){
	  //uncomment and remove 'true' to enable 'dont allow players to match whith eachother more than one consecutive time'
      if(/*users[username].prevgamewith != waitingUsers[i] && users[waitingUsers[i]].prevgamewith != username*/ true){
        users[waitingUsers[i]].socket.emit('startGame', {side: 0, other: (users[username].sendname) ? users[username].name : "Χρήστης"});
        socket.emit('startGame', {side: 1, other: (users[waitingUsers[i]].sendname) ? users[waitingUsers[i]].name : "Χρήστης"});

        games[CryptoJS.MD5(waitingUsers[i] + username).toString()] = {player1: waitingUsers[i], player2: username, player1Grid: users[waitingUsers[i]].grid, player2Grid: users[username].grid, player1Hits: 0, player2Hits: 0, playing: 0, lastMove: Date.now(), gametype: 0, moveLog: []};

        users[username].game = CryptoJS.MD5(waitingUsers[i] + username).toString();
        users[waitingUsers[i]].game = CryptoJS.MD5(waitingUsers[i] + username).toString();

        waitingUsers.splice(i, 1);

        return;
      }
    }

    //didn't find a game. Add to waiting list
    waitingUsers.push(username);
    socket.emit('queue', {status: "waiting"});
  });

  socket.on('exitqueue', function (data) {
    let username = socketToUser[socket.id];

    if(username == undefined){
      socket.emit('queue', {status: "left"});
      socket.disconnect();
      return;
    }

    let idx = waitingUsers.indexOf(username);
    if(idx >= 0) waitingUsers.splice(idx, 1);

    if(userToRoom[username] != undefined){
      let room = rooms[userToRoom[username]];
      delete rooms[userToRoom[username]];
      delete userToRoom[room[0]];
      delete userToRoom[room[1]];
    }

    socket.emit('queue', {status: "left"});
  });

  socket.on('attack', function (data) {

    let username = socketToUser[socket.id];

    if(username == undefined){
      socket.disconnect();
      return;
    }

    if(users[username].game == null){
      socket.disconnect();
      return;
    }

    let game = games[users[username].game];

    if(game.player1 == username && game.playing != 0){
      socket.emit('attackstat', 'other');
      return;
    }
    else if(game.player2 == username && game.playing != 1){
      socket.emit('attackstat', 'other');
      return;
    }

    if(data == undefined){
      socket.emit('attackstat', 'again');
      return;
    }
    if (data !== parseInt(data, 10)){
      socket.emit('attackstat', 'again');
      return;
    }
    if(data < 0 || data > 99){
      socket.emit('attackstat', 'again');
      return;
    }

    let who = (game.player1 == username) ? 0 : 1;

    //{who (0|1), where (0-99)}
    for(let i = 0; i < game.moveLog.length; i++){
      if(game.moveLog[i][0] == who && game.moveLog[i][1] == data){
        socket.emit('attackstat', 'already');
        return;
      }
    }

    games[users[username].game].lastMove = Date.now();
	  games[users[username].game].playing = (game.playing == 0) ? 1 : 0;

    games[users[username].game].moveLog.push([who, data]);

    if(who == 0){
      if(!game.player2Grid[data]){
        socket.emit('attack', {tile: data, side: false, hit: false, sunk: false, sunkarr: []});
        users[game.player2].socket.emit('attack', {tile: data, side: true, hit: false, sunk: false, sunkarr: []})
        return;
      }
      else {
        games[users[username].game].player1Hits++;

        let shiptiles = getShipTiles(data, game.player2Grid);
        let found = 0;
        game.moveLog.push([who, data]);
        for(let i = 0; i < shiptiles.length; i++){
          for(let j = 0; j < game.moveLog.length; j++){
            if(game.moveLog[j][0] == who && game.moveLog[j][1] == shiptiles[i]){
              found++;
              break;
            }
          }
        }

        games[users[username].game].playing = who;

        if(found == shiptiles.length){
          socket.emit('attack', {tile: data, side: false, hit: true, sunk: true, sunkarr: shiptiles});
          users[game.player2].socket.emit('attack', {tile: data, side: true, hit: true, sunk: true, sunkarr: shiptiles})

          //addAutoMoves(game.player2Grid, shiptiles, users[username].game, who);
        }
        else {
          socket.emit('attack', {tile: data, side: false, hit: true, sunk: false, sunkarr: []});
          users[game.player2].socket.emit('attack', {tile: data, side: true, hit: true, sunk: false, sunkarr: []})
        }
      }
    }
    else {
      if(!game.player1Grid[data]){
        socket.emit('attack', {tile: data, side: false, hit: false, sunk: false, sunkarr: []});
        users[game.player1].socket.emit('attack', {tile: data, side: true, hit: false, sunk: false, sunkarr: []})
        return;
      }
      else {
        games[users[username].game].player2Hits++;

        let shiptiles = getShipTiles(data, game.player1Grid);
        let found = 0;
        game.moveLog.push([who, data]);
        for(let i = 0; i < shiptiles.length; i++){
          for(let j = 0; j < game.moveLog.length; j++){
            if(game.moveLog[j][0] == who && game.moveLog[j][1] == shiptiles[i]){
              found++;
              break;
            }
          }
        }

        games[users[username].game].playing = who;

        if(found == shiptiles.length){
          socket.emit('attack', {tile: data, side: false, hit: true, sunk: true, sunkarr: shiptiles});
          users[game.player1].socket.emit('attack', {tile: data, side: true, hit: true, sunk: true, sunkarr: shiptiles})

          //addAutoMoves(game.player1Grid, shiptiles, users[username].game, who);
        }
        else {
          socket.emit('attack', {tile: data, side: false, hit: true, sunk: false, sunkarr: []});
          users[game.player1].socket.emit('attack', {tile: data, side: true, hit: true, sunk: false, sunkarr: []})
        }
      }
    }

    if(who == 0 && games[users[username].game].player1Hits >= 20){
      socket.emit('gamefinish', true);
      users[game.player2].socket.emit('gamefinish', false);

      let gameId = users[username].game;
      if(gameId != null){

        let first = gamedb.escape(games[gameId].player1);
        let second = gamedb.escape(games[gameId].player2);
        let fgrid = gamedb.escape(gridToBinary(games[gameId].player1Grid));
        let sgrid = gamedb.escape(gridToBinary(games[gameId].player2Grid));
        let gamelog = gamedb.escape(JSON.stringify(games[gameId].moveLog));

        users[games[gameId].player2].game = null;
        users[games[gameId].player1].game = null;

        if(game.gametype == 0){
          gamedb.query("INSERT INTO games SET game_first_user=" + first + ", game_second_user=" + second + ", game_first_grid=" + fgrid + ", game_second_grid=" + sgrid + ", game_move_log=" + gamelog + ", game_winner=0", function (err, result) {if (err) throw err;});
          gamedb.query("SELECT * FROM users WHERE user_username=" + first, function (err, result) {
            if (err) throw err;
            let wins = result[0]['user_wins'] + 1;
            gamedb.query("UPDATE users SET user_wins=" + wins + ", user_lastgamewith=" + second + " WHERE user_username=" + first, function (erra, resulta) {if (erra) throw erra;})
          });
          gamedb.query("SELECT * FROM users WHERE user_username=" + second, function (err, result) {
            if (err) throw err;
            let losses = result[0]['user_losses'] + 1;
            gamedb.query("UPDATE users SET user_losses=" + losses + ", user_lastgamewith=" + first + " WHERE user_username=" + second, function (erra, resulta) {if (erra) throw erra;})
          });

          users[games[gameId].player2].prevgamewith = games[gameId].player1;
          users[games[gameId].player1].prevgamewith = games[gameId].player2;
        }
        else if(game.gametype == 1){
          if(userToRoom[username] != undefined){
            let room = rooms[userToRoom[username]];
            delete rooms[userToRoom[username]];
            delete userToRoom[room[0]];
            delete userToRoom[room[1]];
          }
        }

        delete games[gameId];
      }
    }
    else if(who == 1 && games[users[username].game].player2Hits >= 20){
      socket.emit('gamefinish', true);
      users[game.player1].socket.emit('gamefinish', false);

      let gameId = users[username].game;
      if(gameId != null){

        let first = gamedb.escape(games[gameId].player1);
        let second = gamedb.escape(games[gameId].player2);
        let fgrid = gamedb.escape(gridToBinary(games[gameId].player1Grid));
        let sgrid = gamedb.escape(gridToBinary(games[gameId].player2Grid));
        let gamelog = gamedb.escape(JSON.stringify(games[gameId].moveLog));

        users[games[gameId].player2].game = null;
        users[games[gameId].player1].game = null;

        if(game.gametype == 0){
          gamedb.query("INSERT INTO games SET game_first_user=" + first + ", game_second_user=" + second + ", game_first_grid=" + fgrid + ", game_second_grid=" + sgrid + ", game_move_log=" + gamelog + ", game_winner=1", function (err, result) {if (err) throw err;});
          gamedb.query("SELECT * FROM users WHERE user_username=" + second, function (err, result) {
            if (err) throw err;
            let wins = result[0]['user_wins'] + 1;
            gamedb.query("UPDATE users SET user_wins=" + wins + ", user_lastgamewith=" + first + " WHERE user_username=" + second, function (erra, resulta) {if (erra) throw erra;})
          });
          gamedb.query("SELECT * FROM users WHERE user_username=" + first, function (err, result) {
            if (err) throw err;
            let losses = result[0]['user_losses'] + 1;
            gamedb.query("UPDATE users SET user_losses=" + losses + ", user_lastgamewith=" + second + " WHERE user_username=" + first, function (erra, resulta) {if (erra) throw erra;})
          });

          users[games[gameId].player2].prevgamewith = games[gameId].player1;
          users[games[gameId].player1].prevgamewith = games[gameId].player2;
        }
        else if(game.gametype == 1){
          if(userToRoom[username] != undefined){
            let room = rooms[userToRoom[username]];
            delete rooms[userToRoom[username]];
            delete userToRoom[room[0]];
            delete userToRoom[room[1]];
          }
        }

        delete games[gameId];
      }
    }

  });

  socket.on('checktime', function (data) {

    let username = socketToUser[socket.id];

    if(username == undefined){
      socket.disconnect();
      return;
    }

    if(users[username].game == null){
      return;
    }

    let gameId = users[username].game;
    let game = games[gameId];

    if(Math.ceil((Date.now() - game.lastMove)/1000) >= 40){
      if(game.playing == 0){
        let first = gamedb.escape(game.player1);
        let second = gamedb.escape(game.player2);
        let fgrid = gamedb.escape(gridToBinary(game.player1Grid));
        let sgrid = gamedb.escape(gridToBinary(game.player2Grid));
        let gamelog = gamedb.escape(JSON.stringify(game.moveLog));

        users[game.player2].game = null;
        users[game.player1].game = null;

        if(game.gametype == 0){
          gamedb.query("INSERT INTO games SET game_first_user=" + first + ", game_second_user=" + second + ", game_first_grid=" + fgrid + ", game_second_grid=" + sgrid + ", game_move_log=" + gamelog + ", game_winner=1", function (err, result) {if (err) throw err;});
          gamedb.query("SELECT * FROM users WHERE user_username=" + second, function (err, result) {
            if (err) throw err;
            let wins = result[0]['user_wins'] + 1;
            gamedb.query("UPDATE users SET user_wins=" + wins + ", user_lastgamewith=" + first + " WHERE user_username=" + second, function (erra, resulta) {if (erra) throw erra;})
          });
          gamedb.query("SELECT * FROM users WHERE user_username=" + first, function (err, result) {
            if (err) throw err;
            let losses = result[0]['user_losses'] + 1;
            gamedb.query("UPDATE users SET user_losses=" + losses + ", user_lastgamewith=" + second + " WHERE user_username=" + first, function (erra, resulta) {if (erra) throw erra;})
          });

          users[games[gameId].player2].prevgamewith = games[gameId].player1;
          users[games[gameId].player1].prevgamewith = games[gameId].player2;
        }
        else if(game.gametype == 1){
          if(userToRoom[username] != undefined){
            let room = rooms[userToRoom[username]];
            delete rooms[userToRoom[username]];
            delete userToRoom[room[0]];
            delete userToRoom[room[1]];
          }
        }

        users[game.player1].socket.emit('gamefinish', false);
        users[game.player2].socket.emit('gamefinish', true);

        delete games[gameId];
      }
      else {
        let first = gamedb.escape(game.player1);
        let second = gamedb.escape(game.player2);
        let fgrid = gamedb.escape(gridToBinary(game.player1Grid));
        let sgrid = gamedb.escape(gridToBinary(game.player2Grid));
        let gamelog = gamedb.escape(JSON.stringify(game.moveLog));

        users[game.player2].game = null;
        users[game.player1].game = null;

        if(game.gametype == 0){
          gamedb.query("INSERT INTO games SET game_first_user=" + first + ", game_second_user=" + second + ", game_first_grid=" + fgrid + ", game_second_grid=" + sgrid + ", game_move_log=" + gamelog + ", game_winner=0", function (err, result) {if (err) throw err;});
          gamedb.query("SELECT * FROM users WHERE user_username=" + first, function (err, result) {
            if (err) throw err;
            let wins = result[0]['user_wins'] + 1;
            gamedb.query("UPDATE users SET user_wins=" + wins + ", user_lastgamewith=" + second + " WHERE user_username=" + first, function (erra, resulta) {if (erra) throw erra;})
          });
          gamedb.query("SELECT * FROM users WHERE user_username=" + second, function (err, result) {
            if (err) throw err;
            let losses = result[0]['user_losses'] + 1;
            gamedb.query("UPDATE users SET user_losses=" + losses + ", user_lastgamewith=" + first + " WHERE user_username=" + second, function (erra, resulta) {if (erra) throw erra;})
          });

          users[games[gameId].player2].prevgamewith = games[gameId].player1;
          users[games[gameId].player1].prevgamewith = games[gameId].player2;
        }
        else if(game.gametype == 1){
          if(userToRoom[username] != undefined){
            let room = rooms[userToRoom[username]];
            delete rooms[userToRoom[username]];
            delete userToRoom[room[0]];
            delete userToRoom[room[1]];
          }
        }

        users[game.player1].socket.emit('gamefinish', true);
        users[game.player2].socket.emit('gamefinish', false);

        delete games[gameId];
      }
    }
    else {
      socket.emit('timeleft', 40 - Math.floor((Date.now() - game.lastMove)/1000));
      return;
    }
  });

  socket.on('createroom', function(data){
    if(data == undefined){
      socket.emit('roomcreated', {status: "bad"});
      return;
    }

    if(data.sendname == undefined || data.savegrid == undefined || data.griddata == undefined){
      socket.emit('roomcreated', {status: "bad"});
      return;
    }

    if (typeof data.sendname !== "boolean" || typeof data.savegrid !== "boolean"){
      socket.emit('roomcreated', {status: "bad"});
      return;
    }

    let username = socketToUser[socket.id];

    if(username == undefined){
      socket.emit('roomcreated', {status: "notauthed"});
      socket.disconnect();
      return;
    }

    if(users[username].game != null){
      socket.emit('roomcreated', {status: "ingame"});
      return;
    }
    if(waitingUsers.indexOf(username) >= 0){
      socket.emit('roomcreated', {status: "ingame"});
      return;
    }

    if(!validateGrid(data.griddata)){
      socket.emit('roomcreated', {status: "badgrid"});
      return;
    }

    users[username].sendname = data.sendname;
    users[username].grid = binaryToGrid(data.griddata);
    gamedb.query("UPDATE users SET user_sendname=" + ((data.sendname) ? 1 : 0) + ", user_savegrid=" + ((data.savegrid) ? 1 : 0) + ", user_grid=" + gamedb.escape(data.griddata) + " WHERE user_username=" + gamedb.escape(username), function (err, result) {
      if (err) throw err;
    });

    if(userToRoom[username] != undefined){
      socket.emit('roomcreated', {status: 'ok', room: userToRoom[username]});
      return;
    }

    let dict = "abcdefghijklmnopqrstuvwxyz";
    let code = '';
    while(rooms[code] != undefined || code == ''){
      code = '';
      for(let i = 0; i < 4; i++) code += dict.charAt(Math.floor(Math.random() * dict.length));
    }

    rooms[code] = [username, null];
    userToRoom[username] = code;
    socket.emit('roomcreated', {status: 'ok', room: userToRoom[username]});
  });

  socket.on('joinroom', function(data){
    if(data == undefined){
      socket.emit('roomcreated', {status: "bad"});
      return;
    }

    if(data.sendname == undefined || data.savegrid == undefined || data.griddata == undefined || data.room == undefined){
      socket.emit('roomcreated', {status: "badb"});
      return;
    }

    if (typeof data.sendname !== "boolean" || typeof data.savegrid !== "boolean"){
      socket.emit('roomcreated', {status: "bad"});
      return;
    }

    let username = socketToUser[socket.id];

    if(username == undefined){
      socket.emit('roomcreated', {status: "notauthed"});
      socket.disconnect();
      return;
    }

    if(users[username].game != null){
      socket.emit('roomcreated', {status: "ingame"});
      return;
    }
    if(waitingUsers.indexOf(username) >= 0){
      socket.emit('roomcreated', {status: "ingame"});
      return;
    }

    if(!validateGrid(data.griddata)){
      socket.emit('roomcreated', {status: "badgrid"});
      return;
    }

    users[username].sendname = data.sendname;
    users[username].grid = binaryToGrid(data.griddata);
    gamedb.query("UPDATE users SET user_sendname=" + ((data.sendname) ? 1 : 0) + ", user_savegrid=" + ((data.savegrid) ? 1 : 0) + ", user_grid=" + gamedb.escape(data.griddata) + " WHERE user_username=" + gamedb.escape(username), function (err, result) {
      if (err) throw err;
    });


    if(userToRoom[username] != undefined){
      socket.emit('joinroom', 'err');
      return;
    }

    if(rooms[data.room] == undefined){
      socket.emit('joinroom', 'notfound');
      return;
    }

    if(rooms[data.room][1] != null){
      socket.emit('joinroom', 'full');
      return;
    }

    rooms[data.room][1] = username;
    userToRoom[username] = data.room;

    users[rooms[data.room][0]].socket.emit('startGame', {side: 0, other: (users[username].sendname) ? users[username].name : "Χρήστης"});
    socket.emit('startGame', {side: 1, other: (users[rooms[data.room][0]].sendname) ? users[rooms[data.room][0]].name : "Χρήστης"});

    games[CryptoJS.MD5(rooms[data.room][0] + username).toString()] = {player1: rooms[data.room][0], player2: username, player1Grid: users[rooms[data.room][0]].grid, player2Grid: users[username].grid, player1Hits: 0, player2Hits: 0, playing: 0, lastMove: Date.now(), gametype: 1, moveLog: []};

    users[username].game = CryptoJS.MD5(rooms[data.room][0] + username).toString();
    users[rooms[data.room][0]].game = CryptoJS.MD5(rooms[data.room][0] + username).toString();
  });

  socket.on('disconnect', function() {
    let username = socketToUser[socket.id];

    if(username != undefined){
      let idx = waitingUsers.indexOf(username);
      if(idx >= 0) waitingUsers.splice(idx, 1);

      let gameId = users[username].game;
      if(gameId != null){

        let first = gamedb.escape(games[gameId].player1);
        let second = gamedb.escape(games[gameId].player2);
        let fgrid = gamedb.escape(gridToBinary(games[gameId].player1Grid));
        let sgrid = gamedb.escape(gridToBinary(games[gameId].player2Grid));
        let gamelog = gamedb.escape(JSON.stringify(games[gameId].moveLog));

        if(games[gameId].player1 == username){
          users[games[gameId].player2].game = null;
          users[games[gameId].player2].socket.emit('otherLeft', 'yes');

          if(games[gameId].gametype == 0){
            gamedb.query("INSERT INTO games SET game_first_user=" + first + ", game_second_user=" + second + ", game_first_grid=" + fgrid + ", game_second_grid=" + sgrid + ", game_move_log=" + gamelog + ", game_winner=1", function (err, result) {if (err) throw err;});
            gamedb.query("SELECT * FROM users WHERE user_username=" + first, function (err, result) {
              if (err) throw err;
              let losses = result[0]['user_losses'] + 1;
              gamedb.query("UPDATE users SET user_losses=" + losses + ", user_lastgamewith=" + second + " WHERE user_username=" + first, function (erra, resulta) {if (erra) throw erra;})
            });
            gamedb.query("SELECT * FROM users WHERE user_username=" + second, function (err, result) {
              if (err) throw err;
              let wins = result[0]['user_wins'] + 1;
              gamedb.query("UPDATE users SET user_wins=" + wins + ", user_lastgamewith=" + first + " WHERE user_username=" + second, function (erra, resulta) {if (erra) throw erra;})
            });

            users[games[gameId].player2].prevgamewith = games[gameId].player1;
            users[games[gameId].player1].prevgamewith = games[gameId].player2;
          }
          else if(games[gameId].gametype == 1){
            if(userToRoom[username] != undefined){
              let room = rooms[userToRoom[username]];
              delete rooms[userToRoom[username]];
              delete userToRoom[room[0]];
              delete userToRoom[room[1]];
            }
          }
        }
        else {
          users[games[gameId].player1].game = null;
          users[games[gameId].player1].socket.emit('otherLeft', 'yes');

          if(games[gameId].gametype == 0){
            gamedb.query("INSERT INTO games SET game_first_user=" + first + ", game_second_user=" + second + ", game_first_grid=" + fgrid + ", game_second_grid=" + sgrid + ", game_move_log=" + gamelog + ", game_winner=0", function (err, result) {if (err) throw err;});
            gamedb.query("SELECT * FROM users WHERE user_username=" + second, function (err, result) {
              if (err) throw err;
              let losses = result[0]['user_losses'] + 1;
              gamedb.query("UPDATE users SET user_losses=" + losses + ", user_lastgamewith=" + first + " WHERE user_username=" + second, function (erra, resulta) {if (erra) throw erra;})
            });
            gamedb.query("SELECT * FROM users WHERE user_username=" + first, function (err, result) {
              if (err) throw err;
              let wins = result[0]['user_wins'] + 1;
              gamedb.query("UPDATE users SET user_wins=" + wins + ", user_lastgamewith=" + second + " WHERE user_username=" + first, function (erra, resulta) {if (erra) throw erra;})
            });

            users[games[gameId].player2].prevgamewith = games[gameId].player1;
            users[games[gameId].player1].prevgamewith = games[gameId].player2;
          }
          else if(games[gameId].gametype == 1){
            if(userToRoom[username] != undefined){
              let room = rooms[userToRoom[username]];
              delete rooms[userToRoom[username]];
              delete userToRoom[room[0]];
              delete userToRoom[room[1]];
            }
          }
        }

        delete games[gameId];
      }

      if(userToRoom[username] != undefined){
        let room = rooms[userToRoom[username]];
        delete rooms[userToRoom[username]];
        delete userToRoom[room[0]];
        delete userToRoom[room[1]];
      }

      delete users[username];
      delete socketToUser[socket.id];

      io.emit("online", Object.keys(users).length);
    }
 });
});

function validateGrid(grid){
  if(typeof grid !== 'string' && !(myVar instanceof String)) return false;

  if(grid.length != 100) return false;
  let playergrid = binaryToGrid(grid);

  for(let i=0;i<100;i++){
    if(playergrid[i]){
      if(i % 10 == 0){
        if(Math.floor(i / 10) == 0){
            //top left
            if(playergrid[i + 1 + 10]) return false;
        }
        else if(Math.floor(i / 10) == 9){
            //bottom left
            if(playergrid[i + 1 - 10]) return false;
        }
        else {
            if(playergrid[i + 1 + 10]) return false;
            if(playergrid[i + 1 - 10]) return false;
        }
      }
      else if(i % 10 == 9){
        if(Math.floor(i / 10) == 0){
            //top right
            if(playergrid[i - 1 + 10]) return false;
        }
        else if(Math.floor(i / 10) == 9){
            //bottom right
            if(playergrid[i - 1 - 10]) return false;
        }
        else {
            if(playergrid[i - 1 + 10]) return false;
            if(playergrid[i - 1 - 10]) return false;
        }
      }
      else {
        if(Math.floor(i / 10) == 0){
            //top
            if(playergrid[i - 1 + 10]) return false;
            if(playergrid[i + 1 + 10]) return false;
        }
        else if(Math.floor(i / 10) == 9){
            //bottom
            if(playergrid[i - 1 - 10]) return false;
            if(playergrid[i + 1 - 10]) return false;
        }
        else {
            if(playergrid[i + 1 + 10]) return false;
            if(playergrid[i + 1 - 10]) return false;
            if(playergrid[i - 1 + 10]) return false;
            if(playergrid[i - 1 - 10]) return false;
        }
      }

      if(getShipSize(i, playergrid) > 4) return false;
    }
  }

  let ships = getShipCount(playergrid);
  if(ships[0] != 4 || ships[1] != 3 || ships[2] != 2 || ships[3] != 1) return false;

  return true;
}

function getShipCount(grid){
  let ships = [0, 0, 0, 0];

  for(let i = 0; i < 100; i++){
      let ss = getShipSize(i, grid);
      if(ss > 0)
          ships[ss - 1]++;
  }

  ships[1] /= 2;
  ships[2] /= 3;
  ships[3] /= 4;

  return ships;
}

function getShipSize(pos, playergrid){
  return getShipTiles(pos, playergrid).length;
}

function getShipTiles(pos, playergrid, from = null){
  if(pos >= 100 || pos < 0 || !playergrid[pos]) return [];
  let o = [pos];

  if(pos % 10 < 9 && pos + 1 != from)
    o = o.concat(getShipTiles(pos + 1, playergrid, pos));
  if(pos % 10 > 0 && pos - 1 != from)
    o = o.concat(getShipTiles(pos - 1, playergrid, pos));
  if(Math.floor(pos / 10) < 9 && pos + 10 != from)
    o = o.concat(getShipTiles(pos + 10, playergrid, pos));
  if(Math.floor(pos / 10) > 0 && pos - 10 != from)
    o = o.concat(getShipTiles(pos - 10, playergrid, pos));

  return o;
}

//useless computation and storage usage...
function addAutoMoves(grid, shiptiles, gameId, who){
  for(let i = 0; i < shiptiles.length; i++){
    if(shiptiles[i] % 10 == 0){
      if(!grid[shiptiles[i] + 1])
        games[gameId].moveLog.push([who, shiptiles[i] + 1]);

      if(Math.floor(shiptiles[i] / 10) < 9){
        if(!grid[shiptiles[i] + 10])
          games[gameId].moveLog.push([who, shiptiles[i] + 10]);
        if(!grid[shiptiles[i] + 10 + 1])
          games[gameId].moveLog.push([who, shiptiles[i] + 10 + 1]);
      }
      if(Math.floor(shiptiles[i] / 10) > 0){
        if(!grid[shiptiles[i] - 10])
          games[gameId].moveLog.push([who, shiptiles[i] - 10]);
        if(!grid[shiptiles[i] - 10 + 1])
          games[gameId].moveLog.push([who, shiptiles[i] - 10 + 1]);
      }
    }
    else if(shiptiles[i] % 10 == 9){
      if(!grid[shiptiles[i] - 1])
        games[gameId].moveLog.push([who, shiptiles[i] - 1]);

      if(Math.floor(shiptiles[i] / 10) < 9){
        if(!grid[shiptiles[i] + 10])
          games[gameId].moveLog.push([who, shiptiles[i] + 10]);
        if(!grid[shiptiles[i] + 10 - 1])
          games[gameId].moveLog.push([who, shiptiles[i] + 10 - 1]);
      }
      if(Math.floor(shiptiles[i] / 10) > 0){
        if(!grid[shiptiles[i] - 10])
          games[gameId].moveLog.push([who, shiptiles[i] - 10]);
        if(!grid[shiptiles[i] - 10 - 1])
          games[gameId].moveLog.push([who, shiptiles[i] - 10 - 1]);
      }
    }
    else {
      if(!grid[shiptiles[i] - 1])
        games[gameId].moveLog.push([who, shiptiles[i] - 1]);
      if(!grid[shiptiles[i] + 1])
        games[gameId].moveLog.push([who, shiptiles[i] + 1]);

      if(Math.floor(shiptiles[i] / 10) < 9){
        if(!grid[shiptiles[i] + 10])
          games[gameId].moveLog.push([who, shiptiles[i] + 10]);
        if(!grid[shiptiles[i] + 10 + 1])
          games[gameId].moveLog.push([who, shiptiles[i] + 10 + 1]);
        if(!grid[shiptiles[i] + 10 - 1])
          games[gameId].moveLog.push([who, shiptiles[i] + 10 - 1]);
      }
      if(Math.floor(shiptiles[i] / 10) > 0){
        if(!grid[shiptiles[i] - 10])
          games[gameId].moveLog.push([who, shiptiles[i] - 10]);
        if(!grid[shiptiles[i] - 10 + 1])
          games[gameId].moveLog.push([who, shiptiles[i] - 10 + 1]);
        if(!grid[shiptiles[i] - 10 - 1])
          games[gameId].moveLog.push([who, shiptiles[i] - 10 - 1]);
      }
    }
  }
}

function updateLeaderboards(){
	//change the LIMIT from 10 to how many players the leaderboard should display
  gamedb.query("SELECT * FROM users WHERE user_wins>0 ORDER BY (user_wins*(user_wins/(user_wins+user_losses))) DESC LIMIT 10", function(err, res){
    if(err) throw err;

    let senddt = [];

    for(let i = 0; i < res.length; i++){
      let winper = Math.round((res[i]['user_wins']*100) / (res[i]['user_wins'] + res[i]['user_losses']));
      if(res[i]['user_sendname'] == 0)
        senddt.push({name: "Χρήστης", wins: res[i]['user_wins'], per: winper});
      else
        senddt.push({name: res[i]['user_name'], wins: res[i]['user_wins'], per: winper});
    }
    io.emit("leaderboards", senddt);
  });
}

function binaryToGrid(grid){
  let o = [];
  for(let i=0; i<100; i++) o.push(grid.charAt(i) == '1');
  return o;
}
function gridToBinary(grid){
  let o = "";
  for(let i = 0; i < 100; i++) o += grid[i] ? '1' : '0';
  return o;
}

var decryptName = function (encryptedName, username) {
  let secret = CryptoJS.MD5(username).toString().substr(0, 32);
  let iv = CryptoJS.MD5(secret).toString().substr(0, 16);
  var decryptor = require('crypto').createDecipheriv('AES-256-CBC', secret, iv);
  let out = 'Χρήστης';
  try {
    out = decryptor.update(encryptedName, 'base64', 'utf8') + decryptor.final('utf8');
    out = Buffer.from(out, 'base64').toString('utf8');
  }
  catch(err){
    out = 'Χρήστης';
  }
  return out;
};

var port = 3030; //server port
app.get('/', function(req, res){res.send('ok');});
app.get('/users', function(req, res){
	let ot = '';
	for (const [key, value] of Object.entries(users)) {
	  ot += key + ' ';
  }
  ot += '<br><br>';
  waitingUsers.forEach(el => ot += el + ' ');
	ot += '<br><br>';
	for (const [key, value] of Object.entries(games)) {
	  ot += value.player1 + '-' + value.player2 + '<br>';
	}
	res.send(ot);
});
http.listen(port, function(){
  console.log('listening on port ' + port);
});
