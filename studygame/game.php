<?php
	// ensure the user is logged in
	// if not, redirect them to the login page
	session_start();
	if (!isset($_SESSION["username"])) {
		header("Location: index.php");
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="author" content="">
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	<meta name="robots" content="all" />
	
	<link rel="icon" type="image/x-icon" href="/favicon.ico">
	<link rel="stylesheet" type="text/css" href="css/reset.css" media="screen">
	<link rel="stylesheet" type="text/css" href="css/main.css" media="screen">
	<link rel="stylesheet" href="jquery-ui-1.9.1.custom/css/start/jquery-ui-1.9.1.custom.css" />

	<title>Study Flash</title>

	<!-- IE HTML5 support fixes -->
	<!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	<!--[if lt IE 9]><script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script><![endif]-->

	<script src="jquery-ui-1.9.1.custom/js/jquery-1.8.2.js"></script>
	<script src="jquery-ui-1.9.1.custom/js/jquery-ui-1.9.1.custom.js"></script>

	<script type="text/javascript" charset="utf-8">
		
		var curGameState = {"stateid": -1};
		var oldScore = 0;
		var activeButton;
		
		// returns the next game state given the current state
		var getNextGameState = function() {
			$.ajax({
				type: 'POST',
				url: 'gameproc.php',
				data: {  
					'function': 'getNextGameState',
					'curGameState': JSON.stringify(curGameState)
				},
				dataType: 'json',
				cache: false,
				success: function(newGameState) {
					if (newGameState["status"] == -1) {
						return $('#errors').html(JSON.stringify(newGameState["data"]));
					}
					curGameState = newGameState["data"];
					return procGameState();
				},
				error: function(errMsg) {
					$('#errors').html(JSON.stringify(errMsg) + this.data);
				}
			});
		};
				
		// processes the current game state
		// requests game state changes when appropriate
		var procGameState = function() {
			
			// render the current game state
			renderGameState();
			//$('#gamestate').html(JSON.stringify(curGameState));
			
			// check the time limit of the current state
			// if it has run out, request the next game state
			if (curGameState.timer == 0) {
				return getNextGameState();
			}
			
			// otherwise, decrement the game state timer
			curGameState.timer--;
			
			// loop every second until next game state
			setTimeout(procGameState, 1000);
			
		};
		
		// renders the current game state
		var renderGameState = function() {
			
			// show the deck information
			$('#decktitle').html(curGameState.deckname);

			// render the game state based on state id
			switch (curGameState.stateid) {
				
				case 0: // a new game is starting
					
						// show starting message
						if (curGameState.timer == curGameState.timelimit) {
							$("#progressbar").progressbar({value: 100});
							$('#message').html('Starting Game');
							$('#message').dialog("open");
						}
						
						// hide starting message
						if (curGameState.timer == 0) {
							$('#message').html('');
							$('#message').dialog("close");
						}
					break;
				
				case 1: // a new round is starting
					
						// show round starting message
						if (curGameState.timer == curGameState.timelimit) {
							$("#progressbar").progressbar({value: 100});
							$('#message').html('Round ' + curGameState.roundnum);
							$('#message').dialog("open");
							$('#question').html('');
							$('#answercol1, #answercol2').html('');
						}
						
						// hide round starting message
						if (curGameState.timer == 0) {
							$('#message').html('');
							$('#message').dialog("close");
						}
					break;
				
				case 2: // a card is ready to view
					
						// show the card, and card answers
						if (curGameState.timer == curGameState.timelimit) {
							
							// keep track of our old score for showing earned points
							oldScore = curGameState.score;
							
							// reset progress bar and guess bar
							$("#progressbar").progressbar({value: 100});
							
							// update the card number
							$('#cardcount').html('Card number '+ curGameState.cardnum + ' / ' + curGameState.numcards);
							
							// show the question
							$('#question').html(curGameState.card.question);
							
							// show the answers as buttons
							$('#answercol1, #answercol2').html('');
							if (typeof curGameState.card.answers != null) {
								activeButton = null;
								var numAnswers = curGameState.card.answers.length;
								for (var i = 0; i < numAnswers; i++) {
								
									// create the answer button
									var button = $('<button id="answer' + String.fromCharCode(65+i) + '" class="answerButton">' + String.fromCharCode(65+i) + ') ' + curGameState.card.answers[i].answer +'</button><br/>');
									button.button();
								
									// handle button click for answer guess
									button.click(function(i) {
										return function() {
											
											// set answer guess
											curGameState.guess = curGameState.card.answers[i].answer;
											curGameState.answertimeleft = curGameState.timer + 1;
											
											// fix button to active
											if (activeButton) $(activeButton).button('enable').removeClass('ui-state-active ui-state-hover');
											$(this).button('disable').addClass('ui-state-active').removeClass('ui-state-disabled');
											activeButton = this;
										}
									}(i));
								
									// add answer button to appropriate column
									if (i < 3) $('#answercol1').append(button);
									else $('#answercol2').append(button);
								}
							}
							
						}
						
						// animate timer
						var barValue = (curGameState.timer/curGameState.timelimit) * 100;
						$("#progressbar").progressbar({value: barValue});
						
						// show timer value
						$("#progressval").html(curGameState.timer);
						
						// pulsate timer if nearing time up
						if (barValue < 30) {
							$("#progressbar").effect('pulsate');
						}
												
						// hide card and card answers after timer has finished
						if (curGameState.timer == 0) {
							$("#progressbar").progressbar({value: 0});
							$('#progressval').html('');
						}
					break;
				
				case 3: // an answer is ready to view
					
						// show the answer
						if (curGameState.timer == curGameState.timelimit) {
							var message = 'The correct answer was...<br/>"' + curGameState.card.correct + '"';
							if (oldScore != curGameState.score) {
								message += '<br/><br/>You were correct!<br/>You earned ' + (curGameState.score - oldScore) + ' points!';
							}
							else {
								message += '<br/><br/>Better luck next time!';
							}
							$('#question').html('');
							$('#message').html(message);
							$('#message').dialog("open");
						}
						
						// hide the answer
						if (curGameState.timer == 0) {
							$('#message').html('');
							$('#message').dialog("close");
						}
					break;
				
				case 4: // the game is over

						$('#message').html('Game over!<br/><br/>Your Score: ' + curGameState.score + '<br/><br/>A new game will start in...<br/>' + curGameState.timer + ' seconds');
					
						// show the game over message
						if (curGameState.timer == curGameState.timelimit) {
							$('#message').dialog("open");
							$('#question').html('');
							$('#answercol1, #answercol2').html('');
						}
						
						// hide game over message
						if (curGameState.timer == 0) {
							$('#message').html('');
							$('#message').dialog("close");
						}
					break;
			}
			
			// render score and high scores
			if (curGameState.timer == curGameState.timelimit) {

				// show player score
				$('#scoreval').html(curGameState.score);

				$('#highscores').html('');
				if (typeof curGameState.highscores != null) {

					// sort the scores
					curGameState.highscores.sort(function(a, b) { return b.highscore - a.highscore; });

					// show the high scores
					var numScores = curGameState.highscores.length <= 10 ? curGameState.highscores.length : 10;
					for (var i = 0; i < numScores; i++) {
						var score = $('<div class="highscore"><div class="float-left">' + curGameState.highscores[i].username + '</div><div class="float-right">' + curGameState.highscores[i].highscore + '</div></div>');
						//var score = $('<div class="highscore"><div class="float-left">' + 'ttt' + '</div><div class="float-right">' + '999999' + '</div></div>');
						$('#highscores').append(score);
					}

				}
				
			}
			
		};
		
		var handleGuess = function(event) {

			// only check for a..h and A..H keypresses
			if ((event.which >= 65 && event.which <= 72) || (event.which >= 97 && event.which <= 122)) {
				var guess = String.fromCharCode(event.which).toUpperCase();
				var button = $('#answer'+guess);
				if (button) button.click();
			}
			
		};
		
		$(function() {
			
			// create the message dialog
			$('#message').dialog({autoOpen: false, resizable: false, show: "explode", hide: "explode", position: {my: "center", at: "center", of: "#card"}});
			$(".ui-dialog-titlebar").hide();
			
			// enable keypress handler for guessing answers
			$(document).keypress(handleGuess);
			
			// style and enable lobby button
			$("#lobby").button();

			// start the game
			getNextGameState();
		});
		
	</script>
	
	<style>
		html {
			font-family: Verdana, Helvetica, Arial;
		}
		#pagebox {
			max-width: 1020px;
			margin: 4px auto 0 auto;
			background: #333;
		}
		#appNav {
			
		}
		#cardbox {
			float: left;
			width: 60%;
			margin: 0;
			padding: 20px;
		}
		#deck {
			height: 30px;
			width: 100%;
			color: #fff;
		}
		#decktitle {
			float: left;
		}
		#cardcount {
			float: right;
		}		
		#timer {
			height: 40px;
			padding: 0;
			width: 100%;
			margin-bottom: 20px;
			background: #000;			
		}
		#progressbar {
			height: 100%;
		}
		#progressval {
			float: left;
			margin: 6px 0 0 10px;
			font-size: 1.2em;
			color: #fff;
		}
		#card {
			display: table;
			height: 400px;
			width: 100%;
			margin-bottom: 20px;
			background: #fff;
		}
		#question {
			display: table-cell;
			font-size: 2em;
			width: 100%;
			height: 100%;
			padding: 10px;
			text-align: center;
			vertical-align: middle;
		}
		#answers {
			height: 190px;
			width: 100%;
		}
		.answerButton {
			text-align: left;
			margin: 5px 0 0 5px;
			width: 96%;
		}
		#answercol1 {
			float: left;
			height: 100%;
			width: 50%;
			background: #666;
		}
		#answercol2 {
			margin-left: 50%;
			height: 100%;
			width: 50%;
			background: #666;
		}
		#scorebox {
			width: 34%;
			margin: 0 0 0 64%;
			padding: 20px 0 20px 0px;
		}
		#score {
			height: 40px;
			width: 100%;
			color: #fff;
			background-color: #666;
			margin-bottom: 20px;
		}
		#scoretitle {
			float: left;
			font-size: 1.2em;
			margin: 8px 0 0 10px;
		}
		#scoreval {
			float: right;
			font-size: 1.2em;
			margin: 8px 10px 0 0;
		}
		#highscoretitle {
			height: 30px;
			color: #fff;
			width: 100%;
		}
		#highscores {
			height: 434px;
			background: #666;
			padding: 20px;
			margin-bottom: 20px;
			color: #fff;
		}
		.highscore {
			background-color: #222;
			margin: 0 0 4px 0;
			height: 20px;
			padding: 4px;
		}
		#appNav {
			height: 116px;
			background: #666;
		}
		.float-right {
			float: right;
		}
		.float-left {
			float: left;
		}
		#message {
			text-align: center;
			margin-top: 25%;
			margin-bottom: 25%;
		}

	</style>

</head>
<body>
	<div id="pagebox">
		<div id="topbox">
			<div id="cardbox">
				<div id="deck"><div id="decktitle"></div><div id="cardcount"></div></div>
				<div id="timer">
					<div id="progressbar"><span id="progressval"></span></div>
				</div>
				<div id="card"><span id="question"></span></div>
				<div id="answers"><div id="answercol1"></div><div id="answercol2"></div></div>
			</div>
			<div id="scorebox">
				<div id="score"><div id="scoretitle">Your Score:</div><div id="scoreval"></div></div>
				<div id="highscoretitle">Current High Scores</div>
				<div id="highscores"></div>
				<div id="appNav">
					<button id="lobby">Return to Lobby</button>
				</div>
			</div>
		</div>
	</div>
	<div id="message"></div>
	<div id="gamestate"></div>
	<div id="errors"></div>
</body>
</html>
