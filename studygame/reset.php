<!DOCTYPE html>
<html>
<head>
<title>Password Reset</title>
	<link rel="stylesheet" href="jquery-ui-1.9.1.custom/css/start/jquery-ui-1.9.1.custom.css" />
	<script src="jquery-ui-1.9.1.custom/js/jquery-1.8.2.js"></script>
	<script src="jquery-ui-1.9.1.custom/js/jquery-ui-1.9.1.custom.js"></script>
	<script>
    $(function() {
        $( "input[type=submit]" )
            .button();
    });
    </script>
<?php
	session_start();
    include('dbconnect.php');
	$name=$_SESSION['username'];
	//if logged in, jump to homepage
    if(isset($name)) {
        header("Location: lobby.php");
        goto always;
    }
?>
</head>
<body>
<form method='POST' action='reset.php'>
	<br/><label for="email">Enter your email to reset your password: </label>
	<br/><input type='text' size=46 name='email' id='email' placeholder='name@example.com' class='text ui-widget-content ui-corner-all' />
	<br/><br/>
	<input type='submit' name='submit-reset' id='create-user' value='Submit' />
	<br/><br/><a href='index.php'>Click here</a> to return to the log-in page.
</form>
<?php
	//make sure connection is set
    if(!$dbconn) {
        echo "Unable to connect to database.";
        exit;
    }
	//when they press the button
    if(isset($_POST['submit-reset'])) {
		//grab email
     	$email=!empty($_POST['email']) ? $_POST['email'] : null;
    	if($email === null) {
    	 	echo "<br/><br/>Error: The email field was not filled out.";	
			goto always;
     	}
		//compare email to database
     	$emailhash=sha1($email);
     	$query = "SELECT emailhash, passhash, salthash FROM Member WHERE (emailhash=$1)";
		$stmt = pg_prepare($dbconn, "verify", $query);
		if(!$stmt) {
			echo"<br/><br/>Error: Unable to prepare statement.";
			goto always;
		}
		$result = pg_execute($dbconn, "verify", array($emailhash));
		if(!$result) {
			echo"<br/><br/>An unknown error occurred during user lookup.";
			goto always;
		}
		$numRows = pg_num_rows($result);
		//make sure it found something
        if($numRows == 0) {
			echo "<br/>Invalid email.";
			goto always;
		}
		$row=pg_fetch_assoc($result);
		$passHash = $row['passhash'];
		$saltHash = $row['salthash'];
		$tf = 0;
		$testQuery = "SELECT passresethash FROM Member WHERE passresethash = $1";
		$testStmt = pg_prepare($dbconn, "test", $testQuery);
		while($tf == 0) {
			$key = str_rand(40, 'alphanum');
			$key = sha1($key);
			//check if key is in the table, if it is, make a new one
			if(!$testStmt) {
				echo"<br/><br/>Error: Unable to prepare statement.";
				echo pg_last_error();
				goto always;
			}
			$testResult = pg_execute($dbconn, "test", array($key));
			if(!$testResult) {
				echo"<br/><br/>An unknown error occurred during user lookup.";
				goto always;
			}
			$numTestRows = pg_num_rows($testResult);
			if($numTestRows == 0)
				$tf = 1;
			else 
				$tf = 0;
		}
		$keyQuery = "UPDATE Member SET passresethash=$1 WHERE emailhash=$2";
		$keyStmt = pg_prepare($dbconn, "key", $keyQuery);
		if(!$keyStmt) {
			echo"<br/><br/>Error: Unable to prepare statement.";
			goto always;
		}
		$keyResult = pg_execute($dbconn, "key", array($key, $emailhash));
		if(!$keyResult) {
			echo"<br/><br/>An unknown error occurred during user lookup.";
			goto always;
		}
		//send out reset email
		$link='http://babbage.cs.missouri.edu/~cs3380f12grp8/studygame/reset2.php?key='.$key;
		$message="<br/><a href='$link'>Click here</a> to reset your password for Study Flash. <br/><br/><br/>If you did not request a password reset, please disregard this message.<br/>";
		mail($email, 'Password reset for Study Flash', $message, 'Content-Type: text/html; charset=ISO-8859-1');
		if($message) {
			echo"<br/>Your email has been sent. Please check your email to finish resetting your password.";
		}
	}
    always:
    
    function str_rand($length = 40, $seeds = 'alphanum') {
	    // Possible seeds
	    $seedings['alpha'] = 'abcdefghijklmnopqrstuvwqyz';
	    $seedings['numeric'] = '0123456789';
	    $seedings['alphanum'] = 'abcdefghijklmnopqrstuvwqyz0123456789';
	    $seedings['hexidec'] = '0123456789abcdef';
	    // Choose seed
	    if (isset($seedings[$seeds]))
	    {
	        $seeds = $seedings[$seeds];
	    }
	    // Seed generator
	    list($usec, $sec) = explode(' ', microtime());
	    $seed = (float) $sec + ((float) $usec * 100000);
	    mt_srand($seed);
	    // Generate
	    $str = '';
	    $seeds_count = strlen($seeds);
	    for ($i = 0; $length > $i; $i++)
	    {
	        $str .= $seeds{mt_rand(0, $seeds_count - 1)};
	    }   
	    return $str;
	}
?>
</body>
</html>
