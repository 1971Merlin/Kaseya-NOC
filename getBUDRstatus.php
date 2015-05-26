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

$tsql2 = "select distinct top ".$resultcount." machine_GroupID as machName, bs.startTime as startTime, bs.statusType AS backupType, bs.backupType as bt2, st.online as online, st.currentLogin as currentLogin 
  from backupStatus bs";
if ($usescopefilter==true) { $tsql2.=" join vdb_Scopes_Machines foo on (foo.agentGuid = bs.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql2.=" 
 join dbo.DenormalizedOrgToMach on bs.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
$tsql2.=" join vAgentLabel st on st.agentGuid = bs.agentGuid
  where bs.completeTime is NULL
  order by startTime desc";
 ?>
 
<div id="dialog"></div>
<script type="text/javascript">
$(document).ready(function() {
$( "#dialog" ).dialog({ autoOpen: false, dialogClass: "no-close" });
$( "#backuplogslist td:first-child" ).mouseover(function() { 
	$( "#dialog" ).dialog("option", "title", $(this).text());
	$.ajax({
		'type':'GET',
		'url': "getBUDRStatusPopup.php?name="+$(this).attr('ref'),
		'cache':false,
		'success':function(data) {
			$("#dialog").html(data);
		}
	});
	$("#dialog").dialog("option", "minHeight", 50);
	$("#dialog").dialog("option", "width", 'auto');
	$( "#dialog" ).dialog( "open" );
	$( "#dialog" ).dialog({ position: { my: "left top", at: "right middle", of: $(this), collision: "fit" } });
	}).mouseout(function() {
		$( "#dialog" ).dialog( "close" );
	});
});
</script>
<?php
 
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



//* in progress *//

echo "<div class=\"heading\">";
echo "<image src=\"images/acronis-logo.png\" style=\"vertical-align:middle\"> Kaseya Backup(s) In Progress";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";



echo "<div class=\"datatable\">";
echo "<table id=\"backuplogslist\">";
echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colM\">Start Time</th><th class=\"colM\">Backup Type</th></tr>";

while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC))
{

  echo "<tr><td class=\"colL\">";
  showAgentIcon($row2['online'],$row2['currentLogin']);
  echo "&nbsp;".$row2['machName']."</td><td class=\"colL\">".$row2['startTime']->format($datestyle." ".$timestyle)."</td>";
  
  echo "<td class=\"colL\">";
  
  if ($row2['backupType']==0) { echo "Volume"; }
  if ($row2['backupType']==2) { echo "Folder"; }

  if ($row2['bt2']=='inc') { echo " Incremental"; }
  if ($row2['bt2']=='dif') { echo " Differential"; }
  if ($row2['bt2']=='ful') { echo " Full"; }
  if ($row2['bt2']=='syn') { echo " Synthetic Full"; }
  echo "</td></tr>";

}
echo "</table>";

//* spacer *//
echo "<div class=\"spacer\">";
echo "</div>";

//* Status - Completed Backups *//

echo "<div class=\"heading heading2\">";
echo "Kaseya Backup Status Last 24Hrs<br/>";
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


//* now main table *//

$tooltip = "Backup missed because the scheduler could not create the backup job.&#013;This is usually caused by the agent being offline for an extended period.";

echo "<div class=\"datatable\">";
echo "<table id=\"backuplogslist\">";
echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colL\">Scheduled</th><th class=\"colL\">Success</th><th class=\"colL\">Failed</th>";
echo "<th class=\"colL\">Skipped</th><th class=\"colL\">Cancelled</th><th class=\"colL\" title=\"{$tooltip}\">Missed</th><th class=\"colL\">Last Backup</th><th class=\"colL\">Backup Type</th></tr>";

foreach ($bulist as $key=>$value) {

  echo "<tr><td class=\"colL\" ref=\"{$value['name']}\">";
  showAgentIcon($value['online'],$value['currentLogin']);
  echo "&nbsp;".$value['name']."</td>";
  echo "<td class=\"colM\">".$value['Scheduled']."</td>";
  echo "<td class=\"colM\">".$value['Success']."</td>";
  echo "<td class=\"colM\">";
  if ($value['Fail']>0) echo "<font color=\"red\">{$value['Fail']}</font></td>"; else echo "{$value['Fail']}</td>";
  echo "<td class=\"colM\">";  
  if ($value['Skip']>0) echo "<font color=\"orange\">{$value['Skip']}</font></td>"; else echo "{$value['Skip']}</td>";
  echo "<td class=\"colM\">".$value['Cancel']."</td>";
  echo "<td class=\"colM\">";  
  if ($value['UnScheduled']>0) echo "<font color=\"red\">{$value['UnScheduled']}</font></td>"; else echo "{$value['UnScheduled']}</td>";
  echo "<td class=\"colM\">{$value['lastBackup']->format($datestyle." ".$timestyle)}</td>";
  echo "<td class=\"colL\">";

  if ($value['backupType']==0) { echo "Volume"; }
  if ($value['backupType']==2) { echo "Folder"; }
  
  if ($value['bt2']=='inc') { echo " Incremental"; }
  if ($value['bt2']=='dif') { echo " Differential"; }
  if ($value['bt2']=='ful') { echo " Full"; }
  if ($value['bt2']=='syn') { echo " Synthetic Full"; }


  echo "</td></tr>";
 }


echo "</table>";
echo "</div>";

sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>