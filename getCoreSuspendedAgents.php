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

$tsql = "select distinct vl.displayName as MachineName,
 case
	when users.suspendAgent=1 then 'Agent'
	when patchParams.suspendAutoUpdate=1 and ((getdate()> monitorSuspend.startSuspend) and (getdate() < monitorSuspend.endSuspend)) then 'Patching, Monitor'
	when patchParams.suspendAutoUpdate=1 then 'Patching'
	when ((getdate()> monitorSuspend.startSuspend) and (getdate() < monitorSuspend.endSuspend)) then 'Monitor'
 end as reason
 from users";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = users.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on users.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql.=" join vAgentLabel vl on vl.agentGuid = users.agentGuid
  full outer join patchParams on users.agentGuid = patchParams.agentGuid
  full outer join monitorSuspend on users.agentGuid = monitorSuspend.agentGuid
  where users.suspendAgent=1 or patchParams.suspendAutoUpdate=1 or ((getdate()> monitorSuspend.startSuspend) and (getdate() < monitorSuspend.endSuspend))";


//* get counts *//
 
$tsql2 = "select count(distinct users.agentGuid) as num
 from users";
if ($usescopefilter==true) { $tsql2.=" join vdb_Scopes_Machines foo on (foo.agentGuid = users.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql2.=" 
 join dbo.DenormalizedOrgToMach on users.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql2.=" full outer join patchParams on users.agentGuid = patchParams.agentGuid
  full outer join monitorSuspend on users.agentGuid = monitorSuspend.agentGuid
  where users.suspendAgent=1 or patchParams.suspendAutoUpdate=1 or ((getdate()> monitorSuspend.startSuspend) and (getdate() < monitorSuspend.endSuspend))";

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



$row_count = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC);


$total = $row_count['num'];
 
 
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
echo "</div>";


if ($total!=0) {
  echo "<table id=\"suspendedlist\" class=\"datatable\">";
  echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colL\">Reason</th></tr>";

  while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
  {
     echo "<tr><td class=\"colL\"><FONT COLOR=\"FF0000\">".$row['MachineName']."</font></td><td class=\"colM\">".$row['reason']."</td></tr>";
  }
  echo "</table>";
}

}
sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>