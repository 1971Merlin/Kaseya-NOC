<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

/* $tsql = "SELECT adminName, adminIp
 FROM administrators
 where sessionExpiration >= GETDATE() and sessionid != -1  and disableUntil <= GETDATE()
 order by adminName";
*/
 
$tsql = "SELECT userName,fullName,signedOn, adminType, datediff(mi, getdate(), sessionExpiration)  as expires
  FROM vSystemUsers
  join administrators on administrators.adminName = vSystemUsers.userName
  where signedOn = 'Yes'";
  

$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

echo "<div class=\"heading\">";
  echo "Active VSA Users";
echo "</div>";
echo "<div class=\"datatable\">";
  echo "<table id=\"userslist\">";
    echo "<tr><th class=\"colL\">Name</th><th class=\"colL\">Session Expires</th></tr>";
	// <th class=\"colL\">IP Address</th></tr>";     -- IP no longer updated
    while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
    {
      echo "<tr><td class=\"colL\">";
	  
	  if ($row['adminType'] == 2) { echo "<b>"; }
	  echo $row['fullName'];
	  if ($row['adminType'] == 2) { echo "</b>"; }
	  echo "</td>";
      echo "<td class=\"colL\">".$row['expires']." mins</td></tr>";
      echo "</tr>";
   }
  echo "</table>";
echo "</div>";

sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>