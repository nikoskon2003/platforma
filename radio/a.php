<!DOCTYPE html>
<meta charset="utf-8" />
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>aaa</title>
	
	
	<!-- This was a test file. Decided to leave it in! -->
	
	
</head>

<body>
    <div class="l-radio-holder">
		<div class="l-radio-img">
			<img src="./resources/radio/radio.gif" />
		</div>
		<div class="l-radio-button">
			<img src="./resources/radio/play.png" id="live-radio-play" onclick="ToggleRadio();" />
		</div>
		<audio id="play-music"></audio>
		<style>
			.l-radio-holder{position:fixed;left:10px;bottom:10px;z-index:99999;user-select:none;background-color:#59b1ea;border-radius:6px;}
			.l-radio-img{width:55px;height:55px;text-align:center;display:inline-block;vertical-align:top;}
			.l-radio-img img{width:40px;height:40px;margin:5px;}
			.l-radio-button{margin: 0 auto;display:inline-block;height:55px;text-align:center;vertical-align:top;margin-right:5px;}
			.l-radio-button img{width:35px;height:35px;cursor:pointer;margin-top:10px;}
		</style>
		<script>
		const audio=document.getElementById('play-music');const playIcon=document.getElementById('live-radio-play');let prevtime=0;let cnter=-1;let loading=true;
		function ToggleRadio(){
			if (!audio.getAttribute("src"))
			{audio.setAttribute("src", "http<?= empty($_SERVER['HTTPS'])?'':'s'?>://<?= $_SERVER['HTTP_HOST']; ?>:<?= empty($_SERVER['HTTPS'])?'8000':'8443'?>/stream.ogg");
				audio.load();audio.muted=false;audio.play();playIcon.src="./resources/radio/pause.png";loading=true;}
			else if(!audio.muted){audio.muted=true;playIcon.src="./resources/radio/play.png";}
			else{audio.muted=false;audio.play();playIcon.src="./resources/radio/pause.png";}
		}
		audio.addEventListener('timeupdate',(e)=>{cnter++;if(cnter!=prevtime)loading=false;});
		setInterval(function(){
			if(prevtime==cnter&&!loading){
				console.log('reloading source');
				audio.setAttribute("src", "http<?= empty($_SERVER['HTTPS'])?'':'s'?>://<?= $_SERVER['HTTP_HOST']; ?>:<?= empty($_SERVER['HTTPS'])?'8000':'8443'?>/stream.ogg");
				audio.load();audio.play();loading=true;
			}if(!loading)prevtime=cnter;
		},2000);
		</script>
	</div>
	
</body>
</html>