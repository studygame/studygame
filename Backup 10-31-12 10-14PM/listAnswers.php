<?php
include 'dbconnect.php';
session_start();
$access = 0;

if(isset($_POST['cardbutton'])) {
        header("Location: listCards.php");
}
if(isset($_POST['deckbutton'])) {
        header("Location: listDecks.php");
}
if(!$_SESSION['username'])
{
	header("Location: index.php");
	exit;
}
if(isset($_POST['updatecard']))
{
	$access++;
	$_SESSION['cardid'] = $_POST['cardid'];

	$query = "SELECT * FROM card WHERE cardid = $1";
	$stmt = pg_prepare($dbconn, "getquestion", $query);
	$questionResult = pg_execute($dbconn, "getquestion", array($_SESSION['cardid']));

	$row = pg_fetch_assoc($questionResult);

	$_SESSION['question']  = $row['question'];
}
if(isset($_POST['deleteanswer']))
{
	$access++;
}
if(isset($_POST['createanswer']))
{
	$access++;
}
if ($access != 1)
{	
	echo "You do not have access to this page. Please go through deck pages sequentially</br></br>";
	echo "<a href='listDecks.php'>View Decks</a></br></br>";
	echo "<a href='lobby.php'>Return Home</a></br></br>";
	exit;
}


?>
<html>
<head>
<style type="text/css">


body
{
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
<title>Answer List</title>

</head>
<body>

	<h1>Create New Answers</h1>
	<h4>Deck: "<?php echo $_SESSION['deckname']?>"</h4>
	<h4>Question: "<?php echo $_SESSION['question']?>"</h4>
	<div>
		<form action="listAnswers.php" method="POST">
			<label for="answer">Enter An Answer For The Card</label>
			<input type="text" name="answer"/>
			</br></br>
			<label for="correct">Check Box If This Is The Correct Answer</label>
			<input type="checkbox" name="correct"/>
			</br></br>
			<input type="hidden" name="cardid" value="<?php echo $_SESSION['cardid']; ?>">
			<input type="submit" value="Create!" name="createanswer">	
			<br/><br/>
		</form>

		<form action="listCards.php" method="POST">
			<input type="submit" value="Return to Card List" name="cardbutton">
		</form>
		<form action="listAnswers.php" method="POST">
			<input type="submit" value="Return to Deck List" name="deckbutton">
		</form>
	</div>
<?php
if(isset($_POST['createanswer']))
{
	$answer = $_POST['answer'];
	$correct = $_POST['correct'];

	if($correct == null)
	{
		$correct = 0;
	}
	else
	{
		$correct = 1;
	}

	if($answer == null)
	{
		echo 'You Must Input An Answer!</br>';
		exit;	
	}
	else
	{
		$query = "INSERT INTO answer (cardid, answer, correct)
			VALUES ($1, $2, $3);";
		$stmt = pg_prepare($dbconn, "newAnswer", $query);
		$insertResult = pg_execute($dbconn, "newAnswer", array($_SESSION['cardid'], $answer, $correct));
		if(!$insertResult)
		{
			echo 'Answer Creation Failed. Please Try Again</br></br>';
		}
	}
}




if(isset($_POST['deleteanswer']))
{
	$query = "DELETE FROM answer WHERE answer = $1";
	$stmt = pg_prepare($dbconn, "deleteAnswer", $query);
	$deleteResult = pg_execute($dbconn, "deleteAnswer", array($_POST['deleteAnswer']));

	if(!$deleteResult)
	{
		echo 'Deletion Failed! Please Try Again.</br></br></br>';
	}
}

?>

	</br></br></br></br>
	<hr>
	<h1>Current Answer List</h1>

<?php
$query = "SELECT answer, correct FROM answer WHERE cardid = $1;";
$stmt = pg_prepare($dbconn, "answerList", $query);
$result = pg_execute($dbconn, "answerList", array($_SESSION['cardid']));

if(!$result)
{	
	echo 'No Answers Have Been Created Yet!</br></br></br>';
	
}
else
{
	echo '<table>';
	while($row = pg_fetch_assoc($result))
	{
		answerTable($row);
	}
	echo '</table>';
}
function answerTable($row){
	static $counter = 0;
	if($counter == 0)							//country table construction
	{

		echo '<tr>';
		echo '<th class="even">Answer</th>';
		echo '<th class="odd">Correct</th>';
		echo '<th class="even">Delete?</th>';
		echo '</tr>';
	
		$counter = $counter + 1;
	}
	echo '<form action="listAnswers.php" method="POST">';
	echo '<tr>';
	echo '<td class="even">' . $row['answer'] . '</td>';
	if($row['correct'] == 0)
	{
		echo '<td class="odd"><input type="radio" name="correctAnswer"/></td>';
	}
	else
	{
		echo '<td class="odd"><input type="radio" name="correctAnswer" checked="checked"/></td>';
	}
	
	echo '<input type="hidden" name="deleteAnswer" value="'.$row['answer'].'"  />';
	echo '<td> <input class="even" type ="submit" name="deleteanswer" value="Delete?" /></td>';
	echo '</tr>';
	echo '</form>';
}
?>
<h3>*Be Sure To Have Only One Correct Answer!</h3>
</body>
</html>
		




