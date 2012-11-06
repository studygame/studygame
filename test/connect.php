<?php

/*Name: Clayton Sussman

This is my connection file. May be used in other labs*/

$server = "dbhost-pgsql.cs.missouri.edu";
$username = "cs3380f12grp8";
$password = "KUqRzeX7";
$dbName = "cs3380f12grp8";

$connection = pg_connect("host=$server user=$username password=$password dbname=$dbName") or die("Could not complete connection!");


?>
