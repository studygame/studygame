<?php

	session_start();

	if (empty($_SESSION["dbconn"]) || pg_connection_status($_SESSION["dbconn"]) == PGSQL_CONNECTION_BAD) {

		$server = "dbhost-pgsql.cs.missouri.edu";
		$username = "cs3380f12grp8";
		$password = "KUqRzeX7";
		$dbname = "cs3380f12grp8";
		
		$_SESSION["dbconn"] = pg_pconnect("host=$server user=$username password=$password dbname=$dbname");
	}

	$dbconn = $_SESSION["dbconn"];

?>