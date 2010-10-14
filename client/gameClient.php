<?php
    session_start();
    session_destroy();
?><html>
<head>
	<script src="jquery.js"></script>
	<script src="jquery.ui.js"></script>
	<script>
		   $(document).ready(function(){
		       $('#talker').css('opacity', 0.7);//.draggable();
		   });
		   var letters= Array("A", "B", "C", "D", "E", "F", "G", "H");
		   function sendMsg()
		   {
		       msg= document.getElementById('sms').value;
		       $.ajax({
		           type:'POST',
		           url:'../server/gameServer.php',
		           data:{
		               message:msg,
		               gameId: document.getElementById('gameId').value
		           }
		       });
		       document.getElementById('sms').value= '';
		       document.getElementById('sms').focus();
		       document.getElementById('textArea').innerHTML= ">: You said: "+msg+"\n"+document.getElementById('textArea').innerHTML;
		   }
		   var keepVerifying= true;
		   var gameStarted= false;
		   function verify(gameId, verif)
		   {
		   		var postData= {
		                            gameId:gameId,
		                            userName:user
					  };
		            if(verif)
		                postData.verifying= true;
		   		$.ajax({
		   			type:'POST',
					url:'../server/gameServer.php',
					data:postData,
					success:function(ret){
		                                if(ret!= '>:')
		                                {
		                                    var ta= document.getElementById('textArea');
		                                    if(ret.substring(0,6) == '>:###:')
		                                    {
		                                        ta.innerHTML= "<span style='color:red; font-weight:bold;'>"+ret.replace("###:", '')+"</span><hr/>"+ta.innerHTML.substring(0,1024);
		                                        keepVerifying= false;
		                                        return;
		                                    }
		                                    if(ret.substring(0, 16) == ">:MessageReturn:")
		                                        ta.innerHTML= "<i>"+ret.replace("MessageReturn:", '')+"</i>"+ta.innerHTML.substring(0,1024);
		                                    else{
		                                        if(ret.substring(0,17) == ">:MovimentReturn:")
		                                        {
		                                            var ret2= ret;
		                                            ret= ret.replace('>:MovimentReturn:', '').split('|');
		                                            ret[2]= ret[2].split(',');
		                                            ret[2][1];
		                                            ret[2][0]= letters[ret[2][0]-1];
		                                            ret= "<b>>: "+ret[4]+" moved "+ret[0]+" "+ret[3]+" to "+ret[2][0]+ret[2][1]+"</b>\n";
		                                            ta.innerHTML= ret+ta.innerHTML.substring(0,1024);
		                                            gameRel.tryMove(ret2);
		                                        }else{
		                                        	if(ret!='')
			                                            ta.innerHTML= ret+ta.innerHTML.substring(0,1024);
		                                        }
		                                    }
		                                }
		                                if(keepVerifying)
		                                {
		                                    setTimeout("verify('"+gameId+"', true);", 1000);
		                                }
		                                if(!gameStarted)
		                                {
		                                    document.getElementById('ifr').src= '../games/chess/index.php';
		                                    gameStarted= true;
		                                }
					}
		   		});
		   }

		   top.sendMoviment= function(color, piece, to, el){
		        $.ajax({
		           type:'POST',
		           url:'../server/gameServer.php',
		           data:{
		               gameId: document.getElementById('gameId').value,
		               moviment:'{"color":"'+color+'", "piece":"'+piece+'", "to":"'+to+'", "el":"'+el+'"}'
		           },
		           success: function(ret)
		           {
		               ret= ret.replace('>: MovimentReturn ', '').split('|');
		               ret[2]= ret[2].split(',');
		               ret[2][1];
		               ret[2][0]= letters[ret[2][0]-1];
		               ret= ">: You moved "+ret[0]+" "+ret[3]+" to "+ret[2][0]+ret[2][1];
		               document.getElementById('textArea').innerHTML= ret+"\n"+document.getElementById('textArea').innerHTML;
		           }
		       });
		   };

		   var user= null;
		   function startANewGame(el)
		   {
			   user= document.getElementById('userName').value;
			   if(user.replace(/ /g, '') == '')
			   	return;
			   if(!document.getElementById('playAlone').checked)
			   {
				   keepVerifying= true;
				   verify(document.getElementById('gameId').value);
			   }else{
				   	document.getElementById('ifr').src= '../games/chess/index.php';
				   	document.getElementById('textArea').innerHTML= user + " playing with the machine";
				   	keepVerifying= false;
			   }
		       //document.getElementById('gameId').disabled= 'disabled';
		       //document.getElementById('userName').disabled= 'disabled';
		       //el.disabled= 'disabled';
		       document.getElementById('ifr').parentNode.style.top= '4px';
		       document.getElementById('ifr').parentNode.style.display= '';
		       document.getElementById('talker').style.top= '2px';
		       document.getElementById('talker').style.display= '';
		       //document.getElementById('ifr').src= '../games/chess/index.php';
		       document.getElementById('form').style.display='none';
		   }
		   
		   function showHelp()
		   {
		   		var msg= "Hi there!\nThis keyword will allow you to get into a game that already started.\n";
		   		msg+= "If you want to create a new game, then ignore this field.\n";
		   		msg+= "When you start your game, a new key will be given to you invite a friend of yours, simply giving him the same key to type here\n\n";
		   		msg+= "IMPORTANT: your user name and his must NOT be the same.";
		   		alert(msg);
		   }
		   function alertInfo()
		   {
			   	alert("It uses PHP to run, so, you simply need to extract the zip package in your server folder and access it in your browser\nIt does not require any plugin or add-on.");
		   }
	</script>
	<?php
		if(isset($_SESSION['gameId']))
		{
		    try
		    {
			    @unlink("../server/games/".$_SESSION['gameId'].".json");
			    @session_destroy();
			}catch(Exception $e){}
		    //session_destroy();
		}
	?>
		<style type='text/css'>
			*{
				margin:0px;
				font-family: Arial, Tahoma, Sans-Serif;
				color:#444;
			}
			input[type=text]
			{
				background-colorR:white;
				color:#444;
				font-style:italic;
				border: solid 1px #99f;
			}
			a
			{
				text-decoration:none;
				font-weight:bold;
				border-bottom:dashed 1px #999;
				color:#999;
			}
			a:hover
			{
				text-decoration:underline;
				font-weight:bold;
				border-bottom:solid 1px #999;
				color:#777;
			}
			#form
			{
				width:780px;
				margin:auto;
				margin-top:50px;
				text-align:left;
				padding:16px;
				padding-top:16px;
				background-image:url(bg.jpg);
				background-repeat:no-repeat;
				border:solid 1px #444;
				border-radius: 8px;
				-webkit-border-radius:16px;
				-moz-border-radius:16px;
				-o-border-radius:16px;
				
				box-shadow: 0px 0px 16px #444;
				-webkit-box-shadow: 0px 0px 16px #444;
				-moz-box-shadow: 0px 0px 16px #444;
				
				-webkit-transform:matrix(1, 0, -0, 0.9, 0, -30);
			    -webkit-transform-origin:50% 50%;
			    
   				-moz-transform:matrix(1, 0, -0, 0.9, 0, -30);
			    -moz-transform-origin:50% 50%;
			    
   				-o-transform:matrix(1, 0, -0, 0.9, 0, -30);
			    -o-transform-origin:50% 50%;
			}
			#form>.form
			{
				text-align:center;
				padding-bottom:8px;
				margin:auto;
				width:680px;
				border-bottom:dashed 1px #d0d0d0;
			}
			#content div
			{
				padding-left:90px;
				padding-top:10px;
				padding-bottom:20px;
			}
		</style>
	</head>
	<body>
      <div id="form">
      	<div class='form'>
          User name<input type="text" value="" id="userName" />
          Game keyword <a href="Javascript:showHelp();void(0);">(?)</a>
          <input type="text" value="" name="gameId" id="gameId" />
		  <span style='display:none;'>UNDER CONSTRUCTION<input type='checkbox' id='playAlone'> Play alone</span>
          <input type="button" onclick="startANewGame(this)" value="Let's play" />
          <!--<input type="button" onclick="keepVerifying= !keepVerifying;">-->
        </div>
          <div id='content'>
		      <div>
				  Type your name in the first field, and click que button to start a new game.<br/>
				  A code will be generated and you can pass this code to your friend play with you.<br/>
				  If you are the friend who has got a code, simply fill the second field with this code to get into the same game with your friend, who started it.<br/>
				  If you want, you can send me an e-mail with ideas or bugs: felipenmoura@gmail.com - <a href='http://felipenascimento.org' target='_quot'>http://felipenascimento.org</a>
		      </div>
		      <hr/>
		      This game has been created by Felipe Nascimento de Moura during one weekend, when Felipe wasn't busy with the university stuff.<br/>
		      You can <a href='http://felipenascimento.org/projetos/chessMate/chessMate.zip' onclick="alertInfo()">download</a> it, change it and redistribute it as you wish, simply refering Felipe in your code. It is under the GPL license.<br/>
		      This game uses up to date technologies, so, it will NOT run in your poor Internet Explorer, sorry.
		      <hr/>
		      I'm still working on the inteligence of this game, to allow you to play agains your browser(an engine in javascript). Actualy, it is already working, but it is not smart, not playing very well :p<br/>
		      If you want to, you can even donate something<br/>
		      <center>
		      <table style="">
					<tbody><tr>
						<td>
							<center>
								<!-- INICIO FORMULARIO BOTAO PAGSEGURO -->
									<form target="pagseguro" action="https://pagseguro.uol.com.br/security/webpagamentos/webdoacao.aspx" method="post" style="margin: 0px;
												 padding: 0px;
												 padding-left: 3px;">
										<input type="hidden" name="email_cobranca" value="felipenmoura@gmail.com">
										<input type="hidden" name="moeda" value="BRL">
										<input type="image" src="https://pagseguro.uol.com.br/Security/Imagens/FacaSuaDoacao.gif" name="submit" alt="Pague com PagSeguro - é rápido, grátis e seguro!">
									</form>
								<!-- FINAL FORMULARIO BOTAO PAGSEGURO -->
							</center>
						</td>
						<td>
							<center>
								<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
									<input type="hidden" name="cmd" value="_s-xclick">
									<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
									">
									<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
									<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
								</form>
							</center>
						</td>
					</tr>
				</tbody>
			</table>
			</center>
          </div>
      </div>
      <div style="float:left;
                  width:200px;
                  position:absolute;
                  left:10px;
                  top:40px;
                  z-index:999;
                  border:solid 1px #444;
                  background-color:#fff;
                  display:none;
                  -moz-border-radius:4px;
                  -webkit-border-radius:4px;"
           id="talker">
          <div id="textArea"
               style="height:200px;
                      white-space: pre-wrap;
                      overflow:auto;
                      border:solid 1px #777;
                      background-color:#d0d0d0;
                      -moz-border-radius:4px;
                      border-radius:4px;
                      -webkit-border-radius:4px;"></div>
          <center>
              <input type="text"
                     id="sms"
                     style="width:150px;"
                     onkeyup="if(event.keyCode==13) sendMsg()"/>
              <!--<input type="button" style="width:40px;" value="ok" onclick="sendMsg()" />-->
              <div style="border:solid 1px #000; background-color:white; width:30px; height:20px; float:right; margin:2px;" onclick="$('#textArea').toggle();" id="whose"></div>
          </center>
      </div>
      <div style="width:920px;
                  height:610px;
                  border:solid 1px #777;
                  position:absolute;
                  left:10px;
                  top:40px;
                  display:none;
                  z-index:1"
           id='gameFather'>
          <iframe id="ifr" name="ifr" style="width:100%; height:100%; border:0;">
          </iframe>
      </div>
	</body>
<script type="text/javascript"> 
 
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-1270869-12']);
  _gaq.push(['_trackPageview']);
 
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
 
</script>
</html>
