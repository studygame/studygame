<?php
session_start();

if(isset($_POST["user"]) && isset($_POST["pass"])){
	$username = $_POST["user"];
        $password = $_POST["pass"];
	$user = sha1($username);

	include('dbconnect.php');

	if(!$dbconn){
		echo "Unable to connect to database.";
		exit;
	}//End if

	$query = "SELECT username, emailhash, passhash, salthash FROM Member WHERE username = $1 OR emailhash = $2";


	$stmt = pg_prepare($dbconn, "logggingIn", $query);

	if(!$stmt)
		echo "<br />Error: Unable to prepare statement.";
	else{
		$params = array($username, $user);
		$result = pg_execute($dbconn, "logggingIn", $params);

		if($result){
                	$row = pg_fetch_assoc($result);
			$username = $row["username"];
			$salt = $row["salthash"];

			$password_hash = sha1($password);
        		$password_hash = sha1($salt.$password_hash);

			if($password_hash == $row["passhash"]){
                		$_SESSION["username"] = $username;
				header("Location: home.php");

			}//End if

		}//End if
		else
			echo "<br />Password or user name incorrect";

	}//End else

	pg_close($dbconn);

}//End if

include('index.php');

?>