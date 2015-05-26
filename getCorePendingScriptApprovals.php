<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

$left=true;
$right=true;

$id=$_GET["type"];
if ($id=='l') { $right=false; }
if ($id=='r') { $left=false; }


$tsql = "select ScriptName, ModifiedBy, DateModified, TreeFullPath
  from vw_AgentProceduresPendingApproval
  ";
 
$tsql2 = "select count(*) as num
  from vw_AgentProceduresPendingApproval
  ";


if ($KVer > 6.5 ) {
  

$stmt2 = sqlsrv_query( $conn, $tsql2);
if( $stmt2 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

  $row_count = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC);

  
  
if ($left==true) {

echo "<table class=\"datatable\"><tr><td>";

if ($row_count['num']==0) {
	 echo "<img src=\"images/check.png\">";
}
 else {
	 echo "<img src=\"images/cross.png\">";
 }
 echo "</td><td>{$row_count['num']} Script";
 if ($row_count['num'] != 1) { echo 's'; }
 echo " Pending Approval";
 echo "</td></tr></table>";

 }


if ($right==true) {
	
echo "<div class=\"heading\">";
echo "Scripts Pending Approval";
// echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";


if ($row_count['num']!=0) {
  $stmt = sqlsrv_query( $conn, $tsql);
  if( $stmt === false )
  {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }
  
  echo "<table id=\"pendscript\" class=\"datatable\">";
  echo "<tr><th class=\"colL\">Script Name</th><th class=\"colL\">Author</th><th class=\"colL\">Date Modified</th></tr>";

  while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
  {
     echo "<tr><td class=\"colL\">".$row['ScriptName']."</td>";
	 echo "<td class=\"colL\">".$row['ModifiedBy']."</td>";
	 echo "<td class=\"colL\">".$row['DateModified']->format($datestyle." ".$timestyle)."</td></tr>";
  }

  echo "</table>";
 }
}
}
sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>