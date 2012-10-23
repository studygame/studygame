<?php
session_start();

if(isset($_POST["username"]) && isset($_POST["password"])){
// if the user has just tried to log in
	$username = $_POST["username"];
        $password = $_POST["password"];

	include('db_connect.php');

	if(!$conn){
		echo "Unable to connect to database.";
		exit;
	}//End if

	$query = "SELECT username, password_hash, salt FROM lab7.authentication WHERE username = $1";


	$stmt = pg_prepare($conn, "logggingIn", $query);

	if(!$stmt)
		echo "<br />Error: Unable to prepare statement.";
	else{
		$params = array($username);
		$result = pg_execute($conn, "logggingIn", $params);

		if($result){
                	$row = pg_fetch_assoc($result);
			$salt = trim($row["salt"]);
        		$password_hash = sha1($salt.$password);

			if($password_hash == $row["password_hash"])
                		$_SESSION["username"] = $username;

		}//End if
		else
			echo "<br />An unknown error occurred during Authorization creation.";

	}//End else

	pg_close($conn);

}//End if

if(isset($_SESSION["username"]))
	header("Location: http://babbage.cs.missouri.edu/~cs3380f12grp8/Login/home.php");
else{
	if(isset($username))
		echo 'Could not log you in.<br />';

//Log in form
	include('index.php');

}//End else

?>
