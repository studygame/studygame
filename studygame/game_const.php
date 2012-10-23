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
		var gamescore = 0;
		var roundscore = 0;

		var getGameState = function(success) {
			$.ajax({
				type: 'POST',
				url: 'gameproc_const.php',
				data: {  
					'function': 'getGameState',
					'gamescore': gamescore
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
					return pollGameState();
				}
			});
		};
		
		var renderGame = function(data) {
			
			// show game starting message
			if (data.state == 0) {
				gamescore = 0;
				$("#progresstext").html("Game Starts in " + data.startTimer);
			}
			
			// animate card progress bar
			else if (data.state == 1 && data.cardNumber != 0) {
				var barWidth = Math.round((data.cardTimer / data.cardDuration) * 100);
				if (data.cardTimer == data.cardDuration)
					$("#progressbar > .ui-progressbar-value").animate({width: "100%"}, 100, 'linear');
				else
					$("#progressbar > .ui-progressbar-value").animate({width: barWidth + "%"}, 100, 'linear');
				$("#progresstext").html(data.cardTimer);
			}
					
			// show round starting message
			else if (data.state == 2) {
				$("#progresstext").html("Round " + data.roundNumber + " starts in " + data.roundTimer + " seconds");
				if (state != data.state) {
					$("#progressbar > .ui-progressbar-value").animate({width: "100%"}, 100, 'linear');
				}
			}
			
			// show game over message
			else if (data.state == 3) {
				$("#progresstext").html("Game Over. Next Game in " + data.endTimer + " seconds");
			}
		
			state = data.state;
			
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