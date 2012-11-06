<?php
session_start();
/*if(!$_SESSION['username']);
{
        echo "You do not have access to this page. Please sign in!";
        exit;
}*/
?>


<!DOCTYPE html>
<html>

<head>

<title> Title </title>

<style type ="text/css">

body{
text-align: center;
}

table, td, tr, th
{
        border: 1px solid black;
        border-collapse: collapse;
        margin: auto;
        text-align: center;
}

td, tr, th
{
        padding: 4px 3px;
}


</style>

</head>

<body>


<h1>Card List</h1>
	<h2>Deck Name:</h2>
	<h3>Available Cards:</h3>
	


<?php

include 'dbconnect.php';
$query = "SELECT ";







?>




	<table>
		<tr>
		<th colspan="3">Card List</th>
		</tr>
		
		<tr>
		<th>Question</th>
		<th>Edit Answer</th>
		<th>Delete</th>
		</tr>
		
<!-- LOOP, EDIT, DELETE-->

<!-- while($row = mysql_fetch_array($result))-->
	{
		<tr>
		<th></th>
		<th><input Type=Button onClick "edit.php;" Value="Edit" </th>
		<th><input Type=Button onClick "delete.php;" Value="Delete" </th>
		</tr>
	}	
	</table>


<?php

if isset($_POST['']))
	{


	}





?>


<br><br><br><br>	
<hr>
<h1>Add a Question</h1>	
	<div>
        <form action="listCards.php" method="POST">
                <label for="question">Enter A Question</label>
                <input type="text" name="question"/>
                </br></br>
                 <label for="timeLimit">Time Limit</label>
                <input type="text" name="timelimit"/>
                </br></br>     

        </form>
    </div>


	<form action="createcard.php" method="POST">
		<input type="submit" value="Create Card" name="create"/>
	</form>

</body>


<?php
include 'db_connect.php';

if (isset($_POST['']))
	{


	}



?>






</html>
