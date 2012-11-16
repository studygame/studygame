<?php
include 'dbconnect.php';
session_start();

$username = $_SESSION['username'];

if(!$_SESSION['username'])
{
	echo "You do not have access to this page. Please sign in!";
	echo "</br></br></br>";
	echo "<a href='index.php'>Return Home</a>";
	exit;	
}



$query = "SELECT schoolid  FROM member where username = $1";
$stmt = pg_prepare($dbconn, "getSchoolId", $query);
$resultschoolid = pg_execute($dbconn, "getSchoolId", array($_SESSION['username']));
	
if(!empty($resultschoolid))
{
	$row = pg_fetch_assoc($resultschoolid);
	$userschoolid = $row['schoolid'];
}
?>
<html>
<head>	
	<link rel="stylesheet" href="jquery-ui-1.9.1.custom/css/start/jquery-ui-1.9.1.custom.css" />

	<script src="jquery-ui-1.9.1.custom/js/jquery-1.8.2.js"></script>
	<script src="jquery-ui-1.9.1.custom/js/jquery-ui-1.9.1.custom.js"></script>
	
	<script type="text/javascript" charset="utf-8">
		$(function(){
			$("input[type=submit]").button();
		});
	</script>

<style type="text/css">

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

/*
table, td, tr, th
{
	border: 1px solid black;
	border-collapse: collapse;
	margin: auto;
	text-align: center;
}
*/

td, tr, th
{
	padding: 4px 3px;
}
</style>
<title>Deck List</title>

</head>
<body class="ui-form">
	<h1>Create New Deck</h1>
		
	<div>
		<form action="listDecks.php" method="POST">
			<select name="university" >
				<option value="null" selected="selected">Select A University</option>;

			<?php
			$query = "SELECT schoolname, schoolid FROM School";
			$stmt = pg_prepare($dbconn, "getSchools", $query);
	
			if(!$stmt)
				$ERROR = "Error: Unable 2 prepare statement.";
			else
			{
				$result = pg_execute($dbconn, "getSchools", array());
	
			if(empty($result))
				$ERROR = "Error getting schools";
			}
			while($row = pg_fetch_assoc($result)){
			$schoolID = $row["schoolid"];
			$schoolNAME = $row["schoolname"];
			if($userschoolid == $schoolID)
			{
				echo "<option value=$schoolID selected='selected'>".$schoolNAME."</option>";
			}
			else
			{
			echo "<option value=$schoolID>".$schoolNAME."</option>";
			}
		}//End while
			?>
			</select>
			</br></br>
			<select name="semester">
				<option value="SP12">Spring 2012</option>
				<option value="FA12">Fall 2012</option>
				<option value="SP13">Spring 2013</option>
				<option value="null" selected="selected">Select A Semester</option>
			</select>
			</br></br>
			<label for="course">Enter Course Name (e.g CS3380)</label>
			<input type="text" name="course"/>
			</br></br>
			<label for="professor">Enter Professor's Last Name (e.g Klaric)</label>
			<input type="text" name="professor"/>
			</br></br>
			<label for="deckname">Enter Deck Name (e.g Midterm 1)</label>
			<input type="text" name="deckname"/>
			</br></br>
			<input id="button" type="submit" value="Create!" name="createdeck">	
		</form>
	</div>
<?php
if(isset($_POST['deletedeck']))
{	

	$query = "SELECT * FROM card where deckid = $1";
	$stmt = pg_prepare($dbconn, "getcards", $query);
	$cardresult = pg_execute($dbconn, "getcards", array($_POST['deckid']));

	while($row = pg_fetch_assoc($cardresult))
	{
		$query = "DELETE FROM answer WHERE cardid = $1";
		$stmt = pg_prepare($dbconn, "deleteanswer", $query);
		$deleteanswers = pg_execute($dbconn, "deleteanswer", array($row['cardid']));

		$query = "DELETE FROM card where cardid = $1";
		$stmt = pg_prepare($dbconn, "deletecard", $query);
		$deletecard = pg_execute($dbconn, "deletecard", array($row['cardid']));
	}

	$query = "DELETE FROM game where deckid = $1";
	$stmt = pg_prepare($dbconn, "deletegame", $query);
	$deletegame = pg_execute($dbconn, "deletegame", array($_POST['deckid']));

	$query = "DELETE FROM deck where deckid = $1";
	$stmt = pg_prepare($dbconn, "deletedeck", $query);
	$deletegame = pg_execute($dbconn, "deletedeck", array($_POST['deckid']));
}


