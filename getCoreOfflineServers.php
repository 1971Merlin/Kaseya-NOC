<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

$left=true;
$right=true;

$id=$_GET["type"];
if ($id=='l') { $right=false; }
if ($id=='r') { $left=false; }



$tsql = "select distinct vl.displayName as MachineName, agentState.online as IsOnline, offlinetime as TimeOffline, DATEDIFF(HOUR,offlineTime, getdate()) as HoursOffline
 from agentState";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = agentState.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on agentState.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql.=" join userIpInfo ip on ip.agentGuid = agentState.agentGuid 
 join vAgentLabel vl on vl.agentGuid = agentState.agentGuid
 join users on users.agentGuid = agentState.agentGuid
 where ip.osInfo like '%server%' and agentState.online = 0 and (users.suspendAgent is null or users.suspendAgent = 0)
 order by HoursOffline ASC";

$tsql2 = "select count(distinct vl.DisplayName) as num
 from agentState";
if ($usescopefilter==true) { $tsql2.=" join vdb_Scopes_Machines foo on (foo.agentGuid = agentState.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql2.=" 
 join dbo.DenormalizedOrgToMach on agentState.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql2.=" join userIpInfo ip on ip.agentGuid = agentState.agentGuid 
 join vAgentLabel vl on vl.agentGuid = agentState.agentGuid
 join users on users.agentGuid = agentState.agentGuid
 where ip.osInfo like '%server%' and agentState.online = 0 and (users.suspendAgent is null or users.suspendAgent = 0)";



 
$stmt2 = sqlsrv_query( $conn, $tsql2);
if( $stmt2 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}
  $row_count = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC);


if ($left==true) {

  
//servers down

echo "<table class=\"datatable\"><tr><td>";

if ($row_count['num']==0) {
	 echo "<img src=\"images/check.png\">";
}
 else {
	 echo "<img src=\"images/cross.png\">";
 }
 echo "</td><td>{$row_count['num']} Offline Server";
 if ($row_count['num'] != 1) { echo 's'; }
 echo "</td></tr></table>";

 }
 

if ($row_count['num']!=0) {

if  ($alarmon==true) {

?><audio autoplay>
  <source src="sounds/tng_red_alert1.mp3" type="audio/mpeg">
Your browser does not support the audio element.
</audio>
<?php } ?>
<script type="text/javascript">
  document.body.style.background = 'red';
  document.title="<?php echo $row_count['num']." Server";
  if ($row_count['num']>1) { echo "s"; };
  echo " Offline :: ".$NOCtitle; ?>"
</script>
<?php

} else {
?>
<script type="text/javascript">
  document.title="<?php echo $NOCtitle; ?>"
  document.body.style.background = 'white';
</script>
<?php 
}




if ($right==true) {

echo "<div class=\"heading\">";
echo "Offline Servers";
echo "</div>";


if ($row_count['num']!=0) {




  $stmt = sqlsrv_query( $conn, $tsql);
  if( $stmt === false )
  {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }
 
  echo "<table id=\"offlinelist\" class=\"datatable\">";
  echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colL\">Last Seen Online</th><th class=\"colL\">Elapsed Time</th></tr>";

  
  while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
  {
     echo "<tr><td class=\"colL\">".$row['MachineName']."</td>";
	 echo "<td class=\"colM\">".$row['TimeOffline']->format($datestyle." ".$timestyle)."</td>";	 
	 echo "<td class=\"colM\">".formatdatediff($row['TimeOffline']->format('Y/m/d H:i:s'),new datetime("now"))."</td></tr>";
  }
  echo "</table>";
  
}  

}
sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>