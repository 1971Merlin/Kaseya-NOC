<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

//* AVG Server Side Section *//

$tsql = "select KaseyaAVVersion, SignatureVersion,AVGInstallerVersion, AVGX64InstallerVersion, CheckForUpdatesLastCompletedAt
  from dbo.AVManager";

$tsql4 = "select count, caption, description from vKESSummary where caption not like 'Active Licenses Expiring%'";
 
   
  
$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
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

echo "<div class=\"heading\">";
echo "<image src=\"images/avg-logo.png\" style=\"vertical-align:middle\"> Security (AVG) Server Status";
echo "</div>";

echo "<div class=\"datatable\">";

echo "<table id=\"securitystats\">";
echo "<tr><th class=\"colL\">Kaseya AV Version</th><th class=\"colM\">Current Signature</t><th class=\"colM\">Last Time Sigs Checked</th><th class=\"colM\">AVG x86 Installer Version</th><th class=\"colL\">AVG x64 Installer Version</th></tr>";


while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
	$currentAvSig = $row['SignatureVersion'];
    echo "<tr><td class=\"colM\">".$row['KaseyaAVVersion']."</td>";
    echo "<td class=\"colM\">".$row['SignatureVersion']."</td>";
    echo "<td class=\"colM\">".$row['CheckForUpdatesLastCompletedAt']->format($datestyle." ".$timestyle)."</td>";
    echo "<td class=\"colM\">".$row['AVGInstallerVersion']."</td>";
    echo "<td class=\"colM\">".$row['AVGX64InstallerVersion']."</td></tr>";
}


echo "</table>";
echo "</div>";


echo "<div class=\"datatable\">";

echo "<table id=\"srvrstats\">";
echo "<tr><th class=\"colL\">License Counts</th><th class=\"colL\"></th><th class=\"colL\"></th></tr>";


while( $row = sqlsrv_fetch_array( $stmt4, SQLSRV_FETCH_ASSOC))
{
    echo "<tr><td class=\"colM\">".$row['count']."</td><td class=\"colL\">".$row['caption']."</td><td class=\"colL\">".$row['description']."</td></tr>";
}


echo "</table>";
echo "</div>";

//* spacer *//
echo "<div class=\"spacer\"></div>";

//* AVG client Side Section *//

$tsql = "select top ".$resultcount." * from 
 (SELECT distinct vl.displayName, case avf.InstallStatus
   WHEN 0 THEN 'Uninstalled'
   WHEN 1 THEN 'Installed'
   WHEN 2 THEN 'Failure'
   WHEN 3 THEN 'Removed by the user'
  END [InstallationStatus],
    avp.ProfileName,case avf.EnableProtection
    when 1 then 'Enabled'
	else 'Not Enabled'
  end [ProtectionEnabled],
  avf.KaseyaAVVersion,avf.AVClientVersion,avf.SignatureVersion,avf.LastUpdateTime,
  (select count(VirusName) from AVFile where avf.agentguid = AVFile.agentguid and AVFile.Quarantined<>1 and ResolutionAction<>5) as ActiveThreats,
  avf.filemonitorstatus as residentshield,
  vl.online as online, vl.currentLogin as currentLogin
  FROM AVFeature avf
  join vAgentLabel vl on vl.agentGuid = avf.agentGuid 
  JOIN AVProfile avp ON avf.ProfileId = avp.Id";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = avf.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on avf.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql.=") poop
  where (online>0 and online <198) and (
    (installationStatus='Installed' and residentshield<>1)
	or (ActiveThreats>0 and installationstatus='Installed')
	or InstallationStatus='Failure'
	or InstallationStatus='Removed by the user'
	or ProtectionEnabled<>'Enabled'
	or SignatureVersion not like '%".$currentAvSig."%'
  )
  order by ActiveThreats desc, case when residentshield = 0 then '1' when residentshield > 1 then '2' else '3' end, ProtectionEnabled desc, SignatureVersion desc";

  
$tsql10 = "select count(distinct avf.agentguid) as count
  from AVFeature avf
  join vAgentLabel vl on vl.agentGuid = avf.agentGuid";