if(isset($_POST['createdeck']))
{
	$university = $_POST['university'];
	$semester = $_POST['semester'];
	$course = $_POST['course'];
	$professor = $_POST['professor'];
	$deckname = $_POST['deckname'];

	if($university == null || $semester == null || $course == null || $professor == null || $deckname == null)
	{
		echo 'All Fields Must Be Completed To Create A New Deck</br>';
			
	}
	else
	{
		$query = "INSERT INTO deck (schoolid, course, semester, professor, deckname, username)
				VALUES ($1, $2, $3, $4, $5, $6);";
		$stmt = pg_prepare($dbconn, "newDeck", $query);
		$insertResult = pg_execute($dbconn, "newDeck", array($university, $course, $semester, $professor, $deckname, $username));
		if(!$insertResult)
		{
			echo 'Deck Creation Failed. Please Try Again</br></br>';
		}

	}
}	
?>
    <form action="listDecks.php" method="POST">
		<input id = "button" type="submit" name="Home" value="Return to Lobby">
		<?php
			if(isset($_POST['Home'])) {
				?>
				<script language="javascript">
					var x=confirm("Are you sure you want to leave this page? You may have created an empty deck. Click OK to proceed or Cancel to stay on this page and edit your decks.");
					if (x==true) {
						x=window.location='lobby.php';
					}
				</script>
				<?php
			}
		?>
	</form>

	</br></br></br></br>
	<hr>
	<h1>Current Deck List</h1>

<?php
$query = "SELECT course, deckname, professor, username, deckid FROM DECK WHERE deck.username= $1;";
$stmt = pg_prepare($dbconn, "deckList", $query);
$result = pg_execute($dbconn, "deckList", array($username));

if(!empty($result))
{
	echo '<table id="currentDeckTable" class="ui-widget ui-widget-content">';
	while($row = pg_fetch_assoc($result))
	{
		deckTable($row);
	}
		echo '</table>';
			

}
else
{
	echo 'No Decks Have Been Created Yet!</br></br></br>';
}
function deckTable($row){
	static $counter = 0;
	if($counter == 0)							//country table construction
	{

		echo '<tr>';
		echo '<th class="ui-widget-header">Course</th>';
		echo '<th class="ui-widget-header">Deck Name</th>';
		echo '<th class="ui-widget-header">Professor</th>';
		echo '<th class="ui-widget-header">Update?</th>';
		echo '<th class="ui-widget-header">Delete?</th>';
		echo '</tr>';
	
		$counter = $counter + 1;
	}

	echo '<tr>';
	echo '<td class="even">' . $row['course'] . '</td>';
	echo '<td class="odd">' . $row['deckname'] . '</td>';
	echo '<td class="even">' . $row['professor'] . '</td>';
	
	/*echo '<input type="hidden" name="countrycode" value="'.$row['countrycode'].'"  />';
 	echo '<input type="hidden" name="tablename" value="'.$_POST["search-type"].'"  />';
	echo '<input type="hidden" name="countryname" value='.$row['name'].'"/>';*/
	echo '<form action="listCards.php" method="POST">';
	echo '<input type="hidden" name="deckid" value="'.$row['deckid'].'"/>';
	echo '<td> <input id="button" class="even" type="submit" name="updatedeck" value="Edit Cards?"  /></td>';
	echo '</form>';
	echo '<form action="listDecks.php" method="POST">';
	echo '<input type="hidden" name="deckid" value="'.$row['deckid'].'"/>';
	echo '<td> <input id="button" class="odd" type ="submit" name="deletedeck" value="Delete?" /></td>';
	echo '</tr>';
	echo '</form>';
}
?>
	</br></br></br></br>

</body>
</html>
