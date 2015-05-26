<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

$tsql = "select distinct machine_GroupID as machName, ISNULL(bl.result, -1) AS result, count(distinct bl.agentGuid) as resultCount,
  bl.statusType as statusType, ISNULL(bs.completeTime, '19800606') as lastBackup, bs.statusType AS backupType, bs.backupType as bt2, st.online as online, st.currentLogin as currentLogin
  from backupParams p";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = p.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on p.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
$tsql.=" left outer join backuplog as bl on p.agentGuid = bl.agentGuid and bl.eventTime > DATEADD(day,-1,getdate()) AND bl.statusType IN (0, 2, 5, 7, 8, 9, 12, 13, 15, 16, 17, 18, 20, 21)
  join vAgentLabel st on st.agentGuid = p.agentGuid
  left outer join backupStatus bs ON p.backupParamsId = bs.backupParamsId AND bs.completeTime = ( SELECT MAX(completeTime) FROM backupStatus WHERE backupParamsId = p.backupParamsId AND result=1 )
  group by machine_groupID, bl.result, bl.agentGuid, bl.statusType, bs.completetime, bs.statusType, bs.backupType, st.online, st.currentLogin
  order by machine_GroupID";

 
$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}




//* Status - Completed Backups *//

echo "<div class=\"heading\">";
echo "<image src=\"images/acronis-logo.png\" style=\"vertical-align:middle\"> Kaseya Backup Status Last 24Hrs<br/>";
echo "</div>";

//* spacer *//
echo "<div class=\"spacer\">";
echo "</div>";



$bulist = array();


//* tally up results for each agent *//
while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{

$result="";
$found=false;

    foreach ($bulist as $key=>$value) {
	  if ($value['name'] == $row['machName'] ) { $result = $key; $found=true; };
  	}
	
  if ($found==false) { 

  $bulist[] = array('name'=>$row['machName'],'lastBackup'=>$row['lastBackup'],'backupType'=>$row['backupType'],'result'=>$row['result'],'bt2'=>$row['bt2'],'UnScheduled'=>0,'Scheduled'=>0,'Success'=>0,'Fail'=>0,'Skip'=>0,'Cancel'=>0,'online'=>$row['online'],'currentLogin'=>$row['currentLogin']);

    foreach ($bulist as $key=>$value) {
	  if ($value['name'] == $row['machName'] ) { $result = $key; $found=true; };
  	}
  }  
 
 $bulist[$result]['Scheduled'] += 1;

 if ($row['result']==-1) { $bulist[$result]['UnScheduled'] += 1; }
 elseif ($row['result']==0) { $bulist[$result]['Fail'] += $row['resultCount']; }
 elseif ($row['result']==3) { $bulist[$result]['Skip'] += $row['resultCount']; }
 elseif ($row['result']==4) { $bulist[$result]['Cancel'] += $row['resultCount']; }
 else $bulist[$result]['Success'] += $row['resultCount'];
     	
}



//* now tally up grand totals *//


$resultlist = array('Scheduled'=>array('count'=>0,'color'=>'blue'),
'Success'=>array('count'=>0,'color'=>'#009933'),
'Failed'=>array('count'=>0,'color'=>'red'),
'Skipped'=>array('count'=>0,'color'=>'orange'),
'Cancelled'=>array('count'=>0,'color'=>'orange'),
'Missed'=>array('count'=>0,'color'=>'red'));


foreach($bulist as $key=>$value) {
  $resultlist['Scheduled']['count'] += $bulist[$key]['Scheduled'];
  $resultlist['Success']['count'] += $bulist[$key]['Success'];
  $resultlist['Failed']['count'] += $bulist[$key]['Fail'];
  $resultlist['Skipped']['count'] += $bulist[$key]['Skip'];
  $resultlist['Cancelled']['count'] += $bulist[$key]['Cancel'];
  $resultlist['Missed']['count'] += $bulist[$key]['UnScheduled'];
}


foreach($resultlist as $key=>$value) {

echo "<div class=\"minibox\">";
  echo "<div class=\"miniheading\">$key</div>";
  echo "<div class=\"mininum\">";
  
  if ($value['count']==0) {
    echo "<font color=\"#009933\">";
  } else {
    echo "<font color=\"".$value['color']."\">";
  }
  echo $value['count'];
  echo "</font>";
  echo "</div>";
  echo "</div>";

}



sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>