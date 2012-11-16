<?php
session_start();
include('dbconnect.php');

//Connect to database and retreive school names for drop-down box in form.
if(!$dbconn)
	$error = "Unable to connect to database.";
else{
	$query = "SELECT schoolname, schoolid FROM School";
	$stmt = pg_prepare($dbconn, "getSchools", $query);

	if(!$stmt)
		$error = "Error: Unable 2 prepare statement.";
	else{
		$schoolresult = pg_execute($dbconn, "getSchools", array());
	
		if(empty($schoolresult))
			$error = "Error getting schools";

	}//End else

}//End else

//Check if the insert button was pressed.
if(isset($_POST['submit-insert'])){

//Check all of the input fields were filled if they have insert, otherwise, set to null.
	$email = !empty($_POST['email']) ? $_POST['email'] : null;
	$username = !empty($_POST['username']) ? $_POST['username'] : null;
	$password = !empty($_POST['password']) ? $_POST['password'] : null;
	$conpassword = !empty($_POST['conpassword']) ? $_POST['conpassword'] : null;
	$school = !empty($_POST['school']) ? $_POST['school'] : null;

//Send an error message for required empty required fields.		
	if($username === null || $email === null || $password === null || $conpassword === null){
		$error = "</br></br>One or more required fields was not filled out.";
	}//End if
	else if($password != $conpassword){
		$error = "Password fields do not match.";
	}//End if
	
//Connect to database and insert user info
	else{
		include('dbconnect.php');

        if(!$dbconn)
                $error = "Unable to connect to database.";
		else{
       		$salthash = sha1(mt_rand(0, 99999));
			$passhash = sha1($password);
        	$passhash = sha1($salthash.$passhash);
			$emailhash = sha1($email);

			$query = "INSERT INTO Member (username, emailhash, passhash, salthash, schoolid) VALUES ($1, $2, $3, $4, $5)";
			$stmt = pg_prepare($dbconn, "insertAccount", $query);

			if(!$stmt)
				$error = "Error: Unable to prepare statement.";
			else{
				$params = array($username, $emailhash, $passhash, $salthash, $school);
				$result = pg_execute($dbconn, "insertAccount", $params);
	
				if(pg_affected_rows($result) == 1){
					$_SESSION["username"] = $username;
					header("Location: lobby.php");

				}//End if
				else
					$error = "User name already exists.</br>";
	
			}//End else

		}//End else
		
		pg_close($dbconn);

	}//End else

}//End if

?>
<div id="registration">

<form name="registration" method='POST' action='index.php'>

	<select name="school">
		<option value="">Select your University</option>
			<?php

//Fetch previously queried school names for the drop-down box.
				while($row = pg_fetch_assoc($schoolresult)){
					$schoolID = $row["schoolid"];
					$schoolNAME = $row["schoolname"];
					echo "<option value=$schoolID>".$schoolNAME."</option>";
				}//End while
			?>
	</select>
	<br /><br />

	<input type='text' name='username' id='username' value='Username' onfocus='ClearPlaceHolder(this)' onblur='SetPlaceHolder(this)'>
	<br /><br />

	<input type='text' name='email' id='email' value='Email' onfocus='ClearPlaceHolder(this)' onblur='SetPlaceHolder(this)' >
	<br /><br />

	<input type='text' name='password' id='password' value='Password' onfocus='ClearPlaceHolder(this)' onblur='SetPlaceHolder(this)'>
	<br /><br />

	<input type='text' name='conpassword' id='conpassword' value='Confirm password' onfocus='ClearPlaceHolder(this)' onblur='SetPlaceHolder(this)'>
	<br /><br />

	<input type='submit' name='submit-insert' value='Create new account'>
	<br />

</form>

</div>

<!-- Display any error messages.-->
<?php if(isset($error)) { echo "$error"; unset($error); } ?>