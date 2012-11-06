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

<!doctype html>
 
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>jQuery UI Dialog - Modal form</title>
	<link rel="stylesheet" href="../studygame/jquery-ui-1.9.1.custom/css/start/jquery-ui-1.9.1.custom.css" />
	
	<title>Home</title>
	
	<script src="../studygame/jquery-ui-1.9.1.custom/js/jquery-1.8.2.js"></script>
	<script src="../studygame/jquery-ui-1.9.1.custom/js/jquery-ui-1.9.1.custom.js"></script>
    <style>
        body { font-size: 62.5%; }
        label, input { display:block; }
        input.text { margin-bottom:12px; width:95%; padding: .4em; }
        fieldset { padding:0; border:0; margin-top:25px; }
        h1 { font-size: 1.2em; margin: .6em 0; }
        div#users-contain { width: 350px; margin: 20px 0; }
        div#users-contain table { margin: 1em 0; border-collapse: collapse; width: 100%; }
        div#users-contain table td, div#users-contain table th { border: 1px solid #eee; padding: .6em 10px; text-align: left; }
        .ui-dialog .ui-state-error { padding: .3em; }
        .validateTips { border: 1px solid transparent; padding: 0.3em; }
    </style>
    <script>
    $(function() {
//Previous fields

				        var username = $( "#username" ),
						email = $( "#email" ),
						password = $( "#password" ),
						conpassword = $( "#conpassword" ),
						allFields = $( [] ).add( username ).add( email ).add( password ),
						tips = $( ".validateTips" );		
 
        function updateTips( t ) {
            tips
                .text( t )
                .addClass( "ui-state-highlight" );
            setTimeout(function() {
                tips.removeClass( "ui-state-highlight", 1500 );
            }, 500 );
        }
 
        function checkLength( o, n, min, max ) {
			if ( o.val().length > max || o.val().length < min ) {

                o.addClass( "ui-state-error" );
                updateTips( "Length of " + n + " must be between " +
                    min + " and " + max + "." );
                return false;
            } else {
                return true;
            }
        }
 
        function checkRegexp( o, regexp, n ) {
            if ( !( regexp.test( o.val() ) ) ) {
                o.addClass( "ui-state-error" );
                updateTips( n );
                return false;
            } else {
                return true;
            }
        }
 
        $( "#dialog-form" ).dialog({
            autoOpen: false,
            height: 385,
            width: 350,
            modal: true,
            buttons: {				
				"Create an account": function() {
				
                    var bValid = true;
                    allFields.removeClass( "ui-state-error" );
                    bValid = bValid && checkLength( username, "username", 3, 20 );
                    bValid = bValid && checkLength( email, "email", 6, 80 );
					bValid = bValid && (conpassword.val() == password.val());
					
					alert(conpassword.val());
 					alert(password.val());
                    bValid = bValid && checkRegexp( username, /^[a-z]([0-9a-z_])+$/i, "Username may consist of a-z, 0-9, underscores, begin with a letter." );
                    bValid = bValid && checkRegexp( email, /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i, "eg. ui@jquery.com" );
					bValid = bValid && checkRegexp( password, /^([0-9a-zA-Z])+$/, "Password field only allow : a-z 0-9" );
					bValid = bValid && checkRegexp( conpassword, /^([0-9a-zA-Z])+$/, "Password field only allow : a-z 0-9" );
 
                    if ( bValid ) {
						$("#register").submit();
                    }
					else{
						alert("Not valid");
					}
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            },
            close: function() {
                allFields.val( "" ).removeClass( "ui-state-error" );
            }
        });
 
        $( "#create-user" )
            .button()
            .click(function() {
                $( "#dialog-form" ).dialog( "open" );
            });
			
    });
    </script>
</head>
<body>
 
<div id="dialog-form" title="Registration">
    <p class="validateTips">All form fields are required.</p>
 
    <form id="register" action="login.php" method="POST">
    <fieldset>
			
		
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

<label class='required' for='username' id='uname'></label>
<input type='text' name='username' id='username' placeholder='Username' class="text ui-widget-content ui-corner-all" />
<br />

<label class='required' for='email' id='mail'></label>
<input type='text' name='email' id='email' placeholder='Email' class="text ui-widget-content ui-corner-all" />
<br />

<label class='required' for='password' id='pass'></label>
<input type='password' name='password' id='password' placeholder='Password' class="text ui-widget-content ui-corner-all" />
<br />

<label class='required' for='conpassword' id='conpass'></label>
<input type='password' name='conpassword' id='conpassword' placeholder='Confirm password' class="text ui-widget-content ui-corner-all" />
<br />

    </fieldset>
    </form>
</div>

<?php if(isset($error)) { echo $error; unset($error); } ?>
 
<button id="create-user">Create new user</button>
<a href='reset.php'>Forgotten password</a>

 
</body>
</html>