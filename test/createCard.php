<?php
if(isset($_POST["submit"]))
{
	session_start();
	
	$_SESSION['deckname'] = $_POST['deckname'];
}
else if(isset($_POST["addAnswers"]))
{
	session_start();
	//Query will go here based off whether 2 4 or 6 was chosen
	echo 'print success or failure, try again sort of thing';
}
else
{
	header("Location: /~cs3380f12grp8/test/createDeck.php");
}
?>
<form action="createAnswers.php" method="POST">
	<label for="question">Enter the question for the card: </label>
	<input type="text" name="question" />
	<br/><br/>
	<label for="timer">Enter the amount of time (in seconds) the user should have to answer: </label>
	<input type="text" name="timer"/> (seconds)
	<br/><br/>
	Choose the number of answers you will have for this card:
	<input type="radio" name="numAnswers" value="2"/>2
	<input type="radio" name="numAnswers" value="4"/>4
	<input type="radio" name="numAnswers" value="6"/>6
	<br/><br/>
	<input type="submit" name="cardcreate" value="Create Card!">
</form>

<br/><br/>
<form action="lobby2.php" method="POST">
	<input type="submit" name="home" value="Return Home?"/>
</form>


