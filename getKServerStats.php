<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

$tsql = "select productCode, utilization, sysutilization, kutilavg, sysutilavg, listenPort, freedisk
  FROM serverInfo, siteParams
  WHERE serverInfo.servername IN (SELECT servername FROM siteParams)";
$tsql2 = "sp_helpdb ksubscribers";
$tsql3 = "select paramValue as version from kserverParams where paramName = 'version'";
$tsql4 = "select tempValue FROM tempData WHERE tempName='vsaPatchLast'";

$tsql5 = "IF EXISTS (SELECT 1 FROM dbo.tempData WHERE tempName = 'KaseyaSystemPatch')
		 SELECT TOP 1 ISNULL(tempValue, 'None') AS PatchVersion FROM tempData WHERE tempName = 'KaseyaSystemPatch' ORDER BY creationDate DESC
			ELSE SELECT 'None' AS PatchVersion";

$tsql6 = "IF EXISTS (SELECT 1 FROM dbo.tempData WHERE tempName = 'KaseyaPatchLevel')
		 SELECT TOP 1 ISNULL(tempValue, 'None') AS PatchLevel FROM tempData WHERE tempName = 'KaseyaPatchLevel' ORDER BY creationDate DESC
			ELSE SELECT 'Unknown' AS PatchLevel";

$tsql7 = "select ref, Value from vdb_licenseValues where ref='Maxagents' or ref='Expiration'";

			
$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false ) {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$stmt2 = sqlsrv_query( $conn, $tsql2);
if( $stmt2 === false ) {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$stmt3 = sqlsrv_query( $conn, $tsql3);
if( $stmt3 === false ) {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$stmt4 = sqlsrv_query( $conn, $tsql4);
if( $stmt4 === false ) {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$stmt5 = sqlsrv_query( $conn, $tsql5);
if( $stmt5 === false ) {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$stmt6 = sqlsrv_query( $conn, $tsql6);
if( $stmt6 === false ) {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$stmt7 = sqlsrv_query( $conn, $tsql7);
if( $stmt7 === false ) {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

echo "<table id=\"uptimelist\" class=\"datatable\">";

$row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC);
$row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC);
$row3 = sqlsrv_fetch_array( $stmt3, SQLSRV_FETCH_ASSOC);


echo "<tr><th class=\"colL\">Site code</th><th class=\"colL\">CPU Use 5min</th><th class=\"colL\">Long Term CPU</th><th class=\"colM\">DB Size</th><th class=\"colL\">Free Disk Space</th><th class=\"colL\">Checkin Port</th><th class=\"colL\">KServer Version</th>";

if ($KVer>6.3) echo "<th class=\"colL\">Patch Level</th>"; else echo "<th class=\"colL\">Latest Hotfix</th>";
if ($KVer>6.5) echo "<th class=\"colL\">Latest Patch</th>";
echo "<th class=\"colL\">Max Agents</th>";
echo "<th class=\"colL\">License Expires</th>";
echo "</tr>";

/* 6.5 has a new way to get the hotfix version number */
if ($KVer>6.3) { 
  $row5 = sqlsrv_fetch_array( $stmt5, SQLSRV_FETCH_ASSOC);
  $kpatch = $row5['PatchVersion'];
  
  $row6 = sqlsrv_fetch_array( $stmt6, SQLSRV_FETCH_ASSOC);
  
} else { 
  $row4 = sqlsrv_fetch_array( $stmt4, SQLSRV_FETCH_ASSOC);
  $kpatch = $row4['tempValue'];
};



 echo "<tr><td>".$row['productCode']."</td>";
 echo "<td class=\"colM\">".round($row['utilization']/10000,2)."%</td>";
 echo "<td class=\"colM\">".round($row['kutilavg']/10000,2)."%</td>";
 echo "<td class=\"colM\">".$row2['db_size']."</td>";
 
 echo "<td class=\"colM\">";
 if ($row['freedisk'] < 1024*1024*15) {
     if ($row['freedisk'] < 1024*1024*10) {
        echo "<font color=red>";
     } else {
        echo "<font color=orange>";
     }
     echo formatBytes($row['freedisk'])."</font></td>";

  } else { echo formatBytes($row['freedisk'])."</td>"; }
 
 echo "<td class=\"colM\">".$row['listenPort']."</td>";
 echo "<td class=\"colM\">".$row3['version']."</td>";
 echo "<td class=\"colM\">".$kpatch."</td>";
if ($KVer>6.5) {
  $col='black';
  if (trim($row6['PatchLevel']) != trim($kpatch)) $col='red';
  echo "<td class=\"colM\"><font color=\"".$col."\">".$row6['PatchLevel']."</font></td>";
}

$lic=array();
while( $row7 = sqlsrv_fetch_array( $stmt7, SQLSRV_FETCH_ASSOC))
{
	$lic[$row7['ref']] = $row7['Value'];
}

echo "<td class=\"colM\">".$lic['MaxAgents']."</td>";
$exp = new datetime($lic['Expiration']);
$now = new datetime();

$diff = ($now->diff($exp)->format("%a"))+1;

 $col='black';
  if ($diff<=14) $col='red';
  echo "<td class=\"colM\"><font color=\"".$col."\">".$exp->format($datestyle)."</font></td>";


echo "</tr>";
echo "</table>";

sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>