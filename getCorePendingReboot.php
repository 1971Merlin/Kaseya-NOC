<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

$left=true;
$right=true;

$id=$_GET["type"];
if ($id=='l') { $right=false; }
if ($id=='r') { $left=false; }


$tsql = "select distinct top ".$resultcount." vl.displayName as MachineName, RebootReason = CASE
  when ps.rebootPending > 0 THEN 'Patching'";
  
  if ($avon==1) { $tsql .= " when (ps.rebootPending > 0 and avf.RebootNeeded > 0) THEN 'Patching, Security'
  when avf.RebootNeeded > 0 THEN 'Security'"; }
  $tsql .= " END
  from users";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = users.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on users.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  if ($avon==1) { $tsql .= " join AVFeature avf on avf.agentGuid = users.agentGuid "; }
  $tsql.=" join vAgentLabel vl on vl.agentGuid = users.agentGuid
  join patchStatusTotals ps on ps.agentGuid = users.agentGuid
  right join agentState st on st.agentGuid = users.agentGuid
  where (ps.rebootPending > 0";
  if ($avon==1) { $tsql .= " or avf.RebootNeeded >0) "; } else { $tsql .=")"; }
  $tsql.=" and st.online != 0";
  

$tsql2 = "select count(distinct vl.displayName) as num
  from users";
if ($usescopefilter==true) { $tsql2.=" join vdb_Scopes_Machines foo on (foo.agentGuid = users.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql2.=" 
 join dbo.DenormalizedOrgToMach on users.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  if ($avon==1) { $tsql2 .= " join AVFeature avf on avf.agentGuid = users.agentGuid "; }
  $tsql2.=" join vAgentLabel vl on vl.agentGuid = users.agentGuid
  join patchStatusTotals ps on ps.agentGuid = users.agentGuid
  right join agentState st on st.agentGuid = users.agentGuid
  where (ps.rebootPending > 0";
  if ($avon==1) { $tsql2 .= " or avf.RebootNeeded >0) "; } else { $tsql2 .=")"; }
  $tsql2.=" and st.online != 0";

  

$stmt2 = sqlsrv_query( $conn, $tsql2);
if( $stmt2 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$row_count = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC);


if ($left==true) {

echo "<table class=\"datatable\"><tr><td>";

if ($row_count['num']==0) {
	 echo "<img src=\"images/check.png\">";
}
 else {
	 echo "<img src=\"images/cross.png\">";
 }
 echo "</td><td>{$row_count['num']} Agent";
 if ($row_count['num'] != 1) { echo 's'; }
 echo " Pending Reboot";
 echo "</td></tr></table>";

}


if ($right==true) {

echo "<div class=\"heading\">";
echo "Agents Pending Reboot";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";

if ($row_count['num']!=0) {

  $stmt = sqlsrv_query( $conn, $tsql);
  if( $stmt === false )
  {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }

  echo "<table id=\"rebootlist\" class=\"datatable\">";
  echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colL\">Reason</th></tr>";

  while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
  {
     echo "<tr><td class=\"colL\">".$row['MachineName']."</td><td class=\"colL\">".$row['RebootReason']."</td></tr>";
  }
  echo "</table>";
}
}
sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>