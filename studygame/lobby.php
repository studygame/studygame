<?php
include 'dbconnect.php';
session_start();
if(!isset($_SESSION['username']))
{
	echo "You do not have access to this page. Please sign in first!";
	echo "</br></br></br>";
	echo "<a href='index.php'>Login</a>";
	exit;
}

$query = "SELECT schoolid FROM member where username = $1";
$stmt = pg_prepare($dbconn, "getSchoolId", $query);
$resultschoolid = pg_execute($dbconn, "getSchoolId", array($_SESSION['username']));
	
if(!empty($resultschoolid))
{
	$row = pg_fetch_assoc($resultschoolid);
	if (!isset($_SESSION['schoolid'])) {
		$_SESSION['schoolid'] = $row['schoolid'];
	}
}

// If choosing a different university, override my default (Travis)
if (isset($_POST["university"])) {
	$_SESSION['schoolid'] = $_POST["university"];
}

if (!isset($_SESSION['schoolid'])) {
	$_SESSION['schoolid'] = 'null';
}

// check for semester selection
// save to session variable
if (isset($_POST['semester'])) {
	$_SESSION['semester'] = $_POST['semester'];
}

?>
<!DOCTYPE html>
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
#university {
	width: 300px;
}
#semester {
	width: 150px;
}
</style>
<title>Lobby</title>

