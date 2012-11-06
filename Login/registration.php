<?php
include('dbconnect.php');

if(!$dbconn)
	$ERROR = "Unable to connect to database.";
else{
	$query = "SELECT schoolname, schoolid FROM School";
	$stmt = pg_prepare($dbconn, "getSchools", $query);

	if(!$stmt)
		$ERROR = "Error: Unable 2 prepare statement.";
	else{
		$result = pg_execute($dbconn, "getSchools", array());
	
		if(empty($result))
			$ERROR = "Error getting schools";

	}//End else

}//End else

// Check if the insert button was pressed
if(isset($_POST['submit-insert'])){

	// Check all of the input fields
	$email = !empty($_POST['email']) ? $_POST['email'] : null;
	$username = !empty($_POST['username']) ? $_POST['username'] : null;
	$password = !empty($_POST['password']) ? $_POST['password'] : null;


	if(empty($_POST['school']))
		$school = NULL;
	else
		$school = $_POST['school'];
	

	$ERROR = "";
	if($username === null || $email === null || $password === null){
		$ERROR = "One or more required fields was not filled out.";

	}//End if
	else if($password === $conpassword){
		$ERROR = "Password fields do not match.";

	}//End if
	else{
		include('dbconnect.php');

        	if(!$dbconn)
                	$ERROR = "Unable to connect to database.";
		else{
			$salt = rand(0, 32767);
       		$salt = sha1($salt);
			
			$passhash = sha1($password);
        	$passhash = sha1($salt.$passhash);

			$email = sha1($email);

echo "$username</br>";
echo "$email</br>";
echo "$passhash</br>";
echo "$salt</br>";
echo "$school</br>";

			$query = "INSERT INTO Member (username, emailhash, passhash, salthash, schoolid) VALUES ($1, $2, $3, $4, $5)";
			$stmt = pg_prepare($dbconn, "insertAccount", $query);

			if(!$stmt)
				$ERROR = "Error: Unable to prepare statement.";
			else{
			
echo "$username</br>";
echo "$email</br>";
echo "$passhash</br>";
echo "$salt</br>";
echo "$school</br>";
			
				$params = array($username, $email, $passhash, $salt, $school);
				$result = pg_execute($dbconn, "insertAccount", $params);
	
				if(pg_affected_rows($result) == 1){
					session_start();
					$_SESSION["username"] = $username;
					header("Location: home.php");

				}//End if
				else
					$ERROR = "User name already exists.";
	
			}//End else

		}//End else

	}//End else

}//End if

/*
include('dbconnect.php');

if(!$dbconn)
	$ERROR = "Unable to connect to database.";
else{
	$query = "SELECT schoolname, schoolid FROM School";
	$stmt = pg_prepare($dbconn, "getSchools", $query);

	if(!$stmt)
		$ERROR = "Error: Unable 2 prepare statement.";
	else{
		$result = pg_execute($dbconn, "getSchools", array());
	
		if(empty($result))
			$ERROR = "Error getting schools";

	}//End else

}//End else

*/
?>

<html>
<head>
<title>Registration</title>
</head>
<body>

<form method='POST' action='registration.php' onsubmit='return checkSubmit();'>

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

<input type='submit' name='submit-insert' value='Create new account'>
<br />

</form>

<?php echo "$ERROR"; ?>

</body>

</html>