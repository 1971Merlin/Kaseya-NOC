<?php 

//* VSA 9.3+ KAV / Kaspersky Security Status *//

$pageContent = null;
ob_start();
include 'dblogin.php';


$tsql = "select distinct top ".$resultcount." ie.machineId, ie.ClientVersion, ie.HasActiveThreats, ie.ProfileName, ie.rebootRequired, ie.LastUpdated, ie.LastFullScan, st.online as online, st.currentLogin, installStatusDomainItemRef, mi.isInstalled as minorVersion,

case 
 when ie.isInstalled <> 1 then '0'
 when ie.LastUpdated is null then '1'
 when ie.lastupdated < DATEADD(day,-7,getdate()) then '1'
else '0'
 end as outdated
 from sec.vMachineInfoExtended ie";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = sec.vMachineInfoExtended.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on ie.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql.="
  join vAgentLabel st on st.agentGuid = ie.agentGuid
  join sec.vMachineInfo mi on mi.agentGuid = ie.agentGuid
 where online>0 and ((HasActiveThreats > 0 or (ie.LastUpdated is null or ie.lastupdated < DATEADD(day,-7,getdate())) or (ie.LastFullScan is null or ie.lastfullscan < DATEADD(day,-30,getdate()))) or (mi.installStatusDomainItemRef <> 'SEC_InstallStatus_Installed'))
 order by ie.HasActiveThreats desc, ie.rebootRequired desc, ie.ClientVersion, outdated desc, ie.LastUpdated, ie.LastFullScan
 
 ";

 
$tsql2 = "select * from SEC.vAVLicenseSummary";

$tsql3 = "select top 1 productVersion from sec.vMachineInfo order by productVersion desc";

$tsql4 = "select count(productVersion) as count, productVersion from sec.vMachineInfo where installStatusDomainItemRef='SEC_InstallStatus_Installed' group by productVersion order by count desc";



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


$stmt3 = sqlsrv_query( $conn, $tsql3);
if( $stmt3 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$stmt4 = sqlsrv_query( $conn, $tsql4);
if( $stmt4 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}



$currentver = sqlsrv_fetch_array( $stmt3, SQLSRV_FETCH_ASSOC);
$currclientver = $currentver['productVersion'];


$licdata = array();

while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC)) { 

	$licdata[] = $row2;
}


foreach ($licdata as $key => $row) {
    $volume[$key]  = $row['sortOrder'];
}	
	array_multisort($volume, $licdata);
	

//* licences *//


echo "<div class=\"heading\">";
Echo "<image src=\"images/kav-logo.png\" style=\"vertical-align:middle\"> Antivirus Server Status";
echo "</div>";


echo "<div class=\"datatable\">";
echo "<table id=\"srvrstats\">";
echo "<caption>Licence Information</caption>";
echo "<tr><th>&nbsp;</th><th class=\"colL\">Workstation</th><th class=\"colL\">Server</th></tr>";
foreach ($licdata as $licentry)
{
    echo "<tr><td class=\"colL\">".$licentry['caption']."</td><td class=\"colM\">".$licentry['WorkstationCount']."</td><td class=\"colM\">".$licentry['ServerCount']."</td></tr>";
}
echo "</table>";
echo "</div>";



echo "<div class=\"datatable2\">";
echo "<table id=\"clientverison\">";
echo "<caption>Current Client Version</caption>";
echo "<tr><td class=\"colM\">".$currclientver."</td></tr>";
echo "</table>";
echo "</div>";



echo "<div class=\"datatable2\" >";
echo "<table id=\"clientvercounts\">";
echo "<caption>Client Version Counts</caption>";
echo "<tr><th class=\"colM\">Version</th><th class=\"colM\">Count</th></tr>";

while( $row = sqlsrv_fetch_array( $stmt4, SQLSRV_FETCH_ASSOC))
{
	echo "<tr><td class=\"colL\">".$row['productVersion']."</td><td class=\"colM\">".$row['count']."</td></tr>";
}
echo "</table>";
echo "</div>";



//* spacer *//
echo "<div class=\"spacer\"></div>";


//* agent status *//

echo "<div class=\"heading heading2\">";
echo "Antivirus Status";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";


echo "<div class=\"datatable\">";
echo "<table id=\"avlist\">";
echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colM\">Version</th><th class=\"colL\">Flags</th><th class=\"colM\">Profile Name</th><th class=\"colM\">Last Full Scan</th></tr>";


while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{


  
  echo "<tr><td class=\"colL\">";
  showAgentIcon($row['online'],$row['currentLogin']);
  echo "&nbsp;".$row['machineId']."</td>";


  $color="black";
  if ($row['ClientVersion'] != $currclientver) { $color='orange'; }
 
  echo "<td class=\"colL\"><font color =\"".$color."\">".$row['ClientVersion'];
  if ($row['minorVersion']<>'NotAvailable') { echo " ".$row['minorVersion']; }
  echo "</font></td>";
  
 
 echo "<td class=\"colM\">";
 
 if ($row['rebootRequired'] == 1) { echo "<image src=\"images/reboot.png\"  title=\"Needs Reboot\" >"; }
 
 if ($row['HasActiveThreats'] == 1) { echo "<image src=\"images/exclamation.png\"  title=\"Has Active Threat\" >"; }
 
 if ($row['installStatusDomainItemRef'] == 'SEC_InstallStatus_Installing') { echo "<image src=\"images/installing.png\"  title=\"Installing\" >"; }
 if ($row['installStatusDomainItemRef'] == 'SEC_InstallStatus_InstallFail') { echo "<image src=\"images/installfail.gif\"  title=\"Installation Failed\" >"; }
 
 if ($row['outdated'] == 1) { echo "<image src=\"images/warning.png\"  title=\"Outdated Definitions\" >"; }
 


echo "</td>";

  
  
  echo "<td class=\"colM\">".$row['ProfileName']."</td>";   

/*  

  $dispdate = (isset($row['LastUpdated']) ? date($datestyle." ".$timestyle,$row['LastUpdated']->getTimestamp()) : 'Never');
  $color = (isset($row['LastUpdated']) ? 'black' : 'red');
  echo "<td class=\"colM\"><font color =\"".$color."\">".$dispdate."</font></td>";
*/
  
  $dispdate = (isset($row['LastFullScan']) ? date($datestyle." ".$timestyle,$row['LastFullScan']->getTimestamp()) : 'Never');
  $color = (isset($row['LastFullScan']) ? 'black' : 'red');
  echo "<td class=\"colM\"><font color =\"".$color."\">".$dispdate."</font></td>";   
  
}
echo "</table>";
echo "</div>";

sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>