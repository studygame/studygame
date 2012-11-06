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
		var curChatState = {"lines": 0};
		
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
			$('#gamestate').html(JSON.stringify(curGameState));
			
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
							$('#message').html('Starting Game');
						}
						// hide starting message
						if (curGameState.timer == 0) {
							$('#message').html('');						
						}
					break;
				
				case 1: // a new round is starting
						// show round starting message
						if (curGameState.timer == curGameState.timelimit) {
							$('#message').html('Starting Round ' + curGameState.roundnum);
						}
						// hide round starting message
						if (curGameState.timer == 0) {
							$('#message').html('');
						}
					break;
				
				case 2: // a card is ready to view
						// show the card and card answers
						if (curGameState.timer == curGameState.timelimit) {
							$("#progressbar").progressbar({value: 100});
							$('#message').html('Showing card and card answers...');
						}
						
						// animate timer
						$("#progressbar").progressbar({value: (curGameState.timer/curGameState.timelimit) * 100});
						
						// hide card and card answers
						if (curGameState.timer == 0) {
							$("#progressbar").progressbar({value: 0});
							$('#message').html('');
						}
					break;
				
				case 3: // an answer is ready to view
						// show the answer
						if (curGameState.timer == curGameState.timelimit) {
							$('#message').html('The correct answer is...' + curGameState.card.correct);
						}
						// hide the answer
						if (curGameState.timer == 0) {
							$('#message').html('');
						}
					break;
				
				case 4: // the game is over
						// show the game over message
						if (curGameState.timer == curGameState.timelimit) {
							$('#message').html('Game is over');
						}
						// hide game over message
						if (curGameState.timer == 0) {
							$('#message').html('');
						}
					break;
			}
			
			// render high scores
			
		};
		
		// begin game
		$(function() {
			getNextGameState();
		});
		
	</script>

</head>
<body>
	<div id="progressbar"></div>
	<div id="message"></div>
	State:<div id="gamestate"></div>
	<div id="errors"></div>
</body>
</html>