function generateGrid(who){
    let grid = document.getElementById((who == 0) ? 'own-grid' : 'opp-grid');

    for (var i=0; i<100; i++){
        let tile = document.createElement("div");
        tile.classList.add('tile');

        tile.grididx = i;

        if(who == 0){
            tile.addEventListener('click', placeShip);
            tile.addEventListener('touchcancel', placeShip);
        }
        else if(who == 1){
            tile.addEventListener('click', attackTile);
            tile.addEventListener('touchcancel', attackTile);
        }

        grid.appendChild(tile);
    }
}

let playergrid = genemptgrid();

function placeShip(e){
    let position = e.currentTarget.grididx;

    if(!checkIfValidPlacement(position)){
        showBadPlacement(e.currentTarget);
        return;
    }
    let t = 0;
    playergrid.forEach(el => t += el ? 1 : 0);
    if(t >= 20 && !playergrid[position]){
        showBadPlacement(e.currentTarget);
        return;
    }
    
    playergrid[position] ^= true;

    if(playergrid[position])
        e.currentTarget.classList.add('good-tile');
    else
        e.currentTarget.classList.remove('good-tile');

    updateShipStatus();
}

function showBadPlacement(el){
    el.classList.add('bad-tile');
    setTimeout(() => {el.classList.remove('bad-tile');}, 999)
}

function checkIfValidPlacement(pos){
    if(playergrid[pos]) return true;

    if(pos % 10 == 0){
        if(Math.floor(pos / 10) == 0){
            //top left
            if(playergrid[pos + 1 + 10]) return false;
        }
        else if(Math.floor(pos / 10) == 9){
            //bottom left
            if(playergrid[pos + 1 - 10]) return false;
        }
        else {
            if(playergrid[pos + 1 + 10]) return false;
            if(playergrid[pos + 1 - 10]) return false;
        }
    }
    else if(pos % 10 == 9){
        if(Math.floor(pos / 10) == 0){
            //top right
            if(playergrid[pos - 1 + 10]) return false;
        }
        else if(Math.floor(pos / 10) == 9){
            //bottom right
            if(playergrid[pos - 1 - 10]) return false;
        }
        else {
            if(playergrid[pos - 1 + 10]) return false;
            if(playergrid[pos - 1 - 10]) return false;
        }
    }
    else {
        if(Math.floor(pos / 10) == 0){
            //top
            if(playergrid[pos - 1 + 10]) return false;
            if(playergrid[pos + 1 + 10]) return false;
        }
        else if(Math.floor(pos / 10) == 9){
            //bottom
            if(playergrid[pos - 1 - 10]) return false;
            if(playergrid[pos + 1 - 10]) return false;
        }
        else {
            if(playergrid[pos + 1 + 10]) return false;
            if(playergrid[pos + 1 - 10]) return false;
            if(playergrid[pos - 1 + 10]) return false;
            if(playergrid[pos - 1 - 10]) return false;
        }
    }

    playergrid[pos] = true;
    if(getShipSize(pos) > 4){
        playergrid[pos] = false;
        return false;
    }
    playergrid[pos] = false;

    return true;
}

function updateShipStatus(){
    let ships = getShipCount();
    document.querySelectorAll('.ship-cnt.carrier').forEach(el => {
        el.innerHTML = ships[3] + '/1'; 
        if(ships[3] == 1) el.style.color = 'green';
        else el.style.color = 'red';
    });
    document.querySelectorAll('.ship-cnt.battleship').forEach(el => {
        el.innerHTML = ships[2] + '/2'; 
        if(ships[2] == 2) el.style.color = 'green';
        else el.style.color = 'red';
    });
    document.querySelectorAll('.ship-cnt.submarine').forEach(el => {
        el.innerHTML = ships[1] + '/3'; 
        if(ships[1] == 3) el.style.color = 'green';
        else el.style.color = 'red';
    });
    document.querySelectorAll('.ship-cnt.patrol').forEach(el => {
        el.innerHTML = ships[0] + '/4'; 
        if(ships[0] == 4) el.style.color = 'green';
        else el.style.color = 'red';
    });

    if(ships[0] == 4 && ships[1] == 3 && ships[2] == 2 && ships[3] == 1){
        document.querySelectorAll('.enterqueuebutton').forEach(el => el.disabled = false);
        document.querySelectorAll('.createroombutton').forEach(el => el.disabled = false);
        document.querySelectorAll('.inputroomcode').forEach(el => el.disabled = false);
    }
    else{
        document.querySelectorAll('.enterqueuebutton').forEach(el => el.disabled = true);
        document.querySelectorAll('.createroombutton').forEach(el => el.disabled = true);
        document.querySelectorAll('.inputroomcode').forEach(el => el.disabled = true);
    }
    
}

function getShipCount(){
    let ships = [0, 0, 0, 0];
    
    for(let i = 0; i < 100; i++){
        let ss = getShipSize(i);
        if(ss > 0)
            ships[ss - 1]++;
    }

    ships[1] /= 2;
    ships[2] /= 3;
    ships[3] /= 4;
    
    return ships;
}

function getShipSize(pos, from = null){
    if(pos >= 100 || pos < 0 || !playergrid[pos]) return 0;
    let tot = 1;

    if(pos % 10 < 9 && pos + 1 != from)
        tot += getShipSize(pos + 1, pos);
    if(pos % 10 > 0 && pos - 1 != from)
        tot += getShipSize(pos - 1, pos);
    if(Math.floor(pos / 10) < 9 && pos + 10 != from)
        tot += getShipSize(pos + 10, pos);
    if(Math.floor(pos / 10) > 0 && pos - 10 != from)
        tot += getShipSize(pos - 10, pos);
    
    return tot;
}

