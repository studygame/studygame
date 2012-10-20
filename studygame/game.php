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

	<title></title>
	
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script type="text/javascript">

		var getGameState = function(callback) {
			$.ajax({
				type: 'POST',
				url: 'gameproc.php',
				data: {  
					'function': 'getGameState'
				},
				dataType: 'json',
				success: callback
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
					setTimeout(pollGameState, 1000);
				}
			});
		}

		$(function() {
			pollGameState();
		});
		
	</script>
	
</head>
<body>
	<div id="gamestate"></div>
</body>
</html>