<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

$tsql = "select distinct top ".$resultcount." st.displayName as machName, Name, HasActiveThreats, LastUpdated, BaseDate, RebootNeeded,
 OldDefs = case
   when BaseDate < DATEADD(day,-14,getutcdate()) then 1
   else 0
	   end,
 ClientVersion, st.online, st.currentLogin
  from kav.kasperskyFeature as kav";  
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = kav.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on kav.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql.=" join vAgentLabel st on st.agentGuid = kav.agentGuid 
  join kav.KasperskyProfile ON kav.AppliedProfileId = kav.KasperskyProfile.Id
  
  where RebootNeeded=1 or HasActiveThreats=1 or BaseDate < DATEADD(day,-14,getutcdate()) or ClientVersion not like ( select availableversion from SecurityCenter.InstallerVersion where installername like '%Kaspersky%' )
  
  order by HasActiveThreats DESC, RebootNeeded DESC, BaseDate, LastUpdated, ClientVersion";

  
  

  
  $tsql2 = "select KAVWorstationLicensesUsed as wslic, KAVServerLicensesUsed as svrlic, KAVWorstationLicensesExpired as wslicexp, KAVServerLicensesExpired as svrlicexp,
 KAVWorkstationPurchasedLicenseCount as wslicpurch, KAVServerPurchasedLicenseCount as svrlicpurch
 from kav.vKAVLicenseCounts";
  
  $tsql3 = "select installername, version, availableversion from SecurityCenter.InstallerVersion where installername like '%Kaspersky%'";
  
  $tsql4 = "select st.Machine_GroupID as name, started, phase, st.online, st.currentLogin
  FROM kav.AVInstallProgressState as kav";
