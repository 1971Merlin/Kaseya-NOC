<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';


$cache_time = 3600; // 3600 seconds = 1 hour
  
$rss = new DOMDocument();

// cache / load

$cache_file = $_SERVER['DOCUMENT_ROOT'].'/rsscache.rss';
$timedif = @(time() - filemtime($cache_file));


if (file_exists($cache_file) && $timedif < $cache_time && !isset($_GET['reload'])) {
    	$rss->load($cache_file);
} else {
    $rss->load($rssurl);
	
	$string = $rss->saveXML();
	
    if ($f = @fopen($cache_file, 'w')) {
        fwrite ($f, $string, strlen($string));
        fclose($f);		
    }
}

$lastrefresh = filemtime($cache_file);
	

// display

	foreach ($rss->getElementsByTagName('channel') as $node) {
		$title = isset($node->getElementsByTagName('title')->item(0)->nodeValue) ? $node->getElementsByTagName('title')->item(0)->nodeValue : "";
	}
	
	
	

	$feed = array();
	foreach ($rss->getElementsByTagName('item') as $node) {
		$item=array();
		
		if (isset($node->getElementsByTagName('title')->item(0)->nodeValue)) { $item['title'] = $node->getElementsByTagName('title')->item(0)->nodeValue; }
		if (isset($node->getElementsByTagName('description')->item(0)->nodeValue)) { $item['desc'] = $node->getElementsByTagName('description')->item(0)->nodeValue; }
		if (isset($node->getElementsByTagName('link')->item(0)->nodeValue)) { $item['link'] = $node->getElementsByTagName('link')->item(0)->nodeValue; }
		if (isset($node->getElementsByTagName('pubDate')->item(0)->nodeValue)) { $item['date'] = $node->getElementsByTagName('pubDate')->item(0)->nodeValue; }
		
		array_push($feed, $item);
	}
	
	$limit = count($feed);
	
	if ($limit > ($resultcount-1)) { $limit = $resultcount-1; }


echo "<div class=\"heading\">RSS Feed : ".$title;
echo "<div class=\"topn\">showing first ".$limit."</div>";
echo "<div style=\"float:right; margin-right:7px;\"><a href=\"getRSSFeed.php?reload\" title=\"Last refreshed ".date($datestyle." ".$timestyle,$lastrefresh)."\"><img src=\"images/refresh.png\"></a></div>";
echo "</div>";


	for($x=0;$x<$limit;$x++) {
		$title = isset($feed[$x]['title']) ? str_replace(' & ', ' &amp; ', $feed[$x]['title']) : "";
		$link = isset($feed[$x]['link']) ? $feed[$x]['link'] : "";
		$description = isset($feed[$x]['desc']) ? $feed[$x]['desc'] : "";
		$date = isset($feed[$x]['date']) ? date('F d', strtotime($feed[$x]['date'])) : "";
		echo '<div class="rss">';
			echo '<div class="rssL"><a href="'.$link.'" title="'.$title.'" target="_blank">'.$title.'</a></div>';
			echo '<div class="rssR">'.$date.'</div>';
			echo '<div class="rssDetail">';
				echo substr($description,0,69);		
				if (strlen($description)>=69) { echo '...'; }
			echo '</div>';
		echo '</div>';
	}
	
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent; 
?>