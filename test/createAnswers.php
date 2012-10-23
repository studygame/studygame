<?php
session_start();
if(isset($_POST['cardcreate']))
{
	$_SESSION['timer'] = $_POST['timer'];
	$_SESSION['numAnswers'] = $_POST['numAnswers'];
	$_SESION['question'] = $_POST['question'];
}
else
{
	header("Location: /~cs3380f12grp8/test/createDeck.php");
}

if($_SESSION['numAnswers'] == 2)
{
?>
<form action="createCard.php" method="POST">
	<label for="answer1">Enter your first answer: </label>
	<input type="text" name="answer1">
	<br/>
	<input type="radio" name="correct">Click to choose as right answer
	<br/><br/>
	<label for="answer2">Enter your second answer: </label>
	<input type="text" name="answer2">
	<br/>
	<input type="radio" name="correct">Click to choose as right answer
	<br/><br/>
	<input type="submit" name="addAnswers" value="Add Answers to Card!">
</form>
<?php
}
if($_SESSION['numAnswers'] == 4)
{
?>

<form action="createCard.php" method="POST">
	<label for="answer1">Enter your first answer: </label>
	<input type="text" name="answer1">
	<br/>
	<input type="radio" name="correct">Click to choose as right answer
	<br/><br/>
	<label for="answer2">Enter your second answer: </label>
	<input type="text" name="answer2">
	<br/>
	<input type="radio" name="correct">Click to choose as right answer
	<br/><br/>
	<label for="answer3">Enter your third answer: </label>
	<input type="text" name="answer3">
	<br/>
	<input type="radio" name="correct">Click to choose as right answer
	<br/><br/>
	<label for="answer4">Enter your fourth answer: </label>
	<input type="text" name="answer4">
	<br/>
	<input type="radio" name="correct">Click to choose as right answer
	<br/><br/>
	<input type="submit" name="addAnswers" value="Add Answers to Card!">


</form>

<?php
}
if($_SESSION['numAnswers'] == 6)
{
?>

<form action="createCard.php" method="POST">
	<label for="answer1">Enter your first answer: </label>
	<input type="text" name="answer1">
	<br/>
	<input type="radio" name="correct">Click to choose as right answer
	<br/><br/>
	<label for="answer2">Enter your second answer: </label>
	<input type="text" name="answer2">
	<br/>
	<input type="radio" name="correct">Click to choose as right answer
	<br/><br/>
	<label for="answer3">Enter your third answer: </label>
	<input type="text" name="answer3">
	<br/>
	<input type="radio" name="correct">Click to choose as right answer
	<br/><br/>
	<label for="answer4">Enter your fourth answer: </label>
	<input type="text" name="answer4">
	<br/>
	<input type="radio" name="correct">Click to choose as right answer
	<br/><br/>
	<label for="answer5">Enter your fifth answer: </label>
	<input type="text" name="answer5">
	<br/>
	<input type="radio" name="correct">Click to choose as right answer
	<br/><br/>
	<label for="answer6">Enter your sixth answer: </label>
	<input type="text" name="answer6">
	<br/>
	<input type="radio" name="correct">Click to choose as right answer
	<br/><br/>
	<input type="submit" name="addAnswers" value="Add Answers to Card!">


<?php
}
?>
