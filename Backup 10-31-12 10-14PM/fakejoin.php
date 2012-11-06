<?php

	session_start();
	if (!isset($_SESSION["username"])) {
		$_SESSION["username"] = rand(0, 255); // fake user login for testing
	}
	
?>
<html>
<head>
	<title>Fake game join</title>
</head>
<body>
	<form action="gameproc.php" method="post">
		<input type="hidden" name="function" value="joinGame"/>
		<input type="hidden" name="deckid" value="2"/>
		<input type="submit" name="submit" value="Submit"/>
	</form>
</body>
</html>