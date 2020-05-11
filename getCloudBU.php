<?php 

//* VSA 9.4+ KCB Acronis / Cloud backup *//

$pageContent = null;
ob_start();
include 'dblogin.php';



// failed backups
// logic is if last backup ID and lastSuccessful backup ID != then last backup must have failed.
//
// replaced!! Think the logic is very poor here.
/*
$tsql = "SELECT st.displayName as machName, aal.title as lastTitle, aal.cause as lastCause, aal.reason as lastReason, aal.effect as lastEffect, aal.startTime as lastStartTime, aal.finishTime as lastFinishTime, aal.status as lastStatus,
 isnull(a.sqlComponent,0) as sql, isnull(a.exchangeComponent,0) as exchange, isnull(a.activedirectoryComponent,0) as AD, isnull(a.vmwareComponent,0) as VMWare, isnull(a.hypervComponent,0) as HyperV, st.online, st.currentLogin
 from vAgentLabel as st ";
 if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = st.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
 if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on st.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
 and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql.="
 inner join KCB.Asset AS a ON st.agentGuid = a.agentGuid
 LEFT OUTER JOIN KCB.AcronisActivity AS aas ON a.lastSuccessfulActivityId = aas.id
 LEFT OUTER JOIN KCB.AcronisActivity AS aal ON a.lastActivityId = aal.id
 WHERE (a.installStatusDomainItemRef = 'KCB_InstallStatus_Installed') and aas.id != aal.id and aal.finishTime is NOT NULL";
*/


$tsql = "SELECT st.displayName as machName, aal.title as lastTitle, aal.cause as lastCause, aal.reason as lastReason, aal.effect as lastEffect, aal.startTime as lastStartTime, aal.finishTime as lastFinishTime, aal.status as lastStatus,
 isnull(a.sqlComponent,0) as sql, isnull(a.exchangeComponent,0) as exchange, isnull(a.activedirectoryComponent,0) as AD, isnull(a.vmwareComponent,0) as VMWare, isnull(a.hypervComponent,0) as HyperV, st.online, st.currentLogin
 from vAgentLabel as st ";
 if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = st.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
 if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on st.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
 and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql.="
 inner join KCB.Asset AS a ON st.agentGuid = a.agentGuid
 LEFT OUTER JOIN KCB.AcronisActivity AS aal ON a.lastActivityId = aal.id
 WHERE aal.status != 'ok' and aal.finishTime is NOT NULL";



$tsql2 = "SELECT MAX( case when name like 'LastTimeActivityHarvesterRan' then value end) as value,
 MAX( case when name like 'AcronisWindowsInstallerVersion' then value end) as winver,
 MAX( case when name like 'AcronisMacOSXInstallerVersion' then value end) as macver
 from KCB.Setting"; /* value returned is varchar not DateTime */

 
$tsql2a = "select kcb.fnGetCloudBackupExpiryDate(1) as expiry, kcb.fnGetCloudBackupVMLicenseCount(1) as vmcount, kcb.fnGetCloudBackupServerLicenseCount(1) as servercount, KCB.fnGetCloudBackupWSLicenseCount(1) as wscount, * from KCB.vAssetTypeSummaryDataSet";

