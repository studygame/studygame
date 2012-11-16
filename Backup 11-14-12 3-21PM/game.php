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
	<link rel="stylesheet" href="jquery-ui-1.9.1.custom/css/start/jquery-ui-1.9.1.custom.css" />

	<title>Study Flash</title>

	<!-- IE HTML5 support fixes -->
	<!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	<!--[if lt IE 9]><script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script><![endif]-->

	<script src="jquery-ui-1.9.1.custom/js/jquery-1.8.2.js"></script>
	<script src="jquery-ui-1.9.1.custom/js/jquery-ui-1.9.1.custom.js"></script>

	<script type="text/javascript" charset="utf-8">
		var correctimgs = ['http://i.imgur.com/5zKXz.gif', 'http://i.imgur.com/t8zvc.gif', 'http://i.imgur.com/0SBuk.gif', 'http://i.imgur.com/DYO6X.gif', 'http://i.imgur.com/tvwQC.gif', 'http://i.imgur.com/Wx9MQ.gif', 'http://i.imgur.com/QxTD6.gif', 'http://i.imgur.com/UmpOi.gif'];
		var wrongimgs = ['http://i.imgur.com/R6qrD.gif', 'http://i.imgur.com/dHpQc.gif', 'http://i.imgur.com/f6due.gif', 'http://i.imgur.com/h8eUL.gif', 'http://i.imgur.com/dImyJ.gif', 'http://gifsoup.com/webroot/animatedgifs1/3213479_o.gif'];
		var correctidx = 0;
		var wrongidx = 0;
	</script>

	<script type="text/javascript" charset="utf-8">
		
		var curGameState = {"stateid": -1};
		var oldScore = 0;
		var activeButton;
		var errMsg = "An error has occured!<br/><br/>Please refresh your browser window.<br/><br/>If the error persists, return to the lobby and choose a different deck.";

		// random sort function
		function shuffle(a, b) {
		   return Math.random() > 0.5 ? -1 : 1;
		}
		
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
						$('#message').html(errMsg + "<br/><br/>Error: " + JSON.stringify(newGameState["data"]));
						$('#message').dialog("open");
						return;
					}
					curGameState = newGameState["data"];
					return procGameState();
				},
				error: function(errMsg) {
					$('#message').html(errMsg + "<br/><br/>Error: " + JSON.stringify(errMsg) + this.data);
					$('#message').dialog("open");
					return;
				}
			});
		};
				
		// processes the current game state
		// requests game state changes when appropriate
		var procGameState = function() {
			
			// render the current game state
			renderGameState();
			
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
			
			// render the game state based on state id
			switch (curGameState.stateid) {
				
				case 0: // a new game is starting
					
						// show starting message
						if (curGameState.timer == curGameState.timelimit) {
							$("#progressbar").progressbar({value: 100});
							$("#progressbar2").progressbar({value: 0});
							$('#progressbar2 > div').addClass('ui-state-active');
							$('#cardcount').html('Card number '+ curGameState.cardnum + ' / ' + curGameState.numcards);
							$('#decktitle').html(curGameState.deckname);
							$('#message').html('<p>Starting Game</p>');
							$('#message').dialog("open");
							
							// randomize correct and wrong images
							correctimgs.sort(shuffle);
							wrongimgs.sort(shuffle);
							correctidx = 0;
							wrongidx = 0;
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
							$("#progressbar2").progressbar({value: 0});
							$('#message').html('<p>Round ' + curGameState.roundnum + '</p>');
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
							$("#progressbar2").progressbar({value: 0});
							
							// update the card number
							$('#cardcount').html('Card number '+ curGameState.cardnum + ' / ' + curGameState.numcards);
							
							// show the question
							$('#question').html(curGameState.card.question);
							
							// show the answers as buttons
							$('#answercol1, #answercol2').html('');
							if (typeof curGameState.card.answers != null) {
								
								activeButton = null;
								var numAnswers = curGameState.card.answers.length;
								var collinecount = 0;
								
								// randomize answers
								curGameState.card.answers.sort(shuffle);
								
								// create answer buttons
								for (var i = 0; i < numAnswers; i++) {
								
									// create the answer button
									var buttontext = String.fromCharCode(65+i) + ') ' + curGameState.card.answers[i].answer;
									var button = $('<button id="answer' + String.fromCharCode(65+i) + '" class="answerButton">' + buttontext +'</button><br/>');
									button.button();
								
									// handle button click for answer guess
									button.click(function(i) {
										return function() {
											
											// ensure time is not up
											if (curGameState.stateid == 2) {
											
												// set answer guess
												curGameState.guess = curGameState.card.answers[i].answer;
												curGameState.answertimeleft = curGameState.timer + 1;
											
												// fix button to active
												if (activeButton) $(activeButton).button('enable').removeClass('ui-state-active ui-state-hover');
												$(this).button('disable').addClass('ui-state-active').removeClass('ui-state-disabled');
												activeButton = this;
											
												// stop guess timer
												$("#progressbar2").progressbar({value: (curGameState.answertimeleft/curGameState.timelimit) * 100});
												$("#progressval2").html('Answered with ' + (curGameState.answertimeleft) + ' seconds left!');
												
											}
											
										}
									}(i));
								
									// add answer button to appropriate column
									collinecount += Math.ceil((buttontext).length / 28);
									if (i < 3 && collinecount < 4) $('#answercol1').append(button);
									else $('#answercol2').append(button);
								}
								
							}
							
						}
						
						// animate timer
						var barValue = (curGameState.timer/curGameState.timelimit) * 100;
						$("#progressbar").progressbar({value: barValue});
						
						// show timer value
						$("#progressval").html(curGameState.timer);
						
						// pulsate timer if nearing time up (< 30%)
						if (barValue < 30) {
							$("#progressbar").effect('pulsate');
						}
												
						// hide card and card answers after timer has finished
						if (curGameState.timer == 0) {
							$("#progressbar").stop();
						}
						
					break;
				
				case 3: // an answer is ready to view
					
						// show the answer
						if (curGameState.timer == curGameState.timelimit) {
							var message = 'The correct answer was...<br/><br/>"' + curGameState.card.correct + '"';
							if (oldScore != curGameState.score) {
								message += '<br/><br/><img id="answerimg" src="' + correctimgs[(correctidx++) % correctimgs.length] + '"/>';
								message += '<br/><br/>You were correct!<br/>You earned ' + (curGameState.score - oldScore) + ' points!';
							}
							else {
								message += '<br/><br/><img id="answerimg" src="' + wrongimgs[(wrongidx++) % wrongimgs.length] + '"/>';
								message += '<br/><br/>Better luck next time!';
							}
							message += '<br/><br/><button id="dispute">Dispute Answer</button>';
							$('#question').html('');
							$('#message').html(message);
							$("#dispute").button().click(function() { curGameState.dispute = 1; });
							$('#message').dialog("open");
							
							// clear any answers
							if (typeof curGameState.guess != 'undefined') delete curGameState.guess;
							if (typeof curGameState.answertimeleft != 'undefined') delete curGameState.answertimeleft;

						}
						
						// hide the answer, reset progress bars
						if (curGameState.timer == 0) {
							$("#progressbar").progressbar({value: 100});
							$("#progressbar2").progressbar({value: 0});
							$('#progressval').html('');
							$("#progressval2").html('');
							$('#message').html('');
							$('#message').dialog("close");
						}
						
					break;
				
				case 4: // the game is over
					
						// show the game over message
						if (curGameState.timer == curGameState.timelimit) {
							var message = 'Game over!<br/><br/>Your Score: ' + curGameState.score;
							if (curGameState.score == curGameState.highscores[0].highscore) {
								message += '<br/><br/>YOU HAVE THE HIGHEST SCORE IN THE GAME!';
								message += '<br/><br/><img src="http://shechive.files.wordpress.com/2011/03/nealpatrick.gif"/>';
							}
							message += '<br/><br/>A new game will start in...<br/><span id="seconds"></span> seconds';
							$('#message').html(message);
							$('#message').dialog("open");
							$('#question').html('');
							$('#answercol1, #answercol2').html('');
						}
						
						$('#seconds').html(curGameState.timer);
						
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

				// show the high scores
				$('#highscores').html('');
				if (typeof curGameState.highscores != null) {

					var numScores = curGameState.highscores.length <= 10 ? curGameState.highscores.length : 10;
					for (var i = 0; i < numScores; i++) {
						var score = $('<div class="highscore"><div class="float-left">' + curGameState.highscores[i].username + '</div><div class="float-right">' + curGameState.highscores[i].highscore + '</div></div>');
						$('#highscores').append(score);
					}

				}
				
			}
			
		};
		
		var handleGuess = function(event) {

			// only check for a..h and A..H keypresses
			if ((event.which >= 65 && event.which <= 72) || (event.which >= 97 && event.which <= 122)) {
				if (curGameState.stateid == 2) {
					var guess = String.fromCharCode(event.which).toUpperCase();
					var button = $('#answer'+guess);
					if (button) button.click();
				}
			}
			
		};
		
		$(function() {
			
			// create the message dialog
			$('#message').dialog({autoOpen: false, resizable: false, show: "explode", hide: "explode", position: {my: "center", at: "center", of: "#card"}});
			$(".ui-dialog-titlebar").hide();
			
			// enable keypress handler for guessing answers
			$(document).keypress(handleGuess);
			
			// style and enable lobby button
			$('#lobbybutton').button();
			
			// watch for window resize events
			$(window).resize(function() {
				$('#message').dialog('option', 'position', {my: 'center', at: 'center', of: '#card'});	
			});

			// start the game
			getNextGameState();
		});
		
	</script>
	
	<style>
		html {
			font-family: Verdana, Helvetica, Arial;
		}
		#pagebox {
			max-width: 920px;
			margin: 8px auto 8px auto;
			background: #ddd;
			border: 1px solid #aaa;
		}
		#cardbox {
			float: left;
			width: 615px;
			margin: 0;
			padding: 20px;
		}
		#deck {
			height: 30px;
			width: 100%;
			color: #222;
			font-size: 1.2em;
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
		}
		#progressbar, #progressbar2 {
			position: absolute;
			height: 40px;
			width: 615px;
			border-color: #888;
		}
		#progressbar > div, #progressbar2 > div {
			border-color: #888;
		}
		#progressbar {
			z-index: 1;
			background: none;
		}
		#progressbar2 {
			z-index: 0;
			background: #aaa;
		}
		#progressval {
			position: absolute;
			z-index: 2;
			margin: 10px 0 0 12px;
			font-size: 1.2em;
			color: #fff;
		}
		#progressval2 {
			position: relative;
			float: right;
			z-index: 2;
			margin: 10px 12px 0 0;
			font-size: 1.1em;
			color: #fff;
		}
		#cardwrapper {
			height: 445px;
			max-height: 445px;
			overflow-y: auto;
			margin-bottom: 20px;
			border: 1px solid #aaa;
		}
		#card {
			display: table;
			height: 100%;
			width: 100%;
			background-color: #fff;
		}
		#question {
			display: table-cell;
			font-size: 2em;
			color: #222;
			width: 100%;
			height: 100%;
			padding: 10px;
			text-align: center;
			vertical-align: middle;
		}
		#question > img {
			max-width: 100%;
			max-height: 100%;
		}
		#answers {
			height: 147px;
			overflow: hidden;
			width: 100%;
			border: 1px solid #aaa;
			background-color: #eee;
		}
		.answerButton {
			text-align: left;
			margin: 0 0 6px 0;
			width: 100%;
			border-color: #888;
		}
		#answercol1 {
			float: left;
			height: 100%;
			width: 48%;
			padding: 6px;
		}
		#answercol2 {
			margin-left: 50%;
			height: 100%;
			width: 48%;
			padding: 6px;
		}
		#scorebox {
			width: 245px;
			margin: 0 0 0 655px;
			padding: 20px 0 20px 0px;
		}
		#highscoretitle {
			height: 30px;
			color: #222;
			font-size: 1.2em;
			width: 100%;
		}
		#score {
			height: 40px;
			width: 100%;
			color: #fff;
			background-color: #aaa;
			border: 1px solid #888;
			margin-bottom: 20px;
		}
		#scoretitle {
			float: left;
			font-size: 1.1em;
			margin: 10px 0 0 10px;
		}
		#scoreval {
			float: right;
			font-size: 1.1em;
			margin: 10px 10px 0 0;
		}
		#highscores {
			height: 612px;
			background-color: #aaa;
			border: 1px solid #888;
			color: #fff;
		}
		.highscore {
			height: 1.2em;
			font-size: 1.1em;
			padding: 10px;
			border-bottom: 1px solid #888;
		}
		.float-right {
			float: right;
		}
		.float-left {
			float: left;
		}
		#message {
			text-align: center;
			padding: 10%;
		}
		.ui-dialog {
			border-color: #aaa;
		}
		#message p {
			min-height: 60px;
			margin-top: 18%;
		}
		#message img {
			border: 1px solid #aaa;
			max-width: 99%;
			max-height: 175px;
			height: 175px;
		}
	</style>

