<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';


$left=true;
$right=true;

$id=$_GET["type"];
if ($id=='l') { $right=false; }
if ($id=='r') { $left=false; }


//* suspended agent *//

$tsql = "select distinct vl.displayName as MachineName, suspendAgent 
 from users";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = users.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on users.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql.=" join vAgentLabel vl on vl.agentGuid = users.agentGuid
 where users.suspendAgent=1";

$tsql2 = "select count(distinct users.agentGuid) as num
 from users";
if ($usescopefilter==true) { $tsql2.=" join vdb_Scopes_Machines foo on (foo.agentGuid = users.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql2.=" 
 join dbo.DenormalizedOrgToMach on users.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql2.=" where users.suspendAgent=1";
 
 //* suspended alarms *//
 
$tsql3 = "select distinct vl.displayName as MachineName
 from monitorSuspend";
if ($usescopefilter==true) { $tsql3.=" join vdb_Scopes_Machines foo on (foo.agentGuid = monitorSuspend.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql3.=" 
 join dbo.DenormalizedOrgToMach on monitorSuspend.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql3.=" join vAgentLabel vl on vl.agentGuid = monitorSuspend.agentGuid
 where (getdate()> startSuspend) and (getdate() < endSuspend)";

$tsql4 = "select count(distinct monitorSuspend.agentGuid) as num
 from monitorSuspend";
if ($usescopefilter==true) { $tsql4.=" join vdb_Scopes_Machines foo on (foo.agentGuid = monitorSuspend.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql4.=" 
 join dbo.DenormalizedOrgToMach on monitorSuspend.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql4.=" where (getdate()> startSuspend) and (getdate() < endSuspend)";
 
//* suspended patching *//
 
$tsql5 = "SELECT distinct vl.displayName as MachineName
  FROM patchParams";
if ($usescopefilter==true) { $tsql5.=" join vdb_Scopes_Machines foo on (foo.agentGuid = patchParams.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql5.=" 
 join dbo.DenormalizedOrgToMach on patchParams.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql5.=" join vAgentLabel vl on vl.agentGuid = patchParams.agentGuid
 where suspendAutoUpdate=1";
  
$tsql6 = "SELECT count(distinct patchParams.agentGuid) as num
  FROM patchParams";
if ($usescopefilter==true) { $tsql6.=" join vdb_Scopes_Machines foo on (foo.agentGuid = patchParams.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql6.=" 
 join dbo.DenormalizedOrgToMach on patchParams.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql6.=" where suspendAutoUpdate=1";
 
  
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

$stmt6 = sqlsrv_query( $conn, $tsql6);
if( $stmt6 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}



$row_count = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC);
$row_count2 = sqlsrv_fetch_array( $stmt4, SQLSRV_FETCH_ASSOC);
$row_count3 = sqlsrv_fetch_array( $stmt6, SQLSRV_FETCH_ASSOC);

$total = $row_count['num'] + $row_count2['num'] + $row_count3['num'];
 
 
if ($left==true) { 


echo "<table class=\"datatable\"><tr><td>";

if ($total==0) {
	 echo "<img src=\"images/check.png\">";
}
 else {
	 echo "<img src=\"images/stop.png\">";
 }
 echo "</td><td>{$total} Suspended Agent";
 if ($total != 1) { echo 's'; }
 echo "</td></tr></table>";

 }
 

if ($right==true) {

echo "<div class=\"heading\">";
echo "Suspended Agents";
// echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";


if ($total!=0) {
  echo "<table id=\"suspendedlist\" class=\"datatable\">";
  echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colL\">Reason</th></tr>";

  while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
  {
     echo "<tr><td class=\"colL\"><FONT COLOR=\"FF0000\">".$row['MachineName']."</font></td><td class=\"colM\">Agent</td></tr>";
  }
    while( $row = sqlsrv_fetch_array( $stmt3, SQLSRV_FETCH_ASSOC))
  {
     echo "<tr><td class=\"colL\"><FONT COLOR=\"FF0000\">".$row['MachineName']."</font></td><td class=\"colM\">Alarms</td></tr>";
  }
      while( $row = sqlsrv_fetch_array( $stmt5, SQLSRV_FETCH_ASSOC))
  {
     echo "<tr><td class=\"colL\"><FONT COLOR=\"FF0000\">".$row['MachineName']."</font></td><td class=\"colM\">Patching</td></tr>";
  }
  echo "</table>";
}

}
sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>