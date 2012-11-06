

<!DOCTYPE html>
<html>
<head>
<style type="text/css">
body{
	text-align: center;
}

</style>
<title>Lobby</title>

</head>
<body>
	
	<h1>Home Page</h1>
		
	<h2>Search Games:</h2>
	<form action="lobby.php" method="POST">
		<select name="university" >
			<option value="Mizzou">Mizzou</option>
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
	

		if($university != "null" && $semester != null)
		{
			$query = "SELECT course.course, deck.deckname, course.professor, deck.userid
					FROM deck
					INNER JOIN course ON deck.classid = course.classid
					INNER JOIN school ON course.schoolid = school.schoolid
					WHERE school.schoolid =$1 AND course.semester = $2;";
			$stmt = pg_prepare($connection, "both", $query);
			$result = pg_execute($connection, "both", array($unversity, $semester));
		}

		if($result)
		{
			echo "Search Successful! Although it is possible no results were found. </br>";
		}
		else
		{
			echo 'Search was unsuccessful. </br>';
			exit;
		}
	
		while($row = pg_fetch_assoc($result))
		{
			deckTable($row);
		}
		
	}	

	function deckTable($row){
		static $counter = 0;
		if($counter == 0)							//country table construction
		{
	
			echo '<tr>';
			echo '<th class="even">Deck Name</th>';
			echo '<th class="odd">University</th>';
			echo '<th class="even">School</th>';
			echo '<th class="odd">Department</th>';
			echo '<th class="even">Course</th>';
			echo '<th class="odd">Semester</th>';
			echo '<th class="even">Professor</th>';
			echo '<th class="odd">Join?</th>';
			echo '</tr>';
		
			$counter = $counter + 1;
		}
		echo '<form action="lobby.php" method="POST">';
		echo '<tr>';
		echo '<td class="even">' . $row['name'] . '</td>';
		echo '<td class="odd">' . $row['university'] . '</td>';
		echo '<td class="even">' . $row['school'] . '</td>';
		echo '<td class="odd">' . $row['dept'] . '</td>';
		echo '<td class="even">' . $row['course'] . '</td>';
		echo '<td class="odd">' . $row['sem'] . '</td>';
		echo '<td class="even">' . $row['prof'] . '</td>';
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