</head>
<body>
	
	<span id="toolbar" class="ui-widget-header">
        <input id='lobbybutton' class='left ui-button ui-widget ui-state-default ui-corner-all' type="submit" value="Return to Lobby" onclick="window.location.href='lobby.php'"/>
		<input id='logout' class='right ui-button ui-widget ui-state-default ui-corner-all' type='submit' name='log-out' value='Logout' onclick="window.location.href='logout.php'" />
	</span>
	
	<div id="pagebox" class="ui-corner-all">
		<div id="topbox">
			<div id="cardbox">
				<div id="deck"><div id="decktitle"></div><div id="cardcount"></div></div>
				<div id="timer" class="ui-corner-all">
					<div id="progressval"></div>
					<div id="progressval2"></div>
					<div id="progressbar"></div>
					<div id="progressbar2"></div>
				</div>
				<div id="cardwrapper" class="ui-corner-all"><div id="card"><span id="question"></span></div></div>
				<div id="answers" class="ui-corner-all"><div id="answercol1"></div><div id="answercol2"></div></div>
			</div>
			<div id="scorebox">
				<div id="highscoretitle">Current and High Scores</div>
				<div id="score" class="ui-corner-all"><div id="scoretitle">Your Score:</div><div id="scoreval"></div></div>
				<div id="highscores" class="ui-corner-all"></div>
			</div>
		</div>
	</div>
	<div id="message"></div>
	<div id="errors"></div>
</body>
</html>
