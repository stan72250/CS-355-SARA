<?php

function getResults($terms){
	$dbhost = "stanlin58.coaob0ignv6b.us-east-2.rds.amazonaws.com";
    $dbuser = "list9158";
    $dbpass = "aws23079158";
    $db = "SARA";
	$conn = new mysqli($dbhost, $dbuser, $dbpass, $db) or die ("Connection failed: %s\n". $conn->error);
	
	$results = array();
	
	$searchQuery = $conn->query("SELECT * FROM page ORDER BY lastModified DESC");
	if($searchQuery->num_rows > 0){
		while($r = mysqli_fetch_array($searchQuery, MYSQLI_ASSOC)){
			$results[] = $r;
		}
	}
	
	print json_encode($results);
	$conn->close();
}

getResults();