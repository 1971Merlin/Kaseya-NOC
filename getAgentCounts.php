<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

// this query IGNORES suspended agents

$tsql = "select SUM(onl) as onl, SUM(ofl) as ofl, sum(onlsvr) as onlsvr, sum(oflsvr) as oflsvr from (

  select count (distinct agentState.agentGuid) as onl, 0 as ofl, 0 as onlsvr, 0 as oflsvr
  from agentState";
  
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = agentState.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on agentState.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
$tsql.=" join userIpInfo ip on ip.agentGuid = agentState.agentGuid
  where online<>0 and (ip.osInfo not like '%server%' and ip.osType not in ('2003','2008','2012','2016'))

  union
  
  select 0 as onl,count (distinct agentState.agentGuid) as ofl, 0 as onlsvr, 0 as oflsvr
  from agentState";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = agentState.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on agentState.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
$tsql.=" join userIpInfo ip on ip.agentGuid = agentState.agentGuid
  where online=0 and (ip.osInfo not like '%server%' and ip.osType not in ('2003','2008','2012','2016'))
  
  union
  
  select distinct 0 as onl, 0 as ofl, count(distinct agentState.agentGuid) as onlsvr, 0 as oflsvr
  from agentState";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = agentState.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on agentState.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
$tsql.=" join userIpInfo ip on ip.agentGuid = agentState.agentGuid
  where online<>0 and (ip.osInfo like '%server%' or ip.osType in ('2003','2008','2012','2016'))
  union
  
  select 0 as onl, 0 as ofl, 0 as onlsvr, count(distinct agentState.agentGuid) as oflsvr
  from agentState";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = agentState.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on agentState.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
$tsql.=" join userIpInfo ip on ip.agentGuid = agentState.agentGuid
  where online=0 and (ip.osInfo like '%server%' or ip.osType in ('2003','2008','2012','2016'))
 
) foo2";


$tsql2  = "select count (deviceID) as devcount
  from KMDM.vMobileDevices";
  

  
  
$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}



     $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC);
	 
	 echo "<table>";
	 echo "<tr><td>Servers</td><td>";
	 echo "<img src=\"images/systemok.gif\"> ";
	 echo $row['onlsvr']."</td><td>";
	 echo "<img src=\"images/systemdown.gif\"> ";
	 echo $row['oflsvr']."</td></tr>";
	 
	 echo "<tr><td>Workstations</td><td>";
	 echo "<img src=\"images/systemok.gif\"> ";
	 echo $row['onl']."</td><td>";
	 echo "<img src=\"images/systemdown.gif\"> ";
	 echo $row['ofl']."</td></tr>"; 

	 
// no more old MDM as of v9
if ($KVer > 6.3 and $KVer < 9.0) {

  $stmt2 = sqlsrv_query( $conn, $tsql2);
  if( $stmt2 === false )
  {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }

  $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC);
  echo "<tr><td>Mobile Devices</td><td>".$row2['devcount']."</td></tr>";
}
	 


	 echo "</table>";

sqlsrv_close( $conn );	 
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>