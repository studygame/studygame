<?php
session_start();
if(isset($_POST["submit-login"])){

	$username = $_POST["user"];
	$password = $_POST["pass"];
	$emailhash = sha1($username);
	
	if (empty($username) || empty($password)) {
		$error = "Username and password required to log in.<br/></br>";
	}
	
	else {

		include('dbconnect.php');

		if(!$dbconn) {
			echo "Unable to connect to database.";
			exit;
		}//End if

		$query = "SELECT username, emailhash, passhash, salthash FROM Member WHERE username = $1 OR emailhash = $2";
		$stmt = pg_prepare($dbconn, "logggingIn", $query);

		if(!$stmt)
			$error = "Error: Unable to prepare statement.<br /><br />";
		else {
			$params = array($username, $emailhash);
			$result = pg_execute($dbconn, "logggingIn", $params);

			if(pg_num_rows($result) == 1){

	            $row = pg_fetch_assoc($result);
				$username = $row["username"];
				$salthash = $row["salthash"];

				$password_hash = sha1($password);
				$password_hash = sha1($salthash.$password_hash);

				if($password_hash == $row["passhash"]){
	                $_SESSION["username"] = $username;
					header("Location: lobby.php");
				}//End if
				else
					$error = "Password or user name incorrect<br /><br />";

			}//End if
			else
				$error = "Password or user name incorrect<br /><br />";

		}//End else

		pg_close($dbconn);

	}//End if
	
}//End if

//include('index2.php');

?>
<h2>Log in</h2>
<form name="login" method="POST" action="index.php">
<input type="text" name="user" size=20 placeholder="Username">
</br>

<input type="password" name="pass" size=20 placeholder="Password">
</br>

<input type="submit" name="submit-login" value="Login">
</br></br>

<?php if(isset($error)) { echo $error; unset($error); } ?>

<a href='reset.php'>Forgotten password</a>

</br>

</form>
