<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';
echo "<div class=\"heading\">Remote Control Details Last 24Hrs</div>";

$rc = array();

 // Classic RC // 
$tsql = "select adminName, eventTime, type, (duration/(1000*60)) as total, st.Machine_GroupID as machine, 99 as completed
  from rcLog 
  join vAgentLabel st on st.agentGuid = rcLog.agentGuid
  where eventTime > DATEADD(day,-1,getutcdate()) and duration>0 and rclog.agentGuid != 123456789
  order by eventTime desc";

		 
 // VSA 7.0 only //		 
$tsql2 = "select adminName, eventTime, 1 as type, substring(description,36,len(description)-36) as machine, 99 as completed
	from adminLog
	where eventTime > DATEADD(day,-1,getutcdate()) and description like 'Remote control%'
	order by eventTime desc";

  
 // VSA R8+ //
 $tsql3 = "select adminName, startTime as eventTime, sessionType as type, datediff(mi,startTime,lastActiveTime) as total, st.Machine_GroupID as machine, completed
   from KaseyaRemoteControl.Log";

if ($usescopefilter==true) { $tsql3.=" join vdb_Scopes_Machines foo on (foo.agentGuid = KaseyaRemoteControl.Log.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql3.=" 
 join dbo.DenormalizedOrgToMach on KaseyaRemoteControl.Log.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
   
  $tsql3.=" join vAgentLabel st on st.agentGuid = KaseyaRemoteControl.Log.agentGuid
   where startTime > DATEADD(day,-1,getdate()) and datediff(mi,startTime,lastActiveTime) > 0
   order by startTime desc";


 // LC sessions
 $tsql4 = "select UserName, TimeStamp, LogEntry, EventType, IPAddress, AgentName
 FROM Agents.KLCAuditLog
 where logEntry like 'Session started against endpoint:%' and timestamp > getdate()-1 
 order by timestamp desc";


// get classic sessions info //

$stmt = sqlsrv_query( $conn, $tsql);

if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
	$rc[] = array( 'adminName' => $row['adminName'], 'time' => $row['eventTime'], 'duration' => $row['total'], 'machine' => $row['machine'], 'type' => $row['type'], 'completed' => $row['completed'] );
}


// get R7 sessions info //

if ($KVer > 6.5 ) {
	$stmt2 = sqlsrv_query( $conn, $tsql2);
	if( $stmt2 === false )
	{
		echo "Error in executing query.<br/>";
		die( print_r( sqlsrv_errors(), true));
	}

	while( $row = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC))
	{
		$rc[] = array( 'adminName' => $row['adminName'], 'time' => $row['eventTime'], 'duration' => 0, 'machine' => $row['machine'], 'type' => $row['type'], 'completed' => $row['completed'] );
	}
}


// get R8 sessions info //

if ($KVer > 7 ) {
	$stmt3 = sqlsrv_query( $conn, $tsql3);
	if( $stmt3 === false )
	{
		echo "Error in executing query.<br/>";
		die( print_r( sqlsrv_errors(), true));
	}

	while( $row = sqlsrv_fetch_array( $stmt3, SQLSRV_FETCH_ASSOC))
	{
		$rc[] = array( 'adminName' => $row['adminName'], 'time' => $row['eventTime'], 'duration' => $row['total'], 'machine' => $row['machine'], 'type' => $row['type'], 'completed' => $row['completed'] );
	}
	
	

	$stmt4 = sqlsrv_query( $conn, $tsql4);
	if( $stmt4 === false )
	{
		echo "Error in executing query.<br/>";
		die( print_r( sqlsrv_errors(), true));
	}


	while( $row = sqlsrv_fetch_array( $stmt4, SQLSRV_FETCH_ASSOC))
	{
		$rc[] = array( 'adminName' => $row['UserName'], 'time' => $row['TimeStamp'], 'duration' => 0, 'machine' => $row['AgentName'], 'type' =>999, 'completed' => 1);
	}

}



// sort $rc into order by time //

  if (!empty($rc)) {

    foreach ($rc as $key => $row) {
      $dates[$key]  = $row['time']; 
    }
    array_multisort($dates, SORT_DESC, $rc);




  echo "<div class=\"datatable\">";

  echo "<table id=\"rcinfolist\">";
  echo "<tr><th class=\"colL\">Admin Name</th><th>Active</th><th class=\"colL\">Machine Name</th><th class=\"colL\">Start Time</th><th class=\"colL\">Duration</th><th class=\"colM\">Type</th></tr>";

  foreach ($rc as $value) {
 
  
	echo "<tr><td class=\"colL\">".$value['adminName']."</td>";
	
	echo "<td class=\"colM\">";
	if ($value['completed'] == 0) echo "<img src=\"images/systemok.gif\" title=\"Active Session\">";
	echo "</td>";
	echo "<td class=\"colL\">".$value['machine']."</td>";
	echo "<td class=\"colL\">".$value['time']->format($datestyle." ".$timestyle)."</td>";
	
	if ($value['duration']==0) echo "<td class=\"colM\">-"; else echo "<td class=\"colL\">".formatinterval($value['duration']);
	echo "</td>";

	
	echo "<td  class=\"colM\">";
//	if ($value['type']==1) { echo "KRC Console"; } else
//	if ($value['type']==2) { echo "KRC Private"; } else

	if ($value['type']==1) { echo "<img src=\"images/KLCshared.png\" title=\"KRC Console\">"; } else
	if ($value['type']==2) { echo "<img src=\"images/KLCprivate.png\" title=\"KRC Private\">"; } else
	if ($value['type']==101) { echo "FTP"; } else
//	if ($value['type']==201) { echo "VNC"; } else
	if ($value['type']==201) { echo "<img src=\"images/VNCicon.gif\" title=\"kVNC\">"; } else
	if ($value['type']==202) { echo "RAdmin"; } else
	if ($value['type']==203) { echo "<img src=\"images/rdp.png\" title=\"RDP\">"; } else
	if ($value['type']==204) { echo "PC Anywhere"; } else
	if ($value['type']==205) { echo "K-VNC"; } else
	if ($value['type']==206) { echo "KRC(beta?)"; } else
	if ($value['type']==999) { echo "<img src=\"images/liveconnect.png\" title=\"Live Connect\">"; } else
	echo "Unknown ID=".$value['type'];	
    echo "</td></tr>";
	
  }

  echo "</table>";
  echo"</div>";
  
} else echo "No RC sessions logged.";

sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>