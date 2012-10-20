
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
	
	<h1>Home Page</h1>
		
	<h2>Search Games:</h2>
	<form action="lobby2.php" method="POST">
		<select name="university" >
			<option value="1">Mizzou</option>
			<option value="null" selected="selected">Select A University</option>
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
	include 'connect.php';

	if(isset($_POST["search"]))
	{
		$university = $_POST["university"];
		$semester = $_POST["semester"];
	

		if($university != "null" && $semester != "null")
		{
			$query = "SELECT course.course, deck.deckname, course.professor, member.username
					FROM deck
					INNER JOIN course ON deck.classid = course.classid
					INNER JOIN school ON course.schoolid = school.schoolid
					INNER JOIN member ON member.userid = deck.userid
					WHERE school.schoolid =$1 AND course.semester = $2;";
			$stmt = pg_prepare($connection, "both", $query);
			$result = pg_execute($connection, "both", array($university, $semester));
		}
		else if($university != "null" )
		{
			$query = "SELECT course.course, deck.deckname, course.professor, member.username
					FROM deck
					INNER JOIN course ON deck.classid = course.classid
					INNER JOIN school ON course.schoolid = school.schoolid
					INNER JOIN member ON member.userid = deck.userid
					WHERE school.schoolid =$1 ";
			$stmt = pg_prepare($connection, "both", $query);
			$result = pg_execute($connection, "both", array($university,));
		}
		else if($semester != "null")
		{
			$query = "SELECT course.course, deck.deckname, course.professor, member.username
					FROM deck
					INNER JOIN course ON deck.classid = course.classid
					INNER JOIN school ON course.schoolid = school.schoolid
					INNER JOIN member ON member.userid = deck.userid
					WHERE  course.semester = $1;";
			$stmt = pg_prepare($connection, "both", $query);
			$result = pg_execute($connection, "both", array($semester));
		}
		else
		{
			echo "Please Select From The Drop Down Tables Above To Search!</br>";
		}
		if($result)
		{
			echo "Search Successful! Although it is possible no results were found. </br>";
		}
		else
		{
			echo 'Search was unsuccessful. </br>';
		}
		echo '<table>';	
		while($row = pg_fetch_assoc($result))
		{
			deckTable($row);
		}
		echo '</table>';
	}	

	function deckTable($row){
		static $counter = 0;
		if($counter == 0)							//country table construction
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
		echo '<form action="lobby.php" method="POST">';
		echo '<tr>';
		echo '<td class="even">' . $row['course'] . '</td>';
		echo '<td class="odd">' . $row['deckname'] . '</td>';
		echo '<td class="even">' . $row['professor'] . '</td>';
		echo '<td class="odd">' . $row['username'] . '</td>';
	
		/*echo '<input type="hidden" name="countrycode" value="'.$row['countrycode'].'"  />';
	 	echo '<input type="hidden" name="tablename" value="'.$_POST["search-type"].'"  />';
		echo '<input type="hidden" name="countryname" value='.$row['name'].'"/>';*/
		echo '<td> <input class="odd" type="submit" name="joinBtn" value="Join?"  /></td>';
		echo '</tr>';


	}
	
	?><p>This is where current games will be displayed</p>
	</div>
	</br></br></br></br>
	<div>
	<form action="create.php" method="POST">
		<input type="submit" value="Create New Game"/>
	</form>
	</br>
	
	<form action="new.php" method="POST">
		<input type="submit" value="Create New Deck"/>
	</form>
	</br>
	<form action="edit.php" method="POST">
		<input type="submit" value="Edit a Previous Deck"/>
	</form>
	</br>
	<form action="delete.php" method="POST">
		<input type="submit" value="Delete a Deck"/>
	</form>
	</br>
	</div>
	
	
	

</body>
</html>
