<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';


// get list of machine groups //
$tsql = "SELECT distinct groupName FROM machGroup";

if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_MachineGroups foo on (foo.MachGroupGuid = machGroup.MachGroupGuid and foo.scope_ref = '".$scope_filter."')"; }

if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMachGroup on machGroup.MachGroupGuid = dbo.DenormalizedOrgToMachGroup.MachGroupGuid
  and dbo.DenormalizedOrgToMachGroup.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }

 

// list of machine groups to be checked //
$mglist = array(); 

// list of machine groups with an issue found //
$mgissue = array();


$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}
while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
	 $mglist[]=$row['groupName'];
}



// alarms
  $allist = array();

  
// get list of all possible alarm types
  $mtlist = array();
  
  $tsql = "select name from monitorType";
  $stmt = sqlsrv_query( $conn, $tsql);
  if( $stmt === false )
  {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }

  while( $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
	$mtlist[]=$row['name'];
  }
  
  
// get alarms

  $tsql = "select count(distinct vl.machine_GroupID) as count, groupName, mt.name as altype
    from Users";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = users.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on users.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
    $tsql.=" join vAgentName vl on vl.agentGuid = users.agentGuid 
    join monitoralarm al on al.agentguid = users.agentguid
    join monitorType mt on mt.monitorTypeId = al.monitorType  
    where monitorAlarmStateId=1 
	group by vl.groupName, mt.name";
 
  $stmt = sqlsrv_query( $conn, $tsql);
  if( $stmt === false )
  {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }

  while( $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
	$allist[$row['groupName']] = array ($row['altype'] => $row['count']);
	if (!in_array($row['groupName'], $mgissue)) { $mgissue[] = $row['groupName']; }
}


  
  
// low disk //

$ldlist = array();

$tsql = "select count(distinct vl.Machine_GroupID) as count, vl.groupName as groupName
 from vCurrDiskInfo";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = vCurrdiskInfo.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on vCurrDiskInfo.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql.=" join vAgentName vl on vl.agentGuid = vCurrDiskInfo.agentGuid 
 where DriveType = 'Fixed' and TotalSpace >0 and VolumeName not like '%recovery%' and VolumeName not like 'System Reserved' and VolumeName not like 'HP_TOOLS'
 and case
   when FreeSpace < 1024*15 or (cast(freespace as float)/cast(totalspace as float))*100 < 15 then 1
  else 2
  end <2  
  group by vl.groupName";
 
  $stmt = sqlsrv_query( $conn, $tsql);
  if( $stmt === false )
  {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }
  while( $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
	$ldlist[$row['groupName']] = $row['count'];
	if (!in_array($row['groupName'], $mgissue)) { $mgissue[] = $row['groupName']; }
  }
 

 
 



// backup //
if ($buon==1) {

$bulist = array();

$tsql = "select count(distinct vl.Machine_GroupID) as count, vl.groupName as groupName
 from vBackupLog";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = vBackupLog.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on vBackupLog.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
$tsql.=" join vAgentName vl on vl.agentGuid = vBackupLog.agentGuid 
 where result<>1
 group by vl.groupName";
 
  $stmt = sqlsrv_query( $conn, $tsql);
  if( $stmt === false )
  {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }
  while( $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
	$bulist[$row['groupName']] = $row['count'];
	if (!in_array($row['groupName'], $mgissue)) { $mgissue[] = $row['groupName']; }
  }
}  
  


  
// security //
if ($avon==1) {

  $avlist = array();

  $tsql = "select count(distinct Machine_GroupID) as count, groupName
  from (SELECT avf.InstallStatus, vl.Machine_GroupID,avf.EnableProtection, vl.groupName,
 (select count(VirusName) from AVFile where avf.agentguid = AVFile.agentguid and AVFile.Quarantined<>1) as ActiveThreats
  FROM AVFeature avf
  join vAgentName vl on vl.agentGuid = avf.agentGuid  
  JOIN AVProfile avp ON avf.ProfileId = avp.Id
  join vdb_Scopes_Machines foo on (foo.agentGuid = avf.agentGuid ) 
  where (avf.InstallStatus<>1 or avf.EnableProtection<>1)
  ) poop
  where (ActiveThreats>0 or InstallStatus<>1 or EnableProtection<>1)
  group by groupName";
 
  $stmt = sqlsrv_query( $conn, $tsql);
  if( $stmt === false )
  {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }
  while( $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $avlist[$row['groupName']] = $row['count'];
	if (!in_array($row['groupName'], $mgissue)) { $mgissue[] = $row['groupName']; }
  }
}  
  

  
// Patch Fails //