if ($usescopefilter==true) { $tsql4.=" join vdb_Scopes_Machines foo on (foo.agentGuid = kav.agentid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql4.=" 
 join dbo.DenormalizedOrgToMach on kav.agentid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql4.=" join vAgentLabel st on st.agentGuid = kav.AgentId 
  where isCompleted = 0";

// incomplete installs
  
  $tsql5 = "select count (distinct agentId) as num FROM kav.AVInstallProgressState as kav";
if ($usescopefilter==true) { $tsql5.=" join vdb_Scopes_Machines foo on (foo.agentGuid = kav.agentid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql5.=" 
 join dbo.DenormalizedOrgToMach on kav.agentid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql5.=" where kav.isCompleted = 0";

// installs filtered by scope/org
  
  $tsql5a = "select count (distinct agentId) as num FROM kav.AVInstallProgressState as kav";
if ($usescopefilter==true) { $tsql5a.=" join vdb_Scopes_Machines foo on (foo.agentGuid = kav.agentid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql5a.=" 
 join dbo.DenormalizedOrgToMach on kav.agentid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql5a.=" where kav.isCompleted = 1";

// infected machines count
  
 $tsql6 = "select count (distinct  MachineID) as count
  from kav.vMachines as kav";  
if ($usescopefilter==true) { $tsql6.=" join vdb_Scopes_Machines foo on (foo.agentGuid = kav.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql6.=" 
 join dbo.DenormalizedOrgToMach on kav.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql6.=" where HasActiveThreats > 0 and isInstalled >0";


// top active threats count for graph
  
 $tsql7 = "select top 5 count (name) as count, name
  from kav.ThreatDetection as kav";  
if ($usescopefilter==true) { $tsql7.=" join vdb_Scopes_Machines foo on (foo.agentGuid = kav.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql7.=" 
 join dbo.DenormalizedOrgToMach on kav.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql7.=" where Status in (0,1,2,6)
  group by name
  order by count desc";
 
/*
0 = Infected 
1 = Suspicious 
2 = Detected 
3 = Disinfected 
4 = Deleted 
5 = Other 
6 = Unknown 
7 = Quarantined 
8 = QuarantinedRestoreRequest 
9 = Restored 
10 = QuarantinedDeleteRequest 
11 = AddedByUser 
12 = RemediatedByUser
*/

 
// top resolved threats count for graph
  
 $tsql8 = "select top 5 count (name) as count, name
  from kav.ThreatDetection as kav";  
if ($usescopefilter==true) { $tsql8.=" join vdb_Scopes_Machines foo on (foo.agentGuid = kav.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql8.=" 
 join dbo.DenormalizedOrgToMach on kav.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql8.=" where Status not in (0,1,2,6)
  group by name
  order by count desc";

// full scans in progress
  
 $tsql9 = "select count (distinct kav.agentguid) as count
  from kav.KasperskyFeature as kav";  
if ($usescopefilter==true) { $tsql9.=" join vdb_Scopes_Machines foo on (foo.agentGuid = kav.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql9.=" 
 join dbo.DenormalizedOrgToMach on kav.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql9.=" where FullScanInProgress > 0";
  
// outdated definitions  
  
$tsql10 = "select count (distinct kav.agentguid) as count
  from kav.vMachines as kav";  
if ($usescopefilter==true) { $tsql10.=" join vdb_Scopes_Machines foo on (foo.agentGuid = kav.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql10.=" 
 join dbo.DenormalizedOrgToMach on kav.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql10.=" where BaseDate < DATEADD(day,-14,getutcdate())";

  
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

$stmt5 = sqlsrv_query( $conn, $tsql5);
if( $stmt5 === false )
{
  echo "Error in executing query.<br/>";
  die( print_r( sqlsrv_errors(), true));
}

$stmt5a = sqlsrv_query( $conn, $tsql5a);
if( $stmt5a === false )
{
  echo "Error in executing query.<br/>";
  die( print_r( sqlsrv_errors(), true));
}

$stmt6 = sqlsrv_query( $conn, $tsql6);
if( $stmt6 === false )
{
  echo "Error in executing query.<br/>";
  die( print_r( sqlsrv_errors(), true));
}

$stmt7 = sqlsrv_query( $conn, $tsql7);
if( $stmt7 === false )
{
  echo "Error in executing query.<br/>";
  die( print_r( sqlsrv_errors(), true));
}

$stmt8 = sqlsrv_query( $conn, $tsql8);
if( $stmt8 === false )
{
  echo "Error in executing query.<br/>";
  die( print_r( sqlsrv_errors(), true));
}

$stmt9 = sqlsrv_query( $conn, $tsql9);
if( $stmt9 === false )
{
  echo "Error in executing query.<br/>";
  die( print_r( sqlsrv_errors(), true));
}

$stmt10 = sqlsrv_query( $conn, $tsql10);
if( $stmt10 === false )
{
  echo "Error in executing query.<br/>";
  die( print_r( sqlsrv_errors(), true));
}


$row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC);

$row3 = sqlsrv_fetch_array( $stmt3, SQLSRV_FETCH_ASSOC);

$row5a = sqlsrv_fetch_array( $stmt5a, SQLSRV_FETCH_ASSOC);

$row6 = sqlsrv_fetch_array( $stmt6, SQLSRV_FETCH_ASSOC);

$row9 = sqlsrv_fetch_array( $stmt9, SQLSRV_FETCH_ASSOC);

$row10 = sqlsrv_fetch_array( $stmt10, SQLSRV_FETCH_ASSOC);


echo "<div class=\"heading\">";
echo "<image src=\"images/kav-logo.png\" style=\"vertical-align:middle\"> Anti-Virus (KAV) Server Status";
echo "</div>";

echo "<div class=\"datatable\">";

echo "<table id=\"kavsvrlist\">";
echo "<tr><th class=\"colL\">SVR Purchased</th><th class=\"colL\">WS Purchased</th><th class=\"colL\">SVR in Use</th><th class=\"colL\">WS in Use</th><th class=\"colL\">SVR Expired</th><th class=\"colL\">WS Expired</th></tr>";

echo "<tr>";
  
echo "<td class=\"colM\">".$row2['svrlicpurch']."</td>";
echo "<td class=\"colM\">".$row2['wslicpurch']."</td>";
echo "<td class=\"colM\">".$row2['svrlic']."</td>";
echo "<td class=\"colM\">".$row2['wslic']."</td>";
echo "<td class=\"colM\">".$row2['svrlicexp']."</td>";
echo "<td class=\"colM\">".$row2['wslicexp']."</td>";
echo "</tr></table>";


$checked_count = $row2['svrlic'] + $row2['wslic'];

echo $row2['svrlicpurch']-$row2['svrlic']-$row2['svrlicexp']." available Server Licenses</br>";
echo $row2['wslicpurch']-$row2['wslic']-$row2['wslicexp']." available Workstation Licenses</br>";
echo "AV Engine ".$row3['installername'].": Current Version ".$row3['version'].". Available Version ".$row3['availableversion']."</br>";

echo "</div>";

//* spacer *//
echo "<div class=\"spacer\"></div>";


echo "<div class=\"heading heading2\">";
echo "Anti-Virus (KAV) Workstation Status";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";




// installs
  echo "<div class=\"minibox\">";
  echo "<div class=\"miniheading\">Installations</div>";
  echo "<div class=\"mininum\">";
  echo "<font color=\"blue\">".$row5a['num']."</font>";
  echo "</div>";
  echo "</div>";


// infected machines
  echo "<div class=\"minibox\">";
  echo "<div class=\"miniheading\">Active Threats</div>";
  echo "<div class=\"mininum\">";
  $color="green";
  if ($row6['count'] > 0) { $color="red"; }
  echo "<font color =\"".$color."\">".$row6['count']."</font>";
  echo "</div>";
  echo "</div>";

// Scans in progress
  echo "<div class=\"minibox\">";
  echo "<div class=\"miniheading\">Scanning</div>";
  echo "<div class=\"mininum\">";
  $color="blue";
  if ($row9['count'] > 0) { $color="orange"; }
  echo "<font color =\"".$color."\">".$row9['count']."</font>";
  echo "</div>";
  echo "</div>";

// Out of date definitions
  echo "<div class=\"minibox\">";
  echo "<div class=\"miniheading\">Old Defs</div>";
  echo "<div class=\"mininum\">";
  $color="blue";
  if ($row10['count'] > 0) { $color="orange"; }
  echo "<font color =\"".$color."\">".$row10['count']."</font>";
  echo "</div>";
  echo "</div>";

  

// list machines active

echo "<div class=\"datatable\">";
echo "<table id=\"kavlist\">";
echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colL\">Profile Name</th><th class=\"colL\">Active Threats</th><th class=\"colL\">Last Client Update</th><th class=\"colL\">Definition File</th><th class=\"colL\">Version</th><th class=\"colM\">Reboot Needed</th></tr>";


while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
  echo "<tr><td class=\"colL\">";
  showAgentIcon($row['online'],$row['currentLogin']);  
  echo "&nbsp;".$row['machName']."</td>";
  echo "<td class=\"colL\">".$row['Name']."</td>";
  echo "<td class=\"colM\">";
  
  
  if ($row['HasActiveThreats']>0) {
	echo "<font color=\"red\">".$row['HasActiveThreats']." Threat";
    if ($row['HasActiveThreats']!=1) { echo "s"; }
    echo " Detected</font>";
  } else { echo "None"; }
  echo "</td>";
 
  $offset = date('Z');
  $dispdate = date($datestyle." ".$timestyle,$row['LastUpdated']->getTimestamp()+$offset);
  
  $defdate  = date($datestyle." ".$timestyle,$row['BaseDate']->getTimestamp());
  
  echo "<td class=\"colL\">".$dispdate."</td>";
  
  
  if ($row['OldDefs']==1) { $colr='red'; } else $colr='black';
  echo "<td class=\"colL\"><font color=\"".$colr."\">".$defdate."</font></td>";

  echo "<td class=\"colL\">".$row['ClientVersion']."</td>";

    echo "<td class=\"colM\">";
	if ($row['RebootNeeded']==1) { echo "Yes"; } else { echo "No"; }
	echo "</td>";


  echo "</tr>";
}


echo "</table>";
echo "</div>";


$row5 = sqlsrv_fetch_array( $stmt5, SQLSRV_FETCH_ASSOC);

if ($row5['num']!=0) {

//* spacer *//
  echo "<div class=\"spacer\"></div>";

  echo "<div class=\"heading heading2\">";
  echo "Pending Installs Status";
  echo "</div>";

// list machines being installed

  echo "<div class=\"datatable\">";
  echo "<table id=\"kavinstlist\">";
  echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colL\">Stage</th><th class=\"colL\">Date Started</th></tr>";

  while( $row4 = sqlsrv_fetch_array( $stmt4, SQLSRV_FETCH_ASSOC))
  {
    echo "<tr><td class=\"colL\">";
    showAgentIcon($row4['online'],$row4['currentLogin']);  
    echo "&nbsp;".$row4['name']."</td>";

    echo "<td class=\"colM\">";
  
    if ($row4['phase']==1) { echo "Installed"; }
    elseif ($row4['phase']==2) { echo "Failed"; }
    elseif ($row4['phase']==3) { echo "Removed by User"; }
    elseif ($row4['phase']==4) { echo "Script Scheduled"; }
    elseif ($row4['phase']==5) { echo "Waiting for Service"; }
    elseif ($row4['phase']==6) { echo "Waiting for Installer"; }
    elseif ($row4['phase']==7) { echo "Installing Antivirus"; }
    elseif ($row4['phase']==8) { echo "Uninstall Script Scheduled"; }
    elseif ($row4['phase']==9) { echo "Uninstalling Antivirus"; }
	elseif ($row4['phase']==10) { echo "Finalizing Uninstall"; }
	elseif ($row4['phase']==11) { echo "Uninstall Failed"; }
	elseif ($row4['phase']==12) { echo "Not Installed"; }
    elseif ($row4['phase']==13) { echo "Verifying Install"; }
    elseif ($row4['phase']==14) { echo "KES Installed"; }
    elseif ($row4['phase']==15) { echo "Uninstalling KES"; }
    elseif ($row4['phase']==16) { echo "Waiting for Reboot"; }
    elseif ($row4['phase']==17) { echo "KES Uninstall Failed"; }
    elseif ($row4['phase']==18) { echo "Verify Failed"; }
    elseif ($row4['phase']==19) { echo "Upgrading Client"; }
    elseif ($row4['phase']==20) { echo "Upgrading Client Failed"; }
    elseif ($row4['phase']==21) { echo "End of Life"; }
    elseif ($row4['phase']==22) { echo "Connect Install"; }

    else echo $row4['phase'];
  
    echo "</td>";
 
    $dispdate = date($datestyle." ".$timestyle,$row4['started']->getTimestamp());

    echo "<td class=\"colL\">".$dispdate."</td>";
    echo "</tr>";
  }


  echo "</table>";
  echo "</div>";

}


//* spacer *//
echo "<div class=\"spacer\"></div>";



// active threats graph

echo "<div class=\"minibox\" style=\"clear:both\">";
  echo "<div class=\"heading\">";
  echo "Top 5 Active Threats";
  echo "</div>";
  echo "<div id=\"kavThreats\" class=\"avGraph\"></div>";
echo "</div>";

$datax = array();

while( $row7 = sqlsrv_fetch_array( $stmt7, SQLSRV_FETCH_ASSOC))
{
  $datax[]= "['".$row7['name']."', ".$row7['count']."]";
}

if ( count($datax)>0 ) { 

?>
<script type="text/javascript">
var chartKAVTHREAT;

$(document).ready(function () {

	Highcharts.setOptions({
global: {
useUTC: false
		},
credits: {
enabled: false
		}
	});	

if (chartKAVTHREAT) chartKAVTHREAT.destroy();

chartSVR = new Highcharts.Chart({

chart: {
renderTo: 'kavThreats',
type: 'pie',
options3d: {
enabled: false,
alpha: 45,
beta: 0,
},
height: 252,
width: 400,	
margin: [0, 0, 0, 0],
},
tooltip: { enabled: true },
legend: {
enabled: true,
align: 'left',
labelFormat: '<b>{name}</b> ({percentage:.1f}%)',
verticalAlign: 'top',
layout: 'vertical',
symbolHeight: 9,
itemStyle: { fontSize: '8px', fontWeight: 'normal' },
margin: 0,
borderWidth: 1,
borderRadius: 3,
backgroundColor: '#f0f0f0'
},
plotOptions: {
pie: {
center: ['50%','65%'],
animation: false,
depth: 25,
dataLabels: {
distance: 15,
enabled: true,
format: '{point.name} : {point.y}',
style: {
fontSize: '9px',
width: '120px'
},
},
showInLegend: true,
allowPointSelect: false,
},
},
title: { text: null	},
series: [{
name: 'Active Threats',
data: [<?php echo join($datax, ',') ?>],
tooltip: {
headerFormat: '<b>{point.key}</b><br/>',
pointFormat: '{series.name}: {point.y}',
			}
		}]
	})
});
</script>
<?php
}


// resolved threats graph

echo "<div class=\"minibox\">";
  echo "<div class=\"heading\">";
  echo "Top 5 Resolved Threats";
  echo "</div>";
  echo "<div id=\"kavResolved\" class=\"avGraph\"></div>";
echo "</div>";

$datax = array();

while( $row8 = sqlsrv_fetch_array( $stmt8, SQLSRV_FETCH_ASSOC))
{
  $datax[]= "['".$row8['name']."', ".$row8['count']."]";
}

if ( count($datax)>0 ) { 

?>
<script type="text/javascript">
var chartKAVRESOLVED;

$(document).ready(function () {

	Highcharts.setOptions({
global: {
useUTC: false
		},
credits: {
enabled: false
		}
	});	

if (chartKAVRESOLVED) chartKAVRESOLVED.destroy();

chartSVR = new Highcharts.Chart({

chart: {
renderTo: 'kavResolved',
type: 'pie',
options3d: {
enabled: false,
alpha: 45,
beta: 0,
},
height: 252,
width: 400,	
margin: [0, 0, 0, 0],
},
tooltip: { enabled: true },
legend: {
enabled: true,
align: 'left',
labelFormat: '<b>{name}</b> ({percentage:.1f}%)',
verticalAlign: 'top',
layout: 'vertical',
symbolHeight: 9,
itemStyle: { fontSize: '8px', fontWeight: 'normal' },
margin: 0,
borderWidth: 1,
borderRadius: 3,
backgroundColor: '#f0f0f0'
},
plotOptions: {
pie: {
center: ['50%','65%'],
animation: false,
depth: 25,
dataLabels: {
distance: 15,
enabled: true,
format: '{point.name} : {point.y}',
style: {
fontSize: '9px',
width: '120px'
},
},
showInLegend: true,
allowPointSelect: false,
},
},
title: { text: null	},
series: [{
name: 'Resolved Threats',
data: [<?php echo join($datax, ',') ?>],
tooltip: {
headerFormat: '<b>{point.key}</b><br/>',
pointFormat: '{series.name}: {point.y}',
			}
		}]
	})
});
</script>
<?php 
}

sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>