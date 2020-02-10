<?php
function getResults($terms){
	$dbhost = "stanlin58.coaob0ignv6b.us-east-2.rds.amazonaws.com";
    $dbuser = "list9158";
    $dbpass = "aws23079158";
    $db = "SARA";
	$conn = new mysqli($dbhost, $dbuser, $dbpass, $db) or die ("Connection failed: %s\n". $conn->error);
	
	$starttime = microtime(true);
	
	$checkVal = $_POST["value"];
	$searchVal = explode(" ",$terms);
	$results = array();
	foreach($searchVal as $term){
		if(trim($term) != ""){
			if($checkVal == 0){
				$term = '"' . $term . '"';
				$searchQuery = $conn->query("SELECT page.url, page.title, page.description, page_word.freq
				FROM  page, word, page_word
				WHERE page.pageId = page_word.pageId
				AND word.wordId = page_word.wordId
				AND word.wordName = $term
				ORDER BY freq DESC");
			}
			else if($checkVal == 1){
				$term = '"' . $term . '"';
				$searchQuery = $conn->query("SELECT page.url, page.title, page.description, page_word.freq
				FROM page, word, page_word
				WHERE page.pageId = page_word.pageId
				AND word.wordId = page_word.wordId
				AND UPPER(word.wordName) = UPPER($term)
				ORDER BY freq DESC");
			}
			elseif($checkVal == 2){
				$term = '"%' . $term . '%"';
				$searchQuery = $conn->query("SELECT page.url, page.title, page.description, page_word.freq
				FROM page, word, page_word
				WHERE page.pageId = page_word.pageId
				AND word.wordId = page_word.wordId
				AND word.wordName LIKE $term
				GROUP BY url
				ORDER BY freq DESC");
			}
			elseif($checkVal == 3){
				$term = '"%' . $term . '%"';
				$searchQuery = $conn->query("SELECT page.url, page.title, page.description, page_word.freq
				FROM page, word, page_word
				WHERE page.pageId = page_word.pageId
				AND word.wordId = page_word.wordId
				AND Upper(word.wordName) LIKE Upper($term)
				GROUP BY url
				ORDER BY freq DESC");
			}
		}
		
		if($searchQuery->num_rows > 0){
			while($r = mysqli_fetch_array($searchQuery, MYSQLI_ASSOC)){
				$results[] = $r;
			}
		}
	}
	
	$count = count($results);
    $endtime = microtime(true);
    $time = $endtime - $starttime;
    $terms = '"' . $terms . '"';
    $insertSearch = "INSERT INTO search (terms, count, timeToSearch)
        VALUES($terms, $count, $time)";
    mysqli_query($conn, $insertSearch);
    print json_encode($results);
    $conn -> close();
}
$terms = $_POST["search"];
getResults($terms);
	