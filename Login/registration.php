<html>
<head>
<title>Registration</title>
</head>
<body>

<form method='POST' action='registration.php' onsubmit='return checkSubmit();'>

What is your University?<br />
<select name="programs">
	<option value=""></option>
</select>
<br />

<label class='required' for='username' id='username'></label>
<input type='text' name='username' id='username' placeholder='Username'>
<br />

<label class='required' for='email' id='email'></label>
<input type='text' name='email' id='email' placeholder='email'>
<br />

<label class='required' for='password' id='password'></label>
<input type='password' name='password' id='password' placeholder='Password'>
<br />

<label class='required' for='conpassword' id='conpassword'></label>
<input type='password' name='conpassword' id='conpassword' placeholder='Confirm Password'>
<br />

<input type='submit' name='submit-insert' value='Insert'>
<br /><br />

<a href='li.php'>Return to login</a>
<br />

</form>

<?php
// Check if the insert button was pressed
if(isset($_POST['submit-insert'])){

	// Check all of the input fields
	$username = !empty($_POST['username']) ? $_POST['username'] : null;
	$password = !empty($_POST['password']) ? $_POST['password'] : null;

	if ($username === null || $password === null){
		echo "One or more required fields was not filled out.";

	}//End if
	else{
		include('db_connect.php');

        	if(!$conn){
                	echo "Unable to connect to database.";
                	exit;
        	}//End if

		$salt = rand(0, 32767);
        	$password_hash = sha1($salt.$password);

		$query = "INSERT INTO lab7.user_info (username) VALUES ($1)";
		$stmt = pg_prepare($conn, "insertAccount", $query);

		if(!$stmt)
			echo "<br />Error: Unable to prepare statement.";
		else{
			$params = array($username);
			$result = pg_execute($conn, "insertAccount", $params);

			if($result){
				$query = "INSERT INTO lab7.authentication (username, password_hash, salt) VALUES ($1, $2, $3)";

				$stmt = pg_prepare($conn, "insertAuth", $query);

				if(!$stmt)
					echo "<br />Error: Unable to prepare statement.";
				else{
					$params = array($username, $password_hash, $salt);

					$result = pg_execute($conn, "insertAuth", $params);
					if($result){
						session_start();
						header("Location: http://babbage.cs.missouri.edu/~cs3380f12grp8/Login/home.php");

					}//End if
					else
						echo "<br />An unknown error occurred during creation.";

				}//End else

			}//End if
			else
                        	echo "<br />User name already exists.";

		}//End else

	}//End else

}//End if

?>

</body>

</html>