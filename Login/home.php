<?php
session_start();

if(!isset($_SESSION["username"]))
	header("Location: index.php");

?>

<html>

<form method='POST' action='logout.php' onsubmit='return checkSubmit();'>
	<input type='submit' name='log-out' value='Logout' />
</form>
</br>

<form method='POST' action='update.php' onsubmit='return checkSubmit();'>
	<input type='submit' name='update' value='Update' />
</form>
</br>

</html>