// last successful backup
// get last successful ID and report on it
$tsql3 = "SELECT top ".$resultcount." st.displayName as machName, aas.title as lastTitle, aas.startTime as lastStartTime, aas.finishTime as lastFinishTime, CASE WHEN aas.status = 'warning' THEN 'Warning' WHEN aas.state = 'running' THEN 'Running' WHEN aas.state = 'completed' THEN 'Success' ELSE aas.state END as lastStatus,
aas.bytesSaved, aas.bytesProcessed, isnull(a.sqlComponent,0) as sql, isnull(a.exchangeComponent,0) as exchange, isnull(a.activedirectoryComponent,0) as AD, isnull(a.vmwareComponent,0) as VMWare, isnull(a.hypervComponent,0) as HyperV, st.online, st.currentLogin
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

 

 
// in progress backup
// get lask activity where state is not null (filters out agents with no jobs) and finishtime not set (no finish time = must be running).
$tsql4 = "SELECT top ".$resultcount." st.displayName as machName, aas.title as lastTitle, aas.startTime as lastStartTime, CASE WHEN aas.status = 'warning' THEN 'Warning' WHEN aas.state = 'running' THEN 'Running' WHEN aas.state = 'completed' THEN 'Success' ELSE aas.state END as lastStatus,
aas.bytesSaved, aas.bytesProcessed, isnull(a.sqlComponent,0) as sql, isnull(a.exchangeComponent,0) as exchange, isnull(a.activedirectoryComponent,0) as AD, isnull(a.vmwareComponent,0) as VMWare, isnull(a.hypervComponent,0) as HyperV, st.online, st.currentLogin
 from vAgentLabel as st ";
 if ($usescopefilter==true) { $tsql4.=" join vdb_Scopes_Machines foo on (foo.agentGuid = st.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
 if ($org_filter!="Master") { $tsql4.=" 
 join dbo.DenormalizedOrgToMach on st.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
 and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql4.="
 inner join KCB.Asset AS a ON st.agentGuid = a.agentGuid
 LEFT OUTER JOIN KCB.AcronisActivity AS aas ON a.lastActivityId = aas.id
 WHERE (a.installStatusDomainItemRef = 'KCB_InstallStatus_Installed') and aas.state is NOT NULL and aas.finishtime IS NULL
 order by lastStartTime desc";
  

// storagestats

$tsql5 = "SELECT bg.groupName, CASE
	WHEN isnull(CloudStorageType, 0) = 0 THEN 'Combined'
	WHEN isnull(CloudStorageType, 0) = 1 THEN 'Cloud'
	WHEN isnull(CloudStorageType, 0) = 2 THEN 'Gateway' END
AS CloudStorageType, CEILING(CAST(ash.spaceUsed AS decimal) / 1073741824) AS Usage, dateCreated
FROM KCB.AcronisStorageHistory AS ash
INNER JOIN KCB.BackupGroup AS bg ON bg.id = ash.backupGroupId

where ash.dateCreated = (select max(ash2.datecreated) from KCB.AcronisStorageHistory ash2 where ash2.backupGroupId = bg.id)

order by Usage desc";


  
  
  

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
if( $stmt3 === false ){
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}



$stmt4 = sqlsrv_query( $conn, $tsql4);
if( $stmt4 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}


$stmt5 = sqlsrv_query( $conn, $tsql5);
if( $stmt5 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}


echo "<div class=\"heading\">";
Echo "<image src=\"images/acro-cloud.png\" style=\"vertical-align:middle\"> Cloud Backup Status";
echo "</div>";


$row2a = sqlsrv_fetch_array( $stmt2a, SQLSRV_FETCH_ASSOC);

echo "<div class=\"datatable\">";
echo "<table id=\"cloudinfolicstats\">";
echo "<caption class=\"heading3\">Licence Information</caption>";
echo "<tr><th>&nbsp;</th><th>Server</th><th>Workstation</th><th>VM</th></tr>";
echo "<tr><td>Purchased</td><td class=\"colM\">".$row2a['servercount']."</td><td class=\"colM\">".$row2a['wscount']."</td><td class=\"colM\">".$row2a['vmcount']."</td></tr>";
echo "<tr><td>Installed</td><td class=\"colM\">".$row2a['TotalServers']."</td><td class=\"colM\">".$row2a['TotalWorkstations']."</td><td class=\"colM\">".$row2a['TotalVMs']."</td></tr>";
$dispdate = (isset($row2a['expiry']) ? date($datestyle." ".$timestyle,$row2a['expiry']->getTimestamp()) : 'Not Set');
echo "<tr><td> Licence Expires</td><td colspan=\"3\" class=\"colM\">".$dispdate."</td></tr>";
echo "</table>";
echo "</div>";



$row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC);
$valu = new DateTime($row2['value']);
$dispdate = (isset($row2['value']) ? date($datestyle." ".$timestyle,$valu->getTimestamp()) : 'Never');


echo "<div class=\"datatable2\">";
echo "<table id=\"cloudinfostats\">";
echo "<caption class=\"heading3\">Statistics</caption>";
echo "<tr><td>Data last retrieved</td><td>".$dispdate."</td></tr>";
echo "<tr><td>Windows Client version</td><td>".$row2['winver']."</td></tr>";
echo "<tr><td>Mac Client version</td><td>".$row2['macver']."</td></tr>";
echo "</table>";
echo "</div>";


echo "<div class=\"datatable2\">";
echo "<table id=\"cloudspaceused\">";
echo "<caption class=\"heading3\">Storage Stats</caption>";
echo "<tr><th>Group</th><th>Storage Used</th><th>Storage Type</th></tr>";


while( $row5 = sqlsrv_fetch_array( $stmt5, SQLSRV_FETCH_ASSOC))
{

  
  echo "<tr><td>".$row5['groupName']."</td><td  class=\"colM\">".$row5['Usage']." Gb</td><td class=\"colM\">".$row5['CloudStorageType']."</td>";
  
  
  echo "<td class=\"colM\">{$row5['dateCreated']->format($datestyle." ".$timestyle)}</td></tr>";
  

  
  
  
  
//  ".date($datestyle." ".$timestyle,$row5['dateCreated']->getTimestamp())."
  
}

echo "</table>";
echo "</div>";

//* spacer *//
echo "<div class=\"spacer\"></div>";



echo "<div class=\"heading heading2\">";
echo "Endpoints with Errors";
echo "</div>";


echo "<div class=\"datatable\">";
echo "<table id=\"cloudbuerr\">";
echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colM\">Flags</th><th class=\"colM\">Most Recent Backup Job</th><th class=\"colM\">Time Job was Run</th><th class=\"colM\">Result</th><th class=\"colL\">Reported Cause of Error</th></tr>";


while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
  echo "<tr><td class=\"colL\">";
  showAgentIcon($row['online'],$row['currentLogin']);
  echo "&nbsp;".$row['machName']."</td>";
  
  // flags  
  echo "<td>";
  if ($row['sql'] == 1) { echo "<image src=\"images/sql.png\" title=\"SQL Agent Installed\" style=\"vertical-align:middle\">"; } else { echo "<image src=\"images/spacer.png\" style=\"vertical-align:middle\">"; }
  if ($row['exchange'] == 1) { echo "<image src=\"images/exchange.png\" title=\"MS Exchange Agent Installed\" style=\"vertical-align:middle\">"; } else { echo "<image src=\"images/spacer.png\" style=\"vertical-align:middle\">"; }
  if ($row['AD'] == 1) { echo "<image src=\"images/ActiveDirectory.png\"  title=\"Active Directory Agent Installed\" style=\"vertical-align:middle\">"; } else { echo "<image src=\"images/spacer.png\" style=\"vertical-align:middle\">"; }
  if ($row['VMWare'] == 1) { echo "<image src=\"images/VMWare.png\"  title=\"VMWare Agent Installed\" style=\"vertical-align:middle\">"; } else { echo "<image src=\"images/spacer.png\" style=\"vertical-align:middle\">"; }
  if ($row['HyperV'] == 1) { echo "<image src=\"images/HyperV.png\"  title=\"Hyper-V Agent Installed\" style=\"vertical-align:middle\">"; } else { echo "<image src=\"images/spacer.png\" style=\"vertical-align:middle\">"; }
  echo "</td>";


  echo "<td class=\"colL\">".$row['lastTitle']."</td>";
 
  $dispdate = (isset($row['lastStartTime']) ? date($datestyle." ".$timestyle,$row['lastStartTime']->getTimestamp()) : 'Never');
  echo "<td>".$dispdate."</font></td>";
 
  $color = "black";
  if ($row['lastStatus'] == 'warning') { $color = "orange"; }
  if ($row['lastStatus'] == 'cancelled') { $color = "orange"; }
  if ($row['lastStatus'] == 'error') { $color = "red"; }

  echo "<td class=\"colM\"><font color=\"".$color."\">".$row['lastStatus']."</font></td>";

  echo "<td class=\"colL\">".substr($row['lastCause'],0,20);
  if (strlen($row['lastCause'])>=20) { echo '...'; }
  echo "</td>";
 
 
 echo "</tr>";
 }
