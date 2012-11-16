<html>
<head>
	<title>Home</title>
	
	<link rel="stylesheet" href="jquery-ui-1.9.1.custom/css/start/jquery-ui-1.9.1.custom.css" />
	<script src="jquery-ui-1.9.1.custom/js/jquery-1.8.2.js"></script>
	<script src="jquery-ui-1.9.1.custom/js/jquery-ui-1.9.1.custom.js"></script>
	<script>

//Open dialog box if registration button clicked.			
		$(function(){
			$("#register").button().click(function(){
				$("#registration").dialog("open");				
			});//End .click

//Format the dialog box.			
			$("input[type=submit]").button();
			$("input[type=text], input[type=password]").addClass("text ui-corner-all placeholder");
			$("#registration").dialog({autoOpen: false, modal: true, width: 325, resizable: false, title: "Create a New Account", position: { my: "center", at: "center", of: window }});
			
		});//End function

		
//Change the input type to password and remove the placeholder text if the user selects the field.
		function ClearPlaceHolder (input) {
			if (input.value == input.defaultValue) {
				$(input).removeClass('placeholder');
				input.value = "";
				
				if ((input.name.indexOf('pass') != -1) && input.type != 'password') {
					input.type= 'password';
				}
			}//End if
		}//End function ClearPlaceHolder
		
		
//Change the input type to text and insert placeholder text if the field is blank.
		function SetPlaceHolder (input) {
			if (input.value == "") {
				if (input.type == 'password') {
					input.type = 'text';
				}
				$(input).addClass('placeholder');
				input.value = input.defaultValue;
			}//End if
		}//End function SetPlaceHolder
		
	</script>
		
<style type="text/css">
select {
	text-align: center;
	font-family: Verdana, Arial, sans-serif;
	width: 300px;
}
body{
	text-align: center;
	font-family: Verdana, Arial, sans-serif; 
}
.placeholder {
	color: #bbb;
}
#registration {
	text-align: center;
}

</style>

</head>

<body>
	<span id="toolbar" class="ui-widget-header"></span>
	<h2>Welcome to StudyFlash!</h2>
	<h3>Please log in below</h2>
		
<?php
session_start();

//If the user is already logged in, redirect them to the lobby page.
if (isset($_SESSION["username"])) header("Location: lobby.php");

//Include the login form and logic.
include('login.php');

echo "</br><hr /></br>";
?>

<button id="register" class="ui-button ui-widget ui-state-default ui-corner-all">Register</button>

<?php include('registration.php'); ?>

</body>

</html>