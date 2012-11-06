<?php
session_start();
include('dbconnect.php');

if(!$dbconn)
	$error = "Unable to connect to database.";
else {
	$query = "SELECT schoolname, schoolid FROM School";
	$stmt = pg_prepare($dbconn, "getSchools", $query);

	if(!$stmt)
		$error = "Error: Unable 2 prepare statement.";
	else {

		$schoolresult = pg_execute($dbconn, "getSchools", array());
	
		if(empty($schoolresult))
			$error = "Error getting schools";

	}//End else

}//End else

// Check if the insert button was pressed
if(isset($_POST['submit-insert'])) {

	// Check all of the input fields
	$email = !empty($_POST['email']) ? $_POST['email'] : null;
	$username = !empty($_POST['username']) ? $_POST['username'] : null;
	$password = !empty($_POST['password']) ? $_POST['password'] : null;
	$conpassword = !empty($_POST['conpassword']) ? $_POST['conpassword'] : null;

	if(empty($_POST['school']))
		$school = NULL;
	else
		$school = $_POST['school'];
	
	if($username === null || $email === null || $password === null || $conpassword === null){
		$error = "One or more required fields was not filled out.";
	}//End if
	else if($password != $conpassword){
		$error = "Password fields do not match.";
	}//End if
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
					$error = "User name already exists.";
	
			}//End else

		}//End else
		
		pg_close($dbconn);

	}//End else

}//End if

?>
<h2>Create a New Account</h2>
<form name="registration" method='POST' action='index.php'>

<select name="school">
	<option value="">Select your University</option>
<?php
	while($row = pg_fetch_assoc($schoolresult)){
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

<input type='submit' name='submit-insert' value='Create new account'>
<br />

</form>

<?php if(isset($error)) { echo "$error"; unset($error); } ?>
