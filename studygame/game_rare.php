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
			width: 250px;
		}
		#progresstext {
			position: fixed;
			left: 25px;
			top: 15px;
		}
	</style>

	<script src="http://code.jquery.com/jquery-1.8.2.js"></script>
	<script src="http://code.jquery.com/ui/1.9.0/jquery-ui.js"></script>
    <script type="text/javascript">

		var state = -1;
		var oldstate = -1;
		var rerender = 1;
		var preventStateChange = 0;
		var stateOverrideTimer = -1;
		var gamescore = 0;
		var roundscore = 0;

		var getGameState = function(success) {
			$.ajax({
				type: 'POST',
				url: 'gameproc_rare.php',
				data: {  
					'function': 'getGameState'
				},
				dataType: 'json',
				success: success
				//error: success
			});
		};

		var setGameState = function(success) {
			$.ajax({
				type: 'POST',
				url: 'gameproc_rare.php',
				data: {  
					'function': 'setGameState',
					'gamescore': gamescore,
					'state': state
				},
				dataType: 'json',
				success: success
				//error: success
			});
		};
		
		var pollGameState = function() {
			getGameState(function(data) {
				var div = $('#gamestate');
				if (data.errno) {
					div.html(JSON.stringify(data));
				}
				else {
					div.html(JSON.stringify(data));
					renderGame(data);
				}
			});
		};
		
		var renderGame = function(data) {
			
			rerender = 1;
			
			if (state == -1) {	
				
				if (oldstate == -1) oldstate = data.state;
				preventStateChange == 1;

				if (data.state == 0) {
					if (stateOverrideTimer == -1) stateOverrideTimer = data.startTimer * 4;
					if (oldstate == data.state) {
						if (stateOverrideTimer > 0) {
							stateOverrideTimer--;
							$("#progresstext").html("Waiting for current game to start...");
							return setTimeout(function() { pollGameState(); }, 250);
						}
						else {
							preventStateChange == 0;
							data.startTimer = 0;
						}
					}
				}
				else if (data.state == 1) {
					if (stateOverrideTimer == -1) stateOverrideTimer = data.cardTimer * 4;
					if (oldstate == data.state) {
						if (stateOverrideTimer > 0) {
							stateOverrideTimer--;
							$("#progresstext").html("Waiting for current round to end...");
							return setTimeout(function() { pollGameState(); }, 250);
						}
						else {
							preventStateChange == 0;
							data.cardTimer = 0;
						}
					}
				}
				else if (data.state == 2) {
					if (stateOverrideTimer == -1) stateOverrideTimer = data.roundTimer * 4;
					if (oldstate == data.state) {
						if (stateOverrideTimer > 0) {
							stateOverrideTimer--;
							$("#progresstext").html("Waiting for new round to start...");
							return setTimeout(function() { pollGameState(); }, 250);
						}
						else {
							preventStateChange == 0;
							data.roundTimer = 0;
						}
					}
				}
				else if (data.state == 3) {
					if (stateOverrideTimer == -1) stateOverrideTimer = data.endTimer * 4;
					if (oldstate == data.state) {
						if (stateOverrideTimer > 0) {
							stateOverrideTimer--;
							$("#progresstext").html("Waiting for current game to end...");
							return setTimeout(function() { pollGameState(); }, 250);
						}
						else {
							preventStateChange == 0;
							data.endTimer = 0;
						}
					}
				}
			}
			
			// show game starting message
			if (data.state == 0) {
				gamescore = 0;
				if (data.startTimer == 0) {
					rerender = 0;
					getGameState(function (data) {
						if (data.state == state && preventStateChange == 0) {
							setGameState(function (data) {
								setTimeout(function() { pollGameState(); }, 0);
							});
						}
						else { 
							setTimeout(function() { pollGameState(); }, 0);
						}
					});
				}
				else {
					$("#progresstext").html("Game Starts in " + data.startTimer--);
				}
			}
			
			// animate card progress bar
			else if (data.state == 1 && data.cardNumber != 0) {
				if (data.cardTimer == -1) {
					rerender = 0;
					getGameState(function (data) {
						if (data.state == state && preventStateChange == 0) {
							setGameState(function (data) {
								setTimeout(function() { pollGameState(); }, 0);
							});
						}
						else { 
							setTimeout(function() { pollGameState(); }, 0);
						}
					});
				}
				else {
					var barWidth = Math.round((data.cardTimer / data.cardDuration) * 100);
					if (data.cardTimer == data.cardDuration) {
						$("#progressbar > .ui-progressbar-value").animate({width: "100%"}, 100, 'linear');
					}
					else {
						$("#progressbar > .ui-progressbar-value").animate({width: barWidth + "%"}, 100, 'linear');
					}
					$("#progresstext").html(data.cardTimer--);
				}
			}
					
			// show round starting message
			else if (data.state == 2) {
				if (data.roundTimer == 0) {
					rerender = 0;
					getGameState(function (data) {
						if (data.state == state && preventStateChange == 0) {
							setGameState(function (data) {
								setTimeout(function() { pollGameState(); }, 0);
							});
						}
						else { 
							setTimeout(function() { pollGameState(); }, 0);
						}
					});
				}
				else {
					$("#progresstext").html("Round " + data.roundNumber + " starts in " + (data.roundTimer--) + " seconds");
					if (state != data.state) {
						$("#progressbar > .ui-progressbar-value").animate({width: "100%"}, 100, 'linear');
					}
				}
			}
			
			// show game over message
			else if (data.state == 3) {
				if (data.endTimer == 0) {
					rerender = 0;
					getGameState(function (data) {
						if (data.state == state && preventStateChange == 0) {
							setGameState(function (data) {
								setTimeout(function() { pollGameState(); }, 0);
							});
						}
						else { 
							setTimeout(function() { pollGameState(); }, 0);
						}
					});
				}
				else {
					$("#progresstext").html("Game Over. Next Game in " + (data.endTimer--) + " seconds");
				}
			}
		
			state = data.state;
			preventStateChange = 0;
			if (rerender) {
				setTimeout(function() { renderGame(data); }, 1000);
			}
			
		};
		
		$(function() {
			$("#progressbar").progressbar({value: 100});
			return pollGameState();
		});
		
	</script>
	
</head>
<body>
	<div id="progressbar"><span id="progresstext"></div></div>
	<div id="gamestate"></div>
</body>
</html>