if ($paton==1) {

 $patlist = array();

 $tsql = "SELECT count(distinct vl.displayName) as count, vl.groupName as groupName
  FROM vPatchPolicyMember";

if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = vPatchPolicyMember.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on vPatchPolicyMember.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql.=" join vAgentName vl on vl.agentGuid = vPatchPolicyMember.agentGuid
  join vPatchStatusByAgent pp on pp.agentGuid = vPatchPolicyMember.agentGuid
  where PolicyName not like '-No policy-' and failed >0
  group by vl.groupName";
 
  $stmt = sqlsrv_query( $conn, $tsql);
  if( $stmt === false )
  {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }
  while( $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
	$patlist[$row['groupName']] = $row['count'];
	if (!in_array($row['groupName'], $mgissue)) { $mgissue[] = $row['groupName']; }
  }
} 
  
// done collecting data // 
// so print out the table // 

echo "<div class=\"heading\">";
echo "Machine Group Status";
echo "</div>";

echo "<div class=\"datatable\">";

echo "<table id=\"dotlist\" class=\"table-header-rotated\">";
echo "<tr><th class=\"colL\">Machine Group</th>";
foreach ($mtlist as $mtitem) { echo "<th class=\"rotate-45\"><div><span>".$mtitem."</span></div></th>"; }
echo "<th class=\"rotate-45\"><div><span>Disk</span></div></th>";
if ($buon==1) { echo "<th class=\"rotate-45\"><div><span>Backup</span></div></th>"; }
if ($avon==1) { echo "<th class=\"rotate-45\"><div><span>KES</span></div></th>"; }
if ($paton==1) { echo "<th class=\"rotate-45\"><div><span>Patch</span></div></th>"; }
echo "</tr>";

//all
//foreach($mglist as $key=>$value) {
	
//errors only	
  foreach($mgissue as $key=>$value) {	
	
	echo "<tr><td class=\"colL\">".$value."</td>";

// draw a dot for each of the alarms in the alarm list
	
    foreach ($mtlist as $mtitem) { 
		echo "<td class=\"colM\">";  
		if (array_key_exists($value, $allist)) {	
			if (array_key_exists($mtitem,$allist[$value])) {
				echo showdot($allist[$value][$mtitem]);
			} else { echo showdot(0); };
		} else { echo showdot(0); };
			
	echo "</td>";
	}
	


// low disk dot
	echo "<td class=\"colM\">";
	if (array_key_exists($value,$ldlist)) { echo showdot($ldlist[$value]); } else { echo showdot(0); };
	echo "</td>";

// backup dot
	if ($buon==1) {
		echo "<td class=\"colM\">";
		if (array_key_exists($value,$bulist)) { echo showdot($bulist[$value]); } else { echo showdot(0); };
		echo "</td>";	 
	}
	 
// security dot
	if ($avon==1) {
		echo "<td class=\"colM\">";
		if (array_key_exists($value,$avlist)) { echo showdot($avlist[$value]); } else { echo showdot(0); };
		echo "</td>";	 
	}
	 
// patch dot
    if ($paton==1) {
		echo "<td class=\"colM\">";
		if (array_key_exists($value,$patlist)) { echo showdot($patlist[$value]); } else { echo showdot(0); };
		echo "</td>";
	} 

	echo "</tr>";
}

echo "</table>";
echo "</div>";

// special one-off spacer div to allow slanting text enough room to not fall outside the border
echo "<div style=\"height: 10px; width: 15px; float:left;\"></div>";


sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>