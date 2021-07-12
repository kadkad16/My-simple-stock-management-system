<?php
	//database
	$hostname = "localhost";
	$username = "root";
	$password = "";
	$database = "inventory";
	$connection = mysqli_connect($hostname, $username, $password, $database);

	if(!$connection)
	{
		die("Connection Failed!");
	}


?>