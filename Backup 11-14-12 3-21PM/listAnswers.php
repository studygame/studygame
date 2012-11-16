 <?php
include 'dbconnect.php';
session_start();
$access = 0; 
$_SESSION['fromanswers'] = 5;
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

	<link rel="stylesheet" href="jquery-ui-1.9.1.custom/css/start/jquery-ui-1.9.1.custom.css" />

	<script src="jquery-ui-1.9.1.custom/js/jquery-1.8.2.js"></script>
	<script src="jquery-ui-1.9.1.custom/js/jquery-ui-1.9.1.custom.js"></script>
	
	<script type="text/javascript" charset="utf-8">
		$(function(){
			$("#createForm").dialog({resizable: false, modal: true, autoOpen: false, width: 500, title: "Create New Answer"});
			$("#addNewAnswer").button().click(function() { $("#createForm").dialog("open"); });
			$("input[type=submit]").button();
		});
	</script>

<style type="text/css">
select
{
	text-align:center;
	font-family: Verdana, Arial, sans-serif;
	font-size: 1em;
}
body
{
	text-align: center;
	font-family: Verdana, Arial, sans-serif; 
	font-size: 1em;
}
table, td, tr, th
{
	border: 1px solid black;
	border-collapse: collapse;
	text-align: center;
	margin: auto;
}
td, tr, th
{
	padding: 4px 3px;
} 
</style>
<title>Answer List</title>

</head>
<body>
	<span id="toolbar" class="ui-widget-header ">
		<input id='button' class='left ui-button ui-widget ui-state-default ui-corner-all' type="submit" value="Lobby" onclick="window.location.href='lobby.php'"/>
		<input id='button' class='left ui-button ui-widget ui-state-default ui-corner-all' type="submit" value="Deck List" onclick="window.location.href='listDecks.php'"/>
		<input id='button' class='left ui-button ui-widget ui-state-default ui-corner-all' type="submit" value="Card List" onclick="window.location.href='listCards.php'"/>
		<input id='button' class='right ui-button ui-widget ui-state-default ui-corner-all' type='submit' name='log-out' value='Logout' onclick="window.location.href='logout.php'" />
	</span>
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
	$query = "DELETE FROM answer WHERE answer = $1 AND cardid = $2";
	$stmt = pg_prepare($dbconn, "deleteAnswer", $query);
	$deleteResult = pg_execute($dbconn, "deleteAnswer", array($_POST['deleteAnswer'], $_SESSION['cardid']));

	if(!$deleteResult)
	{
		echo 'Deletion Failed! Please Try Again.</br></br></br>';
	}
}

?>
	
	<h1>Current Answer List</h1>
	<h4>Deck: "<?php echo $_SESSION['deckname']?>"</h4>
	<h4>Question: "<?php echo $_SESSION['question']?>"</h4>
	
<?php
$query = "SELECT answer, correct FROM answer WHERE cardid = $1 ORDER BY answerid;";
$stmt = pg_prepare($dbconn, "answerList", $query);
$result = pg_execute($dbconn, "answerList", array($_SESSION['cardid']));

if(!$result)
{	
	echo 'No Answers Have Been Created Yet!</br></br></br>';
	
}
else
{
	echo '<table class="ui-widget ui-widget-content">';
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
		echo '<th class="ui-widget-header">Answer</th>';
		echo '<th class="ui-widget-header">Correct</th>';
		echo '<th class="ui-widget-header">Delete?</th>';
		echo '</tr>';
	
		$counter = $counter + 1;
	}
	echo '<form action="listAnswers.php" method="POST">';
	echo '<tr>';
	echo '<td class="even">' . $row['answer'] . '</td>';
	if($row['correct'] == 0)
	{
		echo '<td class="odd"></td>';
	}
	else
	{
		echo '<td class="odd">X</td>';
	}
	
	echo '<input type="hidden" name="deleteAnswer" value="'.$row['answer'].'"  />';
	echo '<td> <input id="button" class="even ui-button ui-widget ui-state-default ui-corner-all" type ="submit" name="deleteanswer" value="Delete?" /></td>';
	echo '</tr>';
	echo '</form>';
}
?>
	<br/><br/>
	<button id="addNewAnswer" class="ui-button ui-widget ui-state-default ui-corner-all">Create a New Answer</button>
	<div id="createForm">
		<br/>
		<form action="listAnswers.php" method="POST">
			<label for="answer">Enter An Answer For The Card:</label><br/><br/>
			<input name="answer" class='text ui-corner-all' maxlength="50"/>
			</br></br>
			<label for="correct">Is This The Correct Answer?:</label>
			<input type="checkbox" name="correct"/>
			</br></br>
			<input type="hidden" name="cardid" value="<?php echo $_SESSION['cardid']; ?>">
			<input id="button" type="submit" value="Create!" name="createanswer">	
			<br/><br/>
		</form>
	</div>
	<br/><br/>

</body>
</html>
		




