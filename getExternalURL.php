<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

echo "<div class=\"heading\">External URL<div style=\"float:right\"><a href=\"".$exturl."\" target=\"blank\"><img src=\"images/link.png\" style=\"vertical-align:middle\"></a></div></div>";		


echo "<div id=\"extDIV\">";
$dtime=time();

// gif only ? original method
// echo "<a href=\"{$exturl}\" target=\"_blank\"><img style=\"max-width: 100%; height: auto;\" src=\"{$exturl}?ver={$dtime}\" ></a>";

// embed method
echo "<object data={$exturl}?ver={$dtime} width=\"100%\" height=\"auto\"> <embed src={$exturl}?ver={$dtime}> </embed> Error: Embedded data could not be displayed. </object>";

// iframe method
// echo "<iframe src=\"{$exturl}?ver={$dtime}\" width=\"100%\" height=\"100%\" frameborder=\"0\" scrolling=\"auto\"></iframe>";

echo "</div>";

$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent; 
?>