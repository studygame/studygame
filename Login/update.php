<html>
<head>
<title>Update</title>
</head>
<body>

<form method='POST' action='update.php'>


<select name="school">
	<option value="">Select your University</option>
<?php
	while($row = pg_fetch_assoc($result)){
		$schoolID = $row["schoolid"];
		$schoolNAME = $row["schoolname"];
		echo "<option value=$schoolID>".$schoolNAME."</option>";
	}//End while
?>
</select>
<br />

<label class='required' for='username' id='username'></label>
<input type='text' name='username' id='username' placeholder='Username'>
<br />

<label class='required' for='email' id='email'></label>
<input type='text' name='email' id='email' placeholder='Email'>
<br />

<label class='required' for='password' id='password'></label>
<input type='password' name='password' id='password' placeholder='Password'>
<br />

<label class='required' for='conpassword' id='conpassword'></label>
<input type='password' name='conpassword' id='conpassword' placeholder='Confirm password'>
<br />

<input type='submit' name='submit' value='Update' />
</br>

</form>

<?php
session_start();
if(!isset($_SESSION["username"]))
	header("Location: index.php");	

if(isset($_POST['submit'])){
	$description = $_POST['description'];
	$username = $_SESSION["username"];

	include('dbconnect.php');

	if(!$dbconn){
		echo "Unable to connect to database.";
		exit;
	}//End if

	$query = "UPDATE lab7.user_info SET description = $1 WHERE username = $2";
	$stmt = pg_prepare($dbconn, "updateDescription", $query);

	if(!$stmt)
		echo "Error: failure occurred when preparing statement.<br />";

	$result = pg_execute($dbconn, "updateDescription", array($description, $username));

	if(!$result)
		echo "An error occurred when attempting to update.<br />";
	else
		echo "Updated successfully!<br />";

}//End if

?>

<a href='home.php'>Return to homepage</a>

</body>

</html>