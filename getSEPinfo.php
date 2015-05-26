<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

$tsql = "select distinct vl.displayName, ClientVersion, CurDefs, IsOutOfDate = case
    when IsOutOfDate = 0 then 'Up to Date'
	when IsOutOfDate = 1 then 'OUT of DATE'
  end, infected = case
    when IsInfected = 0 then 'Not Infected'
    when IsInfected = 1 then 'Infection Detected!'
  end,
  vl.online as online, vl.currentLogin as currentLogin
  from SymInt.EndpointMetaData
  join vAgentLabel vl on vl.agentGuid = SymInt.EndpointMetaData.agentGuid";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = SymInt.EndpointMetaData.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on SymInt.EndpointMetaData.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  
  
$tsql2 = "select distinct vl.displayName, CurDefs, TotalLicensedSeats, UsedLicenseSeats, ServerVersion, IsServerOutOfDate, LastServerContentDownload, LastServerContentDownloadDate,
  vl.online as online, vl.currentLogin as currentLogin
  from SymInt.EndpointMetaData
  join vAgentLabel vl on vl.agentGuid = SymInt.EndpointMetaData.agentGuid";
if ($usescopefilter==true) { $tsql2.=" join vdb_Scopes_Machines foo on (foo.agentGuid = SymInt.EndpointMetaData.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql2.=" 
 join dbo.DenormalizedOrgToMach on SymInt.EndpointMetaData.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql2.=" where IsServer = 1 ";
  
  

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

//* servers *//

echo "<div class=\"heading\">";
echo "<image src=\"images/sep-logo.png\" style=\"vertical-align:middle\"> Symantec Endpoint Protection Server Status";
echo "</div>";

echo "<div class=\"datatable\">";
echo "<table id=\"SEPServerStats\">";
echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colM\">Total Licences</t><th class=\"colM\">Used Licences</th><th class=\"colM\">Server Version</th><th class=\"colL\">Current Definitions</th><th class=\"colL\">Last Update</th><th class=\"colL\">Last Update Check</th></tr>";

while( $row = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC))
{
     echo "<tr><td class=\"colL\">";
	 showAgentIcon($row['online'],$row['currentLogin']);  
	 echo "&nbsp;".$row['displayName']."</td>";
	 echo "<td class=\"colM\">".$row['TotalLicensedSeats']."</td>";
	 
	 echo "<td class=\"colM\">";
	 
	 if ( $row['UsedLicenseSeats'] > $row['TotalLicensedSeats']) {
	 
	   echo "<font color=\"red\">".$row['UsedLicenseSeats']."</font></td>";
	 } else {
	   echo $row['UsedLicenseSeats']."</td>";
	 }
	 echo "<td class=\"colM\">".$row['ServerVersion']."</td>";
     echo "<td class=\"colM\">".date($datestyle,strtotime($row['CurDefs']))."</td>"; 
	 echo "<td class=\"colM\">".$row['LastServerContentDownload']."</td>";
	 $offset = date('Z');
     echo "<td class=\"colL\">".date($datestyle." ".$timestyle,$row['LastServerContentDownloadDate']->getTimestamp()+$offset)."</td></tr>";
	 }
echo "</table>";
echo "</div>";

//* spacer *//

echo "<div class=\"spacer\">";
echo "</div>";

//* endpoints *//

echo "<div class=\"heading heading2\">";
echo "Symantec Endpoint Protection Client Status";
echo "</div>";

echo "<div class=\"datatable\">";
echo "<table id=\"SEPStats\">";
echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colM\">Infection Status</t><th class=\"colM\">Content Status</t><th class=\"colM\">Client Version</th><th class=\"colL\">Current Definitions</th></tr>";

while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
     echo "<tr><td class=\"colL\">";
	 showAgentIcon($row['online'],$row['currentLogin']);  
	 echo "&nbsp;".$row['displayName']."</td>";
	 echo "<td class=\"colM\">".$row['infected']."</td>";
	 echo "<td class=\"colM\">".$row['IsOutOfDate']."</td>";
	 echo "<td class=\"colM\">".$row['ClientVersion']."</td>";
     echo "<td class=\"colM\">".date($datestyle,strtotime($row['CurDefs']))."</td></tr>"; 
	 }
echo "</table>";
echo "</div>";

sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>