if ($usescopefilter==true) { $tsql10.=" join vdb_Scopes_Machines foo on (foo.agentGuid = avf.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql10.=" 
 join dbo.DenormalizedOrgToMach on avf.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql10.=" where (installstatus<>1 or (installstatus=1 and (filemonitorstatus<>1 or EnableProtection<>1))) and (vl.online>0 and vl.online<198)";
 
 
$tsql7 = "select count(VirusName) as ActiveThreats from AVFile";
if ($usescopefilter==true) { $tsql7.=" join vdb_Scopes_Machines foo on (foo.agentGuid = AVFile.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql7.=" 
 join dbo.DenormalizedOrgToMach on AVFile.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
$tsql7.=" where Quarantined<>1 and ResolutionAction<>5";

 
 $tsql2 = "select count (distinct avf.agentGuid) as count
  from AVFeature avf";
if ($usescopefilter==true) { $tsql2.=" join vdb_Scopes_Machines foo on (foo.agentGuid = avf.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql2.=" 
 join dbo.DenormalizedOrgToMach on avf.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql2.=" where InstallStatus=1";
 

 $tsql3 = "select count (distinct avf.agentGuid) as count
  from AVFeature avf";
if ($usescopefilter==true) { $tsql3.=" join vdb_Scopes_Machines foo on (foo.agentGuid = avf.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql3.=" 
 join dbo.DenormalizedOrgToMach on avf.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql3.=" where (avf.FullScanEndedAt is null or avf.FullScanEndedAt < DATEADD(day,-7,getdate())) and avf.InstallStatus = 1";  

  
 $tsql5 = "select count (distinct avf.agentGuid) as count
  from AVFeature avf";
if ($usescopefilter==true) { $tsql5.=" join vdb_Scopes_Machines foo on (foo.agentGuid = avf.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql5.=" 
 join dbo.DenormalizedOrgToMach on avf.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql5.=" join vAgentLabel vl on vl.agentGuid = avf.agentGuid  
  where ((vl.online>0 and vl.online<198) and SignatureVersion not like '%".$currentAvSig."%')";
  
  
 $tsql6 = "select count (distinct avf.agentGuid) as count
  from AVFeature avf";
if ($usescopefilter==true) { $tsql6.=" join vdb_Scopes_Machines foo on (foo.agentGuid = avf.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql6.=" 
 join dbo.DenormalizedOrgToMach on avf.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql6.=" where avf.FullScanStatus = 1 or avf.FullScanStatus = 2";  

 
$tsql8 = "select top 5 virusname, count(virusname) as count
 from avfile";
if ($usescopefilter==true) { $tsql8.=" join vdb_Scopes_Machines foo on (foo.agentGuid = avfile.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql8.=" 
 join dbo.DenormalizedOrgToMach on avfile.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
$tsql8.=" where quarantined = 0 and resolutionaction <> 5
 group by VirusName
 order by count desc";

 
$tsql9 = "select top 5 virusname, count(virusname) as count
 from avfile";
if ($usescopefilter==true) { $tsql9.=" join vdb_Scopes_Machines foo on (foo.agentGuid = avfile.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql9.=" join dbo.DenormalizedOrgToMach on avfile.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
$tsql9.=" where quarantined = 1
 group by VirusName
 order by count desc";

 
$tsql11 = "select count(AVClientVersion) as count, AVClientVersion
 from AVFeature";
if ($usescopefilter==true) { $tsql11.=" join vdb_Scopes_Machines foo on (foo.agentGuid = AVFeature.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql11.=" join dbo.DenormalizedOrgToMach on AVFeature.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql11.=" where AVClientVersion is not null
 group by AVClientVersion
 order by AVClientVersion desc";

 
 

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


$stmt5 = sqlsrv_query( $conn, $tsql5);
if( $stmt5 === false )
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

$stmt11 = sqlsrv_query( $conn, $tsql11);
if( $stmt11 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC);
$checked_count = $row2['count'];

$row3 = sqlsrv_fetch_array( $stmt3, SQLSRV_FETCH_ASSOC);
$noscans = $row3['count'];

$row5 = sqlsrv_fetch_array( $stmt5, SQLSRV_FETCH_ASSOC);
$outdated = $row5['count'];

$row6 = sqlsrv_fetch_array( $stmt6, SQLSRV_FETCH_ASSOC);
$inprogress = $row6['count'];

$row7 = sqlsrv_fetch_array( $stmt7, SQLSRV_FETCH_ASSOC);
$threatcount = $row7['ActiveThreats'];

$row10 = sqlsrv_fetch_array( $stmt10, SQLSRV_FETCH_ASSOC);
$issues = $row10['count'];



echo "<div class=\"heading heading2\">";
echo "Security (AVG) Workstation Status";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";



// installs
  echo "<div class=\"minibox\">";
  echo "<div class=\"miniheading\">Installations</div>";
  echo "<div class=\"mininum\">";
  echo "<font color=\"blue\">".$checked_count."</font>";
  echo "</div>";
  echo "</div>";

// threats
  echo "<div class=\"minibox\">";
  echo "<div class=\"miniheading\">Active Threats</div>";
  echo "<div class=\"mininum\">";
  $color="green";
  if ($threatcount > 0) { $color="red"; }
  
  echo "<font color=\"".$color."\">".$threatcount."</font>";
  echo "</div>";
  echo "</div>";
  
// install issues
  echo "<div class=\"minibox\">";
  echo "<div class=\"miniheading\">Protection Issue</div>";
  echo "<div class=\"mininum\">";
  $color="blue";
  if ($issues > 0) { $color="orange"; }
  echo "<font color =\"".$color."\">".$issues."</font>";
  echo "</div>";
  echo "</div>";
  
// scans
  echo "<div class=\"minibox\">";
  echo "<div class=\"miniheading\">No Scan >7 days</div>";
  echo "<div class=\"mininum\">";
  
  $color="green";
  if ($noscans > 0) { $color="orange"; }
  echo "<font color =\"".$color."\">".$noscans."</font>";
  echo "</div>";
  echo "</div>";

// outdated
  echo "<div class=\"minibox\">";
  echo "<div class=\"miniheading\">Old Sigs</div>";
  echo "<div class=\"mininum\">";
  $color="green";
  if ($outdated > 0) { $color="orange"; }
  echo "<font color =\"".$color."\">".$outdated."</font>";
  echo "</div>";
  echo "</div>";

// in progress
  echo "<div class=\"minibox\">";
  echo "<div class=\"miniheading\">Scanning</div>";
  echo "<div class=\"mininum\">";
  $color="blue";
  if ($inprogress > 0) { $color="orange"; }
  echo "<font color =\"".$color."\">".$inprogress."</font>";
  echo "</div>";
  echo "</div>";
  
 // spacer
echo "<div class=\"spacer\"></div>";


// AV versions installed stats
echo "<div class=\"datatable\">";
echo "<table id=\"avversions\"><caption>AVG Installed Versions</caption>";
echo "<tr><th class=\"colL\">AVG Version</th><th class=\"colL\">Count</th></tr>";
while( $row = sqlsrv_fetch_array( $stmt11, SQLSRV_FETCH_ASSOC))
{
    echo "<tr><td class=\"colL\">".$row['AVClientVersion']."</td><td class=\"colL\">".$row['count']."</td></tr>";
}
echo "</table>";
echo "</div>";



// spacer
echo "<div class=\"spacer\"></div>";

  
echo "<div class=\"datatable\">";
echo "<table id=\"avgwsstats\">";
echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colM\">Status</th><th class=\"colM\">Active Threats</th><th class=\"colM\">Protection</th><th class=\"colM\">Client Version</th><th class=\"colM\">AV Signatures</th><th class=\"colM\">Last Time Sigs Updated</th></tr>";


while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{

  echo "<tr><td class=\"colL\">";
  showAgentIcon($row['online'],$row['currentLogin']);  
  echo "&nbsp;".$row['displayName']."</td>";
  
  $color='black';
  if ($row['InstallationStatus']== "Failure") { $color = 'red'; };
  
  
  echo "<td class=\"colM\"><font color=\"".$color."\">".$row['InstallationStatus']."</color></td>";
  
  echo "<td class=\"colM\">";  
  if ($row['ActiveThreats']>0) {
    echo "<font color=red>".$row['ActiveThreats']."</font></td>";
  } else {
    echo $row['ActiveThreats']."</td>";
  }
  
  echo "<td class=\"colM\">"; 
  
  $color="black";
  $message="";
  
  if ($row['InstallationStatus']=='Installed') {
	$message=$row['ProtectionEnabled'];
  	if ($row['ProtectionEnabled']!="Enabled") { $color = 'red'; $message=$row['ProtectionEnabled']; }
  	if ($row['residentshield']<>1) { 
  		$color = 'red';
		$message='Res. Shield ';
		if ($row['residentshield']==3) { $message.=' Partly Enabled'; } else { $message.=' Disabled'; }
	}
  }
  echo "<font color=".$color.">".$message."</font></td>";

  
  echo "<td class=\"colM\">".$row['AVClientVersion']."</td>";
  echo "<td class=\"colM\">".$row['SignatureVersion']."</td>";

  echo "<td class=\"colM\">";
  if ($row['LastUpdateTime']==null) {
    echo "-";
	} else {
    echo $row['LastUpdateTime']->format($datestyle." ".$timestyle);
   } 
  echo "</td>";
  
echo "</tr>";
}

echo "</table>";
echo "</div>";



//* spacer *//
echo "<div class=\"spacer\"></div>";


// active threats graph

echo "<div class=\"minibox\" style=\"clear:both\">";
  echo "<div class=\"heading\">";
  echo "Top 5 Active Threats";
  echo "</div>";
  echo "<div id=\"activeThreats\" class=\"avGraph\"></div>";
echo "</div>";

$datax = array();
while( $row8 = sqlsrv_fetch_array( $stmt8, SQLSRV_FETCH_ASSOC))
{
  $datax[]= "['".$row8['virusname']."', ".$row8['count']."]";
}

if ( count($datax)>0 ) { 

?>
<script type="text/javascript">
var chartACTIVE;

$(document).ready(function () {

	Highcharts.setOptions({
global: {
useUTC: false
		},
credits: {
enabled: false
		}
	});	

if (chartACTIVE) chartACTIVE.destroy();

chartSVR = new Highcharts.Chart({

chart: {
renderTo: 'activeThreats',
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
center: ['50%','60%'],
animation: false,
depth: 25,
dataLabels: {
distance: 15,
enabled: true,
format: '{point.name} : {point.y}',
style: {
fontSize: '9px'
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
} else {
?>	
	<script type="text/javascript">
	$('#activeThreats').html('<p class="AVmsg">No Active Threats Detected</p>');
	</script>
<?php
}


// virus vault threats graph

echo "<div class=\"minibox\">";
  echo "<div class=\"heading\">";
  echo "Top 5 Virus Vault Threats";
  echo "</div>";
  echo "<div id=\"vaultThreats\" class=\"avGraph\"></div>";
echo "</div>";

$datax = array();
while( $row9 = sqlsrv_fetch_array( $stmt9, SQLSRV_FETCH_ASSOC))
{
  $datax[]= "['".$row9['virusname']."', ".$row9['count']."]";
}

if ( count($datax)>0 ) { 

?>
<script type="text/javascript">
var chartVAULT;

$(document).ready(function () {

	Highcharts.setOptions({
global: {
useUTC: false
		},
credits: {
enabled: false
		}
	});	

if (chartVAULT) chartVAULT.destroy();

chartSVR = new Highcharts.Chart({

chart: {
renderTo: 'vaultThreats',
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
center: ['50%','60%'],
animation: false,
depth: 25,
dataLabels: {
distance: 15,
enabled: true,
format: '{point.name} : {point.y}',
style: {
fontSize: '9px'
},
},
showInLegend: true,
allowPointSelect: false,
},
},
title: { text: null	},
series: [{
name: 'Vault Threats',
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
} else {
?>	
	<script type="text/javascript">
	$('#vaultThreats').html('<p class="AVmsg">No Vault Threats Detected</p>');
	</script>
<?php
}
sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>