</head>
<body>
	<span id="toolbar" class="ui-widget-header">
		<input id='decks' class='left ui-button ui-widget ui-state-default ui-corner-all' type="submit" value="My Decks" onclick="window.location.href='listDecks.php'"/>
		<input id='logout' class='right ui-button ui-widget ui-state-default ui-corner-all' type='submit' name='log-out' value='Logout' onclick="window.location.href='logout.php'" />
	</span>
	
	<h1>Welcome To The Lobby!</h1>
		
	<h2>Search For A Deck To Study:</h2>
	<form action="lobby.php" method="POST">
		<select id="university" name="university">
			<option value="null" selected="selected">Select A University</option>
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
				if($_SESSION['schoolid'] == $schoolID)
				{
					echo "<option value=$schoolID selected ='selected'>".$schoolNAME."</option>";
				}
				else
				{
				echo "<option value=$schoolID>".$schoolNAME."</option>";
				}
			}//End while
			?>		
		</select>
		<select id="semester" name="semester">
			<option value="SP12" <?php if(isset($_SESSION['semester']) && $_SESSION['semester'] == 'SP12') echo 'selected="selected"'; ?>>Spring 2012</option>
			<option value="FA12" <?php if(isset($_SESSION['semester']) && $_SESSION['semester'] == 'FA12') echo 'selected="selected"'; ?>>Fall 2012</option>
			<option value="SP13" <?php if(isset($_SESSION['semester']) && $_SESSION['semester'] == 'SP13') echo 'selected="selected"'; ?>>Spring 2013</option>
			<option value="null" <?php if(!isset($_SESSION['semester']) || (isset($_SESSION['semester']) && $_SESSION['semester'] == 'null')) echo 'selected="selected"'; ?>>Semester?</option>
		</select>
		<input id="button" type="submit" value="Submit" name="search">	
	</form>
	<div>
	</br>
	<?php
	
	
	$university = $_SESSION['schoolid'];
	$semester = $_SESSION["semester"] ? $_SESSION["semester"] : 'null';
	
	if($university != "null" && $semester != "null")
	{
		$query = "SELECT COUNT(card.cardid) as numcards, deck.course, deck.deckname, deck.professor, deck.username, deck.deckid, deck.difficulty
				FROM deck	
				INNER JOIN school ON deck.schoolid = school.schoolid
				LEFT OUTER JOIN card ON card.deckid = deck.deckid
				WHERE school.schoolid =$1 AND deck.semester = $2
				GROUP BY course, deckname, professor, deck.username, deck.deckid, deck.difficulty
				ORDER BY deck.deckid;";
		$stmt = pg_prepare($dbconn, "both", $query);
		$searchresult = pg_execute($dbconn, "both", array($university, $semester));
	}
	else if($university != "null" )
	{
		$query = "SELECT COUNT(card.cardid) as numcards, deck.course, deck.deckname, deck.professor, deck.username, deck.deckid, deck.difficulty
				FROM deck
				INNER JOIN school ON deck.schoolid = school.schoolid
				LEFT OUTER JOIN card ON card.deckid = deck.deckid
				WHERE school.schoolid =$1
				GROUP BY course, deckname, professor, deck.username, deck.deckid, deck.difficulty
				ORDER BY deck.deckid;";
		$stmt = pg_prepare($dbconn, "both", $query);
		$searchresult = pg_execute($dbconn, "both", array($university));
	}
	else if($semester != "null")
	{
		$query = "SELECT COUNT(card.cardid) as numcards, deck.course, deck.deckname, deck.professor, deck.username, deck.deckid, deck.difficulty
				FROM deck
				INNER JOIN school ON deck.schoolid = school.schoolid
				LEFT OUTER JOIN card ON card.deckid = deck.deckid
				WHERE  deck.semester = $1
				GROUP BY course, deckname, professor, deck.username, deck.deckid, deck.difficulty
				ORDER BY deck.deckid;";
		$stmt = pg_prepare($dbconn, "both", $query);
		$searchresult = pg_execute($dbconn, "both", array($semester));
	}
	else
	{
		$query = "SELECT COUNT(card.cardid) as numcards,deck.course, deck.deckname, deck.professor, deck.deckid, deck.username, deck.difficulty
				FROM deck
				LEFT OUTER JOIN card ON deck.deckid = card.deckid
				GROUP BY course, deckname, professor, deck.deckid, deck.username, deck.difficulty
				ORDER BY deck.deckid";
		$stmt = pg_prepare($dbconn, "default", $query);
		$searchresult = pg_execute($dbconn, "default", array());

		if(!empty($searchresult))
		{
			echo '<table class="ui-widget ui-widget-content">';
			while($row = pg_fetch_assoc($searchresult))
			{
				deckTable($row);
			}
			echo '</table>';
		}
		else
		{
			echo "Query was not executed!</br></br>";
		}
	}
	if(!$searchresult)
	{
		echo 'Search was unsuccessful. </br>';
	}
	echo '<table>';	
	while($row = pg_fetch_assoc($searchresult))
	{
		deckTable($row);
	}
	echo '</table>';

	function deckTable($row){
		static $counter = 0;
		if($counter == 0)						
		{
	
			echo '<tr>';
			echo '<th class="ui-widget-header">Course</th>';
			echo '<th class="ui-widget-header">Deck Name</th>';
			echo '<th class="ui-widget-header">Professor</th>';
			echo '<th class="ui-widget-header"># of Cards</th>';
			echo '<th class="ui-widget-header">Difficulty</th>';
			echo '<th class="ui-widget-header">Author</th>';
			echo '<th class="ui-widget-header">Join?</th>';
			echo '</tr>';
		
			$counter = $counter + 1;
		}
		echo '<form action="gameproc.php" method="POST">';
		echo '<tr>';
		echo '<td class="even">' . $row['course'] . '</td>';
		echo '<td class="odd">' . $row['deckname'] . '</td>';
		echo '<td class="even">' . $row['professor'] . '</td>';
		echo '<td>'.$row['numcards'].'</td>';
		echo '<td class="odd">' . $row['difficulty'] . '</td>';
		echo '<td class="even">' . $row['username'] . '</td>';
		echo '<input type="hidden" name="deckid" value="'.$row['deckid'].'"  />';
		echo '<input type="hidden" name="function" value="joinGame"/>';
		echo '<td> <input id="button" class="odd" type="submit" name="joinBtn" value="Study?"  /></td>';
		echo '</tr>';
		echo '</form>';


	}
	
	?>
</body>
</html>
