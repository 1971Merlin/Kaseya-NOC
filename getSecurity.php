<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';
 
$tsql = "select KaseyaAVVersion, SignatureVersion, AVGInstallerVersion, AVGX64InstallerVersion, CheckForUpdatesLastCompletedAt
  from dbo.AVManager"; 
 
 
 $tsql2 = "select count (distinct avf.agentGuid) as count
  from AVFeature avf";
if ($usescopefilter==true) { $tsql2.=" join vdb_Scopes_Machines foo on (foo.agentGuid = avf.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql2.=" 
 join dbo.DenormalizedOrgToMach on avf.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 

 $tsql3 = "select count (distinct avf.agentGuid) as count
  from AVFeature avf";
if ($usescopefilter==true) { $tsql3.=" join vdb_Scopes_Machines foo on (foo.agentGuid = avf.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql3.=" 
 join dbo.DenormalizedOrgToMach on avf.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql3.=" where (avf.FullScanEndedAt is null or avf.FullScanEndedAt < DATEADD(day,-7,getdate())) and avf.InstallStatus = 1";  

  
  
 $tsql6 = "select count (distinct avf.agentGuid) as count
  from AVFeature avf";
if ($usescopefilter==true) { $tsql6.=" join vdb_Scopes_Machines foo on (foo.agentGuid = avf.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql6.=" 
 join dbo.DenormalizedOrgToMach on avf.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql6.=" where avf.FullScanStatus = 1 or avf.FullScanStatus = 2";  

  
$tsql7 = "select count(VirusName) as ActiveThreats from AVFile";
if ($usescopefilter==true) { $tsql7.=" join vdb_Scopes_Machines foo on (foo.agentGuid = AVFile.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql7.=" 
 join dbo.DenormalizedOrgToMach on AVFile.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
$tsql7.=" where Quarantined<>1 and ResolutionAction<>5";

$tsql10 = "select count(distinct avf.agentguid) as count
  from AVFeature avf";
if ($usescopefilter==true) { $tsql10.=" join vdb_Scopes_Machines foo on (foo.agentGuid = avf.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql10.=" 
 join dbo.DenormalizedOrgToMach on avf.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql10.=" where installstatus=1 and (filemonitorstatus<>1 or EnableProtection<>1)"; 
  
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

$stmt10 = sqlsrv_query( $conn, $tsql10);
if( $stmt10 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}



$row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC);
$currentAvSig = $row['SignatureVersion'];


// must do this now due dependency on $currentAvSig *//

 $tsql5 = "select count (distinct avf.agentGuid) as count
  from AVFeature avf";
if ($usescopefilter==true) { $tsql5.=" join vdb_Scopes_Machines foo on (foo.agentGuid = avf.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql5.=" 
 join dbo.DenormalizedOrgToMach on avf.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql5.=" join vAgentLabel vl on vl.agentGuid = avf.agentGuid  
  where ((vl.online>0 and vl.online<198) and SignatureVersion not like '%".$currentAvSig."%')";


$stmt5 = sqlsrv_query( $conn, $tsql5);
if( $stmt5 === false )
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


echo "<div class=\"heading\">";
echo "<image src=\"images/avg-logo.png\" style=\"vertical-align:middle\"> Security (AVG) Server Status";
echo "</div>";

// spacer
echo "<div class=\"spacer\"></div>";


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


sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>