echo "</table>";
echo "</div>";



//* spacer *//
//* in progress *//
echo "<div class=\"spacer\"></div>";




echo "<div class=\"heading heading2\">";
Echo "Endpoint Backups Currently Running";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";


echo "<div class=\"datatable\">";
echo "<table id=\"cloudrunlist\">";
echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colM\">Flags</th><th class=\"colM\">Current Backup Job</th><th class=\"colM\">Start Time</th><th class=\"colM\">Status</th><th class=\"colM\">Processed</th></tr>";


while( $row4 = sqlsrv_fetch_array( $stmt4, SQLSRV_FETCH_ASSOC))
{
  echo "<tr><td class=\"colL\">";
  showAgentIcon($row4['online'],$row4['currentLogin']);
  echo "&nbsp;".$row4['machName']."</td>";

  // flags  
  echo "<td>";
  if ($row4['sql'] == 1) { echo "<image src=\"images/sql.png\" title=\"SQL Agent Installed\" style=\"vertical-align:middle\">"; } else { echo "<image src=\"images/spacer.png\" style=\"vertical-align:middle\">"; }
  if ($row4['exchange'] == 1) { echo "<image src=\"images/exchange.png\" title=\"MS Exchange Agent Installed\" style=\"vertical-align:middle\">"; } else { echo "<image src=\"images/spacer.png\" style=\"vertical-align:middle\">"; }
  if ($row4['AD'] == 1) { echo "<image src=\"images/ActiveDirectory.png\"  title=\"Active Directory Agent Installed\" style=\"vertical-align:middle\">"; } else { echo "<image src=\"images/spacer.png\" style=\"vertical-align:middle\">"; }
  if ($row4['VMWare'] == 1) { echo "<image src=\"images/VMWare.png\"  title=\"VMWare Agent Installed\" style=\"vertical-align:middle\">"; } else { echo "<image src=\"images/spacer.png\" style=\"vertical-align:middle\">"; }
  if ($row4['HyperV'] == 1) { echo "<image src=\"images/HyperV.png\"  title=\"Hyper-V Agent Installed\" style=\"vertical-align:middle\">"; } else { echo "<image src=\"images/spacer.png\" style=\"vertical-align:middle\">"; }
  echo "</td>";

  echo "<td class=\"colL\">".$row4['lastTitle']."</td>";
 
  $dispdate = (isset($row4['lastStartTime']) ? date($datestyle." ".$timestyle,$row4['lastStartTime']->getTimestamp()) : 'Never');
  echo "<td>".$dispdate."</td>";
  
  $color = "black";
  if ($row4['lastStatus'] == 'Warning') { $color = "orange"; }
  if ($row4['lastStatus'] == 'Cancelled') { $color = "orange"; }
  if ($row4['lastStatus'] == 'Failed') { $color = "red"; }

  echo "<td class=\"colM\"><font color=\"".$color."\">".$row4['lastStatus']."</font></td>";
  
  echo "<td class=\"colR\">".formatBytes($row4['bytesProcessed'])."</td>";
  
 echo "</tr>";
 }
