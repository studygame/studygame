<html>
<head>
<title>Update</title>
</head>
<body>

<form method='POST' action='update.php'>
<textarea name="description" cols="40" rows="4">Description...</textarea>
<br />

<input type='submit' name='submit' value='Update' />
</br>

</form>

<?php
session_start();
if(!isset($_SESSION["username"]))
	header("Location: http://babbage.cs.missouri.edu/~cs3380f12grp8/Login/index.php");	

// Check if update button was previously pressed
if(isset($_POST['submit'])){
	$description = $_POST['description'];
	$username = $_SESSION["username"];

	include('db_connect.php');

	if(!$conn){
		echo "Unable to connect to database.";
		exit;
	}//End if

	$query = "UPDATE lab7.user_info SET description = $1 WHERE username = $2";
	$stmt = pg_prepare($conn, "updateDescription", $query);

	if(!$stmt)
		echo "Error: failure occurred when preparing statement.<br />";

	$result = pg_execute($conn, "updateDescription", array($description, $username));

	if(!$result)
		echo "An error occurred when attempting to update.<br />";
	else
		echo "Updated successfully!<br />";

}//End if

?>

<a href='home.php'>Return to homepage</a>

</body>

</html>