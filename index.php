<!DOCTYPE html>
<html lang="en">
<head>
	<link rel="icon" href="data:;base64,iVBORw0KGgo=">
	<meta charset="UTF-8">
	<title>Ogame</title>
</head>
<body>
 <div class="container">
    <div class="table">
      <div class="cell" id="0" onclick="checkMove(0)"></div>
      <div class="cell" id="1" onclick="checkMove(1)"></div>
      <div class="cell" id="2" onclick="checkMove(2)"></div>
      <div class="cell" id="3" onclick="checkMove(3)"></div>
      <div class="cell" id="4" onclick="checkMove(4)"></div>
      <div class="cell" id="5" onclick="checkMove(5)"></div>
      <div class="cell" id="6" onclick="checkMove(6)"></div>
      <div class="cell" id="7" onclick="checkMove(7)"></div>
      <div class="cell" id="8" onclick="checkMove(8)"></div>
    </div>
  </div>
  <div style="position: absolute; left: 0; top: 210px;">
 <textarea id="text" cols="10" rows="2"></textarea><br>
	<button onclick="send()">Send</button>
	</div>
	<div id="chat" style="background: white; width: 150px;height: 200px; position: absolute; top: 0;"></div>
<style>
	/* Eric Meyer's Reset CSS v2.0 - http://cssreset.com */
html,body,div,span,applet,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,acronym,address,big,cite,code,del,dfn,em,img,ins,kbd,q,s,samp,small,strike,strong,sub,sup,tt,var,b,u,i,center,dl,dt,dd,ol,ul,li,fieldset,form,label,legend,table,caption,tbody,tfoot,thead,tr,th,td,article,aside,canvas,details,embed,figure,figcaption,footer,header,hgroup,menu,nav,output,ruby,section,summary,time,mark,audio,video{border:0;font-size:100%;font:inherit;vertical-align:baseline;margin:0;padding:0}article,aside,details,figcaption,figure,footer,header,hgroup,menu,nav,section{display:block}body{line-height:1}ol,ul{list-style:none}blockquote,q{quotes:none}blockquote:before,blockquote:after,q:before,q:after{content:none}table{border-collapse:collapse;border-spacing:0}

body {
  background-color: black;
}
.actions {
  text-align: center;
}
.player1 {
  background-image: url('./img/ai.jpg');
}
.container {
  margin: 50px auto;
  width: 300px;
}
.cell {
  background-color: white;
  border: 2px solid black;
  float: left;
  height: 96px;
  width: 96px;
}
.cell:hover {
  border-color: red;
}
.player {
  background-image: url('./img/player.jpg');
}
</style>

	<script>
var chat = document.getElementById('chat');
var t = new Array(9);
var pRole;
var pRole1;
var conn = new WebSocket('ws://localhost:8080');
var oppId;
var turn;
conn.onopen = function(e) {
    chat.innerHTML += "Connection"+"<br>";
};

conn.onmessage = function(e) {
	if (0 <= e.data && e.data <= 8) {
		oppMove(e.data);
	}
	else if(~e.data.indexOf("player")){
		var arr = e.data.split(":");
		pRole = arr[1];
		chat.innerHTML += arr[0]+"<br>";
		oppId = arr[0].split(" - ")[1];
		turn = arr[2];
		if (turn) {
			alert("Your turn");
		}
	}
	else if(e.data == "player:myRole-roleInfo" || e.data == "player1:myRole-roleInfo"){
		var arr = e.data.split(":");
		pRole = arr[0];
	}
	else if(e.data == "player:oppRole-roleInfo" || e.data == "player1:oppRole-roleInfo"){
		var arr = e.data.split(":");
		pRole1 = arr[0];
	}
	else{
    	chat.innerHTML += e.data+"<br>";
	}
};
function send(){
    		var text = document.getElementById('text').value;
    		conn.send(text);
}
function role(){
	if (pRole == "player") {
		return "player1";
	}
	else{
		return "player";
	}
}

function checkEnd() {
  if (t[0]=='player1' && t[1]=='player1' && t[2]=='player1' || t[0]=='player' && t[1]=='player' && t[2]=='player')  return true;
  if (t[3]=='player1' && t[4]=='player1' && t[5]=='player1' || t[3]=='player' && t[4]=='player' && t[5]=='player')  return true;
  if (t[6]=='player1' && t[7]=='player1' && t[8]=='player1' || t[6]=='player' && t[7]=='player' && t[8]=='player')  return true;
  if (t[0]=='player1' && t[3]=='player1' && t[6]=='player1' || t[0]=='player' && t[3]=='player' && t[6]=='player')  return true;
  if (t[1]=='player1' && t[4]=='player1' && t[7]=='player1' || t[1]=='player' && t[4]=='player' && t[7]=='player')  return true;
  if (t[2]=='player1' && t[5]=='player1' && t[8]=='player1' || t[2]=='player' && t[5]=='player' && t[8]=='player')  return true;
  if (t[0]=='player1' && t[4]=='player1' && t[8]=='player1' || t[0]=='player' && t[4]=='player' && t[8]=='player')  return true;
  if (t[2]=='player1' && t[4]=='player1' && t[6]=='player1' || t[2]=='player' && t[4]=='player' && t[6]=='player')  return true;
  if(t[0] && t[1] && t[2] && t[3] && t[4] && t[5] && t[6] && t[7] && t[8]) return true;
}

function checkMove(id){
	if (turn) {
		move(id);
		turn = 0;
	}
}

function move(id) {
	if(t[id]) return false;
  	t[id] = pRole;
  	document.getElementById(id).className = 'cell ' + pRole;
  	conn.send(id + ":" + oppId);
  	if (checkEnd()) {
  		reset();
  	}
  // !checkEnd() ? (role == 'player') ? ai() : null : reset()
}
function oppMove(id){
	pRole1 = role();
	t[id] = pRole1;
	turn = 1;
	document.getElementById(id).className = 'cell ' + pRole1;
	if (checkEnd()) {
  		reset();
  	}
  	alert("Your turn");
}

function reset() {
	conn.send("gameOver"+":"+oppId);
	alert("Игра окончена!");
	location.reload();
}
	</script>
</body>
</html>