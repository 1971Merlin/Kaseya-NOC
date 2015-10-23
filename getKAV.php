<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

// installs filtered by scope/org
  
  $tsql5a = "select count (distinct agentId) as num FROM kav.AVInstallProgressState as kav";
if ($usescopefilter==true) { $tsql5a.=" join vdb_Scopes_Machines foo on (foo.agentGuid = kav.agentid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql5a.=" 
 join dbo.DenormalizedOrgToMach on kav.agentid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql5a.=" where kav.isCompleted = 1";

// failed installs  
$tsql5b = "select count(kav.agentId) as num from kav.AVInstallProgressState kav";
if ($usescopefilter==true) { $tsql5b.=" join vdb_Scopes_Machines foo on (foo.agentGuid = kav.agentid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql5b.=" 
 join dbo.DenormalizedOrgToMach on kav.agentid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql5b.=" inner join (select AgentId, MAX(Started) as started
  from kav.AVInstallProgressState
  group by AgentId) toprecord
  on toprecord.started = kav.Started
  where Phase <> 1";

// infected machines count
  
 $tsql6 = "select count (distinct  MachineID) as count
  from kav.vMachines as kav";  
if ($usescopefilter==true) { $tsql6.=" join vdb_Scopes_Machines foo on (foo.agentGuid = kav.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql6.=" 
 join dbo.DenormalizedOrgToMach on kav.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql6.=" where HasActiveThreats > 0 and isInstalled >0";

 
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


$stmt5a = sqlsrv_query( $conn, $tsql5a);
if( $stmt5a === false )
{
  echo "Error in executing query.<br/>";
  die( print_r( sqlsrv_errors(), true));
}

$stmt5b = sqlsrv_query( $conn, $tsql5b);
if( $stmt5b === false )
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

$row5a = sqlsrv_fetch_array( $stmt5a, SQLSRV_FETCH_ASSOC);
$row5b = sqlsrv_fetch_array( $stmt5b, SQLSRV_FETCH_ASSOC);
$row6 = sqlsrv_fetch_array( $stmt6, SQLSRV_FETCH_ASSOC);
$row9 = sqlsrv_fetch_array( $stmt9, SQLSRV_FETCH_ASSOC);
$row10 = sqlsrv_fetch_array( $stmt10, SQLSRV_FETCH_ASSOC);

echo "<div class=\"heading\">";
echo "<image src=\"images/kav-logo.png\" style=\"vertical-align:middle\"> Anti-Virus (KAV) Server Status";
echo "</div>";

// spacer
echo "<div class=\"spacer\"></div>";

$okinstalls = $row5a['num'];
$failedinstalls = $row5b['num'];
$okinstalls = $okinstalls - $failedinstalls;

// installs
  echo "<div class=\"minibox\">";
  echo "<div class=\"miniheading\">Installations</div>";
  echo "<div class=\"mininum\">";
  echo "<font color=\"blue\">".$okinstalls."</font>";
  echo "</div>";
  echo "</div>";
  
// failed installs
  echo "<div class=\"minibox\">";
  echo "<div class=\"miniheading\">Failed Installs</div>";
  echo "<div class=\"mininum\">";
  $color="green";
  if ($failedinstalls > 0) { $color="red"; }
  echo "<font color=\"".$color."\">".$failedinstalls."</font>";
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

 
sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>