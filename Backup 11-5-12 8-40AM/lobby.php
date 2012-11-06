<?php
include 'dbconnect.php';
session_start();
if(!$_SESSION['username'])
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
	$userschoolid = $row['schoolid'];
}

// If choosing a different university, override my default (Travis)
if (isset($_POST["university"])) {
	$userschoolid = $_POST["university"];
}

?>
<!DOCTYPE html>
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
<title>Lobby</title>

</head>
<body>
	
	<h1>Welcome To The Lobby!</h1>
		
	<h2>Search Decks:</h2>
	<form action="lobby.php" method="POST">
		<select name="university" >
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
				if($userschoolid == $schoolID)
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
		<select name="semester">
			<option value="SP12">Spring 2012</option>
			<option value="FA12">Fall 2012</option>
			<option value="SP13">Spring 2013</option>
			<option value="null" selected="selected">Select A Semester</option>
		</select>
		<input type="submit" value="Submit" name="search">	
	</form>
	<div>

	<?php
	
	if(isset($_POST["search"]))
	{
		$university = $_POST["university"];
		$semester = $_POST["semester"];
	


		if($university != "null" && $semester != "null")
		{
			$query = "SELECT deck.course, deck.deckname, deck.professor, deck.username, deck.deckid
					FROM deck	
					INNER JOIN school ON deck.schoolid = school.schoolid
					WHERE school.schoolid =$1 AND deck.semester = $2;";
			$stmt = pg_prepare($dbconn, "both", $query);
			$searchresult = pg_execute($dbconn, "both", array($university, $semester));
		}
		else if($university != "null" )
		{
			$query = "SELECT deck.course, deck.deckname, deck.professor, deck.username, deck.deckid
					FROM deck
					INNER JOIN school ON deck.schoolid = school.schoolid
					WHERE school.schoolid =$1 ";
			$stmt = pg_prepare($dbconn, "both", $query);
			$searchresult = pg_execute($dbconn, "both", array($university,));
		}
		else if($semester != "null")
		{
			$query = "SELECT deck.course, deck.deckname, deck.professor, deck.username, deck.deckid
					FROM deck
					INNER JOIN school ON deck.schoolid = school.schoolid
					WHERE  deck.semester = $1;";
			$stmt = pg_prepare($dbconn, "both", $query);
			$searchresult = pg_execute($dbconn, "both", array($semester));
		}
		else
		{
			echo "Please Select From The Drop Down Tables Above To Search!</br>";
		}
		if($searchresult)
		{
			echo "Search Successful! Although it is possible no results were found. </br>";
		}
		else
		{
			echo 'Search was unsuccessful. </br>';
		}
		echo '<table>';	
		while($row = pg_fetch_assoc($searchresult))
		{
			deckTable($row);
		}
		echo '</table>';
	}	
	else
	{
		$query = "SELECT deck.course, deck.deckname, deck.professor, deck.deckid, deck.username
				FROM deck
				INNER JOIN school ON deck.schoolid = school.schoolid
				INNER JOIN member ON member.schoolid = school.schoolid
				WHERE member.username =$1 ";
		$stmt = pg_prepare($dbconn, "default", $query);
		$defaultresult = pg_execute($dbconn, "default", array($_SESSION['username']));
	
		if(!empty($defaultresult))
		{
			echo '<table>';
			while($row = pg_fetch_assoc($defaultresult))
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


	function deckTable($row){
		static $counter = 0;
		if($counter == 0)						
		{
	
			echo '<tr>';
			echo '<th class="even">Course</th>';
			echo '<th class="odd">Deck Name</th>';
			echo '<th class="even">Professor</th>';
			echo '<th class="odd">Author</th>';
			echo '<th class="odd">Join?</th>';
			echo '</tr>';
		
			$counter = $counter + 1;
		}
		echo '<form action="gameproc.php" method="POST">';
		echo '<tr>';
		echo '<td class="even">' . $row['course'] . '</td>';
		echo '<td class="odd">' . $row['deckname'] . '</td>';
		echo '<td class="even">' . $row['professor'] . '</td>';
		echo '<td class="odd">' . $row['username'] . '</td>';
		echo '<input type="hidden" name="deckid" value="'.$row['deckid'].'"  />';
		echo '<input type="hidden" name="function" value="joinGame"/>';
		echo '<td> <input class="odd" type="submit" name="joinBtn" value="Study?"  /></td>';
		echo '</tr>';
		echo '</form>';


	}
	
	?>
	</div>
	</br></br></br></br>
	<hr>
	</br></br></br></br>
	<div>
	<form action="listDecks.php" method="POST">
		<input type="submit" value="View My Decks"/>
	</form>
	</br>
	<form method='POST' action='logout.php' onsubmit='return checkSubmit();'>
		<input type='submit' name='log-out' value='Logout' />
	</form>
	</br>
	</div>
	
	
	

</body>
</html>
