<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

$left=true;
$right=true;

$id=$_GET["type"];
if ($id=='l') { $right=false; }
if ($id=='r') { $left=false; }


$tsql = "select distinct top ".$resultcount." COUNT(a.scriptId) as cnt, i.scriptName as Script, vl.displayName as Machine
    FROM scriptAssignment a
    LEFT OUTER JOIN scriptIdTab i ON a.scriptId = i.scriptId 
    LEFT OUTER JOIN agentState t ON a.agentGuid=t.agentGuid
    left outer join vAgentLabel vl on vl.agentGuid = a.agentGuid
    WHERE (a.execScriptTime < CURRENT_TIMESTAMP) AND (i.scriptName IS NOT NULL) AND (a.agentGuid != '123456789') AND t.online=1 
    GROUP BY vl.displayName, a.scriptId, i.scriptName ORDER BY cnt DESC";


$tsql2 = "SELECT COUNT(a.scriptId) as num
    FROM scriptAssignment a
    LEFT OUTER JOIN scriptIdTab i ON a.scriptId = i.scriptId 
    LEFT OUTER JOIN agentState t ON a.agentGuid=t.agentGuid
    WHERE (a.execScriptTime < CURRENT_TIMESTAMP) AND (i.scriptName IS NOT NULL) AND (a.agentGuid != '123456789') AND t.online=1 
";

$stmt2 = sqlsrv_query( $conn, $tsql2);
if( $stmt2 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}
  $row_count = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC);

if ($left==true) {

echo "<table class=\"datatable\"><tr><td>";

if ($row_count['num']<50) {
	 echo "<img src=\"images/check.png\">";
}
 else {
	 echo "<img src=\"images/question.png\">";
 }
 echo "</td><td>{$row_count['num']} Running Script";
 if ($row_count['num'] != 1) { echo 's'; }
 echo "</td></tr></table>";

 }



if ($right==true) {

echo "<div class=\"heading\">";
echo "Currently Running Scripts";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";

if ($row_count['num']!=0) {

  $stmt = sqlsrv_query( $conn, $tsql);
  if( $stmt === false )
  {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }

  echo  "<table id=\"runscriptlist\" class=\"datatable\">";
  echo  "<tr><th class=\"colL\">Machine Name</th><th class=\"colL\">Script</th></tr>";

  while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
  {
     echo  "<tr><td class=\"colL\">".$row['Machine']."</td><td class=\"colL\">".$row['Script']."</td></tr>";
  }
  echo  "</table>";
}

}
sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>