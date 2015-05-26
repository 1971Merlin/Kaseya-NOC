<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

if (isset($_GET['name'])) {

  $agent=$_GET['name'];


$tsql = "select Title, PatchStatusDescription, UpdateClassificationDescription
  from vPatchStatus
  Where vPatchStatus.Machine_GroupID like '".$agent."' and (PatchStatus>1 or (ApprovalStatus = 0 and PatchAppliedFlag != 1) )
  order by PatchStatus";
 
$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

echo "<div class=\"heading\">";
echo "Patch Status Detail";
echo "</div>";


echo "<div class=\"datatable\">";
echo "<table id=\"patchstatus\">";
echo "<tr><th class=\"colL\">Patch Title</th><th class=\"colL\">Status</th><th class=\"colL\">Patch Type</th></tr>";


while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
 echo "<tr>";
 echo "<td class=\"colL\">".$row['Title']."</td>";
 echo "<td class=\"colL\">".$row['PatchStatusDescription']."</td>";
 echo "<td class=\"colL\">".$row['UpdateClassificationDescription']."</td>";
 echo "</tr>";
 }

echo "</table>";
echo "</div>";

sqlsrv_close( $conn );
} else echo "Parameter Missing!";
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>