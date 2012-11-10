<html>
<head>
<title>Index</title>

</head>
<body>
	
<?php
session_start();

// if user is already logged in, redirect them to the lobby page
if (isset($_SESSION["username"])) header("Location: lobby.php");

// include login form and logic
include('login.php');

echo "</br><hr /></br>";

// include registration form and logic
include('registration.php');
?>
</body>
</html>