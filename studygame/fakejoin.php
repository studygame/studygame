<?php

	session_start();
	if (!isset($_SESSION["userid"])) {
		$_SESSION["userid"] = rand(0, 255); // fake user login for testing
	}
	
	$algorithm = "rare2";
	
?>
<html>
<head>
	<title>Fake game join</title>
</head>
<body>
	<form action="gameproc_<?php echo $algorithm?>.php" method="post">
		<input type="hidden" name="function" value="joinGame"/>
		<input type="hidden" name="deckid" value="1"/>
		<input type="submit" name="submit" value="Submit"/>
	</form>
</body>
</html>