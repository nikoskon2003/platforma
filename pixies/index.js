let img = document.getElementById('img');
let result = document.getElementById('img-2');

img.src = "img.png";

var lens, cx, cy;

lens = document.createElement("DIV");
lens.setAttribute("class", "zoom-lens");
img.parentElement.insertBefore(lens, img);

cx = result.offsetWidth / lens.offsetWidth;
cy = result.offsetHeight / lens.offsetHeight;

result.style.backgroundImage = "url('" + img.src + "')";
result.style.backgroundSize = (img.width * cx) + "px " + (img.height * cy) + "px";

//init a random X and Y
let MainX = Math.round(Math.random() * 222), MainY = Math.round(Math.random() * 222);
lens.style.left = (MainX + img.offsetLeft) + "px";
lens.style.top = (MainY + img.offsetTop) + "px";
result.style.backgroundPosition = "-" + (MainX * cx) + "px -" + (MainY * cy) + "px";
MainX = MainX + Math.round(MainX / 128);
MainY = MainY + Math.round(MainY / 128);

function moveLens(e) {
    e.preventDefault();

    var pos, x, y;
    pos = getCursorPos(e);
    x = pos.x - (lens.offsetWidth / 2);
    y = pos.y - (lens.offsetHeight / 2);

    x = Math.max(Math.min(Math.ceil(x), 222), 0);
    y = Math.max(Math.min(Math.ceil(y), 222), 0);

    lens.style.display = "block";
    lens.style.left = (x + img.offsetLeft) + "px";
    lens.style.top = (y + img.offsetTop) + "px";
    result.style.backgroundPosition = "-" + (x * cx) + "px -" + (y * cy) + "px";
    
    MainX = x + Math.round(x / 128);
    MainY = y + Math.round(y / 128);

    document.getElementById("selector").style.display = "none";
}

window.addEventListener("resize", () => {
    lens.style.left = (MainX + img.offsetLeft) + "px";
    lens.style.top = (MainY + img.offsetTop) + "px";
});

function getCursorPos(e) {
    var xCoordinate = event.offsetX;
    var yCoordinate = event.offsetY;

    var img = document.getElementById('img');

    var xRatio = img.clientWidth / img.width;
    var yRatio = img.clientHeight / img.height;

    let x = xCoordinate/xRatio;
    let y = yCoordinate/yRatio;

    return {x : x, y : y};
}

let selectedX = -1;
let selectedY = -1;

function getSecImagePos(event) {
    var xCoordinate = event.offsetX;
    var yCoordinate = event.offsetY;

    var img = document.getElementById('img-2');

    var xRatio = img.clientWidth / 32;
    var yRatio = img.clientHeight / 32;

    let xPos = Math.max(Math.min(Math.floor(xCoordinate/xRatio), 31), 0);
    let yPos = Math.max(Math.min(Math.floor(yCoordinate/yRatio), 31), 0);

    let sel = document.getElementById("selector");

    sel.style.display = "block";

    sel.style.width = Math.ceil(xRatio) + 'px';
    sel.style.height = Math.ceil(yRatio) + 'px';

    sel.style.left = xPos * xRatio + "px";
    sel.style.top = yPos * yRatio + "px";

    selectedX = MainX + xPos;
    selectedY = MainY + yPos;
}

let selectedColor = -1;
function selectColor(id){
    if(selectedColor != -1){
        let col = document.getElementsByClassName('col')[selectedColor];
        col.classList.remove("sel");
    }
    selectedColor = id;

    let col = document.getElementsByClassName('col')[id];
    col.classList.add("sel");
}


function submitColor(){
    if(selectedX < 0 || selectedX > 255 || selectedY < 0 || selectedY > 255 || selectedColor < 0 || selectedColor > 15) return;

    if(timeleft - Math.round(Date.now() / 1000) > 0) return;

    var data = new FormData();
    data.append('col', selectedColor);
    data.append('x', selectedX);
    data.append('y', selectedY);

    var xhr = new XMLHttpRequest();

    xhr.open('POST', './send.php', true);
    xhr.onload = function(e) {
        timeSent = Math.round(Date.now() / 1000);
        handelWait(e.currentTarget.responseText);
    }
    xhr.send(data);
    document.getElementById("selector").style.display = "none";
}

let timeSent = 0;
let timeleft = 0;
function handelWait(t){
    //console.log(t);

    if(isNaN(t)) return;
    t = parseInt(t);
    if(t == -1) return;
    timeleft = t;
    document.getElementById("submitButton").disabled = true;
    updateTimer();
}

function updateTimer(){
    let now = Math.round(Date.now() / 1000);
    if(timeleft - (now - timeSent) > 0){
        document.getElementById("submitButton").innerHTML = timeleft - (now - timeSent) + 's';
        setTimeout(updateTimer, 2);
    }
    else
    { 
        document.getElementById("submitButton").innerHTML = "Αποστολή";
        document.getElementById("submitButton").disabled = false;
    }
}

document.getElementsByTagName("BODY")[0].onload = () => {
    var data = new FormData();
    data.append('col', -1);
    data.append('x', -1);
    data.append('y', -1);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', './send.php', true);
    xhr.onload = function(e) {
        timeSent = Math.round(Date.now() / 1000);
        handelWait(e.currentTarget.responseText);
    }
    xhr.send(data);
}

//find out how much time does it take to load an image.
var startTime = new Date().getTime();
var t_img = new Image();
t_img.onload = function() {
    var loadtime = new Date().getTime() - startTime;
    //Javascript Magic!
    setInterval(
        async function updateImages(){
            let d = new Date().getTime();
            await addMainImage("img.png?" + d);
            result.style.backgroundImage = "url('" + img.src + "')";
        }, Math.max(loadtime * 2, 2000));
    function addMainImage(src){
        return new Promise((resolve, reject) => {
            img.onload = () => resolve(img.src);
            img.onerror = reject;
            img.src = src;
        });
    }
};
t_img.src = "img.png?" + startTime;