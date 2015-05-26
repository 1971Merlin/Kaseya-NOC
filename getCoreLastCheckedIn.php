<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';


$left=true;
$right=true;

$id=$_GET["type"];
if ($id=='l') { $right=false; }
if ($id=='r') { $left=false; }


//* agent not checked in for a long time *//

$tsql = "select distinct top ".$resultcount." vl.displayName as MachineName, offlineTime, DATEDIFF(day, offlineTime, getdate()) as daysOffline
 from agentstate";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = agentstate.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on agentstate.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql.=" join vAgentLabel vl on vl.agentGuid = agentstate.agentGuid
 where agentstate.offlineTime < DATEADD(day,-30,getdate()) and agentstate.online=0
 order by offlineTime";

$tsql2 = "select count(distinct agentstate.agentGuid) as num
 from agentstate";
if ($usescopefilter==true) { $tsql2.=" join vdb_Scopes_Machines foo on (foo.agentGuid = agentstate.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql2.=" 
 join dbo.DenormalizedOrgToMach on agentstate.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql2.=" where agentstate.offlineTime < DATEADD(day,-30,getdate()) and agentstate.online=0";
 
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
	 echo "<img src=\"images/question.png\">";
 }
 echo "</td><td>{$total} Not Checked-in >30 days";
 echo "</td></tr></table>";

 }


if ($right==true) {

echo "<div class=\"heading\">";
echo "Agents not checking in >30 days";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";


if ($total!=0) {
  echo  "<table id=\"checkinlist\" class=\"datatable\">";
  echo  "<tr><th class=\"colL\">Machine Name</th><th class=\"colM\">Days Offline</th><th class=\"colM\">Last Check-in</th></tr>";

  while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
  {
     echo "<tr><td class=\"colL\">".$row['MachineName']."</td>";
	 echo "<td class=\"colM\">".$row['daysOffline']."</td>";
	 echo "<td class=\"colM\">".$row['offlineTime']->format($datestyle." ".$timestyle)."</td></tr>";
  }
  echo "</table>";
}

}
sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>