echo "</table>";
echo "</div>";








//* spacer *//

echo "<div class=\"spacer\"></div>";


//* success *//




echo "<div class=\"heading heading2\">";
Echo "Last Successful Endpoint Backups";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";


echo "<div class=\"datatable\">";
echo "<table id=\"cloudbulist\">";
echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colM\">Flags</th><th class=\"colM\">Last Successful Backup Job</th><th class=\"colM\">Start Time</th><th class=\"colM\">Finish Time</th><th class=\"colM\">Duration</th><th class=\"colM\">Result</th><th class=\"colM\">Processed</th></tr>";


while( $row3 = sqlsrv_fetch_array( $stmt3, SQLSRV_FETCH_ASSOC))
{
  echo "<tr><td class=\"colL\">";
  showAgentIcon($row3['online'],$row3['currentLogin']);
  echo "&nbsp;".$row3['machName']."</td>";

  // flags  
  echo "<td>";
  if ($row3['sql'] == 1) { echo "<image src=\"images/sql.png\" title=\"SQL Agent Installed\" style=\"vertical-align:middle\">"; } else { echo "<image src=\"images/spacer.png\" style=\"vertical-align:middle\">"; }
  if ($row3['exchange'] == 1) { echo "<image src=\"images/exchange.png\" title=\"MS Exchange Agent Installed\" style=\"vertical-align:middle\">"; } else { echo "<image src=\"images/spacer.png\" style=\"vertical-align:middle\">"; }
  if ($row3['AD'] == 1) { echo "<image src=\"images/ActiveDirectory.png\"  title=\"Active Directory Agent Installed\" style=\"vertical-align:middle\">"; } else { echo "<image src=\"images/spacer.png\" style=\"vertical-align:middle\">"; }
  if ($row3['VMWare'] == 1) { echo "<image src=\"images/VMWare.png\"  title=\"VMWare Agent Installed\" style=\"vertical-align:middle\">"; } else { echo "<image src=\"images/spacer.png\" style=\"vertical-align:middle\">"; }
  if ($row3['HyperV'] == 1) { echo "<image src=\"images/HyperV.png\"  title=\"Hyper-V Agent Installed\" style=\"vertical-align:middle\">"; } else { echo "<image src=\"images/spacer.png\" style=\"vertical-align:middle\">"; }
  echo "</td>";
  
  
  $title = preg_replace("/\([^)]+\)/","",$row3['lastTitle']);
  
  
  echo "<td class=\"colL\">".$title."</td>";
 
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

  echo "<td class=\"colR\">".formatBytes($row3['bytesProcessed'])."</td>";
  
 echo "</tr>";
 }
echo "</table>";
echo "</div>";




sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>