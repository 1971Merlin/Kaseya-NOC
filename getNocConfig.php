<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';
  
  echo "<table id=\"NOCcfginfo\" class=\"datatable\">";
  echo "<tr><th class=\"colM\">Selected ORG</th><th class=\"colM\">Selected Scope</th><th class=\"colM\">Items Limit</th></tr>";
  echo "<tr><td class=\"colM\">".$org_filter."</td>";
  echo "<td class=\"colM\">".$scope_filter."</td>";
  echo "<td class=\"colM\">".$resultcount."</td>";
  echo "</tr>";
  echo "</table>";  

$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>