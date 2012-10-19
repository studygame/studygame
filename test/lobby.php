

<!DOCTYPE html>
<html>
<head>
<style type="text/css">
body{
	text-align: center;
}

</style>
<title>Main Page</title>

</head>
<body>
	
	<h1>Home Page</h1>
		
	<h2>Search Games:</h2>
	<form action="lobby.php" method="POST">
		<input type="text" id="universitySearch"/>
		<input type="submit" id="search" name="search" value="Search"/>
	</form>
	<div>
	<?php
	include 'connect.php';

	if(isset($_POST["search"]))
	{
		$university = $_POST["universitySearch"];
	
		$query = "SELECT decks.name, course.school, course.course, course.sem course.prof FROM decks, course, WHERE decks.course_id = course.course_id AND course.university = $1 GROUP BY course.university ORDER BY course.university ASC;";	//select query based off user input
		$stmt = pg_prepare($connection, "universitySearch", $query);
		$result = pg_execute($connection, "universitySearch", array($search."%"));


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
