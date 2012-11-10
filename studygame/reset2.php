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
        //$("input[type=text], input[type=password]").css("font-size", "1.2em");
    });
</script>
<style type="text/css"">
	label {
		display: block;
		float: left;
		width: 160px;
	}
    body{
		font-family: Verdana, Arial, sans-serif; 
		font-size: 1em;
	}
</style>

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
<?php
    //make sure connection is set
	if(!$dbconn) {
       echo "Unable to connect to database.";
       exit;
   	}
	//pull user info from database
    $key = $_GET['key'];
    if(!$key) {
    	echo"<br/>Error: You've used the wrong link. Please try again.";
    	goto always;
    }
		
    $query = "SELECT username, salthash, passhash, emailhash FROM Member WHERE passresethash = $1";
	$stmt = pg_prepare($dbconn, "verify", $query);
	if(!$stmt) {
		echo"<br/>Error: Unable to prepare statement.";
		goto always;
	}
	$result = pg_execute($dbconn, "verify", array($key));
	if(!$result) {
		echo"<br/>An unknown error occurred during registration.";
		goto always;
	}
	$numRows = pg_num_rows($result);
	//make sure it found something
	if($numRows == 0) {
		echo "<br/>You've used the wrong link, or your link has expired. Please try again.";
		goto always;
	}
	$row = pg_fetch_assoc($result);
	$username = $row['username'];
	$salt = $row['salthash'];
	$emailhash = $row['emailhash'];
	echo"<br/>"
?>
<form method='POST' action='reset2.php?key=<?php echo $key; ?>'>
	<label for='pass' >New Password: </label>
	<input type='password' name='pass' id='pass' value='' class='text ui-corner-all'/>
	<br/> 
	<label for='confirmpass'>Confirm New Password: </label>
	<input type='password' name='confirmpass' id='confirmpass' value='' class='text ui-corner-all'/>
	<br/><br/>
	<input type='submit' name='submit-reset' value='Submit' />
	<br/><br/><a href='index.php'>Click here</a> to return to the log-in page. 
</form>
<?php
	//when they press the button
	if(isset($_POST['submit-reset'])) {
		//grab new password
		$pass=!empty($_POST['pass']) ? $_POST['pass'] : null;
		$confirmpass=!empty($_POST['confirmpass']) ? $_POST['confirmpass'] : null;
		if($pass === null || $confirmpass === null) {
			echo "<br/>Error: One or more required fields was not filled out.";	
			goto always;
		}
		//make sure password fields match
		if($pass != $confirmpass) {
			echo"<br/>Error: Password fields do not match.";
			goto always;
		}
		//set new password in database
		$pass=sha1($pass);
		$pass=sha1($salt.$pass);
		
		$newQuery = "UPDATE Member SET passHash=$1, passresethash=NULL WHERE emailhash=$2";
		$newStmt = pg_prepare($dbconn, "update", $newQuery);
		if(!$newStmt) {
			echo"<br/>Error: Unable to prepare statement.";
			goto always;
		}
		$newResult = pg_execute($dbconn, "update", array($pass, $emailhash));
		if(!$newResult) {
			echo"<br/>An unknown error occurred.";
			goto always;
		}
		else {
			echo"<br/>Your password was successfully reset!";
		}
	}
	always:
	if($numRows == 0) {
		echo "<br/><br/><a href='index.php'>Click here</a> to return to the log-in page. ";
	}
?>
</body>
</html>
