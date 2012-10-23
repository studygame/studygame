<?php
session_start();
$_SESSION = array();
session_destroy();

header("Location: http://babbage.cs.missouri.edu/~cs3380f12grp8/Login/index.php");

?>