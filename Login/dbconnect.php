<?php

	session_start();

	// ensure there is a valid database connection available
	if (empty($_SESSION["dbconn"]) || pg_connection_status($_SESSION["dbconn"]) == PGSQL_CONNECTION_BAD) {

		$DBServer = "dbhost-pgsql.cs.missouri.edu";
		$DBUsername = "cs3380f12grp8";
		$DBPassword = "KUqRzeX7";
		$DBName = "cs3380f12grp8";
		
		$_SESSION["dbconn"] = pg_pconnect("host=$DBServer user=$DBUsername password=$DBPassword dbname=$DBName");
		
	}

	$dbconn = $_SESSION["dbconn"];

?>