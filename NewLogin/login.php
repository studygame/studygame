<?php
session_start();
if(isset($_POST["submit-login"])){

	$username = $_POST["user"];
	$password = $_POST["pwd"];
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
            height: 400,
            width: 577,
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
 
<div id="dialog-form" title="Login">
    <p class="validateTips">All form fields are required.</p>
 
    <form id="login" action="login.php" method="POST">
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
<input type='text' name='user' id='user' placeholder='Username' class="text ui-widget-content ui-corner-all" />
<br />

<label class='required' for='email' id='mail'></label>
<input type='text' name='email' id='email' placeholder='Email' class="text ui-widget-content ui-corner-all" />
<br />

<label class='required' for='password' id='pass'></label>
<input type='password' name='pwd' id='pwd' placeholder='Password' class="text ui-widget-content ui-corner-all" />
<br />

<label class='required' for='conpassword' id='conpass'></label>
<input type='password' name='conpwd' id='conpwd' placeholder='Confirm password' class="text ui-widget-content ui-corner-all" />
<br />
    </fieldset>
    </form>
</div>

<?php if(isset($error)) { echo $error; unset($error); } ?>
</br></br>


<button id="create-user">Create new user</button>
</br>

<a href='reset.php'>Forgotten password</a>

 
</body>
</html>