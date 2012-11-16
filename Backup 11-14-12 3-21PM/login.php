<?php
session_start();
if(isset($_POST["submit-login"])){
	$username = $_POST["user"];
	$password = $_POST["pass"];
	$emailhash = sha1($username);
	
	if(empty($username) || empty($password)){
		$error = "Username and password required to log in.<br/></br>";
	}
//Connect to database and query for matching username or email. If the information is found then validate the passwords authenticity.	
	else{
		include('dbconnect.php');

		if(!$dbconn){
			echo "Unable to connect to database.";
			exit;
		}//End if

		$query = "SELECT username, emailhash, passhash, salthash FROM Member WHERE username = $1 OR emailhash = $2";
		$stmt = pg_prepare($dbconn, "logggingIn", $query);

		if(!$stmt)
			$error = "Error: Unable to prepare statement.<br /><br />";
		else{
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

?>
<form name="login" method="POST" action="index.php">

</br>

<input type="text" name="user" size=20 value="Username" class="text ui-corner-all placeholder" onfocus="ClearPlaceHolder (this)" onblur="SetPlaceHolder (this)" />
</br></br>

<span><input type="text" name="pass" size=20 value="Password" class="text ui-corner-all placeholder" onfocus="ClearPlaceHolder(this)" onblur="SetPlaceHolder(this)" /></span>
</br></br>

<input type="submit" name="submit-login" value="Login" class="ui-button ui-widget ui-state-default ui-corner-all">
</br></br>

</form>

<!-- Display any error messages.-->
<?php if(isset($error)) { echo $error; unset($error); } ?>
</br>

<a href='reset.php'>Forgotten password?</a>
</br>