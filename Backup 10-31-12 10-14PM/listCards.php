<?php
include 'dbconnect.php';
session_start();
$access = 0;
if(!$_SESSION['username'])
{
	header("Location: index.php");
	exit;
}
if(isset($_POST['deckbutton'])) {
	header("Location: listDecks.php");
}
if(isset($_POST['cardbutton']))
{
	$access++;
}
if(isset($_POST['deletecard']))
{
	$access++;
}
if(isset($_POST['updatecard']))
{
	$access++;
}
if(isset($_POST['updatedeck']))
{
	$access++;
	$_SESSION['deckid'] = $_POST['deckid'];

	$query = "SELECT * FROM deck WHERE deckid = $1";
	$stmt = pg_prepare($dbconn, "getdeck", $query);
	$deckResult = pg_execute($dbconn, "getdeck", array($_SESSION['deckid']));

	$row = pg_fetch_assoc($deckResult);

	$_SESSION['deckname']  = $row['deckname'];

}

if(isset($_POST['createcard']))
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
<title>Card List</title>

</head>
<body>

	<h1>Create New Cards</h1>
	<h4>Deck: "<?php echo $_SESSION['deckname']?>"</h4>	
	<div>
		<form action="listCards.php" method="POST">
			<label for="question">Enter A Question For The Card</label>
			<input type="text" name="question"/>
			</br></br>
			<label for="time">Enter The Time Limit For The Game Feature</label>
			<input type="text" name="time"/>
			</br></br>
			<input type="hidden" name="deckid" value="<?php echo $_SESSION['deckid']; ?>">
			<input type="submit" value="Create Card!" name="createcard">	
			<br/><br/>
			<input type="submit" value="Return to Deck List" name="deckbutton">
		</form>
	</div>
<?php
if(isset($_POST['createcard']))
{
	$deckid = $_SESSION['deckid'];
	$question = $_POST['question'];
	$time = $_POST['time'];

	if($question == null || $time == null)
	{
		echo 'You Must Input All Fields!</br>';
	}
	else
	{
		$query = "INSERT INTO card (deckid, question, timelimit)
			VALUES ($1, $2, $3);";
		$stmt = pg_prepare($dbconn, "newAnswer", $query);
		$insertResult = pg_execute($dbconn, "newAnswer", array($deckid, $question, $time));
		if(!$insertResult)
		{
			echo 'Card Creation Failed. Please Try Again</br></br>';
		}
	}
}



if(isset($_POST['deletecard']))
{
	$query = "DELETE FROM answer WHERE cardid = $1";
	$stmt = pg_prepare($dbconn, "deleteAnswer", $query);
	$deleteResult = pg_execute($dbconn, "deleteAnswer", array($_POST['cardid']));

	if(!$deleteResult)
	{
		echo 'Deletion Failed! Please Try Again.</br></br></br>';
	}

	$query = "DELETE FROM card WHERE cardid = $1";
	$stmt = pg_prepare($dbconn, "deleteCard", $query);
	$deleteResult = pg_execute($dbconn, "deleteCard", array($_POST['cardid']));

	if(!$deleteResult)
	{
		echo 'Deletion Failed! Please Try Again.</br></br></br>';
	}
}

?>

	</br></br></br></br>
	<hr>
	<h1>Current Card List</h1>

<?php
$query = "SELECT * FROM card WHERE deckid = $1;";
$stmt = pg_prepare($dbconn, "answerList", $query);
$result = pg_execute($dbconn, "answerList", array($_SESSION['deckid']));

if(!$result)
{	
	echo 'No Cards Have Been Created Yet!</br></br></br>';
	
}
else
{
	echo '<table>';
	while($row = pg_fetch_assoc($result))
	{
		cardTable($row);
	}
	echo '</table>';
}
function cardTable($row){
	static $counter = 0;
	if($counter == 0)							
	{

		echo '<tr>';
		echo '<th class="even">Question</th>';
		echo '<th class="odd">Time Limit</th>';
		echo '<th class="even">Update?</th>';
		echo '<th class="odd">Delete?</th>';
		echo '</tr>';
	
		$counter = $counter + 1;
		
	}

	echo '<tr>';
	echo '<td class="even">' . $row['question'] . '</td>';
	echo '<td class="odd">'.$row['timelimit'].'</td>';
	echo '<form action="listAnswers.php" method="POST">';
	echo '<input type="hidden" name="cardid" value="'.$row['cardid'].'"/>';	
	echo '<td> <input class="even" type ="submit" name="updatecard" value="Update?" /></td>';
	echo '</form>';
	echo '<form action="listCards.php" method="POST">';
	echo '<input type="hidden" name="cardid" value="'.$row['cardid'].'"/>';	
	echo '<td> <input class="odd" type="submit" name="deletecard" value="Delete?"/></td>';
	echo '</form>';
	echo '</tr>';
}
?>

</body>
</html>
		




