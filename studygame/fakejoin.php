<?php

	session_start();
	$_SESSION["userid"] = "1234"; // fake user login for testing
	
?>
<html>
<head>
	<title>Fake game join</title>
</head>
<body>
	<form action="gameproc.php" method="post">
		<input type="hidden" name="function" value="joinGame"/>
		<input type="hidden" name="deckid" value="345"/>
		<input type="submit" name="submit" value="Submit"/>
	</form>
</body>
</html>