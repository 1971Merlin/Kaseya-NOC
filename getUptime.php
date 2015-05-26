<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

$tsql = "select * from ( 

  Select distinct top ".ceil($resultcount/2)." t1.machName, t1.agentGuid as agentGuid, t1.groupName, t1.lastReboot, st.online, case when st.online = 2 then 1 else st.online end as olorder, st.currentLogin
  from vAgentConfiguration as t1";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = t1.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on t1.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql.=" join userIpInfo ip on ip.agentGuid = t1.agentGuid
  join vAgentLabel st on st.agentGuid = t1.agentGuid 
  where ip.osInfo like '%server%'
  order by olorder, lastReboot desc ) a
  
  union 

  select * from (
  
Select distinct top ".ceil($resultcount/2)." t1.machName, t1.agentGuid as agentGuid, t1.groupName, t1.lastReboot, st.online, case when st.online = 2 then 1 else st.online end as olorder, st.currentLogin
  from vAgentConfiguration as t1";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = t1.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on t1.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql.=" join userIpInfo ip on ip.agentGuid = t1.agentGuid
  join vAgentLabel st on st.agentGuid = t1.agentGuid 
  where ip.osInfo like '%server%'
  order by olorder desc, lastReboot ) b
  
  
  order by olorder, lastReboot desc
  ";

  

$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false ) {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$slist = array();

while ($row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC)) { $slist[] = $row; }




// get the uptimes

foreach ($slist as $key => $row) {

  $agentGuid = $row['agentGuid'];
  $tsql2 = "select totalOnline, measureTime from dbo.getMachUptime( {$agentGuid},GETDATE() - 7) ";
  
  $stmt2 = sqlsrv_query( $conn, $tsql2);
  if( $stmt2 === false ) {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }

  $res = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC);

  
  $slist[$key]['totalOnline'] = $res['totalOnline'];
  $slist[$key]['measureTime'] = $res['measureTime'];

}


  
echo "<div class=\"heading\">";
Echo "Server Uptimes";
echo "<div class=\"topn\">showing bottom and top ".ceil($resultcount/2)."</div>";
echo "</div>";
echo "<div class=\"datatable\">";
echo "<table id=\"uptimelist\">";
echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colL\">Time since last reboot</th><th class=\"colL\">7 day Uptime</th></tr>";


$count=0;


foreach ($slist as $key => $row) {


  echo "<tr><td class=\"colL\">";
  

  $count++;
 
  if ($count==ceil($resultcount/2)+1) {
	echo "......</td></tr>";
	echo "<tr><td class=\"colL\">";
  }
	  
  
  
  showAgentIcon($row['online'],$row['currentLogin']);
  echo "&nbsp;".$row['machName'].".".$row['groupName']."</td>";
  echo "<td class=\"colL\">";
  if ($row['online']==0)   { echo "<font color=red><b>CURRENTLY OFFLINE!!</b></font>"; } else
  if ($row['online']==198) { echo "<font color=orange><b>Agent Suspended!</b></font>"; } else
  if ($row['online']==199) { echo "<font color=red><b>Never Checked In!</b></font>"; } else
  { 
/* uptime low = color orange */
    if (is_null($row['lastReboot'])) {
		echo 'Not Checked In';
	} else {
		$uptime = date_diff(new datetime("now"),$row['lastReboot']);
		if ($uptime->days < 1) {
		echo "<font color=orange>".formatDateDiff($row['lastReboot'],new datetime("now"))."</font>";
		} else {
		echo formatDateDiff($row['lastReboot'],new datetime("now"));
		}
	}
  }
  echo "</td>";
  
  
  echo "<td class=\"colM\">";
  if ($row['online']==0) { echo "--"; } else { echo round(($row['totalOnline']/$row['measureTime'])*100,2)."%"; }
  echo "</td>";
  
  echo "</tr>";

}

echo "</table>";
echo "</div>";

sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>