function gridToBinary(){
    let o = "";
    for(let i = 0; i < 100; i++) o += playergrid[i] ? '1' : '0';
    return o;
}

function placeGridFromBinary(grid){
    let gridpar = document.getElementById("own-grid");
    for(let i=0; i<100; i++){
        if(grid.charAt(i) == '1'){
            playergrid[i] = true;
            gridpar.childNodes[i].classList.add('good-tile');
        }
        else {
            playergrid[i] = false;
            gridpar.childNodes[i].classList.remove('good-tile');
        }
    }
    updateShipStatus();
}

function clearGrid(){
    playergrid = genemptgrid();
    document.querySelectorAll('.tile').forEach(el => el.className = 'tile');
    updateShipStatus();
}

let attacks = genemptgrid();
function attackTile(e){
    let pos = e.currentTarget.grididx;
    if(!attacks[pos]){
        sendAttack(pos);
    }
}
function awknowledgeAttack(tile){
    attacks[tile] = true;
}

function sinkTile(pos, par, awkn){
    par.childNodes[pos].className = 'tile sunk';

    if(pos % 10 == 0){
        if(awkn) awknowledgeAttack(pos + 1);
        if(par.childNodes[pos + 1].classList.length == 1)
            par.childNodes[pos + 1].classList.add('miss');
        
        if(Math.floor(pos / 10) > 0){
            if(awkn) awknowledgeAttack(pos - 10);
            if(par.childNodes[pos - 10].classList.length == 1)
                par.childNodes[pos - 10].classList.add('miss');

            if(awkn) awknowledgeAttack(pos - 10 + 1);
            if(par.childNodes[pos - 10 + 1].classList.length == 1)
                par.childNodes[pos - 10 + 1].classList.add('miss');
        }

        if(Math.floor(pos / 10) < 9){
            if(awkn) awknowledgeAttack(pos + 10);
            if(par.childNodes[pos + 10].classList.length == 1)
                par.childNodes[pos + 10].classList.add('miss');
            
            if(awkn) awknowledgeAttack(pos + 10 + 1);
            if(par.childNodes[pos + 10 + 1].classList.length == 1)
                par.childNodes[pos + 10 + 1].classList.add('miss');
        }
    }
    else if(pos % 10 == 9){
        if(awkn) awknowledgeAttack(pos - 1);
        if(par.childNodes[pos - 1].classList.length == 1)
            par.childNodes[pos - 1].classList.add('miss');
        
        if(Math.floor(pos / 10) > 0){
            if(awkn) awknowledgeAttack(pos - 10);
            if(par.childNodes[pos - 10].classList.length == 1)
                par.childNodes[pos - 10].classList.add('miss');

            if(awkn) awknowledgeAttack(pos - 10 - 1);
            if(par.childNodes[pos - 10 - 1].classList.length == 1)
                par.childNodes[pos - 10 - 1].classList.add('miss');
        }

        if(Math.floor(pos / 10) < 9){
            if(awkn) awknowledgeAttack(pos + 10);
            if(par.childNodes[pos + 10].classList.length == 1)
                par.childNodes[pos + 10].classList.add('miss');
            
            if(awkn) awknowledgeAttack(pos + 10 - 1);
            if(par.childNodes[pos + 10 - 1].classList.length == 1)
                par.childNodes[pos + 10 - 1].classList.add('miss');
        }
    }
    else {
        if(awkn) awknowledgeAttack(pos - 1);
        if(par.childNodes[pos - 1].classList.length == 1)
            par.childNodes[pos - 1].classList.add('miss');

        if(awkn) awknowledgeAttack(pos + 1);
        if(par.childNodes[pos + 1].classList.length == 1)
            par.childNodes[pos + 1].classList.add('miss');
    
        if(Math.floor(pos / 10) > 0){
            if(awkn) awknowledgeAttack(pos - 10);
            if(par.childNodes[pos - 10].classList.length == 1)
                par.childNodes[pos - 10].classList.add('miss');
            
            if(awkn) awknowledgeAttack(pos - 10 - 1);
            if(par.childNodes[pos - 10 - 1].classList.length == 1)
                par.childNodes[pos - 10 - 1].classList.add('miss');

            if(awkn) awknowledgeAttack(pos - 10 + 1);
            if(par.childNodes[pos - 10 + 1].classList.length == 1)
                par.childNodes[pos - 10 + 1].classList.add('miss');
        }

        if(Math.floor(pos / 10) < 9){
            if(awkn) awknowledgeAttack(pos + 10);
            if(par.childNodes[pos + 10].classList.length == 1)
                par.childNodes[pos + 10].classList.add('miss');
            
            if(awkn) awknowledgeAttack(pos + 10 - 1);
            if(par.childNodes[pos + 10 - 1].classList.length == 1)
                par.childNodes[pos + 10 - 1].classList.add('miss');

            if(awkn) awknowledgeAttack(pos + 10 + 1);
            if(par.childNodes[pos + 10 + 1].classList.length == 1)
                par.childNodes[pos + 10 + 1].classList.add('miss');
        }
    }
}

function resetGrids(){
    attacks = genemptgrid();
    document.querySelectorAll('.tile').forEach(el => el.className = 'tile');
    placeGridFromBinary(gridToBinary());
}

function genemptgrid(){
    let o = [];
    for(let i=0; i<100; i++) o.push(false);
    return o;
}

generateGrid(0);
generateGrid(1);
updateShipStatus();