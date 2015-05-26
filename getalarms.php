<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

$tsql = "select distinct top ".$resultcount." al.monitorAlarmId as id, vl.machine_GroupID as machName, al.alertSubject as Message, al.message as Message2, al.eventDateTime as evdate,
  active = CASE
    when monitoralarmstateid=1 then 'Open'
	else 'Closed'
	end, mt.name as type, al.monitorType as typeID, vl.online as online, vl.currentLogin as currentLogin, mset.name as monset, sl.name as servicename,
	pset.name as procset, pl.name as processname, cset.name as counterset, mc.name as countername, alt.setname as alertname
  from Users";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = users.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on users.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
$tsql.=" join vAgentLabel vl on vl.agentGuid = users.agentGuid
  join monitoralarm al on al.agentguid = users.agentguid
  join monitorType mt on mt.monitorTypeId = al.monitorType  

  left join monitorService ms WITH(NOLOCK) on ms.monitorServiceId = al.monitorCSPId
  left join serviceList sl WITH(NOLOCK) on sl.serviceListId = ms.serviceListId
  left join monitorSet mset WITH(NOLOCK) on mset.monitorSetId = ms.monitorSetId

  left join monitorProcess mp WITH(NOLOCK) on mp.monitorProcessId = al.monitorCSPId
  left join processList pl WITH(NOLOCK) on pl.processListId = mp.monitorProcessId  
  left join monitorSet pset WITH(NOLOCK) on pset.monitorSetID = mp.monitorSetId
  
  left join monitorCounter mc WITH(NOLOCK) on mc.monitorCounterId = al.monitorCSPId
  left join monitorSet cset WITH(NOLOCK) on cset.monitorSetID = mc.monitorSetId

  left join monitorAlertType alt WITH(NOLOCK) on alt.alertID = al.monitorCSPId
  
  where eventDateTime > DATEADD(day,-1,getutcdate())
  order by eventDateTime DESC";
  
  

$tsql2 = "select count(vl.machine_GroupID) as num
  from Users";
if ($usescopefilter==true) { $tsql2.=" join vdb_Scopes_Machines foo on (foo.agentGuid = users.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql2.=" 
 join dbo.DenormalizedOrgToMach on users.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
$tsql2.=" join vAgentLabel vl on vl.agentGuid = users.agentGuid
  right join monitoralarm al on al.agentguid = users.agentguid
  where eventDateTime > DATEADD(day,-1,getutcdate())";



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

// Fetch total tickets
$row = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC);
$al_count = $row['num'];



echo "<div class=\"heading\">";
echo "Alarms Last 24Hrs - {$al_count}";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";

echo "<div class=\"datatable\">";
echo "<table id=\"alarmlist\">";
echo "<tr><th class=\"colL\">Age</th><th class=\"colL\">Machine Name</th><th class=\"colM\">Status</th><th class=\"colM\">Type</th><th class=\"colM\">Alert/Monitor Set</th><th class=\"colM\">Counter/Service/Process</th><th class=\"colL\">Alarm Detail</th></tr>";

while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{

     $offset = date('Z');
	 $dispdate = date('Y/m/d H:i:s',$row['evdate']->getTimestamp()+$offset);
	 	 
	 echo "<tr><td class=\"colL\">".formatDateDiff($dispdate,new datetime("now"))."</td>";

	 echo "<td class=\"colL\">";
	 showAgentIcon($row['online'],$row['currentLogin']);  
	 echo "&nbsp;".$row['machName']."</td>";

     echo "<td class=\"colM\">";
     if ($row['active']=='Open') {
       echo "<font color=red>".$row['active']."</font></td>";
     } else {
       echo $row['active']."</td>";
     }

     echo "<td class=\"colM\">".$row['type']."</td>";

// ** print ID
//     echo "<td class=\"colM\">".$row['id']."</td>";
	 
     $tooltip = $row['Message2'];
	 $tooltip = str_replace(array('\r\n***', '\r\n', '\r', '\n'), '&#013;', $tooltip); 

	 
	 echo "<td class=\"colM\">";	 
	 if ($row['typeID']=="0") {	 
	   echo $row['counterset'];
	   
	   $len=strlen($tooltip);
	   $start=strpos($tooltip,'Log Value');
	   $len=strpos($tooltip,0x0D, $start)-$start;	   
	   $tooltip = substr($tooltip, $start, $len );
	  }
	  if ($row['typeID']=="1") {	 
	   echo $row['monset'];
	   $len=strlen($tooltip);
	   $start=strpos($tooltip,'Log Value');
	   $len=strpos($tooltip,0x0D, $start)-$start;	   
	   $tooltip = substr($tooltip, $start, $len );
	   }
	 if ($row['typeID']=="2") {	 
	   echo $row['procset'];
	   $len=strlen($tooltip);
	   $start=strpos($tooltip,'Log Value');
	   $len=strpos($tooltip,0x0D, $start)-$start;	   
	   $tooltip = substr($tooltip, $start, $len );
	   }
	  if ($row['typeID']=="4") {	 
	   echo $row['alertname'];
	  }
	  echo "</td>";

  
	  
	 echo "<td class=\"colL\" title=\"{$tooltip}\">";

	 if ($row['typeID']=='0') {	 
	   echo $row['countername'];
	  }

	 
	 if ($row['typeID']=='1') {	 
	   echo $row['servicename'];
	  }

	 
	 if ($row['typeID']=='2') {	 
	   echo $row['processname'];
	  }	  
	  
	 if ($row['typeID']=='4') {	 
	   echo substr($tooltip,0,35);
	   if (strlen($tooltip)>35) { echo "..."; }
	  }
	  echo "</td>";	 

	  
     echo "<td class=\"colL\">".substr($row['Message'],0,200);
     if (strlen($row['Message'])>200) { echo "..."; }

	 echo "</td></tr>";
}

echo "</table>";
echo "</div>";

sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>