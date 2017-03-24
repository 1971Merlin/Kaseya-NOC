<?php 

//* VSA 9.4+ KCB Acronis / Cloud backup *//

$pageContent = null;
ob_start();
include 'dblogin.php';



// failed backups
$tsql = "SELECT st.displayName as machName, aal.title as lastTitle, aal.cause as lastCause, aal.reason as lastReason, aal.effect as lastEffect, aal.startTime as lastStartTime, aal.finishTime as lastFinishTime, aal.status as lastStatus, st.online, st.currentLogin
 from vAgentLabel as st ";
// if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = st.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
// if ($org_filter!="Master") { $tsql.=" 
// join dbo.DenormalizedOrgToMach on st.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
//  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql.="
 inner join KCB.Asset AS a ON st.agentGuid = a.agentGuid
 LEFT OUTER JOIN KCB.AcronisActivity AS aas ON a.lastSuccessfulActivityId = aas.id
 LEFT OUTER JOIN KCB.AcronisActivity AS aal ON a.lastActivityId = aal.id
 WHERE (a.installStatusDomainItemRef = 'KCB_InstallStatus_Installed') and aas.id != aal.id";
 
 

$tsql2 = "SELECT value from KCB.Setting where name like 'LastTimeActivityHarvesterRan'"; /* value returned is varchar not DateTime */

$tsql2a = "select * from KCB.vAssetTypeSummaryDataSet";


// last successful backup
$tsql3 = "SELECT top ".$resultcount." st.displayName as machName, aas.title as lastTitle, aas.startTime as lastStartTime, aas.finishTime as lastFinishTime, CASE WHEN aas.status = 'warning' THEN 'Warning' WHEN aas.state = 'running' THEN 'Running' WHEN aas.state = 'completed' THEN 'Success' ELSE aas.state END as lastStatus,
aas.bytesSaved, aas.bytesProcessed, st.online, st.currentLogin
 from vAgentLabel as st ";
 if ($usescopefilter==true) { $tsql3.=" join vdb_Scopes_Machines foo on (foo.agentGuid = st.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
 if ($org_filter!="Master") { $tsql3.=" 
 join dbo.DenormalizedOrgToMach on st.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
 and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql3.="
 inner join KCB.Asset AS a ON st.agentGuid = a.agentGuid
 LEFT OUTER JOIN KCB.AcronisActivity AS aas ON a.lastSuccessfulActivityId = aas.id
 WHERE (a.installStatusDomainItemRef = 'KCB_InstallStatus_Installed')
 order by lastStartTime desc";
 


$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$stmt2 = sqlsrv_query( $conn, $tsql2);
if( $stmt2 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$stmt2a = sqlsrv_query( $conn, $tsql2a);
if( $stmt2a === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$stmt3 = sqlsrv_query( $conn, $tsql3);
if( $stmt3 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}




echo "<div class=\"heading heading2\">";
Echo "<image src=\"images/acro-cloud.png\" style=\"vertical-align:middle\"> Cloud Backup Status";
//* echo "<div class=\"topn\">showing first ".$resultcount."</div>"; *//
echo "</div>";


$row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC);

$valu = new DateTime($row2['value']);
$dispdate = (isset($row2['value']) ? date($datestyle." ".$timestyle,$valu->getTimestamp()) : 'Never');

$row2a = sqlsrv_fetch_array( $stmt2a, SQLSRV_FETCH_ASSOC);


echo "<div class=\"datatable\">";
echo "<table id=\"cloudinfostats\">";

echo "<tr><td>Data last retrieved</td><td>".$dispdate."</td></tr>";

echo "<tr><td>Total Servers</td><td class=\"colM\">".$row2a['TotalServers']."</td></tr>";
echo "<tr><td>Total Workstations</td><td class=\"colM\">".$row2a['TotalWorkstations']."</td></tr>";
echo "<tr><td>Total total VMs</td><td class=\"colM\">".$row2a['TotalVMs']."</td></tr>";
echo "</table>";
echo "</div>";



//* spacer *//
echo "<div class=\"spacer\"></div>";

echo "<div class=\"heading heading2\">";
echo "Endpoints with Errors";
echo "</div>";


echo "<div class=\"datatable\">";
echo "<table id=\"cloudbuerr\">";
echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colM\">Most Recent Backup Job</th><th class=\"colM\">Time Job was Run</th><th class=\"colM\">Most Recent Result</th><th class=\"colL\">Reported Cause of Error</th></tr>";


while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
  echo "<tr><td class=\"colL\">";
  showAgentIcon($row['online'],$row['currentLogin']);
  echo "&nbsp;".$row['machName']."</td>";

  echo "<td class=\"colL\">".$row['lastTitle']."</td>";
 
  $dispdate = (isset($row['lastStartTime']) ? date($datestyle." ".$timestyle,$row['lastStartTime']->getTimestamp()) : 'Never');
  echo "<td>".$dispdate."</font></td>";
 
  $color = "black";
  if ($row['lastStatus'] == 'warning') { $color = "orange"; }
  if ($row['lastStatus'] == 'cancelled') { $color = "orange"; }
  if ($row['lastStatus'] == 'error') { $color = "red"; }

  echo "<td class=\"colM\"><font color=\"".$color."\">".$row['lastStatus']."</font></td>";

  echo "<td class=\"colL\">".$row['lastCause']."</td>";
 
 echo "</tr>";
 }
echo "</table>";
echo "</div>";

//* spacer *//
echo "<div class=\"spacer\"></div>";



echo "<div class=\"heading heading2\">";
Echo "Last Successful Endpoint Backups";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";


echo "<div class=\"datatable\">";
echo "<table id=\"cloudbulist\">";
echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colM\">Last Successful Backup Job</th><th class=\"colM\">Start Time</th><th class=\"colM\">Finish Time</th><th class=\"colM\">Duration</th><th class=\"colM\">Result</th><th class=\"colM\">Backup Size</th></tr>";


while( $row3 = sqlsrv_fetch_array( $stmt3, SQLSRV_FETCH_ASSOC))
{
  echo "<tr><td class=\"colL\">";
  showAgentIcon($row3['online'],$row3['currentLogin']);
  echo "&nbsp;".$row3['machName']."</td>";

  echo "<td class=\"colL\">".$row3['lastTitle']."</td>";
 
  $dispdate = (isset($row3['lastStartTime']) ? date($datestyle." ".$timestyle,$row3['lastStartTime']->getTimestamp()) : 'Never');
  echo "<td>".$dispdate."</td>";
  
  $dispdate = (isset($row3['lastFinishTime']) ? date($datestyle." ".$timestyle,$row3['lastFinishTime']->getTimestamp()) : 'Never');
  echo "<td>".$dispdate."</td>";
  

  echo "<td class=\"colR\">".formatDateDiff($row3['lastFinishTime'],$row3['lastStartTime'])."</td>";
  
  
  $color = "black";
  if ($row3['lastStatus'] == 'Warning') { $color = "orange"; }
  if ($row3['lastStatus'] == 'Cancelled') { $color = "orange"; }
  if ($row3['lastStatus'] == 'Failed') { $color = "red"; }

  echo "<td class=\"colM\"><font color=\"".$color."\">".$row3['lastStatus']."</font></td>";


  echo "<td class=\"colR\">".formatBytes($row3['bytesSaved'])."</td>";

  
 echo "</tr>";
 }
echo "</table>";
echo "</div>";




sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>