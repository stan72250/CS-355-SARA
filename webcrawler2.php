<?php
function crawl_page($url)
{
    $dbhost = "stanlin58.coaob0ignv6b.us-east-2.rds.amazonaws.com";
    $dbuser = "list9158";
    $dbpass = "aws23079158";
    $db = "SARA";
    $conn = new mysqli($dbhost, $dbuser, $dbpass,$db) or die("Connect failed: %s\n". $conn -> error);
 
    $starttime = microtime(true);
	
    $dom = new DOMDocument('1.0');
    @$dom->loadHTMLFile($url);
    $anchors = $dom->getElementsByTagName('a');
    $nodes = $dom->getElementsByTagName('title');

    $title = strval($nodes->item(0)->nodeValue);
    $metas = $dom->getElementsByTagName('meta');
    $metaAr = [];
    for ($i = 0; $i < $metas->length; $i++)
    {
        $meta = $metas->item($i);

        $metaAr = $meta;
        if($meta->getAttribute('name') == 'description')
            $description = strval($meta->getAttribute('content'));
            
        if($meta->getAttribute('name') == 'keywords')
            $keywords = $meta->getAttribute('content');
    }
    $ps = $dom->getElementsByTagName('p');
    $parray = [];
    
 
    $result = $conn->query("SELECT pageId FROM page WHERE url = '$url'");
    if($result->num_rows == 0) {
       
        $insertPage = "INSERT INTO page (url, title, description, timeToIndex)
            VALUES ('$url', '$title', '$description', 0)";
        mysqli_query($conn, $insertPage);

        $pageQuery = $conn->query("SELECT pageId FROM page WHERE url = '$url'");
        $pageRow = $pageQuery->fetch_assoc();
        $pageId = $pageRow["pageId"];
        foreach($ps as $element){
            $text = $element->textContent;
            if(trim($text) != ""){
                $words = explode(" ", $text);
                foreach($words as $word){
                    if(trim($word) != ""){
                        $word = '"' . $word . '"';
                        $wordResult = $conn->query("SELECT wordId FROM word WHERE wordName = $word");
                        if($wordResult->num_rows == 0) {
                            $insertWord = "INSERT INTO word (wordName)
                                VALUES ($word)";
                            mysqli_query($conn, $insertWord);
                        }

                        $wordQuery = $conn->query("SELECT wordId FROM word WHERE wordName = $word");
                        if($wordQuery->num_rows == 0) {
 
                        }
                        else {
                            $wordRow = $wordQuery->fetch_assoc();
                            $wordId = $wordRow["wordId"];
 
                            $pageWordResult = $conn->query(
                                    "SELECT pageId, wordId FROM page_word 
                                    WHERE pageId = '$pageId' AND wordId = '$wordId'");
             
                            if($pageWordResult->num_rows == 0){
                                $insertPageWord = "INSERT IGNORE INTO page_word (pageId, wordId, freq)
                                    VALUES ('$pageId','$wordId', 1)";
                            }
							
                            else {
                                $insertPageWord = "UPDATE page_word
                                    SET freq = freq + 1
                                    WHERE pageId = '$pageId' AND wordId = '$wordId'";
                            }
                            mysqli_query($conn, $insertPageWord);
                        }  
                    }
                }
            }
        }

        $endtime = microtime(true);
        $loadtime = $endtime - $starttime;
        $updatePage = "UPDATE page
            SET timeToIndex = $loadtime
            WHERE url = '$url'";
        mysqli_query($conn, $updatePage);
    }
    else {
        echo "row already exists";
    }
    $conn -> close();
}
crawl_page($_POST['url']);
echo " Indexed ",$_POST['url'];