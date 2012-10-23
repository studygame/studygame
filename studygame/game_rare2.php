<?php

	// ensure user is logged in, otherwise redirect to login page
	session_start();
	if (!isset($_SESSION["userid"])) {
		header("Location: login.php");
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
	<link rel="stylesheet" href="http://code.jquery.com/ui/1.9.0/themes/base/jquery-ui.css" />

	<style>
		#progressbar {
			width: 350px;
		}
		#progresstext {
			position: fixed;
			left: 25px;
			top: 15px;
		}
		#card {
			width: 350px;
			height: 200px;
		}
		#answers {
			width: 350px;
			height: auto;
		}
	</style>

	<script src="http://code.jquery.com/jquery-1.8.2.js"></script>
	<script src="http://code.jquery.com/ui/1.9.0/jquery-ui.js"></script>
    <script type="text/javascript">

		var localState = -1;
		var enterState = -1;
		var enterTime = -1;
		var overrideTime = -1;
		var gameScore = 0;

		var getGameState = function(success) {
			$.ajax({
				type: 'POST',
				url: 'gameproc_rare2.php',
				data: {  
					'function': 'getGameState'
				},
				dataType: 'json',
				success: success
			});
		};

		var setGameState = function(success) {
			$.ajax({
				type: 'POST',
				url: 'gameproc_rare2.php',
				data: {  
					'function': 'setGameState',
					'gamescore': gameScore,
					'state': localState
				},
				dataType: 'json',
				success: success
			});
		};
		
		var pollGameState = function() {
			return getGameState(function(data) {
				processGameState(data);
			});
		};
		
		var processGameState = function(data) {
			
			var textDiv = $('#progresstext');
			var errDiv = $('#gamestate');
			
			// if there were errors, display them
			if (data.errno) {
				errDiv.html(JSON.stringify(data));
			}
			
			// otherwise, process game state
			else {

				// if entering the room for the first time
				// wait until current game state changes
				// so as to stay in sync with other players
				if (localState == -1) {
					
					// save the state of the game when
					// the player first entered the room
					if (enterState == -1) {
						enterState = data.state;
						enterTime = Date.now();
					}

					if (enterState == data.state) {

						// set override timer based on the type
						// of state we are currently in
						if (overrideTime == -1) {
							switch (enterState) {
								case 0:
									overrideTime = data.startTimer;
									break;
								case 1:
									overrideTime = data.cardTimer;
									break;
								case 2:
									overrideTime = data.roundTimer;
									break;
								case 3:
									overrideTime = data.endTimer;
									break;
							}
						}

						// if player is the only player in the
						// room, or all players are waiting for
						// the game state to change before continuing,
						// the game will never change state
						// - to remedy this, if the timer for the 
						// specific state is passed due, continue
						// the game without waiting for another player
						// to update the game state
						if (((Date.now() - enterTime)/1000) >= overrideTime) {
							enterState = -2;
							localState = data.state
							return setGameState(processGameState);
						}
						
						return renderGameState(data, pollGameState);

					}
					
				}
								
				// player is not entering the room for the
				// first time, process the game normally

				// update local state to reflect server state
				localState = data.state;
				
				// render state
				renderGameState(data);
			
				// update state
				switch (data.state) {
				
					// game is getting ready to start
					case 0:
					
						// reset game score
						if (gameScore > 0) gameScore = 0;
					
						// if timer has finished, update the game state
						if (data.startTimer == 0) {
							return setTimeout(function() { setGameState(processGameState); }, 1000);
						}

						// update timer
						data.startTimer--;
					
						break;
					
					// round is in progress
					case 1:
						
						// if timer has finished, update the game state
						if (data.cardTimer == 0) {
							return setTimeout(function() { setGameState(processGameState); }, 1000);
						}
						
						// update timer
						data.cardTimer--;
						
						break;
						
					// round is over, new round is starting
					case 2:
						
						// if timer has finished, update the game state
						if (data.roundTimer == 0) {
							return setTimeout(function() { setGameState(processGameState); }, 1000);
						}

						// update timer
						data.roundTimer--;
						
						break;
						
					// game is over
					case 3:
						
						// if timer has finished, update the game state
						if (data.endTimer == 0) {
							return setTimeout(function() { setGameState(processGameState); }, 1000);
						}

						// update timer
						data.endTimer--;
						
						break;
				}
				
				// keep processing game state
				return setTimeout(function() { processGameState(data); }, 1000);

			}
			
		};
		
		var renderGameState = function(data, callback) {
			
			var textDiv = $('#progresstext');
			var stateDiv = $('#gamestate');
			var progressBar = $("#progressbar > .ui-progressbar-value");
			var cardDiv = $('#card');
			var answersDiv = $('#answers');
			var dialogDiv = $('#dialog');

			// game state output
			stateDiv.html(JSON.stringify(data));
			
			if (overrideTime == 1) dialogDiv.dialog("close");
			
			// if player just entered the room
			if (localState == -1) {
				
				// initialize game elements for the first time
				$("#progressbar").progressbar({value: 100});
			
				// show waiting message
				switch (enterState) {
					case 0:
						if (overrideTime == data.startTimer) dialogDiv.dialog("open");
						dialogDiv.html('Waiting for game to start...');
						break;
					case 1:
						if (overrideTime == data.cardTimer) dialogDiv.dialog("open");
						dialogDiv.html('Waiting for current round to end...');
						break;
					case 2:
						if (overrideTime == data.roundTimer) dialogDiv.dialog("open");
						dialogDiv.html('Waiting for round to start...');
						break;
					case 3:
						if (overrideTime == data.endTimer) dialogDiv.dialog("open");
						dialogDiv.html('Waiting for current game to end...');
						break;
				}
				
			}

			// render game state elements
			else {
				
				switch (data.state) {
		
					// a new game is getting ready to start
					case 0:
			
						var barWidth = (data.startTimer / data.startDuration) * 100;
						if (data.startTimer == data.startDuration) {
							progressBar.animate({width: "100%"}, 100, 'linear');
						}
						else {
							progressBar.animate({width: barWidth + "%"}, 1000, 'linear');
						}
						dialogDiv.html("New game in: " + Math.min(data.startTimer + 1, data.startDuration));

						if (data.startTimer == data.startDuration) dialogDiv.dialog("open");
						if (data.startTimer == 0) dialogDiv.dialog("close");
			
						break;
			
					// a round is currently in progress
					case 1:
						
						if (dialogDiv.dialog("isOpen")) dialogDiv.dialog("close");
			
						if (data.cardNumber != 0) {
							
							// display card
							cardDiv.html(data.card.question);
							
							// display possible answers
							answersDiv.html($('<table><tr><td>Test</td></tr></table>'));

							var barWidth = (data.cardTimer / data.cardDuration) * 100;
							if (data.cardTimer == data.cardDuration) {
								progressBar.animate({width: "100%"}, 100, 'linear');
							}
							else {
								progressBar.animate({width: barWidth + "%"}, 1000, 'linear');
							}
							textDiv.html(Math.min(data.cardTimer + 1, data.cardDuration));
						}
			
						break;
				
					// round is over, new round is starting
					case 2:
						
						progressBar.animate({width: "100%"}, 100, 'linear');
						dialogDiv.html("Round " + data.roundNumber);
						
						if (data.roundTimer == data.roundDuration) dialogDiv.dialog("open");
						if (data.roundTimer == 0) dialogDiv.dialog("close");
					
						break;
				
					// game is over, new game is starting
					case 3:

						var barWidth = (data.endTimer / data.endDuration) * 100;
						if (data.endTimer == data.endDuration) {
							progressBar.animate({width: "100%"}, 100, 'linear');
						}
						else {
							progressBar.animate({width: barWidth + "%"}, 1000, 'linear');
						}
						dialogDiv.html("Game Over. Next game in: " + Math.min(data.endTimer + 1, data.endDuration));

						if (data.endTimer == data.endDuration) dialogDiv.dialog("open");
						if (data.endTimer == 0) dialogDiv.dialog("close");
					
						break;
				}

			}
			
			if (callback) {
				return callback(data);
			}
						
		};
		
		// render game and start polling for game state
		$(function() {
			$('#dialog').dialog({
				resizable: false,
				draggable: false,
				autoOpen: false,
				show: {effect: 'explode', 'duration': 500},
				hide: {effect: 'explode', 'duration': 500}
			});
			$('.ui-dialog-titlebar').hide();
			$('.ui-dialog-titlebar-close').hide();
			pollGameState();
		});
				
	</script>
	
</head>
<body>
	<div id="dialog"></div>
	<div id="progressbar"><span id="progresstext"></div></div>
	<div id="card"></div>
	<div id="answers"></div>
	<div id="gamestate"></div>
</body>
</html>