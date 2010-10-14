<?php
    //session_start();
    $color= $_SESSION['color'];
?>
<script src="../../client/jquery.ui.js"></script>
<script>
	
</script>
<div id='messageDiv'
	 onclick="top.location.href= top.location.href;"
	 style='position:absolute;
	 		left:240px;
	 		top:100px;
	 		z-index:99999;
	 		border:solid 1px #000;
	 		width:340px;
	 		height:80px;
	 		padding-top:30px;
	 		display:none;
	 		text-align:center;
	 		font-size:50px;
	 		font-weight:bold;
	 		background-color:#fff;
	 		cursor:pointer;
			-moz-box-shadow: 0px 0px 8px #000;
			-webkit-box-shadow: 0px 0px 8px #000;
			-o-box-shadow: 0px 0px 8px #000;'><br/></div>

<div id='blocker'
	 style='text-align:center;
	 		background-color:#e0e0e0;
	 		position:absolute;
	 		left:0px;
	 		color:black;
	 		top:0px;
	 		z-index:999999;
	 		width:2048px;
	 		height:1024px;'>
	 Loading...
</div>
<script>
    var totalMoves= 0;
    var who= 'white';

    function win()
    {
        var el= document.getElementById('messageDiv');
        el.innerHTML= "You win!";
        el.style.display= '';
        el.style.top= (document.body.clientHeight/2) - (el.offsetHeight/2);
        el.style.left= (document.body.clientWidth/2) - (el.offsetWidth/2);
        el.style.backgroundColor= '#afa';
        top.keepVerifying= false;
        who= false;
    }
    function loose()
    {
        var el= document.getElementById('messageDiv');
        el.innerHTML= "You loose!";
        el.style.display= '';
        el.style.top= (document.body.clientHeight/2) - (el.offsetHeight/2);
        el.style.left= (document.body.clientWidth/2) - (el.offsetWidth/2);
        el.style.backgroundColor= '#f88';
        who= false;
        top.keepVerifying= false;
    }

    var table={
            places: Array(),
            lines: Array(),
            cols: Array(),
            selected: null
        };

        var knight=[
                [2, 1],
                [2, -1],
                [1, 2],
                [1, -2],
                [-2, 1],
                [-2, -1],
                [-1, 2],
                [-1, -2]
        ];

        var pieces= Array();
        pieces['white']= 16;
        pieces['black']= 16;
        var lost= false;

        function desSelect()
        {
            $(table.selected).removeClass('selectedColl');
            table.selected= null;
            $('.possibilities').removeClass('possibilities');
        }

		var machinePieces= false;
		var mPossibilities= Array();
		var bestIdea= Array();
        function machineTurn()
        {
			if(!machinePieces)
				machinePieces= $('[color=black]');
			mPossibilities= Array();
			var tmpObj= null;
			bestIdea= Array();
			secondBestChoice= Array();
			for(var i=0, j=machinePieces.length; i<j; i++)
			{
				if(!machinePieces[i] || !document.getElementById(machinePieces[i].id))
				{
					machinePieces[i]= false;
					continue;
				}
				// para cada peça
				tmpPossibilities= getPossibilities(machinePieces[i])
				tmpObj= {
							piece:machinePieces[i].id,
							possibilities: Array(),
							bestChoice:null,
							secondBest:null
						};
				bestChoice= false;
				//alert(machinePieces[i].getAttribute('piece') +'\n'+ tmpPossibilities);
				// para cada possibilidade de cada peça
				if(tmpPossibilities)
				{
					//alert(tmpPossibilities.length+'\n'+tmpPossibilities);
					for(var k=0; k<tmpPossibilities.length; k++)
					{
						//alert(k+'\n'+tmpPossibilities[k]+'\n'+tmpPossibilities[k].tagName)
						if(tmpPossibilities[k].lastChild.tagName!='IMG')
						{
							// nao ha ninguem para comer
							if(!tmpObj.secondBest)
							{
								tmpObj.secondBest= tmpObj;
							}
							secondBestChoice.push(tmpObj);
							l= 0;
						}else{ // ha possibilidades de comer alguem \o/
								l= tmpPossibilities[k].lastChild.getAttribute('level');
								if(!bestChoice || bestChoice.lastChild.getAttribute('level') < l)
								{
									bestChoice= tmpPossibilities[k];
									tmpObj.bestChoice= tmpPossibilities[k];
								}
									//alert(bestIdea +'\n'+ bestIdea.bestChoice);
								//if(!bestIdea[l] || machinePieces[i].getAttribute('level') < l)
								//{
									if(bestIdea[l])
									{
										// a peça de menor peso deverá comer, caso hajam duas com a mesma possibilidade
										// por exemplo, um bispo e um peao podem comer uma torre. Entao, o peao quem deverá comer
										var p= document.getElementById(bestIdea[l].piece).getAttribute('level');
										if(p > machinePieces[i].getAttribute('level'))
										{
											bestIdea[l]= tmpObj;
											tmpObj.bestChoice= tmpObj;
										}
										//bestIdea[l]= Math.ceil(Math.random()*3)%2==0? bestIdea[l]:tmpObj;
									}else{
										bestIdea[l]= tmpObj;
										tmpObj.bestChoice= tmpObj;
									}
								//}

							}
						tmpObj.possibilities.push({place:tmpPossibilities[k], level:l});
					}
				}
				mPossibilities.push(tmpObj);
			}
			if(bestIdea.length == 0)
			{
				if(secondBestChoice.length == 0)
					win(); // cheque mate
				else
				{
					var k= Math.ceil(Math.random()*secondBestChoice.length-1);
					k= secondBestChoice[k];
					var k2= Math.ceil(Math.random()*k.possibilities.length-1);
					tryMove('black|'+k.piece+'|'+$(k.possibilities[k2].place).data('ref'));
				}
			}else
			{
				var k= bestIdea[bestIdea.length-1];
				var k2= Math.ceil(Math.random()*k.possibilities.length-1);
				tryMove('black|'+k.piece+'|'+$(k.possibilities[k2].place).data('ref'));
			}
			//alert(bestIdea);//+'\n'+ document.getElementById(bestIdea.piece).getAttribute('piece')+"\n");
				//$(bestIdea.bestChoice).data('ref')+"\n"+$(bestIdea.bestChoice).data('ref').lastChild.getAttribute('piece'));
			//tryMove('black|'+bestIdea.piece+'|'+$(bestIdea.bestChoice).data('ref'));
        }

		function getPossibilities(piece, show)
		{
			if(table.selected)
			{
				if(table.selected == piece.parentNode)
				{
					desSelect();
					return;
				}
				desSelect();
			}
			table.selected= piece.parentNode;
			if(show)
				$(table.selected).addClass("selectedColl");
			var selectedPiece= piece;
			var cur= $(table.selected).data('ref');
			var endPoint;
			var cell;
			var ret= Array();
			switch(selectedPiece.getAttribute('piece'))
			{
				case 'knight': // knight moviments // movimentos do cavalo
								for(var i=0; i<knight.length; i++)
								{
									if(table.places[ cur[0] + knight[i][0] ]
									   &&
									   table.places[ cur[0] + knight[i][0] ][ cur[1] + knight[i][1]]
									   &&
									   $(table.places[ cur[0] + knight[i][0] ][ cur[1] + knight[i][1]]).hasClass('tableCell')
								   )
									{
										cell= table.places[ cur[0] + knight[i][0] ][ cur[1] + knight[i][1]];
										endPoint= cell.lastChild;
										if(endPoint.tagName!='IMG' || endPoint.getAttribute('color')!= who)
										{
											if(show)
												$(cell).addClass('possibilities');
											else{
												ret.push(cell);
											}
										}
									}
								}
						break;
				case 'pawn': // pawn moviments // movimentos do peao
							if(who == 'white')
							{
								var curLine= cur[0] - 1;
								var curLine2= cur[0] - 2;
							}else{
								var curLine= cur[0] + 1;
								var curLine2= cur[0] + 2;
							}
							
							if(
								table.places[ curLine ] && table.places[ cur[0]-1 ][cur[1]]
							  )
							{
								cell= table.places[ curLine ][cur[1]];
								/*if(cell.lastChild.tagName == 'IMG' && cell.lastChild.getAttribute('color') == '<?php echo $color; ?>')
									return;*/
								if(cell.lastChild.tagName != 'IMG')
								{
									if(show)
										$(cell).addClass('possibilities');
									else
										ret.push(cell);

									// se for o primeiro passo, então pode andar duas casas
									if( selectedPiece.getAttribute('firstMove')
										&&
										table.places[ curLine2 ][cur[1]].lastChild.tagName != 'IMG')
									{
										if(show)
											$(table.places[ curLine2 ][cur[1]]).addClass('possibilities');
										else
											ret.push(table.places[ curLine2 ][cur[1]]);
									}
								}
								// looking for victims // vendo se pode comer alguem
								// left // esquerda
								if(table.places[curLine] && table.places[cur[0] - 1][ cur[1] -1 ]
								   &&
								   table.places[curLine][ cur[1] -1 ].lastChild.tagName=='IMG'
								   &&
								   table.places[curLine][ cur[1] -1 ].lastChild.getAttribute('color') != who)
								{
								   // alert(table.places[cur[0] - 1][ cur[1] -1 ].lastChild);
									if(show)
										$(table.places[curLine][ cur[1] -1 ]).addClass('possibilities');
									else
										ret.push(table.places[curLine][ cur[1] -1 ]);
								}
								// right // direita
								if(table.places[curLine] && table.places[curLine][ cur[1] +1 ]
								   &&
								   table.places[curLine][ cur[1] +1 ].lastChild.tagName=='IMG'
								   &&
								   table.places[curLine][ cur[1] +1 ].lastChild.getAttribute('color') != who)
								{
								   // alert(table.places[cur[0] - 1][ cur[1] -1 ].lastChild);
								    if(show)
										$(table.places[curLine][ cur[1] +1 ]).addClass('possibilities');
									else
										ret.push(table.places[curLine][ cur[1] +1 ]);
								}
							}
						break;
				case 'rock': // rock moviments // movimentos da torre
				case 'king':
				case 'queen': // queen and king also has these moviments // rei e rainha também tem estes movimentos
							// cross moviment
							// going left
							for(var i=1; i<cur[1]; i++)
							{
								if(table.places[cur[0]][ cur[1]-i ])
								{
									if(table.places[cur[0]][ cur[1]-i ].lastChild.tagName == 'IMG')
									{
										if(table.places[cur[0]][ cur[1]-i ].lastChild.getAttribute('color') != who)
										{
											if(show)
												$(table.places[cur[0]][ cur[1]-i ]).addClass('possibilities');
											else
												ret.push(table.places[cur[0]][ cur[1]-i ]);
										}
										break;
									}else{
										if(show)
											$(table.places[cur[0]][ cur[1]-i ]).addClass('possibilities');
										else
											ret.push(table.places[cur[0]][ cur[1]-i ]);
									}
								}
								if(selectedPiece.getAttribute('piece')== 'king')
									break;
							}
							// going right
							for(var i=cur[1]+1; i<9; i++)
							{
								if(table.places[cur[0]][ i ])
								{
									if(table.places[cur[0]][ i ].lastChild.tagName == 'IMG')
									{
										if(table.places[cur[0]][ i ].lastChild.getAttribute('color') != who)
										{
											if(show)
												$(table.places[cur[0]][ i ]).addClass('possibilities');
											else
												ret.push(table.places[cur[0]][ i ]);
										}
										break;
									}else{
										if(show)
											$(table.places[cur[0]][ i ]).addClass('possibilities');
										else
											ret.push(table.places[cur[0]][ i ]);
									}
								}
								if(selectedPiece.getAttribute('piece')== 'king')
									break;
							}
							// going up
							var going= null;
							for(var i=cur[0]-1; i>0; i--)
							{
								going= table.places[i][ cur[1] ];
								if(going)
								{
									if(going.lastChild.tagName == 'IMG')
									{
										if(going.lastChild.getAttribute('color') != who)
										{
											if(show)
												$(going).addClass('possibilities');
											else
												ret.push(going);
										}
										break;
									}else{
										if(show)
											$(going).addClass('possibilities');
										else
											ret.push(going);
									}
								}
								if(selectedPiece.getAttribute('piece')== 'king')
									break;
							}
							// going down
							for(var i=cur[0]+1; i<9; i++)
							{
								going= table.places[i][ cur[1] ];
								if(going)
								{
									if(going.lastChild.tagName == 'IMG')
									{
										if(going.lastChild.getAttribute('color') != who)
										{
											if(show)
												$(going).addClass('possibilities');
											else
												ret.push(going);
										}
										break;
									}else{
										if(show)
											$(going).addClass('possibilities');
										else
											ret.push(going);
									}
								}
								if(selectedPiece.getAttribute('piece')== 'king')
									break;
							}
				case 'bishop': // bishop moviments // movimentos do bispo
				case 'queen':
				case 'king':
							/*
							 * I know some of these steps could be added to another function
							 * though, I believe it easier to understand, and to perform
							 * future chenges and find specific actions in the code
							 */
							if(selectedPiece.getAttribute('piece')== 'rock')
							   break;
							// going left top
							var c= [cur[0]-1, cur[1]-1];
							while(table.places[c[0]] && table.places[c[0]][c[1]])
							{
								cell= table.places[c[0]][c[1]];
								if(cell.lastChild.tagName == 'IMG')
								{
									if(cell.lastChild.getAttribute('color') != who)
									{
										if(show)
											$(cell).addClass('possibilities');
										else
											ret.push(cell);
									}
									break;
								}else{
									if(show)
										$(cell).addClass('possibilities');
									else
										ret.push(cell);
								}
								c[0]--;
								c[1]--;
								if(selectedPiece.getAttribute('piece')== 'king' || c[0]<0 || c[1]<0)
									break;
							}
							// going right top
							c= [cur[0]-1, cur[1]+1];
							while(table.places[c[0]] && table.places[c[0]][c[1]])
							{
								cell= table.places[c[0]][c[1]];
								if(cell.lastChild.tagName == 'IMG')
								{
									if(cell.lastChild.getAttribute('color') != who)
									{
										if(show)
											$(cell).addClass('possibilities');
										else
											ret.push(cell);
									}
									break;
								}else{
									if(show)
										$(cell).addClass('possibilities');
									else
										ret.push(cell);
								}
								c[0]--;
								c[1]++;
								if(selectedPiece.getAttribute('piece')== 'king' || c[0]<0)
									break;
							}
							// going left down
							c= [cur[0]+1, cur[1]-1];
							while(table.places[c[0]] && table.places[c[0]][c[1]])
							{
								cell= table.places[c[0]][c[1]];
								if(cell.lastChild.tagName == 'IMG')
								{
									if(cell.lastChild.getAttribute('color') != who)
									{
										if(show)
											$(cell).addClass('possibilities');
										else
											ret.push(cell);
									}
									break;
								}else{
									if(show)
										$(cell).addClass('possibilities');
									else
										ret.push(cell);
								}
								c[0]++;
								c[1]--;
								if(selectedPiece.getAttribute('piece')== 'king' || c[1]<0)
									break;
							}
							// going right down
							c= [cur[0]+1, cur[1]+1];
							while(table.places[c[0]] && table.places[c[0]][c[1]])
							{
								cell= table.places[c[0]][c[1]];
								if(cell.lastChild.tagName == 'IMG')
								{
									if(cell.lastChild.getAttribute('color') != who)
									{
										if(show)
											$(cell).addClass('possibilities');
										else
											ret.push(cell);
									}
									break;
								}else{
									if(show)
										$(cell).addClass('possibilities');
									else
										ret.push(cell);
								}
								c[0]++;
								c[1]++;
								if(selectedPiece.getAttribute('piece')== 'king')
									break;
							}
						break;
			}
			if(ret.length>0)
				return ret;
		}
        function tryMove(movimentData, el)
        {
            if(!el)
            {
                var origMovData= movimentData;
                movimentData= movimentData.replace('>:MovimentReturn:', '').split('|');
                //movimentData[1] <- piece
                //movimentData[2] <- where to
                var where= movimentData[2].split(',');
                var cell= table.places[where[0]][where[1]];
                if(cell.lastChild.tagName=='IMG')
                {
                    var king= false;
                    if(cell.lastChild.getAttribute('piece') == 'king')
                        king= true;

                    if(cell.lastChild.style.display!= 'none')
                    {
                        $(cell.lastChild).fadeOut('slow', function(){
                            tryMove(origMovData);
                        });
                        return;
                    }
                    cell.removeChild(cell.lastChild);
                    /*$(cell.lastChild).hide('explode', function(){
                        cell.removeChild(cell.lastChild);
                    });*/
                    
                }
                cell.appendChild(document.getElementById(movimentData[1]))
				if(document.getElementById(movimentData[1]).getAttribute('firstMove'))
				document.getElementById(movimentData[1]).removeAttribute('firstMode');
                if(king)
                {
                    lost= true;
                }
                who= who=='white'? 'black': 'white';
                top.document.getElementById('whose').style.backgroundColor= who;
            }else
                if(table.selected && table.selected.lastChild.getAttribute('color') == '<?php echo $color; ?>')
                {
                    if($(el).hasClass('possibilities'))
                    { // moving here // vindo para cá
                        totalMoves++;
                        if(el.lastChild.tagName=='IMG')
                        { // killing an enemy // comendo uma peça
                            pieces[el.lastChild.getAttribute('color')]--;
                            var king= false;
                            if(el.lastChild.getAttribute('piece') == 'king')
                            {
                                king= true;
                            }
                            if(el.lastChild.style.display!= 'none')
				            {
				                $(el.lastChild).fadeOut('slow', function(){
				                    tryMove(false, el);
				                });
				                return;
				            }
                            el.removeChild(el.lastChild);
                        }
                        if(table.selected.lastChild.getAttribute('firstMove'))
                            table.selected.lastChild.removeAttribute('firstMove');
                        el.appendChild(table.selected.lastChild);
                        desSelect();
                        // sending the moviment to the other side // movendo
						who= who=='white'? 'black': 'white';
                        <?php
                        	if(!isset($_SESSION['alone']) || !$_SESSION['alone'])
                        	{
                        ?>
                                top.sendMoviment('<?php echo $color; ?>', el.lastChild.id, $(el).data('ref'), el.lastChild.getAttribute('piece'));
                        <?php
							}else{
									?>
										machineTurn();
									<?php
							}
                        ?>
                        if(king)
                        {
                            win();
                        }
                        //who= who=='white'? 'black': 'white';
                        top.document.getElementById('whose').style.backgroundColor= who;
                    }
                }
            if(lost)
                loose();
        }
        var selectPlace= function(event){

            if(who != '<?php echo $color; ?>')
                return;

            if(this.lastChild.tagName=='IMG' && this.lastChild.getAttribute('color') == '<?php echo $color; ?>')
            {
                getPossibilities(this.lastChild, true);
            }else{
                tryMove(false, this)
            }
        };

		var loadedImages= 0;
		var imagesToLoad= 0;
		
        $(document).ready(function(){
            top.gameRel= self;

            table.lines= Array();
            table.cols= Array();
            var places= document.getElementById('table').getElementsByTagName('table')[0].getElementsByTagName('tr');

            table.places= Array();
            var line= null;
            for(var i=1, j=places.length; i<j; i++)
            {
                line= places[i].getElementsByTagName('TD');
                table.places[i]= line;
                for(var k=1; k<line.length; k++)
                {
                    $(line[k]).data('ref', [i, k]).bind('click', selectPlace);
                }
            }
            $(table.places[1][1]).append("<img src='images/torre-preta.png' width='40'                   color='black' piece='rock' level='3'>");
            $(table.places[1][2]).append("<img src='images/cavalo-preto.png' width='40'                  color='black' piece='knight' level='2'>");
            $(table.places[1][3]).append("<img src='images/bispo-preto.png' width='40' class='highter'   color='black' piece='bishop' level='3'>");
            $(table.places[1][4]).append("<img src='images/rei-preto.png' width='40' class='highter'     color='black' piece='king' level='5'>");
            $(table.places[1][5]).append("<img src='images/rainha-preta.png' width='40' class='highter'  color='black' piece='queen' level='4'>");
            $(table.places[1][6]).append("<img src='images/bispo-preto.png' width='40' class='highter'   color='black' piece='bishop' level='3'>");
            $(table.places[1][7]).append("<img src='images/cavalo-preto.png' width='40'                  color='black' piece='knight' level='2'>");
            $(table.places[1][8]).append("<img src='images/torre-preta.png' width='40'                   color='black' piece='rock' level='3'>");


            $(table.places[8][1]).append("<img src='images/torre-branca.png' width='40'                  color='white' piece='rock' level='3'>");
            $(table.places[8][2]).append("<img src='images/cavalo-branco.png' width='40'                 color='white' piece='knight' level='2'>");
            $(table.places[8][3]).append("<img src='images/bispo-branco.png' width='40' class='highter'  color='white' piece='bishop' level='3'>");
            $(table.places[8][4]).append("<img src='images/rei-branco.png' width='40' class='highter'    color='white' piece='king' level='5'>");
            $(table.places[8][5]).append("<img src='images/rainha-branca.png' width='40' class='highter' color='white' piece='queen' level='4'>");
            $(table.places[8][6]).append("<img src='images/bispo-branco.png' width='40' class='highter'  color='white' piece='bishop' level='3'>");
            $(table.places[8][7]).append("<img src='images/cavalo-branco.png' width='40'                 color='white' piece='knight' level='2'>");
            $(table.places[8][8]).append("<img src='images/torre-branca.png' width='40'                  color='white' piece='rock' level='3'>");

            $([ table.places[7][1],
                table.places[7][2],
                table.places[7][3],
                table.places[7][4],
                table.places[7][5],
                table.places[7][6],
                table.places[7][7],
                table.places[7][8]]).append("<img src='images/peao-branco.png' color='white' piece='pawn' width='50' level='1' firstMove='true'>");

            $([ table.places[2][1],
                table.places[2][2],
                table.places[2][3],
                table.places[2][4],
                table.places[2][5],
                table.places[2][6],
                table.places[2][7],
                table.places[2][8]]).append("<img src='images/peao-preto.png' color='black' piece='pawn' width='50' level='1' firstMove='true'>");
            var pieceCounter= 1;
            
            imagesToLoad= $('img');
            imagesToLoad.bind('load', function(){
            	loadedImages++;
            	if(imagesToLoad.length<= loadedImages)
            		$('#blocker').fadeOut();
            });
            $('#table img').each(function(){
               this.id= 'piece_'+pieceCounter;
               pieceCounter++
            });
            
            $(document.getElementById('table')).draggable();
            
        });
</script>
